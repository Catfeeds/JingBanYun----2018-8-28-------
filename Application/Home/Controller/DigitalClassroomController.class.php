<?php
namespace Home\Controller;

use Think\Controller;

class DigitalClassroomController extends PublicController
{
    public function index()
    {
        header("Content-type: text/html; charset=utf-8");
        $platform = $_GET['platform'];
        session("platform", $platform);
        if (empty($platform)) //PC
        {
            if (!session('?teacher') && !session('?student'))
                redirect(U('Index/index'));
        }
        $ClassroomModel = M('biz_classroom_information');

        //是否为教师角色
        $isTeacher = $_GET['isTeacher'];
        $this->assign('isTeacher', $isTeacher);

        //查找备课课件
        $classroomId = getParameter('classroomId','int');
        $this->assign('classroomId', $classroomId);



        //获取CLASSID

        if (!empty($classroomId)) {
            $result = M('biz_classroom_information')
                ->where("id=$classroomId")
                ->find();

            if (empty($result)) {
                echo("您输入的课堂不存在!");
                return;
            }

        }

        $jinyongmap['id'] = $result['class_id'];
        $is_jinyong = M('biz_class')->where( $jinyongmap )->find();

        if ($is_jinyong['is_delete'] !=0){
            echo "该班级已经被删除";
            exit;
        }

        if ($is_jinyong['flag'] == 0 ) {
            echo("该班级已经被禁用不能加入!");
            exit;
        } else {

            if ($is_jinyong['class_status'] == 1) { //校建班
                $shc_map['id'] = $is_jinyong['school_id'];
                $shc_status = M('dict_schoollist')->where( $shc_map )->find();
                if ( $shc_status['flag'] == 0) {
                    echo("该班级已经被禁用不能加入!");
                    exit;
                }

            } else{//自建班
                /*$auth_info_map['id'] = $is_jinyong['class_teacher_id'];
                $auth_info = M('auth_teacher')->where( $auth_info_map )->find();

                $shc_map['id'] = $auth_info['school_id'];
                $shc_status = M('dict_schoollist')->where( $shc_map )->find();
                if ( $shc_status['flag'] == 0) {
                    echo("该班级已经被禁用不能加入!");
                    exit;
                }*/

            }

        }

        if ($isTeacher == 'true') {
            $chat_haszandata = array(
                'roomid' => $classroomId,
            );
            M('chat_haszan')->where( $chat_haszandata )->delete();

            $findclassstumap['class_id'] = $result['class_id'];
            $findclassstumap['teacher_id'] = $result['teacher_id'];

            $classstuinfo = M('biz_class_teacher')->where( $findclassstumap )->find();

            if (empty($classstuinfo)) {
                echo("您已被移除班级");
                exit;
            }

        } else {

            $findclassstumap['class_id'] = $result['class_id'];
            $findclassstumap['student_id'] = $_GET['student_id'];

            $classstuinfo = M('biz_class_student')->where( $findclassstumap )->find();

            if (empty($classstuinfo)) {
                echo("没有加入该班级!");
                exit;
            }
        }


        $classId = $result['class_id'];
        $this->assign('classId', $classId);
        //获取学生总数
        $Model = M('biz_class_student');
        $students = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->join('biz_class on biz_class.id=biz_class_student.class_id')
            ->field('auth_student.sex,auth_student.id,auth_student.id_card,auth_student.student_name,auth_student.student_id,auth_student.user_name,auth_student.avatar,biz_class_student.class_id')
            ->where("biz_class_student.class_id=$classId and biz_class_student.status=2")
            ->select();

        foreach ($students as $sk=>&$sv) {
            if (empty($sv['avatar'])) {
                $sv['avatar'] = 'default.jpg';
            }
        }
        //print_r($students);die();
        $this->assign('students', $students);
        $this->assign('studentCount', count($students));

        $class_id = null;



        if ($isTeacher != 'true') { //验证学生的正确性
            $telephone = $_GET['telephone'];
            $studentId = $_GET['student_id'];
            $TeacherModel = M('auth_teacher');
            $StudentModel = M('auth_student');
            $teacher = $TeacherModel->where("telephone='$telephone'")->find();
            if (empty($teacher)) {
                echo("您输入的教师手机号或课堂编号有误，经查不存在此课堂");
                return;
            }
            $teacherId = $teacher['id'];//根据手机号查教师编号

            $student = $StudentModel->where("id=$studentId")->find();
            $this->assign('student', $student);

            $classroom = $ClassroomModel->where("id=$classroomId and teacher_id=$teacherId")->find();
            if (empty($classroom)) {
                echo("您输入的教师手机号或课堂编号有误，经查不存在此课堂");
                return;
            }
            //验证是否为该老师的学生

            $class_id = $classroom['class_id'];
            $this->assign('studentId', $studentId);
            //judge valid
            $classModel = M('biz_class_student');
            $classResult = $classModel->where("class_id={$classroom['class_id']} and student_id=$studentId")->find();

            if (empty($classResult)) {
                echo("您不在该教师的课堂中!");
                return;
            }

           /* //获取当前课堂同组的所有成员
            $allgroupstumap['student_id'] = $studentId;
            $allgroupstumap['room_id'] = $classroomId;
            $allgroupstuinfo = M('group_student')
                                ->join('teacher_room_group on teacher_room_group.id=group_student.group_id')
                                ->where( $allgroupstumap )
                                ->find();

            if (!empty($allgroupstuinfo)) {
                $allgroupmap['group_student.group_id'] = $allgroupstuinfo['group_id'];
                $allgroupmap['group_student.student_id'] = array('neq',$allgroupstuinfo['student_id']);
                $allgroup = M('group_student')
                            ->join('auth_student on auth_student.id=group_student.student_id')
                            ->field('auth_student.id as sid,auth_student.student_name,auth_student.avatar,group_student.*')
                            ->where( $allgroupmap )
                            ->select();
            }

            //print_r($allgroup);die();

            $this->assign('allgroup',$allgroup);*/

        }

        //classroom info
        $classroom = $ClassroomModel->where("id=$classroomId")->find();
        $this->assign('classroom', $classroom);

        $group_map = array(
            'room_id' => $classroomId,
            'teacher_id' => $classroom['teacher_id'],
        );
        $row_group = M('teacher_room_group')->where( $group_map )->select();

        foreach ($row_group as $gk=>$gv) {
            $gropustumap = array(
                'group_id' => $gv['id'],
            );

            $result_stu = M('group_student')
                ->join("auth_student on auth_student.id=group_student.student_id")
                ->field('group_student.id as gid,auth_student.id as sid,auth_student.student_name')
                ->where($gropustumap)
                ->select();

            foreach ($result_stu as $sk_g=>$sv_g) {
                $is_show_group[] = $sv_g['sid'];
            }
            $row_group[$gk]['child'] = $result_stu;
        }

        $this->assign('is_show_group',$is_show_group);
        $this->assign('row_group',$row_group);



        //备课课件信息
        $LessonPlanningModel = M('biz_classroom_lesson_planning');
        $lessonPlannings = $LessonPlanningModel
            ->join("biz_lesson_planning on biz_lesson_planning.id=biz_classroom_lesson_planning.lesson_planning_id")
            ->field("biz_lesson_planning.*")
            ->where("classroom_id=$classroomId")->select();

        $LessonPlanningContactModel = M('biz_lesson_planning_contact');
        foreach ($lessonPlannings as $key => $val) {
            //if ($val['type'] == 'VIDEO' || $val['type'] == 'AUDIO') {
            //$config = C('BLWS_CONFIG');
            //$blwsQueryResult = json_decode(file_get_contents("http://v.polyv.net/uc/services/rest?method=getById&vid=" . $val['vid'] . "&readtoken=" . $config['READ_TOKEN']), true);
            //$mediaSource = $blwsQueryResult['data'][0]['mp4'];
            //$lessonPlannings[$key]['mediaSource'] = $mediaSource;
            //}
            $r = $LessonPlanningContactModel->where("biz_lesson_planning_id=" . $val['id'])->select();
            $lessonPlannings[$key]['details'] = $r;
        }

        $this->assign('lessonPlannings', $lessonPlannings);
        //取电子课本
        $textbookId = $classroom["textbook_id"];
        $TextbookModel = M('biz_textbook');
        $book = $TextbookModel->where("id=$textbookId")->find();
        $this->assign('book', $book);
        $this->assign('class_id', $class_id);
        $platform = $_GET['platform'];
        if (empty($platform))
            $platform = 'pc';
        $this->assign('platform', $platform);
        $this->assign('REMOTE_ADDR',$_SERVER['HTTP_HOST']);


        //字典数据

        $Model = M('auth_teacher_second');
        $courses = $Model
            ->field('distinct dict_course.*')
            ->join('dict_course on auth_teacher_second.course_id=dict_course.id')
            ->where("auth_teacher_second.teacher_id=".$classroom['teacher_id'])
            ->order('dict_course.sort_order asc')->select();
        $this->assign('dict_courses', $courses);

        $grades = D('Biz_class')->getGradeListByTeacher( $classroom['teacher_id'] );
        $this->assign('dict_grades', $grades);
        $this->display();
    }

