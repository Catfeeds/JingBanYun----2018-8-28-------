<?php
namespace Exercise\Controller;
use Think\Controller;
use Think\Verify;

class PublishPlatformController extends ExerciseGlobalController
{

    public $model;
    public $page_size=20;
                //permissions
    public function __construct() {
        parent::__construct();
        $this->model=D('Auth_admin');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }

    public function platformMgmtPage(){

        $data = array();
        $header = array(
            array('name'=>'序号','field'=>'rownum','callback'=>function($value){return $value;}),
            array('name'=>'平台ID','field'=>'id','callback'=>function($value){return $value;}),
            array('name'=>'平台名称','field'=>'name','callback'=>function($value){return $value;}),
            array('name'=>'服务类型','field'=>'type','callback'=>function($value){
             if(PLATFORM_AFFORD == $value)
                 return '获取';
             else if(PLATFORM_GET == $value)
                 return '提供';
        })

        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $export =  getParameter('export','str',false);
        if($export)
            $condition = json_decode(htmlspecialchars_decode(getParameter('formCondition','str',false)),true);
        else
            $condition = array(
                'platformId'  =>   getParameter('platformId','int',false),
                'keyword'  =>   getParameter('keyword','str',false),
            );
        $count =  D('Exercises_platform')->getPlatformCount($condition);
        $list =  D('Exercises_platform')->getPlatformList($condition);
        if(!$export) {
            $header[] = array('name' => '操作', 'field' => 'type,id',
                'callback' => function ($value) {
                    if($value[0] == PLATFORM_GET)
                    return 'viewResource,viewConfig,publishResource';
                    else
                    return 'viewConfig';
                }
            );
            $header[] = array('name'=>'操作','field'=>'startip,endip',
                'callback'=>function($value)
                {
                    return long2ip($value[0]).' , '.long2ip($value[1]);
                }
            );
        }


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

    function platformMgmt(){
        $this->assign('parent', '发布平台管理');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '');
        $this->display();
    }
    public function exerciseEnteringPage()
    {
        $data = array();
        $header = array(
            array('name' => '序号', 'field' => 'rownum', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '试题ID', 'field' => 'id', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '学科', 'field' => 'course_name', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '年级', 'field' => 'mark_grade'),
            array('name' => '题型', 'field' => 'topic_name'),
            array('name'=>'题目信息','field'=>'subject_name','callback'=>function($value){return $value;}),
            array('name'=>'授权时间','field'=>'authtime','callback'=>function($value){return $value;}),
            array('name'=>'id','field'=>'pubid'),
        );
        $data['sEcho'] = getParameter('sEcho', 'str', false);
        $export = getParameter('export', 'str', false);
        if ($export)
            $condition = json_decode(htmlspecialchars_decode(getParameter('formCondition', 'str', false)), true);
        else
            $condition = array(
                'questionId' => getParameter('questionId', 'str', false),
                'paperId'  =>   getParameter('paperId','str',false),
                'courseId' => getParameter('courseId', 'int', false),
                'exerciseCategory' => getParameter('exerciseCategory', 'int', false),
                'gradeId'  =>   getParameter('gradeId','int',false),
                'keyword' => getParameter('keyword', 'str', false),
                'paperName' => getParameter('paperName'  ,'str',false),
                'study_section' => getParameter('section', 'int', false),
                'authStartTime' => getParameter('authStartTime', 'str', false),
                'authEndTime' => getParameter('authEndTime', 'str', false),
                'publishStatus' => getParameter('publishStatus', 'int', false),
                'platformId' => getParameter('platformId', 'int', false),
                'startIndex' => getParameter('startIndex', 'int', false),
                'pageSize' => getParameter('pageSize', 'int', false),
            );
        $condition['bNeedKnowledge'] = true;
        $condition['publishNotExpired'] = 1;
        $count = D('Exercises_question_processinfo')->getQuestionCount($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition,'publish_time');
        $list = D('Exercises_question_processinfo')->getQuestionList($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition,'publish_time');
        //transform data
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $list[$k]['subject_name'] = html_entity_decode($v['subject_name']);
            }
        }
        if(!$export)
            $header[] = array('name' => '试题ID', 'field' => 'id' );
        $list = $this->transData($header, $list);
        if ($export) {
            $fileName = date('Ymd') . rand(0, 1000) . '.csv';
            $this->exportCSV($fileName, $header, array_column($list, 'data'));
            exit;
        }
        $data['iTotalRecords'] = $count;
        $data['iTotalDisplayRecords'] = $count;
        $data['aaData'] = array_column($list, 'data');
        echo json_encode($data);
    }

    function testEnteringPage($formCondition=array(),$requestFromWeb=1)
    {
        $data = array();
        $header = array(
            array('name'=>'序号','field'=>'rownum','callback'=>function($value){return $value;}),
            array('name'=>'试卷ID','field'=>'id','callback'=>function($value){return $value;}),
            array('name'=>'学科','field'=>'course_name','callback'=>function($value){return $value;}),
            array('name'=>'分类','field'=>'period','callback'=>function($value){foreach(C('questionCategory') as $key=>$val){ if($val['id'] == $value) return $val['name'];};return '' ;}),
            array('name'=>'年级','field'=>'grade_name','callback'=>function($value){return $value;}),
            array('name'=>'省份','field'=>'province','callback'=>function($value){return $value;}),
            array('name'=>'试卷名称','field'=>'paper_name','callback'=>function($value){return $value;}),
            array('name'=>'试卷类型','field'=>'paper_type','callback'=>function($value){foreach(C('paperCategory') as $key=>$val){ if($val['id'] == $value) return $val['name'];};return '' ;}),
            array('name'=>'年份','field'=>'year'),
            array('name'=>'录入人员','field'=>'creator_name'),
            array('name'=>'授权时间','field'=>'authtime','callback'=>function($value){return $value;}),
            array('name'=>'id','field'=>'pubid'),
        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $export =  getParameter('export','str',false);
        if($export)
            $condition = json_decode(htmlspecialchars_decode(getParameter('formCondition','str',false)),true);
        else
            $condition = array(
                'paperId'  =>   getParameter('paperId','str',false),
                'courseId' =>   getParameter('courseId','int',false),
                'gradeId'  =>   getParameter('gradeId','int',false),
                'paperName' => getParameter('paperName','str',false),
                'section' => getParameter('section', 'int', false),
                'keyword' => getParameter('keyword', 'str', false),
                'provinceId' => getParameter('provinceId','str',false),
                'paperCategory' => getParameter('paperCategory','str',false),
                'authStartTime' => getParameter('authStartTime', 'str', false),
                'authEndTime' => getParameter('authEndTime', 'str', false),
                'publishStatus' => getParameter('publishStatus', 'int', false),
                'platformId' => getParameter('platformId', 'int', false),
                'year' => getParameter('year','str',false),
                'startIndex' =>  getParameter('startIndex','int',false),
                'pageSize' =>  getParameter('pageSize','int',false),
                'cat' => getParameter('cat'  ,'str',false)

            );
        $condition['bNeedKnowledge'] = true;
        $condition['publishNotExpired'] = 1;
        $count =  D('Exercises_paper_processinfo')->getPaperCount($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition,'publish_time');
        $list =  D('Exercises_paper_processinfo')->getPaperList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition,'publish_time');

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

    function resourceList(){
        $this->assign('parent', '发布平台管理');
        $this->assign('parentHref', U('PublishPlatform/platformMgmt'));
        $this->assign('own', ' >> 查看发布资源清单');

        $platform = D('Exercises_platform')->getPlatformListByType(PLATFORM_GET);
        $courses = D('Exercises_Course')->getCourseList();
        $grade = D('Dict_grade')->getGradeList(1);
        $id =  getParameter('id','int');

        $this->assign('platformId',$id);
        $this->assign('platform',$platform);
        $this->assign('courseList',$courses);
        $this->assign('gradeList',$grade);
        $this->display();
    }
    function publishResource(){
        $this->assign('parent', '发布平台管理');
        $this->assign('parentHref', U('PublishPlatform/platformMgmt'));
        $this->assign('own', ' >> 发布资源');
        $courses = D('Exercises_Course')->getCourseList();
        $id =  getParameter('id','int');
        $platform = D('Exercises_platform')->getPlatformListByType(PLATFORM_GET);
        $grade = D('Dict_grade')->getGradeList(1);
        $this->assign('courseList',$courses);
        $this->assign('gradeList',$grade);
        $this->assign('platformId',$id);
        $this->assign('platform',$platform);
        $this->display();
    }

    public function exercisePublishPage()
    {
        $data = array();
        $header = array(
            array('name' => '序号', 'field' => 'rownum', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '试题ID', 'field' => 'id', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '学科', 'field' => 'course_name', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '年级', 'field' => 'mark_grade'),
            array('name' => '题型', 'field' => 'topic_name'),
            array('name'=>'题目信息','field'=>'subject_name','callback'=>function($value){return $value;}),
        );

        $data['sEcho'] = getParameter('sEcho', 'str', false);
        $export = getParameter('export', 'str', false);
        if(!$export)
            $header[] =  array('name' => '是否可发布', 'field' => 'canpublish,is_lock','callback'=>function($value){if($value[1] == LOCKSTATE_LOCK)return 2;return $value[0];});

        if ($export)
            $condition = json_decode(htmlspecialchars_decode(getParameter('formCondition', 'str', false)), true);
        else
            $condition = array(
                'questionId' => getParameter('questionId', 'str', false),
                'paperId'  =>   getParameter('paperId','str',false),
                'courseId' => getParameter('courseId', 'int', false),
                'exerciseCategory' => getParameter('exerciseCategory', 'int', false),
                'gradeId'  =>   getParameter('gradeId','int',false),
                'keyword' => getParameter('keyword', 'str', false),
                'paperName' => getParameter('paperName'  ,'str',false),
                'startIndex' => getParameter('startIndex', 'int', false),
                'pageSize' => getParameter('pageSize', 'int', false),
                'mixPlatformId' => getParameter('platformId','str',false)
            );
        $condition['bNeedKnowledge'] = true;
        $condition['status'] = array('gt',EXERCISE_STATE_UNINBOUND);
        $count = D('Exercises_question_processinfo')->getQuestionCount($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition);
        $list = D('Exercises_question_processinfo')->getQuestionList($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition);

        //transform data
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $list[$k]['subject_name'] = html_entity_decode($v['subject_name']);
            }
        }

        $list = $this->transData($header, $list);
        if ($export) {
            $fileName = date('Ymd') . rand(0, 1000) . '.csv';
            $this->exportCSV($fileName, $header, array_column($list, 'data'));
            exit;
        }
        $data['iTotalRecords'] = $count;
        $data['iTotalDisplayRecords'] = $count;
        $data['aaData'] = array_column($list, 'data');
        echo json_encode($data);
    }

    function testPublishPage($formCondition=array(),$requestFromWeb=1)
    {
        $data = array();
        $header = array(
            array('name'=>'序号','field'=>'rownum','callback'=>function($value){return $value;}),
            array('name'=>'试卷ID','field'=>'id','callback'=>function($value){return $value;}),
            array('name'=>'学科','field'=>'course_name','callback'=>function($value){return $value;}),
            array('name'=>'年级','field'=>'grade_name','callback'=>function($value){return $value;}),
            array('name'=>'试卷名称','field'=>'paper_name','callback'=>function($value){return $value;}),
            array('name'=>'试卷类型','field'=>'paper_type','callback'=>function($value){foreach(C('paperCategory') as $key=>$val){ if($val['id'] == $value) return $val['name'];};return '' ;}),

        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $export =  getParameter('export','str',false);
        if(!$export)
            $header[] =  array('name' => '是否可发布', 'field' => 'canpublish,is_lock','callback'=>function($value){if($value[1] == LOCKSTATE_LOCK)return 2;return $value[0];});
        if($export)
            $condition = json_decode(htmlspecialchars_decode(getParameter('formCondition','str',false)),true);
        else
            $condition = array(
                'paperId'  =>   getParameter('paperId','str',false),
                'courseId' =>   getParameter('courseId','int',false),
                'gradeId'  =>   getParameter('gradeId','int',false),
                'paperName' => getParameter('paperName','str',false),
                'section' => getParameter('section', 'int', false),
                'keyword' => getParameter('keyword', 'str', false),
                'year' => getParameter('year','str',false),
                'startIndex' =>  getParameter('startIndex','int',false),
                'pageSize' =>  getParameter('pageSize','int',false),
                'mixPlatformId' => getParameter('platformId','str',false)
            );
        $condition['status'] = array('gt',EXERCISE_STATE_UNINBOUND);
        $count =  D('Exercises_paper_processinfo')->getPaperCount($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
        $list =  D('Exercises_paper_processinfo')->getPaperList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);

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

    function publishExercise()
    {
        $condition = json_decode(htmlspecialchars_decode(getParameter('formCondition','str',false)),true);
        $list =  D('Exercises_question_processinfo')->getQuestionList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
        $ids = implode(',',array_column($list,'id'));
        A('Exercise/ExerciseState')->publishExercise($condition['platformId'],$ids,$condition['startTime'],$condition['endTime']);
    }

    function publishPaper()
    {
        $condition = json_decode(htmlspecialchars_decode(getParameter('formCondition','str',false)),true);
        $list =  D('Exercises_paper_processinfo')->getPaperList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
        $ids = implode(',',array_column($list,'id'));
        A('Exercise/ExerciseState')->publishPaper($condition['platformId'],$ids,$condition['startTime'],$condition['endTime']);
    }

    public function deletePublish()
    {
        $id = getParameter('id','int');
        $result = D('Exercises_platform')->deletePublish($id);
        if($result)
            $this->showMessage(200,"删除成功");
        else
            $this->showMessage(500,"删除失败");
    }

}
