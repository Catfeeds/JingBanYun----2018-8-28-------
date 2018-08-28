<?php
namespace Exercise\Controller;
use ApiInterface\Controller\GlobalController;
use Think\Controller;
use Think\Verify;

class IndexController extends ExerciseGlobalController
{
    public $model;
    public $page_size=20;
    private $questionInfoModel;
    private $paperInfoModel;
    private $userInfo;
                //permissions
    public function __construct() {
        parent::__construct();
        $this->questionInfoModel = D('Exercises_question_processinfo');
        $this->paperInfoModel = D('Exercises_paper_processinfo');
        $this->userInfo = $this->getUserRoleAuth();
    }

    private function getUnprocesedMatters()
    {
        $unProcessList = array();
        $condition = array();

        $condition['status'] = EXERCISE_STATE_WAITVERIFY;
        $condition['is_self'] = -1;
        if(in_array('CreateExercise/examinationQuestions',session('exercises_permissions'))) {
            $unProcessMattersCount = $this->questionInfoModel->getQuestionCount($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition);
            $unProcessList[] = array('name' => '道试题待校审', 'url' => '/index.php?m=Exercise&c=createExercise&a=exerciseEntering#eyJjYXQiOiIyIiwic3RhcnRJbmRleCI6MCwicGFnZVNpemUiOjEwfQ==', 'count' => $unProcessMattersCount);
        }
        if(in_array('CreateExercise/examinePaper',session('exercises_permissions'))) {
            $unProcessMattersCount = $this->paperInfoModel->getPaperCount($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition);
            $unProcessList[] = array('name' => '套试卷待校审', 'url' => '/index.php?m=Exercise&c=createExercise&a=testEntering#eyJjYXQiOiIyIiwic3RhcnRJbmRleCI6MCwicGFnZVNpemUiOjEwfQ==', 'count' => $unProcessMattersCount);
        }
        $condition['is_self'] = 1;
        $condition['status'] = EXERCISE_STATE_REPROCESS;
        if(in_array('CreateExercise/examinationQuestions',session('exercises_permissions'))) {
            $unProcessMattersCount = $this->questionInfoModel->getQuestionCount($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition);
            $unProcessList[] = array('name' => '道试题被返工待修改', 'url' => '/index.php?m=Exercise&c=createExercise&a=exerciseEntering#eyJjYXQiOiIzIiwic3RhcnRJbmRleCI6MCwicGFnZVNpemUiOjEwfQ==', 'count' => $unProcessMattersCount);
        }
        if(in_array('CreateExercise/examinePaper',session('exercises_permissions'))) {
            $unProcessMattersCount = $this->paperInfoModel->getPaperCount($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition);
            $unProcessList[] = array('name' => '套试卷被返工待修改', 'url' => '/index.php?m=Exercise&c=createExercise&a=testEntering#eyJjYXQiOiIzIiwic3RhcnRJbmRleCI6MCwicGFnZVNpemUiOjEwfQ==', 'count' => $unProcessMattersCount);
        }
        unset($condition['is_self']);

        $condition['status'] = array('in',EXERCISE_STATE_WAITASSIGN.','.EXERCISE_STATE_REASSIGN);
        if(in_array('ExerciseState/setExerciseStateEXERCISE_STATE_WAITMARK',session('exercises_permissions'))) {
            $unProcessMattersCount = $this->questionInfoModel->getQuestionCount($this->userInfo['id'], ROLE_ASSIGNEXERCISE, $condition);
            $unProcessList[] = array('name' => '道试题待指派', 'url' => '/index.php?m=Exercise&c=ExerciseIndexing&a=exerciseIndexingMgmt#eyJzdGFydEluZGV4IjowLCJwYWdlU2l6ZSI6MTB9', 'count' => $unProcessMattersCount);
        }
        $condition['is_self'] = 1;
        $condition['status'] = EXERCISE_STATE_WAITMARK;
        if(in_array('KnowledgeIndexing/knowledgeMgmt',session('exercises_permissions'))) {
            $unProcessMattersCount = $this->questionInfoModel->getQuestionCount($this->userInfo['id'], ROLE_MARKKNOWLEDGE, $condition);
            $unProcessList[] = array('name' => '道试题待标引知识点', 'url' => '/index.php?m=Exercise&c=KnowledgeIndexing&a=knowledgeMgmt#eyJzdGFydEluZGV4IjowLCJwYWdlU2l6ZSI6MTB9', 'count' => $unProcessMattersCount);
        }

        $condition['status'] = EXERCISE_STATE_FINISH;
        if(in_array('KnowledgeIndexing/knowledgeMgmt',session('exercises_permissions'))) {
            $unProcessMattersCount = $this->questionInfoModel->getQuestionCount($this->userInfo['id'], ROLE_MARKKNOWLEDGE, $condition);
            $unProcessList[] = array('name' => '道试题已完成知识标引待提交审核', 'url' => '/index.php?m=Exercise&c=KnowledgeIndexing&a=knowledgeMgmt#eyJzdGFydEluZGV4IjowLCJwYWdlU2l6ZSI6MTB9', 'count' => $unProcessMattersCount);
        }
        unset($condition['is_self']);
        $condition['status'] = EXERCISE_STATE_UNINBOUND;
        if(in_array('shi-ti-shen-he-guan-li-pi-liang-ru-ku-an-niu',session('exercises_permissions'))) {
            $unProcessMattersCount = $this->questionInfoModel->getQuestionCount($this->userInfo['id'], ROLE_MARKKNOWLEDGE, $condition);
            $unProcessList[] = array('name' => '道试题待审核入库', 'url' => '/index.php?m=Exercise&c=ExerciseCheck&a=exerciseCheckMgmt#eyJiTmVlZEtub3dsZWRnZSI6dHJ1ZSwic3RhcnRJbmRleCI6MCwicGFnZVNpemUiOjEwfQ==', 'count' => $unProcessMattersCount);
        }
        //check data is valid
        foreach($unProcessList as $key=>$val)
        {
            if(false === $val['count'] )
                return false;
        }
        return $unProcessList;
    }

