<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/7/20
 * Time: 11:09
 */
namespace Admin\Controller;

use Think\Controller;

class WeiXinPushController extends Controller
{

    public $page_size = 20;
    private $model;

    public function __construct()
    {
        parent::__construct();
        if (!session('?admin')) redirect(U('Login/login'));
        $this->model = D('Weixin_push');
        $this->assign('oss_path', C('oss_path'));
    }

    /*
     *列表页
     */
    public function weiXinPushList()
    {
        $creat_time = I('creat_time');
        $stime = strtotime($creat_time);
        $p = I('p');
        $etime = strtotime($creat_time)+86399;
        $title = I('title');
        if (!empty($creat_time)) {
            $where['creat_time'] = array('BETWEEN',"$stime,$etime");
        }
        if (!empty($title)) {
            $where['title'] = array('like','%'.$title.'%');
        }
        $resources = $this->model->getAll($where,$p,$this->page_size);

        $Page = new \Think\Page($resources['count'],$this->page_size);
        if (!empty($creat_time)) {
            $Page->parameter['creat_time']   =   $creat_time;
        }
        if (!empty($title)) {
            $Page->parameter['title']   =   $title;
        }
        $show = $Page->show();
        $this->assign('list', $resources['resources']);
        $this->assign('type', $type);
        $this->assign('creat_time', $creat_time);
        $this->assign('title', $title);
        $this->assign('page', $show);
        $this->display();
    }

    /*
     *添加详情页
     */
    public function weiXinPushAdd()
    {
        if($_POST){
            //$a =  json_encode(I('title'),JSON_UNESCAPED_UNICODE);
            //$b = json_decode($a);var_dump($b);die;
            $data['title'] =I('title');
            $data['weixin_push_status'] = I('status');
            $data['img_path'] = I('img_path');
            $data['url'] = I('url');
            $data['show_time'] = strtotime(I('show_time'));
            $data['creat_time'] = time();
            $this->model->startTrans();
                if($this->model->addResources($data) === false){
                    $this->model->rollback();
                }else{
                    $this->model->commit();
                    $this->redirect('WeiXinPush/weiXinPushList');
                }
        }
        $this->display('weiXinPushDetail');
    }



    /*
     *修改详情页
     */
    public function weiXinPushSave()
    {
        $id = I('id');
        $where['id'] = $id;

        if($_POST){
            $data['title'] =I('title');
            $data['weixin_push_status'] = I('status');
            $data['img_path'] = I('img_path');
            $data['url'] = I('url');
            $data['show_time'] = strtotime(I('show_time'));
            $this->model->startTrans();
            if($this->model->saveResources($where,$data) === false){
                $this->model->rollback();
            }else{
                $this->model->commit();
                $this->redirect('WeiXinPush/weiXinPushList');
            }
        }
        $resources = $this->model->getOne($where);
        //var_dump($resources);die;
        $this->assign('id',$id);
        $this->assign('status',$resources['weixin_push_status']);
        $this->assign('title',$resources['title']);
        $this->assign('url',$resources['url']);
        $this->assign('img_path',$resources['img_path']);
        $this->assign('show_time',date('Y-m-d H:i:s',$resources['show_time']));
        $this->display();
    }

    /*
     *预览
     */
    public function preview(){
        $id = I('id');
        $type = I('type');
        $status = I('status');
        if($status === 'one'){
            if($type == '1') {
                $where['weixin_column.status'] = '1';
            }else{
                $where['weixin_column.status'] = '1';
                $where['weixin_column_title_contact.status_contact'] = '1';
            }
        }
        $where['weixin_column.id'] = $id;
        if($type == '1'){
            if($status === 'one') {
                $where['weixin_push_status'] = '1';//weixin_push表中的weixin_push_status字段
            }
            $resources = $this->model->getAllById($where,$type);//标题类型
            $this->assign('list',$resources);
            $this->display('primaryChinese');
        }else{
            //列表类型
            $blockResources = $this->model->getListAll($where);
            //var_dump($blockResources);die;
            foreach ($blockResources as $k=>$item){
                $wheres['weixin_column_title_contact_id'] = $item['ctcid'];
                $wheres['weixin_column_id'] = $id;
                if($status === 'one') {
                    $wheres['weixin_push_status'] = '1';
                }
                $blockResources[$k]['content'] = $this->model->getAllById($wheres,$type);
            }
            //var_dump($blockResources);die;
            $this->assign('list',$blockResources);
            if($id == '1') {
                $this->display('biologyChemistryHistory');
            }elseif ($id == '3'){
                $this->display('primaryMath');
            }elseif($id == '4'){
                $this->display('middleMathChinese');
            }elseif ($id == '5'){
                $this->display('english');
            }

        }

    }

