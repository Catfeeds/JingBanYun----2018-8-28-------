<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/21 0021
 * Time: 下午 3:12
 */
namespace Common\Model;

use Think\Model;

class Knowledge_pointModel extends Model
{
    public $model = '';
    protected $tableName = 'knowledge_point';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    public function get_resources($check=array())
    {
        $resource = $this->model
            ->field('id,knowledge_name,knowledge_name name')
            ->where($check)
            ->select();
        return $resource;
    }

    /*public function get_chapter($check=array())
    {
        $this->model
            ->field('id,knowledge_name')
            ->where($check)
            ->select();
    }

    public function get_festival($check=array())
    {
        $this->model
            ->field('id,knowledge_name')
            ->where($check)
            ->select();
    }*/
}