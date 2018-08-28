<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Common\CSV;
  
define('ENABLE_STUDENT_STATUS',1);
define('DISABLE_STUDENT_STATUS',0); 
define('APPLY_SCHOOL_WAIT', 0);
define('STUDENT_EXISTS','该学生信息已存在');
define('INSERT_STUDENT_DATA_FAILED','数据插入失败');
define('GRADE_NOT_EXISTS','年级信息不存在');
define('CLASS_NOT_EXISTS','班级信息不存在'); 
define('PARENT_INFO_NOT_EXISTS','家长信息不存在!');
define('PARENT_INFO_CONTACT_EXISTS','该手机号已是该学生的家长!'); 
define('CLASS_STUDETN_STATUS',2);
define('STUDENT_JOIN_SCHOOL_CLASS_FAILED_MESSAGE','一个学生只能加入一个校内班!');
define('VIP_CONFIG_MAX_NUM',3);
define('DENY_STUDENT_JOIN_CLASS',3);
define('PERSONAL_VIP',4);
define('COMMON_PRIVILEGE',2);

class StudentController extends Controller
{ 

    public $model;
    public $page_size=20; 
            
    public function __construct() {
        parent::__construct();  
        $this->model=D('Auth_student');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }

    //设定vip

    /*
     * 给学生开通VIp
     */
    function giveStudentVip(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);

