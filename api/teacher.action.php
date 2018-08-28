<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';
include 'Common/ImageResize.php';

// 路由
$app->post('/login', 'login');//
$app->post('/my_students', 'getMyStudents');
$app->post('/good_teachers', 'getGoodTeachers');
$app->get('/good_teachers', 'getGoodTeachers');
$app->post('/details/:id', 'getDetails');
$app->get('/details/:id', 'getDetails');
//教师登录
function login()
{    
    $telephone = $_POST['telephone'];
    $password = sha1($_POST['pwd']);

    if (empty($telephone) || empty($password)) {
        renderJsonResponse(404, '账号不存在', array());
        return;
    }

    $db = new Database();
    $user = $db->fetch_single_row('auth_teacher', 'telephone', $telephone);
    /*if($user->lock == 1)
    {
      renderJsonResponse(404, '账号已被锁定', array());
      return;
    }

    if($user->lock == 0)
    {
        renderJsonResponse(404, '账号未通过审核', array());
        return;
    }

    if($user->lock == 3)
    {
        renderJsonResponse(404, '账号被拒绝', array());
        return;
    }*/

    if ($user && $user->password == $password) {
        $result = array(
            'id' => $user->id,
            'name' => $user->name,
            'telephone' => $user->telephone,
            'access_token' => $user->access_token,
            'avatar' => $user->avatar,
            'points' => $user->points,
            'brief_intro' => $user->brief_intro,
            'school_id' => $user->school_id
        );

        $school = $db->fetch_single_row('dict_schoollist', 'id', $result['school_id']);
        $result['school_name'] = isset($school->school_name)?$school->school_name:'';

        if (preg_match('/Resources/', $result['avatar'])){
            
            $result['avatar'] = OSS_URL.$result['avatar'];
        } else {
            $result['avatar'] = "http://".WEB_URL."/Uploads/Avatars/".$result['avatar'];
        }

        renderJsonResponse(200, '', $result);

    } else {
        renderJsonResponse(404, '密码错误', array());
    }

    $db = null;
}

function getDetails($id)
{
    $db = new Database();
    $user = $db->fetch_single_row('auth_teacher', 'id', $id);

    $result = array(
        'id' => $user->id,
        'name' => $user->name,
        'telephone' => $user->telephone,
        'access_token' => $user->access_token,
        'avatar' => $user->avatar,
        'points' => $user->points,
        'brief_intro' => $user->brief_intro,
        'school_id' => $user->school_id,
    );

    if(strpos($user->avatar,'.')===false)
    {
        $user->avatar.='.jpg';
    }

    if (preg_match('/Resources/', $user->avatar)){
            
        $result['img_url']            = OSS_URL.$user->avatar;
    } else {
        $result['img_url']            = "http://".WEB_URL."/Uploads/Avatars/".$user->avatar;
    }

    $school = $db->fetch_single_row('dict_schoollist', 'id', $result['school_id']);

    $result['school_name'] = $school->school_name;

    renderJsonResponse(200, '', $result);
}

//获取我的学生
function getMyStudents()
{
    $db = new Database();
    $qr = "select s.id,s.student_name,s.telephone,s.user_name,s.avatar,c.name as class_name from biz_class_student bcs inner join auth_student s on s.id=bcs.student_id inner join biz_class c on c.id=bcs.class_id  order by s.student_name asc";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

//获取教师风采的教师列表
function getGoodTeachers()
{
    $db = new Database();
    $pageIndex = $_REQUEST['pageIndex'];
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize = getPageSize();
    $startIndex = ($pageIndex - 1) * $pageSize;
    //$qr = "select auth_teacher.id,auth_teacher.name,auth_teacher.avatar,auth_teacher.email,auth_teacher.tags,auth_teacher.points,auth_teacher.brief_info,dict_school.school_name,auth_teacher.school_age, auth_teacher.tags,c.course_name from auth_teacher inner join dict_school on auth_teacher.school_id=dict_school.id inner join dict_course c on auth_teacher.course_id=c.id order by auth_teacher.points desc LIMIT $startIndex, $pageSize";
    $qr = "SELECT auth_teacher.*,dict_schoollist.school_name FROM `auth_teacher` INNER JOIN dict_schoollist on dict_schoollist.id=auth_teacher.school_id  WHERE (  auth_teacher.flag=1  ) ORDER BY points desc LIMIT $startIndex, $pageSize ";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);


    $list = array();
    foreach($result as $val)
    {
        //dict_course
        $course = $db->fetch_single_row('dict_course', 'id', $val->course_id);

        $course_name = empty($course) ? '':$course->course_name;

        $data=array();
        $data['id']         = $val->id;
        $data['name']       = $val->name;
        $data['avatar']         = $val->avatar;
        $data['email']         = $val->email;
        $data['tags']         = $val->tags;
        $data['points']         = $val->points;
        $data['brief_intro']         = $val->brief_intro;
        $data['school_name']         = $val->school_name;
        $data['school_age']         = $val->school_age;
        $data['course_name']         = $course_name;
        if(strpos($val->avatar,'.')===false)
        {
            $val->avatar.='.jpg';
        }

        if (preg_match('/Resources/', $val->avatar)){
            
            $data['img_url'] = OSS_URL.$val->avatar;
        } else {
            $data['img_url'] = "http://".WEB_URL."/Uploads/StudentAvatars/".$val->avatar;
        }

        //$data['img_url']            = "http://".WEB_URL."/Uploads/Avatars/".$val->avatar;

        $list[] = $data;
    }

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
    $db = null;

    renderJsonResponse(200, '', $list);
}


