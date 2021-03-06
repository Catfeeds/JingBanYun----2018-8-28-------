<?php
namespace ApiInterface\Controller\Version1_2;

use Common\Common\REDIS;
use Common\Common\SMS; 

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
        $telephone = getParameter('telephone', 'str');
        $password  = sha1(getParameter('pwd', 'str'));
        $mac = getParameter('macToken', 'str',false);
        $Device = getParameter('Device', 'str',false);
        $channel_id = getParameter('channel_id', 'str',false);


        if (empty($telephone) || empty($password))
            $this->ajaxReturn(array('status' => 404, 'message' => '账号不存在'));
        $userInfo = $this->Model->getTeacherByTel($telephone);


        if(empty($userInfo))
            $this->ajaxReturn(array('status' => 404, 'message' => '账号不存在'));

        if($userInfo['flag'] == 0 || $userInfo['flag'] == -1)
        {
            $this->ajaxReturn(array('status' => 404, 'message' => '账号被停用'));
        }

        if ($userInfo['password'] == $password) {

            $result = array(
                'id' => $userInfo['id'],
                'name' => $userInfo['name'],
                'telephone' => $userInfo['telephone'],
                'access_token' => $userInfo['access_token'],
                'avatar' => $userInfo['avatar'],
                'points' => $userInfo['points'],
                'brief_intro' => $userInfo['brief_intro'],
                'school_id' => $userInfo['school_id'],
                'need_perfect' => empty($userInfo['school_id'])? 1:0
            );
            $school = D('Dict_schoollist')->getSchoolInfo($userInfo['school_id']);
            $result['school_name'] = $school['school_name'];

            (new CommonController())->getAvatarInfo(ROLE_TEACHER,$result);


            $userip = getIPaddress();

            if (!empty($userip)){

                if ($userip != '127.0.0.1') {
                    $shengfen = getIPLoc_sina( $userip );
                } else {
                    $shengfen = '内网';
                }
                if (empty($shengfen)){
                    $shengfen = '内网';
                }

                if (empty($userInfo['login_address'])){ //添加

                    $teacherLogin_address['login_address'] = $shengfen;
                    $this->Model->where("id=".$userInfo['id'])->save( $teacherLogin_address );

                } else{ //判断
                    if ($shengfen != $userInfo['login_address']){ //发送
                        $teacherLogin_address['login_address'] = $shengfen;
                        $rsave_id= $this->Model->where("id=".$userInfo['id'])->save( $teacherLogin_address );
                        if ( $rsave_id != false && !empty($userInfo['login_address'])){ //发送消息，异地登陆
                            $parameters = array( 'msg' => array(date("Y-m-d H:i:s",time()),$shengfen."($userip)",'app') , 'url' => array( 'type' => 0));
                            $Message = new \Home\Controller\MessageController();
                            $Message->addPushUserMessage('EXCEPTION_LOGIN',2,$userInfo['id'],$parameters);
                        }
                    }
                }

            }
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

            $account_list   = D('Account_auths')->isVipInfo($userInfo['id'],2);
            if (empty($account_list['is_auth'])) {
                $account_list['is_auth']=2;
            }
            $result['is_auth'] =$account_list['is_auth'];

            $editorTmap['teacher_id'] = $userInfo['id'];
            $editorTmap['delete_status'] = 1;
            $editorT = M('editor_teacher_concat')->where($editorTmap)->find();
            if (!empty($editorT)) {
                $result['is_editor'] = 1;
            } else {
                $result['is_editor'] = 0;
            }

            $this->ajaxReturn(array('status' => 200,'result'=>$result));
        } else
            $this->ajaxReturn(array('status' => 404, 'message' => '登录信息有误,请核对登录信息'));
    }

    /**
     * @描述：注册
     * @参数：telephone[int] Y 手机号码
     * @参数：name[string] Y 姓名
     * @参数：email[string] Y 姓名
     * @参数：pwd[string] Y 密码
     * @参数：confirm_pwd[string] Y 确认密码
     * @参数：role[int] Y 教师角色
     * @参数：school_id[int] Y 学校ID
     * @参数：professional[int] Y 职称ID
     * @参数：version_course_grade[string] Y 版本学科年级字符串
     * @参数：brief_intro[string] N 简介
     * @参数：wechat[string] N 微信号
     * @参数：qq[int] N QQ号
     * @参数：sex[string] Y 性别
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */  

    public function register()
    {
        $telephone = getParameter('telephone', 'str');
        $name = getParameter('name', 'str');
        $password  = sha1(getParameter('pwd', 'str'));
        $confirmPassword  = sha1(getParameter('confirm_pwd', 'str'));
        $professional = getParameter('professional', 'int',false);
        $sex = getParameter('sex', 'str',false);

        $email = getParameter('email', 'str',false);
        $role = getParameter('role', 'int');;

        $school_id = getParameter('school_id', 'int');
        $version_course_grade = $_POST['version_course_grade'];
        if(!is_array($version_course_grade))
            $version_course_grade = json_decode($version_course_grade,true);
        $brief_intro = getParameter('brief_intro', 'str',false);
        $weChat = getParameter('wechat', 'str',false);
        $qq = getParameter('qq', 'int',false);


        if (empty($telephone) || empty($password) || empty($name))
            $this->ajaxReturn(array('status' => 406, 'message' => '手机号、密码和姓名不能为空'));

        if (empty($school_id) )
            $this->ajaxReturn(array('status' => 403, 'message' => '学校不能为空'));

        if (empty($version_course_grade))
            $this->ajaxReturn(array('status' => 403, 'message' => '版本学科和年级不能为空'));

        if (!is_array($version_course_grade))
            $this->ajaxReturn(array('status' => 403, 'message' => '版本学科年级信息格式错误'));

        $versionList = C('TEXTBOOK_VERSION');
        foreach($version_course_grade as $key=>$val)
        {
            $info = explode(',',$val);
            if(count($info) <> 3)
            {
                $this->ajaxReturn(array('status' => 406, 'message' => '版本学科年级格式错误'));
            }
            // version_name to every string
            foreach($versionList as $index=>$data)
            {
                if($data['id'] == $info[0])
                {
                    $version_course_grade[$key] .= ','.$data['name'];
                    break;
                }
            }
        }

        if ($confirmPassword <> $password)
            $this->ajaxReturn(array('status' => 406, 'message' => '密码与确认密码不一致'));

        if (strlen($telephone) <> 11)
            $this->ajaxReturn(array('status' => 403, 'message' => '手机号格式不正确'));
        
        $telephone_result=$this->Model->getTeacherByTel($telephone);
        if (!empty($telephone_result))
            $this->ajaxReturn(array('status' => 406, 'message' => '该手机号已经注册'));

        $school_info=D('Dict_schoollist')->getSchoolInfo($school_id);
        if(empty($school_info)){
            $this->ajaxReturn(array('status' => 406, 'message' => '学校信息不存在'));
        }

        $course_grade_first = $version_course_grade[0];
        $firstInfo = explode(',',$course_grade_first);

        //教师第一版本 学科 年级
        //$version_id = $firstInfo[0];
        $course_id = $firstInfo[1];
        $grade_id  = $firstInfo[2];

        $token = $this->guid();
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
            'sex'=>$sex,
            'role' => $role,
            'wechat' => $weChat,
            'access_token' => $token,
            'professional' => $professional,
            'brief_intro' => $brief_intro
        );

        if($teacherId = $this->Model->addEditTeacherWithVersion($data,$version_course_grade)) {
            //赠送30天vip
            $vip_config=C('VIP_CONFIG.APP_REGISTER_GIVE_VIP_STATUS');
            
            if($vip_config && $vip_config<=3){ 
                $result=give_new_vip_operation(2, $vip_config,$teacherId,$school_id); 
                 
                if($result['status']=='success'){
                    $smsApi = new SMS();
                    $smsApi->newUserNotice($telephone);
                    $Message = new \Home\Controller\MessageController();
                    $parameters = array( 'msg' => array() , 'url' => array( 'type' => 0));
                    $Message->addPushUserMessage('REG_SUCCESS',2,$teacherId,$parameters);
                    $this->ajaxReturn(array('status' => 200, 'message' => '注册成功', 'result' => array('name' => $name, 'token' => $token, 'id' => $teacherId)));
                }else{
                    $this->ajaxReturn(array('status' => 500, 'message' => '注册权限失败'));
                }
            } 
            $this->ajaxReturn(array('status' => 200, 'message' => '注册成功', 'result' => array('name' => $name, 'token' => $token, 'id' => $teacherId)));
        }
    }

    /**
     * @描述：修改个人信息
     * @参数：name[string] Y 姓名
     * @参数：email[string] Y EMAIL
     * @参数：school_id[int] Y 学校ID
     * @参数：professional[int] Y 职称ID
     * @参数：version_course_grade[string] Y 版本学科年级字符串
     * @参数：brief_intro[string] N 简介
     * @参数：sex[string] Y 性别
     * @参数：schoolAge[string] Y 教龄
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
        $professional = getParameter('professional', 'int',false);
        $sex = getParameter('sex', 'str');
        $schoolAge = getParameter('schoolAge', 'int');

        $email = getParameter('email', 'str',false);
        $school_id = getParameter('school_id', 'int');

        $version_course_grade = $_POST['version_course_grade'];
        if(!is_array($version_course_grade))
            $version_course_grade = json_decode($version_course_grade,true);
        $brief_intro = getParameter('brief_intro', 'str',false);


        if (empty($name))
            $this->ajaxReturn(array('status' => 406, 'message' => '姓名不能为空'));

        if (empty($school_id) )
            $this->ajaxReturn(array('status' => 403, 'message' => '学校不能为空'));

        if (empty($version_course_grade))
            $this->ajaxReturn(array('status' => 403, 'message' => '版本学科和年级不能为空'));

        if (!is_array($version_course_grade))
            $this->ajaxReturn(array('status' => 403, 'message' => '版本学科年级信息格式错误'));

        $versionList = C('TEXTBOOK_VERSION');
        foreach ($version_course_grade as $key => $val) {
            $info = explode(',', $val);
            if (count($info) <> 3) {
                $this->ajaxReturn(array('status' => 406, 'message' => '版本学科年级信息格式错误'));
            }
            // version_name to every string
            foreach ($versionList as $index => $data) {
                if ($data['id'] == $info[0]) {
                    $version_course_grade[$key] .= ',' . $data['name'];
                    break;
                }
            }
        }

        $school_info=D('Dict_schoollist')->getSchoolInfo($school_id);
        if(empty($school_info)){
            $this->ajaxReturn(array('status' => 406, 'message' => '学校信息不存在'));
        }

        $course_grade_first = $version_course_grade[0];
        $firstInfo = explode(',',$course_grade_first);

        //教师第一版本 学科 年级
        //$version_id = $firstInfo[0];
        $course_id = $firstInfo[1];
        $grade_id  = $firstInfo[2];

        $currentTime = time();
        $data = array(
            'update_at' => $currentTime,
            'name' => $name,
            'school_id' => $school_id,
            'course_id' => $course_id,
            'grade_id' => $grade_id,
            'email' => $email,
            'sex'=>$sex,
            'professional' => $professional,
            'school_age' => $schoolAge,
            'brief_intro' => $brief_intro
        );
        $oldTeacherInfo = D('Auth_teacher')->getTeacherSimpleData($id);
        if($teacherId = $this->Model->addEditTeacherWithVersion($data,$version_course_grade,$id)) {
            if (!empty($data['school_id']) && ($oldTeacherInfo['school_id'] != $data['school_id'])) {
                //修改教师学校审核状态为未审核

                $school_info = D('Dict_schoollist')->getSchoolInfo($data['school_id']);
                if ($school_info['is_create_administartor'] == 1) { //修改后的学校为认证的学校
                    $parameters = array(
                        'msg' => array(
                            $name,
                        ),
                        'url' => array('type' => 0)
                    );
                    D('UserInfo')->sendMsg(ROLE_TEACHER,$id,json_encode($parameters),'ADMIN_SEND_SCHOOL_TEACHER',date('Y-m-d H:i:s',time()+3));
                }
            }

            $this->ajaxReturn(array('status' => 200, 'message' => '修改成功', 'result' => array()));
        }
        else
            $this->ajaxReturn(array('status' => 500, 'message' => '修改失败', 'result' => array()));
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
        if (strlen($telephone) > 11)
            $this->ajaxReturn(array('status' => 403, 'message' => '手机号格式不正确'));

        if (!($userInfo = $this->Model->getTeacherByTel($telephone)))
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
            $redis->close();
            $this->ajaxReturn(array('status' => 500, 'message' => '验证码错误'));
        }
         
        $token = $this->guid(); 
        $data = array(
            'password' => $password,
            'access_token' => $token
        ); 
        $redis->delete($redis_key);
        $redis->close();
         
        
        $this->Model->addEditTeacher($data,0,$userInfo['id']);

        $parameters = array( 'msg' => array(date("Y-m-d H:i:s",time())) , 'url' => array( 'type' => 0));
        $Message = new \Home\Controller\MessageController();
        $Message->addPushUserMessage('PASSWORD_MODIFY',2,$userInfo['id'],$parameters);
 
        $this->ajaxReturn(array('status' => 200, 'message' => '成功设定新密码','result' =>array('telephone' => $telephone, 'token' => $token)));
    }
    //获取token

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
            //$course = D('Dict_course')->getCourseInfo($val['course_id']);
            //$course_name = empty($course) ? '' : $course['course_name'];
            $course_name = $val['course_name'];
            $data = array();
            $data['id'] = $val['id'];
            $data['sex'] = $val['sex'];
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
                if ($val['sex'] == '男') {
                    $data['img_url'] = 'http://'.WEB_URL.'/Public/img/classManage/teacher_m.png';
                } else {
                    $data['img_url'] = 'http://'.WEB_URL.'/Public/img/classManage/teacher_w.png';
                }
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

        $this->ajaxReturn(array('status' => 200, 'message' => 'success','result' =>$list));

    }

    public function getTeacherCourses()
    {
        $teacherId = getParameter('userId', 'int');
        $this->showMessage(200, 'success', D('Auth_teacher')->getTeacherAllCourse($teacherId));
    }


}