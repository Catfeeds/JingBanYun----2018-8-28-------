<?php
namespace ApiInterface\Controller\Version1_1;

use Think\Controller;
use Common\Common\REDIS;
use Common\Common\SMS; 

class StudentController extends PublicController
{
    public $pageSize = 20;
    public $Model;

    public function __construct()
    {
        parent::__construct();
        $this->Model = D('Auth_student');
        $this->assign('oss_path', C('oss_path'));
    }
    /**
     * @描述：登录
     * @参数：student_name[string] Y 学生姓名
     * @参数：parent_tel[int] Y 手机号码
     * @参数：pwd[string] Y 密码
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */
    public function login()
    {
        $telephone = getParameter('parent_tel', 'int');
        $password  = sha1(getParameter('pwd', 'str'));
        $student_name = getParameter('student_name', 'str');
        $mac = getParameter('macToken', 'str',false);
        $Device = getParameter('Device', 'str',false);
        $channel_id = getParameter('channel_id', 'str',false);

        if (empty($student_name) || empty($password))
            $this->ajaxReturn(array('status' => 404, 'message' => '账号不存在'));
        $userInfo = $this->Model->getStudentInfoByTelAndName($telephone,$student_name);

        if(empty($userInfo))
            $this->ajaxReturn(array('status' => 404, 'message' => '账号不存在'));

        if($userInfo['flag'] == 0)
        {
            $this->ajaxReturn(array('status' => 404, 'message' => '账号被停用'));
        }

        if ($userInfo['password'] == $password) {
            $result = array(
                'id' => $userInfo['id'],
                'student_name' => $userInfo['student_name'],
                'name' => $userInfo['student_name'],
                'user_name' => $userInfo['user_name'],
                'telephone' => $userInfo['parent_tel'],
                'access_token' => $userInfo['access_token'],
                'avatar' => $userInfo['avatar'],
                'need_perfect' => empty($userInfo['school_id'])? 1:0
            );
            if (preg_match('/Resources/', $result['avatar'])){
                $result['avatar'] = C('oss_path').$result['avatar'];
            } else {
                $result['avatar'] = "http://".WEB_URL."/Uploads/StudentAvatars/".$result['avatar'];
            }
            $client_ip=get_client_ip();
            $current_login_address=getIPLoc_sina($client_ip);
            if($current_login_address!=$result['login_address'] && !empty($result['login_address'])) {
                $this->Model->saveLoginAddress($userInfo['id'], $current_login_address);

                $ip_arr = explode('.', $client_ip);
                $ip_arr[3] = '*';
                $ip_string = implode('.', $ip_arr);
                $login_address_str = $current_login_address . '(' . $ip_string . ')';

                $parameter_arr = array(
                    'msg' => array(date('Y-m-d H:i:s'), $login_address_str, 'app'),
                    'url' => array(
                        'type' => 0,
                        'data' => array()
                    )
                );
                $controller_obj=new \Home\Controller\MessageController();
                $controller_obj->addPushUserMessage('EXCEPTION_LOGIN', 3, $userInfo['id'], $parameter_arr);
                
                $parentInfo = D('Auth_student')->getStudentParentInfo($userInfo['id']);
                $controller_obj->addPushUserMessage('CHILD_EXCEPTION_LOGIN', 4, $parentInfo['id'], $parameter_arr);
                
                //A('Home/Message')->addPushUserMessage('EXCEPTION_LOGIN', 3, $userInfo['id'], $parameter_arr);
                //$parentInfo = D('Auth_student')->getStudentParentInfo($userInfo['id']);
                //A('Home/Message')->addPushUserMessage('CHILD_EXCEPTION_LOGIN', 4, $parentInfo['id'], $parameter_arr);
            }
            if ($Device=="iPad") {
                $login_mac_address['ipad_address'] = $mac;
                $login_mac_address['pad_prev_channel_id_info'] = $userInfo['pad_channel_id_info'];
            } else {
                $login_mac_address['mac_address'] = $mac;
                $login_mac_address['prev_channel_id_info'] = $userInfo['channel_id_info'];
            }

            $login_channel_address['channel_id_info'] = $channel_id;
            $this->Model->where("id=".$userInfo['id'])->save( $login_channel_address );

            $this->Model->where("id=".$userInfo['id'])->save( $login_mac_address );


            $id = $userInfo['id'];
            $role_id = 3;
            if ($id == 0 || $role_id==5 ) { //游客模式要获取的权限
                $map['auth_id'] = 1;
                $map['users_type_id'] = 5;
                $row = M('account_auth_to_node')->where( $map )->field('node_id')->find();
                $node_list = explode(',', $row['node_id']);
                foreach ($node_list as $key => $value) {
                    $node_list[$key] = intval($value);
                }
                $data['auth'] = $node_list;
            } else {
                $auth_type_use = D('Account_auths'); //普通权限加
                $auth_list = $auth_type_use->getIphoneAuthAndVipauth($id,$role_id);
                foreach ($auth_list as $key => $value) {

                    if ($value == 13) {
                        array_push($auth_list, 17);
                    }
                    if ($value == 14) {
                        array_push($auth_list, 20);
                    }
                    $auth_list[$key] = intval($value);
                }
                $data['auth'] = $auth_list;
            }

            $auth_type_use = D('Account_auths'); //普通权限加
            $isvip = $auth_type_use->isVipInfo($id,$role_id);

            if ($isvip['is_vip'] == 1) {
                $data['is_vip'] = 1;
            } else {
                $data['is_vip'] = 2;
            }
            $result['auth_list'] = $data;

            $data = [];
            $data['machine_type'] = getMachineType();
            $orgChannelIdName = 'channel_id_info';
            $prevChannelName = 'prev_channel_id_info';
            $Device = getallheaders()['Device'];
            if($Device == 'iPad') {
                $orgChannelIdName = 'pad_' . $orgChannelIdName;
                $prevChannelName = 'pad_'.$prevChannelName;
            }
            $map['id'] = $id;
            $data[$orgChannelIdName] = $channel_id;
            $stuinfo = M('auth_student')->where( $map )->find();
            if($stuinfo[$prevChannelName] !== $channel_id && !empty($stuinfo[$prevChannelName])){
                $Message = new \Home\Controller\MessageController();
                $parameters = [];
                $Message->addPushUserMessage('LOGIN_PASS',ROLE_STUDENT,$id,$parameters,USE_PREV_CHANNEL_ID,USE_USER_DEFINE_MESSAGE);
            }
            if ( empty($stuinfo[$orgChannelIdName]) ) { //判断渠道id是否为空
                $tid = M('auth_student')->where( $map )->save( $data );
            } else {
                if ( $stuinfo[$orgChannelIdName] != $channel_id ) {
                    $tid = M('auth_student')->where( $map )->save( $data );
                }
            }

            $this->ajaxReturn(array('status' => 200,'result'=>$result));
        } else
            $this->ajaxReturn(array('status' => 404, 'message' => '登录信息有误,请核对登录信息'));
    }

