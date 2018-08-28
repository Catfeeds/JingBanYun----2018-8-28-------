<?php
namespace ApiInterface\Controller\Version1_0;

use Think\Controller;

class TeacherController extends PublicController
{
    public $pageSize = 20;
    public $Model;

    function getPageSize(){
        return $this->pageSize;
    }
    public function __construct()
    {
        parent::__construct();
        $this->Model = D('Auth_teacher');
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
        $userInfo = $this->Model->getTeacherByTel($telephone);

        if(empty($userInfo))
            $this->ajaxReturn(array('status' => 404, 'message' => '账号不存在'));

        if ($userInfo['password'] == $password) {
            $result = array(
                'id' => $userInfo['id'],
                'name' => $userInfo['name'],
                'telephone' => $userInfo['telephone'],
                'access_token' => $userInfo['access_token'],
                'avatar' => $userInfo['avatar'],
                'points' => $userInfo['points'],
                'brief_intro' => $userInfo['brief_intro'],
                'school_id' => $userInfo['school_id']
            );
            $school = D('Dict_schoollist')->getSchoolInfo($userInfo['school_id']);
            $result['school_name'] = $school['school_name'];

            if (preg_match('/Resources/', $result['avatar'])){
                $result['avatar'] = C('oss_path').$result['avatar'];
            } else {
                $result['avatar'] = "http://".WEB_URL."/Uploads/Avatars/".$result['avatar'];
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
            $role_id = 2;
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
            $teainfo = M('auth_teacher')->where( $map )->find();
            if($teainfo[$prevChannelName] !== $channel_id && !empty($teainfo[$prevChannelName])){
                $Message = new \Home\Controller\MessageController();
                $parameters = [];
                $Message->addPushUserMessage('LOGIN_PASS',ROLE_TEACHER,$id,$parameters,USE_PREV_CHANNEL_ID,USE_USER_DEFINE_MESSAGE);
            }
            if ( empty($teainfo[$orgChannelIdName]) ) { //判断渠道id是否为空
                $tid = M('auth_teacher')->where( $map )->save( $data );
            } else {
                if ( $teainfo[$orgChannelIdName] != $channel_id ) {
                    $tid = M('auth_teacher')->where( $map )->save( $data );
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
     * @参数：email[string] Y 姓名
     * @参数：pwd[string] Y 密码
     * @参数：confirm_pwd[string] Y 确认密码
     * @参数：role[int] Y 教师角色
     * @参数：school_id[int] Y 学校ID
     * @参数：course_grade[string] Y 学科年级字符串
     * @参数：brief_intro[string] N 简介
     * @参数：wechat[string] N 微信号
     * @参数：qq[int] N QQ号
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */

    public function register()
    {
        $telephone = getParameter('telephone', 'int');
        $name = getParameter('name', 'str');
        $password  = sha1(getParameter('pwd', 'str'));
        $confirmPassword  = sha1(getParameter('confirm_pwd', 'str'));
        $verify_code = getParameter('verify_code', 'int');

        $email = getParameter('email', 'str',false);
        $role = getParameter('role', 'int');;

        $school_id = getParameter('school_id', 'int');
        $course_grade = getParameter('course_grade', 'str');
        $brief_intro = getParameter('brief_intro', 'str',false);
        $wechat = getParameter('wechat', 'str',false);
        $qq = getParameter('qq', 'int',false);


        if (empty($telephone) || empty($password) || empty($name))
            $this->ajaxReturn(array('status' => 406, 'message' => '手机号、密码和姓名不能为空'));

        if (empty($school_id) || empty($course_grade))
            $this->ajaxReturn(array('status' => 403, 'message' => '学科、学校和年级不能为空'));

        if ($confirmPassword <> $password)
            $this->ajaxReturn(array('status' => 406, 'message' => '密码与确认密码不一致'));

        if (strlen($telephone) <> 11)
            $this->ajaxReturn(array('status' => 403, 'message' => '手机号格式不正确'));
        if (empty($this->Model->getTeacherByTel($telephone)))
            $this->ajaxReturn(array('status' => 406, 'message' => '该手机号已经注册'));

        if(!D('Misc_register_phone_validcode')->verifyCode($telephone,$verify_code))
            $this->ajaxReturn(array('status' => 500, 'message' => '验证码错误'));

        $course_id = 0;
        $grade_id = 0;
        if(isset($course_grade) && (!empty($course_grade)))
        {
            $course_grade = json_decode($course_grade,true);
            $course_grade_frist = $course_grade[0];
            $firstInfo = explode(',',$course_grade_frist);
            $course_id = $firstInfo[0];
            $grade_id  = $firstInfo[1];
        }

        $token = guid();
        $currentTime = time();
        $data = array(
            'telephone' => $telephone,
            'password' => $password,
            'create_at' => $currentTime,
            'update_at' => $currentTime,
            'name' => $name,
            'school_id' => $school_id,
            'course_id' => $course_id,
            'grade_id' => $grade_id,
            'email' => $email,
            'qq' => $qq,
            'role' => $role,
            'wechat' => $wechat,
            'access_token' => $token,
            'brief_intro' => $brief_intro
        );

        if($teacherId = $this->Model->addEditTeacher($data,$course_grade)) {
            //赠送30天vip
            if (D('Account_auths')->inserNode($teacherId, 2, 3, $currentTime, $currentTime + 3600 * 24 * 30 * 3, 1))
                $this->ajaxReturn(array('status' => 200, 'message' => '注册成功', 'result' => array('name' => $name, 'token' => $token, 'id' => $teacherId)));
            else
                $this->ajaxReturn(array('status' => 500, 'message' => '注册权限失败'));
        }
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
        $this->Model->addEditTeacher($data,0,$userInfo->id);
        $this->ajaxReturn(array('status' => 200, 'message' => '成功设定新密码','result' =>array('telephone' => $telephone, 'token' => $token)));
    }

    /**
     * @描述：教师详情
     * @参数：id[int] Y 教师ID
     * @返回值：详情页HTML
     */
    public function teacherDetails()
    {
        $id =getParameter('id','int',true);
        $result = $this->Model->getTeachInfo($id);
        $this->assign('data', $result);
        $this->display();
    }
    /**
     * @描述：教师风采列表
     * @参数：pageIndex[int] Y 页码
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */
    public function getGoodTeachers()
    {
        $pageIndex = getParameter('pageIndex', 'int');
        $pageSize = $this->getPageSize();
        $result = $this->Model->getGoodTeacherList($pageIndex, $pageSize);
        $list = array();
        foreach ($result as $val) {
            //dict_course
            $course = D('Dict_course')->getCourseInfo($val['course_id']);
            $course_name = empty($course) ? '' : $course['course_name'];
            $data = array();
            $data['id'] = $val['id'];
            $data['name'] = $val['name'];
            $data['avatar'] = $val['avatar'];
            $data['email'] = $val['email'];
            $data['tags'] = $val['tags'];
            $data['points'] = $val['points'];
            $data['brief_intro'] = $val['brief_intro'];
            $data['school_name'] = $val['school_name'];
            $data['school_age'] = $val['school_age'];
            $data['course_name'] = $course_name;
            if (strpos($val['avatar'], '.') === false) {
                $val['avatar'] .= '.jpg';
            }
            if (preg_match('/Resources/', $val['avatar'])) {
                $data['img_url'] = OSS_URL . $val['avatar'];
            } else {
                $data['img_url'] = "http://" . WEB_URL . "/Uploads/Avatars/" . $val['avatar'];
            }
            $list[] = $data;
        }

        if(WEB_URL=='www.jingbanyun.com')
        {
            $data['id']         = 0;
            $data['name']       = '暂无';
            $data['avatar']         = '1/1';
            $data['email']         = '';
            $data['tags']         = '';
            $data['points']         = '';
            $data['brief_intro']         = '';
            $data['school_name']         = '';
            $data['school_age']         = '';
            $data['course_name']         = '';
            $data['img_url']            = '';
            $list = array();
            $list[] = $data;

            $data['id']         = 0;
            $data['name']       = '暂无';
            $data['avatar']         = '1/1';
            $data['email']         = '';
            $data['tags']         = '';
            $data['points']         = '';
            $data['brief_intro']         = '';
            $data['school_name']         = '';
            $data['school_age']         = '';
            $data['course_name']         = '';
            $data['img_url']            = '';
            $list[] = $data;

            $data['id']         = 0;
            $data['name']       = '暂无';
            $data['avatar']         = '1/1';
            $data['email']         = '';
            $data['tags']         = '';
            $data['points']         = '';
            $data['brief_intro']         = '';
            $data['school_name']         = '';
            $data['school_age']         = '';
            $data['course_name']         = '';
            $data['img_url']            = '';
            $list[] = $data;

        }
        $this->ajaxReturn(array('status' => 200, 'message' => '成功设定新密码','result' =>$list));

    }



}