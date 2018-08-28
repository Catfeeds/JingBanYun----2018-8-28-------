<?php

namespace Home\Controller;

use Common\Common\SMS;

use Think\Controller;
use Common\Common\CSV;
use Common\Common\DES3;
use Common\Common\REDIS;
use Think\Verify;

define('TEACHER_IMAGE_PRODUCE_ID', 11);
define('TEACHER_FORGET_PASSWORD_PRODUCE_ID', 61);
define('ENABLE_TEACHER_STATUS', 1);
define('DISABLE_TEACHER_STATUS', 0);

class TeachController extends PublicController
{
    public $c_a = '';

    function __construct()
    {
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
        $this->c_a = CONTROLLER_NAME . "_" . ACTION_NAME;
        set_time_limit(0);
        $this->teacherId_id_online = session('teacher.id');
    }

    function material_video_image_upload()
    {
        $file = $_FILES['file'];
        $array = array();
        //前端框架每次发送一个file过来
        for ($i = 0; $i < count($file['name']); $i++) {
            $suffix = substr($file['name'][$i], strrpos($file['name'][$i], '.') + 1);
            if ($suffix == 'mp4') {
                $original_file = $file['tmp_name'][$i];
                $current_file = 'Public/tmp/' . rand(100, 10000) . '.jpg';
                //file_put_contents('Public/tmp/aa.txt','33');
                exec("/usr/local/ffmpeg/bin/ffmpeg -i " . $original_file . " -y -f image2 -ss 2.010 -t 0.001 -s 800*600 " . $current_file . " ");

                //这里判断视频是否是h264格式的
                exec("/usr/local/ffmpeg/bin/ffprobe -i " . $original_file . " 2>&1", $output, $status);
                $str = implode('', $output);
                if (strpos($str, 'h264') == true) {
                    $is_h264 = 1;
                } else {
                    $is_h264 = 0;
                }
                $temp_file['file'] = array();
                $temp_file['file']['name'][0] = rand(100, 10000) . '.jpg';
                $temp_file['file']['type'][0] = 'image/jpeg';
                $temp_file['file']['tmp_name'][0] = $current_file;
                $temp_file['file']['error'][0] = 0;
                $temp_file['file']['size'][0] = 4746507;
                $upload = new \Oss\Ossupload();// 实例化上传类
                $result = $upload->upload(3, $temp_file, 5, 0);

                @unlink($current_file);
                $data = array();
                $data['video_image'] = $result[1];
                $data['is_transition'] = $is_h264;
                return $data;
                //return $result[1];
            } else {
                return '';
            }
        }
    }

    function resource_video_image_upload()
    {//
        $file = $_FILES['file'];
        $string = '';
        $transition_str = '';

        for ($i = 0; $i < count($file['name']); $i++) {
            $suffix = substr($file['name'][$i], strrpos($file['name'][$i], '.') + 1);
            $is_h264 = 0;
            if ($suffix == 'mp4' || $suffix == 'mov' || $suffix == 'flv' || $suffix == 'wmv' || $suffix == 'avi') {
                $original_file = $file['tmp_name'][$i];
                $current_file = 'Public/tmp/' . rand(100, 10000) . '.jpg';
                //file_put_contents('Public/tmp/aa.txt','33');
                exec("/usr/local/ffmpeg/bin/ffmpeg -i " . $original_file . " -y -f image2 -ss 2.010 -t 0.001 -s 800*600 " . $current_file . " ");

                //这里判断视频是否是h264格式的
                exec("/usr/local/ffmpeg/bin/ffprobe -i " . $original_file . " 2>&1", $output);
                $str = implode('', $output);
                if (strpos($str, 'h264') == true) {
                    $is_h264 = 1;
                }

                $temp_file['file'] = array();
                $temp_file['file']['name'][0] = rand(100, 10000) . '.jpg';
                $temp_file['file']['type'][0] = 'image/jpeg';
                $temp_file['file']['tmp_name'][0] = $current_file;
                $temp_file['file']['error'][0] = 0;
                $temp_file['file']['size'][0] = 4746507;
                $upload = new \Oss\Ossupload();// 实例化上传类
                $result = $upload->upload(3, $temp_file, 3, 0);

                @unlink($current_file);
                $string = $string . $result[1] . ',';
                $transition_str = $transition_str . $is_h264 . ',';
            } else {
                $string = $string . ',';
                $transition_str = $transition_str . ',';

            }
        }
        $string = substr($string, 0, -1);
        $transition_str = substr($transition_str, 0, -1);
        $data = array();
        $data['video_image'] = $string;
        $data['is_transition'] = $transition_str;
        return $data;
        //return $string;
    }


    //拿到全部的教师资源主表
    public function resource()
    {
        $model = M('biz_resource');
        $result = $model->field('id,vid')->where("(type='video' or type='audio') and vid_fullpath=''")->select();
        $error_data = array();
        foreach ($result as $val) {
            $temp_data = $this->curl_request($val['vid']);
            if ($temp_data == -1) {
                $error_data[] = $val['id'];
            } else {
                $add_data['vid_fullpath'] = $temp_data['file'];
                $add_data['vid_image_path'] = isset($temp_data['image']) ? $temp_data['image'] : '';
                $model->where("id=" . $val['id'])->save($add_data);
                echo '运行id' . $val['id'] . "<hr>";
            }
        }

        echo "以下是保利威视返回错误码错误的id信息";
        echo "<pre>";
        print_r($error_data);
    }


    //得到全部教师资源从表
    function resourceContact()
    {
        $model = M('biz_resource');
        $contact_model = M('biz_resource_contact');
        $result = $model->join('biz_resource_contact on biz_resource_contact.biz_resource_id=biz_resource.id')
            ->field('biz_resource_contact.id,biz_resource_contact.vid')->where("(biz_resource.type='video' or  biz_resource.type='audio') and biz_resource_contact.vid_fullpath=''")->select();

        $error_data = array();
        foreach ($result as $val) {
            $temp_data = $this->curl_request($val['vid']);
            if ($temp_data == -1) {
                $error_data[] = $val['id'];
            } else {
                $add_data['vid_fullpath'] = $temp_data['file_path'];
                $add_data['vid_image_path'] = isset($temp_data['image']) ? $temp_data['image'] : '';
                $contact_model->where("id=" . $val['id'])->save($add_data);
                echo '运行id' . $val['id'] . "<hr>";
            }
        }
        echo "以下是保利威视返回错误码错误的id信息";
        echo "<pre>";
        print_r($error_data);
    }


    //拿到全部的京版资源主表
    function jbresource()
    {
        $model = M('biz_bj_resources');
        $result = $model->field('id,vid')->where("(type='video' or type='audio' ) and vid_image_path=''")->select();
        $error_data = array();
        if (empty($result)) {
            echo '没有需要执行的了';
        }
        foreach ($result as $val) {
            $temp_data = $this->curl_request($val['vid']);
            if ($temp_data == -1) {
                $error_data[] = $val['id'];
            } else {
                $add_data['vid_fullpath'] = $temp_data['file_path'];
                $add_data['vid_image_path'] = isset($temp_data['image']) ? $temp_data['image'] : '';
                $model->where("id=" . $val['id'])->save($add_data);
                echo '运行id' . $val['id'] . "<hr>";
            }
        }
        echo "以下是保利威视返回错误码错误的id信息";
        echo "<pre>";
        print_r($error_data);
    }

    //得到全部京版资源从表
    function bjresourceContact()
    {
        $model = M('biz_bj_resources');
        $contact_model = M('biz_bj_resource_contact');
        $result = $model->join("biz_bj_resource_contact on biz_bj_resource_contact.biz_bj_resource_id=biz_bj_resources.id")
            ->field('biz_bj_resource_contact.id,biz_bj_resource_contact.vid')->where("(biz_bj_resources.type='video' or biz_bj_resources.type='audio') and biz_bj_resource_contact.vid_image_path=''")->select();

        if (empty($result)) {
            echo '没有需要执行的了';
        }
        $error_data = array();
        foreach ($result as $val) {
            $temp_data = $this->curl_request($val['vid']);
            if ($temp_data == -1) {
                $error_data[] = $val['id'];
            } else {
                $add_data['vid_fullpath'] = $temp_data['file_path'];
                $add_data['vid_image_path'] = isset($temp_data['image']) ? $temp_data['image'] : '';
                $contact_model->where("id=" . $val['id'])->save($add_data);
                echo '运行id' . $val['id'] . "<hr>";
            }
        }
        echo "以下是保利威视从表返回错误码错误的id信息";    ////
        echo "<pre>";
        print_r($error_data);
    }


    /*
     * 得到全部视频习题
     */
    public function exercise_video()
    {
        $model = M('biz_exercise_library');
        $result = $model->where("mp3_vid!=''")->field('id,mp3_vid')->select();
        if (empty($result)) {
            echo '没有需要执行的了';
        }
        $error_data = array();
        foreach ($result as $val) {
            $temp_data = $this->curl_request($val['mp3_vid']);
            if ($temp_data == -1) {
                $error_data[] = $val['id'];
            } else {
                $add_data['video_file_path'] = $temp_data['file_path'];
                $model->where("id=" . $val['id'])->save($add_data);
                echo '运行id' . $val['id'] . "<hr>";
            }
        }
        echo "以下是保利威视从表返回错误码错误的id信息";    ////
        echo "<pre>";
        print_r($error_data);
    }


    function curl_request($vid)
    {
        $url = "http://v.polyv.net/uc/services/rest?method=getById";
        $post_data = array("readtoken" => "95402908-8fc2-4328-a4cf-4f49601a5812", "vid" => $vid);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        //打印获得的数据
        //print_r($output);
        $data = json_decode($output);
        $return_value = array();
        if ($data->error == 0) {
            $return_value['image'] = $data->data[0]->images[1];
            $return_value['file_path'] = $data->data[0]->mp4;
            return $return_value;
        } else {
            //出错
            return -1;
        }
    }


    public function testUpload()
    {
        var_dump($_FILES);
    }


    public function array_remove($data, $key)
    {
        if (!array_key_exists($key, $data)) {
            return $data;
        }
        $keys = array_keys($data);
        $index = array_search($key, $keys);
        if ($index !== FALSE) {
            array_splice($data, $index, 1);
        }
        return $data;
    }

    public function alert()
    {
        $this->display();
    }

    public function index()
    {
        if (!session('?teacher')) redirect(U('Index/index'));
        $this->display();
    }


    public function index1()
    {

        if (!session('?teacher')) redirect(U('Index/index'));

        $this->auth_error = $_GET['auth_error'];
        $this->laiurl = $_SERVER['HTTP_REFERER'];
        $this->auth_error = $_GET['auth_error'];
        $this->first = $_GET['first'];

        $is_session = session('teacher');
        if ($is_session != 'youke') {
            $teachinfo = M('auth_teacher')->where("id=" . session('teacher.id'))->find();
            if (empty($teachinfo['sex'])) {
                $teachinfo['sex'] = '男';
            }
            $this->assign('data', $teachinfo);
            $this->assign('tip', session('tip'));
        }
        $vipInfo = D('Account_auths')->isVipInfo(session('teacher.id'),ROLE_TEACHER);
        if($vipInfo['is_auth'] == 3 && $vipInfo['vip_day'] >=0){
            $this->assign('team_vip',1);
            $token = base64_encode($_COOKIE['PHPSESSID'].','.ROLE_TEACHER.','.session('teacher.id'));
            $key = A('Home/Auth')->getAuthKey($token);
            $pepUrl = "http://jby-szxy.mypep.cn/home/jby_auth/login?token=".$token.'&key='.$key;
            $this->assign('pep_redirect_url',$pepUrl);
        }//团体VIP且剩余天数大于0
        //
        $this->display_nocache();
        session('tip', null);
    }

    public function blank()
    {
        $this->assign('module', '模块');
        $this->assign('nav', '功能');
        $this->assign('navicon', 'black');
        $this->assign('subnav', '子功能');

        $this->display();
    }

    //个人中心
    public function me()
    {
        if (!session('?teacher')) redirect(U('Index/index'));
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        $this->assign('module', '个人中心');
        $this->assign('nav', '我');
        $this->assign('navicon', 'jiaoshifengcai');
        $this->assign('subnav', '我的资料');

        $teacherId = session('teacher.id');
        $TeacherModel = M('auth_teacher');
        $result = $TeacherModel->join('dict_schoollist on auth_teacher.school_id = dict_schoollist.id', 'left')->where("auth_teacher.id=$teacherId")
            ->field('auth_teacher.id as vid,auth_teacher.*,dict_schoollist.school_name')->find();
        if ($result['avatar'] == 'default') {
            $result['avatar'] = '';
        }

        if (empty($result['sex'])) {
            $result['sex'] = '男';
        }

        $this->assign('data', $result);

        $TeacherModelSecond = M('auth_teacher_second');


        $courseGradeIdNameSecond = $TeacherModelSecond
            ->where("auth_teacher_second.teacher_id=$teacherId")
            ->join('dict_course on auth_teacher_second.course_id=dict_course.id')
            ->join('dict_grade on auth_teacher_second.grade_id=dict_grade.id')
            ->field('auth_teacher_second.course_id,auth_teacher_second.grade_id,dict_course.course_name,dict_grade.grade,auth_teacher_second.p_type')
            ->select();

        $this->assign('gradeCourseList', $courseGradeIdNameSecond);
        $courseGradeStr = "";

        for ($i = 0; $i < sizeof($courseGradeIdNameSecond); $i++) {
            $courseGradeStr = $courseGradeStr . $courseGradeIdNameSecond[$i]['course_id'] . ',' . $courseGradeIdNameSecond[$i]['grade_id'];
            if ($i != sizeof($courseGradeIdNameSecond) - 1)
                $courseGradeStr = $courseGradeStr . ',';
        }

        $this->assign('courseGradeListStr', $courseGradeStr);

        $msgModel = M('notice_message');
        $where['role'] = 0; //teacher
        $where['user_id'] = $teacherId;
        $where['read_status'] = 0; //unread
        $count = $msgModel->where($where)->count();
        $this->assign('unreadCount', '(' . $count . ')');
        $this->assign('courses', D('Dict_course')->getCourseList());
        $this->assign('grades', D('Dict_grade')->getGradeList());
        $this->display_nocache();

    }

    //message list
    public function message()
    {
        if (!session('?teacher')) redirect(U('Index/index'));
        $teacherId = session('teacher.id');
        $msgModel = M('notice_message');
        if ($_POST) {
            //$msgid = $_POST['id'];
            $msgid = getParameter('id', 'int', false);
            $data['read_status'] = 1;//read
            $msgModel->where("id=$msgid")->save($data);
            $rs['status'] = 200;
            echo json_encode($rs);
        } else {
            $where['role'] = 0; //teacher
            $where['user_id'] = $teacherId;
            $result = $msgModel->where($where)->select();
            for ($i = 0; $i < sizeof($result); $i++)
                $result[$i]['create_at'] = date("Y-m-d H:i:s", $result[$i]['create_at']);
            $this->assign('data', $result);
            $where['read_status'] = 0; //unread
            $count = $msgModel->where($where)->count();
            $this->assign('unreadCount', '(' . $count . ')');
            $this->display();
        }
    }

    public function isMobile()
    {
        $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $useragent_commentsblock = preg_match('|.??|', $useragent, $matches) > 0 ? $matches[0] : '';
        function CheckSubstrs($substrs, $text)
        {
            $result = false;
            foreach ($substrs as $substr) {
                if (false !== strpos($text, $substr)) {
                    return true;
                }
            }
            return $result;
        }

        $mobile_os_list = array('iPad', 'Google Wireless Transcoder', 'Windows CE', 'WindowsCE', 'Symbian', 'Android', 'armv6l', 'armv5', 'Mobile', 'CentOS', 'mowser', 'AvantGo', 'Opera Mobi', 'J2ME/MIDP', 'Smartphone', 'Go.Web', 'Palm', 'iPAQ');
        $mobile_token_list = array('Android', 'Profile/MIDP', 'Configuration/CLDC-', '160×160', '176×220', '240×240', '240×320', '320×240', 'UP.Browser', 'UP.Link', 'SymbianOS', 'PalmOS', 'PocketPC', 'SonyEricsson', 'Nokia', 'BlackBerry', 'Vodafone', 'BenQ', 'Novarra-Vision', 'Iris', 'NetFront', 'HTC_', 'Xda_', 'SAMSUNG-SGH', 'Wapaka', 'DoCoMo', 'iPhone', 'iPod', 'iPad');
        $found_mobile = CheckSubstrs($mobile_os_list, $useragent_commentsblock) || CheckSubstrs($mobile_token_list, $useragent);

        if ($found_mobile) {
            return true;
        } else {
            return false;
        }
    }

    //登陆验证并跳转
    public function login()
    {
        $type = $_GET['type'];

        if ($type == 'youke' && !empty($type)) {
            $theme = "1";
            session_start();
            session('teacher', null);
            session('student', null);
            session('parent', null);
            session('teacher', 'youke');
            $auth_type_use = D('Account_auths');
            $auth_list = $auth_type_use->getUserAuthAndRole(1, 5); //游客的权限和角色

            $list = array();
            foreach ($auth_list as $ak => $av) {
                $action_map['id'] = $av;
                $row_action = M('account_node_list')->where($action_map)->find();

                if (!empty($row_action['controller_action'])) {
                    $a = explode(',', $row_action['controller_action']);
                    foreach ($a as $aak => $aav) {
                        if (!in_array($aav, $list)) { //如果不存在把权限push到普通权限中
                            array_push($list, $aav);
                        }
                    }

                }
            }

            session('auth_teacher', $list);
            $this->redirect("Teach/index$theme?typt=youke");
        }


        if ($_POST) {


            $theme = "1";
            //$check['telephone'] = $_POST['telephone'];
            //$check['password'] = sha1($_POST['password_1']);
            $check['telephone'] = getParameter('telephone', 'str', false);
            $check['password'] = sha1(getParameter('password_1', 'str', false));

            //$check['status'] = 1;
            //$check['flag'] = 1;
            //$check['lock'] = 2;

            $TeacherModel = M('auth_teacher');
            $result = $TeacherModel->where($check)->find();
            $firstLogin = $result['is_first'];
            if ($result) {
                if ($result['flag'] == 0 || $result['flag'] == -1) {
                    $this->redirect("Index/index?role=t&err=5");
                }

                session('auth_parent', null);
                session('auth_student', null);
                session('student', null);
                session('parent', null);

                /*if ($result['lock'] == 1) {
                    $this->redirect("Index/index?role=t&err=5");
                }

                if ($result['lock'] == 3) {
                    $this->redirect("Index/index?role=t&err=6");
                }

                if ($result['lock'] == 0) {
                    $this->redirect("Index/index?role=t&err=7");
                }*/

                if ($result['flag'] == DISABLE_TEACHER_STATUS) {
                    $this->redirect("Index/index?role=t&err=9");
                }
                session_start();
                session('teacher', $result);

                $login_mac_address['access_token'] = session_id();
                $TeacherModel->where("id=" . $result['id'])->save($login_mac_address);

                session('theme', $theme);

                //判断是否是vip 如果是计算天数

                $auth_type_use = D('Account_auths');
                //$auth_list = $auth_type_use->isVipAndInfo(1,5); //游客的权限和角色
                $auth_list = $auth_type_use->getAuthAndVipauth(session('teacher.id'), 2);

                session('auth_teacher', $auth_list);

                $isVipInfo = $auth_type_use->isVipInfo(session('teacher.id'), 2);

                session('teacher_vip', $isVipInfo);
                $btntheme = "primary";
                if ($theme == 2) $btntheme = "danger";
                if ($theme == 3) $btntheme = "dark";
                session('btntheme', $btntheme);
                $userip = getIPaddress();
                if (!empty($userip)) {

                    if ($userip != '127.0.0.1') {
                        $shengfen = getIPLoc_sina($userip);
                    } else {
                        $shengfen = '内网';
                    }
                    if (empty($shengfen)) {
                        $shengfen = '内网';
                    }

                    if ($shengfen != $result['login_address']) { //发送
                        $teacherLogin_address['login_address'] = $shengfen;
                        $rsave_id = $TeacherModel->where("id=" . $result['id'])->save($teacherLogin_address);
                        if ($rsave_id != false && !empty($result['login_address'])) { //发送消息，异地登陆

                            $parameters = array('msg' => array(date("Y-m-d H:i:s", time()), $shengfen . "($userip)", 'pc'), 'url' => array('type' => 0));
                            //A('Home/Message')->addPushUserMessage('EXCEPTION_LOGIN', 2, $result['id'], $parameters);
                        }
                    }
                    if ($result['login_address'] == '') {
                        $teacherLogin_address['login_address'] = $shengfen;
                        $result = $TeacherModel->where("id=" . $result['id'])->save($teacherLogin_address);
                    }
                }


                if ($firstLogin == 1) {
                    $mapfirst['id'] = $result['id'];
                    $firstdata['is_first'] = 2;
                    M('auth_teacher')->where($mapfirst)->save($firstdata);

                }

                $tip = A('Home/Common')->registered_but_no_uploadworks(session('teacher.id'), 2);
                session('tip', $tip);

                $share_par = $_REQUEST['url'];
                if (!empty($share_par)) {
                    $share_par = base64_decode($share_par);
                    $tokenValue = base64_encode(session_id().','.ROLE_TEACHER.','.session('teacher.id'));
                    $tokenString = "token=".$tokenValue;
                    $key = A('Home/Auth')->getAuthKey($tokenValue);
                    if(strpos($share_par,'?') !== false)
                        $share_par .= "&".$tokenString.'&key='.$key;
                    else
                        $share_par .= "?".$tokenString.'&key='.$key;
                    header('Location:' . $share_par);
                } else {
                    $this->redirect("Teach/index$theme");
                }
            } else {
                //$this->error('登陆失败，即将返回，请输入正确的账号及密码，或者您的账号可能被管理员锁定，如有问题，请联系400-XXXXXXXXX');
                $this->redirect("Index/index?role=t&err=1");
            }
        } else {
            session('teacher', null);
            $this->assign('REMOTE_ADDR', C('REMOTE_ADDR'));
            $this->display();
        }
    }

    //教师找回密码图形验证生成
    public function produceVerifyCodeP()
    {
        $config = array(
            'fontSize' => 20,
            'length' => 4, // 验证码位数
            'imageH' => 40);
        $verify = new Verify($config);
        $verify->entry(TEACHER_FORGET_PASSWORD_PRODUCE_ID);
    }

    function check_verify_p()
    {
        //$code=getParameter('code','str',false);
        $this->check_verify_code(1, $code, TEACHER_FORGET_PASSWORD_PRODUCE_ID);
    }


    public function forgetPassword()
    {
        if ($_POST) {
            /*$data['telephone'] = $_POST['telephone'];
            //$data['valid_code'] = $_POST['valid_code'];
            $data['password'] = sha1($_POST['password']);
            $confirmPassword = sha1($_POST['confirm_password']);*/
            $data['telephone'] = getParameter('telephone', 'str', false);
            $data['password'] = sha1(getParameter('password', 'str', false));
            $confirmPassword = sha1(getParameter('confirm_password', 'str', false));
            $validCode = getParameter('valid_code', 'int', false);

            $verifyCode = getParameter('verify_code', 'str', false);
            $nVerifyCode = getParameter('n_verify_code', 'str', false);

            if (empty($nVerifyCode)) {
                //图形验证码
                if (!$this->check_verify_code(false, $verifyCode, TEACHER_FORGET_PASSWORD_PRODUCE_ID)) {
                    $this->ajaxReturn(array('code' => -1, 'msg' => '请填写正确的图形验证码'));
                }
            }

            if ($confirmPassword <> $data['password']) {
                $this->ajaxReturn(array('code' => -1, 'msg' => '两次密码不一致'));
            }
            $TeacherModel = M('auth_teacher');
            //$CodeModel = M('misc_forgot_password_phone_code');
            $check['telephone'] = $data['telephone'];
            //$code = $CodeModel->where($check)->find();
            $redis_obj = new REDIS();
            $redis = $redis_obj->init_redis();
            $redis_key = 'sms_' . $check['telephone'];
            $code['code'] = $redis->get($redis_key);

            $Teacherinfo_id = $TeacherModel->where($check)->field('id')->find();
            if (empty($Teacherinfo_id)) {
                $this->ajaxReturn(array('code' => -1, 'msg' => '该用户不存在'));
            }

            if ($validCode && $code['code'] == $validCode) {
                $TeacherModel->where($check)->save($data);
                $redis->delete($redis_key);
                $redis->close();
                //$CodeModel->where($check)->delete();

                $parameters = array('msg' => array(date("Y-m-d H:i:s"), time()), 'url' => array('type' => 0));
                A('Home/Message')->addPushUserMessage('PASSWORD_MODIFY', 2, $Teacherinfo_id['id'], $parameters);

                $share_par = getParameter('url', 'str', false);
                if (!empty($share_par)) {
                    $share_par = base64_decode($share_par);
                    $this->ajaxReturn(array('code' => 2, 'msg' => '重置成功', 'url' => $share_par));
                } else {
                    $this->ajaxReturn(array('code' => 0, 'msg' => '重置成功'));
                }

            } else {
                $this->ajaxReturn(array('code' => -1, 'msg' => '验证码错误'));
            }
        } else {
            $err = $_GET['err'];
            $this->assign('err', $err);

            $share_string = '';
            $share_url = getParameter('url', 'str', false);
            if (!empty($share_url)) {
                $share_string = 'url=' . $share_url;
            }
            $this->assign('share_str', $share_string);

            $this->display();
        }
    }

    //发送找回验证码
    public function sendForgetPasswordPhoneCode()
    {
        $redis_obj = new REDIS();
        $redis = $redis_obj->init_redis();

        if (!$this->isMobile()) {
            //判断不是手机,不是登陆后个人中心发送的就判断
            $n_verify_code = getParameter('n_verify_code', 'str', false);
            if (empty($n_verify_code)) {
                //图形验证码
                $code = getParameter('code', 'str', false);
                if (!$this->check_verify_code(false, $code, TEACHER_FORGET_PASSWORD_PRODUCE_ID)) {
                    $this->showjson(-5, '请填写正确的图形验证码');
                }
            }
            $sendSmsFunction = 'templateSMS';
        } else {
            $sendSmsFunction = 'newTemplateSMS';
        }

        //$telephone = $_REQUEST['telephone'];
        $telephone = getParameter('telephone', 'str', false);
        if (empty($telephone) || strlen($telephone) != 11) {
            $this->showjson(-4, '手机号码格式错误');
        }

        $check['telephone'] = $telephone;

        $TeacherModel = M('auth_teacher');
        $teacher = $TeacherModel->where($check)->find();

        $out['status'] = 0;
        $out['msg'] = '';
        $out['content'] = '';

        if (empty($teacher)) {
            $this->showjson(-1, '该用户不存在');
        }

        $redis_key = 'sms_' . $telephone;
        $redis_exists = $redis->exists('p' . $redis_key);
        if ($redis_exists) {
            $this->showjson(-1, '60秒内只能发送一次验证码');
        }


        $randcode = rand(10000, 99999);
        $smsapi = new SMS();
        $ret = $smsapi->$sendSmsFunction($telephone, '找回密码,' . $randcode, 'json');
        if ($ret['status'] == false ||
            $ret < 0) {
            $this->showjson(-2, '验证码发送失败');
            return;
        }

        $redis->setex($redis_key, 300, $randcode);
        $redis->setex('p' . $redis_key, 62, $randcode);
        $redis->close();

        $this->showjson(0, 'success');
        exit();
    }


    //生成验证码
    function produceVerifyCode()
    {
        $config = array(
            'fontSize' => 20,
            'length' => 4, // 验证码位数
            'imageH' => 40);
        $verify = new Verify($config);
        $verify->entry(TEACHER_IMAGE_PRODUCE_ID);
    }

    function check_verify()
    {
        //$code=getParameter('code','str',false);
        $this->check_verify_code(1, $code, TEACHER_IMAGE_PRODUCE_ID);
    }

    //检查验证码
    public function check_verify_code($show_flag = 1, $code = '', $image_produce_id)
    {
        $config = array(
            'reset' => false
        );
        $verify = new \Think\Verify($config);
        if ($show_flag == 1) {
            $verify_code = getParameter('code', 'str');
            $res = $verify->check($verify_code, $image_produce_id);
            if ($res) {
                $this->showjson(200);
            } else {
                $this->showjson(401);
            }
        } else {
            $verify_code = $code;
            $res = $verify->check($verify_code, $image_produce_id);
            return $res;
        }
    }

    //注册
    public function register()
    {


        if ($_POST) {

            $vip_config = C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');
            /*
            $check['telephone'] = $_POST['telephone'];

            $data['telephone'] = remove_xss($_POST['telephone']);
            $data['password'] = sha1($_POST['password']);
            $data['valid_code'] = $_POST['valid_code'];
            $data['brief_intro'] = $_POST['brief_intro'];
            $data['sex'] = $_POST['sex'];
            $data['school_id'] = $_POST['school_id'];
            //for primary course grade
            $data['course_id'] = substr($_POST['coursegrade'],0,1);
            $data['grade_id']  = substr($_POST['coursegrade'],2,1);
            $data['role'] = $_POST['role'];
            $data['name'] = remove_xss($_POST['name']);
            $data['email'] = remove_xss($_POST['email']);
            $data['confirm_password'] = sha1($_POST['confirm_password']);
            */
            $check['telephone'] = getParameter('telephone', 'str', false);

            $data['telephone'] = $check['telephone'];
            $data['password'] = sha1(getParameter('password', 'str', false));
            $datatemp['valid_code'] = getParameter('valid_code', 'int', false);
            $data['sex'] = $_POST['sex'];
            $data['brief_intro'] = getParameter('brief_intro', 'str', false);
            $data['sex'] = getParameter('sex', 'str', false);
            $data['school_id'] = getParameter('school_id', 'int', false);
            //$data['course_id'] = substr(getParameter('coursegrade','str',false),0,1);
            //$data['grade_id'] = substr(getParameter('coursegrade','str',false),2,1);
            $data['role'] = getParameter('role', 'int', false);
            $data['name'] = getParameter('name', 'str', false);
            $data['email'] = getParameter('email', 'str', false);
            $dataTemp['confirm_password'] = sha1(getParameter('confirm_password', 'str', false));

            $data['create_at'] = time();
            $data['update_at'] = time();
            $data['lock'] = 2;
            $data['access_token'] = "1265poefe3rffe";//todo


            if (session('from_param')) {
                $data['source'] = session('from_param');
            }

            if (empty($data['telephone']) || empty($data['password']) || empty($data['name'])) {
                $this->showMessage(500, '请填写完整信息', array());
            }

            if ($dataTemp['confirm_password'] == '') {
                $this->showMessage(500, '密码不能为空', array());
            }

            if ($dataTemp['confirm_password'] != $data['password']) {
                $this->showMessage(500, '密码与确认密码不一致', array());
            }

            $shcool_model = M('dict_schoollist');
            $school_info = $shcool_model->where('id=' . $data['school_id'])->find();
            if (empty($school_info)) {
                $this->showMessage(500, '学校信息不存在', array());
            }

            $ref = session('rid');
            if (!empty($ref)) {
                $refmap['a_id'] = session('rid');
                $this->addActivityUser($refmap);
            }

            $TeacherModel = M('auth_teacher');
            $result = $TeacherModel->where($check)->find();//判断是否已经注册

            if (empty($result)) {
                $CodeModel = M('misc_register_phone_validcode');

                //$code = $CodeModel->where($check)->find();
                $redis_obj = new REDIS();
                $redis = $redis_obj->init_redis();
                $redis_key = 'sms_' . $check['telephone'];
                $code['code'] = $redis->get($redis_key);

                if ($code['code'] == $datatemp['valid_code'] && $datatemp['valid_code']) {
                    $secondModel = M('auth_teacher_second');
                    $TeacherModel->startTrans();

                    $id = $TeacherModel->add($data);
                    if ($vip_config && $vip_config <= 3) {
                        give_new_vip_operation(2, $vip_config, $id, $data['school_id']);
                    }
                    /*
                    if($vip_config && $vip_config<=3){
                        $vipdata = array(
                            'user_id' => $id,
                            'role_id' => 2,
                            'auth_id' => 4,
                            'auth_start_time' => time(),
                            'auth_end_time' => time()+3600*24*30*3,
                            'timetype' => 1,
                        );

                        if($vip_config==1){
                            //赠送90天vip
                           $vipdata['auth_id']=4;

                        }elseif($vip_config==2){
                            //普通权限
                            $vipdata['auth_id']=2;
                            $vipdata['auth_start_time']=0;
                            $vipdata['auth_end_time']=0;
                            $vipdata['timetype']=0;

                        }elseif($vip_config==3){
                            //根据学校的权限来赋予教师的权限
                            if ($school_info['user_auth'] == 3 && time() >= $school_info['auth_start_time'] && time() < $school_info['auth_end_time'] ) {
                                //学校的权限给予教师
                                $vipdata['timetype']=$school_info['timetype'];
                                $vipdata['auth_id']=3;
                                $vipdata['auth_end_time']=$school_info['auth_end_time'];

                            } else {
                                //普通权限
                                $vipdata['auth_id']=2;
                                $vipdata['auth_start_time']=0;
                                $vipdata['auth_end_time']=0;
                                $vipdata['timetype']=0;
                            }
                        }
                        $auth_type_use = D('Account_auths');
                        $auth_list = $auth_type_use->addUserVip( $vipdata );
                    }*/
                    //add secondary data

                    $secondData['teacher_id'] = $id;
                    $secondStr = substr($_POST['coursegrade'], 0);
                    if(empty($secondStr)){
                        $TeacherModel->rollback();
                        $this->showMessage(500,'请选择学科年级',array());
                    }
                    $pieces = explode(",", $secondStr);
                    $count = count($pieces);
                    for ($i = 0; $i < $count; $i += 2) {
                        $secondData['course_id'] = $pieces[$i];
                        $secondData['grade_id'] = $pieces[$i + 1];
                        $secondModel->add($secondData);
                    }
                    $TeacherModel->commit();
                    $redis->delete($redis_key);
                    $redis->close();
                    //$CodeModel->where($check)->delete();
                    $smsapi = new SMS();
                    $smsapi->newUserNotice($check['telephone']);
                    $Message = new \Home\Controller\MessageController();
                    $parameters = array('msg' => array(), 'url' => array('type' => 0));
                    $Message->addPushUserMessage('REG_SUCCESS', 2, $id, $parameters);

                    $share_par = getParameter('url', 'str', false);
                    if (!empty($share_par)) {
                        $share_par = base64_decode($share_par);
                        //写session
                        $theme = "1";
                        session('auth_parent', null);
                        session('auth_student', null);
                        session('student', null);
                        session('parent', null);

                        $TeacherModel = M('auth_teacher');
                        $check['password'] = $data['password'];
                        $result = $TeacherModel->where($check)->find();
                        session('teacher', $result);
                        session('theme', $theme);

                        $btntheme = "primary";
                        session('btntheme', $btntheme);

                        $auth_type_use = D('Account_auths');
                        $auth_list = $auth_type_use->getAuthAndVipauth(session('teacher.id'), ROLE_TEACHER);
                        session('auth_teacher', $auth_list);

                        $isVipInfo = $auth_type_use->isVipInfo(session('teacher.id'), ROLE_TEACHER);
                        session('teacher_vip', $isVipInfo);

                        $userip = getIPaddress();
                        if ($userip != '127.0.0.1') {
                            $shengfen = getIPLoc_sina($userip);
                        } else {
                            $shengfen = '内网';
                        }
                        if (empty($shengfen)) {
                            $shengfen = '内网';
                        }

                        $teacherLogin_address['login_address'] = $shengfen;
                        $result = $TeacherModel->where("id=" . $result['id'])->save($teacherLogin_address);


                        $this->showMessage(201, 'success', array('telephone' => $check['telephone'], 'url' => $share_par));
                    } else {

                        $this->showMessage(200, 'success', array('telephone' => $check['telephone']));
                    }
                } else {
                    $this->showMessage(500, '验证码错误', array());
                }
            } else {
                $this->showMessage(500, '该手机号已经被注册', array());
            }
        } else {

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            setSourceParameter();

            $this->display();
        }
    }


    //京版活动带来的注册用户
    public function addActivityUser($data)
    {
        $data['type'] = 2;
        $data['user_count'] = 1;
        M('activity_register_user')->add($data);

    }

    //完善资料
    function perfectingformation()
    {
        if (!empty($_POST)) {
            if (!session('?teacher')) {
                $this->showjson(500, '请完成登录');
            }
            $id = session('teacher.id');
            $course_model = D('Dict_course');
            $grade_model = D('Dict_grade');

            $role = getParameter('role', 'int');
            $school_id = getParameter('school_id', 'int');
            $secondStr = substr($_POST['coursegrade'], 0);
            if ($secondStr == '') {
                $this->showjson(500, '异常访问');
            }
            $pieces = explode(",", $secondStr);
            $count = count($pieces);
            if ($count % 2 != 0) {
                $this->showjson(500, '异常访问');
            }
            $teacher_model = M('auth_teacher');
            $secondModel = M('auth_teacher_second');
            $secondModel->startTrans();
            $secondData['teacher_id'] = $id;
            for ($i = 0; $i < $count; $i += 2) {
                $secondData['course_id'] = $pieces[$i];
                $secondData['grade_id'] = $pieces[$i + 1];
                $course_result = $course_model->getCourseInfo($secondData['course_id']);
                $grade_result = $grade_model->getGradeInfo($secondData['grade_id']);
                if (empty($course_result)) {
                    $secondModel->rollback();
                    break;
                }
                if (empty($grade_result)) {
                    $secondModel->rollback();
                    break;
                }
                $secondModel->add($secondData);
            }
            //$school_id
            $where['id'] = $id;
            $data['school_id'] = $school_id;
            if ($teacher_model->where($where)->save($data) === false) {
                $secondModel->rollback();
                $this->showjson(500, '数据修改失败');
            }
            $secondModel->commit();
            $this->showJson(200);
        } else {
            $this->showjson(500, '异常访问');
        }
    }

    //获得所有年级
    function getGradeList()
    {
        $result = D('Dict_grade')->getGradeList();
        $this->showjson(200, '', $result);
    }

    //获得所有学科
    function getCourseList()
    {
        $result = D('Dict_course')->getCourseList();
        $this->showjson(200, '', $result);
    }

    public function ossUploadAvatar()
    {

        $id = session('teacher.id');
        if ($_POST['img']) {
            saveTempAvatar($_POST['img']);
        }

        $img_path = $this->upload_file(1);
        $img_path_url = json_decode($img_path, true);
        $Model = M('auth_teacher');
        $c1['id'] = $id;
        $data['avatar'] = $img_path_url['res'];
        $Model->where($c1)->save($data);
        $rs['status'] = 1;
        $rs['src'] = C('oss_path') . $img_path_url['res'];
        echo json_encode($rs);
    }

    // 上传头像
    public function uploadAvatar()
    {
        //$path = "./Uploads/Avatars/";
        $id = session('teacher.id');
        $filename_id = $id . "_t.jpg";

        $post_input = 'php://input';
        $save_path = "./Uploads/Avatars";
        $postdata = file_get_contents($post_input);

        if (isset($postdata) && strlen($postdata) > 0) {
            $filename = $save_path . '/' . $filename_id;
            $handle = fopen($filename, 'w+');
            fwrite($handle, $postdata);
            fclose($handle);
            if (is_file($filename)) {
                //echo 'Image data save successed,file:' . $filename;
                //exit();
            } else {
                die ('Image upload error!');
            }
        } else {
            die ('Image data not detected!');
        }

        $Model = M('auth_teacher');
        $c1['id'] = $id;

        $data['avatar'] = $id;
        $Model->where($c1)->save($data);

        $rs['status'] = 1;

        echo json_encode($rs);
    }

    public function getProvince()
    {
        $Model = M('dict_citydistrict');
        $result = $Model
            ->where('level=1')
            ->field('id,name')
            ->select();
        $this->ajaxReturn($result);

    }

    public function getCityByProvince()
    {
        if ($_GET) {
            $zhiXiaIdArray = array(1, 2, 9, 22);
            //$provinceId = $_GET['id'];
            $provinceId = getParameter('id', 'int');
            $where = "";
            if (in_array($provinceId, $zhiXiaIdArray)) {
                $where = 'level=1 and ' . 'id=' . $provinceId;
            } else {
                $where = 'level=2 and ' . 'upid=' . $provinceId;
            }
            $Model = M('dict_citydistrict');
            $result = $Model
                ->where($where)
                ->order('name asc')
                ->field('id,name')
                ->select();
            $this->ajaxReturn($result);
        }
    }

    public function getDistrictByCity()
    {
        if ($_GET) {
            //$cityId = $_GET['id'];
            $cityId = getParameter('id', 'int');
            $Model = M('dict_citydistrict');
            $result = $Model
                ->where("upid=" . $cityId)
                ->order('name asc')
                ->field('id,name')
                ->select();
            $this->ajaxReturn($result);
        }
    }

    public function getSchoolByDistrict()
    {
        if ($_GET) {
            $districtId = getParameter('id', 'int');
            $Model = M('dict_schoollist');
            $result = $Model
                ->where("district_id=" . $districtId . " AND id <> 2000")
                ->order('school_name asc')
                ->field('id,school_name as name,is_create_administartor hasAdmin')
                ->select();
            $this->ajaxReturn($result);
        }
    }

    /*
    public function getSchoolByName()
    {
        //$name = $_GET['name'];
        $name=getParameter('name','str');
        $map['school_name'] = array('like', "%$name%");

        $Model = M('dict_schoollist');
        $result = $Model
            ->field('id,school_name,stage,province')
            ->where($map)
            ->order('school_name asc')
            ->page(1, 50)->select();

        $this->ajaxReturn($result);
    }
    */

    //发送注册验证码
    public function sendRegisterPhoneCode()
    {

        if (!$this->isMobile()) {
            //判断图形验证
            $verify_code = getParameter('code', 'str', false);
            if (!$this->check_verify_code(false, $verify_code, TEACHER_IMAGE_PRODUCE_ID)) {
                $this->showjson(-5, '图形验证码错误');
            }
            $sendSmsFunction = 'templateSMS';
        } else {
            $sendSmsFunction = 'newTemplateSMS';
        }

        $smsGap = 60; //发送间隔60S
        //$telephone = $_REQUEST['telephone'];
        $telephone = getParameter('telephone', 'str', false);
        if (empty($telephone) || strlen($telephone) != 11) {
            $this->showjson(-4, '手机号码格式错误');
        }

        $check['telephone'] = $telephone;

        $TeacherModel = M('auth_teacher');
        $teacher = $TeacherModel->where($check)->find();

        $out['status'] = 0;
        $out['msg'] = '';
        $out['content'] = '';

        if (!empty($teacher)) {
            $this->showjson(-1, '用户已存在');
        }

        $randcode = rand(10000, 99999);
        $smsapi = new SMS();

        $ret = $smsapi->$sendSmsFunction($telephone, '注册教师,' . $randcode, 'json');
        if ($ret['status'] == false || $ret < 0) {
            $this->showjson(-2, '验证码发送失败');
            \Think\Log::write('短信发送错误,错误信息:' . json_encode($ret), 'ERR/SMS.ERR');
            return;
        }

        $redis_obj = new REDIS();
        $redis = $redis_obj->init_redis();
        $redis_key = 'sms_' . $telephone;
        $redis->setex($redis_key, 300, $randcode);
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
        A('Home/Auth')->pepSendLogout($_COOKIE['PHPSESSID'],ROLE_TEACHER,session('teacher.id'));
        session('teacher', null);
        $this->redirect("Index/index");
    }

    //OA首页
    public function oa()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '励耘圈');
        $this->assign('nav', '教育信息发布系统');
        $this->assign('subnav', '我收到的信息');

        //$page = $_GET['page'];
        $page = getParameter('page', 'int', false);
        if (empty($page)) $page = 1;

        $Model = M('oa_message');
        $result = $Model
            ->join('LEFT JOIN oa_message_reply on  oa_message.id=oa_message_reply.message_id')
            ->where('oa_message.status=2 and oa_message.group_id=1')
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
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('nav', '教育信息发布系统');
        $this->assign('subnav', '我的回执');

        //$page = $_GET['page'];
        $page = getParameter('page', 'int', false);
        if (empty($page)) $page = 1;

        $Model = M('oa_message');
        $result = $Model->where('status=2')
            ->order('create_at desc')
            ->page($page, 50)
            ->select();

        $this->assign('list', $result);

        $this->display();
    }

    //我的发布
    public function oaMyPublished()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '励耘圈');
        $this->assign('nav', '教育信息发布系统');
        $this->assign('subnav', '我发布的信息');

        //$page = $_GET['page'];
        $page = getParameter('page', 'int', false);
        if (empty($page)) $page = 1;

        $Model = M('oa_message');
        $result = $Model->where('status=2')
            ->where('publisher_id=' . session('teacher.id'))
            ->order('create_at desc')
            ->page($page, 50)
            ->select();

        $this->assign('list', $result);

        $this->display();
    }

    //发布消息
    public function oaPublish()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if ($_POST) {
            /*
            $data['is_need_receipt'] = $_POST['is_need_receipt'];
            $data['title'] = $_POST['title'];
            $data['content'] = $_POST['content'];
            $data['brief_content'] = $_POST['content'];
            */
            $data['is_need_receipt'] = getParameter('is_need_receipt', 'int', false);
            $data['title'] = getParameter('title', 'str', false);
            $data['content'] = getParameter('content', 'str', false);
            $data['brief_content'] = $data['content'];

            $data['publisher_id'] = session('teacher.id');
            $data['publisher'] = session('teacher.name');
            $data['group_id'] = 2;


            $data['create_at'] = time();
            $data['update_at'] = time();

            $ResourceModel = M('oa_message');

            $ResourceModel->add($data);

            $this->redirect("Teach/oaMyPublished");

        } else {
            $this->assign('module', '励耘圈');
            $this->assign('nav', '教育信息发布系统');
            $this->assign('subnav', '发布信息');

            $Model = M('biz_class');
            $classes = $Model->order('name asc')->select();
            $this->assign('classes', $classes);

            $this->display();
        }
    }

    //消息详情
    public function oaMessageDetails()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '励耘圈');
        $this->assign('nav', '教育信息发布系统');
        $this->assign('subnav', '信息详情');

        //$id = $_GET['id'];
        $id = getParameter('id', 'int', false);

        $Model = M('oa_message');
        $result = $Model->where("id=$id")
            ->find();

        $this->assign('data', $result);

        $this->display();
    }

    //消息回执
    public function oaReply()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$messageId = $_GET['message_id'];
        $messageId = getParameter('message_id', 'int', false);
        $Model = M('oa_message_reply');

        $data['message_id'] = $messageId;
        $data['reader_id'] = session('teacher.id');;
        $data['reader'] = session('teacher.name');
        $data['reply_at'] = time();
        $data['content'] = '';
        $data['reader_type'] = 1;

        $Model->add($data);

        $this->ajaxReturn('success');
    }

    //专家资讯信息列表
    public function expertInformationList()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $isAuth = $this->isAuth($this->c_a);

        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }


        $this->assign('module', '励耘圈');
        $this->assign('nav', '专家资讯');
        $this->assign('subnav', '资讯信息');
        $this->assign('navicon', 'zhuanjiazixun');

        $Model = M('social_expert_information');
        $where['status'] = 2;
        $keyword = $_GET['keyword'];

        if ($keyword)
            $where['title'] = array("like", "%" . $keyword . "%");
        $count = $Model->join('auth_admin on social_expert_information.publisher_id = auth_admin.id')->where($where)->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        $result = $Model->where($where)
            ->join('auth_admin on social_expert_information.publisher_id = auth_admin.id')
            ->order('create_at desc')
            ->field('social_expert_information.*,auth_admin.nickname')
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
        $this->assign('keyword', $keyword);
        $this->display();
    }

    //专家资讯信息详情
    public function expertInformationDetails($id)
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '励耘圈');
        $this->assign('nav', '专家资讯');
        $this->assign('subnav', '资讯详情');
        $this->assign('navicon', 'zhuanjiazixun');

        $Model = M('social_expert_information');
        $AdminModel = M('auth_admin');
        //$check['id'] = $id;
        $check['id'] = intval($id);

        $result = $Model->where($check)->find();

        $check['id'] = $result['publisher_id'];
        $info = $AdminModel->where($check)->find();

        $result['publisher'] = $info['nickname'];

        $this->assign('data', $result);

        $this->display();
    }

    /*oss上传携带参数
    * @dir_par 1京版 2备课   3教师    4老师报名活动的附件
    */
    public function upload_activity_file()
    {
        //$activity_id=$_GET['activity_id'];
        $activity_id = getParameter('activity_id', 'int', false);

        $teacher_id = session('teacher.id');
        //这里去查询区 学校 老师姓名
        $model = M('dict_schoollist');
        $result = $model->where("auth_teacher.id=" . $teacher_id)->join("auth_teacher on dict_schoollist.id=auth_teacher.school_id")
            ->join("dict_citydistrict on dict_citydistrict.id=dict_schoollist.district_id")
            ->field("auth_teacher.name teacher_name,dict_schoollist.school_name,dict_citydistrict.name district")
            ->find();
        //这里判断用于.
        if (empty($result)) {
            $arr['msg'] = '上传失败';
            die;
        }
        $file_path = $result['district'] . '-' . $result['school_name'] . '-' . $result['teacher_name'] . '-' . rand(100, 10000);
        $path = $activity_id . '/' . $teacher_id . '/' . $file_path;

        $upload = new \Oss\Ossupload();// 实例化上传类
        $result = $upload->upload(3, $_FILES, 4, $path); //1 pic 2//
        $returnArray = explode(",", $result[1]);
        $uploadOK = 1;
        for ($i = 0; $i < sizeof($returnArray); $i++) {
            if ($returnArray[$i] == "") {
                $uploadOK = 0;
                break;
            }
        }
        if ($uploadOK == 0) {
            $arr['msg'] = '上传失败';
            $arr['code'] = -1;
        } else {
            $arr['msg'] = '上传成功';
            $arr['code'] = 0;
        }
        $arr['res'] = $result[1];
        $arr['message'] = $result[2];
        echo json_encode($arr);
    }

    //精通活动详情
    public function activities()
    {
        redirect(U('Index/index'));
    }

    //老版活动报名
    public function reportActivity()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$data['activity_id'] = $_POST['activity_id'];
        $data['activity_id'] = getParameter('activity_id', 'int');

        $data['user_id'] = session('teacher.id');
        $data['user_name'] = session('teacher.name');
        $data['register_at'] = time();
        $data['user_type'] = 1;
        //$data['register_info'] = $_POST['register_info'];
        //$data['file_path'] = $_POST['file_path'];
        $data['register_info'] = getParameter('register_info', 'str', false);
        $data['file_path'] = getParameter('file_path', 'str', false);

        if (D('Social_activity')->regActivity($data)) {
            $arr['msg'] = '报名成功';
            $arr['code'] = 0;
        } else {
            $arr['msg'] = '报名失败';
            $arr['code'] = -1;
        }
        echo json_encode($arr);
    }


    //赞一个京版活动
    public function zanActivity()
    {
        $teacherId = session('teacher.id');

        $typerole = session('teacher');
        if ($typerole == 'youke') {
            $this->ajaxReturn("youke");
        }

        if (empty($teacherId)) {
            //$teacherId = $_GET['user_id'];
            $teacherId = getParameter('user_id', 'int');
            if (empty($teacherId)) {
                redirect(U('Index/index'));
            }
        }
        //$social_activity_id = $_GET['social_activity_id'];
        $social_activity_id = getParameter('social_activity_id', 'int');

        $ZanModel = M('social_activity_zan');
        $zanData['social_activity_id'] = $social_activity_id;
        $zanData['user_id'] = $teacherId;
        $zanData['user_type'] = 1;


        $existed = $ZanModel->where($zanData)->find();
        if (empty($existed)) {
            $zanData['zan_time'] = time();
            $ZanModel->add($zanData);

            $Model = M('social_activity');
            $Model->where("id=$social_activity_id")->setInc('zan_count', 1);

            $this->ajaxReturn("success");
        } else {
            $ZanModel->where("social_activity_id=$social_activity_id and user_id=$teacherId and user_type=1")->delete();
            $Model = M('social_activity');
            $Model->where("id=$social_activity_id")->setDec('zan_count', 1);

            $this->ajaxReturn("已经取消点赞");
        }
    }

    //收藏一个京版活动
    public function favorActivity()
    {
        $teacherId = session('teacher.id');

        $typerole = session('teacher');
        if ($typerole == 'youke') {
            $this->ajaxReturn("youke");
        }

        if (empty($teacherId)) {
            //$teacherId = $_GET['user_id'];
            $teacherId = getParameter('user_id', 'int');
            if (empty($teacherId)) {
                redirect(U('Index/index'));
            }
        }
        //$social_activity_id = $_GET['social_activity_id'];
        $social_activity_id = getParameter('social_activity_id', 'int');

        $FavorModel = M('social_activity_favor');
        $favorData['social_activity_id'] = $social_activity_id;
        $favorData['user_id'] = $teacherId;
        $favorData['user_type'] = 1;

        $existed = $FavorModel->where($favorData)->find();
        if (empty($existed)) {
            $favorData['favor_time'] = time();
            $FavorModel->add($favorData);

            $Model = M('social_activity');
            $Model->where("id=$social_activity_id")->setInc('favor_count', 1);

            $this->ajaxReturn("success");
        } else {
            $FavorModel->where("social_activity_id=$social_activity_id and user_id=$teacherId and user_type=1")->delete();
            $Model = M('social_activity');
            $Model->where("id=$social_activity_id")->setDec('favor_count', 1);

            $this->ajaxReturn("已经取消收藏");
        }
    }


    //教师风采
    public function teacherStyle()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$id = $_GET['auth_id'];
        $id = getParameter('auth_id', 'int', false);

        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        $this->assign('module', '励耘圈');
        $this->assign('nav', '教师风采');
        $this->assign('subnav', '教师风采榜');
        $this->assign('navicon', 'jiaoshifengcai');

        //$courseId = $_REQUEST['course_id'];
        $courseId = getParameter('course_id', 'int', false);
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
            ->page(1, 15)->select();
        if (1 == DISPLAY_TEACHERSTYLE) {
            $this->assign('list', $result);
        } else {
            if (session('teacher.telephone') == '15311410001')
                $this->assign('list', $result);
            else
                $this->assign('list', array());
        }
        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        $this->assign('courses', $courses);
        //$this->assign('courses', array());

        $this->display();
    }


    ///////////////////教学+

    //发布资源
    public function publishResource()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if ($_POST) {
            /*$data['name'] = remove_xss($_POST['name']);
            $data['description'] = remove_xss($_POST['description']);
            $data['course_id'] = $_POST['course_id'];
            $data['grade_id'] = $_POST['grade_id'];
            $data['textbook_id'] = $_POST['textbook_id'];

            $data['type'] = strtolower($_POST['real_type']);
            */
            $data['name'] = getParameter('name', 'str', false);
            $data['description'] = getParameter('description', 'str', false);
            $data['course_id'] = getParameter('course_id', 'int', false);
            $data['grade_id'] = getParameter('grade_id', 'int', false);
            $data['textbook_id'] = getParameter('textbook_id', 'int', false);

            //$data['type'] = strtolower($_POST['real_type']);
            $data['type'] = strtolower(getParameter('real_type', 'str', false));

            $data['status'] = 1;
            $data['teacher_id'] = session('teacher.id');
            $data['teacher_name'] = session('teacher.name');

            $data['create_at'] = time();


            //求出textbook是上册下册还是全一册
            $textbook_model = M('biz_textbook');
            if ($data['textbook_id'] != -1) {
                $textbook_result = $textbook_model->where('id=' . $data['textbook_id'])->field('id,school_term')->find();
                if (empty($textbook_result)) {
                    redirect(U('Index/systemError'));
                }
            }
            $data['school_term_id'] = $textbook_result['school_term'];
            //$data['textbook_id']

            //$contact['file_path']=$_POST['unique_string'];
            $contact['file_path'] = getParameter('unique_string', 'str', false);
            $file_array = explode(',', $contact['file_path']);


            if ($data['type'] == 'audio' || $data['type'] == 'video') {
                /*$contact['vid_width']=$_POST['vid_width'];
                $contact['vid_time']=$_POST['vid_time'];
                $contact['vid']=$_POST['unique_vid'];
                $contact['vid_fullpath']=$_POST['vid_fullpath'];
                $contact['vid_image_path']=$_POST['vid_image_path'];
                $contact['vid_transition']=$_POST['vid_transition'];
                */

                $contact['vid_width'] = getParameter('vid_width', 'str', false);
                $contact['vid_time'] = getParameter('vid_time', 'str', false);
                $contact['vid'] = getParameter('unique_vid', 'str', false);
                $contact['vid_fullpath'] = getParameter('vid_fullpath', 'str', false);
                $contact['vid_image_path'] = getParameter('vid_image_path', 'str', false);
                $contact['vid_transition'] = getParameter('vid_transition', 'str', false);

                $vid_width_array = explode(',', $contact['vid_width']);
                $vid_time_array = explode(',', $contact['vid_time']);
                $vid_array = explode(',', $contact['vid']);
                $vid_fullpath_array = explode(',', $contact['vid_fullpath']);
                $vid_image_path_array = explode(',', $contact['vid_image_path']);
                $vid_transition_array = explode(',', $contact['vid_transition']);

                $data['vid'] = $vid_array[0];
                $data['playerwidth'] = $vid_width_array[0];
                $data['playerduration'] = $vid_time_array[0];
                $data['vid_fullpath'] = $vid_fullpath_array[0];
                $data['vid_image_path'] = $vid_image_path_array[0];
            }
            if ($data['type'] == 'image') {
                $arr = explode('.', $file_array[0]);
                $watermark_image = $arr[count($arr) - 2] . '_w' . '.' . $arr[count($arr) - 1];
                $data['file_path'] = $watermark_image;
            } else {
                $data['file_path'] = $file_array[0];
            }
            /*$filter = new \Org\Util\SensitiveFilter;
            if (!$filter::filter($data['name'])  || !$filter::filter($data['description'])) {
                //标记敏感词汇
                $data['has_sensitive_words'] = 1;
            }*/
            $filter = D('Sensitive_words');
            if (!$filter->filter($data['name']) || !$filter->filter($data['description'])) {
                //标记敏感词汇
                $data['has_sensitive_words'] = 1;
            }
            //得到年级和学科
            $grade_model = M('dict_grade');
            $course_model = M('dict_course');
            $where['id'] = $data['grade_id'];
            $grade_result = $grade_model->where($where)->find();
            $where['id'] = $data['course_id'];
            $course_result = $course_model->where($where)->find();
            if (empty($grade_result)) {
                redirect(U('Index/systemError'));
            }
            if (empty($course_result)) {
                redirect(U('Index/systemError'));
            }
            $textbook_result = $textbook_model->where('id=' . $data['textbook_id'])->field('id,school_term,name')->find();
            if (empty($textbook_result)) {
                redirect(U('Index/systemError'));
            }
            $school_model = M('dict_schoollist');
            $school_result = $school_model->where('id=' . session('teacher.school_id'))->find();
            if (empty($school_result)) {
                $school_result['school_name'] = '';
            }

            //得到许多字段的信息
            $data['resource_info'] = $data['name'] . ',' . $data['description'] . ',' . $data['teacher_name'] . ',' . $grade_result['grade'] . ',' . $course_result['course_name'] . ',' . $textbook_result['name'] . ',' . $school_result['school_name'];

            $ResourceModel = M('biz_resource');
            $contact_data['biz_resource_id'] = $ResourceModel->add($data);


            $resource_contact_model = M('biz_resource_contact');
            if ($data['type'] == 'audio' || $data['type'] == 'video') {
                for ($i = 0; $i < count($file_array); $i++) {

                    $contact_data['vid'] = $vid_array[$i];
                    $contact_data['playerwidth'] = $vid_width_array[$i];
                    $contact_data['playerduration'] = $vid_time_array[$i];
                    $contact_data['resource_path'] = $file_array[$i];
                    $contact_data['vid_fullpath'] = $vid_fullpath_array[$i];
                    $contact_data['vid_image_path'] = $vid_image_path_array[$i];
                    $contact_data['is_transition'] = $vid_transition_array[$i];

                    $resource_contact_model->add($contact_data);
                }
            } else {
                for ($i = 0; $i < count($file_array); $i++) {
                    if ($data['type'] == 'image') {
                        $watermark_image_ = $file_array[$i];
                        $arr = explode('.', $watermark_image_);
                        $watermark_image = $arr[count($arr) - 2] . '_w' . '.' . $arr[count($arr) - 1];

                        $contact_data['resource_path'] = $watermark_image;
                        $contact_data['copy_img'] = $file_array[$i];
                        $contact_data['watermark_img'] = $watermark_image;
                        $contact_data['watermark_status'] = 1;
                    } else {
                        $contact_data['resource_path'] = $file_array[$i];
                    }
                    $insert_id = $resource_contact_model->add($contact_data);

                    if ('ppt' == $data['type']) {
                        $pushMQData = array($insert_id, $contact_data['biz_resource_id'], $contact_data['resource_path'], OSS_URL, 'teacher');
                        processMQMessage(CONVERTPPT_EX_NAME, K_ROUTE, 'push', implode(' ', $pushMQData));
                    }
                }
            }

            $this->redirect("Teach/myPublishResource");

        } else {

            $this->assign('module', '教学+');
            $this->assign('nav', '教学资源');
            $this->assign('subnav', '发布资源');
            $this->assign('navicon', 'jiaoshiziyuan');

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            $this->display();
        }
    }

    //小黑板文件上传
    public function blackboard_file_upload()
    {
        $upload = new \Oss\Ossupload();// 实例化上传类
        $result = $upload->upload(3, $_FILES, 7, 0); //1 pic 2//
        $returnArray = explode(",", $result[1]);
        $uploadOK = 1;
        for ($i = 0; $i < sizeof($returnArray); $i++) {
            if ($returnArray[$i] == "") {
                $uploadOK = 0;
                break;
            }
        }
        if ($uploadOK == 0) {
            $arr['msg'] = '上传失败';
            $arr['code'] = -1;
        } else {
            $arr['msg'] = '上传成功';
            $arr['code'] = 0;
        }
        $arr['res'] = $result[1];
        $arr['is_transition'] = 0;
        echo json_encode($arr);
    }


    //教师资源文件上传
    public function upload_file($a = null)
    {
        if (!empty($_GET['watermark_status'])) {
            $GLOBALS['is_watermark'] = 1;
        }
        $video_array = $this->resource_video_image_upload();

        $upload = new \Oss\Ossupload();// 实例化上传类
        //var_dump($_FILES);exit;
        $result = $upload->upload(3, $_FILES, 3, 0); //1 pic 2//

        $returnArray = explode(",", $result[1]);
        $uploadOK = 1;
        for ($i = 0; $i < sizeof($returnArray); $i++) {
            if ($returnArray[$i] == "") {
                $uploadOK = 0;
                break;
            }
        }
        if ($uploadOK == 0) {
            $arr['msg'] = '上传失败';
            $arr['code'] = -1;
            $arr['message'] = $result[2];
        } else {
            $arr['msg'] = '上传成功';
            $arr['code'] = 0;
        }
        $arr['res'] = $result[1];
        $arr['message_video_image'] = $video_array['video_image'];
        $arr['is_transition'] = $video_array['is_transition'];
        if ($a == 1) {
            return json_encode($arr);
        } else {
            echo json_encode($arr);
        }
    }

    //修改资源
    public function modifyResource()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if ($_POST) {
            /*$check['id'] = $_POST['id'];
            $data['name'] = remove_xss($_POST['name']);
            $data['description'] = remove_xss($_POST['description']);
            $data['course_id'] = $_POST['course_id'];
            $data['grade_id'] = $_POST['grade_id'];
            $data['textbook_id'] = $_POST['textbook_id'];
            $data['type'] = strtolower($_POST['real_type']);
            */
            $check['id'] = getParameter('id', 'int', false);
            $data['name'] = getParameter('name', 'str', false);
            $data['description'] = getParameter('description', 'str', false);
            $data['course_id'] = getParameter('course_id', 'int', false);
            $data['grade_id'] = getParameter('grade_id', 'int', false);
            $data['textbook_id'] = getParameter('textbook_id', 'int', false);
            $data['type'] = strtolower(getParameter('real_type', 'str', false));

            $data['status'] = 1;

            $existsed_ids = $_POST['y_id'];
            //$contact['file_path']=$_POST['unique_string'];
            $contact['file_path'] = getParameter('unique_string', 'str', false);

            if ($contact['file_path'] != '') {
                $file_array = explode(',', $contact['file_path']);

                if ($data['type'] == 'audio' || $data['type'] == 'video') {
                    /*
                    $contact['vid_width']=$_POST['vid_width'];
                    $contact['vid_time']=$_POST['vid_time'];
                    $contact['vid']=$_POST['unique_vid'];
                    $contact['vid_fullpath']=$_POST['vid_fullpath'];
                    $contact['vid_image_path']=$_POST['vid_image_path'];
                    $contact['vid_transition']=$_POST['vid_transition'];
                    */

                    $contact['vid_width'] = getParameter('vid_width', 'str', false);
                    $contact['vid_time'] = getParameter('vid_time', 'str', false);
                    $contact['vid'] = getParameter('unique_vid', 'str', false);
                    $contact['vid_fullpath'] = getParameter('vid_fullpath', 'str', false);
                    $contact['vid_image_path'] = getParameter('vid_image_path', 'str', false);
                    $contact['vid_transition'] = getParameter('vid_transition', 'str', false);


                    $vid_width_array = explode(',', $contact['vid_width']);
                    $vid_time_array = explode(',', $contact['vid_time']);
                    $vid_array = explode(',', $contact['vid']);
                    $vid_fullpath_array = explode(',', $contact['vid_fullpath']);
                    $vid_image_path_array = explode(',', $contact['vid_image_path']);
                    $vid_transition_array = explode(',', $contact['vid_transition']);
                }
            }


            $contact_model = M('biz_resource_contact');
            $resource_id = $check['id'];
            $contact_model->startTrans();

            //判断过去的id字符串是否为空
            if (!empty($existsed_ids)) {
                $existsed_ids_string = implode(',', $existsed_ids);
                $existsed_ids_string = rtrim($existsed_ids_string, ',');
                $contact_where_string = '(' . $existsed_ids_string . ')';
                $contact_model->where("biz_resource_id=$resource_id and id not in " . $contact_where_string)->delete();
            } else {
                $contact_model->where("biz_resource_id=$resource_id")->delete();
            }
            $contact_data['biz_resource_id'] = $resource_id;
            if ($contact['file_path'] != '') {
                if ($data['type'] == 'audio' || $data['type'] == 'video') {
                    for ($i = 0; $i < count($file_array); $i++) {
                        $contact_data['vid'] = $vid_array[$i];
                        $contact_data['playerwidth'] = $vid_width_array[$i];
                        $contact_data['playerduration'] = $vid_time_array[$i];
                        $contact_data['resource_path'] = $file_array[$i];
                        $contact_data['vid_fullpath'] = $vid_fullpath_array[$i];
                        $contact_data['vid_image_path'] = $vid_image_path_array[$i];
                        $contact_data['is_transition'] = $vid_transition_array[$i];

                        $contact_model->add($contact_data);
                    }
                } else {
                    for ($i = 0; $i < count($file_array); $i++) {
                        if ($data['type'] == 'image') {
                            $watermark_image_ = $file_array[$i];
                            $arr = explode('.', $watermark_image_);
                            $watermark_image = $arr[count($arr) - 2] . '_w' . '.' . $arr[count($arr) - 1];

                            $contact_data['resource_path'] = $watermark_image;
                            $contact_data['copy_img'] = $file_array[$i];
                            $contact_data['watermark_img'] = $watermark_image;
                            $contact_data['watermark_status'] = 1;
                        } else {
                            $contact_data['resource_path'] = $file_array[$i];
                        }
                        $contact_model->add($contact_data);
                    }
                }
            }


            $resource_contact_data = $contact_model->where('biz_resource_id=' . $resource_id)->find();
            $data['vid'] = $resource_contact_data['vid'];
            $data['playerwidth'] = $resource_contact_data['playerwidth'];
            $data['playerduration'] = $resource_contact_data['playerduration'];
            $data['file_path'] = $resource_contact_data['resource_path'];
            $data['vid_fullpath'] = $resource_contact_data['vid_fullpath'];
            $data['vid_image_path'] = $resource_contact_data['vid_image_path'];

            $ResourceModel = M('biz_resource');
            /*
            $filter = new \Org\Util\SensitiveFilter;
            if (!$filter::filter($data['name'])  || !$filter::filter($data['description'])) {
                //标记敏感词汇
                $data['has_sensitive_words'] = 1;
            }*/
            $filter = D('Sensitive_words');
            if (!$filter->filter($data['name']) || !$filter->filter($data['description'])) {
                //标记敏感词汇
                $data['has_sensitive_words'] = 1;
            }

            if ($ResourceModel->where($check)->save($data) === false) {
                $contact_model->rollback();
                $this->error('访问异常');
            }
            $contact_model->commit();

            $this->redirect("Teach/myPublishResource");

        } else {
            $this->assign('module', '教学+');
            $this->assign('nav', '教学资源');
            $this->assign('subnav', '修改我发布的资源');
            $this->assign('navicon', 'jiaoshiziyuan');

            //$id = $_GET['id'];
            $id = getParameter('id', 'int', false);

            if (!$id) {
                redirect(U('Index/systemError'));
            }
            $from = $_GET['from'];
            $this->assign('id', $id);
            $this->assign('from', $from);

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            $Model = M('biz_textbook');
            $textbooks = $Model->order('sort_order asc')->select();
            $this->assign('textbooks', $textbooks);

            $Model = M('biz_resource');
            $result = $Model
                ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id', 'left')
                ->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
                ->join('dict_course on biz_resource.course_id=dict_course.id', 'left')
                ->join('dict_grade on biz_resource.grade_id=dict_grade.id', 'left')
                ->field('biz_resource.*,biz_textbook.name as textbook,auth_teacher.brief_intro as teacher_brief_intro,auth_teacher.points as points,dict_course.course_name,dict_grade.grade')
                ->where("(biz_resource.status=2 or biz_resource.status=3) and biz_resource.id='$id'")
                ->find();
            if (empty($result)) {
                redirect(U('Index/systemError'));
            }
            //$this->assign('subnav', '修改 ' + $result['name']);
            $this->assign('data', $result);

            //这里求出关联表的所有数据
            $contact_resource_model = M('biz_resource_contact');
            $contact_result = $contact_resource_model->where("biz_resource.id=" . $id)->join("biz_resource on biz_resource.id=biz_resource_contact.biz_resource_id")
                ->field("biz_resource_contact.id,biz_resource.teacher_id,biz_resource.type,biz_resource_contact.resource_path,biz_resource_contact.vid vvid,biz_resource_contact.vid_image_path")->select();
            $this->assign('contact_result', $contact_result);

            $TextbookModel = M('biz_textbook');
            $c1['course_id'] = $result['course_id'];
            $c1['grade_id'] = $result['grade_id'];
            $textbooks = $TextbookModel->where($c1)->select();
            $this->assign('textbooks', $textbooks);

            $this->display();
        }
    }

    //删除我发布的资源
    public function deleteResource()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$id = $_GET['id'];
        $id = getParameter('id', 'int');

        $ResourceModel = M('biz_resource');

        $c1['id'] = $id;
        $c1['teacher_id'] = session('teacher.id');

        $ResourceModel->startTrans();
        if ($ResourceModel->where($c1)->delete() === false) {
            $ResourceModel->rollback();
            $this->ajaxReturn('failed');
        }

        $resource_contact_model = M('biz_resource_contact');
        $c2['biz_resource_id'] = $id;
        if ($resource_contact_model->where($c2)->delete() === false) {
            $ResourceModel->rollback();
            $this->ajaxReturn('failed');
        }
        $ResourceModel->commit();

        $this->ajaxReturn('success');
    }

    //我发布的资源列表
    public function myPublishResource()
    {
        if (!session('?teacher')) redirect(U('Index/index'));
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }
        $this->assign('module', '教学+');
        $this->assign('nav', '教学资源');
        $this->assign('subnav', '我发布的资源');
        $this->assign('navicon', 'jiaoshiziyuan');


        $Model = M('biz_resource');
        /*
        $count = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
            ->where("biz_resource.type!='html' and biz_resource.teacher_id=" . session('teacher.id'))
            ->count('biz_resource.id');
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();
        */
        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id', 'left')
            ->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
            ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id', 'left')
            ->field('biz_resource.*,biz_textbook.name as textbook,dict_schoollist.school_name')
            ->where("biz_resource.type!='html' and biz_resource.teacher_id=" . session('teacher.id'))
            ->order('biz_resource.create_at desc')
            //->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $this->assign('list', $result);
        //$this->assign('page', $show);

        $this->display();
    }

    //我收藏的资源
    public function myFavorResource()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '教学+');
        $this->assign('nav', '教学资源');
        $this->assign('subnav', '我收藏的资源');
        $this->assign('navicon', 'jiaoshiziyuan');

        $Model = M('biz_resource');

        $count = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id')
            ->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')
            ->where("biz_resource.status=2 and user_type=0 and biz_resource_collect.user_id=" . session('teacher.id'))
            ->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id')
            ->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')
            ->field('biz_resource.*,biz_textbook.name as textbook')
            ->where("biz_resource.status=2 and user_type=0 and biz_resource_collect.user_id=" . session('teacher.id'))
            ->order('biz_resource.create_at desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $this->assign('list', $result);
        $this->assign('page', $show);

        $this->display();
    }

    //所有教师的资源列表
    public function resourceList()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        $this->assign('module', '教学+');
        $this->assign('nav', '教师资源分享');
        $this->assign('subnav', '资源列表');
        $this->assign('navicon', 'jiaoshiziyuan');

        /*
        $filter['course_id'] = $_REQUEST['course'];
        $filter['grade_code'] = $_REQUEST['grade'];
        //$filter['textbook_id'] = $_REQUEST['textbook_id'];
        $filter['school_term'] = $_REQUEST['textbook'];
        $filter['type'] = $_REQUEST['type'];
        $filter['keyword'] = $_REQUEST['keyword'];
        $filter['sort_column'] = $_REQUEST['sort_column'];
        */

        $filter['course_id'] = getParameter('course', 'int', false);
        $filter['grade_code'] = getParameter('grade', 'int', false);
        //$filter['textbook_id'] = $_REQUEST['textbook_id'];
        $filter['school_term'] = getParameter('textbook', 'int', false);
        $filter['type'] = getParameter('type', 'str', false);
        $filter['keyword'] = $_REQUEST['keyword'];
        $filter['sort_column'] = getParameter('sort_column', 'int', false);


        $check['biz_resource.status'] = 2;
        if (!empty($filter['type'])) $check['biz_resource.type'] = $filter['type'];
        if (!empty($filter['keyword'])) $check['biz_resource.name'] = array('like', '%' . $filter['keyword'] . '%');
        if (!empty($filter['school_term'])) $check['biz_textbook.school_term'] = $filter['school_term'];
        if (empty($filter['sort_column'])) $filter['sort_column'] = '0';

        $unique_grade_code = intval($filter['grade_code']);
        if ($unique_grade_code == 14) {
            $check['_string'] = "dict_grade.code in (14)";
        } elseif ($unique_grade_code == 15) {
            $check['_string'] = "dict_grade.code in (15)";
        } elseif ($unique_grade_code == 16) {
            $check['_string'] = "dict_grade.code in (16)";
        } else {
            if ($unique_grade_code > 0 && $unique_grade_code < 7) {
                $check['_string'] = "dict_grade.code in (14," . $unique_grade_code . ")";
            } elseif ($unique_grade_code > 6 && $unique_grade_code < 10) {
                $check['_string'] = "dict_grade.code in (15," . $unique_grade_code . ")";
            } elseif ($unique_grade_code > 9 && $unique_grade_code < 13) {
                $check['_string'] = "dict_grade.code in (16," . $unique_grade_code . ")";
            }
        }

        $unique_course_id = intval($filter['course_id']);
        if ($unique_course_id == -1) {
            if (empty($ceck['_string'])) {
                if (!empty($filter['course_id'])) $check['_string'] = 'biz_resource.course_id in (' . $unique_course_id . ')';
            } else {
                if (!empty($filter['course_id'])) $check['_string'] .= ' and biz_resource.course_id in (' . $unique_course_id . ')';
            }
        } else {
            if (empty($check['_string'])) {
                if (!empty($filter['course_id'])) $check['_string'] = 'biz_resource.course_id in (-1,' . $unique_course_id . ')';
            } else {
                if (!empty($filter['course_id'])) $check['_string'] .= ' and biz_resource.course_id in (-1,' . $unique_course_id . ')';
            }
        }
        if (empty($check['_string'])) {
            $check['_string'] = "biz_resource.type!='html'";
        } else {
            $check['_string'] .= " and biz_resource.type!='html'";
        }


        $sort = I('sort_column');
        if (!empty($sort) && $sort != '') {
            switch ($sort) {
                case 0:
                    $sort_string = "zan_count desc";
                    break;
                case 1:
                    $sort_string = "zan_count asc";
                    break;
                case 2:
                    $sort_string = "favorite_count desc";
                    break;
                case 3:
                    $sort_string = "favorite_count asc";
                    break;
                case 4:
                    $sort_string = "follow_count desc";
                    break;
                case 5:
                    $sort_string = "follow_count asc";
                    break;
                case 6:
                    $sort_string = "create_at desc";
                    break;
                case 7:
                    $sort_string = "create_at asc";
                    break;
                default:
                    $sort_string = "create_at desc";
                    break;
            }
            if ($sort >= 0 && $sort < 8) {
                $filter['sort_column'] = $sort;
            } else {
                $filter['sort_column'] = 6;
            }
        } else {
            if ($sort != '') {

                $sort_string = "zan_count desc";
                $filter['sort_column'] = 0;
            } else {

                $sort_string = "create_at desc";
                $filter['sort_column'] = 6;
            }
        }


        $this->assign('course_id', $filter['course_id']);
        $this->assign('grade_code', $filter['grade_code']);
        $this->assign('textbook_id', $filter['school_term']);
        $this->assign('type', $filter['type']);
        $this->assign('keyword', $filter['keyword']);
        $this->assign('sort_column', $filter['sort_column']);


        $Model = M('biz_resource');

        $count = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id', 'left')
            ->join('dict_grade on dict_grade.id=biz_resource.grade_id', 'left')
            ->field('biz_resource.*,biz_textbook.name as textbook')
            ->where($check)
            ->count('biz_resource.id');
        $Page = new \Think\Page($count, 21);
        //C('PAGE_SIZE_FRONT')
        foreach ($filter as $key => $val) {
            $Page->parameter[$key] =$val;// urlencode($val);
        }

        $show = $Page->show();

        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id', 'left')
            ->join('dict_grade on dict_grade.id=biz_resource.grade_id', 'left')
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

        $TextbookModel = M('biz_textbook');

        $this->display_nocache();
    }

    //资源详情
    public function resourceDetails($id = array(), $from = "", $type = 2)
    {

        if (!session('?teacher')) {
            $this->assign('teacher_tag', 2);
        } else {
            $this->assign('teacher_tag', 1);
        }

        if ($type != 1) {
            $isAuth = $this->isAuth($this->c_a);

            if (!$isAuth) { //如果访问的模块没有权限
                redirect(U('Teach/index1?auth_error=1'));
            }

        }


        $this->assign('module', '教学+');
        $this->assign('nav', '');
        $this->assign('navicon', 'jiaoshiziyuan');

        $id = intval($id);
        if (!empty($id)) {
            //$id = $_GET['id'];
            $id = getParameter('id', 'int', false);
            if (!$id) {
                redirect(U('Index/systemError'));
            }
            $teacher_online = 1;
        } else {
            redirect(U('Index/systemError'));
        }
        /*
        if(isset($_SERVER['HTTP_REFERER'])){
            //后台过来的
            $string= substr(strchr($_SERVER['REQUEST_URI'],'key'),4);
            $des=new DES3();
            $id=$des->des3_decrypt($string);
            $teacher_online=2;
            if(!$id){
                redirect(U('Index/systemError'));
            }
        }*/
        $goback = $_GET['goback'];
        $this->assign('showGoBackbutton', $goback);
        if (!empty($_GET['f']))
            $from = $_GET['f'];
        $this->assign('from', $from);

        $Model = M('biz_resource');
        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id', 'left')
            ->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
            ->join('dict_course on biz_resource.course_id=dict_course.id', 'left')
            ->join('dict_grade on biz_resource.grade_id=dict_grade.id', 'left')
            ->field('biz_resource.*,biz_textbook.name as textbook,biz_resource.teacher_id,auth_teacher.brief_intro as teacher_brief_intro,auth_teacher.points as points,dict_course.course_name,dict_grade.grade')
            ->where("biz_resource.id=" . $id)
            ->find();
        //判断资源是否为空
        /*if(empty($result)){
            redirect(U('Index/systemError'));
        }*/
        $result['type'] = strtolower($result['type']);      //var_dump($result);    die;
        $this->assign('subnav', $result['name']);
        $this->assign('data', $result);

        //拿到关联表的数据
        $contact_result = $Model->where("biz_resource.id=" . $id)->join("biz_resource_contact on biz_resource_contact.biz_resource_id=biz_resource.id")
            ->field("biz_resource_contact.*")->select();        //echo $Model->getLastSql();die;
        $this->assign('contact_data', $contact_result);
        /*if(empty($contact_result)){
            redirect(U('Index/systemError'));
        }*/
        if ($teacher_online == 2 || $teacher_online == 1) {
            //观看次数+1
            $Model->where("id=$id")->setInc('follow_count', 1);
        }
        //$User = M("auth_teacher");
        //$User->where("id=" . $result['teacher_id'])->setInc("points", 1);// 积分加1

        //判断登陆者是否和发布者是一人
        if ($result['teacher_id'] == session('teacher.id')) {
            $this->assign('operation_status', 1);
        } else {
            $this->assign('operation_status', 2);
        }
        if ($teacher_online == 1) {
            //判断我是否赞过和收藏过
            $ZanModel = M('biz_resource_zan');
            $zanData['resource_id'] = $id;
            $zanData['user_type'] = 0;
            $zanData['user_id'] = session('teacher.id');
            $existedZan = $ZanModel->where($zanData)->find();
            $existedZan = empty($existedZan) ? 'no' : 'yes';
            $this->assign('existedZan', $existedZan);


            $FavorModel = M('biz_resource_collect');
            $favorData['resource_id'] = $id;
            $favorData['user_type'] = 0;
            $favorData['user_id'] = session('teacher.id');
            $existedFavor = $FavorModel->where($favorData)->find();
            $existedFavor = empty($existedFavor) ? 'no' : 'yes';
            $this->assign('existedFavor', $existedFavor);
        }

        $this->display();

    }

    //赞一个教师资源
    public function zanResource()
    {
        $teacherId = session('teacher.id');
        if (empty($teacherId)) {
            //$teacherId = $_GET['user_id'];
            $teacherId = getParameter('user_id', 'int');
            if (empty($teacherId)) {
                redirect(U('Index/index'));
            }
        }
        //$id = $_GET['id'];
        $id = getParameter('id', 'int');

        $ZanModel = M('biz_resource_zan');
        $zanData['resource_id'] = $id;
        $zanData['user_id'] = $teacherId;
        $zanData['user_type'] = 0;

        $existed = $ZanModel->where($zanData)->find();
        if (empty($existed)) {
            $zanData['create_at'] = time();
            $res = M('auth_teacher')->where("id=$teacherId")->find();
            $zanData['user_name'] = $res['name'];
            $ZanModel->add($zanData);


            $Model = M('biz_resource');
            $Model->where("id=$id")->setInc('zan_count', 1);

            $resource = $Model->where("id=$id")->find();
            $User = M("auth_teacher");
            $User->where("id=" . $resource['teacher_id'])->setInc("points", 5);// 积分加5

            $this->ajaxReturn("success");
        } else {
            $ZanModel->where("resource_id=$id and user_type=0 and user_id=$teacherId")->delete();
            $Model = M('biz_resource');
            $Model->where("id=$id")->setDec('zan_count', 1);

            $resource = $Model->where("id=$id")->find();
            $User = M("auth_teacher");
            $User->where("id=" . $resource['teacher_id'])->setDec("points", 5);//积分减5

            $this->ajaxReturn("已经取消点赞");
        }
    }


    //收藏一个教师资源
    public function favorResource()
    {
        $teacherId = session('teacher.id');
        if (empty($teacherId)) {
            //$teacherId = $_GET['user_id'];
            $teacherId = getParameter('user_id', 'int');

            if (empty($teacherId)) {
                redirect(U('Index/index'));
            }
        }

        //$id = $_GET['id'];
        $id = getParameter('id', 'int');

        $FavorModel = M('biz_resource_collect');
        $favorData['resource_id'] = $id;
        $favorData['user_id'] = $teacherId;
        $favorData['user_type'] = 0;

        $existed = $FavorModel->where($favorData)->find();
        if (empty($existed)) {
            $favorData['create_at'] = time();
            $res = M('auth_teacher')->where("id=$teacherId")->find();
            $favorData['user_name'] = $res['name'];
            $FavorModel->add($favorData);

            $Model = M('biz_resource');
            $Model->where("id=$id")->setInc('favorite_count', 1);

            $resource = $Model->where("id=$id")->find();
            $User = M("auth_teacher");
            $User->where("id=" . $resource['teacher_id'])->setInc("points", 5);// 积分加5

            $this->ajaxReturn("success");
        } else {
            $FavorModel->where("resource_id=$id and user_type=0 and user_id=$teacherId")->delete();
            $Model = M('biz_resource');
            $Model->where("id=$id")->setDec('favorite_count', 1);

            $resource = $Model->where("id=$id")->find();
            $User = M("auth_teacher");
            $User->where("id=" . $resource['teacher_id'])->setDec("points", 5);//积分减5

            $this->ajaxReturn("已经取消收藏");
        }
    }


    /* public function redirectNobook()
    {
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')){
            $token = getParameter('token','str');
            $time = getParameter('time','str');
            $currentTime = time();
            if(abs($currentTime - $time) < 60*5)
            {
                $appid=C('NOBOOK_CONFIG.appid');
                $appkey=C('NOBOOK_CONFIG.appkey');
                $string=md5($appid.$time.$appkey);
                if($token != $string)
                    exit;
            }
            else
              exit;
        }
        else {
            A('Home/Common')->getUserIdRole($userId,$role);;
            if (-1 == $role)
                exit;
        }
        $course = getParameter('course','str');
        $id = getParameter('id','int',false);
        $str = $this->getNobookLink(1);
        if($course =='wuli')
        {
            $url = 'http://' . $course . '.nobook.com.cn/openapi' . $str;
        }
        else {
            if ($course != 'science')
                $redirectLink = 'http://' . $course . '.nobook.com.cn/online?id=' . $id;
            else
                $redirectLink = 'http://' . $course . '.nobook.com.cn/online/view/' . $id;
            $url = 'http://' . $course . '.nobook.com.cn/openapi' . $str . '&url=' . $redirectLink;
        }
        echo "<style>*{padding:0;margin:0}</style><iframe frameborder=\"0\" border=\"0\" marginwidth=\"0\" marginheight=\"0\" style=\"height:100%;width:100%\" src=\"$url\" ></iframe>";

    }*/
    public function redirectNobook()
    {
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad') || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone') || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')) {
            $token = getParameter('token', 'str');
            $time = getParameter('time', 'str');
            $userId = getParameter('userId', 'int');
            $currentTime = time();
            if (abs($currentTime - $time) < 60 * 5) {
                $appid = '1626773242';
                $appkey = 'Eco3uXAKzxF85wnS';
                $string = md5($appid . $time . $appkey);
                if ($token != $string)
                    exit;
            } else
                exit;
        } else {
            A('Home/Common')->getUserIdRole($userId, $role);
            if (-1 == $role)
                exit;
        }
        //从id的地方开始截取并替换成?sourceid=之后的内容
        $course = getParameter('course', 'str');
        //截取sourceid之后的所有内容
        $serverParameters = $_SERVER['QUERY_STRING'];
        $newServerParameters = explode("&redirectLink=", $serverParameters);
        $newServerParameters = $newServerParameters['1'];
        //小学科学：$newServerParameters = substance0002.html
        //物理：$newServerParameters = 98b7908cdb19fcadfe77a380b4769c63
        //初中生物：$newServerParameters = 1ff802be15710631066f78c22ffa1def&ah=37a6259cc0c1dae2&rh=2a05eb183f4e42a8
        switch ($course) {
            case  'science':
                $nobookCourse = 'sci';
                break;
            case 'shengwu':
                $nobookCourse = 'biocz';
                break;
            case 'wuli':
                $nobookCourse = 'phy';
                break;
            default:exit;
        }
        $redirectLink = $newServerParameters;
        if(strpos($redirectLink,'?') === false){
            if(strpos($redirectLink,'&') !== false)
            $redirectLink = substr($redirectLink,0,strpos($redirectLink,'&'));
        }
        $strLogin = $this->getLoginUrl($userId, $nobookCourse, C('NOBOOK_CONFIG.appid'), time(), C('NOBOOK_CONFIG.appkey'), $redirectLink);
        if ((strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad') || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone') || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')) && strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'safari') === false) {
            header("Location:$strLogin");
        }
        else
            echo "<style>*{padding:0;margin:0}</style><iframe frameborder=\"0\" border=\"0\" marginwidth=\"0\" marginheight=\"0\" style=\"height:100%;width:100%\" src=\"$strLogin\" ></iframe>";
    }

    /**
     *脚本替换老的nobookURL
     */
    public function jiaobentihuan(){
        $nobookCourse = getParameter('nobookCourse','str');
        $nobookCourse = $nobookCourse;
        $strList = $this->getListUrl($nobookCourse, C('NOBOOK_CONFIG.appid'), C('NOBOOK_CONFIG.appkey'), time());
        $a = file_get_contents($strList);
//var_dump(json_decode($a)->data);die;
        //查询库中的所有nobook资源获取名字，进行匹配然后替换URL
        $Model = M('knowledge_resource');
        $resourceData = $Model->where("knowledge_resource.resource_type=2")
            ->join("knowledge_resource_file_contact on knowledge_resource.id=knowledge_resource_file_contact.resource_id")
            ->field("knowledge_resource.id,knowledge_resource.name,knowledge_resource_file_contact.resource_path")
            ->select();
//var_dump($resourceData);die;
        M('knowledge_resource_file_contact')->startTrans();
        foreach ($resourceData as $item){
            foreach (json_decode($a)->data as $values){
                if(md5($values->name) === md5($item['name'])){
                    $newStr = explode('&id=',$item['resource_path']);
                    $newStr = $newStr[0].'&redirectLink='.$values->url;
                    //修改数据库数据
                    $data['resource_path'] = $newStr;
                    $where['resource_id'] = $item['id'];
                    $status = M('knowledge_resource_file_contact')
                        ->where($where)
                        ->save($data);
                    if($status === false){
                        $errorData[] = $values;
                        M('knowledge_resource_file_contact')->rollback();
                    }else{
                        M('knowledge_resource_file_contact')->commit();
                    }
                }
            }
        }
        if(!empty($errorData)){
            var_dump($errorData) ;
        }
    }

//生成签名
    function sign($array)
    {
        ksort($array);
        $string = "";
        while (list($key, $val) = each($array)) {
            $string = $string . $val;
        }
        return md5($string);
    }
    //获取登录Url
    function getLoginUrl($uid, $subject, $appid, $timestamp, $appkey, $redirectLink)
    {
        $arr = [
            'uid' => $uid,
            'subject' => $subject,
            'appid' => $appid,
            'timestamp' => $timestamp,
            'appkey' => $appkey,
        ];
        $sign = $this->sign($arr);
        $param = [
            'timestamp' => $timestamp,
            'uid' => $uid,
            'subject' => $subject,
            'appid' => $appid,
            'sign' => $sign,
            'redirect' => $redirectLink,
        ];

        $url = 'https://res-api.nobook.com/api/login/autologin?' . http_build_query($param);

        return $url;


    }

//获取实验列表URL
    function getListUrl($subject, $appid, $appkey, $timestamp)
    {
        $arr = [
            'subject' => $subject,
            'appid' => $appid,
            'appkey' => $appkey,
            'timestamp' => $timestamp,
        ];

        $sign = $this->sign($arr);
        $param = [
            'timestamp' => $timestamp,
            'subject' => $subject,
            'appid' => $appid,
            'sign' => $sign,
        ];

        $url = 'https://res-api.nobook.com/api/experiment/get?' . http_build_query($param);

        return $url;
    }


    //返回一个nobook的链接
    public function getNobookLink($para = 0, $course = '', $userId = '')
    {
        $time = time();
        $appid = C('NOBOOK_CONFIG.appid');
        $appkey = C('NOBOOK_CONFIG.appkey');
        $subject = $course;
        $newarr = array('uid' => $userId, 'subject' => $subject, 'appid' => $appid, 'timestamp' => $time, 'appkey' => $appkey);
        ksort($newarr);
        //$newArrValue = array_values($newarr);
        $newString = implode('', $newarr);
        $string = md5($newString);
//var_dump($string);die;
        //$url="?appid=".$appid."&uid=".$userId."&timestamp=".$time."&sign=".$string."&subject=".$course;
        $url = "appid=" . $appid . "&timestamp=" . $time . "&sign=" . $string . "&subject=" . $course;
        if (0 == $para)
            echo $url;
        else
            return $url;
    }

    /*public function getNobookLink($para = 0){
        $time=time();
        //$appid=C('NOBOOK_CONFIG.appid');
        $appid='251537';
        //$appkey=C('NOBOOK_CONFIG.appkey');
        $appkey='218535e5334eef9d';
        $string=md5($appid.$time.$appkey);
        $url="?appid=".$appid."&code=".$string."&time=".$time;
        if(0 == $para)
        echo $url;
        else
        return $url;
    }*/

    public function bjResourceTools()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '教学+');
        $this->assign('nav', '京版数字资源');
        $this->assign('subnav', 'nobook');
        $this->assign('navicon', 'jingbanziyuan');

        $filter['cat'] = $_REQUEST['cat'];
        if (!empty($filter['cat'])) $filter['cat'];

        if ($filter['cat'] < 0 || $filter['cat'] > 1) {
            $filter['cat'] = 0;
        }
        $time = time();
        $appid = C('NOBOOK_CONFIG.appid');
        $appkey = C('NOBOOK_CONFIG.appkey');
        $string = md5($appid . $time . $appkey);

        if ($filter['cat'] == 1) {
            //生物
            $this->assign('cat', 1);
            $url = "https://shengwu.nobook.com.cn/openapi/get_resource?appid=" . $appid . '&code=' . $string . "&time=" . $time;
        } else {
            //科学
            $this->assign('cat', 0);
            $url = "https://science.nobook.com.cn/openapi/get_resource?appid=" . $appid . '&code=' . $string . "&time=" . $time;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($output);     //echo "<pre>";print_r(($data));die;

        //$this->assign('appid', C('NOBOOK_CONFIG.appid'));
        //$this->assign('appkey', C('NOBOOK_CONFIG.appkey'));
        $this->assign('list', $data);
        $this->display();
    }

    //物理实验
    public function bjResourceToolsPhysics()
    {
        if (!session('?teacher')) {
            redirect(U('Index/index'));
        }
        $this->assign('module', '教学+');
        $this->assign('nav', '京版数字资源');
        $this->assign('subnav', '资源列表');
        $this->assign('navicon', 'jingbanziyuan');

        $time = time();

        $appid = C('NOBOOK_CONFIG.appid');
        $appkey = C('NOBOOK_CONFIG.appkey');
        $string = md5($appid . $time . $appkey);
        $url = "https://wuli.nobook.com.cn/openapi?appid=" . $appid . '&code=' . $string . "&time=" . $time;


        $this->assign('url', $url);

        $this->display();
    }

    //京版资源列表
    public function bjResourceList()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $id = getParameter('auth_id', 'int', false);
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
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
        $filter['course_id'] = getParameter('course', 'int', false);
        $filter['grade_code'] = getParameter('grade', 'int', false);
        //$filter['textbook_id'] = $_REQUEST['textbook_id'];
        $filter['school_term'] = getParameter('textbook', 'int', false);
        $filter['type'] = getParameter('type', 'str', false);
        $filter['keyword'] = $_REQUEST['keyword'];
        $filter['sort_column'] = getParameter('sort_column', 'int', false);

        $check['biz_bj_resources.status'] = 2;
        $check['biz_bj_resources.isDisplay'] = 1;

        if (!empty($filter['type'])) $check['biz_bj_resources.type'] = $filter['type'];
        if (!empty($filter['keyword'])) $check['biz_bj_resources.name'] = array("like", "%" . $filter['keyword'] . "%");
        if (!empty($filter['school_term'])) $check['biz_textbook.school_term'] = $filter['school_term'];
        if (empty($filter['sort_column'])) $filter['sort_column'] = '0';


        $unique_grade_code = intval($filter['grade_code']);;
        if ($unique_grade_code == 14) {
            $check['_string'] = "dict_grade.code in (14)";
        } elseif ($unique_grade_code == 15) {
            $check['_string'] = "dict_grade.code in (15)";
        } elseif ($unique_grade_code == 16) {
            $check['_string'] = "dict_grade.code in (16)";
        } else {
            if ($unique_grade_code > 0 && $unique_grade_code < 7) {
                $check['_string'] = "dict_grade.code in (14," . $unique_grade_code . ")";
            } elseif ($unique_grade_code > 6 && $unique_grade_code < 10) {
                $check['_string'] = "dict_grade.code in (15," . $unique_grade_code . ")";
            } elseif ($unique_grade_code > 9 && $unique_grade_code < 13) {
                $check['_string'] = "dict_grade.code in (16," . $unique_grade_code . ")";
            }
        }

        $unique_course_id = intval($filter['course_id']);
        if ($unique_course_id == -1) {
            if (empty($check['_string'])) {
                if (!empty($filter['course_id'])) $check['_string'] = 'biz_bj_resources.course_id in (' . $unique_course_id . ')';
            } else {
                if (!empty($filter['course_id'])) $check['_string'] .= ' and biz_bj_resources.course_id in (' . $unique_course_id . ')';
            }
        } else {
            if (empty($check['_string'])) {
                if (!empty($filter['course_id'])) $check['_string'] = 'biz_bj_resources.course_id in (-1,' . $unique_course_id . ')';
            } else {
                if (!empty($filter['course_id'])) $check['_string'] .= ' and biz_bj_resources.course_id in (-1,' . $unique_course_id . ')';
            }
        }
        if (empty($check['_string'])) {
            $check['_string'] = "biz_bj_resources.type!='html' and biz_bj_resources.type!='others'";
        } else {
            $check['_string'] .= " and biz_bj_resources.type!='html' and biz_bj_resources.type!='others'";
        }

        $sort = I('sort_column');
        if (!empty($sort) && $sort != '') {
            switch ($sort) {
                case 0:
                    $sort_string = "zan_count desc";
                    break;
                case 1:
                    $sort_string = "zan_count asc";
                    break;
                case 2:
                    $sort_string = "favorite_count desc";
                    break;
                case 3:
                    $sort_string = "favorite_count asc";
                    break;
                case 4:
                    $sort_string = "follow_count desc";
                    break;
                case 5:
                    $sort_string = "follow_count asc";
                    break;
                case 6:
                    $sort_string = "create_at desc";
                    break;
                case 7:
                    $sort_string = "create_at asc";
                    break;
                default:
                    $sort_string = "create_at desc";
                    break;
            }
            if ($sort >= 0 && $sort < 8) {
                $filter['sort_column'] = $sort;
            } else {
                $filter['sort_column'] = 6;
            }
        } else {

            if ($sort != '') {

                $sort_string = "zan_count desc";
                $filter['sort_column'] = 0;
            } else {

                $sort_string = "create_at desc";
                $filter['sort_column'] = 6;
            }

        }

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
            ->count('biz_bj_resources.id');
        $Page = new \Think\Page($count, 21);

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

        $this->display_nocache();
    }

    //下载京版资源
    public function downloadBjResource()
    {
        //$id=intval(I('id'));
        //$url=I('url');
        $id = getParameter('id', 'int');
        $url = getParameter('url', 'str');

        if (empty($url) || $url == '') {
            redirect(U('Index/systemError'));
        } else {
            $model = M('biz_bj_resources');
            $model->where('id=' . $id)->setInc('download_count');
            $csv = new CSV();
            $csv->downloadMedia($url);
        }
    }


    //app用户选择京版资源的选项接口
    public function bjResourceJsonOption()
    {

        $out = array();
        $Model = M('dict_course');//学科
        $courses = $Model->order('sort_order asc')->select();

        $Model = M('dict_grade');//年级
        $grades = $Model->select();

        $classInfo = array(
            array(
                'id' => '1',
                'name' => '足球',
            ),
        );

        $book_type = array(
            array(
                'id' => '1',
                'name' => '上册',
            ),
            array(
                'id' => '2',
                'name' => '下册',
            ),
        );

        $file_type = array(
            array(
                'value' => 'video',
                'name' => '视频',
            ),
            array(
                'value' => 'audio',
                'name' => '音频',
            ),
            array(
                'value' => 'image',
                'name' => '图片',
            ),
            array(
                'value' => 'word',
                'name' => 'Word',
            ),
            array(
                'value' => 'pdf',
                'name' => 'PDF',
            ),
            array(
                'value' => 'ppt',
                'name' => 'PPT',
            )
        );

        $out['courses'] = $courses;
        $out['grades'] = $grades;
        $out['class_info'] = $classInfo;
        $out['book_type'] = $book_type;
        $out['file_type'] = $file_type;

        echo json_encode($out);
        exit;
    }

    //京版资源详情
    public function bjResourceDetails($id, $type = 2)
    {
        if (!session('?teacher')) {

            if ($type != 1) {
                redirect(U('Index/index'));
            }
        }


        if ($type != 1) {
            $isAuth = $this->isAuth($this->c_a);

            if (!$isAuth) { //如果访问的模块没有权限
                redirect(U('Teach/index1?auth_error=1'));
            }
        }


        $this->assign('module', '教学+');
        $this->assign('nav', '京版资源');
        $this->assign('navicon', 'jingbanziyuan');


        $id = intval($_GET['id']);
        if (!$id) {
            redirect(U('Index/systemError'));
        }
        $from = $_GET['f'];
        $this->assign('from', $from);

        $Model = M('biz_bj_resources');
        $result = $Model
            ->join('biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id', 'left')
            ->join('dict_course on biz_bj_resources.course_id=dict_course.id', 'left')
            ->join('dict_grade on biz_bj_resources.grade_id=dict_grade.id', 'left')
            ->field('biz_bj_resources.*,biz_textbook.name as textbook,dict_course.course_name,dict_grade.grade')
            ->where("biz_bj_resources.status=2 and biz_bj_resources.id=$id")
            ->find();

        $this->assign('subnav', $result['name']);
        $this->assign('data', $result);

        //拿到关联表的数据
        $contact_result = $Model->where("biz_bj_resources.status=2 and biz_bj_resources.id=" . $id)->join("biz_bj_resource_contact on biz_bj_resource_contact.biz_bj_resource_id=biz_bj_resources.id")
            ->field("biz_bj_resource_contact.*")->select();
        $this->assign('contact_data', $contact_result);


        /*if(empty($result)){
            redirect(U('Index/systemError'));
        }
        if(empty($contact_result)){
            redirect(U('Index/systemError'));
        }*/

        //观看次数+1
        if (session('teacher') != 'youke') {
            $Model->where("id=$id")->setInc('follow_count', 1);
        }


        //判断我是否赞过和收藏过
        $ZanModel = M('biz_bj_resource_zan');
        $zanData['resource_id'] = $id;
        $zanData['role'] = 0;
        $zanData['user_id'] = session('teacher.id');
        $existedZan = $ZanModel->where($zanData)->find();
        $existedZan = empty($existedZan) ? 'no' : 'yes';

        $this->assign('existedZan', $existedZan);

        $FavorModel = M('biz_bj_resource_collect');
        $favorData['resource_id'] = $id;
        $favorData['role'] = 0;
        $favorData['user_id'] = session('teacher.id');
        $existedFavor = $FavorModel->where($favorData)->find();
        $existedFavor = empty($existedFavor) ? 'no' : 'yes';
        $this->assign('existedFavor', $existedFavor);


        //$arr = explode("/", $result[file_path]);
        //$fileName = $arr[1];
        $arr = explode(".", $result[file_path]);
        $this->assign('localPdffileName', '../../../Resources/jb/' . $arr[0] . ".pdf");
        $this->assign('data', $result);
        $this->assign('REMOTE_ADDR', C('REMOTE_ADDR'));
        $this->display_nocache();

    }

    //我收藏的京版资源      没用了
    public function myFavorBjResource()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '教学+');
        $this->assign('nav', '京版资源');
        $this->assign('subnav', '我收藏的资源');
        $this->assign('navicon', 'jingbanziyuan');

        //$page = $_REQUEST['page'];
        $page = getParameter('page', 'int', false);
        if (empty($page)) $page = 1;
        $this->assign('page', $page);

        $Model = M('biz_bj_resources');

        $result = $Model
            ->join('biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id')
            ->join('biz_bj_resource_collect on biz_bj_resource_collect.resource_id=biz_bj_resources.id')
            ->field('biz_bj_resources.*,biz_textbook.name as textbook')
            ->where("biz_bj_resources.status=2 and role=0 and biz_bj_resource_collect.user_id=" . session('teacher.id'))
            ->order('biz_bj_resources.create_at desc')
            ->page($page, C('PAGE_SIZE_FRONT'))
            ->select();

        $this->assign('list', $result);

        $this->display();

    }

    //赞一个京版资源
    public function zanBjResource()
    {
        $teacherId = session('teacher.id');
        if (empty($teacherId)) {
            //$teacherId = $_GET['user_id'];
            $teacherId = getParameter('user_id', 'int');
            if (empty($teacherId)) {
                redirect(U('Index/index'));
            }
        }

        //$id = $_GET['id'];
        $id = getParameter('id', 'int');

        $ZanModel = M('biz_bj_resource_zan');
        $zanData['role'] = 0;
        $zanData['resource_id'] = $id;
        $zanData['user_id'] = $teacherId;

        $existed = $ZanModel->where($zanData)->find();
        if (empty($existed)) {
            $zanData['create_at'] = time();
            $res = M('auth_teacher')->where("id=$teacherId")->find();
            $zanData['user_name'] = $res['name'];
            $ZanModel->add($zanData);

            $Model = M('biz_bj_resources');
            $Model->where("id=$id")->setInc('zan_count', 1);
            $this->ajaxReturn("success");
        } else {
            $ZanModel->where("resource_id=$id and role=0 and user_id=$teacherId")->delete();
            $Model = M('biz_bj_resources');
            $Model->where("id=$id")->setDec('zan_count', 1);
            $this->ajaxReturn("已经取消点赞");
        }
    }

    //收藏一个京版资源
    public function favorBjResource()
    {
        $teacherId = session('teacher.id');
        if (empty($teacherId)) {
            //$teacherId = $_GET['user_id'];
            $teacherId = getParameter('user_id', 'int');
            if (empty($teacherId)) {
                redirect(U('Index/index'));
            }
        }

        //$id = $_GET['id'];
        $id = getParameter('id', 'int');

        $FavorModel = M('biz_bj_resource_collect');
        $favorData['resource_id'] = $id;
        $favorData['role'] = 0;
        $favorData['user_id'] = $teacherId;

        $existed = $FavorModel->where($favorData)->find();
        if (empty($existed)) {
            $favorData['create_at'] = time();
            $res = M('auth_teacher')->where("id=$teacherId")->find();
            $favorData['user_name'] = $res['name'];
            $FavorModel->add($favorData);

            $Model = M('biz_bj_resources');
            $Model->where("id=$id")->setInc('favorite_count', 1);
            $this->ajaxReturn("success");
        } else {
            $FavorModel->where("resource_id=$id and role=0 and user_id=$teacherId")->delete();
            $Model = M('biz_bj_resources');
            $Model->where("id=$id")->setDec('favorite_count', 1);
            $this->ajaxReturn("已经取消收藏");
        }
    }


    ////电子课本
    public function textbookList()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        if ($_REQUEST['course'])
            $check['biz_textbook.course_id'] = getParameter('course', 'int', false);
        if ($_REQUEST['grade'])
            $check['biz_textbook.grade_id'] = getParameter('grade', 'int', false);
        if ($_REQUEST['textbook'])
            $check['biz_textbook.school_term'] = getParameter('textbook', 'int', false);
        if ($_REQUEST['keyword']) {
            $keyword = getParameter('keyword', 'str', false);
            $check['biz_textbook.name'] = array('like', '%' . $keyword . '%');
        }


        $this->assign('module', '教学+');
        $this->assign('nav', '电子课本');
        $this->assign('subnav', '电子课本');
        $this->assign('navicon', 'shuzijiaocai');


        $check['_string'] = 'biz_textbook.has_ebook=1 and flag=1';
        $course_con = A('Home/Common')->getTextBookSelector($check, 'course');
        if (!empty($course_con)) {
            $grade_con = A('Home/Common')->getTextBookSelector($check, 'grade');
            $school_term_con = A('Home/Common')->getTextBookSelector($check, 'school_term');
        } else {
            if (!empty($check['biz_textbook.course_id'])) {
                $course_model = D('Dict_course');
                $course_result = $course_model->getCourseInfo($check['biz_textbook.course_id']);
                $course_con[] = $course_result;
            }
            if (!empty($check['biz_textbook.grade_id'])) {
                $grade_model = D('Dict_grade');
                $grade_result = $grade_model->getGradeInfo($check['biz_textbook.grade_id']);
                $grade_con[] = $grade_result;
            }
            if (!empty($check['biz_textbook.school_term'])) {
                if ($check['biz_textbook.school_term'] == 1) {
                    $school_term_con[] = array('school_term' => 1);
                } elseif ($check['biz_textbook.school_term'] == 2) {
                    $school_term_con[] = array('school_term' => 2);
                } elseif ($check['biz_textbook.school_term'] == 3) {
                    $school_term_con[] = array('school_term' => 3);
                }

            }
        }

        $this->assign('courses', $course_con);
        $this->assign('grades', $grade_con);
        $this->assign('textbooks', $school_term_con);

        $this->assign('course_id', $check['biz_textbook.course_id']);
        $this->assign('grade_id', $check['biz_textbook.grade_id']);
        $this->assign('textbook_id', $check['biz_textbook.school_term']);
        $this->assign('keyword', $_REQUEST['keyword']);

        /*
        A('Home/Common')->CourseGradeTextBookSelector(array(
            'course_id'=>$check['biz_textbook.course_id'],
            'grade_id'=>$check['biz_textbook.grade_id'],
            'textbook_id'=>$check['biz_textbook.textbook_id'],
            'keyword'=>$check['keyword']
        ));*/
        $this->display();
    }

    //电子课本详情
    public function textbookDetails()
    {
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            $teacher = session('auth_teacher');
            $parent = session('auth_parent');

            if (!empty($teacher)) {
                redirect(U('Teach/index1?auth_error=1'));
            } elseif (!empty($parent)) {
                redirect(U('Parent/index1?auth_error=1'));
            } else {
                redirect(U('Student/index1?auth_error=1'));
            }

        }


        if (!session('?teacher') && !session('?student') && !session('?parent') && !session('?admin'))
            redirect(U('Index/index'));
        $this->assign('module', '教学+');
        $this->assign('nav', '电子课本');
        $this->assign('navicon', 'shuzijiaocai');


        //$c['id'] = $_GET['id'];
        $c['id'] = getParameter('id', 'int', false);
        if (empty($c['id'])) {
            redirect(U('Index/systemError'));
        }

        $Model = M('biz_textbook');
        $result = $Model->where($c)->find();
        $this->assign('book', $result);

        $this->assign('subnav', $result['name']);

        $this->display();
    }


    //备课系统
    public function myLessonPlannings()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        $this->assign('module', '教学+');
        $this->assign('nav', '备课系统');
        $this->assign('subnav', '我的备课');
        $this->assign('navicon', 'beikexitong');

        if ($_GET['course'])
            $check['biz_lesson_planning.course_id'] = getParameter('course', 'int', false);
        if ($_GET['grade'])
            $check['biz_lesson_planning.grade_id'] = getParameter('grade', 'int', false);
        if ($_GET['textbook'])
            $check['biz_textbook.school_term'] = getParameter('textbook', 'int', false);

        $filter['startDate'] = $_REQUEST['startDate'];
        $filter['endDate'] = $_REQUEST['endDate'];
        $filter['keyword'] = $_REQUEST['keyword'];

        if ($check['biz_lesson_planning.course_id'] != NULL || $check['biz_lesson_planning.grade_id'] != NULL || $check['biz_textbook.school_term'] != NULL || $filter['startDate'] != NULL || $filter['endDate'] != NULL || $filter['keyword'] != NULL) {
            $this->assign('kw', 1);
        }

        if (!empty($filter['endDate']))
            $check['_string'] = "biz_lesson_planning.create_at<=UNIX_TIMESTAMP('" . $filter['endDate'] . "')+86400 ";
        if (!empty($filter['startDate']))
            if (empty($check['_string'])) {
                $check['_string'] = " biz_lesson_planning.create_at>=UNIX_TIMESTAMP('" . $filter['startDate'] . "')";
            } else {
                $check['_string'] .= " and biz_lesson_planning.create_at>=UNIX_TIMESTAMP('" . $filter['startDate'] . "')";
            }

        if (empty($check['_string'])) {
            if (!empty($filter['keyword'])) $check['_string'] = "biz_lesson_planning.name like '%" . $filter['keyword'] . "%' ";
        } else {
            if (!empty($filter['keyword'])) $check['_string'] .= " and biz_lesson_planning.name like '%" . $filter['keyword'] . "%' ";
        }

        $check['teacher_id'] = session('teacher.id');

        $Model = M('biz_lesson_planning');

        $count = $Model->join('biz_textbook on biz_textbook.id=biz_lesson_planning.textbook_id')->where($check)->count();
        $Page = new \Think\Page($count, 23);
        $show = $Page->show();

        $result = $Model
            //->join('dict_course on dict_course.id=biz_lesson_planning.course_id')
            //->join('dict_grade on dict_grade.id=biz_lesson_planning.grade_id')
            ->join('biz_textbook on biz_textbook.id=biz_lesson_planning.textbook_id')
            //->field('biz_lesson_planning.*,dict_course.course_name,dict_grade.grade,biz_textbook.name as textbook')
            ->where($check)
            ->field('biz_lesson_planning.*')
            ->order('biz_lesson_planning.create_at desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $this->assign('keyword', $filter['keyword']);
        $this->assign('startDate', $filter['startDate']);
        $this->assign('endDate', $filter['endDate']);
        $this->assign('date', $filter['date']);
        $this->assign('list', $result);
        $this->assign('page', $show);

        /*A('Home/Common')->CourseGradeTextBookSelector(array(
          'course_id'=>$check['course_id'],
          'grade_id'=>$check['grade_id'],
          'textbook_id'=>$check['biz_textbook.school_term']
         ));*/
        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        $this->assign('courses', $courses);

        $Model = M('dict_grade');
        $grades = $Model->select();
        $this->assign('grades', $grades);

        $this->assign('course_id', $_GET['course']);
        $this->assign('grade_id', $_GET['grade']);
        $this->assign('textbook_id', $_GET['textbook']);


        $this->display();
    }

    //创建修改老版的教案
    public function createLessonPlanningBefore()
    {
        $this->createLessonPlanning();
    }

    //创建教案
    public function createLessonPlanning()
    {
        if (!session('?teacher')) redirect(U('Index/index'));
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }
        if ($_POST) {

            $pieces = explode(",", $_POST['MediaList']);
            for ($i = 0; $i < sizeof($pieces); $i++) {
                $subpiceces[$i] = explode(":", $pieces[$i]);
            }
            $fileList = $_POST['fileList'];
            $returnArray = explode(",", $fileList);

            /*
            $data['name'] = remove_xss($_POST['name']);
            $data['teacher_id'] = session('teacher.id');
            $data['course_id'] = $_POST['course_id'];
            $data['grade_id'] = $_POST['grade_id'];
            $data['textbook_id'] = $_POST['textbook_id'];
            */
            $data['name'] = getParameter('name', 'str', false);
            $data['teacher_id'] = session('teacher.id');
            $data['course_id'] = getParameter('course_id', 'int', false);
            $data['grade_id'] = getParameter('grade_id', 'int', false);
            $data['textbook_id'] = getParameter('textbook_id', 'int', false);

            $data['update_at'] = time();
            //$isEdit = $_POST['isEdit'];
            $isEdit = getParameter('isEdit', 'int', false);
            if ($isEdit == 0) {
                $data['create_at'] = time();
                $lessonPlanningId = M('biz_lesson_planning')->add($data);
            } else {
                //$lessonPlanningId = $_GET['id'];
                $lessonPlanningId = getParameter('id', 'int', false);
                M('biz_lesson_planning')->where("id=$lessonPlanningId")->save($data);
            }
            $addData['biz_lesson_planning_id'] = $lessonPlanningId;
            M('biz_lesson_planning_contact')->where("biz_lesson_planning_id=$lessonPlanningId")->delete();
            for ($i = 0; $i < sizeof($returnArray); $i++) {
                $fileExt = strtolower(pathinfo($returnArray[$i], PATHINFO_EXTENSION));
                if ($fileExt == '')
                    continue;
                switch ($fileExt) {
                    case 'ppt':
                    case 'pptx':
                        $addData['type'] = 'PPT';
                        break;
                    case 'mp4':
                        for ($j = 0; $j < sizeof($subpiceces); $j++) {
                            if ($subpiceces[$j][0] == $i)
                                $addData['vid'] = $subpiceces[$j][1];
                        }
                        $addData['type'] = 'VIDEO';
                        break;
                    case 'mp3':
                        for ($j = 0; $j < sizeof($subpiceces); $j++) {
                            if ($subpiceces[$j][0] == $i)
                                $addData['vid'] = $subpiceces[$j][1];
                        }
                        $addData['type'] = 'AUDIO';
                        break;
                    case 'doc':
                    case 'docx':
                        $addData['type'] = 'WORD';
                        break;
                    case 'pdf':
                        $addData['type'] = 'PDF';
                        break;
                    case 'jpg':
                    case 'png':
                        $addData['type'] = 'PIC';
                        break;
                    case 'swf':
                        $addData['type'] = 'SWF';
                        break;
                    default:
                        continue;
                }
                $addData['file_path'] = $returnArray[$i];
                M('biz_lesson_planning_contact')->add($addData);
            }

            $this->redirect("Teach/myLessonPlannings");
        } else {
            $this->assign('module', '教学+');
            $this->assign('nav', '备课系统');
            $this->assign('subnav', '创建教案');
            $this->assign('navicon', 'beikexitong');

            $currentFunction = 0; //0 -- add lesson planning 1 -- edit lesson planning
            if (!empty($_GET['id'])) {
                //$lpId = $_GET['id'];
                $lpId = getParameter('id', 'int', false);
                $currentFunction = 1;
            }

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);
            $this->assign('REMOTE_ADDR', C('REMOTE_ADDR'));
            if ($currentFunction == 1) {

                $result = M('biz_lesson_planning')->where("id=$lpId")->find();

                $TextbookModel = M('biz_textbook');
                $c1['course_id'] = $result['course_id'];
                $c1['grade_id'] = $result['grade_id'];
                $textbooks = $TextbookModel->where($c1)->select();
                $this->assign('editInfo', $result);
                $this->assign('textbooks', $textbooks);


                $subResult = M('biz_lesson_planning_contact')->where("biz_lesson_planning_id=$lpId")->select();
                $this->assign('subList', $subResult);
                $vidStr = "";
                $fileList = "";
                for ($i = 0; $i < sizeof($subResult); $i++) {
                    if (basename($subResult[$i]['type']) == 'VIDEO') {
                        if ($vidStr == "")
                            $vidStr = $i . ':' . $subResult[$i]['vid'];
                        else
                            $vidStr = $vidStr . ',' . $i . ':' . $subResult[$i]['vid'];
                    }
                    if ($i == 0)
                        $fileList = $subResult[$i]['file_path'];
                    else
                        $fileList = $fileList . ',' . $subResult[$i]['file_path'];
                }
                $this->assign('vid', $vidStr);
                $this->assign('fileList', $fileList);
                //count
                $this->assign('fileCount', count($subResult));
                $this->assign('isEdit', 1);
                $this->assign('lpId', $lpId);
            } else {
                $this->assign('fileCount', 0);
                $this->assign('isEdit', 0);
            }

            $this->display();
        }
    }

    /*
    //修改教案
    public function editLessonPlanning()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if ($_POST) {
            //$data['type'] = $_POST['type'];
            $data['type'] = getParameter('type', 'str',false);
            switch ($data['type']) {
                case 'PPT':
                    if ($_FILES["file"]["error"] == 0) {
                        $upload = new \Think\Upload();// 实例化上传类
                        $upload->maxSize = 911127886;// 设置附件上传大小
                        $upload->exts = array('ppt', 'pptx');// 设置附件上传类型
                        $upload->rootPath = './Resources/lessonplanning/'; // 设置附件上传根目录
                        // 上传单个文件
                        $info = $upload->uploadOne($_FILES['file']);
                        if (!$info) {// 上传错误提示错误信息
                            $this->error($upload->getError());
                        }
                        $data['filepath'] = $info['savepath'] . $info['savename'];
                        $data['create_html'] = 0;
                    }
                    break;
                case 'HTML':
                    $data['content'] = $_POST['content'];
                    break;
                case 'VIDEO':
                    if (!empty($_POST['vid']) && $_POST['vid'] != '') {
                        $data['vid'] = $_POST['vid'];
                        $data['playerwidth'] = $_POST['playerwidth'];
                        $data['playerduration'] = $_POST['playerduration'];
                    }
                    break;
            }
            /*
            $check['id'] = $_POST['id'];
            $data['name'] = remove_xss($_POST['name']);
            $data['course_id'] = $_POST['course_id'];
            $data['grade_id'] = $_POST['grade_id'];
            $data['textbook_id'] = $_POST['textbook_id'];
             */
    /*
        $check['id'] = getParameter('id', 'int',false);
        $data['name'] = getParameter('name', 'str',false);
        $data['course_id'] = getParameter('course_id', 'int',false);
        $data['grade_id'] = getParameter('grade_id', 'int',false);
        $data['textbook_id'] = getParameter('textbook_id', 'int',false);

        $data['update_at'] = time();
        $ResourceModel = M('biz_lesson_planning');
        $ResourceModel->where($check)->save($data);
        $this->redirect("Teach/myLessonPlannings");
    } else {
        $this->assign('module', '教学+');
        $this->assign('nav', '备课系统');
        $this->assign('subnav', '修改教案');
        $this->assign('navicon', 'beikexitong');

        //$id = $_GET['id'];
        $id = getParameter('id', 'int',false);
        $this->assign('id', $id);

        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        $this->assign('courses', $courses);

        $Model = M('dict_grade');
        $grades = $Model->select();
        $this->assign('grades', $grades);

        $Model = M('biz_textbook');
        $textbooks = $Model->order('sort_order asc')->select();
        $this->assign('textbooks', $textbooks);

        $Model = M('biz_lesson_planning');
        $result = $Model->where("id=$id")->find();

        $this->assign('data', $result);

        $TextbookModel = M('biz_textbook');
        $c1['course_id'] = $result['course_id'];
        $c1['grade_id'] = $result['grade_id'];
        $textbooks = $TextbookModel->where($c1)->select();
        $this->assign('textbooks', $textbooks);

        $this->display();
    }
}*/

    //备课课件库
    public function lessonPlanningLib()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $isAuth = $this->isAuth($this->c_a);

        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        $this->assign('module', '教学+');
        $this->assign('nav', '备课系统');
        $this->assign('subnav', '公共课件库');
        $this->assign('navicon', 'beikexitong');
        /*
        $filter['course_id'] = $_REQUEST['course_id'];
        $filter['grade_id'] = $_REQUEST['grade_id'];
        $filter['textbook_id'] = $_REQUEST['textbook_id'];
        */
        $filter['course_id'] = getParameter('course_id', 'int', false);
        $filter['grade_id'] = getParameter('grade_id', 'int', false);
        $filter['textbook_id'] = getParameter('textbook_id', 'int', false);

        if (!empty($filter['course_id'])) $check['biz_lesson_planning_template.course_id'] = $filter['course_id'];
        if (!empty($filter['grade_id'])) $check['biz_lesson_planning_template.grade_id'] = $filter['grade_id'];
        if (!empty($filter['textbook_id'])) $check['biz_lesson_planning_template.textbook_id'] = $filter['textbook_id'];

        $this->assign('course_id', $filter['course_id']);
        $this->assign('grade_id', $filter['grade_id']);
        $this->assign('textbook_id', $filter['textbook_id']);

        $Model = M('biz_lesson_planning_template');

        $count = $Model
            ->join("dict_course on dict_course.id=biz_lesson_planning_template.course_id")
            ->join("dict_grade on dict_grade.id=biz_lesson_planning_template.grade_id")
            ->join("biz_textbook on biz_textbook.id=biz_lesson_planning_template.textbook_id")
            ->field("biz_lesson_planning_template.*,dict_course.course_name,dict_grade.grade,biz_textbook.name as textbook")
            ->where($check)
            ->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($filter as $key => $val) {
            //$Page->parameter[$key] = urlencode($val);
            $Page->parameter[$key] = $val;
        }
        $show = $Page->show();

        $result = $Model
            ->join("dict_course on dict_course.id=biz_lesson_planning_template.course_id")
            ->join("dict_grade on dict_grade.id=biz_lesson_planning_template.grade_id")
            ->join("biz_textbook on biz_textbook.id=biz_lesson_planning_template.textbook_id")
            ->field("biz_lesson_planning_template.*,dict_course.course_name,dict_grade.grade,biz_textbook.name as textbook")
            ->where($check)
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

        $TextbookModel = M('biz_textbook');
        $c1['course_id'] = $filter['course_id'];
        $c1['grade_id'] = $filter['grade_id'];
        $textbooks = $TextbookModel->where($c1)->select();
        $this->assign('textbooks', $textbooks);

        $this->display();
    }

    //ajax 创建课件
    public function createLessonPlanningWithOffice()
    {
        //$name = remove_xss($_GET['name']);
        //$filepath = $_GET['filepath'];
        $name = getParameter('name', 'str');
        $filepath = getParameter('filepath', 'str');

        $data['name'] = $name;
        $data['description'] = '';
        $data['filepath'] = $filepath;

        $data['type'] = 'PPT';
        $data['teacher_id'] = session('teacher.id');

        $data['create_at'] = time();
        $data['update_at'] = time();

        $LessonModel = M('biz_lesson_planning');

        $check['filepath'] = $filepath;
        $existed = $LessonModel->where($check)->find();

        if (empty($existed)) {
            $LessonModel->add($data);
        } else {
            $LessonModel->where($check)->save($data);
        }

        $this->ajaxReturn('success');
    }

    //ajax 获得我创建的课件
    public function getMyLessonPlannings()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $Model = M('biz_lesson_planning');
        $result = $Model
            ->where("teacher_id=" . session('teacher.id'))
            ->order('update_at desc')
            ->select();

        $this->ajaxReturn($result);
    }

    //将课件与数字课堂match
    public function matchLessonWithClassroom()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$classroom_id = $_GET['classroom_id'];
        $classroom_id = getParameter('classroom_id', 'int');
        $lesson_planning_ids = $_GET['lesson_planning_ids'];
        $lessonIds = explode(",", $lesson_planning_ids);

        $Model = M('biz_classroom_lesson_planning');
        $Model->where("classroom_id=" . $classroom_id)->delete();

        for ($i = 0; $i < count($lessonIds); $i++) {
            if (!empty($lessonIds[$i])) {
                $data['classroom_id'] = $classroom_id;
                $data['lesson_planning_id'] = $lessonIds[$i];
                $data['create_at'] = time();
                $Model->add($data);
            }
        }
        $this->ajaxReturn('success');
    }

    //删除备课课件
    public function deleteLessonPlanning()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$id = $_GET['id'];
        $id = getParameter('id', 'int');

        $ResourceModel = M('biz_lesson_planning');
        $c1['id'] = $id;
        $c1['teacher_id'] = session('teacher.id');
        $ResourceModel->where($c1)->delete();

        $ClassroomLessonModel = M('biz_classroom_lesson_planning');
        $c2['lesson_planning_id'] = $id;
        $ClassroomLessonModel->where($c2)->delete();

        $subModel = M('biz_lesson_planning_contact');
        $subModel->where("biz_lesson_planning_id=$id")->delete();

        $this->ajaxReturn('success');
    }

    //老备课详情
    public function lessonplanningDetailsBefore()
    {
        $this->lessonPlanningDetails();
    }

    //备课课件详情
    public function lessonplanningDetails()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        //$resId = $_GET['id'];
        $resId = getParameter('id', 'int', false);

        if (empty($resId)) {
            redirect(U('Index/systemError'));
            //$this->error('请求参数错误');

            exit;
        }
        $Model = M('biz_lesson_planning');
        $result = $Model
            ->join('dict_course on dict_course.id=biz_lesson_planning.course_id')
            ->join('dict_grade on dict_grade.id=biz_lesson_planning.grade_id')
            ->join('biz_textbook on biz_textbook.id=biz_lesson_planning.textbook_id')
            ->field('biz_lesson_planning.name,biz_lesson_planning.filepath,biz_lesson_planning.id,biz_lesson_planning.type,biz_lesson_planning.vid,biz_lesson_planning.oss_path,biz_lesson_planning.content,dict_course.course_name as course,dict_grade.grade,biz_textbook.name as textbook')
            ->where("biz_lesson_planning.id=" . $resId)
            ->select();
        //TODO:process for old version (only one resource)
        if ("" != $result[0]['type']) {
            $result[0]['file_path'] = 'Resources/lessonplanning/' . $result[0]['filepath'];
            $subResult = $result;
        } else {
            $subModel = M('biz_lesson_planning_contact');
            $subResult = $subModel
                ->field('id,biz_lesson_planning_id,type,file_path,vid')
                ->where("biz_lesson_planning_id=" . $resId)
                ->select();
            for ($i = 0; $i < sizeof($subResult); $i++)
                $subResult[$i]['oss_path'] = $result[0]['oss_path'];
        }
        $this->assign('data', $result[0]);
        $this->assign('sublist', $subResult);
        $this->assign('navicon', 'beikexitong');
        $this->display();
    }


    //论坛/////
    //论坛板块
    public function forum()
    {
        $this->assign('module', '教学+');
        $this->assign('nav', '编教论坛');
        $this->assign('subnav', '论坛板块');

        $Model = M('bbs_class');
        $result = $Model->where("status=1 and grade='小学'")->order('sort_order asc')->select();
        $this->assign('list1', $result);

        $result = $Model->where("status=1 and grade='初中'")->order('sort_order asc')->select();
        $this->assign('list2', $result);

        $result = $Model->where("status=1 and grade='高中'")->order('sort_order asc')->select();
        $this->assign('list3', $result);

        $this->display();
    }

    //我收藏的帖子
    public function myFavoriteForum()
    {
        $this->assign('nav', '编教论坛');
        $this->assign('subnav', '我收藏的板块');

        $Model = M('bbs_class');
        $result = $Model
            ->join('bbs_my_favorite_class on bbs_class.id=bbs_my_favorite_class.class_id')
            ->field('bbs_class.*')
            ->where("bbs_class.status=1 and bbs_my_favorite_class.user_type=1 and bbs_my_favorite_class.user_id=" . session('teacher.id'))->order('sort_order asc')->select();
        $this->assign('list', $result);

        $this->display();
    }

    //某个板块下的帖子
    public function bbsTopics($id)
    {
        $this->assign('module', '教学+');
        $this->assign('nav', '编教论坛');


        $Model = M('bbs_topic');
        $result = $Model
            ->where("class_id=$id")->order('create_at desc')->select();
        $this->assign('list', $result);

        $ClassModel = M('bbs_class');
        $class = $ClassModel->where("id=$id")->find();

        $this->assign('subnav', $class['class_name'] . '版块');

        $this->display();
    }

    //我发布的帖子
    public function bbsMyPublishedTopics()
    {

        $this->assign('nav', '编教论坛');
        $this->assign('subnav', '我发布的帖子');

        $Model = M('bbs_topic');
        $result = $Model
            ->join('bbs_class on bbs_topic.class_id=bbs_class.id')
            ->field('bbs_topic.*,bbs_class.class_name')
            ->where("bbs_topic.creater_id=" . session('teacher.id'))->order('create_at desc')->select();
        $this->assign('list', $result);

        $this->display();
    }

    //我回复的帖子
    public function bbsMyReceivedTopics()
    {

        $this->assign('nav', '编教论坛');
        $this->assign('subnav', '我回复的帖子');

        $Model = M('bbs_reply');
        $result = $Model
            ->join('bbs_topic on bbs_reply.topic_id=bbs_topic.id')
            ->join('bbs_class on bbs_topic.class_id=bbs_class.id')
            ->field('bbs_reply.*,bbs_class.class_name,bbs_topic.view_count,bbs_topic.reply_count')
            ->where("bbs_reply.creater_id=" . session('teacher.id'))->order('create_at desc')->select();
        $this->assign('list', $result);

        $this->display();
    }


    /////////////////////////////班级行

    //班级管理
    public function classList()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '班级管理');
        $this->assign('subnav', '我的班级');
        $this->assign('navicon', 'banjiguanli');

        $Model = M('biz_class');
        //$teacherId = session("teacher.id");

        //$filter['grade_id'] = $_REQUEST['grade'];
        $filter['grade_id'] = getParameter('grade', 'int', false);

        if ($filter['grade_id'] == 0) { //如果没有选择班级 那么就把年级也设置为0
            $filter['class_id'] = 0;
        } else {
            //$filter['class_id'] = $_REQUEST['class'];
            $filter['class_id'] = getParameter('class', 'int', false);
        }

        if (!empty($filter['grade_id'])) $check['dict_grade.id'] = $filter['grade_id'];
        if (!empty($filter['class_id'])) $check['biz_class.id'] = $filter['class_id'];

        $this->assign('class_id_view', $filter['class_id']);

        $this->assign('default_grade', $check['dict_grade.id']);
        $this->assign('default_class', $check['biz_class.id']);
        $check['dest_teacherid'] = session("teacher.id");
        $this->assign('t_id_info', session("teacher.id"));
        $check['biz_class.flag'] = 2;

        if (!empty($filter['grade_id'])) $where['dict_grade.id'] = $filter['grade_id'];
        if (!empty($filter['class_id'])) $where['biz_class.id'] = $filter['class_id'];
        $where['biz_class.class_teacher_id'] = session("teacher.id");
        //$where['biz_class.flag'] =  1;

        $rlModel = M('biz_class_handsoff');
        $rlResult = $rlModel
            ->where($check)
            ->join('biz_class on biz_class.id = biz_class_handsoff.class_id')
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('biz_class_student on biz_class.id=biz_class_student.class_id and biz_class_student.status=2', 'left')
            ->join('auth_student on auth_student.id=biz_class_student.student_id', 'left')
            ->field('biz_class.*,count(auth_student.id) student_count,dict_schoollist.school_name,dict_grade.grade,dict_grade.id as did')
            ->group('biz_class.id')
            ->order('biz_class.create_at desc')
            ->select();

        for ($i = 0; $i < sizeof($rlResult); $i++)
            $rlResult[$i]['flag'] = 3;

        $result = $Model
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('biz_class_student on biz_class.id=biz_class_student.class_id and biz_class_student.status=2', 'left')
            ->join('auth_student on auth_student.id=biz_class_student.student_id', 'left')
            ->field('biz_class.*,count(auth_student.id) student_count,dict_schoollist.school_name,dict_grade.grade,dict_grade.id as did')
            ->where($where)
            ->group('biz_class.id')
            ->order('biz_class.create_at desc')
            //->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $result = array_merge($rlResult, $result);
        $this->assign('list', $result);
        //$this->assign('page', $show);
        //年级不为空求出班级
        if (!empty($filter['grade_id'])) {
            $class_model = M('biz_class');
            $classmap['grade_id'] = $filter['grade_id'];
            $classmap['class_teacher_id'] = session("teacher.id");
            $class_list = $class_model->where($classmap)->field('id,name')->select();
        }

        $grade_model = M('dict_grade');
        $grade_list = $grade_model->select();
        $this->assign('grade_list', $grade_list);
        $this->assign('class_list', $class_list);


        $this->display();
    }

    public function getListClass()
    {
        //$filter['grade_id'] = $_GET['grade_id'];
        $filter['grade_id'] = getParameter('grade_id', 'int');
        $class_model = M('biz_class');
        $classmap['grade_id'] = $filter['grade_id'];
        //$classmap['class_teacher_id'] = $_GET['class_teacher_id'];
        $classmap['class_teacher_id'] = getParameter('class_teacher_id', 'int');
        $class_list = $class_model->where($classmap)->select();
        $data['res'] = $class_list;
        $this->ajaxReturn($data, 'json');
    }

    public function transferClass()
    {
        if (!session('?teacher')) redirect(U('Index/index'));
        //TODO:1.verify identity
        if ($_POST) {
            /*
            $where['name'] = $_POST['name'];
            $where['telephone'] = $_POST['telephone'];
            $destClassId = $_POST['classId'];
            */
            $where['name'] = getParameter('name', 'str', false);
            $where['telephone'] = getParameter('telephone', 'str', false);
            $destClassId = getParameter('classId', 'int', false);

            $original_teahcer_school_id = session("teacher.school_id");
            $teacherModel = M('auth_teacher');

            $res = $teacherModel->where($where)->find();


            if (empty($res)) {
                $arr['msg'] = '该教师不存在';
                $arr['code'] = -1;
                echo json_encode($arr);
                die;
            } elseif ($res['school_id'] != $original_teahcer_school_id) {
                $arr['msg'] = '只能移交给本学校的老师';
                $arr['code'] = -1;
                echo json_encode($arr);
                die;
            } elseif ($res['id'] == session('teacher.id')) {
                $arr['msg'] = '不能移交给自己';
                $arr['code'] = -1;
                echo json_encode($arr);
                die;
            } else {
                //TODO:modify bizclass and add record to biz_class_handsoff
                $classModal = M('biz_class');
                $dataSave['flag'] = 2;
                $classModal->where("id=$destClassId")->save($dataSave);
                $classinfo = $classModal->where("id=$destClassId")->find();
                $namenianji = M('dict_grade')->where("id=" . $classinfo['grade_id'])->find();

                $allstu = D('Biz_class_student')->getClassStudent($classinfo['id']);

                $destTeacherId = $res['id'];
                $transferModal = M('biz_class_handsoff');
                $addData['dest_teacherid'] = $destTeacherId;
                $addData['class_id'] = $destClassId;

                $record = $transferModal->where($addData)->find();
                if (empty($record)) {
                    $transferModal->add($addData);

                    $parameters = array('msg' => array($where['name'] . "({$where['telephone']})"), 'url' => array('type' => 0));
                    A('Home/Message')->addPushUserMessage('CLASSMOVE_SENDER', 2, session('teacher.id'), $parameters);

                    //接收放数据

                }
                $rev_parameters = array('msg' => array($namenianji['grade'], $classinfo['name'], session('teacher.name'), session('teacher.telephone'), $classinfo['name']), 'url' => array('type' => 0));
                A('Home/Message')->addPushUserMessage('CLASSMOVE_RECEIVER', 2, $res['id'], $rev_parameters);
                $idsstring = '';

                foreach ($allstu as $k => $v) {
                    if ($k == 0) {
                        $idsstring .= $v['id'];
                    } else {
                        $idsstring .= ',' . $v['id'];
                    }
                    //给家长发
                    $parentparameters = array('msg' => array($v['student_name'], $namenianji['grade'], $classinfo['name'], $res['name'], $res['telephone']), 'url' => array('type' => 0));
                    A('Home/Message')->addPushUserMessage('CLASSMOVE_SEND_STUDENT_CHILD', 4, $v['parent_id'], $parentparameters);
                }
                //给学生移交发送信息
                $stuparameters = array('msg' => array($namenianji['grade'], $classinfo['name'], $res['name'], $res['telephone']), 'url' => array('type' => 0));
                A('Home/Message')->addPushUserMessage('CLASSMOVE_SEND_STUDENT', 3, $idsstring, $stuparameters);


                $arr['msg'] = '移交完成';
                $arr['code'] = 0;
                echo json_encode($arr);
                die;
            }

        }
    }

    public function undoClass()
    {
        if (!session('?teacher')) redirect(U('Index/index'));
        if ($_POST) {
            $transferModal = M('biz_class_handsoff');
            $classModel = M('biz_class');
            //$classId = $_POST['classid'];
            $classId = getParameter('classid', 'int');

            $delData['class_id'] = $classId;
            $queryResult = $transferModal->where($delData)->field('dest_teacherid')->find();

            $sqlResult = true;
            $transferModal->startTrans();
            $classModel->startTrans();
            $sqlResult &= $transferModal->where($delData)->delete();
            $dataSave['flag'] = 1;
            $sqlResult &= $classModel->where("id=$classId")->save($dataSave);
            if (true == $sqlResult) {
                $destTeacherId = $queryResult['dest_teacherid'];
                $transferModal->commit();
                $classModel->commit();
                $classInfo = $classModel->where("id=$classId")->field('class_teacher_id,name')->find();
                $sourceTeacherId = $classInfo['class_teacher_id'];
                $sourceTeacherInfo = D('Auth_teacher')->getTeachInfo($sourceTeacherId);
                $destTeacherInfo = D('Auth_teacher')->getTeachInfo($destTeacherId);

                $rev_parameters_receiver = array('msg' => array($sourceTeacherInfo['name'], $sourceTeacherInfo['telephone'], $classInfo['name']),
                    'url' => array('type' => 0));
                $rev_parameters_sender = array('msg' => array($destTeacherInfo['name'], $destTeacherInfo['telephone'], $classInfo['name']),
                    'url' => array('type' => 0));
                A('Home/Message')->addPushUserMessage('CLASSMOVEUNDO_SENDER', 2, $sourceTeacherId, $rev_parameters_sender);
                A('Home/Message')->addPushUserMessage('CLASSMOVEUNDO_RECEIVER', 2, $destTeacherId, $rev_parameters_receiver);
                //向家长推送
                //拿到这个班下的所有学生和家长
                $class_model = M('biz_class');
                $student_parent_result = $class_model->where('biz_class.id=' . $classId)->join('dict_grade on dict_grade.id=biz_class.grade_id')->join('biz_class_student on biz_class_student.class_id=biz_class.id and biz_class_student.status=2')
                    ->join('auth_student on auth_student.id=biz_class_student.student_id')->join('auth_parent on auth_parent.id=auth_student.parent_id', 'left')
                    ->field('auth_student.id,auth_student.student_name,auth_parent.id parent_id,auth_parent.parent_name')->select();

                $obj = A('Home/Message');
                if (!empty($student_parent_result)) {
                    $student_ids = '';
                    $where['biz_class.id'] = $classId;
                    $class_result = $class_model->where($where)->join('dict_grade on dict_grade.id=biz_class.grade_id')->field('biz_class.name,dict_grade.grade')->find();
                    foreach ($student_parent_result as $val) {
                        $student_ids .= $val['id'] . ',';
                        if ($val['parent_id'] != '') {
                            $parameter_arr = array(
                                'msg' => array($val['student_name'], $class_result['grade'], $class_result['name']),
                                'url' => array(
                                    'type' => 0,
                                    'data' => array()
                                )
                            );
                            $obj->addPushUserMessage('CLASSMOVEUNDO_PARENT', 4, $val['parent_id'], $parameter_arr);
                        }
                    }
                }
                $this->ajaxReturn(array('code' => 200));
            } else {
                $transferModal->rollback();
                $classModel->rollback();
                $this->ajaxReturn(array('code' => 500));
            }

        }
    }


    public function receiveClass()
    {
        if (!session('?teacher')) redirect(U('Index/index'));
        if ($_POST) {
            //TODO:remove record from biz_class_handsoff
            $transferModal = M('biz_class_handsoff');
            //$classId = $_POST['classid'];
            $classId = getParameter('classid', 'int', false);
            $allstu = D('Biz_class_student')->getClassStudent($classId);

            $delData['class_id'] = $classId;
            //这里先求出班级以前的老师ID
            $class_model = M('biz_class');
            $class_result = $class_model->where("id=" . "'$classId'")->find();
            $class_info_data = M('dict_grade')->where('id=' . $class_result['grade_id'])->find();

            if (empty($class_result)) {
                $returnData['code'] = 201;
                $this->ajaxReturn($returnData);
                die;
            }
            $original_teacher_id = $class_result['class_teacher_id'];

            $transferModal->startTrans();
            if (!$transferModal->where($delData)->delete()) {
                $returnData['code'] = 201;
                $this->ajaxReturn($returnData);
                die;
            }

            //TODO:change flag to 1 and change teacher id,name
            $classModal = M('biz_class');
            $teacherId = session('teacher.id');

            $teacherModal = M('auth_teacher');
            $res = $teacherModal->where("id=$teacherId")->find();

            $dataSave['flag'] = 1;
            $dataSave['class_teacher_id'] = $teacherId;
            $dataSave['class_teacher'] = $res['name'];

            if ($classModal->where("id=$classId")->save($dataSave) === false) {
                $transferModal->rollback();
                $returnData['code'] = 201;
                $this->ajaxReturn($returnData);
                die;
            }
            //这里去修改作业主表
            $homework_model = M('biz_homework');
            $homework_data['teacher_id'] = session('teacher.id');               //$original_teacher_id
            if ($homework_model->where("class_id=" . $classId . " and teacher_id=" . $original_teacher_id)->save($homework_data) === false) {
                $transferModal->rollback();
                $returnData['code'] = 201;
                $this->ajaxReturn($returnData);
                die;
            }
            //这里去修改课堂教师表
            $classroom_model = M('biz_classroom_information');
            $classroom_data['teacher_id'] = session('teacher.id');
            if ($classroom_model->where("class_id=" . $classId . " and teacher_id=" . $original_teacher_id)->save($classroom_data) === false) {
                $transferModal->rollback();
                $returnData['code'] = 201;
                $this->ajaxReturn($returnData);
                die;
            }
            $transferModal->commit();
            $returnData['code'] = 200;

            $rev_parameters = array('msg' => array($class_info_data['grade'], $class_result['name'], $res['name'], $res['telephone']), 'url' => array('type' => 0));
            A('Home/Message')->addPushUserMessage('CLASSMOVE_RECEIVE', 2, $original_teacher_id, $rev_parameters);
            A('Home/Message')->addPushUserMessage('CLASSMOVE_RECEIVER_SUCCESS', 2, $teacherId, $rev_parameters);

            $idsstring = '';

            foreach ($allstu as $k => $v) {
                if ($k == 0) {
                    $idsstring .= $v['id'];
                } else {
                    $idsstring .= ',' . $v['id'];
                }
                //给家长发
                $parentparameters = array('msg' => array($v['student_name'], $class_info_data['grade'], $class_result['name'], $res['name'], $res['telephone']), 'url' => array('type' => 0));
                A('Home/Message')->addPushUserMessage('CLASSMOVE_RECEIVE_STUDENT_CHILD', 4, $v['parent_id'], $parentparameters);
            }
            //给学生移交发送信息
            $stu_parameters = array('msg' => array($class_info_data['grade'], $class_result['name'], $res['name'], $res['telephone']), 'url' => array('type' => 0));
            A('Home/Message')->addPushUserMessage('CLASSMOVE_RECEIVE_STUDENT', 3, $idsstring, $stu_parameters);


            $this->ajaxReturn($returnData);
        }
    }

    //创建班级
    public function createClass()
    {

        if (!session('?teacher')) redirect(U('Index/index'));

        $isAuth = $this->isAuth($this->c_a);

        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        if ($_POST) {
            //$data['name'] = remove_xss($_POST['name']);
            //$pri_id = $_POST['grade_id'];
            $data['name'] = getParameter('name', 'str', false);
            $pri_id = getParameter('grade_id', 'int', false);

            $data['school_id'] = session('teacher.school_id');
            $data['class_teacher_id'] = session('teacher.id');
            $data['class_teacher'] = session('teacher.name');
            $data['create_at'] = time();

            $Model = M('dict_grade');
            $teacherId = session('teacher.id');
            $grades = $Model->where("auth_teacher_second.teacher_id=" . $teacherId . " and auth_teacher_second.grade_id=" . $pri_id)
                ->join("auth_teacher_second on auth_teacher_second.grade_id=dict_grade.id ")
                ->join("dict_course on dict_course.id=auth_teacher_second.course_id")
                ->field("auth_teacher_second.id pri_id,auth_teacher_second.course_id,dict_course.course_name,"
                    . "dict_grade.grade,dict_grade.id grade_id")->find();
            if (empty($grades)) {
                $arr['msg'] = '该年级不存在';
                $arr['code'] = -1;
                echo json_encode($arr);
                die;
            }
            //$data['course_id']=$grades['course_id'];
            $data['grade_id'] = $grades['grade_id'];

            //$where['course_id'] = $data['course_id'];
            $where['name'] = $data['name'];
            $where['grade_id'] = $data['grade_id'];
            $where['school_id'] = $data['school_id'];
            $where['class_teacher_id'] = session('teacher.id');

            $ResourceModel = M('biz_class');
            $res = $ResourceModel->where($where)->find();
            if (!empty($res)) //已经存在该班级
            {
                $arr['msg'] = '该班级已经存在';
                $arr['code'] = -1;
                echo json_encode($arr);
                die;
            }
            $ResourceModel->add($data);
            $arr['msg'] = '添加成功';
            $arr['code'] = 0;
            echo json_encode($arr);
            //$this->redirect("Teach/classList");

        } else {

            $this->assign('module', '班级行');
            $this->assign('nav', '班级管理');
            $this->assign('subnav', '创建班级');
            $this->assign('navicon', 'banjiguanli');

            //这里只能选择它自己注册时候填的年级和学科
            $Model = M('dict_grade');
            $teacherId = session('teacher.id');
            $grades = $Model->where("auth_teacher_second.teacher_id=" . $teacherId)
                ->join("auth_teacher_second on auth_teacher_second.grade_id=dict_grade.id")
                ->join("dict_course on dict_course.id=auth_teacher_second.course_id")
                ->field("auth_teacher_second.id pri_id,auth_teacher_second.id course_id,dict_course.course_name,"
                    . "dict_grade.grade,dict_grade.id grade_id")->select();
            $this->assign('grades', $grades);
            $singleResult = M('auth_teacher')->where("id=$teacherId")->find();
            $schoolId = $singleResult['school_id'];
            $classModel = M('biz_class');
            $grades = $classModel->where("school_id = $schoolId")->field('distinct name')->select();
            $this->assign('gradeList', $grades);
            $this->display();
        }
    }

    //编辑班级
    public function editClass()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $isAuth = $this->isAuth($this->c_a);

        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        if ($_POST) {
            $check['id'] = $_POST['id'];
            $data['name'] = remove_xss($_POST['name']);
            $data['grade_id'] = $_POST['grade_id'];
            $data['create_at'] = time();
            $ResourceModel = M('biz_class');
            $oldData = $ResourceModel->where($check)->find();
            $ResourceModel->where($check)->save($data);

            $gradeInfo = D('Dict_grade')->getGradeInfo($oldData['grade_id']);
            $parameters_teacher = array('msg' => array($gradeInfo['grade'], $oldData['name']),
                'url' => array('type' => 0)
            );
            A('Home/Message')->addPushUserMessage('CLASS_MODIFIED', 2, session('teacher.id'), $parameters_teacher);

            $parameters_student = array('msg' => array($gradeInfo['grade'], $oldData['name']),
                'url' => array('type' => 0)
            );

            //find students in this class
            $studentList = D('Biz_class_student')->getClassStudent($check['id']);
            A('Home/Message')->addPushUserMessage('CLASS_MODIFIED_STUDENT', 3, implode(',', array_column($studentList, 'id')), $parameters_student);

            $parentList = D('Biz_class_student')->getClassStudentParent($check['id']);
            foreach ($parentList as $parent) {
                $parameters_parent = array('msg' => array($parent['student_name'], $gradeInfo['grade'], $oldData['name']),
                    'url' => array('type' => 0)
                );
                A('Home/Message')->addPushUserMessage('CLASS_MODIFIED_STUDENT_CHILD', 4, $parent['id'], $parameters_parent);
            }


            $this->redirect("Teach/classList");

        } else {
            //$id = intval($_GET['classId']);
            $id = getParameter('classId', 'int', false);
            if (!$id) {
                redirect(U('Index/systemError'));
            }
            $this->assign('module', '班级行');
            $this->assign('nav', '班级管理');
            $this->assign('subnav', '修改班级');
            $this->assign('navicon', 'banjiguanli');

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            $Model = M('biz_class');
            $result = $Model
                ->where("id=$id")
                ->find();
            $this->assign('data', $result);

            $this->display();
        }
    }

    //学习轨迹管理
    public function learnPathList()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '学习轨迹');
        $this->assign('subnav', '学生学习轨迹');
        $this->assign('navicon', 'xuexiguiji');

        $filter['keyword'] = $_GET['keyword'];
        $filter['grade'] = getParameter('grade', 'int', false);
        $filter['class'] = getParameter('class', 'str', false);
        $pageIndex = getParameter('p', 'int', false);
        if (empty($pageIndex))
            $pageIndex = 1;
        if (!empty($filter['grade'])) $check['dict_grade.id'] = $filter['grade'];
        if (!empty($filter['class'])) $check['biz_class.name'] = $filter['class'];

        $model = D('Biz_class');
        $result = $model->getClassList(ROLE_TEACHER, session('teacher.id'), $pageIndex, 21, $filter['grade'], $filter['class'], $count, $availableGrade, $availableClassName);

        $Page = new \Think\Page($count, 21);
        $show = $Page->show();


        $this->assign('list', $result);
        $this->assign('page', $show);

        $this->assign('grade_code', $filter['grade']);
        $this->assign('class_code', $filter['class']);
        $this->assign('grade_list', $availableGrade);
        $this->assign('class_list', $availableClassName);
        $this->display();
    }

    //查看班级中的学生
    public function classStudentList()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $isAuth = $this->isAuth($this->c_a);

        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '班级管理');
        $this->assign('subnav', '班级学生');
        $this->assign('navicon', 'xuexiguiji');

        //$page = $_GET['page'];
        $page = getParameter('page', 'int', false);
        if (empty($page)) $page = 1;

        //$classId = $_GET['classId'];
        $classId = getParameter('classId', 'int', false);
        $this->assign('classId', $classId);

        $Model = M('biz_class_student');
        $result = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->join('biz_class on biz_class.id=biz_class_student.class_id')
            ->field('auth_student.*,biz_class_student.class_id')
            ->where("biz_class_student.class_id=$classId and biz_class_student.status=2")
            ->order('auth_student.student_name asc')
            ->page($page, 1000)
            ->select();
        $this->assign('student_count', count($result));
        $this->assign('list', $result);

        $ClassModel = M('biz_class');
        $class = $ClassModel
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_class.*,dict_schoollist.school_name,dict_grade.grade')
            ->where("biz_class.id=$classId")
            ->find();

        $this->assign('class', $class);

        $this->display();
    }

    //ajax方式查询班级的学生（带查询条件）
    public function ajax_classStudentList()
    {
        /*
        $classId = $_REQUEST['classId'];
        $name = $_REQUEST['name'];
        $sex = $_REQUEST['sex'];
        $studentId = $_REQUEST['student_id'];
        $parentTel = $_REQUEST['parent_tel'];
         */
        $classId = getParameter('classId', 'int', false);
        $name = getParameter('name', 'str', false);
        $sex = getParameter('sex', 'str', false);
        $studentId = getParameter('student_id', 'int', false);
        $parentTel = getParameter('parent_tel', 'str', false);

        $check['biz_class_student.status'] = 2;
        $check['biz_class_student.class_id'] = $classId;
        if (!empty($name)) $check['auth_student.student_name'] = array('like', '%' . $name . '%');
        if (!empty($sex)) $check['auth_student.sex'] = $sex;
        if (!empty($studentId)) $check['auth_student.student_id'] = array('like', '%' . $studentId . '%');
        if (!empty($parentTel)) $check['auth_student.parent_tel'] = array('like', $parentTel . '%');

        $Model = M('biz_class_student');
        $result = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->join('biz_class on biz_class.id=biz_class_student.class_id')
            ->field('auth_student.*,biz_class_student.class_id')
            ->where($check)
            ->order('auth_student.student_name asc')
            ->select();

        $this->ajaxReturn($result);
    }

    //管理班级中的学生
    public function classStudentMgmt()
    {

        if (!session('?teacher')) redirect(U('Index/index'));

        $isAuth = $this->isAuth($this->c_a);

        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '班级管理');
        $this->assign('subnav', '班级学生管理');
        $this->assign('navicon', 'banjiguanli');

        //$page = $_REQUEST['page'];
        $page = getParameter('page', 'int', false);
        if (empty($page)) $page = 1;
        $this->assign('page', $page);

        //$classId = $_GET['classId'];
        $classId = getParameter('classId', 'int', false);
        if (!$classId) {
            redirect(U('Index/systemError'));
        }
        $this->assign('classId', $classId);

        $Model = M('biz_class_student');
        $result = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->join('biz_class on biz_class.id=biz_class_student.class_id')
            ->field('auth_student.birth_date,auth_student.id,auth_student.sex,auth_student.email,auth_student.id_card,auth_student.student_name,auth_student.parent_tel,auth_student.student_id,auth_student.user_name,auth_student.avatar,biz_class_student.class_id,biz_class_student.status')
            ->where("biz_class_student.class_id=$classId and biz_class_student.status <> 3")
            ->order('biz_class_student.create_at desc')
            ->page($page, 1000)//班级学生全显示
            ->select();


        for ($i = 0; $i < sizeof($result); $i++) {
            $result[$i]['birth_date'] = date("Y-m-d", $result[$i]['birth_date']);
            if (empty($result[$i]['avatar']) || $result[$i]['avatar'] == '') {
                $result[$i]['avatar'] = 'default.jpg';
            }
        }


        $this->assign('list', $result);

        $ClassModel = M('biz_class');
        $class = $ClassModel
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_class.*,dict_schoollist.school_name,dict_grade.grade')
            ->where("biz_class.id=$classId")
            ->find();

        $this->assign('class', $class);
        $this->assign('classId', $classId);
        $this->display_nocache();
    }

    /**
     * 学生列表页  没用了
     */
    public function studentlist()
    {
        //if (!session('?teacher')) redirect(U('Index/index'));

        //$classId = $_GET['classId'];
        $classId = getParameter('classId', 'int', false);
        $this->assign('classId', $classId);

        $Model = M('biz_class_student');
        $result = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->join('biz_class on biz_class.id=biz_class_student.class_id')
            ->field('auth_student.id,auth_student.id_card,auth_student.student_name,auth_student.parent_tel,auth_student.student_id,auth_student.user_name,auth_student.avatar,biz_class_student.class_id,biz_class_student.status')
            ->where("biz_class_student.class_id=$classId")
            ->order('biz_class_student.create_at desc')
            ->page(1, 1000)//班级学生全显示
            ->select();
        $result = json_encode($result);
        $this->assign('list', $result);
        //print_r($result);exit;
        $this->display();
    }

    //学生错误集列表
    function wrongHomeworkList()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $isAuth = $this->isAuth($this->c_a);

        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }
        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '错题集');
        $this->assign('navicon', 'zuoyexitong');
        $mca = I('mca');
        if ($mca == 'action') {
            $this->assign('kw', 1);
        }
        if (I('grade') > 0) {
            $where['dict_grade.id'] = intval(I('grade'));
        }
        if (I('class') > 0) {
            $class_id = intval(I('class'));
            //这里求出班级
            $class_model = M('biz_class');
            $class_result = $class_model->where("id=" . $class_id)->field('name')->find();
            if (empty($class_result)) {
                redirect(U('Index/systemError'));
            } else {
                $class_name = $class_result['name'];
                //$where['biz_class.name']=$class_name;
                $where['biz_class.id'] = $class_id;
            }
        }
        if (I('course') > 0) {
            $where['dict_course.id'] = intval(I('course'));
        }
        if (I('subject') > 0) {
            $where['biz_textbook.id'] = intval(I('subject'));
        }
        if (I('type') > 0) {
            if (I('type') == 1) {
                $where['biz_homework.homework_type'] = '课堂作业';
            } elseif (I('type') == 2) {
                $where['biz_homework.homework_type'] = '课后作业';
            }
        }
        if (I('keyword')) {
            $where['biz_homework.homework_name'] = array('like', '%' . I('keyword') . '%');
        }
        if (I('sort')) {
            $sort = intval(I('sort'));
            if ($sort == 1) {
                $sort_string = "asc";
            } else if ($sort == 2) {
                $sort_string = "desc";
            } else {
                $sort_string = "asc";
            }
        } else {
            $sort_string = "asc";
            $sort = 1;
        }

        $where['biz_homework.homework_status'] = 1;
        $where['biz_homework.teacher_id'] = session("teacher.id");
        //$alone_where['biz_homework.teacher_id']=session("teacher.id");
        $alone_where['biz_class.class_teacher_id'] = session("teacher.id");
        $where['biz_class.is_delete'] = 0;

        $Model = M('biz_homework');

        $count_temp_sql = $Model
            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_homework.class_id')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
            ->join('dict_course on dict_course.id=biz_homework.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
            ->join('biz_homework_student_details detail on detail.homework_id=biz_homework.id')
            ->field('biz_homework.id,biz_homework.exercises_number,biz_homework.completed_number')
            ->where($where)
            ->group("biz_homework.id")
            ->order('biz_homework.create_at desc')
            ->select(false);

        $temp_sql = $Model
            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_homework.class_id')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
            ->join('dict_course on dict_course.id=biz_homework.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
            ->join('biz_homework_student_details detail on detail.homework_id=biz_homework.id')
            ->field('biz_homework.*,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,dict_grade.grade,count(detail.id) total_number')
            ->where($where)
            ->group("biz_homework.id")
            ->order('biz_homework.create_at desc')
            ->select(false);

        $right_model = M('biz_homework_score_details');
        $right_sql = $right_model->where("flag!=1")->field('homework_id')->group('homework_id')->select(false);

        for ($j = 0; $j < 2; $j++) {
            if ($j == 0) {
                $sql = "select ROUND((count(temp1.homework_id)/(temp2.exercises_number * temp2.completed_number) ) * 100) percent,temp2.* FROM `biz_homework_score_details` temp1 "
                    . "join (" . $right_sql . ") temp3 on temp1.homework_id=temp3.homework_id"
                    . " join (" . $count_temp_sql . ")" . "temp2 on temp2.id=temp1.homework_id GROUP BY temp2.id" . " having  percent IS not NULL ";

                $count = $Model->query($sql);
                $count = count($count);
                $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
            } else {
                $sql = "select ROUND((count(temp1.homework_id)/(temp2.exercises_number * temp2.completed_number) ) * 100) percent,temp2.* FROM `biz_homework_score_details` temp1 "
                    . "join (" . $right_sql . ") temp3 on temp1.homework_id=temp3.homework_id and (flag != 1) "
                    . " join (" . $temp_sql . ")" . "temp2 on temp2.id=temp1.homework_id GROUP BY temp2.id" . " having  percent IS not NULL ORDER BY percent " . $sort_string . " limit " . $Page->firstRow . ',' . $Page->listRows;
                $result = $Model->query($sql);
            }
        }


        $show = $Page->show();
        $this->assign('page', $show);


        $Model = M('biz_exercise_library_chapter');
        $score_model = M('biz_homework_score_details');
        foreach ($result as $key => $val) {
            $info = $Model->where("u.id=" . $val['id'])//where("biz_exercise_library_chapter.textbook_id=".$val['textbook_id'])
            ->join("biz_homework_exercise t on t.chapter_id=biz_exercise_library_chapter.id")
                ->join("biz_homework u on u.id=t.homework_id")
                ->group("t.chapter_id")->field("chapter,festival,biz_exercise_library_chapter.id")->select();

            $result[$key]['chapter'] = $info;
        }
        $this->assign('list', $result);

        $grade_model = M('dict_grade');
        $class_model = M('biz_class');
        $course_model = M('dict_course');
        $biz_textbook_mode = M('biz_textbook');

        $grade_result = $class_model->where($alone_where)->field('dict_grade.id,dict_grade.grade')->join('dict_grade on dict_grade.id=biz_class.grade_id')->group("dict_grade.id")->select();
        //$class_result=$class_model->where($alone_where)->field('biz_class.id,biz_class.name')->join('biz_homework on biz_homework.class_id=biz_class.id')->group("biz_class.name")->select();
        //$course_result=$course_model->where($alone_where)->field('dict_course.id,dict_course.course_name')->join('biz_homework on biz_homework.course_id=dict_course.id')->group("dict_course.id")->select();
        //$textbook_result=$biz_textbook_mode->where($alone_where)->field('biz_textbook.id,biz_textbook.name')->join('biz_homework on biz_homework.textbook_id=biz_textbook.id')->group("biz_textbook.id")->select();
        //年级不为空,求出班级和学科
        if (!empty($where['dict_grade.id'])) {
            $class_model = M('biz_class');
            $class_result = $class_model->where("grade_id=" . $where['dict_grade.id'] . " and class_teacher_id=" . session('teacher.id'))->field('biz_class.id,biz_class.name')
                ->group("biz_class.name")->select();

            $course_model = M('dict_course');
            $course_result = $course_model->where("auth_teacher_second.grade_id=" . $where['dict_grade.id'])
                ->field('dict_course.id,dict_course.course_name')
                ->join("auth_teacher_second on auth_teacher_second.teacher_id=" . session('teacher.id') . " and auth_teacher_second.course_id=dict_course.id")->group("dict_course.id")
                ->select();
        }

        //年级和学科不为空,求出教材分册
        if (!empty($where['dict_grade.id']) && !empty($where['dict_course.id'])) {
            $course_model = M('biz_textbook');
            $textbook_result = $course_model->where("grade_id=" . $where['dict_grade.id'] . " and course_id=" . $where['dict_course.id'])
                ->field('id,name')->select();
        }

        $this->assign('grade_list', $grade_result);
        $this->assign('class_list', $class_result);
        $this->assign('course_list', $course_result);
        $this->assign('textbook_list', $textbook_result);

        $this->assign('default_grade', $where['dict_grade.id']);
        $this->assign('default_class', $class_id);
        $this->assign('default_course', $where['dict_course.id']);
        $this->assign('default_textbook', $where['biz_textbook.id']);
        $this->assign('default_type', intval(I('type')));
        $this->assign('keyword', I('keyword'));
        $this->assign('default_sort', $sort);

        $this->display();
    }

    //根据习题来获得相关信息
    function getExerciseData()
    {
        $id_array = I('exercises_id');
        $id_array = array_unique($id_array);
        $ids = implode(',', $id_array);
        $ids = '(' . $ids . ')';
        $Model = M('biz_exercise_library_chapter');
        $exercise_result = $Model->join('biz_exercise_template tt on biz_exercise_library.type=tt.id')
            ->field('biz_exercise_library.id questions_primary_id,tt.*,biz_exercise_library.*,11')
            ->where("biz_exercise_library.id in " . $ids)
            ->order('biz_exercise_library.id asc')
            ->select();
        echo json_encode($exercise_result);
    }


    //错误题详情
    function wrongHomeworkDetail()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '错题集');
        $this->assign('navicon', 'zuoyexitong');


        $use_tag = 1;
        //这里接收名称和那个错误率
        //$homework_name=I('keyword');
        //$student_id=intval(I('student_id'));
        $homework_name = $_REQUEST['keyword'];
        $student_id = getParameter('student_id', 'int', false);

        $date = I('date');
        //$id=intval(I('id'));
        $id = getParameter('id', 'int', false);
        if (!empty($date)) {
            $other_where["_string"] = "biz_homework.create_at>=" . strtotime(I("date")) . " and " . "biz_homework.create_at<=" . (strtotime(I("date") . "+1 day") - 1);
            $this->assign('default_date', $date);
        }
        if (!empty($homework_name) || $student_id) {
            if ($student_id) {
                $other_where['biz_class_student.student_id'] = $student_id;
                $this->assign('student_id', $student_id);
            }
            if (!empty($homework_name)) {
                $other_where['homework_name'] = array('like', '%' . $homework_name . '%');
                $this->assign('homework_name', $homework_name);
            }
            $use_tag = 2;
        }
        if ($use_tag == 2) {
            $other_where['biz_homework.teacher_id'] = session('teacher.id');
            if (empty($other_where['_string'])) {
                $other_where['_string'] = 'biz_homework_score_details.flag!=1';
            } else {
                $other_where['_string'] .= ' and biz_homework_score_details.flag!=1';
            }
            $Model = M('biz_homework_score_details');
            $result = $Model->where($other_where)
                ->join('biz_exercise_library on biz_exercise_library.id=biz_homework_score_details.question_org_id')
                ->join("biz_homework on biz_homework.id=biz_homework_score_details.homework_id")
                ->join("biz_class_student on biz_class_student.class_id=biz_homework.class_id and biz_homework_score_details.student_id=biz_class_student.student_id")
                ->field("distinct biz_homework_score_details.*,biz_exercise_library.questions,biz_exercise_library.chapter_id,biz_exercise_library.answer,biz_homework.homework_name as homework,biz_homework.create_at,"
                    . "biz_homework.completed_number")
                ->order('biz_homework.create_at desc')
                ->select();

        }
        if ($use_tag == 1) {
            $sort = 0;
            $sort = intval(I('sort'));
            if (!$id) {
                redirect(U('Index/systemError'));
            }
            if ($sort) {
                if ($sort == 1) {
                    $sort_string = "order by percent desc";
                } else {
                    $sort_string = "order by percent";
                }
            } else {
                $sort = 0;
                $sort_string = "order by percent";
            }
            $this->assign('sort_order', $sort);

            //错误率
            $having = "having percent<=100 and percent is not null";
            if (I('rate') > 0) {
                $rage = intval(I('rate'));
                $this->assign('default_sort', $rage);
                switch ($rage) {
                    case 1:
                        $having = "having percent<=100 and percent is not null and percent<=10";
                        break;
                    case 2:
                        $having = "having percent<=100 and percent is not null and percent>10 and percent<=20";
                        break;
                    case 3:
                        $having = "having percent<=100 and percent is not null and percent>20 and percent<=30";
                        break;
                    case 4:
                        $having = "having percent<=100 and percent is not null and percent>30 and percent<=40";
                        break;
                    case 5:
                        $having = "having percent<=100 and percent is not null and percent>40 and percent<=50";
                        break;
                    case 6:
                        $having = "having percent<=100 and percent is not null and percent>50 and percent<=60";
                        break;
                    case 7:
                        $having = "having percent<=100 and percent is not null and percent>60 and percent<=70";
                        break;
                    case 8:
                        $having = "having percent<=100 and percent is not null and percent>70 and percent<=80";
                        break;
                    case 9:
                        $having = "having percent<=100 and percent is not null and percent>80 and percent<=90";
                        break;
                    case 10:
                        $having = "having percent<=100 and percent is not null and percent>90";
                        break;
                }
            }

            $where['biz_homework.id'] = $id;
            $where['biz_homework.teacher_id'] = session("teacher.id");
            $Model = M('biz_homework_score_details');
            $right_sql = $Model
                ->join("biz_exercise_library on biz_exercise_library.id=biz_homework_score_details.question_org_id")
                ->join("biz_homework on biz_homework.id=biz_homework_score_details.homework_id")
                ->field("distinct biz_homework_score_details.*,biz_exercise_library.questions,biz_exercise_library.chapter_id,biz_exercise_library.answer,biz_homework.homework_name as homework,biz_homework.create_at,"
                    . "biz_homework.completed_number,count(question_org_id) all_question_number")
                ->where($where)
                ->group("question_org_id")
                ->order('biz_exercise_library.id asc')
                ->select(false);

            $middle_sql = $Model->where("flag!=1")->field('homework_id,question_org_id,student_id')->select(false);
            $sql = "SELECT ROUND(count(temp1.homework_id) / temp3.all_question_number*100) percent,temp1.homework_id,temp3.* FROM	`biz_homework_score_details` temp1"
                . " join(" . $middle_sql . ")temp2 on temp1.question_org_id=temp2.question_org_id and temp1.student_id=temp2.student_id and temp1.homework_id=temp2.homework_id "
                . "join(" . $right_sql . ")temp3 on temp2.homework_id=temp3.homework_id and temp3.question_org_id=temp2.question_org_id  GROUP BY temp1.question_org_id  " . $having . ' ' . $sort_string;
            $result = $Model->query($sql);

        }
        //根据作业ID来判断
        if (!empty($id)) {
            //得到该班级的所有学生
            $homework_model = M('biz_homework');
            $class_student = $homework_model->where("biz_homework.id=" . "'$id'")->field(' biz_homework.id homework_id,biz_class_student.class_id,biz_class_student.student_id,auth_student.student_name')
                ->join("biz_class_student on biz_homework.class_id=biz_class_student.class_id")
                ->join("auth_student on auth_student.id=biz_class_student.student_id")
                ->group("biz_class_student.student_id")->select();
        } else {
            //求出该班级的学生
            $Model = M('biz_class_student');
            $temp_class = $Model->where("biz_class_student.student_id=" . "'$student_id'")->field('biz_class_student.class_id')->find();
            $class_id = $temp_class['class_id'];

            $class_student = $Model->where("biz_class_student.class_id=" . "'$class_id'")->join("biz_class on biz_class.id=biz_class_student.class_id")
                ->join("auth_student on auth_student.id=biz_class_student.student_id")
                ->field("auth_student.id student_id,auth_student.student_name")->select();
        }


        $this->assign('class_student', $class_student);


        $this->assign('homework_id', $id);
        $this->assign('use_tag', $use_tag);
        $this->assign('list', $result);
        $this->assign('sort', $sort);

        $this->display();
    }

    //学生错题集
    function wrongHomework()
    {
        //$studentId = $_GET['id'];
        $studentId = getParameter('id', 'int', false);
        $this->assign('id', $studentId);

        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '错题集');
        $this->assign('navicon', 'zuoyexitong');

        //$filter['course_id'] = $_REQUEST['course_id'];
        //$filter['grade_id'] = $_REQUEST['grade_id'];
        //$filter['textbook_id'] = $_REQUEST['textbook_id'];

        $filter['course_id'] = getParameter('course_id', 'int', false);
        $filter['grade_id'] = getParameter('grade_id', 'int', false);
        $filter['textbook_id'] = getParameter('textbook_id', 'int', false);

        $check['biz_homework_score_details.student_id'] = $studentId;
        $check['biz_homework_score_details.flag'] = array('neq', 1);
        if (!empty($filter['course_id'])) $check['biz_homework.course_id'] = $filter['course_id'];
        if (!empty($filter['grade_id'])) $check['biz_homework.grade_id'] = $filter['grade_id'];
        if (!empty($filter['textbook_id'])) $check['biz_homework.textbook_id'] = $filter['textbook_id'];

        $Model = M('biz_homework_score_details');

        $count = $Model
            ->join("biz_exercise_library on biz_exercise_library.id=biz_homework_score_details.question_org_id")
            ->join("biz_homework on biz_homework.id=biz_homework_score_details.homework_id")
            ->where($check)
            ->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($filter as $key => $val) {
            //$Page->parameter[$key] = urlencode($val);
            $Page->parameter[$key] = $val;
        }
        $show = $Page->show();

        $result = $Model
            ->join("biz_exercise_library on biz_exercise_library.id=biz_homework_score_details.question_org_id")
            ->join("biz_homework on biz_homework.id=biz_homework_score_details.homework_id")
            //->join("biz_homework_student_details on biz_homework.id=biz_homework_student_details.homework_id")
            ->field("distinct biz_homework_score_details.*,biz_exercise_library.questions,biz_exercise_library.chapter_id,biz_homework.homework_name as homework,biz_homework.create_at")
            ->where($check)
            ->order('biz_homework.create_at desc')
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

        $TextbookModel = M('biz_textbook');
        $c1['course_id'] = $filter['course_id'];
        $c1['grade_id'] = $filter['grade_id'];
        $textbooks = $TextbookModel->where($c1)->select();
        $this->assign('textbooks', $textbooks);

        $this->assign('course_id', $filter['course_id']);
        $this->assign('grade_id', $filter['grade_id']);
        $this->assign('textbook_id', $filter['textbook_id']);

        $this->display();
    }

    //同意学生加入
    public function approveStudentInClass()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$student_id = $_GET['student_id'];
        //$class_id = $_GET['class_id'];
        $student_id = getParameter('student_id', 'int');
        $class_id = getParameter('class_id', 'int');

        $check['student_id'] = $student_id;
        $check['class_id'] = $class_id;

        $data['status'] = 2;

        $Model = M('biz_class_student');
        $Model->where($check)->save($data);

        //学生数加1
        $Model = M('biz_class');
        $Model->where("id=$class_id")->setInc('student_count', 1);

        $classInfo = D('Biz_class')->getClassInfo($class_id);
        $gradeInfo = D('Dict_grade')->getGradeInfo($classInfo['grade_id']);
        $parameters = array('msg' => array($gradeInfo['grade'], $classInfo['name']),
            'url' => array('type' => 0)
        );
        A('Home/Message')->addPushUserMessage('JOINCLASS_SUCCESS', 3, $student_id, $parameters);

        $this->ajaxReturn("success");
    }

    //拒绝学生加入
    public function declineStudentInClass()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$student_id = $_GET['student_id'];
        //$class_id = $_GET['class_id'];
        $student_id = getParameter('student_id', 'int');
        $class_id = getParameter('class_id', 'int');

        $check['student_id'] = $student_id;
        $check['class_id'] = $class_id;

        $data['status'] = 3;

        $Model = M('biz_class_student');
        $Model->where($check)->save($data);

        $classInfo = D('Biz_class')->getClassInfo($class_id);
        $gradeInfo = D('Dict_grade')->getGradeInfo($classInfo['grade_id']);
        $parameters = array('msg' => array($gradeInfo['grade'], $classInfo['name']),
            'url' => array('type' => 0)
        );
        A('Home/Message')->addPushUserMessage('JOINCLASS_FAILED', 3, $student_id, $parameters);

        $this->ajaxReturn("success");
    }

    //移出班级
    public function denyStudentInClass()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$student_id = $_GET['student_id'];
        //$class_id = $_GET['class_id'];
        $student_id = getParameter('student_id', 'int');
        $class_id = getParameter('class_id', 'int');

        $check['student_id'] = $student_id;
        $check['class_id'] = $class_id;

        $Model = M('biz_class_student');
        $Model->where($check)->delete();

        $Model = M('biz_class');
        $Model->where("id=$class_id")->setDec('student_count', 1);

        $classInfo = D('Biz_class')->getClassInfo($class_id);
        $gradeInfo = D('Dict_grade')->getGradeInfo($classInfo['grade_id']);
        $parameters = array('msg' => array($gradeInfo['grade'], $classInfo['name']),
            'url' => array('type' => 0)
        );
        A('Home/Message')->addPushUserMessage('EXITCLASS', 3, $student_id, $parameters);
        $this->ajaxReturn('success');
    }

    //查看班级课表
    public function classTimetable()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $isAuth = $this->isAuth($this->c_a);

        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '班级管理');
        $this->assign('navicon', 'banjiguanli');

        //$classId = intval($_GET['classId']);
        $classId = getParameter('classId', 'int', false);

        if (!$classId) {
            redirect(U('Index/systemError'));
        }

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

    public function editTimetable()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if ($_POST) {
            //$classId = $_POST['class_id'];
            $classId = getParameter('class_id', 'int', false);
            $check['class_id'] = getParameter('class_id', 'int', false);

            $data['class_id'] = $classId;

            $is_pan = $_POST['is_pan'];
            if ($is_pan == 1) {
                $data['content'] = $_POST['content'];
                $data['comments'] = getParameter('comments', 'str', false);
            } else {
                $data['content_teacher'] = $_POST['content'];
                $data['comments_teacher'] = getParameter('comments', 'str', false);
            }

            $data['create_at'] = time();
            $data['update_at'] = time();

            $Model = M('biz_class_timetable');

            $table = $Model->where("class_id=$classId")->find();

            if (empty($table)) {
                $Model->add($data);
            } else {
                $Model->where($check)->save($data);

                $class_id = $check['class_id'];
                $class_model = M('biz_class');
                $where['biz_class.id'] = $class_id;
                $class_result = $class_model->where($where)->join('dict_grade on dict_grade.id=biz_class.grade_id')->field('biz_class.name,dict_grade.grade')->find();
                if (!empty($class_result)) {
                    $teacher_id = session('teacher.id');

                    //拿到这个班下的所有学生和家长
                    $student_parent_result = $class_model->where('biz_class.id=' . $class_id)->join('dict_grade on dict_grade.id=biz_class.grade_id')->join('biz_class_student on biz_class_student.class_id=biz_class.id and biz_class_student.status=2')
                        ->join('auth_student on auth_student.id=biz_class_student.student_id')->join('auth_parent on auth_parent.id=auth_student.parent_id', 'left')
                        ->field('auth_student.id,auth_student.student_name,auth_parent.id parent_id,auth_parent.parent_name')->select();

                    $obj = A('Home/Message');
                    if (!empty($student_parent_result)) {
                        $student_ids = '';

                        foreach ($student_parent_result as $val) {
                            $student_ids .= $val['id'] . ',';
                            if ($val['parent_id'] != '') {
                                $parameter_arr = array(
                                    'msg' => array($val['student_name'], $class_result['grade'], $class_result['name']),
                                    'url' => array(
                                        'type' => 0,
                                        'data' => array()
                                    )
                                );
                                $obj->addPushUserMessage('CLASSTABLE_MODIFIED_STUDENT_CHILD', 4, $val['parent_id'], $parameter_arr);
                            }
                        }
                        //学生推送
                        $student_ids = rtrim($student_ids, ',');
                        $parameter_arr = array(
                            'msg' => array($class_result['grade'], $class_result['name']),
                            'url' => array(
                                'type' => 0,
                                'data' => array()
                            )
                        );
                        $obj->addPushUserMessage('CLASSTABLE_MODIFIED_STUDENT', 3, $student_ids, $parameter_arr);
                    }

                    //教师推送
                    $parameter_arr = array(
                        'msg' => array($class_result['grade'], $class_result['name']),
                        'url' => array(
                            'type' => 0,
                            'data' => array()
                        )
                    );
                    $obj->addPushUserMessage('CLASSTABLE_MODIFIED', 2, $teacher_id, $parameter_arr);
                }
            }
            $big = I('big');
            if ($big == 1) {
                $this->redirect("Teach/classTimetableInner?classId=$classId&status=1");
            } elseif ($big == 2) {
                $this->redirect("Teach/classTimetableInnerTeach?classId=$classId&status=1");
            }

        }
    }

    public function classTimetableInner()
    {
        //$classId = $_GET['classId'];
        //$status = $_GET['status'];
        $classId = getParameter('classId', 'int', false);
        $status = getParameter('status', 'int', false);

        $teacherId = session('teacher.id');
        $this->assign('teacherId', $teacherId);
        $this->assign('classId', $classId);
        $this->assign('status', $status);

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

        $map['teacher_id'] = session('teacher.id');
        $dict_list = M('auth_teacher_second')
            ->join('dict_course on dict_course.id=auth_teacher_second.course_id')
            ->field('dict_course.id,dict_course.course_name')
            ->group('dict_course.id')
            ->where($map)->select();

        $class_data = M('biz_class')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_class.name,dict_grade.grade,biz_class.flag')
            ->where("biz_class.id=$classId")
            ->find();

        $this->assign('dict_list', $dict_list);

        $this->assign('class_data_info', $class_data);

        $this->display();
    }

    public function classTimetableInnerSchool()
    {
        //$classId = $_GET['classId'];
        //$status = $_GET['status'];
        $classId = getParameter('classId', 'int', false);
        $status = getParameter('status', 'int', false);

        $teacherId = session('teacher.id');
        $this->assign('teacherId', $teacherId);
        $this->assign('classId', $classId);
        $this->assign('status', $status);

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

        $map['teacher_id'] = session('teacher.id');
        $dict_list = M('auth_teacher_second')
            ->join('dict_course on dict_course.id=auth_teacher_second.course_id')
            ->field('dict_course.id,dict_course.course_name')
            ->group('dict_course.id')
            ->where($map)->select();

        $class_data = M('biz_class')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_class.name,dict_grade.grade')
            ->where("biz_class.id=$classId")
            ->find();

        $this->assign('dict_list', $dict_list);
        $this->assign('class_data_info', $class_data);

        $this->display();
    }

    public function classTimetableInnerTeach()
    {
        //$classId = $_GET['classId'];
        //$status = $_GET['status'];
        $classId = getParameter('classId', 'int', false);
        $status = getParameter('status', 'int', false);

        $this->assign('classId', $classId);
        $this->assign('status', $status);

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

        $class_data = M('biz_class')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_class.name,dict_grade.grade,biz_class.flag')
            ->where("biz_class.id=$classId")
            ->find();

        $this->assign('class_data_info', $class_data);

        $this->display();
    }

    public function classTimetableInnerTeachSchool()
    {
        //$classId = $_GET['classId'];
        //$status = $_GET['status'];
        $classId = getParameter('classId', 'int', false);
        $status = getParameter('status', 'int', false);

        $this->assign('classId', $classId);
        $this->assign('status', $status);

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


    //学生轨迹
    public function learningPath()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '学习轨迹');
        $this->assign('navicon', 'xuexiguiji');


        //$studentId = $_GET['id'];
        //$classId = $_GET['class_id'];
        $studentId = getParameter('id', 'int', false);
        $classId = getParameter('class_id', 'int', false);

        //如果班级是校建班级 判断班级是否停用

        $school_status = D('Biz_classList')->getSchoolClassStatus($classId);

        $this->assign('school_status', $school_status);

        $this->assign('classId', $classId);

        $Model = M('biz_student_learning_path');

        $path = $Model
            ->join('left join biz_class on biz_class.id=biz_student_learning_path.class_id')
            ->join('left join dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('left join dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_student_learning_path.*,dict_schoollist.school_name,dict_grade.grade,biz_class.name as class_name')
            ->where("biz_student_learning_path.student_id=$studentId")
            ->order('create_at desc')
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

    //
    public function addLearningPath()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if ($_POST) {

            if (!session('?teacher')) {
                redirect(U('Index/index'), 0, '登录超时，请重新登录...');
            }

            $Model = M('biz_student_learning_path');
            //$data['student_id'] = $_POST['student_id'];
            $data['student_id'] = getParameter('student_id', 'int', false);
            $data['teacher_id'] = session('teacher.id');
            /*$data['item_name'] = remove_xss($_POST['item_name']);
            $data['class_id'] = $_POST['class_id'];
            $data['content'] = remove_xss($_POST['description']);
            $data['type'] = remove_xss($_POST['type']);
             */
            $data['item_name'] = getParameter('item_name', 'str', false);
            $data['class_id'] = getParameter('class_id', 'int', false);
            $data['content'] = getParameter('description', 'str', false);
            $data['type'] = getParameter('type', 'str', false);

            $data['create_at'] = time();
            $recordId = $Model->add($data);
            $studentInfo = D('Auth_student')->getStudentInfo($data['student_id']);
            //手机推送
            $parameter_arr = array(
                'msg' => array($studentInfo['student_name'], session('teacher.name'), $studentInfo['student_name'], $data['item_name']),
                'url' => array(
                    'type' => 0,
                    'data' => array()
                )
            );
            A('Home/Message')->addPushUserMessage('STUDENT_EXPRESSION_CHILD', 4, $studentInfo['parent_id'], $parameter_arr);

            $parameters = array('msg' => array(session('teacher.name'), $data['item_name']),
                'url' => array('type' => 0)
            );
            A('Home/Message')->addPushUserMessage('STUDENT_EXPRESSION', 3, $data['student_id'], $parameters);

            $this->redirect("Teach/learningPath?id=" . $data['student_id'] . "&class_id=" . $data['class_id']);
        } else {
            $this->assign('module', '班级行');
            $this->assign('nav', '学习轨迹');
            $this->assign('navicon', 'xuexiguiji');

            $studentId = $_GET['id'];
            $classId = $_GET['class_id'];

            $studentId = getParameter('id', 'str', false);
            $classId = getParameter('class_id', 'str', false);

            $where['id'] = $studentId;
            $this->assign('classId', $classId);

            $StudentModel = M('auth_student');
            $student = $StudentModel
                ->where($where)
                ->find();

            $this->assign('student', $student);

            $this->assign('subnav', '增加' . $student['student_name'] . '同学的学习轨迹');

            $this->display();
        }
    }

    //删除备课课件
    public function deleteLearnPath()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$id = $_GET['id'];
        $id = getParameter('id', 'int');

        $ResourceModel = M('biz_student_learning_path');
        $where['id'] = $id;
        $pathInfo = $ResourceModel->where($where)->field('student_id,item_name')->find();

        if (true == $ResourceModel->where($where)->delete()) {
            $studentInfo = D('Auth_student')->getStudentInfoByStuId($pathInfo['student_id']);
            $parentIds = D('Auth_student')->getParentList(array($pathInfo['student_id']));
            $parameter_arr = array(
                'msg' => array(session('teacher.name'), $pathInfo['item_name']),
                'url' => array(
                    'type' => 0
                )
            );
            A('Home/Message')->addPushUserMessage('STUDENT_EXPRESSION_DELETE', 3, $pathInfo['student_id'], $parameter_arr);

            $parameter_arr = array(
                'msg' => array($studentInfo['student_name'], session('teacher.name'), $pathInfo['item_name']),
                'url' => array(
                    'type' => 0
                )
            );
            A('Home/Message')->addPushUserMessage('STUDENT_EXPRESSION_DELETE_CHILD', 4, $parentIds[0]['id'], $parameter_arr);
            $this->ajaxReturn('success');
        } else
            $this->ajaxReturn('failed');
    }

    //数字课堂
    public function classroomList()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '数字课堂');
        $this->assign('subnav', '课堂列表');
        $this->assign('navicon', 'shuziketang');

        $Model = M('biz_classroom_information');

        $map['biz_classroom_information.teacher_id'] = session('teacher.id');
        $map['biz_class.is_delete'] = 0;

        $count = $Model
            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_classroom_information.class_id AND (biz_classroom_information.course_id = biz_class_teacher.course_id OR is_handler=1)')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('auth_teacher on auth_teacher.id=biz_classroom_information.teacher_id')
            ->join('dict_course on dict_course.id=biz_classroom_information.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_classroom_information.textbook_id')
            ->where($map)
            ->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        $result = $Model
            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_classroom_information.class_id AND (biz_classroom_information.course_id = biz_class_teacher.course_id OR is_handler=1)')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id ')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('auth_teacher on auth_teacher.id=biz_classroom_information.teacher_id')
            ->join('dict_course on dict_course.id=biz_classroom_information.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_classroom_information.textbook_id')
            ->field('biz_class.school_id,biz_class.class_status,biz_class.flag,biz_class.is_delete,biz_classroom_information.*,dict_grade.grade,auth_teacher.name as teacher_name,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook')
            ->where($map)
            ->order('biz_classroom_information.time desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        foreach ($result as $k => $v) {
            if ($v['class_status'] == 1) {
                $d_m['id'] = $v['school_id'];
                $sc_name = M('dict_schoollist')->where($d_m)->find();
                if ($sc_name['flag'] == 0) {
                    $result[$k]['flag'] = 0;
                }
            }
        }

        $this->assign('list', $result);
        $this->assign('page', $show);

        $this->display();
    }

    //创建课堂
    public function createClassroom()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if ($_POST) {
            /*$data['class_id'] = $_POST['class_id'];
            $data['grade_id'] = $_POST['grade_id'];
            $data['course_id'] = $_POST['course_id'];
            $data['textbook_id'] = $_POST['textbook_id'];
            $data['classroom'] = remove_xss($_POST['classroom']);
             */
            $data['class_id'] = getParameter('class_id', 'int', false);
            $data['grade_id'] = getParameter('grade_id', 'int', false);
            $data['course_id'] = getParameter('course_id', 'int', false);
            $data['textbook_id'] = getParameter('textbook_id', 'int', false);
            $data['classroom'] = getParameter('classroom', 'str', false);

            $data['teacher_id'] = session('teacher.id');

            $data['time'] = time();

            $ResourceModel = M('biz_classroom_information');

            $ResourceModel->add($data);

            $this->redirect("Teach/classroomList");

        } else {
            $this->assign('module', '班级行');
            $this->assign('nav', '数字课堂');
            $this->assign('subnav', '创建课堂');
            $this->assign('navicon', 'shuziketang');

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            //获取该教师所有的班级和年级
            $classes = D('Biz_classList')->getClassListTeacherAll();

            for ($i = 0; $i < sizeof($classes); $i++) {
                $classes[$i]['name'] = $classes[$i]['grade'] . ' ' . $classes[$i]['name'];
            }

            $this->assign('classes', $classes);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            $this->display();
        }
    }

    //编辑课堂
    public function editClassroom()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if ($_POST) {
            /*$check['id'] = $_POST['id'];
            $data['class_id'] = $_POST['class_id'];
            $data['grade_id'] = $_POST['grade_id'];
            $data['course_id'] = $_POST['course_id'];
            $data['textbook_id'] = $_POST['textbook_id'];
            $data['classroom'] = remove_xss($_POST['classroom']);
            */
            $check['id'] = getParameter('id', 'int', false);
            $data['class_id'] = getParameter('class_id', 'int', false);
            $data['grade_id'] = getParameter('grade_id', 'int', false);
            $data['course_id'] = getParameter('course_id', 'int', false);
            $data['textbook_id'] = getParameter('textbook_id', 'int', false);
            $data['classroom'] = getParameter('classroom', 'str', false);

            $data['teacher_id'] = session('teacher.id');

            //$data['time'] = time();

            $ResourceModel = M('biz_classroom_information');
            //get teachername
            $teacherInfo = D('Auth_teacher')->getTeachInfo(session('teacher.id'));
            $teacherName = $teacherInfo['name'];
            //get coursename
            $courseInfo = D('Dict_course')->getCourseInfo($data['course_id']);
            $courseName = $courseInfo['course_name'];


            //old data
            $oldData = $ResourceModel->where($check)->find();
            $oldCourseInfo = D('Dict_course')->getCourseInfo($oldData['course_id']);
            $oldCourseName = $oldCourseInfo['course_name'];
            $oldCourseInfo = D('Dict_grade')->getGradeInfo($oldData['grade_id']);
            $oldGradeName = $oldCourseInfo['grade'];
            $oldBookInfo = D('Biz_textbook')->getTextBookDetails($oldData['textbook_id']);
            $oldBookName = $oldBookInfo['name'];
            $oldClassRoom = $oldData['classroom'];

            $ResourceModel->where($check)->save($data);
            //new data
            $newCourseInfo = D('Dict_course')->getCourseInfo($data['course_id']);
            $newCourseName = $newCourseInfo['course_name'];
            $newCourseInfo = D('Dict_grade')->getGradeInfo($data['grade_id']);
            $newGradeName = $newCourseInfo['grade'];
            $newBookInfo = D('Biz_textbook')->getTextBookDetails($data['textbook_id']);
            $newBookName = $newBookInfo['name'];
            $newClassRoom = $data['classroom'];


            $parameters_teacher = array('msg' => array($check['id'],
                $oldCourseName, $oldGradeName, $oldBookName, $oldClassRoom,
                $newCourseName, $newGradeName, $newBookName, $newClassRoom
            ),
                'url' => array('type' => 0)
            );
            A('Home/Message')->addPushUserMessage('CLASSROOM_MODIFIED', 2, $data['teacher_id'], $parameters_teacher);
            $parameters_student = array('msg' => array($courseName, $teacherName, $check['id'],
                $oldCourseName, $oldGradeName, $oldBookName, $oldClassRoom,
                $newCourseName, $newGradeName, $newBookName, $newClassRoom
            ),
                'url' => array('type' => 0)
            );
            //find students in this class
            $studentList = D('Biz_class_student')->getClassStudent($data['class_id']);
            A('Home/Message')->addPushUserMessage('STUCLASSROOM_MODIFIED', 3, implode(',', array_column($studentList, 'id')), $parameters_student);

            $parentList = D('Biz_class_student')->getClassStudentParent($data['class_id']);
            foreach ($parentList as $parent) {
                $parameters_parent = array('msg' => array($parent['student_name'], $courseName, $teacherName, $check['id'],
                    $oldCourseName, $oldGradeName, $oldBookName, $oldClassRoom,
                    $newCourseName, $newGradeName, $newBookName, $newClassRoom
                ),
                    'url' => array('type' => 0)
                );
                A('Home/Message')->addPushUserMessage('CLASSROOM_MODIFIED_CHILD', 4, $parent['id'], $parameters_parent);
            }

            $this->redirect("Teach/classroomList");

        } else {
            $this->assign('module', '班级行');
            $this->assign('nav', '数字课堂');
            $this->assign('subnav', '创建课堂');
            $this->assign('navicon', 'shuziketang');

            //$id = $_GET['id'];
            $id = getParameter('id', 'int', false);
            $ResourceModel = M('biz_classroom_information');
            $classroom = $ResourceModel->where("id=$id")->find();
            $this->assign('data', $classroom);

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('biz_class');
            $classes = $Model
                ->where("class_teacher_id=" . session('teacher.id'))
                ->order('name asc')->select();
            $this->assign('classes', $classes);


            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            $TextbookModel = M('biz_textbook');
            $c1['course_id'] = $classroom['course_id'];
            $c1['grade_id'] = $classroom['grade_id'];
            $textbooks = $TextbookModel->where($c1)->select();
            $this->assign('textbooks', $textbooks);

            $this->display();
        }
    }


    public function deleteClassroom()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$id = $_GET['id'];
        $id = getParameter('id', 'int');

        $ResourceModel = M('biz_classroom_information');
        $c1['id'] = $id;
        $c1['teacher_id'] = session('teacher.id');
        $ResourceModel->where($c1)->delete();
        $this->ajaxReturn('success');
    }


    //获得学生浏览小黑板信息
    public function studentBrowseBlackboard()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$class_id=intval(I('class_id'));
        //$blackboard_id=intval(I('blackboard_id'));
        $class_id = getParameter('class_id', 'int', false);
        $blackboard_id = getParameter('blackboard_id', 'int', false);

        if (!$class_id || !$blackboard_id) {
            echo json_encode(array());
        } else {
            $class_model = M('biz_class');
            $check['biz_class.id'] = $class_id;
            $check['biz_class_student.status'] = 2;

            $result = $class_model->where($check)->join('biz_class_student on biz_class_student.class_id=biz_class.id')
                ->join('auth_student on auth_student.id=biz_class_student.student_id')
                ->join('biz_isread_blackboard on biz_isread_blackboard.user_id=auth_student.id and role_id=3 and biz_isread_blackboard.b_id=' . $blackboard_id, 'left')
                ->field("auth_student.id,auth_student.student_name,auth_student.avatar,IFNULL(biz_isread_blackboard.id,'no') browse_flag")->select();
            echo json_encode($result);
        }
    }


    //小黑板
    public function blackboard()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        $is_board = session('teacher.is_board');

        if ($is_board == 1) {
            $teacher_id = session('teacher.id');
            D('Biz_classList')->isFirstLoginBoard($teacher_id);
            $this->redirect('Teach/blackboradFunctionGuidancecopy');
            $_SESSION['teacher']['is_board'] = 2;
        }


        $this->assign('module', '班级行');
        $this->assign('nav', '小黑板');
        $this->assign('subnav', '消息列表');
        $this->assign('navicon', 'xiaoheiban');

        //$filter['grade'] = $_REQUEST['grade'];
        //$filter['class'] = $_REQUEST['class'];
        $filter['grade'] = getParameter('grade', 'int', false);
        $filter['class'] = getParameter('class', 'str', false);

        $filter['keyword'] = $_REQUEST['keyword'];
        if (!empty($filter['keyword'])) $check['biz_blackboard.message_title'] = array('like', '%' . $filter['keyword'] . '%');
        $check['publisher_id'] = session('teacher.id');

        $check['biz_class.is_delete'] = 0;

        $checkmap['publisher_id'] = session('teacher.id');
        $checkmap['biz_class.is_delete'] = 0;

        //获取发布消息的总数
        $countboard = D('Biz_blackboard')->getBoardCount($checkmap);//小黑板总数
        $this->assign('countboard', count($countboard));

        $result = D('Biz_blackboard')->getBoardList($check);//小黑板列表

        $this->assign('list', $result['result']);
        $this->assign('page', $result['show']);

        $teacher_id = session('teacher.id');
        $grade_result = D('Biz_class')->getGradeListByTeacher($teacher_id);
        $this->assign('grade_list', $grade_result);

        //年级不为空,求出班级和学科
        if (!empty($check['dict_grade.id'])) {
            $class_model = M('biz_class');
            $class_result = $class_model->where("grade_id=" . $check['dict_grade.id'] . " and biz_class_teacher.teacher_id=" . session('teacher.id'))->field('biz_class.id,biz_class.name')
                ->join('biz_class_teacher on biz_class.id=biz_class_teacher.class_id')
                ->group("biz_class.name")->select();
        }

        $this->assign('grade_list', $grade_result);
        $this->assign('class_list', $class_result);
        $this->assign('default_grade', $filter['grade']);
        $this->assign('default_class', $filter['class']);
        $this->assign('keyword', $filter['keyword']);


        $this->display_nocache();
    }

    //删除消息
    public function deleteBlackboardMessage()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$id = $_GET['id'];
        $id = getParameter('id', 'int');

        $ResourceModel = M('biz_blackboard');
        $c1['id'] = $id;
        $ResourceModel->where($c1)->delete();
        $map['b_id'] = $id;
        M('boardandclass')->where($map)->delete();
        $this->ajaxReturn('success');
    }


    //发布小黑板消息
    public function publishBlackboardMessage()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if ($_POST) {
            if (!session('?teacher')) {
                redirect(U('Index/index'), 0, '登录超时，请重新登录...');
            }
            /*
            $data['class_id'] = $_POST['class_id'];
            $data['message_title'] = remove_xss($_POST['message_title']);
            $data['message'] = $_POST['message'];
            */
            //$data['class_id'] = getParameter('class_id', 'int',false);
            $data['message_title'] = getParameter('message_title', 'str', false);
            $data['message'] = $_POST['message'];
            $class_id = $_POST['class_id'];


            $data['publisher_id'] = session('teacher.id');
            $data['publisher'] = session('teacher.name');
            $data['school_id'] = session('teacher.school_id');
            $data['create_at'] = time();
            $ResourceModel = M('biz_blackboard');

            $ResourceModel->startTrans();

            $id = $ResourceModel->add($data);

            if ($id) {
                if (!empty($class_id)) {

                    foreach ($class_id as $k => $v) {
                        $dataclass = array(
                            'b_id' => $id,
                            'class_id' => $v,
                        );
                        $databoard = M('boardandclass')->where($dataclass)->find();
                        if (empty($databoard)) {
                            $bid = M('boardandclass')->add($dataclass);
                            if (!$bid) {
                                $ResourceModel->rollback();
                                $this->error("创建小黑板失败");
                            }
                        }
                    }
                }
            } else {
                $ResourceModel->rollback();
                $this->error("创建小黑板失败");
            }

            $ResourceModel->commit();

            //push message to students in this class
            /* $parameters = array( 'msg' => array(session('teacher.name'),$data['message_title']) ,
                'url' => array( 'type' => 1,'data' => array($id))
            );
            $studentList = D('Biz_class_student')->getClassStudent($data['class_id']);
            A('Home/Message')->addPushUserMessage('BLACKBOARD_PUBLISHED',3,implode(',',array_column($studentList,'id')),$parameters);*/

            //拿到这个班下的所有学生和家长
            /* $class_model=M('biz_class');
            $student_parent_result=$class_model->where('biz_class.id='.$data['class_id'])->join('dict_grade on dict_grade.id=biz_class.grade_id')->join('biz_class_student on biz_class_student.class_id=biz_class.id and biz_class_student.status=2')
                ->join('auth_student on auth_student.id=biz_class_student.student_id')->join('auth_parent on auth_parent.id=auth_student.parent_id')
                ->field('auth_student.id,auth_student.student_name,auth_parent.id parent_id,auth_parent.parent_name')->select();
            if(!empty($student_parent_result)){
                foreach($student_parent_result as $val){
                    $parent_parameters=array( 'msg' => array($val['student_name'],session('teacher.name'),$data['message_title']) ,
                        'url' => array( 'type' => 1,'data' => array($id))
                    );

                    A('Home/Message')->addPushUserMessage('BLACKBOARD_PUBLISHED_CHILD',4,$val['parent_id'],$parent_parameters);
                }
            }*/

            $this->redirect("Teach/blackboard");
        } else {
            $this->assign('module', '班级行');
            $this->assign('nav', '小黑板');
            $this->assign('subnav', '发布消息');
            $this->assign('navicon', 'xiaoheiban');

            //获得该教师所教得年级
            /* $class_model = M('biz_class');
             $grade_result=$class_model
                         ->join('dict_grade on dict_grade.id=biz_class.grade_id')
                         ->where('biz_class.class_teacher_id='.session("teacher.id"))
                         ->field('dict_grade.id,dict_grade.grade')
                         ->group("dict_grade.id")
                         ->select();*/
            $teacher_id = session('teacher.id');
            //$grade_result = D('Biz_class')->getGradeListByTeacher( $teacher_id );

            $grade_result = D('Biz_class')->getGradeClassListTeacher($teacher_id);
//            print_r($grade_result);die();
            $this->assign('grade_list', $grade_result);

            $this->display();
        }

    }

    //小黑板消息详情
    public function blackboardMessageDetails()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $isAuth = $this->isAuth($this->c_a);

        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '小黑板');
        $this->assign('navicon', 'xiaoheiban');


        //$id = $_GET['id'];
        $id = getParameter('id', 'int', false);
        $read_person_number = getParameter('read_person_number', 'int', false);

        $Model = M('biz_blackboard');
        $map['biz_blackboard.id'] = $id;
        $map['biz_class.is_delete'] = 0;
        $result = $Model
            ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
            ->join('biz_class_teacher on biz_class_teacher.class_id=boardandclass.class_id')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->field('group_concat(grade,biz_class.name) as cgname,biz_blackboard.*,biz_class.name as class_name')
            ->where($map)
            ->find();
        $cgname = explode(',', $result['cgname']);
        $result['cgname'] = array_unique($cgname);

        $toumap['id'] = $result['publisher_id'];
        $tou = M('auth_teacher')->where($toumap)->find();
        $result['touimg'] = $tou['avatar'];

        $this->assign('data', $result);


        $add_data['user_id'] = session('teacher.id');
        $add_data['b_id'] = $result['id'];
        $add_data['role_id'] = 2;
        $model = M('biz_isread_blackboard');
        //判断是否存在,不存在则入库

        if (!empty($result)) {
            $browse_result = $model->where('role_id=2' . ' and user_id=' . session('teacher.id') . ' and b_id=' . $result['id'])->field('id')->find();

            if (empty($browse_result)) {
                $model->add($add_data);
            }
        }


        $this->assign('subnav', $result['class_name'] . " 小黑板");

        $Model->where("id=$id")->setInc('view_count', 1);

        $this->assign('read_person_number', $read_person_number);

        $this->display();
    }


    //////作业系统
    public function homework()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if (!$this->teacherId_id_online) {
            redirect(U('Teach/index1?auth_error=1'));
        }

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '作业系统');
        $this->assign('navicon', 'zuoyexitong');

        $mca = I('mca');
        if ($mca == 'action') {
            $this->assign('kw', 1);
        }

        $Model = M('biz_homework');
        $grade_model = M('dict_grade');
        $class_model = M('biz_class');
        $course_model = M('dict_course');
        $biz_textbook_mode = M('biz_textbook');

        if (I('grade') > 0) {
            $where['dict_grade.id'] = intval(I('grade'));
        }
        if (I('class') > 0) {
            $class_id = intval(I('class'));
            //这里求出班级
            $class_model = M('biz_class');
            $class_result = $class_model->where("id=" . $class_id)->field('name')->find();
            if (empty($class_result)) {
                redirect(U('Index/systemError'));
            } else {
                $class_name = $class_result['name'];
                //$where['biz_class.name']=$class_name;
                $where['biz_class.id'] = $class_id;
            }
        }
        if (I('course') > 0) {
            $where['dict_course.id'] = intval(I('course'));
        }
        if (I('textbook') > 0) {
            $where['biz_textbook.id'] = intval(I('textbook'));
        }
        if (I('type') > 0) {
            if (I('type') == 1) {
                $where['biz_homework.homework_type'] = '课堂作业';
            } elseif (I('type') == 2) {
                $where['biz_homework.homework_type'] = '课后作业';
            }
        }
        if (I('status') > 0) {
            $status = intval(I('status'));
            if ($status == 2) {
                $status = 0;
            }
            $where['biz_homework.homework_status'] = $status;
        }
        if (I('keyword')) {
            $where['biz_homework.homework_name'] = array('like', '%' . I('keyword') . '%');
        }
        $where['biz_homework.teacher_id'] = session("teacher.id");
        //$alone_where['biz_homework.teacher_id']=session("teacher.id");
        $alone_where['biz_class.class_teacher_id'] = session("teacher.id");

        //$where['biz_class.is_delete']=0;
        /* if (!empty($check['course_id']))
            $unionSql = $unionSql." and biz_bj_resources.course_id = ".$filter['course_id'];*/

        //$unionSql = "SELECT biz_homework.id FROM `biz_homework` INNER JOIN biz_class_teacher_record on biz_class_teacher_record.class_id=biz_homework.class_id INNER JOIN biz_class on biz_class.id=biz_class_teacher_record.class_id INNER JOIN dict_grade on dict_grade.id=biz_homework.grade_id INNER JOIN dict_course on dict_course.id=biz_homework.course_id INNER JOIN biz_textbook on biz_textbook.id=biz_homework.textbook_id WHERE biz_homework.teacher_id =".session('teacher.id')." GROUP BY biz_homework.id";

        $sort_string = 'biz_homework.create_at desc';


        $count = $Model
            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_homework.class_id')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
            ->join('dict_course on dict_course.id=biz_homework.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
            ->field('biz_homework.id')
            ->group('biz_homework.id')
            //    ->union($unionSql)
            ->where($where)
            ->order($sort_string)
            ->select();
        $count = count($count);


        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        //$unionSqlSelect = "SELECT biz_class.flag,biz_class.school_id,biz_class.class_status,biz_homework.*,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,dict_grade.grade FROM `biz_homework` INNER JOIN biz_class_teacher_record on biz_class_teacher_record.class_id=biz_homework.class_id INNER JOIN biz_class on biz_class.id=biz_class_teacher_record.class_id INNER JOIN dict_grade on dict_grade.id=biz_homework.grade_id INNER JOIN dict_course on dict_course.id=biz_homework.course_id INNER JOIN biz_textbook on biz_textbook.id=biz_homework.textbook_id WHERE biz_homework.teacher_id = ".session('teacher.id')." GROUP BY biz_homework.id";


        $result = $Model
            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_homework.class_id')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
            ->join('dict_course on dict_course.id=biz_homework.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
            ->field('biz_class.flag,biz_class.school_id,biz_class.class_status,biz_homework.*,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,dict_grade.grade')
            ->group('biz_homework.id')
            //->union($unionSqlSelect." LIMIT ".$Page->firstRow . ',' . $Page->listRows)
            //->union($unionSqlSelect.' ORDER BY '.$sort_string." LIMIT ".$Page->firstRow . ',' . $Page->listRows)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->where($where)
            ->order($sort_string)
            ->select();

        $Model = M('biz_exercise_library_chapter');

        foreach ($result as $key => $val) {
            //再去求那个章节的
            $info = $Model->where("u.id=" . $val['id'])
                ->join("biz_homework_exercise t on t.chapter_id=biz_exercise_library_chapter.id")
                ->join("biz_homework u on u.id=t.homework_id")
                ->group("t.chapter_id")->field("distinct chapter,festival")->select();     //echo $Model->getLastsql();die;
            $result[$key]['chapter'] = $info;

            if ($val['class_status'] == 1) {
                $d_m['id'] = $val['school_id'];
                $sc_name = M('dict_schoollist')->where($d_m)->find();
                if ($sc_name['flag'] == 0) {
                    $result[$key]['flag'] = 0;
                }
            }

            $is_delete_teacher_map['class_id'] = $val['class_id'];
            $is_delete_teacher_map['teacher_id'] = session('teacher.id');
            $is_delete_teacher = M('biz_class_teacher_record')->where($is_delete_teacher_map)->find();
            if (!empty($is_delete_teacher)) {
                $result[$key]['flag'] = 0; //被学校移除的老师
            }
        }


        $teacher_id = session('teacher.id');
        $grade_result = D('Biz_class')->getGradeListByTeacher($teacher_id);
        //$grade_result=$class_model->where($alone_where)->field('dict_grade.id,dict_grade.grade')->join('dict_grade on dict_grade.id=biz_class.grade_id')->group("dict_grade.id")->select();

        //$class_result=$class_model->where($alone_where)->field('biz_class.id,biz_class.name')->join('biz_homework on biz_homework.class_id=biz_class.id')->group("biz_class.name")->select();

        //$course_result=$course_model->where($alone_where)->field('dict_course.id,dict_course.course_name')->join('biz_homework on biz_homework.course_id=dict_course.id')->group("dict_course.id")->select();
        //$textbook_result=$biz_textbook_mode->where($alone_where)->field('biz_textbook.id,biz_textbook.name')->join('biz_homework on biz_homework.textbook_id=biz_textbook.id')->group("biz_textbook.id")->select();
        //年级不为空,求出班级和学科

        if (!empty($where['dict_grade.id'])) {
            if (!empty($class_id)) {
                $class_model = M('biz_class');
                $class_result = $class_model->where("id=" . $class_id)->select();
            }

            $course_model = M('dict_course');
            $course_result = $course_model->where("auth_teacher_second.grade_id=" . $where['dict_grade.id'])
                ->field('dict_course.id,dict_course.course_name')
                ->join("auth_teacher_second on auth_teacher_second.teacher_id=" . session('teacher.id') . " and auth_teacher_second.course_id=dict_course.id")->group("dict_course.id")
                ->select();
        }


        //年级和学科不为空,求出教材分册
        if (!empty($where['dict_grade.id']) && !empty($where['dict_course.id'])) {
            $textbook_model = M('biz_textbook');
            $textbook_result = $textbook_model->where("grade_id=" . $where['dict_grade.id'] . " and course_id=" . $where['dict_course.id'])
                ->field('id,name')->select();
        }


        $this->assign('list', $result);
        $this->assign('page', $show);
        $this->assign('grade_list', $grade_result);
        $this->assign('class_list', $class_result);
        $this->assign('course_list', $course_result);
        $this->assign('textbook_list', $textbook_result);
        //$this->assign('firstPage', $Page->firstRow);


        $this->assign('default_grade', $where['dict_grade.id']);
        $this->assign('default_class', $class_id);
        $this->assign('default_course', $where['dict_course.id']);
        $this->assign('default_textbook', $where['biz_textbook.id']);
        $this->assign('default_type', intval(I('type')));
        $this->assign('default_status', intval(I('status')));
        $this->assign('keyword', I('keyword'));

        $this->display();
    }

    //点击布置作业
    public function updateHomework()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $id = intval(I('id'));
        if (!$id) {
            echo 0;
        } else {
            $model = M('biz_homework');
            $data['homework_status'] = 1;
            $model->where("id=" . $id)->save($data);
            $homeworkResult = $model->where("id=" . $id)->find();

            //find students in this class
            $userIdList = D('Biz_class_student')->getStudentIdParentIdByClassId($homeworkResult['class_id']);
            if (!empty($userIdList)) {
                $parameters = array('msg' => array(session('teacher.name'), $homeworkResult['homework_name']),
                    'url' => array('type' => 0)
                );

                if (!empty($userIdList['studentId'])) {
                    D('UserInfo')->sendMsg(ROLE_STUDENT, $userIdList['studentId'], json_encode($parameters), "HOMEWORK_PUBLISHED", date('Y-m-d H:i:s', time()));
                }

                foreach ($userIdList['parentStudentName'] as $key => $val) {
                    $parentId = $val['id'];
                    $studentName = $val['name'];
                    $parameters = array('msg' => array(session('teacher.name'), $studentName, $homeworkResult['homework_name']),
                        'url' => array('type' => 0)
                    );
                    D('UserInfo')->sendMsg(ROLE_PARENT, $parentId, json_encode($parameters), "HOMEWORK_PUBLISHED_CHILD", date('Y-m-d H:i:s', time()));
                }

            }


            echo 1;
        }
    }

    //习题库ajax请求
    public function exercisesAjax()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $filter['grade_id'] = intval($_REQUEST['grade']);
        $filter['course_id'] = intval($_REQUEST['course']);
        $filter['textbook_id'] = intval($_REQUEST['textbook']);
        $filter['chapter'] = intval($_REQUEST['chapter']);
        $filter['festival'] = intval($_REQUEST['festival']);

        if (!empty($filter['grade_id'])) $check['grade_id'] = $filter['grade_id'];
        if (!empty($filter['course_id'])) $check['course_id'] = $filter['course_id'];
        if (!empty($filter['textbook_id'])) $check['textbook_id'] = $filter['textbook_id'];
        $chapter_model = M('biz_exercise_library_chapter');

        if (!empty($filter['chapter'])) {
            $chapter = $chapter_model->where('id=' . $filter['chapter'])->field('chapter')->find();
            if (!empty($chapter)) {
                $chapter_string = $chapter['chapter'];
                $check['chapter'] = "$chapter_string";

                if (!empty($filter['festival'])) {
                    $festival = $chapter_model->where('id=' . $filter['festival'])->field('festival')->find();
                    if (!empty($festival)) {
                        $festival_string = $festival['festival'];
                        $check['festival'] = "$festival_string";
                    }
                }
            }
        }
        $model = M('biz_exercise_library_chapter');
        $count = $model->where($check)->order("grade_id asc,course_id asc")->field("id,chapter,festival,title")->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        $result = $model->where($check)->order("grade_id asc,course_id asc")->limit($Page->firstRow . ',' . $Page->listRows)->field("id,chapter,festival,title")->select();

        foreach ($result as $k => $v) {
            $data['chapter_id'] = $v['id'];
            $data['is_pay'] = 1;
            $num = M('biz_exercise_library')->where($data)->count();
            if ($num == 0) {
                unset($result[$k]);
            }
        }
        $result = array_values($result);

        $data['count'] = ceil($count / C('PAGE_SIZE_FRONT'));
        $data['data'] = $result;
        echo json_encode($data);
    }

    //习题库
    public function exercisesLibrary()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '习题库');
        $this->assign('navicon', 'zuoyexitong');

        $filter['grade_id'] = intval($_REQUEST['grade']);
        $filter['course_id'] = intval($_REQUEST['course']);
        $filter['textbook_id'] = intval($_REQUEST['textbook']);
        $filter['chapter'] = intval($_REQUEST['chapter']);
        $filter['festival'] = intval($_REQUEST['festival']);

        if (!empty($filter['grade_id'])) $check['grade_id'] = $filter['grade_id'];
        if (!empty($filter['course_id'])) $check['course_id'] = $filter['course_id'];
        if (!empty($filter['textbook_id'])) $check['textbook_id'] = $filter['textbook_id'];
        $chapter_model = M('biz_exercise_library_chapter');

        if (!empty($filter['chapter'])) {
            $chapter = $chapter_model->where('id=' . $filter['chapter'])->field('chapter')->find();
            if (!empty($chapter)) {
                $chapter_string = $chapter['chapter'];
                $check['chapter'] = "$chapter_string";

                if (!empty($filter['festival'])) {
                    $festival = $chapter_model->where('id=' . $filter['festival'])->field('festival')->find();
                    if (!empty($festival)) {
                        $festival_string = $festival['festival'];
                        $check['festival'] = "$festival_string";
                    }
                }
            }
        }


        $model = M('biz_exercise_library_chapter');
        $count = $model->where($check)->order("grade_id asc,course_id asc")->field("id,chapter,festival,title")->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        $result = $model->where($check)->order("grade_id asc,course_id asc")->limit($Page->firstRow . ',' . $Page->listRows)->field("id,chapter,festival,title")->select();
        $this->assign('list', $result);
        $this->assign('page', $show);

        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        $this->assign('courses', $courses);

        $grade_model = M('dict_grade');
        $grade_result = $grade_model->field('dict_grade.id,dict_grade.grade')->select();
        $this->assign('grade_list', $grade_result);

        //学科和教材分册不为空取分册
        if (!empty($filter['course_id']) && !empty($filter['grade_id'])) {
            $textbook_model = M('biz_textbook');
            $textbook_result = $textbook_model->where("grade_id=" . $check['grade_id'] . " and course_id=" . $check['course_id'])->field('id,name')->select();

            //求章
            if (!empty($filter['textbook_id'])) {

                //$chapter_result=$chapter_model->where('textbook_id='.$filter['textbook_id'])->field('id,chapter')->group('chapter')->select();

                $chapter_result = $chapter_model->field('id,chapter')->group('chapter')->select();

                $this->assign('chapter_list', $chapter_result);
                //求节
                if (!empty($filter['festival'])) {
                    $festival_result = $chapter_model->where("chapter=" . "'{$check['chapter']}' and textbook_id=" . $filter['textbook_id'])->field("id,chapter,festival")->select();
                    $this->assign('festival_list', $festival_result);
                }
            }

            $this->assign('textbook_list', $textbook_result);
        }

        $this->assign('grade_id', $filter['grade_id']);
        $this->assign('course_id', $filter['course_id']);
        $this->assign('textbook_id', $filter['textbook_id']);
        $this->assign('chapter', $filter['chapter']);
        $this->assign('festival', $filter['festival']);

        $this->display();
    }

    //查看作业习题
    public function homeworkExercises()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '作业系统');
        $this->assign('navicon', 'zuoyexitong');
        $homework = intval(I('id'));
        if (!$homework) {
            redirect(U('Index/systemError'));
            die;
        }

        $Model = M('biz_homework');
        $result = $Model->where("id=" . $homework)->field('id,homework_name,claim,exercises_number')->find();

        $chapter_result = array();
        if (!empty($result)) {
            $where['homework_id'] = $homework;
            $homework_exercise_model = M('biz_homework_exercise');
            $chapter_result = $homework_exercise_model->where($where)
                ->join('biz_exercise_library_chapter chapter on chapter.id=biz_homework_exercise.chapter_id')
                ->group('chapter.id')
                ->field('chapter.chapter,chapter.festival')
                ->select();
        }
        $this->assign('data', $result);
        $this->assign('chapter_data', $chapter_result);

        $this->display();
    }

    //根据章节ID,得到所有习题
    public function getExerciseChapterDetails()
    {
        //$id = $_GET['id'];
        $id = getParameter('id', 'int', false);
        if (!$id) {
            echo json_encode(array());
        } else {
            $Model = M('biz_exercise_library');
            $where['biz_exercise_library.chapter_id'] = $id;
            $result = $Model
                ->join('biz_exercise_template on biz_exercise_library.type=biz_exercise_template.id')
                ->field("biz_exercise_library.*,biz_exercise_library.id questions_primary_id,biz_exercise_template.template_name")
                ->where($where)
                ->order('biz_exercise_library.id asc')->select();
            echo json_encode($result);
        }
    }

    //通过作业ID,拿到该作业的所有题
    public function getHomeworkExercise()
    {
        $id = intval(I('id'));
        if (!$id) {
            echo json_encode(array());
        } else {
            $Model = M('biz_homework_exercise');
            $result = $Model->where("biz_homework_exercise.homework_id=" . $id)->join("biz_exercise_library on biz_exercise_library.id=biz_homework_exercise.exercise_id")
                ->join("biz_exercise_template on biz_exercise_template.id=biz_exercise_library.type")
                ->field("biz_homework_exercise.id,biz_homework_exercise.exercise_id,biz_exercise_library.question_id,"
                    . "biz_exercise_library.questions,biz_exercise_library.points,biz_exercise_library.body,biz_exercise_template.template_name")
                ->order('biz_exercise_library.id')
                ->select();     //echo $Model->getLastSql();die;
            echo json_encode($result);

        }
    }

    //通过作业ID,拿到该作业的所有题这个有用用于某些js。
    public function getHomeworkExercise_copy()
    {
        $id = intval(I('id'));
        if (!$id) {
            echo json_encode(array());
        } else {
            $Model = M('biz_homework_exercise');
            $result = $Model->where("biz_homework_exercise.homework_id=" . $id)->join("biz_exercise_library on biz_exercise_library.id=biz_homework_exercise.exercise_id")
                ->join("biz_exercise_template on biz_exercise_template.id=biz_exercise_library.type")
                ->field("biz_homework_exercise.exercise_id id,biz_exercise_library.question_id,"
                    . "biz_exercise_library.questions,biz_exercise_library.points,biz_exercise_library.body,biz_exercise_template.template_name")
                ->order('biz_exercise_library.id')
                ->select();     //echo $Model->getLastSql();die;
            echo json_encode($result);

        }
    }

    //通过习题id,拿到详细信息
    public function getExerciseInfo()
    {
        $ids = I('id');
        if ($ids == '') {
            echo json_encode(array());
        } else {
            $condition_str = "biz_exercise_library.id in (" . $ids . ")";
            $exercise_model = M('biz_exercise_library');
            $result = $exercise_model->where($condition_str)
                ->join('biz_exercise_template on biz_exercise_template.id=biz_exercise_library.type')
                ->join('biz_exercise_collect on biz_exercise_collect.exercise_id=biz_exercise_library.id', 'left')
                ->field("biz_exercise_template.*,biz_exercise_library.*,biz_exercise_collect.create_at")
                ->group('biz_exercise_library.id')
                ->order('biz_exercise_collect.create_at desc')
                ->select();
            echo json_encode($result);
        }
    }


    //通过某个作业ID,拿到其下所有习题
    public function getHomeworkInfo()
    {
        $id = intval(I('id'));
        $student_id = intval(I('student_id')); //这个是查看已批改作业的时候传递的
        $id_array = I('exercises_id'); //这个是习题ID,第一次是没有的,添加习题后就存在了
        $auto_score = I('auto_score');//这个是老师自动打分时,传递的
        if (!$id) {
            echo json_encode(array());
        } else {
            //$str="->join(biz_homework_student_details l on u.id=l.homework_id and l.student_id=305)";
            if (!($student_id)) {
                $Model = M('biz_exercise_library_chapter');
                $result = $Model->where("u.id=" . $id)
                    ->join("biz_homework_exercise t on t.chapter_id=biz_exercise_library_chapter.id")
                    ->join("biz_homework u on u.id=t.homework_id")
                    ->join("biz_exercise_library v on t.exercise_id=v.id")
                    ->join("biz_exercise_template tt on v.type=tt.id")
                    ->field("chapter,festival,v.id questions_primary_id,u.homework_name,tt.*,v.*,v.id as question_id,mp3_vid vid")
                    ->order('v.id asc')
                    ->select();
                /*if($auto_score){
                    //只有这个是老师打分的页面
                    foreach($result as $value){
                        $this->auto_score($value);
                    }
                }*/

            } else {
                $Model = M('biz_exercise_library_chapter');
                $result = $Model->where("u.id=" . $id)
                    ->join("biz_homework_exercise t on t.chapter_id=biz_exercise_library_chapter.id")
                    ->join("biz_homework u on u.id=t.homework_id")
                    ->join("biz_exercise_library v on t.exercise_id=v.id")
                    ->join("biz_exercise_template tt on v.type=tt.id")
                    ->join("biz_homework_score_details l on u.id=l.homework_id and l.student_id=$student_id  and v.id=l.question_org_id", 'left')
                    ->field("chapter,festival,v.id questions_primary_id,u.homework_name,tt.*,v.*,v.id as question_id,mp3_vid vid,l.score student_score")
                    ->order('v.id asc')
                    ->select();
                //echo $Model->getLastsql();die;
            }

            if (empty($id_array)) {
                echo json_encode($result);
            } else {
                $id_array = array_unique($id_array);
                $ids = implode(',', $id_array);
                $ids = '(' . $ids . ')';
                $Model = M('biz_exercise_library');

                $exercise_result = $Model->join('biz_exercise_template tt on biz_exercise_library.type=tt.id')
                    ->field('biz_exercise_library.id questions_primary_id,tt.*,biz_exercise_library.*,biz_exercise_library.id as question_id')
                    ->where("biz_exercise_library.id in " . $ids)
                    ->order('biz_exercise_library.id asc')
                    ->select();

                $new_array = array();
                $i = 0;
                foreach ($result as $v) {
                    $new_array[$i] = $v;
                    $i++;
                }
                foreach ($exercise_result as $ev) {
                    $new_array[$i] = $ev;
                    $i++;
                }

                $result = $this->array_sort($new_array, 'questions_primary_id');
                echo json_encode($result);
            }

        }
    }

    //二维数组对某个指定的key排序进行排序
    public function array_sort($arr, $key, $sort = 'asc')
    {
        $keyvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            $keyvalue[$k] = $v[$key];
        }
        if ($sort == 'asc') {
            asort($keyvalue);
        } else {
            arsort($keyvalue);
        }
        reset($keyvalue);

        foreach ($keyvalue as $lk => $lv) {
            $new_array[] = $arr[$lk];
        }
        return $new_array;
    }

    //根据学科获得章节
    public function getTextbookChapter()
    {
        $id = intval(I('id'));
        if (!$id) {
            echo json_encode(array());
        } else {
            $model = M('biz_exercise_library_chapter');
            $chapter_result = $model->where('textbook_id=' . $id)->field('id,chapter')->group('chapter')->order('id asc')->select();
            foreach ($chapter_result as $k => $v) {
                $data['chapter_id'] = $v['id'];
                $data['is_pay'] = 1;
                $num = M('biz_exercise_library')->where($data)->count();
                if ($num == 0) {
                    unset($chapter_result[$k]);
                }
            }
            $chapter_result = array_values($chapter_result);
            echo json_encode($chapter_result);
        }
    }


    //根据某一个章节获得其下的所有习题
    public function getChapterInfo()
    {
        $id = intval(I('id'));
        if (!$id) {
            echo json_encode(array());
        } else {
            $Model = M('biz_exercise_library');
            $c1['biz_exercise_library.chapter_id'] = $id;
            $result = $Model
                ->join('biz_exercise_template on biz_exercise_library.type=biz_exercise_template.id')
                ->field("biz_exercise_library.*,biz_exercise_template.template_name")
                ->where($c1)
                ->order('biz_exercise_library.question_id asc')->select();
            $this->ajaxReturn($result);
        }
    }


    //复制习题页面数据保存
    public function assignHomeworkCopy()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$data['homework_name'] = remove_xss($_POST['homework_name']);
        //$data['claim'] = remove_xss($_POST['claim']);
        $data['homework_name'] = getParameter('homework_name', 'str', false);
        $data['claim'] = getParameter('claim', 'str', false);
        $data['class_id'] = intval($_POST['class_id']);
        $data['course_id'] = intval($_POST['course_id']);
        $data['grade_id'] = intval($_POST['grade_id']);
        $data['textbook_id'] = intval($_POST['textbook_id']);
        $data['homework_type'] = intval($_POST['type_id']);


        $data['teacher_id'] = session('teacher.id');
        $data['teacher_name'] = session('teacher.name');
        $data['create_at'] = time();
        $data['update_at'] = time();
        if ($data['homework_type'] == 1) {
            $data['homework_type'] = '课堂作业';
        } else {
            $data['homework_type'] = '课后作业';
        }

        //这里先判断这个班级和学科等是否存在
        $grade_id = $data['grade_id'];
        $class_id = $data['class_id'];
        $course_id = $data['course_id'];
        $class_model = M('biz_class');
        $class_result = $class_model->where('biz_class.id=' . $class_id . " and biz_class.grade_id=" . $grade_id . " and auth_teacher_second.course_id=" . $course_id)
            ->join('auth_teacher_second on auth_teacher_second.teacher_id=biz_class.class_teacher_id and '
                . 'auth_teacher_second.grade_id=biz_class.grade_id')->field('biz_class.id')->find();

        if (empty($class_result)) {
            redirect(U('Index/systemError'));
        }
        $exercise_ids = $_GET['exercise_ids'];
        $exercise_array = explode(',', $exercise_ids);
        $exercise_array = array_unique($exercise_array);
        $data['exercises_number'] = count($exercise_array);

        //开插入作业表
        $model = M('biz_homework');
        $model->startTrans();
        if ((!$homework_id = $model->add($data))) {
            redirect(U('Index/systemError'));
        }

        $exercise_model = M('biz_exercise_library');
        $homework_exercise = M('biz_homework_exercise');
        $tag = 0;
        //这里的逻辑是没有找到该问题就跳过该问题
        foreach ($exercise_array as $v) {
            $temp_result = $exercise_model->where("id=" . $v)->field('id,chapter_id')->find();
            if (empty($temp_result)) {
                continue;
            } else {
                $exercise_data['homework_id'] = $homework_id;
                $exercise_data['exercise_id'] = $v;
                $exercise_data['chapter_id'] = $temp_result['chapter_id'];
                if (!$homework_exercise->add($exercise_data)) {
                    $model->rollback();
                    redirect(U('Index/systemError'));
                } else {
                    $tag = 1;
                }
            }
        }
        if ($tag == 1) {

            $model->commit();
        } else {
            $model->rollback();
            redirect(U('Index/systemError'));
        }
        $this->redirect("Teach/homework");
    }


    //复制习题页面
    public function homeworkCopy()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '布置作业');
        $this->assign('navicon', 'zuoyexitong');

        $homework_id = intval(I('id'));
        if (!$homework_id) {
            redirect(U('Index/systemError'));
            die;
        }
        $Model = M('biz_homework');
        $result = $Model
            ->join('biz_class on biz_class.id=biz_homework.class_id')
            ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
            ->join('dict_course on dict_course.id=biz_homework.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
            ->field('biz_homework.*,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,dict_grade.grade')
            ->where("biz_homework.id=" . $homework_id)
            ->find();
        if (empty($result)) {
            redirect(U('Index/systemError'));
        }
        $chose_list = I('b_id');
        $chose_other_list = I('c_id');
        if ($chose_list) {
            $chose_list = explode(',', $chose_list);
            $chose_list = array_unique($chose_list);

        }


        if ($chose_other_list) {
            $chose_other__arr = explode(',', $chose_other_list);
            $chose_other__arr = array_unique($chose_other__arr);
            $ids = '';
            foreach ($chose_other__arr as $v) {
                $ids .= intval($v) . ',';
            }
            $ids = rtrim($ids, ',');
            $chose_other_list = $ids;

            //筛除掉不是该作业的相同的年级,学科,分册
            $exercise_where['biz_exercise_library_chapter.course_id'] = $result['course_id'];
            $exercise_where['biz_exercise_library_chapter.grade_id'] = $result['grade_id'];
            $exercise_where['biz_exercise_library_chapter.textbook_id'] = $result['textbook_id'];
            $exercise_where['_string'] = "biz_exercise_library.id in (" . $chose_other_list . ")";
            $exercise_model = M('biz_exercise_library');
            $exercise_result = $exercise_model->where($exercise_where)->join('biz_exercise_library_chapter on biz_exercise_library_chapter.id=biz_exercise_library.chapter_id')
                ->field('biz_exercise_library.id,biz_exercise_library_chapter.grade_id,biz_exercise_library_chapter.course_id,biz_exercise_library_chapter.textbook_id')->select();
            $chose_other_list = array();
            if (!empty($exercise_result)) {
                foreach ($exercise_result as $v) {
                    $chose_other_list[] = $v['id'];
                }
            }

            //$chose_other_list=explode(',',$chose_other_list);
            //$chose_other_list=array_unique($chose_other_list);
            if (!empty($chose_list)) {
                for ($i = 0; $i < count($chose_list); $i++) {
                    for ($j = 0; $j < count($chose_other_list); $j++) {
                        if ($chose_other_list[$j] == $chose_list[$i]) {
                            unset($chose_other_list[$j]);
                            break;
                        }
                    }
                }
            }
            sort($chose_other_list);
        }
        $grade_model = M('dict_grade');

        $Model = M('biz_exercise_library_chapter');

        $textbook_id = $result['textbook_id'];
        $grade_id = $result['grade_id'];
        $class_id = $result['class_id'];

        $model = M('biz_exercise_library_chapter');
        $chapter_result = $model->where('textbook_id=' . $textbook_id)->field('id,chapter')->group('chapter')->select();


        $grade_result = D('Biz_class')->getGradeListByTeacher(session('teacher.id'));


        //根据年级获得班级
        /*$class_model=M('biz_class');
        $class_result=$class_model->where("is_delete=0 and grade_id=".$grade_id." and class_teacher_id=".session('teacher.id'))->field('id,name')->select();*/

        $teacher_id = session('teacher.id');
        $class_result = D('Biz_class')->getClassListTeacher($teacher_id, $grade_id);
        $class_result = array_values($class_result);

        //根据年级获得所有学科
        $course_model = M('dict_course');
        $course_result = $course_model->where("auth_teacher_second.grade_id=" . $grade_id)
            ->field('dict_course.id,dict_course.course_name')
            ->join("auth_teacher_second on auth_teacher_second.teacher_id=" . session('teacher.id') . " and auth_teacher_second.course_id=dict_course.id")->group("dict_course.id")
            ->select();

        $this->assign('class', $class_result);
        $this->assign('course_list', $course_result);
        $this->assign('textbook_id', $textbook_id);
        $this->assign('data', $result);
        $this->assign('grade_list', $grade_result);
        $this->assign('chapter_list', $chapter_result);
        $this->assign('chose_list', $chose_list);
        $this->assign('chose_other_list', $chose_other_list);

        $this->display();
    }

    //收藏习题或取消习题
    public function collectExercise()
    {
        $id = intval($_GET['id']);

        if (session('teacher.id')) {
            $teacherId = session('teacher.id');
        } else {
            $url = $_SERVER['HTTP_REFERER'];
            $m = array();
            preg_match("/classroomId=(\d+)/", $url, $m);
            $classRoomId = explode('=', $m[0]);
            $classRoomId = $classRoomId[1];
            $result = M('biz_classroom_information')->where('id=' . $classRoomId)->find();
            $teacherId = $result['teacher_id'];
        }

        if (!$id) {
            echo 0;
        } else {
            $model = M('biz_exercise_collect');
            $check['exercise_id'] = $id;
            $check['role'] = 0;
            $check['user_id'] = $teacherId;
            $result = $model->where($check)->field('id')->find();
            if (!empty($result)) {
                $model->where($check)->delete();
                echo 2;
                die;
            } else {
                $add_data = $check;
                $add_data['create_at'] = time();
                $add_data['user_name'] = session('teacher.name');
                $model->add($add_data);
                echo 1;
            }
        }
    }

    //1_3_5,2_4_6格式,根据逗号分隔取得年级学科分册的数据
    public function getGradeMoreData()
    {
        //$string=$_GET['str'];
        $string = getParameter('str', 'str', false);
        if (!$string) {
            echo json_encode(array());
        } else {
            $data = array();
            //分隔逗号
            $string = rtrim($string, ',');
            $ids = explode(',', $string);
            $model = M('dict_grade');
            for ($i = 0; $i < count($ids); $i++) {
                //分隔下划线
                $grade_course_textbook = explode('_', $ids[$i]);
                $check['dict_grade.id'] = $grade_course_textbook[0];
                $check['dict_course.id'] = $grade_course_textbook[1];
                $check['biz_textbook.id'] = $grade_course_textbook[2];
                $result = $model->where($check)->join("biz_exercise_library_chapter on biz_exercise_library_chapter.grade_id=dict_grade.id")
                    ->join("dict_course on dict_course.id=biz_exercise_library_chapter.course_id")
                    ->join("biz_textbook on biz_textbook.id=biz_exercise_library_chapter.textbook_id")
                    ->join("auth_teacher_second on auth_teacher_second.grade_id=dict_grade.id and dict_course.id=auth_teacher_second.course_id and teacher_id=" . session('teacher.id'), 'left')
                    ->field("dict_grade.id grade_id,dict_grade.grade,dict_course.id course_id,dict_course.course_name,biz_textbook.id textbook_id,"
                        . "biz_textbook.name,IFNULL(auth_teacher_second.id,'no') second_id")->find();
                $data[] = $result;
            }
            echo json_encode($data);
        }
    }

    //根据习题id来判断是否收藏过该信息
    function ExerciseCollect()
    {
        $id = intval($_GET['id']);

        if (session('teacher.id'))
            $teacherId = session('teacher.id');
        else {
            $url = $_SERVER['HTTP_REFERER'];
            $m = array();
            preg_match("/classroomId=(\d+)/", $url, $m);
            $classRoomId = explode('=', $m[0]);
            $classRoomId = $classRoomId[1];
            if (!empty($classRoomId)) {
                $result = M('biz_classroom_information')->where('id=' . $classRoomId)->find();
                $teacherId = $result['teacher_id'];
            }
        }
        if (!$id) {
            echo json_encode(array());
        } else {
            if (!$teacherId) {
                echo json_encode(array());
                exit;
            }
            $Model = M('biz_exercise_library');
            $check['biz_exercise_library.id'] = $id;
            $result = $Model
                ->join('biz_exercise_library_chapter on biz_exercise_library_chapter.id=biz_exercise_library.chapter_id')
                ->join("biz_exercise_collect on biz_exercise_collect.exercise_id=biz_exercise_library.id and biz_exercise_collect.role=0 and biz_exercise_collect.user_id=" . $teacherId, 'left')
                ->field("biz_exercise_library.*,IFNULL(biz_exercise_collect.id,'no') is_collect,biz_exercise_library_chapter.course_id,grade_id,textbook_id")
                ->where($check)->find();
            echo json_encode($result);
        }
    }

    //根据章节来获得一个习题和收藏的数据
    public function ExerciseLibraryCollect()
    {
        $id = intval($_GET['id']);
        if (!$id) {
            echo json_encode(array());
        } else {
            $Model = M('biz_exercise_library');
            $c1['biz_exercise_library.chapter_id'] = $id;
            $result = $Model
                ->join('biz_exercise_library_chapter on biz_exercise_library_chapter.id=biz_exercise_library.chapter_id')
                ->join("biz_exercise_collect on biz_exercise_collect.exercise_id=biz_exercise_library.id and biz_exercise_collect.role=0 and biz_exercise_collect.user_id=" . session('teacher.id'), 'left')
                ->field("biz_exercise_library.*,IFNULL(biz_exercise_collect.id,'no') is_collect,biz_exercise_library_chapter.course_id,grade_id,textbook_id")
                ->where($c1)
                ->order('biz_exercise_library.question_id asc')->select();
            echo json_encode($result);
        }
    }

    //根据章和节来获得标题
    public function getExerciseChapterTitle()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $textbook_id = intval(I('textbook_id'));
        $chapter = I('chapter');
        $festival = I('festival');

        $check['chapter'] = $chapter;
        $check['festival'] = $festival;

        if ($chapter == '' || $festival == '') {
            echo json_encode(array());
        } else {
            $model = M('biz_exercise_library_chapter');
            if (empty($textbook_id)) {
                //$result=$model->where("chapter="."'$chapter' and festival="."'$festival'")->field("id,chapter,festival,title")->select();
                $result = $model->where($check)->field("id,chapter,festival,title")->select();

                foreach ($result as $k => $v) {
                    $data['chapter_id'] = $v['id'];
                    $data['is_pay'] = 1;
                    $num = M('biz_exercise_library')->where($data)->count();
                    if ($num == 0) {
                        unset($result[$k]);
                    }
                }

            } else {
                //$result=$model->where("chapter="."'$chapter' and festival="."'$festival' and textbook_id=".$textbook_id)->field("id,chapter,festival,title")->select();
                $check['textbook_id'] = $textbook_id;
                $result = $model->where($check)->field("id,chapter,festival,title")->select();
                foreach ($result as $k => $v) {
                    $data['chapter_id'] = $v['id'];
                    $data['is_pay'] = 1;
                    $num = M('biz_exercise_library')->where($data)->count();
                    if ($num == 0) {
                        unset($result[$k]);
                    }
                }
            }
            $result = array_values($result);
            echo json_encode($result);
        }
    }


    //根据章来获得节
    public function getExerciseChapter()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $chapter = I('chapter');
        $textbook_id = intval(I('textbook_id'));
        if ($chapter == '' || $textbook_id == false) {
            echo json_encode(array());
        } else {
            $model = M('biz_exercise_library_chapter');
            $check['chapter'] = $chapter;
            $check['textbook_id'] = $textbook_id;
            //$result=$model->where("chapter="."'$chapter' and textbook_id=".$textbook_id)->field("id,festival")->group('festival')->select();
            $result = $model->where($check)->field("id,festival")->group('festival')->select();
            echo json_encode($result);
        }
    }

    //根据年级获得班级
    public function getGradeClass()
    {
        $id = intval(I('id'));
        if (!$id) {
            echo json_encode(array());
        } else {
            /* $class_model = M('biz_class');
                 $class_result=$class_model->where("grade_id=".$id." and class_teacher_id=".session('teacher.id'))->field('biz_class.id,biz_class.name')
                         ->group("biz_class.name")
                         ->select();*/
            $teacher_id = session('teacher.id');
            if(empty($teacher_id))
            {
                $refer = $_SERVER['HTTP_REFERER'];
                preg_match_all('/.*?classroomId=(\\d+).*?/is', $refer, $matches);
                $classroomId = array_unique(array_pop($matches))[0];
                $roomInfo = M()->query("SELECT teacher_id FROM biz_classroom_information WHERE id=$classroomId");
                $teacher_id = $roomInfo[0]['teacher_id'];
            }

            $class_result = D('Biz_class')->getClassListTeacher($teacher_id, $id);
            $class_result = array_values($class_result);

            echo json_encode($class_result);
        }
    }

    public function getGradeClassCopy()
    {
        $id = intval(I('id'));
        if (!$id) {
            echo json_encode(array());
        } else {
            /* $class_model = M('biz_class');
                 $class_result=$class_model->where("grade_id=".$id." and class_teacher_id=".session('teacher.id'))->field('biz_class.id,biz_class.name')
                         ->group("biz_class.name")
                         ->select();*/
            $teacher_id = session('teacher.id');
            if (!$teacher_id)
                exit;
            $class_result = D('Biz_class')->getClassListTeacherCopy($teacher_id, $id);
            $class_result = array_values($class_result);

            echo json_encode($class_result);
        }
    }


    //根据年级获得学科
    public function getClassCourse()
    {
        $id = intval(I('id'));
        if (!$id) {
            echo json_encode(array());
        } else {
            $class_id = I('class_id');
            $map['id'] = $class_id;
            $classinfo = M('biz_class')->where($map)->find();

            if ($classinfo['class_status'] == 2) {
                /* $course_model = M('dict_course');
                $course_result=$course_model->where("auth_teacher_second.grade_id=".$id)
                    ->field('dict_course.id,dict_course.course_name')
                    ->join("auth_teacher_second on auth_teacher_second.teacher_id=".session('teacher.id')." and auth_teacher_second.course_id=dict_course.id")->group("dict_course.id")
                    ->select();
                echo json_encode($course_result);*/
                $course_map['auth_teacher_second.teacher_id'] = $classinfo['class_teacher_id'];
                $course_result = M('auth_teacher_second')
                    ->join("dict_course on dict_course.id = auth_teacher_second.course_id")
                    ->field('dict_course.id,dict_course.course_name')
                    ->where($course_map)
                    ->group("dict_course.id")
                    ->select();
                echo json_encode($course_result);

            } else {

                $bctmap['class_id'] = $class_id;
                $bctmap['teacher_id'] = session('teacher.id');
                $course_result = M('biz_class_teacher')
                    ->join('dict_course on dict_course.id=biz_class_teacher.course_id')
                    ->field('dict_course.id,dict_course.course_name')
                    ->where($bctmap)
                    ->select();

                echo json_encode($course_result);
            }
        }
    }

    //根据学科获得教材
    public function getCourseTextbook()
    {

        $course_id = intval(I('course_id'));
        $grade_id = intval(I('grade_id'));
        if (!$course_id || !$grade_id) {
            echo json_encode(array());
        } else {
            $course_model = M('biz_textbook');
            $textbook_result = $course_model->where("grade_id=" . $grade_id . " and course_id=" . $course_id)
                ->field('id,name')
                ->select();
            echo json_encode($textbook_result);
        }
    }

    //选择作业进行复制  没用到
    public function homeworkChoose()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '添加作业');
        $this->assign('navicon', 'zuoyexitong');

        //这里这个是章节的主键ID,$checked_ids上页已选ID
        $chapter_id = intval(I('chapter_id'));
        $homework_id = intval(I('homework_id'));
        $checked_ids = I('b_id');
        $other_chose_ids = I('c_id');
        if (!$chapter_id || !$homework_id) {
            redirect(U('Index/systemError'));
        }
        $model = M('biz_exercise_library');
        $result = $model->where("chapter_id=" . $chapter_id)->select();

        $this->assign('homework_id', $homework_id);
        $this->assign('checked_ids', $checked_ids);
        $this->assign('chapter_id', $chapter_id);
        $other_chose_arr = array();
        if ($other_chose_ids != '') {
            $other_chose_arr = explode(',', $other_chose_ids);
            $other_chose_arr = array_unique($other_chose_arr);
        }
        $this->assign('other_chose', $other_chose_arr);
        $this->assign('data', $result);

        $this->display();
    }

    //获取作业库
    public function getExercisesByTextbook()
    {
        //$textbookId = $_GET['textbook_id'];
        $textbookId = getParameter('textbook_id', 'int');

        $Model = M('biz_exercise_library_chapter');

        $result = $Model
            ->where("textbook_id=$textbookId")
            ->order('create_at desc')
            ->select();

        foreach ($result as $k => $v) {
            $datachapter['chapter_id'] = $v['id'];
            $datachapter['is_pay'] = 1;
            $numchapter = M('biz_exercise_library')->where($datachapter)->count();
            if ($numchapter == 0) {
                unset($result[$k]);
            }
        }
        $result = array_values($result);
        $this->ajaxReturn($result);
    }


    //布置作业
    public function assignHomework()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if ($_POST) {

            //$data['homework_name'] = remove_xss($_POST['homework_name']);
            //$data['claim'] = remove_xss($_POST['claim']);
            $data['homework_name'] = getParameter('homework_name', 'str', false);
            $data['claim'] = getParameter('claim', 'str', false);

            $data['class_id'] = intval($_POST['class_id']);
            $data['course_id'] = intval($_POST['course_id']);
            $data['grade_id'] = intval($_POST['grade_id']);
            $data['textbook_id'] = intval($_POST['textbook_id']);
            $data['homework_type'] = intval($_POST['type_id']);

            $data['teacher_id'] = session('teacher.id');
            $data['teacher_name'] = session('teacher.name');
            $data['create_at'] = time();
            $data['update_at'] = time();
            if ($data['homework_type'] == 1) {
                $data['homework_type'] = '课堂作业';
            } else {
                $data['homework_type'] = '课后作业';
            }

            //这里先判断这个班级和学科等是否存在
            $grade_id = $data['grade_id'];
            $class_id = $data['class_id'];
            $course_id = $data['course_id'];
            $class_model = M('biz_class');
            $class_result = $class_model->where('biz_class.id=' . $class_id . " and biz_class.grade_id=" . $grade_id . " and auth_teacher_second.course_id=" . $course_id)
                ->join('auth_teacher_second on auth_teacher_second.teacher_id=biz_class.class_teacher_id and '
                    . 'auth_teacher_second.grade_id=biz_class.grade_id')->field('biz_class.id')->find();

            /*if(empty($class_result)){
                redirect(U('Index/systemError'));
            }*/

            $exerciseIds = $_POST['exercise_ids'];
            $exerciseIdArr = explode(';', $exerciseIds);
            $data['exercises_number'] = count($exerciseIdArr);

            //开插入作业表
            $model = M('biz_homework');
            $model->startTrans();
            if ((!$homework_id = $model->add($data))) {
                redirect(U('Index/systemError'));
            }

            $exercise_model = M('biz_exercise_library');
            $homework_exercise = M('biz_homework_exercise');
            $tag = 0;
            //这里的逻辑是没有找到该问题就跳过该问题
            //$exerciseIdArr = explode(';', $exerciseIds);

            foreach ($exerciseIdArr as $v) {
                $arr = explode('.', $v);
                $temp_result = $exercise_model->where("id=" . intval($arr[1]))->field('id,chapter_id')->find();
                if (empty($temp_result)) {
                    continue;
                } else {
                    $exercise_data['homework_id'] = $homework_id;
                    $exercise_data['exercise_id'] = intval($arr[1]);
                    $exercise_data['chapter_id'] = intval($arr[0]);
                    if (!$homework_exercise->add($exercise_data)) {
                        $model->rollback();
                        redirect(U('Index/systemError'));
                    } else {
                        $tag = 1;
                    }
                }
            }
            if ($tag == 1) {
                $model->commit();
            } else {
                $model->rollback();
                redirect(U('Index/systemError'));
            }
            $this->redirect("Teach/homework");

        } else {
            $this->assign('module', '班级行');
            $this->assign('nav', '作业系统');
            $this->assign('subnav', '布置作业');
            $this->assign('navicon', 'zuoyexitong');

            $filter['grade_more'] = $_REQUEST['grade'];
            $filter['exercise_id'] = $_REQUEST['ids'];

            if (!empty($filter['grade_more'])) {
                $grade_course_textbook = explode('_', $filter['grade_more']);
                if (count($grade_course_textbook) >= 3) {
                    $model = M('dict_grade');
                    $check['dict_grade.id'] = $grade_course_textbook[0];
                    $check['dict_course.id'] = $grade_course_textbook[1];
                    $check['biz_textbook.id'] = $grade_course_textbook[2];
                    $result = $model->where($check)->join("biz_exercise_library_chapter on biz_exercise_library_chapter.grade_id=dict_grade.id")
                        ->join("dict_course on dict_course.id=biz_exercise_library_chapter.course_id")
                        ->join("biz_textbook on biz_textbook.id=biz_exercise_library_chapter.textbook_id")
                        ->join("auth_teacher_second on auth_teacher_second.grade_id=dict_grade.id and dict_course.id=auth_teacher_second.course_id and teacher_id=" . session('teacher.id'))
                        ->field("dict_grade.id grade_id,dict_grade.grade,dict_course.id course_id,dict_course.course_name,biz_textbook.id textbook_id,"
                            . "biz_textbook.name")->find();
                    if (empty($result)) {
                        redirect(U('Index/systemError'));
                        //echo '错误页面,该年级学科含有教师不支持的学科或年级';
                    } else {
                        //拿到某个年级下的所有班级
                        $class_model = M('biz_class');
                        $class_result = $class_model->where("grade_id=" . $check['dict_grade.id'] . " and class_teacher_id=" . session("teacher.id"))->field('id,name')->select();

                        //拿到所有习题
                        $id_array = explode(',', $filter['exercise_id']);
                        $id_array = array_unique($id_array);
                        $exercise_model = M('biz_exercise_library');
                        $condition_string = "biz_exercise_library.id in (" . $filter['exercise_id'] . ")";
                        $exercise_result = $exercise_model->where($condition_string . " and auth_teacher_second.teacher_id=" . session("teacher.id"))
                            ->join("biz_exercise_library_chapter on biz_exercise_library_chapter.id=biz_exercise_library.chapter_id")
                            ->join("auth_teacher_second on auth_teacher_second.course_id=biz_exercise_library_chapter.course_id and "
                                . "auth_teacher_second.grade_id=biz_exercise_library_chapter.grade_id")
                            ->field('biz_exercise_library_chapter.id chapter_id,biz_exercise_library.id exercise_id')
                            ->select();
                        if (empty($exercise_result) || count($id_array) != count($exercise_result)) {
                            redirect(U('Index/systemError'));
                            //echo '错误页面,该习题含有教师不支持的习题,或习题不存在';
                        }
                        $this->assign('default_grade', $check['dict_grade.id']);
                        $this->assign('default_course', $check['dict_course.id']);

                        $textbook_array['id'] = $check['biz_textbook.id'];
                        $textbook_array['name'] = $result['name'];
                        $this->assign('default_textbook', $textbook_array);

                        $this->assign('class_list', $class_result);

                        $exercise_list_string = '';
                        foreach ($exercise_result as $value) {
                            $exercise_list_string .= $value['chapter_id'] . '.' . $value['exercise_id'] . ';';
                        }
                        $exercise_list_string = rtrim($exercise_list_string, ';');
                        $this->assign('exercise_list', $exercise_list_string);
                    }
                }
            }

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();

            $this->assign('courses', $courses);

            /* $grade_model = M('dict_grade');
            $grade_result=$grade_model->where("biz_class.class_teacher_id=".session('teacher.id'))
                ->field('dict_grade.id,dict_grade.grade')
                ->join("biz_class on biz_class.grade_id=dict_grade.id")->group("dict_grade.id")
                ->select();*/
            $grade_result = D('Biz_class')->getGradeListByTeacher(session('teacher.id'));

            $this->assign('grade_list', $grade_result);     //echo $grade_model->getLastSql();die;

            $Model = M('biz_textbook');
            $textbooks = $Model->order('sort_order asc')->select();
            $this->assign('textbooks', $textbooks);

            $Model = M('biz_class');
            $classes = $Model
                ->where("class_teacher_id=" . session("teacher.id"))
                ->order('name asc')->select();
            $this->assign('classes', $classes);

            $this->display();
        }

    }

    public function moveClass()
    {
        if (!session('?teacher')) redirect(U('Index/index'));
        if ($_POST) {
            //$classId = $_POST['classId'];
            $classId = getParameter('classId', 'int', false);
            $myTeacherId = session('teacher.id');
            //$desTeacherTel = $_POST['telephone'];
            //$desTeacherName = $_POST['name'];
            $desTeacherTel = getParameter('telephone', 'str', false);
            $desTeacherName = getParameter('name', 'str', false);

            $teacherModel = M('auth_teacher');
            $teacherClassModel = M('biz_class');
            $res = $teacherModel->where("name=$desTeacherName and telephone=$desTeacherTel")->find();

            $desTeacherId = $res['id'];
            $desData['class_teacher'] = $res['name'];
            $desData['class_teacher_id'] = $desTeacherId;
            $teacherClassModel->where("id=$classId")->save($desData);
        } else {
            $this->display();
        }
    }


    //添加学生
    public function addStudent()
    {
        $this->assign('module', '班级行');
        $this->assign('nav', '班级管理');
        $this->assign('subnav', '班级学生管理');
        $this->assign('navicon', 'banjiguanli');
        if ($_POST) {
            /*
            $check['student_name'] = $_POST['student_name'];
            $check['parent_tel'] = remove_xss($_POST['telephone']);

            $data['student_name'] = $_POST['student_name'];
            $data['sex'] = $_POST['sex'];
            $data['birth_date'] = strtotime($_POST['birthday']);
            $data['email'] = $_POST['email'];
            $data['student_id'] = $_POST['student_id'];
            $data['id_card'] = $_POST['id_card'];
            $data['parent_tel'] = $check['parent_tel'];
            $data['password'] = sha1($_POST['password']);
            $data['avatar'] = $_POST['os_avatar'];
            */
            $check['student_name'] = getParameter('student_name', 'str', false);
            $check['parent_tel'] = getParameter('telephone', 'str', false);

            $data['student_name'] = $check['student_name'];
            $data['sex'] = getParameter('sex', 'str', false);
            $data['birth_date'] = strtotime(getParameter('birthday', 'str', false));
            $data['email'] = getParameter('email', 'str', false);
            $data['student_id'] = getParameter('student_id', 'str', false);
            $data['id_card'] = getParameter('id_card', 'str', false);
            $data['parent_tel'] = $check['parent_tel'];
            $data['password'] = sha1(getParameter('password', 'str', false));
            $data['avatar'] = getParameter('os_avatar', 'str', false);

            $teacherId = session('teacher.id');
            $result = M('auth_teacher')->where("id=$teacherId")->find();
            $schoolId = $result['school_id'];
            $class_id = intval($_GET['classId']);

            $data['school_id'] = $schoolId;
            $data['create_at'] = time();
            $data['update_at'] = time();

            $data_info['student_name'] = $data['student_name'];
            $data_info['parent_tel'] = $data['parent_tel'];
            $find_parent = M('auth_student')->where($data_info)->find();
            if (!empty($find_parent)) {

                $arr['msg'] = '该名学生已经存在';
                $arr['code'] = -8;
                echo json_encode($arr);
                die;
            }


            if (empty($_POST['telephone']) || empty($_POST['password']) || empty($_POST['student_name'])) {
                $arr['msg'] = '请填写完整信息';
                $arr['code'] = -4;
                echo json_encode($arr);
                die;
            }

            if ($_POST['confirm_password'] != $_POST['password']) {
                $arr['msg'] = '密码与确认密码不一致';
                $arr['code'] = -3;
                echo json_encode($arr);
                die;
            }
            $parent_demol = M('auth_parent');
            $parent_tel = $data['parent_tel'];
            $parent_result = $parent_demol->where("telephone=" . "'$parent_tel'")->field('id,parent_name,telephone')->find();

            if (!empty($parent_result)) {
                $data['parent_id'] = $parent_result['id'];
                $data['parent_name'] = $parent_result['parent_name'];
            }

            $studentModel = M('auth_student');
            $result = $studentModel->where($check)->find();//判断是否已经注册

            if (empty($result)) {
                $id = $studentModel->add($data);

                $classStudentModel = M('biz_class_student');
                $joinData['class_id'] = $class_id;
                $joinData['student_id'] = $id;
                $join_result = $classStudentModel->where($joinData)->find();
                if (empty($join_result)) {
                    $joinData['status'] = 2;
                    $joinData['create_at'] = time();
                    $joinData['update_at'] = time();
                    $classStudentModel->add($joinData);
                    //学生数加1
                    $Model = M('biz_class');
                    //$class_id = $_GET['classId'];
                    $class_id = getParameter('classId', 'int', false);
                    $Model->where("id=$class_id")->setInc('student_count', 1);
                }
                $arr['msg'] = '添加成功';
                $arr['code'] = 0;
                $arr['id'] = $id;
                $parameter_arr = array(
                    'msg' => array(session('teacher.name'), $check['student_name']),
                    'url' => array(
                        'type' => 0
                    )
                );
                A('Home/Message')->addPushUserMessage('ADDCHILD_TEACHER', 4, $parent_result['id'], $parameter_arr);
                echo json_encode($arr);
                die;
            } else {
                //TODO:update record if force add
                if ('true' != $_POST['forceAdd']) {
                    $arr['msg'] = '该用户已被注册';
                    $arr['code'] = -2;
                    echo json_encode($arr);
                    die;
                } else //force add record
                {
                    $studentModel->where($check)->save($data);
                    $res = $studentModel->where($check)->field('id')->find();
                    $id = $res['id'];

                    $classStudentModel = M('biz_class_student');
                    $joinData['class_id'] = $class_id;
                    $joinData['student_id'] = $id;
                    $join_result = $classStudentModel->where($joinData)->find();
                    if (empty($join_result)) {
                        $joinData['status'] = 2;
                        $joinData['create_at'] = time();
                        $joinData['update_at'] = time();
                        $classStudentModel->add($joinData);
                        //学生数加1
                        $Model = M('biz_class');
                        //$class_id = $_GET['classId'];
                        $class_id = getParameter('classId', 'int', false);
                        $Model->where("id=$class_id")->setInc('student_count', 1);
                        $arr['msg'] = '添加成功';

                        $parameter_arr = array(
                            'msg' => array(session('teacher.name'), $check['student_name']),
                            'url' => array(
                                'type' => 0
                            )
                        );
                        A('Home/Message')->addPushUserMessage('ADDCHILD_TEACHER', 4, $parent_result['id'], $parameter_arr);
                    } else
                        $arr['msg'] = '该学生已加入该班级,无需再次添加';

                    $arr['code'] = 0;
                    $arr['id'] = $id;
                    echo json_encode($arr);
                    die;
                }
            }
        } else {
            //$classId = $_GET['classId'];
            $classId = getParameter('classId', 'int', false);
            $this->assign('classId', $classId);
            $this->display();
        }
    }

    public function downloadStudentListFile()
    {
        $csv = new CSV();
        $file = "Public/csv/StudentListDemo01.csv";
        $csv->downloadFile($file);
    }

    function verifyImportStudentData($data)
    {
        $returnResult['errCode'] = 0;
        $resMsg = array();

        $phone = $this->getNumToStr($data[2]);
        if (!preg_match("/^1[34578]{1}\d{9}$/", $phone)) {
            $resMsg[] = '手机号码格式不正确';
        }

        if (!empty($data[4])) {

            $is_date = strtotime($data[4]) ? strtotime($data[4]) : false;
            if ($is_date === false) {
                $resMsg[] = '出生日期不正确';
            }

        }

        if (!empty($data[5])) {
            $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
            if (!preg_match($pattern, $data[5])) {
                $resMsg[] = 'Email不合法';
            }
        }
        /*if(!preg_match("/^1[34578]{1}\d{9}$/",$phone))
        {
            $resMsg[] = '手机号码格式不正确';
        }*/

        if (count($resMsg) != 0) {
            $returnResult['errCode'] = -1;
            $returnResult['errMsg'] = join(' ', $resMsg);
        }
        //var_dump($data[1]);exit;
        return $returnResult;
    }

    public function getNumToStr($num)
    {
        if (stripos($num, 'e') === false) return $num;
        $num = trim(preg_replace('/[=\'"]/', '', $num, 1), '"');//出现科学计数法，还原成字符串
        $result = "";
        while ($num > 0) {
            $v = $num - floor($num / 10) * 10;
            $num = floor($num / 10);
            $result = $v . $result;
        }
        return $result;
    }

    function echoResult($code, $msg)
    {
        echo json_encode(array(
            'code' => $code,
            'msg' => $msg
        ));
        die;
    }

    public function NumToStr($num)
    {
        if (stripos($num, 'e') === false) return $num;
        $num = trim(preg_replace('/[=\'"]/', '', $num, 1), '"');//出现科学计数法，还原成字符串
        $result = "";
        while ($num > 0) {
            $v = $num - floor($num / 10) * 10;
            $num = floor($num / 10);
            $result = $v . $result;
        }
        return $result;
    }


    /*
     * code
     * 1为gb2312转utf8
     * 2为gbk转utf8
     * 3为utf-8直接返回
     * 4为utf8转gbk
     */
    function encode_string($code, $string)
    {
        $return_string = '';
        if ($code == 1) {
            $return_string = iconv('gbk', 'utf-8', $string);
        } else if ($code == 2) {
            $return_string = iconv('gbk', 'utf-8', $string);
        } else if ($code == 0) {
            $return_string = $string;
        } else {
            $return_string = iconv('utf-8', 'gbk', $string);
        }
        return $return_string;
    }

    public function saveNumToStr($num)
    {
        if (stripos($num, 'e') === false) return $num;
        $num = trim(preg_replace('/[=\'"]/', '', $num, 1), '"');//出现科学计数法，还原成字符串
        $result = "";
        while ($num > 0) {
            $v = $num - floor($num / 10) * 10;
            $num = floor($num / 10);
            $result = $v . $result;
        }
        return $result;
    }

    public function importStudentsList()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if (empty($_FILES)) {
            $this->echoResult(-1, '上传文件为空');
        }
        //$classId = $_GET['classId'];
        $classId = getParameter('classId', 'int', false);
        if (empty($classId)) {
            $this->echoResult(-1, '班级CLASSID为空,请不要修改网页URL');
        }
        $csv = new CSV();
        $result = $csv->getCsvData($_FILES);
        if (!is_array($result)) {
            $this->echoResult(-1, 'CSV文件为空');
        }

        //默认是utf8
        $encode = mb_detect_encoding($result['result'][0][0], array('UTF-8', 'GB2312', 'GBK', 'EUC-CN'));
        if ($encode == 'EUC-CN' || $encode == 'GB2312') {
            $isUTF8 = 1;
        } else if ($encode == 'GBK') {
            $isUTF8 = 2;
        } else if ($encode == 'UTF-8') {
            $isUTF8 = 0;
        }

        /*$isUTF8 = 1;
        if(!json_encode($result))
        {
          $isUTF8 = 0;
        }*/
        $data = $result['result'];
        $length = $result['length'];
        $data_values = '';

        $model = M('auth_student');
        $parent_demol = M('auth_parent');
        $classStudentModel = M('biz_class_student');
        $classModel = M('biz_class');
        $vip_config = C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');

        //查询某个手机号是否存在,不存在则跳过改行
        $errArray = array();

        for ($i = 1; $i < $length; $i++) {

            //TODO:data format verify

            $data[$i][0] = $this->encode_string(2, $data[$i][0]);
            $data[$i][1] = $this->encode_string($isUTF8, $data[$i][1]);

            $verifyResult = $this->verifyImportStudentData($data[$i]);

            if ($verifyResult['errCode'] == -1) //verify failed
            {
                $errArray[] = array_merge($data[$i], array($verifyResult['errMsg']));
                continue;
            }
            $dbError = '';

            $model->startTrans();

            $add_data['student_name'] = $data[$i][0];
            $add_data['sex'] = $data[$i][1];
            //$add_data['parent_name'] = iconv('gb2312', 'utf-8', $data[$i][2]);
            $add_data['student_id'] = $this->encode_string($isUTF8, $data[$i][2]);
            $add_data['birth_date'] = strtotime($this->encode_string($isUTF8, $data[$i][3]));
            $add_data['create_at'] = time();
            $add_data['update_at'] = time();
            $add_data['email'] = $this->encode_string($isUTF8, $data[$i][4]);
            $add_data['parent_tel'] = $this->encode_string($isUTF8, $data[$i][5]);
            //$add_data['id_card'] = $this->NumToStr(iconv('gb2312', 'utf-8', $data[$i][6]));
            $add_data['id_card'] = $this->saveNumToStr($this->encode_string($isUTF8, $data[$i][6]));
            $add_data['password'] = sha1('123456');
            $add_data['school_id'] = session('teacher.school_id');

            $parent_tel = $add_data['parent_tel'];
            $parent_result = $parent_demol->where("telephone=" . $parent_tel)->field('id,parent_name,telephone')->find();
            if (!empty($parent_result)) {
                $add_data['parent_id'] = $parent_result['id'];
                $add_data['parent_name'] = $parent_result['parent_name'];
                $add_data['parent_tel'] = $parent_result['telephone'];
            }

            $check['student_name'] = $add_data['student_name'];
            $check['parent_tel'] = $add_data['parent_tel'];

            $student_result = $model->where($check)->find();
            $stuId = 0;
            $is_register = 0;
            if (empty($student_result)) {
                $stuId = $model->add($add_data);
                if (!$stuId) {
                    $dbError = '学生注册错误';
                } else {
                    $is_register = 1;
                }
            } else {
                unset($add_data['password']);
                if (false == $model->where($check)->save($add_data)) {
                    $dbError = "更新学生信息失败";
                }
                $result = $model->where($check)->field('id')->find();
                $stuId = $result['id'];
            }
            //todo: add join table

            $joinData['class_id'] = $classId;
            $joinData['student_id'] = $stuId;
            $join_result = $classStudentModel->where($joinData)->find();
            if (empty($join_result)) {
                $joinData['status'] = 2;
                $joinData['create_at'] = time();
                $joinData['update_at'] = time();
                if (!$classStudentModel->add($joinData)) {
                    $dbError = '学生关联班级失败';
                }

                if (false == $classModel->where("id=$classId")->setInc('student_count', 1)) {
                    $dbError = '班级人数加1错误';
                }

                if ($is_register == 1 && $dbError == '') {
                    //加入权限
                    if ($vip_config && $vip_config <= 3) {
                        if (empty($parent_result)) {
                            $result = give_new_vip_operation(3, $vip_config, $stuId, session('teacher.school_id'));
                        } else {
                            $result = give_new_vip_operation(3, $vip_config, $stuId, session('teacher.school_id'));
                        }
                        if ($result['status'] == 'failed') {
                            $dbError = '权限操作失败';
                        }
                        /*
                        $vipdata = array(
                            'user_id' => $stuId,
                            'role_id' => 3,
                            'auth_id' => 4,
                            'auth_start_time' => time(),
                            'auth_end_time' => time()+3600*24*30*3,
                            'timetype' => 1,
                        );

                        if($vip_config==1){
                            //赠送90天vip
                           $vipdata['auth_id']=4;

                        }elseif($vip_config==2){
                            //普通权限
                            $vipdata['auth_id']=2;
                            $vipdata['auth_start_time']=0;
                            $vipdata['auth_end_time']=0;
                            $vipdata['timetype']=0;

                        }elseif($vip_config==3){
                            $shcool_model=M('dict_schoollist');
                            $school_info=$shcool_model->where('id='.session('teacher.school_id'))->find();
                            //根据学校的权限来赋予学生的权限
                            if ($school_info['user_auth'] == 3 && time() >= $school_info['auth_start_time'] && time() < $school_info['auth_end_time'] ) {
                                //给予学校的权限
                                $vipdata['timetype']=$school_info['timetype'];
                                $vipdata['auth_id']=3;
                                $vipdata['auth_end_time']=$school_info['auth_end_time'];

                            } else {
                                //普通权限
                                $vipdata['auth_id']=2;
                                $vipdata['auth_start_time']=0;
                                $vipdata['auth_end_time']=0;
                                $vipdata['timetype']=0;
                            }
                        }
                        $auth_type_use = D('Account_auths');
                        $auth_list = $auth_type_use->addUserVip( $vipdata );
                        */
                    }
                }
            } else {
                $dbError = ('该学生已经在班级中');
            }
            if ('' != $dbError) {
                $model->rollback();
                $errArray[] = array_merge($data[$i], array($dbError));
            } else {
                $model->commit();
                $parentTel = $this->encode_string($isUTF8, $data[$i][5]);
                $parentInfo = D('Auth_parent')->getParentInfoByTelephone($parentTel);
                $parameter_arr = array(
                    'msg' => array(session('teacher.name'), $data[$i][0]),
                    'url' => array(
                        'type' => 0
                    )
                );
                A('Home/Message')->addPushUserMessage('ADDCHILD_TEACHER', 4, $parentInfo['id'], $parameter_arr);
            }
        }
        if (count($errArray) != 0) {
            $this->echoResult(-2, json_encode($errArray));
        }
        echo $this->echoResult(0, '导入成功');
    }

    public function teacherImportStudentsList()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if (empty($_FILES)) {
            $this->echoResult(-1, '上传文件为空');
        }
        //$classId = $_GET['classId'];
        $classId = getParameter('classId', 'int', false);

        $class_result_info_add = D('Biz_class')->getClassAndGradeInfo($classId);
        if (empty($classId)) {
            $this->echoResult(-1, '班级CLASSID为空,请不要修改网页URL');
        }
        $csv = new CSV();
        $result = $csv->getCsvData($_FILES);
        if (!is_array($result)) {
            $this->echoResult(-1, 'CSV文件为空');
        }

        //默认是utf8
        $encode = mb_detect_encoding($result['result'][0][0], array('UTF-8', 'GB2312', 'GBK', 'EUC-CN'));
        if ($encode == 'EUC-CN' || $encode == 'GB2312') {
            $isUTF8 = 1;
        } else if ($encode == 'GBK') {
            $isUTF8 = 2;
        } else if ($encode == 'UTF-8') {
            $isUTF8 = 0;
        }

        $data = $result['result'];
        $length = $result['length'];

        foreach ($data as $dk => $dv) {
            foreach ($dv as $cdk => $cdv) {
                if (empty($data[$dk][0])) {
                    $data[$dk][0] = '';
                }
                if (empty($data[$dk][1])) {
                    $data[$dk][1] = '';
                }
                if (empty($data[$dk][2])) {
                    $data[$dk][2] = '';
                }
                if (empty($data[$dk][3])) {
                    $data[$dk][3] = '';
                }
                if (empty($data[$dk][4])) {
                    $data[$dk][4] = '';
                }
                if (empty($data[$dk][5])) {
                    $data[$dk][5] = '';
                }

            }
        }


        $model = M('auth_student');
        $parent_demol = M('auth_parent');
        $classStudentModel = M('biz_class_student');
//        $classModel = M('biz_class');

        $errArray = array();
        $datacount = count($data);
        for ($i = 1; $i < $length; $i++) {

            $verifyResult = $this->verifyImportStudentData($data[$i]);

            if ($verifyResult['errCode'] == -1) //verify failed
            {
                $errArray[] = array_merge($data[$i], array($verifyResult['errMsg']));
                continue;
            }
            $dbError = '';
            $model->startTrans();

            $add_data['student_name'] = $this->encode_string($isUTF8, $data[$i][0]);
            $add_data['sex'] = $this->encode_string($isUTF8, $data[$i][1]);
            if (empty($add_data['sex'])) {
                $add_data['sex'] = '男';
            }
            $add_data['parent_tel'] = $this->getNumToStr($this->encode_string($isUTF8, $data[$i][2]));

            //家长的信息
            $add_parent_data['telephone'] = $this->getNumToStr($this->encode_string($isUTF8, $data[$i][2]));
            //$add_parent_data['parent_name'] = strtotime($this->encode_string($isUTF8,$data[$i][3]));
            $add_parent_data['parent_name'] = $this->encode_string($isUTF8, $data[$i][3]); //文字不进行编码
            $add_parent_data['password'] = sha1('123456');
            $add_parent_data['parent_name_is'] = $data[$i][3]; //文字不进行编码
            $add_parent_data['create_at'] = time();
            $add_parent_data['update_at'] = time();
            $add_parent_data['update_at'] = session('teacher.school_id');
            //家长信息结束

            $add_data['create_at'] = time();
            $add_data['update_at'] = time();
            $add_data['birth_date'] = strtotime($this->saveNumToStr($this->encode_string($isUTF8, $data[$i][4])));

            $add_data['email'] = $this->encode_string($isUTF8, $data[$i][5]);
            $add_data['password'] = sha1('123456');
            $add_data['school_id'] = session('teacher.school_id');

            $student_info_map['student_name'] = $add_data['student_name'];
            $student_info_map['parent_tel'] = $add_parent_data['telephone'];
            $student_info = $model->where($student_info_map)->find();

            $is_parent_exists_map['telephone'] = $add_parent_data['telephone'];
            $parent_info = $parent_demol->where($is_parent_exists_map)->find();

            //判断学生是否存在
            $classinfo_data = M('biz_class')->where("id={$classId}")->find();

            if ($classinfo_data['class_status'] == 1) {
                $add_data['apply_school_status'] = 1;
            } else {
                $add_data['apply_school_status'] = 0;
            }

            if (!empty($student_info)) {


                if (empty($add_parent_data['parent_name_is']) && !empty($parent_info)) { //家长为空&&家长表存在家长 学生和家长关联

                    if ($classinfo_data['class_status'] == 1) { //校内班判断是否学校一致

                        if ($student_info['school_id'] != $classinfo_data['school_id']) {

                            $dbError = "该学生不是本学校的学生无法导入";
                        }

                        $whereis['biz_class_student.student_id'] = $student_info['id'];
                        $whereis['biz_class.class_status'] = 1;

                        $is_class_add = M('biz_class_student')
                            ->join('biz_class on biz_class.id=biz_class_student.class_id')
                            ->where($whereis)
                            ->find();

                        if (!empty($is_class_add)) {

                            $dbError = "该学生已经加入过校建班级";
                        }

                    }
                    /* $relation_map['student_id'] = $student_info['id'];
                                         $relation_map['parent_id'] = $parent_info['id'];
                                         $relation_map['create_at'] = time();
                                         $relation_map['parent_tel'] = $parent_info['telephone'];


                                         $stu_parent_relation = M('auth_student_parent_contact')->add( $relation_map );
                     */
                    $relation_map['student_id'] = $student_info['id'];
                    $relation_map['parent_id'] = $parent_info['id'];
                    $stu_parent_relation = M('auth_student_parent_contact')->where($relation_map)->find();
                    if (empty($stu_parent_relation)) {
                        $relation_map['create_at'] = time();
                        $relation_map['parent_tel'] = $parent_info['telephone'];
                        $stu_parent_relation_id = M('auth_student_parent_contact')->add($relation_map);

                        if (!$stu_parent_relation_id) {
                            $dbError = "学生和家长关联失败,请重新尝试";
                        }

                    }

                } elseif (!empty($add_parent_data['parent_name_is']) && !empty($parent_info)) { //家长不为空 && 家长存在  判断有没有关联关系，没有创建关系


                    if ($classinfo_data['class_status'] == 1) { //校内班判断是否学校一致
                        if ($student_info['school_id'] != $classinfo_data['school_id']) {
                            $dbError = "该学生不是本学校的学生无法导入";
                        }

                        $whereis['biz_class_student.student_id'] = $student_info['id'];
                        $whereis['biz_class.class_status'] = 1;

                        $is_class_add = M('biz_class_student')
                            ->join('biz_class on biz_class.id=biz_class_student.class_id')
                            ->where($whereis)
                            ->find();

                        if (!empty($is_class_add)) {
                            $dbError = "该学生已经加入过校建班级";
                        }
                    }

                    $relation_map['student_id'] = $student_info['id'];
                    $relation_map['parent_id'] = $parent_info['id'];
                    $stu_parent_relation = M('auth_student_parent_contact')->where($relation_map)->find();
                    if (empty($stu_parent_relation)) {
                        $relation_map['create_at'] = time();
                        $relation_map['parent_tel'] = $parent_info['telephone'];
                        $stu_parent_relation_id = M('auth_student_parent_contact')->add($relation_map);

                        if (!$stu_parent_relation_id) {
                            $dbError = "学生和家长关联失败,请重新尝试";
                        }

                    }


                } elseif (!empty($add_parent_data['parent_name_is']) && empty($parent_info)) { //家长不为空 && 家长不存在 创建家长账号 关联关系


                    if ($classinfo_data['class_status'] == 1) { //校内班判断是否学校一致
                        if ($student_info['school_id'] != $classinfo_data['school_id']) {
                            $dbError = "该学生不是本学校的学生无法导入";
                        }

                        $whereis['biz_class_student.student_id'] = $student_info['id'];
                        $whereis['biz_class.class_status'] = 1;

                        $is_class_add = M('biz_class_student')
                            ->join('biz_class on biz_class.id=biz_class_student.class_id')
                            ->where($whereis)
                            ->find();

                        if (!empty($is_class_add)) {
                            $dbError = "该学生已经加入过校建班级";
                        }

                    }


                    unset($add_parent_data['parent_name_is']);
                    $addparent = $parent_demol->add($add_parent_data);

                    give_new_vip_operation(4, 1, $addparent, ''); //2教师,3学生,4家长

                    if (!$addparent) {
                        $dbError = "家长创建失败,请重新尝试";
                    }

                    $relation_map['student_id'] = $student_info['id'];
                    $relation_map['parent_id'] = $addparent;
                    $stu_parent_relation = M('auth_student_parent_contact')->where($relation_map)->find();

                    if (empty($stu_parent_relation)) {
                        $relation_map['create_at'] = time();
                        $relation_map['parent_tel'] = $add_data['parent_tel'];
                        $stu_parent_relation_id = M('auth_student_parent_contact')->add($relation_map);

                        if (!$stu_parent_relation_id) {
                            $dbError = "学生和家长已经关联";
                        }

                    }


                } else { //家长为空&&家长表不存在家长 学生和家长关联

                    if ($classinfo_data['class_status'] == 1) { //校内班判断是否学校一致

                        if ($student_info['school_id'] != $classinfo_data['school_id']) {

                            $dbError = "该学生不是本学校的学生无法导入";
                        }

                        $whereis['biz_class_student.student_id'] = $student_info['id'];
                        $whereis['biz_class.class_status'] = 1;

                        $is_class_add = M('biz_class_student')
                            ->join('biz_class on biz_class.id=biz_class_student.class_id')
                            ->where($whereis)
                            ->find();

                        if (!empty($is_class_add)) {

                            $dbError = "该学生已经加入过校建班级";
                        }
                    }
                    /*$student_info['id'] = $model->add($add_data);*/

                }


                $classmap['class_id'] = $classId;
                $classmap['student_id'] = $student_info['id'];
                if (!empty($classmap['student_id'])) {
                    $classinfo = $classStudentModel->where($classmap)->find();
                }

                if ($classinfo_data['class_status'] == 1) {
                    $ass_add_data['apply_school_status'] = 1;
                    $ass_map_id['id'] = $student_info['id'];
                    $up_ass = M('auth_student')->where($ass_map_id)->save($ass_add_data);
                }

                //学生存在
                $bcsrmap['class_id'] = $classId;
                $bcsrmap['student_id'] = $student_info['id'];

                $bcsrinfo = M('biz_class_student_record')->where($bcsrmap)->find();
                if (!empty($bcsrinfo)) {
                    $bcsrid = M('biz_class_student_record')->where($bcsrmap)->delete();
                    if (!$bcsrid) {
                        $dbError = "学生移入副本表操作失败";
                    }
                }


                if (!empty($classinfo)) {
                    $dbError = "该学生已经加入了该班级";
                } else {

                    if ($classinfo_data['class_status'] == 1) { //校内班判断是否学校一致
                        $whereis['biz_class_student.student_id'] = $student_info['id'];
                        $whereis['biz_class.class_status'] = 1;

                        $is_class_add = M('biz_class_student')
                            ->join('biz_class on biz_class.id=biz_class_student.class_id')
                            ->where($whereis)
                            ->find();
                    }

                    if (!empty($is_class_add)) {
                        $dbError = "该学生已经加入过校建班级";
                    } else {

                        //就把学生和班级进行关联
                        $classStudentaddmap['class_id'] = $classId;
                        $classStudentaddmap['student_id'] = $student_info['id'];
                        $classStudentaddmap['create_at'] = time();
                        $classStudentaddmap['update_at'] = time();
                        $classStudentaddmap['status'] = 2;
                        $classadd_id = $classStudentModel->add($classStudentaddmap);
                        if (!$classadd_id) {
                            $dbError = "加入班级失败";
                        }
                    }

                }


                if (!empty($parent_info)) {
                    $parameters = array(
                        'msg' => array(
                            session('teacher.name'),
                            $class_result_info_add['grade'],
                            $class_result_info_add['name'],
                        ),
                        'url' => array('type' => 1, 'data' => array($student_info['id'], $add_data['student_name']))
                    );

                    A('Home/Message')->addPushUserMessage('ADDSTU', 4, $parent_info['id'], $parameters);
                }


                $parametersstu = array(
                    'msg' => array(
                        session('teacher.name'),
                        $class_result_info_add['grade'],
                        $class_result_info_add['name'],
                    ),
                    'url' => array('type' => 0,)
                );

                A('Home/Message')->addPushUserMessage('CLASSADDSENDSTUDENT', 3, $student_info['id'], $parametersstu);//学生


            } else {

                if (empty($add_parent_data['parent_name_is']) && !empty($parent_info)) { //家长为空   家长表里存在  给学生创建账号  关联关系

                    if ($classinfo_data['class_status'] == 1) {
                        $adddata['flag'] = 1;
                    } else {
                        $adddata['flag'] = 0;
                    }

                    $stuid = $model->add($add_data);

                    give_new_vip_operation(3, 1, $stuid, ''); //2教师,3学生,4家长
                    if (!$stuid) {
                        $dbError = "学生创建失败";
                    }
                    $relation_map['student_id'] = $stuid;
                    $relation_map['parent_id'] = $parent_info['id'];
                    $stu_parent_relation = M('auth_student_parent_contact')->where($relation_map)->find();

                    if (empty($stu_parent_relation)) {
                        $relation_map['create_at'] = time();
                        $relation_map['parent_tel'] = $parent_info['telephone'];
                        $stu_parent_relation_id = M('auth_student_parent_contact')->add($relation_map);

                        if (!$stu_parent_relation_id) {
                            $dbError = "学生和家长关联失败,请重新尝试";
                        }
                    }


                } elseif (empty($add_parent_data['parent_name_is']) && empty($parent_info)) { //家长为空 家长不存在   只注册学生 关联班级与学生关系

                    if ($classinfo_data['class_status'] == 1) {
                        $adddata['flag'] = 1;
                    } else {
                        $adddata['flag'] = 0;
                    }

                    $stuid = $model->add($add_data);
                    give_new_vip_operation(3, 1, $stuid, ''); //2教师,3学生,4家长
                    if (!$stuid) {
                        $dbError = "学生创建失败";
                    }

                } elseif (!empty($add_parent_data['parent_name_is']) && !empty($parent_info)) { //家长不为空 家长存在   校验关联关系 如果没有就创建
                    if ($classinfo_data['class_status'] == 1) {
                        $adddata['flag'] = 1;
                    } else {
                        $adddata['flag'] = 0;
                    }

                    $stuid = $model->add($add_data);
                    give_new_vip_operation(3, 1, $stuid, ''); //2教师,3学生,4家长
                    if (!$stuid) {
                        $dbError = "学生创建失败";
                    }

                    $relation_map['student_id'] = $stuid;
                    $relation_map['parent_id'] = $parent_info['id'];
                    $stu_parent_relation = M('auth_student_parent_contact')->where($relation_map)->find();

                    if (empty($stu_parent_relation)) {
                        $relation_map['create_at'] = time();
                        $relation_map['parent_tel'] = $parent_info['telephone'];
                        $stu_parent_relation_id = M('auth_student_parent_contact')->add($relation_map);
                        if (!$stu_parent_relation_id) {
                            $dbError = "学生和家长关联失败,请重新尝试";
                        }
                    }

                } elseif (!empty($add_parent_data['parent_name_is']) && empty($parent_info)) { //家长不为空 家长不存在   注册学生账号 注册家长账号 关联学生与家长关系

                    if ($classinfo_data['class_status'] == 1) {
                        $adddata['flag'] = 1;
                    } else {
                        $adddata['flag'] = 0;
                    }

                    $stuid = $model->add($add_data);
                    give_new_vip_operation(3, 1, $stuid, ''); //2教师,3学生,4家长
                    if (!$stuid) {
                        $dbError = "学生创建失败";
                    }
                    unset($add_parent_data['parent_name_is']);
                    $addparent = $parent_demol->add($add_parent_data);
                    give_new_vip_operation(4, 1, $addparent, ''); //2教师,3学生,4家长

                    if (!$addparent) {
                        $dbError = "家长创建失败,请重新尝试";
                    }

                    $relation_map['student_id'] = $stuid;
                    $relation_map['parent_id'] = $addparent;
                    $stu_parent_relation = M('auth_student_parent_contact')->where($relation_map)->find();
                    if (empty($stu_parent_relation)) {
                        $relation_map['create_at'] = time();
                        $relation_map['parent_tel'] = $add_data['parent_tel'];
                        $stu_parent_relation_id = M('auth_student_parent_contact')->add($relation_map);
                        if (!$stu_parent_relation_id) {
                            $dbError = "学生和家长关联失败,请重新尝试";
                        }
                    }

                }
                //关联学生和班级

                $classmap['class_id'] = $classId;
                $classmap['student_id'] = $stuid;
                $classinfo = $classStudentModel->where($classmap)->find();

                if (!empty($classinfo)) {
                    $dbError = "该学生已经加入了该班级";
                } else {

                    //就把学生和班级进行关联
                    $classStudentaddmap['class_id'] = $classId;
                    $classStudentaddmap['student_id'] = $stuid;
                    $classStudentaddmap['create_at'] = time();
                    $classStudentaddmap['update_at'] = time();
                    $classStudentaddmap['status'] = 2;
                    $classadd_id = $classStudentModel->add($classStudentaddmap);
                    if (!$classadd_id) {
                        $dbError = "加入班级失败";
                    }
                }

                //添加赠送vip
                $vipstatus = C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');
                if ($vipstatus == 1) {

                    $vipdata = array(
                        'user_id' => $stuid,
                        'role_id' => 3,
                        'auth_id' => 4,
                        'auth_start_time' => time(),
                        'auth_end_time' => time() + 3600 * 24 * 30 * 3,
                        'timetype' => 1,
                    );
                    $auth_type_use = D('Account_auths');
                    $auth_type_use->addUserVip($vipdata);

                }


                if (!empty($parent_info)) {
                    $parameters = array(
                        'msg' => array(
                            session('teacher.name'),
                            $add_data['student_name'],
                            $class_result_info_add['grade'],
                            $class_result_info_add['name'],
                        ),
                        'url' => array('type' => 0,)
                    );

                    A('Home/Message')->addPushUserMessage('REGISTERADDSTU', 4, $parent_info['id'], $parameters);
                }

                $parametersstu = array(
                    'msg' => array(
                        session('teacher.name'),
                        $class_result_info_add['grade'],
                        $class_result_info_add['name'],
                    ),
                    'url' => array('type' => 0,)
                );

                A('Home/Message')->addPushUserMessage('WEICLASSADDSENDSTUDENT', 3, $stuid, $parametersstu);//学生

                (new SMS())->templateSMSAddStudent($add_data['parent_tel'], $add_data['student_name'], session('teacher.name'), $class_result_info_add['grade'] . $class_result_info_add['name']);
            }

            if ('' != $dbError) {
                $model->rollback();
                $errArray[] = array_merge($data[$i], array($dbError));
            } else {
                $model->commit();
            }

        }

        if (count($errArray) != 0) {
            //错误下载下来
            //$this->echoResult(-2,$errArray);
            /*$str="学生姓名,学生性别,家长手机号,家长姓名,生日,邮箱,失败信息\n";
            $str=iconv('utf-8','gb2312', $str);

            foreach($errArray as $k=>$v){
                $name = $v[0];
                $sex = $v[1];
                $parent_phone=iconv( 'utf-8','gb2312', $v[2]);
                $parent_name=$v[3];
                $date=iconv( 'utf-8','gbk', $v[4]);
                $email=iconv( 'utf-8','gb2312', $v[5]);
                $errorinfo=iconv( 'utf-8','gb2312', $v[6]);
                $str.=$name.",".$sex.",".$parent_phone.",".$parent_name.",".$date.",".$email.",".$errorinfo."\n";
            }

            $filename=date('Ymd').rand(0,1000).'errorStu'.'.csv';
            $error_csv = array();
            $error_csv['filename'] = $filename;
            $error_csv['str'] = iconv( 'gb2312','utf-8', $str);
            $datajson = json_encode($error_csv);
            echo $datajson;die();*/

            foreach ($errArray as $ek => $ev) {
                foreach ($ev as $eek => $eev) {
                    $encode = mb_detect_encoding($eev, array('UTF-8', 'GB2312', 'GBK', 'EUC-CN'));

                    if ($encode == 'EUC-CN' || $encode == 'GB2312') {
                        $errArray[$ek][$eek] = iconv('GBK', 'utf-8', $eev);
                    } else if ($encode == 'GBK') {
                        $errArray[$ek][$eek] = iconv('GBK', 'utf-8', $eev);
                    } else if ($encode == 'UTF-8') {
                        continue;
                    } else {
                        $errArray[$ek][$eek] = iconv('GBK', 'utf-8', $eev);
                    }
                }
            }

            $maplist['error_list'] = $errArray;
            if ($datacount > 1)
                $datacount = $datacount - 1;

            if ($datacount == count($errArray)) { //全部错误
                echo $this->echoResult(2, $maplist);
            } else {//部分错误
                echo $this->echoResult(1, $maplist);
            }


        } else {
            echo $this->echoResult(0, '导入成功');
        }
    }

    public function teacherdownloadFileCsv()
    {

        $errArray = I('errorlist');

        if (!empty($errArray)) {

            foreach ($errArray as $k => $v) {
                $child = explode(',', $v);
                $errArray[$k] = $child;
            }
        } else {
            return;
        }

        $str = "学生姓名,学生性别,家长手机号,家长姓名,生日,邮箱,失败信息\n";
        $str = iconv('utf-8', 'GBK', $str);

        foreach ($errArray as $k => $v) {
            $name = iconv('utf-8', 'GBK', $v[0]);

            if ($v[1] != '男') {
                $sex = iconv('utf-8', 'GBK', '女');
            } else {
                $sex = iconv('utf-8', 'GBK', $v[1]);
            }

            $parent_phone = iconv('utf-8', 'GBK', $v[2]);
            $parent_name = iconv('utf-8', 'GBK', $v[3]);
            $date = iconv('utf-8', 'GBK', $v[4]);
            $email = iconv('utf-8', 'GBK', $v[5]);
            $errorinfo = iconv('utf-8', 'GBK', $v[6]);
            $str .= $name . "," . $sex . "," . $parent_phone . "," . $parent_name . "," . $date . "," . $email . "," . $errorinfo . "\n";
        }

        $filename = date('Ymd') . rand(0, 1000) . 'errorStu' . '.csv';
        $csv = new CSV();
        $csv->downloadFileCsv($filename, $str);
    }

    public function sctonum($num, $double = 5)
    {
        if (false !== stripos($num, "e")) {
            $a = explode("e", strtolower($num));
            return bcmul($a[0], bcpow(10, $a[1], $double), $double);
        }
    }

    public function exportStudents()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if (empty($_GET['classId'])) {
            $this->error('参数错误');
        } else {
            //$classId = $_GET['classId'];
            $classId = getParameter('classId', 'int', false);
            $Model = M('biz_class_student');
            $stuIds = $_POST['list'];
            if (empty($stuIds))
                $additionalWhere = "";
            else
                $additionalWhere = " and biz_class_student.student_id in (" . $stuIds . ")";

            $result = $Model
                ->join('auth_student on auth_student.id=biz_class_student.student_id')
                ->join('biz_class on biz_class.id=biz_class_student.class_id')
                ->field('auth_student.birth_date,auth_student.sex,auth_student.email,auth_student.parent_name,auth_student.student_name,auth_student.parent_tel,auth_student.student_id,auth_student.user_name,auth_student.id_card')
                ->where("biz_class_student.class_id=$classId" . $additionalWhere)
                ->order('biz_class_student.create_at desc')
                ->page($page, 1000)//班级学生全显示
                ->select();
            $str = "学生姓名,性别,学生编号,出生年月,邮箱,家长手机号,身份证号\n";
            foreach ($result as $v) {
                $name = $v['student_name'];
                $sex = $v['sex'];
                $student_id = $v['student_id'];
                $birth_date = date("Y-m-d", $v['birth_date']);
                $email = $v['email'];
                $parent_tel = $v['parent_tel'];
                $id_card = $v['id_card'];
                $str .= $name . "," . $sex . "," . $student_id . "," . $birth_date . "," . $email . "," . $parent_tel . "," . $id_card . "\n";
            }
            $filename = date('Ymd') . rand(0, 1000) . 'student' . '.csv';
            $csv = new CSV();
            $csv->downloadFileCsv($filename, iconv('utf-8', 'gbk', $str));
        }
    }

    //作业完成情况
    public function homeworkCompleteDetails()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '作业完成情况');
        $this->assign('navicon', 'zuoyexitong');

        //$homeworkId = $_GET['homeworkId'];
        $homeworkId = getParameter('homeworkId', 'int', false);

        $mca = getParameter('mca', 'str', false);

        if ($mca == 'action') {
            $this->assign('kw', 1);
        }

        if (empty($homeworkId)) {
            //$homeworkId = $_POST['homeworkId'];
            $homeworkId = getParameter('homeworkId', 'int', false);
            if (empty($homeworkId)) {
                redirect(U('Index/systemError'));
            }
        }
        $this->assign('homeworkId', $homeworkId);


        $Model = M('biz_homework');
        $homework = $Model->where("id=$homeworkId")->find();
        if (empty($homework)) {
            redirect(U('Index/systemError'));
        }

        $classId = $homework['class_id'];
        $Model = M('biz_class');
        $class = $Model->join('biz_class_student ON biz_class_student.class_id = biz_class.id AND biz_class_student.status=2')->where("biz_class.id=$classId")->field('count(1) student_count')->find();
        $classStudentCount = $class['student_count'];
        $this->assign('classStudentCount', $classStudentCount);

        /*此字段已不存在
         * $exercise_chapter_id = $homework['exercise_chapter_id'];
        $this->assign('exercise_chapter_id', $exercise_chapter_id);
         */

        /*$name = $_POST['name'];
        $sortColumn = $_POST['sort_column'];
        $state=$_POST['state'];
         */
        $name = $_POST['name'];
        $sortColumn = getParameter('sort_column', 'int', false);
        $state = getParameter('state', 'int', false);

        $check['biz_homework_student_details.homework_id'] = $homeworkId;
        if (!empty($name)) $check['auth_student.student_name'] = array('like', '%' . $name . '%');
        if (empty($sortColumn)) {
            $sortColumn_value = 1;
            $sortColumn = 'create_at';
        } else {
            $sortColumn_value = $sortColumn;
            if ($sortColumn == 1) {
                $sortColumn = 'points asc';
            } else {
                $sortColumn = 'points desc';
            }
        }


        $this->assign('name', $name);
        $this->assign('sort_column', $sortColumn_value);
        $this->assign('state', $state);

        $Model = M('biz_homework_student_details');
        $result = $Model
            ->join('inner join auth_student on biz_homework_student_details.student_id=auth_student.id')
            ->field('biz_homework_student_details.*,auth_student.student_name')
            ->where($check)
            ->order("biz_homework_student_details.$sortColumn ")
            ->select();         //echo $Model->getLastSql();die;

        //判断是否存在作业了
        $StudentModel = M('biz_class_student');
        //get class id
        $checkStudents['biz_class_student.status'] = 2;
        $checkStudents['biz_class_student.class_id'] = $homework['class_id'];
        if (!empty($name)) $checkStudents['auth_student.student_name'] = array('like', $name . '%');
        $students = $StudentModel
            ->join("auth_student on auth_student.id=biz_class_student.student_id")
            ->field("auth_student.id as student_id,auth_student.student_name,0 as create_at,0 as duration,0 as points,0 as status,0 as id")
            ->where($checkStudents)
            ->select();
        $i = 0;
        $outlist = array();

        foreach ($students as $student) {
            $isExisted = false;
            foreach ($result as $r) {
                if ($r['student_id'] == $student['student_id']) {//根据学生姓名查询,如果学生姓名为空，则都显示
                    $isExisted = true;
                    break;
                }
            }
            if (!$isExisted) { //如果不存在
                $outlist[$i] = $student;
                $i = $i + 1;
            }
        }
        if (empty($state)) {
            $list = array_merge($result, $outlist);
        } else {
            if ($state == 1) {
                $list = $outlist;
            } else {
                $list = $result;
            }
        }
        $this->assign('list', $list);

        $Model = M('biz_homework_student_details');
        $avgDuration = $Model
            ->where("biz_homework_student_details.homework_id=$homeworkId")
            ->avg('duration');

        $maxDuration = $Model
            ->where("biz_homework_student_details.homework_id=$homeworkId")
            ->max('duration');


        $minDuration = $Model
            ->where("biz_homework_student_details.homework_id=$homeworkId")
            ->min('duration');

        $this->assign('avgDuration', $avgDuration);
        $this->assign('maxDuration', $maxDuration);
        $this->assign('minDuration', $minDuration);

        $this->display();
    }

    //学生作业详情
    public function studentHomeworkDetails()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '查作业');
        $this->assign('navicon', 'zuoyexitong');

        $id = intval($_GET['id']);
        if (!$id) {
            redirect(U('Index/systemError'));
        }
        $this->assign('id', $id);

        //判断是否存在作业了
        $Model = M('biz_homework_student_details');
        $result = $Model->where("id=$id")->find();
        if (empty($result)) {
            redirect(U('Index/systemError'));
        }
        //这里判断,如果批改过了就不能再改了
        if ($result['status'] == 2) {
            $this->redirect(U('Teach/markingAfterHomework'), array('id' => $id));
        }

        $this->assign('homework', $result);
        $chapter_result = array();
        if (!empty($result)) {
            $where['homework_id'] = $result['homework_id'];
            $homework_exercise_model = M('biz_homework_exercise');
            $chapter_result = $homework_exercise_model->where($where)
                ->join('biz_exercise_library_chapter chapter on chapter.id=biz_homework_exercise.chapter_id')
                ->group('chapter.id')
                ->field('chapter.chapter,chapter.festival')
                ->select();
        }
        $this->assign('chapter_data', $chapter_result);
        //试题信息
        $homeworkId = $result['homework_id'];
        $Model = M('biz_homework');
        $exerciseChapter = $Model->where("id=$homeworkId")->find();
        $this->assign('exerciseChapter', $exerciseChapter);
        $this->assign('homeworkId', $homeworkId);

        //获取题目数量
        //$exercise_chapter_id = $exerciseChapter['exercise_chapter_id'];
        $Model = M('biz_homework_exercise');
        $exerciseCount = $Model->where("homework_id=$homeworkId")->count('id');
        $this->assign('exerciseCount', $exerciseCount);

        //获取学生得分细节
        $studentId = $result['student_id'];
        $Model = M('biz_homework_score_details');
        $scoreDetails = $Model->where("homework_id=$homeworkId and student_id=$studentId")->find();
        $this->assign('scoreDetails', $scoreDetails);


        $this->display();
    }

    //批改完的学生作业详情
    public function markingAfterHomework()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '查作业');
        $this->assign('navicon', 'zuoyexitong');

        $id = intval($_GET['id']);
        if (!$id) {
            redirect(U('Index/systemError'));
        }
        $this->assign('id', $id);

        //判断是否存在作业了
        $Model = M('biz_homework_student_details');
        $result = $Model->where("id=$id")->find();
        if (empty($result)) {
            redirect(U('Index/systemError'));
        }

        $this->assign('homework', $result);

        //试题信息
        $homeworkId = $result['homework_id'];
        $Model = M('biz_homework');
        $exerciseChapter = $Model->where("id=$homeworkId")->find();
        $this->assign('exerciseChapter', $exerciseChapter);
        $this->assign('homeworkId', $homeworkId);

        $chapter_result = array();
        if (!empty($result)) {
            $where['homework_id'] = $homeworkId;
            $homework_exercise_model = M('biz_homework_exercise');
            $chapter_result = $homework_exercise_model->where($where)
                ->join('biz_exercise_library_chapter chapter on chapter.id=biz_homework_exercise.chapter_id')
                ->group('chapter.id')
                ->field('chapter.chapter,chapter.festival')
                ->select();
        }
        $this->assign('chapter_data', $chapter_result);

        //获取题目数量
        //$exercise_chapter_id = $exerciseChapter['exercise_chapter_id'];
        $Model = M('biz_homework_exercise');
        $exerciseCount = $Model->where("homework_id=$homeworkId")->count('id');
        $this->assign('exerciseCount', $exerciseCount);

        //获取学生得分细节
        $studentId = $result['student_id'];
        $Model = M('biz_homework_score_details');
        $scoreDetails = $Model->where("homework_id=$homeworkId and student_id=$studentId")->find();
        $this->assign('scoreDetails', $scoreDetails);

        $this->display();
    }

    //作业打分
    function doGrade()
    {
        if (!session('?teacher')) redirect(U('Index/index'));
        if ($_POST) {
            //$check['id'] = $_POST['details_id'];
            $check['id'] = getParameter('details_id', 'int', false);
            $Model = M('biz_homework_student_details');
            $data['score_details'] = $_POST['score_details'];
            $data['score_at'] = time();
            $data['status'] = 2;
            //$data['points'] = $_POST['total_score'];
            $data['points'] = getParameter('total_score', 'int', false);
            $Model->where($check)->save($data);
            $this->redirect("Teach/homeworkCompleteDetails?homeworkId=" . $_POST['homework_id']);
        }
    }

    //打分详情进入作业系统
    function addScoreDetailsIntoDb()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if ($_POST) {
            $Model = M('biz_homework_score_details');
            //$data['student_id'] = $_POST['student_id'];
            //$data['homework_id'] = $_POST['homework_id'];
            $data['student_id'] = getParameter('student_id', 'int', false);
            $data['homework_id'] = getParameter('homework_id', 'int', false);

            $details = $_POST['details'];
            $questionArr = explode("#", $details);
            foreach ($questionArr as $value) {
                $arr = explode("|", $value);
                $questionId = $arr[0];
                $score = $arr[1];
                $full_score = $arr[2];
                $flag = $arr[3];
                $questionOrgId = $arr[4];
                $data['question_id'] = $questionId;
                $data['score'] = $score;
                $data['full_score'] = $full_score;
                $data['flag'] = $flag;
                $data['question_org_id'] = $questionOrgId;

                //$check['question_id'] = $questionId;
                $check['question_org_id'] = $questionOrgId;
                $check['student_id'] = $_POST['student_id'];
                $check['homework_id'] = $_POST['homework_id'];
                $Model->where($check)->delete();
                $Model->add($data);
            }
            //push score message to student
            $where['id'] = $data['homework_id'];
            $homeworkResult = M('biz_homework')->where($where)->field('homework_name')->find();
            $parameters = array('msg' => array(session('teacher.name'), $homeworkResult['homework_name']),
                'url' => array('type' => 0)
            );
            A('Home/Message')->addPushUserMessage('HOMEWORK_CORRECT', 3, $_POST['student_id'], $parameters);
            $this->ajaxReturn('success');
        }
    }

    //统计-正确率
    function homeworkTongji_Accuracy()
    {
        //$homeworkId = $_GET['homework_id'];
        $homeworkId = getParameter('homework_id', 'int', false);
        $Model = M('biz_homework_score_details');
        $result = $Model
            ->where("homework_id=$homeworkId")->select();

        $this->ajaxReturn($result);
    }


    //预览章节中的习题
    public function previewExerciseLibraryChapter()
    {
        $this->assign('module', '习题库管理');
        $this->assign('nav', '章节详情');
        $this->assign('subnav', '预览章节习题');
        $this->assign('navicon', 'zuoyexitong');

        //$id = $_GET['id'];
        $id = getParameter('homework_id', 'int', false);
        $this->assign('id', $id);
        $this->display();
    }

    //京版概览
    function jboverview()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $isAuth = $this->isAuth($this->c_a);

        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        $this->assign('module', '励耘圈');
        $this->assign('nav', '京版概览');
        $this->assign('subnav', '京版概览');
        $this->assign('navicon', 'jingbangailan');

        $Model = M('biz_bj_overview');
        $where['status'] = 2;
        $content = $Model->where($where)->select();
        $this->assign('data', $content[0]);

        $this->display();
    }

    //学生详情
    public function studentDetails()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$id = $_GET['id'];
        $id = getParameter('id', 'int', false);

        $this->assign('module', '学生管理');
        $this->assign('nav', '学生信息');
        $this->assign('navicon', 'banjiguanli');


        $Model = M('auth_student');
        $result = $Model
            ->field('id,sex,birth_date,student_id,id_card,parent_tel,user_name,student_name,telephone,avatar,email')
            ->where("id=$id")
            ->find();

        $this->assign('subnav', $result['student_name']);

        $this->assign('data', $result);

        $this->display();
    }


    //教师详情
    public function teacherDetails()
    {

        //$id = $_GET['id'];
        $id = getParameter('id', 'int', false);
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

    //获取作业成绩（根据班级编号）
    public function getHomeworkScoreByClassId()
    {
        //if (!session('?teacher'))  redirect(U('Index/index'));

        /*$classId = $_GET['class_id'];
        $strat_date=$_GET['startDate'];
        $end_date=$_GET['endDate'];
        */
        $classId = getParameter('class_id', 'int', false);
        $strat_date = getParameter('startDate', 'str', false);
        $end_date = getParameter('endDate', 'str', false);

        $Model = M('biz_homework_student_details');

        $c1['biz_homework.class_id'] = $classId;
        if ($strat_date != '' && $end_date != '') {
            $c1['_string'] = 'biz_homework.create_at>=' . $strat_date . ' AND biz_homework.create_at <=' . $end_date;
        }
        $result = $Model
            ->join('biz_homework on biz_homework.id=biz_homework_student_details.homework_id')
            ->join("RIGHT JOIN (select id,student_name from auth_student join (select student_id from biz_class_student where status=2 and class_id=$classId) a on a.student_id = auth_student.id) b on b.id=biz_homework_student_details.student_id")
            ->field("biz_homework.id as homework_id,biz_homework.homework_name,biz_homework_student_details.points,b.student_name,b.id as student_id")
            ->where($c1)
            ->order('biz_homework.create_at asc')->select();        //echo $Model->getLastSql();die;
        $this->ajaxReturn($result);
    }

    //获取作业成绩（根据学生编号）
    public function getHomeworkScoreByStudentId()
    {
        /*
        $student_tag=$_GET['tag'];
        $studentId = $_GET['student_id'];
        $strat_date=$_GET['startDate'];
        $end_date=$_GET['endDate'];
        $course_id=$_GET['course'];
        */
        $student_tag = getParameter('tag', 'int', false);
        $studentId = getParameter('student_id', 'str', false);
        $class_id = getParameter('class_id', 'str', false);
        $strat_date = getParameter('startDate', 'str', false);
        $end_date = getParameter('endDate', 'str', false);
        $course_id = getParameter('course', 'int', false);

        if (empty($student_tag)) {
            $c1['auth_student.id'] = $studentId;
        } else {
            if (empty($studentId)) //student id is null
                $this->ajaxReturn(array());
            $c1['_string'] = "auth_student.id in (" . $studentId . ')';
        }
        if ($strat_date != '' && $end_date != '') {
            if (empty($c1['_string'])) {
                $c1['_string'] = 'biz_homework.create_at>=' . $strat_date . ' AND biz_homework.create_at <=' . $end_date;
            } else {
                $c1['_string'] .= ' and biz_homework.create_at>=' . $strat_date . ' AND biz_homework.create_at <=' . $end_date;
            }

        }
        if (!empty($course_id)) {
            $c1['biz_homework.course_id'] = $course_id;
        }
        if (!empty($class_id)) {
            $c1['biz_homework.class_id'] = $class_id;
        }

        $Model = M('biz_homework_student_details');

        $result = $Model
            ->join('biz_homework on biz_homework.id=biz_homework_student_details.homework_id')
            ->join('auth_student on auth_student.id=biz_homework_student_details.student_id')
            ->field("biz_homework.id as homework_id,biz_homework.homework_name,biz_homework_student_details.points,auth_student.student_name,auth_student.id as student_id")
            ->where($c1)
            ->order('biz_homework.create_at asc')->select();

        $this->ajaxReturn($result);
    }

    public function information()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if ($_POST) {

            $teacherId = session('teacher.id');
            $check['id'] = $teacherId;
            $course_model = D('Dict_course');
            $grade_model = D('Dict_grade');
            //$data['telephone'] = getParameter('telephone', 'str',false);
            $data['brief_intro'] = getParameter('brief_intro', 'str', false);
            $data['sex'] = getParameter('sex', 'str', false);
            $data['professional'] = getParameter('professional', 'str', false);
            $data['school_age'] = getParameter('school_age', 'str', false);

            if (!empty($_POST['school_id'])) {
                $data['school_id'] = getParameter('school_id', 'int', false);
                if (session('teacher.school_id') != $data['school_id']) {
                    $data['apply_school_status'] = 0;
                }
            }
            if (!empty($_POST['password']))
                $data['password'] = sha1(getParameter('password', 'str', false));

            $data['name'] = getParameter('name', 'str', false);
            $data['email'] = getParameter('email', 'str', false);

            $data['update_at'] = time();

            if (empty($_POST['telephone']) || empty($_POST['name'])) {
                $this->error('请填写完整信息');
            }

            $TeacherModel = M('auth_teacher');
            $result = $TeacherModel->where($check)->save($data);
            //add secondary data
            $secondModel = M('auth_teacher_second');
            $secondModel->where("teacher_id=$teacherId")->delete();
            $secondModel->startTrans();

            $secondData['teacher_id'] = $teacherId;
            //$secondStr = substr(getParameter('coursegrade', 'str',false),0);

            $secondStr = $_POST['coursegrade_add'];

            if (empty($secondStr) || count($secondStr) > 6) {
                $this->error('请选择任教年级和学科');
            }

            foreach ($secondStr as $sk => $sv) {
                $pieces = explode('_', $sv);
                $secondData['course_id'] = $pieces[0];
                $secondData['grade_id'] = $pieces[1];
                $secondData['p_type'] = $pieces[2];
                $course_result = $course_model->getCourseInfo($secondData['course_id']);
                $grade_result = $grade_model->getGradeInfo($secondData['grade_id']);
                if (empty($course_result)) {
                    $secondModel->rollback();
                    break;
                }
                if (empty($grade_result)) {
                    $secondModel->rollback();
                    break;
                }
                $secondModel->add($secondData);

                $secondModel->commit();
            }

            $_SESSION['teacher']['name'] = $data['name'];
            if ($data['school_id']) {
                $_SESSION['teacher']['school_id'] = $data['school_id'];
            }
            $this->redirect('Teach/me');

        } else {
            $teacherId = session('teacher.id');
            $TeacherModel = M('auth_teacher');
            $TeacherModelSecond = M('auth_teacher_second');
            $result = $TeacherModel->join('dict_schoollist on auth_teacher.school_id = dict_schoollist.id')->where("auth_teacher.id=$teacherId")
                ->field('auth_teacher.id as vid,auth_teacher.*,dict_schoollist.school_name')->find();        //echo $TeacherModel->getLastsql();die;
            $this->assign('data', $result);
            //var_dump($result);exit;
            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $courseGradeIdNameSecond = $TeacherModelSecond
                ->where("auth_teacher_second.teacher_id=$teacherId")
                ->join('dict_course on auth_teacher_second.course_id=dict_course.id')
                ->join('dict_grade on auth_teacher_second.grade_id=dict_grade.id')
                ->field('auth_teacher_second.course_id,auth_teacher_second.grade_id,dict_course.course_name,dict_grade.grade')
                ->select();

            $this->assign('gradeCourseList', $courseGradeIdNameSecond);
            $courseGradeStr = "";

            for ($i = 0; $i < sizeof($courseGradeIdNameSecond); $i++) {
                $courseGradeStr = $courseGradeStr . $courseGradeIdNameSecond[$i]['course_id'] . ',' . $courseGradeIdNameSecond[$i]['grade_id'];
                if ($i != sizeof($courseGradeIdNameSecond) - 1)
                    $courseGradeStr = $courseGradeStr . ',';
            }

            $this->assign('courseGradeListStr', $courseGradeStr);
            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);
            $this->display();

        }
    }

    //判断是否有权限
    public function isAuth($c_a)
    {

        $teacher = session('auth_teacher');
        $parent = session('auth_parent');
        $student = session('auth_student');
        $admin = session('admin');
        if (!empty($teacher)) {

            $is_auth = in_array($c_a, session('auth_teacher'));

        } elseif (!empty($parent)) {
            $is_auth = in_array($c_a, session('auth_parent'));

        } elseif (!empty($student)) {
            $is_auth = in_array($c_a, session('auth_student'));

        } elseif (!empty($admin)) {
            return true;
        }

        if ($is_auth) {
            return true;
        } else {
            return false;
        }
    }

    //功能引导
    public function functionGuidancecopy()
    {
        session('teacher.is_login', 2);
        $this->display();
    }

    //小黑板引导
    public function blackboradFunctionGuidancecopy()
    {
        session('teacher.is_board', 2);
        $this->display();
    }

    //作业列表,对应班级等   没用了
    function homeworkList()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '作业系统');
        $this->assign('navicon', 'zuoyexitong');

        $Model = M('biz_homework');

        $count = $Model
            ->where("teacher_id=" . session("teacher.id"))
            ->count();

        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        $result = $Model
            ->join('biz_class on biz_class.id=biz_homework.class_id')
            ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
            ->join('dict_course on dict_course.id=biz_homework.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
            ->field('biz_homework.*,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook')
            ->where("biz_homework.teacher_id=" . session("teacher.id"))
            ->order('biz_homework.create_at desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $Model = M('biz_exercise_library_chapter');

        foreach ($result as $key => $val) {
            $info = $Model->where("textbook_id={$val['textbook_id']} and id={$val['exercise_chapter_id']}")->find();
            $result[$key]['chapter'] = $info['chapter'];
        }

        $this->assign('list', $result);
        $this->assign('page', $show);
    }

    public function redbjResourceList()
    {
        if (!session('?teacher')) redirect(U('Index/index'));
        /*$filter['course_id'] = $_REQUEST['course'];
        $filter['grade_id'] = $_REQUEST['grade'];
        $filter['school_term_id'] = $_REQUEST['textbook'];
        $filter['type'] = $_REQUEST['type'];
        */
        $filter['course_id'] = getParameter('course', 'int', false);
        $filter['grade_id'] = getParameter('grade', 'int', false);
        $filter['school_term_id'] = getParameter('textbook', 'int', false);
        $filter['type'] = getParameter('type', 'str', false);

        $filter['keyword'] = $_REQUEST['keyword'];
        $filter['sort_column'] = isset($_REQUEST['sort_column']) ? intval($_REQUEST['sort_column']) : 6;
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
        if ('' != $res)
            $check['grade_id'] = $res;
        $res = $this->getCourseWhere($filter['course_id']);
        if ('' != $res)
            $check['course_id'] = $res;


        if ($searchCate == 'bj') {
            if (!empty($filter['keyword'])) $check['biz_bj_resources.name'] = array('like', '%' . $filter['keyword'] . '%');
            $Model = M('biz_bj_resources');
            $check['biz_bj_resource_collect.user_id'] = session('teacher.id');
            $check['biz_bj_resource_collect.role'] = 0;
            $count = $Model->join('biz_bj_resource_collect on biz_bj_resource_collect.resource_id=biz_bj_resources.id')->where($check)->select();
            $count = count($count);
            //C('PAGE_SIZE_FRONT')
            $Page = new \Think\Page($count, 21);

            //$Page->parameter['keyword'] = urlencode($keyword);
            $Page->parameter['keyword'] = $keyword;

            $Page->parameter['course_id'] = $filter['course_id'];
            $Page->parameter['grade_id'] = $filter['grade_id'];
            $Page->parameter['textbook_id'] = $filter['school_term_id'];
            $Page->parameter['sort_column'] = $filter['sort_column'];
            $Page->parameter['type'] = $filter['type'];
            //$Page->parameter['resource_cate'] = urlencode($searchCate);
            $Page->parameter['resource_cate'] = $searchCate;

            $show = $Page->show();

            $result = $Model
                ->join('(select id,name from biz_textbook) a  on biz_bj_resources.textbook_id=a.id', 'left')
                ->join('biz_bj_resource_collect on biz_bj_resource_collect.resource_id=biz_bj_resources.id')
                ->field('biz_bj_resources.*,a.name as textbook,"北京出版集团" publisher_name')
                ->where($check)
                ->order("biz_bj_resources." . $sort_string)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
            for ($i = 0; $i < sizeof($result); $i++)
                $result[$i]['category'] = 'bj';
        } else if ($searchCate == 'teacher') {
            if (!empty($filter['keyword'])) $check['biz_resource.name'] = array('like', '%' . $filter['keyword'] . '%');
            $Model = M('biz_resource');
            $check['biz_resource_collect.user_id'] = session('teacher.id');
            $check['biz_resource_collect.user_type'] = 0;
            $count = $Model->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')->where($check)->select();
            $count = count($count);
            //C('PAGE_SIZE_FRONT')
            $Page = new \Think\Page($count, 21);
            //$Page->parameter['keyword'] = urlencode($keyword);
            $Page->parameter['keyword'] = $keyword;
            $Page->parameter['course_id'] = $filter['course_id'];
            $Page->parameter['grade_id'] = $filter['grade_id'];
            $Page->parameter['textbook_id'] = $filter['school_term_id'];
            $Page->parameter['sort_column'] = $filter['sort_column'];
            $Page->parameter['type'] = $filter['type'];
            //$Page->parameter['resource_cate'] = urlencode($searchCate);
            $Page->parameter['resource_cate'] = $searchCate;
            $show = $Page->show();

            $result = $Model
                ->join('(select id,name from biz_textbook) a on biz_resource.textbook_id=a.id', 'left')
                ->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')
                ->field('biz_resource.*,a.name as textbook,biz_resource.teacher_name publisher_name')
                ->where($check)
                ->order("biz_resource." . $sort_string)
                ->limit($Page->firstRow . ',' . $Page->listRows)
                ->select();
            for ($i = 0; $i < sizeof($result); $i++)
                $result[$i]['category'] = 'teacher';
        } else if ($searchCate == 'all') {
            $unionSql = " SELECT 'bj' as category,biz_bj_resources.file_path,'北京出版集团' publisher_name,biz_bj_resources.vid_image_path,biz_bj_resources.id,biz_bj_resources.type,biz_bj_resources.name,biz_bj_resources.create_at,biz_bj_resources.zan_count,biz_bj_resources.favorite_count,biz_bj_resources.follow_count,biz_textbook.name as textbook FROM biz_bj_resources left JOIN biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id
                         join (select resource_id from biz_bj_resource_collect where role = 0 and user_id =" . session('teacher.id') . ") a on a.resource_id=biz_bj_resources.id
                         WHERE biz_bj_resources.status = 2 ";
            if (!empty($check['course_id']))
                $unionSql = $unionSql . " and biz_bj_resources.course_id = " . $filter['course_id'];
            if (!empty($check['grade_id']))
                $unionSql = $unionSql . " and biz_bj_resources.grade_id = " . $filter['grade_id'];
            if (!empty($filter['type']))
                $unionSql = $unionSql . " and biz_bj_resources.type = '" . $filter['type'] . "'";
            if (!empty($filter['school_term_id']))
                $unionSql = $unionSql . " and biz_bj_resources.school_term_id = " . $filter['school_term_id'];

            if (!empty($filter['keyword']))
                $unionSql = $unionSql . " and biz_bj_resources.name like '%" . $filter['keyword'] . "%'";


            $check['biz_resource_collect.user_id'] = session('teacher.id');
            $check['biz_resource_collect.user_type'] = 0;
            if (!empty($filter['keyword'])) $check['biz_resource.name'] = array('like', '%' . $filter['keyword'] . '%');
            if (!empty($check['course_id'])) {
                $check['biz_resource.course_id'] = $check['course_id'];
                $check = $this->array_remove($check, 'course_id');
            }
            if (!empty($check['grade_id'])) {
                $check['biz_resource.grade_id'] = $check['grade_id'];
                $check = $this->array_remove($check, 'grade_id');
            }
            if (!empty($check['school_term_id'])) {
                $check['biz_resource.school_term_id'] = $check['school_term_id'];
                $check = $this->array_remove($check, 'school_term_id');
            }
            $Model = M('biz_resource');

            $count = $Model->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id', 'left')->where($check)->field("'teacher' as category,biz_resource.file_path,'北京出版社集团' publisher_name,biz_resource.vid_image_path," . 'biz_resource.id,biz_resource.type,biz_resource.name,biz_resource.create_at,biz_resource.zan_count,biz_resource.favorite_count,biz_resource.follow_count,biz_textbook.name as textbook')->union($unionSql)->select();
            //C('PAGE_SIZE_FRONT')
            $count = count($count);
            $Page = new \Think\Page($count, 21);
            //$Page->parameter['keyword'] = urlencode($keyword);
            $Page->parameter['keyword'] = $keyword;
            $Page->parameter['course_id'] = $filter['course_id'];
            $Page->parameter['grade_id'] = $filter['grade_id'];
            $Page->parameter['textbook_id'] = $filter['school_term_id'];
            $Page->parameter['sort_column'] = $filter['sort_column'];
            $Page->parameter['type'] = $filter['type'];
            //$Page->parameter['resource_cate'] = urlencode($searchCate);
            $Page->parameter['resource_cate'] = $searchCate;
            $show = $Page->show();


            $result = $Model
                ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id', 'left')
                ->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')
                ->field("'teacher' as category,biz_resource.file_path,biz_resource.teacher_name publisher_name,biz_resource.vid_image_path," . 'biz_resource.id,biz_resource.type,biz_resource.name,biz_resource.create_at,biz_resource.zan_count,biz_resource.favorite_count,biz_resource.follow_count,biz_textbook.name as textbook')
                ->where($check)
                ->union($unionSql . ' ORDER BY ' . $sort_string . " LIMIT " . $Page->firstRow . ',' . $Page->listRows)
                ->select();
            //echo "<pre>";
            //header("Content-type: text/html; charset=utf-8");
            //echo $Model->getLastsql();die;
        }


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

    //我的素材详情
    public function myMaterialDetails()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $id = intval(I('id'));
        if (!$id) {
            //参数错误
            redirect(U('Index/systemError'));
        }
        $model = M('biz_material');

        $result = $model->field("id,type,teacher_id,create_at,material_name,file_path,vid,flag")->where("id=" . $id)->order("create_at desc")->find();


        $this->assign('data', $result);
        $this->display();
    }


    /*oss上传携带参数
    * @dir_par 1京版 2备课   3教师    4老师报名活动的附件   5我的素材
    */
    public function upload_Material_file()
    {
        //处理截图
        $video_array = $this->material_video_image_upload();
        //阿里云oss上传
        $upload = new \Oss\Ossupload();// 实例化上传类
        $result = $upload->upload(3, $_FILES, 5, 0); //1 pic 2//
        $returnArray = explode(",", $result[1]);
        $uploadOK = 1;
        for ($i = 0; $i < sizeof($returnArray); $i++) {
            if ($returnArray[$i] == "") {
                $uploadOK = 0;
                break;
            }
        }
        if ($uploadOK == 0) {
            $arr['msg'] = '上传失败';
            $arr['code'] = -1;
        } else {
            $arr['msg'] = '上传成功';
            $arr['code'] = 0;
        }
        $arr['res'] = $result[1];
        $arr['message'] = $result[2];

        $arr['message_video_image'] = '';
        $arr['is_transition'] = '';
        if (is_array($video_array)) {
            $arr['message_video_image'] = $video_array['video_image'];
            $arr['is_transition'] = $video_array['is_transition'];
        }

        echo json_encode($arr);
    }

    //添加素材
    public function addMyterials()
    {
        if (!session('?teacher')) redirect(U('Index/index'));
        $model = M('biz_material');
        /*$data['material_name']=$_POST['material_name'];
        $data['file_path']=$_POST['file_path'];
        $data['vid_fullpath']=!empty($_POST['vid_fullpath'])?$_POST['vid_fullpath']:'';
        $data['vid_image_path']=!empty($_POST['vid_image_path'])?$_POST['vid_image_path']:'';
        $data['is_transition']=!empty($_POST['is_transition'])?$_POST['is_transition']:'';

        $data['type']=$_POST['type'];
        $data['vid']=!empty($_POST['vid'])?$_POST['vid']:'';
        */
        $material_name = getParameter('material_name', 'str', false);
        $data['file_path'] = getParameter('file_path', 'str', false);
        $vid_fullpath_data = getParameter('vid_fullpath', 'str', false);
        $vid_image_path_data = getParameter('vid_image_path', 'str', false);
        $is_transition_data = getParameter('is_transition', 'str', false);

        $data['material_name'] = !empty($material_name) ? getParameter('material_name', 'str', false) : '';
        $data['vid_fullpath'] = !empty($vid_fullpath_data) ? getParameter('vid_fullpath', 'str', false) : '';
        $data['vid_image_path'] = !empty($vid_image_path_data) ? getParameter('vid_image_path', 'str', false) : '';
        $data['is_transition'] = !empty($is_transition_data) ? getParameter('is_transition', 'str', false) : '';

        $vid_data = getParameter('vid', 'str', false);
        $data['type'] = getParameter('type', 'str', false);
        $data['vid'] = !empty($vid_data) ? getParameter('vid', 'str', false) : '';

        $data['create_at'] = time();
        $data['teacher_id'] = session('teacher.id');
        if ($insert_id = $model->add($data)) {
            $return_data['status'] = 1;
            $return_data['id'] = $insert_id;
            $return_data['time'] = date('Y-m-d H:i');
            if ('ppt' == $data['type']) {
                $pushMQData = array($insert_id, 0, $data['file_path'], OSS_URL, 'material');
                processMQMessage(CONVERTPPT_EX_NAME, K_ROUTE, 'push', implode(' ', $pushMQData));
            }
        } else {
            $return_data['status'] = 2;
        }
        echo json_encode($return_data);
    }


    //我的素材列表
    public function myMaterials()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$filter['type'] = $_REQUEST['type'];
        $filter['type'] = getParameter('type', 'str', false);
        $filter['keyword'] = $_REQUEST['keyword'];
        if ($filter['keyword'] != NULL) {
            $this->assign('kw', 1);
        }

        if (!empty($filter['type'])) $check['biz_material.type'] = $filter['type'];
        if (!empty($filter['keyword'])) $check['biz_material.material_name'] = array("like", "%" . $filter['keyword'] . "%");
        $check['teacher_id'] = session('teacher.id');

        $model = M('biz_material');
        $result = $model->field("id,type,teacher_id,create_at,material_name,file_path,vid_image_path")->where($check)->order("create_at desc")->select();

        $this->assign('type', $filter['type']);
        $this->assign('keyword', $filter['keyword']);
        $this->assign('data', $result);
        $this->display_nocache();
    }

    //删除我的素材
    public function deleteMyMaterial()
    {
        $Model = M('biz_material');
        if (!session('teacher.id')) {
            $this->ajaxReturn('您尚未登录!');
            return;
        }
        if (!I('id')) {
            $this->ajaxReturn('数据异常!');
            return;
        }
        $check['biz_material.teacher_id'] = session('teacher.id');
        $check['id'] = intval(I('id'));
        if ($Model->where($check)->delete()) {
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('failed');
        }
    }

    //编辑保存素材
    public function editMyMaterial()
    {
        $Model = M('biz_material');
        if (!session('teacher.id')) {
            $this->ajaxReturn('您尚未登录!');
            return;
        }
        if (!I('id')) {
            $this->ajaxReturn('数据异常!');
            return;
        }
        //$data['material_name']=remove_xss($_POST['material_name']);
        $data['material_name'] = getParameter('material_name', 'str', false);
        $check['teacher_id'] = session('teacher.id');
        $check['id'] = intval(I('id'));
        if ($Model->where($check)->save($data) === false) {
            $this->ajaxReturn('failed');
        } else {
            $this->ajaxReturn('success');
        }
    }

    //我收藏的习题
    public function myExercises()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $where['role'] = 0;
        $where['user_id'] = session("teacher.id");
        if (I('grade') > 0) {
            $where['biz_exercise_library_chapter.grade_id'] = intval(I('grade'));
        }
        if (I('course') > 0) {
            $where['biz_exercise_library_chapter.course_id'] = intval(I('course'));
        }
        if (I('textbook') > 0) {
            $where['biz_exercise_library_chapter.textbook_id'] = intval(I('textbook'));
        }
        $mca = I('mca');
        if ($mca == 'action') {
            $this->assign('kw', 1);
        }

        $chapter = intval(I('chapter'));
        $festival = intval(I('festival'));
        $keyword = I('keyword');
        if (!empty($chapter)) {
            $chapter_model = M('biz_exercise_library_chapter');
            $chapter_result = $chapter_model->where('id=' . $chapter)->field('chapter')->find();
            if (!empty($chapter_result)) {
                $where['biz_exercise_library_chapter.chapter'] = $chapter_result['chapter'];

                if (!empty($festival)) {
                    $festival_result = $chapter_model->where('id=' . $festival)->field('festival')->find();
                    if (!empty($festival_result)) {
                        $where['biz_exercise_library_chapter.festival'] = $festival_result['festival'];
                    }
                }
            }
        }

        if (!empty($keyword)) {
            $where['biz_exercise_library.questions'] = array("like", "%" . $keyword . "%");
        }

        if ($keyword != NULL) {
            $this->assign('kw', 1);
        }

        $exercise_collect_model = M('biz_exercise_collect');
        $exercise_result = $exercise_collect_model->where($where)
            ->join("biz_exercise_library on biz_exercise_library.id=biz_exercise_collect.exercise_id")
            ->join("biz_exercise_library_chapter on biz_exercise_library_chapter.id=biz_exercise_library.chapter_id")
            ->field("biz_exercise_collect.exercise_id")
            ->order("biz_exercise_collect.create_at desc")
            ->select();
        $ids = '';
        foreach ($exercise_result as $value) {
            $ids .= $value['exercise_id'] . ',';
        }
        $ids = rtrim($ids, ',');
        $this->assign('ids', $ids);
        $this->assign('list', $exercise_result);

        $grade_model = M('dict_grade');
        $course_model = M('dict_course');
        //$class_model=M('biz_class');
        //$grade_result=$class_model->where("biz_class.class_teacher_id=".session("teacher.id"))->field('dict_grade.id,dict_grade.grade')->join('dict_grade on dict_grade.id=biz_class.grade_id')->group("dict_grade.id")->select();
        $grade_result = $grade_model->field('dict_grade.id,dict_grade.grade')->select();
        $course_result = $course_model->field('dict_course.id,dict_course.course_name')->order('sort_order asc')->select();

        //年级不为空,求出班级和学科
        /*if(!empty($where['biz_exercise_library_chapter.grade_id'])){
            $course_model = M('dict_course');
            $course_result=$course_model->where("auth_teacher_second.grade_id=".$where['biz_exercise_library_chapter.grade_id'])
                ->field('dict_course.id,dict_course.course_name')
                ->join("auth_teacher_second on auth_teacher_second.teacher_id=".session('teacher.id')." and auth_teacher_second.course_id=dict_course.id")->group("dict_course.id")
                ->select();
        }*/
        //年级和学科不为空,求出教材分册
        if (!empty($where['biz_exercise_library_chapter.grade_id']) && !empty($where['biz_exercise_library_chapter.course_id'])) {
            $textbook_model = M('biz_textbook');
            $textbook_result = $textbook_model->where("grade_id=" . $where['biz_exercise_library_chapter.grade_id'] . " and course_id=" . $where['biz_exercise_library_chapter.course_id'])
                ->field('id,name')->select();
        }
        //教材分册不为空,求出章
        if (!empty($where['biz_exercise_library_chapter.textbook_id'])) {
            $model = M('biz_exercise_library_chapter');
            $chapter_result = $model->where('textbook_id=' . $where['biz_exercise_library_chapter.textbook_id'])->field('id,chapter')->group('chapter')->select();
        }
        //章和分册不为空,求出节
        if (!empty($chapter)) {
            $model = M('biz_exercise_library_chapter');
            $chapter_arr = $model->field('id,chapter')->where('id=' . $chapter)->find();
            if (!empty($chapter_arr)) {
                $chapter_string = $chapter_arr['chapter'];

                $festival_result = $model->where("chapter=" . "'$chapter_string' and textbook_id=" . $where['biz_exercise_library_chapter.textbook_id'])->field("id,festival")->group('festival')->select();
            }
        }

        $this->assign('grade_list', $grade_result);
        $this->assign('course_list', $course_result);
        $this->assign('textbook_list', $textbook_result);
        $this->assign('chapter_list', $chapter_result);
        $this->assign('festival_list', $festival_result);


        $this->assign('default_grade', $where['biz_exercise_library_chapter.grade_id']);
        $this->assign('default_course', $where['biz_exercise_library_chapter.course_id']);
        $this->assign('default_textbook', $where['biz_exercise_library_chapter.textbook_id']);
        $this->assign('default_chapter', $chapter);
        $this->assign('default_festival', $festival);
        $this->assign('default_keyword', $keyword);

        $this->display();
    }

    public function myResourceDetails($id, $from = "")
    {

        if (!empty($_GET['from']))
            $this->resourceDetails($id, $_GET['from'], 1);
        else
            $this->resourceDetails($id, 'myfavor', 1);
    }

    public function myActivityDetails($id)
    {
        $this->activityDetails($id);
    }

    public function mybjResourceDetails($cate, $id)
    {
        if ($cate == 'bj')
            $id = intval($id);
        $this->bjResourceDetails($id, 1);
    }

    //我的发布
    public function introduced()
    {
        if (!session('?teacher')) redirect(U('Index/index'));
        $teacherId = session('teacher.id');
        $result = D('Biz_resource')->getPublishedResource(array(), 1, 0, '', '', $teacherId, 1);

        $teacherInfo = D('Auth_teacher')->getTeachInfo($teacherId);

        if ($teacherInfo['avatar'] == 'default') {
            $teacherInfo['avatar'] = '';
        }

        $this->assign('list', $result);
        $this->assign('teacherInfo', $teacherInfo);
        $this->display();
    }

    public function uploadMyLessonPlanning()
    {
        $upload = new \Oss\Ossupload();// 实例化上传类
        $result = $upload->upload(3, $_FILES, 2, 0, 1); //1 pic 2//

        $returnArray = explode(",", $result[1]);
        $uploadOK = 1;
        for ($i = 0; $i < sizeof($returnArray); $i++) {
            if ($returnArray[$i] == "") {
                $uploadOK = 0;
                break;
            }
        }
        if ($uploadOK == 0) {
            $arr['msg'] = '上传失败';
            $arr['code'] = -1;
        } else {
            $arr['msg'] = '上传成功';
            $arr['code'] = 0;
        }
        $arr['res'] = $result[1];
        echo json_encode($arr);

    }

    //下载京版资源
    public function downloadlessonPlanning()
    {
        //$url=I('url');
        $url = getParameter('url', 'str', false);
        if (empty($url) || $url == '') {
            redirect(U('Index/systemError'));
        } else {
            $csv = new CSV();
            $csv->downloadMedia($url);
        }
    }

    //下载教师资源
    public function downloadResource()
    {
        //$url=I('url');
        $url = getParameter('url', 'str', false);
        if (empty($url) || $url == '') {
            redirect(U('Index/systemError'));
        } else {
            $csv = new CSV();
            $csv->downloadMedia($url);
        }
    }

    /**
     * 更改教师资料接口
     */
    public function updata()
    {
        $check['id'] = I('request.id', '', 'remove_xss');
        if (empty($check['id'])) {
            $this->showjson(-100, 'id为空', array());
        }
        $oldTeachInfo = D('Auth_teacher')->getTeachInfo($check['id']);
        $data['school_id'] = I('request.school_id', '', 'remove_xss');
        $data['role'] = I('request.role', '', 'remove_xss');
        if (!empty($oldTeachInfo['school_id'])) {
            $data['telephone'] = I('request.telephone', '', 'remove_xss');
            $data['sex'] = I('request.sex', '', 'remove_xss');
            $data['email'] = I('request.email', '', 'remove_xss');
            $data['brief_intro'] = I('request.brief_intro', '', 'remove_xss');//个人介绍
            $data['name'] = I('request.name', '', 'remove_xss');
            //name brief intro  email filter
            $data['update_at'] = time();
            $data['sex'] = I('request.sex', '', 'remove_xss');
        }
        foreach ($data as $key => $val) {
            switch ($key) {
                case 'telephone':
                    if (empty($data[$key])) {
                        $this->showjson(-101, "{$key}为空", array());
                    }
                    break;
                case 'name':
                    if (empty($data[$key])) {
                        $this->showjson(-102, "{$key}为空", array());
                    }
                    break;
                case 'school_id':
                    if (empty($data[$key])) {
                        $this->showjson(-102, "{$key}为空", array());
                    }
                    $data[$key] = intval($data[$key]);
                    break;

                default:
                    break;
            }
        }

        $course_grade['course_grade'] = (isset($_REQUEST['course_grade']) && (!empty($_REQUEST['course_grade']))) ? $_REQUEST['course_grade'] : "";

        if (isset($course_grade['course_grade']) && (!empty($course_grade['course_grade']))) {
            $course_grade['course_grade'] = json_decode($course_grade['course_grade']);
            $first = (isset($course_grade['course_grade']) && (!empty($course_grade['course_grade']))) ? $course_grade['course_grade'][0] : array();
            if (!empty($first)) {
                $firstInfo = explode(',', $first);
                $data['course_id'] = $firstInfo[0]; //学科
                $data['grade_id'] = $firstInfo[1]; //年级
            }

        }
        D('Auth_teacher')->startTrans();
        $TeacherModel = M('auth_teacher');

        //若完善资料,则不屏蔽修改学校的功能

//        if(!empty($oldTeachInfo['school_id']))
//            unset($data['school_id']);

        if ($oldTeachInfo['school_id'] != $data['school_id']) //修改学校
        {
            //如果有校建班则返回错误
            $classList = D('Biz_class')->getTeacherSchoolClassList($check['id']);
            if (!empty($classList)) {
                M()->rollback();
                $this->showjson(-105, "您有校建班未退出,无法更改学校", array());
            }
        }

        $result = $TeacherModel->where($check)->save($data);
        if (false === $result) {
            D('Auth_teacher')->rollback();
            $this->showjson(-105, "资料更新失败", array());
        }
        /*
        $currentTeacherInfo = D('Auth_teacher')->getTeachInfo($check['id']);
        if($currentTeacherInfo['school_id']!=$data['school_id']){
            $data['apply_school_status']=WAIT_SCHOOL_PASS_STATUS;
        }

        D('Auth_teacher')->startTrans();
        if(!D('Auth_teacher')->updateInfoById($data,$check['id'])){
            D('Auth_teacher')->rollback();
            $this->showjson(-103,"数据入库失败",array());
        }
        if($currentTeacherInfo['school_id']!=$data['school_id']){
            //删除校建班的课表
            if(!D('Biz_class')->deleteClassTimetable($check['id'])){
                D('Auth_teacher')->rollback();
                $this->showjson(-103,"数据入库失败",array());
            }
            //删除该教师在校建班的课表的信息
            if(!D('Biz_class')->deleteClassTeacher($check['id'])){
                D('Auth_teacher')->rollback();
                $this->showjson(-103,"数据入库失败",array());
            }
        }*/

        $currentTeacherInfo = D('Auth_teacher_second')->getCourseGradeById($check['id']);
        $currentCourseStr = implode(',', array_column($currentTeacherInfo, 'course_id'));
        $currentClassList = D('Biz_class')->getClassListByCourseAndTeacher($currentCourseStr, $check['id']);
        foreach ($course_grade['course_grade'] as $val) {
            $pieces = explode(',', $val);
            $newCourseArray[] = $pieces[0];
        }
        $newCourseStr = implode(',', $newCourseArray);
        $futureClassList = D('Biz_class')->getClassListByCourseAndTeacher($newCourseStr, $check['id']);
        if (implode(',', array_column($currentClassList, 'id')) != implode(',', array_column($futureClassList, 'id'))) {
            $this->showjson(-104, "您任教的校建班级中含有被修改的学科,请先离开班级再修改学科", array());
        }
        //更改教师学科附属表
        $teacherId = $check['id'];
        $secondModel = M('auth_teacher_second');
        $secondModel->where("teacher_id=$teacherId")->delete();

        $secondData['teacher_id'] = $teacherId;
        if (isset($course_grade['course_grade']) && (!empty($course_grade['course_grade']))) {
            if (count($course_grade['course_grade']) > 0) {
                //unset($course_grade['course_grade'][0]);
                foreach ($course_grade['course_grade'] as $val) {
                    $pieces = explode(',', $val);
                    $secondData['course_id'] = $pieces[0];
                    $secondData['grade_id'] = $pieces[1];
                    $secondModel->add($secondData);
                }
            }
        }
        D('Auth_teacher')->commit();
        $this->showjson(0, 'update ok', array());
    }


    public function editStudent()
    {
        if (!session('?teacher')) {
            redirect(U('Index/index'));
        }
        $this->assign('module', '班级行');
        $this->assign('nav', '班级管理');
        $this->assign('subnav', '班级学生管理');
        $this->assign('navicon', 'banjiguanli');
        if ($_POST) {
            if (!session('?teacher')) {
                redirect(U('Index/index'), 0, '登录超时，页面跳转中...');
            }
            /*
            $data['student_name'] = remove_xss($_POST['student_name']);
            $data['sex'] = $_POST['sex'];
            $data['birth_date'] = strtotime($_POST['birthday']);
            $data['email'] = $_POST['email'];
            $data['student_id'] = $_POST['student_id'];
            $data['id_card'] = $_POST['id_card'];
            $data['parent_tel'] = $_POST['telephone'];
            */
            $data['student_name'] = getParameter('student_name', 'str', false);
            $data['sex'] = getParameter('sex', 'str', false);
            $data['birth_date'] = strtotime(getParameter('birthday', 'str', false));
            $data['email'] = getParameter('email', 'str', false);
            $data['student_id'] = getParameter('student_id', 'int', false);
            $data['id_card'] = getParameter('id_card', 'str', false);
            $data['parent_tel'] = getParameter('telephone', 'str', false);

            $str = "0123456789abcdefghijklmnopqrstuvwxyz";//输出字符集
            $len = strlen($str) - 1;
            $s = '';
            for ($i = 0; $i < 10; $i++) {
                $s .= $str[rand(0, $len)];
            }
            $data['access_token'] = $s;

            //TODO:join parent telephone
            $parent_demol = M('auth_parent');
            $parent_tel = $data['parent_tel'];
            $parent_result = $parent_demol->where("telephone=" . "'$parent_tel'")->field('id,parent_name,telephone')->find();
            if (!empty($parent_result)) {
                $data['parent_id'] = $parent_result['id'];
                $data['parent_name'] = $parent_result['parent_name'];
            }

            $data['update_at'] = time();

            //$studentId = $_GET['id'];
            $studentId = getParameter('id', 'int', false);
            $Model = M('auth_student');
            $Model->where("id=$studentId")->save($data);
            $arr['msg'] = '编辑成功';
            $arr['code'] = 1;
            echo json_encode($arr);

            $parameter_arr = array(
                'msg' => array(session('teacher.name')),
                'url' => array(
                    'type' => 0,
                    'data' => array()
                )
            );

            A('Home/Message')->addPushUserMessage('INFO_MODIFIED_BYOTHER_STUDENT', 3, $studentId, $parameter_arr);


            die;
        } else {
            if (!empty($_GET['id'])) //edit child
            {
                //$studentId = $_GET['id'];
                $studentId = getParameter('id', 'int', false);
                $StudentModel = M('auth_student');
                $result = $StudentModel->where("id=$studentId")->find();
                $result['birth_date'] = date("Y-m-d", $result['birth_date']);
                $this->assign('data', $result);
                //$classId = $_GET['classId'];
                $classId = getParameter('classId', 'int', false);
                $this->assign('classId', $classId);
            }
            $this->display();
        }
    }

    public function activityMore()
    {
        $type = getParameter('type', 'str', false);

        $arr = array('train', 'experience', 'research', 'seminar', 'work');
        $this->assign('courses', D('Dict_course')->getCourseList());
        $this->assign('categorys', D('Social_activity')->getActivityClassList(5)); //作品评比

        $this->activities(array_search($type, $arr) + 1);
    }

    function getSortString($sort)
    {
        $filter['sort_column'] = $sort;
        switch ($sort) {
            case 0:
                $sort_string = "zan_count desc";
                break;
            case 1:
                $sort_string = "zan_count asc";
                break;
            case 2:
                $sort_string = "favorite_count desc";
                break;
            case 3:
                $sort_string = "favorite_count asc";
                break;
            case 4:
                $sort_string = "follow_count desc";
                break;
            case 5:
                $sort_string = "follow_count asc";
                break;
            case 6:
                $sort_string = "create_at desc";
                break;
            case 7:
                $sort_string = "create_at asc";
                break;
            default:
                $sort_string = "create_at desc";
                $filter['sort_column'] = 6;
                break;
        }
        return array($sort_string, $filter['sort_column']);
    }

    function getCourseWhere($courseId)
    {
        if ($courseId == -1) {
            return $courseId;
        } else {
            if (!empty($courseId))
                return array('in', '-1,' . $courseId);
            else
                return '';
        }
    }

    function getGradeWhere($gradeId)
    {
        if ($gradeId == 14) {
            return 14;
        } elseif ($gradeId == 15) {
            return 15;
        } elseif ($gradeId == 16) {
            return 16;
        } else {
            if ($gradeId > 0 && $gradeId < 7) {
                return array('in', '(14,' . $gradeId . ')');
            } elseif ($gradeId > 6 && $gradeId < 10) {
                return array('in', '(15,' . $gradeId . ')');
            } elseif ($gradeId > 9 && $gradeId < 13) {
                return array('in', '(16,' . $gradeId . ')');
            }
        }
        return $gradeId < 0 ? $gradeId : '';
    }

    public function myMessageDetails()
    {
        $messageId = getParameter('id', 'int', false);
        A('Home/Message')->messageDetails($messageId);

    }

    public function myMessage()
    {
        $this->display();
    }

    public function exerciseList()
    {
        $exerciseIds = getParameter('exerciseId', 'str');
        $this->assign('ids', $exerciseIds);
        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '作业系统');
        $this->assign('navicon', 'zuoyexitong');
        $this->display();
    }

    public function getExercisesByIds()
    {
        $exerciseIds = getParameter('exercises_id', 'str');

        $id_array = array_unique(explode(',', $exerciseIds));
        $ids = implode(',', $id_array);
        $ids = '(' . $ids . ')';
        $Model = M('biz_exercise_library');

        $exercise_result = $Model->join('biz_exercise_template tt on biz_exercise_library.type=tt.id')
            ->field('biz_exercise_library.id questions_primary_id,tt.*,biz_exercise_library.*,11')
            ->where("biz_exercise_library.id in " . $ids)
            ->order('biz_exercise_library.id asc')
            ->select();

        $new_array = array();
        $i = 0;
        foreach ($result as $v) {
            $new_array[$i] = $v;
            $i++;
        }
        foreach ($exercise_result as $ev) {
            $new_array[$i] = $ev;
            $i++;
        }

        $result = $this->array_sort($new_array, 'questions_primary_id');
        echo json_encode($result);

    }


    //////多媒体作业作业列表
    public function mulHomework()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $id = $_GET['auth_id'];
        // $isAuth = $this->isAuth($this->c_a);
        // if (!$isAuth) { //如果访问的模块没有权限
        //     redirect(U('Teach/index1?auth_error=1'));
        // }

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '作业系统');
        $this->assign('navicon', 'zuoyexitong');

        $mca = I('mca');
        if ($mca == 'action') {
            $this->assign('kw', 1);
        }

        $Model = M('biz_homework');
        $grade_model = M('dict_grade');
        $class_model = M('biz_class');
        $course_model = M('dict_course');
        $biz_textbook_mode = M('biz_textbook');

        if (I('grade') > 0) {
            $where['dict_grade.id'] = intval(I('grade'));
        }
        if (I('class') > 0) {
            $class_id = intval(I('class'));
            //这里求出班级
            $class_model = M('biz_class');
            $class_result = $class_model->where("id=" . $class_id)->field('name')->find();
            if (empty($class_result)) {
                redirect(U('Index/systemError'));
            } else {
                $class_name = $class_result['name'];
                //$where['biz_class.name']=$class_name;
                $where['biz_class.id'] = $class_id;
            }
        }
        if (I('course') > 0) {
            $where['dict_course.id'] = intval(I('course'));
        }
        if (I('textbook') > 0) {
            $where['biz_textbook.id'] = intval(I('textbook'));
        }
        if (I('type') > 0) {
            if (I('type') == 1) {
                $where['biz_homework.homework_type'] = '课堂作业';
            } elseif (I('type') == 2) {
                $where['biz_homework.homework_type'] = '课后作业';
            }
        }
        if (I('status') > 0) {
            $status = intval(I('status'));
            if ($status == 2) {
                $status = 0;
            }
            $where['biz_homework.homework_status'] = $status;
        }
        if (I('keyword')) {
            $where['biz_homework.homework_name'] = array('like', '%' . I('keyword') . '%');
        }
        $where['biz_homework.teacher_id'] = session("teacher.id");
        //$alone_where['biz_homework.teacher_id']=session("teacher.id");
        $alone_where['biz_class.class_teacher_id'] = session("teacher.id");

        //$where['biz_class.is_delete']=0;
        /* if (!empty($check['course_id']))
            $unionSql = $unionSql." and biz_bj_resources.course_id = ".$filter['course_id'];*/

        //$unionSql = "SELECT biz_homework.id FROM `biz_homework` INNER JOIN biz_class_teacher_record on biz_class_teacher_record.class_id=biz_homework.class_id INNER JOIN biz_class on biz_class.id=biz_class_teacher_record.class_id INNER JOIN dict_grade on dict_grade.id=biz_homework.grade_id INNER JOIN dict_course on dict_course.id=biz_homework.course_id INNER JOIN biz_textbook on biz_textbook.id=biz_homework.textbook_id WHERE biz_homework.teacher_id =".session('teacher.id')." GROUP BY biz_homework.id";

        $sort_string = 'biz_homework.create_at desc';


        $count = $Model
            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_homework.class_id')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
            ->join('dict_course on dict_course.id=biz_homework.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
            ->field('biz_homework.id')
            ->group('biz_homework.id')
            //    ->union($unionSql)
            ->where($where)
            ->order($sort_string)
            ->select();
        $count = count($count);


        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        //$unionSqlSelect = "SELECT biz_class.flag,biz_class.school_id,biz_class.class_status,biz_homework.*,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,dict_grade.grade FROM `biz_homework` INNER JOIN biz_class_teacher_record on biz_class_teacher_record.class_id=biz_homework.class_id INNER JOIN biz_class on biz_class.id=biz_class_teacher_record.class_id INNER JOIN dict_grade on dict_grade.id=biz_homework.grade_id INNER JOIN dict_course on dict_course.id=biz_homework.course_id INNER JOIN biz_textbook on biz_textbook.id=biz_homework.textbook_id WHERE biz_homework.teacher_id = ".session('teacher.id')." GROUP BY biz_homework.id";


        $result = $Model
            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_homework.class_id')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
            ->join('dict_course on dict_course.id=biz_homework.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
            ->field('biz_class.flag,biz_class.school_id,biz_class.class_status,biz_homework.*,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,dict_grade.grade')
            ->group('biz_homework.id')
            //->union($unionSqlSelect." LIMIT ".$Page->firstRow . ',' . $Page->listRows)
            //->union($unionSqlSelect.' ORDER BY '.$sort_string." LIMIT ".$Page->firstRow . ',' . $Page->listRows)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->where($where)
            ->order($sort_string)
            ->select();

        $Model = M('biz_exercise_library_chapter');

        foreach ($result as $key => $val) {
            //再去求那个章节的
            $info = $Model->where("u.id=" . $val['id'])
                ->join("biz_homework_exercise t on t.chapter_id=biz_exercise_library_chapter.id")
                ->join("biz_homework u on u.id=t.homework_id")
                ->group("t.chapter_id")->field("distinct chapter,festival")->select();     //echo $Model->getLastsql();die;
            $result[$key]['chapter'] = $info;

            if ($val['class_status'] == 1) {
                $d_m['id'] = $val['school_id'];
                $sc_name = M('dict_schoollist')->where($d_m)->find();
                if ($sc_name['flag'] == 0) {
                    $result[$key]['flag'] = 0;
                }
            }

            $is_delete_teacher_map['class_id'] = $val['class_id'];
            $is_delete_teacher_map['teacher_id'] = session('teacher.id');
            $is_delete_teacher = M('biz_class_teacher_record')->where($is_delete_teacher_map)->find();
            if (!empty($is_delete_teacher)) {
                $result[$key]['flag'] = 0; //被学校移除的老师
            }
        }


        $teacher_id = session('teacher.id');
        $grade_result = D('Biz_class')->getGradeListByTeacher($teacher_id);
        //$grade_result=$class_model->where($alone_where)->field('dict_grade.id,dict_grade.grade')->join('dict_grade on dict_grade.id=biz_class.grade_id')->group("dict_grade.id")->select();

        //$class_result=$class_model->where($alone_where)->field('biz_class.id,biz_class.name')->join('biz_homework on biz_homework.class_id=biz_class.id')->group("biz_class.name")->select();

        //$course_result=$course_model->where($alone_where)->field('dict_course.id,dict_course.course_name')->join('biz_homework on biz_homework.course_id=dict_course.id')->group("dict_course.id")->select();
        //$textbook_result=$biz_textbook_mode->where($alone_where)->field('biz_textbook.id,biz_textbook.name')->join('biz_homework on biz_homework.textbook_id=biz_textbook.id')->group("biz_textbook.id")->select();
        //年级不为空,求出班级和学科

        if (!empty($where['dict_grade.id'])) {
            if (!empty($class_id)) {
                $class_model = M('biz_class');
                $class_result = $class_model->where("id=" . $class_id)->select();
            }

            $course_model = M('dict_course');
            $course_result = $course_model->where("auth_teacher_second.grade_id=" . $where['dict_grade.id'])
                ->field('dict_course.id,dict_course.course_name')
                ->join("auth_teacher_second on auth_teacher_second.teacher_id=" . session('teacher.id') . " and auth_teacher_second.course_id=dict_course.id")->group("dict_course.id")
                ->select();
        }


        //年级和学科不为空,求出教材分册
        if (!empty($where['dict_grade.id']) && !empty($where['dict_course.id'])) {
            $textbook_model = M('biz_textbook');
            $textbook_result = $textbook_model->where("grade_id=" . $where['dict_grade.id'] . " and course_id=" . $where['dict_course.id'])
                ->field('id,name')->select();
        }


        $this->assign('list', $result);
        $this->assign('page', $show);
        $this->assign('grade_list', $grade_result);
        $this->assign('class_list', $class_result);
        $this->assign('course_list', $course_result);
        $this->assign('textbook_list', $textbook_result);
        //$this->assign('firstPage', $Page->firstRow);


        $this->assign('default_grade', $where['dict_grade.id']);
        $this->assign('default_class', $class_id);
        $this->assign('default_course', $where['dict_course.id']);
        $this->assign('default_textbook', $where['biz_textbook.id']);
        $this->assign('default_type', intval(I('type')));
        $this->assign('default_status', intval(I('status')));
        $this->assign('keyword', I('keyword'));

        $this->display();
    }


    //多媒体作业布置作业
    public function mulAssignHomework()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if ($_POST) {

            //$data['homework_name'] = remove_xss($_POST['homework_name']);
            //$data['claim'] = remove_xss($_POST['claim']);
            $data['homework_name'] = getParameter('homework_name', 'str', false);
            $data['claim'] = getParameter('claim', 'str', false);

            $data['class_id'] = intval($_POST['class_id']);
            $data['course_id'] = intval($_POST['course_id']);
            $data['grade_id'] = intval($_POST['grade_id']);
            $data['textbook_id'] = intval($_POST['textbook_id']);
            $data['homework_type'] = intval($_POST['type_id']);

            $data['teacher_id'] = session('teacher.id');
            $data['teacher_name'] = session('teacher.name');
            $data['create_at'] = time();
            $data['update_at'] = time();
            if ($data['homework_type'] == 1) {
                $data['homework_type'] = '课堂作业';
            } else {
                $data['homework_type'] = '课后作业';
            }

            //这里先判断这个班级和学科等是否存在
            $grade_id = $data['grade_id'];
            $class_id = $data['class_id'];
            $course_id = $data['course_id'];
            $class_model = M('biz_class');
            $class_result = $class_model->where('biz_class.id=' . $class_id . " and biz_class.grade_id=" . $grade_id . " and auth_teacher_second.course_id=" . $course_id)
                ->join('auth_teacher_second on auth_teacher_second.teacher_id=biz_class.class_teacher_id and '
                    . 'auth_teacher_second.grade_id=biz_class.grade_id')->field('biz_class.id')->find();

            /*if(empty($class_result)){
                redirect(U('Index/systemError'));
            }*/

            $exerciseIds = $_POST['exercise_ids'];
            $exerciseIdArr = explode(';', $exerciseIds);
            $data['exercises_number'] = count($exerciseIdArr);

            //开插入作业表
            $model = M('biz_homework');
            $model->startTrans();
            if ((!$homework_id = $model->add($data))) {
                redirect(U('Index/systemError'));
            }

            $exercise_model = M('biz_exercise_library');
            $homework_exercise = M('biz_homework_exercise');
            $tag = 0;
            //这里的逻辑是没有找到该问题就跳过该问题
            //$exerciseIdArr = explode(';', $exerciseIds);

            foreach ($exerciseIdArr as $v) {
                $arr = explode('.', $v);
                $temp_result = $exercise_model->where("id=" . intval($arr[1]))->field('id,chapter_id')->find();
                if (empty($temp_result)) {
                    continue;
                } else {
                    $exercise_data['homework_id'] = $homework_id;
                    $exercise_data['exercise_id'] = intval($arr[1]);
                    $exercise_data['chapter_id'] = intval($arr[0]);
                    if (!$homework_exercise->add($exercise_data)) {
                        $model->rollback();
                        redirect(U('Index/systemError'));
                    } else {
                        $tag = 1;
                    }
                }
            }
            if ($tag == 1) {
                $model->commit();
            } else {
                $model->rollback();
                redirect(U('Index/systemError'));
            }
            $this->redirect("Teach/homework");

        } else {
            $this->assign('module', '班级行');
            $this->assign('nav', '作业系统');
            $this->assign('subnav', '布置作业');
            $this->assign('navicon', 'zuoyexitong');

            $filter['grade_more'] = $_REQUEST['grade'];
            $filter['exercise_id'] = $_REQUEST['ids'];

            if (!empty($filter['grade_more'])) {
                $grade_course_textbook = explode('_', $filter['grade_more']);
                if (count($grade_course_textbook) >= 3) {
                    $model = M('dict_grade');
                    $check['dict_grade.id'] = $grade_course_textbook[0];
                    $check['dict_course.id'] = $grade_course_textbook[1];
                    $check['biz_textbook.id'] = $grade_course_textbook[2];
                    $result = $model->where($check)->join("biz_exercise_library_chapter on biz_exercise_library_chapter.grade_id=dict_grade.id")
                        ->join("dict_course on dict_course.id=biz_exercise_library_chapter.course_id")
                        ->join("biz_textbook on biz_textbook.id=biz_exercise_library_chapter.textbook_id")
                        ->join("auth_teacher_second on auth_teacher_second.grade_id=dict_grade.id and dict_course.id=auth_teacher_second.course_id and teacher_id=" . session('teacher.id'))
                        ->field("dict_grade.id grade_id,dict_grade.grade,dict_course.id course_id,dict_course.course_name,biz_textbook.id textbook_id,"
                            . "biz_textbook.name")->find();
                    if (empty($result)) {
                        redirect(U('Index/systemError'));
                        //echo '错误页面,该年级学科含有教师不支持的学科或年级';
                    } else {
                        //拿到某个年级下的所有班级
                        $class_model = M('biz_class');
                        $class_result = $class_model->where("grade_id=" . $check['dict_grade.id'] . " and class_teacher_id=" . session("teacher.id"))->field('id,name')->select();

                        //拿到所有习题
                        $id_array = explode(',', $filter['exercise_id']);
                        $id_array = array_unique($id_array);
                        $exercise_model = M('biz_exercise_library');
                        $condition_string = "biz_exercise_library.id in (" . $filter['exercise_id'] . ")";
                        $exercise_result = $exercise_model->where($condition_string . " and auth_teacher_second.teacher_id=" . session("teacher.id"))
                            ->join("biz_exercise_library_chapter on biz_exercise_library_chapter.id=biz_exercise_library.chapter_id")
                            ->join("auth_teacher_second on auth_teacher_second.course_id=biz_exercise_library_chapter.course_id and "
                                . "auth_teacher_second.grade_id=biz_exercise_library_chapter.grade_id")
                            ->field('biz_exercise_library_chapter.id chapter_id,biz_exercise_library.id exercise_id')
                            ->select();
                        if (empty($exercise_result) || count($id_array) != count($exercise_result)) {
                            redirect(U('Index/systemError'));
                            //echo '错误页面,该习题含有教师不支持的习题,或习题不存在';
                        }
                        $this->assign('default_grade', $check['dict_grade.id']);
                        $this->assign('default_course', $check['dict_course.id']);

                        $textbook_array['id'] = $check['biz_textbook.id'];
                        $textbook_array['name'] = $result['name'];
                        $this->assign('default_textbook', $textbook_array);

                        $this->assign('class_list', $class_result);

                        $exercise_list_string = '';
                        foreach ($exercise_result as $value) {
                            $exercise_list_string .= $value['chapter_id'] . '.' . $value['exercise_id'] . ';';
                        }
                        $exercise_list_string = rtrim($exercise_list_string, ';');
                        $this->assign('exercise_list', $exercise_list_string);
                    }
                }
            }

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();

            $this->assign('courses', $courses);

            /* $grade_model = M('dict_grade');
            $grade_result=$grade_model->where("biz_class.class_teacher_id=".session('teacher.id'))
                ->field('dict_grade.id,dict_grade.grade')
                ->join("biz_class on biz_class.grade_id=dict_grade.id")->group("dict_grade.id")
                ->select();*/
            $grade_result = D('Biz_class')->getGradeListByTeacher(session('teacher.id'));

            $this->assign('grade_list', $grade_result);     //echo $grade_model->getLastSql();die;

            $Model = M('biz_textbook');
            $textbooks = $Model->order('sort_order asc')->select();
            $this->assign('textbooks', $textbooks);

            $Model = M('biz_class');
            $classes = $Model
                ->where("class_teacher_id=" . session("teacher.id"))
                ->order('name asc')->select();
            $this->assign('classes', $classes);

            $this->display();
        }

    }

    //多媒体作业完成情况
    public function mulHomeworkCompleteDetails()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '作业完成情况');
        $this->assign('navicon', 'zuoyexitong');

        //$homeworkId = $_GET['homeworkId'];
        $homeworkId = getParameter('homeworkId', 'int', false);

        $mca = getParameter('mca', 'str', false);

        if ($mca == 'action') {
            $this->assign('kw', 1);
        }

        if (empty($homeworkId)) {
            //$homeworkId = $_POST['homeworkId'];
            $homeworkId = getParameter('homeworkId', 'int', false);
            if (empty($homeworkId)) {
                redirect(U('Index/systemError'));
            }
        }
        $this->assign('homeworkId', $homeworkId);


        $Model = M('biz_homework');
        $homework = $Model->where("id=$homeworkId")->find();
        if (empty($homework)) {
            redirect(U('Index/systemError'));
        }

        $classId = $homework['class_id'];
        $Model = M('biz_class');
        $class = $Model->join('biz_class_stuent ON biz_class_student.class_id = biz_class.id AND biz_class_student.status=2')->where("biz_class.id=$classId")->field('count(1) student_count')->find();
        $classStudentCount = $class['student_count'];
        $this->assign('classStudentCount', $classStudentCount);

        /*此字段已不存在
         * $exercise_chapter_id = $homework['exercise_chapter_id'];
        $this->assign('exercise_chapter_id', $exercise_chapter_id);
         */

        /*$name = $_POST['name'];
        $sortColumn = $_POST['sort_column'];
        $state=$_POST['state'];
         */
        $name = $_POST['name'];
        $sortColumn = getParameter('sort_column', 'int', false);
        $state = getParameter('state', 'int', false);

        $check['biz_homework_student_details.homework_id'] = $homeworkId;
        if (!empty($name)) $check['auth_student.student_name'] = array('like', '%' . $name . '%');
        if (empty($sortColumn)) {
            $sortColumn_value = 1;
            $sortColumn = 'create_at';
        } else {
            $sortColumn_value = $sortColumn;
            if ($sortColumn == 1) {
                $sortColumn = 'points asc';
            } else {
                $sortColumn = 'points desc';
            }
        }


        $this->assign('name', $name);
        $this->assign('sort_column', $sortColumn_value);
        $this->assign('state', $state);

        $Model = M('biz_homework_student_details');
        $result = $Model
            ->join('inner join auth_student on biz_homework_student_details.student_id=auth_student.id')
            ->field('biz_homework_student_details.*,auth_student.student_name')
            ->where($check)
            ->order("biz_homework_student_details.$sortColumn ")
            ->select();         //echo $Model->getLastSql();die;

        //判断是否存在作业了
        $StudentModel = M('biz_class_student');
        //get class id
        $checkStudents['biz_class_student.status'] = 2;
        $checkStudents['biz_class_student.class_id'] = $homework['class_id'];
        if (!empty($name)) $checkStudents['auth_student.student_name'] = array('like', $name . '%');
        $students = $StudentModel
            ->join("auth_student on auth_student.id=biz_class_student.student_id")
            ->field("auth_student.id as student_id,auth_student.student_name,0 as create_at,0 as duration,0 as points,0 as status,0 as id")
            ->where($checkStudents)
            ->select();
        $i = 0;
        $outlist = array();

        foreach ($students as $student) {
            $isExisted = false;
            foreach ($result as $r) {
                if ($r['student_id'] == $student['student_id']) {//根据学生姓名查询,如果学生姓名为空，则都显示
                    $isExisted = true;
                    break;
                }
            }
            if (!$isExisted) { //如果不存在
                $outlist[$i] = $student;
                $i = $i + 1;
            }
        }
        if (empty($state)) {
            $list = array_merge($result, $outlist);
        } else {
            if ($state == 1) {
                $list = $outlist;
            } else {
                $list = $result;
            }
        }
        $this->assign('list', $list);

        $Model = M('biz_homework_student_details');
        $avgDuration = $Model
            ->where("biz_homework_student_details.homework_id=$homeworkId")
            ->avg('duration');

        $maxDuration = $Model
            ->where("biz_homework_student_details.homework_id=$homeworkId")
            ->max('duration');


        $minDuration = $Model
            ->where("biz_homework_student_details.homework_id=$homeworkId")
            ->min('duration');

        $this->assign('avgDuration', $avgDuration);
        $this->assign('maxDuration', $maxDuration);
        $this->assign('minDuration', $minDuration);

        $this->display();
    }

    //多媒体作业复制习题页面
    public function mulHomeworkCopy()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '布置作业');
        $this->assign('navicon', 'zuoyexitong');

        $homework_id = intval(I('id'));
        if (!$homework_id) {
            redirect(U('Index/systemError'));
            die;
        }
        $Model = M('biz_homework');
        $result = $Model
            ->join('biz_class on biz_class.id=biz_homework.class_id')
            ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
            ->join('dict_course on dict_course.id=biz_homework.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
            ->field('biz_homework.*,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,dict_grade.grade')
            ->where("biz_homework.id=" . $homework_id)
            ->find();
        if (empty($result)) {
            redirect(U('Index/systemError'));
        }
        $chose_list = I('b_id');
        $chose_other_list = I('c_id');
        if ($chose_list) {
            $chose_list = explode(',', $chose_list);
            $chose_list = array_unique($chose_list);

        }


        if ($chose_other_list) {
            $chose_other__arr = explode(',', $chose_other_list);
            $chose_other__arr = array_unique($chose_other__arr);
            $ids = '';
            foreach ($chose_other__arr as $v) {
                $ids .= intval($v) . ',';
            }
            $ids = rtrim($ids, ',');
            $chose_other_list = $ids;

            //筛除掉不是该作业的相同的年级,学科,分册
            $exercise_where['biz_exercise_library_chapter.course_id'] = $result['course_id'];
            $exercise_where['biz_exercise_library_chapter.grade_id'] = $result['grade_id'];
            $exercise_where['biz_exercise_library_chapter.textbook_id'] = $result['textbook_id'];
            $exercise_where['_string'] = "biz_exercise_library.id in (" . $chose_other_list . ")";
            $exercise_model = M('biz_exercise_library');
            $exercise_result = $exercise_model->where($exercise_where)->join('biz_exercise_library_chapter on biz_exercise_library_chapter.id=biz_exercise_library.chapter_id')
                ->field('biz_exercise_library.id,biz_exercise_library_chapter.grade_id,biz_exercise_library_chapter.course_id,biz_exercise_library_chapter.textbook_id')->select();
            $chose_other_list = array();
            if (!empty($exercise_result)) {
                foreach ($exercise_result as $v) {
                    $chose_other_list[] = $v['id'];
                }
            }

            //$chose_other_list=explode(',',$chose_other_list);
            //$chose_other_list=array_unique($chose_other_list);
            if (!empty($chose_list)) {
                for ($i = 0; $i < count($chose_list); $i++) {
                    for ($j = 0; $j < count($chose_other_list); $j++) {
                        if ($chose_other_list[$j] == $chose_list[$i]) {
                            unset($chose_other_list[$j]);
                            break;
                        }
                    }
                }
            }
            sort($chose_other_list);
        }
        $grade_model = M('dict_grade');

        $Model = M('biz_exercise_library_chapter');

        $textbook_id = $result['textbook_id'];
        $grade_id = $result['grade_id'];
        $class_id = $result['class_id'];

        $model = M('biz_exercise_library_chapter');
        $chapter_result = $model->where('textbook_id=' . $textbook_id)->field('id,chapter')->group('chapter')->select();


        $grade_result = D('Biz_class')->getGradeListByTeacher(session('teacher.id'));


        //根据年级获得班级
        /*$class_model=M('biz_class');
        $class_result=$class_model->where("is_delete=0 and grade_id=".$grade_id." and class_teacher_id=".session('teacher.id'))->field('id,name')->select();*/

        $teacher_id = session('teacher.id');
        $class_result = D('Biz_class')->getClassListTeacher($teacher_id, $grade_id);
        $class_result = array_values($class_result);

        //根据年级获得所有学科
        $course_model = M('dict_course');
        $course_result = $course_model->where("auth_teacher_second.grade_id=" . $grade_id)
            ->field('dict_course.id,dict_course.course_name')
            ->join("auth_teacher_second on auth_teacher_second.teacher_id=" . session('teacher.id') . " and auth_teacher_second.course_id=dict_course.id")->group("dict_course.id")
            ->select();

        $this->assign('class', $class_result);
        $this->assign('course_list', $course_result);
        $this->assign('textbook_id', $textbook_id);
        $this->assign('data', $result);
        $this->assign('grade_list', $grade_result);
        $this->assign('chapter_list', $chapter_result);
        $this->assign('chose_list', $chose_list);
        $this->assign('chose_other_list', $chose_other_list);

        $this->display();
    }

    //多媒体作业查看作业习题
    public function mulHomeworkExercises()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '作业系统');
        $this->assign('navicon', 'zuoyexitong');
        $homework = intval(I('id'));
        if (!$homework) {
            redirect(U('Index/systemError'));
            die;
        }

        $Model = M('biz_homework');
        $result = $Model->where("id=" . $homework)->field('id,homework_name,claim,exercises_number')->find();

        $chapter_result = array();
        if (!empty($result)) {
            $where['homework_id'] = $homework;
            $homework_exercise_model = M('biz_homework_exercise');
            $chapter_result = $homework_exercise_model->where($where)
                ->join('biz_exercise_library_chapter chapter on chapter.id=biz_homework_exercise.chapter_id')
                ->group('chapter.id')
                ->field('chapter.chapter,chapter.festival')
                ->select();
        }
        $this->assign('data', $result);
        $this->assign('chapter_data', $chapter_result);

        $this->display();
    }

    //多媒体作业批改完的学生作业详情
    public function mulMarkingAfterHomework()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '查作业');
        $this->assign('navicon', 'zuoyexitong');

        $id = intval($_GET['id']);
        if (!$id) {
            redirect(U('Index/systemError'));
        }
        $this->assign('id', $id);

        //判断是否存在作业了
        $Model = M('biz_homework_student_details');
        $result = $Model->where("id=$id")->find();
        if (empty($result)) {
            redirect(U('Index/systemError'));
        }

        $this->assign('homework', $result);

        //试题信息
        $homeworkId = $result['homework_id'];
        $Model = M('biz_homework');
        $exerciseChapter = $Model->where("id=$homeworkId")->find();
        $this->assign('exerciseChapter', $exerciseChapter);
        $this->assign('homeworkId', $homeworkId);

        $chapter_result = array();
        if (!empty($result)) {
            $where['homework_id'] = $homeworkId;
            $homework_exercise_model = M('biz_homework_exercise');
            $chapter_result = $homework_exercise_model->where($where)
                ->join('biz_exercise_library_chapter chapter on chapter.id=biz_homework_exercise.chapter_id')
                ->group('chapter.id')
                ->field('chapter.chapter,chapter.festival')
                ->select();
        }
        $this->assign('chapter_data', $chapter_result);

        //获取题目数量
        //$exercise_chapter_id = $exerciseChapter['exercise_chapter_id'];
        $Model = M('biz_homework_exercise');
        $exerciseCount = $Model->where("homework_id=$homeworkId")->count('id');
        $this->assign('exerciseCount', $exerciseCount);

        //获取学生得分细节
        $studentId = $result['student_id'];
        $Model = M('biz_homework_score_details');
        $scoreDetails = $Model->where("homework_id=$homeworkId and student_id=$studentId")->find();
        $this->assign('scoreDetails', $scoreDetails);

        $this->display();
    }

    //多媒体作业学生作业详情
    public function mulStudentHomeworkDetails()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '查作业');
        $this->assign('navicon', 'zuoyexitong');

        $id = intval($_GET['id']);
        if (!$id) {
            redirect(U('Index/systemError'));
        }
        $this->assign('id', $id);

        //判断是否存在作业了
        $Model = M('biz_homework_student_details');
        $result = $Model->where("id=$id")->find();
        if (empty($result)) {
            redirect(U('Index/systemError'));
        }
        //这里判断,如果批改过了就不能再改了
        if ($result['status'] == 2) {
            $this->redirect(U('Teach/markingAfterHomework'), array('id' => $id));
        }

        $this->assign('homework', $result);
        $chapter_result = array();
        if (!empty($result)) {
            $where['homework_id'] = $result['homework_id'];
            $homework_exercise_model = M('biz_homework_exercise');
            $chapter_result = $homework_exercise_model->where($where)
                ->join('biz_exercise_library_chapter chapter on chapter.id=biz_homework_exercise.chapter_id')
                ->group('chapter.id')
                ->field('chapter.chapter,chapter.festival')
                ->select();
        }
        $this->assign('chapter_data', $chapter_result);
        //试题信息
        $homeworkId = $result['homework_id'];
        $Model = M('biz_homework');
        $exerciseChapter = $Model->where("id=$homeworkId")->find();
        $this->assign('exerciseChapter', $exerciseChapter);
        $this->assign('homeworkId', $homeworkId);

        //获取题目数量
        //$exercise_chapter_id = $exerciseChapter['exercise_chapter_id'];
        $Model = M('biz_homework_exercise');
        $exerciseCount = $Model->where("homework_id=$homeworkId")->count('id');
        $this->assign('exerciseCount', $exerciseCount);

        //获取学生得分细节
        $studentId = $result['student_id'];
        $Model = M('biz_homework_score_details');
        $scoreDetails = $Model->where("homework_id=$homeworkId and student_id=$studentId")->find();
        $this->assign('scoreDetails', $scoreDetails);


        $this->display();
    }


}
