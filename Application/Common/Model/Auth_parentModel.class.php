<?php
namespace Common\Model;
use Think\Model;  
define('CLASS_STUDETN_STATUS',2);
define('PARENT_ROLE',4);

class Auth_parentModel extends Model{
    
    public    $model='';
    protected $tableName = 'auth_parent';  
	
    public function __construct(){  
        parent::__construct();  
        $this->model=M($this->tableName);
    }
     
    /*
     * 获得某个家长的信息
     * id       
     */ 
    public function getParentInfo($id,$fields='id,grade_id,parent_name,telephone,email,sex,avatar,flag'){
        $model=$this->model;
        $result=$model->where('id='.$id)->field($fields)->find();
        return $result;
    }

    /*
     * 家长修改自己的信息
     */
    public function updateParentInfo($info,$tel)
    {
        $this->model->where("telephone=$tel")->save($info); 
    }

    public function updateInfoById($info,$id)
    {
        $this->model->where("id=$id")->save($info); 
    }


    public function getParentInfoByTelephone($tel,$parent_name='')
    {
       if($parent_name!=''){
           $where['parent_name']=$parent_name;
       }
       $where['telephone']=$tel;
       $result=$this->model->where($where)->find();
       return $result;
    }
    public function addParent($info)
    {
        $currentTime = time();
        $info['create_at'] = $currentTime;
        $info['update_at'] = $currentTime;
        return $this->model->add($info);
    }
    
    
    /*
     * 获得家长的全部数据
     */
    public function getParentDataAll($condition=array(),$order='desc',$having=''){ 
        $result=$this->model->where($condition)
                ->join('auth_student_parent_contact on auth_student_parent_contact.parent_id=auth_parent.id','left')
                ->join('auth_student on auth_student_parent_contact.student_id=auth_student.id and auth_student.apply_school_status = 1','left')
                ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id','left')
                ->join('biz_class_student on auth_student.id=biz_class_student.student_id and biz_class_student.status='.CLASS_STUDETN_STATUS,'left')
                ->join('biz_class on biz_class.id=biz_class_student.class_id AND biz_class.is_delete = 0 AND biz_class.class_status = 1','left')
                ->join('dict_grade on dict_grade.id=biz_class.grade_id','left')
                ->join('account_user_and_auth on account_user_and_auth.user_id=auth_parent.id and role_id='.PARENT_ROLE,'left')
                ->field('auth_parent.id,auth_parent.create_at,auth_parent.parent_name,auth_parent.sex,group_concat(DISTINCT auth_student.student_name SEPARATOR "、")student_name,'
                        . 'group_concat(DISTINCT dict_schoollist.school_name SEPARATOR "、")school_name,account_user_and_auth.auth_id,account_user_and_auth.auth_end_time,auth_parent.telephone,auth_parent.flag,'
                        . 'case when auth_id=4 and account_user_and_auth.auth_start_time<=UNIX_TIMESTAMP(NOW()) and account_user_and_auth.auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')
                ->group('auth_parent.id')
                ->having($having)
                ->order('auth_parent.create_at '.$order)
                ->select();             
        return $result;
    }


    /*
     * 获得家长的总数
     */
    public function getParentCount($condition=array(),$having=''){ 
        $result=$this->model->where($condition)
                ->join('auth_student_parent_contact on auth_student_parent_contact.parent_id=auth_parent.id','left')
            //TODO:这里添加了一个学生申请加入学校审核通过的条件筛选
                ->join('auth_student on auth_student.id = auth_student_parent_contact.student_id and auth_student.apply_school_status = 1','left')
                ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id','left')
                ->join('biz_class_student on auth_student.id=biz_class_student.student_id and biz_class_student.status='.CLASS_STUDETN_STATUS,'left')
                ->join('biz_class on biz_class.id=biz_class_student.class_id','left')
                ->join('dict_grade on dict_grade.id=biz_class.grade_id','left')
                ->join('account_user_and_auth on account_user_and_auth.user_id=auth_parent.id and role_id='.PARENT_ROLE,'left')
                ->field('case when auth_id=4 and account_user_and_auth.auth_start_time<=UNIX_TIMESTAMP(NOW()) and account_user_and_auth.auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')
                ->group('auth_parent.id')
                ->having($having)
                ->select(); 
        return count($result);
    }


