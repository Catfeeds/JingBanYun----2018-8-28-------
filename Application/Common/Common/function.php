<?php
use Common\Common\DES3;


function getDateFormat( $time ) {
    $d = floor($time / (3600*24));
    $h = floor(($time % (3600*24)) / 3600);
    $m = floor((($time % (3600*24)) % 3600) / 60);
    if($d>'0'){
        return $d.'天'.$h.'小时'.$m.'分';
    }else{
        if($h!='0'){
            return $h.'小时'.$m.'分';
        }else{
            return $m.'分';
        }
    }
}

if(!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

function regExtract($sql, $expression)
{
    preg_match_all($expression, $sql, $matches);
    return array_unique(array_pop($matches));
}
/**
 * 比较版本号大小
 *
 * @param $sourceVersion string 源版本
 * @param $targetVersion string 目标版本
 * @return integer 0--相等 -1--source<target 1--source>target
 */
function compareVersion($sourceVersion,$targetVersion)
{
    $sVersionArray = explode('.',$sourceVersion);
    $tVersionArray = explode('.',$targetVersion);
    for($i=0;$i< min(sizeof($sVersionArray),sizeof($tVersionArray));$i++)
    {

        $iSource = intval($sVersionArray[$i]);
        $iTarget = intval($tVersionArray[$i]);
        if($iSource < $iTarget) {
            return -1;
        }
        else if($iSource > $iTarget){
            return 1;
        }
    }
    return 0;
}
function useJPush($channelId)
{
    if(empty($channelId))
        return 0;
    if(intval($channelId) != $channelId)
        return 1;
    return 0;
}

function pushData($data = array(),$isUserDefinedMessage=0)
{
  $controller = new \Home\Controller\ApiController();

  $result = $controller->sendTo($data,$isUserDefinedMessage);
  return $result;
}
function generatePushData( $ids='', $msg=array())
{
    $newMsg = array('extras'=>array());
    $title = $msg['title'];
    foreach($msg as $key=>$val)
    {
        switch($key)
        {
            case 'title':break;
            case 'aps': if(isset($val['alert']))
                           $title = $val['alert'];
                         if(isset($val['sound']))
                           $newMsg['sound'] = $val['sound'];
                         if(isset($val['badge']))
                           $newMsg['badge'] = $val['badge'];
                         break;
            case 'badge':$newMsg['badge'] = $val;break;
            case 'sound':$newMsg['sound'] = $val;break;
            case 'custom_content': foreach($msg['custom_content'] as $subKey=>$subVal)
            {
                $newMsg['extras'][$subKey] = $subVal;
            }
            break;
            default:$newMsg['extras'][$key] = $val;break;
        }
    };
    return array('id'=>$ids,'title'=>$title,'msg'=>$newMsg);
}
//对某个字符串进行加密。
function encrypt_string($input){
	$des=new DES3();
	$string=$des->des3_encrypt($input);
	return $string;
}
//查看中文汉字的长度
function _mb_strlen($str, $chr = 'utf-8')
{
    return mb_strlen($str, $chr);
}

//对某个字符串进行解密。
function decrypt_string($input){
	$des=new DES3();
	$string=$des->des3_decrypt($input);
	return $string;
}

//按照某个字段对二维数组进行排序
function my_sort($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC ){   
        if(is_array($arrays)){   
            foreach ($arrays as $array){   
                if(is_array($array)){   
                    $key_arrays[] = $array[$sort_key];   
                }else{   
                    return false;   
                }   
            }   
        }else{   
            return false;   
        }  
        array_multisort($key_arrays,$sort_order,$sort_type,$arrays);   
        return $arrays;   
} 


//设置来源参数
function setSourceParameter(){
    if(!empty($_SERVER['HTTP_REFERER'])){
        $from_url=$_SERVER['HTTP_REFERER'];
        if($start_index=strrpos($from_url, 'from=')){
            $from_url_str=mb_substr($from_url,$start_index,strlen($from_url), 'utf-8');
            $from_url_arr=explode('=', $from_url_str);
            $param=$from_url_arr[1]; 
            if(in_array($param,C('SOURCE_PARAM'))){
                session('from_param',$param);
            }  
        }    
    } 
}

function phpescape($str)
{
    $sublen=strlen($str);
    $retrunString="";
    for ($i=0;$i<$sublen;$i++)
    {
        if(($str[$i])=='"' || ($str[$i])==':' ||($str[$i])=='{' ||($str[$i])=='}' ||($str[$i])=='/' || ($str[$i])=='=')
        {
            $tmpString=bin2hex(iconv("gb2312","ucs-2",substr($str,$i,1)));
            $retrunString.="%".substr($tmpString,2,2);

        } else
        {
            $retrunString.=$str[$i];//"%".dechex(ord($str[$i]));
        }
    }
    return $retrunString;
}

// 
if($_SERVER['HTTP_HOST']=='jby.com' || $_SERVER['HTTP_HOST']=='www.jby.com' || $_SERVER['HTTP_HOST']=='jbyy.com' || $_SERVER['HTTP_HOST']=='www.jbyy.com'){
    function array_column($input, $columnKey, $indexKey=null){      
        //if(!function_exists('array_column')){ 
            $columnKeyIsNumber  = (is_numeric($columnKey))?true:false; 
            $indexKeyIsNull            = (is_null($indexKey))?true :false; 
            $indexKeyIsNumber     = (is_numeric($indexKey))?true:false; 
            $result                         = array(); 
            foreach((array)$input as $key=>$row){ 
                if($columnKeyIsNumber){ 
                    $tmp= array_slice($row, $columnKey, 1); 
                    $tmp= (is_array($tmp) && !empty($tmp))?current($tmp):null; 
                }else{ 
                    $tmp= isset($row[$columnKey])?$row[$columnKey]:null; 
                } 
                if(!$indexKeyIsNull){ 
                    if($indexKeyIsNumber){ 
                      $key = array_slice($row, $indexKey, 1); 
                      $key = (is_array($key) && !empty($key))?current($key):null; 
                      $key = is_null($key)?0:$key; 
                    }else{ 
                      $key = isset($row[$indexKey])?$row[$indexKey]:0; 
                    } 
                } 
                $result[$key] = $tmp; 
            } 
            return $result;   
        } 
}

/**
 * 对所有的用户进行的推送
 * @sdk 推送连接句柄
 * @msg Array 要发送的信息 title 和 description描述
 * @opts 消息控制选项配置
 * @author peen
 */
function sendPushAll( $sdk,$msg,$opts)
{
    Vendor('Bpush.bpush');
    // 创建消息内容
    $rs = pushData(generatePushData('',$msg));
    $rs &= $sdk -> pushMsgToAll($msg,$opts);
    return $rs;
}

/**
 * 对单个用户进行的推送
 * @default_apiKey integer key
 * @default_secretkey integer 密钥
 * @msg array 要发送的信息 title 和 description描述
 * @channel_id Array 单个用户的channel_id
 * @author peen
 */
function sendPushUserDevice( $default_apiKey, $default_secretkey,$msg,$channel_id)
{
    Vendor('Bpush.bpush');
    $sdk = new \PushSDK($default_apiKey,$default_secretkey);
    // 创建消息内容

    $msg = array(
        'title' => $msg['title'],
        'description' => $msg['description'],
    );

    // 消息控制选项。
    $opts = array(
        'msg_type' => 1,
    );
    // 发送
    if(1 == useJPush($channel_id))
    {
        $rs = pushData(generatePushData($channel_id,$msg));
    }
    else
    $rs = $sdk -> pushBatchUniMsg($channel_id,$msg,$opts);
    return $rs;
}

/**
 * 对所有用户进行推送
 * @$sdkKeys keys数组
 * @msg array 要发送的信息 title 和 description描述
 * @commonMessage 包含消息推送对应的URL 与推送模块编号
 * @author peen
 */
function sendPushAllDevice($sdkKeys,$msg,$commonMessage)
{	
    Vendor('Bpush.bpush');
    $rs = true;
    $sdk_ios = new \PushSDK($sdkKeys[0]['default_apiKey'],$sdkKeys[0]['default_secretkey']);
    $sdk_android = new \PushSDK($sdkKeys[1]['default_apiKey'],$sdkKeys[1]['default_secretkey']);

    //android push
    $sendMsg = array(
        'title' => $msg['title'],
        'description' => $msg['description'],
        'open_type' => 1,
        'url' => $commonMessage['url'],
        'custom_content' => array(
            'category' => $commonMessage['category']
        )
    );
    $opts = array('msg_type' => 0,'deploy_status' => PUSHCONFIG);

    $rs = pushData(generatePushData('',$sendMsg));

    $rs &= $sdk_android -> pushMsgToAll($sendMsg,$opts);

    //ios push
    $sendMsg = array(
        'aps' => array('alert' => $msg['title'],
            'sound' => 'default',
        ),
        'url' => $commonMessage['url'],
        'category' => $commonMessage['category']
    );
    $opts['msg_type']=1;
    $rs &= $sdk_ios -> pushMsgToAll($sendMsg,$opts);
    return $rs;
}


/**
 * 对指定用户进行推送
 * @$sdkKeys keys数组
 * @msg array 要发送的信息 title 和 description描述
 * @commonMessage 包含消息推送对应的URL 与推送模块编号
 * @author peen
 */ 
function sendPushClockSend($sdkKeys,$msg_title,$teacher_data)
{ 
    Vendor('Bpush.bpush');
    $rs = true;
    $sdk_ios = new \PushSDK($sdkKeys[0]['default_apiKey'],$sdkKeys[0]['default_secretkey']);
    $sdk_android = new \PushSDK($sdkKeys[1]['default_apiKey'],$sdkKeys[1]['default_secretkey']);
for($i=0;$i<count($teacher_data['android']['channel_id_info']);$i++){ 
        $opts = array('msg_type' => 0,'deploy_status' => PUSHCONFIG);
        $msg = (array(
            'title' => $msg_title,
            'category'=>'101',
            'id'=>$teacher_data['android']['id'][$i] //时钟id
        ));
        if(1 == useJPush($teacher_data['android']['channel_id_info'][$i]))
        {
            $rs &= pushData(generatePushData($teacher_data['android']['channel_id_info'][$i],$msg));
        }
        $rs &= $sdk_android->pushBatchUniMsg(array($teacher_data['android']['channel_id_info'][$i]),$msg,$opts);  
}

for($k=0;$k<count($teacher_data['ios']['channel_id_info']);$k++){  
        $opts = array('msg_type' => 1,'deploy_status' => PUSHCONFIG);
        $msg = array(
                'aps' => array('alert' => $msg_title,
                'sound' => 'default',
                'category'=>'101'
            ),
            'category'=>'101',
            'id'=>$teacher_data['ios']['id'][$i]    //时钟id
        );
    if(1 == useJPush($teacher_data['ios']['channel_id_info'][$i]))
    {
        $rs &= pushData(generatePushData($teacher_data['ios']['channel_id_info'][$i],$msg));
    }
        $rs &= $sdk_ios->pushBatchUniMsg(array($teacher_data['ios']['channel_id_info'][$i]),$msg,$opts);  
    return $rs;
    }
}



function batchSend($sdk_ios,$sdk_android,&$channel_id,$unreadnum,$msg,$machineType,$commonMessage,$isUserDefinedMessage=0)
{

    if($machineType == '2') //android
    {
        $opts = array('msg_type' => 0,'deploy_status' => PUSHCONFIG);

        $msg = (array(
            'title' => $msg['title'],
            'description' => $msg['description'],
            'url' => $commonMessage['url'],
            'category' => $commonMessage['category'],
            'custom_content'=> $msg['extras']
        ));

        if(1 == useJPush($channel_id))
        {
            $rs = pushData(generatePushData($channel_id,$msg),$isUserDefinedMessage);
        }
        else
        $rs = $sdk_android->pushBatchUniMsg($channel_id,$msg,$opts);

    }
    else //ios
    {
        $opts = array('msg_type' => 1,'deploy_status' => PUSHCONFIG);
        $msg = array(
            'aps' => array('alert' => $msg['title'],
                'sound' => 'default',
                'badge' => $unreadnum
            ),
            'url' => $commonMessage['url'],
            'category' => $commonMessage['category'],
            'custom_content'=> $msg['extras']
        );
        if(1 == useJPush($channel_id))
        {
            $rs = pushData(generatePushData($channel_id,$msg),$isUserDefinedMessage);
        }
        else
        $rs = $sdk_ios->pushBatchUniMsg($channel_id,$msg,$opts);

    }
    $channel_id = array();
    return $rs;
}


/**
 * 批量用户进行的推送
 * @$sdkKeys keys数组
 * @msg array 要发送的信息 title 和 description描述
 * @$userInfos Array 包含每个用户的channel_id 机器类型(machine_type) 未读消息数(unreadnum)
 * @commonMessage 包含消息推送对应的URL 与推送模块编号
 * @author peen
 */
function batchSendPushUserDevice( $sdkKeys,$msg,$userInfos,$commonMessage,$isUserDefinedMessage=0)
{
    Vendor('Bpush.bpush');
    $rs = true;
    $sdk_ios = new \PushSDK($sdkKeys[0]['default_apiKey'],$sdkKeys[0]['default_secretkey']);
    $sdk_android = new \PushSDK($sdkKeys[1]['default_apiKey'],$sdkKeys[1]['default_secretkey']);
    //按机器类型和未读数量对消息进行分组
    foreach ($userInfos as $key => $row) {
        $key1[$key]  = $row['machine_type'];
        $key2[$key] = $row['unreadnum'];
    }
    array_multisort($key1,SORT_NUMERIC,SORT_ASC,
        $key2,SORT_NUMERIC,SORT_ASC,$userInfos);

    $count = count($userInfos);
    $lastUnreadNum = 0;
    $lastMachineType = 0;
    $channel_id = array();
    for($i=0;$i<$count;$i++)
    {
        if($i==0)
        {
            $lastUnreadNum = $userInfos[0]['unreadnum'];
            $lastMachineType = $userInfos[0]['machine_type'];

        }
        else if(($userInfos[$i]['unreadnum'] != $lastUnreadNum) || ($userInfos[$i]['machine_type'] != $lastMachineType) || ($i==$count-1) || count($channel_id)==10000)
        {

            $rs = batchSend($sdk_ios,$sdk_android,$channel_id,$lastUnreadNum,$msg,$lastMachineType,$commonMessage,$isUserDefinedMessage);
            usleep(100000);
            $lastUnreadNum = $userInfos[$i]['unreadnurs &= batchSend($sdk_ios,$sdkm'];
            $lastMachineType = $userInfos[$i]['machine_type'];
        }
        $channel_id[] = $userInfos[$i]['channel_id'];
    }
    if(count($channel_id)!=0) {

        $rs = batchSend($sdk_ios, $sdk_android, $channel_id, $lastUnreadNum, $msg, $lastMachineType, $commonMessage,$isUserDefinedMessage);
    }
    return $rs;
}
        


/*
 * @param   operation_role   当前操作者如当前在教师控制器下,当前的操作者为教师  2教师,3学生,4家长       该方法已停止使用
 * $param   vip_status       传递过来的vip状态(配置文件中的vip状态)
 * $param   user_id          用户Id
 * @param   school_id        非必填参数
 * @param   parent_id        非必填参数
 * @param   parent_tel       非必填参数
 */
function give_vip_operation($operation_role,$vip_status,$user_id,$school_id,$parent_id=0,$parent_tel){
    if($operation_role>=2 && $operation_role<=4){
        if($vip_status && $vip_status<=3){
            $vipdata = array(
                'user_id' => $user_id,
                'role_id' => $operation_role,
                'auth_id' => 4,
                'auth_start_time' => time(),
                'auth_end_time' => time()+3600*24*30*3,
                'timetype' => 1
            );
            $auth_type_use = D('Account_auths');

            if($vip_status==1){
                //赠送90天vip
                $vipdata['auth_id']=4;
                $auth_type_use->addUserVip($vipdata);
                return array('status'=>'success','message'=>'');

            }elseif($vip_status==2){
                //普通权限
                $vipdata['auth_id']=2;
                $vipdata['auth_start_time']=0;
                $vipdata['auth_end_time']=0;
                $vipdata['timetype']=0;
                $auth_type_use->addUserVip($vipdata);
                return array('status'=>'success','message'=>'');
            }else{
                if($operation_role==2){
                    //教师
                    if(!($school_info=get_school_vip_info($school_id))){
                        return array('status'=>'failed','message'=>'学校id参数错误或数据为空');
                    }else{
                        if ($school_info['user_auth'] == 3 && time() >= $school_info['auth_start_time'] && time() < $school_info['auth_end_time'] ) {
                            //给予学校的权限
                            $vipdata['timetype']=$school_info['timetype'];
                            $vipdata['auth_id']=3;
                            $vipdata['auth_end_time']=$school_info['auth_end_time'];
                        }else{
                            //普通权限
                            $vipdata['auth_id']=2;
                            $vipdata['auth_start_time']=0;
                            $vipdata['auth_end_time']=0;
                            $vipdata['timetype']=0;
                        }
                        $auth_type_use->addUserVip($vipdata);
                    }
                    return array('status'=>'success','message'=>'');
                }elseif($operation_role==3){
                    //学生
                    if(!($school_info=get_school_vip_info($school_id))){
                        return array('status'=>'failed','message'=>'学校id参数错误或数据为空');
                    }else{
                        if ($school_info['user_auth'] == 3 && time() >= $school_info['auth_start_time'] && time() < $school_info['auth_end_time'] ) {
                            //把学校的权限赋予学生
                            $vipdata['timetype']=$school_info['timetype'];
                            $vipdata['auth_id']=3;
                            $vipdata['auth_end_time']=$school_info['auth_end_time'];
                        }else{
                            //普通权限
                            $vipdata['auth_id']=2;
                            $vipdata['auth_start_time']=0;
                            $vipdata['auth_end_time']=0;
                            $vipdata['timetype']=0;
                        }
                    }
                    $auth_model=M('account_user_and_auth');
                    $auth_model->startTrans();
                    $auth_model->add($vipdata);

                    //学生操作家长信息
                    if($parent_id!=0){
                        $parent_info=get_parent_privilege($parent_id);
                        if(empty($parent_info)){
                            $parent_vip_data=$vipdata;
                            $parent_vip_data['user_id']=$parent_id;
                            $parent_vip_data['role_id']=4;
                            if(!$auth_model->add($parent_vip_data)){
                                $auth_model->rollback();
                                return array('status'=>'failed','message'=>'修改数据失败');
                            }
                        }else{
                            $parent_auth_where['role_id']=4;
                            $parent_auth_where['user_id']=$parent_id;
                            if($vipdata['auth_end_time']>$parent_info['auth_end_time'] && ($vipdata['timetype']==1 || $vipdata['timetype']==2)){
                                $parent_vip_data=$vipdata;
                                $parent_vip_data['user_id']=$parent_id;
                                $parent_vip_data['role_id']=4;
                                if($auth_model->where($parent_auth_where)->save($parent_vip_data)==false){
                                    $auth_model->rollback();
                                    return array('status'=>'failed','message'=>'修改数据失败');
                                }
                            }
                        }
                    }
                    $auth_model->commit();
                    return array('status'=>'success','message'=>'');
                }else{
                    //家长
                    $parent_model=M('auth_parent');
                    $auth_model=M('account_user_and_auth');

                    $parent_auth_where['role_id']=4;
                    $parent_auth_where['user_id']=$user_id;
                    $parent_result=$auth_model->where($parent_auth_where)->find();

                    $student_model=M('auth_student');
                    $student_info=$student_model->where("parent_tel='$parent_tel'")->group("school_id")->select();
                    if(!empty($student_info)){
                        $school_model=M('dict_schoollist');
                        $school_privilege_arr=array();
                        for($i=0;$i<count($student_info);$i++){
                            $school_info=$school_model->where("id=".$student_info[$i]['school_id'])->find();
                            if(!empty($school_info)){
                                if(empty($school_privilege_arr)){
                                    if ($school_info['user_auth'] == 3 && time() >= $school_info['auth_start_time'] && time() < $school_info['auth_end_time']) {
                                        $school_privilege_arr=$school_info;
                                    }
                                }else{
                                    if ($school_info['user_auth'] == 3 && time()>=$school_info['auth_start_time'] && $school_privilege_arr['auth_end_time'] < $school_info['auth_end_time']) {
                                        $school_privilege_arr=$school_info;
                                    }
                                }
                            }
                        }
                        if(empty($school_privilege_arr)){
                            //普通权限
                            $vipdata['auth_id']=2;
                            $vipdata['auth_start_time']=0;
                            $vipdata['auth_end_time']=0;
                            $vipdata['timetype']=0;
                        }else{
                            //团体vip
                            $vipdata['timetype']=$school_privilege_arr['timetype'];
                            $vipdata['auth_id']=3;
                            $vipdata['auth_end_time']=$school_privilege_arr['auth_end_time'];
                        }

                    }else{
                        $vipdata['auth_id']=2;
                        $vipdata['auth_start_time']=0;
                        $vipdata['auth_end_time']=0;
                        $vipdata['timetype']=0;
                    }
                    if(empty($parent_result)){
                        $auth_type_use->addUserVip($vipdata);
                    }else{
                        $auth_model=M('account_user_and_auth');
                        $parent_auth_where['role_id']=4;
                        $parent_auth_where['user_id']=$parent_result['id'];
                        $auth_model->where($parent_auth_where)->save($vipdata);
                    }
                    return array('status'=>'success','message'=>'');
                }
            }

        }else{
            return array('status'=>'failed','message'=>'vip参数错误');
        }
    }else{
        return array('status'=>'failed','message'=>'用户角色参数错误');
    }
}


/*
 * @param   operation_role   当前操作者如当前在教师控制器下,当前的操作者为教师  2教师,3学生,4家长
 * $param   vip_status       传递过来的vip状态(配置文件中的vip状态)
 * $param   user_id          用户Id
 * @param   school_id        非必填参数
 */
function give_new_vip_operation($operation_role,$vip_status,$user_id,$school_id){
    if($operation_role>=2 && $operation_role<=4){
        if($vip_status && $vip_status<=3){
            $vipdata = array(
                'user_id' => $user_id,
                'role_id' => $operation_role,
                'auth_id' => 4,
                'auth_start_time' => time(),
                'auth_end_time' => time()+3600*24*30*3,
                'timetype' => 1
            );
            $auth_type_use = D('Account_auths');

            if($vip_status==1){
                //赠送90天vip
                $vipdata['auth_id']=4;
                $auth_type_use->addUserVip($vipdata);
                return array('status'=>'success','message'=>'');

            }elseif($vip_status==2){
                //普通权限
                return array('status'=>'success','message'=>'');
            }else{
                if($operation_role==2){
                    //教师
                    if(!($school_info=get_school_vip_info($school_id))){
                        return array('status'=>'failed','message'=>'学校id参数错误或数据为空');
                    }else{
                        if ($school_info['user_auth'] == 3 && time() >= $school_info['auth_start_time'] && time() < $school_info['auth_end_time'] ) {
                            //给予学校的权限
                            $vipdata['timetype']=$school_info['timetype'];
                            $vipdata['auth_id']=3;
                            $vipdata['auth_end_time']=$school_info['auth_end_time'];
                            $auth_type_use->addUserVip($vipdata);
                        }
                    }
                    return array('status'=>'success','message'=>'');
                }elseif($operation_role==3){
                    //学生
                    if(!($school_info=get_school_vip_info($school_id))){
                        return array('status'=>'failed','message'=>'学校id参数错误或数据为空');
                    }else{
                        if ($school_info['user_auth'] == 3 && time() >= $school_info['auth_start_time'] && time() < $school_info['auth_end_time'] ) {
                            //把学校的权限赋予学生
                            $vipdata['timetype']=$school_info['timetype'];
                            $vipdata['auth_id']=3;
                            $vipdata['auth_end_time']=$school_info['auth_end_time'];
                            $auth_model=M('account_user_and_auth');
                            $auth_model->add($vipdata);
                        }
                    }
                    return array('status'=>'success','message'=>'');
                }else{
                    return array('status'=>'success','message'=>'');
                }
            }

        }else{
            return array('status'=>'failed','message'=>'vip参数错误');
        }
    }else{
        return array('status'=>'failed','message'=>'用户角色参数错误');
    }
}


//得到学校信息
function get_school_vip_info($school_id){
    if(!$school_id){
        return false;
    }else{
        $shcool_model=M('dict_schoollist');
        $school_info=$shcool_model->where('id='.$school_id)->find();
        if(empty($school_info)){
            return false;
        }else{
            return $school_info;
        }
    }
}

//获得家长在权限表中的信息
function get_parent_privilege($parent_id){
    if(!$parent_id){
        return false;
    }else{
        $auth_model=M('account_user_and_auth');
        $parent_auth_where['role_id']=4;
        $parent_auth_where['user_id']=$parent_id;
        $auth_parent_result=$auth_model->where($parent_auth_where)->find();
        if(empty($auth_parent_result)){
            return false;
        }else{
            return $auth_parent_result;
        }
    }
}

function getCodeRand()
{
    srand((double)microtime()*1000000);//create a random number feed.
    $ychar="0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z";
    $list=explode(",",$ychar);
    $authnum = '';
    for($i=0;$i<6;$i++){
        $randnum=mt_rand(0,61); // 10+26;
        $authnum.=$list[$randnum];
    }
    return $authnum;
}

function getFileType($fileName)
{
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if($fileExt == '')
        return '';
    switch ($fileExt) {
        case 'ppt':
        case 'pptx': $type='ppt';
            break;
        case 'mp4':
        case 'mov':
        case 'mpg':
        case 'mpeg':
        case 'avi':
        case 'flv':
        case 'wmv':
        case '3gp':
            $type  = 'video';
            break;
        case 'mp3':  $type='audio';
            break;
        case 'doc':
        case 'docx': $type='word';
            break;
        case 'pdf':  $type='pdf';
            break;
        case 'jpg':
        case 'jpeg':
        case 'png':  $type='image';
            break;
        case 'swf':  $type='swf';
            break;
        case 'rar':
        case 'zip':  $type='condensed';
                      break;
        default:     ;
    }
    return $type;
}


function processMQMessage($exName, $k_route, $funSelect, $msg='')
{
    $conn_args = array(
        'host' => HOST,
        'port' => PORT,
        'login' => USER,
        'password' => PASS,
        'vhost'=>VHOST
    );
    $e_name = $exName; //交换机名

    //创建连接和channel
    $conn = new AMQPConnection($conn_args);
    if (!$conn->connect()) {
        die("Cannot connect to the broker!\n");
    }
    $channel = new AMQPChannel($conn);

    $ex = new AMQPExchange($channel);
    $ex->setName($e_name);
    $ex->setType(AMQP_EX_TYPE_DIRECT);
    $ex->setFlags(AMQP_DURABLE);

    $q = new AMQPQueue($channel);
    $q->setName($k_route);
    $q->setFlags(AMQP_DURABLE);
    $q->declare();
    $q->bind($e_name,$k_route);//将你的队列绑定到routingKey
    switch($funSelect)
    {
        case "push": //发送消息
            $channel->startTransaction(); //开始事务
            if(is_array($msg))
            {

                for($i=0;$i<count($msg);$i++)
                    $ex->publish($msg[$i],$k_route);
            }
            else
                $ex->publish($msg, $k_route);//将你的消息通过指定routingKey发送
            $channel->commitTransaction(); //提交事务
            break;
        case "purge":$q->purge();
            break;
        default:break;
    }
    $conn->disconnect();
}

function addFileToZip($path, $zip) {
    $handler = opendir($path); //打开当前文件夹由$path指定。
    /*
    循环的读取文件夹下的所有文件和文件夹
    其中$filename = readdir($handler)是每次循环的时候将读取的文件名赋值给$filename，
    为了不陷于死循环，所以还要让$filename !== false。
    一定要用!==，因为如果某个文件名如果叫'0'，或者某些被系统认为是代表false，用!=就会停止循环
    */
    while (($filename = readdir($handler)) !== false) {
        if ($filename != "." && $filename != "..") { //文件夹文件名字为'.'和‘..’，不要对他们进行操作
            if (is_dir($path . "/" . $filename)) { // 如果读取的某个对象是文件夹，则递归
                addFileToZip($path . "/" . $filename, $zip);
            } else { //将文件加入zip对象
                $zip->addFile($path . "/" . $filename);
            }
        }
    }
    @closedir($path);
}

function deldir($dir) {
    //先删除目录下的文件：
    $dh=opendir($dir);
    while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                deldir($fullpath);
            }
        }
    }

    closedir($dh);

}

