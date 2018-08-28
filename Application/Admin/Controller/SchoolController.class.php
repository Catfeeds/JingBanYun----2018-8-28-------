<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Common\CSV;

define('SCHOOL_ADMIN_ROLE',3);
define('PASS_SCHOOL_STATUS',1);  
define('ENABLE_SCHOOL_STATUS',1);
define('DISABLE_SCHOOL_STATUS',0); 
define('CREATE_SCHOOL_FAILED','抱歉,由于网络原因创建学校操作失败!');
define('PROVINCE_LEVEL',1);
define('CITY_LEVEL',2);
define('DISTRICT_LEVEL',3);
define('CODE',99);          //这个为区县编码不够6位就补位 
define('PRIVILEGE_GROUP_VIP',3); 
define('SCHOOL_CLASS_CODE_PRIFIX','00');
define('INDIVIDUAL_CLASS_CODE_PRIFIX','20');
define('PERSONAL_CLASS',2);
define('SCHOOL_CLASS',1);
define('PERSONAL_CLASS_STR','自建班');
define('SCHOOL_CLASS_STR','校建班');

class SchoolController extends Controller
{  
    public $model='';
    public $page_size=20; 
    
    public function __construct() {
        parent::__construct();
        $this->model=D('Dict_schoollist');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }
    
   /*
    * 学校列表      
    */
    public function schoolList(){  
        if (!session('?admin')) redirect(U('Login/login')); 
        
        $school_model=D('Dict_schoollist'); 
        $filter['province']=getParameter('province','int',false);
        $filter['city']=getParameter('city','int',false);
        $filter['district']=getParameter('district','int',false);
        $filter['school']=getParameter('school','int',false); 
        $filter['school_name']=getParameter('school_name','str',false);
        $filter['school_code']=getParameter('school_code','str',false);
        $filter['flag']=$_GET['school_status'];
        $filter['administartor_status']=$_GET['is_create_administartor'];
        $filter['privilege_type']=getParameter('privilege_type','int',false);
        $filter['school_category']=$_GET['school_category'];
        $filter['order']=getParameter('order','int',false);  
        $order=$filter['order'];
        $order?$order='asc':$order='desc'; 
        
        $condition=array();
        if(!empty($filter['school']))   $condition['dict_schoollist.id']=$filter['school'];
        if(!empty($filter['school_name']))   $condition['dict_schoollist.school_name']=array('like', '%' . $filter['school_name']. '%');
        if(!empty($filter['school_code']))   $condition['school_code']=array('like', '%' . $filter['school_code']. '%');
        if($filter['flag']!='')   $condition['dict_schoollist.flag']=intval($filter['flag']);
        if($filter['administartor_status']!='')   $condition['is_create_administartor']=intval($filter['administartor_status']);
        if(!empty($filter['province']))   $condition['provice_id']=$filter['province'];
        if(!empty($filter['city']))   $condition['city_id']=$filter['city'];
        if(!empty($filter['district']))   $condition['district_id']=$filter['district'];
        if($filter['school_category']!='')   $condition['school_category']=intval($filter['school_category']);
        
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        }  
        
        $having='';
        if(!empty($filter['privilege_type']))  $having='permissions_status='.$filter['privilege_type'];
          
        $city_result=array();
        $district_result=array();
        $school_result=array();
        if(!empty($filter['province']))   $city_result=$school_model->getCityByProvince($filter['province']);
        if(!empty($filter['city']))       $district_result=$school_model->getDistrictByCity($filter['city']); 
        if(!empty($filter['district']))   $school_result=$school_model->getSchoolByDistrict($filter['district']);
         
        $school_category=C('SCHOOL_CATEGORY');   
        $province_result=$school_model->getProvince();  
        $result=$this->model->getSchoolData($condition,$order,$having); 

        $this->assign('condition_str',$condition_string);
        $this->assign('province',$filter['province']);
        $this->assign('city',$filter['city']);
        $this->assign('district',$filter['district']);
        $this->assign('school',$filter['school']);
        $this->assign('school_name',$filter['school_name']);
        $this->assign('school_code',$filter['school_code']);
        $this->assign('flag',$filter['flag']);
        $this->assign('administartor_status',$filter['administartor_status']);
        $this->assign('school_cat',$filter['school_category']);
        $this->assign('privilege_type',$filter['privilege_type']);
        $this->assign('order',$order);
        