    /*
     *微信推送栏目列表
     */
    public function weiXinColumnList(){
        $type = I('type');
        $creat_time = I('creat_time');
        $p = I('p');
        $stime = strtotime($creat_time);
        $etime = strtotime($creat_time)+86399;
        $name = I('title');
        if (!empty($type)) {
            $where['type'] = $type;
        }
        if (!empty($creat_time)) {
            $where['creat_time'] = array('BETWEEN',"$stime,$etime");
        }
        if (!empty($name)) {
            $where['name'] = array('like','%'.$name.'%');
        }
        $resources = $this->model->getColumnAll($where,$p,$this->page_size);
        $this->assign('list', $resources['resources']);
        $Page = new \Think\Page($resources['count'], $this->page_size);
        if (!empty($type)) {
            $Page->parameter['type'] = $type;
        }
        if (!empty($creat_time)) {
            $Page->parameter['creat_time'] = $creat_time;
        }
        if (!empty($name)) {
            $Page->parameter['title'] = $name;
        }
        $show = $Page->show();
        $this->assign('type', $type);
        $this->assign('creat_time', $creat_time);
        $this->assign('title', $name);
        $this->assign('page', $show);
        $this->display();
    }

    /*
     * 微信推送栏目添加
     */
    public function weiXinColumnAdd(){
        if($_POST){
            $data['type'] = I('type');
            $data['name'] =I('title');
            $data['creat_time'] = time();
            $data['status'] = I('status');
            //var_dump($data);die;
            M('weixin_column')->startTrans();
            if($this->model->addColumnResources($data) === false){
                M('weixin_column')->rollback();
            }else{
                M('weixin_column')->commit();
                $this->redirect('WeiXinPush/weiXinColumnList');
            }
        }
        $this->display();
    }

    /*
    * 微信推送栏目修改
    */
    public function weiXinColumnSave(){
        $id = I('id');
        $where['id'] = $id;

        if($_POST){
            $data['name'] =I('title');
            $data['status'] = I('status');
            M('weixin_column')->startTrans();
            if($this->model->saveColumnResources($where,$data) === false){
                M('weixin_column')->rollback();
            }else{
                M('weixin_column')->commit();
                $this->redirect('WeiXinPush/weiXinColumnList');
            }
        }
        $resources = $this->model->getColumnOne($where);
        //var_dump($resources);die;
        $this->assign('id',$id);
        $this->assign('title',$resources['name']);
        $this->assign('type',$resources['type']);
        $this->assign('status',$resources['status']);
        $this->display();
    }




    /*
     *推送的列表类型操作列表
     */
    public function blockList(){
        //$type = I('type');
        $creat_time = I('creat_time');
        $p = I('p');
        $stime = strtotime($creat_time);
        $etime = strtotime($creat_time)+86399;
        $status = I('status');
        $column_id = I('column_id');
        //var_dump($column_id);die;
        if (!empty($creat_time)) {
            $where['creat_time'] = array('BETWEEN',"$stime,$etime");
        }
        if ($status !='') {
            $where['status_contact'] = $status;
        }
        $where['column_id'] = $column_id;
        $resources = $this->model->get_column_title_contact_all($where,$p,$this->page_size);
        $this->assign('list', $resources['resources']);
        $Page = new \Think\Page($resources['count'], $this->page_size);
        if (!empty($creat_time)) {
            $Page->parameter['creat_time']   =  $creat_time;
        }
        if ($status !='') {
            $Page->parameter['status']   =   $status;
        }
        $show = $Page->show();
        $this->assign('status', $status);
        $this->assign('creat_time', $creat_time);
        $this->assign('column_id',$column_id);
        $this->assign('page', $show);
        $this->display();
    }

