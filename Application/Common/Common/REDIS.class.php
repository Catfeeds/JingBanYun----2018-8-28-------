<?php
namespace Common\Common;
    /*
     *@param 1001 连接失败
     *@param 1002 用户认证失败 
     */
   class REDIS
     {
        
       public  $redis;
       private $redis_host;
       private $redis_port;
       private $redis_auth;
        
       public function __construct(){
           $this->redis_host=C('REDIS_CONFIG.REDIS_HOST');   
           $this->redis_port=C('REDIS_CONFIG.REDIS_PORT');
           $this->redis_auth=C('REDIS_CONFIG.REDIS_AUTH');  
       }
       
       public function init_redis(){    
           $this->redis=$redis=new \Redis();    
           $connect_result=$redis->connect($this->redis_host,$this->redis_port);
           //$connect_result=$redis->connect('localhost','6379'); 
           if(!$connect_result){
               return 1001;
           }
           $auth_result=$redis->auth($this->redis_auth);
           if(!$auth_result){
               return 1002;
           }
           $redis->select('0'); 
           return $this->redis;
       } 
}
?>