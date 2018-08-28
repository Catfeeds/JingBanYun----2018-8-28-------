<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/7 0007
 * Time: 上午 9:49
 */
namespace Home\Controller;

use Think\Controller;
use Common\Common\JSSDK;

class SignUpController extends PublicController
{
    private $model;
    private $pageSize = 20;

    public function __construct()
    {
        parent::__construct();
        $this->model = D('activity_wx');
    }

    public function insert()
    {
        if ($_POST) {
            $data['student_name'] = getParameter('student_name', 'str');
            $data['school_name'] = getParameter('school_name', 'str');
            $data['class_name'] = getParameter('class', 'str');
            if (I('post.teacher_name')) {
                $data['class_teacher'] = I('post.teacher_name');
            } else {
                $data['class_teacher'] = '';
            }
            $data['telephone'] = getParameter('telephone', 'str');
            $data['content'] = getParameter('content', 'str');
            static $status = false;
            if (_mb_strlen($data['content']) > 300) {
                $status = true;
            }
            if (_mb_strlen($data['student_name']) > 20) {
                $status = true;
            }
            if (_mb_strlen($data['class_name']) > 20) {
                $status = true;
            }
            if (_mb_strlen($data['class_teacher']) > 20) {
                $status = true;
            }
            if (_mb_strlen($data['telephone']) > 11) {
                $status = true;
            }
            if (_mb_strlen($data['telephone']) == 11) {
                $preg = "/13[0-9]{1}\d{8}|15[0-9]{1}\d{8}|18[0-9]{1}\d{8}|17[0-9]{1}\d{8}/";
                $tip = preg_match($preg, $data['telephone']);
                if (!$tip) {
                    $status = true;
                }
            }
            if (_mb_strlen($data['school_name']) > 20) {
                $status = true;
            }
            if ($status) {
                $this->error('报名失败,字数超出限制');
            } else {
                $tip = $this->model->add($data);
                if ($tip) {
                    $this->redirect("SignUp/SignUpSuccess");
                }
            }
        }
        $this->WxShare();
        $this->display('insert');
    }

    /*
     *微信分享
     */
    public function WxShare()
    {
        $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage', $signPackage);

    }

    public function SignUpSuccess()
    {
        $this->display();
    }


}