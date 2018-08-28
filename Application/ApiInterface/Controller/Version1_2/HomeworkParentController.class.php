<?php
namespace ApiInterface\Controller\Version1_2;

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
            default:break;
        }
        return -1;
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
        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');
        $studentId = getParameter('studentId', 'int');
        $pageIndex = getParameter('pageIndex','int',false);
        $pageSize = getParameter('pageSize','int',false);
        $isFlatten2 = getParameter('isFlatten2','int',false);
        if (intval($pageIndex) < 1)
            $pageIndex = 1;
        if (empty($pageSize))
            $pageSize = 4;
        //get my classes
        $classList = D('Biz_class_student')->getStudentClass($studentId);
        if(empty($classList))
            $this->showMessage(200, 'success', array());
        $classIds = implode(',',array_column($classList,'class_id'));
        $condition['role'] = ROLE_PARENT;
        $condition['studentId'] = $studentId;
        $condition['userId'] = $userId;
        $condition['classId'] = $classIds;
        $condition['studentCanSee'] = 1;
        $newResult = array();
        $subResult1 = array();
        $result = D('Exercises_homework_class_relation')->getHomeworkListGroup($condition, $pageIndex, $pageSize);
        foreach ($result as $key => $val) {
            $subResult = array();
            $homeworkIdList = explode("///", $val['homework_id']);
            $submitId = explode("///", $val['student_homeworkid']);
            $classIdList = explode("///", $val['class_id']);
            $classNameList = explode("///", $val['class_name']);
            $nameList = explode("///", $val['name']);
            $deadLineList = explode("///", $val['deadline']);
            $totalExerciseCountList = explode("///", $val['exercise_count']);
            $scoreList = explode("///", $val['student_score']);
            $courseList = explode("///", $val['course_name']);
            $teacherNameList = explode("///", $val['teacher_name']);
            $statusList = explode("///", $val['parent_status']);
            foreach ($classNameList as $i => $nameValue) {
                $subResult['homeworkId'] = $homeworkIdList[$i];
                $subResult['classId'] = $classIdList[$i];
                $subResult['submitId'] = $submitId[$i];
                /*
                $subResult['content1'] = $nameList[$i];
                $subResult['content2'] = $courseList[$i];
                $subResult['content3'] = '截止时间: '.$deadLineList[$i];
                $subResult['content4'] = $statusList[$i];
                $subResult['content5'] = $classNameList[$i];
                $subResult['content6'] = $teacherNameList[$i] . '老师布置'; //OK
                $subResult['content7'] = "共 {$totalExerciseCountList[$i]} 道题 共 {$totalScoreList[$i]} 分";
                $subResult['content8'] = $finishCountList[$i].' 人已提交作业';
                */
                $subResult['img_url'] = "http://jbyoss.oss-cn-beijing.aliyuncs.com/public/web_img/APPHomework/Parent/yingyu.png";
                $subResult['content1'] = $classNameList[$i] . ' ('.$courseList[$i] .')';
                $subResult['content2'] = $this->status2Number($statusList[$i]) ;
                $subResult['content3'] = $nameList[$i]."(共$totalExerciseCountList[$i]题)";
                $subResult['content4'] = $scoreList[$i] ;
                $subResult['content5'] = $deadLineList[$i].'截止';
                $subResult['content6'] = $teacherNameList[$i] . '老师布置';
                $subResult1[] = $subResult;
            }
            $newResult[] = array('date' => $val['date'], 'data' => $subResult1);
            $subResult1 = array();
        }
        $result = $newResult;
        if($isFlatten2)
        {
            $newResult = array();
            foreach($result as $key=>$val)
            {
                foreach($val['data'] as $subKey => $subVal)
                {
                    if(sizeof($newResult) < 2)
                    {
                        $newResult[] = $subVal;
                    }
                else
                    break;
                }
                if(sizeof($newResult) == 2)
                    break;
            }
            $result = $newResult;
        }
        $this->showMessage(200, 'success', $result);
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
        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');
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

}
?>