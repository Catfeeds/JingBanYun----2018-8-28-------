<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/9/7
 * Time: 11:42
 */
namespace Common\Model;
use Think\Model;

class Exercises_tree_logModel extends Model
{
    public $model = '';
    protected $tableName = 'exercises_tree_log';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    /*
     *插入LOG
     *输入参数：
     *输出参数：BOOL型成功状态
     */
    public function insertLog($tree_id=0,$tree_category=0,$oper_name='',$ip_address='',$operator_id=0,$operator_name='',$comment='',$error_status=0)
    {
        if(strpos($ip_address,'.') !== false)
            $ip_address = sprintf('%u',ip2long($ip_address));
        $result = M()->execute(" INSERT INTO exercises_tree_log (tree_id,tree_category,oper_name,ip_address,operator_id,operator_name,comment,error_status) ".
            " VALUES ($tree_id,$tree_category,'$oper_name',$ip_address,$operator_id,'$operator_name','$comment',$error_status)");
        if(false === $result)
            return false;
        else
            return true;
    }

    /*
     *查询课标树知识点
     */
}
?>