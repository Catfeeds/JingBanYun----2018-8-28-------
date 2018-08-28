<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/7 0007
 * Time: 下午 6:33
 */

namespace Common\Model;

use Think\Model;

class Activity_wxModel extends Model
{
    public $model = '';
    protected $tableName = 'activity_wx';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    /*
     *获取报名的总数
     */
    public function getSignUpCount($condition = array())
    {
        $count = $this->model->count('1');
        return $count;
    }

    /*
     * 获取所有的报名信息列表
     */
    public function getSignUpList($condition = array(), $order = 'desc')
    {
        $count = $this->getSignUpCount($condition);
        $Page = new \Think\Page($count,  C('PAGE_SIZE_FRONT'));
        $show = $Page->show();
        $result = $this->model->field('id,student_name,school_name,class_name,class_teacher,telephone,creat_time')
            ->where($condition)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->order('creat_time ' . $order)
            ->select();
        $data['count'] = $count;
        $data['data'] = $result;
        $data['page'] = $show;
        return $data;
    }

    /*
     * 获取所有的报名信息
     */
    public function getSignUpAll($condition = array(), $order = 'desc')
    {
        $result = $this->model->field('student_name,school_name,class_name,class_teacher,telephone,creat_time,content')
            ->where($condition)
            ->order('creat_time ' . $order)
            ->select();
        return $result;
    }

    /*
     *获取详细信息
     */
    public function getSignUpDetail($condition = array())
    {
        $result = $this->model->field('student_name,school_name,class_name,class_teacher,telephone,content,creat_time')
            ->where($condition)
            ->find();
        return $result;
    }
}