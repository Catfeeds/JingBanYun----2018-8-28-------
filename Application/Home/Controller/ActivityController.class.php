<?php

namespace Home\Controller;

use Think\Controller;

define('WORKUPLOAD', 0);
define('WORKVIEW', 1);
define('WORKMODIFY', 2);

class ActivityController extends PublicController
{
    private $model;
    private $pageSize = 20;

    public function __construct()
    {
        parent::__construct();
        $this->model = D('Social_activity');
        header("Content-type: text/html; charset=utf-8");
        $this->assign('module', '励耘圈');
        $this->assign('nav', '京版活动');
        $this->assign('navicon', 'jingbanhuodong');
        $this->assign('oss_path', C('oss_path'));
    }

    public function contactFileUpload()
    {
        $resultInfo = A('Home/Common')->baseUploader(8);  //'Resources/activity/contactFile/'
        if ($resultInfo['status']) //upload success
        {
            $this->showMessage(200, '上传成功', $resultInfo);
        } else {
            $this->showMessage(500, '上传失败', $resultInfo);
        }
    }

    public function workFileUpload()
    {
        $resultInfo = A('Home/Common')->baseUploader(9);  //'Resources/activity/workFile/'
        if ($resultInfo['status']) //upload success
        {
            $this->showMessage(200, '上传成功', $resultInfo);
        } else {
            $this->showMessage(500, '上传失败', $resultInfo);
        }
    }
/*
 *往视图里赋值操作
 */
    public function activityWorkInfoAssign($activity_id, $workUserInfo=array(), $userId, $role,$workId=0)
    {
        $info = $this->model->getActivityRegistration($activity_id, $workUserInfo['userId'], $workUserInfo['role']);
        if($workId == 0) {
            if (!empty($info)) {
                $workId = $this->model->getWorkId($info['id']);
            }
        }
            if (!empty($workId)) {
                $works2 = $this->model->getWorksFileInfo($workId);
                $works = $this->model->getResourcesInfo($workId);
                $workInfo = $this->model->getWorksInfo($workId);
                if(ROLE_TEACHER == $role) {
                    $Info = D('Auth_teacher')->getTeacherInfo($workUserInfo['userId']);
                    $workInfo['user_name'] = $Info['name'];
                }elseif (ROLE_STUDENT == $role){
                    $Info = D('Auth_student')->getStudentInfo($workUserInfo['userId']);
                    $workInfo['user_name'] = $Info['name'];
                }elseif (ROLE_PARENT == $role){
                    $Info = D('Auth_parent')->getParentInfo($workUserInfo['userId']);
                    $workInfo['user_name'] = $Info['name'];
                }
                $this->assign('workInfo', ($workInfo));
            }
            $this->assign('role',$role);
            $this->assign('courses', $info);
            $this->assign('existedZan', $this->model->getWorksIsZan($workId, $userId, $role));
            $this->assign('existedFavor', $this->model->getWorksIsFavor($workId, $userId, $role));
            if (!empty($works)) {
                $this->assign('works', ($works));
            }
            if(!empty($works2)){
                $this->assign('works2', ($works2));
            }

    }
    public function myActivityWorks()
    {
        $this->activityWorkDetails();

    }
/*
 *查看自己上传的资料
 */
    public function activityWorkDetails()
    {

        $id = getParameter('id', 'int');
        $own = getParameter('own','str',false);
        //get act id and teacher id
        $info = $this->model->getWorksInfo($id);
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')){
            $userId = getParameter('userId','int');
            $role = getParameter('role','int');
        }else{
                $controller = new \Home\Controller\CommonController();
                $controller->getUserIdRole($userId, $role);
				if(-1 == $role)
					exit;
                switch ($role) {
                    case ROLE_TEACHER :
                        layout('teacher_layout_1');
                        break;
                    case ROLE_STUDENT :
                        layout('student_layout_1');
                        break;
                    case ROLE_PARENT  :
                        layout('parent_layout_1');
                        break;
                    default:
                        break;
                }
        }
        $this->activityWorkInfoAssign($info['activity_id'], array('userId'=>$info['user_id'],'role'=>$info['role']), $userId, $role,$id);
        if ($info['works_show_status'] == 0) {
            if ($info['user_id'] != $userId || $info['role'] != $role) {
                $this->error('该作品暂不能查看');
            }
        }

        $result = $this->model->getActivityDetails($info['activity_id']);
        $this->assign('data',$result);
        $this->assign('subnav', '作品详情');
        //browse plus 1
        $this->model->addBrowseCount($id);
        if(!empty($own)){
            $this->display('workOwnDetails');
        }else{
            $this->display();
        }

    }

    /*
     *查看自己的作品
     */
    public function see_own_work_details(){

    }

    public function zanWork()
    {
        $id = getParameter('id', 'int');
        A('Home/Common')->getUserIdRole($userId,$role);;
        A('ApiInterface/' . APIINTERFACE_DIR . '/Activity')->operationWorksZan($id, $userId, $role);
    }

    public function favorWork()
    {
        $id = getParameter('id', 'int');
        A('Home/Common')->getUserIdRole($userId,$role);;
        A('ApiInterface/' . APIINTERFACE_DIR . '/Activity')->operationWorksFavor($id, $userId, $role);
    }