        $id=getParameter('id','int');
        $vip_type=getParameter('vip_type','int');
        $vip_use_type=getParameter('use_type','int');
        $start_time=getParameter('start_time','str');
        $end_time=getParameter('end_time','str');
        $result=$this->model->getStudentSimpleData($id);
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
                $data['role_id']=3;
                $data['auth_start_time']=$start_time;
                $data['auth_end_time']=$end_time;
                $data['timetype']=$vip_use_type;
            }elseif($vip_type==1){
                $data['auth_id']=COMMON_PRIVILEGE;
                $data['user_id']=$id;
                $data['role_id']=3;
                $data['auth_start_time']='';
                $data['auth_end_time']='';
                $data['timetype']=$vip_use_type;
            }else{
                $this->showjson('405',COMMON_FAILED_MESSAGE);
            }
            $auth_data=$this->model->getStudentPrivilegeInfo($id);
            if(!empty($auth_data)){
                if(!$this->model->updateStudentPrivilege($data,$id)){
                    $this->showjson('406',COMMON_FAILED_MESSAGE);
                }
            }else{
                if(!$this->model->addStudentPrivilege($data)){
                    $this->showjson('407',COMMON_FAILED_MESSAGE);
                }
            }
            $this->showjson(200);
        }
    }
             
    //Home@Admin/test跨目录调用
    /*
     * 学生列表
     */
    public function studentList(){ 
        if (!session('?admin')) redirect(U('Login/login')); 
        
        $school_model=D('Dict_schoollist');
        $grade_model=D('Dict_grade');
        $filter['student_name']=getParameter('student_name','str',false);
        $filter['province']=getParameter('province','int',false);
        $filter['city']=getParameter('city','int',false);
        $filter['district']=getParameter('district','int',false);
        $filter['school']=getParameter('school','int',false);
        $filter['school_name']=getParameter('school_name','str',false);
        $filter['school_code']=$_GET['school_code'];
        $filter['telephone']=getParameter('telephone','str',false);
        $filter['grade']=getParameter('grade','int',false);
        $filter['class']=getParameter('class_name','str',false); 
        $filter['apply_school_status']=$_GET['apply_status'];
        $filter['account_status']=$_GET['status'];
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];   
        $order?$order='asc':$order='desc';      
          
        $condition['auth_student.flag']=array('neq',-1); 
        if(!empty($filter['student_name']))   $condition['auth_student.student_name']=array('like', '%' . $filter['student_name']. '%'); 
        if(!empty($filter['telephone']))   $condition['auth_student.parent_tel']=array('like', '%' . $filter['telephone']. '%');
        if(!empty($filter['province']))   $condition['dict_schoollist.provice_id']= $filter['province'];
        if(!empty($filter['city']))   $condition['dict_schoollist.city_id']= $filter['city'];
        if(!empty($filter['district']))   $condition['dict_schoollist.district_id']= $filter['district'];
        
        if(!empty($filter['school']))   $condition['dict_schoollist.id']= $filter['school'];
        if(!empty($filter['school_name']))   $condition['dict_schoollist.school_name']=array('like', '%' . $filter['school_name']. '%'); 
        if(!empty($filter['school_code']))   $condition['dict_schoollist.school_code']=intval($filter['school_code']);
        if(!empty($filter['parent_tel']))   $condition['auth_student.parent_tel']=array('like', '%' . $filter['parent_tel']. '%'); 
        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade'];
        if(!empty($filter['class']))   $condition['biz_class.name']=array('like', '%' . $filter['class']. '%');
        if($filter['apply_school_status']!='')   $condition['auth_student.apply_school_status']=$filter['apply_school_status'];
        if($filter['account_status']!='')   $condition['auth_student.flag']=$filter['account_status'];
        
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        }
        
        $city_result=array();
        $district_result=array();
        $school_result=array();
        if(!empty($filter['province']))   $city_result=$school_model->getCityByProvince($filter['province']);
        if(!empty($filter['city']))       $district_result=$school_model->getDistrictByCity($filter['city']); 
        if(!empty($filter['district']))   $school_result=$school_model->getSchoolByDistrict($filter['district']);
        
        $province_result=$school_model->getProvince(); 
        $grade_result=$grade_model->getGradeList(true);
        $result=$this->model->getStudentData($condition,$order);

        
        $this->assign('condition_str',$condition_string);
        $this->assign('student_name',$filter['student_name']);
        $this->assign('province',$filter['province']);
        $this->assign('city',$filter['city']);
        $this->assign('district',$filter['district']);
        $this->assign('school',$filter['school']);
        $this->assign('school_name',$filter['school_name']);
        $this->assign('school_code',$filter['school_code']);
        $this->assign('telephone',$filter['telephone']);
        $this->assign('grade',$filter['grade']);
        $this->assign('class_name',$filter['class']);       
        $this->assign('apply_status',$filter['apply_school_status']);       //echo $filter['apply_school_status'];
        $this->assign('account_status',$filter['account_status']);               
        $this->assign('order',$order); 
        
        $this->assign('province_list',$province_result); 
        $this->assign('city_list',$city_result);        
        $this->assign('district_list',$district_result);
        $this->assign('school_list',$school_result);
        $this->assign('grade_list',$grade_result);
        $this->assign('list',$result['data']);
        $this->assign('page',$result['page']);
        $this->assign('other_school_id',OTHER_SCHOOL_ID);   
        $this->display();
    }
    
    
    /*
     * 通过或拒绝加入学校审核
     */
    public function updateApplyStatus(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $class_model=D('Biz_class');
        $id=getParameter('id','int');
        $apply_status=getParameter('status','int');

        $result=$this->model->getStudentInfo($id);
        $school_data = D('Dict_schoollist')->getSchoolInfo($result['school_id']);

        $result=$this->model->getStudentInfo($id);


        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{ 
             if($apply_status!=APPLY_SCHOOL_ALLOW && $apply_status!=APPLY_SCHOOL_DENY){
                 $this->showjson(402,COMMON_FAILED_MESSAGE);
             } 
             
             $this->model->startTrans();
             if(!$this->model->updateApplyStatusManagement($id,$apply_status)){
                 $this->model->rollback();
                 $this->showjson(403,'操作失败');
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
                        $this->showjson(4045,'操作失败');
                    } 
                    /*if(!$class_model->updateClassStudentCount($class_val['class_id'])){
                        $this->model->rollback();
                        $this->showjson(404,'操作失败');
                    }*/
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
     * 启用或禁用状态
     */
    public function updateStudentStatus(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $id=getParameter('id','int');
        $result=$this->model->getStudentInfo($id);      
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{
            if($result['flag']){
                $status=$this->model->updateEnableStatus($id,DISABLE_STUDENT_STATUS);
            }else{
                $status=$this->model->updateEnableStatus($id,ENABLE_STUDENT_STATUS);
            }
            if($status){
                $this->showjson(200);
            }else{
                $this->showjson(402,COMMON_FAILED_MESSAGE);
            }
        }
    }
    
    
    /*
     * 删除学生(修改状态)
     */
    public function deleteStudent(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $id=getParameter('id','int');
        $result=$this->model->getStudentInfo($id);  
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{  
            $data['flag']=-1;
            if($status=$this->model->updateInfoById($data,$id)){
                $this->showjson(200);
            }else{
                $this->showjson(402,COMMON_FAILED_MESSAGE);
            }
        }
    }
    
    
    /*
     * 移除家长
     */
    public function removeStudentParentContact(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $parent_model=D('Auth_parent');
        $id=getParameter('id','int');
        $parent_id=getParameter('parent_id','int');
        $result=$this->model->getStudentInfo($id);
        $parent_result=$parent_model->getParentSimpleData($parent_id);
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{
            if(empty($parent_result)){
                $this->showjson(402,ID_NOT_EXISTS_MESSAGE);
            }
            $status=$this->model->deleteStudentParentContactData($id,$parent_id);
            if($status){
                $this->showjson(200);
            }else{
                $this->showjson(403,COMMON_FAILED_MESSAGE);
            }
        }
    }
    
    
    /*
     * 学生详情
     */
    public function studentDetail(){
        if (!session('?admin')) redirect(U('Login/login')); 
        
        $id=getParameter('id','int');
        $result=$this->model->getStudentSchoolData($id);        
        $this->assign('data',$result);
        $this->display();
    }
    
    
    /*
     * 某个学生的班级列表iframe
     */
    public function student_iframe_class(){
        $class_model=D('Biz_class');
        $school_model=D('Dict_schoollist');
        $grade_model=D('Dict_grade');
        $id=getParameter('id','int');
//        $filter['grade']=getParameter('grade','int',false);
//        $filter['class']=getParameter('class','int',false);
//        $filter['class_status']=getParameter('class_status','int',false);
//        $order=getParameter('order','int',false);
//        $order?$order='asc':$order='desc'; 
        
        $condition['auth_student.id']=$id;
        $condition['biz_class.is_delete']=0;
        $condition['biz_class_student.status']=2;
//        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade'];
//        if(!empty($filter['class']))   $condition['biz_class.id']=$filter['class'];
//        if(!empty($filter['class_status']))   $condition['biz_class.class_status']=$filter['class_status'];
        $student_result=$this->model->getStudentSchoolData($id);        
        if(!empty($student_result)){      
            $result=$this->model->getClassByStudentData($condition,$order);        
        }
        $province_result=$school_model->getProvince();
        $grade_result=$grade_model->getGradeList(true); 
        $this->assign('grade_list',$grade_result);
        $this->assign('province_list',$province_result);
        $this->assign('student_data',$student_result);  
        $this->assign('list',$result); 
        $this->assign('student_id',$id);
        
        $this->display();
    }
    
    
    /*
     * 学生加入班级     
     */
    public function studentJoinClass(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $class_model=D('Biz_class');
        $student_id=getParameter('student_id','int');
        $class_id=getParameter('class_id','int');
        $class_type=getParameter('class_type','int');
        $class_result=$class_model->getClassInfo($class_id);

        $class_result_info=D('Biz_class')->getClassAndGradeInfo($class_id);

        if(empty($class_result)){
             $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }
        $student_result=$this->model->getStudentSchoolData($student_id);
        if(empty($student_result)){
             $this->showjson(402,ID_NOT_EXISTS_MESSAGE);
        }
        if($student_result['apply_school_status']!=APPLY_SCHOOL_ALLOW){
            $this->showjson(407,'该学生还未通过学校审核!');
        }
        $condition['auth_student.id']=$student_id;  
        if($class_type==1){
            $school_class=1; 
            $condition['biz_class_student.status']=CLASS_STUDETN_STATUS;
            $condition['biz_class.class_status']=$school_class;  
            
        }else{
            $personal_class=2;
            $condition['biz_class.class_status']=$personal_class; 
            $condition['biz_class.id']=$class_id;  
        }
        $result=$class_model->getClassStudentDataAll($condition);
        if(!empty($result)){
            if($class_type==1){
                $this->showjson(403,STUDENT_JOIN_SCHOOL_CLASS_FAILED_MESSAGE);
                $condition['biz_class.id']=$class_id;
                $result=$class_model->getClassStudentDataAll($condition);
                if(!empty($result)){
                    $this->showjson(406,'该学生已在该班级中!');
                }
            }else{
                $this->showjson(403,'该学生已加过该班级!');
            }
        }    
        $class_student_data['class_id']=$class_id;
        $class_student_data['student_id']=$student_id;
        $class_student_data['create_at']=time();
        $class_student_data['status']=CLASS_STUDETN_STATUS;
        $this->model->startTrans();
        if(!$class_model->addClassStudentData($class_student_data)){
            $this->model->rollback();
            $this->showjson(404,COMMON_FAILED_MESSAGE);
        }
        if(!$class_model->deleteClassStudentRecord($class_id,$student_id)){
            $this->model->rollback();
            $this->showjson(405,COMMON_FAILED_MESSAGE);
        }
        /*if(!$class_model->updateClassStudentCount($class_id,true)){
            $this->model->rollback();
            $this->showjson(405,COMMON_FAILED_MESSAGE);
        }*/

        $parametersstu = array(
            'msg' => array(
                C('ADMIN_ROOT'),
                $class_result_info['grade'],
                $class_result_info['name'],
            ),
            'url' => array( 'type' => 0,)
        );

        A('Home/Message')->addPushUserMessage('CLASSADDSENDSTUDENT', 3,$student_id , $parametersstu);

        $this->model->commit();
        $this->showjson(200);
    }
    
    
    /*
     * 某个学生的家长列表ifrma  
     */
    public function student_iframe_parent(){
        $id=getParameter('id','int');  
        $result=$this->model->getStudentAllParent($id);
        $this->assign('list',$result);
        $this->assign('student_id',$id);
        
        $this->display();
    }
    
    
    /*
     * 学生增加(添加)家长
     */
     public function studentAddParent(){
         if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
         
         $parent_model=D('Auth_parent');  
         $student_id=getParameter('student_id','int');
         $telephone=getParameter('telephone','str');
         $tel_reg="/^1[34578]{1}\d{9}$/";
         if(!preg_match($tel_reg,$telephone)){
                $this->showjson(401,'手机号格式不正确!');
         }
         $result=$parent_model->getParentInfoByTelephone($telephone);   
         if(empty($result)){ 
             $this->showjson(402,PARENT_INFO_NOT_EXISTS);
         }      
         $parent_id=$result['id'];
         $student_result=$this->model->getStudentInfo($student_id);
         if(empty($student_result)){
             $this->showjson(403,'学生信息不存在!');
         } 
         //判断家长和学生关系是否已经存在 
         $contact_result=$this->model->getStudentAllParent($student_id,0,$parent_id); 
         if(!empty($contact_result)){
             $this->model->rollback();
            $this->showjson(406,PARENT_INFO_CONTACT_EXISTS);
         }
         
         $this->model->startTrans();  
          
         $student_parent_data['student_id']=$student_id;
         $student_parent_data['parent_id']=$parent_id;
         $student_parent_data['parent_tel']=$telephone;
         $student_parent_data['create_at']=time(); 
         if(!$this->model->addStudentParentContactData($student_parent_data)){
             $this->model->rollback();
             $this->showjson(407,COMMON_FAILED_MESSAGE);
         }
         $this->model->commit();
         $this->showjson(200);
     }
     
     
     /*
      * 创建学生
      */
    public function createStudentAccount(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        if(!empty($_POST)){
            $vip_config=C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');
            $school_model=D('Dict_schoollist');
            $parent_model=D('Auth_parent');
            $show_next_page_flag=getParameter('next_flag', 'int'); 
            $student_name=getParameter('student_name', 'str');
            $sex=getParameter('sex', 'str'); 
            $parent_tel=getParameter('parent_tel', 'str');  
            $password=getParameter('password', 'str'); 
            $birth_date=getParameter('birth_date', 'str');  
            $school_id=getParameter('school', 'int'); 
            
            $school_result=$school_model->getSchoolInfo($school_id);
            if(empty($school_result)){
                $this->error('学校不存在!');
            }
            if($sex!=SEX_MAN && $sex!=SEX_WOMAN){
                $this->error('性别参数有误!');
            }
            $tel_reg="/^1[34578]{1}\d{9}$/"; 
            if(!preg_match($tel_reg,$parent_tel)){
                $this->error('手机格式有误!');
            } 
            $student_con['auth_student.parent_tel']=$parent_tel;
            $student_con['auth_student.student_name']=$student_name;
            $student_result=$this->model->getStudentDataAll($student_con);  
            if(!empty($student_result)){
                $this->error('此学生已存在!');
            }
            $parent_result=$parent_model->getParentInfoByTelephone($parent_tel);
            $this->model->startTrans();
             if(!empty($parent_result)){
                $student_parent_data['parent_tel']=$parent_tel;
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
            $student_data['parent_tel']=$parent_tel;
            $student_data['birth_date']=strtotime($birth_date);
            $student_data['school_id']=$school_id;
            $student_data['create_at']=time();
            $student_data['apply_school_status']=APPLY_SCHOOL_ALLOW;
            if(!($student_id=$this->model->addStudentData($student_data))){
                $this->model->rollback();
                $this->error('数据入库失败!');
            }
            if($vip_config && $vip_config<=VIP_CONFIG_MAX_NUM){ 
                give_new_vip_operation(ROLE_STUDENT,$vip_config,$student_id,$school_id);
            }
            $update_student_parent_data['student_id']=$student_id;
            if(!$this->model->updateStudentParentDta($insert_id,$update_student_parent_data)){
                $this->model->rollback();
                $this->error('数据入库失败!');
            }
            $this->model->commit();
            if($show_next_page_flag){
                $this->redirect(U('Student/studentChooseClass'),array('id'=>$student_id));
            }else{
                $this->redirect(U('Student/studentList'));
            }
        }else{
            $school_model=D('Dict_schoollist');
            $province_result=$school_model->getProvince(); 
            $this->assign('province_list',$province_result);
            $this->display();
        }
    }
    
    
    /*
     * 学生选择班级
     */
    public function studentChooseClass(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        if(!empty($_POST)){ 
            $class_model=D('Biz_class');
            $next_flag=getParameter('next_flag','int');
            $student_id=getParameter('id','int');
            $class_arr=$_POST['class'];
            $result=$this->model->getStudentSchoolData($student_id);    
            if(empty($result)){
                $this->error('参数错误!'); 
            }
            //判断学生加入过班级没有,若有就是异常访问
            $exists_class_con['auth_student.id']=$student_id;
            $result=$this->model->getClassByStudentData($exists_class_con);         
            if(!empty($result)){
                $this->error('异常访问!'); 
            }
            
            $this->model->startTrans();
            //这里判断学生是否有班级
            for($i=0;$i<count($class_arr);$i++){
                $school_grade_class=explode('_',$class_arr[$i]); 
                if(count($school_grade_class)!=3){
                    $this->error('参数错误!');
                }
                $class_id=$school_grade_class[2];
                $grade_id=$school_grade_class[1];
                $condition['auth_student.id']=$grade_id;
                $condition['dict_grade.id']=$grade_id;
                $condition['biz_class.id']=$class_id;
                $result=$this->model->getClassByStudentData($condition);    
                if(!empty($result)){
                    $this->model->rollback();
                    $this->error('您已经加入过该班级!');
                }
                $class_result=$class_model->getClassInfo($class_id);
                if(empty($class_result)){
                    $this->model->rollback();
                    $this->error('参数有误!');
                }else{
                    if($class_result['grade_id']!=$grade_id){
                        $this->model->rollback();
                        $this->error('参数有误!');
                    }
                }
                $class_student_data['class_id']=$class_id;
                $class_student_data['student_id']=$student_id;
                $class_student_data['create_at']=time();
                $class_student_data['status']=CLASS_STUDETN_STATUS;
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
            if($next_flag){
                $this->redirect(U('Student/studentChooseParent'),array('id'=>$student_id));
            }else{
                $this->redirect(U('Student/studentList'));
            }
        }else{
            $school_model=D('Dict_schoollist');
            $grade_model=D('Dict_grade');
            $id=getParameter('id','int'); 
            $result=$this->model->getStudentSchoolData($id);    
            if(empty($result)){
                $this->error('参数错误'); 
            }
            $province_result=$school_model->getProvince(); 
            $grade_result=$grade_model->getGradeList(true);
            
            $this->assign('data',$result);
            $this->assign('province_list',$province_result);
            $this->assign('grade_list',$grade_result);
            $this->display('studentAddClass');
        }
    } 
    
    
    /*
     * ajax按照手机号和姓名得到家长信息
     */
    public function getParentInfoByTel(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $parent_model=D('Auth_parent');
        $telephone=getParameter('telephone','str');
        $parent_name=getParameter('parent_name','str');
        $result=$parent_model->getParentInfoByTelephone($telephone,$parent_name);
        $this->showjson(200,'',$result);
    }
    
    
    /*
     * 学生选择家长
     */
    public function studentChooseParent(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        if(!empty($_POST)){
            
            $parent_model=D('Auth_parent');
            $student_id=getParameter('id','int');
            $parent_arr=$_POST['parent'];  
            $result=$this->model->getStudentSchoolData($student_id);
            if(empty($result)){
                $this->error('参数有误!');
            } 
            //判断该学生若家长信息超过1条,异常访问
            $parent_info=$this->model->getStudentAllParent($student_id);      
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
                $this->error('参数错误'); 
            }
            $this->assign('student_id',$student_id);
            $this->display('studentAddParent');
        }
    }
    
    
    /*
     * 修改学生详情
     */
    public function studentModify(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        if(!empty($_POST)){ 
            $class_model=D('Biz_class');
            $school_model=D('Dict_schoollist');
            $student_id=getParameter('id','int');
            $school_id=getParameter('school', 'str');
            $student_name=getParameter('student_name', 'str');
            $password=getParameter('password', 'str');
            $sex=getParameter('sex', 'str');
            $birth_date=getParameter('birth_date', 'str'); 
            $account_status=getParameter('status', 'int');  
            if($sex!=SEX_MAN && $sex!=SEX_WOMAN){
                $this->error('性别参数有误!');
            }
            $result=$this->model->getStudentInfo($student_id);  
            if(empty($result)){
                $this->error('学生信息不存在!');
            } 
            if($account_status!=ENABLE_STUDENT_STATUS && $account_status!=DISABLE_STUDENT_STATUS){
                $this->error('参数错误!');
            }
            $school_result=$school_model->getSchoolSimpleData($school_id);
            if(empty($school_result)){  
                $this->error('参数错误');
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
            $data['school_id']=$school_id;
            $data['flag']=$account_status; 
            
            $this->model->startTrans();
            if(!$this->model->updateInfoById($data,$student_id)){  
                $this->model->rollback();
                $this->error('数据入库失败!');
            }
            if($result['school_id']!=$school_id){
                //这里删除之前学校的校建班级
                $class_con['biz_class_student.student_id']=$student_id; 
                $class_result=$class_model->getClassStudentDataAll($class_con);
                foreach($class_result as $class_val){
                    if(!$class_model->removeClassStudentById($class_val['class_id'],$student_id)){
                        $this->model->rollback();
                        $this->error('数据入库失败!');
                    }
                    /*if(!$class_model->updateClassStudentCount($class_val['class_id'])){
                        $this->model->rollback();
                        $this->error('数据入库失败!');
                    }*/
                }
            }
            $this->model->commit();  
            $this->redirect(U('Student/studentModify'),array('id'=>$student_id));
        }else{
            $school_model=D('Dict_schoollist');
            $id=getParameter('id','int');
            $result=$this->model->getStudentSchoolData($id);        
            if(!empty($result)){  
                if($result['birth_date']==false || $result['birth_date']==null){
                    $result['birth_date']='';
                }else{
                    $result['birth_date']=date('Y-m-d',$result['birth_date']);
                }
            }
            $province_result=array();
            $city_result=array();
            $district_result=array();
            $school_result=array(); 
            if(!empty($result)){
                $province_result=$school_model->getProvince(); 
                $city_result=$school_model->getCityByProvince($result['province_id']);
                $district_result=$school_model->getDistrictByCity($result['city_id']); 
                $school_result=$school_model->getSchoolByDistrict($result['district_id']);
            }  
            $this->assign('province_list',$province_result);
            $this->assign('city_list',$city_result);        
            $this->assign('district_list',$district_result);
            $this->assign('school_list',$school_result);
            $this->assign('data',$result);              
            $this->assign('other_school_id',OTHER_SCHOOL_ID);     
            $this->display();
        }
    }
    
    
    public function importStudentView(){    
        $school_model=D('Dict_schoollist');
        $province_result=$school_model->getProvince(); 
        $this->assign('province_list',$province_result);
        $this->display('studentImport');
    } 
    
    
    /*
     * 导入页面搜索学校 
     */
    public function searchSchool(){
        $filter['province']=getParameter('province','int',false);
        $filter['city']=getParameter('city','int',false);
        $filter['district']=getParameter('district','int',false);
        $filter['school_name']=getParameter('school_name','int',false);
        
        $condition=array();
        if(!empty($filter['province']))   $condition['dict_schoollist.provice_id']=$filter['province'];
        if(!empty($filter['city']))   $condition['dict_schoollist.city_id']=$filter['city'];
        if(!empty($filter['district']))   $condition['dict_schoollist.district_id']=$filter['district'];
        if(!empty($filter['school_name']))   $condition['dict_schoollist.school_name']=array('like'.'%'.$filter['school_name'].'%');
        $school_model=D('Dict_schoollist');
        $result=$school_model->getSchool($condition);
        $this->showjson(200,'',$result);
    }
    
    
    
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
     
    
    //下载学生模板
    public function downloadStudentDemo(){ 
        $csv=new CSV(); 
        $file="Public/csv/admin/studentDemo.csv";
        //export disable
        //$csv->downloadFile($file);
    }
    
    
    /*
     * 下载导入失败的数据
     */
    public function downloadImportErrorData(){
        if (!session('?admin')) redirect(U('Login/login'));
          
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
        if(empty($_FILES)){ 
            $this->showjson(1001,'文件为空');  
        } 
        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){
            $this->showjson(1002,'文件内容为空');
        }
        $str_encode=$this->getStringEncode($result['result'][0][0]);
        $i_data=$result['result'];        
        $length=$result['length'];
        
        
        $school_model=D('Dict_schoollist');  
        $parent_model=D('Auth_parent');
        
        //$class_type=getParameter('clss_type','int');
        $school_id=getParameter('school_id','int');
        $school_result=$school_model->getSchoolInfo($school_id);
        if(empty($school_result)){
            $this->showjson(1003,'学校参数错误');
        } 
        $notice_array=array(); 
        $success_array=array();
        $vip_config=C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');
        
        //如果学生存在就该条数据就跳过
        for($i=1;$i<$length;$i++){
            $data['student_name']=trim($this->encode_string($str_encode,$i_data[$i][0]));
            $data['parent_tel']=trim($this->encode_string($str_encode,$i_data[$i][1]));
            $data['parent_name']=trim($this->encode_string($str_encode,$i_data[$i][2]));
            $data['sex']=trim($this->encode_string($str_encode,$i_data[$i][3]));
            $data['birth_date']= trim($this->encode_string($str_encode,$i_data[$i][4]));
            $data['school_id']=$school_id;
            $data['password']=sha1(ADMIN_IMPORT_PASSWORD);
            
            $notice=$data; 
            $success=$data;
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
                        if($vip_config && $vip_config<=VIP_CONFIG_MAX_NUM){ 
                            give_new_vip_operation(ROLE_PARENT,$vip_config,$insert_parent_id,$school_id);
                        }
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
                
                if(!($insert_id=$this->model->addStudentData($data))){
                    $this->model->rollback();
                    $notice['notice_message']=INSERT_STUDENT_DATA_FAILED;
                    $notice_array[]=$notice;
                    continue;
                }
                if($vip_config && $vip_config<=VIP_CONFIG_MAX_NUM){ 
                    give_new_vip_operation(ROLE_STUDENT,$vip_config,$insert_id,$school_id);
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
        if (!session('?admin')) redirect(U('Login/login'));
        
        if(empty($_POST)){
            $this->error('参数有误');
        }else{
            $condition_arr=I('hid'); 
            $condition['auth_student.id']=array('in',$condition_arr);    
            $data=$this->model->getStudentDataAll($condition);

            $str="学生姓名,注册时间,性别,家长手机号,所属学校,所在年级,所在班级,加入学校审核状态,账号状态\n";
            $str=iconv('utf-8','gbk', $str);
            foreach($data as $val){
                $student_name=iconv('utf-8','gbk', $val['student_name']);
                $sex=iconv('utf-8','gbk', $val['sex']);
                $regTime = date("Y-m-d", $val['create_at']);
                $parent_tel=$val['parent_tel']; 
                $school_name=iconv('utf-8','gbk', $val['school_name']);
                $grade=iconv('utf-8','gbk', $val['grade']);
                $class=iconv('utf-8','gbk', $val['class_name']);  
                
                
                $status=iconv('utf-8','gbk', $status);
                if($val['apply_school_status']==1){
                    $apply_status='同意';
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
                $str.=$student_name.','.$regTime.",".$sex.",".$parent_tel.",".$school_name.",".$grade.",".$class.",".$apply_status.",".$account_status."\n";
            }
            $filename=date('Ymd').rand(0,1000).'student'.'.csv';
            $csv=new CSV();
            //export disable
            //$csv->downloadFileCsv($filename,$str);
        }
    }
    
    
    /*
     * 导出全部学生
     */
    public function exportedStudentAll(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        $filter['student_name']=getParameter('student_name','str',false);
        $filter['province']=getParameter('province','int',false);
        $filter['city']=getParameter('city','int',false);
        $filter['district']=getParameter('district','int',false);
        $filter['school']=getParameter('school','int',false);
        $filter['school_code']=$_GET['school_code'];
        $filter['telephone']=getParameter('telephone','str',false);
        $filter['grade']=getParameter('grade','int',false);
        $filter['class']=getParameter('class_name','str',false); 
        $filter['apply_school_status']=$_GET['apply_status'];
        $filter['account_status']=$_GET['status'];
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];   
        $order?$order='asc':$order='desc';      
          
        
        $condition['auth_student.flag']=array('neq',-1); 
        if(!empty($filter['student_name']))   $condition['auth_student.student_name']=array('like', '%' . $filter['student_name']. '%'); 
        if(!empty($filter['school']))   $condition['dict_schoollist.id']= $filter['school'];
        if(!empty($filter['school_code']))   $condition['dict_schoollist.school_code']=intval($filter['school_code']);
        if(!empty($filter['parent_tel']))   $condition['auth_student.parent_tel']=array('like', '%' . $filter['parent_tel']. '%'); 
        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade'];
        if(!empty($filter['class']))   $condition['biz_class.name']=array('like', '%' . $filter['class']. '%');
        if(!empty($filter['apply_school_status']))   $condition['auth_student.apply_school_status']=$filter['apply_school_status'];
        if(!empty($filter['account_status']))   $condition['auth_student.flag']=$filter['account_status'];
        $data=$this->model->getStudentDataAll($condition,$order);               //var_dump($data); die;
        
        $str="学生姓名,注册时间,性别,家长手机号,所属学校,所在年级,所在班级,加入学校审核状态,账号状态\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($data as $val){
            $student_name=iconv('utf-8','gbk', $val['student_name']);
            $regTime = date("Y-m-d", $val['create_at']);
            $sex=iconv('utf-8','gbk', $val['sex']);
            $parent_tel=$val['parent_tel']; 
            $school_name=iconv('utf-8','gbk', $val['school_name']);
            $grade=iconv('utf-8','gbk', $val['grade']);
            $class=iconv('utf-8','gbk', $val['class_name']); 
          
            $status=iconv('utf-8','gbk', $status);
            if($val['apply_school_status']==1){
                $apply_status='同意';
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
            $str.=$student_name.','.$regTime.",".$sex.",".$parent_tel.",".$school_name.",".$grade.",".$class.",".$apply_status.",".$account_status."\n";
        }
        $filename=date('Ymd').rand(0,1000).'student'.'.csv';
        $csv=new CSV();
        //export disable
        //$csv->downloadFileCsv($filename,$str);
    }
}