/* by http://www.manongjc.com/article/27.html*/
function delDirRun($directory){//自定义函数递归的函数整个目录
    if(file_exists($directory)){//判断目录是否存在，如果不存在rmdir()函数会出错
        if($dir_handle=@opendir($directory)){//打开目录返回目录资源，并判断是否成功
            while($filename=readdir($dir_handle)){//遍历目录，读出目录中的文件或文件夹
                if($filename!='.' && $filename!='..'){//一定要排除两个特殊的目录
                    $subFile=$directory."/".$filename;//将目录下的文件与当前目录相连
                    if(is_dir($subFile)){//如果是目录条件则成了
                        delDirRun($subFile);//递归调用自己删除子目录
                    }
                    if(is_file($subFile)){//如果是文件条件则成立
                        unlink($subFile);//直接删除这个文件
                    }
                }
            }
            closedir($dir_handle);//关闭目录资源
            rmdir($directory);//删除空目录
        }
    }
}

function saveTempAvatar($img)
{
    $img = str_replace('data:image/jpeg;base64,', '', $img);
    $img = str_replace(' ', '+', $img);
    $imgData = base64_decode($img);
    $file = LOCAL_AVATAR_TEMP_DIR . uniqid() . '.jpg';
    $success = file_put_contents($file, $imgData);
    $_FILES["file"]["type"] = "image/jpeg";
    $_FILES["file"]["tmp_name"] = $file;
    $_FILES["file"]["name"] = $file;
    $_FILES["file"]["size"] = filesize($file);
}


