<?php
namespace Common\Model;
use Think\Model; 

class CommonModel extends Model{
    
    public    $model='';
    protected $tableName = 'auth_parent';  
	
    public function __construct(){  
        parent::__construct();  
        $this->model=M($this->tableName);
    }
     
    
   //获得所有省份
    public function getAllProvince(){
        $Model = M('dict_citydistrict');
        $result = $Model-> where('level=1')-> field ('id,name')-> select();
        return $result; 
    }
    
    //获得所有学科
    public function getAllCourse(){
        $Model = M('dict_course');
        $result = $Model->order('sort_order asc')->field('id,course_name,code')->select();
        return $result;
    }

    public function getUserName($userId,$role)
    {
        $result = [];
        switch ($role){
            case ROLE_TEACHER : $result = M()->query("SELECT name FROM auth_teacher WHERE id=$userId LIMIT 1");
                                 break;
            case ROLE_STUDENT : $result = M()->query("SELECT student_name name FROM auth_student WHERE id=$userId LIMIT 1");
                                 break;
            case ROLE_PARENT : $result = M()->query("SELECT parent_name name FROM auth_parent WHERE id=$userId LIMIT 1");
                                 break;
            default:break;
        }
        return $result[0]['name'];
    }
}