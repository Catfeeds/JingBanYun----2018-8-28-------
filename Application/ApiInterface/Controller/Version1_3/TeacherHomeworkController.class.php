<?php
namespace ApiInterface\Controller\Version1_3;
use Common\Common\simple_html_dom;
include 'Application/Exercise/Conf/const.php';

class TeacherHomeworkController extends PublicController{
    private $createExercise;
	public function __construct(){
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
        $this->createExercise =  include 'Application/Exercise/Conf/createExercise.php';
    }

	public function test(){
         $this->display();
    }

    private function __getWeek($date)
    {
        //强制转换日期格式
        $date_str=date('Y-m-d',strtotime($date));
        //封装成数组
        $arr=explode("-", $date_str);
        //参数赋值
        //年
        $year=$arr[0];
        //月，输出2位整型，不够2位右对齐
        $month=sprintf('%02d',$arr[1]);
        //日，输出2位整型，不够2位右对齐
        $day=sprintf('%02d',$arr[2]);
        //时分秒默认赋值为0；
        $hour = $minute = $second = 0;
        //转换成时间戳
        $strap = mktime($hour,$minute,$second,$month,$day,$year);
        //获取数字型星期几
        $number_wk=date("w",$strap);
        //自定义星期数组
        $weekArr=array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
        //获取数字对应的星期
        return $weekArr[$number_wk];
    }

