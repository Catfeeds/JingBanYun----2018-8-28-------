<?php
namespace Home\Controller;

class TeacherHomeworkController extends PublicController{

	public function __construct(){
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
        $this->assign('navicon', 'zuoyexitong');
    }

    public function test() {
    }

    //获取我的班级列表
    public function getMyQuestionBankList() {
        $condition['biz_class_teacher.teacher_id'] = session('teacher.id');
        $condition['exercises_homwork_basics.is_delete'] = 2;
        $condition['biz_class.is_delete'] = 0;
        $result = D('Exercises_homework_class_relation')->getMyQuestionBankList($condition);
        $this->showMessage( 200,'success',$result);
    }

    //获取我的班级列表
    public function getMyClassList(){
        $list = D('Biz_class')->getClassListByTeacherAndStudent(session('teacher.id'));
        $this->showMessage( 200,'success',$list);
    }

    //获取我的作业列表
    public function getMyClassHomeworkList()
    {
        $result = array();
        $userId = session('teacher.id');
        $page=I('pageIndex')<1?1:I('pageIndex');
        $pageSize=I('pageSize')<1?1:I('pageSize');
        $status=getParameter('status','int',false);
        $classId=getParameter('classId','int',false);
        $keywork=getParameter('keywork','str',false);
        $keywork = trim($keywork);


        if (!empty($status)) {
            switch ($status) {
                case 1: //待开放
                    $havingwhere = "exercises_homwork_basics.release_time > ".'\''.date("Y-m-d H:i:s",time()).'\'';

                    break;
                case 2: //作业中
                    $havingwhere = "exercises_homwork_basics.release_time < ".'\''.date("Y-m-d H:i:s",time()).'\''." AND exercises_homwork_basics.create_at < ".'\''.date("Y-m-d H:i:s",time()).'\''." and "."exercises_homwork_basics.deadline > ".'\''.date("Y-m-d H:i:s",time()).'\''." and submit_student_count != class_student_count";

                    break;
                case 3: //待批改
                    $havingwhere = "(exercises_homwork_basics.deadline < ".'\''.date("Y-m-d H:i:s",time()).'\''." AND "."submit_student_count!=correct_student_count)"." OR (exercises_homwork_basics.deadline > ".'\''.date("Y-m-d H:i:s",time()).'\''." AND submit_student_count=class_student_count AND submit_student_count!=correct_student_count AND submit_student_count>0)";
                    break;
                case 4: //作业报告
                    //$havingwhere = "correct_student_count=submit_student_count and "."exercises_homwork_basics.deadline < ".'\''.date("Y-m-d H:i:s",time()).'\'';
                    $havingwhere = "(exercises_homwork_basics.deadline > ".'\''.date("Y-m-d H:i:s",time()).'\''."AND correct_student_count=class_student_count)"." OR "."(exercises_homwork_basics.deadline < ".'\''.date("Y-m-d H:i:s",time()).'\''." AND submit_student_count=correct_student_count)";
                    break;
            }
        }

        //echo $havingwhere;die();

        if (!empty($classId)) {
            $condition['exercises_homwork_class_relation.class_id'] = $classId;
        }

        if (!empty($keywork)) {
            $condition['exercises_homwork_basics.name'] = array('like','%'.$keywork.'%');
        }


        $condition['exercises_homwork_basics.create_user_id'] = $userId;
        $condition['exercises_homwork_basics.is_delete'] = 2;
        $condition['biz_class.is_delete'] = 0;
//            $condition['role'] = ROLE_TEACHER;
        $field = "group_concat(distinct exercises_student_homework.student_id) as student_list_id,exercises_homwork_basics.deadline,exercises_homwork_basics.total_score,exercises_homwork_basics.chapter_id,exercises_homwork_basics.knowledge_id,exercises_homwork_class_relation.class_id,exercises_homwork_basics.release_time,exercises_student_homework.correct_status,exercises_homwork_basics.create_at,exercises_homwork_basics.name,exercises_homwork_basics.id,dict_grade.grade as grade_name,biz_class.name as class_name,count(distinct exercises_homwork_relation.exercises_id) as exercises_id_count,count(distinct biz_class_student.student_id) as class_student_count,count(distinct exercises_student_homework.student_id) as submit_student_count,correct_student_count";
        $order ="exercises_homwork_basics.create_at desc,class_id desc";
        $result = D('Exercises_homework_class_relation')->getHomeworkListByClass($condition, $field, $page, $pageSize, $join = '', $order,$havingwhere);
        //print_r(M()->getLastSql());die();
        foreach ($result as $k=>&$v) {

            if ( strtotime($v['release_time']) > time() ) {
                $v['status'] = "未开始";
            } elseif( strtotime($v['create_at']) <time() &&  strtotime($v['release_time'])< time() && strtotime($v['deadline']) > time() && $v['submit_student_count'] != $v['class_student_count']) {
                $v['status'] = "作业中";
            } elseif ( (strtotime($v['deadline']) < time() && $v['submit_student_count']!=$v['correct_student_count']) || (strtotime($v['deadline']) > time() && $v['submit_student_count']==$v['class_student_count'] && $v['submit_student_count']!=$v['correct_student_count'] && $v['submit_student_count']>0 ) ) {
                //(time()>$v['deadline'] && $v['correct_student_count']!=$v['submit_student_count']) || ($v['correct_student_count']!=$v['submit_student_count'] &&$v['submit_student_count']==$v['class_student_count'] )
                $v['status'] = "待批改";
            } else {
                $v['status'] = "作业报告";
            }

            $v['deadline'] = date("m月d日 H:i",strtotime($v['deadline']))."截止";
            $v['create_at'] = date("Y-m-d",strtotime($v['create_at']));
            $v['create_at_show'] = date("m-d",strtotime($v['create_at']));


            if (!empty($v['chapter_id'])) {
                if ($v['chapter_id'] ==-1) {
                    $v['chapter_id'] = "期中";
                } elseif($v['chapter_id']==-2) {
                    $v['chapter_id'] = "期末";
                } else {
                    $chapter_map['id'] = $v['chapter_id'];
                    $chapter_map['level'] = 1;
                    $chapter_id_info = M('exercises_textbook_tree_info')->where($chapter_map)->find();
                    $v['chapter_id'] =  $chapter_id_info['tree_point_name'];
                }
            }

            if (!empty($v['knowledge_id'])) {
                $chapter_map['id'] = $v['knowledge_id'];
                $chapter_map['level'] = 2;
                $chapter_id_info = M('exercises_curriculum_tree_info')->where($chapter_map)->find();
                $v['knowledge_id'] =  $chapter_id_info['tree_point_name'];
            }

        }

        $this->showMessage( 200,'success',$result);
    }

