<?php
namespace Common\Model;

use Think\Model;

class MessageModel extends Model
{

    public $model = '';
    protected $tableName = 'role_message';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }
    /**
     * 增加MESSAGE记录
     * @param $telephone
     * @return mixed
     *
     */


    /**
     * 更新MESSAGE记录
     * @param $telephone
     * @return mixed
     *
     */
    
    /*
     * 得到某个角色下所有用户
     */
    public function getRoleUserAll($role){ 
        if($role==2){
            $model=M('auth_teacher');
            $result=$model->field('id,name,channel_id_info')->select(); 
            
        }elseif($role==3){
            $model=M('auth_student');
            $result=$model->field('id,student_name name,channel_id_info')->select(); 
            
        }else{
            $model=M('auth_parent');
            $result=$model->field('id,parent_name name,channel_id_info')->select(); 
        }
        return $result;
    }
    
    
    /*
     * 对所有角色的所有用户插入消息接受者表
     * 返回添加人数
     */
   public function addMessageReceive($message_id){
       $role_arr=array(2,3,4);
       $receive_model=M('receive_message_user');
       $receive_model->startTrans(); 
       $number=0;
       for($i=0;$i<count($role_arr);$i++){
           $role=$role_arr[$i];
            $allUser=$this->getRoleUserAll($role); 

            foreach($allUser as $val){
                $add_data['message_id']=$message_id;
                $add_data['role_id']=$role;
                $add_data['user_id']=$val['id'];
                $add_data['addtime']=time();
                $number++;

                if(!$receive_model->add($add_data)){
                    $receive_model->rollback();
                    return false;
                }
            }
       }
       $receive_model->commit();
       return $number;
   }
    
    private function __addChannelInfo(&$array,$channelIdInfo,$unreadNum,$machineType)
    {
        if(!is_array($channelIdInfo)) {
            $array[] = array('channel_id' => $channelIdInfo,
                'unreadnum' => $unreadNum,
                'machine_type' => $machineType);
        }
        else{
            foreach($channelIdInfo as $channelIndex => $channelInfo)
            {
                $array[] = array('channel_id' => $channelInfo,
                    'unreadnum' => $unreadNum,
                    'machine_type' => $machineType);
            }
        }
    }
    /**
     * 根据消息id获取所有用户的渠道id以及未读数量和机型
     * @param $id int
     * @return Array
     *
     */
    public function getUserChannelWithUnReadCountMachineType($id,$usePrevChannelId=0)
    {
        $senddata['message_id'] = $id;
        $Model = M('receive_message_user');
        $userinfo = M()->query("SELECT a.user_id,a.role_id,SUM(is_read=1) unreadnum FROM (SELECT user_id,role_id FROM receive_message_user WHERE message_id = $id) a JOIN receive_message_user b ON b.role_id=a.role_id AND b.user_id = a.user_id GROUP BY a.user_id,a.role_id");

        $channel_id = array();

        $Device = getallheaders()['Device'];
        $channelIdFieldName = 'channel_id_info';
        if($usePrevChannelId)
            $channelIdFieldName = 'prev_'.$channelIdFieldName;
        if($Device == 'iPad')
            $channelIdFieldName = 'pad_'.$channelIdFieldName;
        if(empty($Device))
            $channelIdFieldName = array($channelIdFieldName,'pad_'.$channelIdFieldName);
        foreach ($userinfo as $ukey => $uvalue) {
            $map['id'] = $uvalue['user_id'];
            if ($uvalue['role_id'] == 2) { //老师
                $userinfo = M('auth_teacher')->where($map)->find();
                    if(is_array($channelIdFieldName)){
                        $channelArray = [];
                        array_map(function($value) use ($userinfo,&$channelArray){if(!empty($userinfo[$value])) $channelArray[] = $userinfo[$value];return $userinfo[$value];},$channelIdFieldName);
                        foreach($channelArray as $key=>$val){
                            if(empty($val))
                                unset($channelArray[$key]);
                        }
                        $userinfo['channel_id_info'] = $channelArray;
                    }
                    else {
                        $userinfo['channel_id_info'] = $userinfo[$channelIdFieldName];
                    }
                if (!empty($userinfo['channel_id_info'])) {
                    $this->__addChannelInfo($array,$userinfo['channel_id_info'],$uvalue['unreadnum'],$userinfo['machine_type']);
                }

            } elseif ($uvalue['role_id'] == 3) { //学生
                $userinfo = M('auth_student')->where($map)->find();
                if(is_array($channelIdFieldName)){
                    $channelArray = [];
                    array_map(function($value) use ($userinfo,&$channelArray){if(!empty($userinfo[$value])) $channelArray[] = $userinfo[$value];return $userinfo[$value];},$channelIdFieldName);
                    foreach($channelArray as $key=>$val){
                        if(empty($val))
                            unset($channelArray[$key]);
                    }
                    $userinfo['channel_id_info'] = $channelArray;
                }
                else {
                    $userinfo['channel_id_info'] = $userinfo[$channelIdFieldName];
                }
                if (!empty($userinfo['channel_id_info']) ) {
                    $this->__addChannelInfo($array,$userinfo['channel_id_info'],$uvalue['unreadnum'],$userinfo['machine_type']);
                }

            } elseif ($uvalue['role_id'] == 4) { //家长
                $userinfo = M('auth_parent')->where($map)->find();
                if(is_array($channelIdFieldName)){
                    $channelArray = [];
                    array_map(function($value) use ($userinfo,&$channelArray){if(!empty($userinfo[$value])) $channelArray[] = $userinfo[$value];return $userinfo[$value];},$channelIdFieldName);
                    foreach($channelArray as $key=>$val){
                        if(empty($val))
                            unset($channelArray[$key]);
                    }
                    $userinfo['channel_id_info'] = $channelArray;
                }
                else {
                    $userinfo['channel_id_info'] = $userinfo[$channelIdFieldName];
                }
                if (!empty($userinfo['channel_id_info'])) {
                    $this->__addChannelInfo($array,$userinfo['channel_id_info'],$uvalue['unreadnum'],$userinfo['machine_type']);
                }

            }
        }

        return $array;
    }

    /**
     * 根据消息id撤回消息
     * @param $id int
     * @return Array
     *
     */

    public function withdrawMessageInfo($id)
    {
        $map['id'] = $id;
        $data['status'] = 4;

        $idinfo = $this->model->where($map)->save($data);

        if ($idinfo) {
            return true;
        } else {
            $errordata['status'] = 5;
            $idinfo = $this->model->where($map)->save($errordata);
            if ($idinfo) {
                return false;
            } else {
                return false;
            }
        }
    }

    public function getMonthRange($date, $returnFirstDay = true)
    {
        $timestamp = strtotime($date);
        if ($returnFirstDay) {
            $monthFirstDay = date('Y-m-d 00:00:00', $timestamp);
            return strtotime($monthFirstDay);
        } else {

            $monthLastDay = date('Y-m-d 23:59:59', $timestamp);
            return strtotime($monthLastDay);
        }
    }

    public function getMessageList($queryArray, &$count, &$result,$phone_tag=0, $pageIndex = 1, $pageSize = 20)
    {
        foreach ($queryArray as $key => $val) {
            if (empty($queryArray[$key]))
                unset($queryArray[$key]);
        }

        $string = "";

        if (!empty($queryArray['role_id'])) {
            if($phone_tag==0) //后台查询
            $queryArray['receive_message_user.role_id'] = array('in',implode(',',$queryArray['role_id']));
            else
            $queryArray['role_message.role_id'] = implode(',',$queryArray['role_id']);
            unset($queryArray['role_id']);
        }
        if (!empty($queryArray['user_id'])) {
            $queryArray['receive_message_user.user_id'] = $queryArray['user_id'];
            unset($queryArray['user_id']);
        }

        if (!empty($queryArray['message_content'])) {
            $queryArray['message_content'] = array('like', '%' . $queryArray['message_content'] . '%');
        }

        if (!empty($queryArray['send_time'])) {
            $startTime = $this->getMonthRange($queryArray['send_time'], true);
            $endTime = $this->getMonthRange($queryArray['send_time'], false);
            $queryArray['send_time'] = array(
                array('egt', $startTime),
                array('elt', $endTime)
            );
        }
         if (!empty($queryArray['title'])) {
             $queryArray['title'] = array('like', '%' . $queryArray['title'] . '%');
         }
        //$queryArray['status'] = '2';
        //$queryArray['_string']='receive_type =2 or receive_type=3';

        if($phone_tag==0){
            $count = $this->model->where($queryArray)->join("receive_message_user on receive_message_user.message_id=role_message.id")
                ->field('count(distinct role_message.id) as count')
                ->order('role_message.send_time desc')
                ->select();

            $result =$this->model->where($queryArray)->join("receive_message_user on receive_message_user.message_id=role_message.id")
                ->order('order_weight desc,role_message.send_time desc')->page($pageIndex . ',' . $pageSize)->field('role_message.message_content,role_message.id,title,receive_num,'
                    . 'receive_type,send_time,status,user_id,role_message.role_id as role_data,is_read,(CASE WHEN send_time is NULL THEN 1 ELSE 0 END) order_weight')->group('role_message.id')
                ->select();


        }else{
            $queryArray['status'] = array('in',array(2,3,5));
            $queryArray['receive_type'] = array('in',array(2,3));
            $queryArray['receive_message_user.role_id'] = $queryArray['role_message.role_id'];
            unset($queryArray['role_message.role_id']);
            $date_result =$this->model->where($queryArray)->join("receive_message_user on receive_message_user.message_id=role_message.id")
                ->group('date')->order('order_weight desc,date desc')->page($pageIndex . ',' . $pageSize)->field("role_message.id,group_concat(role_message.id) message_ids,"
                    . "(CASE WHEN send_time is NULL THEN 1 ELSE 0 END) order_weight,FROM_UNIXTIME(send_time,'%Y-%m-%d') date,truncated_title as title,receive_num,receive_type,send_time,status,user_id,receive_message_user.role_id,is_read")->select();
            $data=array();
            $index=0;
            $weekarray=array("日","一","二","三","四","五","六");
            $now_date=date('Ymd');
            foreach($date_result as $key=>$value){
                $child_arr=array();
                $index++;
                $other_date=date('Ymd',strtotime($value['date']));
                if($index<=2){
                    if($now_date==$other_date){
                        $week_str='今天';
                    }elseif(($now_date-$other_date)==1){
                        $week_str='昨天';
                    }else{
                        $week_str="星期".$weekarray[date("w",strtotime($value['date']))];
                    }
                }else{
                    $week_str="星期".$weekarray[date("w",strtotime($value['date']))];
                }
                $message_idsArray = explode(',',$value['message_ids']);

                $condition['role_message.id'] = array('in',$message_idsArray);
                $condition['receive_message_user.role_id'] = $queryArray['receive_message_user.role_id'];
                $condition['receive_message_user.user_id'] = $queryArray['receive_message_user.user_id'];
                $condition['receive_type'] = $queryArray['receive_type'];
                $condition['status'] = array('in',array(2,3,5));
                if(!empty($queryArray['message_content']))
                $condition['message_content'] = $queryArray['message_content'];
                $date_result =$this->model->where($condition)->join("receive_message_user on receive_message_user.message_id=role_message.id")
                    ->order('order_weight desc,send_time desc')->field("(CASE WHEN send_time is NULL THEN 1 ELSE 0 END) order_weight,role_message.id,receive_message_user.id receive_id,role_message.truncated_title as title,send_time,user_id,receive_message_user.role_id,is_read")->select();
                $child_arr['week']=$week_str;
                $child_arr['date']=date('Y年m月d日',strtotime($value['date']));
                $child_arr['message_count']=count($date_result);
                $child_arr['data']=$date_result;
                $data[]=$child_arr;
            }
            $result=$data;
        }
    }
    
    
    public function updateMessageReceivenum($message_id,$receive_number){
        $receive_message_user=M('receive_message_user');
        $receive_message_user->where('id=' . $message_id)->save(array('receive_num'=>$receive_number));
    }
    
    public function updateMessageRedyStatus($id){
        $receive_message_user=M('receive_message_user');
        $receive_message_user->where('id=' . $id)->save(array('is_read'=>2));        
    }

    public function deleteMessage($id)
    {
        return $this->model->where('id=' . $id)->delete();
    }

    public function getMessageCategory($id)
    {
        $result = $this->model->where('id=' . $id)->find();
        return $result['receive_type'];
    }

    public function setMessageSendTime($id,$time)
    {
        $this->model->where('id=' . $id)->save(array('send_time'=>$time));
    }

    public function addMessageData($data,$userIdString)
    {
        $this->model->startTrans();
        $roleModel = M('receive_message_user');
        if(isset($data['id'])) {
            $this->model->save($data);
            $message_id = $data['id'];
        }
        else
            $message_id = $this->model->add($data);
        if(!$message_id)
        {
            $this->model->rollback();
            return 0;
        }
        $userIdArray = explode(',',$userIdString);
        $count = count($userIdArray);
        for($i=0;$i<$count;$i++) {
            $roleData[$i]['role_id'] = $data['role_id'];
            $roleData[$i]['message_id'] = $message_id;
            $roleData[$i]['user_id'] = $userIdArray[$i];
            $roleData[$i]['addtime'] = time();
        }
        if($roleModel->addAll($roleData))
        {
            $this->model->commit();
        }
        else
        {
            $this->model->rollback();
            return 0;
        }
        return $message_id;
    }

    public function getMessagesByUser($userId,$role,$pageIndex=1,$pageSize=20)
    {   
        $roleModel = M('receive_message_user');
        $where['receive_message_user.user_id'] = $userId;
        $where['receive_message_user.role_id'] = $role;
        $roleModel->where($where)
            ->join($this->tableName.' ON '.$this->tableName.'.id = receive_message_user.message_id')
            ->field($this->tableName.'.id,title,addtime')
            ->page($pageIndex.','.$pageSize)
            ->select();
    }

    public function getUnreadMessagesCount($userId,$role)
    {
        $roleModel = M('receive_message_user');
        $where['receive_message_user.user_id'] = $userId;
        $where['receive_message_user.role_id'] = $role;
        $where['is_read'] = 1;
        $where['status'] = array('in',array(2,3,5));
        $where['_string']='receive_type =2 or receive_type=3';
        $result=$roleModel->join('role_message on role_message.id=receive_message_user.message_id')->where($where)->field('count(distinct(concat(receive_message_user.user_id,receive_message_user.role_id,receive_message_user.message_id))) as num')->find();
        return $result;
    }
    public function setMessageState($messageId,$state)
    {
        $saveData['status'] = $state;
        $this->model->where('id='.$messageId)->save($saveData);
    }
    public function setMessageReadState($messageId,$userId,$role,$state)
    {
        if (!empty($messageId)) {
            if(!is_array($messageId))
              $where['message_id'] = array('in',array($messageId));
            else
              $where['message_id'] = array('in',($messageId));
        }
        $where['user_id'] = $userId;
        $where['role_id'] = $role;  
        return M('receive_message_user')->where($where)->save(array('is_read' => $state)); 
    }
    
    public function getMessageDetail($messageId){
        $where['status'] = array('in',array(2,3,4,5));
        $where['id'] =  $messageId;
        $result = $this->model->where($where)->field('id,status,message_content')->find();
        return $result;
    }

    public function getMessageTitle($messageId)
    {
        $result = $this->model->where('id='.$messageId)->field('title')->find();
        return $result['title'];
    }
	
	//for app interface
    public function getMessageInfo($messageId,$userId,$role)
    {
        $where[$this->tableName.'.id'] = $messageId;
        $where['receive_message_user.user_id'] = $userId;
        $where['receive_message_user.role_id'] = $role;
        
        return $this->model->join('receive_message_user ON receive_message_user.message_id='.$this->tableName.'.id')
                    ->where($where)
                    ->field('title,message_content,status,send_time')
                    ->find();
    }
	
	public function deletePersonalMessage($messageId,$userId,$role)
    {
        if (!empty($messageId)) {
            $where['message_id'] = array('in',$messageId);
        }
        $where['user_id'] = $userId;
        $where['role_id'] = $role;
        return M('receive_message_user')->where($where)->delete();
    }

    //获取班级和学科
    public function getCoursesAndGrades() {
        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        $Model = M('dict_grade');
        $grades = $Model->select();
        $new['courses'] = $courses;
        $new['grades'] = $grades;
        return $new;
    }

    //获取教师列表
    public function getTeacherAjaxData( $where ,$join) {

        $Model = M('auth_teacher');


        $count = $Model
            ->join($join)
            ->field('1')
            ->where($where)
            ->group('auth_teacher.id')
            ->field('1')
            ->select(); 

        $Page = new \Think\Page(count($count), C('PAGE_SIZE_FRONT'));
        //$Page->parameter['keyword'] = urlencode($keyword);

        $show = $Page->show('queryList');


        $result = $Model
            ->join($join)
            ->field('account_user_and_auth.auth_id as is_vip_auth_id,account_user_and_auth.auth_start_time,account_user_and_auth.auth_end_time,dict_schoollist.school_name,auth_teacher.name,auth_teacher.sex,auth_teacher.telephone,auth_teacher.create_at,email,points,auth_teacher.lock,auth_teacher.id'
                )
            ->group('auth_teacher.id')
            ->where($where)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();     //echo $Model->getLastsql();die;
        //var_dump(M('')->getLastSql());exit;
        $auth_type_use = D('Account_auths');
        foreach ($result as $key => $value) {
            $isVipInfo = $auth_type_use->isVipInfo($value['id'],2);
            $result[$key]['vipinfo'] = $isVipInfo;
            $result[$key]['create_at'] = date('Y-m-d H:i:s',$value['create_at']);

            foreach ($isVipInfo['vip_data'] as $vk=>$vv) {
                $result[$key]['vipendtime'] = date('Y-m-d',$vv['auth_end_time']);
            }

        }

        $data['result'] = $result;
        $data['page'] = $show;
        $data['count'] = count($count);

        return $data;
    }

    //获取学生列表
    public function getStuAjaxData( $where ) {
        $Model = M('auth_student');
        $count = $Model
            ->join('left join dict_schoollist on auth_student.school_id=dict_schoollist.id')
            ->join('left join biz_class_student on biz_class_student.student_id=auth_student.id')
            ->join('left join biz_class on biz_class.id=biz_class_student.class_id')
            ->join('left join auth_parent on auth_student.parent_id = auth_parent.id')
            ->join('left join account_user_and_auth ON account_user_and_auth.user_id = auth_student.id and account_user_and_auth.role_id = 3')
            ->field('account_user_and_auth.user_id,account_user_and_auth.auth_id as is_vip_auth_id,account_user_and_auth.auth_start_time,account_user_and_auth.auth_end_time,dict_schoollist.school_name,auth_student.id,auth_student.student_id,auth_student.student_name,'
                . 'auth_student.birth_date,auth_student.create_at,auth_student.email,auth_parent.parent_name,'
                . 'auth_student.parent_tel,auth_student.lock,auth_student.flag')
            ->order('auth_student.create_at desc')
            ->where($where)
            ->group('auth_student.id')
            ->select();         
        $count = count($count);

        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));

        $show = $Page->show('stuQueryList');

        $result = $Model
            ->join('left join dict_schoollist on auth_student.school_id=dict_schoollist.id')
            ->join('left join biz_class_student on biz_class_student.student_id=auth_student.id')
            ->join('left join biz_class on biz_class.id=biz_class_student.class_id')
            ->join('left join auth_parent on auth_student.parent_id = auth_parent.id')
            ->join('left join account_user_and_auth ON account_user_and_auth.user_id = auth_student.id and account_user_and_auth.role_id = 3')
            ->field('account_user_and_auth.user_id,account_user_and_auth.auth_id as is_vip_auth_id,account_user_and_auth.auth_start_time,account_user_and_auth.auth_end_time,dict_schoollist.school_name,auth_student.id,auth_student.student_id,auth_student.student_name,'
                . 'auth_student.birth_date,auth_student.create_at,auth_student.email,auth_parent.parent_name,'
                . 'auth_student.parent_tel,auth_student.lock,auth_student.flag,auth_student.sex,dict_schoollist.status')
            ->order('auth_student.create_at desc')
            ->where($where)
            ->group('auth_student.id')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $auth_type_use = D('Account_auths');
        foreach ($result as $key => $value) {
            $isVipInfo = $auth_type_use->isVipInfo($value['id'],3);
            $result[$key]['vipinfo'] = $isVipInfo;
            $result[$key]['birth_date'] = date("Y-m-d",$value['birth_date']);
            $result[$key]['create_at'] = date("Y-m-d,H:i:s",$value['create_at']);

            foreach ($isVipInfo['vip_data'] as $vk=>$vv) {
                $result[$key]['vipendtime'] = date('Y-m-d',$vv['auth_end_time']);
            }
        }

        $data['result'] = $result;
        $data['page'] = $show;
        $data['count'] = $count;
        return $data;
    }

    //获取家长列表
    public function getParentAjaxData( $where ) {
        $Model = M('auth_parent');

        $count = $Model->where($where)
            ->join("auth_student on auth_parent.id=auth_student.parent_id",'left')
            ->join('left join account_user_and_auth ON account_user_and_auth.user_id = auth_parent.id and account_user_and_auth.role_id = 4')
            ->field("auth_parent.id")
            ->group("auth_parent.id")
            ->select();
        $count=count($count);

        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));

        $show = $Page->show('parentQueryList');

        //这里必须用join,牵扯到一个学校的ID
        $result = $Model
            ->where($where)
            ->join("auth_student on auth_parent.id=auth_student.parent_id",'left')
            ->join('left join account_user_and_auth ON account_user_and_auth.user_id = auth_parent.id and account_user_and_auth.role_id = 4')
            ->group("auth_parent.id")
            ->order('create_at desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->field("account_user_and_auth.user_id,account_user_and_auth.auth_id as is_vip_auth_id,account_user_and_auth.auth_start_time,account_user_and_auth.auth_end_time,auth_parent.id,auth_parent.parent_name,auth_parent.telephone,auth_parent.create_at,auth_parent.email"
                . ",auth_parent.lock,auth_parent.flag,auth_parent.sex,group_concat(auth_student.student_name SEPARATOR'|') student_name")
            ->select();     

        $auth_type_use = D('Account_auths');
        foreach ($result as $key => $value) {
            $isVipInfo = $auth_type_use->isVipInfo($value['id'],4);
            $result[$key]['vipinfo'] = $isVipInfo;
            $result[$key]['create_at'] = date("Y-m-d,H:i:s",$value['create_at']);
            foreach ($isVipInfo['vip_data'] as $vk=>$vv) {
                $result[$key]['vipendtime'] = date('Y-m-d',$vv['auth_end_time']);
            }
        }

        $data['result'] = $result;
        $data['page'] = $show;
        $data['count'] = $count;
        return $data;
    }

    //message入库操作
    public function addMessageInfo( $data ) {
        $id = M('role_message')->add( $data );
        if ( $id ) {
            return $id;
        } else {
            return false;
        }
    }
    //接收推送的用户入库
    public function pushAddUser( $data ){

        $id = M('receive_message_user')->add( $data );
        if ( $id !=false ) {
            return true;
        } else {
            return false;
        }

    }

    public function pushAddUserAll( $data ){

        $id = M('receive_message_user')->addAll( $data );
        if ( $id !=false ) {
            return true;
        } else {
            return false;
        }

    }

    //入库所有人
    public function pushAllUser( $type,$message_id ) {
        if ( $type == 2) { //全部老师
            $ids = M('auth_teacher')->query("SELECT auth_teacher.id FROM `auth_teacher` INNER JOIN dict_schoollist ON auth_teacher.school_id = dict_schoollist.id LEFT JOIN account_user_and_auth ON account_user_and_auth.user_id = auth_teacher.id AND account_user_and_auth.role_id = 2 LEFT JOIN biz_class ON biz_class.class_teacher_id = auth_teacher.id GROUP BY auth_teacher.id ORDER BY auth_teacher.create_at DESC");

            foreach ( $ids as $k=>$v ){

                $teachermap = array(
                    'message_id' => $message_id,
                    'role_id' => 2,
                    'user_id' => $v['id'],
                    'addtime' => time(),
                );
                $this->pushAddUser( $teachermap );
            }

        }elseif ($type == 3) { //全部学生
            $sids = M('auth_student')->query("SELECT auth_student.id,dict_schoollist.`status` FROM `auth_student` LEFT JOIN dict_schoollist ON auth_student.school_id = dict_schoollist.id LEFT JOIN biz_class_student ON biz_class_student.student_id = auth_student.id LEFT JOIN biz_class ON biz_class.id = biz_class_student.class_id LEFT JOIN auth_parent ON auth_student.parent_id = auth_parent.id LEFT JOIN account_user_and_auth ON account_user_and_auth.user_id = auth_student.id AND account_user_and_auth.role_id = 3 GROUP BY auth_student.id ORDER BY auth_student.create_at DESC");
            foreach ( $sids as $sk=>$sv ){
                $stumap = array(
                    'message_id' => $message_id,
                    'role_id' => 3,
                    'user_id' => $sv['id'],
                    'addtime' => time(),
                );
                $this->pushAddUser( $stumap );
            }
        } else{ //全部家长
            $pids = M('auth_parent')->query("SELECT auth_parent.id,auth_student.parent_id,auth_student.create_at FROM `auth_parent` LEFT JOIN auth_student ON auth_parent.id = auth_student.parent_id LEFT JOIN account_user_and_auth ON account_user_and_auth.user_id = auth_parent.id AND account_user_and_auth.role_id = 4 GROUP BY auth_parent.id ORDER BY create_at DESC");
            foreach ( $pids as $pk=>$pv ){
                $parentmap = array(
                    'message_id' => $message_id,
                    'role_id' => 4,
                    'user_id' => $pv['id'],
                    'addtime' => time(),
                );
                $this->pushAddUser( $parentmap );
            }
        }
    }

    //更新message表
    public function updateMessageInfo( $message_id ) {

        $count = M('receive_message_user')->where("message_id=".$message_id)->count();
        $data = array(
            'receive_num' => $count,
        );
        $receive_num_count = M('role_message')->where("id=".$message_id)->find();

        if ($receive_num_count['receive_num'] != $count) {
            $mid = M('role_message')->where("id=".$message_id)->save( $data );

            if ( $mid !==false ) {

                return $mid;
            } else {
                return false;
            }
        } else {
            return true;
        }

    }

    //获取消息详情
    public function getMessageDetails( $message_id ){
        $messageInfo = M('role_message')->where("id=".$message_id)->find();
        return $messageInfo;
    }

    //修改主的message消息
    public function editMessageInfo( $data,$id ){
        M('role_message')->startTrans();

        $messageInfo = M('role_message')->where("id=".$id)->find();
        if ($messageInfo['status'] == 4) {
            $data['status'] = 1;
            $id = M('role_message')->where("id=".$id)->save( $data );

            if ( $id != false ) {
                return $id;
            } else {
                M('role_message')->rollback();
                return false;
            }
        } else{
            $id = M('role_message')->where("id=".$id)->save( $data );

            if ( $id != false ) {
                return $id;
            } else {
                M('role_message')->rollback();
                return false;
            }
        }

    }

    //删除message消息的所有接收的人
    public function delMessageUser( $message_id ){
        M('receive_message_user')->startTrans();
        $id = M('receive_message_user')->where("message_id=".$message_id)->delete();
        if ( $id ) {
            return $id;
        } else {
            M('receive_message_user')->rollback();
            return false;
        }
    }

    //根据id获取三个角色的user_id
    public function getMessageidUserid( $message_id ){
        $teacherrole = '';
        $sturole = '';
        $parentrole = '';
        $info = M('receive_message_user')->where("message_id=".$message_id)->field('role_id,user_id')->select();
        foreach ( $info as $k=>$v ){
            if ($v['role_id'] == 2) {
                $teacherrole .=','.$v['user_id'];
            }
            if ($v['role_id'] == 3) {
                $sturole .=','.$v['user_id'];
            }
            if ($v['role_id'] == 4) {
                $parentrole .=','.$v['user_id'];
            }
        }

        $teacherrole = ltrim($teacherrole,',');
        $sturole = ltrim($sturole,',');
        $parentrole = ltrim($parentrole,',');

        $data['teacherrole'] = $teacherrole;
        $data['sturole'] = $sturole;
        $data['parentrole'] = $parentrole;

        return $data;
    }

    //获取学生的选中详情
    public function getSelectAllStuModel( $map ){
        $count = M('receive_message_user')
            ->join('left join auth_student on auth_student.id=receive_message_user.user_id')
            ->join('left join dict_schoollist on auth_student.school_id=dict_schoollist.id')
            ->join('left join biz_class_student on biz_class_student.student_id=auth_student.id')
            ->join('left join biz_class on biz_class.id=biz_class_student.class_id')
            ->join('left join auth_parent on auth_student.parent_id = auth_parent.id')
            ->join('left join account_user_and_auth ON account_user_and_auth.user_id = auth_student.id and account_user_and_auth.role_id = 3')
            ->field('account_user_and_auth.user_id,account_user_and_auth.auth_id as is_vip_auth_id,account_user_and_auth.auth_start_time,account_user_and_auth.auth_end_time,dict_schoollist.school_name,auth_student.id,auth_student.student_id,auth_student.student_name,'
                . 'auth_student.birth_date,auth_student.create_at,auth_student.email,auth_parent.parent_name,'
                . 'auth_student.parent_tel,auth_student.lock,auth_student.flag')
            ->group('auth_student.id')
            ->where( $map )
            ->select();
        $count = count($count);

        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));

        $show = $Page->show('stuQueryList');

        $result = M('receive_message_user')
            ->join('left join auth_student on auth_student.id=receive_message_user.user_id')
            ->join('left join dict_schoollist on auth_student.school_id=dict_schoollist.id')
            ->join('left join biz_class_student on biz_class_student.student_id=auth_student.id')
            ->join('left join biz_class on biz_class.id=biz_class_student.class_id')
            ->join('left join auth_parent on auth_student.parent_id = auth_parent.id')
            ->join('left join account_user_and_auth ON account_user_and_auth.user_id = auth_student.id and account_user_and_auth.role_id = 3')
            ->field('account_user_and_auth.user_id,account_user_and_auth.auth_id as is_vip_auth_id,account_user_and_auth.auth_start_time,account_user_and_auth.auth_end_time,dict_schoollist.school_name,auth_student.id,ifnull(auth_student.student_id,\'\') student_id,auth_student.student_name,'
                . 'auth_student.birth_date,auth_student.create_at,auth_student.email,auth_parent.parent_name,'
                . 'auth_student.parent_tel,auth_student.lock,auth_student.flag,auth_student.sex,dict_schoollist.status')
            ->where( $map )
            ->group('auth_student.id')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $auth_type_use = D('Account_auths');
        foreach ($result as $key => $value) {
            $isVipInfo = $auth_type_use->isVipInfo($value['id'],3);
            $result[$key]['vipinfo'] = $isVipInfo;
            $result[$key]['birth_date'] = date("Y-m-d",$value['birth_date']);
            $result[$key]['create_at'] = date("Y-m-d,H:i:s",$value['create_at']);

            foreach ($isVipInfo['vip_data'] as $vk=>$vv) {
                $result[$key]['vipendtime'] = date('Y-m-d',$vv['auth_end_time']);
            }
        }

        $data['result'] = $result;
        $data['page'] = $show;
        $data['count'] = $count;
        return $data;
    }

    //获取家长的选中详情
    public function getSelectAllParentModel( $where ){
        $count = M('receive_message_user')
            ->join('left join auth_parent on auth_parent.id=receive_message_user.user_id')
            ->join("auth_student on auth_parent.id=auth_student.parent_id",'left')
            ->join('left join account_user_and_auth ON account_user_and_auth.user_id = auth_parent.id and account_user_and_auth.role_id = 4')
            ->where($where)
            ->group("auth_parent.id")
            ->order('create_at desc')
            ->field("account_user_and_auth.user_id,account_user_and_auth.auth_id as is_vip_auth_id,account_user_and_auth.auth_start_time,account_user_and_auth.auth_end_time,auth_parent.id,auth_parent.parent_name,auth_parent.telephone,auth_parent.create_at,auth_parent.email"
                . ",auth_parent.lock,auth_parent.flag,auth_parent.sex,group_concat(auth_student.student_name SEPARATOR'|') student_name")
            ->select();
        $count=count($count);

        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));

        $show = $Page->show('parentQueryList');

        //这里必须用join,牵扯到一个学校的ID
        $result = M('receive_message_user')
            ->join('left join auth_parent on auth_parent.id=receive_message_user.user_id')
            ->join("auth_student on auth_parent.id=auth_student.parent_id",'left')
            ->join('left join account_user_and_auth ON account_user_and_auth.user_id = auth_parent.id and account_user_and_auth.role_id = 4')
            ->where($where)
            ->group("auth_parent.id")
            ->order('create_at desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->field("account_user_and_auth.user_id,account_user_and_auth.auth_id as is_vip_auth_id,account_user_and_auth.auth_start_time,account_user_and_auth.auth_end_time,auth_parent.id,auth_parent.parent_name,auth_parent.telephone,auth_parent.create_at,auth_parent.email"
                . ",auth_parent.lock,auth_parent.flag,auth_parent.sex,group_concat(auth_student.student_name SEPARATOR'|') student_name")
            ->select();

        $auth_type_use = D('Account_auths');
        foreach ($result as $key => $value) {
            $isVipInfo = $auth_type_use->isVipInfo($value['id'],4);
            $result[$key]['vipinfo'] = $isVipInfo;
            $result[$key]['create_at'] = date("Y-m-d,H:i:s",$value['create_at']);
            foreach ($isVipInfo['vip_data'] as $vk=>$vv) {
                $result[$key]['vipendtime'] = date('Y-m-d',$vv['auth_end_time']);
            }
        }

        $data['result'] = $result;
        $data['page'] = $show;
        $data['count'] = $count;
        return $data;
    }

    //获取教师的选中详情
    public function getSelectAllTeacherModel( $where ) {

        $Model = M('receive_message_user');
        $join[] = 'left join auth_teacher on auth_teacher.id=receive_message_user.user_id';
        $join[] = "left JOIN dict_schoollist on auth_teacher.school_id = dict_schoollist.id";
        $join[] = "left JOIN auth_teacher_second ON auth_teacher_second.teacher_id = auth_teacher.id";
        $join[] = "left JOIN dict_course ON auth_teacher_second.course_id = dict_course.id";
        $join[] = "left JOIN dict_grade ON auth_teacher_second.grade_id = dict_grade.id";
        $join[] = "LEFT JOIN account_user_and_auth ON account_user_and_auth.user_id = auth_teacher.id and account_user_and_auth.role_id = 2";

        $count = $Model
            ->join($join)
            ->field("auth_teacher.id")
            ->where($where)
            ->group('auth_teacher.id')
            ->select();

        $Page = new \Think\Page(count($count), C('PAGE_SIZE_FRONT'));
        //$Page->parameter['keyword'] = urlencode($keyword);

        $show = $Page->show('queryList');

        $join[] = "LEFT JOIN biz_class ON biz_class.class_teacher_id = auth_teacher.id";
        $result = $Model
            ->join($join)
            ->field('account_user_and_auth.auth_id as is_vip_auth_id,account_user_and_auth.auth_start_time,account_user_and_auth.auth_end_time,dict_schoollist.school_name,auth_teacher.*,'
                . "GROUP_CONCAT(DISTINCT dict_course.course_name SEPARATOR '.')course_name,"
                . "GROUP_CONCAT(dict_grade.grade,biz_class.name SEPARATOR '.')grade_name")
            ->group('auth_teacher.id')
            ->order('auth_teacher.create_at desc')
            ->where($where)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        
        $auth_type_use = D('Account_auths');
        foreach ($result as $key => $value) {
            $isVipInfo = $auth_type_use->isVipInfo($value['id'],2);
            $result[$key]['vipinfo'] = $isVipInfo;
            $result[$key]['create_at'] = date('Y-m-d H:i:s',$value['create_at']);

            foreach ($isVipInfo['vip_data'] as $vk=>$vv) {
                $result[$key]['vipendtime'] = date('Y-m-d',$vv['auth_end_time']);
            }

        }

        $data['result'] = $result;
        $data['page'] = $show;
        $data['count'] = count($count);

        return $data;
    }
}