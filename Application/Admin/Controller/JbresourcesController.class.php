<?php

namespace Admin\Controller;

use Think\Controller;
use Think\Verify;
use Common\Common\CSV;
define('DATE_TIME',3600*24);

class JbresourcesController extends Controller
{

    private $model = '';

    public function __construct()
    {
        parent::__construct();
        $this->model = D('Knowledge_point');
        $this->assign('oss_path', C('oss_path'));
    }

    //京版资源
    function jbresources()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '京版资源');
        $this->assign('nav', '京版资源');
        $this->assign('subnav', '');


        $filter['id'] = $_REQUEST['id'];
        $filter['keyword'] = $_REQUEST['keyword'];
        $filter['start'] = $_REQUEST['start'];
        $filter['end'] = $_REQUEST['end'];
        $filter['course_id'] = $_REQUEST['course_id'];
        $filter['grade_id'] = $_REQUEST['grade_id'];
        $filter['textbook_id'] = $_REQUEST['textbook_id'];
        $filter['chapter'] = $_REQUEST['chapter'];
        $filter['festival'] = $_REQUEST['festival'];
        $filter['file_type'] = $_REQUEST['file_type'];
        $filter['knowledgePoint'] = $_REQUEST['knowledgePoint'];
        $filter['displayStatus'] = $_REQUEST['displayStatus'];
        $filter['putaway_status'] = I('putaway_status');
        $filter['status'] = I('status');
        $filter['resourceType'] = I('resourceType');
        $filter['resourceFileType'] = I('resourceFileType');

        if (!empty($filter['id'])) $check['knowledge_resource.id'] = $filter['id'];
        if (!empty($filter['keyword'])) $check['knowledge_resource.name'] = array('like','%'.$filter['keyword'].'%');
        if (!empty($filter['start']) && !empty($filter['end'])) $check['putaway_time'] = array('between', array(strtotime($filter['start']), strtotime($filter['end']) + 86399));
        if (!empty($filter['course_id'])) $check['course'] = $filter['course_id'];
        if (!empty($filter['grade_id'])) $check['grade'] = $filter['grade_id'];
        if (!empty($filter['textbook_id'])) $check['textbook'] = $filter['textbook_id'];
        if (!empty($filter['chapter'])) $check['chapter'] = $filter['chapter'];
        if (!empty($filter['festival'])) $check['festival'] = $filter['festival'];
        if (!empty($filter['file_type'])) $check['resource_type'] = $filter['file_type'];
        if (!empty($filter['resourceFileType'])) $check['knowledge_resource.file_type'] = $filter['resourceFileType'];
        if (!empty($filter['resourceType'])) $check['knowledge_type.id'] = $filter['resourceType'];

        if (($filter['knowledgePoint'] == 1)) {
            $check['knowledge_resource_point.id'] = array('exp','is not null');
        }
        else if(($filter['knowledgePoint'] == 2)) {
            $check['knowledge_resource_point.id'] = array('exp','is null');
        }
        if (($filter['displayStatus'] == 2)) {
            $map['_logic'] = 'or';
            $map['putaway_status'] = 0;
            $map['status'] = array('neq',1);
            $check['_complex'] = $map;
        }else if (($filter['displayStatus'] == 1)) {
            $map['_logic'] = 'and';
            $map['putaway_status'] = 1;
            $map['status'] = array('eq',1);
            $check['_complex'] = $map;
        }
        if ($filter['putaway_status'] !== '' && $filter['putaway_status'] !== null) $check['putaway_status'] = $filter['putaway_status'];
        if ($filter['status'] !== '' && $filter['status'] !== null) $check['status'] = $filter['status'];

        $this->assign('id', $filter['id']);
        $this->assign('keyword', $filter['keyword']);
        $this->assign('start', $filter['start']);
        $this->assign('end', $filter['end']);
        $this->assign('course_id', $filter['course_id']);
        $this->assign('grade_id', $filter['grade_id']);
        $this->assign('textbook_id', $filter['textbook_id']);
        $this->assign('chapter', $filter['chapter']);
        $this->assign('festival', $filter['festival']);
        $this->assign('knowledgePoint',$filter['knowledgePoint']);
        $this->assign('displayStatus',$filter['displayStatus']);
        $this->assign('file_type', $filter['file_type']);
        $this->assign('putaway_status', $filter['putaway_status']);
        $this->assign('status', $filter['status']);
        $this->assign('resourceFileTypeSelected', $filter['resourceFileType']);
        $this->assign('resourceTypeSelected', $filter['resourceType']);

        $Model = M('dict_course_copy_resource');
        $courses = $Model->order('sort_order asc')->select();

        $Model = M('dict_grade');
        $grades = $Model->select();
        //册
        if (!empty($filter['course_id']) && !empty($filter['grade_id'])) {
            $c1['course_id'] = $filter['course_id'];
            $c1['grade_id'] = $filter['grade_id'];
            $this->model = D('Knowledge_resource');
            $textbook_result = $this->model->get_textbooks($c1);
            $this->assign('textbook_list', $textbook_result);
        }
        //章、单元
        if (!empty($filter['textbook_id'])) {

            $c1['textbook_id'] = $filter['textbook_id'];
            $c1['level'] = '1';
            $this->model = D('Knowledge_point');
            $chapter_result = $this->model->get_resources($c1);
            $this->assign('chapter_list', $chapter_result);
        }
        //节课
        if (!empty($filter['chapter'])) {
            $c2['parent_id'] = $filter['chapter'];
            $c2['level'] = '2';
            $this->model = D('Knowledge_point');
            $festival_result = $this->model->get_resources($c2);
            $this->assign('festival_list', $festival_result);
        }

        $this->model = D('Knowledge_resource');
        $data = $this->model->getResourceAll($check, $filter);
        $resourceType = D('Knowledge_type')->get_resources_all();
        $resourceFileType = C('BJ_RESOURCE_UPLOAD_FILE_TYPE');
        $this->assign('resourceFileType',$resourceFileType);
        $this->assign('resourceType',$resourceType);
        $this->assign('role', session('admin.role'));
        $this->assign('courses', $courses);
        $this->assign('grades', $grades);
        $this->assign('list', $data['list']);
        $this->assign('page', $data['page']);
        $this->assign('whereCondition', urlencode(json_encode($check)));
        $this->display('resourceList');
    }


    //删除京版资源
    public function deleteJBResource()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $Model = M('knowledge_resource');
        $Model->startTrans();
        if ($Model->where("id=$id")->delete() === false) {
            $Model->rollback();
            $this->ajaxReturn('failed');
        }

        $contact_model = M('knowledge_resource_file_contact');
        if ($contact_model->where("resource_id=" . $id)->delete() === false) {
            $Model->rollback();
            $this->ajaxReturn('failed');
        }
        //删除关联表dict_column_contact中的数据
        $column_contact_model = M('dict_column_contact');
        if ($column_contact_model->where("resource_id=" . $id)->delete() === false) {
            $Model->rollback();
            $this->ajaxReturn('failed');
        }
        //删除关联表knowledge_resource_attr中的数据
        $knowledge_resource_attr = M('knowledge_resource_attr');
        if ($knowledge_resource_attr->where("resource_id=" . $id)->delete() === false) {
            $Model->rollback();
            $this->ajaxReturn('failed');
        }

        //删除关联表knowledge_resource_point中的数据
        $knowledge_resource_point_model = M('knowledge_resource_point');
        if($knowledge_resource_point_model->where("knowledge_resource_id=" . $id)->delete() === false){
            $Model->rollback();
            $this->ajaxReturn('failed');
        }

        $Model->commit();
