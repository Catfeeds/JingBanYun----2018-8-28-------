<?php

namespace School\Controller;

use Think\Controller;

define('STUDENT_AWAIT_APPLY_SCHOOL_STATUS', 0);
define('TEACHER_AWAIT_APPLY_SCHOOL_STATUS', 0);
define('STUDENT_PASS_APPLY_SCHOOL_STATUS', 1);
define('TEACHER_PASS_APPLY_SCHOOL_STATUS', 1);
define('SCHOOL_AUTH_CLASS', 1);

class IndexController extends Controller
{

    public $model;
    public $page_size = 20;

    public function __construct()
    {
        parent::__construct();
        if (!session('?school')) redirect(U('Login/login'));
        if($_GET['error']==1){
            echo "<script>alert('无权进行此操作!')</script>";
        }
        $this->model = D('Auth_school_admin');
        $this->assign('oss_path', 'http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }



    /*
     * 首页
     */
    /* public function indexs(){
         if (!session('?school')) redirect(U('Login/login'));


         if($_GET['error']==1){
             echo "<script>alert('无权进行此操作!')</script>";
         }

         $teacher_model=D('Auth_teacher');
         $student_model=D('Auth_student');
         $class_model=D('Biz_class');
         $school_model=D('Dict_schoollist');
         $teacher_condition['apply_school_status']=TEACHER_AWAIT_APPLY_SCHOOL_STATUS;
         $teacher_condition['auth_teacher.school_id']=session('school.school_id');
         $teacher_result=$teacher_model->getTeacherFixedData($teacher_condition);

         $student_condition['apply_school_status']=STUDENT_AWAIT_APPLY_SCHOOL_STATUS;
         $student_condition['auth_student.school_id']=session('school.school_id');
         $student_result=$student_model->getStudentFixedData($student_condition);

         $class_condition['biz_class.school_id']=session('school.school_id');
         $class_condition['class_status']=SCHOOL_AUTH_CLASS;
         $class_condition['biz_class.is_delete']=0;
         $class_count=$class_model->getClassCount($class_condition);

         $teacher_count_condition['apply_school_status']=TEACHER_PASS_APPLY_SCHOOL_STATUS;
         $teacher_count_condition['auth_teacher.school_id']=session('school.school_id');
         $teacher_count=$teacher_model->getTeacherCount($teacher_count_condition);

         $student_count_condition['apply_school_status']=STUDENT_PASS_APPLY_SCHOOL_STATUS;
         $student_count_condition['auth_student.school_id']=session('school.school_id');
         $student_count=$student_model->getStudentCount($student_count_condition);

         $school_result=$school_model->getSchoolInfo(session('school.school_id'));
         $school_name=$school_result['school_name'];
         $string=$school_name.'现有班级'.$class_count.'个,'.'教师'.$teacher_count.'人,'.'学生'.$student_count.'人';

         $this->assign('login_user',session('school.name'));
         $this->assign('introduce',$string);
         $this->assign('teacher_list',$teacher_result);
         $this->assign('student_list',$student_result);

         $this->display();
     }*/
    public function format($date, $type)
    {
        if ($type === 'Y') {
            $start_date = date('Y', mktime(00, 00, 00, 00, 00, date('Y', mktime(00, 00, 00, 00, 00, $date)) + 3));
            $start_date = strtotime($start_date . '-01-01');
        } elseif ($type == 'm') {
            $start_date = date('Y-m-d', mktime(00, 00, 00, date('m', strtotime($date)) + 1, 01));
            $start_date = strtotime($start_date);
        }

        return $start_date;
    }

    /*
     *新版首页
     */
    public function Index()
    {
        /*$date = '2018-05';
        $start_date = date('Y-m-d', mktime(00, 00, 00, date('m', strtotime($date))+1, 01));
        $end_date = date('Y-m-d', mktime(23, 59, 59, date('m', strtotime($date))+1, 00));
        var_dump($start_date);die;*/
        $teacher_model = D('Auth_teacher');
        $student_model = D('Auth_student');
        $class_model = D('Biz_class');
        $school_model = D('Dict_schoollist');
        $teacher_condition['apply_school_status'] = TEACHER_AWAIT_APPLY_SCHOOL_STATUS;
        $teacher_condition['auth_teacher.school_id'] = session('school.school_id');
        $teacher_result = $teacher_model->getTeacherFixedData($teacher_condition);

        $student_condition['apply_school_status'] = STUDENT_AWAIT_APPLY_SCHOOL_STATUS;
        $student_condition['auth_student.school_id'] = session('school.school_id');
        $student_result = $student_model->getStudentFixedData($student_condition);

        $class_condition['biz_class.school_id'] = session('school.school_id');
        $class_condition['class_status'] = SCHOOL_AUTH_CLASS;
        $class_condition['biz_class.is_delete'] = 0;
        $class_count = $class_model->getClassCount($class_condition);

        $teacher_count_condition['apply_school_status'] = TEACHER_PASS_APPLY_SCHOOL_STATUS;
        $teacher_count_condition['auth_teacher.school_id'] = session('school.school_id');
        $teacher_count = $teacher_model->getTeacherCount($teacher_count_condition);

        $student_count_condition['apply_school_status'] = STUDENT_PASS_APPLY_SCHOOL_STATUS;
        $student_count_condition['auth_student.school_id'] = session('school.school_id');
        $student_count = $student_model->getStudentCount($student_count_condition);

        $school_result = $school_model->getSchoolInfo(session('school.school_id'));
        $school_name = $school_result['school_name'];
        $string = $school_name . '现有班级' . $class_count . '个,' . '教师' . $teacher_count . '人,' . '学生' . $student_count . '人';

        $this->assign('login_user', session('school.name'));
        $this->assign('introduce', $string);
        /**************************筛选时间**************************************/
        $schoolId = session('school.school_id');
        $times = getParameter('times', 'str', false);
        $startTime = getParameter('startTime', 'str', false);
        $endTime = getParameter('endTime', 'str', false);
        if (empty($times) || (empty($startTime) && empty($endTime))) {
            $where['access_time'] = array(array('LT', strtotime(date('Y-m-d'))), array('EGT', strtotime(date('Y-m-d'))-604800));
            $times = 'day';
            $startTime = date('Y-m-d', strtotime(date('Y-m-d')) - 604800);
            $endTime = date('Y-m-d', strtotime(date('Y-m-d')));
        }
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);
        $this->assign('times', $times);
        $this->display();
    }

    /*
     *描述：人员登录统计接口
     */
    public function getLoginPersonCount(){
        /**************************筛选时间**************************************/
        $schoolId = session('school.school_id');
        $times = getParameter('times', 'str', false);
        $startTime = getParameter('startTime', 'str', false);
        $endTime = getParameter('endTime', 'str', false);
        if (empty($times) || (empty($startTime) && empty($endTime))) {
            $where['access_time'] = array(array('LT', strtotime(date('Y-m-d'))), array('EGT', strtotime(date('Y-m-d'))-604800));
        } else {
            if ($times == 'day') {
                $where['access_time'] = array(array('LT', strtotime($endTime)+86400), array('EGT', strtotime($startTime)));
            } elseif ($times == 'month') {
                $where['access_time'] = array(array('LT', $this->format($startTime, 'm')), array('EGT', strtotime($startTime.'-01')));
            } elseif ($times == 'year') {
                $where['access_time'] = array(array('LT', $this->format($startTime, 'Y')), array('EGT', strtotime($startTime.'-01-01')));
            }
        }

        /*****************************获取登录教师*********************************/
        $where['school_id'] = $schoolId;
        $where['apply_school_status'] = TEACHER_PASS_APPLY_SCHOOL_STATUS;
        $teacherData = M('usertables.access_history_teacher')
            ->field('usertables.access_history_teacher.id')
            ->join('auth_teacher ON auth_teacher.id = usertables.access_history_teacher.teacher_id')
            ->where($where)
            ->group('auth_teacher.id')
            ->select();
        /*****************************获取登录学生*********************************/
        unset($where['apply_school_status']);
        $studentData = M('usertables.access_history_student')
            ->field('usertables.access_history_student.id')
            ->join('auth_student ON auth_student.id = usertables.access_history_student.student_id')
            ->where($where)
            ->group('auth_student.id')
            ->select();
        /*****************************获取登录家长*********************************/
        unset($where['school_id']);
        $where['auth_student.school_id'] = $schoolId;
        $parentData = M('usertables.access_history_parent')
            ->field('usertables.access_history_parent.id')
            ->join('auth_parent ON auth_parent.id = usertables.access_history_parent.parent_id')
            ->join('auth_student_parent_contact ON auth_student_parent_contact.parent_id = auth_parent.id')
            ->join('auth_student ON auth_student.id = auth_student_parent_contact.student_id')
            ->where($where)
            ->group('auth_parent.id')
            ->select();
        $data['teacherCount'] = count($teacherData);
        $data['studentCount'] = count($studentData);
        $data['parentCount'] = count($parentData);
        $this->ajaxreturn(array('status' => '200', 'data' => $data));
    }

    /**
     *描述：学生作业统计数据接口
     *parms type:1、作业布置2、作业完成3、作业得分
     */
    public function studentHomeWorkStatistics()
    {
        $schoolId = session('school.school_id');
//$schoolId = 1035;
        $exercises_homwork_basics = M('exercises_homwork_basics');
        $exercises_student_homework = M('exercises_student_homework');
        $type = getParameter('type', 'int');
        $times = getParameter('times', 'str', false);
        $startTime = getParameter('startTime', 'str', false);
        $endTime = getParameter('endTime', 'str', false);
        if (empty($times) || (empty($startTime) && empty($endTime))) {
            $where['release_time'] = array(array('LT', strtotime(date('Y-m-d'))), array('EGT', strtotime(date('Y-m-d'))-604800));
        } else {
            if ($times == 'day') {
                $where['release_time'] = array(array('LT', strtotime($endTime)+86400), array('EGT', strtotime($startTime)));
            } elseif ($times == 'month') {
                $where['release_time'] = array(array('LT', $this->format($startTime, 'm')), array('EGT', strtotime($startTime.'-01')));
            } elseif ($times == 'year') {
                $where['release_time'] = array(array('LT', $this->format($startTime, 'Y')), array('EGT', strtotime($startTime.'-01-01')));
            }
        }
        $whereCompletedHomeWork['auth_student.school_id'] = $schoolId;
        $whereCompletedHomeWork['correct_status'] = 1;//批改完的状态
        $completedHomeWork = $exercises_student_homework
            ->field('exercises_student_homework.id,exercises_student_homework.total_score score,exercises_homwork_basics.total_score,sum(work_timeout) work_timeouts')
            ->join('auth_student ON auth_student.id = exercises_student_homework.student_id')
            ->join('exercises_homwork_basics ON exercises_homwork_basics.id = exercises_student_homework.work_id')
            ->where($whereCompletedHomeWork)
            ->select();
        $whereCompletedHomeWorkCount['auth_student.school_id'] = $schoolId;
        $whereCompletedHomeWorkCount['correct_status'] = 1;
        $completedHomeWorkCount = $exercises_student_homework
            ->join('auth_student ON auth_student.id = exercises_student_homework.student_id')
            ->where($whereCompletedHomeWorkCount)
            ->count();
//echo M()->getLastSql();die;
        //算出整个学校的教师所布置的作业总数
        $where['school_id'] = $schoolId;
        $where['apply_school_status'] = TEACHER_PASS_APPLY_SCHOOL_STATUS;
        $homeWorkData = $exercises_homwork_basics
            ->join('auth_teacher ON auth_teacher.id = exercises_homwork_basics.create_user_id')
            ->where($where)
            ->select();
        $total = count($homeWorkData);
        if ($type == 1) {
            //算出每个学科的百分比
            $homeWorkCourseData = $exercises_homwork_basics
                ->field("count(1) countbycourse,course_name")
                ->join('auth_teacher ON auth_teacher.id = exercises_homwork_basics.create_user_id')
                ->where($where)
                ->group('exercises_homwork_basics.course_id')
                ->select();
//echo M()->getLastSql();die;
            foreach ($homeWorkCourseData as $k => $item) {
                $data[$k]['name'] = $item['course_name'];
                $data[$k]['num'] = $item['countbycourse'] + 0;
                $data[$k]['y'] = round(($item['countbycourse'] / $total) * 100);
            }
            /*$data[0]['name'] = '语文';
            $data[0]['num'] = 110;
            $data[0]['y'] = 10;
            $data[1]['name'] = '语文12';
            $data[1]['num'] = 126;
            $data[1]['y'] = 20;
            $data[2]['name'] = '语文343';
            $data[2]['num'] = 155;
            $data[2]['y'] = 30;
            $data[3]['name'] = '语文8';
            $data[3]['num'] = 112;
            $data[3]['y'] = 10;
            $data[4]['name'] = '语文7';
            $data[4]['num'] = 56;
            $data[4]['y'] = 10;
            $data[5]['name'] = '语文9';
            $data[5]['num'] = 89;
            $data[5]['y'] = 20;*/
        } elseif ($type == 2) {
            $data[0]['num'] = $completedHomeWorkCount+0;
            $data[0]['name'] = '已完成';
            $data[0]['y'] = round(($completedHomeWorkCount / $total)* 100) ;
            $data[1]['num'] = $total - $completedHomeWorkCount;
            $data[1]['name'] = '未完成';
            $data[1]['y'] = 1 - round(($completedHomeWorkCount / $total) * 100);


           /* $data[0]['num'] = 500;
            $data[0]['name'] = '已完成';
            $data[0]['y'] = 40;
            $data[1]['num'] = 260;
            $data[1]['name'] = '未完成';
            $data[1]['y'] = 60;*/

        } elseif ($type == 3) {
            $fail = 0;
            $pass = 0;
            $good = 0;
            $excellent = 0;
            $totaly = 0;
            //一份作业的正确率为：这份作业中的每道题的正确率相加/这份作业的题数
            foreach ($completedHomeWork as $val) {
                $percentage = round(($val['score'] / $val['total_score']) * 100);
                $totalPercentage += $percentage;
                if ($percentage > 0 && $percentage <= 40) {
                    $fail++;
                } elseif ($percentage > 40 && $percentage <= 60) {
                    $pass++;
                } elseif ($percentage > 60 && $percentage <= 80) {
                    $good++;
                } elseif ($percentage > 80 && $percentage <= 100) {
                    $excellent++;
                }
            }
            $data[0]['num'] = $fail;
             $data[0]['name'] = '不及格';
            $data[0] ['y'] = round(($fail / count($completedHomeWork)) * 100);
            $data[1] ['num'] = $pass;
            $data[1]['name'] = '及格';
            $data[1] ['y'] = round(($pass / count($completedHomeWork)) * 100);
            $data[2] ['num'] = $good;
             $data[2]['name'] = '良';
            $data[2] ['y'] = round(($good / count($completedHomeWork)) * 100);
            $data[3] ['num'] = $excellent;
             $data[3]['name'] = '优秀';
            $data[3] ['y'] = round(($excellent / count($completedHomeWork)) * 100);

            /*$data[0]['num'] = 100;
            $data[0]['name'] = '不及格';
            $data[0]['y'] = 20;
            $data[1]['num'] = 200;
            $data[1]['name'] = '及格';
            $data[1]['y'] = 30;
            $data[2]['num'] = 300;
            $data[2]['name'] = '良';
            $data[2]['y'] = 10;
            $data[3]['num'] = 600;
            $data[3]['name'] = '优秀';
            $data[3]['y'] = 40;*/
        }
        /******************************提交平均时间*********************************/
        $dataTwo['submit_average_time'] = $completedHomeWork[0]['work_timeouts'] / count($completedHomeWork);
        /**************************平均得分*************************************/
        $dataTwo['average_score'] = $totalPercentage / count($completedHomeWork);
        /**************************作业完成率**************************************/
        $dataTwo['completedPercentage'] = round(($completedHomeWorkCount / $total) * 100);
        /**************************作业平均正确率*****************************/


        /*$dataTwo['submit_average_time'] = 66;
        $dataTwo['average_score'] = 68;
        $dataTwo['completedPercentage'] = 67;*/


        $this->ajaxreturn(array('status' => '200', 'data' => array($data, $dataTwo)));
    }

   /* public function selects($where, $group = '')
    {
        return M('usertables.user_access_total')
            ->where($where)
            ->group($group)
            ->select();
    }*/

    public function selects($where,$join,$group){
        /* return M('usertables.user_access_total')
             ->where($where)
             ->group($group)
             ->select();*/
        $sql = "SELECT
	SUM(a.access_total) access_total,
	access_time
FROM
	usertables.user_access_total a
$join
WHERE
$where
GROUP BY 
$group";
        $result = M()->query($sql);
        return $result;
    }
    /*
     *用户使用资源情况接口
     */
    public function usedResource()
    {
        //$user_access_total = D('User_access_total');
        $user_access_total = $this;
        $type = getParameter('type', 'str');
        /**************************筛选时间**************************************/
        $schoolId = session('school.school_id');
        $times = getParameter('times', 'str', false);
        $startTime = getParameter('startTime', 'str', false);
        $endTime = getParameter('endTime', 'str', false);

        if (empty($times) || (empty($startTime) && empty($endTime))) {
            $endTime = strtotime(date('Y-m-d'));
            $startTime = $endTime - 604800;
            $group = 'date_format(from_unixtime(access_time),\'%Y%m%d\')';
//$where['access_time'] = array(array('LT', $endTime), array('EGT', $startTime));

            $time = array();
            for ($i = 0; $i < 7; $i++) {
                if ($startTime + (86400 * $i) > $endTime) {
                    $time[] = date('Y-m-d', $endTime - 1);
                } else {
                    $time[] = date('Y-m-d', $startTime + (86400 * $i));
                }
            }
            if ($type == 2) {
                /*$where['module_name'] = 'Home';
                $where['controller_name'] = 'Teach';
                $where['action_name'] = 'homework';
                $where['user_access_total.role'] = 2;
                $where['school_id'] = $schoolId;*/
                // 作业系统、数字教材、备课系统、数字课堂、京版资源
               // $homeWorkData = $this->selects($where,$group);
                $join = "INNER JOIN auth_teacher ON auth_teacher.id = a.userId INNER JOIN dict_schoollist ON dict_schoollist.id = auth_teacher.school_id";
                $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkTeacher' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getExerciseLevelInfo') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkTeacher' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getHomeWorkListByClassId') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Teach' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'homework')";
                $homeWorkData = $user_access_total->selects($where,$join,$group);
                $totalData[0] = $this->contrast($homeWorkData, $time, '作业系统', $endTime);
                /*foreach ($homeWorkData as $itema) {
                    if ($itema['access_time'] == strtotime($startTime)) {

                    }
                }*/
                /*$where['module_name'] = 'Home';
                $where['controller_name'] = 'TextBook';
                $where['action_name'] = 'textbookList';

                $numberTeachData = $this->selects($where,$group);*/
                $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'EtextBook' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getETextBook') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'TextBook' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'textbookList')";
                $numberTeachData = $user_access_total->selects($where,$join,$group);
                $totalData[1] = $this->contrast($numberTeachData, $time, '数字教材', $endTime);
               /* $where['module_name'] = 'Home';
                $where['controller_name'] = 'Teach';
                $where['action_name'] = 'myLessonPlannings';
                $prepareLessonsData = $this->selects($where,$group);*/
                $where = "access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Teach' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'myLessonPlannings'";
                $prepareLessonsData = $user_access_total->selects($where,$join,$group);
                $totalData[2] = $this->contrast($prepareLessonsData, $time, '备课系统', $endTime);
                /*$where['module_name'] = 'Home';
                $where['controller_name'] = 'Teach';
                $where['action_name'] = 'classroomList';
                $numberClassData = $this->selects($where,$group);*/
                $where = "access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Teach' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'classroomList'";
                $numberClassData = $user_access_total->selects($where,$join,$group);
                $totalData[3] = $this->contrast($numberClassData, $time, '数字课堂', $endTime);
                /*$where['module_name'] = 'Home';
                $where['controller_name'] = 'BjResource';
                $where['action_name'] = 'bjResourceIndex';
                $bjResourceData = $this->selects($where,$group);*/
                $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'KnowledgeResource' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getAllResourceList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'BjResource' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'bjResourceIndex')";
                $bjResourceData = $user_access_total->selects($where,$join,$group);
                $totalData[4] = $this->contrast($bjResourceData, $time, '京版资源', $endTime);
                if(empty($homeWorkData) && empty($numberTeachData) && empty($prepareLessonsData)  && empty($numberClassData) && empty($bjResourceData)){
                    $this->ajaxreturn(array('status' => 200, 'data' => ''));
                }else{
                    $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                }

            } elseif ($type == 3) {
                /*****************************获取学生访问次数*********************************/
                // 作业系统 小黑板 我的班级
                /*$where['user_access_total.role'] = 3;
                $where['school_id'] = $schoolId;
                $where['module_name'] = 'Home';
                $where['controller_name'] = 'Student';
                $where['action_name'] = 'homework';
                $homeWorkData = $this->selects($where,$group);*/
                $join = "INNER JOIN auth_student ON auth_student.id = a.userId INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id";
                $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkStudent' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'getUnFinishHomeworkList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Student' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'homework')";
                $homeWorkData = $user_access_total->selects($where,$join,$group);
                $totalData[0] = $this->contrast($homeWorkData, $time, '作业系统', $endTime);
                /*$where['module_name'] = 'Home';
                $where['controller_name'] = 'Student';
                $where['action_name'] = 'blackboard';
                $blackboardData = $this->selects($where,$group);*/
                $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'Blackboard' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'getBlackboardList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Student' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'blackboard')";
                $blackboardData = $user_access_total->selects($where,$join,$group);
                $totalData[1] = $this->contrast($blackboardData, $time, '小黑板', $endTime);
                /*$where['module_name'] = 'Home';
                $where['controller_name'] = 'ClassstuList';
                $where['action_name'] = 'classList';
                $classData = $this->selects($where,$group);*/
                $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'ClassManagement' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'getMyClassList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'ClassstuList' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'classList')";
                $classData = $user_access_total->selects($where,$join,$group);
                $totalData[2] = $this->contrast($classData, $time, '我的班级', $endTime);
                if(empty($homeWorkData) && empty($blackboardData) && empty($classData)){
                    $this->ajaxreturn(array('status' => 200, 'data' => ''));
                }else{
                    $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                }

            } elseif ($type == 4) {
                /*****************************获取家长访问次数*********************************/
                // 小黑板 作业系统 学习轨迹
                /*unset($where['school_id']);
                $where['user_access_total.role'] = 4;
                $where['auth_student.school_id'] = $schoolId;
                $where['module_name'] = 'Home';
                $where['controller_name'] = 'Parent';
                $where['action_name'] = 'blackboard';
                $blackboardData = $this->selects($where,$group);*/
                $group = 'date_format(from_unixtime(access_time),\'%Y%m%d\')';
                //$join = "INNER JOIN auth_parent ON auth_parent.id = a.userId INNER JOIN auth_student_parent_contact ON auth_student_parent_contact.parent_id = auth_parent.id INNER JOIN auth_student ON auth_student_parent_contact.student_id = auth_student.id INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id";
                $join = "INNER JOIN(SELECT DISTINCT auth_parent.id parent_id,dict_schoollist.id school_id
FROM auth_parent 
INNER JOIN auth_student_parent_contact ON auth_student_parent_contact.parent_id = auth_parent.id
INNER JOIN auth_student ON auth_student_parent_contact.student_id = auth_student.id
INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id 
WHERE auth_student.school_id = $schoolId
) tmp ON tmp.parent_id = a.userId";
                $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'Blackboard' AND a.role = 2  AND action_name = 'getBlackboardList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Parent' AND a.role = 2  AND action_name = 'blackboard')";
                $blackboardData = $user_access_total->selects($where,$join,$group);
                $totalData[0] = $this->contrast($blackboardData, $time, '小黑板', $endTime);
                /*$where['module_name'] = 'Home';
                $where['controller_name'] = 'Parent';
                $where['action_name'] = 'homework';
                $homeWorkData = $this->selects($where,$group);*/
                $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkParent' AND a.role = 2  AND action_name = 'getStudentHomeworkList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Parent' AND a.role = 2 AND action_name = 'homework')";
                $homeWorkData = $user_access_total->selects($where,$join,$group);
                $totalData[1] = $this->contrast($homeWorkData, $time, '作业系统', $endTime);
                /*$where['module_name'] = 'Home';
                $where['controller_name'] = 'ParentLearnPath';
                $where['action_name'] = 'classList';
                $classData = $this->selects($where,$group);*/
                $where = "access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'ParentLearnPath' AND a.role = 2  AND action_name = 'classList'";
                $classData = $user_access_total->selects($where,$join,$group);
                $totalData[2] = $this->contrast($classData, $time, '学习轨迹', $endTime);

                if(empty($blackboardData) && empty($homeWorkData) && empty($classData)){
                    $this->ajaxreturn(array('status' => 200, 'data' => ''));
                }else{
                    $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                }

            }
