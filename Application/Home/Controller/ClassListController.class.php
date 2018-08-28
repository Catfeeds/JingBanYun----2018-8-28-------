<?php
namespace Home\Controller;

use Think\Controller;
use Common\Common\CSV;
use Common\Common\SMS;

class ClassListController extends PublicController{
	    //班级管理
    public $c_a = '';

    function __construct(){
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));
        $this->c_a = CONTROLLER_NAME."_".ACTION_NAME;
        set_time_limit(0);
        $this->teacherId_id_online = session('teacher.id');

    }

    public function classList()
    {
        $is_login = session('teacher.is_login');

        if ( $is_login == 1) {
            $teacher_id = session('teacher.id');
            D('Biz_classList')->isFirstLogin( $teacher_id );
            $this->redirect('Teach/functionGuidancecopy');
        }
        A('Home/Common')->getUserIdRole($userId,$role);

        if (!$this->teacherId_id_online) {
            redirect(U('Teach/index1?auth_error=1'));
        }

        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

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
        $this->assign('navicon', 'banjiguanli');
        $result = D('Biz_classList')->getClassListTeacherAllCopy();
        $this->assign('list', $result);
        $this->display();
    }

    //点击班级成员获取信息
    public function isClassinfo() {
        $class_status=getParameter('class_status','str',false);
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
        $classid=getParameter('classid','int',false);
        if (empty($classid)){
            die();
        }

        $keyword=getParameter('keyword','str',false);
        //获取学生列表
        $datainfo = D('Biz_classList')->getClassStuList( $classid, $keyword);
        $data_stu['class_info'] = $datainfo['class_info'];
        $data_stu['stu_count'] = $datainfo['stu_count'];
        $data_stu['wating_stu'] = $datainfo['wating_stu'];
        $this->assign('list',$datainfo['list']);
        $this->assign('data_stu',$data_stu);
        $this->assign('classid',$classid);

        $html = $this->fetch('stulist');

        return $html;
    }

    //获取学生和老师的列表
    public function getStuandTeachList() {

        $classid=getParameter('classid','int',false);
        $keyword=getParameter('keyword','str',false);
        if (empty($classid)){
            die();
        }
        //获取校建班学生列表
        $datainfo = D('Biz_classList')->getClassStuandTeacherList( $classid, $keyword );

        $data_stu['class_info'] = $datainfo['class_info'];
        $data_stu['stu_count'] = $datainfo['stu_count'];
        $data_stu['tea_count'] = $datainfo['tea_count'];
        $this->assign('count',$datainfo['count']);
        $this->assign('data_stu',$data_stu);
        $this->assign('classid',$classid);
        $this->assign('list',$datainfo['list']);

        $this->assign('dict_string_name',$datainfo['dict_string_name']);

        $html = $this->fetch('stuandteacher');
        return $html;
    }
    //班级信息设置
    public function setClassButton(){
        $classid=getParameter('classid','int',false);
        $datainfo = D('Biz_classList')->setClassButtonModel( $classid );
        $data_stu['class_info'] = $datainfo['class_info'];
        $data_stu['stu_count'] = $datainfo['stu_count'];
        $data_stu['wating_stu'] = $datainfo['wating_stu'];

        $this->assign('data_stu',$data_stu);
        $this->assign('classid',$classid);

        $hrow = D('Biz_classList')->findTeachClassHandoff( $classid );

        if (!empty($hrow) && $hrow['handsoff_status'] != 3) {
            $hteacher['id'] = $hrow['dest_teacherid'];
            $hoffrow = M('auth_teacher')->where( $hteacher )->field('id,name,telephone')->find();
            $this->assign('hoffrow',$hoffrow);
            $this->assign('is_show_data',1);
        }

        $html = $this->fetch('setClassInfo');

        $this->ajaxReturn($html);
    }

    //老师设置信息
    public function setSchoolClassButton(){
        $classid=getParameter('classid','int',false);
        $row = D('Biz_classList')->teacherSetclass( $classid );
        $data_stu['class_info'] = $row;
        $this->assign('data_stu',$data_stu);
        $this->assign('classid',$classid);

        $bctmap['class_id'] = $classid;
        $bctmap['teacher_id'] = session('teacher.id');
        $dict_names = M('biz_class_teacher')
                        ->join('dict_course on dict_course.id=biz_class_teacher.course_id')
                        ->where($bctmap)
                        ->select();
        //print_r($dict_names);die();
        if (!empty($dict_names)) {
            $this->assign('dict_course_list',$dict_names);
            $dict_string = array();
            foreach ($dict_names as $k=>$v){
                $dict_string[] = $v['course_name'];
            }
        }

        $handmap['send_teacherid'] = session('teacher.id');
        $handmap['class_id'] = $classid;
        $handmap['handsoff_status'] = array('eq',1);

        $hand = M('biz_class_handsoff')->where( $handmap )->select();

        if ( !empty( $hand ) ) {
            foreach ( $hand as $hk=>$hv ) {
                $tmap['id'] = $hv['dest_teacherid'];
                $teacher_info = M('auth_teacher')->where( $tmap )->find();
                $hand[$hk]['t_name'] = $teacher_info['name'];
                $hand[$hk]['t_phone'] = $teacher_info['telephone'];
                $dmap['id'] = $hv['course_id'];
                $d_info = M('dict_course')->where( $dmap )->find();
                $hand[$hk]['t_dict_name'] =  $d_info['course_name'];
            }
            $this->assign('dest_teacher_list',$hand);
        }

        if( count($dict_names) == count($hand) ) {

            $this->assign('is_show_hide',1);
        }

        if (!empty($dict_string)){
            $dict_string_name = implode(',',$dict_string);
            $this->assign('dict_string_name',$dict_string_name);
        }

        $html = $this->fetch('setSchoolClassButton');
        $this->ajaxReturn($html);
    }

    //闹钟
    public function setClasstimetables() {
        $html = $this->fetch('crontabClass');
        $this->ajaxReturn($html);
    }

    public function setClassTeachtimetables() {
        $html = $this->fetch('crontabClass');
        $this->ajaxReturn($html);
    }

    //未通过
    public function getNotPassed() {
        $classid=getParameter('classid','int',false);
        $keyword=getParameter('keyword','str',false);

        $mapwhere = array(
            'biz_class_student.class_id' => $classid,
            'biz_class_student.status' => 1
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

        $dengdaistu = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->where("biz_class_student.class_id=$classid and biz_class_student.status = 1")
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
        $data_stu['wating_stu'] = $dengdaistu;
        $this->assign('data_stu',$data_stu);

        $this->assign('list',$result);
        $this->assign('classid',$classid);

        $html = $this->fetch('stulistwating');
        $this->ajaxReturn($html);
    }

    //接收班级
    public function acceptClass(){

         $this->assign('module', '班级行');
         $this->assign('nav', '班级管理');
         $this->assign('subnav', '创建班级');
         $this->assign('navicon', 'banjiguanli');
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
         $map = array(
             'biz_class_handsoff.dest_teacherid' => session('teacher.id'),
             'biz_class_handsoff.handsoff_status' => 1,
         );

         $rowlist = M('biz_class_handsoff')
             ->join('left join dict_course on dict_course.id=biz_class_handsoff.course_id')
             ->join('left join biz_class on biz_class.id=biz_class_handsoff.class_id')
             ->join('left join dict_grade on dict_grade.id=biz_class.grade_id')
             ->field('dict_course.course_name,biz_class.name,dict_grade.grade,biz_class_handsoff.send_teacherid,biz_class_handsoff.dest_teacherid,biz_class_handsoff.class_id,biz_class_handsoff.course_id')

             ->where( $map )
             ->select();

         $this->assign('list',$rowlist);

         $this->display();
	 }

	 //设置接收班级或者拒绝接收
    public function setClasStatusTeacher(){

        $classid=getParameter('classid','int',false);
        $steacher=getParameter('steacher','str',false);
        $dteacher=getParameter('dteacher','str',false);
        $status=getParameter('status','int',false);
        $course_id=getParameter('course_id','int',false);

        $biz = D('Biz_class')->receiveClass($classid,$steacher,$course_id,$status);

        $class_model=D('Biz_class');
        $class_result=$class_model->getClassAndGradeInfo($classid);
        $teacher_model = D('Auth_teacher');
        $teacherinfo = $teacher_model->getTeachInfo( $dteacher );

        $parent_id_all = D('Biz_class')->getParentIdAll($classid);
        $course_info = D('Dict_course')->getCourseInfo($course_id);

        $student_id_all = D('Biz_class')->getStudentIdAll($classid);


        if ( $status == 2) { //接收班级
            //转移作业
            D('Exercises_homework_basics')->updateHomeworkAuth($steacher,$dteacher,$classid,$course_id);
            $parameters = array(
                'msg' => array(
                    $teacherinfo['name'],
                    $class_result['grade'],
                    $class_result['name'],
                ),
                'url' => array( 'type' => 0 )
            );

            A('Home/Message')->addPushUserMessage('ACCEPT_CLASS', 2, $steacher , $parameters);

            //对家长发送推送信息
            if ($class_result['class_status'] == 1) { //校建班

                //对家长的发送
                $parameters = array(
                    'msg' => array(
                        $class_result['grade'],
                        $class_result['name'],
                        $course_info['course_name'],
                        $teacherinfo['name'],
                    ),
                    'url' => array( 'type' => 0,)
                );
                A('Home/Message')->addPushUserMessage('ZIJIAN_XIAOJIAN', 4,implode(',', $parent_id_all)  , $parameters);

                //对学生的发送
                A('Home/Message')->addPushUserMessage('STU_ZIJIAN_XIAOJIAN', 3,implode(',', $student_id_all)  , $parameters);

            } else {

                //对家长的发送
                $parameters = array(
                    'msg' => array(
                        $class_result['grade'],
                        $class_result['name'],
                        $teacherinfo['name'],
                    ),
                    'url' => array( 'type' => 0,)
                );
                A('Home/Message')->addPushUserMessage('ZIJIAN_YIJIAO', 4,implode(',', $parent_id_all)  , $parameters);

                //对学生的发送
                A('Home/Message')->addPushUserMessage('STU_ZIJIAN_YIJIAO', 3,implode(',', $student_id_all)  , $parameters);
            }
        }

        if($biz) {
            $data['code'] = 200;
            $this->ajaxReturn( $data );
        } else {
            $data['code'] = 400;
            $this->ajaxReturn( $data );
        }


    }


	public function joinClass(){

        $this->assign('module', '班级行');
        $this->assign('nav', '班级管理');
        $this->assign('subnav', '创建班级');
        $this->assign('navicon', 'banjiguanli');

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



        $Model = M('biz_class');
        $teacherId = session('teacher.school_id');

        $schoolclassmap['class_status'] = 1;
        $schoolclassmap['school_id'] =$teacherId;

        if (!$teacherId) {
            $this->redirect(U('Index/index'));
        }

        $grades = $Model->where( $schoolclassmap )
            ->join("left join dict_grade on dict_grade.id=biz_class.grade_id")
            ->field("dict_grade.grade,dict_grade.id grade_id")
            ->group('dict_grade.id')
            ->select();

        $this->assign('xiaojian',$grades);


        //获取学科

        $Model = M('auth_teacher_second');
        $teacherId_id = session('teacher.id');

        if (!$teacherId) {
            $this->redirect(U('Index/index'));
        }

        $grades = $Model->where("auth_teacher_second.teacher_id=".$teacherId_id)
            ->join("dict_grade on dict_grade.id=auth_teacher_second.grade_id")
            ->join("dict_course on dict_course.id=auth_teacher_second.course_id")
            ->field("auth_teacher_second.id pri_id,auth_teacher_second.course_id,dict_course.course_name,"
                . "dict_grade.grade,dict_grade.id grade_id,dict_course.id as c_id")
            ->group('auth_teacher_second.course_id')
            ->select();

        $this->assign('course_data',$grades);
        $this->assign('course_data_copy',$grades);


        $this->display();
	}

	//根据年级id查询所有的校建班班级名称

    public function getclassSchool() {
        $gradeId=getParameter('grade_id','int',false);
        $where=array(
            'grade_id' => $gradeId,
            'class_status' => 1,
            'school_id' => session('teacher.school_id'),
        );

        $row = M('biz_class')->where( $where )->field('id,name')->select();
        $this->ajaxReturn($row);
    }




	public function createClass(){
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

        if ($_POST) {
            $data['name'] = trim(remove_xss($_POST['name']));
            //$pri_id = $_POST['grade_id'];
            /*$data['name'] = getParameter('name', 'str',false);
            $pri_id = getParameter('grade_id', 'int',false);

            $data['school_id'] = session('teacher.school_id');
            $data['class_teacher_id'] = session('teacher.id');
            $data['class_teacher'] = session('teacher.name');
            $data['create_at'] = time();

            $Model = M('dict_grade');
            $teacherId = session('teacher.id');
            $grades = $Model->where("auth_teacher_second.teacher_id=".$teacherId." and auth_teacher_second.grade_id=".$pri_id)
                ->join("auth_teacher_second on auth_teacher_second.grade_id=dict_grade.id ")
                ->join("dict_course on dict_course.id=auth_teacher_second.course_id")
                ->field("auth_teacher_second.id pri_id,auth_teacher_second.course_id,dict_course.course_name,"
                    . "dict_grade.grade,dict_grade.id grade_id")->find();
            if(empty($grades)){
                $arr['msg']='该年级不存在';
                $arr['code']=-1;
                echo json_encode($arr);
                die;
            }
            //$data['course_id']=$grades['course_id'];
            $data['grade_id']=$grades['grade_id'];
            $data['class_status'] = 2;

            //$where['course_id'] = $data['course_id'];
            $where['name'] = $data['name'];
            $where['grade_id'] = $data['grade_id'];
            $where['school_id'] = $data['school_id'];
            $where['class_teacher_id'] = session('teacher.id');

            $ResourceModel = M('biz_class');
            $res = $ResourceModel ->where($where)->find();
            //print_r(M()->getLastSql());die();
            if(!empty($res)) //已经存在该班级
            {
                $arr['msg']='该班级已经存在';
                $arr['code']=-1;
                echo json_encode($arr);
                die;
            }
            $c_id = $ResourceModel->add($data);

            if ( $c_id ) {
                $tickdata = array(
                    'class_id' => $c_id,
                    'teacher_id' => $data['class_teacher_id'],
                    'create_at' => time(),
                    'is_handler' => 1,
                );
                $tick = M('biz_class_teacher')->add($tickdata);
            }*/


            $errorInfo ='';
            $gradeId = getParameter('grade_id', 'int',false);

            if( empty($data['name']) ) //已经存在该班级
            {
                $arr['msg']='请输入班级名称';
                $arr['code']=-1;
                echo json_encode($arr);
                die;
            }
            if( empty( $gradeId ) ) //已经存在该班级
            {
                $arr['msg']='请选择年级';
                $arr['code']=-1;
                echo json_encode($arr);
                die;
            }

            if(preg_match("/[ '.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$data['name'])){
                $arr['msg']= '班级名称包含特殊字符';
                $arr['code']=-1;
                echo json_encode($arr);
                exit();
            }

            $teacherInfo = D('Auth_teacher')->getTeachInfo(session('teacher.id'));
            $cModel = D('Biz_class')->createClass($data['name'],$gradeId,$teacherInfo,$errorInfo);


            if ( $cModel ) {
                $arr['msg']='添加成功';
                $arr['code']=0;
                echo json_encode($arr);
            } else {
                $arr['msg']=$errorInfo;
                $arr['code']=1;
                echo json_encode($arr);
            }

            //$this->redirect("Teach/classList");

        } else {

            $this->assign('module', '班级行');
            $this->assign('nav', '班级管理');
            $this->assign('subnav', '创建班级');
            $this->assign('navicon', 'banjiguanli');

            //这里只能选择它自己注册时候填的年级和学科
            /*$Model = M('dict_grade');
            $teacherId = session('teacher.id');
            $grades = $Model->where("auth_teacher_second.teacher_id=".$teacherId)
                ->join("auth_teacher_second on auth_teacher_second.grade_id=dict_grade.id")
                ->join("dict_course on dict_course.id=auth_teacher_second.course_id")
                ->field("auth_teacher_second.id pri_id,auth_teacher_second.id course_id,dict_course.course_name,"
                    . "dict_grade.grade,dict_grade.id grade_id")->select();*/

            $Model = M('auth_teacher_second');
            $teacherId = session('teacher.id');
            /*$grades = $Model->where("auth_teacher_second.teacher_id=".$teacherId)
                    ->join("dict_grade on dict_grade.id=auth_teacher_second.grade_id")
                    ->join("dict_course on dict_course.id=auth_teacher_second.course_id")
                    ->field("auth_teacher_second.id pri_id,auth_teacher_second.id course_id,dict_course.course_name,"
                    . "dict_grade.grade,dict_grade.id grade_id")
                    ->group('dict_grade.id')
                    ->select();*/
            $grades = M('dict_grade')->where('id<14')->select();

            $this->assign('grades', $grades);

            if (!$teacherId) {
                $this->redirect(U('Index/index'));
            }


            $singleResult = M('auth_teacher')->where("id=$teacherId")->find();
            $schoolId = $singleResult['school_id'];
            $classModel = M('biz_class');
            $grades = $classModel->where("school_id = $schoolId")->field('distinct name')->select();

            $this->assign('gradeList', $grades);
            $this->display();
        }


	}

	//根据老师选择的年级获取年级下的所有学科
    public function getgradesandCourse() {
        $teacherId = session('teacher.id');
        $gradeId=getParameter('grade_id','int',false);
        $Model = M('auth_teacher_second');
        $where['auth_teacher_second.teacher_id'] = $teacherId;
        $where['auth_teacher_second.grade_id'] = $gradeId;

        $course = $Model
            ->join("dict_grade on dict_grade.id=auth_teacher_second.grade_id")
            ->join("dict_course on dict_course.id=auth_teacher_second.course_id")
            ->field("dict_course.id,dict_course.course_name")
            ->where($where)
            ->select();
        $this->ajaxReturn($course);
    }

	public function registerClass(){
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
		 $this->display();
	}

	//老师加入校建班
    public function teacherAddClass(){

        $class_id=getParameter('class_id','int',false);
        $course_id=getParameter('course_id','int',false);

        $where['class_code'] = $class_id;
        $where['class_status'] = 1;
        $row = M('biz_class')->where( $where )->find();

        if (empty($row)) {
            $data['code'] = 400;
            $this->ajaxReturn( $data );
        } else {

            if ($row['flag'] == 0) {
                $data['code'] = 900;
                $this->ajaxReturn( $data );
            }

            if ( $row['class_status'] == 1) {
                $is_shcool_map['id'] = $row['school_id'];
                $schoolmap = M('dict_schoollist')->where( $is_shcool_map )->find();

                if ($schoolmap['flag'] == 0) {
                    $data['code'] = 900;
                    $this->ajaxReturn( $data );
                }
            }

            if($row['school_id']!=session('teacher.school_id')) { //不在同一个学校
                $data['code'] = 1001;
                $this->ajaxReturn( $data );
            } else {
                if ( $row['class_status'] == 1) {
                    if (session('teacher.apply_school_status') != 1) {
                        $data['code'] = 1002;
                        $this->ajaxReturn($data);
                    }
                }
            }

            $adddata = array(
                'class_id' => $row['id'],
                'teacher_id' => session('teacher.id'),
                'course_id' => $course_id,
                'create_at' => time(),
                'is_handler' => 0,
            );

            $hmap =  array(
                'class_id' => $row['id'],
                'teacher_id' => session('teacher.id'),
                'course_id' => $course_id,
            );

            $fid = M('biz_class_teacher')->where($hmap)->find();

            /*if(session('teacher.lock') !=2) {
                $data['code'] = 1000;
                $this->ajaxReturn( $data );
            }*/

            if (!empty($fid)) {
                $data['code'] = 600;
                $this->ajaxReturn( $data );
            } else {

                $id = M('biz_class_teacher')->add($adddata);

                if ( $id ) {
                    $data['code'] = 200;
                    $this->ajaxReturn( $data );
                } else {
                    $data['code'] = 300;
                    $this->ajaxReturn( $data );
                }
            }

        }
    }

    //选择班级

    public function teacherSelectAddClass(){

        $class_id=getParameter('class_id','int',false);
        $course_id=getParameter('course_id','int',false);

        $where['id'] = $class_id;
        $where['class_status'] = 1;
        $row = M('biz_class')->where( $where )->find();

        if (empty($row)) {
            $data['code'] = 400;
            $this->ajaxReturn( $data );
        } else {

            if ($row['flag'] == 0) {
                $data['code'] = 900;
                $this->ajaxReturn( $data );
            }

            if ( $row['class_status'] == 1) {
                $is_shcool_map['id'] = $row['school_id'];
                $schoolmap = M('dict_schoollist')->where( $is_shcool_map )->find();

                if ($schoolmap['flag'] == 0) {
                    $data['code'] = 900;
                    $this->ajaxReturn( $data );
                }
            }

            if($row['school_id']!=session('teacher.school_id')) { //不在同一个学校
                $data['code'] = 1001;
                $this->ajaxReturn( $data );
            } else {

                if ( $row['class_status'] == 1) {
                    if (session('teacher.apply_school_status') != 1) {
                        $data['code'] = 1002;
                        $this->ajaxReturn($data);
                    }
                }
            }

            /*if(session('teacher.lock') !=2) {
                $data['code'] = 1000;
                $this->ajaxReturn( $data );
            }*/

            $adddata = array(
                'class_id' => $row['id'],
                'teacher_id' => session('teacher.id'),
                'course_id' => $course_id,
                'create_at' => time(),
                'is_handler' => 0,
            );

            $hmap =  array(
                'class_id' => $class_id,
                'teacher_id' => session('teacher.id'),
                'course_id' => $course_id,
            );

            $fid = M('biz_class_teacher')->where($hmap)->find();

            if (!empty($fid)) {
                $data['code'] = 600;
                $this->ajaxReturn( $data );
            } else {

                $id = M('biz_class_teacher')->add($adddata);

                if ( $id ) {
                    $data['code'] = 200;
                    $this->ajaxReturn( $data );
                } else {
                    $data['code'] = 300;
                    $this->ajaxReturn( $data );
                }
            }

        }
    }

    //提交课程表
    public function classTimetable()
    {

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
            ->field('biz_class.*,dict_schoollist.school_name,dict_grade.grade')
            ->where("biz_class.id=$classId")
            ->find();

        if ( $class['flag'] ==1) {
            $class['flag'] = '正常';
        }elseif ($class['flag'] ==2) {
            $class['flag'] = '移交中';
        } else{
            $class['flag'] = '停用';
        }

        $this->assign('class', $class);

        $this->assign('subnav', $class['name'] . '的班级课表');

        $html = $this->fetch('classTimetable');
        $this->ajaxReturn($html);

    }

    //校建班课程表
    //提交课程表
    public function classSchoolTimetable()
    {

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
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_class.*,dict_schoollist.school_name,dict_grade.grade')
            ->where("biz_class.id=$classId")
            ->find();


        $schoolmap['id'] =  $class['school_id'];
        $school_status = M('dict_schoollist')
            ->where( $schoolmap )->find();

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

        $html = $this->fetch('classTimetableSchool');
        $this->ajaxReturn($html);

    }

    public function classteacherTimetable()
    {

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
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_class.*,dict_schoollist.school_name,dict_grade.grade')
            ->where("biz_class.id=$classId")
            ->find();

        if ( $class['flag'] ==1) {
            $class['flag'] = '正常';
        }elseif ($class['flag'] ==2) {
            $class['flag'] = '移交中';
        } else{
            $class['flag'] = '停用';
        }

        $this->assign('class', $class);

        $this->assign('subnav', $class['name'] . '的班级课表');

        $html = $this->fetch('classteacherTimetable');
        $this->ajaxReturn($html);

    }

    public function classteacherTimetableSchool()
    {

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
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_class.*,dict_schoollist.school_name,dict_grade.grade')
            ->where("biz_class.id=$classId")
            ->find();

        $schoolmap['id'] =  $class['school_id'];
        $school_status = M('dict_schoollist')
            ->where( $schoolmap )->find();

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

    //删除班级学生
    public function delStudentClass() {

        $classid=getParameter('classid','int',false);
        $student_id=getParameter('student_id','str',false);
        $class_result_info=D('Biz_class')->getClassAndGradeInfo($classid);
        $student_info = D('Auth_student')->getStudentInfo( $student_id );

        $is_init = is_numeric($student_id);
        if($is_init) {
            $data = array(
                'class_id' => $classid,
                'student_id' => $student_id
            );
        } else {
            if (!empty($student_id)) {
                $student_id = ltrim($student_id,',');
                $student_id = explode(',',$student_id);
            }

            $data = array(
                'class_id' => $classid,
                'student_id' => array('in',$student_id),
            );
        }



        if (is_array($student_id)) {

            foreach ($student_id as $k=>$v) {

                $datas['class_id'] = $classid;
                $datas['student_id'] = $v;
                $isclassinfo = M('biz_class_student_record')->where( $datas )->find();

                $delinfo = M('biz_class_student')->where( $datas )->find();
                $addrecordmap = array(
                    'class_id' => $delinfo['class_id'],
                    'student_id' => $delinfo['student_id'],
                    'create_at' => $delinfo['create_at'],
                    'update_at' => $delinfo['update_at'],
                    'status' => $delinfo['status'],
                    'joinmode' => $delinfo['joinmode'],
                );

                if ( empty($isclassinfo) ) {
                    $addrecord = M('biz_class_student_record')->add( $addrecordmap );
                } else {
                    $addrecord = true;
                }
            }

        } else {

            $isclassinfo = M('biz_class_student_record')->where( $data )->find();

            $delinfo = M('biz_class_student')->where( $data )->find();

            $addrecordmap = array(
                'class_id' => $delinfo['class_id'],
                'student_id' => $delinfo['student_id'],
                'create_at' => $delinfo['create_at'],
                'update_at' => $delinfo['update_at'],
                'status' => $delinfo['status'],
                'joinmode' => $delinfo['joinmode'],
            );

            if ( empty($isclassinfo)) {
                $addrecord = M('biz_class_student_record')->add( $addrecordmap );
            } else {
                $addrecord = true;
            }

        }

        if ($is_init) {
            $parameters = array(
                'msg' => array(
                    session('teacher.name'),
                    $student_info['student_name'],
                    $class_result_info['grade'],
                    $class_result_info['name'],
                    session('teacher.name'),
                    $class_result_info['grade'],
                    $class_result_info['name'],
                ),
                'url' => array( 'type' => 1,'data'=>array($student_info['id'],$student_info['student_name']))
            );

            A('Home/Message')->addPushUserMessage('DELSTUDENTCLASS', 4,$student_info['parent_id'] , $parameters);
            $parameters['url'] = array( 'type' => 0);
            unset($parameters['msg'][1]);
            //移除学生
            A('Home/Message')->addPushUserMessage('STU_CLASS_REMOVE_TEACHER', 3,$student_info['id'], $parameters);//学生
        } else {

            foreach ($student_id as $k=>$sid){
                $student_info = D('Auth_student')->getStudentInfo( $sid );
                if(!empty($student_info)) {
                    $parameters = array(
                        'msg' => array(
                            session('teacher.name'),
                            $student_info['student_name'],
                            $class_result_info['grade'],
                            $class_result_info['name'],
                            session('teacher.name'),
                            $class_result_info['grade'],
                            $class_result_info['name'],
                        ),
                        'url' => array( 'type' => 0,)
                    );

                    A('Home/Message')->addPushUserMessage('DELSTUDENTCLASS', 4,$student_info['parent_id'] , $parameters);

                    unset($parameters['msg'][1]);
                    //移除学生
                    A('Home/Message')->addPushUserMessage('STU_CLASS_REMOVE_TEACHER', 3,$student_info['id'], $parameters);//学生
                }
            }
        }

        $del = M('biz_class_student')->where( $data )->delete();

        if ($del && $addrecord) {
            $resouce['code'] = 200;
            $this->ajaxReturn($resouce);
        }else{
            $resouce['code'] = 400;
            $this->ajaxReturn($resouce);
        }
    }
    //导出学生列表
    public function exportedStu(){

        $classid=getParameter('classid','int',false);
        $student_id=getParameter('student_id','str',false);

        $classmap['biz_class.id'] = $classid;
        $classinfo = M('biz_class')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->where( $classmap )
            ->find();
        $class_name_export = $classinfo['grade'].$classinfo['name'].'-学生信息列表';

        if (!empty($student_id)) {
            $student_id = ltrim($student_id,',');
            $student_id = explode(',',$student_id);

        }

        $mapwhere = array(
            'biz_class_student.class_id' => $classid,
            'biz_class_student.student_id' => array('in',$student_id),
        );

        $Model = M('biz_class_student');
        $result = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->field('auth_student.parent_tel,auth_student.student_name,auth_student.sex,auth_student.sex,auth_student.avatar,auth_student.email,biz_class_student.student_id')
            ->where( $mapwhere )
            ->select();

        for($i=0;$i<sizeof($result);$i++) {
            $result[$i]['birth_date'] = date("Y-m-d",$result[$i]['birth_date']);
            if(empty($result[$i]['avatar']) || $result[$i]['avatar'] =='') {
                $result[$i]['avatar'] = 'default.jpg';
            }

        }

        $str="姓名,性别,家长手机号,邮箱\n";
        $str=iconv('utf-8','gb2312', $str);
        foreach($result as $v){
            $student_name=iconv('utf-8','gbk', $v['student_name']);
            $sex=iconv('utf-8','gbk', $v['sex']);
            $parent_tel=iconv('utf-8','gbk', $v['parent_tel']);
            $email=iconv('utf-8','gbk', $v['email']);
            $str.=$student_name.",".$sex.",".$parent_tel.",".$email."\n";
        }


        $filename=$class_name_export.'.csv';
        $ua = $_SERVER["HTTP_USER_AGENT"];
        if(strpos($ua,'MSIE')!==false || strpos($ua,'rv:11.0')) {
            $filename = urlencode($filename);
        }

        $csv=new CSV();
        $csv->downloadFileCsv($filename,$str);


    }

    //添加学生未注册京版云
    public function registeraddStu() {

        $classid=getParameter('classid','int',false);
        $student_name=getParameter('student_name','str',false);
        $parent_tel_data=getParameter('parent_tel_data','str',false);
        $password=getParameter('password','str',false);
        $pass=getParameter('pass','str',false);

        $map['student_name'] = $student_name;
        $map['parent_tel'] = $parent_tel_data;
        $findinfo = M('auth_student')->where( $map )->find();

        $findmap['telephone'] = $map['parent_tel'];
        $parent_info = M('auth_parent')->where( $findmap )->find();

        $class_result_info_add=D('Biz_class')->getClassAndGradeInfo($classid);

        if (!empty($findinfo)) { //已经注册
            $resouce['code'] = 600;
            $this->ajaxReturn($resouce);
        } else {

            if(!empty($parent_info)) {
                $adddata = array(
                    'student_name' => $student_name,
                    'parent_tel' => $parent_tel_data,
                    'password' => sha1($password),
                    'parent_id' =>$parent_info['id'],
                    'school_id' => session('teacher.school_id'),
                    'create_at' => time(),
                    'update_at' => time(),
                );

            }else {
                $adddata = array(
                    'student_name' => $student_name,
                    'parent_tel' => $parent_tel_data,
                    'password' => sha1($password),
                    'school_id' => session('teacher.school_id'),
                    'create_at' => time(),
                    'update_at' => time(),
                );

            }

            $idmap['id'] = $classid;
            $class_row = M('biz_class')->where( $idmap )->field('class_code,flag,class_status,school_id')->find();

            if($class_row['class_status'] ==1) {
                $adddata['apply_school_status'] = 1;
            } else{
                $adddata['apply_school_status'] = 0;
            }


            $addid = M('auth_student')->add( $adddata );

            if ( $addid ) {
                $classdata = array(
                    'class_id' => $classid,
                    'student_id' => $addid,
                    'create_at' => time(),
                    'update_at' => time(),
                    'status' => 2,
                );

                $sid = M('biz_class_student')->add($classdata);

                //添加赠送vip
                $vipstatus = C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');
                if ( $vipstatus == 1) {

                    $vipdata = array(
                        'user_id' => $addid,
                        'role_id' => 3,
                        'auth_id' => 4,
                        'auth_start_time' => time(),
                        'auth_end_time' => time()+3600*24*30*3,
                        'timetype' => 1,
                    );
                    $auth_type_use = D('Account_auths');
                    $auth_type_use->addUserVip($vipdata);

                }
            }

            if (!empty($parent_info)) {
                $aspc = array(
                    'student_id' =>$addid,
                    'parent_tel' =>$parent_info['telephone'],
                    'parent_id' =>$parent_info['id'],
                    'create_at' => time(),
                );
                M('auth_student_parent_contact')->add($aspc);

                $parameters = array(
                    'msg' => array(
                        session('teacher.name'),
                        $student_name,
                        $class_result_info_add['grade'],
                        $class_result_info_add['name'],
                    ),
                    'url' => array( 'type' => 0,)
                );

                A('Home/Message')->addPushUserMessage('REGISTERADDSTU', 4,$parent_info['id'] , $parameters);



            }

            $parametersstu = array(
                'msg' => array(
                    session('teacher.name'),
                    $class_result_info_add['grade'],
                    $class_result_info_add['name'],
                ),
                'url' => array( 'type' => 0,)
            );

            A('Home/Message')->addPushUserMessage('WEICLASSADDSENDSTUDENT', 3,$addid, $parametersstu);//学生
            (new SMS())->templateSMSAddStudent($parent_tel_data, $student_name, session('teacher.name'), $class_result_info_add['grade'] . $class_result_info_add['name']);

            if ( $sid ) {
                $resouce['code'] = 200;
                $stumap['id'] = $addid;
                $stuinfo = M('auth_student')->where( $stumap )->find();
                $resouce['data'] = $stuinfo;
                $this->ajaxReturn($resouce);
            } else {
                $resouce['code'] = 400;
                $this->ajaxReturn($resouce);
            }
        }
    }

    //已经存在的学生添加进班级
    public function addStu() {
        $classid=getParameter('classid','int',false);

        $classinfo = M('biz_class')->where("id={$classid}")->find();

        $student_name = I('student_name');
        $parent_tel_data = I('parent_tel_data');
        $map = array(
            'student_name' => $student_name,
            'parent_tel' => $parent_tel_data,
        );
        $row = M('auth_student')->where( $map )->find();

        $class_result_info_add=D('Biz_class')->getClassAndGradeInfo($classid);

        if (!empty($row)) {
            $stumap = array(
                'class_id' => $classid,
                'student_id' => $row['id']
            );

            if($classinfo['class_status'] ==1) { //校内班判断是否学校一致
                if ($row['school_id'] != $classinfo['school_id']) {
                    $resouce['code'] = 1001;
                    $this->ajaxReturn($resouce);
                }
            }
            //判断是否加入学校通过
            $studentInfo = D('Auth_student')->getStudentInfo($row['id']);
            if($studentInfo['apply_school_status'] !=1 && $classinfo['class_status'] ==1){
                $resouce['code'] = 1002;
                $this->ajaxReturn($resouce);
            }


            $ids = M('biz_class_student')->where($stumap)->find();
            if (!empty($ids)) { //已加入班级
                $resouce['code'] = 700;
                $this->ajaxReturn($resouce);
            } else {

                if($classinfo['class_status'] ==1) { //校内班判断是否学校一致

                    $whereis['biz_class_student.student_id'] = $row['id'];
                    $whereis['biz_class.class_status'] = 1;

                    $is_class = M('biz_class_student')
                        ->join('biz_class on biz_class.id=biz_class_student.class_id')
                        ->where( $whereis )
                        ->find();

                    if ( !empty($is_class)) {
                        $resouce['code'] = 500;
                        $this->ajaxReturn($resouce);

                    }
                }

                $stumap['create_at'] = time();
                $stumap['update_at'] = time();
                $stumap['status'] = 2;
                $stumap['school_id'] = session('teacher.school_id');

                $findmap['telephone'] = $parent_tel_data;
                $parent_info = M('auth_parent')->where( $findmap )->find();
                $stumap['parent_id'] = $parent_info['id'];

                $bs = M('biz_class_student')->add($stumap);

                $up_map['auth_student'] = 1;
                $up_id['id'] = $row['id'];
                $up_stu = M('auth_student')->where( $up_id )->save( $up_map );

                $bcsrmap['class_id'] = $classid;
                $bcsrmap['student_id'] = $row['id'];
                $bcsr = M('biz_class_student_record')->where( $bcsrmap )->find();
                if (!empty($bcsr)) {
                    M('biz_class_student_record')->where( $bcsrmap )->delete();
                }

                $parameters = array(
                    'msg' => array(
                        session('teacher.name'),
                        $class_result_info_add['grade'],
                        $class_result_info_add['name'],
                    ),
                    'url' => array( 'type' => 1,'data'=>array($row['id'],$student_name))
                );

                A('Home/Message')->addPushUserMessage('ADDSTU', 4,$parent_info['id'] , $parameters); //家长
                $parameters['url'] = array( 'type' => 0);
                //给学生推送
                A('Home/Message')->addPushUserMessage('CLASSADDSENDSTUDENT', 3,$row['id'] , $parameters);//学生

                if ($bs) {
                    $resouce['code'] = 200; //已存在学生
                    $resouce['data'] = $row;
                    $this->ajaxReturn($resouce);
                } else {
                    $resouce['code'] = 400; //已存在学生
                    $this->ajaxReturn($resouce);
                }
            }

        } else {
            $resouce['code'] = 600; //已存在学生
            $this->ajaxReturn($resouce);
        }
    }

    //下载导入实例
    public function downloadStudentListFile()
    {
        $csv=new CSV();
        $filepath="Public/csv/stulist.csv";
        $filename = '导入学生模板表格';
        $csv->downloadFileCopy($filepath,$filename);
    }

    //为班级导入学生
    public function importStudentsList() {

        if(empty($_FILES))
        {
            $this->echoResult(-1,'上传文件为空');
        }
        //$classId = $_GET['classId'];
        $classId = getParameter('classId', 'int',false);
        if(empty($classId))
        {
            $this->echoResult(-1,'班级CLASSID为空,请不要修改网页URL');
        }
        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){
            $this->echoResult(-1,'CSV文件为空');
        }

        //默认是utf8
        $encode = mb_detect_encoding($result['result'][0][0], array('UTF-8','GB2312','GBK','EUC-CN'));
        if($encode=='EUC-CN' || $encode=='GB2312'){
            $isUTF8=1;
        }else if($encode=='GBK'){
            $isUTF8=2;
        }else if($encode=='UTF-8'){
            $isUTF8=0;
        }

        /*$isUTF8 = 1;
        if(!json_encode($result))
        {
          $isUTF8 = 0;
        }*/
        $data=$result['result'];
        $length=$result['length'];
        $data_values='';

        $model=M('auth_student');
        $parent_demol=M('auth_parent');
        $classStudentModel = M('biz_class_student');
        $classModel = M('biz_class');
        $vip_config=C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');

        //查询某个手机号是否存在,不存在则跳过改行
        $errArray = array();

        for($i=1;$i<$length;$i++){

            //TODO:data format verify

            $data[$i][0] =$this->encode_string(2,$data[$i][0]);
            $data[$i][1] =$this->encode_string($isUTF8,$data[$i][1]);

            $verifyResult = $this->verifyImportStudentData($data[$i]);

            if($verifyResult['errCode'] == -1) //verify failed
            {
                $errArray[] = array_merge($data[$i],array($verifyResult['errMsg']));
                continue;
            }
            $dbError = '';

            $model->startTrans();

            $add_data['student_name'] = $data[$i][0];
            $add_data['sex'] =  $data[$i][1];
            //$add_data['parent_name'] = iconv('gb2312', 'utf-8', $data[$i][2]);
            $add_data['student_id'] =$this->encode_string($isUTF8,$data[$i][2]);
            $add_data['birth_date'] = strtotime($this->encode_string($isUTF8,$data[$i][3]));
            $add_data['create_at'] = time();
            $add_data['update_at'] = time();
            $add_data['email'] = $this->encode_string($isUTF8,$data[$i][4]);
            $add_data['parent_tel'] = $this->encode_string($isUTF8,$data[$i][5]);
            //$add_data['id_card'] = $this->NumToStr(iconv('gb2312', 'utf-8', $data[$i][6]));
            $add_data['id_card'] = $this->saveNumToStr($this->encode_string($isUTF8,$data[$i][6]));
            $add_data['password'] = sha1('123456');
            $add_data['school_id'] = session('teacher.school_id');

            $parent_tel=$add_data['parent_tel'];
            $parent_result=$parent_demol->where("telephone=".$parent_tel)->field('id,parent_name,telephone')->find();
            if(!empty($parent_result)){
                $add_data['parent_id']=$parent_result['id'];
                $add_data['parent_name']=$parent_result['parent_name'];
                $add_data['parent_tel']=$parent_result['telephone'];
            }

            $check['student_name'] = $add_data['student_name'];
            $check['parent_tel'] = $add_data['parent_tel'];

            $student_result = $model->where($check)->find();
            $stuId=0;
            $is_register=0;
            if(empty($student_result)){
                $stuId = $model->add($add_data);
                if(!$stuId){
                    $dbError = '学生注册错误';
                }else{
                    $is_register=1;
                    //发送短信 + 推送消息中心
                    $classInfo = D('Biz_class')->getGradeClass($classId);
                    $parametersstu = array(
                        'msg' => array(
                            session('teacher.name'),
                            $classInfo['grade'],
                            $classInfo['class_name'],
                        ),
                        'url' => array( 'type' => 0,)
                    );

                    A('Home/Message')->addPushUserMessage('WEICLASSADDSENDSTUDENT', ROLE_STUDENT ,$stuId, $parametersstu);//学生
                    (new SMS())->templateSMSAddStudent($add_data['parent_tel'], $add_data['student_name'], session('teacher.name'), $classInfo['grade'] . $classInfo['class_name']);


                }
            }else{
                unset($add_data['password']);
                if(false == $model->where($check)->save($add_data))
                {
                    $dbError = "更新学生信息失败";
                }
                $result = $model->where($check)->field('id')->find();
                $stuId = $result['id'];
            }
            //todo: add join table

            $joinData['class_id'] = $classId;
            $joinData['student_id'] = $stuId ;
            $join_result = $classStudentModel->where($joinData)->find();
            if(empty($join_result)){
                $joinData['status'] = 2;
                $joinData['create_at'] = time();
                $joinData['update_at'] = time();
                if(!$classStudentModel->add($joinData)){
                    $dbError = '学生关联班级失败';
                }

                if(false == $classModel->where("id=$classId")->setInc('student_count', 1)){
                    $dbError = '班级人数加1错误';
                }

                if($is_register==1 && $dbError==''){
                    //加入权限
                    if($vip_config && $vip_config<=3){
                        if(empty($parent_result)){
                            $result=give_new_vip_operation(3, $vip_config, $stuId,session('teacher.school_id'));
                        }else{
                            $result=give_new_vip_operation(3, $vip_config, $stuId,session('teacher.school_id'));
                        }
                        if($result['status']=='failed'){
                            $dbError='权限操作失败';
                        }
                        /*
                        $vipdata = array(
                            'user_id' => $stuId,
                            'role_id' => 3,
                            'auth_id' => 4,
                            'auth_start_time' => time(),
                            'auth_end_time' => time()+3600*24*30*3,
                            'timetype' => 1,
                        );

                        if($vip_config==1){
                            //赠送90天vip
                           $vipdata['auth_id']=4;

                        }elseif($vip_config==2){
                            //普通权限
                            $vipdata['auth_id']=2;
                            $vipdata['auth_start_time']=0;
                            $vipdata['auth_end_time']=0;
                            $vipdata['timetype']=0;

                        }elseif($vip_config==3){
                            $shcool_model=M('dict_schoollist');
                            $school_info=$shcool_model->where('id='.session('teacher.school_id'))->find();
                            //根据学校的权限来赋予学生的权限
                            if ($school_info['user_auth'] == 3 && time() >= $school_info['auth_start_time'] && time() < $school_info['auth_end_time'] ) {
                                //给予学校的权限
                                $vipdata['timetype']=$school_info['timetype'];
                                $vipdata['auth_id']=3;
                                $vipdata['auth_end_time']=$school_info['auth_end_time'];

                            } else {
                                //普通权限
                                $vipdata['auth_id']=2;
                                $vipdata['auth_start_time']=0;
                                $vipdata['auth_end_time']=0;
                                $vipdata['timetype']=0;
                            }
                        }
                        $auth_type_use = D('Account_auths');
                        $auth_list = $auth_type_use->addUserVip( $vipdata );
                        */
                    }
                }
            }else{
                $dbError = ('该学生已经在班级中');
            }
            if('' != $dbError)
            {
                $model->rollback();
                $errArray[] = array_merge($data[$i],array($dbError));
            }
            else
            {
                $model->commit();
                $parentTel   = $this->encode_string($isUTF8,$data[$i][5]);
                $parentInfo = D('Auth_parent')->getParentInfoByTelephone($parentTel);
                $classInfo = D('Biz_class')->getGradeClass($classId);
                if($is_register == 0) {
                    $parameter_arr = array(
                        'msg' => array(session('teacher.name'), $data[$i][0], $classInfo['grade'],
                            $classInfo['class_name']),
                        'url' => array(
                            'type' => 0
                        )
                    );
                    A('Home/Message')->addPushUserMessage('REGISTERADDSTU', ROLE_PARENT, $parentInfo['id'], $parameter_arr);
                }
                else
                {
                    $parameters = array(
                        'msg' => array(
                            session('teacher.name'),
                            $classInfo['grade'],
                            $classInfo['class_name'],
                        ),
                        'url' => array( 'type' => 1,'data'=>array($stuId,$add_data['student_name']))
                    );

                    A('Home/Message')->addPushUserMessage('ADDSTU', ROLE_PARENT ,$parentInfo['id'] , $parameters); //家长

                    $parameters = array(
                        'msg' => array(
                            session('teacher.name'),
                            $classInfo['grade'],
                            $classInfo['class_name'],
                        ),
                        'url' => array( 'type' => 0)
                    );

                    //给学生推送
                    A('Home/Message')->addPushUserMessage('CLASSADDSENDSTUDENT', ROLE_STUDENT ,$stuId , $parameters);//学生
                }
            }
        }
        if(count($errArray) != 0)
        {
            $this->echoResult(-2,json_encode($errArray));
        }
        echo $this->echoResult(0,'导入成功');
    }


    //自建通过拒绝学生
    public function getAdoptStu() {
        $errorInfo = '';
        $classId=getParameter('classid','int',false);
        $student_id=getParameter('student_id','int',false);
        $status=getParameter('status','int',false);
        $biz = D('Biz_class')->updateClassStudentStatus($classId,$student_id,$status,$errorInfo);

        $class_result_info=D('Biz_class')->getClassAndGradeInfo($classId);
        $student_info = D('Auth_student')->getStudentInfo( $student_id );

        if ( $status == 2) { //通过

            $parameters = array(
                'msg' => array(
                    session('teacher.name'),
                    $student_info['student_name'],
                    $class_result_info['grade'],
                    $class_result_info['name'],
                ),
                'url' => array( 'type' => 1,'data'=>array($student_info['id'],$student_info['student_name']))
            );

            A('Home/Message')->addPushUserMessage('GETADOPTSTU2', 4,$student_info['parent_id'] , $parameters);

            //给学生发
            $parametersstu = array(
                'msg' => array(
                    session('teacher.name'),
                    $class_result_info['grade'],
                    $class_result_info['name'],
                ),
                'url' => array( 'type' => 0,)
            );

            A('Home/Message')->addPushUserMessage('STUGETADOPTSTU', 3,$student_id, $parametersstu);//学生

        } else {//拒绝
            $parameters = array(
                'msg' => array(
                    session('teacher.name'),
                    $student_info['student_name'],
                    $class_result_info['grade'],
                    $class_result_info['name'],
                ),
                'url' => array( 'type' => 0,)
            );

            A('Home/Message')->addPushUserMessage('GETADOPTSTU3', 4,$student_info['parent_id'] , $parameters);

            //给学生发
            $parametersstu = array(
                'msg' => array(
                    session('teacher.name'),
                    $class_result_info['grade'],
                    $class_result_info['name'],
                ),
                'url' => array( 'type' => 0,)
            );

            A('Home/Message')->addPushUserMessage('STUGETADOPTSTUDISABLE', 3,$student_id, $parametersstu);//学生
        }

        if ($biz) {
            $resouce['code'] = 200; //班级通过成功
            $this->ajaxReturn($resouce);
        } else {
            $resouce['code'] = 400; //班级通过成功
            $resouce['error'] = $errorInfo; //班级通过成功
            $this->ajaxReturn($resouce);
        }
    }
    //批量通过学生 批量不通过学生
    public function setBatchAdoptAndNodoptStu() {
        $classId=getParameter('classid','int',false);
        $student_id=getParameter('student_id','str',false);
        $student_id_copy = $student_id;
        $student_id_copy = explode(',',$student_id_copy);

        $status=getParameter('status','int',false);

        $class_result_info=D('Biz_class')->getClassAndGradeInfo($classId);


        if (!empty($student_id)) {
            $student_id = ltrim($student_id,',');
            $student_id = explode(',',$student_id);
        }

        $where = array(
            'class_id' => $classId,
            'student_id' => array('in',$student_id),
        );

        $map['status'] = $status;

        $ids = M('biz_class_student')->where( $where )->save( $map );

        if ( $ids ) {

            if (!empty($student_id_copy)) {
                foreach ($student_id_copy as $k=>$sid) {
                    if (!empty($sid)) {
                        $student_info = D('Auth_student')->getStudentInfo( $sid );

                        if ( $status == 2) { //通过

                            $parameters = array(
                                'msg' => array(
                                    session('teacher.name'),
                                    $student_info['student_name'],
                                    $class_result_info['grade'],
                                    $class_result_info['name'],
                                ),
                                'url' => array( 'type' => 1,'data'=>array($student_info['id'],$student_info['student_name']))
                            );

                            A('Home/Message')->addPushUserMessage('GETADOPTSTU2', 4,$student_info['parent_id'] , $parameters);
                        } else {//拒绝
                            $parameters = array(
                                'msg' => array(
                                    session('teacher.name'),
                                    $student_info['student_name'],
                                    $class_result_info['grade'],
                                    $class_result_info['name'],
                                ),
                                'url' => array( 'type' => 0,)
                            );

                            A('Home/Message')->addPushUserMessage('GETADOPTSTU3', 4,$student_info['parent_id'] , $parameters);
                        }

                    }
                }
            }

            $resouce['code'] = 200; //班级通过成功
            $this->ajaxReturn($resouce);
        } else {
            $resouce['code'] = 400; //班级通过成功
            $this->ajaxReturn($resouce);
        }
    }

    //移交班级
    public function teacherTransferclass() {

        $Transferclassphone=getParameter('Transferclassphone','str',false);
        $classid=getParameter('classid','int',false);


        $data = array(
            'telephone' => $Transferclassphone
        );
        $is_teacher = M('auth_teacher')->where( $data )->find();
        if (!empty($is_teacher)) {

            if ( $Transferclassphone == session('teacher.telephone') ) { //对不起不可以移交给自己
                $resouce['code'] = 600;
                $resouce['info'] = '不能移交自己';
                $this->ajaxReturn($resouce);
            }

            if (D('Biz_class')->transferClass($classid, session('teacher.id'), $course, $Transferclassphone, $errorInfo)){

                $parameters = array(
                    'msg' => array(
                    ),
                    'url' => array( 'type' => 0 )
                );

                A('Home/Message')->addPushUserMessage('YIJIAO_CLASS', 2, $is_teacher['id'] , $parameters);

                $resouce['code'] = 200;

                $hmap = array(
                    'send_teacherid' =>session('teacher.id'),
                    'class_id' => $classid
                );
                $hrow = M('biz_class_handsoff')->where( $hmap )->find();

                if (!empty($hrow)) {
                    $hteacher['id'] = $hrow['dest_teacherid'];
                    $hoffrow = M('auth_teacher')->where( $hteacher )->field('id,name,telephone')->find();
                    $resouce['info'] = $hoffrow;
                }

                $this->ajaxReturn($resouce);

            } else {
                $resouce['code'] = 500;
                $resouce['info'] = '移交失败请重新尝试';
                $this->ajaxReturn($resouce);
            }
/*
            if ( $Transferclassphone == session('teacher.telephone') ) { //对不起不可以移交给自己
                $resouce['code'] = 600;
                $resouce['info'] = '不能移交自己';
                $this->ajaxReturn($resouce);
            }

            //if ($is_teacher['school_id'] != session('teacher.school_id')) {
                $handsoff = array(
                    'send_teacherid' => session('teacher.id'),
                    'dest_teacherid' => $is_teacher['id'],
                    'class_id' => $classid,
                    'handsoff_status' => array('neq',3),
                );

                $hrow = M('biz_class_handsoff')->where( $handsoff )->find();

                if (!empty($hrow)) {

                    $resouce['code'] = 800;
                    $resouce['info'] = '已发送移交班级';
                    $this->ajaxReturn($resouce);

                } else {
                    $hid = M('biz_class_handsoff')->add( $handsoff );

                    $parameters = array(
                        'msg' => array(
                        ),
                        'url' => array( 'type' => 0 )
                    );

                    A('Home/Message')->addPushUserMessage('YIJIAO_CLASS', 2, $is_teacher['id'] , $parameters);

                    if ( $hid ) {
                        $resouce['code'] = 200;

                        $hmap = array(
                            'send_teacherid' =>session('teacher.id'),
                            'class_id' => $classid
                        );
                        $hrow = M('biz_class_handsoff')->where( $hmap )->find();

                        $flagmap['flag'] = 2;
                        $whereclassid['id'] = $classid;
                        $did = M('biz_class')->where( $whereclassid )->save( $flagmap );

                        if (!empty($hrow)) {
                            $hteacher['id'] = $hrow['dest_teacherid'];
                            $hoffrow = M('auth_teacher')->where( $hteacher )->field('id,name,telephone')->find();
                            $resouce['info'] = $hoffrow;
                        }

                        $this->ajaxReturn($resouce);
                    } else {
                        $resouce['code'] = 500;
                        $resouce['info'] = '移交失败请重新尝试';
                        $this->ajaxReturn($resouce);
                    }
                }*/

           /* } else { //不是同一个学校的
                $resouce['code'] = 700;
                $resouce['info'] = '移交的老师和您不是同一个学校';
                $this->ajaxReturn($resouce);
            }*/

        }else{
            $resouce['code'] = 400; //老师不存在
            $resouce['info'] = '移交的老师不存在';
            $this->ajaxReturn($resouce);
        }
    }

    //个人撤销班级
    public function teacherRevokeClass() {

        $student_id=getParameter('student_id','int',false);
        $classid=getParameter('classid','int',false);


        $data = array(
            'send_teacherid' => session('teacher.id'),
            'dest_teacherid' => $student_id,
            'class_id' => $classid,
        );

        $class_send_info = M('biz_class_handsoff')->where( $data )->find();

        $delid = M('biz_class_handsoff')->where( $data )->delete();

        $flagmap['flag'] = 1;
        $whereclassid['id'] = $classid;
        $did = M('biz_class')->where( $whereclassid )->save( $flagmap );

        $class_info = D('Biz_class')->getClassAndGradeInfo($classid);

        if ( $delid){

            $parameters = array(
                'msg' => array(
                    session('teacher.name'),
                    $class_info['grade'],
                    $class_info['name'],
                ),
                'url' => array( 'type' => 0 )
            );

            A('Home/Message')->addPushUserMessage('JIAOSHI_CHECIAO_CLASS', 2, $class_send_info['dest_teacherid'], $parameters);

            $resouce['code'] = 200; //老师不存在
            $resouce['info'] = '撤销成功';
            $this->ajaxReturn($resouce);
        } else {
            $resouce['code'] = 400; //老师不存在
            $resouce['info'] = '撤销失败';
            $this->ajaxReturn($resouce);
        }
    }

    //删除自建班班级
    public function deleteClassAlldata(){

        $classid=getParameter('classid','int',false);
        $status=getParameter('status','int',false);

        $errorInfo = '';
        $userId = session('teacher.id');

        $map['send_teacherid'] = $userId;
        $map['class_id'] = $classid;
        $map['handsoff_status'] = array('neq',2);

        $mapclass = M('biz_class_handsoff')->where( $map )->find();

        if (!empty($mapclass)) {
            if ($status==1) {
                $resouce['code'] = 500; //老师不存在
                $resouce['title'] = '离开班级'; //老师不存在
                $resouce['info'] = '该班级在移交中，不可以离开'; //老师不存在
                $this->ajaxReturn($resouce);
            } else {
                $resouce['code'] = 500; //老师不存在
                $resouce['title'] = '删除班级'; //老师不存在
                $resouce['info'] = '该班级在移交中，不可以删除'; //老师不存在
                $this->ajaxReturn($resouce);
            }
        }

        if ($status==1) {
            $biz = D('Biz_class')->leaveClass($classid,2,$userId,$errorInfo);
        } else {
            $biz = D('Biz_class')->deleteClass($classid,$errorInfo);
        }

        if ( $biz ) {
            $resouce['code'] = 200; //老师不存在
            $this->ajaxReturn($resouce);
        } else {

            $resouce['code'] = 400; //老师不存在
            $resouce['title'] = '离开失败'; //老师不存在
            $resouce['info'] = '班级离开失败'; //老师不存在
            $this->ajaxReturn($resouce);
        }
    }

    //筛选老师
    public function findTeacherList() {

        $classid=getParameter('classid','int',false);
        $keyword=getParameter('keyword','str',false);

        $mapwhere = array(
            'biz_class_teacher.class_id' => $classid,
        );

        if (!empty($keyword)) {
            $mapwhere['auth_teacher.name|auth_teacher.telephone'] = array('like', '%' . $keyword . '%');
        }

        $Model = M('biz_class_teacher');
        $result = $Model
            ->join('auth_teacher on auth_teacher.id=biz_class_teacher.teacher_id')
            ->join('dict_course on dict_course.id=biz_class_teacher.course_id')
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

        $Model = M('biz_class_student');
        $countstu = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->where("biz_class_student.class_id=$classid and biz_class_student.status = 2")
            ->count();

        $bctModel = M('biz_class_teacher');
        $teachercount = $bctModel
            ->where("biz_class_teacher.class_id=$classid")
            ->count();

        $bctmap['class_id'] = $classid;
        $bctmap['teacher_id'] = session('teacher.id');
        $dict_names = M('biz_class_teacher')
            ->join('dict_course on dict_course.id=biz_class_teacher.course_id')
            ->where($bctmap)
            ->select();

        if (!empty($dict_names)) {
            $dict_string = array();
            foreach ($dict_names as $k=>$v){
                $dict_string[] = $v['course_name'];
            }
        }

        if (!empty($dict_string)){
            $dict_string_name = implode(',',$dict_string);
        }

        $data_stu['class_info'] = $row;
        $data_stu['stu_count'] = $countstu;
        $data_stu['tea_count'] = $teachercount;
        $count = $countstu+$teachercount;

        $this->assign('count',$count);
        $this->assign('data_stu',$data_stu);
        $this->assign('classid',$classid);
        $this->assign('list',$result);
        $this->assign('dict_string_name',$dict_string_name);

        $html = $this->fetch('findteacher');
        $this->ajaxReturn($html);
    }

    //修改班级名称
    public function saveClassDataName() {

        $classid=getParameter('classid','int',false);
        $xiugaiclass=getParameter('xiugaiclass','str',false);

        $data['id'] = $classid;
        $map['name'] = $xiugaiclass;

        $classinfo = M('biz_class')->where( $data )->find();

        $straddclass =  $map['name'].'-'.$classinfo['grade_id']; //传过来的

        $classmap['teacher_id'] = session('teacher.id');
        $classmap['class_status'] = 2;

        $allclassinfo = M('biz_class_teacher')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->where( $classmap )
            ->select();

        $is_array_teacher = array();

        foreach ($allclassinfo as $k=>$v) {
            $is_array_teacher[] = $v['name'].'-'.$v['grade_id'];
        }
        //print_r($straddclass);
        //print_r($is_array_teacher);die();

        if(!in_array($straddclass,$is_array_teacher)) {
            $ids = M('biz_class')->where( $data )->save( $map );

            if ( $ids !== false) {
                $data['code'] = 200;
                $this->ajaxReturn($data);
            } else {
                $data['code'] = 400;
                $this->ajaxReturn($data);
            }
        } else { //你已创建了该班级
            $data['code'] = 600;
            $this->ajaxReturn($data);
        }




    }
    //把老师移除班级
    public function delTeacherClass(){

        $classid=getParameter('classid','int',false);
        $teacher_id=getParameter('teacher_id','int',false);


        $map['class_id'] = $classid;
        $map['teacher_id'] = $teacher_id;

        $ids = M('biz_class_teacher')->where($map)->delete();

        if ( $ids ) {
            $data['code'] = 200;
            $this->ajaxReturn($data);
        } else {
            $data['code'] = 400;
            $this->ajaxReturn($data);
        }

    }

    //校建班移交班级
    public function teacherSchoolTransferclass() {

        $classid=getParameter('classid','int',false);
        $TeacherPhone=getParameter('TeacherPhone','str',false);
        $course_id=getParameter('course_id','int',false);


        $data = array(
            'telephone' => $TeacherPhone
        );
        $is_teacher = M('auth_teacher')->where( $data )->find();

        if ($is_teacher['apply_school_status'] !=1) {
            $resouce['code'] = 600;
            $resouce['info'] = '移交教师没有通过学校的审核';
            $this->ajaxReturn($resouce);
        }

        if (!empty($is_teacher)) {
            $sendclassmp['class_id'] = $classid;
            $sendclassmp['teacher_id'] = $is_teacher['id'];
            $sendclassmp['course_id'] = $course_id;

            $is_teacher_class = M('biz_class_teacher')->where( $sendclassmp )->find();

            if (!empty($is_teacher_class)) {
                $resouce['code'] = 600;
                $resouce['info'] = '移交的老师已经是这个班级的学科老师';
                $this->ajaxReturn($resouce);
            }

            if ( $TeacherPhone == session('teacher.telephone') ) { //对不起不可以移交给自己
                $resouce['code'] = 600;
                $resouce['info'] = '不能移交自己';
                $this->ajaxReturn($resouce);
            }

            if ($is_teacher['school_id'] != session('teacher.school_id')) {
                $resouce['code'] = 700;
                $resouce['info'] = '移交的老师和您不是同一个学校';
                $this->ajaxReturn($resouce);
            } else { //不是同一个学校的

                $atsmap['teacher_id'] = $is_teacher['id'];
                $is_teacher_course = M('auth_teacher_second')->where( $atsmap )->select();

                $is_course = array();
                foreach ($is_teacher_course as $k=>$v ) {
                    $is_course[] = $v['course_id'];
                }
                $is_course = array_unique($is_course);

                if (!in_array($course_id,$is_course)) {
                    $resouce['code'] = 800;
                    $resouce['info'] = '移交的老师没有任教该学科';
                    $this->ajaxReturn($resouce);
                }

                $addhandoff = array(
                    'dest_teacherid' => $is_teacher['id'],
                    'class_id' => $classid,
                    'course_id' => $course_id,
                );

                $oneoff = M('biz_class_handsoff')->where( $addhandoff )->find();

                if (!empty($oneoff)) {

                   // if ($oneoff['handsoff_status'] == 3) {

                    $findmap = array(
                        'send_teacherid' => session('teacher.id'),
                        'class_id' => $classid,
                        'course_id' => $course_id,
                    );
                    $savemap['handsoff_status'] = 1;

                    $handis = M('biz_class_handsoff')->where( $findmap )->save($savemap);


                    /*$classModal = M('biz_class');

                    $classinfo =   $classModal->where("id=$classid")->find();
                    $namenianji = M('dict_grade')->where("id=".$classinfo['grade_id'])->find();

                    $allstu = D('Biz_class_student')->getClassStudent($classinfo['id']);


                    $parameters = array( 'msg' => array($is_teacher['name']."({$is_teacher['telephone']})") , 'url' => array( 'type' => 0));
                    A('Home/Message')->addPushUserMessage('CLASSMOVE_SENDER',2,session('teacher.id'),$parameters);

                    //接收放数据
                    $rev_parameters = array( 'msg' => array($namenianji['grade'],$classinfo['name'],session('teacher.name'),session('teacher.telephone'),$classinfo['name']) , 'url' => array( 'type' => 0));
                    A('Home/Message')->addPushUserMessage('CLASSMOVE_RECEIVER',2,$is_teacher['id'],$rev_parameters);
                    $idsstring = '';

                    foreach ($allstu as $k=>$v) {
                        if ($k==0) {
                            $idsstring .= $v['id'];
                        } else {
                            $idsstring .= ','.$v['id'];
                        }
                        //给家长发
                        $parentparameters = array( 'msg' => array($v['student_name'],$namenianji['grade'],$classinfo['name'],$is_teacher['name'],$is_teacher['telephone']) , 'url' => array( 'type' => 0));
                        A('Home/Message')->addPushUserMessage('CLASSMOVE_SEND_STUDENT_CHILD',4,$v['parent_id'],$parentparameters);
                    }
                    //给学生移交发送信息
                    $stuparameters = array( 'msg' => array($namenianji['grade'],$classinfo['name'],$is_teacher['name'],$is_teacher['telephone']) , 'url' => array( 'type' => 0));
                    A('Home/Message')->addPushUserMessage('CLASSMOVE_SEND_STUDENT',3,$idsstring,$stuparameters);*/


                    if ( $handis ) {
                        $resouce['code'] = 200;
                        $this->ajaxReturn($resouce);
                    } else {
                        $resouce['code'] = 300;
                        $resouce['info'] = '移交的老师失败';
                        $this->ajaxReturn($resouce);
                    }

                  /*  } else {

                        $resouce['code'] = 900;
                        $resouce['info'] = '该班级已经被移交';
                        $this->ajaxReturn($resouce);
                    }*/

                } else { //入库

                    $findmap = array(
                        'send_teacherid' => session('teacher.id'),
                        'class_id' => $classid,
                        'course_id' => $course_id,
                    );

                    $handis = M('biz_class_handsoff')->where( $findmap )->find();

                    if (!empty($handis)) {
                        $resouce['code'] = 1000;
                        $resouce['info'] = '该班级已经在移交中';
                        $this->ajaxReturn($resouce);
                    }

                    $addhanddata = array(
                        'send_teacherid' => session('teacher.id'),
                        'dest_teacherid' => $is_teacher['id'],
                        'class_id' => $classid,
                        'course_id' => $course_id,
                    );
                    $handid = M('biz_class_handsoff')->add( $addhanddata );

                    if ( $handid ) {
                        $resouce['code'] = 200;
                        $this->ajaxReturn($resouce);
                    } else {
                        $resouce['code'] = 300;
                        $resouce['info'] = '移交的老师失败';
                        $this->ajaxReturn($resouce);
                    }
                }

            }

        }else{
            $resouce['code'] = 400; //老师不存在
            $resouce['info'] = '移交的老师不存在';
            $this->ajaxReturn($resouce);
        }
    }

    public function revokeclassTeacherDel() {

        $id=getParameter('id','int',false);

        $data = array(
            'id' => $id,
        );
        $delid = M('biz_class_handsoff')->where( $data )->delete();

        if ( $delid){
            $resouce['code'] = 200; //老师不存在
            $resouce['info'] = '撤销成功';
            $this->ajaxReturn($resouce);
        } else {
            $resouce['code'] = 400; //老师不存在
            $resouce['info'] = '撤销失败';
            $this->ajaxReturn($resouce);
        }
    }

    public function teacherdownloadFileCsv() {

        $filename=getParameter('filename','str',false);
        $str=getParameter('str','str',false);

        $str['str'] = iconv( 'utf-8','gb2312', $str);
        $csv=new CSV();
        $csv->downloadFileCsv($filename,$str);
    }


    //判断是否有权限
    public function isAuth( $c_a ) {

        $teacher = session('auth_teacher');
        $parent = session('auth_parent');
        $student = session('auth_student');
        $admin = session('admin');
        if (!empty($teacher)) {

            $is_auth = in_array($c_a, session('auth_teacher'));

        } elseif(!empty($parent)) {
            $is_auth = in_array($c_a, session('auth_parent'));

        }elseif(!empty($student)){
            $is_auth = in_array($c_a, session('auth_student'));

        } elseif(!empty($admin)) {
            return true;
        }

        if ( $is_auth ) {
            return true;
        } else {
            return false;
        }
    }

    public function getJoinedCourseByClassId()
    {
        $classId = getParameter('classId','int');

        $classCategory = D('Biz_class')->getClassCategory($classId);
        if (empty($classCategory))
            $this->showMessage(500, '班级不存在', array());
        //判断是校建还是自建
        if (SCHOOL_CLASS == $classCategory){
            $where['biz_class.id'] = $classId;
            $where['auth_teacher.id'] = session('teacher.id');
            $resultTemp = D('Biz_class')->getTeacherSchoolClassData($where);
            foreach($resultTemp as $key=>$val){
                $result[$key]['id'] = $val['course_id'];
                $result[$key]['name'] = $val['course_name'];
            }
        }
        else{
            $result = D('Dict_course')->getCourseList();
        }
        $this->showMessage(200,'success',$result);
    }
}
