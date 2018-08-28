<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/19 0019
 * Time: 下午 2:47
 */
namespace Home\Controller;

use Think\Controller;

class SchoolJoinController extends PublicController
{
    public function SchoolJoin(){
        if($_POST){
            //var_dump($_POST);die;
            $data['proposer'] = getParameter('name','str');
            $data['proposer_tel'] = getParameter('tel','str');
            $data['school_name'] = getParameter('school_name','str');
            $tip = D('School_join')->SchoolJoin($data);
            if($tip){
                $data['code'] = 200;
               echo json_encode($data);
            }else{
                $data['code'] = 500;
                $data['message'] = '入驻申请失败';
                echo json_encode($data);
            }
        }else{
            $this->display('schoolJoin');

        }
    }
}