<?php
namespace Api\Controller;
use Think\Controller;

class StudyController extends Controller
{ 
    public $model='';
    public $page_size=20;
    public $firstRow=0;
    public $listRow=0;
            
    public function __construct() {
        parent::__construct(); 
        $this->model=D('Biz_homework'); 
    }
 
   //教师学习轨迹
    public function teachStudyList(){
        //班级id
        $id=intval(I('id'));
        if(!$id){
            //$this->showjson(-1,'failed',array());
            $this->assign('classId', null); 
            $this->display();die;
        }
        //$model=D('Biz_class');
        //$data=$model->getGradeClass($id); 
        
        //$class_student_model=D('Biz_class_student');
        //$class_student_result=$class_student_model->getClassStudent($id);   
        
        $model=D('Biz_class_student');
        $result=$model->teachStudy($id);        
        $this->assign('data_exists', $result);  
        
        //$this->assign('list', $class_student_result);
        $this->assign('classId', $id);  
        //$this->assign('class', $data);  
        
        $this->display();
    }
    
    //家长学习轨迹
    public function parentStudyList(){
        $id=intval(I('id'));
        if(!$id){
            //$this->showjson(-1,'failed',array());
            $this->assign('student_id', null);
            $this->display();die;
        }
        $parent_model=D('Auth_parent');
        $parent_result=$parent_model->getParentInfo($id);        
        if(empty($parent_result)){
            //$this->showjson(-2,'failed',array()); 
            $this->assign('student_id', null);   
            $this->display();die;
        }
        $student_model=D('Auth_student');
        $student_info=$student_model->getParentStudent($id);        
        if(empty($student_info)){
            //$this->showjson(-3,'failed',array()); 
            $this->assign('student_id', null);
            $this->display();die;
        }
        $student_ids='';
        foreach($student_info as $v){
            $student_ids.=$v['id'].',';
        }
        $student_ids=rtrim($student_ids,',');       
        
        //$result
        $model=D('Biz_class_student');
        $result=$model->parentStudy($student_ids);
        $this->assign('data_exists', $result); 
        
        $this->assign('student_id', $student_ids); 
        $this->display();
        
    }
    
}