$app->post('/classrooms', 'getClassrooms');
//获取我的课堂
function getClassrooms()
{
    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;

    $db = new Database();
    $qr = "select cr.*,c.name as class_name,course.course_name,t.name as textbook,t.school_term from biz_classroom_information cr inner join biz_class c on cr.class_id=c.id inner join dict_course course on cr.course_id=course.id inner join biz_textbook t on cr.textbook_id=t.id where cr.teacher_id=$currentTeacherId order by cr.time desc";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

//教师注册
$app->post('/register', 'register');
$app->get('/register', 'register');
function register()
{ 
    
    
        $telephone = $_REQUEST['telephone'];
        $password = sha1($_REQUEST['pwd']);
        $confirmPassword = sha1($_REQUEST['confirm_pwd']);
        $verify_code = $_REQUEST['verify_code'];
    	 $role = 0;
    	if(isset($_REQUEST['role']))
         $role=$_REQUEST['role'];
        $name = $_REQUEST['name'];
        //$course_id = $_POST['course_id'];
        $school_id = $_REQUEST['school_id'];
        //$grade_id = $_POST['grade_id'];
        $course_grade = $_REQUEST['course_grade'];
        $brief_intro = $_REQUEST['brief_intro'];

        $wechat = $_REQUEST['wechat'];
        $qq = $_REQUEST['qq'];
        $email = $_REQUEST['email'];

        $isvalid = true;

        if (empty($telephone) || empty($password) || empty($name)) {
            $isvalid = false;
            renderJsonResponse(406, '手机号、密码和姓名不能为空', array());
            return;
        }

        if (empty($school_id) || empty($course_grade)) {
            $isvalid = false;
            renderJsonResponse(406, '学科、学校和年级不能为空', array());
            return;
        }
        if ($confirmPassword <> $password) {
            $isvalid = false;
            renderJsonResponse(406, '密码与确认密码不一致', array());
            return;
        }

        if (strlen($telephone) <> 11) {
            $isvalid = false;
            renderJsonResponse(403, '手机号格式不正确', array());
            return;
        }
        $db = new Database();
	$qr = "select * from dict_schoollist where id=" . $school_id ; 
        $sel = $db->fetch_custom($qr,array());
        $school_info = pushToArray($sel);   
        if(empty($school_info)){
            $isvalid = false;
            renderJsonResponse(403, '学校信息不存在', array());
            return;
        }

        //验证是否为正确的手机验证码
        
        /*
        $codeData = array('telephone' => $telephone, 'code' => $verify_code);
        $codeResult = $db->check_exist('misc_register_phone_validcode', $codeData);
        if ($codeResult <> true) {
            $isvalid = false;
            renderJsonResponse(500, '验证码错误', array());
            return;
        } else {
            $db->delete('misc_register_phone_validcode', 'telephone', $telephone);
        }*/
        $redis=new Redis();
        $redis->connect(REDIS_HOST,REDIS_PORT);  
        $redis->auth(REDIS_AUTH); 
        
        $redis_key='sms_'.$telephone;   
        $code=$redis->get($redis_key);  
        
        
        if($verify_code!=$code || !$verify_code){
            $isvalid = false;
            renderJsonResponse(500, '验证码错误', array());
            return;
        }
        

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
        $data = array(
            'telephone' => $telephone,
            'password' => $password,
            'create_at' => time(),
            'update_at' => time(),
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

        $teacher = getTeacherByTelephone($telephone);

        $teacherId = 0;

        if ($teacher) { //已经存在该电话号码
            $isvalid = false;
            renderJsonResponse(406, '该手机号已经注册', array());
            return;
        } else { // 不存在改号码
            // 入注册用户到数据库
            $redis->delete($redis_key); 
            
            if ($isvalid) {
                $teacherId = $db->insert('auth_teacher', $data);

                 //赠送30天vip 
                $vip_config=APP_REGISTER_GIVE_VIP_STATUS;
                if($vip_config && $vip_config<=3){
                    $vipdata = array(
                        'user_id' => $teacherId,
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
                        if ($school_info[0]->user_auth == 3 && time() >= $school_info[0]->auth_start_time && time() < $school_info[0]->auth_end_time) {
                            //学校的权限给予教师 
                            $vipdata['timetype']=$school_info[0]->timetype;
                            $vipdata['auth_id']=3;  
                            $vipdata['auth_end_time']=$school_info[0]->auth_end_time;

                        } else {
                            //普通权限
                            $vipdata['auth_id']=2;
                            $vipdata['auth_start_time']=0;
                            $vipdata['auth_end_time']=0;
                            $vipdata['timetype']=0;
                        }
                    }
                    $db->insert('account_user_and_auth', $vipdata);
                }
            }
        }
        $redis->close(); 

        if(isset($course_grade) && (!empty($course_grade)))
        {
            if(count($course_grade)>0)
            {
                //unset($course_grade[0]);
                foreach($course_grade as $val)
                {
                    $secondData = array();
                    $pieces = explode(',',$val);
                    $secondData['course_id'] =  $pieces[0];
                    $secondData['grade_id'] =  $pieces[1];
                    $secondData['teacher_id'] =  $teacherId;
                    $db->insert('auth_teacher_second', $secondData);
                }
            }
        }

        if ($isvalid) {
            renderJsonResponse(200, '注册成功', array('telephone' => $telephone, 'token' => $token, 'id' => $teacherId));
        }


        $db = null;
}

function GetIP()
{
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
        $ip = getenv("REMOTE_ADDR");
    else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
        $ip = $_SERVER['REMOTE_ADDR'];
    else
        $ip = "unknown";
    return ($ip);
}

$app->post('/register/sendPhoneVerificationCode', 'sendRegisterPhoneVerificationCode');
function sendRegisterPhoneVerificationCode()
{
    $telephone = $_POST['telephone'];

    if (empty($telephone)) {
        renderJsonResponse(406, '手机号不能为空', array());
        return;
    }

    if (strlen($telephone) <> 11) {
        renderJsonResponse(403, '手机号格式不正确', array());
        return;
    }

    $db = new Database();

    $code = rand(1000, 9999);
    $ip = GetIP();
    $data = array('telephone' => $telephone, 'code' => $code, 'ip' => $ip);

    $db->delete('misc_register_phone_code', 'telephone', $telephone);
    $db->insert('misc_register_phone_code', $data);

    $content = "您的注册验证码是" . $code . "。请您在5分钟内填写，如非本人操作请忽略。【京版云】";

    renderJsonResponse(200, '发送验证码成功', array());

    $db = null;
}

//找回密码
$app->post('/forgotpwd/setNewPassword', 'setNewPasswordByPhone');
function setNewPasswordByPhone()
{
    $telephone = $_POST['telephone'];
    $code = $_POST['verify_code'];
    $password = sha1($_POST['newpassword']);

    if (empty($telephone) || empty($password)) {
        renderJsonResponse(406, '手机号或密码不能为空', array());
        return;
    }

    if (strlen($telephone) <> 11) {
        renderJsonResponse(403, '手机号格式不正确', array());
        return;
    }

    $db = new Database();

    //验证是否为正确的手机验证码
    /*$codeData = array('telephone' => $telephone, 'code' => $code);
    $codeResult = $db->check_exist('misc_forgot_password_phone_code', $codeData);
    if ($codeResult <> true) {
        renderJsonResponse(500, '验证码错误', array());
        return;
    } else {
        $db->delete('misc_forgot_password_phone_code', 'telephone', $telephone);
    }*/ 
    $redis=new Redis();
    $redis->connect(REDIS_HOST,REDIS_PORT);  
    $redis->auth(REDIS_AUTH); 

    $redis_key='sms_'.$telephone;   
    $sms_code=$redis->get($redis_key);  
      
    if($code!=$sms_code || !$code){
        $isvalid = false;
        renderJsonResponse(500, '验证码错误', array());
        return;
    } 

    //验证是否注册
    $t = getTeacherByTelephone($telephone);
    if (empty($t)) {
        renderJsonResponse(406, '该手机号尚未注册', array());
        return;
    }

    $token = guid();

    $data = array(
        'password' => $password,
        'access_token' => $token
    );
    $redis->delete($redis_key);
    $redis->close(); 
    
    // 更改用户到数据库
    $db->update('auth_teacher', $data, 'telephone', $telephone);

    renderJsonResponse(200, '成功设定新密码', array('telephone' => $telephone, 'token' => $token));

    $db = null;
}

$app->post('/forgotpwd/sendForgotPasswordPhoneVerificationCode', 'sendForgotPasswordPhoneVerificationCode');
function sendForgotPasswordPhoneVerificationCode()
{
    $telephone = $_POST['telephone'];
    if (empty($telephone)) {
        renderJsonResponse(406, '手机号不能为空', array());
        return;
    }
    if (strlen($telephone) <> 11) {
        renderJsonResponse(403, '手机号格式不正确', array());
        return;
    }

    $db = new Database();

    $code = rand(1000, 9999);//todo
    $code = '8888';
    $data = array('telephone' => $telephone, 'code' => $code);

    $db->delete('misc_forgot_password_phone_code', 'telephone', $telephone);
    $db->insert('misc_forgot_password_phone_code', $data);

    $content = "您的密码修改验证码是" . $code . "。请您在5分钟内填写，如非本人操作请忽略。【京版云】";

    //sendSmsMessage($telephone, $content);

    renderJsonResponse(200, '发送验证码成功', array());

    $db = null;
}

$app->run();