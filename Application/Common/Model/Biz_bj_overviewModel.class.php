<?php
namespace Common\Model;
use Think\Model;

class Biz_bj_overviewModel extends Model
{

    public $model = '';
    protected $tableName = 'biz_bj_overview';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    /*
     * 获取京版概览
     *
     */

    public function getJbOverView()
    {
        $result =$this->model->where('status=2')->select();
        return $result[0];
    }

}