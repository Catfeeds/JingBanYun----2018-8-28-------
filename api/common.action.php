<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';

/*路由*/
$app->post('/login_jrkt', 'login');
$app->post('/personalinfo','getPersonalInfo');
$app->get('/personalinfo','getPersonalInfo');
$app->post('/uploadavatar','uploadAvatar');
$app->post('/resetpassword','resetpassword');
function uploadAvatar()
{
    $userid = $_POST['user_id'];
    $role = $_POST['role'];
    switch($role)
    {
     case 0: $dirName = "../Uploads/Avatars/";
             $fileName = $userid ."_t.jpg";
             $sourceTable = "auth_teacher";
             break;
     case 1: $dirName = "../Uploads/StudentAvatars/";
             $fileName = $userid .".jpg";
             $sourceTable = "auth_student";
             break;
     case 2: $dirName = "../Uploads/ParentAvatars/";
             $fileName = $userid .".jpg";
             $sourceTable = "auth_parent";
             break;
     default:exit;
    }
     header("Access-Control-Allow-Origin: *");
     if ($_FILES["file"]["type"] == "image/jpeg") {
         if ($_FILES["file"]["error"] > 0) {
             renderJsonResponse(406, $_FILES["file"]["error"], array()); //406 Not Acceptable
         } else {
            
            move_uploaded_file($_FILES["file"]["tmp_name"], $dirName . $fileName); //注意文件夹权限
            
            $dir_img = str_replace('..','',$dirName);

            $ossurl = str_replace('/api','',dirname(__FILE__));

            $imgpath = $ossurl.$dir_img . $fileName;

            $urldata = curl_post('http://'.WEB_URL.'/index.php?m=Home&c=App&a=upload_file',$imgpath);

            $jsonurl = json_decode($urldata,true); //得到oss文件路径
            
            if( $jsonurl ) {
                 $db = new Database();
                 $data = array(
                     'avatar' => $jsonurl[1],
                     'update_at' => time(),
                 );
                 
                 $db->update($sourceTable, $data, 'id', $userid);
                 unlink($dirName . $fileName);
                 renderJsonResponse(200, '上传成功', array()); 
            } else {
                renderJsonResponse(406, '上传失败', array()); 
            }
               
             $db = null;
         }
     } else {
         renderJsonResponse(406, '图片格式不正确', array());
     }
}

function curl_post( $url,$filepath) { 
    $ch = curl_init();
    $data = array('file' => '@'.$filepath);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);    // 5.6 给改成 true了, 弄回去 
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt ( $ch, CURLOPT_HEADER, 0 );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $return = curl_exec ( $ch );
    curl_exec($ch);
    return $return;
}

