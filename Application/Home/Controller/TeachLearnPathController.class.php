<?php
namespace Home\Controller;

use Think\Controller;
class TeachLearnPathController extends PublicController{

    public function __construct() {
        parent::__construct();

        header("Content-type: text/html; charset=utf-8");
        $this->assign('oss_path',C('oss_path'));
        $this->teacherId_id_online = session('teacher.id');


    }


    //所有班级列表
    public function classList() {
        $userId = -1;
        $role = -1;

        A('Home/Common')->getUserIdRole($userId,$role);

        $row = A('Home/Common')->getLearnPathPrompt($userId,$role);
        if(empty($row)) {
            $this->display('LearnPathFunctionGuidance');
            exit();
        }
        if (!$this->teacherId_id_online) {
            redirect(U('Teach/index1?auth_error=1'));
        }
        //权限
        A('Home/Common')->authJudgement();
        /*$isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }*/
        $id = getParameter ('id', 'int',false);

        //学习轨迹班级列表
        $result = D('Biz_classList')->getClassListTeacherAllCopy();

        if (!empty($id)) { //如果没有选择id就默认选择第一个id
            $this->assign('id', $id);
        } else {
            $this->assign('id', $result[0]['id']);
        }

        $this->assign('list', $result);

        $this->assign('module', '班级行');
        $this->assign('nav', '学习轨迹');
        $this->assign('subnav', '学生学习轨迹');
        $this->assign('navicon', 'xuexiguiji');
        $this->display();

    }

    //根据班级id获取班级学生和学生学习轨迹
    public function getClassStuPath() {

        if (!session('?teacher')) redirect(U('Index/index'));

        /*$isAuth = $this->isAuth($this->c_a);

        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }*/

        $this->assign('module', '班级行');
        $this->assign('nav', '班级管理');
        $this->assign('subnav', '班级学生');
        $this->assign('navicon', 'xuexiguiji');

        $classId = getParameter('classId', 'int',false);
        if (empty($classId))
            redirect(U('Index/index'));
        $this->assign('classId', $classId);

        //获取班级学生列表
        $result = D('Biz_classList')->getClassStuList( $classId );

        $courses = D('Dict_course')->getCourseList();

        $this->assign('courses', $courses);

        $this->assign('list', $result['list']);
        $this->assign('student_count', $result['stu_count']);
        $this->display();
    }


    //获取作业成绩（根据班级编号）
    public function getHomeworkScoreByClassId()
    {
        if (!session('?teacher'))  redirect(U('Index/index'));

        $classId = getParameter('class_id', 'int',false);
        if (empty($classId))
            redirect(U('Index/index'));
        $strat_date= getParameter('startDate', 'str',false);
        $end_date= getParameter('endDate', 'str',false);
        $courseId= getParameter('courseId', 'str',false);
        $filter['start'] = $strat_date;
        $filter['end'] = $end_date;

        if (!empty($filter['start'])) {
            $startime = strtotime($filter['start']);
            $startime = date('Y-m-d 00:00:00', $startime);
            $check['biz_homework.create_at'] = array('egt',strtotime($startime));
        }

        if (!empty($filter['end'])) {
            $endtime = strtotime($filter['end']);
            $endtime = date('Y-m-d 23:59:59', $endtime);
            $check['biz_homework.create_at'] = array('elt',strtotime($endtime));
        }

        if (!empty($filter['start']) && !empty($filter['end']) ){
            $startime = strtotime($filter['start']);
            $startime = date('Y-m-d 00:00:00', $startime);
            $endtime = strtotime($filter['end']);
            $endtime = date('Y-m-d 23:59:59', $endtime);
            $check['biz_homework.create_at'] = array(array('egt',strtotime($startime)),array('elt',strtotime($endtime))) ;
        }
        if (!empty($courseId)) {
            $check['biz_homework.course_id'] = $courseId;
        }

        $result = D('Biz_classList')->getHomeworkScoreByClass( $classId,$check );

        $this->ajaxReturn($result);
    }


    //获取教师寄语页面
    public function teacherMessage() {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '学习轨迹');
        $this->assign('navicon', 'xuexiguiji');

        $studentId = getParameter('student_id', 'int',false);
        $classId = getParameter('classId', 'int',false);
        $keyword = getParameter('keyword', 'str',false);

        $where['class_id'] = $classId;
        $where['student_id'] = $studentId;

        if (!empty($keyword)) {
            $where['content'] = array('like', '%' . $keyword . '%');
        }

        $path = D('Biz_classList')->getTeacherMessage( $where );
        if ( !empty($path) ) {
            foreach ($path as $k=>&$v){
                $v['week_name'] = getTimeWeek($v['create_at']);
            }
        }