    //demo 网页同步
    public function indexsohu()
    {
        $ClassroomModel = M('biz_classroom_information');

        //是否为教师角色
        $isTeacher = 'true';//$_GET['isTeacher'];
        $this->assign('isTeacher', $isTeacher);

        //查找备课课件
        $classroomId = 2253;//$_GET['classroomId'];
        $this->assign('classroomId', $classroomId);


        if ($isTeacher != 'true') { //验证学生的正确性
            $telephone = $_GET['telephone'];

            $TeacherModel = M('auth_teacher');
            $teacher = $TeacherModel->where("telephone='$telephone'")->find();
            if (empty($teacher)) {
                echo("您输入的教师手机号或课堂编号有误，经查不存在此课堂");
                return;
            }
            $teacherId = $teacher['id'];//根据手机号查教师编号

            $classroom = $ClassroomModel->where("id=$classroomId and teacher_id=$teacherId")->find();
            if (empty($classroom)) {
                echo("您输入的教师手机号或课堂编号有误，经查不存在此课堂");
                return;
            }
            //验证是否为该老师的学生


            $studentName = $_GET['userName'];

            //GET student id
            $studentModel = M('auth_student');
            $studentId = $studentModel->where("user_name = '$studentName'")->field("id")->find();
            $this->assign('studentId', $studentId);
            //judge valid

            $classModel = M('biz_class_student');
            $classResult = $classModel->where("class_id={$classroom['class_id']} and student_id={$studentId['id']}")->find();

            if (empty($classResult)) {
                echo("您不在该教师的课堂中!");
                return;
            }


        }
        //classroom info
        $classroom = $ClassroomModel->where("id=$classroomId")->find();
        $this->assign('classroom', $classroom);

        //备课课件信息
        $LessonPlanningModel = M('biz_classroom_lesson_planning');
        $lessonPlannings = $LessonPlanningModel
            ->join("biz_lesson_planning on biz_lesson_planning.id=biz_classroom_lesson_planning.lesson_planning_id")
            ->field("biz_lesson_planning.*")
            ->where("classroom_id=$classroomId")->select();
        $this->assign('lessonPlannings', $lessonPlannings);

        //取电子课本
        $textbookId = $classroom["textbook_id"];
        $TextbookModel = M('biz_textbook');
        $book = $TextbookModel->where("id=$textbookId")->find();
        $this->assign('book', $book);

        $platform = $_GET['platform'];
        if (empty($platform))
            $platform = 'pc';
        $this->assign('platform', $platform);

        $this->display('indexsohu');
    }

