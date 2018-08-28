<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify;
define('LOGIN_FAILED','账号或密码错误');
define('VERIFT_FAILED','验证码有误');

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
                $result=$this->model->userLogin($user_name,sha1($password),2);    
                if(!empty($result)){ 
                    $this->showjson(200); 
                }else{
                    $this->showjson(403,LOGIN_FAILED);
                }
            } 
        }else{
            $this->display();
        }
    }

    
    function produceVerifyCode(){ 
        $config = array(
             'fontSize' => 20,
             'useNoise' => false,
             'useCurve' => false,
             'length' => 4, // 验证码位数
             'imageH' => 40);
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
                $result=$this->model->userLogin($user_name,sha1($password),2); 
                if(!empty($result)){ 
                    //登录成功
                    session_start();
                    session('admin', $result);
                    //refresh activity online status
                    $list = A('Admin/Activities')->getUnProcessColumnActivity();
                    session('admin.message',$list);
                    $this->redirect(U('School/schoolList'));
                }else{
                    $this->error('异常操作!');
                }
            }
            
        }else{
            $this->error('异常操作!');
        }
    }
    
    
    public function logout(){
        session('admin', null);
        $this->redirect(U('Login/login'));
    }
}
