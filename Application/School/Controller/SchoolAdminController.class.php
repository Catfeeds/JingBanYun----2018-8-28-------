<?php
namespace School\Controller;
use Think\Controller;
use Common\Common\CSV;
   
define('ENABLE_TEACHER_STATUS',1);
define('DISABLE_TEACHER_STATUS',0);
define('SCHOOL_FLAG_ENABLE',1);
define('SCHOOL_FLAG_DISABLE',0);
define('CONTROLLER_GROUP',true); 
define('CONTROLLER_MODULE_TYPE',1); 
define('FUNCTION_MODULE_TYPE',2);  
define('SCHOOL_ADMIN',1);
define('SCHOOL_CHILD_ADMIN',2); 
define('CHILD_ADMIN_FLAG',1);
define('DATA_UPDATE_FAILED','信息修改失败!');
define('DATA_ADD_FAILED','数据添加失败!');
define('ADMIN_SCHOOL_ROLE',3);

class SchoolAdminController extends Controller
{   

    public $model;
    public $page_size=20;  
    public function __construct() {
        parent::__construct();  
        $this->model=D('Auth_school_admin');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }
             
    
    /*
     * 管理员列表
     */
    public function adminList(){  
        if (!session('?school')) redirect(U('Login/login'));
        $this->denyChildAdminOperation();
        
        $filter['name']=getParameter('name','str',false); 
        $filter['real_name']=getParameter('real_name','str',false);    
        $filter['telephone']=getParameter('telephone','str',false);
        $filter['account_status']=I('status');      
        $order=getParameter('order','int',false);
        $order?$order='asc':$order='desc';
        
        $condition=array(); 
        $condition['parent_id']=session('school.id');
        $condition['school_id']=session('school.school_id');
        if(!empty($filter['name']))   $condition['auth_admin.name']=array('like', '%' . $filter['name']. '%');   
        if(!empty($filter['real_name']))   $condition['auth_admin.real_name']=array('like', '%' . $filter['real_name']. '%'); 
        if(!empty($filter['telephone']))   $condition['auth_admin.telephone']=array('like', '%' . $filter['telephone']. '%'); 
        if($filter['account_status']!='')   $condition['auth_admin.school_flag']=intval($filter['account_status']);
        
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        }
        
        $result=$this->model->getSchoolAdministartor($condition,$order);  
        
        $this->assign('condition_str',$condition_string);
        $this->assign('account_name',$filter['name']);
        $this->assign('real_name',$filter['real_name']);
        $this->assign('telephone',$filter['telephone']);
        $this->assign('account_status',$filter['account_status']);
        $this->assign('order',$order);
        
