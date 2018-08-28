<?php
namespace Home\Controller;
use Think\Controller;
use Common\Common\SMS;

class OrderMgmtController extends PublicController{

    public static $orderModel = '';

    public function __construct() {
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));
        self::$orderModel = D('Order_info');
    }


    //发送手机短信通知
    public function sendIphone() {
        $smsapi = new SMS();
        //$is_send = $smsapi->templateSMSOrder(17600774286,'100,玫瑰花,101454654574878745452412412');
        $is_send = $smsapi->templateSMSCancelOrder(17600774286,'玫瑰花');
        print_r($is_send);die();
    }

    //取消订单
    public function sendCancelOrderMessage( $data ) {
        $smsapi = new SMS();
        $smsapi->templateSMSCancelOrder($data['iphone'],$data['name']);
    }

    //个人中心订单信息
    public function orderMgmt(){
        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId,$role);//判断角色
        if ( $userId == -1 && $role == -1 ) {//是否登陆授权
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        layoutHtml($role,'withouticon');//加载html头部文件

        $filter['name']=getParameter('name','str',false);
        $mca=getParameter('mca','str',false);
        $filter['order_status']=getParameter('order_status','int',false);

        $filter['start']=getParameter('start','str',false);
        $filter['end']=getParameter('end','str',false);

        if ( $mca == 'action' ) {
            $this->assign('kw',1);
        }

        if (!empty($filter['start'])) {
            $startime = strtotime($filter['start']);
            $startime = date('Y-m-d 00:00:00', $startime);
            $check['order_info.create_at'] = array('egt',strtotime($startime));
        }

        if (!empty($filter['end'])) {
            $endtime = strtotime($filter['end']);
            $endtime = date('Y-m-d 23:59:59', $endtime);
            $check['order_info.create_at'] = array('elt',strtotime($endtime));
        }

        if (!empty($filter['start']) && !empty($filter['end']) ){
            $startime = strtotime($filter['start']);
            $startime = date('Y-m-d 00:00:00', $startime);
            $endtime = strtotime($filter['end']);
            $endtime = date('Y-m-d 23:59:59', $endtime);
            $check['order_info.create_at'] = array(array('egt',strtotime($startime)),array('elt',strtotime($endtime))) ;
        }

        if(!empty($filter['name']))   $check['knowledge_resource.name']=array('like', '%' . $filter['name']. '%');
        if (!empty($filter['order_status'])) $check['order_info.order_status'] = $filter['order_status'];


        $orderlist = self::$orderModel->getUserWebOrderList($userId,$role,$check,$filter);//根据角色获取对应的订单

        foreach ($orderlist['list'] as $k=>&$v) {

            if ($v['order_type']==1 && !empty($v['resources_id'])) {

                $join = "left join auth_teacher ON auth_teacher.id = direct_train.special_column_editor_quizzer_id";
                $map['direct_train.id'] = $v['resources_id'];
                $avatar = M('direct_train')->join( $join )
                    ->field('auth_teacher.avatar')
                    ->where($map)->find();
                $v['pc_cover'] = C('oss_path').$avatar['avatar'];
            }
        }

        $this->assign('parameter',$filter); //搜索条件
        $this->assign('module', '个人中心');
        $this->assign('page', $orderlist['show']);
        $this->assign('list', $orderlist['list']);
        $this->display();
    }

    //删除订单
    public function delOrder() {
        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId,$role);//判断角色
        if ( $userId == -1 && $role == -1 ) {//是否登陆授权
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $order_sn=getParameter('order_sn','str',false);
        $order_sn = ltrim($order_sn,',');
        $order_sn = explode(',',$order_sn);
        if (empty($order_sn)) {
            exit();
        }
        foreach($order_sn as $key=>$val){
            $orderInfo = self::$orderModel->getOrderInfo($val);
            if($orderInfo['order_status'] == 1) //未支付
            {
                $this->ajaxReturn('未支付的订单无法删除');
            }
        }
        $ids = self::$orderModel->delOrder( $order_sn );
        if ( $ids ) {
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('删除失败');
        }
    }

    //取消订单
    public function cancelOrder() {
        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId,$role);//判断角色
        if ( $userId == -1 && $role == -1 ) {//是否登陆授权
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $order_sn=getParameter('order_sn','str',false);
        $order_sn = ltrim($order_sn,',');
        $order_sn = explode(',',$order_sn);
        if (empty($order_sn)) {
            exit();
        }
        $ids = self::$orderModel->cancelOrderModel( $order_sn );

        if ( $ids ) {

            foreach ( $order_sn as $k=>$order_sn ) {
                $orderinfo = self::$orderModel->orderAppSnGetOrderInfo( $order_sn,$userId,$role );
                $parameters = [];
                $parameters = array(
                    'msg' => array(
                        $orderinfo['name'],
                    ),
                    'url' => array( 'type' => 0)
                );
                A('Home/Message')->addPushUserMessage('ORDER_CLOSED',$orderinfo['user_role'],$orderinfo['user_id'],$parameters);

                if ( $orderinfo['user_role']==2 ) { //教师

                    $userinfo = D('Auth_teacher')->getTeachInfo( $orderinfo['user_id'] );
                    $iphone = $userinfo['telephone'];
                }

                if ( $orderinfo['user_role']==3 ) {//学生
                    $userinfo = D('Auth_student')->getStudentInfo( $orderinfo['user_id'] );
                    $iphone = $userinfo['parent_tel'];
                }

                if ( $orderinfo['user_role']==4 ) {//家长
                    $userinfo = D('Auth_parent')->getParentInfo( $orderinfo['user_id'] );
                    $iphone = $userinfo['telephone'];
                }

                if (!empty($iphone)) {
                    $sendmessage['iphone'] = $iphone;
                    $sendmessage['name'] = $orderinfo['name'];
                    $this->sendCancelOrderMessage( $sendmessage );
                }

            }



            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('error');
        }
    }

}
