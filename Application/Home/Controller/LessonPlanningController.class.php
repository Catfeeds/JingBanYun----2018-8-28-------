<?php
namespace Home\Controller;

use Think\Controller;
use Common\Common\CSV;
use Common\Common\simple_html_dom;
define('PUTAWAY',1);
define('APPROVE',1);
class LessonPlanningController extends PublicController{
    public function __construct() {
        parent::__construct();
        header("Content-type: text/html; charset=utf-8");
        $this->assign('oss_path',C('oss_path'));
    }

    public function getMyCollectedBjResourceList()
    {
        if(!session('teacher.id'))
        {
            $this->ajaxReturn(array('code' => -1 , 'msg' => '您尚未登录!'));
        }
        $Model = M('knowledge_resource');
        $check['knowledge_resource_collect.user_id'] = session('teacher.id');
        $check['knowledge_resource_collect.role'] = 0;
        $check['knowledge_resource.putaway_status']=PUTAWAY;
        $check['knowledge_resource.status']=APPROVE;
        $check['knowledge_resource.id'] = array('not in',array_merge(json_decode(GUOXUE_SUBRESOURCE_IDLIST,true),array(GUOXUE_ID)));
        $result = $Model
            ->join('knowledge_resource_point ON knowledge_resource_point.knowledge_resource_id = knowledge_resource.id')
            ->join('knowledge_resource_file_contact ON knowledge_resource_file_contact.resource_id = knowledge_resource.id')
            ->join('(select id,name from biz_textbook) a  on knowledge_resource_point.textbook=a.id','left')
            ->join('knowledge_resource_collect on knowledge_resource_collect.resource_id=knowledge_resource.id')
            ->field('knowledge_resource.id,knowledge_resource.name,knowledge_resource.file_type type ,knowledge_resource_file_contact.resource_path file_path,\''. C('oss_path') . '\' as oss_path,knowledge_resource_file_contact.vid,knowledge_resource_file_contact.vid_fullpath,knowledge_resource_file_contact.flag,\'bj\' as category')
            ->order('knowledge_resource.create_at desc')
            ->where($check)
            ->group('knowledge_resource.id')
            ->select();
        for($i=0;$i<sizeof($result);$i++)
        {
                $where['resource_id']   =   $result[$i]['id'];
                $where['knowledge_resource_file_contact.type'] = array('in',array('','video','audio','image','ppt'));
                $res = M('knowledge_resource_file_contact')->join('knowledge_resource ON knowledge_resource.id = resource_id')->where($where)->field('file_name,resource_path,vid,knowledge_resource_file_contact.id as contactid,is_transition,knowledge_resource_file_contact.vid_fullpath,(case when knowledge_resource.file_type = \'mixed\' then knowledge_resource_file_contact.type else knowledge_resource.file_type end) type')->select();
                $result[$i]['child'] = $res;
        }
        $this->ajaxReturn(array('code' => 0 , 'msg' => $result));
    }
    /*
     * 部分参数说明: name:备课资料名称 content:网页备课数组
     */
    public function submitLessonPlanning()
    {
        if(!session('teacher.id'))
        {
            $this->ajaxReturn(array('code' => -1 , 'msg' => '您尚未登录!'));
        }
        $teacherId = session('teacher.id');
        if($_POST)
        {
            $contact_model=M('biz_lesson_planning_contact');
            $Model = M('biz_lesson_planning');
            $Model->startTrans();
            if($_GET['id'])//if is editing lesson planning
            {
                $addDataId = $_GET['id'];
            }
            $addData = array (
                'name'      => $_POST['name'],
                'course_id' => $_POST['course_id'],
                'grade_id' => $_POST['grade_id'],
                'textbook_id' => $_POST['textbook_id'],
                'ver'       => 3,
                'update_at' => time(),
                'teacher_id' => $teacherId
            );
            if(!$_GET['id'])
            {
                $addData['create_at'] = time();
                $addDataId = $Model-> add($addData);
            }
            else
            {
                //判断该教师是否有编辑该教案的权限
                $res = $Model->where('id='.$addDataId)->field('teacher_id')->find();
                if($res['teacher_id'] != $teacherId)
                    $this->ajaxReturn(array('code' => -1 , 'msg' => '您没有编辑该备课资料的权限!'));
                $Model->where('id='.$addDataId)->save($addData);
            }
            $contact_model->startTrans();
            $pages = sizeof($_POST['content']);

            $flag = 1;
            $contact_model->where('biz_lesson_planning_id=' .$addDataId)->delete();
            $contactAddData['biz_lesson_planning_id'] = $addDataId;
            for( $i=0 ; $i<$pages ; $i++ )
            {
                $contactAddData['content'] = $_POST['content'][$i];
                if(false == $contact_model ->add($contactAddData))
                {
                    $flag = 0;
                    $contact_model->rollback();
                    break;
                }
            }
            if(1 == $flag)
            {
                $Model->commit();
                $contact_model->commit();
                $this->ajaxReturn(array('code' => 0 , 'msg' => '添加/修改备课成功'));
            }
            else
            {
                $Model->rollback();
                $this->ajaxReturn(array('code' => -1 , 'msg' => '添加/修改备课失败'));
            }
        }
    }