    //格式化数据结构进行缓存存储
    public function FormatDataHomework() {
        $homeworkId = getParameter('homeworkId','int',false);
        $map['work_id'] = $homeworkId;
        $list = M('exercises_homwork_relation')
            ->join("exercises_createexercise ON exercises_createexercise.id = exercises_homwork_relation.exercises_id")
            ->join("left join exercises_course ON exercises_course.id = exercises_createexercise.home_topic_type")
            ->field("ordinary_type,types,CASE types WHEN 1 THEN  home_topic_type WHEN 2 THEN ordinary_type END topic_type,exercises_id,knowledge_type,count_score,exercises_course.name as topic_ype_name")
            ->where( $map )
            ->select();
        $exerciseIdList=[];
        $exerciseNumLength = 0;
        $exerciseNumScore=0;
        $selectExerciseGroupList=[];

        if(!empty($list)) {
            foreach ($list as $k=>&$v) {
                if ($v['types']!=1) {
                    if ($v['ordinary_type']==1) {
                        $v['topic_ype_name'] = "跟读-词汇";
                    }
                    if ($v['ordinary_type']==2) {
                        $v['topic_ype_name'] = "跟读-句子";
                    }
                    if ($v['ordinary_type']==3) {
                        $v['topic_ype_name'] = "看-视频";
                    }
                    if ($v['ordinary_type']==4) {
                        $v['topic_ype_name'] = "看-课本";
                    }
                }
                $exerciseIdList[] = $v['exercises_id'];
                $exerciseNumLength++;
                $exerciseNumScore += $v['count_score'];

                if(empty($selectExerciseGroupList[$v['topic_type']])) {
                    $arrmap = [];
                    $arrmap['typename'] = $v['topic_ype_name'];
                    $arrmap['num'] = 1;
                    $arrmap['score'] = $v['count_score'];

                    $selectExerciseGroupList[$v['topic_type']] = $arrmap;
                } else {
                    $selectExerciseGroupList[$v['topic_type']]["num"] += 1;
                    $selectExerciseGroupList[$v['topic_type']]["score"] += $v['count_score'];
                }
            }
        }

        $homeDetails = M('exercises_homwork_basics')->where("id=".$homeworkId)->find();

        $data['exerciseIdList'] = $exerciseIdList;
        $data['exerciseNumLength'] = $exerciseNumLength;
        $data['exerciseNumScore'] = $exerciseNumScore;
        $data['selectExerciseGroupList'] = $selectExerciseGroupList;
        $data['courseId'] = $homeDetails['course_id'];
        $this->showMessage( 200,'success',$data);
    }

    //获取我的收藏
    public function getMyExerciseCollectionList() {
        $userId = session('teacher.id');
        $role = 2;
        $map['exercises_collection.teacher_id'] = $userId;
        $map['exercises_collection.role'] = $role;

        $result = M('exercises_collection')->field("exercises_createexercise.*")->join("exercises_createexercise ON exercises_createexercise.id=exercises_collection.exercises_id")->where($map)->select();

        foreach($result as $key=>$val) {
            $result[$key]['search_name'] = htmlspecialchars_decode($val['search_name']);
            $result[$key]['subject_name'] = htmlspecialchars_decode($val['subject_name']);
            $result[$key]['analysis'] = htmlspecialchars_decode($val['analysis']);
            $result[$key]['answer'] = htmlspecialchars_decode($val['answer']);
        }
        $this->showMessage( 200,'success',$result);
    }

    //获取我的错误库班级列表
    public function getMyQuestionBankErrorList() {
        $condition['biz_class_teacher.teacher_id'] = session('teacher.id');
        $condition['exercises_homwork_basics.is_delete'] = 2;
        $condition['biz_class.is_delete'] = 0;
        $condition['exercises_student_relation.is_right'] = 2;
        $result = D('Exercises_homework_class_relation')->getMyQuestionBankErrorList($condition);
        $this->showMessage( 200,'success',$result);
    }

    //试题库学生错题章节信息
    public function getMyQuestionMaterialBankErrorList(){
        $userId = session('teacher.id');
        $classId = getParameter('classId','int',false);
        $condition['biz_class_teacher.teacher_id'] = $userId;
        $condition['exercises_homwork_class_relation.class_id'] = $classId;
        $condition['exercises_homwork_basics.is_delete'] = 2;
        $condition['biz_class.is_delete'] = 0;
        $condition['exercises_student_relation.is_right'] = 2;
        $result = D('Exercises_homework_class_relation')->getMyQuestionMaterialBankErrorList($condition);

        foreach ($result as $k=>&$v) {
            $map['parent_id'] = $v['chapter_id'];
            $map['level'] = 2;
            $res = M('exercises_textbook_tree_info')->field('id,tree_point_name')->where($map)->select();
            $v['festival_list'] = $res;
        }

        $this->showMessage( 200,'success',$result);
    }


    //我的习题库用的接口 根据学科 班级 章节 获取习题列表
    public function getMyExerciseBankList (){
        $userId = session('teacher.id');
        $classId = getParameter('classId','int');

        $chapterId = getParameter('chapterId','int',false);
        $festivalId = getParameter('festivalId','int',false);
        $home_topic_type = getParameter('home_topic_type','int',false);
        $difficultyId = getParameter('difficultyId','int',false);

        if(!empty($home_topic_type)) {
            $condition['exercises_createexercise.home_topic_type'] = $home_topic_type;
        }

        if(!empty($difficultyId)) {
            $condition['exercises_createexercise.difficulty'] = $difficultyId;
        }

        $page=I('pageIndex')<1?1:I('pageIndex');

        $condition['biz_class_teacher.teacher_id'] = $userId;
        $condition['exercises_homwork_class_relation.class_id'] = $classId;
        $condition['exercises_homwork_relation.chapter'] = $chapterId;
        $condition['exercises_homwork_relation.festival'] = $festivalId;
        $condition['exercises_homwork_basics.is_delete'] = 2;
        $condition['biz_class.is_delete'] = 0;
        $condition['exercises_student_relation.is_right'] = 2;
        $result = D('Exercises_homework_class_relation')->getMyExerciseBankList($condition,$page);

        foreach($result as $key=>$val) {
            $result[$key]['search_name'] = htmlspecialchars_decode($val['search_name']);
            $result[$key]['subject_name'] = htmlspecialchars_decode($val['subject_name']);
            $result[$key]['analysis'] = htmlspecialchars_decode($val['analysis']);
            $result[$key]['answer'] = htmlspecialchars_decode($val['answer']);
        }
        $this->showMessage( 200,'success',$result);
    }

    //根据学科和教材版本获取教材同步知识点的年级和分册

    public function getGradeAndF() {
        $f = getParameter('version_id','int');//版本
        $courseId = getParameter('courseId','int');
        $map['exercises_textbook_tree_breviary.version_id'] = $f;
        $map['exercises_textbook_tree_breviary.course_id'] = $courseId;//学科
        $list = M('exercises_textbook_tree_breviary')->field('exercises_textbook_tree_breviary.grade_id,dict_grade.grade,exercises_textbook_tree_breviary.school_term,exercises_textbook_tree_breviary.id')->join('dict_grade ON dict_grade.id = exercises_textbook_tree_breviary.grade_id')->where($map)->select();

        foreach ($list as $k=>&$v) {
            switch ($v['school_term']) {
                case  1:
                    $v['term_name'] = "上册";
                    break;
                case  2:
                    $v['term_name'] = "下册";
                    break;
                case  3:
                    $v['term_name'] = "全一册";
                    break;
            }
            $Zmap['level']=1;
            $Zmap['textbook_tree_breviary_id']=$v['id'];
            $z  = M('exercises_textbook_tree_info')->field('id as chapter_id,tree_point_name')->where($Zmap)->select();//查询章的
            $v['chapter_list'] = $z;

            foreach ($z as $ck=>&$cv) {
                $Smap['parent_id']=$cv['chapter_id'];
                $Smap['level']=2;
                $S  = M('exercises_textbook_tree_info')->field('id as section_id,tree_point_name')->where($Smap)->select();//查询节的
                $v['chapter_list'][$ck]['section_list'] = $S;
            }
        }

        $this->showMessage( 200,'success',$list);
    }

    //根据学科和教材版本获取课标知识树知识点

