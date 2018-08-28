<?php
namespace Common\Model;
use Think\Model;



class Social_activity_voteModel extends Model
{

    public $model = '';
    protected $tableName = 'activity_vote';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }
    public function getVoteType($id)
    {
        $result = $this->model->where('id=' . $id)->field('type')->find();
        return $result['type'];
    }
    public function getVoteExists($id)
    {
        $result = $this->model->where('id=' . $id)->field('1')->find();
        return empty($result)? false:true;
    }
    public function getVoteStatisticList($where,$pageIndex=1,$pageSize=20)
    {
        $offset  =  $pageSize*($pageIndex-1);
        $whereStr = '';
        if(isset($where['title']))
            $whereStr .= ' AND title '.$where['title'][0]. '\''.$where['title'][1].'\'';
        $sql = 'SELECT activity_vote.id,title,type,ifnull(b.browsecount,0) browsecount,ifnull(b.browsepeoplecount,0) browsepeoplecount,'.
               ' COUNT(distinct activity_vote_data.id) as voteCount,COUNT(distinct activity_vote_data.role,activity_vote_data.user_id) AS votePeopleCount,ifnull(a.reg_count,0) reg_count'.
               ' FROM activity_vote '.
               ' JOIN activity_vote_candidate ON activity_vote_candidate.vote_id = activity_vote.id '.
               ' LEFT JOIN activity_vote_data ON activity_vote_data.vote_candidate_id = activity_vote_candidate.id '.
               ' LEFT JOIN (SELECT vote_id, SUM(browse_count) AS browsecount,COUNT(distinct activity_vote_browse_statistics.role,activity_vote_browse_statistics.user_id) browsepeoplecount FROM activity_vote_browse_statistics GROUP BY vote_id) b ON b.vote_id = activity_vote.id '.
               " LEFT JOIN (SELECT vote_id,SUM(reg_count) reg_count FROM activity_vote_reg_statistics GROUP BY vote_id) a ON a.vote_id = activity_vote.id ".
               " WHERE 1=1 $whereStr ".
               ' GROUP BY activity_vote.id '.
               ' ORDER BY activity_vote.create_at DESC ';
        if( -1 != $pageIndex)
               $sql.= " LIMIT $offset,$pageSize";
        $result = M()->query($sql);
        return $result;
    }
    public function getVoteCount($where=array())
    {
        $count = $this->model->where($where)->field('count(1) as num')->find();
        return $count['num'];
    }

    public function getVoteDataStatisticList($voteType,$id=0,$where=array(),$groupCondition='date')
    {
        $whereStr1 = '';
        $whereStr2 = '';
        $whereStr3 = '';
        if(!$id)
        {
            return array();
        }
        if(isset($where['time']))
        {
           $startTimeStamp =  strtotime($where['time'][0]);
           $endTimeStamp =  strtotime($where['time'][1])+86359;
           $startTimeShort = date('Ymd',$startTimeStamp);
           $endTimeShort = date('Ymd',$endTimeStamp);

           if(!empty($where['time'][0] ) && !empty($where['time'][1] ))
           {
               $whereStr1 .= ' AND (activity_vote_data.create_at BETWEEN '. $startTimeStamp .' AND '. $endTimeStamp.') ';
               $whereStr2 .= " AND (activity_vote_browse_statistics.create_date BETWEEN $startTimeShort AND $endTimeShort) ";
               $whereStr3 .= " AND (activity_vote_reg_statistics.create_date BETWEEN $startTimeShort AND $endTimeShort) ";
           }
           else if(!empty($where['time'][0] ) )
           {
               $whereStr1 .= ' AND (activity_vote_data.create_at >= '. $startTimeStamp.') ';
               $whereStr2 .= " AND (activity_vote_browse_statistics.create_date >= $startTimeShort)";
               $whereStr3 .= " AND (activity_vote_reg_statistics.create_date >= $startTimeShort)";
           }
           else if(!empty($where['time'][1] ) )
           {

               $whereStr1 .= ' AND (activity_vote_data.create_at <= '.$endTimeStamp.') ';
               $whereStr2 .= " AND (activity_vote_browse_statistics.create_date <= $endTimeShort) ";
               $whereStr3 .= " AND (activity_vote_reg_statistics.create_date <= $endTimeShort) ";

           }
        }
        if('date' == $groupCondition)
        {
            $percentData = '%Y%m%d';
            $groupField = 'create_date';
        }
        else if('hour' == $groupCondition)
        {
            $percentData = '%k';
            $groupField = 'create_hour';
        }
        $browseTableSql = "SELECT SUM(browse_count) AS browsecount,COUNT(DISTINCT user_id,role) AS browsepeoplecount,{$groupField} FROM activity_vote_browse_statistics WHERE vote_id=$id $whereStr2 GROUP BY {$groupField}";
        $voteTableSql = "SELECT COUNT(DISTINCT activity_vote_data.user_id,activity_vote_data.role) AS votepeoplecount,COUNT(1) AS votecount,cast(from_unixtime(activity_vote_data.create_at,'{$percentData}') AS UNSIGNED INTEGER) AS votedate  FROM activity_vote_data JOIN activity_vote_candidate ON activity_vote_candidate.id = activity_vote_data.vote_candidate_id WHERE activity_vote_candidate.vote_id=$id $whereStr1 GROUP BY from_unixtime(activity_vote_data.create_at,'{$percentData}') ";
        $regTableSql = "SELECT SUM(reg_count) reg_count,{$groupField} FROM activity_vote_reg_statistics WHERE vote_id=$id $whereStr3 GROUP BY {$groupField}";
        $sql = " SELECT ifnull(a.browsecount,0) browsecount,ifnull(a.browsepeoplecount,0) browsepeoplecount,ifnull(b.votecount,0) votecount,ifnull(b.votepeoplecount,0) votepeoplecount, ifnull(c.reg_count,0) reg_count,a.{$groupField} ".
               " FROM ($browseTableSql) a LEFT JOIN ($voteTableSql) b ON b.votedate = a.{$groupField} ".
               " LEFT JOIN ($regTableSql) c ON c.{$groupField} = a.{$groupField} ";

        $sql .= ' UNION  ';
        $sql .= " SELECT ifnull(a.browsecount,0) browsecount,ifnull(a.browsepeoplecount,0) browsepeoplecount,ifnull(b.votecount,0) votecount,ifnull(b.votepeoplecount,0) votepeoplecount, ifnull(c.reg_count,0) reg_count,b.votedate {$groupField} ".
            " FROM ($voteTableSql) b LEFT JOIN ($browseTableSql) a ON b.votedate = a.{$groupField} ".
            " LEFT JOIN ($regTableSql) c ON c.{$groupField} = b.votedate ";
        $sql .= ' UNION  ';
        $sql .= " SELECT ifnull(a.browsecount,0) browsecount,ifnull(a.browsepeoplecount,0) browsepeoplecount,ifnull(b.votecount,0) votecount,ifnull(b.votepeoplecount,0) votepeoplecount, ifnull(c.reg_count,0) reg_count,c.{$groupField} ".
                " FROM ($regTableSql) c LEFT JOIN ($browseTableSql) a ON c.{$groupField} = a.{$groupField} ".
                " LEFT JOIN ($voteTableSql) b ON c.{$groupField} = b.votedate ORDER BY {$groupField} ASC";
        $result = M()->query($sql);
        return $result;
    }
    public function getRegRoleStatistics($id)
    {
        if(empty(intval($id)))
            exit;
        $voteWhere = 'vote_id = '.$id;
        $teacherSql = "SELECT COUNT(1) AS COUNT FROM activity_vote_reg_info WHERE $voteWhere AND role=2";
        $studentSql = "SELECT COUNT(1) AS COUNT FROM activity_vote_reg_info WHERE $voteWhere AND role=3";
        $parentSql = "SELECT COUNT(1) AS COUNT FROM activity_vote_reg_info WHERE $voteWhere AND role=4";
        $teacherRegCount = M()->query($teacherSql);
        $teacherRegCount = $teacherRegCount[0]['count'];
        $studentRegCount = M()->query($studentSql);
        $studentRegCount = $studentRegCount[0]['count'];
        $parentRegCount = M()->query($parentSql);
        $parentRegCount = $parentRegCount[0]['count'];
        return '注册用户: '.($teacherRegCount+$studentRegCount+$parentRegCount)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;教师: $teacherRegCount  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;学生: $studentRegCount  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;家长: $parentRegCount";
    }
    public function getRegBrowseVoteData($id, $where=array())
    {
        if(empty(intval($id)))
         exit;
        $whereStr = ' vote_id='.$id;
        $whereReg = ' c.vote_id='.$id;
       if(!empty($where['role']))
       {
           $whereStr .= ' AND role='.$where['role'];
           $whereReg .= ' AND c.role='.$where['role'];
       }
       $browseTableSql = "SELECT user_id,role,SUM(browse_count) AS browsecount FROM activity_vote_browse_statistics WHERE $whereStr GROUP BY user_id,role";
       $voteTableSql = "SELECT user_id,role,COUNT(1) AS votecount FROM activity_vote_data JOIN activity_vote_candidate ON activity_vote_candidate.id = activity_vote_data.vote_candidate_id WHERE $whereStr GROUP by user_id,role";
       $sql = " SELECT ".
              " (CASE WHEN c.role=2 then '教师' WHEN c.role=3 THEN '学生' WHEN c.role=4 THEN '家长' END) AS role, ".
              " (CASE WHEN c.role=2 then auth_teacher.telephone WHEN c.role=3 THEN auth_student.parent_tel WHEN c.role=4 THEN auth_parent.telephone END) AS telephone, ".
              " (CASE WHEN c.role=2 then auth_teacher.name WHEN c.role=3 THEN auth_student.student_name WHEN c.role=4 THEN auth_parent.parent_name END) AS name, ".
              " ifnull(a.browsecount,0) browsecount,ifnull(b.votecount,0) votecount FROM activity_vote_reg_info c ".
              " LEFT JOIN ($browseTableSql) a ON a.user_id = c.user_id AND a.role = c.role ".
              " LEFT JOIN ($voteTableSql) b ON b.user_id = c.user_id AND b.role = c.role ".
              " LEFT JOIN auth_teacher ON c.user_id = auth_teacher.id AND c.role = 2 ".
              " LEFT JOIN auth_student ON c.user_id = auth_student.id AND c.role = 3 ".
              " LEFT JOIN auth_parent ON c.user_id = auth_parent.id AND c.role = 4 ".
              " WHERE  $whereReg";
        $result = M()->query($sql);
        return $result;
    }

    public function getVoteList($where=array(),$pageIndex=1,$pageSize=20)
    {
        return $this->model->where($where)->order('create_at desc')->page($pageIndex,$pageSize)->select();
    }
    public function getVoteData($id)
    {
        $where['id'] = $id;
        return $this->model->where($where)->find();
    }
    public function deleteVote($id)
    {
        $where['id'] = $id;
        $result = $this->model->where($where)->delete();
        if ($result === false)
            return false;
        else
            return true;
    }

    public function setVoteFlag($id,$flag)
    {
        $where['id'] = $id;
        $data['flag'] = $flag;
        $result = $this->model->where($where)->save($data);
        if ($result === false)
            return false;
        else
            return true;
    }

    public function addEditVoteInfo($id=0,$title,$voteDisplay,$voteStart,$voteEnd,$description,$imgPath,$voteFreq,$voteType)
    {
        $data['title'] = $title;
        $data['votedisplay'] = $voteDisplay;
        $data['votestart'] = $voteStart;
        $data['voteend'] = $voteEnd;
        $data['description'] = $description;
        $data['img_path'] = $imgPath;
        $data['vote_freq'] = $voteFreq;
        $data['flag'] = VOTE_FLAG_UNAUTH;
        $data['type'] = $voteType;
        if($id == 0) {
            $data['create_at'] = time();
            $id = $this->model->add($data);
        }
        else
        {
            $where = array();
            $where['id'] = $id;
            $result = $this->model->where($where)->save($data);
            if(false === $result)
                return false;
        }
        return $id;
    }

    public function addVoteCandidates($voteId,$candidateId,$candidateName,$candidateImg,$candidateDescription)
    {
        $model = M('activity_vote_candidate');
        $data = array();
        $data['vote_id'] = $voteId;
        $currentIds = $model->where($data)->field('id')->select();
        $currentIds = array_column($currentIds,'id');
        $excludeIds = array();
        for($i=0;$i<sizeof($currentIds);$i++)
        {
            if(!in_array($currentIds[$i],$candidateId))
                $excludeIds[] = $currentIds[$i];
        }

        $where['id'] = array('in',implode(',',$excludeIds));
        $model->where($where)->delete();

        $addResult = true;

        for($i=0;$i<sizeof($candidateId);$i++)
        {
          $data['candidate_name'] = $candidateName[$i];
          $data['img_path'] = $candidateImg[$i];
          $data['description'] = $candidateDescription[$i];
          if($candidateId[$i] != 0)
          {
              $where = array();
              $where['id'] = $candidateId[$i];
              $result = $model->where($where)->save($data);
          }
          else
          {
              $data['vote_id'] = $voteId;
              $result = $model->add($data);
          }
            if(false === $result)
            {
                $addResult = false;
                break;
            }
        }
        return $addResult;
    }