//死数据
           /* $time = array('2018-06-01', '2018-06-02', '2018-06-03', '2018-06-04', '2018-06-05', '2018-06-06', '2018-06-07', '2018-06-08', '2018-06-09', '2018-06-10');
            if ($type == 2) {
                $data[0]['name'] = '作业系统';
                $data[0]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                $data[1]['name'] = '数字教材';
                $data[1]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                $data[2]['name'] = '备课系统';
                $data[2]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                $data[3]['name'] = '数字课堂';
                $data[3]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                $data[4]['name'] = '京版资源';
                $data[4]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $data, 'timeList' => $time)));
            } elseif ($type == 3) {
                $data[0]['name'] = '作业系统';
                $data[0]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                $data[1]['name'] = '小黑板';
                $data[1]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                $data[2]['name'] = '我的班级';
                $data[2]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $data, 'timeList' => $time)));
            } elseif ($type == 4) {
                $data[0]['name'] = '作业系统';
                $data[0]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                $data[1]['name'] = '小黑板';
                $data[1]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                $data[2]['name'] = '我的班级';
                $data[2]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $data, 'timeList' => $time)));
            }*/
            /**************************时间轴**************************************/
        } else {
            if ($times == 'day') {
                $endTime = strtotime($endTime) + 86400;
                $startTime = strtotime($startTime);
//$where['access_time'] = array(array('LT', $endTime), array('EGT', $startTime));
                /**************************时间轴（6小时）**************************************/
                if ($endTime - $startTime <= 172800) {
                    $group = 'floor((access_time-' . $startTime . ')/21600)';
                    $time = array();
                    for ($i = 0; $i < ceil(($endTime - $startTime) / 21600); $i++) {
                        if ($startTime + (21600 * $i) > $endTime) {
                            $time[] = date('Y-m-d H', $endTime - 1);
                        } else {
                            $time[] = date('Y-m-d', $startTime + (21600 * $i));
                        }

                    }
                    if ($type == 2) {
                        /*$where['module_name'] = 'Home';
                        $where['controller_name'] = 'Teach';
                        $where['action_name'] = 'homework';
                        $where['user_access_total.role'] = 2;
                        $where['school_id'] = $schoolId;*/
                        // 作业系统、数字教材、备课系统、数字课堂、京版资源
                        // $homeWorkData = $this->selects($where,$group);
                        $join = "INNER JOIN auth_teacher ON auth_teacher.id = a.userId INNER JOIN dict_schoollist ON dict_schoollist.id = auth_teacher.school_id";
                        $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkTeacher' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getExerciseLevelInfo') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkTeacher' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getHomeWorkListByClassId') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Teach' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'homework')";
                        $homeWorkData = $user_access_total->selects($where,$join,$group);
                        $totalData[0] = $this->contrast($homeWorkData, $time, '作业系统', $endTime);
                        /*foreach ($homeWorkData as $itema) {
                            if ($itema['access_time'] == strtotime($startTime)) {

                            }
                        }*/
                        /*$where['module_name'] = 'Home';
                        $where['controller_name'] = 'TextBook';
                        $where['action_name'] = 'textbookList';

                        $numberTeachData = $this->selects($where,$group);*/
                        $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'EtextBook' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getETextBook') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'TextBook' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'textbookList')";
                        $numberTeachData = $user_access_total->selects($where,$join,$group);
                        $totalData[1] = $this->contrast($numberTeachData, $time, '数字教材', $endTime);
                        /* $where['module_name'] = 'Home';
                         $where['controller_name'] = 'Teach';
                         $where['action_name'] = 'myLessonPlannings';
                         $prepareLessonsData = $this->selects($where,$group);*/
                        $where = "access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Teach' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'myLessonPlannings'";
                        $prepareLessonsData = $user_access_total->selects($where,$join,$group);
                        $totalData[2] = $this->contrast($prepareLessonsData, $time, '备课系统', $endTime);
                        /*$where['module_name'] = 'Home';
                        $where['controller_name'] = 'Teach';
                        $where['action_name'] = 'classroomList';
                        $numberClassData = $this->selects($where,$group);*/
                        $where = "access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Teach' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'classroomList'";
                        $numberClassData = $user_access_total->selects($where,$join,$group);
                        $totalData[3] = $this->contrast($numberClassData, $time, '数字课堂', $endTime);
                        /*$where['module_name'] = 'Home';
                        $where['controller_name'] = 'BjResource';
                        $where['action_name'] = 'bjResourceIndex';
                        $bjResourceData = $this->selects($where,$group);*/
                        $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'KnowledgeResource' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getAllResourceList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'BjResource' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'bjResourceIndex')";
                        $bjResourceData = $user_access_total->selects($where,$join,$group);
                        $totalData[4] = $this->contrast($bjResourceData, $time, '京版资源', $endTime);
                        if(empty($homeWorkData) && empty($numberTeachData) && empty($prepareLessonsData)  && empty($numberClassData) && empty($bjResourceData)){
                            $this->ajaxreturn(array('status' => 200, 'data' => ''));
                        }else{
                            $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                        }
                    } elseif ($type == 3) {
                        /*****************************获取学生访问次数*********************************/
                        // 作业系统 小黑板 我的班级
                        /*$where['user_access_total.role'] = 3;
                        $where['school_id'] = $schoolId;
                        $where['module_name'] = 'Home';
                        $where['controller_name'] = 'Student';
                        $where['action_name'] = 'homework';
                        $homeWorkData = $this->selects($where,$group);*/
                        $join = "INNER JOIN auth_student ON auth_student.id = a.userId INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id";
                        $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkStudent' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'getUnFinishHomeworkList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Student' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'homework')";
                        $homeWorkData = $user_access_total->selects($where,$join,$group);
                        $totalData[0] = $this->contrast($homeWorkData, $time, '作业系统', $endTime);
                        /*$where['module_name'] = 'Home';
                        $where['controller_name'] = 'Student';
                        $where['action_name'] = 'blackboard';
                        $blackboardData = $this->selects($where,$group);*/
                        $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'Blackboard' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'getBlackboardList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Student' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'blackboard')";
                        $blackboardData = $user_access_total->selects($where,$join,$group);
                        $totalData[1] = $this->contrast($blackboardData, $time, '小黑板', $endTime);
                        /*$where['module_name'] = 'Home';
                        $where['controller_name'] = 'ClassstuList';
                        $where['action_name'] = 'classList';
                        $classData = $this->selects($where,$group);*/
                        $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'ClassManagement' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'getMyClassList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'ClassstuList' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'classList')";
                        $classData = $user_access_total->selects($where,$join,$group);
                        $totalData[2] = $this->contrast($classData, $time, '我的班级', $endTime);
                        if(empty($homeWorkData) && empty($blackboardData) && empty($classData)){
                            $this->ajaxreturn(array('status' => 200, 'data' => ''));
                        }else{
                            $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                        }
                    } elseif ($type == 4) {
                        /*****************************获取家长访问次数*********************************/
                        // 小黑板 作业系统 学习轨迹
                        /*unset($where['school_id']);
                        $where['user_access_total.role'] = 4;
                        $where['auth_student.school_id'] = $schoolId;
                        $where['module_name'] = 'Home';
                        $where['controller_name'] = 'Parent';
                        $where['action_name'] = 'blackboard';
                        $blackboardData = $this->selects($where,$group);*/
                        $group = 'floor((access_time-' . $startTime . ')/21600)';
                        //$join = "INNER JOIN auth_parent ON auth_parent.id = a.userId INNER JOIN auth_student_parent_contact ON auth_student_parent_contact.parent_id = auth_parent.id INNER JOIN auth_student ON auth_student_parent_contact.student_id = auth_student.id INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id";
                        $join = "INNER JOIN(SELECT DISTINCT auth_parent.id parent_id,dict_schoollist.id school_id
FROM auth_parent 
INNER JOIN auth_student_parent_contact ON auth_student_parent_contact.parent_id = auth_parent.id
INNER JOIN auth_student ON auth_student_parent_contact.student_id = auth_student.id
INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id 
WHERE auth_student.school_id = $schoolId
) tmp ON tmp.parent_id = a.userId";
                        $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'Blackboard' AND a.role = 2  AND action_name = 'getBlackboardList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Parent' AND a.role = 2  AND action_name = 'blackboard')";
                        $blackboardData = $user_access_total->selects($where,$join,$group);
                        $totalData[0] = $this->contrast($blackboardData, $time, '小黑板', $endTime);
                        /*$where['module_name'] = 'Home';
                        $where['controller_name'] = 'Parent';
                        $where['action_name'] = 'homework';
                        $homeWorkData = $this->selects($where,$group);*/
                        $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkParent' AND a.role = 2 A AND action_name = 'getStudentHomeworkList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Parent' AND a.role = 2 AND action_name = 'homework')";
                        $homeWorkData = $user_access_total->selects($where,$join,$group);
                        $totalData[1] = $this->contrast($homeWorkData, $time, '作业系统', $endTime);
                        /*$where['module_name'] = 'Home';
                        $where['controller_name'] = 'ParentLearnPath';
                        $where['action_name'] = 'classList';
                        $classData = $this->selects($where,$group);*/
                        $where = "access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'ParentLearnPath' AND a.role = 2  AND action_name = 'classList'";
                        $classData = $user_access_total->selects($where,$join,$group);
                        $totalData[2] = $this->contrast($classData, $time, '学习轨迹', $endTime);
                        if(empty($blackboardData) && empty($homeWorkData) && empty($classData)){
                            $this->ajaxreturn(array('status' => 200, 'data' => ''));
                        }else{
                            $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                        }
                    }

                   /* $time = array('2018-06-01', '2018-06-02', '2018-06-03', '2018-06-04', '2018-06-05', '2018-06-06', '2018-06-07', '2018-06-08', '2018-06-09', '2018-06-10');
                    if ($type == 2) {
                        $data[0]['name'] = '作业系统';
                        $data[0]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $data[1]['name'] = '数字教材';
                        $data[1]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $data[2]['name'] = '备课系统';
                        $data[2]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $data[3]['name'] = '数字课堂';
                        $data[3]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $data[4]['name'] = '京版资源';
                        $data[4]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $data, 'timeList' => $time)));
                    } elseif ($type == 3) {
                        $data[0]['name'] = '作业系统';
                        $data[0]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $data[1]['name'] = '小黑板';
                        $data[1]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $data[2]['name'] = '我的班级';
                        $data[2]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $data, 'timeList' => $time)));
                    } elseif ($type == 4) {
                        $data[0]['name'] = '作业系统';
                        $data[0]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $data[1]['name'] = '小黑板';
                        $data[1]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $data[2]['name'] = '我的班级';
                        $data[2]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $data, 'timeList' => $time)));
                    }*/
                    /**************************时间轴（按天）**************************************/
                } elseif ($endTime - $startTime > 172800 && $endTime - $startTime <= 2592000) {
                    $group = "date_format(from_unixtime(access_time),'%Y%m%d')";
                    $time = array();
                    for ($i = 0; $i < ceil(($endTime - $startTime) / 86400); $i++) {
                        if ($startTime + (86400 * $i) > $endTime) {
                            $time[] = date('Y-m-d', $endTime - 1);
                        } else {
                            $time[] = date('Y-m-d', $startTime + (86400 * $i));
                        }

                    }
                    if ($type == 2) {
                        /*$where['module_name'] = 'Home';
                        $where['controller_name'] = 'Teach';
                        $where['action_name'] = 'homework';
                        $where['user_access_total.role'] = 2;
                        $where['school_id'] = $schoolId;*/
                        // 作业系统、数字教材、备课系统、数字课堂、京版资源
                        // $homeWorkData = $this->selects($where,$group);
                        $join = "INNER JOIN auth_teacher ON auth_teacher.id = a.userId INNER JOIN dict_schoollist ON dict_schoollist.id = auth_teacher.school_id";
                        $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkTeacher' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getExerciseLevelInfo') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkTeacher' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getHomeWorkListByClassId') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Teach' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'homework')";
                        $homeWorkData = $user_access_total->selects($where,$join,$group);
                        $totalData[0] = $this->contrast($homeWorkData, $time, '作业系统', $endTime);
                        /*foreach ($homeWorkData as $itema) {
                            if ($itema['access_time'] == strtotime($startTime)) {

                            }
                        }*/
                        /*$where['module_name'] = 'Home';
                        $where['controller_name'] = 'TextBook';
                        $where['action_name'] = 'textbookList';

                        $numberTeachData = $this->selects($where,$group);*/
                        $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'EtextBook' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getETextBook') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'TextBook' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'textbookList')";
                        $numberTeachData = $user_access_total->selects($where,$join,$group);
                        $totalData[1] = $this->contrast($numberTeachData, $time, '数字教材', $endTime);
                        /* $where['module_name'] = 'Home';
                         $where['controller_name'] = 'Teach';
                         $where['action_name'] = 'myLessonPlannings';
                         $prepareLessonsData = $this->selects($where,$group);*/
                        $where = "access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Teach' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'myLessonPlannings'";
                        $prepareLessonsData = $user_access_total->selects($where,$join,$group);
                        $totalData[2] = $this->contrast($prepareLessonsData, $time, '备课系统', $endTime);
