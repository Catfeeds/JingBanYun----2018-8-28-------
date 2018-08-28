<?php
namespace Api\Controller;
use Think\Controller;

class BlackboardController extends Controller
{ 

    public $model='';
    public $page_size=20;
    public $firstRow=0;
    public $listRow=0;
            
    public function __construct() {
        parent::__construct(); 
        $this->model=D('Biz_blackboard');
        header("Content-type: text/html; charset=utf-8");
    }

    //分页处理
    public function pageOperation(){
        if(I('page')){
            $page=I('page')<1?1:I('page');
        }else{
            $page=1;
        }
        $data['start']=($page-1)*$this->page_size;
        $data['end']=  $this->page_size;
        return $data;
    }
    
    //老师的小黑板列表
    public function teachBlackboardList(){
        //老师ID
        $id=intval(I('id'));
        if(!$id){
            $this->showjson(-1,'参数传递不正确',array());
        }   
        $teach_model=D('Auth_teacher');
        $teach_info=$teach_model->getTeachInfo($id);    
        if(empty($teach_info)){
            $this->showjson(-2,'教师信息不存在',array());
        }      
        $pageData=$this->pageOperation();
        $pageStart=$pageData['start'];
        $pageEnd=$pageData['end']; 
        $model=$this->model; 
        //班级id 
        $result=$model->getTeachBlackboard($id,$pageStart,$pageEnd);   
        $class_model=D('Biz_class'); 
        foreach($result as $k=>$v){
            $class_result=$class_model->getClassInfo($v['class_id']);
            if(empty($class_result)){ 
                //$result[$k]['class_name']=null;
                unset($result[$k]);
            }else{
                $result[$k]['class_name']=$class_result['name'];
            }
        }
        foreach($result as $k=>$v){		
		 $num[$k] = $v['create_at'];
		}
		array_multisort($num, SORT_DESC, $result);
        $this->showjson(1,'success',$result); 
        
    }
    
    
    //学生小黑板列表
    public function studentBlackboardList(){
        //学生ID
        $id=intval(I('id'));
        if(!$id){
            $this->showjson(-1,'参数传递不正确',array());
        } 
        $student_model=D('Auth_student');
        $student_info=$student_model->getStudentInfo($id);      
        if(empty($student_info)){
            $this->showjson(-2,'学生信息不存在',array()); 
        }
        $student_class_model=D('Biz_class_student');
        $student_class=$student_class_model->getStudentClass($id);  
        if(empty($student_class)){
            $this->showjson(-3,'你还没有加入班级',array());      //之前为-3
        }else{    
            $ids='';
            foreach($student_class as $v){
                $ids.=$v['class_id'].','; 
            } 
            $ids=rtrim($ids,',');  
            $pageData=$this->pageOperation();
            $pageStart=$pageData['start'];
            $pageEnd=$pageData['end']; 
            $model=$this->model;
            $data=$model->getIdsBlackboard($ids,$pageStart,$pageEnd);
            $class_model=D('Biz_class'); 
            foreach($data as $k=>$v){
                $class_result=$class_model->getClassInfo($v['class_id']);
                if(empty($class_result)){
                    //$data[$k]['class_name']=null;
                    unset($data[$k]);
                }else{
                    $data[$k]['class_name']=$class_result['name'];
                }
            }  
            foreach($data as $k=>$v){		
		     $num[$k] = $v['create_at'];
		    }
		    array_multisort($num, SORT_DESC, $data);
            $this->showjson(1,'success',$data);
        } 
    }
    
    
    //家长的小黑板列表
    public function ParentBlackboardList(){ 
        //家长id
        $id=intval(I('id'));
        if(!$id){
            $this->showjson(-1,'参数传递不正确',array());
        }
        $parent_model=D('Auth_parent');
        $parent_result=$parent_model->getParentInfo($id);        
        if(empty($parent_result)){
            $this->showjson(-2,'家长信息不存在',array());        
        }
        $student_model=D('Auth_student');
        $student_info=$student_model->getParentStudent($id);        
        if(empty($student_info)){
            $this->showjson(-3,'您还没有添加孩子',array());   //之前为-3
        }
        $student_ids='';
        foreach($student_info as $v){
            $student_ids.=$v['id'].',';
        }
        $student_ids=rtrim($student_ids,',');  
        $student_model=D('Biz_class_student');
        $student_class=$student_model->getStudentClassInfo($student_ids);   
        if(empty($student_class)){
            $this->showjson(-4,'您的小孩还没有加入班级',array());      //之前为-4
        }else{
            $ids='';
            foreach($student_class as $v){
                $ids.=$v['class_id'].','; 
            } 
            $ids=rtrim($ids,',');   
            $pageData=$this->pageOperation();
            $pageStart=$pageData['start'];
            $pageEnd=$pageData['end']; 
            $model=$this->model;
            $data=$model->getIdsBlackboard($ids,$pageStart,$pageEnd,1);   
            $class_model=D('Biz_class'); 
            foreach($data as $k=>$v){
                $class_result=$class_model->getClassInfo($v['class_id']);
                if(empty($class_result)){
                    //$data[$k]['class_name']=null;
                    unset($data[$k]);
                }else{
                    $data[$k]['class_name']=$class_result['name'];
                }
            }
            foreach($data as $k=>$v){		
		     $num[$k] = $v['create_at'];
		    }
		    array_multisort($num, SORT_DESC, $data);
            $this->showjson(1,'success',$data);
        }
    }
    
    
    //小黑板删除操作
    public function balckboardDelete(){
        $id=intval(I('id'));
        if(!$id){
            //参数错误 之后商量返回负几
            $this->showjson(-1,'参数传递不正确',array());
        }else{
            $model=$this->model;
            if(($status=$model->deleteBlackboard($id))==true){ 
                $this->showjson(1,'success',array());
            }else{ 
                $this->showjson(-1,'删除信息失败',array());
            }
        }
    }
    
