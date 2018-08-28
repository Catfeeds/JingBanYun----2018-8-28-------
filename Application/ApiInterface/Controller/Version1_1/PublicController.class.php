<?php
/*
 * mawengang
 * 2015-12-10
 */


namespace ApiInterface\Controller\Version1_1;
use Think\Controller;

class PublicController extends Controller {

    public function _initialize() {

        $switch = C('MESSAGE_SWITCH');

        if( $switch == 2) {
            $headers = $this->getallheaders();
            $role = $headers['Role'];
            $macToken = $headers['Mactoken'];
            $Device = $headers['Device'];
            $UserId = $headers['Userid'];

            if ($Device == "iPad") {

                if (empty($macToken)) {
                    //$this->ajaxReturn(array('status' => 200, 'message' => 'macToken为空'));
                } else {

                    $url = $_SERVER["REQUEST_URI"];

                    if ( strpos($url,"login") !== false ) { //如果是login
                        $role = 0;
                    }
                    switch ($role) {
                        case 2: //老师
                            $map['ipad_address'] = $macToken;
                            $map['id'] = $UserId;
                            $info = M('auth_teacher')->where( $map )->find();

                            if (empty($info)) {
                                $this->ajaxReturn(array('status' => 200, 'message' => '您的设备已在别处登录，请重新登录','authError'=>'error'));
                            }
                            break;
                        case 3: //学生
                            $map['ipad_address'] = $macToken;
                            $map['id'] = $UserId;
                            $info = M('auth_student')->where( $map )->find();
                            if (empty($info)) {
                                $this->ajaxReturn(array('status' => 200, 'message' => '您的设备已在别处登录，请重新登录','authError'=>'error'));
                            }
                            break;
                        case 4: //家长
                            $map['ipad_address'] = $macToken;
                            $map['id'] = $UserId;
                            $info = M('auth_parent')->where( $map )->find();
                            if (empty($info)) {
                                $this->ajaxReturn(array('status' => 200, 'message' => '您的设备已在别处登录，请重新登录','authError'=>'error'));
                            }
                            break;
                        default:
                            //未登陆

                    }
                }

            } else {
                if (empty($macToken)) {
                    //$this->ajaxReturn(array('status' => 200, 'message' => 'macToken为空'));
                } else {

                    $url = $_SERVER["REQUEST_URI"];

                    if ( strpos($url,"login") !== false ) { //如果是login
                        $role = 0;
                    }
                    switch ($role) {
                        case 2: //老师
                            $map['mac_address'] = $macToken;
                            $map['id'] = $UserId;
                            $info = M('auth_teacher')->where( $map )->find();

                            if (empty($info)) {
                                $this->ajaxReturn(array('status' => 200, 'message' => '您的设备已在别处登录，请重新登录','authError'=>'error'));
                            }
                            break;
                        case 3: //学生
                            $map['mac_address'] = $macToken;
                            $map['id'] = $UserId;
                            $info = M('auth_student')->where( $map )->find();
                            if (empty($info)) {
                                $this->ajaxReturn(array('status' => 200, 'message' => '您的设备已在别处登录，请重新登录','authError'=>'error'));
                            }
                            break;
                        case 4: //家长
                            $map['mac_address'] = $macToken;
                            $map['id'] = $UserId;
                            $info = M('auth_parent')->where( $map )->find();
                            if (empty($info)) {
                                $this->ajaxReturn(array('status' => 200, 'message' => '您的设备已在别处登录，请重新登录','authError'=>'error'));
                            }
                            break;
                        default:
                            //未登陆

                    }
                }
            }
        }


    }

    public function getallheaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

}