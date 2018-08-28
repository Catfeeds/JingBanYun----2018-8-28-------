<?php
namespace ApiInterface\Controller\Version1_1;

use Think\Controller;

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
        $userId = getParameter('user_id', 'int');
        $role  = getParameter('role', 'int');

        switch($role)
        {   
            case 0: $result = D('Auth_teacher')->getTeachInfo($userId); 
                     $list = D('Auth_teacher_second')->getCourseGradeById($userId);
                     if(!empty($list))
                     {
                         foreach($list as $val)
                         {
                             $course_grade[] = $val['course_id'].','.$val['grade_id'];
                             $course_gradeName[] = $val['course_name'].$val['grade'];
                         }
                     }
                     $result['course_grade'] = $course_grade;
                     $result['course_grade_name'] = $course_gradeName;
                     $localAvatarFolder = 'Avatars';
                     break;
            case 1: $result = D('Auth_student')->getStudentInfo($userId);
                     $localAvatarFolder = 'StudentAvatars';
                     break;
            case 2: $result = D('Auth_parent')->getParentInfo($userId);
                     $localAvatarFolder = 'ParentAvatars';
                     break;
            default:$this->ajaxReturn(array('status' => 404, 'message' => '用户类型错误'));
        }

        $result = empty($result) ? array():$result;

        if((($role==1) || ($role==0)) )//增加学校昵称
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
        if($role==0){


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
        }elseif ($role==1){



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
                    $data['avatar'] = OSS_URL.$data['avatar'];
                    $this->ajaxReturn(array('status' => 200, 'message' => '上传成功','data'=>$data));
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

}