//根据角色加载html 根据后缀决定加载哪个layout
function layoutHtml( $role,$withouticon ) {
    switch($role)
    {
        case ROLE_TEACHER:layout('teacher_layout_'.$withouticon);
            break;
        case ROLE_STUDENT:layout('student_layout_'.$withouticon);
            break;
        case ROLE_PARENT:layout('parent_layout_'.$withouticon);
            break;
        default:
            layout('teacher_layout_'.$withouticon);
            break;
    }
}

//生成订单号
function StrOrderOne(){
    /* 选择一个随机的方案 */
    $order_number = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 7);
    return $order_number;
}

//rsa解密
function rsaDecrypted( $encrypted='' ) {
    /*$private_key = WEB_URL_DIR_SUP.'rsa_private_key.txt';
    $public_key = WEB_URL_DIR_SUP.'rsa_public_key.txt';*/
    $private_key = './rsaautograph/rsa_private_key.txt';
    $public_key = './rsaautograph/rsa_public_key.txt';

    $private_key = str_split(file_get_contents($private_key),64);
    $public_key = str_split(file_get_contents($public_key),64);

    $private_start = "-----BEGIN RSA PRIVATE KEY-----\n";
    $private_key = implode("\n",$private_key);
    $private_end = "-----END RSA PRIVATE KEY-----";
    $private_key = $private_start.$private_key."\n".$private_end;

    $public_start = "-----BEGIN PUBLIC KEY-----\n";
    $public_key = implode("\n",$public_key);
    $public_end = "-----END PUBLIC KEY-----";
    $public_key = $public_start.$public_key."\n".$public_end;

    $decrypted = "";
    $pi_key =  openssl_pkey_get_private($private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id

    openssl_private_decrypt(base64_decode($encrypted),$decrypted,$pi_key);//私钥解密

    return $decrypted;
}

//根据url &编码获取数据参数
function getParseStr( $url ) {
    parse_str($url, $parameter);
    return $parameter;
}

function getSing() {
    return "Jingbanyun426!";
}

function isIos() {
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
        return 3;
    } else {
        return 2;
    }
}

