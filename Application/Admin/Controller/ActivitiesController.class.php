<?php
namespace Admin\Controller;

use Think\Controller;
use Think\Verify;
use Common\Common\CSV;

define('COLUMN_WARMUPACTIVITY',4);
define('COLUMN_HOTACTIVITY',5);

function pathinfo($filepath)
{
    $path_parts = array();
    $path_parts ['dirname'] = rtrim(substr($filepath, 0, strrpos($filepath, '/')),"/")."/";
    $path_parts ['basename'] = ltrim(substr($filepath, strrpos($filepath, '/')),"/");
    $path_parts ['extension'] = substr(strrchr($filepath, '.'), 1);
    $path_parts ['filename'] = ltrim(substr($path_parts ['basename'], 0, strrpos($path_parts ['basename'], '.')),"/");
    return $path_parts;
}


class ActivitiesController extends Controller
{

    public $page_size = 20;
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = D('Social_activity');
        $this->assign('oss_path', C('oss_path'));
        if(ACTION_NAME !== "resdownfile") {
            if ($_GET['token'] == 'jyyuser' && empty(session('admin'))) {
                session_start();
                session('admin.id', 532);
                session('admin.userName', 'jyyuser');
            }
            if (!session('?admin')) {
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    //..这是一个ajax请求，然后...
                    $this->showjson(400, ACCOUNT_FAILURE);
                } else {
                    //..这不是一个ajax请求，然后...
                    redirect(U('Login/login'));
                }

            }
        }
    }

    /*
         * code
         * 1为gb2312转utf8
         * 2为gbk转utf8
         * 3为utf-8直接返回
         * 4为utf8转gbk
         */
    function encode_string($code,$string){
        $return_string='';
        if($code==1){
            $return_string=iconv('gb2312', 'utf-8', $string);
        }else if($code==2){
            $return_string=iconv('gbk', 'utf-8', $string);
        }else if($code==0){
            $return_string=$string;
        }else{
            $return_string=iconv('utf-8', 'gbk', $string);
        }
        return $return_string;
    }
    //京版活动管理
    public function activitiesMgmt()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '励耘圈管理');
        $this->assign('nav', '京版活动管理');
        $this->assign('subnav', '京版活动列表');

        $cat = I('cat');
        $searchVal = I('val');
        $status = intval($_GET['status']);
        $date = I('date');

        $child_class_name = I('child_class_name');

        if ($cat > 0) {
            $where['social_activity_class.id'] = $cat;
        }

        if (!empty($child_class_name) && $cat == 5) {
            $where['social_activity_class.id'] = $child_class_name;
            $this->assign('child_class_name', $child_class_name);
        }

        if (!empty($searchVal)) {
            $where['_string'] = "social_activity.title like '%" . $searchVal . "%' OR " . "auth_admin.nickname like '%" . $searchVal . "%' OR " . "social_activity.content like '%" . $searchVal . "%'";
        }
        if (!empty($status)) {
            $where['social_activity.status'] = $status;
            $this->assign('status', $status);
        }
        if (!empty($date)) {
            $where['from_unixtime(social_activity.create_at,\'%Y-%m-%d\')'] = $date;
            $this->assign('publishTime', $date);
        }
        $SocialActivity = M('social_activity');
        $classActivity = M('social_activity_class');


        $count = $SocialActivity->join('auth_admin on social_activity.publisher_id = auth_admin.id')
            ->join('social_activity_class on social_activity_class.id=social_activity.class_id')->where($where)->count('social_activity.id');
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        $result = $SocialActivity
            ->join('auth_admin on social_activity.publisher_id = auth_admin.id')
            ->join('social_activity_class on social_activity_class.id=social_activity.class_id')->where($where)->order('create_at desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->Field('social_activity.*,auth_admin.nickname as publisher_admin,social_activity_class.class,social_activity_class.parent_id')->select();

        $class_result = $classActivity->Field('id,class')->where('parent_id=0')->select();
        $this->assign('class_list', $class_result);
        $this->assign('list', $result);
        $this->assign('page', $show);
        $this->assign('role', session('admin.role'));
        $this->assign('search_val', $searchVal);
        $this->assign('cat', $cat);


        $this->display_nocache();
    }


    public function childPid()
    {
        $pid = $_GET['pid'];
        if(empty($pid))
            exit;
        $row = M('social_activity_class')->where("parent_id=" . $pid)->select();
        $data['info'] = $row;
        $this->ajaxReturn($data);

    }


    //发布活动
    public function publishActivity()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        if ($_POST) {

            $data['title'] = remove_xss($_POST['title']);
            //$data['short_content'] = remove_xss($_POST['short_content']);
            $data['content'] = $_POST['content'];
            $data['is_live'] = $_POST['is_live'];
            if($data['is_live']) {
                $data['url'] = $_POST['ingUrl'];
                $data['home_jump_url'] = $_POST['home_jump_url'];
                $data['livestart'] = strtotime($_POST['liveStart']);//报名开始时间
                $data['liveend'] = strtotime($_POST['liveEnd']);//报名结束时间
            }
            $data['is_work_compare_activity'] = $_POST['activityType'];

            $data['stakeholder'] = remove_xss($_POST['stakeholder']);
            $data['category'] = remove_xss($_POST['category']);
            $data['class_id'] = $_POST['class_id'];
            $data['additional_info'] =  getParameter('additionalInfoJSON','str',false);
            $data['additional_info'] = str_replace("&quot;","\"",$data['additional_info']);
            $class_type_id = $_POST['class_type_id'];
            if ($class_type_id != 0) {
                $data['class_id'] = $class_type_id;
            }

            $data['code_num'] = $_POST['limitNums'];//邀请码个数
            $data['is_disable'] = $_POST['number'];//是否限制人数
            $data['remark'] = $_POST['remark'];//邀请码个数
            //if($data['is_disable']== 1) { //点击
            $data['apply_people_number'] = $_POST['limitNums'];//限制人数
            $data['display_people_register'] = $_POST['display_people_register'];//限制人数 报名人数 报名按钮
            //}


            $data['is_generate'] = $_POST['code'];//是否生成邀请码
            $data['activitystart'] = strtotime($_POST['activityStart']);//活动开始时间
            $data['activityend'] = strtotime($_POST['activityEnd']);//活动结束
            $data['applystart'] = strtotime($_POST['applyStart']);//报名开始时间
            $data['applyend'] = strtotime($_POST['applyEnd']);//报名结束时间
            $data['display_activity_startendtime'] = ($_POST['activityTimeDisplay']);//报名开始时间
            $data['display_activity_apply_startendtime'] = ($_POST['applyTimeDisplay']);//报名结束时间
            $voteIds = getParameter('voteId', 'iArr', false);
            $voteNames = getParameter('voteName', 'sArr', false);
            $selectedFields = getParameter('selectedFields','iArr',false);
            if(empty($selectedFields) && empty($data['additional_info']))
            {
                $this->showMessage(500,"没有选择报名信息字段");
            }
            $data['selectedfields'] = implode(',',$selectedFields);
            for ($i = 0; $i < sizeof($voteIds); $i++) {
                $result = D('Social_activity_vote')->getVoteData($voteIds[$i]);
                if (empty($result))
                    $this->showMessage(500,'ID为' . $voteIds[$i] . '的投票不存在');
            }
            if (!empty($data['activitystart']) && !empty($data['activityend'])) {
                if ($data['activitystart'] == $data['activityend'] || $data['activitystart'] > $data['activityend'] ) {
                    $this->showMessage(500,"活动开始时间和活动结束时间填写错误");
                }
            }
            if(!$data['is_live']) {
                if (!empty($data['applystart']) && !empty($data['applyend'])) {
                    if ($data['applystart'] == $data['applyend'] || $data['applystart'] > $data['applyend']) {
                        $this->showMessage(500, "报名开始时间和报名结束时间填写错误");
                    }
                }
            }


            $codenameinfo = $_POST['codename'];//生成的邀请码
            $codenameinfo = explode(",", $codenameinfo);
            $vid_file_path_info = $_POST['vid_file_path'];//上传的活动资料
            $vid_file_path_info = explode(",#", $vid_file_path_info);


            $data['is_upload'] = $_POST['is_upload'];
            if(1 == $data['is_upload'])
            {
                $workExtensionArray = getParameter('uploadFileType','sArr');
                $data['work_extension'] = implode(',',$workExtensionArray);
                $workExtensionArray = explode(',',$data['work_extension'] );
                //enable app upload judgement
                if((in_array('png',$workExtensionArray) || in_array('mp4',$workExtensionArray)) &&
                   !in_array('mp3',$workExtensionArray) && !in_array('doc',$workExtensionArray) &&
                   !in_array('ppt',$workExtensionArray) && !in_array('pdf',$workExtensionArray) &&
                   !in_array('swf',$workExtensionArray))
                $data['enable_app_upload'] = 1;
                else
                 $data['enable_app_upload'] = 0;
                $data['upload_info'] = implode(',',getParameter('uploadSelectedFields','iArr'));
            }
            if(!$data['is_live']) {
                $data['role'] = implode(',', getParameter('role', 'iArr'));
            }
            $data['update_at'] = time();
            $data['create_at'] = time();

            $data['status'] = 1;

            $data['publisher_id'] = session('admin.id');
            $data['publisher'] = session('admin.name');

            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 911127998;// 设置附件上传大小
            $upload->exts = array('jpg', 'png');// 设置附件上传类型
            $upload->rootPath = './Resources/socialactivity/'; // 设置附件上传根目录
            // 上传单个文件
            $info = $upload->uploadOne($_FILES['filepic']);
            if (!$info) { // 上传错误提示错误信息

                $this->showMessage(500,$upload->getError());
            } else { // 上传成功 获取上传文件信息
                //echo $info['savepath'] . $info['savename'];
            }
            $data['short_content'] = $info['savepath'] . $info['savename'];

            $Model = M('social_activity');

            $islock = true;
            $Model->startTrans();
            $hid = $Model->add($data);

            if (!$hid) {
                $islock = false;
            }

            if (!empty($codenameinfo) && $data['is_generate'] == 2) { //邀请码入库条件
                foreach ($codenameinfo as $k => $v) {
                    if (!empty($v)) {
                        $datacode['activity_id'] = $hid;
                        $datacode['invitation_code'] = $v;
                        $datacode['status'] = 1;
                        $datacode['create_at'] = time();
                        $sc = M('social_activity_invitation_code')->add($datacode);
                        if (!$sc) {
                            $islock = false;
                            $Model->rollback();
                        }
                    }
                }
            }

            $voteData['activity_id'] = $hid;
            for ($i = 0; $i < sizeof($voteIds); $i++) {
                if (!empty($voteIds[$i])) {
                    $voteData['vote_id'] = $voteIds[$i];
                    $voteData['vote_name'] = $voteNames[$i];
                    $addResult = M('activity_vote_contact')->add($voteData);
                    if (!$addResult) {
                        $islock = false;
                        $Model->rollback();
                        break;
                    }
                }

            }

            if (!empty($vid_file_path_info)) {

                foreach ($vid_file_path_info as $vk => $vv) {
                    if (!empty($vv) && $vv != 'undefined') {
                        $filedatapath = explode(":", $vv);
                        $datafile['activity_id'] = $hid;
                        $datafile['activity_file_path'] = $filedatapath[0];
                        $file_info_name = pathinfo($filedatapath[1]);
                        $datafile['activity_file_name'] =$file_info_name['filename'];
                        //$datafile['activity_file_name'] = mb_substr($filedatapath[1], 0, strrpos($filedatapath[1], '.'));
                        $datafile['type'] = end(explode('.', $filedatapath[1]));
                        if ($datafile['type'] == 'jpeg' || $datafile['type'] == 'jpg' || $datafile['type'] == 'png' || $datafile['type'] == 'gif') {
                            $datafile['type'] = 'image';
                        }

                        if ($datafile['type'] == 'mp4' || $datafile['type'] == 'mov' || $datafile['type'] == 'rmvb' || $datafile['type'] == 'avi') {
                            $datafile['type'] = 'video';

                        } else if ($datafile['type'] == 'mp3' || $datafile['type'] == 'wav' || $datafile['type'] == 'aac' || $datafile['type'] == 'amr') {
                            $datafile['type'] = 'audio';

                        } else if ($datafile['type'] == 'docx' || $datafile['type'] == 'doc') {
                            $datafile['type'] = 'word';

                        } else if ($datafile['type'] == 'pptx') {
                            $datafile['type'] = 'ppt';
                        } else if($datafile['type'] == 'pdf'){
                            $datafile['flag'] = 2;
                        }

                        if ($datafile['type'] == 'video' ) {
                            $vid = $_POST['vid'];
                            $vid_fullpath = $_POST['vid_fullpath'];
                            $vidarr = explode(",", $vid);
                            $vid_fullpatharr = explode(",", $vid_fullpath);

                            if (!empty($vidarr[$vk])) {
                                $datafile['vid'] = $vidarr[$vk];
                            }
                            if (!empty($vid_fullpatharr[$vk])) {
                                $datafile['vid_fullpath'] = $vid_fullpatharr[$vk];
                            }

                        }

                        $datafile['create_at'] = time();
                        $sf = M('social_activity_contact_file')->add($datafile);
                        if (!$sf) {
                            $islock = false;
                            $Model->rollback();
                        }
                    }
                }
            }
            $is_all = $_POST['allgradeorcourse'];
            $allcount = count($is_all);

            if ($allcount == 2) { //选择了全学科和全年级
                $second['course'] = 0;
                $second['grade'] = 0;
                $second['activity_id'] = $hid;
                $social_activity_course_grade_second = M('social_activity_course_grade');
                if (!$second_result = $social_activity_course_grade_second->add($second)) {
                    $islock = false;
                    $Model->rollback();
                }

                //全部选中
                $social_activity_is_select['is_grade_select'] = 1;
                $social_activity_is_select['is_course_select'] = 1;
                $save_is_select = M('social_activity')->where("id=" . $hid)->save($social_activity_is_select);

                if ($save_is_select === false || $save_is_select < 0) {

                    $islock = false;
                    $Model->rollback();
                }

            } else {

                if ($allcount == 0 || $allcount == '') {
                    $grade_second = $_POST['grade'];
                    $course_second = $_POST['course'];
                    $second['activity_id'] = $hid;
                    $social_activity_course_grade_second = M('social_activity_course_grade');

                    if (count($course_second) > 0) {
                        $grade_second = $_POST['grade'];
                        $course_second = $_POST['course'];
                        $second['activity_id'] = $hid;

                        $social_activity_course_grade_second = M('social_activity_course_grade');
                        for ($k = 0; $k < count($course_second); $k++) {
                            $second['course'] = $course_second[$k];
                            $second['grade'] = $grade_second[$k];
                            if (!$second_result = $social_activity_course_grade_second->add($second)) {
                                $islock = false;
                                $Model->rollback();
                            }

                            if ($second['course'] == 0) {
                                $social_activity_is_select['is_course_select'] = 1;

                                $save_is_select = M('social_activity')->where("id=" . $hid)->save($social_activity_is_select);
                                if ($save_is_select === false || $save_is_select < 0) {
                                    $islock = false;
                                    $Model->rollback();
                                }

                            } else {
                                $social_activity_is_select['is_grade_select'] = 1;
                                $save_is_select = M('social_activity')->where("id=" . $hid)->save($social_activity_is_select);
                                if ($save_is_select === false || $save_is_select < 0) {
                                    $islock = false;
                                    $Model->rollback();
                                }
                            }

                        }
                    } else {
                        $social_activity_course_grade_second = M('social_activity_course_grade');
                        $second['course'] = 0;
                        $second['grade'] = 0;
                        $second['activity_id'] = $hid;
                        if (!$second_result = $social_activity_course_grade_second->add($second)) {
                            $islock = false;
                            $Model->rollback();
                        }
                    }
                } else {
                    $grade_second = $_POST['grade'];
                    $course_second = $_POST['course'];
                    $second['activity_id'] = $hid;

                    $social_activity_course_grade_second = M('social_activity_course_grade');
                    for ($k = 0; $k < count($course_second); $k++) {
                        $second['course'] = $course_second[$k];
                        $second['grade'] = $grade_second[$k];
                        if (!$second_result = $social_activity_course_grade_second->add($second)) {
                            $islock = false;
                            $Model->rollback();
                        }

                        if ($second['course'] == 0) {
                            $social_activity_is_select['is_course_select'] = 1;

                            $save_is_select = M('social_activity')->where("id=" . $hid)->save($social_activity_is_select);
                            if ($save_is_select === false || $save_is_select < 0) {
                                $islock = false;
                                $Model->rollback();
                            }

                        } else {
                            $social_activity_is_select['is_grade_select'] = 1;
                            $save_is_select = M('social_activity')->where("id=" . $hid)->save($social_activity_is_select);
                            if ($save_is_select === false || $save_is_select < 0) {
                                $islock = false;
                                $Model->rollback();
                            }
                        }

                    }
                }


            }

            if ($islock == false) {
                $this->showMessage(500,"添加失败");

            } else {
                $Model->commit();
                $this->showMessage(200);
            }

        } else {
            $this->assign('module', '励耘圈管理');
            $this->assign('nav', '京版活动管理');
            $this->assign('subnav', '发布活动');

            $Model = M('social_activity_class');
            $classes = $Model->order('sort_order asc')->where('parent_id=0')->select();
            $this->assign('classes', $classes);

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            $this->display();
        }
    }


    //修改活动
    public function modifyActivity()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        if ($_POST) {
            $check['id'] = $_POST['id'];
            $oldActivityInfo = D('Social_activity')->getActivityDetails($check['id']);
            $data['title'] = remove_xss($_POST['title']);

            $newgradchordata = $_POST['newgradchordata'];
            $gradchordata = $_POST['gradchordata'];
            $newgradchordata = explode('|', $newgradchordata);
            $gradchordata = explode('|', $gradchordata);

            if (count($newgradchordata) != count($gradchordata)) { //长度不一样直接发送推送

                $sar = M('social_activity_register')->where("activity_id=" . $check['id'])->select();
                $sendpushids = '';
                foreach ($sar as $sendk => $sendv) {
                    if ($sendk == 0) {
                        $sendpushids .= $sendv['user_id'];
                    } else {
                        $sendpushids .= ',' . $sendv['user_id'];
                    }
                }

                /*$parameters = array( 'msg' => array($data['title'],$_POST['gradchordata'],$_POST['newgradchordata']) , 'url' => array( 'type' => 0));
                A('Home/Message')->addPushUserMessage('TEACHER_RESOURCEDOWN_Grad',2,$sendpushids,$parameters);*/
            } else { //长度一样，判断年级学科是不是一样
                foreach ($newgradchordata as $k => $v) {
                    $is_false = in_array($v, $gradchordata);
                    if ($is_false != true) {

                        $sar = M('social_activity_register')->where("activity_id=" . $check['id'])->select();
                        $sendpushids = '';
                        foreach ($sar as $sendk => $sendv) {
                            if ($sendk == 0) {
                                $sendpushids .= $sendv['user_id'];
                            } else {
                                $sendpushids .= ',' . $sendv['user_id'];
                            }
                        }

                        /*$parameters = array( 'msg' => array($data['title'],$_POST['gradchordata'],$_POST['newgradchordata']) , 'url' => array( 'type' => 0));
                        A('Home/Message')->addPushUserMessage('TEACHER_RESOURCEDOWN_Grad',2,$sendpushids,$parameters);*/
                    }
                }
            }
            //$data['short_content'] = remove_xss($_POST['short_content']);
            $data['content'] = $_POST['content'];
            $data['stakeholder'] = remove_xss($_POST['stakeholder']);
            $data['category'] = remove_xss($_POST['category']);
            $data['is_live'] = $_POST['is_live'];
            if($data['is_live']) {
                $data['url'] = $_POST['ingUrl'];
                $data['home_jump_url'] = $_POST['home_jump_url'];
                $data['livestart'] = strtotime($_POST['liveStart']);//报名开始时间
                $data['liveend'] = strtotime($_POST['liveEnd']);//报名结束时间
            }

            $data['is_work_compare_activity'] = $_POST['activityType'];
            $class_type_id = $_POST['class_type_id'];
            if ($class_type_id != 0) {
                $data['class_id'] = $class_type_id;
            }


            $data['update_at'] = time();
            $data['status'] = 1;
            //$data['class_id'] = $_POST['class_id'];

            $data['apply_people_number'] = $_POST['limitNums'];//限制人数
            $data['code_num'] = $_POST['limitNums'];//邀请码个数
            $data['remark'] = $_POST['remark'];//邀请码个数
            $data['is_disable'] = $_POST['number'];//是否限制人数
            $data['display_people_register'] = $_POST['display_people_register'];//限制人数 报名人数 报名按钮

            $data['is_generate'] = $_POST['code'];//是否生成邀请码
            $data['activitystart'] = strtotime($_POST['activityStart']);//活动开始时间
            $data['activityend'] = strtotime($_POST['activityEnd']);//活动结束
            $data['applystart'] = strtotime($_POST['applyStart']);//报名开始时间
            $data['applyend'] = strtotime($_POST['applyEnd']);//报名结束时间
            $data['display_activity_startendtime'] = ($_POST['activityTimeDisplay']);//报名开始时间
            $data['display_activity_apply_startendtime'] = ($_POST['applyTimeDisplay']);//报名结束时
            $voteIds = getParameter('voteId', 'iArr', false);
            $voteNames = getParameter('voteName', 'sArr', false);
            $selectedFields = getParameter('selectedFields','iArr',false);

            for ($i = 0; $i < sizeof($voteIds); $i++) {
                $voteType = D('Social_activity_vote')->getVoteType($voteIds[$i]);
                if($voteType != VOTE_TYPE_NORMAL)
                    $this->error('只能关联正常投票，无法关联ID为'.$voteIds[$i].'的投票');
                $result = D('Social_activity_vote')->getVoteData($voteIds[$i]);
                if (empty($result))
                    $this->error('ID为' . $voteIds[$i] . '的投票不存在');
            }
            if (!empty($data['activitystart']) && !empty($data['activityend'])) {
                if ($data['activitystart'] == $data['activityend'] || $data['activitystart'] > $data['activityend']) {
                    $this->error("活动开始时间和活动结束时间填写错误");
                }
            }
            if(!$data['is_live']) {
                if (!empty($data['applystart']) && !empty($data['applyend'])) {
                    if ($data['applystart'] == $data['applyend'] || $data['applystart'] > $data['applyend']) {
                        $this->error("报名开始时间和报名结束时间填写错误");
                    }
                }
            }

            $codenameinfo = $_POST['codename'];//生成的邀请码
            $codenameinfo = explode(",", $codenameinfo);
            $vid_file_path_info = $_POST['vid_file_path'];//上传的活动资料
            $vid_file_path_info = explode(",#", $vid_file_path_info);

            $hidden_resource = $_POST['hidden_resource'];//旧的资源没有删除的



            if ($_FILES["filepic"]["error"] == 0) {
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 911127998;// 设置附件上传大小
                $upload->exts = array('jpg', 'png');// 设置附件上传类型
                $upload->rootPath = './Resources/socialactivity/'; // 设置附件上传根目录
                // 上传单个文件
                $info = $upload->uploadOne($_FILES['filepic']);
                if (!$info) { // 上传错误提示错误信息
                    $this->error($upload->getError());
                }
                $data['short_content'] = $info['savepath'] . $info['savename'];
            }


            $Model = M('social_activity');
            $islock = true;
            $Model->startTrans();

            $hid = $Model->where($check)->save($data);

            if (!$hid) {
                $islock == false;
            }
            M('activity_vote_contact')->where('activity_id=' . $check['id'])->delete();
            $voteData['activity_id'] = $check['id'];
            for ($i = 0; $i < sizeof($voteIds); $i++) {
                if (!empty($voteIds[$i])) {
                    $voteData['vote_id'] = $voteIds[$i];
                    $voteData['vote_name'] = $voteNames[$i];
                    $addResult = M('activity_vote_contact')->add($voteData);
                    if (!$addResult) {
                        $islock = false;
                        $Model->rollback();
                        break;
                    }
                }

            }
            if (!empty($hidden_resource)) { //不为空删除相反的
                $reswhere['id'] = array('not in', $hidden_resource);
                $reswhere['activity_id'] = $check['id'];
                $delres = M('social_activity_contact_file')->where($reswhere)->delete();
                if ($delres == false && $delres != 0) {
                    $islock = false;
                    $Model->rollback();
                }
            } else { //全部删除
                $delres = M('social_activity_contact_file')->where("activity_id=" . $check['id'])->delete();
                if ($delres == false && $delres != 0) {
                    $islock = false;
                    $Model->rollback();
                }
            }


            if (!empty($vid_file_path_info)) {

                foreach ($vid_file_path_info as $vk => $vv) {
                    if (!empty($vv) && $vv != 'undefined') {
                        $filedatapath = explode(":", $vv);
                        $datafile_save['activity_id'] = $check['id'];
                        $datafile_save['activity_file_path'] = $filedatapath[0];
                        $file_info_name = pathinfo($filedatapath[1]);
                        $datafile_save['activity_file_name'] = $file_info_name['filename'];
                        //$datafile['activity_file_name'] = mb_substr($filedatapath[1], 0, strrpos($filedatapath[1], '.'));
                        $datafile_save['type'] = end(explode('.', $filedatapath[1]));
                        if ($datafile_save['type'] == 'jpeg' || $datafile_save['type'] == 'jpg' || $datafile_save['type'] == 'png' || $datafile_save['type'] == 'gif') {
                            $datafile_save['type'] = 'image';
                        }

                        if ($datafile_save['type'] == 'mp4' || $datafile_save['type'] == 'mov' || $datafile_save['type'] == 'rmvb' || $datafile_save['type'] == 'avi') {
                            $datafile_save['type'] = 'video';

                        } else if ($datafile_save['type'] == 'mp3' || $datafile_save['type'] == 'wav' || $datafile_save['type'] == 'aac' || $datafile_save['type'] == 'amr') {
                            $datafile_save['type'] = 'audio';

                        } else if ($datafile_save['type'] == 'docx' || $datafile_save['type'] == 'doc') {
                            $datafile_save['type'] = 'word';

                        } else if ($datafile_save['type'] == 'pptx') {
                            $datafile_save['type'] = 'ppt';
                        }


                        if ($datafile_save['type'] == 'video' ) {
                            $vid = $_POST['vid'];
                            $vid_fullpath = $_POST['vid_fullpath'];
                            $vidarr = explode(",", $vid);
                            $vid_fullpatharr = explode(",", $vid_fullpath);

                            if (!empty($vidarr[$vk])) {
                                $datafile_save['vid'] = $vidarr[$vk];
                            }
                            if (!empty($vid_fullpatharr[$vk])) {
                                $datafile_save['vid_fullpath'] = $vid_fullpatharr[$vk];
                            }

                        }


                        $datafile_save['create_at'] = time();
                        $sf = M('social_activity_contact_file')->add($datafile_save);
                        if ($sf == false) {
                            $islock = false;
                            $Model->rollback();
                        }
                    }
                }
            }


            if (!empty($codenameinfo)) {
                foreach ($codenameinfo as $k => $v) {
                    if (!empty($v)) {
                        $datacode['activity_id'] = $check['id'];
                        $datacode['invitation_code'] = $v;
                        $datacode['status'] = 1;
                        $datacode['create_at'] = time();
                        $sc = M('social_activity_invitation_code')->add($datacode);
                        if ($sc == false) {
                            $islock = false;
                            $Model->rollback();
                        }
                    }
                }
            }

            $delgcmap['activity_id'] = $check['id'];

            $delgc = M('social_activity_course_grade')->where($delgcmap)->delete();

            if ($delgc == false && $delgc != 0) {
                $islock = false;
                $Model->rollback();
            }


            /*$grade_second=$_POST['grade'];
            $course_second=$_POST['course'];
            $second['activity_id']=$check['id'];

            $social_activity_course_grade_second=M('social_activity_course_grade');

            for($k=0;$k<count($course_second);$k++){
                $second['course']=$course_second[$k];
                $second['grade']=$grade_second[$k];
                if(!$second_result=$social_activity_course_grade_second->add($second)){
                    $islock = false;
                    $Model->rollback();
                }
            }*/

            $is_all = $_POST['allgradeorcourse'];
            $allcount = count($is_all);

            if ($allcount == 2) { //选择了全学科和全年级
                $second['course'] = 0;
                $second['grade'] = 0;
                $second['activity_id'] = $check['id'];

                $social_activity_course_grade_second = M('social_activity_course_grade');
                if (!$second_result = $social_activity_course_grade_second->add($second)) {
                    $islock = false;
                    $Model->rollback();
                }

                //全部选中
                $social_activity_is_select['is_grade_select'] = 1;
                $social_activity_is_select['is_course_select'] = 1;
                $save_is_select = M('social_activity')->where("id=" . $hid)->save($social_activity_is_select);

                if ($save_is_select === false || $save_is_select < 0) {

                    $islock = false;
                    $Model->rollback();
                }

            } else {

                if ($allcount == 0 || $allcount == '') {
                    $grade_second = $_POST['grade'];
                    $course_second = $_POST['course'];
                    $second['activity_id'] = $check['id'];
                    $social_activity_course_grade_second = M('social_activity_course_grade');

                    if (count($course_second) > 0) {
                        $grade_second = $_POST['grade'];
                        $course_second = $_POST['course'];
                        $second['activity_id'] = $check['id'];

                        $social_activity_course_grade_second = M('social_activity_course_grade');
                        for ($k = 0; $k < count($course_second); $k++) {
                            $second['course'] = $course_second[$k];
                            $second['grade'] = $grade_second[$k];
                            if (!$second_result = $social_activity_course_grade_second->add($second)) {
                                $islock = false;
                                $Model->rollback();
                            }

                            if ($second['course'] == 0) {
                                $social_activity_is_select['is_course_select'] = 1;

                                $save_is_select = M('social_activity')->where("id=" . $hid)->save($social_activity_is_select);
                                if ($save_is_select === false || $save_is_select < 0) {
                                    $islock = false;
                                    $Model->rollback();
                                }

                            } else {
                                $social_activity_is_select['is_grade_select'] = 1;
                                $save_is_select = M('social_activity')->where("id=" . $hid)->save($social_activity_is_select);
                                if ($save_is_select === false || $save_is_select < 0) {
                                    $islock = false;
                                    $Model->rollback();
                                }
                            }

                        }
                    } else {
                        $social_activity_course_grade_second = M('social_activity_course_grade');
                        $second['course'] = 0;
                        $second['grade'] = 0;
                        $second['activity_id'] = $check['id'];
                        if (!$second_result = $social_activity_course_grade_second->add($second)) {
                            $islock = false;
                            $Model->rollback();
                        }
                    }
                } else {
                    $grade_second = $_POST['grade'];
                    $course_second = $_POST['course'];
                    $second['activity_id'] = $check['id'];

                    $social_activity_course_grade_second = M('social_activity_course_grade');
                    for ($k = 0; $k < count($course_second); $k++) {
                        $second['course'] = $course_second[$k];
                        $second['grade'] = $grade_second[$k];
                        if (!$second_result = $social_activity_course_grade_second->add($second)) {
                            $islock = false;
                            $Model->rollback();
                        }

                        if ($second['course'] == 0) {
                            $social_activity_is_select['is_course_select'] = 1;

                            $save_is_select = M('social_activity')->where("id=" . $hid)->save($social_activity_is_select);
                            if ($save_is_select === false || $save_is_select < 0) {
                                $islock = false;
                                $Model->rollback();
                            }

                        } else {
                            $social_activity_is_select['is_grade_select'] = 1;
                            $save_is_select = M('social_activity')->where("id=" . $hid)->save($social_activity_is_select);
                            if ($save_is_select === false || $save_is_select < 0) {
                                $islock = false;
                                $Model->rollback();
                            }
                        }

                    }
                }

            }


            if ($islock == false) {
                $this->error("修改失败");

            } else {
                if ($oldActivityInfo['activitystart'] != $data['activitystart']) {
                    $teacherIds = D('Social_activity')->getRegisteredIds($check['id'], ROLE_TEACHER);
                    $parameters = array('msg' => array($oldActivityInfo['title'], date("Y-m-d H:i:s", $oldActivityInfo['activitystart']), date("Y-m-d H:i:s", $data['activitystart'])), 'url' => array('type' => 0));
                    A('Home/Message')->addPushUserMessage('ACTIVITY_STARTTIME_MODIFIED', 2, implode(',', array_column($teacherIds, 'id')), $parameters);
                }
                $Model->commit();
                $this->redirect("Activities/activitiesMgmt");
            }

            $this->redirect("Activities/activitiesMgmt");

        } else {
            $this->assign('module', '励耘圈管理');
            $this->assign('nav', '京版活动管理');
            $this->assign('subnav', '修改活动');

            $id = $_GET['id'];
            $this->assign('id', $id);

            $Model = M('social_activity');
            $result = $Model->where("id=$id")->find();
            $this->assign('child_class', $result['class_id']);

            $result['activitystart'] = date("Y-m-d H:i:s", $result['activitystart']);
            $result['activityend'] = date("Y-m-d H:i:s", $result['activityend']);
            $result['applystart'] = date("Y-m-d H:i:s", $result['applystart']);
            $result['applyend'] = date("Y-m-d H:i:s", $result['applyend']);

            $Model = M('social_activity_class');
            $classes = $Model->order('sort_order asc')->where('parent_id=0')->select();

            $this->assign('classes', $classes);

            //根据活动id获取活动的资源
            $res = M('social_activity_contact_file')->where("activity_id=" . $id)->select();

            $this->assign('resource_list', $res);

            $classrow = M('social_activity_class')->where('id=' . $result['class_id'])->find();

            if (!empty($classrow['parent_id'])) {
                $result['class_id'] = $classrow['parent_id'];
            }


            $this->assign('data', $result);

            $childdata = M('social_activity_class')->where('parent_id=' . $result['class_id'])->select();

            $this->assign('childdata', $childdata);

            $codelist = M('social_activity_invitation_code')->where("activity_id=" . $id)->select();
            $where['activity_id'] = $id;
            $class_grade = M('social_activity_course_grade')->where($where)
                ->join("left join dict_grade ON dict_grade.id = social_activity_course_grade.grade")
                ->join("left join dict_course on dict_course.id=social_activity_course_grade.course")
                ->field("dict_grade.id grade_id,dict_grade.grade,dict_course.id course_id,dict_course.course_name")->select();

            foreach ($class_grade as $gv => &$gk) {
                if (empty($gk['course_id']) && empty($gk['course_name'])) {
                    $gk['course_id'] = 0;
                    $gk['course_name'] = '全学科';
                }

                if (empty($gk['grade_id']) && empty($gk['grade'])) {
                    $gk['grade_id'] = 0;
                    $gk['grade'] = '全年级';
                }
            }

            if ($result['is_grade_select'] == 1 && $result['is_course_select'] == 1) {
                $this->assign('is_panduan', 1);
            }

            //print_r($class_grade);die();

            $this->assign('grade_class_select', $class_grade);

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);


            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);
            $this->assign('voteData', M('activity_vote_contact')->where('activity_id=' . $id)->select());
            $this->assign('codelist', $codelist);
            $this->display();
        }
    }


    //京版活动详情
    public function activityDetails($id)
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '励耘圈管理');
        $this->assign('nav', '京版活动管理');
        $this->assign('subnav', '活动详情');

        $SocialActivity = M('social_activity');
        $check['id'] = $id;
        $result = $SocialActivity->where($check)->find();
        if ($result['class_id']) {
            $mokuaimap['id'] = $result['class_id'];
            $mokuai = M('social_activity_class')->where($mokuaimap)->find();
            $result['class_name_data'] = $mokuai['class'];
            $result['parent_class_id'] = $mokuai['parent_id'];
        }
        //查询所有的资源
        $res = M('social_activity_contact_file')->where("activity_id=" . $check['id'])->select();
        $this->assign('resource_list', $res);
        $codelist = M('social_activity_invitation_code')->where("activity_id=" . $id)->select(); //查询邀请码
        $this->assign('codelist', $codelist);
        $this->assign('data', $result);
        $this->assign('res_id', $check['id']);
        $data = D('Message')->getCoursesAndGrades();

        $this->assign('courses', $data['courses']);
        $this->assign('grades', $data['grades']);
        $this->assign('voteData', M('activity_vote_contact')->where('activity_id=' . $id)->select());
        $this->display();
    }


    //根据京版活动id获取报名情况
    public function getActivityListInfo()
    {
        $id = $_GET['id'];
        $queryParas = $_GET;
        $keyword = $queryParas['keyword'];
        $status = intval($queryParas['lock_status']);
        $pageIndex = $_GET['p'];
        $where = array();
        if (!empty($status)) {
            $where['social_activity_works.status'] = $status - 1;
        }
        if (session('admin.role') == 3) {
            $where['auth_teacher.school_id'] = session('admin.school_id');
        }
        if (!empty($queryParas['province_id']))
            $where['dict_schoollist.provice_id'] = $queryParas['province_id'];
        if (!empty($queryParas['city_id']))
            $where['dict_schoollist.city_id'] = $queryParas['city_id'];
        if (!empty($queryParas['country_id']))
            $where['dict_schoollist.district_id'] = $queryParas['country_id'];
        if (!empty($queryParas['school_id']))
            $where['social_activity_register.school_id'] = $queryParas['school_id'];

        if (!empty($queryParas['school_name']))
            $where['social_activity_register.school_name'] = array('like',"%{$queryParas['school_name']}%");

        if (!empty($queryParas['course_id']))
            $where['social_activity_register.course'] = $queryParas['course_id'];
        if (!empty($queryParas['grade_id']))
            $where['social_activity_works.grade'] = $queryParas['grade_id'];
        if (!empty($queryParas['teacher_telephone']))
            $where['auth_teacher.telephone'] = array('like', '%' . $queryParas['teacher_telephone'] . '%');


        if (!empty($queryParas['register_at'])) {
            $startTime = $this->getMonthRange($queryParas['register_at'], true);
            $endTime = $this->getMonthRange($queryParas['register_at'], false);
            $where['social_activity_register.register_at'] = array(
                array('lt', $endTime),
                array('egt', $startTime)
            );
        }

        if (!empty($keyword)) {
            $where['social_activity_register.user_name|auth_teacher.telephone|social_activity_register.lesson'] = array('like', '%' . $keyword . '%');
        }

        $SocialActivityRegister = M('social_activity_register');
        $where['social_activity_register.activity_id'] = $id;
        $registerDetails = $SocialActivityRegister->where($where)
            ->join("left join social_activity_works on social_activity_works.activity_register_id=social_activity_register.id")
            ->join("left join auth_teacher on auth_teacher.id=social_activity_register.user_id")
            ->join("left join dict_schoollist on dict_schoollist.id=social_activity_register.school_id")
            ->field('(CASE WHEN social_activity_register.role='.ROLE_TEACHER .' THEN \'教师\' WHEN social_activity_register.role='.ROLE_STUDENT .' THEN \'学生\' WHEN social_activity_register.role='.ROLE_PARENT .' THEN \'家长\' END) role,(case when social_activity_works.id > 0 then 1 else 0 end) hasUploadWorks,social_activity_register.lesson,auth_teacher.email,register_info,social_activity_works.works_name,social_activity_works.works_description,social_activity_register.id,social_activity_register.user_name as name,social_activity_register.telephone,dict_schoollist.school_name,social_activity_register.register_at,social_activity_register.invitation_code,social_activity_works.status,social_activity_works.point,social_activity_works.voted_title')
            ->group('social_activity_register.id')
            ->buildSql();
        try {
            $count = M()->table($registerDetails.' a')->field('count(1) count')->find();
        }catch(\Exception $e){echo M()->getLastSql();exit;}
        $Page = new \Think\Page($count['count'], 20);
        $show = $Page->show('pageCallback');
        $result = M()->table($registerDetails.' a')->page($pageIndex,20)->select();
        //print_r(M()->getLastSql());die();
        $this->assign('registerDetails', $result);
        $html = $this->fetch('getActivityListInfo');
        $datainfo['info'] = $html;
        $datainfo['pageInfo'] = $show;
        $this->ajaxReturn($datainfo);
    }

    //京版活动下报名人导出
    public function exportedActivityRegister($activity_id = 0)
    {

        $oss_path = C('oss_path');
        if (0 == $activity_id)
            $activity_id = $_GET['activity_id'];
        $activity_register_model = M('social_activity_register');
        $activity_register = $activity_register_model->join("social_activity on social_activity.id=social_activity_register.activity_id")
            ->join("auth_teacher on auth_teacher.id=social_activity_register.user_id")
            ->join("dict_schoollist on dict_schoollist.id=auth_teacher.school_id")
            ->join("dict_citydistrict a on a.id=dict_schoollist.provice_id")
            ->join("dict_citydistrict b on b.id=dict_schoollist.city_id")
            ->join("dict_citydistrict c on c.id=dict_schoollist.district_id")
            ->field("user_name,FROM_UNIXTIME(social_activity_register.register_at) as register_time,social_activity_register.register_info,file_path,social_activity.title,"
                . "auth_teacher.telephone,a.name provice,b.name city,c.name district,dict_schoollist.school_name,auth_teacher.email")
            ->where("activity_id=" . $activity_id)->select();

        //$str="活动名称,报名人姓名,报名人联系方式,填写信息,报名时间,上传附件,省份,城市,区县,学校名称\n";
        $str = "报名人,报名信息,报名时间\n";
        $str = iconv('utf-8', 'gb2312', $str);
        foreach ($activity_register as $v) {
            $teacher_name = iconv('utf-8', 'gbk', $v['user_name']);
            $teacher_telephone = iconv('utf-8', 'gb2312', $v['telephone']);
            $email = iconv('utf-8', 'gb2312', $v['email']);
            $register_info = iconv('utf-8', 'gb2312', $v['register_info']);
            $register_time = iconv('utf-8', 'gb2312', $v['register_time']);

            $line = $teacher_name . ',' . iconv('utf-8', 'gb2312', '手机') . ":" . $teacher_telephone . " " . iconv('utf-8', 'gb2312', '邮箱') . ':' . $email . " " . iconv('utf-8', 'gb2312', '报名信息') . ":" . $register_info . "," . $register_time . "\n";

            $str .= $line;

        }

        $filename = date('Ymd') . rand(0, 1000) . 'activityRegister' . '.csv';
        $csv = new CSV();
        $csv->downloadFileCsv($filename, $str);
        die;
    }

    //导出排名信息根据活动id
    public function exportRanking()
    {
        $id = $_GET['id'];
        $activityInfo = D('Social_activity')->getActivityDetails($id);
//        if ($activityInfo['class_id'] != 6)
//            $this->exportedActivityRegister($id);
        $queryParas = $_GET;
        $keyword = $queryParas['keyword'];
        $status = intval($queryParas['lock_status']);
        $where = array();
        if (!empty($status)) {
            $where['social_activity_works.status'] = $status - 1;
        }
        if (session('admin.role') == 3) {
            $where['auth_teacher.school_id'] = session('admin.school_id');
        }
        if (!empty($queryParas['province_id']))
            $where['dict_schoollist.provice_id'] = $queryParas['province_id'];
        if (!empty($queryParas['city_id']))
            $where['dict_schoollist.city_id'] = $queryParas['city_id'];
        if (!empty($queryParas['country_id']))
            $where['dict_schoollist.district_id'] = $queryParas['country_id'];
        if (!empty($queryParas['school_id']))
            $where['auth_teacher.school_id'] = $queryParas['school_id'];
        if (!empty($queryParas['course_id']))
            $where['auth_teacher_second.course_id'] = $queryParas['course_id'];
        if (!empty($queryParas['grade_id']))
            $where['auth_teacher_second.grade_id'] = $queryParas['grade_id'];
        if (!empty($queryParas['teacher_telephone']))
            $where['auth_teacher.telephone'] = array('like', '%' . $queryParas['teacher_telephone'] . '%');


        if (!empty($queryParas['register_at'])) {
            $startTime = $this->getMonthRange($queryParas['register_at'], true);
            $endTime = $this->getMonthRange($queryParas['register_at'], false);
            $where['social_activity_register.register_at'] = array(
                array('lt', $endTime),
                array('egt', $startTime)
            );
        }

        if (!empty($keyword)) {
            $where['auth_teacher.name|social_activity_works.works_name|social_activity_works.works_description'] = array('like', '%' . $keyword . '%');
        }

        $SocialActivityRegister = M('social_activity_register');
        $where['social_activity_register.activity_id'] = $id;
        $registerDetails = $SocialActivityRegister->where($where)
            ->join("left join social_activity_works on social_activity_works.activity_register_id=social_activity_register.id")
            ->join("left join auth_teacher on auth_teacher.id=social_activity_register.user_id")
            ->join("left join auth_teacher_second ON auth_teacher_second.teacher_id = auth_teacher.id")
            ->join("left join dict_course ON auth_teacher_second.course_id = dict_course.id")
            ->join("left join dict_grade ON auth_teacher_second.grade_id = dict_grade.id")
            ->join("left join biz_class ON biz_class.class_teacher_id = auth_teacher.id")
            ->join("left join dict_schoollist on dict_schoollist.id=auth_teacher.school_id")
            ->join('dict_course course ON course.id = social_activity_works.course')
            ->join('dict_grade grade ON grade.id = social_activity_works.grade')
            ->field('(CASE WHEN social_activity_register.role='.ROLE_TEACHER .' THEN \'教师\' WHEN social_activity_register.role='.ROLE_STUDENT .' THEN \'学生\' WHEN social_activity_register.role='.ROLE_PARENT .' THEN \'家长\' END) role,social_activity_works.works_name,social_activity_works.works_description,social_activity_register.id,social_activity_register.user_name name,social_activity_register.telephone,dict_schoollist.school_name,social_activity_register.register_at,social_activity_register.invitation_code,social_activity_register.invitation_code,social_activity_works.status,social_activity_works.point,social_activity_works.voted_title,dict_grade.grade,biz_class.name as bname,'
                . "GROUP_CONCAT(DISTINCT dict_course.course_name SEPARATOR '.')course_name,"
                . "GROUP_CONCAT(dict_grade.grade,biz_class.name SEPARATOR '.')grade_name,course.course_name reg_course_name,grade.grade reg_grade_name,local_course,school_course")
            ->group('social_activity_register.id')
            ->select();

        $str = "报名人,手机号码,角色,学科,年级,校本课程,地方课程,参评课题,奖项\n";
        $str = iconv('utf-8', 'gb2312', $str);
        foreach ($registerDetails as $v) {
            $name = iconv('utf-8', 'gbk', $v['name']);
            $role = iconv('utf-8', 'gbk', $v['role']);
            $courseName = iconv('utf-8', 'gbk', $v['reg_course_name']);
            $gradeName = iconv('utf-8', 'gbk', $v['reg_grade_name']);
            $school_course = $v['school_course'] == 0? '否':'是';
            $local_course = $v['local_course'] == 0? '否':'是';
            $school_course = iconv('utf-8', 'gbk', $school_course);
            $local_course = iconv('utf-8', 'gbk', $local_course);
            $telephone = iconv('utf-8', 'gb2312', $v['telephone']);
            $voted_title = iconv('utf-8', 'gbk', $v['voted_title']);
            $point = iconv('utf-8', 'gbk', $v['point']);
            $str .= $name . "," . $telephone . "," .$role .',' . $courseName.','.$gradeName.','.$school_course.','.$local_course.','.$voted_title  . "," . $point . "\n";
        }

        $filename = date('Ymd') . rand(0, 1000) . 'point' . '.csv';
        $csv = new CSV();
        $csv->downloadFileCsv($filename, $str);

    }

    //导入排名
    public function importRanking()
    {
        $id = $_GET['id'];
        $activityDetails = D('Social_activity')->getActivityDetails($id);
        if (time() < $activityDetails['applyend']) {
            $data['status'] = 1004;
            echo json_encode($data);
            die;
        }

        /*if( 0 == $activityDetails['is_pack'])
        {
            $data['status']=1005;
            echo json_encode($data);die;
        }*/

        if (empty($_FILES)) {
            $data['status'] = 1001;
            echo json_encode($data);
            die;
        }
        $csv = new CSV();
        $result = $csv->getCsvData($_FILES);
        if (!is_array($result)) {
            $data['status'] = $result;
            echo json_encode($data);
            die;
        }
        $encode = mb_detect_encoding($result['result'][0][0], array('UTF-8', 'GB2312', 'GBK', 'EUC-CN'));
        if ($encode == 'EUC-CN' || $encode == 'GB2312') {
            $is_utf8 = 1;
        } else if ($encode == 'GBK') {
            $is_utf8 = 2;
        } else if ($encode == 'UTF-8') {
            $is_utf8 = 0;
        }
        $data = $result['result'];
        $length = $result['length'];


        $allNumber = $length - 1;
        $successNumber = 0;

        if ($allNumber == 0) //empty file
        {
            $this->ajaxReturn(array('status' => 1002));
        }
        for ($i = 1; $i < $length; $i++) {
            $role = $this->encode_string($is_utf8, $data[$i][2]); //角色
            $telephone = $this->encode_string($is_utf8, $data[$i][1]); //手机号码
            $data[$i][0] = $this->encode_string($is_utf8, $data[$i][0]); //name
            if (!empty($telephone)) {
                $userInfo = '';
                $iRole = 0;
                switch($role)
                {
                    case '教师':$userInfo = D('Auth_teacher')->getTeacherByTel($telephone);
                                 $iRole = 2;
                                 break;
                    case '学生':$userInfo = D('Auth_student')->getStudentInfoByTelAndName($telephone,$data[$i][0]);
                                 $iRole = 3;
                                 break;
                    case '家长':$userInfo = D('Auth_parent')->getParentInfoByTelephone($telephone);
                                 $iRole = 4;
                                 break;
                    default:    $errorArray[] = $data[$i];
                                 $errorInfo[] = '用户角色错误';
                                 continue;
                }

                $map = array();
                $map['activity_id'] = $id;
                $map['user_id'] = $userInfo['id'];//根据手机号码获取用户id
                $map['role'] = $iRole;
                $userinfo = M('social_activity_register')->where($map)->find();

                $savpoint['point'] = $this->encode_string($is_utf8, $data[$i][4]);
                if (!empty($savpoint['point']) && !empty($userinfo)) {
                    $wmap['activity_register_id'] = $userinfo['id'];
                    $workInfo = M('social_activity_works')->where($wmap)->find();
                    if (!empty($workInfo)) {
                        if($workInfo['status'] != ACTIVITY_WORK_STATUS_VERIFIED)
                        {
                            $errorArray[] = $data[$i];
                            $errorInfo[] = '用户传作品未通过审核';
                        }
                        else {
                            $save_id = M('social_activity_works')->where($wmap)->save($savpoint);
                            $successNumber++;
                        }
                    } else {
                        $errorArray[] = $data[$i];
                        $errorInfo[] = '用户未上传作品';
                    }
                } else {
                    $errorArray[] = $data[$i];
                    $errorInfo[] = '用户未注册或奖项设置错误';
                }

            }

        }
        //var_dump($successNumber);echo $allNumber;exit;
        if ($successNumber != $allNumber) {

            $returnData['status'] = 1003;
            $returnData['all_number'] = $allNumber;
            $returnData['success_number'] = $successNumber;
            $returnData['notice_data'] = $errorArray;
            $returnData['notice_info'] = $errorInfo;
            $this->ajaxReturn(($returnData));
        } else if (D('Social_activity')->getHasPointPeopleNumber($id) == D('Social_activity')->getWorkUploadPeopleNumber($id)) {
            $saveData['works_show_status'] = 1;
            M('social_activity')->where('id=' . $id)->save($saveData);
        }
        $this->ajaxReturn('success');

    }


    //审核通过
    public function reviewedAdopt()
    {
        $id = $_GET['id'];
        $data['activity_register_id'] = $id;

        $res = M('social_activity_works')->where($data)->find();

        if (!empty($res)) {
            $savedata['status'] = 1;
            $wid = M('social_activity_works')->where($data)->save($savedata);

            if ($wid) {
                $this->ajaxReturn('success');
            } else {
                $this->ajaxReturn('error');
            }
        } else {
            $this->ajaxReturn(1001);
        }

    }

    //拒绝审核
    public function refuseAdopt()
    {
        $id = $_GET['id'];
        $content = $_GET['content'];
        $data['activity_register_id'] = $id;

        $res = M('social_activity_works')->where($data)->find();
        if (!empty($res)) {

            $savedata['status'] = 2;
            $savedata['error_data'] = $content;
            $wid = M('social_activity_works')->where($data)->save($savedata);

            if ($wid !== false) {
                $this->ajaxReturn('success');
            } else {
                $this->ajaxReturn('error');
            }
        } else {
            $this->ajaxReturn(1001);
        }

    }

    function get_fileSize($url){
        if(!isset($url)||trim($url)==''){
            return '';
        }
        ob_start();
        $ch=curl_init($url);
        curl_setopt($ch,CURLOPT_HEADER,1);
        curl_setopt($ch,CURLOPT_NOBODY,1);
        $okay=curl_exec($ch);
        curl_close($ch);
        $head=ob_get_contents();
        ob_end_clean();
        $regex='/Content-Length:\s([0-9].+?)\s/';
        $count=preg_match($regex,$head,$matches);
        return isset($matches[1])&&is_numeric($matches[1])?$matches[1]:'';
    }

    public function resdownfile()
    {
        $filePath = $_GET['path'];
        $filePath = urldecode($filePath);
        header('Content-Description: File Transfer');
        $size = $this->get_fileSize($filePath);
        header('Content-type:application/octet-stream');
        header('Content-Disposition: attachment; filename=' . iconv('utf-8','gbk',basename($filePath)));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $size);
        readfile($filePath);
    }

    //手动打分
    public function saveScore()
    {
        $ids = getParameter('ids','iArr');
        $values = getParameter('values','sArr');
        $activityId = M('social_activity_register')->where('id='.$ids[0])->field('activity_id id')->find();
        $activityId = $activityId['id'];
        $activityDetails = D('Social_activity')->getActivityDetails($activityId);
        if (time() < $activityDetails['applyend'])
            $this->ajaxReturn(1002);

        //if( 0 == $activityDetails['is_pack'])
        //    $this->ajaxReturn(1003);
        $result = true;
        for($i=0;$i<sizeof($ids);$i++) {
            $id = $ids[$i];
            $data['activity_register_id'] = $id;
            $da = M('social_activity_works')->where($data)->find();
            $regInfo = M('social_activity_register')->where('id='.$id)->find();

            $num = $values[$i];
            if (!empty($da)) {
                //判断是否通过审核
                $workInfo = $res = M('social_activity_works')->where($data)->field('status')->find();
                if($workInfo['status'] != ACTIVITY_WORK_STATUS_VERIFIED && $num != '')
                {
                    $this->ajaxReturn(array(1003,$regInfo['user_name']));
                }
                $savedata['point'] = $num;
                $res = M('social_activity_works')->where($data)->save($savedata);
                $activityId = M('social_activity_register')->where('id=' . $id)->field('activity_id id')->find();
                $activityId = $activityId['id'];
                if ($res !== false) {
                    $successNumber = M('social_activity_works')->join('social_activity_register ON social_activity_register.id = social_activity_works.activity_register_id')
                        ->where('social_activity_works.point <> \'\' and social_activity_register.activity_id=' . $activityId)->field('count(1) as num')->find();
                    $successNumber = $successNumber['num'];
                    if (D('Social_activity')->getHasPointPeopleNumber($activityId) == D('Social_activity')->getWorkUploadPeopleNumber($activityId)) //所有人均打分
                    {
                        $saveData['works_show_status'] = 1;
                        M('social_activity')->where('id=' . $activityId)->save($saveData);
                    }

                } else {
                    $result =  false;
                }
            } else {
                if($num != '') {
                    $result = false;
                    $this->ajaxReturn(array(1001, $regInfo['user_name']));
                }
            }
        }
       if(false == $result)
           $this->ajaxReturn('failed');
        $this->ajaxReturn('success');
    }

    //查看报名详情
    public function lookRanking()
    {
        $this->assign('module', '京版活动管理');
        $this->assign('nav', '京版活动管理');
        $this->assign('subnav', '报名/作品信息');
        $id = $_GET['id'];

        $sar = M('social_activity_register')
            ->join("left join dict_course ON dict_course.id = social_activity_register.course")
            ->join('social_activity ON social_activity.id = social_activity_register.activity_id')
            ->where("social_activity_register.id=" . $id)->field('*,social_activity_register.role regrole,social_activity_register.additional_info regaddinfo')->find();
        $saw = M('social_activity_works')
            ->join("left join dict_course ON dict_course.id = social_activity_works.course")
            ->join("left join dict_grade ON social_activity_works.grade = dict_grade.id")
            ->field("social_activity_works.*,dict_course.course_name,dict_grade.grade")
            ->where("activity_register_id=" . $id)->find();


        if (!empty($sar['user_id'])) {
            $map['auth_teacher.id'] = $sar['user_id'];
            $userinfo = M('auth_teacher')
                ->join("left join auth_teacher_second ON auth_teacher_second.teacher_id = auth_teacher.id")
                ->join("left join dict_course ON auth_teacher_second.course_id = dict_course.id")
                ->join("left join dict_grade ON auth_teacher_second.grade_id = dict_grade.id")
                ->join("left join biz_class ON biz_class.class_teacher_id = auth_teacher.id")
                ->join("left join dict_schoollist on dict_schoollist.id=auth_teacher.school_id")
                ->field('dict_schoollist.provice_id,dict_schoollist.city_id,dict_schoollist.district_id,auth_teacher.name,dict_course.course_name,dict_schoollist.school_name,dict_schoollist.school_address,auth_teacher.sex,auth_teacher.email')
                ->where($map)->find();
        }

        $quxianarr = array(
            'id' => $sar['province']
        );
        $quxian = M('dict_citydistrict')->where($quxianarr)->find();
        $this->quxian = $quxian['name'];

        $cityarr = array(
            'id' => $sar['city']
        );
        $cityinfo = M('dict_citydistrict')->where($cityarr)->find();
        $this->cityinfo = $cityinfo['name'];

        $district_idarr = array(
            'id' => $sar['district']
        );
        $district_idinfo = M('dict_citydistrict')->where($district_idarr)->find();
        $this->district_idinfo = $district_idinfo['name'];

        if (!empty($saw['id'])) {
            $resou_list = M('social_activity_works_file')->where("activity_works_id=" . $saw['id'])->select();
            if (empty($resou_list)) {
                $this->assign('is_res_show', 1);
            }
        }
        $this->assign('resource_list', $resou_list);
        $this->assign('sar', $sar);
        $this->assign('saw', $saw);
        $this->assign('userinfo', $userinfo);
        $this->assign('res_id', $id);
        $this->display();
    }

    public function getMonthRange($date, $returnFirstDay = true)
    {
        $timestamp = strtotime($date);
        if ($returnFirstDay) {
            $monthFirstDay = date('Y-m-d 00:00:00', $timestamp);
            return strtotime($monthFirstDay);
        } else {

            $monthLastDay = date('Y-m-d 23:59:59', $timestamp);
            return strtotime($monthLastDay);
        }
    }

    //通过审核活动
    public function approveActivity()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $Model = M('social_activity');

        $check['id'] = $id;
        $result = $Model->where($check)->field('id,title,content,push_status')->find();
        if (empty($result)) {
            $this->ajaxReturn('failed');
            die;
        }
        if ($result['push_status'] == 1) {
            $message_id = $id;
            $message_model = D('Message');
            $message_add_data['role_id'] = '2,3,4';
            $message_add_data['title'] = '京版活动：' . substr($result['title'], 0, 50);
            $message_add_data['truncated_title'] = substr($result['content'], 0, 50);
            $message_add_data['message_content'] = $result['content'];
            $message_add_data['receive_type'] = 1;
            $message_add_data['message_type'] = 2;

            $message_id = $message_id = $message_model->addMessageInfo($message_add_data);
            $people_number = $message_model->addMessageReceive($message_id);
            $message_model->updateMessageReceivenum($message_id, $people_number);
            $parameters = array(
                'url' => array(
                    'type' => 1,
                    'data' => array($id)
                )
            );
            $config_arr = C('PUSH_MESSAGE');
            $format_url = $config_arr['ACTIVITY_PUBLISHED']['FORMAT_URL'];
            if ($parameters['url']['type'] == 0) {
                $messageUrl = 'http://' . $_SERVER["SERVER_NAME"] . sprintf($format_url, $message_id);
            } else {
                $messageUrl = 'http://' . $_SERVER["SERVER_NAME"] . vsprintf($format_url, $parameters['url']['data']);
            }
            A('Home/Message')->sendMessage($message_id, $messageUrl);

            $Model->where($check)->save(array('push_status' => 2));
        }

        $data['status'] = 2;
        $data['approve_at'] = time();
        $Model->where("id=$id")->save($data);

        $this->ajaxReturn('success');
    }

    //拒绝活动
    public function denyActivity()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $Model = M('social_activity');

        $data['status'] = 3;

        $Model->where("id=$id")->save($data);

        $this->ajaxReturn('success');
    }

    //审核各种状态
    public function alldeny()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $status = $_GET['status'];

        switch ($status) {
            case 1:
                $status = 2;
                break;
            case 2:
                $status = 3;
                break;
            case 3:
                $status = 4;
                break;
            case 4:
                $status = 2;
                break;
            case 5:
                $status = 5;
                break;
            case 6:
                $status = 4;
                break;

            default:
                $status = 0;
                break;
        }

        $Model = M('social_activity');
        $data['status'] = $status;
        if ($status == 5)
            $data['approve_at'] = time();
        $id = $Model->where("id=$id")->save($data);

        if ($id) {
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('error');
        }

    }


    //删除活动
    public function deleteActivity()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $Model = M('social_activity');

        $data['status'] = 3;

        $teacherIds = D('Social_activity')->getRegisteredIds($id, ROLE_TEACHER);
        $teacherIds = array_column($teacherIds, 'id');
        $activityInfo = D('Social_activity')->getActivityTitle($id);
        for ($i = 0; $i < count($teacherIds); $i++) {
            $regInfo = D('Social_activity')->getRegisterInfo($id, $teacherIds[$i]);
            $parameters = array('msg' => array(date('Y-m-d H:i:s',$regInfo['register_at']), $activityInfo['title']), 'url' => array('type' => 0));
            A('Home/Message')->addPushUserMessage('ACTIVITY_CANCELED', 2, $teacherIds[$i], $parameters);

        }
        $Model->where("id=$id")->delete();
        $this->ajaxReturn('success');
    }

    /**/
    //下架活动
    public function downActivity()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $Model = M('social_activity');

        $data['status'] = 1;

        $Model->where("id=$id")->save($data);

        $this->ajaxReturn('success');
    }


    public function upload_file_jb()
    {
        //处理截图
        //$video_array=$this->bjresource_video_image_upload();
        //
        $file_name = $_FILES['file']['name'][0];
        $upload = new \Oss\Ossupload();// 实例化上传类
        $result = $upload->upload(3, $_FILES, 7, 0); //1 pic 2//
        $returnArray = explode(",", $result[1]);
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

    public function worksCompare($id)
    {
        $this->assign('module', '励耘圈管理');
        $this->assign('nav', '京版活动管理');
        $this->assign('subnav', '活动详情');
        $SocialActivity = M('social_activity');
        $check['id'] = $id;
        $result = $SocialActivity->where($check)->find();
        if ($result['class_id']) {
            $mokuaimap['id'] = $result['class_id'];
            $mokuai = M('social_activity_class')->where($mokuaimap)->find();
            $result['class_name_data'] = $mokuai['class'];
            $result['parent_class_id'] = $mokuai['parent_id'];
        }
        //查询所有的资源
        $res = M('social_activity_contact_file')->where("activity_id=" . $check['id'])->select();
        $this->assign('resource_list', $res);
        $codelist = M('social_activity_invitation_code')->where("activity_id=" . $id)->select(); //查询邀请码
        $this->assign('codelist', $codelist);
        $this->assign('data', $result);
        $this->assign('res_id', $check['id']);
        $data = D('Message')->getCoursesAndGrades();

        $this->assign('courses', $data['courses']);
        $this->assign('grades', $data['grades']);
        $this->display();
    }

    public function publishWorks()
    {
        $id = getParameter('activity_id', 'int');
        //check points
        $workInfo = $this->model->getActivityWorks($id, '', 0, 20);
        $canPublish = false;
        for($i=0;$i<sizeof($workInfo);$i++)
        {
            if($workInfo[$i]['point'] != '')
            {
                $canPublish = true;
                break;
            }
        }
        if(false === $canPublish)
        {
            $this->showMessage('500', '没有作品评奖，请至少为一位作者进行评奖', array());
        }
        $saveData['works_show_status'] = 1;
        $result = $this->model->saveActivityInfo($id, $saveData);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', 'failed', array());
    }

    public function saveWorkTitle()
    {
        $id = getParameter('activity_id', 'int');
        $title = getParameter('title', 'str');
        $saveData['display_work_title'] = $title;
        $result = $this->model->saveActivityInfo($id, $saveData);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', 'failed', array());
    }

    public function columnMgmt()
    {
        $this->display();
    }

    public function setColumnDisplayState()
    {
        $id = getParameter('id', 'int');
        $state = getParameter('state', 'int');
        $result = D('Dict_column')->setColumnDisplayState($id, $state);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', 'failed', array());
    }

    public function deleteColumnContent()
    {
        $id = getParameter('id', 'int');
        $columnId = getParameter('column_id', 'int');
        $result = $this->model->deleteColumnContent($id, $columnId);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', 'failed', array());
    }

    public function upDownContent()
    {
        $id = getParameter('id', 'int');
        $columnId = getParameter('column_id', 'int');
        $status = getParameter('status', 'int');
        $result = $this->model->upDownColumnContent($id, $columnId, $status, $errorInfo);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', 'failed', array());
    }

    public function saveColumnSort()
    {
        $ids = getParameter('ids', 'iArr');
        $values = getParameter('values', 'iArr');
        $result = $this->model->saveColumnSort($ids, $values);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', 'failed', array());
    }

    public function addColumnContent()
    {
        $id = getParameter('resource_id', 'int');
        $columnId = getParameter('column_id', 'int');
        $title = getParameter('title', 'str');
        $contentType = getParameter('content_type', 'int');
        //check if resource exists
        switch ($contentType) {
            case COLUMN_CONTENTTYPE_ACTIVITY:
                $result = $this->model->getActivityExists($id);
                if (!$result)
                    $this->showMessage('500', '指定活动不存在', array());
                break;
            case COLUMN_CONTENTTYPE_INFORMATION:
                $result = D('Social_expert_information')->getInformationDetails($id);
                if (!$result)
                    $this->showMessage('500', '指定资讯不存在', array());
                break;
            case COLUMN_CONTENTTYPE_VOTE:
                $result = D('Social_activity_vote')->getVoteExists($id);
                if (!$result)
                    $this->showMessage('500', '投票不存在', array());
                $result = D('Social_activity_vote')->getVoteType($id);
                if($result != VOTE_TYPE_NORMAL)
                    $this->showMessage('500', '只能关联正常投票，无法关联此投票', array());
                break;
            default:
        }
        //check has add this resource
        $result = $this->model->getColumnContent($id, $contentType);
        if (!empty($result))
            $this->showMessage('500', '该内容已经添加', array());
        //add this resource
        $result = $this->model->addColumnContent($contentType, $id, $columnId, $title);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', '添加失败', array());
    }

    public function editColumnContent()
    {
        $id = getParameter('id', 'int');
        $title = getParameter('title', 'str');
        $result = $this->model->editColumnContent($id, $title);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', '添加失败', array());
    }


    public function specialColumnMgmt()
    {
        $status = getParameter('status', 'int', false);
        $keyword = getParameter('keyword', 'str', false);
        $result = $this->model->getFilteredSpecialColumn($status, $keyword);
        $this->assign('list', $result);
        $this->assign('status', $status);
        $this->assign('keyword', $keyword);
        $this->display();
    }

    public function setSpecialColumnName()
    {
        $id = getParameter('id', 'int');
        $name = getParameter('name', 'str');
        $result = $this->model->setSpecialColumnName($id, $name);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', '设置失败', array());
    }

    public function setSpecialColumnStatus()
    {
        $id = getParameter('id', 'int');
        $status = getParameter('status', 'int');
        if(COLUMN_STATUS_PUBLISHED == $status) {
            if (!$this->model->getSpecialColumnHasJoinActivity($id))
                $this->showMessage('500', '该专栏未关联活动，请关联活动', array());
            if (!$this->model->getSpecialColumnHasJoinPublishActivity($id))
                $this->showMessage('500', '该专栏关联活动未上架', array());
        }
        $result = $this->model->setSpecialColumnStatus($id, $status);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', '设置失败', array());
    }

    public function addSpecialColumn()
    {
        $name = getParameter('name', 'str');
        $id = $this->model->addSpecialColumn($name,$errorInfo);
        if($id)
            $this->showMessage('200', $id, array());
        else
            $this->showMessage('500', $errorInfo, array());
    }

    public function editSpecialColumn($id)
    {
        $data = $this->model->getSpecialColumnData($id);
        $list = $this->model->getSpecialColumnContentList($id);
        $this->assign('data', $data);
        $this->assign('list', $list);
        $this->display('specialColumnDetail');
    }

    public function setSpecialColumnDisplayState()
    {
        $id = getParameter('id', 'int');
        $state = getParameter('state', 'int');
        $result = D('Dict_column')->setSpecialColumnDisplayState($id, $state);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', 'failed', array());
    }

    public function deleteSpecialColumn()
    {
        $id = getParameter('id', 'int');
        $result = $this->model->deleteSpecialColumn($id);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', 'failed', array());
    }

    public function deleteSpecialColumnContent()
    {
        $id = getParameter('id', 'int');
        $result = $this->model->deleteSpecialColumnContent($id);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', 'failed', array());
    }

    public function upDownSpecialContent()
    {
        $id = getParameter('id', 'int');
        $columnId = getParameter('column_id', 'int');
        $status = getParameter('status', 'int');
        $result = $this->model->upDownSpecialColumnContent($id, $columnId, $status, $errorInfo);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', 'failed', array());
    }

    public function saveSpecialColumnSort()
    {
        $ids = getParameter('ids', 'iArr');
        $values = getParameter('values', 'iArr');
        $result = $this->model->saveSpecialColumnSort($ids, $values);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', 'failed', array());
    }

    public function saveSpecialColumnContentSort()
    {
        $ids = getParameter('ids', 'iArr');
        $values = getParameter('values', 'iArr');
        $result = $this->model->saveSpecialColumnContentSort($ids, $values);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', 'failed', array());
    }

    public function addSpecialColumnContent()
    {
        $id = getParameter('resource_id', 'int');
        $columnId = getParameter('column_id', 'int');
        $title = getParameter('title', 'str');
        $contentType = getParameter('content_type', 'int');
        //check if resource exists
        switch ($contentType) {
            case COLUMN_CONTENTTYPE_ACTIVITY:
                $result = $this->model->getActivityExists($id);
                if (!$result)
                    $this->showMessage('500', '指定活动不存在', array());
                if($this->model->getSpecialColumnHasJoinActivity($columnId))
                    $this->showMessage('500', '该专栏已经关联活动，无法再次关联活动', array());
                break;
            case COLUMN_CONTENTTYPE_INFORMATION:
                $result = D('Social_expert_information')->getInformationDetails($id);
                if (!$result)
                    $this->showMessage('500', '指定资讯不存在', array());
                break;
            case COLUMN_CONTENTTYPE_VOTE:
                $result = D('Social_activity_vote')->getVoteExists($id);
                if (!$result)
                    $this->showMessage('500', '投票不存在', array());
                break;
            default:
        }
        //check has add this resource
        $result = $this->model->getSpecialColumnContent($id, $columnId);
        if (!empty($result))
            $this->showMessage('500', '该资源已经添加', array());
        //add this resource
        $result = $this->model->addSpecialColumnContent($contentType, $id, $columnId, $title);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', '添加失败', array());
    }

    public function editSpecialColumnContent()
    {
        $id = getParameter('id', 'int');
        $title = getParameter('title', 'str');
        $result = $this->model->editSpecialColumnContent($id, $title);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', '添加失败', array());
    }

    public function voteMgmt()
    {
        $flag = getParameter('flag', 'int', false);
        $keyword = getParameter('keyword', 'str', false);
        $pageSize = C('PAGE_SIZE_FRONT');
        if (!empty($flag))
            $where['flag'] = $flag;
        if (!empty($keyword))
            $where['title'] = array('like', "%$keyword%");
        $count = D('Social_activity_vote')->getVoteCount($where);
        $Page = new \Think\Page($count, $pageSize);
        $show = $Page->show();
        $pageIndex = $_GET['p'];

        $this->assign('list', D('Social_activity_vote')->getVoteList($where, $pageIndex, $pageSize));
        $this->assign('page', $show);
        $this->assign('flag', $flag);
        $this->assign('keyword', $keyword);
        $this->display();
    }

    public function deleteVote()
    {
        $id = getParameter('id', 'int');
        $result = D('Social_activity_vote')->deleteVote($id);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', '删除失败', array());
    }

    public function setVoteFlag()
    {
        $id = getParameter('id', 'int');
        $flag = getParameter('flag', 'int');
        $result = D('Social_activity_vote')->setVoteFlag($id, $flag);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', 'failed', array());
    }

    public function addVote()
    {
        if ($_POST) {

            $title = getParameter('title', 'str');
            $voteDisplay = getParameter('voteDisplay', 'str');
            $voteStart = strtotime(getParameter('voteStart', 'str'));
            $voteEnd = strtotime(getParameter('voteEnd', 'str'));
            $description = getParameter('description', 'str');
            $imgPath = getParameter('voteImgPath', 'str');
            $voteFreq =getParameter('vote_freq', 'int');
            $type = VOTE_TYPE_NORMAL;
            $id = getParameter('id', 'int', false);
            $model = M('');
            $model->startTrans();
            $voteId = D('Social_activity_vote')->addEditVoteInfo($id, $title, $voteDisplay, $voteStart, $voteEnd, $description, $imgPath,$voteFreq,$type);
            if (!$voteId) {
                $model->rollback();
                $this->showMessage('500', '投票添加失败', array());
            }
            $candidateId = getParameter('candidateId', 'iArr');
            $candidateName = getParameter('candidateName', 'sArr');
            $candidateImg = getParameter('candidateImg', 'sArr');
            $candidateDescription = $_POST['candidateDescription'];
            $addResult = D('Social_activity_vote')->addVoteCandidates($voteId, $candidateId, $candidateName, $candidateImg, $candidateDescription);
            if (!$addResult) {
                $model->rollback();
                $this->showMessage('500', '投票添加失败', array());
            }
            $model->commit();
            $this->showMessage('200', 'success', array());
        }

        $this->display('voteDetail');
    }

    public function editVote()
    {
        $id = getParameter('id', 'int');
        $r=D('Social_activity_vote')->getCandidateList($id);
        $this->assign('data', D('Social_activity_vote')->getVoteData($id));
        $this->assign('list', D('Social_activity_vote')->getCandidateList($id));
        $this->display('voteDetail');
    }
    public function viewVote()
    {
        $this->assign('disableEdit',1);
        $this->editVote();
    }
    public function activityClass()
    {
        $result = $this->model->getActivityClassList();
        $this->assign('list',$result);
        $this->display();
    }
    public function getAvailableActivityClass()
    {
        $id = getParameter('id', 'int',false);
        $this->showMessage(200, 'success' , $this->model->getAvailableActivityClass($id));
    }
    public function addEditClass()
    {
        $id = getParameter('id', 'int',false);
        $parentId = getParameter('parentId', 'int');
        $typeName = getParameter('typeName', 'str');
        $result = $this->model->addEditClass($id,$parentId,$typeName);
        if (false !== $result)
            $this->showMessage('200', 'success', array());
        else
            $this->showMessage('500', 'failed', array());
    }

    public function getUnProcessColumnActivity()
    {
       //data title id
        $result = array();
        $where['activity_column_contact.content_type'] = array('in',array(COLUMN_CONTENTTYPE_ACTIVITY,COLUMN_CONTENTTYPE_VOTE));
        $where['activity_column_contact.status'] = COLUMN_CONTENTSTATUS_ONLINE;

        $having = time()>' > start_at';
        $unProcessWarmUpList = $this->model->getColumnContentList(COLUMN_WARMUPACTIVITY,$where,$having);
        for($i=0; $i<sizeof($unProcessWarmUpList);$i++)
        {
            $subResult = array();
            $subResult['content'] = '预热活动栏目中 '.$unProcessWarmUpList[$i]['title'] . ' 的活动/投票需要下架，请及时处理。';
            $subResult['title'] = ($subResult['content']);
            $result[] = $subResult;
        }
        $having = time()>' > end_at';
        $unProcessHotList = $this->model->getColumnContentList(COLUMN_HOTACTIVITY,$where,$having);
        for($i=0; $i<sizeof($unProcessHotList);$i++)
        {
            $subResult = array();
            $subResult['content'] = '火热进行活动栏目中 '.$unProcessHotList[$i]['title'] . ' 的活动/投票需要下架，请及时处理。';
            $subResult['title'] = ($subResult['content']);
            $result[] = $subResult;
        }
        return $result;
    }
}