    /*
     *我收藏的教师资源列表
     */
    public function getMyCollectedResourceList(){
        $Model = M('biz_resource');
        if(!session('teacher.id'))
        {
            $this->ajaxReturn(array('code' => -1 , 'msg' => '您尚未登录!'));
            return;
        }
        $check['biz_resource_collect.user_id'] =session('teacher.id');
        $check['biz_resource_collect.user_type'] = 0;
        $check['biz_resource.status'] = 2;
        $check['_string'] = "biz_resource.type not in('word,pdf,ppt')";

        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
            ->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')
            ->field('biz_resource.id,biz_resource.name,\''. C('oss_path') . '\' as oss_path,biz_resource.type,\'teacher\' as category')
            ->where($check)
            ->order("biz_resource.create_at desc")
            ->select();
        foreach($result as $key=>$value){
            $res = M('biz_resource_contact')->where('biz_resource_id='.$value['id'])->field('id  as contactid ,resource_path,vid,is_transition')->select();

            if(empty($res)){
                unset($result[$key]);
            }else{
                $result[$key]['child']=$res;
            }
        }
        //sort($result);
        $this->ajaxReturn(array('code' => 0 , 'msg' => $result));
    }

    /*
     * 我的素材列表
     */
    public function getMyMaterial(){
        $Model = M('biz_material');
        if(!session('teacher.id'))
        {
            $this->ajaxReturn(array('code' => -1 , 'msg' => '您尚未登录!'));
            return;
        }
        $check['biz_material.teacher_id'] =session('teacher.id');

        $result=$Model->where($check)->field('id,material_name as name,type,file_path,vid,\''.C('oss_path').'\' as oss_path,is_transition')->order('create_at desc')->select();
        $this->ajaxReturn(array('code' => 0,'msg' => $result));
    }

    /*
     * 删除某个素材
     */
    public function deleteMyMaterial(){
        $Model = M('biz_material');
        if(!session('teacher.id'))
        {
            $this->ajaxReturn(array('code' => -1 , 'msg' => '您尚未登录!'));
            return;
        }
        if(!I('id')){
            $this->ajaxReturn(array('code' => -1 , 'msg' => '数据异常!'));
            return;
        }
        $check['biz_material.teacher_id'] =session('teacher.id');
        $check['id']=intval(I('id'));
        if($Model->where($check)->delete()){
            $this->ajaxReturn(array('code' => 0 , 'msg' => '删除成功'));
        }else{
            $this->ajaxReturn(array('code' => -1 , 'msg' => '删除失败'));
        }
    }

    public function addMyMaterial()
    {
        if(!session('teacher.id'))
        {
            $this->ajaxReturn(array('code' => -1 , 'msg' => '您尚未登录!'));
        }
        if($_POST)
        {
            $Model = M('biz_material');
            $addData = array(
                'type' => $_POST['type'],
                'teacher_id' => session('teacher.id'),
                'create_at' => time(),
                'file_path' => $_POST['file_path'],
                'vid' => $_POST['vid'],
                'flag' => 0
            );
            $Model->add($addData);
            $this->ajaxReturn(array('code' => 0 , 'msg' => '添加成功!'));
        }
        else
        {
            $this->ajaxReturn(array('code' => -1 , 'msg' => '缺少POST参数!'));
        }
    }