function getPersonalInfo()
{

    $db = new Database();
    $userid = $_REQUEST['user_id'];
    $role = $_REQUEST['role'];
    switch($role)
    {
    case 0: $sourceTable = 'auth_teacher';
            break;
    case 1: $sourceTable = 'auth_student';
             break;
    case 2: $sourceTable = 'auth_parent';
            break;
    default:return;
    }
    $qr = "select * from $sourceTable where id=$userid";        

    $sel = $db->fetch_custom($qr,array());
    $result = pushToArray($sel);

    $result = empty($result) ? array():$result[0];


    if(($role==1) && (!empty($result->school_id)))//增加学校昵称
    {
        $result->school_name = '';
        $qr="select * from dict_schoollist where id=".$result->school_id;
        $sel = $db->fetch_custom($qr,array());
        $school = pushToArray($sel);
        if(!empty($school)){
            $school = $school[0];
            $result->school_name = $school->school_name;
        }else{
            $result->school_name = '';
        }
            
        if(strpos($result->avatar,'.')===false)
        {
            $result->avatar.='.jpg';
        }

        if (preg_match('/Resources/', $result->avatar)){
            
            $result->img_url = OSS_URL.$result->avatar;
        } else {
            $result->img_url = "http://".WEB_URL."/Uploads/StudentAvatars/".$result->avatar;
        }


        
        //echo $result->birth_date;exit;
        $result->birth_date = date('Y-m-d',$result->birth_date);
        if(null == $result->id_card)
         $result->id_card = '';
    }

    if($role==0)
    {
        $result->course_grade='';
        $course_grade[] = $result->course_id.','.$result->grade_id;
        $qr="select * from auth_teacher_second where teacher_id=".$userid;
        $sel = $db->fetch_custom($qr,array());
        $list = pushToArray($sel);
        if(!empty($list))
        {
            foreach($list as $val)
            {
                $course_grade[] = $val->course_id.','.$val->grade_id;
            }
        }
        $result->course_grade = $course_grade;

        $result->school_name = '';
        $qr="select * from dict_schoollist where id=".$result->school_id;
        $sel = $db->fetch_custom($qr,array());
        $school = pushToArray($sel);
        if(!empty($school))
        {
            $school = $school[0];
            $result->school_name = $school->school_name;
        }
        if(strpos($result->avatar,'.')===false)
        {
            $result->avatar.='.jpg';
        }
        if (preg_match('/Resources/', $result->avatar)){

            $result->img_url = OSS_URL.$result->avatar;
        } else {
            $result->img_url = "http://".WEB_URL."/Uploads/Avatars/".$result->avatar;
        }
        
    }

    if($role==2)
    {
        if(strpos($result->avatar,'.')===false)
        {
            $result->avatar.='.jpg';
        }

        if (preg_match('/Resources/', $result->avatar)){
            
            $result->img_url = OSS_URL.$result->avatar;
        } else {
            $result->img_url = "http://".WEB_URL."/Uploads/ParentAvatars/".$result->avatar;
        }

        $result->name = $result->parent_name;
    }



    $db = null;
    renderJsonResponse(200, '', $result);
}
function login()
{
    $telephone = isset($_REQUEST['telephone']) ? $_REQUEST['telephone']:'';
    $password = isset($_REQUEST['pwd']) ? $_REQUEST['pwd']:'';

    if (empty($telephone) || empty($password)) {
            renderJsonResponse(404, '用户名密码错误', array());
            return;
        }

    $password = md5($password);
    $db = new Database();
    $isValid = false;

//    $coulm = array('id','name','nickname','usertype','phoneno');
//    $where = array('phoneno'=>$telephone);

    $user = $db->fetch_single_row('userinfo','phoneno',$telephone);

    if(empty($user))
    {
        renderJsonResponse(404, '用户名或密码错误', array());return;
    }

    if(strtolower($user->password)==strtolower($password))
    {
        $isValid=true;

             $result = array(
                 'id' => $user->id,
                 'name' => $user->name,
                 'role' => $user->usertype //0--teacher 1--student 2--parent
             );

        renderJsonResponse(200, '', $result);return;
    }
//    $lookForTable = array('auth_teacher','auth_student','auth_parent');
//    for($i=0;$i<count($lookForTable);$i++)
//    {
//     $user = $db->fetch_single_row($lookForTable[$i], 'telephone', $telephone);
//     if ($user && $user->password == $password)
//     {
//     $result = array(
//                 'id' => $user->id,
//                 'name' => $user->name,
//                 'sex' => $user->sex,
//                 'student_name' => $user->student_name,
//                 'parent_name' => $user->parent_name,
//                 'parent_tel' => $user->parent_tel,
//                 'telephone' => $user->telephone,
//                 'access_token' => $user->access_token,
//                 'avatar' => $user->avatar,
//                 'points' => $user->points,
//                 'brief_intro' => $user->brief_intro,
//                 'school_id' => $user->school_id,
//                 'role' => $i //0--teacher 1--student 2--parent
//             );
//     renderJsonResponse(200, '', $result);
//     $isValid = true;
//     break;
//     }
//
//    }
    if(!$isValid)
     renderJsonResponse(404, '该用户不存在', array());
    $db = null;
}

/**
 * 忘记密码
 */
function resetpassword()
{
    $telephone = isset($_REQUEST['telephone'])?$_REQUEST['telephone']:'';
    $password = isset($_REQUEST['pwd'])?$_REQUEST['pwd']:'';
    $confirmPassword = isset($_REQUEST['confirm_pwd'])?$_REQUEST['confirm_pwd']:'';
    $verify_code = isset($_REQUEST['verify_code'])?$_REQUEST['verify_code']:'';

    if(empty($telephone) || empty($password) || empty($confirmPassword) || empty($verify_code))
    {
        renderJsonResponse(404, '参数错误', array());
        return;
    }

    if($password!=$confirmPassword)
    {
        renderJsonResponse(404, '两次密码不一致', array());
        return;
    }

    $password = md5($password);
    $confirmPassword = md5($confirmPassword);

    $db = new Database();

    //验证是否为正确的手机验证码
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

    $a = $db->update('userinfo',array('password'=>$password),'phoneno',$telephone);
    renderJsonResponse(200, '更改成功', array());

}


$app->run();