<?php
namespace ApiInterface\Controller\Version1_0;
use Think\Controller;

class DictController extends PublicController
{
    public function __construct() {
        parent::__construct();
    }
    /**
     * @描述：获取学科列表
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getCourseList()
    {
        $this->ajaxReturn(array('status' => 200,'result' => D('Dict_course')->getCourseList()));
    }
    /**
     * @描述：获取年级列表
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getGradeList()
    {
        $this->ajaxReturn(array('status' => 200,'result' => D('Dict_grade')->getGradeList()));
    }

}