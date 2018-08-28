<?php
/**
 * Created by PhpStorm.
 * User: GM
 * Date: 2017/12/28
 * Time: 10:00
 */
namespace Common\Model;

use Think\Model;
class Wx_trave_activityModel extends Model
{
    public $model = '';
    protected $tableName = 'wx_trave_activity';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    /**
     *描述:wx_trave_activity入库操作
     */
    public function add($data)
    {
        $result = $this->model
            ->add($data);
        return $result;
    }
    /**
     *描述：wx_trave_activity_order入库操作
     */
    public function orderAdd($data)
    {
        $result = M('wx_trave_activity_order')
            ->add($data);
        return $result;
    }

    /**
     *描述：查询所有数据
     */
    public function getAll(){
        $result = $this->model
            ->field('sum(pay_fee) totle_pay,wx_travel_activity_order.order_sn,pay_fee,wx_travel_activity_order.create_at,name,phone,age,activity_time,activity_type')
            ->join('wx_travel_activity_order on wx_travel_activity_order.order_sn = wx_trave_activity.order_sn')
            ->order('wx_travel_activity_order.create_at')
            ->select();
        return $result;
    }
}