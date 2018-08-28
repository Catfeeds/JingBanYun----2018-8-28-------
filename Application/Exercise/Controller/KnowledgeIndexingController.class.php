<?php
namespace Exercise\Controller;
use Think\Controller;
use Think\Exception;
use Think\Verify;

class KnowledgeIndexingController extends ExerciseGlobalController
{

    public $model;
    public $page_size=20;
    private $userInfo;
                //permissions
    public function __construct() {
        parent::__construct();
        $this->userInfo = $this->getUserRoleAuth();
    }

    public function knowledgeList(){
        $id = getParameter('id','int');
        $data = array();
        $header = array(
            array('name'=>'序号','field'=>'rownum','callback'=>function($value){return $value;}),
            array('name'=>'教材版本','field'=>'version_name','callback'=>function($value){return $value;}),
            array('name'=>'知识点','field'=>'knowledge_name'),
            array('name'=>'年级分册','field'=>'grade_name,school_term','callback'=>function($val){
                foreach(C('schoolTerm') as $index =>$data)
                {
                    if($val[1] == $data['id']) {
                        return $val[0].$data['name'];
                    }
                }
                return $val[0];
            }),
            array('name'=>'操作','field'=>'id','callback'=>function($value){return $value.',modify,delete';}),
        );

        $data['sEcho'] = getParameter('sEcho','str',false);
        $list =  D('Exercises_textbook_tree_info_createexercise')->getKnowledgeListByExerciseId($id);
        $count = sizeof($list);
        $list = $this->transData($header,$list);

        $data['iTotalRecords'] = $count;
        $data['iTotalDisplayRecords'] = $count;
        $data['aaData'] = array_column($list,'data');
        echo json_encode($data);

    }
    function exerciseListPage()
    {
        $data = array();
        $header = array(
            array('name'=>'序号','field'=>'rownum','callback'=>function($value){return $value;}),
            array('name'=>'试题ID','field'=>'id','callback'=>function($value){return $value;}),
            array('name'=>'题型','field'=>'topic_name'),
            array('name'=>'题目信息','field'=>'subject_name','callback'=>function($value){return $value;}),
            array('name'=>'试题状态','field'=>'status','callback'=>function($value){foreach(C('exerciseState') as $key=>$val){ if($val['id'] == $value) return $val['name'];}return '';}),
            array('name'=>'锁定状态','field'=>'is_lock','callback'=>function($value){if($value[0] != LOCKSTATE_NORMAL)return '是'; else return '否';}),
        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $export =  getParameter('export','str',false);
        if($export)
            $condition = json_decode(htmlspecialchars_decode(getParameter('formCondition','str',false)),true);
        else
            $condition = array(
                'questionId'  =>   getParameter('questionId','int',false),
                'paperId'  =>   getParameter('paperId','int',false),
                'exerciseCategory' => getParameter('exerciseCategory','int',false),
                'paperName'=> getParameter('paperName','str',false),
                'keyword' => getParameter('keyword','str',false),
                'assignStartTime' => getParameter('assignStartTime','str',false),
                'assignEndTime' => getParameter('assignEndTime','str',false),
                'lock'  =>   getParameter('lock','int',false),
                'startIndex' =>  getParameter('startIndex','int',false),
                'pageSize' =>  getParameter('pageSize','int',false),
            );
        $userInfo = D('Exercises_account')->getUserInfo($this->userInfo['id']);
        $condition['courseId'] = $userInfo['course_id'];
        $condition['is_self'] = 1;
        $condition['status'] =getParameter('status'  ,'int',false)===false ? array('in',EXERCISE_STATE_WAITMARK.','.EXERCISE_STATE_FINISH) : getParameter('status'  ,'int',false);
        $count =  D('Exercises_question_processinfo')->getQuestionCount($this->userInfo['id'],ROLE_MARKKNOWLEDGE,$condition);
        $list =  D('Exercises_question_processinfo')->getQuestionList($this->userInfo['id'],ROLE_MARKKNOWLEDGE,$condition,'status');

        //transform data
        if (!empty($list)) {
            foreach ($list as $k=>$v) {
                $list[$k]['subject_name'] = html_entity_decode($v['subject_name']);
            }
        }

        $list = $this->transData($header,$list);
        if($export)
        {
            $fileName=date('Ymd').rand(0,1000).'.csv';
            $this->exportCSV($fileName,$header,array_column($list,'data'));
            exit;
        }
        $data['iTotalRecords'] = $count;
        $data['iTotalDisplayRecords'] = $count;
        $data['aaData'] = array_column($list,'data');
        echo json_encode($data);
    }
    function knowledgeMgmt(){
        $this->assign('parent', '知识标引管理');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '');
        $userInfo = D('Exercises_account')->getUserInfo($this->userInfo['id']);
        $courseId = $userInfo['course_id'];
        $exerciseCategoryList = D('Exercises_Course')->getCourseList($courseId);
        $versionData = D('Exercises_textbook_version')->getAllVersions();
        $difficulty = C('difficulty');
        $this->assign('difficultyList',$difficulty);
        $this->assign('versionData',$versionData);
        $this->assign('courseId',$courseId);
        $this->assign('exerciseCategory',$exerciseCategoryList);
        $this->display();
    }
    function queryGradeByKeywordAndVersion()
    {
        $versionId = getParameter('versionId','int');
        $keyword = addslashes($_REQUEST['keyword']);
        $courseId = $this->userInfo['courseId'];
        $result = D('Exercises_textbook_tree_info')->getGradeTermByVersionAndKeyword($versionId,$keyword,$courseId);
        foreach($result as $key => $val)
        {
            foreach(C('schoolTerm') as $index =>$data)
            {
                if($val['school_term'] == $data['id']) {
                    $result[$key]['name'] .= $data['name'];
                    break;
                }
            }
        }
        $this->showMessage(200,'success',$result);
    }
    public function queryChapterByKeywordAndTree()
    {
        $treeId = getParameter('treeId','int');
        $keyword = getParameter('keyword','str');
        $result = D('Exercises_textbook_tree_info')->getChapterByKeywordAndTree($treeId,$keyword);
        $this->showMessage(200,'success',$result);
    }
    public function queryFestivalByKeywordAndChapter()
    {
        $chapterId = getParameter('chapterId','int');
        $keyword = getParameter('keyword','str');
        $result = D('Exercises_textbook_tree_info')->getFestivalByKeywordAndChapter($chapterId,$keyword);
        $this->showMessage(200,'success',$result);
    }
    private function _judgeQuestionKnowledgeCanModify($questionId)
    {
        $questionInfo = D('Exercises_question_processinfo')->getQuestionInfo($questionId);
        if ($questionInfo['is_delete'] == STATE_DELETED) {
            $this->showMessage(500, "ID:$questionId" . '习题已被删除');
        }
        if ($questionInfo['is_lock'] == LOCKSTATE_LOCK) {
            $this->showMessage(500, "ID:$questionId" . '习题被锁定');
        }
        if($questionInfo['status'] != EXERCISE_STATE_WAITMARK && $questionInfo['status'] != EXERCISE_STATE_FINISH){
            $this->showMessage(500, "ID:$questionId" . '习题当前状态不允许修改知识点');
        }
    }
    public function saveDifficulty()
    {
        $difficulty = getParameter('difficulty','int');
        $id = getParameter('id','int');
        $this->_judgeQuestionKnowledgeCanModify($id);

        $result = D('Exercises_createexercise')->setDifficulty($id,$difficulty);


        if($result)
            $this->showMessage(200,'success');
        else
            $this->showMessage(500,'保存失败');
    }

