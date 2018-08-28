<?php
namespace Common\Model;
use Think\Model; 

class Biz_resource_contactModel extends Model{
    
    public    $model='';
    protected $tableName = 'biz_resource_contact';  
	
    public function __construct(){
        parent::__construct();  
        $this->model=M($this->tableName);
    } 
    
    /*
     * 获得某个资源的关联资源
    */
    public function getContactResource($id){
        $model=$this->model;
        $result=$model->field('id,biz_resource_id,resource_path,vid,flag')
                    ->where("biz_resource_id=".$id)->select(); 
        return $result;
    }
     
    
    /*
     * 删除某个资源的关联资源
     */
    public function deleteContactResource($id){
        $model=$this->model;
        if($model->where("biz_resource_id=".$id)->delete()){
            return true;
        }else{
            return false;
        }
    }
   
}