/*
 *获得所有候选人的所得投票数和名字
 */
    public function getCandidateList($voteId)
    {
        $where = array();
        $where['vote_id'] = $voteId;
        $result = M('activity_vote_candidate')->where($where)
                  ->join('activity_vote_data ON activity_vote_data.vote_candidate_id = activity_vote_candidate.id','left')
                 ->join('activity_vote ON activity_vote_candidate.vote_id = activity_vote.id','left')
                  ->group('activity_vote_candidate.id')
                  ->field('activity_vote_candidate.id,activity_vote.id v_id,activity_vote_candidate.candidate_name,activity_vote_candidate.img_path,activity_vote_candidate.description ad,activity_vote.description,activity_vote.title,activity_vote.voteend,activity_vote.votestart,activity_vote.votedisplay,'.
                          'count(activity_vote_data.id) as votenum')
            ->select();
        return $result;
    }

    public function getCandidateListOrder($voteId)
    {
        $where = array();
        $where['vote_id'] = $voteId;
        $result = M('activity_vote_candidate')->where($where)
            ->join('activity_vote_data ON activity_vote_data.vote_candidate_id = activity_vote_candidate.id','left')
            ->join('activity_vote ON activity_vote_candidate.vote_id = activity_vote.id','left')
            ->group('activity_vote_candidate.id')
            ->field('activity_vote_candidate.id,activity_vote.id v_id,activity_vote_candidate.candidate_name,activity_vote_candidate.img_path,activity_vote_candidate.description ad,activity_vote.description,activity_vote.title,activity_vote.voteend,activity_vote.votestart,activity_vote.votedisplay,'.
                'count(activity_vote_data.id) as votenum')
            ->order('votenum desc')
            ->select();
        return $result;
    }

    /*
     *根据活动获取投票列表
     */
   /* public function get_vote_list($activity_id){
        $where['activity_vote_contact.activity_id'] = $activity_id;
        $where['activity_vote.flag'] = 4;
        $resources = M('activity_vote_contact')
            ->field('activity_vote.title,activity_vote.id')
            ->join('activity_vote ON activity_vote_contact.vote_id = activity_vote.id','left')
            ->where($where)
            ->select(false);
        return $resources;
    }*/

    /*
     *投票结果详情
     */
   /* public function get_vote_detail($vote_id){
        $value = $this->model
            ->field('activity_vote.description,activity_vote_candidate.candidate_name,activity_vote_candidate.img_path,activity_vote_candidate.description,activity_vote.title')
            ->join('activity_vote_candidate ON activity_vote.id = activity_vote_candidate.vote_id','left')
            ->where($vote_id)
            ->select(false);
        return $value;
    }*/

    /*
     *根据活动ID查看当前活动是否存在投票
     */
    public function is_votes($activity_id){
        $where['activity_vote_contact.activity_id'] = $activity_id;
        $where['activity_vote.flag'] = 4;
        $where['activity_vote.type'] = VOTE_TYPE_NORMAL;  //正常投票
        $result = M('activity_vote_contact')
            ->field('activity_vote.title,activity_vote.id,activity_vote.votestart,activity_vote.voteend,activity_vote.votedisplay,activity_vote.description,activity_vote.img_path')
            ->join('activity_vote ON activity_vote_contact.vote_id = activity_vote.id','left')
            ->where($where)
            ->select();
        //return $result;
        return empty($result) ? array('result'=>'no','value'=>'') : array('result'=>'yes','value'=>$result);
    }

    /*
     *根据当前用户查看是否投票
     */
    public function if_vote($user_id,$vote_id,$role){
        $where['activity_vote_data.user_id'] = $user_id;
        $where['activity_vote.id'] = $vote_id;
        $where['activity_vote_data.role'] = $role;
        $vote = $this->model
            ->field('activity_vote.id,activity_vote_candidate.id vc_id')
            ->join('activity_vote_candidate ON activity_vote.id = activity_vote_candidate.vote_id','left')
            ->join('activity_vote_data ON activity_vote_candidate.id = activity_vote_data.vote_candidate_id','left')
            ->where($where)
            ->select();
        return empty($vote) ? array('if_vote'=>'no','vote_candidate'=>'') : array('if_vote'=>'yes','vote_candidate'=>$vote['0']['vc_id']);
    }

    /*
     * 查看今天该用户是否还可继续投票
     */
    public function getCanVoteAndVoteData($voteId,$userId,$role)
    {
        $where['activity_vote_data.user_id'] = $userId;
        $where['activity_vote.id'] = $voteId;
        $where['activity_vote_data.role'] = $role;
        $currentDayDate = strtotime(date("Y-m-d",time()));
        $where['activity_vote_data.create_at'] = array('between',array($currentDayDate,$currentDayDate+86400));
        $voteData = $this->model
            ->field('activity_vote.id,activity_vote_candidate.id vc_id')
            ->join('activity_vote_candidate ON activity_vote.id = activity_vote_candidate.vote_id','left')
            ->join('activity_vote_data ON activity_vote_candidate.id = activity_vote_data.vote_candidate_id','left')
            ->where($where)
            ->select();
        $result = array();
        $voteInfo = $this->getVoteData($voteId);
        if($voteInfo['vote_freq'] <= sizeof($voteData))
            $canVote = 0;
        else
            $canVote = 1;
        for($i=0;$i<sizeof($voteData);$i++)
        {
          $result[$voteData[$i]['vc_id']] = empty($result[$voteData[$i]['vc_id']]) ? 1: $result[$voteData[$i]['vc_id']]+1;
        }
        return array('canVote'=>$canVote,'data' => $result);
    }
    function getVoteIdByCandidateId($candidateId)
    {
       $model = M('activity_vote_candidate');
       $where['id'] = $candidateId;
       $result = $model->where($where)->field('vote_id')->find();
       if(empty($result))
           return 0;
       return $result['vote_id'];
    }
}
