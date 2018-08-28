<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify; 

class MonitorController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));
    }

    //二维码后台管理
    public function getQrcodeInfo() {
        $row = D('Monitor')->getQrcodeModel();
        $this->assign('data',$row);
        $this->display();
    }

    //京版资源和京版活动的统计信息
    public function getShareInfo() {
        $row = D('Monitor')->getShareModel();

        $this->assign('list',$row);
        $this->display();
    }

    //京版云活动新增用户数
    public function activitiAddUser() {
        $row = D('Monitor')->getActivitiUserModel();
        $this->assign('list',$row);
        $this->display();
    }

    //轮播图统计
    public function AdInfo() {
        $row = D('Monitor')->getAdCountModel();
        $this->assign('list',$row);
        $this->display();
    }

    //区县统计
    public function regionCount() {

        $filter['province']=getParameter('province','int',false);
        $filter['city']=getParameter('city','int',false);
        $filter['district']=getParameter('district','int',false);
        $filter['school']=getParameter('school','int',false);
        $filter['course']=getParameter('course','int',false);

        if(!empty($filter['province']))   $condition['dict_schoollist.provice_id']= $filter['province'];
        if(!empty($filter['city']))   $condition['dict_schoollist.city_id']= $filter['city'];
        if(!empty($filter['district']))   $condition['dict_schoollist.district_id']= $filter['district'];

        if(!empty($filter['school']))   $condition['dict_schoollist.id']= $filter['school'];

        if(!empty($filter['course']))   $condition['dict_course.id']=$filter['course'];

        $school_model=D('Dict_schoollist');

        $course_model=D('Dict_course');


        $city_result=array();
        $district_result=array();
        $school_result=array();
        if(!empty($filter['province']))   $city_result=$school_model->getCityByProvince($filter['province']);
        if(!empty($filter['city']))       $district_result=$school_model->getDistrictByCity($filter['city']);
        if(!empty($filter['district']))   $school_result=$school_model->getSchoolByDistrict($filter['district']);

        $province_result=$school_model->getProvince();

        $this->assign('province_list',$province_result);
        $this->assign('city_list',$city_result);
        $this->assign('district_list',$district_result);
        $this->assign('school_list',$school_result);
        $course_result=$course_model->getCourseList();
        $this->assign('course_list',$course_result);

        $teachercount = D('Monitor')->getTeacherCount( $condition );
        $teachercountall = D('Monitor')->getTeacherCountAll();

        unset($condition['dict_course.id']); //学生和家长不需要查询学科

        $stucount = D('Monitor')->getStudentCount( $condition );
        $stucountall = D('Monitor')->getStudentCountAll();

        $parentcount = D('Monitor')->getParentdentCount( $condition );
        $parentcountall = D('Monitor')->getParentdentCountAll();




        $this->assign('stucount',$stucount);
        $this->assign('stucountall',$stucountall);
        $this->assign('parentcount',$parentcount);
        $this->assign('parentcountall',$parentcountall);
        $this->assign('teachercount',$teachercount);
        $this->assign('teachercountall',$teachercountall);


        //搜素结果
        $this->assign('province',$filter['province']);
        $this->assign('city',$filter['city']);
        $this->assign('district',$filter['district']);
        $this->assign('school',$filter['school']);
        $this->assign('school_name',$filter['school_name']);
        $this->assign('course',$filter['course']);

        $this->display();
    }

    //站内信统计
    public function messageCount() {
        $roleMessage=getParameter('roleMessage','int',false);
        if (empty($roleMessage)) {
            $roleMessage = 2;
        }
        if ($roleMessage==2) { //教师
            $res = D('Monitor')->getTeacherMessage(2);
        }

        if ($roleMessage==3) { //学生
            $res = D('Monitor')->getStuMessage(3);
        }

        if ($roleMessage==4) { //家长
            $res = D('Monitor')->getParentMessage(4);
        }


        $this->assign('list',$res['list']);
        $this->assign('page',$res['show']);

        $this->assign('roleMessage',$roleMessage);
        $this->display();
    }

    //统计每个教师的学生数量
    public function getTeacherStuCount(){
        $keyword=getParameter('keyword','str',false);
        $strat_date= getParameter('start', 'str',false);
        $end_date= getParameter('end', 'str',false);

        $filter['start'] = $strat_date;
        $filter['end'] = $end_date;

        if (!empty($filter['start'])) {
            $startime = strtotime($filter['start']);
            $startime = date('Y-m-d 00:00:00', $startime);
            $where['biz_class_student.create_at'] = array('egt',strtotime($startime));
        }

        if (!empty($filter['end'])) {
            $endtime = strtotime($filter['end']);
            $endtime = date('Y-m-d 23:59:59', $endtime);
            $where['biz_class_student.create_at'] = array('elt',strtotime($endtime));
        }

        if (!empty($filter['start']) && !empty($filter['end']) ){
            $startime = strtotime($filter['start']);
            $startime = date('Y-m-d 00:00:00', $startime);
            $endtime = strtotime($filter['end']);
            $endtime = date('Y-m-d 23:59:59', $endtime);
            $where['biz_class_student.create_at'] = array(array('egt',strtotime($startime)),array('elt',strtotime($endtime))) ;
        }

        if (!empty($filter['start'])) {
            $startime = strtotime($filter['start']);
            $startime = date('Y-m-d 00:00:00', $startime);
            $where['biz_class_student.create_at'] = array('egt',strtotime($startime));
        }

        if (!empty($filter['end'])) {
            $endtime = strtotime($filter['end']);
            $endtime = date('Y-m-d 23:59:59', $endtime);
            $where['biz_class_student.create_at'] = array('elt',strtotime($endtime));
        }

        if (!empty($filter['start']) && !empty($filter['end']) ){
            $startime = strtotime($filter['start']);
            $startime = date('Y-m-d 00:00:00', $startime);
            $endtime = strtotime($filter['end']);
            $endtime = date('Y-m-d 23:59:59', $endtime);
            $where['biz_class_student.create_at'] = array(array('egt',strtotime($startime)),array('elt',strtotime($endtime))) ;
        }

        if(!empty($keyword)) {
            $where['auth_teacher.name|auth_teacher.telephone'] = array('like', '%' . $keyword . '%');
            $this->assign('keyword',$keyword);
        }

        $res = D('Monitor')->getTeacherStuCountModel($where);

        $this->assign('start',$strat_date);
        $this->assign('end',$end_date);

        $this->assign('list',$res['list']);
        $this->assign('page',$res['show']);

        $this->display();
    }

    public function PcCount() {

        $strat_date= getParameter('start', 'str',false);

        if (!empty($strat_date)) {
            $this->assign('start',$strat_date);
            $strat_date = intval(str_replace('-','',$strat_date));
            $where['dateorder'] = $strat_date;
        }
        $row = D('Monitor')->PcCountModel(1,$where);


        $this->assign('list',$row['list']);
        $this->assign('page',$row['show']);
        $this->display();
    }

    public function qiPaoCount() {
        $strat_date= getParameter('start', 'str',false);

        if (!empty($strat_date)) {
            $this->assign('start',$strat_date);
            $strat_date = intval(str_replace('-','',$strat_date));
            $where['dateorder'] = $strat_date;
        }

        $row = D('Monitor')->PcCountModel(2,$where);

        $this->assign('list',$row['list']);
        $this->assign('page',$row['show']);
        $this->display();
    }
}