    //demo 互动白板
    public function indexwhiteboard()
    {
        $ClassroomModel = M('biz_classroom_information');

        //是否为教师角色
        $isTeacher = 'true';//$_GET['isTeacher'];
        $this->assign('isTeacher', $isTeacher);

        //查找备课课件
        $classroomId = 1153;//$_GET['classroomId'];
        $this->assign('classroomId', $classroomId);


        if ($isTeacher != 'true') { //验证学生的正确性
            $telephone = $_GET['telephone'];

            $TeacherModel = M('auth_teacher');
            $teacher = $TeacherModel->where("telephone='$telephone'")->find();
            if (empty($teacher)) {
                echo("您输入的教师手机号或课堂编号有误，经查不存在此课堂");
                return;
            }
            $teacherId = $teacher['id'];//根据手机号查教师编号

            $classroom = $ClassroomModel->where("id=$classroomId and teacher_id=$teacherId")->find();
            if (empty($classroom)) {
                echo("您输入的教师手机号或课堂编号有误，经查不存在此课堂");
                return;
            }
            //验证是否为该老师的学生


            $studentName = $_GET['userName'];

            //GET student id
            $studentModel = M('auth_student');
            $studentId = $studentModel->where("user_name = '$studentName'")->field("id")->find();
            $this->assign('studentId', 4);
            //judge valid

            $classModel = M('biz_class_student');
            $classResult = $classModel->where("class_id={$classroom['class_id']} and student_id={$studentId['id']}")->find();

            if (empty($classResult)) {
                echo("您不在该教师的课堂中!");
                return;
            }


        }
        //classroom info
        $classroom = $ClassroomModel->where("id=$classroomId")->find();
        $this->assign('classroom', $classroom);

        //备课课件信息
        $LessonPlanningModel = M('biz_classroom_lesson_planning');
        $lessonPlannings = $LessonPlanningModel
            ->join("biz_lesson_planning on biz_lesson_planning.id=biz_classroom_lesson_planning.lesson_planning_id")
            ->field("biz_lesson_planning.*")
            ->where("classroom_id=$classroomId")->select();
        $this->assign('lessonPlannings', $lessonPlannings);

        //取电子课本
        $textbookId = $classroom["textbook_id"];
        $TextbookModel = M('biz_textbook');
        $book = $TextbookModel->where("id=$textbookId")->find();
        $this->assign('book', $book);

        $platform = $_GET['platform'];
        if (empty($platform))
            $platform = 'pc';
        $this->assign('platform', $platform);

        $this->display('indexwhiteboard');
    }


    //布置作业
    public function assignHomework()
    {
        $data['homework_name'] = remove_xss($_POST['homework_name']);
        $data['claim'] = remove_xss($_POST['claim']);
        $data['class_id'] = $_POST['class_id'];
        $data['course_id'] = $_POST['course_id'];
        $data['grade_id'] = $_POST['grade_id'];
        $data['textbook_id'] = $_POST['textbook_id'];
        $exerciseIds = $_POST['exercise_ids'];
        $data['homework_type'] = $_POST['type'];
        $data['classroom_id'] = $_POST['classroom_id'];
        $data['exercise_chapter_id'] = 0;

        $classroom = M('biz_classroom_information')->where("id=" . $data['classroom_id'])->find();

        if (empty($classroom)) {
            $this->ajaxReturn(array());
            exit();
        }

        $data['teacher_id'] = $classroom['teacher_id'];
        //根据teacherid获取teacher
        $TeacherModel = M('auth_teacher');
        $teacher = $TeacherModel->where("id=" . $data['teacher_id'])->find();
        $data['teacher_name'] = $teacher['name'];

        $data['create_at'] = time();
        $data['update_at'] = time();

        $exerciseIdArr = explode('; ', $exerciseIds);

        $data['exercises_number'] = count($exerciseIdArr);

        foreach ($exerciseIdArr as $v) {
            if (!empty($v)) {
                $arr = explode('.', $v);
                $idsnum[] =  $arr[0];
            }
        }
        $idsnum = array_unique($idsnum);
        $idsnum = implode(',',$idsnum);
        $data['exercise_chapter_id'] = $idsnum;
        $HomeworkModel = M('biz_homework');
        $homeworkId = $HomeworkModel->add($data);

        $temp_model = M('biz_exercise_library');
        foreach ($exerciseIdArr as $v) {
            if (!empty($v)) {
                $arr = explode('.', $v);
                $dataList[] = array('homework_id' => $homeworkId, 'exercise_id' => $arr[1], 'chapter_id' => $arr[0]);
            }
        }

        if (!empty($dataList)) {
            $Model = M('biz_homework_exercise');
            $Model->addAll($dataList);
        }

        $this->ajaxReturn('ok');
    }

    //加载课堂作业
    public function loadInClassHomework()
    {
        $classroomId = $_GET['classroom_id'];
        //该课堂的课中作业
        $HomeworkModel = M('biz_homework');

        $name = $_REQUEST['name'];
        $grade = $_REQUEST['grade'];
        $course = $_REQUEST['course'];
        $textbook = $_REQUEST['textbook'];
        $state = $_REQUEST['state'];
        $teacherId = $_REQUEST['teacher_id'];

        $where['teacher_id'] = $teacherId;
        $where['homework_type'] = '课堂作业';
        if (!empty($name)) $where['homework_name'] = array('like', '%' . "$name" . '%');
        if (!empty($grade)) $where['grade_id'] = $grade;
        if (!empty($course)) $where['course_id'] = $course;
        if (!empty($textbook)) $where['textbook_id'] = $textbook;
        if ($state == "1") {
            $where['homework_status'] = $state;
        }
        if ($state == "2") {
            $where['homework_status'] = array('neq', 1);
        }

        $homeworkResultInClass = $HomeworkModel
            ->where($where)
            ->order('biz_homework.create_at desc')
            ->select();     //echo $HomeworkModel->getLastsql();die;

        $bhe = M('biz_homework_exercise');

        foreach ($homeworkResultInClass as $key => $val) {
            $xiti['homework_id'] = $val['id'];
            $info = $bhe->where($xiti)->select();
            $ids = array();
            foreach ($info as $k=>$v) {
                $ids[] = $v['chapter_id'];
            }
            $ids = array_unique($ids);
            
            if (!empty($ids)) {
                $info_chap = array();
                foreach ($ids as $kid=>$vid) {
                    $map_id['id'] = $vid;
                    $info_chap[] = M('biz_exercise_library_chapter')->where($map_id)->find();
                }
            }
            $homeworkResultInClass[$key]['list_chapter'] = $info_chap;
        }
        /*echo "<pre>";
        print_r($homeworkResultInClass);die();*/
        $this->assign('homeworkResultInClass', $homeworkResultInClass);
        $this->display();

    }

