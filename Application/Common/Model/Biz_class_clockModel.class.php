<?php
namespace Common\Model;
use Think\Model; 

class Biz_class_clockModel extends Model{
    
    public    $model='';
    protected $tableName = 'biz_class_clock';  
	
    public function __construct(){  
        parent::__construct();  
        $this->model=M($this->tableName);
        
    }
     
    /*
     * 获得当前时间内设定的时钟数据  
     * @param   machine_type    机器类型
     */ 
    public function getCurrentTimeClock($machine_type=0){
        $week_several=date('w');   
        $current_time=date('Hi'); 
        $where['several_week']=intval($week_several);   
        $where['user_type']=TEACHER_ROLE;
         
        if($machine_type){
            $where['machine_type']=$machine_type;  
        }
        $where['biz_clock_contact_user.push_status']=PUSH_OPEN; 
        $where['_string']="channel_id_info!='' and push_count!=5 and (case when biz_clock_contact_user.push_count=0 then clock_time='{$current_time}'"
                . "else biz_clock_contact_user.next_notice_time='{$current_time}' end)";
       
        $result=$this->model->where($where)->join('biz_clock_contact_user on biz_clock_contact_user.clock_id=biz_class_clock.id')
                    ->join('auth_teacher on auth_teacher.id=biz_clock_contact_user.user_id')
                    ->field('biz_clock_contact_user.id clock_contact_id,user_id,channel_id_info,machine_type,clock_time,clock_time_interval,push_count')->select();    
         
        return $result;
    }
    
    
    /*
     * 关闭某个用户的时钟
     */
    public function closeClockByUser($user_clock_id){
        $model=M('biz_clock_contact_user');
        $where['id']=$user_clock_id;
        $data['push_status']=0;
        if($model->where($where)->save($data)){
            return true;
        }else{
            return false;
        }
    }
    
    
    /*
     * 更改某个用户的推送次数信息和下次时间
     * @param   id  时钟关联用户表ID
     */
    public function updateUserPushCount($id,$clock_time,$time_interval,$push_count){
        $clock_timestamp=strtotime(date('Ymd'.$clock_time));
        $time=($push_count+1)*$time_interval*60;
        $next_notice_time=date('Hi',$clock_timestamp+$time);
        $data['next_notice_time']=$next_notice_time;
        $data['push_count']=$push_count+1;
        
        $model=M('biz_clock_contact_user');
        $where['id']=$id;
        if($model->where($where)->save($data)){   
            return true;
        }else{
            return false;
        }  
    }
    
    
    /*
     * 通过某个班级获得其下的所有时钟
     * @param   class_id    班级ID
     */
    public function getClockByClass($class_id){
        $where['class_id']=$class_id;
        $result=$this->model->where($where)->field('id,several_week,clock_time,clock_time_interval')->order('create_at desc')->select();
        return $result;
    }
    
    
    /*
     * 添加时钟
     * @param   data    键值对数组
     * @param   week_arr 星期数组
     */
    public function addClock($data,$week_arr){
        $this->model->startTrans();
        foreach($week_arr as $val){
            if($val>=0 && $val<=6){
                $data['several_week']=$val; 
                if($this->getClockInfoByCondition($data)){  
                    $this->model->rollback();
                    return USER_EXISTS_STATUS;
                } 
                if(!$insert_id=$this->model->add($data)){   
                    $this->model->rollback();
                    return false;
                }  
                if(!$this->addClockContactUser($insert_id,$data['teacher_id'])){
                    $this->model->rollback();
                    return false;
                }
                
            }else{
                $this->model->rollback();
                return false;
            }  
        }
        $this->model->commit();
        return true;
    }
    
    
    /*
     * 添加时钟关联用户信息
     * @param   clock_id    时钟ID 
     * @param   user        用户ID
     */
    public function addClockContactUser($clock_id,$user_id){
        $model=M('biz_clock_contact_user');
        $clock_user_data['clock_id']=$clock_id;
        $clock_user_data['user_type']=TEACHER_ROLE;
        $clock_user_data['user_id']=$user_id; 
        $clock_user_data['create_at']=time();
        $clock_user_data['update_at']=time();       
        if($model->add($clock_user_data)){
            return true;
        }else{
            return false;
        }
    }
    
    
    /*
     * 根据某条件查询钟表中信息
     * @param   condition_arr   条件数组
     */
    public function getClockInfoByCondition($condition_arr){
        $condition_array['class_id']=$condition_arr['class_id'];
        $condition_array['several_week']=$condition_arr['several_week'];
        $condition_array['clock_time']=$condition_arr['clock_time'];
        $result=$this->model->where($condition_array)->field('id')->find();     
        if(!empty($result)){
            return true;
        }else{
            return false;
        }
    }
           
    
    /*
     * 管理删除时钟
     */
    public function deleteClockControl($clock_id,$class_id){
        $this->model->startTrans();
        if(!$this->deleteClock($clock_id, $class_id)){
            $this->model->rollback();
            return false;
        }else{
            if($this->deleteClockContactUser($clock_id)){
                $this->model->commit();
                return true; 
            }else{
                $this->model->rollback();
                return false;
            }
        }
    }
 
    /*
     * 删除时钟
     * @param   clock_id    闹钟ID
     */
    public function deleteClock($clock_id,$class_id){
        $where['id']=$clock_id;
        $where['class_id']=$class_id;
        if($this->model->where($where)->delete()){ 
            return true;
        }else{
            return false;
        }
    }
    
    
    /*
     * 删除对应某个时钟的用户
     */
    public function deleteClockContactUser($clock_id){
        $where['clock_id']=$clock_id;
        $user_clock_model=M('biz_clock_contact_user');
        if($user_clock_model->where($where)->delete()){ 
            return true;
        }else{ 
            return false;
        }
    }
    
    
    /*
     * 重置时钟对应的用户信息
     */
    public function resetClockUser(){
        $model=M('biz_clock_contact_user');
        $data['push_count']=0;
        $data['push_status']=1;
        $data['next_notice_time']=0;
        $where['push_status']=0; 
        
        if($model->where($where)->save($data)){
            return true;
        }else{ 
            return  false;
        }
    } 
     
    
}