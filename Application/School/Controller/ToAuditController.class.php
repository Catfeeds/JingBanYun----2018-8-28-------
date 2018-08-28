<?php
namespace School\Controller;
use Think\Controller; 
use Common\Common\CSV;
  
define('ENABLE_TEACHER_STATUS',1);
define('DISABLE_TEACHER_STATUS',0);
define('WAIT_CHECK_TEACHER_STATUS',0);
define('WAIT_CHECK_STUDENT_STATUS',0);
define('TEACHER_AUTH_ALLOW', 1);
define('TEACHER_AUTH_DENY', 2); 
define('SCHOOL_CLASS',1);
define('SCHOOL_ID',session('school.school_id')); 
define('TEACHER_SECOND_INFO_EXISTS','教师年级学科已存在！');
define('TEACHER_TEACHS_EXISTS','教师已任教该班级该学科！');


class ToAuditController extends Controller
{  
    public $model;
    public $page_size=20; 
            
    public function __construct() {
        parent::__construct();
        $this->model=D('Auth_teacher');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }
             
    
    /*
     * 待审核教师列表    
     */
    public function teacherList(){  
        if (!session('?school')) redirect(U('Login/login')); 
        A('School/SchoolAdmin')->check_permissions();
        
        $filter['teacher_name']=trim(getParameter('teacher_name','str',false));       
        $filter['telephone']=trim(getParameter('telephone','str',false));
        $filter['course']=getParameter('course','int',false);  
        //$filter['apply_school_status']=$_GET['apply_status'];
        $filter['account_status']=$_GET['status'];                  
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition['auth_teacher.flag']=array('neq',-1);
        $condition['auth_teacher.school_id']=SCHOOL_ID;
        $class_status=SCHOOL_CLASS;
        if(!empty($filter['teacher_name']))   $condition['auth_teacher.name']=array('like', '%' . $filter['teacher_name']. '%');  
        if(!empty($filter['telephone']))   $condition['auth_teacher.telephone']=array('like', '%' . $filter['telephone']. '%');
        if(!empty($filter['course']))   $where['dict_course.id']=$filter['course']; 
        if($filter['account_status']!='')   $condition['auth_teacher.flag']=intval($filter['account_status']);
        $condition['auth_teacher.apply_school_status']= WAIT_CHECK_TEACHER_STATUS;
        // if($filter['apply_school_status']!='')   $condition['auth_teacher.apply_school_status']=intval($filter['apply_school_status']);
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        }
        $result=$this->model->getTeacherData($condition,$order,$class_status,$where);   
        $course_model=D('Dict_course'); 
        $course_result=$course_model->getCourseList();    
        
        $this->assign('condition_str',$condition_string);
        $this->assign('teacher_name',$filter['teacher_name']);
        $this->assign('telephone',$filter['telephone']);
        $this->assign('course',$filter['course']);
        $this->assign('apply_status',$filter['apply_school_status']);
        $this->assign('account_status',$filter['account_status']);
        $this->assign('order',$order);
        
