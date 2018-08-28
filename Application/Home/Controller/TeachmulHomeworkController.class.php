<?php
namespace Home\Controller;

use Think\Controller;


class TeachmulHomeworkController extends PublicController
{
    public $c_a = '';
    function __construct(){
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));
        $this->c_a = CONTROLLER_NAME."_".ACTION_NAME;
        set_time_limit(0);
    }

    //////多媒体作业作业列表
    public function mulHomework() {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '作业系统');
        $this->assign('navicon', 'zuoyexitong');

        $mca = I('mca');
        if ( $mca == 'action' ) {
            $this->assign('kw',1);
        }
        $courses = D('Exercises_Course')->getCourseList();
        foreach ($courses as $k=>$v){
            if ($v['name']!='英语') {
                unset($courses[$k]);
            }
        }
        $data['classId'] = I('classId');
        $data['courseId'] = I('courseId');
        $data['keyword'] = I('keyword');
        $data['type'] = I('type');
        $data['homeworkType'] = I('homeworkType');
        $data['release_time'] = I('release_time');

        if(empty(session('teacher.id'))) {
            redirect(U('Teach/index1?auth_error=1'));
        }

        $condition['classIdBySerach'] = !empty($data['classId'])?$data['classId']:null;
        $condition['courseId'] = !empty($data['courseId'])?$data['courseId']:null;
        $condition['homeworkType'] = !empty($data['homeworkType'])?$data['homeworkType']:null;
        $condition['keyword'] =!empty($data['keyword'])?$data['keyword']:null;
        $condition['type'] = !empty($data['type'])?$data['type']:null;
        $condition['release_time'] = !empty($data['release_time'])?$data['release_time']:null;

        $condition['role'] = ROLE_TEACHER;
        $condition['userId'] = session('teacher.id');
        $p = I('p');

        if (empty($p)) {
            $p = 1;
        }
        $pageIndex = $p;
        $pageSize= 20;

        $result = D('Exercises_homework_class_relation')->getHomeworkListGroup($condition,$pageIndex,$pageSize,'id');

        $count = D('Exercises_homework_class_relation')->getHomeworkListCount($condition,'id');

        foreach ($result as $k=>$v) {
            $release_time = strtotime($v['release_time']);
            if ( $release_time < 0 ) {
                $result[$k]['is_release'] = 1; //显示
            } else {
                $result[$k]['is_release'] = 2; //不显示
            }

        }

        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($condition as $key => $val) {
            $Page->parameter[$key] = $val;
        }
        $show = $Page->show();
        $this->page = $show;
        $this->list = $result;
        $this->assign('courseList', $courses);//所有学科
        $classList = D('Exercises_homework_class_relation')->getHomeworkCountGroupByClassId($condition);

        $this->where = $data;
        $this->class_list = $classList;
        $this->display();
    }

    //多媒体复制作业
    public function mulAssignCopyHomework() {
        if (IS_POST) {
            $homeworkId = I('homeworkId');
            $homewordinfo =D('Exercises_homework_relation')->getInfo($homeworkId);

            $mapwhere['work_id'] = $homeworkId;
            $countEx = M('exercises_homwork_relation')->where($mapwhere)->select();
            //主库
            $form_selected_exercise = I('form_selected_exercise');
            $exercises_list = explode(",",$form_selected_exercise);
            if (empty($exercises_list)) {
                $res['code'] = 400;
                $res['msg'] = "请选择习题";
                $this->ajaxReturn($res);
            }
            $homework_name = I('homework_name');
            $jobsments = I('jobsments');
            $type = I('type');
            $class_id = I('class_id');
            $mastergrade_class = explode("|",$class_id);
            $courseId= $homewordinfo['course_id'];
            $course_name = $homewordinfo['course_name'];
            $arrangeTime= I('arrangeTime');
            $endTime = I('endTime');
            $userIdList = D('Biz_class_student')->getStudentIdParentIdByClassId($mastergrade_class[0]);
            $strarrangeTime = strtotime($arrangeTime);
            $strendTime = strtotime($endTime);
            if (!empty($arrangeTime)) {
                if ($strarrangeTime < time()){
                    $res['code'] = 400;
                    $res['msg'] = "布置时间不能小于当前时间";
                    $this->ajaxReturn($res);
                }
            } else {
                $arrangeTime= date('Y-m-d H:i:s',time());
                $startime= strtotime($arrangeTime);
            }

            if ($startime >= $strendTime){
                $res['code'] = 400;
                $res['msg'] = "布置时间不能大于或者等于截止时间";
                $this->ajaxReturn($res);
            }

            $masterdata['name'] = $homework_name;
            $masterdata['jobsments'] = $jobsments;
            $masterdata['type'] = $type;
            $masterdata['jobsments'] = $jobsments;
            $masterdata['exercises_num'] = count($countEx);//习题数量
            $masterdata['create_user_id'] = session('teacher.id');
            $masterdata['create_user_name'] = session('teacher.name');
            $masterdata['total_score'] = count($countEx)*10;
            $masterdata['course_id'] = $courseId;
            $masterdata['course_name'] = $course_name;
            $masterdata['is_delete'] = 2;
            $masterdata['release_time'] = $arrangeTime;
            $masterdata['deadline'] = $endTime;

            M('exercises_homwork_basics')->startTrans();

            $mid  = D('Exercises_homework_basics')->addOneHomework($masterdata);

            $slavdata['work_id'] = $mid;
            $slavdata['grade_id'] = $mastergrade_class[1];
            $slavdata['class_id'] = $mastergrade_class[0];

            $sid = D('Exercises_homework_basics')->addEhcr($slavdata);


            //从库

            $grade = $homewordinfo['grade'];
            $volume = $homewordinfo['section'];
            $chapter = $homewordinfo['chapter'];
            $section = $homewordinfo['festival'];

            $exercises_data['subject'] = $courseId;
            $exercises_data['grade'] = $grade;
            $exercises_data['section'] = $volume;
            $exercises_data['chapter'] = $chapter;
            $exercises_data['festival'] = $section;
            $exercises_data['work_id'] = $mid;

            $eid = D('Exercises_homework_basics')->addCopyExercisesList($homeworkId,$mid);

            if ($sid==true && $sid == true && $eid== true) {

                //发送推送
                if (!empty($userIdList)) {
                    $parameters = array( 'msg' => array(session('teacher.name'),$homework_name) ,
                        'url' => array( 'type' => 0)
                    );
                    if (!empty($userIdList['studentId'])) {
                        D('UserInfo')->sendMsg(ROLE_STUDENT,$userIdList['studentId'],json_encode($parameters),"HOMEWORK_PUBLISHED",$arrangeTime);
                    }

                    foreach($userIdList['parentStudentName']  as $key=>$val){
                        $parentId = $val['id'];
                        $studentName = $val['name'];
                        $parameters = array( 'msg' => array(session('teacher.name'),$studentName,$homework_name) ,
                            'url' => array( 'type' => 0)
                        );
                        D('UserInfo')->sendMsg(ROLE_PARENT,$parentId,json_encode($parameters),"HOMEWORK_PUBLISHED_CHILD",$arrangeTime);
                    }


                }

                M('exercises_homwork_basics')->commit();
                $res['code'] = 200;
                $this->ajaxReturn($res);

            } else {
                M('exercises_homwork_basics')->rollback();
                $res['code'] = 400;
                $res['msg'] = "添加失败";
                $this->ajaxReturn($res);
            }

        } else {
            $this->assign('module', '班级行');
            $this->assign('nav', '作业系统');
            $this->assign('subnav', '作业完成情况');
            $this->assign('navicon', 'zuoyexitong');

            if (!session('?teacher')) redirect(U('Index/index'));

            $classList = D('Biz_classList')->getClassListTeacherAll();


            /***************************获取所有学科*************************************/
            $courses = D('Exercises_Course')->getCourseList();
            /***************************获取所有版本*************************************/
            $versionData = D('Exercises_textbook_version')->getAllVersions();
            /***************************获取所有年级*************************************/
            $gradeList = D('Exercises_school_term')->getGradeList();

            $this->assign('courseList', $courses);//所有学科
            $this->assign('versionData', $versionData);//所有版本
            $this->assign('grade', $gradeList);//所有年级
            $this->class_list = $classList;
        }

        $this->display();


    }

    //多媒体作业布置作业
    public function mulAssignHomework() {
        if (IS_POST) {
            //主库
            $form_selected_exercise = I('form_selected_exercise');
            $exercises_list = explode(";",$form_selected_exercise);
            if (empty($exercises_list)) {
                $res['code'] = 400;
                $res['msg'] = "请选择习题";
                $this->ajaxReturn($res);
            }
            $homework_name = I('homework_name');
            $jobsments = I('jobsments');
            $type = I('type');
            $class_id = I('class_id');
            $mastergrade_class = explode("|",$class_id);
            $courseId= I('courseId');
            $course_name = I('course_name');

            $arrangeTime= I('arrangeTime'); //布置时间
            $endTime = I('endTime');//截止时间
            $strarrangeTime = strtotime($arrangeTime);
            $strendTime = strtotime($endTime);

            $userIdList = D('Biz_class_student')->getStudentIdParentIdByClassId($mastergrade_class[0]);

            if (!empty($arrangeTime)) {
                if ($strarrangeTime < time()){
                    $res['code'] = 400;
                    $res['msg'] = "布置时间不能小于当前时间";
                    $this->ajaxReturn($res);
                }
            } else {
                $arrangeTime= date('Y-m-d H:i:s',time());
                $startime= strtotime($arrangeTime);
            }

            if ($startime >= $strendTime){
                $res['code'] = 400;
                $res['msg'] = "布置时间不能大于或者等于截止时间";
                $this->ajaxReturn($res);
            }


            $masterdata['name'] = $homework_name;
            $masterdata['jobsments'] = $jobsments;
            $masterdata['type'] = $type;
            $masterdata['jobsments'] = $jobsments;
            $masterdata['exercises_num'] = count($exercises_list);//习题数量
            $masterdata['create_user_id'] = session('teacher.id');
            $masterdata['create_user_name'] = session('teacher.name');
            $masterdata['total_score'] = count($exercises_list)*10;
            $masterdata['course_id'] = $courseId;
            $masterdata['course_name'] = $course_name;
            $masterdata['is_delete'] = 2;
            $masterdata['release_time'] = $arrangeTime;
            $masterdata['deadline'] = $endTime;

            M('exercises_homwork_basics')->startTrans();

            $mid  = D('Exercises_homework_basics')->addOneHomework($masterdata);

            $slavdata['work_id'] = $mid;
            $slavdata['grade_id'] = $mastergrade_class[1];
            $slavdata['class_id'] = $mastergrade_class[0];

            $sid = D('Exercises_homework_basics')->addEhcr($slavdata);


            //从库

            $grade = I('grade');
            $volume = I('volume');
            $chapter = I('chapter');
            $section = I('section');

            $exercises_data['subject'] = $courseId;
            $exercises_data['grade'] = $grade;
            $exercises_data['section'] = $volume;
            $exercises_data['chapter'] = $chapter;
            $exercises_data['festival'] = $section;
            $exercises_data['work_id'] = $mid;
            $selectedChapterExercise = I('selectedChapterExercise');

            $eid = D('Exercises_homework_basics')->addExercisesList($selectedChapterExercise,$mid);

            if ($sid==true && $sid == true && $eid== true) {
                //发送推送
                if (!empty($userIdList)) {
                    $parameters = array( 'msg' => array(session('teacher.name'),$homework_name) ,
                        'url' => array( 'type' => 0)
                    );

                    if (!empty($userIdList['studentId'])) {
                        D('UserInfo')->sendMsg(ROLE_STUDENT,$userIdList['studentId'],json_encode($parameters),"HOMEWORK_PUBLISHED",$arrangeTime);
                    }

                    foreach($userIdList['parentStudentName']  as $key=>$val){
                        $parentId = $val['id'];
                        $studentName = $val['name'];
                        $parameters = array( 'msg' => array(session('teacher.name'),$studentName,$homework_name) ,
                            'url' => array( 'type' => 0)
                        );
                        D('UserInfo')->sendMsg(ROLE_PARENT,$parentId,json_encode($parameters),"HOMEWORK_PUBLISHED_CHILD",$arrangeTime);
                    }


                }

                M('exercises_homwork_basics')->commit();

                $res['code'] = 200;
                $this->ajaxReturn($res);

            } else {
                M('exercises_homwork_basics')->rollback();
                $res['code'] = 400;
                $res['msg'] = "添加失败";
                $this->ajaxReturn($res);
            }

        } else {
            $this->assign('module', '班级行');
            $this->assign('nav', '作业系统');
            $this->assign('subnav', '作业完成情况');
            $this->assign('navicon', 'zuoyexitong');

            if (!session('?teacher')) redirect(U('Index/index'));

            $classList = D('Biz_classList')->getClassListTeacherAll();


            /***************************获取所有学科*************************************/
            $courses = D('Exercises_Course')->getCourseList();
            foreach ($courses as $k=>$v){
                if ($v['name']!='英语') {
                    unset($courses[$k]);
                }
            }
            /***************************获取所有版本*************************************/
            $versionData = D('Exercises_textbook_version')->getAllVersions();
            /***************************获取所有年级*************************************/
            $gradeList = D('Exercises_school_term')->getGradeList();

            $this->assign('courseList', $courses);//所有学科
            $this->assign('versionData', $versionData);//所有版本
            $this->assign('grade', $gradeList);//所有年级
            $this->class_list = $classList;
        }

        $this->display();


    }

    //多媒体作业完成情况
    public function mulHomeworkCompleteDetails()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '作业完成情况');
        $this->assign('navicon', 'zuoyexitong');
        $homeworkId = I('homeworkId');
        $classId = I('classId');
        $this->homeworkId = $homeworkId;
        $this->classId = $classId;

        $p = I('p');

        if (empty($p)) {
            $p = 1;
        }
        $pageIndex = $p;
        $pageSize= 100;

        $category = I('category');
        if (empty($category)) {
            $category = '';
        } else {
            $this->category = $category;
        }
        $keyword = I('keyword');
        if (empty($keyword)) {
            $keyword = '';
        } else {
            $this->keyword = $keyword;
        }

        $list = D('Exercises_student_homework')->getClassHomeworkStudentSubmitInfo($homeworkId,$classId,$category,$pageIndex,$pageSize,$pc='pc',$keyword);
        $count = D('Exercises_student_homework')->getClassHomeworkStudentSubmitCount($homeworkId,$classId,$category,$pc='',$keyword='');

        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();
        $this->page = $show;

        $this->list = $list;
        $this->display();
    }

    //多媒体作业复制习题页面
    public function mulHomeworkCopy(){
        if (!session('?teacher')) redirect(U('Index/index'));
        $homeworkId = I('homeworkId');
        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '布置作业');
        $this->assign('navicon', 'zuoyexitong');
        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '作业完成情况');
        $this->assign('navicon', 'zuoyexitong');

        if (!session('?teacher')) redirect(U('Index/index'));

        $classList = D('Biz_classList')->getClassListTeacherAll();


        /***************************获取所有学科*************************************/
        $courses = D('Exercises_Course')->getCourseList();
        /***************************获取所有版本*************************************/
        $versionData = D('Exercises_textbook_version')->getAllVersions();
        /***************************获取所有年级*************************************/
        $gradeList = D('Exercises_school_term')->getGradeList();

        $this->assign('courseList', $courses);//所有学科
        $this->assign('versionData', $versionData);//所有版本
        $this->assign('grade', $gradeList);//所有年级
        $this->class_list = $classList;

        $exidList = [];
        $exList = D('Exercises_homework_basics')->getExercises($homeworkId);
        foreach ($exList as $k=>$v) {
            if (!empty($v['id'])) {
                array_push($exidList,$v['id']);
            }
        }
        if (!empty($exidList)) {
            $this->countList = count($exidList);
            $this->exidList = implode(',',$exidList);
        }

        $this->homeworkId = $homeworkId;

        /*$Exinfo = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);
        $this->Exinfo = $Exinfo;*/
        //$classEx = D('Exercises_homework_basics')->getHomeworkClassInfo($homeworkId);

        $this->display();
    }

    //多媒体作业查看作业习题
    public function mulHomeworkExercises(){
        if (!session('?teacher')) redirect(U('Index/index'));
        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '作业完成情况');
        $this->assign('navicon', 'zuoyexitong');
        $homeworkId = I('homeworkId');
        $classId = I('classId');
        $this->classId = $classId;
        $info = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);
        $this->data = $info;

        $list = D('Exercises_homework_basics')->getHomeworkIdgetfk($homeworkId);
        $newlist = [];
        foreach ($list as $k=>$v) {
            array_push($newlist,$v['fname'].$v['sname']);
        }
        $this->newlist = implode(',',$newlist);
        $this->display();
    }

    //多媒体作业批改完的学生作业详情
    public function mulMarkingAfterHomework(){
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '作业完成情况');
        $this->assign('navicon', 'zuoyexitong');
        $homeworkId = I('homeworkId');
        $id = I('id');
        $uid = I('uid');
        $info = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);
        $this->data = $info;

        $list = D('Exercises_homework_basics')->getHomeworkIdgetfk($homeworkId);
        $newlist = [];
        foreach ($list as $k=>$v) {
            array_push($newlist,$v['fname'].$v['sname']);
        }
        $this->newlist = implode(',',$newlist);

        $score = D('Exercises_student_homework')->getStudentScore($id);
        $this->score = $score;
        $classId = I('classId');
        $this->homework_id = $homeworkId;
        $this->student_id = $uid;
        $this->classId = $classId;
        $this->display();
    }

    //多媒体作业学生作业详情
    public function mulStudentHomeworkDetails()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $this->assign('module', '班级行');
        $this->assign('nav', '作业系统');
        $this->assign('subnav', '查作业');
        $this->assign('navicon', 'zuoyexitong');

        $id = intval($_GET['id']);
        if(!$id){
            redirect(U('Index/systemError'));
        }
        $this->assign('id', $id);

        //判断是否存在作业了
        $Model = M('biz_homework_student_details');
        $result = $Model->where("id=$id")->find();
        if(empty($result)){
            redirect(U('Index/systemError'));
        }
        //这里判断,如果批改过了就不能再改了
        if($result['status']==2){
            $this->redirect(U('Teach/markingAfterHomework'),array('id'=>$id));
        }

        $this->assign('homework', $result);
        $chapter_result=array();
        if(!empty($result)){
            $where['homework_id']=$result['homework_id'];
            $homework_exercise_model=M('biz_homework_exercise');
            $chapter_result=$homework_exercise_model->where($where)
                ->join('biz_exercise_library_chapter chapter on chapter.id=biz_homework_exercise.chapter_id')
                ->group('chapter.id')
                ->field('chapter.chapter,chapter.festival')
                ->select();
        }
        $this->assign('chapter_data', $chapter_result);
        //试题信息
        $homeworkId = $result['homework_id'];
        $Model = M('biz_homework');
        $exerciseChapter = $Model->where("id=$homeworkId")->find();
        $this->assign('exerciseChapter', $exerciseChapter);
        $this->assign('homeworkId', $homeworkId);

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

    //根据节id获取所有习题
    //@参数：category[int] N 作业类型 1--词汇 2--句子 3--视频 4--课本
    //@参数：infoId[int]  节ID
    public function getExerciseList() {
        $category= '';
        $infoId =getParameter('section_id','int',true);
        $result = D('Exercises_textbook_tree_info_createexercise')->getGroupInfoOfExercises(0,$category,'exercise',$infoId);
        $this->showjson(200, '', $result);
    }

    //根据学生id和作业id获取所有习题详情
    public function getStudentExList() {
        $student_id =getParameter('student_id','int',true);
        $homework_id =getParameter('homework_id','int',true);
        $classId =getParameter('classId','int',true);
        $result = D('Exercises_student_homework')->getStudentExList($student_id,$homework_id,$classId);
        $this->showjson(200, '', $result);
    }


    /*
    *获得下一级知识点
    */
    function getNextLevelKnowledge()
    {
        $level = getParameter('level', 'int', false);
        if ($level == 1) { //1获取章内容
            $where['version_id'] = getParameter('version', 'int');//版本
            $where['course_id'] = getParameter('courseId', 'int');//学科
            $where['grade_id'] = getParameter('grade_id', 'int');//年级
            $where['school_term'] = getParameter('school_term', 'int');//分册
            $where['parent_id'] = 0;
            $result = D('Exercises_textbook_tree_breviary')->textbookConcat($where);//查询所有的章操作
            //查看以当前ID的知识下点下有无子集知识点，并合并
            foreach ($result as $key => $val) {
                $where2['parent_id'] = $val['id'];
                $count = D('Exercises_textbook_tree_breviary')->textbookConcat($where2);
                $count = count($count);
                $result[$key]['count'] = $count;
            }
        } else { //获取节、知识点、子集知识点
            $knowledge_id = getParameter('id', 'int');
            $result = D('Exercises_textbook_tree_breviary')->getTextbookKnowledgePointByParentId($knowledge_id);
            //查看以当前ID的知识下点下有无子集知识点，并合并
            foreach ($result as $key => $val) {
                $where2['parent_id'] = $val['id'];
                $count = D('Exercises_textbook_tree_breviary')->textbookConcat($where2);
                $count = count($count);
                $result[$key]['count'] = $count;
            }
        }
        foreach ($result as $k => $item) {
            $result[$k]['knowledge_name'] = stripslashes($result[$k]['knowledge_name']);
        }
        if (!empty($result)) {
            $this->showjson(200, '', $result);
        } else {
            $this->showjson(404, '没有查出数据');
        }

    }

    //根据作业id获取所有习题
    public function getExerciseHomeworkList() {

        $homeworkId =getParameter('homeworkId','int',true);
        $result = D('Exercises_homework_basics')->getExercises($homeworkId);
        $this->showjson(200, '', $result);
    }


    public function getExerciseIdList() {

        $exerciseId =getParameter('exerciseId','str',true);
        $result = D('Exercises_homework_basics')->getExercisesIdList($exerciseId);
        $this->showjson(200, '', $result);
    }

    //布置作业
    public function updateHomework() {
        $homeworkId =getParameter('id','int',true);
        $arrangeTime =getParameter('arrangeTime','str',true);
        $result = D('Exercises_homework_basics')->setUpdateHomework($homeworkId,$arrangeTime);

        if ($result!== false) {
            $this->showjson(200, '', $result);
        } else {
            $this->showjson(400, '', $result);
        }
    }


}