/*
 *查看获奖作品
 */
    public function activityWorks()
    {
        $activity_id = getParameter('id', 'int');
        $keyword = getParameter('keyword', 'str', false);
        $pageIndex = getParameter('p', 'int', false);

        if (empty($pageIndex))
            $pageIndex = getParameter('pageIndex', 'int', false);
        if (empty($pageIndex))
            $pageIndex = 1;
        if(!(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android'))) {
            A('Home/Common')->getUserIdRole($userId,$role);;
            switch ($role) {
                case ROLE_TEACHER :
                    layout('teacher_layout_1');
                    break;
                case ROLE_STUDENT :
                    layout('student_layout_1');
                    break;
                case ROLE_PARENT  :
                    layout('parent_layout_1');
                    break;
                default:
                    break;
            }
        }
        $result = $this->model->getActivityWorks($activity_id, $keyword, $pageIndex, C('PAGE_SIZE_FRONT'), $count);
        for ($i = 0; $i < count($result); $i++) {
          $result[$i]['ranking'] = $result[$i]['point'];
        }
        $info = $this->model->getWorksInfo($activity_id);
        $activityInfo = $this->model->getActivityDetails($activity_id);
        $info['activity_id'] = $activity_id;
        $info['title'] = $activityInfo['title'];

        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();
        if (!empty($keyword)) {
            $Page->parameter['keyword'] = $keyword;
        }

        $this->assign('info', $info);
        $this->assign('keyword', $keyword);
        $this->assign('works', $result);
        $this->assign('activity_id',$activity_id);
        $this->assign('activity_title',$activityInfo['display_work_title']);
        $this->assign('class_id', $activityInfo['class_id']);
        $this->assign('page', $show);
        $this->display();
    }

    private function extractGrades($period, $gradeList)
    {
        $returnArray = $gradeList;
        switch ($period) {
            case 1:
                $extractIndex = array(1, 2, 3, 4, 5, 6);
                break;
            case 2:
                $extractIndex = array(7, 8, 9);
                break;
            case 3:
                $extractIndex = array(10, 11, 12);
                break;
            default :
                return $returnArray;
                break;
        }
        $returnArray = array();
        for ($i = 0; $i < count($extractIndex); $i++) {
            $returnArray[] = $gradeList[$extractIndex[$i] - 1];
        }
        return $returnArray;
    }

    /*
     * 精通活动列表页面
     */
    public function activities($cat = 0)
    {
        /*$argc = D('Social_activity')->get_column_resource();
        $argc = D('Social_activity')->get_column();
        echo $argc;die;*/
        /*$argc = D('Social_activity')->get_index_list(5,5);
        var_dump($argc);die;*/
        /*$count = 0;
        $argc = D('Social_activity')->getActivities('2','','','',$count,'','','');
        var_dump($argc);die;*/
        //var_dump($teacher = session('auth_teacher'));die;
        //print_r($_GET);die();
        //$id = $_GET['auth_id'];
        $id = getParameter('auth_id', 'int', false);
        A('Home/Common')->getUserIdRole($userId,$role);;
        if ($userId == 0) {
            $this->showMessage(500, '缺少用户ID');
        }
        if ($role == 0) {
            $this->showMessage(500, '缺少用户类型');
        }

        A('Home/Common')->authJudgement($role);


        $this->assign('module', '励耘圈');
        $this->assign('nav', '京版活动');
        $this->assign('subnav', '活动列表');
        $this->assign('navicon', 'jingbanhuodong');

        /*$class_id = ($cat == 0) ? getParameter('class_id', 'int', false) : $cat;
        $pc = getParameter('pc', 'str', false);*/

        $layoutPrefix = '';
        switch ($role) {
            case ROLE_TEACHER :
                $layoutPrefix = 'teacher_layout_';
                $OldController = 'Teach';
                break;
            case ROLE_STUDENT :
                $layoutPrefix = 'student_layout_';
                $OldController = 'Student';
                break;
            case ROLE_PARENT  :
                $layoutPrefix = 'parent_layout_';
                $OldController = 'Parent';
                break;
            default:
                $layoutPrefix = 'teacher_layout_';
                $OldController = 'Teach';
                break;
        }
        /*if ($pc == 'my')
            layout($layoutPrefix . 'withouticon');
        else*/
            layout($layoutPrefix . '1');

        /*$myCategory = getParameter('mycat', 'int', false);*/

        //首页数据
        $status = $this->model->column_details(4);//预热状态是否显示
        $status1 = $this->model->column_details(5);//火热状态是否显示
        //var_dump($status);die;
        $resultLists = $this->model->get_index_list('', 4);//预热活动
        $resultLists1 = $this->model->get_index_list('', 5);//火热进行中
        //var_dump($resultLists);die;
        $wheres['social_activity.activityend'] = array('LT', time());
        $resultLists2 = $this->model->getActivities('', '', '', '', $count, '', '', '', 3);//历史活动
        $argcs = $this->model->get_column();
        $argc = array_column($argcs, 'id');
        //var_dump($argc);die;
        foreach ($argc as $value) {
            $argv[$value] = $this->model->get_column_resource($value);//栏目

        }
        //var_dump($resultLists2);die;

        /*if (0 == $pageIndex && $class_id != 0)
            $pageIndex = 1;
        $count = 0;*/
        /*if ($pc == 'my')
            $resultList = $this->model->getActivities($class_id, $keyword, $pageIndex, C('PAGE_SIZE_FRONT'), $count, $role, $userId, $myCategory);
        else
            $resultList = $this->model->getActivities($class_id, $keyword, $pageIndex, C('PAGE_SIZE_FRONT'), $count);*/

        /*if (!empty($class_id) && $class_id != '') {
            $this->assign('class_id', $class_id);
        } else {
            $this->assign('class_id', 0);
        }*/

        $this->assign('status',$status['is_display']);
        $this->assign('status1',$status1['is_display']);
        $this->assign('role', $role);
        $this->assign('list', $resultLists);
        $this->assign('list1', $resultLists1);
        //var_dump($resultLists);die;
        $this->assign('column_list', $argv);
        $this->assign('column', $argcs);
        $this->assign('history', $resultLists2);
        $this->assign('oss',C('oss_path'));
        //$this->assign('keyword',$keyword);
        $this->assign('OldController', $OldController);

        $this->display_nocache('newActivityBase');
    }

    /*
     *个人中心
     */
    public function my(){
        $category = getParameter('mycat','int',false);
        $this->mycatdata = $category;
        A('Home/Common')->getUserIdRole($userId,$role);
        if($userId == 0)
        {
            $this->showMessage(500,'缺少用户ID');
        }
        if($role == 0)
        {
            $this->showMessage(500,'缺少用户类型');
        }

        //A('Home/Common')->authJudgement($role);

        $this->assign('module', '励耘圈');
        $this->assign('nav', '京版活动');
        $this->assign('subnav', '活动列表');
        $this->assign('navicon', 'jingbanhuodong');
        $layoutPrefix = '';
        switch ($role)
        {
            case ROLE_TEACHER : $layoutPrefix = 'teacher_layout_';
                $OldController = 'Teach';
                break;
            case ROLE_STUDENT : $layoutPrefix = 'student_layout_';
                $OldController = 'Student';
                break;
            case ROLE_PARENT  : $layoutPrefix = 'parent_layout_';
                $OldController = 'Parent';
                break;
            default:
                $layoutPrefix = 'teacher_layout_';
                $OldController = 'Teach';
                break;
        }
        layout($layoutPrefix.'withouticon');
        $keyword = getParameter('keyword','str',false);
        if(1 == $category) {
            //我参加的活动
            $resultLists = $this->model->getActivities(1, $keyword, 1, 4, $count, $role, $userId, 2, '');//赛事
            $result = $this->model->getActivities(2, $keyword, 1, 4, $count, $role, $userId, 2, '');//培训
        }
        else
        {
            $resultLists = $this->model->getActivities(1, $keyword, 1, 4, $count, $role, $userId, 1, '');//赛事
            $result = $this->model->getActivities(2, $keyword, 1, 4, $count, $role, $userId, 1, '');//培训
        }

        if ($keyword != NULL) {
            $this->assign('kw',1);
        }
       // var_dump($result);die;
        $this->assign('list', $resultLists);
        $this->assign('list2', $result);
        $this->assign('count',$count);
        $this->assign('keyword',$keyword);
        $this->assign('OldController',$OldController);
        $this->display_nocache('newMyActivity');
    }

    /*
     *个人中心更多
     */
    public function get_more_my(){
        $id = getParameter('id', 'int', false);
        $category = getParameter('mycat', 'int', false);
        A('Home/Common')->getUserIdRole($userId,$role);
        if($userId == 0)
        {
            $this->showMessage(500,'缺少用户ID');
        }
        if($role == 0)
        {
            $this->showMessage(500,'缺少用户类型');
        }

        //A('Home/Common')->authJudgement($role);

        $this->assign('module', '励耘圈');
        $this->assign('nav', '京版活动');
        $this->assign('subnav', '活动列表');
        $this->assign('navicon', 'jingbanhuodong');
        $layoutPrefix = '';
        switch ($role)
        {
            case ROLE_TEACHER : $layoutPrefix = 'teacher_layout_';
                $OldController = 'Teach';
                break;
            case ROLE_STUDENT : $layoutPrefix = 'student_layout_';
                $OldController = 'Student';
                break;
            case ROLE_PARENT  : $layoutPrefix = 'parent_layout_';
                $OldController = 'Parent';
                break;
            default:
                $layoutPrefix = 'teacher_layout_';
                $OldController = 'Teach';
                break;
        }
        layout($layoutPrefix.'withouticon');
        $keyword = getParameter('keyword','str',false);
        if ($keyword!=NULL) {
            $this->assign('kw',1);
        }
        if( 1 == $category) {
            $resultLists = $this->model->getActivities($id, $keyword, '', '', $count, $role, $userId, 2, '');
        }
        else
        {
            $resultLists = $this->model->getActivities($id, $keyword, '', '', $count, $role, $userId, 1, '');
        }
         //var_dump($resultLists);die;
        $Page = new \Think\Page($count, 5);
        $show = $Page->show();
        $this->assign('id',$id);
        $this->assign('page', $show);
        $this->assign('list', $resultLists);
        $this->assign('keyword',$keyword);
        $this->assign('OldController',$OldController);
        $this->display_nocache('newMyActivityMore');
    }

    /*
     *搜索页
     */
    public function search()
    {
        A('Home/Common')->getUserIdRole($userId,$role);;
        if ($userId == 0) {
            $this->showMessage(500, '缺少用户ID');
        }
        if ($role == 0) {
            $this->showMessage(500, '缺少用户类型');
        }

        //A('Home/Common')->authJudgement($role);


        $this->assign('module', '励耘圈');
        $this->assign('nav', '京版活动');
        $this->assign('subnav', '活动列表');
        $this->assign('navicon', 'jingbanhuodong');

        /*$class_id = ($cat == 0) ? getParameter('class_id', 'int', false) : $cat;
        $pc = getParameter('pc', 'str', false);*/

        $layoutPrefix = '';
        switch ($role) {
            case ROLE_TEACHER :
                $layoutPrefix = 'teacher_layout_';
                $OldController = 'Teach';
                break;
            case ROLE_STUDENT :
                $layoutPrefix = 'student_layout_';
                $OldController = 'Student';
                break;
            case ROLE_PARENT  :
                $layoutPrefix = 'parent_layout_';
                $OldController = 'Parent';
                break;
            default:
                $layoutPrefix = 'teacher_layout_';
                $OldController = 'Teach';
                break;
        }
        /*if ($pc == 'my')
            layout($layoutPrefix . 'withouticon');
        else*/
        layout($layoutPrefix . '1');

        $tip = getParameter('tip','int',false);
        if($tip == 1){
            $type = getParameter('type', 'int', false); //活动分类
        }elseif ($tip == 2){
            $type = getParameter('type', 'int', false); //活动分类
            $time_status = getParameter('time_status', 'int', false); //未开始、进行中
        }
        else{
            $time_status = getParameter('time_status', 'int', false); //未开始、进行中
        }


        // var_dump($time_status);die;
        $keyword = trim(getParameter('keyword', 'str', false));
        $pageIndex = getParameter('p', 'int', false);
        //var_dump($pageIndex);die;
        $resultLists = $this->model->getActivities($type, $keyword, $pageIndex, '10', $count, '', '', '', $time_status);
         //var_dump($resultLists);die;
        //var_dump($count);die;
        $Page = new \Think\Page($count, 10);
        //$array = array('type' => $type, 'time_status' => $time_status, 'keyword' => $keyword, 'pageIndex' => $pageIndex);
        $array = array('type' => $type, 'time_status' => $time_status, 'keyword' => $keyword,'tip' => $tip);
        $Page->parameter = $array;
        $show = $Page->show();
        //var_dump($show);die;
        $this->assign('page', $show);
        $this->assign('list', $resultLists);
        $this->assign('count', $count);
        $this->assign('keyword',$keyword);
        $this->assign('time_status', $time_status);
        $this->assign('type', $type);
        $this->assign('module', '励耘圈');
        $this->assign('nav', '京版活动');
        $this->assign('subnav', '活动列表');
        $this->assign('navicon', 'jingbanhuodong');
        $this->display('newActivitySearch');
    }

    /*
     *历史活动更多
     */
    public function activity_history_more()
    {
        A('Home/Common')->getUserIdRole($userId,$role);;
        if ($userId == 0) {
            $this->showMessage(500, '缺少用户ID');
        }
        if ($role == 0) {
            $this->showMessage(500, '缺少用户类型');
        }

        //A('Home/Common')->authJudgement($role);


        $this->assign('module', '励耘圈');
        $this->assign('nav', '京版活动');
        $this->assign('subnav', '活动列表');
        $this->assign('navicon', 'jingbanhuodong');

        /*$class_id = ($cat == 0) ? getParameter('class_id', 'int', false) : $cat;
        $pc = getParameter('pc', 'str', false);*/

        $layoutPrefix = '';
        switch ($role) {
            case ROLE_TEACHER :
                $layoutPrefix = 'teacher_layout_';
                $OldController = 'Teach';
                break;
            case ROLE_STUDENT :
                $layoutPrefix = 'student_layout_';
                $OldController = 'Student';
                break;
            case ROLE_PARENT  :
                $layoutPrefix = 'parent_layout_';
                $OldController = 'Parent';
                break;
            default:
                $layoutPrefix = 'teacher_layout_';
                $OldController = 'Teach';
                break;
        }
        /*if ($pc == 'my')
            layout($layoutPrefix . 'withouticon');
        else*/
        layout($layoutPrefix . '1');

        $type = getParameter('type','str',false);
        $keyword = trim(getParameter('keyword', 'str', false));
        $pageIndex = getParameter('p', 'int', false);
//$resultLists = $this->model->get_index_list('', 4);//预热活动$where['activity_column_contact.column_id']= $column_id;
        if($type == 4){
            //$where[]
            $resultLists = $this->model->get_more_list($count,4,$keyword, $pageIndex, 10);//var_dump($resultLists);die;//echo M()->getLastSql();die;
            $title = '预热活动';
        }elseif ($type == 5){
            $resultLists = $this->model->get_more_list($count,5,$keyword, $pageIndex, 10);//echo M()->getLastSql();die;
            $title = '火热进行';
        }else{
            $resultLists = $this->model->getActivities('', $keyword, $pageIndex, 10, $count, '', '', '', 3);//var_dump($resultLists);die;
            $title = '历史活动';
        }
        $this->assign('title',$title);
        $this->assign('type',$type);
        $Page = new \Think\Page($count, 10);
        $array = array('keyword' => $keyword, 'type'=>$type);
        $Page->parameter = $array;
        $show = $Page->show();
        $this->assign('page', $show);
        //var_dump($resultLists);die;
        $this->assign('count',$count);
        $this->assign('keyword', $keyword);
        $this->assign('list', $resultLists);
        $this->assign('oss',C('oss_path'));
        $this->display('newActivityMore');
    }

    /*
     *专栏更多
     */
    public function get_column_more()
    {
        A('Home/Common')->getUserIdRole($userId,$role);;
        if ($userId == 0) {
            $this->showMessage(500, '缺少用户ID');
        }
        if ($role == 0) {
            $this->showMessage(500, '缺少用户类型');
        }

       // A('Home/Common')->authJudgement($role);


        $this->assign('module', '励耘圈');
        $this->assign('nav', '京版活动');
        $this->assign('subnav', '活动列表');
        $this->assign('navicon', 'jingbanhuodong');

        /*$class_id = ($cat == 0) ? getParameter('class_id', 'int', false) : $cat;
        $pc = getParameter('pc', 'str', false);*/

        $layoutPrefix = '';
        switch ($role) {
            case ROLE_TEACHER :
                $layoutPrefix = 'teacher_layout_';
                $OldController = 'Teach';
                break;
            case ROLE_STUDENT :
                $layoutPrefix = 'student_layout_';
                $OldController = 'Student';
                break;
            case ROLE_PARENT  :
                $layoutPrefix = 'parent_layout_';
                $OldController = 'Parent';
                break;
            default:
                $layoutPrefix = 'teacher_layout_';
                $OldController = 'Teach';
                break;
        }
        /*if ($pc == 'my')
            layout($layoutPrefix . 'withouticon');
        else*/
        layout($layoutPrefix . '1');

        $id = getParameter('id', 'int', false);
        $resultLists = $this->model->get_column_more($id,10);
        //var_dump($resultLists['list']);die;
        $this->assign('page', $resultLists['page']);
        $this->assign('list', $resultLists['list']);
        $this->assign('oss',C('oss_path'));
        $this->display('newColumnList');
    }


    /*
     *投票详情
     */
    public function vote_details()
    {
        //var_dump($_SESSION);die;
        A('Home/Common')->getUserIdRole($userId,$role);;
        if ($userId == 0) {
            $this->showMessage(500, '缺少用户ID');
        }
        if ($role == 0) {
            $this->showMessage(500, '缺少用户类型');
        }
        $activity_id = getParameter('activity_id','int',false);
        $this->assign('activity_id',$activity_id);

        //A('Home/Common')->authJudgement($role);


        $this->assign('module', '励耘圈');
        $this->assign('nav', '京版活动');
        $this->assign('subnav', '活动列表');
        $this->assign('navicon', 'jingbanhuodong');

        $layoutPrefix = '';
        switch ($role) {
            case ROLE_TEACHER :
                $layoutPrefix = 'teacher_layout_';
                $OldController = 'Teach';
                break;
            case ROLE_STUDENT :
                $layoutPrefix = 'student_layout_';
                $OldController = 'Student';
                break;
            case ROLE_PARENT  :
                $layoutPrefix = 'parent_layout_';
                $OldController = 'Parent';
                break;
            default:
                $layoutPrefix = 'teacher_layout_';
                $OldController = 'Teach';
                break;
        }
        layout($layoutPrefix . '1');

            if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')){
                $userId = getParameter('userId','int',false);
                $role = getParameter('role','int',false);
            }else{
				$controller = new \Home\Controller\CommonController();
                $controller->getUserIdRole($userId, $role);
				if(-1 == $role);
					//exit;
            }
            $vote_id = getParameter('id','int');
            $result = D('Social_activity_vote')->getCanVoteAndVoteData($vote_id,$userId,$role);
            $voteInfo = D('Social_activity_vote')->getVoteData($vote_id);
            //活動列表

            if ( $voteInfo['voteend']<time() ) {
                $details = D('Social_activity_vote')->getCandidateListOrder($vote_id);
            } else {
                $details = D('Social_activity_vote')->getCandidateList($vote_id);
            }

            $if_vote = D('Social_activity_vote')->if_vote($userId,$vote_id,$role);

            //var_dump($details);
            $this->assign('num',$result['data']);
            $this->assign('voteInfo',$voteInfo);
            $this->assign('details',$details);
            //$this->assign('if_vote',$if_vote['if_vote']);
            //$this->assign('vc_id',$if_vote['vote_candidate']);
            $this->assign('oss',C('oss_path'));
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')){
                redirect('ApiInterface/Version1_1/Activity/votingDetails?id='.$vote_id.'&activity_id='.$activity_id);
            }else{
                $this->display('newVote');
            }
    }

    /*
     *投票操作
     */
    public function ajax_action_vote()
    {
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')){
            $userId = getParameter('userId','str');
            $role = getParameter('role','int');
            $data['user_id'] = $userId;
            $data['role'] = $role;

        }else{
            A('Home/Common')->getUserIdRole($userId,$role);;
            $data['user_id'] = $userId;
            $data['role'] = $role;
        }

        $vote_candidate_id = getParameter('id','int');
        $voteId = D('Social_activity_vote')->getVoteIdByCandidateId($vote_candidate_id);
        if(0 == $voteId)
            $this->ajaxReturn('error');
        $result = D('Social_activity_vote')->getCanVoteAndVoteData($voteId,$userId,$role);
        if($result['canVote'] == 0)
            $this->ajaxReturn('full');
        $data['vote_candidate_id'] = $vote_candidate_id;
        $data['create_at'] = time();
        $vote = M('activity_vote_data');
        $vote->startTrans();
        $status = $vote->add($data);
        if($status == false){
            $vote->rollback();
            $this->ajaxReturn('error');
        }else{
            $vote->commit();
        $this->ajaxReturn('success');
        }
    }

    /*
     *查看当前活动是否绑定投票
     */
    public function is_vote($activity_id)
    {
        //判断当前域名是否为线上或预发布环境
        //var_dump($_SERVER['HTTP_HOST']);die;
        if(WEB_URL == 'www.jingbanyun.com' || $_SERVER['HTTP_HOST'] == 'test.jingbanyun.com'){
            $this->assign('environment','true');
        }
       // $activity_id = 427;
        $is_true = D('Social_activity_vote')->is_votes($activity_id);
        $this->assign('is_true',$is_true['result']);
        //var_dump($is_true);die;
    }

    /*
     *查看列表
     */
    public function ajax_vote(){
        $id = getParameter('id','int');
        $result = D('Social_activity_vote')->is_votes($id);
        $this->ajaxReturn(array('status' => 200,'result'=>$result['value']));
    }
    /*
     *我收藏的活动作品
     */
    function myWorks()
    {
        A('Home/Common')->getUserIdRole($userId,$role);;
        if ($role >= 2 && $role <= 4) {
            $pageIndex = getParameter('p', 'int', false);
            $keyword = getParameter('keyword', 'str', false);
            $period = getParameter('period', 'int', false); //学段
            $course = getParameter('course', 'int', false); //学科
            $grade = getParameter('grade', 'int', false);   //年级
            $category = getParameter('category', 'int', false); //类型

            if ($keyword != NULL||$period != NULL||$course != NULL||$grade != NULL||$category != NULL) {
                $this->assign('kw',1);
            }

            $gradeList = $this->extractGrades($period, D('Dict_grade')->getGradeList());

            $result = $this->model->getMyCollectActivityWorks($userId, $role, $keyword, $pageIndex, C('PAGE_SIZE_FRONT'), $count, $period, $course, $grade, $category);
        } else {
            $this->showMessage(500, '参数有误');
        }
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));//
        $Page->parameter = $_GET;
        //unset($Page->parameter['p']);
        $show = $Page->show();
        //var_dump($result);exit;
        $this->assign('data', $result);
        $this->assign('page', $show);
        $this->assign('courses', D('Dict_course')->getCourseList());
        $this->assign('categorys', $this->model->getActivityClassList(5)); //作品评比CLASS
        $filterSelect['keyword'] = $keyword;
        $filterSelect['period'] = $period;
        $filterSelect['course'] = $course;
        $filterSelect['grade'] = $grade;
        $filterSelect['category'] = $category;
        $this->assign('filterSelect', $filterSelect);
        $this->assign('grades', $gradeList);
        switch ($role) {
            case ROLE_TEACHER :
                layout('teacher_layout_withouticon');
                break;
            case ROLE_STUDENT :
                layout('student_layout_withouticon');
                break;
            case ROLE_PARENT  :
                layout('parent_layout_withouticon');
                break;
            default:
                break;
        }
        $this->display();
    }
