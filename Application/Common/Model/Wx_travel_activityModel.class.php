<?php
/**
 * Created by PhpStorm.
 * User: GM
 * Date: 2017/12/28
 * Time: 10:00
 */
namespace Common\Model;

use Think\Model;
class Wx_travel_activityModel extends Model
{
    public $model = '';
    protected $tableName = 'wx_travel_activity';

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
        $result = M('wx_travel_activity_order')
            ->add($data);
        return $result;
    }

    /**
     *描述：查询所有数据
     */
    public function getAll($where){
        if($where){
            $str = "where order_status =". $where;
        }
        $sql = "SELECT
	(select sum(pay_fee) FROM
	wx_travel_activity_order) total_pay,
	wx_travel_activity_order.order_sn,
	`pay_fee`,
	order_status,
	wx_travel_activity_order.create_time,
	`name`,
	`phone`,
	`age`,
	`activity_time`,
	`activity_type`
FROM
	`wx_travel_activity`
INNER JOIN wx_travel_activity_order ON wx_travel_activity_order.order_sn = wx_travel_activity.order_sn
$str
ORDER BY
	wx_travel_activity_order.create_time desc";
        $result = $this->model
            ->query($sql);
        return $result;
    }

    /**
     *描述：修改操作
     */
    public function saveOrderStatus($data,$where){
        $result = M('wx_travel_activity_order')
            ->where($where)
            ->save($data);
        return $result;
    }

    /**
     *描述：根据条件查询
     */
    public function selectByWhere($where){
        $result = $this->model
            ->where($where)
            ->select();
        return $result;
    }

    public function getOrderPrice($orderId)
    {
        $result = M()->query("SELECT pay_fee FROM wx_travel_activity_order WHERE order_sn=$orderId LIMIT 1");
        return $result[0]['pay_fee'];
    }
}