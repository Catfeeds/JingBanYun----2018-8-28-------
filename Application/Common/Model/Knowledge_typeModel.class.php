<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/22 0022
 * Time: 上午 11:41
 */
namespace Common\Model;

use Think\Model;

class Knowledge_typeModel extends Model
{
    public $model = '';
    protected $tableName = 'knowledge_type';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    /*
     *获取数据字典分类中的所有数据
     */
    public function get_resources_all(){
        $data = $this->model
            ->select();
        return $data;
    }

    public function getGroupTypeName($typeIds = [])
    {
       $where = [];
       if(!empty($typeIds))
        $where['id'] = array('in',$typeIds);
       $result = $this->model->where($where)->field('group_concat(type_name) names')->find();
       return $result['names'];
    }
}