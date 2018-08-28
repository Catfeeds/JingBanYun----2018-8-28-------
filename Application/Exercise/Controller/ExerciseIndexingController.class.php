<?php
namespace Exercise\Controller;

class ExerciseIndexingController extends ExerciseGlobalController
{


    public $model;
    public $page_size=20;
    private $userInfo;

    public $getCourseList;
    public $Create_Exercise;
    public $Exercises_question_processinfo;
    public $Exercises_Log;
    public $City;
    public $paper;
    public $Exercises_paper_processinfo;
    public $Exercises_paper_bigquestion;
    public $Exercises_parper_concat;

    public function __construct() {
        parent::__construct();
        $this->model=D('Auth_admin');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
        $this->Create_Exercise = D('Create_Exercise');
        $this->userInfo = $this->getUserRoleAuth();

        $this->getCourseList = D('Exercises_Course');
        $this->Exercises_question_processinfo = D('Exercises_question_processinfo');
        $this->Exercises_Log = D('Exercises_log');
        $this->City = D('Dict_citydistrict');
        $this->paper = D('Exercises_create_paper');
        $this->Exercises_paper_processinfo = D('Exercises_paper_processinfo');
        $this->Exercises_paper_bigquestion = D('Exercises_paper_bigquestion');
        $this->Exercises_parper_concat = D('Exercises_parper_concat');
    }

    public function exerciseEnteringPage(){

        $data = array();
        $header = array(
            array('name'=>'序号','field'=>'rownum','callback'=>function($value){return $value;}),
            array('name'=>'试题ID','field'=>'id','callback'=>function($value){return $value;}),
            array('name'=>'学科','field'=>'course_name','callback'=>function($value){return $value;}),
            array('name'=>'学段','field'=>'study_section','callback'=>function($value){foreach(C('Studysection') as $key=>$val){ if($val['id'] == $value) return $val['name'];}return '';}),
            array('name'=>'题型','field'=>'topic_name'),
            array('name'=>'试题状态','field'=>'status','callback'=>function($value){foreach(C('exerciseState') as $key=>$val){ if($val['id'] == $value) return $val['name'];}return '';}),
            array('name'=>'锁定状态','field'=>'is_lock','callback'=>function($value){if($value[0] != LOCKSTATE_NORMAL)return '是'; else return '否';}),
            array('name'=>'题目信息','field'=>'subject_name','callback'=>function($value){return $value;}),
            array('name'=>'上一步操作人','field'=>'lastoperator_name'),
        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $export =  getParameter('export','str',false);
        if($export)
            $condition = json_decode(htmlspecialchars_decode(getParameter('formCondition','str',false)),true);
        else
            $condition = array(
                'questionId'  =>   getParameter('questionId','int',false),
                'paperId'  =>   getParameter('paperId','int',false),
                'courseId' =>   getParameter('courseId','int',false),
                'exerciseCategory' => getParameter('exerciseCategory','int',false),
                'keyword' => getParameter('keyword','str',false),
                'study_section' => getParameter('section','int',false),
                'creatorId' => getParameter('creatorId','int',false),
                'inputStartTime' => getParameter('inputStartTime','str',false),
                'inputEndTime' => getParameter('inputEndTime','str',false),
                'startIndex' =>  getParameter('startIndex','int',false),
                'pageSize' =>  getParameter('pageSize','int',false),
                'paperName' => getParameter('paperName'  ,'str',false),
                'lock'=>getParameter('lock'  ,'int',false),
            );
           $condition['status'] =getParameter('status'  ,'int',false)===false ? array('in',EXERCISE_STATE_WAITASSIGN.','.EXERCISE_STATE_REASSIGN) : getParameter('status'  ,'int',false);

           $count =  D('Exercises_question_processinfo')->getQuestionCount($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
           $list =  D('Exercises_question_processinfo')->getQuestionList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
           if(!$export)
               $header[] = array('name'=>'操作','field'=>'is_lock,id','callback'=>function($value){if($value[0] == LOCKSTATE_LOCK) return 'unlock'; else return 'lock,assign,reject';} );


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

    function exerciseIndexingMgmt(){
            $this->assign('parent', '待标引试题管理');
            $this->assign('parentHref', 'javascript:;');
            $this->assign('own', '');
            $courses = D('Exercises_Course')->getCourseList();
            $province = D('Dict_citydistrict')->getProvince();
            $creators = D('Exercises_question_processinfo')->getDistinctCreator();
            $markers = D('Exercises_account')->getMarkerList();
            $sections = C('Studysection');
            $this->assign('creatorList',$creators);
            $this->assign('courseList',$courses);
            $this->assign('markTeachers',$markers);
            $this->assign('sections',$sections);
            $this->assign('provinceList',$province);
            $this->display();
    }

    //分派
    function exerciseDetails(){
        $id = I('id');
        if(empty($id)) {
            die('参数错误');
        }
        $exercise_info = $this->Create_Exercise->getExerciseInfo( $id );
        $this->verifyExerciseOperationAuth($exercise_info,array(EXERCISE_STATE_WAITASSIGN,EXERCISE_STATE_REASSIGN));
        $textbook_tree_List = D('Exercises_textbook_tree_info_createexercise')->getKnowledgeListByExerciseId($id);//习题知识点

        $markers = D('Exercises_account')->getMarkerList();//老师
        $this->textbook_tree_List = $textbook_tree_List;
        $this->markTeachers = $markers;

        if ($exercise_info['exercise_type']==1) { //独立题
            $exercise_info['topic_type_show'] = $exercise_info['topic_type']-1;
            $exercise_info['study_section'] = $exercise_info['study_section']-1;
            $exercise_info['exercise_source'] = $exercise_info['exercise_source']-1;

            $exercise_info['topic_type_name'] = C('exercisesType.'.$exercise_info['topic_type_show']);
            $exercise_info['study_section_name'] = C('Studysection.'.$exercise_info['study_section']);
            $exercise_info['exercise_source_name'] = C('SOURCE.'.$exercise_info['exercise_source']);
            $exercise_info['track'] = $this->Exercises_Log->getQuestionLog($id);
            if (!empty($exercise_info['subject'])) {
                $course_name = $this->getCourseList->getCourseName($exercise_info['subject']);
            }
            $exercise_info['course_name'] = $course_name['name'];
            $this->exercise_info = $exercise_info;

        } else { //复合题
            $exercise_info['track'] = $this->Exercises_Log->getQuestionLog($id);

            $getCourse = $this->getCourseList->getCourseList($exercise_info['subject']);
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
            $exercise_info['track'] = $this->Exercises_Log->getQuestionLog($id);
            if (!empty($exercise_info['subject'])) {
                $course_name = $this->getCourseList->getCourseName($exercise_info['subject']);
            }
            $exercise_info['course_name'] = $course_name['name'];

            $this->exercise_info = $exercise_info;
            $this->isFu = 1;
        }
        //创建防止重复提交token
 //       $this->creatToken();
        $this->display();
    }
}
