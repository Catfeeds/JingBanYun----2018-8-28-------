<?php
namespace Home\Controller;

use Think\Controller;
class ParentLearnPathController extends PublicController{

    public function __construct() {
        parent::__construct();
        header("Content-type: text/html; charset=utf-8");
        $this->assign('oss_path',C('oss_path'));
        $teacherId_id_online = session('parent.id');
        if (!$teacherId_id_online) {
            $this->redirect(U('Index/index'));
        }

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

        //权限
        A('Home/Common')->authJudgement();

        $this->assign('module', '班级行');
        $this->assign('nav', '学习轨迹');
        $this->assign('subnav', '');
        $this->assign('navicon', 'xuexiguiji');

        //家长获取学生信息并获取学生所在的班级
        $class_id = getParameter ('class_id', 'int',false);
        $student_id = getParameter ('student_id', 'int',false);

        $result = D('Auth_student')->getParentStudents(session('parent.id'),'','');

        if (!empty($class_id)) { //如果没有选择id就默认选择第一个id
            $this->assign('class_id', $class_id);
        } else {
            $this->assign('class_id', $result[0]['class_id']);
        }

        if (!empty($student_id)) { //如果没有选择id就默认选择第一个id
            $this->assign('student_id', $student_id);
        } else {
            $this->assign('student_id', $result[0]['sid']);
            $this->assign('isshow', 1);
        }

        $this->assign('list', $result);
        $this->display();
    }

    //ifrm 获取学生的学习轨迹
    public function getClassStuPath() {
        //查询学科
        $courses = D('Dict_course')->getCourseList();
        //查询是否有老师寄语
        $student_id = getParameter ('student_id', 'int',false);
        $class_id = getParameter ('class_id', 'int',false);
        if (!empty($student_id) || !empty($class_id)) {
            $map['student_id'] = $student_id;
            $map['class_id'] = $class_id;

            //判断是否显示老师寄语
            $messageCount = D('Biz_class')->getTeacherMessage( $map );

            $this->assign('messageCount', $messageCount);
            $this->assign('courses', $courses);

            //获取学生的错题集
            $errorwork = D('Biz_homework_student_details')->getStudentErrorHomework($student_id,$class_id);

            $this->assign('errorwork', $errorwork);
            $student = D('Auth_student')->getStudentInfoByStuId($student_id);
            $this->assign('student', $student);
            $this->assign('student_id', $student_id);
            $this->assign('class_id', $class_id);
        }

        $this->display();
    }

    //获取作业成绩（根据学生编号）
    public function getHomeworkScoreByStudentId()
    {
        /*
        $student_tag=$_GET['tag'];
        $studentId = $_GET['student_id'];
        $strat_date=$_GET['startDate'];
        $end_date=$_GET['endDate'];
        $course_id=$_GET['course'];
        */
        $student_tag= getParameter('tag', 'int',false);
        $studentId = getParameter('student_id', 'str',false);
        $class_id = getParameter('class_id', 'str',false);
        $strat_date= getParameter('startDate', 'str',false);
        $end_date= getParameter('endDate', 'str',false);
        $course_id= getParameter('course', 'int',false);

        if (!empty($studentId) && !empty($class_id)) {
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

            $result = D('Auth_student')->getStudentPath( $where );
        } else {
            $result = [];
        }


        $this->ajaxReturn($result);
    }

    //查询学生的寄语
    public function studentMessage() {

        $this->assign('module', '班级行');
        $this->assign('nav', '学习轨迹');
        $this->assign('navicon', 'xuexiguiji');

        $studentId = getParameter('student_id', 'int',false);
        $classId = getParameter('class_id', 'int',false);
        $keyword = getParameter('keyword', 'str',false);

        $where['class_id'] = $classId;
        $where['student_id'] = $studentId;

        if (empty($studentId) || empty($classId)) {
            die("非正常参数错误");
        }

        if (!empty($keyword)) {
            $where['content'] = array('like', '%' . $keyword . '%');
        }

        $path = D('Biz_classList')->getTeacherMessage( $where );

        foreach ($path as $k=>$v) {
            $map['id'] = $v['teacher_id'];
            $teacherinfo = D('Auth_teacher')->getTeacherInfo( $map );
            $path[$k]['teacher_name'] =$teacherinfo['name'];
            $path[$k]['week_name'] = getTimeWeek($v['create_at']);
        }

        $this->assign('list', $path);
        $this->assign('classId', $classId);
        $this->assign('student_id', $studentId);
        $this->assign('keyword', $keyword);
        $this->display();
    }


    //获取错题集
    public function homeworkExercises() {


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