        $this->assign('course_list',$course_result);
        $this->assign('list',$result['data']);      
        $this->assign('page',$result['page']);
        $this->display();
    }
 
    /*
     * 通过或拒绝学校审核
     */
    public function updateApplyStatus(){
        if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);
        A('School/SchoolAdmin')->check_permissions(true);
         
        $class_model=D('Biz_class');
        $id=getParameter('id','int');
        $apply_status=getParameter('status','int');
        
        $result=$this->model->getTeachInfo($id);

        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{
            if($result['school_id']!=SCHOOL_ID){
                $this->showjson(402,COMMON_FAILED_MESSAGE);
            }
            if($apply_status!=APPLY_SCHOOL_ALLOW && $apply_status!=APPLY_SCHOOL_DENY){
                $this->showjson(403,COMMON_FAILED_MESSAGE);
            } 
            $this->model->startTrans();
            if(!$this->model->updateApplyStatusManagement($id,$apply_status,SCHOOL_ID)){
                $this->model->rollback();
                $this->showjson(404,'操作失败!');
            }  
            if($apply_status==APPLY_SCHOOL_DENY){
                //删除该教师在校建班的课表的信息
                if(!$class_model->deleteClassTimetable($id)){   
                    $this->model->rollback();
                    $this->showjson(404,'操作失败!');
                }
                $class_teacher_result=$class_model->getClassListByTeacher($id);     
                
                $record_data['teacher_id']=$id;
                foreach($class_teacher_result as $val){
                    //加入教师班级记录表
                    if($val['class_status']==PERSONAL_CLASS){
                        continue;
                    }
                    $record_data['class_id']=$val['id'];
                    $record_data['course_id']=$val['course_id'];
                    $record_data['is_handler']=$val['is_handler'];
                    $record_data['create_at']=time();
                    if(!$class_model->addClassTeacherRecord($record_data)){
                        $this->model->rollback();
                        $this->showjson(405,'操作失败!');
                    }
                }
                //删除在校建班的任教信息
                if(!$class_model->deleteClassTeacher($id,true)){
                    $this->model->rollback();
                    $this->showjson(405,'操作失败!'); 
                } 
            }

            if ( $apply_status == 1) {

                $parameters = array(
                    'msg' => array(
                        $result['school_name'],
                    ),
                    'url' => array( 'type' => 0 )
                );

                A('Home/Message')->addPushUserMessage('TEACHER_Add_SCHOOL', 2, $id , $parameters);


            } else {
                $parameters = array(
                    'msg' => array(
                        C('SCHOOL_ROOT'),
                        $result['school_name'],
                    ),
                    'url' => array( 'type' => 0 )
                );

                A('Home/Message')->addPushUserMessage('TEACHER_REMOVE_SCHOOL', 2, $id , $parameters);
            }


            $this->model->commit();
            $this->showjson(200);
        }
    }

    public function studentList(){
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions();
        $class_model=D('Biz_class');
        $grade_model=D('Dict_grade');
        $auth_student=D('Auth_student');
        $filter['student_name']=getParameter('student_name','str',false);
        $filter['parent_tel']=getParameter('telephone','str',false);
        $filter['grade']=getParameter('grade','int',false);
        $filter['class']=getParameter('class','str',false);
        //$filter['apply_school_status']=$_GET['apply_status'];
        $filter['account_status']=$_GET['status'];
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];
        $order?$order='asc':$order='desc';

        $condition['auth_student.flag']=array('neq',-1);
        $condition['auth_student.school_id']=SCHOOL_ID;
        if(!empty($filter['student_name']))   $condition['auth_student.student_name']=array('like', '%' . trim ($filter['student_name']). '%');
        if(!empty($filter['parent_tel']))   $condition['auth_student.parent_tel']=array('like', '%' . trim($filter['parent_tel']). '%');
        $condition['auth_student.apply_school_status']= WAIT_CHECK_STUDENT_STATUS;
        //if($filter['apply_school_status']!='')   $condition['auth_student.apply_school_status']=intval($filter['apply_school_status']);
        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade'];
        if(!empty($filter['class']))   $condition['biz_class.name']=$filter['class'];
        if($filter['account_status']!='')   $condition['auth_student.flag']=intval($filter['account_status']);
        if(!empty($filter['grade'])){
            $class_result=$class_model->getClassDataBySchool(SCHOOL_ID,$filter['grade'],'',true);
        }

        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        }


        $grade_result=$grade_model->getGradeList(true);



        $result=$auth_student->getStudentData($condition,$order,SCHOOL_CLASS);
//var_dump($result);die;

        $this->assign('condition_str',$condition_string);
        $this->assign('student_name',$filter['student_name']);
        $this->assign('telephone',$filter['parent_tel']);
        $this->assign('grade',$filter['grade']);
        $this->assign('class',$filter['class']);
        $this->assign('apply_status',$filter['apply_school_status']);
        $this->assign('account_status',$filter['account_status']);
        $this->assign('order',$order);

        $this->assign('class_list',$class_result);
        $this->assign('grade_list',$grade_result);
        $this->assign('list',$result['data']);
        $this->assign('page',$result['page']);
        $this->display();
    }

}