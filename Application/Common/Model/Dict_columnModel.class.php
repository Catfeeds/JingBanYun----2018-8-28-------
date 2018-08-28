<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/22 0022
 * Time: 下午 5:07
 */
namespace Common\Model;

use Think\Model;

class Dict_columnModel extends Model
{
    public $model = '';
    protected $tableName = 'dict_column';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }
    /*
        *获得新京版资源栏目总数
        */
    public function getResourceCount($check = array())
    {
        $resource = $this->model
            ->where($check)
            ->count();
        return $resource;
    }

    /*
    *获得新京版资源所有栏目内容
    */
    public function getResourceAll($check = array())
    {
        $count = $this->getResourceCount($check);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();
        $resource = $this->model
            ->where($check)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->order('module_name,sort')
            ->select();
        $data['page'] = $show;
        $data['list'] = $resource;
        return $data;
    }

    /*
    *获得新京版资源一条栏目内容
    */
    public function getResourceOne($check = array())
    {
        $resource = $this->model
            ->where($check)
            ->find();

        return  $resource;
    }

    /*
    *获得新栏目显示状态
    */
    public function setColumnDisplayState($id,$state)
    {
        $check['id'] = $id;
        $data['is_display'] = $state;
        $resource = $this->model
            ->where($check)
            ->save($data);

        return  $resource;
    }

    /*
     * 获取栏目排序
     */
    public function getColumnSort($idArray=array())
    {
        $idStr = '('.implode(',',$idArray).')';
        $result = M()->query("SELECT sort FROM dict_column WHERE id in $idStr");
        return array_column($result,'sort');
    }


    /*
    * 获取栏目排好序之后的id
    */
    public function getColumnSortForId($idArray=array())
    {
        $idStr = '('.implode(',',$idArray).')';
        $result = M()->query("SELECT id FROM dict_column WHERE id in $idStr ORDER BY sort");
        return array_column($result,'id');
    }
}