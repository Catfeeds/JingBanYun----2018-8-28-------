<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/9/9
 * Time: 17:01
 */
namespace Common\Model;
use Think\Model;

class Activity_pull_peopleModel extends Model{

    public    $model='';
    protected $tableName = 'activity_pull_people';
    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);

    }

    /*
     *添加
     */
    public function AddResources($data){
        $addStatus = $this->model
            ->add($data);
        return $addStatus;
    }

    /*
     *修改
     */
    public function SaveResources($data,$where){
        $saveStatus = $this->model
            ->where($where)
            ->save($data);
        return $saveStatus;
    }

    /*
     *查询一条数据
     */
    public function getResourcesOne($where){
        $dataOne = $this->model
            ->where($where)
            ->find();
        return $dataOne;
    }

    /*
     *查询所有数据
     */
    public function getAllForList(&$count, $where = array(), $pageIndex = 1, $pageSize = 20)
    {
        $count = $this->model
            ->where($where)
            ->select();
        $count = count($count);

        $resources = $this->model
            ->where($where)
            ->page($pageIndex . ',' . $pageSize)
            ->select();  //echo M()->getLastSql();die;
        return $resources;
    }
}
