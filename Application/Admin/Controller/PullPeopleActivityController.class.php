<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/9/9
 * Time: 16:57
 */
namespace Admin\Controller;

use Think\Controller;

class PullPeopleActivityController extends Controller
{
    private $model = '';

    public function __construct()
    {
        parent::__construct();
        if (!session('?admin')) redirect(U('Login/login'));
        $this->model = D('Activity_pull_people');
        $this->assign('oss_path', C('oss_path'));
    }

    //oss上传
    public function upload_file($file = array())
    {
        $_FILES['file'] = $file;
        //$file_name = $_FILES['file']['name'];
        $upload = new \Oss\Ossupload();// 实例化上传类
        $result = $upload->upload(1, $_FILES, 1, 0); //1 pic 2//
        //$returnArray = explode(",", $result[1]);
        return $result['1'];
    }

    /*
     *列表
     */
    public function pullPeopleActivityList()
    {
        $where['delete_status'] = 0;
        $list = $this->model->getAllForList($count,$where);
        $Page = new \Think\Page($count,20);
        $show=$Page->show();
        $this->assign('list',$list);
       // var_dump($list);die;
        $this->assign('page',$show);
        $this->display();
    }

    /*
     *添加或修改详情
     */
    public function pullPeopleActivityInfo()
    {
        $id = getParameter('id', 'int', false);
        if ($_POST) {
            $data['activity_name'] = getParameter('activity_name', 'str');
            $data['activity_status'] = getParameter('status', 'int');
            $data['activity_url'] = $_SERVER['HTTP_HOST'].'/index.php?m=Wchat&c=Pull&a=i';
        }

        if ($_FILES['qq_code']['name']) {
            $data['qq_code'] = $this->upload_file($_FILES['qq_code']);
        }
        if ($_FILES['wx_code']['name']) {
            $data['wx_code'] = $this->upload_file($_FILES['wx_code']);
        }
        $this->model->startTrans();
        if ($id) {
            $where['id'] = $id;
            if ($this->model->SaveResources($data, $where) === false) {
                $this->model->rollback();
            } else {
                $this->model->commit();
                $this->redirect("PullPeopleActivity/pullPeopleActivityList");
            }
        } else {
            if ($this->model->AddResources($data) === false) {
                $this->model->rollback();
            } else {
                $this->model->commit();
                $this->redirect("PullPeopleActivity/pullPeopleActivityList");
            }
        }

        if ($id) {
            $info = $this->model->getResourcesOne($where);
            $this->assign('info', $info);
        }
        $this->display();
    }

    /*
     *上下架操作
     */
    public function upDown(){
        $where['id'] = getParameter('id','int');
        $data['activity_status'] = getParameter('up_down_status','int');
        $this->model->startTrans();
        if($this->model->SaveResources($data,$where) === false){
            $this->model->rollback();
            $this->showjson('404','操作失败，请稍后重试');
        }else{
            $this->model->commit();
            $this->showjson('200');
        }
    }
    /*
     *删除操作
     */
    public function delete(){
        $where['id'] = getParameter('id','int');
        $data['delete_status'] = getParameter('delete_status','int');
        $this->model->startTrans();
        if($this->model->SaveResources($data,$where) === false){
            $this->model->rollback();
            $this->showjson('404','操作失败，请稍后重试');
        }else{
            $this->model->commit();
            $this->showjson('200');
        }
    }
}