<?php
/**
 * Created by PhpStorm.
 * User: hyliu
 * Date: 2017/4/21
 * Time: 10:02
 */
namespace Common\Common;
define('CHECK_ERR',1);
define('CHECK_BLOCK',2);
define('CHECK_REVIEW',3);
define('CHECK_OK',0);

use Green\Request\V20170112 as Green;
require_once  'ThinkPHP/Library/aliyuncs/aliyun-php-sdk-core/Config.php';
class Filter
{
    private $logName;
    public function __construct($logName){

        //请替换成你自己的accessKeyId、accessKeySecret
        date_default_timezone_set("PRC");
        $iClientProfile = \DefaultProfile::getProfile("cn-shanghai", "LTAItpuhTlgicIVf", "UzkBrw5CAWWBYJDV8fLt3fAg3SnWtS"); // TODO
        \DefaultProfile::addEndpoint("cn-shanghai", "cn-shanghai", "Green", "green.cn-shanghai.aliyuncs.com");
        $this->client = new \DefaultAcsClient($iClientProfile);
        $this->logName = $logName;
    }

    public function textFilter($content)
    {
        $request = new Green\TextScanRequest();
        $request->setMethod("POST");
        $request->setAcceptFormat("JSON");
        $checkContent = preg_replace('/(\d+)/','',$content);
        if(empty($checkContent))
            return CHECK_OK;
        $task1 = array('dataId' => uniqid(),
            'content' => $checkContent
        );
        $request->setContent(json_encode(array("tasks" => array($task1),
            "scenes" => array("keyword"))));
        try {
            $response = $this->client->getAcsResponse($request);
            //print_r($response);
            \Think\Log::writeCompactLog("REQUEST:".json_encode($request->getQueryParameters())." </br> RESPONSE:".json_encode($response),'INFO','',$this->logName);
            if (200 == $response->code) {
                $taskResults = $response->data;
                foreach ($taskResults as $taskResult) {
                    if (200 == $taskResult->code) {
                        $sceneResults = $taskResult->results;
                        foreach ($sceneResults as $sceneResult) {
                            $scene = $sceneResult->scene;
                            $suggestion = $sceneResult->suggestion;
                            if($suggestion == 'block')
                            {
                                return CHECK_BLOCK;
                            }
                            return CHECK_OK;
                            //根据scene和suggetion做相关的处理
                            //do something

                        }
                    } else {
                        print_r("task process fail:" + $response->code);
                        return CHECK_ERR;
                    }
                }
            } else {
                print_r("detect not success. code:" + $response->code);
                return CHECK_ERR;
            }
        } catch
        (Exception $e) {
            print_r($e);
            return CHECK_ERR;
        }
        return CHECK_ERR;
    }

    public function imageSyncScanRequest($imageUrl,$category)
    {
        //$category: sface 敏感人物头像 terrorism 暴恐
        $request = new Green\ImageSyncScanRequest();
        $request->setMethod("POST");
        $request->setAcceptFormat("JSON");
        $imageUrl = str_replace("\\\"","",$imageUrl);
        $imageUrl = str_replace("\/","/",$imageUrl);
        $task1 = array('dataId' =>  uniqid(),
            'url' => str_replace("\\\"","",$imageUrl),
            'time' => round(microtime(true)*1000)
        );
        $request->setContent(json_encode(array("tasks" => array($task1),
            "scenes" => array($category))));

        try {
            $response = $this->client->getAcsResponse($request);
            \Think\Log::writeCompactLog("REQUEST:".json_encode($request->getQueryParameters())." </br> RESPONSE:".json_encode($response),'INFO','',$this->logName);
            if(200 == $response->code){
                $taskResults = $response->data;
                foreach ($taskResults as $taskResult) {
                    if(200 == $taskResult->code){
                        $suggestion = $taskResult->results[0]->suggestion;
                        switch($suggestion)
                        {
                            case "pass" :return CHECK_OK;
                            case "block":return CHECK_BLOCK;
                            case "review":return CHECK_REVIEW;
                            default:return CHECK_ERR;
                        }

                    }else if(590 == $taskResult->code)
                    {
                        //format is not support
                        return CHECK_REVIEW;
                    }
                        else{
                        print_r("task process fail:" + $response->code);
                        return CHECK_ERR;
                    }
                }
            }else{
                print_r("detect not success. code:" + $response->code);
                return CHECK_ERR;
            }
        } catch (Exception $e) {
            print_r($e);
            return CHECK_ERR;
        }
        return CHECK_ERR;
    }

    public function imageAsyncScanRequest($imageUrl,$category)
    {
        //$category: sface 敏感人物头像 terrorism 暴恐
        $request = new Green\ImageAsyncScanRequest();
        $request->setMethod("POST");
        $request->setAcceptFormat("JSON");

        $task1 = array('dataId' =>  uniqid(),
            'url' => $imageUrl,
            'time' => round(microtime(true)*1000)
        );
        $request->setContent(json_encode(array("tasks" => array($task1),
            "scenes" => array($category))));

        try {
            $response = $this->client->getAcsResponse($request);
            \Think\Log::writeCompactLog("REQUEST:".json_encode($request->getQueryParameters())." </br> RESPONSE:".json_encode($response),'INFO','',$this->logName);
            if(200 == $response->code){
                $taskResults = $response->data;
                foreach ($taskResults as $taskResult) {
                    if(200 == $taskResult->code){
                        $taskId = $taskResult->taskId;
//                        print_r($taskId);
                        return $taskId;
                        // 将taskId 保存下来，间隔一段时间来轮询结果, 参照ImageAsyncScanResultsRequest
                    }else{
                        print_r("task process fail:" + $response->code);
                        return CHECK_ERR;
                    }
                }
            }else{
                print_r("detect not success. code:" + $response->code);
                return CHECK_ERR;
            }
        } catch (Exception $e) {
            print_r($e);
            return CHECK_ERR;
        }
        return CHECK_ERR;
    }

    public function imageAsyncScanResults($taskId)
    {
        $request = new Green\ImageAsyncScanResultsRequest();
        $request->setMethod("POST");
        $request->setAcceptFormat("JSON");


        $request->setContent(json_encode(array($taskId)));

        try {
            $response = $this->client->getAcsResponse($request);
            print_r($response);
            if(200 == $response->code){
                $taskResults = $response->data;
                foreach ($taskResults as $taskResult) {
                    if(200 == $taskResult->code){
                        $sceneResults = $taskResult->results;
                        foreach ($sceneResults as $sceneResult) {
                            $scene = $sceneResult->scene;
                            $suggestion = $sceneResult->suggestion;
                            //根据scene和suggetion做相关的处理
                            //do something
                            print_r($scene);
                            print_r($suggestion);
                        }
                    }else{
                        print_r("task process fail:" + $response->code);
                    }
                }
            }else{
                print_r("detect not success. code:" + $response->code);
            }
        } catch (Exception $e) {
            print_r($e);
        }
        return CHECK_ERR;
    }

    public function voiceFilter($voiceUrl)
    {
      //调用百度语音识别

    }

    public function pptFilter()
    {

    }

    public function wordFilter()
    {

    }

    public function pdfFilter()
    {

    }
}