    public function getExerciseDifficulty()
    {
        $id = getParameter('id','int');
        $result = D('Exercises_createexercise')->getExerciseDifficulty($id);
        $this->showMessage(200,$result);
    }

    public function getExerciseKnowledge()
    {
        $qId = getParameter('questionId','int');
        $id = getParameter('id','int');
        $result =  D('Exercises_textbook_tree_info_createexercise')->getKnowledgeListByExerciseId($qId,$id);
        $this->showMessage(200,'success',$result[0]);
    }

    public function saveExerciseKnowledge()
    {
        $qId = getParameter('questionId','int');
        $id = getParameter('id','int',false);
        $versionId = getParameter('versionId','int');
        $gradeTerm = getParameter('gradeTerm','int');
        $chapter = getParameter('chapter','int');
        $knowledgeId = getParameter('knowledgeId','int');
        $festival = getParameter('festival','int',false);

        $this->_judgeQuestionKnowledgeCanModify($qId);

        $data['versionId'] = $versionId;
        $data['gradeTerm'] = $gradeTerm;
        $data['chapter'] = $chapter;
        $data['festival'] = $festival;
        $data['knowledge_id'] = $knowledgeId;
        $data['knowledge_name'] = D('Exercises_textbook_tree_info')->getKnowledgeNameByKnowledgeId($knowledgeId);
        $data['version_name'] = D('Exercises_textbook_version')->getInfo($versionId)[0]['version_name'];
        $data['chapter_name'] = D('Exercises_textbook_tree_info')->getKnowledgeNameByKnowledgeId($chapter);
        $data['festival_name'] =D('Exercises_textbook_tree_info')->getKnowledgeNameByKnowledgeId($festival);
        $treeInfo = D('Exercises_textbook_tree_breviary')->getTreeInfoByTreeId($gradeTerm);
        $data['section_id'] = $treeInfo['school_term'];

        foreach(C('schoolTerm') as $index =>$dataV)
        {
            if($treeInfo['school_term'] == $dataV['id']) {
                $data['section_name'] = $dataV['name'];
            }
        }
        $data['difficulty'] = 0;
        M()->startTrans();

        $clientIP = get_client_ip();
        $logModel = D('Exercises_log');
        if(0 == $id) //add
        {
            try
             {
                $setResult = D('Exercises_textbook_tree_info_createexercise')->addKnowledge($qId, $data);
             }
            catch(Exception $e)
            {
                $setResult = false;
            }
            if($setResult)
                $setResult &=$logModel->insertLog($qId,0,'增加知识点',$clientIP,$this->userInfo['id'],$this->userInfo['name'],'',0,'',BEHAVIOR_NORMAL);
            if(!$setResult)
            {
                M()->rollback();
                $this->showMessage(500, "ID:$qId".'习题增加知识点失败,您可能添加了相同的知识点');
            }
        }
        else //update
        {
            $setResult = D('Exercises_textbook_tree_info_createexercise')->editKnowledge($id,$data);
            if($setResult)
                $setResult &=$logModel->insertLog($qId,0,'编辑知识点',$clientIP,$this->userInfo['id'],$this->userInfo['name'],'',0,'',BEHAVIOR_NORMAL);
            if(!$setResult)
            {
                M()->rollback();
                $this->showMessage(500, "ID:$qId".'习题编辑知识点失败');
            }
        }
        M()->commit();
        $this->showMessage(200,'success');
    }