    /**
     * @描述：注册
     * @参数：name[string] Y 学生姓名
     * @参数：sex[string] Y 性别
     * @参数：pwd[string] Y 密码
     * @参数：birthday[string] Y 出生年月日字符串
     * @参数：email[string] N EMAIL
     * @参数：parent_tel[int] Y 家长手机号
     * @参数：school_id[int] Y 学校ID
     * @参数：parent_name[string] N 家长姓名
     * @参数：studentId[int] N 学号
     * @参数：id_card[int] N 身份证号
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */

    public function register()
    {
         
        $name = getParameter('name', 'str');
        $sex = getParameter('sex', 'str');
        $password  = sha1(getParameter('pwd', 'str'));
        $birthday = getParameter('birthday', 'str');
        $email = getParameter('email', 'str',false);
        $parent_tel = getParameter('parent_tel', 'str');
        $schoolId = getParameter('school_id', 'int');
        $parent_name = getParameter('parent_name', 'str',false);
        $stuId = getParameter('studentId', 'str',false);
        $id_card = getParameter('id_card', 'int',false);
//        if(!empty($stuId))
//        if (!preg_match("/^\d+$/", $stuId))
//            $this->ajaxReturn(array('status' => 500, 'message' => '学生编号不正确'));


        if (empty($parent_tel) || empty($password) || empty($name))
            $this->ajaxReturn(array('status' => 500, 'message' => '手机号、密码和姓名不能为空'));

        if (strlen($parent_tel) <> 11)
            $this->ajaxReturn(array('status' => 500, 'message' => '手机号格式不正确'));

        $parentInfo=D('Auth_parent')->getParentInfoByTelephone($parent_tel);
        if (!empty($parentInfo ))
        {
            $parent_id = $parentInfo['id'];
            $parent_name = $parentInfo['parent_name'];
        }
        else
            $parent_id = 0;
        $currentTime = time();
        $data = array(
            'student_name' =>$name,
            'sex' => $sex,
            'school_id' => $schoolId,
            'birth_date' => strtotime($birthday),
            'email' => $email,
            'student_id' => $stuId,
            'id_card' =>  $id_card,
            'parent_id' => $parent_id,
            'parent_tel' => $parent_tel,
            'parent_name' => $parent_name,
            'password' => $password,
            'create_at' => $currentTime,
            'update_at' => $currentTime
        );

//        if(!empty($stuId)) {
//            $student_info = $this->Model->getStudentBySchoolIdStuId($schoolId,$stuId);
//            if (!empty($student_info)) {
//                $this->ajaxReturn(array('status' => 500, 'message' => '该学校中该学号已存在'));
//            }
//        }
        $student = $this->Model->getStudentInfoByTelAndName($parent_tel,$name);
        if(!empty($student))
            $this->ajaxReturn(array('status' => 500, 'message' => '该用户已经注册'));

        $studentId = $this->Model->addStudentForRegister($data);
        if(empty($studentId))
        $this->ajaxReturn(array('status' => 500, 'message' => '注册失败'));

        if (0 != $parent_id){
            D('Auth_student')->updateStudentParentInfo($parent_id, $studentId);
        }
        $currentTime =   time();
        //赠送30天vip
	    $vip_config=C('VIP_CONFIG.APP_REGISTER_GIVE_VIP_STATUS');
	
         if($vip_config && $vip_config<=3){
            $result=give_new_vip_operation(3, $vip_config,$studentId,$data['school_id'],$parentInfo['id']);
            if($result['status']=='success'){
                $smsapi = new SMS();
                $smsapi->noticeParentAddStudent($parent_tel,$name);
                $Message = new \Home\Controller\MessageController();
                $parameters = array( 'msg' => array() , 'url' => array( 'type' => 0));
                $Message->addPushUserMessage('REG_SUCCESS',3,$studentId,$parameters);
                //家长推送
                $parameters = array( 'msg' => array($name) , 'url' => array( 'type' => 0));
                $Message->addPushUserMessage('CHILD_REG_SUCCESS',ROLE_PARENT,$parent_id,$parameters);
                $this->ajaxReturn(array('status' => 200, 'message' => '注册成功','result' => array('name' => $name, 'token' => '', 'id' => $studentId)));
            }else{
                $this->ajaxReturn(array('status' => 500, 'message' => '注册权限失败'));
            }
        } 
        $this->ajaxReturn(array('status' => 200, 'message' => '注册成功','result' => array('name' => $name, 'token' => '', 'id' => $studentId)));  
    }
    
    
    /**
     * @描述：注册          ios最新的注册
     * @参数：name[string] Y 学生姓名
     * @参数：sex[string] Y 性别
     * @参数：pwd[string] Y 密码
     * @参数：birthday[string] Y 出生年月日字符串
     * @参数：email[string] N EMAIL
     * @参数：parent_tel[int] Y 家长手机号
     * @参数：school_id[int] Y 学校ID
     * @参数：parent_name[string] N 家长姓名
     * @参数：studentId[int] N 学号
     * @参数：id_card[int] N 身份证号
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */

