<?php
namespace Common\Model;
use Think\Model;

class App_statisticsModel extends Model{

    public    $model='';
    protected $tableName = 'app_statistics';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }

    /*
     * 添加接入信息
     *
     */
    public function addAccessRecord($data){
       $this->model->add($data);
    }
}