    public function getGradeAndFList() {
        $userId = getParameter('userId','int');
        $courseId = getParameter('courseId','int');

        $course_name = M('dict_course')->where("id=".$courseId)->field('course_name')->find();
        $map['auth_teacher.id'] =$userId;
        $xued = M('auth_teacher')->field('dict_schoollist.school_category')->join('dict_schoollist ON dict_schoollist.id=auth_teacher.school_id')->where($map)->find();

        $XId=[];
        switch ($xued['school_category']) {
            case 0:
                $XId[] = $xued['school_category'];
                break;
            case 1:
                $XId[] = $xued['school_category'];
                break;
            case 2:
                $XId[] = $xued['school_category'];
                break;
            case 3:
                $XId[] = $xued['school_category'];
                break;
            case 4:
                $XId[] = 1;
                $XId[] = 2;
                break;
            case 5:
                $XId[] = 1;
                $XId[] = 2;
                $XId[] = 3;
                break;
            case 6:
                $XId[] = 2;
                $XId[] = 3;
                break;
        }

        if (count($XId) > 1) { //多个学段
            $list = [];

            foreach ($XId as $k=>$v) {
                if ($v==1) {
                    $list[$k]['grade'] = "小学";
                }
                if ($v==2) {
                    $list[$k]['grade'] = "初中";
                }
                if ($v==3) {
                    $list[$k]['grade'] = "高中";
                }
                $list[$k]['grade_id'] = $v;
                //$list[$k]['term_name'] = $course_name['course_name'];
                //$list[$k]['term_name'] =$courseId;
                $where['course_id'] = $courseId;
                $where['Learning_period_id'] = $v;
                $id = M('exercises_curriculum_tree_breviary')->field('id')->where($where)->find();
                $whereE['curriculum_tree_breviary_id'] = $id['id'];
                $whereE['level'] = 1;
                $res = M('exercises_curriculum_tree_info')
                    ->join("exercises_textbook_tree_curriculum_tree ON exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id=exercises_curriculum_tree_info.id")
                    ->join("exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.knowledge_id=exercises_textbook_tree_curriculum_tree.textbook_tree_info_id")
                    ->where($whereE)
                    ->field("exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id as chapter_id,exercises_curriculum_tree_info.tree_point_name")
                    ->group('exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id')
                    ->select();
                $list[$k]['chapter_list'] = $res;

                if (!empty($res)) {
                    foreach ($res as $ck=>$cv) {
                        $cMap['parent_id'] = $cv['chapter_id']; //知识点id
                        $cMap['level'] = 2;
                        $reslist = M('exercises_curriculum_tree_info')
                            ->join("exercises_textbook_tree_curriculum_tree ON exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id=exercises_curriculum_tree_info.id")
                            ->join("exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.knowledge_id=exercises_textbook_tree_curriculum_tree.textbook_tree_info_id")
                            ->where($cMap)
                            ->field("exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id as section_id,exercises_curriculum_tree_info.tree_point_name")
                            ->group('exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id')
                            ->select();

                        $list[$k]['chapter_list'][$ck]['section_list'] = $reslist;
                    }
                }

            }

            $this->showMessage( 200,'success',$list);

        } else { //一个学段

            $where['course_id'] = $courseId;
            $where['Learning_period_id'] = $XId[0];
            $id = M('exercises_curriculum_tree_breviary')->field('id')->where($where)->find();
            $whereE['curriculum_tree_breviary_id'] = $id['id'];
            $whereE['level'] = 1;
            $res = M('exercises_curriculum_tree_info')
                ->join("exercises_textbook_tree_curriculum_tree ON exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id=exercises_curriculum_tree_info.id")
                ->join("exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.knowledge_id=exercises_textbook_tree_curriculum_tree.textbook_tree_info_id")
                ->where($whereE)
                ->field("exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id as chapter_id,exercises_curriculum_tree_info.tree_point_name")
                ->group('exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id')
                ->select();
            foreach ($res as $k=>$v) {
                $cMap['parent_id'] = $v['chapter_id'];//知识点id
                $cMap['level'] = 2;

                $reslist = M('exercises_curriculum_tree_info')
                    ->join("exercises_textbook_tree_curriculum_tree ON exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id=exercises_curriculum_tree_info.id")
                    ->join("exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.knowledge_id=exercises_textbook_tree_curriculum_tree.textbook_tree_info_id")
                    ->where($cMap)
                    ->field("exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id as section_id,exercises_curriculum_tree_info.tree_point_name")
                    ->group('exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id')
                    ->select();

                $res[$k]['section_list'] = $reslist;
            }

            $setdata=[[
                'grade' =>"知识点",
                'grade_id' =>-1,
                'chapter_list'=>$res
                ]
            ];

            $this->showMessage( 200,'success',$setdata);
        }

    }



    //习题库获取习题列表 全部习题用的接口 根据搜索条件搜索试题
    public function setSearchgetExerciseList() {
        $version_id = getParameter('version_id','int'); //版本
        $courseId = getParameter('course_id','int');//学科
        $grade_id = getParameter('grade_id','int',false);//年级
        $school_term = getParameter('school_term','int',false);//分册
        $chapter_id = getParameter('chapter_id','int',false);//章
        $section_id = getParameter('section_id','int',false);//节
        $knowledge_id = getParameter('knowledge_id','int',false);//节

        $userId = session('teacher.id');
        $page=I('pageIndex')<1?1:I('pageIndex');

        $type_id = getParameter('type_id','int',false);//题型
        $difficulty_id = getParameter('difficulty_id','int',false);//难度
        $status = getParameter('status','int',false);//状态 1 已收藏 2 曾出过 3 未出过 4 学生错题

        if (!empty($type_id)) {
            $where['exercises_createexercise.home_topic_type'] = $type_id;
            $where['exercises_createexercise.home_topic_type'] = $type_id;
        }

        if (!empty($difficulty_id)) {
            $where['exercises_createexercise.difficulty'] = $difficulty_id;
        }
        if (!empty($status)) {
            if ($status ==1) { //已收藏
                $where['exercises_collection.exercises_id'] = array('EXP','is not null');
                $where['exercises_collection.teacher_id'] = $userId;
            }

            if ($status ==2) { //已出过
                $where['exercises_homwork_relation.teacher_id'] = array('EXP','is not null');
            }

            if ($status ==3) { //未出过
                $where['exercises_homwork_relation.teacher_id'] = array('EXP','is null');
            }

            if ($status ==4) { //学生错题
                $where['exercises_homwork_relation.teacher_id'] = array('EXP','is not null');
                $where['exercises_student_relation.is_right'] = 2;
            }
        }

        if (!empty($version_id)) {
            $where['exercises_textbook_tree_info_createexercise.version_id'] = $version_id;
        }

        if (!empty($courseId)) {
            $where['exercises_textbook_tree_info_createexercise.course_id'] = $courseId;
        }

        if (!empty($grade_id)) {
            $where['exercises_textbook_tree_info_createexercise.grade_id'] = $grade_id;
        }

        if (!empty($school_term)) {
            $where['exercises_textbook_tree_info_createexercise.section_id'] = $school_term;
        }

        if(!empty($chapter_id) && !empty($section_id)) {
            $where['exercises_textbook_tree_info_createexercise.chapter'] = $chapter_id;
            $where['exercises_textbook_tree_info_createexercise.festival'] = $section_id;
        }
        if (!empty($knowledge_id)) {
            $where['exercises_textbook_tree_info_createexercise.knowledge_id'] = $knowledge_id;
        }


        $list = M('exercises_createexercise')
            ->join("exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id = exercises_createexercise.id")
            ->join("left join exercises_collection ON exercises_collection.exercises_id=exercises_createexercise.id")
            ->join("left join exercises_homwork_relation ON exercises_homwork_relation.exercises_id=exercises_createexercise.id")
            ->join("left join exercises_student_relation ON exercises_student_relation.exercises_id=exercises_createexercise.id")
            ->where($where)
            ->field("exercises_collection.id as eid,exercises_createexercise.id,exercises_createexercise.subject_name,exercises_createexercise.home_topic_type,exercises_createexercise.difficulty")
            ->page($page, 20)
            ->group('exercises_createexercise.id')
            ->select();

        $this->showMessage( 200,'success',$list);
    }

