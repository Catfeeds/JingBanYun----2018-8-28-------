<?php
namespace Home\Controller;
use Think\Controller;

class PublicController extends Controller {

    public function _initialize() {
        $idtrue = isMobile();
        $idtrue = true;
        if ($idtrue === false) {
            $userId = 0;
            $role = 0;
            $this->getUserIdRole($userId, $role);

            if (session('teacher.id')) {
                $mactoken = session('teacher.access_token');
            } else if (session('student.id')) {
                $mactoken = session('student.access_token');
            } else if (session('parent.id')) {
                $mactoken = session('parent.access_token');
            }

            if (empty($mactoken)) {
                //$this->ajaxReturn(array('status' => 200, 'message' => 'macToken为空'));
            } else {
                $url = $_SERVER["REQUEST_URI"];

                if ( strpos($url,"login") !== false || strpos($url,"logout") !== false ) { //如果是login
                    $role = 0;
                }


                switch ($role) {
                    case 2: //老师
                        $map['access_token'] = session_id();
                        $map['id'] = session('teacher.id');
                        $info = M('auth_teacher')->where( $map )->find();
                        if (empty($info)) {
                            session("teacher",null);
                            session("student",null);
                            session("parent",null);
                            echo "<script>alert('请重新登陆');window.location.href='/';</script>";exit;
                        }
                        break;
                    case 3: //学生
                        $map['access_token'] = session_id();
                        $map['id'] = session('student.id');
                        $info = M('auth_student')->where( $map )->find();
                        if (empty($info)) {
                            session("teacher",null);
                            session("student",null);
                            session("parent",null);
                            echo "<script>alert('请重新登陆');window.location.href='/';</script>";exit;
                        }
                        break;
                    case 4: //家长
                        $map['access_token'] = session_id();
                        $map['id'] = session('parent.id');
                        $info = M('auth_parent')->where( $map )->find();
                        if (empty($info)) {
                            session("teacher",null);
                            session("student",null);
                            session("parent",null);
                            echo "<script>alert('请重新登陆');window.location.href='/';</script>";exit;
                        }
                        break;
                    default:
                        //未登陆

                }
            }
        }

    }

    public function getUserIdRole(&$userId,&$role){
        //TODO:judgement by useragent
        if(!$this->isApp()) {
            if (session('teacher.id')) {
                $userId = session('teacher.id');
                $role = ROLE_TEACHER;
            } else if (session('student.id')) {
                $userId = session('student.id');
                $role = ROLE_STUDENT;
            } else if (session('parent.id')) {
                $userId = session('parent.id');
                $role = ROLE_PARENT;
            } else {
                $userId = -1;
                $role = -1;
            }
        }
        else{
            //get userId role from header
            if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone') || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')){
                $userId = empty($_REQUEST['userId'])?-1:$_REQUEST['userId'];
                if($userId != -1)
                    $role = empty($_REQUEST['role'])?-1:$_REQUEST['role'];
            }
            else if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')){
                preg_match("/&userId=(\d+)/is",$_SERVER['HTTP_USER_AGENT'],$regs);
                $userId = $regs[1];
                if(empty($user_id))
                    $userId=-1;
                preg_match("/&role=(\d+)/is",$_SERVER['HTTP_USER_AGENT'],$regs);
                $role = $regs[1];
                if(empty($regs[1]))
                    $role=-1;
                if(!empty($_REQUEST['userId']) && !empty($_REQUEST['role'])){
                    $userId = $_REQUEST['userId'];
                    $role = $_REQUEST['role'];
                }

            }
        }
    }

    public function isApp()
    {
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'jingbanyun') !== false )
            return true;
        return false;
    }

}