    //加载课后作业
    public function loadOutClassHomework()
    {
        $classroomId = $_GET['classroom_id'];
        //该课堂的课中作业
        $HomeworkModel = M('biz_homework');

        $name = $_REQUEST['name'];
        $grade = $_REQUEST['grade'];
        $course = $_REQUEST['course'];
        $textbook = $_REQUEST['textbook'];
        $state = $_REQUEST['state'];
        $teacherId = $_REQUEST['teacher_id'];

        $where['teacher_id'] = $teacherId;
        $where['homework_type'] = '课后作业';
        if (!empty($name)) $where['homework_name'] = array('like', '%' . "$name" . '%');
        if (!empty($grade)) $where['grade_id'] = $grade;
        if (!empty($course)) $where['course_id'] = $course;
        if (!empty($textbook)) $where['textbook_id'] = $textbook;
        if ($state == "1") {
            $where['homework_status'] = $state;
        }
        if ($state == "2") {
            $where['homework_status'] = array('exp', 'is null');
        }

        $homeworkResultInClass = $HomeworkModel
            ->where($where)
            ->order('biz_homework.create_at desc')
            ->select();

        $bhe = M('biz_homework_exercise');

        foreach ($homeworkResultInClass as $key => $val) {
            $xiti['homework_id'] = $val['id'];
            $info = $bhe->where($xiti)->select();
            $ids = array();
            foreach ($info as $k=>$v) {
                $ids[] = $v['chapter_id'];
            }
            $ids = array_unique($ids);

            if (!empty($ids)) {
                $info_chap = array();
                foreach ($ids as $kid=>$vid) {
                    $map_id['id'] = $vid;
                    $info_chap[] = M('biz_exercise_library_chapter')->where($map_id)->find();
                }
            }
            $homeworkResultInClass[$key]['list_chapter'] = $info_chap;
        }

        $this->assign('homeworkResultInClass', $homeworkResultInClass);
        $this->display();
    }

    //异步加载作业详情
    public function ajax_LoadHomeworkDetails()
    {
        $homeworkId = $_GET['homework_id'];
        $HomeworkModel = M('biz_homework');
        $homework = $HomeworkModel->where("id=$homeworkId")->find();

        $Model = M('biz_homework_exercise');
        $exercises = $Model->where("homework_id=$homeworkId")->select();
        $homework['exercises'] = $exercises;

        $this->ajaxReturn($homework);
    }

    //布置作业给学生（作业分发）
    public function ajax_AssignHomeworkToStudents()
    {
        $homeworkId = $_GET['homework_id'];
        $HomeworkModel = M('biz_homework');
        $data['homework_status'] = 1;
        $data['update_at'] = time(); //添加布置作业的时间
        $HomeworkModel->where("id=$homeworkId")->save($data);
        $this->ajaxReturn("ok");
    }

    //加载课堂作业
    public function loadStudentInClassHomework()
    {
        $classroomId = $_GET['classroom_id'];
        $studentId = $_GET['student_id'];

        $where['biz_homework.classroom_id'] = $classroomId;
        $where['biz_homework.homework_status'] = 1;
        $where['biz_homework.homework_type'] = '课堂作业';
        $where['biz_class_student.student_id'] = $studentId;

        //该课堂的课中作业
        $HomeworkModel = M('biz_homework');
        $homeworkResultInClass = $HomeworkModel
            ->join("biz_class_student on biz_class_student.class_id = biz_homework.class_id and biz_class_student.status=2")
            ->join("biz_class on biz_class_student.class_id = biz_class.id")
            ->field('biz_homework.id as bid,biz_homework.*,biz_class_student.*,biz_class.*')
            ->where($where)//2:课中作业
            ->order('biz_homework.update_at desc')
            ->select();


        $ExerciseLibraryModel = M('biz_exercise_library_chapter');
        $DetailsModel = M('biz_homework_student_details');


        foreach ($homeworkResultInClass as $key => $val) {
            $info = $ExerciseLibraryModel->where("textbook_id={$val['textbook_id']} and id={$val['exercise_chapter_id']}")->find();
            $homeworkResultInClass[$key]['chapter'] = $info['chapter'];

            $detailsResult = $DetailsModel->where("student_id=$studentId and homework_id={$val['bid']}")->find();

            if (!empty($detailsResult)) {
                $homeworkResultInClass[$key]['has_completed'] = 'true';
            } else {
                $homeworkResultInClass[$key]['has_completed'] = 'false';
            }


        }

        //print_r($homeworkResultInClass);die();

        $this->assign('homeworkResultInClass', $homeworkResultInClass);
        $this->display();

    }

