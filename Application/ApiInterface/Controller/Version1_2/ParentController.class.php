<?php
namespace ApiInterface\Controller\Version1_2;

use Common\Common\REDIS;
use Common\Common\SMS; 

class ParentController extends PublicController
{

    public $pageSize = 20;
    public $Model;

    public function __construct()
    {
        parent::__construct();
        $this->Model = D('Auth_parent');
        $this->assign('oss_path', C('oss_path'));
    }
    private function __clearCurrentChannel($channel_id){
        M()->execute("UPDATE auth_teacher set pad_channel_id_info = '' WHERE pad_channel_id_info = '$channel_id'");
        M()->execute("UPDATE auth_teacher set channel_id_info = '' WHERE channel_id_info = '$channel_id'");
        M()->execute("UPDATE auth_student set pad_channel_id_info = '' WHERE pad_channel_id_info = '$channel_id'");
        M()->execute("UPDATE auth_student set channel_id_info = '' WHERE channel_id_info = '$channel_id'");
        M()->execute("UPDATE auth_parent set pad_channel_id_info = '' WHERE pad_channel_id_info = '$channel_id'");
        M()->execute("UPDATE auth_parent set channel_id_info = '' WHERE channel_id_info = '$channel_id'");
    }
    /**
     * @描述：登录
     * @参数：telephone[int] Y 手机号码
     * @参数：pwd[string] Y 密码
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */
    public function login()
    {
        $telephone = getParameter('telephone', 'int');
        $password  = sha1(getParameter('pwd', 'str'));
        $mac = getParameter('macToken', 'str',false);
        $Device = getParameter('Device', 'str',false);
        $channel_id = getParameter('channel_id', 'str',false);

        if (empty($telephone) || empty($password))
            $this->ajaxReturn(array('status' => 404, 'message' => '账号不存在'));
        $userInfo = $this->Model->getParentInfoByTelephone($telephone);

        if(empty($userInfo))
            $this->ajaxReturn(array('status' => 404, 'message' => '账号不存在'));

        if ($userInfo && $userInfo['password'] == $password) {

            //手机推送
            if($userInfo['flag'] == 0 || $userInfo['flag'] == -1)
                $this->ajaxReturn(array('status' => 404, 'message' => '账号已被停用'));

            $parent_id=$userInfo['id'];
            $client_ip=get_client_ip();
            $current_login_address=getIPLoc_sina($client_ip);
            if($current_login_address!=$userInfo['login_address'] && !empty($userInfo['login_address'])){ 
                $this->Model->where('id='.$userInfo['id'])->save(array('login_address'=>$current_login_address));

                $ip_arr=explode('.',$client_ip);
                $ip_arr[3]='*';
                $ip_string=implode('.',$ip_arr);
                $login_address_str=$current_login_address.'('.$ip_string.')';
                
                $parameter_arr=array(
                    'msg'=>array(date('Y-m-d H:i:s'),$login_address_str,'app'),
                    'url'=>array(
                        'type'=>1,
                        'data'=>array()
                    )
                );
                $controller_obj=new \Home\Controller\MessageController();
                $controller_obj->addPushUserMessage('EXCEPTION_LOGIN',4,$parent_id,$parameter_arr);
            }
            
            $result = array(
                'id' => $userInfo['id'],
                'parent_name' => $userInfo['parent_name'],
                'name' => $userInfo['parent_name'],
                'telephone' => $userInfo['telephone'],
                'access_token' => $userInfo['access_token'],
                'avatar' => $userInfo['avatar'],
            );
            (new CommonController())->getAvatarInfo(ROLE_PARENT,$result);
            $this->__clearCurrentChannel($channel_id);
            if ($Device=="iPad") {
                $login_mac_address['ipad_address'] = $mac;
                $login_mac_address['pad_prev_channel_id_info'] = $userInfo['pad_channel_id_info'];
                $login_mac_address['pad_channel_id_info'] = $channel_id;
            } else {
                $login_mac_address['mac_address'] = $mac;
                $login_mac_address['prev_channel_id_info'] = $userInfo['channel_id_info'];
                $login_mac_address['channel_id_info'] = $channel_id;
            }

            $this->Model->where("id=".$userInfo['id'])->save( $login_mac_address );

            $id = $userInfo['id'];
            $role_id = 4;
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
            if($Device == 'iPad') {
                $orgChannelIdName = 'pad_' . $orgChannelIdName;
                $prevChannelName = 'pad_'.$prevChannelName;
            }

            $map['id'] = $id;
            $data[$orgChannelIdName] = $channel_id;
            $pinfo = M('auth_parent')->where( $map )->find();
            if($pinfo[$prevChannelName] !== $channel_id && !empty($pinfo[$prevChannelName])){
                $Message = new \Home\Controller\MessageController();
                $parameters = [];
                $Message->addPushUserMessage('LOGIN_PASS',ROLE_PARENT,$id,$parameters,USE_PREV_CHANNEL_ID,USE_USER_DEFINE_MESSAGE);
            }

            if ( empty($pinfo[$orgChannelIdName]) ) { //判断渠道id是否为空
                $tid = M('auth_parent')->where( $map )->save( $data );
            } else {
                if ( $pinfo[$orgChannelIdName] != $channel_id ) {
                    $tid = M('auth_parent')->where( $map )->save( $data );
                }
            }

            $this->ajaxReturn(array('status' => 200,'result'=>$result));
        } else
            $this->ajaxReturn(array('status' => 404, 'message' => '登录信息有误,请核对登录信息'));
    }