    //试卷列表
    public function ExercisesPaperList() {
        $city_id = getParameter('city_id','str',false);
        $year = getParameter('year','str',false);
        $type_id = getParameter('paper_type','int',false);
        $grade_id = getParameter('grade','int',false);
        $subject = getParameter('course_id','int',false);
        $page=I('pageIndex')<1?1:I('pageIndex');
        $pageSize=I('pageSize')<1?1:I('pageSize');

        if (!empty($city_id) && $city_id!='全部') {
            $where['exercises_create_paper.region'] = $city_id;
        }
        if (!empty($year) && $year!=0 && $year!='全部') {
            $where['exercises_create_paper.year'] = $year;
        }

        if (!empty($type_id) && $type_id!='全部') {
            $where['exercises_create_paper.paper_type'] = $type_id;
        }

        if (!empty($grade_id) && $grade_id!='全部') {
            $where['exercises_create_paper.grade'] = $grade_id;
        }
        if (!empty($subject) && $subject!='全部' ) {
            $where['exercises_create_paper.subject'] = $subject;
        }

        $field = "exercises_create_paper.id,paper_name,city_id,year,paper_type,period,course_name,region";
        $where['exercises_create_paper.status'] = 110;
        $where['exercises_create_paper.is_delete'] = 2;
        $res    = D('Exercises_homework_class_relation')->getPaperListView($where,$field, $page, $pageSize);

        $city = D('Dict_citydistrict')->getProvince();
        $citylist= [];
        foreach ($city as $ck=>$cv) {
            $citylist[$cv['id']] = $cv['name'];
        }

        foreach ($res as $k=>&$v) {
            $p = C('paperCity');
            $pr = C('questionCategory');
            $v['paper_type'] = $p[$v['paper_type']];
            $v['period'] = $pr[$v['period']];
            $map['paper_id'] = $v['id'];
            $num = M('exercises_paper_bigquestion')->field("sum(big_topic_num) as exercises_num")->where($map)->select();
            $v['exercises_count'] = $num[0]['exercises_num'];
            $v['city_id'] = $citylist[$v['city_id']];

        }
        $this->showMessage( 200,'success',$res);
    }

    //根据试卷id获取习题列表
    public function getPaperExerciseList() {

        $paperId = getParameter('paperId','int');
        $bigList = D('Exercises_paper_bigquestion')->getPaperBigQuestion( $paperId );
        if (!empty($bigList)) {
            foreach ($bigList as $k=>$v) {
                if (!empty($v['id'])) {
                    $data['big_question_id'] = $v['id'];
                    $childquestions = D('Exercises_parper_concat')->getQuestionBigPaper($data); //关联试卷试题的关系
                    if (!empty($childquestions)) {
                        $bigList[$k]['childquestions'] = $childquestions;
                    }
                }
            }
        }

        $data['exercise_list'] =$bigList;
        $this->showMessage( 200,'success',$bigList);
    }


    //获取我的组卷列表
    public function getMyTestAssemblyList() {
        $userId = session('teacher.id');
        $map['exercises_create_paper.teacher_id'] = $userId;
        $list = M('exercises_create_paper')
            ->join("left join exercises_paper_bigquestion ON exercises_paper_bigquestion.paper_id=exercises_create_paper.id")
            ->field('exercises_create_paper.id,paper_name,score,sum(exercises_paper_bigquestion.big_topic_num) as exercise_count')
            ->where( $map )
            ->group('exercises_create_paper.id')
            ->select();
        $this->showMessage( 400,'success',$list);
    }

    //获取所有云作业列表
    public function getCloudExerciseList() {
	    $map['is_delete'] = 2;
        $map['types'] = 2;
        $map['status'] = 110;

        $page=I('pageIndex')<1?1:I('pageIndex');
        $pageSize=I('pageIndex')<1?1:I('pageSize');
        $ordinary_type = getParameter('ordinary_type','int',false);
        $courseId = getParameter('courseId','int',false);

        if(!empty($ordinary_type)) {
            $map['ordinary_type'] = $ordinary_type;
        }
        if(!empty($courseId)) {
            $map['subject'] = $courseId;
        }


	    $list = M('exercises_createexercise')->field("id,subject_name as url,words as name,count_score as point,analysis as translation,ordinary_type as category")->page($page, $pageSize)->where($map)->select();
        foreach ($list as $k=>&$v) {
            switch ($v['category']){
                case 1:
                    $v['category_name'] = "跟读-词汇";
                    break;
                case 2:
                    $v['category_name'] = "跟读-句子";
                    break;
                case 3:
                    $v['category_name'] = "看-视频";
                    break;
                case 4:
                    $v['category_name'] = "看-课本";
                    break;

            }
        }
        $this->showMessage( 200,'success',$list);
    }

    //云作业习题类型
    public function cloudExerciseUrl() {
        $courseId = getParameter('courseId','int',false);
        if (!empty($courseId)) {
            $map['subject'] =$courseId;
        }

        $list = M('exercises_createexercise')
            ->group('ordinary_type')
            ->where("types=2 and ordinary_type != ''")
            ->field('ordinary_type as id')
            ->where($map)
            ->select();

        foreach ($list as $k=>&$v) {
            switch ($v['id']){
                case 1:
                    $v['category_name'] = "跟读词汇";
                    break;
                case 2:
                    $v['category_name'] = "跟读句子";
                    break;
                case 3:
                    $v['category_name'] = "看视频";
                    break;
                case 4:
                    $v['category_name'] = "看课本";
                    break;

            }
        }

        $this->showMessage( 400,'success',$list);
    }

    //获取版本和学科
    public function getVersionAndCourseList() {
        $map['exercises_createexercise.status'] = 110;
        $map['exercises_createexercise.is_delete'] = 1;
        $courselist = M('exercises_createexercise')->join('exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id=exercises_createexercise.id')->field('exercises_textbook_tree_info_createexercise.course_id as id,exercises_textbook_tree_info_createexercise.course_name as name')->group('exercises_textbook_tree_info_createexercise.course_id')->select();

        $gradelist = M('exercises_createexercise')->join('exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id=exercises_createexercise.id')->field('exercises_textbook_tree_info_createexercise.grade_id as id,exercises_textbook_tree_info_createexercise.grade_name as name')->group('exercises_textbook_tree_info_createexercise.grade_id')->select();

        $versionlist = M('exercises_createexercise')
            ->join('exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id=exercises_createexercise.id')
            ->join('exercises_textbook_version ON exercises_textbook_version.id=exercises_textbook_tree_info_createexercise.version_id')
            ->field('exercises_textbook_tree_info_createexercise.version_id as id,exercises_textbook_version.version_name as name')
            ->group('exercises_textbook_tree_info_createexercise.version_id')
            ->select();

        $regionlist = M('exercises_create_paper')
            ->field('region as name')
            ->where("region != ''")
            ->group('region')
            ->select();

        $yearlist = M('exercises_create_paper')
            ->field('year as name')
            ->where("year != ''")
            ->group('year')
            ->select();

        $data['course_list'] = $courselist;
        $data['version_list'] = $versionlist;
        $data['gradelist'] = $gradelist;
        $papertypeList = array(array('id'=>1,'name'=>'真题',),array('id'=>2,'name'=>'模拟题',),array('id'=>3,'name'=>'同步检测',));
        $data['papertypeList'] = $papertypeList;
        $data['regionList'] = $regionlist;
        $data['yearlist'] = $yearlist;

        $this->showMessage( 200,'success',$data);
    }

