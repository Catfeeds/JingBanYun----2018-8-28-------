<?php
namespace Exercise\Controller;

use Think\Controller;
use Think\Page;
use Think\Verify;

class UserManagementController extends ExerciseGlobalController
{

    public $model;
    public $page_size = 20;

    //permissions
    public function __construct()
    {
        parent::__construct();
        $this->userInfo = $this->getUserRoleAuth();
        $this->model = D('Exercises_account');
        $this->assign('oss_path', 'http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }

    /*
     *用户管理列表
     */
    function userManagement()
    {
        //$where['account_status'] = ACCOUNT_STATUS_NORMAL;
        $where['exercises_account.delete_status'] = DELETE_STATUS_FALSE;
        $count = 0;
        $page = getParameter('p','int',false);
        if ($_POST) {
            $name = trim(getParameter('name', 'str', false));
            $account = trim(getParameter('account', 'str', false));
            $phone = trim(getParameter('phone', 'str', false));
            if (!empty($name)) {
                $where['exercises_account.user_name'] = $name;
            }
            if (!empty($account)) {
                $where['account'] = $account;
            }
            if (!empty($phone)) {
                $where['mobile_phone'] = $phone;
            }
        }
        $list = $this->model->getAllForList($count, $where,$page);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        if ($where) {
            $Page->parameter = $where;
        }
        if($this->userInfo['id'] == ACCOUNT_SUPERADMIN_ID){
            $this->assign('tip',true);//如果不为超级管理员就给个标识，用作前台判断
        }else{
            $this->assign('tip',false);
        }
        $show = $Page->show();
        $this->assign('name', $name);
        $this->assign('account', $account);
        $this->assign('phone', $phone);
        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->assign('parent', '用户管理');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '');
        $this->display();
    }

    /*
     *禁用账号操作
     */
    public function accountDisable()
    {
        $data['account_status'] = getParameter('account_status', 'int');
        $where['id'] = getParameter('id', 'int');
        $this->model->startTrans();
        $status = $this->model->updateResources($data, $where);
        if ($status === false) {
            $this->model->rollback();
            $this->showjson('400', '请刷新后，重试');
        } else {
            $this->model->commit();
            $this->showjson('200');

        }
    }

    /*
     *账号删除操作
     */
    public function deleteStatus()
    {
        $data['delete_status'] = getParameter('delete_status', 'int');
        $where['id'] = getParameter('id', 'int');
        //判断当前账号是否有待标引的习题是否有录入过习题或试卷，有的话不能删除
        $this->getWaitingForIndexingByAccount($where['id'],true);

        $this->model->startTrans();
        $status = $this->model->updateResources($data, $where);
        if ($status === false) {
            $this->model->rollback();
            $this->showjson('400', '请刷新后，重试');
        } else {
            $this->model->commit();
            $this->showjson('200');
        }
    }

    /**
     *描述：判断此账号有无待标引和录入的习题或者试卷
     */
    public function getWaitingForIndexingByAccount($id='',$type=false)
    {
        $where_id = getParameter('id','str',false);
        if($where_id){
            $id = $where_id;
        }
        $whereStr1 = "marker_id =$id AND exercises_createexercise.is_delete= 2";//标引人
        $whereStr2 = "creator_id=$id AND exercises_createexercise.is_delete= 2";//录入人
        $whereStr3 = "creator_id=$id AND exercises_create_paper.is_delete= 2";//录入试卷人
        $result1 = $this->model->getWaitingForIndexingByAccount($whereStr1);//echo M()->getLastSql();die;严重程度高
        $result2 = $this->model->getWaitingForIndexingByAccount($whereStr2);//echo M()->getLastSql();die;低
        $result3 = $this->model->getPaperByAccount($whereStr3);//echo M()->getLastSql();die;
        if(!empty($result1)){
            $this->showjson('404', '该账号下有标引的习题，不能操作');
        }elseif(!empty($result2)){
        $this->showjson('403', '该账号下有录入的习题，不能操作');
        }elseif (!empty($result3)){
        $this->showjson('402', '该账号下有录入的试卷，不能操作');
        }elseif($type){

        }else{
            $this->showjson('200');
        }
    }

        /*
         *添加用户或修改用户操作
         */
    function addUser()
    {
        if ($_POST) {
            $data['account'] = trim(getParameter('account', 'str'));
            $data['user_name'] = trim(getParameter('name', 'str'));
            $data['password'] = sha1(trim(getParameter('password', 'str')));
            $data['role'] = getParameter('role', 'str');
            $data['mobile_phone'] = trim(getParameter('phone', 'str'));
            if ($data['role'] == ROLE_MARKKNOWLEDGE) {
                $data['Learning_period_id'] = getParameter('Learning_period', 'int');
                $data['course_id'] = getParameter('course', 'int');
                $data['version_id'] = getParameter('version_id', 'iArr');
                $data['version_id'] = implode(',', $data['version_id']);
                $data['superior_content'] = getParameter('superior', 'str');
                $temp = explode('-', $data['superior_content']);
                $data['superior_content'] = $temp[1];
                $data['superior'] = $temp[0];
            } elseif ($data['role'] == ROLE_INTSTRUCTOR) {
                $data['Learning_period_id'] = getParameter('Learning_period', 'int');
                $data['course_id'] = getParameter('course', 'int');
                $data['version_id'] = getParameter('version_id', 'iArr');
                $data['version_id'] = implode(',', $data['version_id']);
            }

            $data['login_ip'] = getParameter('ip', 'str', false);//可有可无
            //添加
            $this->model->startTrans();
            if ($this->model->addResources($data) === false) {
                $this->model->rollback();
            } else {
                $this->model->commit();
                $this->redirect("UserManagement/userManagement");
            }
        }
        //角色
        $roleModel = D('Exercises_auth_permissions');
        $roleList = $roleModel->getResources();
        $this->assign('roleList', $roleList['resources']);
        //教材版本
        $versionModel = D('Exercises_textbook_version');
        $versionList = $versionModel->getResourcesAll(array(), $pageIndex = 1, 1000);
        $this->assign('versionList', $versionList['resources']);
        //学科
        $courseModel = D('Exercises_Course');
        $courseList = $courseModel->getCourseList();
        $this->assign('courseList', $courseList);
        //上级
        $where3['role'] = ROLE_INTSTRUCTOR;//教研员角色ID
        $superior = $this->model->getResources($where3);
        //拼接
        foreach ($superior['resources'] as $item) {
            $str[$item['id']] = $item['user_name'] . ',' . $item['account'] . ',' . $item['course_name'] . ',' . $item['learning_period'];
        }
        $this->assign('superior', $str);
        $this->assign('parent', '用户管理');
        $this->assign('parentHref', U('UserManagement/userManagement'));
        $this->assign('own', ' >> 用户添加');
        $this->display();
    }

    /**
     *描述：Ajax请求账号是否存在
     */
    public function accountIsHaveByAjax()
    {
        $id = getParameter('id','str',false);
       $where['account'] = getParameter('account','str');
       $where['exercises_account.delete_status'] = 2;
       if($id){
           $where['exercises_account.id'] = array('neq',$id);
           $result = $this->model->getResourcesOne($where);
       }else{
           $result = $this->model->getResourcesOne($where);
       }

       if($result){
           $this->showjson('400','账号已存在');
       }
    }

    /*
     *用户的修改
     */
    public function saveUser()
    {
        $id = getParameter('id', 'int');

        if ($_POST) {
            $data['account'] = trim(getParameter('account', 'str'));
            $data['user_name'] = trim(getParameter('name', 'str'));
            $oldPassword = getParameter('oldPassword','str');
            if($oldPassword != getParameter('password', 'str')){
                $data['password'] = sha1(trim(getParameter('password', 'str')));
            }
            $data['role'] = getParameter('role', 'str');
            $oldRole = getParameter('oldRole','str');
            if($oldRole != $data['role']){
                $whereStr1 = "marker_id =$id AND exercises_createexercise.is_delete= 2";//标引人
                $whereStr2 = "creator_id=$id AND exercises_createexercise.is_delete= 2";//录入人
                $whereStr3 = "creator_id=$id AND exercises_create_paper.is_delete= 2";//录入试卷人
                $result1 = $this->model->getWaitingForIndexingByAccount($whereStr1);//echo M()->getLastSql();die;严重程度高
                $result2 = $this->model->getWaitingForIndexingByAccount($whereStr2);//echo M()->getLastSql();die;低
                $result3 = $this->model->getPaperByAccount($whereStr3);//echo M()->getLastSql();die;
                if(!empty($result1) || !empty($result2) || !empty($result3)){
                    $this->error('该账号下有关联的习题，不能修改');
                }
            }
            $data['mobile_phone'] = trim(getParameter('phone', 'str'));
            if ($data['role'] == ROLE_MARKKNOWLEDGE) {
                $data['Learning_period_id'] = getParameter('Learning_period', 'int');
                $data['course_id'] = getParameter('course', 'int');
                $oldCourse = getParameter('oldCourse','str');
                if($oldCourse != $data['course_id']){
                    $whereStr1 = "marker_id =$id AND exercises_createexercise.is_delete= 2";//标引人
                    $result1 = $this->model->getWaitingForIndexingByAccount($whereStr1);//echo M()->getLastSql();die;严重程度高
                    if(!empty($result1)){
                        $this->error('该账号下有待标引的习题，不能修改');
                    }
                }
                $data['version_id'] = getParameter('version_id', 'iArr');
                $data['version_id'] = implode(',', $data['version_id']);
                $data['superior_content'] = getParameter('superior', 'str');
                $temp = explode('-', $data['superior_content']);
                $data['superior_content'] = $temp[1];
                $data['superior'] = $temp[0];
            } elseif ($data['role'] == ROLE_INTSTRUCTOR) {
                $data['Learning_period_id'] = getParameter('Learning_period', 'int');
                $data['course_id'] = getParameter('course', 'int');
                $oldCourse = getParameter('oldCourse','str');
                if($oldCourse != $data['course_id']){
                    $whereStr1 = "marker_id =$id AND exercises_createexercise.is_delete= 2";//标引人
                    $result1 = $this->model->getWaitingForIndexingByAccount($whereStr1);//echo M()->getLastSql();die;严重程度高
                    if(!empty($result1)){
                        $this->error('该账号下有待标引的习题，不能修改');
                    }
                }
                $data['version_id'] = getParameter('version_id', 'iArr');
                $data['version_id'] = implode(',', $data['version_id']);
            }else{//清空操作
                $data['Learning_period_id'] = '';
                $data['course_id'] = '';
                $data['version_id'] = '';
                $data['superior_content'] = '';
                $data['superior'] = '';
            }

            $data['login_ip'] = getParameter('ip', 'str', false);//可有可无

            //修改
            $this->model->startTrans();
            $where['exercises_account.id'] = $id;
            if ($this->model->saveResources($data, $where) === false) {
                $this->model->rollback();
            } else {
                $this->model->commit();
                $this->redirect("UserManagement/userManagement");
            }
        }

        $where2['exercises_account.id'] = $id;
        $info = $this->model->getResourcesOne($where2);
        $this->assign('info', $info);
        //角色
        $roleModel = D('Exercises_auth_permissions');
        $roleList = $roleModel->getResources();
        $this->assign('roleList', $roleList['resources']);
        //教材版本
        $versionModel = D('Exercises_textbook_version');
        $versionList = $versionModel->getResourcesAll();
        $this->assign('versionList', $versionList['resources']);
        //学科
        $courseModel = D('Exercises_Course');
        $courseList = $courseModel->getCourseList();
        $this->assign('courseList', $courseList);
        //上级
        $where3['role'] = ROLE_INTSTRUCTOR;//教研员角色ID
        $superior = $this->model->getResources($where3);
        //拼接
        foreach ($superior['resources'] as $item) {
            $str[$item['id']] = $item['user_name'] . ',' . $item['account'] . ',' . $item['course_name'] . ',' . $item['learning_period'];
        }
        $this->assign('superior', $str);
        $this->assign('parent', '用户管理');
        $this->assign('parentHref', U('UserManagement/userManagement'));
        $this->assign('own', ' >> 用户修改');
        $this->display('addUser');
    }

    /*
     *角色权限列表
     */
    function roleList()
    {
        $authModel = D('Exercises_auth_permissions');

        $where['exercises_auth_permissions.delete_status'] = DELETE_STATUS_FALSE;
        $list = $authModel->getResources($where);
        $this->assign('list', $list['resources']);
        $this->assign('parent', '用户管理');
        $this->assign('parentHref', U('UserManagement/userManagement'));
        $this->assign('own', ' >> 角色管理');
        $this->display();
    }

    /*
     *用户详情页（只能看）
      */
    function roleInformation()
    {
        $id = getParameter('id', 'int');
        $where['exercises_account.id'] = $id;
        $accountInfo = $this->model->getResourcesOne($where);
        $this->assign('accountInfo', $accountInfo);
        $this->assign('parent', '用户管理');
        $this->assign('parentHref', U('UserManagement/userManagement'));
        $this->assign('own', ' >> 用户信息查看');
        $this->display();
    }

    /*
     *用户权限添加操作
     */
    function addRoles()
    {
        D('Exercises_auth_permissions')->startTrans();

        if ($_POST) {
            $data['name'] = trim(getParameter('name', 'str'));
            $data['description'] = trim(getParameter('description', 'str'));
            $dataPersmissions = $_POST['persmissions'];//提交过来的权限数组
//往权限角色关联表中插入数据
            $add = D('Exercises_auth_permissions')->addData($data);
            if ($add === false) {
                D('Exercises_auth_permissions')->rollback();
            }
            //var_dump($dataPersmissions);die;
            if (!empty($dataPersmissions)) {
                $addPersmissions = D('Exercises_auth_permissions')->addResources($add, $dataPersmissions);
                if ($addPersmissions === false) {
                    D('Exercises_auth_permissions')->rollback();
                }
            }
            D('Exercises_auth_permissions')->commit();
            $this->redirect("UserManagement/roleList");
        }

//获取所有权限数据
        $where['parent_id'] = 0;
        $persmissionsList = D('Exercises_auth_permissions')->getResourcesAll($where);
        foreach ($persmissionsList as $k => $v) {
            $where2['parent_id'] = $v['id'];
            $persmissionsList[$k]['children'] = D('Exercises_auth_permissions')->getResourcesAll($where2);
        }
        //var_dump($persmissionsList);die;
        $this->assign('persmissionsList', $persmissionsList);

        $this->assign('action', U('UserManagement/addRoles'));
        $this->assign('parent', '用户管理');
        $this->assign('parentHref', U('UserManagement/userManagement'));
        $this->assign('parentTwo', ' >> 角色管理');
        $this->assign('parentTwoHref', U('UserManagement/roleList'));
        $this->assign('own', ' >> 角色添加');
        $this->display();
    }

    /*
     *用户权限修改操作
     */
    function saveRoles()
    {
        $wherePost['id'] = getParameter('id', 'int');
        $wherePost2['auth_permissions_id'] = getParameter('id', 'int');
        $persmissions['auth_permissions_id'] = getParameter('id', 'int');

        D('Exercises_auth_permissions')->startTrans();

        if ($_POST) {
            $data['name'] = trim(getParameter('name', 'str'));
            $data['description'] = trim(getParameter('description', 'str'));
            $persmissionsarr = $_POST['persmissions'];
//往权限角色表更新数据
            $add = D('Exercises_auth_permissions')->savaResourse($data, $wherePost);
            if ($add === false) {
                D('Exercises_auth_permissions')->rollback();
            }

//往角色权限关联表中先删除，后添加
            $delete = D('Exercises_auth_permissions')->deleteResources($wherePost2);
            if ($delete === false) {
                D('Exercises_auth_permissions')->rollback();
            }
            $addPersmissions = D('Exercises_auth_permissions')->addResources($wherePost['id'], $persmissionsarr);
            if ($addPersmissions === false) {
                D('Exercises_auth_permissions')->rollback();
            }
            D('Exercises_auth_permissions')->commit();
            $this->redirect("UserManagement/roleList");
        }

//查询名称和描述
        $info = D('Exercises_auth_permissions')->getResourcesOne($wherePost);
        $this->assign('info', $info);
//查询勾选的权限
        $where3['auth_permissions_id'] = $wherePost['id'];
        $checked = D('Exercises_auth_permissions')->getResourcesOfAuthPermissionsConcat($where3);
        $checked = array_column($checked, 'permissions_id');
        $this->assign('checked', $checked);

//获取所有权限数据
        $where['parent_id'] = 0;
        $persmissionsList = D('Exercises_auth_permissions')->getResourcesAll($where);
        foreach ($persmissionsList as $k => $v) {
            $where2['parent_id'] = $v['id'];
            $persmissionsList[$k]['children'] = D('Exercises_auth_permissions')->getResourcesAll($where2);
        }
        $this->assign('persmissionsList', $persmissionsList);


        $this->assign('action', U('UserManagement/saveRoles'));
        $this->assign('id', $wherePost['id']);
        $this->assign('parent', '用户管理');
        $this->assign('parentHref', U('UserManagement/userManagement'));
        $this->assign('parentTwo', ' >> 角色管理');
        $this->assign('parentTwoHref', U('UserManagement/roleList'));
        $this->assign('own', ' >> 角色修改');
        $this->display('addRoles');
    }


    /*
     *删除角色
     */
    public function deletes()
    {
        $data['delete_status'] = getParameter('delete_status', 'int');
        $where['id'] = getParameter('id', 'int');
        $this->model->startTrans();
        //查询此角色下有没有关联账号，有则不能删除
        $haveAccountByRole = D('Exercises_auth_permissions')->haveAccountByrole($where['id']);
        if($haveAccountByRole){
            $this->showjson('503', '该角色下已关联账号，不能删除！');
        }
        $status = D('Exercises_auth_permissions')->savaResourse($data, $where);
        if ($status === false) {
            $this->model->rollback();
            $this->showjson('400', '失败，请刷新后，重试');
        } else {
            $this->model->commit();
            $this->showjson('200');

        }
    }


    /*
     *用户行为列表
     */
    function userBehaviorManagement()
    {

        set_time_limit(0);
        //$where['account_status'] = ACCOUNT_STATUS_NORMAL;
        $where['exercises_account.delete_status'] = DELETE_STATUS_FALSE;

        $page = getParameter('p','str',false);
        if ($_POST) {
            $name = getParameter('name', 'str', false);
            $account = getParameter('account', 'str', false);
            $phone = getParameter('phone', 'str', false);
            if (!empty($name)) {
                $where['exercises_account.user_name'] = $name;
            }
            if (!empty($account)) {
                $where['exercises_account.account'] = $account;
            }
            if (!empty($phone)) {
                $where['exercises_account.mobile_phone'] = $phone;
            }
        }
//查询数据
        $list = D('Exercises_log')->getUserBehaviorResources1($count, $where,$page);//model方法未完
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        if ($where) {
            $Page->parameter = $where;
        }
        $show = $Page->show();

        //获取数组操作
        foreach ($list as $k => $temp) {
            $result = D('Exercises_log')->getUserBehaviorResources2($temp['id']);
            //$result2 = D('Exercises_log')->getUserBehaviorResources3($temp['id']);
            //var_dump($result[0]['oper_time']);
            /*if(strtotime($result[0]['oper_time']) > strtotime($result2[0]['oper_time'])){*/
            $last[] = $result[0]['oper_time'];
            $last_name[] = $result[0]['oper_name'];
            /*}else{
                $last[] = $result2[0]['oper_time'];
                $last_name[] = $result2[0]['oper_name'];
            }*/
            array_push($list[$k], $last[$k]);//行为日志创建时间
            array_push($list[$k], $last_name[$k]);//行为日志操作类型文字描述
        }
        if($this->userInfo['id'] == ACCOUNT_SUPERADMIN_ID){
            $this->assign('tip',true);//如果不为超级管理员就给个标识，用作前台判断
        }else{
            $this->assign('tip',false);
        }
//var_dump($list);die;
        $this->assign('name', $name);
        $this->assign('account', $account);
        $this->assign('phone', $phone);
        $this->assign('page', $show);
        $this->assign('list', $list);
        $this->assign('parent', '用户管理');
        $this->assign('parentHref', U('UserManagement/userManagement'));
        $this->assign('own', ' >> 用户行为管理');
        $this->display();
    }

    /*
   *用户行为记录页
   */
    function roleQuery()
    {
            $page = getParameter('p', 'int', false);

        $where_id['exercises_account.id'] = getParameter('id', 'int');
//        $where['exercises_account.id'] = getParameter('id', 'int');
        $count = 0;

            $starTime = getParameter('starTime', 'str', false);
            $endTime = getParameter('endTime', 'str', false);
            //var_dump($starTime,$endTime);die;
            $time = strtotime($endTime);
            $newTime = $time + 86400;
            $newTime = date("Y-m-d",$newTime);
            if (!empty($starTime)) {
                $where['exercises_log.oper_time'] = array('GT', $starTime);
                //$where['exercises_tree_log.oper_time'] = array('GT', $starTime);
            }
            if (!empty($endTime)) {
                $where['exercises_log.oper_time'] = array('LT', $newTime);
                // $where['exercises_tree_log.oper_time'] = array('LT', $endTime);
            }
            if (!empty($starTime) && !empty($endTime)) {
                $where['exercises_log.oper_time'] = array('between', "$starTime,$newTime");
                // $where['exercises_tree_log.oper_time'] = array('between', "$starTime,$endTime");
            }
            $data = getParameter('behavior', 'int', false);
            if ($data) {
                $where['exercises_log.error_status'] = $data;
                //  $where['exercises_tree_log.error_status'] = $data;
            }
//查询账号数据
        $info = D('Exercises_account')->getResourcesOne($where_id);
        $this->assign('info', $info);
//用户行为数据
        $where['operator_id'] =  $where_id['exercises_account.id'];
        $result = D('Exercises_log')->getUserBehavior($count, $where, $page);//echo M()->getLastSql();die;
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        if ($starTime) {
            $Page->parameter['starTime'] = $starTime;
        }
        if ($endTime) {
            $Page->parameter['endTime'] = $endTime;
        }
        if ($where_id['exercises_account.id']) {
            $Page->parameter['id'] = $where_id['exercises_account.id'];
        }
        if (!empty($data)) {
            $Page->parameter['behavior'] = $data;
        }
        $show = $Page->show();
        $this->assign('id', $where_id['exercises_account.id']);
        $this->assign('starTime', $starTime);
        $this->assign('endTime', $endTime);
        $this->assign('behavior', $data);
        $this->assign('page', $show);
        $this->assign('list', $result);
        $this->assign('parent', '用户管理');
        $this->assign('parentHref', U('UserManagement/userManagement'));
        $this->assign('parentTwo', ' >> 用户行为管理');
        $this->assign('parentTwoHref', U('UserManagement/userBehaviorManagement'));
        $this->assign('own', ' >> 用户行为查询');
        $this->display();
    }

}
