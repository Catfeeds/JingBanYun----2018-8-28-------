<?php
namespace Api\Controller;
use Think\Controller;

class ClassController extends Controller
{ 
    public $model='';
    public $page_size=20;
    public $firstRow=0;
    public $listRow=0;
            
    public function __construct() {
        parent::__construct(); 
        $this->model=D('Biz_class');
        header("Content-type: text/html; charset=utf-8");
    }
    
    public function test(){
        $model=D('Biz_resource_contact');
        $result=$model->getContactResource(155);
        print_r($result);
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
    
    
    //老师的班级列表
    public function teachClassList(){
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
        $class_model=D('Biz_class');
        $class_result=$class_model->getTeachClassData($id);
        if(empty($class_result)){
            $this->showjson(-3,'您还没有创建班级',array());
        }else{ 
           /*$class_ids='';
           foreach($class_result as $v){
               $class_ids.=$v['id'].',';
           }
           $class_ids=rtrim($class_ids,',');  
           $student_class_model=D('Biz_class_student'); 
           
           $class_result=$student_class_model->getClassData($class_ids);   
           if(empty($class_result)){
               $this->showjson(-4,'success',array());
           }*/
               //这里去得到年级和学校和班级信息,最后连在一起
               $next_last_ids='';
                foreach($class_result as $v){
                    $next_last_ids.=$v['id'].',';
                } 
                $next_last_ids=rtrim($next_last_ids,',');
                $class_result=$class_model->getClassDataInfo($next_last_ids);   
                 
                $school_model=D('Dict_schoollist'); 
                $grade_model=D('Dict_grade');
                foreach($class_result as $key=>$val){
                    $school_result=$school_model->getSchoolInfo($val['school_id']);
                    $grade_result=$grade_model->getGradeInfo($val['grade_id']);  
                    if(!empty($grade_result) && !empty($school_result)){  
                        $class_result[$key]['school_name']=$school_result['school_name'];
                        $class_result[$key]['grade']=$grade_result['grade'];
                    }else{
                        unset($class_result[$key]);
                    } 
                }  
                sort($class_result);

                if (preg_match('/Resources/', $teach_info['avatar'])){  
                    $teach_data['avatar'] = C('oss_path').$teach_info['avatar'];
                } else {
                    $teach_data['avatar']='http://'.C('REMOTE_ADDR').'/Uploads/Avatars/'.$id.'_t.jpg'; 
                }


                $teach_data['name']=$teach_info['name'];
                $big_data['teach_info']=$teach_data;
                $big_data['class_data']=$class_result; 
                $this->showjson(1,'success',$big_data);
           
        } 
    }
    
    
    //查看某个班级的学生
    public function classStudent(){   
        //班级ID
        $id=intval(I('id'));
        if(!$id){
            $this->showjson(-1,'参数传递不正确',array());
        }
        $class_model=D('Biz_class');
        $class_result=$class_model->getClassInfo($id);  
        if(empty($class_result)){
            $this->showjson(-2,'班级信息不存在',array());      
        }
        $student_class_model=D('Biz_class_student');
        $student_result=$student_class_model->getClassInfo($id);
        $student_ids='';
        if(empty($student_result)){
            $big_data['student']=array();
            $big_data['class']=array();
            $this->showjson(-3,'该班级下还没有学生',$big_data);      //之前为-3
        }else{
            $student_ids='';
            foreach($student_result as $v){
                $student_ids.=$v['student_id'].',';
            }
            $student_ids=rtrim($student_ids,',');   
            $student_model=D('Auth_student');
            $student_info=$student_model->getStudents($student_ids);

            if(empty($student_info)){
                $big_data['student']=array();
                $big_data['class']=array();
                $this->showjson(-4,'该班级下还没有学生',$big_data);  //之前为-4
            }else{
                //拼接学生的头像
                foreach($student_info as $key=>$val){
                    
                    if (preg_match('/Resources/', $val['avatar'])){  
                        $student_info[$key]['avatar'] = C('oss_path').$val['avatar'];
                    } else {
                        $student_info[$key]['avatar']='http://'.C('REMOTE_ADDR').'/Uploads/StudentAvatars/'.$val['id'].'.jpg';
                    }    
                } 
                $big_data['student']=$student_info;
                $big_data['class']=$class_result; 
                $this->showjson(1,'success',$big_data);
            }
        }
    }
    
    
   //学生的班级列表,我的班级
   public function studentClassList(){
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
        $class_student_model=D('Biz_class_student');
        $class_student=$class_student_model->getStudentClass($id);
        if(empty($class_student)){
            $this->showjson(-3,'您还没有加入任何班级',array());      //之前为-3
        }else{  
            $class_model=D('Biz_class');
            $class_ids='';
            foreach($class_student as $v){
                $class_ids.=$v['class_id'].',';
            }
            $class_ids=rtrim($class_ids, ',');
            $class_result=$class_model->getClassDataInfo($class_ids); 
            if(empty($class_result)){
                $this->showjson(-4,'班级信息不存在',array());  //之前为-4
                die;
            } 
            $school_model=D('Dict_schoollist'); 
            $grade_model=D('Dict_grade');  
            
            foreach($class_result as $key=>$val){  
                $school_result=$school_model->getSchoolInfo($val['school_id']);
                $grade_result=$grade_model->getGradeInfo($val['grade_id']); 
                 if(!empty($grade_result) && !empty($school_result)){
                    $class_result[$key]['school_name']=$school_result['school_name'];
                    $class_result[$key]['grade']=empty($school_result['grade']) ? '':$school_result['grade'];
                 }else{
                     unset($class_result[$key]);
                 } 
            }
            sort($class_result);
            $this->showjson(1,'success',$class_result);
        } 
   }
   
   
   //家长督学
   public function parentClassList(){
       //家长ID
        $id=intval(I('id'));
        if(!$id){
            $this->showjson(-1,'参数传递不正确',array());
        }
        $student_model=D('Auth_student');
        $student_info=$student_model->getParentStudent($id);
        if(empty($student_info)){
            $this->showjson(-2,'您还没有添加任何小孩',array());       //之前为-2
        }
        $student_ids='';
        foreach($student_info as $v){
            $student_ids.=$v['id'].',';
        }
        $student_ids=rtrim($student_ids,',');  
        $student_model=D('Biz_class_student');
        $student_class=$student_model->getStudentClassData($student_ids);         
        if(empty($student_class)){
            $this->showjson(-3,'您的小孩还没有加入任何班级',array());      //之前为-3
        }else{
            //这里去得到年级,学校
            
            $student_model=D('Auth_teacher');//getTeachInfo
            $school_model=D('Dict_schoollist'); 
            $grade_model=D('Dict_grade');  
            
            foreach($student_class as $key=>&$val){  
                if($val['class_teacher_id']==null){
                    $student_class[$key]['teach_name']=null;
                }else{
                    $teach=$student_model->getTeachInfo($val['class_teacher_id']);
                    $student_class[$key]['teach_name']=$teach['name'];
                }
               if($val['school_id']==null){
                   $student_class[$key]['school_name']=null;
               }else{
                   $school_result=$school_model->getSchoolInfo($val['school_id']);
                   $student_class[$key]['school_name']=$school_result['school_name'];
               }
               if($val['grade_id']==null){
                   $student_class[$key]['grade']=null;
               }else{
                   $grade_result=$grade_model->getGradeInfo($val['grade_id']);
                   $student_class[$key]['grade']=empty($grade_result['grade']) ? '':$grade_result['grade'];
               } 
                
                
            }  
            $this->showjson(1,'success',$student_class); 
        } 
   }
   
   //添加家长信息到学生表中
   public function updateStudentParent(){
       //Auth_parent
       $student_id=intval(I('student_id'));
       $parent_id=intval(I('parent_id'));
       if(!$student_id || !$parent_id){
           $this->showjson(-1,'参数传递不正确',array());
       }
       $student_model=D('Auth_student');
       $student_info=$student_model->getStudentInfo($student_id); 
       if(empty($student_info)){
            $this->showjson(-2,'学生信息不存在',array()); 
       }
       $parent_model=D('Auth_parent');
       $parent_info=$parent_model->getParentInfo($parent_id);
       if(empty($parent_info)){
           $this->showjson(-3,'家长信息不存在',array()); 
       }
       if($student_model->updateStudentParentInfo($student_id,$parent_id)){
           $this->showjson(1,'success',array()); 
       }else{
           $this->showjson(-4,'添加失败',array());
       } 
       
   }
   
   //班级详情添加学生(把该班级和学生进行关联)
   public function addClassStudent(){
       $class_id=intval(I('class_id'));
       $student_id=intval(I('student_id'));
        if(!$class_id || !$student_id){
            $this->showjson(-1,'参数传递不正确',array());
        }
        $class_model=D('Biz_class');
        $class_result=$class_model->getClassInfo($class_id);  
        if(empty($class_result)){
            $this->showjson(-2,'班级信息不存在',array());
        }
        $student_model=D('Auth_student');
        $student_info=$student_model->getStudentInfo($student_id);
        if(empty($student_info)){
            $this->showjson(-3,'学生信息不存在',array()); 
        }
        $parent_model=D('Biz_class_student');
        if($parent_model->addClassStudent($class_id,$student_id)){
            $this->showjson(1,'success',array());
        }else{
            $this->showjson(-4,'添加失败',array()); 
        }
   }
   
   
   //班级课表
   public function classTimeatable(){  
       //班级id
       $id=intval(I('id'));
        if(!$id){
            $this->showjson(-1,'failed',array());
        }  
        $class_model=D('biz_class');
        $timetable=$class_model->getClassTimetable($id);    //var_dump($timetable);
        if(!empty($timetable)){
             $reg='/width="(\d){2,3}"/';
             $reg2='/<tr><td class="time"><\/td><td class="lunch" colspan="7">(.){6,12}<\/td><\/tr>/';
             $timetable['content']=preg_replace($reg,'', $timetable['content']);
             $timetable['content']=preg_replace($reg2,'', $timetable['content']); 
        }
        $this->assign('timetable',$timetable);
        $this->display();
   }
   
}