    //加载课后作业
    public function loadStudentOutClassHomework()
    {
        //$classroomId = $_GET['classroom_id'];
       /* $classId = $_GET['class_id'];
        $studentId = $_GET['student_id'];
        //该课堂的课中作业
        $HomeworkModel = M('biz_homework');
        $homeworkResultInClass = $HomeworkModel
            ->where("class_id=$classId and homework_status=1 and homework_type='课后作业'")//1:课后作业
            ->order('biz_homework.update_at desc')
            ->select();
        //print_r($homeworkResultInClass);die();
        $ExerciseLibraryModel = M('biz_exercise_library_chapter');

        $DetailsModel = M('biz_homework_student_details');

        foreach ($homeworkResultInClass as $key => $val) {
            $info = $ExerciseLibraryModel->where("textbook_id={$val['textbook_id']} and id={$val['exercise_chapter_id']}")->find();
            $homeworkResultInClass[$key]['chapter'] = $info['chapter'];

            $detailsResult = $DetailsModel->where("student_id=$studentId and homework_id={$val['id']}")->find();
            if (!empty($detailsResult)) {
                $homeworkResultInClass[$key]['has_completed'] = 'true';
            } else {
                $homeworkResultInClass[$key]['has_completed'] = 'false';
            }
        }

        $this->assign('homeworkResultInClass', $homeworkResultInClass);*/
        $classroomId = $_GET['classroom_id'];
        $studentId = $_GET['student_id'];

        $where['biz_homework.classroom_id'] = $classroomId;
        $where['biz_homework.homework_status'] = 1;
        $where['biz_homework.homework_type'] = '课后作业';
        $where['biz_class_student.student_id'] = $studentId;

        //该课堂的课中作业
        $HomeworkModel = M('biz_homework');
        $homeworkResultInClass = $HomeworkModel
            ->join("biz_class_student on biz_class_student.class_id = biz_homework.class_id and biz_class_student.status=2")
            ->join("biz_class on biz_class_student.class_id = biz_class.id")
            ->field('biz_homework.id as bid,biz_homework.*,biz_class_student.*,biz_class.*')
            ->where($where)//2:课中作业
            ->order('biz_homework.update_at desc')
            ->select();

        $ExerciseLibraryModel = M('biz_exercise_library_chapter');
        $DetailsModel = M('biz_homework_student_details');

        foreach ($homeworkResultInClass as $key => $val) {
            $info = $ExerciseLibraryModel->where("textbook_id={$val['textbook_id']} and id={$val['exercise_chapter_id']}")->find();
            $homeworkResultInClass[$key]['chapter'] = $info['chapter'];

            $detailsResult = $DetailsModel->where("student_id=$studentId and homework_id={$val['bid']}")->find();
            if (!empty($detailsResult)) {
                $homeworkResultInClass[$key]['has_completed'] = 'true';
            } else {
                $homeworkResultInClass[$key]['has_completed'] = 'false';
            }


        }

        $this->assign('homeworkResultInClass', $homeworkResultInClass);

        $this->display();
    }

    //加载PPT课件
    public function lesson_planning_show_ppt()
    {
        $id = $_GET['id'];
        $LessonPlanningModel = M('biz_lesson_planning_contact');
        $lessonPlanning = $LessonPlanningModel
            ->join("biz_lesson_planning on biz_lesson_planning.id = biz_lesson_planning_contact.biz_lesson_planning_id")
            ->field("biz_lesson_planning.oss_path,biz_lesson_planning_contact.*")
            ->where("biz_lesson_planning_contact.id=$id")->find();
        $this->assign('lessonPlanning', $lessonPlanning);
        $this->display();
    }

    //加载pic课件
    public function lesson_planning_show_pic()
    {
        $id = $_GET['id'];
        $LessonPlanningModel = M('biz_lesson_planning_contact');
        $lessonPlanning = $LessonPlanningModel
            ->join("biz_lesson_planning on biz_lesson_planning.id = biz_lesson_planning_contact.biz_lesson_planning_id")
            ->field("biz_lesson_planning.oss_path,biz_lesson_planning_contact.*")
            ->where("biz_lesson_planning_contact.id=$id")->find();
        $this->assign('lessonPlanning', $lessonPlanning);
        $this->display();
    }

    //加载word课件
    public function lesson_planning_show_word()
    {
        $id = $_GET['id'];
        $LessonPlanningModel = M('biz_lesson_planning_contact');
        $lessonPlanning = $LessonPlanningModel
            ->join("biz_lesson_planning on biz_lesson_planning.id = biz_lesson_planning_contact.biz_lesson_planning_id")
            ->field("biz_lesson_planning.oss_path,biz_lesson_planning_contact.*")
            ->where("biz_lesson_planning_contact.id=$id")->find();
        $this->assign('lessonPlanning', $lessonPlanning);
        $this->assign('platform', session('platform'));
        $this->display();
    }

    //加载pdf课件
    public function lesson_planning_show_pdf()
    {
        $id = $_GET['id'];
        $LessonPlanningModel = M('biz_lesson_planning_contact');
        $lessonPlanning = $LessonPlanningModel
            ->join("biz_lesson_planning on biz_lesson_planning.id = biz_lesson_planning_contact.biz_lesson_planning_id")
            ->field("biz_lesson_planning.oss_path,biz_lesson_planning_contact.*")
            ->where("biz_lesson_planning_contact.id=$id")->find();
        $this->assign('lessonPlanning', $lessonPlanning);
        $this->assign('platform', session('platform'));
        $this->display();
    }