//更新knowledge_count字段
        A('Admin/Common')->updateKnowledge_count();

        $this->refreshRecommendResource($id);

        $this->ajaxReturn('success');
    }

    /*
     *ajax获取分册
     */
    public function ajax_get_textbooks()
    {
        $check['course_id'] = $_REQUEST['course_id'];
        $check['grade_id'] = $_REQUEST['grade_id'];
        if(!empty($_REQUEST['publishing_id'])){
            $check['publishing_house_id'] =$_REQUEST['publishing_id'];
        }

        /* $check['course_id'] = '3';
         $check['grade_id'] = '1';*/
        $this->model = D('Knowledge_resource');
        $textbook_result = $this->model->get_textbooks($check);
        echo json_encode($textbook_result);
    }

    /*
    *ajax获取节
    */
    public function ajax_get_chapter()
    {
        // $check['textbook'] = $_REQUEST['textbook_id'];
        $check['textbook_id'] = $_REQUEST['textbook_id'];
        $check['level'] = '1';
        $this->model = D('Knowledge_point');
        $data = $this->model->get_resources($check);
        echo json_encode($data);
    }

    /*
    *ajax获取章知识点
    */
    public function ajax_get_festival()
    {
        $check['parent_id'] = $_REQUEST['chapter'];
        $check['level'] = '2';
        $this->model = D('Knowledge_point');
        $data = $this->model->get_resources($check);
        echo json_encode($data);
    }

    /*
   *ajax获取章知识点一
   */
    public function ajax_get_festival1()
    {
        $check['parent_id'] = $_REQUEST['festival'];
        $check['level'] = '3';
        $this->model = D('Knowledge_point');
        $data = $this->model->get_resources($check);
        echo json_encode($data);
    }

    /*
   *ajax获取章知识点二
   */
    public function ajax_get_festival2()
    {
        $check['parent_id'] = $_REQUEST['knowledge'];
        $check['level'] = '4';
        $this->model = D('Knowledge_point');
        $data = $this->model->get_resources($check);
        echo json_encode($data);
    }

    /*
     *ajax获取年级和学科
     */
    public function ajax_get_grade_course_by_publishing(){
        $check['publishing_house_id'] = $_REQUEST['publishing'];
        $this->model = D('Knowledge_resource');
        $data['grade'] = $this->model->getGradeByPublishing(1);//获得年级
        $data['course'] = $this->model->getCourseByPublishing(1);//获得学科
        echo json_encode($data);
    }
    /*
     * 创建京版资源
     */
    function createBJResource()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        //获取教材版本
        $publishingDate = D('Knowledge_resource')->publishing();
        $this->assign('publishingDate',$publishingDate);
        //获取资源类型（课件，微课）
        $this->model = D('Knowledge_type');
        $data = $this->model->get_resources_all();
        $this->assign('data', $data);


        if ($_POST) {

            $type = $_POST['type'];
            $ids = array();
            $ResourceModel = M('knowledge_resource');
            $contact_model = M('knowledge_resource_file_contact');
            $knowledge_resource_attr = M('knowledge_resource_attr');
            $knowledge_resource_type_contact = M('knowledge_resource_type_contact');
            $knowledge_resource_point = M('knowledge_resource_point');
            $ResourceModel->startTrans();

            $_POST['name']=  trim($_POST['name']);
            $_POST['description']=  trim($_POST['description']);
            //获取pc封面图片、手机封面图片OSS地址
            if(!empty($_POST['pc_cover_dataurl'])) {
                $uploadResult = $this->__uploadCover($_POST['pc_cover_dataurl']);
                if ($uploadResult === false) {
                    $ResourceModel->rollback();
                    $this->error('上传PC封面失败,请重试');
                }
                $data['pc_cover'] = $uploadResult;
            }
            if(!empty($_POST['mobile_cover_dataurl'])) {
                $uploadResult = $this->__uploadCover($_POST['mobile_cover_dataurl']);
                if ($uploadResult === false) {
                    $ResourceModel->rollback();
                    $this->error('上传APP封面失败,请重试');
                }
                $data['mobile_cover'] = $uploadResult;
            }
//            if($_POST['pc_cover'])
//                $data['pc_cover'] = $_POST['pc_cover'];
//            if($_POST['mobile_cover'])
//                $data['mobile_cover'] = $_POST['mobile_cover'];
//            //获取pc封面图片、手机封面图片OSS地址
//            if ($_FILES['pc_cover']['name'] && $_FILES['mobile_cover']['name']) {
//                $arr1 = $this->upload_file2($_FILES['pc_cover']);
//                $arr2 = $this->upload_file2($_FILES['mobile_cover']);
//                $data['pc_cover'] = $arr1;
//                $data['mobile_cover'] = $arr2;
//            } elseif ($_FILES['pc_cover']['name']) {
//                $arr = $this->upload_file2($_FILES['pc_cover']);
//                $data['pc_cover'] = $arr;
//            } elseif ($_FILES['mobile_cover']['name']) {
//                $arr = $this->upload_file2($_FILES['mobile_cover']);
//                $data['mobile_cover'] = $arr;
//            }

            $vider_trial_time_s=C('TRIAL_TIME');
            if ($type == 'video' || $type == 'audio' ||  $type == 'mixed') {
                $vid_array = explode(',', $_POST['vid']);
                $vid_path_array = explode(',', $_POST['vid_file_path']);
                $playerwidth = explode(',', $_POST['playerwidth']);
                $playerduration = explode(',', $_POST['playerduration']);
                $vid_fullpath_array = explode(',', $_POST['vid_fullpath']);
                $vid_image_path_array = explode(',', $_POST['vid_image_path']);
                $vid_transition_status = explode(',', $_POST['is_transition']);

                $vid_size = explode(',', $_POST['vid_fullsize']);
                $url_arr = array();
                foreach ($vid_array as $key => $v) {
                    $contact_data['vid'] = $v;
                    $contact_data['playerwidth'] = $playerwidth[$key];
                    $contact_data['playerduration'] = $playerduration[$key];
                    $contact_data['vid_image_path'] = $vid_image_path_array[$key];
                    $contact_data['vid_fullpath'] = $vid_fullpath_array[$key];
                    $contact_data['is_transition'] = $vid_transition_status[$key];
                    $contact_data['vid_fullsize'] = $vid_size[$key];
                    $contact_data['file_name'] = $_POST['contact_resource_name'][$key];
                    //TODO：添加VIP可下载
                    $contact_data['vip_status'] = $_POST['vip_status'][$key];

                    if($_POST['choose_id'][$key]=='1'){
                        $contact_data['putaway_status'] = 1;
                    }else{
                        $contact_data['putaway_status'] = 0;
                    }
                    if($_POST['resource_type']=='1'){
                        //是否为免费
                        if($_POST['resource_charge_status']=='1'){ //免费(按照大资源是否收费来判断)
                            $contact_data['charge_status']='1';
                            $contact_data['trial_status']='2';
                            $contact_data['trial_time']='0';
                        }else{
                            $contact_data['charge_status']=$_POST['resource_contact_charge_status'][$key]; //上传的文件是否收费的状态
                            //$contact_data['trial_status']=$_POST['resource_trial'][$key]; //是否允许使用1：是2：否
                            //$contact_data['trial_time']=$_POST['browse_time'][$key];
                            if($contact_data['charge_status'] == 1){  //上传的文件是免费的
                                $contact_data['trial_status']='2';   //是否允许使用1：是2：否
                                $contact_data['trial_time']='0';     //试看时间
                            }else{
                                $contact_data['trial_status']=$_POST['resource_trial'][$key]; //是否允许试用1：是2：否
                                if($contact_data['trial_status']==1){   //允许试用
                                    $contact_data['trial_time']=$vider_trial_time_s;
                                    /*$contact_data['trial_time']=$_POST['browse_time'][$key];
                                    if($contact_data['trial_time']!=$vider_trial_time_s){
                                        $ResourceModel->rollback();
                                        $this->error('试用时间规定必须为'.$vider_trial_time_s.'秒');
                                    } //暂时先注释掉，就直接用默认的试用时间就可以*/
                                }else{  //不允许试用
                                    $contact_data['trial_time']='0';
                                }
                            }
                        }
                    }


                    $url = $vid_path_array[$key];
                    $contact_data['resource_path'] = $url;
                    $contact_data['type'] = getFileType($url);
                    if($contact_data['type'] == 'image'){
                        $watermark_image_=$vid_path_array[$key];
                        $arr=explode('.',$watermark_image_);
                        $watermark_image=$arr[count($arr)-2].'_w'.'.'.$arr[count($arr)-1];

                        $contact_data['resource_path'] = $watermark_image;
                        $contact_data['copy_img']=$vid_path_array[$key];
                        $contact_data['watermark_img']=$watermark_image;
                        $contact_data['watermark_status']=1;
                    }
                    if (($insert_id = $contact_model->add($contact_data)) == false) {
                        $ResourceModel->rollback();
                        $this->error('数据提交失败');
                    } else {
                        $ids[] = $insert_id;
                    }
                }



            } elseif(!empty($type)) {
                $vid_path_array = explode(',', $_POST['vid_file_path']);

                //判断有几个文件
                $new_file = array();
                for ($i = 0; $i < count($vid_path_array); $i++) {
                    //这里先插进去,之后再更新
                     if($type == 'image' || getFileType($vid_path_array[$i]) == 'image'){
                        $watermark_image_=$vid_path_array[$i];
                        $arr=explode('.',$watermark_image_);
                        $watermark_image=$arr[count($arr)-2].'_w'.'.'.$arr[count($arr)-1];
                        $contact_data['resource_path'] = $watermark_image;
                        $contact_data['copy_img']=$vid_path_array[$i];
                        $contact_data['watermark_img']=$watermark_image;
                        $contact_data['watermark_status']=1;
                    }else{
                        $contact_data['resource_path'] = $vid_path_array[$i];
                    }

                    if($_POST['choose_id'][$i]=='1'){
                        $contact_data['putaway_status'] = 1;
                    }else{
                        $contact_data['putaway_status'] = 0;
                    }
                    if($_POST['resource_type']=='1'){
                        //是否为免费
                        if($_POST['resource_charge_status']=='1'){ //免费(按照大资源是否收费来判断)
                            $contact_data['charge_status']='1';
                            $contact_data['trial_status']='2';
                            $contact_data['trial_time']='0';
                        }else{
                            $contact_data['charge_status']=$_POST['resource_contact_charge_status'][$i]; //上传的文件是否收费的状态
                            //$contact_data['trial_status']=$_POST['resource_trial'][$key]; //是否允许使用1：是2：否
                            //$contact_data['trial_time']=$_POST['browse_time'][$key];
                            if($contact_data['charge_status'] == 1){  //上传的文件是免费的
                                $contact_data['trial_status']='2';   //是否允许使用1：是2：否
                                $contact_data['trial_time']='0';     //试看时间
                            }else{
                                $contact_data['trial_status']=$_POST['resource_trial'][$i]; //是否允许试用1：是2：否
                                if($contact_data['trial_status']==1){   //允许试用
                                    $contact_data['trial_time']=$vider_trial_time_s;
                                    /*$contact_data['trial_time']=$_POST['browse_time'][$key];
                                    if($contact_data['trial_time']!=$vider_trial_time_s){
                                        $ResourceModel->rollback();
                                        $this->error('试用时间规定必须为'.$vider_trial_time_s.'秒');
                                    } //暂时先注释掉，就直接用默认的试用时间就可以*/
                                }else{  //不允许试用
                                    $contact_data['trial_time']='0';
                                }
                            }
                        }
                    }
                    //TODO：添加VIP可下载
                    $contact_data['vip_status'] = $_POST['vip_status'][$i];
                    $contact_data['file_name'] = $_POST['contact_resource_name'][$i];

                    if (($insert_id = $contact_model->add($contact_data)) == false) {
                        $ResourceModel->rollback();
                        $this->error('数据提交失败');
                    } else {
                        $ids[] = $insert_id;
                    }


                }
            }

            $data['resource_type'] = $_POST['resource_type'];
            $data['source'] = $_POST['source'];
            $data['name'] = ($_POST['name']);
            $data['creation_year'] = $_POST['creation_year'];
            $data['author'] = $_POST['author'];
            $data['description'] = ($_POST['description']);
            $data['putaway_time'] = time();
            //TODO:添加获奖作品关联资源ID
            $data['activity_work_id'] = $_POST['activity_work_id'];
            //多个知识点
            foreach (array_unique($_POST['arr']) as $items){
                $temp[] = explode(',',$items);
            }

            $data['file_type'] = $_POST['type'];
            $data['create_at'] = time();

            if ($_POST['is_allowed_share'] == NULL)
                $data['is_allowed_share'] = 0;
            else
                $data['is_allowed_share'] = 1;
            if ($_POST['is_allowed_download'] == 1) {
                $data['is_allowed_download'] = 1;
            } else {
                $data['is_allowed_download'] = 0;
            }

            //是否允许APP缓存
            if ($_POST['is_allowed_app_download'] == 1) {
                $data['is_allowed_app_download'] = 1;
            } else {
                $data['is_allowed_app_download'] = 0;
            }

            if ($_POST['putaway_status'] != null) {
                $data['putaway_status'] = 1;
            } else {
                $data['putaway_status'] = 0;
            }

            if($_POST['resource_type']=='1'){
                if($_POST['resource_charge_status']=='2'){
                    if($_POST['resource_price']==''){
                        $this->error('缺少价格参数');
                    }else{
                        if($_POST['promote_price']!='' && $_POST['promote_price']!=0){
                            if($_POST['resource_price']<$_POST['promote_price']){
                                $this->error('优惠价格不能大于原价');
                            }else{
                                $data['promote_price']=$_POST['promote_price'];
                                $data['real_price']=$_POST['promote_price'];
                            }
                        }else{
                             $data['real_price']=$_POST['resource_price'];
                            $data['promote_price']='0';
                        }
                    }
                    $data['resource_price']=$_POST['resource_price'];
                    $data['charge_type']=$_POST['resource_time'];
                    $data['charge_status']=$_POST['resource_charge_status'];
                    if($_POST['resource_time']=='1'){
                        $data['charge_time']=3600*24*360*10;
                    }elseif($_POST['resource_time']=='2'){
                        $data['charge_time']=3600*24*180;
                    }else{
                        $data['charge_time']=3600*24*90;
                    }
                }else{
                    $data['promote_price']='0';
                    $data['real_price']='0';
                    $data['resource_price']='0';
                    $data['charge_time']='0';
                }
            }

            if ($data['resource_type'] == 2 || $data['resource_type'] == 3 || $data['resource_type'] == 4 ) {
                $data['file_type'] = 'html';
            }


            if (!$insert_id = $ResourceModel->add($data)) {
                $ResourceModel->rollback();
                $this->error('数据提交失败');
            }

            $temp_value_publishing_arr=array_column($temp,0);
            $temp_value_publishing_arr=array_unique($temp_value_publishing_arr);
            if(count($temp_value_publishing_arr)>1){
                $this->error('知识点只能关联到一个教材版本');
            }

            $temp_value_course_arr=array_column($temp,1);
            $temp_value_course_arr=array_unique($temp_value_course_arr);
            if(count($temp_value_course_arr)>1){
                $this->error('知识点只能关联到一个学科');
            }

            $this->model = D('Knowledge_resource');

            if ($data['source'] == 1) {
                $data['source_name'] = '教师资源分享';
            }
            if ($data['source'] == 2) {
                $data['source_name'] = '京版活动获奖设计';
            }

            if ($data['resource_type'] == 1) {
                $data['resource_type_name'] = '京版资源';
            }

            if ($data['resource_type'] == 2) {
                $data['resource_type_name'] = 'nobook';
            }

            if ($data['resource_type'] == 3) {
                $data['resource_type_name'] = '万邦华堂资源';
            }

            if ($data['resource_type'] == 4) {
                $data['resource_type_name'] = '京版资源网页';
            }
            //增加资源类型关键字
            $typeNames = D('Knowledge_type')->getGroupTypeName($_POST['types']);
            $data['resource_type_name'] .= ','.$typeNames;
            //批量插入多知识点表
            foreach ($temp as $temp2){
                $temp_data['publishing_house_id'] = $temp2[0];
                $temp_data['course'] = $temp2[1];
                $temp_data['grade'] = $temp2[2];
                $temp_data['textbook'] = $temp2[3];
                $temp_data['chapter'] = $temp2[4];
                $temp_data['festival'] = $temp2[5];
                $temp_data['knowledge'] = $temp2[6];
                $temp_data['child_knowledge'] = $temp2[7];
                $temp_data['knowledge_resource_id'] = $insert_id;

                $datas = $this->model->get_some_resource($temp_data['publishing_house_id'],$temp_data['grade'], $temp_data['course'], $temp_data['textbook'], $temp_data['chapter'], $temp_data['festival'], $temp_data['knowledge'], $temp_data['child_knowledge'], $data['name'],$data['creation_year'],$data['source_name'],$data['author'],$data['resource_type_name'],$data['description']);

                $temp_data['knowledge_info'] = $datas['links'];
                $temp_data['knowledge_info_point'] = $datas['linkspoint'];
                if (!$insert_ids = $knowledge_resource_point->add($temp_data)) {
                    $ResourceModel->rollback();
                    $this->error('数据提交失败');
                }
            }

            if(!empty($ids)){
                $string = implode(',', $ids);
                $string = '(' . $string . ')';
                $contact_after_data['resource_id'] = $insert_id;
                $contact_model->where('id in ' . $string)->save($contact_after_data);

            }


            //nobook和万邦华堂的URL插入
            if ($_POST['url']) {
                $urls['resource_path'] = $_POST['url'];
                $urls['resource_id'] = $insert_id;
                if ($contact_model->add($urls) == false) {
                    $ResourceModel->rollback();
                    $this->error('数据提交失败');
                }
            }

            //$knowledge_resource_attr->startTrans();
            //$knowledge_resource_type_contact->startTrans();
            $where['resource_id'] = $insert_id;
            $results['resource_id'] = $insert_id;
            $tips['resource_id'] = $insert_id;

            //插入资源属性关联表
            if (count($_POST['types']) >= 1) {
                foreach ($_POST['types'] as $item) {
                    $results['type_id'] = $item;
                    if ($knowledge_resource_type_contact->add($results) == false) {
                        $ResourceModel->rollback();
                        $this->error('数据操作异常');
                    }
                }
            } else {
                $result['type_id'] = $_POST['types']['0'];
                if ($knowledge_resource_type_contact->add($results) == false) {
                    $ResourceModel->rollback();
                    $this->error('数据操作异常');
                }
            }

            //插入资源栏目关联表
            if (count($_POST['column_ids']) > 1) {
                foreach ($_POST['column_ids'] as $tmp) {
                    $tips['column_id'] = $tmp;
                    if ($knowledge_resource_attr->add($tips) == false) {
                        $ResourceModel->rollback();
                        $this->error('数据操作异常');
                    }
                }
            } else {
                $tips['column_id'] = $_POST['column_ids']['0'];
                if ($knowledge_resource_attr->add($tips) == false) {
                    $ResourceModel->rollback();
                    $this->error('数据操作异常');
                }
            }


            $ResourceModel->commit();
            //更新knowledge_count字段
            A('Admin/Common')->updateKnowledge_count();
            if( ($type == 'ppt' || $type == 'word' ||$type == 'pdf' || $type == 'swf') && ( empty($_FILES['pc_cover']['name']) &&  empty($_FILES['mobile_cover']['name'])) )
              $this->refreshResourceCoverAsync($insert_id,$vid_path_array[0]);
            else if($type == 'image' && ( empty($_FILES['pc_cover']['name']) &&  empty($_FILES['mobile_cover']['name']))){
              //生成图片资源封面
              $param = ['id' => $insert_id];
              $url = '/index.php?m=Home&c=Watermark&a=refreshKnowledgeCover&token=2579';
              $host = $_SERVER['SERVER_NAME'];
              $this->curl_async_post($host,$url,$param);
            }
            //ppt的上传操作
            $result = $contact_model->where($where)->field('id,resource_path')->select();
            if ('ppt' == $type)
                for ($i = 0; $i < count($result); $i++) {
                    $pushMQData = array($result[$i]['id'], $insert_id, $result[$i]['resource_path'], OSS_URL, 'bjresource');
                    processMQMessage(CONVERTPPT_EX_NAME, K_ROUTE, 'push', implode(' ', $pushMQData));//消息队列操作
                }
            $this->redirect("Jbresources/jbresources");

        } else {
            /*$Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();

            $Model = M('dict_grade');
            $grades = $Model->select();*/
            $columns = M('dict_column');
            $column_all = $columns->where('is_display=1 and module_name=\'京版资源\'')->select();
            $this->assign('column_all', $column_all);

            $this->assign('video_trial_time',C('TRIAL_TIME'));
//            $this->assign('courses', $courses);
//            $this->assign('grades', $grades);
            $this->display('resourceUpload');
        }
    }

    private function __base64SaveJPG($base64String='')
    {
        $imgData = substr($base64String,strpos($base64String,",") + 1);
        $decodedData = base64_decode($imgData);
        $randCode = rand(10000,99999);
        $fileName = "Public/tmp/$randCode.jpg";
        file_put_contents($fileName,$decodedData );

        return ['tmp_name'=>$fileName,'type'=>'image/jpeg','size'=>filesize($fileName)];
    }

    private function __uploadCover($dataUrl)
    {
        $file = $this->__base64SaveJPG($dataUrl);
        //upload file to oss
        $uploadResult = $this->upload_file2($file);
        @unlink($file['tmp_name']);
        if(empty($uploadResult))
            return false;
        return $uploadResult;
    }

    //编辑京版资源
    function editJBResource($isDisplay=0)
    {
        if (!session('?admin')) redirect(U('Login/login'));
        if ($_POST) {

            $global_tag = 0;
            $check['id'] = $_POST['id'];
            if (!$check['id']) {
                $this->error('错误参数');
            }

            $type = $_POST['type'];
            $id = $check['id'];
            $existed_resource = $_POST['hidden_resource'];

            $ResourceModel = M('knowledge_resource');
            $contact_model = M('knowledge_resource_file_contact');
            $ResourceModel->startTrans();

            $_POST['name']=  trim($_POST['name']);
            $_POST['description']=  trim($_POST['description']);

            //write contact backup information

            //check old status
            define('PUTAWAY',1);
            define('APPROVE',1);
            $oldStatus = $ResourceModel->where('id='.$check['id'])->field('status,putaway_status')->find();
            if($oldStatus['status'] == APPROVE) {
                $deleteResult = M('knowledge_resource_file_contact_backup')->where("resource_id=" . $check['id'])->delete();
                if (false === $deleteResult) {
                    $ResourceModel->rollback();
                    $this->error('备份数据删除失败');
                }
                $insertResult = M()->execute('INSERT knowledge_resource_file_contact_backup SELECT * FROM knowledge_resource_file_contact WHERE resource_id=' . $check['id']);
                if (false === $insertResult) {
                    $ResourceModel->rollback();
                    $this->error('备份数据增加失败');
                }
            }
            //这里判断那个已经有的资源是否为空,清除不等于这些ID的数据
            if (count($existed_resource) > 0) {
                $resource_ids = implode(',', $existed_resource);
                $resource_ids_string = "(" . $resource_ids . ")";

                if ($contact_model->where("resource_id=" . $check['id'] . " and id not in " . $resource_ids_string)->delete() === false) {
                    $ResourceModel->rollback();
                    $this->error('数据提交失败1');
                }
            } else {
                if ($contact_model->where("resource_id=" . $check['id'])->delete() === false) {
                    $ResourceModel->rollback();
                    $this->error('数据提交失败2');
                }
            }
            //这里修改选中的
            if(!empty($_POST['exists_choose_id'])) {
                //这里先把所有从表资源上架状态改了
                $con_temp_data['putaway_status']=0;
                $contact_model->where('resource_id=' . $check['id'])->save($con_temp_data);

                $contact_result = $contact_model->where('resource_id=' . $check['id'])->field('id,putaway_status')->select();
                foreach($contact_result as $c_key=>$c_val){
                    if(in_array($c_val['id'],$_POST['hidden_resource'])){
                        $contactData['putaway_status']=$_POST['exists_choose_id'][$c_key];
                        $contactData['charge_status']=$_POST['exists_resource_contact_charge_status'][$c_key];
                        $contactData['trial_status']=$_POST['exists_resource_trial'][$c_key];
                        $contactData['trial_time']=$_POST['exists_browse_time'][$c_key];
                        //TODO:添加VIP可下载
                        $contactData['vip_status'] = $_POST['vip_status'][$c_key];
                        if($contact_model->where('id='.$c_val['id'])->save($contactData) === false){
                            $ResourceModel->rollback();
                            $this->error('数据提交失败2');
                        }
                    }
                }
            }//die;
            //修改文件名
            if(!empty($_POST['contact_resource_name_choose'])) {
                //这里先把所有从表资源上架状态改了
                $contact_result = $contact_model->where('resource_id=' . $check['id'])->field('id')->select();
                foreach($contact_result as $c_key=>$c_val){
                    if(in_array($c_val['id'],$_POST['hidden_resource'])){
                        $contactData = array();
                        $contactData['file_name']=$_POST['contact_resource_name_choose'][$c_key];
                        if($contact_model->where('id='.$c_val['id'])->save($contactData) === false){
                            $ResourceModel->rollback();
                            $this->error('数据提交失败2');
                        }
                    }
                }
            }


            $knowledge_resource_attr = M('knowledge_resource_attr');
            $knowledge_resource_type_contact = M('knowledge_resource_type_contact');

            //获取pc封面图片、手机封面图片OSS地址
            if(!empty($_POST['pc_cover_dataurl'])) {
                $uploadResult = $this->__uploadCover($_POST['pc_cover_dataurl']);
                if ($uploadResult === false) {
                    $ResourceModel->rollback();
                    $this->error('上传PC封面失败,请重试');
                }
                $data['pc_cover'] = $uploadResult;
            }
            if(!empty($_POST['mobile_cover_dataurl'])) {
                $uploadResult = $this->__uploadCover($_POST['mobile_cover_dataurl']);
                if ($uploadResult === false) {
                    $ResourceModel->rollback();
                    $this->error('上传APP封面失败,请重试');
                }
                $data['mobile_cover'] = $uploadResult;
            }
//            if ($_FILES['pc_cover']['name'] || $_FILES['mobile_cover']['name']) {
//                if ($_FILES['pc_cover']['name'] && $_FILES['mobile_cover']['name']) {
//                    $arr1 = $this->upload_file2($_FILES['pc_cover']);
//                    $arr2 = $this->upload_file2($_FILES['mobile_cover']);
//                    $data['pc_cover'] = $arr1;
//                    $data['mobile_cover'] = $arr2;
//                } elseif ($_FILES['pc_cover']['name']) {
//                    $arr = $this->upload_file2($_FILES['pc_cover']);
//                    $data['pc_cover'] = $arr;
//                } else {
//                    $arr = $this->upload_file2($_FILES['mobile_cover']);
//                    $data['mobile_cover'] = $arr;
//                }
//            }

            $vider_trial_time_s=C('TRIAL_TIME');
            if ($_POST['vid_file_path'] != '') {
                if ($type == 'video' || $type == 'audio' ||  $type == 'mixed') {
                    $vid_array = explode(',', $_POST['vid']);
                    $vid_path_array = explode(',', $_POST['vid_file_path']);
                    $playerwidth = explode(',', $_POST['playerwidth']);
                    $playerduration = explode(',', $_POST['playerduration']);
                    $vid_fullpath_array = explode(',', $_POST['vid_fullpath']);
                    $vid_image_path_array = explode(',', $_POST['vid_image_path']);
                    $vid_transition_status = explode(',', $_POST['is_transition']);
                    $vid_size = explode(',', $_POST['vid_fullsize']);
                    $url_arr = array();
                    foreach ($vid_array as $key => $v) {

                        $contact_data['resource_id'] = $id;
                        $contact_data['vid'] = $v;
                        $contact_data['playerwidth'] = $playerwidth[$key];
                        $contact_data['playerduration'] = $playerduration[$key];
                        $contact_data['vid_image_path'] = $vid_image_path_array[$key];
                        $contact_data['vid_fullpath'] = $vid_fullpath_array[$key];
                        $contact_data['is_transition'] = $vid_transition_status[$key];
                        $contact_data['file_name'] = $_POST['contact_resource_name'][$key];
                        $contact_data['vid_fullsize'] = $vid_size[$key];
                        //TODO:添加VIP可下载
                        $contact_data['vip_status'] = $_POST['vip_status'][$key];

                        if($_POST['choose_id'][$key]=='1'){
                            $contact_data['putaway_status'] = 1;
                        }else{
                            $contact_data['putaway_status'] = 0;
                        }
                        if($_POST['resource_type']=='1'){
                            //是否为免费
                            if($_POST['resource_charge_status']=='1'){ //免费(按照大资源是否收费来判断)
                                $contact_data['charge_status']='1';
                                $contact_data['trial_status']='2';
                                $contact_data['trial_time']='0';
                            }else{
                                $contact_data['charge_status']=$_POST['resource_contact_charge_status'][$key]; //上传的文件是否收费的状态
                                //$contact_data['trial_status']=$_POST['resource_trial'][$key]; //是否允许使用1：是2：否
                                //$contact_data['trial_time']=$_POST['browse_time'][$key];
                                if($contact_data['charge_status'] == 1){  //上传的文件是免费的
                                    $contact_data['trial_status']='2';   //是否允许使用1：是2：否
                                    $contact_data['trial_time']='0';     //试看时间
                                }else{
                                    $contact_data['trial_status']=$_POST['resource_trial'][$key]; //是否允许试用1：是2：否
                                    if($contact_data['trial_status']==1){   //允许试用
                                        $contact_data['trial_time']=$vider_trial_time_s;
                                        /*$contact_data['trial_time']=$_POST['browse_time'][$key];
                                        if($contact_data['trial_time']!=$vider_trial_time_s){
                                            $ResourceModel->rollback();
                                            $this->error('试用时间规定必须为'.$vider_trial_time_s.'秒');
                                        } //暂时先注释掉，就直接用默认的试用时间就可以*/
                                    }else{  //不允许试用
                                        $contact_data['trial_time']='0';
                                    }
                                }
                            }
                        }

                        $url = $vid_path_array[$key];
                        $contact_data['type'] = getFileType($url);
                        $contact_data['resource_path'] = $url;
                        if($contact_data['type'] == 'image'){
                            $watermark_image_=$vid_path_array[$key];
                            $arr=explode('.',$watermark_image_);
                            $watermark_image=$arr[count($arr)-2].'_w'.'.'.$arr[count($arr)-1];

                            $contact_data['resource_path'] = $watermark_image;
                            $contact_data['copy_img']=$vid_path_array[$key];
                            $contact_data['watermark_img']=$watermark_image;
                            $contact_data['watermark_status']=1;
                        }



                        if (($insert_id = $contact_model->add($contact_data)) == false) {
                            $ResourceModel->rollback();
                            $this->error('数据提交失败');
                        } else {
                            $global_tag = 1;
                        }
                    }
                } else {
                    $vid_path_array = explode(',', $_POST['vid_file_path']);
                    //判断有几个文件
                    for ($i = 0; $i < count($vid_path_array); $i++) {
                        //这里入库
                        $contact_data['resource_id'] = $id;
                        if($type == 'image' || getFileType($vid_path_array[$i]) == 'image' ){
                            $watermark_image_=$vid_path_array[$i];
                            $arr=explode('.',$watermark_image_);
                            $watermark_image=$arr[count($arr)-2].'_w'.'.'.$arr[count($arr)-1];

                            $contact_data['resource_path'] = $watermark_image;
                            $contact_data['copy_img']=$vid_path_array[$i];
                            $contact_data['watermark_img']=$watermark_image;
                            $contact_data['watermark_status']=1;
                        }else{
                            $contact_data['resource_path'] = $vid_path_array[$i];
                        }

                        //TODO:添加VIP可下载
                        $contact_data['vip_status'] = $_POST['vip_status'][$i];

                        if($_POST['choose_id'][$i]=='1'){
                            $contact_data['putaway_status'] = 1;
                        }else{
                            $contact_data['putaway_status'] = 0;
                        }
                        if($_POST['resource_type']=='1'){
                            //是否为免费
                            if($_POST['resource_charge_status']=='1'){ //免费(按照大资源是否收费来判断)
                                $contact_data['charge_status']='1';
                                $contact_data['trial_status']='2';
                                $contact_data['trial_time']='0';
                            }else{
                                $contact_data['charge_status']=$_POST['resource_contact_charge_status'][$i]; //上传的文件是否收费的状态
                                //$contact_data['trial_status']=$_POST['resource_trial'][$key]; //是否允许使用1：是2：否
                                //$contact_data['trial_time']=$_POST['browse_time'][$key];
                                if($contact_data['charge_status'] == 1){  //上传的文件是免费的
                                    $contact_data['trial_status']='2';   //是否允许使用1：是2：否
                                    $contact_data['trial_time']='0';     //试看时间
                                }else{
                                    $contact_data['trial_status']=$_POST['resource_trial'][$i]; //是否允许试用1：是2：否
                                    if($contact_data['trial_status']==1){   //允许试用
                                        $contact_data['trial_time']=$vider_trial_time_s;
                                        /*$contact_data['trial_time']=$_POST['browse_time'][$key];
                                        if($contact_data['trial_time']!=$vider_trial_time_s){
                                            $ResourceModel->rollback();
                                            $this->error('试用时间规定必须为'.$vider_trial_time_s.'秒');
                                        } //暂时先注释掉，就直接用默认的试用时间就可以*/
                                    }else{  //不允许试用
                                        $contact_data['trial_time']='0';
                                    }
                                }
                            }
                        }

                        $contact_data['file_name'] = $_POST['contact_resource_name'][$i];
                        if (($contact_model->add($contact_data)) == false) {
                            $ResourceModel->rollback();
                            $this->error('数据提交失败4');
                        }
                        $global_tag = 1;
                    }
                }
            }


            $data['resource_type'] = $_POST['resource_type'];
            $data['source'] = $_POST['source'];
            $data['name'] = ($_POST['name']);
            $data['creation_year'] = $_POST['creation_year'];
            $data['author'] = $_POST['author'];
            $data['description'] = ($_POST['description']);
            //TODO:这里添加获奖作品的关联ID
            $data['activity_work_id'] = $_POST['activity_work_id'];

            //拼接knowledge_info字段内容
            $this->model = D('Knowledge_resource');


            $data['file_type'] = $_POST['type'];
            $data['status'] = 0;

            $data['create_at'] = time();
            if ($_POST['is_allowed_share'] == NULL)
                $data['is_allowed_share'] = 0;
            else
                $data['is_allowed_share'] = 1;
            if ($_POST['is_allowed_download'] == 1) {
                $data['is_allowed_download'] = 1;
            } else {
                $data['is_allowed_download'] = 0;
            }

            //是否允许APP缓存
            if ($_POST['is_allowed_app_download'] == 1) {
                $data['is_allowed_app_download'] = 1;
            } else {
                $data['is_allowed_app_download'] = 0;
            }

            if ($_POST['putaway_status'] != null) {
                $data['putaway_status'] = 1;
            } else {
                $data['putaway_status'] = 0;
            }

            if($_POST['resource_type']=='1'){
                if($_POST['resource_charge_status']=='2'){
                    if($_POST['resource_price']==''){
                        $this->error('缺少价格参数');
                    }else{
                        if($_POST['promote_price']!='' && $_POST['promote_price']!=0){
                            if($_POST['resource_price']<$_POST['promote_price']){
                                $this->error('优惠价格不能大于原价');
                            }else{
                                $data['promote_price']=$_POST['promote_price'];
                                $data['real_price']=$_POST['promote_price'];
                            }
                        }else{
                             $data['real_price']=$_POST['resource_price'];
                            $data['promote_price']='0';
                        }
                    }
                    $data['resource_price']=$_POST['resource_price'];
                    if($_POST['resource_time']=='1'){
                    $data['charge_time']=3600*24*360*10;
                    }elseif($_POST['resource_time']=='2'){
                        $data['charge_time']=3600*24*180;
                    }else{
                        $data['charge_time']=3600*24*90;
                    }
                }else{
                    $data['promote_price']='0';
                    $data['real_price']='0';
                    $data['resource_price']='0';
                    $data['charge_time']='0';
                }
            }

            if ($data['resource_type'] == 2 || $data['resource_type'] == 3 || $data['resource_type'] == 4) {
                $data['file_type'] = 'html';
            }

            $data['charge_status']=$_POST['resource_charge_status'];
            $data['charge_type']=$_POST['resource_time'];

            if (!$ResourceModel->where("id=$id")->save($data)) {
                $ResourceModel->rollback();
                $this->error('数据提交失败');
            }

            //nobook和万邦华堂的URL插入
            if ($_POST['url']) {
                $deletes['resource_id'] =  $id;
                $urls['resource_path'] = $_POST['url'];
                $urls['resource_id'] = $id;
                //先删除，后新增
                if($contact_model->where($deletes)->delete() == false){
                    $ResourceModel->rollback();
                    $this->error('数据提交失败');
                }
                if ($contact_model->add($urls) == false) {
                    $ResourceModel->rollback();
                    $this->error('数据提交失败');
                }
            }

            $where['resource_id'] = $id;
            $results['resource_id'] = $id;
            $tips['resource_id'] = $id;

            //插入资源属性关联表
            if ($_POST['types']) {
                //先删除以已经关联的数据
                if ($knowledge_resource_type_contact->where($results)->delete() === false) {
                    $ResourceModel->rollback();
                    $this->error('数据操作异常1');
                }
                //再添加数据
                if (count($_POST['types']) > 1) {
                    foreach ($_POST['types'] as $item) {
                        $results['type_id'] = $item;
                        if ($knowledge_resource_type_contact->add($results) ===false) {
                            $ResourceModel->rollback();
                            $this->error('数据操作异常2');
                        }
                    }
                } else {
                    $results['type_id'] = $_POST['types']['0'];
                    if ($knowledge_resource_type_contact->add($results) === false) {
                        $ResourceModel->rollback();
                        $this->error('数据操作异常3');
                    }
                }
            }

            //插入资源栏目关联表
            if ($_POST['column_ids']) {
                //先删除以已经关联的数据
                if ($knowledge_resource_attr->where($tips)->delete() === false) {
                    $ResourceModel->rollback();
                    $this->error('数据操作异常4');
                }
                //再添加数据
                if (count($_POST['column_ids']) > 1) {
                    foreach ($_POST['column_ids'] as $tmp) {
                        $tips['column_id'] = $tmp;
                        if ($knowledge_resource_attr->add($tips) === false) {
                            $ResourceModel->rollback();
                            $this->error('数据操作异常5');
                        }
                    }
                } else {
                    $tips['column_id'] = $_POST['column_ids']['0'];
                    if ($knowledge_resource_attr->add($tips) === false) {
                        $ResourceModel->rollback();
                        $this->error('数据操作异常6');
                    }
                }
            }

            //插入多知识点表

            if($_POST['arr']){

                if ($data['source'] == 1) {
                    $data['source_name'] = '教师资源分享';
                }
                if ($data['source'] == 2) {
                    $data['source_name'] = '京版活动获奖设计';
                }

                if ($data['resource_type'] == 1) {
                    $data['resource_type_name'] = '京版资源';
                }

                if ($data['resource_type'] == 2) {
                    $data['resource_type_name'] = 'nobook';
                }

                if ($data['resource_type'] == 3) {
                    $data['resource_type_name'] = '万邦华堂资源';
                }

                if ($data['resource_type'] == 4) {
                    $data['resource_type_name'] = '京版资源网页';
                }
                //增加资源类型关键字
                $typeNames = D('Knowledge_type')->getGroupTypeName($_POST['types']);
                $data['resource_type_name'] .= ','.$typeNames;
                foreach ($_POST['arr'] as $items){
                    $str1 = explode('|',$items);    //var_dump($str1);    echo "<hr>";
                    $str_ids[] = explode(',',$str1[0]);
                    $str_id[] = explode('_',$str1[1]);
                    //过滤请选择操作
                    $temp_arr = explode(',',$str1[2]);
                    $new_temp = array();
                   foreach ($temp_arr as $temp){
                       if($temp !== "-请选择-"){
                            array_push($new_temp,$temp);
                       }
                   }
                    $str_info[] = implode(',',$new_temp);
                }
                $str_publishing = array_column($str_ids,0);//教材版本
                $str_publishing_vari=array_unique($str_publishing);
                if(count($str_publishing_vari)>1){
                    $this->error('知识点只能关联到一个教材版本!');
                }
                $str_course = array_column($str_ids,1);//学科
                $str_course_vari=array_unique($str_course);
                if(count($str_course_vari)>1){
                    $this->error('知识点只能关联到一个学科!');
                }
                $str_grade = array_column($str_ids,2);//年级
                $str_textbook = array_column($str_ids,3);//教材分册
                $str_chapter = array_column($str_ids,4);//关联章/单元
                $str_festival = array_column($str_ids,5);//关联节/课
                $str_knowledge = array_column($str_ids,6);//关联知识点1
                $str_child_knowledge = array_column($str_ids,7);//关联知识点2
                $str_id = array_column($str_id,1);//知识点id

                $addResult = D('Knowledge_resource')->point($_POST['id'],$str_publishing,$str_course,$str_grade,$str_textbook,$str_chapter,$str_festival,$str_knowledge,$str_child_knowledge,$str_id,$str_info, $data['name'],$data['creation_year'],$data['source_name'],$data['author'],$data['resource_type_name'],$data['description'] );

                if (!$addResult) {
                    $ResourceModel->rollback();
                }
            }

             if($type == 'image' && ( empty($_FILES['pc_cover']['name']) &&  empty($_FILES['mobile_cover']['name']))){
                $param = ['id' => $id];
                $url = '/index.php?m=Home&c=Watermark&a=refreshKnowledgeCover';
                $host = $_SERVER['SERVER_NAME'];
                $this->curl_async_post($host,$url,$param);
            }
            $ResourceModel->commit();
            //更新knowledge_count字段
            A('Admin/Common')->updateKnowledge_count();

            $this->refreshRecommendResource($id);

            $this->redirect("Jbresources/jbresources");
        } else {
            $this->assign('module', '京版资源');
            $this->assign('nav', '京版资源');
            $this->assign('subnav', '修改资源');

            $id = $_GET['id'];
            if (!$id) {
                $this->error('错误参数');
            }

            $Model = M('dict_course_copy_resource');
            $courses = $Model->order('sort_order asc')->select();

            $Model = M('dict_grade');
            $grades = $Model->select();

            $this->assign('courses', $courses);
            $this->assign('grades', $grades);

            //获取教材版本
            $publishingDate = D('Knowledge_resource')->publishing();
            $this->assign('publishingDate',$publishingDate);

            //获取资源类型（课件，微课）
            $this->model = D('Knowledge_type');
            $data1 = $this->model->get_resources_all();
            //var_dump($data1);die;
            $this->assign('data', $data1);

            //当前资源所关联的知识点
            $wheres['knowledge_resource_id'] = $id;
            $knowledge_resource_point = M('knowledge_resource_point')->where($wheres)->select();

            $this->assign('knowledge_resource_point',$knowledge_resource_point);      //echo "<pre>";print_r($knowledge_resource_point);  die;

            //获取所有数据从资源表中
            $Model = D('Knowledge_resource');
            $one_resource = $Model->getResourceOne($id);
            $this->assign('id', $id);
            $this->assign('content', $one_resource);

            //资源类型的选中状态
            //获取资源类型
            $knowledge_resource_type = M('knowledge_resource_type_contact');
            $type_value['resource_id'] = $id;
            $type_ids = $knowledge_resource_type->where($type_value)
                ->field('type_id')
                ->select();
            $types = array_column($type_ids, 'type_id');
            $this->assign('types', $types);

            //所选中栏目
            $knowledge_resource_attr = M('knowledge_resource_attr');
            $attr['resource_id'] = $id;
            $attrs = $knowledge_resource_attr->where($attr)
                ->select();
            $attrs = array_column($attrs, 'column_id');
            //var_dump($attrs);die;
            $this->assign('attrs', $attrs);

            //所有栏目
            $columns = M('dict_column');
            $column_all = $columns->where('is_display=1 and module_name=\'京版资源\'')->select();
            $this->assign('column_all', $column_all);

            //获取分册
            $check['course_id'] = $one_resource['course'];
            $check['grade_id'] = $one_resource['grade'];
            $this->model = D('Knowledge_resource');
            $textbook_result = $this->model->get_textbooks($check);
            $this->assign('textbook_result', $textbook_result);

            //获取章
            $check['textbook_id'] = $one_resource['textbook'];
            $check['level'] = '1';
            $this->model = D('Knowledge_point');
            $data = $this->model->get_resources($check);
            $this->assign('chapter', $data);

            //获取节
            $check1['parent_id'] = $one_resource['chapter'];
            $check1['level'] = '2';
            $this->model = D('Knowledge_point');
            $festival = $this->model->get_resources($check1);
            $this->assign('festival', $festival);

            //获取知识点一
            $check2['parent_id'] = $one_resource['festival'];
            $check2['level'] = '3';
            $this->model = D('Knowledge_point');
            $knowledge = $this->model->get_resources($check2);
            $this->assign('knowledge', $knowledge);

            //获取知识点二
            $check3['parent_id'] = $one_resource['knowledge'];
            $check3['level'] = '4';
            $this->model = D('Knowledge_point');
            $child_knowledge = $this->model->get_resources($check3);
            $this->assign('child_knowledge', $child_knowledge);

            $bjResource_oss_path = C('oss_path');
            $this->assign('real_file_path', $bjResource_oss_path);

            $Model = M('knowledge_resource');
            $resource_list = $Model->where("knowledge_resource.id=" . $id)->join("knowledge_resource_file_contact on knowledge_resource.id=knowledge_resource_file_contact.resource_id")
                ->field("knowledge_resource_file_contact.type filetype,knowledge_resource_file_contact.id,knowledge_resource.file_type,knowledge_resource_file_contact.resource_path,knowledge_resource_file_contact.vid,knowledge_resource_file_contact.file_name,vid_fullpath,
                knowledge_resource_file_contact.vid_image_path,knowledge_resource_file_contact.putaway_status,knowledge_resource_file_contact.charge_status,knowledge_resource_file_contact.trial_status,knowledge_resource_file_contact.trial_time,vip_status  ")->select();
            $this->assign('resource_list', $resource_list);    // echo "<pre>";print_r($resource_list);die;
            $vals = array();
            //获取上传的资源详情
            foreach ($resource_list as $val) {
                $vals = $val;
            }
            $this->assign('vals', $vals);
            //var_dump($vals);die;

            if($isDisplay == 0)
            $this->display('editJBResource1');
            else
            $this->display();
        }
    }

    private function getDurationOfVideo($hmsString)
    {
        $hms = explode(':',$hmsString);
        list($second,$ms) =  explode('.',$hms[2]);
        return $hms[0]*3600+$hms[1]*60+$second;
    }
    private function bjresource_img_cut_upload()
    {
        $file = $_FILES['file'];
        $data = array();
        //前端框架每次发送一个file过来
        for ($i = 0; $i < count($file['name']); $i++) {
            $suffix = substr($file['name'][$i], strrpos($file['name'][$i], '.') + 1);
            if ($suffix == 'jpg' || $suffix == 'png') {
                $original_file = $file['tmp_name'][$i];
                $targetFile = 'Public/tmp/a.jpg';
                $reshapeResult = A('Admin/Common')->reshapeImage($original_file,$_SERVER['DOCUMENT_ROOT'].'/'.$targetFile,450,364);
                if(1 != $reshapeResult)
                {
                    return '';
                }
                $newPath = 'Public/tmp/a_app.jpg';
                $cutResult = A('Admin/Common')->reshapeImageTo1V1($targetFile,$_SERVER['DOCUMENT_ROOT'].'/'.$newPath);
                if(1 != $cutResult)
                {
                    @unlink($targetFile);
                    return '';
                }
               $temp_file['file'] = array();
               $temp_file['file']['name'][0] = rand(100, 10000) . '.jpg';
               $temp_file['file']['type'][0] = 'image/jpeg';
               $temp_file['file']['tmp_name'][0] = $targetFile;
               $temp_file['file']['error'][0] = 0;
               $temp_file['file']['size'][0] = 4746507;
              if(1 == $cutResult)
              {
                  $temp_file['file']['name'][1] = rand(100, 10000) . '.jpg';
                  $temp_file['file']['type'][1] = 'image/jpeg';
                  $temp_file['file']['tmp_name'][1] = $newPath;
                  $temp_file['file']['error'][1] = 0;
                  $temp_file['file']['size'][1] = 4746507;
              }
              $upload = new \Oss\Ossupload();// 实例化上传类
              $result = $upload->upload(3, $temp_file, 1, 0);
              @unlink($targetFile);
              @unlink($newPath);
              $pathArray = explode(',',$result[1]);
              $picData = array('pc_cover'=>$pathArray[0],'mobile_cover'=>$pathArray[1]);
              $data['video_image'][] = $picData;
                //return $result[1];
            } else {
                return '';
            }
        }
        return $data;
    }
    function bjresource_video_image_upload()
    {//
        $file = $_FILES['file'];
        $data = array();
        //前端框架每次发送一个file过来
        for ($i = 0; $i < count($file['name']); $i++) {
            $suffix = substr($file['name'][$i], strrpos($file['name'][$i], '.') + 1);
            if ($suffix =='mp4' || $suffix =='mov' ||$suffix =='flv' ||$suffix =='wmv' ||$suffix =='avi' ) {
                $original_file = $file['tmp_name'][$i];
                // get duration of video
                exec("/usr/local/ffmpeg/bin/ffmpeg -i $original_file 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//", $output);
                $allSeconds = $this->getDurationOfVideo($output[0]);
                //这里判断视频是否是h264格式的
                exec("/usr/local/ffmpeg/bin/ffprobe -i " . $original_file . " 2>&1", $output);
                $str = implode('', $output);
                if (strpos($str, 'h264') == true) {
                    $is_h264 = 1;
                } else {
                    $is_h264 = 0;
                }
                for($frame=0;$frame<5;$frame++) {
                    $current_file = 'Public/tmp/' . rand(100, 10000) . '.jpg';
                    $newPath = 'Public/tmp/' . rand(100, 10000) . '_m.jpg';
                    //file_put_contents('Public/tmp/aa.txt','33');
                    if($frame < 5)
                       $timeLine = 0.2 * $frame + 0.05;
                    else
                        $timeLine =  round(1.1 + ($frame-3)*(floatval($allSeconds)-1)/10,2);
                    exec("/usr/local/ffmpeg/bin/ffmpeg -ss ".$timeLine." -i " . $original_file . " -y -f image2 -t 0.001 -s 450*364 " . $current_file . " ");
                    $cutResult = A('Admin/Common')->reshapeImageTo1V1($current_file,$_SERVER['DOCUMENT_ROOT'].'/'.$newPath);

                    $temp_file['file'] = array();
                    $temp_file['file']['name'][0] = rand(100, 10000) . '.jpg';
                    $temp_file['file']['type'][0] = 'image/jpeg';
                    $temp_file['file']['tmp_name'][0] = $current_file;
                    $temp_file['file']['error'][0] = 0;
                    $temp_file['file']['size'][0] = 4746507;
                    if(1 == $cutResult)
                    {
                        $temp_file['file']['name'][1] = rand(100, 10000) . '.jpg';
                        $temp_file['file']['type'][1] = 'image/jpeg';
                        $temp_file['file']['tmp_name'][1] = $newPath;
                        $temp_file['file']['error'][1] = 0;
                        $temp_file['file']['size'][1] = 4746507;
                    }
                    try {
                        $upload = new \Oss\Ossupload();// 实例化上传类
                        $result = $upload->upload(3, $temp_file, 1, 0);
                        @unlink($current_file);
                        @unlink($newPath);
                        $pathArray = explode(',', $result[1]);
                        $picData = array('pc_cover' => $pathArray[0], 'mobile_cover' => $pathArray[1]);
                    }catch(\Exception $e){
                        continue;
                    }
                    $data['video_image'][] = $picData;
                }
                $data['is_transition'] = $is_h264;
                return $data;
                //return $result[1];
            } else {
                return '';
            }
        }
    }

    //oss上传
    public function upload_file()
    {
        //ini_set('memory_limit', '-1');
        set_time_limit(0);
        if(!empty($_GET['watermark_status'])){
            $GLOBALS['is_watermark']=1;
        }
        //处理截图
        $video_array = $this->bjresource_video_image_upload();
        if('' == $video_array)
        {
            $video_array = $this->bjresource_img_cut_upload();
        }
        //
        $file_name = $_FILES['file']['name'][0];
        $upload = new \Oss\Ossupload();// 实例化上传类
        $result = $upload->upload(3, $_FILES, 1, 0); //1 pic 2//
        $returnArray = explode(",", rtrim($result[1],','));
        //$returnArray = explode(",", $result[1]);
        $uploadOK = 1;
        for ($i = 0; $i < sizeof($returnArray); $i++) {
            if ($returnArray[$i] == "") {
                $uploadOK = 0;
                break;
            }
        }
        if ($uploadOK == 0) {
            $arr['msg'] = '上传失败';
            $arr['code'] = -1;
        } else {
            $arr['msg'] = '上传成功';
            $arr['code'] = 0;
        }
        $arr['res'] = $result[1];
        $arr['message'] = $result[2];
        $arr['name'] = $file_name;
        $arr['is_transition'] = '';
        $arr['message_video_image'] = '';
        if (is_array($video_array)) {
            $arr['message_video_image'] = $video_array['video_image'];
            $arr['is_transition'] = $video_array['is_transition'];
        }
        echo json_encode($arr);
    }

    //oss上传封面
    public function upload_file2($file=array())
    {
        $_FILES['file'] = $file;
        //$file_name = $_FILES['file']['name'];
        $upload = new \Oss\Ossupload();// 实例化上传类
        $result = $upload->upload(3, $_FILES, 1, 0); //1 pic 2//
        //$returnArray = explode(",", $result[1]);
        return $result['1'];
    }

    //京版本资源下架
    function downJBResource()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $ids = getParameter('id','str');
        $Model = M('knowledge_resource');
        $where['id'] = array('in',$ids);
        $data['putaway_status'] = intval(I('status', 'get'));
        if ($data['putaway_status'] == 1) {
            $data['putaway_time'] = time();
        }

        if ($Model->where($where)->save($data)) {
            echo 1;
        } else {
            echo 0;
        }
        //更新knowledge_count字段
        A('Admin/Common')->updateKnowledge_count();
        //add/delete recommend
        $this->refreshRecommendResource($ids);
    }

    //京版资源审核通过或拒绝
    function reviewJBResource()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $ids = getParameter('id','str');
        $status = I('status');
        $Model = M('knowledge_resource');
        $where['id'] = array('in',$ids);
        $result = $Model->where($where)->field('id,name')->find();
        if (empty($result)) {
            echo 0;
            die;
        }

        if ($status == 1) {
            //审核通过
            $data['status'] = 1;

        } elseif ($status == 2) {
            //审核拒绝
            $data['status'] = 2;
        }
        if ($Model->where($where)->save($data)) {
            echo 1;
        } else {
            echo 0;
        }
        //更新knowledge_count字段
        A('Admin/Common')->updateKnowledge_count();

        $this->refreshRecommendResource($ids);
    }
    public function jbResourceDetails()
    {
        $this->editJBResource(1);
       // $this->display();
    }

    public function refreshResourceCoverAsync($resourceId,$resourcePath)
    {
        $post_data['id']=$resourceId;
        $post_data['path']=$resourcePath;
        $host = $_SERVER['SERVER_NAME'];

        $data = http_build_query($post_data);
        // create connect
        $errno = '';
        $errstr = '';
        $fp = fsockopen($host, 80, $errno, $errstr, 10);
        if(!$fp){
            error_log('fp is no open', 3);
            return false;
        }
        $writeURL = 'http://'.$host .'/index.php?m=Admin&c=Jbresources&a=refreshResourceCover';
        $out = "POST ${writeURL} HTTP/1.1\r\n";
        $out .= "Host:${host}\r\n";
        $out .= "Content-type:application/x-www-form-urlencoded\r\n";
        $out .= "Content-length:".strlen($data)."\r\n";
        $out .= "Connection:close\r\n\r\n";
        $out .= "${data}";
        fputs($fp, $out);
        fclose($fp);
    }

    public function refreshResourceCover()
    {
        $id = getParameter('id','int');
        $path = urldecode(getParameter('path','str'));
        $remoteFilePath = C('oss_path').$path;
        $localFileName = '/tmp/'.basename($remoteFilePath);
        A('Admin/Common')->downloadFile($remoteFilePath,$localFileName);
        if(!is_file($localFileName))
            return ;
        $jpgPath = A('Admin/Common')->convertResource2JPG($localFileName,$remoteFilePath);
        //TODO:cut image
        if(false === $jpgPath)
            return;
        else
            $ossPCCoverPath = A('Admin/Common')->uploadOssFile($jpgPath);

        if(-1 == $ossPCCoverPath)
            return;
        else
        {
            //generate mobile cover
            $newPath = $jpgPath.'_m.jpg';
            $cutResult = A('Admin/Common')->reshapeImageTo1V1($jpgPath,$newPath);
            if(1 !== $cutResult)
                return;
            else {
                $ossMobileCoverPath = A('Admin/Common')->uploadOssFile($newPath);
                //update pc cover and mobile cover
                $data['pc_cover'] = $ossPCCoverPath;
                $data['mobile_cover'] = $ossMobileCoverPath;
                if(false === M('knowledge_resource')->where('id='.$id)->save($data))
                {
                    $this->showMessage(500,$id."转换错误");
                }
                $successId[] = $id;
            }
        }
        $this->showMessage(200,'success',$successId);
    }

    public function exportResourceList()
    {
        $whereCondition = json_decode(urldecode(getParameter('whereCondition','str',false)));
        $result = D('Knowledge_resource')->getExportResourceList($whereCondition);
        $filename=date('Ymd').rand(0,1000).'.csv';
        $header = '资源ID,资源名称,学科,年级,教材分册,资源提供商,资源状态,是否上架,上架时间,观看数,收藏数';
        $this->exportCSV($filename,$header,$result);
    }
    private function exportCSV($name,$header,$data)
    {
        $csv=new CSV();
        $str = '';
        $csvContent = '';
        if(empty($data))
        {
            echo "<script>alert('数据为空,无法导出数据')</script>";
            exit;
        }
        $csvHeader = $header."\r\n";
        foreach($data as $key=> $val)
        {
            $csvContent.= implode(',',array_values($val));
            $csvContent.= "\r\n";
        }
        $str.=$csvHeader . $csvContent;
        $str=iconv('utf-8','gbk', $str);
        $csv->downloadFileCsv($name,$str);
    }

    public function initESIndex()
    {
        // ip restriction
        $REMOTE_ADDR = (isset($_SERVER['LOCAL_ADDR']) && (!empty($_SERVER['LOCAL_ADDR']))) ? $_SERVER['LOCAL_ADDR'] : $_SERVER['SERVER_ADDR'];
        if(get_client_ip() == $REMOTE_ADDR) {
            $esAvailable = getESAvailable();
            if($esAvailable !== ES_AVAILABLE)
                return;
            $model = D('Knowledge_resource');
            $model->deleteIndex();
            $model->initIndex();
            $courseList = D('Dict_course')->getResourceCourseList();
            $courseList = array_merge(array(array('id' => '0')), $courseList);
            $gradeList = D('Dict_grade')->getGradeList();
            $gradeList = array_merge(array(array('id' => '0')), $gradeList);
            $check['id'] = array('in', array(1, 2, 3, 6));

            $columnList['list'] = array(array('id' => '0'));
            for ($k = 0; $k < sizeof($columnList['list']); $k++) {
                $columnId = $columnList['list'][$k]['id'];
                for ($i = 0; $i < sizeof($courseList); $i++) {
                    $courseId = $courseList[$i]['id'];
                    for ($j = 0; $j < sizeof($gradeList); $j++) {
                        $gradeId = $gradeList[$j]['id'];
                        if(false === $model->setWeightSortableMappingKey("w${courseId}_${gradeId}_${columnId}"))
                        {
                            \Think\Log::write("设置 ES Document weight: w${courseId}_${gradeId}_${columnId} 失败",C('LOG_PATH').'ES.ERR');
                            return;
                        }
                    }
                }
            }
        }
    }

    private function refreshRecommendResource($ids)
    {
        //refresh resource sort
        $condition['knowledge_resource.id'] = array('in',$ids);
        $condition['knowledge_resource.putaway_status']=PUTAWAY;
        $condition['knowledge_resource.status']=APPROVE;
        $result = D('Knowledge_resource')->getResourceData($condition,'','','',10000,'',1);

        if(empty($result['data']))
            $action = "remove";
        else
            $action = "add";

        $param = [$action => $ids];
        $url = '/index.php?m=Home&c=BjResource&a=refreshRecommendResourcesOrderIncrement';
        $host = $_SERVER['SERVER_NAME'];
        //A('Home/BjResource')->refreshRecommendResourcesOrderIncrement($param);
        //$this->curl_async_post($host,$url,$param);
        //向消息队列发送更新消息
		try{
         processMQMessage(ESUPDATE_EX_NAME,ESUPDATE_ROUTE,'push',json_encode(['url'=>'http://'.$host.$url,'param'=>$param]));
        }
        catch(\Exception $e){
         $this->curl_async_post($host,$url,$param);
        }
    }

    public function refreshRecommendResourceAll()
    {
        $url = '/index.php?m=Home&c=BjResource&a=refreshRecommendResourcesOrder';
        $host = $_SERVER['SERVER_NAME'];
        try{
         processMQMessage(ESUPDATE_EX_NAME,ESUPDATE_ROUTE,'push',json_encode(['url'=>'http://'.$host.$url,'param'=>[]]));
        }
        catch(\Exception $e){
         $this->curl_async_post($host,$url,[]);
        }

    }
    private function curl_async_post($host,$url,$param)
    {
        $fp = fsockopen($host, 80, $errno, $errstr, 10);
        if(!$fp){
            return false;
        }
        $data = http_build_query($param);
        $out = "POST ${url} HTTP/1.1\r\n";
        $out .= "Host:${host}\r\n";
        $out .= "Content-type:application/x-www-form-urlencoded\r\n";
        $out .= "Content-length:".strlen($data)."\r\n";
        $out .= "Connection:close\r\n\r\n";
        $out .= "${data}";
        fputs($fp, $out);
        fclose($fp);
        return true;
    }

    public function polyVideoCallback()
    {
        $sign=$_GET["sign"];
        $vid=$_GET["vid"];
        $type=$_GET["type"];
        $format=$_GET["format"];
        $df=$_GET["df"];
        $secretkey= "aErb9yxETs"; //在“POLYV后台”——“系统管理”——“视频接口API”中获取

        //获取当前上海标准时间
        date_default_timezone_set("Asia/Shanghai");
        $time = date('Y-m-d H:i:s',time());

        if($type=="upload"){
            $verifySign = md5($type.$vid.$secretkey);
            if($verifySign==$sign){
                //当上传完成时，向数据库插入上传记录

               // mysql_query("insert into upload(vid,type,time) values('{$vid}','{$type}','{$time}')");
            }
        }

        if($type=="encode"){
            $verifySign = md5($type.$format.$vid.$df.$secretkey);
            if($verifySign==$sign){
                //当编码完成时，向数据库插入编码结果
                D('Knowledge_resource')->updateVidTransitionState($vid);
                //mysql_query("insert into encode(vid,type,format,df,time) values('{$vid}','{$type}','{$format}',{$df},'{$time}')");
            }
        }

        if($type=="pass"){
            $verifySign = md5("manage".$type.$vid.$secretkey);
            if($verifySign==$sign){
                //当审核通过时，向数据库插入视频状态
                //mysql_query("insert into pass(vid,type,time) values('{$vid}','{$type}','{$time}')");
            }
        }
    }

    //接收CANVAS数据
    //定时获取REDIS CANVAS
    //获取截图页面
    public function getSnapshotPage()
    {
    }
}
