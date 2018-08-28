<?php
namespace Home\Controller;

use Think\Controller;

class HelpController extends PublicController
{
    public function __construct() {
        parent::__construct();
        header("Content-type: text/html; charset=utf-8");
		$this->assign('oss_path',C('oss_path'));
    }
    public function document()
    {
        $studentsession = session('student');
        $parentsession = session('parent');
        $teachersession = session('teacher');
        if ( !empty($studentsession) ) {
            $role = 3;
        }
        if ( !empty($parentsession) ) {
            $role = 4;
        }
        if ( !empty($teachersession) ) {
            $role = 2;
        }

        $this->assign('role',$role);
        $this->display('document');
    }

    public function about_app()
    {
        $this->display();
    }

    public function share_app()
    {
        $this->display();
    }

    public function share_teacher()
    {
        $this->display();
    }

    public function countuser()
    { 
        $sign = I('request.sign');
        if($sign!='bb@fdsafdsa..')
        {
            exit;
        }

        //$sdate = I('request.start_date');
        //$edate = I('request.end_date');
        $sdate=  getParameter('start_date','str');
        $edate=  getParameter('end_date','str');

        echo '截止今日用户统计：</br></br>';

        //统计平台用户总数
        $TAPI = M('auth_teacher');
        $c1 = $TAPI->select();
        echo '教师总用户数：'.count($c1).'</br>';
        $SAPI = M('auth_student');
        $c2 = $SAPI->select();
        echo '学生总用户数：'.count($c2).'</br>';
        $PAPI = M('auth_parent');
        $c3 = $PAPI->select();

        echo '家长总用户数：'.count($c3).'</br>';
        echo '平台用户总数：'.(count($c1)+count($c2)+count($c3)).'</br>';
        echo '－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－－</br>';
        $a = 0;

        $sdate = strtotime($sdate);
        $edate = strtotime($edate);
        $total = 0;
        for($i=0;$i>=0;$i++)
        {
            if($i>0)$sdate += 60*60*24;
            $ed = $sdate+(60*60*24);

            if($sdate>$edate)
            {
                break;
            }

            $API = M('auth_teacher');
            $c1 = $API->where('create_at>'.$sdate.' and create_at<'.$ed)->select();
            $API = M('auth_student');
            $c2 = $API->where('create_at>'.$sdate.' and create_at<'.$ed)->select();
            $API = M('auth_parent');
            $c3 = $API->where('create_at>'.$sdate.' and create_at<'.$ed)->select();
            echo date('Y-m-d',$sdate).'到'.date('Y-m-d',$ed).'零点。增加：'.count($c1).'名教师;'.count($c2).'名学生;'.count($c3).''.'名家长;';
            $total += (count($c1)+count($c2)+count($c3));

            $API = M('auth_teacher');
            $tc1 = $API->where('create_at<'.$ed)->select();
            $API = M('auth_student');
            $tc2 = $API->where('create_at<'.$ed)->select();
            $API = M('auth_parent');
            $tc3 = $API->where('create_at<'.$ed)->select();
            echo date('Y-m-d',$sdate).'日增长总和为：'.(count($c1)+count($c2)+count($c3))."; 截止当日用户数：老师：".count($tc1)."学生：".count($tc2)."家长：".count($tc3)."总用户：".(count($tc1)+count($tc2)+count($tc3))."</br>";
        }

        echo "共增长:{$total}名用户";
    }

    public function vedio()
    {
        $this->display();
    }

    /**
     * 升级须知 for App
     */
    public function upgrade_app()
    {
        $this->display();
    }
}