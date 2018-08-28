<?php
//header('Access-Control-Allow-Origin: http://www.baidu.com'); //设置http://www.baidu.com允许跨域访问
//header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
date_default_timezone_set("Asia/chongqing");
error_reporting(E_ERROR);
header("Content-Type: text/html; charset=utf-8");

$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("config.json")), true);
$action = $_GET['action'];

switch ($action) {
    case 'config':
        $result =  json_encode($CONFIG);
        break;

    /* 上传图片 */
    case 'uploadimage':
    /* 上传涂鸦 */
    case 'uploadscrawl':
    /* 上传视频 */
    case 'uploadvideo':
    /* 上传文件 */
    case 'uploadfile':  
        //$result = include("action_upload.php");
        
        $data = include("action_upload.php");
        $data=json_decode($data,true);
        $result=curl_post($data['url']);
        
        /*$fileName=$config_data['filedName'];
        $file=$_FILES[$fileName]['tmp_name']; */ 
        break;

    /* 列出图片 */
    case 'listimage':
        $result = include("action_list.php");
        break;
    /* 列出文件 */
    case 'listfile':
        $result = include("action_list.php");
        break;

    /* 抓取远程文件 */
    case 'catchimage':
        $result = include("action_crawler.php");
        break;

    default:
        $result = json_encode(array(
            'state'=> '请求地址出错'
        ));
        break;
}


function curl_post($url){   
    $ch = curl_init();
    $root_dir=$_SERVER['DOCUMENT_ROOT'];   
    //$url=  ltrim($url,'/');
    $file=$root_dir.$url;  //$file        
    $data = array('file' => '@'.$file);     
    $host=$_SERVER['HTTP_HOST'];
    $request_url='http://'.$host.'/index.php?m=Home&c=Teach&a=blackboard_file_upload';
    curl_setopt($ch, CURLOPT_URL, $request_url);
    curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);    // 5.6 给改成 true了, 弄回去 
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt ( $ch, CURLOPT_HEADER, 0 );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec ( $ch );       //print_r(json_decode($result,true));
    $result=json_decode($result,true);
    @unlink($file);
    if($result['code']==0){
        $result['state']='SUCCESS';
    }
    $result['url']=$result['res'];
    $result['title']='';
    $result=json_encode($result);
    curl_exec($ch); 
    return $result;
}

/* 输出结果 */
if (isset($_GET["callback"])) { echo 22;
    if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
        echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
    } else {
        echo json_encode(array(
            'state'=> 'callback参数不合法'
        ));
    }
} else {    
    echo $result;
}