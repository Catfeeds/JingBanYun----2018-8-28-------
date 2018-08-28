<?php
namespace Common\Model;
use Think\Model;
define('SCHOOL_ROLE',3);

class Auth_school_adminModel extends Model{

    public    $model='';
    protected $tableName = 'auth_admin';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }
     
    
     
    /*
     * 用户登录
     */
    public function userLogin($user_name,$password){
        $condition['role']=SCHOOL_ROLE;
        $condition['name']=$user_name;
        $condition['password']=$password; 
        $result=$this->model->where($condition)->find(); 
        return $result;
    }
    
    
    /*
     * 根据id,密码获得管理员信息
     */
    public function getAadminDataByPassword($id,$password){
        $condition['password']=$password;
        $condition['id']=$id;
        $result=$this->model->where($condition)->select();
        return $result;
    }
    
    
    /*
     * 得到所有学校管理员数据
     */
    public function getSchoolAdminDataAll($condition=array(),$order='desc'){
        $condition['role']=SCHOOL_ROLE;
        $result=$this->model->where($condition)->join('dict_schoollist on dict_schoollist.id=auth_admin.school_id')
                    ->field('auth_admin.id,real_name,auth_admin.name account,school_name,auth_admin.telephone,parent_id,school_flag')
                    ->order('auth_admin.create_at '.$order)
                    ->select();     
        return $result;
    }
    

    /*
     * 获得所有学校管理员总数
     */
    public function getSchoolAdministartorCount($condition=array()){
        $condition['role']=SCHOOL_ROLE;
        $result=$this->model->where($condition)->join('dict_schoollist on dict_schoollist.id=auth_admin.school_id')
                    ->count('1');
        return $result;
    }
    
    
    /*
     * 获得学校管理员数据
     */
    public function getSchoolAdministartor($condition=array(),$order='desc'){        
        $count=$this->getSchoolAdministartorCount($condition);      
        
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show=$Page->show();
        
        $condition['role']=SCHOOL_ROLE;
        $result=$this->model->where($condition)->join('dict_schoollist on dict_schoollist.id=auth_admin.school_id')
                    ->field('auth_admin.id,real_name,auth_admin.name account,school_name,auth_admin.telephone,parent_id,school_flag')
                    ->limit($Page->firstRow . ',' . $Page->listRows)
                    ->order('auth_admin.create_at '.$order)
                    ->select();           
        $data['page']=$show;
        $data['data']=$result;
        $data['count']=$count;  
        return $data;
    }
    
    
    /*
     * 更改某个学校管理员的状态
     */
    public function updateAdminStatus($school_admin_id,$status,$child_admin_falg=0){
        $condition['auth_admin.id']=$school_admin_id; 
        $condition['role']=SCHOOL_ROLE; 
        if($child_admin_falg!=0){
            $condition['parent_id']=array('neq',0); 
        }
        $data['school_flag']=$status;
        if($this->model->where($condition)->save($data)){
            return true;
        }else{  
            return false;
        }
    }
    
    
    /*
     * 获得所有权限
     */
    public function getAllPermissions($module_type){
        $where['module_type']=$module_type;
        $model=M('school_permissions');
        $result=$model->where($where)->field('id,module_action,module_name,parent_id')->select();
        return $result;
    }
    
    
    
    /*
     * 获得某个学校管理员与其学校的信息
     */
    public function getSchoolAdminData($school_admin_id,$admin_parent_id=0){
        $condition['auth_admin.id']=$school_admin_id; 
        $condition['role']=SCHOOL_ROLE; 
        if($admin_parent_id!=0){
            $condition['parent_id']=$admin_parent_id; 
        } 
        $result=$this->model->where($condition)->join('dict_schoollist on dict_schoollist.id=auth_admin.school_id')
                    ->join('dict_citydistrict province on province.id=dict_schoollist.provice_id')
                    ->join('dict_citydistrict city on city.id=dict_schoollist.city_id')
                    ->join('dict_citydistrict district on district.id=dict_schoollist.district_id') 
                    ->field('auth_admin.id,auth_admin.password,auth_admin.school_id,province.id province_id,province.name province,city.id city_id,city.name city,district.id district_id,district.name district,obligation_person,obligation_tel,obligation_email,'
                            . 'school_name,dict_schoollist.school_code,dict_schoollist.school_category,dict_schoollist.school_address,auth_admin.name account,real_name,auth_admin.email,auth_admin.telephone,'
                            . 'parent_id,dict_schoollist.flag school_flag,auth_admin.school_flag admin_flag,auth_start_time,auth_end_time,'
                            . 'case when user_auth=3 and auth_start_time<=UNIX_TIMESTAMP(NOW()) and auth_end_time>UNIX_TIMESTAMP(NOW()) then 1 else 2 end as permissions_status')      
                    ->find();          
        return $result;
    }
    
    
    /*
     * 获得某个子级学校管理员的权限
     */
    public function getAdminPermissions($school_admin_id,$parent_id_flag=''){
        $condition['auth_admin.id']=$school_admin_id;
        $condition['role']=SCHOOL_ROLE;    
        $group_string='school_permissions.id';
        if($parent_id_flag===true){
            $condition['school_permissions.parent_id']=0;
        }elseif($parent_id_flag===false){        
            $condition['school_permissions.parent_id']=array('neq',0);
        } 
        $result=$this->model->where($condition)->join('school_admin_permissions on school_admin_permissions.school_admin_id=auth_admin.id')
                    ->join('school_permissions on school_permissions.id=school_admin_permissions.permissions_id')
                    ->field('school_permissions.id,module_action,module_name,module_type,school_permissions.parent_id') 
                    ->group($group_string)
                    ->order('school_permissions.parent_id asc')
                    ->select();            
        return $result;
    }
    
    
    
    /*
     * 得到所有权限ID信息
     */ 
    public function getPermissionsData($condition=array()){
        $model=M('school_permissions');
        $result=$model->where($condition)->select();   
        return $result;
    }
    
    
    /*
     * 删除某个管理员(子级)的权限
     */
    public function deleteAdminPermissions($id){
        $model=M('school_admin_permissions');
        $where['school_admin_id']=$id;
        if($model->where($where)->delete()===false){
            return false;
        }else{
            return true;
        }
    }
     
    
    /*
     * 添加某个管理员的权限
     */
    public function addAdminPermissions($id,$data){
        $model=M('school_admin_permissions');
        $per_data=array();
        foreach($data as $val){ 
            $per_child_data['permissions_id']=$val;
            $per_child_data['school_admin_id']=$id;
            $per_child_data['create_at']=time();
            $per_data[]=$per_child_data;
        }
        if($model->addAll($per_data)===false){
            return false;
        }else{  
            return true;
        }
    }
    
    
    /*
     * 获得某个顶级管理员信息
     */
    public function getSchoolAdminBYSchool($school_id){
        $top_admin=0;
        $where['school_id']=$school_id; 
        $where['parent_id']=$top_admin;
        $result=$this->model->where($where)->find();
        return $result;
    }
    
    
    
    /*
     * 修改某个顶级管理员信息
     */
    public function updateSchoolAdminData($id,$data){
        $top_admin=0;
        $where['id']=$id;
        $where['parent_id']=$top_admin;
        if($this->model->where($where)->save($data)===false){   
            return false;
        }else{ 
            return true;
        }
    }
    
    
    /*
     * 获得某个子级管理员的信息
     */
    public function getSchoolChildAdminData($id){
        $where['id']=$id;
        $where['parent_id']=array('neq',0);
        $result=$this->model->where($where)->field('id,name,parent_id')->find();
        return $result;
    }


    /*
    * 删除管理员 
    */
   public function deleteSchoolAdmin($id){   
       $condition['id']=$id;
       $condition['parent_id']=array('neq',0);
       if($this->model->where($condition)->delete()){
           return true;
       }else{
           return false;
       }
   }
   
   
   /*
    * 修改某个子级管理员信息
    */
   public function updateSchoolAdmin($id,$data){
       $where['id']=$id;
       $where['parent_id']=array('neq',0);
       if($this->model->where($where)->save($data)===false){
           return false;
       }else{
           return true;
       }
   }
   
   
   /*
    * 根据id字符串获得某些权限
    */
   public function getPermissionsById($ids){
       $model=M('school_permissions');
       $where['id']=array('in',$ids);
       $function_flag=2;
       $where['module_type']=$function_flag;
       $result=$model->where($where)->field('id,module_action,module_name')->find();
       return $result;
   }
   
   
   /*
    * 修改某个子级管理员的权限  暂没用到
    */
   public function updateSchoolAdminPermissions($id,$permissions_arr=array()){  
       $model=M('school_admin_permissions');
       if(empty($permissions_arr)){
           return true;
       }else{
            $result=$this->getSchoolChildAdminData($id);
            if(empty($result)){
               return false; 
            } 
            $count=count($permissions_arr);
            $permissions_string=rtrim(explode(',',$permissions_arr),',');
            $per_result=$this->getPermissionsById($permissions_string);
            if($count!=(count($per_result))){
                return false;
            } 
            $where['school_id']=$id;
            if($model->where($where)->delete()===false){
                return false;
            } 
            foreach($permissions_arr as $val){ 
                $add_data['permissions_id']=$val;
                $add_data['school_admin_id']=$id;
                $add_data['create_at']=time();
                if(!$model->add($add_data)){
                    return false;
                }
            }
            return true;
       }    
   }
   
   
   /*
    * 添加管理员
    */
   public function addAdmin($data){
       if(!($insert_id=$this->model->add($data))){
           return false;
       }else{
           return $insert_id;
       }
   }
   
   
   /*
    * 插入某个用户的权限
    */
   public function addSchoolAdminPermissions($id,$permissions_arr=array()){
       foreach($permissions_arr as $val){ 
            $add_data['permissions_id']=$val;
            $add_data['school_admin_id']=$id;
            $add_data['create_at']=time();
            if(!$model->add($add_data)){
                return false;
            }
        }
        return true;
   }
   
   
   
   
}