    function deleteKnowledge()
    {
        $id = getParameter('ids','int',false);
        $questionId = D('Exercises_textbook_tree_info_createexercise')->getExerciseIdByPrimaryKey($id);

        $this->_judgeQuestionKnowledgeCanModify($questionId);

        M()->startTrans();
        $clientIP = get_client_ip();
        $logModel = D('Exercises_log');

        if(empty($questionId))
            $this->showMessage(500, '该知识点已经被删除，删除失败');
        $setResult = D('Exercises_textbook_tree_info_createexercise')->deleteKnowledge($id);
        if($setResult)
            $setResult &=$logModel->insertLog($questionId,0,'删除知识点',$clientIP,$this->userInfo['id'],$this->userInfo['name'],'',0,'',BEHAVIOR_NORMAL);
        if(!$setResult)
        {
            M()->rollback();
            $this->showMessage(500, "ID:$id".'习题删除知识点失败');
        }

        M()->commit();
        $this->showMessage(200,'success');
    }

    public function getUnMarkCount()
    {
        $condition['status'] = EXERCISE_STATE_WAITMARK;
        $condition['is_self'] = 1;
        $count =  D('Exercises_question_processinfo')->getQuestionCount($this->userInfo['id'],ROLE_MARKKNOWLEDGE,$condition);
        $this->showMessage(200, $count);
    }

    public function getNextUnMarkExerciseId()
    {
        $condition['status'] = EXERCISE_STATE_WAITMARK;
        $condition['is_self'] = 1;
        $condition['startIndex'] = 0;
        $condition['pageSize'] = 1;
        $data =  D('Exercises_question_processinfo')->getQuestionList($this->userInfo['id'],ROLE_MARKKNOWLEDGE,$condition);
        $this->showMessage(200, '',$data);

    }

    public function knowledgeWordList(){
        $versionId = getParameter('versionId','int');
        $keyword = getParameter('keyword','str');
        $courseId = $this->userInfo['courseId'];
        $result = D('Exercises_textbook_tree_info')->getNameListByKeywordVersion($keyword,$versionId,$courseId);
        foreach($result as $key=>$val)
        {
            $result[$key]['title'] = stripslashes(strip_tags($val['title'])) ;
        }
        echo json_encode(array('data' => $result));
    }
}