    /**
     * @描述：注册
     * @参数：telephone[int] Y 手机号码
     * @参数：verify_code[int] Y 验证码
     * @参数：name[string] Y 姓名
     * @参数：email[string] Y EMAIL
     * @参数：pwd[string] Y 密码
     * @参数：sex[string] Y 性别
     * @参数：confirm_pwd[string] Y 确认密码
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */

    public function register()
    {
        $telephone = getParameter('telephone', 'str');
        $password  = sha1(getParameter('pwd', 'str'));
        $confirmPassword  = sha1(getParameter('confirm_pwd', 'str'));
        $name = getParameter('name', 'str');
        $email = getParameter('email', 'str',false);
        $sex = getParameter('sex', 'str');
        $grade_id = getParameter('gradeId', 'int',false);

        if (empty($telephone) || empty($password) || empty($name))
            $this->ajaxReturn(array('status' => 406, 'message' => '手机号、密码和姓名不能为空'));

        if ($confirmPassword <> $password)
            $this->ajaxReturn(array('status' => 406, 'message' => '密码与确认密码不一致'));

        if (strlen($telephone) <> 11)
            $this->ajaxReturn(array('status' => 403, 'message' => '手机号格式不正确'));

        if ($this->Model->getParentInfoByTelephone($telephone))
            $this->ajaxReturn(array('status' => 406, 'message' => '该用户已经注册'));

        /*if(!D('Misc_register_phone_validcode')->verifyCode($telephone,$verify_code))
            $this->ajaxReturn(array('status' => 500, 'message' => '验证码错误'));
        */

        $token = $this->guid();
        $data = array(
            'telephone' =>$telephone,
            'parent_name' =>$name,
            'email' =>$email,
            'password' => $password,
            'sex' => $sex,
            'grade_id' => $grade_id
        );
        $parentId = $this->Model->addParent($data);
        if(empty($parentId))
            $this->ajaxReturn(array('status' => 500, 'message' => '注册失败'));
        $currentTime = time();
        
        
        //让家长和学生进行绑定
        $student_model=M('auth_student'); 
        $student_where['parent_tel']=$telephone;
        $student_data['parent_id']=$parentId;
        $student_info=$student_model->where($student_where)->save($student_data);
        $studentsArray =  $student_model->where($student_where)->field('id')->select();
        for($i=0;$i<sizeof($studentsArray);$i++)
        {
            D('Auth_student')->updateStudentParentInfo($parentId, $studentsArray[$i]['id']);
        }
        //赠送30天vip
        $vip_config=C('VIP_CONFIG.APP_REGISTER_GIVE_VIP_STATUS');

        if($vip_config && $vip_config<=3){ 
             
            $result=give_new_vip_operation(4,$vip_config, $parentId,0,0,$telephone); 
                     
            if($result['status']=='success'){
                $smsapi = new SMS();
                $smsapi->newUserNotice($telephone);
                $Message = new \Home\Controller\MessageController();
                $parameters = array( 'msg' => array() , 'url' => array( 'type' => 0));
                $Message->addPushUserMessage('REG_SUCCESS',4,$parentId,$parameters);
                $this->ajaxReturn(array('status' => 200, 'message' => '注册成功', 'result' => array('name' => $name, 'token' => $token, 'id' => $parentId)));
            }else{
                $this->ajaxReturn(array('status' => 500, 'message' => '注册权限失败'));
            }
        }
       $this->ajaxReturn(array('status' => 200, 'message' => '注册成功', 'result' => array('name' => $name, 'token' => $token, 'id' => $parentId)));
        /*
        //赠送30天vip
        if(D('Account_auths')->inserNode($parentId,4,3,$currentTime,$currentTime+3600*24*30*3,1)){
            $redis->delete($redis_key);
            $redis->close();
            $this->ajaxReturn(array('status' => 200, 'message' => '注册成功','result' => array('name' => $name, 'token' => $token, 'id' => $parentId)));
        }else{
            $redis->close();
            $this->ajaxReturn(array('status' => 500, 'message' => '注册权限失败'));
        }*/
    }

