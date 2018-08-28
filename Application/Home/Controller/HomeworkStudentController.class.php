<?php
namespace Home\Controller;

define('FAKE_DATA',1);



class HomeworkStudentController extends PublicController
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



    /**
     * @描述：查看待做作业列表
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：pageIndex[int] N 分页INDEX 从1开始
     * @参数：pageSize[int] N 分页大小 不传默认4
     * @参数：isFlatten3[int] N 1 只查前3条
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getUnFinishHomeworkList()
    {
        $userId = session('student.id');
        $role = ROLE_STUDENT;
        $pageIndex = getParameter('pageIndex','int',false);
        $pageSize = getParameter('pageSize','int',false);
        $isFlatten3 = getParameter('isFlatten3','int',false);
        $requiredCount = 3;
        if (empty($pageIndex))
            $pageIndex = 1;
        if (empty($pageSize))
            $pageSize = 4;
        //get my classes
        $subResult1 = array();
        $classList = D('Biz_class_student')->getStudentClass($userId);
        $classIds = implode(',',array_column($classList,'class_id'));
        $condition['hasSubmited'] = false;
        $condition['role'] = ROLE_STUDENT;
        $condition['userId'] = $userId;
        $condition['classId'] = $classIds;
        $condition['studentCanSee'] = 1;
        $condition['type'] = HOMEWORK_PUBLISHED;
        $condition['deadline'] = " + 86400 > NOW()  ";
        $newResult = array();
        $result = D('Exercises_homework_class_relation')->getHomeworkListGroup($condition, $pageIndex, $pageSize,'id');
        foreach ($result as $key => $val) {
            $subResult = array();
            $homeworkIdList = explode("///", $val['homework_id']);
            $classIdList = explode("///", $val['class_id']);
            $classNameList = explode("///", $val['class_name']);
            $nameList = explode("///", $val['name']);
            $finishCountList = explode("///", $val['finish_count']);
            $deadLineList = explode("///", $val['deadline']);
            $totalExerciseCountList = explode("///", $val['exercise_count']);
            $totalScoreList = explode("///", $val['total_score']);
            $courseList = explode("///", $val['course_name']);
            $teacherNameList = explode("///", $val['teacher_name']);

            foreach ($classNameList as $i => $nameValue) {
                $subResult['homeworkId'] = $homeworkIdList[$i];
                $subResult['classId'] = $classIdList[$i];
                $subResult['content1'] = $nameList[$i];
                $subResult['content2'] = $courseList[$i];
                $subResult['content3'] = $deadLineList[$i];
                $subResult['content4'] = $classNameList[$i];
                $subResult['content5'] = $teacherNameList[$i] . '老师布置';
                $subResult['content6'] = "共 {$totalExerciseCountList[$i]} 道题 共 $totalScoreList[$i] 分";
                $subResult['content7'] = $finishCountList[$i].' 人已提交作业';
                $subResult['content9'] = $totalScoreList[$i];
            }
            $subResult['date'] = $val['date'];
            $newResult[] = $subResult;
        }
        $result = $newResult;
        $this->showMessage(200, 'success', $result);
    }
    /**
     * @描述：获取作业数量
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getHomeworkCount()
    {
        $result = array();
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        if(ROLE_STUDENT != $role)
            $this->showMessage( 500,'角色错误',$result);
        $condition = array();
        $condition['hasSubmited'] = false;
        $condition['role'] = ROLE_STUDENT;
        $condition['userId'] = $userId;
        $condition['type'] = HOMEWORK_PUBLISHED;
        //未完成数量
        $unFinishCount = D('Exercises_homework_class_relation')->getHomeworkListCount($condition,'id');

        $condition = array();
        $condition['role'] = ROLE_STUDENT;
        $condition['userId'] = $userId;
        $condition['hasSubmitOrEnded'] = 1;
        //已完成数量
        $homeworkBookCount = D('Exercises_homework_class_relation')->getHomeworkListCount($condition,'id');
        unset($condition['hasSubmitOrEnded']);
        $condition['hasProcessed'] = 1;
        //已批改数量
        $processedCount = D('Exercises_homework_class_relation')->getHomeworkListCount($condition,'id');
        $result['unFinishCount'] = $unFinishCount;
        $result['homeworkBookCount'] = $homeworkBookCount;
        $result['processedCount'] = $processedCount;
        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：获取我完成作业的学科年级列表对应数量
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getFinishHomeworkCount()
    {
        $userId = session('student.id');
        $role = ROLE_STUDENT;
        $condition['hasSubmited'] = true;
        $condition['role'] = ROLE_STUDENT;
        $condition['userId'] = $userId;
        $result = D('Exercises_homework_class_relation')->getHomeworkCountGroupByCourse($condition);
        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：查看作业列表
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：courseId[int] Y 学科ID
     * @参数：gradeId[int] Y 年级ID
     * @参数：pageIndex[int] N 分页INDEX 从1开始
     * @参数：pageSize[int] N 分页大小 不传默认4
     * @参数：classId[int] Y 班级ID
     * @参数：status[int] N 作业状态 1--待做 2--待批改 3--已过期 4--作业报告
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getHomeworkList()
    {
        $userId = session('student.id');
        $role = ROLE_STUDENT;
        $pageIndex = getParameter('pageIndex','int',false);
        $pageSize = getParameter('pageSize','int',false);
        $courseId = getParameter('courseId', 'int',false);
        $classId = getParameter('classId','int',false);
        $status = getParameter('status','int',false);


        if (empty($pageIndex))
            $pageIndex = 1;
        if (empty($pageSize))
            $pageSize = 400;
        $subResult1 = array();
        $condition['role'] = ROLE_STUDENT;
        $condition['userId'] = $userId;
        if(!empty($courseId))
            $condition['courseId'] = $courseId;
        if(!empty($classId))
            $condition['classId'] = $classId;
        if(!empty($status)){
            $condition['status'] = $status;
        }
        else
            $condition['status'] = 0;
        $newResult = array();
        $result = D('Exercises_homework_class_relation')->getHomeworkListGroup($condition, $pageIndex, $pageSize,'id');
        foreach ($result as $key => $val) {
            $subResult = array();
            $homeworkIdList = explode("///", $val['homework_id']);
            $classIdList = explode("///", $val['class_id']);
            $studentHomeworkIdList = explode("///", $val['student_homeworkid']);
            $classNameList = explode("///", $val['class_name']);
            $nameList = explode("///", $val['name']);
            $finishCountList = explode("///", $val['finish_count']);
            $deadLineList = explode("///", $val['deadline']);
            $totalExerciseCountList = explode("///", $val['exercise_count']);
            $totalScoreList = explode("///", $val['total_score']);
            $scoreList = explode("///", $val['sum_score']);
            $teacherNameList = explode("///", $val['teacher_name']);
            $correctInfoList = explode("///", $val['correct_info']);
            $courseNameList =  explode("///", $val['course_name']);
            foreach ($classNameList as $i => $nameValue) {
                $subResult['homeworkId'] = $homeworkIdList[$i];
                $subResult['classId'] = $classIdList[$i];
                $subResult['submitId'] = $studentHomeworkIdList[$i];
                $subResult['content1'] = $nameList[$i];
                $subResult['content2'] = $scoreList[$i];
                $subResult['content3'] = '截止时间: '.$deadLineList[$i];
                $subResult['content4'] = $correctInfoList[$i];
                $subResult['content5'] = $classNameList[$i];
                $subResult['content6'] = $teacherNameList[$i]. '老师布置';
                $subResult['content7'] = "共 {$totalExerciseCountList[$i]} 道题 共 $totalScoreList[$i] 分";
                $subResult['content8'] = $finishCountList[$i].' 人已提交作业';
                $subResult['content9'] = $courseNameList[$i];
                $subResult['date'] =  $val['date'];
            }
            $newResult[] = $subResult;
        }
        $result = $newResult;
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
        $userId = session('student.id');
        $role = ROLE_STUDENT;
        $homeworkId = getParameter('homeworkId', 'int');
        $result = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);
        $newResult = array();
        $newResult['name'] = $result['name'];
        $newResult['endTime'] = $result['end_time_c'];
        $newResult['requirement'] = $result['jobsments'];
        $newResult['totalCount'] =  $result['exercises_num'];
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

        $userId = session('student.id');
        $role = ROLE_STUDENT;
        $homeworkId = getParameter('homeworkId', 'int');
        $classId = getParameter('classId', 'int');
        $result = [];
        //只取已批改的
        $resultTemp = D('Exercises_student_homework')->getClassHomeworkStudentSubmitInfo($homeworkId, $classId, STUDENT_HOMEWORK_SUBMITANDCORRECTED, -1);
        foreach($resultTemp as $key=>$val)
        {
            if (preg_match('/Resources/', $val['avatar'])){
                if(strpos($val['avatar'],'.')===false)
                {
                    $val['avatar'].='.jpg';
                }
                $resultTemp[$key]['avatar'] = C('oss_path').$val['avatar'];
            } else {
                if ($val['sex'] == '男' || empty($val['sex'])) {
                    $resultTemp[$key]['avatar'] = 'http://'.WEB_URL.'/Public/img/classManage/student_m.png';
                } else {
                    $resultTemp[$key]['avatar'] = 'http://'.WEB_URL.'/Public/img/classManage/student_w.png';
                }
            }
            if($val['sid'] == $userId){  //如果自己的作业被批改，则显示额外输出自己的结果
                $result['myResult'] = $val;
            }
        }
        $convertArray = array(
            array('id' => TYPE_FIELD), array('id'),
            array('avatar' => TYPE_FIELD), array('avatar'),
            array('sex' => TYPE_FIELD), array('sex'),
            array('student_name' => TYPE_FIELD), array('name'),
            array('total_score' => TYPE_FIELD), array('content1'),
            array('create_at' => TYPE_FIELD, '提交' => TYPE_STRING), array('content2'),
        );

        $result['data'] = fieldsCompose($resultTemp, $convertArray);
        if(!empty($result['myResult'])){
            $result['myResult'] = fieldsCompose([$result['myResult']], $convertArray);
        }
        $totalStudent = D('Biz_class_student')->getStudentCount($classId);
        $result['totalStudentCount'] = $totalStudent;
        $this->showMessage(200, 'success', $result);
    }

    /**
     * @描述：获取已提交作业的教师评语
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：homeworkId[int] Y 作业ID
     * @参数：classId[int] Y 班级ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getHomeworkComment()
    {
        $userId = session('student.id');
        $role = ROLE_STUDENT;
        $homeworkId = getParameter('homeworkId', 'int');
        $classId = getParameter('classId', 'int');
        $submitId = D('Exercises_student_homework')->getSubmitIdByHomeworkIdClassIdStudentId($userId,$homeworkId,$classId);
        if(empty($submitId)){
            $this->showMessage(200,'success',[]);
        }
        $result = D('Exercises_homework_comment')->getComments($submitId,0);
        $this->showMessage(200,'success',$result);

    }

    /**
     * @描述：获取某道题的评语
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：submitExerciseId[int] Y 习题提交ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getExerciseComment()
    {
        $userId = session('student.id');
        $role = ROLE_STUDENT;
        $submitExerciseId = getParameter('submitExerciseId', 'int');
        $result = D('Exercises_homework_comment')->getComments(0,$submitExerciseId);
        $this->showMessage(200,'success',$result);

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
        //load normal category from database
        $normalCategory = D('Exercises_Course')->getAllTopicIdName();

        $newNormalCategory = [];
        foreach($normalCategory as $key=>$val){
            $newNormalCategory[$val['id']] = $val;
        }
        $categoryConfig['1']['data'] = $newNormalCategory;
        foreach($result as $key=>$val)
        {
            list($mainTempCategory,$subTempCategory) = explode(',',$val['category']);
            if($mainTempCategory == 1){
                $val['category'] = $mainTempCategory.','.$val['home_topic_type'];
            }
            if($category != $val['category'])
            {
                list($mainCategory,$subCategory) = explode(',',$val['category']);

                if(empty($mainCategory) || empty($subCategory))
                    return array();
                if(!empty($sub2))
                {
                    if($lastSubMainCategory != 0) {
                        $sub3[] = array('name' => $categoryConfig[$lastMainCategory]['data'][$lastSubMainCategory]['name'],
                            'type'=> $val['topic_type'],
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
                                    'type'=> $val['topic_type'],
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
            $lastTopicType = $val['topic_type'];
        }
        if(!empty($sub2) && !empty($mainCategory))
            $sub3[] = array('name'=>$categoryConfig[$mainCategory]['data'][$subCategory]['name'],
                'type'=> $lastTopicType,
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

    /**
     * @描述：查看未做作业 已过期作业详情
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
        $result = array();
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $homeworkId = getParameter('homeworkId','int');
        $resultData = D('Exercises_student_homework')->getHomeworkExerciseList($homeworkId);
        $result['total'] = count($resultData);
        foreach($resultData as $key => $val){
            $resultData[$key]['subList'] = [];
            if(!empty($val['sub_subject_name'])){ //复合题
                $subTypeArray = explode("///", $val['sub_topic_type']);
                $subSubjectNameArray = explode("///", $val['sub_subject_name']);
                $subAnswerArray = explode("///", $val['sub_answer']);
                foreach($subSubjectNameArray as $i => $name){
                    $resultData[$key]['subList'][] = ['name' => $name,'topic_type' => $subTypeArray[$i],'answer' => $subAnswerArray[$i]];
                }
            }
            unset($resultData[$key]['sub_topic_type']);
            unset($resultData[$key]['sub_subject_name']);
            unset($resultData[$key]['sub_answer']);
        }
        $result['data'] = $this->transExerciseData($resultData,'id,name,translation,url,score,answer,main_type,sub_type,answer,subject_name,subList,topic_type');
        $result['data'] = $this->flattenData($result['data'],3);
        $this->showMessage(200, $result['total'], $result['data']);
    }

    /**
     * @描述：学生提交作业
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：homeworkId[int] Y 作业ID
     * @参数：classId[int] Y 班级ID
     * @参数：duration[int] Y 耗时 (S)
     * @参数：answerList[str] Y 习题列表 {格式为JSON字符串,每个元素为JSON，格式为：{id:习题ID,answer:答案,score:习题得分,totalscore:习题总分}}
     *        例"[{id:1,answer:'testset',score:5.5,totalscore:10},{id:2,answer:'testset',score:5.5,totalscore:10}]" ,评分需要教师端或后台分数填-1
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */

    public function submitHomework()
    {
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $duration = getParameter('duration','int',false);
        $homeworkId = getParameter('homeworkId', 'int');
        $classId = getParameter('classId', 'int');
        $answerList = json_decode(htmlspecialchars_decode(getParameter('answerList','str')),true);
        $model = D('Exercises_student_homework');
        $result = true;
        //判断作业是否删除 已经截止 是否已经提交
        $homeworkInfo = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);
        if($homeworkInfo['is_delete'] == STATE_DELETED || empty($homeworkInfo))
            $this->showMessage( 500,'提交作业不存在',$result);
        $inClassStatus = D('Biz_class_student')->getStudentIsExistsInClass($userId,$classId);
        if(false === $inClassStatus)
            $this->showMessage( 500,'您不在该班级中,无法提交作业',$result);
        if(strtotime($homeworkInfo['deadline']) < time())
            $this->showMessage( 500,'作业提交已经截止,无法提交作业',$result);
        if(true === $model->getHomeworkIsSubmit($userId,$homeworkId,$classId))
            $this->showMessage( 500,'您已提交过该作业,无法再次提交',$result);
        if(empty($answerList))
            $this->showMessage( 500,'提交作业内容为空,无法提交',$result);
        //添加学生作业主表数据

        $mainData = array();
        $mainData['student_id'] = $userId;
        $mainData['work_id'] = $homeworkId;
        $mainData['work_timeout'] = $duration;
        $mainData['class_id'] = $classId;
        $mainData['work_status'] = STUDENT_HOMEWORK_STATUS_NORMAL;
        $mainData['is_delete'] = STATE_NORMAL;
        $mainData['correct_status'] = STUDENT_HOMEWORK_UNCORRECTED;
        $homeworkInfo = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);

        M()->startTrans();
        $submitId = $model->addInfo($mainData);
        if(!$submitId)
        {
            M()->rollback();
            $this->showMessage( 500,'提交作业失败',$result);
        }
        //更新完成数
        $updateCountResult = D('Exercises_homework_class_relation')->updateHomeworkFinishCount($homeworkId,$classId);
        if(!$updateCountResult)
        {
            M()->rollback();
            $this->showMessage( 500,'提交作业失败',$result);
        }
        //添加学生习题关联
        $subData = array();
        $subAllData = array();
        $subData['work_id'] = $submitId;
        $subData['student_id'] = $userId;
        $subData['class_id'] = $classId;
        $subData['homework_id'] = $homeworkId;
        foreach($answerList as $key=>$exerciseInfo)
        {
            $subData['exercises_id'] = $exerciseInfo['id'];
            $subData['exercises_score'] = empty($exerciseInfo['score'])? 0: $exerciseInfo['score'];
            $subData['answer'] = str_replace('https://','http://',$exerciseInfo['answer']);
            if(is_array(json_decode($subData['answer'],true))){
                $answerArray = json_decode($subData['answer'],true);
                $size = sizeof($answerArray);
                $subExerciseArray = [];
                for($i=0;$i<$size;$i++){
                    $subExerciseArray[] = 0;
                }
                $subData['subexercise_score'] = json_encode($subExerciseArray);
                $subData['subexercise_correct_status'] = json_encode($subExerciseArray);
            }
            else
            {
                unset($subData['subexercise_score']);
                unset($subData['subexercise_correct_status']);
            }
            $subData['total_score'] = $exerciseInfo['totalscore'];
            $subData['type'] = $exerciseInfo['type'];
            $subData['course_id'] = $homeworkInfo['course_id'];
            $subData['teacher_id'] = $homeworkInfo['create_user_id'];
            $subData['total_score'] = $exerciseInfo['totalscore'];
            $subData['is_ignore_statistic'] = $exerciseInfo['is_ignore_statistic'];
            $subData['total_score'] = $exerciseInfo['totalscore'];
            $subAllData[] = $subData;
        }

        $addResult = $model->addSubInfo($subAllData);
        if(!$addResult)
        {
            M()->rollback();
            $this->showMessage( 500,'提交作业失败',$result);
        }

        //客观题自动打分
        D('Exercises_student_homework')->autoCorrectHomework($submitId);
        //若所有题均已判分，则置标志位
        $score = D('Exercises_homework_class_relation')->getHomeworkIsAllCorrected($submitId);
        if($score !== false)
        {
            $updateResult = D('Exercises_homework_class_relation')->updateCorrectStatusPoint($submitId,$score,$homeworkInfo['create_user_id'],$homeworkInfo['create_user_name']);
            if(!$updateResult)
            {
                M()->rollback();
                $this->showMessage( 500,'提交作业失败',$result);
            }
        }
        M()->commit();
        $submitIdList = $model->getSubmitIdList($homeworkId,$classId);
        $index = $this->getSubmitCount($submitIdList,$submitId);

        $this->showMessage( 200,'提交成功',array('count'=>$index,'id'=>$submitId));
    }
    private function getSubmitCount($submitIdList,$submitId)
    {
        $index = 1;
        foreach($submitIdList as $key=>$val)
        {
            if($val['id'] == $submitId)
            {
                break;
            }
            else
                $index++;
        }
        return $index;
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
        if($userId != $info['student_id'])
        {
            $this->showMessage( 500,'您无法查看其它人的作业');
        }
        $resultData = $model->getHomeworkExerciseList(0,0,$id);

        $result['submittime'] = $info['submit_at'];
        $result['duration'] = $info['work_timeout'];
        $result['point'] = $info['total_score'];
        $result['totalpoint'] = $info['total_score_base'];
        $wrongNumber = 0;
        foreach($resultData as $key => $val){
            if($val['point'] != $val['total_point'])
                $wrongNumber ++;
            $resultData[$key]['subList'] = [];
            if(!empty($val['sub_subject_name'])){ //复合题
                $subTypeArray = explode("///", $val['sub_topic_type']);
                $subSubjectNameArray = explode("///", $val['sub_subject_name']);
                $subAnswerArray = explode("///", $val['sub_answer']);
                $subAnswerSelectArray = explode("///", $val['sub_answer_select']);

                foreach($subSubjectNameArray as $i => $name){
                    $resultData[$key]['subList'][] = ['name' => $name,'topic_type' => $subTypeArray[$i],'answer' => $subAnswerArray[$i],'answer_select'=>$subAnswerSelectArray[$i]];
                }
            }
            unset($resultData[$key]['sub_subject_name']);
            unset($resultData[$key]['sub_topic_type']);
            unset($resultData[$key]['sub_answer_select']);
            unset($resultData[$key]['sub_answer']);
        }
        $result['data'] = $this->transExerciseData($resultData,'name,translation,url,point,is_correct,total_point,category,org_answer_url,subject_name,answer,answer_select,student_answer,subList,topic_type,comment');
        $result['data'] = $this->flattenData($result['data'],$isFlatten);
        $result['wrongNum'] = $wrongNumber;
        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：学生收藏习题
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：exerciseId[int] Y 习题ID
     * @参数：isCancel[int] N 收藏/取消收藏标志位 0--收藏 1--取消收藏
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function favorExercise()
    {
        $isCancel = getParameter('isCancel','int',false);
        $exercise_id = getParameter('exerciseId','int'); //习题id
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');

        if ($isCancel == 1) {
            $result = D('Exercises_Collection')->deleteCollect($userId,$role,$exercise_id);
            if ($result) {
                $this->showMessage( 200,'success','取消成功');
            } else {
                $this->showMessage( 400,'success','取消失败');
            }
        } else {
            $exerciseInfo = D('Exercises_createexercise')->getExerciseInfo($exercise_id);
            if(empty($exerciseInfo)){
                $this->showMessage( 401,'success','习题不存在');
            }
            $result =D('Exercises_Collection')->addCollect($userId,$role,$exercise_id,$exerciseInfo['subject']);
            if ($result !== false) {
                $this->showMessage( 200,'success','收藏成功');
            } else {
                $this->showMessage( 400,'success','收藏失败');
            }
        }

    }

    /**
     * @描述：获取我收藏的习题学科及其数量信息
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getMyCollectedLibraryBaseInfo()
    {
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $result = D('Exercises_Collection')->getMyCollectCountInfo($userId,$role);
        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：获取我收藏的某学科的习题列表
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：courseId[int] Y 学科ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getMyCollectedLibraryIdList()
    {
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $courseId = getParameter('courseId','int');
        $result = D('Exercises_Collection')->getMyCollectExerciseList($userId,$role,$courseId);

        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：获取我的错题学科及其数量信息
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getMyWrongWorkBaseInfo()
    {
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $result = D('Exercises_wrong_exercise')->getStudentWrongExerciseCountInfo($userId);
        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：获取我的某学科的错题列表
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：courseId[int] Y 学科ID
     * @参数：pageIndex[int] Y 分页INDEX 从1开始
     * @参数：pageSize[int] N 分页大小 不传默认20
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getMyWrongExerciseList()
    {
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $courseId = getParameter('courseId','int');
        $pageIndex = getParameter('pageIndex','int');
        $pageSize = getParameter('pageSize','int',false);
        if(empty($pageSize))
            $pageSize = $this->pageSize;
        $result = D('Exercises_wrong_exercise')->getStudentWrongExerciseList($userId,$courseId,0,$pageIndex,$pageSize);
        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：删除错题
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：exerciseId[int] Y 习题ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function deleteWrongExercise()
    {
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $exerciseId = getParameter('exerciseId','int'); //习题id
        $result = D('Exercises_wrong_exercise')->clearWrongExercise($userId,$exerciseId);
        if($result)
            $this->showMessage(200,'success');
        else
            $this->showMessage(500,'删除失败');

    }

    /**
     * @描述：查看解析
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：submitId[int] Y 提交作业ID
     * @参数：type[int] Y 类型 1--错题 0--全部
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function viewAnalysis()
    {
        //复合题需要查询子题的解析
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $submitId = getParameter('submitId','int');
        $type = getParameter('type','int');
        if($type == 0) {
            $exerciseIds = D('Exercises_student_homework')->getExerciseIdBySubmitId($submitId);
        }
        else{
            $exerciseIds = D('Exercises_student_homework')->getWrongExerciseId($submitId);
        }
        if(empty($exerciseIds)){
            $this->showMessage( 400,'提交作业ID不存在');
        }
        $result = D('Exercises_homework_class_relation')->getAnalysis($exerciseIds);
        $this->showMessage( 200,'success',$result);

    }

    /**
     * @描述：开始练习错题
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：courseId[int] Y 学科ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function startWrongPractice()
    {
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $courseId = getParameter('courseId','int');
        if(empty($pageSize))
            $pageSize = $this->pageSize;
        $result = D('Exercises_wrong_exercise')->getStudentWrongExerciseList($userId,$courseId,1,-1,$pageSize);
        $this->showMessage( 200,'success',$result);
    }

    /**
     * @描述：错题练习次数加1
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：exerciseId[int] Y 习题ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function addPracticeCount()
    {
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $exerciseId = getParameter('exerciseId','int'); //习题id
        $result = D('Exercises_wrong_exercise')->addOnePracticeCount($userId,$exerciseId);
        if($result)
            $this->showMessage(200,'success');
        else
            $this->showMessage(500,'操作成功');
    }

    /**
     * @描述：获取提交单个习题的详情
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：submitExerciseId[int] Y 单个习题提交ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getSubmitExerciseInfo()
    {
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $id = getParameter('submitExerciseId','int'); //提交习题id
        $result = D('Exercises_student_homework')->getSubmitExerciseInfo($id);
        $this->showMessage(200,'success',$result);
    }

    /**
     * @描述：反查所有作业的学科
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getHomeworkAvailableCourse()
    {
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $condition['role'] = ROLE_STUDENT;
        $condition['userId'] = $userId;
        $result = D('Exercises_homework_class_relation')->getHomeworkCountGroupByCourse($condition);
        $this->showMessage(200,'success',$result);
    }

    //以下是田婉璐的地盘
    //学生错题库页面
    public function index(){
        $this->userId = I('userId');
        $this->role = I('role');
        $this->display();
    }

    //学生做作业页面
    public function doHomework(){
        $this->userId = I('userId');
        $this->role = I('role');
        $this->display();
    }

    //学生我的收藏
    public function myCollectionCourse(){
        $this->display();
    }
}
?>