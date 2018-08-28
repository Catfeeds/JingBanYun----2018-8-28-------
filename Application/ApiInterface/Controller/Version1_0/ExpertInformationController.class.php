<?php
namespace ApiInterface\Controller\Version1_0;

use Think\Controller;

class ExpertInformationController extends PublicController
{
    public $pageSize = 20;
    public $Model;

    public function __construct()
    {
        parent::__construct();
        $this->Model = D('Social_expert_information');
        $this->assign('oss_path', C('oss_path'));
    }


    /**
     * @描述：获取专家资讯列表
     * @参数：pageIndex[int] Y 页码索引
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getInformationList()
    {
        $pageIndex = getParameter('pageIndex', 'int');
        $result = $this->Model->getInformationList($pageIndex, $this->pageSize);
        $this->ajaxReturn(array('status' => 200, 'result' => $result));
    }

    /**
     * @描述：获取最新资讯
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getFirstInformation()
    {
        $result = $this->Model->getInformationList(0, 1);
        $this->ajaxReturn(array('status' => 200, 'result' => $result));
    }

    /**
     * @描述：资讯详情
     * @参数：id[int] Y 资讯ID
     * @返回：资讯HTML页面
     */
    public function informationDetails()
    {
        $id = getParameter('id', 'int');
        $result = $this->Model->getInformationDetails($id);
        $this->assign('data', $result);
        $this->display();
    }

}