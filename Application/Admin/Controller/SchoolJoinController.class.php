<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/19 0019
 * Time: 上午 9:54
 */
namespace Admin\Controller;
use Think\Controller;

class SchoolJoinController extends Controller
{
    /*
     *学校加入列表
     */
    public function SchoolJoinList(){
        $pageIndex = getParameter('p','int',false);
        if(empty($pageIndex))
            $pageIndex = 1;
        $list = D('School_join')->resource_all($count,$pageIndex);
        $Page = new \Think\Page($count['num'], C('PAGE_SIZE_FRONT'));
        $show = $Page->show();
        $this->assign('page',$show);
        $this->assign('list',$list);
        $this->assign('oss_path',C('oss_path'));
        $this->display();
    }
}