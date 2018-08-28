<?php
namespace School\Controller;
use Think\Controller;  
define('SCHOOL_AUTH_CLASS',1); 
define('PROVINCE_LEVEL',1);
define('CITY_LEVEL',2);
define('DISTRICT_LEVEL',3);

class UserController extends Controller
{   

    public $model;
    public $page_size=20; 
                
    public function __construct() {
        parent::__construct();
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
        $this->model=D('Auth_school_admin');
    } 
    
    /*
     * 个人中心
     */
     public function userCenter(){
         if (!session('?school')) redirect(U('Login/login'));
         
         $id=session('school.id');
         $result=$this->model->getSchoolAdminData($id);
         if(!empty($result)){
            $school_category=C('SCHOOL_CATEGORY');
            foreach($school_category as $key=>$val){
                if($result['school_category']==$key){
                    $result['school_category']=$val;
                }
            } 
            
            if($result['permissions_status']==1){ 
                    $result['permissions']='团体VIP';
                    $result['auth_start_time']=date('Y-m-d',$result['auth_start_time']);
                    $result['auth_end_time']=date('Y-m-d',$result['auth_end_time']); 
            }else{
                $result['permissions']='普通权限';
                $result['auth_start_time']='';
                $result['auth_end_time']='';
            }
         }
         $this->assign('data',$result);
         $this->display();
     }
     
     
     /*
      * 修改个人信息    
      */
     public function updateUserData(){
         if (!session('?school')) redirect(U('Login/login'));
         
         $school_model=D('Dict_schoollist');
         if($_POST){
             /*$province=getParameter('province','int');
             $city=getParameter('city','int');
             $district=getParameter('district','int');
             $school_address=getParameter('school_address','str',false);
             $school_name=getParameter('school_name','str'); 
             $school_category=getParameter('school_category','str');*/
             $real_name=getParameter('real_name','str');
             $telephone=getParameter('telephone','str');
             $email=getParameter('email','str',false); 
             $school_id=session('school.school_id'); 
             $admin_id=session('school.id');
             
             $tel_reg="/^1[34578]{1}\d{9}$/";
             $email_reg="/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/";
             if(!preg_match($tel_reg,$telephone)){
                 $this->error('手机格式有误');
             } 
             if($email!=''){
                if(!preg_match($email_reg,$email)){
                    $this->error('邮箱格式有误');
                }
             }
             /*$zhiXiaIdArray = array(1,2,9,22);
             $province_con['id']=$province;
             $province_con['level']=PROVINCE_LEVEL;
             if(!in_array($province,$zhiXiaIdArray)){
                 $city_con['upid']=$province; 
             } 
             $city_con['id']=$city; 
             $district_con['upid']=$city;
             $district_con['id']=$district; 
             $province_result=$school_model->gtAddressByParameter($province_con);           
             $city_result=$school_model->gtAddressByParameter($city_con);           
             $district_result=$school_model->gtAddressByParameter($district_con);   
             if(empty($province) || empty($city_result) || empty($district_result)){
                 $this->error('地址填写有误');
             }  
             $school_edit_data['provice_id']=$province;
             $school_edit_data['city_id']=$city;
             $school_edit_data['district_id']=$district;
             $school_edit_data['school_name']=$school_name;
             $school_edit_data['school_address']=$school_address;*/
             
             /*$auth_edit_data['real_name']=$real_name;
             $auth_edit_data['telephone']=$telephone;
             $auth_edit_data['email']=$email;*/
             $school_edit_data['obligation_person']=$real_name;
             $school_edit_data['obligation_tel']=$telephone;
             $school_edit_data['obligation_email']=$email;
             $this->model->startTrans();
             if(!$school_model->updateSchoolData($school_id,$school_edit_data)){
                 $this->model->rollback();
                 $this->error('修改数据失败');
             }
             /*if(!$this->model->updateSchoolAdminData($admin_id,$auth_edit_data)){
                 $this->model->rollback();
                 $this->error('修改数据失败');
             }*/
             $this->model->commit();
             $this->redirect('User/userCenter');
         }else{ 
            $id=session('school.id');
            $school_category=C('SCHOOL_CATEGORY');
            $result=$this->model->getSchoolAdminData($id); 
            $province=$city=$district=array(); 
            if(!empty($result)){ 
                $province=$school_model->getProvince(); 
                $city=$school_model->getCityByProvince($result['province_id']);
                $district=$school_model->getDistrictByCity($result['city_id']);
                
                if($result['auth_id']!=null){
                    if($result['auth_end_time']>=time() && $result['auth_start_time']<time()){
                        $result['permissions']='团体VIP';
                        $result['auth_start_time']=date('Y-m-d',$result['auth_start_time']);
                        $result['auth_end_time']=date('Y-m-d',$result['auth_end_time']);
                    }else{
                        $result['permissions']='普通权限';
                        $result['auth_start_time']='';
                        $result['auth_end_time']='';
                    }
                }else{
                    $result['permissions']='普通权限';
                    $result['auth_start_time']='';
                    $result['auth_end_time']='';
                }
            } 
            $this->assign('province_list',$province);       
            $this->assign('city_list',$city);
            $this->assign('district_list',$district);
            $this->assign('data',$result);
            $this->assign('school_category',$school_category);  
            
            $this->display();
         }
     }
     
    
     
}
