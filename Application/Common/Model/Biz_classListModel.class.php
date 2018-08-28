<?php
namespace Common\Model;
use Think\Model; 


class Biz_classListModel extends Model{
    
    public    $model='';
    protected $tableName = 'biz_class';  
	
    public function __construct(){
        parent::__construct();  
        $this->model=M($this->tableName);
    } 
    
    //判断用户是否是第一次登陆
    public function isFirstLogin(){
        $is_login_map['is_login'] = 2;
        $where_is_login['id'] = session('teacher.id');
        M('auth_teacher')->where( $where_is_login )->save( $is_login_map );
    }
    //判断用户是否是第一次查看小黑板
    public function isFirstLoginBoard(){
        $is_login_map['is_board'] = 2;
        $where_is_login['id'] = session('teacher.id');
        M('auth_teacher')->where( $where_is_login )->save( $is_login_map );
    }

    //判断用户是否是第一次查看小黑板
    public function isFirstLoginParentBoard(){
        $is_login_map['is_board'] = 2;
        $where_is_login['id'] = session('parent.id');
        M('auth_parent')->where( $where_is_login )->save( $is_login_map );
    }

    //判断用户是否是第一次查看小黑板
    public function isFirstLoginStudentBoard(){
        $is_login_map['is_board'] = 2;
        $where_is_login['id'] = session('student.id');
        M('auth_student')->where( $where_is_login )->save( $is_login_map );
    }



    //获取老师的所有的班级包括自建班
    public function getClassListTeacherAll() {
        $Model = M('biz_class_teacher');

        $where['biz_class_teacher.teacher_id'] =  session("teacher.id");
        $where['biz_class.is_delete'] =  0;
        $where['biz_class.flag'] =  array('neq',0);

        $result = $Model
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join('left join dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('left join dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('biz_class_student on biz_class.id=biz_class_student.class_id and biz_class_student.status=2','left')
            ->join('auth_student on auth_student.id=biz_class_student.student_id','left')
            ->field('biz_class.*,count(auth_student.id) student_count,dict_schoollist.school_name,dict_grade.grade,dict_grade.id as did')
            ->where($where)
            ->group('biz_class.id')
            ->order('dict_grade.id ASC,biz_class.create_at desc')
            //->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        foreach ($result as $k=>$v) {
            if ($v['class_status'] == 1) {
                $d_m['id'] = $v['school_id'];
                $sc_name = M('dict_schoollist')->where( $d_m )->find();
                if ($sc_name['flag'] == 0) {
                    $result[$k]['flag'] = 0;
                    unset($result[$k]);
                }

            }
        }

        return $result;
    }


    //获取老师的所有的班级包括自建班
    public function getClassListTeacherAllCopy() {
        $Model = M('biz_class_teacher');

        $where['biz_class_teacher.teacher_id'] =  session("teacher.id");
        $where['biz_class.is_delete'] =  0;

        $result = $Model
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join('left join dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('left join dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('biz_class_student on biz_class.id=biz_class_student.class_id and biz_class_student.status=2','left')
            ->join('auth_student on auth_student.id=biz_class_student.student_id','left')
            ->field('biz_class.*,count(auth_student.id) student_count,dict_schoollist.school_name,dict_grade.grade,dict_grade.id as did')
            ->where($where)
            ->group('biz_class.id')
            ->order('biz_class.create_at desc')
            //->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        foreach ($result as $k=>$v) {
            if ($v['class_status'] == 1) {
                $d_m['id'] = $v['school_id'];
                $sc_name = M('dict_schoollist')->where( $d_m )->find();
                if ($sc_name['flag'] == 0) {
                    $result[$k]['flag'] = 0;
                }
            }
        }

        return $result;
    }

    //获取学生列表
    public function getClassStuList( $classid,$keyword) {
        $mapwhere = array(
            'biz_class_student.class_id' => $classid,
            'biz_class_student.status' => 2
        );

        if (!empty($keyword)) {
            $mapwhere['auth_student.student_name|auth_student.parent_tel'] = array('like', '%' . $keyword . '%');
        }


        $Model = M('biz_class_student');
        $result = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->join('biz_class on biz_class.id=biz_class_student.class_id')
            ->join('left join biz_class_teacher on biz_class_teacher.class_id=biz_class.id')
            ->field('biz_class.school_id,biz_class_teacher.teacher_id,biz_class.id,auth_student.parent_tel,auth_student.student_name,auth_student.sex,auth_student.sex,auth_student.avatar,auth_student.email,biz_class_student.student_id')
            ->where( $mapwhere )
            ->order('biz_class_student.create_at desc')
            ->group('biz_class_student.student_id')
            ->select();

        for($i=0;$i<sizeof($result);$i++) {
            $result[$i]['birth_date'] = date("Y-m-d",$result[$i]['birth_date']);
            if(empty($result[$i]['avatar']) || $result[$i]['avatar'] =='') {
                $result[$i]['avatar'] = 'default.jpg';
            }
        }


        $idmap['id'] = $classid;
        $row = M('biz_class')->where( $idmap )->field('class_code,flag,class_status')->find();

        if ($row['flag'] ==0) {
            $row['flag_name'] = '停用';
        }
        if ($row['flag'] ==1) {
            $row['flag_name'] = '正常';
        }
        if ($row['flag'] ==2) {
            $row['flag_name'] = '移交中';
        }

        $countstu = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->where("biz_class_student.class_id=$classid and biz_class_student.status = 2")
            ->count();

        $dengdaistu = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->where("biz_class_student.class_id=$classid and biz_class_student.status = 1")
            ->count();

        $datainfo['class_info'] = $row;
        $datainfo['stu_count'] = $countstu;
        $datainfo['wating_stu'] = $dengdaistu;
        $datainfo['list'] = $result;

        return $datainfo;

    }