    //加载VIDEO课件
    public function lesson_planning_show_video()
    {
        $id = $_GET['id'];
        $LessonPlanningModel = M('biz_lesson_planning_contact');
        $lessonPlanning = $LessonPlanningModel
            ->join("biz_lesson_planning on biz_lesson_planning.id = biz_lesson_planning_contact.biz_lesson_planning_id")
            ->field("biz_lesson_planning.oss_path,biz_lesson_planning_contact.*")
            ->where("biz_lesson_planning_contact.id=$id")->find();
        $this->assign('lessonPlanning', $lessonPlanning);
        $this->display();
    }
    //新版备课资料获取
    public function lesson_planning_V3()
    {
        $lessonPlanningId = $_GET['id'];    
        $lessonPlanningContent = json_decode(A('Home/LessonPlanning')->getLessonPlanning($lessonPlanningId,true),true);
        if(0 == $lessonPlanningContent['code'])
        {
            $this->assign('lessonPlanningDetailsInfo',$lessonPlanningContent);
            $this->assign('lessonPlanningDetails',$lessonPlanningContent['msg']);
        }
        else
        {
           $this->assign('lessonPlanningDetails','打开备课资料失败');
        }
        $this->display();
    }
    //做作业
    public function doHomework()
    {
        $homeworkId = $_GET['homeworkId'];
        $studentId = $_GET['studentId'];
        $this->assign('homeworkId', $homeworkId);
        $this->assign('studentId', $studentId);


        //判断是否存在作业了
        $Model = M('biz_homework_student_details');
        $result = $Model
            ->where("student_id=$studentId and homework_id=$homeworkId")
            ->find();
        if (!empty($result)) {
            $this->redirect("DigitalClassroom/studentHomeworkCompleteDetails?from=student&err=completed&id=" . $result['id']);
        }

        $this->assign('homework', $result);

        //获取此次作业对象信息
        $Model = M('biz_homework');
        $currentHomework = $Model->where("id=$homeworkId")->find();
        $this->assign('currentHomework', $currentHomework);

        /*$currentHomework['exercise_chapter_id'] = 653;

        $ExerciseModel = M('biz_exercise_library_chapter');
        $chapter = $ExerciseModel->where("id=" . $currentHomework['exercise_chapter_id'])->find();
        $this->assign('chapter', $chapter);
        */

        $this->display();
    }

    //学生提交笔记
    public function addNote(){
        $sid = $_POST['s_id'];
        if (empty($sid)) {
            $this->ajaxReturn('error');
        }

        $data = array(
            'grade_id'=>$_POST['g_id'],
            'user_id'=>$_POST['s_id'],
            'course_id'=>$_POST['c_id'],
            'chapter_id'=>$_POST['t_id'],
            'content'=>$_POST['con'],
            'create_time' => time()
        );
        $addid = M('my_note')->add($data);
        if ( $addid ) {
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('error');
        }
    }

    //学生提交作业
    public function submitHomework()
    {
        $data['homework_id'] = $_POST['homework_id'];       //var_dump($_POST);die;
        $data['answers'] = $_POST['answers'];
        $data['duration'] = $_POST['duration'];
        $data['teacher_id'] = 0;
        $data['points'] = 0;
        $data['create_at'] = time();
        $data['student_id'] = $_POST['studentId'];

        $ResourceModel = M('biz_homework_student_details');

        $result = $ResourceModel
            ->where("student_id=" . $data['student_id'] . " and homework_id=" . $data['homework_id'])
            ->find();
        if (!empty($result)) {
            $this->redirect("DigitalClassroom/studentHomeworkCompleteDetails?from=student&err=completed&id=" . $result['id']);
        }


        $ResourceModel->add($data);
        //作业数量+1
        $HomeworkModel = M('biz_homework');
        $HomeworkModel->where("id=" . $_POST['homework_id'])->setInc("completed_number", 1);

        $this->display('afterDoHomework');
        //$this->redirect("DigitalClassroom/afterDoHomework");
    }

    function afterDoHomework()
    {
        $this->display();
    }