    /**
     * @描述：设置新密码
     * @参数：telephone[int] Y 手机号码
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
        $telephone = getParameter('telephone', 'str');
        $code = getParameter('verify_code', 'int');
        $password = sha1(getParameter('newpassword','str'));

        if (empty($telephone) || empty($password))
            $this->ajaxReturn(array('status' => 406, 'message' => '手机号、密码不能为空'));
        if (strlen($telephone) <> 11)
            $this->ajaxReturn(array('status' => 403, 'message' => '手机号格式不正确'));

        if (!($userInfo = $this->Model->getParentInfoByTelephone($telephone)))
            $this->ajaxReturn(array('status' => 406, 'message' => '该用户未注册'));
        /*
        if(!D('Misc_register_phone_validcode')->verifyCode($telephone,$code))
            $this->ajaxReturn(array('status' => 500, 'message' => '验证码错误'));
        */
        
        $redis_obj=new REDIS();
        $redis=$redis_obj->init_redis(); 

        $redis_key='sms_'.$telephone;   
        $sms_code=$redis->get($redis_key);   
        if($sms_code!=$code || !$code){
            $isvalid = false;
            $redis->close();
            $this->ajaxReturn(array('status' => 500, 'message' => '验证码错误'));
            return;
        }
        
        
        $token = $this->guid();
        $data = array(
            'password' => $password,
            'access_token' => $token
        );
        $this->Model->updateParentInfo($data,$telephone);
        //推送消息
        $result=$this->Model->getParentInfo($telephone);
        $parent_id=$result['id'];
        $parameter_arr=array(
            'msg'=>array($telephone,date('Y-m-d H:i:s')),
            'url'=>array(
                'type'=>0,
                'data'=>array()
            )
        );

        $controller_obj=new \Home\Controller\MessageController(); 
        $controller_obj->addPushUserMessage('PARENT_PASSWORD_MODIFY',4,$parent_id,$parameter_arr);
        
        $redis->delete($redis_key);
        $redis->close();
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

    /**
     * @描述：修改个人信息
     * @参数：name[string] Y 姓名
     * @参数：email[string] N EMAIL
     * @参数：sex[string] Y 性别
     * @参数：gradeId[int] Y 年级ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */
    public function modifyInfo()
    {
        $id = getParameter('userId', 'int');
        $name = getParameter('name', 'str');
        $sex = getParameter('sex', 'str');
        $email = getParameter('email', 'str',false);
        $grade_id = getParameter('gradeId', 'int');

        if (empty($name))
            $this->ajaxReturn(array('status' => 406, 'message' => '姓名不能为空'));
        if ($sex != '男' && $sex != '女' )
            $this->ajaxReturn(array('status' => 406, 'message' => '性别错误'));
        $gradeInfo = D('Dict_grade')->getGradeInfo($grade_id);
        if (empty($gradeInfo))
            $this->ajaxReturn(array('status' => 406, 'message' => '年级不存在'));
        $data = array(
            'parent_name' =>$name,
            'email' =>$email,
            'sex' => $sex,
            'grade_id' => $grade_id
        );
        $result = $this->Model->updateInfoById($data,$id);
        if($result !== false) {
            $this->ajaxReturn(array('status' => 200, 'message' => '修改成功', 'result' => array()));
        }
        else
            $this->ajaxReturn(array('status' => 500, 'message' => '修改失败', 'result' => array()));
    }
     
}