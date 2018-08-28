<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Common\CSV;
 
define('ENABLE_TEACHER_STATUS',1);
define('DISABLE_TEACHER_STATUS',0);
define('SCHOOL_FLAG_ENABLE',1); 
define('SCHOOL_FLAG_DISABLE',0);
define('CONTROLLER_MODULE',1);
define('FUNCTION_MODULE',2);
define('SCHOOL_ADMIN_FLAG',1);
define('SCHOOL_ADMIN',0); 
define('CONTROLLER_MODULE_TYPE',1);
define('FUNCTION_MODULE_TYPE',2);
define('ADMIN_SCHOOL_ROLE',3);

class SchoolAdminController extends Controller
{   

    public $model;
    public $page_size=20; 
                //permissions
    public function __construct() {
        parent::__construct();  
        $this->model=D('Auth_school_admin');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }
             
    
    /*
     * 管理员列表
     */
    public function adminList(){
        if (!session('?admin')) redirect(U('Login/login')); 
        
        $filter['name']=getParameter('name','str',false);
        $filter['school_name']=getParameter('school_name','str',false);
        $filter['school_code']=getParameter('school_code','str',false);
        $filter['real_name']=getParameter('real_name','str',false);
        $filter['telephone']=getParameter('telephone','str',false);
        $filter['account_status']=$_GET['status'];
        $filter['administartor_type']=$_GET['type'];
        $order=getParameter('order','int',false);
        $order?$order='asc':$order='desc';
        
        $condition=array();
        if(!empty($filter['name']))   $condition['auth_admin.name']=array('like', '%' . $filter['name']. '%');
        if(!empty($filter['school_name']))   $condition['dict_schoollist.school_name']=array('like', '%' . $filter['school_name']. '%');  
        if(!empty($filter['school_code']))   $condition['dict_schoollist.school_code']=array('like', '%' . $filter['school_code']. '%');  
        if(!empty($filter['real_name']))   $condition['auth_admin.real_name']=array('like', '%' . $filter['real_name']. '%'); 
        if(!empty($filter['telephone']))   $condition['auth_admin.telephone']=array('like', '%' . $filter['telephone']. '%'); 
        if($filter['account_status']!='')   $condition['auth_admin.school_flag']=intval($filter['account_status']); 
        if($filter['administartor_type']!=''){
            if($filter['administartor_type']==SCHOOL_ADMIN_FLAG){    
                $condition['auth_admin.parent_id']=SCHOOL_ADMIN;
            }else{
                $condition['auth_admin.parent_id']=array('neq',SCHOOL_ADMIN);      
            }
        } 
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        } 
        $result=$this->model->getSchoolAdministartor($condition,$order);    
        
        $this->assign('condition_str',$condition_string);
        $this->assign('name',$filter['name']);
        $this->assign('school_name',$filter['school_name']);
        $this->assign('school_code',$filter['school_code']);
        $this->assign('real_name',$filter['real_name']);
        $this->assign('telephone',$filter['telephone']);
        $this->assign('account_status',$filter['account_status']);
        $this->assign('administartor_type',$filter['administartor_type']);
        $this->assign('order',$order);
        
        $this->assign('list',$result['data']);
        $this->assign('page',$result['page']);
        
