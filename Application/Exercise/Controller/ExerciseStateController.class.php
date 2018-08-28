<?php
namespace Exercise\Controller;
use Think\Controller;
use Think\Verify;

class ExerciseStateController extends ExerciseGlobalController
{

    public $model;
    public $page_size=20;
    private $userInfo;
    public $Exercises_paper_bigquestion;

    private function _exercisePaperStatusFilter()
    {
        $statusList = getParameter("statusList",'str');
        $idList = getParameter("ids","str");
        $idList = explode(',',$idList);
        $statusList = json_decode(htmlspecialchars_decode($statusList),true);

        if($statusList['exercise'])
        {
            $model = D('Exercises_question_processinfo');
            $action = 'getQuestionInfo';
            $exerciseCategory = '习题';
            $categoryKey = 'exercise';
        }
        else
        {
            $model = D('Exercises_paper_processinfo');
            $action = 'getPaperInfo';
            $exerciseCategory = '试卷';
            $categoryKey = 'paper';
        }
        $statusArray = explode(',',$statusList[$categoryKey]);
        foreach($statusArray as $key=>$val)
        {
            $info = $model->$action($idList[$key]);
            if($info['status'].$info['is_delete'] != $val)
                $this->showMessage(500, "ID:{$idList[$key]}"."$exerciseCategory 状态异常，请刷新页面后重试");
        }
    }
    public function __construct() {
        parent::__construct();
        $this->model=D('Auth_admin');
        $this->userInfo = $this->getUserRoleAuth();
        $this->Exercises_paper_bigquestion = D('Exercises_paper_bigquestion');
        $this->_exercisePaperStatusFilter();
    }
    private function getIndexOfStatus($status)
    {
        foreach(C('exerciseLogDescription') as $key=>$val)
        {
             if($val['id'] == $status)
                 return $key;
        }
        return -1;
    }

    /*
     * 删除习题
     */
    public function deleteExercise()
    {
        $bigid = I('bigid');

        $idArray =  explode(',',getParameter('ids','str'));
        $clientIP = get_client_ip();
        $logModel = D('Exercises_log');
        $model = D('Exercises_createexercise');
        $setResult = true;
        //TODO：是否有删除权限
        M()->startTrans();
        foreach($idArray as $key=>$val) {
            $result = D('Exercises_question_processinfo')->getQuestionInfo($val);
            if ($result['is_delete'] == STATE_DELETED) {
                M()->rollback();
                $this->showMessage(500, "ID:$val" . '习题已被删除');
            }
            if ($result['is_lock'] == LOCKSTATE_LOCK) {
                M()->rollback();
                $this->showMessage(500, "ID:$val" . '习题被锁定');
            }
            $setResult &= $model->deleteExercise($val);
         if($setResult)
           $setResult &=$logModel->insertLog($val,0,'删除习题',$clientIP,$this->userInfo['id'],$this->userInfo['name'],'',0,'',BEHAVIOR_ABNORMAL);
         if(!$setResult)
         {
             M()->rollback();
             $this->showMessage(500, "ID:$val".'习题删除失败');
         }
        }
        $sid = $this->Exercises_paper_bigquestion->updateParperBigReduceNum($bigid);
        if ($sid == true){
            M()->commit();
        } else {
            M()->rollback();
            $this->showMessage(500, "ID:$val".'习题删除失败');
        }

        $this->showMessage(200, '删除成功');

    }

    /*
     * 删除试卷
     */
    public function deletePaper()
    {
        $idArray =  explode(',',getParameter('ids','str'));
        $clientIP = get_client_ip();
        $logModel = D('Exercises_log');
        $model = D('Exercises_create_paper');
        $setResult = true;
        //TODO：是否有删除权限
        M()->startTrans();
        foreach($idArray as $key=>$val) {
            $result = D('Exercises_paper_processinfo')->getPaperInfo($val);
            if ($result['is_delete'] == STATE_DELETED) {
                M()->rollback();
                $this->showMessage(500, "ID:$val" . '试卷已被删除');
            }
            if ($result['is_lock'] == LOCKSTATE_LOCK) {
                M()->rollback();
                $this->showMessage(500, "ID:$val" . '试卷被锁定');
            }
            $setResult &= $model->deletePaper($val);
            //delete exercises
            $exercisesIds = D('Exercises_parper_concat')->getQuestionIdsByPaperId($val);
            foreach($exercisesIds as $null => $value)
            {
                $result = D('Exercises_question_processinfo')->getQuestionInfo($value['id']);
                if ($result['is_delete'] == STATE_DELETED) {
//                    M()->rollback();
//                    $this->showMessage(500, "ID:{$value['id']}" . '习题已被删除');
                }
                if ($result['is_lock'] == LOCKSTATE_LOCK) {
                    M()->rollback();
                    $this->showMessage(500, "ID:{$value['id']}" . '习题被锁定');
                }
                $setResult &= D('Exercises_createexercise')->deleteExercise($value['id']);
                if($setResult)
                    $setResult &=$logModel->insertLog($value['id'],0,'删除习题',$clientIP,$this->userInfo['id'],$this->userInfo['name'],'',0,'',BEHAVIOR_ABNORMAL);
                if(!$setResult)
                {
                    M()->rollback();
                    $this->showMessage(500, "ID:$val".'习题删除失败');
                }
            }
            if($setResult)
                $setResult &=$logModel->insertLog(0,$val,'删除试卷',$clientIP,$this->userInfo['id'],$this->userInfo['name'],'',0,'',BEHAVIOR_ABNORMAL);
            if(!$setResult)
            {
                M()->rollback();
                $this->showMessage(500, "ID:$val".'试卷删除失败');
            }
        }
        M()->commit();
        $this->showMessage(200, '删除成功');

    }

    /*
     * 设置习题锁定状态
     */

