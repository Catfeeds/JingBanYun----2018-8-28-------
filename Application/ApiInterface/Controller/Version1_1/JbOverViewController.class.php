<?php
namespace ApiInterface\Controller\Version1_1;

use Think\Controller;

class JbOverViewController extends PublicController
{
    public $pageSize = 20;
    public $Model;

    public function __construct()
    {
        parent::__construct();
        $this->Model = D('Biz_bj_overview');
        $this->assign('oss_path', C('oss_path'));
    }


    /**
     * @描述：获取京版概览
     * @参数：pageIndex[int] Y 页码索引
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function jbOverView()
    {
        $this->assign('data', $this->Model->getJbOverView());
        $this->display();
    }



}