        $this->display();
    }
    
    
    /*
     * 更改学校管理员禁用或启用状态
     */
    public function updateSchoolAdminStatus(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $id=getParameter('id','int');
        $result=$this->model->getSchoolAdminData($id);      
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{
            if($result['admin_flag']){
                $status=$this->model->updateAdminStatus($id,SCHOOL_FLAG_DISABLE);
            }else{
                $status=$this->model->updateAdminStatus($id,SCHOOL_FLAG_ENABLE);
            }
            if($status){
                $this->showjson(200);
            }else{
                $this->showjson(402,COMMON_FAILED_MESSAGE);
            }
        }
        
    }
     
    
    
    /*
     * 把子权限放入功能权限数组下,进行合并.
     */
    public function mergePermissions($module_per,$function_per){
        $big_data=array();
        for($i=0;$i<count($module_per);$i++){  
            $module_per[$i]['child']=array();
            for($j=0;$j<count($function_per);$j++){
                if($module_per[$i]['id']==$function_per[$j]['parent_id']){
                    $module_per[$i]['child'][]=$function_per[$j];
                }else{
                    continue;
                }
            }
            $big_data[]=$module_per[$i];
        }
        return $big_data;
    }
    
    
    /*
     * 全部权限和子级管理员拥有的权限进行比对,相同的加选中状态.
     */
    public function managementPermissionsSelected($allPer,$per){
        foreach($allPer as $all_key=>$all_val){
            foreach($per as $key=>$val){
                if($all_val['id']==$val['id']){
                    $allPer[$all_key]['selected']=1;
                }else{
                    continue;
                }
            }
        }
        return $allPer;
    }
    
    
    
    /*
     * 查看管理员详情
     */
    public function adminDetail(){ 
        if (!session('?admin')) redirect(U('Login/login'));
        
        /*echo __FUNCTION__;
        echo "<hr>";
        echo __CONTROLLER__ ;die;*/  
        $id=getParameter('id','int');
        $result=$this->model->getSchoolAdminData($id); 
        $owner_permissions=array();
        if(!empty($result)){
            if($result['parent_id']!=0){
                $controller_permissions=$this->model->getAdminPermissions($id,true);   
                $funciton_permissions=$this->model->getAdminPermissions($id,false);       
                $owner_permissions=$this->mergePermissions($controller_permissions,$funciton_permissions);      
            }
        }
        $this->assign('permissions_list',$owner_permissions);   
        $this->assign('data',$result);
        
        $this->display();
    }
    
    
    /*
     * 创建管理员
     */
    public function createAdmin(){
        if (!session('?admin')) redirect(U('Login/login')); 
        
        if(!empty($_POST)){ 
            $data['name']=getParameter('account_name','str');
            $data['password']=sha1(getParameter('password','str')); 
            $data['real_name']=getParameter('real_name','str');
            $data['telephone']=getParameter('telephone','str');
            $data['email']=getParameter('email','str',false);
            $data['school_id']=getParameter('school_id','int');
            $permissions_arr=$_POST['per_id'];      
            $tel_reg="/^1[34578]{1}\d{9}$/";
            $email_reg="/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/";
            if(!preg_match($tel_reg,$data['telephone'])){    
                $this->error('手机格式有误!');
            } 
            if($email!=''){
                if(!preg_match($email_reg,$data['email'])){
                    $this->error('邮箱格式有误!');
                }
            }
            $result=$this->model->getSchoolAdminBYSchool($data['school_id']);  
            if(empty($result)){
                $this->error('该学校下无学校管理员,不允许进行更改!');
            }
            $data['parent_id']=$result['id'];
            $data['role']=ADMIN_SCHOOL_ROLE;
            $data['create_at']=time();
            if(!empty($permissions_arr)){ 
                $per_con['id']=array('in',$permissions_arr);  
                $per_con['parent_id']=array('neq',0);
                $per_result=$this->model->getPermissionsData($per_con);
                $parent_id_arr=array_column($per_result,'parent_id');
                $permission_array=array_merge($permissions_arr,$parent_id_arr);
                $permission_array=array_unique($permission_array);
            }
            //判断账号是否已存在
            $admin_con['auth_admin.name']=$admin_data['name'];
            $count=$this->model->getSchoolAdministartorCount($admin_con);
            if($count){
                $this->error('该账号已被注册!');
            }
            $this->model->startTrans();
            if(!($insert_id=$this->model->addAdmin($data))){    
                $this->model->rollback();
                $this->error('入库失败!');
            } 
            if(!empty($permission_array)){
                if(!$this->model->addAdminPermissions($insert_id,$permission_array)){  
                    $this->model->rollback();
                    $this->error('入库失败!');
                }
            }
            $this->model->commit();
            $this->redirect(U('SchoolAdmin/adminList'));
        }else{
            $school_model=D('Dict_schoollist');
            $province_result=$school_model->getProvince(); 
            $module_permissions_all=$this->model->getAllPermissions(CONTROLLER_MODULE_TYPE);   
            $fundion_permissions_all=$this->model->getAllPermissions(FUNCTION_MODULE_TYPE);
                
            $permissions=$this->mergePermissions($module_permissions_all, $fundion_permissions_all);
            $this->assign('permissions_list',$permissions);
            $this->assign('province_list',$province_result); 
            $this->display();
        }
    } 
    
    
    /*
     * 修改管理员
     */
    public function adminModify(){    
        if (!session('?admin')) redirect(U('Login/login')); 
        
        if(!empty($_POST)){  
            $admin_id=getParameter('admin_id','int');
            $data['name']=getParameter('account_name','str');
            $data['password']=sha1(getParameter('password','str')); 
            $data['real_name']=getParameter('real_name','str');
            $data['telephone']=getParameter('telephone','str');
            $data['email']=getParameter('email','str',false);
            $data['school_flag']=$_POST['account_status'];  
            $permissions_arr=$_POST['per_id'];  
            $tel_reg="/^1[34578]{1}\d{9}$/";
            $email_reg="/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/";
            if(!preg_match($tel_reg,$data['telephone'])){    
                $this->error('手机格式有误!');
            } 
            if($email!=''){
                if(!preg_match($email_reg,$data['email'])){
                    $this->error('邮箱格式有误!');
                }
            }
            
            $result=$this->model->getSchoolAdminData($admin_id);
            if(empty($result)){
                $this->error('参数错误');
            }
            $parent_id=$result['parent_id'];
            if($result['parent_id']!=0){
                $data['school_id']=getParameter('school_id','int');
                $result=$this->model->getSchoolAdminBYSchool($data['school_id']);  
                if(empty($result)){
                    $this->error('该学校下无学校管理员,不允许就行更改!');
                }
                $data['parent_id']=$result['id'];
            } 
            
            
            $result=$this->model->getSchoolAdminData($admin_id,0,$data['password']);
            if(empty($result)){
                $data['password']=sha1($data['password']);
            }   
            if($parent_id!=0){
                if(!empty($permissions_arr)){ 
                    $per_con['id']=array('in',$permissions_arr);  
                    $per_con['parent_id']=array('neq',0);
                    $per_result=$this->model->getPermissionsData($per_con);
                    $parent_id_arr=array_column($per_result,'parent_id');
                    $permission_array=array_merge($permissions_arr,$parent_id_arr);
                    $permission_array=array_unique($permission_array);
                } 
                //清空权限,再插
                $this->model->startTrans();
                if(!$this->model->deleteAdminPermissions($admin_id)){   
                    $this->model->rollback();
                    $this->error('入库失败!');
                }
                if(!empty($permission_array)){
                    if(!$this->model->addAdminPermissions($admin_id,$permission_array)){
                        $this->model->rollback();
                        $this->error('入库失败!');
                    }
                }
                if(!$this->model->updateSchoolAdmin($admin_id,$data)){
                    $this->model->rollback();
                    $this->error('入库失败!');
                }
                $this->model->commit();
            }else{
                if(!$this->model->updateSchoolAdminData($admin_id,$data)){  
                    $this->error('入库失败3!');
                }
            }
            $this->redirect(U('SchoolAdmin/adminList'),array('id'=>$admin_id));
            
        }else{
            $school_model=D('Dict_schoollist');
            $id=getParameter('id','int');
            $result=$this->model->getSchoolAdminData($id);  
            $province_result=array();
            $city_result=array();
            $district_result=array();
            $school_result=array();
            if(!empty($result)){
                $province_result=$school_model->getProvince(); 
                $city_result=$school_model->getCityByProvince($result['province_id']);
                $district_result=$school_model->getDistrictByCity($result['city_id']);
                $school_result=$school_model->getSchoolByDistrict($result['district_id']);
                
                $module_permissions_all=$this->model->getAllPermissions(CONTROLLER_MODULE_TYPE);   
                $fundion_permissions_all=$this->model->getAllPermissions(FUNCTION_MODULE_TYPE);         
                $module_permissions=$this->model->getAdminPermissions($id,true);
                $funciton_permissions=$this->model->getAdminPermissions($id,false);         
                 
                $module_permissions=$this->managementPermissionsSelected($module_permissions_all,$module_permissions); 
                $funciton_permissions=$this->managementPermissionsSelected($fundion_permissions_all,$funciton_permissions);
                $owner_permissions=$this->mergePermissions($module_permissions, $funciton_permissions);
            } 
            $this->assign('owner_permissions',$owner_permissions);      
            $this->assign('province_list',$province_result);        
            $this->assign('city_list',$city_result);    
            $this->assign('district_list',$district_result);
            $this->assign('school_list',$school_result);
            $this->assign('data',$result);              
            $this->display();
        }
    }
    
    
    
    /*
     * 批量导出管理员
     */
    public function exportedSchoolAdmin(){
        if (!session('?admin')) redirect(U('Login/login')); 
        
        set_time_limit(0); 
        if(empty($_POST)){
            $this->error('参数错误');
        }else{
            $condition_arr=I('hid'); 
            $condition['auth_admin.id']=array('in',$condition_arr);
            $data=$this->model->getSchoolAdminDataAll($condition);      
            
            $str="管理员姓名,管理员账号,所属学校,管理员手机号,账号类型,账号状态\n";
            $str=iconv('utf-8','gbk', $str);
            foreach($data as $v){
                    $real_name=iconv('utf-8','gbk', $v['real_name']);
                    $account=iconv('utf-8','gbk', $v['account']);
                    $school_name=iconv('utf-8','gbk', $v['school_name']);
                    $telephone=iconv('utf-8','gbk', $v['telephone']); 
                    if($v['parent_id']==0){
                        $admin_type='学校管理员';
                    }else{
                        $admin_type='学校子级管理员';
                    }
                    $admin_type=iconv('utf-8','gbk', $admin_type); 
                    if($v['school_flag']==0){
                        $school_flag='停用';
                    }else{
                        $school_flag='正常';
                    }  
                    $school_flag=iconv('utf-8','gbk', $school_flag);

                    $str.=$real_name.",".$account.",".$school_name.",".$telephone.",".$admin_type.",".$school_flag."\n";
                }
                $filename=date('Ymd').rand(0,1000).'SchoolAdmin'.'.csv';
                $csv=new CSV();
                $csv->downloadFileCsv($filename,$str);
        }
    }
    
     
    /*
     * 导出全部学校管理员
     */
    public function exportedSchoolAdminAll(){
        if (!session('?admin')) redirect(U('Login/login')); 
        
        set_time_limit(0);
        $filter['name']=getParameter('name','str',false);
        $filter['school_name']=getParameter('school_name','str',false);
        $filter['school_code']=getParameter('school_code','str',false);
        $filter['real_name']=getParameter('real_name','str',false);
        $filter['telephone']=getParameter('telephone','str',false);
        $filter['account_status']=$_GET['status'];
        $filter['administartor_type']=$_GET['type'];
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition=array();
        if(!empty($filter['name']))   $condition['auth_admin.name']=array('like', '%' . $filter['name']. '%');
        if(!empty($filter['school_name']))   $condition['dict_schoollist.school_name']=array('like', '%' . $filter['school_name']. '%');  
        if(!empty($filter['school_code']))   $condition['dict_schoollist.school_code']=array('like', '%' . $filter['school_code']. '%');  
        if(!empty($filter['real_name']))   $condition['auth_admin.real_name']=array('like', '%' . $filter['real_name']. '%'); 
        if(!empty($filter['telephone']))   $condition['auth_admin.telephone']=array('like', '%' . $filter['telephone']. '%'); 
        if($filter['account_status']!='')   $condition['auth_admin.school_flag']=intval($filter['account_status']); 
        if($filter['administartor_type']!=''){
            if($filter['administartor_type']==SCHOOL_ADMIN_FLAG){    
                $condition['auth_admin.parent_id']=SCHOOL_ADMIN;
            }else{
                $condition['auth_admin.parent_id']=array('neq',SCHOOL_ADMIN);      
            }
        }
        $data=$this->model->getSchoolAdminDataAll($condition,$order);   
        
        $str="管理员姓名,管理员账号,所属学校,管理员手机号,账号类型,账号状态\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($data as $v){
                $real_name=iconv('utf-8','gbk', $v['real_name']);
                $account=iconv('utf-8','gbk', $v['account']);
                $school_name=iconv('utf-8','gbk', $v['school_name']);
                $telephone=iconv('utf-8','gbk', $v['telephone']); 
                if($v['parent_id']==0){
                    $admin_type='学校管理员';
                }else{
                    $admin_type='学校子级管理员';
                }
                $admin_type=iconv('utf-8','gbk', $admin_type); 
                if($v['school_flag']==0){
                    $school_flag='停用';
                }else{
                    $school_flag='正常';
                }  
                $school_flag=iconv('utf-8','gbk', $school_flag);
                
                $str.=$real_name.",".$account.",".$school_name.",".$telephone.",".$admin_type.",".$school_flag."\n";
            }
            $filename=date('Ymd').rand(0,1000).'SchoolAdmin'.'.csv';
            $csv=new CSV();
            $csv->downloadFileCsv($filename,$str);
    }
    
}
