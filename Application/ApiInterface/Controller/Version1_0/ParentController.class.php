<?php
namespace ApiInterface\Controller\Version1_0;

use Think\Controller;

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

        if($userInfo->lock != 0)
            $this->ajaxReturn(array('status' => 404, 'message' => '账号已被锁定'));

        if ($userInfo && $userInfo['password'] == $password) {
            $result = array(
                'id' => $userInfo['id'],
                'parent_name' => $userInfo['parent_name'],
                'name' => $userInfo['parent_name'],
                'telephone' => $userInfo['telephone'],
                'access_token' => $userInfo['access_token'],
                'avatar' => $userInfo['avatar'],
            );
            if (preg_match('/Resources/', $result['avatar'])){
                $result['avatar'] = C('oss_path').$result['avatar'];
            } else {
                $result['avatar'] = "http://".WEB_URL."/Uploads/ParentAvatars/".$result['avatar'];
            }

            if ($Device=="iPad") {
                $login_mac_address['ipad_address'] = $mac;
                $login_channel_address['pad_channel_id_info'] = $channel_id;
            } else {
                $login_mac_address['mac_address'] = $mac;
                $login_channel_address['channel_id_info'] = $channel_id;
            }

            $this->Model->where("id=".$userInfo['id'])->save( $login_channel_address );
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
            $Device = getallheaders()['Device'];
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
            $this->ajaxReturn(array('status' => 404, 'message' => '密码错误'));
    }

    /**
     * @描述：注册
     * @参数：telephone[int] Y 手机号码
     * @参数：verify_code[int] Y 验证码
     * @参数：name[string] Y 姓名
     * @参数：email[string] Y 姓名
     * @参数：pwd[string] Y 密码
     * @参数：confirm_pwd[string] Y 确认密码
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */

    public function register()
    {
        $telephone = getParameter('telephone', 'int');
        $password  = sha1(getParameter('pwd', 'str'));
        $confirmPassword  = sha1(getParameter('confirm_pwd', 'str'));
        $name = getParameter('name', 'str');
        $email = getParameter('email', 'str',false);
        $verify_code = getParameter('verify_code', 'str');

        if (empty($telephone) || empty($password) || empty($name))
            $this->ajaxReturn(array('status' => 406, 'message' => '手机号、密码和姓名不能为空'));

        if ($confirmPassword <> $password)
            $this->ajaxReturn(array('status' => 406, 'message' => '密码与确认密码不一致'));

        if (strlen($telephone) <> 11)
            $this->ajaxReturn(array('status' => 403, 'message' => '手机号格式不正确'));

        if ($this->Model->getParentInfoByTelephone($telephone))
            $this->ajaxReturn(array('status' => 406, 'message' => '该用户已经注册'));

        if(!D('Misc_register_phone_validcode')->verifyCode($telephone,$verify_code))
            $this->ajaxReturn(array('status' => 500, 'message' => '验证码错误'));

        $token = guid();
        $data = array(
            'telephone' =>$telephone,
            'parent_name' =>$name,
            'email' =>$email,
            'password' => $password,
        );
        if(empty($parentId = $this->Model->addParent($data)))
            $this->ajaxReturn(array('status' => 500, 'message' => '注册失败'));
        $currentTime = time();
        //赠送30天vip
        if(D('Account_auths')->inserNode($parentId,4,3,$currentTime,$currentTime+3600*24*30*3,1))
            $this->ajaxReturn(array('status' => 200, 'message' => '注册成功','result' => array('name' => $name, 'token' => $token, 'id' => $parentId)));
        else
            $this->ajaxReturn(array('status' => 500, 'message' => '注册权限失败'));
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
        $telephone = getParameter('telephone', 'int');
        $code = getParameter('verify_code', 'int');
        $password = sha1(getParameter('newpassword','str'));

        if (empty($telephone) || empty($password))
            $this->ajaxReturn(array('status' => 406, 'message' => '手机号、密码不能为空'));
        if (strlen($telephone) <> 11)
            $this->ajaxReturn(array('status' => 403, 'message' => '手机号格式不正确'));

        if (!($userInfo = $this->Model->getParentInfoByTelephone($telephone)))
            $this->ajaxReturn(array('status' => 406, 'message' => '该用户未注册'));

        if(!D('Misc_register_phone_validcode')->verifyCode($telephone,$code))
            $this->ajaxReturn(array('status' => 500, 'message' => '验证码错误'));

        $token = guid();
        $data = array(
            'password' => $password,
            'access_token' => $token
        );
        $this->Model->updateParentInfo($data,$telephone);
        $this->ajaxReturn(array('status' => 200, 'message' => '成功设定新密码','result' =>array('telephone' => $telephone, 'token' => $token)));
    }
}