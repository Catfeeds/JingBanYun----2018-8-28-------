<?php
/**
 * 短信接口
 */

namespace Common\Common;
use Think\Log;      //日志类
use Think\Controller;
use Common\Common\CSV;
use Common\Common\DES3;


/**
 * Created by PhpStorm.
 * User: UCPAAS JackZhao
 * Date: 2014/10/22
 * Time: 12:04
 * Dec : ucpass php sdk
 */
class SMS
{

    /**
     *  云之讯REST API版本号。当前版本号为：2014-06-30
     */
    const SoftVersion = "2014-06-30";
    /**
     * API请求地址
     */
    const BaseUrl = "https://api.ucpaas.com/";
    /**
     * @var string
     * 开发者账号ID。由32个英文字母和阿拉伯数字组成的开发者账号唯一标识符。
     */
    private $accountSid = "d4f20e43efccf152531da29abf6d518a";//开发者自己的token
    /**
     * @var string
     * 开发者账号TOKEN
     */
    private $token = "a7a3a6097ac3c8270ecacc0513502d4f";//开发者自己的token
    /**
     * @var string
     * 时间戳
     */
    private $timestamp;

    private $appId          ="52f5e069050a497bbe423df7fa1bfe27";   //开发者自己申请的应用ID
    private $templateId1     ="25335";   //短信验证码模版ID
    private $templateId2    = "34172";  //用户注册短信通知模版ID
    private $templateId3    = "34635";  //孩子注册给家长发送信息模版ID
    private $errorInfoTemplate  = "42243";  //平台错误信息模版ID
    private $addUnRegStudentTemplate = "272440";
    private $to             ="";   //需要获取验证码的手机号
    private $para           ="";   //短信所需参数，个数与模板中变量个数一直，多个参数时使用下面格式“a,b,c”
    private $version        ="2014-06-30";   //目前就是这个值不用修改，也可以在接口文档中查到
    private $server         ="https://api.ucpaas.com";   //目前就是这个值不用修改
    private $sign           = null;


    /**
     * @param $options 数组参数必填
     * $options = array(
     *
     * )
     * @throws Exception
     */
    public function  __construct()
    {
        $options['accountsid'] = $this->accountSid;
        $options['token'] = $this->authToken;

        if (is_array($options) && !empty($options)) {
            $this->timestamp = date("YmdHis") + 7200;
        } else {
            throw new Exception("非法参数");
        }
    }

    /**
     * @return string
     * 包头验证信息,使用Base64编码（账户Id:时间戳）
     */
    private function getAuthorization()
    {
        $data = $this->accountSid . ":" . $this->timestamp;
        return trim(base64_encode($data));
    }

    /**
     * @return string
     * 验证参数,URL后必须带有sig参数，sig= MD5（账户Id + 账户授权令牌 + 时间戳，共32位）(注:转成大写)
     */
    private function getSigParameter()
    {
        $sig = $this->accountSid . $this->token . $this->timestamp;
        return strtoupper(md5($sig));
    }

    /**
     * @param $url
     * @param string $type
     * @return mixed|string
     */
    private function getResult($url, $body = null, $type = 'json',$method)
    {
        $data = $this->connection($url,$body,$type,$method);
        if (isset($data) && !empty($data)) {
            $result = $data;
        } else {
            $result = '没有返回数据';
        }
        return $result;
    }

