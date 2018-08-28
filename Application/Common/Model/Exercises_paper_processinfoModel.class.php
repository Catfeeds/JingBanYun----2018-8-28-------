<?php
namespace Common\Model;
use Think\Model;

class Exercises_paper_processinfoModel extends Model{

    public    $model='';
    protected $tableName = 'exercises_paper_processinfo';
    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);

    }
    private function getUpdateLastInfoSql($userId,$paperId)
    {
        return "UPDATE exercises_paper_processinfo SET lastoperator_id=$userId,lastoperate_time=now() WHERE paper_id=$paperId";
    }
    /*
     * 添加录入人员信息
     */

    public function addCreatorInfo($paperId,$creatorId,$creator)
    {
        $result = M()->execute("INSERT INTO exercises_paper_processinfo (paper_id,creator_id,creator_name,create_time) VALUES ($paperId,$creatorId,'$creator',now())");
        if(false === $result)
            return false;
        $result = M()->execute($this->getUpdateLastInfoSql($creatorId,$paperId));
        if(false === $result)
            return false;
        else
            return true;
    }

    /*
     * 更新录入人员信息
     */

    public function updateCreatorInfo($paperId,$creatorId,$creator)
    {
        $result = M()->execute("UPDATE exercises_paper_processinfo SET creator_id=$creatorId,creator_name='$creator',create_time=now() WHERE paper_id=$paperId");
        if(false === $result)
            return false;
        $result = M()->execute($this->getUpdateLastInfoSql($creatorId,$paperId));
        if(false === $result)
            return false;
        else
            return true;
    }

    /*
     * 获取录入人员信息
     */

    public function getCreatorInfo($paperId)
    {
        $result = M()->query("SELECT creator_id,create_time FROM exercises_paper_processinfo JOIN exercises_create_paper ON exercises_create_paper.id = exercises_paper_processinfo.paper_id WHERE is_delete = ".DELETE_STATUS_FALSE." AND paper_id = $paperId LIMIT 1");
        return $result[0];
    }

    private function getConditionWhere($userId,$role,$condition=array())
    {
       $whereStr ='';
       foreach($condition as $key=>$val)
       {
           if(false !== $val) {
               if(!is_array($val))
               $val = str_replace('%', '\%', mysql_escape_string($val));
               switch ($key) {
                   case 'publishNotExpired':
                       if ($val == 1)
                           $whereStr .= " AND exercises_platform_exercises.endtime >= now() ";
                       break;
                   case 'authStartTime' :
                       $whereStr .= " AND exercises_platform_exercises.starttime >= $val ";
                       break;
                   case 'authEndTime' :
                       $whereStr .= " AND exercises_platform_exercises.endtime <= $val ";
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
                       $whereStr .= " AND exercises_platform_exercises.platform_id = $val AND resource_type = " . RESOURCETYPE_PAPER . ' ';
                       break;
                   case 'paperId':
                       $whereStr .= " AND exercises_create_paper.id in ($val) ";
                       break;
                   case 'courseId':
                       $whereStr .= " AND exercises_create_paper.subject=$val ";
                       break;
                   case 'schoolTerm' :
                       $whereStr .= " AND exercises_create_paper.section=$val ";
                       break;
                   case 'gradeId':
                       $whereStr .= " AND exercises_create_paper.grade=$val ";
                       break;
                   case 'section':
                       $whereStr .= " AND (exercises_create_paper.period = $val ) ";
                       break;
                   case 'paperName':
                   case 'keyword':
                       $whereStr .= " AND exercises_create_paper.paper_name like '%$val%' ";
                       break;
                   case 'provinceId':
                       $whereStr .= " AND exercises_create_paper.city_id=$val ";
                       break;
                   case 'paperCategory':
                       $whereStr .= " AND exercises_create_paper.paper_type=$val ";
                       break;
                   case 'year':
                       $whereStr .= " AND exercises_create_paper.year=$val ";
                       break;
                   case 'creator' :
                       $whereStr .= " AND exercises_paper_processinfo.creator_name like '%$val%' ";
                       break;
                   case 'creatorId':
                       $whereStr .= " AND exercises_paper_processinfo.creator_id=$val ";
                       break;
                   case 'verifyId' :
                       $whereStr .= " AND exercises_paper_processinfo.verifier_id=$val ";
                       break;
                   case 'inputStartTime':
                       $whereStr .= " AND exercises_paper_processinfo.create_time >= '$val' ";
                       break;
                   case 'inputEndTime':
                       $whereStr .= " AND exercises_paper_processinfo.create_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399) . "' ";
                       break;
                   case 'verifyStartTime':
                       $whereStr .= " AND (exercises_paper_processinfo.verify_time >= '$val' OR exercises_paper_processinfo.reject_time >= '$val')";
                       break;
                   case 'verifyEndTime':
                       $whereStr .= " AND (exercises_paper_processinfo.verify_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399) . "' " . " OR exercises_paper_processinfo.reject_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399) . "') ";
                       break;
                   case 'hasReprocessed' :
                       if ($val == 1) $whereStr .= " AND exercises_paper_processinfo.reprocess_time is not null  ";
                       break;
                   case 'reprocessStartTime':
                       $whereStr .= " AND exercises_paper_processinfo.reprocess_time >= '$val' ";
                       break;
                   case 'reprocessEndTime':
                       $whereStr .= " AND exercises_paper_processinfo.reprocess_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399) . "' ";
                       break;
                   case 'assignStartTime':
                       $whereStr .= " AND exercises_paper_processinfo.assign_time >= '$val' ";
                       break;
                   case 'assignEndTime':
                       $whereStr .= " AND exercises_paper_processinfo.assign_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399) . "' ";
                       break;
                   case 'markStartTime':
                       $whereStr .= " AND exercises_paper_processinfo.mark_time >= '$val' ";
                       break;
                   case 'markEndTime':
                       $whereStr .= " AND exercises_paper_processinfo.mark_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399) . "' ";
                       break;
                   case 'markVerifyStartTime':
                       $whereStr .= " AND exercises_paper_processinfo.markverify_time >= '$val' ";
                       break;
                   case 'markVerifyEndTime':
                       $whereStr .= " AND exercises_paper_processinfo.markverify_time <= '" . date('Y-m-d H:m:s', strtotime($val) + 86399) . "' ";
                       break;
                   case 'lock' :
                       $whereStr .= " AND exercises_paper_processinfo.is_lock = $val ";
                       break;
                   case 'status' :
                       if (is_array($val)) {
                           if (!empty($val[1])) {
                               switch ($val[0]) {
                                   case 'eq' :
                                       $whereStr .= " AND exercises_create_paper.status = {$val[1]}";
                                       break;
                                   case 'gt' :
                                       $whereStr .= " AND exercises_create_paper.status > {$val[1]}";
                                       break;
                                   case 'in' :
                                       $whereStr .= " AND exercises_create_paper.status in ({$val[1]})";
                                       break;
                               }
                           }
                       } else if (!empty($condition['status']))
                           $whereStr .= " AND exercises_create_paper.status = {$val}";
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
                               case ROLE_MARKKNOWLEDGE:
                                   $whereStr .= " AND marker_id = $userId";
                                   break;
                               case ROLE_COTENTADMIN:
                                   $whereStr .= " AND markverifier_id = $userId";
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
                                   $whereStr .= " AND creator_id <> $userId ";
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
    private function getOrderString($orderCondition)
    {
        $orderString = '';
        if($orderCondition == 'create_time')
            $orderString = ' ORDER BY exercises_paper_processinfo.create_time desc ';
        else if($orderCondition == 'reject_time')
            $orderString = ' ORDER BY exercises_paper_processinfo.reject_time desc ';
        else if($orderCondition == 'inbound_time')
            $orderString = ' ORDER BY exercises_paper_processinfo.inbound_time desc ';
        else if($orderCondition == 'publish_time')
            $orderString = ' ORDER BY exercises_platform_exercises.create_at desc ';
        return $orderString;
    }
    private function getConditionJoin($condition)
    {

        $join = " JOIN exercises_create_paper ON exercises_create_paper.id = exercises_paper_processinfo.paper_id ".
            " LEFT JOIN exercises_course ON exercises_create_paper.subject = exercises_course.id ".
            " LEFT JOIN dict_grade ON dict_grade.id = exercises_create_paper.grade ".
            " LEFT JOIN dict_citydistrict ON dict_citydistrict.id = exercises_create_paper.city_id";

        if(isset($condition['publishNotExpired']) || isset($condition['authStartTime']) || isset($condition['authEndTime']) ||isset($condition['publishStatus']) || isset($condition['platformId']))
        {
            $join .= " JOIN exercises_platform_exercises ON exercises_platform_exercises.paper_id = exercises_create_paper.id ";
        }
        else if (isset($condition['mixPlatformId']))
        {
            $join .= " LEFT JOIN exercises_platform_exercises pub ON pub.paper_id = exercises_create_paper.id AND pub.endtime >= now() AND pub.platform_id = {$condition['mixPlatformId']} ";
        }
        if(isset($condition['needTotalScore']) && false !== $condition['needTotalScore'])
        {
            $join.= " LEFT JOIN exercises_parper_concat ON exercises_parper_concat.paper_id = exercises_paper_processinfo.paper_id";
            $join.= " LEFT JOIN exercises_createexercise ON exercises_createexercise.id = exercises_parper_concat.exercise_id";
        }
        return $join;
    }
    /*
     * 获取录入用户某种试卷状态的数目
     */
    public function getPaperCount($userId,$role,$condition=array(),$orderCondition = 'create_time')
    {
        $conditionJoin = $this->getConditionJoin($condition);
        $conditionWhere = $this->getConditionWhere($userId,$role,$condition);
        $result = M()->query("SELECT COUNT(1) COUNT FROM exercises_paper_processinfo ".
            $conditionJoin.
            " WHERE exercises_create_paper.is_delete = ".STATE_NORMAL." $conditionWhere LIMIT 1");
        return $result[0]['count'];
    }

    /*
    * 获取录入用户某种试卷状态的列表
    */
    public function getPaperList($userId,$role,$condition=array(),$orderCondition = 'create_time')
    {
        $conditionJoin = $this->getConditionJoin($condition);
        $conditionWhere = $this->getConditionWhere($userId,$role,$condition);
        $orderString = $this->getOrderString($orderCondition);
        if(isset($condition['startIndex']) && $condition['pageSize'])
            $limitStr = ' LIMIT '.$condition['startIndex'] .',' .$condition['pageSize'].' ';
        $fieldStr = 'exercises_create_paper.*,creator_name,exercises_course.name course_name,dict_grade.grade grade_name,dict_citydistrict.name province,paper_num module_count,is_lock';
        if(isset($condition['needTotalScore'])) {
            $groupStr = " GROUP BY exercises_create_paper.id ";
            $fieldStr .= ",ifnull(SUM(exercises_createexercise.score),0) truetotalscore";
        }
        if(isset($condition['publishNotExpired']))
        {
            $fieldStr .= " ,concat(date(exercises_platform_exercises.starttime),'至',date(exercises_platform_exercises.endtime)) authtime,exercises_platform_exercises.id pubid ";
        }
        if(isset($condition['mixPlatformId']))
        {
            $fieldStr .= " ,(CASE WHEN pub.id is NULL THEN 1 ELSE 0 END) canpublish ";
        }

        //TODO:sql join adjustment -- first select paperids by order and limit then join tables
        $result = M()->query(" SELECT $fieldStr FROM exercises_paper_processinfo ".
        $conditionJoin.
            " WHERE exercises_create_paper.is_delete = ".STATE_NORMAL." $conditionWhere $groupStr $orderString $limitStr");
        $i=0;
        foreach($result as $key=>$val)
        {
            $result[$key]['rownum'] = ++$i;
            //add hidden status info to rownum field
            $result[$key]['rownum'] .= '<input type="hidden" id="paper_status_'.$result[$key]['id'].'" value='.$result[$key]['status'].$result[$key]['is_delete'].' />';
        }
        return $result;

    }

    /*
     * 获取不同的录入人员
     */
    public function getDistinctCreator()
    {
        $result = M()->query('SELECT creator_id,exercises_account.user_name name FROM exercises_paper_processinfo JOIN exercises_account ON exercises_paper_processinfo.creator_id = exercises_account.id GROUP BY creator_id ');
        return $result;
    }

    /*
     * 获取锁定状态
     */
    public function getLockState($id)
    {
        $id=intval($id);
        $result = M()->query("SELECT is_lock FROM exercises_paper_processinfo WHERE paper_id=$id");
        return $result[0]['is_lock'];
    }

    /*
     * 获取锁定者
     */
    public function getLockerId($id)
    {
        $id=intval($id);
        $result = M()->query("SELECT locker_id FROM exercises_paper_processinfo WHERE paper_id=$id");
        return $result[0]['locker_id'];
    }

    /*
     * 设置锁定状态
     */
    public function setLockState($id,$userId,$state)
    {
        $result = M()->execute("UPDATE exercises_paper_processinfo SET locker_id = $userId, is_lock=$state WHERE paper_id = $id");
        if(false === $result) {
            return false;
        }
        return true;
    }


    /*
     * 获取试卷信息
     */
    public function getPaperInfo($id)
    {
        $id = intval($id);
        $result = M()->query("SELECT * FROM exercises_create_paper JOIN exercises_paper_processinfo ON exercises_paper_processinfo.paper_id = exercises_create_paper.id WHERE exercises_create_paper.id = $id LIMIT 1");
        return $result[0];
    }

    /*
     * 设置试卷状态
     */

    public function setPaperState($id,$state,$userId)
    {
        $id = intval($id);
        $state = intval($state);
        $result = M()->execute("UPDATE exercises_create_paper SET status=$state WHERE id=$id");
        if(false === $result)
        {
            return false;
        }
        if($state == EXERCISE_STATE_INBOUND)
            $result = M()->execute("UPDATE exercises_paper_processinfo SET status=$state,inbound_time = now() WHERE paper_id=$id");
        else
            $result = M()->execute("UPDATE exercises_paper_processinfo SET status=$state WHERE paper_id=$id");
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
     * 记录试卷在整个操作流程中各步骤的操作人
     * 输入参数：
     * paperId : 试卷ID
     * userId : 用户ID
     * userName : 用户姓名
     * isWriteTime : 是否需要写入时间字段  (0 --不写入 1--写入)
     * funName : 操作流程(verify-- 审核  reject--返工 )
     * 返回值：
     * BOOL: 成功--true 失败--false
     */
    public function setStateUser($paperId,$userId,$userName,$isWriteTime=0,$funName)
    {
        $configArray = array(
            'verify' => array('id' =>'verifier_id','time'=>'verify_time'),
            'reprocess' => array('time'=>'reprocess_time'),
            'reject' => array('id' =>'rejecter_id','time'=>'reject_time','name'=>'reject_name'),
        );
        $updateInfo = array();
        if($configArray[$funName]['id'] && $userId)
        {
            $updateInfo[] = $configArray[$funName]['id'] . '=' .$userId;
        }
        if($configArray[$funName]['name'] && $userName)
        {
            $updateInfo[] = $configArray[$funName]['name'] . '=' .'\''.$userName.'\'';
        }
        if($configArray[$funName]['time'] && $isWriteTime == 1)
        {
            $updateInfo[] = $configArray[$funName]['time'] . '=now()';
        }
        $updateSql = implode(',',$updateInfo);
        $result = M()->execute("UPDATE exercises_paper_processinfo SET $updateSql WHERE paper_id = $paperId");
        if(false === $result) {
            return false;
        }
        $result = M()->execute($this->getUpdateLastInfoSql($userId,$paperId));
        if(false === $result) {
            return false;
        }
        return true;
    }

    /*
     * 更新上架时间
     */
    public function updateUpTime($id)
    {
        $result = M()->execute("UPDATE exercises_paper_processinfo SET up_time=now() WHERE paper_id = $id");
        if(false === $result) {
            return false;
        }
        return true;
    }

    /*
     * 获取试卷状态
     */
    public function getPaperStatus($id)
    {
        $id = intval($id);
        $result = M()->query("SELECT status FROM exercises_paper_processinfo  WHERE paper_id = $id LIMIT 1");
        return $result[0]['status'];
    }
}