    private function getProcessMatters($startTime=false,$endTime=false)
    {
        $processedMatters = array();
        $startTime = $startTime == '' ? false:$startTime;
        $endTime = $endTime == '' ? false:$endTime;
        $condition = array();
        $condition['is_self'] = 1;

        if($startTime)
        $condition['inputStartTime'] = $startTime;
        if($endTime)
        $condition['inputEndTime'] = $endTime;

        if(in_array('CreateExercise/createChoiceExercise',session('exercises_permissions')) || in_array('CreateExercise/editChoiceExercise',session('exercises_permissions')) || in_array('CreateExercise/deleteExercise',session('exercises_permissions'))) {
            $processMattersCount = $this->questionInfoModel->getQuestionCount($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition);
            $processedMatters[] = array('name' => '道试题提交', 'count' => $processMattersCount);
        }
        if(in_array('CreateExercise/createPaper',session('exercises_permissions')) || in_array('CreateExercise/ModifyPaper',session('exercises_permissions')) || in_array('CreateExercise/deletePaper',session('exercises_permissions'))) {
            $processMattersCount = $this->paperInfoModel->getPaperCount($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition);
            $processedMatters[] = array('name' => '套试卷提交', 'count' => $processMattersCount);
        }
        //校审
        $condition = array();
        $condition['is_self'] = 1;
        $condition['verifyStartTime'] = $startTime;
        $condition['verifyEndTime'] = $endTime;
        if(in_array('CreateExercise/examinationQuestions',session('exercises_permissions'))) {
            $processMattersCount = $this->questionInfoModel->getQuestionCount($this->userInfo['id'], ROLE_VERIFY, $condition);
            $processedMatters[] = array('name' => '道试题审核', 'count' => $processMattersCount);
        }
        if(in_array('CreateExercise/examinePaper',session('exercises_permissions'))) {
            $processMattersCount = $this->paperInfoModel->getPaperCount($this->userInfo['id'], ROLE_VERIFY, $condition);
            $processedMatters[] = array('name' => '套试卷审核', 'count' => $processMattersCount);
        }
        //返工修改
        $condition = array();
        $condition['is_self'] = 1;
        $condition['reprocessStartTime'] = $startTime;
        $condition['reprocessEndTime'] = $endTime;
        $condition['hasReprocessed'] = 1;
        if(in_array('CreateExercise/createChoiceExercise',session('exercises_permissions')) || in_array('CreateExercise/editChoiceExercise',session('exercises_permissions')) || in_array('CreateExercise/deleteExercise',session('exercises_permissions'))) {
            $processMattersCount = $this->questionInfoModel->getQuestionCount($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition);
            $processedMatters[] = array('name' => '道试题返工修改', 'count' => $processMattersCount);
        }

        if(in_array('CreateExercise/createPaper',session('exercises_permissions')) || in_array('CreateExercise/ModifyPaper',session('exercises_permissions')) || in_array('CreateExercise/deletePaper',session('exercises_permissions'))) {
            $processMattersCount = $this->paperInfoModel->getPaperCount($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition);
            $processedMatters[] = array('name' => '套试卷返工修改', 'count' => $processMattersCount);
        }
        //指派
        $condition = array();
        $condition['is_self'] = 1;
        $condition['assignStartTime'] = $startTime;
        $condition['assignEndTime'] = $endTime;
        $condition['status'] = array('gt',EXERCISE_STATE_WAITASSIGN);
        if(in_array('ExerciseState/setExerciseStateEXERCISE_STATE_WAITMARK',session('exercises_permissions'))) {
            $processMattersCount = $this->questionInfoModel->getQuestionCount($this->userInfo['id'], ROLE_ASSIGNEXERCISE, $condition);
            $processedMatters[] = array('name' => '道试题指派', 'count' => $processMattersCount);
        }
        $condition = array();
        $condition['is_self'] = 1;
        $condition['markStartTime'] = $startTime;
        $condition['markEndTime'] = $endTime;
        $condition['hasMark'] = 1;
        if(in_array('KnowledgeIndexing/knowledgeMgmt',session('exercises_permissions'))) {
            $processMattersCount = $this->questionInfoModel->getQuestionCount($this->userInfo['id'], ROLE_MARKKNOWLEDGE, $condition);
            $processedMatters[] = array('name' => '道试题标引', 'count' => $processMattersCount);
        }
        $condition = array();
        $condition['is_self'] = 1;
        $condition['markSubmitStartTime'] = $startTime;
        $condition['markSubmitEndTime'] = $endTime;
        $condition['hasMarkSubmit'] = 1;
        if(in_array('KnowledgeIndexing/knowledgeMgmt',session('exercises_permissions'))) {
            $processMattersCount = $this->questionInfoModel->getQuestionCount($this->userInfo['id'], ROLE_MARKKNOWLEDGE, $condition);
            $processedMatters[] = array('name' => '道试题的知识标引并提交审核', 'count' => $processMattersCount);
        }

        $condition = array();
        $condition['is_self'] = 1;
        $condition['inboundStartTime'] = $startTime;
        $condition['inboundEndTime'] = $endTime;
        $condition['status'] = array('gt',EXERCISE_STATE_UNINBOUND);
        if(in_array('shi-ti-shen-he-guan-li-pi-liang-ru-ku-an-niu',session('exercises_permissions'))) {
            $processMattersCount = $this->questionInfoModel->getQuestionCount($this->userInfo['id'], ROLE_COTENTADMIN, $condition);
            $processedMatters[] = array('name' => '道试题审核入库', 'count' => $processMattersCount);
        }
        //check data is valid
        foreach($processedMatters as $key=>$val)
        {
            if($val['count'] === false)
                return false;
        }

        return $processedMatters;
    }

     function index(){
       $unProcessList =   $this->getUnprocesedMatters();
       if(false === $unProcessList)
           die(-1);
       $processedList =   $this->getProcessMatters();

       if(false === $processedList)
           die(-2);
       $this->assign('userName',$this->userInfo['name']);
       $this->assign('unProcessed',$unProcessList);
       $this->assign('processedUntilToday',$processedList);
       $this->display();
     }

     function getProcessedMattersByDate()
     {
         $startDate =  getParameter('startDate','str',false);
         $endDate   =  getParameter('endDate','str',false);
         $processMattersDate = $this->getProcessMatters($startDate,$endDate);
         $this->showMessage(200,'success',$processMattersDate);
     }

     function createChoiceExercise(){
       $this->display();
     }

     function createCompletion(){
       $this->display();
     }
}
