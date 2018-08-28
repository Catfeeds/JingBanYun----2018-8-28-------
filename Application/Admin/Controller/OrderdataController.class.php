<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Common\CSV;

class OrderdataController extends Controller
{  
    public $order='';
    public $page_size=20; 
    
    public function __construct() {
        parent::__construct();
        if (!session('?admin')) redirect(U('Login/login'));
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
        $this->order=D('Order_info');
    }
    
    /*
    * 学校列表      
    */
    public function orderList(){

        $crentp = I('p');
        if(empty($crentp)) {
            $this->p=1;
            $crentp = 1;
        }
        $filter['name']=trim(getParameter('name','str',false));
        $filter['order_sn']=getParameter('order_sn','str',false);
        $filter['order_status']=getParameter('order_status','int',false);
        $filter['telephone']=getParameter('telephone','str',false);
        $filter['user_role']=getParameter('user_role','int',false);
        $filter['pay_type']=getParameter('pay_type','int',false);
        $filter['pay_source']=getParameter('pay_source','int',false);
        $filter['telephone'] = trim($filter['telephone']);
        $filter['start']=getParameter('start','str',false);
        $filter['end']=getParameter('end','str',false);

        if(!empty($filter['name']))   $check['knowledge_resource.name']=array('like', '%' . $filter['name']. '%');
        if (!empty($filter['order_sn'])) $check['order_info.order_sn'] = $filter['order_sn'];
        if (!empty($filter['order_status'])) $check['order_info.order_status'] = $filter['order_status'];
        if (!empty($filter['telephone'])) {
            $having = 'telephone = '.$filter['telephone'];
        }

        if (!empty($filter['user_role'])) $check['order_info.user_role'] = $filter['user_role'];
        if (!empty($filter['pay_type'])) $check['order_info.pay_type'] = $filter['pay_type'];
        if (!empty($filter['pay_source'])) $check['order_info.pay_source'] = $filter['pay_source'];


        if (!empty($filter['start'])) {
            $startime = strtotime($filter['start']);
            $startime = date('Y-m-d 00:00:00', $startime);
            $check['order_info.create_at'] = array('egt',strtotime($startime));
        }

        if (!empty($filter['end'])) {
            $endtime = strtotime($filter['end']);
            $endtime = date('Y-m-d 23:59:59', $endtime);
            $check['order_info.create_at'] = array('elt',strtotime($endtime));
        }

        if (!empty($filter['start']) && !empty($filter['end']) ){
            $startime = strtotime($filter['start']);
            $startime = date('Y-m-d 00:00:00', $startime);
            $endtime = strtotime($filter['end']);
            $endtime = date('Y-m-d 23:59:59', $endtime);
            $check['order_info.create_at'] = array(array('egt',strtotime($startime)),array('elt',strtotime($endtime))) ;
        }

        $check['order_info.user_id'] = array('gt',0);
        $list=$this->order->getAdminOrderList( $check,$having );
        $this->assign('parameter',$filter);
        $this->assign('page',$list['show']);
        $this->assign('list',$list['list']);
        $this->assign('p',$crentp);
        $this->display();
    }

    //测试获取订单
    public function show() {
        $user_id = 1;
        $role = 2;
        $orderList=$this->order->getUserWebOrderList( $user_id,$role );
        print_r($orderList);die();

    }

    /**
     *描述：后台数据展示
     */
    public function orderLists(){
        $result = D('Wx_travel_activity')->getAll();
        $this->assign('list',$result);
        $this->display();
    }

    public function headmasterActivityReg()
    {
        $pageIndex = $_GET['p'] ? $_GET['p'] : 1;
        $pageSize = C('PAGE_SIZE_FRONT');
        $allRecordCount = 0;
        $result = D('Wx_headmaster_reg')->getRegData($pageIndex,$pageSize,$allRecordCount);

        $Page = new \Think\Page($allRecordCount, $pageSize);
        $show = $Page->show();

        $this->assign('list',$result);
        $this->assign('page', $show);
        $this->display();
    }

    private function exportCSV($fileName,$header,$data)
    {
        $csv=new CSV();
        $str = '';
        if(empty($data))
        {
            echo "<script>alert('数据为空,无法导出数据')</script>";
            exit;
        }
        $str.=$header . $data;
        $str=mb_convert_encoding($str,'gbk','utf-8');
        $csv->downloadFileCsv($fileName,$str);
    }

    public function headMasterRegExport()
    {
        $allRecordCount = 0;
        $result = D('Wx_headmaster_reg')->getRegData(-1,0,$allRecordCount);
        $content = '';
        foreach($result as $key => $line){
            $content .= "{$line['name']},{$line['telephone']},{$line['school_name']},{$line['professional']},{$line['create_at']}\n";
        }
        $header = "姓名,手机号,学校名称,职称,报名时间\n";
        $fileName=date('Ymd').rand(0,1000).'.csv';
        $this->exportCSV($fileName,$header,$content);

    }


}