    function setLockState(){
        $idArray =  explode(',',getParameter('ids','str'));
        $state =  getParameter('state','int');
        $comment = '';
        if(LOCKSTATE_LOCK != $state && LOCKSTATE_NORMAL != $state)
        {
            $this->showMessage(500, "状态标志位错误");
        }
        $lockChineseArray = array(LOCKSTATE_LOCK=>'锁定',LOCKSTATE_NORMAL=>'解锁');
        $model = D('Exercises_question_processinfo');
        $logModel = D('Exercises_log');
        $model->startTrans();
        $clientIP = get_client_ip();
        foreach($idArray as $key=>$val) {
            $result = $model->getQuestionInfo($val);
            if ($result['is_delete'] == STATE_DELETED)
            {
                $model->rollback();
                $this->showMessage(500, "ID:$val".'习题已被删除');
            }
            if(EXERCISE_STATE_WAITASSIGN > $result['status'])
            {
                $model->rollback();
                $this->showMessage(500, "ID:$val".'习题当前状态为'.C('exerciseLogDescription')[$this->getIndexOfStatus($result['status'])]['name'].'状态,不允许设置锁定状态');
            }
            $setResult = true;
            $currentState = $model->getLockState($val);
            if($currentState == $state)
            {
                $model->rollback();
                $this->showMessage(500, "错误!ID:$val".'习题状态已经是'.$lockChineseArray[$state].'状态,无法再次设置状态');
            }
            if(LOCKSTATE_NORMAL == $state)
            {
                //角色判断，如果不是超级管理员或加锁者，则退出
                if($this->userInfo['id'] != ACCOUNT_SUPERADMIN_ID) //不是超级管理员ID
                {
                   $lockerId = $model->getLockerId($val);
                   if($lockerId != $this->userInfo['id']) {
                       $model->rollback();
                       $this->showMessage(500, "您不是超级管理员或锁定者,无法解锁");
                   }
                }
            }
            else
            {
                $reason = getParameter('additionalInfo','str');
                $comment = $reason;
                //如果习题属于试卷，则锁定试卷
                $paperId = D('Exercises_parper_concat')->getPaperIdByQuestionId($val);
                if(!empty($paperId['paper_id'])) {
                    $currentState = D('Exercises_paper_processinfo')->getLockState($paperId['paper_id']);
                    if ($currentState == LOCKSTATE_NORMAL) {
                        $setResult &= D('Exercises_paper_processinfo')->setLockState($paperId['paper_id'],$this->userInfo['id'],LOCKSTATE_LOCK);
                    }
                }
            }
            $setResult &= $model->setLockState($val,$this->userInfo['id'],$state);
            if($setResult)
                $setResult &=$logModel->insertLog($val,0,$lockChineseArray[$state],$clientIP,$this->userInfo['id'],$this->userInfo['name'],$comment,0,'',BEHAVIOR_ABNORMAL);
            //删除发布
            if($setResult)
            $setResult &= D('Exercises_platform')->deletePublishExercise($val);

            if(false === $setResult)
            {
                $model->rollback();
                $this->showMessage(500, "ID:$val".'习题状态设置异常,请确认该习题状态');
            }

        }
        M()->commit();
        $this->showMessage(200, '锁定状态设置成功');
    }

    /*
    * 设置试卷锁定状态
    */

    function setPaperLockState(){
        $idArray =  explode(',',getParameter('ids','str'));
        $state =  getParameter('state','int');
        $comment  = '';
        if(LOCKSTATE_LOCK != $state && LOCKSTATE_NORMAL != $state)
        {
            $this->showMessage(500, "状态标志位错误");
        }
        $lockChineseArray = array(LOCKSTATE_LOCK=>'锁定',LOCKSTATE_NORMAL=>'解锁');
        $model = D('Exercises_paper_processinfo');
        $logModel = D('Exercises_log');
        $model->startTrans();
        $clientIP = get_client_ip();
        foreach($idArray as $key=>$val) {
            $result = $model->getPaperInfo($val);
            if ($result['is_delete'] == STATE_DELETED)
            {
                $model->rollback();
                $this->showMessage(500, "ID:$val".'试卷已被删除');
            }
            if(EXERCISE_STATE_WAITASSIGN > $result['status'])
            {
                $model->rollback();
                $this->showMessage(500, "ID:$val".'试卷当前状态为'.C('exerciseLogDescription')[$this->getIndexOfStatus($result['status'])]['name'].'状态,不允许设置锁定状态');
            }
            $setResult = true;
            $currentState = $model->getLockState($val);
            if($currentState == $state)
            {
                $model->rollback();
                $this->showMessage(500, "错误!ID:$val".'习试卷状态已经是'.$lockChineseArray[$state].'状态,无法再次设置状态');
            }
            if(LOCKSTATE_NORMAL == $state)
            {
                //角色判断，如果不是超级管理员或加锁者，则退出
                if($this->userInfo['id'] != ACCOUNT_SUPERADMIN_ID) //不是超级管理员ID
                {
                    $lockerId = $model->getLockerId($val);
                    if($lockerId != $this->userInfo['id']) {
                        $model->rollback();
                        $this->showMessage(500, "您不是超级管理员或锁定者,无法解锁");
                    }
                }
                $exerciseIds = D('Exercises_parper_concat')->getQuestionIdsByPaperId($val);
                foreach($exerciseIds as $null => $id)
                {
                    //judge state of each exercise
                    $setResult &= D('Exercises_question_processinfo')->setLockState($id['id'],$this->userInfo['id'],$state);
                    if($setResult)
                        $setResult &=$logModel->insertLog($id['id'],0,$lockChineseArray[$state],$clientIP,$this->userInfo['id'],$this->userInfo['name'],$comment,0,'',BEHAVIOR_ABNORMAL);
                }
            }
            else
            {
                $reason = getParameter('additionalInfo','str');
                $comment = $reason;
            }
            $setResult &= $model->setLockState($val,$this->userInfo['id'],$state);
            if($setResult)
                $setResult &=$logModel->insertLog(0,$val,$lockChineseArray[$state],$clientIP,$this->userInfo['id'],$this->userInfo['name'],$comment,0,'');

            //删除发布
            if($setResult)
            $setResult &= D('Exercises_platform')->deletePublishPaper($val);

            if(false === $setResult)
            {
                $model->rollback();
                $this->showMessage(500, "ID:$val".'试卷状态设置异常,请确认该习题状态');
            }
        }
        M()->commit();
        $this->showMessage(200, '锁定状态设置成功');
    }

