<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/19 0019
 * Time: 上午 10:10
 */
namespace Common\Model;

use Think\Model;

class School_joinModel extends Model
{

    public $model = '';
    protected $tableName = 'school_join';

    public function __construct()
    {
        parent::__construct();
        $this->model = M('school_join');
    }

    /*
     *获取所有内容
     */
    public function resource_all(&$count,$pageIndex=1,$pageSize=20)
    {
        $count = $this->model->field('count(1) as num')->find();
        $count = $count['num'];
        $resources = $this->model->order('creat_time desc')->page($pageIndex . ',' . $pageSize)->select();
        return $resources;
    }
    /*
     *学校加入
     */
    public function SchoolJoin($data){
        $this->model->startTrans();
        $tip = $this->model->add($data);
        if($tip == false){
            $this->model->rollback();
            return false;
        }else{
            $this->model->commit();
            return true;
        }
    }

}