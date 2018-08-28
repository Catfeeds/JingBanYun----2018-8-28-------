<?php
namespace Common\Model;
use Think\Model; 

class Account_authsModel extends Model{
    
    public    $model='';
    protected $tableName = 'account_user_and_auth';  
	
    public function __construct(){  
        parent::__construct();  
        $this->model=M($this->tableName);
    }
     
    /*
     * 公共插入权限方法      
     */ 
    public function inserNode( $id, $role_id,$auth_id,$starttime,$endtime,$timetype){
        $model=$this->model;
        $map = array(
            'user_id' => $id,
            'role_id' => $role_id,
            'auth_id' => $auth_id,
        );
        $row = $model->where( $map )->find();

        if (!empty($row)) {
            $data = array(
                'auth_start_time' => $starttime,
                'auth_end_time' => $endtime,
                'timetype' => $timetype
            );

            $res_id = $model->where($map)->save( $data); 

        } else {
            $data = array(
                'user_id' => $id,
                'role_id' => $role_id,
                'auth_id' => $auth_id,
                'auth_start_time' => $starttime,
                'auth_end_time' => $endtime,
                'timetype' => $timetype
            );

            $res_id = $model->add( $data ); 
        }
            
        //print_r($res_id);die();
        return $res_id;
    }

    /*
     * 公共更新权限方法      
     */ 

    public function updateNode( $id, $role_id,$auth_id,$starttime,$endtime,$timetype){
        $model=$this->model;

        $data = array(
            'auth_start_time' => $starttime,
            'auth_end_time' => $endtime,
            'timetype' => $timetype,
        );

        $where = array(
            'user_id' => $id,
            'role_id' => $role_id,
            'auth_id' => $auth_id,

        );
        $info = $model->where( $where )->find();
        
        if (!empty($info)) {
            $up_id = $model->where( $where )->save( $data );
        } else {
            $up_id = $this->inserNode($id, $role_id,$auth_id,$starttime,$endtime,$timetype);
        }

        return $up_id;
    }



    /*
    *   插入到主表中 就是学校表 先判断是否存在 不存在就插入 否则就修改
    */

