<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify; 

class SensitiveWordsController extends Controller
{    
    
    public function __construct() {
        parent::__construct();   
        $this->assign('oss_path',C('oss_path'));
    }
          
    
    //敏感词列表页面
    function sensitive_words(){ 
        if (!session('?admin')) redirect(U('Login/login'));
        
        $this->assign('module', '敏感词');
        $this->assign('nav', '敏感词');
        $this->assign('subnav', '敏感词列表');
                
        $filter['keyword'] = $_REQUEST['keyword'];
        if (!empty($filter['keyword'])) $check['sensitive_words'] = array('like', '%' . $filter['keyword'] . '%');
                
        $model=M('sensitive_words'); 
        $count=$model->where($check)->field('id,sensitive_words')->order('id desc')->count('id');        
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));  
               
        $now_page=isset($Page->parameter['p'])?$Page->parameter['p']:1;    
        $count_page=ceil($count/C('PAGE_SIZE_FRONT')); 
        $mo_rows=$count%C('PAGE_SIZE_FRONT');   
        $page_i=($count_page-$now_page)==0?$page_i=0:($count_page-$now_page);   
        $total_rows=$page_i*C('PAGE_SIZE_FRONT')+((C('PAGE_SIZE_FRONT')-(C('PAGE_SIZE_FRONT')-$mo_rows)));    
        
        $show = $Page->show();
        
        $result=$model->where($check)->limit($Page->firstRow . ',' . $Page->listRows)->field('id,sensitive_words,create_at')->order('id desc')->select(); 
                
        
        $this->assign('keyword', $filter['keyword']);
        $this->assign('list', $result);
        $this->assign('page', $show); 
        $this->assign('data_page', $total_rows);
        $this->display();
    }
    
    //添加敏感词
    function add_sensitive_words(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        $model=M('sensitive_words');
        $data['sensitive_words']=$_POST['sensitive_words'];
        $data['create_at']=time();
                
        if(empty($data['sensitive_words'])){
            $this->ajaxReturn('failed');
        }
        
        $result=$model->field('id')->where("sensitive_words='".$data['sensitive_words']."'")->find();      
        if(!empty($result)){
            $this->ajaxReturn('exists');
        }
        
        
        if($model->add($data)){     
            $this->ajaxReturn('success');
        }else{
            $this->ajaxReturn('failed');
        }
    }
    
    //删除敏感词
    function delete_sensitive_words(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        $model=M('sensitive_words');
        $id=intval($_GET['id']); 
        if($model->where("id=$id")->delete()){
            $this->ajaxReturn('success');
        }else{
            $this->ajaxReturn('failed');
        }
        
    }
}
