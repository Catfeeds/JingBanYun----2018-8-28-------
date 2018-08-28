<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify; 

class GradeController extends Controller
{   
 
    
    public function __construct() {
        parent::__construct();   
        $this->assign('oss_path',C('oss_path'));
    }
          
    //年级管理
    function gradeMgmt()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '字典数据管理');
        $this->assign('nav', '年级管理');
        $this->assign('subnav', '年级列表');

        $Model = M('dict_grade');
        $result = $Model->where('is_delete = 0')->select();

        $this->assign('list', $result);

        $this->display();
    }
    
    function createEditGrade()
    {
        $model = M('dict_grade');
        $id = getParameter('id','int',false);
        if($_POST)
        {
            $data['grade'] = getParameter('grade','str');
            $data['code'] = getParameter('code','int');
            $data['chinese_code'] = getParameter('chinese_code','str');
            $data['short_name'] = getParameter('short_name','str');
            if(empty($id)) //add
            {
             if($model->where('code='.$data['code'])->find())
               $this->error('年级编号已经存在');
                 M('dict_grade')->add($data);
                redirect(U('Grade/gradeMgmt'));
            }
            else
            {
               $model->where('id='.$id)->save($data);
                redirect(U('Grade/gradeMgmt'));
            }
        }
        else
        {
            if(!empty($id)) {
                $data = $model->where('id=' . $id)->find();
                $this->assign('data', $data);
                $this->assign('subnav', '修改年级');
            }
            else
                $this->assign('subnav', '添加年级');
            $this->assign('module', '年级管理');
            $this->assign('nav', '年级管理');

            $this->display();
        }

    }
    
    function falseDeleteGrade()
    {
        $model = M('dict_grade');
        $id = getParameter('id','int');
        $data['is_delete'] = 1;
        $model->where('id='.$id)->save($data);
        $this->showMessage(200,'删除成功',array());
    }
}