        $this->assign('list',$result['data']);
        $this->assign('page',$result['page']); 
        $this->display();
    }
    
    
    /*
     * 更改学校管理员禁用或启用状态
     */
    public function updateSchoolAdminStatus(){
        if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);
        $this->denyChildAdminOperation();
        
        $id=getParameter('id','int'); 
        $top_admin_id=session('school.id');
        $result=$this->model->getSchoolAdminData($id,$top_admin_id);
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{ 
            if($result['school_id']!=session('school.school_id')){
                $this->showjson(402,COMMON_FAILED_MESSAGE);
            }  
            if($result['admin_flag']){ 
                $status=$this->model->updateAdminStatus($id,SCHOOL_FLAG_DISABLE,CHILD_ADMIN_FLAG);
            }else{ 
                $status=$this->model->updateAdminStatus($id,SCHOOL_FLAG_ENABLE,CHILD_ADMIN_FLAG);
            }
            if($status){
                $this->showjson(200);
            }else{
                $this->showjson(403,COMMON_FAILED_MESSAGE);
            }
        }
        
    }
    
    
    /*
     * 查看管理员详情
     */
    public function adminDetail(){ 
        if (!session('?school')) redirect(U('Login/login'));
        $this->denyChildAdminOperation();
        
        $id=getParameter('id','int'); 
        $top_admin_id=session('school.id');
        $result=$this->model->getSchoolAdminData($id,$top_admin_id); 
        $permissions_arr=array(); 
        if(!empty($result)){   
            $module_permissions=$this->model->getAdminPermissions($id,true);     
            $funciton_permissions=$this->model->getAdminPermissions($id,false);    
            $permissions_arr=$this->mergePermissions($module_permissions,$funciton_permissions);  
        }   
        $this->assign('data',$result);
        $this->assign('permissions',$permissions_arr);
        $this->display();
    }
    
    
    /*
     * 拒绝子级管理员操作此控制器
     */
    public function denyChildAdminOperation($is_ajax=0){
        if(session('school.parent_id')!=0){
            if($is_ajax){
                echo $this->showjson(501,'无权进行操作'); 
            }else{
                $this->redirect(U('Index/index',array('auth'=>'1')));
            }
        }
    }
    
    /*
     * 检查学校子级管理员是否有操作权限
     */
    function check_permissions($is_axax=0,$is_iframe=0){
        $arr=array(1,2,3);  
        if(session('school.parent_id')!=0){
            $action=CONTROLLER_NAME.'/'.ACTION_NAME;    
            $permissions=session('school_permissions');
             
            if(!in_array($action,$permissions)){
                if($is_axax){
                    echo $this->showjson(501,'无权进行操作'); 
                }elseif ($is_iframe == true){
                    return 1;//针对ifream中的形式
                }
                else{
                    $this->redirect(U('Index/index',array('error'=>'1')));
                }
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
     * 创建子级管理员
     */
    public function createAdmin(){
        if (!session('?school')) redirect(U('Login/login'));
        $this->denyChildAdminOperation();
        
        if($_POST){  
            $name=getparameter('account_name','str');
            $password=getParameter('password','str');
            $real_name=getParameter('real_name','str');
            $telephone=getParameter('telephone','str');
            $email=getParameter('email','str',false);
            $permissions_arr=$_POST['per_id'];     
            $id=session('school.id');
            $school_id=session('school.school_id');
            
            $tel_reg="/^1[34578]{1}\d{9}$/";
            $email_reg="/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/";
            if(!preg_match($tel_reg,$telephone)){
                $this->error('手机格式有误');
            } 
            if($email!=''){
                if(!preg_match($email_reg,$email)){
                    $this->error('邮箱格式有误!');
                }
            }
            $admin_data['parent_id']=$id;
            $admin_data['school_id']=$school_id;
            $admin_data['role']=ADMIN_SCHOOL_ROLE;
            $admin_data['name']=$name;
            $admin_data['password']=sha1($password);
            $admin_data['real_name']=$real_name;
            $admin_data['telephone']=$telephone;
            $admin_data['email']=$email;
            $admin_data['create_at']=time();
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
            if(!($insert_id=$this->model->addAdmin($admin_data))){    
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
            $this->redirect('SchoolAdmin/adminList');
            
        }else{
            $school_model=D('Dict_schoollist');
            $province_result=$school_model->getProvince(); 
            $module_permissions_all=$this->model->getAllPermissions(CONTROLLER_MODULE_TYPE);   
            $fundion_permissions_all=$this->model->getAllPermissions(FUNCTION_MODULE_TYPE);
                
            $permissions=$this->mergePermissions($module_permissions_all, $fundion_permissions_all);
            foreach ($permissions as $key=>$item){
                if($item['module_action'] == 'Class/classList'){
                    foreach ($permissions[$key]['child'] as $itemTwo){
                        if($itemTwo['module_action'] == 'Class/classList' || $itemTwo['module_action'] == 'Class/addClass' || $itemTwo['module_action'] == 'Class/importClassView' || $itemTwo['module_action'] == 'Class/importClass' || $itemTwo['module_action'] == 'Class/downloadClssDemo' || $itemTwo['module_action'] == 'Class/getGradeBySchool' || $itemTwo['module_action'] == 'Class/getClassByGrade' || $itemTwo['module_action'] == 'Class/deleteClass' || $itemTwo['module_action'] == 'Common/classRemoveTeacher' || $itemTwo['module_action'] == 'Class/classDetail' || $itemTwo['module_action'] == 'Class/getTeacherListByClass' || $itemTwo['module_action'] == 'Class/getHomeWordListByClass' || $itemTwo['module_action'] == 'Class/getStudentListByClass'){
                            $newArray[] = $itemTwo;
                        }
                    }
                    unset($permissions[$key]['child']);
                    $permissions[$key]['child'] = $newArray;
                }
            }
            $this->assign('permissions_list',$permissions);
            $this->assign('province_list',$province_result);  
            
            $this->display();
        } 
    }
    
     
    /*
     * 修改子级管理员
     */
    public function updateAdmin(){  
        if (!session('?school')) redirect(U('Login/login'));
        $this->denyChildAdminOperation();
        
        if($_POST){     
            $id=getParameter('id','int'); 
            $name=getparameter('account_name','str');
            $password=getParameter('password','str');
            $real_name=getParameter('name','str');      
            $telephone=getParameter('telephone','str');
            $email=getParameter('email','str',false);        
            $permissions_arr=$_POST['per_id'];
            
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
            $current_admin_parent_id=session('school.id');        
            $result=$this->model->getSchoolAdminData($id);     
            if(empty($result)){
                $this->error('参数错误!');
            }else{
                if($result['parent_id']!=$current_admin_parent_id){
                    $this->error('无权操作此管理员!');
                }
            }
            $result=$this->model->getAadminDataByPassword($id,$password);     
            if(empty($result)){
                $admin_data['password']=sha1($password);
            }
           
            $admin_data['name']=$name; 
            $admin_data['real_name']=$real_name;
            $admin_data['telephone']=$telephone;
            $admin_data['email']=$email;
            if(!empty($permissions_arr)){ 
                $per_con['id']=array('in',$permissions_arr);  
                $per_con['parent_id']=array('neq',0);
                $per_result=$this->model->getPermissionsData($per_con);
                $parent_id_arr=array_column($per_result,'parent_id');
                $permission_array=array_merge($permissions_arr,$parent_id_arr);
                $permission_array=array_unique($permission_array);
            }
            $admin_id=$id;
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
            if(!$this->model->updateSchoolAdmin($admin_id,$admin_data)){
                $this->model->rollback();
                $this->error('入库失败!');
            } 
            $this->model->commit();
            $this->redirect('SchoolAdmin/adminList',array('id'=>$id));
            
        }else{
            $school_model=D('Dict_schoollist');
            $id=getParameter('id','int');
            $current_admin_id=session('school.id'); 
            $result=$this->model->getSchoolAdminData($id,$current_admin_id);
            if(!empty($result)){ 
                $module_permissions_all=$this->model->getAllPermissions(CONTROLLER_MODULE_TYPE);
                $fundion_permissions_all=$this->model->getAllPermissions(FUNCTION_MODULE_TYPE); 
                $module_permissions=$this->model->getAdminPermissions($id,true);
                $funciton_permissions=$this->model->getAdminPermissions($id,false);
                $module_permissions=$this->managementPermissionsSelected($module_permissions_all,$module_permissions);
                $funciton_permissions=$this->managementPermissionsSelected($fundion_permissions_all,$funciton_permissions);
                $permissions_list=$this->mergePermissions($module_permissions, $funciton_permissions);

                foreach ($permissions_list as $key=>$item){
                    if($item['module_action'] == 'Class/classList'){
                        foreach ($permissions_list[$key]['child'] as $itemTwo){
                            if($itemTwo['module_action'] == 'Class/classList' || $itemTwo['module_action'] == 'Class/addClass' || $itemTwo['module_action'] == 'Class/importClassView' || $itemTwo['module_action'] == 'Class/importClass' || $itemTwo['module_action'] == 'Class/downloadClssDemo' || $itemTwo['module_action'] == 'Class/getGradeBySchool' || $itemTwo['module_action'] == 'Class/getClassByGrade'  || $itemTwo['module_action'] == 'Class/deleteClass' || $itemTwo['module_action'] == 'Common/classRemoveTeacher' || $itemTwo['module_action'] == 'Class/classDetail' || $itemTwo['module_action'] == 'Class/getTeacherListByClass' || $itemTwo['module_action'] == 'Class/getHomeWordListByClass' || $itemTwo['module_action'] == 'Class/getStudentListByClass'){
                                $newArray[] = $itemTwo;
                            }
                        }
                        unset($permissions_list[$key]['child']);
                        $permissions_list[$key]['child'] = $newArray;
                    }
                }


                $province=$school_model->getProvince(); 
                $city=$school_model->getCityByProvince($result['province_id']); 
                $district=$school_model->getDistrictByCity($result['city_id']);

                $this->assign('permissions_list',$permissions_list);
                $this->assign('province_list',$province);
                $this->assign('city_list',$city);
                $this->assign('district_list',$district); 
            }
            $this->assign('data',$result);  
            $this->display();  
        } 
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
     * 根据省份获得城市
     */
    public function getCityByProvince(){
        if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $school_model=D('Dict_schoollist');
        $id=getParameter('province_id','int');
        $result=$school_model->getCityByProvince($id);
        $this->showjson(200,'',$result);
    }
    
    
    /*
     * 根据城市获得县区
     */
    public function getDistrictByCity(){
        if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $school_model=D('Dict_schoollist');
        $id=getParameter('city_id','int');
        $result=$school_model->getDistrictByCity($id);
        $this->showjson(200,'',$result);
    }
    
    
    /*
     * 根据区县获得学校
     */
    public function getSchoolByDistrict(){
        if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $school_model=D('Dict_schoollist');
        $id=getParameter('district_id','int');
        $result=$school_model->getSchoolByDistrict($id);
        $this->showjson(200,'',$result);
    }
    
    
    /*
     * 删除管理员
     * 只能删除子级管理员
     */
    public function deleteAdminManage(){
        if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);
        $this->denyChildAdminOperation(true);
        
        $id=getParameter('id','int');
        $result=$this->model->getSchoolAdminData($id,CHILD_ADMIN_FLAG);
        if(!empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE); 
        }
        if($this->model->deleteSchoolAdmin($id)){
            $this->showjson(200);
        }else{
            $this->showjson(402,COMMON_FAILED_MESSAGE);
        } 
    }
    
    
    /*
     * 批量导出管理员
     */
    public function exportedSchoolAdmin(){
        if (!session('?school')) redirect(U('Login/login'));
        $this->denyChildAdminOperation();
        
        if(empty($_POST)){
            $this->error('参数错误');
        }else{ 
            $condition_arr=I('hid'); 
            $condition['parent_id']=session('school.id');
            $condition['auth_admin.id']=array('in',$condition_arr);
            $data=$this->model->getSchoolAdminDataAll($condition);   
            
            $str="管理员姓名,管理员账号,管理员手机号,状态\n";
            $str=iconv('utf-8','gbk', $str);
            foreach($data as $val){
                $real_name=iconv('utf-8','gbk', $val['real_name']);
                $acoount_name=iconv('utf-8','gbk', $val['account']);
                $telephone=iconv('utf-8','gbk', $val['telephone']);
                if($val['school_flag']==0){
                    $status='停用';
                }else{
                    $status='正常';
                }
                $status=iconv('utf-8','gbk', $status);
                $str.=$real_name.",".$acoount_name.",".$telephone.",".$status."\n";
            }
            $filename=date('Ymd').rand(0,1000).'admin'.'.csv';
            $csv=new CSV();
            //export disable
            $csv->downloadFileCsv($filename,$str);
        }
    }
    
    
    /*
     * 导出全部管理员
     */
    public function exportedSchoolAdminAll(){
        if (!session('?school')) redirect(U('Login/login'));
        $this->denyChildAdminOperation();
        
        $filter['name']=getParameter('name','str',false); 
        $filter['real_name']=getParameter('real_name','str',false);
        $filter['telephone']=getParameter('telephone','str',false);
        $filter['account_status']=getParameter('status','int',false); 
        $order=getParameter('order','int',false);
        $order?$order='asc':$order='desc';
        
        $condition['parent_id']=session('school.id');
        if(!empty($filter['name']))   $condition['auth_admin.student_name']=array('like', '%' . $filter['name']. '%');   
        if(!empty($filter['real_name']))   $condition['auth_admin.real_name']=array('like', '%' . $filter['real_name']. '%'); 
        if(!empty($filter['telephone']))   $condition['auth_admin.telephone']=array('like', '%' . $filter['telephone']. '%'); 
        if(!empty($filter['account_status']))   $condition['auth_admin.school_flag']=$filter['account_status'];  
        $data=$this->model->getSchoolAdminDataAll($condition,$order);  
        $str="管理员姓名,管理员账号,管理员手机号,状态\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($data as $val){
            $real_name=iconv('utf-8','gbk', $val['real_name']);
            $acoount_name=iconv('utf-8','gbk', $val['account']);
            $telephone=iconv('utf-8','gbk', $val['telephone']);
            if($val['school_flag']==0){
                $status='停用';
            }else{
                $status='正常';
            }
            $status=iconv('utf-8','gbk', $status);
            $str.=$real_name.",".$acoount_name.",".$telephone.",".$status."\n";
        }
        $filename=date('Ymd').rand(0,1000).'admin'.'.csv';
        $csv=new CSV();
        //export disable
        $csv->downloadFileCsv($filename,$str);
    }
    
     
}
