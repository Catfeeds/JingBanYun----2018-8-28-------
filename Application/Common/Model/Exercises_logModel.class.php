<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/9/7
 * Time: 11:42
 */
namespace Common\Model;
use Think\Model;

class Exercises_logModel extends Model
{
    public    $model='';
    protected $tableName = 'exercises_log';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }
    /*
     *插入LOG
     *输入参数：习题ID 试卷ID 操作内容 IP地址 操作人ID 操作人姓名 备注 被操作人ID 被操作人姓名 异常行为标志（1--异常 2--正常）
     *输出参数：BOOL型成功状态
     */
    public function insertLog($questionId=0,$paperId=0,$operationName='',$ipAddress='',$operatorId=0,$operatorName='',$comment='',$poperatorId=0,$poperatorName='',$status=BEHAVIOR_NORMAL)
    {
        $userInfo = D('Exercises_account')->getUserInfo($poperatorId);
        if(strpos($ipAddress,'.') !== false)
            $ipAddress = sprintf('%u',ip2long($ipAddress));
        $result = M()->execute(" INSERT INTO exercises_log (question_id,paper_id,oper_name,ip_address,operator_id,operator_name,poperator_id,poperator_account,poperator_name,comment,error_status) ".
                              " VALUES ($questionId,$paperId,'$operationName',$ipAddress,$operatorId,'$operatorName',$poperatorId,'{$userInfo['account']}','$poperatorName','$comment',$status)");
        if(false === $result)
            return false;
        else
        return true;
    }
    /*
     * 获取习题LOG
     */
    public function getQuestionLog($questionId)
    {
        $result = M()->query("SELECT @rownum:=@rownum+1 AS rownum,oper_name,INET_NTOA(ip_address),operator_name,oper_time,poperator_name,comment FROM (SELECT @rownum:=0) r,exercises_log".
                             " WHERE question_id = $questionId");
        return $result;
    }

    /*
     * 获取习题LOG(含有用户账号)
     */
    public function getQuestionLogWithAccount($questionId)
    {
        $result = M()->query("SELECT @rownum:=@rownum+1 AS rownum,oper_name,INET_NTOA(ip_address),operator_name,oper_time,poperator_name,comment,exercises_account.account FROM (SELECT @rownum:=0) r,exercises_log,exercises_account".
            " WHERE question_id = $questionId AND operator_id = exercises_account.id");
        return $result;
    }


    /*
     * 获取试卷LOG
     */
    public function getPaperLog($paperId)
    {
        $result = M()->query("SELECT @rownum:=@rownum+1 AS rownum,oper_name,INET_NTOA(ip_address),operator_name,oper_time,poperator_name,comment FROM (SELECT @rownum:=0) r,exercises_log".
            " WHERE paper_id = $paperId");
        return $result;
    }

    /*
         * 获取试卷LOG(含有用户账号)
         */
    public function getPaperLogWithAccount($paperId)
    {
        $result = M()->query("SELECT @rownum:=@rownum+1 AS rownum,oper_name,INET_NTOA(ip_address),operator_name,oper_time,poperator_name,comment,exercises_account.account FROM (SELECT @rownum:=0) r,exercises_log,exercises_account".
            " WHERE paper_id = $paperId AND operator_id = exercises_account.id");
        return $result;
    }
    private function getConditionWhere($condition)
    {
        $whereStr = ' WHERE 1=1 ';
        foreach($condition as $key=>$val) {
            if (!empty($val))
                switch ($key) {
                    case 'startDate': $whereStr .= " AND  oper_time >= '$val' ";break;
                    case 'endDate': $whereStr .= " AND oper_time <= '".date('Y-m-d H:m:s',strtotime($val)+86399)."' ";break;
                    case 'behavior': $whereStr .= " AND oper_name = '$val' ";break;
                    case 'userId': $whereStr .= " AND operator_id = $val ";break;
                    default :break;
                }
        }
        return $whereStr;
    }

    public function getUserLogCount($condition)
    {
       if(empty($condition['userId']))
            return array();
       $whereCondition = $this->getConditionWhere($condition);
       $result = M()->query("SELECT COUNT(1) COUNT FROM exercises_log $whereCondition");
       return $result[0]['count'];
    }

    /*
     * 获取某用户LOG
     */
    public function getUserLogList($condition)
    {
        if(empty($condition['userId']))
            return array();
        $whereCondition = $this->getConditionWhere($condition);
        if(isset($condition['startIndex']) && $condition['pageSize'])
            $limitStr = ' LIMIT '.$condition['startIndex'] .',' .$condition['pageSize'].' ';
        $result = M()->query("SELECT question_id,paper_id,oper_name,oper_time,INET_NTOA(ip_address) ip FROM exercises_log $whereCondition ORDER BY oper_time DESC $limitStr");
        return $result;
    }
    /*
     *用户行为管理（试题试卷知识树）一
     */
    public function getUserBehaviorResources1(&$count, $where = array(), $pageIndex = 1, $pageSize = 20){
        $count = M('exercises_account')
            ->join('exercises_auth_permissions ON exercises_account.role = exercises_auth_permissions.id')
            ->where($where)
            ->count();

        $resources = M('exercises_account')
            ->field('exercises_account.*,exercises_auth_permissions.name r_name,ea.user_name ea_name')
            ->join('exercises_auth_permissions ON exercises_account.role = exercises_auth_permissions.id')
            ->join('exercises_account ea on ea.superior = exercises_account.id','left')
            ->where($where)
            ->group('exercises_account.id')
            ->page($pageIndex . ',' . $pageSize)
            ->select();  //echo M()->getLastSql();die;
        return $resources;
       //$result = M()->query("select ")
    }

    /*
    *用户行为管理（试题试卷知识树）二
    */
    public function getUserBehaviorResources2($id){
       $result = M()->query("SELECT exercises_log.oper_name,exercises_log.oper_time,exercises_log.operator_id FROM exercises_log WHERE operator_id = $id ORDER BY oper_time DESC limit 1");//echo M()->getLastSql();
        //$result = M()->query("select ")
        return $result;
    }

    /*
    *用户行为管理（知识树）三
    */
    public function getUserBehaviorResources3($id){
        $result = M()->query("SELECT exercises_tree_log.oper_name,exercises_tree_log.oper_time FROM exercises_tree_log WHERE operator_id=  $id ORDER BY oper_time DESC limit 1"); //echo M()->getLastSql();
        //$result = M()->query("select ")
        return $result;
    }

    /*
     *
     */

    public function getUserBehavior(&$count, $where = array(), $pageIndex = 1, $pageSize = 20){
        $field = "id,question_id,paper_id,oper_name,oper_time,operator_id,operator_name,poperator_id,poperator_name,poperator_account,comment,error_status,INET_NTOA(ip_address) ip_address";
        $count = M('exercises_log')
            ->where($where)
            ->count();

        $resources = M('exercises_log')
            ->field($field)
            ->where($where)
            ->order('oper_time desc')
            ->page($pageIndex,$pageSize)
            ->select();  //echo M()->getLastSql();die;
        return $resources;
        //$result = M()->query("select ")
    }
	
	//获取所有教材课标知识树的数据(分页)
	public function getTreeAll(&$count,$pageIndex = 1, $pageSize = 20){
		$count = M('exercises_tree_log')
				->join("exercises_account on exercises_account.id=exercises_tree_log.operator_id",'left')
				->count();
		$result = M('exercises_tree_log')
				->field('exercises_tree_log.id,exercises_tree_log.tree_category,exercises_tree_log.tree_id,exercises_tree_log.oper_name,exercises_tree_log.oper_time,INET_NTOA(ip_address) ip,exercises_tree_log.operator_id,exercises_tree_log.operator_name,exercises_tree_log.comment,exercises_tree_log.error_status,exercises_account.user_name,exercises_account.account')
				->join("exercises_account on exercises_account.id = exercises_tree_log.operator_id",'left')
				->order('oper_time desc')
				->page($pageIndex . ',' . $pageSize)
				->select();
           return $result;
	}

	public function getDistinctBehavior($userId)
    {
        return M()->query("SELECT DISTINCT oper_name FROM exercises_log WHERE operator_id=$userId");
    }
	

}