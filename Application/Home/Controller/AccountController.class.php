<?php
namespace Home\Controller;

use Think\Controller;
use Common\Common\CSV;

class AccountController extends PublicController{
    public function __construct() {
        parent::__construct(); 
        header("Content-type: text/html; charset=utf-8");
    }
    
    //权限列表
    public function auth() {

        if (!session('?admin')) redirect(U('Index/index'));
        //赋值公共的头部信息
        $this->assign('module', '账户管理');
        $this->assign('nav', '权限类型管理');
        $this->assign('subnav', '权限类型列表');


        $keyword = $_GET['keyword'];
        
        if (!empty($keyword)) {
            $where['auth_name'] = array('like', '%' . $keyword . '%');
        }

        $where['status'] = 1;

        $auth_list = M('account_auth')->where( $where )->select();
        $this->assign('list',$auth_list);
        $this->keyword = $keyword;
        $this->display();
    }

    //用户角色列表

    public function userRole() {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '账户管理');
        $this->assign('nav', '用户角色管理');
        $this->assign('subnav', '用户角色列表');

        $keyword = $_GET['keyword'];
        
        if (!empty($keyword)) {
            $where['type_name'] = array('like', '%' . $keyword . '%');
        }

        $where['status'] = 1;

        $auth_list = M('account_users_type')->where( $where )->select();
        $this->assign('list',$auth_list);
        $this->keyword = $keyword;
        $this->display();
    }
     
    //功能模块节点列表

    public function node () {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '账户管理');
        $this->assign('nav', '功能模块管理');
        $this->assign('subnav', '功能模块列表');

        $keyword = $_POST['keyword'];
        $level = $_POST['level'];//模块级别
        $fid = $_POST['fid'];//父级模块fid
        
        if (!empty($fid)) $where['fid'] = $fid;

        if (!empty($level)) {
            if ($level ==1 ) {  
                $where['fid'] = array('eq',0);
            } else { //2级
                $where['fid'] = array('neq',0);
            } 
        }

        if (!empty($level) && !empty($fid)) {
            if ($level ==1 ) {  
                $where['fid'] = array('eq',0);
                $where['id'] = array('eq',$fid);
            } else { //2级
                $where['fid'] = array(array('gt',0),array('eq',$fid), 'and') ;
            } 
            
        }
        //print_r($where);die();

        if (!empty($keyword)) {
            $where['node_name'] = array('like', '%' . $keyword . '%');
        }

        $this->assign('level', $level);
        $this->assign('f_id', $fid);
        $this->assign('keyword', $keyword);

        $where['status'] = 1;
        $count = M('account_node_list')->where( $where )->count();

        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($where as $key => $val) {
            //$Page->parameter[$key] = urlencode($val);
            $Page->parameter[$key] = $val;
        }
        //dump($check);
        $show = $Page->show();


        $auth_list = M('account_node_list')->where( $where )->limit($Page->firstRow . ',' . $Page->listRows)->select();

        if (!empty($auth_list)) {
            foreach ($auth_list as $key => $value) {
                if ($value['fid']!=0) {
                    $map['id'] = $value['fid'];
                    $row = M('account_node_list')->where( $map )->field('node_name')->find();
                    $auth_list[$key]['fname'] = $row['node_name'];
                } else {
                    $auth_list[$key]['fname'] = "无";
                }
            }
        }
        
        //查询父级模块
        $fw['fid'] = 0;
        $fw['status'] = 1;
        $fdata = M('account_node_list')->where( $fw )->field('id,node_name')->select();

        $this->assign('fdata',$fdata);
        $this->assign('list',$auth_list);
        $this->assign('page', $show);
        $this->display();
    }


    //账户列表

    public function accountList () {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '账户管理');
        $this->assign('nav', '账户列表管理');
        $this->assign('subnav', '账户列表');

        $keyword = $_GET['keyword'];
        $this->keyword = $keyword;
        if (!empty($keyword)) {
            $map['a.auth_name'] = array('like', '%' . $keyword . '%');
            $map['aut.type_name'] = array('like', '%' . $keyword . '%');
            $map['_logic'] = 'OR';
        }

        $count =  M('account_auth_to_node as atn')
            ->JOIN('LEFT JOIN account_auth as a ON atn.auth_id = a.id')
            ->JOIN('LEFT JOIN account_users_type as aut ON atn.users_type_id = aut.id')
            ->field('atn.id,a.auth_name,aut.type_name,atn.create_at,atn.node_id,atn.status')
            ->order('atn.create_at desc')
            ->where( $map )
            ->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($map as $key => $val) {
            //$Page->parameter[$key] = urlencode($val);
            $Page->parameter[$key] = $val;
        }
        //dump($check);
        $show = $Page->show();


        $list = M('account_auth_to_node as atn')
            ->JOIN('LEFT JOIN account_auth as a ON atn.auth_id = a.id')
            ->JOIN('LEFT JOIN account_users_type as aut ON atn.users_type_id = aut.id')
            ->field('atn.id,a.auth_name,aut.type_name,atn.create_at,atn.node_id,atn.status')
            ->order('atn.create_at desc')
            ->where($map)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        
        if ( !empty($list) ) {
            foreach ($list as $k => $v) {
                if(!empty($v['node_id'])) {
                    $ids = explode(',', $v['node_id']);
                    $idmap['id'] = array('in',$ids);
                    $row = M('account_node_list')->where( $idmap )->field('node_name')->select();
                    $id_list = array();
                    foreach ($row as $ik => $iv) {
                        $id_list[] = $iv['node_name'];
                    }

                    $list[$k]['users_type_name'] = $id_list;

                }
            }
        }
        
        //print_r($list);die();
        $this->assign('page', $show);
        $this->assign('list',$list);
        $this->display();
    }

    //账户列表停用

    public function approve() {
        if (!session('?admin')) redirect(U('Index/index'));

        $id  = $_GET['id'];
        if (empty($id)){
            $this->ajaxReturn('error');
        } 
        $map['id'] = $id;
        $data['status'] = 0;
        $update_id = M('account_auth_to_node')->where( $map )->save( $data );

        if ( $update_id ) {
            $this->ajaxReturn('success'); 
        } else {
            $this->ajaxReturn('error'); 
        }

    }

     //账户恢复使用

    public function recovery() {
        if (!session('?admin')) redirect(U('Index/index'));

        $id  = $_GET['id'];
        if (empty($id)){
            $this->ajaxReturn('error');
        } 
        $map['id'] = $id;
        $data['status'] = 1;
        $update_id = M('account_auth_to_node')->where( $map )->save( $data );

        if ( $update_id ) {
            $this->ajaxReturn('success'); 
        } else {
            $this->ajaxReturn('error'); 
        }

    }

    //删除账户

    public function deleteAccount(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        if (session('admin.role') == 3) {
            echo 'error';die;
        }
        
        $id  = $_GET['id'];
        $Model = M('account_auth_to_node');
        $id  = $Model->where("id=$id")->delete(); 

        if ( $id ) {
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('error');
        }
        
    }


    //添加账号

    public function addAccount(){

        $this->assign('module', '账户管理');
        $this->assign('nav', '账户添加管理');
        $this->assign('subnav', '账户添加');

        //获取权限列表
        $auth_list = M('account_auth')->field('id,auth_name')->select();
        
        //获取角色列表
        $user_type_list = M('account_users_type')->field('id,type_name')->select();

        //获取功能模块列表
        $map['fid'] = 0; //先查询父级
        $node_list = M('account_node_list')->field('id,node_name,fid')->where( $map )->select();

        foreach ($node_list as $key => $value) {
            $data['fid'] = $value['id'];
            $row = M('account_node_list')->where( $data )->field('id,node_name,fid')->select();
            $node_list[$key]['child_data'] = $row;
        }

        $this->assign('auth_list',$auth_list);
        $this->assign('user_type_list',$user_type_list);
        $this->assign('node_list',$node_list);

        $this->display();
    }

    //进行权限入库
    public function saveNodeData() {
        $auth_id = $_POST['auth_id'];
        $user_type_id = $_POST['user_type_id'];
        $node_id = implode(',', $_POST['node_id']);
        if (empty($node_id)) {
            $this->error('请选择至少一项功能权限');
        }
        
        $data = array(
            'auth_id' => $auth_id,
            'users_type_id' => $user_type_id,
            'node_id' => $node_id,
            'create_at' => time(),
        );

        $map = array(
            'auth_id' => $auth_id,
            'users_type_id' => $user_type_id,
        );

        $row = M('account_auth_to_node')->where( $map )->find();

        if (!empty($row)) {
            $this->error('已存在权限,请进行修改');

        } else {

            $id = M('account_auth_to_node')->add( $data );
            if ( $id ) {
                $this->redirect("Account/accountList");
            } else {
                $this->error('添加失败'); 
            }
        }
    }

    //修改权限

    public function editNodeView() {
        $id  = $_GET['id'];

        $this->id = $id;//把id复制到模板，为添加做准备
        $this->assign('module', '账户管理');
        $this->assign('nav', '账户修改管理');
        $this->assign('subnav', '账户修改');
        //获取权限列表
        $auth_list = M('account_auth')->field('id,auth_name')->select();
        
        //获取角色列表
        $user_type_list = M('account_users_type')->field('id,type_name')->select();

        //获取功能模块列表
        $map['fid'] = 0; //先查询父级
        $node_list = M('account_node_list')->field('id,node_name,fid')->where( $map )->select();

        foreach ($node_list as $key => $value) {
            $data['fid'] = $value['id'];
            $row = M('account_node_list')->where( $data )->field('id,node_name,fid')->select();
            $node_list[$key]['child_data'] = $row;
        }

        //获取该id的node节点
        $map['id'] = $id;

        $nodedata = M('account_auth_to_node')->where( $map )->find();
        $nodelist = explode(',',$nodedata['node_id']);
        
        //print_r($nodedata);die();
        $this->assign('nodelist',$nodelist);//用户选择的权限
        $this->assign('nodedata',$nodedata); //选取的节点功能信息


        $this->assign('count_all',count($nodelist));
        //首先拼装好已经选择的权限
        $num = 0;
        foreach ($node_list as $k => $v) {
            if ( in_array($v['id'], $nodelist)) {
                $num++;
                $node_list[$k]['is_selectd'] = 'on';
            }

            if (!empty($v['child_data'])) {
                
                foreach ($v['child_data'] as $ck => $cv) {

                    if ( in_array($cv['id'], $nodelist)) {
                        $num++;
                        $node_list[$k]['child_data'][$ck]['is_selectd'] = 'on';
                        $node_list[$k]['is_red'] = 'on';
                    }
                }
            }
        }
        
        if ($num == 18) { //全部节点权限
            $this->assign('all_node',1);
        }

        $this->assign('auth_list',$auth_list); //角色列表
        $this->assign('user_type_list',$user_type_list);//用户类型列表
        $this->assign('node_list',$node_list);//最后的节点列表 并且已经得到选中的
        $this->display();
    }

    //执行修改

    public function saveNodeEdit() {

        $id  = $_POST['id'];
        $auth_id = $_POST['auth_id'];
        $user_type_id = $_POST['user_type_id'];
        $node_id = implode(',', $_POST['node_id']);
        if (empty($node_id)) {
            $this->error('请选择至少一项功能权限');
        }
        
        $data = array(
            'auth_id' => $auth_id,
            'users_type_id' => $user_type_id,
            'node_id' => $node_id,
            'create_at' => time(),
        );

       /* $map = array(
            'auth_id' => $auth_id,
            'users_type_id' => $user_type_id,
        );

        $row = M('account_auth_to_node')->where( $map )->find();*/

       /* if (!empty($row)) {
            $this->error('已存在权限,请进行修改');

        } else {
*/
        //get userinfo by $mapid
        $userInfo = D('Account')->getUserInfo($id);

        $map['id'] = $id;
        $id = M('account_auth_to_node')->where( $map )->save( $data );

        //push message
        $parameters = array( 'msg' => array() , 'url' => array( 'type' => 0));
        if($userInfo['auth_id'] == 2 && $data['auth_id'] == 3)
        {
            A('Home/Message')->addPushUserMessage('VIP_SUCCESS',$userInfo['role_id'],$userInfo['user_id'],$parameters);
        }
        else if ($userInfo['auth_id'] == 3 && $data['auth_id'] == 2)
        {
            A('Home/Message')->addPushUserMessage('VIP_EXPIRED',$userInfo['role_id'],$userInfo['user_id'],$parameters);
        }

        if ( $id ) {
            $this->redirect("Account/accountList");
        } else {
            $this->error('添加失败'); 
        }
        /*}*/
    }

}