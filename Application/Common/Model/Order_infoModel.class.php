<?php
namespace Common\Model;
use Think\Model;
define('PAYMENT_STATUS',2);

class Order_infoModel extends Model{

    public    $model='';
    protected $tableName = 'order_info';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }

    /*
     * 根据订单号获得订单信息
     * tel 手机号
     */
    public function getOrderInfo($order_sn) {
        $data['order_sn'] = $order_sn;
        return $this->model->where($data)->find();
    }


    /*
    * 后台获取订单列表
    *
    */
    public function getAdminOrderList( $where,$having ) {

        if (!empty($having)) {
            $count = $this->model
                ->join('left join knowledge_resource ON knowledge_resource.id = order_info.resources_id')
                ->join('left join direct_train ON direct_train.id = order_info.resources_id')
                ->join('left join auth_student ON auth_student.id = order_info.user_id')
                ->join('left join auth_teacher ON auth_teacher.id = order_info.user_id')
                ->join('left join auth_parent ON auth_parent.id = order_info.user_id')
                ->field('order_info.*,knowledge_resource.name,case when order_info.user_role=2 then auth_teacher.telephone when order_info.user_role=3 then auth_student.parent_tel when order_info.user_role=4 then auth_parent.telephone end as telephone')
                ->where($where)
                ->having($having)
                ->order('order_info.id desc')
                ->select();
            $count = count( $count );
        } else {
            $count = $this->model
                ->join('knowledge_resource ON knowledge_resource.id = order_info.resources_id')
                ->join('left join auth_student ON auth_student.id = order_info.user_id')
                ->join('left join auth_teacher ON auth_teacher.id = order_info.user_id')
                ->join('left join auth_parent ON auth_parent.id = order_info.user_id')
                ->field('order_info.*,knowledge_resource.name,case when order_info.user_role=2 then auth_teacher.telephone when order_info.user_role=3 then auth_student.parent_tel when order_info.user_role=4 then auth_parent.telephone end as telephone')
                ->where($where)
                //->having($having)
                ->order('order_info.id desc')
                ->count();
        }
        
        //$count = $count['tp_count'];

        $Page       = new \Think\Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $show       = $Page->show();// 分页显示输出

        $list = $this->model
            ->join('left join knowledge_resource ON knowledge_resource.id = order_info.resources_id')
            ->join('left join direct_train ON direct_train.id = order_info.resources_id')
            ->join('left join auth_student ON auth_student.id = order_info.user_id')
            ->join('left join auth_teacher ON auth_teacher.id = order_info.user_id')
            ->join('left join auth_parent ON auth_parent.id = order_info.user_id')
            ->field('(case when order_info.order_type = 0 then knowledge_resource.name when order_info.order_type = 1 then special_column_question_title else \'\' end) name,order_info.*,case when order_info.user_role=2 then auth_teacher.telephone when order_info.user_role=3 then auth_student.parent_tel when order_info.user_role=4 then auth_parent.telephone end as telephone')
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->having($having)
            ->order('order_info.id desc')
            ->select();


        $data['show'] = $show;
        $data['list'] = $list;
        return $data;
    }

    /*
    * 根据用户角色获取用户的所有订单
    *
    */
    public function getUserWebOrderList( $user_id,$role,$check=[],$filter ) {
        $where = [
            'order_info.is_delete' => 1,
            'order_info.user_id' => $user_id,
            'order_info.user_role' => $role,
        ];
        if (!empty($check))
            $where = array_merge($where,$check);

        $join[] = 'left join knowledge_resource ON knowledge_resource.id = order_info.resources_id';
        $join[] = 'left join direct_train ON direct_train.id = order_info.resources_id';

        $count = $this->model
            ->join( $join )
            ->field('(case when order_info.order_type = 0 then knowledge_resource.name when order_info.order_type = 1 then special_column_question_title else \'\' end) name,order_info.resources_id,order_info.order_sn,order_info.order_status,order_info.pay_type,order_info.pay_fee,order_info.create_at,knowledge_resource.pc_cover,order_type')
            ->order('order_info.create_at desc')
            ->where( $where )
            ->count();

        $Page       = new \Think\Page($count,5);// 实例化分页类 传入总记录数和每页显示的记录数(25)

        foreach($filter as $key=>$val) {
            $Page->parameter[$key]   =   $val;
        }

        $show       = $Page->show();// 分页显示输出

        $list = $this->model
            ->join( $join )
            ->field('(case when order_info.order_type = 0 then knowledge_resource.name when order_info.order_type = 1 then special_column_question_title else \'\' end) name,order_info.resources_id,order_info.order_sn,order_info.order_status,order_info.pay_type,order_info.pay_fee,order_info.create_at,knowledge_resource.pc_cover,order_info.pay_create_at,order_info.order_cancel_create_at,order_type')
            ->order('order_info.create_at desc')
            ->where( $where )
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();

        foreach ( $list as $k=>&$v ) {
            if (!empty($v['pc_cover'])) {
                if (strpos($v['pc_cover'],'http')===false) {
                    $v['pc_cover'] = C('oss_path').$v['pc_cover'];
                }
            }

        }

        $data['show'] = $show;
        $data['list'] = $list;
        return $data;
    }

    /*
   * 根据用户角色获取用户的所有订单  APP
   *
   */

    public function getUserAppOrderList( $user_id,$role,$check=[],$filter,$p,$pagesize ) {
        $where = [
            'order_info.is_delete' => 1,
            'order_info.user_id' => $user_id,
            'order_info.user_role' => $role,
        ];
        if (!empty($check))
             $where = array_merge($where,$check);


        $join[] = 'left join knowledge_resource ON knowledge_resource.id = order_info.resources_id';
        $join[] = 'left join direct_train ON direct_train.id = order_info.resources_id';

        if (empty($where['order_status'])) {
            $order = 'order_info.create_at desc';
        }

        if ($where['order_status']== 1) {
            $order = 'order_info.create_at desc';
        }

        if ($where['order_status']== 2) {
            $order = 'order_info.pay_create_at desc';
        }

        if ($where['order_status']== 3) {
            $order = 'order_info.order_cancel_create_at desc';
        }

        $list = $this->model
            ->join( $join )
            ->field('(case when order_info.order_type = 0 then knowledge_resource.name when order_info.order_type = 1 then special_column_question_title else \'\' end) name,order_info.resources_id,order_info.order_sn,order_info.order_status,order_info.pay_type,order_info.pay_fee,order_info.create_at,knowledge_resource.pc_cover,order_info.pay_create_at,order_info.order_cancel_create_at,order_type')
            ->order( $order )
            ->where( $where )
            ->page($p.','.$pagesize)
            ->select();

        foreach ( $list as $k=>&$v ) {
            if (!empty($v['pc_cover'])) {
                if (strpos($v['pc_cover'],'http')===false) {
                    $v['pc_cover'] = C('oss_path').$v['pc_cover'];
                }

                $v['create_at'] = "下单时间: ".date("Y-m-d H:i:s",$v['create_at']);
                $v['pay_fee'] = $v['pay_fee'].'元/套';
            }

        }

        return $list;
    }
    
    
    /*
     * 根据用户角色获得某个订单某个资源信息
     */
    public function getPaymentOrderResource($resource_id,$user_id,$role){
        $where=array(
            //'order_info.is_delete'=>1,
            'order_info.user_id' => $user_id,
            'order_info.user_role' => $role,
            'knowledge_resource.id'=>$resource_id,
            //'order_status'=>PAYMENT_STATUS
        );
        $join[] = 'knowledge_resource ON knowledge_resource.id = order_info.resources_id';
        $data = $this->model
            ->join( $join )
            ->field('order_info.order_sn,order_info.order_status,order_info.pay_type,order_info.pay_fee,order_info.create_at,knowledge_resource.name,knowledge_resource.pc_cover')
            ->order('order_info.create_at desc')
            ->where( $where )
            ->find();

        if (!empty($data['pc_cover'])) {
            if (strpos($data['pc_cover'],'http')===false) {
                $data['pc_cover'] = C('oss_path').$data['pc_cover'];
            }
        }


        return $data;
    }

    /*
     * 根据用户角色获得某个订单某个资源信息
     */
    public function getResourceByOrder($resource_id,$user_id,$role){
        $where=array(
            //'order_info.is_delete'=>1,
            'order_info.user_id' => $user_id,
            'order_info.user_role' => $role,
            'knowledge_resource.id'=>$resource_id
           // 'order_status'=>PAYMENT_STATUS
        );
        $join[] = 'knowledge_resource ON knowledge_resource.id = order_info.resources_id';
        $data = $this->model
            ->join( $join )
            ->field('order_info.is_delete,order_info.order_sn,order_info.order_status,order_info.pay_type,order_info.pay_fee,order_info.create_at,knowledge_resource.name,knowledge_resource.pc_cover')
            ->order('order_info.create_at desc')
            ->where( $where )
            ->find();

        if (!empty($data['pc_cover'])) {
            if (strpos($data['pc_cover'],'http')===false) {
                $data['pc_cover'] = C('oss_path').$data['pc_cover'];
            }
        }


        return $data;
    }


    /*
     * 根据用户角色获得某个订单某个栏目信息
     */
    public function getDirectByOrder($id,$user_id,$role){
        $where=array(
            //'order_info.is_delete'=>1,
            'order_info.user_id' => $user_id,
            'order_info.user_role' => $role,
            'direct_train.id'=>$id,
            'order_info.order_type'=>1,
            // 'order_status'=>PAYMENT_STATUS
        );
        $join[] = 'direct_train ON direct_train.id = order_info.resources_id';
        $data = $this->model
            ->join( $join )
            ->field('order_info.is_delete,order_info.order_sn,order_info.order_status,order_info.pay_type,order_info.pay_fee,order_info.create_at,direct_train.special_column_question_title')
            ->order('order_info.create_at desc')
            ->where( $where )
            ->find();
        return $data;
    }

    //pc删除订单
    public function delOrder( $order_sn ) {
        $data['order_sn'] = array('in',$order_sn);
        $map['is_delete'] = 2;
        $res = $this->model->where( $data )->save( $map );
        return $res;
    }

    //app删除订单
    public function delAppOrder( $userId,$role,$order_sn ) {
        $where = [
            'user_id' => $userId,
            'user_role' => $role,
            'order_sn' => $order_sn[0],
        ];

        $row = $this->model->where( $where )->find();

        if ( !empty($row) ) {
            $data['order_sn'] = $order_sn[0];
            $map['is_delete'] = 2;
            $res = $this->model->where( $data )->save( $map );
            return $res;
        } else {
            return false;
        }
    }
    //app取消订单
    public function cancelAppOrderModel( $userId,$role,$order_sn  ) {
        $where = [
            'user_id' => $userId,
            'user_role' => $role,
            'order_sn' => $order_sn[0],
        ];

        $row = $this->model->where( $where )->find();

        if ( !empty($row) ) {
            $data['order_sn'] = $order_sn[0];
            $map['order_status'] = 3;
            $map['order_cancel_create_at'] = time();
            $res = $this->model->where( $data )->save( $map );
            return $res;
        } else {
            return false;
        }

    }

    //pc取消订单
    public function cancelOrderModel( $order_sn ) {
        $data['order_sn'] = array('in',$order_sn);
        $map['order_status'] = 3;
        $map['order_cancel_create_at'] = time();
        $res = $this->model->where( $data )->save( $map );
        return $res;
    }

    //根据订单号查询订单信息
    public function orderSnGetOrderInfo( $order_sn,$userId,$role ) {
        if (empty($order_sn)) {
            return true;
        }
        $where=array(
            'order_info.is_delete'=>1,
            'order_info.user_id' => $userId,
            'order_info.user_role' => $role,
            'order_info.order_sn'=>$order_sn,
        );
        $join[] = 'knowledge_resource ON knowledge_resource.id = order_info.resources_id';
        $order = $this->model
            ->join( $join )
            ->field('order_info.resources_id,order_info.order_sn,order_info.order_status,order_info.pay_type,order_info.pay_fee,order_info.create_at,knowledge_resource.name,knowledge_resource.pc_cover,order_info.pay_create_at,order_info.order_cancel_create_at')
            ->order('order_info.create_at desc')
            ->where( $where )
            ->find();

        if (!empty($order['pc_cover'])) {
            if (strpos($order['pc_cover'],'http')===false) {
                $order['pc_cover'] = C('oss_path').$order['pc_cover'];
            }
        }


        if ( $order ) {
            return $order;
        } else {
            return false;
        }

    }

    //根据订单号查询订单信息
    public function SnGetOrderInfo( $order_sn,$userId,$role ) {
        if (empty($order_sn)) {
            return true;
        }
        $where=array(
            'order_info.is_delete'=>1,
            'order_info.user_id' => $userId,
            'order_info.user_role' => $role,
            'order_info.order_sn'=>$order_sn,
        );
        $join[] = 'knowledge_resource ON knowledge_resource.id = order_info.resources_id';
        $order = $this->model
            ->join( $join )
            ->field('order_info.resources_id,order_info.order_sn,order_info.order_status,order_info.pay_type,order_info.pay_fee,order_info.create_at,knowledge_resource.name,knowledge_resource.pc_cover,order_info.pay_create_at,order_info.order_cancel_create_at')
            ->order('order_info.create_at desc')
            ->where( $where )
            ->find();

        if (!empty($order['pc_cover'])) {
            if (strpos($order['pc_cover'],'http')===false) {
                $order['pc_cover'] = C('oss_path').$order['pc_cover'];
            }
        }


        if ( $order ) {
            return $order;
        } else {
            return false;
        }

    }

    //专栏
    public function directSnGetOrderInfo( $order_sn,$userId,$role ) {
        if (empty($order_sn)) {
            return true;
        }
        $where=array(
            'order_info.is_delete'=>1,
            'order_info.user_id' => $userId,
            'order_info.user_role' => $role,
            'order_info.order_sn'=>$order_sn,
            'order_info.order_type'=>1,
        );
        $join[] = 'direct_train ON direct_train.id = order_info.resources_id';
        $order = $this->model
            ->join( $join )
            ->field('order_info.resources_id,order_info.order_sn,order_info.order_status,order_info.pay_type,order_info.pay_fee,order_info.create_at,direct_train.special_column_question_title,order_info.pay_create_at,order_info.order_cancel_create_at')
            ->order('order_info.create_at desc')
            ->where( $where )
            ->find();

        if ( $order ) {
            return $order;
        } else {
            return false;
        }

    }

    //App根据订单号查询订单信息
    public function orderAppSnGetOrderInfo( $order_sn,$userId,$role ) {
        if (empty($order_sn)) {
            return true;
        }
        $where=array(
            'order_info.is_delete'=>1,
            'order_info.user_id' => $userId,
            'order_info.user_role' => $role,
            'order_info.order_sn'=>$order_sn,
        );
        $join[] = 'left join knowledge_resource ON knowledge_resource.id = order_info.resources_id';
        $join[] = 'left join direct_train ON direct_train.id = order_info.resources_id';

        $order = $this->model
            ->join( $join )
            ->field('order_type,(case when order_info.order_type = 0 then knowledge_resource.name when order_info.order_type = 1 then special_column_question_title else \'\' end) name,order_info.user_id,order_info.user_role,order_info.resources_id,order_info.order_sn,order_info.order_status,order_info.pay_type,order_info.pay_fee,order_info.create_at,knowledge_resource.pc_cover,order_info.pay_create_at,order_info.order_cancel_create_at')
            ->order('order_info.create_at desc')
            ->where( $where )
            ->find();
        $resourceInfo = D('Knowledge_resource')->getResourceDetailInfoWithoutMulti($order['resources_id'],$role,$userId);

        if ($order['order_type']==0) {
            $order['fee_type'] = '元/套';
            $order['pc_cover'] = C('oss_path') . $resourceInfo[0]['pc_cover'];
            $order['dec'] = $resourceInfo[0]['course_name'];
            $order['resource_url'] = 'http://'.WEB_URL .'/ApiInterface/'.APIINTERFACE_DIR.'/KnowledgeResource/resourceDetails?id='.$order['resources_id'];
        } else {
            $array = [1=>"上册",2=>"下册",3=>"全一册"];

            $map['direct_train.id'] = $order['resources_id'];
            $resdata = M('direct_train')->field("auth_teacher.avatar,dict_grade.grade,direct_train.fascicule_id")
                ->join( "auth_teacher ON auth_teacher.id=direct_train.special_column_editor_quizzer_id" )
                ->join( "dict_grade ON dict_grade.id=direct_train.grade_id" )
                ->where($map)
                ->find();
            $order['fee_type'] = '元';
            $order['pc_cover'] = C('oss_path') . $resdata['avatar'];
            $order['dec'] =$resdata['grade'].$array[$resdata['fascicule_id']];
            $order['resource_url'] = "http://".WEB_URL."/ApiInterface/Version1_3/DirectCar/specialColumnDetailsView?id=".$order['resources_id'];
        }

        if ( $order ) {
            return $order;
        } else {
            return false;
        }

    }



    //接口支付改变订单状态和时间
    //pc取消订单
    public function editOrderStatusSuccess( $order_sn ) {
        $data['order_sn'] = array('in',$order_sn);
        $map['order_status'] = 2;
        $map['pay_create_at'] = time();
        $map['pay_type'] = 2;
        $map['trade_no'] = $GLOBALS['notify_data']['transaction_id'];
        $res = $this->model->where( $data )->save( $map );
        return $res;
    }

    //入库订单接口
    public function addOrder( $order_data ) {
        $id = $this->model->add( $order_data );
        return $id;
    }


    //根据订单id查询订单信息
    public function orderIdGetOrderInfo( $id,$userId,$role ) {
        if (empty($id)) {
        return true;
    }
        $where=array(
            'order_info.is_delete'=>1,
            'order_info.user_id' => $userId,
            'order_info.user_role' => $role,
            'order_info.id'=>$id,
        );
        $join[] = 'knowledge_resource ON knowledge_resource.id = order_info.resources_id';
        $order = $this->model
            ->join( $join )
            ->field('order_info.resources_id,order_info.order_sn,order_info.order_status,order_info.pay_type,order_info.pay_fee,order_info.create_at,knowledge_resource.name,knowledge_resource.pc_cover,order_info.pay_create_at,order_info.order_cancel_create_at')
            ->order('order_info.create_at desc')
            ->where( $where )
            ->find();

        if (!empty($order['pc_cover'])) {
            if (strpos($order['pc_cover'],'http')===false) {
                $order['pc_cover'] = C('oss_path').$order['pc_cover'];
            }
        }

        if ( $order ) {
            return $order;
        } else {
            return false;
        }

    }

    public function DirectIdGetOrderInfo( $id,$userId,$role ) {
        if (empty($id)) {
            return true;
        }
        $where=array(
            'order_info.is_delete'=>1,
            'order_info.user_id' => $userId,
            'order_info.user_role' => $role,
            'order_info.id'=>$id,
        );
        $join[] = 'direct_train ON direct_train.id = order_info.resources_id';
        $order = $this->model
            ->join( $join )
            ->field('order_info.resources_id,order_info.order_sn,order_info.order_status,order_info.pay_type,order_info.pay_fee,order_info.create_at,direct_train.special_column_question_title,order_info.pay_create_at,order_info.order_cancel_create_at')
            ->order('order_info.create_at desc')
            ->where( $where )
            ->find();

        if ( $order ) {
            return $order;
        } else {
            return false;
        }

    }



}