    /**
     * @描述：获取我的班级作业列表
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：pageIndex[int] N 分页INDEX 从1开始
     * @参数：pageSize[int] N 分页大小 不传默认20
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getMyClassHomeworkList()
    {
        $userId = getParameter('userId','int');
        $page=I('pageIndex')<1?1:I('pageIndex');
        $status=getParameter('status','int',false);
        $classId=getParameter('classId','int',false);

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

        if (!empty($classId)) {
            $condition['exercises_homwork_class_relation.class_id'] = $classId;
        }

        $condition['exercises_homwork_basics.create_user_id'] = $userId;
        $condition['exercises_homwork_basics.is_delete'] = 2;
        $condition['biz_class.is_delete'] = 0;
//            $condition['role'] = ROLE_TEACHER;
        $field = "group_concat(distinct exercises_student_homework.student_id) as student_list_id,exercises_homwork_basics.deadline,exercises_homwork_basics.total_score,exercises_homwork_basics.chapter_id,exercises_homwork_basics.knowledge_id,exercises_homwork_class_relation.class_id,exercises_homwork_basics.release_time,exercises_student_homework.correct_status,exercises_homwork_basics.create_at,exercises_homwork_basics.name,exercises_homwork_basics.id,dict_grade.grade as grade_name,biz_class.name as class_name,count(distinct exercises_homwork_relation.exercises_id) as exercises_id_count,count(distinct biz_class_student.student_id) as class_student_count,count(distinct exercises_student_homework.student_id) as submit_student_count,correct_student_count";
        $order ="exercises_homwork_basics.create_at desc,class_id desc";
        $result = D('Exercises_homework_class_relation')->getHomeworkListByClass($condition, $field, $page, 100, $join = '', $order,$havingwhere);

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

            $v['exercises_id_count'] = intval($v['exercises_id_count']);
            $v['class_student_count'] = intval($v['class_student_count']);
            $v['submit_student_count'] = intval($v['submit_student_count']);
            $v['correct_student_count'] = intval($v['correct_student_count']);

//            $v['deadline'] = date("m月d日 H:i",strtotime($v['deadline']))."截止";
//            $v['create_at'] = date("Y-m-d",strtotime($v['create_at']));
//            $v['create_at_show'] = date("m-d",strtotime($v['create_at']));

            $v['deadline'] = date("m月d日 H:i",strtotime($v['deadline']))."截止";
            $v['name'] = $v['name'].sprintf("共(%s)题",$v['exercises_id_count']);
            $day = $this->__getWeek($v['create_at']);
            $v['create_at'] = date("Y年m月d日",strtotime($v['create_at'])) . " $day";
            $v['create_at_show'] = date("m月d日",strtotime($v['create_at'])) . " $day";

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

    public function getHomeWorkStatus() {
        $userId = getParameter('userId','int');
        $homeworkId = getParameter('homeworkId','int');

        $condition['exercises_homwork_basics.create_user_id'] = $userId;
        $condition['exercises_homwork_basics.is_delete'] = 2;
        $condition['exercises_homwork_basics.id'] = $homeworkId;
        $condition['biz_class.is_delete'] = 0;
//            $condition['role'] = ROLE_TEACHER;
        $field = "group_concat(distinct exercises_student_homework.student_id) as student_list_id,exercises_homwork_basics.deadline,exercises_homwork_basics.total_score,exercises_homwork_basics.chapter_id,exercises_homwork_basics.knowledge_id,exercises_homwork_class_relation.class_id,exercises_homwork_basics.release_time,exercises_student_homework.correct_status,exercises_homwork_basics.create_at,exercises_homwork_basics.name,exercises_homwork_basics.id,dict_grade.grade as grade_name,biz_class.name as class_name,count(distinct exercises_homwork_relation.exercises_id) as exercises_id_count,count(distinct biz_class_student.student_id) as class_student_count,count(distinct exercises_student_homework.student_id) as submit_student_count,correct_student_count";
        $order ="exercises_homwork_basics.create_at desc,class_id desc";
        $havingwhere="";
        $result = D('Exercises_homework_class_relation')->getHomeworkListByClass($condition, $field, $page, 100, $join = '', $order,$havingwhere);

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

            $v['exercises_id_count'] = intval($v['exercises_id_count']);
            $v['class_student_count'] = intval($v['class_student_count']);
            $v['submit_student_count'] = intval($v['submit_student_count']);
            $v['correct_student_count'] = intval($v['correct_student_count']);

//            $v['deadline'] = date("m月d日 H:i",strtotime($v['deadline']))."截止";
//            $v['create_at'] = date("Y-m-d",strtotime($v['create_at']));
//            $v['create_at_show'] = date("m-d",strtotime($v['create_at']));

            $v['deadline'] = date("m月d日 H:i",strtotime($v['deadline']))."截止";
            $v['name'] = $v['name'].sprintf("共(%s)题",$v['exercises_id_count']);
            $day = $this->__getWeek($v['create_at']);
            $v['create_at'] = date("Y年m月d日",strtotime($v['create_at'])) . " $day";
            $v['create_at_show'] = date("m月d日",strtotime($v['create_at'])) . " $day";

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
        $status = "";
        if (!empty($result)) {
            foreach ($result as $k=>$v) {
                $status = $v['status'];
            }
        }

        $this->showMessage( 200,'success',$status);
    }

    //获取首页的翻查搜索条件
    public function getSearchWhere() {
        $result = array();
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $page=I('pageIndex')<1?1:I('pageIndex');
        $status=getParameter('status','int',false);


        if( 1 == FAKE_DATA)
        {
            $result[] = array('id' => 1, 'name' => '一年级一班', 'homeworkcount' => 15);
            $result[] = array('id' => 2, 'name' => '一年级二班', 'homeworkcount' => 16);
            $result[] = array('id' => 3, 'name' => '一年级三班', 'homeworkcount' => 17);
            $result[] = array('id' => 4, 'name' => '一年级四班', 'homeworkcount' => 18);
        }
        else
        {
            if (!empty($status)) {
                switch ($status) {
                    case 1: //待开放
                        $havingwhere = "exercises_homwork_basics.release_time > ".'\''.date("Y-m-d H:i:s",time()).'\'';

                        break;
                    case 2: //作业中
                        $havingwhere = "exercises_homwork_basics.release_time < ".'\''.date("Y-m-d H:i:s",time()).'\''." AND exercises_homwork_basics.create_at < ".'\''.date("Y-m-d H:i:s",time()).'\''." and "."exercises_homwork_basics.deadline > ".'\''.date("Y-m-d H:i:s",time()).'\''." and submit_student_count != class_student_count";

                        break;
                    case 3: //待批改
                        $havingwhere = "(exercises_homwork_basics.deadline < ".'\''.date("Y-m-d H:i:s",time()).'\''." AND "."submit_student_count!=correct_student_count)"." OR (exercises_homwork_basics.deadline > ".'\''.date("Y-m-d H:i:s",time()).'\''." AND submit_student_count=class_student_count AND submit_student_count!=correct_student_count)";
                        break;
                    case 4: //作业报告
                        //$havingwhere = "correct_student_count=submit_student_count and "."exercises_homwork_basics.deadline < ".'\''.date("Y-m-d H:i:s",time()).'\'';
                        $havingwhere = "(exercises_homwork_basics.deadline > ".'\''.date("Y-m-d H:i:s",time()).'\''."AND correct_student_count=class_student_count)"." OR "."(exercises_homwork_basics.deadline < ".'\''.date("Y-m-d H:i:s",time()).'\''." AND submit_student_count=correct_student_count)";
                        break;
                }
            }

            if (!empty($classId)) {
                $condition['exercises_homwork_class_relation.class_id'] = array('neq',$classId);
            }

            $condition['exercises_homwork_basics.create_user_id'] = $userId;
            $condition['exercises_homwork_basics.is_delete'] = 2;
            $condition['biz_class.is_delete'] = 0;
//            $condition['role'] = ROLE_TEACHER;
            $field = "exercises_homwork_class_relation.class_id,exercises_homwork_basics.release_time,exercises_student_homework.correct_status,exercises_homwork_basics.create_at,exercises_homwork_basics.deadline,exercises_homwork_basics.name,exercises_homwork_basics.id,dict_grade.grade as grade_name,biz_class.name as class_name,count(distinct exercises_homwork_relation.exercises_id) as exercises_id_count,count(distinct biz_class_student.student_id) as class_student_count,count(distinct exercises_student_homework.student_id) as submit_student_count,sum(distinct correct_status=1) as correct_student_count";
            $order ="exercises_homwork_basics.create_at desc";
            $result = D('Exercises_homework_class_relation')->getHomeworkSearchWhere($condition, $field, $page, 10, $join = '', $order,$havingwhere);

            $class_list_arr=[];
            foreach ($result as $k=>$v) {
                $class_list_arr[$k]['id'] = $v['class_id'];
                $class_list_arr[$k]['name'] = $v['grade_name'].$v['class_name'];
            }

            $status_list_arr = array(
                array(
                    'id' => 1,
                    'name' =>'待布置',
                ),
                array(
                    'id' => 2,
                    'name' =>'作业中',
                ),
                array(
                    'id' => 3,
                    'name' =>'待批改',
                ),
                array(
                    'id' => 4,
                    'name' =>'作业报告',
                ),
            );

            $res['class_list'] =$class_list_arr;
            $res['status_list'] =$status_list_arr;

        }
        $this->showMessage( 200,'success',$res);
    }

    //作业详情
    public function getHomeworkDetail()
    {
        $homeworkId = getParameter('homeworkId', 'int',false);
        $result = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);

        $buf = [];
        $buf['id'] = $result['id'];
        $buf['name'] = $result['name'];
        $buf['name'] = $result['name'];
        $buf['release_time'] = $result['release_time'];
        $buf['deadline'] = $result['deadline'];
        $this->showMessage( 200,'success',$buf);
    }
	//习题详情
    public function homeworkDetails()
    {
        $this->homeworkId = I('homeworkId');
        $this->classId = I('classId');
        $this->status = I('status');
        $this->exercises_id_count = I('exercises_id_count');
	    $this->userId = I('userId');
	    $this->role= I('role');
        $this->display();
    }
	// 待批改单个习题详情
	public function homeworkCorrecting()
	{
		$this->classId = I('classId');
		$this->userId = I('userId');
		$this->exerciseId = I('exerciseId');
		$this->workId = I('workId');
        $this->studentId = I('studentId');
        $this->homeworkId = I('homeworkId');
		$this->display("homeworkCorrecting");
	}
	public function swiperExerciseDetails()
	{
		$this->homeworkId = I('homeworkId');
		$this->classId = I('classId');
		$this->status = I('status');
		$this->userId = I('userId');
		$this->role= I('role');
		$this->flag= I('flag');
		$this->exerciseId = I('exerciseId');
		$this->workId = I('workId');
		$this->display("swiperExerciseDetails");
	}
    //获取已提交或者未提交的作业列表
    public function getStudentHomeWorkList() {

        $homeworkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');
        $isSubmit = getParameter('isSubmit','int',false);

        $condition['exercises_homwork_class_relation.work_id'] = $homeworkId;
        $condition['biz_class.is_delete'] = 0;

        if (!empty($isSubmit) && $isSubmit==1) {
            $condition['exercises_student_homework.id'] = array('exp','is not null');
        }
        if (!empty($isSubmit) && $isSubmit==2) {
            $condition['exercises_student_homework.id'] = array('exp','is null');
        }

        $field = "exercises_homwork_basics.deadline,auth_student.id,auth_student.avatar,auth_student.student_name,auth_student.sex,exercises_student_homework.total_score,exercises_student_homework.id as hid,exercises_student_homework.submit_at";
        $order ="exercises_student_homework.submit_at desc";
        $havingwhere= "";
        $result = D('Exercises_homework_class_relation')->getStudentHomeWorkList($condition, $field, 1, 1000, $join = '', $order,$havingwhere,$classId);

        foreach ($result as $k=>$v) {
            if ($v['submit_at'] > $v['deadline']) {
                $result[$k]['submit_at'] = $v['submit_at']."截止后提交";
            }

            if (empty($v['avatar'])|| $v['avatar'] =='default.jpg') {
                if ($v['sex']=="男") {
                    $result[$k]['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_m.png";
                } else {
                    $result[$k]['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_w.png";
                }

            } else {
                $result[$k]['avatar'] = C('oss_path').$v['avatar'];
            }
        }
        $this->showMessage( 200,'success',$result);
    }

    //获取所有待批改试题
    public function getCorrectHomeWorkListEx() {

        $homeworkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');

        $map['homework_id'] = $homeworkId;
        $map['class_id'] = $classId;


        $map['exercises_student_relation.status'] = 0;

        $map['exercises_student_relation.type'] = 1;
        $map['exercises_student_relation.is_delete'] = 1;

        $field = "exercises_createexercise.subject_name,sum(exercises_student_relation.status=1) as correct_status_count,count(student_id) as student_count,exercises_id,work_id";
        $result = D('Exercises_homework_class_relation')->getStudentHomeWorkListEx($map,$field);

        foreach ($result as $k=>$v) {
            if ($v['submit_at'] > $v['deadline']) {
                $result[$k]['submit_at'] = $v['submit_at']."截止后提交";
            }
            if ($v['correct_status_count'] == $v['student_count']) {
                $result[$k]['isP'] = 1;
            } else {
                $result[$k]['isP'] = 2; // 未批改
            }
            $result[$k]['homeworkId'] = $homeworkId;
            $result[$k]['classId'] = $classId;
            $result[$k]['subject_name'] = htmlspecialchars_decode($v['subject_name']);
            $exmap['exercises_createexercise.id'] = $v['exercises_id'];
            $typeinfo = M('exercises_createexercise')->join("exercises_course ON exercises_course.id=exercises_createexercise.home_topic_type")->field("exercises_course.name")->where($exmap)->find();
            $result[$k]['type_name'] = $typeinfo['name'];
            $result[$k]['subject_name'] =str_replace('ㄖ','&nbsp;__________&nbsp; ',$result[$k]['subject_name']);
        }
        $this->showMessage( 200,'success',$result);
    }

    public function getCorrectHomeWorkListExWork() {

        $homeworkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');

        $map['homework_id'] = $homeworkId;
        $map['class_id'] = $classId;

        $map['exercises_student_relation.type'] = 1;
        $map['exercises_student_relation.is_delete'] = 1;

        $field = "exercises_createexercise.subject_name,sum(exercises_student_relation.status=1) as correct_status_count,count(student_id) as student_count,exercises_id,work_id";
        $result = D('Exercises_homework_class_relation')->getStudentHomeWorkListEx($map,$field);

        foreach ($result as $k=>$v) {
            if ($v['submit_at'] > $v['deadline']) {
                $result[$k]['submit_at'] = $v['submit_at']."截止后提交";
            }
            if ($v['correct_status_count'] == $v['student_count']) {
                $result[$k]['isP'] = 1;
            } else {
                $result[$k]['isP'] = 2; // 未批改
            }
            $result[$k]['homeworkId'] = $homeworkId;
            $result[$k]['classId'] = $classId;
            $result[$k]['subject_name'] = htmlspecialchars_decode($v['subject_name']);
            $exmap['exercises_createexercise.id'] = $v['exercises_id'];
            $typeinfo = M('exercises_createexercise')->join("exercises_course ON exercises_course.id=exercises_createexercise.home_topic_type")->field("exercises_course.name")->where($exmap)->find();
            $result[$k]['type_name'] = $typeinfo['name'];
            $result[$k]['subject_name'] =str_replace('ㄖ','&nbsp;__________&nbsp; ',$result[$k]['subject_name']);
        }
        $this->showMessage( 200,'success',$result);
    }
    //开始批改作业
    public function startHomeworkCorrect(){
        $homeworkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');
        $exercisesId = getParameter('exercisesId','int');
        $teacherId = getParameter('teacherId','int');
        $map['homework_id'] = $homeworkId;
        $map['exercises_id'] = $exercisesId;
        $map['class_id'] = $classId;
        $map['teacher_id'] = $teacherId;
        $map['is_delete'] = 1;
        $map['status'] = 0;
        $list   =   M('exercises_student_relation')
            ->field("id,student_id,exercises_id,answer,total_score")
            ->where($map)
            ->select();

        foreach ($list as $k=>$v) {
            $teacher_name = M('auth_student')->where("id=".$v['student_id'])->find();
            $list[$k]['student_name'] = $teacher_name['student_name'];

            $exinfo = M('exercises_createexercise')->where("id=".$v['exercises_id'])->find();

            $exinfo['answer'] = json_decode($exinfo['answer'],true);
            $list[$k]['subject_name'] = htmlspecialchars_decode($exinfo['subject_name']);
            $list[$k]['subject_name'] = str_replace('ㄖ','&nbsp;__________&nbsp; ',$list[$k]['subject_name']);

            if (is_array($exinfo['answer'])) {
                foreach ($exinfo['answer'] as $kk=>$vv) {
                    $exinfo['answer'][$kk] = htmlspecialchars_decode($vv);
                }
                $list[$k]['right_key'] = implode(",",$exinfo['answer']);
            } else {
                $list[$k]['right_key'] = htmlspecialchars_decode($exinfo["answer"]);
            }
        }
        $this->showMessage( 200,'success',$list);

    }

    //待批改作业  查看作业内容
    public function LookContentHomeWorkDetails(){
        $homeworkId = getParameter('homeworkId','int');

        $details = M('exercises_homwork_basics')->where("id=".$homeworkId)->find();

        $classId = getParameter('classId','int');
        $map['exercises_homwork_basics.id'] = $homeworkId;
        $map['exercises_homwork_class_relation.class_id'] = $classId;

        $list = M('exercises_homwork_basics')
            ->join("exercises_homwork_class_relation ON exercises_homwork_class_relation.work_id=exercises_homwork_basics.id")
            ->join("exercises_homwork_relation ON exercises_homwork_relation.work_id=exercises_homwork_basics.id")
            ->join("exercises_createexercise ON exercises_createexercise.id=exercises_homwork_relation.exercises_id")
            ->where($map)
            ->field("exercises_homwork_relation.exercises_id,exercises_createexercise.count_score,exercises_homwork_relation.id,exercises_createexercise.subject_name,exercises_createexercise.ordinary_type,exercises_homwork_relation.knowledge_type,exercises_createexercise.analysis,exercises_createexercise.answer")
            ->group('exercises_homwork_relation.exercises_id')
            ->order("exercises_homwork_relation.create_at desc")
            ->select();
        $count = 0;
        $num = 0;
        $arrexerciseList=[];
        foreach ($list as $k=>$v) {
            $num++;
            $count += $v['count_score'];
            $arrexerciseList[] = $v['exercises_id'];
        }

        if (!empty($arrexerciseList)) {
            $exerciselist = A('ApiInterface/Version1_3/HomeworkCommon')->getOrderedExerciseList(implode(",",$arrexerciseList),2,0,1);
        }

        $setlist['num'] = $num;
        $setlist['name'] = $details['name'];
        $setlist['count'] = $count;
        $setlist['exerciselist'] = $exerciselist;
        $this->showMessage( 200,'success',$setlist);
    }


    public function changeTimeType($seconds){
    if ($seconds >3600){
    $hours =intval($seconds/3600);
    $minutes = $seconds % 3600;
    $time = $hours.":".gmstrftime('%M:%S',$minutes);
    }else{
        $time = gmstrftime('%H:%M:%S',$seconds);
    }
    return$time;
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
            ->field("exercises_student_homework.id as submitId,exercises_homwork_basics.name,auth_student.student_name,exercises_student_homework.id as submitId,submit_at,work_timeout,correct_status,exercises_homwork_basics.total_score")
            ->find();
        $details['work_timeout'] = $this->changeTimeType( $details['work_timeout'] );
        if(empty($details['submitid'])){
            $this->showMessage( 400,'failed',$details);
        }
        $contents = M('exercises_homework_comment')->where("homework_submit_id=".$details['submitid'])->find();
        $details['comment'] = htmlspecialchars_decode($contents['comment']);


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
            $details['correct_rate'] = round(($rightcount/$count)*100)."%";
            unset($cmap['is_right']);

            $wmap['exercises_student_relation.work_id'] = $details['submitid'];
            $wmap['exercises_student_relation.class_id'] = $classId;
            $wmap['exercises_student_relation.homework_id'] = $homeworkId;
            $wmap['exercises_student_relation.student_id'] = $studentId;
            $wmap['exercises_student_relation.is_delete'] = 1;

            $list = M('exercises_student_relation')
                ->join("exercises_createexercise ON exercises_createexercise.id=exercises_student_relation.exercises_id")
                ->join("left join exercises_homwork_relation ON exercises_homwork_relation.exercises_id=exercises_createexercise.id")
                ->field("exercises_student_relation.exercises_id,exercises_student_relation.exercises_score,exercises_homwork_relation.id as eid,exercises_student_relation.id,exercises_createexercise.subject_name,exercises_createexercise.ordinary_type,exercises_homwork_relation.knowledge_type,CASE types WHEN 1 THEN analysis WHEN 2 THEN words END analysis,exercises_createexercise.answer,exercises_student_relation.type,exercises_student_relation.answer as submit_answer")
                ->where($wmap)
                ->group("exercises_student_relation.exercises_id")
                ->select();

            $exerciseArr = [];
            $num = 0;
            foreach ($list as $k=>$v) {
                $exerciseArr[] = $v['exercises_id'];
                $num++;
            }
            

            $exerciselist = A('ApiInterface/Version1_3/HomeworkCommon')->getOrderedExerciseList(implode(",",$exerciseArr),2,0,1);

            foreach ($exerciselist as $answer=>&$answerV){
                foreach ($answerV['data'] as $dataanswer=>&$dataV) {
                    if(is_null($dataV['answer'])) {
                        $dataV['answer'] = "";
                    }

                    $mapanswer['homework_id'] = $homeworkId;
                    $mapanswer['class_id'] = $classId;
                    $mapanswer['class_id'] = $classId;
                    $mapanswer['student_id'] = $studentId;
                    $mapanswer['exercises_id'] = $dataV['id'];
                    $answerinfo = M('exercises_student_relation')->where($mapanswer)->find();
                    if (!empty($answerinfo['answer'])) {
                        $dataV['submit_score'] =  $answerinfo['exercises_score'];
                        $dataV['submit_answer'] = $answerinfo['answer'];
                    } else {
                        $dataV['submit_answer'] = "";
                    }
                }
            }

            $details['exercise_list_count'] = $num;
            $details['exercise_list'] = $exerciselist;

            $wmap['exercises_student_relation.is_right'] = 2;
            $wmap['exercises_createexercise.types'] = 1;
            $errorlist = M('exercises_student_relation')
                ->join("exercises_createexercise ON exercises_createexercise.id=exercises_student_relation.exercises_id")
                ->join("left join exercises_homwork_relation ON exercises_homwork_relation.exercises_id=exercises_createexercise.id")
                ->field("exercises_student_relation.exercises_id,exercises_student_relation.exercises_score,exercises_homwork_relation.id as eid,exercises_student_relation.id,exercises_createexercise.subject_name,exercises_createexercise.ordinary_type,exercises_homwork_relation.knowledge_type,exercises_createexercise.words as analysis,exercises_createexercise.answer")
                ->where($wmap)
                ->group("exercises_student_relation.exercises_id")
                ->select();

            $ErrexerciseArr = [];
            $errnum = 0;
            foreach ($errorlist as $ek=>$ev) {
                $ErrexerciseArr[] = $ev['exercises_id'];
                $errnum++;
            }

            $seterrorlist = A('ApiInterface/Version1_3/HomeworkCommon')->getOrderedExerciseList(implode(",",$ErrexerciseArr),2,0,1);


            foreach ($seterrorlist as $erranswer=>&$erranswerV){
                foreach ($erranswerV['data'] as $errdataanswer=>&$errdataV) {
                    if(is_null($errdataV['answer'])) {
                        $errdataV['answer'] = "";
                    }

                    $mapanswer['homework_id'] = $homeworkId;
                    $mapanswer['class_id'] = $classId;
                    $mapanswer['class_id'] = $classId;
                    $mapanswer['student_id'] = $studentId;
                    $mapanswer['exercises_id'] = $errdataV['id'];
                    $erranswerinfo = M('exercises_student_relation')->where($mapanswer)->find();
                    if (!empty($erranswerinfo['answer'])) {
                        $dataV['submit_score'] =  $answerinfo['exercises_score'];
                        $errdataV['submit_answer'] = $erranswerinfo['answer'];
                    } else {
                        $errdataV['submit_answer'] = "";
                    }
                }
            }

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

    //获取我的班级列表
    public function getMyClassList(){
        $userId = getParameter('userId', 'int');
        $list = D('Biz_class')->getClassListByTeacherAndStudentstatus($userId);
        foreach ($list as $k=>&$v) {
            $v['classname'] = $v['gradename'].$v['classshortname'];
        }
        $this->showMessage( 200,'success',$list);
    }

    //教师寄语
    public function teacherSendMessage() {
        $homework_submit_id = I('submitId');
        $comment = I('comment');
        $homeWorkId = I('homeWorkId');
        $classId = I('classId');
        $exercise_id = I('exercise_id');
        $score = I('score');
        $studentId = I('studentId');
        $isRight = I('isRight');

        if (empty($isRight)) {
            $cid   = M('exercises_homework_comment')->add(compact('homework_submit_id','comment'));

            if ($cid === false) {
                $this->showMessage( 400,'success',"添加失败");
                die();
            } else {
                $this->showMessage( 200,'success',"添加成功");
                die();
            }
        }

        M()->startTrans();
        $submit_exercise_id = $homework_submit_id;
        $id   = M('exercises_homework_comment')->add(compact('submit_exercise_id','comment'));

        if ($id === false) {

            M()->rollback();
            $this->showMessage( 400,'success',"添加失败");

        } else {

            if ($score==0 || !empty($score)) { //改变习题的状态
                $where['student_id'] = $studentId;
                $where['homework_id'] = $homeWorkId;
                $where['class_id'] = $classId;
                $where['exercises_id'] = $exercise_id;

                $datascore['exercises_score'] = $score;
                $datascore['is_right'] = $isRight;
                $datascore['status'] = 1;
                $datascore['edit_at'] = time();

                $updateExerId = M('exercises_student_relation')->where($where)->save($datascore);

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
                            $count += $sv['exercises_score'];
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

                        $classMasterId = M('exercises_homwork_class_relation')->where($masterClass)->setInc('correct_student_count',1);

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
            } else {
                M()->commit();
                $this->showMessage( 200,'success',"添加成功");
            }
        }
    }



    //获取教师所有未批改的作业数量
    public function getStudentCorrectCount() {
        $userId = getParameter('userId','int');
        $havingwhere = "(exercises_homwork_basics.deadline < ".'\''.date("Y-m-d H:i:s",time()).'\''." AND "."submit_student_count!=correct_student_count)"." OR (exercises_homwork_basics.deadline > ".'\''.date("Y-m-d H:i:s",time()).'\''." AND submit_student_count=class_student_count AND submit_student_count!=correct_student_count AND submit_student_count>0)";
        $condition['biz_class_teacher.teacher_id'] = $userId;
        $condition['exercises_homwork_basics.is_delete'] = 2;
        $condition['biz_class.is_delete'] = 0;
        $page = 1;
//            $condition['role'] = ROLE_TEACHER;
        $field = "group_concat(distinct exercises_student_homework.student_id) as student_list_id,exercises_homwork_basics.deadline,exercises_homwork_basics.total_score,exercises_homwork_basics.chapter_id,exercises_homwork_basics.knowledge_id,exercises_homwork_class_relation.class_id,exercises_homwork_basics.release_time,exercises_student_homework.correct_status,exercises_homwork_basics.create_at,exercises_homwork_basics.name,exercises_homwork_basics.id,dict_grade.grade as grade_name,biz_class.name as class_name,count(distinct exercises_homwork_relation.exercises_id) as exercises_id_count,count(distinct biz_class_student.student_id) as class_student_count,count(distinct exercises_student_homework.student_id) as submit_student_count,correct_student_count";
        $order ="exercises_homwork_basics.create_at desc,class_id desc";
        $result = D('Exercises_homework_class_relation')->getHomeworkListByClass($condition, $field, $page, 1000000, $join = '', $order,$havingwhere);
        $this->showMessage( 200,'success',count($result));

        $this->showMessage( 200,'success',count($result));
    }

    //点击推送
    public function push() {


        $homeworkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');
        $findmap['homework_id'] = $homeworkId;
        $findmap['class_id'] = $classId;
        $res = M('send_rush_news')->where($findmap)->find();
        if (empty($res)) {
            $addmap['homework_id'] = $homeworkId;
            $addmap['class_id'] = $classId;
            $addmap['addsendtime'] = time()+3600;
            M('send_rush_news')->add($addmap);
            $condition['exercises_homwork_class_relation.work_id'] = $homeworkId;
            $condition['biz_class.is_delete'] = 0;

            $condition['exercises_student_homework.id'] = array('exp','is null');
            $field = "exercises_homwork_basics.create_user_name,exercises_homwork_basics.deadline,auth_student.id,auth_student.avatar,exercises_student_homework.total_score,exercises_student_homework.id as hid,exercises_student_homework.submit_at";
            $order ="exercises_student_homework.submit_at desc";
            $havingwhere= "";
            $result = D('Exercises_homework_class_relation')->push($condition, $field, 1, 1000, $join = '', $order,$havingwhere,$classId);
            $studentId = [];
            if (!empty($result)) {
                foreach ($result as $k=>$v) {
                    $studentId[]=  $v['id'];
                }
            }

            if (!empty($studentId)) {
                $parameters = array( 'msg' => array($result[0]['create_user_name']) , 'url' => array( 'type' => 0));
                $Message = new \Home\Controller\MessageController();
                $studentId = implode(',',$studentId);
                $Message->addPushUserMessage('GROUP_STUDENT_SENDHOMEWORK',3,$studentId,$parameters);
            }
            $this->showMessage( 200,'success','发送完毕');
        } else {

            if (time()< $findmap['addsendtime']) {

                $addmap[] = $findmap;
                $addmap['addsendtime'] = time()+3600;

                M('send_rush_news')->where($findmap)->save($addmap);

                $condition['exercises_homwork_class_relation.work_id'] = $homeworkId;
                $condition['biz_class.is_delete'] = 0;

                $condition['exercises_student_homework.id'] = array('exp','is null');
                $field = "exercises_homwork_basics.create_user_name,exercises_homwork_basics.deadline,auth_student.id,auth_student.avatar,exercises_student_homework.total_score,exercises_student_homework.id as hid,exercises_student_homework.submit_at";
                $order ="exercises_student_homework.submit_at desc";
                $havingwhere= "";
                $result = D('Exercises_homework_class_relation')->push($condition, $field, 1, 1000, $join = '', $order,$havingwhere,$classId);
                $studentId = [];
                if (!empty($result)) {
                    foreach ($result as $k=>$v) {
                        $studentId[]=  $v['id'];
                    }
                }

                if (!empty($studentId)) {
                    $parameters = array( 'msg' => array($result[0]['create_user_name']) , 'url' => array( 'type' => 0));
                    $Message = new \Home\Controller\MessageController();
                    $studentId = implode(',',$studentId);
                    $Message->addPushUserMessage('GROUP_STUDENT_SENDHOMEWORK',3,$studentId,$parameters);
                }
                $this->showMessage( 200,'success','发送完毕');
            } else {
                $this->showMessage( 400,'error','发送失败');
            }
        }
    }

    public function getPushStatus() {
        $homeworkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');
        $findmap['homework_id'] = $homeworkId;
        $findmap['class_id'] = $classId;
        $res = M('send_rush_news')->where($findmap)->find();
        if (empty($res)) {
            $this->showMessage( 200,'success','show');
        } else {
            if (time()< $findmap['addsendtime']) {
                $this->showMessage( 200,'success','show');
            } else {
                $this->showMessage( 400,'success','hide');
            }
        }
    }

    //试卷列表
    public function ExercisesPaperList() {
        $city_id = getParameter('city_id','int',false);
        $year = getParameter('year','int',false);
        $type_id = getParameter('paper_type','int',false);
        $grade_id = getParameter('grade','int',false);
        $subject = getParameter('course_id','int',false);

        if (!empty($city_id)) {
            $where['exercises_create_paper.city_id'] = $city_id;
        }
        if (!empty($year) && $year!=0) {
            $where['exercises_create_paper.year'] = $year;
        }

        if (!empty($type_id)) {
            $where['exercises_create_paper.paper_type'] = $type_id;
        }

        if (!empty($grade_id)) {
            $where['exercises_create_paper.grade'] = $grade_id;
        }
        if (!empty($subject)) {
            $where['exercises_create_paper.subject'] = $subject;
        }

        $field = "exercises_create_paper.id,paper_name,city_id,year,paper_type,period";

        $where['exercises_create_paper.status'] = 110;
        $where['exercises_create_paper.is_delete'] = 2;
        $res    = D('Exercises_homework_class_relation')->getPaperListView($where,$field);
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
    public function paperList()
    {
        $this->userId = I('userId');
        $this->role = I('role');
        $this->paper_type= I('paper_type');
        $this->display();
    }
    //试卷列表布置组卷
    public function testPaperList()
    {
        $this->userId = I('userId');
        $this->role = I('role');
        $this->versionId = I('versionId');
        $this->courseId = I('courseId');
        $this->gradeId = I('gradeId');
        $this->schoolTerm = I('schoolTerm');
        $this->classId = I('classId');
        $this->chapterId = I('chapterId');
        $this->festivalId = I('festivalId');
        $this->parentId = I('parentId');
        $this->knowledgeId = I('knowledgeId');
        $this->edit = I('edit');
        $this->display();
    }
    public function studentHomeworkContent(){
        $this->userId = I('userId');
        $this->role = I('role');
        $this->homeworkId = I('homeworkId');
        $this->studentId = I('studentId');
        $this->classId = I('classId');
        $this->display();
    }
    public function homeworkContent(){
        $this->userId = I('userId');
        $this->role = I('role');
        $this->homeworkId = I('homeworkId');
        $this->classId = I('classId');
        $this->display();
    }
    //试卷搜索条件
    public function paperWhereSearch() {
        $diqu = D('Dict_citydistrict')->getProvince();
        $year = D('Exercises_homework_class_relation')->getyear();
        $paperCity = array(  //试卷类型
            array(
                'id' => 1,
                'name' => '真题'
            ),
            array(
                'id' => 2,
                'name' => '模拟题'
            ),
        );

        $grade = D('Dict_grade')->getGradeList();
        $courselist = M('exercises_create_paper')
            ->join('dict_course ON dict_course.id=exercises_create_paper.subject')
            ->field('dict_course.id,dict_course.course_name as name')
            ->group('exercises_create_paper.subject')
            ->select();

        $res['course_list'] = $courselist;
        $res['city'] = $diqu;
        $res['year'] = $year;
        $res['papertype'] = $paperCity;
        $res['grade'] = $grade;

        $this->showMessage( 200,'success',$res);
    }

    public function updateTime(){
        $homeworkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');

        $release_time = getParameter('release_time','str');
        $deadline = getParameter('deadline','str');

        if(!empty($release_time)) {
            $map['exercises_homwork_basics.release_time'] = $release_time;
        }

        if(!empty($deadline)) {
            $map['exercises_homwork_basics.deadline'] = $deadline;
        }

        $where['Exercises_homework_class_relation.work_id'] = $homeworkId;
        $where['Exercises_homework_class_relation.class_id'] = $classId;

        $id = M('exercises_homwork_basics')->join("Exercises_homework_class_relation ON Exercises_homework_class_relation.work_id=exercises_homwork_basics.id")->where($where)->save($map);

        if ($id !== false) {
            $this->showMessage( 200,'success','修改成功');
        } else {
            $this->showMessage( 400,'success','修改失败');
        }
    }

    //修改作业时间
    public function editHomeWorkTime() {
        $release_time = getParameter('startime','str',false);
        $deadline = getParameter('endtime','str',false);

        $idMobile = isMobile();
        if ($idMobile==true) {
            $release_time = date('Y-m-d H:i:s',$release_time);
            $deadline = date('Y-m-d H:i:s',$deadline);
        }

        $id = getParameter('homeworkId','str',false);
        //$classId = getParameter('classId','str',false);
        $isTrue = M('exercises_homwork_basics')->where(compact('id'))->save(compact('release_time','deadline'));
        if ($isTrue !== false) {
            $this->showMessage( 200,'success',"");
        } else {
            $this->showMessage( 400,'success',"");
        }
    }
    
    //学生单题批改情况 已提交 未提交 待批改
    public function studentCorrectHomeWorkSituation() {

        $homeworkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');
        $eid = getParameter('exercises_id','int');
        $map['homework_id'] = $homeworkId;
        $map['class_id'] = $classId;
        $map['exercises_id'] = $eid;
        $map['status'] = 1;

        $submitmap= []; //存放已提交的和未提交的

        //已批改
        $yitiajio = M('exercises_student_relation')->where($map)->field("id as subminteid,exercises_score,student_id,answer")->select();
        foreach ($yitiajio as $k=>&$v) {
            $setmap['id'] = $v['student_id'];
            $studentY = M('auth_student')->where($setmap)->find();
            $submitmap[] = $v['student_id'];
            if (empty($studentY['avatar']) || $studentY['avatar'] =='default.jpg') {
                if ($studentY['sex']=="男") {
                    $v['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_m.png";
                } else {
                    $v['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_w.png";
                }

            } else {
                $v['avatar'] = C('oss_path').$studentY['avatar'];
            }
            $v['id'] = $studentY['id'];
            $v['student_name'] = $studentY['student_name'];
        }

        //未批改
        $map['status'] = 0;
        $field = "group_concat(student_id) as student_id,group_concat(id) as submintEid,answer";
        $result = D('Exercises_homework_class_relation')->studentCorrectHomeWorkSituation($map,$field);
        $subminteid = explode(",",$result[0]['subminteid']);
        if(!empty($result[0]['student_id'])) {
            $where['id'] = array('in',$result[0]['student_id']);
            $weipigai = M('auth_student')->field('student_name,avatar,id')->where($where)->select();

            foreach ($weipigai as $k=>&$v) {
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
                $v['subminteid'] = $subminteid[$k];
            }
        } else {
            $weipigai = [];
        }

        $search['exercises_homwork_class_relation.work_id'] = $homeworkId;
        $search['exercises_homwork_class_relation.class_id'] = $classId;

        //未提交的 查询的所有的用户呀
        $allStudent = M('exercises_homwork_class_relation')
            ->field('biz_class_student.student_id,auth_student.avatar,student_name')
            ->join('biz_class_teacher ON biz_class_teacher.class_id = exercises_homwork_class_relation.class_id')
            ->join('biz_class ON biz_class.id = exercises_homwork_class_relation.class_id')
            ->join('left join biz_class_student ON biz_class_student.class_id = biz_class.id and biz_class_student.status=2')
            ->join('auth_student ON auth_student.id = biz_class_student.student_id')
            ->group('biz_class_student.student_id')
            ->where($search)
            ->select();
        $res['allStudent'] = $allStudent;
        if (!empty($allStudent)) {
            foreach ($allStudent as $sk => &$sv) {
                if (in_array_case($sv['student_id'], $submitmap)) {
                    unset($allStudent[$sk]);
                } else {
                    if (empty($sv['avatar']) || $sv['avatar'] =='default.jpg') {
                        if ($sv['sex']=="男") {
                            $sv['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_m.png";
                        } else {
                            $sv['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_w.png";
                        }

                    } else {
                        $sv['avatar'] = C('oss_path').$sv['avatar'];
                    }
                }
            }
            $NoSubmitStudent = array_values($allStudent);
        }

        $res['correct_list'] = $yitiajio;
        $res['to_correct_list'] = $weipigai;
        $res['submit_student_count'] = $submitmap;
        $res['no_submit_list'] = $NoSubmitStudent;


        $nextmap['homework_id'] = $homeworkId;
        $nextmap['class_id'] = $classId;
        $nextmap['exercises_student_relation.status'] = 0;
        $nextmap['exercises_student_relation.type'] = 1;
        $nextmap['exercises_student_relation.is_delete'] = 1;

        $nextfield = "exercises_id";

        $result = M('exercises_student_relation')
            ->where($nextmap)
            ->field($nextfield)
            ->group('exercises_id')
            ->order('create_at desc')
            ->find();

        $res['next_exerciseId'] = $result;
        $this->showMessage( 200,'success',$res);
    }

    //学生单题批改情况 已提交 未提交 待批改页面
    public function studentHomeworkCheck()
    {
       $this->homeworkId = I('homeworkId');
       $this->classId = I('classId');
       $this->exerciseId = I('exerciseId');
       $this->display();
    }

    //获取我的试题库班级列表
    public function getMyQuestionBankList() {
        $userId = getParameter('userId','int');
        $condition['biz_class_teacher.teacher_id'] = $userId;
        $condition['exercises_homwork_basics.is_delete'] = 2;
        $condition['biz_class.is_delete'] = 0;
        $result = D('Exercises_homework_class_relation')->getMyQuestionBankList($condition);
        $this->showMessage( 200,'success',$result);
    }

    //试题库章节信息
    public function getMyQuestionMaterialBankList(){
        $userId = getParameter('userId','int');
        $classId = getParameter('classId','int',false);
        $condition['biz_class_teacher.teacher_id'] = $userId;
        $condition['exercises_homwork_class_relation.class_id'] = $classId;
        $condition['exercises_homwork_basics.is_delete'] = 2;
        $condition['biz_class.is_delete'] = 0;
        $result = D('Exercises_homework_class_relation')->getMyQuestionMaterialBankList($condition);

        foreach ($result as $k=>&$v) {
            $map['parent_id'] = $v['chapter_id'];
            $map['level'] = 2;
            $res = M('exercises_textbook_tree_info')->field('id,tree_point_name')->where($map)->select();
            $v['festival_list'] = $res;
        }

        $this->showMessage( 200,'success',$result);
    }

    //我的试题库页面
    // public function myTestLibrary()
    // {
    //    $this->userId = I('userId');
    //    $this->role = I('role');
    //    $this->courseId = I('courseId');
    //    $this->display();
    // }
    //

    //获取错题 学科 班级 年级 错题
    public function getMyErrorList(){
        $userId = getParameter('userId','int');
        $page=I('pageIndex')<1?1:I('pageIndex');
        $pageSize=I('pageIndex')<1?1:I('pageSize');

        $where['exercises_student_relation.is_right'] = 2;
        $where['exercises_student_relation.is_delete'] = 1;
        $where['exercises_student_relation.status'] = 1;
        $where['exercises_homwork_basics.create_user_id'] = $userId;
        $courseId = getParameter('course_id','int',false);
        $classId = getParameter('class_id','int',false);
        $where['exercises_homwork_basics.course_id'] = $courseId;
        $where['exercises_homwork_class_relation.class_id'] = $classId;
       // $where['exercises_homwork_relation.knowledge_type'] = ['neq',1];
        $where['exercises_createexercise.types'] = 1;

        $list = M('exercises_homwork_basics')
            ->join("exercises_homwork_relation ON exercises_homwork_relation.work_id=exercises_homwork_basics.id")
            ->join("exercises_homwork_class_relation ON exercises_homwork_class_relation.work_id=exercises_homwork_basics.id")
            ->join("exercises_student_relation ON exercises_student_relation.homework_id=exercises_homwork_basics.id")
            ->join("left join exercises_createexercise ON exercises_createexercise.id=exercises_student_relation.exercises_id")
            ->join("left join exercises_collection ON exercises_collection.exercises_id=exercises_createexercise.id AND exercises_collection.teacher_id={$userId} AND exercises_collection.role=2")
            ->join("left join dict_grade ON dict_grade.id=exercises_homwork_class_relation.grade_id")
            ->join("left join biz_class ON biz_class.id=exercises_homwork_class_relation.class_id")
            ->where($where)
            ->field("exercises_createexercise.subject,answer_select as answerlist,exercises_createexercise.right_key,exercises_createexercise.class_type,exercises_createexercise.topic_type as is_topic_type,exercises_createexercise.answer,exercises_createexercise.analysis,exercises_createexercise.count_score,exercises_collection.id as eid,exercises_createexercise.id,exercises_createexercise.subject_name,exercises_createexercise.home_topic_type as topic_type,exercises_createexercise.difficulty")
            ->group('exercises_student_relation.exercises_id')
            ->order('exercises_homwork_basics.create_at desc')
            ->page($page, $pageSize)
            ->select();
        //print_r(M()->getLastSql());die();
        foreach ( $list as $k=>&$v) {
            //$list[$k]['subject_name'] = htmlspecialchars_decode($v['subject_name']);
            if (!empty($v['topic_type'])) {
                $topic_name = M('exercises_course')->where("id=".$v['topic_type'])->find();
                $list[$k]['topic_type_name'] = $topic_name['name'];
                $list[$k]['difficulty'] = C('APIdifficulty.'.$v['difficulty']);
            }

            $v['subject_name'] = htmlspecialchars_decode($v["subject_name"]);

            $v['subject_name'] =str_replace('ㄖ','&nbsp;__________&nbsp; ',$v['subject_name']);
            $v['answer'] = json_decode($v["answer"],true);

            if (is_array($v['answer'])) {

                if ($v['is_topic_type']== 1) {
                    foreach ($v['answer'] as $kk=>$vv) {
                        $v['answer'][$kk] = htmlspecialchars_decode($vv);
                    }
                } else {
                    $v['answer'] = htmlspecialchars_decode(implode(",",$v['answer']));
                }
                //
            } else {
                $v['answer'] = htmlspecialchars_decode($v["answer"]);
            }

            $v['analysis'] = htmlspecialchars_decode($v["analysis"]);

            $v['answerList'] = json_decode($v["answerlist"],true);

            if (is_array($v['answerList'])) {
                foreach ($v['answerList'] as $kk=>$vv) {
                    $v['answerList'][$kk] = htmlspecialchars_decode($vv);
                }
            } else {
                $v['answerList'] = htmlspecialchars_decode($v["answerList"]);
            }

        }

        $this->showMessage( 200,'success',$list);
    }

    //我的收藏学科列表
    public function myCollectionSubject()
    {
        $this->userId = I('userId');
        $this->role = I('role');
        $this->display();
    }

    //我的收藏
    public function myCollectionList()
    {
        $this->userId = I('userId');
        $this->role = I('role');
        $this->courseId = I('courseId');
        $this->display();
    }

    //我的收藏试题详情
    public function myCollectionDetails()
    {
        $this->userId = I('userId');
        $this->role = I('role');
        $this->courseId = I('courseId');
        $this->exerciseId = I('exerciseId');
        $this->display();
    }

    //获取我的错误库班级列表
    public function getMyQuestionBankErrorList() {
        $userId = getParameter('userId','int');
        $condition['biz_class_teacher.teacher_id'] = $userId;
        $condition['exercises_homwork_basics.is_delete'] = 2;
        $condition['biz_class.is_delete'] = 0;
        $condition['exercises_student_relation.is_right'] = 2;
        $result = D('Exercises_homework_class_relation')->getMyQuestionBankErrorList($condition);
        $this->showMessage( 200,'success',$result);
    }

    //学生错题库页面
    public function studentHomeworkWrongSubject()
    {

       $this->assign('WEB_URL',WEB_URL);
       $this->userId = I('userId');
       $this->role = I('role');
       $this->courseId = I('courseId');
       $this->display();
    }

    //学生错题库试题列表
    public function studentHomeworkWrongList()
    {
        $this->userId = I('userId');
        $this->role = I('role');
        $this->courseId = I('courseId');
        $this->classId = I('classId');
        $this->chapterId = I('chapterId');
        $this->festivalId = I('festivalId');
        $this->display();
    }

    //学生错题库试题详情
    public function studentHomeworkWrongDetails()
    {
        $this->userId = I('userId');
        $this->role = I('role');
        $this->exerciseId = I('exerciseId');
        $this->display();
    }

    //试题库学生错题章节信息
    public function getMyQuestionMaterialBankErrorList(){
        $userId = getParameter('userId','int');
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

    //获取错题库学科分组
    public function getErrorExerciseGroupCourse(){
        $userId = getParameter('userId','int');
        $where['exercises_student_relation.is_right'] = 2;
        $where['exercises_student_relation.is_delete'] = 1;
        $where['exercises_student_relation.status'] = 1;
        $where['exercises_homwork_basics.create_user_id'] = $userId;
        //$where['exercises_homwork_relation.knowledge_type'] = ['neq',1];
        $where['exercises_createexercise.types'] = EXERCISE_TYPE_NORMAL;
        $where['exercises_createexercise.status'] = EXERCISE_STATE_ONSHELF;
        $where['exercises_createexercise.is_delete'] = STATE_NORMAL;
        $result = M('exercises_homwork_basics')
            ->join("exercises_homwork_relation ON exercises_homwork_relation.work_id=exercises_homwork_basics.id")
            ->join("exercises_student_relation ON exercises_student_relation.homework_id=exercises_homwork_basics.id")
            ->join("left join exercises_createexercise ON exercises_createexercise.id=exercises_student_relation.exercises_id")
            ->join("left join dict_course ON dict_course.id=exercises_homwork_basics.course_id")
            ->where($where)
            ->field("dict_course.course_name,exercises_homwork_basics.course_id as id,count(distinct exercises_student_relation.exercises_id) as count_exercise")
            ->group('exercises_homwork_basics.course_id')
            ->order('dict_course.id desc')
            ->select();
        //print_r(M()->getLastSql());die();

        foreach ($result as $k=>&$v) {
            switch ($v['id']) {
                case 1:
                    $v['url'] = C('oss_path')."public/web_img/APPHomework/v1-3/yuwen.png";
                    break;
                case 2:
                    $v['url'] = C('oss_path')."public/web_img/APPHomework/v1-3/shuxue.png";
                    break;
                case 3:
                    $v['url'] = C('oss_path')."public/web_img/APPHomework/v1-3/yingyu.png";
                    break;
                case 4:
                    $v['url'] = C('oss_path')."public/web_img/App/DirectTrain/wuli@2x.png";
                    break;
                case 5:
                    $v['url'] = C('oss_path')."public/web_img/App/DirectTrain/huaxue@2x.png";
                    break;
                case 6:
                    $v['url'] = C('oss_path')."public/web_img/App/DirectTrain/si@2x.png";
                    break;
                case 7:
                    $v['url'] = C('oss_path')."public/web_img/App/DirectTrain/lishi@2x.png";
                    break;
                case 8:
                    $v['url'] = C('oss_path')."public/web_img/App/DirectTrain/dili@2x.png";
                    break;
                case 9:
                    $v['url'] = C('oss_path')."public/web_img/App/DirectTrain/shengwu@2x.png";
                    break;
                case 11:
                    $v['url'] = C('oss_path')."public/web_img/App/DirectTrain/meishu@2x.png";
                    break;
                case 12:
                    $v['url'] = C('oss_path')."public/web_img/App/DirectTrain/tiyu@2x.png";
                    break;
                case 17:
                    $v['url'] = C('oss_path')."public/web_img/App/DirectTrain/laoji@2x.png";
                    break;
                case 31:
                    $v['url'] = C('oss_path')."public/web_img/App/DirectTrain/kexue@2x.png";
                    break;
                case 37:
                    $v['url'] = C('oss_path')."public/web_img/App/DirectTrain/yinyue@2x.png";
                    break;
            }
        }
        $this->showMessage( 200,'success',$result);
    }

    //获取错误库年级分组
    public function getErrorExerciseGroupGrade(){
        $userId = getParameter('userId','int');
        $where['exercises_student_relation.is_right'] = 2;
        $where['exercises_student_relation.is_delete'] = 1;
        $where['exercises_student_relation.status'] = 1;
        $where['exercises_homwork_basics.create_user_id'] = $userId;
        $where['exercises_homwork_relation.knowledge_type'] = ['neq',1];
        $result = M('exercises_homwork_basics')
            ->join("exercises_homwork_relation ON exercises_homwork_relation.work_id=exercises_homwork_basics.id")
            ->join("exercises_student_relation ON exercises_student_relation.homework_id=exercises_homwork_basics.id")
            ->join("left join dict_grade ON dict_grade.id=exercises_homwork_relation.grade")
            ->where($where)
            ->field("dict_grade.id,dict_grade.grade as name")
            ->group('exercises_homwork_relation.grade')
            ->order('exercises_homwork_basics.create_at desc')
            ->select();
        $this->showMessage( 200,'success',$result);
    }

    //获取错题 学科 班级 年级 错题
    public function getMyErrorExerciseClassGrade(){
        $userId = getParameter('userId','int');
        $where['exercises_student_relation.is_right'] = 2;
        $where['exercises_student_relation.is_delete'] = 1;
        $where['exercises_student_relation.status'] = 1;
        $where['exercises_homwork_basics.create_user_id'] = $userId;
        $courseId = getParameter('courseId','int',false);
        $where['exercises_homwork_basics.course_id'] = $courseId;
        $where['exercises_createexercise.subject'] = $courseId;
        //$where['exercises_homwork_relation.knowledge_type'] = ['neq',1];
        $where['exercises_createexercise.types'] = 1;

        $result = M('exercises_homwork_basics')
            ->join("exercises_homwork_relation ON exercises_homwork_relation.work_id=exercises_homwork_basics.id")
            ->join("exercises_homwork_class_relation ON exercises_homwork_class_relation.work_id=exercises_homwork_basics.id")
            ->join("exercises_student_relation ON exercises_student_relation.homework_id=exercises_homwork_basics.id")
            ->join("left join exercises_createexercise ON exercises_createexercise.id=exercises_student_relation.exercises_id")
            ->join("left join dict_grade ON dict_grade.id=exercises_homwork_class_relation.grade_id")
            ->join("left join biz_class ON biz_class.id=exercises_homwork_class_relation.class_id")
            ->where($where)
            ->field("dict_grade.grade as grade_name,biz_class.name as class_name,exercises_homwork_relation.subject as id,count(distinct exercises_student_relation.exercises_id) as count_exercise,exercises_homwork_class_relation.grade_id,exercises_homwork_class_relation.class_id,group_concat(distinct exercises_student_relation.exercises_id) as exercises_id_list")
            ->group('exercises_homwork_basics.course_id,exercises_homwork_class_relation.grade_id,exercises_homwork_class_relation.class_id')
            ->order('exercises_homwork_basics.create_at desc')
            ->select();
        //print_r(M()->getLastSql());die();
        $this->showMessage( 200,'success',$result);
    }

    //错题库获取班级 年级分组列表
    public function getMyErrorClassGrade(){
        $userId = getParameter('userId','int');
        $where['exercises_student_relation.is_right'] = 2;
        $where['exercises_student_relation.is_delete'] = 1;
        $where['exercises_student_relation.status'] = 1;
        $where['exercises_homwork_basics.create_user_id'] = $userId;
        //$where['exercises_homwork_relation.knowledge_type'] = ['neq',1];
        $where['exercises_createexercise.types'] = 1;

        $result = M('exercises_homwork_basics')
            ->join("exercises_homwork_relation ON exercises_homwork_relation.work_id=exercises_homwork_basics.id")
            ->join("exercises_homwork_class_relation ON exercises_homwork_class_relation.work_id=exercises_homwork_basics.id")
            ->join("exercises_student_relation ON exercises_student_relation.homework_id=exercises_homwork_basics.id")
            ->join("left join exercises_createexercise ON exercises_createexercise.id=exercises_student_relation.exercises_id")
            ->join("left join dict_grade ON dict_grade.id=exercises_homwork_class_relation.grade_id")
            ->join("left join biz_class ON biz_class.id=exercises_homwork_class_relation.class_id")
            ->where($where)
            ->field("dict_grade.grade as grade_name,biz_class.name as class_name,exercises_homwork_relation.subject as id,exercises_homwork_class_relation.grade_id,exercises_homwork_class_relation.class_id")
            ->group('exercises_homwork_class_relation.grade_id,exercises_homwork_class_relation.class_id')
            ->order('exercises_homwork_basics.create_at desc')
            ->select();
        $this->showMessage( 200,'success',$result);
    }

    //根据学科id获取我的题型
    public function getExerciseType() {
        $id = I('id');
        if(0 == $id){
            $this->ajaxReturn(array());
        }
        if(empty($id)  || is_numeric($id)==false ) {
            die('参数错误');
        }
        $list = D('Exercises_Course')->getCourseList($id);
        $this->showMessage( 200,'success',$list);
    }

    //我的习题库用的接口 根据学科 班级 章节 获取习题列表
    public function getMyExerciseBankList (){
        $userId = getParameter('userId','int');
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
            $setmap['exercises_id'] = $val['id'];
            $setmap['teacher_id'] = $userId;
            $setmap['role'] = 2;
            $info = M('exercises_collection')->where($setmap)->find();
            if (!empty($info)) {
                $result[$key]['eid'] = $info['id'];
            } else {
                $result[$key]['eid'] = null;
            }
        }
        $this->showMessage( 200,'success',$result);
    }

    //获取我的收藏
    public function getMyExerciseCollectionList() {
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $courseId = getParameter('courseId','int',false);

        $map['exercises_collection.teacher_id'] = $userId;
        $map['exercises_collection.role'] = $role;

        $page=I('pageIndex')<1?1:I('pageIndex');
        $pageSize=I('pageIndex')<1?1:I('pageSize');

        if (!empty($courseId))
        $map['exercises_createexercise.subject'] = $courseId;
        $map['exercises_createexercise.topic_type'] = ['neq',4];

        if(empty(session('teacher.id'))) {
            $result = M('exercises_collection')
                ->order('exercises_collection.id desc')
                ->field("exercises_createexercise.count_score as score,topic_type as is_topic_type,answer_select as answerlist,exercises_createexercise.*,exercises_collection.id as eid,exercises_createexercise.home_topic_type as topic_type")
                ->join("exercises_createexercise ON exercises_createexercise.id=exercises_collection.exercises_id")
                ->where($map)
                ->select();
        }
        else{
            $result = M('exercises_collection')
                ->order('exercises_collection.id desc')
                ->field("exercises_createexercise.count_score as score,topic_type as is_topic_type,answer_select as answerlist,exercises_createexercise.*,exercises_collection.id as eid,exercises_createexercise.home_topic_type as topic_type")
                ->join("exercises_createexercise ON exercises_createexercise.id=exercises_collection.exercises_id")
                ->where($map)
                ->page($page, $pageSize)
                ->select();
        }
        foreach($result as $key=>&$val) {
            $isMobile = isMobile();
            if ($isMobile== true) {
                $val['score'] = $val['count_score'];
            }

            $result[$key]['search_name'] = htmlspecialchars_decode($val['search_name']);
            $result[$key]['subject_name'] = htmlspecialchars_decode($val['subject_name']);
            $result[$key]['subject_name'] =str_replace('ㄖ','&nbsp;__________&nbsp; ',$val['subject_name']);
            $result[$key]['analysis'] = htmlspecialchars_decode($val['analysis']);
            $result[$key]['answer'] = json_decode($val["answer"],true);

            if (!empty($val['topic_type'])) {
                $topic_name = M('exercises_course')->where("id=".$val['topic_type'])->find();
                $result[$key]['topic_type_name'] = $topic_name['name'];
                $result[$key]['difficulty'] = C('APIdifficulty.'.$val['difficulty']);
            }

            if (is_array($val['answer'])) {
                foreach ($val['answer'] as $kk=>$vv) {
                    $val['answer'][$kk] = htmlspecialchars_decode($vv);
                }
                //$val['answer'] = implode(",",$val['answer']);
            } else {
                $val['answer'] = htmlspecialchars_decode($val["answer"]);
            }

            $val['analysis'] = htmlspecialchars_decode($val["analysis"]);

            $val['answerList'] = json_decode($val["answerlist"],true);

            if (is_array($val['answerList'])) {
                foreach ($val['answerList'] as $kk=>$vv) {
                    $val['answerList'][$kk] = htmlspecialchars_decode($vv);
                }
            } else {
                $val['answerList'] = htmlspecialchars_decode($val["answerList"]);
            }

        }


        $this->showMessage( 200,'success',$result);
    }

    //获取我的收藏学科分组
    public function getMyExerciseCollectionCourseGroup() {
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $map['exercises_collection.teacher_id'] = $userId;
        $map['exercises_collection.role'] = $role;

        $result = M('exercises_collection')->field('dict_course.id,dict_course.course_name,count(exercises_collection.exercises_id) as exercises_id_count,group_concat(exercises_id) as exercises_id_list')
            ->join('exercises_createexercise ON exercises_createexercise.id=exercises_collection.exercises_id')
            ->join('dict_course ON dict_course.id=exercises_createexercise.subject')
            ->group('exercises_createexercise.subject')
            ->where($map)
            ->select();

        foreach ($result as $k=>&$v) {
            switch ($v['id']) {
                case 1:
                    $v['url'] = C('oss_path')."public/web_img/APPHomework/v1-3/yuwen.png";
                    break;
                case 2:
                    $v['url'] = C('oss_path')."public/web_img/APPHomework/v1-3/shuxue.png";
                    break;
                case 3:
                    $v['url'] = C('oss_path')."public/web_img/APPHomework/v1-3/yingyu.png";
                    break;
            }
        }

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
        if (!empty($bigList)) {
            foreach ($bigList as $k=>$v) {
                if (!empty($v['id'])) {
                    $data['big_question_id'] = $v['id'];
                    $childquestions = D('Exercises_parper_concat')->getQuestionBigPaper($data); //关联试卷试题的关系
                    if (!empty($childquestions)) {

                        foreach($childquestions as $key=>&$val) {
                            $val['search_name'] = htmlspecialchars_decode($val['search_name']);
                            $val['subject_name'] = htmlspecialchars_decode($val['subject_name']);
                            $val['subject_name'] =str_replace('ㄖ','&nbsp;__________&nbsp; ',$val['subject_name']);
                            $val['analysis'] = htmlspecialchars_decode($val['analysis']);
                            $val['answer'] = json_decode($val["answer"],true);
                            $val['json_html'] = htmlspecialchars_decode(json_decode($val["json_html"],true));

                            if (!empty($val['topic_type'])) {
                                $topic_name = M('exercises_course')->where("id=".$val['topic_type'])->find();
                                $val['topic_type_name'] = $topic_name['name'];
                                $val['difficulty'] = C('APIdifficulty.'.$val['difficulty']);
                            }

                            if (is_array($val['answer'])) {
                                foreach ($val['answer'] as $kk=>$vv) {
                                    $val['answer'][$kk] = htmlspecialchars_decode($vv);
                                }
                                //$val['answer'] = implode(",",$val['answer']);
                            } else {
                                $val['answer'] = htmlspecialchars_decode($val["answer"]);
                            }

                            $val['analysis'] = htmlspecialchars_decode($val["analysis"]);

                            $val['answerList'] = json_decode($val["answerlist"],true);

                            if (is_array($val['answerList'])) {
                                foreach ($val['answerList'] as $kk=>$vv) {
                                    $val['answerList'][$kk] = htmlspecialchars_decode($vv);
                                }
                            } else {
                                $val['answerList'] = htmlspecialchars_decode($val["answerList"]);
                            }

                        }

                        $bigList[$k]['childquestions'] = $childquestions;
                    }
                }
            }
        }
        $paperinfo["exercise_list"] = $bigList;
        $this->showMessage( 200,'success',$paperinfo);
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

        //$data['exercise_list'] =$bigList;
        $this->showMessage( 200,'success',$bigList);
    }

    //语音教材获取
    public function getVoiceGradeAndF() {
        $f = getParameter('version_id','int');//版本
        $courseId = getParameter('courseId','int');
        $map['exercises_textbook_tree_info_createexercise.version_id'] = $f;
        $map['exercises_textbook_tree_info_createexercise.course_id'] = $courseId;//学科
        $map['exercises_createexercise.status'] = 110;//学科
        $map['exercises_createexercise.is_delete'] = 2;//学科
        $map['exercises_createexercise.types'] = 2;//学科
        $list = M('exercises_createexercise')
            ->field('exercises_textbook_tree_info_createexercise.grade_id,exercises_textbook_tree_info_createexercise.grade_name as grade,exercises_textbook_tree_info_createexercise.section_id as school_term,exercises_textbook_tree_info_createexercise.section_name as term_name')
            ->join('exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id = exercises_createexercise.id')
            ->where($map)
            ->group('exercises_textbook_tree_info_createexercise.grade_id,exercises_textbook_tree_info_createexercise.section_id')
            ->select();

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

            $Zmap['exercises_textbook_tree_info_createexercise.version_id'] = $f;
            $Zmap['exercises_textbook_tree_info_createexercise.course_id'] = $courseId;//学科
            $Zmap['exercises_createexercise.status'] = 110;//学科
            $Zmap['exercises_createexercise.is_delete'] = 2;//学科
            $Zmap['exercises_createexercise.types'] = 2;//学科

            $Zmap['exercises_textbook_tree_info_createexercise.grade_id'] = $v['grade_id'];//年级
            $Zmap['exercises_textbook_tree_info_createexercise.section_id'] = $v['school_term'];//分册

            $z = M('exercises_createexercise')
                ->field('exercises_textbook_tree_info.id as chapter_id,exercises_textbook_tree_info.tree_point_name')
                ->join('exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id = exercises_createexercise.id')
                ->join('left join exercises_textbook_tree_info ON exercises_textbook_tree_info.id = exercises_textbook_tree_info_createexercise.chapter')
                ->where($Zmap)
                ->group('exercises_textbook_tree_info_createexercise.chapter')
                ->select();
            $v['chapter_list'] = $z;

            if (!empty($z)) {
                foreach ($z as $ck=>&$cv) {
                    $Smap['exercises_textbook_tree_info_createexercise.version_id'] = $f;
                    $Smap['exercises_textbook_tree_info_createexercise.course_id'] = $courseId;//学科
                    $Smap['exercises_createexercise.status'] = 110;//学科
                    $Smap['exercises_createexercise.is_delete'] = 2;//学科
                    $Smap['exercises_createexercise.types'] = 2;//学科

                    $Smap['exercises_textbook_tree_info_createexercise.grade_id'] = $v['grade_id'];//年级
                    $Smap['exercises_textbook_tree_info_createexercise.section_id'] = $v['school_term'];//分册
                    $Smap['exercises_textbook_tree_info_createexercise.chapter'] = $cv['chapter_id'];//分册

                    $S = M('exercises_createexercise')
                        ->field('exercises_textbook_tree_info.id as section_id,exercises_textbook_tree_info.tree_point_name')
                        ->join('exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id = exercises_createexercise.id')
                        ->join('left join exercises_textbook_tree_info ON exercises_textbook_tree_info.id = exercises_textbook_tree_info_createexercise.festival')
                        ->where($Smap)
                        ->group('exercises_textbook_tree_info_createexercise.festival')
                        ->select();
                    $v['chapter_list'][$ck]['section_list'] = $S;
                }
            }
        }

        $this->showMessage( 200,'success',$list);
    }

    //根据学科和教材版本获取教材同步知识点的年级和分册

    public function getGradeAndF() {
        $f = getParameter('version_id','int');//版本
        $courseId = getParameter('courseId','int');
        $map['exercises_textbook_tree_info_createexercise.version_id'] = $f;
        $map['exercises_textbook_tree_info_createexercise.course_id'] = $courseId;//学科
        $map['exercises_createexercise.status'] = 110;//学科
        $map['exercises_createexercise.is_delete'] = 2;//学科
        $map['exercises_createexercise.types'] = 1;//学科
        $list = M('exercises_createexercise')
            ->field('exercises_textbook_tree_info_createexercise.grade_id,exercises_textbook_tree_info_createexercise.grade_name as grade,exercises_textbook_tree_info_createexercise.section_id as school_term,exercises_textbook_tree_info_createexercise.section_name as term_name')
            ->join('exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id = exercises_createexercise.id')
            ->where($map)
            ->group('exercises_textbook_tree_info_createexercise.grade_id,exercises_textbook_tree_info_createexercise.section_id')
            ->select();

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

            $Zmap['exercises_textbook_tree_info_createexercise.version_id'] = $f;
            $Zmap['exercises_textbook_tree_info_createexercise.course_id'] = $courseId;//学科
            $Zmap['exercises_createexercise.status'] = 110;//学科
            $Zmap['exercises_createexercise.is_delete'] = 2;//学科
            $Zmap['exercises_createexercise.types'] = 1;//学科

            $Zmap['exercises_textbook_tree_info_createexercise.grade_id'] = $v['grade_id'];//年级
            $Zmap['exercises_textbook_tree_info_createexercise.section_id'] = $v['school_term'];//分册
            $Zmap['exercises_textbook_tree_info.level']= 1;

            $z = M('exercises_createexercise')
                ->field('exercises_textbook_tree_info.id as chapter_id,exercises_textbook_tree_info.tree_point_name')
                ->join('exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id = exercises_createexercise.id')
                ->join('left join exercises_textbook_tree_info ON exercises_textbook_tree_info.id = exercises_textbook_tree_info_createexercise.chapter')
                ->where($Zmap)
                ->group('exercises_textbook_tree_info_createexercise.chapter')
                ->select();

            $v['chapter_list'] = $z;

            if (!empty($z)) {
                foreach ($z as $ck=>&$cv) {
                    $Smap['exercises_textbook_tree_info_createexercise.version_id'] = $f;
                    $Smap['exercises_textbook_tree_info_createexercise.course_id'] = $courseId;//学科
                    $Smap['exercises_createexercise.status'] = 110;//学科
                    $Smap['exercises_createexercise.is_delete'] = 2;//学科
                    $Smap['exercises_createexercise.types'] = 1;//学科

                    $Smap['exercises_textbook_tree_info_createexercise.grade_id'] = $v['grade_id'];//年级
                    $Smap['exercises_textbook_tree_info_createexercise.section_id'] = $v['school_term'];//分册
                    $Smap['exercises_textbook_tree_info_createexercise.chapter'] = $cv['chapter_id'];//分册
                    $Smap['exercises_textbook_tree_info.level'] = 2;//分册

                    $S = M('exercises_createexercise')
                        ->field('exercises_textbook_tree_info.id as section_id,exercises_textbook_tree_info.tree_point_name')
                        ->join('exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id = exercises_createexercise.id')
                        ->join('left join exercises_textbook_tree_info ON exercises_textbook_tree_info.id = exercises_textbook_tree_info_createexercise.festival')
                        ->where($Smap)
                        ->group('exercises_textbook_tree_info_createexercise.festival')
                        ->select();
                    if(!empty($S)) {
                        $v['chapter_list'][$ck]['section_list'] = $S;
                    } else {
                        unset($v['chapter_list'][$ck]);
                        $v['chapter_list'] = array_values($v['chapter_list']);
                    }
                }
            }
        }

        $this->showMessage( 200,'success',$list);
    }

    //根据章id查询所有小节
    public function getSectionList() {
        $chapter_id = getParameter('chapter_id','int');//版本
        $Smap['exercises_textbook_tree_info.parent_id']=$chapter_id;
        $Smap['exercises_textbook_tree_info.level']=2;
        //$list  = M('exercises_textbook_tree_info')->field('id as section_id,tree_point_name')->where($Smap)->select();//查询节的

        $list = M('exercises_createexercise')
            ->field('exercises_textbook_tree_info.id as section_id,exercises_textbook_tree_info.tree_point_name')
            ->join('exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id = exercises_createexercise.id')
            ->join('left join exercises_textbook_tree_info ON exercises_textbook_tree_info.id = exercises_textbook_tree_info_createexercise.festival')
            ->where($Smap)
            ->group('exercises_textbook_tree_info_createexercise.festival')
            ->select();

        $this->showMessage( 200,'success',$list);
    }

    //获取版本和学科
    public function getVersionAndCourseList() {
        $map['exercises_createexercise.status'] = 110;
        $map['exercises_createexercise.is_delete'] = 2;
        //elt
        $map['exercises_textbook_tree_info_createexercise.course_id'] = array('elt',3);

        $courselist = M('exercises_createexercise')
            ->join('exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id=exercises_createexercise.id')
            ->field('exercises_textbook_tree_info_createexercise.course_id as id,exercises_textbook_tree_info_createexercise.course_name as name')
            ->group('exercises_textbook_tree_info_createexercise.course_id')
            ->where($map)
            ->select();

        $versionlist = M('exercises_createexercise')
            ->join('exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id=exercises_createexercise.id')
            ->join('exercises_textbook_version ON exercises_textbook_version.id=exercises_textbook_tree_info_createexercise.version_id')
            ->field('exercises_textbook_tree_info_createexercise.version_id as id,exercises_textbook_version.version_name as name')
            ->group('exercises_textbook_tree_info_createexercise.version_id')
            ->where($map)
            ->select();
        
        foreach ($courselist as $k=>&$v) {
            switch ($v['id']) {
                case 1:
                    $v['url'] = "http://".WEB_URL."/Public/img/Apphomework/zyyuwen@2x.png";
                    break;
                case 2:
                    $v['url'] = "http://".WEB_URL."/Public/img/Apphomework/shuxue@2x.png";
                    break;
                case 3:
                    $v['url'] = "http://".WEB_URL."/Public/img/Apphomework/yingyu@2x.png";
                    break;
                case 4:
                    $v['url'] = "http://".WEB_URL."/Public/img/Apphomework/wuli@2x.png";
                    break;
                case 5:
                    $v['url'] = "http://".WEB_URL."/Public/img/Apphomework/huaxue@2x.png";
                    break;
                case 6:
                    $v['url'] = "http://".WEB_URL."/Public/img/Apphomework/zyyuwen@2x.png";
                    break;
                case 7:
                    $v['url'] = "http://".WEB_URL."/Public/img/Apphomework/zyyuwen@2x.png";
                    break;
                case 8:
                    $v['url'] = "http://".WEB_URL."/Public/img/Apphomework/zyyuwen@2x.png";
                    break;
                case 9:
                    $v['url'] = "http://".WEB_URL."/Public/img/Apphomework/shengwu@2x.png";
                    break;
                case 11:
                    $v['url'] = "http://".WEB_URL."/Public/img/Apphomework/zyyuwen@2x.png";
                    break;
                case 12:
                    $v['url'] = "http://".WEB_URL."/Public/img/Apphomework/zyyuwen@2x.png";
                    break;
                case 17:
                    $v['url'] = "http://".WEB_URL."/Public/img/Apphomework/zyyuwen@2x.png";
                    break;
                case 31:
                    $v['url'] = "http://".WEB_URL."/Public/img/Apphomework/zyyuwen@2x.png";
                    break;
                case 37:
                    $v['url'] = "http://".WEB_URL."/Public/img/Apphomework/zyyuwen@2x.png";
                    break;
            }
        }
        $data['course_list'] = $courselist;
        $data['version_list'] = $versionlist;
        $this->showMessage( 200,'success',$data);
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

                $res[$k]['child_curriculem_list'] = $reslist;
            }
            $this->showMessage( 200,'success',$res);
        }

    }

    //根据试卷获取知识点
    public function getPaperTaskKnowledgeName(){
        $paperId = getParameter('paperId','int');

        $paperDetails = M('exercises_create_paper')->where("id=".$paperId)->find();

        $paperList = M('exercises_parper_concat')->where("paper_id=".$paperId)->select();
        $exerciseArr = [];
        foreach ($paperList as $k=>$v) {
            $exerciseArr[] = $v['exercise_id'];
        }

        if (!empty($exerciseArr)) {
            $ids = implode(",",$exerciseArr);
            $courseId = $paperDetails['subject'];
            if ($courseId ==2) {
                $map['exercises_createexercise.id'] = array('in',$ids);
                $map['exercises_curriculum_tree_info.level'] = 2;
                $list = M('exercises_createexercise')
                    ->join("exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id=exercises_createexercise.id")
                    ->join("exercises_textbook_tree_curriculum_tree ON exercises_textbook_tree_curriculum_tree.textbook_tree_info_id=exercises_textbook_tree_info_createexercise.knowledge_id")
                    ->join("exercises_curriculum_tree_info ON exercises_curriculum_tree_info.id=exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id")
                    ->group("exercises_curriculum_tree_info.id")
                    ->field("count(exercises_createexercise.id) as exercise_id_count,exercises_curriculum_tree_info.id,exercises_curriculum_tree_info.tree_point_name as name")
                    ->order("exercise_id_count desc")
                    ->where($map)
                    ->find();
                $list['name'] = $paperDetails['paper_name'];
                $this->showMessage( 200,'success',$list);
            } else {
                $map['exercises_createexercise.id'] = array('in',$ids);
                $map['exercises_curriculum_tree_info.level'] = 2;
                $list = M('exercises_createexercise')
                    ->join("exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id=exercises_createexercise.id")
                    ->join("exercises_textbook_tree_curriculum_tree ON exercises_textbook_tree_curriculum_tree.textbook_tree_info_id=exercises_textbook_tree_info_createexercise.knowledge_id")
                    ->join("exercises_curriculum_tree_info ON exercises_curriculum_tree_info.id=exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id")
                    ->group("exercises_curriculum_tree_info.id")
                    ->field("count(exercises_createexercise.id) as exercise_id_count,exercises_curriculum_tree_info.id,exercises_curriculum_tree_info.tree_point_name as name")
                    ->order("exercise_id_count desc")
                    ->where($map)
                    ->find();
                unset($map['exercises_curriculum_tree_info.level']);
                $flist = M('exercises_createexercise')
                    ->join("exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id=exercises_createexercise.id")
                    ->group("exercises_textbook_tree_info_createexercise.festival")
                    ->field("count(exercises_createexercise.id) as exercise_id_count,exercises_textbook_tree_info_createexercise.festival")
                    ->order("exercise_id_count desc")
                    ->where($map)
                    ->find();
                if (!empty($flist)) {
                    $childmap['id'] = $flist['festival'];
                    $childmap['level'] = 2;
                    $fdata = M('exercises_textbook_tree_info')->where($childmap)->find();
                }

                $list['name'] = $paperDetails['paper_name'];
                $list['fname'] = "";
                $this->showMessage( 200,'success',$list);
            }

        }
    }


    //根据学科习题id列表获取作业名称和知识点id
    public function getTaskKnowledgeName() {
        $ids = getParameter('id','str');
        $courseId = getParameter('courseId','int');

        if ($courseId ==2) {
            $map['exercises_createexercise.id'] = array('in',$ids);
            $map['exercises_curriculum_tree_info.level'] = 2;
            $list = M('exercises_createexercise')
                ->join("exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id=exercises_createexercise.id")
                ->join("exercises_textbook_tree_curriculum_tree ON exercises_textbook_tree_curriculum_tree.textbook_tree_info_id=exercises_textbook_tree_info_createexercise.knowledge_id")
                ->join("exercises_curriculum_tree_info ON exercises_curriculum_tree_info.id=exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id")
                ->group("exercises_curriculum_tree_info.id")
                ->field("count(exercises_createexercise.id) as exercise_id_count,exercises_curriculum_tree_info.id,exercises_curriculum_tree_info.tree_point_name as name")
                ->order("exercise_id_count desc")
                ->where($map)
                ->find();

            $this->showMessage( 200,'success',$list);
        } else {
            $map['exercises_createexercise.id'] = array('in',$ids);
            $map['exercises_curriculum_tree_info.level'] = 2;
            $list = M('exercises_createexercise')
                ->join("exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id=exercises_createexercise.id")
                ->join("exercises_textbook_tree_curriculum_tree ON exercises_textbook_tree_curriculum_tree.textbook_tree_info_id=exercises_textbook_tree_info_createexercise.knowledge_id")
                ->join("exercises_curriculum_tree_info ON exercises_curriculum_tree_info.id=exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id")
                ->group("exercises_curriculum_tree_info.id")
                ->field("count(exercises_createexercise.id) as exercise_id_count,exercises_curriculum_tree_info.id,exercises_curriculum_tree_info.tree_point_name as name")
                ->order("exercise_id_count desc")
                ->where($map)
                ->find();
            unset($map['exercises_curriculum_tree_info.level']);
            $flist = M('exercises_createexercise')
                ->join("exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id=exercises_createexercise.id")
                ->group("exercises_textbook_tree_info_createexercise.festival")
                ->field("count(exercises_createexercise.id) as exercise_id_count,exercises_textbook_tree_info_createexercise.festival")
                ->order("exercise_id_count desc")
                ->where($map)
                ->find();
            if (!empty($flist)) {
                $childmap['id'] = $flist['festival'];
                $childmap['level'] = 2;
                $fdata = M('exercises_textbook_tree_info')->where($childmap)->find();
            }
            $list['fname'] = $fdata['tree_point_name'];
            $this->showMessage( 200,'success',$list);
        }
    }

    //布置作业获取单元信息
    public function doHomeWorkKnowledgeName(){
        $paperId = getParameter('paperId','int',false);
        if(!empty($paperId)) {
            $paperList = M('exercises_parper_concat')->where("paper_id=".$paperId)->select();
            $exerciseArr = [];
            foreach ($paperList as $k=>$v) {
                $exerciseArr[] = $v['exercise_id'];
            }
            $ids = implode(",",$exerciseArr);
        } else {
            $ids = getParameter('id','str',false);
        }

        $map['exercises_createexercise.id'] = array('in',$ids);
        $flist = M('exercises_createexercise')
            ->join("exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id=exercises_createexercise.id")
            ->group("exercises_textbook_tree_info_createexercise.chapter")
            ->field("count(exercises_createexercise.id) as exercise_id_count,exercises_textbook_tree_info_createexercise.chapter")
            ->order("exercise_id_count desc")
            ->where($map)
            ->find();
        if (!empty($flist)) {
            $childmap['id'] = $flist['chapter'];
            $childmap['level'] = 1;
            $fdata = M('exercises_textbook_tree_info')->where($childmap)->find();
            $flist['fname'] = $fdata['tree_point_name'];
        }

        $list = M('exercises_createexercise')
            ->join("exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id=exercises_createexercise.id")
            ->group("exercises_textbook_tree_info_createexercise.chapter")
            ->field("count(exercises_createexercise.id) as exercise_id_count,exercises_textbook_tree_info_createexercise.chapter")
            ->order("exercise_id_count desc")
            ->where($map)
            ->select();
        foreach ($list as $k=>$v) {
            $where['id'] = $v['chapter'];
            $where['level'] = 1;
            $wdata = M('exercises_textbook_tree_info')->where($where)->find();
            $list[$k]['fname'] = $wdata['tree_point_name'];
        }

        $data['setOrder'] = $flist;

        $stylelist = array(
            array('chapter'=>-1,'fname'=>'期中'),
            array('chapter'=>-2,'fname'=>'期末'),
        );
        $allList=[];
        if (!empty($list)) {
            $allList = array_merge($stylelist,$list);
        } else {
            $allList = $stylelist;
        }

        $data['list'] = $allList;

        $this->showMessage( 200,'success',$data);
    }
	//作业列表
	public function homeworkList(){
		$this->userId = I('userId');
		$this->role= I('role');
		$this->display("homeworkList");
	}
    //根据一级知识点查询二级知识点
    public function getTwoCurricuList() {
        $id = getParameter('two_id','int');
        $cMap['parent_id'] = $id;
        $cMap['level'] = 2;
        //$reslist = M('exercises_curriculum_tree_info')->where($cMap)->select();
        $reslist = M('exercises_curriculum_tree_info')
            ->join("exercises_textbook_tree_curriculum_tree ON exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id=exercises_curriculum_tree_info.id")
            ->join("exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.knowledge_id=exercises_textbook_tree_curriculum_tree.textbook_tree_info_id")
            ->where($cMap)
            ->field("exercises_curriculum_tree_info.*")
            ->group('exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id')
            ->select();
        $this->showMessage( 200,'success',$reslist);
    }

    //习题库获取习题列表 全部习题用的接口 根据搜索条件搜索试题
    public function setSearchgetExerciseList() {
        $version_id = getParameter('version_id','int'); //版本
        $courseId = getParameter('course_id','int',false);//学科
        $grade_id = getParameter('grade_id','int',false);//年级
        $school_term = getParameter('school_term','int',false);//分册
        $chapter_id = getParameter('chapter_id','int',false);//章
        $section_id = getParameter('section_id','int',false);//节
        $knowledge_id = getParameter('knowledge_id','int',false);//节

        $class_id = getParameter('class_id','int',false);//节

        $userId = getParameter('userId','int',false);
        $page=I('pageIndex')<1?1:I('pageIndex');
        $isMobile = isMobile();
        if ($isMobile) {
            $pageSize=1000;
        } else {
            $pageSize=I('pageIndex')<1?1:I('pageSize');
        }

        $type_id = getParameter('type_id','int',false);//题型
        $difficulty_id = getParameter('difficulty_id','int',false);//难度
        $status = getParameter('status','int',false);//状态 1 已收藏 2 曾出过 3 未出过 4 学生错题

        if (!empty($type_id)) {
            $where['exercises_createexercise.home_topic_type'] = $type_id;
            $where['exercises_createexercise.home_topic_type'] = $type_id;
        }

        if(empty($userId)) {
            $userId = 0;
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
                $where['exercises_homwork_basics.create_user_id'] = $userId;
                $where['exercises_student_relation.is_right'] = 2;
                $where['exercises_student_relation.is_delete'] = 1;
                $where['exercises_student_relation.status'] = 1;
                $where['exercises_student_relation.teacher_id'] = $userId;
            }
        }

        if (!empty($version_id)) {
            $where['exercises_textbook_tree_info_createexercise.version_id'] = $version_id;
        }

        if (!empty($courseId) && $courseId != "undefined") {
            $where['exercises_textbook_tree_info_createexercise.course_id'] = $courseId;
        }

        if (!empty($grade_id)) {
            if (!empty($class_id)) { //根据班级和年级搜索
                $where['exercises_homwork_class_relation.class_id'] = $class_id;
            } else {
                $where['exercises_textbook_tree_info_createexercise.grade_id'] = $grade_id;
            }

        }

        if (!empty($school_term)) {
            $where['exercises_textbook_tree_info_createexercise.section_id'] = $school_term;
        }

        if(!empty($chapter_id) && !empty($section_id)) {
            $where['exercises_textbook_tree_info_createexercise.chapter'] = $chapter_id;
            $where['exercises_textbook_tree_info_createexercise.festival'] = $section_id;
        }
        if (!empty($knowledge_id)) {
            $where['exercises_textbook_tree_curriculum_tree.curriculum_tree_info_id'] = $knowledge_id;
        }

        $where['exercises_createexercise.types'] = ['neq',2];
        $where['exercises_createexercise.is_delete'] = 2;
        $where['exercises_createexercise.status'] = 110;
        $where['exercises_createexercise.topic_type'] = ['neq',4]; //过滤连线题

        $list = M('exercises_createexercise')
            ->join("exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercises_createexercise_id = exercises_createexercise.id")
            ->join("left join exercises_collection ON exercises_collection.exercises_id=exercises_createexercise.id AND exercises_collection.teacher_id={$userId} AND exercises_collection.role=2")
            ->join("left join exercises_textbook_tree_curriculum_tree ON exercises_textbook_tree_curriculum_tree.textbook_tree_info_id=exercises_textbook_tree_info_createexercise.knowledge_id")
            ->join("left join exercises_homwork_relation ON exercises_homwork_relation.exercises_id=exercises_createexercise.id")
            ->join("left join exercises_homwork_basics ON exercises_homwork_basics.id=exercises_homwork_relation.work_id")
            ->join("left join exercises_homwork_class_relation ON exercises_homwork_class_relation.work_id=exercises_homwork_basics.id")
            ->join("left join exercises_student_relation ON exercises_student_relation.exercises_id=exercises_createexercise.id")
            ->where($where)
            ->field("exercises_createexercise.subject,answer_select as answerlist,exercises_createexercise.right_key,exercises_createexercise.class_type,exercises_createexercise.topic_type as is_topic_type,exercises_createexercise.answer,exercises_createexercise.analysis,exercises_createexercise.count_score,exercises_collection.id as eid,exercises_createexercise.id,exercises_createexercise.subject_name,exercises_createexercise.home_topic_type as topic_type,exercises_createexercise.difficulty")
            ->page($page, $pageSize)
            ->group('exercises_createexercise.id')
            ->select();
        //print_r(M()->getLastSql());die();
        foreach ( $list as $k=>&$v) {
            $isMobile = isMobile();
            if ($isMobile== true) {
                $v['score'] = $v['count_score'];
            }
            //$list[$k]['subject_name'] = htmlspecialchars_decode($v['subject_name']);
            if (!empty($v['topic_type'])) {
                $topic_name = M('exercises_course')->where("id=".$v['topic_type'])->find();
                $list[$k]['topic_type_name'] = $topic_name['name'];
                $list[$k]['difficulty'] = C('APIdifficulty.'.$v['difficulty']);
            }

            $v['subject_name'] = htmlspecialchars_decode($v["subject_name"]);

            $v['subject_name'] =str_replace('ㄖ','&nbsp;__________&nbsp; ',$v['subject_name']);
            $v['answer'] = json_decode($v["answer"],true);

            if (is_array($v['answer'])) {

                if ($v['is_topic_type']== 1) {
                    foreach ($v['answer'] as $kk=>$vv) {
                        $v['answer'][$kk] = htmlspecialchars_decode($vv);
                    }
                } else {
                    $v['answer'] = htmlspecialchars_decode(implode(",",$v['answer']));
                }
                //
            } else {
                $v['answer'] = htmlspecialchars_decode($v["answer"]);
            }

            $v['analysis'] = htmlspecialchars_decode($v["analysis"]);

            $v['answerList'] = json_decode($v["answerlist"],true);

            if (is_array($v['answerList'])) {
                foreach ($v['answerList'] as $kk=>$vv) {
                    $v['answerList'][$kk] = htmlspecialchars_decode($vv);
                }
            } else {
                $v['answerList'] = htmlspecialchars_decode($v["answerList"]);
            }

        }

        $this->showMessage( 200,'success',$list);
    }

    //习题库列表
    public function exerciseList()
    {
        $this->userId = I('userId');
        $this->role = I('role');
        $this->versionId = I('versionId');
        $this->courseId = I('courseId');
        $this->gradeId = I('gradeId');
        $this->schoolTerm = I('schoolTerm');
        $this->classId = I('classId');
        $this->chapterId = I('chapterId');
        $this->festivalId = I('festivalId');
        $this->parentId = I('parentId');
        $this->knowledgeId = I('knowledge_id');
        header("Expires: Mon, 09 Jul 2028 01:58:17 GMT");
        header("Cache-Control: max-age=2592000");
        $this->display();

    }

    //习题库列表布置组卷
    public function exerciseListPublish()
    {
        $this->userId = I('userId');
        $this->role = I('role');
        $this->versionId = I('versionId');
        $this->courseId = I('courseId');
        $this->gradeId = I('gradeId');
        $this->schoolTerm = I('schoolTerm');
        $this->classId = I('classId');
        $this->chapterId = I('chapterId');
        $this->festivalId = I('festivalId');
        $this->parentId = I('parentId');
        $this->knowledgeId = I('knowledgeId');
        $this->sectionId =I('sectionId');
        $this->display();
    }
    public function paperDetails()
    {
        $this->userId = I('userId');
        $this->id = I('id');
        $this->display();
    }
    //习题库单个详情
    public function exerciseDetails()
    {
        $this->versionId = I('versionId');
        $this->courseId = I('courseId');
        $this->gradeId = I('gradeId');
        $this->schoolTerm = I('schoolTerm');
        $this->userId = I('userId');
        $this->classId = I('classId');
        $this->chapterId = I('chapterId');
        $this->festivalId = I('festivalId');
        $this->knowledgeId = I('knowledgeId');
        $this->role = I('role');
        $this->exerciseId = I('exerciseId');
        $this->display();
    }

    //进行习题的收藏
    public function IWantToCollect(){
        $iscancel = getParameter('iscancel','int',false);
        $exercise_id = getParameter('exercise_id','int'); //习题id
        $courseId = getParameter('course_id','int',false);//学科
        $grade_id = getParameter('grade_id','int',false);//年级
        $school_term = getParameter('school_term','int',false);//分册
        //$chapter_id = getParameter('chapter_id','int');//章
        $section_id = getParameter('section_id','int',false);//节
        $Knowledge_id = getParameter('Knowledge_id','int',false); //知识点
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');

        $map['exercises_id'] = $exercise_id;
        $map['teacher_id'] = $userId;
        $map['role'] = $role;

        if ($iscancel == 1) {
            $id = M('exercises_collection')->where($map)->delete();
            if ($id !== false) {
                $this->showMessage( 200,'success','取消成功');
            } else {
                $this->showMessage( 400,'success','取消失败');
            }
        } else {
            $map['subject'] = $courseId;
            $map['grade'] = $grade_id;
            $map['section'] = $school_term;
            $map['chapter_section'] = $section_id;
            $map['knowledge'] = $Knowledge_id;


            $id = M('exercises_collection')->add($map);
            if ($id !== false) {
                $this->showMessage( 200,'success','收藏成功');
            } else {
                $this->showMessage( 400,'success','收藏失败');
            }
        }
    }

    private function transExerciseData($result,$fields)
    {
        $newResult = array();
        $sub2 = array();
        $sub3 = array();
        $lastMainCategory = 0;
        $lastSubMainCategory = 0;
        $category = '';
        $fieldArray = explode(',',$fields);
        $categoryConfig = json_decode(EXERCISE_CATEGORY,true);
        foreach($result as $key=>$val)
        {
            if($category != $val['category'])
            {
                list($mainCategory,$subCategory) = explode(',',$val['category']);

                if(empty($mainCategory) || empty($subCategory))
                    return array();
                if(!empty($sub2))
                {
                    if($lastSubMainCategory != 0) {
                        $sub3[] = array('name' => $categoryConfig[$lastMainCategory]['data'][$lastSubMainCategory]['name'],
                            'data' => $sub2
                        );
                        $sub2 = array();
                    }

                }

                $lastSubMainCategory = $subCategory;

                if($lastMainCategory != $mainCategory ){
                    if(!empty($sub3)) {
                        if ($lastMainCategory != 0) {
                            if(!empty($sub2))
                            {
                                $sub3[] = array('name' => $categoryConfig[$mainCategory]['data'][$lastSubMainCategory]['name'],
                                    'data' => $sub2
                                );
                                $sub2 = array();
                            }
                            $newResult[] = array('name' => $categoryConfig[$lastMainCategory]['name'],
                                'data' => $sub3
                            );
                            $sub3 = array();
                        }
                    }
                }
                $lastMainCategory = $mainCategory;
                $category = $val['category'];
            }
            $sub1 = array();
            foreach($fieldArray as $keyIndex=>$fieldName)
            {
                $sub1[$fieldName] = $val[$fieldName];
                if($fieldName==='category')
                {
                    $sub1[$fieldName] = explode(',',$val[$fieldName])[1];
                }
            }
            $sub2[] = $sub1;

        }
        if(!empty($sub2) && !empty($mainCategory))
            $sub3[] = array('name'=>$categoryConfig[$mainCategory]['data'][$subCategory]['name'],
                'data' => $sub2
            );

        if(!empty($sub3)){
            $newResult[] = array('name'=>$categoryConfig[$mainCategory]['name'],
                'data' => $sub3
            );
        }
        return $newResult;
    }

    public function getAllExerciseInfo()
    {
        $result = array();

        $ids = getParameter('exercise_id_list','str');
        $userId = getParameter('userId','int',false);
        $role = getParameter('role','int',false);
        $role = 2;
        //语音题
        $condition['questionId'] = $ids;
        $condition['status'] = EXERCISE_STATE_ONSHELF;
        $condition['exerciseMainCategory'] = 1;
        $condition['exerciseSubCategory'] = EXERCISE_WORD.','.EXERCISE_SENTENCE.','.EXERCISE_VIDEO.','.EXERCISE_TEXTBOOK;
        $oldResult = D('Exercises_question_processinfo')->getQuestionList($userId, $role, $condition,"create_time",1);
        foreach ($oldResult as $key => $val)
        {
            $result[$key]['id'] = $oldResult[$key]['id'];
            $result[$key]['name'] = $oldResult[$key]['words'];
            $result[$key]['translation'] = $oldResult[$key]['analysis'];
            $result[$key]['url'] = $oldResult[$key]['subject_name'];
            $result[$key]['count_score'] = $oldResult[$key]['count_score'];
            $result[$key]['category'] = $oldResult[$key]['category'];
        }
        $voiceData = $this->transExerciseData($result,'id,name,translation,url,count_score');

        //普通题
        $result = array();
        unset($condition['exerciseMainCategory']);
        $isNormalExercise = 1;
        $oldResult = D('Exercises_student_homework')->getOrderedExerciseList($ids,$role,$userId,$isNormalExercise);

        foreach ($oldResult as $key => &$val)
        {
            if (!empty($val['home_topic_type'])) {
                $topic_name = M('exercises_course')->where("id=".$val['home_topic_type'])->find();
                $oldResult[$key]['topic_type'] = $topic_name['name'];
            }
            $result[$key]['id'] = $oldResult[$key]['id'];
            //$result[$key]['subject_name'] = $oldResult[$key]['subject_name'];
            $val['subject_name'] = htmlspecialchars_decode($val['subject_name']);
            $result[$key]['subject_name'] =str_replace('ㄖ','&nbsp;__________&nbsp; ',$val['subject_name']);

            $result[$key]['topic_type'] = $oldResult[$key]['topic_type'];
            $result[$key]['analysis'] = $oldResult[$key]['analysis'];
            $result[$key]['count_score'] = $oldResult[$key]['score'];
            $result[$key]['difficulty'] = $this->createExercise['difficulty'][$oldResult[$key]['difficulty']-1]['name'];
            $result[$key]['answer'] = $oldResult[$key]['answer'];
            $result[$key]['answer_select'] = $oldResult[$key]['answer_select'];
            $result[$key]['right_key'] = $oldResult[$key]['right_key'];
            $result[$key]['eid'] = $oldResult[$key]['eid'];
            if($oldResult[$key]['exercise_type'] == 2) //复合题
             ;//TODO:增加复合题逻辑;

            $result[$key]['category'] = $oldResult[$key]['category'];
        }

        $normalData = $this->transExerciseData($result,'id,subject_name,topic_type,analysis,difficulty,answer,answer_select,right_key,count_score,eid');
        $this->showMessage(200,'success',array_merge($voiceData,$normalData));
    }

    //根据选择的习题id 获取习题列表展示
    public function accordingExerciseIdShowList() {
        $id = getParameter('exercise_id_list','str');
        $userId = getParameter('userId','int',false);
        $role = getParameter('role','int',false);
        $map['exercises_createexercise.id'] = array('in',$id);
        $createexercise_list = explode(",",$id);
        $setList=[];
        foreach ($createexercise_list as $kk=>$vv) {
            $setList[$vv] = $kk+1;
        }
        
        $userId = empty($userId)?0:$userId;
        $role = empty($role)?0:$role;
        if (!empty($id)) {
            $list = M('exercises_createexercise')
                ->join("left join exercises_collection ON exercises_collection.exercises_id=exercises_createexercise.id and exercises_collection.exercises_id is not null and exercises_collection.teacher_id=".$userId." and role=".$role)
                ->field("exercises_collection.id as eid,exercises_createexercise.count_score,exercises_createexercise.id,exercises_createexercise.subject_name,exercises_createexercise.home_topic_type as topic_type,exercises_createexercise.difficulty,exercises_createexercise.analysis")
                ->group('exercises_createexercise.id')
                ->where($map)
                ->select();

            foreach ( $list as $k=>&$v) {

                if (!empty($v['topic_type'])) {
                    $topic_name = M('exercises_course')->where("id=".$v['topic_type'])->find();
                    $list[$k]['topic_type'] = $topic_name['name'];
                    $list[$k]['difficulty'] = C('APIdifficulty.'.$v['difficulty']);
                }

                $list[$k]['topic_type_id'] = $v['topic_type'];
                $list[$k]['order_keys'] = $setList[$v['id']];

                $v['subject_name'] = htmlspecialchars_decode($v["subject_name"]);
                $v['subject_name'] =str_replace('ㄖ','&nbsp;__________&nbsp; ',$v['subject_name']);

            }

            array_multisort(array_column($list,'order_keys'),SORT_ASC,$list);

        }
        $this->showMessage( 400,'success',$list);
    }

    //根据习题id获取所有的习题详情
    public function getExerciseIdAllDetails(){
        $id = getParameter('exercise_id_list','str');
        $userId = getParameter('userId','int',false);
        $role = getParameter('role','int',false);
        $isArr = getParameter('isArr','int',false);

        if (strpos($id,",")!== false) {
            $list=[];
            $id = explode(",",$id);
            foreach ( $id as $k=>$vid) {
                $list[$k] = $this->getExerciseIdDetails($vid,$userId,$role);
            }
        } else {
            $list = $this->getExerciseIdDetails($id,$userId,$role);
        }

        if ($isArr ==1) {
            //$arr[] = $list;
            $this->showMessage( 200,'success',$list);
        } else {

            if ($isArr == 2) {
                if (count($list) == count($list, 1)) {
                    $arrList[] = $list;
                } else {
                    $arrList = $list;
                }
                $this->showMessage(200, 'success', $arrList);
            } else {
                $this->showMessage(200, 'success', $list);
            }
        }
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
        $exercise_info['answerlist'] = $exercise_info['answer_select'];
        if (empty($exercise_info)) {
            return "";
        }
//        if($exercise_info['types'] == EXERCISE_TYPE_ABNORMAL)
//        {
//            redirect('/index.php?m=Exercise&c=Multimedia&a=homeworkDetails&id='.$id);
//        }


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

            $exercise_info['difficulty'] = C('APIdifficulty.'.$exercise_info['difficulty']);

            if (!empty($exercise_info['subject'])) {
                $course_name = D('Exercises_Course')->getCourseName($exercise_info['subject']);
            }
            $exercise_info['course_name'] = $course_name['name'];
            $exercise_info['is_topic_type'] = $exercise_info['topic_type'];
            $getCourse = D('Exercises_Course')->getCourseList($exercise_info['subject']);
            if (!empty($getCourse)) {
                foreach ($getCourse as $k=>$v) {
                    if ($exercise_info['home_topic_type'] == $v['id']) {
                        $exercise_info['topic_type']= $v['name'];
                        break;
                    }
                }
            }

        } else { //复合题

            $getCourse = D('Exercises_Course')->getCourseList($exercise_info['subject']);
            if (!empty($getCourse)) {
                foreach ($getCourse as $k=>$v) {
                    if ($exercise_info['home_topic_type'] == $v['id']) {
                        $exercise_info['topic_type'] = $v['name'];
                        break;
                    }
                }
            }

            $exercise_info['study_section'] = $exercise_info['study_section']-1;
            $exercise_info['exercise_source'] = $exercise_info['exercise_source']-1;

            $exercise_info['study_section_name'] = C('Studysection.'.$exercise_info['study_section']);
            $exercise_info['exercise_source_name'] = C('SOURCE.'.$exercise_info['exercise_source']);
            $exercise_info['difficulty'] = C('APIdifficulty.'.$exercise_info['difficulty']);

            if (!empty($exercise_info['subject'])) {
                $course_name = D('Exercises_Course')->getCourseName($exercise_info['subject']);
            }
            $exercise_info['course_name'] = $course_name['name'];

        }

        $isMoble = isMobile();

        if ($isMoble == true) {
            $exercise_info['score'] = $exercise_info['count_score'];
        }

        $exercise_info['search_name'] = htmlspecialchars_decode($exercise_info["search_name"]);
        $exercise_info['subject_name'] = htmlspecialchars_decode($exercise_info["subject_name"]);
        $exercise_info['subject_name'] =str_replace('ㄖ','&nbsp;__________&nbsp; ',$exercise_info['subject_name']);

        $exercise_info['json_html'] = htmlspecialchars_decode($exercise_info["json_html"]);
        $exercise_info['answerList'] = json_decode($exercise_info["answerlist"],true);

        if (is_array($exercise_info['answerList'])) {
            foreach ($exercise_info['answerList'] as $kk=>$vv) {
                $exercise_info['answerList'][$kk] = htmlspecialchars_decode($vv);
            }
        } else {
            $exercise_info['answerList'] = htmlspecialchars_decode($exercise_info["answerList"]);
        }


        $exercise_info['answer'] = json_decode($exercise_info["answer"],true);

        if (is_array($exercise_info['answer'])) {

            if ($exercise_info['is_topic_type']== 1) {
                foreach ($exercise_info['answer'] as $kk=>$vv) {
                    $exercise_info['answer'][$kk] = htmlspecialchars_decode($vv);
                }
            } else {
                $exercise_info['answer'] = htmlspecialchars_decode(implode(",",$exercise_info['answer']));
            }
            //
        } else {
            $exercise_info['answer'] = htmlspecialchars_decode($exercise_info["answer"]);
        }

        $exercise_info['analysis'] = htmlspecialchars_decode($exercise_info["analysis"]);
        $exercise_info['right_key'] = htmlspecialchars_decode($exercise_info["right_key"]);


        if ($exercise_info['is_topic_type']== 3) {
            $exercise_info['right_key'] = $exercise_info["answer"];
            $exercise_info['answer'] = $exercise_info["answerList"];
        }

        if ($exercise_info['is_topic_type']== 2) {
            $exercise_info['right_key'] = $exercise_info["answer"];
            unset($exercise_info["answer"]);
        }

        if ($exercise_info['is_topic_type']== 5) {
            unset($exercise_info["answer"]);
        }

        if ($exercise_info['is_topic_type']== 6) {
            unset($exercise_info["answer"]);
        }

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


//    public function getExerciseIdDetails($id,$userId,$role) {
//
////        $id = getParameter('id','int');
////        $userId = getParameter('userId','int',false);
////        $role = getParameter('role','int',false);
//        if( empty($id) || is_numeric($id)==false ) {
//            die('参数错误');
//        }
//
//        $exercise_info = D('Create_Exercise')->getExerciseInfo( $id );
//        if (empty($exercise_info)) {
//            die("非法错误信息");
//        }
//        if($exercise_info['types'] == EXERCISE_TYPE_ABNORMAL)
//        {
//            redirect('/index.php?m=Exercise&c=Multimedia&a=homeworkDetails&id='.$id);
//        }
//
//
//        $difficultyList = C('difficulty');
//        $difficultyName = '';
//        $difficulty = D('Exercises_createexercise')->getExerciseDifficulty($id);
//        foreach ($difficultyList as $key2 => $val2) {
//            if ($difficulty == $val2['id'])
//                $difficultyName = $val2['name'];
//        }
//        $exercise_info['difficulty_name'] = $difficultyName;
//        if ($exercise_info['exercise_type']==1) { //独立题
//            $exercise_info['topic_type_show'] = $exercise_info['topic_type']-1;
//            $exercise_info['study_section'] = $exercise_info['study_section']-1;
//            $exercise_info['exercise_source'] = $exercise_info['exercise_source']-1;
//
//            $exercise_info['topic_type_name'] = C('exercisesType.'.$exercise_info['topic_type_show']);
//            $exercise_info['study_section_name'] = C('Studysection.'.$exercise_info['study_section']);
//            $exercise_info['exercise_source_name'] = C('SOURCE.'.$exercise_info['exercise_source']);
//
//            if (!empty($exercise_info['subject'])) {
//                $course_name = D('Exercises_Course')->getCourseName($exercise_info['subject']);
//            }
//            $exercise_info['course_name'] = $course_name['name'];
//
//            $getCourse = D('Exercises_Course')->getCourseList($exercise_info['subject']);
//            if (!empty($getCourse)) {
//                foreach ($getCourse as $k=>$v) {
//                    if ($exercise_info['home_topic_type'] == $v['id']) {
//                        $exercise_info['topic_type_name']['name'] = $v['name'];
//                        break;
//                    }
//                }
//            }
//
//        } else { //复合题
//
//            $getCourse = D('Exercises_Course')->getCourseList($exercise_info['subject']);
//            if (!empty($getCourse)) {
//                foreach ($getCourse as $k=>$v) {
//                    if ($exercise_info['home_topic_type'] == $v['id']) {
//                        $exercise_info['topic_type_name']['name'] = $v['name'];
//                        break;
//                    }
//                }
//            }
//
//            $exercise_info['study_section'] = $exercise_info['study_section']-1;
//            $exercise_info['exercise_source'] = $exercise_info['exercise_source']-1;
//
//            $exercise_info['study_section_name'] = C('Studysection.'.$exercise_info['study_section']);
//            $exercise_info['exercise_source_name'] = C('SOURCE.'.$exercise_info['exercise_source']);
//
//            if (!empty($exercise_info['subject'])) {
//                $course_name = D('Exercises_Course')->getCourseName($exercise_info['subject']);
//            }
//            $exercise_info['course_name'] = $course_name['name'];
//
//        }
//
//        $exercise_info['search_name'] = htmlspecialchars_decode($exercise_info["search_name"]);
//        $exercise_info['subject_name'] = htmlspecialchars_decode($exercise_info["subject_name"]);
//        $exercise_info['answer'] = htmlspecialchars_decode($exercise_info['answer']);
//        $exercise_info['json_html'] = htmlspecialchars_decode($exercise_info["json_html"]);
//        $exercise_info['answer'] = htmlspecialchars_decode(json_decode($exercise_info["answer"],true));
//        $exercise_info['analysis'] = htmlspecialchars_decode($exercise_info["analysis"]);
//
//        $ecmap['exercises_id'] = $id;
//        $ecmap['teacher_id'] = $userId;
//        $ecmap['role'] = $role;
//        $ec = M('exercises_collection')->where($ecmap)->find();
//        if(!empty($ec)) {
//            $exercise_info['is_exercises_collection'] =1;
//        } else {
//            $exercise_info['is_exercises_collection'] =0;
//        }
//
//        return $exercise_info;
//        //$this->showMessage( 400,'success',$exercise_info);
//
//    }

    //获取我的组卷列表
    public function getMyTestAssemblyList() {
        $userId = getParameter('userId','int');
        $map['exercises_create_paper.teacher_id'] = $userId;
        $list = M('exercises_create_paper')
            ->join("left join exercises_paper_bigquestion ON exercises_paper_bigquestion.paper_id=exercises_create_paper.id")
            ->field('exercises_create_paper.id,paper_name,score,sum(exercises_paper_bigquestion.big_topic_num) as exercise_count')
            ->where( $map )
            ->group('exercises_create_paper.id')
            ->select();
        $this->showMessage( 400,'success',$list);
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

    //我的组卷 去组卷 保存组卷
    public function saveMyTestAssembly() {
        $json = $_POST['info'];
        $json = json_decode($json,true);

        $masterinfo['paper_name'] = $json['parper_name'];
        $masterinfo['score'] = $json['count_parper_score'];
        $masterinfo['status'] = 110;
        $masterinfo['create_at'] = time();
        $masterinfo['update_at'] = time();
        $masterinfo['paper_num'] = count($json['typelist']);
        $masterinfo['teacher_id'] = $json['teacherId'];

        $Model = M();
        $Model->startTrans();

        $isLock = false;
        $id =  M('exercises_create_paper')->add($masterinfo);

        if ($id !==false) {
            $isLock = true;
        }

        if (!empty($json['typelist'])) {
            foreach ($json['typelist'] as $k=>$item) {
                $itemmap['id'] = $item['type'];
                $info = M('exercises_course')->field("id,name")->where($itemmap)->find();
                $addmap['big_topic_number'] =$k+1;
                $addmap['paper_id'] =$id;
                $addmap['big_topic_name'] =$info['name'];
                $addmap['big_topic_describe'] ="自定义组卷";
                $addmap['big_topic_num'] =$item['count'];
                $addmap['template_id'] =$info['id']; //习题前台展示类型变为模板id

                $bigid  = M('exercises_paper_bigquestion')->add($addmap);
                if ($bigid !== false) {
                    $isLock = true;
                } else {
                    break;
                }

                foreach ( $item['exercise_list'] as $kk=>$vv) {
                    $childinfo = explode("_",$vv);
                    $bigmap['exercise_id'] = $childinfo[0];
                    $bigmap['paper_id'] = $id;
                    $bigmap['big_question_id'] = $bigid;
                    $bigmap['big_order'] = $kk+1;
                    $bigmap['create_at'] = time();
                    $bigmap['update_at'] = time();
                    $bigmap['exercises_score'] = $childinfo[1];
                    $bigmap['knowledge_type'] = $item['type'];

                    unset($childinfo[0]);
                    unset($childinfo[1]);

                    if (!empty($childinfo)) {
                        $knowledge_list = implode("_",$childinfo);
                        $bigmap['knowledge_val'] = $knowledge_list;
                    }

                    $sid = M('exercises_parper_concat')->add($bigmap);

                    if ($sid !== false) {
                        $isLock = true;
                    } else {
                        break;
                    }
                }
            }
        }

        if ($isLock == true ){
            $Model->commit();
            $this->showMessage( 200,'success',"保存成功");
        } else {
            $Model->rollback();
            $this->showMessage( 500,'success',"保存失败");
        }

    }

    //我的组卷 布置作业
    public function myTestAssemblyAssignHomeWork() {
        $name = getParameter('name','str');
        $release_time = getParameter('release_time','str');
        $deadline = getParameter('deadline','str');
        $jobsments = getParameter('jobsments','str');
        $exercises_num = getParameter('exercises_num','int');
        $userId = getParameter('userId','int');
        $userName = getParameter('userName','str');
        $is_group_work = getParameter('is_group_work','int');
        $course_id = getParameter('course_id','int');
        $course_name = getParameter('course_name','str');
        $grade_class_list = getParameter('grade_class_list','str');

        $mastermap['name'] = $name;
        $mastermap['release_time'] = $release_time;
        $mastermap['deadline'] = $deadline;
        $mastermap['jobsments'] = $jobsments;
        $mastermap['type'] = 1;
        $mastermap['exercises_num'] = $exercises_num;
        $mastermap['create_user_id'] = $userId;
        $mastermap['create_user_name'] = $userName;
        $mastermap['is_group_work'] = $is_group_work;
        $mastermap['total_score'] = $userName;
        $mastermap['course_id'] = $course_id;
        $mastermap['course_name'] = $course_name;


        $Model = M();
        $Model->startTrans();

        $isLock = false;

        $homeId = M('exercises_homwork_basics')->add($mastermap);

        if ($homeId !== false) {
            $isLock = true;
        }

        $grade_class_list_arr = explode(",",$grade_class_list);

        foreach ($grade_class_list_arr as $k=>$v) {
            $adddata = explode("_",$v);
            $addmap['work_id'] = $homeId;
            $addmap['grade_id'] = $adddata[0];
            $addmap['class_id'] = $adddata[1];
            $addmap['grade_name'] = $adddata[2];
            $addmap['class_name'] = $adddata[3];
            $cid    = M('exercises_homwork_class_relation')->add($addmap);

            if ($cid !== false) {
                $isLock = true;
            } else {
                break;
            }
        }


        if ($isLock == true ){
            $Model->commit();
            $this->showMessage( 200,'success',"保存成功");
        } else {
            $Model->rollback();
            $this->showMessage( 500,'success',"保存失败");
        }

    }


    //总得分统计
    public function totalScoreCount(){
        $homeWorkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');
        $deadline = M('exercises_homwork_basics')->where("id=".$homeWorkId)->find();
        $diffdeadline = $deadline['deadline'];

        $map['work_id'] = $homeWorkId;
        $map['correct_status'] = 1;
        //$map['submit_at'] = array('lt',$diffdeadline); //小于截止时间的
        $map['is_delete'] = 2;
        $map['class_id'] = $classId;
        $info   = M('exercises_student_homework')->join("auth_student ON auth_student.id=exercises_student_homework.student_id")->field("total_score,student_name")->where($map)->select();

        $setX=[];
        $setY=[];

        $list=[];
        $count=0;
        $stu_num=0;
        if (!empty($info)) {
            foreach ($info as $k=>$v) {
                $setX[] = $v['student_name'];
                $setY[] = floatval($v['total_score']);
                $count += $v['total_score'];
                $stu_num++;
            }
            $list['avg_total'] = $count/$stu_num;

            sort($setY);
            $list['minimum'] = $setY[0]; //最小值
            rsort($setY);
            $list['highest'] = $setY[0];//最大值

            $list['setX'] = $setX;
            $list['setY'] = $setY;
        }

        if (empty($list)) {
            $list = (object)[];
        }

        $this->showMessage( 200,'success',$list);
    }

    //提交速度统计
    public function submitPeedCount(){
        $homeWorkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');
        $deadline = M('exercises_homwork_basics')->where("id=".$homeWorkId)->find();
        $diffdeadline = $deadline['deadline'];

        $map['work_id'] = $homeWorkId;
        $map['correct_status'] = 1;
        //$map['submit_at'] = array('lt',$diffdeadline); //小于截止时间的
        $map['is_delete'] = 2;
        $map['class_id'] = $classId;
        $info   = M('exercises_student_homework')->join("auth_student ON auth_student.id=exercises_student_homework.student_id")->field("work_timeout,student_name")->where($map)->select();

        $setX=[];
        $setY=[];

        $list=[];
        if (!empty($info)) {
            foreach ($info as $k=>$v) {
                $setX[] = $v['student_name'];
                $setY[] = intval($v['work_timeout']);
            }
            $list['setX'] = $setX;
            $list['setY'] = $setY;
        }

        $list['avgpeed'] = array_sum($setY)/(count($setY));


        $fastest   = M('exercises_student_homework')->join("auth_student ON auth_student.id=exercises_student_homework.student_id")->field("work_timeout,student_name")->where($map)->order('exercises_student_homework.work_timeout asc')->find();
        $slowest  = M('exercises_student_homework')->join("auth_student ON auth_student.id=exercises_student_homework.student_id")->field("work_timeout,student_name")->where($map)->order('exercises_student_homework.work_timeout desc')->find();

        $list['fastest'] =$fastest['work_timeout'];
        $list['slowest'] =$slowest['work_timeout'];

        $this->showMessage( 200,'success',$list);
    }

    //完成率统计
    public function completionRateCount() {
        $homeWorkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');
        $map['exercises_homwork_basics.id'] = $homeWorkId;
        $map['exercises_homwork_class_relation.class_id'] = $classId;
        $infolist = M('exercises_homwork_basics')
            ->join("exercises_homwork_class_relation ON exercises_homwork_class_relation.work_id=exercises_homwork_basics.id")
            ->join("biz_class_student ON biz_class_student.class_id=exercises_homwork_class_relation.class_id")
            ->field("biz_class_student.student_id")
            ->where($map)
            ->select();
        //全部学生 $allStudentList = [];
        foreach ($infolist as $k=>$v) {
            $allStudentList[] = $v['student_id'];
        }

        //查询已完成作业的
        $map['exercises_homwork_class_relation.class_id'] = $classId;
        $FinishedWork = M('exercises_homwork_basics')
            ->join("exercises_homwork_class_relation ON exercises_homwork_class_relation.work_id=exercises_homwork_basics.id")
            ->join("biz_class_student ON biz_class_student.class_id=exercises_homwork_class_relation.class_id")
            ->join("exercises_student_homework ON exercises_student_homework.work_id = exercises_homwork_basics.id")
            ->field("exercises_student_homework.student_id")
            ->where($map)
            ->group("exercises_student_homework.student_id")
            ->select();

        $FinishedWorkStudentId=[];
        foreach ($FinishedWork as $ck=>$cv) {
            $FinishedWorkStudentId[]=  $cv['student_id'];
        }

        $diffweiHomeWork = array_values(array_diff($allStudentList,$FinishedWorkStudentId));

        $data['count_student'] = count($allStudentList);
        $data['count_submit_student'] = count($FinishedWorkStudentId);
        $data['count_unsubmit_student'] = count($diffweiHomeWork);

        if(!empty($diffweiHomeWork)) {
            $wheremap['id'] = array('in',implode(",",$diffweiHomeWork));
            $stu_list = M('auth_student')->where($wheremap)->field("student_name")->select();
        }
        $data['count_unsubmit_student_list'] = $stu_list;

        $this->showMessage( 200,'success',$data);
    }

    //正确率统计
    public function correctRateCount() {
        $homeWorkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');
        $deadline = M('exercises_homwork_basics')->where("id=".$homeWorkId)->find();
        $diffdeadline = $deadline['deadline'];

        $result=[];

        $map['exercises_student_homework.work_id'] = $homeWorkId;
        //$map['exercises_student_homework.submit_at'] = array('lt',$diffdeadline); //小于截止时间的
        $map['exercises_student_homework.is_delete'] = 2;
        $map['exercises_student_homework.class_id'] = $classId;
        $list   = M('exercises_student_homework')
            ->join("exercises_student_relation ON exercises_student_relation.work_id=exercises_student_homework.id")
            ->field("sum(exercises_student_relation.total_score) as total_score,sum(exercises_student_relation.exercises_score) as exercises_score,exercises_student_relation.type,sum(exercises_student_relation.is_right=1) as right_student_count,count(exercises_student_relation.student_id) as submit_student_count,exercises_student_relation.exercises_id")
            ->where($map)
            ->group('exercises_id')
            ->select();
        //print_r(M()->getLastSql());die();
        foreach ($list as $ok=>$ov) {
            $arrexerciseList[] = $ov['exercises_id'];
        }
        //开始转化
        $exerciselist = A('ApiInterface/Version1_3/HomeworkCommon')->getOrderedExerciseList(implode(",",$arrexerciseList),2,0,2);
        foreach ($exerciselist as $kk=>$vv) {
            $mapexerciselist[] = $vv['id'];
        }

        $resultdata=[];

        foreach ($mapexerciselist as $sk=>$sv) {
            $setmapwhere['exercises_student_relation.homework_id'] = $homeWorkId;
            $setmapwhere['exercises_student_relation.class_id'] = $classId;
            $setmapwhere['exercises_student_relation.exercises_id'] = $sv;
            $info = M('exercises_student_relation')->where($setmapwhere)->field("sum(total_score) as total_score,sum(exercises_score) as exercises_score,type,sum(is_right=1) as right_student_count,count(student_id) as submit_student_count,exercises_id")->group('exercises_id')->find();
            $resultdata[] = $info;
        }

        $setX=[];
        $setY=[];
        foreach ($resultdata as $k=>&$v) {
            if ($v['type']==2) {
                $v['percent'] = round(($v['right_student_count']/$v['submit_student_count'])*100);
            } else {
                $v['percent'] =round(($v['exercises_score']/$v['total_score'])*100);
            }

            $setY[] = $v['percent'];
            $num = $k+1;
            $setX[] = "题目".$num;
            $v['name'] = "题目".$num;
        }

        $result['setY'] = $setY;
        $result['setX'] = $setX;


        $result['exercise_list']=$resultdata;


        array_multisort(array_column($resultdata,'percent'),SORT_ASC,$resultdata);//排序之后的

        $result['order_exercise_list']=$resultdata;
        

        $exercise_count = M('exercises_homwork_relation')->where("work_id=".$homeWorkId)->count();
        $result['exercise_count']=$exercise_count;
        $where['work_id'] = $homeWorkId;
        $where['class_id'] = $classId;
        $submit_student_list = M('exercises_student_homework')->where($where)->count();
        $result['submit_student_count']=$submit_student_list;


        $setmap['exercises_homwork_class_relation.work_id'] = $homeWorkId;
        $setmap['exercises_homwork_class_relation.class_id'] = $classId;
        $class_student = M('exercises_homwork_class_relation')->join("biz_class_student ON biz_class_student.class_id=exercises_homwork_class_relation.class_id")->where($setmap)->field("biz_class_student.student_id")->count("distinct biz_class_student.student_id");
        $result['class_student_count']=$class_student;

        $this->showMessage( 200,'success',$result);
    }

	//语音作业
	public function voiceWork(){
		$this->courseId = I('courseId');
		$this->category= I('category');
		$this->userId = I('userId');
		$this->role= I('role');

		$this->infoId= I('infoId');
		$this->termId = I('term_id');
		$this->chapterId= I('chapter_id');
		$this->sectionId= I('section_id');
		$this->gradeId= I('grade_id');
		$this->display("voiceWork");
	}
	//微课
	public function textBookWork(){
		$this->courseId = I('courseId');
		$this->category= I('category');
		$this->userId = I('userId');
		$this->role= I('role');

		$this->infoId= I('infoId');
        $this->termId= I('term_id');
        $this->chapterId= I('chapter_id');
        $this->gradeId= I('grade_id');
        $this->sectionId= I('section_id');
		$this->display("textBookWork");
	}
	public function homeworkShare(){
		$this->assign('oss_path', C('oss_path'));
        $url = str_replace('flag=1','',WEB_URL.$_SERVER['REQUEST_URI']);
        $this->assign('urldata',"http://".$url );
		$this->display("homeworkShare");
	}
    //根据所选习题布置作业

    public function selectExerciseIdMakeHomeWork(){

        $mastermap['name'] = getParameter('name','str');
        $mastermap['release_time'] = I('release_time');
        $mastermap['deadline'] = I('deadline');

//        $strarrangeTime = strtotime($mastermap['release_time']);
//        $strendTime = strtotime($mastermap['deadline']);
//
//        if (!empty($arrangeTime)) {
//            if ($strarrangeTime < time()){
//                $res['status'] = 400;
//                $res['info'] = "布置时间不能小于当前时间";
//                $this->ajaxReturn($res);
//            }
//        } else {
//            $arrangeTime= date('Y-m-d H:i:s',time());
//            $startime= strtotime($arrangeTime);
//        }
//
//        if ($startime >= $strendTime){
//            $res['status'] = 400;
//            $res['info'] = "布置时间不能大于或者等于截止时间";
//            $this->ajaxReturn($res);
//        }

        $mastermap['jobsments'] = I('jobsments');
        $mastermap['type'] = 1;
        $mastermap['exercises_num'] = I('exercises_num');
        $mastermap['create_user_id'] = I('userId');
        $mastermap['create_user_name'] =I('userName');
        $mastermap['is_group_work'] = I('is_group_work');
        $mastermap['total_score'] = I('total_score');
        $mastermap['course_id'] = I('course_id');
        $mastermap['course_name'] = I('course_name');
        $mastermap['version_id'] = I('version_id');
        $selectExerciseListMap = I('selectExerciseListMap');
        $mastermap['is_delete'] = 2;
        $mastermap['knowledge_id'] = I('knowledge_id');
        $mastermap['chapter_id'] = I('chapter_id');
        $mastermap['create_at'] = date('Y-m-d H:i:s',strtotime("-1 second"));

        $isPc = I('isPc');

        if($isPc == 1){
            $idListString = implode(",",$_POST['exercise_list']);
        }
        else{
            $list = json_decode($_POST['exercise_list'], true);
            $idList = [];
            foreach($list as $key=>$val){
                $idList = array_merge(array_map(function($value){
                    return explode('_',$value)[1];
                },$val['list']),$idList);
            }
            $idListString = implode(',',$idList);
        }
        $idWhere = ' id IN ('.$idListString .') ';
        $mastermap['subject_num'] = M()->query("SELECT SUM(CASE WHEN topic_type IN (2,4,5,6) THEN 1 ELSE 0 END) subject_num FROM exercises_createexercise WHERE $idWhere ")[0]['subject_num'];

        if ($isPc == 1) {
            $c_name = M('exercises_course')->where("id=".$mastermap['course_id'])->find();
            $mastermap['course_name'] = $c_name['name'];
        }

        $Model = M();
        $Model->startTrans();

        $isLock = false;


        if ($isPc ==1) {
            $grade_class_list = $_POST['grade_class_list'];
        } else {
            $grade_class_list = json_decode($_POST['grade_class_list'],true);
        }


        if (!empty($grade_class_list)) {

            foreach ($grade_class_list as $k=>$v) {
                $id =  M('exercises_homwork_basics')->add($mastermap);

                if ($id ===false) {
                    $isLock = false;
                    break;
                }

                if ($isPc ==1) {
                    $wheres['id'] = array('in', implode(",",$_POST['exercise_list']));
                    $setList = M('exercises_createexercise')->where($wheres)->field("id,count_score as score")->select();

                    foreach ($setList as $kk=>$vv) {
                        $cmap['work_id'] = $id;
                        $cmap['subject'] = $mastermap['course_id'];
                        $cmap['textbook_edition'] = $mastermap['version_id'];

                        $cmap['knowledge'] = 0;
                        $cmap['status'] = 2;
                        $cmap['teacher_id'] = I('userId');

                        $cmap['knowledge_type'] = $selectExerciseListMap[$vv['id']];
                        $cmap['exercises_score'] = $vv['score'];
                        $cmap['exercises_id'] = $vv['id'];

                        $ccid = M('exercises_homwork_relation')->add($cmap);

                        if ($ccid === false) {
                            $isLock = false;
                            break;
                        } else {
                            $isLock = true;

                        }
                    }
                } else {
                    $exercise_list = json_decode($_POST['exercise_list'], true);
                    if (!empty($exercise_list)) {
                        foreach ($exercise_list as $ck=>$cv) {

                            foreach ($cv['list'] as $clk=>$clv) {
                                $exerinfo = explode("_",$clv);
                                $cmap['work_id'] = $id;
                                $cmap['subject'] = $mastermap['course_id'];
                                $cmap['textbook_edition'] = $mastermap['version_id'];

                                $cmap['knowledge'] = 0;
                                $cmap['status'] = 2;
                                $cmap['teacher_id'] = I('userId');
                                $cmap['knowledge_type'] = $cv['type'];
                                $cmap['exercises_score'] = $exerinfo[0];
                                $cmap['exercises_id'] = $exerinfo[1];

                                $ccid = M('exercises_homwork_relation')->add($cmap);
                                if ($ccid === false) {
                                    $isLock = false;
                                    break;
                                } else {
                                    $isLock = true;
                                }

                            }

                        }

                    }
                }

                $map['work_id'] = $id;
                $map['grade_id'] = $v['grade_id'];
                $map['class_id'] = $v['class_id'];
                $map['grade_name'] = $v['grade_name'];
                $map['class_name'] = $v['class_name'];

                $cid = M('exercises_homwork_class_relation')->add($map);
                if ($cid !== false) {
                    $isLock = true;
                } else {
                    $isLock = false;
                    break;
                }
            }
        }

        if ($isLock == true ){
            $Model->commit();
            $this->showMessage( 200,'success',"保存成功");
        } else {
            $Model->rollback();
            $this->showMessage( 500,'success',"保存失败");
        }
    }

    //根据试卷布置作业
    public function accordingPaperAssignment(){
        $mastermap['name'] = I('name');
        $mastermap['release_time'] = I('release_time');
        $mastermap['deadline'] = I('deadline');


        $strarrangeTime = strtotime($mastermap['release_time']);
        $strendTime = strtotime($mastermap['deadline']);

        if (!empty($arrangeTime)) {
            if ($strarrangeTime < time()){
                $res['status'] = 400;
                $res['info'] = "布置时间不能小于当前时间";
                $this->ajaxReturn($res);
            }
        } else {
            $arrangeTime= date('Y-m-d H:i:s',time());
            $startime= strtotime($arrangeTime);
        }

        if ($startime >= $strendTime){
            $res['status'] = 400;
            $res['info'] = "布置时间不能大于或者等于截止时间";
            $this->ajaxReturn($res);
        }

        $mastermap['jobsments'] = I('jobsments');
        $mastermap['type'] = 1;
        $mastermap['exercises_num'] = I('exercises_num');
        $mastermap['create_user_id'] = I('userId');
        $mastermap['create_user_name'] =I('userName');
        //$mastermap['is_group_work'] = I('is_group_work');
        $mastermap['total_score'] = I('total_score');
        $mastermap['course_id'] = I('course_id');
        $mastermap['course_name'] = I('course_name');
        $mastermap['version_id'] = I('version_id');
        $mastermap['is_delete'] = 2;
        $mastermap['knowledge_id'] = I('knowledge_id');
        $mastermap['chapter_id'] = I('chapter_id');
        $mastermap['create_at'] = date('Y-m-d H:i:s',strtotime("-1 second"));

        $paperId = I('paperId');
        $escore = M('exercises_create_paper')->where("id=".$paperId)->find();
        $mastermap['total_score'] = $escore["score"];
        $mastermap['course_id'] = $escore["subject"];

        $course_info = M('exercises_course')->where("id=".$mastermap['course_id'])->find();

        $mastermap['course_name'] = $course_info['name'];

        $count = M('exercises_parper_concat')->where("paper_id=".$paperId)->count();
        $mastermap['exercises_num'] = $count;

        $Model = M();
        $Model->startTrans();

        $isLock = false;



        $isPc = I('isPc');
        if ($isPc ==1) {
            $grade_class_list = $_POST['grade_class_list'];
        } else {
            $grade_class_list = json_decode($_POST['grade_class_list'],true);
        }

        if (!empty($grade_class_list)) {

            foreach ($grade_class_list as $k=>$v) {

                $id =  M('exercises_homwork_basics')->add($mastermap);

                if ($id ===false) {
                    $isLock = false;
                    break;
                }


                $paperList = M('exercises_parper_concat')->where("paper_id=".$paperId)->select();

                if (!empty($paperList)) {

                    foreach ($paperList as $clk=>$clv) {
                        $cmap['work_id'] = $id;
                        $cmap['subject'] = $mastermap['course_id'];
                        $cmap['textbook_edition'] = $mastermap['version_id'];
                        $cmap['knowledge'] = 0;
                        $cmap['status'] = 2;
                        $cmap['teacher_id'] = I('userId');
                        $cmap['exercises_score'] = $clv["exercises_score"];
                        $cmap['exercises_id'] = $clv["exercise_id"];
                        $cmap['assembly_id'] = $paperId;

                        $ccid = M('exercises_homwork_relation')->add($cmap);
                        if ($ccid === false) {
                            $isLock = false;
                            break;
                        } else {
                            $isLock = true;
                        }
                    }
                }


                $map['work_id'] = $id;
                $map['grade_id'] = $v['grade_id'];
                $map['class_id'] = $v['class_id'];
                $map['grade_name'] = $v['grade_name'];
                $map['class_name'] = $v['class_name'];

                $cid = M('exercises_homwork_class_relation')->add($map);
                if ($cid !== false) {
                    $isLock = true;
                } else {
                    $isLock = false;
                    break;
                }
            }
        }


        if ($isLock == true ){
            $Model->commit();
            $this->showMessage( 200,'success',"保存成功");
        } else {
            $Model->rollback();
            $this->showMessage( 500,'success',"保存失败");
        }
    }

    //获取作业报告
    public function getWorkReport(){
        $homeworkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');
        $map['exercises_student_homework.work_id'] = $homeworkId;
        $map['exercises_student_homework.class_id'] = $classId;
        $row = M('exercises_student_homework')
            ->join("exercises_homwork_basics ON exercises_homwork_basics.id=exercises_student_homework.work_id")
            ->join("left join dict_course ON dict_course.id=exercises_homwork_basics.course_id")
            ->where($map)
            ->field("exercises_homwork_basics.course_id,dict_course.course_name,exercises_homwork_basics.name,exercises_homwork_basics.create_user_name,exercises_homwork_basics.total_score,count(distinct exercises_student_homework.student_id) as student_count,sum(work_timeout) as work_timeout,sum(exercises_student_homework.total_score) as correct_total_score")
            ->group("exercises_student_homework.work_id,exercises_student_homework.class_id")
            ->find();
        $row['avg_time'] = ($row['work_timeout'] / $row['student_count'])."s";
        $row['avg_total_score'] = ($row['correct_total_score'] / $row['student_count'])."分";

        //获取成绩优秀榜

        $youxiu = M('exercises_student_homework')
            ->join("auth_student ON auth_student.id=exercises_student_homework.student_id")
            ->where($map)
            ->order('total_score desc,submit_at desc')
            ->limit(3)
            ->field("exercises_student_homework.student_id,auth_student.student_name,exercises_student_homework.total_score,work_timeout")
            ->select();
        $row['excellent_student_list'] = $youxiu;

        //本次作业错误详情
        $map['exercises_student_relation.is_right'] = 2;
        $errorList = M('exercises_student_homework')
            ->join("exercises_student_relation ON exercises_student_relation.work_id=exercises_student_homework.id")
            ->join("exercises_createexercise ON exercises_createexercise.id=exercises_student_relation.exercises_id")
            ->join("left join exercises_course ON exercises_course.id=exercises_createexercise.home_topic_type")
            ->where($map)
            ->field("exercises_course.name as top_name,exercises_createexercise.right_key,exercises_createexercise.answer,exercises_createexercise.count_score,exercises_createexercise.subject_name,exercises_createexercise.home_topic_type,exercises_student_relation.exercises_id,count(distinct exercises_student_relation.student_id) as error_student_count")
            ->group("exercises_student_relation.exercises_id")
            ->select();
        foreach ($errorList as $k=>$v){
            $errorList[$k]['error_rate'] = (($v['error_student_count'] / $row['student_count'])*100)."%";
            $errorList[$k]['subject_name'] = htmlspecialchars_decode($v["subject_name"]);
            $errorList[$k]['answer'] = json_decode($v['answer'],true);
        }

        $row['error_list'] = $errorList;


        //待提高同学 同学的姓名
        //截止后提交作业的同学

        unset($map['exercises_student_relation.is_right']);

        $deadline = M('exercises_homwork_basics')->where("id=".$homeworkId)->find();
        $diffdeadline = $deadline['deadline'];
        $map['exercises_student_homework.submit_at'] = array('gt',$diffdeadline); //小于截止时间的

        $jiezhi = M('exercises_student_homework')
            ->join("auth_student ON auth_student.id=exercises_student_homework.student_id")
            ->where($map)
            ->field("auth_student.student_name,auth_student.id")
            ->limit(2)
            ->select();

        $row['end_time_student_list'] = $jiezhi;

        //未提交的同学

        $condition['exercises_homwork_class_relation.work_id'] = $homeworkId;
        $condition['biz_class.is_delete'] = 0;
        $condition['exercises_student_homework.id'] = array('exp','is null');


        $field = "auth_student.id,auth_student.student_name";
        $order ="exercises_student_homework.submit_at desc";
        $havingwhere= "";
        $result = D('Exercises_homework_class_relation')->getStudentHomeWorkList($condition, $field, 1, 2, $join = '', $order,$havingwhere,$classId);
        $row['unsubmit_student_list'] = $result;

        $this->showMessage( 200,'success',$row);
    }


}
