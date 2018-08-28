<?php
namespace ApiInterface\Controller\Version1_2;

use Common\Common\REDIS;
use Common\Common\SMS;

class CommonController extends PublicController
{

    public $pageSize = 20;

    function getPageSize(){
        return $this->pageSize;
    }
    public function __construct()
    {
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
    }
    /**
     * @描述：增加访问记录
     * @参数：user_type[int] Y 用户类型
     * @参数：user_id[int] Y 用户ID
     * @参数：machine[string] Y 用户机器字符串
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */
    public function addAccessRecord()
    {
        $data['machine_type'] = getParameter('machine', 'str');
        $data['user_type'] = getParameter('user_type', 'int');
        $data['user_id'] = getParameter('user_id', 'int');

        $user_IP = ($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
        $user_IP = ($user_IP) ? $user_IP : $_SERVER["REMOTE_ADDR"];

        $data['ip_address'] = $user_IP;
        $data['create_at'] = time();
        D('App_statistics')->addAccessRecord($data);
        $this->ajaxReturn('success');
    }
    /**
     * @描述：今日课堂用户登录
     * @参数：telephone[int] Y 手机号码
     * @参数：pwd[string] Y 密码
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */
    public function jrkt_login()
    {
        $telephone = getParameter('telephone', 'int');
        $password  = getParameter('pwd', 'str');

        if (empty($telephone) || empty($password))
            $this->ajaxReturn(array('status' => 404, 'message' => '用户名密码错误'));

        $password = md5($password);
        $userInfo = D('UserInfo')->getUserInfoBytelephone($telephone);


        if(empty($userInfo))
            $this->ajaxReturn(array('status' => 404, 'message' => '用户不存在'));

        if(strtolower($userInfo['password'])==strtolower($password))
        {
            $result = array(
                'id' => $userInfo['id'],
                'name' => $userInfo['name'],
                'role' => $userInfo['usertype'] //0--teacher 1--student 2--parent
            );
            $this->ajaxReturn(array('status' => 200, 'result' => $result));
        }
        else
            $this->ajaxReturn(array('status' => 404, 'message' => '用户名密码错误'));
    }

    public function getAvatarInfo($role,&$result)
    {
        if($role==ROLE_TEACHER){
            if (preg_match('/Resources/', $result['avatar'])){
                if(strpos($result['avatar'],'.')===false)
                {
                    $result['avatar'].='.jpg';
                }
                $result['img_url'] = C('oss_path').$result['avatar'];
            } else {
                if ($result['sex'] == '男' || empty($result['sex'])) {
                    $result['img_url'] = 'http://'.WEB_URL.'/Public/img/classManage/teacher_m.png';
                } else {
                    $result['img_url'] = 'http://'.WEB_URL.'/Public/img/classManage/teacher_w.png';
                }

            }
        }elseif ($role==ROLE_STUDENT){
            if (preg_match('/Resources/', $result['avatar'])){
                if(strpos($result['avatar'],'.')===false)
                {
                    $result['avatar'].='.jpg';
                }
                $result['img_url'] = C('oss_path').$result['avatar'];
            } else {
                if ($result['sex'] == '男' || empty($result['sex'])) {
                    $result['img_url'] = 'http://'.WEB_URL.'/Public/img/classManage/student_m.png';
                } else {
                    $result['img_url'] = 'http://'.WEB_URL.'/Public/img/classManage/student_w.png';
                }
            }
        } else {
            if (preg_match('/Resources/', $result['avatar'])){
                if(strpos($result['avatar'],'.')===false)
                {
                    $result['avatar'].='.jpg';
                }
                $result['img_url'] = C('oss_path').$result['avatar'];
            } else {

                if ($result['sex'] == '男' || empty($result['sex'])) {
                    $result['img_url'] = 'http://'.WEB_URL.'/Public/img/classManage/jiazhang.png';
                } else {
                    $result['img_url'] = 'http://'.WEB_URL.'/Public/img/classManage/jiazhang2.png';
                }

            }
        }

        if(empty($result['avatar'])){
            $result['avatar'] = $result['img_url'];
        }
        $result['avatar'] = $result['img_url'];
    }
    private function __getVipInfo($userId,$role)
    {
        if ($userId == 0 || $role==5 ) { //游客模式要获取的权限
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
            $auth_list = $auth_type_use->getIphoneAuthAndVipauth($userId,$role);
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
        $isvip = $auth_type_use->isVipInfo($userId,$role);

        if ($isvip['is_vip'] == 1) {
            $data['is_vip'] = 1;
        } else {
            $data['is_vip'] = 2;
        }
        $result['auth_list'] = $data;
        return $data;
    }

    private function getAvatar(&$result)
    {
        if ($result['role'] == ROLE_TEACHER) {

            if (preg_match('/Resources/', $result['avatar'])) {
                if (strpos($result['avatar'], '.') === false) {
                    $result['avatar'] .= '.jpg';
                }
                $result['avatar'] = C('oss_path') . $result['avatar'];
            } else {
                if ($result['sex'] == '男' || empty($result['sex'])) {
                    $result['avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/teacher_m.png';
                } else {
                    $result['avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/teacher_w.png';
                }

            }
        } elseif ($result['role'] == ROLE_STUDENT) {
            if (preg_match('/Resources/', $result['avatar'])) {
                if (strpos($result['avatar'], '.') === false) {
                    $result['avatar'] .= '.jpg';
                }
                $result['avatar'] = C('oss_path') . $result['avatar'];
            } else {

                if ($result['sex'] == '男' || empty($result['sex'])) {
                    $result['avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/student_m.png';
                } else {
                    $result['avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/student_w.png';
                }

            }
        } elseif ($result['role'] == ROLE_PARENT) {


            if (preg_match('/Resources/', $result['avatar'])) {
                if (strpos($result['avatar'], '.') === false) {
                    $result['avatar'] .= '.jpg';
                }
                $result['avatar'] = C('oss_path') . $result['avatar'];
            } else {

                if ($result['sex'] == '男' || empty($result['sex'])) {
                    $result['avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/jiazhang.png';
                } else {
                    $result['avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/jiazhang2.png';
                }

            }
        }
    }

    /**
     * @描述：用户验证
     * @参数：role[int] Y 用户类型
     * @参数：userId[int] Y 用户ID
     * @参数：telephone[str] Y 手机
     * @参数：name[str] N 姓名
     * @参数：password[str] Y 明文密码
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */
    public function loginVerify()
    {
        $userId = getParameter('userId', 'int');
        $role  = getParameter('role', 'int');
        $studentName = getParameter('name','str',false);
        $telephone = getParameter('telephone','str',false);
        $password = getParameter('password','str',false);
        switch($role){
            case ROLE_TEACHER:$userInfo = D('Auth_teacher')->getTeacherByTel($telephone);
                               break;
            case ROLE_STUDENT:$userInfo = D('Auth_student')->getStudentInfoByTelAndName($telephone,$studentName);
                               break;
            case ROLE_PARENT: $userInfo = D('Auth_parent')->getParentInfoByTelephone($telephone);
                               break;
            case ROLE_YOUKE:  $userInfo['id'] = $userId;
                               $userInfo['password'] = sha1($password);
                               break;
            default:$userInfo = [];
                     break;
        }
        if($userInfo['flag'] == 0 || $userInfo['flag'] == -1)
        {
            $this->showMessage(404, '账号被停用');
        }
        $userInfo['auth_list'] = $this->__getVipInfo($userId,$role);
        $userInfo['role'] = $role;
        $account_list   = D('Account_auths')->isVipInfo($userId,$role);
        if (empty($account_list['is_auth'])) {
            $account_list['is_auth']=2;
        }
        $userInfo['is_auth'] =$account_list['is_auth'];
        $this->getAvatar($userInfo);

        $editorTmap['teacher_id'] = $userId;
        $editorTmap['delete_status'] = 1;
        $editorT = M('editor_teacher_concat')->where($editorTmap)->find();
        if (!empty($editorT)) {
            $userInfo['is_editor'] = 1;
        } else {
            $userInfo['is_editor'] = 0;
        }

        if($userInfo['id'] == $userId && $userInfo['password'] == sha1($password)){

            if ($role != ROLE_PARENT && $role != ROLE_YOUKE) {
                $userInfo['need_perfect'] = empty($userInfo['school_id'])? 1:0;
            }
            
            if ($role == ROLE_STUDENT) {
                $userInfo['name'] = $userInfo['student_name'];
                $userInfo['telephone'] = $userInfo['parent_tel'];

                unset($userInfo['student_name']);
                unset($userInfo['parent_tel']);
            }

            unset($userInfo['pad_channel_id_info']);
            unset($userInfo['channel_id_info']);
            unset($userInfo['password']);
            $data['status'] = 200;
            $data['message'] = "验证通过";
            $data['result'] = $userInfo;
            echo json_encode($data);
            //$this->showMessage(200,'验证通过',$userInfo);
        }
        else{
            $this->showMessage(500,'验证失败');
        }

    }
    /**
     * @描述：今日课堂获取个人信息
     * @参数：role[int] Y 用户类型
     * @参数：user_id[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */
    public function getPersonalInfo()
    {
        $userId = getParameter('userId', 'int');
        $role  = getParameter('role', 'int');

        switch($role)
        {   
            case ROLE_TEACHER:
                     $result = D('Auth_teacher')->getTeachInfo($userId);
                     $versionList = C('TEXTBOOK_VERSION');
                     $list = D('Auth_teacher_second')->getVersionCourseGradeById($userId);//add version info
                     $course_grade = array();
                     $course_gradeName = array();
                     if(!empty($list))
                     {
                         foreach($list as $val)
                         {
                             $course_grade[] = $val['grade_id'] .','.$val['course_id'].','.$val['version_id'];
                             $versionName = '';
                             foreach ($versionList as $index => $data) {
                                 if ($data['id'] == $val['version_id']) {
                                     $versionName =  $data['name'];
                                     break;
                                 }
                             }
                             $course_gradeName[] = $val['grade'].' '.$val['course_name'].' '.$versionName;
                         }
                     }
                     $result['version_course_grade'] = $course_grade;
                     $result['version_course_grade_name'] = $course_gradeName;
                     //add profession name

                     break;
            case ROLE_STUDENT: $result = D('Auth_student')->getStudentInfo($userId);
                                $result['telephone'] = $result['parent_tel'];
                                break;
            case ROLE_PARENT: $result = D('Auth_parent')->getParentInfo($userId);
                     break;
            default:$this->ajaxReturn(array('status' => 404, 'message' => '用户类型错误'));
        }

        $result = empty($result) ? array():$result;

        if((($role==ROLE_STUDENT) || ($role==ROLE_TEACHER)) )//增加学校昵称
        {
            if(!empty($result['school_id'])) {
                $school = D('Dict_schoollist')->getSchoolInfo($result['school_id']);
                if (!empty($school)) {
                    $result['school_name'] = $school['school_name'];

                } else {
                    $result['school_name'] = '';
                }
            }
            else
                $result['school_name'] = '';
        }

        $this->getAvatarInfo($role,$result);

        if(array_key_exists('parent_name',$result))
        {
            if(empty($result['parent_name']))
                $result['parent_name'] = '';
            $result['name'] = $result['parent_name'];

        }

        if(isset($result['id_card']))
          if(null == $result['id_card'])
             $result['id_card'] = '';

        if(!empty($result['birth_date']))
          $result['birth_date'] = date('Y-m-d',$result['birth_date']);
        $this->ajaxReturn(array('status' => 200, 'result' => $result));
    }
    /**
     * @描述：重置密码
     * @参数：telephone[int] Y 手机号码
     * @参数：verify_code[int] Y 验证码
     * @参数：pwd[string] Y 密码
     * @参数：confirm_pwd[string] Y 确认密码
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */
    public function resetPassword()
    {
        $telephone = getParameter('telephone', 'int');
        $verify_code = getParameter('verify_code', 'int');
        $password = getParameter('pwd', 'str');
        $confirmPassword = getParameter('confirm_pwd', 'str');
        $user_info=(D('UserInfo')->getUserInfoBytelephone($telephone));
        if(empty($user_info))
            $this->ajaxReturn(array('status' => 404, 'message' => '手机号不存在'));

        if(empty($telephone) || empty($password) || empty($confirmPassword) || empty($verify_code))
            $this->ajaxReturn(array('status' => 404, 'message' => '参数错误'));

        if($password!=$confirmPassword)
            $this->ajaxReturn(array('status' => 404, 'message' => '两次密码不一致'));
        $password = md5($password);

        if(!D('Misc_register_phone_validcode')->verifyCode($telephone,$verify_code))
            $this->ajaxReturn(array('status' => 500, 'message' => '验证码错误'));
        D('UserInfo')->updateInfoByTelephone(array('password'=>$password),$telephone);
        $this->ajaxReturn(array('status' => 200, 'message' => '更改成功'));
    }
    /**
     * @描述：上传头像
     * @参数：role[int] Y 用户类型
     * @参数：user_id[int] Y 用户ID
     * @参数：file[file] Y 头像FILE
     * @返回值：array(
     *    status 状态码
     *    message 异常字符串
     *    result 结果数组
     * )
     */
    public function uploadAvatar()
    {
        $userId = getParameter('user_id', 'int');
        $role  = getParameter('role', 'int');
        switch($role)
        {
            case 0: $dirName = "./Uploads/Avatars/";
                $fileName = $userId ."_t.jpg";
                $model = D('Auth_teacher');
                break;
            case 1: $dirName = "./Uploads/StudentAvatars/";
                $fileName = $userId .".jpg";
                $model = D('Auth_student');
                break;
            case 2: $dirName = "./Uploads/ParentAvatars/";
                $fileName = $userId .".jpg";
                $model = D('Auth_parent');
                break;
            default:exit;
        }
        header("Access-Control-Allow-Origin: *");
        if ($_FILES["file"]["type"] == "image/jpeg" || $_FILES["file"]["type"] == "image/png") {
            if ($_FILES["file"]["error"] > 0) {
                $this->ajaxReturn(array('status' => 406, 'message' => $_FILES["file"]["error"]));
            } else {
                $tmp = str_replace('server.','server',$_SERVER['DOCUMENT_ROOT'].$dirName . $fileName);

                $is_upload = move_uploaded_file($_FILES["file"]["tmp_name"], $tmp); //注意文件夹权限

                $imgpath = $_SERVER['DOCUMENT_ROOT'].$dirName . $fileName;
                $imgpath = str_replace($_SERVER['DOCUMENT_ROOT'].'.',$_SERVER['DOCUMENT_ROOT'],$imgpath);

                $urldata = curl_post('http://'.WEB_URL.'/index.php?m=Home&c=App&a=upload_file',$imgpath);
                $jsonurl = json_decode($urldata,true); //得到oss文件路径
                if( $jsonurl ) {
                    $data = array(
                        'avatar' => $jsonurl[1],
                        'update_at' => time(),
                    );
                    $model->updateInfoById($data,$userId);
                    unlink($dirName . $fileName);
                    $this->ajaxReturn(array('status' => 200, 'message' => '上传成功'));
                } else {
                    $this->ajaxReturn(array('status' => 500, 'message' => '上传失败'));
                }
            }
        } else {
            $this->ajaxReturn(array('status' => 500, 'message' => '图片格式不正确'));
        }
    }


    //获取所有以前小黑板的数据
    public function getblackboardandclass() {
        set_time_limit(0);
        $row = M('biz_blackboard')->field('id,class_id')->select();

        foreach ( $row as $k=>$v ) {
            $data = array(
                'b_id' => $v['id'],
                'class_id' => $v['class_id'],
            );

            $list = M('boardandclass')->where( $data )->find();
            if ( empty( $list ) && $v['class_id'] != 0 ) {
                M('boardandclass')->add( $data );
            }
        }

        echo "执行完毕";
    }
    private function _getSMSCategory($method)
    {
        switch($method)
        {
            case 'register':return '注册';
            case 'modifyTelephoneStep2':return '修改手机号码';
            case 'forgetPassword':return '忘记密码';
            case 'modifyTelephoneStep1': return '修改手机号码';
            default:return '';
        }
    }
    /**
     * @描述：发送验证码（加时间验证鉴权）
     * @参数：telephone[str] Y 手机号
     * @参数：role[int] Y 角色ID 2--教师 3--学生 4--家长
     * @参数：method[str] Y 获取验证码方式
     *                      register -- 注册
     *                      forgetPassword -- 忘记密码
     *                      modifyTelephoneStep1 -- 修改手机号码手机验证步骤1
     *                      modifyTelephoneStep2 -- 修改手机号码手机验证步骤2
     * @参数：name[str] N 学生姓名
     *                     method 为 register 或 modifyTelephoneStep2时必填
     * @参数：time[str] Y 10位UNIX时间字符串
     * @参数：token[str] Y 加密字符串
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */

    public function sendVerifyCodeEncrypt()
    {
        $telephone = getParameter('telephone','str');
        $role = getParameter('role','int');
        $method = getParameter('method','str');
        $token = getParameter('token','str');
        $time = getParameter('time','str');
        $student_name = getParameter('name','str',false);
        $userId = getParameter('userId','int',false);
        $currentTime = time();

        if(abs($currentTime - $time) < 60*5)
        {
            $appid='744pCHqN';
            $appkey='gpXZPB';
            $string=md5($appid.$time.$appkey);
            if($token != $string)
                $this->showMessage(500, '鉴权失败');
        }
        else
            $this->showMessage(500, '当前系统时间不准确');
        $smsCategory = $this->_getSMSCategory($method);
        switch($method)
        {
            case "register":
            case "modifyTelephoneStep2":
                   switch($role) {
                       case ROLE_TEACHER:
                           $info = D('Auth_teacher')->getTeacherByTel($telephone);
                           if (!empty($info))
                               $this->showMessage(500, '该手机号对应教师已经注册');
                           break;
                       case ROLE_STUDENT:
                           if(empty($student_name))
                               $this->showMessage(500, '请输入学生姓名');
                           $info = D('Auth_student')->getStudentInfoByTelAndName($telephone, $student_name);
                           if (!empty($info))
                               $this->showMessage(500, '该学生已经注册');

                           break;
                       case ROLE_PARENT: $info = D('Auth_parent')->getParentInfoByTelephone($telephone);
                           if(!empty($info))
                               $this->showMessage(500, '该手机号对应家长已经注册');
                           break;
                       default:$this->showMessage(500, '角色参数错误');break;
                   }
                   break;
            case "forgetPassword":
            case "modifyTelephoneStep1":
                switch($role)
                {
                    case ROLE_TEACHER: $info = D('Auth_teacher')->getTeacherByTel($telephone);
                        if(empty($info))
                            $this->showMessage(500, '该教师未注册');
                        break;
                    case ROLE_STUDENT:
                        if('forgetPassword' == $method)
                            $info = D('Auth_student')->getStudentInfoByTelAndName($telephone, $student_name);
                        else
                            $info = D('Auth_student')->getStudentInfo($userId);
                        if(empty($info))
                            $this->showMessage(500, '该学生未注册');
                        break;
                    case ROLE_PARENT: $info = D('Auth_parent')->getParentInfoByTelephone($telephone);
                        if(empty($info))
                            $this->showMessage(500, '该家长未注册');
                        break;
                    default:$this->showMessage(500, '角色参数错误');break;
                }
                break;
            default:$this->showMessage(500, 'method参数错误');break;
        }
        $redis_obj=new REDIS();
        $redis=$redis_obj->init_redis();
        $sendSmsFunction='newTemplateSMS';

        $pregs = preg_match_all("/^[1][3,4,5,7,8][0-9]{9}$/",$telephone);
        if (empty($telephone) || strlen($telephone) != 11 || $pregs == 0 || $pregs == false) {
            $this->showMessage(500, '手机号码格式错误');
        }

        $redis_key='sms_'.$telephone;
        $redis_exists = $redis->exists('p'.$redis_key);
        $remainSeconds = $redis->ttl('p'.$redis_key);
        if ($redis_exists) {
            $this->showMessage(500, "验证码请求过于频繁,请过 $remainSeconds 秒后重试");
        }

        $randcode = rand(10000, 99999);
        $smsapi = new SMS();
        $ret = $smsapi->$sendSmsFunction($telephone, "$smsCategory," . $randcode, 'json');
        if ($ret['status'] == false ||
            $ret < 0) {
            if($ret['reason'] == 105122) //发送过于频繁
              $this->showMessage(500, '发送验证码次数超出限制，请24小时再试');
            else
              $this->showMessage(500, '发送验证码失败，请稍候再试');
            return;
        }
        $redis->setex($redis_key,300,$randcode);
        $redis->setex('p'.$redis_key,62,$randcode);
        $redis->close();

        $this->showMessage(200, '验证码发送成功');
    }

    /**
     * @描述：注册第一步对验证码进行验证
     * @参数：telephone[str] Y 手机号
     * @参数：role[int] Y 角色ID 2--教师 3--学生 4--家长
     * @参数：name[str] N 学生姓名 角色为学生时必填
     * @参数：verifyCode[int] Y 验证码
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function verifyCode()
    {
        $telephone = getParameter('telephone','str');
        $role = getParameter('role','int',false);
        $student_name = getParameter('name','str',false);
        $verifyCode = getParameter('verifyCode','str');
        switch($role) {
            case ROLE_TEACHER:
                $info = D('Auth_teacher')->getTeacherByTel($telephone);
                if (!empty($info))
                    $this->showMessage(500, '该手机号对应教师已经注册');
                break;
            case ROLE_STUDENT:
                if(empty($student_name))
                    $this->showMessage(500, '请输入学生姓名');
                $info = D('Auth_student')->getStudentInfoByTelAndName($telephone, $student_name);
                if (!empty($info))
                    $this->showMessage(500, '该学生已经注册');

                break;
            case ROLE_PARENT: $info = D('Auth_parent')->getParentInfoByTelephone($telephone);
                if(!empty($info))
                    $this->showMessage(500, '该手机号对应家长已经注册');
                break;
        }
        //验证码验证
        $redis_obj=new REDIS();
        $redis=$redis_obj->init_redis();
        $redis_key='sms_'.$telephone;
        $code=$redis->get($redis_key);
        if($verifyCode!=$code || !$verifyCode){
            $redis->close();
            $this->showMessage(500,'验证码错误');
        }
        $redis->setex($redis_key,1,123);
        $redis->close();
        $this->showMessage(200, '验证成功');
    }
    /**
     * @描述：修改手机号第一步对验证码进行验证
     * @参数：telephone[str] Y 手机号
     * @参数：role[int] Y 角色ID 2--教师 3--学生 4--家长
     * @参数：name[str] N 学生姓名 角色为学生时必填
     * @参数：verifyCode[int] Y 验证码
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function verifyCodeForModifyTelephone()
    {
        $telephone = getParameter('telephone','str');
        $role = getParameter('role','int',false);
        $student_name = getParameter('name','str',false);
        $verifyCode = getParameter('verifyCode','str');

        //验证码验证
        $redis_obj=new REDIS();
        $redis=$redis_obj->init_redis();
        $redis_key='sms_'.$telephone;
        $code=$redis->get($redis_key);
        if($verifyCode!=$code || !$verifyCode){
            $redis->close();
            $this->showMessage(500,'验证码错误');
        }
        $redis->setex($redis_key,1,123);
        $redis->close();
        $this->showMessage(200, '验证成功');
    }
    /**
     * @描述：修改手机号
     * @参数：originalTelephone[str] Y 原手机号
     * @参数：password[str] N 原密码，原手机号停用时需要输入此字段
     * @参数：method[int] Y 修改方式 1--原手机停用 2--原手机正常
     * @参数：newTelephone[str] Y 新手机号
     * @参数：verifyCode[int] Y 验证码
     * @参数：role[int] N 角色ID 2--教师 3--学生 4--家长
     * @参数：name[str] N 学生姓名
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    function modifyTelephone()
    {
        $originalTelephone = getParameter('originalTelephone','str');
        $password = getParameter('password','str',false);
        $method = getParameter('method','str');
        $newTelephone = getParameter('newTelephone','str');
        $verifyCode = getParameter('verifyCode','str');
        $role = getParameter('role','str');
        $name = getParameter('name','str',false);
        if($newTelephone == $originalTelephone)
            $this->showMessage(500, '新旧手机号码相同，无法更改手机号');
        //新手机账号验证
        switch($role) {
            case ROLE_TEACHER:
                $info = D('Auth_teacher')->getTeacherByTel($newTelephone);
                if (!empty($info))
                    $this->showMessage(500, '新手机号对应教师已经注册');
                break;
            case ROLE_STUDENT:
                if(empty($name))
                    $this->showMessage(500, '缺少学生姓名参数');
                $info = D('Auth_student')->getStudentInfoByTelAndName($newTelephone, $name);
                if (!empty($info))
                    $this->showMessage(500, '新手机号下该学生已经注册');

                break;
            case ROLE_PARENT: $info = D('Auth_parent')->getParentInfoByTelephone($newTelephone);
                if(!empty($info))
                    $this->showMessage(500, '新手机号对应家长已经注册');
                break;
            default:$this->showMessage(500, '角色参数错误');
        }
        //验证码验证
        $redis_obj=new REDIS();
        $redis=$redis_obj->init_redis();
        $redis_key='sms_'.$newTelephone;
        $code=$redis->get($redis_key);
        if($verifyCode!=$code || !$verifyCode){
            $redis->close();
            $this->showMessage(500,'验证码错误');
        }
        $redis->setex($redis_key,1,123);
        $redis->close();

        //原始用户验证
        $updateResult = false;
        switch($role) {
            case ROLE_TEACHER:
                $info = D('Auth_teacher')->getTeacherByTel($originalTelephone);
                if (empty($info))
                    $this->showMessage(500, '原用户不存在');
                if($method == 1)
                {
                    if($info['password'] != sha1($password))
                    {
                        $this->showMessage(500, '原始密码不正确');
                    }
                }
                $updateResult = D('Auth_teacher')->updateTeacherTelephone($originalTelephone,$newTelephone);

                break;
            case ROLE_STUDENT:
                if(empty($name))
                    $this->showMessage(500, '缺少学生姓名参数');
                $info = D('Auth_student')->getStudentInfoByTelAndName($originalTelephone, $name);
                if (empty($info)) {
                    $this->showMessage(500, '用户错误');
                } else {
                    if($method == 1)
                    {
                        if($info['password'] != sha1($password))
                        {
                            $this->showMessage(500, '原始密码不正确');
                        }
                    }
                    $updateResult = D('Auth_student')->updateStudentTelephone($name,$originalTelephone,$newTelephone);
                }
                break;
            case ROLE_PARENT: $info = D('Auth_parent')->getParentInfoByTelephone($originalTelephone);
                if(empty($info))
                    $this->showMessage(500, '原用户不存在');
                if($method == 1)
                {
                    if($info['password'] != sha1($password))
                    {
                        $this->showMessage(500, '原始密码不正确');
                    }
                }
                $updateResult = D('Auth_parent')->updateParentTelephone($originalTelephone,$newTelephone);
                break;
        }
       if($updateResult === false)
           $this->showMessage(500, '服务器繁忙,请稍候再试');
        else
           $this->showMessage(200, '修改成功');
    }

    public function refreshCourseGradeTable()
    {
        $courseGradeTable = array(
            '语文'=>array('startGrade'=>1,'endGrade'=>12 ,'additional'=>'14,15,16'),
            '数学'=>array('startGrade'=>1,'endGrade'=>12 ,'additional'=>'14,15,16'),
            '英语'=>array('startGrade'=>1,'endGrade'=>12 ,'additional'=>'14,15,16'),
            '物理'=>array('startGrade'=>8,'endGrade'=>12 ,'additional'=>'15,16'),
            '化学'=>array('startGrade'=>9,'endGrade'=>12 ,'additional'=>'16'),
            '政治'=>array('startGrade'=>1,'endGrade'=>12 ,'additional'=>'14,15,16'),
            '历史'=>array('startGrade'=>7,'endGrade'=>12 ,'additional'=>'15,16'),
            '地理'=>array('startGrade'=>7,'endGrade'=>12 ,'additional'=>'15,16'),
            '生物'=>array('startGrade'=>7,'endGrade'=>12 ,'additional'=>'15,16'),
            '美术'=>array('startGrade'=>1,'endGrade'=>12 ,'additional'=>'14,15,16'),
            '体育与健康'=>array('startGrade'=>1,'endGrade'=>12 ,'additional'=>'14,15,16'),
            '科学'=>array('startGrade'=>1,'endGrade'=>9 ,'additional'=>'14,15'),
            '品德与生活'=>array('startGrade'=>1,'endGrade'=>9 ,'additional'=>'14,15'),
            '道德与法制'=>array('startGrade'=>1,'endGrade'=>12 ,'additional'=>'14,15'),
            '品德与社会'=>array('startGrade'=>1,'endGrade'=>12 ,'additional'=>'14,15,16'),
            '劳动技术'=>array('startGrade'=>1,'endGrade'=>12 ,'additional'=>'14,15,16'),
            '信息技术'=>array('startGrade'=>1,'endGrade'=>12 ,'additional'=>'14,15,16'),
            '研究性学习实践与评价'=>array('startGrade'=>1,'endGrade'=>12 ,'additional'=>'14,15,16'),
            '思想品德'=>array('startGrade'=>1,'endGrade'=>12 ,'additional'=>'14,15,16'),
            '思想政治'=>array('startGrade'=>10,'endGrade'=>12 ,'additional'=>'16'),
            '音乐'=>array('startGrade'=>1,'endGrade'=>12 ,'additional'=>'14,15,16'),
            '书法'=>array('startGrade'=>1,'endGrade'=>12 ,'additional'=>'14,15,16'),
            '通用技术'=>array('startGrade'=>1,'endGrade'=>12 ,'additional'=>'14,15,16'),
            '全学科'=>array('startGrade'=>1,'endGrade'=>12 ,'additional'=>'14,15,16'),
        );
          $result = D('Dict_course')->refreshCourseGradeInfo($courseGradeTable);
          if($result)
              $this->showMessage(200,'success');
          else
              $this->showMessage(500,'failed');
    }

    public function addFeedBack() {
        $content = getParameter('feedInfo','str');
        $contact = getParameter('contact','str');
        $role = getParameter('role','str');
        $userId = getParameter('userId','str');
        if (empty($content))
            $this->showMessage(500, '反馈内容为空');

        if (empty($contact))
            $this->showMessage(500, '反馈联系人手机号为空');

        if (empty($role))
            $this->showMessage(500, '反馈联系人角色为空');

        if (empty($userId))
            $this->showMessage(500, '反馈联系人id为空');

        $data = array(
            'feed_info' => $content,
            'contact' => $contact,
            'feed_createtime' => time(),
            'user_role' => $role,
            'user_id' => $userId,
        );
        $is_id = M('feedback')->add( $data );

        if( $is_id ) {
            $this->showMessage(200,'反馈成功,运营人员会在48小时内回复您请保持常用手机号畅通,并及时查看短信');
        } else {
            $this->showMessage(500,'failed');
        }

    }

    //获取职称列表
    public function getProfessionalList() {
        $list = C('professionalApp');
        $this->ajaxReturn(array('status' => 200, 'result' => $list));
    }
}