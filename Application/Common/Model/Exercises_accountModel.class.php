<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/9/7
 * Time: 11:42
 */
namespace Common\Model;

use Think\Model;


class Exercises_accountModel extends Model
{
    public $model = '';
    protected $tableName = 'exercises_account';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    /*
     *查询所有数据
     */
    public function getAllForList(&$count, $where = array(), $pageIndex = 1, $pageSize = 20)
    {
        $count = $this->model
            ->join('exercises_auth_permissions on exercises_auth_permissions.id = exercises_account.role')
            ->where($where)
            ->select();
        $count = count($count);

        $resources = $this->model
            ->field('exercises_account.*,exercises_auth_permissions.name r_name')
            ->join('exercises_auth_permissions on exercises_auth_permissions.id = exercises_account.role')
            ->where($where)
            ->page($pageIndex . ',' . $pageSize)
            ->select();  //echo M()->getLastSql();die;
        return $resources;
    }

    /*
    * 用户登录
    */
    public function userLogin($user_name, $password)
    {
        $condition['account'] = $user_name;
        $condition['password'] = $password;
        $condition['exercises_account.delete_status'] = DELETE_STATUS_FALSE;
        $result = $this->model->where($condition)->join('exercises_auth_permissions ON exercises_auth_permissions.id = exercises_account.role','left')->field('exercises_account.*,exercises_auth_permissions.name role_name')->find();
        return $result;
    }

    /*
     *修改数据
     * $data数组
     * $where数组
     */
    public function updateResources($data, $where)
    {
        $status = $this->model
            ->where($where)
            ->save($data);
        return $status;
    }

    /*
     *查询(无分页)
     */
    public function getResources($where = array())
    {
        $resources = $this->model
            ->field('exercises_account.*,exercises_course.name course_name,' . '(CASE WHEN exercises_account.Learning_period_id = 1 THEN \'小学\' WHEN exercises_account.Learning_period_id = 2 THEN \'初中\' WHEN exercises_account.Learning_period_id = 3 THEN \'高中\' END) Learning_period')
            ->join('exercises_course on exercises_course.id = exercises_account.course_id')
            ->where($where)
            ->select(); //echo M()->getLastSql();die;
        $count = count($resources);
        $data['resources'] = $resources;
        $data['count'] = $count;
        return $data;
    }

    /*
     *添加账号操作
     */
    public function addResources($data)
    {
        $result = $this->model
            ->add($data);
        return $result;
    }

    /*
     *账号修改
     */
    public function saveResources($data,$where)
    {
        $result = $this->model
            ->where($where)
            ->save($data);
        return $result;
    }
    /*
     *查询一条数据
     */
    public function getResourcesOne($where){
        $resources = $this->model
            ->field('exercises_account.*,exercises_course.name course_name,exercises_auth_permissions.name role_name,' . '(CASE WHEN exercises_account.Learning_period_id = 1 THEN \'小学\' WHEN exercises_account.Learning_period_id = 2 THEN \'初中\' WHEN exercises_account.Learning_period_id = 3 THEN \'高中\' END) Learning_period')
            ->join('exercises_course on exercises_course.id = exercises_account.course_id','left')
            ->join('exercises_auth_permissions on exercises_auth_permissions.id = exercises_account.role')
            ->where($where)
            ->find();
        return $resources;
    }

    /*
     * 获取所有正常用户ID
     */
    public function getAllUserId()
    {
        $where = " delete_status=". STATE_NORMAL ;
        $result = M()->query("SELECT id FROM exercises_account WHERE $where ");
        return array_column($result,'id');
    }

    /*
     * 获取用户信息
     */
    public function getUserInfo($userId,$role=0)
    {
        $userId = intval($userId);
        if($userId == 0)
            return array();
        $role = intval($role);
        $where = " exercises_account.delete_status=". STATE_NORMAL . " AND exercises_account.id=$userId ";
        if($role != 0)
        {
            $where .= " AND role = $role ";
        }
        $result = M()->query("SELECT exercises_account.*,exercises_auth_permissions.name role_name FROM exercises_account JOIN exercises_auth_permissions on exercises_auth_permissions.id = exercises_account.role WHERE $where ");
        return $result[0];
    }

