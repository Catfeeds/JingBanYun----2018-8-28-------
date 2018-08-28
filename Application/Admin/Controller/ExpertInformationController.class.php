<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify; 

class ExpertInformationController extends Controller
{   
  
    public function __construct() {
        parent::__construct();  
        $this->model=D('Auth_admin');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }
         
    
    
    //专家资讯信息列表
    public function expertInformationMgmt()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '励耘圈管理');
        $this->assign('nav', '专家资讯管理');
        $this->assign('subnav', '资讯信息列表');
        $status=intval($_GET['status']);
        $keyword = $_GET['keyword'];

        if(!empty($status)){  
            $where['social_expert_information.status']=$status;
            $this->assign('status', $status);
        }
        if(!empty($keyword))
        {
            $where['social_expert_information.title']=array('like','%'.$keyword.'%');
            $this->assign('keyword', $keyword);
        }
        
        $Model = M('social_expert_information');


        $count = $Model->join('auth_admin on social_expert_information.publisher_id = auth_admin.id')->where($where)->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        $result = $Model
            ->join('auth_admin on social_expert_information.publisher_id = auth_admin.id')->where($where)
            ->order('create_at desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->field('social_expert_information.*,auth_admin.nickname')
            ->select();

        $this->assign('role', session('admin.role'));
        $this->assign('list', $result);
        $this->assign('page', $show);

        $this->display();
    }
    
    
    //发布资讯
    public function publishExpertInformation()
    {
        if (!session('?admin')) redirect(U('Login/login'));

      
        if ($_POST) {
            $data['title'] = remove_xss($_POST['title']);
            //$data['short_content'] = remove_xss($_POST['short_content']);
            $data['content'] = $_POST['content'];

            $data['update_at'] = time();
            $data['create_at'] = time();

            $data['status'] = 1;

            $data['publisher_id'] = session('admin.id');
            $data['publisher'] = session('admin.name');     

            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 911127998;// 设置附件上传大小
            $upload->exts = array('jpg', 'png');// 设置附件上传类型
            $upload->rootPath = './Resources/expertinformation/'; // 设置附件上传根目录
            // 上传单个文件
            $info = $upload->uploadOne($_FILES['file']);
            if (!$info) { // 上传错误提示错误信息
                $this->error($upload->getError());
            } else { // 上传成功 获取上传文件信息
                //echo $info['savepath'] . $info['savename'];
            }
            $data['short_content'] = $info['savepath'] . $info['savename'];
            $Model = M('social_expert_information');
            $Model->add($data);
            
            $this->redirect("ExpertInformation/expertInformationMgmt");
        } else {
            $this->assign('module', '励耘圈管理');
            $this->assign('nav', '专家资讯管理');
            $this->assign('subnav', '发布资讯');

            $this->display();
        }
    }
    
    
    //专家资讯信息详情
    public function expertInformationDetails($id)
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '励耘圈');
        $this->assign('nav', '专家资讯');
        $this->assign('subnav', '资讯详情');

        $Model = M('social_expert_information');
        $check['social_expert_information.id'] = $id;
        $result = $Model->join('auth_admin on social_expert_information.publisher_id = auth_admin.id')->where($check)
        ->field('social_expert_information.*,auth_admin.nickname')
        ->find();

        $this->assign('data', $result);

        $this->display();
    }
    
    
    //修改专家资讯
    public function modifyExpertInformation()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        if ($_POST) {
            $check['id'] = $_POST['id'];
            $data['title'] = remove_xss($_POST['title']);
            $data['content'] = $_POST['content'];
            $data['update_at'] = time();
            $data['status'] = 1;

            if ($_FILES["file"]["error"] == 0) {
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 911127998;// 设置附件上传大小
                $upload->exts = array('jpg', 'png');// 设置附件上传类型
                $upload->rootPath = './Resources/expertinformation/'; // 设置附件上传根目录
                // 上传单个文件
                $info = $upload->uploadOne($_FILES['file']);
                if (!$info) { // 上传错误提示错误信息
                    $this->error($upload->getError());
                }
                $data['short_content'] = $info['savepath'] . $info['savename'];
            }

            $Model = M('social_expert_information');
            $Model->where($check)->save($data);
            $this->redirect("ExpertInformation/expertInformationMgmt");

        } else {
            $this->assign('module', '励耘圈管理');
            $this->assign('nav', '专家资讯管理');
            $this->assign('subnav', '修改资讯');

            $id = $_GET['id'];
            $this->assign('id', $id);

            $Model = M('social_expert_information');
            $result = $Model->where("id=$id")->find();

            $this->assign('data', $result);

            $this->display();
        }
    }
    
    
    //通过审核专家资讯
    public function approveExpertInformation()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $Model = M('social_expert_information');
        $check['id']=$id;
        $result=$Model->where($check)->field('id,title,content,push_status')->find();
        if(empty($result)){
            echo $this->ajaxReturn('failed');die;
        }
        if($result['push_status']==1){
            //手机三个角色推送
            /*
            $message_id=$id;
            $message_model=D('Message');
            $message_add_data['role_id']='2,3,4';
            $message_add_data['title']='专家资讯：'.substr($result['title'],0,50);       
            $message_add_data['truncated_title']=substr($result['content'],0,50);
            $message_add_data['message_content']=$result['content'];
            $message_add_data['receive_type']=1; 
            $message_add_data['message_type']=2;
            
            $message_id=$message_id=$message_model->addMessageInfo($message_add_data);
            $people_number=$message_model->addMessageReceive($message_id);
            $message_model->updateMessageReceivenum($message_id,$people_number); 
            $parameters=array( 
                'url'=>array(
                    'type'=> 1,
                    'data'=>array($id)
                )
            );
            $config_arr=C('PUSH_MESSAGE');
            $format_url=$config_arr['EXPERTINFO_PUBLISHED']['FORMAT_URL'];
            if($parameters['url']['type'] == 0) 
            { 
                $messageUrl = 'http://'. $_SERVER["SERVER_NAME"] .sprintf($format_url, $message_id); 
            }
            else
            { 
                $messageUrl = 'http://'. $_SERVER["SERVER_NAME"] .vsprintf($format_url, $parameters['url']['data']); 
            } 
            A('Home/Message')->sendMessage($message_id,$messageUrl);

            $Model->where("id=$id")->save(array('push_status'=>2));
            */
        }
        
        $data['status'] = 2;

        $Model->where("id=$id")->save($data);

        $this->ajaxReturn('success');
    }
     
    public function publishExpertInformationNormal()
    {
        if (!session('?admin')) redirect(U('Login/login'));
        $id = $_GET['id'];
        $Model = M('social_expert_information');
        $data['status'] = 4;
        $data['up_time'] = time();
        $Model->where("id=$id")->save($data);
        $this->ajaxReturn('success');

    }
    
    //拒绝审核专家资讯
    public function denyExpertInformation()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $Model = M('social_expert_information');

        $data['status'] = 5;

        $Model->where("id=$id")->save($data);

        $this->ajaxReturn('success');
    }
    
    //删除专家资讯
    public function deleteExpertInformation()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $Model = M('social_expert_information');
 

        $Model->where("id=$id")->delete();

        $this->ajaxReturn('success');
    }
     
    //下架审核专家资讯
    public function downExpertInformation(){
        if (!session('?admin')) redirect(U('Login/login'));
        $id = $_GET['id'];
        $Model = M('social_expert_information');

        $data['status'] = 3;

        $Model->where("id=$id")->save($data);

        $this->ajaxReturn('success');
        
    }

    //推送专家资讯
    public function pushExpertInformation(){
        if (!session('?admin')) redirect(U('Login/login'));
        $id = getParameter('id','int');
        $info = D('Social_expert_information')->getInformationDetails($id);
        if(D('Social_expert_information')->setPushStatus($id,EXPERTINFORMATION_HASPUSH)) {
            $parameters = array('msg' => array(
                $info['title']
            ),
                'url' => array('type' => 1, 'data' => array($id))
            );
            //手机三个角色推送
            set_time_limit(0);
            $message_model=D('Message');
            $message_add_data['role_id']='2,3,4';
            $message_add_data['title']='专家资讯：'.substr($info['title'],0,50);
            $message_add_data['truncated_title']=substr($message_add_data['title'],0,50);
            $message_add_data['message_content']=$info['content'];
            $message_add_data['receive_type']=1;
            $message_add_data['message_type']=2;

            $message_id=$message_model->addMessageInfo($message_add_data);
            $people_number=$message_model->addMessageReceive($message_id);

            $message_model->updateMessageReceivenum($message_id,$people_number);
            $parameters=array(
                'url'=>array(
                    'type'=> 1,
                    'data'=>array($id)
                )
            );
            $config_arr=C('PUSH_MESSAGE');
            $format_url=$config_arr['EXPERTINFO_PUBLISHED']['FORMAT_URL'];
            if($parameters['url']['type'] == 0)
            {
                $messageUrl = 'http://'. $_SERVER["SERVER_NAME"] .sprintf($format_url, $message_id);
            }
            else
            {
                $messageUrl = 'http://'. $_SERVER["SERVER_NAME"] .vsprintf($format_url, $parameters['url']['data']);
            }
            A('Home/Message')->sendMessage($message_id,$messageUrl);
            $this->ajaxReturn(array('status' => 200, 'message' => '推送成功'));

        }
        else
            $this->ajaxReturn(array('status' => 500, 'message' => '设置活动推送状态失败'));
    }
    
}
