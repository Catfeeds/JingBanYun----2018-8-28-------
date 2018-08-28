<?php
namespace School\Controller;
use Think\Controller;  
define('CLASS_STUDETN_STATUS',2);
define('SCHOOL_ID',session('school.school_id'));

class CommonController extends Controller
{   

    public $model;
    public $page_size=20; 
                
    public function __construct() {
        parent::__construct();
        $this->model=D('Auth_school_admin');
    }
    
    
   
    /*
     * 把某个学生从某个班级移除
     */
    public function classRemoveStudent(){ 
        if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);

        A('School/SchoolAdmin')->check_permissions(true);

        $model=M('auth_student');
        $class_model=D('Biz_class');
        $class_id=getParameter('class_id','int');
        $student_id=getParameter('student_id','int');
        $condition['biz_class.id']=$class_id;
        $condition['biz_class_student.student_id']=$student_id;
        $result=$class_model->getClassStudentDataAll($condition);

        $student_info = D('Auth_student')->getStudentInfo( $student_id );
        $class_result_info=D('Biz_class')->getClassAndGradeInfo($class_id);

        if(empty($result)){
            $this->showjson(401,COMMON_FAILED_MESSAGE);
        }else{
            if($result[0]['school_id']!=SCHOOL_ID){
                $this->showjson(402,COMMON_FAILED_MESSAGE);
            }
        }  
        $this->model->startTrans();
        $class_con['biz_class_student.student_id']=$student_id; 
        $class_con['biz_class.class_status']=SCHOOL_CLASS;
        $class_con['biz_class.id']=$class_id;
        $class_result=$class_model->getClassStudentDataAll($class_con);
        if(!empty($class_result)){
            $student_data['student_id']=$student_id;
            $student_data['class_id']=$class_id;
            $student_data['status']=$class_result[0]['class_student_status'];
            $student_data['create_at']=time();
            $student_data['joinmode']=$class_result[0]['joinmode'];
            if(!$class_model->addClassStudentRecord($student_data)){
                $this->model->rollback();
                $this->showjson(404,'操作失败');
            }
        }
        if(!$class_model->removeClassStudentById($class_id,$student_id)){
            $this->model->rollback();
            $this->showjson(403,COMMON_FAILED_MESSAGE);
        }
        /*if(!$class_model->updateClassStudentCount($class_id)){
            $this->model->rollback();
            $this->showjson(404,COMMON_FAILED_MESSAGE);
        }*/

        $parameters = array(
            'msg' => array(
                C('SCHOOL_ROOT'),
                $student_info['student_name'],
                $class_result_info['grade'],
                $class_result_info['name'],
                C('SCHOOL_ROOT'),
                $class_result_info['grade'],
                $class_result_info['name'],
            ),
            'url' => array( 'type' => 1,'data'=>array($student_info['id'],$student_info['student_name']))
        );

        A('Home/Message')->addPushUserMessage('DELSTUDENTCLASS', 4,$student_info['parent_id'] , $parameters);

        //发送给学生
        $parameters['url'] = array( 'type' => 0,);
        unset($parameters['msg'][1]);
        A('Home/Message')->addPushUserMessage('SCHOOL_CLASS_REMOVE_TEACHER', 3,$student_info['id'] , $parameters);

        $this->model->commit();
        $this->showjson(200);
    }
     
    
    /*
     * 把某个教师从班级移除
     */
    public function classRemoveTeacher(){
        if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);
        A('School/SchoolAdmin')->check_permissions(true);
        
        $teacher_model=D('Auth_teacher');
        $class_model=D('Biz_class');
        $teacher_id=getParameter('teacher_id','int');
        $class_id=getParameter('class_id','int');
        $teacher_result=$teacher_model->getTeachInfo($teacher_id);
        $class_result=$class_model->getClassSchoolData($class_id);
        if(empty($teacher_result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{
            if($teacher_result['school_id']!=SCHOOL_ID){  
                $this->showjson(406,ID_NOT_EXISTS_MESSAGE);
            }
        }
        if(empty($class_result)){
            $this->showjson(402,ID_NOT_EXISTS_MESSAGE);
        }else{
            if($class_result['school_id']!=SCHOOL_ID){
                $this->showjson(406,ID_NOT_EXISTS_MESSAGE);
            }
        }
        if($class_result==PERSONAL_CLASS){
            $this->showjson(403,PERSONAL_CLASS_DENY_REMOVE_TEACHER);
        }
        $this->model->startTrans();
        //加入教师班级记录表
        $class_teacher_result=$class_model->getClassListByTeacher($teacher_id,$class_id);
        if(!empty($class_teacher_result)){
            $record_data['teacher_id']=$teacher_id; 
            $record_data['class_id']=$class_id;
            $record_data['course_id']=$class_teacher_result[0]['course_id'];
            $record_data['is_handler']=$class_teacher_result[0]['is_handler'];
            $record_data['create_at']=time();
            if(!$class_model->addClassTeacherRecord($record_data)){
                $this->model->rollback();
                $this->showjson(404,'操作失败!');
            }
        }
        if(!$class_model->leaveClass($class_id,PERSONAL_CLASS,$teacher_id,$error_info)){
            $this->model->rollback();
            $this->showjson(404,COMMON_FAILED_MESSAGE);
        }
        if(!$class_model->deleteClassTimetable($class_id,$teacher_id)){
            $this->model->rollback();
            $this->showjson(405,COMMON_FAILED_MESSAGE);
        }

        $parameters = array(
            'msg' => array(
                C('SCHOOL_ROOT'),
                $class_result['grade'],
                $class_result['class_name'],
                C('SCHOOL_ROOT'),
                $class_result['grade'],
                $class_result['class_name'],
            ),
            'url' => array( 'type' => 0 )
        );

        A('Home/Message')->addPushUserMessage('CLASS_REMOVE_TEACHER', 2, $teacher_id , $parameters);

        $this->model->commit();
        $this->showjson(200);
    }
    
    
    /*
     * 通过某个年级获得该年级下所有班级
     */
    public function getClassByGrade(){
        if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $class_model=D('Biz_class');
        $grade_id=getParameter('grade_id','int');
        $result=$class_model->getClassDataBySchool(SCHOOL_ID,$grade_id,1,true);    
        $this->showjson(200,'',$result);
    }

}
