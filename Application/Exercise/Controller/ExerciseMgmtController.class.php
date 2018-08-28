<?php
namespace Exercise\Controller;
use Think\Controller;
use Think\Verify;

class ExerciseMgmtController extends ExerciseGlobalController
{

    public $model;
    public $page_size=20;
                //permissions
    public function __construct() {
        parent::__construct();
    }

    public function exerciseMgmtPage(){

        $data = array();
        $header = array(
            array('name'=>'序号','field'=>'rownum','callback'=>function($value){return $value;}),
            array('name'=>'试题ID','field'=>'id','callback'=>function($value){return $value;}),
            array('name'=>'学科','field'=>'course_name','callback'=>function($value){return $value;}),
            array('name'=>'年级','field'=>'grade_name'),
            array('name'=>'题型','field'=>'topic_name'),
            array('name'=>'题目信息','field'=>'subject_name','callback'=>function($value){return $value;}),
            array('name'=>'锁定状态','field'=>'is_lock','callback'=>function($value){if($value[0] != LOCKSTATE_NORMAL)return '是'; else return '否';}),
            array('name'=>'上架/下架','field'=>'status','callback'=>function($value){foreach(C('exerciseState') as $key=>$val){ if($val['id'] == $value) return $val['name'];}return '';}),

        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $export =  getParameter('export','str',false);
        if($export)
            $condition = json_decode(htmlspecialchars_decode(getParameter('formCondition','str',false)),true);
        else
            $condition = array(
                'questionId'  =>   getParameter('questionId','int',false),
                'paperId'  =>   getParameter('paperId','int',false),
                'gradeId'  =>   getParameter('gradeId','int',false),
                'courseId' =>   getParameter('courseId','int',false),
                'exerciseCategory' => getParameter('exerciseCategory','int',false),
                'keyword' => getParameter('keyword','str',false),
                'startIndex' =>  getParameter('startIndex','int',false),
                'pageSize' =>  getParameter('pageSize','int',false),
                'paperName' => getParameter('paperName'  ,'str',false),
                'lock'=>getParameter('lock'  ,'int',false),
                'bNeedKnowledge' => true,

            );
        $condition['status'] =getParameter('upDownShelfStatus'  ,'int',false)==0 ? array('in',''.EXERCISE_STATE_ONSHELF.','.EXERCISE_STATE_OFFSHELF.','.EXERCISE_STATE_UNONSHELF.'') : getParameter('upDownShelfStatus'  ,'int',false);
        $condition['exerciseMainCategory'] = -1;
        $count =  D('Exercises_question_processinfo')->getQuestionCount($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition,'inbound_time');
        $list =  D('Exercises_question_processinfo')->getQuestionList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition,'inbound_time');
        if(!$export)
            $header[] = array('name'=>'操作','field'=>'is_lock,status,id',
                               'callback'=>function($value)
                                {
                                  if($value[0] == LOCKSTATE_LOCK)
                                    return 'unlock,preview';
                                  else if($value[1] == EXERCISE_STATE_ONSHELF)
                                    return 'offshelf,publish,preview';
                                  else
                                    return 'reject,remark,onshelf,publish,preview,delete,lock';
                                }
                               );
        else
          unset($header[6]);

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

    function exerciseMgmt(){
        $this->assign('parent', '试题管理');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '');
        $courses = D('Exercises_Course')->getCourseList();
        $grade = D('Dict_grade')->getGradeList(1);
        $platform = D('Exercises_platform')->getPlatformListByType(PLATFORM_GET);
        $this->assign('courseList',$courses);
        $this->assign('gradeList',$grade);
        $this->assign('platform',$platform);
        $this->display();
    }

    function exerciseMgmtDetails(){
        $this->display();
    }

}
