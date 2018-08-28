<?php
namespace School\Controller;
use Think\Controller;
use Think\Verify;

class MemoController extends Controller
{   
    public function __construct() {
        parent::__construct();  
        $this->model=M('memorandum');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }
    
    function memoList(){
        $this->display();
    }

    /*
     *描述：备忘录添加接口
     */
    public function memorandumAdd(){
        $data['memorandum_title'] = getParameter('memorandum_title','str');
        $data['memorandum_content'] = I('memorandum_content');
        $data['auth_admin_id'] = session('school.id');
        $this->model->startTrans();
        $addStatus = $this->model
            ->add($data);
        if($addStatus == false){
            $this->model->rollback();
            $this->ajaxreturn(array('status' => 400));
        }else{
            $this->model->commit();
            $this->ajaxreturn(array('status' => 200));
        }
    }

    /*
     *描述：备忘录列表接口
     */
    public function getMemorandumList(){
        $where['auth_admin_id'] = session('school.id');
        $result = $this->model
            ->field('id,memorandum.creat_time,memorandum_title,memorandum_content,DATE_FORMAT(creat_time,"%Y-%m") month,DATE_FORMAT(creat_time,"%m-%d") day')
            ->where($where)
            ->order('id desc')
            ->select();
        foreach ($result as $k=>$item) {
            if(date('Y-m',strtotime($item['creat_time'])) == $item['month']){
                $new[$item['month']][] = $item;
            }
        }
        $this->ajaxreturn(array('status' => 200,'data' => $new));
    }

    function createMemo(){
        $this->display();
    }

    function memoDetails(){
        $this->id = I('id');
        $this->display();
    }

    /*
     *描述：详情的接口
     */
    public function getDetail(){
        $id = getParameter('id','int');
        $where['id'] = $id;
        $result = $this->model
            ->where($where)
            ->find();
        $result['memorandum_content'] = html_entity_decode($result['memorandum_content']);
        $this->ajaxreturn(array('status' => 200,'data' => $result));
    }
}