    public function student_register()
    {
        $name = getParameter('name', 'str');
        $sex = getParameter('sex', 'str');
        $password  = sha1(getParameter('pwd', 'str'));
        $birthday = getParameter('birthday', 'str');
        $verify_code = getParameter('verify_code', 'str');
        $email = getParameter('email', 'str',false);
        $parent_tel = getParameter('parent_tel', 'str');
        $schoolId = getParameter('school_id', 'int');
        $parent_name = getParameter('parent_name', 'str',false);
        $stuId = getParameter('studentId', 'str',false);
        $id_card = getParameter('id_card', 'int',false);
//        if(!empty($stuId))
//        if (!preg_match("/^\d+$/", $stuId))
//            $this->ajaxReturn(array('status' => 500, 'message' => '学生编号不正确'));


        if (empty($parent_tel) || empty($password) || empty($name))
            $this->ajaxReturn(array('status' => 500, 'message' => '手机号、密码和姓名不能为空'));

        if (strlen($parent_tel) <> 11)
            $this->ajaxReturn(array('status' => 500, 'message' => '手机号格式不正确'));

        $parentInfo=D('Auth_parent')->getParentInfoByTelephone($parent_tel);
        if (!empty($parentInfo ))
        {
            $parent_id = $parentInfo['id'];
            $parent_name = $parentInfo['parent_name'];
        }
        else
            $parent_id = 0;
        $currentTime = time();
        $data = array(
            'student_name' =>$name,
            'sex' => $sex,
            'school_id' => $schoolId,
            'birth_date' => strtotime($birthday),
            'email' => $email,
            'student_id' => $stuId,
            'id_card' =>  $id_card,
            'parent_id' => $parent_id,
            'parent_tel' => $parent_tel,
            'parent_name' => $parent_name,
            'password' => $password,
            'create_at' => $currentTime,
            'update_at' => $currentTime
        );

//        if(!empty($stuId)) {
//            $student_info = $this->Model->getStudentBySchoolIdStuId($schoolId,$stuId);
//            if (!empty($student_info)) {
//                $this->ajaxReturn(array('status' => 500, 'message' => '该学校中该学号已存在'));
//            }
//        }
        
        $student = $this->Model->getStudentInfoByTelAndName($parent_tel,$name);
        if(!empty($student))
            $this->ajaxReturn(array('status' => 500, 'message' => '该用户已经注册'));

        
        $redis_obj=new REDIS();
        $redis=$redis_obj->init_redis(); 

        $redis_key='sms_'.$parent_tel;   
        $code=$redis->get($redis_key);   
        if($verify_code!=$code || !$code){
            $isvalid = false;
            $redis->close();
            $this->ajaxReturn(array('status' => 500, 'message' => '验证码错误'));
            return;
        }
        
        $studentId = $this->Model->addStudentForRegister($data);
        if(empty($studentId))
        $this->ajaxReturn(array('status' => 500, 'message' => '注册失败'));

        if(0 != $parent_id)
         D('Auth_student')->updateStudentParentInfo($parent_id, $studentId);
        $currentTime =   time();
        //赠送30天vip
	$vip_config=C('VIP_CONFIG.APP_REGISTER_GIVE_VIP_STATUS');
	
         if($vip_config && $vip_config<=3){
            $result=give_new_vip_operation(3, $vip_config,$studentId,$data['school_id'],$parentInfo['id']);
            if($result['status']=='success'){
                $redis->delete($redis_key); 
                $redis->close();
                
                $smsapi = new SMS();
                $smsapi->noticeParentAddStudent($parent_tel,$name);
                $Message = new \Home\Controller\MessageController();
                $parameters = array( 'msg' => array() , 'url' => array( 'type' => 0));
                $Message->addPushUserMessage('REG_SUCCESS',ROLE_STUDENT,$studentId,$parameters);
                //家长推送
                $parameters = array( 'msg' => array($name) , 'url' => array( 'type' => 0));
                $Message->addPushUserMessage('CHILD_REG_SUCCESS',ROLE_PARENT,$parent_id,$parameters);
                $this->ajaxReturn(array('status' => 200, 'message' => '注册成功','result' => array('name' => $name, 'token' => '', 'id' => $studentId)));
            }else{
                $this->ajaxReturn(array('status' => 500, 'message' => '注册权限失败'));
            }
        } 
        $this->ajaxReturn(array('status' => 200, 'message' => '注册成功','result' => array('name' => $name, 'token' => '', 'id' => $studentId)));  
    }
    

