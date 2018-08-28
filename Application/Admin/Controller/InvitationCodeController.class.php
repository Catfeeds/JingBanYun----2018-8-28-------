<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify; 
use Common\Common\CSV;

class InvitationCodeController extends Controller
{   
 
    public function __construct() {
        parent::__construct();   
        $this->activityModel = D('Social_activity');
        $this->assign('oss_path',C('oss_path'));
    }
          
    
    //邀请码管理列表
    public function codeList() {
        $this->assign('module', '活动邀请码管理');
        $this->assign('nav', '活动邀请码管理');
        $this->assign('subnav', '活动邀请码列表');

        $queryArray['keyword'] = getParameter('keyword', 'str', false);
        $queryArray['is_generate'] = getParameter('is_generate', 'int', false);

        if (!empty($queryArray['keyword'])) {
            $where['title|invitation_code'] = array('like', '%' . $queryArray['keyword'] . '%');
            $this->assign('keyword', $queryArray['keyword']);
        }
        if (!empty($queryArray['is_generate'])) {
            $where['is_generate'] = $queryArray['is_generate'];
            $this->assign('is_generate', $queryArray['is_generate']);
        }


        $result = $this->activityModel->getCodeList( $where,$queryArray );
        
        $this->assign('data_page', $result['data_page']);
        $this->assign('list', $result['result']);
        $this->assign('page', $result['page']);
        $this->display_nocache();
    }
    
    //导出邀请码
    public function exportAll() {
        set_time_limit(0);
        if (!empty($queryArray['keyword'])) {
            $where['title|invitation_code'] = array('like', '%' . $queryArray['keyword'] . '%');
            $this->assign('keyword', $queryArray['keyword']);
        }
        if (!empty($queryArray['is_generate'])) {
            $where['is_generate'] = $queryArray['is_generate'];
            $this->assign('is_generate', $queryArray['is_generate']);
        }
        $all = 'all';
        $result = $this->activityModel->getCodeList( $where,$queryArray,$all );   
        
        $count=count($result);
        $now_page=isset($_GET['p'])?$_GET['p']:1;          
        $count_page=ceil($count/C('PAGE_SIZE_FRONT'));  
        $mo_rows=$count%C('PAGE_SIZE_FRONT');
        $page_i=($count_page-$now_page)==0?$page_i=0:($count_page-$now_page);
        $total_rows=$page_i*C('PAGE_SIZE_FRONT')+((C('PAGE_SIZE_FRONT')-(C('PAGE_SIZE_FRONT')-$mo_rows)));
        
        

        $str="序号,活动名称,是否生成邀请码,邀请码个数\n";
        $str=iconv('utf-8','gb2312', $str);
        foreach($result as $v){
            //$id=iconv('utf-8','gbk', $v['id']);
            $title=iconv('utf-8','gbk', $v['title']);

            if ($v['is_generate'] == 1) {
                $is_generate=iconv('utf-8','gbk', '否');
            } else {
                $is_generate=iconv('utf-8','gbk', '是');
            }

            $snum=iconv('utf-8','gbk', $v['snum']);
            $str.=($total_rows--).",".$title.",".$is_generate.",".$snum."\n";
        }
        $filename=date('Ymd').rand(0,1000).'InvitationCode'.'.csv';
        $csv=new CSV();
        $csv->downloadFileCsv($filename,$str);

    }
    
    //查看活动邀请码详情
    public function lookCodeDetails() {
        $this->assign('module', '活动邀请码管理');
        $this->assign('nav', '活动邀请码管理');
        $this->assign('subnav', '活动邀请码详情');

        $queryArray['id'] = getParameter('id', 'int', false);
        $queryArray['is_generate'] = getParameter('is_generate', 'str', false);
        if (!empty($queryArray['id'])) {
            $where['activity_id'] = $queryArray['id'];
        }
        if (!empty($queryArray['is_generate'])) {
            $where['status'] = $queryArray['is_generate'];
            $this->assign('is_generate', $queryArray['is_generate']);
        }
        $activity_result=$this->activityModel->getActivityTitle($queryArray['id']);
        $result=array();
        if(!empty($activity_result)){
            $result = $this->activityModel->getactivityCodeDetails( $where );
        }
         

        $this->assign('tid', $queryArray['id']);
        $this->assign('list', $result);
        $this->assign('titles', $activity_result['title']);
        $this->display();
    }

    //删除邀请码
    public function deleteCode() {
        $queryArray['id'] = getParameter('id', 'int', false);
        $result = $this->activityModel->delCode( $queryArray['id'] );
        if ($result !=1000) {
            if ( $result ) {
                $this->ajaxReturn('success');
            } else {
                $this->ajaxReturn('error');
            }
        } else {
            $this->ajaxReturn('back');
        }

    }

    //导出全部邀请码
    public function exportAllCode() {
        $queryArray['id'] = getParameter('id', 'int', false);
        $where['activity_id'] = $queryArray['id'];
        $result = $this->activityModel->getactivityCodeDetails( $where );

        $str="序号,邀请码,是否使用,状态\n";
        $str=iconv('utf-8','gb2312', $str);
        foreach($result as $v){
            $id=iconv('utf-8','gbk', $v['id']);
            $invitation_code=iconv('utf-8','gbk', $v['invitation_code']);

            if ($v['status'] == 1) {
                $is_generate=iconv('utf-8','gbk', '未使用');
            } else {
                $is_generate=iconv('utf-8','gbk', '已使用');
            }

            if ($v['status'] == 1) {
                $is_z=iconv('utf-8','gbk', '正常');
            } else {
                $is_z=iconv('utf-8','gbk', '作废');
            }

            $str.=$id.",".$invitation_code.",".$is_generate.",".$is_z."\n";
        }
        $filename=date('Ymd').rand(0,1000).'Code'.'.csv';
        $csv=new CSV();
        $csv->downloadFileCsv($filename,$str);

    }

    //添加邀请码
    public function addCode() {
        $codenum = getParameter('codenum', 'int', false);
        $activity_id = getParameter('activity_id', 'int', false);

        $is_true = true;
        for ($i=0;$i<$codenum;$i++) {
            $code = getCodeRand();
            $id = $result = $this->activityModel->addCodeModel( $code,$activity_id );
            if ( $id == false ) {
                $is_true = false;
            }
        }

        if ( $is_true ) {
            M('social_activity_invitation_code')->commit();
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('error');
        }

    }

    //生成邀请码
    public function getCodeView(){
        $codenum = getParameter('codenum', 'int', false);

        $codearr = array();
        for ($i=0;$i<$codenum;$i++) {
            $codearr[] = getCodeRand();
        }
        $this->ajaxReturn($codearr,'json');
    }

    //根据活动获取邀请码接口
    public function getApiCode() {
        $queryArray['id'] = getParameter('id', 'int', false);
        $where['activity_id'] = $queryArray['id'];
        $result = $this->activityModel->getactivityCodeDetails( $where );
        $this->ajaxReturn($result,'json');

    }
}
