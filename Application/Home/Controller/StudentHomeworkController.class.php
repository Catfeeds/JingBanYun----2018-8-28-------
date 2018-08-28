<?php
namespace Home\Controller;
use Common\Common\simple_html_dom;
use Common\Common\BaiduVoice;
class StudentHomeworkController extends PublicController{
    private $exerciseConfig;
	public function __construct(){
        $this->exerciseConfig = require($_SERVER['DOCUMENT_ROOT'].'/Application/Exercise/Conf/createExercise.php');
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
        $this->assign('navicon', 'zuoyexitong');
        require($_SERVER['DOCUMENT_ROOT'].'/Application/Exercise/Conf/const.php');
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
        $userId = session('student.id');
        $role = ROLE_STUDENT;
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
        $userId = session('student.id');
        $role = ROLE_STUDENT;
        $courseId = getParameter('courseId','int',false);
        $pageIndex = getParameter('pageIndex','int',false);
        if(empty($pageIndex))
            $pageIndex = -1;
        $pageSize = getParameter('pageSize','int',false);
        if(empty($pageSize))
            $pageSize = 20;
        $result = D('Exercises_Collection')->getMyCollectExerciseList($userId,$role,$courseId,$pageIndex,$pageSize);
        $this->transDifficulty($result);
        //add title to exercise
            foreach ($result as $subKey => $subVal) {
                    $result[$subKey]['title_name'] = $subVal['name'];
            }
        $this->__exerciseHTMLRectify($result);
        $this->showMessage( 200,'success',$result);
    }

    private function __exerciseHTMLRectify(&$result)
    {
        $result = array_map(function ($record) {
            foreach ($record as $fieldName => $fieldValue) {
                if($fieldValue === null)
                    continue;
                $html = htmlspecialchars_decode($fieldValue);
                $htmlStr = stripcslashes($this->__decodeUnicode($html));
                $htmlStr = str_replace('ㄖ', '&nbsp;__________&nbsp; ', $htmlStr);

                $record[$fieldName] = str_replace("\n", '', str_replace("\t", '', $htmlStr));
                if ($fieldName == 'json_html') {
                    $record[$fieldName] = substr($record[$fieldName], 1, -1);
                    $html = new simple_html_dom();
                    $html->load($record[$fieldName]);
                    do {
                        $preHTML = $html->find('pre', 0);
                        if ($preHTML != null)
                            $preHTML->tag = 'p';
                    } while ($preHTML != null);
                    $record[$fieldName] = $html->save();

                    if($record['topic_type'] == 3) //选择填空
                    {
                        $rightAnswerArray = [];
                        $startFindIndex = 0;
                        $blankCount = 0;
                        //获取答案
                        do {
                            $answerHTML = $html->find('.claimChoice',0)->find('li p',$startFindIndex++);
                            if ($answerHTML != null)
                                $rightAnswerArray[] = $answerHTML->innertext;
                        } while ($answerHTML != null);
                        //获取空数量
                        do {
                            $answerHTML = $html->find('.claimChoice',$blankCount);
                        } while ($answerHTML != null&&($blankCount++));

                        $record['answerList'] = $rightAnswerArray;
                        $record['blankCount'] = $blankCount;
                    }
                }
                else if($fieldName == 'answer'){
                    if($record['topic_type'] == 1) {
                        $answerValue = json_decode($fieldValue, true);
                        foreach ($answerValue as $answerIndex => $answer) {
                            $answerValue[$answerIndex] = stripcslashes($this->__decodeUnicode(htmlspecialchars_decode($answer)));
                        }
                    }else if($record['topic_type'] == 2 || $record['topic_type'] == 3 ){
                        $answerValue = json_decode($fieldValue, true);
                        foreach ($answerValue as $answerIndex => $answer) {
                            $htmlStr  = stripcslashes($this->__decodeUnicode(htmlspecialchars_decode($answer)));
                            $html = new simple_html_dom();
                            $html->load($htmlStr);
                            $answerValue[$answerIndex] = $html->find('p',0)->innertext;
                        }
                        $answerValue = implode(',',$answerValue);
                    }
                    $record[$fieldName] = $answerValue;

                }
            }
            return $record;
        }, $result);
    }
    private function __decodeUnicode($str)
    {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
            create_function(
                '$matches',
                'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
            ),
            $str);
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
        $exercise_id = getParameter('exercise_id','int'); //习题id
        $userId = session('student.id');
        $role = ROLE_STUDENT;

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
        $userId = session('student.id');
        $role = ROLE_STUDENT;
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
        $userId = session('student.id');
        $role = ROLE_STUDENT;
        $courseId = getParameter('courseId','int');
        $pageIndex = getParameter('pageIndex','int',false);
        if(empty($pageIndex))
            $pageIndex = -1;
        $pageSize = getParameter('pageSize','int',false);
        if(empty($pageSize))
            $pageSize = 20;
        $result = D('Exercises_wrong_exercise')->getStudentWrongExerciseList($userId,$courseId,0,$pageIndex,$pageSize);
        //add is Objective info
        foreach($result as $key=>$val){
            $result[$key]['isObjective'] = $val['topic_type'] == 1 || $val['topic_type'] == 3 ||$val['topic_type'] == 4;
        }