    public function getLessonPlanning($lessonPlanningId='',$isReturn=false)
    {
        if(!session('teacher.id') && !$isReturn)
        {
            $this->ajaxReturn(array('code' => -1 , 'msg' => '您尚未登录!'));
        }
        $ossPath = C('oss_path');
        if($_GET)
        {
            if(empty($_GET['id']))
                $this->ajaxReturn('missing id parameter!');
            if($lessonPlanningId=='')
                $lessonPlanningId = $_GET['id'];
            $Model = M('biz_lesson_planning');
            $contact_model=M('biz_lesson_planning_contact');
            //verify auth
            if(intval($lessonPlanningId) == 0)
             $this->ajaxReturn(array('code' => -1 , 'msg' => 'id error'));
            $teacherRes = $Model->where('id='. $lessonPlanningId)->field('name,course_id,grade_id,textbook_id,teacher_id')->find();
            if($teacherRes['teacher_id'] != session('teacher.id') && !$isReturn)
            {
                $this->ajaxReturn(array('code' => -1 , 'msg' => '您没有权限查看该教案!','sessionid' => session('teacher.id'),'teacherid' => $teacherRes['teacher_id'] ));
            }
            $content_array = $contact_model
                ->join('biz_lesson_planning ON biz_lesson_planning_contact.biz_lesson_planning_id = biz_lesson_planning.id')
                ->where('biz_lesson_planning.ver=3 and biz_lesson_planning.id='.$lessonPlanningId )
                ->field('biz_lesson_planning_contact.content')
                ->select();


            foreach($content_array as $key => $val)
            {
                $html = new simple_html_dom();
                $html->load('<html><body>'.$val['content'] . '</body></html>');

                $mediaRes =  $html->find('img,video,audio');

                $bjResourceModel = M('knowledge_resource');
                $bjResourceContactModel = M('knowledge_resource_file_contact');
                $ResourceModel = M('biz_resource');
                $ResourceContactModel = M('biz_resource_contact');
                $myMaterialModel = M('biz_material');


                for($i=0;$i<sizeof($mediaRes);$i++)
                {
                    $category = $mediaRes[$i]->category;

                    if(!$category)
                        continue;
                    switch($category)
                    {
                        case 'bj' : $mainModel = $bjResourceModel;
                            $contactModel = $bjResourceContactModel;
                            break;
                        case 'teacher':   $mainModel = $ResourceModel;
                            $contactModel = $ResourceContactModel;
                            break;
                        case 'mymaterial':$mainModel = $myMaterialModel;
                            break;

                        default : continue;
                    }

                    $contactId = $mediaRes[$i]->contactid;
                    if($contactId)  //new version bj resource
                    {
                        //query path of resource
                        $res = $contactModel->where('id='.$contactId)->field('resource_path,vid')->find();
                        $mediaRes[$i]->src = $ossPath . $res['resource_path'];
                        $mediaRes[$i]->vid = $res['vid'];
                    }
                    else
                    {
                        if(!$mediaRes[$i]->resid ) {
                            $mediaRes[$i]->resid = $mediaRes[$i]->id;
                        }
                        if($mainModel == $myMaterialModel) {
                            $res = $mainModel->where('id=' . $mediaRes[$i]->resid)->field('file_path,vid')->find();
                        }
                        elseif($mainModel == $ResourceModel) {
                            $res = $mainModel->where('id=' . $mediaRes[$i]->resid)->field('file_path,vid,vid_fullpath')->find();
                        }
                        elseif($mainModel == $bjResourceModel) {
                            $res = $contactModel->where('id=' . $mediaRes[$i]->resid)->field('resource_path file_path,vid,vid_fullpath')->find();
                        }
                        if($res['file_path'])
                            $mediaRes[$i]->src = $ossPath . $res['file_path'];
                        else
                            $mediaRes[$i]->src = $res['vid_fullpath'];
                        $mediaRes[$i]->vid = $res['vid'];
                    }
                }
                $content_array[$key]['content'] = $html->save();
                $content_array[$key]['content'] = substr($content_array[$key]['content'],12,strlen($content_array[$key]['content'])-26);
            }
            if($isReturn)
                return json_encode(array('code' => 0 , 'name'=>$teacherRes['name'] ,'course_id' => $teacherRes['course_id'],'grade_id' => $teacherRes['grade_id'], 'textbook_id' => $teacherRes['textbook_id'], 'msg' => $content_array));
            else
                $this->ajaxReturn(array('code' => 0 , 'name'=>$teacherRes['name'] ,'course_id' => $teacherRes['course_id'],'grade_id' => $teacherRes['grade_id'], 'textbook_id' => $teacherRes['textbook_id'], 'msg' => $content_array));
        }

    }
    public function refreshFilePath()
    {
        set_time_limit(0);
        $Model = M('biz_bj_resources');
        $res = $Model->where('(type=\'video\' or type = \'audio\') and file_path is null and vid_fullpath is null')->field('id,vid,file_path')->select();
        foreach ($res as $key => $val) {
            $config = C('BLWS_CONFIG');
            $blwsQueryResult = json_decode(file_get_contents("http://v.polyv.net/uc/services/rest?method=getById&vid=" . $val['vid'] . "&readtoken=" . $config['READ_TOKEN']), true);
            $mediaSource = $blwsQueryResult['data'][0]['mp4'];
            $data['vid_fullpath'] = $mediaSource;
            $Model->where('id='.$val['id'])->save($data);
            $res[$key]['mediaSource'] = $mediaSource;
        }

    }
    public function getPPTHTMLs()
    {
        $category = $_GET['category'];
        $resId = $_GET['resid'];
        if(isset($_GET['contactid']))
            $contactId = $_GET['contactid'];
        switch($category)
        {
            case 'bj':
                $tableName = 'knowledge_resource_file_contact';
                $ossSubPath = 'bjresource/'.$resId;
                if(isset($contactId))
                    $ossSubPath = $ossSubPath.'/'.$contactId.'/';
                $selectId = $contactId;
                break;
            case 'teacher':  $tableName = 'biz_resource_contact';
                $ossSubPath = 'teacher/'.$resId;
                if(isset($contactId))
                    $ossSubPath = $ossSubPath.'/'.$contactId.'/';
                $selectId = $contactId;
                break;
            case 'material': $tableName = 'biz_material';
                $ossSubPath = 'material/'.$resId .'/';
                $selectId = $resId;
                break;
            default:         $tableName = '';
                break;

        }
        if($tableName != '')
        {
            $Model = M($tableName);
            $res = $Model->where('id='.$selectId)->field('ppt_html,ppt_pages')->find();
            if(empty($res))
                $this->ajaxReturn(array('code' => -1,'content'=>'资源不存在'));
            if ($res['ppt_html'] == 1) //convert finish
            {
                for($i=1;$i<=$res['ppt_pages'];$i++)
                {
                    $file = file_get_contents(C('oss_path') .$ossSubPath.'slide'.$i.'.html');
                    $result[] = $file;
                }
                $this->ajaxReturn(array('code' => 0,'content'=>$result));
            }
            $this->ajaxReturn(array('code' => -1,'content'=>'PPT未转换完成'));
        }
    }
    
    /*
     * 获得我收藏的习题
     */
    public function getMyCollectExercise(){
        if(!session('teacher.id') )
        {
            $this->ajaxReturn(array('code' => -1 , 'msg' => '您尚未登录!'));
        } 
        $teacherId=session('teacher.id');   
        $Model=M('biz_exercise_library');
        $result = $Model
                ->join('biz_exercise_template on biz_exercise_library.type=biz_exercise_template.id')
                ->join('biz_exercise_library_chapter on biz_exercise_library_chapter.id=biz_exercise_library.chapter_id') 
                ->join("biz_exercise_collect on biz_exercise_collect.exercise_id=biz_exercise_library.id and biz_exercise_collect.role=0 and biz_exercise_collect.user_id=".$teacherId) 
                ->field("biz_exercise_library.*,IFNULL(biz_exercise_collect.id,'no') is_collect,biz_exercise_library_chapter.course_id,grade_id,textbook_id,biz_exercise_template.template_name")
                ->order("biz_exercise_collect.create_at desc")
                ->select();  
         $this->ajaxReturn(array('code' => 0 , 'msg' => $result));
    }

}