<?php
namespace Home\Controller;

use Think\Controller;


class ConvertController extends PublicController
{
    function __construct(){
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));
    }

    public function queryNeedToConvertPPTToHTML5()
    {
        $Dao = M();
        $oss_path = C('oss_path');
        $qr="SELECT b.id,b.biz_bj_resource_id as mid,b.filepath,'".$oss_path."' as oss_path,'bjresource' as category FROM biz_bj_resources
         JOIN (SELECT id,biz_bj_resource_id,resource_path as filepath,ppt_html from biz_bj_resource_contact WHERE ppt_html = 0) b
         ON b.biz_bj_resource_id = biz_bj_resources.id and biz_bj_resources.type='ppt' UNION ALL
         SELECT b.id,b.biz_resource_id as mid,b.filepath,'".$oss_path."' as oss_path,'teacher' as category FROM biz_resource
         JOIN (SELECT id,biz_resource_id,resource_path as filepath,ppt_html FROM biz_resource_contact WHERE ppt_html = 0) b
         ON b.biz_resource_id = biz_resource.id and biz_resource.type='ppt' UNION ALL
         SELECT id,0 as mid,file_path,'".$oss_path."' as oss_path, 'material' as category  FROM biz_material WHERE ppt_html = 0 and type = 'ppt'";
        $result = $Dao->query($qr);
        $this->ajaxReturn(array('status' => 200, 'result' => $result));
    }

    public function updateConvertPPTToHTML5ByID()
    {
        $id = getParameter('id', 'int');
        $category = getParameter('category', 'int');
        $pages = getParameter('pages', 'int');

        switch($category)
        {
            case 'bjresource':
                $tableName = 'biz_bj_resource_contact';
                break;
            case 'teacher':  $tableName = 'biz_resource_contact';
                break;
            case 'material': $tableName = 'biz_material';
                break;
            default:         $tableName = '';
                break;
        }
        $data = array(
            'ppt_html' => 1,
            'ppt_pages' => $pages
        );
        if($tableName != '')
        {
            M(tableName)->where('id='.$id)->save($data);
            $renderData = array('ok');
        }
        $this->ajaxReturn(array('status' => 200, 'result' => $renderData));
    }

    function getUnProcessedPPTs4Material()
    {
        $key = getParameter('key', 'str');
        if ($key == 'uefdsf809f9rfef343f') {
            $qr = "select id,teacher_id,file_path,type from biz_material where flag=1 and type in ('word','ppt','pdf') order by create_at desc";
            $result = M()->query($qr);
            $this->ajaxReturn(array('status' => 200, 'result' => $result));
        }

    }

    function updateOssFlag4Material()
    {
        $key = getParameter('key', 'str');
        $id = getParameter('key', 'int');
        if ($key == 'uefdsf809f9rfef343f') {
            M()->execute('update biz_material set flag = 2 where id='.$id);
            $this->ajaxReturn(array('status' => 200));
        }
    }

    //BJ

    function getUnProcessed4BjResource()
    {
        $key = getParameter('key', 'str');
        if ($key == 'uefdsf809f9rfef343f') {
            $qr = "select id,name,type,file_path from biz_bj_resources where flag=0 and biz_bj_resources.status=2 and type in ('word','ppt','pdf') order by create_at desc";
            $result = M()->query($qr);
            $this->ajaxReturn(array('status' => 200, 'result' => $result));
        }

    }


    function getUnProcessedSub4BjResourceContact()
    {
        $key = getParameter('key', 'str');
        if ($key == 'uefdsf809f9rfef343f') {
            $qr = "select biz_bj_resource_contact.id,biz_bj_resource_id as bj_resource_id,resource_path as file_path,type from biz_bj_resource_contact
               join (select id,type from biz_bj_resources where type in ('word','ppt','pdf') and biz_bj_resources.status=2) a on a.id = biz_bj_resource_contact.biz_bj_resource_id where flag=1";
            $result = M()->query($qr);
            $this->ajaxReturn(array('status' => 200, 'result' => $result));
        }

    }


    function updateOssFlag4BjResource()
    {
        $key = getParameter('key', 'str');
        $id = getParameter('key', 'int');
        if ($key == 'uefdsf809f9rfef343f') {
            M()->execute('update biz_bj_resources set flag = 2 where id='.$id);
            $this->ajaxReturn(array('status' => 200));
        }

    }

    function updateOssFlagSub4BjResourceContact()
    {
        $key = getParameter('key', 'str');
        $id = getParameter('key', 'int');
        if ($key == 'uefdsf809f9rfef343f') {
            M()->execute('update biz_bj_resource_contact set flag = 2 where id='.$id);
            $this->ajaxReturn(array('status' => 200));
        }

    }

    function getUnProcessedPPTs4Resource()
    {
        $key = getParameter('key', 'str');
        if ($key == 'uefdsf809f9rfef343f') {
            $qr = "select id,name,teacher_id,file_path from biz_resource where flag=0 and type in ('word','ppt','pdf') order by create_at desc";
            $result = M()->query($qr);
            $this->ajaxReturn(array('status' => 200, 'result' => $result));
        }

    }


    function getUnProcessedSubPPTs4ResourceContact()
    {
        $key = getParameter('key', 'str');
        if ($key == 'uefdsf809f9rfef343f') {
            $qr = "select biz_resource_contact.id,type,biz_resource_id as bj_resource_id,resource_path as file_path,type from biz_resource_contact
                  join (select id,type from biz_resource where type in ('word','ppt','pdf')) a on a.id = biz_resource_contact.biz_resource_id where flag=1";
            $result = M()->query($qr);
            $this->ajaxReturn(array('status' => 200, 'result' => $result));
        }

    }


    function updateOssFlag4Resource()
    {
        $key = getParameter('key', 'str');
        $id = getParameter('key', 'int');
        if ($key == 'uefdsf809f9rfef343f') {
            M()->execute('update biz_resource set flag = 2 where id='.$id);
            $this->ajaxReturn(array('status' => 200));
        }

    }

    function updateOssFlagSub4ResourceContact()
    {
        $key = getParameter('key', 'str');
        $id = getParameter('key', 'int');
        if ($key == 'uefdsf809f9rfef343f') {
            M()->execute('update biz_resource_contact set flag = 2 where id='.$id);
            $this->ajaxReturn(array('status' => 200));
        }

    }

    //获取京版活动中未转换为PDF的PPT DOC
    /* OSS PATH:
     * Activity \ ID \ attachment...
                \ Works \ workId \ works
     *
     */
    function getUnProcessedPPTDOCs4ActivityAttachment()
    {
        $key = getParameter('key', 'str');
        if ($key == 'uefdsf809f9rfef343f') {
            $qr = "select id,activity_id,activity_file_name as name,activity_file_path as file_path,type from social_activity_contact_file where type in ('word','ppt') and flag =1";
            $result = M()->query($qr);
            $this->ajaxReturn(array('status' => 200, 'result' => $result));
        }

    }
    //更新京版活动文件转换状态
    function updateOssFlag4ActivityAttachment()
    {
        $key = getParameter('key', 'str');
        $id = getParameter('id', 'int');
        if ($key == 'uefdsf809f9rfef343f') {
            M()->execute('update social_activity_contact_file set flag = 2 where id='.$id);
            $this->ajaxReturn(array('status' => 200));
        }

    }

    //获取京版活动中未转换为PDF的PPT DOC
    function getUnProcessedPPTDOCs4ActivityWork()
    {
        $key = getParameter('key', 'str');
        if ($key == 'uefdsf809f9rfef343f') {
            $qr = 'SELECT 
                 social_activity_works_file.id,
                 activity_works_id work_id,
                 activity_id,
                 social_activity_works_file.type,
                 social_activity_works_file.works_file_name AS name,
                 works_file_path AS file_path
             FROM
                 social_activity_works_file
                     JOIN
                 social_activity_works ON social_activity_works.id = activity_works_id
                     JOIN
                 social_activity_register ON social_activity_register.id = social_activity_works.activity_register_id
             WHERE
                 type IN (\'word\' , \'ppt\') AND flag = 1';
            $result = M()->query($qr);
            $this->ajaxReturn(array('status' => 200, 'result' => $result));
        }

    }
    //更新京版活动文件转换状态
    function updateOssFlag4ActivityWork()
    {
        $key = getParameter('key', 'str');
        $id = getParameter('id', 'int');
        if ($key == 'uefdsf809f9rfef343f') {
            M()->execute('update social_activity_works_file set flag = 2 where id='.$id);
            $this->ajaxReturn(array('status' => 200));
        }

    }

}

?>