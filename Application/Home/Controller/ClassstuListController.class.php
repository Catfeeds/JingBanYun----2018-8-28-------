<?php
namespace Home\Controller;

use Think\Controller;
use Common\Common\CSV;

class ClassstuListController extends PublicController{
	    //班级管理
    public $c_a = '';
    function __construct(){
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));
        $this->c_a = CONTROLLER_NAME."_".ACTION_NAME;
        set_time_limit(0);

        $student = session('student');
        if(!$student) {
            $this->redirect(U('Index/index'));
        }
    }

    //点击班级成员获取信息
    public function isClassinfo() {
        $class_status = I('class_status');
        if ($class_status==2) { //自建ajax返回值
            $html = $this->getStuList();
            $this->ajaxReturn($html);
        } else { //校建班ajax返回值
            $html = $this->getStuandTeachList();
            $this->ajaxReturn($html);
        }
    }

    //学生的列表
    public function getStuList() {

        $classid = I('classid');
        $keyword = I('keyword');
        if (empty($classid)) {die;}

        $mapwhere = array(
            'biz_class_student.class_id' => $classid,
            'biz_class_student.status' => 2,
        );

        if (!empty($keyword)) {
            $mapwhere['auth_student.student_name|auth_student.parent_tel'] = array('like', '%' . $keyword . '%');
        }



        $Model = M('biz_class_student');
        $result = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->field('auth_student.parent_tel,auth_student.student_name,auth_student.sex,auth_student.sex,auth_student.avatar,auth_student.email,biz_class_student.student_id')
            ->where( $mapwhere )
            ->order('biz_class_student.create_at desc')
            ->select();

        for($i=0;$i<sizeof($result);$i++) {
            $result[$i]['birth_date'] = date("Y-m-d",$result[$i]['birth_date']);
            if(empty($result[$i]['avatar']) || $result[$i]['avatar'] =='') {
                $result[$i]['avatar'] = 'default.jpg';
            }

        }

        $idmap['id'] = $classid;
        $row = M('biz_class')->where( $idmap )->field('class_code,flag,class_status')->find();

        $countstu = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->where("biz_class_student.class_id=$classid and biz_class_student.status = 2")
            ->count();

        $teacher_count = M('biz_class_teacher')
            ->where("class_id=$classid")
            ->count();

        if ($row['flag'] ==0) {
            $row['flag_name'] = '停用';
        }
        if ($row['flag'] ==1) {
            $row['flag_name'] = '正常';
        }
        if ($row['flag'] ==2) {
            $row['flag_name'] = '移交中';
        }
        $data_stu['class_info'] = $row;
        $data_stu['stu_count'] = $countstu;
        $data_stu['teacher_count'] = $teacher_count;
        $data_stu['count'] = $teacher_count+$countstu;
        $this->assign('data_stu',$data_stu);

        $this->assign('list',$result);
        $this->assign('classid',$classid);

        $html = $this->fetch('stuandteacher');
        return $html;
    }

    //获取学生和老师的列表
    public function getStuandTeachList() {

        $classid = I('classid');
        $keyword = I('keyword');

        if (empty($classid)){die;}

        $mapwhere = array(
            'biz_class_student.class_id' => $classid,
            'biz_class_student.status' => 2,
        );

        if (!empty($keyword)) {
            $mapwhere['auth_student.student_name|auth_student.parent_tel'] = array('like', '%' . $keyword . '%');
        }



        $Model = M('biz_class_student');
        $result = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->field('auth_student.parent_tel,auth_student.student_name,auth_student.sex,auth_student.sex,auth_student.avatar,auth_student.email,biz_class_student.student_id')
            ->where( $mapwhere )
            ->order('biz_class_student.create_at desc')
            ->select();

        for($i=0;$i<sizeof($result);$i++) {
            $result[$i]['birth_date'] = date("Y-m-d",$result[$i]['birth_date']);
            if(empty($result[$i]['avatar']) || $result[$i]['avatar'] =='') {
                $result[$i]['avatar'] = 'default.jpg';
            }

        }

        $idmap['id'] = $classid;
        $row = M('biz_class')->where( $idmap )->field('class_code,flag,class_status,school_id')->find();

        $schoolmap['id'] =  $row['school_id'];
        $school_status = M('dict_schoollist')
            ->where( $schoolmap )->find();

        $row['s_flag'] = $school_status['flag'];

        if ($row['flag'] ==0) {
            $row['flag_name'] = '停用';
        }

        if ($row['flag'] ==1 ) {

            if ($row['s_flag']==1) {
                $row['flag_name'] = '正常';
            } else {
                $row['flag_name'] = '停用';
                $row['flag'] = 0;
            }

        }

        if ($row['flag'] ==2) {
            $row['flag_name'] = '移交中';
        }

        $countstu = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->where("biz_class_student.class_id=$classid and biz_class_student.status = 2")
            ->count();

        $teacher_count = M('biz_class_teacher')
            ->where("class_id=$classid")
            ->count();


        $data_stu['class_info'] = $row;
        $data_stu['stu_count'] = $countstu;
        $data_stu['teacher_count'] = $teacher_count;
        $data_stu['count'] = $teacher_count+$countstu;
        $this->assign('data_stu',$data_stu);

        $this->assign('list',$result);
        $this->assign('classid',$classid);

        $html = $this->fetch('stuandteacher');
        return $html;
    }

    public function classList()
    {  
//        if (!session('?teacher')) redirect(U('Index/index'));
//        
//        $id = $_GET['auth_id'];
//        $isAuth = $this->isAuth($this->c_a);
//        if (!$isAuth) { //如果访问的模块没有权限
//            redirect(U('Teach/index1?auth_error=1'));
//        }
        A('Home/Common')->getUserIdRole($userId,$role);
        A('Home/Common')->authJudgement();
	    switch($role)
		{
                        case ROLE_TEACHER:layout('teacher_layout_3'); 
				              break;
			case ROLE_STUDENT:layout('student_layout_3');
				              break;
			case ROLE_PARENT:layout('parent_layout_3');
				              break;
		}   
        $this->assign('module', '班级行');
        $this->assign('nav', '班级管理');
        $this->assign('subnav', '我的班级');
        $this->assign('navicon', 'wodebanji');

        $Model = M('biz_class_student');
        

        $check['biz_class.flag'] =  2;

        $where['biz_class_student.student_id'] =  session("student.id");
        $where['biz_class.is_delete'] =  0;
        $where['biz_class_student.status'] =  2;

        $result = $Model
            ->join('left join biz_class on biz_class.id=biz_class_student.class_id')
            ->join('left join biz_class_teacher ON biz_class_teacher.class_id = biz_class.id')
            ->join('left join auth_teacher ON auth_teacher.id = biz_class_teacher.teacher_id')
            ->join('left join dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('auth_teacher.school_id as ats_id,biz_class_teacher.teacher_id,biz_class.*,dict_schoollist.school_name,dict_grade.grade,dict_grade.id as did')
            ->where($where)
            ->group('biz_class.id')
            ->order('biz_class.create_at desc')
            ->select();

        foreach ($result as $k=>$v) {

            if ($v['class_status'] ==1) {
                $schoolmap['id'] =  $v['school_id'];
                $school_status = M('dict_schoollist')
                    ->where( $schoolmap )->find();
            } else {
                $schoolmap['id'] =  $v['ats_id'];
                $school_status = M('dict_schoollist')
                    ->where( $schoolmap )->find();
            }

            $result[$k]['s_flag'] = $school_status['flag'];

            if ($v['flag'] ==0) {
                $result[$k]['flag'] = 0;
            }

            if ($v['flag'] ==1 ) {

                if ($result[$k]['s_flag']==1) {
                    $result[$k]['flag'] = 1;
                } else {
                    $result[$k]['flag']=0;
                }
            }
            if ($v['flag'] ==2) {
                $result[$k]['flag']=2;
            }
        }

        $this->assign('list', $result);

        //年级不为空求出班级
        if(!empty($filter['grade_id'])){
            $class_model=M('biz_class');
            $classmap['grade_id'] = $filter['grade_id'];
            $classmap['class_teacher_id'] = session("teacher.id");
            $class_list = $class_model->where($classmap)->field('id,name')->select();
        }

        $grade_model = M('dict_grade');
        $grade_list = $grade_model->select();
        $this->assign('grade_list', $grade_list);
        $this->assign('class_list', $class_list);
        

        $this->display();
    }

    //晒选查找老师
    public function findTeacherList() {
        $classid = I('classid');

        $keyword = I('keyword');

        $mapwhere = array(
            'biz_class_teacher.class_id' => $classid,
        );

        if (!empty($keyword)) {
            $mapwhere['auth_teacher.name|auth_teacher.telephone'] = array('like', '%' . $keyword . '%');
        }

        $Model = M('biz_class_teacher');
        $result = $Model
            ->join('left join auth_teacher on auth_teacher.id=biz_class_teacher.teacher_id')
            ->join('left join dict_course on dict_course.id=biz_class_teacher.course_id')
            ->field('auth_teacher.name,auth_teacher.telephone,auth_teacher.sex,auth_teacher.avatar,auth_teacher.email,biz_class_teacher.teacher_id,dict_course.course_name')
            ->where( $mapwhere )
            ->order('biz_class_teacher.create_at desc')
            ->select();



        for($i=0;$i<sizeof($result);$i++) {
            $result[$i]['birth_date'] = date("Y-m-d",$result[$i]['birth_date']);
            if(empty($result[$i]['avatar']) || $result[$i]['avatar'] =='') {
                $result[$i]['avatar'] = 'default.jpg';
            }

        }



        $idmap['biz_class.id'] = $classid;
        //$row = M('biz_class')->where( $idmap )->field('class_code,flag,class_status,school_id,')->find();

        $row = M('biz_class')
            ->join('left join dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('left join biz_class_teacher on biz_class_teacher.class_id=biz_class.id')
            ->join('left join auth_teacher on auth_teacher.id=biz_class_teacher.teacher_id')
            ->where( $idmap )
            ->field('auth_teacher.school_id as ats_id,biz_class_teacher.teacher_id,biz_class.school_id,biz_class.class_code,biz_class.flag,biz_class.class_status,biz_class.name,biz_class.class_teacher,dict_grade.grade')
            ->find();

        if ($row['class_status'] ==1) {
            $schoolmap['id'] =  $row['school_id'];
            $school_status = M('dict_schoollist')
                ->where( $schoolmap )->find();
        } else {
            $schoolmap['id'] =  $row['ats_id'];
            $school_status = M('dict_schoollist')
                ->where( $schoolmap )->find();
        }


        $row['s_flag'] = $school_status['flag'];

        if ($row['flag'] ==0) {
            $row['flag_name'] = '停用';
        }

        if ($row['flag'] ==1 ) {

            if ($row['s_flag']==1) {
                $row['flag_name'] = '正常';
            } else {
                $row['flag_name'] = '停用';
                $row['flag'] = 0;
            }

        }

        if ($row['flag'] ==2) {
            $row['flag_name'] = '移交中';
        }

        $Model = M('biz_class_student');
        $countstu = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->where("biz_class_student.class_id=$classid and biz_class_student.status = 2")
            ->count();

        $bctModel = M('biz_class_teacher');
        $teachercount = $bctModel
            ->where("biz_class_teacher.class_id=$classid")
            ->count();

        $data_stu['class_info'] = $row;
        $data_stu['stu_count'] = $countstu;
        $data_stu['tea_count'] = $teachercount;
        $count = $countstu+$teachercount;

        $this->assign('count',$count);
        $this->assign('data_stu',$data_stu);
        $this->assign('classid',$classid);
        $this->assign('list',$result);

        $html = $this->fetch('findteacher');
        $this->ajaxReturn($html);
    }


    public function setClassButton(){
        $classid = I('classid');
        $Model = M('biz_class_student');

        $idmap['biz_class.id'] = $classid;
        $row = M('biz_class')
            ->join('left join dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('left join biz_class_teacher on biz_class_teacher.class_id=biz_class.id')
            ->join('left join auth_teacher on auth_teacher.id=biz_class_teacher.teacher_id')
            ->where( $idmap )
            ->field('auth_teacher.school_id as ats_id,biz_class_teacher.teacher_id,biz_class.school_id,biz_class.class_code,biz_class.flag,biz_class.class_status,biz_class.name,biz_class.class_teacher,dict_grade.grade')
            ->find();

        $countstu = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->where("biz_class_student.class_id=$classid and biz_class_student.status = 2")
            ->count();

        $dengdaistu = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->where("biz_class_student.class_id=$classid and biz_class_student.status = 1")
            ->count();

        if ($row['class_status'] ==1) {
            $schoolmap['id'] =  $row['school_id'];
            $school_status = M('dict_schoollist')
                ->where( $schoolmap )->find();
        } else {
            $schoolmap['id'] =  $row['ats_id'];
            $school_status = M('dict_schoollist')
                ->where( $schoolmap )->find();
        }


        $row['s_flag'] = $school_status['flag'];

        if ($row['flag'] ==0) {
            $row['flag_name'] = '停用';
        }

        if ($row['flag'] ==1 ) {

            if ($row['s_flag']==1) {
                $row['flag_name'] = '正常';
            } else {
                $row['flag_name'] = '停用';
                $row['flag'] = 0;
            }

        }

        if ($row['flag'] ==2) {
            $row['flag_name'] = '移交中';
        }

        $data_stu['class_info'] = $row;
        $data_stu['stu_count'] = $countstu;
        $data_stu['wating_stu'] = $dengdaistu;
        $this->assign('data_stu',$data_stu);
        $this->assign('classid',$classid);



        $html = $this->fetch('setClassInfo');

        $this->ajaxReturn($html);
    }

    //删除自建班班级
    public function deleteClassAlldata(){
        $classid = I('classid');
        $status = I('status');
        $errorInfo = '';
        $userId = session("student.id");

        $biz = D('Biz_class')->leaveClass($classid,3,$userId,$errorInfo);

        if ( $biz ) {
            $resouce['code'] = 200; //老师不存在
            $this->ajaxReturn($resouce);
        } else {

            $resouce['code'] = 400; //老师不存在
            $this->ajaxReturn($resouce);
        }
    }

    public function registerClass(){

        $this->assign('module', '班级行');
        $this->assign('nav', '班级管理');
        $this->assign('subnav', '我的班级');
        $this->assign('navicon', 'wodebanji');

        A('Home/Common')->getUserIdRole($userId,$role);

        switch($role)
        {
            case ROLE_TEACHER:layout('teacher_layout_3');
                break;
            case ROLE_STUDENT:layout('student_layout_3');
                break;

            case ROLE_PARENT:layout('parent_layout_3');
                break;
        }

        //所有的校建班年级
        $allxiaomap['biz_class.school_id'] = session('student.school_id');
        $allxiaomap['biz_class.class_status'] = 1;
        $allxiaomap['biz_class.is_delete'] = 0;
        $schoolclass = M('biz_class')
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->field("dict_grade.grade,dict_grade.id grade_id")
            ->where( $allxiaomap )
            ->group('grade_id')
            ->select();

        $this->assign('xiaojian',$schoolclass);

        $map['student_id'] = session('student.id');
        $map['status'] = array('neq',2);
        $class_list = M('biz_class_student')
                    ->join('left join biz_class on biz_class.id=biz_class_student.class_id')
                    ->join('dict_grade on dict_grade.id=biz_class.grade_id')
                    ->field('biz_class.id,biz_class_student.class_id,biz_class_student.student_id,biz_class_student.status,biz_class.name,biz_class.class_code,dict_grade.grade')
                    ->where( $map )
                    ->order('biz_class_student.create_at desc')
                    ->select();

        $this->assign('class_list',$class_list);
        $this->display();
    }

    public function getclassSchool() {
        $gradeId = I('grade_id');
        $where=array(
            'grade_id' => $gradeId,
            'class_status' => 1,
            'school_id' => session('student.school_id'),
            'is_delete' => 0
        );
        $row = M('biz_class')->where( $where )->field('id,name')->select();
        $this->ajaxReturn($row);
    }

    //加入班级
    public function setClassCode() {

        $class_code = I('class_code');
        $data['class_code'] = $class_code;
        $sendmap['class_code'] = $class_code;
        $row = M('biz_class')->where( $data )->find();
        $errorInfo = '';
        if ( !empty($row)) {

            if($row['is_delete'] == 1){
                $resouce['code'] = 600;
                $resouce['info'] = '你加入的班级已经删除';
                $this->ajaxReturn($resouce);
            }

            if($row['flag'] == 0){
                $resouce['code'] = 600;
                $resouce['info'] = '你加入的班级已经被停用';
                $this->ajaxReturn($resouce);
            }

            if ($row['class_status'] == 1) {
                if($row['school_id'] != session('student.school_id')){
                    $resouce['code'] = 600;
                    $resouce['info'] = '你所在的学校与加入班级的学校不一致';
                    $this->ajaxReturn($resouce);
                } else {
                    if (session('student.apply_school_status') !=1) {
                        $resouce['code'] = 600;
                        $resouce['info'] = '你未通过该学校的审核';
                        $this->ajaxReturn($resouce);
                    }
                }
            }



            if ( $row['class_status'] == 1) {
                $is_shcool_map['id'] = $row['school_id'];
                $schoolmap = M('dict_schoollist')->where( $is_shcool_map )->find();

                if ($schoolmap['flag'] == 0) {
                    $resouce['code'] = 600;
                    $resouce['info'] = '你加入的班级已经被停用';
                    $this->ajaxReturn( $data );
                }

                $where['biz_class_student.student_id'] = session('student.id');
                $where['biz_class.class_status'] = 1;

                $is_class = M('biz_class_student')
                    ->join('biz_class on biz_class.id=biz_class_student.class_id')
                    ->where( $where )
                    ->find();
            }



            if ( !empty($is_class)) {
                $resouce['code'] = 500;
                $resouce['info'] = '你已经加入过校建班';
                $this->ajaxReturn($resouce);
            } else {

                $biz = D('Biz_class')->joinClass($row['id'],3,session('student.id'),0,$errorInfo,1);

                if ( $biz ) {

                    if( $row['class_status'] == 2 ) {
                        $class_info_send =  M('biz_class')
                            ->join('biz_class_teacher on biz_class_teacher.class_id=biz_class.id')
                            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
                            ->where( $sendmap )
                            ->find();
                        //消息推送
                        $parameters = array(
                            'msg' => array(
                                session('student.student_name'),
                                $class_info_send['grade'],
                                $class_info_send['name'],
                            ),
                            'url' => array( 'type' => 1,'data'=>array($row['id']) )
                        );

                        A('Home/Message')->addPushUserMessage('STUDENT_ADD_PERSON_CLASS', 2, $class_info_send['teacher_id'] , $parameters);
                    }
                    else{
                        $resouce['class_category'] = 'school'; //校建班
                    }

                    $resouce['code'] = 200;
                    $this->ajaxReturn($resouce);
                } else {
                    $resouce['code'] = 300;
                    $resouce['info'] = $errorInfo;
                    $this->ajaxReturn($resouce);
                }
            }

        } else {
            $resouce['code'] = 400;
            $resouce['info'] = '班级编号不存在';
            $this->ajaxReturn($resouce);
        }

    }

    //加入班级
    public function setClassjoinClass() {
        $class_id = I('class_id');
        $errorInfo = '';

        $where['biz_class_student.student_id'] = session('student.id');
        $where['biz_class.class_status'] = 1;
        //$where['biz_class.id'] = $class_id;

        $is_class = M('biz_class_student')
            ->join('biz_class on biz_class.id=biz_class_student.class_id')
            ->where( $where )
            ->find();

        $class_info_map['id'] = $class_id;
        $classinfo = M('biz_class')->where( $class_info_map )->find();


        if ( !empty($is_class)) {
            $resouce['code'] = 500;
            $resouce['info'] = '你已经加入过校建班';
            $this->ajaxReturn($resouce);

        }

        if ($classinfo['class_status'] == 1) {
            if($classinfo['school_id'] != session('student.school_id')){
                $resouce['code'] = 600;
                $resouce['info'] = '你所在的学校与加入班级的学校不一致';
                $this->ajaxReturn($resouce);
            } else {
                if (session('student.apply_school_status') !=1) {
                    $resouce['code'] = 600;
                    $resouce['info'] = '你未通过该学校的审核';
                    $this->ajaxReturn($resouce);
                }
            }
        }


        if ( $classinfo['is_delete'] == 1) {
            $resouce['code'] = 600;
            $resouce['info'] = '你加入的班级已经被删除';
            $this->ajaxReturn($resouce);
        }

        if($classinfo['flag'] == 0){
            $resouce['code'] = 800;
            $resouce['info'] = '你加入的班级已经停用';
            $this->ajaxReturn($resouce);
        }

        if ( $classinfo['class_status'] == 1) {
            $is_shcool_map['id'] = $classinfo['school_id'];
            $schoolmap = M('dict_schoollist')->where( $is_shcool_map )->find();

            if ($schoolmap['flag'] == 0) {
                $data['code'] = 900;
                $this->ajaxReturn( $data );
            }
        }

        $biz = D('Biz_class')->joinClass($class_id,3,session('student.id'),0,$errorInfo,1);
        if ( $biz ) {
            $resouce['code'] = 200;
            $this->ajaxReturn($resouce);
        } else {
            $resouce['code'] = 300;
            $resouce['info'] = $errorInfo;
            $this->ajaxReturn($resouce);
        }
    }
    //不加入班级
    public function delClassjoinClass() {
        $class_id = I('classid');
        $student_id = I('student_id');

        $map = array(
            'class_id' => $class_id,
            'student_id' => $student_id,
        );
        $id = M('biz_class_student')->where( $map )->delete();
        $class_result_info=D('Biz_class')->getClassAndGradeInfo($class_id);
        $teacherall_id = D('Biz_class')->getTeacherIdAll($class_id);

        if( $id ) {

            //消息推送
            $parameters = array(
                'msg' => array(
                    session('student.student_name'),
                    $class_result_info['grade'],
                    $class_result_info['name'],
                ),
                'url' => array( 'type' => 0, )
            );

            A('Home/Message')->addPushUserMessage('STUDENT_CEXIAO_PERSON_CLASS', 2, implode(',', $teacherall_id ) , $parameters);

            $resouce['code'] = 200;
            $this->ajaxReturn($resouce);
        } else {
            $resouce['code'] = 400;
            $this->ajaxReturn($resouce);
        }
    }

    //发送提醒
    public function sendRemind() {
        $userId = getParameter('student_id', 'int');
        $classId = getParameter('classid', 'int');

        //$this->model->getClassNameTeacher($classId); ->D('Biz_class')->getClassNameTeacher($classId);
        $studentInfo = D('Auth_student')->getStudentInfo($userId);
        $classCategory = D('Biz_class')->getClassCategory($classId);

        $class_result_info=D('Biz_class')->getClassAndGradeInfo($classId);
        $teacherall_id = D('Biz_class')->getTeacherIdAll($classId);

        if (PERSONAL_CLASS == $classCategory) {
            //消息推送
            $parameters = array(
                'msg' => array(
                    session('student.student_name'),
                    $class_result_info['grade'],
                    $class_result_info['name'],
                ),
                'url' => array( 'type' => 1, 'data'=>array($classId))
            );

            A('Home/Message')->addPushUserMessage('STUDENT_ADD_PERSON_CLASS', 2, implode(',', $teacherall_id ) , $parameters);

            $this->showMessage(200, 'success', array());
        } else
            $this->showMessage(500, '班级不存在或不是教师自建班级', array());
    }

    //课程表
    public function classTimetable() {

        /*if (!session('?teacher')) redirect(U('Index/index'));

       $isAuth = $this->isAuth($this->c_a);

       if (!$isAuth) { //如果访问的模块没有权限
           redirect(U('Teach/index1?auth_error=1'));
       }*/
        $this->assign('module', '班级行');
        $this->assign('nav', '班级管理');
        $this->assign('navicon', 'banjiguanli');

        //$classId = intval($_GET['classId']);
        $classId = getParameter('classId', 'int',false);

        if(!$classId){
            redirect(U('Index/systemError'));
        }

        $this->assign('classId', $classId);


        $ClassModel = M('biz_class');
        $class = $ClassModel
            ->join('left join dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('left join dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('left join biz_class_teacher on biz_class_teacher.class_id=biz_class.id')
            ->join('left join auth_teacher on auth_teacher.id=biz_class_teacher.teacher_id')
            ->field('auth_teacher.school_id as ats_id,biz_class_teacher.teacher_id,biz_class.school_id,biz_class.*,dict_schoollist.school_name,dict_grade.grade')
            ->where("biz_class.id=$classId")
            ->find();

        if ($class['class_status'] ==1) {
            $schoolmap['id'] =  $class['school_id'];
            $school_status = M('dict_schoollist')
                ->where( $schoolmap )->find();
        } else {
            $schoolmap['id'] =  $class['ats_id'];
            $school_status = M('dict_schoollist')
                ->where( $schoolmap )->find();
        }

        $class['s_flag'] = $school_status['flag'];

        if ($class['flag'] ==0) {
            $class['flag_name'] = '停用';
        }

        if ($class['flag'] ==1 ) {

            if ($class['s_flag']==1) {
                $class['flag_name'] = '正常';
            } else {
                $class['flag_name'] = '停用';
                $class['flag'] = 0;
            }

        }

        if ($class['flag'] ==2) {
            $class['flag_name'] = '移交中';
        }




        $this->assign('class', $class);

        $this->assign('subnav', $class['name'] . '的班级课表');

        $html = $this->fetch('classteacherTimetableSchool');
        $this->ajaxReturn($html);
    }
}