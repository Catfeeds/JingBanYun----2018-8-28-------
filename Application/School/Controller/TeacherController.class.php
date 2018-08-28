<?php
namespace School\Controller;
use Think\Controller; 
use Common\Common\CSV;
  
define('ENABLE_TEACHER_STATUS',1);
define('DISABLE_TEACHER_STATUS',0);
define('CHECKED_TEACHER_STATUS',1);
define('TEACHER_AUTH_ALLOW', 1);
define('TEACHER_AUTH_DENY', 2); 
define('SCHOOL_CLASS',1);
define('SCHOOL_ID',session('school.school_id')); 
define('TEACHER_SECOND_INFO_EXISTS','教师年级学科已存在！');
define('TEACHER_TEACHS_EXISTS','教师已任教该班级该学科！');


class TeacherController extends Controller
{  
    public $model;
    public $page_size=20; 
            
    public function __construct() {
        parent::__construct();  
        $this->model=D('Auth_teacher');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }
             
    
    /*
     * 教师列表    
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
        $filter['class']=getParameter('class','str',false);
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition['auth_teacher.flag']=array('neq',-1);
        $condition['auth_teacher.school_id']=SCHOOL_ID;
        $class_status=SCHOOL_CLASS;
        if(!empty($filter['teacher_name']))   $condition['auth_teacher.name']=array('like', '%' . $filter['teacher_name']. '%');  
        if(!empty($filter['telephone']))   $condition['auth_teacher.telephone']=array('like', '%' . $filter['telephone']. '%');
        if(!empty($filter['course']))   $where['dict_course.id']=$filter['course']; 
        if(!empty($filter['class'])) {
            $data = explode('_',$filter['class']);
            $condition['dict_grade.id']=$data[0];
            $condition['biz_class.id']=$data[1];
        }
        if($filter['account_status']!='')   $condition['auth_teacher.flag']=intval($filter['account_status']);
        $condition['auth_teacher.apply_school_status']= CHECKED_TEACHER_STATUS;
        //if($filter['apply_school_status']!='')   $condition['auth_teacher.apply_school_status']=intval($filter['apply_school_status']);
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        }
        $result=$this->model->getTeacherData($condition,$order,$class_status,$where);
//echo M()->getLastSql();die;
        $course_model=D('Dict_course'); 
        $course_result=$course_model->getCourseList();
        //TODO：获取当前学校下的所有班级
        $schoolId['dict_schoollist.id'] = session('school.school_id');
        $classModel = D('Biz_class');
        $classResult = $classModel->getAllClassBySchool($schoolId);

        
        $this->assign('condition_str',$condition_string);
        $this->assign('teacher_name',$filter['teacher_name']);
        $this->assign('telephone',$filter['telephone']);
        $this->assign('course',$filter['course']);
        $this->assign('apply_status',$filter['apply_school_status']);
        $this->assign('account_status',$filter['account_status']);
        $this->assign('order',$order);
        
        $this->assign('course_list',$course_result);
        $this->assign('class',$filter['class']);
        $this->assign('class_list',$classResult);
        $this->assign('list',$result['data']);
        $this->assign('page',$result['page']);
        $this->display();
    }

//    /*
//     *描述：根据学科反查班级接口
//     */
//    public function getGradeByCourse(){
//        $courseId = getParameter('courseId','int');
//        $schoolId = session('school.school_id');
//        $where['auth_teacher_second.course_id'] = $courseId;
//        $where['dict_schoollist.id'] = $schoolId;
//        $classModel = D('Biz_class');
//        $result = $classModel->getAllClassByCourse($where);
//        echo M()->getLastSql();die;
//        var_dump($result);die;
//    }
    