    /*
     * 设置习题流转状态
     */
    function setExerciseState(){
      $idArray =  explode(',',getParameter('ids','str'));
      $state =  getParameter('state','int');
      $additionalInfo = getParameter('additionalInfo','str',false);
      $model = D('Exercises_question_processinfo');
      $logModel = D('Exercises_log');
      $model->startTrans();
      $clientIP = get_client_ip();
      foreach($idArray as $key=>$val) {
          $comment = '';
          $userDefinedOperation = '';
          $behaviorStatus = BEHAVIOR_NORMAL;
          $result = $model->getQuestionInfo($val);
          if ($result['is_delete'] == STATE_DELETED)
          {$model->rollback();$this->showMessage(500, "ID:$val".'习题已被删除');}
          if ($result['is_lock'] == LOCKSTATE_LOCK)
          {$model->rollback();$this->showMessage(500, "ID:$val".'习题被锁定');}
          //TODO：auth judgement
          $setResult = true;
          switch ($result['status']) {
              case EXERCISE_STATE_WAITVERIFY:
                  switch ($state) {
                      case EXERCISE_STATE_WAITVERIFY:
                          M()->rollback();
                          $this->showMessage(500, "ID:$val".'习题已经处于待审核状态');
                          break;
                      case EXERCISE_STATE_REPROCESS:
                      case EXERCISE_STATE_WAITASSIGN:
                          if($state == EXERCISE_STATE_WAITASSIGN) {
                              $setResult &= $model->setStateUser($val,$this->userInfo['id'], $this->userInfo['name'], 1 ,'verify');
                          }
                          else
                          {
                              $behaviorStatus = BEHAVIOR_ABNORMAL;
                              $userDefinedOperation = '校审不通过';
                              if(empty($additionalInfo))
                              {
                                  M()->rollback();
                                  $this->showMessage(500, "请输入返工理由");
                              }
                              $comment = $additionalInfo;
                              $setResult &= $model->setStateUser($val,$this->userInfo['id'], $this->userInfo['name'], 1, 'reject');
                          }
                          if($setResult)
                              $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                          break;
                      default:
                          M()->rollback();
                          $this->showMessage(500, "ID:$val".'习题状态无法跳跃设置');
                          break;
                  }
                  break;
              case EXERCISE_STATE_REPROCESS:
              case EXERCISE_STATE_DECLINE  :
                  switch ($state) {
                      case EXERCISE_STATE_WAITVERIFY:
                           $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                           //reprocess update
//                          $setResult &= $model->setStateUser($val,$this->userInfo['id'], $this->userInfo['name'], 1, 'reprocess');
                          break;
                      case EXERCISE_STATE_REPROCESS:
                          M()->rollback();
                          $this->showMessage(500, "ID:$val".'习题已经处于返工状态');
                          break;
                      default:
                          M()->rollback();
                          $this->showMessage(500, "ID:$val".'习题状态无法跳跃设置');
                          break;
                  }
                  break;
              case EXERCISE_STATE_WAITASSIGN:
              case EXERCISE_STATE_REASSIGN:
                  switch ($state) {
                      case EXERCISE_STATE_WAITMARK:
                      case EXERCISE_STATE_REPROCESS:
                           if($state == EXERCISE_STATE_WAITMARK) {
                               $additionalInfo = intval($additionalInfo);
                               if(intval($additionalInfo) == 0)
                               {
                                   M()->rollback();
                                   $this->showMessage(500, "请选择指派人员");
                               }
                               $userInfo = D('Exercises_account')->getUserInfo($additionalInfo);
                               if(empty($userInfo))
                               {
                                   M()->rollback();
                                   $this->showMessage(500, "该用户不存在");
                               }
                               $questionInfo = D('Create_Exercise')->getExerciseInfo( $val );
                               if($questionInfo['subject'] != $userInfo['course_id'])
                               {
                                   M()->rollback();
                                   $this->showMessage(500, "该教师所属学科ID：$val 习题所属学科不同，无法标引");
                               }
                               $setResult &= $model->setStateUser($val,$userInfo['id'], $userInfo['user_name'], 0,'mark');
                              $setResult &= $model->setStateUser($val,$this->userInfo['id'], $this->userInfo['name'], 1,'assign');
                          }
                          else
                          {
                              if(empty($additionalInfo))
                              {
                                  M()->rollback();
                                  $this->showMessage(500, "请输入返工理由");
                              }
                              $comment = $additionalInfo;
                              $behaviorStatus = BEHAVIOR_ABNORMAL;
                              $setResult &= $model->setStateUser($val,$this->userInfo['id'], $this->userInfo['name'], 1,'reject');
                          }
                          if($setResult)
                          $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                          break;
                      case EXERCISE_STATE_WAITASSIGN:
                          M()->rollback();
                          $this->showMessage(500, "ID:$val".'习题已经处于待指派状态');
                          break;
                      default:
                          M()->rollback();
                          $this->showMessage(500, "ID:$val".'习题状态无法跳跃设置');
                          break;
                  }
                  break;
              case EXERCISE_STATE_WAITMARK:
                  switch ($state) {
                      case EXERCISE_STATE_REPROCESS:
                      case EXERCISE_STATE_FINISH:
                      case EXERCISE_STATE_REASSIGN:
                          if($state == EXERCISE_STATE_REPROCESS) {
                              if(empty($additionalInfo))
                              {
                                  M()->rollback();
                                  $this->showMessage(500, "请输入返工理由");
                              }
                              $comment = $additionalInfo;
                              $behaviorStatus = BEHAVIOR_ABNORMAL;
                              $setResult &= $model->setStateUser($val, $this->userInfo['id'], $this->userInfo['name'], 1, 'reject');
                          }
                          else if(EXERCISE_STATE_FINISH == $state)//标引完成
                          {
                                  $setResult &= $model->setStateUser($val, $this->userInfo['id'], $this->userInfo['name'], 1,'mark');
                          }
                          else //重新分配
                          {
                              if(empty($additionalInfo))
                              {
                                  M()->rollback();
                                  $this->showMessage(500, "请输入重新分派理由");
                              }
                              $comment = $additionalInfo;
                              $behaviorStatus = BEHAVIOR_ABNORMAL;
                              $userDefinedOperation = '重新分派';
                              $setResult &= $model->setStateUser($val,0, '', 0,'mark');
                          }
                          if($setResult)
                          $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                          break;
                      case EXERCISE_STATE_WAITMARK:
                          M()->rollback();
                          $this->showMessage(500, "ID:$val".'习题已经处于待标引状态');
                          break;
                      default:
                          M()->rollback();
                          $this->showMessage(500, "ID:$val".'习题状态无法跳跃设置');
                          break;
                  }
                  break;
              case EXERCISE_STATE_FINISH   :
                  switch ($state) {
                      case EXERCISE_STATE_REASSIGN:
                          if(empty($additionalInfo))
                          {
                              M()->rollback();
                              $this->showMessage(500, "请输入重新分派理由");
                          }
                          $behaviorStatus = BEHAVIOR_ABNORMAL;
                          $userDefinedOperation = '重新分派';
                          $setResult &= $model->setStateUser($val,0, '', 0,'mark');
                          if($setResult)
                              $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                          break;
                      case EXERCISE_STATE_UNINBOUND: //标引完成后的审核操作
                          if($setResult)
                              $setResult &= $model->setStateUser($val,$this->userInfo['id'], $this->userInfo['name'], 1,'marksubmit');
                          if($setResult)
                          $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);

                          break;
                      case EXERCISE_STATE_FINISH:
                          M()->rollback();
                          $this->showMessage(500, "ID:$val".'习题已经处于已完成状态');
                          break;
                      case EXERCISE_STATE_REPROCESS:
                          $behaviorStatus = BEHAVIOR_ABNORMAL;
                          if(empty($additionalInfo))
                          {
                              M()->rollback();
                              $this->showMessage(500, "请输入返工理由");
                          }
                          $comment = $additionalInfo;
                          $setResult &= $model->setStateUser($val,$this->userInfo['id'], $this->userInfo['name'], 1, 'reject');
                          if($setResult)
                              $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                          break;
                      default:
                          M()->rollback();
                          $this->showMessage(500, "ID:$val".'习题状态无法跳跃设置');
                          break;
                  }
                  break;
              //标引完成后点击提交审核状态跳至未入库

              case EXERCISE_STATE_UNINBOUND:
                     switch ($state) {

                         case EXERCISE_STATE_INBOUND:
                         case EXERCISE_STATE_WAITMARK:
                         case EXERCISE_STATE_REPROCESS:
                             if($state == EXERCISE_STATE_REPROCESS) {
                                 if(empty($additionalInfo))
                                 {
                                     M()->rollback();
                                     $this->showMessage(500, "请输入返工理由");
                                 }
                                 $comment = $additionalInfo;
                                 $behaviorStatus = BEHAVIOR_ABNORMAL;
                                 $setResult &= $model->setStateUser($val, $this->userInfo['id'], $this->userInfo['name'], 1, 'reject');
                             }
                                 else if($state == EXERCISE_STATE_WAITMARK)
                             {
                                 if(empty($additionalInfo))
                                 {
                                     M()->rollback();
                                     $this->showMessage(500, "请输入重新标引理由");
                                 }
                                 $comment = $additionalInfo;
                                 $behaviorStatus = BEHAVIOR_ABNORMAL; //重新标引
                                 $userDefinedOperation = '重新标引';
                                 //$setResult &= $model->setStateUser($val, $this->userInfo['id'], $this->userInfo['name'], 0, 'assign');
                                 $setResult &= $model->setStateUser($val, $this->userInfo['id'], $this->userInfo['name'], 1, 'markreject');
                             }
                             else //inbound
                             {
                                 $setResult &= $model->setStateUser($val, $this->userInfo['id'], $this->userInfo['name'], 1, 'inbound');
                             }
                             if($setResult)
                              $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                             if($state == EXERCISE_STATE_INBOUND)
                             {

                                 //judge if the question exists in paper
                                 //get paperId by questionId
                                 $questionPaperModel = D('Exercises_parper_concat');
                                 $paperInfo = $questionPaperModel->getPaperIdByQuestionId($val);
                                 if(!empty($paperInfo))
                                 {
                                     $allCount = $questionPaperModel->getPaperQuestionCount($paperInfo['paper_id']);
                                     $inBoundQuestionCount = $questionPaperModel->getPaperQuestionCount($paperInfo['paper_id'],EXERCISE_STATE_INBOUND);
                                     if($allCount == $inBoundQuestionCount)
                                     {
                                         $paperSetResult = D('Exercises_paper_processinfo')->setPaperState($paperInfo['paper_id'],EXERCISE_STATE_INBOUND,0);
                                         if(false === $paperSetResult)
                                         {M()->rollback();$this->showMessage(500, "ID:$val".'习题所属试卷入库失败，请联系管理员');}
                                     }
                                 }
                             }
                             break;
                         case EXERCISE_STATE_UNINBOUND:
                             M()->rollback();
                             $this->showMessage(500, "ID:$val".'习题已经处于未入库状态');
                             break;
                         case EXERCISE_STATE_WAITASSIGN:
                             if(empty($additionalInfo))
                             {
                                 M()->rollback();
                                 $this->showMessage(500, "请输入重新分派理由");
                             }
                             $behaviorStatus = BEHAVIOR_ABNORMAL;
                             $userDefinedOperation = '重新分派';
                             $setResult &= $model->setStateUser($val,0, '', 0,'mark');
                             if($setResult)
                                 $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                             break;
                         default:
                             M()->rollback();
                             $this->showMessage(500, "ID:$val".'习题状态无法跳跃设置');
                             break;
                     }
                     break;
              case EXERCISE_STATE_INBOUND :
              case EXERCISE_STATE_UNONSHELF:
                        switch ($state) {
                            case EXERCISE_STATE_ONSHELF:
                                $comment = PLATFORM_NAME;
                                $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                                //UPDATE UP_TIME
                                $setResult &= $model->updateUpTime($val);

                                break;
                            case EXERCISE_STATE_INBOUND:
                                M()->rollback();
                                $this->showMessage(500, "ID:$val".'习题已经处入库/未上架状态');
                                break;
                            case EXERCISE_STATE_REPROCESS:
                                $behaviorStatus = BEHAVIOR_ABNORMAL;
                                if(empty($additionalInfo))
                                {
                                    M()->rollback();
                                    $this->showMessage(500, "请输入返工理由");
                                }
                                $comment = $additionalInfo;
                                $setResult &= $model->setStateUser($val,$this->userInfo['id'], $this->userInfo['name'], 1, 'reject');
                                if($setResult)
                                    $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                                break;
                            case EXERCISE_STATE_WAITMARK:
                                if(empty($additionalInfo))
                                {
                                    M()->rollback();
                                    $this->showMessage(500, "请输入重新标引理由");
                                }
                                $behaviorStatus = BEHAVIOR_ABNORMAL;
                                $userDefinedOperation = '重新标引';
                                $comment = $additionalInfo;
                                if($setResult)
                                    $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                                break;
                            default:
                                M()->rollback();
                                $this->showMessage(500, "ID:$val".'习题状态无法跳跃设置');
                                break;
                        }
                        break;
              case EXERCISE_STATE_ONSHELF :
                         switch ($state) {
                             case EXERCISE_STATE_OFFSHELF:
                                 $comment = PLATFORM_NAME;
                                 $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                                 break;
                             case EXERCISE_STATE_ONSHELF:
                                 M()->rollback();
                                 $this->showMessage(500, "ID:$val".'习题已经处于上架状态');
                                 break;
                             case EXERCISE_STATE_REPROCESS:
                                 $behaviorStatus = BEHAVIOR_ABNORMAL;
                                 if(empty($additionalInfo))
                                 {
                                     M()->rollback();
                                     $this->showMessage(500, "请输入返工理由");
                                 }
                                 $comment = $additionalInfo;
                                 $setResult &= $model->setStateUser($val,$this->userInfo['id'], $this->userInfo['name'], 1, 'reject');
                                 if($setResult)
                                     $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                                 break;
                             case EXERCISE_STATE_WAITMARK:
                                 if(empty($additionalInfo))
                                 {
                                     M()->rollback();
                                     $this->showMessage(500, "请输入重新标引理由");
                                 }
                                 $behaviorStatus = BEHAVIOR_ABNORMAL;
                                 $userDefinedOperation = '重新标引';
                                 $comment = $additionalInfo;
                                 if($setResult)
                                     $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                                 break;
                             default:
                                 M()->rollback();
                                 $this->showMessage(500, "ID:$val".'习题状态无法跳跃设置');
                                 break;
                         }
                         break;
              case EXERCISE_STATE_OFFSHELF :
                        switch ($state) {
                            case EXERCISE_STATE_WAITVERIFY:
                                $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                                break;
                            case EXERCISE_STATE_REPROCESS:
                                $behaviorStatus = BEHAVIOR_ABNORMAL;
                                if(empty($additionalInfo))
                                {
                                    M()->rollback();
                                    $this->showMessage(500, "请输入返工理由");
                                }
                                $comment = $additionalInfo;
                                $setResult &= $model->setStateUser($val,$this->userInfo['id'], $this->userInfo['name'], 1, 'reject');
                                if($setResult)
                                    $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                                break;
                            case EXERCISE_STATE_OFFSHELF:
                                M()->rollback();
                                $this->showMessage(500, "ID:$val".'习题已经处于下架状态');
                                break;
                            case EXERCISE_STATE_ONSHELF:
                                $comment = PLATFORM_NAME;
                                $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                                break;
                            case EXERCISE_STATE_WAITMARK:
                                if(empty($additionalInfo))
                                {
                                    M()->rollback();
                                    $this->showMessage(500, "请输入重新标引理由");
                                }
                                $behaviorStatus = BEHAVIOR_ABNORMAL;
                                $userDefinedOperation = '重新标引';
                                $comment = $additionalInfo;
                                if($setResult)
                                    $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
                                break;
                            default:
                                M()->rollback();
                                $this->showMessage(500, "ID:$val".'习题状态无法跳跃设置');
                                break;
                        }
                        break;
              default:$this->showMessage(500, "ID:$val".'习题当前状态异常');break;

          }
          $pOperatorId = 0;
          $pOperatorName = '';
          if($state == EXERCISE_STATE_REPROCESS)
          {
              $questionInfo = $model->getQuestionInfo($val);
              $pOperatorId = $questionInfo['creator_id'];
              $pOperatorName = $questionInfo['creator_name'];
              $setResult &= D('Exercises_textbook_tree_info_createexercise')->deleteKnowledgeByExerciseId($val);
          }
          elseif ($state == EXERCISE_STATE_WAITMARK && $result['status']>EXERCISE_STATE_WAITMARK )
          {
              $questionInfo = $model->getQuestionInfo($val);
              $pOperatorId = $questionInfo['marker_id'];
              $pOperatorName = $questionInfo['marker_name'];
              $setResult &= D('Exercises_textbook_tree_info_createexercise')->deleteKnowledgeByExerciseId($val);
          }
          if($setResult)
              $setResult &=$logModel->insertLog($val,0,
                                                $userDefinedOperation==''?C('exerciseLogDescription')[$this->getIndexOfStatus($state)]['name']:$userDefinedOperation,
                                                $clientIP,$this->userInfo['id'],$this->userInfo['name'],$comment,$pOperatorId,$pOperatorName,$behaviorStatus);
          if(false === $setResult)
          {
              $model->rollback();
              $this->showMessage(500, "ID:$val".'习题状态设置异常,请确认该习题状态');
          }
          //如果习题属于试卷，则当state为返工或重新标引时将试卷状态置为未入库
          if(EXERCISE_STATE_REPROCESS == $state || EXERCISE_STATE_WAITMARK == $state)
          {
              $paperId = D('Exercises_parper_concat')->getPaperIdByQuestionId($val);
              if(!empty($paperId['paper_id']))
              {
                  $setResult &= D('Exercises_paper_processinfo')->setPaperState($paperId['paper_id'], EXERCISE_STATE_UNINBOUND,$this->userInfo['id']);
                  if(!$setResult)
                  {
                      $model->rollback();
                      $this->showMessage(500, "ID:$paperId".'试卷状态设置异常,请确认该试卷状态');
                  }
              }
          }
          if(EXERCISE_STATE_OFFSHELF == $state )
          {
              $paperId = D('Exercises_parper_concat')->getPaperIdByQuestionId($val);
              if(!empty($paperId['paper_id']))
              {
                  $setResult &= D('Exercises_paper_processinfo')->setPaperState($paperId['paper_id'], EXERCISE_STATE_OFFSHELF,$this->userInfo['id']);
                  if(!$setResult)
                  {
                      $model->rollback();
                      $this->showMessage(500, "ID:$paperId".'试卷状态设置异常,请确认该试卷状态');
                  }
              }
          }
          //
          else if(EXERCISE_STATE_ONSHELF == $state){
              $questionPaperModel = D('Exercises_parper_concat');
              $paperInfo = $questionPaperModel->getPaperIdByQuestionId($val);
              if(!empty($paperInfo))
              {
                  $allCount = $questionPaperModel->getPaperQuestionCount($paperInfo['paper_id']);
                  $inBoundQuestionCount = $questionPaperModel->getPaperQuestionCount($paperInfo['paper_id'],EXERCISE_STATE_ONSHELF);
                  if($allCount == $inBoundQuestionCount)
                  {
                      $paperSetResult = D('Exercises_paper_processinfo')->setPaperState($paperInfo['paper_id'],EXERCISE_STATE_ONSHELF,0);
                      if(false === $paperSetResult)
                      {M()->rollback();$this->showMessage(500, "ID:$val".'习题所属试卷上架失败，请联系管理员');}
                  }
              }

          }

      }
        $model->commit();
        $this->showMessage(200, '设置成功');
    }
    /*
     * 设置试卷的试题状态 status对应19 24
     */
    function setPaperExerciseState()
    {
      //TODO：
        $idArray =  explode(',',getParameter('ids','str'));
        $state =  getParameter('state','int');
        $additionalInfo = getParameter('additionalInfo','str',false);
        $model = D('Exercises_question_processinfo');
        $logModel = D('Exercises_log');
        $model->startTrans();
        $clientIP = get_client_ip();
        foreach($idArray as $key=>$val) {
            $comment = '';
            $userDefinedOperation = '';
            $behaviorStatus = BEHAVIOR_NORMAL;
            $result = $model->getQuestionInfo($val);
            if ($result['is_delete'] == STATE_DELETED)
            {$model->rollback();$this->showMessage(500, "ID:$val".'习题已被删除');}
            if ($result['is_lock'] == LOCKSTATE_LOCK)
            {$model->rollback();$this->showMessage(500, "ID:$val".'习题被锁定');}
            //question In paper
            $paperId = D('Exercises_parper_concat')->getPaperIdByQuestionId($val);
            if(empty($paperId))
            {$model->rollback();$this->showMessage(500, "ID:$val".'习题不属于试卷');}
            $setResult = true;
            switch ($result['status']) {
                case EXERCISE_STATE_PAPEREXERCISEWAITVERIFY:
                                                             switch($state)
                                                             {
                                                                 case EXERCISE_STATE_PAPEREXERCISEDECLINE:
                                                                 case EXERCISE_STATE_WAITASSIGN:
                                                                     if(EXERCISE_STATE_PAPEREXERCISEDECLINE == $state)
                                                                     {
                                                                         if(empty($additionalInfo))
                                                                         {
                                                                             M()->rollback();
                                                                             $this->showMessage(500, "请输入返工理由");
                                                                         }
                                                                         $comment = $additionalInfo;
                                                                         $behaviorStatus = BEHAVIOR_ABNORMAL;
                                                                         $setResult &= $model->setStateUser($val,$this->userInfo['id'], $this->userInfo['name'], 1,'reject');
                                                                     }
                                                                     break;
                                                                 default:M()->rollback();
                                                                          $this->showMessage(500, "ID:$val".'习题状态无法跳跃设置');
                                                                          break;
                                                             }
                                                             break;
                case EXERCISE_STATE_PAPEREXERCISEDECLINE:
                                                             switch($state)
                                                             {
                                                                 case EXERCISE_STATE_PAPEREXERCISEWAITVERIFY:
                                                                     $setResult &= $model->setQuestionState($val, $state,$this->userInfo['id']);
//                                                                     if($setResult)
//                                                                     $setResult &= $model->setStateUser($val,$this->userInfo['id'], $this->userInfo['name'], 1,'reprocess');
                                                                     break;
                                                                 default:M()->rollback();
                                                                     $this->showMessage(500, "ID:$val".'习题状态无法跳跃设置');
                                                                     break;
                                                             }
                                                             break;
                default:$this->showMessage(500, "ID:$val".'习题当前状态异常');break;
            }
            $pOperatorId = 0;
            $pOperatorName = '';
            if($state == EXERCISE_STATE_REPROCESS)
            {
                $questionInfo = $model->getQuestionInfo($val);
                $pOperatorId = $questionInfo['creator_id'];
                $pOperatorName = $questionInfo['creator_name'];
            }
            if($setResult)
                $setResult &=$logModel->insertLog($val,0,
                    $userDefinedOperation==''?C('exerciseLogDescription')[$this->getIndexOfStatus($state)]['name']:$userDefinedOperation,
                    $clientIP,$this->userInfo['id'],$this->userInfo['name'],$comment,$pOperatorId,$pOperatorName,$behaviorStatus);
            if(false === $setResult)
            {
                $model->rollback();
                $this->showMessage(500, "ID:$val".'习题状态设置异常,请确认该习题状态');
            }
        }
        $model->commit();
        $this->showMessage(200, '设置成功');
    }

    /*
     * 设置试卷状态
     */
    function setPaperState(){
        $idArray =  explode(',',getParameter('ids','str'));
        $state =  getParameter('state','int');
        $additionalInfo = getParameter('additionalInfo','str',false);
        $model = D('Exercises_paper_processinfo');
        $logModel = D('Exercises_log');
        $model->startTrans();
        $clientIP = get_client_ip();
        foreach($idArray as $key=>$val) {
            $comment = '';
            $userDefinedOperation = '';
            $behaviorStatus = BEHAVIOR_NORMAL;
            $result = $model->getPaperInfo($val);
            if ($result['is_delete'] == STATE_DELETED)
            {$model->rollback();$this->showMessage(500, "ID:$val".'试卷已被删除');}
            if ($result['is_lock'] == LOCKSTATE_LOCK)
            {$model->rollback();$this->showMessage(500, "ID:$val".'试卷被锁定');}
            //TODO：auth judgement
            $setResult = true;
            switch ($result['status']) {
                case EXERCISE_STATE_DRAFT:
                    switch($state)
                    {
                        case EXERCISE_STATE_WAITVERIFY:
                            $setResult &= $model->setPaperState($val, $state,$this->userInfo['id']);
                            break;
                        default: M()->rollback();
                        $this->showMessage(500, "ID:$val".'试卷状态无法跳跃设置');
                    }
                    break;
                case EXERCISE_STATE_WAITVERIFY:
                    switch ($state) {
                        case EXERCISE_STATE_WAITVERIFY:
                            M()->rollback();
                            $this->showMessage(500, "ID:$val".'试卷已经处于待审核状态');
                            break;
                        case EXERCISE_STATE_REPROCESS:
                        case EXERCISE_STATE_UNINBOUND:
                                if(EXERCISE_STATE_UNINBOUND == $state) //审核通过后状态转为未入库
                                {
                                    $setResult &= $model->setStateUser($val, $this->userInfo['id'], $this->userInfo['name'], 1, 'verify');
                                    $exerciseIds = D('Exercises_parper_concat')->getQuestionIdsByPaperId($val);
                                    foreach($exerciseIds as $null => $id)
                                    {
                                        //judge state of each exercise
                                        $exerciseStatus = D('Exercises_question_processinfo')->getQuestionStatus($id['id']);
                                        if(EXERCISE_STATE_REPROCESS == $exerciseStatus)
                                        {
                                            M()->rollback();
                                            $this->showMessage(500, "ID:{$id['id']}".'试题状态为返工状态,试卷无法提交');
                                        }
                                        $setResult &= D('Exercises_question_processinfo')->setStateUser($id['id'], $this->userInfo['id'], $this->userInfo['name'], 1, 'verify');
                                        if($setResult)
                                        $setResult &= D('Exercises_question_processinfo')->setQuestionState($id['id'], EXERCISE_STATE_WAITASSIGN,$this->userInfo['id']);
                                        if($setResult)
                                            $setResult &=$logModel->insertLog($id['id'],0,
                                                $userDefinedOperation==''?C('exerciseLogDescription')[$this->getIndexOfStatus(EXERCISE_STATE_WAITASSIGN)]['name']:$userDefinedOperation,
                                                $clientIP,$this->userInfo['id'],$this->userInfo['name'],$comment,0,'',$behaviorStatus);
                                    }
                                }
                                else {
                                    $behaviorStatus = BEHAVIOR_ABNORMAL;
                                    $userDefinedOperation = '校审不通过';
                                    if (empty($additionalInfo)) {
                                        M()->rollback();
                                        $this->showMessage(500, "请输入返工理由");
                                    }
                                    $comment = $additionalInfo;
                                    $setResult &= $model->setStateUser($val, $this->userInfo['id'], $this->userInfo['name'], 1, 'reject');
                                }
                            if($setResult)
                                $setResult &= $model->setPaperState($val, $state,$this->userInfo['id']);
                            break;
                        default:
                            M()->rollback();
                            $this->showMessage(500, "ID:$val".'试卷状态无法跳跃设置');
                            break;
                    }
                    break;
                case EXERCISE_STATE_REPROCESS:
                case EXERCISE_STATE_DECLINE  :
                    switch ($state) {
                        case EXERCISE_STATE_WAITVERIFY:
                            $setResult &= $model->setPaperState($val, $state,$this->userInfo['id']);
                            break;
                        case EXERCISE_STATE_REPROCESS:
                            M()->rollback();
                            $this->showMessage(500, "ID:$val".'试卷已经处于返工状态');
                            break;
                        default:
                            M()->rollback();
                            $this->showMessage(500, "ID:$val".'试卷状态无法跳跃设置');
                            break;
                    }
                    break;
                case EXERCISE_STATE_INBOUND :   //试卷所有习题入库后，试卷状态自动变为已入库
                case EXERCISE_STATE_UNONSHELF:
                    switch ($state) {
                        case EXERCISE_STATE_ONSHELF:
                            $setResult &= $model->setPaperState($val, $state,$this->userInfo['id']);
                            $exerciseIds = D('Exercises_parper_concat')->getQuestionIdsByPaperId($val);
                            foreach($exerciseIds as $null => $id)
                            {
                                //judge state of each exercise
                                $exerciseStatus = D('Exercises_question_processinfo')->getQuestionStatus($id);
                                if(EXERCISE_STATE_REPROCESS == $exerciseStatus)
                                {
                                    M()->rollback();
                                    $this->showMessage(500, "ID:$id".'试题状态为返工状态,试卷无法上架');
                                }
                                if($setResult)
                                    $setResult &= D('Exercises_question_processinfo')->setQuestionState($id['id'], EXERCISE_STATE_ONSHELF,$this->userInfo['id']);
                                if($setResult)
                                    $setResult &=$logModel->insertLog($id['id'],0,
                                        $userDefinedOperation==''?C('exerciseLogDescription')[$this->getIndexOfStatus(EXERCISE_STATE_ONSHELF)]['name']:$userDefinedOperation,
                                        $clientIP,$this->userInfo['id'],$this->userInfo['name'],$comment,0,'',$behaviorStatus);
                            }
                            break;
                        case EXERCISE_STATE_INBOUND:
                            M()->rollback();
                            $this->showMessage(500, "ID:$val".'试卷已经处入库/未上架状态');
                            break;
                        case EXERCISE_STATE_REPROCESS:
                            $behaviorStatus = BEHAVIOR_ABNORMAL;
                            if (empty($additionalInfo)) {
                                M()->rollback();
                                $this->showMessage(500, "请输入返工理由");
                            }
                            $comment = $additionalInfo;
                            $exerciseIds = D('Exercises_parper_concat')->getQuestionIdsByPaperId($val);
                            foreach($exerciseIds as $null=>$ids)
                            {
                                $id = $ids['id'];
                                $result = D('Exercises_question_processinfo')->getQuestionInfo($val);
                                if ($result['is_delete'] == STATE_DELETED)
                                {$model->rollback();$this->showMessage(500, "ID:$id".'习题已被删除');}
                                if ($result['is_lock'] == LOCKSTATE_LOCK)
                                {$model->rollback();$this->showMessage(500, "ID:$id".'习题被锁定');}
                                $setResult &= D('Exercises_question_processinfo')->setQuestionState($id, EXERCISE_STATE_PAPEREXERCISEWAITVERIFY,$this->userInfo['id']);

                            }
                            if($setResult)
                            $setResult &= $model->setPaperState($val, $state,$this->userInfo['id']);
                            break;
                        case EXERCISE_STATE_WAITMARK:
                            //set all exercise state to waitmark
                            $behaviorStatus = BEHAVIOR_ABNORMAL;
                            if (empty($additionalInfo)) {
                                M()->rollback();
                                $this->showMessage(500, "请输入重新标引理由");
                            }
                            $comment = $additionalInfo;
                            $exerciseIds = D('Exercises_parper_concat')->getQuestionIdsByPaperId($val);
                            foreach($exerciseIds as $null=>$ids)
                            {
                                $id = $ids['id'];
                                $result = D('Exercises_question_processinfo')->getQuestionInfo($val);
                                if ($result['is_delete'] == STATE_DELETED)
                                {$model->rollback();$this->showMessage(500, "ID:$id".'习题已被删除');}
                                if ($result['is_lock'] == LOCKSTATE_LOCK)
                                {$model->rollback();$this->showMessage(500, "ID:$id".'习题被锁定');}
                                $setResult &= D('Exercises_question_processinfo')->setQuestionState($id, EXERCISE_STATE_WAITMARK,$this->userInfo['id']);
                                if(!$setResult)
                                {$model->rollback();$this->showMessage(500, "ID:$id".'习题状态设置错误');}
                                //write log to every exercise
                                if($setResult)
                                $setResult &=$logModel->insertLog($id,0,
                                    '重新标引',
                                    $clientIP,$this->userInfo['id'],$this->userInfo['name'],$additionalInfo,0,'',$behaviorStatus);
                                if(!$setResult)
                                {$model->rollback();$this->showMessage(500, "ID:$id".'习题LOG写入错误');}
                            }
                            $state = EXERCISE_STATE_UNINBOUND;
                            if($setResult)
                                $setResult &= $model->setPaperState($val, $state,$this->userInfo['id']);

                            break;
                        default:
                            M()->rollback();
                            $this->showMessage(500, "ID:$val".'试卷状态无法跳跃设置');
                            break;
                    }
                    break;
                case EXERCISE_STATE_ONSHELF :
                    switch ($state) {
                        case EXERCISE_STATE_OFFSHELF:
                            $setResult &= $model->setPaperState($val, $state,$this->userInfo['id']);
                            break;
                        case EXERCISE_STATE_ONSHELF:
                            M()->rollback();
                            $this->showMessage(500, "ID:$val".'试卷已经处于上架状态');
                            break;
                        case EXERCISE_STATE_REPROCESS:
                            $behaviorStatus = BEHAVIOR_ABNORMAL;
                            if (empty($additionalInfo)) {
                                M()->rollback();
                                $this->showMessage(500, "请输入返工理由");
                            }
                            $comment = $additionalInfo;
                            $exerciseIds = D('Exercises_parper_concat')->getQuestionIdsByPaperId($val);
                            foreach($exerciseIds as $null=>$ids)
                            {
                                $id = $ids['id'];
                                $result = D('Exercises_question_processinfo')->getQuestionInfo($val);
                                if ($result['is_delete'] == STATE_DELETED)
                                {$model->rollback();$this->showMessage(500, "ID:$id".'习题已被删除');}
                                if ($result['is_lock'] == LOCKSTATE_LOCK)
                                {$model->rollback();$this->showMessage(500, "ID:$id".'习题被锁定');}
                                $setResult &= D('Exercises_question_processinfo')->setQuestionState($id, EXERCISE_STATE_PAPEREXERCISEWAITVERIFY,$this->userInfo['id']);

                            }
                            if($setResult)
                                $setResult &= $model->setPaperState($val, $state,$this->userInfo['id']);
                            break;
                        default:
                            M()->rollback();
                            $this->showMessage(500, "ID:$val".'试卷状态无法跳跃设置');
                            break;
                    }
                    break;
                case EXERCISE_STATE_OFFSHELF :
                    switch ($state) {
                        case EXERCISE_STATE_WAITVERIFY:
                        case EXERCISE_STATE_ONSHELF:
                             $setResult &= $model->setPaperState($val, $state,$this->userInfo['id']);
                             $exerciseIds = D('Exercises_parper_concat')->getQuestionIdsByPaperId($val);
                             foreach($exerciseIds as $null => $id)
                             {
                                 //judge state of each exercise
                                 $exerciseStatus = D('Exercises_question_processinfo')->getQuestionStatus($id);
                                 if(EXERCISE_STATE_REPROCESS == $exerciseStatus)
                                 {
                                     M()->rollback();
                                     $this->showMessage(500, "ID:$id".'试题状态为返工状态,试卷无法上架');
                                 }
                                 if($setResult)
                                     $setResult &= D('Exercises_question_processinfo')->setQuestionState($id['id'], EXERCISE_STATE_ONSHELF,$this->userInfo['id']);
                                 if($setResult)
                                     $setResult &=$logModel->insertLog($id['id'],0,
                                         $userDefinedOperation==''?C('exerciseLogDescription')[$this->getIndexOfStatus(EXERCISE_STATE_ONSHELF)]['name']:$userDefinedOperation,
                                         $clientIP,$this->userInfo['id'],$this->userInfo['name'],$comment,0,'',$behaviorStatus);
                             }
                            break;
                        case EXERCISE_STATE_OFFSHELF:
                            M()->rollback();
                            $this->showMessage(500, "ID:$val".'试卷已经处于下架状态');
                            break;
                        case EXERCISE_STATE_REPROCESS:
                            $behaviorStatus = BEHAVIOR_ABNORMAL;
                            if (empty($additionalInfo)) {
                                M()->rollback();
                                $this->showMessage(500, "请输入返工理由");
                            }
                            $comment = $additionalInfo;
                            $exerciseIds = D('Exercises_parper_concat')->getQuestionIdsByPaperId($val);
                            foreach($exerciseIds as $null=>$ids)
                            {
                                $id = $ids['id'];
                                $result = D('Exercises_question_processinfo')->getQuestionInfo($val);
                                if ($result['is_delete'] == STATE_DELETED)
                                {$model->rollback();$this->showMessage(500, "ID:$id".'习题已被删除');}
                                if ($result['is_lock'] == LOCKSTATE_LOCK)
                                {$model->rollback();$this->showMessage(500, "ID:$id".'习题被锁定');}
                                $setResult &= D('Exercises_question_processinfo')->setQuestionState($id, EXERCISE_STATE_PAPEREXERCISEWAITVERIFY,$this->userInfo['id']);

                            }
                            if($setResult)
                                $setResult &= $model->setPaperState($val, $state,$this->userInfo['id']);
                            break;
                        case EXERCISE_STATE_WAITMARK:
                            //set all exercise state to waitmark
                            $behaviorStatus = BEHAVIOR_ABNORMAL;
                            if (empty($additionalInfo)) {
                                M()->rollback();
                                $this->showMessage(500, "请输入重新标引理由");
                            }
                            $comment = $additionalInfo;
                            $exerciseIds = D('Exercises_parper_concat')->getQuestionIdsByPaperId($val);
                            foreach($exerciseIds as $null=>$ids)
                            {
                                $id = $ids['id'];
                                $result = D('Exercises_question_processinfo')->getQuestionInfo($id);
                                if ($result['is_delete'] == STATE_DELETED)
                                {$model->rollback();$this->showMessage(500, "ID:$id".'习题已被删除');}
                                if ($result['is_lock'] == LOCKSTATE_LOCK)
                                {$model->rollback();$this->showMessage(500, "ID:$id".'习题被锁定');}
                                $setResult &= D('Exercises_question_processinfo')->setQuestionState($id, EXERCISE_STATE_WAITMARK,$this->userInfo['id']);
                                if(!$setResult)
                                {$model->rollback();$this->showMessage(500, "ID:$id".'习题状态设置错误');}
                                //write log to every exercise
                                if($setResult)
                                    $setResult &=$logModel->insertLog($id,0,
                                        '重新标引',
                                        $clientIP,$this->userInfo['id'],$this->userInfo['name'],$additionalInfo,0,'',$behaviorStatus);
                                if(!$setResult)
                                {$model->rollback();$this->showMessage(500, "ID:$id".'习题LOG写入错误');}
                            }
                            $state = EXERCISE_STATE_UNINBOUND;
                            if($setResult)
                                $setResult &= $model->setPaperState($val, $state,$this->userInfo['id']);

                            break;
                        default:
                            M()->rollback();
                            $this->showMessage(500, "ID:$val".'试卷状态无法跳跃设置');
                            break;
                    }
                    break;
                default: $this->showMessage(500, "ID:$val".'试卷当前状态异常');break;

            }
            $pOperatorId = 0;
            $pOperatorName = '';
            if($state == EXERCISE_STATE_REPROCESS)
            {
                $questionInfo = $model->getPaperInfo($val);
                $pOperatorId = $questionInfo['creator_id'];
                $pOperatorName = $questionInfo['creator_name'];
            }
            if($setResult)
                $setResult &=$logModel->insertLog(0,$val,
                    $userDefinedOperation==''?C('exerciseLogDescription')[$this->getIndexOfStatus($state)]['name']:$userDefinedOperation,
                    $clientIP,$this->userInfo['id'],$this->userInfo['name'],$comment,$pOperatorId,$pOperatorName,$behaviorStatus);
            if(false === $setResult)
            {
                $model->rollback();
                $this->showMessage(500, "ID:$val".'试卷状态设置异常,请确认该试卷状态');
            }
        }
        $model->commit();
        $this->showMessage(200, '设置成功');
    }

    /*
     * 习题发布第三方
     */
    function publishExercise($platformId=0,$ids='',$startTime='',$endTime='',$isEdit=false)
    {
        $platformId = $platformId==0 ? getParameter('platformId','int'):$platformId;
        $ids =  $ids == '' ? getParameter('ids','str') :$ids;
        $startTime = $startTime == '' ?getParameter('startTime','str'):$startTime;
        $endTime = $endTime == '' ?getParameter('endTime','str'):$endTime;
        $isEdit = $isEdit === false ?getParameter('isEdit','int',false):$isEdit;
        $platformInfo = D('Exercises_platform')->getPlatformInfo($platformId);

        if(empty($platformInfo))
            $this->showMessage(500,'平台不存在');
        if($platformInfo['type'] != PLATFORM_GET)
        {
            $this->showMessage(500,'平台不属于获取类型');
        }
        //判断试题状态是否入库
        foreach(explode(',',$ids) as $key=>$val)
        {
            $result = D('Exercises_question_processinfo')->getQuestionInfo($val);
            if ($result['is_delete'] == STATE_DELETED)
            {$this->showMessage(500, "ID:$val".'习题已被删除');}
            if ($result['is_lock'] == LOCKSTATE_LOCK)
            {$this->showMessage(500, "ID:$val".'习题被锁定');}
            $status = D('Exercises_question_processinfo')->getQuestionStatus($val);
            if($status < EXERCISE_STATE_INBOUND)
            {
                $this->showMessage(500,"ID:$val 习题未入库,无法发布");
            }
        }
        $errorInfo = '';
        $getResult = D('Exercises_platform')->getIsPublished($platformId, RESOURCETYPE_EXERCISE, $ids, $errorInfo);
        if(false === $isEdit) { //添加发布
            if (false === $getResult)  //已发布
                $this->showMessage(500, $errorInfo);
            $result = D('Exercises_platform')->insertPublishInfo($platformId, RESOURCETYPE_EXERCISE, $ids, array('startTime' => $startTime, 'endTime' => date('Y-m-d H:i:s',strtotime($endTime)+86399)));
            if(true === $result)
                $this->showMessage(200,'添加成功');
        }
        else{ //编辑发布
            if (false !== $getResult)  //未发布
                $this->showMessage(500, empty($errorInfo) ? '习题未发布,请刷新页面重试': $errorInfo);
            $result = D('Exercises_platform')->insertPublishInfo($platformId, RESOURCETYPE_EXERCISE, $ids, array('startTime' => $startTime, 'endTime' => date('Y-m-d H:i:s',strtotime($endTime)+86399)),$isEdit);
            if(true === $result)
                $this->showMessage(200,'修改成功');
        }
        if(false === $result)
            $this->showMessage(500,'平台不属于获取类型');
    }

    /*
     * 试卷发布第三方
     */
    function publishPaper($platformId=0,$ids='',$startTime='',$endTime='',$isEdit=false)
    {
        $platformId = $platformId==0 ? getParameter('platformId','int'):$platformId;
        $ids =  $ids == '' ? getParameter('ids','str') :$ids;
        $startTime = $startTime == '' ?getParameter('startTime','str'):$startTime;
        $endTime = $endTime == '' ?getParameter('endTime','str'):$endTime;
        $platformInfo = D('Exercises_platform')->getPlatformInfo($platformId);
        $isEdit = $isEdit === false ?getParameter('isEdit','int',false):$isEdit;
        if(empty($platformInfo))
            $this->showMessage(500,'平台不存在');
        if($platformInfo['type'] != PLATFORM_GET)
        {
            $this->showMessage(500,'平台不属于获取类型');
        }

        //判断试题状态是否入库
        foreach(explode(',',$ids) as $key=>$val)
        {
            $result = D('Exercises_paper_processinfo')->getPaperInfo($val);
            if ($result['is_delete'] == STATE_DELETED)
            {$this->showMessage(500, "ID:$val".'试卷已被删除');}
            if ($result['is_lock'] == LOCKSTATE_LOCK)
            {$this->showMessage(500, "ID:$val".'试卷被锁定');}
            $status = D('Exercises_paper_processinfo')->getPaperStatus($val);
            if($status < EXERCISE_STATE_INBOUND)
            {
                $this->showMessage(500,"ID:$val 试卷未入库,无法发布");
            }

        }
        $errorInfo = '';
        $getResult = D('Exercises_platform')->getIsPublished($platformId,RESOURCETYPE_PAPER,$ids,$errorInfo);
        if(false === $isEdit) { //添加发布
            if (false == $getResult)
                $this->showMessage(500, $errorInfo);
            $result = D('Exercises_platform')->insertPublishInfo($platformId, RESOURCETYPE_PAPER, $ids, array('startTime' => $startTime, 'endTime' => $endTime));
            if (true === $result)
                $this->showMessage(200, '添加成功');
        }
        else
        {
            if (false !== $getResult)  //未发布
                $this->showMessage(500, $errorInfo);
            $result = D('Exercises_platform')->insertPublishInfo($platformId, RESOURCETYPE_PAPER, $ids, array('startTime' => $startTime, 'endTime' => $endTime),$isEdit);
            if(true === $result)
                $this->showMessage(200,'修改成功');
        }
        if(false === $result)
            $this->showMessage(500,'平台不属于获取类型');

    }
}