//echo M()->getLastSql();die;
                        /*$where['module_name'] = 'Home';
                        $where['controller_name'] = 'Teach';
                        $where['action_name'] = 'classroomList';
                        $numberClassData = $this->selects($where,$group);*/
                        $where = "access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Teach' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'classroomList'";
                        $numberClassData = $user_access_total->selects($where,$join,$group);
                        $totalData[3] = $this->contrast($numberClassData, $time, '数字课堂', $endTime);
                        /*$where['module_name'] = 'Home';
                        $where['controller_name'] = 'BjResource';
                        $where['action_name'] = 'bjResourceIndex';
                        $bjResourceData = $this->selects($where,$group);*/
                        $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'KnowledgeResource' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getAllResourceList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'BjResource' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'bjResourceIndex')";
                        $bjResourceData = $user_access_total->selects($where,$join,$group);
//echo M()->getLastSql();die;
                        $totalData[4] = $this->contrast($bjResourceData, $time, '京版资源', $endTime);
                        if(empty($homeWorkData) && empty($numberTeachData) && empty($prepareLessonsData)  && empty($numberClassData) && empty($bjResourceData)){
                            $this->ajaxreturn(array('status' => 200, 'data' => ''));
                        }else{
                            $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                        }
                    } elseif ($type == 3) {
                        /*****************************获取学生访问次数*********************************/
                        // 作业系统 小黑板 我的班级
                        /*$where['user_access_total.role'] = 3;
                        $where['school_id'] = $schoolId;
                        $where['module_name'] = 'Home';
                        $where['controller_name'] = 'Student';
                        $where['action_name'] = 'homework';
                        $homeWorkData = $this->selects($where,$group);*/
                        $join = "INNER JOIN auth_student ON auth_student.id = a.userId INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id";
                        $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkStudent' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'getUnFinishHomeworkList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Student' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'homework')";
                        $homeWorkData = $user_access_total->selects($where,$join,$group);
                        $totalData[0] = $this->contrast($homeWorkData, $time, '作业系统', $endTime);
                        /*$where['module_name'] = 'Home';
                        $where['controller_name'] = 'Student';
                        $where['action_name'] = 'blackboard';
                        $blackboardData = $this->selects($where,$group);*/
                        $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'Blackboard' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'getBlackboardList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Student' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'blackboard')";
                        $blackboardData = $user_access_total->selects($where,$join,$group);
                        $totalData[1] = $this->contrast($blackboardData, $time, '小黑板', $endTime);
                        /*$where['module_name'] = 'Home';
                        $where['controller_name'] = 'ClassstuList';
                        $where['action_name'] = 'classList';
                        $classData = $this->selects($where,$group);*/
                        $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'ClassManagement' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'getMyClassList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'ClassstuList' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'classList')";
                        $classData = $user_access_total->selects($where,$join,$group);
