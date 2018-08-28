<?php
namespace ApiInterface\Controller\Version1_2;

use Common\Common\JSSDK;
define('FAVOR_STATUS',1);
define('DEFAVOR_STATUS',2);

define('NEWS_PROPAGANDA_COLUMN',7);
define('TEACH_STATUS_COLUMN',8);
define('AWARD_WORK_COLUMN',9);
define('HOT_TOPIC_COLUMN',10);
define('EDU_NEWS_COLUMN',11);

class ExpertInformationController extends PublicController
{
    public $pageSize = 15;
    public $model;
    private $columnArray;
    public function __construct() {
        parent::__construct();
        $this->model = D('Social_expert_information');
        $this->assign('oss_path', C('oss_path'));
        $this->columnArray = array(
            array('id'=>TEACH_STATUS_COLUMN,'name'=>'教学动态','style'=>1,'count'=>3),
            array('id'=>AWARD_WORK_COLUMN,'name'=>'获奖作品','style'=>2,'count'=>9),
            array('id'=>HOT_TOPIC_COLUMN,'name'=>'热点话题','style'=>3,'count'=>5),
            array('id'=>EDU_NEWS_COLUMN,'name'=>'教育新闻','style'=>4,'count'=>5),
        );
    }
    function getPageSize(){
        return $this->pageSize;
    }

    private function addOssPath($array,$field)
    {
        $ossPath = C('oss_path');
        for($i=0;$i<sizeof($array);$i++)
        {
            if(false === strpos($array[$i][$field],'http://'))
                $array[$i][$field] = $ossPath . $array[$i][$field];
        }
        return $array;
    }

    /**
     * @描述：获取资讯首页列表
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getHomeInformationList()
    {

        $user_id = getParameter('userId','int');
        $role = getParameter('role','int');
        $result = array();
        for($i=0;$i<sizeof($this->columnArray);$i++)
        {
            $result[$i]['id'] = $this->columnArray[$i]['id'];
            $result[$i]['style'] = $this->columnArray[$i]['style'];
            $result[$i]['name'] = $this->columnArray[$i]['name'];
            $result[$i]['data'] = $this->model->getInformationList(1,$this->columnArray[$i]['count']+1,$this->columnArray[$i]['id']);
        }

        $convertArray = array(
            array('title'=> TYPE_FIELD),array('title'),
            array('id' =>TYPE_FIELD) ,array('id'),
            array(C('oss_path') =>TYPE_STRING,'mobile_cover' =>TYPE_FIELD) ,array('img_url'),
            array('浏览量：' =>TYPE_STRING,'browse_count'=> TYPE_FIELD),array('content1'),
            array('c_time'=>TYPE_FIELD),array('content2'),
            array('http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.'Version1_2' . '/ExpertInformation/informationDetails?id='  => TYPE_STRING,'id' =>TYPE_FIELD) ,array('url'),
        );
        for($i=0;$i<sizeof($this->columnArray);$i++) {
            $result[$i]['data'] = fieldsCompose($result[$i]['data'],$convertArray);
            for($j=0;$j<sizeof($result[$i]['data']);$j++)
            {
                if($result[$i]['data'][$j]['img_url'] == C('oss_path'))
                    $result[$i]['data'][$j]['img_url'] = '';
            }
            if(sizeof($result[$i]['data']) <= $this->columnArray[$i]['count'])
                unset($result[$i]['id']);
            $result[$i]['data'] = array_slice( $result[$i]['data'],0,$this->columnArray[$i]['count']);
        }
        if((strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad') !== false|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')) != false) {
            $result[2]['data'][] = $result[2]['data'][count($result[2]['data'])-1];
        }
        $this->showMessage( 200,'success',$result);
    }

    /**
     *   获取栏目种类
     *
     */
    public function getColumnType()
    {

        $this->showMessage(200,'success',$this->columnArray);
    }

    /**
     * @描述：获取筛选后的资讯列表
     * @参数：column[int] N 栏目类型
     * @参数：keyword[str] N 关键字
     * @参数：pageIndex[int] N 页码
     * @参数：pageSize[int] N 条数
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */

