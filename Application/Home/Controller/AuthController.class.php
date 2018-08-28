<?php
namespace Home\Controller;

use Think\Controller;
use Common\Common\CSV;
use Common\Common\REDIS;
class AuthController extends Controller
{
    private $appid ='';
    private $appkey ='';

    public function getAuthKey($string)
    {
        return md5($this->appid.$string.$this->appkey);
    }
    public function __construct()
    {
        parent::__construct();
        header("Content-type: text/html; charset=utf-8");
        $this->appid='bGImgbnnYXf0tyYV';
        $this->appkey='Q0CyrGJQ9VkVyHxEOBW1';
    }
    private function __random($length, $chars = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ')
    {
        $hash = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }

    public function getAccessToken()
    {
        $role = getParameter('roleId','int');
        $userId = getParameter('userId','int');
        $time = getParameter('time','str');
        $key = getParameter('key','str');
        $currentTime = time();
        if(abs($currentTime - strtotime($time)) < 60*5)
        {

            $string=$this->getAuthKey($role.','.$userId.','.$time);
            if($key != $string)
                $this->showMessage(500,'Auth failed');

            //write token to redis
            $redis_obj=new REDIS();
            $redis=$redis_obj->init_redis();
            $token= $this->__random(12);
            $redis->setex($token,5*60,$role.','.$userId);
            $redis->close();
            $data['token'] = $token;
            $this->showMessage(200,'success',$data);
        }
        else{
            $this->showMessage(501,'Time Error');
        }
    }
    private function __changeArrayKey($destKey,$sourceKey,&$array){
        if(!is_array($sourceKey)) {
            $array[$destKey] = $array[$sourceKey];
            unset($array[$sourceKey]);
        }
        else{
            $array[$destKey] = implode('_',array_map(function($n) use($array){
               return $array[$n];
            },$sourceKey));
            foreach($sourceKey as $key=>$val){
                unset($array[$val]);
            }
        }
    }
    private function __teacherInfoCallback($info,$role,$userId)
    {
        $this->__changeArrayKey('account','telephone',$info);
        $this->__changeArrayKey('display_name','teacher_name',$info);
        $this->__changeArrayKey('province_name','province',$info);
        $this->__changeArrayKey('city_name','city',$info);
        $this->__changeArrayKey('district_name','district',$info);
        $info['user_id'] = $role*10000000+$userId;
        $info['school_stage'] = ['id'=>$info['school_category'],'name'=>C('SCHOOL_CATEGORY')[$info['school_category']]];
        $info['role'] = '教师';
        unset($info['school_category']);
        unset($info['course']);
        unset($info['class_name']);
        unset($info['auth_status']);
        unset($info['permissions_status']);
        unset($info['auth_status']);
        unset($info['apply_school_status']);
        unset($info['flag']);
        unset($info['create_at']);
        unset($info['teacher_id']);
        return $info;
    }

    private function __studentInfoCallback($info,$role,$userId)
    {
        $info['display_name'] = $info['student_name'];
        $this->__changeArrayKey('account',['parent_tel','student_name'],$info);
        $this->__changeArrayKey('province_name','province',$info);
        $this->__changeArrayKey('city_name','city',$info);
        $this->__changeArrayKey('district_name','district',$info);
        $info['user_id'] = $role*10000000+$userId;
        $info['school_stage'] = ['id'=>$info['school_category'],'name'=>C('SCHOOL_CATEGORY')[$info['school_category']]];
        $info['role'] = '教师';
        unset($info['flag']);
        unset($info['apply_school_status']);
        unset($info['school_category']);
        unset($info['school_code']);
        unset($info['id']);
        unset($info['sex']);
        unset($info['birth_date']);
        unset($info['password']);
        unset($info['auth_start_time']);
        unset($info['auth_end_time']);
        return $info;
    }

    private function __parentInfoCallback($info,$role,$userId)
    {
        $this->__changeArrayKey('display_name','parent_name',$info);
        $this->__changeArrayKey('account','telephone',$info);
        $info['user_id'] = $role*10000000+$userId;
        $info['school_stage'] = ['id'=>1,'name'=>C('SCHOOL_CATEGORY')[1]];
        $info['role'] = '教师';
        $info['school_id'] = 2000;
        $info['school_name'] = '其它学校';
        $info['province_id'] = 1;
        $info['city_id'] = 1;
        $info['district_id'] = 37;
        $info['province_name'] = '北京市';
        $info['city_name'] = '北京市';
        $info['district_name'] = '东城区';
        unset($info['flag']);
        unset($info['id']);
        unset($info['sex']);
        unset($info['email']);
        unset($info['avatar']);
        unset($info['grade_id']);
        return $info;
    }

    public function getUserInfo()
    {
        $token = getParameter('token','str');
        $redis_obj=new REDIS();
        $redis=$redis_obj->init_redis();
        $code=$redis->get($token);
        $redis->setex($token,1,'');
        $redis->close();
        if(empty($code)){
            $this->showMessage(502,'Token is invalid');
        }
        list($role,$userId) = explode(',',$code);
        $info = [];
        switch($role){
            case ROLE_TEACHER:
                $where['auth_teacher.id'] = $userId;
                $teacherInfo = D('Auth_teacher')->getTeacherDataAll($where);
                $info = $this->__teacherInfoCallback($teacherInfo[0],$role,$userId);
                break;
            case ROLE_STUDENT:
                $info = D('Auth_student')->getStudentSchoolData($userId);
                $info = $this->__studentInfoCallback($info,$role,$userId);
                break;
            case ROLE_PARENT:
                $info = D('Auth_parent')->getParentInfo($userId);
                $info = $this->__parentInfoCallback($info,$role,$userId);
                break;
            default:$this->showMessage(503,'role error!',[]);break;
        }

       $this->showMessage(200,'success',$info);
    }

    public function pepSendLogout($token,$role,$userId)
    {
        $host = $_SERVER['SERVER_NAME'];
        $token = base64_encode($token.','.$role.','.$userId);
        $key = $this->getAuthKey($token);
        $data = array(
            'token' => $token,
            'key' => $key,
        );
        $data = http_build_query($data);
        $writeURL = 'http://jby-szxy.mypep.cn/home/jby_auth/api-logout';
        $out = "POST ${writeURL} HTTP/1.1\r\n";
        $out .= "Host:${host}\r\n";
        $out .= "Content-type:application/x-www-form-urlencoded\r\n";
        $out .= "Content-length:".strlen($data)."\r\n";
        $out .= "Connection:close\r\n\r\n";
        $out .= "${data}";

        // create connect
        $errno = '';
        $errstr = '';
        $fp = fsockopen($host, 80, $errno, $errstr, 10);
        fputs($fp, $out);
        fclose($fp);
    }

}