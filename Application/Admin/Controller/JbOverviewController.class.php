<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify; 

class JbOverviewController extends Controller
{   
 
    public function __construct() {
        parent::__construct();   
        $this->assign('oss_path',C('oss_path'));
    }
         
    
    
    //京版概览
    public function jbOverviewMgmt(){
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '励耘圈管理');
        $this->assign('nav', '京版概览');
        $this->assign('subnav', '编辑');

        //
        $Model = M('biz_bj_overview');
        $content = $Model->select();  
        $this->assign('data', $content[0]);

        $this->display();
    }

    
    //京版概览 - 修改
    public function modifyjbOverview()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        if ($_POST) {
            $check['id'] = 1;
            $data['status'] =1;
            $data['content'] = $_POST['content'];
            $Model = M('biz_bj_overview');
            $Model->where($check)->save($data);
            
            $this->redirect("JbOverview/jbOverviewMgmt");
        }
    }
    
    
    //京版概览审核通过或拒绝
    public function reviewedjbOverview(){
        if (!session('?admin')) redirect(U('Login/login'));
        $status=I('status');
        $id=I('id');
        if($status==1){
            //通过
            $data['status']=2;
        }else{
            //拒绝
            $data['status']=3;
        }
        $Model = M('biz_bj_overview');
        $Model->where("id=$id")->save($data);
        $this->ajaxReturn('success');
    }
    
    //京版概览下架
    public function downjbOverview(){
        if (!session('?admin')) redirect(U('Login/login'));
        $id = I('id');
        $Model = M('biz_bj_overview');
        $data['status']=1;
        $Model->where("id=$id")->save($data); 

        $this->ajaxReturn('success');
    }
  
}
