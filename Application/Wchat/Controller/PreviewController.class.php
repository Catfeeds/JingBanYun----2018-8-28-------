<?php
namespace Wchat\Controller;
use Think\Controller;

class PreviewController extends Controller
{

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = D('Weixin_push');
        $this->assign('oss_path', C('oss_path'));
    }

    /*
     *预览
     */
    public function preview(){
        $id = I('id');
        $type = I('type');
        $status = I('status');
        if($status === 'one'){
            if($type == '1') {
                $where['weixin_column.status'] = '1';
            }else{
                $where['weixin_column.status'] = '1';
                $where['weixin_column_title_contact.status_contact'] = '1';
            }
        }
        $where['weixin_column.id'] = $id;
        if($type == '1'){
            if($status === 'one') {
                $where['weixin_push_status'] = '1';//weixin_push表中的weixin_push_status字段
            }
            $resources = $this->model->getAllById($where,$type);//标题类型
            $this->assign('list',$resources);
            $this->display('primaryChinese');
        }else{
            //列表类型
            $blockResources = $this->model->getListAll($where);
            //var_dump($blockResources);die;
            foreach ($blockResources as $k=>$item){
                $wheres['weixin_column_title_contact_id'] = $item['ctcid'];
                $wheres['weixin_column_id'] = $id;
                if($status === 'one') {
                    $wheres['weixin_push_status'] = '1';
                }
                $blockResources[$k]['content'] = $this->model->getAllById($wheres,$type);
            }
            //var_dump($blockResources);die;
            $this->assign('list',$blockResources);
            if($id == '1') {
                $this->display('biologyChemistryHistory');
            }elseif ($id == '3'){
                $this->display('primaryMath');
            }elseif($id == '4'){
                $this->display('middleMathChinese');
            }elseif ($id == '5'){
                $this->display('english');
            }

        }

    }

}