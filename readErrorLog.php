<?php 
 $date = isset($_GET['date']) ? $_GET['date']:0;
 $token = isset($_GET['token']) ? $_GET['token']:0;
 if($token !== 'Jingbanyun426!')
	 die;
 if(empty($date))
	 $date = date('Y-m-d',time());
 if(strtotime($date) <= 0)
	 die;
 $date = strtotime($date);
 $lastDate = date('Y-m-d',$date-86400);
 $nextDate = date('Y-m-d',$date+86400);
 
 echo "<div style='position:fixed;top:0px'><button onclick=\"location.href='/readErrorLog.php?token=Jingbanyun426!&date=$lastDate'\">上一天</button>";
 echo "&nbsp;&nbsp;&nbsp;&nbsp;";
 echo "<button onclick=\"location.href='/readErrorLog.php?token=Jingbanyun426!&date=$nextDate'\">下一天</button></div>";
 
 $logName = 'Application/Runtime/Logs/Home/'.substr(date('Y_m_d',$date),2).'.err';
 
 if(!is_file($logName))
	 exit;
 $handle = fopen($logName, 'r');
 $data = fread($handle,filesize($logName));
 
 
 echo "<div style='overflow-y:scroll;max-height:97%;margin-top:2%'><pre>".$data.'</div>';
?>