    /*
     * 创建教师
     */
    public function createTeacherAccount(){
        if (!session('?school')) redirect(U('Login/login'));  
        A('School/SchoolAdmin')->check_permissions();

        if(!empty($_POST)){ 
            //$vip_config=C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');
            $show_next_page_flag=getParameter('next_flag', 'int'); 
            $teacher_name=getParameter('name', 'str');
            $telephone=getParameter('telephone', 'str');
            $sex=getParameter('sex', 'str');
            $password=getParameter('password', 'str');
            $grade_course_ids=getParameter('grade_course_ids', 'str');
            $email=getParameter('email', 'str',false); 
            $brief_intro=getParameter('brief_info','str',false); 
            $grade_course_arr=explode(',',$grade_course_ids);
            $grade_course_arr=array_unique($grade_course_arr);
            if(empty($grade_course_arr)){
                $this->error('任教年级学科参数有误!');
            }
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
            $result=$this->model->getTeacherByTel($telephone);
            if(!empty($result)){
                $this->error('该手机号已经存在!');
            }
            if($sex!=SEX_MAN && $sex!=SEX_WOMAN){
                $this->error('性别参数有误');
            }
            
            $teacher_data['name']=$teacher_name;
            $teacher_data['telephone']=$telephone;
            $teacher_data['password']=sha1($password);
            $teacher_data['school_id']=SCHOOL_ID;
            $teacher_data['email']=$email;
            $teacher_data['sex']=$sex;
            $teacher_data['brief_intro']=$brief_intro;
            $teacher_data['create_at']=time();
            $teacher_data['apply_school_status']=1;
            //得到所有年级和所有学科
            $grade_model=D('Dict_grade');
            $grade_result=array_column($grade_model->getGradeList(true),'id'); 
            $course_model=D('Dict_course');
            $course_result=array_column($course_model->getCourseList(),'id');           
            
            $this->model->startTrans();
            if(!($insert_id=$this->model->addTeacherData($teacher_data))){
                $this->model->rollback();
                $this->error('教师信息入库失败!');
            }
           /* if($vip_config && $vip_config<=3){
                give_new_vip_operation(ROLE_TEACHER,$vip_config,$insert_id,SCHOOL_ID);
            }*/
            if(!($school_info=get_school_vip_info(SCHOOL_ID))){
                return array('status'=>'failed','message'=>'学校id参数错误或数据为空');
            }else{
                if ($school_info['user_auth'] == 3 && time() >= $school_info['auth_start_time'] && time() < $school_info['auth_end_time'] ) {
                    give_new_vip_operation(ROLE_TEACHER,3,$insert_id,SCHOOL_ID);
                }else{
                    give_new_vip_operation(ROLE_TEACHER,1,$insert_id,SCHOOL_ID);
                }
            }
            foreach($grade_course_arr as $val){
                $temp_arr=explode('_',$val);
                @$grade=$temp_arr[0];
                @$course=$temp_arr[1];
                if(!in_array($grade,$grade_result)){
                    $this->model->rollback();
                    $this->error('年级信息不存在!');
                }
                if(!in_array($course,$course_result)){
                    $this->model->rollback();
                    $this->error('学科信息不存在!');
                }
                $teacher_contact['teacher_id']=$insert_id;
                $teacher_contact['grade_id']=$grade;
                $teacher_contact['course_id']=$course; 
                if(!$this->model->addTeacherSecond($teacher_contact)){
                    $this->model->rollback();
                    $this->error('教师学科信息入库失败!');
                }
            }
            $this->model->commit(); 
            if($show_next_page_flag){
                $this->redirect(U('Teacher/teacherChooseClass',array('id'=>$insert_id)));
            }else{
                $this->redirect(U('Teacher/teacherList'));
            }
        }else{
            $grade_model=D('Dict_grade');
            $course_model=D('Dict_course'); 
            $course_result=$course_model->getCourseList();
            $where['school_id'] = SCHOOL_ID;
            $where['biz_class.class_status'] = 1;
            $where['biz_class.is_delete'] = 0;
            $grade_result = $grade_model->getGradeListBySchool($where);
            $this->assign('course_list',$course_result);
            $this->assign('grade_list',$grade_result);
            
            $this->display();
        }
    }
    
