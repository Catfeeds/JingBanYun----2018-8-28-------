<?php
namespace Home\Controller;

define('FAKE_DATA',1);


class HomeworkParentController extends PublicController
{

    public $pageSize = 20;
    public $model;

    public function __construct()
    {
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
        require($_SERVER['DOCUMENT_ROOT'].'/Application/Exercise/Conf/const.php');
    }


    function getPageSize()
    {
        return $this->pageSize;
    }
    private function status2Number($status)
    {
        switch($status)
        {
            case '新': return 1;
            case '已截止': return 2;
            case '查看报告': return 3;
            case '待批改': return 4;
            default:break;
        }
        return -1;
    }
    /**
     * @描述：查看作业列表
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：studentId[int] Y 学生ID
     * @参数：pageIndex[int] N 分页INDEX 从1开始
     * @参数：pageSize[int] N 分页大小 不传默认4
     * @参数：isFlatten2[int] N 是否只显示前2条
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getStudentHomeworkList()
    {
        $pageIndex=I('pageIndex')<1?1:I('pageIndex');
        $studentId = getParameter('studentId', 'int');
        $parent_id = session('parent.id');

        $map['biz_class.is_delete'] = 0;
        $map['biz_class_student.status'] = 2;
        $map['auth_student_parent_contact.parent_id'] = $parent_id;
        $map['auth_student_parent_contact.student_id'] = $studentId;

        $list = M('exercises_homwork_basics')
            ->join("exercises_homwork_class_relation ON exercises_homwork_class_relation.work_id = exercises_homwork_basics.id")
            ->join("exercises_homwork_relation ON exercises_homwork_relation.work_id = exercises_homwork_basics.id")
            ->join("biz_class ON biz_class.id = exercises_homwork_class_relation.class_id")
            ->join("left join dict_grade ON dict_grade.id = biz_class.grade_id")
            ->join("biz_class_student ON biz_class_student.class_id = exercises_homwork_class_relation.class_id")
            ->join("auth_student_parent_contact ON auth_student_parent_contact.student_id = biz_class_student.student_id")
            ->join("LEFT JOIN exercises_student_homework ON exercises_student_homework.work_id = exercises_homwork_class_relation.work_id AND exercises_student_homework.class_id = exercises_homwork_class_relation.class_id AND exercises_student_homework.student_id = biz_class_student.student_id")
            ->join("LEFT JOIN auth_student ON biz_class_student.student_id = auth_student.id")
            ->group("exercises_homwork_basics.id,biz_class.id")
            ->field("exercises_student_homework.id as student_hid,exercises_homwork_basics.deadline,exercises_homwork_basics.release_time,count(distinct exercises_student_homework.student_id) as submit_student_count,count(distinct biz_class_student.student_id) as all_student_count,exercises_homwork_basics.id,count(distinct exercises_homwork_relation.exercises_id) as exercises_count, exercises_homwork_basics.name,biz_class.name as class_name,dict_grade.grade as grade_name,exercises_homwork_basics.release_time,exercises_homwork_basics.deadline")
            ->order('exercises_homwork_basics.create_at desc')
            ->where($map)
            ->page($pageIndex, $this->pageSize)
            ->select();

        foreach ($list as $k=>$v) {
            if ($v['student_hid']==null) {
                $list[$k]['status_name'] = "待完成";
            }
            if ($v['student_hid']!=null) {
                if ( date("Y-m-d H:i:s",time()) > $v['deadline'] ||  $v['all_student_count'] == $v['submit_student_count']) {
                    $list[$k]['status_name'] = "待批改";
                } else {
                    $list[$k]['status_name'] = "作业报告";
                }
            }
        }



        $this->showMessage(200, 'success', $list);
    }

    //获取最近一次作业的得分
    public function getStudentLatelyHomeWork() {
        $studentId = getParameter('studentId', 'int');
        $map['student_id'] = $studentId;
        $info = M('exercises_student_homework')->where($map)->order('submit_at desc')->field('total_score')->find();
        $empty_object=(object)array();

        if (!empty($info)) {
            $this->showMessage(200, 'success', $info);
        } else {
            $this->showMessage(200, 'success', $empty_object);
        }
    }

    //获取班级排行榜
    public function getStudentRanking() {
        $work_id = getParameter('work_id', 'int');
        $class_id = getParameter('class_id', 'int');
        $map['exercises_student_homework.work_id'] = $work_id;
        $map['exercises_student_homework.class_id'] = $class_id;

        $list = M('exercises_student_homework')
            ->join('auth_student on auth_student.id=exercises_student_homework.student_id')
            ->where($map)
            ->field('exercises_student_homework.total_score,exercises_student_homework.submit_at,auth_student.student_name,auth_student.avatar')
            ->order('exercises_student_homework.total_score desc')
            ->select();

        if (!empty($list)) {
            $this->showMessage(200, 'success', $list);
        } else {
            $info['total_score'] = -1;
            $this->showMessage(500, 'success', $list);
        }
    }

    //获取教师寄语
    public function getTeacherWrote() {
        $work_id = getParameter('work_id', 'int');
        $class_id = getParameter('class_id', 'int');
        $student_id = getParameter('student_id', 'int');

        $map['class_id'] = $class_id;
        $map['work_id'] = $work_id;
        $map['student_id'] = $student_id;

        $list = M('exercises_teacher_wrote')->where($map)->field('id,content,type,is_read')->select();
        if (!empty($list)) {
            $this->showMessage(200, 'success', $list);
        } else {
            $info['total_score'] = -1;
            $this->showMessage(500, 'success', $list);
        }
    }
    //老师寄语已读操作
    public function setTeacherRead() {
        $id = getParameter('id', 'int');
        $map['id'] = $id;
        $data['is_read'] = 2;
        $update = M('exercises_teacher_wrote')->where($map)->save($data);
        if ($update!==false) {
            $this->showMessage(200, 'success', "");
        } else {
            $this->showMessage(500, 'error', "");
        }
    }

    //作业学习轨迹
    public function getStudentLearningHomeWork() {
        $studentId = getParameter('studentId', 'int');
        $courseId = getParameter('courseId', 'int',false);
        $strat_date = getParameter('start_time', 'str',false);
        $end_date = getParameter('end_time', 'str',false);
        $map['exercises_student_homework.student_id'] = $studentId;
        $filter['start'] = $strat_date;
        $filter['end'] = $end_date;

        if ($courseId == -1) {
            unset($courseId);
        }

        if(!empty($courseId)) {
            $map['exercises_homwork_basics.course_id'] = $courseId;
        }

        if (!empty($filter['start'])) {
            $startime = strtotime($filter['start']);
            $startime = date('Y-m-d 00:00:00', $startime);
            $map['exercises_homwork_basics.create_at'] = array('egt',$startime);
        }

        if (!empty($filter['end'])) {
            $endtime = strtotime($filter['end']);
            $endtime = date('Y-m-d 23:59:59', $endtime);
            $map['exercises_homwork_basics.create_at'] = array('elt',$endtime);
        }

        if (!empty($filter['start']) && !empty($filter['end']) ){
            $startime = strtotime($filter['start']);
            $startime = date('Y-m-d 00:00:00', $startime);
            $endtime = strtotime($filter['end']);
            $endtime = date('Y-m-d 23:59:59', $endtime);
            $map['exercises_homwork_basics.create_at'] = array(array('egt',$startime),array('elt',$endtime)) ;

        }
        $map['exercises_student_homework.correct_status'] = 1;
        $info = M('exercises_student_homework')
                ->join('exercises_homwork_basics on exercises_homwork_basics.id=exercises_student_homework.work_id')
                ->where($map)->order('exercises_student_homework.submit_at desc')
                ->field('exercises_student_homework.total_score,exercises_homwork_basics.name')
                ->select();

        $x = array();
        $y = array();
        if (!empty($info)) {
            foreach ($info as $k=>$v) {
                array_push($x,$v['name']);
                array_push($y,intval($v['total_score']));
            }
        }

        $setinfo['setX'] = $x;
        $setinfo['setY'] = $y;

        if (!empty($info)) {
            $this->showMessage(200, 'success', $setinfo);
        } else {
            $this->showMessage(200, 'success', $setinfo);
        }

    }

    public function getCycleList () {
        $list = [
            [
                'id' => -1,
                'name' => '两年前'
            ],

            [
                'id' => 4,
                'name' => '一年前'
            ],

            [
                'id' => 3,
                'name' => '三个月前'
            ],

            [
                'id' => 2,
                'name' => '一个月前'
            ],
            [
                'id' => 5,
                'name' => '一周前'
            ],
        ];
        $this->showMessage(200, 'success', $list);

    }

    //获取学科
    public function getCourseList() {
        $list = D('Exercises_Course')->field("id,name")->getCourseList();

        $allList = array(array('id'=>-1,'name'=>'全部'));
        foreach ($list as $k=>$v) {
            unset($v['parent_id']);
            $allList[$k+1] = $v;
        }
        $this->showMessage(200, 'success', $allList);
    }
    /**
     * @描述：获取作业概要信息
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：homeworkId[int] Y 作业ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getHomeworkAbstract()
    {
        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');
        $homeworkId = getParameter('homeworkId', 'int');
        $result = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);
        $newResult = array();
        $newResult['name'] = $result['name'];
        $newResult['endTime'] = $result['end_time_c'];
        $newResult['releaseTime'] = $result['release_time_c'];
        $newResult['requirement'] = $result['jobsments'];
        $newResult['totalCount'] =  $result['exercises_num'];
        $newResult['totalScore'] =  100;//$result['total_score'];
        $newResult['status'] =  '待完成';
        $this->showMessage(200, 'success', $newResult);
    }

    /**
     * @描述：获取已提交作业的学生列表
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：homeworkId[int] Y 作业ID
     * @参数：classId[int] Y 班级ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getSubmitHomeworkList()
    {
        A('ApiInterface/Version1_2/HomeworkStudent')->getSubmitHomeworkList();
    }
    /**
     * @描述：查看未做作业详情
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：homeworkId[int] Y 作业ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getHomeworkDetail()
    {
        A('ApiInterface/Version1_2/HomeworkStudent')->getHomeworkDetail();
    }



    //已批改作业
    public function completionDetails() {

        $userId = getParameter('userId','int',false);
        $role = getParameter('role','int',false);
        $id = getParameter('homeworkId','int',false);
        $classId = getParameter('classId', 'int',false);
        $submitId = getParameter('submitId', 'int',false);
        $this->userId = $userId;
        $this->role = $role;
        $this->homeworkId = $id;
        $this->classId = $classId;
        $this->submitId = $submitId;

        $isFlatten = getParameter('isFlatten','int',false);

        $result = array();

        $model = D('Exercises_student_homework');
        $info = $model->getStudentHomeworkBriefInfo($submitId);
        $resultData = $model->getHomeworkExerciseList(0,0,$submitId);


        $result['submittime'] = $info['submit_at'];
        $result['status'] = '已完成';
        $result['duration'] = $info['work_timeout'];
        $result['point'] = $info['total_score'];
        $result['totalpoint'] = $info['total_score_base'];
        $result['data'] = $this->transExerciseData($resultData,'name,url,point,total_point,category,org_answer_url');

        $result['data'] = $this->flattenData($result['data'],$isFlatten);
        $result['_right'] =round((($result['point'] / $result['totalpoint'])*100),2) .'%';

        $this->result = $result;
        $this->display();
    }

    /**
     * @描述：查看已做作业详情
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @参数：id[int] Y 作业提交ID
     * @参数：isFlatten[int] N 是否FLATTEN返回数组 0--否 1--是
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getSubmitHomeworkDetail()
    {
        $result = array();
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $id = getParameter('id','int');
        $isFlatten = getParameter('isFlatten','int',false);
        $model = D('Exercises_student_homework');
        $info = $model->getStudentHomeworkBriefInfo($id);

        $resultData = $model->getHomeworkExerciseList(0,0,$id);
        $result['submittime'] = $info['submit_at'];
        $result['status'] = '已完成';
        $result['duration'] = $info['work_timeout'];
        $result['point'] = $info['total_score'];
        $result['totalpoint'] = 100;//$info['total_score_base'];
        $result['data'] = $this->transExerciseData($resultData,'name,url,point,translation,total_point,category,org_answer_url');
        $result['data'] = $this->flattenData($result['data'],$isFlatten);
        $this->showMessage( 200,'success',$result);
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


    private function flattenData($data,$isFlatten=0)
    {
        if($isFlatten == 0)
            return $data;
        $newData = array();
        if(0 <  $isFlatten) {

            foreach ($data as $key => $val) {
                foreach ($val['data'] as $subKey => $subVal) {
                    $newData[] =  $subVal;
                }
            }
        }
        if(1 <  $isFlatten)
        {
            $newDataTemp = $newData;
            $newData = array();
            foreach ($newDataTemp as $key => $val) {
                $name = $val['name'];
                $data = $val['data'];
                $subCount = sizeof($data);
                foreach($data as $subKey => $subVal)
                {
                    $subVal['exerciseName'] =  $name;
                    $subVal['count'] = $subCount;
                    $subVal['index'] = $subKey+1;
                    $newData[] = $subVal;
                }
            }
        }
        return  $newData;
    }

    //孩子列表
    public function myChildren()
    {
        $this->assign('module', '班级行');
        $this->assign('nav', '督学窗');
        $this->assign('subnav', '');
        $this->assign('navicon', 'jiazhangduxue');

        $Model = M('auth_student_parent_contact');
        $list = $Model
            ->join('LEFT JOIN auth_student on auth_student.id=auth_student_parent_contact.student_id')
            ->join('LEFT JOIN dict_schoollist on dict_schoollist.id=auth_student.school_id')
            ->field('auth_student_parent_contact.*,auth_student.student_name,auth_student.id_card,dict_schoollist.school_name,auth_student.avatar')
            ->where("auth_student_parent_contact.parent_id=" . session('parent.id'))
            ->order('create_at desc')
            ->select();

        foreach ($list as $k=>$v) {
            if (empty($v['avatar']) || $v['avatar'] =='default.jpg') {
                if ($v['sex']=="男") {
                    $list[$k]['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_m.png";
                } else {
                    $list[$k]['avatar'] = "http://".WEB_URL."/Public/img/classManage/student_w.png";
                }

            } else {
                $list[$k]['avatar'] = C('oss_path').$v['avatar'];
            }

            $flag = $this->getchildList($v['student_id']);
            if (!empty($flag)) {
                $list[$k]['class_grade_list'] = $flag;
            }

        }

        $this->showMessage(200, 'success', $list);
    }

    //获取孩子班级lieb
    public function getchildList($student_id) {
        $Model = M('biz_class_student');
        //$teacherId = session("teacher.id");

        //$filter['grade_id'] = $_REQUEST['grade'];
        $filter['grade_id'] = getParameter('grade', 'int',false);

        if ($filter['grade_id'] == 0) { //如果没有选择班级 那么就把年级也设置为0
            $filter['class_id'] = 0;
        } else {
            //$filter['class_id'] = $_REQUEST['class'];
            $filter['class_id'] = getParameter('class', 'int',false);
        }

        if (!empty($filter['grade_id'])) $check['dict_grade.id'] = $filter['grade_id'];
        if (!empty($filter['class_id'])) $check['biz_class.id'] = $filter['class_id'];

        $this->assign('class_id_view',$filter['class_id']);

        $this->assign('default_grade',$check['dict_grade.id']);
        $this->assign('default_class',$check['biz_class.id']);

        $check['biz_class.flag'] =  2;

        if (!empty($filter['grade_id'])) $where['dict_grade.id'] = $filter['grade_id'];
        if (!empty($filter['class_id'])) $where['biz_class.id'] = $filter['class_id'];

        $where['biz_class_student.student_id'] =  $student_id;
        $where['biz_class.is_delete'] =  0;
        $where['biz_class_student.status'] =  2;

        $result = $Model
            ->join('biz_class on biz_class.id=biz_class_student.class_id')
            ->join('left join biz_class_teacher ON biz_class_teacher.class_id = biz_class.id')
            ->join('left join auth_teacher ON auth_teacher.id = biz_class_teacher.teacher_id')
            //->join('left join dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_class.name,dict_grade.grade')
            ->where($where)
            ->group('biz_class.id,dict_grade.id')
            ->order('biz_class.create_at desc')
            ->select();
        return $result;
    }

    //孩子所在班级列表
    public function classList()
    {

        $Model = M('biz_class_student');
        //$teacherId = session("teacher.id");

        //$filter['grade_id'] = $_REQUEST['grade'];
        $filter['grade_id'] = getParameter('grade', 'int',false);

        if ($filter['grade_id'] == 0) { //如果没有选择班级 那么就把年级也设置为0
            $filter['class_id'] = 0;
        } else {
            //$filter['class_id'] = $_REQUEST['class'];
            $filter['class_id'] = getParameter('class', 'int',false);
        }

        if (!empty($filter['grade_id'])) $check['dict_grade.id'] = $filter['grade_id'];
        if (!empty($filter['class_id'])) $check['biz_class.id'] = $filter['class_id'];

        $this->assign('class_id_view',$filter['class_id']);

        $this->assign('default_grade',$check['dict_grade.id']);
        $this->assign('default_class',$check['biz_class.id']);

        $check['biz_class.flag'] =  2;

        if (!empty($filter['grade_id'])) $where['dict_grade.id'] = $filter['grade_id'];
        if (!empty($filter['class_id'])) $where['biz_class.id'] = $filter['class_id'];

        $where['biz_class_student.student_id'] =  I('student_id');
        $where['biz_class.is_delete'] =  0;
        $where['biz_class_student.status'] =  2;

        $result = $Model
            ->join('biz_class on biz_class.id=biz_class_student.class_id')
            ->join('left join biz_class_teacher ON biz_class_teacher.class_id = biz_class.id')
            ->join('left join auth_teacher ON auth_teacher.id = biz_class_teacher.teacher_id')
            //->join('left join dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_class_student.class_id,count(biz_class_teacher.teacher_id) as teacher_count,auth_teacher.school_id as ats_id,class_status')
            ->where($where)
            ->group('biz_class.id')
            ->order('biz_class.create_at desc')
            ->select();

        foreach ($result as $k=>$v) {
            if ($v['class_status'] == 2) {
                $d_m['id'] = $v['ats_id'];
                $sc_name = M('dict_schoollist')->where( $d_m )->find();

                $result[$k]['school_name'] = $sc_name['school_name'];
            } else {
                $d_m['id'] = $v['school_id'];
                $sc_name = M('dict_schoollist')->where( $d_m )->find();
                $result[$k]['school_name'] = $sc_name['school_name'];
            }
            $stucount = M('biz_class_student')->where('class_id='.$v['class_id'])->count();
            $stucount = $stucount -1;
            if ( $stucount < 0 ) {
                $stucount = 0;
            }
            $result[$k]['student_count'] = $stucount;
        }

        $this->showMessage(200, 'success', $result);

    }

    //获取学生信息
    public function getStudentDetials() {
        $student_id= I('student_id');

        $stuinfo = M('auth_student')
            ->join('left join dict_schoollist on dict_schoollist.id=auth_student.school_id')
            ->where("auth_student.id={$student_id}")
            ->field('auth_student.student_name,dict_schoollist.school_name,auth_student.avatar,auth_student.sex')
            ->find();

        if (empty($stuinfo['avatar']) || $stuinfo['avatar'] =='default.jpg') {
            if ($stuinfo['sex']=="男") {
                $stuinfo['avatar'] = "http://".WEB_URL."/Public/img/classManage/teacher_m.png";
            } else {
                $stuinfo['avatar'] = "http://".WEB_URL."/Public/img/classManage/teacher_w.png";
            }

        } else {
            $stuinfo['avatar'] = C('oss_path').$stuinfo['avatar'];
        }

        $this->showMessage(200, 'success', $stuinfo);
    }
}
?>