        $this->assign('school_category',$school_category);
        $this->assign('province_list',$province_result); 
        $this->assign('city_list',$city_result);        
        $this->assign('district_list',$district_result);
        $this->assign('school_list',$school_result);
        $this->assign('list',$result['data']);         
        $this->assign('page',$result['page']);
        $this->display();
    }
    
     
    /*
     * 开通学校管理员
     */
    function createSchoolAdmin(){            
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $admin_model=D('Auth_admin');
        $school_id=getParameter('id','int'); 
        $admin_realname=getParameter('real_name','str'); 
        $admin_telephone=getParameter('telephone','str'); 
        $admin_name=getParameter('account','str'); 
        $admin_password=getParameter('password','str');  
        $result=$this->model->getSchoolInfo($school_id);   
        if(empty($result)){
            $this->showjson('401',ID_NOT_EXISTS_MESSAGE);
        }else{  
            if($result['is_create_administartor']==1){
                $this->showjson('402',COMMON_FAILED_MESSAGE);
            }
            $tel_reg="/^1[34578]{1}\d{9}$/"; 
            if(!preg_match($tel_reg,$admin_telephone)){    
                $this->showjson('403',COMMON_FAILED_MESSAGE);
            }
            $data['real_name']=$admin_realname;
            $data['telephone']=$admin_telephone;
            $data['name']=$admin_name;
            $data['password']=sha1($admin_password); 
            $data['school_id']=$school_id;
            $data['create_at']=time();
            $data['role']=SCHOOL_ADMIN_ROLE;
            if(!$admin_model->addAdminData($data)){
                $this->showjson('404',COMMON_FAILED_MESSAGE);
            } 
            $update_data['is_create_administartor']=PASS_SCHOOL_STATUS;
            if(!$this->model->updateSchoolData($school_id,$update_data)){
                $this->showjson('405',COMMON_FAILED_MESSAGE);
            }

            $allteacher = $this->model->getAllSchoolLeveTeacher( $school_id );
            $parameters = array(
                'msg' => array(
                    $result['school_name'],
                ),
                'url' => array( 'type' => 0 )
            );
            A('Home/Message')->addPushUserMessage('ADMIN_SEND_SCHOOL_TEACHER', 2, implode(',', $allteacher) , $parameters);

            $this->showjson(200);
        }
    } 
    
    
    /*
     * 给学校开通VIp
     */
    function giveSchoolVip(){
        $vipdata = array(
            'user_id' => 0,
            'role_id' => 0,
            'auth_id' => 4,
            'auth_start_time' => time(),
            'auth_end_time' => time()+3600*24*30*3,
            'timetype' => 1
        );
        $auth_type_use = D('Account_auths');
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        $id=getParameter('id','int');
        $quxiao=getParameter('quxiao','int');
        //根据学校id查询所有的用户
        $school['dict_schoollist.id'] = $id;
        $allTeacherBySchool = $this->model
            ->field('auth_teacher.id teacherid')
            ->join('auth_teacher ON auth_teacher.school_id = dict_schoollist.id and auth_teacher.apply_school_status = 1')
            ->where($school)
            ->select();
        $allStudentBySchool = $this->model
            ->field('auth_student.id studentid')
            ->join('auth_student ON auth_student.school_id = dict_schoollist.id and auth_student.apply_school_status = 1')
            ->where($school)
            ->select();
        $allPersonBySchool = array_merge($allStudentBySchool,$allTeacherBySchool);
        M('account_user_and_auth')->startTrans();

//取消VIP
        if($quxiao==1) {
            //TODO:取消学校VIP后取消此学校下的所有人员的团体VIP权限，全部改成普通VIP权限
            //遍历的方式逐次修改权限
            $data['auth_end_time'] = time()-3600;
            if(!$this->model->updateSchoolData($id,$data)){
                M('account_user_and_auth')->rollback();
                $this->showjson('406',COMMON_FAILED_MESSAGE);
            }
            M('account_user_and_auth')->commit();
            foreach ($allPersonBySchool as $item){
                if(key($item) == 'teacherid'){
                //判断当前用户有没有个人的VIP和团体VIP
                    $whereData['user_id'] = $item['teacherid'];
                    $whereData['role_id'] = ROLE_TEACHER;
                    $whereData['auth_id'] = 4;
                    $ownVip = $auth_type_use->getVipType($whereData);//个人VIP权限
                    $whereData['auth_id'] = 3;
                    $groupVip =  $auth_type_use->getVipType($whereData);//团体VIP权限
                //如果既有团体VIP又有个人VIP的话就给用户有的话把团体VIP删除掉
                    if($ownVip && $groupVip){
                        $whereData['auth_id'] = 3;
                        $auth_type_use->deleteVip($whereData);
                    }
                //如果既没有团体VIP又没有个人VIP的话就给用户添加个人VIP
                    if(empty($ownVip) && empty($groupVip)){
                        give_new_vip_operation(ROLE_TEACHER,1,$item['teacherid']);
                    }
                //如果只有个人VIP不做任何操作
                //如果只有团体VIP就把团体VIP修改成个人VIP
                    if(empty($ownVip) && $groupVip){
                        $auth_type_use->updateNode($item['teacherid'],ROLE_TEACHER,4,time(),time()+3600*24*30*3,$groupVip['timetype']);
                    }
                }elseif (key($item) == 'studentid'){
                    //判断当前用户有没有个人的VIP和团体VIP
                    $whereData['user_id'] = $item['studentid'];
                    $whereData['role_id'] = ROLE_STUDENT;
                    $whereData['auth_id'] = 4;
                    $ownVip = $auth_type_use->getVipType($whereData);//个人VIP权限
                    $whereData['auth_id'] = 3;
                    $groupVip =  $auth_type_use->getVipType($whereData);//团体VIP权限
                    //如果既有团体VIP又有个人VIP的话就给用户有的话把团体VIP删除掉
                    if($ownVip && $groupVip){
                        $whereData['auth_id'] = 3;
                        $auth_type_use->deleteVip($whereData);
                    }
                    //如果既没有团体VIP又没有个人VIP的话就给用户添加个人VIP
                    if(empty($ownVip) && empty($groupVip)){
                        give_new_vip_operation(ROLE_STUDENT,1,$item['studentid']);
                    }
                    //如果只有个人VIP不做任何操作
                    //如果只有团体VIP就把团体VIP修改成个人VIP
                    if(empty($ownVip) && $groupVip){
                        $auth_type_use->updateNode($item['studentid'],ROLE_STUDENT,4,time(),time()+3600*24*30*3,$groupVip['timetype']);
                    }

                }
            }
            $this->showjson(200);
        }


        $privilege_type=getParameter('privilege_type','int',false);  
        $vip_use_type=getParameter('use_type','int');
        $start_time=getParameter('start_time','str');
        $end_time=getParameter('end_time','str'); 
        $opertaion_falg=getParameter('flag','int',false); 
        $result=$this->model->getSchoolInfo($id);   
        if(empty($result)){        
            $this->showjson('401',ID_NOT_EXISTS_MESSAGE);
        }else{  
            /*if($opertaion_falg==0){
                if($result['permissions_status']==1){
                    $this->showjson('402','未知的权限!');
                }
            }*/
            $start_time=strtotime($start_time);
            $end_time = date('y-m-d 23:59:00',strtotime($end_time));
            $end_time= strtotime($end_time);
            /*if($start_time<time()){
                $this->showjson('403',COMMON_FAILED_MESSAGE);
            }*/
            if($start_time>$end_time){
                $this->showjson('404','开始时间不能晚于结束时间!');
            } 
            //试用和试用类型
            $trial_use=1;
            $use=2;
            if($vip_use_type!=$trial_use && $vip_use_type!=$use){
                $this->showjson('405','未知的权限使用类型');
            }  
            if($opertaion_falg){
                if($privilege_type!=1 && $privilege_type!=2){
                    $this->showjson('407','未知的权限类型');
                }
                if($privilege_type==1){
                    $data['user_auth']=0;
                    $data['auth_start_time']='';
                    $data['auth_end_time']='';
                    $data['timetype']=0;
                }else{
                    $data['user_auth']=PRIVILEGE_GROUP_VIP;
                    $data['auth_start_time']=$start_time;
                    $data['auth_end_time']=$end_time;
                    $data['timetype']=$vip_use_type;
                }
            }else{
                $data['user_auth']=PRIVILEGE_GROUP_VIP;
                $data['auth_start_time']=$start_time;
                $data['auth_end_time']=$end_time;
                $data['timetype']=$vip_use_type; 
            }  
            if(!$this->model->updateSchoolData($id,$data)){
                M('account_user_and_auth')->rollback();
                $this->showjson('406',COMMON_FAILED_MESSAGE);
            }
            M('account_user_and_auth')->commit();
            //TODO:添加学校VIP后相应的为此学校下的所有人员添加团体VIP权限
            foreach ($allPersonBySchool as $item){
                if(key($item) == 'teacherid'){
                    //修改和添加VIP的操作
                    $auth_type_use->updateNode($item['teacherid'],ROLE_TEACHER,3,$start_time,$end_time,$vip_use_type);
                    //give_new_vip_operation(ROLE_TEACHER,3,$item['teacherid'],$id);
                }elseif (key($item) == 'studentid'){
                    $auth_type_use->updateNode($item['studentid'],ROLE_STUDENT,3,$start_time,$end_time,$vip_use_type);
                    //give_new_vip_operation(ROLE_STUDENT,3,$item['studentid'],$id);
                }
            }
            $this->showjson(200);
        }
    }
    
    
    
    /*
     * 启用或停用学校状态
     */
    function updateStatusBySchool(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $id=getParameter('id','int');
        $result=$this->model->getSchoolInfo($id);

        $teacher_id_all = D('Dict_schoollist')->getAllSchoolLeveTeacher($id);

        $parent_id_all = D('Dict_schoollist')->getAllSchoolLeveStu($id);

        $student_id_all = D('Dict_schoollist')->getStudentIdAll($id);//对所有的学生进行推送


        if(empty($result)){
            $this->showjson('401',ID_NOT_EXISTS_MESSAGE);
        }else{  
            if($result['flag']==1){
                $status=$this->model->updateSchoolStatus($id,DISABLE_SCHOOL_STATUS,0);
            }else{
                $status=$this->model->updateSchoolStatus($id,ENABLE_SCHOOL_STATUS,0);
            } 
            if($status){

                if ( $result['flag'] == 1) { //该为停用班级
                    //消息推送
                    $parameters = array(
                        'msg' => array(
                            C('ADMIN_ROOT'),
                        ),
                        'url' => array( 'type' => 0,)
                    );

                    A('Home/Message')->addPushUserMessage('SCHOOL_FALG_DISABLE', 2, implode(',', $teacher_id_all) , $parameters);

                    //对家长推送

                    $parameters = array(
                        'msg' => array(
                        ),
                        'url' => array( 'type' => 0,)
                    );

                    A('Home/Message')->addPushUserMessage('DELETESCHOOLE', 4,implode(',', $parent_id_all) , $parameters);

                    //对所有学生推送
                    A('Home/Message')->addPushUserMessage('STUDELETESCHOOLE', 3,implode(',', $student_id_all) , $parameters);

                }

                $this->showjson('200');
            }else{
                $this->showjson('402',COMMON_FAILED_MESSAGE);
            }
        }
    }
    
    
    /*
     * 创建学校 
     */
    public function createSchool(){ 
        if (!session('?admin')) redirect(U('Login/login'));
        
        if($_POST){ 
            $data['school_name']=getParameter('school_name','str');
            $data['school_address']=getParameter('school_address','str');
            $data['provice_id']=getParameter('province','int');
            $data['city_id']=getParameter('city','int');
            $data['district_id']=getParameter('district','int');
            $data['school_category']=intval($_POST['school_category']);                
            $data['obligation_person']=getParameter('obligation_person','str',false);
            $data['obligation_tel']=getParameter('obligation_tel','str',false);
            $data['obligation_email']=getParameter('obligation_email','str',false);
            $school_cate_exists_flag=0;
            $school_type_arr=C('SCHOOL_CATEGORY');      
            foreach($school_type_arr as $key=>$school_type_value){
                if($data['school_category']==$key){
                    $school_type=$key;
                    $school_cate_exists_flag=1;
                    break;
                }
            }   
            $tel_reg="/^1[34578]{1}\d{9}$/";
            $email_reg="/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/";
            if($data['obligation_tel']!=''){
                if(!preg_match($tel_reg,$data['obligation_tel'])){    
                    $this->error('手机格式有误!');
                }
            }
            if($data['obligation_email']!=''){
                if(!preg_match($email_reg,$data['obligation_email'])){
                    $this->error('邮箱格式有误!');
                }
            }
            if($school_cate_exists_flag==0){
                $this->error('学校类型不存在'); 
            }     
            $province_result=$this->model->getAddressById($data['provice_id'],PROVINCE_LEVEL);
            $city_result=$this->model->getAddressById($data['city_id'],CITY_LEVEL);
            $district_result=$this->model->getAddressById($data['district_id']);
            if(empty($province_result)){
               $this->error('省份不存在'); 
            }
            if(empty($city_result)){ 
                $this->error('城市不存在');
            }
            if(empty($district_result)){ 
                $this->error('区县不存在');
            }
            $prifix_code=$district_result['code'];
            if(strlen($prifix_code)!=6){
                $prifix_code=$province_result['code'].$city_result['code'].CODE;
            }
            $rand_number=rand(100,999);
            $school_code=$prifix_code.$rand_number;
            $data['school_code']=$school_code;
            $data['create_at']=time();
            
            $data['provice_id']=$data['provice_id'];
            $con['district_id']=$data['district_id'];
            $con['school_name']=$data['school_name'];
            $school_result=$this->model->getSchool($con);
            if($school_result){
                $this->error('已有相同地区相同名称的学校!');
            }
            if($this->model->addSchool($data)){
                //跳转到列表页面 
                $this->redirect(U('School/schoolList'));
            }else{
                $this->error(CREATE_SCHOOL_FAILED);
            }
        }else{ 
            $province_result=$this->model->getProvince();  
            $school_category=C('SCHOOL_CATEGORY'); 
            $this->assign('province_list',$province_result);  
            $this->assign('school_category',$school_category);
            $this->display('schoolAdd');
        }
    }
    
    
    /*
     * 某个学校下创建班级
     */
    public function schoolCreateClass(){        
        if (!session('?admin')) redirect(U('Login/login'));
        
        if(!empty($_POST)){
            $grade_model=D('Dict_grade');
            $teacher_model=D('Auth_teacher');
            $class_model=D('Biz_class');
            $class_type=getParameter('class_type','int');
            $grade=getParameter('grade','int');
            $class_name=getParameter('class_name','str');
            $school_id=getParameter('schoo_id','int');
            $teacher_name=getParameter('teacher_name','str',false);
            $telephone=getParameter('telephone','str',false);
            
            $grade_result=$grade_model->getGradeInfo($grade);
            if(empty($grade_result)){
                $this->error('年级信息不存在!');
            }
            $school_result=$this->model->getSchoolInfo($school_id);   
            if(empty($school_result)){
                $this->error('学校信息不存在!');
            }
                   
            $data['grade_id']=$grade;
            $data['name']=$class_name;
            $data['class_status']=$class_type;
            $data['create_at']=time();  
            $data['flag']=1;
            if($class_type==SCHOOL_CLASS){
                //校内班 
                $class_con['biz_class.school_id']=$school_id;
                $class_con['biz_class.grade_id']=$grade;
                $class_con['biz_class.is_delete']=0;
                $class_con['biz_class.name']=$class_name;
                $class_con['biz_class.class_status']=SCHOOL_CLASS;
                $class_result=$class_model->getClassDataAll($class_con);
                if(!empty($class_result)){
                    $this->error('该学校下已有相同的班级!');
                } 
                $this->model->startTrans();
                $data['school_id']=$school_id;
                if(!($insert_id=$class_model->addClass($data))){
                    $this->model->rollback();
                    $this->error('入库失败!');
                }
                $update_data['class_code']=$insert_id+CLASS_CODE_ADD_NUMBER; 
                if(!$class_model->updateClassInfo($insert_id,$update_data)){
                    $this->model->rollback();
                    $this->error('入库失败!'); 
                }
                $this->model->commit();
                
            }elseif($class_type==PERSONAL_CLASS){    
                $tel_reg="/^1[34578]{1}\d{9}$/";   
                if(!preg_match($tel_reg,$telephone)){       
                    $this->showjson('手机格式不正确!');
                }
                $teacher_con['auth_teacher.name']=$teacher_name;
                $teacher_con['auth_teacher.telephone']=$telephone;          
                $teacher_result=$teacher_model->getTeacherInfo($teacher_con);       
                if(empty($teacher_result)){ 
                    $this->error('教师信息不存在!');
                }else{
                    if($teacher_result['school_id']!=$school_id){
                        $this->error('该教师不是该学校的老师!');
                    }
                }  
                $personal_class_con['biz_class.class_status']=$class_type;
                $personal_class_con['biz_class.grade_id']=$grade;
                $personal_class_con['biz_class.name']=$class_name;
                $personal_class_con['biz_class.is_delete']=0;
                $personal_class_con['auth_teacher.id']=$teacher_result['id'];
                $class_result=$class_model->getClassDataAll($personal_class_con);
                if(!empty($class_result)){
                    $this->error('已有相同的班级存在!');
                } 
                $this->model->startTrans();
                if(!($insert_id=$class_model->addClass($data))){
                    $this->error('入库失败!');
                }
                $update_data['class_code']=$insert_id+CLASS_CODE_ADD_NUMBER;
                if(!$class_model->updateClassInfo($insert_id,$update_data)){
                    $this->model->rollback();
                    $this->error('入库失败!'); 
                }
                $classTeacherData['class_id'] = $insert_id;
                $classTeacherData['teacher_id'] = $teacher_result['teacher_id'];
                $classTeacherData['create_at'] = time();
                $classTeacherData['is_handler'] = 1;
                if(!$class_model->addClassTeacher($classTeacherData)){
                    $this->error('入库失败!');
                }
                $this->model->commit();
            }else{
                $this->error('参数错误!');
            }
            $this->redirect(U('School/schoolDetail'),array('id'=>$school_id));
        }else{
            $grade_model=D('Dict_grade');
            $id=getParameter('school_id','int');
            $result=$this->model->getSchoolSimpleData($id); 
            if(empty($result)){
                $this->error('参数错误');
            }
            $grade_result=$grade_model->getGradeList(true);
            $this->assign('school_id',$id);
            $this->assign('grade_list',$grade_result);
            $this->display();
        }
    }
    
    
    /*
     * 学校详情
     */
    public function schoolDetail(){ 
        if (!session('?admin')) redirect(U('Login/login'));
        
        $id=getParameter('id','int');
        $result=$this->model->getSchoolSimpleData($id);         
        $school_category=C('SCHOOL_CATEGORY');  
        $this->assign('school_category',$school_category);
        $this->assign('data',$result);             
        $this->display();
    }
    
    
    /*
     * 修改学校信息
     */
    public function schoolModify(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        if(!empty($_POST)){ 
            $school_id=getParameter('id','int',false);
            $province=getParameter('province','int',false);
            $city=getParameter('city','int',false);
            $district=getParameter('district','int',false);
            $school_name=getParameter('school_name','str',false);
            $school_address=getParameter('school_address','str',false);
            $school_category=$_POST['school_category'];
            $obligation_person=getParameter('obligation_person','str',false);
            $obligation_telephone=getParameter('obligation_telephone','str',false);
            $obligation_email=getParameter('obligation_email','str',false);
            //管理员账号
            $school_admin_id=getParameter('school_admin_id','str',false);
            $auth_admin_name=getParameter('account_name','str',false);
              
            $tel_reg="/^1[34578]{1}\d{9}$/";
            $email_reg="/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/";
            if($obligation_telephone!=''){
                if(!preg_match($tel_reg,$obligation_telephone)){    
                    $this->error('手机格式有误!');
                }
            }
            if($email!=''){
                if(!preg_match($email_reg,$obligation_email)){
                    $this->error('邮箱格式有误!');
                }
            }
            $result=$this->model->getSchoolSimpleData($school_id);
            if(empty($result)){
                $this->error('参数错误!');
            }
            $school_cat=C('SCHOOL_CATEGORY');
            if($school_category<count($school_cat) && $school_cat>=0){
                //
            }else{
                $this->error('参数错误!');
            }
            $province_result=$this->model->getAddressById($province,PROVINCE_LEVEL);
            $city_result=$this->model->getAddressById($city,CITY_LEVEL);
            $district_result=$this->model->getAddressById($district);
            if(empty($province_result)){
               $this->error('省份不存在'); 
            }
            if(empty($city_result)){
                $this->error('城市不存在');
            }
            if(empty($district_result)){
                $this->error('区县不存在');
            }
            $con['id']=array('neq',$school_id);
            $con['provice_id']=$province;
            $con['city_id']=$city;
            $con['district_id']=$district;
            $con['school_name']=$school_name;
            $school_result=$this->model->getSchool($con);
            if($school_result){
                $this->error('已有相同地区相同名称的学校!');
            }
            
            $data['provice_id']=$province;
            $data['city_id']=$city;
            $data['district_id']=$district;
            $data['school_name']=$school_name;
            $data['school_address']=$school_address;
            $data['school_category']=intval($school_category);
            $data['obligation_person']=$obligation_person;
            $data['obligation_tel']=$obligation_telephone;
            $data['obligation_email']=$obligation_email;
            $this->model->startTrans();
            if(!$this->model->updateSchoolData($school_id,$data)){ 
                $this->model->rollback();
                $this->error('入库失败!');
            }  
            if($school_admin_id){
                $admin_data['name']=$auth_admin_name;
                $admin_model=D('Auth_admin');
                if(!$admin_model->updateSchoolAdminData($school_admin_id,$admin_data)){
                    $this->model->rollback();
                    $this->error('入库失败!');
                }
            }
            $this->model->commit();
            $this->redirect(U('School/schoolModify'),array('id'=>$school_id));
            
        }else{
            $school_model=D('Dict_schoollist');
            $id=getParameter('id','int');
            $city_result=array();
            $district_result=array();
            $school_result=array();
            $result=$this->model->getSchoolSimpleData($id);
            $province_result=$school_model->getProvince();
            if(!empty($result)){     
                $city_result=$school_model->getCityByProvince($result['province_id']);
                $district_result=$school_model->getDistrictByCity($result['city_id']); 
                $school_result=$school_model->getSchoolByDistrict($result['district_id']);
            }
            $school_category=C('SCHOOL_CATEGORY'); 
            $this->assign('province_list',$province_result);
            $this->assign('city_list',$city_result);        
            $this->assign('district_list',$district_result);
            $this->assign('school_list',$school_result);
            $this->assign('school_category',$school_category);
            $this->assign('data',$result);                   
            $this->display();
        }
    }
    
    
    /*
     * ajax获得一条学校数据
     */
    public function getSchoolInfo(){
        $school_id=getParameter('id','int',false);
        $result=$this->model->getSchoolInfo($school_id);    
        $this->showjson(200,'',$result);
    }
     
    
    /*
     * 班级列表iframe
     */
    public function school_iframe_class(){
        $grade_model=D('Dict_grade');
        $class_model=D('Biz_class');
        $school_id=getParameter('id','int');
        $filter['id']=$school_id;
        $filter['class_code']=getParameter('class_code','str',false);
        $filter['grade_id']=getParameter('grade_id','int',false);
        $filter['class_name']=getParameter('class_name','str',false);
        $filter['class_status']=getParameter('class_status','int',false);
        $filter['class_flag']=$_GET['class_flag'];
        $filter['order']=getParameter('order','int',false);
        $order=getParameter('order','int',false);
        $order?$order='asc':$order='desc';
         
        $where['dict_schoollist.id']=$school_id;
        $where['auth_teacher.school_id']=$school_id;
        $where['_logic'] = 'or';
        $condition['_complex'] = $where;  
        
        $condition['biz_class.is_delete']=0;
        if(!empty($filter['class_code']))   $condition['biz_class.class_code']=array('like', '%' . $filter['class_code']. '%');
        if(!empty($filter['grade_id']))   $condition['dict_grade.id']=$filter['grade_id'];
        if(!empty($filter['class_name']))   $condition['biz_class.name']=$filter['class_name'];    
        if(!empty($filter['class_status']))   $condition['biz_class.class_status']=$filter['class_status'];
        if($filter['class_flag']!='')   $condition['biz_class.flag']=intval($filter['class_flag']);
            
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        }
        
        $class_result=array();
        if(!empty($filter['grade_id'])){    
            $class_list=$class_model->getClassDataByTeacherSchool($school_id,$filter['grade_id'],'1',1);     
        }
        $grade_result=$grade_model->getGradeList(true);
        $class_result=$class_model->getClassData($condition,$order);    
        
        $this->assign('condition_str',$condition_string);
        $this->assign('class_code',$filter['class_code']); 
        $this->assign('grade_id',$filter['grade_id']);
        $this->assign('class_name',$filter['class_name']); 
        $this->assign('class_flag',$filter['class_flag']); 
        $this->assign('class_status',$filter['class_status']); 
        $this->assign('order',$order);
         
        $this->assign('school_id',$school_id);
        $this->assign('grade_list',$grade_result);
        $this->assign('class_list',$class_list);
        $this->assign('list',$class_result['data']);        
        $this->assign('page',$class_result['page']);
        $this->display();
    }
     
    /*
     * 下载学校内导入班级的例子
     */
    public function downloadSchoolClassDemo(){
        if (!session('?admin')) redirect(U('Login/login')); 
        
        $csv=new CSV();
        $file="Public/csv/admin/schoolClassDemo__.csv";   
        $csv->downloadFile($file); 
    }
    
    
    /*
     * 下载导入失败的数据
     */
    public function downloadImportClassErrorData(){
        if (!session('?admin')) redirect(U('Login/login'));
          
        $grade_arr=$_POST['grade'];
        $class_arr=$_POST['class'];  
        $class_type_arr=$_POST['class_type'];
        $teacher_name_arr=$_POST['teacher_name'];
        $telephone_arr=$_POST['telephone'];
        
        $str="学生姓名,家长手机号,班级类型,教师姓名,教师手机号\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($grade_arr as $key=>$val){
            $grade=iconv('utf-8','gbk', $val);
            $class_name=iconv('utf-8','gbk', $class_arr[$key]);      
            $class_type=iconv('utf-8','gbk', $class_type_arr[$key]);      
            $teacher_name=iconv('utf-8','gbk', $teacher_name_arr[$key]);      
            $telephone=iconv('utf-8','gbk', $telephone_arr[$key]);      
            
            $str.=$grade.",".$class_name.",".$class_type.",".$teacher_name.",".$telephone."\n";
        } 
        $filename=date('Ymd').rand(0,1000).'学校内导入班级失败信息'.'.csv';
        $csv=new CSV();
        $csv->downloadFileCsv($filename,$str);
    }
    
    
    /*
     * 学校导入班级
     */
    public function importSchoolClass(){            
        if (!session('?admin')) redirect(U('Login/login'));
        
        if(!empty($_FILES)){ 
            $csv=new CSV();
            $result=$csv->getCsvData($_FILES);
            if(!is_array($result)){
                $this->showjson(1002,'文件内容为空!');
            }
            $str_encode=A('Admin/student')->getStringEncode($result['result'][0][0]);
            $i_data=$result['result'];        
            $length=$result['length'];

            
            $student_model=D('Auth_student');  
            $teacher_model=D('Auth_teacher');
            $grade_model=D('Dict_grade');
            $class_model=D('Biz_class');        
            $school_id=getParameter('id','int'); 
            $school_result=$this->model->getSchoolInfo($school_id);
            if(empty($school_result)){
                $this->showjson(1003,'学校参数错误!');
            }  

            $notice_array=array(); 
            $success_array=array();

            //如果学生存在就该条数据就跳过
            for($i=1;$i<$length;$i++){
                $class_data['grade']=$this->encode_string($str_encode,$i_data[$i][0]);
                $class_data['name']=$this->encode_string($str_encode,$i_data[$i][1]);  
                $class_data['class_type']=$this->encode_string($str_encode,$i_data[$i][2]);
                $class_data['teacher_name']=$this->encode_string($str_encode,$i_data[$i][3]);
                $class_data['teacher_tel']=$this->encode_string($str_encode,$i_data[$i][4]);
                    
                $add_clss_data=$class_data;
                $notice=$add_clss_data; 
                $success=$add_clss_data;    
                if($class_data['class_type']==SCHOOL_CLASS_STR){
                    $class_type_status=1; 
                }elseif($class_data['class_type']==PERSONAL_CLASS_STR){
                    $class_type_status=2;
                }else{
                    $notice['notice_message']='班级类型不存在!';
                    $notice_array[]=$notice;
                    continue;
                }
                $grade_result=$grade_model->getGradeByName($class_data['grade']);
                if(empty($grade_result)){ 
                    $notice['notice_message']='年级不存在!';
                    $notice_array[]=$notice;
                    continue;
                }
                
                
                $class_data['name']=$class_data['name'];
                $class_data['grade_id']=$grade_result['id']; 
                $class_data['create_at']=time();
                $class_data['class_status']=$class_type_status;
                $class_data['flag']=1;  
                
                $this->model->startTrans();

                $class_condition=array();
                if($class_data['class_type']==SCHOOL_CLASS_STR){
                    $class_condition['dict_grade.id']=$grade_result['id'];
                    $class_condition['biz_class.name']=$class_data['name'];
                    $class_condition['biz_class.school_id']=$school_id;   
                    $class_condition['biz_class.is_delete']=0;
                    $class_count=$class_model->getClassCount($class_condition);
                    if($class_count){
                        $notice['notice_message']='班级已存在';
                        $notice_array[]=$notice;
                        continue;
                    }
                    $class_data['school_id']=$school_id;
                    if(!($insert_id=$class_model->addClass($class_data))){
                        $this->model->rollback();
                        $notice['notice_message']='数据插入失败';
                        $notice_array[]=$notice;
                        continue;
                    }
                    $update_data['class_code']=$insert_id.CLASS_CODE_ADD_NUMBER; 
                    if(!$class_model->updateClassInfo($insert_id,$update_data)){
                        $this->model->rollback();
                        $notice['notice_message']='数据插入失败';
                        $notice_array[]=$notice;
                        continue;
                    }
                }else{
                    //判断教师是否存在  
                    $teacher_con['name']=$class_data['teacher_name'];
                    $teacher_con['telephone']=$class_data['teacher_tel'];
                    $teacher_result=$teacher_model->getTeacherInfo($teacher_con);
                    if(empty($teacher_result)){
                        $this->model->rollback();
                        $notice['notice_message']='教师信息不存在!';
                        $notice_array[]=$notice;
                        continue;
                    }  
                    $class_condition['dict_grade.id']=$grade_result['id'];
                    $class_condition['biz_class.name']=$class_data['name'];
                    $class_condition['auth_teacher.school_id']=$teacher_result['school_id']; 
                    $class_condition['biz_class.is_delete']=0;
                    $class_count=$class_model->getClassCount($class_condition);         
                    if($class_count){
                        $this->model->rollback();
                        $notice['notice_message']='班级已存在';
                        $notice_array[]=$notice;
                        continue;
                    }
                    if(!($insert_id=$class_model->addClass($class_data))){
                        $this->model->rollback();
                        $notice['notice_message']='数据插入失败';
                        $notice_array[]=$notice;
                        continue;
                    } 
                    $update_data['class_code']=$insert_id.CLASS_CODE_ADD_NUMBER; 
                    if(!$class_model->updateClassInfo($insert_id,$update_data)){
                        $this->model->rollback();
                        $notice['notice_message']='数据插入失败';
                        $notice_array[]=$notice;
                        continue;
                    } 
                    //班级教师表插入数据
                    $class_teacher['class_id']=$insert_id;
                    $class_teacher['teacher_id']=$teacher_result['id'];
                    $class_teacher['create_at']=time();
                    $class_teacher['is_handler']=1;
                    if(!$class_model->addClassTeacher($class_teacher)){
                        $this->model->rollback();
                        $notice['notice_message']='数据插入失败';
                        $notice_array[]=$notice;
                        continue;
                    }
                }  
                $this->model->commit(); 
                $success_array[]=$success;  
            }
            $big_data=array();
            $big_data['success']=$success_array;
            $big_data['failed']=$notice_array;
            $this->showjson(200,'success',$big_data);
            
        }else{
            $school_id=getParameter('id','int',false);
            $result=$this->model->getSchoolInfo($school_id);
            if(empty($result)){
                $this->error('参数错误!');
            }
            $this->assign('school_id',$school_id);
            $this->display('school_iframe_class_import');
        }
    }
    
    
    /*
     * 教师加入学校
     */
    /*public function teacherJoinSchool(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $teacher_model=D('Auth_teacher');
        $school_id=getParameter('school_id','int');
        $teacher_id=getParameter('teacher_id','int');
        $result=$this->model->getSchoolSimpleData($school_id); 
        $teacher_result=$teacher_model->getTeachInfo($teacher_id);
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }
        if(empty($teacher_result)){
            $this->showjson(402,ID_NOT_EXISTS_MESSAGE);
        }else{
            if($student_result['school_id']==$school_id && $student_result['apply_school_status']==PASS_SCHOOL_STATUS){
                $this->showjson(403,'该教师已在该学校中');
            }
        }
        $data['school_id']=$school_id;
        $data['apply_school_status']=PASS_SCHOOL_STATUS;
        if(!$teacher_model->updateInfoById($data,$teacher_id)){
            $this->showjson(404,COMMON_FAILED_MESSAGE);
        }else{
            $this->showjson(200);
        }
    }*/
            
    
    /*
     * 教师列表iframe
     */
    public function school_iframe_teacher(){
        $grade_model=D('Dict_grade');
        $teacher_model=D('Auth_teacher');
        $course_model=D('Dict_course');
        $class_model=D('Biz_class');
        
        $school_id=getParameter('id','int');
        
        $filter['school_id']=$school_id; 
        $filter['teacher_name']=getParameter('teacher_name','str',false); 
        $filter['telephone']=getParameter('telephone','str',false);  
        $filter['apply_status']=$_GET['apply_status'];
        $filter['course']=getParameter('course','int',false);
        $filter['grade']=getParameter('grade','int',false);
        //$filter['class']=getParameter('class','int',false);
        $filter['account_status']=$_GET['account_status'];
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition=array();
        $where=array();
        $condition['dict_schoollist.id']=$school_id;
        if(!empty($filter['teacher_name']))   $condition['auth_teacher.name']=array('like', '%' . $filter['teacher_name']. '%');
        if(!empty($filter['telephone']))   $condition['auth_teacher.telephone']=array('like', '%' . $filter['telephone']. '%');
        if($filter['apply_status']!='')   $condition['auth_teacher.apply_school_status']=$filter['apply_status'];
        if(!empty($filter['course']))   $where['auth_teacher_second.course_id']=$filter['course'];
        if(!empty($filter['grade']))   $where['dict_grade.id']=$filter['grade'];
        //if(!empty($filter['class']))   $condition['biz_class.id']=$filter['class'];
        if($filter['account_status']!='')   $condition['auth_teacher.flag']=intval($filter['account_status']);
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        }
        
        $class_result=array();
        if(!empty($filter['grade'])){    
            $class_list=$class_model->getClassDataBySchool($school_id,$filter['grade']);    
        } 
        $course_result=$course_model->getCourseList();
        $grade_result=$grade_model->getGradeList(true);
        $teacher_result=$teacher_model->getTeacherData($condition,$order,'',$where);                
                        
        $this->assign('condition_str',$condition_string);
        $this->assign('teacher_name',$filter['teacher_name']); 
        $this->assign('telephone',$filter['telephone']);
        $this->assign('apply_status',$filter['apply_status']);  
        $this->assign('course',$filter['course']); 
        $this->assign('grade',$filter['grade']); 
        //$this->assign('class',$filter['class']);
        $this->assign('account_status',$filter['account_status']);
        $this->assign('order',$order);
        
        $this->assign('school_id',$school_id);
        $this->assign('grade_list',$grade_result);
        $this->assign('course_list',$course_result);
        $this->assign('class_list',$class_list);
        $this->assign('list',$teacher_result['data']);  
        $this->assign('page',$teacher_result['page']);
        $this->display();
    }
     
    /*
     * ajax 获得学生信息
     */
    public function getStudentInfo(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $student_model=D('Auth_student');
        $student_name=getParameter('student_name','str');
        $parent_tel=getParameter('parent_tel','str');
        $student_result=$student_model->getStudentInfoByTelAndName($parent_tel,$student_name);
        $this->showjson(200,'',$student_result);          
        
    }
    
     
    
    /*
     * 学生加入班级
     */
    public function studentJoinSchool(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $student_model=D('Auth_student');
        $school_id=getParameter('school_id','int');
        $student_id=getParameter('student_id','int');
        $result=$this->model->getSchoolSimpleData($school_id); 
        $student_result=$student_model->getStudentInfo($student_id);
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }
        if(empty($student_result)){
            $this->showjson(402,ID_NOT_EXISTS_MESSAGE);
        }else{
            if($student_result['school_id']==$school_id && $student_result['apply_school_status']==PASS_SCHOOL_STATUS){
                $this->showjson(403,'该学生已在该学校中');
            }
        }
        $data['school_id']=$school_id;
        $data['apply_school_status']=PASS_SCHOOL_STATUS;
        if(!$student_model->updateInfoById($data,$student_id)){
            $this->showjson(404,COMMON_FAILED_MESSAGE);
        }else{
            $this->showjson(200);
        }
    }
    
    
    /*
     * 学生列表
     */
    public function school_iframe_student(){ 
        $student_model=D('Auth_student'); 
        $class_model=D('Biz_class');
        $grade_model=D('Dict_grade');
        $school_id=getParameter('id','int');
        
        $filter['school_id']=$school_id; 
        $filter['student_name']=getParameter('student_name','str',false); 
        $filter['parent_tel']=getParameter('parent_tel','str',false); 
        $filter['account_status']=$_GET['account_status'];      
        $filter['grade']=getParameter('grade','int',false); 
        $filter['class']=getParameter('class','int',false); 
        $filter['apply_status']=$_GET['apply_status'];      
        $filter['order']=getParameter('order','int',false); 
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        }
        
        $condition=array();
        $condition['dict_schoollist.id']=$school_id;
        if(!empty($filter['student_name']))   $condition['auth_student.student_name']=array('like', '%' . $filter['student_name']. '%');
        if(!empty($filter['parent_tel']))   $condition['auth_student.parent_tel']=array('like', '%' . $filter['parent_tel']. '%');
        if($filter['account_status']!='')   $condition['auth_student.flag']=intval($filter['account_status']);
        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade'];
        if(!empty($filter['class']))   $condition['biz_class.id']=$filter['class'];
        if($filter['apply_status']!='')   $condition['auth_student.apply_school_status']=intval($filter['apply_status']);
        
        $class_result=array();
        if(!empty($filter['grade'])){    
            $class_list=$class_model->getClassDataBySchool($school_id,$filter['grade']);    
        }
        $grade_result=$grade_model->getGradeList(true);
        $student_result=$student_model->getStudentData($condition,$order);         
        
        $this->assign('condition_str',$condition_string);
        $this->assign('student_name',$filter['student_name']); 
        $this->assign('parent_tel',$filter['parent_tel']);
        $this->assign('account_status',$filter['account_status']);        
        $this->assign('grade',$filter['grade']); 
        $this->assign('class',$filter['class']); 
        $this->assign('apply_status',$filter['apply_status']);
        $this->assign('order',$order);
        
        $this->assign('grade_list',$grade_result);
        $this->assign('class_list',$class_list);
        $this->assign('school_id',$school_id);
        $this->assign('list',$student_result['data']);
        $this->assign('page',$student_result['page']); 
        $this->display();
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
     * 下载导入失败的数据
     */
    public function downloadImportErrorData(){
        if (!session('?admin')) redirect(U('Login/login'));
         
        //var_dump($_POST);   //die;
        $school_arr=$_POST['school'];
        $province_arr=$_POST['province'];
        $city_arr=$_POST['city'];
        $district_arr=$_POST['district'];
        $school_type_arr=$_POST['school_type'];
        $obligation_person_arr=$_POST['obligation_person'];
        $obligation_telephone_arr=$_POST['obligation_telephone'];
        
        $str="学校名称,省份,城市,区/县,学校性质,负责人,负责人联系电话\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($school_arr as $key=>$val){
            $school_name=iconv('utf-8','gbk', $school_arr[$key]);
            $province=iconv('utf-8','gbk', $province_arr[$key]);  
            $city=iconv('utf-8','gbk', $city_arr[$key]);   
            
            $district=iconv('utf-8','gbk', $district_arr[$key]);
            $school_type=iconv('utf-8','gbk', $school_type_arr[$key]);
            $obligation_person=iconv('utf-8','gbk', $obligation_person_arr[$key]);
            $obligation_telephone=iconv('utf-8','gbk', $obligation_telephone_arr[$key]);
            
            $str.=$school_name.",".$province.",".$city.",".$district.",".$school_type.",".$obligation_person.",".$obligation_telephone."\n";
        } 
            $filename=date('Ymd').rand(0,1000).'学校导入失败信息'.'.csv';
            $csv=new CSV();
            $csv->downloadFileCsv($filename,$str);
    }
    
    
    /*
     * 导入学校视图
     */
    public function schoolImport(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        $this->display();
    }
    
     
    /*
     * 导入学校
     */
    public function importSchool(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        if(empty($_FILES)){ 
            $this->showjson(1001);  //1002文件为空
        } 
        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){
            $this->showjson($result);
        }
        $str_encode=A('Admin/Student')->getStringEncode($result['result'][0][0]);     
        $import_data=$result['result'];        
        $length=$result['length'];
        
        $failed_array=array();
        $success_array=array();
        $notice_array=array();
        for($i=1;$i<$length;$i++){
            $data['school_name']=$this->encode_string($str_encode,$import_data[$i][0]);        
            $data['province_string']=$this->encode_string($str_encode,$import_data[$i][1]);
            $data['city_string']=$this->encode_string($str_encode,$import_data[$i][2]);
            $data['district_string']=$this->encode_string($str_encode,$import_data[$i][3]);
            $data['school_type']=$this->encode_string($str_encode,$import_data[$i][4]);
            $data['obligation_person']=$this->encode_string($str_encode,$import_data[$i][5]);
            $data['obligation_tel']=$this->encode_string($str_encode,$import_data[$i][6]);
            $notice=$data;
            
            $province_result=$this->model->getAddress($data['province_string'],PROVINCE_LEVEL); 
            if(empty($province_result)){
                $notice['notice_message']='省份不存在';
                $notice_array[]=$notice;
                continue;
            }
            $city_result=$this->model->getAddress($data['city_string'],CITY_LEVEL); 
            if(empty($city_result)){
                $notice['notice_message']='城市不存在';
                $notice_array[]=$notice;
                continue;
            }
            $district_result=$this->model->getAddress($data['district_string'],DISTRICT_LEVEL); 
            if(empty($district_result)){
                $notice['notice_message']='区县不存在';
                $notice_array[]=$notice;
                continue;
            }   
            $school_type_arr=C('SCHOOL_CATEGORY');            
            if(!in_array($data['school_type'],$school_type_arr)){
                $notice['notice_message']='学校类型不存在';
                $notice_array[]=$notice;
                continue;
            }else{
                foreach($school_type_arr as $key=>$school_type_value){
                    if($data['school_type']==$school_type_value){
                        $school_type=$key;
                    }
                }
            }         
            $school_condition['provice_id']=$province_result['id'];
            $school_condition['city_id']=$city_result['id'];
            $school_condition['district_id']=$district_result['id'];
            $school_condition['school_name']=$data['school_name'];
            $count=$this->model->getCountSchool($school_condition); 
            if($count){
                $notice['notice_message']='学校已存在';
                $notice_array[]=$notice;
                continue;
            }
            $exists_admin=0;
            if($data['obligation_tel']!=''){
                if(!preg_match("/^1[34578]{1}\d{9}$/",$data['obligation_tel'])){      
                    $notice['notice_message']='手机号格式不正确';
                    $notice_array[]=$notice;
                    continue;
                }
                $exists_admin=1;
            }
            $prifix_code=$district_result['code'];
            if(strlen($prifix_code)!=6){
                $prifix_code=$province_result['code'].$city_result['code'].CODE;
            }
            $rand_number=rand(100,999);
            $school_code=$prifix_code.$rand_number;
            $school_data['school_name']=$data['school_name'];
            $school_data['provice_id']=$province_result['id'];
            $school_data['city_id']=$city_result['id'];
            $school_data['district_id']=$district_result['id'];
            $school_data['obligation_tel']=$data['obligation_tel'];
            $school_data['obligation_person']=$data['obligation_person'];
            $school_data['create_at']=time();
            $school_data['school_code']=$school_code; 
            $school_data['school_category']=$school_type;
            
            $this->model->startTrans();
            if(!$this->model->addSchool($school_data)){
                $notice['notice_message']='手机号格式不正确';
                $notice_array[]=$notice;
                $this->model->rollback();
                continue;
            }else{
                $this->model->commit();
                $success_array[]=$data;
            }
        } 
        
        $big_data=array();
        $big_data['success']=$success_array;
        $big_data['failed']=$notice_array;     
        
        echo json_encode($big_data);
    }
    
    
    /*
     * 批量导出学校
     */
    public function exportedSchool(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        set_time_limit(0); 
        if(empty($_POST)){
            $this->error('参数错误');
        }else{  
            $condition_arr=I('hid'); 
            $condition['dict_schoollist.id']=array('in',$condition_arr);
            $data=$this->model->getSchoolDataAll($condition);  
            
            $str="学校名称,学校代号,学校性质,学校状态,是否已开通管理员,权限类型,负责人,负责人手机号\n";
            $str=iconv('utf-8','gbk', $str);
            foreach($data as $v){
                $school_name=iconv('utf-8','gbk', $v['school_name']);
                $school_code=iconv('utf-8','gbk', $v['school_code']);
                $school_cat='';
                foreach($school_cateogyr as $key=>$val){
                    if($v['school_category']==$key){
                        $school_cat=$val;
                    }
                }
                $school_cat_string=iconv('utf-8','gbk', $school_cat);
                if($v['flag']==1){
                    $school_status='正常';
                }else{
                    $school_status='停用';
                }
                $school_status=iconv('utf-8','gbk', $school_status);
                if($v['is_create_administartor']==1){
                    $administartor_status='已开通';
                }else{
                    $administartor_status='未开通';
                }
                $administartor_status=iconv('utf-8','gbk', $administartor_status);
                if($v['permissions_status']==1){
                    $permissions_status='团体VIP';
                }else{
                    $permissions_status='普通权限';
                }
                $permissions_status=iconv('utf-8','gbk', $permissions_status);
                $obligation_person=iconv('utf-8','gbk', $v['obligation_person']);
                $obligation_tel=iconv('utf-8','gbk', $v['obligation_person']);

                $str.=$school_name.",".$school_code.",".$school_cat_string.",".$school_status.",".$administartor_status.",".$permissions_status.",".$obligation_person.",".$obligation_tel."\n";
            } 
            $filename=date('Ymd').rand(0,1000).'School'.'.csv';
            $csv=new CSV();
            //export disable
            //$csv->downloadFileCsv($filename,$str);
        }
    }
    
    
    /*
     * 导出全部学校
     */
    public function exportedSchoolAll(){
        if (!session('?admin')) redirect(U('Login/login')); 
        
        set_time_limit(0); 
        $filter['provice_id']=getParameter('province','int',false);
        $filter['city_id']=getParameter('city','int',false);
        $filter['district_id']=getParameter('district','int',false);
        $filter['school']=getParameter('school','int',false); 
        $filter['school_code']=getParameter('school_code','str',false);
        $filter['flag']=$_GET['flag'];     //school_status
        $filter['administartor_status']=$_GET['administartor_status'];
        $filter['privilege_type']=getParameter('privilege_type','int',false);
        $filter['school_category']=$_GET['school_category'];
        $filter['order']=getParameter('order','int',false);  
        $order=$filter['order'];
        $order?$order='asc':$order='desc'; 
        
        $condition=array();
        if(!empty($filter['school']))   $condition['dict_schoollist.id']=$filter['school'];
        if(!empty($filter['school_code']))   $condition['school_code']=array('like', '%' . $filter['school_code']. '%');
        if($filter['flag']!='')   $condition['dict_schoollist.flag']=intval($filter['flag']);
        if($filter['administartor_status']!='')   $condition['is_create_administartor']=intval($filter['administartor_status']);
        if(!empty($filter['provice_id']))   $condition['provice_id']=$filter['provice_id'];
        if(!empty($filter['city_id']))   $condition['city_id']=$filter['city_id'];
        if(!empty($filter['district_id']))   $condition['district_id']=$filter['district_id'];
        if($filter['school_category']!='')   $condition['school_category']=intval($filter['school_category']);
        
        $having='';
        if(!empty($filter['privilege_type']))  $having='permissions_status='.$filter['privilege_type'];
        
        $school_cateogyr=C('SCHOOL_CATEGORY'); 
        $data=$this->model->getSchoolDataAll($condition,$order,$having);              
            
        
        $str="学校名称,学校代号,学校性质,学校状态,是否已开通管理员,权限类型,负责人,负责人手机号\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($data as $v){
            $school_name=iconv('utf-8','gbk', $v['school_name']);
            $school_code=iconv('utf-8','gbk', $v['school_code']);
            $school_cat='';
            foreach($school_cateogyr as $key=>$val){
                if($v['school_category']==$key){
                    $school_cat=$val;
                }
            }
            $school_cat_string=iconv('utf-8','gbk', $school_cat);
            if($v['flag']==1){
                $school_status='正常';
            }else{
                $school_status='停用';
            }
            $school_status=iconv('utf-8','gbk', $school_status);
            if($v['is_create_administartor']==1){
                $administartor_status='已开通';
            }else{
                $administartor_status='未开通';
            }
            $administartor_status=iconv('utf-8','gbk', $administartor_status);
            if($v['permissions_status']==1){
                $permissions_status='团体VIP';
            }else{
                $permissions_status='普通权限';
            }
            $permissions_status=iconv('utf-8','gbk', $permissions_status);
            $obligation_person=iconv('utf-8','gbk', $v['obligation_person']);
            $obligation_tel=iconv('utf-8','gbk', $v['obligation_person']);
                     
            $str.=$school_name.",".$school_code.",".$school_cat_string.",".$school_status.",".$administartor_status.",".$permissions_status.",".$obligation_person.",".$obligation_tel."\n";
        } 
        $filename=date('Ymd').rand(0,1000).'School'.'.csv';
        $csv=new CSV();
        //export disable
        //$csv->downloadFileCsv($filename,$str);
    }
    
    
    //下载学校模板
    public function downloadSchoolDemo(){ 
        $csv=new CSV(); 
        $file="Public/csv/admin/schoolDemo.csv"; 
        $csv->downloadFile($file);
    }
     
    
    
    /*
     * 批量导出学校下的班级
     */
    public function exportedSchoolClass(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        set_time_limit(0); 
        if(empty($_POST)){
            $this->error('参数错误');
        }else{
            $class_model=D('Biz_class');
            $condition_arr=I('hid'); 
            $condition['biz_class.id']=array('in',$condition_arr);      
            $data=$class_model->getClassDataAll($condition);   
            
            $str="年级,班级,班级代码,班级类型,班级状态,\n";
            $str=iconv('utf-8','gbk', $str);
            foreach($data as $v){
                $grade=iconv('utf-8','gbk', $v['grade']);
                $class=iconv('utf-8','gbk', $v['class_name']); 
                $class_code=iconv('utf-8','gbk', $v['class_code']); 

                if($v['class_status']==1){
                    $class_type='校内班';
                }else{
                    $class_type='个人班';
                }
                $class_type=iconv('utf-8','gbk', $class_type);
                if($v['flag']==1){
                    $class_status='正常';
                }else{
                    $class_status='停用';
                } 
                $class_status=iconv('utf-8','gbk', $class_status);

                $str.=$grade.",".$class.",".$class_code.",".$class_type.",".$class_status."\n";
            } 
            $filename=date('Ymd').rand(0,1000).'SchoolClass'.'.csv';
            $csv=new CSV();
            //export disable
            //$csv->downloadFileCsv($filename,$str);
        }
    }
    
    
    /*
     * 导出学校下的所有班级
     */
    public function exportedSchoolClassAll(){
        if (!session('?admin')) redirect(U('Login/login')); 
        
        set_time_limit(0); 
        $class_model=D('Biz_class');
        $school_id=getParameter('id','int'); 
        $filter['class_code']=getParameter('class_code','str',false);
        $filter['grade_id']=getParameter('grade_id','int',false);
        $filter['class_id']=getParameter('class_id','int',false);
        $filter['class_status']=getParameter('class_status','int',false);
        $filter['class_flag']=$_GET['class_flag'];
        $order=getParameter('order','int',false);
        $order= $filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition=array();
        $condition['biz_class.is_delete']=0;
        $condition['dict_schoollist.id']=$school_id;
        if(!empty($filter['class_code']))   $condition['biz_class.class_code']=array('like', '%' . $filter['class_code']. '%');
        if(!empty($filter['grade_id']))   $condition['dict_grade.id']=$filter['grade_id'];
        if(!empty($filter['class_id']))   $condition['biz_class.id']=$filter['class_id'];
        if(!empty($filter['class_status']))   $condition['biz_class.class_status']=$filter['class_status'];
        if(!empty($filter['class_flag']))   $condition['biz_class.flag']=$filter['class_flag'];
        
        $data=$class_model->getClassDataAll($condition,$order);       
        $str="年级,班级,班级代码,班级类型,班级状态,\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($data as $v){
            $grade=iconv('utf-8','gbk', $v['grade']);
            $class=iconv('utf-8','gbk', $v['class_name']); 
            $class_code=iconv('utf-8','gbk', $v['class_code']); 
            
            if($v['class_status']==1){
                $class_type='校内班';
            }else{
                $class_type='个人班';
            }
            $class_type=iconv('utf-8','gbk', $class_type);
            if($v['flag']==1){
                $class_status='正常';
            }else{
                $class_status='停用';
            } 
            $class_status=iconv('utf-8','gbk', $class_status);
            
            $str.=$grade.",".$class.",".$class_code.",".$class_type.",".$class_status."\n";
        } 
        $filename=date('Ymd').rand(0,1000).'SchoolClass'.'.csv';
        $csv=new CSV();
        //export disable
        //$csv->downloadFileCsv($filename,$str);
    }
    
    
    
    /*
     * 批量导出某个学校下的教师
     */
    public function exportedSchoolTeacher(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        set_time_limit(0); 
        if(empty($_POST)){
            $this->error('参数错误');
        }else{
            $teacher_model=D('Auth_teacher');
            $condition_arr=I('hid'); 
            $condition['auth_teacher.id']=array('in',$condition_arr);    
            $data=$teacher_model->getTeacherDataAll($condition);    
            
            $str="教师姓名,教师手机号,任教学科,任教年级,任教班级,申请加入学校审核状态,账号状态\n";
            $str=iconv('utf-8','gbk', $str);
            foreach($data as $v){
                $teacher_name=iconv('utf-8','gbk', $v['teacher_name']);
                $telephone=iconv('utf-8','gbk', $v['telephone']); 
                $course=iconv('utf-8','gbk', $v['course']);
                $grade=iconv('utf-8','gbk', $v['grade']);
                $class_name=iconv('utf-8','gbk', $v['class_name']);  

                if($v['apply_school_status']==1){
                    $apply_school_status='同意加入';
                }elseif($v['apply_school_status']==2){
                    $apply_school_status='已拒绝';
                }else{
                    $apply_school_status='待审核';
                }
                $apply_school_status=iconv('utf-8','gbk', $apply_school_status);

                if($v['flag']==1){
                    $account_status='正常';
                }else{
                    $account_status='停用';
                }
                $account_status=iconv('utf-8','gbk', $account_status); 

                $str.=$teacher_name.",".$telephone.",".$course.",".$grade.",".$class_name.",".$apply_school_status.",".$account_status."\n";
            } 
            $filename=date('Ymd').rand(0,1000).'SchoolTeacher'.'.csv';
            $csv=new CSV();
            //export disable
            //$csv->downloadFileCsv($filename,$str);
        }
    }
    
    
    /*
     * 导出某个学校下的所有教师
     */
    public function exportedSchoolTeacherAll(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        $teacher_model=D('Auth_teacher'); 
        $filter['school_id']=getParameter('school_id','int');
        $filter['teacher_name']=getParameter('teacher_name','str',false); 
        $filter['telephone']=getParameter('telephone','str',false);  
        $filter['apply_status']=$_GET['apply_status'];
        $filter['course']=getParameter('course','int',false);
        $filter['grade']=getParameter('grade','int',false);
        $filter['class']=getParameter('class','int',false);
        $filter['account_status']=$_GET['account_status'];
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition['dict_schoollist.id']=$filter['school_id'];
        if(!empty($filter['teacher_name']))   $condition['auth_teacher.name']=array('like', '%' . $filter['teacher_name']. '%');
        if(!empty($filter['telephone']))   $condition['auth_teacher.telephone']=array('like', '%' . $filter['telephone']. '%');
        if(!empty($filter['apply_status']))   $condition['auth_teacher.apply_school_status']=$filter['apply_status'];
        if(!empty($filter['grade']))   $where['dict_grade.id']=$filter['grade'];
        if(!empty($filter['class']))   $where['biz_class.id']=$filter['class'];
        if(!empty($filter['account_status']))   $condition['auth_teacher.flag']=$filter['account_status'];
        
        $data=$teacher_model->getTeacherDataAll($condition,$order,'',$where);     
        
        $str="教师姓名,教师手机号,任教学科,任教年级,任教班级,申请加入学校审核状态,账号状态\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($data as $v){
            $teacher_name=iconv('utf-8','gbk', $v['teacher_name']);
            $telephone=iconv('utf-8','gbk', $v['telephone']); 
            $course=iconv('utf-8','gbk', $v['course']);
            $grade=iconv('utf-8','gbk', $v['grade']);
            $class_name=iconv('utf-8','gbk', $v['class_name']);  
            
            if($v['apply_school_status']==1){
                $apply_school_status='同意加入';
            }elseif($v['apply_school_status']==2){
                $apply_school_status='已拒绝';
            }else{
                $apply_school_status='待审核';
            }
            $apply_school_status=iconv('utf-8','gbk', $apply_school_status);
             
            if($v['flag']==1){
                $account_status='正常';
            }else{
                $account_status='停用';
            }
            $account_status=iconv('utf-8','gbk', $account_status); 
             
            $str.=$teacher_name.",".$telephone.",".$course.",".$grade.",".$class_name.",".$apply_school_status.",".$account_status."\n";
        } 
        $filename=date('Ymd').rand(0,1000).'SchoolTeacher'.'.csv';
        $csv=new CSV();
        //export disable
        //$csv->downloadFileCsv($filename,$str);
    }
    
    
    /*
     * 批量导出某个学校下的学生
     */
    public function exportedSchoolStudent(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        set_time_limit(0); 
        if(empty($_POST)){
            $this->error('参数错误');
        }else{
            $student_model=D('Auth_student');
            $condition_arr=I('hid');           
            $condition['auth_student.id']=array('in',$condition_arr);    
            $data=$student_model->getStudentDataAll($condition);       
            $str="学生姓名,性别,家长手机号,状态,加入学校审核状态\n"; 
            
            $str=iconv('utf-8','gbk', $str);
            foreach($data as $val){
                $student_name=iconv('utf-8','gbk', $val['student_name']);
                $sex=iconv('utf-8','gbk', $val['sex']);
                $parent_tel=$val['parent_tel']; 
                $school_name=iconv('utf-8','gbk', $val['school_name']); 

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
                $str.=$student_name.",".$sex.",".$parent_tel.",".$school_name.",".$apply_status.",".$account_status."\n";
            }
            $filename=date('Ymd').rand(0,1000).'Schoolstudent'.'.csv';
            $csv=new CSV();
            //export disable
            //$csv->downloadFileCsv($filename,$str);
        }
    }
    
    
    /*
     * 导出某个学校的所有学生
     */
    public function exportedSchoolStudnetAll(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        $student_model=D('Auth_student');
        $filter['school_id']=getParameter('school_id','int');
        $filter['student_name']=getParameter('student_name','str',false); 
        $filter['parent_tel']=getParameter('parent_tel','str',false); 
        $filter['account_status']=$_GET['account_status'];      
        $filter['grade']=getParameter('grade','int',false); 
        $filter['class']=getParameter('class','int',false); 
        $filter['apply_status']=$_GET['apply_status'];
        $filter['order']=getParameter('order','int',false); 
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition=array();
        $condition['dict_schoollist.id']=$filter['school_id'];
        if(!empty($filter['student_name']))   $condition['auth_student.student_name']=array('like', '%' . $filter['student_name']. '%');
        if(!empty($filter['parent_tel']))   $condition['auth_student.parent_tel']=array('like', '%' . $filter['parent_tel']. '%');
        if($filter['account_status']!='')   $condition['auth_student.flag']=intval($filter['account_status']);
        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade'];
        if(!empty($filter['class']))   $condition['biz_class.id']=$filter['class'];
        if(!empty($filter['apply_status']))   $condition['auth_student.apply_school_status']=intval($filter['apply_status']);
        
        $data=$student_model->getStudentDataAll($condition,$order);       
        $str="学生姓名,性别,家长手机号,状态,加入学校审核状态\n";
        
        $str=iconv('utf-8','gbk', $str);
        foreach($data as $val){
            $student_name=iconv('utf-8','gbk', $val['student_name']);
            $sex=iconv('utf-8','gbk', $val['sex']);
            $parent_tel=$val['parent_tel']; 
            $school_name=iconv('utf-8','gbk', $val['school_name']); 
          
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
            $str.=$student_name.",".$sex.",".$parent_tel.",".$school_name.",".$apply_status.",".$account_status."\n";
        }
        $filename=date('Ymd').rand(0,1000).'Schoolstudent'.'.csv';
        $csv=new CSV();
        //export disable
        //$csv->downloadFileCsv($filename,$str);
    }
}
