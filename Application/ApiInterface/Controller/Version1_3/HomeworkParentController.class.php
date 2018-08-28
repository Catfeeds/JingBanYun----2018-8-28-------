<?php
namespace ApiInterface\Controller\Version1_3;

define('FAKE_DATA',1);


class HomeworkParentController extends PublicController
{

    public $pageSize = 20;
    public $model;

    public function __construct()
    {
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
        require($_SERVER['DOCUMENT_ROOT'].'/Application/Exercise/Conf/const.php');
    }

    function getPageSize()
    {
        return $this->pageSize;
    }
    private function status2Number($status)
    {
        switch($status)
        {
            case '新': return 1;
            case '已截止': return 2;
            case '查看报告': return 3;
            case '待批改': return 4;
            default:break;
        }
        return -1;
    }

    //查看作业列表
    public function getHomeworkList() {
        A('ApiInterface/Version1_3/HomeworkStudent')->getHomeworkList();
    }


    /**
     * @描述：查看作业列表
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：studentId[int] Y 学生ID
     * @参数：pageIndex[int] N 分页INDEX 从1开始
     * @参数：pageSize[int] N 分页大小 不传默认4
     * @参数：isFlatten2[int] N 是否只显示前2条
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getStudentHomeworkList()
    {

        A('ApiInterface/Version1_3/HomeworkStudent')->getHomeworkList();
    }


    //获取学生未做的
    public function getWeiHomeworkDetail() {
        $details    = A('ApiInterface/Version1_3/HomeworkStudent')->getHomeworkDetail();
        $this->showMessage(200, 'success', $details);
    }

    //获取学生待批改作业详情
    public function getStudentHomeWorkDetails() {
//        $homeworkId = getParameter('homeworkId','int');
//        $classId = getParameter('classId','int');
//        $studentId = getParameter('studentId','int');

        $details    = A('ApiInterface/Version1_3/HomeworkStudent')->getSubmitHomeworkDetail();
        $this->showMessage(200, 'success', $details);
//        $map['exercises_student_homework.student_id'] = $studentId;
//        $map['exercises_student_homework.work_id'] = $homeworkId;
//        $map['exercises_student_homework.class_id'] = $classId;
//
//        $details = M('exercises_student_homework')
//            ->join("left join exercises_homwork_basics ON exercises_homwork_basics.id=exercises_student_homework.work_id")
//            ->join("left join auth_student ON auth_student.id=exercises_student_homework.student_id")
//            ->where($map)
//            ->field("exercises_homwork_basics.name,auth_student.student_name,exercises_student_homework.id as submitId,submit_at,work_timeout,correct_status,exercises_homwork_basics.total_score")
//            ->find();
//
//        $details['student_name'] =$details['student_name']."的作业";
//
//        if (!empty($details)) {
//            $cmap['work_id'] = $details['submitid'];
//            $cmap['is_delete'] = 1;
//            $cmap['class_id'] = $classId;
//            $cmap['homework_id'] = $homeworkId;
//            $cmap['student_id'] = $studentId;
//            $count = M('exercises_student_relation')->where($cmap)->count();
//
//            $setsum =  M('exercises_student_relation')->field("sum(exercises_score) as score")->where($cmap)->select();
//            if (!empty($setsum)) {
//                $details['score'] =  $setsum[0]["score"];
//            }
//
//            $cmap['is_right'] = 1;
//            $rightcount = M('exercises_student_relation')->where($cmap)->count();
//            $details['correct_rate'] = (($rightcount/$count)*100)."%";
//            unset($cmap['is_right']);
//
//            $wmap['exercises_student_relation.work_id'] = $details['submitid'];
//            $wmap['exercises_student_relation.class_id'] = $classId;
//            $wmap['exercises_student_relation.homework_id'] = $homeworkId;
//            $wmap['exercises_student_relation.student_id'] = $studentId;
//            $wmap['exercises_student_relation.is_delete'] = 1;
//
//            $list = M('exercises_student_relation')
//                ->join("exercises_createexercise ON exercises_createexercise.id=exercises_student_relation.exercises_id")
//                ->join("left join exercises_homwork_relation ON exercises_homwork_relation.exercises_id=exercises_createexercise.id")
//                ->field("exercises_createexercise.id as cid,exercises_student_relation.exercises_score,exercises_homwork_relation.id as eid,exercises_student_relation.id,exercises_createexercise.subject_name,exercises_createexercise.ordinary_type,exercises_homwork_relation.knowledge_type,exercises_createexercise.words as analysis,exercises_createexercise.answer")
//                ->where($wmap)
//                ->group("exercises_student_relation.exercises_id")
//                ->select();
//
//            $exerciseId = [];
//
//            foreach ($list as $k=>$v) {
//                $exerciseId[] =$v['cid'];
//            }
//
//            $exerciseId = implode(",",$exerciseId);
//
//            $elist = A('ApiInterface/Version1_3/HomeworkCommon')->getOrderedExerciseList($exerciseId,3,$studentId);
//
//            $details['exercise_list'] = $elist;
//
//            //寄语
//            //exercises_student_homework
//            $clist = M('exercises_homework_comment')->where("homework_submit_id=".$details["submitid"])->select();
//
//            foreach ($clist as $kk=>$vv) {
//                $clist[$kk]['comment'] = htmlspecialchars_decode($vv['comment']);
//            }
//            $details['comment_list'] = $clist;
//        }
//
//        $this->showMessage( 200,'success',$details);
    }

    //获取最近一次作业的得分
    public function getStudentLatelyHomeWork() {
        $studentId = getParameter('studentId', 'int');
        $map['student_id'] = $studentId;
        $map['correct_status'] = 1;
        $info = M('exercises_student_homework')->where($map)->order('submit_at desc')->field('total_score')->find();
        $empty_object=(object)array();

        if (!empty($info)) {
            $this->showMessage(200, 'success', $info);
        } else {
            $this->showMessage(200, 'success', $empty_object);
        }
    }

    //获取班级排行榜
    public function getStudentRanking() {
        $work_id = getParameter('work_id', 'int');
        $class_id = getParameter('class_id', 'int');
        $map['exercises_student_homework.work_id'] = $work_id;
        $map['exercises_student_homework.class_id'] = $class_id;

        $list = M('exercises_student_homework')
            ->join('auth_student on auth_student.id=exercises_student_homework.student_id')
            ->where($map)
            ->field('exercises_student_homework.total_score,exercises_student_homework.submit_at,auth_student.student_name,auth_student.avatar')
            ->order('exercises_student_homework.total_score desc')
            ->select();

        if (!empty($list)) {
            $this->showMessage(200, 'success', $list);
        } else {
            $info['total_score'] = -1;
            $this->showMessage(500, 'success', $list);
        }
    }

    //获取教师寄语
    public function getTeacherWrote() {
        $work_id = getParameter('work_id', 'int');
        $class_id = getParameter('class_id', 'int');
        $student_id = getParameter('student_id', 'int');

        $map['class_id'] = $class_id;
        $map['work_id'] = $work_id;
        $map['student_id'] = $student_id;

        $list = M('exercises_teacher_wrote')->where($map)->field('id,content,type,is_read')->select();
        if (!empty($list)) {
            $this->showMessage(200, 'success', $list);
        } else {
            $info['total_score'] = -1;
            $this->showMessage(500, 'success', $list);
        }
    }
    //老师寄语已读操作
    public function setTeacherRead() {
        $id = getParameter('id', 'int');
        $map['id'] = $id;
        $data['is_read'] = 2;
        $update = M('exercises_teacher_wrote')->where($map)->save($data);
        if ($update!==false) {
            $this->showMessage(200, 'success', "");
        } else {
            $this->showMessage(500, 'error', "");
        }
    }

    //作业学习轨迹
    public function getStudentLearningHomeWork() {
        $studentId = getParameter('studentId', 'int');
        $courseId = getParameter('courseId', 'int',false);
        $time = getParameter('time', 'int',false);
        $map['exercises_student_homework.student_id'] = $studentId;

        if ($courseId == -1) {
            unset($courseId);
        }

        if(!empty($courseId)) {
            $map['exercises_homwork_basics.course_id'] = $courseId;
        }
        if (empty($time)) {
            $map['exercises_student_homework.submit_at'] = array(array('GT',date("Y-m-d H:i:s", strtotime("-2 year"))), array('LT',date('Y-m-d H:i:s')),'and');
        }
        if ( $time == -1) {
            $map['exercises_student_homework.submit_at'] = array(array('GT',date("Y-m-d H:i:s", strtotime("-2 year"))), array('LT',date('Y-m-d H:i:s')),'and');
        }

        if ( $time == 2) {
            $map['exercises_student_homework.submit_at'] = array(array('GT',date("Y-m-d H:i:s", strtotime("-1 month"))), array('LT',date('Y-m-d H:i:s')),'and');
        }

        if ( $time == 3) {
            $map['exercises_student_homework.submit_at'] = array(array('GT',date("Y-m-d H:i:s", strtotime("-3 month"))), array('LT',date('Y-m-d H:i:s')),'and');
        }

        if ( $time == 4) {
            $map['exercises_student_homework.submit_at'] = array(array('GT',date("Y-m-d H:i:s", strtotime("-1 year"))), array('LT',date('Y-m-d H:i:s')),'and');
        }

        if ( $time == 5) {
            $map['exercises_student_homework.submit_at'] = array(array('GT',date("Y-m-d H:i:s", strtotime("-1 week"))), array('LT',date('Y-m-d H:i:s')),'and');
        }

        $map['exercises_student_homework.correct_status'] = 1;

        $info = M('exercises_student_homework')
                ->join('exercises_homwork_basics on exercises_homwork_basics.id=exercises_student_homework.work_id')
                ->where($map)->order('exercises_student_homework.submit_at desc')
                ->field('exercises_student_homework.total_score,exercises_homwork_basics.name')
                ->select();
        
        $x = array();
        $y = array();
        if (!empty($info)) {
            foreach ($info as $k=>$v) {
                array_push($x,$v['name']);
                array_push($y,intval($v['total_score']));
            }
        }

        $setinfo['setX'] = $x;
        $setinfo['setY'] = $y;

        if (!empty($info)) {
            $this->showMessage(200, 'success', $setinfo);
        } else {
            $this->showMessage(200, 'success', $setinfo);
        }

    }

    public function getCycleList () {
        $list = [
            [
                'id' => -1,
                'name' => '两年内'
            ],

            [
                'id' => 4,
                'name' => '一年内'
            ],

            [
                'id' => 3,
                'name' => '三个月内'
            ],

            [
                'id' => 2,
                'name' => '一个月内'
            ],
            [
                'id' => 5,
                'name' => '一周内'
            ],
        ];
        $this->showMessage(200, 'success', $list);

    }

    //获取学科
    public function getCourseList() {
        $list = D('Exercises_Course')->field("id,name")->getCourseList();

        $allList = array(array('id'=>-1,'name'=>'全部'));
        foreach ($list as $k=>$v) {
            unset($v['parent_id']);
            $allList[$k+1] = $v;
        }
        $this->showMessage(200, 'success', $allList);
    }
    /**
     * @描述：获取作业概要信息
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：homeworkId[int] Y 作业ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getHomeworkAbstract()
    {
        $homeworkId = getParameter('homeworkId', 'int');
        $result = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);
        $newResult = array();
        $newResult['name'] = $result['name'];
        $newResult['endTime'] = $result['end_time_c'];
        $newResult['releaseTime'] = $result['release_time_c'];
        $newResult['requirement'] = $result['jobsments'];
        $newResult['totalCount'] =  $result['exercises_num'];
        $newResult['totalScore'] =  100;//$result['total_score'];
        $newResult['status'] =  '待完成';
        $this->showMessage(200, 'success', $newResult);
    }

    /**
     * @描述：获取已提交作业的学生列表
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：homeworkId[int] Y 作业ID
     * @参数：classId[int] Y 班级ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getSubmitHomeworkList()
    {
        A('ApiInterface/Version1_2/HomeworkStudent')->getSubmitHomeworkList();
    }
    /**
     * @描述：查看未做作业详情
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：homeworkId[int] Y 作业ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getHomeworkDetail()
    {
        A('ApiInterface/Version1_2/HomeworkStudent')->getHomeworkDetail();
    }



    //已批改作业
    public function completionDetails() {

        $userId = getParameter('userId','int',false);
        $role = getParameter('role','int',false);
        $id = getParameter('homeworkId','int',false);
        $classId = getParameter('classId', 'int',false);
        $submitId = getParameter('submitId', 'int',false);
        $studentId = getParameter('studentId', 'int',false);
        $this->userId = $userId;
        $this->studentId = $studentId;
        $this->role = $role;
        $this->homeworkId = $id;
        $this->classId = $classId;
        $this->submitId = $submitId;

        $isFlatten = getParameter('isFlatten','int',false);

        $result = array();

        $model = D('Exercises_student_homework');
        $info = $model->getStudentHomeworkBriefInfo($submitId);
        $resultData = $model->getHomeworkExerciseList(0,0,$submitId);


        $result['submittime'] = $info['submit_at'];
        $result['status'] = '已完成';
        $result['duration'] = $info['work_timeout'];
        $result['point'] = $info['total_score'];
        $result['totalpoint'] = $info['total_score_base'];
        $result['data'] = $this->transExerciseData($resultData,'name,url,point,total_point,category,org_answer_url');

        $result['data'] = $this->flattenData($result['data'],$isFlatten);
        $result['_right'] =round((($result['point'] / $result['totalpoint'])*100),2) .'%';

        $this->result = $result;
        $this->display();
    }

    /**
     * @描述：查看已做作业详情
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：id[int] Y 作业提交ID
     * @参数：isFlatten[int] N 是否FLATTEN返回数组 0--否 1--是
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getSubmitHomeworkDetail()
    {
        $result = array();
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $id = getParameter('id','int');
        $isFlatten = getParameter('isFlatten','int',false);
        $model = D('Exercises_student_homework');
        $info = $model->getStudentHomeworkBriefInfo($id);

        $resultData = $model->getHomeworkExerciseList(0,0,$id);
        $result['submittime'] = $info['submit_at'];
        $result['status'] = '已完成';
        $result['duration'] = $info['work_timeout'];
        $result['point'] = $info['total_score'];
        $result['totalpoint'] = 100;//$info['total_score_base'];
        $result['data'] = $this->transExerciseData($resultData,'name,url,point,translation,total_point,category,org_answer_url');
        $result['data'] = $this->flattenData($result['data'],$isFlatten);
        $this->showMessage( 200,'success',$result);
    }
    private function transExerciseData($result,$fields)
    {
        $newResult = array();
        $sub2 = array();
        $sub3 = array();
        $lastMainCategory = 0;
        $lastSubMainCategory = 0;
        $category = '';
        $fieldArray = explode(',',$fields);
        $categoryConfig = json_decode(EXERCISE_CATEGORY,true);
        foreach($result as $key=>$val)
        {
            if($category != $val['category'])
            {
                list($mainCategory,$subCategory) = explode(',',$val['category']);

                if(empty($mainCategory) || empty($subCategory))
                    return array();
                if(!empty($sub2))
                {
                    if($lastSubMainCategory != 0) {
                        $sub3[] = array('name' => $categoryConfig[$lastMainCategory]['data'][$lastSubMainCategory]['name'],
                            'data' => $sub2
                        );
                        $sub2 = array();
                    }

                }

                $lastSubMainCategory = $subCategory;

                if($lastMainCategory != $mainCategory ){
                    if(!empty($sub3)) {
                        if ($lastMainCategory != 0) {
                            if(!empty($sub2))
                            {
                                $sub3[] = array('name' => $categoryConfig[$mainCategory]['data'][$lastSubMainCategory]['name'],
                                    'data' => $sub2
                                );
                                $sub2 = array();
                            }
                            $newResult[] = array('name' => $categoryConfig[$lastMainCategory]['name'],
                                'data' => $sub3
                            );
                            $sub3 = array();
                        }
                    }
                }
                $lastMainCategory = $mainCategory;
                $category = $val['category'];
            }
            $sub1 = array();
            foreach($fieldArray as $keyIndex=>$fieldName)
            {
                $sub1[$fieldName] = $val[$fieldName];
                if($fieldName==='category')
                {
                    $sub1[$fieldName] = explode(',',$val[$fieldName])[1];
                }
            }
            $sub2[] = $sub1;
        }
        if(!empty($sub2) && !empty($mainCategory))
            $sub3[] = array('name'=>$categoryConfig[$mainCategory]['data'][$subCategory]['name'],
                'data' => $sub2
            );

        if(!empty($sub3)){
            $newResult[] = array('name'=>$categoryConfig[$mainCategory]['name'],
                'data' => $sub3
            );
        }
        return $newResult;
    }


    private function flattenData($data,$isFlatten=0)
    {
        if($isFlatten == 0)
            return $data;
        $newData = array();
        if(0 <  $isFlatten) {

            foreach ($data as $key => $val) {
                foreach ($val['data'] as $subKey => $subVal) {
                    $newData[] =  $subVal;
                }
            }
        }
        if(1 <  $isFlatten)
        {
            $newDataTemp = $newData;
            $newData = array();
            foreach ($newDataTemp as $key => $val) {
                $name = $val['name'];
                $data = $val['data'];
                $subCount = sizeof($data);
                foreach($data as $subKey => $subVal)
                {
                    $subVal['exerciseName'] =  $name;
                    $subVal['count'] = $subCount;
                    $subVal['index'] = $subKey+1;
                    $newData[] = $subVal;
                }
            }
        }
        return  $newData;
    }
}
?>

