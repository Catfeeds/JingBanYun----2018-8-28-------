<?php
namespace Exercise\Controller;
use Think\Controller;
use Think\Verify;

class TestMgmtController extends ExerciseGlobalController
{

    public $model;
    public $page_size=20;
                //permissions
    public function __construct() {
        parent::__construct();
        $this->model=D('Auth_admin');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }

    public function testMgmtPage(){

        $data = array();
        $header = array(
            array('name'=>'序号','field'=>'rownum','callback'=>function($value){return $value;}),
            array('name'=>'试卷ID','field'=>'id','callback'=>function($value){return $value;}),
            array('name'=>'学科','field'=>'course_name','callback'=>function($value){return $value;}),
            array('name'=>'分类','field'=>'period','callback'=>function($value){foreach(C('questionCategory') as $key=>$val){ if($val['id'] == $value) return $val['name'];};return '' ;}),
            array('name'=>'年级','field'=>'grade_name'),
            array('name'=>'试卷类型','field'=>'paper_type','callback'=>function($value){foreach(C('paperCategory') as $key=>$val){ if($val['id'] == $value) return $val['name'];}return '';}),
            array('name'=>'试卷名称','field'=>'paper_name','callback'=>function($value){return $value;}),
            array('name'=>'上架/下架','field'=>'status','callback'=>function($value){foreach(C('exerciseState') as $key=>$val){ if($val['id'] == $value) return $val['name'];}return '';}),

        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $export =  getParameter('export','str',false);
        if($export)
            $condition = json_decode(htmlspecialchars_decode(getParameter('formCondition','str',false)),true);
        else
            $condition = array(
                'paperId'  =>   getParameter('paperId','int',false),
                'gradeId'  =>   getParameter('gradeId','int',false),
                'courseId' =>   getParameter('courseId','int',false),
                'section' =>   getParameter('section','int',false),
                'provinceId' => getParameter('provinceId','int',false),
                'paperName' => getParameter('paperName','str',false),
                'paperCategory' => getParameter('paperCategory','int',false),
                'year' => getParameter('year','int',false),
                'startIndex' =>  getParameter('startIndex','int',false),
                'pageSize' =>  getParameter('pageSize','int',false),
                'lock'=>getParameter('lock'  ,'int',false),

            );
        $condition['status'] =getParameter('upDownShelfStatus'  ,'int',false)==0 ? array('in',''.EXERCISE_STATE_ONSHELF.','.EXERCISE_STATE_OFFSHELF.','.EXERCISE_STATE_UNONSHELF.'') : getParameter('upDownShelfStatus'  ,'int',false);

        $count =  D('Exercises_paper_processinfo')->getPaperCount($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition,'inbound_time');
        $list =  D('Exercises_paper_processinfo')->getPaperList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition,'inbound_time');
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

        //transform data
        if (!empty($list)) {
            foreach ($list as $k=>$v) {
                $list[$k]['paper_name'] = html_entity_decode($v['paper_name']);
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

    function testMgmt(){
        $this->assign('parent', '试卷管理');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '');
        $courses = D('Exercises_Course')->getCourseList();
        $grade = D('Dict_grade')->getGradeList(1);
        $platform = D('Exercises_platform')->getPlatformListByType(PLATFORM_GET);
        $province = D('Dict_citydistrict')->getProvince();
        $year = D('Exercises_create_paper')->getDistinctYear();
        $this->assign('courseList',$courses);
        $this->assign('gradeList',$grade);
        $this->assign('platform',$platform);
        $this->assign('provinceList',$province);
        $this->assign('yearList',$year);
        $this->display();
    }


    function testModify(){
        $this->display();
    }

}