    //作业完成情况
    public function homeworkCompleteDetails()
    {
        $homeworkId = $_GET['homeworkId'];
        $classroomId = $_GET['classroomId'];
        $groupstumap['room_id'] = $classroomId;
        $groupstu = M('teacher_room_group')
                    ->where( $groupstumap )->select();


        $this->assign('homeworkId', $homeworkId);

        $Model = M('biz_homework');
        $homework = $Model->where("id=$homeworkId")->find();
        $classId = $homework['class_id'];
        $Model = M('biz_class');
        $class = $Model->where("id=$classId")->find();
        $classStudentCount = $class['student_count'];
        $this->assign('classStudentCount', $classStudentCount);

        $name = $_POST['name'];
        $sortColumn = $_POST['sort_column'];
        $state = $_POST['state'];
        $check['biz_homework_student_details.homework_id'] = $homeworkId;
        if (!empty($name)) $check['auth_student.student_name'] = array('like', '%' . $name . '%');
        if (empty($sortColumn)) {
            $sortColumn = 'create_at';
        } else {
            $sortColumn_value = $sortColumn;
            if ($sortColumn == 1) {
                $sortColumn = 'points asc';
            } else {
                $sortColumn = 'points desc';
            }
        }


        $this->assign('name', $name);
        $this->assign('sort_column', $sortColumn_value);
        $this->assign('state', $state);

        $Model = M('biz_homework_student_details');
        $result = $Model
            ->join('inner join auth_student on biz_homework_student_details.student_id=auth_student.id')
            ->field('biz_homework_student_details.*,auth_student.student_name')
            ->where($check)
            ->order("biz_homework_student_details.$sortColumn ")
            ->select();

        foreach ( $groupstu as $gsk=>$gsv) {
            $groupstuallmap['group_id'] = $gsv['id'];

            $gsturesult = M('group_student')
                    ->join('auth_student on auth_student.id=group_student.student_id')
                    ->where($groupstuallmap)
                    ->field('auth_student.id as sid,auth_student.student_name')
                    ->select();

            $groupstu[$gsk]['childstu'] = $gsturesult;
        }

        if (!empty($groupstu)) {

            foreach ($groupstu as $ggk=>$ggv) {
                $iii = 0;
                $siii = 0;
                foreach ($ggv['childstu'] as $ggck=>$ggcv) {

                    $ggmap['student_id'] = $ggcv['sid'];
                    $ggmap['homework_id'] = $homeworkId;
                    $pointsstu = M('biz_homework_student_details')->where($ggmap)->find();

                    if (!empty($pointsstu['points']) && $pointsstu['points']!=0) {
                        $groupstu[$ggk]['childstu'][$ggck]['points'] = $pointsstu['points'];
                    } else {
                        $groupstu[$ggk]['childstu'][$ggck]['points'] = 0;
                    }
                    $iii+=$groupstu[$ggk]['childstu'][$ggck]['points'];
                    $siii++;
                }

                $countstuall = $iii/$siii;
                $groupstu[$ggk]['countpointstu'] = $countstuall;

            }
        }

        //print_r($groupstu);die();
        $this->assign('groupstu',$groupstu);

        //判断是否存在作业了
        $StudentModel = M('biz_class_student');
        //get class id
        $checkStudents['biz_class_student.status'] = 2;
        $checkStudents['biz_class_student.class_id'] = $homework['class_id'];
        if (!empty($name)) $checkStudents['auth_student.student_name'] = array('like', $name . '%');
        $students = $StudentModel
            ->join("auth_student on auth_student.id=biz_class_student.student_id")
            ->field("auth_student.id as student_id,auth_student.student_name,0 as create_at,0 as duration,0 as points,0 as status,0 as id")
            ->where($checkStudents)
            ->select();
        $i = 0;
        $outlist = array();

        foreach ($students as $student) {
            $isExisted = false;
            foreach ($result as $r) {
                if ($r['student_id'] == $student['student_id']) {//根据学生姓名查询,如果学生姓名为空，则都显示
                    $isExisted = true;
                    break;
                }
            }
            if (!$isExisted) { //如果不存在
                $outlist[$i] = $student;
                $i = $i + 1;
            }
        }
        if (empty($state)) {
            $list = array_merge($result, $outlist);
        } else {
            if ($state == 1) {
                $list = $outlist;
            } else {
                $list = $result;
            }
        }

        foreach ($list as $addk=>$addv) {
            $addmap['group_student.student_id'] = $addv['student_id'];
            $addmap['teacher_room_group.room_id'] = $classroomId;
            $sgresult = M('group_student')
                ->join("teacher_room_group on teacher_room_group.id=group_student.group_id")
                ->where($addmap)
                ->find();
            if (!empty($sgresult['group_name']))
            $list[$addk]['group_info'] = $sgresult['group_name'];
        }


        $this->assign('list', $list);

        $Model = M('biz_homework_student_details');
        $avgDuration = $Model
            ->where("biz_homework_student_details.homework_id=$homeworkId")
            ->avg('duration');

        $maxDuration = $Model
            ->where("biz_homework_student_details.homework_id=$homeworkId")
            ->max('duration');


        $minDuration = $Model
            ->where("biz_homework_student_details.homework_id=$homeworkId")
            ->min('duration');

        $this->assign('avgDuration', $avgDuration);
        $this->assign('maxDuration', $maxDuration);
        $this->assign('minDuration', $minDuration);

        $this->display();
    }

    //老师加载学生作业的详细信息
    public function studentHomeworkCompleteDetails()
    {
        $from = $_GET['from'];
        $this->assign('from', $from);

        $err = $_GET['err'];
        $this->assign('err', $err);


        $id = intval($_GET['id']);
        $this->assign('id', $id);
        //判断是否存在作业了
        $Model = M('biz_homework_student_details');
        $result = $Model->where("id=$id")->find();
        $this->assign('homework', $result);
        //试题信息
        $homeworkId = $result['homework_id'];
        $Model = M('biz_homework');
        $exerciseChapter = $Model->where("id=$homeworkId")->find();
        $this->assign('exerciseChapter', $exerciseChapter);

        //获取题目数量
        //$exercise_chapter_id = $exerciseChapter['exercise_chapter_id'];
        $Model = M('biz_homework_exercise');
        $exerciseCount = $Model->where("homework_id=$homeworkId")->count('id');
        $this->assign('exerciseCount', $exerciseCount);

        //获取学生得分细节
        $studentId = $result['student_id'];
        $Model = M('biz_homework_score_details');
        $scoreDetails = $Model->where("homework_id=$homeworkId and student_id=$studentId")->find();     
        $this->assign('scoreDetails', $scoreDetails);

        $this->display();
    }

    //通过某个作业ID,拿到其下所有习题
    public function getHomeworkInfo()
    {
        $id = intval(I('id'));
        $Model = M('biz_exercise_library_chapter');
        $result = $Model->where("u.id=" . $id)
            ->join("biz_homework_exercise t on t.chapter_id=biz_exercise_library_chapter.id")
            ->join("biz_homework u on u.id=t.homework_id")
            ->join("biz_exercise_library v on t.exercise_id=v.id")
            ->join("biz_exercise_template tt on v.type=tt.id")
            ->field("chapter,festival,v.id questions_primary_id,u.homework_name,tt.*,v.*,mp3_vid vid")
            ->order('v.id asc')
            ->select();
        $this->ajaxReturn($result);

    }


    //打分详情进入作业系统
    function addScoreDetailsIntoDb()
    {
        if ($_POST) {
            $Model = M('biz_homework_score_details');
            $data['student_id'] = $_POST['student_id'];
            $data['homework_id'] = $_POST['homework_id'];
            $details = $_POST['details'];
            $questionArr = explode("#", $details);
            foreach ($questionArr as $value) {
                $arr = explode("|", $value);
                $questionId = $arr[0];
                $score = $arr[1];
                $full_score = $arr[2];
                $flag = $arr[3];
                $questionOrgId = $arr[4];
                $data['question_id'] = $questionId;
                $data['score'] = $score;
                $data['full_score'] = $full_score;
                $data['flag'] = $flag;
                $data['question_org_id'] = $questionOrgId;

                //$check['question_id'] = $questionId;
                $check['question_org_id'] = $questionOrgId;
                $check['student_id'] = $_POST['student_id'];
                $check['homework_id'] = $_POST['homework_id'];
                $Model->where($check)->delete();
                $Model->add($data);
            }

            $this->ajaxReturn('success');
        }
    }

