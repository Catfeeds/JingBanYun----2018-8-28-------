<?php
namespace Common\Model;
use Think\Model; 
define('CLASS_STUDETN_STATUS',2);
define('STUDENT_ROLE',3);
class Auth_studentModel extends Model{
    public    $model='';
    protected $tableName = 'auth_student';  
	
    public function __construct(){
        parent::__construct();  
        $this->model=M($this->tableName);
    }
    
    /*
     * 获得某个学生的信息
     * id       
     */ 
    public function getStudentInfo($id,$password=''){
        if($password!=''){
            $where['auth_student.password']=$password;
        }
        $where['auth_student.id']=$id;
        $result=$this->model->join('auth_parent ON auth_student.parent_id = auth_parent.id','left')
                ->join('dict_grade ON dict_grade.id = auth_student.grade_id','left')
                ->where($where)->field('auth_student.id,auth_student.student_name,auth_student.user_name,'
                . 'auth_student.telephone,auth_student.access_token,auth_student.avatar,auth_student.email,auth_student.sex,auth_student.birth_date,auth_student.school_id,'
                . 'auth_student.parent_id,auth_student.id_card,auth_student.parent_tel,auth_parent.parent_name,auth_student.student_id,auth_student.apply_school_status,auth_student.flag,auth_student.grade_id,dict_grade.grade')->find();
        return $result;
    }
    
    /*
     * 删除学生家长关联表
     */
    public function deleteStudentParentContactData($student_id,$parent_id=0){
        $model=M('auth_student_parent_contact');
        if($parent_id!=0){
            $where['parent_id']=$parent_id;
        }
        $where['student_id']=$student_id;
        if($model->where($where)->delete()){
            return true;
        }else{  
            return false;
        }
    }
    
    
    /*
     * 修改家长学生关联表
     */
    public function updateStudentParentDta($id,$data){
        $model=M('auth_student_parent_contact');
        $where['id']=$id;
        if($model->where($where)->save($data)===false){
            return false;
        }else{
            return true;
        }
    }
    
    
    /*
     * 插入学生家长关联表
     */
    public function addStudentParentContactData($data){
        $model=M('auth_student_parent_contact');
        if($insert_id=$model->add($data)){
            return $insert_id;
        }else{
            return false;
        }
    }
    

    /*
     *查询学生家长关联表
     */
    public function getDataByStudentParentContact($where=''){
        $model=M('auth_student_parent_contact');
        $result = $model->where($where)
            ->find();
        return $result;
    }

    public function getStudentIdsListByGrade($gradeId)
    {
        $where['biz_class.grade_id'] = $gradeId;
        $result = $this->model->join('biz_class_student ON biz_class_student.student_id = '.$this->tableName .'.id')
                    ->join('biz_class ON biz_class.id = biz_class_student.class_id')
                    ->field($this->tableName.'.id')
                    ->where($where)
                    ->select();
        return array_column($result,'id');
    }
    /*
      * 根据手机 学生姓名获取某个学生的信息
      * id
      */
    public function getStudentInfoByTelAndName($telephone,$student_name){
        $where['parent_tel'] = $telephone;
        $where['student_name'] = $student_name;
        $result=$this->model->where($where)->field('ipad_address,mac_address,pad_channel_id_info,channel_id_info,id,password,student_name,school_id,user_name,telephone,access_token,avatar,email,sex,parent_tel,id student_id,apply_school_status,flag')->find();
        return $result;
    }
    public function getStudentInfoByStuId($stuId)
    {
        $where['id'] = $stuId;
        $result=$this->model->where($where)->field('id,student_name,user_name,telephone,access_token,avatar,email,sex,birth_date,school_id,parent_id,id_card,parent_tel,student_id')->find();
        return $result;
    }
    public function getStudentBySchoolIdStuId($schoolId,$stuId)
    {
        $where['school_id'] = $schoolId;
        $where['student_id'] = $stuId;
        $result=$this->model->where($where)->field('id,student_name,user_name,telephone,access_token,avatar,email,sex,birth_date,school_id,parent_id,id_card,parent_tel,student_id')->find();
        return $result;
    }
    /*
     * 获得学生的信息
     * ids      学生id(字符串)
     */
    public function getStudents($ids){
        $model=$this->model;
        $result=$model->where('id in ('.$ids.')')->field('id,student_id,student_name,email,sex,avatar')->select();     //echo $model->getLastsql();die;
        return $result;
    }
    
    
    /*
     * 获得家长的信息
     * id       家长id
     */
    public function getParentStudent($id){
        $model=$this->model;
        $result=$model->where('parent_id='.$id)->field('id,student_name,email,sex')->select(); 
        return $result;
    }