    /*
     * 获得家长的数据
     */
    public function getParentData($condition=array(),$order='desc',$having=''){ 
        $count=$this->getParentCount($condition,$having);   
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));      
        $show=$Page->show();  
                
        $result=$this->model->where($condition)
                ->join('auth_student_parent_contact on auth_student_parent_contact.parent_id=auth_parent.id','left')
            //TODO:这里添加了一个学生申请加入学校审核通过的条件筛选
                ->join('auth_student on auth_student.id = auth_student_parent_contact.student_id and auth_student.apply_school_status = 1','left')
                ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id','left')
                ->join('biz_class_student on auth_student.id=biz_class_student.student_id and biz_class_student.status='.CLASS_STUDETN_STATUS,'left')
                ->join('biz_class on biz_class.id=biz_class_student.class_id AND biz_class.is_delete = 0 AND biz_class.class_status = 1','left')
                ->join('dict_grade on dict_grade.id=biz_class.grade_id','left')
                ->join('account_user_and_auth on account_user_and_auth.user_id=auth_parent.id and role_id='.PARENT_ROLE,'left') 
                ->field('account_user_and_auth.auth_start_time,account_user_and_auth.auth_end_time,auth_parent.id,auth_parent.parent_name,auth_parent.sex,group_concat(DISTINCT auth_student.student_name SEPARATOR "、")student_name,'
                        . ' group_concat(DISTINCT dict_schoollist.school_name SEPARATOR "、")school_name,account_user_and_auth.auth_id,account_user_and_auth.auth_end_time,auth_parent.telephone,auth_parent.flag,'
                        . 'case when auth_id=4 and account_user_and_auth.auth_start_time<=UNIX_TIMESTAMP(NOW()) and account_user_and_auth.auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')
                ->group('auth_parent.id')
                ->having($having)
                ->order('auth_parent.create_at '.$order)
                ->limit($Page->firstRow . ',' . $Page->listRows)  
                ->select();                                  
        $data['count']=$count;
        $data['page']=$show;
        $data['data']=$result;
        return $data;
    }
    
    
    /*
     * 获得某个家长的信息
     */
    public function getParentSimpleData($parent_id,$school_id=0){
        if($school_id!=0){
            $where['auth_student.school_id']=$school_id;
        }
        $where['auth_parent.id']=$parent_id;
        $result=$this->model->where($where)->join('auth_student_parent_contact on auth_student_parent_contact.parent_id=auth_parent.id','left')
                    ->join('auth_student on auth_student.id=auth_student_parent_contact.student_id','left')
                    ->join('account_user_and_auth on account_user_and_auth.user_id=auth_parent.id and role_id='.PARENT_ROLE,'left')
                    ->field('account_user_and_auth.auth_start_time,account_user_and_auth.auth_end_time,auth_parent.id,auth_parent.parent_name,auth_parent.telephone,auth_parent.flag,account_user_and_auth.auth_start_time,account_user_and_auth.auth_end_time,'
                            . 'case when auth_id=4 and account_user_and_auth.auth_start_time<UNIX_TIMESTAMP(NOW()) and account_user_and_auth.auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status,'
                            . 'FROM_UNIXTIME(auth_start_time,"%Y-%m-%d") format_start_time,FROM_UNIXTIME(auth_end_time,"%Y-%m-%d") format_end_time,timetype')->find();       
        return $result;
    }
    
    
    /*
     * 得到某个用户的权限信息
     */
    public function getParentPrivilegeInfo($parent_id){
        $model=M('account_user_and_auth');
        $where['user_id']=$parent_id;
        $where['role_id']=ROLE_PARENT;
        $result=$model->where($where)->field('*,case when auth_id=4  and account_user_and_auth.auth_start_time<UNIX_TIMESTAMP(NOW()) and'
                             . ' account_user_and_auth.auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')->find();    //echo $model->getLastsql();die;
        return $result;
    }
    
    
    /*
     * 修改权限
     */
    public function updateParentPrivilege($data,$id){
        $model=M('account_user_and_auth');
        $where['user_id']=$id;
        if($model->where($where)->save($data)===false){
            return false;
        }else{      
            return true;
        }
    }
    
    
    /*
     * 添加权限
     */
    public function addParentPrivilege($data){
        $model=M('account_user_and_auth');
        if($model->add($data)){
            return true;
        }else{
            return false;
        }
    }
    
    
    /*
     * 获得某个家长和学生对应的信息详情信息
     */
    public function getParentStudentData($id,$school_id=0){
        if($school_id!=0){
            $condition['auth_student.school_id']=$school_id;
            $str = ' AND auth_student.apply_school_status = 1';
        }
        $condition['auth_parent.id']=$id;
        $result=$this->model->where($condition)
                ->join('auth_student_parent_contact on auth_student_parent_contact.parent_id=auth_parent.id')
            //TODO:这里添加了一个学校学生必须是学校同意加入的状态筛选条件
                ->join('auth_student on auth_student_parent_contact.student_id=auth_student.id'.$str)
                ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id')
                ->join('biz_class_student on auth_student.id=biz_class_student.student_id and biz_class_student.status='.CLASS_STUDETN_STATUS,'left')
            //TODO:这里加了一个班级不能为删除状态的过滤条件
                ->join('biz_class on biz_class.id=biz_class_student.class_id AND biz_class.is_delete=0','left')
                ->join('dict_grade on dict_grade.id=biz_class.grade_id','left')
                ->field('auth_student.id,auth_student.avatar,auth_student.student_name,auth_student.student_name name,auth_student.sex,dict_schoollist.school_name,group_concat(DISTINCT dict_grade.grade)grade,'
                        . 'group_concat(concat(dict_grade.grade,biz_class.name)) class_name,auth_student.flag')
                ->group('auth_student.id')
                ->select();
        for($i=0;$i<sizeof($result);$i++) {
            if (preg_match('/Resources/', $result[$i]['avatar'])) {
                if (strpos($result[$i]['avatar'], '.') === false) {
                    $result[$i]['avatar'] .= '.jpg';
                }
                $result[$i]['img_url'] = C('oss_path') . $result[$i]['avatar'];
            } else {

                if ($result[$i]['sex'] == '男' || empty($result[$i]['sex'])) {
                    $result[$i]['img_url'] = 'http://' . WEB_URL . '/Public/img/classManage/student_m.png';
                } else {
                    $result[$i]['img_url'] = 'http://' . WEB_URL . '/Public/img/classManage/student_w.png';
                }

                //$result[$i]['img_url'] = 'http://'.WEB_URL.'/Uploads/'.'StudentAvatars'.'/'.$result[$i]['id'].'.jpg';

            }
            unset($result[$i]['avatar']);
        }
        return $result;
    }
    
    
    /*
     * 查询某个家长和学生的关系
     */
    public function getParentStudentRelation($parent_id,$student_id){
        $model=M('auth_student_parent_contact');
        $where['parent_id']=$parent_id;
        $where['student_id']=$student_id;
        $result=$model->where($where)->find();
        return $result;
    }
    
    
    /*
     * 删除学生和家长的关系
     */
    public function deleteStudentRelation($parent_id,$student_id_arr){
        $model=M('auth_student_parent_contact');
        $where['parent_id']=$parent_id;
        $where['student_id']=array('in',$student_id_arr);
        if($model->where($where)->delete()){
            return true;
        }else{
            return false;
        }
    }
    
    
    /*
     * 删除某个学生和家长的关系
     */
    public function deleteOneStudentRelation($parent_id,$student_id){
        $model=M('auth_student_parent_contact');
        $where['parent_id']=$parent_id;
        $where['student_id']=$student_id;
        if($model->where($where)->delete()){
            return true;
        }else{
            return false;
        }
    }
    
    
    /*
     * 修改家长信息的启用或禁用状态
     */
    public function updateEnableStatus($id,$status){
        $where['id']=$id;
        $data['flag']=$status;
        if($this->model->where($where)->save($data)){
            return true;
        }else{
            return false;
        }
    }

    /*
     * 更新家长手机号
     */
    public function updateParentTelephone($oldTelephone,$newTelephone)
    {
        $result = M()->execute("UPDATE auth_parent SET telephone=$newTelephone WHERE telephone=$oldTelephone");
        return ($result !== false);
    }

    /**
     *描述：获取所有家长已登录的人数（折线图用）
     */
    public function getAlreadyLoggedInCount($id,$where,$goup){
       /* $result = $this->model
            ->field('count(1) count,biz_class.name,biz_class.id')
            ->join('auth_student_parent_contact on auth_student_parent_contact.parent_id=auth_parent.id','left')
            ->join('auth_student on auth_student_parent_contact.student_id=auth_student.id','left')
            ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id')
            ->join('access_history_parent on access_history_parent.parent_id=auth_parent.id')
            ->join('biz_class_student on biz_class_student.student_id=auth_student.id and biz_class_student.status!=3 and biz_class_student.status='.CLASS_STUDETN_STATUS,'left')
            ->join('biz_class on biz_class.id=biz_class_student.class_id and biz_class.is_delete=0 ','left')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('account_user_and_auth on account_user_and_auth.user_id=auth_parent.id and role_id='.PARENT_ROLE,'left')
            ->where($where)
            ->group('biz_class.id')
            ->select();*/
        $sql = "SELECT
	count(distinct(auth_parent.id)) access_total,
  access_time
FROM
	`auth_parent`
LEFT JOIN auth_student_parent_contact ON auth_student_parent_contact.parent_id = auth_parent.id
LEFT JOIN auth_student ON auth_student_parent_contact.student_id = auth_student.id
INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id
INNER JOIN usertables.access_history_parent a ON a.parent_id = auth_parent.id
LEFT JOIN biz_class_student ON biz_class_student.student_id = auth_student.id
AND biz_class_student. STATUS != 3
AND biz_class_student. STATUS = 2
LEFT JOIN biz_class ON biz_class.id = biz_class_student.class_id
AND biz_class.is_delete = 0
LEFT JOIN account_user_and_auth ON account_user_and_auth.user_id = auth_parent.id
AND role_id = 4
WHERE
$where
AND auth_student.school_id = $id
GROUP BY
	$goup";
        $result = $this->model->query($sql);
        return $result;
    }
    /**
     *描述：获取所有家长已登录的人数(柱状图用)
     */
    public function getAlreadyLoggedInCount2($where='')
    {
         $result = $this->model
               ->field('count(distinct(auth_parent.id)) access_total,biz_class.name,biz_class.id')
               ->join('auth_student_parent_contact on auth_student_parent_contact.parent_id=auth_parent.id','left')
               ->join('auth_student on auth_student_parent_contact.student_id=auth_student.id','left')
               ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id')
               ->join('usertables.access_history_parent on usertables.access_history_parent.parent_id=auth_parent.id')
               ->join('biz_class_student on biz_class_student.student_id=auth_student.id and biz_class_student.status!=3 and biz_class_student.status='.CLASS_STUDETN_STATUS,'left')
               ->join('biz_class on biz_class.id=biz_class_student.class_id and biz_class.is_delete=0 ','left')
               ->join('dict_grade on dict_grade.id=biz_class.grade_id')
               ->join('account_user_and_auth on account_user_and_auth.user_id=auth_parent.id and role_id='.PARENT_ROLE,'left')
               ->where($where)
               ->group('biz_class.id')
               ->select();
        return $result;
    }
    /**
     *描述：获取所有的家长人数
     */
    /*public function getTotal($where = ''){
        $this->model
            ->field('count(1) count,biz_class.name,biz_class.id')
            ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id')
            ->join('biz_class_student on biz_class_student.student_id=auth_student.id and biz_class_student.status!=3 and biz_class_student.status='.CLASS_STUDETN_STATUS,'left')
            ->join('biz_class on biz_class.id=biz_class_student.class_id and biz_class.is_delete=0 ','left')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('account_user_and_auth on account_user_and_auth.user_id=auth_parent.id and role_id='.PARENT_ROLE,'left')
            ->group('auth_student.id')
            ->where($where)
            ->group('biz_class.id')
            ->select();
    }*/
}