<?php
namespace School\Controller;
use Think\Controller;
use Common\Common\CSV;
 
define('SCHOOL_CLASS',1);
define('CHECKED_STUDENT_STATUS',1);
define('CLASS_STUDETN_STATUS',2);
define('ENABLE_TEACHER_STATUS',1);
define('DISABLE_TEACHER_STATUS',0); 
define('STUDENT_EXISTS','该学生信息已存在');
define('INSERT_STUDENT_DATA_FAILED','数据插入失败');
define('GRADE_NOT_EXISTS','年级信息不存在');
define('CLASS_NOT_EXISTS','班级信息不存在');
define('SCHOOL_ID',session('school.school_id'));
define('STUDENT_JOIN_SCHOOL_CLASS_FAILED_MESSAGE','一个学生只能加入一个校建班!'); 
define('PARENT_INFO_NOT_EXISTS','家长信息不存在!'); 
define('PARENT_INFO_CONTACT_EXISTS','该手机号已是该学生的家长!');
define('VIP_CONFIG_MAX_NUM',3);
define('DENY_STUDENT_JOIN_CLASS',3);

class StudentController extends Controller
{ 

    public $model;
    public $page_size=20; 
            
    public function __construct() {
        parent::__construct();  
        $this->model=D('Auth_student');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }
             
    //Home@Admin/test跨目录调用
    /*
     * 学生列表
     */
    public function studentList(){      
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions();
        $class_model=D('Biz_class');
        $grade_model=D('Dict_grade'); 
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
        $condition['auth_student.apply_school_status']= CHECKED_STUDENT_STATUS;
        //if($filter['apply_school_status']!='')   $condition['auth_student.apply_school_status']=intval($filter['apply_school_status']);
        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade'];
        if(!empty($filter['class']))   $condition['biz_class.id']=$filter['class'];
        if($filter['account_status']!='')   $condition['auth_student.flag']=intval($filter['account_status']);  
        if(!empty($filter['grade'])){
            $class_result=$class_model->getClassDataBySchool(SCHOOL_ID,$filter['grade'],'',true);   
        }
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        }
        
        
        $grade_result=$grade_model->getGradeList(true);
        
               
        
        $result=$this->model->getStudentData($condition,$order,SCHOOL_CLASS);
//echo M()->getLastSql();die;
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
    
    
    /*
     * 通过或拒绝加入学校审核
     */
    public function updateApplyStatus(){
        if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);
        A('School/SchoolAdmin')->check_permissions(true);
        
        $class_model=D('Biz_class');
        $id=getParameter('id','int');
        $apply_status=getParameter('status','int');
        