    /*
     * 根据auth_student_parent_contact表获取家长的学生列表
     * id       家长id
     */
    public function getParentStudents($id,$student_id='',$tel='',$keyword=''){
        if($student_id!=''){
            $where['auth_student_parent_contact.student_id']=$student_id;
        }
        if($tel!=''){
            $where['auth_student_parent_contact.parent_tel']=$tel;
        }
        if($keyword!='')
        {
            $where['auth_student.student_name'] =  array('like','%'.$keyword.'%');
        }
        $where['auth_student_parent_contact.parent_id']=$id;
//        $where['biz_class.is_delete']=0;

        $model=M('auth_student_parent_contact');
        $result=$model->where($where)
            ->join('auth_student ON auth_student.id = auth_student_parent_contact.student_id')
            ->join('dict_schoollist ON auth_student.school_id = dict_schoollist.id','left')
            ->join('biz_class_student ON biz_class_student.student_id = auth_student_parent_contact.student_id','left')
            ->join('biz_class ON biz_class.id = biz_class_student.class_id AND biz_class.is_delete = 0','left')

            ->field('auth_student.id as sid,biz_class_student.class_id,auth_student.sex,auth_student.id,auth_student.student_name name,auth_student.student_id,auth_student.id_card,auth_student.parent_tel,ifnull(dict_schoollist.school_name,\'\') school_name,auth_student.avatar')
            ->group('auth_student.id,biz_class_student.class_id')
            ->select();
        for($i=0;$i<sizeof($result);$i++)
        {
            if (preg_match('/Resources/', $result[$i]['avatar'])){
                if(strpos($result[$i]['avatar'],'.')===false)
                {
                    $result[$i]['avatar'].='.jpg';
                }
                $result[$i]['img_url'] = C('oss_path').$result[$i]['avatar'];
            } else {

                if ( $result[$i]['sex'] == '男' || empty($result[$i]['sex'])) {
                    $result[$i]['img_url'] = 'http://'.WEB_URL.'/Public/img/classManage/student_m.png';
                } else {
                    $result[$i]['img_url'] = 'http://'.WEB_URL.'/Public/img/classManage/student_w.png';
                }

                //$result[$i]['img_url'] = 'http://'.WEB_URL.'/Uploads/'.'StudentAvatars'.'/'.$result[$i]['id'].'.jpg';

            }
            unset($result[$i]['avatar']);
            if (!empty($result[$i]['class_id'])) {
                $classmap['biz_class.id'] = $result[$i]['class_id'];
                $classmap['biz_class.is_delete'] = 0;
                $classinfo = M('biz_class')
                        ->join('dict_grade ON dict_grade.id = biz_class.grade_id')
                        ->where( $classmap )
                        ->field('biz_class.name,dict_grade.grade')
                        ->find();
                if (!empty($classinfo))
                 $result[$i]['class_grade_name'] = $classinfo['grade'].$classinfo['name'];
			    else
                 {array_splice($result,$i,1);$i--;}

            }

        }
        return $result;
    }
     
    
    
    /*
     * 根据学生数组获取家长的信息
     * id       家长id
     */
    public function getParentList($studentArray){
        $model=$this->model;
        $where[$this->tableName.'.id'] = array('in',$studentArray);
        $result=$model->where($where)
                      ->join('auth_parent ON '. $this->tableName.".parent_tel = auth_parent.telephone")
                      ->field('auth_parent.id')->select();
        return array_column($result,'id');
    }