    //删除我的组卷
    public function deleteMyTestAssembly(){
        //$userId = getParameter('userId','int'); //用户id和试卷id
        $id = getParameter('paperId','str');

        $Model = M();
        $Model->startTrans();

        $map['paper_id'] = array('in',$id);
        $idList = M('exercises_parper_concat')->where($map)->select();

        if (!empty($idList )) {
            foreach ($idList as $k=>$v) {
                $exerList[] = $v['exercise_id'];
                $bigpaper[] = $v['big_question_id'];
                $currentList[] = $v['id'];
                $paper_idarr[] = $v['paper_id'];

            }

            $pids = implode(",",$paper_idarr);

            $pmap['id'] = array('in',$pids);

            $pid = M('exercises_create_paper')->where($pmap)->delete();

            $eids = implode(",",$exerList);

            $emap['id'] = array('in',$eids);
            $eid = M('exercises_createexercise')->where($emap)->delete();

            $bids = implode(",",$bigpaper);
            $bmap['id'] = array('in',$bids);
            $bid = M('exercises_paper_bigquestion')->where($bmap)->delete();

            $aids = implode(",",$currentList);
            $amap['id'] = array('in',$aids);
            $aid = M('exercises_parper_concat')->where($amap)->delete();

            if ( $eid!==false && $bid!==false && $aid!==false && $pid!==false) {
                $Model->commit();
                $this->showMessage( 200,'success','删除成功');
            } else {
                $this->showMessage( 400,'error','删除失败');
                $Model->rollback();
            }
        } else {
            $Model->rollback();
            $this->showMessage( 400,'error','删除失败');
        }


    }

    //根据习题id获取所有的习题详情
    public function getExerciseIdAllDetails(){
        $id = getParameter('exercise_id_list','str');
        $userId = getParameter('userId','int',false);
        $role = getParameter('role','int',false);
        if (strpos($id,",")!== false) {
            $list=[];
            $id = explode(",",$id);
            foreach ( $id as $k=>$vid) {
                $list[$k] = $this->getExerciseIdDetails($vid,$userId,$role);
            }
        } else {
            $list = $this->getExerciseIdDetails($id,$userId,$role);
        }

        $this->showMessage( 400,'success',$list);
    }

    //根据习题id获取习题详情
    public function getExerciseIdDetails($id,$userId,$role) {

//        $id = getParameter('id','int');
//        $userId = getParameter('userId','int',false);
//        $role = getParameter('role','int',false);
        if( empty($id) || is_numeric($id)==false ) {
            die('参数错误');
        }

        $exercise_info = D('Create_Exercise')->getExerciseInfo( $id );
        if (empty($exercise_info)) {
            return "";
        }
        if($exercise_info['types'] == EXERCISE_TYPE_ABNORMAL)
        {
            redirect('/index.php?m=Exercise&c=Multimedia&a=homeworkDetails&id='.$id);
        }


        $difficultyList = C('difficulty');
        $difficultyName = '';
        $difficulty = D('Exercises_createexercise')->getExerciseDifficulty($id);
        foreach ($difficultyList as $key2 => $val2) {
            if ($difficulty == $val2['id'])
                $difficultyName = $val2['name'];
        }
        $exercise_info['difficulty_name'] = $difficultyName;
        if ($exercise_info['exercise_type']==1) { //独立题
            $exercise_info['topic_type_show'] = $exercise_info['topic_type']-1;
            $exercise_info['study_section'] = $exercise_info['study_section']-1;
            $exercise_info['exercise_source'] = $exercise_info['exercise_source']-1;

            $exercise_info['topic_type_name'] = C('exercisesType.'.$exercise_info['topic_type_show']);
            $exercise_info['study_section_name'] = C('Studysection.'.$exercise_info['study_section']);
            $exercise_info['exercise_source_name'] = C('SOURCE.'.$exercise_info['exercise_source']);

            if (!empty($exercise_info['subject'])) {
                $course_name = D('Exercises_Course')->getCourseName($exercise_info['subject']);
            }
            $exercise_info['course_name'] = $course_name['name'];

            $getCourse = D('Exercises_Course')->getCourseList($exercise_info['subject']);
            if (!empty($getCourse)) {
                foreach ($getCourse as $k=>$v) {
                    if ($exercise_info['home_topic_type'] == $v['id']) {
                        $exercise_info['topic_type_name']['name'] = $v['name'];
                        break;
                    }
                }
            }

        } else { //复合题

            $getCourse = D('Exercises_Course')->getCourseList($exercise_info['subject']);
            if (!empty($getCourse)) {
                foreach ($getCourse as $k=>$v) {
                    if ($exercise_info['home_topic_type'] == $v['id']) {
                        $exercise_info['topic_type_name']['name'] = $v['name'];
                        break;
                    }
                }
            }

            $exercise_info['study_section'] = $exercise_info['study_section']-1;
            $exercise_info['exercise_source'] = $exercise_info['exercise_source']-1;

            $exercise_info['study_section_name'] = C('Studysection.'.$exercise_info['study_section']);
            $exercise_info['exercise_source_name'] = C('SOURCE.'.$exercise_info['exercise_source']);

            if (!empty($exercise_info['subject'])) {
                $course_name = D('Exercises_Course')->getCourseName($exercise_info['subject']);
            }
            $exercise_info['course_name'] = $course_name['name'];

        }

        $exercise_info['search_name'] = htmlspecialchars_decode($exercise_info["search_name"]);
        $exercise_info['subject_name'] = htmlspecialchars_decode($exercise_info["subject_name"]);
        $exercise_info['answer'] = htmlspecialchars_decode($exercise_info['answer']);
        $exercise_info['json_html'] = htmlspecialchars_decode($exercise_info["json_html"]);
        $exercise_info['answer'] = htmlspecialchars_decode(json_decode($exercise_info["answer"],true));
        $exercise_info['analysis'] = htmlspecialchars_decode($exercise_info["analysis"]);

        $ecmap['exercises_id'] = $id;
        $ecmap['teacher_id'] = $userId;
        $ecmap['role'] = $role;
        $ec = M('exercises_collection')->where($ecmap)->find();
        if(!empty($ec)) {
            $exercise_info['eid'] =$ec['id'];
        } else {
            $exercise_info['eid'] =null;
        }

        return $exercise_info;
        //$this->showMessage( 400,'success',$exercise_info);

    }
    public function getOrderedExerciseList() {
        $id = getParameter('id','str');
        $userId = getParameter('userId','str',false);
        $list = A('ApiInterface/Version1_3/HomeworkCommon')->getOrderedExerciseList($id,2,$userId);
        //print_r($list);
//        foreach ($list as $k=>&$v) {
//            if (!empty($v['data'])) {
//
//                foreach ($v['data'] as $ck=>&$cv) {
//                    if (!empty($cv['data'])) {
//                        foreach ($cv['data'] as $cck=>&$ccv) {
//                            $ccv['subject_name'] = htmlspecialchars_decode($ccv['subject_name']);
//                            $ccv['translation'] = htmlspecialchars_decode($ccv['translation']);
//                            $ccv['subject_name'] =str_replace('ㄖ','&nbsp;__________&nbsp; ',$ccv['subject_name']);
//
//                            $ccv['answer'] = json_decode($ccv['answer'],true);
//                            if (!empty($ccv['answer']) && is_array($ccv['answer'])) {
//                                foreach ($ccv['answer'] as $ak=>$av) {
//                                    $ccv['answer'][$ak] = htmlspecialchars_decode($av);
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//        }

        $this->showMessage( 200,'success',$list);
    }

    //修改作业时间
    public function editHomeWorkTime() {
        $release_time = getParameter('startime','str',false);
        $deadline = getParameter('endtime','str',false);
        $id = getParameter('homeworkId','str',false);
        $classId = getParameter('classId','str',false);
        $isTrue = M('exercises_homwork_basics')->where(compact('id'))->save(compact('release_time','deadline'));
        if ($isTrue !== false) {
            $this->showMessage( 200,'success',"");
        } else {
            $this->showMessage( 400,'success',"");
        }
    }

