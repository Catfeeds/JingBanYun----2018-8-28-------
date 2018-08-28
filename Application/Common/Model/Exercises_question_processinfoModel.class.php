<?php
namespace Common\Model;
use Think\Model;
use Common\Common\simple_html_dom;
class Exercises_question_processinfoModel extends Model{

    public    $model='';
    protected $tableName = 'exercises_question_processinfo';
    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);

    }
    private function getUpdateLastInfoSql($userId,$questionId)
    {
        $userName = M()->query("SELECT user_name FROM exercises_account WHERE id=$userId LIMIT 1");
        $userName = $userName[0]['user_name'];
        return "UPDATE exercises_question_processinfo SET lastoperator_id=$userId,lastoperator_name = '$userName',lastoperate_time=now() WHERE question_id=$questionId";
    }
    /*
     * 添加录入人员信息
     */

    public function addCreatorInfo($questionId,$creatorId,$creator)
    {
        $result = M()->execute("INSERT INTO exercises_question_processinfo (question_id,creator_id,creator_name,create_time) VALUES ($questionId,$creatorId,'$creator',now())");
        if(false === $result)
          return false;
        $result = M()->execute($this->getUpdateLastInfoSql($creatorId,$questionId));
        if(false === $result)
            return false;
        else
            return true;
    }

    /*
     * 更新录入人员信息
     */

    public function updateCreatorInfo($questionId,$creatorId,$creator)
    {
        $result = M()->execute("UPDATE exercises_question_processinfo SET creator_id=$creatorId,creator_name='$creator',create_time=now() WHERE question_id=$questionId;");
        if(false === $result)
            return false;
        $result = M()->execute($this->getUpdateLastInfoSql($creatorId,$questionId));
        if(false === $result)
            return false;
        else
            return true;
    }

    /*
     * 获取录入人员信息
     */

    public function getCreatorInfo($questionId)
    {
        $result = M()->query("SELECT creator_id,create_time FROM exercises_question_processinfo JOIN exercises_createexercise ON exercises_createexercise.id = exercises_question_processinfo.question_id WHERE is_delete = ".STATE_NORMAL ." AND question_id = $questionId LIMIT 1");
        return $result[0];
    }

    private function getConditionWhere($userId,$role,$condition=array())
    {
        $whereStr = ' AND exercises_createexercise.parent_id=0 ';
        if(empty($condition['exerciseMainCategory']))
        {
            $whereStr .= ' AND exercises_createexercise.types = '.EXERCISE_TYPE_NORMAL.' ';
        }
        else if($condition['exerciseMainCategory'] > 0)
        {
            $whereStr .= ' AND exercises_createexercise.types = '.EXERCISE_TYPE_ABNORMAL.' AND  exercises_createexercise.ordinary_type in ('.$condition['exerciseSubCategory'].') ';
        }

        foreach($condition as $key=>$val)
        {
            if(false !== $val) {
                if(!is_array($val))
                $val = str_replace('%','\%',mysql_escape_string($val));
                switch ($key) {
                    case 'publishNotExpired':
                        if ($val == 1)
                            $whereStr .= " AND exercises_platform_exercises.endtime >= now() ";
                        break;
                    case 'authStartTime' :
                        $whereStr .= " AND exercises_platform_exercises.starttime >= '$val' ";
                        break;
                    case 'authEndTime' :
                        $whereStr .= " AND exercises_platform_exercises.endtime <= '$val' ";
                        break;
                    case 'publishStatus' :
                        switch ($val) {
                            case 1:
                                $whereStr .= " AND exercises_platform_exercises.starttime > now() ";
                                break;
                            case 2:
                                $whereStr .= " AND exercises_platform_exercises.starttime <= now() ";
                                break;
                        }
                        break;
                    case 'platformId':
                        $whereStr .= " AND exercises_platform_exercises.platform_id = $val AND resource_type = " . RESOURCETYPE_EXERCISE . ' ';
                        break;
                    case 'isOfPaper' :
                        if (EXERCISE_NOT_BE_OFPAPER == $val) {
                            $whereStr .= " AND exercises_parper_concat.id is null ";
                        }
                        break;
                    case 'gradeId':
                        $whereStr .= " AND exercises_textbook_tree_info_createexercise.grade_id=$val ";
                        break;
                    case 'schoolTerm' :
                        $whereStr .= " AND exercises_textbook_tree_breviary.school_term = $val ";
                        break;
                    case 'questionId' :
                        $whereStr .= " AND exercises_createexercise.id in ($val) ";
                        break;
                    case 'courseId':
                        $whereStr .= " AND exercises_createexercise.subject=$val ";
                        break;
                    case 'paperId':
                        $whereStr .= " AND exercises_parper_concat.paper_id in($val) ";
                        break;
                    case 'paperName' :
                        $whereStr .= " AND exercises_create_paper.paper_name like '%$val%' ";
                        break;
                    case 'creator' :
                        $whereStr .= " AND exercises_question_processinfo.creator_name like '%$val%' ";
                        break;
                    case 'marker' :
                        $whereStr .= " AND exercises_question_processinfo.marker_name like '%$val%' ";
                        break;
                    case 'exerciseCategory':
                        $whereStr .= " AND exercises_createexercise.home_topic_type=$val ";
                        break;
                    case 'keyword':
                        $whereStr .= " AND exercises_createexercise.subject_name like '%$val%' ";
                        break;
                    case 'study_section':
                        $whereStr .= " AND exercises_createexercise.study_section=$val ";
                        break;
                    case 'creatorId':
                        $whereStr .= " AND exercises_question_processinfo.creator_id=$val ";
                        break;
                    case 'verifyId' :
                        $whereStr .= " AND exercises_question_processinfo.verifier_id=$val ";
                        break;
                    case 'verifyStartTime':
                        $whereStr .= " AND (exercises_question_processinfo.verify_time >= '$val' OR exercises_question_processinfo.reject_time >= '$val')";
                        break;
                    case 'verifyEndTime':
                        $whereStr .= " AND (exercises_question_processinfo.verify_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399) . "' " . " OR exercises_question_processinfo.reject_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399) . "') ";
                        break;
                    case 'markSubmitStartTime':
                        $whereStr .= " AND (exercises_question_processinfo.marksubmit_time >= '$val')";
                        break;
                    case 'markSubmitEndTime':
                        $whereStr .= " AND (exercises_question_processinfo.marksubmit_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399). "') ";
                        break;
                    case 'hasReprocessed' :
                        if ($val == 1) $whereStr .= " AND exercises_question_processinfo.reprocess_time <> 0  ";
                        break;
                    case 'hasMark' :
                        if ($val == 1) $whereStr .= " AND exercises_question_processinfo.mark_time <> 0  ";
                        break;
                    case 'hasMarkSubmit' :
                        if ($val == 1) $whereStr .= " AND exercises_question_processinfo.marksubmit_time <> 0  ";
                        break;
                    case 'reprocessStartTime':
                        $whereStr .= " AND exercises_question_processinfo.reprocess_time >= '$val' ";
                        break;
                    case 'reprocessEndTime':
                        $whereStr .= " AND exercises_question_processinfo.reprocess_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399) . "' ";
                        break;
                    case 'assignStartTime':
                        $whereStr .= " AND exercises_question_processinfo.assign_time >= '$val' ";
                        break;
                    case 'assignEndTime':
                        $whereStr .= " AND exercises_question_processinfo.assign_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399) . "' ";
                        break;
                    case 'markStartTime':
                        $whereStr .= " AND exercises_question_processinfo.mark_time >= '$val' ";
                        break;
                    case 'markEndTime':
                        $whereStr .= " AND exercises_question_processinfo.mark_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399) . "' ";
                        break;
                    case 'markVerifyStartTime':
                        $whereStr .= " AND exercises_question_processinfo.markverify_time >= '$val' ";
                        break;
                    case 'markVerifyEndTime':
                        $whereStr .= " AND exercises_question_processinfo.markverify_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399) . "' ";
                        break;
                    case 'inboundStartTime':
                        $whereStr .= " AND exercises_question_processinfo.inbound_time >= '$val' ";
                        break;
                    case 'inboundEndTime':
                        $whereStr .= " AND exercises_question_processinfo.inbound_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399) . "' ";
                        break;
                    case 'inputStartTime':
                        $whereStr .= " AND exercises_question_processinfo.create_time >= '$val' ";
                        break;
                    case 'inputEndTime':
                        $whereStr .= " AND exercises_question_processinfo.create_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399) . "' ";
                        break;
                    case 'lock' :
                        $whereStr .= " AND exercises_question_processinfo.is_lock = $val ";
                        break;
                    case 'status' :
                        if (is_array($val)) {
                            if (!empty($val[1])) {
                                switch ($val[0]) {
                                    case 'eq' :
                                        $whereStr .= " AND exercises_createexercise.status = {$val[1]}";
                                        break;
                                    case 'gt' :
                                        $whereStr .= " AND exercises_createexercise.status > {$val[1]}";
                                        break;
                                    case 'in' :
                                        $whereStr .= " AND exercises_createexercise.status in ({$val[1]})";
                                        break;
                                }
                            }
                        } else if (!empty($condition['status']))
                            $whereStr .= " AND exercises_createexercise.status = {$val}";
                        break;
                    case 'is_self':
                        if ($val == 1) {
                            switch ($role) {
                                case ROLE_INPUTEXERCISE:
                                    $whereStr .= " AND creator_id = $userId";
                                    break;
                                case ROLE_VERIFY:
                                    $whereStr .= " AND (verifier_id = $userId OR rejecter_id = $userId)";
                                    break;
                                case ROLE_ASSIGNEXERCISE:
                                    $whereStr .= " AND assigner_id = $userId";
                                    break;
                                case ROLE_MARKKNOWLEDGE:
                                    $whereStr .= " AND marker_id = $userId";
                                    break;
                                case ROLE_COTENTADMIN:
                                    $whereStr .= " AND inbounder_id = $userId";
                                    break;
                                case ROLE_EXERCISEADMIN:
                                    break;
                                case ROLE_OTHER:
                                    $whereStr .= " AND creator_id <> $userId ";
                                    break;
                                default:
                                    return false;
                            }
                        } else {
                            switch ($role) {
                                case ROLE_INPUTEXERCISE:
                                    $whereStr .= " AND creator_id <> $userId AND 1=1 ";
                                    $timeField = 'create_time';
                                    break;
                                default:
                                    break;
                            }
                        }
                        break;
                    default:
                        break;
                }
            }
        }
        return $whereStr;
    }

    private function getConditionJoin($condition)
    {
       $join = " JOIN exercises_createexercise ON exercises_createexercise.id = exercises_question_processinfo.question_id ".
           " JOIN exercises_course ON exercises_createexercise.subject = exercises_course.id ".
           " LEFT JOIN exercises_course topic ON exercises_createexercise.home_topic_type = topic.id ";

       if(EXERCISE_BEORNOT_OFPAPER == $condition['isOfPaper'])
       {
           $join .= " LEFT JOIN exercises_parper_concat ON exercises_parper_concat.exercise_id = exercises_createexercise.id ";
       }
       else if(EXERCISE_NOT_BE_OFPAPER == $condition['isOfPaper'])
       {
           $join .= " LEFT JOIN exercises_parper_concat ON exercises_parper_concat.exercise_id = exercises_createexercise.id ";
       }
       else {
           if ((isset($condition['paperId']) && false !== $condition['paperId']) || $condition['isOfPaper'] == EXERCISE_BE_OFPAPER) {
               $join .= " JOIN exercises_parper_concat ON exercises_parper_concat.exercise_id = exercises_createexercise.id ";
           }
           if (isset($condition['paperName']) && false !== $condition['paperName']) {
               if (strpos($join, 'JOIN exercises_parper_concat') === false)
                   $join .= " JOIN exercises_parper_concat ON exercises_parper_concat.exercise_id = exercises_createexercise.id ";
               $join .= " JOIN exercises_create_paper ON exercises_create_paper.id = exercises_parper_concat.paper_id ";
           }
       }
       if($condition['bNeedKnowledge'] == true)
       {

           $join .= " LEFT JOIN exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id = exercises_createexercise.id ";
           $join .= " LEFT JOIN exercises_textbook_tree_info chapter ON chapter.id = exercises_textbook_tree_info_createexercise.chapter ";
           $join .= " LEFT JOIN exercises_textbook_tree_info festival ON festival.id = exercises_textbook_tree_info_createexercise.festival ";
           $join .= " LEFT JOIN dict_grade ON dict_grade.id = exercises_textbook_tree_info_createexercise.grade_id ";
       }
       if(isset($condition['schoolTerm']) && $condition['schoolTerm'] != 0)
       {
           if (strpos($join, 'JOIN exercises_textbook_tree_info_createexercise') === false)
           $join .= " JOIN exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id = exercises_createexercise.id ";
           $join .= " JOIN exercises_textbook_tree_breviary ON exercises_textbook_tree_breviary.id = exercises_textbook_tree_info_createexercise.textbook_tree_info_id ";
       }


       if(isset($condition['publishNotExpired']) || isset($condition['authStartTime']) || isset($condition['authEndTime']) ||isset($condition['publishStatus']) || isset($condition['platformId']))
       {
           $join .= " JOIN exercises_platform_exercises ON exercises_platform_exercises.exercise_id = exercises_createexercise.id ";
           if(isset($condition['platformId']))
           $join .= " AND exercises_platform_exercises.platform_id = {$condition['platformId']} ";
       }
       else if (isset($condition['mixPlatformId']))
       {
           $join .= " LEFT JOIN exercises_platform_exercises pub ON pub.exercise_id = exercises_createexercise.id AND pub.endtime >= now() AND pub.platform_id = {$condition['mixPlatformId']} ";
       }
       return $join;
    }

    private function getOrderString($orderCondition)
    {
        $orderString = '';
        if($orderCondition == 'create_time')
            $orderString = ' ORDER BY exercises_question_processinfo.create_time desc ';
        else if($orderCondition == 'reject_time')
            $orderString = ' ORDER BY exercises_question_processinfo.reject_time desc ';
        else if($orderCondition == 'inbound_time')
            $orderString = ' ORDER BY exercises_question_processinfo.inbound_time desc ';
        else if($orderCondition == 'publish_time')
            $orderString = ' ORDER BY exercises_platform_exercises.create_at desc ';
        else if($orderCondition == 'status')
            $orderString = ' ORDER BY exercises_question_processinfo.status ASC,exercises_question_processinfo.id DESC ';
        return $orderString;
    }

    private function getGroupString($condition)
    {
        $groupStr = '';
        if($condition['bNeedKnowledge'] == true)
        {
            $groupStr = " GROUP BY exercises_question_processinfo.question_id ";
        }
        return $groupStr;
    }

    private function getFieldString($condition)
    {
        $fieldStr = 'exercises_createexercise.*,creator_name,exercises_course.name course_name,topic.name topic_name,is_lock,lastoperator_name,CONCAT(types,\',\',ordinary_type) category';
        if($condition['bNeedKnowledge'] == true)
        {
            $fieldStr .= " ,dict_grade.grade mark_grade,GROUP_CONCAT(dict_grade.grade) AS grade_name,GROUP_CONCAT(exercises_textbook_tree_info_createexercise.knowledge_name) AS knowledge_name  ";
        }
        if(EXERCISE_BEORNOT_OFPAPER == $condition['isOfPaper'])
        {
            $fieldStr .= " ,IFNULL(exercises_parper_concat.paper_id ,0) of_paper_id ";
        }
        if(isset($condition['publishNotExpired']))
        {
            $fieldStr .= " ,concat(date(exercises_platform_exercises.starttime),'至',date(exercises_platform_exercises.endtime)) authtime,exercises_platform_exercises.id pubid ";
        }
        if(isset($condition['userInfo']))
        {
            $fieldStr .= " ,exercises_question_processinfo.marker_name ";
        }
        if($condition['canPublishStatus'] === true || isset($condition['mixPlatformId']))
        {
            $fieldStr .= " ,(CASE WHEN pub.id is NULL THEN 1 ELSE 0 END) canpublish ";
        }
        return $fieldStr;
    }
    /*
     * 获取录入用户某种习题状态的数目
     */
    public function getQuestionCount($userId,$role,$condition=array(),$orderCondition = 'create_time')
    {
        $conditionWhere = $this->getConditionWhere($userId,$role,$condition);
        $conditionJoin = $this->getConditionJoin($condition);
        $result = M()->query("SELECT COUNT(DISTINCT exercises_question_processinfo.question_id) AS COUNT FROM exercises_question_processinfo ".
            $conditionJoin.
            " WHERE exercises_createexercise.is_delete = ".STATE_NORMAL." $conditionWhere LIMIT 1");
        return $result[0]['count'];
    }

    /*
    * 获取用户某种习题状态的列表
    */
    public function getQuestionList($userId=0,$role=0,$condition=array(),$orderCondition = 'create_time',$notIgnoreHTML = 0)
    {

        $conditionWhere = $this->getConditionWhere($userId,$role,$condition);
        $orderString = $this->getOrderString($orderCondition);
        $conditionJoin = $this->getConditionJoin($condition);
        $groupString = $this->getGroupString($condition);
        $fieldString = $this->getFieldString($condition);
        $limitStr = '';
        if(isset($condition['startIndex']) && $condition['pageSize'])
            $limitStr = ' LIMIT '.$condition['startIndex'] .',' .$condition['pageSize'].' ';
        $upperJoin = strtoupper($conditionJoin);
        //delete left join;
        $joinArray = explode('JOIN',$upperJoin);
        $onlyJoinStr = '';
        for($i=0;$i<sizeof($joinArray);$i++)
        {
            if(empty(trim($joinArray[$i])) )
                continue;
            if(false === strpos($joinArray[$i-1],"LEFT"))
                $onlyJoinStr .=  " JOIN " .strtolower(str_replace('LEFT','',$joinArray[$i]));
            if(false !== strpos($joinArray[$i],"LEFT") ) {
                $i++;
            }
        }

        $result = M()->query(" SELECT $fieldString FROM (SELECT exercises_question_processinfo.id,exercises_question_processinfo.status,inbound_time,creator_name,question_id,is_lock,lastoperator_name,marker_name,create_time,reject_time FROM exercises_question_processinfo ".
                              $conditionJoin.
                             " WHERE exercises_createexercise.is_delete = ".STATE_NORMAL." $conditionWhere $groupString $orderString $limitStr) exercises_question_processinfo $conditionJoin $groupString $orderString");

        $i=0;
        $replaceArray = array('img'=>'[图片]','video'=>'[音/视频]','audio'=>'[音频]');
        $html = new simple_html_dom();
        foreach($result as $key=>$val)
        {
            $result[$key]['rownum'] = ++$i;
            //add hidden status info to rownum field
            $result[$key]['rownum'] .= '<input type="hidden" id="exercise_status_'.$result[$key]['id'].'" value='.$result[$key]['status'].$result[$key]['is_delete'].' />';
            if($notIgnoreHTML == 0) {
                $htmlContent = htmlspecialchars_decode($result[$key]['subject_name']);
                $html->load('<html><body>' . $htmlContent . '</body></html>');
                foreach ($replaceArray as $key1 => $val1) {
                    $elementArray = $html->find($key1);
                    foreach ($elementArray as $id => $ele) {
                        $html->find($key1, $id)->outertext = $val1;
                    }
                }
                $result[$key]['subject_name'] = str_replace('ㄖ','_____',strip_tags($html->save()));
            }

        }
        return $result;
    }

    /*
     * 获取不同的录入人员
     */
    public function getDistinctCreator()
    {
        $result = M()->query('SELECT creator_id,exercises_account.user_name name FROM exercises_question_processinfo JOIN exercises_account ON exercises_question_processinfo.creator_id = exercises_account.id GROUP BY creator_id ');
        return $result;
    }

    /*
     * 获取习题信息
     */
    public function getQuestionInfo($id)
    {
        $id = intval($id);
        $result = M()->query("SELECT * FROM exercises_createexercise JOIN exercises_question_processinfo ON exercises_question_processinfo.question_id = exercises_createexercise.id WHERE exercises_createexercise.id = $id LIMIT 1");
        return $result[0];
    }

    /*
     * 设置习题状态
     */

    public function setQuestionState($id,$state,$userId)
    {
        $id = intval($id);
        $state = intval($state);
        $result = M()->execute("UPDATE exercises_createexercise SET status=$state WHERE id=$id");
        if(false === $result)
        {
            return false;
        }
        $result = M()->execute("UPDATE exercises_question_processinfo SET status=$state WHERE question_id=$id");
        if(false === $result)
        {
            return false;
        }
        $result = M()->execute($this->getUpdateLastInfoSql($userId,$id));
        if(false === $result) {
            return false;
        }
        return true;
    }

    /*
     * 记录习题在整个操作流程中各步骤的操作人
     * 输入参数：
     * questionId : 试题ID
     * userId : 用户ID
     * userName : 用户姓名
     * isWriteTime : 是否需要写入时间字段  (0 --不写入 1--写入)
     * funName : 操作流程(marksubmit--标引提交审核 verify-- 审核 assign--指派 reject--返工 mark--标引 markverify--标引确认 inbound-- 入库)
     * 返回值：
     * BOOL: 成功--true 失败--false
     */
    public function setStateUser($questionId,$userId,$userName,$isWriteTime=0,$funName)
    {
        $configArray = array(
            'verify' => array('id' =>'verifier_id','time'=>'verify_time'),
            'reprocess' => array('time'=>'reprocess_time'),
            'marksubmit' => array('time'=>'marksubmit_time'),
            'assign' => array('id' =>'assigner_id','time'=>'assign_time'),
            'reject' => array('id' =>'rejecter_id','time'=>'reject_time','name'=>'reject_name'),
            'mark' => array('id' =>'marker_id','time'=>'mark_time','name'=>'marker_name'),
            'markreject' => array('id' =>'markrejecter_id','time'=>'markreject_time','name'=>'markreject_name'),
//            'markverify' => array('id' =>'markverifier_id','time'=>'markverify_time'),
            'inbound' => array('id' =>'inbounder_id','time'=>'inbound_time'),

        );
        $updateInfo = array();
        if($configArray[$funName]['id'] && ($userId || 'mark' == $funName))
        {
            $updateInfo[] = $configArray[$funName]['id'] . '=' .$userId;
        }
        if($configArray[$funName]['name'] && ($userName || 'mark' == $funName))
        {
            $updateInfo[] = $configArray[$funName]['name'] . '=' .'\''.$userName.'\'';
        }
        if($configArray[$funName]['time'] && $isWriteTime == 1)
        {
            $updateInfo[] = $configArray[$funName]['time'] . '=now()';
        }
        $updateSql = implode(',',$updateInfo);
        $result = M()->execute("UPDATE exercises_question_processinfo SET $updateSql WHERE question_id = $questionId");
        if(false === $result) {
            return false;
        }
        $result = M()->execute($this->getUpdateLastInfoSql($userId,$questionId));
        if(false === $result) {
            return false;
        }
        return true;
    }

    /*
     * 获取锁定状态
     */
    public function getLockState($id)
    {
       $id=intval($id);
       $result = M()->query("SELECT is_lock FROM exercises_question_processinfo WHERE question_id=$id");
       return $result[0]['is_lock'];
    }

    /*
     * 获取锁定者
     */
    public function getLockerId($id)
    {
        $id=intval($id);
        $result = M()->query("SELECT locker_id FROM exercises_question_processinfo WHERE question_id=$id");
        return $result[0]['locker_id'];
    }
    /*
     * 设置锁定状态
     */
    public function setLockState($id,$userId,$state)
    {
        $result = M()->execute("UPDATE exercises_question_processinfo SET locker_id=$userId, is_lock=$state WHERE question_id = $id");
        if(false === $result) {
            return false;
        }
        return true;
    }

    /*
     * 获取试题状态
     */
    public function getQuestionStatus($id)
    {
        $id = intval($id);
        $result = M()->query("SELECT status FROM exercises_question_processinfo  WHERE question_id = $id LIMIT 1");
        return $result[0]['status'];
    }

    /*
     * 更新上架时间
     */
    public function updateUpTime($id)
    {
        $result = M()->execute("UPDATE exercises_question_processinfo SET up_time=now() WHERE question_id = $id");
        if(false === $result) {
            return false;
        }
        return true;
    }

    private function getExerciseInputStatisticWhere($condition)
    {
        $whereStr = ' WHERE exercises_createexercise.parent_id = 0 AND exercises_createexercise.is_delete='.STATE_NORMAL.' AND exercises_createexercise.types = '.EXERCISE_TYPE_NORMAL.' ';
        foreach($condition as $key=>$val)
        {
            if(!empty($val))
                switch($key)
                {
                    case 'courseId':$whereStr .= " AND exercises_createexercise.subject=$val ";break;
                    case 'study_section':$whereStr .= " AND exercises_createexercise.study_section=$val ";break;
                    default:break;
                }
        }
        return $whereStr;
    }
    private function getExerciseInputStatisticJoin($condition)
    {
        $joinStr = ' JOIN exercises_course ON exercises_createexercise.subject = exercises_course.id ';
        return $joinStr;
    }
    public function getExerciseInputStatisticCountBySection($condition)
    {
        $whereCondition = $this->getExerciseInputStatisticWhere($condition);
        $joinCondition = $this->getExerciseInputStatisticJoin($condition);
        $result = M()->query("SELECT COUNT(1) count FROM exercises_createexercise $joinCondition $whereCondition");
        return $result[0]['count'];
    }

    public function getExerciseInputStatisticCount($condition)
    {
        $whereCondition = $this->getExerciseInputStatisticWhere($condition);
        $joinCondition = $this->getExerciseInputStatisticJoin($condition);
        $result = M()->query("SELECT COUNT(1) count FROM (SELECT 1 FROM exercises_createexercise  $joinCondition $whereCondition GROUP BY subject,study_section) a");
        return $result[0]['count'];
    }
    public function getExerciseInputStatisticList($condition)
    {
        $whereCondition = $this->getExerciseInputStatisticWhere($condition);
        $joinCondition = $this->getExerciseInputStatisticJoin($condition);
        if(isset($condition['startIndex']) && $condition['pageSize'])
            $limitStr = ' LIMIT '.$condition['startIndex'] .',' .$condition['pageSize'].' ';
        $result = M()->query("SELECT exercises_course.name course_name,study_section,count(1) count FROM exercises_createexercise  $joinCondition $whereCondition GROUP BY subject,study_section $limitStr");
        return $result;
    }

    //指派统计
    private function getExerciseAssignStatisticWhere($condition)
    {
        $whereStr = ' WHERE exercises_createexercise.parent_id = 0 AND exercises_createexercise.is_delete='.STATE_NORMAL.' AND exercises_createexercise.types = '.EXERCISE_TYPE_NORMAL.' ';
        foreach($condition as $key=>$val)
        {
            if(!empty($val))
                switch($key)
                {
                    case 'courseId':$whereStr .= " AND exercises_createexercise.subject=$val ";break;
                    case 'study_section':$whereStr .= " AND exercises_createexercise.study_section=$val ";break;
                    default:break;
                }
        }
        return $whereStr;
    }
    private function getExerciseAssignStatisticJoin($condition)
    {
        $joinStr = ' JOIN exercises_course ON exercises_createexercise.subject = exercises_course.id ';
        $joinStr .= ' JOIN exercises_question_processinfo ON exercises_createexercise.id = exercises_question_processinfo.question_id ';
        return $joinStr;
    }
    public function getExerciseAssignStatistic()
    {
        $result = M()->query("SELECT ".
                        " SUM(assigner_id != 0) assignedcount, " .
                        " SUM(assigner_id = 0) unassignedcount, " .
                        " SUM(mark_time != 0) markedcount, " .
                        " ifnull(format(100*SUM(assigner_id != 0)/COUNT(1),2),'--') assignratio, " .
                        " ifnull(format(100*SUM(mark_time != 0)/COUNT(1),2),'--') markratio " .
                        "  FROM exercises_question_processinfo JOIN exercises_createexercise ON exercises_createexercise.id = exercises_question_processinfo.question_id 
                               AND exercises_createexercise.types = ".EXERCISE_TYPE_NORMAL.
                             ' AND exercises_createexercise.parent_id = 0 '.
                             ' AND exercises_createexercise.is_delete='.STATE_NORMAL
        );
        return $result;
    }

    public function getExerciseAssignStatisticCount($condition)
    {
        $whereCondition = $this->getExerciseAssignStatisticWhere($condition);
        $joinCondition = $this->getExerciseAssignStatisticJoin($condition);
        $result = M()->query("SELECT COUNT(1) count FROM (SELECT 1 FROM exercises_createexercise  $joinCondition $whereCondition GROUP BY subject,study_section) a");
        return $result[0]['count'];
    }
    public function getExerciseAssignStatisticList($condition)
    {
        $whereCondition = $this->getExerciseAssignStatisticWhere($condition);
        $joinCondition = $this->getExerciseAssignStatisticJoin($condition);
        if(isset($condition['startIndex']) && $condition['pageSize'])
            $limitStr = ' LIMIT '.$condition['startIndex'] .',' .$condition['pageSize'].' ';
        $result = M()->query("SELECT exercises_course.name course_name,study_section," .
                             " SUM(assigner_id != 0) assignedcount, " .
                             " SUM(assigner_id = 0) unassignedcount, " .
                             " SUM(mark_time != 0) markedcount, " .
                             " ifnull(format(100*SUM(assigner_id != 0)/COUNT(1),2),'--') assignratio, " .
                             " ifnull(format(100*SUM(mark_time != 0)/COUNT(1),2),'--') markratio " .
                             " FROM exercises_createexercise  $joinCondition $whereCondition GROUP BY subject,study_section $limitStr");
        return $result;
    }

    //标引统计
    private function getExerciseMarkStatisticWhere($condition)
    {
        $whereStr = ' WHERE exercises_createexercise.parent_id = 0 AND exercises_createexercise.is_delete='.STATE_NORMAL;
        foreach($condition as $key=>$val)
        {
            if(!empty($val))
                switch($key)
                {
                    case 'gradeId':$whereStr .= " AND exercises_textbook_tree_info_createexercise.grade_id=$val ";break;
                    case 'courseId':$whereStr .= " AND exercises_textbook_tree_info_createexercise.course_id=$val ";break;
                    case 'schoolTerm':$whereStr .= " AND exercises_textbook_tree_breviary.school_term=$val ";break;
                    case 'study_section':$whereStr .= " AND exercises_createexercise.study_section=$val ";break;
                    default:break;
                }
        }
        return $whereStr;
    }
    private function getExerciseMarkStatisticJoin($condition)
    {
        $joinStr = ' JOIN exercises_createexercise ON exercises_createexercise.id = exercises_textbook_tree_info_createexercise.exercises_createexercise_id ';
        $joinStr .= ' JOIN exercises_course ON exercises_course.id = exercises_textbook_tree_info_createexercise.course_id ';
        $joinStr .= ' JOIN dict_grade ON dict_grade.id = exercises_textbook_tree_info_createexercise.grade_id ';
        $joinStr .= ' JOIN exercises_textbook_version ON exercises_textbook_version.id = exercises_textbook_tree_info_createexercise.version_id ';
        $joinStr .= ' JOIN exercises_textbook_tree_breviary ON exercises_textbook_tree_breviary.id = exercises_textbook_tree_info_createexercise.textbook_tree_info_id ';
        return $joinStr;
    }


    public function getExerciseMarkStatisticCount($condition)
    {
        $whereCondition = $this->getExerciseMarkStatisticWhere($condition);
        $joinCondition = $this->getExerciseMarkStatisticJoin($condition);
        $result = M()->query("SELECT COUNT(1) count FROM (SELECT COUNT(DISTINCT exercises_createexercise_id) FROM exercises_textbook_tree_info_createexercise  $joinCondition $whereCondition GROUP BY exercises_course.id,study_section,school_term) a");
        return $result[0]['count'];
    }
    public function getExerciseMarkStatisticList($condition)
    {
        $whereCondition = $this->getExerciseMarkStatisticWhere($condition);
        $joinCondition = $this->getExerciseMarkStatisticJoin($condition);
        if(isset($condition['startIndex']) && $condition['pageSize'])
            $limitStr = ' LIMIT '.$condition['startIndex'] .',' .$condition['pageSize'].' ';
        $result = M()->query("SELECT exercises_textbook_version.version_name,dict_grade.grade grade_name,exercises_course.name course_name,school_term,COUNT(DISTINCT exercises_createexercise_id) count FROM exercises_textbook_tree_info_createexercise  $joinCondition $whereCondition GROUP BY exercises_course.id,study_section,school_term $limitStr");
        return $result;
    }

    /*
     *多媒体作业录入筛选结果列表
     */
    public function getListByScreening($userId,$role,$condition=array(),$tip=1){
        $whereCondition = $this->getExerciseWhere($userId,$role,$condition);
        if($tip === 1){
            if(isset($condition['startIndex']) && $condition['pageSize']){
                $limitStr = ' LIMIT '.$condition['startIndex'] .',' .$condition['pageSize'].' ';
            }
        }
        $result = M()->query("SELECT
	exercises_createexercise.id,
	exercises_question_processinfo.create_time,
	exercises_question_processinfo.creator_name,
	exercises_createexercise.status,
	exercises_createexercise.ordinary_type,
	exercises_createexercise.words,
	exercises_createexercise.subject_name,
	exercises_textbook_tree_info_createexercise.version_name,
	exercises_textbook_tree_info_createexercise.course_name,
	exercises_textbook_tree_info_createexercise.grade_name,
	exercises_textbook_tree_info_createexercise.section_name,
	exercises_textbook_tree_info_createexercise.chapter_name,
	exercises_textbook_tree_info_createexercise.festival_name
FROM
	exercises_createexercise
JOIN exercises_question_processinfo ON exercises_question_processinfo.question_id = exercises_createexercise.id
JOIN exercises_textbook_tree_info_createexercise  ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id = exercises_createexercise.id
WHERE
	exercises_createexercise.is_delete = ".STATE_NORMAL." AND exercises_createexercise.ordinary_type IS NOT NULL $whereCondition
GROUP BY
	exercises_createexercise.id
ORDER BY
	exercises_createexercise.create_at DESC $limitStr"); //echo M()->getLastSql();die;
            foreach($result as $key=>$val)
            {
                $result[$key]['rownum'] = ++$i;
                $result[$key]['count'] = count($result);
            }
        return $result;
    }

    /*
     *多媒体作业统计结果列表
     */
    public function getListByScreeningOfStatistical($userId,$role,$condition=array(),$group_order='exercises_createexercise.id',$tip=1){
        if($tip === 1){
            if(isset($condition['startIndex']) && $condition['pageSize']){
                $limitStr = ' LIMIT '.$condition['startIndex'] .',' .$condition['pageSize'].' ';
            }
        }
        $whereCondition = $this->getExerciseWhere($userId,$role,$condition,$tip='a');
        $result = M()->query("SELECT
	count(
		DISTINCT exercises_createexercise.id
	) countByFestival,
	exercises_createexercise.id,
	exercises_question_processinfo.create_time,
	exercises_question_processinfo.creator_name,
	exercises_createexercise. STATUS,
	exercises_createexercise.ordinary_type,
	exercises_createexercise.words,
	exercises_createexercise.subject_name,
	a.version_name,
	a.course_name,
	a.grade_name,
	a.section_name,
	a.chapter_name,
	a.festival_name
FROM
	exercises_createexercise
JOIN exercises_question_processinfo ON exercises_question_processinfo.question_id = exercises_createexercise.id
JOIN exercises_textbook_tree_info_createexercise a ON a.exercises_createexercise_id = exercises_createexercise.id
WHERE
	exercises_createexercise.is_delete = 2
AND exercises_createexercise.ordinary_type IS NOT NULL $whereCondition
GROUP BY
	$group_order
ORDER BY
	exercises_createexercise.create_at DESC $limitStr"); //echo M()->getLastSql();die;
        foreach($result as $key=>$val)
        {
            $result[$key]['rownum'] = ++$i;
            $result[$key]['count'] = count($result);
        }
        return $result;
    }
    /*
     *语音题where条件拼接
     */
    public function getExerciseWhere($userId,$role,$condition=array(),$tip='exercises_textbook_tree_info_createexercise'){
        $whereStr = '';
        foreach($condition as $key=>$val)
        {
            if(false !== $val) {
                if(!is_array($val))
                    $val = str_replace('%','\%',mysql_escape_string($val));
                switch ($key) {
                    case 'grade':
                        $whereStr .= " AND $tip.grade_id=$val ";
                        break;
                    case 'school_term' :
                        $whereStr .= " AND $tip.section_id = $val ";//分册
                        break;
                    case 'courseId':
                        $whereStr .= " AND $tip.course_id=$val ";
                        break;
                    case 'version':
                        $whereStr .= " AND $tip.version_id=$val ";
                        break;
                    case 'chapter':
                        $whereStr .= " AND $tip.chapter=$val ";
                        break;
                    case 'festival':
                        $whereStr .= " AND $tip.festival=$val ";
                        break;
                    case 'knowledge':
                        $whereStr .= " AND $tip.knowledge_id=$val ";
                        break;
                    case 'keyword':
                        $whereStr .= " AND exercises_createexercise.words like '%$val%' ";
                        break;
                    case 'creatorId':
                        $whereStr .= " AND exercises_question_processinfo.creator_id=$val ";
                        break;
                    case 'inputStartTime':
                        $whereStr .= " AND exercises_question_processinfo.create_time >= '$val' ";
                        break;
                    case 'inputEndTime':
                        $whereStr .= " AND exercises_question_processinfo.create_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399) . "' ";
                        break;
                    case 'type':
                        $whereStr .= " AND exercises_createexercise.ordinary_type = $val ";
                        break;
                    case 'status':
                        $whereStr .= " AND exercises_createexercise.status = $val ";
                        break;
                    case 'is_self':
                        if ($val == 1) {
                            switch ($role) {
                                case ROLE_INPUTEXERCISE:
                                    $whereStr .= " AND creator_id = $userId";
                                    break;
                                case ROLE_VERIFY:
                                    $whereStr .= " AND (verifier_id = $userId OR rejecter_id = $userId)";
                                    break;
                                case ROLE_ASSIGNEXERCISE:
                                    $whereStr .= " AND (assigner_id = $userId)";
                                    break;
                                case ROLE_MARKKNOWLEDGE:
                                    $whereStr .= " AND marker_id = $userId";
                                    break;
                                case ROLE_COTENTADMIN:
                                    $whereStr .= " AND inbounder_id = $userId";
                                    break;
                                case ROLE_EXERCISEADMIN:
                                    break;
                                case ROLE_OTHER:
                                    $whereStr .= " AND creator_id <> $userId ";
                                    break;
                                default:
                                    return false;
                            }
                        } else {
                            switch ($role) {
                                case ROLE_INPUTEXERCISE:
                                    $whereStr .= " AND creator_id <> $userId AND 1=1 ";
                                    $timeField = 'create_time';
                                    break;
                                default:
                                    break;
                            }
                        }
                        break;
                    default:
                        break;
                }
            }
        }
        return $whereStr;

    }
}