    /*
     * 根据ID更新学生信息
     * info     更新数据
     * id       学生id
     */
    public function updateInfoById($info,$id)
    {
        if($this->model->where("id=$id")->save($info)===false){
            return false;
        }else{
            return true;
        }
    }
    /*
     * 修改学生的家长信息
     */
    public function updateStudentParentInfo($parent_id,$student_id){
        $model=$this->model;

        $parent_model=D('Auth_parent');
        $parent_info=$parent_model->getParentInfo($parent_id);
        $data['parent_id']=$parent_info['id'];
        $data['parent_name']=$parent_info['parent_name'];
        $data['parent_tel']=$parent_info['telephone'];
        if(0){
            return false;
        }else{
            $model=M('auth_student_parent_contact');
            $data = array();
            $data['parent_id']=$parent_info['id'];
            $data['parent_tel']=$parent_info['telephone'];
            $data['student_id']=$student_id;
            if($model->where($data)->find())
            {
                return true;
            }
            else
            $data['create_at']=time();
            return $model->add($data);
        }
    }
    public function addStudentForRegister($info)
    {
        if(empty($info['email'])) unset($info['email']);
        if(empty($info['id_card'])) unset($info['id_card']);
        return $this->model->add($info);
    }
    public function addStudent($info){
      $id = -1;
      try{
         $parentInfo = D('auth_parent')->getParentInfoByTelephone($info['parent_tel']);
         $info['parent_id'] = $parentInfo['id'];
         $id = $this->model->add($info);
         $this->updateStudentParentInfo($info['parent_id'],$id);
       }
      catch(Exception $e)
      {
        return array(RUN_FAIL,$e);
      }
      return array(RUN_SUCCESS,$id);
    }

    //编辑学生
    public function editStudent($id,$info)
    {
       $sutdent_info=$this->model->where("id=$id")->find();
     if(!empty($sutdent_info))
      {
       $this->model->where("id=$id")->save($info);
       return array(RUN_SUCCESS,'');
      }
     return array(RUN_FAIL,'id not found');
    }

    //删除学生
    public function deleteStudent($id)
    {
        $sutdent_info=$this->model->where("id=$id")->find();
    if(!empty($sutdent_info))
     {
      $this->model->where("id=$id")->delete();
      return array(RUN_SUCCESS,'');
     }
      return array(RUN_FAIL,'id not found');
    }

    public function saveLoginAddress($id,$address)
    {
        $where['id'] = $id;
        $data['login_address'] = $address;
        $this->model->where($where)->save($data);
    }

    public function getStudentParentInfo($id)
    {
        $where['auth_student.id'] = $id;
        return $this->model->where($where)->join('auth_parent ON '.$this->tableName.'.parent_tel = auth_parent.telephone')
                                           ->field('auth_parent.id,auth_parent.telephone')->find();
    }

    public function getAllStudentParentPair()
    {
        return $this->model->group('parent_id')->field('group_concat(student_name) as name,parent_id')->where('parent_id <> 0')->select();
    }
    
    
    /*
     * 获得全部学生的数据
     */
    public function getStudentDataAll($condition=array(),$order='desc'){ 
        $result=$this->model->where($condition)
                    ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id','left')
                    ->join('biz_class_student on biz_class_student.student_id=auth_student.id','left')
                    ->join('biz_class on biz_class.id=biz_class_student.class_id','left')
                    ->join('dict_grade on dict_grade.id=biz_class.grade_id','left') 
                    ->field('auth_student.id,auth_student.create_at,auth_student.school_id,auth_student.student_name,auth_student.sex,auth_student.apply_school_status,auth_student.parent_tel,'
                            . 'dict_schoollist.school_name,group_concat(dict_grade.grade SEPARATOR "、") grade,group_concat(biz_class.name SEPARATOR "、") class_name,auth_student.flag') 
                    ->group('auth_student.id')
                    ->order('auth_student.create_at '.$order)
                    ->select();
        return $result;
    }


