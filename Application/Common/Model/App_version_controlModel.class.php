<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/6/16
 * Time: 14:51
 */
namespace Common\Model;
use Think\Model;

class App_version_controlModel extends Model
{
    public $model = 'app_version_control';
    protected $tableName = 'app_version_control';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }


    /*
     *查询所有数据并分页
     */
    public function all(&$count,$pageIndex=0,$pageSize=20){
        $count = $this->model->select();
        $count = count($count);
        $data = $this->model->page($pageIndex.','.$pageSize)->order('putaway_time desc')->select();
        return $data;
    }

    /*
     *插入操作
     */
    public function insert($data){
        $result = $this->model->add($data);
        return $result;
    }

    /*
     *修改操作
     */
    public function save($where,$data){
        $result = $this->model->where($where)->save($data);
        return $result;
    }

    /*
     *查询其中一条数据
     */
    public  function getOne($where){
        $result = $this->model->where($where)->find();
        return $result;
    }

    /*
     *前台调用
     */
    public function returnDate($where){
        $result = $this->model->where($where)->order('creat_time desc')->select();
        return $result;
    }

    /**
     *描述：根据条件查询所有数据并分页
     */
    public function getResourceByWhere($where,&$count,$pageIndex=0,$pageSize=20){
        $resource = $this->model->where($where)->select();
        $count = count($resource);
        $data = $this->model->where($where)->page($pageIndex.','.$pageSize)->order('putaway_time desc')->select();
        return $data;
    }
}