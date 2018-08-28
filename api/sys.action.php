<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';

/*路由*/

// 获取当前的版本号
$app->get('/latest_version', 'getLatestVersion');

// 获取所有历史版本记录
$app->get('/versions', 'getVersions');

// 下载地址
$app->get('/getapp', 'getApp');

// 发送测试邮件
$app->get('/testemail', 'sendTestEmail');

// 发送短信
$app->post('/sendsms', $authDoctor, 'sendSMS');
$app->get('/sendsms2', 'sendSMS2');


function getLatestVersion() {

	$db = new Database();

	$version = $db->fetch_single_row('sys_version', 'is_latest', 1);

	renderJsonResponse(200, '', $version);

	$db = null;
}

function getVersions() {

	$db = new Database();

	$versions = $db->fetch_all('sys_version');

	renderJsonResponse(200, '', $versions);

	$db = null;
}

function getApp() {
	//header("Content-type:text/html; charset=utf-8");
	try{
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if(stristr($_SERVER['HTTP_USER_AGENT'], 'Android')) {
			header('Location: http://www.pgyer.com/rk7n'); 
			exit();
		} else if (stristr($_SERVER['HTTP_USER_AGENT'], 'iPhone')){
			header('Location: http://www.pgyer.com/IY2l');
			exit();
		} else {
			header('Location: http://www.pgyer.com/rk7n');
			exit();
		}
	} catch (Exception $e) {
		header('Location: http://www.pgyer.com/rk7n'); 
		exit();
	}
}

function sendTestEmail () {
	echo sendEmail("weimer@126.com", "subject1", "body1");
	echo sendEmail("1481684223@qq.com", "subject1", "body1");
	echo "ok";
}

function sendSMS () {
    header("Content-type:text/html; charset=utf-8");

    $msg = urlencode($_POST['message']);
    $telephone = $_POST['telephone'];

    //$arr = explode(",", $telephone);

    $url = "http://web.1xinxi.cn/asmx/smsservice.aspx?name=18601286609&pwd=369F20CC510DE238AC2AE2B31EA5&content=" . $msg . "&mobile=" . $telephone . "&type=pt";
    $html = file_get_contents($url);

    //foreach($arr as $t){
        //if(strlen($t) == 11) {
            //$url = "http://web.1xinxi.cn/asmx/smsservice.aspx?name=18601286609&pwd=369F20CC510DE238AC2AE2B31EA5&content=" . $msg . "&mobile=" . $t . "&type=pt";
            //$html = file_get_contents($url);
        //}
    //}
    renderJsonResponse(200, '发送成功', array());

}

function sendSMS2 () {
    header("Content-type:text/html; charset=utf-8");

    $msg = HTTPGET('message');
    $telephone = HTTPGET('telephone');

    echo $telephone;
    $d = sendSmsMessage($telephone, $msg);
    renderJsonResponse(200, $d, array());

}


$app->run();