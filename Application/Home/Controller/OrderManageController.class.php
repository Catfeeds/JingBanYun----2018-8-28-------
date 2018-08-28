<?php
namespace Home\Controller;
use Think\Controller;
use Common\Common\WAP;
use Common\Common\SMS;

define('SUCCESS_FLAG','SUCCESS');
define('FAIL_FLAG','FAIL');
import('Vendor.Alipay.Alipay');
import('Vendor.WeixinPay.Weixin');
header("Content-type: text/html; charset=utf-8");

class OrderManageController extends PublicController{


    public static $orderModel = '';

    public function __construct() {
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));
        self::$orderModel = D('Order_info');
    }

    //发送手机短信通知
    public function sendIphone( $data ) {
        $smsapi = new SMS();
        $row = $smsapi->templateSMSOrder($data['iphone'],$data['msg']);
        //$is_send = $smsapi->templateSMSCancelOrder(17600774286,'玫瑰花');
        //print_r($is_send);die();
        return $row;
    }

    /*
     * 支付宝跳转阿里进行支付
     */
    public function jump_alipay(){
        $alipay=new \Alipay();
        $data['out_trade_no'] = '20150320010101001'.rand(100,5555);
        $data['subject']='三星（SAMSUNG）Galaxy S8（SM-G9500）4GB+64GB版 谜夜黑 移动联通电信4G手机 双卡双待';
        $data['body']='【每天10点-11点开启限量预售】【预售价为虚拟价，最终成交价以发布会为准】三星S8！';
        $data['total_amount']='1000';
        $alipay->doAlipay($data);
    }


    /*
     * 支付宝异步地址
     */
    function notifyAlipay(){
        $model=M('bb');
        $data['xx']= json_encode($_GET);
        $data['yy']= json_encode($_POST);
        $model->add($data); die;

        $data='{
                "total_amount": "0.01",
                "buyer_id": "2088102172121676",
                "trade_no": "2017051321001004670200142912",
                "body": "【每天10点-11点开启限量预售】【预售价为虚拟价，最终成交价以发布会为准】三星S8！",
                "notify_time": "2017-05-13 10:08:58",
                "subject": "三星（SAMSUNG）Galaxy S8（SM-G9500）4GB+64GB版 谜夜黑 移动联通电信4G手机 双卡双待",
                "sign_type": "RSA2",
                "auth_app_id": "2016080700188034",
                "charset": "UTF-8",
                "notify_type": "trade_status_sync",
                "invoice_amount": "0.01",
                "out_trade_no": "201503200101010012086",
                "trade_status": "TRADE_SUCCESS",
                "gmt_payment": "2017-05-13 10:08:57",
                "version": "1.0",
                "point_amount": "0.00",
                "sign": "kOU6mXr67pn5+7fGIKPlf/Cz6x+Qv2mWvV/spSAabayvEbfiPjWRJQTQYrWxHOeq45dvT06q/5vgIiS5mAvKL+Pvsd7u8J0VvQhcXnGJAF656eSVDJ+7/L6jqBu+299UYa+NC+0md/f6L37+Wth4s3L7Ra7K/HQAL7VLUkpbNl/8TXBRGGCSjPU9zvJi32y9t6AXyaRlW60wFz+q2R+iLthG4KbVl5/yX9bxTN00L0RSaPSGdHD/CSPpJVoxAgnUeXeHjU8/NYhrfjTlcW4Bp3xBQQ+bIJR0GMsrnOyi/iNBZlJIEzBk28zMrQ8kyFPTPFNamWWNKchsKeP57r3c6w==",
                "gmt_create": "2017-05-13 10:08:38",
                "buyer_pay_amount": "0.01",
                "receipt_amount": "0.01",
                "fund_bill_list": "[{\"amount\":\"0.01\",\"fundChannel\":\"ALIPAYACCOUNT\"}]",
                "app_id": "2016080700188034",
                "seller_id": "2088102170346336",
                "notify_id": "2cb08ad49c3223117281542f39a63d8l66"
            }';
        $data=json_decode($data,true);
        $GLOBALS['alipay_notify_data']=$data;

        $alipay=new \Alipay();
        $verify_status=$alipay->checkSign($data);
        if($verify_status==true){
            //验证支付宝信息 修改数据状态!
            $alipayData=$this->checkAlipayData();
            echo 'success';
        }

    }


    //检查参数
    function checkAlipayData(){
        //操作数据库拿到该订单ID的部分信息;
        $order_model=D('Order_info');
        $order_result=$order_model->getOrderInfo($GLOBALS['alipay_notify_data']['out_trade_no']);
        $error_code=0;
        if(!$this->tradeQuery($GLOBALS['alipay_notify_data']['out_trade_no'],$GLOBALS['alipay_notify_data']['trade_no'])){
            $error_code=1005;
            $error_msg='支付宝携带的订单号在该查询接口中没有找到';

        }elseif(empty($order_result)){
            $error_code=1006;
            $error_msg='支付宝携带的商户订单号在系统中不存在';

        }elseif($GLOBALS['alipay_notify_data']['total_amount']!=$order_result['pay_fee']){
            $error_code=1007;
            $error_msg='微信携带的订单金额和系统中的不一致';
        }
        if($error_code){
            $data['status']=false;
            $data['error_code']=$error_code;
            $data['error_msg']=$error_msg;
        }else{
            $data['status']=true;
        }
        return $data;
    }


    function tradeQuery(){
        $alipay=new \Alipay();
        $data=array('out_trade_no'=>'201503200101010012086','trade_no'=>'2017051321001004670200142912');
        $result=$alipay->tradeQuery($data);
        return $result;
    }

    /*
     * 支付宝同步跳转地址
     */
    function returnAlipay(){
        echo "<pre>";
        print_r($_POST);
        print_r($_GET);die;
        //调用一下异步
        $this->notifyAlipay();
        //根据状态码跳转至不同的页面

    }




    /**************************************微信*****************************************/
    /*
     * 生成二维码
     */
    public function wxQRCode(){

        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId,$role);//判断角色
        if ( $userId == -1 && $role == -1 ) {//是否登陆授权
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        layoutHtml($role,'2');//加载html头部文件

        $this->assign('module', '教学+');
        $this->assign('nav', '京版数字资源');
        $this->assign('subnav', '资源列表');
        $this->assign('navicon', 'jingbanziyuan');

        $order_data["desc"]='华为 HUAWEI P10 全网通 4GB+64GB 曜石黑 移动联通电信4G手机 双卡双待';
        $order_data['attach']='麒麟960芯片！wifi双天线设计！徕卡人像摄影！';
        $order_data['order_sn']='190000985120170511192148';
        $order_data['pay_fee']='1';
        $order_data['goods_id']='4483094';

        $object=new \Weixin();
        $url=$object->createQRCode($order_data);
        $this->assign('qrcode_rul',$url);
        $this->display('payment');
    }



    /*
     * jssdk
     */
    public function jsSdkCode() {
        $order_data["desc"]='macbookpro';
        $order_data['attach']='128G SSD';
        $order_data['order_sn']='190000985120170511192148'.rand(100,999999);
        $order_data['pay_fee']='3';
        $order_data['goods_id']='4483094';

        $object=new \Weixin();
        $data=$object->createjsSdkCode($order_data);

        $this->assign('jsApiParameters',$data['jsApiParameters']);
        $this->assign('editAddress',$data['editAddress']);
        $this->display('jssdk');
    }


    /*
     * 异步地址
     */
    public function wxNotify(){

        $pay_log=M('pay_log');
        $object=new \Weixin();
        $result=$object->notifyHandle(false);

        $resonp_order = explode('_',$GLOBALS['notify_data']['out_trade_no']);
        $GLOBALS['notify_data']['out_trade_no'] = $resonp_order[0];

        //下单日志
        $addwxmap['code'] = '下单成功';
        $addwxmap['msg'] = json_encode($GLOBALS['notify_data']);
        $addwxmap['order_sn'] = $GLOBALS['notify_data']['out_trade_no'];
        $pay_log->add($addwxmap);

        if($result==true){

            $wxData=$this->checkWxData();

            if ($wxData['status'] === true) { //改变订单状态和时间
                self::$orderModel->editOrderStatusSuccess($GLOBALS['notify_data']['out_trade_no']);
                echo "SUCCESS";
                //消息推送

                $orderinfo = self::$orderModel->getOrderInfo( $GLOBALS['notify_data']['out_trade_no'] );
                if ($orderinfo['order_type']==0) {
                    $orderdata = self::$orderModel->orderIdGetOrderInfo( $orderinfo['id'],$orderinfo['user_id'],$orderinfo['user_role'] );
                } else {
                    $orderdata = self::$orderModel->DirectIdGetOrderInfo($orderinfo['id'], $orderinfo['user_id'], $orderinfo['user_role']);
                }
                
                $parameters = array(
                    'msg' => array(
                        $orderdata['pay_fee'],
                        $orderdata['name'],
                        $GLOBALS['notify_data']['out_trade_no'],
                    ),
                    'url' => array( 'type' => 0)
                );


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
                $is_send_map['order_sn'] = $resonp_order[0];
                $is_send = $pay_log->where( $is_send_map )->find();

                if ( !empty($iphone) && $is_send['is_send']==1 ) {

                    $sendOrder['is_send'] = 2;
                    $pay_log->where( $is_send_map )->save($sendOrder);

                    A('Home/Message')->addPushUserMessage('ORDER_SUCCESS',$orderinfo['user_role'],$orderinfo['user_id'],$parameters);

                    $sendexplode=[];
                    $sendexplode[]=$orderdata['pay_fee'].'元';
                    $sendexplode[]=$orderdata['name'];
                    $sendexplode[]=$GLOBALS['notify_data']['out_trade_no'];

                    $sendmessage['iphone'] = $iphone;
                    $sendmessage['msg'] = implode(',',$sendexplode);

                    $iphonesend = $this->sendIphone( $sendmessage );
                    file_put_contents('./Public/sendinfo.json',json_encode($iphonesend).PHP_EOL,FILE_APPEND);
                }

                return "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";

            } else {

                $data['code']=$wxData['error_code'];
                $data['msg']= $wxData['error_msg'];
                $pay_log->add($data);
            }

        }else{
            $data['code']=1001;
            $data['msg']= '返回错误';
            $pay_log->add($data);
        }
    }


    public function DirectwxNotify()
    {
        file_put_contents('./Public/order.json',json_encode($GLOBALS['notify_data']));
        $pay_log = M('pay_log');
        $object = new \Weixin();
        $result = $object->notifyHandle(false);

        $resonp_order = explode('_', $GLOBALS['notify_data']['out_trade_no']);
        $GLOBALS['notify_data']['out_trade_no'] = $resonp_order[0];

        //下单日志
        $addwxmap['code'] = '下单成功';
        $addwxmap['msg'] = json_encode($GLOBALS['notify_data']);
        $addwxmap['order_sn'] = $GLOBALS['notify_data']['out_trade_no'];
        $pay_log->add($addwxmap);

        if ($result == true) {

            $wxData = $this->checkWxData();

            if ($wxData['status'] === true) { //改变订单状态和时间
                self::$orderModel->editOrderStatusSuccess($GLOBALS['notify_data']['out_trade_no']);
                echo "SUCCESS";
                //消息推送

                $orderinfo = self::$orderModel->getOrderInfo($GLOBALS['notify_data']['out_trade_no']);

                $orderdata = self::$orderModel->DirectIdGetOrderInfo($orderinfo['id'], $orderinfo['user_id'], $orderinfo['user_role']);

                $parameters = [];
                $parameters = array(
                    'msg' => array(
                        $orderdata['pay_fee'],
                        $orderdata['special_column_question_title'],
                        $GLOBALS['notify_data']['out_trade_no'],
                    ),
                    'url' => array('type' => 0)
                );


                if ($orderinfo['user_role'] == 2) { //教师

                    $userinfo = D('Auth_teacher')->getTeachInfo($orderinfo['user_id']);
                    $iphone = $userinfo['telephone'];
                }

                if ($orderinfo['user_role'] == 3) {//学生
                    $userinfo = D('Auth_student')->getStudentInfo($orderinfo['user_id']);
                    $iphone = $userinfo['parent_tel'];
                }

                if ($orderinfo['user_role'] == 4) {//家长
                    $userinfo = D('Auth_parent')->getParentInfo($orderinfo['user_id']);
                    $iphone = $userinfo['telephone'];
                }
                $is_send_map['order_sn'] = $resonp_order[0];
                $is_send = $pay_log->where($is_send_map)->find();

                if (!empty($iphone) && $is_send['is_send'] == 1) {

                    $sendOrder['is_send'] = 2;
                    $pay_log->where($is_send_map)->save($sendOrder);

                    A('Home/Message')->addPushUserMessage('ORDER_SUCCESS', $orderinfo['user_role'], $orderinfo['user_id'], $parameters);

                    $sendexplode = [];
                    $sendexplode[] = $orderdata['pay_fee'] . '元';
                    $sendexplode[] = $orderdata['special_column_question_title'];
                    $sendexplode[] = $GLOBALS['notify_data']['out_trade_no'];

                    $sendmessage['iphone'] = $iphone;
                    $sendmessage['msg'] = implode(',', $sendexplode);

                    $iphonesend = $this->sendIphone($sendmessage);
                    file_put_contents('./Public/sendinfo.json', json_encode($iphonesend) . PHP_EOL, FILE_APPEND);
                }

                return "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";

            } else {

                $data['code'] = $wxData['error_code'];
                $data['msg'] = $wxData['error_msg'];
                $pay_log->add($data);
            }

        } else {
            $data['code'] = 1001;
            $data['msg'] = '返回错误';
            $pay_log->add($data);
        }
    }

    public function wxAppNotify()
    {
        $pay_log=M('pay_log');
        $postStr = $this->postdata();//接收post数据

        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $order_data = $this->object2array($postObj);//对象转成数组

        $sign = $this->getSign($order_data);

        //下单日志
        $addwxmap['code'] = '下单成功';
        $addwxmap['msg'] = json_encode($order_data);
        $addwxmap['order_sn'] = $order_data['out_trade_no'];
        $pay_log->add($addwxmap);

        if( $sign == $order_data['sign'] ){//验证成功

            $GLOBALS['notify_data'] = $order_data;

            $wxData=$this->checkAppWxData();

            if ($wxData['status'] === true) { //改变订单状态和时间
                self::$orderModel->editOrderStatusSuccess($GLOBALS['notify_data']['out_trade_no']);
                echo "SUCCESS";

                //消息推送
                $orderinfo = self::$orderModel->getOrderInfo( $GLOBALS['notify_data']['out_trade_no'] );
                if ($orderinfo['order_type']==0) {
                    $orderdata = self::$orderModel->orderIdGetOrderInfo( $orderinfo['id'],$orderinfo['user_id'],$orderinfo['user_role'] );

                    $parameters = array(
                        'msg' => array(
                            $orderdata['pay_fee'],
                            $orderdata['name'],
                            $GLOBALS['notify_data']['out_trade_no'],
                        ),
                        'url' => array( 'type' => 0)
                    );

                    file_put_contents('./Public/parameters.json',json_encode($parameters).PHP_EOL,FILE_APPEND);

                } else {
                    $orderdata = self::$orderModel->DirectIdGetOrderInfo($orderinfo['id'], $orderinfo['user_id'], $orderinfo['user_role']);

                    $parameters = array(
                        'msg' => array(
                            $orderdata['pay_fee'],
                            $orderdata['special_column_question_title'],
                            $GLOBALS['notify_data']['out_trade_no'],
                        ),
                        'url' => array( 'type' => 0)
                    );

                    file_put_contents('./Public/parameters.json',json_encode($parameters).PHP_EOL,FILE_APPEND);

                }




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
                $is_send_map['order_sn'] = $GLOBALS['notify_data']['out_trade_no'];
                $is_send = $pay_log->where( $is_send_map )->find();
                if ( !empty($iphone) && $is_send['is_send']==1 ) {

                    $sendOrder['is_send'] = 2;
                    $pay_log->where( $is_send_map )->save($sendOrder);

                    A('Home/Message')->addPushUserMessage('ORDER_SUCCESS',$orderinfo['user_role'],$orderinfo['user_id'],$parameters);

                    $sendexplode=[];
                    $sendexplode[]=$orderdata['pay_fee'].'元';
                    $sendexplode[]=$orderdata['name'];
                    $sendexplode[]=$GLOBALS['notify_data']['out_trade_no'];

                    $sendmessage['iphone'] = $iphone;
                    $sendmessage['msg'] = implode(',',$sendexplode);

                    $iphonesend = $this->sendIphone( $sendmessage );

                    file_put_contents('./Public/sendinfo.json',json_encode($iphonesend).PHP_EOL,FILE_APPEND);
                }

                return "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
            } else {
                $data['code']=$wxData['error_code'];
                $data['msg']= $wxData['error_msg'];
                $pay_log->add($data);
            }
        } else {

            $data['code']= 1005;
            $data['msg']= json_encode($order_data);
            $pay_log->add($data);
        }


    }

    //app统一下单接口
    public function payAppOrder() {

        $token = getParameter('token','str',false);
        $name = getParameter('name','str',false);
        if (!empty($name)){
            $name = mb_substr($name, 0, 10, 'utf-8');
        }

        $rsa_data = rsaDecrypted($token);
        if ( empty($rsa_data) ) {//校验
            $this->ajaxReturn(array('status' => 400, 'message' => 'rsa解密失败'));
        }

        $rsa_data = getParseStr($rsa_data);

        $appid = 'wx2edc0faaffb8e48e';
        $mch_id = '1481132662';
        $notify_url = $_SERVER['HTTP_HOST'].'/index.php/Home/OrderManage/wxAppNotify';
        $key = 'JBYjdu8432Jby182jgux9wzznuddjd81';
        $wechatAppPay = new WAP($appid, $mch_id, $notify_url, $key);

        $pay_fee=$rsa_data['pay_fee'];
        $order_sn=$rsa_data['order_sn'];
        $body=$name;

        $orderinfo = self::$orderModel->getOrderInfo( $order_sn );

        if ($orderinfo['order_status'] != 1) {
            $this->ajaxReturn(array('status' => 400, 'message' => '订单异常,请重新查看该订单'));
        }

        if ( $rsa_data['sing'] != getSing() ) {
            $this->ajaxReturn(array('status' => 400, 'message' => 'sing参数错误'));
        }

        if (empty($pay_fee)) {
            $this->ajaxReturn(array('status' => 400, 'message' => '价格错误'));
        }

        if (empty($order_sn)) {
            $this->ajaxReturn(array('status' => 400, 'message' => '订单号错误'));
        }

        if (empty($body)) {
            $this->ajaxReturn(array('status' => 400, 'message' => '商品描述错误'));
        }

        $params['body'] = trimall($body);                       //商品描述
        $params['out_trade_no'] = $order_sn;    //自定义的订单号
        $params['total_fee'] = $pay_fee*100;                       //订单金额 只能为整数 单位为分
        $params['trade_type'] = 'APP';                      //交易类型 JSAPI | NATIVE | APP | WAP

        $result = $wechatAppPay->unifiedOrder( $params );

        $data = $wechatAppPay->getAppPayParams( $result['prepay_id'] );

        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            $this->ajaxReturn(array('status' => 200,'data'=>$data,'message'=>'' ));
        } else {
            if (!empty($result['return_msg'])) {
                $data['message'] = $result['return_msg'];
            }
            if (!empty($result['err_code_des'])) {
                $data['message'] = $result['err_code_des'];
            }
            $this->ajaxReturn(array('status' => 400,'message'=>$data['message'] ));
        }

    }

    public function getSign($array) {
        $pay_key = 'JBYjdu8432Jby182jgux9wzznuddjd81';
        unset($array['sign']);
        ksort($array);
        $stringA = urldecode(http_build_query($array));
        $stringSignTemp="$stringA&key=".$pay_key;
        return strtoupper(md5($stringSignTemp));
    }

    // 接收post数据
    /*
    *  微信是用$GLOBALS['HTTP_RAW_POST_DATA'];这个函数接收post数据的
    */
    public function postdata(){
        $receipt = $_REQUEST;
        if($receipt==null){
            $receipt = file_get_contents("php://input");
            if($receipt == null){
                $receipt = $GLOBALS['HTTP_RAW_POST_DATA'];
            }
        }
        return $receipt;
    }

    //把对象转成数组
    public function object2array($array) {
        if(is_object($array)) {
            $array = (array)$array;
        } if(is_array($array)) {
            foreach($array as $key=>$value) {
                $array[$key] = $this->object2array($value);
            }
        }
        return $array;
    }


    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams($arr)
    {
        $weipay_key = 'sdfasdfasdfasd';//微信的key,这个是微信支付给你的key，不要瞎填。
        $buff = "";
        foreach ($arr as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff.'&key='.$weipay_key;
    }

    //根据微信的订单号获取实际的订单号
    public function getTradeNo( $trade_no ) {
        $trade_no =  explode('_',$trade_no);
        return $trade_no[0];
    }

    /*
     * 判断微信携带过来的参数是否正确
     */
    public function checkWxData(){
        //操作数据库拿到该订单ID的部分信息;
        $order_result=self::$orderModel->getOrderInfo( $GLOBALS['notify_data']['out_trade_no'] );
        $order_result['pay_fee'] = $order_result['pay_fee']*100;//换算成分
        $error_code=0;
        if($GLOBALS['notify_data']['return_code']!=SUCCESS_FLAG){
            $error_code=1002;
            $error_msg='return_code 微信通信失败';

        }elseif(isset($GLOBALS['notify_data']['return_msg']) ){
            if($GLOBALS['notify_data']['return_msg']!=''){
                $error_code=1003;
                $error_msg='return_msg 微信签名失败';
            }
        }elseif($GLOBALS['notify_data']['result_code']!=SUCCESS_FLAG){
            $error_code=1004;
            $error_msg='result_code 微信业务结果处理失败 '.isset($GLOBALS['notify_data']['err_code'])?$GLOBALS['notify_data']['err_code']:''.' '.isset($GLOBALS['notify_data']['err_code_des'])?$GLOBALS['notify_data']['err_code_des']:'';

        }elseif(empty($this->orderQuery($GLOBALS['notify_data']['transaction_id']))){
            $error_code=1005;
            $error_msg='微信携带的订单号在该查询接口中没有找到';

        }elseif(empty($order_result)){
            $error_code=1006;
            $error_msg='微信携带的商户订单号在系统中不存在';

        }elseif( $GLOBALS['notify_data']['total_fee']!=$order_result['pay_fee'] ){//这里是实付款
            $error_code=1007;
            $error_msg='微信携带的订单金额和系统中的不一致';
        }

        if($error_code){
            $data['status']=false;
            $data['error_code']=$error_code;
            $data['error_msg']=$error_msg;
        }else{
            $data['status']=true;
        }
        return $data;
    }

    /*
     * 判断微信携带过来的参数是否正确
     */
    public function checkAppWxData(){
        //操作数据库拿到该订单ID的部分信息;
        $order_result=self::$orderModel->getOrderInfo( $GLOBALS['notify_data']['out_trade_no'] );
        $order_result['pay_fee'] = $order_result['pay_fee']*100;//换算成分
        $error_code=0;
        if($GLOBALS['notify_data']['return_code']!=SUCCESS_FLAG){
            $error_code=1002;
            $error_msg='return_code 微信通信失败';

        }elseif(isset($GLOBALS['notify_data']['return_msg']) ){
            if($GLOBALS['notify_data']['return_msg']!=''){
                $error_code=1003;
                $error_msg='return_msg 微信签名失败';
            }
        }elseif($GLOBALS['notify_data']['result_code']!=SUCCESS_FLAG){
            $error_code=1004;
            $error_msg='result_code 微信业务结果处理失败 '.isset($GLOBALS['notify_data']['err_code'])?$GLOBALS['notify_data']['err_code']:''.' '.isset($GLOBALS['notify_data']['err_code_des'])?$GLOBALS['notify_data']['err_code_des']:'';

        }elseif(empty($order_result)){
            $error_code=1006;
            $error_msg='微信携带的商户订单号在系统中不存在';

        }elseif( $GLOBALS['notify_data']['total_fee']!=$order_result['pay_fee'] ){//这里是实付款
            $error_code=1007;
            $error_msg='微信携带的订单金额和系统中的不一致';
        }

        if($error_code){
            $data['status']=false;
            $data['error_code']=$error_code;
            $data['error_msg']=$error_msg;
        }else{
            $data['status']=true;
        }
        return $data;
    }


    /*
     * 查询订单号
     */
    public function orderQuery($transaction_id){
        //$transaction_id='4001212001201705110393819727';
        $object=new \Weixin();
        $result=$object->orderQuery($transaction_id);
        return $result;
    }

    //pc端点击立即支付
    public function BuyNowResources() {
        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId,$role);//判断角色
        if ( $userId == -1 && $role == -1 ) {//是否登陆授权
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $filter['id']=getParameter('id','str',false);
        if (empty($filter['id'])){ //判断订单号
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $res_info = D('Knowledge_resource')->getResourceOne( $filter['id'] );

        if (!empty($res_info)) {
            /*$order_data=[];
            $order_data['order_sn'] = StrOrderOne();
            $order_data['user_role'] = $role;
            $order_data['resources_id'] = $res_info['id'];
            $order_data['user_id'] = $userId;
            $order_data['pay_fee'] = $res_info['real_price'];
            $order_data['pay_source'] = 1;
            $order_data['create_at'] = time();
            $id = self::$orderModel->addOrder( $order_data );//入库订单
            $order_info = self::$orderModel->orderIdGetOrderInfo( $id,$userId,$role );//获取订单信息*/
            $this->redirect('confirmOrder',array('id'=>$res_info['id']));
        } else {
            header("HTTP/1.1 404 Not Found");
            exit();
        }

    }


    //确认下单
    public function confirmOrder(){

        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId,$role);//判断角色
        if ( $userId == -1 && $role == -1 ) {//是否登陆授权
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        layoutHtml($role,'2');//加载html头部文件

        $filter['id']=getParameter('id','str',false);
        if (empty($filter['id'])){ //判断订单号
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        $userOrder=D('Knowledge_resource')->getResourceDetailInfo($filter['id'],$role,$userId);

        //$userOrder = self::$orderModel->orderSnGetOrderInfo( $filter['id'],$userId,$role );//根据角色获取对应的订单
        if ($userOrder === false) {
            die("非法用户查询");
        }

        $this->assign('order', $userOrder);

        $this->assign('module', '教学+');
        $this->assign('nav', '京版数字资源');
        $this->assign('subnav', '资源列表');
        $this->assign('navicon', 'jingbanziyuan');
        $this->display();
    }
    public function successFulpayment(){

        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId,$role);//判断角色
        if ( $userId == -1 && $role == -1 ) {//是否登陆授权
            header("HTTP/1.1 404 Not Found");
            exit();
        }

        layoutHtml($role,'2');//加载html头部文件

        //$filter['order_sn']=getParameter('order_sn','str',false);
        $filter['resources_id']=getParameter('resources_id','str',false);

        $this->assign('id', $filter['resources_id']);

        $this->assign('module', '教学+');
        $this->assign('nav', '京版数字资源');
        $this->assign('subnav', '资源列表');
        $this->assign('navicon', 'jingbanziyuan');
        $this->display();
    }

    //跳转到支付页面
    public function payment() {

        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId,$role);//判断角色
        if ( $userId == -1 && $role == -1 ) {//是否登陆授权
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        layoutHtml($role,'2');//加载html头部文件

        $filter['id']=getParameter('id','str',false);
        if (empty($filter['id'])){ //判断订单号
            header("HTTP/1.1 404 Not Found");
            exit();
        }

        $res_info = D('Knowledge_resource')->getResourceDetailInfo($filter['id'],$role,$userId);
        $userOrder = self::$orderModel->getResourceByOrder( $filter['id'],$userId,$role );//根据角色获取对应的订单

        if (!empty($res_info) ) {

            if( empty($userOrder) ) { //直接跳转到成功的页面
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
            } else {//已经下单

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
                } else {
                    $order_info = $userOrder;
                }

            }

            $this->redirect('rightPayment',array('order_sn'=>$order_info['order_sn']));
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
        if ( $userId == -1 && $role == -1 ) {//是否登陆授权
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        layoutHtml($role,'2');//加载html头部文件

        $filter['order_sn']=getParameter('order_sn','str',false);
        if (empty($filter['order_sn'])){ //判断订单号
            header("HTTP/1.1 404 Not Found");
            exit();
        }


        $userOrder = self::$orderModel->orderSnGetOrderInfo( $filter['order_sn'],$userId,$role );//根据角色获取对应的订单
        if($userOrder['order_status'] ==2 ){ //直接跳转到成功的页面
            $this->redirect('successFulpayment',array('order_sn'=>$userOrder['order_sn'],'resources_id'=>$userOrder['resources_id']));
        }
        if (!empty($userOrder) && $userOrder['order_status']==1 ) {
            if (!empty($userOrder['name'])){
                $userOrder['name'] = mb_substr($userOrder['name'], 0, 10, 'utf-8');
            }
            $order_data["desc"]=trimall($userOrder['name']);
            //$order_data['attach']=$userOrder['name'];
            $order_data['order_sn']=$userOrder['order_sn'].'_'.date("YmdHis");
            $order_data['pay_fee']=$userOrder['pay_fee']*100;
            //$order_data['goods_id']='4483094';
            $object=new \Weixin();
            $url=$object->createQRCode($order_data);
            $this->assign('qrcode_rul',$url);

            $this->assign('order', $userOrder);
            $this->assign('module', '教学+');
            $this->assign('nav', '京版数字资源');
            $this->assign('subnav', '资源列表');
            $this->assign('navicon', 'jingbanziyuan');

            $this->display('payment');
        } else {
            die('非法用户');
        }
    }

    //微信成功跳转
    public function getOrderStatsu() {

        $userId = -1;
        $role = -1;
        A('Home/Common')->getUserIdRole($userId,$role);//判断角色
        if ( $userId == -1 && $role == -1 ) {//是否登陆授权
            header("HTTP/1.1 404 Not Found");
            exit();
        }
        layoutHtml($role,'2');//加载html头部文件

        $filter['order_sn']=getParameter('order_sn','str',false);
        if (empty($filter['order_sn'])){ //判断订单号
            header("HTTP/1.1 404 Not Found");
            exit();
        }

        $userOrder = self::$orderModel->orderSnGetOrderInfo( $filter['order_sn'],$userId,$role );//根据角色获取对应的订单
        if ($userOrder['order_status'] == 2) {
            $this->ajaxReturn('jump');
        } else {
            $this->ajaxReturn('error');
        }
    }
}
