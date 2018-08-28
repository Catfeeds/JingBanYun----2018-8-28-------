<?php
namespace Home\Controller;
use Common\Common\SMS;
use Think\Controller;
use Common\Common\REDIS;
use Think\Verify;
define('STUDENT_IMAGE_PRODUCE_ID',22);
define('STUDENT_FORGET_PASSWORD_PRODUCE_ID',62);

class StudentController extends PublicController
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
    public function index()
    {
        if (!session('?student')) redirect(U('Index/index'));

        $this->display();
    }

    public function index1()
    {
        //print_r(session('auth_student'));die();
        if (!session('?student')) redirect(U('Index/index'));
        $this->auth_error = $_GET['auth_error'];
        $this->first = $_GET['first'];
        $studentinfo = M('auth_student')->where("id=".session('student.id'))->find();

        if (empty($studentinfo['sex'])) {
            $studentinfo['sex'] = '男';
        }

        $this->assign('tip',session('tip'));
        $this->assign('data',$studentinfo);
        $vipInfo = D('Account_auths')->isVipInfo(session('student.id'),ROLE_STUDENT);
        if($vipInfo['is_auth'] == 3 && $vipInfo['vip_day'] >=0){
            $this->assign('team_vip',1);
            $token = base64_encode($_COOKIE['PHPSESSID'].','.ROLE_STUDENT.','.session('student.id'));
            $key = A('Home/Auth')->getAuthKey($token);
            $pepUrl = "http://jby-szxy.mypep.cn/home/jby_auth/login?token=".$token.'&key='.$key;
            $this->assign('pep_redirect_url',$pepUrl);
        }//团体VIP且剩余天数大于0
        $this->display_nocache();
        session('tip', null);
    }


    public function me()
    {
        if (!session('?student')) redirect(U('Index/index'));

        A('Home/Common')->authJudgement();

        $this->assign('module', '个人中心');
        $this->assign('nav', '我');
        $this->assign('subnav', '我的资料');
        $this->assign('navicon', 'xueshengfengcai');

        $id = session('student.id');
        $Model = M('auth_student');
        $result = $Model->field('auth_student.*,dict_schoollist.*,dict_schoollist.id as did,auth_student.id')->join('left join dict_schoollist on auth_student.school_id = dict_schoollist.id')->where("auth_student.id=$id")->find();
        if (!empty($result['birth_date'])) {
            $result['birth_date'] = date("Y-m-d",$result['birth_date']);
        } else{
            $result['birth_date'] = '';
        }

        if ( !preg_match('/Resources/', $result['avatar']) ) {
            $result['avatar'] = '';
        }
        if(empty( $result['sex'] )) {
            $result['sex'] = '男';
        }

        $this->assign('data', $result);
		$this->assign('uid',$id);
         $msgModel = M('notice_message');
         $where['role'] = 1; //student
         $where['user_id'] = $id;
         $where['read_status'] = 0; //unread
         $count = $msgModel ->where($where)->count();
         $this->assign('unreadCount','('.$count.')');

        $stumap['student_id'] = $id;
        $stumap['biz_class.flag'] = 1;
        $stumap['biz_class.is_delete'] = 0;

        $stuclass_grade = M('biz_class_student')
            ->join('biz_class on biz_class.id = biz_class_student.class_id')
            ->join('dict_grade on dict_grade.id = biz_class.grade_id')
            ->where($stumap)
            ->field('biz_class.name,dict_grade.grade,biz_class.class_status')
            ->select();

        if (!empty($stuclass_grade)) {
            $strclass = '';
            $classlist = [];
            foreach ($stuclass_grade as $sk=>$sv) {
                if ($sv['class_status']==1) { //校建
                    $classlist[] = '<span class="myClass xjClass">'.$sv['grade'].$sv['name'].'<img src="'.C('oss_path').'public/web_img/Me/xj.png" alt=""></span>';
                } else {
                    $classlist[] = '<span class="myClass">'.$sv['grade'].$sv['name'].'</span>';
                }

            }
            $strclass = implode('&nbsp;',$classlist);
            $this->assign('strclass',$strclass);
        }


        $this->display_nocache();
    }

     public function ossUploadAvatar() {
        //$id = $_GET['id'];
        $id=getParameter('id', 'int',false);
         if($_POST['img'])
         {
             saveTempAvatar($_POST['img']);
         }
        $img_path = $this->upload_file();
        $img_path_url = json_decode($img_path,true);
        $Model = M('auth_student');
        $c1['id'] = $id;
        $data['avatar'] = $img_path_url['res'];
        $a = $Model->where($c1)->save($data);

        $rs['status'] = 1;
        $rs['src'] = C('oss_path').$img_path_url['res'];
        echo json_encode($rs);
    }

    public function ossUploadData() {
        //$id = $_GET['id'];
        $id=getParameter('id', 'int',false);
        $img_path = $this->upload_file();
        $img_path_url = json_decode($img_path,true);
        $Model = M('auth_student');
        $c1['id'] = $id;
        $data['avatar'] = $img_path_url['res'];
        $a = $Model->where($c1)->save($data);

        $rs['status'] = 1;
        $rs['src'] = $img_path_url['res'];
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
        $arr['message_video_image']=$video_image;
        return json_encode($arr);
    }

    //message list
        public function message()
        {
         if (!session('?student')) redirect(U('Index/index'));
         $studentId = session('student.id');
         $msgModel = M('notice_message');
         if($_POST)
         {
         //$msgid = $_POST['id'];
         $msgid =getParameter('id', 'int',false);
         $data['read_status'] = 1;//read
         $msgModel->where ("id=$msgid") ->save($data);
         $rs['status'] = 200;
         echo json_encode($rs);
         }
         else
         {
         $where['role'] = 1; //student
         $where['user_id'] = $studentId;
         $result = $msgModel ->where($where)->select();
         for($i=0;$i<sizeof($result);$i++)
            $result[$i]['create_at'] = date("Y-m-d H:i:s",$result[$i]['create_at']);
         $this->assign('data',$result);
         $where['read_status'] = 0; //unread
         $count = $msgModel ->where($where)->count();
         $this->assign('unreadCount','('.$count.')');

         $studentInfo = M('auth_student')->where("id=$studentId")->find();
         $this->assign('dataAvatar',$studentInfo);

         $this->display();
         }
        }
    //登陆验证并跳转
    public function login()
    {
        if ($_POST) {
            $theme = "1";
            //$check['parent_tel'] = $_POST['telephone_2'];
            //$check['student_name'] = $_POST['user_name_2'];
            //$check['password'] = sha1($_POST['password_2']);

            $check['parent_tel'] = getParameter('telephone_2', 'str',false);
            $check['student_name'] = getParameter('user_name_2', 'str',false);
            $check['password'] = sha1(getParameter('password_2', 'str',false));

            //$check['lock'] = 0;

            $Model = M('auth_student');
            $result = $Model->where($check)->find();
            if ($result) {

                if ($result['flag'] == 0|| $result['flag'] == -1) {
                    $this->redirect("Index/index?role=t&err=5");
                }
                session('auth_teacher', null);
                session('auth_parent', null);
                session('teacher', null);
                session('parent', null);

                session('student', $result);

                $login_mac_address['access_token'] = session_id();
                $Model->where("id=".$result['id'])->save( $login_mac_address );

                session('theme', $theme);


                //判断是否是vip 如果是计算天数

                $auth_type_use = D('Account_auths');
                //$auth_list = $auth_type_use->isVipAndInfo(1,5); //游客的权限和角色

                $auth_list = $auth_type_use->getAuthAndVipauth(session('student.id'),3);
                session('auth_student', $auth_list);

                $isVipInfo = $auth_type_use->isVipInfo(session('student.id'),3);
                session('student_vip',$isVipInfo);

                $btntheme = "primary";
                if ($theme == 2) $btntheme = "danger";
                if ($theme == 3) $btntheme = "dark";
                session('btntheme', $btntheme);

                $student_id=session('student.id');
                $client_ip=get_client_ip();
                $current_login_address=getIPLoc_sina($client_ip);
                //if($current_login_address!=$result['login_address'] && '' != $result['login_address'] ) {
                if($current_login_address!=$result['login_address'] ) {
                    $Model->where('id=' . $result['id'])->save(array('login_address' => $current_login_address));

                    $ip_arr = explode('.', $client_ip);
                    $ip_arr[3] = '*';
                    $ip_string = implode('.', $ip_arr);
                    $login_address_str = $current_login_address . '(' . $ip_string . ')';
                    if(!empty($result['login_address'])) {
                        $parameter_arr = array(
                            'msg' => array(date('Y-m-d H:i:s'), $login_address_str, 'pc'),
                            'url' => array(
                                'type' => 0,
                                'data' => array()
                            )
                        );
                        A('Home/Message')->addPushUserMessage('EXCEPTION_LOGIN', 3, $student_id, $parameter_arr);
                        $parentInfo = D('Auth_student')->getStudentParentInfo($student_id);
                        A('Home/Message')->addPushUserMessage('CHILD_EXCEPTION_LOGIN', 4, $parentInfo['id'], $parameter_arr);
                    }
                }
                    if ($result['is_first'] == 1) {
                        $mapfirst['id'] = $result['id'];
                        $firstdata['is_first'] = 2;
                        M('auth_student')->where($mapfirst)->save($firstdata);

                    }

                $tip = A('Home/Common')->registered_but_no_uploadworks(session('student.id'),3);
                session('tip', $tip);

                $share_par=$_REQUEST['url'];
                if(!empty($share_par)){
                    $share_par=base64_decode($share_par);
                    $tokenValue = base64_encode(session_id().','.ROLE_STUDENT.','.session('student.id'));
                    $tokenString = "token=".$tokenValue;
                    $key = A('Home/Auth')->getAuthKey($tokenValue);
                    if(strpos($share_par,'?') !== false)
                        $share_par .= "&".$tokenString.'&key='.$key;
                    else
                        $share_par .= "?".$tokenString.'&key='.$key;
                    header('Location:' . $share_par);
                }else{
                    $this->redirect("Student/index$theme");
                }

            } else {
                $this->redirect("Index/index?role=s&err=1");
            }
        } else {
            session('student', null);
            $this->assign('REMOTE_ADDR',C('REMOTE_ADDR'));
            $this->display();
        }
    }

    //退出登录
    public function logout()
    {
        //向人教发送登出请求
        A('Home/Auth')->pepSendLogout($_COOKIE['PHPSESSID'],ROLE_STUDENT,session('student.id'));
        session('student', null);
        $this->redirect("Index/index");
    }

    //发送找回验证码
    public function sendForgetPasswordPhoneCode()
    {

        $redis_obj=new REDIS();
        $redis=$redis_obj->init_redis();

        if(!$this->isMobile()) {
            //判断不是手机,不是登陆后个人中心发送的就判断
            $n_verify_code = getParameter('n_verify_code','str',false);
            if(empty($n_verify_code)){
                $verify_code = getParameter('code', 'str', false);
                if (!$this->check_verify_code(false, $verify_code,STUDENT_FORGET_PASSWORD_PRODUCE_ID)) {
                    $this->showjson(-5, '图形验证码错误');
                }
            }
            $sendSmsFunction='templateSMS';
        }else{
            $sendSmsFunction='newTemplateSMS';
        }

        $telephone = getParameter('telephone', 'str',false);

        if (empty($telephone) || strlen($telephone) != 11) {
            $this->showjson(-4, '手机号码格式错误');
        }

        $check['parent_tel'] = $telephone;
        //$check['student_name'] = $_REQUEST['name'];
        $studentModel = M('auth_student');
        $student = $studentModel->where($check)->find();

        $out['status'] = 0;
        $out['msg'] = '';
        $out['content'] = '';

        if (empty($student)) {
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

        /*$CodeModel = M('misc_forgot_password_phone_code');
        $code = $CodeModel->where("telephone=$telephone")->find();
        $data['telephone'] = $telephone;
        $data['code'] = $randcode;
        $data['create_at'] = time();

        if (empty($code)) {
            $CodeModel->add($data);
        } else {
            $CodeModel->where("telephone=$telephone")->save($data);
        }*/

        $this->showjson(0, 'success');
    }

    //学生找回密码图形验证生成
    public function produceVerifyCodeP(){
        $config = array(
             'fontSize' => 20,
             'length' => 4, // 验证码位数
             'imageH' => 40);
         $verify = new Verify($config);
         $verify->entry(STUDENT_FORGET_PASSWORD_PRODUCE_ID);
    }

    function check_verify_p(){
        //$code=getParameter('code','str',false);
        $this->check_verify_code(1,$code,STUDENT_FORGET_PASSWORD_PRODUCE_ID);
    }

     public function forgetPassword()
        {
            if ($_POST) {
                /*
                $data['student_name'] = $_POST['name'];
                $data['parent_tel'] = $_POST['telephone'];
                $data['password'] = sha1($_POST['password']);
                $confirmPassword = sha1($_POST['confirm_password']);
                */
                $data['student_name'] = getParameter('name', 'str',false);
                $data['parent_tel'] = getParameter('telephone', 'str',false);
                $data['password'] = sha1(getParameter('password', 'str',false));
                $confirmPassword = sha1(getParameter('confirm_password','str',false));

                $verifyCode= getParameter('verify_code','str',false);
                $nVerifyCode= getParameter('n_verify_code','str',false);

                if(empty($nVerifyCode)){
                    //图形验证码
                    if(!$this->check_verify_code(false,$verifyCode,STUDENT_FORGET_PASSWORD_PRODUCE_ID)){
                        $this->ajaxReturn(array('code' => -1,'msg' => '请填写正确的图形验证码'));
                    }
                }

                if ($confirmPassword <> $data['password']) {
                    $this->ajaxReturn(array('code' => -1,'msg' => '两次密码不一致'));
                }

                $studentModel = M('auth_student');
                //$CodeModel = M('misc_forgot_password_phone_code');

                $check['student_name'] = $data['student_name'];
                $check['parent_tel'] = $data['parent_tel'];

                //$code = $CodeModel->where("telephone=".$_POST['telephone'])->find();
                $redis_obj=new REDIS();
                $redis=$redis_obj->init_redis();
                $redis_key='sms_'.$check['parent_tel'];
                $code['code']=$redis->get($redis_key);

                $result = $studentModel->where($check)->find();
                if(empty($result)){
                    $this->ajaxReturn(array('code' => -1,'msg' => '该用户不存在'));
                }

                if ($code['code'] == $_POST['valid_code'] && $_POST['valid_code']) {
                    $redis->delete($redis_key);
                    $redis->close();

                    $studentModel->where($check)->save($data);

                    //$CodeModel->where("telephone=".$_POST['telephone'])->delete();
                    $parameters = array( 'msg' => array(date('Y-m-d H:i:s',time())) ,
                         'url' => array( 'type' => 0)
                          );
                    A('Home/Message')->addPushUserMessage('PASSWORD_MODIFY',3,$result['id'],$parameters);

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

    //专家资讯信息列表
    public function expertInformationList()
    {
        if (!session('?student')) redirect(U('Index/index'));

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Student/index1?auth_error=1'));
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
        if (!session('?student')) redirect(U('Index/index'));

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
        $studentId = session('student.id');
         if(empty($studentId))
          {
          //$studentId = $_GET['user_id'];
             $studentId = getParameter('user_id', 'int',false,2);
          if(empty($studentId))
           {
           redirect(U('Index/index'));
           }
          }
        //$social_activity_id = $_GET['social_activity_id'];
          $social_activity_id = getParameter('social_activity_id', 'int',false,2);

        $ZanModel = M('social_activity_zan');
        $zanData['social_activity_id'] = $social_activity_id;
        $zanData['user_id'] = $studentId;
        $zanData['user_type'] = 2;


        $existed = $ZanModel->where($zanData)->find();
        if (empty($existed)) {
            $zanData['zan_time'] = time();
            $ZanModel->add($zanData);

            $Model = M('social_activity');
            $Model->where("id=$social_activity_id")->setInc('zan_count', 1);

            $this->ajaxReturn("success");
        } else {
            $ZanModel->where("social_activity_id=$social_activity_id and user_id=$studentId and user_type=2")->delete();
            $Model = M('social_activity');
            $Model->where("id=$social_activity_id")->setDec('zan_count', 1);

            $this->ajaxReturn("已经取消点赞");
        }
    }

    //收藏一个京版活动
    public function favorActivity()
    {
        $studentId = session('student.id');
         if(empty($studentId))
          {
          //$studentId = $_GET['user_id'];
            $social_activity_id = getParameter('social_activity_id', 'int',false,2);
          if(empty($studentId))
           {
           redirect(U('Index/index'));
           }
          }
        //$social_activity_id = $_GET['social_activity_id'];
          $social_activity_id = getParameter('social_activity_id', 'int',false,2);

        $FavorModel = M('social_activity_favor');
        $favorData['social_activity_id'] = $social_activity_id;
        $favorData['user_id'] = $studentId;
        $favorData['user_type'] = 2;

        $existed = $FavorModel->where($favorData)->find();
        if (empty($existed)) {
            $favorData['favor_time'] = time();
            $FavorModel->add($favorData);

            $Model = M('social_activity');
            $Model->where("id=$social_activity_id")->setInc('favor_count', 1);

            $this->ajaxReturn("success");
        } else {
            $FavorModel->where("social_activity_id=$social_activity_id and user_id=$studentId and user_type=2")->delete();
            $Model = M('social_activity');
            $Model->where("id=$social_activity_id")->setDec('favor_count', 1);

            $this->ajaxReturn("已经取消收藏");
        }
    }

    //教师风采
    public function teacherStyle()
    {
        if (!session('?student')) redirect(U('Index/index'));

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Student/index1?auth_error=1'));
        }

        $this->assign('module', '励耘圈');
        $this->assign('nav', '教师风采');
        $this->assign('subnav', '教师风采榜');
        $this->assign('navicon', 'jiaoshifengcai');

        //$courseId = $_REQUEST['course_id'];
        $courseId = getParameter('course_id', 'int',false,2);
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
        if (!session('?student')) redirect(U('Index/index'));

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Student/index1?auth_error=1'));
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
        $filter['course_id'] = getParameter('course', 'int',false,2);
        $filter['grade_code'] = getParameter('grade', 'int',false,2);
        //$filter['textbook_id'] = $_REQUEST['textbook_id'];
        $filter['school_term'] = getParameter('textbook', 'int',false,2);
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
            //->where("biz_resource.status=2 and biz_resource.course_id=$courseId and biz_resource.grade_id=$gradeIdId")
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
        */

        $this->display_nocache();
    }

    //资源详情
    public function resourceDetails($id,$from="",$type=2)
    {
        if (!session('?student')) redirect(U('Index/index'));

        if($type!=1) {
            $isAuth = $this->isAuth($this->c_a);
            if (!$isAuth) { //如果访问的模块没有权限
                redirect(U('Student/index1?auth_error=1'));
            }
        }

        $this->assign('module', '教学+');
        $this->assign('nav', '教师资源分享');
        $this->assign('navicon', 'jiaoshiziyuan');

        $id=intval($id);
        if(empty($id))
        //$id = $_GET['id'];
          $id = getParameter('id', 'int',false,2);
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
        $zanData['user_type'] = 1;
        $zanData['user_id'] = session('student.id');
        $existedZan = $ZanModel->where($zanData)->find();
        $existedZan = empty($existedZan) ? 'no' : 'yes';

        $this->assign('existedZan', $existedZan);

        $FavorModel = M('biz_resource_collect');
        $favorData['resource_id'] = $id;
        $favorData['user_type'] = 1;
        $favorData['user_id'] = session('student.id');
        $existedFavor = $FavorModel->where($favorData)->find();
        $existedFavor = empty($existedFavor) ? 'no' : 'yes';
        $this->assign('existedFavor', $existedFavor);
        $this->display();

    }
    //赞一个教师资源
    public function zanResource()
    {
        $studentId = session('student.id');
        if(empty($studentId))
         {
         //$studentId = $_GET['user_id'];
            $studentId = getParameter('user_id', 'int');
         if(empty($studentId))
          {
          redirect(U('Index/index'));
          }
         }

        //$id = $_GET['id'];
         $id = getParameter('id', 'int');

        $ZanModel = M('biz_resource_zan');
        $zanData['resource_id'] = $id;
        $zanData['user_id'] = $studentId;
        $zanData['user_type'] = 1;

        $existed = $ZanModel->where($zanData)->find();
        if (empty($existed)) {
            $zanData['create_at'] = time();
            $res = M('auth_student')->where("id=$studentId")->find();
            $zanData['user_name'] = $res['student_name'];
            $ZanModel->add($zanData);

            $Model = M('biz_resource');
            $Model->where("id=$id")->setInc('zan_count', 1);


            $this->ajaxReturn("success");
        } else {
            $ZanModel->where("resource_id=$id and user_type=1 and user_id=$studentId")->delete();
            $Model = M('biz_resource');
            $Model->where("id=$id")->setDec('zan_count', 1);


            $this->ajaxReturn("已经取消点赞");
        }
    }


    //收藏一个教师资源
    public function favorResource()
    {
        $studentId = session('student.id');
         if(empty($studentId))
          {
          //$studentId = $_GET['user_id'];
            $studentId = getParameter('user_id', 'int');
          if(empty($studentId))
           {
           redirect(U('Index/index'));
           }
          }

        //$id = $_GET['id'];
          $id = getParameter('id', 'int');

        $FavorModel = M('biz_resource_collect');
        $favorData['resource_id'] = $id;
        $favorData['user_id'] = $studentId;
        $favorData['user_type'] = 1;

        $existed = $FavorModel->where($favorData)->find();
        if (empty($existed)) {
            $favorData['create_at'] = time();
            $res = M('auth_student')->where("id=$studentId")->find();
            $favorData['user_name'] = $res['student_name'];
            $FavorModel->add($favorData);

            $Model = M('biz_resource');
            $Model->where("id=$id")->setInc('favorite_count', 1);


            $this->ajaxReturn("success");
        } else {
            $FavorModel->where("resource_id=$id and user_type=1 and user_id=$studentId")->delete();
            $Model = M('biz_resource');
            $Model->where("id=$id")->setDec('favorite_count', 1);


            $this->ajaxReturn("已经取消收藏");
        }
    }



    ////电子课本
    public function textbookList()
    {
        if (!session('?student')) redirect(U('Index/index'));
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Student/index1?auth_error=1'));
        }

        if($_REQUEST['course'])
            $check['biz_textbook.course_id'] = getParameter('course', 'int',false);
        if($_REQUEST['grade'])
            $check['biz_textbook.grade_id'] = getParameter('grade', 'int',false);
        if($_REQUEST['textbook'])
            $check['biz_textbook.school_term'] = getParameter('textbook', 'int',false);
        if($_REQUEST['keyword']){
            $keyword=getParameter('keyword', 'str',false);;
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

    //查看班级中的学生  没用到
    public function classStudentList()
    {
        if (!session('?student')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '我的班级');
        $this->assign('subnav', '班级学生');
        $this->assign('navicon', 'wodebanji');

        //$page = $_GET['page'];
          $page = getParameter('page', 'int',false,2);
        if (empty($page)) $page = 1;

        //$classId = $_GET['classId'];
        $classId = getParameter('classId', 'int',false,2);

        $Model = M('biz_class_student');
        $result = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->join('biz_class on biz_class.id=biz_class_student.class_id')
            ->field('auth_student.id,auth_student.student_name,auth_student.student_id,auth_student.user_name,auth_student.avatar')
            ->where("biz_class_student.class_id=$classId")
            ->order('auth_student.student_name asc')
            ->page($page, 50)
            ->select();

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

    //学生轨迹
    public function learningPath()
    {
        if (!session('?student')) redirect(U('Index/index'));

        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Student/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '我的班级');
        $this->assign('navicon', 'wodebanji');


        $studentId = session('student.id');

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

//////作业系统
    public function homework()
    {
        if (!session('?student')) redirect(U('Index/index'));

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Student/index1?auth_error=1'));
        }

        $mca = I('mca');
        if ( $mca == 'action' ) {
            $this->assign('kw',1);
        }
        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '我的作业');
        $this->assign('navicon', 'zuoyexitong');

        $where['biz_class_student.student_id'] = session('student.id');
        $student_id=session('student.id');

        if (empty($student_id)) {
            die('error');
        }

        if(I('grade')>0){
            $where['dict_grade.id']=intval(I('grade'));
        }
         if(I('class')>0){
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
                $sort_where='detail.duration asc,dostatus asc,create_at desc';
            }else if($sort_column==2){
                $sort_where='detail.duration desc,dostatus desc,create_at desc';
            }else if($sort_column==3){
                $sort_where='detail.points asc,dostatus asc,create_at desc';
            }else{
                $sort_where='detail.points desc,dostatus desc,create_at desc';
            }
        }else{
            $sort_column=1;
            $sort_where='create_at desc';
        }


        $date=I('date');
        if(!empty($date)){
            $where["_string"]="biz_homework.create_at>=".strtotime(I("date"))." and "."biz_homework.create_at<=".(strtotime(I("date")."+1 day")-1);
        }

        $where['biz_homework.homework_status']=1;

        $Model = M('biz_homework');

        if($status==1){
           // $unionSql = "SELECT biz_homework.id FROM `biz_homework` INNER JOIN biz_class on biz_class.id=biz_homework.class_id INNER JOIN biz_class_student_record on biz_class_student_record.class_id=biz_homework.class_id INNER JOIN dict_grade on dict_grade.id=biz_homework.grade_id INNER JOIN dict_course on dict_course.id=biz_homework.course_id INNER JOIN biz_textbook on biz_textbook.id=biz_homework.textbook_id INNER JOIN biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id=1 WHERE biz_class_student_record.student_id = ".session('student.id')." AND biz_homework.homework_status = 1 GROUP BY biz_homework.id";
            $count = $Model
            ->join('biz_class on biz_class.id=biz_homework.class_id')
            ->join('biz_class_student on biz_class_student.class_id=biz_homework.class_id')
            ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
            ->join('dict_course on dict_course.id=biz_homework.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
            ->join('biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id='.$student_id)
            ->field("biz_homework.id")
            ->where($where)
            ->group("biz_homework.id")
            //->union($unionSql)
            ->select();

            $count=count($count);

            $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));

            //$unionSqlSelect = "SELECT biz_homework.*,dict_grade.grade,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,detail.duration,detail.status dostatus,detail.points FROM `biz_homework` INNER JOIN biz_class on biz_class.id=biz_homework.class_id INNER JOIN biz_class_student_record on biz_class_student_record.class_id=biz_homework.class_id INNER JOIN dict_grade on dict_grade.id=biz_homework.grade_id INNER JOIN dict_course on dict_course.id=biz_homework.course_id INNER JOIN biz_textbook on biz_textbook.id=biz_homework.textbook_id INNER JOIN biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id=1 WHERE biz_class_student_record.student_id = ".session('student.id')." AND biz_homework.homework_status = 1 GROUP BY biz_homework.id";

            $homeworks = $Model
            ->join('biz_class on biz_class.id=biz_homework.class_id')
            ->join('biz_class_student on biz_class_student.class_id=biz_homework.class_id')
            ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
            ->join('dict_course on dict_course.id=biz_homework.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
            ->join('biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id='.$student_id)
            ->field("biz_homework.*,dict_grade.grade,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,detail.duration,detail.status dostatus,detail.points ")
            ->where($where)
            ->group("biz_homework.id")
            ->order("biz_homework.create_at desc")
            ->limit($Page->firstRow . ',' . $Page->listRows)
            //->union($unionSqlSelect.' ORDER BY '.$sort_where." LIMIT ".$Page->firstRow . ',' . $Page->listRows)
            ->select();

        }else if($status==2){

            //$unionSql = "SELECT biz_homework.id,detail.points FROM `biz_homework` INNER JOIN biz_class on biz_class.id=biz_homework.class_id INNER JOIN biz_class_student_record on biz_class_student_record.class_id=biz_homework.class_id INNER JOIN dict_grade on dict_grade.id=biz_homework.grade_id INNER JOIN dict_course on dict_course.id=biz_homework.course_id INNER JOIN biz_textbook on biz_textbook.id=biz_homework.textbook_id left JOIN biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id=1 WHERE biz_class_student_record.student_id = ".session('student.id')." AND biz_homework.homework_status = 1 GROUP BY biz_homework.id HAVING detail.points is null";

            $count = $Model
                ->join('biz_class on biz_class.id=biz_homework.class_id')
                ->join('biz_class_student on biz_class_student.class_id=biz_homework.class_id')
                ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
                ->join('dict_course on dict_course.id=biz_homework.course_id')
                ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
                ->join('biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id='.$student_id,'left')
                ->field("biz_homework.id,detail.points ")
                ->where($where)
                //->union($unionSql)
                ->group("biz_homework.id")
                ->having("detail.points is null")
                ->select();

            $count=count($count);
            $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));

            //$unionSqlSelect = "SELECT biz_homework.*,dict_grade.grade,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,detail.duration,detail.status dostatus,detail.points FROM `biz_homework` INNER JOIN biz_class on biz_class.id=biz_homework.class_id INNER JOIN biz_class_student_record on biz_class_student_record.class_id=biz_homework.class_id INNER JOIN dict_grade on dict_grade.id=biz_homework.grade_id INNER JOIN dict_course on dict_course.id=biz_homework.course_id INNER JOIN biz_textbook on biz_textbook.id=biz_homework.textbook_id left JOIN biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id=1 WHERE biz_class_student_record.student_id = ".session('student.id')." AND biz_homework.homework_status = 1 GROUP BY biz_homework.id HAVING detail.points is null";
            $homeworks = $Model
                ->join('biz_class on biz_class.id=biz_homework.class_id')
                ->join('biz_class_student on biz_class_student.class_id=biz_homework.class_id')
                ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
                ->join('dict_course on dict_course.id=biz_homework.course_id')
                ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
                ->join('biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id='.$student_id,'left')
                ->field("biz_homework.*,dict_grade.grade,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,detail.duration,detail.status dostatus,detail.points ")
                ->where($where)
                ->group("biz_homework.id")
                ->order("biz_homework.create_at desc")
                ->having("detail.points is null")
                ->limit($Page->firstRow . ',' . $Page->listRows)
                //->union($unionSqlSelect.' ORDER BY '.$sort_where." LIMIT ".$Page->firstRow . ',' . $Page->listRows)
                ->select();


        }else{

            //$unionSql = "SELECT biz_homework.id FROM `biz_homework` INNER JOIN biz_class on biz_class.id=biz_homework.class_id INNER JOIN biz_class_student_record on biz_class_student_record.class_id=biz_homework.class_id INNER JOIN dict_grade on dict_grade.id=biz_homework.grade_id INNER JOIN dict_course on dict_course.id=biz_homework.course_id INNER JOIN biz_textbook on biz_textbook.id=biz_homework.textbook_id left JOIN biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id=1 WHERE biz_class_student_record.student_id = ".session('student.id')." AND biz_homework.homework_status = 1 GROUP BY biz_homework.id";

            $count = $Model
            ->join('biz_class on biz_class.id=biz_homework.class_id')
            ->join('biz_class_student on biz_class_student.class_id=biz_homework.class_id')
            ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
            ->join('dict_course on dict_course.id=biz_homework.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
            ->join('biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id='.$student_id,'left')
            ->field("biz_homework.id ")
            ->where($where)
            ->group("biz_homework.id")
            //->union($unionSql)
            ->select();
            $count=count($count);
            $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));

            //$unionSqlSelect = "SELECT biz_class.is_delete,biz_class.flag, biz_homework.*,dict_grade.grade,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,detail.duration,detail.status dostatus,detail.points FROM `biz_homework` INNER JOIN biz_class on biz_class.id=biz_homework.class_id INNER JOIN biz_class_student_record on biz_class_student_record.class_id=biz_homework.class_id INNER JOIN dict_grade on dict_grade.id=biz_homework.grade_id INNER JOIN dict_course on dict_course.id=biz_homework.course_id INNER JOIN biz_textbook on biz_textbook.id=biz_homework.textbook_id left JOIN biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id=1 WHERE biz_class_student_record.student_id = ".session('student.id')." AND biz_homework.homework_status = 1 GROUP BY biz_homework.id";

            $homeworks = $Model
                    ->join('biz_class on biz_class.id=biz_homework.class_id')
                    ->join('biz_class_student on biz_class_student.class_id=biz_homework.class_id')
                    ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
                    ->join('dict_course on dict_course.id=biz_homework.course_id')
                    ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
                    ->join('biz_homework_student_details detail on detail.homework_id=biz_homework.id and detail.student_id='.$student_id,'left')
                    ->field("biz_class.is_delete,biz_class.flag,biz_homework.*,dict_grade.grade,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,detail.duration,detail.status dostatus,detail.points ")
                    ->where($where)
                    ->order("biz_homework.create_at desc")
                    ->group("biz_homework.id")
                    ->limit($Page->firstRow . ',' . $Page->listRows)
                    //->union($unionSqlSelect.' ORDER BY '.$sort_where." LIMIT ".$Page->firstRow . ',' . $Page->listRows)
                    ->select();
        }

        $show = $Page->show();
        $Model = M('biz_exercise_library_chapter');
        $homework_model=M('biz_homework');
        $i = 0;
        $outlist = array();
        $is_read=0;

        foreach ($homeworks as $key=>$homework) {


            //这里去求那个章节
            $info=$Model->where("u.id=".$homework['id'])
                    ->join("biz_homework_exercise t on t.chapter_id=biz_exercise_library_chapter.id")
                    ->join("biz_homework u on u.id=t.homework_id")
                    ->group("t.chapter_id")->field("distinct chapter,festival")->select();   //echo $Model->getLastsql();die;
            $homework['chapter']=$info;
            if($homework['duration']==null){
                //该作业ID,插入学生ID,逗号分隔。
                $temp_homework=$homework_model->where('id='.$homework['id'])->field('id,read_ids')->find();
                //判断该学生是否已在列表中
                $read_ids=  explode(',',$temp_homework['read_ids']);
                if(!in_array(session('student.id'), $read_ids)){
                    $is_read=1;
                    if($temp_homework['read_ids']!=''){
                        $update_data['read_ids']=$temp_homework['read_ids'].','.session('student.id');
                    }else{
                        $update_data['read_ids']=session('student.id');
                    }
                    $homework_model->where("id=".$homework['id'])->save($update_data);
                }
            }

            $is_delete_teacher_map['class_id'] = $homework['class_id'];
            $is_delete_teacher_map['student_id'] = session('student.id');
            $is_delete_teacher = M('biz_class_student_record')->where( $is_delete_teacher_map )->find();
            if (!empty($is_delete_teacher)) {
                $homework['flag'] = 0; //被学校移除的老师
            }

            if ($homework['is_delete'] != 0) {
                $homework['flag'] = 0; //班级删除
            }

            $outlist[$i] = $homework;
            $i = $i + 1;


        }

        //print_r($outlist);die();

        //echo "<pre>";
        //print_r($outlist);die;
        //该学生是否有新的作业
        $this->assign('is_read', $is_read);
        $this->assign('list', $outlist);
        $this->assign('page', $show);

        $grade_model=M('dict_grade');
        $grade_result=$grade_model->where("biz_class_student.student_id=".session('student.id'))->join("biz_class on biz_class.grade_id=dict_grade.id")
                ->join("biz_class_student on biz_class_student.class_id=biz_class.id")->field('dict_grade.id,dict_grade.grade')->group("dict_grade.id")->select();
        //年级不为空,求出班级和学科
        if(!empty($where['dict_grade.id'])){
            $class_model = M('biz_class');
            $class_result=$class_model->join('biz_class_student on biz_class_student.class_id=biz_class.id')->where("grade_id=".$where['dict_grade.id']." and biz_class_student.status=2 and biz_class_student.student_id=".session('student.id'))
                        ->field('biz_class.id,biz_class.name')
                        ->group("biz_class.name")
                        ->select();

            //$course_model = M('dict_course');
            $course_result=$class_model->join('biz_class_student on biz_class_student.class_id=biz_class.id')
                        ->join('auth_teacher_second on auth_teacher_second.teacher_id=biz_class.class_teacher_id')
                        ->join('dict_course on dict_course.id=auth_teacher_second.course_id')
                        ->where("biz_class.grade_id=".$where['dict_grade.id']." and biz_class_student.status=2 and biz_class_student.student_id=".session('student.id'))
                        ->field('dict_course.id,dict_course.course_name')
                        ->group("dict_course.id")
                        ->select();
        }

        //年级和学科不为空,求出教材分册
        if(!empty($where['dict_grade.id']) && !empty($where['dict_course.id'])){
            $course_model = M('biz_textbook');
            $textbook_result=$course_model->where("grade_id=".$where['dict_grade.id']." and course_id=".$where['dict_course.id'])
                ->field('id,name')->select();
        }


        /*$Model = M('dict_course');
        $courses = $Model->where("student.id=".session('student.id')." and biz_homework.homework_status=1")->field("dict_course.id,dict_course.course_name")
                    ->join("biz_homework on biz_homework.course_id=dict_course.id")
                    ->join('biz_class on biz_class.id=biz_homework.class_id')
                    ->join('biz_class_student class_contact on class_contact.class_id=biz_class.id')
                    ->join('auth_student student on student.id=class_contact.student_id')
                    ->order('sort_order asc')->group("dict_course.id")->select();
        $this->assign('courses_list', $courses);

        $class_model = M('biz_class');
        $class_result=$class_model->where("student.id=".session('student.id')." and biz_homework.homework_status=1")->field('biz_class.id,biz_class.name,student.id student_id')
                                ->join('biz_class_student class_contact on class_contact.class_id=biz_class.id')
                                ->join('auth_student student on student.id=class_contact.student_id')
                                ->join('biz_homework on biz_homework.class_id=biz_class.id')->group("biz_class.name")->select();
        $this->assign('class_list', $class_result);

        $biz_textbook_mode=M('biz_textbook');
        $textbook_result=$biz_textbook_mode->where("student.id=".session('student.id'))->field('biz_textbook.id,biz_textbook.name')
                        ->join('biz_homework on biz_homework.textbook_id=biz_textbook.id')
                        ->join('biz_class on biz_class.id=biz_homework.class_id')
                        ->join('biz_class_student class_contact on class_contact.class_id=biz_class.id')
                        ->join('auth_student student on student.id=class_contact.student_id')
                        ->group("biz_textbook.id")->select();
        $this->assign('textbook_list', $textbook_result);
        */

        $this->assign('grade_list', $grade_result);
        $this->assign('class_list', $class_result);
        $this->assign('courses_list', $course_result);
        $this->assign('textbook_list', $textbook_result);


        $this->assign('default_grade', $where['dict_grade.id']);
        $this->assign('default_class', $class_id);
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
            if(!session('student.id'))
            {echo json_encode(array());exit;}
            $class_model = M('biz_class');
                $class_result=$class_model->join('biz_class_student on biz_class_student.class_id=biz_class.id')->where("grade_id=".$id." and biz_class_student.status=2 and biz_class_student.student_id=".session('student.id'))
                        ->field('biz_class.id,biz_class.name')
                        ->group("biz_class.name")
                        ->select();
            echo json_encode($class_result);
        }
    }

    //根据年级获得学科
    public function getClassCourse(){
        $id=intval(I('id'));
        if(!$id || !session('student.id')){
            echo json_encode(array());
        }else{
            $class_model = M('biz_class');
            $course_result=$class_model->join('biz_class_student on biz_class_student.class_id=biz_class.id')
                        ->join('auth_teacher_second on auth_teacher_second.teacher_id=biz_class.class_teacher_id')
                        ->join('dict_course on dict_course.id=auth_teacher_second.course_id')
                        ->where("biz_class.grade_id=".$id." and biz_class_student.status=2 and biz_class_student.student_id=".session('student.id'))
                        ->field('dict_course.id,dict_course.course_name')
                        ->group("dict_course.id")
                        ->select();
            echo json_encode($course_result);
        }
    }

    //错误集列表
    public function wrongHomeworkList(){
        if (!session('?student')) redirect(U('Index/index'));

        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Student/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '错题集');
        $this->assign('navicon', 'zuoyexitong');

        $where['score.student_id'] = session('student.id');

        $mca = I('mca');
        if ( $mca == 'action' ) {
            $this->assign('kw',1);
        }

        if(I('grade')>0){
            $where['dict_grade.id']=intval(I('grade'));
        }
        if(I('class')>0){
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
        $where['biz_homework.homework_status'] = 1;

        $Model = M('biz_homework');
        $count = $Model
            ->join('biz_class on biz_class.id=biz_homework.class_id')
            ->join('biz_class_student on biz_class_student.class_id=biz_class.id')
            ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
            ->join('dict_course on dict_course.id=biz_homework.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
            ->join('biz_homework_score_details score on score.homework_id=biz_homework.id and score.flag!=1')
            ->field('biz_homework.id')
            ->where($where)
            ->group("biz_homework.id")
            ->select();
        $count=count($count);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show=$Page->show();
        $this->assign('page', $show);

        $homeworks = $Model
            ->join('biz_class on biz_class.id=biz_homework.class_id')
            ->join('biz_class_student on biz_class_student.class_id=biz_class.id')
            ->join('dict_grade on dict_grade.id=biz_homework.grade_id')
            ->join('dict_course on dict_course.id=biz_homework.course_id')
            ->join('biz_textbook on biz_textbook.id=biz_homework.textbook_id')
            ->join('biz_homework_score_details score on score.homework_id=biz_homework.id and score.flag!=1')
            ->field('biz_homework.*,biz_class.name as class_name,dict_course.course_name,biz_textbook.name as textbook,dict_grade.grade')
            ->where($where)
            ->group("biz_homework.id")
            ->order('biz_homework.create_at desc')
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


        $grade_model=M('dict_grade');
        $grade_result=$grade_model->where("biz_class_student.student_id=".session('student.id'))->join("biz_class on biz_class.grade_id=dict_grade.id")
                ->join("biz_class_student on biz_class_student.class_id=biz_class.id")->field('dict_grade.id,dict_grade.grade')->group("dict_grade.id")->select();
        //年级不为空,求出班级和学科
        if(!empty($where['dict_grade.id'])){
            $class_model = M('biz_class');
            $class_result=$class_model->join('biz_class_student on biz_class_student.class_id=biz_class.id')->where("grade_id=".$where['dict_grade.id']." and biz_class_student.status=2 and biz_class_student.student_id=".session('student.id'))
                        ->field('biz_class.id,biz_class.name')
                        ->group("biz_class.name")
                        ->select();

            $course_result=$class_model->join('biz_class_student on biz_class_student.class_id=biz_class.id')
                        ->join('auth_teacher_second on auth_teacher_second.teacher_id=biz_class.class_teacher_id')
                        ->join('dict_course on dict_course.id=auth_teacher_second.course_id')
                        ->where("biz_class.grade_id=".$where['dict_grade.id']." and biz_class_student.status=2 and biz_class_student.student_id=".session('student.id'))
                        ->field('dict_course.id,dict_course.course_name')
                        ->group("dict_course.id")
                        ->select();
        }

        //年级和学科不为空,求出教材分册
        if(!empty($where['dict_grade.id']) && !empty($where['dict_course.id'])){
            $course_model = M('biz_textbook');
            $textbook_result=$course_model->where("grade_id=".$where['dict_grade.id']." and course_id=".$where['dict_course.id'])
                ->field('id,name')->select();
        }
        /*$Model = M('dict_course');
        $courses = $Model->where("student.id=".session('student.id')." and biz_homework.homework_status=1")->field("dict_course.id,dict_course.course_name")
                    ->join("biz_homework on biz_homework.course_id=dict_course.id")
                    ->join('biz_class on biz_class.id=biz_homework.class_id')
                    ->join('biz_class_student class_contact on class_contact.class_id=biz_class.id')
                    ->join('auth_student student on student.id=class_contact.student_id')
                    ->order('sort_order asc')->group("dict_course.id")->select();
        $this->assign('course_list', $courses);

        $class_model = M('biz_class');
        $class_result=$class_model->where("student.id=".session('student.id')." and biz_homework.homework_status=1")->field('biz_class.id,biz_class.name,student.id student_id')
                                ->join('biz_class_student class_contact on class_contact.class_id=biz_class.id')
                                ->join('auth_student student on student.id=class_contact.student_id')
                                ->join('biz_homework on biz_homework.class_id=biz_class.id')->group("biz_class.name")->select();
        $this->assign('class_list', $class_result);

        $biz_textbook_mode=M('biz_textbook');
        $textbook_result=$biz_textbook_mode->where("student.id=".session('student.id'))->field('biz_textbook.id,biz_textbook.name')
                        ->join('biz_homework on biz_homework.textbook_id=biz_textbook.id')
                        ->join('biz_class on biz_class.id=biz_homework.class_id')
                        ->join('biz_class_student class_contact on class_contact.class_id=biz_class.id')
                        ->join('auth_student student on student.id=class_contact.student_id')
                        ->group("biz_textbook.id")->select();
        $this->assign('textbook_list', $textbook_result);
        */
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

        $this->display();
    }

    //错误集
    public function wrongHomework(){
        if (!session('?student')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '错题集');
        $this->assign('navicon', 'zuoyexitong');

        /**/
        $homework_id=  intval(I('id'));
        if(!$homework_id){
            redirect(U('Index/systemError'));
        }

        $studentId = session('student.id');
        $where['biz_homework_score_details.student_id'] = $studentId;
        $where['biz_homework.id'] = $homework_id;
        $where['biz_homework.homework_status'] = 1;
        $where['_string']='biz_homework_score_details.flag!=1';

        $Model = M('biz_homework_score_details');
        $result = $Model
            ->join("biz_exercise_library on biz_exercise_library.id=biz_homework_score_details.question_org_id")
            ->join("biz_homework on biz_homework.id=biz_homework_score_details.homework_id")
            ->field("distinct biz_homework_score_details.*,biz_exercise_library.questions,biz_exercise_library.answer,biz_exercise_library.chapter_id,biz_homework.homework_name as homework,biz_homework.create_at")
            ->where($where)
            ->order('biz_homework.create_at desc')
            ->select();

        $this->assign('homework_id', $homework_id);
        $this->assign('list', $result);

        $this->display();
    }



    //作业完成情况
    public function homeworkCompleteDetails()
    {
        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '作业完成情况');
        $this->assign('navicon', 'zuoyexitong');

        //$homeworkId = $_GET['homeworkId'];
        $homeworkId = getParameter('homeworkId', 'int',false);
        $studentId = session('student.id');
        if(empty($studentId))
            exit;
        $this->assign('studentId', $studentId);
        if (empty($studentId)) {
            //$studentId = $_GET['studentId'];
            $studentId = getParameter('studentId', 'int',false);
        }
        if(empty($homeworkId)){
            redirect(U('Index/systemError'));
        }

        $gotoback = $_GET['gotoback'];
        $this->assign('gotoback', $gotoback);

        $this->assign('homeworkId', $homeworkId);

        //获取此次作业对象信息
        $Model = M('biz_homework');
        $currentHomework = $Model
            ->where("id=$homeworkId and homework_status=1")
            ->find();
        if(empty($currentHomework)){
            redirect(U('Index/systemError'));
        }
        $this->assign('currentHomework', $currentHomework);

        $ExerciseModel = M('biz_exercise_library_chapter');
        $chapter = $ExerciseModel
            ->where("id=" . $currentHomework['exercise_chapter_id'])
            ->find();
        $this->assign('chapter', $chapter);

        //判断是否存在作业了
        $Model = M('biz_homework_student_details');
        $result = $Model
            ->where("student_id=$studentId and homework_id=$homeworkId")
            ->find();

        $this->assign('homework', $result);

        $where['homework_id']=$homeworkId;
        $homework_exercise_model=M('biz_homework_exercise');
        $chapter_result=$homework_exercise_model->where($where)
                        ->join('biz_exercise_library_chapter chapter on chapter.id=biz_homework_exercise.chapter_id')
                        ->group('chapter.id')
                        ->field('chapter.chapter,chapter.festival')
                        ->select();
        $this->assign('chapter_data', $chapter_result);

        //获取学生得分细节
        /*$Model = M('biz_homework_score_details');
        $scoreDetails = $Model->where("homework_id=$homeworkId and student_id=$studentId")->find();
        $this->assign('scoreDetails', $scoreDetails);
        */
        $this->display();
    }

    //获取作业答案
    public function getHomeworkAnswers()
    {
        //$id = $_GET['id'];
        $id = getParameter('id', 'int',false,2);
        $Model = M('biz_homework_student_details');
        $result = $Model
            ->where("id=$id")
            ->find();

        $data = json_decode($result['answers']);
        $this->ajaxReturn($data);
    }

    //做作业
    public function doHomework()
    {
        if (!session('?student')) redirect(U('Index/index'));


        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '做作业');
        $this->assign('navicon', 'zuoyexitong');

        //$homeworkId = $_GET['homeworkId'];
        $homeworkId = getParameter('homeworkId', 'int',false,2);
        $studentId = session('student.id');

        $this->assign('homeworkId', $homeworkId);
        $this->assign( 'is_flag', I('is_flag') );

        //获取此次作业对象信息
        $Model = M('biz_homework');
        $currentHomework = $Model->where("id=$homeworkId and homework_status=1")->find();
        $this->assign('currentHomework', $currentHomework);

        $result=array();
        $chapter_result=array();
        if(!empty($currentHomework)){
            $ExerciseModel = M('biz_exercise_library_chapter');
            $chapter = $ExerciseModel->where("id=" . $currentHomework['exercise_chapter_id'])->find();
            $this->assign('chapter', $chapter);

            //判断是否存在作业了
            $Model = M('biz_homework_student_details');
            $result = $Model
                ->where("student_id=$studentId and homework_id=$homeworkId")
                ->find();

            $where['homework_id']=$homeworkId;
            $homework_exercise_model=M('biz_homework_exercise');
            $chapter_result=$homework_exercise_model->where($where)
                            ->join('biz_exercise_library_chapter chapter on chapter.id=biz_homework_exercise.chapter_id')
                            ->group('chapter.id')
                            ->field('chapter.chapter,chapter.festival')
                            ->select();


        }
        $this->assign('homework', $result);
        $this->assign('chapter_data', $chapter_result);

        $this->display();
    }

    public function submitHomework()
    {
        if (!session('?student')) redirect(U('Index/index'));
        /*
        $data['homework_id'] = $_POST['homework_id'];
        $data['answers'] = $_POST['answers'];
        $data['duration'] = $_POST['duration'];
        */
        $data['homework_id'] = getParameter('homework_id', 'int',false,2);
        $data['answers'] = $_POST['answers'];
        $data['duration'] = getParameter('duration', 'int',false,2);

        $bhmap['id'] = $data['homework_id'];
        $is_delete_class = M('biz_homework')->where( $bhmap )->find();

        if (!empty( $is_delete_class )) {
            $classinfomap['id'] = $is_delete_class['class_id'];
            $classinfo = M('biz_class')->where( $classinfomap )->find();
            if ( $classinfo['is_delete'] != 0) {
                $this->error('该班级已经被删除,无法提交作业');
                exit;
            }
        }


        $data['teacher_id'] = 0;
        $data['points'] = 0;
        $data['create_at'] = time();
        $data['student_id'] = session('student.id');

        if(D('Biz_homework_student_details')->isHomeworkSubmitted($data['student_id'],$data['homework_id'])) //学生已经提交了作业
        {
            $this->error('您的作业已经提交,无需重复提交');
            exit;
        }

        $ResourceModel = M('biz_homework_student_details');
        $ResourceModel->add($data);

        //作业数量+1
        $HomeworkModel = M('biz_homework');
        $HomeworkModel->where("id=" . $_POST['homework_id'])->setInc("completed_number", 1);

        //手机推送给家长
//        $homework_result=$HomeworkModel->where('id='.$data['homework_id'])->field('id,homework_name')->find();
//        $homework_name=$homework_result['homework_name'];
//
//        $parent_id=session('student.parent_id');
//        $student_name=session('student.student_name');
//        $parameter_arr=array(
//            'msg'=>array($student_name,$homework_name),
//            'url'=>array(
//                'type'=>0,
//                'data'=>array()
//            )
//        );
//        A('Home/Message')->addPushUserMessage('HOMEWORK_SUBMIT_CHILD',4,$parent_id,$parameter_arr);

        $this->redirect("Student/homework");
    }

    //小黑板
    public function blackboard()
    {
        if (!session('?student')) redirect(U('Index/index'));

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Student/index1?auth_error=1'));
        }


        $is_board = session('student.is_board');
        if ( $is_board == 1) {
            $student_id = session('student.id');
            D('Biz_classList')->isFirstLoginStudentBoard( $student_id );
            $this->redirect('Student/blackboradFunctionGuidancecopy');
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '小黑板');
        $this->assign('subnav', '消息列表');
        $this->assign('navicon', 'xiaoheiban');

        $filter['keyword'] = $_REQUEST['keyword'];
        if ($filter['keyword']!=null){
            $this->assign('kw',1);
        }
        $filter['flag'] = $_REQUEST['flag'];
        $this->assign('flag', $filter['flag']);

        if (!empty($filter['keyword'])) $check['biz_blackboard.message_title'] = array('like', '%' . $filter['keyword'] . '%');
        if ( !empty($filter['flag']) && $filter['flag']==1 ) $check['biz_isread_blackboard.id'] = array('EXP','IS NULL');

        $check['biz_class_student.status']=2;
        $check['biz_class_student.student_id']=session('student.id');
        $check['biz_class.is_delete']=0;

        $Model = M('biz_blackboard');
        $count = $Model
            ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
            ->join('biz_class on biz_class.id=boardandclass.class_id')
            ->join('biz_class_student on biz_class.id=biz_class_student.class_id')
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->join("biz_isread_blackboard on biz_class_student.student_id=biz_isread_blackboard.user_id and biz_isread_blackboard.b_id=biz_blackboard.id and biz_isread_blackboard.class_id=biz_class.id and role_id=3",'left')
            ->where($check)
            ->count('biz_blackboard.id');
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        $result = $Model
            ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
            ->join('biz_class on biz_class.id=boardandclass.class_id')
            ->join('biz_class_student on biz_class.id=biz_class_student.class_id')
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->join("biz_isread_blackboard on biz_class_student.student_id=biz_isread_blackboard.user_id and biz_isread_blackboard.b_id=biz_blackboard.id and biz_isread_blackboard.class_id=biz_class.id and role_id=3",'left')
            ->where($check)
            ->order('biz_blackboard.create_at desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->field('biz_blackboard.id,boardandclass.class_id,biz_blackboard.message_title,biz_blackboard.create_at,'
                    . 'biz_blackboard.publisher_id,biz_blackboard.publisher,biz_blackboard.status,biz_class.name class_name,dict_grade.grade,biz_isread_blackboard.id is_read')
            ->select();


        foreach($result as $k => $v ) {
            $toumap['id'] = $v['publisher_id'];
            $tou = M('auth_teacher')->where( $toumap )->find();
            $result[$k]['touimg'] = $tou['avatar'];
            //求看过学生的数量
           /* $data=$Model->where('biz_blackboard.id='.$v['id'])->join('boardandclass on boardandclass.b_id=biz_blackboard.id')->join('biz_class on biz_class.id=boardandclass.class_id')->join('biz_class_student on biz_class.id=biz_class_student.class_id and biz_class_student.status=2')
                    ->join('biz_isread_blackboard on biz_isread_blackboard.user_id = biz_class_student.student_id and biz_isread_blackboard.role_id=3 and biz_isread_blackboard.b_id=biz_blackboard.id')
                    ->field('biz_blackboard.id,biz_class_student.student_id')->select(); */

            $data_count=$Model->where('biz_blackboard.id='.$v['id'])->join('boardandclass on boardandclass.b_id=biz_blackboard.id')->join('biz_class on biz_class.id=boardandclass.class_id')->join('biz_class_student on biz_class.id=biz_class_student.class_id and biz_class_student.status=2')
                ->join('biz_isread_blackboard on biz_isread_blackboard.user_id = biz_class_student.student_id and biz_isread_blackboard.role_id=3 and biz_isread_blackboard.b_id=biz_blackboard.id')
                ->field('biz_blackboard.id,biz_class_student.student_id')->count();

            $result[$k]['read_person_number'] = $data_count;
        }

        unset($check['biz_blackboard.message_title']);
        $check['biz_isread_blackboard.id'] = array('EXP','IS NULL'); //未读的总数
        $weiducount = $Model
            ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
            ->join('biz_class on biz_class.id=boardandclass.class_id')
            ->join('biz_class_student on biz_class.id=biz_class_student.class_id')
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->join("biz_isread_blackboard on biz_class_student.student_id=biz_isread_blackboard.user_id and biz_isread_blackboard.b_id=biz_blackboard.id and biz_isread_blackboard.class_id=biz_class.id and role_id=3",'left')
            ->where($check)
            ->count();
        $this->assign('count_isread', $weiducount);


        $this->assign('keyword', $filter['keyword']);
        $this->assign('list', $result);
        $this->assign('page', $show);

        $this->display_nocache();
    }


    //小黑板引导
    public function blackboradFunctionGuidancecopy() {
        session('student.is_board', 2);
        $this->display();
    }

    //小黑板消息详情
    public function blackboardMessageDetails()
    {
        if (!session('?student')) redirect(U('Index/index'));

        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Student/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '小黑板');
        $this->assign('navicon', 'xiaoheiban');


        //$id = $_GET['id'];
        $id = getParameter('id', 'int',false,2);
        $class_id = getParameter('class_id', 'int',false,2);
        $read_person_number = getParameter('read_person_number', 'int',false,2);

        $Model = M('biz_blackboard');

        $result = $Model
            ->join("boardandclass on boardandclass.b_id=biz_blackboard.id AND boardandclass.class_id = $class_id")
            ->join('biz_class on biz_class.id=boardandclass.class_id')
            ->field('biz_blackboard.*,biz_class.name as class_name')
            ->where("biz_blackboard.id=$id ")
            ->find();

        if(empty($result)){
            redirect(U('Index/systemError'));
        }

        $toumap['id'] = $result['publisher_id'];
        $tou = M('auth_teacher')->where( $toumap )->find();
        $result['touimg'] = $tou['avatar'];
        $result['sex'] = $tou['sex'];

        $add_data['user_id']=session('student.id');
        $add_data['b_id']=$result['id'];
        $add_data['role_id']=3;
        $add_data['class_id']=$class_id;
        $model=M('biz_isread_blackboard');
        //判断是否存在,不存在则入库
        $browse_result=$model->where('role_id=3'.' and user_id='.session('student.id').' and b_id='.$result['id'].' and class_id='.$class_id)->field('id')->find();
        if(empty($browse_result)){
            $model->add($add_data);
        }


        $this->assign('data', $result);

        $this->assign('read_person_number', $read_person_number);

        $Model->where("id=$id")->setInc('view_count', 1);

        $this->display();
    }

      //判断是否有权限
    public function isAuth( $c_a ) {

        $is_auth = in_array($c_a, session('auth_student'));

        if ( $is_auth ) {
            return true;
        } else {
            return false;
        }
    }
    //京版概览
    function jboverview()
    {
        if (!session('?student')) redirect(U('Index/index'));

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);

        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Student/index1?auth_error=1'));
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

        //$this->assign('appid', C('NOBOOK_CONFIG.appid'));
        //$this->assign('appkey', C('NOBOOK_CONFIG.appkey'));
        $this->assign('list', $data);
        $this->display();
    }


    //物理实验
    public function bjResourceToolsPhysics(){
        if (!session('?student')) {
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
        if (!session('?student')) redirect(U('Index/index'));

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Student/index1?auth_error=1'));
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
        $filter['course_id'] =getParameter('course', 'int',false,2);
        $filter['grade_code'] =getParameter('grade', 'int',false,2);
        //$filter['textbook_id'] = $_REQUEST['textbook_id'];
        $filter['school_term'] =getParameter('textbook', 'int',false,2);
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
            if(empty($check['_string'])){
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
        // C('PAGE_SIZE_FRONT')
        /*foreach ($filter as $key => $val) {
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


        $this->display_nocache();
    }

    //京版资源详情
    public function bjResourceDetails($id,$type=2)
    {
        $id=intval($id);
        if (!session('?student')) redirect(U('Index/index'));


        if ($type != 1) {
            $isAuth = $this->isAuth($this->c_a);
            if (!$isAuth) { //如果访问的模块没有权限
                redirect(U('Student/index1?auth_error=1'));
            }
        }

        $this->assign('module', '教学+');
        $this->assign('nav', '京版资源');
        $this->assign('navicon', 'jingbanziyuan');
        if(empty($id))
          $id = getParameter('id', 'int',false,2);

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
        $zanData['role'] = 1;
        $zanData['user_id'] = session('student.id');
        $existedZan = $ZanModel->where($zanData)->find();
        $existedZan = empty($existedZan) ? 'no' : 'yes';

        $this->assign('existedZan', $existedZan);

        $FavorModel = M('biz_bj_resource_collect');
        $favorData['resource_id'] = $id;
        $favorData['role'] = 1;
        $favorData['user_id'] = session('student.id');
        $existedFavor = $FavorModel->where($favorData)->find();
        $existedFavor = empty($existedFavor) ? 'no' : 'yes';
        $this->assign('existedFavor', $existedFavor);
        $this->assign('REMOTE_ADDR',C('REMOTE_ADDR'));
        $this->display();

    }

    //赞一个京版资源
    public function zanBjResource()
    {
       $studentId = session('student.id');
       if(empty($studentId))
        {
        //$studentId = $_GET['user_id'];
          $studentId = getParameter('user_id', 'int',false,2);
        if(empty($studentId))
         {
         redirect(U('Index/index'));
         }
        }

        //$id = $_GET['id'];
        $id = getParameter('id', 'int',true,2);

        $ZanModel = M('biz_bj_resource_zan');
        $zanData['role'] = 1;
        $zanData['resource_id'] = $id;
        $zanData['user_id'] = $studentId;

        $existed = $ZanModel->where($zanData)->find();
        if (empty($existed)) {
            $zanData['create_at'] = time();
            $res = M('auth_student')->where("id=$studentId")->find();
            $zanData['user_name'] = $res['student_name'];
            $ZanModel->add($zanData);

            $Model = M('biz_bj_resources');
            $Model->where("id=$id")->setInc('zan_count', 1);
            $this->ajaxReturn("success");
        } else {
            $ZanModel->where("resource_id=$id and role=1 and user_id=$studentId")->delete();
            $Model = M('biz_bj_resources');
            $Model->where("id=$id")->setDec('zan_count', 1);
            $this->ajaxReturn("已经取消点赞");
        }
    }

    //收藏一个京版资源
    public function favorBjResource()
    {
        $studentId = session('student.id');
        if(empty($studentId))
         {
         //$studentId = $_GET['user_id'];
           $studentId = getParameter('user_id', 'int',false,2);
         if(empty($studentId))
          {
          redirect(U('Index/index'));
          }
         }

        //$id = $_GET['id'];
         $id = getParameter('id', 'int',false,2);

        $FavorModel = M('biz_bj_resource_collect');
        $favorData['resource_id'] = $id;
        $favorData['role'] = 1;
        $favorData['user_id'] = $studentId;

        $existed = $FavorModel->where($favorData)->find();
        if (empty($existed)) {
            $favorData['create_at'] = time();
            $res = M('auth_student')->where("id=$studentId")->find();
            $favorData['user_name'] = $res['student_name'];
            $FavorModel->add($favorData);

            $Model = M('biz_bj_resources');
            $Model->where("id=$id")->setInc('favorite_count', 1);
            $this->ajaxReturn("success");
        } else {
            $FavorModel->where("resource_id=$id and role=1 and user_id=$studentId")->delete();
            $Model = M('biz_bj_resources');
            $Model->where("id=$id")->setDec('favorite_count', 1);
            $this->ajaxReturn("已经取消收藏");
        }
    }

    //教师详情
    public function teacherDetails()
    {
        if (!session('?student')) redirect(U('Index/index'));

        //$id = $_GET['id'];
        $id=getParameter('id', 'int',false,2);

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
            if (!$this->check_verify_code(false, $verify_code,STUDENT_IMAGE_PRODUCE_ID)) {
                $this->showjson(-5, '图形验证码错误');
            }
            $sendSmsFunction='templateSMS';
        }else{
            $sendSmsFunction='newTemplateSMS';
        }


        $smsGap = 60; //发送间隔60S
        //$telephone = $_REQUEST['telephone'];
        $telephone = getParameter('telephone','str',false);
        if (empty($telephone) || strlen($telephone) != 11) {
            $this->showjson(-1, '手机号码格式错误');
        }


        $randcode = rand(10000, 99999);
        $smsapi = new SMS();

        $ret = $smsapi->$sendSmsFunction($telephone, '注册学生,' . $randcode, 'json');
        if ($ret['status'] == false || $ret < 0) {
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


    //生成验证码
    function produceVerifyCode(){
        $config = array(
             'fontSize' => 20,
             'length' => 4, // 验证码位数
             'imageH' => 40);
         $verify = new Verify($config);
         $verify->entry(STUDENT_IMAGE_PRODUCE_ID);
    }

    function check_verify(){
        //$code=getParameter('code','str',false);
        $this->check_verify_code(1,$code,STUDENT_IMAGE_PRODUCE_ID);
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
            $check['student_name'] = $_POST['name'];
            $check['parent_tel'] = remove_xss($_POST['telephone']);

            $data['student_name'] = $_POST['name'];
            $data['sex'] = $_POST['sex'];
            $data['school_id'] = $_POST['school_id'];
            $data['birth_date'] = strtotime($_POST['birthday']);
            $data['email'] = $_POST['email'];
            $data['student_id'] = $_POST['stuID'];
            $data['id_card'] = $_POST['identity'];
            $data['parent_tel'] = remove_xss($_POST['telephone']);
            $data['password'] = sha1($_POST['password']);
            */
            $check['student_name'] = getParameter('name', 'str',false);
            $data['sex'] = getParameter('sex', 'str',false);
            $data['school_id'] = getParameter('school_id', 'int',false,2);
            $data['birth_date'] = strtotime(getParameter('birthday', 'str',false));
            $data['email'] = getParameter('email', 'str',false);
            $data['student_id'] = getParameter('stuID', 'str',false,2);
            $data['id_card'] = getParameter('identity', 'str',false);
            $check['parent_tel'] = getParameter('telephone', 'str',false);

            $phoneCode['valid_code'] = getParameter('valid_code', 'int',false);
            $data['student_name'] = $check['student_name'];
            $data['school_id'] = getParameter('school_id', 'int',false,2);
            $data['parent_tel'] = $check['parent_tel'];
            $data['password'] = sha1(getParameter('password', 'str',false));

            $data['create_at'] = time();
            $data['update_at'] = time();



            if(session('from_param')){
                $data['source'] = session('from_param');
            }

            if (empty($_POST['telephone']) || empty($_POST['password']) || empty($_POST['name'])) {
                $this->showMessage(500,'请填写完整信息',array());
            }

            if ($_POST['confirm_password'] != $_POST['password']) {
                $this->showMessage(500,'密码与确认密码不一致',array());
            }

            $shcool_model=M('dict_schoollist');
            $school_info=$shcool_model->where('id='.$data['school_id'])->find();
            if(empty($school_info)){
                $this->showMessage(500,'学校信息不存在',array());
            }


            //TODO:add guanlian info to parents
            $parenttel= $data['parent_tel'];
            $parentId = M('auth_parent')->where("telephone='$parenttel'")->find();
            /*if(empty($parentId)) //parent is not exist
            {
                $parentId['id'] = 0;
                $parentId['parent_name'] = '';
            }*/

            $studentModel = M('auth_student');

//            if($data['student_id']) {
//                $student_info = D('Auth_student')->getStudentBySchoolIdStuId($data['school_id'] ,$data['student_id']);
//                if (!empty($student_info)) {
//                    $this->showMessage(500,'该学校中该学号已存在',array());
//                }
//            }

            $ref = session('rid');
            if (!empty($ref)) {
                $refmap['a_id'] = session('rid');
                $this->addActivityUser( $refmap );
            }

            $result = $studentModel->where($check)->find();//判断是否已经注册
            $studentModel->startTrans();

            if (empty($result)) {
                $redis_obj=new REDIS();
                $redis=$redis_obj->init_redis();
                $redis_key='sms_'.$parenttel;
                $code['code']=$redis->get($redis_key);

                if($code['code']==$phoneCode['valid_code'] && $phoneCode['valid_code']){

                    $id = $studentModel->add($data);
                    if($vip_config && $vip_config<=3){
                        give_new_vip_operation(3, $vip_config,$id,$data['school_id']);
                    }

                    if(!empty($parentId)){
                        $add_data['parent_id']=$parentId['id'];
                        $add_data['parent_name']=$parentId['parent_name'];
                        if($studentModel->where("id=".$id)->save($add_data)==false){
                            $studentModel->rollback();
                            $this->error('非法访问');
                        }

                        $joinData['student_id'] = $id;
                        $joinData['parent_id'] = $parentId['id'];
                        $parentStuModel = M('auth_parent_student');
                        $res = $parentStuModel->where($joinData)->find();
                        if(empty($res)){
                            $parentStuModel->add($joinData);
                        }
                       D('Auth_student')->updateStudentParentInfo($joinData['parent_id'], $joinData['student_id']);
                    }

                     $studentModel->commit();
                     $smsapi = new SMS();
                     $smsapi->noticeParentAddStudent($parenttel,$data['student_name']);
                     $Message = new \Home\Controller\MessageController();
                     $parameters = array( 'msg' => array() , 'url' => array( 'type' => 0));
                     $Message->addPushUserMessage('REG_SUCCESS',ROLE_STUDENT,$id,$parameters);
                    //家长推送
                    $parameters = array( 'msg' => array($data['student_name']) , 'url' => array( 'type' => 0));
                    $Message->addPushUserMessage('CHILD_REG_SUCCESS',ROLE_PARENT,$parentId['id'],$parameters);
                    $share_par=getParameter('url','str',false);
                    if(!empty($share_par)){
                        $share_par=base64_decode($share_par);

                        $Model = M('auth_student');
                        $result = $Model->where($check)->find();
                        session('auth_teacher', null);
                        session('auth_parent', null);
                        session('teacher', null);
                        session('parent', null);

                        $theme = "1";
                        session('student', $result);
                        session('theme', $theme);

                        $btntheme = "primary";
                        if ($theme == 2) $btntheme = "danger";
                        if ($theme == 3) $btntheme = "dark";
                        session('btntheme', $btntheme);

                        $auth_type_use = D('Account_auths');
                        $auth_list = $auth_type_use->getAuthAndVipauth(session('student.id'),ROLE_STUDENT);
                        session('auth_student', $auth_list);

                        $isVipInfo = $auth_type_use->isVipInfo(session('student.id'),ROLE_STUDENT);
                        session('student_vip',$isVipInfo);

                        $client_ip=get_client_ip();
                        $current_login_address=getIPLoc_sina($client_ip);
                        $Model->where('id=' . $result['id'])->save(array('login_address' => $current_login_address));

                        $this->showMessage(201,'success',array('telephone'=>$check['parent_tel'],'url'=>$share_par));
                    }else{

                        $this->showMessage(200,'success',array('telephone'=>$check['parent_tel']));
                    }
                }else{
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

    //我的班级
    public function classList()
    {
        if (!session('?student')) redirect(U('Index/index'));

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Student/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '我的班级');
        $this->assign('subnav', '');
        $this->assign('navicon', 'wodebanji');

        $Model = M('biz_class');
        $result = $Model
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('biz_class_student on biz_class_student.class_id=biz_class.id')
            ->field('biz_class.*,dict_schoollist.school_name,dict_grade.grade,biz_class_student.status as instatus')
            ->where("biz_class_student.student_id=" . session('student.id'))
            ->order('biz_class.name asc')
            ->select();

        $this->assign('list', $result);

        $this->display();
    }


    //根据教师编号查询班级
    public function getClassesByTeacherTelephone()
    {
        if (!session('?student')) redirect(U('Index/index'));

        //$telephone = $_GET['telephone'];
        $telephone = getParameter('telephone', 'str');

        $Model = M('biz_class');
        $class = $Model
            ->join('dict_grade on dict_grade.id = biz_class.grade_id')
            ->join('auth_teacher on biz_class.class_teacher_id=auth_teacher.id')
            ->field('biz_class.*,dict_grade.grade')
            ->where("auth_teacher.telephone='$telephone'")
            ->order("create_at desc")
            ->select();
        foreach($class as $k=>$v) {
            $map['class_id'] = $v['id'];
            $map['student_id'] = session('student.id');
            $list = M('biz_class_student')->where($map)->find();
            $class[$k]['status'] = $list['status'];
        }


        $this->ajaxReturn($class);
    }

    //申请加入班级
    public function applyIntoClass()
    {
        if (!session('?student')) redirect(U('Index/index'));

        //$classId = $_GET['id'];
        $classId = getParameter('id', 'int');
        $studentId = session('student.id');
        //TODO:forbid diffent school
        $stuInfo = M('auth_student')->where("id=$studentId")->find();
        $classInfo = M('biz_class')->where("id=$classId")->find();

        if($stuInfo['school_id'] != $classInfo['school_id'])
        {
        $this->ajaxReturn('您不在该班级所在的学校,无法进入班级');
        return;
        }
        $Model = M('biz_class_student');
        $class = $Model
            ->where("class_id=$classId and student_id=$studentId")
            ->find();
        if (!empty($class)) {
           if($class['status'] !=3)
            $this->ajaxReturn('已经加入过改班级，无需再加入');
           else
            {
            //only update status
            $data['status'] = 1;
            $Model->where("class_id=$classId and student_id=$studentId")->save($data);
            $this->ajaxReturn('success');
            }
        } else {
            $data['class_id'] = $classId;
            $data['student_id'] = $studentId;
            $data['create_at'] = time();
            $data['update_at'] = time();
            $data['status'] = 1;
            $Model->add($data);
            $this->ajaxReturn('success');
        }
    }

    //查看班级课表
    public function classTimetable()
    {
        if (!session('?student')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '班级管理');
        $this->assign('navicon', 'wodebanji');

        //$classId = $_GET['classId'];
        $classId = getParameter('classId', 'int',false);
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
       if (!session('?student')) redirect(U('Index/index'));
        if($_POST) {

            $studentId = session('student.id');
            $sc_id = session('student.school_id');
            $check['id'] = $studentId;
            $data['sex'] = getParameter('sex', 'str', false);

            if (!empty($_POST['school_id']))
                $data['school_id'] = getParameter('school_id', 'int', false, 2);
            $data['email'] = getParameter('email', 'str', false);
            $data['student_name'] = getParameter('name', 'str', false);




            if (!empty($_POST['password']))
                $data['password'] = sha1(getParameter('password', 'str', false));
            $data['student_id'] = $_POST['stuID'];

            $data['id_card'] = getParameter('identity', 'str', false);
            $data['birth_date'] = strtotime(getParameter('birthday', 'str', false));

            $data['update_at'] = time();

            if(!empty($data['school_id'])){
                if ($sc_id != $data['school_id']) {
                    $data['apply_school_status'] = 0;
                }
            }

            $studentModel = M('auth_student');
            $studentInfo = $studentModel->where($check)->find();
            $sutdent_name = $data['student_name'];
            $parent_tel = $studentInfo['parent_tel'];
            $student_result = $studentModel->where("id!=" . $studentId . " and student_name=" . "'$sutdent_name'" . " and parent_tel=" . "'$parent_tel'")->field('1')->find();
            if (!empty($student_result)) {
                $this->showMessage(500,'该家长下已经有相同姓名的学生');
            }
            $result = $studentModel->where($check)->save($data);
            $_SESSION['student']['student_name'] = $data['student_name'];

            $parameter_arr = array(
                'msg' => array(),
                'url' => array(
                    'type' => 0,
                    'data' => array()
                )
            );

            if (!empty($data['school_id'])) {
                $school_info = D('Dict_schoollist')->getSchoolInfo($data['school_id']);
                if ($school_info['is_create_administartor'] == 1) { //修改后的学校为认证的学校

                    $parameters = array(
                        'msg' => array(
                            session('student.student_name'),
                        ),
                        'url' => array('type' => 0)
                    );

                    A('Home/Message')->addPushUserMessage('XUEXIAO_ZHENG', 3, session('student.id'), $parameters);
                }
            }

            A('Home/Message')->addPushUserMessage('INFO_MODIFIED_BYSELF_STUDENT', 3, $studentId, $parameter_arr);

            $this->showMessage(200,'success');
        }
        else {
            $studentId = session('student.id');
            $StudentModel = M('auth_student');
            $result = $StudentModel->join('dict_schoollist on auth_student.school_id = dict_schoollist.id')->where("auth_student.id=$studentId")->find();
            $result['birth_date'] = date("Y-m-d", $result['birth_date']);
            $this->assign('data', $result);
            $this->display();
        }
    }
    /**
     * 更改学生个人资料
     */

    public function upstudentdata()
    {
        $check['id'] = I('request.id','','remove_xss');
        if(empty($check['id']))
        {
            $this->showjson(-100,'id为空',array());
        }
        $data['school_id'] = I('request.school_id','','remove_xss');
        $data['grade_id'] = I('request.grade_id','','remove_xss');
        $oldStudentInfo = D('Auth_student')->getStudentInfo($check['id']);
        if(!empty($oldStudentInfo['school_id']))
        {
            $data['student_name'] = I('request.name','','remove_xss');
            $data['sex'] = I('request.sex','','remove_xss');
            $data['email'] = I('request.email','','remove_xss');
            $data['student_id'] = I('request.stuID','','remove_xss');
            $data['id_card'] = I('request.identity','','remove_xss');
            $data['parent_tel'] = I('request.telephone','','remove_xss');
            $data['birth_date'] = I('request.birthday','','remove_xss');
        }

        foreach($data as $key=>$val)
        {
            switch($key)
            {
                case 'student_name':
                    if(empty($data[$key]))
                    {
                        $this->showjson(-101,"{$key}为空",array());
                    }
                    break;
                case 'parent_tel':
                    if(empty($data[$key]))
                    {
                        $this->showjson(-101,"{$key}为空",array());
                    }else{
                        $sutdent_name=$data['student_name'];
                        $parent_tel=$data['parent_tel'];
                        $student_model=M('auth_student');
                        $student_result=$student_model->where("id!=".$check['id']." and student_name="."'$sutdent_name'"." and parent_tel="."'$parent_tel'")->field('1')->find();
                        if(!empty($student_result)){
                            $this->showjson(-107,"该家长下有相同姓名的学生",array());
                        }
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
                case 'student_id':
//                    if(empty($data[$key]))
//                    {
//                        $this->showjson(-105,"{$key}为空",array());
//                    }
                    break;
                case 'school_id'://判断学校是否存在
                    $school = M('dict_schoollist');
                    $sinfo = $school->where(array('id'=>$data[$key]))->find();
                    if(empty($sinfo))
                    {
                        $this->showjson(-106,"学校不存在",array());
                    }
                    break;//
                case 'birth_date'://判断学校是否存在
                    $data[$key]=strtotime($data[$key]);
                    break;
                default:
                    break;
            }
        }

        $data['update_at'] = time();
        //若完善资料,则不屏蔽修改学校的功能

//        if(!empty($oldStudentInfo['school_id']))
//            unset($data['school_id']);

        if ($oldStudentInfo['school_id'] != $data['school_id']) //修改学校
        {
            //如果有校建班则返回错误
            $classList = D('Biz_class')->getStudentJoinedSchoolClassList($check['id']);
            if(!empty($classList))
            {
                $this->showjson(-105,"您有校建班未退出,无法更改学校",array());
            }
        }

        $studentModel = M('auth_student');
        $result = $studentModel->where($check)->save($data);

        $this->showjson(0,"更改成功",array());
    }

    //完善资料
    function perfectingformation(){
        if(!empty($_POST)){
            if (!session('?student')){
                $this->showjson(500,'请完成登录');
            }
            $id = session('student.id');
            $school_id=getParameter('school_id','int');

            $model=M('auth_student');
            $where['id']=$id;
            $data['school_id']=$school_id;
            if($model->where($where)->save($data)===false){
                $this->showjson(500,'数据修改失败');
            }else{
                $this->showJson(200);
            }

        }else{
            $this->showjson(500,'异常访问');
        }
    }


    /**
     * 更改学生个人资料
     */

    public function updata()
    {
        $this->upstudentdata();
    }

    public function redbjResourceList()
           {
             if (!session('?student')) redirect(U('Index/index'));
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
                     $check['biz_bj_resource_collect.user_id'] = session('student.id');
                     $check['biz_bj_resource_collect.role'] = 1;
                      if (!empty($filter['keyword'])) $check['biz_bj_resources.name'] = array('like', '%' . $filter['keyword'] . '%');
                     $count=  $Model->join('biz_bj_resource_collect on biz_bj_resource_collect.resource_id=biz_bj_resources.id')->where($check)->select();
                     $count=count($count);
                     $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));

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
                      $check['biz_resource_collect.user_id'] = session('student.id');
                      $check['biz_resource_collect.user_type'] = 1;
                      if (!empty($filter['keyword'])) $check['biz_resource.name'] = array('like', '%' . $filter['keyword'] . '%');
                      $count=  $Model ->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')->where($check)->select();
                      $count=count($count);
                      $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
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
                                ->join('(select id,name from biz_textbook) a on biz_resource.textbook_id=a.id','left')
                                ->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id')
                                ->field('biz_resource.*,a.name as textbook,biz_resource.teacher_name publisher_name')
                                ->where($check)
                                ->order("biz_resource." . $sort_string)
                                ->limit($Page->firstRow . ',' . $Page->listRows)
                                ->select();
                     for ($i = 0; $i < sizeof($result); $i++)
                       $result[$i]['category'] = 'teacher';
                    }
                    else if($searchCate == 'all')
                    {
                    $unionSql = " SELECT 'bj' as category,biz_bj_resources.file_path,'北京出版集团' publisher_name,biz_bj_resources.vid_image_path,biz_bj_resources.id,biz_bj_resources.type,biz_bj_resources.name,biz_bj_resources.create_at,biz_bj_resources.zan_count,biz_bj_resources.favorite_count,biz_bj_resources.follow_count,biz_textbook.name as textbook FROM biz_bj_resources left JOIN biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id
                    join (select resource_id from biz_bj_resource_collect where role = 1 and user_id =".session('student.id') .") a on a.resource_id=biz_bj_resources.id
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


                     $check['biz_resource_collect.user_id'] = session('student.id');
                     $check['biz_resource_collect.user_type'] = 1;
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
                     //$Page->parameter['keyword'] = urlencode($keyword);
                     $Page->parameter['course_id'] = $filter['course_id'];
                     $Page->parameter['grade_id'] = $filter['grade_id'];
                     $Page->parameter['textbook_id'] = $filter['school_term_id'];
                     $Page->parameter['sort_column'] = $filter['sort_column'];
                     $Page->parameter['type'] = $filter['type'];
                     //$Page->parameter['resource_cate'] = urlencode($searchCate);
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
    $this->resourceDetails($id,'myfavor',1);
    }
    public function mybjResourceDetails($cate,$id)
    {
     if($cate == 'bj')
      $this->bjResourceDetails($id,1);
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


    public function delClassData() {
        //$classId = $_GET['classId'];
        $classId = getParameter('classId', 'int',false);
        $stuid = session('student.id');

        $map['class_id'] = $classId;
        $map['student_id'] = $stuid;
        $id = M('biz_class_student')->where( $map )->delete();

        if ($id) {
            $this->ajaxReturn("success");
        } else {
            $this->ajaxReturn("error");
        }
    }
    public function myMessageDetails()
    {
        $messageId = getParameter('id', 'int');
        A('Home/Message')->messageDetails($messageId);

    }

    //我的笔记
    public function myNote() {

        $map['my_note.user_id'] = session('student.id');
        $keyword = I('keyword');
        $c_id = I('c_id');
        $this->keyword = $keyword;
        if ($keyword!=null || $c_id!=null) {
            $this->assign('kw',1);
        }


        if (!empty($keyword)) {
            $map['my_note.content|dict_course.course_name'] = array('like', '%' . $keyword . '%');
            $where['keyword'] = $keyword;
        }

        if (!empty($c_id)) {
            $map['my_note.course_id'] = $c_id;
            $where['c_id'] = $c_id;
        }

        $count = M('my_note')
            ->join('dict_course on dict_course.id=my_note.course_id')
            ->join('dict_grade on dict_grade.id=my_note.grade_id')
            ->join('biz_textbook on biz_textbook.id=my_note.chapter_id')
            ->field('my_note.id,dict_course.course_name as course,dict_grade.grade,biz_textbook.name as textbook,my_note.content')
            ->where($map)
            ->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));

        foreach ($where as $key => $val) {
            //$Page->parameter[$key] = urlencode($val);
            $Page->parameter[$key] = $val;
        }
        //dump($check);
        $show = $Page->show();

        $result = M('my_note')
            ->join('dict_course on dict_course.id=my_note.course_id')
            ->join('dict_grade on dict_grade.id=my_note.grade_id')
            ->join('biz_textbook on biz_textbook.id=my_note.chapter_id')
            ->field('my_note.id,dict_course.course_name as course,dict_grade.grade,biz_textbook.name as textbook,my_note.content')
            ->where($map)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $this->assign('list',$result);
        $this->assign('page', $show);

        //学科所有

        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        $this->assign('courses', $courses);

        //分册
        $this->display();
    }

    //删除我的笔记
    public function deleteMyNote(){
        $id  = $_GET['id'];
        $Model = M('my_note');
        $id  = $Model->where("id=$id")->delete();

        if ( $id ) {
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('error');
        }
    }
}