    /*
     *推送的列表类型添加操作
     */
    public function blockAdd(){
        if($_POST){
           $data['creat_time'] = time();
           $data['column_id'] = I('column_id');
            $data['show_time'] = strtotime(I('show_time'));
            $data['status_contact'] = I('status');
            //var_dump($data);die;
            M('weixin_column_title_contact')->startTrans();
            if($this->model->get_column_title_contact_add($data) === false){
                M('weixin_column_title_contact')->rollback();
            }else{
                M('weixin_column_title_contact')->commit();
                $this->redirect('WeiXinPush/blockList',array('column_id'=>$data['column_id']));
            }
        }
        $this->assign('column_id',I('column_id'));
        $this->display();
    }

    /*
     *推送的列表类型修改操作
     */
    public function blockSave(){
        $id = I('id');
        $where['id'] = $id;

        if($_POST){
            $data['show_time'] = strtotime(I('show_time'));
            $data['status_contact'] = I('status');
            M('weixin_column_title_contact')->startTrans();
            if($this->model->get_column_title_contact_save($where,$data) === false){
                M('weixin_column_title_contact')->rollback();
            }else{
                M('weixin_column_title_contact')->commit();
                $this->redirect('WeiXinPush/blockList',array('column_id'=>I('column_id')));
            }
        }
        $resources = $this->model->get_column_title_contact_one($where);
        //var_dump($resources);die;
        $this->assign('column_id',I('column_id'));
        $this->assign('id',$id);
        $this->assign('show_time',$resources['show_time']);
        $this->assign('status',$resources['status_contact']);
        $this->display();
    }



    /*
     *推送的标题类型操作列表
     */
    public function titleList(){
        $creat_time = I('creat_time');
        $p = I('p');
        $stime = strtotime($creat_time);
        $etime = strtotime($creat_time)+86399;
        $ctcid = I('ctcid');
        $cloumn_id = I('column_id');
        if(!empty($ctcid)){
            $where['weixin_column_title_contact_id'] = $ctcid;
        }
        $where['weixin_column_id'] = $cloumn_id;
        if (!empty($creat_time)) {
            $where['creat_time'] = array('BETWEEN',"$stime,$etime");
        }
        if ($status !='') {
            $where['status_contact'] = $status;
        }
        $resources = $this->model->get_column_title_push_contact_all($where,$p,$this->page_size);
    //var_dump($resources['resources'][0]['weixin_push_id']);die;
        foreach ($resources['resources'] as $k=>$item){
            $wheres['weixin_push.id'] = $item['weixin_push_id'];
            $resources['resources'][$k]['title'] = $this->model->getOne($wheres)['title'];
        }
    //var_dump($resources['resources']);die;
        $this->assign('list', $resources['resources']);
        $Page = new \Think\Page($resources['count'], $this->page_size);
        if (!empty($creat_time)) {
            $Page->parameter['creat_time']   =   $creat_time;
        }
        $show = $Page->show();
        $this->assign('creat_time', $creat_time);
        $this->assign('column_id',$cloumn_id);
        $this->assign('ctcid',$ctcid);
        $this->assign('page', $show);
        $this->display();
    }

    /*
     *推送的标题类型添加操作
     */
    /*public function titleAdd(){
        if($_POST){
            $data['creat_time'] = time();
            $data['weixin_column_id'] = I('column_id');
            $data['weixin_column_title_contact_id'] = I('ctcid');
            $data['weixin_push_id'] = I('weixin_push_id');
            $data['sort'] = I('sort');
            //var_dump($data);die;
            M('weixin_column_title_contact')->startTrans();
            if($this->model->get_column_title_contact_add($data) === false){
                M('weixin_column_title_contact')->rollback();
            }else{
                M('weixin_column_title_contact')->commit();
                $this->redirect('WeiXinPush/blockList',array('column_id'=>$data['column_id']));
            }
        }
        $this->assign('column_id',I('column_id'));
        $this->assign('ctcid',I('ctcid'));
        $this->display();
    }*/

