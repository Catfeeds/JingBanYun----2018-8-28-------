<?php
namespace Exercise\Controller;
use Think\Controller;
use Think\Verify;

class QueryStatisticsController extends ExerciseGlobalController
{

    public $model;
    public $page_size=20;
    private $userInfo;
    public function __construct() {
        parent::__construct();
        $this->userInfo = $this->getUserRoleAuth();
    }

    function curriculumSystemListPage(){
        $data = array();
        $header = array(
            array('name'=>'学段','field'=>'learning_period_id','callback'=>function($value){foreach(C('Studysection') as $key=>$val){ if($val['id'] == $value) return $val['name'];};return '' ;}),
            array('name'=>'学科','field'=>'name'),
            array('name'=>'状态','field'=>'creat_status','callback'=>function($value){if(TREE_FINISH == $value) return '完成';else if(TREE_CREATING == $value) return '创建中';}),
            array('name'=>'录入用户姓名','field'=>'creat_account'),
            array('name'=>'录入账号','field'=>'creat_name'),
            array('name'=>'id','field'=>'id'),

        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $export =  getParameter('export','str',false);
        $condition = array(
                'courseId' =>   getParameter('courseId','int',false),
                'section' => getParameter('section','int',false),
                'startIndex' =>  getParameter('startIndex','int',false),
                'pageSize' =>  getParameter('pageSize','int',false),
            );

        $count =  D('Exercises_curriculum_tree_breviary')->getTreeCount($condition);
        $list =  D('Exercises_curriculum_tree_breviary')->getTreeList($condition);
        //transform data
        $list = $this->transData($header,$list);
        $data['iTotalRecords'] = $count;
        $data['iTotalDisplayRecords'] = $count;
        $data['aaData'] = array_column($list,'data');
        echo json_encode($data);
    }
    function curriculumSystemList(){
        $this->assign('parent', '查询与统计');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '>> 课标知识体系');
        $courses = D('Exercises_Course')->getCourseList();
        $sections = C('Studysection');
        $data = D('Exercises_Course')->getCurriculumTreeState($sections);
        $this->assign('data',$data);
        $this->assign('courseList',$courses);
        $this->assign('sections',$sections);
        $this->assign('sections1',$sections);
        $this->display();
    }
    function textbookSystemListPage(){
        $data = array();
        $header = array(
            array('name'=>'版本','field'=>'version_name'),
            array('name'=>'年级','field'=>'grade_name'),
            array('name'=>'分册','field'=>'school_term','callback'=>function($value){foreach(C('schoolTerm') as $key=>$val){ if($val['id'] == $value) return $val['name'];};return '' ;}),
            array('name'=>'学科','field'=>'course_name'),
            array('name'=>'状态','field'=>'creat_status','callback'=>function($value){if(TREE_FINISH == $value) return '完成';else if(TREE_CREATING == $value) return '创建中';}),
            array('name'=>'录入用户姓名','field'=>'creat_account'),
            array('name'=>'录入账号','field'=>'creat_name'),
            array('name'=>'id','field'=>'id'),
        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $export =  getParameter('export','str',false);
        $condition = array(
            'courseId' =>   getParameter('courseId','int',false),
            'gradeId' =>   getParameter('gradeId','int',false),
            'schoolTermId' =>   getParameter('schoolTermId','int',false),
            'versionId' => getParameter('versionId','int',false),
            'keyword' => getParameter('keyword','str',false),
            'startIndex' =>  getParameter('startIndex','int',false),
            'pageSize' =>  getParameter('pageSize','int',false),
        );

        $count =  D('Exercises_textbook_tree_breviary')->getTreeCount($condition);
        $list =  D('Exercises_textbook_tree_breviary')->getTreeList($condition);
        //transform data
        $list = $this->transData($header,$list);
        $data['iTotalRecords'] = $count;
        $data['iTotalDisplayRecords'] = $count;
        $data['aaData'] = array_column($list,'data');
        echo json_encode($data);
    }
    function textbookKnowledgeList()
    {
        $this->assign('parent', '查询与统计');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '>> 教材知识体系');
        $courses = D('Exercises_Course')->getCourseList();
        $grade = D('Dict_grade')->getGradeList(1);
        $schoolTermList = C('schoolTerm');
        $data = D('Exercises_textbook_version')->getAllVersions();
        $treeData = D('Exercises_Course')->getTextbookTreeState($schoolTermList);
        $gradeTermNum = count($treeData)/count($data);
        foreach($data as $key=>$val)
        {
            $data[$key]['data'] = array_slice($treeData,$key*$gradeTermNum,$gradeTermNum);
        }
        $this->assign('data',$data);
        $this->assign('courseList',$courses);
        $this->assign('schoolTermList',$schoolTermList);
        $this->assign('gradeList',$grade);
        $this->display();
    }
    /*习题管理*/
    public function queryQuestionsPage(){

        $data = array();
        $header = array(
            array('name'=>'试卷名称','field'=>'paper_name','callback'=>function($value){return $value;}),
            array('name'=>'试卷ID','field'=>'id','callback'=>function($value){return $value;}),
            array('name'=>'试卷性质','field'=>'paper_type','callback'=>function($value){foreach(C('paperCategory') as $key=>$val){ if($val['id'] == $value) return $val['name'];};return '' ;}),
            array('name'=>'省份','field'=>'province','callback'=>function($value){return $value;}),
            array('name'=>'年份','field'=>'year','callback'=>function($value){return $value;}),
            array('name'=>'试卷类型','field'=>'period','callback'=>function($value){foreach(C('questionCategory') as $key=>$val){ if($val['id'] == $value) return $val['name'];}return '';}),
            array('name'=>'年级','field'=>'grade_name'),
            array('name'=>'分册','field'=>'section','callback'=>function($value){foreach(C('schoolTerm') as $key=>$val){ if($val['id'] == $value) return $val['name'];}return '';}),
            array('name'=>'学科','field'=>'course_name','callback'=>function($value){return $value;}),
            array('name'=>'试卷模块数','field'=>'module_count'),
            array('name'=>'试卷总分','field'=>'score'),
            array('name'=>'录入账号','field'=>'creator_name'),
            array('name'=>'状态','field'=>'status','callback'=>function($value){foreach(C('exerciseState') as $key=>$val){ if($val['id'] == $value) return $val['name'];}return '';}),

        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $export =  getParameter('export','str',false);
        if($export)
            $condition = json_decode(htmlspecialchars_decode(getParameter('formCondition','str',false)),true);
        else
            $condition = array(
                'gradeId'  =>   getParameter('gradeId','int',false),
                'paperId'  =>   getParameter('paperId','int',false),
                'courseId' =>   getParameter('courseId','int',false),
                'paperName' => getParameter('keyword','str',false),
                'creator' => getParameter('creator','str',false),
                'section' => getParameter('section','int',false),
                'schoolTerm' => getParameter('schoolTermId','int',false),
                'provinceId' => getParameter('provinceId','int',false),
                'paperCategory' => getParameter('paperCategory','int',false),
                'year' => getParameter('year','int',false),
                'startIndex' =>  getParameter('startIndex','int',false),
                'pageSize' =>  getParameter('pageSize','int',false),
                'status' => getParameter('status'  ,'int',false),
            );

        $count =  D('Exercises_paper_processinfo')->getPaperCount($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
        $list =  D('Exercises_paper_processinfo')->getPaperList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
        if(!$export)
            $header[] = array('name'=>'操作','field'=>'id');


        //transform data


        $list = $this->transData($header,$list);
        if($export)
        {
            $fileName=date('Ymd').rand(0,1000).'.csv';
            $this->exportCSV($fileName,$header,array_column($list,'data'));
            exit;
        }
        $data['iTotalRecords'] = $count;
        $data['iTotalDisplayRecords'] = $count;
        $data['aaData'] = array_column($list,'data');
        echo json_encode($data);
    }

    /* 试卷管理*/
    function queryQuestions(){
        $this->assign('parent', '查询与统计');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '>> 试卷');
        $courses = D('Exercises_Course')->getCourseList();
        $grade = D('Dict_grade')->getGradeList(1);
        $sections = C('questionCategory');
        $province = D('Dict_citydistrict')->getProvince();
        $year = D('Exercises_create_paper')->getDistinctYear();
        $schoolTermList = C('schoolTerm');
        $statusList = C('exerciseState');
        $paperCategory = C('paperCategory');
        $newStatusList = array();
        foreach($statusList as $key=>$val)
        {
            if(!($val['id'] >= EXERCISE_STATE_WAITASSIGN && $val['id'] <= EXERCISE_STATE_FINISH) && $val['id'] != EXERCISE_STATE_PAPEREXERCISEDECLINE && $val['id'] != EXERCISE_STATE_PAPEREXERCISEWAITVERIFY)
            {
                $newStatusList[] = $statusList[$key];
            }
        }
        $this->assign('paperCategory',$paperCategory);
        $this->assign('statusList',$newStatusList);
        $this->assign('yearList',$year);
        $this->assign('courseList',$courses);
        $this->assign('gradeList',$grade);
        $this->assign('provinceList',$province);
        $this->assign('sections',$sections);
        $this->assign('schoolTermList',$schoolTermList);
        $this->display();
    }

    /*习题管理*/
    public function testQuestionsPage(){

        $data = array();
        $header = array(
            array('name'=>'序号','field'=>'rownum','callback'=>function($value){return $value;}),
            array('name'=>'试题ID','field'=>'id','callback'=>function($value){return $value;}),
            array('name'=>'学科','field'=>'course_name','callback'=>function($value){return $value;}),
            array('name'=>'年级','field'=>'grade_name'),
            array('name'=>'知识点','field'=>'knowledge_name'),
            array('name'=>'题型','field'=>'topic_name'),
            array('name'=>'所属试卷ID','field'=>'of_paper_id','callback'=>function($value){if($value != 0)return $value; else return '不属于试卷';}),
            array('name'=>'录入人员','field'=>'creator_name'),
            array('name'=>'标引人员','field'=>'marker_name'),
            array('name'=>'题目信息','field'=>'subject_name','callback'=>function($value){return $value;}),

        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $export =  getParameter('export','str',false);
        if($export)
            $condition = json_decode(htmlspecialchars_decode(getParameter('formCondition','str',false)),true);
        else
            $condition = array(
                'questionId'  =>   getParameter('questionId','int',false),
                'gradeId'  =>   getParameter('gradeId','int',false),
                'courseId' =>   getParameter('courseId','int',false),
                'keyword' => getParameter('keyword','str',false),
                'study_section' => getParameter('section','int',false),
                'creator' => getParameter('creator','str',false),
                'schoolTerm' => getParameter('schoolTermId','int',false),
                'marker' => getParameter('marker','str',false),
                'startIndex' =>  getParameter('startIndex','int',false),
                'pageSize' =>  getParameter('pageSize','int',false),
                'lock'=>getParameter('lock'  ,'int',false),
                'bNeedKnowledge' => true,
                'status' => getParameter('status'  ,'int',false),
                'isOfPaper' => EXERCISE_BEORNOT_OFPAPER
            );
        if($condition['status'] ==EXERCISE_STATE_REPROCESS)
            $condition['status'] = array('in',EXERCISE_STATE_REPROCESS . EXERCISE_STATE_PAPEREXERCISEDECLINE);
        if($condition['status'] ==EXERCISE_STATE_WAITVERIFY)
            $condition['status'] = array('in',EXERCISE_STATE_PAPEREXERCISEWAITVERIFY.EXERCISE_STATE_WAITVERIFY);
        $condition['userInfo'] = 1;
        $count =  D('Exercises_question_processinfo')->getQuestionCount($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
        $list =  D('Exercises_question_processinfo')->getQuestionList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
        if(!$export)
            $header[] = array('name'=>'操作','field'=>'is_lock,status,id',
                'callback'=>function($value)
                {
                    if($value[0] == LOCKSTATE_LOCK)
                        return 'unlock';
                    else if($value[1] == EXERCISE_STATE_ONSHELF)
                        return 'reject,remark,offshelf,publish,preview,delete,lock';
                    else
                        return 'reject,remark,onshelf,publish,preview,delete,lock';
                }
            );


        //transform data
        if (!empty($list)) {
            foreach ($list as $k=>$v) {
                $list[$k]['subject_name'] = html_entity_decode($v['subject_name']);
            }
        }

        $list = $this->transData($header,$list);
        if($export)
        {
            $fileName=date('Ymd').rand(0,1000).'.csv';
            $this->exportCSV($fileName,$header,array_column($list,'data'));
            exit;
        }
        $data['iTotalRecords'] = $count;
        $data['iTotalDisplayRecords'] = $count;
        $data['aaData'] = array_column($list,'data');
        echo json_encode($data);
    }

    /*习题管理*/
    function testQuestions(){//试题列表
        $this->assign('parent', '查询与统计');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '>> 试题');
        $courses = D('Exercises_Course')->getCourseList();
        $grade = D('Dict_grade')->getGradeList(1);
        $sections = C('Studysection');
        $schoolTermList = C('schoolTerm');
        $statusList = C('exerciseState');
        foreach($statusList as $key=>$val)
        {
            if($val['id'] !=EXERCISE_STATE_DRAFT && $val['id'] != EXERCISE_STATE_PAPEREXERCISEDECLINE && $val['id'] != EXERCISE_STATE_PAPEREXERCISEWAITVERIFY)
            {
                $newStatusList[] = $statusList[$key];
            }
        }
        $this->assign('statusList',$newStatusList);
        $this->assign('courseList',$courses);
        $this->assign('gradeList',$grade);
        $this->assign('sections',$sections);
        $this->assign('schoolTermList',$schoolTermList);
        $this->display();
    }

    function testEntryStatisticsPage()
    {
        $data = array();
        $header = array(
            array('name'=>'学段','field'=>'study_section','callback'=>function($value){foreach(C('Studysection') as $key=>$val){ if($val['id'] == $value) return $val['name'];};return '' ;}),
            array('name'=>'学科','field'=>'course_name'),
            array('name'=>'录入数量','field'=>'count'),
        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $condition = array(
            'courseId' =>   getParameter('courseId','int',false),
            'study_section' =>   getParameter('section','int',false),
            'startIndex' =>  getParameter('startIndex','int',false),
            'pageSize' =>  getParameter('pageSize','int',false),
        );

        $count =  D('Exercises_question_processinfo')->getExerciseInputStatisticCount($condition);
        $list =  D('Exercises_question_processinfo')->getExerciseInputStatisticList($condition);
        //transform data
        $list = $this->transData($header,$list);
        $data['iTotalRecords'] = $count;
        $data['iTotalDisplayRecords'] = $count;
        $data['aaData'] = array_column($list,'data');
        echo json_encode($data);
    }
    function testEntryStatistics(){
        $this->assign('parent', '查询与统计');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '>> 试题录入统计');
        $courses = D('Exercises_Course')->getCourseList();
        $sections = C('Studysection');
        $statisticsArray = array();
        $allCount = 0;
        foreach(C('Studysection') as $key=>$val){
            $condition = array(
                'study_section' =>   $val['id']
            );
            $count =  D('Exercises_question_processinfo')->getExerciseInputStatisticCountBySection($condition);
            $statisticsArray[] = array('name'=>$val['name'],'count'=>$count==''?0:$count);
            $allCount += $count;
        }
        $allStatisticInfo = array(array('name'=>'录入总数','count'=>$allCount));
        $statisticsArray = array_merge($allStatisticInfo,$statisticsArray);
        $this->assign('data',$statisticsArray);
        $this->assign('courseList',$courses);
        $this->assign('sections',$sections);
        $this->display();
    }
    function testAssignmentStatisticsPage()
    {
        $data = array();
        $header = array(
            array('name'=>'学段','field'=>'study_section','callback'=>function($value){foreach(C('Studysection') as $key=>$val){ if($val['id'] == $value) return $val['name'];};return '' ;}),
            array('name'=>'学科','field'=>'course_name'),
            array('name'=>'已分配','field'=>'assignedcount'),
            array('name'=>'未分配','field'=>'unassignedcount'),
            array('name'=>'标引完成','field'=>'markedcount'),
            array('name'=>'分配率','field'=>'assignratio','callback'=>function($value){return $value!='--'?$value.'%':$value;}),
            array('name'=>'标引完成率','field'=>'markratio','callback'=>function($value){return $value!='--'?$value.'%':$value;}),
        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $condition = array(
            'courseId' =>   getParameter('courseId','int',false),
            'study_section' =>   getParameter('section','int',false),
            'startIndex' =>  getParameter('startIndex','int',false),
            'pageSize' =>  getParameter('pageSize','int',false),
        );

        $count =  D('Exercises_question_processinfo')->getExerciseAssignStatisticCount($condition);
        $list =  D('Exercises_question_processinfo')->getExerciseAssignStatisticList($condition);
        //transform data
        $list = $this->transData($header,$list);
        $data['iTotalRecords'] = $count;
        $data['iTotalDisplayRecords'] = $count;
        $data['aaData'] = array_column($list,'data');
        echo json_encode($data);
    }
    function testAssignmentStatistics(){
        $this->assign('parent', '查询与统计');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '>> 试题分配统计');
        $courses = D('Exercises_Course')->getCourseList();
        $sections = C('Studysection');
        $info =  D('Exercises_question_processinfo')->getExerciseAssignStatistic();
        $header = array(
            array('name'=>'已分配：','field'=>'assignedcount'),
            array('name'=>'未分配：','field'=>'unassignedcount'),
            array('name'=>'标引完成：','field'=>'markedcount'),
            array('name'=>'分配率：','field'=>'assignratio','callback'=>function($value){return $value!='--'?$value.'%':$value;}),
            array('name'=>'标引完成率：','field'=>'markratio','callback'=>function($value){return $value!='--'?$value.'%':$value;}),
        );
        $list = $this->transData($header,$info);
        $i=0;
        foreach($list[0]['data'] as $key=>$val)
        {
            $list[0]['data'][$key] =$header[$i++]['name'] . $list[0]['data'][$key];
        }
        $this->assign('data',$list[0]['data']);
        $this->assign('courseList',$courses);
        $this->assign('sections',$sections);
        $this->display();
    }



    function itemIndexingStatisticsPage()
    {
        $data = array();
        $header = array(
            array('name'=>'版本','field'=>'version_name'),
            array('name'=>'年级','field'=>'grade_name'),
            array('name'=>'学科','field'=>'course_name'),
            array('name'=>'分册','field'=>'school_term','callback'=>function($value){foreach(C('schoolTerm') as $key=>$val){ if($val['id'] == $value) return $val['name'];};return '' ;}),
            array('name'=>'数量','field'=>'count'),
        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $condition = array(
            'courseId' =>   getParameter('courseId','int',false),
            'gradeId' =>   getParameter('gradeId','int',false),
            'study_section' =>   getParameter('section','int',false),
            'schoolTerm' =>   getParameter('schoolTermId','int',false),
            'startIndex' =>  getParameter('startIndex','int',false),
            'pageSize' =>  getParameter('pageSize','int',false),
        );

        $count =  D('Exercises_question_processinfo')->getExerciseMarkStatisticCount($condition);
        $list =  D('Exercises_question_processinfo')->getExerciseMarkStatisticList($condition);
        //transform data
        $list = $this->transData($header,$list);
        $data['iTotalRecords'] = $count;
        $data['iTotalDisplayRecords'] = $count;
        $data['aaData'] = array_column($list,'data');
        echo json_encode($data);
    }
    function itemIndexingStatistics(){
        $this->assign('parent', '查询与统计');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '>> 试题标引统计');
        $chartList = array(array(1,2,3),array(1,2,3),array(1,2,3),array(1,2,3));
        $courses = D('Exercises_Course')->getCourseList();
        $sections = C('Studysection');
        $schoolTermList = C('schoolTerm');
        $grade = D('Dict_grade')->getGradeList();
        $this->assign('schoolTermList',$schoolTermList);
        $this->assign('courseList',$courses);
        $this->assign('gradeList',$grade);
        $this->assign('sections',$sections);
        //group by publisher
        $chartData = array();
        $chartData[] = array('title'=>'按出版社统计','data' => D('Exercises_createexercise')->getExercisesGroupCount('publisher'));
        $chartData[] = array('title'=>'按年级统计','data' => D('Exercises_createexercise')->getExercisesGroupCount('grade'));
        $chartData[] = array('title'=>'按学科统计','data' => D('Exercises_createexercise')->getExercisesGroupCount('course'));

        $dataTemp = D('Exercises_createexercise')->getExercisesGroupCount('difficulty');
        $allCount = array_reduce(array_column($dataTemp,'count'),function($a,$b){ $a+=$b; return $a; });
        $chartSubData = array();
        foreach(C('difficulty') as $key=>$val)
        {
            $currentData = array('name'=>$val['name'],'count'=>0);
            foreach($dataTemp as $dataKey => $dataVal)
            {
                if($val['id'] == $dataVal['name'])
                {
                  $currentData['count'] = $dataTemp[$dataKey]['count'];
                  break;
                }
            }
            $chartSubData[] = $currentData;
        }
        $chartData[] = array('title'=>'按难度统计','data' => $chartSubData);
        //group by grade
        //group by course
        //group by difficulty
        $this->assign('chartsList',$chartData);
        $this->assign('allCount',$allCount);
        $this->display();
    }

    public function userListPage(){

        $data = array();
        $header = array(
            array('name'=>'用户姓名','field'=>'user_name'),
            array('name'=>'账号','field'=>'account'),
            array('name'=>'角色','field'=>'role_name'),
            array('name'=>'手机号','field'=>'mobile_phone','callback'=>function($value){return $value;}),
            array('name'=>'录入习题数目','field'=>'inputexercisecount'),
            array('name'=>'录入试卷数目','field'=>'inputpapercount'),
            array('name'=>'标引习题数目','field'=>'markexercisecount'),
            array('name'=>'录入合格率','field'=>'inputrightratio','callback'=>function($value){return $value!='--'?$value.'%':$value;}),
            array('name'=>'标引合格率','field'=>'markrightratio','callback'=>function($value){return $value!='--'?$value.'%':$value;}),
            array('name'=>'账号状态','field'=>'account_status','callback'=>function($value){if(ACCOUNT_STATUS_NORMAL == $value) return '已启用';else return '已禁用';}),
        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $export =  getParameter('export','str',false);
        if($export)
            $condition = json_decode(htmlspecialchars_decode(getParameter('formCondition','str',false)),true);
        else
            $condition = array(
                'roleId'  =>   getParameter('roleId','int',false),
                'userName' => getParameter('userName','str',false),
                'account' => getParameter('account','str',false),
                'telephone' => getParameter('telephone','str',false),
                'startIndex' =>  getParameter('startIndex','int',false),
                'pageSize' =>  getParameter('pageSize','int',false),
            );
        if(intval(session('admin.id')) != 1)
            $condition['nid'] = 1;
        $count =  D('Exercises_account')->getUserExerciseInfoCount($condition);
        $list =  D('Exercises_account')->getUserExerciseInfoList($condition);

        if(!$export)
            $header[] = array('name'=>'操作','field'=>'id,account_status','callback'=>function($value){if(ACCOUNT_STATUS_NORMAL == $value[1])return array('id'=>$value[0],'state'=>'disable');else return array('id'=>$value[0],'state'=>'enable');});
        //transform data
        if(intval(session('admin.id')) != 1 && $list[0]['id'] == 1) //admin id equals 1
        {
            unset($list[0]);
        }
        $list = $this->transData($header,$list);
        if($export)
        {
            $fileName=date('Ymd').rand(0,1000).'.csv';
            $this->exportCSV($fileName,$header,array_column($list,'data'));
            exit;
        }
        $data['iTotalRecords'] = $count;
        $data['iTotalDisplayRecords'] = $count;
        $data['aaData'] = array_column($list,'data');
        echo json_encode($data);
    }

    public function userInfoByDayPage(){

        $data = array();
        $header = array(
            array('name'=>'录入习题数','field'=>'inputexercisecount'),
            array('name'=>'录入试卷数','field'=>'inputpapercount'),
            array('name'=>'标引习题数','field'=>'markexercisecount'),
            array('name'=>'审核习题通过数','field'=>'verifyexercisecount'),
            array('name'=>'审核习题不通过数','field'=>'rejectexercisecount'),
            array('name'=>'重新标引数','field'=>'remarkexercisecount'),
            array('name'=>'返工录入数','field'=>'reprocessexercisecount'),
            array('name'=>'录入合格率','field'=>'inputrightratio','callback'=>function($value){return $value != '--' ? $value.'%' : $value;}),
            array('name'=>'标引合格率','field'=>'markrightratio','callback'=>function($value){return $value != '--' ? $value.'%' : $value;}),
            array('name'=>'日期','field'=>'basedate'),
        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $condition = array(
            'startDate'  =>   getParameter('startDate','str',false),
            'endDate' => getParameter('endDate','str',false),
            'startIndex' =>  getParameter('startIndex','int',false),
            'pageSize' =>  getParameter('pageSize','int',false),
            'userId' => getParameter('userId','int',false),
        );

        $count =  D('Exercises_account')->getUserStatisticCount($condition);
        $list =  D('Exercises_account')->getUserStatisticList($condition);

        //transform data
        $list = $this->transData($header,$list);

        $data['iTotalRecords'] = $count;
        $data['iTotalDisplayRecords'] = $count;
        $data['aaData'] = array_column($list,'data');
        echo json_encode($data);
    }

    public function userBehaviorPage(){

        $data = array();
        $header = array(
            array('name'=>'时间','field'=>'oper_time'),
            array('name'=>'操作','field'=>'oper_name'),
            array('name'=>'IP','field'=>'ip'),
            array('name'=>'操作习题ID','field'=>'question_id'),
            array('name'=>'操作试卷ID','field'=>'paper_id'),
        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $condition = array(
            'startDate'  =>   getParameter('startDate','str',false),
            'endDate' => getParameter('endDate','str',false),
            'behavior' => getParameter('behavior','str',false),
            'startIndex' =>  getParameter('startIndex','int',false),
            'pageSize' =>  getParameter('pageSize','int',false),
            'userId' => getParameter('userId','int',false),
        );

        $count =  D('Exercises_log')->getUserLogCount($condition);
        $list =  D('Exercises_log')->getUserLogList($condition);

        //transform data
        $list = $this->transData($header,$list);

        $data['iTotalRecords'] = $count;
        $data['iTotalDisplayRecords'] = $count;
        $data['aaData'] = array_column($list,'data');
        echo json_encode($data);
    }

    function userList(){
        $this->assign('parent', '查询与统计');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '>> 用户');
        $roleList = D('Exercises_auth_permissions')->getRoleList();
        $this->assign('roleList',$roleList);
        $this->display();
    }

    function userBehaviorDetails(){
        $this->assign('parent', '查询与统计');
        $this->assign('parentHref', U('QueryStatistics/userList'));
        $this->assign('parentTwo', ' >> 用户');
        $this->assign('parentTwoHref', U('QueryStatistics/userList'));
        $this->assign('own', ' >> 查看行为');
        $userId = getParameter("id",'int');
        $behaviorList = D('Exercises_log')->getDistinctBehavior($userId);
        $userInfo = D('Exercises_account')->getUserInfo($userId);
        $this->assign('userInfo',$userInfo);
        $this->assign('behaviorList',$behaviorList);
        $this->display();
    }

    /*
     * 天粒度执行用户统计数据清洗
     */
    function userBehaviorETL_Day()
    {
        $date = date('Y-m-d',time()-86400);
        $userIdArray = D('Exercises_account')->getAllUserId();
        D('Exercises_account')->etlOfDayStatisticData($userIdArray,$date);
    }

    function questionsDetails()
    {
        $this->assign('parent', '查询与统计');
        $this->assign('parentHref', U('QueryStatistics/testQuestions'));
        $this->assign('parentTwo', ' >> 试题');
        $this->assign('parentTwoHref', U('QueryStatistics/testQuestions'));
        $this->assign('own', ' >> 查看详情');
        $id = getParameter('id','int');
        $difficulty = D('Exercises_createexercise')->getExerciseDifficulty($id);
        $condition['questionId'] = $id;
        $questionInfo = D('Exercises_question_processinfo')->getQuestionList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition)[0];
        $questionProcessInfo = D('Exercises_question_processinfo')->getQuestionInfo($id);
        $difficultyList = C('difficulty');
        $difficultyName = '';
        $state = '';

        foreach(C('exerciseState') as $key=>$val)
        {
            if($val['id'] == $questionInfo['status'])
                $state = $val['name'];
        }
        foreach(C('Studysection') as $key=>$val) {
            if ($val['id'] == $questionInfo['study_section'])
                $questionInfo['section_name'] =  $val['name'];
        }
        foreach(C('SOURCE') as $key=>$val) {
            if ($val['id'] == $questionInfo['exercise_source'])
                $questionInfo['source_name'] =  $val['name'];
        }
        $logList = D('Exercises_log')->getQuestionLogWithAccount($id);
        $paperId = D('Exercises_parper_concat')->getPaperIdByQuestionId($id);
        $knowledgeList = D('Exercises_textbook_tree_info_createexercise')->getKnowledgeListByExerciseId($id);

        foreach($knowledgeList as $key1=>$val1) {
            foreach ($difficultyList as $key2 => $val2) {
                if ($val1['difficulty'] == $val2['id'])
                    $knowledgeList[$key1]['difficulty'] = $val2['name'];
            }
        }
        $fasId = D('Exercises_school_term')->getIdByGradeTerm($knowledgeList[0]['gradeid'],$knowledgeList[0]['school_term']);
        $curKnowledge = array();
        foreach($knowledgeList as $key=>$val)
        {
            foreach(C('schoolTerm') as $index=>$value)
            { if($value['id'] == $val['school_term'])  $knowledgeList[$key]['school_term'] = $value['name'];}
            //TODO:获取一级、二级、。。。知识点ID及其对应VERSION COURSE 分册
            $result  = D('Exercises_curriculum_tree_breviary')->getTreeInfoChain($val['knowledge_id']);
            foreach($result as $key1=>$val1) {
                foreach (C('Studysection') as $index => $value) {
                    if ($value['id'] == $val1['learning_period_id'])
                        $result[$key1]['lpName'] = $value['name'];
                }
            }
            $curKnowledge[] = $result;
        }
        $versionData = D('Exercises_textbook_version')->getAllVersions();
        $difficulty = C('difficulty');
        $this->assign('difficultyList',$difficulty);
        $this->assign('versionData',$versionData);
        $this->assign('fasciculeId',$fasId['id']);
        $this->assign('difficulty',$difficultyName);
        $this->assign('state',$state);
        $this->assign('knowledgeList',$knowledgeList);
        $this->assign('curKnowledge',$curKnowledge);
        $this->assign('questionInfo',$questionInfo);
        $this->assign('questionProcessInfo',$questionProcessInfo);
        $this->assign('questionPaperInfo',$paperId);
        $this->assign('logList',$logList);
        $this->display();
    }
    function examinationPaperDetails(){
        $this->assign('parent', '查询与统计');
        $this->assign('parentHref', U('QueryStatistics/queryQuestions'));
        $this->assign('parentTwo', ' >> 试卷');
        $this->assign('parentTwoHref', U('QueryStatistics/queryQuestions'));
        $this->assign('own', ' >> 查看详情');
        $id = getParameter('id','int');
        $paperInfo = D('Exercises_create_paper')->getPaperInfo( $id );




        $logList = D('Exercises_log')->getPaperLogWithAccount($id);
        $bigQuestionList = D('Exercises_paper_bigquestion')->getPaperBigQuestion($id);


        foreach($bigQuestionList as $key=>$val)
        {
           foreach(C('exercisesType') as $typeIndex=>$typeValue)
           {
               if($typeValue['id'] == $val['template_id'])
               {
                   $bigQuestionList[$key]['template'] = $typeValue['name'];
                   break;
               }
           }
           //获取小题信息
            $questionList = D('Exercises_parper_concat')->getQuestionsByBigQuestionId($val['id']);
            $bigQuestionList[$key]['data'] = $questionList;
            $bigQuestionList[$key]['big_topic_num'] = count($questionList);
        }
        $this->assign('paperinfo',$paperInfo);
        $this->assign('logList',$logList);
        $this->assign('bigQuestionList',$bigQuestionList);
        $this->display();
    }
}
