<?php
date_default_timezone_set("PRC");
ini_set( "display_errors", true );

define( 'DB_CHARACSET', 'utf8' );
define( 'BLWS_READTOKEN','95402908-8fc2-4328-a4cf-4f49601a5812');
require_once ('Database.php');

function handleException( $exception ) {
  echo  $exception->getMessage();
}

function pushToArray ($sel){
	
	$dataSet = array();

    foreach ($sel as $key) {
        array_push($dataSet, $key);
    }

    return $dataSet;
}

$REMOTE_ADDR = (isset($_SERVER['LOCAL_ADDR']) && (!empty($_SERVER['LOCAL_ADDR']))) ? $_SERVER['LOCAL_ADDR'] : $_SERVER['SERVER_ADDR'];


switch($REMOTE_ADDR)
{
    case '123.56.145.63'://开发机
        define('WEB_URL','123.56.145.63');
        break;
    case '121.42.42.203'://测试机
        define('WEB_URL','www.jtypt.com');
        break;
    case '114.215.106.208'://正式机
        define('WEB_URL','www.jingbanyun.com');
        break;
    default :
        define('WEB_URL','121.42.42.203');
        break;
}
define("REDIS_HOST", "121.42.42.203");
define("REDIS_PORT", $REMOTE_ADDR=='121.42.42.203'?'6379':'6378');
define("REDIS_AUTH", "Jingbanyun426!");

/*注册赠送vip配置
 * 1.赠送vip
 * 2.不赠送vip
 * 3.按照原有代码不做任何修改
 */
define("APP_REGISTER_GIVE_VIP_STATUS", "1");


$OSS_PATH = 'http://jbyoss.oss-cn-beijing.aliyuncs.com/';
set_exception_handler( 'handleException' );