    //修改作业名称
    public function editHomeWorkName() {
        $id = getParameter('homeworkId','int',false);
        $name = getParameter('name','str',false);
        $isTrue = M('exercises_homwork_basics')->where(compact('id'))->save(compact('name'));
        if ($isTrue !== false) {
            $this->showMessage( 200,'success',"");
        } else {
            $this->showMessage( 400,'success',"");
        }
    }

    //作业详情
    public function getHomeworkDetail()
    {
        $homeworkId = getParameter('homeworkId', 'int',false);
        $classId = getParameter('classId', 'int',false);

        $result = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);
        $map['work_id'] = $homeworkId;
        $where['biz_class.id'] = $classId;

        $classinfo = M('biz_class')->join("dict_grade ON dict_grade.id=biz_class.grade_id")->field("biz_class.name,dict_grade.grade")->where($where)->find();

        $exerlist = M('exercises_homwork_relation')->field("exercises_id")->where($map)->select();
        $result['exerciseList'] = $exerlist;
        $result['class_ino'] = $classinfo;
        $this->showMessage( 200,'success',$result);
    }


    //根据试卷Id获取详情
    public function getPaperDetails() {
        $paperId = getParameter('paperId','int');

        $paperinfo = D('Exercises_create_paper')->getPaperInfo( $paperId,"exercises_create_paper.paper_name,exercises_create_paper.score");
        $map['paper_id'] = $paperId;
        $count = M('exercises_parper_concat')->where($map)->count();
        $paperinfo['count'] = $count;

        $bigList = D('Exercises_paper_bigquestion')->getPaperBigQuestion( $paperId );

        $exerciseIdList =[];
        if (!empty($bigList)) {
            foreach ($bigList as $k=>$v) {
                if (!empty($v['id'])) {
                    $data['big_question_id'] = $v['id'];
                    $childquestions = D('Exercises_parper_concat')->getQuestionBigPaper($data); //关联试卷试题的关系

                    if (!empty($childquestions)) {
                        foreach ($childquestions as $qk=>$qv) {
                            $exerciseIdList[] = $qv['id'];
                        }
                        $bigList[$k]['childquestions'] = $childquestions;
                    }
                }
            }
        }
        $paperinfo["exercise_list"] = $bigList;
        $paperinfo["name"] = $paperinfo['paper_name'];
        $paperinfo['exerciseIdList'] = $exerciseIdList;
        $this->showMessage( 200,'success',$paperinfo);
    }

    //获取所有待批改作业列表
    public function getAllCorrectedExerciseList() {
	    $map['work_id'] = getParameter('homeworkId', 'int',false);
        $map['class_id'] = getParameter('classId', 'int',false);
        $map['correct_status'] = 1; //已批改
	    $Ylist = M('exercises_student_homework')->field("exercises_student_homework.id as submitid,auth_student.id,auth_student.avatar,student_name,avatar,sex,total_score")->join("left join auth_student ON auth_student.id=exercises_student_homework.student_id")->where($map)->select();

        $map['correct_status'] = 0;//未批改
	    $Wlist = M('exercises_student_homework')->field("exercises_student_homework.id as submitid,auth_student.id,auth_student.avatar,student_name,avatar,sex,total_score")->join("left join auth_student ON auth_student.id=exercises_student_homework.student_id")->where($map)->select();
        $submitmap=[];

        foreach ($Ylist as $k=>&$v) {
            $submitmap[] = $v['id'];
            if (empty($v['avatar']) || $v['avatar'] =='default.jpg') {
                if ($v['sex']=="男") {
                    $v['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_m.png";
                } else {
                    $v['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_w.png";
                }

            } else {
                $v['avatar'] = C('oss_path').$v['avatar'];
            }
        }

        foreach ($Wlist as $kk=>&$vv) {
            $submitmap[] = $vv['id'];
            if (empty($vv['avatar']) || $vv['avatar'] =='default.jpg') {
                if ($vv['sex']=="男") {
                    $vv['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_m.png";
                } else {
                    $vv['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_w.png";
                }

            } else {
                $vv['avatar'] = C('oss_path').$vv['avatar'];
            }
        }

        $result['Ylist'] = $Ylist; //已批改
        $result['Wlist'] = $Wlist;//未批改



        $search['exercises_homwork_class_relation.work_id'] = $map['work_id'];
        $search['exercises_homwork_class_relation.class_id'] = $map['class_id'];

        //未提交的 查询的所有的用户呀

        $allStudent = M('exercises_homwork_class_relation')
            ->field('biz_class_student.student_id,auth_student.avatar,student_name,avatar,sex')
            ->join('biz_class_teacher ON biz_class_teacher.class_id = exercises_homwork_class_relation.class_id')
            ->join('biz_class ON biz_class.id = exercises_homwork_class_relation.class_id')
            ->join('left join biz_class_student ON biz_class_student.class_id = biz_class.id and biz_class_student.status=2')
            ->join('auth_student ON auth_student.id = biz_class_student.student_id')
            ->group('auth_student.id')
            ->where($search)
            ->select();

        foreach ($allStudent as $vvk=>&$vvv) {
            if (empty($vvv['avatar']) || $vvv['avatar'] =='default.jpg') {
                if ($vvv['sex']=="男") {
                    $vvv['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_m.png";
                } else {
                    $vvv['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_w.png";
                }

            } else {
                $vvv['avatar'] = C('oss_path').$vvv['avatar'];
            }
        }

        $result['Alist'] = $allStudent;//全部人数

        if (!empty($allStudent)) {
            foreach ($allStudent as $sk => &$sv) {
                if (in_array($sv['student_id'], $submitmap)) {
                    unset($allStudent[$sk]);
                } else {
                    if (empty($sv['avatar']) || $sv['avatar'] =='default.jpg') {
                        if ($sv['sex']=="男") {
                            $sv['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_m.png";
                        } else {
                            $sv['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_w.png";
                        }

                    } else {
                        $sv['avatar'] = $sv['avatar'];
                    }
                }
            }
            $NoSubmitStudent = array_values($allStudent);
        }

        $result['Nlist'] = $NoSubmitStudent;//未提交
        $result['Slist'] = $submitmap;//已提交

        $this->showMessage( 200,'success',$result);
    }

    //试卷库
    public function testPaperList(){
       $this->display();
    }

    //布置试卷
    public function publishHomework(){
       $this->display();
    }

    //查看已选试题（布置的第二步）
    public function checkHomeworkChoose(){
        $start =date('Y-m-d H:i:s',time());
	    $this->setstartTime = $start;

        $end =date("Y-m-d H:i:s",strtotime("+1 day"));
        $this->setendTime = $end;
        $this->display();
    }

    //我的收藏
    public function myCollectionList(){
       $this->display();
    }

    //纠错
    public function errorCorrection() {
        $exerciseId= getParameter('exerciseId','int');
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $flag_type = getParameter('flag_type','str',false);
        $content = getParameter('content','str');

        $map['flag_type'] =$flag_type;
        $map['content'] =$content;
        $map['create_at'] =time();
        $map['user_id'] =$userId;
        $map['role'] =$role;
        $map['exercise_id'] =$exerciseId;

        $id = M('error_correction')->add($map);

        if ($id !== false) {
            $this->showMessage( 200,'success',"");
        } else {
            $this->showMessage( 400,'error',"");
        }
    }

    //复制习题
    public function copyHomework(){
        $this->homeworkId = getParameter('homeworkId','int');
        $this->classId = getParameter('classId','int');
        $start =date('Y-m-d H:i:s',time());
        $this->setstartTime = $start;

        $end =date("Y-m-d H:i:s",strtotime("+1 day"));
        $this->setendTime = $end;
        $this->display();
    }

    //首页
    public function homeworkList(){
	    //sleep(1);
       $this->display();
    }

    //查看作业题 跳到待布置页面
    public function lookHomeWorkExercise(){
        $this->homeworkId = getParameter('homeworkId','str');
        $this->classId = getParameter('classId','str');
        $this->status = getParameter('status','str');
        $this->display();
    }

    //作业——待布置
    public function homeworkNotStarted(){
	    $this->homeworkId = getParameter('homeworkId','str');
        $this->classId = getParameter('classId','str');
        $this->display();
    }

    //作业——作业中
    public function homeworkDoing(){
        $this->homeworkId = getParameter('homeworkId','str');
        $this->classId = getParameter('classId','str');
       $this->display();
    }

    //作业——待批改
    public function homeworkNotCorrect(){
        $this->homeworkId = getParameter('homeworkId','str');
        $this->classId = getParameter('classId','str');
       $this->display();
    }

    //作业——作业报告
    public function homeworkReport(){
        $this->homeworkId = getParameter('homeworkId','str');
        $this->classId = getParameter('classId','str');
       $this->display();
    }

    //作业-查看某个习题的回答情况
    public function checkOneExercise(){
        $this->homeworkId = getParameter('homeworkId','str');
        $this->classId = getParameter('classId','str');
        $this->exercisesId = getParameter('exercisesId','str');
       $this->display();
    }

    //批改学生作业
    public function correctStudentHomework(){
        $this->homeworkId = getParameter('homeworkId','int');
        $this->classId = getParameter('classId','int');
        $this->exerciseId = getParameter('exerciseId','int');
       $this->display();
    }

    //试卷详情
    public function testPaperDetails(){
        $this->paperId = getParameter('paperId','int');

        $start =date('Y-m-d H:i:s',time());
        $this->setstartTime = $start;

        $end =date("Y-m-d H:i:s",strtotime("+1 day"));
        $this->setendTime = $end;

        $this->display();
    }

    //学生做作业的详情
    public function studentHomework(){
        $this->homeworkId = getParameter('homeworkId','int');
        $this->classId = getParameter('classId','int');
        $this->studentId = getParameter('studentId','int');
        $this->submitId = getParameter('submitId','int');
       $this->display();
    }

    //学生作业详情
    public function getStudentHomeWorkDetails() {
        $homeworkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');
        $studentId = getParameter('studentId','int');
        $map['exercises_student_homework.student_id'] = $studentId;
        $map['exercises_student_homework.work_id'] = $homeworkId;
        $map['exercises_student_homework.class_id'] = $classId;

        $details = M('exercises_student_homework')
            ->join("left join exercises_homwork_basics ON exercises_homwork_basics.id=exercises_student_homework.work_id")
            ->join("left join auth_student ON auth_student.id=exercises_student_homework.student_id")
            ->where($map)
            ->field("auth_student.student_name,exercises_student_homework.id as submitId,submit_at,work_timeout,correct_status,exercises_homwork_basics.total_score")
            ->find();

        $details['student_name'] =$details['student_name']."的作业";

        if (!empty($details)) {
            $cmap['work_id'] = $details['submitid'];
            $cmap['is_delete'] = 1;
            $cmap['class_id'] = $classId;
            $cmap['homework_id'] = $homeworkId;
            $cmap['student_id'] = $studentId;
            $count = M('exercises_student_relation')->where($cmap)->count();

            $setsum =  M('exercises_student_relation')->field("sum(exercises_score) as score")->where($cmap)->select();
            if (!empty($setsum)) {
                $details['score'] =  $setsum[0]["score"];
            }

            $cmap['is_right'] = 1;
            $rightcount = M('exercises_student_relation')->where($cmap)->count();
            $details['correct_rate'] = (($rightcount/$count)*100)."%";
            unset($cmap['is_right']);

            $wmap['exercises_student_relation.work_id'] = $details['submitid'];
            $wmap['exercises_student_relation.class_id'] = $classId;
            $wmap['exercises_student_relation.homework_id'] = $homeworkId;
            $wmap['exercises_student_relation.student_id'] = $studentId;
            $wmap['exercises_student_relation.is_delete'] = 1;

            $list = M('exercises_student_relation')
                ->join("exercises_createexercise ON exercises_createexercise.id=exercises_student_relation.exercises_id")
                ->join("left join exercises_homwork_relation ON exercises_homwork_relation.exercises_id=exercises_createexercise.id")
                ->field("exercises_student_relation.exercises_score,exercises_homwork_relation.id as eid,exercises_student_relation.id,exercises_createexercise.subject_name,exercises_createexercise.ordinary_type,exercises_homwork_relation.knowledge_type,exercises_createexercise.words as analysis,exercises_createexercise.answer")
                ->where($wmap)
                ->group("exercises_student_relation.exercises_id")
                ->select();

            $type1 =[];
            $type2 =[];
            $type3 =[];
            $num = 0;
            foreach ($list as $k=>&$v) {
                $v['subject_name'] = htmlspecialchars_decode($v['subject_name']);
                $v['answer'] = htmlspecialchars_decode($v['answer']);

                $num++;
                if (empty($v['knowledge_type'])) {
                    $v['knowledge_type'] = 1;
                }
                if ($v['knowledge_type']==1) {
                    if ($v['ordinary_type']==1) {
                        $type1['type1'][] = $v;
                    }elseif ($v['ordinary_type']==2){
                        $type1['type2'][] = $v;
                    } elseif($v['ordinary_type']==3) {
                        $type1['type3'][] = $v;
                    }else {
                        $type1['type4'][] = $v;
                    }

                }
                if ($v['knowledge_type']==2) {
                    $type2[] = $v;
                }
                if ($v['knowledge_type']==3) {
                    $type3[] = $v;
                }


            }

            $setlist['type1'] = $type1;
            $setlist['type2'] = $type2;
            $setlist['type3'] = $type3;

            $details['exercise_list_count'] = $num;
            $details['exercise_list'] = $setlist;

            $wmap['exercises_student_relation.is_right'] = 2;
            $errorlist = M('exercises_student_relation')
                ->join("exercises_createexercise ON exercises_createexercise.id=exercises_student_relation.exercises_id")
                ->join("left join exercises_homwork_relation ON exercises_homwork_relation.exercises_id=exercises_createexercise.id")
                ->field("exercises_student_relation.exercises_score,exercises_homwork_relation.id as eid,exercises_student_relation.id,exercises_createexercise.subject_name,exercises_createexercise.ordinary_type,exercises_homwork_relation.knowledge_type,exercises_createexercise.words as analysis,exercises_createexercise.answer")
                ->where($wmap)
                ->group("exercises_student_relation.exercises_id")
                ->select();

            $errortype1 =[];
            $errortype2 =[];
            $errortype3 =[];
            $errnum=0;
            foreach ($errorlist as $k=>&$v) {

                $v['subject_name'] = htmlspecialchars_decode($v['subject_name']);
                $v['answer'] = htmlspecialchars_decode($v['answer']);

                $errnum++;

                if (empty($v['knowledge_type'])) {
                    $v['knowledge_type'] = 1;
                }

                if ($v['knowledge_type']==1) {
                    if ($v['ordinary_type']==1) {
                        $errortype1['type1'][] = $v;
                    }elseif ($v['ordinary_type']==2){
                        $errortype1['type2'][] = $v;
                    } elseif($v['ordinary_type']==3) {
                        $errortype1['type3'][] = $v;
                    }else {
                        $errortype1['type4'][] = $v;
                    }

                }
                if ($v['knowledge_type']==2) {
                    $errortype2[] = $v;
                }
                if ($v['knowledge_type']==3) {
                    $errortype3[] = $v;
                }
            }

            $seterrorlist['type1'] = $errortype1;
            $seterrorlist['type2'] = $errortype2;
            $seterrorlist['type3'] = $errortype3;
            $details['error_exercise_list'] = $seterrorlist;
            $details['error_exercise_list_count'] = $errnum;


            //寄语

            //exercises_student_homework
            $clist = M('exercises_homework_comment')->where("homework_submit_id=".$details["submitid"])->select();

            foreach ($clist as $kk=>$vv) {
                $clist[$kk]['comment'] = htmlspecialchars_decode($vv['comment']);
            }
            $details['comment_list'] = $clist;
        }

        $this->showMessage( 200,'success',$details);
    }

    //获取学生已批改列表
    public function getStudentCorrectStatusList() {
        $homeworkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');
        $exerciseId = getParameter('exerciseId','int');

        $map['homework_id'] = $homeworkId;
        $map['class_id'] = $classId;
        $map['exercises_id'] = $exerciseId;
        $countStudent = M('exercises_student_relation')->where($map)->count(); //总人数
        $map['status'] = 1;
        $YCountStudent = M('exercises_student_relation')->where($map)->count(); //已批改人数


        $map['status'] = 0;
        $allStudentList = M('exercises_student_relation')->where($map)->select();//总人数列表

        foreach ($allStudentList as $k=>$v) {
            if(!empty($v['student_id'])) {
                $stumap['id'] = $v['student_id'];
                $stuinfo = M('auth_student')->where($stumap)->find();
                if (empty($stuinfo['avatar']) || $stuinfo['avatar'] =='default.jpg') {
                    if ($stuinfo['sex']=="男") {
                        $stuinfo['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_m.png";
                    } else {
                        $stuinfo['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_w.png";
                    }

                } else {
                    $stuinfo['avatar'] = C('oss_path').$stuinfo['avatar'];
                }

                $allStudentList[$k]['student_name'] = $stuinfo['student_name'];
                $allStudentList[$k]['avatar'] = $stuinfo['avatar'];
            }

            if ($v['status']==1) {
                $allStudentList[$k]['exercises_score'] = $v['exercises_score']."分";
            } else {
                $allStudentList[$k]['exercises_score'] = "";
            }

        }

        $DCountStudent = M('exercises_student_relation')
            ->join("auth_student ON auth_student.id=exercises_student_relation.student_id")
            ->field("exercises_student_relation.id as submit_id,auth_student.student_name,auth_student.id as student_id,answer,total_score as exercises_score")
            ->where($map)
            ->select();//未批改列表

        foreach ($DCountStudent as $wk=>$wv) {
            $comment = M('exercises_homework_comment')->where("submit_exercise_id=".$wv['submit_id'])->find();
            if (!empty($comment)) {
                $DCountStudent[$wk]['comment'] = htmlspecialchars_decode($comment['comment']);
            }
        }

        $data['countStudent'] = $countStudent;
        $data['YCountStudent'] = $YCountStudent;
        $data['allStudentList'] = $allStudentList;
        $data['DCountStudent'] = $DCountStudent;
        $this->showMessage( 200,'success',$data);
    }

    //教师寄语
    public function teacherSendMessage()
    {
        $submit_exercise_id = I('submitId');
        $comment = I('comment');
        $istrue = M('exercises_homework_comment')->where("submit_exercise_id=".$submit_exercise_id)->find();
        if (!empty($istrue)) {
            $id   = M('exercises_homework_comment')->where("submit_exercise_id=".$submit_exercise_id)->save(compact('comment'));
        }else {
            $id   = M('exercises_homework_comment')->add(compact('submit_exercise_id','comment'));
        }

        if ($id !== false) {
            $this->showMessage( 200,'success',"");
        } else {
            $this->showMessage( 400,'error',"");
        }

    }

    public function teacherMasterSendMessage()
    {
        $homework_submit_id = I('submitId');
        $comment = I('comment');

        $istrue = M('exercises_homework_comment')->where("homework_submit_id=".$homework_submit_id)->find();
        if (!empty($istrue)) {
            $id   = M('exercises_homework_comment')->where("homework_submit_id=".$homework_submit_id)->save(compact('comment'));
        }else {
            $id   = M('exercises_homework_comment')->add(compact('homework_submit_id','comment'));
        }

        if ($id !== false) {
            $this->showMessage( 200,'success',"");
        } else {
            $this->showMessage( 400,'error',"");
        }

    }

    //修改分数
    public function editExerciseScore(){

        $homeWorkId = getParameter('homeWorkId','int',false);
        $classId = getParameter('classId','int',false);
        $exercise_id = getParameter('exercise_id','int',false);
        $score = getParameter('score','int',false);
        $studentId = getParameter('studentId','int',false);
        $isRight = getParameter('isRight','int',false);

        $where['student_id'] = $studentId;
        $where['homework_id'] = $homeWorkId;
        $where['class_id'] = $classId;
        $where['exercises_id'] = $exercise_id;

        $datascore['exercises_score'] = $score;
        $datascore['is_right'] = $isRight;
        $datascore['status'] = 1;
        $datascore['edit_at'] = time();

        M()->startTrans();

        $updateExerId = M('exercises_student_relation')->where($where)->save($datascore);


        //如果学生做错了题目
        if($isRight==2) {
            $exerciseId = $exercise_id;
            $exerciseInfo = D('Exercises_createexercise')->getExerciseInfo($exerciseId);
            $courseId = $exerciseInfo['subject'];
            D('Exercises_wrong_exercise')->add($studentId,$exerciseId,$courseId);
        }


        if ($updateExerId === false) {
            M()->rollback();
            $this->showMessage( 400,'success',"添加失败");

        } else {

            $mapwhere['student_id'] = $studentId;
            $mapwhere['homework_id'] = $homeWorkId;
            $mapwhere['class_id'] = $classId;
            $mapwhere['status'] = 0;
            $mapwhere['type'] = 1;
            $emptyData = M('exercises_student_relation')->where($mapwhere)->select();

            if ( empty($emptyData) ) {
                unset($mapwhere['status']);
                unset($mapwhere['type']);

                $scoreList = M('exercises_student_relation')->where($mapwhere)->select();

                $count = 0;
                foreach ($scoreList as $sk=>$sv) {
                    $count += intval($sv['exercises_score']);
                }


                //统计错题数
                $mapwhere['is_right'] = 2;
                $errorCount = M('exercises_student_relation')->where($mapwhere)->count();


                $masterwhere['work_id'] = $homeWorkId;
                $masterwhere['class_id'] = $classId;
                $masterwhere['student_id'] = $studentId;
                $mastersave['correct_status'] = 1;
                $mastersave['total_score'] = $count;
                $mastersave['correct_at'] = date("Y-m-d H:i:s");
                $mastersave['wrong_num'] = $errorCount;


                $masterId   = M('exercises_student_homework')->where($masterwhere)->save($mastersave);

                $masterClass['work_id'] = $homeWorkId;
                $masterClass['class_id'] = $classId;
                $masterClass['correct_status'] = 1;
                $count = M('exercises_student_homework')->where($masterClass)->count();
                $setInc['correct_student_count'] = $count;

                unset($masterClass['correct_status']);
                $classMasterId   = M('exercises_homwork_class_relation')->where($masterClass)->save($setInc);
//                $classMasterId = M('exercises_homwork_class_relation')->where($masterClass)->setInc('correct_student_count',1);

                if ($masterId === false || $classMasterId===false) {
                    M()->rollback();
                    $this->showMessage( 400,'success',"添加失败");
                } else {
                    M()->commit();
                    $this->showMessage( 200,'success',"添加成功");
                }
            } else {
                M()->commit();
                $this->showMessage( 200,'success',"添加成功");
            }
        }
    }
}