    //获取老师和学校列表
    public function getClassStuandTeacherList( $classid, $keyword ) {
        $mapwhere = array(
            'biz_class_student.class_id' => $classid,
            'biz_class_student.status' => 2
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

        $datainfo['class_info'] = $row;
        $datainfo['stu_count'] = $countstu;
        $datainfo['tea_count'] = $teachercount;
        $count = $countstu+$teachercount;
        $datainfo['count'] = $count;
        $datainfo['list'] = $result;
        $datainfo['dict_string_name'] = $dict_string_name;

        return $datainfo;
    }


    //班级信息设置
    public function setClassButtonModel( $classid ) {
        $Model = M('biz_class_student');

        $idmap['biz_class.id'] = $classid;
        $row = M('biz_class')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->where( $idmap )
            ->field('biz_class.class_code,biz_class.flag,biz_class.class_status,biz_class.name,biz_class.class_teacher,dict_grade.grade')
            ->find();

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


        return $data_stu;
    }
    //查询教师移交的班级
    public function findTeachClassHandoff( $classid ) {

        $hmap = array(
            'send_teacherid' =>session('teacher.id'),
            'class_id' => $classid,
            'handsoff_status' => array('neq',2),
        );
        $hrow = M('biz_class_handsoff')->where( $hmap )->find();

        return $hrow;
    }

    //老师设置校建班信息
    public function teacherSetclass( $classid ) {

        $idmap['biz_class.id'] = $classid;

        $row = M('biz_class')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->where( $idmap )
            ->field('biz_class.school_id,biz_class.class_code,biz_class.flag,biz_class.class_status,biz_class.name,biz_class.class_teacher,dict_grade.grade')
            ->find();

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

        return $row;
    }

    //获取校建班班级状态
    public function getSchoolClassStatus( $classId ) {
        $map['id'] = $classId;
        $classinfo = M('biz_class')
                    ->where( $map )
                    ->find();

        if ( $classinfo['class_status'] == 1 ) {
            $schmap['id'] = $classinfo['school_id'];
            $school_statsu = M('dict_schoollist')->where( $schmap )->find();


            if ($classinfo['flag'] == 1) {
                if ( $school_statsu['flag'] == 1) {
                    $status = 1;
                } else {
                    $status = 2;
                }
            } else {
                $status = 2;
            }
        } else {

            if ($classinfo['flag'] == 0) {
                $status = 2;
            } else {
                $status = 1;
            }

        }
        return $status;
    }

    //根据班级id获取班级成员的作业情况
    public function getHomeworkScoreByClass( $classId,$where ) {
        $Model = M('biz_homework_student_details');

        $c1['biz_homework.class_id'] = $classId;
        if (!empty($where)) {
            $c1 = array_merge($c1,$where);
        }

        $result = $Model
            ->join('biz_homework on biz_homework.id=biz_homework_student_details.homework_id')
            ->join('biz_homework_score_details on biz_homework_score_details.student_id=biz_homework_student_details.student_id and biz_homework_score_details.homework_id=biz_homework.id')
            ->join("RIGHT JOIN (select id,student_name from auth_student join (select student_id from biz_class_student where status=2 and class_id=$classId) a on a.student_id = auth_student.id) b on b.id=biz_homework_student_details.student_id and biz_homework_student_details.homework_id is not null")
            ->field("biz_homework.id as homework_id,biz_homework.homework_name,biz_homework_student_details.points,b.student_name,b.id as student_id")
            ->where($c1)
            ->order('biz_homework.create_at asc')->select();


        return $result;
    }

    //根据班级id和学生id 查看对学生的寄语、
    public function getTeacherMessage( $where ) {
        $Model = M('biz_student_learning_path');

        $path = $Model
            ->where( $where )
            ->order('create_at desc')
            ->select();

        return $path;
    }
    //删除老师寄语
    public function dTMessage( $where ) {
        $del = M('biz_student_learning_path')->where( $where )->delete();
        return $del;
    }

    //
}