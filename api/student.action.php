<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';
include 'Common/ImageResize.php';

// 路由
$app->post('/login', 'login');

function login()
{
    $student_name = $_POST['student_name'];
    $parent_tel = $_POST['parent_tel'];
    $password = sha1($_POST['pwd']);

    if (empty($student_name) || empty($password)) {
        renderJsonResponse(404, '账号不存在', array());
        return;
    }

    $db = new Database();
    $qr = "select * from auth_student where student_name='" . $student_name ."' and parent_tel='". $parent_tel ."' and  password='".$password."'";
    $sel = $db->fetch_custom($qr,array());
    $result = pushToArray($sel);

    if (!empty($result)) {
        $user = $db->fetch_single_row('auth_student', 'id', $result[0]->id);

        /*if($user->lock == 1)
        {
          renderJsonResponse(404, '账号已被锁定', array());
          return;
        }*/
    
        if ($user && $user->password == $password) {
            $result = array(
                'id' => $user->id,
                'student_name' => $user->student_name,
                'name' => $user->student_name,
                'user_name' => $user->user_name,
                'telephone' => $user->telephone,
                'access_token' => $user->access_token,
                'avatar' => $user->avatar,
            );

            if (preg_match('/Resources/', $result['avatar'])){
            
                $result['avatar'] = OSS_URL.$result['avatar'];
            } else {
                $result['avatar'] = "http://".WEB_URL."/Uploads/Avatars/".$result['avatar'];
            }
            
            renderJsonResponse(200, '', $result);

        } else {
            renderJsonResponse(404, '登录信息有误,请核对登录信息', array());
        }
    } else {
        renderJsonResponse(404, '登录信息有误,请核对登录信息', array());
    }
    
    $db = null;
}
function getStudentByNameId($student_name,$stuId)
{
    $db = new Database();

   
    $qr = "select * from auth_student where student_name='" . $student_name ."' and student_id=". $stuId ."";
    $sel = $db->fetch_custom($qr,array());
    $result = pushToArray($sel);
    $id=0;
    $user = null;
    if(isset($result[0]) && !empty($result[0]))
    {
        $id=$result[0]->id;
        $user = $db->fetch_single_row('auth_student', 'id', $id);
    }

    $db = null;
    return $user;
}
function getStudentByNameTel($student_name,$parent_tel)
{
    $db = new Database();
	
    $qr = "select * from auth_student where student_name='" . $student_name ."' and parent_tel='". $parent_tel ."'";
	
    $sel = $db->fetch_custom($qr,array());
    $result = pushToArray($sel);
	$user=array();
	if(!empty($result)){
		$user = $db->fetch_single_row('auth_student', 'id', $result[0]->id);
	}
    $db = null;
    return $user;
}
//student register
$app->post('/register', 'register');
$app->get('/register', 'register');
function register()
{
    $name = $_REQUEST['name'];
    $sex = $_REQUEST['sex'];
    $birthday=$_REQUEST['birthday'];
    $email = $_REQUEST['email'];
    $schoolId = $_REQUEST['school_id'];
    $stuId = $_REQUEST['studentId'];
    $id_card = $_REQUEST['id_card'];
    $parent_tel = $_REQUEST['parent_tel'];
    if(!empty($_REQUEST['parent_name']))
    {
    $parent_name = $_REQUEST['parent_name'];
    }
    else{ 
        $parent_name = ''; 
    }
    $password = sha1($_REQUEST['pwd']);

    $isvalid = true;
    

    if (!preg_match("/^\d+$/", $stuId)) {
        $isvalid = false;
        renderJsonResponse(407, '学生编号不正确', array());
        return;
    }


    if (empty($parent_tel) || empty($password) || empty($name)) {
        $isvalid = false;
        renderJsonResponse(406, '手机号、密码和姓名不能为空', array());
        return;
    }
    
    /*$shenfen = preg_match("/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/", $id_card);
    $shenfentwo = preg_match("/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}(\d|x|X)$/", $id_card);

    if(! ($shenfen || $shenfentwo) ) {
        $isvalid = false;
        renderJsonResponse(407, '身份证号不正确', array());
        return;
    }*/



    if (strlen($parent_tel) <> 11) {
        $isvalid = false;
        renderJsonResponse(403, '手机号格式不正确', array());
        return;
    }
    $db = new Database();

    $token = guid();

    $qr = 'select id,parent_name from auth_parent where telephone='. $parent_tel;
    $sel = $db->fetch_custom($qr,array());
    $result = pushToArray($sel);
     if(isset($result[0]) && !empty($result[0]))
     {
       $parent_id = $result[0]->id;
       $parent_name = $result[0]->parent_name;
     }
     else
     {
       $parent_id = 0;
	   renderJsonResponse(403, '该家长手机号未注册，请先让家长注册京版云', array());
       return;
     }
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
        'create_at' => time(),
        'update_at' => time()
    );

    if(empty($data['email'])) unset($data['email']);

    if(empty($data['id_card'])) unset($data['id_card']);

    //TODO:judge by name and id_card
    $student = getStudentByNameTel($name,$parent_tel);
    
    if (!empty($id_card)) {
        $id_info = $db->fetch_single_row('auth_student', 'id_card', $id_card);
        
        if (!empty($id_info)) {

            $isvalid = false;
            renderJsonResponse(403, '身份证号码已存在', array());
            return;
        }
    }
        
	$qr = "select * from dict_schoollist where id=" . $schoolId ;  
    $sel = $db->fetch_custom($qr,array());
    $school_info = pushToArray($sel); 
    if(empty($school_info)){
        $isvalid = false;
        renderJsonResponse(403, '学校信息不存在', array());
        return;
    }


    $studentId = 0;

    if ($student) { //已经存在该STUDENT
        $isvalid = false;
        renderJsonResponse(406, '该用户已经注册', array());
        return;
    } else { // 不存在改号码
        // 入注册用户到数据库
        if ($isvalid) {
            $studentId = $db->insert('auth_student', $data);

            //赠送30天vip 
            $vip_config=APP_REGISTER_GIVE_VIP_STATUS;
            if($vip_config && $vip_config<=3){
                $vipdata = array(
                    'user_id' => $studentId,
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
                
                //$parent_id
                
                $qr = 'select * from account_user_and_auth where role_id=4 and user_id='. $parent_id;      
                $sel = $db->fetch_custom($qr,array());  
                $result = pushToArray($sel);       
                if(isset($result[0]) && !empty($result[0]))
                {
                    if ($school_info[0]->user_auth == 3 && time() >= $school_info[0]->auth_start_time && time() < $school_info[0]->auth_end_time) {
                        $parent_vip=$vipdata;
                        $parent_vip['role_id']=4;
                        $parent_vip['user_id']=$parent_id;
                        $qr = 'update account_user_and_auth set auth_id='.$parent_vip['auth_id'].',auth_start_time='.$parent_vip['auth_start_time'].',auth_end_time='.$parent_vip['auth_end_time'].
                                ',timetype='.$parent_vip['timetype'].' where role_id=4 and user_id='.$parent_id;    
                        $sel = $db->fetch_custom($qr,array());  
                    } 
                }else{      
                    $parent_vip=$vipdata;
                    $parent_vip['role_id']=4;
                    $parent_vip['user_id']=$parent_id;
                    $studentId = $db->insert('account_user_and_auth', $parent_vip);
                }
            }
        }
    }

    if ($isvalid) {
        renderJsonResponse(200, '注册成功', array('name' => $name, 'token' => $token, 'id' => $studentId));
    }
    $db = null;
}

//找回密码
$app->post('/forgotpwd/setNewPassword', 'setNewPasswordByPhone');
function setNewPasswordByPhone()
{
    $name1 = $_POST['name'];
    $telephone = $_POST['telephone'];
    $name = unicodeDecode($name1);

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
    $t = getStudentByNameTel($name,$telephone);
    if (empty($t)) {
        renderJsonResponse(406, '该手机号下未注册该位同学', array());
        return;
    }

    $token = guid();

    $data = array(
        'password' => $password,
        'access_token' => $token
    );

    // 更改用户到数据库
    $redis->delete($redis_key);
    $redis->close();
    
    $db->update('auth_student', $data, 'id',$t->id);

    renderJsonResponse(200, '成功设定新密码', array('telephone' => $telephone, 'token' => $token));

    $db = null;
}
function unicodeDecode($data)
  {  
    function replace_unicode_escape_sequence($match) {
      return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
    }  
  
    $rs = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $data);
  
    return $rs;
  }  
$app->run();