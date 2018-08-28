<?php
require_once("AopSdk.php"); 

define('ALIPAY_NOTIFY_URL','http://'.$_SERVER['HTTP_HOST'].'/index.php/Home/OrderManage/notifyAlipay');
define('ALIPAY_RETURN_URL','http://'.$_SERVER['HTTP_HOST'].'/index.php/Home/OrderManage/returnAlipay');

class Alipay{
     
    private $appId='2016080700188178';
    //请填写商户私钥   请填写开发者私钥去头去尾去回车，一行字符串 
    private $rsaPrivateKey='MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCzUsKdpoDxqM3a34Ie2JcmI6zROkFwpbQTGyOCm10XmRg2sDKi0IaEK16y6SCq1FzB+dWZ72viDXcihmiFBEVW26YNOVVKWQNi4YOt3Ifhn1JQwu9OxzAS6qYksAvDgS+wfRH8yrehsNiGwFwLnCFrEDHu2BqrQt6tPv6v5pxB+0xJAKX7Pmu1wXGl7mACEo7YstfF23VnyGVLZHZxfgMJTqM+OAIshqgDsWfI9paM7c4J/+tR92SqFZTNrsqWXUayjGuxJd6ombL4foSe5qaSB6tKZ3H905rveTKXMGbTV35MVUGYZaQOuiMvMZxjF/nExEU7Ali+g2N/ck/G+JW5AgMBAAECggEABl6yOxdnDS6J4XR/Eslu1RP/V49SM8YvF16nbERIkkYF7itkIRR3Msq3mnNdjbPtd5aAV++BTY5c5QURQWhdbjBvPZkDxphS3nhUTSDAUutt6SCDj7DTrFhZfoQiZtd38jT/JpOs4jl994ttuZvaNAtnOqzChcLiVhUexvDSLCXJ2kwNMPI4OpY7L8hq0xIbHxEKfH+Eys9ZiTCeTZKtcreIbBJT7HUwXG+pLitEwXziSwp7QFL+olmf5KJChqar0+Ag9stysAihjUj29U1aRkQcVKTkcPHi5x9kYWI6TxrCAN7mfYDRqZA3t2Yk8lwrlPUW9dKBYwiVcnTbv+23gQKBgQDhKjIFJNivr+6dInWeUWsROfJtEo0l/IuFyqKmvlqJ33t2jPwa83+zpXGo56hNvgNSr35JkAJQrhqEjQu8CqjkBT2wtqpRzal4O3EjorNt4p9/ekyzYj8hDr+3Z2d6mFBz9YMMZCB8xkAQTtuBIJVsHIJDdSPYHF+UvDoTcT2wTQKBgQDL4XTz3J7Jkd6C6uIJcabPTj0qwEgCoHIO8uJquvLBllzyO+2bsy/FkGQTkoVirU5az91PonrpK3UZPcLRvpjXZn6VRMYp8XJBIC84tVkYnf1wWTUEWAZqz8Hkqxlzj828hjoV7ffUU7Tf5Phgs98mmvz1IvV2WOfu+mOKGqCRHQKBgQDTaIfQdpkQ07HJTZp7jFxnry2kJV/rg6QIeYqf3mgpvXAxjgwCzg9fv/3opaFLZRW/o9CCBzl1QRLa04dqBeQvO5CBg/CEoAH3RnBjEhdAHCC/UzgC0UMOqcdtzyPEYpBfX+usTbKHFKj/5tqH8ez5tgbEiY6fEXzYrKYmrRNLnQKBgQCaY5SIxlyID6oMQYoB1MqY2YqAQTNGqfE1WMAUSpVh+1dPKAp6iWp3lSvzllTjsJFRO28/yq6Au6PlBVvuMQLUuozIxFe7k1cN8i1QKCPb/GfbF/KJ446Ye9M8MkUHubH2PT7nNFkjtG+XzHA56nvlZCCCEYMHp7OayIlwH1HQEQKBgA//WBuNejySNUIvTK9E6l9WmtlsR5Ky/rhusZDIeqFqKcMR/5ThPFCqO6K+/+1NVgkIVxE7sXnTAkU4DKHH6w9OIx7a0ECsa5uyXkqZsGDjfzmgPvR6jHIxVBXae8J5itsMTWo5uG21eH4K3BHr+6COG95GUEwU5OGh+r5ND5on';
    //请填写支付宝公钥，一行字符串
    private $alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAs1LCnaaA8ajN2t+CHtiXJiOs0TpBcKW0ExsjgptdF5kYNrAyotCGhCtesukgqtRcwfnVme9r4g13IoZohQRFVtumDTlVSlkDYuGDrdyH4Z9SUMLvTscwEuqmJLALw4EvsH0R/Mq3obDYhsBcC5whaxAx7tgaq0LerT7+r+acQftMSQCl+z5rtcFxpe5gAhKO2LLXxdt1Z8hlS2R2cX4DCU6jPjgCLIaoA7FnyPaWjO3OCf/rUfdkqhWUza7Kll1GsoxrsSXeqJmy+H6EnuamkgerSmdx/dOa73kylzBm01d+TFVBmGWkDrojLzGcYxf5xMRFOwJYvoNjf3JPxviVuQIDAQAB';
    
    private $signType='RSA2';
    private $returnUrl=ALIPAY_RETURN_URL;              //同步地址
    private $notifyUrl=ALIPAY_NOTIFY_URL;              //异步地址
    private $product_code='FAST_INSTANT_TRADE_PAY';//销售产品码,目前仅支持FAST_INSTANT_TRADE_PAY
    
    /*
     * 请求支付宝支付页
     * @subject 订单标题
     * @total_amount    订单总金额
     * @body    订单描述
     */
    function doAlipay($data){      
        $aop = new AopClient ();        
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey = $this->rsaPrivateKey;
        $aop->alipayrsaPublicKey = $this->alipayrsaPublicKey;
        $request = new AlipayTradePagePayRequest ();
        $request->setReturnUrl($this->returnUrl);       
        $request->setNotifyUrl($this->notifyUrl);
        $data['product_code']=$this->product_code;  
        //$request->setBizContent('{"product_code":"FAST_INSTANT_TRADE_PAY","out_trade_no":"20150320010101001","subject":"Iphone6 16G","total_amount":"88.88","body":"Iphone6 16G"}');
        $data=json_encode($data);  
        $request->setBizContent($data);     
        //请求  
        $result = $aop->pageExecute ($request);    
        //输出
        echo $result;
    }
    
    
    /*验证签名
     * sing_type暂设置的rsa2
     */
    function checkSign($data){ 
        $aop = new AopClient ();
        return $aop->rsaCheckV1($data,array(),$this->signType);
    }
   
    
    
    /*
     * 交易查询
     * @out_trade_no    商户订单号
     * @trade_no        支付宝交易流水号
     */
    public function tradeQuery($data){
        $aop = new AopClient ();  
        $request = new AlipayTradeQueryRequest (); 
        $request->setBizContent(json_encode($data));
        $result = $aop->execute ( $request); 

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;     
        if(!empty($resultCode)&&$resultCode == 10000){
            return true;;
        } else {
            return false;
        }
    }
}