<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/22 0022
 * Time: 下午 4:37
 */

namespace Admin\Controller;

use Think\Controller;

class ColumnController extends Controller
{
    private $model = '';

    public function __construct()
    {
        parent::__construct();
        $this->model = D('Dict_column');
        $this->assign('oss_path', C('oss_path'));
    }

    /*
     *栏目列表
     */
    public function get_column_list()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '京版资源');
        $this->assign('nav', '京版资源');
        $this->assign('subnav', '');

        $filter['keyword'] = $_REQUEST['keyword'];
        $filter['module_name'] = $_REQUEST['module_name'];
        $filter['port_name'] = $_REQUEST['port_name'];
        /*if($_POST){
            var_dump($_REQUEST['port_name']);die;
        }*/


        if (!empty($filter['keyword'])) $check['column_name'] = array('like','%'.$filter['keyword'].'%');
        if (!empty($filter['module_name'])) $check['module_name'] = $filter['module_name'];
        if (!empty($filter['port_name'])) $check['port_name'] = $filter['port_name'];

        $this->assign('keyword', $filter['keyword']);
        $this->assign('module_name', $filter['module_name']);
        $data = $this->assign('port_name', $filter['port_name']);


        $data = $this->model->getResourceAll($check);
        $this->assign('list', $data['list']);
        $this->assign('page', $data['page']);
        $this->display('columnList');
    }

    /*
     *栏目列表详情
     */
    public function get_column_details()
    {
        $check['id'] = I('id', 'get');
        $data = $this->model->getResourceOne($check);   
        $split = explode('、', $data['port_name']);
        if (count($split) > 1) {
            $this->assign('data1', $split['0']);
            $this->assign('data2', $split['1']);
            $this->assign('data3', $split['2']);
        }
        $check1['dict_column_contact.column_id'] = I('id', 'get');
        switch($data['module_name'])
        {
            case '京版资源':$this->model = D('Dict_column_contact');
                             $list = $this->model->get_resource_all($check1);
                             $this->assign('list', $list['list']);              
                             $this->assign('page', $list['page']);
                             $this->assign('data', $data);
                             $this->display('columnDetail');
                             break;
            case '京版活动':$list = D('Social_activity')->getColumnContentList($check['id']);
                             $this->assign('data', $data);
                             $this->assign('list', $list);
                             $this->display('activityColumnDetail');
                             break;
            case '教师资源':
                            $list=D('Biz_resource')->getColumnContentList($check1);
                            $this->assign('data', $data);
                            $this->assign('list', $list['list']);
                            $this->assign('page', $list['page']);
                            $this->display('TeacjerResourcecolumnDetail');
                            break;
            default        :break;
        }

    }

    /*
     *栏目下关联资源新增
     */
    public function column_add()
    {
        if (!session('?admin')) redirect(U('Login/login'));
        $parms['resource_id'] = I('resource_id', 'post');
        $column_id['column_id'] = I('column_id', 'post');
        $parms['column_id'] = I('column_id', 'post');
        //$sort = I('max','post');

        $model = M('dict_column_contact');
        $tip = $model->where($parms)->order('sort desc')->find();
        $max_sort = $model->where($column_id)->order('sort desc')->select();
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
    *ajax获取资源名称
    */
    public function resource_name()
    {
        $resource_id['id'] = $_POST['resource_id'];
        $model = M('knowledge_resource');
        $name = $model->field('name')
            ->where($resource_id)
            ->find();
        echo json_encode($name);
    }

    /*
     *栏目删除
     */
    public function column_delete()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $column['column_id'] = $_GET['column_id'];
        $column['status'] = 1;
        if ($_GET['column_id'] == 1) {
            $num = 9;
        } elseif ($_GET['column_id'] == 2) {
            $num = 6;
        } elseif ($_GET['column_id'] == 3) {
            $num = 4;
        }
        $Model = M('dict_column_contact');
        $Model->startTrans();
        if ($Model->where("id=$id")->delete() === false) {
            $Model->rollback();
            $this->ajaxReturn('failed');
        } elseif ($Model->where($column)->count() < $num) {
            $Model->rollback();
            $this->ajaxReturn('failed');
        } else {
            $Model->commit();
            $this->ajaxReturn('success');
        }
    }

    /*
     *栏目上下架
     */
    public function column_updown()
    {
        if (!session('?admin')) redirect(U('Login/login'));
        $value['column_id'] = $_GET['column_id'];
        if ($_GET['column_id'] == 1) {
            $num = 9;
        } elseif ($_GET['column_id'] == 2) {
            $num = 6;
        } elseif ($_GET['column_id'] == 3) {
            $num = 4;
        }
        $value['status'] = '1';
        $status['status'] = $_GET['status'];
        $id = $_GET['id'];
        $Model = M('dict_column_contact');
        $Model->startTrans();
        if ($Model->where("id=$id")->data($status)->save() === false) {
            $Model->rollback();
            $this->ajaxReturn('failed');
        } elseif ($Model->where($value)->count() < $num && $_GET['status'] == 0) {
            $Model->rollback();
            $this->ajaxReturn('full');
        } else {
            $Model->commit();
            $this->ajaxReturn('success');
        }
    }

    /*
     *栏目详情内容排序修改
     */
    public function column_save()
    {
        $id = $_POST['ids'];
        $value = $_POST['values'];
        $arr = array_combine($id, $value);
        //print_r($arr);
        $Model = M('dict_column_contact');
        $Model->startTrans();
        foreach ($arr as $k => $v) {
            $sql = "update dict_column_contact set sort=$v WHERE id=$k";
            $tips = $Model->execute($sql);
            if ($tips === false) {
                $Model->rollback();
                $tip = false;
                $this->error('修改失败');
            } else {
                $Model->commit();
            }
        }
        $this->success('修改成功');
    }

    /*
     *栏目排序修改
     */
    public function column_sort_save()
    {
        $id = $_POST['ids'];
        $value = $_POST['values'];
        $arr = array_combine($id, $value);
        //print_r($arr);
        $Model = M('dict_column');
        $Model->startTrans();
        foreach ($arr as $k => $v) {
            $sql = "update dict_column set sort=$v WHERE id=$k";
            $tips = $Model->execute($sql);
            if ($tips === false) {
                $Model->rollback();
                $tip = false;
                $this->error('修改失败');
            } else {
                $Model->commit();
            }
        }
        $this->success('修改成功');
    }


    /*
     *栏目资源关联限制
     */
    public function column_check()
    {
        $data['resource_id'] = $_POST['resource_id'];
        $resource_id['id'] = $_POST['resource_id'];
        $data['column_id'] = $_POST['column_id'];
        $match = '/^[0-9]*[1-9][0-9]*$/';
        if (preg_match($match, $data['resource_id']) == false) {
            $this->ajaxReturn('type_error');
        }elseif (M('knowledge_resource')->where($resource_id)->find() == false){
            $this->ajaxReturn('type_error');
        }else {
            $model = M('knowledge_resource_attr');
            $tip = $model->where($data)->find();      
            if (!$tip) {
                $this->ajaxReturn('type_error');
            } else {
                $this->ajaxReturn('success');
            }
        }
    }
}