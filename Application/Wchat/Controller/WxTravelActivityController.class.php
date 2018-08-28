<?php
/**
 * Created by PhpStorm.
 * User: GM
 * Date: 2017/12/28
 * Time: 9:56
 */
namespace Wchat\Controller;
use Think\Controller;
use Common\Common\CSV;
use Common\Common\REDIS;
use Common\Common\JingCaiYouSMS;
use Common\Common\JSSDK;
define('SUCCESS_FLAG','SUCCESS');
define("APPID", "wxa6d2714aa7728aef");//你微信定义的appid
define("APPSECRET","4b62d67992416eac3e58f3ebd4ae7993");//你微信公众号的appsecret
import('Vendor.Alipay.Alipay');
import('Vendor.WeixinPay.Weixin');
define('ACTIVIY_NOTIFY_URL',$_SERVER['HTTP_HOST'].'/index.php/Wchat/WxTravelActivity/asyncNotify');

define('UNPAY',1);
define('PAYED',2);

define('ONE_PRICE',98.0);
define('TWO_PRICE',600.0);
define('THREE_PRICE',700.0);
define('DISCOUNT',0.9);
class WxTravelActivityController extends Controller
{

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = D('Wx_travel_activity');
        $this->assign('oss_path', C('oss_path'));
    }

    /**
     *描述：报名页
     */
    public function apply()
    {
        $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
        $activityVersion = getParameter('version','int',false);
        if(0)
        {
            echo '<html><body><div style="position:relative;height: 100%;"><div style="position:absolute;left:0px;right:0px;top:0px;bottom:0px;text-align:center;font-size:46px;margin:auto;height:100px">该活动已结束</div></div></div></body></html>';
            exit;
        }
        else {
                $url = "http://" . WEB_URL . "/Public/html/youxueHomePage.html";
                $signPackage = $jssdk->GetSignPackage($url);
                $this->assign('signPackage', $signPackage);
                $this->assign('url', $url);
                $this->assign('price1', ONE_PRICE);
                $this->assign('price2', TWO_PRICE);
                $this->assign('price3', THREE_PRICE);
                $this->assign('discount', DISCOUNT);
                $this->display_nocache();
        }
    }

    /**
     *描述：ajax接受数据并入库
     */
    public function ajaxAdd()
    {
        $applyList = I('applyList');
        $openId = getParameter('openid', 'str');
        //price check
        $priceArray = array(ONE_PRICE,TWO_PRICE,THREE_PRICE);
        $priceSum = 0.0;
        $payFee = getParameter('pay_fee', 'str');

        foreach ($applyList as $value) {
          $priceSum += $priceArray[$value['type']-1];
        }
        if(sizeof($applyList) > 2)
            $priceSum *= DISCOUNT;
        if(round($priceSum,2) != floatval($payFee))
        {
            $this->showMessage('404', '创建订单失败，请联系客服人员');
        }

        //create order
        $orderData = $this->createWxOrder($payFee,$openId);
        if(empty($orderData['apiParameter']))
        {
            $this->showMessage('404', '创建订单失败，请联系客服人员');
        }
        $orderSn = $orderData['order_sn'];
        foreach ($applyList as $value) {
            $condetion['name'] = $value['name'];
            $condetion['phone'] = $value['phone'];
            $condetion['age'] = $value['age'];
            $condetion['activity_type'] = $value['type'];//报名活动类型
            $condetion['order_sn'] = $orderSn;//订单号
            $condetion['activity_time'] = $value['activity_time'];//活动开始时间
            $condetion['create_time'] = date('Y-m-d H:i:s');//创建时间
            $this->model->startTrans();
            //用户表入库操作
            if ($this->model->add($condetion) === false) {
                $this->model->rollback();
                $this->showMessage('404', '操作失败，请联系客服人员');
            }
            $this->model->commit();
        }
        $condetionOrder['order_sn'] = $orderSn;//订单号
        $condetionOrder['pay_fee'] = $payFee;//实付金额
        //$condetionOrder['trade_no'] = getParameter('trade_no','str');//第三方支付订单号
        $condetionOrder['create_time'] = date('Y-m-d H:i:s');//创建时间
        $condetionOrder['openid'] = $openId;//微信用户openid
        //订单表入库操作
        if ($this->model->orderAdd($condetionOrder) === false) {
            $this->model->rollback();
            $this->showMessage('404', '操作失败，请联系客服人员');
        }
        $this->showMessage('200','success',array('wxOrderInfo'=>$orderData['apiParameter']));
    }

    public function enrolmentAgreement()
    {
       $this->success();
    }
    /**
     *描述：微信回调页面
     */
    public function success()
    {
        $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
        $url = "http://" . WEB_URL . "/Public/html/youxueHomePage.html";
        $signPackage = $jssdk->GetSignPackage($url);
        $this->assign('signPackage', $signPackage);
        $this->assign('url',$url);
        $this->display();
    }

    /**
     *描述：导出数据生成报表
     */
    public function export()
    {
        //查询导出项数据
        $where = PAYED;
        $result = $this->model->getAll($where);
        $str = "报名人姓名,报名联系方式,报名人年龄,订单号,报名类型,订单金额,订单创建时间,总金额,总人数\n";
        $str = iconv('utf-8', 'gb2312', $str);
        for ($k = 0; $k < count($result); $k++) {
            $name = iconv('utf-8', 'gbk', $result[$k]['name']);
            $telephone = iconv('utf-8', 'gb2312', $result[$k]['phone']);
            $age = iconv('utf-8', 'gb2312', $result[$k]['age']);

            if ($result[$k + 1]['order_sn'] == $result[$k]['order_sn']) {
                $create_time = iconv('utf-8', 'gb2312', " ");
                $order_sn = iconv('utf-8', 'gb2312', " ");
                $pay_fee = iconv('utf-8', 'gb2312', " ");
            } else {
                $create_time = iconv('utf-8', 'gb2312', $result[$k]['create_time']);
                $order_sn = iconv('utf-8', 'gb2312', $result[$k]['order_sn']);
                $pay_fee = iconv('utf-8', 'gb2312', $result[$k]['pay_fee']);
            }

            if ($result[$k]['activity_type'] == 1) {
                $type = iconv('utf-8', 'gb2312', "儿童自己");
            } elseif ($result[$k]['activity_type'] == 2) {
                $type = iconv('utf-8', 'gb2312', "一大人一儿童");
            } else {
                $type = iconv('utf-8', 'gb2312', "两大人一儿童");
            }

            if ($k == 0) {
                $totle_pay = iconv('utf-8', 'gb2312', $result[$k]['total_pay']);
            } else {
                $totle_pay = iconv('utf-8', 'gb2312', " ");
            }

            if ($k == 0) {
                $totle_children = iconv('utf-8', 'gb2312', count($result));
            } else {
                $totle_children = iconv('utf-8', 'gb2312', " ");
            }


            $line = $name . ',' . $telephone . ',' . $age . ',' . $order_sn . ',' . $type . ',' . $pay_fee . ',' . $create_time . ',' . $totle_pay . ',' . $totle_children . ',' . "\n";

            $str .= $line;

        }
        $filename = date('Ymd') . rand(0, 1000) . 'activityRegister' . '.csv';
        $csv = new CSV();
        $csv->downloadFileCsv($filename, $str);
        die;
    }

    /**
     *描述：根据订单号修改订单状态
     */
    public function saveOrderStatus($order_sn,$status){
        $data['order_status'] = $status;
        $where['order_sn'] = $order_sn;
        $this->model->saveOrderStatus($data,$where);
    }

    /**
     *描述：根据订单号发送短信
     */
    public function sendSMS($order_sn){
        //根据订单查询该订单下的所有用户
        $where['order_sn'] = $order_sn;
        $result = $this->model->selectByWhere($where);
        //遍历数组时发送短信
        $smsapi = new JingCaiYouSMS();
        $sendSmsFunction='templateSMS';
        for($i = 0;$i< count($result); $i++){
            $str = '';
            for($v = 0;$v< count($result); $v++){
                if($result[$v]['activity_type'] == 1){
                    $type = "";
                }elseif ($result[$v]['activity_type'] == 2){
                    $type = "";
                }else{
                    $type = "";
                }
                $str .=   "活动开始时间：".$result[$v]['activity_time'].';';
            }
            //发送短信
            $param = $result[$i]['name'].","."京彩游".",".$result[$i]['order_sn'].",".$str;
            $ret = $smsapi->$sendSmsFunction($result[$i]['phone'],$param,'json');
            if ($ret['status'] == false || $ret < 0) {
                //$this->showjson(-2, '验证码发送失败');
                \Think\Log::write('短信发送错误,错误信息:'.json_encode($ret),'ERR/SMS.ERR');
                return;
            }
        }
    }

    private function createWxOrder($price,$openId)
    {
        $localOrderId = StrOrderOne();
        $order_data["desc"] = '京彩游';
        $order_data['order_sn'] = $localOrderId . '_' . date("YmdHis");
        $order_data['pay_fee'] = round(floatval($price) * 100);
        $object = new \Weixin();
        $data = $object->createjsSdkCode($order_data, $openId,ACTIVIY_NOTIFY_URL);
        return array('order_sn'=>$localOrderId,'apiParameter'=>$data['jsApiParameters']);
        //$this->showMessage(200, $localOrderId, $data['jsApiParameters']);
    }

    public function authorize()
    {
        if (isset($_GET['code']) && empty($_SESSION['openid'])) {
            $code = $_GET['code'];
            $data = $this->getUserInfoBycode($code);
            $data = stripslashes(trim($data, '"'));
            $openid = json_decode($data, true);
            $_SESSION['openid'] = $openid['openid'];
            $this->showMessage(200, 'success', $data);
        }
        $this->showMessage(200, 'success', json_encode(array('openid' => $_SESSION['openid'])));

    }

    public function getUserInfoBycode($code)
    {
        $res = $this->http('https://api.weixin.qq.com/sns/oauth2/access_token', array('appid' => APPID, 'secret' => APPSECRET, 'code' => $code, 'grant_type' => 'authorization_code'), '', 'GET');
        if ($res) {
            return $res;
        }
        return false;
    }

    private function http($url, $param, $data = '', $method = 'GET')
    {
        $opts = array(
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        );
        /* 根据请求类型设置特定参数 */
        $opts[CURLOPT_URL] = $url . '?' . http_build_query($param);
        if (strtoupper($method) == 'POST') {
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $data;
            if (is_string($data)) { //发送JSON数据
                $opts[CURLOPT_HTTPHEADER] = array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($data),
                );
            }
        }
        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        //发生错误，抛出异常
        if ($error)
            throw new \Exception('请求发生错误：' . $error);
        return json_encode($data);
    }

    public function asyncNotify()
    {
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
                //save status
                $orderId = $GLOBALS['notify_data']['out_trade_no'];
                $this->saveOrderStatus($orderId,PAYED);
                $this->sendSMS($orderId);
                echo "SUCCESS";
                return "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
            }
            else
            {
                $data['code']=$wxData['error_code'];
                $data['msg']= $wxData['error_msg'];
                $pay_log->add($data);
            }
        }
        else
        {
            $data['code']=1001;
            $data['msg']= '返回错误';
            $pay_log->add($data);
        }
    }

    /*
     * 判断微信携带过来的参数是否正确
     */
    public function checkWxData(){
        //操作数据库拿到该订单ID的部分信息;
        $orderPrice=D('Wx_travel_activity')->getOrderPrice( $GLOBALS['notify_data']['out_trade_no'] );
        $orderPrice = $orderPrice*100;//换算成分
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

        }elseif(empty($orderPrice)){
            $error_code=1006;
            $error_msg='微信携带的商户订单号在系统中不存在';

        }elseif( $GLOBALS['notify_data']['total_fee']!=$orderPrice){//这里是实付款
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

    public function orderQuery($transaction_id){
        //$transaction_id='4001212001201705110393819727';
        $object=new \Weixin();
        $result=$object->orderQuery($transaction_id);
        return $result;
    }
}
