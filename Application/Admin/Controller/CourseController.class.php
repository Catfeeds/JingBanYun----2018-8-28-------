<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify; 

class CourseController extends Controller
{   
    
    public function __construct() {
        parent::__construct();   
        $this->assign('oss_path',C('oss_path'));
    }
          
    
    //学科管理
    function courseMgmt()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '字典数据管理');
        $this->assign('nav', '学科管理');
        $this->assign('subnav', '学科列表');

        $Model = M('dict_course');
        $result = $Model->select();

        $this->assign('list', $result);

        $this->display();
    }
    
    //学科创建视图
    function courseCreate(){
        if (!session('?admin')) redirect(U('Login/login'));
        $this->assign('module', '字典数据管理');
        $this->assign('nav', '学科管理');
        $this->assign('subnav', '创建学科');
        $this->display(); 
    }
    
    //学科创建操作  
    function courseCreateOp(){
        if (!session('?admin')) redirect(U('Login/login'));
        if(!$_POST){
            $this->ajaxReturn(array('code' => -1,'msg' => '数据不能为空'));
        }
        $data['course_name']=I('course');
        $data['code']=I('code');
        $Model = M('dict_course');
        $Model->data($data)->add();
        $this->ajaxReturn(array('code' => 0,'msg' => '添加成功'));
    }
  
    
    //学科删除
    function courseDelete(){
        if (!session('?admin')) redirect(U('Login/login'));
        $id = $_GET['id']; 
        $Model = M('dict_course');
        if($Model->where('id='.$id)->delete()){
            echo 1;
        } 
    }
    
    //学科修改视图
    function courseUpdView(){    
        if (!session('?admin')) redirect(U('Login/login'));
        $id=I('id');
        $Model = M('dict_course');
        $result=$Model->where("id=$id")->field('id,course_name,code')->find(); 
        if(empty($result)){
            $this->error();
        } 
        
        $this->assign('data', $result);
        $this->assign('module', '字典数据管理');
        $this->assign('nav', '学科管理');
        $this->assign('subnav', '修改学科');
        $this->display();
    }
    
    
    //学科修改操作
    function courseUpdOp(){
        if (!session('?admin')) redirect(U('Login/login'));
        $data['course_name']=I('course');
        $data['code']=I('code');
        $id=I('id');
        $Model = M('dict_course');
        $Model->where("id=$id")->save($data);
        redirect(U('Course/courseMgmt'));
    }
}
