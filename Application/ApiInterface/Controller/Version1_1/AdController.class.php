<?php
namespace ApiInterface\Controller\Version1_1;
use Think\Controller;
use Common\Common\SMS;

class AdController extends PublicController
{ 

    public $model='';
    public $page_size=20;
    public $firstRow=0;
    public $listRow=0;
            
    public function __construct() {
        parent::__construct(); 
        header("Content-type: text/html; charset=utf-8");
        $this->Ad = D('Ad');
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

    /**
     * @描述：班级
     * @参数：id[int] Y 用户id
     * @参数：role_id[int] Y 用户角色
     * @返回值：code[int]{
     * "-1: "user_id参数不合法",
     * "200": "成功"
     * "is_vip": "是否是vip"
     * "auth" : "节点权限列表"
     * } Y 返回代号
     */
    //model_type  1.励耘圈2.教学3.班级行
    public function workList(){
        $types['type'] = 2;
        $types['model_type'] = 1;
        $list = $this->Ad->getLunboList( $types );
        $timeGap = $this->Ad->getLunboTimeGap( $types['model_type'] );
        foreach ($list as $k=>&$v) {
            $v['file_path'] = C('oss_path').$v['file_path'];
            if(false === strpos($v['url'],'?id=')) {
                $v['url'] = $v['url'].'?id='.$v['id'];
            } else {
                $v['url'] = $v['url'].'&aid='.$v['id'];
            }
            $url_count = substr_count($v['url'],'?');

            if ($url_count>1) {
                $url = explode("?",$v['url']);
                $nullurl = "";
                foreach ($url as $kk=>$vv) {
                    if ($kk==0) {
                        $nullurl = $vv.'?';
                    } else {
                        $nullurl .= $vv.'&';
                    }
                }
                $nullurl = rtrim($nullurl,'&');
                $v['url'] = $nullurl;

            }
        }

        $this->ajaxReturn(array('status' => 200, 'data' => $list,'timeInterval'=>$timeGap),'JSON',JSON_UNESCAPED_UNICODE);
    }

    public function teachingList(){
        $types['type'] = 2;
        $types['model_type'] = 2;
        $list = $this->Ad->getLunboList( $types );
        $timeGap = $this->Ad->getLunboTimeGap( $types['model_type'] );
        foreach ($list as $k=>&$v) {
            $v['file_path'] = C('oss_path').$v['file_path'];
            if(false === strpos($v['url'],'?id=')) {
                $v['url'] = $v['url'].'?id='.$v['id'];
            } else {
                $v['url'] = $v['url'].'&aid='.$v['id'];
            }


            $url_count = substr_count($v['url'],'?');

            if ($url_count>1) {
                $url = explode("?",$v['url']);
                $nullurl = "";
                foreach ($url as $kk=>$vv) {
                    if ($kk==0) {
                        $nullurl = $vv.'?';
                    } else {
                        $nullurl .= $vv.'&';
                    }
                }
                $nullurl = rtrim($nullurl,'&');
                $v['url'] = $nullurl;

            }
        }

        $this->ajaxReturn(array('status' => 200, 'data' => $list,'timeInterval'=>$timeGap),'JSON',JSON_UNESCAPED_UNICODE);
    }

    public function classinfoList(){
        $types['type'] = 2;
        $types['model_type'] = 3;
        $list = $this->Ad->getLunboList( $types );
        $timeGap = $this->Ad->getLunboTimeGap( $types['model_type'] );
        foreach ($list as $k=>&$v) {
            $v['file_path'] = C('oss_path').$v['file_path'];
            if(false === strpos($v['url'],'?id=')) {
                $v['url'] = $v['url'].'?id='.$v['id'];
            } else {
                $v['url'] = $v['url'].'&aid='.$v['id'];
            }

            $url_count = substr_count($v['url'],'?');

            if ($url_count>1) {
                $url = explode("?",$v['url']);
                $nullurl = "";
                foreach ($url as $kk=>$vv) {
                    if ($kk==0) {
                        $nullurl = $vv.'?';
                    } else {
                        $nullurl .= $vv.'&';
                    }
                }
                $nullurl = rtrim($nullurl,'&');
                $v['url'] = $nullurl;

            }
        }

        $this->ajaxReturn(array('status' => 200, 'data' => $list,'timeInterval'=>$timeGap),'JSON',JSON_UNESCAPED_UNICODE);
    }


    public function classList() {
        $role = $_GET['role'];
        $user_id = $_GET['userId'];
        $id = $_GET['id'];
        if (empty($id)) {
            $this->ajaxReturn(array('status' => 400, 'data' => '角色id错误'));
        }
        $res = $this->Ad->getIdLunbo( $id );

        if (empty($role)) {
            $this->ajaxReturn(array('status' => 400, 'data' => '角色id错误'));
        }
        if($res['url_jump_type'] ==2) {
            $res['child_page_url'] = $_SERVER['HTTP_HOST'].'?'.$res['child_page_url'];
        }

        $this->assign('weburl',$res['url']);
        $this->assign('url',$res['child_page_url']);
        $this->assign('jump_type',$res['url_jump_type']);

        D('Monitor')->setIncFigure($id);
        $this->display();
    }

    public function shiYan() {
        $id = $_GET['id'];
        D('Monitor')->setIncFigure($id);
        $this->display();
    }

    public function boxList() {
        $role = $_GET['role'];
        $user_id = $_GET['userId'];
        $id = $_GET['id'];
        $res = $this->Ad->getIdLunbo( $id );

        if (empty($role)) {
            $this->ajaxReturn(array('status' => 400, 'data' => '角色id错误'));
        }

        if($res['url_jump_type'] ==2) {
            $res['child_page_url'] = $_SERVER['HTTP_HOST'].'?'.$res['child_page_url'];
        }

        $this->assign('weburl',$res['url']);
        $this->assign('url',$res['child_page_url']);
        $this->assign('jump_type',$res['url_jump_type']);
        D('Monitor')->setIncFigure($id);
        $this->display();
    }

    public function ceshi() {
        $this->display();
    }
}