    public function getFilteredInfoList()
    {
        $column = getParameter('column','int',false);
        $keyword = trim(getParameter('keyword','str',false));
        $keyword=strtr($keyword,array('%'=>'\%', '_'=>'\_', '\\'=>'\\\\'));

        $pageIndex = getParameter('pageIndex','int',false);
        if(empty($pageIndex))
            $pageIndex = 1;
        $pageSize = getParameter('pageSize','int',false);
        if(empty($pageSize))
            $pageSize = 20;
        $_GET['p'] = $pageIndex;
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $result = $this->model->getInformationList($pageIndex,$pageSize,$column,$keyword);
        if($column == AWARD_WORK_COLUMN)
        {
            $convertArray = array(
                array('title'=> TYPE_FIELD),array('title'),
                array('id' =>TYPE_FIELD) ,array('id'),
                array(C('oss_path') =>TYPE_STRING,'mobile_cover' =>TYPE_FIELD) ,array('img_url'),
                array('浏览量：' =>TYPE_STRING,'browse_count'=> TYPE_FIELD),array('content1'),
                array('c_time'=>TYPE_FIELD),array('content2'),
                array('http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.'Version1_2' . '/ExpertInformation/informationDetails?id='  => TYPE_STRING,'id' =>TYPE_FIELD) ,array('url'),
            );
        }
        else{
            $convertArray = array(
                array('title'=> TYPE_FIELD),array('title'),
                array('id' =>TYPE_FIELD) ,array('id'),
                array(C('oss_path') =>TYPE_STRING,'mobile_cover' =>TYPE_FIELD) ,array('img_url'),
                array('浏览量：' =>TYPE_STRING,'browse_count'=> TYPE_FIELD),array('content1'),
                array('c_time'=>TYPE_FIELD),array('content2'),
                array('http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.'Version1_2' . '/ExpertInformation/informationDetails?id='  => TYPE_STRING,'id' =>TYPE_FIELD) ,array('url'),
            );
        }
        $result = fieldsCompose($result,$convertArray);
        for($j=0;$j<sizeof($result);$j++)
        {
            if($result[$j]['img_url'] == C('oss_path'))
                $result[$j]['img_url'] = '';
        }
        $this->showMessage( 200,'success',$result);
    }

    //判断是否登陆 并返回用户id和用户角色
    public function isLogin() {

        $userId = -1;
        $role = -1;

        $controller = new \Home\Controller\CommonController();
        $controller->getUserIdRole($userId,$role);//判断角色

        if ( $userId == -1 && $role == -1 ) {//是否登陆授权 //跳转到登陆界面
            $url = base64_encode($_SERVER['REQUEST_URI']);
            header("Location:/index.php/ApiInterface/Version1_1/RegisterQuick/loginQuick?url=" . $url);
        }
        $data['userId'] = $userId;
        $data['role'] = $role;
        return $data;
    }

    /**
     * @描述：资讯详情
     * @参数：id[int] Y 资讯ID
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回：资讯HTML页面
     */
    public function informationDetails()
    {
        $id = getParameter('id', 'int');
        $flag = getParameter('flag','int',false);
        $flagtrue = getParameter('flagtrue','int',false);

        $is_weixin = is_weixin();
        if ($is_weixin == true && $flagtrue !=='send' ) {
            $weixindata = $this->isLogin();
            $userId = $weixindata['userId'];
            $role = $weixindata['role'];
        } else {
            if($flag != 1) {
                $userId = getParameter('userId', 'int');
                $role = getParameter('role', 'int');
            }
        }

        if(!(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android'))) {
            redirect('/index.php?m=Home&c=ExpertInformation&a=expertInformationDetails&id='.$id);
        }
        $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
        $signPackage = $jssdk->GetSignPackage();
        $column = $this->model->getInfoColumn($id);
        $recommendResult = $this->model->getRecommendInfo($column,$id);
        $result = $this->model->getInformationDetails($id);
        $convertArray = array(
            array('title'=> TYPE_FIELD),array('title'),
            array('id' =>TYPE_FIELD) ,array('id'),
            array(C('oss_path') =>TYPE_STRING,'mobile_cover' =>TYPE_FIELD) ,array('img_url'),
            array('浏览量：' =>TYPE_STRING,'browse_count'=> TYPE_FIELD),array('content1'),
            array('c_time'=>TYPE_FIELD),array('content2'),
            array('http://'.$_SERVER['SERVER_NAME'].'/ApiInterface/'.'Version1_2' . '/ExpertInformation/informationDetails?id='  => TYPE_STRING,'id' =>TYPE_FIELD,"&userId=$userId&role=$role" =>TYPE_STRING) ,array('url'),
        );
        $recommendResult = fieldsCompose($recommendResult,$convertArray);
        for($j=0;$j<sizeof($recommendResult);$j++)
        {
            if($recommendResult[$j]['img_url'] == C('oss_path'))
                $recommendResult[$j]['img_url'] = '';
        }
        $this->model->incBrowseCount($id);
        foreach($this->columnArray as $key => $val)
        {
            if($val['id'] == $result['type'])
            {
               $this->assign('columnInfo',$val);
               break;
            }
        }

        $this->assign('flag',$flag);
        $this->assign('column',$column);
        $this->assign('data', $result);
        $this->assign('recommendData', $recommendResult);
        $this->assign('signPackage',$signPackage);
        if($this->model->getInfoColumn($id) == AWARD_WORK_COLUMN)
            $this->display('winningWorksDetails');
        else
            $this->display();
    }
}
?>