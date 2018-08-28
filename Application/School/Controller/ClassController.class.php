<?php

namespace School\Controller;

use Think\Controller;
use Common\Common\CSV;

define('ENABLE_CLASS_STATUS', 1);
define('DISABLE_CLASS_STATUS', 0);
define('SCHOOL_CLASS_CODE_PRIFIX', '00');
define('INDIVIDUAL_CLASS_CODE_PRIFIX', '20');
define('SCHOOL_ID', session('school.school_id'));
define('STUDENT_NOT_EXISTS', '学生信息不存在');
define('STUDENT_JOIN_SCHOOL_CLASS_FAILED_MESSAGE', '一个学生只能加入一个校建班!');

class ClassController extends Controller
{
    public $model = '';
    public $page_size = 20;

    public function __construct()
    {
        parent::__construct();
        $this->model = D('Biz_class');
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions();
        $this->assign('oss_path', 'http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }

    //TODO:免登陆操作
    public function noLanding(){
//var_dump(session());die;
        $auth_teacher = D('Auth_teacher');
        $auth_teacher->startTrans();
        //根据session中的数据查询是否注册过老师
        $account = session('school.telephone');
        $parentId = session('school.parent_id');
        $password = session('school.password');
        if($parentId == 0){
            //根据账号查找是否由此账户,存在则取出账号密码
            $teacherData = $auth_teacher->getTeacherByTel($account);
            if(!empty($teacherData)){
                $teacherAccount = $teacherData['telephone'];
                $teacherPassword =  $teacherData['password'];
            }else{
            //不存在此账户则创建教师账户
                $data['telephone'] = $account;
                $data['password'] = $password;
                $status = $auth_teacher->addTeacherData($data);
                if($status === false){
                    $auth_teacher->rollback();
                    $this->error('创建账号失败');
                }else{
                    $auth_teacher->commit();
                    $teacherAccount = $account;
                    $teacherPassword =  $password;
                    //TODO:这里给用户添加个人试用VIP
                    give_new_vip_operation(ROLE_TEACHER,1,$status);
                }
            }

        }
            $theme = "1";
            $check['telephone'] = $teacherAccount;
            $check['password'] = $teacherPassword;
            $TeacherModel = M('auth_teacher');
            $result = $TeacherModel->where($check)->find();
            $firstLogin = $result['is_first'];
            if ($result) {
                if ($result['flag'] == 0 || $result['flag'] == -1) {
                    //$this->redirect("Index/index?role=t&err=5");
                    $this->redirect(U("Home/Index/index?role=t&err=5"));
                }

                session('auth_parent', null);
                session('auth_student', null);
                session('student', null);
                session('parent', null);


                if ($result['flag'] == DISABLE_TEACHER_STATUS) {
                   // $this->redirect("Index/index?role=t&err=9");
                    $this->redirect(U("Home/Index/index?role=t&err=9"));
                }
                session_start();
                session('teacher', $result);

                $login_mac_address['access_token'] = session_id();
                $TeacherModel->where("id=" . $result['id'])->save($login_mac_address);

                session('theme', $theme);

                //判断是否是vip 如果是计算天数

                $auth_type_use = D('Account_auths');
                $auth_list = $auth_type_use->getAuthAndVipauth(session('teacher.id'), 2);

                session('auth_teacher', $auth_list);

                $isVipInfo = $auth_type_use->isVipInfo(session('teacher.id'), 2);

                session('teacher_vip', $isVipInfo);
                $btntheme = "primary";
                if ($theme == 2) $btntheme = "danger";
                if ($theme == 3) $btntheme = "dark";
                session('btntheme', $btntheme);
                $userip = getIPaddress();
                if (!empty($userip)) {

                    if ($userip != '127.0.0.1') {
                        $shengfen = getIPLoc_sina($userip);
                    } else {
                        $shengfen = '内网';
                    }
                    if (empty($shengfen)) {
                        $shengfen = '内网';
                    }

                    if ($shengfen != $result['login_address']) { //发送
                        $teacherLogin_address['login_address'] = $shengfen;
                        $rsave_id = $TeacherModel->where("id=" . $result['id'])->save($teacherLogin_address);
                        if ($rsave_id != false && !empty($result['login_address'])) { //发送消息，异地登陆

                            $parameters = array('msg' => array(date("Y-m-d H:i:s", time()), $shengfen . "($userip)", 'pc'), 'url' => array('type' => 0));
                            A('Home/Message')->addPushUserMessage('EXCEPTION_LOGIN', 2, $result['id'], $parameters);
                        }
                    }
                    if ($result['login_address'] == '') {
                        $teacherLogin_address['login_address'] = $shengfen;
                        $result = $TeacherModel->where("id=" . $result['id'])->save($teacherLogin_address);
                    }
                }


                if ($firstLogin == 1) {
                    $mapfirst['id'] = $result['id'];
                    $firstdata['is_first'] = 2;
                    M('auth_teacher')->where($mapfirst)->save($firstdata);

                }

                $tip = A('Home/Common')->registered_but_no_uploadworks(session('teacher.id'), 2);
                session('tip', $tip);

                $share_par = $_REQUEST['url'];
                if (!empty($share_par)) {
                    $share_par = base64_decode($share_par);
                    header('Location:' . $share_par);
                } else {
                    //$this->redirect("Teach/index$theme");
                    $this->redirect(U("Home/Teach/index$theme"));
                }
            } else {
                //$this->error('登陆失败，即将返回，请输入正确的账号及密码，或者您的账号可能被管理员锁定，如有问题，请联系400-XXXXXXXXX');
                //$this->redirect("Index/index?role=t&err=1");
                $this->redirect(U("Home/Index/index?role=t&err=1"));
            }
    }

    /*
    * 班级列表
    */
    public function classList()
    {
//        if (!session('?school')) redirect(U('Login/login'));
//        A('School/SchoolAdmin')->check_permissions();

        /*$grade_model=D('Dict_grade');
        $class_model=D('Biz_class');
        $filter['class_code']=getParameter('class_code','str',false);
        $filter['grade']=getParameter('grade','int',false);
        $filter['class']=getParameter('class','int',false);
        $filter['class_flag']=$_GET['class_flag'];
        $filter['order']=getParameter('order','str',false);
        $order=$filter['order'];
        $order?$order='asc':$order='desc';

        $condition['biz_class.school_id']=SCHOOL_ID;
        $condition['biz_class.is_delete']=0;
        $condition['biz_class.class_status']=SCHOOL_CLASS;
        if(!empty($filter['class_code']))   $condition['biz_class.class_code']=array('like', '%' . $filter['class_code']. '%');
        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade'];
        if(!empty($filter['class']))   $condition['biz_class.id']=$filter['class'];
        if(($filter['class_flag']!=''))   $condition['biz_class.flag']=$filter['class_flag'];

        if(!empty($filter['grade'])) $class_list=$class_model->getClassDataBySchool(SCHOOL_ID,$filter['grade']);
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        }

        $result=$this->model->getClassData($condition,$order);
        $grade_result=$grade_model->getGradeList(true);

        $this->assign('condition_str',$condition_string);
        $this->assign('class_code',$filter['class_code']);
        $this->assign('grade',$filter['grade']);
        $this->assign('class_id',$filter['class']);
        $this->assign('class_flag',$filter['class_flag']);
        $this->assign('order',$order);

        $this->assign('grade_list',$grade_result);
        $this->assign('class_list',$class_list);
        $this->assign('list',$result['data']);
        $this->assign('page',$result['page']);*/
        if (session('school.parent_id') != 0) {
            if (in_array('Class/addClass', session('school_permissions'))) {
                $this->assign('add', 'show');
            }else{
                $this->assign('add', 'display');
            }
        }else{
            $this->assign('add', 'show');
        }
        if (session('school.parent_id') != 0) {
            if (in_array('Class/importClassView', session('school_permissions'))) {
                $this->assign('import', 'yes');
            }else{
                $this->assign('import', 'no');
            }
        }else{
            $this->assign('import', 'yes');
        }
        if(session('school.parent_id')!=0) {
            if (in_array('Class/deleteClass', session('school_permissions'))) {
                $this->assign('delete', 'yes');
            }else{
                $this->assign('delete', 'no');
            }
        }else{
            $this->assign('delete', 'yes');
        }
        $grade_model = D('Dict_grade');
        $result = $grade_model->getGradeListBySchool();
        $this->assign('gradeList',json_encode($result));
            $this->display();
    }

    /**
     *描述：获取学校下所有的年级接口
     */
    public function getGradeBySchool()
    {
//        if (!session('?school')) redirect(U('Login/login'));
//        A('School/SchoolAdmin')->check_permissions();

        $where['school_id'] = SCHOOL_ID;
        $where['biz_class.class_status'] = 1;
        $where['biz_class.is_delete'] = 0;
        $grade_model = D('Dict_grade');
        $result = $grade_model->getGradeListBySchool($where);
        $this->ajaxreturn(array('status' => 200, 'data' => $result));
    }

    /**
     *描述：获取学校该年级下的所有班级接口
     */
    public function getClassByGrade()
    {
//        if (!session('?school')) redirect(U('Login/login'));
//        A('School/SchoolAdmin')->check_permissions();
        $where['biz_class.is_delete'] = 0;
        $where['biz_class.class_status'] = SCHOOL_CLASS;

        $where2['exercises_student_homework.correct_status'] = 1;
        $where2['exercises_student_homework.is_delete'] = 2;

        $gradeId = getParameter('gradeId', 'int');
        $where['biz_class.school_id'] = SCHOOL_ID;
        $where2 = array_merge($where2,$where);
        $where['dict_grade.id'] = $gradeId;

        //TODO:错误处理作业个数
        $class_model = D('Biz_class');
        $result = $class_model->getClassByScholl($where,$where2);
//echo M()->getLastSql();die;
        $this->ajaxreturn(array('status' => 200, 'data' => $result));
    }

    /*
     *描述：班级教师列表接口
     */
    public function getTeacherListByClass(){
        $class_id = getParameter('id', 'int');
        $filter['class_id'] = $class_id;

        $order = getParameter('order', 'int', false);
        $order ? $order = 'asc' : $order = 'desc';

        $condition['biz_class_teacher.is_handler'] = 0;
        $condition['biz_class.school_id'] = SCHOOL_ID;
        $condition['biz_class.id'] = $class_id;
        $condition['auth_teacher.school_id'] = SCHOOL_ID;
        $result = $this->model->getClassTeacher($condition, $order);
        foreach ($result as $key => $value) {
            $result[$key]['role'] = 2;
            $this->getAvatar($result[$key]);//根据不同用户角色拿到不同的头像
        }
        //echo M()->getLastSql();die;
//var_dump(session('school.school_id'));die;
//var_dump($result);die;
        $this->ajaxreturn(array('status' => 200, 'data' => $result));
    }

    private function getAvatar(&$result)
    {
        if ($result['role'] == ROLE_TEACHER) {

            if (preg_match('/Resources/', $result['teacher_avatar'])) {
                if (strpos($result['teacher_avatar'], '.') === false) {
                    $result['teacher_avatar'] .= '.jpg';
                }
                $result['teacher_avatar'] = C('oss_path') . $result['teacher_avatar'];
            } else {
                if ($result['sex'] == '男' || empty($result['sex'])) {
                    $result['teacher_avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/teacher_m.png';
                } else {
                    $result['teacher_avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/teacher_w.png';
                }

            }
        } elseif ($result['role'] == ROLE_STUDENT) {


            if (preg_match('/Resources/', $result['avatar'])) {
                if (strpos($result['avatar'], '.') === false) {
                    $result['avatar'] .= '.jpg';
                }
                $result['student_avatar'] = C('oss_path') . $result['avatar'];
            } else {

                if ($result['sex'] == '男' || empty($result['sex'])) {
                    $result['student_avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/student_m.png';
                } else {
                    $result['student_avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/student_w.png';
                }

            }
        } else {


            if (preg_match('/Resources/', $result['parent_avatar'])) {
                if (strpos($result['parent_avatar'], '.') === false) {
                    $result['parent_avatar'] .= '.jpg';
                }
                $result['parent_avatar'] = C('oss_path') . $result['parent_avatar'];
            } else {

                if ($result['sex'] == '男' || empty($result['sex'])) {
                    $result['parent_avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/jiazhang.png';
                } else {
                    $result['parent_avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/jiazhang2.png';
                }

            }
        }
    }

    /*
    *描述：班级已布置作业列表接口
    */
    public function getHomeWordListByClass(){
        $classId = getParameter('classId','int');
        $where['exercises_student_homework.correct_status'] = 1;
        $where['exercises_student_homework.is_delete'] = 2;
        $where['biz_class.id'] = $classId;
        $result = $this->model->getHomeworkListByClassGroupByCourse($where);

        //TODO:错误处理
 //       echo M()->getLastSql();die;
        $this->ajaxreturn(array('status' => 200, 'data' => $result));
    }

    /*
    *描述：班级所有学生列表接口
    */
    public function getStudentListByClass(){
        $class_id = getParameter('id', 'int');
        $filter['class_id'] = $class_id;
        $filter['order'] = getParameter('order', 'int', false);
        $order = $filter['order'];
        $order ? $order = 'asc' : $order = 'desc';

        $condition['biz_class.id'] = $class_id;
        $condition['biz_class.flag'] = array('neq', '-1');
        $condition['auth_student.school_id'] = SCHOOL_ID;
        $condition['auth_student.flag'] = 1;
        $result = $this->model->getClassStudentDataAll($condition, $order);

        foreach ($result as $key => $value) {
            $result[$key]['role'] = 3;
            $this->getAvatar($result[$key]);//根据不同用户角色拿到不同的头像
        }
//var_dump($result);die;
        $this->ajaxreturn(array('status' => 200, 'data' => $result));
    }

    /*
     *描述：学情分析接口
     */
    public function studySituation (){
        $courseId = getParameter('courseId','int',false);
        $startTime = getParameter('startTime','str',false);
        $endTime = getParameter('endTime','int',false);
        /*if(empty($startTime) && empty($endTime)){
            $where['exercises_homwork_basics.release_time']  = array(array('ELT', strtotime(date('Y-m-d'))), array('EGT', strtotime(date('Y-m-d')) - 2592000));
        }else{
            $where['exercises_homwork_basics.release_time']  = array(array('LT', strtotime($endTime) + 84600), array('EGT', strtotime(date('Y-m-d')) - 2592000));
        }*/
        $classId = getParameter('classId','int');
        $where['exercises_student_homework.correct_status'] = 1;
        if(!empty($courseId)){
            $where['dict_course.id'] = $courseId;
        }
        $where['exercises_student_homework.is_delete'] = 2;
        $where['biz_class.id'] = $classId;
        $result = $this->model->getHomeworkListByClassAndCourse($where);
       $name = array_column($result,'homeworkname');
       //TODO:学情分析错误
       foreach (array_column($result,'y') as $item){
           $data['data'][] = $item+0;
       }
       $data['name'] = '成绩';
       if(empty($result)){
           $this->ajaxreturn(array('status' => 200, 'data' => ''));
       }else{
           $this->ajaxreturn(array('status' => 200, 'data' => array('name'=>$name,'data'=>$data)));
       }
    }

    /*
* 更改班级启用或停用状态 不支持更改为移交中
*/
    public function udpateClassManagement()
    {
        if (!session('?school')) $this->showjson(400, ACCOUNT_FAILURE);
        A('School/SchoolAdmin')->check_permissions(true);

        $id = getParameter('id', 'int');
        $result = $this->model->getClassInfo($id);

        $class_result = $this->model->getClassAndGradeInfo($id);
        $teacher_id_all = $this->model->getTeacherIdAll($id);//获取所有的老师的id

        $parent_id_all = $this->model->getParentIdAll($id);//获取所有的家长的id

        $stu_id_all = $this->model->getStudentIdAll($id);//对所有的学生进行推送

        if (empty($result)) {
            $this->showjson(401, ID_NOT_EXISTS_MESSAGE);
        } else {
            if ($result['school_id'] != SCHOOL_ID) {
                $this->showjson(402, COMMON_FAILED_MESSAGE);
            }
            $handsoff_status = 2;
            if ($result['flag'] == $handsoff_status) {
                $this->showjson(403, COMMON_FAILED_MESSAGE);
            }
            if ($result['flag']) {
                $status = $this->model->udpateClassStatus($id, DISABLE_CLASS_STATUS, SCHOOL_ID);
            } else {
                $status = $this->model->udpateClassStatus($id, ENABLE_CLASS_STATUS, SCHOOL_ID);
            }
            if ($status) {

                if ($result['flag'] == 1) { //该为停用班级
//消息推送
                    $parameters = array(
                        'msg' => array(
                            $class_result['grade'],
                            $class_result['name'],
                            C('SCHOOL_ROOT'),
                            $class_result['grade'],
                            $class_result['name'],
                            C('SCHOOL_ROOT'),
                        ),
                        'url' => array('type' => 0,)
                    );

                    A('Home/Message')->addPushUserMessage('CLASS_FALG_DISABLE', 2, implode(',', $teacher_id_all), $parameters);

//对学生的所有家长进行推送
                    $studentModel = D('Auth_student');
                    foreach ($stu_id_all as $key => $studentId) {
                        $studentInfo = $studentModel->getStudentInfo($studentId);
                        $parameters['url'] = array('type' => 1, 'data' => array($studentInfo['id'], $studentInfo['student_name']));
                        A('Home/Message')->addPushUserMessage('FLAGCLASSDISABLE', ROLE_PARENT, $studentInfo['parent_id'], $parameters);
                    }

//对所有的学生进行推送

                    A('Home/Message')->addPushUserMessage('STU_CLASS_DISABLE', 3, implode(',', $stu_id_all), $parameters);

                }
                $this->showjson(200);
            } else {
                $this->showjson(404, COMMON_FAILED_MESSAGE);
            }
        }

    }


  /*
   *描述：删除班级接口
   */
    public function deleteClass()
    {
        if (!session('?school')) $this->showjson(400, ACCOUNT_FAILURE);
        A('School/SchoolAdmin')->check_permissions(true);

        $id = getParameter('id', 'int');
        $result = $this->model->getClassInfo($id);

        $class_result = $this->model->getClassAndGradeInfo($id);
        $teacher_id_all = D('Biz_class')->getTeacherIdAll($id);

        $parent_id_all = $this->model->getParentIdAll($id);//获取所有的家长的id
        $student_id_all = $this->model->getStudentIdAll($id);

        if (empty($result)) {
            $this->showjson(401, ID_NOT_EXISTS_MESSAGE);
        } else {
            if ($result['school_id'] != SCHOOL_ID) {
                $this->showjson(402, COMMON_FAILED_MESSAGE);
            }
            $handsoff_status = 2;
            if ($result['flag'] == $handsoff_status) {
                $this->showjson(403, '移交中的班级不允许删除!');
            }
            $this->model->startTrans();
            $status = $this->model->deleteClass($id, $errorInfo);
            if (!$status) {
                $this->model->rollback();
                $this->showjson(404, $errorInfo);
            }
            if (!$this->model->deleteClassTimetable($id)) {
                $this->model->rollback();
                $this->showjson(405, COMMON_FAILED_MESSAGE);
            }

//给老师发
            $parameters = array(
                'msg' => array(
                    $class_result['grade'],
                    $class_result['name'],
                    C('SCHOOL_ROOT'),
                    $class_result['grade'],
                    $class_result['name'],
                    C('SCHOOL_ROOT'),
                ),
                'url' => array('type' => 0,)
            );

            A('Home/Message')->addPushUserMessage('DELETE_CLASS', 2, implode(',', $teacher_id_all), $parameters);

//给家长发

            $parameters_parent = array(
                'msg' => array(
                    $class_result['grade'],
                    $class_result['name'],
                    C('SCHOOL_ROOT'),
                    $class_result['grade'],
                    $class_result['name'],
                    C('SCHOOL_ROOT'),
                ),
                'url' => array('type' => 0,)
            );

            A('Home/Message')->addPushUserMessage('PARENTDELETE_CLASS', 4, implode(',', $parent_id_all), $parameters_parent);

//给学生发
            A('Home/Message')->addPushUserMessage('STU_PARENTDELETE_CLASS', 3, implode(',', $student_id_all), $parameters_parent);

            $this->model->commit();
            $this->showjson(200);
        }
    }


/*
 *描述：添加班级接口
 */
        public function addClass()
    {
        if (!session('?school')) $this->showjson(400, ACCOUNT_FAILURE);
        A('School/SchoolAdmin')->check_permissions(true);

        $grade_model = D('Dict_grade');
        $id = getParameter('grade_id', 'int');
        $class_name = getParameter('class_name', 'str');
        $grade_result = $grade_model->getGradeInfo($id);
        if (empty($grade_result)) {
            $this->showjson(401, '年级信息不存在!');
        }
//判断某个班级是否存在
        $class_con['biz_class.name'] = $class_name;
        $class_con['biz_class.grade_id'] = $grade_result['id'];
        $class_con['biz_class.school_id'] = SCHOOL_ID;
        $class_con['biz_class.is_delete'] = 0;
        $class_count = $this->model->getClassCount($class_con);
        if ($class_count) {
            $this->showjson(402, '已经相同的班级存在!');
        }
        $data['name'] = $class_name;
        $data['grade_id'] = $grade_result['id'];
        $data['school_id'] = SCHOOL_ID;
        $data['flag'] = 1;
        $data['create_at'] = time();
        $data['class_status'] = 1;
        $this->model->startTrans();
        if (!($class_id = $this->model->addClass($data))) {
            $this->model->rollback();
            $this->showjson(403, COMMON_FAILED_MESSAGE);
        }
        $class_data['class_code'] = $class_id + CLASS_CODE_ADD_NUMBER;
        if (!$this->model->updateClassInfo($class_id, $class_data)) {
            $this->model->rollback();
            $this->showjson(404, COMMON_FAILED_MESSAGE);
        }
        $this->model->commit();
        $this->showjson(200);
    }


    /*
* 班级详情
*/
    public function classDetail()
    {
//        if (!session('?school')) redirect(U('Login/login'));
//        A('School/SchoolAdmin')->check_permissions();

        $id = getParameter('id', 'int');
        $where['biz_class.id'] = $id;
        $fild = 'dict_course.course_name,dict_course.id courseid,grade,biz_class.name classname';
        $group = 'dict_course.id';
        //$result = $this->model->getClassSchoolData($id, SCHOOL_ID);
        $result = $this->model->getCourseGradeById($where);
        $courseList = $this->model->getHomeworkListByClassAndCourse($where,$group,$fild);
        $this->assign('course', json_encode($result));
        $this->assign('courseList', json_encode($courseList));
        $this->assign('endTime', date('Y-m-d'));
        $this->assign('startTime', date('Y-m-d',strtotime(date('Y-m-d')) - 2592000));

        $this->classId = I('id');
        $this->display();
    }


    /*
* 课程表
*/
    public function class_iframe_classTimetable()
    {
        if (I('tip') == 1) {
            echo "<script>alert('无权操作')</script>";
        }
        $course_model = D('Dict_course');
        $class_id = getParameter('id', 'int');
        $course_result = $course_model->getCourseList();
        $comments = $this->model->getSchoolClassComment($class_id);
        $this->assign('comments', $comments);
        $this->assign('course_list', $course_result);
        $this->assign('class_id', $class_id);
        $this->display();
    }


    /*
* 修改课表
*/
    public function updateClassTimetable()
    {
        if (!session('?school')) redirect(U('Login/login'));
        $classId = getParameter('class_id', 'int');

        if (A('School/SchoolAdmin')->check_permissions(0, true) == 1) {
            $this->redirect('Class/class_iframe_classTimetable', array('id' => $classId, 'tip' => 1));//这里是iframe中进行修改操作没权限时给提示用的
        }


        $data['class_id'] = $classId;
        $result = $this->model->getClassSchoolData($classId);
        if (empty($result)) {
            $this->error('参数错误!');
        }
        $data['comments'] = getParameter('comments', 'str', false);
        $data['update_at'] = time();
        if ($result['class_status'] == PERSONAL_CLASS) {
            $data['content'] = $_POST['content'];
            if (!$this->model->updateTimeTableData($classId, $data)) {
                $this->error('修改课表失败');
            }
        } else {
//校建班
            $data['course_teacher'] = $_POST['courses'];
            if (empty($data['course_teacher'])) {
                $update_data['comments'] = $data['comments'];
                $this->model->updateSchoolClassComment($classId, $update_data);
            } else {
                $this->model->setClassTimeTable($classId, $data['course_teacher'], $data['comments'], $result['class_status'], $error_info);
            }
        }
        $this->redirect('Class/class_iframe_classTimetable', array('id' => $classId));

    }


    /*
* 通过某个班级和学科获得其下面的所有教师
*/
    public function getClassCourseTeacher()
    {
        if (!session('?school')) $this->showjson(400, ACCOUNT_FAILURE);

        $class_id = getParameter('id', 'int');
        $course_id = getParameter('course_id', 'int');
        $condition['dict_course.id'] = $course_id;
        $condition['biz_class.id'] = $class_id;
        $result = $this->model->getClassTeacherCourse($condition, true);
        $this->showjson(200, '', $result);
    }


    /*
* 班级添加学生
*/
    public function classAddStudent()
    {
        if (!session('?school')) $this->showjson(400, ACCOUNT_FAILURE);
        A('School/SchoolAdmin')->check_permissions(true);

        $student_model = D('Auth_student');
        $class_model = D('Biz_class');
        $studnet_name = getParameter('student_name', 'str');
        $parent_tel = getParameter('parent_tel', 'str');
        $class_id = getParameter('class_id', 'int');
        $school_class = 1;
//判断班级是否存在
        $class_result = $this->model->getClassSchoolData($class_id, SCHOOL_ID);
        $class_result_info = $this->model->getClassAndGradeInfo($class_id);

        $parent_info = D('Auth_parent')->getParentInfoByTelephone($parent_tel);

        if (empty($class_result)) {
            $this->showjson(401, ID_NOT_EXISTS_MESSAGE);
        }
        $student_con['student_name'] = $studnet_name;
        $student_con['parent_tel'] = $parent_tel;
        $student_result = $student_model->getStudentDataAll($student_con);
        if (empty($student_result)) {
            $this->showjson(402, STUDENT_NOT_EXISTS);
        } else {
            if ($student_result[0]['school_id'] != SCHOOL_ID) {
                $this->showjson(403, '无权限操作其他学校下的学生!');
            }
        }
        $student_id = $student_result[0]['id'];

        $condition['auth_student.id'] = $student_id;
//$condition['biz_class.id']=$class_id;
        $condition['biz_class.class_status'] = $school_class;
        $condition['biz_class_student.status'] = CLASS_STUDETN_STATUS;
        $condition['biz_class.is_delete'] = 0;
        $result = $class_model->getClassStudentDataAll($condition);
        if (!empty($result)) {
            $this->showjson(404, STUDENT_JOIN_SCHOOL_CLASS_FAILED_MESSAGE);
        }

        $class_student_data['class_id'] = $class_id;
        $class_student_data['student_id'] = $student_id;
        $class_student_data['create_at'] = time();
        $class_student_data['status'] = 2;
        $this->model->startTrans();
        if (!$class_model->addClassStudentData($class_student_data)) {
            $this->model->rollback();
            $this->showjson(405, COMMON_FAILED_MESSAGE);
        }
        if (!$this->model->deleteClassStudentRecord($class_id, $student_id)) {
            $this->model->rollback();
            $this->showjson(406, COMMON_FAILED_MESSAGE);
        }
        /*if(!$class_model->updateClassStudentCount($class_id,true)){
$this->model->rollback();
$this->showjson(406,COMMON_FAILED_MESSAGE);
}*/

        $parameters = array(
            'msg' => array(
                $studnet_name,
                C('SCHOOL_ROOT'),
                $class_result_info['grade'],
                $class_result_info['name'],
            ),
            'url' => array('type' => 1, 'data' => array($student_id, $studnet_name))
        );

        A('Home/Message')->addPushUserMessage('CLASSADDSTUDENT', 4, $parent_info['id'], $parameters);


        $parametersstu = array(
            'msg' => array(
                C('SCHOOL_ROOT'),
                $class_result_info['grade'],
                $class_result_info['name'],
            ),
            'url' => array('type' => 0,)
        );

        A('Home/Message')->addPushUserMessage('CLASSADDSENDSTUDENT', 3, $student_id, $parametersstu);


        $this->model->commit();
        $this->showjson(200);
    }


    /*
* 获得班级下的学生信息 iframe
*/
    public function class_iframe_student()
    {
        $class_id = getParameter('id', 'int');
        $filter['class_id'] = $class_id;
        $filter['student_name'] = getParameter('student_name', 'str', false);
        $filter['parent_tel'] = getParameter('parent_tel', 'str', false);
        $filter['status'] = $_GET['status'];
        $filter['order'] = getParameter('order', 'int', false);
        $order = $filter['order'];
        $order ? $order = 'asc' : $order = 'desc';

        $condition['biz_class.id'] = $class_id;
        $condition['biz_class.flag'] = array('neq', '-1');
        $condition['auth_student.school_id'] = SCHOOL_ID;
        if (!empty($filter['student_name'])) $condition['student_name'] = array('like', '%' . $filter['student_name'] . '%');
        if (!empty($filter['parent_tel'])) $condition['parent_tel'] = array('like', '%' . $filter['parent_tel'] . '%');
        if ($filter['status'] != '') $condition['auth_student.flag'] = intval($filter['status']);
        $result = $this->model->getClassStudentData($condition, $order);

        $condition_string = '';
        foreach ($filter as $key => $val) {
            $condition_string .= '&' . $key . '=' . $val;
        }

        $this->assign('condition_str', $condition_string);
        $this->assign('student_name', $filter['student_name']);
        $this->assign('parent_tel', $filter['parent_tel']);
        $this->assign('status', $filter['status']);
        $this->assign('order', $order);
        $this->assign('class_id', $class_id);

        $this->assign('list', $result['data']);
        $this->assign('page', $result['page']);
        $this->display();
    }


    /*
* 获得班级下的教师信息 iframe
*/
    public function class_iframe_teacher()
    {
        $class_id = getParameter('id', 'int');
        $filter['class_id'] = $class_id;
        $condition_string = '';
        foreach ($filter as $key => $val) {
            $condition_string .= '&' . $key . '=' . $val;
        }

        $order = getParameter('order', 'int', false);
        $order ? $order = 'asc' : $order = 'desc';

        $condition['biz_class_teacher.is_handler'] = 0;
        $condition['biz_class.school_id'] = SCHOOL_ID;
        $condition['biz_class.id'] = $class_id;
        $condition['auth_teacher.school_id'] = SCHOOL_ID;
        $result = $this->model->getClassTeacher($condition, $order);

        $this->assign('condition_str', $condition_string);
        $this->assign('class_id', $class_id);
        $this->assign('list', $result);
        $this->display();
    }


    /*
* 获得教师的任教学科
*/
    public function getTeacherCourse()
    {
        if (!session('?school')) $this->showjson(400, ACCOUNT_FAILURE);

        $teacher_model = D('Auth_teacher');
        $teacher_name = getParameter('name', 'str');
        $telephone = getParameter('tel', 'str');
        $order = 'desc';
        $condition['auth_teacher.telephone'] = $telephone;
        $condition['auth_teacher.name'] = $teacher_name;
        $result = $teacher_model->getTeacherCourse($condition, $order, true);
        if (!empty($result)) {
            $this->showjson(200, '', $result);
        } else {
            $this->showjson(400, '姓名和手机号不匹配');
        }


    }


    /*
* 获得班级下的学生的家长信息   iframe
*/
    public function class_iframe_parent()
    {
        $class_id = getParameter('id', 'int');
        $filter['class_id'] = $class_id;
        $filter['parent_name'] = getParameter('parent_name', 'str', false);
        $filter['parent_tel'] = getParameter('parent_tel', 'str', false);
        $filter['status'] = getParameter('status', 'int', false);
        $filter['student_name'] = getParameter('student_name', 'str', false);
        $filter['privilege_type'] = $_GET['privilege_type'];
        $filter['order'] = getParameter('order', 'int', false);
        $order = $filter['order'];
        $order ? $order = 'asc' : $order = 'desc';


        $having = '';
        $condition['biz_class.id'] = $class_id;
        $condition['auth_student.school_id'] = SCHOOL_ID;
        $condition['biz_class.school_id'] = SCHOOL_ID;
        if (!empty($filter['parent_name'])) $condition['auth_parent.parent_name'] = array('like', '%' . $filter['parent_name'] . '%');
        if (!empty($filter['parent_tel'])) $condition['auth_student_parent_contact.parent_tel'] = array('like', $filter['parent_tel'] . '%');
        if (!empty($filter['status'])) $condition['auth_parent.flag'] = $filter['status'];
        if (!empty($filter['student_name'])) $condition['auth_student.student_name'] = array('like', '%' . $filter['student_name'] . '%');
        if ($filter['privilege_type'] != '') $having = 'permissions_status=' . $filter['privilege_type'];

        $condition_string = '';
        foreach ($filter as $key => $val) {
            $condition_string .= '&' . $key . '=' . $val;
        }

        $result = $this->model->getClassParentData($condition, $order, $having);

        $this->assign('condition_str', $condition_string);
        $this->assign('parent_name', $filter['parent_name']);
        $this->assign('parent_tel', $filter['parent_tel']);
        $this->assign('status', $filter['status']);
        $this->assign('student_name', $filter['student_name']);
        $this->assign('privilege_type', $filter['privilege_type']);
        $this->assign('order', $order);

        $this->assign('list', $result['data']);
        $this->assign('page', $result['page']);
        $this->assign('class_id', $class_id);
        $this->display();
    }


    /*
* 修改班级
*/
    public function classModify()
    {
//        if (!session('?school')) redirect(U('Login/login'));
//        A('School/SchoolAdmin')->check_permissions();

        if (!empty($_POST)) {
            $class_id = getParameter('id', 'int');
            $class_name = getParameter('class', 'str');
            $result = $this->model->getClassInfo($class_id);


            $class_result_info = $this->model->getClassAndGradeInfo($class_id);
            $teacher_id_all = D('Biz_class')->getTeacherIdAll($class_id);
            $parent_id_all = D('Biz_class')->getParentIdAll($class_id);

            $student_id_all = D('Biz_class')->getStudentIdAll($class_id);

            if (empty($result)) {
                $this->error('班级信息有误!');
            } else {
                if ($result['school_id'] != SCHOOL_ID) {
                    $this->error('您没有权限修改此班级!');
                }
            }
            $class_con['biz_class.school_id'] = $school_id;
            $class_con['biz_class.grade_id'] = $grade;
            $class_con['biz_class.name'] = $class_name;
            $class_con['biz_class.is_delete'] = 0;
            $class_result = $this->model->getClassDataAll($class_con);
            if (!empty($class_result)) {
                $this->error('该学校下已有相同的班级!');
            }

            $data['name'] = $class_name;
            if (!$this->model->updateClassInfo($class_id, $data)) {
                $this->error('修改信息失败');
            }

//对老师的推送
            $parameters = array(
                'msg' => array(
                    $class_result_info['grade'],
                    $class_result_info['name'],
                    C('SCHOOL_ROOT'),
                    $class_result_info['grade'],
                    $class_name
                ),
                'url' => array('type' => 0,)
            );

            A('Home/Message')->addPushUserMessage('XIUGAI_CLASS', 2, implode(',', $teacher_id_all), $parameters);

//对家长的推送
            A('Home/Message')->addPushUserMessage('PARENTEDIT_CLASS', 4, implode(',', $parent_id_all), $parameters);

//对学生的推送
            A('Home/Message')->addPushUserMessage('STUDIT_CLASS', 3, implode(',', $student_id_all), $parameters);

            $this->redirect(U('Class/classList'), array('id' => $class_id));
        } else {
            $id = getParameter('id', 'int');
            $result = $this->model->getClassSchoolData($id, SCHOOL_ID);
            $this->assign('data', $result);
            $this->display();
        }
    }


    /*
* 导入班级视图
*/
    public function importClassView()
    {
//        if (!session('?school')) redirect(U('Login/login'));
//        A('School/SchoolAdmin')->check_permissions();

        $this->display('classImport');
    }


    /*
* code
* 1为gb2312转utf8
* 2为gbk转utf8
* 3为utf-8直接返回
* 4为utf8转gbk
*/
    function encode_string($code, $string)
    {
        $return_string = '';
        if ($code == 1) {
            $return_string = iconv('gbk', 'utf-8', $string);
        } else if ($code == 2) {
            $return_string = iconv('gbk', 'utf-8', $string);
        } else if ($code == 0) {
            $return_string = $string;
        } else {
            $return_string = iconv('utf-8', 'gbk', $string);
        }
        return $return_string;
    }


    /*
* 导入班级
*/
    public function importClass()
    {
//        if (!session('?school')) redirect(U('Login/login'));
//        A('School/SchoolAdmin')->check_permissions();

        if (empty($_FILES)) {
            $this->showjson(1001, '文件为空');  //1002文件为空
        }
        $csv = new CSV();
        $result = $csv->getCsvData($_FILES);
        if (!is_array($result)) {
            $this->showjson(1002, '文件内容为空');
        }
        $str_encode = A('School/Student')->getStringEncode($result['result'][0][0]);
        $i_data = $result['result'];
        $length = $result['length'];

        $school_model = D('Dict_schoollist');
        $grade_model = D('Dict_grade');
        $teacher_model = D('Auth_teacher');

        $school_id = SCHOOL_ID;
        $school_result = $school_model->getSchoolInfo($school_id);
        if (empty($school_result)) {
            $this->showjson(1003, ID_NOT_EXISTS_MESSAGE);
        }

        $failed_array = array();
        $success_array = array();
        for ($i = 1; $i < $length; $i++) {
            $data['grade'] = $this->encode_string($str_encode, $i_data[$i][0]);
            $data['class'] = $this->encode_string($str_encode, $i_data[$i][1]);
            $grade_result = $grade_model->getGradeByName($data['grade']);
            $notice = $data;
            if (empty($grade_result)) {
                $notice['notice_message'] = '年级不存在';
                $notice_array[] = $notice;
                continue;
            }
            $class_condition['dict_grade.id'] = $grade_result['id'];
            $class_condition['biz_class.name'] = $data['class'];
            $class_condition['biz_class.school_id'] = $school_id;
            $class_condition['biz_class.is_delete'] = 0;
            $class_count = $this->model->getClassCount($class_condition);
            if ($class_count) {
                $notice['notice_message'] = '班级已存在';
                $notice_array[] = $notice;
                continue;
            }
            $class_data['name'] = $data['class'];
            $class_data['grade_id'] = $grade_result['id'];
            $class_data['school_id'] = $school_id;
            $class_data['create_at'] = time();
            $class_data['flag'] = 1;
            $class_data['class_status'] = 1;
            $this->model->startTrans();
            if (!($insert_id = $this->model->addClass($class_data))) {
                $notice['notice_message'] = '数据插入失败';
                $notice_array[] = $notice;
                continue;
            }
            $update_data['class_code'] = $insert_id + CLASS_CODE_ADD_NUMBER;
            if (!$this->model->updateClassInfo($insert_id, $update_data)) {
                $notice['notice_message'] = '数据插入失败';
                $notice_array[] = $notice;
                continue;
            }
            $this->model->commit();
            $success_array[] = $data;
        }
        $big_data = array();
        $big_data['success'] = $success_array;
        $big_data['failed'] = $notice_array;
        $this->showjson(200, '', $big_data);
    }


    /*
* 下载导入失败的数据
*/
    public function downloadImportErrorData()
    {
        if (!session('?school')) redirect(U('Login/login'));

        $grade_arr = $_POST['grade'];
        $class_arr = $_POST['class'];

        $str = "年级,班级\n";
        $str = iconv('utf-8', 'gbk', $str);
        foreach ($grade_arr as $key => $val) {
            $grade = iconv('utf-8', 'gbk', $grade_arr[$key]);
            $class = iconv('utf-8', 'gbk', $class_arr[$key]);

            $str .= $grade . "," . $class . "\n";
        }
        $filename = date('Ymd') . rand(0, 1000) . '班级导入失败信息' . '.csv';
        $csv = new CSV();
        $csv->downloadFileCsv($filename, $str);
    }


//下载班级模板
    public function downloadClssDemo()
    {
        $csv = new CSV();
        $file = "Public/csv/school/schoolClassDemo.csv";
        $csv->downloadFile($file);
    }


    /*
* 批量导出班级信息
*/
    public function exportedClass()
    {
//        if (!session('?school')) redirect(U('Login/login'));
//        A('School/SchoolAdmin')->check_permissions();

        if (empty($_POST)) {
            $this->error('参数错误');
        } else {
            $condition_arr = I('hid');
            $condition['biz_class.id'] = array('in', $condition_arr);
            $condition['biz_class.school_id'] = SCHOOL_ID;
            $data = $this->model->getAllClassData($condition);
            $str = "年级,班级,班级代码,班级状态\n";
            $str = iconv('utf-8', 'gbk', $str);
            foreach ($data as $val) {
                $grade = iconv('utf-8', 'gbk', $val['grade']);
                $class = iconv('utf-8', 'gbk', $val['class_name']);
                $class_code = sprintf("%s", iconv('utf-8', 'gbk', $val['class_code']));
                if ($val['flag'] == 1) {
                    $status = '正常';
                } elseif ($val['flag'] == 2) {
                    $status = '移交中';
                } else {
                    $status = '停用';
                }
                $status = iconv('utf-8', 'gbk', $status);
                $str .= $grade . "," . $class . "," . $class_code . "," . $status . "\n";
            }
            $filename = date('Ymd') . rand(0, 1000) . 'admin' . '.csv';
            $csv = new CSV();
//export disable
//$csv->downloadFileCsv($filename,$str);
        }
    }


    /*
* 导出全部班级信息
*/
    public function exportedClassAll()
    {
//        if (!session('?school')) redirect(U('Login/login'));
//        A('School/SchoolAdmin')->check_permissions();

        set_time_limit(0);
        $filter['class_code'] = getParameter('class_code', 'str', false);
        $filter['grade'] = getParameter('grade', 'int', false);
        $filter['class'] = getParameter('class', 'int', false);
        $filter['class_flag'] = getParameter('class_flag', 'int', false);
        $filter['order'] = getParameter('order', 'str', false);
        $order = $filter['order'];
        $order ? $order = 'asc' : $order = 'desc';

        $condition['biz_class.school_id'] = SCHOOL_ID;
        $condition['biz_class.is_delete'] = 0;
        if (!empty($filter['class_code'])) $condition['biz_class.class_code'] = array('like', '%' . $filter['class_code'] . '%');
        if (!empty($filter['grade'])) $condition['dict_grade.id'] = $filter['grade'];
        if (!empty($filter['class'])) $condition['biz_class.id'] = $filter['class'];
        if (!empty($filter['class_flag'])) $condition['biz_class.flag'] = $filter['class_flag'];

        $data = $this->model->getAllClassData($condition, $order);
        $str = "年级,班级,班级代码,班级状态\n";
        $str = iconv('utf-8', 'gbk', $str);
        foreach ($data as $val) {
            $grade = iconv('utf-8', 'gbk', $val['grade']);
            $class = iconv('utf-8', 'gbk', $val['class_name']);
            $class_code = sprintf('%s', iconv('utf-8', 'gbk', $val['class_code']));
            if ($val['flag'] == 1) {
                $status = '正常';
            } elseif ($val['flag'] == 2) {
                $status = '移交中';
            } else {
                $status = '停用';
            }
            $status = iconv('utf-8', 'gbk', $status);
            $str .= $grade . "," . $class . "," . $class_code . "," . $status . "\n";
        }
        $filename = date('Ymd') . rand(0, 1000) . 'admin' . '.csv';
        $csv = new CSV();
//export disable
//$csv->downloadFileCsv($filename,$str);
    }

    /*
* 下载班级内导入学生的例子
*/
    public function downloadClassStudentDemo()
    {
        if (!session('?school')) redirect(U('Login/login'));

        $csv = new CSV();
        $file = "Public/csv/school/classStudentDeomo.csv";
        $csv->downloadFile($file);
    }


    /*
* 导入班级内的学生视图
*/
    public function importClassStudentView()
    {
//        if (!session('?school')) redirect(U('Login/login'));
//        A('School/SchoolAdmin')->check_permissions();

        $class_id = getParameter('id', 'int');
        $class_result = $this->model->getClassSchoolData($class_id);
        if (empty($class_result)) {
            $this->error('参数错误!');
        } else {
            if ($class_result['school_id'] != SCHOOL_ID) {
                $this->error('无权操作此班级!');
            }
        }
        $this->assign('class_id', $class_id);
        $this->display('class_iframe_student_import');
    }


    /*
* 下载导入失败的数据
*/
    public function downloadImportStudentErrorData()
    {
        if (!session('?school')) redirect(U('Login/login'));

        $student_name_arr = $_POST['student_name'];
        $parent_tel_arr = $_POST['parent_tel'];

        $str = "学生姓名,家长手机号\n";
        $str = iconv('utf-8', 'gbk', $str);
        foreach ($student_name_arr as $key => $val) {
            $student_name = iconv('utf-8', 'gbk', $val);
            $parent_tel = iconv('utf-8', 'gbk', $parent_tel_arr[$key]);

            $str .= $student_name . "," . $parent_tel . "\n";
        }
        $filename = date('Ymd') . rand(0, 1000) . '班级内学生导入失败信息' . '.csv';
        $csv = new CSV();
        $csv->downloadFileCsv($filename, $str);
    }


    /*
* 导入班级下的学生
*/
    public function importClassStudent()
    {
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions(true);

        if (empty($_FILES)) {
            $this->showjson(1001, '文件为空!');
        }
        $csv = new CSV();
        $result = $csv->getCsvData($_FILES);
        if (!is_array($result)) {
            $this->showjson(1002, '文件内容为空!');
        }
        $str_encode = A('School/student')->getStringEncode($result['result'][0][0]);
        $i_data = $result['result'];
        $length = $result['length'];


        $student_model = D('Auth_student');
        $class_id = getParameter('id', 'int');
        $class_result = $this->model->getClassSchoolData($class_id);
        if (empty($class_result)) {
            $this->showjson(1003, '班级参数错误!');
        } else {
            if ($class_result['school_id'] != SCHOOL_ID) {
                $this->showjson(1004, '无权操作此班级!');
            }
        }

        $class_result_info = $this->model->getClassAndGradeInfo($class_id);

        $notice_array = array();
        $success_array = array();

//如果学生存在就该条数据就跳过
        for ($i = 1; $i < $length; $i++) {
            $studetn_data['student_name'] = $this->encode_string($str_encode, $i_data[$i][0]);
            $studetn_data['parent_tel'] = $this->encode_string($str_encode, $i_data[$i][1]);
            $parent_info = D('Auth_parent')->getParentInfoByTelephone($studetn_data['parent_tel']);

            $notice = $studetn_data;
            $success = $studetn_data;
//判断手机号
            if (!preg_match("/^1[34578]{1}\d{9}$/", $studetn_data['parent_tel'])) {
                $notice['notice_message'] = '手机号格式不正确!';
                $notice_array[] = $notice;
                continue;
            }
            $student_result = $student_model->getStudentParentTel($studetn_data['student_name'], $studetn_data['parent_tel']);
            if (empty($student_result)) {
                $notice['notice_message'] = '学生信息不存在!';
                $notice_array[] = $notice;
                continue;
            }
//查看该学生是否加入过该班级
            $class_student_con['biz_class_student.class_id'] = $class_id;
            $class_student_con['biz_class_student.student_id'] = $student_result['id'];
            $class_result = $this->model->getClassStudentCount($class_student_con);
            if (!empty($class_result)) {
                $notice['notice_message'] = '该学生已在该班级内!';
                $notice_array[] = $notice;
                continue;
            }
            $data['class_id'] = $class_id;
            $data['student_id'] = $student_result['id'];
            $data['create_at'] = time();
            $data['status'] = 2;
            $this->model->startTrans();
            if (!$this->model->addClassStudentData($data)) {
                $this->model->rollback();
                $notice['notice_message'] = '数据入库失败1!';
                $notice_array[] = $notice;
                continue;
            }
            /*if(!$this->model->updateClassStudentCount($class_id,true)){
$this->model->rollback();
$notice['notice_message']='数据入库失败2!';
$notice_array[]=$notice;
continue;
}*/

            $parameters = array(
                'msg' => array(
                    $studetn_data['student_name'],
                    C('SCHOOL_ROOT'),
                    $class_result_info['grade'],
                    $class_result_info['name'],
                ),
                'url' => array('type' => 0,)
            );

            A('Home/Message')->addPushUserMessage('CLASSADDSTUDENT', 4, $parent_info['id'], $parameters);

//给学生发送

            $parametersstu = array(
                'msg' => array(
                    C('SCHOOL_ROOT'),
                    $class_result_info['grade'],
                    $class_result_info['name'],
                ),
                'url' => array('type' => 0,)
            );

            A('Home/Message')->addPushUserMessage('CLASSADDSENDSTUDENT', 3, $student_result['id'], $parametersstu);

            $this->model->commit();
            $success_array[] = $success;

        }
        $big_data = array();
        $big_data['success'] = $success_array;
        $big_data['failed'] = $notice_array;
        $this->showjson(200, 'success', $big_data);
    }


    /*
* 批量导出某班级下的学生
*/
    public function exportedClassStudent()
    {
        if (!session('?school')) redirect(U('Login/login'));
        if (A('School/SchoolAdmin')->check_permissions(0, true) == 1) {
            echo "<script>alert('无权操作');history.go(-1);</script>";
            die;
        }

        set_time_limit(0);
        if (empty($_POST)) {
            $this->error('参数错误');
        } else {
            $condition_arr = I('hid');
            $condition['auth_student.id'] = array('in', $condition_arr);
            $condition['auth_student.school_id'] = SCHOOL_ID;
            $data = $this->model->getClassStudentDataAll($condition);
            $str = "学生姓名,性别,家长手机号,账号状态\n";
            $str = iconv('utf-8', 'gbk', $str);
            foreach ($data as $v) {
                $student_name = iconv('utf-8', 'gbk', $v['student_name']);
                $sex = iconv('utf-8', 'gbk', $v['sex']);
                $parent_tel = iconv('utf-8', 'gbk', $v['parent_tel']);
                if ($v['flag'] == 1) {
                    $account_status = '正常';
                } else {
                    $account_status = '停用';
                }
                $account_status = iconv('utf-8', 'gbk', $account_status);

                $str .= $student_name . "," . $sex . "," . $parent_tel . "," . $account_status . "\n";
            }
            $filename = date('Ymd') . rand(0, 1000) . 'ClassStudent' . '.csv';
            $csv = new CSV();
//export disable
//$csv->downloadFileCsv($filename,$str);
        }
    }


    /*
* 导出某班级下的所有学生
*/
    public function exportedClassStudentAll()
    {
        if (!session('?school')) redirect(U('Login/login'));
        if (A('School/SchoolAdmin')->check_permissions(0, true) == 1) {
            echo "<script>alert('无权操作');history.go(-1);</script>";
            die;
        }

        $filter['class_id'] = getParameter('class_id', 'int');
        $filter['student_name'] = getParameter('student_name', 'str', false);
        $filter['parent_tel'] = getParameter('parent_tel', 'str', false);
        $filter['status'] = $_GET['status'];
        $filter['order'] = getParameter('order', 'int', false);
        $order = $filter['order'];
        $order ? $order = 'asc' : $order = 'desc';

        $condition['biz_class.id'] = $filter['class_id'];
        $condition['auth_student.school_id'] = SCHOOL_ID;
        if (!empty($filter['student_name'])) $condition['student_name'] = array('like', '%' . $filter['student_name'] . '%');
        if (!empty($filter['parent_tel'])) $condition['parent_tel'] = array('like', '%' . $filter['parent_tel'] . '%');
        if ($filter['status'] != '') $condition['auth_student.flag'] = intval($filter['status']);
        $data = $this->model->getClassStudentDataAll($condition, $order);

        $str = "学生姓名,性别,家长手机号,账号状态\n";
        $str = iconv('utf-8', 'gbk', $str);
        foreach ($data as $v) {
            $student_name = iconv('utf-8', 'gbk', $v['student_name']);
            $sex = iconv('utf-8', 'gbk', $v['sex']);
            $parent_tel = iconv('utf-8', 'gbk', $v['parent_tel']);
            if ($v['flag'] == 1) {
                $account_status = '正常';
            } else {
                $account_status = '停用';
            }
            $account_status = iconv('utf-8', 'gbk', $account_status);

            $str .= $student_name . "," . $sex . "," . $parent_tel . "," . $account_status . "\n";
        }
        $filename = date('Ymd') . rand(0, 1000) . 'ClassStudent' . '.csv';
        $csv = new CSV();
//export disable
//$csv->downloadFileCsv($filename,$str);
    }


    /*
* 下载班级内导入教师模板
*/
    public function downloadClassTeacherDemo()
    {
        if (!session('?school')) redirect(U('Login/login'));

        $csv = new CSV();
        $filepath = "Public/csv/school/schoolClassTeacherDemo.csv";
        $filename = "导入班级模板";
        $csv->downloadFileCopy($filepath,$filename);
        //$csv->downloadFile($file);
    }


    /*
* 班级内导入教师视图
*/
    public function importClassTeacherView()
    {
//        if (!session('?school')) redirect(U('Login/login'));
//        A('School/SchoolAdmin')->check_permissions();

        $class_id = getParameter('id', 'int');
        $class_result = $this->model->getClassSchoolData($class_id);
        if (empty($class_result)) {
            $this->error('参数错误!');
        } else {
            if ($class_result['school_id'] != SCHOOL_ID) {
                $this->error('无权操作此班级!');
            }
        }
        $this->assign('class_id', $class_id);
        $this->assign('class_status', $class_result['class_status']);
        $this->display('class_iframe_teacher_import');
    }


    /*
* 下载导入班级内教师失败的数据
*/
    public function downloadImportTeacherErrorData()
    {
        if (!session('?school')) redirect(U('Login/login'));

        $teacher_name_arr = $_POST['teacher_name'];
        $telephone_arr = $_POST['telephone'];
        $course_arr = $_POST['course'];
        $str = "教师姓名,教师手机号,任教学科\n";
        $str = iconv('utf-8', 'gbk', $str);

        foreach ($teacher_name_arr as $key => $val) {
            $teacher_name = iconv('utf-8', 'gbk', $val);
            $telephone = iconv('utf-8', 'gbk', $telephone_arr[$key]);
            $course = iconv('utf-8', 'gbk', $course_arr[$key]);
            $str .= $teacher_name . "," . $telephone . "," . $course . "\n";

        }
        $filename = date('Ymd') . rand(0, 1000) . '班级内教师导入失败信息' . '.csv';
        $csv = new CSV();
        $csv->downloadFileCsv($filename, $str);
    }


    /*
* 班级内导入教师
*/
    public function importClassTeacher()
    {
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions(true);

        if (empty($_FILES)) {
            $this->showjson(1001, '文件为空!');
        }
        $csv = new CSV();
        $result = $csv->getCsvData($_FILES);
        if (!is_array($result)) {
            $this->showjson(1002, '文件内容为空!');
        }
        $str_encode = A('Admin/Student')->getStringEncode($result['result'][0][0]);
        $i_data = $result['result'];
        $length = $result['length'];

        $course_model = D('Dict_course');
        $teacher_model = D('Auth_teacher');

        $class_id = getParameter('id', 'int');
        $class_result = $this->model->getClassSchoolData($class_id);     //class_status

        $class_info = $this->model->getClassAndGradeInfo($class_id);

        if (empty($class_result)) {
            $this->showjson(1003, '班级参数错误!');
        } else {
            if ($class_result['school_id'] != SCHOOL_ID) {
                $this->error('无权操作此班级!');
            }
        }

        $notice_array = array();
        $success_array = array();
        $tel_reg = "/^1[34578]{1}\d{9}$/";
        for ($i = 1; $i < $length; $i++) {
            $teacher_data['name'] = $this->encode_string($str_encode, $i_data[$i][0]);
            $teacher_data['telephone'] = $this->encode_string($str_encode, $i_data[$i][1]);
            $teacher_data['course'] = $this->encode_string($str_encode, $i_data[$i][2]);

            $notice = $teacher_data;
            $success_data = $teacher_data;
//判断手机号
            if (!preg_match($tel_reg, $teacher_data['telephone'])) {
                $notice['notice_message'] = '手机号格式不正确!';
                $notice_array[] = $notice;
                continue;
            }
//
            $teacher_result = $teacher_model->getTeacherByTel($teacher_data['telephone']);
            if (empty($teacher_result)) {
                $notice['notice_message'] = '教师信息不存在!';
                $notice_array[] = $notice;
                continue;
            }
            $teacher_con['name'] = $teacher_data['name'];
            $teacher_con['telephone'] = $teacher_data['telephone'];
            $teacher_result = $teacher_model->getTeacherInfo($teacher_con);
            if (empty($teacher_result)) {
                $notice['notice_message'] = '教师信息填写错误!';
                $notice_array[] = $notice;
                continue;
            }
            $class_teacher_data['class_id'] = $class_id;
            $class_teacher_data['teacher_id'] = $teacher_result['id'];
            $class_teacher_data['create_at'] = time();

            $teacher_course_con['class_id'] = $class_id;
            $teacher_course_con['teacher_id'] = $teacher_result['id'];

            $all_course = explode('.', $teacher_data['course']);
            if (empty($all_course)) {
                $notice['notice_message'] = '学科格式不正确!';
                $notice_array[] = $notice;
                continue;
            }
            $this->model->startTrans();
            $error = 0;
            for ($j = 0; $j < count($all_course); $j++) {
                $course_result = $course_model->getCourseData($all_course[$j]);
                if (empty($course_result)) {
                    $this->model->rollback();
                    $notice['notice_message'] = '学科不存在!';
                    $notice_array[] = $notice;
                    $error = 1;
                    break;
                }
                $class_teacher_data['course_id'] = $course_result['id'];
//查询该教师是否任教该班级学科

                $teacher_course_con['course_id'] = $all_course[$j];
                $teacher_course_result = $teacher_model->getTeacherClassTeachesInfo($teacher_course_con);
                if (!empty($teacher_course_result)) {
                    $this->model->rollback();
                    $notice['notice_message'] = '该教师已任教该班级该学科!';
                    $notice_array[] = $notice;
                    $error = 1;
                    break;
                }
                if (!$this->model->addClassTeacher($class_teacher_data)) {
                    $this->model->rollback();
                    $notice['notice_message'] = '数据入库失败!';
                    $notice_array[] = $notice;
                    $error = 1;
                    break;
                }
            }
            if ($error == 1) {
                continue;
            }

//消息推送
            $parameters = array(
                'msg' => array(
                    C('SCHOOL_ROOT'),
                    $class_info['grade'],
                    $class_info['name'],
                    $teacher_data['course']
                ),
                'url' => array('type' => 0, 'data' => array($class_id))
            );

            A('Home/Message')->addPushUserMessage('ADD_SCHOOL_CLASS', 2, $teacher_result['id'], $parameters);

            $this->model->commit();
            $success_array[] = $success_data;
        }
        $big_data = array();
        $big_data['success'] = $success_array;
        $big_data['failed'] = $notice_array;
        $this->showjson(200, 'success', $big_data);
    }


    /*
* 批量导出某个班下的教师
*/
    public function exportedClassTeacher()
    {
        if (!session('?school')) redirect(U('Login/login'));
        if (A('School/SchoolAdmin')->check_permissions(0, true) == 1) {
            echo "<script>alert('无权操作');history.go(-1);</script>";
            die;
        }

        set_time_limit(0);
        if (empty($_POST)) {
            $this->error('参数错误');
        } else {
            $condition_arr = I('hid');
            $condition['auth_teacher.id'] = array('in', $condition_arr);
            $condition['biz_class_teacher.is_handler'] = 0;
            $condition['biz_class.school_id'] = SCHOOL_ID;
            $condition['auth_teacher.school_id'] = SCHOOL_ID;

            $data = $this->model->getClassTeacher($condition);
            $str = "教师姓名,教师手机号,任教学科,账号状态\n";
            $str = iconv('utf-8', 'gbk', $str);
            foreach ($data as $v) {
                $teacher_name = iconv('utf-8', 'gbk', $v['teacher_name']);
                $telephone = iconv('utf-8', 'gbk', $v['telephone']);
                $course_name = iconv('utf-8', 'gbk', $v['course_name']);
                if ($v['flag'] == 1) {
                    $account_status = '正常';
                } else {
                    $account_status = '停用';
                }
                $account_status = iconv('utf-8', 'gbk', $account_status);
                $str .= $teacher_name . "," . $telephone . "," . $course_name . "," . $account_status . "\n";
            }
            $filename = date('Ymd') . rand(0, 1000) . 'ClassTeacher' . '.csv';
            $csv = new CSV();
//export disable
//$csv->downloadFileCsv($filename,$str);
        }
    }


    /*
* 导出某个班级下的所有教师
*/
    public function exportedClassTeacherAll()
    {
        if (!session('?school')) redirect(U('Login/login'));
        if (A('School/SchoolAdmin')->check_permissions(0, true) == 1) {
            echo "<script>alert('无权操作');history.go(-1);</script>";
            die;
        }

        set_time_limit(0);
        $filter['class_id'] = getParameter('class_id', 'int');
        $condition['biz_class.id'] = $filter['class_id'];
        $condition['biz_class_teacher.is_handler'] = 0;
        $condition['biz_class.school_id'] = SCHOOL_ID;
        $condition['auth_teacher.school_id'] = SCHOOL_ID;

        $data = $this->model->getClassTeacher($condition);
        $str = "教师姓名,教师手机号,任教学科,账号状态\n";
        $str = iconv('utf-8', 'gbk', $str);
        foreach ($data as $v) {
            $teacher_name = iconv('utf-8', 'gbk', $v['teacher_name']);
            $telephone = iconv('utf-8', 'gbk', $v['telephone']);
            $course_name = iconv('utf-8', 'gbk', $v['course_name']);
            if ($v['flag'] == 1) {
                $account_status = '正常';
            } else {
                $account_status = '停用';
            }
            $account_status = iconv('utf-8', 'gbk', $account_status);
            $str .= $teacher_name . "," . $telephone . "," . $course_name . "," . $account_status . "\n";
        }
        $filename = date('Ymd') . rand(0, 1000) . 'ClassTeacher' . '.csv';
        $csv = new CSV();
//export disable
//$csv->downloadFileCsv($filename,$str);
    }


    /*
* 批量导出某个班下的家长
*/
    public function exportedClassParent()
    {
        if (!session('?school')) redirect(U('Login/login'));
        if (A('School/SchoolAdmin')->check_permissions(0, true) == 1) {
            echo "<script>alert('无权操作');history.go(-1);</script>";
            die;
        }

        set_time_limit(0);
        if (empty($_POST)) {
            $this->error('参数错误');
        } else {
            $condition_arr = I('hid');
            $condition['auth_parent.id'] = array('in', $condition_arr);
            $condition['auth_student.school_id'] = SCHOOL_ID;
            $condition['biz_class.school_id'] = SCHOOL_ID;
            $data = $this->model->getClassParentDataAll($condition);

            $str = "家长姓名,性别,学生姓名,权限类型,家长手机号,账号状态\n";
            $str = iconv('utf-8', 'gbk', $str);
            foreach ($data as $v) {
                $parent_name = iconv('utf-8', 'gbk', $v['parent_name']);
                $sex = iconv('utf-8', 'gbk', $v['sex']);
                $student_name = iconv('utf-8', 'gbk', $v['student_name']);
                if ($v['permissions_status'] == 1) {
                    $privilege = 'vip权限';
                } else {
                    $privilege = '普通权限';
                }
                $privilege = iconv('utf-8', 'gbk', $privilege);
                $parent_tel = iconv('utf-8', 'gbk', $v['parent_tel']);
                if ($v['flag'] == 1) {
                    $account_status = '正常';
                } else {
                    $account_status = '停用';
                }
                $account_status = iconv('utf-8', 'gbk', $account_status);
                $str .= $parent_name . "," . $sex . "," . $student_name . "," . $privilege . "," . $parent_tel . "," . $account_status . "\n";
            }
            $filename = date('Ymd') . rand(0, 1000) . 'ClassParent' . '.csv';
            $csv = new CSV();
//export disable
//$csv->downloadFileCsv($filename,$str);
        }
    }


    /*
* 导出某个班下的所有家长
*/
    public function exportedClassParentAll()
    {
        if (!session('?school')) redirect(U('Login/login'));
        if (A('School/SchoolAdmin')->check_permissions(0, true) == 1) {
            echo "<script>alert('无权操作');history.go(-1);</script>";
            die;
        }

        set_time_limit(0);
        $filter['class_id'] = getParameter('class_id', 'int');
        $filter['parent_name'] = getParameter('parent_name', 'str', false);
        $filter['parent_tel'] = getParameter('parent_tel', 'str', false);
        $filter['status'] = getParameter('status', 'int', false);
        $filter['student_name'] = getParameter('student_name', 'str', false);
        $filter['privilege_type'] = $_GET['privilege_type'];
        $filter['order'] = getParameter('order', 'int', false);
        $order = $filter['order'];
        $order ? $order = 'asc' : $order = 'desc';

        $having = '';
        $condition['biz_class.id'] = $filter['class_id'];
        $condition['auth_student.school_id'] = SCHOOL_ID;
        $condition['biz_class.school_id'] = SCHOOL_ID;
        if (!empty($filter['parent_name'])) $condition['auth_parent.parent_name'] = array('like', '%' . $filter['parent_name'] . '%');
        if (!empty($filter['parent_tel'])) $condition['auth_student_parent_contact.parent_tel'] = array('like', $filter['parent_tel'] . '%');
        if (!empty($filter['status'])) $condition['auth_parent.flag'] = $filter['status'];
        if (!empty($filter['student_name'])) $condition['auth_student.student_name'] = array('like', '%' . $filter['student_name'] . '%');
        if ($filter['privilege_type'] != '') $having = 'permissions_status=' . $filter['privilege_type'];
        $data = $this->model->getClassParentDataAll($condition, $order, $having);

        $str = "家长姓名,性别,学生姓名,权限类型,家长手机号,账号状态\n";
        $str = iconv('utf-8', 'gbk', $str);
        foreach ($data as $v) {
            $parent_name = iconv('utf-8', 'gbk', $v['parent_name']);
            $sex = iconv('utf-8', 'gbk', $v['sex']);
            $student_name = iconv('utf-8', 'gbk', $v['student_name']);
            if ($v['permissions_status'] == 1) {
                $privilege = 'vip权限';
            } else {
                $privilege = '普通权限';
            }
            $privilege = iconv('utf-8', 'gbk', $privilege);
            $parent_tel = iconv('utf-8', 'gbk', $v['parent_tel']);
            if ($v['flag'] == 1) {
                $account_status = '正常';
            } else {
                $account_status = '停用';
            }
            $account_status = iconv('utf-8', 'gbk', $account_status);
            $str .= $parent_name . "," . $sex . "," . $student_name . "," . $privilege . "," . $parent_tel . "," . $account_status . "\n";
        }
        $filename = date('Ymd') . rand(0, 1000) . 'ClassParent' . '.csv';
        $csv = new CSV();
//export disable
//$csv->downloadFileCsv($filename,$str);
    }
}