//echo M()->getLastSql();die;
                        $totalData[2] = $this->contrast($classData, $time, '我的班级', $endTime);
                        if(empty($homeWorkData) && empty($blackboardData) && empty($classData)){
                            $this->ajaxreturn(array('status' => 200, 'data' => ''));
                        }else{
                            $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                        }
                    } elseif ($type == 4) {
                        /*****************************获取家长访问次数*********************************/
                        // 小黑板 作业系统 学习轨迹
                        /*unset($where['school_id']);
                        $where['user_access_total.role'] = 4;
                        $where['auth_student.school_id'] = $schoolId;
                        $where['module_name'] = 'Home';
                        $where['controller_name'] = 'Parent';
                        $where['action_name'] = 'blackboard';
                        $blackboardData = $this->selects($where,$group);*/
                        $group = 'date_format(from_unixtime(access_time),\'%Y%m%d\')';
                        //$join = "INNER JOIN auth_parent ON auth_parent.id = a.userId INNER JOIN auth_student_parent_contact ON auth_student_parent_contact.parent_id = auth_parent.id INNER JOIN auth_student ON auth_student_parent_contact.student_id = auth_student.id INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id";
                        $join = "INNER JOIN(SELECT DISTINCT auth_parent.id parent_id,dict_schoollist.id school_id
FROM auth_parent 
INNER JOIN auth_student_parent_contact ON auth_student_parent_contact.parent_id = auth_parent.id
INNER JOIN auth_student ON auth_student_parent_contact.student_id = auth_student.id
INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id 
WHERE auth_student.school_id = $schoolId
) tmp ON tmp.parent_id = a.userId";
                        $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'Blackboard' AND a.role = 2  AND action_name = 'getBlackboardList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Parent' AND a.role = 2  AND action_name = 'blackboard')";
                        $blackboardData = $user_access_total->selects($where,$join,$group);
                        $totalData[0] = $this->contrast($blackboardData, $time, '小黑板', $endTime);
                        /*$where['module_name'] = 'Home';
                        $where['controller_name'] = 'Parent';
                        $where['action_name'] = 'homework';
                        $homeWorkData = $this->selects($where,$group);*/
                        $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkParent' AND a.role = 2  AND action_name = 'getStudentHomeworkList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Parent' AND a.role = 2  AND action_name = 'homework')";
                        $homeWorkData = $user_access_total->selects($where,$join,$group);
