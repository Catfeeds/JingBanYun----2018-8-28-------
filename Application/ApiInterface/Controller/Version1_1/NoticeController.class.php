<?php
namespace ApiInterface\Controller\Version1_1;

use Think\Controller;

class NoticeController extends PublicController
{
    public $pageSize = 20;

    function getPageSize(){
        return $this->pageSize;
    }
    public function __construct()
    {
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
    }
    private function displayIosNotice()
    {
        $ostype = isset($_GET['ostype']) ? $_GET['ostype']:'';
        $version = isset($_GET['version']) ? $_GET['version']:0;
        if($ostype=='ios' && ($version>0)) {
            echo "<html>
            <head>
            <title>用户须知</title>
            </head>
            用户须知
            </html>
            ";
            return true;
        }
        return false;
    }
    /*
     * 家长端升级须知
     */
    public function parentNotice()
    {
        if(!$this->displayIosNotice())
            $this->display();
    }

    /*
     * 教师端升级须知
     */
    public function teacherNotice()
    {
        if(!$this->displayIosNotice())
            $this->display();
    }

    /*
     * 学生端端升级须知
     */
    public function studentNotice()
    {
        if(!$this->displayIosNotice())
            $this->display();
    }
}