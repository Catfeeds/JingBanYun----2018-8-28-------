<?php
namespace Api\Controller;
use Think\Controller;

class AccountController extends Controller
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
    
    //用户 角色 所拥有的权限
    public function AccountOwnerAuthList(){
        
        //用户ID
        $id=I('id');
        
        //用户角色id
        $role_id=intval(I('role_id'));
        if(!$role_id){
            $this->showjson(-1,'failed',array());
        }  

        //模拟所有权限列表

        if ($id == 0 || $role_id==5 ) { //游客模式要获取的权限
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
            $vip_parent['auth_id'] = 3;
            $vip_parent['auth_start_time'] = time();
            $vip_parent['auth_end_time'] = time()+3600*24*30*3;
            $vip_parent['timetype'] = 1;

            $parent_where = array(
                'user_id' => $pv['id'],
                'role_id' => 4,
                'auth_id' => 3,

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
                'auth_id' => 3,

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

    //pub
    public function addcrentvip() {
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
                continue;
            } else {
                $student_id =  M('account_user_and_auth')->add( $vip_student );
            }

            if (!$student_id) {
                echo "--学生入库失败id号为".$sv['id'];
            }
        }

        echo "入库成功";
    }

    public function addtea() {
        //老师
        set_time_limit(0);
        $teacher_map['flag'] = 1;
        $all_teacher = M('auth_teacher')->where($teacher_map)->field('id')->select();
        foreach ($all_teacher as $tk => $tv) {
            $vip_teacher['user_id'] = $tv['id'];
            $vip_teacher['role_id'] = 2;
            $vip_teacher['auth_id'] = 3;
            $vip_teacher['auth_start_time'] = time();
            $vip_teacher['auth_end_time'] = time()+3600*24*30*3;
            $vip_teacher['timetype'] = 1;

            $teacher_where = array(
                'user_id' => $tv['id'],
                'role_id' => 2,
                'auth_id' => 3,

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

    public function addVipDate(){
        set_time_limit(0);
        $vip = M('account_user_and_auth')->select();
        foreach ($vip as $k=> $v) {
            $update['auth_end_time'] = $v['auth_end_time']+3600*24*30*3;
            $data['id'] = $v['id'];
            $up_id = M('account_user_and_auth')->where($data)->save($update);
            if ( !$up_id ) {
                echo "更新失败...失败id为".$v['id'];
            }
        }
        echo "全部更新成功";
    }


    //查询家长是不是在vip中
    public function selectParentVip() {
        set_time_limit(0);
        $parent_map['flag'] = 1;
        $all_parent = M('auth_parent')
                    ->where($parent_map)->field('id')
                    ->select();
        $parentnode = array(); //没有入库权限的
        $overdue = array(); //vip过期的

        foreach ($all_parent as $k=>$v) {
            $nodemap['user_id'] = $v['id'];
            $nodemap['role_id'] = 4;
            $node = M('account_user_and_auth')->where( $nodemap )->find();
            if( empty($node) ) {
                $parentnode[] = $v['id'];
            } else {
                $nodemap['auth_end_time'] = array('lt',time());
                $node = M('account_user_and_auth')->where( $nodemap )->find();
                if (!empty( $node )) {
                    $overdue[] = $node['user_id'];
                }
            }
        }

        print_r($parentnode);
        print_r($overdue);
    }


    //查询学生是不是在vip中
    public function selectStuVip() {
        set_time_limit(0);
        $parent_map['flag'] = 1;
        $all_parent = M('auth_student')
            ->where($parent_map)->field('id')
            ->select();
        $parentnode = array(); //没有入库权限的
        $overdue = array(); //vip过期的

        foreach ($all_parent as $k=>$v) {
            $nodemap['user_id'] = $v['id'];
            $nodemap['role_id'] = 3;
            $node = M('account_user_and_auth')->where( $nodemap )->find();
            if( empty($node) ) {
                $parentnode[] = $v['id'];
            } else {
                $nodemap['auth_end_time'] = array('lt',time());
                $node = M('account_user_and_auth')->where( $nodemap )->find();
                if (!empty( $node )) {
                    $overdue[] = $node['user_id'];
                }
            }
        }

        print_r($parentnode);
        print_r($overdue);
    }


    //查询老师是不是在vip中
    public function selectTeachVip() {
        set_time_limit(0);
        $parent_map['flag'] = 1;
        $all_parent = M('auth_teacher')
            ->where($parent_map)->field('id')
            ->select();
        $parentnode = array(); //没有入库权限的
        $overdue = array(); //vip过期的

        foreach ($all_parent as $k=>$v) {
            $nodemap['user_id'] = $v['id'];
            $nodemap['role_id'] = 2;
            $node = M('account_user_and_auth')->where( $nodemap )->find();
            if( empty($node) ) {
                $parentnode[] = $v['id'];
            } else {
                $nodemap['auth_end_time'] = array('lt',time());
                $node = M('account_user_and_auth')->where( $nodemap )->find();
                if (!empty( $node )) {
                    $overdue[] = $node['user_id'];
                }
            }
        }

        print_r($parentnode);
        print_r($overdue);
    }

    //查询code
    public function getClassCode() {
        $map['class_code'] = '';
        $row = M('biz_class')->where($map)->select();
        $list = array();
        foreach ($row as $k=>$v ) {
            $list[] = $v['id'];
        }
        print_r($list);die();
    }

}