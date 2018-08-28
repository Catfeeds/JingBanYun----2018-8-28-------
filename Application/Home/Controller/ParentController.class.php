<?php
namespace Home\Controller;

use Think\Controller;
use Common\Common\SMS;
use Common\Common\DES3;
use Common\Common\REDIS;
use Think\Verify;
define('PARENT_IMAGE_PRODUCE_ID',33);
define('PARENT_FORGET_PASSWORD_PRODUCE_ID',63);

class ParentController extends PublicController
{
    public $c_a = '';
    public function __construct() {
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));
        $this->c_a = CONTROLLER_NAME."_".ACTION_NAME;
    }

    public function array_remove($data, $key){
    if(!array_key_exists($key, $data)){
        return $data;
    }
    $keys = array_keys($data);
    $index = array_search($key, $keys);
    if($index !== FALSE){
        array_splice($data, $index, 1);
    }
    return $data;
   }

    ///?m=my&c=account&a=index
    public function index()
    {
        if (!session('?parent')) redirect(U('Index/index'));
        $this->display();
    }

    public function index1()
    {
        if (!session('?parent')) redirect(U('Index/index'));
        $this->auth_error = $_GET['auth_error'];
        $this->first = $_GET['first'];
        $parentinfo = M('auth_parent')->where("id=".session('parent.id'))->find();

        if (empty($parentinfo['sex'])) {
            $parentinfo['sex'] = '男';
        }
        $this->assign('tip',session('tip'));
        $this->assign('data',$parentinfo);
        $vipInfo = D('Account_auths')->isVipInfo(session('parent.id'),ROLE_PARENT);
        if($vipInfo['is_auth'] == 3 && $vipInfo['vip_day'] >=0){
            $this->assign('team_vip',1);
            $token = base64_encode($_COOKIE['PHPSESSID'].','.ROLE_PARENT.','.session('parent.id'));
            $key = A('Home/Auth')->getAuthKey($token);
            $pepUrl = "http://jby-szxy.mypep.cn/home/jby_auth/login?token=".$token.'&key='.$key;
            $this->assign('pep_redirect_url',$pepUrl);
        }//团体VIP且剩余天数大于0
        $this->display_nocache();
        session('tip', null);
    }

    public function me()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }
        A('Home/Common')->authJudgement();

        $this->assign('module', '个人中心');
        $this->assign('nav', '我');
        $this->assign('subnav', '我的资料');
        $this->assign('navicon', 'jiazhangfengcai');
        $parentModel = M('auth_parent');
        $id = session('parent.id');
        $result = $parentModel->where("id=$id")->find();

        if ( !preg_match('/Resources/', $result['avatar']) ) {
            $result['avatar'] = '';
        }
        if($result['avatar'] == NULL) {
            $result['avatar'] = '';
        }
        if(empty( $result['sex'] )) {
            $result['sex'] = '男';
        }

        $this->assign('data', $result);

         $msgModel = M('notice_message');
         $where['role'] = 2; //parent
         $where['user_id'] = $id;
         $where['read_status'] = 0; //unread
         $count = $msgModel ->where($where)->count();
         $this->assign('unreadCount','('.$count.')');



        $Model = M('auth_student_parent_contact');
        $parentmychild = $Model
            ->join('LEFT JOIN auth_student on auth_student.id=auth_student_parent_contact.student_id')
            ->join('LEFT JOIN biz_class_student on biz_class_student.student_id=auth_student_parent_contact.student_id')
            ->join('LEFT JOIN dict_schoollist on dict_schoollist.id=auth_student.school_id')
            ->join('LEFT JOIN biz_class on biz_class.id=biz_class_student.class_id')
            ->join('LEFT JOIN dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('auth_student_parent_contact.student_id,auth_student.student_name,biz_class.name,dict_grade.grade,auth_student.avatar,dict_schoollist.school_name,auth_student_parent_contact.create_at')
            ->where("auth_student_parent_contact.parent_id=" . session('parent.id'))
            ->order('auth_student_parent_contact.id desc')
            ->group('auth_student_parent_contact.student_id')
            ->select();

        $this->assign('studentList', $parentmychild);
        $this->display_nocache();
    }
    //message list
    public function message()
    {
     if (!session('?parent')) redirect(U('Index/index'));
     $parentId = session('parent.id');
     $msgModel = M('notice_message');
     if($_POST)
     {
     $msgid = $_POST['id'];
     $data['read_status'] = 1;//read
     $msgModel->where ("id=$msgid") ->save($data);
     $rs['status'] = 200;
     echo json_encode($rs);
     }
     else
     {
     $where['role'] = 2; //parent
     $where['user_id'] = $parentId;
     $result = $msgModel ->where($where)->select();
     for($i=0;$i<sizeof($result);$i++)
        $result[$i]['create_at'] = date("Y-m-d H:i:s",$result[$i]['create_at']);
     $this->assign('data',$result);
     $where['read_status'] = 0; //unread
     $count = $msgModel ->where($where)->count();
     $this->assign('unreadCount','('.$count.')');
     $userInfo = M('auth_parent')->where("id=$parentId")->find();
     $this->assign('userinfo',$userInfo);

     $this->display();
     }
    }


    //登陆验证并跳转
    public function login()
    {
        if ($_POST) {
            $theme = "1";
            //$check['telephone'] = $_POST['telephone'];
            //$check['password'] = sha1($_POST['password_3']);
            $check['telephone'] =getParameter('telephone', 'str',false);
            $check['password'] =sha1(getParameter('password_3', 'str',false));

            //$check['status'] = 1;
            //$check['lock'] = 0;

            $Model = M('auth_parent');
            $result = $Model->where($check)->find();
            if ($result) {

                if ($result['flag'] == 0 || $result['flag'] == -1 ) {
                    $this->redirect("Index/index?role=t&err=5");
                }
                //手机推送
                $parent_id=$result['id'];
                $client_ip=get_client_ip();
                $current_login_address=getIPLoc_sina($client_ip);
                if($current_login_address!=$result['login_address'] ){
                    $Model->where('id='.$result['id'])->save(array('login_address'=>$current_login_address));

                    $ip_arr=explode('.',$client_ip);
                    $ip_arr[3]='*';
                    $ip_string=implode('.',$ip_arr);
                    $login_address_str=$current_login_address.'('.$ip_string.')';
                    if(!empty($result['login_address'])) {
                        $parameter_arr = array(
                            'msg' => array(date('Y-m-d H:i:s'), $login_address_str, 'pc'),
                            'url' => array(
                                'type' => 0,
                                'data' => array()
                            )
                        );
                        A('Home/Message')->addPushUserMessage('EXCEPTION_LOGIN', 4, $parent_id, $parameter_arr);
                    }
                }
                if($result['login_address']==''){
                    $Model->where('id='.$result['id'])->save(array('login_address'=>$current_login_address));
                }

                session_start();

                session('auth_teacher', null);
                session('auth_student', null);
                session('student', null);
                session('teacher', null);

                session('parent', $result);

                $login_mac_address['access_token'] = session_id();
                $Model->where("id=".$result['id'])->save( $login_mac_address );

                session('theme', $theme);
                $btntheme = "primary";
                if ($theme == 2) $btntheme = "danger";
                if ($theme == 3) $btntheme = "dark";
                session('btntheme', $btntheme);


                $auth_type_use = D('Account_auths');
                //$auth_list = $auth_type_use->isVipAndInfo(1,5); //游客的权限和角色

                $auth_list = $auth_type_use->getAuthAndVipauth(session('parent.id'),4);

                session('auth_parent', $auth_list);

                $isVipInfo = $auth_type_use->isVipInfo(session('parent.id'),4);
                session('parent_vip',$isVipInfo);

                if ($result['is_first'] == 1) {
                    $mapfirst['id'] = $result['id'];
                    $firstdata['is_first'] = 2;
                    M('auth_parent')->where($mapfirst)->save($firstdata);

                }

                $tip = A('Home/Common')->registered_but_no_uploadworks(session('parent.id'),4);
                session('tip', $tip);


                $share_par=$_REQUEST['url'];
                if(!empty($share_par)){
                    $share_par=base64_decode($share_par);
                    $tokenValue = base64_encode(session_id().','.ROLE_PARENT.','.session('parent.id'));
                    $tokenString = "token=".$tokenValue;
                    $key = A('Home/Auth')->getAuthKey($tokenValue);
                    if(strpos($share_par,'?') !== false)
                        $share_par .= "&".$tokenString.'&key='.$key;
                    else
                        $share_par .= "?".$tokenString.'&key='.$key;
                    header('Location:' . $share_par);
                }else{
                    $this->redirect("Parent/index$theme");
                }


            } else {
                $this->redirect("Index/index?role=p&err=1");
            }
        } else {
            session('parent', null);
            $this->assign('REMOTE_ADDR',C('REMOTE_ADDR'));
            $this->display();
        }
    }

    //家长找回密码图形验证生成
    public function produceVerifyCodeP(){
        $config = array(
             'fontSize' => 20,
             'length' => 4, // 验证码位数
             'imageH' => 40);
         $verify = new Verify($config);
         $verify->entry(PARENT_FORGET_PASSWORD_PRODUCE_ID);
    }

    function check_verify_p(){
        //$code=getParameter('code','str',false);
        $this->check_verify_code(1,$code,PARENT_FORGET_PASSWORD_PRODUCE_ID);
    }


    public function forgetPassword()
    {
        if ($_POST) {
            /*
            $data['telephone'] = $_POST['telephone'];
            $data['password'] = sha1($_POST['password']);
            $confirmPassword = sha1($_POST['confirm_password']);
            */
            $data['telephone'] = getParameter('telephone', 'str',false);
            $data['password'] = sha1(getParameter('password', 'str',false));
            $confirmPassword = sha1(getParameter('confirm_password','str',false));
            $valid_code=getParameter('valid_code','int',false);

            $verifyCode= getParameter('verify_code','str',false);
            $nVerifyCode= getParameter('n_verify_code','str',false);

            //图形验证码
            if(empty($nVerifyCode)){
                if(!$this->check_verify_code(false,$verifyCode,PARENT_FORGET_PASSWORD_PRODUCE_ID)){
                    $this->ajaxReturn(array('code' => -1,'msg' => '请填写正确的图形验证码'));
                }
            }

            if ($confirmPassword <> $data['password']) {
                $this->ajaxReturn(array('code' => -1,'msg' => '两次密码不一致'));
            }
            $ParentModel = M('auth_parent');
            //$CodeModel = M('misc_forgot_password_phone_code');
            $check['telephone'] = $data['telephone'];
            //$code = $CodeModel->where($check)->find();
            $redis_obj=new REDIS();
            $redis=$redis_obj->init_redis();
            $redis_key='sms_'.$check['telephone'];
            $code['code']=$redis->get($redis_key);

            $result = $ParentModel->where($check)->field('id')->find();
            if(empty($result)){
                $this->ajaxReturn(array('code' => -1,'msg' => '该用户不存在'));
            }

            if ($valid_code && $code['code'] == $valid_code) {
                $redis->delete($redis_key);
                $redis->close();
                //手机推送

                $parent_id=$result['id'];
                $parameter_arr=array(
                    'msg'=>array($check['telephone'],date('Y-m-d H:i:s')),
                    'url'=>array(
                        'type'=>0,
                        'data'=>array()
                    )
                );
                A('Home/Message')->addPushUserMessage('PARENT_PASSWORD_MODIFY',4,$parent_id,$parameter_arr);

                $ParentModel->where($check)->save($data);
                //$CodeModel->where($check)->delete();

                $share_par=getParameter('url','str',false);
                if(!empty($share_par)){
                    $share_par=base64_decode($share_par);
                    $this->ajaxReturn(array('code' => 2,'msg' => '重置成功','url'=>$share_par));
                }else{
                    $this->ajaxReturn(array('code' => 0,'msg' => '重置成功'));
                }
            } else {
                $redis->close();
                $this->ajaxReturn(array('code' => -1,'msg' => '验证码错误'));
            }
        } else {
            $err = $_GET['err'];
            $this->assign('err', $err);

            $share_string='';
            $share_url=getParameter('url','str',false);
            if(!empty($share_url)){
                $share_string='url='.$share_url;
            }
            $this->assign('share_str', $share_string);
            $this->display();
        }
    }

    //发送找回验证码
    public function sendForgetPasswordPhoneCode()
    {
        $redis_obj=new REDIS();
        $redis=$redis_obj->init_redis();

        //判断图形验证
        if(!$this->isMobile()) {
            //判断不是手机,不是登陆后个人中心发送的就判断
            $n_verify_code = getParameter('n_verify_code','str',false);
            if(empty($n_verify_code)){
                $verify_code = getParameter('code', 'str', false);
                if (!$this->check_verify_code(false, $verify_code,PARENT_FORGET_PASSWORD_PRODUCE_ID)) {
                    $this->showjson(-5, '图形验证码错误');
                }
            }
            $sendSmsFunction='templateSMS';
        }else{
            $sendSmsFunction='newTemplateSMS';
        }
        //$telephone = $_GET['telephone'];
        $telephone=getParameter('telephone', 'str',false);
        if (empty($telephone) || strlen($telephone) != 11) {
            $this->showjson(-4, '手机号码格式错误');
        }

        $check['telephone'] = $telephone;

        $TeacherModel = M('auth_parent');
        $teacher = $TeacherModel->where($check)->find();

        $out['status'] = 0;
        $out['msg'] = '';
        $out['content'] = '';

        if (empty($teacher)) {
            $this->showjson(-1, '该用户不存在');
        }

        $redis_key='sms_'.$telephone;
        $redis_exists = $redis->exists('p'.$redis_key);
        if ($redis_exists) {
            $this->showjson(-1, '60秒内只能发送一次验证码');
        }


        $randcode = rand(10000, 99999);
        $smsapi = new SMS();
        $ret = $smsapi->$sendSmsFunction($telephone, '找回密码,' . $randcode, 'json');
        if ($ret['status'] == false || $ret < 0) {
            $this->showjson(-2, '验证码发送失败');
            return;
        }

        $redis->setex($redis_key,300,$randcode);
        $redis->setex('p'.$redis_key,62,$randcode);
        $redis->close();

        /*
        $CodeModel = M('misc_forgot_password_phone_code');
        $code = $CodeModel->where($check)->find();
        $data['telephone'] = $telephone;
        $data['code'] = $randcode;
        $data['create_at'] = time();

        if (empty($code)) {
            $CodeModel->add($data);
        } else {
            $CodeModel->where($check)->save($data);
        }*/

        $this->showjson(0, 'success');
    }

    //生成验证码
    function produceVerifyCode(){
        $config = array(
             'fontSize' => 20,
             'length' => 4, // 验证码位数
             'imageH' => 40);
         $verify = new Verify($config);
         $verify->entry(PARENT_IMAGE_PRODUCE_ID);
    }


    function check_verify(){
        //$code=getParameter('code','str',false);
        $this->check_verify_code(1,$code,PARENT_IMAGE_PRODUCE_ID);
    }


    //检查验证码
    public function check_verify_code($show_flag=1,$code='',$image_produce_id){
        $config = array(
            'reset' => false
        );
         $verify = new \Think\Verify($config);
         if($show_flag==1){
             $verify_code=getParameter('code','str');
             $res = $verify->check($verify_code,$image_produce_id);
             if($res){
                 $this->showjson(200);
             }else{
                 $this->showjson(401);
             }
         }else{
             $verify_code=$code;
             $res = $verify->check($verify_code,$image_produce_id);
             return $res;
         }
    }


    //注册
    public function register()
    {
        if ($_POST) {
            $vip_config=C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');
            /*
            $check['telephone'] = $_POST['telephone'];

            $data['telephone'] = $_POST['telephone'];
            $data['password'] = sha1($_POST['password']);
            $data['valid_code'] = $_POST['valid_code'];
            $data['parent_name'] = remove_xss($_POST['name']);
            $data['email'] = remove_xss($_POST['email']);
            $data['confirm_password'] = sha1($_POST['confirm_password']);
             */
            $check['telephone']=getParameter('telephone', 'str',false);
            $data['telephone'] = $_POST['telephone'];
            $data['password'] = sha1(getParameter('password', 'str',false));
            $dataTemp['valid_code'] = getParameter('valid_code', 'str',false);
            $data['parent_name'] =getParameter('name', 'str',false);
            $data['sex'] = getParameter('sex', 'str',false);
            $data['email'] = getParameter('email', 'str',false);
            $dataTemp['confirm_password'] = sha1(getParameter('confirm_password', 'str',false));

            $data['create_at'] = time();
            $data['update_at'] = time();
            $data['access_token'] = "96874kief336415";


            if(session('from_param')){
                $data['source'] = session('from_param');
            }

            if (empty($_POST['telephone']) || empty($_POST['password']) || empty($_POST['name'])) {
                $this->showMessage(500,'请填写完整信息',array());
            }

            if ($_POST['confirm_password'] != $_POST['password']) {
                $this->showMessage(500,'密码与确认密码不一致',array());
            }


            $ref = session('rid');
            if (!empty($ref)) {
                $refmap['a_id'] = session('rid');
                $this->addActivityUser( $refmap );
            }

            $ParentModel = M('auth_parent');
            $result = $ParentModel->where($check)->find();//判断是否已经注册

            if (empty($result)) {
                //$CodeModel = M('misc_register_phone_validcode');

                //$code = $CodeModel->where($check)->find();
                $redis_obj=new REDIS();
                $redis=$redis_obj->init_redis();
                $redis_key='sms_'.$check['telephone'];
                $code['code']=$redis->get($redis_key);


                if ($dataTemp['valid_code'] && $code['code'] == $dataTemp['valid_code']) {
                    $redis->delete($redis_key);
                    $redis->close();

                    $user_id  = $ParentModel->add($data);

                    if($vip_config && $vip_config<=3){
                        give_new_vip_operation(4,$vip_config, $user_id,0);
                    }
                    //让家长和学生进行绑定
                    $student_model=M('auth_student');
                    $student_where['parent_tel']=$data['telephone'];
                    $student_data['parent_id']=$user_id;
                    $student_info=$student_model->where($student_where)->save($student_data);

                    $studentsArray =  $student_model->where($student_where)->field('id')->select();
                    for($i=0;$i<sizeof($studentsArray);$i++)
                    {
                        $student_info_id = $studentsArray[$i]['id'];
                        D('Auth_student')->updateStudentParentInfo($user_id,$student_info_id );
                    }
                    //$CodeModel->where($check)->delete();
                    $smsapi = new SMS();
                    $smsapi->newUserNotice($check['telephone']);
                    $Message = new \Home\Controller\MessageController();
                    $parameters = array( 'msg' => array() , 'url' => array( 'type' => 0));
                    $Message->addPushUserMessage('REG_SUCCESS',4,$user_id,$parameters);

                    $share_par=getParameter('url','str',false);
                    if(!empty($share_par)){
                        $share_par=base64_decode($share_par);

                        $theme = "1";
                        $Model = M('auth_parent');
                        $result = $Model->where($check)->find();
                        $client_ip=get_client_ip();
                        $current_login_address=getIPLoc_sina($client_ip);
                        if($current_login_address!=$result['login_address']){
                            $Model->where('id='.$result['id'])->save(array('login_address'=>$current_login_address));
                        }
                        session_start();

                        session('auth_teacher', null);
                        session('auth_student', null);
                        session('student', null);
                        session('teacher', null);

                        session('parent', $result);
                        session('theme', $theme);
                        $btntheme = "primary";
                        session('btntheme', $btntheme);

                        $auth_type_use = D('Account_auths');
                        $auth_list = $auth_type_use->getAuthAndVipauth(session('parent.id'),ROLE_PARENT);

                        session('auth_parent', $auth_list);

                        $isVipInfo = $auth_type_use->isVipInfo(session('parent.id'),ROLE_PARENT);
                        session('parent_vip',$isVipInfo);

                        $this->showMessage(201,'success',array('telephone'=>$check['telephone'],'url'=>$share_par));
                    }else{

                        $this->showMessage(200,'success',array('telephone'=>$check['telephone']));
                    }
                } else {
                    $redis->close();
                    $this->showMessage(500,'验证码错误',array());
                }

            } else {
                $this->showMessage(500,'该手机号已经被注册',array());
            }
        } else {
            setSourceParameter();
            $this->display();
        }
    }

    //京版活动带来的注册用户
    public function addActivityUser( $data ) {
        $data['type'] = 2;
        $data['user_count'] = 1;
        M('activity_register_user')->add( $data );

    }


    public function isMobile(){
        $useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $useragent_commentsblock=preg_match('|.??|',$useragent,$matches)>0?$matches[0]:'';
        function CheckSubstrs($substrs,$text){
            $result = false;
            foreach($substrs as $substr){
                if(false!==strpos($text,$substr)){
                    return true;
                }
            }
            return $result;
        }

        $mobile_os_list=array( 'iPad','Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');
        $mobile_token_list=array('Android','Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod','iPad');
        $found_mobile=CheckSubstrs($mobile_os_list,$useragent_commentsblock) || CheckSubstrs($mobile_token_list,$useragent);

        if ($found_mobile){
            return true;
        }else{
            return false;
        }
    }

    //发送注册验证码
    public function sendRegisterPhoneCode()
    {
        //判断图形验证
        if(!$this->isMobile()) {
            $verify_code = getParameter('code', 'str', false);
            if (!$this->check_verify_code(false, $verify_code,PARENT_IMAGE_PRODUCE_ID)) {
                $this->showjson(-5, '图形验证码错误');
            }
            $sendSmsFunction='templateSMS';
        }else{
            $sendSmsFunction='newTemplateSMS';
        }

        //$telephone = $_GET['telephone'];
        $telephone=getParameter('telephone', 'str',false);
        if (empty($telephone)) {
            $this->showjson(-4, '手机号码格式错误');
        }

        $check['telephone'] = $telephone;

        $ParentModel = M('auth_parent');
        $parent = $ParentModel->where($check)->find();
        if (!empty($parent)) {
            $this->showjson(-1, '用户已存在');
        }

        $randcode = rand(10000, 99999);
        $smsapi = new SMS();
        $ret = $smsapi->$sendSmsFunction($telephone, '注册家长,' . $randcode, 'json');
        if ($ret == false || $ret < 0) {
            $this->showjson(-2, '验证码发送失败');
            return;
        }

        $redis_obj=new REDIS();
        $redis=$redis_obj->init_redis();
        $redis_key='sms_'.$telephone;
        $redis->setex($redis_key,300,$randcode);
        $redis->close();

        /*
        $CodeModel = M('misc_register_phone_validcode');
        $code = $CodeModel->where($check)->find();
        $data['telephone'] = $telephone;
        $data['code'] = $randcode;//todo
        $data['create_at'] = time();

        if (empty($code)) {
            $CodeModel->add($data);
        } else {
            $CodeModel->where($check)->save($data);
        }*/

        $this->showjson(0, 'success');
    }

    //退出登录
    public function logout()
    {
        //向人教发送登出请求
        A('Home/Auth')->pepSendLogout($_COOKIE['PHPSESSID'],ROLE_PARENT,session('parent.id'));
        session('parent', null);
        $this->redirect("Index/index");
    }

    //OA首页
    public function oa()
    {
        $this->assign('module', '励耘圈');
        $this->assign('nav', '教育信息发布系统');
        $this->assign('subnav', '我收到的信息');

        $page = intval($_GET['page']);
        if (empty($page)) $page = 1;

        $Model = M('oa_message');
        $result = $Model
            ->join('LEFT JOIN oa_message_reply on  oa_message.id=oa_message_reply.message_id')
            ->where('oa_message.group_id=2')
            ->field('oa_message.*,oa_message_reply.reply_at,oa_message_reply.reader_type')
            ->order('oa_message.create_at desc')
            ->page($page, 50)
            ->select();

        $this->assign('list', $result);

        $this->display();
    }

    //我的回执
    public function oaMyReceipts()
    {
        $this->assign('nav', '教育信息发布系统');
        $this->assign('subnav', '我的回执');

        $page = intval($_GET['page']);
        if (empty($page)) $page = 1;

        $Model = M('oa_message');
        $result = $Model->where('status=2')
            ->order('create_at desc')
            ->page($page, 50)
            ->select();

        $this->assign('list', $result);

        $this->display();
    }

    //消息详情
    public function oaMessageDetails()
    {
        $this->assign('module', '励耘圈');
        $this->assign('nav', '教育信息发布系统');
        $this->assign('subnav', '信息详情');

        $id = intval($_GET['id']);

        $Model = M('oa_message');
        $result = $Model->where("id=$id")
            ->find();

        $this->assign('data', $result);

        $this->display();
    }

    //消息回执
    public function oaReply()
    {
        $messageId = intval($_GET['message_id']);


        //获得家长的孩子信息
        $parentId = session('parent.id');
        $ParentStudentModel = M('auth_parent_student');

        $students = $ParentStudentModel
            ->join("auth_student on auth_parent_student.student_id=auth_student.id")
            ->where("auth_parent_student.parent_id=$parentId")
            ->select();
        $parentNewName = '';
        foreach ($students as $value) {
            $parentNewName = $parentNewName . $value['student_name'] . ' ';
        }
        $parentNewName = $parentNewName . '家长';

        $Model = M('oa_message_reply');
        $data['message_id'] = $messageId;
        $data['reader_id'] = $parentId;
        $data['reader'] = $parentNewName;
        $data['reply_at'] = time();
        $data['content'] = '';
        $data['reader_type'] = 2;

        $Model->add($data);


        $this->ajaxReturn('success');
    }

    //专家资讯信息列表
    public function expertInformationList()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $id=getParameter('auth_id', 'int',false);

        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Parent/index1?auth_error=1'));
        }

        $this->assign('module', '励耘圈');
        $this->assign('nav', '专家资讯');
        $this->assign('subnav', '资讯信息');
        $this->assign('navicon', 'zhuanjiazixun');

        $Model = M('social_expert_information');
        $where['status'] = 2;
        $keyword = $_GET['keyword'];

        if($keyword)
         $where['title'] = array("like", "%" . $keyword . "%");
        $count = $Model->where($where)->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        $result = $Model->where($where)
            ->order('create_at desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $AdminModel = M('auth_admin');

        foreach ($result as $key => $val) {
            $check['id'] = $val['publisher_id'];
            $info = $AdminModel->where($check)->find();
            $info['nickname'] = empty($info['nickname']) ? $info['name'] : $info['nickname'];
            $result[$key]['publisher'] = $info['nickname'];
        }

        $this->assign('list', $result);
        $this->assign('page', $show);
        $this->assign('keyword',$keyword);
        $this->display();
    }

    //专家资讯信息详情
    public function expertInformationDetails($id)
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $this->assign('module', '励耘圈');
        $this->assign('nav', '专家资讯');
        $this->assign('subnav', '资讯详情');
        $this->assign('navicon', 'zhuanjiazixun');

        $Model = M('social_expert_information');
        $id=intval($id);
        $check['id'] = $id;
        $result = $Model->where($check)->find();
        $where['id'] = $result['publisher_id'];
        $info = M('auth_admin')->where($where)->find();
        $result['publisher'] = $info['nickname'];
        $this->assign('data', $result);

        $this->display();
    }

    //赞一个京版活动
    public function zanActivity()
    {
        $parentId = session('parent.id');
        if(empty($parentId))
         {
         //$parentId = $_GET['user_id'];
           $parentId = getParameter('user_id', 'int');
         if(empty($parentId))
          {
          redirect(U('Index/index'));
          }
         }
        //$social_activity_id = $_GET['social_activity_id'];
         $social_activity_id = getParameter('social_activity_id', 'int');

        $ZanModel = M('social_activity_zan');
        $zanData['social_activity_id'] = $social_activity_id;
        $zanData['user_id'] = $parentId;
        $zanData['user_type'] = 3;


        $existed = $ZanModel->where($zanData)->find();
        if (empty($existed)) {
            $zanData['zan_time'] = time();
            $ZanModel->add($zanData);

            $Model = M('social_activity');
            $Model->where("id=$social_activity_id")->setInc('zan_count', 1);

            $this->ajaxReturn("success");
        } else {
            $ZanModel->where("social_activity_id=$social_activity_id and user_id=$parentId and user_type=3")->delete();
            $Model = M('social_activity');
            $Model->where("id=$social_activity_id")->setDec('zan_count', 1);

            $this->ajaxReturn("已经取消点赞");
        }
    }

    //收藏一个京版活动
    public function favorActivity()
    {
        $parentId = session('parent.id');
         if(empty($parentId))
          {
          //$parentId = $_GET['user_id'];
             $parentId = getParameter('user_id', 'int');
          if(empty($parentId))
           {
           redirect(U('Index/index'));
           }
          }
        //$social_activity_id = $_GET['social_activity_id'];
          $social_activity_id = getParameter('social_activity_id', 'int');

        $FavorModel = M('social_activity_favor');
        $favorData['social_activity_id'] = $social_activity_id;
        $favorData['user_id'] = $parentId;
        $favorData['user_type'] = 3;

        $existed = $FavorModel->where($favorData)->find();
        if (empty($existed)) {
            $favorData['favor_time'] = time();
            $FavorModel->add($favorData);

            $Model = M('social_activity');
            $Model->where("id=$social_activity_id")->setInc('favor_count', 1);

            $this->ajaxReturn("success");
        } else {
            $FavorModel->where("social_activity_id=$social_activity_id and user_id=$parentId and user_type=3")->delete();
            $Model = M('social_activity');
            $Model->where("id=$social_activity_id")->setDec('favor_count', 1);

            $this->ajaxReturn("已经取消收藏");
        }
    }

    //教师风采
    public function teacherStyle()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        //$id = $_GET['auth_id'];
        $id = getParameter('auth_id', 'int',false);
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Parent/index1?auth_error=1'));
        }

        $this->assign('module', '励耘圈');
        $this->assign('nav', '教师风采');
        $this->assign('subnav', '教师风采榜');
        $this->assign('navicon', 'jiaoshifengcai');

        //$courseId = $_REQUEST['course_id'];
        $courseId = getParameter('course_id', 'int',false);
        $where = ' auth_teacher.flag=1 and auth_teacher.isfaketeacher = 0 ';
        if (!empty($courseId)) {
            $where = $where . "and auth_teacher.course_id=$courseId ";
        }

        $Model = M('auth_teacher');
        $result = $Model
            ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id')
            ->field('auth_teacher.*,dict_schoollist.school_name')
            ->where($where)
            ->order('points desc,create_at desc')
            ->page(1, 50)->select();

        if(1 == DISPLAY_TEACHERSTYLE)
        {
            $this->assign('list', $result);
        }
        else
        {
            $this->assign('list', array());
        }

        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        $this->assign('courses', $courses);

        $this->display();
    }


    ///////////////////教学+

    //资源列表
    public function resourceList()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        //$id = $_GET['auth_id'];
        $id = getParameter('auth_id', 'int',false);
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Parent/index1?auth_error=1'));
        }

        $this->assign('module', '教学+');
        $this->assign('nav', '教师资源分享');
        $this->assign('subnav', '资源列表');
        $this->assign('navicon', 'jiaoshiziyuan');

        /*
        $filter['course_id'] = $_REQUEST['course'];
        $filter['grade_code'] = $_REQUEST['grade'];
        //$filter['textbook_id'] = $_REQUEST['textbook_id'];
        $filter['school_term'] = $_REQUEST['textbook'];             //上册和下册
        $filter['type'] = $_REQUEST['type'];
        $filter['keyword'] = $_REQUEST['keyword'];
        $filter['sort_column'] = $_REQUEST['sort_column'];
        */

        $filter['course_id'] = getParameter('course', 'int',false);
        $filter['grade_code'] = getParameter('grade', 'int',false);
        //$filter['textbook_id'] = $_REQUEST['textbook_id'];
        $filter['school_term'] = getParameter('textbook', 'int',false);
        $filter['type'] =  getParameter('type', 'str',false);
        $filter['keyword'] = $_REQUEST['keyword'];
        $filter['sort_column'] = getParameter('sort_column', 'int',false,2);


        $check['biz_resource.status'] = 2;
        if (!empty($filter['type'])) $check['biz_resource.type'] = $filter['type'];
        if (!empty($filter['keyword'])) $check['biz_resource.name'] = array('like', '%' . $filter['keyword'] . '%');
        if (!empty($filter['school_term'])) $check['biz_textbook.school_term'] = $filter['school_term'];
        if (empty($filter['sort_column'])) $filter['sort_column'] = '0';

        $unique_grade_code=intval($filter['grade_code']);
        if($unique_grade_code==14){
            $check['_string']="dict_grade.code in (14)";
        }elseif($unique_grade_code==15){
            $check['_string']="dict_grade.code in (15)";
        }elseif($unique_grade_code==16){
            $check['_string']="dict_grade.code in (16)";
        }else{
            if($unique_grade_code>0 && $unique_grade_code<7){
                $check['_string']="dict_grade.code in (14,".$unique_grade_code.")";
            }elseif($unique_grade_code>6 && $unique_grade_code<10){
                $check['_string']="dict_grade.code in (15,".$unique_grade_code.")";
            }elseif($unique_grade_code>9 && $unique_grade_code<13){
                $check['_string']="dict_grade.code in (16,".$unique_grade_code.")";
            }
        }

        $unique_course_id=intval($filter['course_id']);
        if($unique_course_id==-1){
            if(empty($check['_string'])){
                if (!empty($filter['course_id'])) $check['_string'] = 'biz_resource.course_id in ('.$unique_course_id.')';
            }else{
                if (!empty($filter['course_id'])) $check['_string'] .= ' and biz_resource.course_id in ('.$unique_course_id.')';
            }
        }else{
            if(empty($check['_string'])){
                if (!empty($filter['course_id'])) $check['_string'] = 'biz_resource.course_id in (-1,'.$unique_course_id.')';
            }else{
                if (!empty($filter['course_id'])) $check['_string'] .= ' and biz_resource.course_id in (-1,'.$unique_course_id.')';
            }
        }
        if(empty($check['_string'])){
            $check['_string']="biz_resource.type!='html'";
        }else{
            $check['_string'].=" and biz_resource.type!='html'";
        }



        $sort=I('sort_column');

        if (!empty($sort) && $sort != '') {

            switch ($sort) {
                case 0:
                    $sort_string= "zan_count desc";
                    break;
                case 1:
                    $sort_string= "zan_count asc";
                    break;
                case 2:
                    $sort_string= "favorite_count desc";
                    break;
                case 3:
                    $sort_string= "favorite_count asc";
                    break;
                case 4:
                    $sort_string= "follow_count desc";
                    break;
                case 5:
                    $sort_string= "follow_count asc";
                    break;
                case 6:
                    $sort_string= "create_at desc";
                    break;
                case 7:
                    $sort_string= "create_at asc";
                    break;
                default:
                    $sort_string= "create_at desc";
                    break;
            }

            if($sort>=0 && $sort<8){
                $filter['sort_column']=$sort;
            }else{
                $filter['sort_column']=6;
            }
        }else{

            if ( $sort != '' ) {

                $sort_string= "zan_count desc";
                $filter['sort_column']=0;
            }else{

                $sort_string= "create_at desc";
                $filter['sort_column']=6;
            }

        }

        //print_r($sort_string);die();



        $this->assign('course_id', $filter['course_id']);
        $this->assign('grade_code', $filter['grade_code']);
        $this->assign('textbook_id', $filter['school_term']);
        $this->assign('type', $filter['type']);
        $this->assign('keyword', $filter['keyword']);
        $this->assign('sort_column', $filter['sort_column']);

        $Model = M('biz_resource');

        $count = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
            ->join('dict_grade on dict_grade.id=biz_resource.grade_id','left')
            ->field('biz_resource.*,biz_textbook.name as textbook')
            ->where($check)
            ->count('biz_resource.id');
        $Page = new \Think\Page($count, 21);
        //C('PAGE_SIZE_FRONT')
        /*foreach ($filter as $key => $val) {
            $Page->parameter[$key] = urlencode($val);
        }*/
        $show = $Page->show();

        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
            ->join('dict_grade on dict_grade.id=biz_resource.grade_id','left')
            ->field("biz_resource.*,biz_textbook.name as textbook,if(UNIX_TIMESTAMP(NOW())-604800>biz_resource.create_at,'no','yes') is_new")
            ->where($check)
            ->order("biz_resource." . $sort_string)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $this->assign('list', $result);
        $this->assign('page', $show);

        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        $this->assign('courses', $courses);

        $Model = M('dict_grade');
        $grades = $Model->select();
        $this->assign('grades', $grades);

        /*
        $TextbookModel = M('biz_textbook');
        $c1['course_id'] = $filter['course_id'];
        $c1['grade_code'] = $filter['grade_code'];
        $textbooks = $TextbookModel->where($c1)->select();
        $this->assign('textbooks', $textbooks);
         *
         */

        $this->display_nocache();
    }



    //资源详情
    public function resourceDetails($id,$from="",$type=2)
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        if($type!=1) {
            $isAuth = $this->isAuth($this->c_a);
            if (!$isAuth) { //如果访问的模块没有权限
                redirect(U('Parent/index1?auth_error=1'));
            }
        }

        $this->assign('module', '教学+');
        $this->assign('nav', '教师资源分享');
        $this->assign('navicon', 'jiaoshiziyuan');

        $id=intval($id);
        if(empty($id))
        //$id = $_GET['id'];
        $id = getParameter('id', 'int',false);
        $goback = $_GET['goback'];
        $this->assign('showGoBackbutton', $goback);

        $Model = M('biz_resource');
        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
            ->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
            ->join('dict_course on biz_resource.course_id=dict_course.id','left')
            ->join('dict_grade on biz_resource.grade_id=dict_grade.id','left')
            ->field('biz_resource.*,biz_textbook.name as textbook,auth_teacher.brief_intro as teacher_brief_intro,auth_teacher.points as points,dict_course.course_name,dict_grade.grade')
            ->where("biz_resource.id=$id")
            ->find();

        $this->assign('subnav', $result['name']);
        $this->assign('data', $result);

        //拿到关联表的数据
        $contact_result=$Model->where("biz_resource.id=".$id)->join("biz_resource_contact on biz_resource_contact.biz_resource_id=biz_resource.id")
            ->field("biz_resource_contact.*")->select();
        $this->assign('contact_data', $contact_result);

        //观看次数+1
        $Model->where("id=$id")->setInc('follow_count', 1);

        //判断我是否赞过和收藏过
        $ZanModel = M('biz_resource_zan');
        $zanData['resource_id'] = $id;
        $zanData['user_type'] = 2;
        $zanData['user_id'] = session('parent.id');
        $existedZan = $ZanModel->where($zanData)->find();
        $existedZan = empty($existedZan) ? 'no' : 'yes';

        $this->assign('existedZan', $existedZan);

        $FavorModel = M('biz_resource_collect');
        $favorData['resource_id'] = $id;
        $favorData['user_type'] = 2;
        $favorData['user_id'] = session('parent.id');
        $existedFavor = $FavorModel->where($favorData)->find();
        $existedFavor = empty($existedFavor) ? 'no' : 'yes';
        $this->assign('existedFavor', $existedFavor);

        $this->display();

    }

    //赞一个教师资源
    public function zanResource()
    {
         $parentId = session('parent.id');
         if(empty($parentId))
          {
          //$parentId = $_GET['user_id'];
            $parentId=getParameter('user_id', 'int',false,2);
          if(empty($parentId))
           {
           redirect(U('Index/index'));
           }
          }

        //$id = $_GET['id'];
          $id = getParameter('id', 'int');

        $ZanModel = M('biz_resource_zan');
        $zanData['resource_id'] = $id;
        $zanData['user_id'] = $parentId;
        $zanData['user_type'] = 2;
        $parent_id = $parentId;

        $existed = $ZanModel->where($zanData)->find();
        if (empty($existed)) {
            $zanData['create_at'] = time();
            $res = M('auth_parent')->where("id=$parentId")->find();
            $zanData['user_name'] = $res['parent_name'];
            $ZanModel->add($zanData);


            $Model = M('biz_resource');
            $Model->where("id=$id")->setInc('zan_count', 1);


            $this->ajaxReturn("success");
        } else {
            $ZanModel->where("resource_id=$id and user_type=2 and user_id=$parent_id")->delete();
            $Model = M('biz_resource');
            $Model->where("id=$id")->setDec('zan_count', 1);


            $this->ajaxReturn("已经取消点赞");
        }
    }




    //收藏一个教师资源
    public function favorResource()
    {
         $parentId = session('parent.id');
         if(empty($parentId))
          {
          //$parentId = $_GET['user_id'];
            $parentId =getParameter('user_id', 'int',false);
          if(empty($parentId))
           {
           redirect(U('Index/index'));
           }
          }

        //$id = $_GET['id'];
          $id = getParameter('id', 'int');

        $FavorModel = M('biz_resource_collect');
        $favorData['resource_id'] = $id;
        $favorData['user_id'] = $parentId;
        $favorData['user_type'] = 2;

        $existed = $FavorModel->where($favorData)->find();
        if (empty($existed)) {
            $favorData['create_at'] = time();
            $res = M('auth_parent')->where("id=$parentId")->find();
            $favorData['user_name'] = $res['parent_name'];
            $FavorModel->add($favorData);

            $Model = M('biz_resource');
            $Model->where("id=$id")->setInc('favorite_count', 1);


            $this->ajaxReturn("success");
        } else {
            $FavorModel->where("resource_id=$id and user_type=2 and user_id=$parentId")->delete();
            $Model = M('biz_resource');
            $Model->where("id=$id")->setDec('favorite_count', 1);


            $this->ajaxReturn("已经取消收藏");
        }
    }



    ////电子课本
    public function textbookList()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Parent/index1?auth_error=1'));
        }

        if($_REQUEST['course'])
            $check['biz_textbook.course_id'] = getParameter('course', 'int',false);
        if($_REQUEST['grade'])
            $check['biz_textbook.grade_id'] = getParameter('grade', 'int',false);
        if($_REQUEST['textbook'])
            $check['biz_textbook.school_term'] = getParameter('textbook', 'int',false);
        if($_REQUEST['keyword']){
            $keyword=getParameter('keyword', 'str',false);
            $check['keyword'] = array('like','%'.$keyword.'%');
        }

        $check['_string'] = 'biz_textbook.has_ebook=1 and flag=1';
        $course_con=A('Home/Common')->getTextBookSelector($check,'course');
        if(!empty($course_con)){
            $grade_con=A('Home/Common')->getTextBookSelector($check,'grade');
            $school_term_con=A('Home/Common')->getTextBookSelector($check,'school_term');
        }else{
            if(!empty($check['biz_textbook.course_id'])){
                $course_model=D('Dict_course');
                $course_result=$course_model->getCourseInfo($check['biz_textbook.course_id']);
                $course_con[]=$course_result;
            }
            if(!empty($check['biz_textbook.grade_id'])){
                $grade_model=D('Dict_grade');
                $grade_result=$grade_model->getGradeInfo($check['biz_textbook.grade_id']);
                $grade_con[]=$grade_result;
            }
            if(!empty($check['biz_textbook.school_term'])){
                if($check['biz_textbook.school_term']==1){
                    $school_term_con[]=array('school_term'=>1);
                }elseif($check['biz_textbook.school_term']==2){
                    $school_term_con[]=array('school_term'=>2);
                }elseif($check['biz_textbook.school_term']==3){
                    $school_term_con[]=array('school_term'=>3);
                }

            }
        }

        $this->assign('courses',$course_con);
        $this->assign('grades',$grade_con);
        $this->assign('textbooks',$school_term_con);

        $this->assign('course_id', $check['biz_textbook.course_id']);
        $this->assign('grade_id', $check['biz_textbook.grade_id']);
        $this->assign('textbook_id', $check['biz_textbook.school_term']);
        $this->assign('keyword',$_REQUEST['keyword']);


        $this->assign('module', '教学+');
        $this->assign('nav', '电子课本');
        $this->assign('subnav', '电子课本');
        $this->assign('navicon', 'shuzijiaocai');

        /*
        A('Home/Common')->CourseGradeTextBookSelector(array(
            'course_id'=>$check['biz_textbook.course_id'],
            'grade_id'=>$check['biz_textbook.grade_id'],
            'textbook_id'=>$check['biz_textbook.textbook_id'],
            'keyword'=>$check['keyword']
        ));*/
        $this->display();
    }

    /////////////////////////////班级行

    //小黑板
    public function blackboard()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);

        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Parent/index1?auth_error=1'));
        }

        $is_board = session('parent.is_board');
        if ( $is_board == 1) {
            $parent_id = session('parent.id');
            D('Biz_classList')->isFirstLoginParentBoard( $parent_id );
            $this->redirect('Parent/blackboradFunctionGuidancecopy');
        }


        $this->assign('module', '班级行');
        $this->assign('nav', '小黑板');
        $this->assign('subnav', '消息列表');
        $this->assign('navicon', 'xiaoheibanbefore');

        $filter['keyword'] = $_REQUEST['keyword'];
        $filter['flag'] = $_REQUEST['flag'];
        $this->assign('flag', $filter['flag']);
        if ($filter['keyword']!=null){
            $this->assign('kw',1);
        }
        if (!empty($filter['keyword'])) $check['biz_blackboard.message_title'] = array('like', '%' . $filter['keyword'] . '%');
        if ( !empty($filter['flag']) && $filter['flag']==1 ) $check['biz_isread_blackboard.id'] = array('EXP','IS NULL');

        $check['biz_class_student.status']=2;
        $check['auth_student.parent_id']=session('parent.id');
        $check['biz_class.is_delete']=0;

        $Model = M('biz_blackboard');
        $count = $Model
            ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
            ->join('biz_class on biz_class.id=boardandclass.class_id')
            ->join('biz_class_student on biz_class.id=biz_class_student.class_id')
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->join("biz_isread_blackboard on auth_student.parent_id=biz_isread_blackboard.user_id and biz_isread_blackboard.b_id=biz_blackboard.id and biz_isread_blackboard.class_id=biz_class.id  and role_id=4",'left')
            ->where($check)
            ->group('boardandclass.id')
            ->select();

        $Page = new \Think\Page(count($count), C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        $result = $Model
            ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
            ->join('biz_class on biz_class.id=boardandclass.class_id')
            ->join('biz_class_student on biz_class.id=biz_class_student.class_id')
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->join("biz_isread_blackboard on auth_student.parent_id=biz_isread_blackboard.user_id and biz_isread_blackboard.b_id=biz_blackboard.id and biz_isread_blackboard.class_id=biz_class.id  and role_id=4",'left')
            ->field('distinct biz_blackboard.*,biz_class.name as class_name')
            ->where($check)
            ->group('boardandclass.id')
            ->order('biz_blackboard.create_at desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->field('biz_blackboard.id,boardandclass.class_id,biz_blackboard.message_title,biz_blackboard.create_at,'
                    . 'biz_blackboard.publisher_id,biz_blackboard.publisher,biz_blackboard.status,biz_class.name class_name,dict_grade.grade,biz_isread_blackboard.id is_read')
            ->select();


        foreach( $result as $k => $v ) {
            $toumap['id'] = $v['publisher_id'];
            $tou = M('auth_teacher')->where( $toumap )->find();
            $result[$k]['touimg'] = $tou['avatar'];
            //求看过学生的数量
            /*$data=$Model->where('biz_blackboard.id='.$v['id'])->join('boardandclass on boardandclass.b_id=biz_blackboard.id')->join('biz_class on biz_class.id=boardandclass.class_id')->join('biz_class_student on biz_class.id=biz_class_student.class_id and biz_class_student.status=2')
                    ->join('biz_isread_blackboard on biz_isread_blackboard.user_id = biz_class_student.student_id and biz_isread_blackboard.role_id=3 and biz_isread_blackboard.b_id=biz_blackboard.id')
                    ->field('biz_blackboard.id,biz_class_student.student_id')->select();     */
            $data_count=$Model->where('biz_blackboard.id='.$v['id'])->join('boardandclass on boardandclass.b_id=biz_blackboard.id')->join('biz_class on biz_class.id=boardandclass.class_id')->join('biz_class_student on biz_class.id=biz_class_student.class_id and biz_class_student.status=2')
                ->join('biz_isread_blackboard on biz_isread_blackboard.user_id = biz_class_student.student_id and biz_isread_blackboard.role_id=3 and biz_isread_blackboard.b_id=biz_blackboard.id')
                ->field('biz_blackboard.id,biz_class_student.student_id')->count();
            $result[$k]['read_person_number'] = $data_count;

        }

        //print_r($result);die();



        unset($check['biz_blackboard.message_title']);
        $check['biz_isread_blackboard.id'] = array('EXP','IS NULL'); //未读的总数
        $weiducount = $Model
            ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
            ->join('biz_class on biz_class.id=boardandclass.class_id')
            ->join('biz_class_student on biz_class.id=biz_class_student.class_id')
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->join("biz_isread_blackboard on auth_student.parent_id=biz_isread_blackboard.user_id and biz_isread_blackboard.b_id=biz_blackboard.id and biz_isread_blackboard.class_id=biz_class.id  and role_id=4",'left')
            ->where($check)
            ->group('boardandclass.id')
            ->select();

        $weiducount = count($weiducount);
        $this->assign('count_isread', $weiducount);


        $this->assign('keyword', $filter['keyword']);
        $this->assign('list', $result);
        $this->assign('page', $show);

        $this->display_nocache();
    }


    //小黑板引导
    public function blackboradFunctionGuidancecopy() {
        session('parent.is_board', 2);
        $this->display();
    }

    //小黑板消息详情
    public function blackboardMessageDetails()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Parent/index1?auth_error=1'));
        }
        $read_person_number = getParameter('read_person_number', 'int',false,2);
        $class_id = getParameter('class_id', 'int',false,2);

        $this->assign('module', '班级行');
        $this->assign('nav', '小黑板');
        $this->assign('navicon', 'xiaoheiban');


        //$id = $_GET['id'];
        $id = getParameter('id', 'int',false,2);

        $Model = M('biz_blackboard');

        $result = $Model
            ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
            ->join('biz_class on biz_class.id=boardandclass.class_id')
            ->field('biz_blackboard.*,biz_class.name as class_name')
            ->where("biz_blackboard.id=$id")
            ->find();
        if(empty($result)){
            redirect(U('Index/systemError'));
        }

        $toumap['id'] = $result['publisher_id'];
        $tou = M('auth_teacher')->where( $toumap )->find();
        $result['touimg'] = $tou['avatar'];
        $result['sex'] = $tou['sex'];



        $add_data['user_id']=session('parent.id');
        $add_data['b_id']=$result['id'];
        $add_data['role_id']=4;
        $add_data['class_id']=$class_id;
        $model=M('biz_isread_blackboard');
        //判断是否存在,不存在则入库
        $browse_result=$model->where('role_id=4'.' and user_id='.session('parent.id').' and b_id='.$result['id'].' and class_id='.$class_id)->field('id')->find();

        if(empty($browse_result)){
            $model->add($add_data);
        }


        $this->assign('data', $result);

        $this->assign('subnav', $result['class_name']);

        $Model->where("id=$id")->setInc('view_count', 1);
        $this->assign('read_person_number', $read_person_number);

        $this->display();
    }

    //////作业系统
    /*public function homework()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '孩子历史作业');
        $this->assign('navicon', 'zuoyexitong');

        $check['auth_parent_student.parent_id'] = session('parent.id');
        //

        $filter['course_id'] = $_REQUEST['course_id'];
        $filter['sort_column'] = $_REQUEST['sort_column'];

        $this->assign('sort_column', $filter['sort_column']);


        if (!empty($filter['course_id'])) $check['biz_homework.course_id'] = $filter['course_id'];
        if (empty($filter['sort_column'])) {
            $filter['sort_column'] = 'biz_homework.create_at';
        } else if ($filter['sort_column'] == 'create_at') {
            $filter['sort_column'] = 'biz_homework.create_at';
        } else if ($filter['sort_column'] == 'course_name') {
            $filter['sort_column'] = 'dict_course.course_name';
        }

        $this->assign('course_id', $filter['course_id']);

        $Model = M('biz_homework');

        $count = $Model
            ->join('biz_class on biz_class.id=biz_homework.class_id')
            ->join('biz_class_student on biz_class_student.class_id=biz_homework.class_id')
            ->join('auth_parent_student on auth_parent_student.student_id=biz_class_student.student_id')
            ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
            ->join('dict_course on dict_course.id=biz_homework.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
            ->join('auth_student on auth_parent_student.student_id=auth_student.id')
            ->where($check)
            ->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($filter as $key => $val) {
            $Page->parameter[$key] = urlencode($val);
        }
        $show = $Page->show();


        $homeworks = $Model
            ->join('biz_class on biz_class.id=biz_homework.class_id')
            ->join('biz_class_student on biz_class_student.class_id=biz_homework.class_id')
            ->join('auth_parent_student on auth_parent_student.student_id=biz_class_student.student_id')
            ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
            ->join('dict_course on dict_course.id=biz_homework.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
            ->join('auth_student on auth_parent_student.student_id=auth_student.id')
            ->field("biz_homework.*,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,0 as dostatus,'' as duration,'' as points,auth_parent_student.student_id,auth_student.student_name ")
            ->where($check)
            ->order($filter['sort_column'] . " desc")
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        //获取小孩的ID
        $check2['auth_parent_student.parent_id'] = session('parent.id');
        $Model = M('biz_homework_student_details');
        $homeworkdetails = $Model
            ->join('auth_parent_student on auth_parent_student.student_id=biz_homework_student_details.student_id')
            ->field('biz_homework_student_details.homework_id,biz_homework_student_details.duration,biz_homework_student_details.status,biz_homework_student_details.points,biz_homework_student_details.student_id')
            ->where($check2)
            ->select();

        $i = 0;
        $outlist = array();
        foreach ($homeworks as $homework) {
            foreach ($homeworkdetails as $r) {
                if ($homework['id'] == $r['homework_id'] && $homework['student_id'] == $r['student_id']) {
                    $homework['duration'] = $r['duration'];
                    $homework['dostatus'] = $r['status'];
                    $homework['points'] = $r['points'];
                    break;
                }
            }
            $outlist[$i] = $homework;
            $i = $i + 1;
        }

        $this->assign('list', $outlist);
        $this->assign('page', $show);

        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        $this->assign('courses', $courses);

        $this->display();

    }*/

     public function homework()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Parent/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '孩子历史作业');
        $this->assign('navicon', 'zuoyexitong');

        $where['auth_student.parent_id'] = session('parent.id');
        $where['biz_homework.homework_status']=1;
        //
        $mca = I('mca');
        if ( $mca == 'action' ) {
            $this->assign('kw',1);
        }

        if(I('name')){
            $where['auth_student.student_name']=array('like', '%' . I('name') . '%');
        }
        if(I('grade')>0){
            $where['dict_grade.id']=intval(I('grade'));
        }

        if(I('course')>0){
            $where['dict_course.id']=intval(I('course'));
        }
        if(I('subject')>0){
            $where['biz_textbook.id']=intval(I('subject'));
        }
        if(I('type')>0){
             if(I('type')==1){
                $where['biz_homework.homework_type']='课堂作业';
             }elseif(I('type')==2){
                $where['biz_homework.homework_type']='课后作业';
             }
        }
        $status=0;
        if(I('status')>0){
            $status=intval(I('status'));
        }
        if(I('keyword')){
            $where['biz_homework.homework_name']=array('like', '%' . I('keyword') . '%');
        }
        if(I('sort')){
            $sort_column=Intval(I('sort'));
            if($sort_column==1){
                $sort_where='detail.duration asc,dostatus asc,biz_homework.create_at desc';
            }else if($sort_column==2){
                $sort_where='detail.duration desc,dostatus desc,biz_homework.create_at desc';
            }else if($sort_column==3){
                $sort_where='detail.points asc,dostatus asc,biz_homework.create_at desc';
            }else{
                $sort_where='detail.points desc,dostatus desc,biz_homework.create_at desc';
            }
        }else{
            $sort_column=1;
            $sort_where='biz_homework.create_at desc';
        }
        $date=I('date');
        if(!empty($date)){
            $where["_string"]="biz_homework.create_at>=".strtotime(I("date"))." and "."biz_homework.create_at<=".(strtotime(I("date")."+1 day")-1);
        }


        $Model = M('biz_homework');

        if($status==1){
            $count = $Model
                ->join('biz_class on biz_class.id=biz_homework.class_id')
                ->join('biz_class_student on biz_class_student.class_id=biz_homework.class_id')
                ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
                ->join('dict_course on dict_course.id=biz_homework.course_id')
                ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
                ->join('auth_student on auth_student.id=biz_class_student.student_id')
                ->join('biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id=auth_student.id')
                ->field("biz_homework.id")
                ->where($where)
                ->group("biz_homework.id,auth_student.id")
                ->select();
            $count=count($count);
            $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));

            $homeworks = $Model
                ->join('biz_class on biz_class.id=biz_homework.class_id')
                ->join('biz_class_student on biz_class_student.class_id=biz_homework.class_id')
                ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
                ->join('dict_course on dict_course.id=biz_homework.course_id')
                ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
                ->join('auth_student on auth_student.id=biz_class_student.student_id')
                ->join('biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id=auth_student.id')
                ->field("biz_homework.*,dict_grade.grade,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,"
                        . "detail.duration,.detail.status dostatus,detail.points,auth_student.id student_id,auth_student.student_name")
                ->where($where)
                ->group("biz_homework.id,auth_student.id")
                ->order($sort_where)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        }elseif($status==2){
            $count = $Model
                ->join('biz_class on biz_class.id=biz_homework.class_id')
                ->join('biz_class_student on biz_class_student.class_id=biz_homework.class_id')
                ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
                ->join('dict_course on dict_course.id=biz_homework.course_id')
                ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
                ->join('auth_student on auth_student.id=biz_class_student.student_id')
                ->join('biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id=auth_student.id','left')
                ->field("biz_homework.id,detail.points")
                ->where($where)
                ->group("biz_homework.id,auth_student.id")
                ->having("detail.points is null")
                ->select();
            $count=count($count);
            $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));

            $homeworks = $Model
                ->join('biz_class on biz_class.id=biz_homework.class_id')
                ->join('biz_class_student on biz_class_student.class_id=biz_homework.class_id')
                ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
                ->join('dict_course on dict_course.id=biz_homework.course_id')
                ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
                ->join('auth_student on auth_student.id=biz_class_student.student_id')
                ->join('biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id=auth_student.id','left')
                ->field("biz_homework.*,dict_grade.grade,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,"
                        . "detail.duration,.detail.status dostatus,detail.points,auth_student.id student_id,auth_student.student_name")
                ->where($where)
                ->group("biz_homework.id,auth_student.id")
                ->order($sort_where)
                ->having("detail.points is null")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
        }else{
            $count = $Model
                ->join('biz_class on biz_class.id=biz_homework.class_id')
                ->join('biz_class_student on biz_class_student.class_id=biz_homework.class_id')
                ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
                ->join('dict_course on dict_course.id=biz_homework.course_id')
                ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
                ->join('auth_student on auth_student.id=biz_class_student.student_id')
                ->join('biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id=auth_student.id','left')
                ->field("biz_homework.id")
                ->where($where)
                ->group("biz_homework.id,auth_student.id")
                ->select();
            $count=count($count);
            $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));

            $homeworks = $Model
                ->join('biz_class on biz_class.id=biz_homework.class_id')
                ->join('biz_class_student on biz_class_student.class_id=biz_homework.class_id')
                ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
                ->join('dict_course on dict_course.id=biz_homework.course_id')
                ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
                ->join('auth_student on auth_student.id=biz_class_student.student_id')
                ->join('biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id=auth_student.id','left')
                ->field("biz_homework.*,dict_grade.grade,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,"
                        . "detail.duration,.detail.status dostatus,detail.points,auth_student.id student_id,auth_student.student_name")
                ->where($where)
                ->group("biz_homework.id,auth_student.id")
                ->order($sort_where)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();                     //echo $Model->getLastsql();die;
        }
        $show = $Page->show();
        $this->assign('page', $show);
        $Model = M('biz_exercise_library_chapter');
        $i = 0;
        $outlist = array();
        foreach ($homeworks as $homework) {
            //这里去求那个章节
            $info=$Model->where("u.id=".$homework['id'])
                    ->join("biz_homework_exercise t on t.chapter_id=biz_exercise_library_chapter.id")
                    ->join("biz_homework u on u.id=t.homework_id")
                    ->group("t.chapter_id")->field("distinct chapter,festival")->select();
            $homework['chapter']=$info;

            $outlist[$i] = $homework;
            $i = $i + 1;
        }

        $this->assign('list', $outlist);
        $this->assign('page', $show);

        $grade_model=M('dict_grade');
        $grade_result=$grade_model->where("auth_student.parent_id=".session('parent.id'))->join("biz_class on biz_class.grade_id=dict_grade.id")
                ->join("biz_class_student on biz_class_student.class_id=biz_class.id")
                ->join("auth_student on auth_student.id=biz_class_student.student_id")
                ->field('dict_grade.id,dict_grade.grade')->group("dict_grade.id")->select();
        //年级不为空,求出学科
        if(!empty($where['dict_grade.id'])){
            $course_model = M('dict_course');
            $course_result=$grade_model->where("dict_grade.id=".$where['dict_grade.id']." and auth_student.parent_id=".session('parent.id'))->join("biz_class on biz_class.grade_id=dict_grade.id")
                ->join("biz_class_student on biz_class_student.class_id=biz_class.id")
                ->join("auth_student on auth_student.id=biz_class_student.student_id")
                ->join('auth_teacher_second on auth_teacher_second.teacher_id=biz_class.class_teacher_id')
                ->join('dict_course on dict_course.id=auth_teacher_second.course_id')
                ->field('dict_course.id,dict_course.course_name')->group("dict_course.id")->select();
        }

        //年级和学科不为空,求出教材分册
        if(!empty($where['dict_grade.id']) && !empty($where['dict_course.id'])){
            $textbook_model = M('biz_textbook');
            $textbook_result=$textbook_model->where("grade_id=".$where['dict_grade.id']." and course_id=".$where['dict_course.id'])
                ->field('id,name')->select();
        }
        /*
        $Model = M('dict_course');
        $courses = $Model->where("student.parent_id=".session('parent.id')." and biz_homework.homework_status=1")->field("dict_course.id,dict_course.course_name")
                    ->join("biz_homework on biz_homework.course_id=dict_course.id")
                    ->join('biz_class on biz_class.id=biz_homework.class_id')
                    ->join('biz_class_student class_contact on class_contact.class_id=biz_class.id')
                    ->join('auth_student student on student.id=class_contact.student_id')
                    ->order('sort_order asc')->group("dict_course.id")->select();
        $this->assign('courses_list', $courses);


        $biz_textbook_mode=M('biz_textbook');
        $textbook_result=$biz_textbook_mode->where("student.parent_id=".session('parent.id'))->field('biz_textbook.id,biz_textbook.name')
                        ->join('biz_homework on biz_homework.textbook_id=biz_textbook.id')
                        ->join('biz_class on biz_class.id=biz_homework.class_id')
                        ->join('biz_class_student class_contact on class_contact.class_id=biz_class.id')
                        ->join('auth_student student on student.id=class_contact.student_id')
                        ->group("biz_textbook.id")->select();
        $this->assign('textbook_list', $textbook_result);*/

        $this->assign('grade_list', $grade_result);
        $this->assign('courses_list', $course_result);
        $this->assign('textbook_list', $textbook_result);

        $this->assign('default_grade',$where['dict_grade.id']);
        $this->assign('default_name', I('name'));
        $this->assign('default_course', $where['dict_course.id']);
        $this->assign('default_textbook', $where['biz_textbook.id']);
        $this->assign('default_type', intval(I('type')));
        $this->assign('default_date', I('date'));
        $this->assign('default_sort', $sort_column);
        $this->assign('default_status', $status);
        $this->assign('keyword', I('keyword'));

        $this->display();

    }

    //根据年级获得班级
    public function getGradeClass(){
        $id=intval(I('id'));
        if(!$id){
            echo json_encode(array());
        }else{

            $grade_model=M('dict_grade');
            $course_result=$grade_model->where("dict_grade.id=".$id." and auth_student.parent_id=".session('parent.id'))->join("biz_class on biz_class.grade_id=dict_grade.id")
                ->join("biz_class_student on biz_class_student.class_id=biz_class.id")
                ->join("auth_student on auth_student.id=biz_class_student.student_id")
                ->field('biz_class.id,biz_class.name')->group("biz_class.id")->select();
            echo json_encode($course_result);
        }
    }


    //根据年级获得学科
    public function getClassCourse(){
        $id=intval(I('id'));
        if(!$id){
            echo json_encode(array());
        }else{

            $grade_model=M('dict_grade');
            $course_result=$grade_model->where("dict_grade.id=".$id." and auth_student.parent_id=".session('parent.id'))->join("biz_class on biz_class.grade_id=dict_grade.id")
                ->join("biz_class_student on biz_class_student.class_id=biz_class.id")
                ->join("auth_student on auth_student.id=biz_class_student.student_id")
                ->join('auth_teacher_second on auth_teacher_second.teacher_id=biz_class.class_teacher_id')
                ->join('dict_course on dict_course.id=auth_teacher_second.course_id')
                ->field('dict_course.id,dict_course.course_name')->group("dict_course.id")->select();
            echo json_encode($course_result);
        }
    }


    //错误集列表
    public function wrongHomeworkList(){
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Parent/index1?auth_error=1'));
        }


        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '错题集');
        $this->assign('navicon', 'zuoyexitong');

        $where['auth_student.parent_id'] = session('parent.id');
        $where['biz_homework.homework_status']=1;

        if(I('grade')>0){
            $where['dict_grade.id']=intval(I('grade'));
        }

        $mca = I('mca');
        if ( $mca == 'action' ) {
            $this->assign('kw',1);
        }

        if(I('class')){
            $class_id=intval(I('class'));
            //这里求出班级
            $class_model=M('biz_class');
            $class_result=$class_model->where("id=".$class_id)->field('name')->find();
            if(empty($class_result)){
                redirect(U('Index/systemError'));
            }else{
                $class_name=$class_result['name'];
                //$where['biz_class.name']=$class_name;
                $where['biz_class.id']=$class_id;
            }
        }
        if(I('name')){
            $where['auth_student.student_name']=array('like', '%' . I('name') . '%');
        }
        if(I('course')>0){
            $where['dict_course.id']=intval(I('course'));
        }
        if(I('subject')>0){
            $where['biz_textbook.id']=intval(I('subject'));
        }
        if(I('type')>0){
             if(I('type')==1){
                $where['biz_homework.homework_type']='课堂作业';
             }elseif(I('type')==2){
                $where['biz_homework.homework_type']='课后作业';
             }
        }
        if(I('keyword')){
            $where['biz_homework.homework_name']=array('like', '%' . I('keyword') . '%');
        }

        $Model = M('biz_homework');

        $count = $Model
                ->join('biz_class on biz_class.id=biz_homework.class_id')
                ->join('biz_class_student on biz_class_student.class_id=biz_homework.class_id')
                ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
                ->join('dict_course on dict_course.id=biz_homework.course_id')
                ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
                ->join('auth_student on auth_student.id=biz_class_student.student_id')
                ->join('biz_homework_score_details score on biz_homework.id=score.homework_id and score.student_id=auth_student.id and score.flag!=1')
                ->field("biz_homework.id")
                ->where($where)
                ->group("biz_homework.id,auth_student.id")
                ->select();
        $count=count($count);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show=$Page->show();
        $this->assign('page', $show);

        $homeworks = $Model
                ->join('biz_class on biz_class.id=biz_homework.class_id')
                ->join('biz_class_student on biz_class_student.class_id=biz_homework.class_id')
                ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
                ->join('dict_course on dict_course.id=biz_homework.course_id')
                ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
                ->join('auth_student on auth_student.id=biz_class_student.student_id')
                ->join('biz_homework_score_details score on biz_homework.id=score.homework_id and score.student_id=auth_student.id and score.flag!=1')
                ->field("biz_homework.*,dict_grade.grade,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,"
                        . "auth_student.id student_id,auth_student.student_name")
                ->where($where)
                ->group("biz_homework.id,auth_student.id")
                ->order('biz_homework.create_at desc,auth_student.id')
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();

         $Model = M('biz_exercise_library_chapter');
        $i = 0;
        $outlist = array();
        foreach ($homeworks as $homework) {
            //这里去求那个章节
            $info=$Model->where("u.id=".$homework['id'])
                    ->join("biz_homework_exercise t on t.chapter_id=biz_exercise_library_chapter.id")
                    ->join("biz_homework u on u.id=t.homework_id")
                    ->group("t.chapter_id")->field("chapter,festival,biz_exercise_library_chapter.id")->select();
            $homework['chapter']=$info;

            $outlist[$i] = $homework;
            $i = $i + 1;
        }
        $this->assign('list', $outlist);

        /*
        $Model = M('dict_course');
        $courses = $Model->where("student.parent_id=".session('parent.id')." and biz_homework.homework_status=1")->field("dict_course.id,dict_course.course_name")
                    ->join("biz_homework on biz_homework.course_id=dict_course.id")
                    ->join('biz_class on biz_class.id=biz_homework.class_id')
                    ->join('biz_class_student class_contact on class_contact.class_id=biz_class.id')
                    ->join('auth_student student on student.id=class_contact.student_id')
                    ->order('sort_order asc')->group("dict_course.id")->select();
        $this->assign('course_list', $courses);

        $biz_textbook_mode=M('biz_textbook');
        $textbook_result=$biz_textbook_mode->where("student.parent_id=".session('parent.id'))->field('biz_textbook.id,biz_textbook.name')
                        ->join('biz_homework on biz_homework.textbook_id=biz_textbook.id')
                        ->join('biz_class on biz_class.id=biz_homework.class_id')
                        ->join('biz_class_student class_contact on class_contact.class_id=biz_class.id')
                        ->join('auth_student student on student.id=class_contact.student_id')
                        ->group("biz_textbook.id")->select();
        $this->assign('textbook_list', $textbook_result);

        $Model = M('biz_class');
        $courses = $Model->where("student.parent_id=".session('parent.id')." and biz_homework.homework_status=1")->field("biz_class.id,biz_class.name")
                    ->join("biz_homework on biz_homework.class_id=biz_class.id")
                    ->join('dict_course on dict_course.id=biz_homework.course_id')
                    ->join('biz_class_student class_contact on class_contact.class_id=biz_class.id')
                    ->join('auth_student student on student.id=class_contact.student_id')
                    ->order('sort_order asc')->group("biz_class.name")->select();
        $this->assign('class_list', $courses);  */

        $grade_model=M('dict_grade');
        $grade_result=$grade_model->where("auth_student.parent_id=".session('parent.id'))->join("biz_class on biz_class.grade_id=dict_grade.id")
                ->join("biz_class_student on biz_class_student.class_id=biz_class.id")
                ->join("auth_student on auth_student.id=biz_class_student.student_id")
                ->field('dict_grade.id,dict_grade.grade')->group("dict_grade.id")->select();
        //年级不为空,求出班级和学科
        if(!empty($where['dict_grade.id'])){
            $class_model = M('biz_class');
            $class_result=$grade_model->where("dict_grade.id=".$where['dict_grade.id']." and auth_student.parent_id=".session('parent.id'))->join("biz_class on biz_class.grade_id=dict_grade.id")
                        ->join("biz_class_student on biz_class_student.class_id=biz_class.id")
                        ->join("auth_student on auth_student.id=biz_class_student.student_id")
                        ->field('biz_class.id,biz_class.name')->group("biz_class.id")->select();

            //$course_model = M('dict_course');
            $course_result=$course_result=$grade_model->where("dict_grade.id=".$where['dict_grade.id']." and auth_student.parent_id=".session('parent.id'))->join("biz_class on biz_class.grade_id=dict_grade.id")
                ->join("biz_class_student on biz_class_student.class_id=biz_class.id")
                ->join("auth_student on auth_student.id=biz_class_student.student_id")
                ->join('auth_teacher_second on auth_teacher_second.teacher_id=biz_class.class_teacher_id')
                ->join('dict_course on dict_course.id=auth_teacher_second.course_id')
                ->field('dict_course.id,dict_course.course_name')->group("dict_course.id")->select();
        }

        //年级和学科不为空,求出教材分册
        if(!empty($where['dict_grade.id']) && !empty($where['dict_course.id'])){
            $textbook_model = M('biz_textbook');
            $textbook_result=$textbook_model->where("grade_id=".$where['dict_grade.id']." and course_id=".$where['dict_course.id'])
                ->field('id,name')->select();
        }
        $this->assign('grade_list', $grade_result);
        $this->assign('class_list', $class_result);
        $this->assign('course_list', $course_result);
        $this->assign('textbook_list', $textbook_result);

        $this->assign('default_grade', $where['dict_grade.id']);
        $this->assign('default_name', I('name'));
        $this->assign('default_class', $class_id);
        $this->assign('default_course', $where['dict_course.id']);
        $this->assign('default_textbook', $where['biz_textbook.id']);
        $this->assign('default_type', intval(I('type')));
        $this->assign('keyword', I('keyword'));

        $this->display();
    }

    //错误集
    public function wrongHomework(){
        if (!session('?parent')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '错题集');
        $this->assign('navicon', 'zuoyexitong');

        $homework_id=  intval(I('homework_id'));
        $student_id=  intval(I('student_id'));
        if(!$homework_id || !$student_id){
            redirect(U('Index/systemError'));
        }

        $where['biz_homework_score_details.student_id'] = $student_id;
        $where['biz_homework.id'] = $homework_id;
        $where['biz_homework.homework_status'] = 1;
        $where['_string']='biz_homework_score_details.flag!=1';

        $Model = M('biz_homework_score_details');
        $result = $Model
            ->join("biz_exercise_library on biz_exercise_library.id=biz_homework_score_details.question_org_id")
            ->join("biz_homework on biz_homework.id=biz_homework_score_details.homework_id")
            ->join("auth_student on auth_student.id=biz_homework_score_details.student_id")
            ->field("distinct biz_homework_score_details.*,biz_exercise_library.questions,biz_exercise_library.answer,biz_exercise_library.chapter_id,biz_homework.homework_name as homework,biz_homework.create_at")
            ->where($where)
            ->order('biz_homework.create_at desc')
            ->select();

        $this->assign('homework_id', $homework_id);
        $this->assign('student_id', $student_id);
        $this->assign('list', $result);

        $this->display();
    }



    //作业完成情况
    public function homeworkCompleteDetails()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '查作业');
        $this->assign('navicon', 'zuoyexitong');


        //试题信息
        //$homeworkId = $_GET['homeworkId'];
        //$studentId = $_GET['studentId'];

        $homeworkId = getParameter('homeworkId', 'int',false,2);
        $studentId = getParameter('studentId', 'int',false,2);
        if(!$homeworkId || !$homeworkId){
            redirect(U('Index/systemError'));
        }
        $this->assign('homeworkId', $homeworkId);
        $Model = M('biz_homework');
        $exerciseChapter = $Model->where("id=$homeworkId and homework_status=1")->find();
        $this->assign('exerciseChapter', $exerciseChapter);

        //获取题目数量
        $exercise_chapter_id = $exerciseChapter['exercise_chapter_id'];
        $Model = M('biz_exercise_library');
        $exerciseCount = $Model->where("chapter_id=$exercise_chapter_id")->count();
        $this->assign('exerciseCount', $exerciseCount);

        //$studentId = $_GET['studentId'];
        //判断是否存在作业了
        $Model = M('biz_homework_student_details');
        $result = $Model
            ->where("student_id=$studentId and homework_id=$homeworkId")
            ->find();
        $this->assign('homework', $result);
        //如果不存在作业,说明学生没写

        $where['homework_id']=$homeworkId;
        $homework_exercise_model=M('biz_homework_exercise');
        $chapter_result=$homework_exercise_model->where($where)
                        ->join('biz_exercise_library_chapter chapter on chapter.id=biz_homework_exercise.chapter_id')
                        ->group('chapter.id')
                        ->field('chapter.chapter,chapter.festival')
                        ->select();
        $this->assign('chapter_data', $chapter_result);

        //获得年级班级
        $class_model=M('biz_class');
        $class_condition['biz_class.id']=$exerciseChapter['class_id'];
        $class_grade_result=$class_model->where($class_condition)->join('dict_grade on dict_grade.id=biz_class.grade_id')
                    ->field('biz_class.name class_name,dict_grade.grade')->find();
        $this->assign('class_grade_data',$class_grade_result);
        //获取学生得分细节
        /*$Model = M('biz_homework_score_details');
        $scoreDetails = $Model->where("homework_id=$homeworkId and student_id=$studentId")->find();
        $this->assign('scoreDetails', $scoreDetails);
        */
        //$this->assign('homework', $result);

        $this->display();
    }

       //判断是否有权限
    public function isAuth( $c_a ) {

        $is_auth = in_array($c_a, session('auth_parent'));

        if ( $is_auth ) {
            return true;
        } else {
            return false;
        }
    }
    //京版概览
    function jboverview()
    {

        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);

        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Parent/index1?auth_error=1'));
        }

        $this->assign('module', '励耘圈');
        $this->assign('nav', '京版概览');
        $this->assign('subnav', '京版概览');
        $this->assign('navicon', 'jingbangailan');

        $Model = M('biz_bj_overview');
        $where['status']=2;
        $content = $Model->where($where)->select();
        $this->assign('data', $content[0]);

        $this->display();
    }

    //nobook
    public function bjResourceTools(){
        $this->assign('module', '教学+');
        $this->assign('nav', '京版数字资源');
        $this->assign('subnav', 'nobook');
        $this->assign('navicon', 'jingbanziyuan');

        $filter['cat'] = $_REQUEST['cat'];
        if(!empty($filter['cat'])) $filter['cat'];

        if($filter['cat']<0 || $filter['cat']>1){
            $filter['cat']=0;
        }
        $time=time();
        $appid=C('NOBOOK_CONFIG.appid');
        $appkey=C('NOBOOK_CONFIG.appkey');
        $string=md5($appid.$time.$appkey);

        if($filter['cat']==1){
            //生物
            $this->assign('cat', 1);
            $url = "http://shengwu.nobook.com.cn/openapi/get_resource?appid=".$appid.'&code='.$string."&time=".$time;
        }else{
            //科学
            $this->assign('cat', 0);
            $url = "http://science.nobook.com.cn/openapi/get_resource?appid=".$appid.'&code='.$string."&time=".$time;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        $data=json_decode($output);

        $this->assign('appid', C('NOBOOK_CONFIG.appid'));
        $this->assign('appkey', C('NOBOOK_CONFIG.appkey'));
        $this->assign('list', $data);
        $this->display();
    }


    //物理实验
    public function bjResourceToolsPhysics(){
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }
        $this->assign('module', '教学+');
        $this->assign('nav', '京版数字资源');
        $this->assign('subnav', '资源列表');
        $this->assign('navicon', 'jingbanziyuan');

        $time=time();
        $appid=C('NOBOOK_CONFIG.appid');
        $appkey=C('NOBOOK_CONFIG.appkey');
        $string=md5($appid.$time.$appkey);
        $url = "http://wuli.nobook.com.cn/openapi?appid=".$appid.'&code='.$string."&time=".$time;
        $this->assign('url', $url);

        $this->display();
    }


    //京版资源列表
    public function bjResourceList()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Parent/index1?auth_error=1'));
        }

        $this->assign('module', '教学+');
        $this->assign('nav', '京版数字资源');
        $this->assign('subnav', '资源列表');
        $this->assign('navicon', 'jingbanziyuan');
        /*
        $filter['course_id'] = $_REQUEST['course'];
        $filter['grade_code'] = $_REQUEST['grade'];
        //$filter['textbook_id'] = $_REQUEST['textbook_id'];
        $filter['school_term'] = $_REQUEST['textbook'];
        $filter['type'] = $_REQUEST['type'];
        $filter['keyword'] = $_REQUEST['keyword'];
        $filter['sort_column'] = $_REQUEST['sort_column'];
        */
        $filter['course_id'] =getParameter('course', 'int',false);
        $filter['grade_code'] =getParameter('grade', 'int',false);
        //$filter['textbook_id'] = $_REQUEST['textbook_id'];
        $filter['school_term'] =getParameter('textbook', 'int',false);
        $filter['type'] = getParameter('type', 'str',false);
        $filter['keyword'] = $_REQUEST['keyword'];
        $filter['sort_column'] =getParameter('sort_column', 'int',false);

        $check['biz_bj_resources.status'] = 2;

        if (!empty($filter['type'])) $check['biz_bj_resources.type'] = $filter['type'];
        if (!empty($filter['keyword'])) $check['biz_bj_resources.name'] = array("like", "%" . $filter['keyword'] . "%");
        if (!empty($filter['school_term'])) $check['biz_textbook.school_term'] = $filter['school_term'];
        if (empty($filter['sort_column'])) $filter['sort_column'] = '0';

        $unique_grade_code=intval($filter['grade_code']);;
        if($unique_grade_code==14){
            $check['_string']="dict_grade.code in (14)";
        }elseif($unique_grade_code==15){
            $check['_string']="dict_grade.code in (15)";
        }elseif($unique_grade_code==16){
            $check['_string']="dict_grade.code in (16)";
        }else{
            if($unique_grade_code>0 && $unique_grade_code<7){
                $check['_string']="dict_grade.code in (14,".$unique_grade_code.")";
            }elseif($unique_grade_code>6 && $unique_grade_code<10){
                $check['_string']="dict_grade.code in (15,".$unique_grade_code.")";
            }elseif($unique_grade_code>9 && $unique_grade_code<13){
                $check['_string']="dict_grade.code in (16,".$unique_grade_code.")";
            }
        }

        $unique_course_id=intval($filter['course_id']);
        if($unique_course_id==-1){
            if(empty($ceck['_string'])){
                if (!empty($filter['course_id'])) $check['_string'] = 'biz_bj_resources.course_id in ('.$unique_course_id.')';
            }else{
                if (!empty($filter['course_id'])) $check['_string'] .= ' and biz_bj_resources.course_id in ('.$unique_course_id.')';
            }
        }else{
            if(empty($check['_string'])){
                if (!empty($filter['course_id'])) $check['_string'] = 'biz_bj_resources.course_id in (-1,'.$unique_course_id.')';
            }else{
                if (!empty($filter['course_id'])) $check['_string'] .= ' and biz_bj_resources.course_id in (-1,'.$unique_course_id.')';
            }
        }
        if(empty($check['_string'])){
            $check['_string']="biz_bj_resources.type!='html' and biz_bj_resources.type!='others'";
        }else{
            $check['_string'].=" and biz_bj_resources.type!='html' and biz_bj_resources.type!='others'";
        }

        $sort=I('sort_column');
        if (!empty($sort) && $sort != '') {
            switch ($sort) {
                case 0:
                    $sort_string= "zan_count desc";
                    break;
                case 1:
                    $sort_string= "zan_count asc";
                    break;
                case 2:
                    $sort_string= "favorite_count desc";
                    break;
                case 3:
                    $sort_string= "favorite_count asc";
                    break;
                case 4:
                    $sort_string= "follow_count desc";
                    break;
                case 5:
                    $sort_string= "follow_count asc";
                    break;
                case 6:
                    $sort_string= "create_at desc";
                    break;
                case 7:
                    $sort_string= "create_at asc";
                    break;
                default:
                    $sort_string= "create_at desc";
                    break;
            }
            if($sort>=0 && $sort<8){
                $filter['sort_column']=$sort;
            }else{
                $filter['sort_column']=6;
            }
        }else{

            if ( $sort != '' ) {

                $sort_string= "zan_count desc";
                $filter['sort_column']=0;
            }else{

                $sort_string= "create_at desc";
                $filter['sort_column']=6;
            }
        }

        $check['isdisplay'] = '1';

        $this->assign('course_id', $filter['course_id']);
        $this->assign('grade_code', $filter['grade_code']);
        $this->assign('textbook_id', $filter['school_term']);
        $this->assign('type', $filter['type']);
        $this->assign('keyword', $filter['keyword']);
        $this->assign('sort_column', $filter['sort_column']);

        $Model = M('biz_bj_resources');

        $count = $Model
            ->join('biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id')
            ->join('dict_grade on dict_grade.id=biz_bj_resources.grade_id')
            ->field('biz_bj_resources.*,biz_textbook.name as textbook')
            ->where($check)
            ->count();
        $Page = new \Think\Page($count, 21);
        //C('PAGE_SIZE_FRONT')
        /*
        foreach ($filter as $key => $val) {
            $Page->parameter[$key] = urlencode($val);
        }*/
        $show = $Page->show();

        $result = $Model
            ->join('biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id')
            ->join('dict_grade on dict_grade.id=biz_bj_resources.grade_id')
            ->field("biz_bj_resources.*,biz_textbook.name as textbook,if(UNIX_TIMESTAMP(NOW())-604800>biz_bj_resources.create_at,'no','yes') is_new")
            ->where($check)
            ->order("biz_bj_resources." . $sort_string)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $this->assign('list', $result);
        $this->assign('page', $show);

        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        $this->assign('courses', $courses);

        $Model = M('dict_grade');
        $grades = $Model->select();
        $this->assign('grades', $grades);

        /*
        $TextbookModel = M('biz_textbook');
        $c1['course_id'] = $filter['course_id'];
        $c1['grade_code'] = $filter['grade_code'];
        $textbooks = $TextbookModel->where($c1)->select();
        $this->assign('textbooks', $textbooks);
        */

        $this->display_nocache();
    }

    //京版资源详情
    public function bjResourceDetails($id,$type=2)
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }


        if ($type != 1) {

            $isAuth = $this->isAuth($this->c_a);
            if (!$isAuth) { //如果访问的模块没有权限
            //    redirect(U('Parent/index1?auth_error=1'));
            }
        }

        $this->assign('module', '教学+');
        $this->assign('nav', '京版资源');
        $this->assign('navicon', 'jingbanziyuan');

        $id=intval($id);
        if(empty($id))
        //$id = $_GET['id'];
          $id = getParameter('id', 'int',false);

        $Model = M('biz_bj_resources');
        $result = $Model
            ->join('biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id')
            ->join('dict_course on biz_bj_resources.course_id=dict_course.id','left')
            ->join('dict_grade on biz_bj_resources.grade_id=dict_grade.id','left')
            ->field('biz_bj_resources.*,biz_textbook.name as textbook,dict_course.course_name,dict_grade.grade')
            ->where("biz_bj_resources.id=$id")
            ->find();

        $this->assign('subnav', $result['name']);

        $this->assign('data', $result);

        //拿到关联表的数据
        $contact_result=$Model->where("biz_bj_resources.id=".$id)->join("biz_bj_resource_contact on biz_bj_resource_contact.biz_bj_resource_id=biz_bj_resources.id")
            ->field("biz_bj_resource_contact.*")->select();
        $this->assign('contact_data', $contact_result);

        //观看次数+1
        $Model->where("id=$id")->setInc('follow_count', 1);
        //判断我是否赞过和收藏过
        $ZanModel = M('biz_bj_resource_zan');
        $zanData['resource_id'] = $id;
        $zanData['role'] = 2;
        $zanData['user_id'] = session('parent.id');
        $existedZan = $ZanModel->where($zanData)->find();
        $existedZan = empty($existedZan) ? 'no' : 'yes';

        $this->assign('existedZan', $existedZan);

        $FavorModel = M('biz_bj_resource_collect');
        $favorData['resource_id'] = $id;
        $favorData['role'] = 2;
        $favorData['user_id'] = session('parent.id');
        $existedFavor = $FavorModel->where($favorData)->find();
        $existedFavor = empty($existedFavor) ? 'no' : 'yes';
        $this->assign('existedFavor', $existedFavor);
        $this->assign('REMOTE_ADDR',C('REMOTE_ADDR'));
        $this->display();

    }

    //赞一个京版资源
    public function zanBjResource()
    {
        $parentId = session('parent.id');
        if(empty($parentId))
         {
         //$parentId = $_GET['user_id'];
           $parentId = getParameter('user_id', 'int',false);
         if(empty($parentId))
          {
          redirect(U('Index/index'));
          }
         }

        //$id = $_GET['id'];
         $id = getParameter('id', 'int');

        $ZanModel = M('biz_bj_resource_zan');
        $zanData['role'] = 2;
        $zanData['resource_id'] = $id;
        $zanData['user_id'] = $parentId;
        $existed = $ZanModel->where($zanData)->find();
        if (empty($existed)) {
            $zanData['create_at'] = time();
            $res = M('auth_parent')->where("id=$parentId")->find();
            $zanData['user_name'] = $res['parent_name'];
            $ZanModel->add($zanData);
             $Model = M('biz_bj_resources');
            $Model->where("id=$id")->setInc('zan_count', 1);
            $this->ajaxReturn("success");
        } else {
            $ZanModel->where("resource_id=$id and role=2 and user_id=$parentId")->delete();
            $Model = M('biz_bj_resources');
            $Model->where("id=$id")->setDec('zan_count', 1);
            $this->ajaxReturn("已经取消点赞");
        }
    }



    //收藏一个京版资源
    public function favorBjResource()
    {
        $parentId = session('parent.id');
        if(empty($parentId))
         {
         //$parentId = $_GET['user_id'];
           $parentId = getParameter('user_id', 'int',false);
         if(empty($parentId))
          {
          redirect(U('Index/index'));
          }
         }

        //$id = $_GET['id'];
        $id=getParameter('id', 'int');

         $FavorModel = M('biz_bj_resource_collect');
         $favorData['resource_id'] = $id;
         $favorData['role'] = 2;
         $favorData['user_id'] = $parentId;

         $existed = $FavorModel->where($favorData)->find();
         if (empty($existed)) {
             $favorData['create_at'] = time();
             $res = M('auth_parent')->where("id=$parentId")->find();
             $favorData['user_name'] = $res['parent_name'];
             $FavorModel->add($favorData);

             $Model = M('biz_bj_resources');
             $Model->where("id=$id")->setInc('favorite_count', 1);
             $this->ajaxReturn("success");
         } else {
             $FavorModel->where("resource_id=$id and role=2 and user_id=$parentId")->delete();
             $Model = M('biz_bj_resources');
             $Model->where("id=$id")->setDec('favorite_count', 1);
             $this->ajaxReturn("已经取消收藏");
         }
    }

    //教师详情
    public function teacherDetails()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        //$id = $_GET['id'];
        $id = getParameter('id', 'int',false);

        $this->assign('module', '教师风采');
        $this->assign('nav', '教师详情');
        $this->assign('navicon', 'jiaoshifengcai');


        $Model = M('auth_teacher');
        $result = $Model
            ->join("dict_schoollist on auth_teacher.school_id=dict_schoollist.id")
            ->field('auth_teacher.id,auth_teacher.name,auth_teacher.avatar,auth_teacher.points,auth_teacher.brief_intro,auth_teacher.email,dict_schoollist.school_name')
            ->where("auth_teacher.id=$id")
            ->find();

        $this->assign('subnav', $result['name']);

        $this->assign('data', $result);

        $Model = M('biz_resource');
        $check['biz_resource.status'] = 2;
        $check['biz_resource.teacher_id'] = $id;
        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id')
            ->field('biz_resource.*,biz_textbook.name as textbook')
            ->where($check)
            ->order("biz_resource.create_at desc")
            ->select();

        $this->assign('resources', $result);

        $this->display();

    }


    //小孩所在班级
    public function classList()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Parent/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '我的班级');
        $this->assign('subnav', '');
        $this->assign('navicon', 'jiazhangduxue');

        $Model = M('biz_class_student');
        //$teacherId = session("teacher.id");

        //$filter['grade_id'] = $_REQUEST['grade'];
        $filter['grade_id'] = getParameter('grade', 'int',false);

        if ($filter['grade_id'] == 0) { //如果没有选择班级 那么就把年级也设置为0
            $filter['class_id'] = 0;
        } else {
            //$filter['class_id'] = $_REQUEST['class'];
            $filter['class_id'] = getParameter('class', 'int',false);
        }

        if (!empty($filter['grade_id'])) $check['dict_grade.id'] = $filter['grade_id'];
        if (!empty($filter['class_id'])) $check['biz_class.id'] = $filter['class_id'];

        $this->assign('class_id_view',$filter['class_id']);

        $this->assign('default_grade',$check['dict_grade.id']);
        $this->assign('default_class',$check['biz_class.id']);

        $check['biz_class.flag'] =  2;

        if (!empty($filter['grade_id'])) $where['dict_grade.id'] = $filter['grade_id'];
        if (!empty($filter['class_id'])) $where['biz_class.id'] = $filter['class_id'];
        $student_id = I('student_id');
        $where['biz_class_student.student_id'] =  I('student_id');
        $where['biz_class.is_delete'] =  0;
        $where['biz_class_student.status'] =  2;

        $result = $Model
            ->join('biz_class on biz_class.id=biz_class_student.class_id')
            ->join('left join biz_class_teacher ON biz_class_teacher.class_id = biz_class.id')
            ->join('left join auth_teacher ON auth_teacher.id = biz_class_teacher.teacher_id')
            //->join('left join dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_class_student.class_id,biz_class.*,dict_grade.grade,dict_grade.id as did,count(biz_class_teacher.teacher_id) as teacher_count,auth_teacher.school_id as ats_id')
            ->where($where)
            ->group('biz_class.id')
            ->order('biz_class.create_at desc')
            ->select();

        foreach ($result as $k=>$v) {
            if ($v['class_status'] == 2) {
                $d_m['id'] = $v['ats_id'];
                $sc_name = M('dict_schoollist')->where( $d_m )->find();

                $result[$k]['school_name'] = $sc_name['school_name'];
            } else {
                $d_m['id'] = $v['school_id'];
                $sc_name = M('dict_schoollist')->where( $d_m )->find();
                $result[$k]['school_name'] = $sc_name['school_name'];
            }
            $stucount = M('biz_class_student')->where('class_id='.$v['class_id'])->count();
            $stucount = $stucount -1;
            if ( $stucount < 0 ) {
                $stucount = 0;
            }
            $result[$k]['student_count'] = $stucount;
        }


        $this->assign('list', $result);

        //年级不为空求出班级
        if(!empty($filter['grade_id'])){
            $class_model=M('biz_class');
            $classmap['grade_id'] = $filter['grade_id'];
            $classmap['class_teacher_id'] = session("teacher.id");
            $class_list = $class_model->where($classmap)->field('id,name')->select();
        }

        $stuinfo = M('auth_student')
                    ->join('left join dict_schoollist on dict_schoollist.id=auth_student.school_id')
                    ->where("auth_student.id={$student_id}")
                    ->field('auth_student.student_name,dict_schoollist.school_name,auth_student.avatar,auth_student.sex')
                    ->find();


        $this->assign('stuinfo',$stuinfo);

        $grade_model = M('dict_grade');
        $grade_list = $grade_model->select();
        $this->assign('grade_list', $grade_list);
        $this->assign('class_list', $class_list);


        $this->display();
    }

    //管理我的小孩
    function myChildren()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Parent/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '督学窗');
        $this->assign('subnav', '');
        $this->assign('navicon', 'jiazhangduxue');

        $Model = M('auth_student_parent_contact');
                $result = $Model
                    ->join('LEFT JOIN auth_student on auth_student.id=auth_student_parent_contact.student_id')
                    ->join('LEFT JOIN dict_schoollist on dict_schoollist.id=auth_student.school_id')
                    ->field('auth_student_parent_contact.*,auth_student.student_name,auth_student.id_card,dict_schoollist.school_name')
                    ->where("auth_student_parent_contact.parent_id=" . session('parent.id'))
                    ->order('create_at desc')
                    ->select();

        $this->assign('list', $result);

        $this->display();
    }

    //管理我的小孩(学习轨迹查看)
    function learnPathList()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Parent/index1?auth_error=1'));
        }
        $filter['keyword'] = $_REQUEST['keyword'];


        $this->assign('module', '班级行');
        $this->assign('nav', '学习轨迹');
        $this->assign('subnav', '');
        $this->assign('navicon', 'xuexiguiji');

        $result = D('Auth_student')->getParentStudents(session('parent.id'),'','',$filter['keyword']);

        $this->assign('keyword', $filter['keyword']);
        $this->assign('list', $result);

        $this->display();
    }

    //添加学生

    public function addMyChildren()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        if ($_POST) {
            if (!session('?parent')) {
                redirect(U('Index/index'), 0, '登录超时，页面跳转中...');
            }
            /*
            $isAddChild = empty($_GET['id']);
            $data['student_name'] = remove_xss($_POST['student_name']);
            $data['sex'] = $_POST['sex'];
            $data['birth_date'] = strtotime($_POST['birthday']);
            if(!empty($_POST['school_id']))
            $data['school_id'] = $_POST['school_id'];
            $data['email'] = $_POST['email'];
            $data['student_id'] = $_POST['student_id'];
            $data['id_card'] = $_POST['id_card'];
            $data['avatar'] = $_POST['os_avatar'];
            */

            $isAddChild = getParameter('id', 'int',false);
            $data['student_name'] = getParameter('student_name', 'str',false);
            $data['sex'] = getParameter('sex', 'str',false);
            $data['birth_date'] = strtotime(getParameter('birthday', 'str',false));
            $data['school_id'] = getParameter('school_id', 'int',false);
            $data['email'] = getParameter('email', 'str',false);
            $data['student_id'] = getParameter('student_id', 'int',false);
            $data['id_card'] = getParameter('id_card', 'str',false);
            $data['avatar'] = getParameter('os_avatar', 'str',false);

            $str = "0123456789abcdefghijklmnopqrstuvwxyz";//输出字符集
            $len = strlen($str) - 1;
            $s = '';
            for ($i = 0; $i < 10; $i++) {
                $s .= $str[rand(0, $len)];
            }
            $data['access_token'] = $s;

            $data['parent_id'] = session('parent.id');
            $data['parent_name'] = session('parent.parent_name');
            $data['parent_tel'] = getParameter('parent_tel', 'str',false);;
            $data['update_at'] = time();

            $vip_config=C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');

            if(!$isAddChild)
            {
             $data['create_at'] = time();
             $data['password'] = sha1($_POST['password']);

             $Model = M('auth_student');
             $ModelParentStudent = M('auth_student_parent_contact');
            //判断用户名是否已经存在
            $check['student_name'] = $data['student_name'];
            $check['parent_tel'] = $data['parent_tel'];

            $existedUserName = $Model->where($check)->find();


            $parent_map['student_id'] = $existedUserName['id'];
            $parent_map['parent_id'] = session('parent.id');

            $existeguanlian = $ModelParentStudent->where( $parent_map )->find();


            if ( !empty($existedUserName)  ) {

                if (!empty($existeguanlian)) {
                    $arr['msg']='已经进行了关联';
                    $arr['code']=-1;
                    echo json_encode($arr);
                    die;
                }

                $dataMatch['parent_id'] = session('parent.id');
                $dataMatch['student_id'] = $existedUserName['id'];
                $dataMatch['parent_tel'] = $check['parent_tel'];

                if(!$ModelParentStudent->add($dataMatch)){
                    $Model->rollback();
                    $arr['msg']='添加失败';
                    $arr['code']=-2;
                    echo json_encode($arr);
                    die;
                }
                if($vip_config && $vip_config<=3){
                    $result=give_new_vip_operation(3, $vip_config,$existedUserName['id'],$existedUserName['school_id']);
                    if($result['status']=='failed'){
                        $Model->rollback();
                        $arr['msg']='添加失败';
                        $arr['code']=-2;
                        echo json_encode($arr);
                        die;
                    }
                }
                 //手机推送给家长
                $parent_id=$data['parent_id'];
                $parameter_arr=array(
                    'msg'=>array($data['student_name']),
                    'url'=>array(
                        'type'=>0,
                        'data'=>array()
                    )
                );
                A('Home/Message')->addPushUserMessage('ADDCHILD',4,$parent_id,$parameter_arr);


                 $arr['msg']='添加成功';
                 $arr['code']=0;
                 echo json_encode($arr);
                 die;
            } else {

                    $arr['msg']='对不起用户不存在';
                    $arr['code']=-1;
                    echo json_encode($arr);
                    die;
                }
           }
           else //edit child
            {
             $studentId = $_GET['id'];
             $Model = M('auth_student');
             $ModelParentStudent = M('auth_student_parent_contact');

            $check['student_name'] = $data['student_name'];
            $check['parent_tel'] = $data['parent_tel'];
            $existedUserName = $Model->where($check)->find();

            if (empty($existedUserName) ) {
                $arr['msg']='对不起用户不存在';
                $arr['code']=-1;
                echo json_encode($arr);
            }


            $parent_map['student_id'] = $existedUserName['id'];
            $parent_map['parent_id'] = session('parent.id');

            $existeguanlian = $ModelParentStudent->where( $parent_map )->find();


            if (!empty($existeguanlian)) {
                $arr['msg']='已经进行了关联';
                $arr['code']=-1;
                echo json_encode($arr);
                die;
            } else {

            }

            A('Home/Message')->addPushUserMessage('INFO_MODIFIED_BYSELF_CHILD',4,$existeguanlian['parent_id'],$parameter_arr);
            $parentInfo = D('Auth_parent')->getParentInfo($existeguanlian['parent_id']);
            $parameter_arr=array(
                'msg'=>array($parentInfo['parent_name']),
                'url'=>array(
                    'type'=>0,
                    'data'=>array()
                )
            );
            A('Home/Message')->addPushUserMessage('INFO_MODIFIED_BYOTHER_STUDENT',3,$studentId,$parameter_arr);


              $arr['msg']='编辑成功';
              $arr['code']=1;
              echo json_encode($arr);
              die;
            }
        } else {
            $this->assign('module', '班级行');
            $this->assign('nav', '家长督学');
            $this->assign('subnav', '小孩管理');
            $this->assign('navicon', 'jiazhangduxue');

            if (!empty($_GET['id'])) //edit child
            {
                  //$studentId = $_GET['id'];
                $id = getParameter('id', 'int',false);
                $StudentModel = M('auth_student_parent_contact');

                $result = $StudentModel
                        ->join('left join auth_student on auth_student.id=auth_student_parent_contact.student_id')
                        ->field('auth_student_parent_contact.id,auth_student.student_name,auth_student.parent_tel')
                        ->where("auth_student_parent_contact.id=$id")
                        ->find();

                $this->assign('data', $result);

            }
            $this->display();
        }
    }

    public function VerifyStudentPhone() {
        $data['student_name'] = $_GET['student_name'];
        $data['parent_tel'] = $_GET['parent_tel'];

        $check['student_name'] = $data['student_name'];
        $check['parent_tel'] = $data['parent_tel'];

        $Model = M('auth_student');
        $ModelParentStudent = M('auth_student_parent_contact');

        $existedUserName = $Model->where($check)->find();


        $parent_map['student_id'] = $existedUserName['id'];
        $parent_map['parent_id'] = session('parent.id');

        $existeguanlian = $ModelParentStudent->where( $parent_map )->find();

        if ( !empty($existedUserName)  ) {

            if (!empty($existeguanlian)) {
                $arr['msg']='已经进行了关联';
                $arr['code']=-1;
                echo json_encode($arr);
                die;
            } else {
                $arr['msg']='ok';
                $arr['code']=1;
                $arr['student_id']=$existedUserName['id'];
                echo json_encode($arr);
                die;
            }

        } else {

            $arr['msg']='对不起学生不存在';
            $arr['code']=-1;
            echo json_encode($arr);
            die;
        }
    }

    //删除小孩
    function deleteMyChildren()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        //$id = $_GET['id'];
        $id = getParameter('id', 'int');

        $Model = M('auth_student_parent_contact');
        $c1['id'] = $id;
        $c1['parent_id'] = session('parent.id');
        $Model->where($c1)->delete();

        $this->ajaxReturn('success');
    }

    //修改小孩头像  没用到
    function modifyChildAvatar()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '小孩管理');
        $this->assign('navicon', 'jiazhangduxue');

        //$id = $_GET['id'];
        $id = getParameter('id', 'int',false,2);

        $Model = M('auth_student');
        $c1['id'] = $id;
        $c1['parent_id'] = session('parent.id');
        $student = $Model->where($c1)->find();

        session('currentMyClildId', $id);

        $this->assign('student', $student);

        $this->display();
    }

    //这个没用到
    function modifyChildPassword()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        if ($_POST) {
            //$check['id'] = $_POST['student_id'];
            //$data['password'] = sha1($_POST['password']);
            $check['id'] =getParameter('id', 'int',false);
            $data['password'] =sha1(getParameter('name', 'str',false));
            $Model = M('auth_student');
            $Model->where($check)->save($data);
            $this->redirect("Parent/myChildren");
        } else {
            $this->assign('module', '班级行');
            $this->assign('nav', '小孩管理');
            $this->assign('navicon', 'jiazhangduxue');

            //$id = $_GET['id'];
            $id = getParameter('id', 'int',false,2);
            $Model = M('auth_student');
            $c1['id'] = $id;
            $c1['parent_id'] = session('parent.id');
            $student = $Model->where($c1)->find();
            $this->assign('student', $student);
            $this->display();
        }


    }

    public function ossUploadAvatar() {
        //$id = $_GET['id'];
        $id = getParameter('id', 'int');
        if($_POST['img'])
        {
            saveTempAvatar($_POST['img']);
        }
        $img_path = $this->upload_file();
        $img_path_url = json_decode($img_path,true);
        $Model = M('auth_parent');
        $c1['id'] = $id;
        $data['avatar'] = $img_path_url['res'];
        $a = $Model->where($c1)->save($data);

        //print_r(M()->getLastsql());die();
        $rs['status'] = 1;
        $rs['src'] = C('oss_path').$img_path_url['res'];
        echo json_encode($rs);
    }




     //教师资源文件上传
    public function upload_file(){
        //$video_image=$this->resource_video_image_upload();

        $upload = new \Oss\Ossupload();// 实例化上传类
        $result=$upload->upload(3,$_FILES,3,0); //1 pic 2//
        $returnArray = explode(",",$result[1]);
        $uploadOK = 1;
        for($i=0;$i<sizeof($returnArray);$i++)
         {
           if($returnArray[$i] == "")
            {
            $uploadOK = 0;
            break;
            }
         }
        if($uploadOK == 0)
         {
          $arr['msg']='上传失败';
          $arr['code']=-1;
         }
        else
         {
          $arr['msg']='上传成功';
          $arr['code']=0;
         }
        $arr['res']=$result[1];
        $arr['message_video_image']='';
        return json_encode($arr);
    }

    // 上传小孩头像
    public function uploadAvatar()
    {
       $save_path = "./Uploads/StudentAvatars";
               $id = session('currentMyClildId');
               if($id =="")
               //$id = $_GET['id'];
               $id = getParameter('id', 'int',false);
               $filename_id = $id . ".jpg";

               $post_input = 'php://input';
               $postdata = file_get_contents($post_input);

               if (isset($postdata) && strlen($postdata) > 0) {
                   $filename = $save_path . '/' . $filename_id;
                   $handle = fopen($filename, 'w+');
                   fwrite($handle, $postdata);
                   fclose($handle);
                   if (is_file($filename)) {
                       echo 'Image data save successed,file:' . $filename;
                       $Model = M('auth_student');
                       $c1['id'] = $id;
                       $c1['parent_id'] = session('parent.id');

                       $data['avatar'] = $id . '.jpg';
                       $a = $Model->where($c1)->save($data);
                       $rs['status'] = 1;

                       echo json_encode($rs);
                       exit();
                   } else {
                       die ('Image upload error!');
                   }
               } else {
                   die ('Image data not detected!');
               }
    }

    public function uploadAvatarStu()
        {
            $save_path = "./Uploads/StudentAvatars";
            //$id = $_GET['id'];
            $id = getParameter('id', 'int',false);

            $filename_id = $id . ".jpg";

            $post_input = 'php://input';
            $postdata = file_get_contents($post_input);

            if (isset($postdata) && strlen($postdata) > 0) {
                $filename = $save_path . '/' . $filename_id;
                $handle = fopen($filename, 'w+');
                fwrite($handle, $postdata);
                fclose($handle);
                if (is_file($filename)) {
                    echo 'Image data save successed,file:' . $filename;
                    $Model = M('auth_student');
                    $c1['id'] = $id;
                    //$c1['parent_id'] = session('parent.id');

                    $data['avatar'] = $id . '.jpg';
                    $a = $Model->where($c1)->save($data);
                    $rs['status'] = 1;

                    echo json_encode($rs);
                    exit();
                } else {
                    die ('Image upload error!');
                }
            } else {
                die ('Image data not detected!');
            }
        }
         public function uploadParentAvatar()
        {
            $save_path = "./Uploads/ParentAvatars";
            //$id = $_GET['id'];
            $id = getParameter('id', 'int',false);

            $filename_id = $id . ".jpg";

            $post_input = 'php://input';
            $postdata = file_get_contents($post_input);

            if (isset($postdata) && strlen($postdata) > 0) {
                $filename = $save_path . '/' . $filename_id;
                $handle = fopen($filename, 'w+');
                fwrite($handle, $postdata);
                fclose($handle);
                if (is_file($filename)) {
                    echo 'Image data save successed,file:' . $filename;
                    $Model = M('auth_parent');
                    $c1['id'] = $id;
                    //$c1['parent_id'] = session('parent.id');

                    $data['avatar'] = $id . '.jpg';
                    $a = $Model->where($c1)->save($data);
                    $rs['status'] = 1;

                    echo json_encode($rs);
                    exit();
                } else {
                    die ('Image upload error!');
                }
            } else {
                die ('Image data not detected!');
            }
        }
    //学生轨迹
    public function learningPath()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Parent/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '学习轨迹');
        $this->assign('navicon', 'xuexiguiji');


        //$studentId = $_GET['id'];
        //$classId = $_GET['class_id'];
        $studentId=getParameter('id', 'int',false,2);
        $classId=getParameter('class_id', 'int',false,2);

        $this->assign('classId', $classId);

        $Model = M('biz_student_learning_path');

        $path = $Model
            ->join('biz_class on biz_class.id=biz_student_learning_path.class_id')
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_student_learning_path.*,dict_schoollist.school_name,dict_grade.grade,biz_class.name as class_name')
            ->where("biz_student_learning_path.student_id=$studentId")
            ->order("biz_student_learning_path.create_at desc")
            ->select();

        $this->assign('list', $path);

        $StudentModel = M('auth_student');
        $student = $StudentModel
            ->where("id=$studentId")
            ->find();

        $this->assign('student', $student);

        $this->assign('subnav', $student['student_name'] . '同学');

        $this->display();


    }

    //学生轨迹-折线图
    public function learningPathData()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Parent/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '学习轨迹');
        $this->assign('navicon', 'xuexiguiji');

        //$studentId = $_GET['id'];
        $studentId = getParameter('id', 'int',false,2);

        $StudentModel = M('auth_student');
        $student = $StudentModel
            ->where("id=$studentId")
            ->find();

        $this->assign('student', $student);

        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        $this->assign('courses', $courses);

        $this->assign('subnav', $student['student_name'] . '同学');

        $this->display();
    }

    //查看班级课表
    public function classTimetable()
    {
        if (!session('?parent')) {
            redirect(U('Index/index'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '班级管理');
        $this->assign('navicon', 'jiazhangduxue');

        //$classId = $_GET['classId'];
        $classId = getParameter('classId', 'int',false,2);
        $this->assign('classId', $classId);


        $ClassModel = M('biz_class');
        $class = $ClassModel
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_class.*,dict_schoollist.school_name,dict_grade.grade')
            ->where("biz_class.id=$classId")
            ->find();

        $this->assign('class', $class);

        $this->assign('subnav', $class['name'] . '的班级课表');


        $this->display();
    }

    //查看班级课表的具体内容iframe里
    public function classTimetableInner()
    {
        //$classId = $_GET['classId'];
        $classId = getParameter('classId', 'int',false,2);
        $this->assign('classId', $classId);

        $Model = M('biz_class_timetable');
        $result = $Model
            ->where("class_id=$classId")
            ->find();

        if (empty($result)) {
            $this->assign('hasTimetable', "false");
        } else {
            $this->assign('hasTimetable', "true");
        }

        $this->assign('timetable', $result);


        $this->display();
    }
    public function information()
    {
       if (!session('?parent')) redirect(U('Index/index'));
        if($_POST)
        {
          $parentId = session('parent.id');
          $check['id'] = $parentId;
          /*
          $data['parent_name'] = remove_xss($_POST['name']);
          $data['sex'] = $_POST['sex'];
          $data['email'] = remove_xss($_POST['email']);
          $data['telephone'] = $_POST['telephone'];
          */
          $data['parent_name'] = getParameter('name', 'str',false);
          $data['sex'] = getParameter('sex', 'str',false);
          $data['email'] = getParameter('email', 'str',false);
          $data['telephone'] = getParameter('telephone', 'str',false);
          $parent_stu_id = I('parent_stu_id');

          if(!empty($_POST['password'])){
                //$data['password'] = sha1($_POST['password']);
                $data['password'] = sha1(getParameter('password', 'str',false));
            }
          $data['update_at'] = time();

          if (empty($_POST['telephone']) || empty($_POST['name'])) {
              $this->error('请填写完整信息');
          }

          $auth_s_p_c = M('auth_student_parent_contact');
          $auth_s_p_c->where("parent_id=$parentId")->delete();

          $auth_s_p_c->startTrans();

          $parentModel = M('auth_parent');
          $result = $parentModel->where($check)->save($data);
          $_SESSION['parent']['parent_name'] = $data['parent_name'];
            

          $is_lock = true;

          if (!empty($parent_stu_id)) {
               foreach ($parent_stu_id as $sk=>$sv ){
                   $student_parent_map = [];
                   $student_parent_map['student_id'] = $sv;
                   $student_parent_map['parent_tel'] = session('parent.telephone');
                   $student_parent_map['parent_id'] = $parentId;
                   $student_parent_map['create_at'] = time();

                   $add_s = $auth_s_p_c->add($student_parent_map);
                   if (!$add_s) {
                       $is_lock = false;
                   }

               }

               if ($is_lock==true) {
                   $auth_s_p_c->commit();
               } else {
                   $auth_s_p_c->rollback();
               }
           }


          $this->redirect(U('Parent/me'));
        }
        else
         {
            $parentId = session('parent.id');
            $parentModel = M('auth_parent');
            $result = $parentModel->where("id=$parentId")->find();
            $this->assign('data', $result);
            $this->display();

         }
    }
    public function redbjResourceList()
           {
             if (!session('?parent')) redirect(U('Index/index'));
                    /*
                    $filter['course_id'] = $_REQUEST['course'];
                    $filter['grade_id'] = $_REQUEST['grade'];
                    $filter['school_term_id'] = $_REQUEST['textbook'];
                    $filter['type'] = $_REQUEST['type'];
                    */
                    $filter['course_id'] = getParameter('course', 'int',false,2);
                    $filter['grade_id'] = getParameter('grade', 'int',false,2);
                    $filter['school_term_id'] = getParameter('textbook', 'int',false,2);
                    $filter['type'] = getParameter('type', 'str',false);

                    $filter['keyword'] = $_REQUEST['keyword'];
                    $filter['sort_column'] = isset($_REQUEST['sort_column'])?intval($_REQUEST['sort_column']):6;
                    $searchCate = $_REQUEST['resource_cate'];
                    $keyword = $_REQUEST['keyword'];
                    $check['status'] = 2;


                    if (!empty($filter['type'])) $check['type'] = $filter['type'];
                    if (!empty($filter['school_term_id'])) $check['school_term_id'] = $filter['school_term_id'];
                    //if (empty($filter['sort_column'])) $filter['sort_column'] = 'create_at';
                    if (empty($searchCate)) $searchCate = 'all';

                    $res = $this->getSortString($filter['sort_column']);
                    $sort_string = $res[0];
                    $filter['sort_column'] = $res[1];

                    $res = $this->getGradeWhere($filter['grade_id']);
                    if('' != $res)
                    $check['grade_id'] = $res;
                    $res = $this->getCourseWhere($filter['course_id']);
                    if('' != $res)
                    $check['course_id'] = $res;

                    if($searchCate == 'bj')
                    {
                     $Model = M('biz_bj_resources');
                     $check['biz_bj_resource_collect.user_id'] = session('parent.id');
                     $check['biz_bj_resource_collect.role'] = 2;
                     if (!empty($filter['keyword'])) $check['biz_bj_resources.name'] = array('like', '%' . $filter['keyword'] . '%');
                     $count=  $Model->join('biz_bj_resource_collect on biz_bj_resource_collect.resource_id=biz_bj_resources.id')->where($check)->select();
                     $count=count($count);
                     $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));

                     $Page->parameter['keyword'] = $keyword;
                     $Page->parameter['course_id'] = $filter['course_id'];
                     $Page->parameter['grade_id'] = $filter['grade_id'];
                     $Page->parameter['textbook_id'] = $filter['school_term_id'];
                     $Page->parameter['sort_column'] = $filter['sort_column'];
                     $Page->parameter['type'] = $filter['type'];
                     $Page->parameter['resource_cate'] = $searchCate;

                     $show = $Page->show();

                     $result = $Model
                                ->join('(select id,name from biz_textbook) a  on biz_bj_resources.textbook_id=a.id','left')
                                ->join('biz_bj_resource_collect on biz_bj_resource_collect.resource_id=biz_bj_resources.id')
                                ->field('biz_bj_resources.*,a.name as textbook,"北京出版集团" publisher_name')
                                ->where($check)
                                ->order("biz_bj_resources." . $sort_string)
                                ->limit($Page->firstRow . ',' . $Page->listRows)
                                ->select();
                     for ($i = 0; $i < sizeof($result); $i++)
                        $result[$i]['category'] = 'bj';
                    }
                    else if($searchCate == 'teacher' )
                    {
                      $Model = M('biz_resource');
                      $check['biz_resource_collect.user_id'] = session('parent.id');
                      $check['biz_resource_collect.user_type'] = 2;
                      if (!empty($filter['keyword'])) $check['biz_resource.name'] = array('like', '%' . $filter['keyword'] . '%');
                      $count=  $Model ->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')->where($check)->select();
                      $count=count($count);
                      $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
                       $Page->parameter['keyword'] = $keyword;
                       $Page->parameter['course_id'] = $filter['course_id'];
                       $Page->parameter['grade_id'] = $filter['grade_id'];
                       $Page->parameter['textbook_id'] = $filter['school_term_id'];
                       $Page->parameter['sort_column'] = $filter['sort_column'];
                       $Page->parameter['type'] = $filter['type'];
                       $Page->parameter['resource_cate'] = $searchCate;
                       $show = $Page->show();

                      $result = $Model
                                ->join('(select id,name from biz_textbook) a on biz_resource.textbook_id=a.id','left')
                                ->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')
                                ->field('biz_resource.*,a.name as textbook,biz_resource.teacher_name publisher_name')
                                ->where($check)
                                ->order("biz_resource." .$sort_string)
                                ->limit($Page->firstRow . ',' . $Page->listRows)
                                ->select();                     //echo $Model->getLastSql();die;
                     for ($i = 0; $i < sizeof($result); $i++)
                       $result[$i]['category'] = 'teacher';
                    }
                    else if($searchCate == 'all')
                    {
                    $unionSql = " SELECT 'bj' as category,biz_bj_resources.file_path,'北京出版集团' publisher_name,biz_bj_resources.vid_image_path,biz_bj_resources.id,biz_bj_resources.type,biz_bj_resources.name,biz_bj_resources.create_at,biz_bj_resources.zan_count,biz_bj_resources.favorite_count,biz_bj_resources.follow_count,biz_textbook.name as textbook FROM biz_bj_resources left JOIN biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id
                    join (select resource_id from biz_bj_resource_collect where role = 2 and user_id =".session('parent.id') .") a on a.resource_id=biz_bj_resources.id
                    WHERE biz_bj_resources.status = 2 ";
                    if (!empty($check['course_id']))
                    $unionSql = $unionSql." and biz_bj_resources.course_id = ".$filter['course_id'];
                    if (!empty($check['grade_id']))
                    $unionSql = $unionSql." and biz_bj_resources.grade_id = ".$filter['grade_id'];
                    if (!empty($filter['type']))
                    $unionSql = $unionSql." and biz_bj_resources.type = '".$filter['type']."'";
                    if (!empty($filter['school_term_id']))
                    $unionSql = $unionSql ." and biz_bj_resources.school_term_id = ".$filter['school_term_id'];

                    if (!empty($filter['keyword']))
                    $unionSql = $unionSql ." and biz_bj_resources.name like '%".$filter['keyword'] . "%'";


                     $check['biz_resource_collect.user_id'] = session('parent.id');
                     $check['biz_resource_collect.user_type'] = 2;
                     if (!empty($filter['keyword'])) $check['biz_resource.name'] = array('like', '%' . $filter['keyword'] . '%');
                           if(!empty($check['course_id']))
                           {
                           $check['biz_resource.course_id'] = $check['course_id'];
                           $check = $this->array_remove($check,'course_id');
                           }
                           if(!empty($check['grade_id']))
                           {
                           $check['biz_resource.grade_id'] = $check['grade_id'];
                           $check = $this->array_remove($check,'grade_id');
                           }
                           if(!empty($check['school_term_id']))
                           {
                           $check['biz_resource.school_term_id'] = $check['school_term_id'];
                           $check = $this->array_remove($check,'school_term_id');
                           }
                     $Model = M('biz_resource');

                     $count=  $Model->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')->where($check)->field("'teacher' as category,biz_resource.file_path,'北京出版社集团' publisher_name,biz_resource.vid_image_path,".'biz_resource.id,biz_resource.type,biz_resource.name,biz_resource.create_at,biz_resource.zan_count,biz_resource.favorite_count,biz_resource.follow_count,biz_textbook.name as textbook')->union($unionSql)->select();

                     $count=count($count);
                     $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
                     $Page->parameter['keyword'] = $keyword;
                     $Page->parameter['course_id'] = $filter['course_id'];
                     $Page->parameter['grade_id'] = $filter['grade_id'];
                     $Page->parameter['textbook_id'] = $filter['school_term_id'];
                     $Page->parameter['sort_column'] = $filter['sort_column'];
                     $Page->parameter['type'] = $filter['type'];
                     $Page->parameter['resource_cate'] = $searchCate;
                      $show = $Page->show();


                     $result = $Model
                              ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
                              ->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')
                              ->field("'teacher' as category,biz_resource.file_path,biz_resource.teacher_name publisher_name,biz_resource.vid_image_path," . 'biz_resource.id,biz_resource.type,biz_resource.name,biz_resource.create_at,biz_resource.zan_count,biz_resource.favorite_count,biz_resource.follow_count,biz_textbook.name as textbook')
                              ->where($check)
                              ->union($unionSql.' ORDER BY '.$sort_string." LIMIT ".$Page->firstRow . ',' . $Page->listRows)
                              ->select();

                    }
//echo $Model->getLastSql();die;

                            $this->assign('list', $result);
                            $this->assign('page', $show);

                            $Model = M('dict_course');
                            $courses = $Model->order('sort_order asc')->select();
                            $this->assign('courses', $courses);

                            $Model = M('dict_grade');
                            $grades = $Model->select();
                            $this->assign('grades', $grades);

                            $TextbookModel = M('biz_textbook');
                            $c1['course_id'] = $filter['course_id'];
                            $c1['grade_id'] = $filter['grade_id'];
                            $textbooks = $TextbookModel->where($c1)->select();
                            $this->assign('textbooks', $textbooks);

                            $this->assign('course_id', $filter['course_id']);
                            $this->assign('grade_id', $filter['grade_id']);
                            $this->assign('textbook_id', $filter['school_term_id']);
                            $this->assign('type', $filter['type']);
                            $this->assign('keyword', $filter['keyword']);
                            $this->assign('sort_column', $filter['sort_column']);
                            $this->assign('resource_cat_val', $searchCate);
                            $this->display_nocache();
           }

    public function myResourceDetails($id)
    {
    $id=intval($id);
    $this->resourceDetails($id,'myfavor',1);
    }

    public function mybjResourceDetails($cate,$id)
    {
     $id=intval($id);
     if($cate == 'bj')
      $this->bjResourceDetails($id,1);
    }

    /**
     * 更改家长个人资料
     */

    public function updata()
    {
        $check['id'] = I('request.id','','remove_xss');
        if(empty($check['id']))
        {
            $this->showjson(-100,'id为空',array());
        }

        $data['parent_name'] = I('request.name','','remove_xss');
        $data['sex'] = I('request.sex','','remove_xss');
        $data['email'] = I('request.email','','remove_xss');
        $data['telephone'] = I('request.telephone','','remove_xss');
        $data['grade_id'] = I('request.grade_id','','remove_xss');
        if(!$_REQUEST['telephone'] || !$_REQUEST['sex'] || !$_REQUEST['name']){
            $this->showjson(-105,"参数有误",array());
        }

        foreach($data as $key=>$val)
        {
            switch($key)
            {
                case 'telephone':
                    if(empty($data[$key]))
                    {
                        $this->showjson(-101,"{$key}为空",array());
                    }
                    break;
                case 'sex':
                    if(empty($data[$key]))
                    {
                        $this->showjson(-102,"{$key}为空",array());
                    }

                    if(!in_array($data[$key],array('男','女')))
                    {
                        $this->showjson(-103,"{$key}为空",array());
                    }
                    break;
                case 'parent_name':
                    if(empty($data[$key]))
                    {
                        $this->showjson(-104,"{$key}为空",array());
                    }
                    break;
                default:
                    break;
            }
        }

        $data['update_at'] = time();


        $parentModel = M('auth_parent');
        $parentModel->startTrans();

        if(!($result = $parentModel->where($check)->save($data))){
            $parentModel->rollback();
            $this->showjson(-101,"修改失败",array());
        }

        $student_model=M('auth_student');
        $student_result=$student_model->where('parent_id='.$check['id'])->select();
        $student_data['parent_tel']=$data['telephone'];
        foreach($student_result as $val){
            if($student_model->where("id=".$val['id'])->save($student_data)===false){
                $parentModel->rollback();
                $this->showjson(-101,"修改失败",array());
            }
        }
        $parentModel->commit();
        $this->showjson(0,"更改成功",array());
    }

    function getSortString($sort){
             $filter['sort_column'] = $sort;
                switch ($sort) {
                    case 0:
                        $sort_string= "zan_count desc";
                        break;
                    case 1:
                        $sort_string= "zan_count asc";
                        break;
                    case 2:
                        $sort_string= "favorite_count desc";
                        break;
                    case 3:
                        $sort_string= "favorite_count asc";
                        break;
                    case 4:
                        $sort_string= "follow_count desc";
                        break;
                    case 5:
                        $sort_string= "follow_count asc";
                        break;
                    case 6:
                        $sort_string= "create_at desc";
                        break;
                    case 7:
                        $sort_string= "create_at asc";
                        break;
                    default:
                        $sort_string= "create_at desc";
                        $filter['sort_column']=6;
                        break;
                }
      return array($sort_string,$filter['sort_column']);
    }
    function getCourseWhere($courseId)
    {
            if($courseId==-1){
               return $courseId;
            }
            else{
               if(!empty($courseId))
               return array('in', '-1,'.$courseId );
               else
               return '';
            }
    }
    function getGradeWhere($gradeId)
    {
     if($gradeId==14){
         return 14;
     }elseif($gradeId==15){
         return 15;
     }elseif($gradeId==16){
         return 16;
     }else{
      if($gradeId>0 && $gradeId<7){
           return array('in','(14,'. $gradeId .')');
       }elseif($gradeId>6 && $gradeId<10){
           return array('in','(15,'. $gradeId .')');
       }elseif($gradeId>9 && $gradeId<13){
           return array('in','(16,'. $gradeId .')');
       }
     }
     return $gradeId < 0?$gradeId : '';
    }
 public function getAESString()
 {
  if($_REQUEST)
   {
     $url = 'http://api.17find.com/apph5/user/gettoken?appkey=4ed1ffaad9a78a1a&appsecret=fe0144ca9ab1219dad3c94ad0e1f491d&phone='.$_REQUEST['phone'];
     $token1= file_get_contents($url);
     $token1=json_decode($token1);
     $token1=$token1->data->token;
     $url = 'http://i.17find.com/apph5/user/login?sid='.$token1;
     $token2= file_get_contents($url);
     $token2=json_decode($token2);
     $token2=$token2->data->token;
     $token2 = str_replace('+','%2B',$token2);
     $token2 = str_replace(' ','%20',$token2);
     $token2 = str_replace('/','%2F',$token2);
     $token2 = str_replace('.','%2E',$token2);
     echo 'http://i.17find.com/index.html#/map/'.$token2;
   }

 }
  public function myMessageDetails()
  {
      $messageId = getParameter('id', 'int',false);
      A('Home/Message')->messageDetails($messageId);

  }
}