    /**
     * @描述：设置新密码
     * @参数：telephone[int] Y 手机号码
     * @参数：name[string] Y 学生姓名
     * @参数：verify_code[int] Y 验证码
     * @参数：newpassword[string] Y 密码
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */
    public function setNewPassword()
    {
        $name = getParameter('name', 'str');
        $telephone = getParameter('telephone', 'str');
        $code = getParameter('verify_code', 'int');
        $password = sha1(getParameter('newpassword','str'));

        if (empty($telephone) || empty($password))
            $this->ajaxReturn(array('status' => 406, 'message' => '手机号、密码不能为空'));
        if (strlen($telephone) <> 11)
            $this->ajaxReturn(array('status' => 403, 'message' => '手机号格式不正确'));

        //验证是否注册
        $userInfo = $this->Model->getStudentInfoByTelAndName($telephone,$name);
        if (empty($userInfo))
            $this->ajaxReturn(array('status' => 406, 'message' => '该手机号下未注册该位同学'));

        /*if(!D('Misc_register_phone_validcode')->verifyCode($telephone,$code))
            $this->ajaxReturn(array('status' => 500, 'message' => '验证码错误'));
        */
        $redis_obj=new REDIS();
        $redis=$redis_obj->init_redis();

        $redis_key='sms_'.$telephone;   
        $sms_code=$redis->get($redis_key);
        if($sms_code!=$code || !$code){
            $redis->close();
            $this->ajaxReturn(array('status' => 500, 'message' => '验证码错误'));
        }
        
        $token = $this->guid();
        $data = array(
            'password' => $password,
            'access_token' => $token
        );
        // 更改用户到数据库
        $redis->delete($redis_key);
        $redis->close();
        
        $this->Model->updateInfoById($data,$userInfo['id']);
        $parameters = array( 'msg' => array(date('Y-m-d H:i:s',time())) ,
            'url' => array( 'type' => 0)
        );
        $controller_obj=new \Home\Controller\MessageController();
        $controller_obj->addPushUserMessage('PASSWORD_MODIFY',3,$userInfo['id'],$parameters);
        $this->ajaxReturn(array('status' => 200, 'message' => '成功设定新密码','result' =>array('telephone' => $telephone, 'token' => $token)));
    }
    public function guid()
    {
        if (function_exists("com_create_guid")) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000); //optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = substr($charid, 0, 8)
                . substr($charid, 8, 4)
                . substr($charid, 12, 4)
                . substr($charid, 16, 4)
                . substr($charid, 20, 12);
            return $uuid;
        }
    }
}
