<?php
namespace Common\Model;
use Think\Model; 

class School_listModel extends Model{
    
    public    $model='';
    protected $tableName = 'auth_student';  
	
    public function __construct(){  
        parent::__construct();  
        $this->model=M($this->tableName);
    }
     
    /*
     * 公共插入权限方法      
     */ 
    public function getMemberList( $id ){

        $map['school_id'] = $id;
        //查询家长
        //查询学生
        $auth_student = M('auth_student')->where( $map )->field('id,parent_id')->select();

        if(!empty($auth_student)) {
            $auth_parent = array();
            $ar = array();
            foreach ($auth_student as $key => $value) { //根据学生查询家长，让家长也成为vip
                $map_student['id'] = $value['parent_id'];
                $row = M('auth_parent')->where( $map_student )->field('id')->find();

                if (!empty($row['id']) && !in_array($row['id'], $ar)) {
                    $auth_parent[] = $row;
                    $ar[] = $row['id'];
                }
                
            }
        }
        
        //$auth_parent = M('auth_parent')->where( $map )->field('id')->select();
        //查询老师
        $auth_teacher = M('auth_teacher')->where( $map )->field('id')->select();

        $data = array(
            '4' => $auth_parent,
            '3' => $auth_student,
            '2' => $auth_teacher,
        );
        
        return $data;
    }

}