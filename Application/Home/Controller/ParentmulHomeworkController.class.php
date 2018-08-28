<?php
namespace Home\Controller;

use Think\Controller;
use Common\Common\SMS;
use Common\Common\DES3;
use Common\Common\REDIS;
use Think\Verify;
define('PARENT_IMAGE_PRODUCE_ID',33);
define('PARENT_FORGET_PASSWORD_PRODUCE_ID',63);

class ParentmulHomeworkController extends PublicController
{
    public $c_a = '';
    public function __construct() {
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));
        $this->c_a = CONTROLLER_NAME."_".ACTION_NAME;
    }

    //多媒体作业列表
    public function mulHomework()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '孩子历史作业');
        $this->assign('navicon', 'zuoyexitong');

        $mca = I('mca');
        if ( $mca == 'action' ) {
            $this->assign('kw',1);
        }

        $courses = D('Exercises_Course')->getCourseList();
        foreach ($courses as $temp){
            if($temp['id'] == 3){
                $coursesTrue[] = $temp;//学科只要英语
            }
        }
        $data['classId'] = I('classId');
        $data['courseId'] = I('courseId');
        $data['keyword'] = I('keyword');
        $data['type'] = I('type');//已提交状态和未提交状态
        $data['homeworkType'] = I('homeworkType');
        $data['release_time'] = I('release_time');
        $data['sort'] = I('sort');//排序置1位耗时正序2位耗时倒序3为得分正序4为得分倒序
        $data['studentName'] = I('student_name');


        $condition['studentName'] = !empty($data['studentName'])?$data['studentName']:null;
        $condition['classIdBySerach'] = !empty($data['classId'])?$data['classId']:null;
        $condition['courseId'] = !empty($data['courseId'])?$data['courseId']:null;
        $condition['homeworkType'] = !empty($data['homeworkType'])?$data['homeworkType']:null;
        $condition['keyword'] =!empty($data['keyword'])?$data['keyword']:null;
        $condition['release_time'] = !empty($data['release_time'])?$data['release_time']:null;
        $condition['sort'] = !empty($data['sort'])?$data['sort']:null;
        if($data['type'] == 1){
            $condition['hasSubmited'] = true;
        }elseif($data['type'] == 2){
            $condition['hasSubmited'] = false;
        }

        $condition['role'] = ROLE_PARENT;
        $condition['userId'] = session('parent.id');
        $p = I('p');
        if (empty($p)) {
            $p = 1;
        }
        $pageIndex = $p;
        $pageSize= 20;

        $result = D('Exercises_homework_class_relation')->getHomeworkListGroup($condition,$pageIndex,$pageSize,'id');//echo M()->getLastSql();die;
        $count = D('Exercises_homework_class_relation')->getHomeworkListCount($condition,'id');

        foreach ($result as $k=>$v) {
            $release_time = strtotime($result['release_time']);
            if ($release_time===false) {
                $result[$k]['is_release'] = 1; //显示
            } else {
                $result[$k]['is_release'] = 2; //不显示
            }

        }


        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($condition as $key => $val) {
            $Page->parameter[$key] = $val;
        }
        $show = $Page->show();
        $this->page = $show;
        $this->list = $result;
//var_dump($result);die;
        $this->assign('courseList', $coursesTrue);//所有学科
        $classList = D('Exercises_homework_class_relation')->getHomeworkCountGroupByClassId($condition); //echo M()->getLastSql();die;
        $this->where = $data;
        $this->class_list = $classList;
        $this->display();

    }

/*
 *多媒体作业完成详细情况
 */
    public function mulHomeworkCompleteDetails()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '查作业');
        $this->assign('navicon', 'zuoyexitong');


        //试题信息
        //$homeworkId = $_GET['homeworkId'];
        //$studentId = $_GET['studentId'];

        $homeworkId = I('homeworkId');
        $studentId = I('studentId');
        $classId = I('class_id');
        $this->homeworkId = $homeworkId;
        $this->studentId = $studentId;
        $this->classId = $classId;
        $info = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);//echo M()->getLastSql();die;
        $this->data = $info;

        $list = D('Exercises_homework_basics')->getHomeworkIdgetfk($homeworkId);
        $newlist = [];
        foreach ($list as $k=>$v) {
            array_push($newlist,$v['fname'].$v['sname']);
        }
        //根据学生ID作业ID班级ID查询总得分
        $score = D('Exercises_student_homework')->getHomeworkTotalScore($studentId,$homeworkId,$classId);
        $homeworkBaseInfo = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);
        $this->assign('score',round($score[0]['total_score']/$homeworkBaseInfo['exercises_num']));
        $this->newlist = implode(',',$newlist);
        $this->display();
    }

    //根据作业id获取所有习题
    public function getExerciseHomeworkList() {

        $homeworkId =getParameter('homeworkId','int',true);
        $result = D('Exercises_homework_basics')->getExercises($homeworkId);
        $this->showjson(200, '', $result);
    }

    /**
     *描述：作业详情
     */
    public function homeWorkDetail(){
        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '查作业');
        $this->assign('navicon', 'zuoyexitong');
        $homeworkId = I('homeworkId');
        $info = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);
        $this->data = $info;

        $list = D('Exercises_homework_basics')->getHomeworkIdgetfk($homeworkId);
        $newlist = [];
        foreach ($list as $k=>$v) {
            array_push($newlist,$v['fname'].$v['sname']);
        }
        $this->newlist = implode(',',$newlist);
        $this->homeworkId = $homeworkId;
        $this->tip = 1;
        $this->display('mulHomeworkCompleteDetails');
    }
}