function isMobile() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])) {
        //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    //脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array (
            'nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipad',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

function trimall($str){
    $qian=array(" ","　","\t","\n","\r");
    return str_replace($qian, '', $str);
}

function getTimeWeek($time, $i = -1) {
    $weekarray = array("一", "二", "三", "四", "五", "六", "日");
    $oneD = 24 * 60 * 60;
    return "星期" . $weekarray[date("w", $time + $oneD * $i)];
}

function asyncSafeCheckSql($sql,$firstId=0)
{
    $data = array('sql'=>$sql,'firstId'=>$firstId);
    $data = http_build_query($data);
    $errno = '';
    $errstr = '';
    $host = $_SERVER['SERVER_NAME'];
    $fp = fsockopen($host, 80, $errno, $errstr, 10);

    if(!$fp){
        return false;
    }

    $writeURL = 'http://'.$host .'/index.php?m=Home&c=SafeScan&a=sqlSafeCheck';
    $out = "POST ${writeURL} HTTP/1.1\r\n";
    $out .= "Host:${host}\r\n";
    $out .= "Content-type:application/x-www-form-urlencoded;charset=UTF-8\r\n";
    $out .= "Content-length:".strlen($data)."\r\n";
    $out .= "Connection:close\r\n\r\n";
    $out .= "${data}";
    fputs($fp, $out);
    fclose($fp);

}

function is_weixin(){
    if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
        return true;
    }
    return false;
}


//创建TOKEN
function creatToken() {
    $code = chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE));
    session('TOKEN', authcode($code));
}

//判断TOKEN
function checkToken($token) {

    if ($token == session('TOKEN')) {
        session('TOKEN', NULL);
        return TRUE;
    } else {
        return FALSE;
    }
}

/* 加密TOKEN */
function authcode($str) {
    $key = "ANDIAMON";
    $str = substr(md5($str), 8, 10);
    return md5($key . $str);
}

function getESAvailable()
{
    $response = json_decode(curl(ES_HOST,[],'get'),true);
    if(!empty($response['tagline']))
        return ES_AVAILABLE;
    return ES_UNAVAILABLE;

}