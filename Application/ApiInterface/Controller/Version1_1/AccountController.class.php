<?php
namespace ApiInterface\Controller\Version1_1;

use Common\Common\SMS;
use Common\Common\REDIS;

class AccountController extends PublicController
{
    public $model='';
    public $page_size=20;
    public $firstRow=0;
    public $listRow=0;
            
    public function __construct() {
        parent::__construct(); 
        header("Content-type: text/html; charset=utf-8");
    }

    //分页处理
    public function pageOperation(){
        if(I('page')){
            $page=I('page')<1?1:I('page');
        }else{
            $page=1;
        }
        $data['start']=($page-1)*$this->page_size;
        $data['end']=  $this->page_size;
        return $data;
    }

     public function AccountOwnerAuthListbeifen(){
        
        //用户ID
        $id=I('id');
        
        //用户角色id
        $role_id=intval(I('role_id'));
        if(!$role_id){
            $this->showjson(-1,'failed',array());
        }  


        if ($id == 0) { //游客模式要获取的权限
            $data['auth'] = array(1,4,5,6,7,10);
        } else {
            $data['auth'] = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22);
            $data['is_vip'] = 1;
        }

       
        $this->showjson(1,'success',$data);
    }

    /**
     * @描述：返回用户权限列表
     * @参数：id[int] Y 用户id
     * @参数：role_id[int] Y 用户角色
     * @返回值：code[int]{
     * "-1: "user_id参数不合法",
     * "200": "成功"
     * "is_vip": "是否是vip"
     * "auth" : "节点权限列表"
     * } Y 返回代号
     */
    public function AccountOwnerAuthList(){
        
        //用户ID
        $id=I('id');
        
        //用户角色id
        $role_id=intval(I('role_id'));
        if(!$role_id){
            $this->showjson(-1,'failed',array());
        }  

        //模拟所有权限列表

        if ( $id == 0 || $role_id==5 ) { //游客模式要获取的权限
            $map['auth_id'] = 1;
            $map['users_type_id'] = 5;
            $row = M('account_auth_to_node')->where( $map )->field('node_id')->find();
            $node_list = explode(',', $row['node_id']);
            foreach ($node_list as $key => $value) {
                $node_list[$key] = intval($value);
            }
            $data['auth'] = $node_list;    
        } else {
            $auth_type_use = D('Account_auths'); //普通权限加
            $auth_list = $auth_type_use->getIphoneAuthAndVipauth($id,$role_id);
            foreach ($auth_list as $key => $value) {

                if ($value == 13) {
                    array_push($auth_list, 17);
                }
                if ($value == 14) {
                    array_push($auth_list, 20);
                }
                $auth_list[$key] = intval($value);
            }
            $data['auth'] = $auth_list;
        }
        
        $auth_type_use = D('Account_auths'); //普通权限加
        $isvip = $auth_type_use->isVipInfo($id,$role_id);
        
        if ($isvip['is_vip'] == 1) {
            $data['is_vip'] = 1;
        } else {
            $data['is_vip'] = 2;
        }
        
        $this->showjson(1,'success',$data);
    }
    
    
    //把以前的用户都设置成90天的vip
    public function addVip() {
        set_time_limit(0);
        //家长
        $parent_map['flag'] = 1;
        $all_parent = M('auth_parent')->where($parent_map)->field('id')->select();
        foreach ($all_parent as $pk => $pv) {
            $vip_parent['user_id'] = $pv['id'];
            $vip_parent['role_id'] = 4;
            $vip_parent['auth_id'] = 4;
            $vip_parent['auth_start_time'] = time();
            $vip_parent['auth_end_time'] = time()+3600*24*30*3;
            $vip_parent['timetype'] = 1;

            $parent_where = array(
                'user_id' => $pv['id'],
                'role_id' => 4,
                'auth_id' => 4,

            );
            $info = M('account_user_and_auth')->where( $parent_where )->find();

            if(!empty($info)) { //存在就更新时间,不存在就添加90天的vip

                $save_parent = array(
                    'auth_start_time' => time(),
                    'auth_end_time' => time()+3600*24*30*3
                );

                $parent_id = M('account_user_and_auth')->where( $parent_where )->save( $save_parent );

            } else {
                
               $parent_id =  M('account_user_and_auth')->add( $vip_parent );
            }

            if (!$parent_id) {
                echo "--家长入库失败id号为".$pv['id'];
            } else {
                echo "成功";
            }
            
        }
       

        

        echo "----------入库ok---------";
    }

    public function addstu() {

         //学生
        set_time_limit(0);
        $student_map['flag'] = 1;
        $all_student = M('auth_student')->where($student_map)->field('id')->select();
        foreach ($all_student as $sk => $sv) {
            $vip_student['user_id'] = $sv['id'];
            $vip_student['role_id'] = 3;
            $vip_student['auth_id'] = 4;
            $vip_student['auth_start_time'] = time();
            $vip_student['auth_end_time'] = time()+3600*24*30*3;
            $vip_student['timetype'] = 1;

            $student_where = array(
                'user_id' => $sv['id'],
                'role_id' => 3,
                'auth_id' => 4,

            );
            $info2 = M('account_user_and_auth')->where( $student_where )->find();
            
            if(!empty($info2)) { //存在就更新时间,不存在就添加90天的vip

                $save_student = array(
                    'auth_start_time' => time(),
                    'auth_end_time' => time()+3600*24*30*3
                );
               $student_id =  M('account_user_and_auth')->where( $student_where )->save( $save_student );

            } else {
                
               $student_id =  M('account_user_and_auth')->add( $vip_student );
            }

            if (!$student_id) {
                echo "--学生入库失败id号为".$sv['id'];
            }
            
        }
    }

    public function addtea() {
        //老师
        set_time_limit(0);
        $teacher_map['flag'] = 1;
        $all_teacher = M('auth_teacher')->where($teacher_map)->field('id')->select();
        foreach ($all_teacher as $tk => $tv) {
            $vip_teacher['user_id'] = $tv['id'];
            $vip_teacher['role_id'] = 2;
            $vip_teacher['auth_id'] = 4;
            $vip_teacher['auth_start_time'] = time();
            $vip_teacher['auth_end_time'] = time()+3600*24*30*3;
            $vip_teacher['timetype'] = 1;

            $teacher_where = array(
                'user_id' => $tv['id'],
                'role_id' => 2,
                'auth_id' => 4,

            );
            $info3 = M('account_user_and_auth')->where( $teacher_where )->find();
            
            if(!empty($info3)) { //存在就更新时间,不存在就添加90天的vip

                $save_teacher = array(
                    'auth_start_time' => time(),
                    'auth_end_time' => time()+3600*24*30*3
                );
                $teacher_id = M('account_user_and_auth')->where( $teacher_where )->save( $save_teacher );

            } else {
                
                $teacher_id = M('account_user_and_auth')->add( $vip_teacher );
            }

            if (!$teacher_id) {
                echo "--老师入库失败id号为".$tv['id'];
            }
            
        }
    }

    /**
     * @描述：绑定账户百度推送渠道id
     * @参数：id[int] Y 用户id
     * @参数：role_id[int] Y 用户角色
     * @参数：channel_id[int] Y 百度渠道id
     * @返回值：code[int]{
     * "-1": "绑定失败",
     * "1": "成功"
     * } Y 返回代号
     */

    public function userBindBaidu () {

        $id = I('id');
        $role_id = I('role_id');
        $channel_id=I('channel_id');
        //用户角色id

        if(empty($role_id)){
            $this->showjson(-1,'role_id_failed',array());
        }

        if(empty($id)){
            $this->showjson(-1,'id_failed',array());
        }

        if(empty($channel_id)){
            $this->showjson(-1,'c_failed',array());
        }

        //首先判断是否存在这个channel_id
        $map_channel['channel_id_info'] = $channel_id;
        $map_channel_save['channel_id_info'] = '';

        M()->execute("UPDATE auth_teacher set pad_channel_id_info = '' WHERE pad_channel_id_info = '$channel_id'");
        M()->execute("UPDATE auth_teacher set channel_id_info = '' WHERE channel_id_info = '$channel_id'");
        M()->execute("UPDATE auth_student set pad_channel_id_info = '' WHERE pad_channel_id_info = '$channel_id'");
        M()->execute("UPDATE auth_student set channel_id_info = '' WHERE channel_id_info = '$channel_id'");
        M()->execute("UPDATE auth_parent set pad_channel_id_info = '' WHERE pad_channel_id_info = '$channel_id'");
        M()->execute("UPDATE auth_parent set channel_id_info = '' WHERE channel_id_info = '$channel_id'");

        $data['machine_type'] = getMachineType();
        $orgChannelIdName = 'channel_id_info';
        $prevChannelName = 'prev_channel_id_info';
        $Device = getallheaders()['Device'];
        if($Device == 'iPad') {
            $orgChannelIdName = 'pad_' . $orgChannelIdName;
            $prevChannelName = 'pad_'.$prevChannelName;
        }
        if ( $role_id == 2) { //老师


            $map['id'] = $id;
            $data[$orgChannelIdName] = $channel_id;
            $teainfo = M('auth_teacher')->where( $map )->find();
            if($teainfo[$prevChannelName] !== $channel_id && !empty($teainfo[$prevChannelName])){
                $Message = new \Home\Controller\MessageController();
                $parameters = [];
                $Message->addPushUserMessage('LOGIN_PASS',ROLE_TEACHER,$id,$parameters,USE_PREV_CHANNEL_ID,USE_USER_DEFINE_MESSAGE);
            }
            if ( empty($teainfo[$orgChannelIdName]) ) { //判断渠道id是否为空
                $tid = M('auth_teacher')->where( $map )->save( $data );
            } else {
                if ( $teainfo[$orgChannelIdName] != $channel_id ) {
                    $tid = M('auth_teacher')->where( $map )->save( $data );
                }
            }

            if( $tid === false ){
                $this->showjson(-1,'failed',array());
            } else {
                $this->showjson(1,'success',array());
            }

        } elseif( $role_id ==3 ) { //学生


            $map['id'] = $id;
            $data[$orgChannelIdName] = $channel_id;
            $stuinfo = M('auth_student')->where( $map )->find();
            if($stuinfo[$prevChannelName] !== $channel_id && !empty($stuinfo[$prevChannelName])){
                $Message = new \Home\Controller\MessageController();
                $parameters = [];
                $Message->addPushUserMessage('LOGIN_PASS',ROLE_STUDENT,$id,$parameters,USE_PREV_CHANNEL_ID,USE_USER_DEFINE_MESSAGE);
            }
            if ( empty($stuinfo[$orgChannelIdName]) ) { //判断渠道id是否为空
                $tid = M('auth_student')->where( $map )->save( $data );
            } else {
                if ( $stuinfo[$orgChannelIdName] != $channel_id ) {
                    $tid = M('auth_student')->where( $map )->save( $data );
                }
            }

            if( $tid === false ){
                $this->showjson(-1,'failed',array());
            } else {
                $this->showjson(1,'success',array());
            }

        } else { //家长

            $map['id'] = $id;
            $data[$orgChannelIdName] = $channel_id;
            $pinfo = M('auth_parent')->where( $map )->find();
            if($pinfo[$prevChannelName] !== $channel_id && !empty($pinfo[$prevChannelName])){
                $Message = new \Home\Controller\MessageController();
                $parameters = [];
                $Message->addPushUserMessage('LOGIN_PASS',ROLE_PARENT,$id,$parameters,USE_PREV_CHANNEL_ID,USE_USER_DEFINE_MESSAGE);
            }

            if ( empty($pinfo[$orgChannelIdName]) ) { //判断渠道id是否为空
                $tid = M('auth_parent')->where( $map )->save( $data );
            } else {
                if ( $pinfo[$orgChannelIdName] != $channel_id ) {
                    $tid = M('auth_parent')->where( $map )->save( $data );
                }
            }

            if( $tid === false ){
                $this->showjson(-1,'failed',array());
            } else {
                $this->showjson(1,'success',array());
            }
        }

    }
    /**
     * @描述：解绑账户百度推送渠道id
     * @参数：id[int] Y 用户id
     * @参数：role_id[int] Y 用户角色
     * @返回值：code[int]{
     * "-1": "绑定失败",
     * "1": "成功"
     * } Y 返回代号
     */

    public function userUnBindBaidu(){
        $id = getParameter('id', 'int');
        $role = getParameter('role_id', 'int');
        $providerName = getParameter('provide', 'str',false);
        $orgChannelIdName = 'channel_id_info';
        $Device = getallheaders()['Device'];
        if($Device == 'iPad') {
            $orgChannelIdName = 'pad_' . $orgChannelIdName;
        }
        if(empty($providerName))
            $providerName = 'baidu';

       switch($role)
       {
           case ROLE_TEACHER:$model = M('auth_teacher');
                   break;
           case ROLE_STUDENT:$model = M('auth_student');
               break;
           case ROLE_PARENT:$model = M('auth_parent');
               break;
           default:exit;
       }
       $data[$orgChannelIdName] = '';
       $where['id'] =  $id;
        if($model->where($where)->save($data))
         $this->showjson(1,'success',array());
        else
         $this->showjson(-1,'failed',array());
    }


    //查询家长是不是在vip中
    public function selectParentVip() {
        set_time_limit(0);
        $parent_map['flag'] = 1;
        $all_parent = M('auth_parent')
            ->where($parent_map)->field('id')
            ->select();


        $parentnodelist = array(); //没有入库权限的
        $overdue = array(); //vip过期的

        foreach ($all_parent as $k=>$v) {
            $nodemap['user_id'] = $v['id'];
            $nodemap['role_id'] = 4;
            $nodemap['auth_id'] = 4;
            $nodedata = M('account_user_and_auth')->order('auth_end_time desc')->where( $nodemap )->find();

            if( !empty($nodedata) ) {

                if ($nodedata['auth_end_time'] < time() ) {
                    $overdue[] = $v['id'];
                    $save_student = array(
                        'auth_end_time' => time()+3600*24*30*3
                    );
                    M('account_user_and_auth')->where( $nodemap )->save( $save_student );
                }

            } else {
                $parentnodelist[] = $v['id'];

                $vip_student['user_id'] = $v['id'];
                $vip_student['role_id'] = 4;
                $vip_student['auth_id'] = 4;
                $vip_student['auth_start_time'] = time();
                $vip_student['auth_end_time'] = time()+3600*24*30*3;
                $vip_student['timetype'] = 1;
                M('account_user_and_auth')->add( $vip_student );
            }
        }

        print_r($parentnodelist);
        print_r($overdue);
    }


    //查询学生是不是在vip中
    public function selectStuVip() {
        set_time_limit(0);
        $parent_map['flag'] = 1;
        $all_parent = M('auth_student')
            ->where($parent_map)->field('id')
            ->select();


        $parentnodelist = array(); //没有入库权限的
        $overdue = array(); //vip过期的

        foreach ($all_parent as $k=>$v) {
            $nodemap['user_id'] = $v['id'];
            $nodemap['role_id'] = 3;
            $nodemap['auth_id'] = 4;
            $nodedata = M('account_user_and_auth')->order('auth_end_time desc')->where( $nodemap )->find();

            if( !empty($nodedata) ) {

                if ($nodedata['auth_end_time'] < time() ) {
                    $overdue[] = $v['id'];
                    $save_student = array(
                        'auth_end_time' => time()+3600*24*30*3
                    );
                    M('account_user_and_auth')->where( $nodemap )->save( $save_student );
                }

            } else {
                $parentnodelist[] = $v['id'];

                $vip_student['user_id'] = $v['id'];
                $vip_student['role_id'] = 3;
                $vip_student['auth_id'] = 4;
                $vip_student['auth_start_time'] = time();
                $vip_student['auth_end_time'] = time()+3600*24*30*3;
                $vip_student['timetype'] = 1;
                M('account_user_and_auth')->add( $vip_student );

            }
        }

        print_r($parentnodelist);
        print_r($overdue);
    }


    //查询老师是不是在vip中
    public function selectTeachVip() {
        set_time_limit(0);
        $parent_map['flag'] = 1;
        $all_parent = M('auth_teacher')
            ->where($parent_map)->field('id')
            ->select();


        $parentnodelist = array(); //没有入库权限的
        $overdue = array(); //vip过期的

        foreach ($all_parent as $k=>$v) {
            $nodemap['user_id'] = $v['id'];
            $nodemap['role_id'] = 2;
            $nodemap['auth_id'] = 4;
            $nodedata = M('account_user_and_auth')->order('auth_end_time desc')->where( $nodemap )->find();

            if( !empty($nodedata) ) {

                if ($nodedata['auth_end_time'] < time() ) {
                    $overdue[] = $v['id'];
                    $save_student = array(
                        'auth_end_time' => time()+3600*24*30*3
                    );
                    M('account_user_and_auth')->where( $nodemap )->save( $save_student );
                }

            } else {
                $parentnodelist[] = $v['id'];

                $vip_student['user_id'] = $v['id'];
                $vip_student['role_id'] = 2;
                $vip_student['auth_id'] = 4;
                $vip_student['auth_start_time'] = time();
                $vip_student['auth_end_time'] = time()+3600*24*30*3;
                $vip_student['timetype'] = 1;
                M('account_user_and_auth')->add( $vip_student );

            }
        }

        print_r($parentnodelist);
        print_r($overdue);
    }


    public function deleteVipC() {
        $list = M('account_user_and_auth')->group('user_id,role_id,auth_id')->field('count(id) as count_id,group_concat(id) as id')->order('auth_end_time desc')->having('count_id>1')->select();
        if (!empty($list)) {
            foreach ($list as $k=>$v) {
                $ids = explode(',',$v['id']);
                unset($ids[0]);
                $valid = array_values($ids);
                $idstr = implode(',',$valid);
                $map['id'] = array('in',$idstr);
                M('account_user_and_auth')->where($map)->delete();
            }
        }
        echo "success";

    }


    public function sendDiskErrorMessage() {
        $smsapi = new SMS();
        $is_send = $smsapi->templateSMSDisk(17194371392,'磁盘报警故障,000000');
        print_r($is_send);die();
    }

    public function redisFirst() {
        $redis_obj=new REDIS();
        $smsapi = new SMS();
        $redis=$redis_obj->init_redis();
        if ($redis==1001 || $redis==1002) {
            $smsapi = new SMS();
            $smsapi->templateSMSDisk(17194371392,'redis报警故障,000000');
        } else{
            $ok = $redis->setex('ping',5,'ping server....');
            if ($ok == null || $ok== false) {
                $smsapi->templateSMSDisk(17194371392,'redis报警故障,000000');
            }
        }
        echo "执行完毕。。。。。";

    }
}