    private function getConditionWhere($condition)
    {
        $whereStr = ' WHERE exercises_account.delete_status= '.ACCOUNT_STATUS_NORMAL;
        foreach($condition as $key=>$val) {
            if (!empty($val))
                switch ($key) {
                    case 'userName': $whereStr .= " AND exercises_account.user_name like '%$val%' ";break;
                    case 'roleId': $whereStr .= " AND exercises_account.role = $val ";break;
                    case 'account': $whereStr .= " AND exercises_account.account like '%$val%' ";break;
                    case 'telephone': $whereStr .= " AND exercises_account.mobile_phone like '%$val%' ";break;
                    case 'nid' : $whereStr .= " AND exercises_account.id <> $val ";break;
                    default :break;
                }
        }
        return $whereStr;
    }
    /*
     *获取指定条件下用户数量
     */
    public function getUserExerciseInfoCount($condition)
    {
        $whereStr = $this->getConditionWhere($condition);
        $result = M()->query("SELECT COUNT(1) count FROM exercises_account JOIN exercises_auth_permissions a ON a.id = exercises_account.role $whereStr LIMIT 1");
        return $result[0]['count'];
    }
    /*
     *获取用户基本信息、标引、录入统计信息
     */
    public function getUserExerciseInfoList($condition)
    {
        $whereStr = $this->getConditionWhere($condition);
        $limitStr = '';
        if(isset($condition['startIndex']) && $condition['pageSize'])
            $limitStr = ' LIMIT '.$condition['startIndex'] .',' .$condition['pageSize'].' ';
        $publicExerciseTable = "( SELECT exercises_question_processinfo.* FROM exercises_question_processinfo
                 JOIN exercises_createexercise e ON exercises_question_processinfo.question_id = e.id AND e.parent_id = 0 AND e.is_delete=".STATE_NORMAL." JOIN
                 exercises_course ON e.subject = exercises_course.id
                 JOIN
                 exercises_course topic ON e.home_topic_type = topic.id )";
        $sql = " SELECT exercises_account.id,user_name,account,a.name role_name,mobile_phone,COUNT(DISTINCT b.id) inputexercisecount,COUNT(DISTINCT c.id ) inputpapercount,".
               " IFNULL(d.markexercisecount,0) markexercisecount,".
               " ifnull(format(100*SUM(b.rejecter_id = 0 AND (b.verify_time !=0 AND b.reject_time =0))/SUM(b.id is not null AND (b.verify_time !=0 OR b.reject_time !=0) ),2),'--') inputrightratio,".
               " IFNULL(d.markrightratio,'--') markrightratio, ".
               " account_status FROM exercises_account ".
               " JOIN exercises_auth_permissions a ON a.id = exercises_account.role ".
               " LEFT JOIN $publicExerciseTable b ON b.creator_id = exercises_account.id ".
               " LEFT JOIN (SELECT b.* FROM exercises_paper_processinfo b JOIN exercises_create_paper e ON b.paper_id = e.id AND e.is_delete=".STATE_NORMAL." JOIN exercises_course ON e.subject = exercises_course.id JOIN dict_citydistrict ON dict_citydistrict.id = e.city_id)  c ON c.creator_id = exercises_account.id ".
               " LEFT JOIN (SELECT marker_id,COUNT(DISTINCT id) markexercisecount,ifnull(format(100*SUM(markrejecter_id = 0 AND mark_time != 0)/SUM(id is not null AND mark_time != 0),2),'--') markrightratio FROM ($publicExerciseTable) mark GROUP BY marker_id) d ON d.marker_id = exercises_account.id  $whereStr GROUP BY exercises_account.id $limitStr";
       return M()->query($sql);
    }

    private function getCurrentDayStatisticSql($userId,$dateStr=' now() ')
    {
        if($dateStr != ' now() ')
            $dateStr = '\''.$dateStr.'\'';
        $publicExerciseTable = "( SELECT exercises_question_processinfo.* FROM exercises_question_processinfo
            JOIN exercises_createexercise e ON exercises_question_processinfo.question_id = e.id AND e.parent_id = 0 AND e.is_delete=".STATE_NORMAL." JOIN
                 exercises_course ON e.subject = exercises_course.id
                 JOIN
                 exercises_course topic ON e.home_topic_type = topic.id )";
        $sql = "SELECT 
            base.basedate,
            $userId as user_id,
            IFNULL(a.inputexercisecount, 0) inputexercisecount,
            IFNULL(b.inputpapercount, 0) inputpapercount,
            IFNULL(c.markexercisecount, 0) markexercisecount,  
            IFNULL(d.verifyexercisecount, 0) verifyexercisecount,
            IFNULL(e.rejectexercisecount, 0) rejectexercisecount,
            IFNULL(f.remarkexercisecount, 0) remarkexercisecount,
            IFNULL(g.reprocessexercisecount, 0) reprocessexercisecount,
            h.inputrightratio,
            i.markrightratio
        FROM
            (SELECT date($dateStr) basedate) base
                LEFT JOIN
            (SELECT 
                DATE(create_time) date1, COUNT(1) inputexercisecount
            FROM
                $publicExerciseTable a   
            WHERE
                creator_id = $userId
            GROUP BY DATE(create_time)) a ON a.date1 = base.basedate
                LEFT JOIN
            (SELECT 
                DATE(create_time) date1, COUNT(1) inputpapercount
            FROM
                exercises_paper_processinfo
            JOIN exercises_create_paper e ON exercises_paper_processinfo.paper_id = e.id AND e.is_delete=".STATE_NORMAL.
          " JOIN exercises_course ON e.subject = exercises_course.id JOIN dict_citydistrict ON dict_citydistrict.id = e.city_id                   
            WHERE
                creator_id = $userId
            GROUP BY DATE(create_time)) b ON b.date1 = base.basedate
                LEFT JOIN
            (SELECT 
                DATE(mark_time) date1, COUNT(1) markexercisecount
            FROM
                $publicExerciseTable a
            WHERE
                marker_id = $userId
            GROUP BY DATE(mark_time)) c ON c.date1 = base.basedate
                LEFT JOIN
            (SELECT 
                DATE(verify_time) date1, COUNT(1) verifyexercisecount
            FROM
                $publicExerciseTable a
            WHERE
                verifier_id = $userId
            GROUP BY DATE(verify_time)) d ON d.date1 = base.basedate
                LEFT JOIN
            (SELECT 
                DATE(reject_time) date1, COUNT(1) rejectexercisecount
            FROM
                $publicExerciseTable a
            WHERE
                rejecter_id = $userId
            GROUP BY DATE(reject_time)) e ON e.date1 = base.basedate
                LEFT JOIN
            (SELECT 
                DATE(mark_time) date1, COUNT(1) remarkexercisecount
            FROM
                $publicExerciseTable a
            WHERE
                marker_id = $userId AND markrejecter_id != 0
            GROUP BY DATE(mark_time)) f ON f.date1 = base.basedate
                LEFT JOIN
            (SELECT 
                DATE(reprocess_time) date1, COUNT(1) reprocessexercisecount
            FROM
                $publicExerciseTable a
            WHERE
                creator_id = $userId AND rejecter_id != 0
            GROUP BY DATE(reprocess_time)) g ON g.date1 = base.basedate
                JOIN
            (SELECT 
                IFNULL(FORMAT(100 * SUM(rejecter_id = 0
                        AND (verify_time != 0 AND reject_time = 0)) / SUM(id IS NOT NULL
                        AND (verify_time != 0 OR reject_time != 0)), 2), '--') inputrightratio
            FROM
                $publicExerciseTable a
            WHERE
                creator_id = $userId) h ON 1 = 1
                JOIN
            (SELECT 
                IFNULL(FORMAT(100 * SUM(markrejecter_id = 0 AND mark_time != 0) / SUM(id IS NOT NULL AND mark_time != 0), 2), '--') markrightratio
            FROM
                $publicExerciseTable a
            WHERE
                marker_id = $userId) i ON 1 = 1";
        return $sql;
    }
    /*
     *获取当天用户基本信息、标引、录入统计信息
     */
    public function getCurrentDayStatisticData($userId)
    {
        //注：录入合格率 标引合格率为累计值
        $sql = $this->getCurrentDayStatisticSql($userId);
        return M()->query($sql)[0];
    }

    public function etlOfDayStatisticData($userIdArray=array(),$date)
    {
        $tableExistsInfo = M()->query("SHOW TABLES");
        $tableExists =  false;
        foreach($tableExistsInfo as $key=>$val)
        {
            if(array_values($val)[0] == 'exercises_etl_account_statistics')
            {
                $tableExists = true;
                break;
            }
        }
        if(!$tableExists)
        {
           //create table
            M()->execute("CREATE TABLE `exercises_etl_account_statistics` (
                           `basedate` date DEFAULT NULL,
                           `user_id` bigint(21) unsigned not null default 0 ,
                           `inputexercisecount` bigint(21) NOT NULL DEFAULT '0',
                           `inputpapercount` bigint(21) NOT NULL DEFAULT '0',
                           `markexercisecount` bigint(21) NOT NULL DEFAULT '0',
                           `verifyexercisecount` bigint(21) NOT NULL DEFAULT '0',
                           `rejectexercisecount` bigint(21) NOT NULL DEFAULT '0',
                           `remarkexercisecount` bigint(21) NOT NULL DEFAULT '0',
                           `reprocessexercisecount` bigint(21) NOT NULL DEFAULT '0',
                           `inputrightratio` varchar(75) CHARACTER SET utf8 NOT NULL DEFAULT '',
                           `markrightratio` varchar(75) CHARACTER SET utf8 NOT NULL DEFAULT '',
                           unique key `key` (`basedate`,`user_id`),
                           key `index` (`basedate`,`user_id`)
                         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        foreach($userIdArray as $key=>$id)
        {
            $sql = $this->getCurrentDayStatisticSql($id,$date);
            M()->execute("REPLACE INTO exercises_etl_account_statistics $sql");
        }
    }

    private function getStatisticConditionWhere($condition)
    {
        $whereStr = ' WHERE 1=1 ';
        foreach($condition as $key=>$val) {
            if (!empty($val))
                switch ($key) {
                    case 'startDate': $whereStr .= " AND  basedate >= '$val' ";break;
                    case 'endDate': $whereStr .= " AND basedate <= '".date('Y-m-d H:m:s',strtotime($val)+86399)."' ";break;
                    case 'userId': $whereStr .= " AND user_id = $val ";break;
                    default :break;
                }
        }
        return $whereStr;
    }

    public function getUserStatisticCount($condition)
    {
        if(empty($condition['userId']))
            return 0;
        $whereCondition = $this->getStatisticConditionWhere($condition);
        $result = M()->query("SELECT COUNT(1) COUNT FROM (SELECT * FROM exercises_etl_account_statistics UNION ALL ".$this->getCurrentDayStatisticSql($condition['userId']) .") a $whereCondition");
        return $result[0]['count'];
    }

    public function getUserStatisticList($condition)
    {
     if(empty($condition['userId']))
         return array();
     $whereCondition = $this->getStatisticConditionWhere($condition);
     if(isset($condition['startIndex']) && $condition['pageSize'])
     $limitStr = ' LIMIT '.$condition['startIndex'] .',' .$condition['pageSize'].' ';
     $result = M()->query("SELECT * FROM (SELECT * FROM exercises_etl_account_statistics UNION ALL ".$this->getCurrentDayStatisticSql($condition['userId']). ") a $whereCondition ORDER by basedate desc $limitStr");
     return $result;
    }

    public function getMarkerList()
    {
       $result = M()->query("SELECT exercises_account.id,concat(user_name,' ',exercises_course.name) name FROM exercises_account JOIN exercises_course ON exercises_account.course_id = exercises_course.id WHERE course_id <> 0 AND delete_status =2");
       return $result;
    }

    /*
     * 获取当前账号有没有待标引的或录入的习题
     */
    public function getWaitingForIndexingByAccount($where)
    {
        $result = M()->query("SELECT creator_id,exercises_account.user_name name FROM exercises_question_processinfo JOIN exercises_account ON exercises_question_processinfo.creator_id = exercises_account.id JOIN exercises_createexercise ON exercises_createexercise.id = exercises_question_processinfo.question_id WHERE $where");
        return $result;
    }

    /*
     * 获取当前账号有没有录入的试卷
     */
    public function getPaperByAccount($where)
    {
        $result = M()->query("SELECT creator_id FROM exercises_paper_processinfo JOIN exercises_account ON exercises_paper_processinfo.creator_id = exercises_account.id JOIN exercises_create_paper ON exercises_create_paper.id = exercises_paper_processinfo.paper_id WHERE $where");
        return $result;
    }

}