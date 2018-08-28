<?php
/**
 * *************************************************************************
 *
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 *
 * ************************************************************************
 */
/**
 *
 * @file hello.php
 * @encoding UTF-8
 * 
 * 
 *         @date 2015年3月10日
 *        
 */

require_once '../sdk.php';

// 创建SDK对象.
$sdk = new PushSDK();


// 创建消息内容
$msg = array(
    'description' => 'notice msg',
);    

// 消息控制选项。
$opts = array(
    'msg_type' => 1,
);

// 发送
$rs = $sdk -> pushMsgToAll($msg,$opts);

if($rs !== false){
    print_r($rs);    // 将打印出 msg_id 及 timestamp
}
 