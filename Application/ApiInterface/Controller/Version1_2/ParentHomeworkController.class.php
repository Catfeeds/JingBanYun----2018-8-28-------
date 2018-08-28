<?php
namespace ApiInterface\Controller\Version1_2;


class ParentHomeworkController extends PublicController{
	public function __construct(){
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
    }
    
	public function homeworkList(){
        $role = getParameter('role','int');
        $userId = getParameter('userId','int');
        $studentId = getParameter('studentId','int');

        $this->role = $role;
        $this->userId = $userId;
        $this->studentId = $studentId;
        $pageIndex = getParameter('pageIndex','int',false);
        $pageSize = getParameter('pageSize','int',false);

        if (intval($pageIndex) < 1)
            $pageIndex = 1;
        if (empty($pageSize))
            $pageSize = 1000;
        //get my classes
        $classList = D('Biz_class_student')->getStudentClass($studentId);
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
            $finishCountList = explode("///", $val['finish_count']);
            $deadLineList = explode("///", $val['deadline']);
            $totalExerciseCountList = explode("///", $val['exercise_count']);
            $totalScoreList = explode("///", $val['student_score']);
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
                $subResult['content1'] = $classNameList[$i] . ' ('.$courseList[$i] .')';
                $subResult['content2'] = $statusList[$i] ;
                $subResult['content3'] = $nameList[$i]."(共$totalExerciseCountList[$i]题)";
                $subResult['content4'] = $totalScoreList[$i] ;
                $subResult['content5'] = $deadLineList[$i].'截止';
                $subResult['content6'] = $teacherNameList[$i] . '老师布置';
                $subResult1[] = $subResult;
            }
            $newResult[] = array('date' => $val['date'], 'data' => $subResult1);
            $subResult1 = array();
        }

        foreach ( $newResult as $k=>$v ){
            foreach ($v['data'] as $dk=>$dv) {

                if ($dv['submitId'] > 0) {
                    $newResult[$k]['data'][$dk]['is_commit_url'] = "/ApiInterface/Version1_2/ParentHomework/completionDetails?userId={$userId}&role={$role}&homeworkId={$dv['homeworkId']}&classId={$dv['classId']}&submitId={$dv['submitId']}";
                } else {
                    $newResult[$k]['data'][$dk]['is_commit_url'] = "/ApiInterface/Version1_2/ParentHomework/homeworkDetails?userId={$userId}&role={$role}&homeworkId={$dv['homeworkId']}&classId={$dv['classId']}&submitId={$dv['submitId']}";
                }

            }
        }

        $result = $newResult;

        $this->result = $result;
		$this->display();
	}

	public function completionDetails() {
        $userId = getParameter('userId','int',false);
        $role = getParameter('role','int',false);
        $id = getParameter('homeworkId','int',false);
        $classId = getParameter('classId', 'int',false);
        $submitId = getParameter('submitId', 'int',false);
        $this->userId = $userId;
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

        //print_r($result);die();

        $this->result = $result;
        $this->display();
    }

    public  function  homeworkSituation() {
        $userId = getParameter('userId','int',false);
        $role = getParameter('role','int',false);
        $id = getParameter('homeworkId','int',false);
        $classId = getParameter('classId', 'int',false);
        $this->userId = $userId;
        $this->role = $role;
        $this->homeworkId = $id;
        $this->classId = $classId;
        $result = array();
        $isFlatten = getParameter('isFlatten','int',false);
        $model = D('Exercises_student_homework');
        $info = $model->getStudentHomeworkBriefInfo($id);

        $resultData = $model->getHomeworkExerciseList(0,0,$id);
        $result['submittime'] = $info['submit_at'];
        $result['status'] = '已完成';
        $result['duration'] = $info['work_timeout'];
        $result['point'] = $info['total_score'];
        $result['totalpoint'] = $info['total_score_base'];
        $result['data'] = $this->transExerciseData($resultData,'name,url,point,category,org_answer_url');
        if(1 == $isFlatten) {
            $result['data'] = $this->flattenData($result['data']);
        }



        $this->display();
    }

    public function exerciseDetails() {

        $userId = getParameter('userId','int',false);
        $role = getParameter('role','int',false);
        $homeworkId = getParameter('id','int',false);
        $classId = getParameter('classId', 'int',false);
        $submitId = getParameter('submitId', 'int',false);
        $flag = getParameter('flag', 'int',false);
        $name = getParameter('name', 'str',false);

        $this->userId = $userId;
        $this->role = $role;
        $this->homeworkId = $homeworkId;
        $this->classId = $classId;
        $this->submitId = $submitId;
        $this->flag = $flag;
        $this->name = $name;

        $this->display();
    }

    //未做作业列表
    public function homeworkDetails() {

        $result = array();
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $homeworkId = getParameter('homeworkId','int');
        $isFlatten = getParameter('isFlatten','int',false);
        $classId = getParameter('classId', 'int');
        $submitId = getParameter('submitId', 'int',false);

        $resultData = D('Exercises_student_homework')->getHomeworkExerciseList($homeworkId);
        $result['total'] = count($resultData);
        $result['data'] = $this->transExerciseData($resultData,'id,name,translation,url,score');
        if(1 == $isFlatten) {
            $result['data'] = $this->flattenData($result['data']);
        }
        $this->userId = $userId;
        $this->role = $role;
        $this->homeworkId = $homeworkId;
        $this->classId = $classId;
        $this->submitId = $submitId;

        $this->result = $result;
        $this->display();
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
