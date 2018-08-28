<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Think\Log\Driver;

class File {

    protected $config  =   array(
        'log_time_format'   =>  ' c ',
        'log_file_size'     =>  2097152,
        'log_path'          =>  '',
    );

    // 实例化并传入参数
    public function __construct($config=array()){
        $this->config   =   array_merge($this->config,$config);
    }
    /**
     * 精简日志接口
     *
     */
    public function writeCompactLog($log,$destination='')
    {
        $now = date($this->config['log_time_format']);
        if(empty($destination)){
            $destination = $this->config['log_path'].date('y_m_d').'.log';
        }
        else
            $destination = $this->config['log_path'].$destination;
        // 自动创建日志目录
        $log_dir = dirname($destination);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }

        error_log("[{$now}] {$log} \r\n", 3,$destination);
    }

    /**
     * 日志写入接口
     * @access public
     * @param string $log 日志信息
     * @param string $destination  写入目标
     * @return void
     */
    public function write($log,$destination='') {
        $now = date($this->config['log_time_format']);
        if(empty($destination)){
            $destination = $this->config['log_path'].date('y_m_d').'.log';
        }
        // 自动创建日志目录
        $log_dir = dirname($destination);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }        
        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if(is_file($destination) && floor($this->config['log_file_size']) <= filesize($destination) ){
            rename($destination,dirname($destination).'/'.time().'-'.basename($destination));
        }
        $clientIP = get_client_ip();
        $additionalInfo ="";
        $additionalInfo .= "\r\nUSERAGENT:".$_SERVER['HTTP_USER_AGENT']."\r\n";
        $additionalInfo .= "\r\nSESSION INFO:" .json_encode($_SESSION) ."\r\n";
        $additionalInfo .= "\r\nREMOTE IP:" .$clientIP."\r\n";
        $additionalInfo .= "\r\nREQUEST INFO:" .json_encode($_REQUEST)."\r\n";
        error_log("[{$now}] ".$additionalInfo.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['REQUEST_URI']."\r\n{$log}\r\n", 3,$destination);
        if(strpos($_SERVER['REQUEST_URI'],'a=addAccessRecord') !== false || strpos($_SERVER['REQUEST_URI'],'c=SafeScan') !== false)
         return;
        foreach(C('IGNORELOG_AGENT') as $agent)
        {
            if(strpos($_SERVER['HTTP_USER_AGENT'],$agent) !== false)
                return;
        }
        if(in_array($clientIP,C('IGNORELOG_IP')))
        {
            return;
        }
        $host = $_SERVER['SERVER_NAME'];
        $role = 0;
        $user_id = 0;
        if(isset($_SESSION['teacher']))
         {
         $role = 0;
         $user_id = $_SESSION['teacher']['id'];
         }
        if(isset($_SESSION['student']))
         {
         $role = 1;
         $user_id = $_SESSION['student']['id'];
         }
        if(isset($_SESSION['parent']))
         {
         $role = 2;
         $user_id = $_SESSION['parent']['id'];
         }

        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')!== false || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad') !== false){
            $user_id = empty($_REQUEST['userId'])?0:$_REQUEST['userId'];
            if(!empty($user_id))
                $role = empty($_REQUEST['role'])?0:$_REQUEST['role']-2;
        }
        else if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android') !== false){
            preg_match("/&userId=(\d+)/is",$_SERVER['HTTP_USER_AGENT'],$regs);
            $user_id = $regs[1];
            if(empty($user_id))
                $user_id=empty($_REQUEST['userId'])?0:$_REQUEST['userId'];
            preg_match("/&role=(\d+)/is",$_SERVER['HTTP_USER_AGENT'],$regs);
            $role = $regs[1]-2;
            if(empty($regs[1]))
                $role=empty($_REQUEST['role'])?0:$_REQUEST['role']-2;
        }
        $time = time();
        $url = preg_replace("/&_=\d+/", "",  $_SERVER['REQUEST_URI']);
        $refer = preg_replace("/&_=\d+/", "", $_SERVER['HTTP_REFERER']);

        $data = array(
         'role' => $role,
         'user_id' => $user_id,
         'user_agent' =>$_SERVER['HTTP_USER_AGENT'],
         'ip_address' => $clientIP,
         'http_refer' => $refer,
         'access_time'=> $time,
         'access_url' => $url,
         'module_name' => MODULE_NAME,
         'controller_name' => CONTROLLER_NAME,
         'action_name' => ACTION_NAME,
         'parameters_post' => json_encode($_POST)
        );

        $data = http_build_query($data);
        // create connect
        $errno = '';
        $errstr = '';
        $fp = fsockopen($host, 80, $errno, $errstr, 10);

        if(!$fp){
            error_log('fp is no open', 3,$destination);
            return false;
        }
        $writeURL = 'http://'.$host .'/index.php?m=Home&c=Log&a=addAccessRecord';
        $out = "POST ${writeURL} HTTP/1.1\r\n";
        $out .= "Host:${host}\r\n";
        $out .= "Content-type:application/x-www-form-urlencoded\r\n";
        $out .= "Content-length:".strlen($data)."\r\n";
        $out .= "Connection:close\r\n\r\n";
        $out .= "${data}";
        fputs($fp, $out);
        fclose($fp);
    }
}