    //小黑板编辑,读取或查看一条数据
    public function browseBlackboard(){
        $id=intval(I('id'));
        if(!$id){
            //参数错误 之后商量返回负几
            $this->showjson(-1,'参数传递不正确',array());
        }else{
            $model=$this->model;
            $result=$model->get_one_data($id);  
            if(empty($result)){
                $this->showjson(-2,'小黑板信息不存在',array());
            }else{
                //得到班级名称
                $class_model=D('Biz_class');
                $class_result=$class_model->getClassInfo($result['class_id']);  
                if(empty($class_result)){
                    $this->showjson(-3,'班级信息不存在',array());
                }
                
                //$this->showjson(1,'success',$result);
                $teacher_model=D('Auth_teacher');
                $data=$teacher_model->getTeachInfo($class_result['class_teacher_id']); 
                $result['publisher']=($data['name']==null)?'':$data['name'];   
                $result['class_name']=$class_result['name'];
                $this->showjson(1,'success',$result);
            }
        }
    }
    
    //小黑板编辑保存数据 
    public function saveBlackboard(){
        $id=intval(I('id'));
        $this->showjson(-1,'请升级新版本',array());die;
        if(!$id){
            //参数错误 
            $this->showjson(-1,'参数传递不正确',array());
        }else{ 
            $model=$this->model;
            $data['message_title']=remove_xss(I('message_title'));
            $data['message']=remove_xss(I('message'));  
            if($model->updateBlackboard($id,$data)){
                $this->showjson(1,'success',array()); 
            }else{
                $this->showjson(-2,'修改信息失败',array());
            }
        }
    }
    
    //添加小黑板信息
    public function addBlackboard(){
        //老师id
        $id=intval(I('id'));
        $class_id=intval(I('class_id'));
        if(!$id){
            //参数错误 
            $this->showjson(-1,'参数传递不正确',array());
        }
        $teach_model=D('Auth_teacher'); 
        $teach_info=$teach_model->getTeachInfo($id);   
        if(empty($teach_info)){
            $this->showjson(-2,'教师信息不存在',array());
        }
        $class_model=D('Biz_class');
        $class_result=$class_model->getClassInfo($class_id,$id);   
        if(empty($class_result)){
            $this->showjson(-3,'班级信息不存在',array());
        } 
        $model=$this->model;
        $data['message_title']=remove_xss(I('message_title'));
        $data['message']=remove_xss(I('message'));  
        $data['publisher']=$teach_info['name'];  
        $data['publisher_id']=$id;
        $data['class_id']=$class_id;
        $data['create_at']=time();
        if($model->addBlackboardData($data)){
            $this->showjson(1,'success',array());
        }else{ 
            $this->showjson(-4,'信息发布失败',array());
        }
    }
}