    /*
     *推送的标题类型修改操作
     */
   /* public function titleSave(){
        $id = I('id');
        $where['id'] = $id;

        if($_POST){
            $data['name'] =I('title');
            $data['status'] = I('status');
            M('weixin_column')->startTrans();
            if($this->model->saveColumnResources($where,$data) === false){
                M('weixin_column')->rollback();
            }else{
                M('weixin_column')->commit();
                $this->redirect('WeiXinPush/weiXinColumnList');
            }
        }
        $resources = $this->model->getColumnOne($where);
        //var_dump($resources);die;
        $this->assign('id',$id);
        $this->assign('title',$resources['name']);
        $this->assign('type',$resources['type']);
        $this->assign('status',$resources['status']);
        $this->display();
    }*/

    /*
     *关联资源新增
     */
    public function weiXinPush_add()
    {
        if (!session('?admin')) redirect(U('Login/login'));
        $parms2['weixin_push_id'] = I('push_id', 'post');
        $parms2['weixin_column_id'] = I('column_id', 'post');
        $parms2['weixin_column_title_contact_id'] = I('ctcid','post');

        $parms['weixin_push_id'] = I('push_id', 'post');
        $parms['weixin_column_id'] = I('column_id', 'post');
        $parms['weixin_column_title_contact_id'] = I('ctcid','post');
        $parms['creat_time'] = time();
        $check['weixin_column_id'] = I('column_id', 'post');
        $check['weixin_column_title_contact_id'] = I('ctcid','post');
        //$sort = I('max','post');

        $model = M('weixin_column_title_push_contact');
        $tip = $model->where($parms2)->find();//echo M()->getLastSql();die;
        //var_dump($tip);die;
        $max_sort = $model->field('sort')->where($check)->order('sort desc')->select();
        $parms['sort'] = $max_sort[0]['sort']+1;
        if ($tip) {
            $this->ajaxReturn('error');
        } else {
            $model->startTrans();
            $state = $model
                ->data($parms)
                ->add();
            if ($state == false) {
                $model->rollback();
                $this->ajaxReturn('failed');
            } else {
                $model->commit();
                $this->ajaxReturn('success');
            }
        }

    }

    /*
    *ajax获取文章名称
    */
    public function push_name()
    {
        $push_id['id'] = $_POST['push_id'];
        $model = M('weixin_push');
        $name = $model->field('title')
            ->where($push_id)
            ->find();
        echo json_encode($name);
    }

    /*
     *关联删除
     */
    public function weiXinPush_delete()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $Model = M('weixin_column_title_push_contact');
        $Model->startTrans();
        if ($Model->where("id=$id")->delete() === false) {
            $Model->rollback();
            $this->ajaxReturn('failed');
        } else {
            $Model->commit();
            $this->ajaxReturn('success');
        }
    }

    /*
     *文章关联限制
     */
    public function weiXinPush_check()
    {
        $data['weixin_push_id'] = $_POST['push_id'];
        $data['weixin_column_id'] = $_POST['column_id'];
        if($_POST['ctcid']){
            $data['weixin_column_title_contact_id'] = $_POST['ctcid'];
        }

        $match = '/^[0-9]*[1-9][0-9]*$/';
        if (preg_match($match, $data['weixin_push_id']) == false) {
            $this->ajaxReturn('type_error');
        }elseif (M('weixin_column_title_push_contact')->where($data)->find() == true){
            $this->ajaxReturn('type_error');
        }elseif (M('weixin_push')->where("id =".$_POST['push_id'])->find() == false) {
            $this->ajaxReturn('type_error2');
        }else {
            $this->ajaxReturn('success');
        }
    }

    /*
     *栏目详情内容排序修改
     */
    public function weiXinPush_save()
    {
        $ctcid = $_POST['ctcid'];
        $column_id = $_POST['column_id'];
        $id = $_POST['ids'];
        $value = $_POST['values'];
        $arr = array_combine($id, $value);
        //print_r($arr);
        $Model = M('weixin_column_title_push_contact');
        $Model->startTrans();
        foreach ($arr as $k => $v) {
            $sql = "update weixin_column_title_push_contact set sort=$v WHERE id=$k";
            $tips = $Model->execute($sql);
            if ($tips === false) {
                $Model->rollback();
                $tip = false;
                $this->error('修改失败');
            } else {
                $Model->commit();
            }
        }
        if(!empty($ctcid)){

            $this->redirect("WeiXinPush/titleList",array('column_id'=>$column_id,'ctcid'=>$ctcid));
        }else{
            $this->redirect("WeiXinPush/titleList",array('column_id'=>$column_id));
        }

    }


}
