<?php

include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';
include 'Common/ImageResize.php';

// 路由
$app->post('/login', 'login');

function login()
{

    $telephone = $_POST['telephone'];
    $password = sha1($_POST['pwd']);

    if (empty($telephone) || empty($password)) {
        renderJsonResponse(404, '账号不存在', array());
        return;
    }

    $db = new Database();
    $user = $db->fetch_single_row('auth_parent', 'telephone', $telephone);

    /*if($user->lock == 1)
    {
      renderJsonResponse(404, '账号已被锁定', array());
      return;
    }*/

    if ($user && $user->password == $password) {
        $result = array(
            'id' => $user->id,
            'parent_name' => $user->parent_name,
            'name' => $user->parent_name,
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
        renderJsonResponse(404, '密码错误', array());
    }

    $db = null;
}



//student register
$app->post('/register', 'register');
function register()
{
    $telephone = $_POST['telephone'];
    $verify_code = $_POST['verify_code'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = sha1($_POST['pwd']);
    $confirmPassword = sha1($_POST['confirm_pwd']);
    $isvalid = true;
    $db = new Database();
    if (empty($telephone) || empty($password) || empty($name)) {
        $isvalid = false;
        renderJsonResponse(406, '手机号、密码和姓名不能为空', array());
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
    
    if(!preg_match("/^1[34578]{1}\d{9}$/",$telephone)){
        $isvalid = false;
        renderJsonResponse(403, '手机号格式不正确', array());
        return;
    }
    /*
     //验证是否为正确的手机验证码
        $codeData = array('telephone' => $telephone, 'code' => $verify_code);
        $codeResult = $db->check_exist('misc_register_phone_validcode', $codeData);
        if ($codeResult <> true) {
            $isvalid = false;
            renderJsonResponse(500, '验证码错误', array());
            return;
        } else {
            $db->delete('misc_register_phone_validcode', 'telephone', $telephone);
        }
    */
    $redis=new Redis();
    $redis->connect(REDIS_HOST,REDIS_PORT);  
    $redis->auth(REDIS_AUTH); 

    $redis_key='sms_'.$telephone;   
    $code=$redis->get($redis_key);  
    
     
    if($verify_code!=$code || !$code){
        $isvalid = false;
        renderJsonResponse(500, '验证码错误', array());
        return;
    }
    

    $token = guid();
    $data = array(
        'telephone' =>$telephone,
        'parent_name' =>$name,
        'email' =>$email,
        'password' => $password,
        'create_at' => time(),
        'update_at' => time()
    );
    //TODO:judge by name and id_card
    //$parent = getParentByTelephone($telephone);
	$parent = $db->fetch_single_row("auth_parent", 'telephone', $telephone);
    $parentId = 0;

    if ($parent) { //已经存在该STUDENT
        $isvalid = false;
        renderJsonResponse(406, '该用户已经注册', array());
        return;
    } else { // 不存在改号码
        // 入注册用户到数据库
        if ($isvalid) {
            $redis->delete($redis_key); 
            
            $parentId = $db->insert('auth_parent', $data);
            
            //这里让学生和家长进行绑定
            $qr = "update auth_student set parent_id=".$parentId." where parent_tel='". $telephone ."'";
            $sel = $db->fetch_custom($qr,array());

            //赠送30天vip 
            $vip_config=APP_REGISTER_GIVE_VIP_STATUS;
            if($vip_config && $vip_config<=3){
                $vipdata = array(
                    'user_id' => $parentId,
                    'role_id' => 4,
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
                    $qr = "select * from auth_student where parent_tel='" .$telephone."' group by school_id" ;  
                    $sel = $db->fetch_custom($qr,array());
                    $student_info = pushToArray($sel); 
                    if(isset($student_info[0]) && !empty($student_info[0])){
                        $school_privilege_arr=array();
                        for($i=0;$i<count($student_info);$i++){
                            $qr = "select * from dict_schoollist where id=" . $student_info[$i]->school_id ;     
                            $sel = $db->fetch_custom($qr,array());
                            $school_info = pushToArray($sel); 
                            if(!empty($school_info)){
                                if(empty($school_privilege_arr)){
                                    if ($school_info[0]->user_auth == 3 && time() >= $school_info[0]->auth_start_time && time() < $school_info[0]->auth_end_time) {
                                        $school_privilege_arr=$school_info[0];
                                    } 
                                }else{
                                    if ($school_info[0]->user_auth == 3 && time()>=$school_info[0]->auth_end_time && $school_privilege_arr['auth_end_time'] < $school_info[0]->auth_end_time) {
                                        $school_privilege_arr=$school_info[0];
                                    }
                                }
                            }
                        }
                        //判断
                        if(empty($school_privilege_arr)){
                            //普通权限
                            $vipdata['auth_id']=2;
                            $vipdata['auth_start_time']=0;
                            $vipdata['auth_end_time']=0;
                            $vipdata['timetype']=0;
                        }else{
                            //团体vip 
                            $vipdata['timetype']=$school_privilege_arr->timetype;
                            $vipdata['auth_id']=3;  
                            $vipdata['auth_end_time']=$school_privilege_arr->auth_end_time;
                        }
                    }else{
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

    if ($isvalid) {
        renderJsonResponse(200, '注册成功', array('name' => $name, 'token' => $token, 'id' => $parentId));
    }
    $db = null;
}
function getParentByTelephone()
{
    $db = new Database();
    $user = $db->fetch_single_row("auth_parent", 'telephone', $telephone);
    $db = null;
    return $user;
}
$app->post('/forgotpwd/setNewPassword', 'setNewPasswordByPhone');
function setNewPasswordByPhone()
{
	$db = new Database();
    $telephone = $_POST['telephone'];
	//$t1 = getParentByTelephone($telephone);
	$user = $db->fetch_single_row("auth_parent", "telephone", $telephone);
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
    
	
    if (empty($user)) {
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
    $db->update('auth_parent', $data, 'telephone', $telephone);

    renderJsonResponse(200, '成功设定新密码', array('telephone' => $telephone, 'token' => $token));

    $db = null;
}
/*
function getParentByTelephone($telephone)
{
    $db = new Database();
    $user = $db->fetch_single_row("auth_parent", "telephone", $telephone);
    $db = null;
    return $user;
}*/
$app->run();