/*
 *上传作品和修改作品
 */
    public function activityWorkPublish()
    {
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')){
            $userId = getParameter('userId','int');
            $role = getParameter('role','int');
        }else {
            $controller = new \Home\Controller\CommonController();
            $controller->getUserIdRole($userId, $role);
            if (-1 == $role)
                exit;
        }
        $activity_id = getParameter('id', 'int');
        $model = $this->model;
        $activity_info = $model->getActivityDetails($activity_id);
        if (($activity_info['applystart'] <= time() && $activity_info['applyend'] >= time()) == false) {
            $this->showMessage(500, '提交失败, 活动报名已结束');
        }
        $info = $model->getActivityRegistration($activity_id, $userId,$role);
        if (empty($info)) {
            $this->showMessage(500, '您尚未报名');
        }
        $regId = $info['id'];
        $workId = $model->getWorkId($regId);
        //如果已经上传资料又再次在初次上传作品页提交

        if($workId && strpos($_SERVER['HTTP_REFERER'],'teachDesignWorkUpload') !== false){
            $this->showMessage(500, '您已经提交了作品,无法再次提交');
        }
        if ($_POST) {
            if($role == ROLE_TEACHER && in_array('1',explode(',',$activity_info['upload_info']))){
                $grade_id = getParameter('grade_id', 'int');
            }

            if(in_array('3',explode(',',$activity_info['upload_info']))){
                $name = getParameter('name', 'str');
            }

            if(in_array('4',explode(',',$activity_info['upload_info']))){
                $description = getParameter('description', 'str');
            }

            $file_name = getParameter('file_name', 'str');
            $file_path = getParameter('unique_string', 'str');

            $image_path = getParameter('vid_image_path', 'str');
            $is_transition = getParameter('vid_transition', 'str');
            $file_type = getParameter('file_category', 'str', false);
            $vid = getParameter('vid', 'str');
            $vid_fullpath = getParameter('vid_fullpath', 'str');
            $authorContent = getParameter('authorContent', 'str', false);

            $fileType = array();
            $fileNameArray = explode(',', $file_name);
            foreach($fileNameArray as $key=>$val){
                $fileNameArray[$key] = urldecode($val);
            }
            $filePathArray = explode(',', $file_path);
            foreach($filePathArray as $key=>$val){
                $filePathArray[$key] = urldecode($val);
            }
            for ($i = 0; $i < count($fileNameArray); $i++) {
                $fileType[] = getFileType($filePathArray[$i]);
            }
            $fileCategoryArray = explode(',', $file_type);
            for ($i = 0; $i < count($fileNameArray); $i++) {
            if(empty($fileCategoryArray[$i]))
                $fileCategoryArray[$i] = 0;
            }
            $workData = array('file_path' => $filePathArray,
                'image_path' => explode(',', $image_path),
                'file_category' => $fileCategoryArray,
                'vid' => explode(',', $vid),
                'vid_fullpath' => explode(',', $vid_fullpath),
                'is_transition' => explode(',', $is_transition),
                'file_name' => $fileNameArray,
                'type' => $fileType,
            );


            if (empty($workId))
                $isAdd = true;
            else
                $isAdd = false;

            $data = array(
                'course' => empty($info['course_id']) ? '' : $info['course_id'],
                'grade' => empty($grade_id) ? '' : $grade_id,
                'works_name' => empty($name) ? '' : $name,
                'works_description' => empty($description) ? '' : $description,
                'author_remarks' => $authorContent,
                'voted_title' => $info['lesson'],
                'status' => ACTIVITY_WORK_STATUS_WAITFORVERIFY
            );
            if(empty($info['course_id']))
                unset($data['course']);
            if ($isAdd) {
                $data['create_at'] = time();
            } else {
                $data['update_at'] = time();
            }
            if ($model->saveWorkInfo($isAdd, $regId, $data, $workData))
                $this->showMessage(200, '添加/修改成功');
            else
                $this->showMessage(500, '添加/修改失败');
        }
    }

    public function activityDetails($id)
    {
        A('Home/Common')->getUserIdRole($userId,$role);;
        if (!$this->model->getActivityExists($id)) {
            $this->error('该活动不存在');
        }
        switch ($role) {
            case ROLE_TEACHER :
                layout('teacher_layout_1');
                break;
            case ROLE_STUDENT :
                layout('student_layout_1');
                break;
            case ROLE_PARENT  :
                layout('parent_layout_1');
                break;
            default:
                layout('teacher_layout_1');
                break;
        }

        $this->assign('module', '励耘圈');
        $this->assign('nav', '京版活动');
        $this->assign('subnav', '活动详情');
        $this->assign('navicon', 'jingbanhuodong');

        if ($role == -1)
            $role = ROLE_TEACHER;
        A('ApiInterface/' . APIINTERFACE_DIR . '/Activity')->activityDetails($id, $userId, $role);

    }



    //作品评比活动详情
    public function activityApplyDetails()
    {
        $activity_id = getParameter('id', 'int');
        $this->assign('activity_id',$activity_id);
        //check if this activity exists
        if (!$this->model->getActivityExists($activity_id)) {
            $this->error('该活动不存在');
        }
        A('Home/Common')->getUserIdRole($userId,$role);
        //$tips = $this->model->registered_but_no_uploadworks($userId,$role);
        //var_dump($tips);die;

        A('Home/Activity')->activityWorkInfoAssign($activity_id, array('userId'=>$userId,'role'=>$role));

        $hasUploadWork = $this->model->getHasUploadWorks($activity_id, $userId,$role);
        $this->assign('hasUploadWork', $hasUploadWork);

        $this->assign('courselist', $course_result = D('Auth_teacher')->getTeacherAllCourse($userId));
        $this->assign('grades', $grade_result = $this->model->getAvailableRegGrade($userId, $activity_id));
        $this->assign('apiVersion', APIINTERFACE_DIR);
        $this->assign('education', c('education'));//教师学历
        $this->assign('positions', c('professional'));//教师职称
        $this->assign('roles',$role);

        $this->model->qiPaoCount($activity_id);

        $this->activityDetails($activity_id);
    }

    public function teachDesignWorkModify()
    {
        $workId = getParameter('workId', 'int');
        $workTeacherInfo = $this->model->getWorksInfo($workId);

        A('Home/Common')->getUserIdRole($userId,$role);;
        if ($role != ROLE_TEACHER || $workTeacherInfo['user_id'] != $userId)
            $this->error('不能修改其他人的作品');
        $this->assign('pageType', WORKMODIFY);
        $this->worksUpload($workTeacherInfo['activity_id'], $workTeacherInfo['user_id']);
    }

    public function teachDesignWorkView()
    {
        $workId = getParameter('workId', 'int');
        $workTeacherInfo = $this->model->getWorksInfo($workId);
        A('Home/Common')->getUserIdRole($userId,$role);;
        if ($workTeacherInfo['works_show_status'] == 0) {
            if ($role != ROLE_TEACHER || $workTeacherInfo['user_id'] != $userId) {
                echo $role;exit;
                $this->error('该作品暂不能查看');
            }
        }

        $this->assign('pageType', WORKVIEW);
        $this->worksUpload($workTeacherInfo['activity_id'], $workTeacherInfo['user_id']);
    }

    public function teachDesignWorkUpload()
    {
        $activityId = getParameter('id', 'int');
        A('Home/Common')->getUserIdRole($userId,$role);;
        if ($role != ROLE_TEACHER)
            $this->error('您没有上传作品的权限');
        $regInfo = $this->model->getRegisterInfo($activityId, $userId);
        $workId = $this->model->getWorkId($regInfo['regid']);
        if (!empty($workId))
            $this->error('无法再次上传作品');
        $this->assign('pageType', WORKUPLOAD);
        $this->worksUpload($activityId, $userId);
    }

    public function worksUpload($activityId = 0, $workTeacherId = 0)
    {
        if (0 == $activityId)
            $activityId = getParameter('id', 'int');
        if (!$this->model->getActivityExists($activityId)) {
            $this->error('该活动不存在');
        }

        A('Home/Common')->getUserIdRole($userId,$role);;

        if ($workTeacherId == 0) {
            A('Home/Activity')->activityWorkInfoAssign($activityId, array('userId'=>$workTeacherId,'role'=>ROLE_TEACHER), $userId, $role);
            $hasUploadWork = $this->model->getHasUploadWorks($activityId, $userId);
        } else {
            A('Home/Activity')->activityWorkInfoAssign($activityId, array('userId'=>$workTeacherId,'role'=>ROLE_TEACHER), $userId, $role);
            $hasUploadWork = $this->model->getHasUploadWorks($activityId, $workTeacherId);
        }
        $this->assign('hasUploadWork', $hasUploadWork);

        $this->assign('courselist', $course_result = D('Auth_teacher')->getTeacherAllCourse($userId));
        $this->assign('grades', $grade_result = $this->model->getAvailableRegGrade($userId, $activityId));
        $this->assign('isSelfWork', $userId == $workTeacherId);
        $this->assign('data', array('id' => $activityId));
        $this->display_nocache('worksUpload');

    }

    //赞一个京版活动
    public function zanActivity()
    {
        $activity_id = getParameter('id', 'int');
        A('Home/Common')->getUserIdRole($userId,$role);;
        if ($activity_id && $userId && $role >= 2 && $role <= 4) {
            $existed_zan = $this->model->getIsZan($activity_id, $userId, $role);
            if ($existed_zan == 'no') {
                $status = $this->model->operationZanActivity(1, $activity_id, $userId, $role);
            } else {
                $status = $this->model->operationZanActivity(2, $activity_id, $userId, $role);
            }
            if ($status == true) {
                $existed_zan = ($existed_zan == 'no') ? 'yes' : 'no';
                $this->ajaxReturn(array('status' => 200, 'info' => 'success', "result" => $existed_zan));
            } else {
                $this->ajaxReturn(array('status' => 400, 'info' => 'error'));
            }
        } else {
            $this->ajaxReturn(array('status' => 400, 'info' => 'error', "result" => 'youke'));
        }
    }

    //收藏一个京版活动
    public function favorActivity()
    {
        $activity_id = getParameter('id', 'int');
        A('Home/Common')->getUserIdRole($userId,$role);;
        if ($activity_id && $userId && $role >= 2 && $role <= 4) {
            $existed_favor = $this->model->getIsFavor($activity_id, $userId, $role);
            if ($existed_favor == 'no') {
                $status = $this->model->operationFavorctivity(1, $activity_id, $userId, $role);
            } else {
                $status = $this->model->operationFavorctivity(2, $activity_id, $userId, $role);
            }
            if ($status == true) {
                $existed_favor = ($existed_favor == 'no') ? 'yes' : 'no';
                $this->ajaxReturn(array('status' => 200, 'info' => 'success', "result" => $existed_favor));
            } else {
                $this->ajaxReturn(array('status' => 400, 'info' => 'error'));
            }
        } else {
            $this->ajaxReturn(array('status' => 400, 'info' => 'error', "result" => 'youke'));
        }
    }

    //老版活动报名
    public function reportActivity()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$data['activity_id'] = $_POST['activity_id'];
        $data['activity_id'] = getParameter('activity_id', 'int');

        $data['user_id'] = session('teacher.id');
        $data['user_name'] = session('teacher.name');
        $data['register_at'] = time();
        $data['user_type'] = 1;
        //$data['register_info'] = $_POST['register_info'];
        //$data['file_path'] = $_POST['file_path'];
        $data['register_info'] = getParameter('register_info', 'str', false);
        $data['file_path'] = getParameter('file_path', 'str', false);

        if (D('Social_activity')->regActivity($data)) {
            $arr['msg'] = '报名成功';
            $arr['code'] = 0;
        } else {
            $arr['msg'] = '报名失败';
            $arr['code'] = -1;
        }
        echo json_encode($arr);
    }

    /*
     * 查看报名信息
     */
    public function registrationInformationJSON4PC()
    {
        $activity_id = getParameter('id', 'int', false);
        A('Home/Common')->getUserIdRole($userId,$role);;

        $activityRegisterInfo = $this->model->getActivityRegistration($activity_id, $userId,$role);
        if (empty($activityRegisterInfo)) {
            //load additional info from last reg info
            if($role == ROLE_TEACHER){
                $user_type = ROLE_TEACHER;
                $registration_info = $this->model->getTeacherSchoolInfo($userId);
                $registration_info['user_name'] = $registration_info['name'];
            }elseif ($role == ROLE_STUDENT){
                $user_type = ROLE_STUDENT;
                $registration_info = $this->model->getStudentSchoolInfo($userId);
                $registration_info['user_name'] = $registration_info['name'];
            }elseif ($role == ROLE_PARENT){
                $user_type = ROLE_PARENT;
                $registration_info = D('Auth_parent')->getParentInfo($userId);
                $registration_info['user_name'] = $registration_info['parent_name'];
            }
            $registration_info['school_province_id'] = $registration_info['province_id'];
            $registration_info['school_city_id'] = $registration_info['city_id'];
            $registration_info['school_district_id'] = $registration_info['district_id'];

            $lastRegInfo = $this->model->getLastRegInfo($userId,$user_type);

            unset($registration_info['teacher_name']);
            unset($registration_info['parent_name']);
            unset($registration_info['student_name']);
            $registration_info['province_id'] = empty($lastRegInfo['province'])?$registration_info['province_id']:$lastRegInfo['province'];
            $registration_info['city_id'] = empty($lastRegInfo['city'])?$registration_info['city_id']:$lastRegInfo['city'];
            $registration_info['district_id'] = empty($lastRegInfo['district'])?$registration_info['district_id']:$lastRegInfo['district'];

            $registration_info['school_province_id'] = $registration_info['province_id'];
            $registration_info['school_city_id'] = $registration_info['city_id'];
            $registration_info['school_district_id'] = $registration_info['district_id'];
            $registration_info['age'] = ($lastRegInfo['age'] == 0) ? '' : $lastRegInfo['age'];

            $registration_info['post_code'] = $lastRegInfo['post_code'];
            $registration_info['tel'] = $lastRegInfo['tel'];
            $registration_info['local_course'] = $lastRegInfo['local_course'];
            $registration_info['school_course'] = $lastRegInfo['school_course'];

            $education = c('education');
            $positions = c('professional');
            foreach ($education as $k => $v) {
                if ($k == $lastRegInfo['education']) {
                    $registration_info['education'] = $v;
                }
            }
            foreach ($positions as $key => $val) {
                if ($key == $lastRegInfo['positions']) {
                    $registration_info['positions'] = $val;
                }
            }

        } else {
            A('Home/Common')->getUserIdRole($userId,$role);;
            $registration_info = $this->model->getActivityRegistration($activity_id, $userId,$role);
            if (!empty($registration_info)) {
                if ($registration_info['applystart'] <= time() && $registration_info['applyend'] >= time()) {
                    $activity_status = 2;
                } else {
                    $activity_status = 1;
                }

                $education = c('education');
                $positions = c('professional');
                foreach ($education as $k => $v) {
                    if ($k == $registration_info['education']) {
                        $registration_info['education'] = $v;
                    }
                }

                foreach ($positions as $key => $val) {
                    if ($key == $registration_info['positions']) {
                        $registration_info['positions'] = $val;
                    }
                }
            }
        }
        //add school province city district data
            $registration_info['school_province_id'] = $registration_info['province_id'];
            $registration_info['school_city_id'] = $registration_info['city_id'];
            $registration_info['school_district_id'] = $registration_info['district_id'];

        $this->ajaxReturn(array('status' => 200, 'result' => $registration_info));
    }

    public function activityDetailShare()
    {
        $activity_id = getParameter('id', 'int');
        if (A('Home/Common')->isMobile()) {
            redirect('/ApiInterface/' . APIINTERFACE_DIR . '/Activity/activityDetails?id=' . $activity_id . '&flag=1');
        }
        $this->activityDetails($activity_id);
    }

    public function getWorkActivityList()
    {
        $resultList = $this->model->getWorkActivityByPeriodCourseGradeCategoryBrief(0, 0, 0, 1, '');

        $this->showMessage(200, 'success', $resultList);
    }

    public function random($length, $chars = '0123456789abcdefghijklmnopqrstuvwxyz')
    {
        $hash = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }

    private function getBaseFilePath($activityTitle, $line)
    {
        return $activityTitle . '\\活动教师报名及作品信息\\' . $line['course_name'] . '\\'
            . $line['grade'] . '\\'
            . $line['district'] . '\\'
            . $line['school_name'] . '\\'
            . $line['district'] . '_' . $line['user_name'] . '_' . trim($line['works_name']) . '\\';
    }

    public function getWorkFileAndDirectoryList()
    {
        $activity_id = getParameter('id', 'int');
        $activityTitle = $this->model->getActivityTitle($activity_id);
        $activityTitle = $activityTitle['title'];
        $activityDetails = $this->model->getActivityDetails($activity_id);

        $fileName = 'hdfa_' . $this->random(10) . '.html';
        $pathPrefix = 'http://' . $_SERVER['SERVER_NAME'];
        $sourcePath = 'tmp/' . $fileName;
        $fp = fopen(iconv('utf-8', 'gb2312', $sourcePath), "w") or $this->showMessage(500, '服务器创建文件失败', array());
        fwrite($fp, iconv('utf-8', 'gb2312', $activityDetails['content']));
        fclose($fp);

        //generate userinfo csvs
        $result = $this->model->getRegisterDetailList($activity_id);
        $title = iconv('utf-8', 'gb2312', "教师姓名,学科,参评课题,区县,学校全称,性别,年龄,学历,职称,电子信箱,学校地址,学校邮编,办公电话,移动电话,地方课程,校本课程\n");
        $returnResult = array();
        $returnResult[] = array('sourcePath' => $pathPrefix . '/' . $sourcePath,
            'destPath' => $activityTitle . '\\活动方案.html'
        );
        $returnResult[] = array('sourcePath' => C('oss_path') . '/Activity/文件夹结构示意图.jpg',
            'destPath' => $activityTitle . '\\文件夹结构示意图.jpg'
        );
        $yesNoArray = array('否', '是');
        for ($i = 0; $i < sizeof($result); $i++) {
            $fileName = 'userInfo_' . $result[$i]['id'] . $this->random(10) . ".csv";
            $sourcePath = 'tmp/' . $fileName;
            $fp = fopen($sourcePath, "w") or $this->showMessage(500, '服务器创建文件失败', array());
            fwrite($fp, $title);
            $line = implode(',', array(
                $result[$i]['user_name'],
                $result[$i]['course_name'],
                $result[$i]['lesson'],
                $result[$i]['district'],
                $result[$i]['school_name'],
                $result[$i]['sex'],
                $result[$i]['age'],
                C('education.' . $result[$i]['education']),
                C('professional.' . $result[$i]['positions']),
                $result[$i]['email'],
                $result[$i]['school_address'],
                $result[$i]['post_code'],
                $result[$i]['tel'],
                $result[$i]['telephone'],
                $yesNoArray[$result[$i]['local_course']],
                $yesNoArray[$result[$i]['school_course']]
            ));
            $line = iconv('utf-8', 'gb2312', $line);
            fwrite($fp, $line);
            fclose($fp);
            $destPath = $this->getBaseFilePath($activityTitle, $result[$i]) . '教师基本信息\\教师基本信息表.csv';

            $returnResult[] = array('sourcePath' => $pathPrefix . '/' . $sourcePath,
                'destPath' => $destPath
            );
        }
        $worksResult = $this->model->getWorkCompareDetailList($activity_id);
        $fileCategoryArray = array('', '教学资源', '教学设计', '教学反思', '教学视频');
        for ($i = 0; $i < sizeof($worksResult); $i++) {
            if ('' == $fileCategoryArray[$worksResult[$i]['file_category']])
                continue;
            $sourcePath = C('oss_path') . $worksResult[$i]['works_file_path'];
            $destPath = $this->getBaseFilePath($activityTitle, $worksResult[$i]) . $fileCategoryArray[$worksResult[$i]['file_category']] . '\\' . $worksResult[$i]['works_file_name'] . '.' . pathinfo($worksResult[$i]['works_file_path'], PATHINFO_EXTENSION);
            $returnResult[] = array('sourcePath' => $sourcePath,
                'destPath' => $destPath
            );
        }
        $this->showMessage(200, 'success', $returnResult);
        //work file list


    }
    private function getLiveAuth($userId,$role,$url)
    {
           return true;
    }

    //展示互动学生观看网页回调
    public function genseeStudentHTML()
    {
        $url = getParameter('url','str');
        $userId = 0;
        $role = 0;
        A('Home/Common')->getUserIdRole($userId,$role);
        $isAuth = $this->getLiveAuth($userId,$role,$url);
        if(($userId == -1 && $role == -1) || (!$isAuth)){
            echo '您没有权限观看此直播';
            return;
        }

        $name = D('Common')->getUserName($userId,$role);
        if(strpos($url,'?') !== false)
        $url .= "&";
        else
        $url .= "?";
        $url.="nickname=$name";

        echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0,maximum-scale=1.0, user-scalable=no\"/><style>*{padding:0;margin:0}</style><iframe frameborder=\"0\" border=\"0\" marginwidth=\"0\" marginheight=\"0\" style=\"height:100%;width:100%\" src=\"$url\" ></iframe>";

    }

    public function viewPolyMedia()
    {
        A('Home/Common')->getUserIdRole($userId,$role);
        if(($userId == -1 && $role == -1)){
            echo '您没有权限观看此直播';
            return;
        }
        $vid = getParameter('vid','str');
        $htmlTemplate = "<head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no\"></head><script src='Public/js/external/polyvplayer.min.js'></script>
                         <div id=\"media\">
                          </div>
                          <script>
                        player = polyvObject('#media').videoPlayer({
						'width': '100%',
						'height': '100%',
						'vid': '$vid'
					});                          
                      </script>";
        echo $htmlTemplate;


    }
}