    //TODO:创建教师时关联班级逻辑需要修改
    /*
     * 写教师关联班级
     */
    public function teacherChooseClass(){
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions();
        
        if(!empty($_POST)){  
            $class_model=D('Biz_class');
            $grade_model=D('Dict_grade');
            $course_model=D('Dict_course');
            $teacher_id=getParameter('id','int'); 
            $data['teacher_id']=$teacher_id;
             
            $result=$this->model->getTeachInfo($teacher_id);
            if(empty($result)){
                $this->error('参数错误!');
            }else{
                if($result['school_id']!=SCHOOL_ID){
                    $this->error('无权进行操作!');
                }
            }
            $result=$this->model->getTeacherClassTeachesInfo($data);
            //判断教师如果有教师任教班级信息就跳转到列表页(新建的老师还没有关联过任何班级所以异常)
            if(!empty($result)){
                //$this->redirect(U('Teacher/teacherList'));
                $this->error('异常访问');
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
                    $datas['grade_id'] = $class_course[2];
                    $datas['course_id']=$class_course[1];
                    $datas['teacher_id']=$teacher_id;
                    if(!$this->model->matching($datas)){
                        $this->model->rollback();
                        $this->error('请关联相同年级和学科的班级');
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
            }else{
                if($result['school_id']!=SCHOOL_ID){
                    $this->error('无权限操作此教师!');
                }
            }
            $where['school_id'] = SCHOOL_ID;
            $where['biz_class.class_status'] = 1;
            $where['biz_class.is_delete'] = 0;
            $grade_result = $grade_model->getGradeListBySchool($where);
            //$grade_result=$grade_model->getGradeList(true);
            $teachs_course=$this->model->getTeacherAllCourse($teacher_id);  
            $this->assign('grade_list',$grade_result);
            $this->assign('course_list',$teachs_course);    
            $this->assign('teacher_id',$teacher_id);
            $this->display('teacherAddClass');
        }
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

            //TODO:给审核通过的添加VIP
            if ( $apply_status == 1) {
                if(!($school_info=get_school_vip_info($result['school_id']))){
                    return array('status'=>'failed','message'=>'学校id参数错误或数据为空');
                }else{
                    if ($school_info['user_auth'] == 3 && time() >= $school_info['auth_start_time'] && time() < $school_info['auth_end_time'] ) {
                        give_new_vip_operation(ROLE_TEACHER,3,$id,SCHOOL_ID);
                    }else{
                        give_new_vip_operation(ROLE_TEACHER,1,$id,SCHOOL_ID);
                    }
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
      
     
    
    /*
     * 教师详情
     */
    public function teacherDetail(){
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions();
        
        $id=getParameter('id','int');
        $result=$this->model->getTeacherSimpleData($id,SCHOOL_ID);  
        $this->assign('data',$result); 
        $this->display();
    }
    
     
    /*
     * 教师任教的学科iframe
     */
    public function teacherCourseList(){    
        $grade_model=D('Dict_grade');
        $course_model=D('Dict_course');
        $id=getParameter('id','int');
        //$filter['course']=getParameter('course','int',false);
        //$filter['grade']=getParameter('grade','int',false);
        //$order=getParameter('order','int',false);
        //$order?$order='asc':$order='desc';
         
        $condition['auth_teacher.school_id']=SCHOOL_ID;
        $condition['auth_teacher.id']=$id;
        //if(!empty($filter['course']))   $condition['dict_course.id']=$filter['course'];
        //if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade']; 
        $result=$this->model->getTeacherCourse($condition,$order); 
        $course_result=$course_model->getCourseList();
        $where['school_id'] = SCHOOL_ID;
        $where['biz_class.class_status'] = 1;
        $where['biz_class.is_delete'] = 0;
        $grade_result = $grade_model->getGradeListBySchool($where);
        //$grade_result=$grade_model->getGradeList(true);
        
        $this->assign('course_list',$course_result);
        $this->assign('grade_list',$grade_result);
        $this->assign('list',$result);
        $this->assign('teacher_id',$id);
        
        $this->display();
    }
    
    
    /*
     * 删除某个教师的某个任教的年级和学科
     */
    public function detelteTeacherGradeInfo(){
        if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);
        A('School/SchoolAdmin')->check_permissions(true);
        
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
        if($result['school_id']!=SCHOOL_ID){
            $this->showjson(403,COMMON_FAILED_MESSAGE);
        }
        //判断学科只有1个的话,不允许删除
        $all_course=$this->model->getTeacherAllCourse($teacher_id);
        if(count($all_course)==1){
            $this->showjson(407,'教师下必须保留一项任教年级学科!');
        }
        $course_id=$teacher_second_result['course_id'];
        $this->model->startTrans(); 
        if(!$this->model->deleteTeacherSecond($id,$teacher_id)){
            $this->model->rollback();
            $this->showjson(404,'操作失败!');
        }
        //删除该教师下的所有校建班的任教学科
        if(!$class_model->deleteClassTeacher($teacher_id,true,$course_id)){
            $this->model->rollback();
            $this->showjson(405,'操作失败!');
        }
        //删除该教师下的校建班任教课表
        if(!$class_model->deleteSchoolClassByCourse($course_id)){
            $this->model->rollback();
            $this->showjson(406,'操作失败!');
        }
        $this->model->commit();
        $this->showjson(200);
    }
    
    
    /*
     * 添加教师的任教年级学科信息
     */
    public function addTeacherGradeInfo(){
        if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);
        A('School/SchoolAdmin')->check_permissions(true);
        
        $grade_model=D('Dict_grade');
        $course_model=D('Dict_course');
        $teacher_id=getParameter('teacher_id','int'); 
        $grade_id=getParameter('grade_id','int'); 
        $course_id=getParameter('course_id','int'); 
        $result=$this->model->getTeachInfo($teacher_id);    
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{
            if($result['school_id']!=SCHOOL_ID){
                $this->showjson(402,COMMON_FAILED_MESSAGE);
            }
        }
        $grade_result=$grade_model->getGradeInfo($grade_id);
        if(empty($grade_result)){
            $this->showjson(403,COMMON_FAILED_MESSAGE);
        }
        $course_result=$course_model->getCourseInfo($course_id);
        if(empty($course_result)){
            $this->showjson(404,COMMON_FAILED_MESSAGE);
        }
        $sql_con['auth_teacher_second.teacher_id']=$teacher_id;
        $sql_con['auth_teacher_second.course_id']=$course_id;
        $sql_con['auth_teacher_second.grade_id']=$grade_id;        
        $teacher_second_info=$this->model->getTeacherCourse($sql_con); 
        if(!empty($teacher_second_info)){
            $this->showjson(405,TEACHER_SECOND_INFO_EXISTS);
        }
        $data['teacher_id']=$teacher_id;
        $data['course_id']=$course_id;
        $data['grade_id']=$grade_id; 
        $status=$this->model->addTeacherSecond($data);
        if($status){
            $this->showjson(200);
        }else{
            $this->showjson(406,COMMON_FAILED_MESSAGE);
        }
    }
    
    
    /*
     * 教师任教的班级
     */
    public function teacherClassList(){ 
        $class_model=D('Biz_class');
        $id=getParameter('id','int');       
//        $filter['class']=getParameter('class','int',false);
//        $filter['grade']=getParameter('grade','int',false);  
//        $filter['grade']=getParameter('course','int',false);  
//        $filter['class_flag']=getParameter('class_flag','int',false);  
//        $filter['class_code']=getParameter('class_code','int',false); 
//        $order=getParameter('order','int',false);
        $order?$order='asc':$order='desc';
        
        $school_class=1;
        $condition['biz_class.is_delete']=0;
        $condition['auth_teacher.school_id']=SCHOOL_ID; 
        $condition['biz_class_teacher.is_handler']=0;      
        $condition['auth_teacher.id']=$id;      
        $condition['biz_class.class_status']=$school_class;
//        if(!empty($filter['class']))   $condition['biz_class.id']=$filter['class']; 
//        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade']; 
//        if(!empty($filter['course']))   $condition['dict_course.id']=$filter['course'];  
//        if(!empty($filter['class_flag']))   $condition['biz_class.flag']=$filter['class_flag'];   
//        if(!empty($filter['class_code']))   $condition['biz_class.class_code']=$filter['class_code']; 
        $result=$this->model->getTeacherClassData($condition,$order);  
        $grade_data=$class_model->getClassDataBySchool(SCHOOL_ID);
        $course_condition['auth_teacher.school_id']=SCHOOL_ID;
        $course_condition['auth_teacher.id']=$id; 
        $course_result=$this->model->getTeacherCourse($course_condition,'desc',true);  
        $this->assign('list',$result);  
        $this->assign('grade_list',$grade_data);
        $this->assign('course_list',$course_result);   
        $this->assign('teacher_id',$id);
        
        $this->display();
    }



    //TODO:修改时添加教师任教的班级逻辑要修改
    /*
     * 添加教师任教的班级
     */
    public function addTeacherClassInfo(){

        if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);
        A('School/SchoolAdmin')->check_permissions(true);
        
        $teacher_id=getParameter('teacher_id','int');
        $class_id=getParameter('class_id','int');  
        $course_id=getParameter('course_id','int');
        
        $class_model=D('Biz_class');
        $course_model=D('Dict_course');
        $teacher_result=$this->model->getTeachInfo($teacher_id);


        //根据所选班级查找对应的年级
        $class_info = $class_model->getClassAndGradeInfo($class_id);

        $datas['teacher_id'] = $teacher_id;
        $datas['course_id'] = $course_id;


        //根据教师的ID和学科ID查找关联表中的所有满足此条件的年级ID
        $matchingOfGrade=$this->model->matchingOfGrade($datas);//老师任教年级和学科要和任教班级中的年级学科匹配
//var_dump($matchingOfGrade);
//var_dump($class_info['id']);die;
        if(count($matchingOfGrade) > 1){
            if( !in_array($class_info['id'],array_column($matchingOfGrade,'grade_id'))){
                $this->showjson(411,'老师所选学科的任教年级于班级中的年级不符');
            }
        }
        else{
            if($matchingOfGrade[0]['grade_id'] != $class_info['id']){
            $this->showjson(412,'老师所选学科的任教年级于班级中的年级不符');
        }
        }

        if(empty($teacher_result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{
            if($teacher_result['school_id']!=SCHOOL_ID){
                $this->showjson(402,'无权操作此教师!');
            }
            if($teacher_result['apply_school_status']!=APPLY_SCHOOL_ALLOW){
                $this->showjson(409,'教师加入学校状态还未审核通过!');
            }
        }
        $class_result=$class_model->getClassInfo($class_id);
        if(empty($class_result)){
            $this->showjson(403,COMMON_FAILED_MESSAGE);
        }else{
            if($class_result['school_id']!=SCHOOL_ID){
                $this->showjson(404,'无权操作此教师');
            }
            if($class_result['class_status']==PERSONAL_CLASS){
                $this->showjson(409,COMMON_FAILED_MESSAGE);
            }
        }
        $course_result=$course_model->getCourseInfo($course_id);
        if(empty($course_result)){
            $this->showjson(405,COMMON_FAILED_MESSAGE);
        }
        
        $sql_con['teacher_id']=$teacher_id;
        $sql_con['course_id']=$course_id;
        $sql_con['class_id']=$class_id;   
        $second_con['dict_course.id']=$course_id;
        $second_con['auth_teacher.id']=$teacher_id;
        
        $teacher_teachs_info=$this->model->getTeacherCourse($second_con);       
        if(empty($teacher_teachs_info)){
            $this->showjson(406,COMMON_FAILED_MESSAGE);
        }   
        $teaches=$this->model->getTeacherClassTeachesInfo($sql_con);

        if(!empty($teaches)){
            $this->showjson(407,TEACHER_TEACHS_EXISTS);
        }
        $data=$sql_con;
        $data['create_at']=time();
        $this->model->startTrans();
        if(!$this->model->teacherBindClass($data)){  
            $this->model->rollback();
            $this->showjson(408,COMMON_FAILED_MESSAGE);
        } 
        if(!$class_model->deleteClassTeacherRecord($class_id,$teacher_id,$course_id)){
            $this->model->rollback();
            $this->showjson(408,COMMON_FAILED_MESSAGE);
        }

        //消息推送
        $parameters = array(
            'msg' => array(
                C('SCHOOL_ROOT'),
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
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions();
        
        if(!empty($_POST)){ 
            $teacher_id=getParameter('id','int');
            $teacher_name=getParameter('name', 'str');
            $telephone=getParameter('telephone', 'str');
            $sex=getParameter('sex', 'str');
            $password=getParameter('password', 'str'); 
            $email=getParameter('email', 'str',false); 
            $brief_intro=getParameter('brief_intro','str',false);             
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
            $result=$this->model->getTeacherByTel($telephone,$teacher_id);
            if(!empty($result)){
                $this->error('该手机号已经存在!');
            }
            if($sex!=SEX_MAN && $sex!=SEX_WOMAN){
                $this->error('性别参数有误!');
            }
            $teacher_result=$this->model->getTeachInfo($teacher_id);
            if(empty($teacher_result)){
                $this->error('教师信息不存在!');
            }
            $result=$this->model->getTeachInfo($teacher_id,$password);
            if(empty($result)){
               $teacher_data['password']=sha1($password);
            }else{
                if($result['school_id']!=SCHOOL_ID){
                    $this->error('无权限操作此教师信息!');
                }
            }
            $teacher_data['name']=$teacher_name;
            $teacher_data['telephone']=$telephone; 
            $teacher_data['email']=$email;
            $teacher_data['sex']=$sex;
            $teacher_data['brief_intro']=$brief_intro;
            if(!$this->model->updateInfoById($teacher_data,$teacher_id)){
                $this->error('数据入库失败'); 
            }   
            $this->redirect(U('Teacher/teacherList'),array('id'=>$teacher_id));
                    
        }else{
            $id=getParameter('id','int');
            $result=$this->model->getTeacherSimpleData($id,SCHOOL_ID);      
            $this->assign('data',$result);      
            $this->display();
        }
    }
    
    
    public function importTeacherView(){
        A('School/SchoolAdmin')->check_permissions();
        
        $school_model=D('Dict_schoollist');
        $province=$school_model->getProvince();
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
        $filepath="Public/csv/school/teacherDemo.csv";
//        $file="Public/csv/school/teacherDemo.csv";
//        $csv->downloadFile($file);
        $filename = '导入教师模板';
        $csv->downloadFileCopy($filepath,$filename);
    }
    
    
    /*
     * 下载导入失败的数据
     */
    public function downloadImportErrorData(){
        if (!session('?school')) redirect(U('Login/login'));
          
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
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions();
        
        if(empty($_FILES)){ 
            $this->showjson(1001,'文件为空');  //1002文件为空
        }
        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){
            $this->showjson(1002,'文件内容为空');
        }
        $str_encode=A('School/Student')->getStringEncode($result['result'][0][0]);
        $i_data=$result['result'];        
        $length=$result['length'];
        
        $school_model=D('Dict_schoollist'); 
        $grade_model=D('Dict_grade'); 
        $course_model=D('Dict_course');
         
        //$vip_config=C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');
        $school_id=SCHOOL_ID;
        $school_result=$school_model->getSchoolInfo($school_id);
        if(empty($school_result)){
            $this->showjson(1003,ID_NOT_EXISTS_MESSAGE);
        }
        $allow_join_school=1;
        $notice_array=array();
        $success_array=array();
        for($i=1;$i<$length;$i++){
            $data['name']=trim($this->encode_string($str_encode,$i_data[$i][0]));
            $data['telephone']=trim($this->encode_string($str_encode,$i_data[$i][1]));
            $data['sex']=trim($this->encode_string($str_encode,$i_data[$i][2]));
            $grade_course=trim($this->encode_string($str_encode,$i_data[$i][3]));
            $data['grade_course']=$grade_course;
            $data['email']=trim($this->encode_string($str_encode,$i_data[$i][4]));
            $data['brief_intro']=trim($this->encode_string($str_encode,$i_data[$i][5]));
            $data['school_id']=$school_id;
            $data['create_at']=time();
            $data['apply_school_status']=$allow_join_school;
            $notice=$data;
            $success_data=$data;
            if($data['sex']!=''){
                if($data['sex']!='男' && $data['sex']!='女'){
                    $notice['notice_message']='性别参数有误';
                    $notice_array[]=$notice;
                    continue;
                }
            }
            $data['password']=sha1(ADMIN_IMPORT_PASSWORD);
            
            //判断手机号 
            if(!preg_match("/^1[34578]{1}\d{9}$/",$data['telephone'])){      
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
                $email_reg="/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/";
                if(!preg_match($email_reg,$data['email'])){
                    $this->error('邮箱格式有误');
                }
            }
            $this->model->startTrans();
            unset($data['grade_course']);
            if(!($insert_id=$this->model->addTeacherData($data))){
                $this->model->rollback();
            }
            /*if($vip_config && $vip_config<=VIP_CONFIG_MAX_NUM){
                give_new_vip_operation(ROLE_TEACHER,$vip_config,$insert_id,$school_id);
            }*/
            if(!($school_info=get_school_vip_info(SCHOOL_ID))){
                return array('status'=>'failed','message'=>'学校id参数错误或数据为空');
            }else{
                if ($school_info['user_auth'] == 3 && time() >= $school_info['auth_start_time'] && time() < $school_info['auth_end_time'] ) {
                    give_new_vip_operation(ROLE_TEACHER,3,$insert_id,SCHOOL_ID);
                }else{
                    give_new_vip_operation(ROLE_TEACHER,1,$insert_id,SCHOOL_ID);
                }
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
                    continue;
                }
                $course_result=$course_model->getCourseData($course_str);
                if(empty($course_result)){
                    $this->model->rollback();
                    $notice['notice_message']='学科不存在';
                    $notice_array[]=$notice;
                    continue;
                } 
                $teacher_second_data['teacher_id']=$insert_id;
                $teacher_second_data['course_id']=$course_result['id'];
                $teacher_second_data['grade_id']=$grade_result['id'];
                if(!$this->model->addTeacherSecond($teacher_second_data)){
                    $this->model->rollback();
                    $notice['notice_message']='数据插入失败';
                    $notice_array[]=$notice;
                    continue;
                }
            }
            if(empty($notice_array)){
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
     * 批量导出
     */
    public function exportedTeacher(){
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions();
        
        if(empty($_POST)){
            $this->error('参数有误');
        }else{
            $condition_arr=I('hid'); 
            $condition['auth_teacher.id']=array('in',$condition_arr);     
            $condition['auth_teacher.school_id']=SCHOOL_ID;
            $data=$this->model->getTeacherDataAll($condition);        
            
            $str="教师姓名,教师电话,任教学科,任教班级,加入学校审核状态,账号状态\n";
            $str=iconv('utf-8','gbk', $str);
            foreach($data as $v){
                $teacher_name=iconv('utf-8','gbk', $v['teacher_name']);
                $telephone=iconv('utf-8','gbk', $v['telephone']);  
                
                $course=iconv('utf-8','gbk', $v['course']);
                $class_name=iconv('utf-8','gbk', $v['class_name']); 
                if($v['flag']==1){
                    $account_status='正常';
                }else{
                    $account_status='停用';
                }
                $account_status=iconv('utf-8','gbk', $account_status); 

                if($v['apply_school_status']==1){
                    $apply_school_status='已审核';
                }else{
                    $apply_school_status='待审核';
                }
                $apply_school_status=iconv('utf-8','gbk', $apply_school_status); 

                $str.=$teacher_name.",".$telephone.",".$course.",".$class_name.",".$apply_school_status.",".$account_status."\n";   
            }
            $filename=date('Ymd').rand(0,1000).'admin'.'.csv';
            $csv=new CSV();
            //export disable
            $csv->downloadFileCsv($filename,$str);
        }
    }
    
    
    /*
     * 导出全部
     */
    public function exportedTeacherAll(){
        if (!session('?school')) redirect(U('Login/login')); 
        A('School/SchoolAdmin')->check_permissions();
        
        $filter['teacher_name']=getParameter('teacher_name','str',false); 
        $filter['telephone']=getParameter('telephone','str',false);
        $filter['course']=getParameter('course','int',false);  
        $filter['account_status']=getParameter('status','int',false); 
        $filter['apply_school_status']=getParameter('apply_school_status','int',false); 
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition['auth_teacher.school_id']=SCHOOL_ID;
        if(!empty($filter['teacher_name']))   $condition['auth_teacher.name']=array('like', '%' . $filter['teacher_name']. '%');  
        if(!empty($filter['telephone']))   $condition['auth_teacher.telephone']=array('like', '%' . $filter['telephone']. '%');
        if(!empty($filter['course']))   $where['dict_course.id']=$filter['course']; 
        if(!empty($filter['account_status']))   $condition['auth_teacher.flag']=$filter['account_status']; 
        if(!empty($filter['apply_school_status']))   $condition['auth_teacher.apply_school_status']=$filter['apply_school_status'];
        $data=$this->model->getTeacherDataAll($condition,$order,SCHOOL_CLASS,$where); 
        $str="教师姓名,教师电话,任教学科,任教班级,加入学校审核状态,账号状态\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($data as $v){
            $teacher_name=iconv('utf-8','gbk', $v['teacher_name']);
            $telephone=iconv('utf-8','gbk', $v['telephone']);  

            $course=iconv('utf-8','gbk', $v['course']);
            $class_name=iconv('utf-8','gbk', $v['class_name']); 
            if($v['flag']==1){
                $account_status='正常';
            }else{
                $account_status='停用';
            }
            $account_status=iconv('utf-8','gbk', $account_status); 

            if($v['apply_school_status']==1){
                $apply_school_status='已审核';
            }else{
                $apply_school_status='待审核';
            }
            $apply_school_status=iconv('utf-8','gbk', $apply_school_status); 

            $str.=$teacher_name.",".$telephone.",".$course.",".$class_name.",".$apply_school_status.",".$account_status."\n";   
        }
        $filename=date('Ymd').rand(0,1000).'admin'.'.csv';
        $csv=new CSV();
        //export disable
        $csv->downloadFileCsv($filename,$str);
    }
}