    /**
     * @param $url
     * @param $type
     * @param $body  post数据
     * @param $method post或get
     * @return mixed|string
     */
    private function connection($url, $body, $type,$method)
    {
        if ($type == 'json') {
            $mine = 'application/json';
        } else {
            $mine = 'application/xml';
        }
        if (function_exists("curl_init")) {
            $header = array(
                'Accept:' . $mine,
                'Content-Type:' . $mine . ';charset=utf-8',
                'Authorization:' . $this->getAuthorization(),
            );
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            if($method == 'post'){
                curl_setopt($ch,CURLOPT_POST,1);
                curl_setopt($ch,CURLOPT_POSTFIELDS,$body);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $result = curl_exec($ch);
            curl_close($ch);
        } else {
            $opts = array();
            $opts['http'] = array();
            $headers = array(
                "method" => strtoupper($method),
            );
            $headers[]= 'Accept:'.$mine;
            $headers['header'] = array();
            $headers['header'][] = "Authorization: ".$this->getAuthorization();
            $headers['header'][]= 'Content-Type:'.$mine.';charset=utf-8';

            if(!empty($body)) {
                $headers['header'][]= 'Content-Length:'.strlen($body);
                $headers['content']= $body;
            }

            $opts['http'] = $headers;
            $result = file_get_contents($url, false, stream_context_create($opts));
        }
        return $result;
    }

    /**
     * @param string $type 默认json,也可指定xml,否则抛出异常
     * @return mixed|string 返回指定$type格式的数据
     * @throws Exception
     */
    public function getDevinfo($type = 'json')
    {
        if ($type == 'json') {
            $type = 'json';
        } elseif ($type == 'xml') {
            $type = 'xml';
        } else {
            throw new Exception("只能json或xml，默认为json");
        }
        $url = self::BaseUrl . self::SoftVersion . '/Accounts/' . $this->accountSid . '?sig=' . $this->getSigParameter();
        $data = $this->getResult($url,null,$type,'get');
        return $data;
    }


    /**
     * @param $appId 应用ID
     * @param $clientType 计费方式。0  开发者计费；1 云平台计费。默认为0.
     * @param $charge 充值的金额
     * @param $friendlyName 昵称
     * @param $mobile 手机号码
     * @return json/xml
     */
    public function applyClient($appId, $clientType, $charge, $friendlyName, $mobile, $type = 'json')
    {
        $url = self::BaseUrl . self::SoftVersion . '/Accounts/' . $this->accountSid . '/Clients?sig=' . $this->getSigParameter();
        if ($type == 'json') {
            $body_json = array();
            $body_json['client'] = array();
            $body_json['client']['appId'] = $appId;
            $body_json['client']['clientType'] = $clientType;
            $body_json['client']['charge'] = $charge;
            $body_json['client']['friendlyName'] = $friendlyName;
            $body_json['client']['mobile'] = $mobile;
            $body = json_encode($body_json);
        } elseif ($type == 'xml') {
            $body_xml = '<?xml version="1.0" encoding="utf-8"?>
                        <client><appId>'.$appId.'</appId>
                        <clientType>'.$clientType.'</clientType>
                        <charge>'.$charge.'</charge>
                        <friendlyName>'.$friendlyName.'</friendlyName>
                        <mobile>'.$mobile.'</mobile>
                        </client>';
            $body = trim($body_xml);
        } else {
            throw new Exception("只能json或xml，默认为json");
        }
        $data = $this->getResult($url, $body, $type,'post');
        return $data;
    }

    /**
     * @param $clientNumber
     * @param $appId
     * @param string $type
     * @return mixed|string
     * @throws Exception
     */
    public function releaseClient($clientNumber,$appId,$type = 'json'){
        $url = self::BaseUrl . self::SoftVersion . '/Accounts/' . $this->accountSid . '/dropClient?sig=' . $this->getSigParameter();
        if($type == 'json'){
            $body_json = array();
            $body_json['client'] = array();
            $body_json['client']['clientNumber'] = $clientNumber;
            $body_json['client']['appId'] = $appId;
            $body = json_encode($body_json);
        }elseif($type == 'xml'){
            $body_xml = '<?xml version="1.0" encoding="utf-8"?>
                        <client>
                        <clientNumber>'.$clientNumber.'</clientNumber>
                        <appId>'.$appId.'</appId >
                        </client>';
            $body = trim($body_xml);
        }else {
            throw new Exception("只能json或xml，默认为json");
        }
        $data = $this->getResult($url, $body, $type,'post');
        return $data;
    }

    /**
     * @param $appId
     * @param $start
     * @param $limit
     * @param string $type
     * @return mixed|string
     * @throws Exception
     */
    public function getClientList($appId,$start,$limit,$type = 'json'){
        $url = self::BaseUrl . self::SoftVersion . '/Accounts/' . $this->accountSid . '/clientList?sig=' . $this->getSigParameter();
        if($type == 'json'){
            $body_json = array('client'=>array(
                'appId'=>$appId,
                'start'=>$start,
                'limit'=>$limit
            ));
            $body = json_encode($body_json);
        }elseif($type == 'xml'){
            $body_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                        <client>
                            <appId>'.$appId.'</appId>
                            <start>'.$start.'</start>
                            <limit>'.$limit.'</limit>
                        </client>';
            $body = trim($body_xml);
        }else {
            throw new Exception("只能json或xml，默认为json");
        }
        $data = $this->getResult($url, $body, $type,'post');
        return $data;
    }

    /**
     * @param $appId
     * @param $clientNumber
     * @param string $type
     * @return mixed|string
     * @throws Exception
     */
    public function getClientInfo($appId,$clientNumber,$type = 'json'){
        if ($type == 'json') {
            $type = 'json';
        } elseif ($type == 'xml') {
            $type = 'xml';
        } else {
            throw new Exception("只能json或xml，默认为json");
        }
        $url = self::BaseUrl . self::SoftVersion . '/Accounts/' . $this->accountSid . '?sig=' . $this->getSigParameter(). '&clientNumber='.$clientNumber.'&appId='.$appId;
        $data = $this->getResult($url,null,$type,'get');
        return $data;
    }

    /**
     * @param $appId
     * @param $mobile
     * @param string $type
     * @return mixed|string
     * @throws Exception
     */
    public function getClientInfoByMobile($appId,$mobile,$type = 'json'){
        if ($type == 'json') {
            $type = 'json';
        } elseif ($type == 'xml') {
            $type = 'xml';
        } else {
            throw new Exception("只能json或xml，默认为json");
        }
        $url = self::BaseUrl . self::SoftVersion . '/Accounts/' . $this->accountSid . '/ClientsByMobile?sig=' . $this->getSigParameter(). '&mobile='.$mobile.'&appId='.$appId;
        $data = $this->getResult($url,null,$type,'get');
        return $data;
    }

    /**
     * @param $appId
     * @param $date
     * @param string $type
     * @return mixed|string
     * @throws Exception
     */
    public function getBillList($appId,$date,$type = 'json'){
        $url = self::BaseUrl . self::SoftVersion . '/Accounts/' . $this->accountSid . '/billList?sig=' . $this->getSigParameter();
        if($type == 'json'){
            $body_json = array('appBill'=>array(
                'appId'=>$appId,
                'date'=>$date,
            ));
            $body = json_encode($body_json);
        }elseif($type == 'xml'){
            $body_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                        <appBill>
                            <appId>'.$appId.'</appId>
                            <date>'.$date.'</date>
                        </appBill>';
            $body = trim($body_xml);
        }else {
            throw new Exception("只能json或xml，默认为json");
        }
        $data = $this->getResult($url, $body, $type,'post');
        return $data;
    }

    /**
     * @param $appId
     * @param $clientNumber
     * @param $chargeType
     * @param $charge
     * @param string $type
     * @return mixed|string
     * @throws Exception
     */
    public function chargeClient($appId,$clientNumber,$chargeType,$charge,$type = 'json'){
        $url = self::BaseUrl . self::SoftVersion . '/Accounts/' . $this->accountSid . '/chargeClient?sig=' . $this->getSigParameter();
        if($type == 'json'){
            $body_json = array('client'=>array(
                'appId'=>$appId,
                'clientNumber'=>$clientNumber,
                'chargeType'=>$chargeType,
                'charge'=>$charge
            ));
            $body = json_encode($body_json);
        }elseif($type == 'xml'){
            $body_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                        <client>
                            <clientNumber>'.$clientNumber.'</clientNumber>
                            <chargeType>'.$chargeType.'</chargeType>
                            <charge>'.$charge.'</charge>
                            <appId>'.$appId.'</appId>
                        </client>';
            $body = trim($body_xml);
        }else {
            throw new Exception("只能json或xml，默认为json");
        }
        $data = $this->getResult($url, $body, $type,'post');
        return $data;

    }

    /**
     * @param $appId
     * @param $fromClient
     * @param $to
     * @param null $fromSerNum
     * @param null $toSerNum
     * @param string $type
     * @return mixed|string
     * @throws Exception
     */
    public function callBack($appId,$fromClient,$to,$fromSerNum=null,$toSerNum=null,$type = 'json'){
        $url = self::BaseUrl . self::SoftVersion . '/Accounts/' . $this->accountSid . '/Calls/callBack?sig=' . $this->getSigParameter();
        if($type == 'json'){
            $body_json = array('callback'=>array(
                'appId'=>$appId,
                'fromClient'=>$fromClient,
                'fromSerNum'=>$fromSerNum,
                'to'=>$to,
                'toSerNum'=>$toSerNum
            ));
            $body = json_encode($body_json);
        }elseif($type == 'xml'){
            $body_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                        <callback>
                            <fromClient>'.$fromClient.'</clientNumber>
                            <fromSerNum>'.$fromSerNum.'</chargeType>
                            <to>'.$to.'</charge>
                            <toSerNum>'.$toSerNum.'</toSerNum>
                            <appId>'.$appId.'</appId>
                        </callback>';
            $body = trim($body_xml);
        }else {
            throw new Exception("只能json或xml，默认为json");
        }
        $data = $this->getResult($url, $body, $type,'post');
        return $data;
    }

    /**
     * 语音验证码
     * @param $appId
     * @param $verifyCode
     * @param $to
     * @param string $type
     * @return mixed|string
     * @throws Exception
     */
    public function voiceCode($verifyCode,$to,$type = 'json'){
        $appId = $this->appId;

        $url = self::BaseUrl . self::SoftVersion . '/Accounts/' . $this->accountSid . '/Calls/voiceCode?sig=' . $this->getSigParameter();
        if($type == 'json'){
            $body_json = array('voiceCode'=>array(
                'appId'=>$appId,
                'verifyCode'=>$verifyCode,
                'to'=>$to
            ));
            $body = json_encode($body_json);
        }elseif($type == 'xml'){
            $body_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                        <voiceCode>
                            <verifyCode>'.$verifyCode.'</clientNumber>
                            <to>'.$to.'</charge>
                            <appId>'.$appId.'</appId>
                        </voiceCode>';
            $body = trim($body_xml);
        }else {
            throw new Exception("只能json或xml，默认为json");
        }
        $data = $this->getResult($url, $body, $type,'post');
        return $data;
    }

    public function getIP() {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        }
        elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        }
        elseif (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        }
        elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');

        }
        elseif (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        }
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public function getUserStatus($to)
    {
        $_ip = get_client_ip();
        $ip_table = M("jby_iplist");
        $ipinfo = $ip_table->where("ip='{$_ip}'")->find();
        if(empty($ipinfo))
        {
            return false;
        }

        $to =  strval($to);
        $CodeModel = M('misc_register_phone_validcode');
        $time = time();
        $time = $time-60;
        $code = $CodeModel->where("telephone='{$to}' and create_at>$time")->find();
        if(!empty($code))
        {
            return false;
        }

        $obj_verify_code_history = M("verify_code_history");

        //
        $verify_code_history_list = $obj_verify_code_history->select();

        $ip_list = array();
        foreach($verify_code_history_list as $val)
        {
            $ip_list[] = $val['ip_address'];
        }

        $ip = $this->getIP();
        if(in_array($ip,$ip_list))
        {
            //return false;
        }

        $select_time = time()-(60*60*24);
        $count_for_tel = $obj_verify_code_history->where("telephone='{$to}' and create_at>{$select_time}")->count();
        if($count_for_tel>=20)
        {
            //return false;
        }

        $count_for_ip = $obj_verify_code_history->where("ip_address='{$ip}' and create_at>{$select_time}")->count();
        if($count_for_ip>=500)
        {
            //return false;
        }

        //inser send log
        $add=array();
        $add['ip_address'] = $ip;
        $add['telephone'] = $to;
        $add['create_at'] = time();
        $add['verify_code'] = 0;

        $aid = $obj_verify_code_history->add($add);
        if($aid==false) {
            //insert error
            return false;
        }

        return true;
    }

    /**
     * 短信验证码
     * @param $appId
     * @param $to 手机号码
     * @param $templateId
     * @param null $param 举例：（‘注册教师’，123456）
     * @param string $type
     * @return mixed|string
     * @throws Exception
     */
    public function templateSMS($to=null,$param=null,$type = 'json'){

        $userstatus=$this->getUserStatus($to);

        if($userstatus==false)
        {
            $out['status'] = false;
            return $out;
        }

        if(empty($to))
        {
            $out['status'] = false;
            return $out;
        }
        $result = $this->send($to,$this->appId,$this->templateId1,$param,$type);
        /*if(false === $result['status'])
        {
            $result = $this->send($to,$this->appId,$this->templateId1,$param,$type);
        }*/
        return $result;
    }

    public function templateSMSDisk($to=null,$param=null,$type = 'json'){

        $result = $this->send($to,$this->appId,$this->templateId1,$param,$type);
        if(false === $result['status'])
        {
            $result = $this->send($to,$this->appId,$this->templateId1,$param,$type);
        }
        return $result;
    }

    //下单
    public function templateSMSOrder($to=null,$param=null,$type = 'json'){

        $result = $this->send($to,$this->appId,76029,$param,$type);
        if(false === $result['status'])
        {
            $result = $this->send($to,$this->appId,76029,$param,$type);
        }
        return $result;
    }

    //取消订单
    public function templateSMSCancelOrder($to=null,$param=null,$type = 'json'){

        $result = $this->send($to,$this->appId,76031,$param,$type);
        if(false === $result['status'])
        {
            $result = $this->send($to,$this->appId,76031,$param,$type);
        }
        return $result;
    }
    //教师拉未注册学生
    public function templateSMSAddStudent($to=null,$studentName='',$teacherName='',$className='',$type='json')
    {
        $param = $studentName.','.$teacherName.','.$className;
        $result = $this->send($to,$this->appId,$this->addUnRegStudentTemplate,$param,$type);
        if(false === $result['status'])
        {
            $result = $this->send($to,$this->appId,$this->addUnRegStudentTemplate,$param,$type);
        }
        return $result;
    }
    
    /**
     * 新版短信验证码
     * @param $appId
     * @param $to 手机号码
     * @param $templateId
     * @param null $param 举例：（‘注册教师’，123456）
     * @param string $type
     * @return mixed|string
     * @throws Exception
     */
    public function newTemplateSMS($to=null,$param=null,$type = 'json'){

        /*if($userstatus==false)
        {
            $out['status'] = false;
            return $out;
        }*/

        if(empty($to))
        {
            $out['status'] = false;
            return $out;
        }

        return $this->send($to,$this->appId,$this->templateId1,$param,$type);
    }

    /**
     * @param $to 手机号码
     * @sendvalue 恭喜您已成功注册京版云平台，感谢您对京版云平台的使用，
     *           请妥善保管您的账号密码。您可通过京版云官网或手机APP登录并使用平台。
     */
    public function newUserNotice($to=null,$type='json')
    {
        $userstatus=$this->getUserStatus($to);

        $out = array();

        if($userstatus==false)
        {
            $out['status'] = false;
            return $out;
        }

        if(empty($to))
        {
            $out['status'] = false;
            return $out;
        }

        return $this->send($to,$this->appId,$this->templateId2,'',$type);
    }

    /**
     * @param $to 手机号码
     * @sendvalue 恭喜您，您的孩子“{1}”已成功注册京版云平台，感谢您对京版云平台的使用，您的孩子可通过京版云官网或手机APP登录并使用平台。
     * @param null $param 举例：'张三' //孩子姓名
     */
    public function noticeParentAddStudent($to=null,$param=null,$type='json')
    {
        $userstatus=$this->getUserStatus($to);

        $out = array();

        if($userstatus==false)
        {
            $out['status'] = false;
            return $out;
        }

        if(empty($to) || empty($param))
        {
            $out['status'] = false;
            return $out;
        }

        return $this->send($to,$this->appId,$this->templateId3,$param,$type);
    }

    /**
     * @param $to 手机号码
     * @sendvalue 平台错误信息
     * @param null $param 举例：'张三' //孩子姓名
     */
    public function noticePlatformError($to=null,$param=null,$type='json')
    {
        $userstatus=$this->getUserStatus($to);

        $out = array();

        if($userstatus==false)
        {
            $out['status'] = false;
            return $out;
        }

        if(empty($to) || empty($param))
        {
            $out['status'] = false;
            return $out;
        }

        return $this->send($to,$this->appId,$this->errorInfoTemplate,$param,$type);
    }
    private function send($to,$appId,$templateId,$param='',$datatype='json')
    {
        $url = self::BaseUrl . self::SoftVersion . '/Accounts/' . $this->accountSid . '/Messages/templateSMS?sig=' . $this->getSigParameter();

        if($datatype == 'json'){
            $body_json = array('templateSMS'=>array(
                'appId'=>$appId,
                'templateId'=>$templateId,
                'to'=>$to,
                'param'=>$param
            ));
            $body = json_encode($body_json);
        }elseif($datatype == 'xml'){
            $body_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                        <templateSMS>
                            <templateId>'.$templateId.'</templateId>
                            <to>'.$to.'</to>
                            <param>'.$param.'</param>
                            <appId>'.$appId.'</appId>
                        </templateSMS>';
            $body = trim($body_xml);
        }else {
            throw new Exception("只能json或xml，默认为json");
        }
        $data = $this->getResult($url, $body, $datatype,'post');

        $data = json_decode($data,true);

        $out['status']=true;
        $out['data'] = $data;
        if(isset($data['resp']['respCode']) && $data['resp']['respCode']==='000000')
        {
            return $out;
        }else{
            $out['status']=false;
            $out['reason']=$data['resp']['respCode'];
            \Think\Log::write('短信发送错误,错误信息:'.json_encode($data),C('LOG_PATH').'SMS.ERR');
            return $out;
        }

    }
}
//
//class SMS{
//    /**
//     * 短信接口平台
//     * 官网： http://www.ucpaas.com/
//     * //签名 J*B*Y*@*@*!
//     */
//
//    private $accountSid     ="chinajrkt@126.com";//开发者自己的账号
//    private $authToken      ="a7a3a6097ac3c8270ecacc0513502d4f";   //开发者自己的token
//    private $appId          ="52f5e069050a497bbe423df7fa1bfe27";   //开发者自己申请的应用ID
//    private $templateId1     ="25335";   //短信模板ID
//    private $to             ="";   //需要获取验证码的手机号
//    private $para           ="";   //短信所需参数，个数与模板中变量个数一直，多个参数时使用下面格式“a,b,c”
//    private $version        ="2014-06-30";   //目前就是这个值不用修改，也可以在接口文档中查到
//    private $server         ="api.ucpaas.com";   //目前就是这个值不用修改
//    private $sign           = null;
//
//    public function __construct()
//    {
//        $this->password = md5($this->password);
//
//        $this->vcode = rand(100000,999999);
//
//        $this->sign = $this->getsign();
//    }
//
//    /**
//     * 发送短信验证码
//     */
//    public function sendVcode($mobile)
//    {
//
//        $this->mobile = $mobile;
//        $this->connect = '您的验证码为：'.$this->vcode.'，请妥善保管。';
//
//        if($this->send())
//        {
//            return $this->vcode;
//        }
//
//        return false;
//    }
//
//    private function getsign()
//    {
//        return md5($this->accountSid.$this->authToken.date("YmdHis"));
//    }
//
//    /**
//     * @return bool
//     * 短信发送状态码
//     * 100 发送成功
//     * 101 验证失败
//     * 102 短信不足
//     * 103 操作失败
//     * 104 非法字符
//     * 105 内容过多
//     * 106 号码过多
//     * 107 频率过快
//     * 108 号码内容空
//     * 109 帐号冻结
//     * 110 禁止频繁单条发送
//     * 111 系统暂定发送
//     * 112 号码错误
//     * 113 定时时间格式不对
//     * 114 帐号被锁，十分钟后登录
//     * 115 连接失败
//     * 116 禁止接口发送
//     * 117 绑定IP不正确
//     * 120 系统升级
//     */
//    private function send()
//    {
//        $MSG['username']    =   $this->username;
//        $MSG['password']    =   $this->password;
//        $MSG['phone']       =   $this->mobile;
//        $MSG['content']     =   $this->connect;
//        $MSG['time']        =   $this->sendTime;
//        $MSG['encode']      =   'utf8';
//        $MSG['action']      =    $this->action;
//
//        //$this->sendUrl = $this->sendUrl.'?'.trim(implode("&", array_merge(array_keys($MSG),array_values($MSG))));
//
//        $url = '';
//
//        foreach($MSG as $key=>$val)
//        {
//            $url .= '&'.$key.'='.$val;
//        }
//
//        $sendUrl = $this->sendUrl;
//
//        $this->sendUrl = $sendUrl.'?'.trim($url,'&');
//
//        $ch = curl_init();
//
//        //设置参数
//        curl_setopt ($ch, CURLOPT_URL,$this->sendUrl);
//        curl_setopt ($ch, CURLOPT_RETURNTRANSFER,0);
//
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        $return = curl_exec($ch);//执行并结果返回
//
//        if(curl_errno($ch))
//        {
//            Log::record(date("Y_m_d:Y:m:d").'curl程序启动失败：'.'==错误码－－ch='.$ch,Log::EMERG);
//            return false;
//        }
//
//        curl_close ($ch);//关掉进程
//
//        if(intval($return)==$this->send_status_ok)
//        {
//            return true;
//        }
//
//        //记录错误日志
//
//        Log::record(date("Y_m_d:Y:m:d").'短信发送失败：'.$this->connect.'==错误码：'.$return,Log::EMERG);
//
//        return false;
//    }
//}
?>