        $result=$this->model->getStudentInfo($id);
        $school_data = D('Dict_schoollist')->getSchoolInfo($result['school_id']);


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
                $this->showjson(404,'操作失败');
            }
            if($apply_status==APPLY_SCHOOL_DENY){
                $class_con['biz_class_student.student_id']=$id; 
                $class_con['biz_class.class_status']=SCHOOL_CLASS;
                $class_result=$class_model->getClassStudentDataAll($class_con);

                $student_data['student_id']=$id;
                foreach($class_result as $class_val){ 
                    $student_data['class_id']=$class_val['class_id'];
                    $student_data['status']=$class_val['class_student_status'];
                    $student_data['create_at']=time();
                    $student_data['joinmode']=$class_val['joinmode'];
                    if(!$class_model->addClassStudentRecord($student_data)){
                        $this->model->rollback();
                        $this->showjson(404,'操作失败');
                    } 
                    if(!$class_model->removeClassStudentById($class_val['class_id'],$id)){
                        $this->model->rollback();
                        $this->showjson(405,'操作失败');
                    }
                    /*if(!$class_model->updateClassStudentCount($class_val['class_id'])){
                        $this->model->rollback();
                        $this->showjson(404,'操作失败');
                    }*/
                }
             }

            //TODO:给审核通过的添加VIP
            if ( $apply_status == 1) {
                if(!($school_info=get_school_vip_info($result['school_id']))){
                    return array('status'=>'failed','message'=>'学校id参数错误或数据为空');
                }else{
                    if ($school_info['user_auth'] == 3 && time() >= $school_info['auth_start_time'] && time() < $school_info['auth_end_time'] ) {
                        give_new_vip_operation(ROLE_STUDENT,3,$id,SCHOOL_ID);
                    }else{
                        give_new_vip_operation(ROLE_STUDENT,1,$id,SCHOOL_ID);
                    }
                }
            }

            if ($apply_status ==2) { //把学生移除学校
                $parameters = array(
                    'msg' => array(
                        C('SCHOOL_ROOT'),
                        $school_data['school_name'],
                    ),
                    'url' => array( 'type' => 0,)
                );

                A('Home/Message')->addPushUserMessage('SCHOOL_ADMIN_REMOVE_STU', 3,$result['id'], $parameters);//学生
            } else { //同意学生加入学校

                $parameters = array(
                    'msg' => array(
                    ),
                    'url' => array( 'type' => 0,)
                );

                A('Home/Message')->addPushUserMessage('TIANJIA_SCHOOL', 3,$result['id'], $parameters);//学生
            }

            $this->model->commit();
            $this->showjson(200);
        }
    }
    
       
    
    /*
     * 学生详情
     */
    public function studentDetail(){
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions();
        
        $id=getParameter('id','int');
        $result=$this->model->getStudentSchoolData($id,SCHOOL_ID);
        $this->assign('data',$result);         
        $this->display();
    }
    
    
    /*
     * 某个学生的班级列表iframe
     */
    public function student_iframe_class(){
        $class_model=D('Biz_class');
        $id=getParameter('id','int');
//        $filter['grade']=getParameter('grade','int',false);
//        $filter['class']=getParameter('class','int',false);
//        $filter['class_code']=getParameter('class_code','int',false);
        $order=getParameter('order','int',false);
        $order?$order='asc':$order='desc'; 
        
        $school_class=1;
        $condition['auth_student.school_id']=SCHOOL_ID;
        $condition['auth_student.id']=$id;
        $condition['biz_class.class_status']=$school_class;
        $condition['biz_class_student.status']=CLASS_STUDETN_STATUS;
//        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade'];
//        if(!empty($filter['class']))   $condition['biz_class.id']=$filter['class'];
//        if(!empty($filter['class_code']))   $condition['biz_class.class_code']=array('like', '%' . $filter['class_code']. '%');
        
        $grade_data=$class_model->getClassDataBySchool(SCHOOL_ID);      
        $result=$this->model->getClassByStudentData($condition,$order);         
        $this->assign('grade_list',$grade_data);
        $this->assign('list',$result);  
        
        $this->assign('student_id',$id);
        
        $this->display();
    }
    
    
    /*
     * 学生加入班级
     */
    public function studentJoinClass(){
        if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);
        A('School/SchoolAdmin')->check_permissions(true);
        
        $class_model=D('Biz_class');
        $student_id=getParameter('student_id','int');
        $class_id=getParameter('class_id','int');
        $student_result=$this->model->getStudentSchoolData($student_id,SCHOOL_ID);
        if(empty($student_result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{
            if($student_result['school_id']!=SCHOOL_ID){        
                $this->showjson(402,'无权操作此学生!');
            }
            if($student_result['apply_school_status']!=APPLY_SCHOOL_ALLOW){
                $this->showjson(409,'该学生还未通过学校审核!');
            }
        }
        $class_result=$class_model->getClassSchoolData($class_id,SCHOOL_ID);
        if(empty($class_result)){
            $this->showjson(403,ID_NOT_EXISTS_MESSAGE);
        }else{
            if($class_result['school_id']!=SCHOOL_ID){        
                $this->showjson(404,'无权操作此班级!');
            }
        }
        $school_class=1;
        $condition['auth_student.id']=$student_id; 
        $condition['biz_class.class_status']=$school_class;
        $condition['biz_class_student.status']=CLASS_STUDETN_STATUS;
        $condition['biz_class.is_delete'] = 0;
        $result=$class_model->getClassStudentDataAll($condition);       
        if(!empty($result)){
            $this->showjson(405,STUDENT_JOIN_SCHOOL_CLASS_FAILED_MESSAGE);
        } 
        $condition['biz_class.id']=$class_id;
        $result=$class_model->getClassStudentDataAll($condition);       
        if(!empty($result)){
            $this->showjson(408,'该学生已在该班级中');
        }
            
        $class_student_data['class_id']=$class_id;
        $class_student_data['student_id']=$student_id;
        $class_student_data['create_at']=time();
        $class_student_data['status']=CLASS_STUDETN_STATUS;
        $this->model->startTrans();
        if(!$class_model->addClassStudentData($class_student_data)){
            $this->model->rollback();
            $this->showjson(406,COMMON_FAILED_MESSAGE);
        }
        if(!$class_model->deleteClassStudentRecord($class_id,$student_id)){
            $this->model->rollback();
            $this->showjson(407,COMMON_FAILED_MESSAGE);
        }
        /*if(!$class_model->updateClassStudentCount($class_id,true)){
            $this->model->rollback();
            $this->showjson(407,COMMON_FAILED_MESSAGE);
        }*/
        $this->model->commit();
        $this->showjson(200);
    }
    
             
    
    /*
     * 某个学生的家长列表ifrma  
     */
    public function student_iframe_parent(){
        $id=getParameter('id','int');  
        $result=$this->model->getStudentAllParent($id,SCHOOL_ID);     
        $this->assign('list',$result);
        $this->assign('student_id',$id);
        $this->display();
    }

    //TODO:学生增加(添加)家长
    
    /*
     * 学生增加(添加)家长
     */
     public function studentAddParent(){
         if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);
         A('School/SchoolAdmin')->check_permissions(true);
         
         $parent_model=D('Auth_parent');  
         $student_id=getParameter('student_id','int');
         $telephone=getParameter('telephone','str');
         $tel_reg="/^1[34578]{1}\d{9}$/";
         if(!preg_match($tel_reg,$telephone)){
                $this->showjson(401,'手机号格式不正确!');
         }
         $result=$parent_model->getParentInfoByTelephone($telephone);   
         if(empty($result)){ 
             $this->showjson(402,'家长信息不存在!');
         }      
         $parent_id=$result['id'];
         $student_result=$this->model->getStudentInfo($student_id);
         if(empty($student_result)){
             $this->showjson(403,ID_NOT_EXISTS_MESSAGE);
         }else{
             if($student_result['school_id']!=SCHOOL_ID){
                $this->showjson(404,'无权操作此学生!');
            } 
         }
         //拿到关联表
         $contact_result=$this->model->getParentStudents($parent_id,$student_id);      
         if(!empty($contact_result)){
             $this->showjson(407,PARENT_INFO_CONTACT_EXISTS);
         }
         
         $this->model->startTrans();  
         
         $student_parent_data['student_id']=$student_id;
         $student_parent_data['parent_id']=$parent_id;
         $student_parent_data['parent_tel']=$telephone;
         $student_parent_data['create_at']=time(); 
         if(!$this->model->addStudentParentContactData($student_parent_data)){
             $this->model->rollback();
             $this->showjson(406,'修改信息失败!');
         }
         $this->model->commit();
         $this->showjson(200);
     }
     
     //TODO:创建学生
     /*
      * 创建学生
      */
    public function createStudentAccount(){
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions();
        
        if(!empty($_POST)){
            //$vip_config=C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');
            $class_model=D('Biz_class');
            $parent_model=D('Auth_parent');
            $show_next_page_flag=getParameter('next_flag', 'int'); 
            $student_name=getParameter('student_name', 'str');
            $telephone=getParameter('telephone', 'str'); 
            $password=getParameter('password', 'str');
            $sex=getParameter('sex', 'str');
            $birth_date=getParameter('birth_date', 'str'); 
            $class_id=getParameter('class_id', 'int',false);
            if($sex!=SEX_MAN && $sex!=SEX_WOMAN){
                $this->error('性别参数有误!');
            }
            $tel_reg="/^1[34578]{1}\d{9}$/"; 
            if(!preg_match($tel_reg,$telephone)){
                $this->error('手机格式有误!');
            }
            $student_con['auth_student.parent_tel']=$telephone;
            $student_con['auth_student.student_name']=$student_name;
            $student_result=$this->model->getStudentDataAll($student_con);  
            if(!empty($student_result)){
                $this->error('此学生已存在!');
            }
            if($class_id){
                $class_result=$class_model->getClassInfo($class_id);
                if(empty($class_result)){
                    $this->error('班级信息不存在!');
                }else{
                    if($class_result['school_id']!=SCHOOL_ID){
                        $this->error('没有权限操作此班级!');
                    }
                } 
            }
            //判断家长是否存在  之后再修改学生的家长
            $parent_result=$parent_model->getParentInfoByTelephone($telephone);
            $this->model->startTrans();
             if(!empty($parent_result)){
                $student_parent_data['parent_tel']=$telephone;
                $student_parent_data['create_at']=time();
                $student_parent_data['parent_id']=$parent_result['id']; 
                $student_data['parent_id']=$parent_result['id'];
                $student_data['parent_name']=$parent_result['parent_name'];
                if(!($insert_id=$this->model->addStudentParentContactData($student_parent_data))){
                    $this->model->rollback();
                    $this->error('数据入库失败!');
                }
            }
            $student_data['student_name']=$student_name;
            $student_data['password']=sha1($password);
            $student_data['sex']=$sex;
            $student_data['parent_tel']=$telephone;
            $student_data['birth_date']=strtotime($birth_date);
            $student_data['school_id']=SCHOOL_ID;
            $student_data['create_at']=time();
            $student_data['apply_school_status']=APPLY_SCHOOL_ALLOW;
            if(!($student_id=$this->model->addStudentData($student_data))){
                $this->model->rollback();
                $this->error('数据入库失败!');
            }
           /* if($vip_config && $vip_config<=VIP_CONFIG_MAX_NUM){
                give_new_vip_operation(ROLE_STUDENT,$vip_config,$student_id,SCHOOL_ID);
            }*/
            if(!($school_info=get_school_vip_info(SCHOOL_ID))){
                return array('status'=>'failed','message'=>'学校id参数错误或数据为空');
            }else{
                if ($school_info['user_auth'] == 3 && time() >= $school_info['auth_start_time'] && time() < $school_info['auth_end_time'] ) {
                    give_new_vip_operation(ROLE_STUDENT,3,$student_id,SCHOOL_ID);
                }else{
                    give_new_vip_operation(ROLE_STUDENT,1,$student_id,SCHOOL_ID);
                }
            }
            $update_student_parent_data['student_id']=$student_id;
            if(!$this->model->updateStudentParentDta($insert_id,$update_student_parent_data)){
                $this->model->rollback();
                $this->error('数据入库失败!');
            }
            if($class_id){
                $class_student_data['class_id']=$class_id;
                $class_student_data['student_id']=$student_id;
                $class_student_data['create_at']=time();
                $class_student_data['status']=CLASS_STUDETN_STATUS;
                //TODO:这里没有写进去
                if(!$class_model->addClassStudentData($class_student_data)){
                    $this->model->rollback();
                    $this->error('数据入库失败!');
                }
                /*if(!$class_model->updateClassStudentCount($class_id,true)){
                    $this->model->rollback();
                    $this->error('数据入库失败!');
                }*/
            }
            $this->model->commit();
            if($show_next_page_flag){
                $this->redirect(U('Student/studentChooseParent'),array('id'=>$student_id));
            }else{
                $this->redirect(U('Student/studentList'));
            }
        }else{
            $grade_model=D('Dict_grade');
            $where['school_id'] = SCHOOL_ID;
            $where['biz_class.class_status'] = 1;
            $where['biz_class.is_delete'] = 0;
            $grade_result = $grade_model->getGradeListBySchool($where);
            //$grade_result=$grade_model->getGradeList(true);
            $this->assign('grade_list',$grade_result);
            $this->display();
        }
    }
    
    
    /*
     * ajax按照手机号和姓名得到家长信息
     */
    public function getParentInfoByTel(){
        if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $parent_model=D('Auth_parent');
        $telephone=getParameter('telephone','str');
        $parent_name=getParameter('parent_name','str');
        $result=$parent_model->getParentInfoByTelephone($telephone,$parent_name);
        //TODO:判断当前手机号所属的家长ID和学生ID是否在关联表中存在
        $where['auth_student_parent_contact.student_id'] = getParameter('studentId','str');
        $where['auth_student_parent_contact.parent_id'] = $result['id'];
        $data = $this->model->getDataByStudentParentContact($where);
        if(!empty($data)){
            $result['dataStatus'] = 'no';
        }else{
			$result['dataStatus'] = 'yes';
		}
        $this->showjson(200,'',$result);
    }
    
    
    //TODO:学生关联家长
    /*
     * 学生关联家长
     */
    public function studentChooseParent(){
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions();
        if(!empty($_POST)){
            $parent_model=D('Auth_parent');
            $student_id=getParameter('id','int');
            $parent_arr=$_POST['parent'];
            $result=$this->model->getStudentSchoolData($student_id);
            if(empty($result)){
                $this->error('参数有误!');
            }else{
                if($result['school_id']!=SCHOOL_ID){
                    $this->error('无权限操作此学生!');
                }
            }  
            //判断该学生若家长信息超过1条,异常访问
            $parent_info=$this->model->getStudentAllParent($student_id,SCHOOL_ID);
            if(!empty($parent_info)){
                if(count($parent_info)>1){
                    $this->error('访问异常!');
                }
            }
            $data['student_id']=$student_id;
            $this->model->startTrans();
            foreach($parent_arr as $val){ 
                $parent_result=$parent_model->getParentInfo($val);
                if(empty($parent_result)){
                    $this->error('数据异常!');
                }else{
                    $data['parent_tel']=$parent_result['telephone'];
                    $data['parent_id']=$parent_result['id'];
                    $data['create_at']=time();
                    if(!$this->model->addStudentParentContactData($data)){
                        $this->error('入库失败!');
                    }
                }
            }
            $this->model->commit(); 
            $this->redirect(U('Student/studentList'));
        }else{
            $student_id=getParameter('id','int');
            $result=$this->model->getStudentSchoolData($student_id);
            if(empty($result)){
                $this->error('参数有误!');
            }else{
                if($result['school_id']!=SCHOOL_ID){
                    $this->error('无权限操作此学生!');
                }
            }
            $this->assign('student_id',$student_id);
            $this->display('studentAddParent');
        }
    }
     
    
    public function studentModify(){
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions();
        
        if(!empty($_POST)){ 
            $student_id=getParameter('id','int');
            $student_name=getParameter('student_name', 'str');
            $password=getParameter('password', 'str'); 
            $sex=getParameter('sex', 'str');
            $birth_date=getParameter('birth_date', 'str'); 
            
            if($sex!=SEX_MAN && $sex!=SEX_WOMAN){
                $this->error('性别参数有误!');
            }   
            $result=$this->model->getStudentInfo($student_id);  
            if(empty($result)){
                $this->error('学生信息不存在!');
            }else{
                if($result['school_id']!=SCHOOL_ID){
                    $this->error('无权限操作此学生信息!');
                }
            }
            $telephone=$result['parent_tel']; 
            $student_con['auth_student.id']=array('neq',$student_id);
            $student_con['auth_student.parent_tel']=$telephone;
            $student_con['auth_student.student_name']=$student_name;
            $student_result=$this->model->getStudentDataAll($student_con);
            if(!empty($student_result)){
                $this->error('此学生已存在!');
            }
            
            $student_pwd_result=$this->model->getStudentInfo($student_id,$password);
            if(empty($student_pwd_result)){
                $data['password']=sha1($password);
            }   
            $data['student_name']=$student_name; 
            $data['sex']=$sex;
            $data['birth_date']=strtotime($birth_date); 
            if(!$this->model->updateInfoById($data,$student_id)){  
                $this->error('数据入库失败!');
            } 
            $this->redirect(U('Student/studentList'),array('id'=>$student_id));
        }else{
            $id=getParameter('id','int');
            $result=$this->model->getStudentSchoolData($id,SCHOOL_ID);
            if(!empty($result)){  
                if($result['birth_date']==false || $result['birth_date']==null){
                    $result['birth_date']='';
                }else{
                    $result['birth_date']=date('Y-m-d',$result['birth_date']);
                }
            } 
            $this->assign('data',$result);  
            $this->display();
        }
    }
    
    
    
    /*
     * 修改学生详情(含修改手机好多功能)
     */
    /*public function studentModify(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        if(!empty($_POST)){
            $parent_model=D('Auth_parent');
            $student_id=getParameter('id','int');
            $student_name=getParameter('student_name', 'str');
            $password=getParameter('password', 'str');
            $telephone=getParameter('telephone', 'str');
            $sex=getParameter('sex', 'str');
            $birth_date=getParameter('birth_date', 'str'); 
            $tel_reg="/^1[34578]{1}\d{9}$/"; 
            if(!preg_match($tel_reg,$telephone)){
                $this->error('手机格式有误!');
            } 
            if($sex!=SEX_MAN && $sex!=SEX_WOMAN){
                $this->error('性别参数有误!');
            }   
            $result=$this->model->getStudentInfo($student_id);  
            if(empty($result)){
                $this->error('学生信息不存在!');
            }else{
                if($result['school_id']!=SCHOOL_ID){
                    $this->error('无权限操作此学生信息!');
                }
            }
            $student_before_parent_id=$result['parent_id']; 
            $student_con['auth_student.id']=array('neq',$student_id);
            $student_con['auth_student.parent_tel']=$telephone;
            $student_con['auth_student.student_name']=$student_name;
            $student_result=$this->model->getStudentDataAll($student_con);
            if(!empty($student_result)){
                $this->error('此学生已存在');
            }
            
            $student_pwd_result=$this->model->getStudentInfo($student_id,$password);
            if(empty($student_pwd_result)){
                $data['password']=sha1($password);
            } 
            $this->model->startTrans();
            //判断家长是否存在 
            $parent_result=$parent_model->getParentInfoByTelephone($telephone);       
            if(!empty($parent_result)){ 
                $data['parent_id']=$parent_result['id'];
                $data['parent_name']=$parent_result['parent_name'];
                if($student_before_parent_id!=$parent_result['id']){
                    if($student_before_parent_id!=0){
                        if(!$this->model->deleteStudentParentContactData($student_id,$student_before_parent_id)){
                            $this->model->rollback();   
                            $this->error('修改数据失败');
                        }
                    }
                    $student_parent_data['student_id']=$student_id;    
                    $student_parent_data['parent_id']=$parent_result['id'];
                    $student_parent_data['create_at']=time();
                    if(!$this->model->addStudentParentContactData($student_parent_data)){
                        $this->model->rollback();  
                        $this->error('数据入库失败');
                    }
                }
            }else{ 
                //如果之前填了个不存在的家长,就走这里,是否继续删除之前的那个家长?
            }  
            $data['parent_tel']=$telephone; 
            $data['student_name']=$student_name; 
            $data['sex']=$sex;
            $data['birth_date']=strtotime($birth_date); 
            if(!$this->model->updateInfoById($data,$student_id)){ 
                $this->model->rollback();
                $this->error('数据入库失败'); 
            }
            $this->model->commit();
            $this->redirect(U('Student/studentModify'),array('id'=>$student_id));
        }else{
            $id=getParameter('id','int');
            $result=$this->model->getStudentSchoolData($id,SCHOOL_ID);      //var_dump($result);die;
            if(!empty($result)){  
                if($result['birth_date']==false || $result['birth_date']==null){
                    $result['birth_date']='';
                }else{
                    $result['birth_date']=date('Y-m-d',$result['birth_date']);
                }
            }
            $this->assign('data',$result);  
            $this->display();
        }
    }*/
     
    
 
    
    
    
    //得到某个字符串的编码
    public function getStringEncode($string){
        $encode = mb_detect_encoding($string, array('UTF-8','GB2312','GBK','EUC-CN'));  
        if($encode=='EUC-CN' || $encode=='GB2312'){
            $is_utf8=1; 
        }else if($encode=='GBK'){
            $is_utf8=2; 
        }else if($encode=='UTF-8'){
            $is_utf8=0;
        }
        return $is_utf8;
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
    
   
    public function importStudentView(){ 
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions();
        
        $this->assign('school_id',SCHOOL_ID);
        $this->display('studentImport');
    }
    
    //下载学生模板
    public function downloadStudentDemo(){ 
        $csv=new CSV();
        $filepath="Public/csv/school/studentDemo.csv";
        $filename = "学生导入模板";
        //$csv->downloadFile($file);
        $csv->downloadFileCopy($filepath,$filename);
    }
    
    
    /*
     * 下载导入失败的数据
     */
    public function downloadImportErrorData(){
        if (!session('?school')) redirect(U('Login/login'));
          
        $student_name_arr=$_POST['student_name'];
        $parent_tel_arr=$_POST['parent_tel']; 
        $parent_name_arr=$_POST['parent_name']; 
        $sex_arr=$_POST['sex']; 
        $birth_date_arr=$_POST['birth_date'];  
        
        $str="学生姓名,家长手机号,家长姓名,学生性别,出生日期\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($student_name_arr as $key=>$val){
            $student_name=iconv('utf-8','gbk', $val);
            $parent_tel=iconv('utf-8','gbk', $parent_tel_arr[$key]);    
            $parent_name=iconv('utf-8','gbk', $parent_name_arr[$key]); 
            $sex=iconv('utf-8','gbk', $sex_arr[$key]);  
            $birth_date=iconv('utf-8','gbk', $birth_date_arr[$key]); 
            
            $str.=$student_name.",".$parent_tel.",".$parent_name.",".$sex.",".$birth_date."\n";
        } 
            $filename=date('Ymd').rand(0,1000).'学生导入失败信息'.'.csv';
            $csv=new CSV();
            $csv->downloadFileCsv($filename,$str);
    }
    
    
    /*
     * 批量导入学生
     */
    public function importStudent(){
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions(true);
        
        if(empty($_FILES)){ 
            $this->showjson(1001,'文件为空');  //1002文件为空
        } 
        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){
            $this->showjson($result,'文件内容为空');
        }
        $str_encode=$this->getStringEncode($result['result'][0][0]);
        $i_data=$result['result'];        
        $length=$result['length'];
        
        //$vip_config=C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');
        $school_model=D('Dict_schoollist');  
        $parent_model=D('Auth_parent');
         
        $school_id=SCHOOL_ID;
        $school_result=$school_model->getSchoolInfo($school_id);
        if(empty($school_result)){
            $this->showjson(1003,ID_NOT_EXISTS_MESSAGE);
        } 
        $notice_array=array(); 
        $success_array=array();
                
        //如果学生存在就该条数据就跳过
        for($i=1;$i<$length;$i++){
            $data['student_name']=trim($this->encode_string($str_encode,$i_data[$i][0]));
            $data['parent_tel']=trim($this->encode_string($str_encode,$i_data[$i][1]));
            $data['parent_name']=trim($this->encode_string($str_encode,$i_data[$i][2]));
            $data['sex']=trim($this->encode_string($str_encode,$i_data[$i][3]));
            $data['birth_date']=trim($this->encode_string($str_encode,$i_data[$i][4]));
            $data['school_id']=$school_id;  
            $notice=$data; 
            $success=$data;
            $data['password']=sha1(ADMIN_IMPORT_PASSWORD);
            if($data['sex']!=''){
                if($data['sex']!=SEX_MAN && $data['sex']!=SEX_WOMAN){
                    $notice['notice_message']='性别填写不正确';
                    $notice_array[]=$notice;
                    continue;
                }
            }
            if($data['birth_date']!=''){
                $data['birth_date']=strtotime($data['birth_date']);
            }
            //判断手机号 
            if(!preg_match("/^1[34578]{1}\d{9}$/",$data['parent_tel'])){      
                $notice['notice_message']='手机号格式不正确';
                $notice_array[]=$notice;
                continue;
            }
            $result=$this->model->getStudentParentTel($data['student_name'],$data['parent_tel']);
            if(!empty($result)){ 
                $notice['notice_message']=STUDENT_EXISTS;
                $notice_array[]=$notice;
                continue;
            }else{ 
                $parent_result=$parent_model->getParentInfoByTelephone($data['parent_tel']); 
                $this->model->startTrans();
                $parent_exists_flag=0;
                if(!empty($parent_result)){
                    $data['parent_id']=$parent_result['id']; 
                    $student_parent_data['parent_id']=$parent_result['id'];
                    $parent_exists_flag=1;
                }else{
                    if($data['parent_name']!=''){
                        $parent_data['parent_name']=$data['parent_name'];
                        $parent_data['telephone']=$data['parent_tel'];
                        $parent_data['password']=sha1(ADMIN_IMPORT_PASSWORD);
                        if(!($insert_parent_id=$parent_model->addParent($parent_data))){
                            $this->model->rollback();
                            $notice['notice_message']=INSERT_STUDENT_DATA_FAILED;
                            $notice_array[]=$notice;
                            continue;
                        }
                        give_new_vip_operation(ROLE_PARENT,1,$insert_parent_id,$school_id);
                        /*if($vip_config && $vip_config<=VIP_CONFIG_MAX_NUM){
                            give_new_vip_operation(ROLE_PARENT,$vip_config,$insert_parent_id,$school_id);
                        }*/
                        $data['parent_id']=$insert_parent_id; 
                        $student_parent_data['parent_id']=$insert_parent_id;
                        $parent_exists_flag=1;
                        //这里去拿到有多少学生的手机号填的是当前家长手机号,并进行关联
                        if(!$this->studentParentBindByTel($data['parent_tel'],$insert_parent_id)){
                            $this->model->rollback();
                            $notice['notice_message']=INSERT_STUDENT_DATA_FAILED;
                            $notice_array[]=$notice;
                            continue;
                        }
                    }
                }
                $data['create_at']=time();
                $data['apply_school_status']=APPLY_SCHOOL_ALLOW;
                if(!($insert_id=$this->model->addStudentData($data))){
                    $this->model->rollback();
                    $notice['notice_message']=INSERT_STUDENT_DATA_FAILED;
                    $notice_array[]=$notice;
                    continue;
                }
               /* if($vip_config && $vip_config<=VIP_CONFIG_MAX_NUM){
                    give_new_vip_operation(ROLE_STUDENT,$vip_config,$insert_id,$school_id);
                }*/
                if(!($school_info=get_school_vip_info(SCHOOL_ID))){
                    return array('status'=>'failed','message'=>'学校id参数错误或数据为空');
                }else{
                    if ($school_info['user_auth'] == 3 && time() >= $school_info['auth_start_time'] && time() < $school_info['auth_end_time'] ) {
                        give_new_vip_operation(ROLE_STUDENT,3,$insert_id,SCHOOL_ID);
                    }else{
                        give_new_vip_operation(ROLE_STUDENT,1,$insert_id,SCHOOL_ID);
                    }
                }
                if($parent_exists_flag==1){
                    $student_parent_data['student_id']=$insert_id;
                    $student_parent_data['parent_tel']=$data['parent_tel']; 
                    $student_parent_data['create_at']=time();        
                    //插入
                    if(!$this->model->addStudentParentContactData($student_parent_data)){
                        $this->model->rollback();
                        $notice['notice_message']=INSERT_STUDENT_DATA_FAILED;
                        $notice_array[]=$notice;
                        continue;
                    }
                }
                $this->model->commit(); 
                $success_array[]=$success;
                 
                
            }
        }
        $big_data=array();
        $big_data['success']=$success_array;
        $big_data['failed']=$notice_array;
        $this->showjson(200,'success',$big_data);
    }
    
    
    /*
     * 家长和学生进行关联,根据家长手机号
     */
    public function studentParentBindByTel($telephone){
        $parent_model=D('Auth_parent');
        $parent_result=$parent_model->getParentInfoByTelephone($telephone); 
        if(empty($parent_result)){
            return false;
        }else{
            $result_data=$this->model->getStudentByParentTel($telephone);
            $this->model->startTrans();
            $success=1;
            
            $student_parent_data['parent_id']=$parent_result['id'];
            $student_parent_data['parent_tel']=$telephone;  
            foreach($result_data as $student_val){
                $student_parent_data['student_id']=$student_val['id']; 
                $student_parent_data['create_at']=time();  
                //插入
                if(!$this->model->addStudentParentContactData($student_parent_data)){
                    $this->model->rollback(); 
                    $success=0;
                    break;
                }
            }
            if($success){
                return true;
            }else{
                return false;
            }
        }
    }
    
    
    /*
     * 批量导出学生
     */
    public function exportedStudent(){
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions();
        
        if(empty($_POST)){
            $this->error('参数有误');
        }else{
            $condition_arr=I('hid'); 
            $condition['auth_student.id']=array('in',$condition_arr);    
            $condition['auth_student.school_id']=SCHOOL_ID; 
            /*$condition['biz_class_student.status']=2;
            $condition['biz_class.flag']=1;
            $condition['biz_class.class_status']=1;
            $condition['biz_class.is_delete']=0;*/
            $data=$this->model->getStudentDataAllCopy($condition);
//echo M()->getLastSql();die;
            $str="学生姓名,性别,家长手机号,所年级,所在班级,申请加入学校审核,账号状态\n";
            $str=iconv('utf-8','gbk', $str); 
            foreach($data as $val){
                $student_name=iconv('utf-8','gbk', $val['student_name']);
                $sex=iconv('utf-8','gbk', $val['sex']);
                $parent_tel=$val['parent_tel']; 
                $grade=iconv('utf-8','gbk', $val['grade']);
                $class=iconv('utf-8','gbk', $val['class_name']);   
                
                if($val['apply_school_status']==1){
                    $apply_status='已审核';
                }else{
                    $apply_status='待审核';
                } 
                $apply_status=iconv('utf-8','gbk', $apply_status);
                if($val['flag']==1){
                    $account_status='正常';
                }else{
                    $account_status='禁用';
                }
                $account_status=iconv('utf-8','gbk', $account_status);
                $str.=$student_name.",".$sex.",".$parent_tel.",".$grade.",".$class.",".$apply_status.",".$account_status."\n";
            }
            $filename=date('Ymd').rand(0,1000).'student'.'.csv';
            $csv=new CSV();
            //export disable
            $csv->downloadFileCsv($filename,$str);
        }
    }
    
    
    /*
     * 导出全部学生
     */
    public function exportedStudentAll(){
        if (!session('?school')) redirect(U('Login/login'));
        A('School/SchoolAdmin')->check_permissions();
        
        $filter['student_name']=getParameter('student_name','str',false); 
        $filter['parent_tel']=getParameter('telephone','str',false);
        $filter['apply_school_status']=$_GET['apply_school_status'];
        $filter['grade']=getParameter('grade','int',false);
        $filter['class']=getParameter('class','int',false);
        $filter['account_status']=$_GET['account_status'];
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition['auth_student.flag']=array('neq',-1); 
        $condition['auth_student.school_id']=SCHOOL_ID; 
        if(!empty($filter['student_name']))   $condition['auth_student.student_name']=array('like', '%' . $filter['student_name']. '%');   
        if(!empty($filter['parent_tel']))   $condition['auth_student.parent_tel']=array('like', '%' . $filter['parent_tel']. '%'); 
        if($filter['apply_school_status']!='')   $condition['auth_student.apply_school_status']=intval($filter['apply_school_status']);
        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade'];
        if(!empty($filter['class']))   $condition['biz_class.id']=$filter['class']; 
        if($filter['account_status']!='')   $condition['auth_student.flag']=$filter['apply_school_status'];    
        $data=$this->model->getStudentDataAllCopy($condition,$order);
        
        $str="学生姓名,性别,家长手机号,所年级,所在班级,申请加入学校审核,账号状态\n";
        $str=iconv('utf-8','gbk', $str); 
        foreach($data as $val){
            $student_name=iconv('utf-8','gbk', $val['student_name']);
            $sex=iconv('utf-8','gbk', $val['sex']);
            $parent_tel=$val['parent_tel']; 
            $grade=iconv('utf-8','gbk', $val['grade']);
            $class=iconv('utf-8','gbk', $val['class_name']);  
             
            if($val['apply_school_status']==1){
                $apply_status='已审核';
            }else{
                $apply_status='待审核';
            } 
            $apply_status=iconv('utf-8','gbk', $apply_status);
            if($val['flag']==1){
                $account_status='正常';
            }else{
                $account_status='禁用';
            }
            $account_status=iconv('utf-8','gbk', $account_status);
            $str.=$student_name.",".$sex.",".$parent_tel.",".$grade.",".$class.",".$apply_status.",".$account_status."\n";
        }
        $filename=date('Ymd').rand(0,1000).'student'.'.csv';
        $csv=new CSV();
        //export disable
        $csv->downloadFileCsv($filename,$str);
    }
    
    
}