    /*
    * 获得全部学生的数据COPY
    */
    public function getStudentDataAllCopy($condition=array(),$order='desc'){
        $result=$this->model->where($condition)
            ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id','left')
            ->join('biz_class_student on biz_class_student.student_id=auth_student.id','left')
            ->join('biz_class on biz_class.id=biz_class_student.class_id AND  biz_class_student. STATUS = 2
AND biz_class.flag = 1
AND biz_class.class_status = 1
AND biz_class.is_delete = 0','left')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id','left')
            ->field('auth_student.id,auth_student.create_at,auth_student.school_id,auth_student.student_name,auth_student.sex,auth_student.apply_school_status,auth_student.parent_tel,'
                . 'dict_schoollist.school_name,group_concat(dict_grade.grade SEPARATOR "、") grade,group_concat(biz_class.name SEPARATOR "、") class_name,auth_student.flag')
            ->group('auth_student.id')
            ->order('auth_student.create_at '.$order)
            ->select();
        return $result;
    }

    /*
     * 获得学生的总数
     */
    public function getStudentCount($condition=array(),$class_type=''){ 
        if($class_type!=''){
            $class_status_string=' and biz_class.class_status='.$class_type;
        }
        $result=$this->model->where($condition)
                    ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id','left')
                    ->join('biz_class_student on biz_class_student.student_id=auth_student.id and biz_class_student.status!=3 and biz_class_student.status='.CLASS_STUDETN_STATUS,'left')
                    ->join('biz_class on biz_class.id=biz_class_student.class_id and biz_class.is_delete=0 '.$class_status_string,'left')
                    ->join('dict_grade on dict_grade.id=biz_class.grade_id','left') 
                    ->group('auth_student.id')
                    ->field('1')
                    ->select();                   
        return count($result); 
    } 
    
    
    /*
     * 获得学生的数据
     */
    public function getStudentData($condition=array(),$order='desc',$class_type=''){               
        $count=$this->getStudentCount($condition);   
        
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));  
        $show=$Page->show();  
        $class_status_string='';
        if($class_type!=''){
            $class_status_string=' and biz_class.class_status='.$class_type;
        }
       
        $result=$this->model->where($condition)
                    ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id','left')
                    ->join('biz_class_student on biz_class_student.student_id=auth_student.id and biz_class_student.status!=3 and biz_class_student.status='.CLASS_STUDETN_STATUS,'left')
                    ->join('biz_class on biz_class.id=biz_class_student.class_id and biz_class.is_delete=0 '.$class_status_string,'left')
                    ->join('dict_grade on dict_grade.id=biz_class.grade_id','left')
                    ->join('account_user_and_auth on account_user_and_auth.user_id=auth_student.id and role_id=3','left')
                    ->field('account_user_and_auth.auth_start_time,account_user_and_auth.auth_end_time,auth_student.id,auth_student.student_name,auth_student.sex,auth_student.apply_school_status,auth_student.flag,auth_student.parent_tel,dict_schoollist.id school_id,'
                            . 'dict_schoollist.school_name,group_concat(DISTINCT dict_grade.grade SEPARATOR "、") grade,group_concat(concat(dict_grade.grade,biz_class.name) SEPARATOR "、") class_name,'
                        . 'case when account_user_and_auth.auth_start_time<=UNIX_TIMESTAMP(NOW()) and account_user_and_auth.auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')
                    ->limit($Page->firstRow . ',' . $Page->listRows) 
                    ->group('auth_student.id')
                    ->order('auth_student.create_at '.$order)
                    ->select();

        $data['count']=$count;
        $data['page']=$show;
        $data['data']=$result;
        return $data;
    } 
    
    
    /*
     * 修改申请学校审核状态的管理
     */
    public function updateApplyStatusManagement($id,$status,$school_id=0){
        $where['id']=$id;
        if($school_id!=0){
            $where['school_id']=$school_id;
        }
        if($status==1){
            $data['apply_school_status']=$status;
            if($this->model->where($where)->save($data)){
                return true;
            }else{
                return false;
            }
        }else{ 
            $data['school_id']=OTHER_SCHOOL_ID;
            if($this->model->where($where)->save($data)===false){
                return false;
            }else{
                return true;
            }
        }
    }
    
    
    /*
     * 修改学生信息的启用或禁用状态
     */
    public function updateEnableStatus($id,$status,$school_id=0){
        if($school_id!=0){
            $where['school_id']=$school_id;
        }
        $where['id']=$id;
        $data['flag']=$status;
        if($this->model->where($where)->save($data)){
            return true;
        }else{
            return false;
        }
    }


    /*
    * 修改权限
    */
    public function updateStudentPrivilege($data,$id){
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
    public function addStudentPrivilege($data){
        $model=M('account_user_and_auth');
        if($model->add($data)){
            return true;
        }else{
            return false;
        }
    }

    /*
     * 得到某个用户的权限信息
     */
    public function getStudentPrivilegeInfo($student_id){
        $model=M('account_user_and_auth');
        $where['user_id']=$student_id;
        $where['role_id']=STUDENT_ROLE;
        $result=$model->where($where)->field('*,case when auth_id=4  and account_user_and_auth.auth_start_time<UNIX_TIMESTAMP(NOW()) and'
            . ' account_user_and_auth.auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')->find();    //echo $model->getLastsql();die;
        return $result;
    }

    /*
     * 获得某个学生的个人信息
     */
    public function getStudentSimpleData($student_id){
        $where['auth_student.id']=$student_id; 
        $result=$this->model->where($where)  
                ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id') 
                ->field('auth_student.id,auth_student.sex,auth_student.birth_date,auth_student.parent_tel,password,school_name,school_code,auth_student.flag,apply_school_status') 
                ->find(); 
        return $result;
    }
    
    /*
     * 获得某个学生和学校等相关信息
     */
    public function getStudentSchoolData($student_id,$school_id=0){
        if($school_id!=0){
            $where['auth_student.school_id']=$school_id;
        }
        $where['auth_student.id']=$student_id;
        $result=$this->model->where($where)  
                ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id') 
                ->join('dict_citydistrict province on province.id=dict_schoollist.provice_id')
                ->join('dict_citydistrict city on city.id=dict_schoollist.city_id')
                ->join('dict_citydistrict district on district.id=dict_schoollist.district_id')
                ->join('account_user_and_auth on account_user_and_auth.user_id=auth_student.id and account_user_and_auth.role_id=3 and account_user_and_auth.auth_id = 3','left')
                ->join('account_user_and_auth a on a.user_id=auth_student.id and a.role_id=3 and a.auth_id = 4','left')
                ->field('dict_schoollist.school_category,account_user_and_auth.auth_start_time,account_user_and_auth.auth_end_time,auth_student.id,auth_student.student_name,auth_student.sex,auth_student.birth_date,auth_student.parent_tel,password,auth_student.school_id,'
                        . 'school_name,school_code,auth_student.flag,apply_school_status,'
                    .'CASE
WHEN account_user_and_auth.auth_id = 3 THEN account_user_and_auth.auth_start_time 
ELSE
a.auth_start_time
END AS startime,
CASE
WHEN account_user_and_auth.auth_id = 3 THEN account_user_and_auth.auth_end_time 
ELSE
a.auth_end_time
END AS endtime,'
                        . 'province.id province_id,province.name province,city.id city_id,city.name city,district.id district_id,district.name district,'
                        . 'case when account_user_and_auth.auth_start_time<UNIX_TIMESTAMP(NOW()) and account_user_and_auth.auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')
                ->find();

        return $result;
    }
    
     
    /*
     * 获得某个学生的家长信息
     */
    public function getStudentAllParent($student_id,$school_id=0,$parent_id=0){
        $where['auth_student.id']=$student_id;
        if($school_id!=0){
            $where['auth_student.school_id']=$school_id;
        }
        if($parent_id!=0){
            $where['auth_student_parent_contact.parent_id']=$parent_id;
        }
        $result=$this->model->where($where)->join('auth_student_parent_contact on auth_student_parent_contact.student_id=auth_student.id')
                    ->join('auth_parent on auth_parent.id=auth_student_parent_contact.parent_id')
                    ->field('auth_parent.id,auth_parent.parent_name,auth_parent.telephone,auth_parent.sex')
                    ->select();                            
        return $result;
    }
    
    
    /*
     * 获得某个学生加入的班级总数
     */
    public function getClassCountByStudent($condition=array()){ 
        $condition['biz_class_student.status']=CLASS_STUDETN_STATUS;
        $result=$this->model->where($condition)->join('biz_class_student on biz_class_student.student_id=auth_student.id')
                    ->join('biz_class on biz_class.id=biz_class_student.class_id')
                    ->join('dict_grade on dict_grade.id=biz_class.grade_id') 
                    ->group('biz_class.id')  
                    ->count('1');              
        return $result;
    }
    
    
    /*
     * 获得某个学生加入的班级
     */
    public function getClassByStudent($condition=array(),$order='desc'){    
        $count=$this->getClassCountByStudent($condition);  
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show=$Page->show();     
        
        //$condition['biz_class_student.status']=CLASS_STUDETN_STATUS;
        $result=$this->model->where($condition)->join('biz_class_student on biz_class_student.student_id=auth_student.id')
                    ->join('biz_class on biz_class.id=biz_class_student.class_id')
                    ->join('dict_grade on dict_grade.id=biz_class.grade_id')
                    ->field('biz_class.id,biz_class.name class_name,biz_class.class_status,biz_class.class_code')
                    ->group('biz_class.id') 
                    ->limit($Page->firstRow . ',' . $Page->listRows)
                    ->order('biz_class_student.create_at '.$order)
                    ->select();              
        $data['count']=$count;
        $data['data']=$result;
        $data['page']=$show;
        return $data;
    }
    
    
    
    
     /*
     * 获得某个学生加入的全部班级数据
     */
    public function getClassByStudentData($condition=array(),$order='desc'){    
        //$condition['biz_class_student.status']=CLASS_STUDETN_STATUS;
        $result=$this->model->where($condition)->join('biz_class_student on biz_class_student.student_id=auth_student.id')
                    ->join('biz_class on biz_class.id=biz_class_student.class_id AND biz_class.is_delete =0')
                    ->join('dict_grade on dict_grade.id=biz_class.grade_id')
                    ->field('biz_class.id,biz_class.name class_name,biz_class.class_status,biz_class.class_code,dict_grade.grade')
                    ->group('biz_class.id') 
                    ->limit($Page->firstRow . ',' . $Page->listRows)
                    ->order('biz_class_student.create_at '.$order)
                    ->select();                         
        return $result;
    }
    
    
    public function getStudentParentTel($student_name,$parent_tel){
        $condition['auth_student.student_name']=$student_name;
        $condition['auth_student.parent_tel']=$parent_tel;
        $result=$this->model->where($condition)->field('id,student_name,parent_tel')->find();
        return $result;
    }
    
    
    /*
     * 获得所有学生,根据注册填写的家长手机号
     */
    public function getStudentByParentTel($telephone){
        $where['parent_tel']=$telephone;
        $result=$this->model->where($where)->field('id,student_name,parent_tel')->select();
        return $result;
    }
    
    
    /*
     * 插入学生表数据
     */
    public function addStudentData($data){
        if($insert_id=$this->model->add($data)){
            return $insert_id;
        }else{
            return false;
        }
    }
     
    
    
    /*
     * 获得固定的条数的学生信息
     */
    public function getStudentFixedData($condition=array(),$rows=10){
        $result=$this->model->where($condition)
                    ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id')
                    ->join('biz_class_student on biz_class_student.student_id=auth_student.id','left')
                    ->join('biz_class on biz_class.id=biz_class_student.class_id','left')
                    ->join('dict_grade on dict_grade.id=biz_class.grade_id','left') 
                    ->field('auth_student.id,auth_student.student_name,auth_student.sex,auth_student.parent_tel,dict_schoollist.school_name,dict_grade.grade,biz_class.name class_name') 
                    ->limit($rows) 
                    ->order('auth_student.create_at '.$order)
                    ->group('auth_student.id')
                    ->select();          
        return $result;
    }
    
    
    /*
     * 插入学生表
     */
    public function studentAdd($data){
        if(!($insert_id=$this->model->add($data))){
            return false;
        }else{
            return $insert_id;
        }
    }

    //获取学生的学习轨迹
    public function getStudentPath( $where ) {

        $Model = M('biz_homework_student_details');
        $result = $Model
            ->join('biz_homework on biz_homework.id=biz_homework_student_details.homework_id')
            ->join('biz_homework_score_details on biz_homework_score_details.student_id=biz_homework_student_details.student_id and biz_homework_score_details.homework_id=biz_homework.id')
            ->join('auth_student on auth_student.id=biz_homework_student_details.student_id')
            ->field("biz_homework.id as homework_id,biz_homework.homework_name,biz_homework_student_details.points,auth_student.student_name,auth_student.id as student_id")
            ->where($where)
            ->order('biz_homework.create_at asc')
            ->select();
        return $result;
    }

    /*
     * 更新学生手机号
     */
    public function updateStudentTelephone($name,$originalTelephone,$newTelephone)
    {
        $result = M()->execute("UPDATE auth_student SET parent_tel=$newTelephone WHERE parent_tel=$originalTelephone AND student_name='$name'");
        return ($result !== false);
    }

    /**
     *描述：获取所有学生已登录的人数（折线图用）
     */
    public function getAlreadyLoggedInCount($id,$where,$goup){
       /* $result = $this->model
            ->field('count(1) count,biz_class.name,biz_class.id')
            ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id')
            ->join('access_history_student on access_history_student.student_id=auth_student.id')
            ->join('biz_class_student on biz_class_student.student_id=auth_student.id and biz_class_student.status!=3 and biz_class_student.status='.CLASS_STUDETN_STATUS,'left')
            ->join('biz_class on biz_class.id=biz_class_student.class_id and biz_class.is_delete=0 ','left')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->where($where)
            ->group('biz_class.id')
            ->select();*/
        $sql = "SELECT
	count(distinct(auth_student.id)) access_total,
	access_time
FROM
	`auth_student`
INNER JOIN dict_schoollist ON dict_schoollist.id = auth_student.school_id
INNER JOIN usertables.access_history_student a ON a.student_id = auth_student.id
LEFT JOIN biz_class_student ON biz_class_student.student_id = auth_student.id
AND biz_class_student. STATUS != 3
AND biz_class_student. STATUS = 2
LEFT JOIN biz_class ON biz_class.id = biz_class_student.class_id
AND biz_class.is_delete = 0
WHERE
$where
AND auth_student.school_id = $id
GROUP BY
	$goup";
        $result = $this->model->query($sql);
        return $result;
    }

    /**
     *描述：获取所有学生已登录的人数（柱状图用）
     */
    public function getAlreadyLoggedInCount2($where=''){
         $result = $this->model
             ->field('count(distinct(auth_student.id)) access_total,biz_class.name,biz_class.id')
             ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id')
             ->join('usertables.access_history_student on usertables.access_history_student.student_id=auth_student.id')
             ->join('biz_class_student on biz_class_student.student_id=auth_student.id and biz_class_student.status!=3 and biz_class_student.status='.CLASS_STUDETN_STATUS,'left')
             ->join('biz_class on biz_class.id=biz_class_student.class_id and biz_class.is_delete=0 ','left')
             ->join('dict_grade on dict_grade.id=biz_class.grade_id')
             ->where($where)
             ->group('biz_class.id')
             ->select();
        return $result;
    }

    /**
     *描述：获取所有的学生人数
     */
    /*public function getTotal($where = ''){
        $this->model
            ->field('count(1) count,biz_class.name,biz_class.id')
            ->join('dict_schoollist on dict_schoollist.id=auth_student.school_id')
            ->join('biz_class_student on biz_class_student.student_id=auth_student.id and biz_class_student.status!=3 and biz_class_student.status='.CLASS_STUDETN_STATUS,'left')
            ->join('biz_class on biz_class.id=biz_class_student.class_id and biz_class.is_delete=0 ','left')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->group('auth_student.id')
            ->where($where)
            ->group('biz_class.id')
            ->select();
    }*/
}
 