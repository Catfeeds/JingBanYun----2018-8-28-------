<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/6/16
 * Time: 14:39
 */
namespace Admin\Controller;
use Think\Controller;

class AppVersionControlController extends Controller
{
    private $model;
    private $pageSize = 20;

    public function __construct()
    {
        parent::__construct();
        if (!session('?admin')) redirect(U('Login/login'));
        $this->model = D('App_version_control');
        $this->assign('oss_path', C('oss_path'));
    }

    /*
     *APP版本控制首页
     */
    public function appVersionControlIndex(){
        $result = $this->model->all($count);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();
        $this->assign('page',$show);
        $this->assign('data',$result);
        $this->display('PropagandaManagement/appVersionMgmt');
    }

    /*
     *APP发布新版本操作
     */
    public function publishApp(){
        if($_POST){
            $request['id'] = getParameter('id','int',false);
            $data['system_type'] = strtolower(getParameter('system_type','str'));
            $data['version_number'] = getParameter('version_number','int');
            $data['version'] = getParameter('version','str');
            $data['app_name'] = getParameter('app_name','str');
            $data['putaway_time'] = strtotime(getParameter('putaway_time','str'));
            $data['download_path'] = getParameter('download_path','str');
            $data['update_forced'] = getParameter('update_forced','int');
            $data['update_content'] = getParameter('update_content','str');
            $this->model->startTrans();
            if(!empty($request['id'])){
                $data['putaway_status'] = '2';
            $where['id'] = $request['id'];
                $result = $this->model->save($where,$data);
            }else{
                $data['putaway_status'] = '2';
                $data['creat_time'] = time();
                $result = $this->model->insert($data);
            }

            if($result == false){
                $this->model->rollback();
            }else{
                $this->model->commit();
            }
            $this->redirect("AppVersionControl/appVersionControlIndex");
        }else{
            $this->display('PropagandaManagement/publishApp');
        }

    }

    /*
     *APP发布新版本修改操作
     */
    public function publishAppSave(){
        $data['id'] = getParameter('id','int');
        $result = $this->model->getOne($data);
        $this->assign('data',$result);
        $this->display('PropagandaManagement/publishApp');
    }

    /*
     *上下架操作
     */
    public function ajaxPutawayStatus(){
        $where['id'] = getParameter('id','int');
        $data['putaway_status'] = getParameter('putaway','int');
        $type = getParameter('type','str');
        $whereList['putaway_status'] = 1;
        if($type == 'ios'){
            $whereList['system_type'] = 'ios';
        }else{
            $whereList['system_type'] = 'android';
        }
        $this->model->getResourceByWhere($whereList,$count);
        if($count == 1){
            $this->showMessage('404','不能进行下架操作');
        }
        $this->model->startTrans();
        $result = $this->model->save($where,$data);
        if($result == false){
            $this->model->rollback();
            $this->showMessage('500','操作失败请稍后重试');
        }else{
            $this->model->commit();
            $this->showMessage('200');
        }
    }
}