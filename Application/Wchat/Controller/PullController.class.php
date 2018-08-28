<?php
namespace Wchat\Controller;
use Think\Controller;

class PullController extends Controller
{

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = D('Activity_pull_people');
        $this->assign('oss_path', C('oss_path'));
    }

    /*
     *二维码图片展示页
     */
    public function i(){
        $where['id'] = getParameter('id','int');
        $info = $this->model->getResourcesOne($where);
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MicroMessenger')) {
            $this->assign('info', $info['wx_code']);
        }elseif (strpos($user_agent, 'QQ')){
            $this->assign('info', $info['qq_code']);
        }else{
			$this->assign('prompt','请使用QQ或微信扫描二维码');
        
        }
		if($info['activity_status'] == 1 || $info['delete_status'] == 1){ //活动已下架或已删除
			$this->assign('prompt2','活动已下架');
		}
        $this->display();
    }
}