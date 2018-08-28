<?php
namespace Exercise\Controller;
use Think\Controller;
use Think\Verify;
class LoginController extends Controller
{

    public $model;
    public $page_size=20;
                //permissions
    public function __construct() {
        parent::__construct();
        $this->model=D('Exercises_account');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }
     function login()
     {

         if ($_POST) {
             $user_name = getParameter('userName', 'str');
             $password = getParameter('password', 'str');
             $verify_code = getParameter('code', 'str');
             $show_flag = 2;
             $verift_status = $this->check_verify($show_flag, $verify_code);
             if (!$verift_status) {
                 $this->showjson(401, VERIFT_FAILED);
             }
             if ($user_name == '' || $password == '') {
                 $this->showjson(402, LOGIN_FAILED);
             } else {
                 $result = $this->model->userLogin($user_name, sha1($password), 2);
                 if (!empty($result) && $result['account_status'] == ACCOUNT_STATUS_NORMAL && $result['delete_status'] == DELETE_STATUS_FALSE) {
                     $this->showjson(200);
                 } else if($result['account_status'] == 1){
                     $this->showjson(404, '账号被停用');
                 } else
                 {
                     $this->showjson(403, '账号或密码错误');
                 }
             }
         } else {
             $this->display();
         }
     }

    function produceVerifyCode(){
        $config = array(
            'fontSize' => 20,
            'useCurve'  =>  false,            // 是否画混淆曲线
            'useNoise'  =>  false,            // 是否添加杂点
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
                $result=$this->model->userLogin($user_name,sha1($password));
                //TODO:login ip confinement
                if(!empty($result) && $result['account_status'] == ACCOUNT_STATUS_NORMAL && $result['delete_status'] == DELETE_STATUS_FALSE){
                    //登录成功
                    session_start();
                    session('admin', $result);
                    $this->set_permissions_menu($result['role']);
                    $this->redirect(U('Index/index'));
                }else if($result['delete_status'] == 1){
                    $this->error('账号被删除');
                } else if($result['account_status'] == 1){
                    $this->error('账号被停用');
                } else{
                    $this->error('异常操作!');
                }
            }

        }else{
            $this->error('异常操作!');
        }
    }

    //设置权限和顶部菜单
    public function set_permissions_menu($role){

            $exercises_auth_permissions_model=D('Exercises_auth_permissions');
            $module_permissions=$exercises_auth_permissions_model->getPermissionsByRole($role);
            $permissions=array();
            foreach($module_permissions as $val){
                $permissions[]=$val['module_action'];
            }
            session('exercises_permissions',$permissions);
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
            session('exercises_top_menu',$top_menu);
    }

    public function logout(){
        session('admin', null);
        session('exercises_permissions', null);
        session('exercises_top_menu', null);
        session('school_top_menu',null);
        $this->redirect(U('Login/login'));
    }

}
