<?php
/**
 * Created by PhpStorm.
 * User: GM
 * Date: 2017/12/28
 * Time: 9:56
 */
namespace Wchat\Controller;
use Think\Controller;


class WxSignInController extends Controller
{

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = D('Wx_travel_activity');
    }

    private function dataValidate($data = [])
    {
        if(!preg_match("/^1[34578]\d{9}$/", $data['telephone']))
            return '手机号码格式不正确';
        return true;
    }


    /**
     *描述：报名页
     */
    public function signIn()
    {
        if($_POST) {
            $data['name'] = getParameter('name', 'str');
            $data['school_name'] = getParameter('schoolName', 'str');
            $data['telephone'] = getParameter('telephone', 'str');
            $data['professional'] = getParameter('profession', 'str');

            $validResult = $this->dataValidate($data);
            if ($validResult !== true)
                $this->showMessage(500, $validResult);

            $addResult = D('Wx_headmaster_reg')->add($data);
            if ($addResult === true)
                $this->showMessage(200, '报名成功');
            else if (-1 === $addResult)
                $this->showMessage(500, '您已经签到,无需再次签到');
            else
                $this->showMessage(500, '报名失败');
        }
        else
            $this->display();
    }

    public function getAlternativeSchoolName()
    {
        $name = getParameter('keyword','str');
        $condition['school_name'] = array('like',"%$name%");
        $result = D('Dict_schoollist')->getSchool($condition);
        foreach($result as $key=>$val)
        {
            $result[$key]['title'] = ($val['name']) ;
            unset($result[$key]['name']);
        }
        echo json_encode(array('data' => $result));

    }


}
