<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Common\CSV;
   
define('ENABLE_TEACHER_STATUS',1);
define('DISABLE_TEACHER_STATUS',0);
define('WAIT_TEACHER_STATUS',0);
define('TEACHER_AUTH_ALLOW', 1);
define('TEACHER_AUTH_DENY', 2); 
define('TEACHER_SECOND_INFO_EXISTS','教师年级学科已存在！');
define('TEACHER_TEACHS_EXISTS','教师已任教该班级该学科！'); 
define('WAIT_SCHOOL_PASS_STATUS',0);
define('VIP_CONFIG_MAX_NUM',3);
define('PERSONAL_VIP',4);
define('COMMON_PRIVILEGE',2);

class TeacherController extends Controller
{ 

    public $model;
    public $page_size=20; 
            
    public function __construct() {  
        parent::__construct();       
        $this->model=D('Auth_teacher');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }

    //设定vip

    /*
     * 给教师开通VIp
     */
    function giveTeacherVip(){
        
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);

        $id=getParameter('id','int');
        $vip_type=getParameter('vip_type','int');
        $vip_use_type=getParameter('use_type','int');
        $start_time=getParameter('start_time','str');
        $end_time=getParameter('end_time','str');
        $result=$this->model->getTeacherSimpleData($id);
        if(empty($result)){
            $this->showjson('401',ID_NOT_EXISTS_MESSAGE);
        }else{
            $start_time=strtotime($start_time);
            $end_time = date('y-m-d 23:59:00',strtotime($end_time));
            $end_time= strtotime($end_time);
            /*if($start_time<time()){
                $this->showjson('402',COMMON_FAILED_MESSAGE);
            }*/
            if($start_time>$end_time){
                $this->showjson('403','开始时间不能晚于结束时间!');
            }
            //试用和试用类型
            $trial_use=1;
            $use=2;
            if($vip_use_type!=$trial_use && $vip_use_type!=$use){
                $this->showjson('404','未知的权限类型!');
            }
            if($vip_type==2){
                $data['auth_id']=PERSONAL_VIP;
                $data['user_id']=$id;
                $data['role_id']=ROLE_TEACHER;
                $data['auth_start_time']=$start_time;
                $data['auth_end_time']=$end_time;
                $data['timetype']=2;
            }elseif($vip_type==1){
                $data['auth_id']=COMMON_PRIVILEGE;
                $data['user_id']=$id;
                $data['role_id']=ROLE_TEACHER;
                $data['auth_start_time']='';
                $data['auth_end_time']='';
                $data['timetype']=1;
            }else{
                $this->showjson('405',COMMON_FAILED_MESSAGE);
            }
            $auth_data=$this->model->getTeacherPrivilegeInfo($id);
            if(!empty($auth_data)){
                if(!$this->model->updateTeacherPrivilege($data,$id)){
                    $this->showjson('406',COMMON_FAILED_MESSAGE);
                }
            }else{
                if(!$this->model->addTeacherPrivilege($data)){
                    $this->showjson('407',COMMON_FAILED_MESSAGE);
                }
            }
            $this->showjson(200);
        }
    }
    
    /*
     * 教师列表
     */
    public function teacherList(){ 
        if (!session('?admin')) redirect(U('Login/login'));
        
        $grade_model=D('Dict_grade');
        $course_model=D('Dict_course');
        $filter['teacher_name']=getParameter('teacher_name','str',false);
        $filter['school_name']=getParameter('school_name','str',false);
        $filter['school_code']=$_GET['school_code'];
        $filter['telephone']=getParameter('telephone','str',false); 
        $filter['course']=getParameter('course','int',false);
        $filter['grade']=getParameter('grade','int',false);
        $filter['account_status']=$_GET['status'];                 
        $filter['auth_status']=$_GET['auth_status'];
        $filter['apply_school_status']=$_GET['apply_school_status'];
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition['auth_teacher.flag']=array('neq','-1'); 
        if(!empty($filter['teacher_name']))   $condition['auth_teacher.name']=array('like', '%' . $filter['teacher_name']. '%'); 
        if(!empty($filter['school_name']))   $condition['dict_schoollist.school_name']=array('like', '%' . $filter['school_name']. '%');
        if(!empty($filter['school_code']))   $condition['dict_schoollist.school_code']=array('like', '%' . $filter['school_code']. '%');
        if(!empty($filter['telephone']))   $condition['auth_teacher.telephone']=array('like', '%' . $filter['telephone']. '%');
        if(!empty($filter['course']))   $where['dict_course.id']=$filter['course'];
        if(!empty($filter['grade']))   $where['dict_grade.id']=$filter['grade'];
        if($filter['account_status']!='')   $condition['auth_teacher.flag']=intval($filter['account_status']);
        if($filter['auth_status']!='')   $condition['auth_teacher.auth_status']=intval($filter['auth_status']);
        if($filter['apply_school_status']!='')   $condition['auth_teacher.apply_school_status']=intval($filter['apply_school_status']);    
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        }
        
        $result=$this->model->getTeacherData($condition,$order,'',$where);

        $course_result=$course_model->getCourseList(); 
        $grade_result=$grade_model->getGradeList(true); 
        $school_category=C('SCHOOL_CATEGORY');        
        
        $this->assign('condition_str',$condition_string);
        $this->assign('teacher_name',$filter['teacher_name']);
        $this->assign('school_name',$filter['school_name']);
        $this->assign('school_code',$filter['school_code']);
        $this->assign('telephone',$filter['telephone']);
        $this->assign('course',$filter['course']);
        $this->assign('grade',$filter['grade']);
        $this->assign('account_status',$filter['account_status']);
        $this->assign('auth_status',$filter['auth_status']);
        $this->assign('apply_school_status',$filter['apply_school_status']);
        $this->assign('order',$order);
        
        $this->assign('grade_list',$grade_result);
        $this->assign('course_list',$course_result);   
        $this->assign('list',$result['data']);
        $this->assign('page',$result['page']); 
        $this->assign('school_category',$school_category);
        $this->assign('other_school_id',OTHER_SCHOOL_ID);
        $this->display();
    }
    
    
    /*
     * 通过或拒绝学校审核
     */
    public function updateApplyStatus(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $class_model=D('Biz_class');
        $id=getParameter('id','int');
        $apply_status=getParameter('status','int');
        
        $result=$this->model->getTeachInfo($id);
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{ 
            if($apply_status!=APPLY_SCHOOL_ALLOW && $apply_status!=APPLY_SCHOOL_DENY){
                $this->showjson(402,COMMON_FAILED_MESSAGE);
            } 
            $this->model->startTrans();
            if(!$this->model->updateApplyStatusManagement($id,$apply_status)){
                $this->model->rollback();
                $this->showjson(403,'操作失败!');
            }
            if($apply_status==APPLY_SCHOOL_DENY){
                //删除该教师在校建班的课表的信息
                if(!$class_model->deleteClassTimetable(0,$id)){   
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
                    $this->showjson(406,'操作失败!'); 
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
                        C('ADMIN_ROOT'),
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
    
    
    /*
     * 通过或拒绝教师自身的审核
     */
    function updateTeacherAuth(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $id=getParameter('id','int');
        $apply_status=getParameter('status','int');
        
        $result=$this->model->getTeachInfo($id);
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{
             if($apply_status!=TEACHER_AUTH_ALLOW && $apply_status!=TEACHER_AUTH_DENY){
                 $this->showjson(402,COMMON_FAILED_MESSAGE);
             }
             $status=$this->model->updateAuthStatusManagement($id,$apply_status);
             if($status){
                 $this->showjson(200);
             }else{
                 $this->showjson(403,COMMON_FAILED_MESSAGE);
             }
        }
    }
    
    
    /*
     * 启用或禁用教师状态
     */
    public function updateTeacherStatus(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $id=getParameter('id','int');
        $result=$this->model->getTeachInfo($id);
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{
            if($result['flag']){
                $status=$this->model->updateEnableStatus($id,DISABLE_TEACHER_STATUS);
            }else{
                $status=$this->model->updateEnableStatus($id,ENABLE_TEACHER_STATUS);
            }
            if($status){
                $this->showjson(200);
            }else{
                $this->showjson(402,COMMON_FAILED_MESSAGE);
            }
        }
    }
    
    
    /*
     * 删除教师(修改状态)
     */
    public function deleteTeacher(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $id=getParameter('id','int');
        $result=$this->model->getTeachInfo($id);
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{ 
            $data['flag']=-1;
            if(!$this->model->updateInfoById($data,$id)){
                $this->showjson(402,COMMON_FAILED_MESSAGE);
            }else{
                $this->showjson(200);
            }
               
        }
    }
    
    
    /*
     * 创建教师
     */
    public function createTeacherAccount(){
        if (!session('?admin')) redirect(U('Login/login'));  

        if(!empty($_POST)){    
            $vip_config=C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');
            $school_model=D('Dict_schoollist');
            $show_next_page_flag=getParameter('next_flag', 'int');  
            $school_id=getParameter('school', 'int');
            $teacher_name=getParameter('name', 'str');
            $telephone=getParameter('telephone', 'str');
            $password=getParameter('password', 'str');
            $sex=getParameter('sex', 'str');
            $grade_course_ids=getParameter('grade_course_ids', 'str');
            $email=getParameter('email', 'str',false); 
            $brief_intro=getParameter('brief_info','str',false);
            $grade_course_arr=explode(',',$grade_course_ids);
            $grade_course_arr=array_unique($grade_course_arr);  
            if(empty($grade_course_arr)){
                $this->showMessage(500,'任教年级学科参数有误!');
            }
            $tel_reg="/^1[34578]{1}\d{9}$/";
            $email_reg="/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/";
            if(!preg_match($tel_reg,$telephone)){
                $this->showMessage(500,'手机格式有误!');
            }
            if($email!=''){
                if(!preg_match($email_reg,$email)){
                    $this->showMessage(500,'邮箱格式有误!');
                }
            }
            $result=$this->model->getTeacherByTel($telephone);
            if(!empty($result)){
                $this->showMessage(500,'该手机号已经存在!');
            }
            if($sex!=SEX_MAN && $sex!=SEX_WOMAN){
                $this->showMessage(500,'性别参数有误');
            }
            $school_result=$school_model->getSchoolInfo($school_id);    
            if(empty($school_result)){
                $this->showMessage(500,'学校信息有误');
            }
            $teacher_data['name']=$teacher_name;
            $teacher_data['telephone']=$telephone;
            $teacher_data['password']=sha1($password);
            $teacher_data['school_id']=$school_id;
            $teacher_data['email']=$email;
            $teacher_data['sex']=$sex;
            $teacher_data['brief_intro']=$brief_intro;
            $teacher_data['create_at']=time();
            //得到所有年级和所有学科
            $grade_model=D('Dict_grade');
            $grade_result=array_column($grade_model->getGradeList(true),'id'); 
            $course_model=D('Dict_course');
            $course_result=array_column($course_model->getCourseList(),'id'); 
            
            $this->model->startTrans();
            if(!($insert_id=$this->model->addTeacherData($teacher_data))){
                $this->model->rollback();
                $this->showMessage(500,'教师信息入库失败!');
            }
            if($vip_config && $vip_config<=VIP_CONFIG_MAX_NUM){ 
                give_new_vip_operation(ROLE_TEACHER,$vip_config,$insert_id,$school_id);
            }
            foreach($grade_course_arr as $val){
                $temp_arr=explode('_',$val);
                @$grade=$temp_arr[0];
                @$course=$temp_arr[1];
                if(!in_array($grade,$grade_result)){
                    $this->model->rollback();
                    $this->showMessage(500,'年级信息不存在!');
                }
                if(!in_array($course,$course_result)){
                    $this->model->rollback();
                    $this->showMessage(500,'学科信息不存在!');
                }
                $teacher_contact['teacher_id']=$insert_id;
                $teacher_contact['grade_id']=$grade;
                $teacher_contact['course_id']=$course; 
                if(!$this->model->addTeacherSecond($teacher_contact)){
                    $this->model->rollback();
                    $this->showMessage(500,'教师学科信息入库失败!');
                }
            }
            $this->model->commit();  
            if($show_next_page_flag){
                $this->showMessage(200,'success',array('redirectUrl'=>U('Teacher/teacherChooseClass')."&id=$insert_id"));
            }else{
                $this->showMessage(200,'success',array('redirectUrl'=>U('Teacher/teacherList')));
            }
            
        }else{ 
            $grade_model=D('Dict_grade');
            $course_model=D('Dict_course');  
            $school_model=D('Dict_schoollist');
            $province_result=$school_model->getProvince();
            $course_result=$course_model->getCourseList(); 
            $grade_result=$grade_model->getGradeList(true);
            
            $this->assign('course_list',$course_result);
            $this->assign('grade_list',$grade_result); 
            $this->assign('province_list',$province_result);         
            $this->display();
        }
    }

    /**
     *描述：验证手机号是否重复
     */
    public function checkPhone(){
        $telephone = getParameter('telephone','str');
        $result=$this->model->getTeacherByTel($telephone);
        if(!empty($result)){
            $this->showjson('400','该手机号已经存在!');
        }
    }
    
    /*
     * 教师选择班级
     */
    public function teacherChooseClass(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        if(!empty($_POST)){         
            $class_model=D('Biz_class');
            $grade_model=D('Dict_grade');
            $course_model=D('Dict_course');
            $teacher_id=getParameter('id','int'); 
            $data['teacher_id']=$teacher_id;
            $result=$this->model->getTeacherClassTeachesInfo($data);    
            //判断教师如果有教师任教班级信息就跳转到列表页
            if(!empty($result)){
                $this->redirect(U('Teacher/teacherList'));
            }
            
            $class_course_arr=$_POST['class_course'];      
            if(!empty($class_course_arr)){  
                //操作教师任教班级和学科
                $this->model->startTrans();
                foreach($class_course_arr as $val){
                    $class_course=explode('_',$val);        
                    $data['class_id']=$class_course[0];
                    $data['course_id']=$class_course[1];
                    $data['create_at']=time();
                    //判断班级id和学科Id是否存在
                    $class_result=$class_model->getClassInfo($data['class_id']);    
                    $course_result=$course_model->getCourseInfo($data['course_id']);
                    if(empty($class_result) || empty($course_result)){
                        $this->error('参数有误');
                    }
                    if(!$this->model->teacherBindClass($data)){
                        $this->model->rollback();
                        $this->error('入库失败');
                    }
                }
                $this->model->commit();
            } 
             $this->redirect(U('Teacher/teacherList'));
        }else{
            $grade_model=D('Dict_grade');
            $teacher_id=getParameter('id','int');
            $result=$this->model->getTeachInfo($teacher_id);       
            if(empty($result)){
                $this->error('参数有误!');
            } 
            $grade_result=$grade_model->getGradeList(true);    
            $teachs_course=$this->model->getTeacherAllCourse($teacher_id);  
            $this->assign('grade_list',$grade_result);
            $this->assign('course_list',$teachs_course);        
            $this->assign('data',$result);          
            $this->display('teacherAddClass'); 
        }
    }
    
    
    /*
     * 删除某个教师的某个任教的年级和学科
     */
    public function detelteTeacherGradeInfo(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $class_model=D('Biz_class');
        $teacher_id=getParameter('teacher_id','int'); 
        $id=getParameter('id','int'); 
        $result=$this->model->getTeachInfo($teacher_id); 
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        } 
        $teacher_second_result=$this->model->getCourseInfoByTeacher($id,$teacher_id);
        if(empty($teacher_second_result)){
            $this->showjson(402,ID_NOT_EXISTS_MESSAGE);
        }
        //判断学科只有1个的话,不允许删除
        $all_course=$this->model->getTeacherAllCourse($teacher_id);
        if(count($all_course)==1){
            $this->showjson(406,'教师下必须保留一项任教年级学科!');
        }
        $course_id=$teacher_second_result['course_id'];
        $this->model->startTrans(); 
        if(!$this->model->deleteTeacherSecond($id,$teacher_id)){
            $this->model->rollback();
            $this->showjson(403,'操作失败!');
        }
        //删除该教师下的所有校建班的任教学科
        if(!$class_model->deleteClassTeacher($teacher_id,true,$course_id)){
            $this->model->rollback();
            $this->showjson(404,'操作失败!');
        } 
        //删除该教师下的校建班任教课表
        if(!$class_model->deleteSchoolClassByCourse($course_id)){
            $this->model->rollback();
            $this->showjson(405,'操作失败!');
        }
        $this->model->commit();
        $this->showjson(200);
    }
    
    
    
    /*
     * 教师详情
     */
    public function teacherDetail(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        $id=getParameter('id','int');
        $result=$this->model->getTeacherSimpleData($id); 
        $this->assign('data',$result);
        $this->display();
    }
    
    
    /*
     * 添加教师的任教年级学科信息
     */
    public function addTeacherGradeInfo(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $grade_model=D('Dict_grade');
        $course_model=D('Dict_course');
        $teacher_id=getParameter('teacher_id','int'); 
        $grade_id=getParameter('grade_id','int'); 
        $course_id=getParameter('course_id','int'); 
        $result=$this->model->getTeachInfo($teacher_id);    
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        } 
        $grade_result=$grade_model->getGradeInfo($grade_id);
        if(empty($grade_result)){
            $this->showjson(402,COMMON_FAILED_MESSAGE);
        }
        $course_result=$course_model->getCourseInfo($course_id);
        if(empty($course_result)){
            $this->showjson(403,COMMON_FAILED_MESSAGE);
        }
        $sql_con['auth_teacher_second.teacher_id']=$teacher_id;
        $sql_con['auth_teacher_second.course_id']=$course_id;
        $sql_con['auth_teacher_second.grade_id']=$grade_id;        
        $teacher_second_info=$this->model->getTeacherCourse($sql_con); 
        if(!empty($teacher_second_info)){
            $this->showjson(404,TEACHER_SECOND_INFO_EXISTS);
        }
        $data['teacher_id']=$teacher_id;
        $data['course_id']=$course_id;
        $data['grade_id']=$grade_id; 
        $status=$this->model->addTeacherSecond($data);
        if($status){
            $this->showjson(200);
        }else{
            $this->showjson(405,COMMON_FAILED_MESSAGE);
        }
    }
    
    
    
     
    /*
     * 教师任教的学科iframe
     */
    public function teacherCourseList(){    
        $grade_model=D('Dict_grade');
        $course_model=D('Dict_course');
        
        $id=getParameter('id','int');
        $filter['course']=getParameter('course','int',false);
        $filter['grade']=getParameter('grade','int',false);
        $order=getParameter('order','int',false);
        $order?$order='asc':$order='desc';
         
        $condition['auth_teacher.id']=$id;
        if(!empty($filter['course']))   $condition['dict_course.id']=$filter['course'];
        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade']; 
        $result=$this->model->getTeacherCourse($condition,$order);
        $course_result=$course_model->getCourseList(); 
        $grade_result=$grade_model->getGradeList(true);
        $this->assign('course_list',$course_result);
        $this->assign('grade_list',$grade_result);
        $this->assign('list',$result);
        $this->assign('teacher_id',$id);
        $this->display();
    }
    
    
    /*
     * 教师任教的班级
     */
    public function teacherClassList(){
        $class_model=D('Biz_class');
        $id=getParameter('id','int');
//        $filter['course']=getParameter('course','int',false);
//        $filter['class']=getParameter('class','int',false);
//        $filter['grade']=getParameter('grade','int',false);  
//        $filter['class_flag']=getParameter('class_flag','int',false); 
//        $filter['class_status']=getParameter('class_status','int',false);
//        $filter['class_code']=getParameter('class_code','int',false); 
//        $order=getParameter('order','int',false);
        $order?$order='asc':$order='desc';
        
        $condition['auth_teacher.id']=$id;
        $condition['biz_class.is_delete']=0; 
//        if(!empty($filter['course']))   $condition['dict_course.id']=$filter['course'];
//        if(!empty($filter['class']))   $condition['biz_class.id']=$filter['class']; 
//        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade']; 
//        if(!empty($filter['class_flag']))   $condition['biz_class.flag']=$filter['class_flag'];  
//        if(!empty($filter['class_status']))   $condition['biz_class.class_status']=$filter['class_status'];
//        if(!empty($filter['class_code']))   $condition['biz_class.class_code']=$filter['class_code'];
        $teacher_info=$this->model->getTeachInfo($id);
        if(empty($teacher_info)){
            die('参数有误');
        }
        $result=$this->model->getTeacherClassData($condition,$order);               
        $grade_data=$class_model->getClassDataBySchool($teacher_info['school_id']);
        $teachs_course=$this->model->getTeacherAllCourse($id); 
        $this->assign('grade_list',$grade_data);
        $this->assign('course_list',$teachs_course);    
        $this->assign('data',$teacher_info);
        $this->assign('list',$result);
        $this->display();
    }
    
    
    /*
     * 添加教师任教的班级
     */
    public function addTeacherClassInfo(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $teacher_id=getParameter('teacher_id','int');
        $class_id=getParameter('class_id','int');  
        $course_id=getParameter('course_id','int');
        
        $class_model=D('Biz_class');
        $course_model=D('Dict_course');
        $teacher_result=$this->model->getTeachInfo($teacher_id);
        $class_info = $class_model->getClassAndGradeInfo($class_id);

        if(empty($teacher_result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{
            if($teacher_result['apply_school_status']!=APPLY_SCHOOL_ALLOW){
                $this->showjson(407,'教师加入学校状态还未审核通过');
            }
        } 
        $class_result=$class_model->getClassInfo($class_id);
        if(empty($class_result)){
            $this->showjson(402,COMMON_FAILED_MESSAGE);
        }else{
            if($class_result['class_status']==PERSONAL_CLASS){
                $this->showjson(402,'个人班不允许其他老师加入!');
            }
        } 
        $course_result=$course_model->getCourseInfo($course_id);
        if(empty($course_result)){
            $this->showjson(403,COMMON_FAILED_MESSAGE);
        }
        
        $sql_con['teacher_id']=$teacher_id;
        $sql_con['course_id']=$course_id;
        $sql_con['class_id']=$class_id;   
        $second_con['dict_course.id']=$course_id;
        $second_con['auth_teacher.id']=$teacher_id;
        
        $teacher_teachs_info=$this->model->getTeacherCourse($second_con);       
        if(empty($teacher_teachs_info)){
            $this->showjson(404,COMMON_FAILED_MESSAGE);
        }   
        $teaches=$this->model->getTeacherClassTeachesInfo($sql_con);        
        if(!empty($teaches)){
            $this->showjson(405,TEACHER_TEACHS_EXISTS);
        }
        $data=$sql_con;
        $data['create_at']=time();
        $this->model->startTrans();
        if(!$this->model->teacherBindClass($data)){  
            $this->model->startTrans();
            $this->showjson(406,COMMON_FAILED_MESSAGE); 
        }  
        if(!$class_model->deleteClassTeacherRecord($class_id,$teacher_id,$course_id)){
            $this->model->startTrans();
            $this->showjson(407,COMMON_FAILED_MESSAGE); 
        }

        //消息推送
        $parameters = array(
            'msg' => array(
                C('ADMIN_ROOT'),
                $class_info['grade'],
                $class_info['name'],
                $course_result['course']
            ),
            'url' => array( 'type' => 0, 'data' => array( $class_id ) )
        );

        A('Home/Message')->addPushUserMessage('ADD_SCHOOL_CLASS', 2, $teacher_id , $parameters);

        $this->model->commit();
        $this->showjson(200);
    }
    

    
    /*
     * 修改教师详情
     */
    public function updateTeacher(){ 
        if (!session('?admin')) redirect(U('Login/login'));
        if(!empty($_POST)){         
            $school_model=D('Dict_schoollist');
            $class_model=D('Biz_class');
            
            $teacher_id=getParameter('id','int');
            $teacher_name=getParameter('name', 'str');
            $telephone=getParameter('telephone', 'str');
            $sex=getParameter('sex', 'str');
            $password=getParameter('password', 'str'); 
            $email=getParameter('email', 'str',false); 
            $brief_intro=getParameter('brief_intro','str',false);       
            $account_status=intval($_POST['account_status']);
            $auth_status=intval($_POST['auth_status']); 
            $school_id=getParameter('school_id', 'int');            
            
            $tel_reg="/^1[34578]{1}\d{9}$/";
            $email_reg="/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/";
            if(!preg_match($tel_reg,$telephone)){
                $this->error('手机格式有误!');
            }
            if($email!=''){
                if(!preg_match($email_reg,$email)){
                    $this->error('邮箱格式有误!');
                }
            }
            if($sex!=SEX_MAN && $sex!=SEX_WOMAN){
                $this->error('性别参数有误!');
            }  
            if($auth_status!=ENABLE_TEACHER_STATUS && $auth_status!=DISABLE_TEACHER_STATUS && $auth_status!=WAIT_TEACHER_STATUS){
                $this->error('参数错误!');
            }
            $teacher_result=$this->model->getTeacherSimpleData($teacher_id);
            if(empty($teacher_result)){
                $this->error('参数错误!');
            } 
            $result=$this->model->getTeacherByTel($telephone,$teacher_id);
            if(!empty($result)){
                $this->error('该手机号已经存在!');
            }
            $result=$this->model->getTeachInfo($teacher_id,$password); 
            if(empty($result)){
               $teacher_data['password']=sha1($password);
            }   
            $teacher_data['name']=$teacher_name;
            $teacher_data['telephone']=$telephone; 
            $teacher_data['email']=$email;
            $teacher_data['sex']=$sex;
            $teacher_data['brief_intro']=$brief_intro; 
            $teacher_data['flag']=$account_status;
            $teacher_data['auth_status']=$auth_status; 
            $teacher_data['school_id']=$school_id;
            if($teacher_result['school_id']!=$school_id){
                $teacher_data['apply_school_status']=WAIT_SCHOOL_PASS_STATUS;
            }
            
            $this->model->startTrans();
            if(!$this->model->updateInfoById($teacher_data,$teacher_id)){
                $this->model->rollback();
                $this->error('数据入库失败'); 
            }      
            if($teacher_result['school_id']!=$school_id){   
                //删除校建班的课表
                if(!$class_model->deleteClassTimetable(0,$teacher_id)){   
                    $this->model->rollback();
                    $this->error('数据入库失败'); 
                }   
                //删除该教师在校建班的课表的信息
                if(!$class_model->deleteClassTeacher($teacher_id,true)){
                    $this->model->rollback();
                    $this->error('数据入库失败'); 
                } 
            } 
            $this->model->commit(); 
            
            $this->redirect(U('Teacher/updateTeacher'),array('id'=>$teacher_id));
            
        }else{
            $school_model=D('Dict_schoollist');  
            $id=getParameter('id','int'); 
            $province_result=$school_model->getProvince();
            $city_result=array();
            $district_result=array();
            $school_result=array();
            $result=$this->model->getTeacherSimpleData($id);
            if(!empty($result)){
                $city_result=$school_model->getCityByProvince($result['province_id']);  
                $district_result=$school_model->getDistrictByCity($result['city_id']);
                $school_result=$school_model->getSchoolByDistrict($result['district_id']);
            }
            $this->assign('province_list',$province_result);    
            $this->assign('city_list',$city_result);    
            $this->assign('district_list',$district_result);
            $this->assign('school_list',$school_result);        
            $this->assign('otehr_school_id',OTHER_SCHOOL_ID);   
            $this->assign('data',$result);              
            $this->display();
        }
    }
    
    
    public function importTeacherView(){
        $school_model=D('Dict_schoollist');
        $province_result=$school_model->getProvince(); 
        $this->assign('province_list',$province_result); 
        $this->display('teacherImport');
    } 
    
    
    /*
     * code
     * 1为gb2312转utf8
     * 2为gbk转utf8
     * 3为utf-8直接返回
     * 4为utf8转gbk
     */
    function encode_string($code,$string){
        $return_string='';
        if($code==1){
            $return_string=iconv('gbk', 'utf-8', $string);
        }else if($code==2){
            $return_string=iconv('gbk', 'utf-8', $string);
        }else if($code==0){
            $return_string=$string;
        }else{
            $return_string=iconv('utf-8', 'gbk', $string);
        }  
        return $return_string;
    }
    
    
    /*
     * 下载教师导入模板
     */ 
    public function downloadTeacherDemo(){ 
        $csv=new CSV(); 
        $file="Public/csv/admin/teacherDemo.csv"; 
        $csv->downloadFile($file);
    }
    
    
    /*
     * 下载导入失败的数据
     */
    public function downloadImportErrorData(){
        if (!session('?admin')) redirect(U('Login/login'));
          
        $teacher_namr_arr=$_POST['teacher_name'];
        $telephone_arr=$_POST['telephone']; 
        $sex_arr=$_POST['sex']; 
        $grade_course_arr=$_POST['grade_course']; 
        $email_arr=$_POST['email']; 
        $brief_intro_arr=$_POST['brief_intro']; 
        
        $str="教师姓名,教师手机号,性别,任教年级学科,邮箱,简介\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($teacher_namr_arr as $key=>$val){
            $teacher_name=iconv('utf-8','gbk', $val);
            $telephone=iconv('utf-8','gbk', $telephone_arr[$key]);    
            $sex=iconv('utf-8','gbk', $sex_arr[$key]); 
            $grade_course=iconv('utf-8','gbk', $grade_course_arr[$key]); 
            $email=iconv('utf-8','gbk', $email_arr[$key]); 
            $brief_intro=iconv('utf-8','gbk', $brief_intro_arr[$key]); 
            
            $str.=$teacher_name.",".$telephone.",".$sex.",".$grade_course.",".$email.",".$brief_intro."\n";
        } 
            $filename=date('Ymd').rand(0,1000).'教师导入失败信息'.'.csv';
            $csv=new CSV();
            $csv->downloadFileCsv($filename,$str);
    }
    
    
    /*
     * 导入教师
     */
    public function importTeacher(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        if(empty($_FILES)){ 
            $this->showjson(1001,'文件为空'); 
        }
        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){
            $this->showjson(1002,'文件内容为空');
        }
        $str_encode=A('Admin/Student')->getStringEncode($result['result'][0][0]);
        $i_data=$result['result'];        
        $length=$result['length'];
        
        $school_model=D('Dict_schoollist'); 
        $grade_model=D('Dict_grade'); 
        $course_model=D('Dict_course');
         
        $school_id=getParameter('school_id','int');
        $school_result=$school_model->getSchoolInfo($school_id);
        if(empty($school_result)){
            $this->showjson(1003,'学校参数错误');
        } 
        $vip_config=C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');
        $notice_array=array();
        $success_array=array();
        $email_reg="/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/";
        $tel_reg="/^1[34578]{1}\d{9}$/";
        for($i=1;$i<$length;$i++){
            $data['name']=trim($this->encode_string($str_encode,$i_data[$i][0]));
            $data['telephone']=trim($this->encode_string($str_encode,$i_data[$i][1]));
            $data['sex']=trim($this->encode_string($str_encode,$i_data[$i][2]));
            $grade_course=trim($this->encode_string($str_encode,$i_data[$i][3]));
            $data['grade_course']=$grade_course;
            $data['email']=trim($this->encode_string($str_encode,$i_data[$i][4]));
            $data['brief_intro']=trim($this->encode_string($str_encode,$i_data[$i][5]));
            $data['school_id']=$school_id;
            $data['password']=sha1(ADMIN_IMPORT_PASSWORD);
            $data['create_at']=time();
            $notice=$data; 
            $success_data=$data; 
            if($data['sex']!=''){
                if($data['sex']!=SEX_MAN && $data['sex']!=SEX_WOMAN){
                    $notice['notice_message']='性别参数有误';
                    $notice_array[]=$notice;
                    continue;
                }
            }
            //判断手机号 
            if(!preg_match($tel_reg,$data['telephone'])){      
                $notice['notice_message']='手机号格式不正确';
                $notice_array[]=$notice;
                continue;
            }
            $teacher_result=$this->model->getTeacherByTel($data['telephone']);
            if(!empty($teacher_result)){
                $notice['notice_message']='该手机号已存在';
                $notice_array[]=$notice;
                continue;
            }
            if($data['email']!=''){
                if(!preg_match($email_reg,$data['email'])){
                    $notice['notice_message']='邮箱格式不正确';
                    $notice_array[]=$notice;
                    continue;
                }
            }
            
            $this->model->startTrans();
            unset($data['grade_course']);
            if(!($insert_id=$this->model->addTeacherData($data))){
                $this->model->rollback();
                $notice['notice_message']='数据入库失败';
                $notice_array[]=$notice;
                continue;
            }
            if($vip_config && $vip_config<=VIP_CONFIG_MAX_NUM){ 
                give_new_vip_operation(ROLE_TEACHER,$vip_config,$insert_id,$school_id);
            }
            $grade_course_array=explode('.',$grade_course);
            for($j=0;$j<count($grade_course_array);$j++){
                $grade_course_arr=explode('级',$grade_course_array[$j]);
                $grade_str=$grade_course_arr[0].'级';
                $course_str=$grade_course_arr[1];
                $grade_result=$grade_model->getGradeByName($grade_str);
                if(empty($grade_result)){
                    $this->model->rollback();
                    $notice['notice_message']='年级不存在';
                    $notice_array[]=$notice;
                    break;
                }
                $course_result=$course_model->getCourseData($course_str);
                if(empty($course_result)){
                    $this->model->rollback();
                    $notice['notice_message']='学科不存在';
                    $notice_array[]=$notice;
                    break;
                } 
                $teacher_second_data['teacher_id']=$insert_id;
                $teacher_second_data['course_id']=$course_result['id'];
                $teacher_second_data['grade_id']=$grade_result['id'];
                if(!$this->model->addTeacherSecond($teacher_second_data)){
                    $this->model->rollback();
                    $notice['notice_message']='数据插入失败';
                    $notice_array[]=$notice;
                    break;
                }
            }
            if(!isset($notice['notice_message'])){
                $success_array[]=$success_data;
            }
            $this->model->commit();
        }

        $big_data=array();
        $big_data['success']=$success_array;
        $big_data['failed']=$notice_array;
        $this->showjson(200,'success',$big_data);
    }
    
    
    /*
     * 批量导出教师
     */
    public function exportedTeacher(){
        if (!session('?admin')) redirect(U('Login/login'));
        $school_category=C('SCHOOL_CATEGORY');
        set_time_limit(0); 
        if(empty($_POST)){
            $this->error('参数错误');
        }else{
            $condition_arr=I('hid'); 
            $condition['auth_teacher.id']=array('in',$condition_arr);    
            $data=$this->model->getTeacherDataAll($condition);        
            
            $str="教师姓名,教师电话,注册时间,所属学校类型,省,市,区,学校名称,任教学科,任教班级,账号状态,教师审核状态,加入学校审核状态\n";
            $str=iconv('utf-8','gbk', $str);
            foreach($data as $v){
                $teacher_name=iconv('utf-8','gbk', $v['teacher_name']);
                $telephone=iconv('utf-8','gbk', $v['telephone']);
                $regTime = date("Y-m-d", $v['create_at']);
                $province=iconv('utf-8','gbk', $v['province']);
                $city=iconv('utf-8','gbk', $v['city']);
                $district=iconv('utf-8','gbk', $v['district']);
                $school_cat='';
                foreach($school_category as $key=>$val){
                    if($v['school_category']==$key){
                        $school_cat=$val;
                    }
                }
                $school_cat=iconv('utf-8','gbk', $school_cat);
                $schoolName=iconv('utf-8','gbk', $v['school_name']);
                $course=iconv('utf-8','gbk', $v['course']);
                $class_name=iconv('utf-8','gbk', $v['class_name']); 
                if($v['flag']==1){
                    $account_status='正常';
                }else{
                    $account_status='停用';
                }
                $account_status=iconv('utf-8','gbk', $account_status);
                if($v['auth_status']==1){
                    $auth_status='已认证';
                }elseif($v['auth_status']==2){
                    $auth_status='已拒绝';
                }else{
                    $auth_status='待认证';
                }
                $auth_status=iconv('utf-8','gbk', $auth_status);

                if($v['apply_school_status']==1){
                    $apply_school_status='同意加入';
                }else{
                    $apply_school_status='待审核';
                }
                $apply_school_status=iconv('utf-8','gbk', $apply_school_status);

                $str.=$teacher_name.",".$telephone.",".$regTime.','.$school_cat.",".$schoolName.','.$province.','.$city.','.$district.','.$course.",".$class_name.",".$account_status.",".$auth_status.",".$apply_school_status."\n";
            } 
            $filename=date('Ymd').rand(0,1000).'Teacher'.'.csv';
            $csv=new CSV();
            //export disable
            //$csv->downloadFileCsv($filename,$str);
        }
    }
    
    /*
     * 导出全部教师
     */
    public function exportedTeacherAll(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        set_time_limit(0);
        $filter['teacher_name']=getParameter('teacher_name','str',false);
        $filter['school_name']=getParameter('school_name','str',false);
        $filter['school_code']=$_GET['school_code'];
        $filter['telephone']=getParameter('telephone','str',false); 
        $filter['course']=getParameter('course','int',false);
        $filter['grade']=getParameter('grade','int',false);
        $filter['account_status']=$_GET['status'];                 
        $filter['auth_status']=$_GET['auth_status'];
        $filter['apply_school_status']=$_GET['apply_school_status'];
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition['auth_teacher.flag']=array('neq','-1');
        if(!empty($filter['teacher_name']))   $condition['auth_teacher.name']=array('like', '%' . $filter['teacher_name']. '%'); 
        if(!empty($filter['school_name']))   $condition['dict_schoollist.school_name']=array('like', '%' . $filter['school_name']. '%');
        if(!empty($filter['school_code']))   $condition['dict_schoollist.school_code']=array('like', '%' . $filter['school_code']. '%');
        if(!empty($filter['telephone']))   $condition['auth_teacher.telephone']=array('like', '%' . $filter['telephone']. '%');
        if(!empty($filter['course']))   $where['dict_course.id']=$filter['course'];
        if(!empty($filter['grade']))   $where['dict_grade.id']=$filter['grade'];
        if($filter['account_status']!='')   $condition['auth_teacher.flag']=intval($filter['account_status']);
        if($filter['auth_status']!='')   $condition['auth_teacher.auth_status']=intval($filter['auth_status']);
        if($filter['apply_school_status']!='')   $condition['auth_teacher.apply_school_status']=intval($filter['apply_school_status']);
        
        $school_cateogyr=C('SCHOOL_CATEGORY');
        $data=$this->model->getTeacherDataAll($condition,$order,'',$where);

        $str="教师姓名,教师电话,注册时间,所属学校类型,省,市,区,学校名称,任教学科,任教班级,账号状态,教师审核状态,加入学校审核状态\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($data as $v){
            $teacher_name=iconv('utf-8','gbk', $v['teacher_name']);
            $telephone=iconv('utf-8','gbk', $v['telephone']);
            $regTime = date("Y-m-d", $v['create_at']);
            $province=iconv('utf-8','gbk', $v['province']);
            $city=iconv('utf-8','gbk', $v['city']);
            $district=iconv('utf-8','gbk', $v['district']);
            $schoolName=iconv('utf-8','gbk', $v['school_name']);
            $school_cat='';
            foreach($school_cateogyr as $key=>$val){
                if($v['school_category']==$key){
                    $school_cat=$val;
                }
            }
            $school_cat=iconv('utf-8','gbk', $school_cat);
            $course=iconv('utf-8','gbk', $v['course']);
            $class_name=iconv('utf-8','gbk', $v['class_name']); 
            if($v['flag']==1){
                $account_status='正常';
            }else{
                $account_status='停用';
            }
            $account_status=iconv('utf-8','gbk', $account_status);
            if($v['auth_status']==1){
                $auth_status='已认证';
            }elseif($v['auth_status']==2){
                $auth_status='已拒绝';
            }else{
                $auth_status='待认证';
            }
            $auth_status=iconv('utf-8','gbk', $auth_status);
            
            if($v['apply_school_status']==1){
                $apply_school_status='同意加入';
            }else{
                $apply_school_status='待审核';
            }
            $apply_school_status=iconv('utf-8','gbk', $apply_school_status);

            $str.=$teacher_name.",".$telephone.",".$regTime.','.$school_cat.",".$schoolName.','.$province.','.$city.','.$district.','.$course.",".$class_name.",".$account_status.",".$auth_status.",".$apply_school_status."\n";
        } 
        $filename=date('Ymd').rand(0,1000).'Teacher'.'.csv';
        $csv=new CSV();
        //export disable
        //$csv->downloadFileCsv($filename,$str);
    }
}