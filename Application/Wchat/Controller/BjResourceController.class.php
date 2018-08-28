<?php
namespace Wchat\Controller;
use Think\Controller;
use Common\Common\JSSDK;
define('SUCCESS_FLAG','SUCCESS');
define('FAIL_FLAG','FAIL');
define('ROLE_NUMBER',2);//这里为减去的数字。资源和京版资源角色从0开始!
import('Vendor.Alipay.Alipay');
import('Vendor.WeixinPay.Weixin');

define('FAVOR_STATUS',1);
class BjResourceController extends Controller
{
    public static $model = '';
    public static $orderModel = '';
    public function __construct(){
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));
        self::$model = D('Knowledge_resource');
        self::$orderModel = D('Order_info');
    }
    
    //国学资源
    public function bjResourceList(){
        /*session('teacher',null);
        session('student',null);
        session('parent',null);*/
        $check['knowledge_resource_attr.column_id'] = 6;
        $check['knowledge_resource.putaway_status'] = 1;
        $check['knowledge_resource.status'] = 1;
        $check['knowledge_resource.flag'] = 1;
        //$check['knowledge_resource.status'] = 1;

        $result=self::$model->getWcahtResourceData($check);
        foreach($result['data'] as $key=>$val){
            if($val['id'] == GUOXUE_ID)
            {
                $result['data'] = [];
                $result['data'][] = $val;
                break;
            }
        }
        $this->assign('result',$result['data']);
        $this->display_nocache();
    }

    //查看国学详情
    public function bjResourceDetails() {
        $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        $this->assign('WEB_URL',WEB_URL);

        $id = getParameter('id','int');
        if(GUOXUE_ID != $id ) {
            $userinfo = $this->isLogin();
            //查看资源的详情进行开始购买
            $this->resourceDetails($id, $userinfo['userId'], $userinfo['role']);
        }
        else
        {
            A('Home/Common')->getUserIdRole($userId,$role);//判断角色
            if ( $userId == -1 && $role == -1 ) {
                if(isset($_GET['bookId']))
                {
                    $userinfo = $this->isLogin();
                    //查看资源的详情进行开始购买
                    $this->resourceDetails($id, $userinfo['userId'], $userinfo['role']);
                }
                else {
                    $resourceDetails = self::$model->getResourceDetailInfoWithoutMulti($id,$role,$userId);
                    $this->assign('resourceDetailsother',$resourceDetails);
                    $this->assign('path',$_SERVER['REQUEST_URI']);
                    $this->resourceDetails($id, $userId, $role);
                }
            }
            else {
                $this->assign('bookId',$_GET['bookId']);
                $this->resourceDetails($id, $userId, $role);
            }
        }

    }

    //判断是否登陆 并返回用户id和用户角色
    public function isLogin() {

        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId,$role);//判断角色

        if ( $userId == -1 && $role == -1 ) {//是否登陆授权 //跳转到登陆界面
                $url = base64_encode($_SERVER['REQUEST_URI']);
                header("Location:/index.php/ApiInterface/Version1_1/RegisterQuick/loginQuick?url=" . $url);
        }
        $data['userId'] = $userId;
        $data['role'] = $role;
        return $data;
    }

    //马上购买
    public function buyRightNow() {

        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId,$role);//判断角色

        if ( $userId == -1 && $role == -1 ) {//是否登陆授权 //跳转到登陆界面
            $url = base64_encode($_SERVER['REQUEST_URI']);
            header("Location:/index.php/ApiInterface/Version1_1/RegisterQuick/loginQuick?url=" . $url);
        }
        $id = getParameter('id','int');
        $res = $this->getResoure($id,$role,$userId);


        $userOrder = self::$orderModel->getResourceByOrder( $id,$userId,$role );//根据角色获取对应的订单

        if($userOrder['order_status'] ==2 ){ //直接跳转到成功的页面
            $this->redirect('bjResourceDetails',array('id'=>$id));
            die();
        }


        $this->assign('res',$res);
        $this->display_nocache('orderConfirm');
    }

    public function getResoure($id,$role,$userId) {
        $resourceDetails = D('Knowledge_resource')->ResourceDetailInfoWithoutMulti($id,$role,$userId);
        return $resourceDetails;
    }

    //去支付
    //跳转到支付页面
    public function payment() {

        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId,$role);//判断角色

        if ( $userId == -1 && $role == -1 ) {//是否登陆授权 //跳转到登陆界面
            $url = base64_encode($_SERVER['REQUEST_URI']);
            header("Location:/index.php/ApiInterface/Version1_1/RegisterQuick/loginQuick?url=" . $url);
        }

        $filter['id']=getParameter('id','str',false);
        if (empty($filter['id'])){ //判断订单号
            header("HTTP/1.1 404 Not Found");
            exit();
        }

        $res_info = D('Knowledge_resource')->getResourceDetailInfo($filter['id'],$role,$userId);

        if (!empty($res_info)) {

            $userOrder = self::$orderModel->getResourceByOrder( $filter['id'],$userId,$role );//根据角色获取对应的订单

            if( empty($userOrder) ){ //直接跳转到成功的页面
                $order_data=[];
                $order_data['order_sn'] = StrOrderOne();
                $order_data['user_role'] = $role;
                $order_data['resources_id'] = $res_info['id'];
                $order_data['user_id'] = $userId;
                $order_data['pay_fee'] = $res_info['real_price'];
                $order_data['pay_source'] = 1;
                $order_data['create_at'] = time();
                $id = self::$orderModel->addOrder( $order_data );//入库订单
                $order_info = self::$orderModel->orderIdGetOrderInfo( $id,$userId,$role );//获取订单信息
                $this->redirect('rightPayment',array('order_sn'=>$order_info['order_sn']));
            } else {
                if($userOrder['order_status'] == 3 || $userOrder['is_delete'] == 2) {
                    $order_data=[];
                    $order_data['order_sn'] = StrOrderOne();
                    $order_data['user_role'] = $role;
                    $order_data['resources_id'] = $res_info['id'];
                    $order_data['user_id'] = $userId;
                    $order_data['pay_fee'] = $res_info['real_price'];
                    $order_data['pay_source'] = 1;
                    $order_data['create_at'] = time();
                    $id = self::$orderModel->addOrder( $order_data );//入库订单
                    $order_info = self::$orderModel->orderIdGetOrderInfo( $id,$userId,$role );//获取订单信息
                    $this->redirect('rightPayment',array('order_sn'=>$order_info['order_sn']));

                } else {
                    $this->redirect('rightPayment',array('order_sn'=>$userOrder['order_sn']));
                }
            }

        } else {
            header("HTTP/1.1 404 Not Found");
            exit();
        }

    }

    //立即支付
    public function rightPayment() {
        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId,$role);//判断角色

        if ( $userId == -1 && $role == -1 ) {//是否登陆授权 //跳转到登陆界面
            $url = base64_encode($_SERVER['REQUEST_URI']);
            header("Location:/index.php/ApiInterface/Version1_1/RegisterQuick/loginQuick?url=" . $url);
        }

        $filter['order_sn']=getParameter('order_sn','str',false);
        if (empty($filter['order_sn'])){ //判断订单号
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $userOrder = self::$orderModel->orderSnGetOrderInfo( $filter['order_sn'],$userId,$role );//根据角色获取对应的订单

        if($userOrder['order_status'] ==2 ){ //直接跳转到成功的页面
            $this->redirect('orderPaySuccess',array('order_sn'=>$userOrder['order_sn'],'resources_id'=>$userOrder['resources_id']));
        }

        if ($userOrder['order_status'] ==3) {
            header('Location:/index.php?m=Wchat&c=BjResource&a=bjResourceDetails&id='.$userOrder['resources_id']);
            die("非法订单");
        }
        if (empty($userOrder)) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }

        if (!empty($userOrder['name'])){
            $userOrder['name'] = mb_substr($userOrder['name'], 0, 10, 'utf-8');
        }

        $order_data["desc"]=trimall($userOrder['name']);
        //$order_data['attach']=$userOrder['name'];
        $order_data['order_sn']=$userOrder['order_sn'].'_'.date("YmdHis");
        $order_data['pay_fee']=$userOrder['pay_fee']*100;

        //$order_data['goods_id']='4483094';
        $object=new \Weixin();
        $data=$object->createjsSdkCode($order_data);
        $this->assign('jsApiParameters',$data['jsApiParameters']);
        $this->assign('editAddress',$data['editAddress']);

        $this->assign('userOrder',$userOrder);

        $this->display_nocache('orderPay');
    }

    //支付成功跳转页面
    public function orderPaySuccess() {
        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId,$role);//判断角色

        if ( $userId == -1 && $role == -1 ) {//是否登陆授权 //跳转到登陆界面
            $url = base64_encode($_SERVER['REQUEST_URI']);
            header("Location:/index.php/ApiInterface/Version1_1/RegisterQuick/loginQuick?url=" . $url);
        }
        $filter['order_sn']=getParameter('order_sn','str',false);
        if (empty($filter['order_sn'])){ //判断订单号
            header("HTTP/1.1 404 Not Found");
            exit();
        }

        $userOrder = self::$orderModel->orderSnGetOrderInfo( $filter['order_sn'],$userId,$role );//根据角色获取对应的订单
        if ( $userOrder['order_status'] != 2 ) {
            header("HTTP/1.1 404 Not Found");
            exit();
        }

        $this->assign('userOrder',$userOrder);
        $this->display_nocache();
    }

    /**
     * @描述：获取资源详情页
     * @参数：id[int] Y 资源ID
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function resourceDetails($id,$userId,$role)
    {
        /*$id = getParameter('id','int');
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');*/
        $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
        $this->assign('WEB_URL',WEB_URL);

        $resourceDetails = self::$model->getResourceDetailInfoWithoutMulti($id,$role,$userId);
        $contactInfo = self::$model->getResourceContactFiles($id);
        $recommendInfo = self::$model->getResourceContactRecommend($id);

        $orderInfo = D('Order_info')->getPaymentOrderResource($id,$userId,$role);
        $controller = new \Home\Controller\CommonController();
        $orderStatus = $controller->returnStatus($id,$userId,$role);

        $this->assign('resourceDetails',$resourceDetails);
        $this->assign('contactInfo',$contactInfo);
        $this->assign('recommendInfo',$recommendInfo);
        $this->assign('orderStatus',$orderStatus);
        $this->assign('id',$id);

        $this->assign('orderInfo',$orderInfo);
        $this->assign('userId',$userId);
        $this->assign('role',$role);

        $subResourceIdList = json_decode(GUOXUE_SUBRESOURCE_IDLIST,true);
        if($id == GUOXUE_ID || in_array($id,$subResourceIdList)){
            if($id != GUOXUE_ID){
                //获取主套餐是否已经购买
                $result = D('Knowledge_resource')->getResourceDetailInfoWithoutMulti(GUOXUE_ID, $role, $userId);
                $is_allowed_browse = $controller->returnStatus(GUOXUE_ID, $userId, $role); //调公共的方法判断状态
                $this->assign('main_is_allowed_browse', $is_allowed_browse);
                $this->assign('main_data', $result);
            }
            else{
                $is_allowed_browse_array = [];
                foreach($subResourceIdList as $key=>$id){
                    $is_allowed_browse_array[] = $controller->returnStatus($id, $userId, $role); //调公共的方法判断状态
                }
                $this->assign('is_allowed_browse_array', $is_allowed_browse_array);
            }
            $this->assign('subResourceIdList',$subResourceIdList);
            $this->display('bjResourceSinology');
        }
        else{
            $this->display_nocache();
        }

    }

    /**
     * @描述：收藏/取消资源
     * @参数：id[int] Y 资源ID
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：status[int] Y 设置收藏状态 1--收藏 2--取消收藏
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */

    public function favorResource()
    {
        $id = getParameter('id','int');
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $status = getParameter('status','int');
        if(FAVOR_STATUS == $status)
        {
            self::$model->deleteResourceCollect($id,$role,$userId);
            $result = self::$model->addResourceCollect(array('resource_id' => $id , 'role' => $role - ROLE_NUMBER , 'user_id' => $userId));
            if($result)
                $this->showMessage(200,'收藏成功',array());
            else
                $this->showMessage(500,'收藏失败',array());
        }
        else
        {
            $result = self::$model->deleteResourceCollect($id,$role - ROLE_NUMBER,$userId);
            if($result)
                $this->showMessage(200,'取消收藏',array());
            else
                $this->showMessage(500,'取消收藏',array());
        }
    }

}