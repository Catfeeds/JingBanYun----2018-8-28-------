<?php
namespace Common\Model;
use Think\Model; 

class Biz_resource_collectModel extends Model{
    
    public    $model='';
    protected $tableName = 'biz_resource_collect';  
	
    public function __construct(){
        parent::__construct();  
        $this->model=M($this->tableName);
    } 
    
    /*
     * 获得某个角色收藏的全部资源
     * @role    角色
     * @user_id 用户id
     */
   public function getAllTeachResource($parameter_role,$user_id){  
       if($parameter_role==1){
           //教师
           $role=0;
       }elseif($parameter_role==2){
           //学生
           $role=1;
       }else{
           //家长
           $role=2;
       }
        $model=$this->model;
        $result=$model->field('resource_id,user_id,user_type,create_at,user_name')->where("user_id=$user_id and "."user_type='$role'")->order('create_at desc')->select();  
        return $result;
   }
      
   
   /*
     * 获得某个角色收藏的资源
     * @role    角色
     * @user_id 用户id
     */
   public function getTeachResource($parameter_role,$user_id){  
       if($parameter_role==1){ 
           $role=0;
       }elseif($parameter_role==2){ 
           $role=1;
       }else{ 
           $role=2;
       }
        $model=$this->model;
        $result=$model->field('resource_id,user_id,user_type,create_at,user_name')->where("user_id=$user_id and "."user_type='$role'")->order('create_at desc')->find();  
        return $result;
   }
   
   /*
    * 删除某个角色收藏的资源
    * @id   收藏ID
    */
   public function deleteResource($id){
       $model=$this->model;
       if($model->where("id=".$id)->delete()){
           return true;
       }else{
           return false;
       }
   }
   
}