        $this->assign('list', $path);
        $this->assign('classId', $classId);
        $this->assign('student_id', $studentId);
        $this->assign('keyword', $keyword);
        $this->display();
    }

    //添加老师寄语
    public function addTeacherMessage() {
        $Model = M('biz_student_learning_path');
        $data['student_id'] = getParameter('student_id', 'int',false);
        $data['teacher_id'] = session('teacher.id');
        $data['class_id'] = getParameter('classId', 'int',false);
        $data['content'] = getParameter('content', 'str',false);
        $data['create_at'] = time();

        if(empty($data['student_id'])) {
            $this->error('参数错误');
        }

        if(empty($data['class_id'])) {
            $this->error('参数错误');
        }
        $recordId = $Model->add($data);
        if ($recordId) {
            $this->redirect('teacherMessage', array('student_id' => $data['student_id'],'classId'=>$data['class_id']) );
        } else {
            $this->error('添加失败');
        }

    }

    //删除寄语
    public function delTeacherMessage() {
        $id = getParameter('id', 'int',false);
        if (empty($id))
            $this->ajaxReturn('error');
        $map['id'] = $id;
        $del = D('Biz_classList')->dTMessage( $map );
        if ( $del )
            $this->ajaxReturn('success');
        else
            $this->ajaxReturn('error');

    }

    //成绩分析
    public function scoreAnalysis() {

        $this->assign('module', '班级行');
        $this->assign('nav', '学习轨迹');
        $this->assign('navicon', 'xuexiguiji');

        //$studentId = $_GET['id'];
        $studentId = getParameter('id', 'int',false,2);
        $classId = getParameter('classId', 'int',false);
        $this->assign('class_id', $classId);
        $this->assign('studentId', $studentId);

        $student = D('Auth_student')->getStudentInfoByStuId( $studentId );
        $this->assign('student', $student);

        $courses = D('Dict_course')->getCourseList();

        //获取学生的错题集
        $errorwork = D('Biz_homework_student_details')->getStudentErrorHomework($studentId,$classId);

        $this->assign('errorwork', $errorwork);

        $this->assign('courses', $courses);
        $this->assign('subnav', $student['student_name'] . '同学');

        $this->display();
    }


    //获取学生作业成绩（根据学生编号）
    public function getHomeworkScoreByStudentId()
    {
        $student_tag= getParameter('tag', 'int',false);
        $studentId = getParameter('student_id', 'str',false);
        $class_id = getParameter('class_id', 'str',false);
        $strat_date= getParameter('startDate', 'str',false);
        $end_date= getParameter('endDate', 'str',false);
        $course_id= getParameter('course', 'int',false);

        if(empty($student_tag)){
            $where['auth_student.id'] = $studentId;
        }else{
            if(empty($studentId)) //student id is null
                $this->ajaxReturn(array());
            $where['_string'] ="auth_student.id in (".$studentId.')';
        }

        $filter['start'] = $strat_date;
        $filter['end'] = $end_date;

        if (!empty($filter['start'])) {
            $startime = strtotime($filter['start']);
            $startime = date('Y-m-d 00:00:00', $startime);
            $where['biz_homework.create_at'] = array('egt',strtotime($startime));
        }

        if (!empty($filter['end'])) {
            $endtime = strtotime($filter['end']);
            $endtime = date('Y-m-d 23:59:59', $endtime);
            $where['biz_homework.create_at'] = array('elt',strtotime($endtime));
        }

        if (!empty($filter['start']) && !empty($filter['end']) ){
            $startime = strtotime($filter['start']);
            $startime = date('Y-m-d 00:00:00', $startime);
            $endtime = strtotime($filter['end']);
            $endtime = date('Y-m-d 23:59:59', $endtime);
            $where['biz_homework.create_at'] = array(array('egt',strtotime($startime)),array('elt',strtotime($endtime))) ;
        }


        if(!empty($course_id)){
            $where['biz_homework.course_id'] = $course_id;
        }
        if (!empty($class_id)) {
            $where['biz_homework.class_id'] = $class_id;
        }

        $result = D('Biz_homework_student_details')->getHomeworkScoreByStudent( $where );

        $this->ajaxReturn($result);
    }

    //获取错题集
    public function homeworkExercises() {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '作业系统');
        $this->assign('navicon', 'zuoyexitong');
        $homework= getParameter('id', 'int',false);
        $studentId= getParameter('studentId', 'int',false);
        $this->assign('studentId', $studentId);

        if(!$homework){
            redirect(U('Index/systemError'));
            die;
        }

        $map['id'] = $homework;
        $result = D('Biz_homework_student_details')->getHomeWorkD( $map );

        $bhsdmap['homework_id'] = $homework;
        $bhsdmap['student_id'] = $studentId;

        $bhsd_info = D('Biz_homework_student_details')->getHomeWorkBhsd( $bhsdmap );


        $chapter_result=array();
        if(!empty($result)){
            $where['homework_id']=$homework;
            $chapter_result = D('Biz_homework_student_details')->getChapterInfo( $where );
        }
        $this->assign('data', $result);
        $this->assign('chapter_data', $chapter_result);
        $this->assign('bhsd_info', $bhsd_info);

        $this->display();
    }

    //判断是否有权限
    public function isAuth( $c_a ) {

        $teacher = session('auth_teacher');
        $parent = session('auth_parent');
        $student = session('auth_student');
        $admin = session('admin');
        if (!empty($teacher)) {

            $is_auth = in_array($c_a, session('auth_teacher'));

        } elseif(!empty($parent)) {
            $is_auth = in_array($c_a, session('auth_parent'));

        }elseif(!empty($student)){
            $is_auth = in_array($c_a, session('auth_student'));

        } elseif(!empty($admin)) {
            return true;
        }

        if ( $is_auth ) {
            return true;
        } else {
            return false;
        }
    }


}