<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/9/7
 * Time: 11:42
 */
namespace Common\Model;

use Think\Model;


class Exercises_homework_relationModel extends Model
{
    public $model = '';
    protected $tableName = 'exercises_homwork_relation';

    public function __construct()
    {
        parent::__construct();
    }

    /*
     *增加作业习题相关
     */
    public function addInfo($data)
    {
        $id = M($this->tableName)->addAll($data);
        if(empty($id))
            return false;
        return $id;
    }


    //根据作业id获取作业的教材列表
    public function getInfo($id)
    {
        $where['work_id'] = $id;
        $data = M($this->tableName)
            ->join('exercises_homwork_basics on exercises_homwork_basics.id=exercises_homwork_relation.work_id')
            ->where($where)
            ->field("exercises_homwork_relation.*,exercises_homwork_basics.course_id,exercises_homwork_basics.course_name")
            ->find();
        return $data;
    }

}