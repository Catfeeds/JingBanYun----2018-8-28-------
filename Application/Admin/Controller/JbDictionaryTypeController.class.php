<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/22 0022
 * Time: 上午 11:18
 */
namespace Admin\Controller;

use Think\Controller;

class JbDictionaryTypeController extends Controller
{
    private $model = '';

    public function __construct()
    {
        parent::__construct();
        $this->model = D('Knowledge_type');
        $this->assign('oss_path', C('oss_path'));
    }

    /*
     * 资源融合数据字典分类列表
     */
    public function get_dictionary_list()
    {
        if (!session('?admin')) redirect(U('Login/login'));
        $data = $this->model->get_resources_all();
        $this->assign('list', $data);
        $this->display('dataDictionary');
    }

    /*
     * 资源融合数据字典分类添加
     */
    public function dictionary_add()
    {
        if (!session('?admin')) redirect(U('Login/login'));
        $parms['type_name'] = I('value', 'get');
        $model = M('knowledge_type');
        $model->startTrans();
        $state = $model
            ->data($parms)
            ->add();
        if($state == false){
            $model->rollback();
            $this->ajaxReturn('failed');
        }else{
            $model->commit();
            $this->ajaxReturn('success');
        }
    }

    /*
    * 资源融合数据字典分类修改
    */
    public function dictionary_save()
    {
        if (!session('?admin')) redirect(U('Login/login'));
        $parms['id'] = I('id', 'get');
        $data['type_name'] = I('type_name', 'get');
        $model = M('knowledge_type');
        $model->startTrans();
        $state = $model->where($parms)
            ->save($data);
        if($state == false){
            $model->rollback();
            $this->ajaxReturn('failed');
        }else{
            $model->commit();
            $this->ajaxReturn('success');
        }

    }

    /*
     * 资源融合数据字典分类删除
     */
    public function dictionary_delete()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $Model = M('knowledge_type');
        $Model->startTrans();
        if($Model->where("id=$id")->delete()===false){
            $Model->rollback();
            $this->ajaxReturn('failed');
        }else{
            $Model->commit();
            $this->ajaxReturn('success');
        }

    }
}