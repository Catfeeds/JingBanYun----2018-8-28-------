<?php
namespace ApiInterface\Controller\Version1_1;
use Think\Controller;

class OrderMgmtController extends PublicController{

    public static $orderModel = '';
    public static $pagesize=20;
    public function __construct() {
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));
        self::$orderModel = D('Order_info');
    }

    //个人中心订单信息
    public function orderList(){
        //rsa私钥解密
       /* $data = rsaDecrypted('cxBicPItz2YHJWpKz/4mQSlDgc+khiNWweNCiJ9sCldZwZH4VsqAQUDf74Not2sgSEyUrO93c30eu+N+4BmyGkw8uhm+G2kq9fwbyP8w3bsJPg8/CNQlrNCaPCAa5egIENmm5TvTIm6kuF1XWFa/EAW58TpZJRlp+bak1E4Vt9E=');
        print_r($data);die();*/
        $token = getParameter('token','str',false);
        $rsa_data = rsaDecrypted($token);

        if ( empty($rsa_data) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '刷新失败'));
        }
        $rsa_data = getParseStr($rsa_data);

        if ( empty($rsa_data['userId']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '用户id错误'));
        } else {
            $userId = $rsa_data['userId'];
        }

        if ( empty($rsa_data['role']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '角色id错误'));
        } else {
            $role = $rsa_data['role'];
        }

        if ( empty($rsa_data['pageIndex']) || $rsa_data['pageIndex']<=0 ) {//校验
            $p = 1;
        } else {
            $p = $rsa_data['pageIndex'];
        }

        if ( $rsa_data['sing'] != getSing() ) {
            $this->ajaxReturn(array('status' => 400, 'message' => 'sing参数错误'));
        }

        if (!empty($rsa_data['orderStatus'])) {
            $filter['order_status'] = $rsa_data['orderStatus'];
            $check['order_info.order_status'] = $filter['order_status'];
        }

        $orderlist = self::$orderModel->getUserAppOrderList($userId,$role,$check,$filter,$p,self::$pagesize);//根据角色获取对应的订单

        if ( !empty($orderlist) ){
            foreach ($orderlist as $k=>$v ) {
                if ($v['order_type'] == 1) {
                    $orderlist[$k]['resource_url'] = "http://".WEB_URL.'/ApiInterface/Version1_3/DirectCar/specialColumnDetailsView?id='.$v['resources_id'].'&userId='.$rsa_data['userId'].'&role='.$rsa_data['role'];
                } else {
                    $orderlist[$k]['resource_url'] = "http://".WEB_URL.'/ApiInterface/Version1_1/KnowledgeResource/resourceDetails?id='.$v['resources_id'].'&userId='.$rsa_data['userId'].'&role='.$rsa_data['role'];
                }

                if ($v['order_type']==1 && !empty($v['resources_id'])) {

                    $join = "left join auth_teacher ON auth_teacher.id = direct_train.special_column_editor_quizzer_id";
                    $map['direct_train.id'] = $v['resources_id'];
                    $avatar = M('direct_train')->join( $join )
                        ->field('auth_teacher.avatar')
                        ->where($map)->find();
                    $orderlist[$k]['pc_cover'] = C('oss_path').$avatar['avatar'];
                }

                if($v['create_at'] > 0) {
                    $orderlist[$k]['create_at'] = "下单时间: ".date("Y-m-d H:i:s",$v['create_at']);
                }

            }
        }

        if (!empty($orderlist)) {
            $this->ajaxReturn(array('status' => 200, 'data' => $orderlist));
        } else {
            $this->ajaxReturn(array('status' => 200, 'message' =>'没有数据', 'data'=>array() ));
        }

    }

    //删除订单
    public function delOrder() {
        $token = getParameter('token','str',false);
        $rsa_data = rsaDecrypted($token);
        if ( empty($rsa_data) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => 'rsa解密失败'));
        }

        $rsa_data = getParseStr($rsa_data);

        if ( empty($rsa_data['userId']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '用户id错误'));
        } else {
            $userId = $rsa_data['userId'];
        }

        if ( empty($rsa_data['role']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '角色id错误'));
        } else {
            $role = $rsa_data['role'];
        }

        if ( empty($rsa_data['orderSn']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '订单号错误'));
        } else {
            $order_sn = $rsa_data['orderSn'];
        }
        if ( $rsa_data['sing'] != getSing() ) {
            $this->ajaxReturn(array('status' => 400, 'message' => 'sing参数错误'));
        }

        $order_sn = ltrim($order_sn,',');
        $order_sn = explode(',',$order_sn);

        $ids = self::$orderModel->delAppOrder( $userId,$role,$order_sn );

        if ( $ids ) {
            $this->ajaxReturn(array('status' => 200,'message' => '删除订单成功'));
        } else {
            $this->ajaxReturn(array('status' => 400,'message' => '删除订单失败'));
        }
    }

    //取消订单
    public function cancelOrder() {

        $token = getParameter('token','str',false);
        $rsa_data = rsaDecrypted($token);
        if ( empty($rsa_data) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => 'rsa解密失败'));
        }

        $rsa_data = getParseStr($rsa_data);

        if ( empty($rsa_data['userId']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '用户id错误'));
        } else {
            $userId = $rsa_data['userId'];
        }

        if ( $rsa_data['sing'] != getSing() ) {
            $this->ajaxReturn(array('status' => 400, 'message' => 'sing参数错误'));
        }

        if ( empty($rsa_data['role']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '角色id错误'));
        } else {
            $role = $rsa_data['role'];
        }

        if ( empty($rsa_data['orderSn']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '订单号错误'));
        } else {
            $order_sn = $rsa_data['orderSn'];
        }

        $order_sn = ltrim($order_sn,',');
        $order_sn = explode(',',$order_sn);

        $ids = self::$orderModel->cancelAppOrderModel( $userId,$role,$order_sn );
        if ( $ids ) {

            foreach ( $order_sn as $k=>$order_sn_v ) { //app取消订单进行推送
                $orderinfo = self::$orderModel->orderAppSnGetOrderInfo( $order_sn_v,$userId,$role );
                $parameters = [];
                $parameters = array(
                    'msg' => array(
                        $orderinfo['name'],
                    ),
                    'url' => array( 'type' => 0)
                );
                $controller_obj=new \Home\Controller\MessageController();
                $controller_obj->addPushUserMessage('ORDER_CLOSED',$orderinfo['user_role'],$orderinfo['user_id'],$parameters);
            }

            $this->ajaxReturn(array('status' => 200,'message' => '取消订单成功'));
        } else {
            $this->ajaxReturn(array('status' => 400,'message' => '取消订单失败'));
        }
    }
    //根据订单号查询订单
    public function accordOrdergetInfo() {
        $token = getParameter('token','str',false);
        $rsa_data = rsaDecrypted($token);
        if ( empty($rsa_data) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => 'rsa解密失败'));
        }

        $rsa_data = getParseStr($rsa_data);

        if ( empty($rsa_data['userId']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '用户id错误'));
        } else {
            $userId = $rsa_data['userId'];
        }

        if ( empty($rsa_data['role']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '角色id错误'));
        } else {
            $role = $rsa_data['role'];
        }

        if ( $rsa_data['sing'] != getSing() ) {
            $this->ajaxReturn(array('status' => 400, 'message' => 'sing参数错误'));
        }

        if ( empty($rsa_data['orderSn']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '用户id错误'));
        } else {
            $orderSn = $rsa_data['orderSn'];
        }
        $info = self::$orderModel->orderAppSnGetOrderInfo( $orderSn,$userId,$role );

        if(!empty( $info ) && !empty($info['pay_fee'])) {
            $this->ajaxReturn(array('status' => 200,'data'=>$info));
        } else {
            $this->ajaxReturn(array('status' => 400,'message'=>'没有数据', 'data'=>array() ));
        }


    }

    //根据订单入库订单信息
    public function addOrderGo() {
        $token = getParameter('token','str',false);
        $rsa_data = rsaDecrypted($token);
        if ( empty($rsa_data) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => 'rsa解密失败'));
        }

        $rsa_data = getParseStr($rsa_data);

        if ( empty($rsa_data['userId']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '用户id错误'));
        } else {
            $userId = $rsa_data['userId'];
        }

        if ( empty($rsa_data['role']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '角色id错误'));
        } else {
            $role = $rsa_data['role'];
        }

        if ( $rsa_data['sing'] != getSing() ) {
            $this->ajaxReturn(array('status' => 400, 'message' => 'sing参数错误'));
        }

        if ( empty($rsa_data['id']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '资源id错误'));
        }

        if ( empty($rsa_data['real_price']) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => '资源价格错误'));
        }

        $userOrder = self::$orderModel->getResourceByOrder( $rsa_data['id'],$userId,$role );//根据角色获取对应的订单

        if( empty($userOrder) ) { //直接跳转到成功的页面
            $order_data = [];
            $order_data['order_sn'] = StrOrderOne();
            $order_data['user_role'] = $role;
            $order_data['resources_id'] = $rsa_data['id'];
            $order_data['user_id'] = $userId;
            $order_data['pay_fee'] = $rsa_data['real_price'];
            $order_data['pay_source'] = isIos();
            $order_data['create_at'] = time();
            $id = self::$orderModel->addOrder($order_data);//入库订单
            if ( $id ) {
                $order_info = self::$orderModel->orderIdGetOrderInfo( $id,$userId,$role );//获取订单信息
                $this->ajaxReturn(array('status' => 200,'data'=>$order_info['order_sn'],'message'=>'入库成功'));
            } else {
                $this->ajaxReturn(array('status' => 400, 'message' => '入库失败', 'data'=>array() ));
            }
        } else {//已经下单
            if($userOrder['order_status'] == 3 || $userOrder['is_delete'] == 2) {

                $order_data = [];
                $order_data['order_sn'] = StrOrderOne();
                $order_data['user_role'] = $role;
                $order_data['resources_id'] = $rsa_data['id'];
                $order_data['user_id'] = $userId;
                $order_data['pay_fee'] = $rsa_data['real_price'];
                $order_data['pay_source'] = isIos();
                $order_data['create_at'] = time();
                $id = self::$orderModel->addOrder($order_data);//入库订单
                if ( $id ) {
                    $order_info = self::$orderModel->orderIdGetOrderInfo( $id,$userId,$role );//获取订单信息
                    $this->ajaxReturn(array('status' => 200,'data'=>$order_info['order_sn'],'message'=>'入库成功'));
                } else {
                    $this->ajaxReturn(array('status' => 400, 'message' => '入库失败', 'data'=>array() ));
                }

            } else {
                $this->ajaxReturn(array('status' => 200,'data'=>$userOrder['order_sn'],'message'=>'入库成功'));
            }
        }
    }


	public function orderConfirm(){
		$this->display();
	}
	public function orderPay(){
		$this->display();
	}
	public function orderPaySuccess(){
		$this->display();
	}

}