//echo  M()->getLastSql();die;
                        $totalData[1] = $this->contrast($homeWorkData, $time, '作业系统', $endTime);
                        /*$where['module_name'] = 'Home';
                        $where['controller_name'] = 'ParentLearnPath';
                        $where['action_name'] = 'classList';
                        $classData = $this->selects($where,$group);*/
                        $where = "access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'ParentLearnPath' AND a.role = 2  AND action_name = 'classList'";
                        $classData = $user_access_total->selects($where,$join,$group);
                        $totalData[2] = $this->contrast($classData, $time, '学习轨迹', $endTime);
                        if(empty($blackboardData) && empty($homeWorkData) && empty($classData)){
                            $this->ajaxreturn(array('status' => 200, 'data' => ''));
                        }else{
                            $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                        }
                    }
                    /*$time = array('2018-06-01', '2018-06-02', '2018-06-03', '2018-06-04', '2018-06-05', '2018-06-06', '2018-06-07', '2018-06-08', '2018-06-09', '2018-06-10');
                    if ($type == 2) {
                        $data[0]['name'] = '作业系统';
                        $data[0]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $data[1]['name'] = '数字教材';
                        $data[1]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $data[2]['name'] = '备课系统';
                        $data[2]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $data[3]['name'] = '数字课堂';
                        $data[3]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $data[4]['name'] = '京版资源';
                        $data[4]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $data, 'timeList' => $time)));
                    } elseif ($type == 3) {
                        $data[0]['name'] = '作业系统';
                        $data[0]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $data[1]['name'] = '小黑板';
                        $data[1]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $data[2]['name'] = '我的班级';
                        $data[2]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $data, 'timeList' => $time)));
                    } elseif ($type == 4) {
                        $data[0]['name'] = '作业系统';
                        $data[0]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $data[1]['name'] = '小黑板';
                        $data[1]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $data[2]['name'] = '我的班级';
                        $data[2]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
                        $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $data, 'timeList' => $time)));
                    }*/
                }
                } elseif ($times == 'month') {
                    $group = 'floor((access_time-' . strtotime($startTime.'-01') . ')/604800)';
//$where['access_time'] = array(array('ELT', $this->format($startTime, 'm')), array('EGT', strtotime($startTime.'-01')));
                    /**************************时间轴（按周）**************************************/
                    $time = array();
                    for ($i = 0; $i < ceil(($this->format($startTime, 'm') - strtotime($startTime.'-01')) / 604800); $i++) {
                        if (strtotime($startTime.'-01') + (604800 * $i) > $this->format($startTime, 'm')) {
                            $time[] = date('Y-m-d', $this->format($startTime, 'm') - 1);
                        } else {
                            $time[] = date('Y-m-d', strtotime($startTime.'-01') + (604800 * $i));
                        }

                    }
                    $endTime = $this->format($startTime, 'm');
                    $startTime = strtotime($startTime.'-01');
                    /*$dayNum = date('t', strtotime($startTime));
                    $weekNum = ceil($dayNum / 7);
                    for ($i = 1; $i <= $weekNum; $i++) {
                        array_push($data['weekNum'], $i);
                    }*/
                    /*****************************获取教师访问次数*********************************/
                if ($type == 2) {
                    /*$where['module_name'] = 'Home';
                    $where['controller_name'] = 'Teach';
                    $where['action_name'] = 'homework';
                    $where['user_access_total.role'] = 2;
                    $where['school_id'] = $schoolId;*/
                    // 作业系统、数字教材、备课系统、数字课堂、京版资源
                    // $homeWorkData = $this->selects($where,$group);
                    $join = "INNER JOIN auth_teacher ON auth_teacher.id = a.userId INNER JOIN dict_schoollist ON dict_schoollist.id = auth_teacher.school_id";
                    $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkTeacher' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getExerciseLevelInfo') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkTeacher' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getHomeWorkListByClassId') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Teach' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'homework')";
                    $homeWorkData = $user_access_total->selects($where,$join,$group);
                    $totalData[0] = $this->contrast($homeWorkData, $time, '作业系统', $endTime);
                    /*foreach ($homeWorkData as $itema) {
                        if ($itema['access_time'] == strtotime($startTime)) {

                        }
                    }*/
                    /*$where['module_name'] = 'Home';
                    $where['controller_name'] = 'TextBook';
                    $where['action_name'] = 'textbookList';

                    $numberTeachData = $this->selects($where,$group);*/
                    $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'EtextBook' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getETextBook') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'TextBook' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'textbookList')";
                    $numberTeachData = $user_access_total->selects($where,$join,$group);
                    $totalData[1] = $this->contrast($numberTeachData, $time, '数字教材', $endTime);
                    /* $where['module_name'] = 'Home';
                     $where['controller_name'] = 'Teach';
                     $where['action_name'] = 'myLessonPlannings';
                     $prepareLessonsData = $this->selects($where,$group);*/
                    $where = "access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Teach' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'myLessonPlannings'";
                    $prepareLessonsData = $user_access_total->selects($where,$join,$group);
                    $totalData[2] = $this->contrast($prepareLessonsData, $time, '备课系统', $endTime);
                    /*$where['module_name'] = 'Home';
                    $where['controller_name'] = 'Teach';
                    $where['action_name'] = 'classroomList';
                    $numberClassData = $this->selects($where,$group);*/
                    $where = "access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Teach' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'classroomList'";
                    $numberClassData = $user_access_total->selects($where,$join,$group);
                    $totalData[3] = $this->contrast($numberClassData, $time, '数字课堂', $endTime);
                    /*$where['module_name'] = 'Home';
                    $where['controller_name'] = 'BjResource';
                    $where['action_name'] = 'bjResourceIndex';
                    $bjResourceData = $this->selects($where,$group);*/
                    $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'KnowledgeResource' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getAllResourceList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'BjResource' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'bjResourceIndex')";
                    $bjResourceData = $user_access_total->selects($where,$join,$group);
                    $totalData[4] = $this->contrast($bjResourceData, $time, '京版资源', $endTime);
                    if(empty($homeWorkData) && empty($numberTeachData) && empty($prepareLessonsData)  && empty($numberClassData) && empty($bjResourceData)){
                        $this->ajaxreturn(array('status' => 200, 'data' => ''));
                    }else{
                        $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                    }
                } elseif ($type == 3) {
                    /*****************************获取学生访问次数*********************************/
                    // 作业系统 小黑板 我的班级
                    /*$where['user_access_total.role'] = 3;
                    $where['school_id'] = $schoolId;
                    $where['module_name'] = 'Home';
                    $where['controller_name'] = 'Student';
                    $where['action_name'] = 'homework';
                    $homeWorkData = $this->selects($where,$group);*/
                    $join = "INNER JOIN auth_student ON auth_student.id = a.userId INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id";
                    $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkStudent' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'getUnFinishHomeworkList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Student' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'homework')";
                    $homeWorkData = $user_access_total->selects($where,$join,$group);
                    $totalData[0] = $this->contrast($homeWorkData, $time, '作业系统', $endTime);
                    /*$where['module_name'] = 'Home';
                    $where['controller_name'] = 'Student';
                    $where['action_name'] = 'blackboard';
                    $blackboardData = $this->selects($where,$group);*/
                    $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'Blackboard' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'getBlackboardList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Student' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'blackboard')";
                    $blackboardData = $user_access_total->selects($where,$join,$group);
                    $totalData[1] = $this->contrast($blackboardData, $time, '小黑板', $endTime);
                    /*$where['module_name'] = 'Home';
                    $where['controller_name'] = 'ClassstuList';
                    $where['action_name'] = 'classList';
                    $classData = $this->selects($where,$group);*/
                    $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'ClassManagement' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'getMyClassList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'ClassstuList' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'classList')";
                    $classData = $user_access_total->selects($where,$join,$group);
                    $totalData[2] = $this->contrast($classData, $time, '我的班级', $endTime);
                    if(empty($homeWorkData) && empty($blackboardData) && empty($classData)){
                        $this->ajaxreturn(array('status' => 200, 'data' => ''));
                    }else{
                        $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                    }
                } elseif ($type == 4) {
                    /*****************************获取家长访问次数*********************************/
                    // 小黑板 作业系统 学习轨迹
                    /*unset($where['school_id']);
                    $where['user_access_total.role'] = 4;
                    $where['auth_student.school_id'] = $schoolId;
                    $where['module_name'] = 'Home';
                    $where['controller_name'] = 'Parent';
                    $where['action_name'] = 'blackboard';
                    $blackboardData = $this->selects($where,$group);*/
                    //$group = 'floor((access_time-' . strtotime($startTime.'-01') . ')/604800),auth_parent.id';
                    //$join = "INNER JOIN auth_parent ON auth_parent.id = a.userId INNER JOIN auth_student_parent_contact ON auth_student_parent_contact.parent_id = auth_parent.id INNER JOIN auth_student ON auth_student_parent_contact.student_id = auth_student.id INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id";
                    $join = "INNER JOIN(SELECT DISTINCT auth_parent.id parent_id,dict_schoollist.id school_id
FROM auth_parent 
INNER JOIN auth_student_parent_contact ON auth_student_parent_contact.parent_id = auth_parent.id
INNER JOIN auth_student ON auth_student_parent_contact.student_id = auth_student.id
INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id 
WHERE auth_student.school_id = $schoolId
) tmp ON tmp.parent_id = a.userId";
                    $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'Blackboard' AND a.role = 2  AND action_name = 'getBlackboardList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Parent' AND a.role = 2  AND action_name = 'blackboard')";
                    $blackboardData = $user_access_total->selects($where,$join,$group);
                    $totalData[0] = $this->contrast($blackboardData, $time, '小黑板', $endTime);
                    /*$where['module_name'] = 'Home';
                    $where['controller_name'] = 'Parent';
                    $where['action_name'] = 'homework';
                    $homeWorkData = $this->selects($where,$group);*/
                    $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkParent' AND a.role = 2  AND action_name = 'getStudentHomeworkList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Parent' AND a.role = 2  AND action_name = 'homework')";
                    $homeWorkData = $user_access_total->selects($where,$join,$group);
                    $totalData[1] = $this->contrast($homeWorkData, $time, '作业系统', $endTime);
                    /*$where['module_name'] = 'Home';
                    $where['controller_name'] = 'ParentLearnPath';
                    $where['action_name'] = 'classList';
                    $classData = $this->selects($where,$group);*/
                    $where = "access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'ParentLearnPath' AND a.role = 2  AND action_name = 'classList'";
                    $classData = $user_access_total->selects($where,$join,$group);
                    $totalData[2] = $this->contrast($classData, $time, '学习轨迹', $endTime);
                    if(empty($blackboardData) && empty($homeWorkData) && empty($classData)){
                        $this->ajaxreturn(array('status' => 200, 'data' => ''));
                    }else{
                        $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                    }
                }
                } elseif ($times == 'year') {
                    $group = 'date_format(from_unixtime(access_time),\'%Y%m\')';
//$where['access_time'] = array(array('LT', $this->format($startTime, 'Y')), array('EGT', strtotime($startTime . '-01-01')));
                $endTime = $this->format($startTime, 'Y');
                    /**************************时间轴（按月）**************************************/
                    $time = array($startTime . '-01', $startTime . '-02', $startTime . '-03', $startTime . '-04', $startTime . '-05', $startTime . '-06', $startTime . '-07', $startTime . '-08', $startTime . '-09', $startTime . '-10', $startTime . '-11', $startTime . '-12',);
                $startTime = strtotime($startTime . '-01-01');
                if ($type == 2) {
                    /*$where['module_name'] = 'Home';
                    $where['controller_name'] = 'Teach';
                    $where['action_name'] = 'homework';
                    $where['user_access_total.role'] = 2;
                    $where['school_id'] = $schoolId;*/
                    // 作业系统、数字教材、备课系统、数字课堂、京版资源
                    // $homeWorkData = $this->selects($where,$group);
                    $join = "INNER JOIN auth_teacher ON auth_teacher.id = a.userId INNER JOIN dict_schoollist ON dict_schoollist.id = auth_teacher.school_id";
                    $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkTeacher' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getExerciseLevelInfo') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkTeacher' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getHomeWorkListByClassId') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Teach' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'homework')";
                    $homeWorkData = $user_access_total->selects($where,$join,$group);
                    $totalData[0] = $this->contrast($homeWorkData, $time, '作业系统', $endTime);
                    /*foreach ($homeWorkData as $itema) {
                        if ($itema['access_time'] == strtotime($startTime)) {

                        }
                    }*/
                    /*$where['module_name'] = 'Home';
                    $where['controller_name'] = 'TextBook';
                    $where['action_name'] = 'textbookList';

                    $numberTeachData = $this->selects($where,$group);*/
                    $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'EtextBook' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getETextBook') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'TextBook' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'textbookList')";
                    $numberTeachData = $user_access_total->selects($where,$join,$group);
                    $totalData[1] = $this->contrast($numberTeachData, $time, '数字教材', $endTime);
                    /* $where['module_name'] = 'Home';
                     $where['controller_name'] = 'Teach';
                     $where['action_name'] = 'myLessonPlannings';
                     $prepareLessonsData = $this->selects($where,$group);*/
                    $where = "access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Teach' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'myLessonPlannings'";
                    $prepareLessonsData = $user_access_total->selects($where,$join,$group);
                    $totalData[2] = $this->contrast($prepareLessonsData, $time, '备课系统', $endTime);
                    /*$where['module_name'] = 'Home';
                    $where['controller_name'] = 'Teach';
                    $where['action_name'] = 'classroomList';
                    $numberClassData = $this->selects($where,$group);*/
                    $where = "access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Teach' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'classroomList'";
                    $numberClassData = $user_access_total->selects($where,$join,$group);
                    $totalData[3] = $this->contrast($numberClassData, $time, '数字课堂', $endTime);
                    /*$where['module_name'] = 'Home';
                    $where['controller_name'] = 'BjResource';
                    $where['action_name'] = 'bjResourceIndex';
                    $bjResourceData = $this->selects($where,$group);*/
                    $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'KnowledgeResource' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'getAllResourceList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'BjResource' AND a.role = 0 AND auth_teacher.school_id = $schoolId AND action_name = 'bjResourceIndex')";
                    $bjResourceData = $user_access_total->selects($where,$join,$group);
                    $totalData[4] = $this->contrast($bjResourceData, $time, '京版资源', $endTime);
                    if(empty($homeWorkData) && empty($numberTeachData) && empty($prepareLessonsData)  && empty($numberClassData) && empty($bjResourceData)){
                        $this->ajaxreturn(array('status' => 200, 'data' => ''));
                    }else{
                        $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                    }
                } elseif ($type == 3) {
                    /*****************************获取学生访问次数*********************************/
                    // 作业系统 小黑板 我的班级
                    /*$where['user_access_total.role'] = 3;
                    $where['school_id'] = $schoolId;
                    $where['module_name'] = 'Home';
                    $where['controller_name'] = 'Student';
                    $where['action_name'] = 'homework';
                    $homeWorkData = $this->selects($where,$group);*/
                    $join = "INNER JOIN auth_student ON auth_student.id = a.userId INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id";
                    $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkStudent' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'getUnFinishHomeworkList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Student' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'homework')";
                    $homeWorkData = $user_access_total->selects($where,$join,$group);
                    $totalData[0] = $this->contrast($homeWorkData, $time, '作业系统', $endTime);
                    /*$where['module_name'] = 'Home';
                    $where['controller_name'] = 'Student';
                    $where['action_name'] = 'blackboard';
                    $blackboardData = $this->selects($where,$group);*/
                    $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'Blackboard' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'getBlackboardList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Student' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'blackboard')";
                    $blackboardData = $user_access_total->selects($where,$join,$group);
                    $totalData[1] = $this->contrast($blackboardData, $time, '小黑板', $endTime);
                    /*$where['module_name'] = 'Home';
                    $where['controller_name'] = 'ClassstuList';
                    $where['action_name'] = 'classList';
                    $classData = $this->selects($where,$group);*/
                    $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'ClassManagement' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'getMyClassList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'ClassstuList' AND a.role = 1 AND auth_student.school_id = $schoolId AND action_name = 'classList')";
                    $classData = $user_access_total->selects($where,$join,$group);
                    $totalData[2] = $this->contrast($classData, $time, '我的班级', $endTime);
                    if(empty($homeWorkData) && empty($blackboardData) && empty($classData)){
                        $this->ajaxreturn(array('status' => 200, 'data' => ''));
                    }else{
                        $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                    }
                } elseif ($type == 4) {
                    /*****************************获取家长访问次数*********************************/
                    // 小黑板 作业系统 学习轨迹
                    /*unset($where['school_id']);
                    $where['user_access_total.role'] = 4;
                    $where['auth_student.school_id'] = $schoolId;
                    $where['module_name'] = 'Home';
                    $where['controller_name'] = 'Parent';
                    $where['action_name'] = 'blackboard';
                    $blackboardData = $this->selects($where,$group);*/
                    $group = "date_format(from_unixtime(access_time),'%Y%m')";
                    //$join = "INNER JOIN auth_parent ON auth_parent.id = a.userId INNER JOIN auth_student_parent_contact ON auth_student_parent_contact.parent_id = auth_parent.id INNER JOIN auth_student ON auth_student_parent_contact.student_id = auth_student.id INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id";
                    $join = "INNER JOIN(SELECT DISTINCT auth_parent.id parent_id,dict_schoollist.id school_id
FROM auth_parent 
INNER JOIN auth_student_parent_contact ON auth_student_parent_contact.parent_id = auth_parent.id
INNER JOIN auth_student ON auth_student_parent_contact.student_id = auth_student.id
INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id 
WHERE auth_student.school_id = $schoolId
) tmp ON tmp.parent_id = a.userId";
                    $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'Blackboard' AND a.role = 2  AND action_name = 'getBlackboardList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Parent' AND a.role = 2  AND action_name = 'blackboard')";
                    $blackboardData = $user_access_total->selects($where,$join,$group);
