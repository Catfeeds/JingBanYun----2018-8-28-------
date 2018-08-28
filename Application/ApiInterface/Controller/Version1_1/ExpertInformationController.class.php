<?php
namespace ApiInterface\Controller\Version1_1;

use Think\Controller;
use Common\Common\JSSDK;
define('NEWS_PROPAGANDA_COLUMN',7);
define('TEACH_STATUS_COLUMN',8);
define('AWARD_WORK_COLUMN',9);
define('HOT_TOPIC_COLUMN',10);
define('EDU_NEWS_COLUMN',11);
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
        if(!(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android'))) {
            redirect('/index.php?m=Home&c=ExpertInformation&a=expertInformationDetails&id='.$id);
        }
        $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
        $signPackage = $jssdk->GetSignPackage();


        $result = $this->Model->getInformationDetails($id);
        $this->Model->incBrowseCount($id);
        $column = $this->Model->getInfoColumn($id);
        $recommendResult = $this->Model->getRecommendInfo($column,$id);
        $convertArray = array(
            array('title'=> TYPE_FIELD),array('title'),
            array('id' =>TYPE_FIELD) ,array('id'),
            array(C('oss_path') =>TYPE_STRING,'mobile_cover' =>TYPE_FIELD) ,array('img_url'),
            array('浏览量：' =>TYPE_STRING,'browse_count'=> TYPE_FIELD),array('content1'),
            array('c_time'=>TYPE_FIELD),array('content2'),
            array('http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.'Version1_1' . '/ExpertInformation/informationDetails?id='  => TYPE_STRING,'id' =>TYPE_FIELD) ,array('url'),
        );
        $recommendResult = fieldsCompose($recommendResult,$convertArray);
        $this->assign('data', $result);
        $this->assign('signPackage',$signPackage);
        $this->assign('recommendData', $recommendResult);
        if($column == AWARD_WORK_COLUMN)
         $this->display('winningWorksDetails');
        else
         $this->display();
    }

}