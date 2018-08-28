<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify; 

class ApiController extends Controller
{   
 
    
    public function __construct() {
        parent::__construct();   
        $this->assign('oss_path',C('oss_path'));
    }
          
    //权限列表
    public function apiList() {

        if (!session('?admin')) redirect(U('Login/login'));
        //赋值公共的头部信息
        $this->assign('module', 'Api接口管理');
        $this->assign('nav', 'Api接口管理');
        $this->assign('subnav', 'Api接口管理列表');


        $keyword = $_GET['keyword'];
        
        if (!empty($keyword)) {
            $where['describe'] = array('like', '%' . $keyword . '%');
        }

        $where['is_delete'] = 1;

        $count =M('api_version_control')->where( $where )->order('id desc')->where( $where )->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($where as $key => $val) {
            //$Page->parameter[$key] = urlencode($val);
            $Page->parameter[$key] = $val;
        }

        $show = $Page->show();


        $auth_list = M('api_version_control')->where( $where )->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach( $auth_list as $k => $v) {
            $auth_list[$k]['urlname'] = 'http://'.C('REMOTE_ADDR').'/ApiInterface/'.$v['version_path'].'/'.$v['controller_name'].'/'.$v['function_name'];
        }

        $this->assign('list',$auth_list);
        $this->assign('page', $show);
        $this->keyword = $keyword;
        $this->display();
    }

    public function deleteApi() {
        if (!session('?admin')) redirect(U('Login/login'));

        if (session('admin.role') == 3) {
            echo 'error';die;
        }

        $id  = $_GET['id'];
        $Model = M('api_version_control');
        $id  = $Model->where("id=$id")->delete();

        if ( $id ) {
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('error');
        }
    }

    public function addApi() {
        $this->assign('module', 'Api接口管理');
        $this->assign('nav', 'Api接口管理');
        $this->assign('subnav', '添加Api接口');

        $this->display();
    }

    public function saveApi() {
        $data   = M('api_version_control')->create();
        $data['version_path'] = 'Version'.preg_replace('/\./','_',$data['version']);
        $data['modify_time'] = time();
        $data['create_time'] = time();
        $data['is_delete'] = 1;

        if (M('api_version_control')->add($data)) {
            $res['code'] = 1;
            $res['msg'] = "添加成功";

        } else {
            $res['code'] = -1;
            $res['msg'] = "添加失败";

        }

        $this->ajaxReturn($res);
    }
}