//echo M()->getLastSql();die;
                    $totalData[0] = $this->contrast($blackboardData, $time, '小黑板', $endTime);
                    /*$where['module_name'] = 'Home';
                    $where['controller_name'] = 'Parent';
                    $where['action_name'] = 'homework';
                    $homeWorkData = $this->selects($where,$group);*/
                    $where = "(access_time < $endTime AND access_time >= $startTime AND module_name = 'ApiInterface' AND controller_name = 'HomeworkParent' AND a.role = 2  AND action_name = 'getStudentHomeworkList') OR (access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'Parent' AND a.role = 2  AND action_name = 'homework')";
                    $homeWorkData = $user_access_total->selects($where,$join,$group);
                    $totalData[1] = $this->contrast($homeWorkData, $time, '作业系统', $endTime);
                    /*$where['module_name'] = 'Home';
                    $where['controller_name'] = 'ParentLearnPath';
                    $where['action_name'] = 'classList';
                    $classData = $this->selects($where,$group);*/
                    $where = "access_time < $endTime AND access_time >= $startTime AND module_name = 'Home' AND controller_name = 'ParentLearnPath' AND a.role = 2  AND action_name = 'classList'";
                    $classData = $user_access_total->selects($where,$join,$group);
                    $totalData[2] = $this->contrast($classData, $time, '学习轨迹', $endTime);
                    if(empty($blackboardData) && empty($homeWorkData) && empty($classData)){
                        $this->ajaxreturn(array('status' => 200, 'data' => ''));
                    }else{
                        $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                    }
                }
            }
        }
    }

    public function contrast($arr1, $arr2, $name, $lastTime)
    {
        for ($i = 0; $i < count($arr2); $i++) {
            $data['data'][$i] = 0;
        }
        for ($i = 0; $i < count($arr1); $i++) {
            for ($r = 0; $r < count($arr2); $r++) {
                //TODO:公共调用有错误
                if ($r + 1 == count($arr2)) {
                    if ($arr1[$i]['access_time'] >= strtotime($arr2[$r]) && $arr1[$i]['access_time'] < $lastTime) {
                        $data['data'][$r] = $arr1[$i]['access_total'] + 0;

                    }
                } else {
                    if ($arr1[$i]['access_time'] >= strtotime($arr2[$r]) && $arr1[$i]['access_time'] < strtotime($arr2[$r + 1])) {
                        $data['data'][$r] = $arr1[$i]['access_total'] + 0;
                    }

                }
            }
        }
        if (!empty($name)) {
            $data['name'] = $name;
        }
//var_dump($arr1,$arr2,$lastTime,$data);die;
        return $data;
    }

    /*
     *教师教学统计接口
     *@parms $type:1、备课统计2、上课统计
     */
    public function teachStatistics()
    {
        /**************************筛选时间**************************************/
        $schoolId = session('school.school_id');
        $times = getParameter('times', 'str', false);
        $startTime = getParameter('startTime', 'str', false);
        $endTime = getParameter('endTime', 'str', false);
        $type = getParameter('type', 'str');

        $where['school_id'] = $schoolId;
        $where['apply_school_status'] = TEACHER_PASS_APPLY_SCHOOL_STATUS;
        /*******************************教师备课统计*************************************/
        if ($type == '1') {
            if (empty($times) || (empty($startTime) && empty($endTime))) {
                $where['biz_lesson_planning.create_at'] = array(array('LT', strtotime(date('Y-m-d'))), array('EGT', strtotime(date('Y-m-d'))-604800));
            } else {
                if ($times == 'day') {
                    $where['biz_lesson_planning.create_at'] = array(array('LT', strtotime($endTime)+86400), array('EGT', strtotime($startTime)));
                } elseif ($times == 'month') {
                    $where['biz_lesson_planning.create_at'] = array(array('LT', $this->format($startTime, 'm')), array('EGT', strtotime($startTime.'-01')));
                } elseif ($times == 'year') {
                    $where['biz_lesson_planning.create_at'] = array(array('LT', $this->format($startTime, 'Y')), array('EGT', strtotime($startTime.'-01-01')));
                }
            }
            $Model = M('biz_lesson_planning');
            $total = $Model
                ->field('dict_course.course_name name,count(1) total')
                ->join('dict_course on dict_course.id=biz_lesson_planning.course_id')
                ->join('auth_teacher on auth_teacher.id=biz_lesson_planning.teacher_id')
                ->where($where)
                ->group('biz_lesson_planning.course_id')
                ->select();

            foreach ($total as $k => $item) {
                $data[$k]['name'] = $item['name'];
                $data[$k]['y'] = $item['total']+0;
            }
            /*$data[0]['name'] = '语文';
            $data[0]['y'] = 20;
            $data[1]['name'] = '数学';
            $data[1]['y'] = 20;
            $data[2]['name'] = '英语';
            $data[2]['y'] = 30;
            $data[3]['name'] = '物理';
            $data[3]['y'] = 40;
            $data[4]['name'] = '生物';
            $data[4]['y'] = 50;
            $data[5]['name'] = '化学';
            $data[5]['y'] = 60;
            $data[6]['name'] = '地理';
            $data[6]['y'] = 70;*/
            $this->ajaxreturn(array('status' => 200, 'data' => $data));
        } elseif ($type == '2') {
            if (empty($times) || (empty($startTime) && empty($endTime))) {
                $where['usertables.user_access_digital_classroom.access_time'] = array(array('LT', strtotime(date('Y-m-d'))), array('EGT', strtotime(date('Y-m-d'))-604800));
            } else {
                if ($times == 'day') {
                    $where['usertables.user_access_digital_classroom.access_time'] = array(array('LT', strtotime($endTime)+86400), array('EGT', strtotime($startTime)));
                } elseif ($times == 'month') {
                    $where['usertables.user_access_digital_classroom.access_time'] = array(array('LT', $this->format($startTime, 'm')), array('EGT', strtotime($startTime.'-01')));
                } elseif ($times == 'year') {
                    $where['usertables.user_access_digital_classroom.access_time'] = array(array('LT', $this->format($startTime, 'Y')), array('EGT', strtotime($startTime.'-01-01')));
                }
            }
            //unset($where['biz_lesson_planning.create_at']);
            /*******************************教师上课统计*************************************/
            $Model = M('usertables.user_access_digital_classroom');
            $total = $Model
                ->field('dict_course.course_name name,count(1) total')
                ->join('dict_course on dict_course.id=usertables.user_access_digital_classroom.course_id')
                ->join('auth_teacher on auth_teacher.id=usertables.user_access_digital_classroom.user_id')
                ->join('dict_schoollist ON dict_schoollist.id = auth_teacher.school_id')
                ->where($where)
                ->group('usertables.user_access_digital_classroom.course_id')
                ->select();
//echo M()->getLastSql();die;
            foreach ($total as $k => $item) {
                $data[$k]['name'] = $item['name'];
                $data[$k]['y'] = $item['total']+0;
            }
           /* $data[0]['name'] = '语文';
            $data[0]['y'] = 20;
            $data[1]['name'] = '数学';
            $data[1]['y'] = 20;
            $data[2]['name'] = '英语';
            $data[2]['y'] = 30;
            $data[3]['name'] = '物理';
            $data[3]['y'] = 40;
            $data[4]['name'] = '生物';
            $data[4]['y'] = 50;
            $data[5]['name'] = '化学';
            $data[5]['y'] = 60;
            $data[6]['name'] = '地理';
            $data[6]['y'] = 70;*/
            $this->ajaxreturn(array('status' => 200, 'data' => $data));
        }


    }

    /**
     *描述：人员登录统计详情页
     */
    public function personnelStatisticsOfLogin()
    {
        $teacher_model = D('Auth_teacher');
        $student_model = D('Auth_student');
        $class_model = D('Biz_class');
        $school_model = D('Dict_schoollist');
        $teacher_condition['apply_school_status'] = TEACHER_AWAIT_APPLY_SCHOOL_STATUS;
        $teacher_condition['auth_teacher.school_id'] = session('school.school_id');
        $teacher_result = $teacher_model->getTeacherFixedData($teacher_condition);

        $student_condition['apply_school_status'] = STUDENT_AWAIT_APPLY_SCHOOL_STATUS;
        $student_condition['auth_student.school_id'] = session('school.school_id');
        $student_result = $student_model->getStudentFixedData($student_condition);

        $class_condition['biz_class.school_id'] = session('school.school_id');
        $class_condition['class_status'] = SCHOOL_AUTH_CLASS;
        $class_condition['biz_class.is_delete'] = 0;
        $class_count = $class_model->getClassCount($class_condition);

        $teacher_count_condition['apply_school_status'] = TEACHER_PASS_APPLY_SCHOOL_STATUS;
        $teacher_count_condition['auth_teacher.school_id'] = session('school.school_id');
        $teacher_count = $teacher_model->getTeacherCount($teacher_count_condition);

        $student_count_condition['apply_school_status'] = STUDENT_PASS_APPLY_SCHOOL_STATUS;
        $student_count_condition['auth_student.school_id'] = session('school.school_id');
        $student_count = $student_model->getStudentCount($student_count_condition);

        $school_result = $school_model->getSchoolInfo(session('school.school_id'));
        $school_name = $school_result['school_name'];
        $string = $school_name . '现有班级' . $class_count . '个,' . '教师' . $teacher_count . '人,' . '学生' . $student_count . '人';

        $this->assign('login_user', session('school.name'));
        $this->assign('introduce', $string);


        $grade_model = D('Dict_grade');
        $schoolId = session('school.school_id');
        $where['biz_class.class_status'] = 1;
        $where['biz_class.is_delete'] = 0;
        $where['dict_schoollist.id'] = $schoolId;
        $result = $grade_model->getGradeListBySchool($where);
        $this->assign('gradeList', json_encode($result));
        $role = getParameter('role', 'int');
        $times = getParameter('times', 'str', false);
        $startTime = getParameter('startTime', 'str', false);
        $endTime = getParameter('endTime', 'str', false);

        if (empty($times) || (empty($startTime) && empty($endTime))) {
            $where['access_time'] = array(array('ELT', strtotime(date('Y-m-d'))), array('EGT', strtotime(date('Y-m-d'))));
            $times = 'day';
            $startTime = date('Y-m-d', strtotime(date('Y-m-d')) - 604800);
            $endTime = date('Y-m-d', strtotime(date('Y-m-d')));
        }
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);
        $this->assign('times', $times);
        $this->assign('role', $role);
        $this->display('loginStatistics');
    }

    /**
     *描述：登录统计饼状图接口
     */
    public function loginStatisticsByPieChart()
    {
        $schoolId = session('school.school_id');
        $role = getParameter('role', 'int');
        $times = getParameter('times', 'str', false);
        $startTime = getParameter('startTime', 'str', false);
        $endTime = getParameter('endTime', 'str', false);

        $teacher_model = D('Auth_teacher');
        $student_model = D('Auth_student');
        $parent_model = D('Auth_parent');

        if (empty($times) || (empty($startTime) && empty($endTime))) {
            $where['access_time'] = array(array('LT', strtotime(date('Y-m-d'))), array('EGT', strtotime(date('Y-m-d'))-604800));
        } else {
            if ($times == 'day') {
                $where['access_time'] = array(array('LT', strtotime($endTime)+86400), array('EGT', strtotime($startTime)));
            } elseif ($times == 'month') {
                $where['access_time'] = array(array('LT', $this->format($startTime, 'm')), array('EGT', strtotime($startTime.'-01')));
            } elseif ($times == 'year') {
                $where['access_time'] = array(array('LT', $this->format($startTime, 'Y')), array('EGT', strtotime($startTime.'-01-01')));
            }
        }
        $where['school_id'] = $schoolId;
        $where['apply_school_status'] = 1;
        //获取此学校的所有人员
        if ($role == 2) {
            $teacher_count_condition['apply_school_status'] = TEACHER_PASS_APPLY_SCHOOL_STATUS;
            $teacher_count_condition['auth_teacher.school_id'] = session('school.school_id');
            $teacher_count = $teacher_model->getTeacherCount($teacher_count_condition);
            /*****************************获取登录教师*********************************/
            $teacherData = M('usertables.access_history_teacher')
                ->field('usertables.access_history_teacher.id')
                ->join('auth_teacher ON auth_teacher.id = usertables.access_history_teacher.teacher_id')
                ->where($where)
                ->group('auth_teacher.id')
                ->select();
//echo M()->getLastSql();die;
            $loginCount = count($teacherData);
            $percentage = round(($loginCount / $teacher_count) * 100);
            $notLoginCount = $teacher_count - $loginCount;
        } elseif ($role == 3) {
            $student_count_condition['apply_school_status'] = STUDENT_PASS_APPLY_SCHOOL_STATUS;
            $student_count_condition['auth_student.school_id'] = session('school.school_id');
            $student_count = $student_model->getStudentCount($student_count_condition);
            /*****************************获取登录学生*********************************/
            $studentData = M('usertables.access_history_student')
                ->field('usertables.access_history_student.id')
                ->join('auth_student ON auth_student.id = usertables.access_history_student.student_id')
                ->where($where)
                ->group('auth_student.id')
                ->select();
            $loginCount = count($studentData);
            $percentage = round(($loginCount / $student_count) * 100);
            $notLoginCount = $student_count - $loginCount;
        } elseif ($role == 4) {
            unset($where['school_id']);
            //获取家长的总人数
            $parent_count_condition['apply_school_status'] = STUDENT_PASS_APPLY_SCHOOL_STATUS;
            $parent_count_condition['auth_student.school_id'] = session('school.school_id');
            $parent_count = $parent_model->getParentCount($parent_count_condition);
            /*****************************获取登录家长*********************************/
            $where['auth_student.school_id'] = $schoolId;
            $parentData = M('usertables.access_history_parent')
                ->field('usertables.access_history_parent.id')
                ->join('auth_parent ON auth_parent.id = usertables.access_history_parent.parent_id')
                ->join('auth_student ON auth_student.parent_id = auth_parent.id')
                ->where($where)
                ->group('auth_parent.id')
                ->select();
            $loginCount = count($parentData);
            $percentage = round(($loginCount / $parent_count) * 100);
            $notLoginCount = $parent_count - $loginCount;
        }
        /*$data['alreadyLoggedIn'] = $percentage;
        $data['logOut'] = 100 - $percentage;*/
        $data[0]['name'] = '已登录';
        $data[0]['num'] = $loginCount + 0;
        $data[0]['y'] = $percentage;
        $data[1]['name'] = '未登录';
        $data[1]['num'] = $notLoginCount;
        $data[1]['y'] = 100 - $percentage;
        $this->ajaxreturn(array('status' => '200', 'data' => $data));

    }

    /**
     *描述：登录统计柱状图接口
     */
    public function loginStatisticsByBarChart()
    {
        $role = getParameter('role', 'int');
        $times = getParameter('times', 'str', false);
        $startTime = getParameter('startTime', 'str', false);
        $endTime = getParameter('endTime', 'str', false);
        $auth_teacher_second = D('Auth_teacher_second');
        $auth_student = D('Auth_student');
        $auth_parent = D('Auth_parent');

        if (empty($times) || (empty($startTime) && empty($endTime))) {
            $where['access_time'] = array(array('LT', strtotime(date('Y-m-d'))), array('EGT', strtotime(date('Y-m-d'))-604800));
        } else {
            if ($times == 'day') {
                $where['access_time'] = array(array('LT', strtotime($endTime)+86400), array('EGT', strtotime($startTime)));
            } elseif ($times == 'month') {
                $where['access_time'] = array(array('LT', $this->format($startTime, 'm')), array('EGT', strtotime($startTime.'-01')));
            } elseif ($times == 'year') {
                $where['access_time'] = array(array('LT', $this->format($startTime, 'Y')), array('EGT', strtotime($startTime.'-01-01')));
            }
        }
        if ($role == 2) {
            $teacher_condition['apply_school_status'] = TEACHER_PASS_APPLY_SCHOOL_STATUS;
            $teacher_condition['school_id'] = session('school.school_id');
            $teacher_condition = array_merge($teacher_condition, $where);
            //获取登录人数根据学科
            $alreadyLoggedInCount = $auth_teacher_second->getAlreadyLoggedInCount2($teacher_condition);
//echo M()->getLastSql();die;
            foreach ($alreadyLoggedInCount as $k=>$alreadyLoggedInItem) {
                $data[$k]['name'] = $alreadyLoggedInItem['course_name'];
                $data[$k]['y'] = $alreadyLoggedInItem['access_total'] + 0;
            }
            /*$data[0]['name'] = '语文';
            $data[0]['y'] = 20;
            $data[1]['name'] = '数学';
            $data[1]['y'] = 20;
            $data[2]['name'] = '英语';
            $data[2]['y'] = 30;
            $data[3]['name'] = '物理';
            $data[3]['y'] = 40;
            $data[4]['name'] = '生物';
            $data[4]['y'] = 50;
            $data[5]['name'] = '化学';
            $data[5]['y'] = 60;
            $data[6]['name'] = '地理';
            $data[6]['y'] = 70;*/
        } elseif ($role == 3) {
            $grade = getParameter('grade', 'int');
            $student_condition['dict_grade.id'] = $grade;
            $student_condition['auth_student.school_id'] = session('school.school_id');
            $student_condition = array_merge($student_condition, $where);
            //获取登录人数根据学科
            $alreadyLoggedInCount = $auth_student->getAlreadyLoggedInCount2($student_condition);
//echo M()->getLastSql();die;
             foreach ($alreadyLoggedInCount as $k=>$alreadyLoggedInItem) {
                 $data[$k]['name'] = $alreadyLoggedInItem['name'];
                 $data[$k]['y'] = $alreadyLoggedInItem['access_total'] + 0;
             }
            /*$data[0]['name'] = '一班';
            $data[0]['y'] = 20;
            $data[1]['name'] = '二班';
            $data[1]['y'] = 20;
            $data[2]['name'] = '三班';
            $data[2]['y'] = 30;
            $data[3]['name'] = '四班';
            $data[3]['y'] = 40;
            $data[4]['name'] = '五班';
            $data[4]['y'] = 50;
            $data[5]['name'] = '六班';
            $data[5]['y'] = 60;
            $data[6]['name'] = '七班';
            $data[6]['y'] = 70;*/
        } elseif ($role == 4) {
            $grade = getParameter('grade', 'int');
            $parent_condition['dict_grade.id'] = $grade;
            $parent_count_condition['apply_school_status'] = STUDENT_PASS_APPLY_SCHOOL_STATUS;
            $parent_condition['auth_student.school_id'] = session('school.school_id');
            $parent_condition = array_merge($parent_condition, $where);
            //获取登录人数根据学科
            $alreadyLoggedInCount = $auth_parent->getAlreadyLoggedInCount2($parent_condition);
            //获取所有人数根据学科
            /*$total = $auth_student->getTotal($student_condition);*/
            //获取百分比
            /*foreach ($alreadyLoggedInCount as $alreadyLoggedInItem) {
                foreach ($alreadyLoggedInCount as $totalItem) {
                    if ($totalItem['id'] == $alreadyLoggedInItem['id']) {
                        $data['name'] = $alreadyLoggedInItem['name'];
                        $data['percentage'] = round(($alreadyLoggedInItem['count'] / $totalItem['count'])* 100);
                        $data['number'] = $alreadyLoggedInItem['count'] . "/" . $totalItem['count'];
                    }
                }
            }*/
            foreach ($alreadyLoggedInCount as $k=>$alreadyLoggedInItem) {
                $data[$k]['name'] = $alreadyLoggedInItem['name'];
                $data[$k]['y'] = $alreadyLoggedInItem['access_total'] + 0;
            }
            /*$data[0]['name'] = '一班';
            $data[0]['y'] = 20;
            $data[1]['name'] = '二班';
            $data[1]['y'] = 20;
            $data[2]['name'] = '三班';
            $data[2]['y'] = 30;
            $data[3]['name'] = '四班';
            $data[3]['y'] = 40;
            $data[4]['name'] = '五班';
            $data[4]['y'] = 50;
            $data[5]['name'] = '六班';
            $data[5]['y'] = 60;
            $data[6]['name'] = '七班';
            $data[6]['y'] = 70;*/
        }
        $this->ajaxreturn(array('status' => '200', 'data' => $data));
    }

    /**
     *描述：登录统计折线图接口
     */
    public function loginStatisticsByLineChart()
    {
        $schoolId = session('school.school_id');
        $times = getParameter('times', 'str', false);
        $startTime = getParameter('startTime', 'str', false);
        $endTime = getParameter('endTime', 'str', false);
        $type = getParameter('type', 'str');

        $auth_teacher_second = D('Auth_teacher_second');
        $auth_student = D('Auth_student');
        $auth_parent = D('Auth_parent');
        /**************************筛选时间**************************************/
//        if (empty($times) || (empty($startTime) && empty($endTime))) {
//            $where['access_time'] = array(array('ELT', strtotime(date('Y-m-d'))), array('EGT', strtotime(date('Y-m-d'))));
//            /**************************时间轴**************************************/
//        } else {
//            if ($times == 'day') {
//                $where['access_time'] = array(array('ELT', strtotime($endTime)), array('EGT', strtotime($startTime)));
//                /**************************时间轴（待定）**************************************/
//
//            } elseif ($times == 'month') {
//
//
//                $where['access_time'] = array(array('LT', $this->format($startTime, 'm')), array('GT', strtotime($startTime)));
//                /**************************时间轴（按周）**************************************/
//                $data['weekNum'] = array();
//                $dayNum = date('t', strtotime($startTime));
//                $weekNum = ceil($dayNum / 7);
//                for ($i = 1; $i <= $weekNum; $i++) {
//                    array_push($data['weekNum'], $i);
//                }
//                /*****************************获取教师登录数*********************************/
//                if ($role == 2) {
//                    $auth_teacher_second = D('Auth_teacher_second');
//                    $teacher_condition['apply_school_status'] = TEACHER_PASS_APPLY_SCHOOL_STATUS;
//                    $teacher_condition['school_id'] = session('school.school_id');
//                    $teacher_condition = array_merge($teacher_condition, $where);
//                    $loginCount = $auth_teacher_second->getAlreadyLoggedInCount($teacher_condition);
//                } elseif ($role == 3) {
//
//                } elseif ($role == 4) {
//
//                }
//
//
//            } elseif ($times == 'year') {
//                $where['access_time'] = array(array('LT', $this->format($startTime, 'Y')), array('GT', strtotime($startTime . '-01-01')));
//                /**************************时间轴（按月）**************************************/
//            }
//        }

        if (empty($times) || (empty($startTime) && empty($endTime))) {
            $group = "date_format(from_unixtime(access_time),'%Y%m%d')";
            $endTime = strtotime(date('Y-m-d'));
            $startTime = $endTime - 604800;
            $where['access_time'] = array(array('LT', $endTime), array('EGT', $startTime));

            $time = array();
            for ($i = 0; $i < 7; $i++) {
                if ($startTime + (86400 * $i) > $endTime) {
                    $time[] = date('Y-m-d', $endTime - 1);
                } else {
                    $time[] = date('Y-m-d', $startTime + (86400 * $i));
                }
            }
            if ($type == 2) {
                $whereId = session('school.school_id');
                $whereCondetion = "access_time < $endTime AND access_time >= $startTime";
                $loginCount = $auth_teacher_second->getAlreadyLoggedInCount($whereId, $whereCondetion, $group);
                $totalData[0] = $this->contrast($loginCount, $time, '', $endTime);
                if(empty($loginCount)){
                    $this->ajaxreturn(array('status' => 200, 'data' => ''));
                }else{
                    $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                }
            } elseif ($type == 3) {
                /*****************************获取学生访问次数*********************************/
                $whereId = session('school.school_id');
                $whereCondetion = "access_time < $endTime AND access_time >= $startTime";
                $loginCount = $auth_student->getAlreadyLoggedInCount($whereId, $whereCondetion, $group);
                $totalData[0] = $this->contrast($loginCount, $time, '', $endTime);
                if(empty($loginCount)){
                    $this->ajaxreturn(array('status' => 200, 'data' => ''));
                }else{
                    $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                }
            } elseif ($type == 4) {
                /*****************************获取家长访问次数*********************************/
                $whereId = session('school.school_id');
                $whereCondetion = "access_time < $endTime AND access_time >= $startTime";
                $loginCount = $auth_parent->getAlreadyLoggedInCount($whereId, $whereCondetion, $group);
                $totalData[0] = $this->contrast($loginCount, $time, '', $endTime);
                if(empty($loginCount)){
                    $this->ajaxreturn(array('status' => 200, 'data' => ''));
                }else{
                    $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                }
            }
            /**************************时间轴**************************************/
        } else {
            if ($times == 'day') {
                $endTime = strtotime($endTime) + 86400;
                $startTime = strtotime($startTime);
                /**************************时间轴（6小时）**************************************/
                if ($endTime - $startTime <= 172800) {
                    $group = 'floor((access_time-' . $startTime . ')/21600)';
                    $time = array();
                    for ($i = 0; $i < ceil(($endTime - $startTime) / 21600); $i++) {
                        if ($startTime + (21600 * $i) > $endTime) {
                            $time[] = date('Y-m-d H', $endTime - 1);
                        } else {
                            $time[] = date('Y-m-d', $startTime + (21600 * $i));
                        }

                    }
                    if ($type == 2) {
                        $whereId = session('school.school_id');
                        $whereCondetion = "access_time < $endTime AND access_time >= $startTime";
                        $loginCount = $auth_teacher_second->getAlreadyLoggedInCount($whereId, $whereCondetion, $group);
                        $totalData[0] = $this->contrast($loginCount, $time, '', $endTime);
                        if(empty($loginCount)){
                            $this->ajaxreturn(array('status' => 200, 'data' => ''));
                        }else{
                            $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                        }
                    } elseif ($type == 3) {
                        /*****************************获取学生访问次数*********************************/
                        $whereId = session('school.school_id');
                        $whereCondetion = "access_time < $endTime AND access_time >= $startTime";
                        $loginCount = $auth_student->getAlreadyLoggedInCount($whereId, $whereCondetion, $group);
                        $totalData[0] = $this->contrast($loginCount, $time, '', $endTime);

                        if(empty($loginCount)){
                            $this->ajaxreturn(array('status' => 200, 'data' => ''));
                        }else{
                            $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                        }
                    } elseif ($type == 4) {
                        /*****************************获取家长访问次数*********************************/
                        $whereId = session('school.school_id');
                        $whereCondetion = "access_time < $endTime AND access_time >= $startTime";
                        $loginCount = $auth_parent->getAlreadyLoggedInCount($whereId, $whereCondetion, $group);
                        $totalData[0] = $this->contrast($loginCount, $time, '', $endTime);

                        if(empty($loginCount)){
                            $this->ajaxreturn(array('status' => 200, 'data' => ''));
                        }else{
                            $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                        }
                    }

                    /**************************时间轴（按天）**************************************/
                } elseif ($endTime - $startTime > 172800 && $endTime - $startTime <= 2592000) {
                    $group = "date_format(from_unixtime(access_time),'%Y%m%d')";
                    $time = array();
                    for ($i = 0; $i < ceil(($endTime - $startTime) / 86400); $i++) {
                        if ($startTime + (86400 * $i) > $endTime) {
                            $time[] = date('Y-m-d', $endTime - 1);
                        } else {
                            $time[] = date('Y-m-d', $startTime + (86400 * $i));
                        }

                    }
                    if ($type == 2) {
                        $whereId = session('school.school_id');
                        $whereCondetion = "access_time < $endTime AND access_time >= $startTime";
                        $loginCount = $auth_teacher_second->getAlreadyLoggedInCount($whereId, $whereCondetion, $group);
                        $totalData[0] = $this->contrast($loginCount, $time, '', $endTime);
                        if(empty($loginCount)){
                            $this->ajaxreturn(array('status' => 200, 'data' => ''));
                        }else{
                            $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                        }
                    } elseif ($type == 3) {
                        /*****************************获取学生访问次数*********************************/
                        $whereId = session('school.school_id');
                        $whereCondetion = "access_time < $endTime AND access_time >= $startTime";
                        $loginCount = $auth_student->getAlreadyLoggedInCount($whereId, $whereCondetion, $group);
                        $totalData[0] = $this->contrast($loginCount, $time, '', $endTime);
    //TODO:折线图有错误
//    var_dump($loginCount);die;
//echo M()->getLastSql();die;
                        if(empty($loginCount)){
                            $this->ajaxreturn(array('status' => 200, 'data' => ''));
                        }else{
                            $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                        }
                    } elseif ($type == 4) {
                        /*****************************获取家长访问次数*********************************/
                        $whereId = session('school.school_id');
                        $whereCondetion = "access_time < $endTime AND access_time >= $startTime";
                        $loginCount = $auth_parent->getAlreadyLoggedInCount($whereId, $whereCondetion, $group);
                        $totalData[0] = $this->contrast($loginCount, $time, '', $endTime);

                        if(empty($loginCount)){
                            $this->ajaxreturn(array('status' => 200, 'data' => ''));
                        }else{
                            $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                        }
                    }
                }
                } elseif ($times == 'month') {
                    $endTime = $this->format($startTime, 'm');
                    /**************************时间轴（按周）**************************************/
                    $time = array();
                    for ($i = 0; $i < ceil(($this->format($startTime, 'm') - strtotime($startTime.'-01')) / 604800); $i++) {
                        if (strtotime($startTime.'-01') + (604800 * $i) > $this->format($startTime, 'm')) {
                            $time[] = date('Y-m-d', $this->format($startTime, 'm') - 1);
                        } else {
                            $time[] = date('Y-m-d', strtotime($startTime.'-01') + (604800 * $i));
                        }

                    }
                $startTime = strtotime($startTime.'-01');
                $group = 'floor((access_time-' . $startTime . ')/604800)';
                    /*$dayNum = date('t', strtotime($startTime));
                    $weekNum = ceil($dayNum / 7);
                    for ($i = 1; $i <= $weekNum; $i++) {
                        array_push($data['weekNum'], $i);
                    }*/
                    /*****************************获取教师访问次数*********************************/
                    if ($type == 2) {
                        $whereId = session('school.school_id');
                        $whereCondetion = "access_time < $endTime AND access_time >= $startTime";
                        $loginCount = $auth_teacher_second->getAlreadyLoggedInCount($whereId, $whereCondetion, $group);
                        $totalData[0] = $this->contrast($loginCount, $time, '', $endTime);
                        if(empty($loginCount)){
                            $this->ajaxreturn(array('status' => 200, 'data' => ''));
                        }else{
                            $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                        }
                    } elseif ($type == 3) {
//                    /*****************************获取学生访问次数*********************************/
                        $whereId = session('school.school_id');
                        $whereCondetion = "access_time < $endTime AND access_time >= $startTime";
                        $loginCount = $auth_student->getAlreadyLoggedInCount($whereId, $whereCondetion, $group);
                        $totalData[0] = $this->contrast($loginCount, $time, '', $endTime);

                        if(empty($loginCount)){
                            $this->ajaxreturn(array('status' => 200, 'data' => ''));
                        }else{
                            $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                        }
                    } elseif ($type == 4) {
                        /*****************************获取家长访问次数*********************************/

                        $whereId = session('school.school_id');
                        $whereCondetion = "access_time < $endTime AND access_time >= $startTime";
                        $loginCount = $auth_parent->getAlreadyLoggedInCount($whereId, $whereCondetion, $group);
                        $totalData[0] = $this->contrast($loginCount, $time, '', $endTime);

                        if(empty($loginCount)){
                            $this->ajaxreturn(array('status' => 200, 'data' => ''));
                        }else{
                            $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                        }
                    }
                    } elseif ($times == 'year') {
                        $group = 'date_format(from_unixtime(access_time),\'%Y%m\')';
                        $endTime = $this->format($startTime, 'Y');
                        /**************************时间轴（按月）**************************************/
                        $time = array($startTime . '-01', $startTime . '-02', $startTime . '-03', $startTime . '-04', $startTime . '-05', $startTime . '-06', $startTime . '-07', $startTime . '-08', $startTime . '-09', $startTime . '-10', $startTime . '-11', $startTime . '-12',);
                $startTime = strtotime($startTime . '-01-01');
                        if ($type == 2) {
                            $whereId = session('school.school_id');
                            $whereCondetion = "access_time < $endTime AND access_time >= $startTime";
                            $loginCount = $auth_teacher_second->getAlreadyLoggedInCount($whereId, $whereCondetion, $group);
                            $totalData[0] = $this->contrast($loginCount, $time, '', $endTime);
                            if(empty($loginCount)){
                                $this->ajaxreturn(array('status' => 200, 'data' => ''));
                            }else{
                                $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                            }
                        } elseif ($type == 3) {
                            /*****************************获取学生访问次数*********************************/
                            $whereId = session('school.school_id');
                            $whereCondetion = "access_time < $endTime AND access_time >= $startTime";
                            $loginCount = $auth_student->getAlreadyLoggedInCount($whereId, $whereCondetion, $group);
                            $totalData[0] = $this->contrast($loginCount, $time, '', $endTime);

                            if(empty($loginCount)){
                                $this->ajaxreturn(array('status' => 200, 'data' => ''));
                            }else{
                                $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                            }
                        } elseif ($type == 4) {
                            /*****************************获取家长访问次数*********************************/
                            $whereId = session('school.school_id');
                            $whereCondetion = "access_time < $endTime AND access_time >= $startTime";
                            $loginCount = $auth_parent->getAlreadyLoggedInCount($whereId, $whereCondetion, $group);
                            $totalData[0] = $this->contrast($loginCount, $time, '', $endTime);

                            if(empty($loginCount)){
                                $this->ajaxreturn(array('status' => 200, 'data' => ''));
                            }else{
                                $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $totalData, 'timeList' => $time)));
                            }
                        }
                    }
                }
            /*$time = array('2018-06-01', '2018-06-02', '2018-06-03', '2018-06-04', '2018-06-05', '2018-06-06', '2018-06-07', '2018-06-08', '2018-06-09', '2018-06-10');
            if ($type == 2) {
                $data[0]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
            } elseif ($type == 3) {
                $data[0]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
            } elseif ($type == 4) {
                $data[0]['data'] = array(100, 150, 145, 78, 56, 88, 90, 188, 166, 260);
            }
            $this->ajaxreturn(array('status' => 200, 'data' => array('data' => $data, 'timeList' => $time)));*/
        }
    }
