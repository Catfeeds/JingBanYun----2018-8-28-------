<?php
namespace ApiInterface\Controller\Version1_1;

use Think\Controller;

class MessageController extends PublicController
{  
    
    public $firstRow=0;
    public $listRow=0; 
    public $page_size = 20;
    public $model;
    function getPageSize(){
        return $this->page_size;
    }
    public function __construct()
    {
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
        $this->model = D('Message');
    }
    
    
    //分页处理
    public function pageOperation(){
        $page=intval(I('page'));
        if($page){
            $page=$page<1?1:$page;
        }else{
            $page=1;
        }
        $data['start']=($page-1)*$this->page_size;
        $data['end']=  $this->page_size;
        return $data;
    }
    
    
    /*
     * 个人中心,未读消息总数
     * @param   string  id教师id 
     * @param   string  role角色   
     * @return  json    
     */
    public function unreadMessageCount(){
        $user_id=getParameter('id','int');
        $role=getParameter('role','int'); 
        if(!$user_id || !$role){
            $this->ajaxReturn(array('status' => 406, 'message' => '参数错误'));
        }  
        $message_model=D('Message');
        $data=$message_model->getUnreadMessagesCount($user_id,$role);
        $this->ajaxReturn(array('status' => 200,'message' => 'success', 'result' =>$data));
    }
     
    
    /*
     * 个人中心,列表数据和搜索
     * @param   string  id教师id 
     * @param   string  role角色 
     * @param   string  keyword关键字搜索
     * @param   string  页码数
     * @return  json
     */
    public function messageList(){
        $user_id=getParameter('id','int');
        $role=getParameter('role','int');
        $keyword=I('keyword');  
        if(!empty($keyword)){   
            $keyword=getParameter('keyword','str');
        }   
        if(!$user_id || !$role){
            $this->ajaxReturn(array('status' => 406, 'result' => '参数错误'));
        }
        $message_model=D('Message');   
        $condition_array['message_content']=$keyword;
        $condition_array['role_id']=array($role);
        $condition_array['user_id']=$user_id;
        
        $count=0;
        $data['result']=array();    
        
        $pageData=$this->pageOperation();   
        $pageStart=$pageData['start'];
        $pageEnd=$pageData['end'];      
        $message_model->getMessageList($condition_array,$count,$data['result'],'1',$pageStart,$pageEnd);
        //判断时间
        $this->ajaxReturn(array('status' => 200,'message' => 'success', 'result' =>$data)); 
    }
    
    /*
     * @param   string  id消息id 
     * 个人中心,消息详情
     */
    public function messageDetail(){
        $message_id=getParameter('id','int');
        $receive_id=getParameter('receive_id','int',false);
        $role = getParameter('role','int',false);
        $userId = getParameter('userId','int',false);
        $message_model=D('Message');

        if(empty($receive_id))
        {
            $message_model->setMessageReadState($message_id,$userId,$role,2);
        }
        else
        {
            $message_model->updateMessageRedyStatus($receive_id);
        }
        $result=$message_model->getMessageDetail($message_id); 
        $this->assign('data', $result);
        $this->display();
    }
    
     
}