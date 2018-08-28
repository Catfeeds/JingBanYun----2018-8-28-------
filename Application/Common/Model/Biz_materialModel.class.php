<?php
namespace Common\Model;
use Think\Model; 

class Biz_materialModel extends Model{
    
    public    $model='';
    protected $tableName = 'biz_material';  
	
    public function __construct(){  
        parent::__construct();  
        $this->model=M($this->tableName);
    }
     
    /*
     * 获得全部我的素材
     * @teach_id    老师id
     * @type 类型
     */
    public function getAllMaterial($teach_id,$type=''){
        $model=$this->model;
        $where['teacher_id'] = $teach_id;
        if(!empty($type))
         $where['type'] = $type;
        $result=$model->field('id,type,teacher_id,create_at,vid_image_path,file_path,vid')->where($where)->order('create_at desc')->select();
        return $result;
    }
    
    /*
     * 获得某一个素材
     * @id  素材id
     */
    public function getMaterial($id){
        $model=$this->model;
        $result=$model->field('id,type,teacher_id,create_at,file_path,vid,material_name,flag')->where("id='$id'")->find();
        return $result;
    }

    /*
     * 删除某一个素材
     * @id  素材id
     */
    public function deleteMaterial($id){
        $model=$this->model;
        if($model->where("id='$id'")->delete()){
            return true;
        }else{
            return false;
        }
    }

    /*
     * 保存素材
     * @data    对应的数据和值
     */
    public function saveMaterial($data){
        $model=$this->model;
        if($model->add($data)){     
            return true;
        }else{
            return false;
        }
    }
}