<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/7 0007
 * Time: 下午 6:01
 */

namespace Admin\Controller;

use Common\Common\CSV;
use Think\Controller;

class SignUpController extends Controller
{
    private $model;
    private $pageSize;

    public function __construct()
    {
        parent::__construct();
        $this->model = D('Activity_wx');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
        if (!session('?admin')) redirect(U('Login/login'));
    }

    public function SignUpList()
    {
        $result = $this->model->getSignUpList();
        $this->assign('list', $result['data']);
        $this->assign('page', $result['page']);
        $this->display();
    }

    /*
     *获取详细信息
     */
    public function SignUpDetail()
    {
        $where = array();
        $where['id'] = $_GET['id'];
        //var_dump($_GET['id']);die;
        $result = $this->model->getSignUpDetail($where);
        //var_dump($result);die;
        $this->assign('data', $result);
        $this->display();
    }

    /*
     * 导出全部学校管理员
     */
    public function SignUpExportAll()
    {
        $data = $this->model->getSignUpAll();
        $str = "创建时间,学生姓名,学校名字,年级和班级,指导老师,联系方式,内容\n";
        $str = iconv('utf-8', 'gbk', $str);
        foreach ($data as $v) {
            $creat_time = iconv('utf-8', 'gbk', $v['creat_time']);
            $student_name = iconv('utf-8', 'gbk', $v['student_name']);
            $school_name = iconv('utf-8', 'gbk', $v['school_name']);
            $class_name = iconv('utf-8', 'gbk', $v['class_name']);
            $class_teacher = iconv('utf-8', 'gbk', $v['class_teacher']);
            $telephone = iconv('utf-8', 'gbk', $v['telephone']);
            $content = iconv('utf-8', 'gbk', $v['content']);
            $str .= $creat_time . "," . $student_name . "," . $school_name . "," . $class_name . "," . $class_teacher . "," . $telephone . "," . $content . "\n";
        }
        $filename = date('Ymd') . rand(0, 1000) . 'SignUp' . '.csv';
        $csv = new CSV();
        //export disable
        //$csv->downloadFileCsv($filename, $str);
    }
}