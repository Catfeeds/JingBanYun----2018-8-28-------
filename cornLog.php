<?php
date_default_timezone_set("PRC");
require 'vendor/autoload.php';
use Medoo\Medoo;

$database = new Medoo([
    'database_type' => 'mysql',
    'database_name' => 'usertables',
    'server' => '127.0.0.1',
    'username' => 'root',
    'password' => 'Jby&*2016'
]);

$databaseTwo = new Medoo([
    'database_type' => 'mysql',
    'database_name' => 'jingtongcloud',
    'server' => '127.0.0.1',
    'username' => 'root',
    'password' => 'Jby&*2016'
]);

$time 	= strtotime(date("YmdHis",strtotime("-1 day"))); //查询时间
$starttime = date("Y-m-d 00:00:00",$time);
$starttime = strtotime($starttime);

$current_time = date("Y-m-d 00:00:00",time());
$current_time = strtotime($current_time);

$datetime 	= $starttime - 3600*24*7; //流失用户比对时间
$datetime = date("Y-m-d 00:00:00",$datetime);
$datetime = strtotime($datetime);

$data = $database->select("user_access", "*", [
	"user_id[>]"=>0,
	"access_date[>=]" => $starttime ,
	"access_date[<]" => $current_time,
	"ORDER"=> ["access_time"=>"ASC"],
	"GROUP"=>['user_id','role'],
]);

foreach( $data as $k=>&$v) {
	$newF = $database->get("user_access", ['access_date'],[
		'role'=>$v['role'],
		'user_id'=>$v['user_id'],
		"ORDER"=> ["access_time"=>"ASC"],
        "access_date[>]" =>1,
	]);
	
	if (!empty($newF['access_date'])) {
		if ($newF['access_date']>=$starttime && $newF['access_date']<time()) { //新访客
			$databaseTwo->insert("log_count_new", [
				"access_time" => $v['access_time'],
				"ftype" => 1,
				"user_id" => $v['user_id'],
				"role" => $v['role'],
				"access_date" => $v['access_date'],
				"settype" => 1,
				"create_at" =>$starttime,
			]);
            $count = $database->query("SELECT count(access_url) as count FROM `user_access` WHERE `user_id` = ".$v['user_id']." AND `role` = ".$v['role']." AND `access_time` >= ".$starttime)->fetchAll();

            $databaseTwo->update("log_count_new", [
                "type" => 1,
            ],[
                "user_id" => $v['user_id'],
                "role" => $v['role'],
                "access_date" => $v['access_date'],
            ]);

		} else { //老访客
			$databaseTwo->insert("log_count_new", [
				"access_time" => $v['access_time'],
				"ftype" =>2,
				"user_id" => $v['user_id'],
				"role" => $v['role'],
				"access_date" => $v['access_date'],
				"settype" => 1,
				"create_at" => $starttime,
			]);

            $count = $database->query("SELECT count(access_url) as count FROM `user_access` WHERE `user_id` = ".$v['user_id']." AND `role` = ".$v['role']." AND `access_time` >= ".$starttime)->fetchAll();

            if ( $count[0]['count'] >= 10 ) { //活跃
                $databaseTwo->update("log_count_new", [
                    "type" => 1,
                ],[
                    "user_id" => $v['user_id'],
                    "role" => $v['role'],
                    "access_date" => $v['access_date'],
                ]);
            } else {
                $Nocount = $database->query("SELECT count(access_url) as count FROM `user_access` WHERE `user_id` = ".$v['user_id']." AND `role` = ".$v['role']." AND `access_date` < ".$starttime." AND access_date>".$datetime)->fetchAll();

                if ( $Nocount[0]['count'] <= 0 ) { //流失
                    $databaseTwo->update("log_count_new", [
                        "type" => 2,
                    ],[
                        "user_id" => $v['user_id'],
                        "role" => $v['role'],
                        "access_date" => $v['access_date'],
                    ]);
                } else {
                    $databaseTwo->update("log_count_new", [
                        "type" => 1,
                    ],[
                        "user_id" => $v['user_id'],
                        "role" => $v['role'],
                        "access_date" => $v['access_date'],
                    ]);
                }
            }

		}
	}	


}
echo "success";