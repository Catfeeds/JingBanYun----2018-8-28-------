<?php
namespace Exercise\Controller;
use Think\Controller;
use Think\Verify;

class TestEntryMgmtController extends ExerciseGlobalController
{

    public $model;
    public $page_size=20;
                //permissions
    public function __construct() {
        parent::__construct();
        $this->model=D('Auth_admin');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }

    function examinationQuestions(){
        $this->display();
    }

    function testModify(){
        $this->display();
    }

}
