<?php
namespace Home\Controller;

use Think\Controller;
use Common\Common\CSV;
define('NOTICE_NUMBER',5);
define('TEACHER_ROLE', 2);
define('USER_EXISTS_STATUS',501);
define('PUSH_OPEN',1);
define('RESET_CLOCK_TIME','23:58');

class ClockNoticeController extends PublicController{
    public $clock_model;
    
    public function __construct() {
        parent::__construct(); 
        $this->clock_model=D('Biz_class_clock'); 
    }
     
    
    /*
     * 对时钟表信息进行推送
     */
    public function pushClock(){
        if(!isset($_GET['pushFlag'])) die;
        if($_GET['pushFlag']!='push') die; 
        if(date('H:i')==RESET_CLOCK_TIME) die; 
        
        $ios_machine_type=1;
        $android_machine_type=2;
        $ios_teacher_clock_info=$this->clock_model->getCurrentTimeClock($ios_machine_type);
        $android_teacher_clock_info=$this->clock_model->getCurrentTimeClock($android_machine_type);       
        
        $ios_arr=$android_arr=array();
        foreach($ios_teacher_clock_info as $val){
            $this->clock_model->updateUserPushCount($val['clock_contact_id'],$val['clock_time'],$val['clock_time_interval'],$val['push_count']);
            $ios_arr['channel_id_info'][]=$val['channel_id_info'];
            $ios_arr['id'][]=$val['clock_contact_id'];
        } 
        foreach($android_teacher_clock_info as $value){
            $this->clock_model->updateUserPushCount($value['clock_contact_id'],$value['clock_time'],$value['clock_time_interval'],$value['push_count']);
            $android_arr['channel_id_info'][]=$value['channel_id_info'];
            $android_arr['id'][]=$value['clock_contact_id'];
        }
        $allMachineTeacherData=array();
        $allMachineTeacherData['ios']=$ios_arr;
        $allMachineTeacherData['android']=$android_arr;      
        $data=A('Home/Message')->addPushClockMessage($allMachineTeacherData); 
    }
    
    
    /*
     * 重置时钟对应用户的状态信息   
     */
    public function resetClockUserInfo(){
        if(!isset($_GET['pushFlag'])) die;
        if($_GET['pushFlag']!='reset') die; 
        $index=0;
        if(!$this->clock_model->resetClockUser()){
            //发送短信或邮件给开发者
        }else{
            $this->pushClock();
        }
    }
    
    
    /*
     * 关闭某个用户的时钟
     */
    public function closeUserClock(){       
        $user_clock_id=getParameter('user_clock_id','int');
        $this->clock_model->closeClockByUser($user_clock_id);
    }
    
    
    /*
     * 时钟列表
     */
    public function clockList(){
        $class_id=getParameter('class_id','int');
        $clock_result=$this->clock_model->getClockByClass($class_id);
        $this->showjson(200,'',$clock_result);
    }
     
    
    
    /*
     * 添加时钟
     */
    public function addClock(){  
        if (!session('?teacher')) redirect(U('Index/index'));
        $teacher_id=session('teacher.id');      
        $class_model=D('Biz_class');        
            
        if(!is_array($_GET['week'])){
            $this->showjson(500);
        }
        $classId=getParameter('classId','int');
        $several_week=$_GET['week'];  
        $hour=intval($_GET['hour']);
        $minute=intval($_GET['minute']);
        $interval=getParameter('interval','int'); 
        $class_result=$class_model->getTeachClassData($teacher_id,$classId);        
        if(empty($class_result)){
            $this->showjson(500);
        }
        if($hour<0 || $hour>60){
            $this->showjson(500,'时钟有误');
        }
        if($minute<0 || $minute>60){
            $this->showjson(500,'分钟有误');
        }
        if($interval!=3 && $interval!=5 && $interval!=10){
            $this->showjson(500,'间隔有误');
        }
        $hour=sprintf("%02d",$hour);
        $minute=sprintf("%02d",$minute);
        $data['clock_time']=$hour.$minute;  
        $data['clock_time_interval']=$interval; 
        $end_time=NOTICE_NUMBER*$data['clock_time_interval']*60;
        $clock_timestamp=strtotime(date('Ymd'.$data['clock_time'])); 
        $data['clock_end_time']=date('Hi',$clock_timestamp+$end_time); 
        
        $data['class_id']=$classId; 
        $data['teacher_id']=$teacher_id;
        $data['create_at']=time();
        $status=$this->clock_model->addClock($data,$several_week);  
        if($status===true){
            $this->showjson(200);
        }elseif($status==501){
            $this->showjson(501,'已经有相同时间的参数');
        }else{
            $this->showjson(500);
        }
    }
        
    
    /*
     * 删除时钟
     */
    public function deleteClock(){
        $clock_id=getParameter('clock_id','int');
        $class_id=getParameter('class_id','int');
        if($this->clock_model->deleteClockControl($clock_id,$class_id)){
            $this->showjson(200);
        }else{
            $this->showjson(400);
        }
    }
     
}