    //作业打分
    function doGrade()
    {
        if ($_POST) {
            $check['id'] = $_POST['details_id'];
            $Model = M('biz_homework_student_details');
            $data['score_details'] = $_POST['score_details'];
            $data['score_at'] = time();
            $data['status'] = 2;
            $data['points'] = $_POST['total_score'];
            $Model->where($check)->save($data);
            $this->redirect("DigitalClassroom/studentHomeworkCompleteDetails?id=" . $check['id']);
        }
    }

    //老师添加分组
    public function addGroup() {
        $groupname = I('groupname');
        $teacherId = I('teacherId');
        $classroomId = I('classroomId');
        $data = array(
            'group_name' => $groupname,
            'teacher_id' => $teacherId,
            'create_time' => time(),
            'room_id' => $classroomId
        );

        $gid = M('teacher_room_group')->add($data);
        if ( $gid ) {
            $code['info'] = 'success';
            $code['id'] = $gid;
            $this->ajaxReturn( $code );
        } else {
            $code['info'] = 'error';
            $this->ajaxReturn( $code );
        }
    }

    //删除分组
    public function delGroup() {
        $id  = $_GET['id'];
        $Model = M('teacher_room_group');
        $ids  = $Model->where("id=$id")->delete();

        $stuid  = M('group_student')->where("group_id=$id")->delete();

        if ( $ids && $stuid ) {
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('error');
        }
    }
    //为分组添加学生
    public function addGroupStu() {
        $gid = I('gid');
        $stuid = I('stuid');
        $stuid = explode(',',$stuid);
        if (!empty($stuid)) {
            foreach ($stuid as $k=>$v) {
                $data = array(
                    'group_id' => $gid,
                    'student_id' => $v,
                );
                $stuinfo = M('group_student')->where($data)->find();
                if (empty($stuinfo)) {
                    M('group_student')->add($data);
                }
            }
        }
    }

    //删除分组中的学生
    public function delGroupStu() {
        $gid = I('gid');
        $stuid = I('stuid');
        $stuid = explode(',',$stuid);
        $map['student_id'] = array('in',$stuid);
        $map['group_id'] = $gid;

        $res  = M('group_student')->where($map)->delete();

        if ($res) {
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('error');
        }

    }

    //同组学生进行推送
    public function addGroupComment() {
        $id = I('id'); //学生id
        $studentName = I('studentName');//评价人的名称
        $classroomId = I('classroomId');//房间号
        $content = I('content'); //评价一个内容

        $parameters = array( 'msg' => array($studentName,$classroomId,$content) ,
            'url' => array( 'type' => 0)
        );
        A('Home/Message')->addPushUserMessage('GROUP_STUDENT_COMMENT',3,$id,$parameters);
        $this->ajaxReturn('success');
    }

    //当前的学生用户加载同组的学生
    public function getGroupStudent() {
        $studentId = I('studentId');
        $classroomId = I('classroomId');
        //获取当前课堂同组的所有成员
        $allgroupstumap['student_id'] = $studentId;
        $allgroupstumap['room_id'] = $classroomId;
        $allgroupstuinfo = M('group_student')
            ->join('teacher_room_group on teacher_room_group.id=group_student.group_id')
            ->where( $allgroupstumap )
            ->find();

        if (!empty($allgroupstuinfo)) {
            $allgroupmap['group_student.group_id'] = $allgroupstuinfo['group_id'];
            $allgroupmap['group_student.student_id'] = array('neq',$allgroupstuinfo['student_id']);
            $allgroup = M('group_student')
                ->join('auth_student on auth_student.id=group_student.student_id')
                ->field('auth_student.id as sid,auth_student.student_name,auth_student.avatar,group_student.*')
                ->where( $allgroupmap )
                ->select();
        }

        $instu = array();
        $data = array(
            'sendstuid' => $studentId,
            'roomid' => $classroomId,
        );
        $rows = M('chat_haszan')->where( $data )->select();

        if (!empty($rows)) {
            foreach ($rows as $k=> $v) {
                $instu[] = $v['tostuid'];
            }
        }

        $this->assign('allgroup_list',$allgroup);
        $this->assign('instu',$instu);

        $html = $this->fetch('gropuhtml');

        $countmap = array(
            'tostuid' => $studentId,
            'roomid' => $classroomId,
        );
        $stucount = M('chat_haszan')->where( $countmap )->count();
        $data['stucount'] = $stucount;
        $data['info'] = $html;
        $this->ajaxReturn( $data );
    }

    //执行点赞
    public function haszan() {
        $map = array(
            'sendstuid' => I('sendstuid'),
            'tostuid' => I('tostuid'),
            'roomid' => I('roomid'),
        );

        $id  = M('chat_haszan')->add( $map );

        if ( $id ) {
            $data['code'] = 200;
            $countmap = array(
                'tostuid' => I('tostuid'),
                'roomid' => I('roomid'),
            );
            $stucount = M('chat_haszan')->where( $countmap )->count();
            $data['stucount'] = $stucount;
            $this->ajaxReturn( $data );
        }
    }
    //取消点赞
    public function quxiaohaszan() {

        $map = array(
            'sendstuid' => I('sendstuid'),
            'tostuid' => I('tostuid'),
            'roomid' => I('roomid'),
        );

        $id  = M('chat_haszan')->where( $map )->delete();

        if ( $id ) {
            $data['code'] = 200;
            $countmap = array(
                'tostuid' => I('tostuid'),
                'roomid' => I('roomid'),
            );
            $stucount = M('chat_haszan')->where( $countmap )->count();
            $data['stucount'] = $stucount;

            $this->ajaxReturn( $data );
        }
    }

    //删除房间所有点赞效果
    public function delHaszan() {
        $data = array(
            'roomid' => I('classroomId'),
        );
        M('chat_haszan')->where( $data )->delete();
    }

}