    public function insertSchool( $id,$data ) {
        $map['id'] = $id;
        $row = M('dict_schoollist')->where( $map )->save( $data );
        if ( $row ) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * 公共操作记录入库方法   
     */ 

    public function insertAuthLog ( $timetype,$edittype,$viptimelong,$editexpiretime,$root_id,$remark,$user_id,$role_id) {
        $data = array(
            'timetype' => $timetype,
            'edittype' => $edittype,
            'viptimelong' => $viptimelong,
            'editexpiretime' => $editexpiretime,
            'addtime' => time(),
            'root_id' => $root_id,
            'remark' => $remark,
            'user_id' => $user_id,
            'role_id' => $role_id,
        );

        $log_id = M('account_auth_notes')->add( $data );

        if ( $log_id ) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * 根据用户权限和角色  获取对应的权限类表
     */

    public function getUserAuthAndRole( $auth_id,$users_type_id ) {
        $data['auth_id'] = $auth_id;
        $data['users_type_id'] = $users_type_id;
        $auth_list = M('account_auth_to_node')->field('node_id')->where( $data )->find();
        if (!empty($auth_list)) {
            $auth_array = explode(',', $auth_list['node_id']);
        }

        return $auth_array;
    }

    /*
     * 获取普通用户的权限列表
     */

    public function getAuthAndVipauth( $user_id,$users_type_id ) {
        
        $auth_list = $this->getUserAuthAndRole(2,$users_type_id); //先查询普通权限

        $data= array(
            'user_id' => $user_id,
            'role_id' => $users_type_id,
            'auth_start_time' => array('lt',time()),
            'auth_end_time' =>array('gt',time()),
        );

        $auth_ids = M('account_user_and_auth')->where( $data )->field('role_id,auth_id')->select(); //查询vip权限 
        
        //根据vip的auth_id获取该权限节点

        if (!empty($auth_ids)) {

            $data_info = array();
            foreach ($auth_ids as $key => $value) {
                $data_info[] =  $this->getUserAuthAndRole($value['auth_id'],$value['role_id']);
            }
        }

        
        //查询vip权限 如果存在就push到普通权限里面
        if(!empty($data_info)) {
            foreach ($data_info as $k => $v) {
                foreach ($v as $ck => $cv) {
                    if ( !in_array($cv, $auth_list)) { //如果不存在把权限push到普通权限中
                        
                        if (!empty($auth_list)) {
                            array_push($auth_list, $cv);
                        } else {
                            $auth_list[] = $cv;
                        }
                    }
                }
            }
        }

        //把控制器和方法放到限制列表中
        if (!empty($auth_list)) {
            $list = array();
            foreach ($auth_list as $ak => $av) {

                $action_map['id'] = $av;
                $row_action = M('account_node_list')->where( $action_map )->find();
                if (!empty($row_action['controller_action'])) {

                    $a = explode(',', $row_action['controller_action']);
                    foreach ($a as $aak => $aav) {
                        if ( !in_array($aav, $list)) { //如果不存在把权限push到普通权限中
                            array_push($list, $aav);
                        }
                    }
                    
                }
            }
        } 
        return $list;
    }

    /*
    *手机获取权限列表
    */

    public function getIphoneAuthAndVipauth( $user_id,$users_type_id ) {
        
        $auth_list = $this->getUserAuthAndRole(2,$users_type_id); //先查询普通权限

        $data= array(
            'user_id' => $user_id,
            'role_id' => $users_type_id,
            'auth_start_time' => array('lt',time()),
            'auth_end_time' =>array('gt',time()),
        );

        $auth_ids = M('account_user_and_auth')->where( $data )->field('role_id,auth_id')->select(); //查询vip权限
        
        //根据vip的auth_id获取该权限节点

        if (!empty($auth_ids)) {

            $data_info = array();
            foreach ($auth_ids as $key => $value) {
                $data_info[] =  $this->getUserAuthAndRole($value['auth_id'],$value['role_id']);
            }
        }

        
        //查询vip权限 如果存在就push到普通权限里面
        if(!empty($data_info)) {
            foreach ($data_info as $k => $v) {
                foreach ($v as $ck => $cv) {
                    if ( !in_array($cv, $auth_list)) { //如果不存在把权限push到普通权限中
                        
                        if (!empty($auth_list)) {
                            array_push($auth_list, $cv);
                        } else {
                            $auth_list[] = $cv;
                        }
                    }
                }
            }
        }

        return $auth_list;
    }

    /*
     * 获取用户是否是vip并获取vip时间
     */

    public function isVipInfo( $user_id,$users_type_id) {
        
        $data= array(
            'user_id' => $user_id,
            'role_id' => $users_type_id,
            'auth_start_time' => array('lt',time()),
            'auth_end_time' =>array('gt',time()),
        );

        $auth_ids = M('account_user_and_auth')->order('auth_end_time desc')->where( $data )->select(); //查询vip权限

        $vipdata = array();
        if(!empty($auth_ids)) {

            $daycount= 0;
            foreach ($auth_ids as $key => $value) {
                if ($value['auth_id'] == 3) { //团体
                    $vipdata=[];
                    $daycount = round(($value['auth_end_time'] - time())/3600/24);

                    if ($value['timetype'] == 1) {
                        $vipdata['info'] = "试用时间";
                    } else {
                        $vipdata['info'] = "使用时间";
                    }
                    $vipdata['is_auth'] = $value['auth_id'];
                    break;
                } else { //个人
                    
                    if (empty($vipdata)) {
                        $daycount = round(($value['auth_end_time'] - time())/3600/24);

                        if ($value['timetype'] == 1) {
                            $vipdata['info'] = "试用时间";
                        } else {
                            $vipdata['info'] = "使用时间";
                        }
                        $vipdata['is_auth'] = $value['auth_id'];
                    }

                }

            }
            $vipdata['is_vip'] = 1;
            $vipdata['vip_day'] = $daycount;
        } else {
            $vipdata['is_vip'] = 2;
        }
        /*echo "<pre>";
        print_r($vipdata);
        echo "</pre>";
        die();*/
        return $vipdata;
    }


    /*
     * 添加散户30天vip
     */

    public function addUserVip( $data ) {

        $vipmap['user_id'] = $data['user_id'];
        $vipmap['role_id'] = $data['role_id'];
        $vipmap['auth_id'] = $data['auth_id'];

        $vipinfo =M('account_user_and_auth')->where( $vipmap )->find();

        if (!empty($vipinfo)) {
            return true;
        } else {
            $id = M('account_user_and_auth')->add( $data );

            if ( $id ) {
                return true;
            }else{
                return false;
            }
        }

    }

    /*
     * 查询账户还剩余N天的VIP账号
     * auth_id 用户类型
     * daysLeft 剩余天数
     * role_id  角色
     */

    public function getAccountsByAuthIdAndDaysLeft( $auth_id,$daysLeft,$role_id ) {
        $where['role_id'] = $role_id;
        $where['auth_id'] = $auth_id;
        $where['from_unixtime(auth_end_time,\'%Y-%m-%d\')'] = date("Y-m-d",time()+$daysLeft*86400);
        return $this->model->where($where)->field('user_id')->select();
    }

    public function getUserInfo( $keyId ) {
        $where['id'] = $keyId;
        return $this->model->where($where)->field('role_id,user_id,auth_id')->find();
    }
    
    /*
     * 获得所有vip权限列表
     */
    public function getAuthList(){
        $account_where['id']=array('not in','1,2');
        $account_list=M('account_auth')->where($account_where)->field('id,auth_name')->select();
        return $account_list;
    }
    
    /*
     * 获得某个用户的所有vip权限
     */
    public function getUserVipAuthAll($user_id,$role){
        $map['user_id'] = $user_id;
        $map['role_id'] = $role;
        $map['account_auth.id'] = array('not in','1,2');
        $list = M('account_user_and_auth')->where($map)->join('account_auth on account_auth.id=account_user_and_auth.auth_id')->field('account_user_and_auth.*,account_auth.auth_name')->select();   
        foreach($list as $key=>$val){
            if($val['auth_start_time']<=time() && $val['auth_end_time']>=time()){
                $expired=1;
            }elseif($val['auth_start_time']>=time() && $val['auth_end_time']>=time()){
                $expired=2;
            }else{
                $expired=0;
            }
            $list[$key]['expired']=$expired;
        }   
        return $list;
    }

    //TODO:获得某个用户的所有VIP类型
    /*
     *描述：获得某个用户的所有VIP类型
     */
    public function getVipType($where)
    {
        return $this->model
            ->where($where)
            ->find();
    }
    //TODO:删除某个用户的VIP
    /*
     *描述：删除某个用户的VIP
     */
    public function deleteVip($where)
    {
        return $this->model
            ->where($where)
            ->delete();
    }
}