        $this->transDifficulty($result);
        $this->__exerciseHTMLRectify($result);
        $this->showMessage( 200,'success',$result);
    }

    private function transDifficulty(&$data)
    {
        $difficultyList = $this->exerciseConfig['difficulty'];
        foreach($data as $key=>$val) {
            foreach ($difficultyList as $key2 => $val2) {
                if ($val['difficulty'] == $val2['id'])
                    $data[$key]['difficulty'] = $val2['name'];
            }
        }
    }

    private function __getWeek($date)
    {
            //强制转换日期格式
            $date_str=date('Y-m-d',strtotime($date));
            //封装成数组
            $arr=explode("-", $date_str);
            //参数赋值
            //年
            $year=$arr[0];
            //月，输出2位整型，不够2位右对齐
            $month=sprintf('%02d',$arr[1]);
            //日，输出2位整型，不够2位右对齐
            $day=sprintf('%02d',$arr[2]);
            //时分秒默认赋值为0；
            $hour = $minute = $second = 0;
            //转换成时间戳
            $strap = mktime($hour,$minute,$second,$month,$day,$year);
            //获取数字型星期几
            $number_wk=date("w",$strap);
            //自定义星期数组
            $weekArr=array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
            //获取数字对应的星期
            return $weekArr[$number_wk];
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
        if(empty($userId))
         $userId = getParameter('userId','int',false);
        $role = ROLE_STUDENT;
        $pageIndex = getParameter('pageIndex','int',false);
        $pageSize = getParameter('pageSize','int',false);
        $courseId = getParameter('courseId', 'int',false);
        $classId = getParameter('classId','int',false);
        $status = getParameter('status','int',false);


        if (empty($pageIndex))
            $pageIndex = 1;
        if (empty($pageSize))
            $pageSize = -1;
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
        $subResult2 = [];
        foreach ($result as $key => $val) {
            $subResult = array();
            $subResult2 = [];
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
            $statusList =  explode("///", $val['correct_info_num']);
            $durationList =  explode("///", $val['duration']);
            $wrongNumList =  explode("///", $val['wrong_num']);

            foreach ($classNameList as $i => $nameValue) {
                $subResult['homeworkId'] = $homeworkIdList[$i];
                $subResult['classId'] = $classIdList[$i];
                $subResult['submitId'] = $studentHomeworkIdList[$i];
                $subResult['content1'] = $nameList[$i];
                $subResult['content2'] = $scoreList[$i];
                $subResult['content3'] = '截止时间: '.$deadLineList[$i];
                $subResult['content4'] = $correctInfoList[$i];
                $subResult['content5'] = $totalExerciseCountList[$i];
                $subResult['content6'] = $teacherNameList[$i]. '老师布置';
                $subResult['content7'] = $totalScoreList[$i];
                $subResult['content8'] = $finishCountList[$i].' 人已提交作业';
                $subResult['content9'] = $courseNameList[$i];
                $subResult['wrongNum'] = $wrongNumList[$i];
                $subResult['status'] = $statusList[$i];
                $subResult['duration'] = sprintf("%02d",($durationList[$i]/60)).':'.sprintf("%02d",($durationList[$i]%60));
                $subResult2 = $subResult;
            }
            $day = $this->__getWeek($val['release_time']);
            $subResult2['date'] = $val['date'].$day;
            $newResult[] = $subResult2;
        }
        $result = $newResult;
        $this->showMessage(200, 'success', $result);
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
        if (empty($pageIndex))
            $pageIndex = 1;
        if (empty($pageSize))
            $pageSize = -1;
        //get my classes
        $classList = D('Biz_class_student')->getStudentClass($userId);
        $classIds = implode(',',array_column($classList,'class_id'));
        $condition['hasSubmited'] = false;
        $condition['role'] = ROLE_STUDENT;
        $condition['userId'] = $userId;
        $condition['classId'] = $classIds;
        $condition['studentCanSee'] = 1;
        $condition['type'] = HOMEWORK_PUBLISHED;
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
            $orgDeadLine = explode("///", $val['org_deadline']);
            foreach ($classNameList as $i => $nameValue) {
                $subResult['homeworkId'] = $homeworkIdList[$i];
                $subResult['classId'] = $classIdList[$i];
                $subResult['content1'] = $nameList[$i];
                $subResult['content2'] = $courseList[$i];
                $subResult['content3'] = $deadLineList[$i];
                $subResult['content4'] = $totalExerciseCountList[$i];
                $subResult['content5'] = $teacherNameList[$i] . '老师布置';
                $subResult['content6'] = $totalScoreList[$i];
                $subResult['content7'] = $finishCountList[$i].' 人已提交作业';
                $subResult['content9'] = $totalScoreList[$i];
                $subResult['hasPassed'] = strtotime($orgDeadLine[$i]) < time() ? 1 : 0;
            }
            $subResult['date'] = $val['date'];
            $newResult[] = $subResult;
        }
        $result = $newResult;
        $this->showMessage(200, 'success', $result);
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
        $userId = session('student.id');
        if(empty($userId))
            $userId = getParameter('userId','int',false);
        $role = ROLE_STUDENT;
        $condition['role'] = ROLE_STUDENT;
        $condition['userId'] = $userId;
        $result = D('Exercises_homework_class_relation')->getHomeworkCountGroupByCourse($condition);
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
        $lastTopicType = 0;
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
                            'type'=> $lastTopicType,
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
                                    'type'=> $lastTopicType,
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
            $lastTopicType = $val['home_topic_type'];
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
    private function __processResult(&$result,$normalIndex,&$newResult = [])
    {
        foreach ($result['data'][$normalIndex]['data'] as $key => $val) {
            if (1) {
                foreach ($val['data'] as $subKey => $subVal) {

                    foreach ($subVal as $fieldName => $fieldValue) {
                        $html = htmlspecialchars_decode($fieldValue);
                        $htmlStr = stripcslashes($this->__decodeUnicode($html));
                        $htmlStr = str_replace('ㄖ', '&nbsp;__________&nbsp; ', $htmlStr);

                        $val['data'][$subKey][$fieldName] = str_replace("\n", '', str_replace("\t", '', $htmlStr));
                        if ($fieldName == 'json_html') {
                            $val['data'][$subKey][$fieldName] = substr($val['data'][$subKey][$fieldName], 1, -1);
                            $html = new simple_html_dom();
                            $html->load($val['data'][$subKey][$fieldName]);
                            do {
                                $preHTML = $html->find('pre', 0);
                                if ($preHTML != null)
                                    $preHTML->tag = 'p';
                            } while ($preHTML != null);
                            $val['data'][$subKey][$fieldName] = $html->save();
                            if($val['data'][$subKey]['topic_type'] == 3) //选择填空
                            {
                                $rightAnswerArray = [];
                                $startFindIndex = 0;
                                $blankCount = 0;
                                //获取答案
                                do {
                                    $answerHTML = $html->find('.claimChoice',0)->find('li p',$startFindIndex++);
                                    if ($answerHTML != null)
                                        $rightAnswerArray[] = $answerHTML->innertext;
                                } while ($answerHTML != null);
                                //获取空数量
                                do {
                                    $answerHTML = $html->find('.answerBox',$blankCount);
                                    if($answerHTML){
                                        $blankCount++;
                                    }
                                } while ($answerHTML != null);

                                $val['data'][$subKey]['answerList'] = $rightAnswerArray;
                                $val['data'][$subKey]['blankCount'] = $blankCount;
                            }
                        }
                        else if($fieldName == 'answer'){
                            if($val['data'][$subKey]['topic_type'] == 1) {
                                $answerValue = json_decode($fieldValue, true);
                                foreach ($answerValue as $answerIndex => $answer) {
                                    $answerValue[$answerIndex] = stripcslashes($this->__decodeUnicode(htmlspecialchars_decode($answer)));
                                }
                                $val['data'][$subKey][$fieldName] = $answerValue;
                            }else if($val['data'][$subKey]['topic_type'] == 2 || $val['data'][$subKey]['topic_type'] == 3){
                                $answerValue = json_decode($fieldValue, true);
                                foreach ($answerValue as $answerIndex => $answer) {
                                    $htmlStr  = stripcslashes($this->__decodeUnicode(htmlspecialchars_decode($answer)));
                                    $html = new simple_html_dom();
                                    $html->load($htmlStr);
                                    $answerValue[$answerIndex] = $html->find('p',0)->innertext;
                                    }
                                $answerValue = implode(',',$answerValue);
                                $val['data'][$subKey][$fieldName] = $answerValue;
                            }else if($val['data'][$subKey]['topic_type'] == 5 || $val['data'][$subKey]['topic_type'] == 6)
                            {
                                $htmlStr  = stripcslashes($this->__decodeUnicode(htmlspecialchars_decode($fieldValue)));
                                $html = new simple_html_dom();
                                $html->load($htmlStr);
                                $val['data'][$subKey][$fieldName] =  $html->find('p',0)->innertext;
                            }

                        }

                    }
                }
                if (empty($newResult[$val['type']]['data']))
                    $newResult[$val['type']]['data'] = $val['data'];
                else
                    $newResult[$val['type']]['data'] = array_merge($newResult[$val['type']]['data'], $val['data']);
                $newResult[$val['type']]['name'] = $val['name'];
            }
        }
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
        $userId = session('student.id');
        if(!$userId)
            $userId = 0;
        $role = ROLE_STUDENT;
        $id = getParameter('id', 'int');
        $model = D('Exercises_student_homework');
        $info = $model->getStudentHomeworkBriefInfo($id);
        if ($userId != $info['student_id']) {
            ;//$this->showMessage(500, '您无法查看其它人的作业');
        }
        $resultData = $model->getHomeworkExerciseList(0, 0, $id,0,$role,$userId);
        $comment = D('Exercises_homework_comment')->getComments($id);
        $result['total'] = count($resultData);
        $wrongNumber = 0;
        $unProcessNum = 0;
        foreach ($resultData as $key => $val) {
            $unProcessNum += $val['has_process'] == 0;
            if ($val['point'] != $val['total_point'] && $val['has_process'] == 1)
                $wrongNumber++;
            $resultData[$key]['subList'] = [];
            if (!empty($val['sub_subject_name'])) { //复合题
                $subTypeArray = explode("///", $val['sub_topic_type']);
                $subSubjectNameArray = explode("///", $val['sub_subject_name']);
                $subAnswerArray = explode("///", $val['sub_answer']);
                $subAnswerSelectArray = explode("///", $val['sub_answer_select']);

                foreach ($subSubjectNameArray as $i => $name) {
                    $resultData[$key]['subList'][] = ['name' => $name, 'topic_type' => $subTypeArray[$i], 'answer' => $subAnswerArray[$i], 'answer_select' => $subAnswerSelectArray[$i]];
                }
            }
            unset($resultData[$key]['sub_subject_name']);
            unset($resultData[$key]['sub_topic_type']);
            unset($resultData[$key]['sub_answer_select']);
            unset($resultData[$key]['sub_answer']);
        }
        $result['data'] = $this->transExerciseData($resultData, 'id,class_type,eid,has_process,name,translation,url,point,is_correct,total_point,category,org_answer_url,subject_name,answer,answer_select,student_answer,subList,topic_type,comment,json_html,home_topic_type');
        //add title to exercise
        foreach ($result['data'] as $index => $val) {
            foreach ($val['data'] as $subKey => $subVal) {
                foreach ($subVal['data'] as $subKey1 => $subVal1) {
                    $result['data'][$index]['data'][$subKey]['data'][$subKey1]['title_name'] = $subVal['name'];
                }
            }
        }

        $normalIndex = -1;
        if ($result['data'][0]['name'] == '普通习题') {
            $normalIndex = 0;
        } else if ($result['data'][1]['name'] == '普通习题') {
            $normalIndex = 1;
        }
        if ($normalIndex != -1) {
            $newResult = [];
            $this->__processResult($result,$normalIndex,$newResult);
            if ($normalIndex == 1) {
                foreach ($newResult as $key => $val) {
                    $result['data'][0]['data'][] = ['type' => $key, 'name' => $val['name'], 'data' => $val['data']];
                }
                unset($result['data'][1]);
            } else if ($normalIndex == 0) {
                $newResult2 = [];
                foreach ($newResult as $key => $val) {
                    $newResult2[] = ['type' => $key, 'name' => $val['name'], 'data' => $val['data']];
                }
                $result['data'][0]['data'] = $newResult2;
            }
        }
        $result = array_column($result['data'], 'data');
        $newResult = [];
        foreach ($result as $key => $val)
        {
            $newResult = array_merge($newResult,$val);
        }
        $allComment = '';
        foreach($comment as $key => $val){
            $allComment  .= htmlspecialchars_decode($val['comment']);
        }
        $this->showMessage(200,'success', $newResult,['comment'=>['comment'=>$allComment],'unProcessNum'=>$unProcessNum,'totalExercise'=>count($resultData),'wrongNum'=>$wrongNumber,'workName'=>$info['name'],'submittime'=>$info['submit_at'],'duration'=>$info['work_timeout'],'point'=>$info['total_score'],'totalpoint'=>$info['total_score_base']]);

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
        $userId = session('student.id');
        $role = ROLE_STUDENT;
        $courseId = getParameter('courseId','int');
        if(empty($pageSize))
            $pageSize = $this->pageSize;
        //只取客观题
        $result = D('Exercises_wrong_exercise')->getStudentWrongExerciseList($userId,$courseId,1,-1,$pageSize);
        $resultCount = count($result);
        $result['data'] = $this->transExerciseData($result,'id,name,right_key,translation,url,score,answer,main_type,sub_type,answer,subject_name,subList,topic_type,json_html,home_topic_type');
        //add title to exercise
        $totalPoint = 0;
        foreach($result['data'] as $index=>$val) {
            foreach ($val['data'] as $subKey => $subVal) {
                foreach ($subVal['data'] as $subKey1 => $subVal1) {
                    $result['data'][$index]['data'][$subKey]['data'][$subKey1]['title_name'] = $subVal['name'];
                    $totalPoint += $subVal1['score'];
                }
            }
        }

        $normalIndex = -1;
        if ($result['data'][0]['name'] == '普通习题') {
            $normalIndex = 0;
        } else if ($result['data'][1]['name'] == '普通习题') {
            $normalIndex = 1;
        }
        if ($normalIndex != -1) {
            $newResult = [];
            $this->__processResult($result,$normalIndex,$newResult);
            if ($normalIndex == 1) {
                foreach ($newResult as $key => $val) {
                    $result['data'][0]['data'][] = ['type' => $key, 'name' => $val['name'], 'data' => $val['data']];
                }
                unset($result['data'][1]);
            } else if ($normalIndex == 0) {
                $newResult2 = [];
                foreach ($newResult as $key => $val) {
                    $newResult2[] = ['type' => $key, 'name' => $val['name'], 'data' => $val['data']];
                }
                $result['data'][0]['data'] = $newResult2;
            }
        }
        $result = array_column($result['data'], 'data');
        $newResult = [];
        foreach ($result as $key => $val)
        {
            $newResult = array_merge($newResult,$val);
        }
        $this->showMessage(200,$resultCount, $newResult,['workName'=>'错题练习','totalExercise'=>$resultCount,'totalpoint'=>$totalPoint]);
    }

    /**
     * @描述：错题练习次数加1
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：exerciseId[str] Y 习题ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function addPracticeCount()
    {
        $userId = session('student.id');
        $role = ROLE_STUDENT;
        $exerciseIds = getParameter('exerciseIds','str'); //习题id
        $result = D('Exercises_wrong_exercise')->addOnePracticeCount($userId,$exerciseIds);
        if($result)
            $this->showMessage(200,'success');
        else
            $this->showMessage(500,'操作成功');
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
        $userId = getParameter('userId','int');
        $role = ROLE_STUDENT;
        $homeworkId = getParameter('homeworkId', 'int',false);
        $classId = getParameter('classId', 'int',false);
        $submitId = getParameter('submitId', 'int',false);
        if(empty($submitId))
            $submitId = D('Exercises_student_homework')->getSubmitIdByHomeworkIdClassIdStudentId($userId,$homeworkId,$classId);
        $newResult = array();
        if(empty($submitId)) {
                $result = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);
                $newResult['name'] = $result['name'];
                $newResult['releaseTime'] = $result['release_time'];
                $newResult['endTime'] = $result['deadline'];
                $newResult['requirement'] = $result['jobsments'];
                $newResult['totalCount'] = $result['exercises_num'];
                $newResult['courseName'] = $result['course_name'];
                $newResult['statusText'] = strtotime($result['deadline']) > time() ? '未完成':'逾期未交';
        }
        else{
            $newResult = D('Exercises_student_homework')->getStudentHomeworkBriefInfo($submitId);
            $newResult['statusText'] = $newResult['correct_status'] == HOMEWORK_EXERCISE_CORRECTED ? '已完成':'待批改';
        }
        $this->showMessage(200, 'success', $newResult);
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
        $userId = session('student.id');
        $role = ROLE_STUDENT;
        $homeworkId = getParameter('homeworkId','int');

        $result = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);
        $workName = $result['name'];
        $totalPoint = $result['total_score'];
        $resultData = D('Exercises_student_homework')->getHomeworkExerciseList($homeworkId);

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
        $baseField = 'id,name,class_type,category,translation,url,score,answer,main_type,sub_type,answer,subject_name,subList,topic_type,json_html,home_topic_type,right_key';
        $result['data'] = $this->transExerciseData($resultData,$baseField);

        $normalIndex = -1;
        if ($result['data'][0]['name'] == '普通习题') {
            $normalIndex = 0;
        } else if ($result['data'][1]['name'] == '普通习题') {
            $normalIndex = 1;
        }
        if ($normalIndex != -1) {
            $newResult = [];
            $this->__processResult($result,$normalIndex,$newResult);
            if ($normalIndex == 1) {
                foreach ($newResult as $key => $val) {
                    $result['data'][0]['data'][] = ['type' => $key, 'name' => $val['name'], 'data' => $val['data']];
                }
                unset($result['data'][1]);
            } else if ($normalIndex == 0) {
                $newResult2 = [];
                foreach ($newResult as $key => $val) {
                    $newResult2[] = ['type' => $key, 'name' => $val['name'], 'data' => $val['data']];
                }
                $result['data'][0]['data'] = $newResult2;
            }
        }
        $result = array_column($result['data'], 'data');
        $newResult = [];
        foreach ($result as $key => $val)
        {
            $newResult = array_merge($newResult,$val);
        }
        $this->showMessage(200,count($resultData), $newResult,['workName'=>$workName,'totalExercise'=>count($resultData),'totalpoint'=>$totalPoint]);
    }

    private function random($length, $chars = '0123456789') {
        $hash = '';
        $max = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }

    //返回时间加随机数
    private function randomfilename(){
        $result = $this->random(10, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
        $result = date("Ymdhis").$result;
        return $result;
    }
    //上传图片
    public function uploadImage()
    {
        $url = 'Answers/'.getParameter('url','str');
        $imagePath = './tmp/'.$this->randomfilename().'.jpg';//图片保存路径
        $image = base64_decode($_POST['image']);
        $newFile = fopen($imagePath,'w');
        $fwriteRes = fwrite($newFile,$image);
        fclose($newFile);
        $_FILES['file']['tmp_name'] = $imagePath;
        $_FILES['file']['type'] = 'image/jpeg';
        $upload = new \Oss\Ossupload();// 实例化上传类
        $result=$upload->upload(3,$_FILES,6,$url); //1 pic 2//
        $returnArray = explode(",", $result[1]);
        $uploadOK = 1;
        for ($i = 0; $i < sizeof($returnArray); $i++) {
            if ($returnArray[$i] == "") {
                $uploadOK = 0;
                break;
            }
        }
        //TODO：通知对应浏览器更新

        if ($uploadOK == 1) {
            $this->showMessage(200,'success');
        } else {
            $this->showMessage(500,'failed');
        }

    }

    public function scavenging()
    {
        $exerciseId = getParameter('id','int');
        $url = getParameter('url','str');
        $this->url = $url;
        $result = D('Exercises_createexercise')->getExerciseInfo($exerciseId);
        $html = htmlspecialchars_decode($result['subject_name']);
        $htmlStr = stripcslashes($this->__decodeUnicode($html));
        $htmlStr = str_replace('ㄖ', '&nbsp;__________&nbsp; ', $htmlStr);
        $this->html = $htmlStr;
        $this->display();
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
        $userId = session('student.id');
        $role = ROLE_STUDENT;
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
//        if(strtotime($homeworkInfo['deadline']) < time())
//            $this->showMessage( 500,'作业提交已经截止,无法提交作业',$result);
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
            if(empty($exerciseInfo['id']))
            {
                M()->rollback();
                $this->showMessage( 500,'习题信息缺失,提交作业失败');
            }
            $subData['exercises_id'] = $exerciseInfo['id'];
            $subData['exercises_score'] = empty($exerciseInfo['score'])? 0: $exerciseInfo['score'];

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
            $subData['answer'] = $exerciseInfo['answer'];
            $subData['total_score'] = $exerciseInfo['totalscore'];
            $subData['course_id'] = $homeworkInfo['course_id'];
            $subData['teacher_id'] = $homeworkInfo['create_user_id'];
            $subData['total_score'] = $exerciseInfo['totalscore'];
            $subData['is_ignore_statistic'] = $exerciseInfo['is_ignore_statistic'];
            $subData['total_score'] = $exerciseInfo['totalscore'];
            //获取习题是主观还是客观
            $isSubjective = D('Exercises_createexercise')->getExerciseIsSubjective($exerciseInfo['id']);
            $subData['type'] = $isSubjective ? 1:2;
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
        //错题入库
        $wrongExerciseInfo = D('Exercises_student_homework')->getWrongExerciseIdSubject($submitId);
        foreach($wrongExerciseInfo as $key=>$val)
        {
            D('Exercises_wrong_exercise')->add($userId,$val['exercises_id'],$val['subject']);
        }
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
            $masterClass['work_id'] = $homeworkId;
            $masterClass['class_id'] = $classId;
            $classMasterId = M('exercises_homwork_class_relation')->where($masterClass)->setInc('correct_student_count',1);
        }
        M()->commit();
        $submitIdList = $model->getSubmitIdList($homeworkId,$classId);
        $index = $this->getSubmitCount($submitIdList,$submitId);

        $this->showMessage( 200,'提交成功',array('count'=>$index,'id'=>$submitId));
    }

    public function voiceCompare()
    {
        set_time_limit(0);
        $userId = session('student.id');
        if(empty($userId))
            $this->showMessage(500,'登录已失效!');
        $id = getParameter('id','int');
        $wavPath = './tmp/'.$this->randomfilename().'.wav';//保存路径
        move_uploaded_file($_FILES['file']['tmp_name'],$wavPath);

        $abp = new BaiduVoice\AipSpeech();
        $result = $abp->asr(file_get_contents($wavPath),'wav',16000,array('dev_pid' => 1737));
        $this->__judgeRegResult($result);
        //对比结果
        $info = D('Exercises_question_processinfo')->getQuestionInfo($id);
        $score = $this->__compareVoice($info['words'],$result['result'][0]);

        //转换MP3
        $mp3Path = './tmp/'.$this->randomfilename().'.mp3';
        $convertResult = $this->__convertMP3($wavPath,$mp3Path);
        if($convertResult === false)
            $this->showMessage(500,'MP3转换失败');

        //上传OSS

        $remoteMp3Path = 'Answers/voice/'.$userId.'/'.$this->randomfilename().'.mp3';//保存路径
        $uploadResult = $this->__uploadSingleFile($mp3Path,'audio/mp3',$remoteMp3Path);
        if($uploadResult === false)
            $this->showMessage(500,'OSS上传失败');

        //删除本地文件
        @unlink($mp3Path);
        @unlink($wavPath);
        $this->showMessage(200,'success',['text'=>$result['result'][0],'score'=>$score,'path'=>C('oss_path').$remoteMp3Path]);
    }

    private function __convertMP3($basePath,$outputPath)
    {
       if(get_client_ip() == "0.0.0.0"){
           exec('d:/bin/ffmpeg/ffmpeg -i '.$basePath . ' -f mp3 -acodec libmp3lame -y '.$outputPath);
       }
       else
       {
           exec('/usr/local/ffmpeg/bin/ffmpeg -i '.$basePath . ' -f mp3 -acodec libmp3lame -y '.$outputPath);
       }
       return file_exists($outputPath);
    }
    private function __calcDistance($arr1,$arr2)
    {
        $m = sizeof($arr1);
        $n = sizeof($arr2);
        $matrix = [];
        for ($i = 1; $i <= $n; $i++) {
            $matrix[$i][0] = $i;
        }
        for ($i = 1; $i < $m; $i++) {
            $matrix[0][$i] = $i;
        }
        for ($i = 1; $i <= $n; $i++) {
            $si = $arr1[$i - 1];
            for ($j = 1; $j <= $m; $j++) {
                $sj = $arr2[$j - 1];
                if ($si == $sj) {
                    $cost = 0;
                } else {
                    $cost = 1;
                }
                $above = $matrix[$i - 1][$j] + 1;
                $left = $matrix[$i][$j - 1] + 1;
                $diag = $matrix[$i - 1][$j - 1] + $cost;
                $matrix[$i][$j] = min($above, min($left, $diag));
            }
        }
        return 100.0 - 100.0 * $matrix[$n][$m] / sizeof($arr1);
    }
    private function __filterScore(&$score)
    {
        $realScore = min(intval($score),100);
        //再来次产品公式  （0-50 ： -0.0023*x*x+1.0645x+16.358+随机数（0-10）） （>50 :  -0.0023*x*x+1.0645x）
        $x = floatval($realScore);
        if ($realScore > 50) {
            $realScore =intval(-0.0044* $x * $x  + 0.9167*$x + 50.4+ rand(0,100)/10);
            if ($realScore>100) {
                $realScore = 100;
            }
        } else {
            $realScore =intval(-0.0044* $x * $x + 0.9167*$x + 50.4);

        }
        $score = $realScore;
    }
    private function __compareVoice($baseText,$userText)
    {
       //判断语音长度
        $baseText = str_replace([',','?','.','!','`','……'],'',$baseText);
        $userTextWordsArray = explode(' ',$userText);
        if(sizeof($userTextWordsArray) == 0)
        {
            $this->showMessage(500,'未识别到语语音');
        }
        $baseTextArray = explode(' ',$baseText);

        $counter = 0;
        //两者单词数相等
        if(sizeof($userTextWordsArray) == sizeof($baseTextArray)){
          foreach($userTextWordsArray as $index => $word)
          {
              $counter += $this->__calcDistance($baseTextArray[$index],$word);
          }
            $score = floatval($counter)/ sizeof($baseTextArray);
        }
        //单词数不等
        else{
            foreach($userTextWordsArray as $key => $val){
                if(in_array($val,$baseTextArray))
                    $counter ++;
            }
            $score = floatval($counter)*100 / sizeof($baseTextArray);
        }
       $this->__filterScore($score);
       return $score;
    }

    private function __judgeRegResult($result)
    {
        if($result['err_no'] != 0 ) //识别错误
        {
            $msg ='';
            switch($result['err_no']){
                case 3301:$msg = '音频质量过差';
                    break;
                case 3308:$msg = '音频过长';
                    break;
            }
            $this->showMessage(500,$msg);
        }
    }

    private function __uploadSingleFile($localPath,$type,$remotePath)
    {

        $_FILES['file']['tmp_name'] = $localPath;
        $_FILES['file']['type'] = $type;
        $upload = new \Oss\Ossupload();// 实例化上传类
        $result=$upload->upload(3,$_FILES,6,$remotePath); //1 pic 2//
        $returnArray = explode(",", $result[1]);
        $uploadOK = 1;
        for ($i = 0; $i < sizeof($returnArray); $i++) {
            if ($returnArray[$i] == "") {
                $uploadOK = 0;
                break;
            }
        }
        if ($uploadOK == 1) {
            return true;
        } else {
            return false;
        }
    }
}
