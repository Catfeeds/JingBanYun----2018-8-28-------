<?php
namespace School\Controller;
use Think\Controller;
use Think\Verify;
define('LOGIN_FAILED','账号或密码错误');
define('VERIFT_FAILED','验证码有误');
define('DISABLED_SCHOOL','学校已被禁用,无法登录');
define('DISABLED_ACCOUNT','账号已被禁用,无法登陆');
class LoginController extends Controller
{   

    public $model;
    public $page_size=20; 
                //permissions
    public function __construct() {
        parent::__construct();  
        $this->model=D('Auth_admin');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }
          
    
    
    function login(){
        if($_POST){ 
            $user_name=getParameter('userName','str');
            $password=getParameter('password','str');
            $verify_code=getParameter('code','str');   
            $show_flag=2;
            $verift_status=$this->check_verify($show_flag,$verify_code);    
            if(!$verift_status){
                $this->showjson(401,VERIFT_FAILED);
            }
            if($user_name=='' || $password==''){
                $this->showjson(402,LOGIN_FAILED);
            }else{
                $result=$this->model->userLogin($user_name,sha1($password),1);
                if(empty($result)){
                    $this->showjson(403,LOGIN_FAILED);
                }
                $school_model=D('Dict_schoollist');             
                $school_result=$school_model->getSchoolInfo($result['school_id']);
                if($result['school_flag'] == 0){
                    $this->showjson(405,DISABLED_ACCOUNT);
                }
                if($school_result['flag']!=1){
                    $this->showjson(404,DISABLED_SCHOOL);
                }else{
                    $this->showjson(200); 
                }
                
            } 
        }else{
            $this->display();
        }
    }

    
    function produceVerifyCode(){ 
        $config = array(
             'fontSize' => 20,
             'length' => 4, // 验证码位数
             'imageH' => 40, );
         $verify = new Verify($config);
         $verify->entry();
    }
    
    
    public function check_verify($show_flag=1,$code=''){  
        $config = array(
            'reset' => false
        );
         $verify = new \Think\Verify($config); 
         if($show_flag==1){
             $verify_code=getParameter('code','str');
             $res = $verify->check($verify_code);   
             if($res){  
                 $this->showjson(200);
             }else{
                 $this->showjson(401);
             }
         }else{
             $verify_code=$code;
             $res = $verify->check($verify_code);  
             return $res;
         }  
    }
     
      
    public function login_entry(){ 
        if($_POST){
            $user_name=getParameter('userName','str');
            $password=getParameter('password','str');
            $verify_code=getParameter('code','str');
            $show_flag=2;
            $verift_status=$this->check_verify($show_flag,$verify_code);        
            if(!$verift_status){
                $this->error('异常操作!');
            }   
            if($user_name=='' || $password==''){    
                $this->error('异常操作!');  
            }else{
                $result=$this->model->userLogin($user_name,sha1($password),1);      
                if(!empty($result)){ 
                    //登录成功
                    $school_model=D('Dict_schoollist');
                    $school_result=$school_model->getSchoolInfo($result['school_id']);  
                    if($school_result['flag']!=1){
                        $this->error('异常操作!');
                    }  
                    $result['school_name']=$school_result['school_name'];
                    session_start();
                    session('school', $result);     
                    $this->set_permissions_menu($result['parent_id'],$result['id']); 
                    
                    $this->redirect(U('Index/index'));
                }else{
                    $this->error('异常操作!');
                }
            }
            
        }else{
            $this->error('异常操作!');
        }
    }
    
    //设置权限和顶部菜单
    public function set_permissions_menu($parent_id,$login_id){
        if($parent_id!=0){    
            $school_admin_model=D('Auth_school_admin');
            $module_permissions=$school_admin_model->getAdminPermissions($login_id,true);       
            $funciton_permissions=$school_admin_model->getAdminPermissions($login_id,false);  
            $permissions_arr=array_merge($module_permissions,$funciton_permissions);    
            $permissions=array();
            foreach($permissions_arr as $val){
                $permissions[]=$val['module_action'];       
            } 
            session('school_permissions',$permissions);
            //部菜单      
            $top_menu=array();
            $top_menu_config=C('ADMIN_TOP_MENU');
            $top_menu['首页']='index.php?m=School&c=Index&a=index';
            foreach($module_permissions as $module_val){
                foreach($top_menu_config as $menu_key=>$menu_val){
                    if($module_val['module_name']==$menu_key){
                        $top_menu[$menu_key]=$menu_val;
                    }
                }
            } 
            session('school_top_menu',$top_menu);
        }else{ 
            //头部菜单
            $top_menu_config=C('ADMIN_TOP_MENU');
            session('school_top_menu',$top_menu_config); 
        }
    }
     
    
    public function logout(){
        session('school', null);
        session('school_permissions', null);
        session('school_top_menu', null);
        $this->redirect(U('Login/login'));
    }
}
