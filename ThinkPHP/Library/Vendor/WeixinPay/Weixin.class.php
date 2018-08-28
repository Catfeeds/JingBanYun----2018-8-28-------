<?php
//namespace Weixin;

$dir=getcwd();
$weixin_dir=$dir.DIRECTORY_SEPARATOR.'ThinkPHP'.DIRECTORY_SEPARATOR.'Library'.DIRECTORY_SEPARATOR.'Vendor'.DIRECTORY_SEPARATOR.'WeixinPay';
$GLOBALS['weixin_dir']=$weixin_dir; 
$GLOBALS['notify_data']=array();

ini_set('date.timezone','Asia/Shanghai');
require_once "lib/WxPay.Api.php";
require_once "example/WxPay.NativePay.php"; 
require_once "example/notify.php";
require_once 'example/log.php';
require_once 'example/WxPay.JsApiPay.php';

define('WX_PAY_NOTIFY_URL',$_SERVER['HTTP_HOST'].'/index.php/Home/OrderManage/wxNotify');

class Weixin{
      
    private $notify_url=WX_PAY_NOTIFY_URL;
    private $trade_type='NATIVE';

    /*
     * 统一下单生成二维码
     * 
     */
    public function createQRCode($data){             
        $notify = new NativePay(); 
        
        $input = new WxPayUnifiedOrder();           
        $input->SetBody($data['desc']);                                        //简要描述
        $input->SetAttach($data['attach']);                                      //附加数据,自定义数据
        $input->SetOut_trade_no($data['order_sn']);                         //商户订单号,32个字符串内
        $input->SetTotal_fee($data['pay_fee']);                                      //订单总金额,单位为分
        $input->SetTime_start(date("YmdHis"));                          //交易起始时间
        $input->SetTime_expire(date("YmdHis", time() + 600));           //交易结束时间                                  
           
        $input->SetNotify_url($this->notify_url);   
        $input->SetTrade_type($this->trade_type);                                //JSAPI--公众号支付、NATIVE--原生扫码支付、APP--app支付
        $input->SetProduct_id($data['goods_id']);                             //商户端设置的商品ID
        $result = $notify->GetPayUrl($input);     
        $url = $result["code_url"];   
        return $url;
    }
    
    /*
     * 回调地址处理
     */
    public function notifyHandle($needSign = true){ 
        $notify = new PayNotifyCallBack();  
        $result=$notify->Handle($needSign);  
        return $result;
    }
    
    
    /*
     * 订单查询
     */
    public function orderQuery($transaction_id){ 
        $notify = new PayNotifyCallBack(); 
        $result=$notify->Queryorder($transaction_id); 
        return $result;
    }

    //微信jssdk支付
    public function createjsSdkCode($data,$openId='',$notifyUrl='') {
        $tools = new JsApiPay();
        if(empty($openId))
        $openId = $tools->GetOpenid();
        $input = new WxPayUnifiedOrder();
        $input->SetBody($data['desc']);
        $input->SetAttach($data['attach']);
        $input->SetOut_trade_no($data['order_sn']);
        $input->SetTotal_fee($data['pay_fee']);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        //$input->SetGoods_tag("test");
        if($notifyUrl!='')
            $this->notify_url = $notifyUrl;
        $input->SetNotify_url($this->notify_url);
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        $editAddress = $tools->GetEditAddressParameters();
        $order_data['jsApiParameters'] = $jsApiParameters;
        $order_data['editAddress'] = $editAddress;
        return $order_data;
    }
    
}