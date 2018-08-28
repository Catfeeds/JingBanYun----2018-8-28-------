<?php
namespace Common\Model;
use Think\Model;
define('SCHOOL_ROLE',3);

class Auth_adminModel extends Model{

    public    $model='';
    protected $tableName = 'auth_admin';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }
    
      
    /*
     * 用户登录
     */
    public function userLogin($user_name,$password,$school_role=''){
        if($school_role==1){
            $condition['role']=array('eq',SCHOOL_ROLE);
        }else if($school_role==2){
            $condition['parent_id']=array('eq',0);
            $condition['role']=array('neq',SCHOOL_ROLE);
        }
        $condition['name']=$user_name;
        $condition['password']=$password; 
        $result=$this->model->where($condition)->find();           
        return $result;
    }
     
       
    /*
     * 得到一条学校管理员信息
     */
    public function getSchoolAdminData($id,$super_admin_flag=0,$password=''){
        if($super_admin_flag!=0){
            $condition['parent_id']=0;
        }
        if($password!=''){
            $condition['password']=$password;
        }
        $condition['id']=$id;
        $condition['role']=SCHOOL_ROLE;
        $result=$this->model->where($condition)->find(); 
        return $result;
    }
    
    
    /*
     * 修改学校管理员信息
     */
    public function updateSchoolAdminData($id,$data){
        $where['role']=SCHOOL_ROLE;
        $where['id']=$id;
        if($this->model->where($where)->save($data)===false){
            return false;
        }else{  
            return true;
        }
    }
    
    /*
     * 创建学校管理员
     */
    public function addAdminData($data){
        if($this->model->add($data)){
            return true;
        }else{
            return false;
        }
    }
    
    
    
}