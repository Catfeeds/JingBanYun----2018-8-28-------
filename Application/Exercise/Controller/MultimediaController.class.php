<?php

namespace Exercise\Controller;

use Think\Controller;
use Think\Verify;
use Common\Common\CSV;

class MultimediaController extends ExerciseGlobalController
{

    public $Create_Exercise;
    public $Exercises_Log;
    public $Multimedia;
    public $getCourseList;
    public $Exercises_question_processinfo;
    public $Tree;

    public $page_size = PAGESIZE;
    private $userInfo;
    public $City;
    public $paper;
    public $Exercises_paper_processinfo;
    public $Exercises_paper_bigquestion;
    public $Exercises_parper_concat;

    public function __construct()
    {
        parent::__construct();
        $this->Create_Exercise = D('Create_Exercise');
        $this->Exercises_Log = D('Exercises_log');
        $this->getCourseList = D('Exercises_Course');
        $this->Exercises_question_processinfo = D('Exercises_question_processinfo');
        $this->Tree = D('Exercises_textbook_tree_info_createexercise');
        $this->getCourseList = D('Exercises_Course');
        $this->Create_Exercise = D('Create_Exercise');
        $this->Exercises_paper_processinfo = D('Exercises_paper_processinfo');
        $this->Exercises_paper_bigquestion = D('Exercises_paper_bigquestion');
        $this->Exercises_parper_concat = D('Exercises_parper_concat');
        $this->userInfo = $this->getUserRoleAuth();
    }

    //修改云习题
    public function updateOssExercises()
    {
        $id = getParameter('id', 'int', true);

        //主数据
        $masterdata['words'] = $_POST['word'];
        $masterdata['analysis'] = getParameter('translate', 'str', false);
        $masterdata['subject_name'] = getParameter('ossAdress', 'str', true);

        //从数据
        $slavdata['version_id'] = getParameter('versions', 'int', true); //版本
        $slavdata['version_name'] = getParameter('version_name', 'str', true); //版本
        $slavdata['grade_id'] = getParameter('grade', 'int', true); //年级
        $slavdata['grade_name'] = getParameter('grade_name', 'str', true);//年级
        $slavdata['section_id'] = getParameter('volume', 'int', true); //分册
        $slavdata['section_name'] = getParameter('section_name', 'str', true);//分册
        $slavdata['chapter'] = getParameter('chapter', 'int', true); //章
        $slavdata['chapter_name'] = getParameter('chapter_name', 'str', true);//分册
        $slavdata['festival'] = getParameter('section', 'int', true);//节
        $slavdata['festival_name'] = getParameter('festival_name', 'str', true);//节
        $Multimedia = $this->Create_Exercise->addMasterAndSlavEx($masterdata, $slavdata, $id);

        $truep = $this->Exercises_question_processinfo->updateCreatorInfo($id, session('admin.id'), session('admin.user_name'));
        //输入参数：习题ID 试卷ID 操作内容 IP地址 操作人ID 操作人姓名 备注 被操作人ID 被操作人姓名
        $log_id = $this->Exercises_Log->insertLog($id, $paperId = 0, '修改习题', get_client_ip(), session('admin.id'), session('admin.user_name'), '', $poperatorId = 0, $poperatorName = '');

        $eqp_id = D('Exercises_question_processinfo')->setQuestionState($id, EXERCISE_STATE_UNINBOUND, session('admin.id'));


        if ($Multimedia == true && $truep == true && $log_id == true && $eqp_id == true) {
            $this->showjson(200);
        } else {
            $this->showjson(400);
        }
    }

    //多媒体作业录入管理
    public function homeworkEntering()
    {
        /*******************************列表展示操作*********************************************/

        $this->assign('parent', '多媒体作业录入管理');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '');
        /***************************获取所有学科*************************************/
        $courses = D('Exercises_Course')->getCourseList();
        /***************************获取所有版本*************************************/
        $versionData = D('Exercises_textbook_version')->getAllVersions();
        /***************************获取所有年级*************************************/
        $gradeList = D('Exercises_school_term')->getGradeList();
        /***************************获取所有录入人员*************************************/
        $creators = D('Exercises_question_processinfo')->getDistinctCreator();
        if (empty($_GET['cat']))
            $_GET['cat'] = 1;
        $type = C('yunyintype');//类别
        $this->assign('type', $type);//类别
        $this->assign('creatorList', $creators);//所有录入人员
        $this->assign('courseList', $courses);//所有学科
        $this->assign('versionData', $versionData);//所有版本
        $this->assign('grade', $gradeList);//所有年级
        $this->display_nocache();
    }

    /*
     *筛选操作并把结果渲染
     */
    public function screening()
    {
        $data = array();
        $header = array(
            array('name' => '序号', 'field' => 'rownum', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => 'ID', 'field' => 'id', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '上传日期', 'field' => 'create_time', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '录入人员', 'field' => 'creator_name'),
            array('name' => '状态', 'field' => 'status', 'callback' => function ($value) {
                if ($value == EXERCISE_STATE_UNINBOUND) {
                    return '未入库';
                } elseif ($value == EXERCISE_STATE_REPROCESS) {
                    return '返工待修改';
                }
                return '';
            }),
            array('name' => '类别', 'field' => 'ordinary_type', 'callback' => function ($value) {
                foreach (C('yunyintype') as $val) {
                    if ($val['id'] == $value) {
                        return $val['name'];
                    }
                }
                return '';
            }),
            array('name' => '上传内容', 'field' => 'words', 'callback' => function ($value) {
                return $value;
            }),

        );
        /********************************筛选操作*************************************************/
        $condition = array(
            'version' => getParameter('version', 'int', false),//新增
            'courseId' => getParameter('courseId', 'int', false),//新增
            'grade' => getParameter('grade', 'int', false),//新增
            'school_term' => getParameter('school_term', 'int', false),//新增
            'keyword' => getParameter('keyword', 'str', false),
            'chapter' => getParameter('chapter', 'int', false),//新增
            'festival' => getParameter('festival', 'int', false),//新增
            'knowledge' => getParameter('knowledge', 'int', false),//新增
            'type' => getParameter('type', 'int', false),//新增
            'inputStartTime' => getParameter('inputStartTime', 'str', false),
            'inputEndTime' => getParameter('inputEndTime', 'str', false),
            //'pageSize' =>  getParameter('knowledgepoint','int',false),
            'creatorId' => getParameter('creatorId', 'int', false),
            'startIndex' => getParameter('startIndex', 'int', false),
            'pageSize' => getParameter('pageSize', 'int', false),
            'cat' => getParameter('cat', 'str', false),
        );
        switch ($condition['cat']) {
            case EXERCISE_HASINPUT:
                $condition['is_self'] = 1;
                $condition['status'] = EXERCISE_STATE_UNINBOUND;
                $list = D('Exercises_question_processinfo')->getListByScreening($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition);
                $count = D('Exercises_question_processinfo')->getListByScreening($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition,'count');
                $header[] = array('name' => '操作', 'field' => 'id', 'callback' => function ($value) {
                    return 'delete';
                });//删除
                break;
            case EXERCISE_REPROCESS:
                $condition['is_self'] = 1;
                $condition['status'] = EXERCISE_STATE_REPROCESS;
                $list = D('Exercises_question_processinfo')->getListByScreening($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition);
                $count = D('Exercises_question_processinfo')->getListByScreening($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition,'count');
                $header[] = array('name' => '操作', 'field' => 'id', 'callback' => function ($value) {
                    return 'modify,delete';
                });//修改，删除
                break;
            default:
                $count = 0;
                $list = array();
        }
        $data['iTotalRecords'] = $count[0]['count'] ? $count[0]['count'] : 0;
        $data['iTotalDisplayRecords'] = $count[0]['count'] ? $count[0]['count'] : 0;
        $list = $this->transData($header, $list);
        $data['aaData'] = array_column($list, 'data');
        echo json_encode($data);
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

    //多媒体作业录入管理详情
    public function homeworkDetails()
    {
        $id = I('id');
        if (empty($id) || is_numeric($id) == false) {
            die('参数错误');
        }
        $this->id = $id;
        $exercise_info = $this->Create_Exercise->getExerciseWorkInfo($id);
        if (empty($exercise_info)) {
            die("非法错误信息");
        }
        $exercise_info['track'] = $this->Exercises_Log->getQuestionLog($id);

        if (!empty($exercise_info['subject'])) {
            $course_name = $this->getCourseList->getCourseName($exercise_info['subject']);
        }
        $exercise_info['course_name'] = $course_name['name'];
        $exercise_info['ordinary_type'] = $exercise_info['ordinary_type'] - 1;
        $exercise_info['ordinary_type'] = C('yunyintype.' . $exercise_info['ordinary_type']);

        foreach (C('exerciseState') as $k => $v) {

            if ($exercise_info['status'] == $v['id']) {
                $exercise_info['status'] = $v['name'];
            }
        }

        $this->exercise_info = $exercise_info;


        /***************************获取所有学科*************************************/
        $courses = D('Exercises_Course')->getCourseList();
        /***************************获取所有版本*************************************/
        $versionData = D('Exercises_textbook_version')->getAllVersions();
        /***************************获取所有年级*************************************/
        $gradeList = D('Exercises_school_term')->getGradeList();

        $this->assign('courseList', $courses);//所有学科
        $this->assign('versionData', $versionData);//所有版本
        $this->assign('grade', $gradeList);//所有年级

        $this->assign('parent', '多媒体作业录入管理');
        $this->assign('parentHref', U('Multimedia/homeworkEntering'));
        $this->assign('own', ' >> 试卷校审');

        $this->display();
    }

    //多媒体作业审核
    public function homeworkCheckMgmt()
    {
        $this->assign('parent', '多媒体作业审核');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '');
        /***************************获取所有学科*************************************/
        $courses = D('Exercises_Course')->getCourseList();
        /***************************获取所有版本*************************************/
        $versionData = D('Exercises_textbook_version')->getAllVersions();
        /***************************获取所有年级*************************************/
        $gradeList = D('Exercises_school_term')->getGradeList();
        /***************************获取所有录入人员*************************************/
        $creators = D('Exercises_question_processinfo')->getDistinctCreator();
        if (empty($_GET['cat']))
            $_GET['cat'] = 1;
        $type = C('yunyintype');//类别
        $this->assign('type', $type);//类别
        $this->assign('creatorList', $creators);//所有录入人员
        $this->assign('courseList', $courses);//所有学科
        $this->assign('versionData', $versionData);//所有版本
        $this->assign('grade', $gradeList);//所有年级
        $this->display_nocache();
    }

    /*
     *筛选操作并把结果渲染
     */
    public function screeningByAudit()
    {
        $data = array();
        $header = array(
            array('name' => '序号', 'field' => 'rownum', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => 'ID', 'field' => 'id', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '录入人员', 'field' => 'creator_name'),
            array('name' => '状态', 'field' => 'status', 'callback' => function ($value) {
                if ($value == EXERCISE_STATE_UNINBOUND) {
                    return '未入库';
                } elseif ($value == EXERCISE_STATE_REPROCESS) {
                    return '返工待修改';
                }
                return '';
            }),
            array('name' => '录入内容', 'field' => 'words', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '资源地址', 'field' => 'subject_name', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '类别', 'field' => 'ordinary_type', 'callback' => function ($value) {
                foreach (C('yunyintype') as $val) {
                    if ($val['id'] == $value) {
                        return $val['name'];
                    }
                }
                return '';
            }),
            array('name' => '版本', 'field' => 'version_name', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '学科', 'field' => 'course_name', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '年级', 'field' => 'grade_name', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '分册', 'field' => 'section_name', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '章', 'field' => 'chapter_name', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '节', 'field' => 'festival_name', 'callback' => function ($value) {
                return $value;
            }),


        );
        /********************************筛选操作*************************************************/
        $condition = array(
            'version' => getParameter('version', 'int', false),//新增
            'courseId' => getParameter('courseId', 'int', false),//新增
            'grade' => getParameter('grade', 'int', false),//新增
            'school_term' => getParameter('school_term', 'int', false),//新增
            'keyword' => getParameter('keyword', 'str', false),
            'chapter' => getParameter('chapter', 'int', false),//新增
            'festival' => getParameter('festival', 'int', false),//新增
            'knowledge' => getParameter('knowledge', 'int', false),//新增
            'type' => getParameter('type', 'int', false),//新增
            'inputStartTime' => getParameter('inputStartTime', 'str', false),
            'inputEndTime' => getParameter('inputEndTime', 'str', false),
            //'pageSize' =>  getParameter('knowledgepoint','int',false),
            'creatorId' => getParameter('creatorId', 'int', false),
            'startIndex' => getParameter('startIndex', 'int', false),
            'pageSize' => getParameter('pageSize', 'int', false),
            'cat' => 1,
        );
        switch ($condition['cat']) {
            case EXERCISE_HASINPUT:
                //$condition['is_self'] = 1;
                $condition['status'] = EXERCISE_STATE_UNINBOUND;
                $list = D('Exercises_question_processinfo')->getListByScreening($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition);
                $count = D('Exercises_question_processinfo')->getListByScreening($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition,'count');
                $header[] = array('name' => '操作', 'field' => 'id', 'callback' => function ($value) {
                    return 'inbound,reject';
                });//删除
                break;
            default:
                $count = 0;
                $list = array();
        }
        $data['iTotalRecords'] = $count[0]['count'] ? $count[0]['count'] : 0;
        $data['iTotalDisplayRecords'] = $count[0]['count'] ? $count[0]['count'] : 0;
        $list = $this->transData($header, $list);
        $data['aaData'] = array_column($list, 'data');
        echo json_encode($data);
    }

    //多媒体作业审核详情
    public function homeworkCheckDetails()
    {

        $id = I('id');
        if (empty($id) || is_numeric($id) == false) {
            die('参数错误');
        }
        $this->id = $id;
        $exercise_info = $this->Create_Exercise->getExerciseWorkInfo($id);
        if (empty($exercise_info)) {
            die("非法错误信息");
        }
        $exercise_info['track'] = $this->Exercises_Log->getQuestionLog($id);

        if (!empty($exercise_info['subject'])) {
            $course_name = $this->getCourseList->getCourseName($exercise_info['subject']);
        }
        $exercise_info['course_name'] = $course_name['name'];
        $exercise_info['ordinary_type'] = $exercise_info['ordinary_type'] - 1;
        $exercise_info['ordinary_type'] = C('yunyintype.' . $exercise_info['ordinary_type']);

        foreach (C('exerciseState') as $k => $v) {

            if ($exercise_info['status'] == $v['id']) {
                $exercise_info['status'] = $v['name'];
            }
        }

        $this->exercise_info = $exercise_info;

        $this->assign('parent', '多媒体作业审核');
        $this->assign('parentHref', U('Multimedia/homeworkCheckMgmt'));
        $this->assign('own', ' >> 多媒体作业详情');

        $this->display();
    }

    //多媒体作业查询
    function homeworkQuery()
    {//试题列表
        $this->assign('parent', '查询与统计');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '>> 多媒体作业查询');
        $statusList = C('exerciseState');
        foreach ($statusList as $key => $val) {
            if ($val['id'] == EXERCISE_STATE_INBOUND || $val['id'] == EXERCISE_STATE_REPROCESS || $val['id'] == EXERCISE_STATE_UNINBOUND) {
                $newStatusList[] = $statusList[$key];
            }
        }
        /***************************获取所有学科*************************************/
        $courses = D('Exercises_Course')->getCourseList();
        /***************************获取所有版本*************************************/
        $versionData = D('Exercises_textbook_version')->getAllVersions();
        /***************************获取所有年级*************************************/
        $gradeList = D('Exercises_school_term')->getGradeList();
        /***************************获取所有录入人员*************************************/
        $creators = D('Exercises_question_processinfo')->getDistinctCreator();
        if (empty($_GET['cat']))
            $_GET['cat'] = 1;
        $type = C('yunyintype');//类别
        $this->assign('type', $type);//类别
        $this->assign('creatorList', $creators);//所有录入人员
        $this->assign('courseList', $courses);//所有学科
        $this->assign('versionData', $versionData);//所有版本
        $this->assign('grade', $gradeList);//所有年级
        $this->assign('statusList', $newStatusList);//状态
        $this->display();
    }

    /*
     *筛选多媒体作业查询操作并把结果渲染
     */
    public function screeningByWork()
    {
        $data = array();
        $header = array(
            array('name' => '序号', 'field' => 'rownum', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => 'ID', 'field' => 'id', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '录入时间', 'field' => 'create_time', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '录入人员', 'field' => 'creator_name'),
            array('name' => '状态', 'field' => 'status', 'callback' => function ($value) {
                foreach (C('exerciseState') as $val) {
                    if ($val['id'] == $value) {
                        return $val['name'];
                    }
                }
                {
                    return '';
                }
            }),
            array('name' => '类别', 'field' => 'ordinary_type', 'callback' => function ($value) {
                foreach (C('yunyintype') as $val) {
                    if ($val['id'] == $value) {
                        return $val['name'];
                    }
                }
                return '';
            }),
            array('name' => '内容', 'field' => 'words', 'callback' => function ($value) {
                return $value;
            }),

        );
        /********************************筛选操作*************************************************/
        $condition = array(
            'version' => getParameter('version', 'int', false),//新增
            'courseId' => getParameter('courseId', 'int', false),//新增
            'grade' => getParameter('grade', 'int', false),//新增
            'school_term' => getParameter('school_term', 'int', false),//新增
            'keyword' => getParameter('keyword', 'str', false),
            'chapter' => getParameter('chapter', 'int', false),//新增
            'festival' => getParameter('festival', 'int', false),//新增
            'status' => getParameter('status', 'int', false),//新增
            'type' => getParameter('type', 'int', false),//新增
            //'pageSize' =>  getParameter('knowledgepoint','int',false),
            'creatorId' => getParameter('creator', 'int', false),
            'startIndex' => getParameter('startIndex', 'int', false),
            'pageSize' => getParameter('pageSize', 'int', false),
            'cat' => 1,
        );
        switch ($condition['cat']) {
            case EXERCISE_HASINPUT:
                $list = D('Exercises_question_processinfo')->getListByScreening($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition);
                $count = D('Exercises_question_processinfo')->getListByScreening($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition,'count');
                break;
            default:
                $count = 0;
                $list = array();
        }

        $data['iTotalRecords'] = $count[0]['count'] ? $count[0]['count'] : 0;
        $data['iTotalDisplayRecords'] = $count[0]['count'] ? $count[0]['count'] : 0;
        $list = $this->transData($header, $list);
        $data['aaData'] = array_column($list, 'data');
        echo json_encode($data);
    }

    //多媒体作业录入统计
    function homeworkStatistics()
    {
        $this->assign('parent', '查询与统计');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '>> 多媒体作业录入统计');
        /***************************获取所有学科*************************************/
        $courses = D('Exercises_Course')->getCourseList();
        /***************************获取所有版本*************************************/
        $versionData = D('Exercises_textbook_version')->getAllVersions();
        /***************************获取所有年级*************************************/
        $gradeList = D('Exercises_school_term')->getGradeList();
        /***************************获取所有录入人员*************************************/
        $creators = D('Exercises_question_processinfo')->getDistinctCreator();
        $type = C('yunyintype');//类别
        $this->assign('type', $type);//类别
        $this->assign('creatorList', $creators);//所有录入人员
        $this->assign('courseList', $courses);//所有学科
        $this->assign('versionData', $versionData);//所有版本
        $this->assign('grade', $gradeList);//所有年级
        $this->display();
    }

    /*
     *筛选多媒体作业录入统计操作并把结果渲染
     */
    public function screeningByStatistical()
    {
        $data = array();
        $header = array(
            array('name' => '分类', 'field' => 'ordinary_type', 'callback' => function ($value) {
                foreach (C('yunyintype') as $val) {
                    if ($val['id'] == $value) {
                        return $val['name'];
                    }
                }
                return '';
            }),
            array('name' => '版本', 'field' => 'version_name', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '年级', 'field' => 'grade_name', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '学科', 'field' => 'course_name', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '分册', 'field' => 'section_name', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '章', 'field' => 'chapter_name', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '节', 'field' => 'festival_name', 'callback' => function ($value) {
                return $value;
            }),
            array('name' => '数量', 'field' => 'countbyfestival', 'callback' => function ($value) {
                return $value;
            }),

        );
        /********************************筛选操作*************************************************/
        $condition = array(
            'version' => getParameter('version', 'int', false),//新增
            'courseId' => getParameter('courseId', 'int', false),//新增
            'grade' => getParameter('grade', 'int', false),//新增
            'school_term' => getParameter('school_term', 'int', false),//新增
            'keyword' => getParameter('keyword', 'str', false),
            'chapter' => getParameter('chapter', 'int', false),//新增
            'festival' => getParameter('festival', 'int', false),//新增
            'status' => getParameter('status', 'int', false),//新增
            'type' => getParameter('type', 'int', false),//新增
            //'pageSize' =>  getParameter('knowledgepoint','int',false),
            'creatorId' => getParameter('creatorId', 'int', false),
            'startIndex' => getParameter('startIndex', 'int', false),
            'pageSize' => getParameter('pageSize', 'int', false),
            'cat' => EXERCISE_HASINPUT,
        );
        switch ($condition['cat']) {
            case EXERCISE_HASINPUT:
                $list = D('Exercises_question_processinfo')->getListByScreeningOfStatistical($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition, 'a.festival,exercises_createexercise.ordinary_type');
                $count = D('Exercises_question_processinfo')->getListByScreeningOfStatistical($this->userInfo['id'], ROLE_INPUTEXERCISE, $condition, 'a.festival,exercises_createexercise.ordinary_type','count');
                break;
            default:
                $count = 0;
                $list = array();
        }

        $data['iTotalRecords'] = $count[0]['count'] ? $count[0]['count'] : 0;
        $data['iTotalDisplayRecords'] = $count[0]['count'] ? $count[0]['count'] : 0;
        $list = $this->transData($header, $list);
        $data['aaData'] = array_column($list, 'data');
        echo json_encode($data);
    }

    //多媒体作业导入
    function homeworkImport()
    {


        /***************************获取所有学科*************************************/
        $courses = D('Exercises_Course')->getCourseList();
        /***************************获取所有版本*************************************/
        $versionData = D('Exercises_textbook_version')->getAllVersions();
        /***************************获取所有年级*************************************/
        $gradeList = D('Exercises_school_term')->getGradeList();


        $this->assign('courseList', $courses);//所有学科
        $this->assign('versionData', $versionData);//所有版本
        $this->assign('grade', $gradeList);//所有年级

        $this->assign('parent', '多媒体作业录入管理');
        $this->assign('parentHref', U('Multimedia/homeworkEntering'));
        $this->assign('own', ' >> 多媒体作业导入');

        $this->display();
    }

    public function setCodeBase()
    {
        $str = I('str');
        $base = base64_encode($str);
        $this->ajaxReturn($base);
    }

    public function getCodeBase()
    {
        $str = I('str');
        $base = base64_decode($str);
        $this->ajaxReturn($base);
    }


    //下载示例表格
    public function cihuiDownloadFile()
    {

        $csv = new CSV();
        $filepath = "./Public/csv/cihui.xls";
        $filename = '导入习题模板表格';
        $csv->downloadFileXls($filepath, $filename);
    }

    public function juziDownloadFile()
    {

        $csv = new CSV();
        $filepath = "./Public/csv/juzi.xls";
        $filename = '导入习题模板表格';
        $csv->downloadFileXls($filepath, $filename);
    }

    //上传词汇
    public function uploadWordsFile()
    {
        set_time_limit(0);
        import("Org.Util.Ereader");
        import("Org.Util.Sreader");
        $type = I('type');
        $score = 5;
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 20145728;// 设置附件上传大小
        $upload->exts = array('ods', 'xls', 'csv');// 设置附件上传类型
        $upload->rootPath = './Uploads/'; // 设置附件上传根目录
        // 上传单个文件
        $info = $upload->uploadOne($_FILES['file']);
        if (!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        } else {// 上传成功 获取上传文件信息
            $file_path = $upload->rootPath . $info['savepath'] . $info['savename'];
        }

        if (!empty($file_path)) {
            $Reader = new \SpreadsheetReader($file_path);
            $array = [];
            $errordata = [];
            $totalNum = 0; //总条数
            $successTotal = 0;//成功的条数
            $loserTotal = 0;//失败的条数
            $this->Create_Exercise->startTrans();
            $lock = true;
            foreach ($Reader as $k => $Row) { //进行校验数据的正确性

                if ($k == 1) {
                    continue;
                } else {
                    $totalNum++;
                    $truedata = $this->__checkValidation($Row,$type); //校验数据的正确性

                    if ($truedata == true) { //验证通过
                        $successTotal++;
                        list($words,$analysis,$subjectName,$code) = $this->__getInsertValues($Row,$type);

                        if (array_key_exists($code, $array)) {

                            $array[$code]++;
                        } else {
                            $array[$code] = 1;
                        }

                        $codebye = explode("||", $code);
                        $codeId = explode("&amp;", $codebye[0]);
                        $codeName = explode("&amp;", $codebye[1]);

                        $adddata['words'] = $words;
                        $adddata['analysis'] = $analysis;
                        $adddata['subject_name'] = $subjectName;
                        $adddata['status'] = EXERCISE_STATE_UNINBOUND;
                        $adddata['ordinary_type'] = $type;
                        $adddata['types'] = 2;
                        $adddata['create_at'] = time();
                        $adddata['subject'] = $codeId[1];
                        $adddata['home_topic_type'] = 100000;
                        $adddata['count_score'] = ($type == 1 || $type == 2)?10:$score; //视频课本作业0分
                        $adddata['score'] = ($type == 1 || $type == 2)?10:$score;


                        $id = $this->Create_Exercise->createExerciseInfo($adddata);
                        $truep = $this->Exercises_question_processinfo->addCreatorInfo($id, session('admin.id'), session('admin.user_name'));
                        $log_id = $this->Exercises_Log->insertLog($id, $paperId = 0, '导入习题', get_client_ip(), session('admin.id'), session('admin.user_name'), '个人导入习题', $poperatorId = 0, $poperatorName = '');

                        $eqp_id = D('Exercises_question_processinfo')->setQuestionState($id, EXERCISE_STATE_UNINBOUND,session('admin.id'));

                        $is_tree = $this->Tree->addTreeInfo($codeId, $codeName, $id);
                        if ($id != false && $truep!= false && $log_id!= false && $is_tree!= false && $eqp_id !=false ) {
                            $lock = true;
                        } else {
                            $lock = false;
                        }

                    } else { //验证不通过
                        $loserTotal++;
                        //array_push($Row,$type);
                        $errordata[$k] = $Row;
                    }

                    $errordata = array_values($errordata);
                }

            }
            unlink($file_path);

            if ($lock) {
                $this->Create_Exercise->commit();
                $res['code'] = SUCCESSCODE;
                $res['array'] = $array;
                $res['errordata'] = json_encode($errordata);
                $res['totalNum'] = $totalNum;
                $res['successTotal'] = $successTotal;
                $res['loserTotal'] = $loserTotal;
                $this->ajaxReturn($res);
            } else {
                $this->Create_Exercise->rollback();
                $res['msg'] = '添加失败';
                $res['code'] = ERRORCODE;
                $this->ajaxReturn($res);
            }
        }
    }
    private function __getInsertValues($Row,$type)
    {
        switch($type)
        {
            case 1:
            case 2:return [$Row[0],$Row[1],$Row[2],$Row[3]];
                    break;
            case 3:return [$Row[1],'',$Row[0],$Row[2]];
            case 4:return ["<img src='$Row[0]?x-oss-process=image/resize,w_200'/>",'',$Row[0],$Row[2]];
            default:return [];
        }
    }

    private function __checkValidation(&$Row,$type)
    {
        switch($type){
            case 1:
            case 2:return $this->importCheckWords($Row);  //词汇句子
                    break;
            case 3:return $this->__checkVideo($Row);
            case 4:return $this->__checkTextbook($Row);
            default:break;
        }
       return false;
    }

    private function __checkVideo(&$Row){
        if (empty($Row[0])) {
            $msg[] = '视频为空';
            $Row = array_merge($Row, $msg);
            return false;
        }
        $basedata = $Row[2];
        $is_base = $this->is_base64($basedata);

        if( $is_base ){
            $Row[2] = base64_decode($Row[2]);
        }else {
            $msg[] = '节编码错误';
            $Row = array_merge($Row, $msg);
            return false;
        }

        if (empty($Row[2])) {

            $msg[] = '节编码错误';
            $Row = array_merge($Row, $msg);
            return false;
        }
        return true;
    }

    private function __checkTextbook(&$Row){
        if (empty($Row[0])) {
            $msg[] = '课本图片错误为空';
            $Row = array_merge($Row, $msg);
            return false;
        }
        $basedata = $Row[2];
        $is_base = $this->is_base64($basedata);

        if( $is_base ){
            $Row[2] = base64_decode($Row[2]);
        }else {
            $msg[] = '节编码错误';
            $Row = array_merge($Row, $msg);
            return false;
        }

        if (empty($Row[2])) {

            $msg[] = '节编码错误';
            $Row = array_merge($Row, $msg);
            return false;
        }
        return true;
    }

    public function importCheckWords(&$Row)
    {

        //$Row0 = $this->ischinese($Row[0]);
        $Row[0] = trim($Row[0]);
        $Row[1] = trim($Row[1]);
        $Row[2] = trim($Row[2]);
        $Row[3] = trim($Row[3]);

        //$Row0 = $this->isZw($Row[0]);
        if (empty($Row[0])) {
            $msg[] = '词汇/句子错误';
            $Row = array_merge($Row, $msg);
            return false;
        }

        /*$Row1 = $this->isZw($Row[1]);

        if (empty($Row1) || $Row1 == false) {
            $msg[] = '翻译错误,只能填写中文';
            $Row = array_merge($Row, $msg);

            return false;
        }*/

        $Row2 = $Row[2];
        if (empty($Row2)) {
            $msg[] = 'OSS地址错误';
            $Row = array_merge($Row, $msg);
            return false;
        }
        $Row[3] = trim($Row[3]);
        $basedata = $Row[3];
        $is_base = $this->is_base64($basedata);

        if( $is_base ){
            $Row[3] = base64_decode($Row[3]);
        }else {
            $msg[] = '节编码错误';
            $Row = array_merge($Row, $msg);
            return false;
        }

        if (empty($Row[3])) {

            $msg[] = '节编码错误';
            $Row = array_merge($Row, $msg);
            return false;
        }
        return true;
    }

    public function is_base64($str){
        if($str==base64_encode(base64_decode($str))){
            return true;
        }else{
            return false;
        }
    }

    public function getUrl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);  // $resp = curl_exec($ch);
        $curl_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curl_code == 200) {
            return true;
        } else {
            return false;
        }
    }

    public function isZw($str)
    {
        if (preg_match(' /[^\x00-\x80]/', $str) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function ischinese($str)
    {
        $content = $str;
        if (preg_match("/^[\x4e00-\x9fa5a-zA-Z]+$/", $content, $m)) {
            return true;
        } else {
            return false;
        }

    }

    public function downloadError()
    {
        $types = $_POST['types'];
        $errArray = $_POST['errorlist'];
        $errArray = json_decode($errArray, true);
        if (empty($errArray)) {
            die("未知错误");
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=demo.xls');
        header('Pragma: no-cache');
        header('Expires: 0');
        switch($types){
            case 1:$title = array('词汇', '翻译', 'oss地址', '节编码', '错误原因');
                    break;
            case 2:$title = array('句子', '翻译', 'oss地址', '节编码', '错误原因');
                    break;
            case 3:$title = array('视频URL地址', '节编码', '错误原因');
                    break;
            case 4:$title = array('课本图片URL地址', '', '节编码', '错误原因');
                    break;
            default:echo '';exit;
        }


        echo iconv('utf-8', 'gbk', implode("\t", $title)), "\n";

        foreach ($errArray as $value) {
            echo iconv('utf-8', 'gbk', implode("\t", $value)), "\n";
        }

    }

    /**
     *
     * execl数据导出
     * 应用场景：订单导出
     * @param string $title 模型名（如Member），用于导出生成文件名的前缀
     * @param array $cellName 表头及字段名
     * @param array $data 导出的表数据
     *
     * 特殊处理：合并单元格需要先对数据进行处理
     */
    private function exportOrderExcel($title,$cellName,$data)
    {
        //引入核心文件
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        //定义配置
        $topNumber = 1;//表头有几行占用
        $xlsTitle = iconv('utf-8', 'gb2312', $title);//文件名称
        $fileName = $title.date('_YmdHis');//文件名称
        $cellKey = array(
            'A','B','C','D','E','F','G','H','I','J','K','L','M',
            'N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
            'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM',
            'AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ'
        );

        //写在处理的前面（了解表格基本知识，已测试）
        //     $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);//所有单元格（行）默认高度
        //     $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);//所有单元格（列）默认宽度
        //     $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);//设置行高度
        //     $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);//设置列宽度
        //     $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(18);//设置文字大小
        //     $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);//设置是否加粗
        //     $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);// 设置文字颜色
        //     $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//设置文字居左（HORIZONTAL_LEFT，默认值）中（HORIZONTAL_CENTER）右（HORIZONTAL_RIGHT）
        //     $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
        //     $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);//设置填充颜色
        //     $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('FF7F24');//设置填充颜色




        //处理表头


        foreach ($cellName as $k=>$v)
        {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellKey[$k].$topNumber, $v[1]);//设置表头数据
            $objPHPExcel->getActiveSheet()->freezePane($cellKey[$k].($topNumber+1));//冻结窗口
            $objPHPExcel->getActiveSheet()->getStyle($cellKey[$k].$topNumber)->getFont()->setBold(true);//设置是否加粗
            if($v[3] > 0)//大于0表示需要设置宽度
            {
                $objPHPExcel->getActiveSheet()->getColumnDimension($cellKey[$k])->setWidth($v[3]);//设置列宽度
            }
        }
        //处理数据
        foreach ($data as $k=>$v)
        {
            foreach ($cellName as $k1=>$v1)
            {
                if(!$v1[5])
                 $objPHPExcel->getActiveSheet()->setCellValue($cellKey[$k1].($k+1+$topNumber), $v[$v1[0]]);
                if($v['end'] > 0)
                {
                    if($v1[2] == 1)//这里表示合并单元格
                    {
                        $objPHPExcel->getActiveSheet()->mergeCells($cellKey[$k1].$v['start'].':'.$cellKey[$k1].$v['end']);
                    }
                }
                if($v1[4] != "" && in_array($v1[4], array("LEFT","CENTER","RIGHT")))
                {
                    $v1[4] = eval('return PHPExcel_Style_Alignment::HORIZONTAL_'.$v1[4].';');
                    //这里也可以直接传常量定义的值，即left,center,right；小写的strtolower
                    $objPHPExcel->getActiveSheet()->getStyle($cellKey[$k1].($k+1+$topNumber))->getAlignment()->setHorizontal($v1[4]);
                }
                if($v1[5]){ //ispic
                    $objPHPExcel->getActiveSheet()->getRowDimension(($k+1+$topNumber))->setRowHeight(150);
                    // 图片生成
                    $objDrawing[$k] = new \PHPExcel_Worksheet_MemoryDrawing();
                    $img = imagecreatefrompng($v[$v1[0]].'?x-oss-process=image/resize,w_200');
                    $objDrawing[$k]->setImageResource($img);
                    // 设置宽度高度
                    $objDrawing[$k]->setHeight(200);//照片高度
                    /*设置图片要插入的单元格*/
                    $objDrawing[$k]->setCoordinates($cellKey[$k1].($k+1+$topNumber));
                    // 图片偏移距离
                    $objDrawing[$k]->setRenderingFunction(\PHPExcel_Worksheet_MemoryDrawing::RENDERING_DEFAULT);//渲染方法
                    $objDrawing[$k]->setMimeType(\PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
                    $objDrawing[$k]->setWorksheet($objPHPExcel->getActiveSheet());
                }
            }
        }
        //导出execl
        ob_end_clean();
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        $_SESSION['xlsProcess'] = 1;
        exit;
    }

    public function TextbookDownloadFile()
    {
        $_SESSION['xlsProcess'] = 0;
        set_time_limit(0);
        vendor("PHPExcel.PHPExcel");

        $objPHPExcel = new\PHPExcel();

        $textBookId = getParameter('id','int',false);

        if(empty($textBookId)) {
            echo '<script>window.top.$.NotifyBox.NotifyOneCallClose(\'错误\',\'请选择正确的课本\',\'确定\');</script>';
            $_SESSION['xlsProcess'] = 1;
            exit;
        }

       //get oss path
       $info = D('Biz_textbook')->getTextBookDetails($textBookId);

       if($info['has_ebook'] == 1)
       {
         //load oss path
         $xmlPath = $info['server_path'].'/Pages.xml';
         $xmlInfo = file_get_contents($xmlPath);
         $xmlObj = simplexml_load_string($xmlInfo);
         $header = [['url','课本图片URL地址',0,90],['img','图片',0,21,'',1],['code','节编码',0,100]];
         $content = [];
           foreach($xmlObj->page as $key=>$val){
               $orgPath = $val[0]['src'];
               $startPos = strpos($orgPath,'content/');
               $path = strval($info['server_path'].'/'.substr($orgPath,$startPos));
               $content[]= ['url'=>"$path",'img'=>$path];
           }
           $this->exportOrderExcel('课本样本文件',$header,$content);
       }
       else
       {
           echo '<script>window.top.$.NotifyBox.NotifyOneCallClose(\'错误\',\'选择的课本没有电子版\',\'确定\');</script>';
           $_SESSION['xlsProcess'] = 1;
       }
    }

    public function VideoDownloadFile()
    {
        $header = [['url','视频URL地址',0,90],['name','视频名称',0,40],['code','节编码',0,100]];
        $content = [['url'=>'http://www.baidu.com','name'=>'教学视频']];
        $this->exportOrderExcel('视频样本文件',$header,$content);
    }

    public function getXLSProcessIsFinish()
    {
        if($_SESSION['xlsProcess'] == 1){
            $_SESSION['xlsProcess'] = 0;
            $this->showMessage(200);
        }
        else
            $this->showMessage(201);
    }
    private function __processAddVideoResource($data)
    {
        $score = 5;
        foreach ($data as $k => $Row) { //进行校验数据的正确性
            $this->Create_Exercise->startTrans();
            list($words, $analysis, $subjectName, $code,$isDisplay) = [$Row['name'],$Row['vid_image_path'],$Row['url'],$Row['code'],$Row['is_display']];


            $codes = explode('||||',$code);
            $codebye = explode("||", $codes[0]);
            $codeId = explode("&amp;", $codebye[0]);
            $codeName = explode("&amp;", $codebye[1]);

            $adddata['words'] = $words;
            $adddata['analysis'] = $analysis;
            $adddata['subject_name'] = $subjectName;

            $adddata['status'] = EXERCISE_STATE_OFFSHELF;
            $adddata['ordinary_type'] = 3;
            $adddata['types'] = 2;
            $adddata['create_at'] = time();
            $adddata['subject'] = $codeId[1];
            $adddata['home_topic_type'] = 100000;
            $adddata['count_score'] = $score;
            $adddata['score'] = $score;
            if($this->Tree->getResourceExerciseContact($Row['contactid'])){
                echo '<p>'.$subjectName . "资源已经有关联 id:{$Row['id']}".'</p>';
                $this->Create_Exercise->rollback();
                continue;
            }
            $id = $this->Create_Exercise->createExerciseInfo($adddata);
            if($id){
                //insert join info to knowledge_resource_exercise_contact
                $joinData['resource_id'] = $Row['id'];
                $joinData['contact_resource_id'] = $Row['contactid'];
                $joinData['exercise_id'] = $id;
                $addResult = $this->Tree->addResourceExerciseContact($joinData);
                if(!$addResult){
                    echo '<p>'.$subjectName . "资源已经有关联 id:{$Row['id']}".'</p>';
                    $this->Create_Exercise->rollback();
                    continue;
                }
            }
            $truep = $this->Exercises_question_processinfo->addCreatorInfo($id, 1, 'system');
            $log_id = $this->Exercises_Log->insertLog($id, $paperId = 0, '导入习题', get_client_ip(), 1, 'system','个人导入习题', $poperatorId = 0, $poperatorName = '');

            $eqp_id = D('Exercises_question_processinfo')->setQuestionState($id, $isDisplay == 1?EXERCISE_STATE_ONSHELF:EXERCISE_STATE_OFFSHELF, 1);
            $is_tree =  false;
            foreach($codes as $key => $val){
                $codebye = explode("||", $val);
                $codeId = explode("&amp;", $codebye[0]);
                $codeName = explode("&amp;", $codebye[1]);
                try {
                    $is_tree = $this->Tree->addTreeInfo($codeId, $codeName, $id);
                    if (!$is_tree) {
                        echo '<p>'.$subjectName . "资源无对应知识点，请添加对应知识点 id:{$Row['id']}".'</p>';
                        $this->Create_Exercise->rollback();
                        break;
                    }
                }catch(\Exception $e){
                    echo '<p>'.$e->getMessage()."</br>";
                    print_r("info: $code id:{$Row['id']}</p>");
                    $this->Create_Exercise->rollback();
                    break;
                }
            }

            if ($id != false && $truep != false && $log_id != false  && $is_tree && $eqp_id != false) {
                //insert connection info to table
                $this->Create_Exercise->commit();
            } else {
                $this->Create_Exercise->rollback();
            }

        }
    }

    private function __processDeleteVideoResource($data)
    {

        $clientIP = get_client_ip();
        $logModel = D('Exercises_log');
        $model = D('Exercises_createexercise');
        foreach($data as $key=>$val) {
            $setResult = true;
            M()->startTrans();
            $exerciseId = $this->Tree->getExerciseIdByResourceId($val);
            $result = D('Exercises_question_processinfo')->getQuestionInfo($exerciseId);
            if ($result['is_delete'] == STATE_DELETED) {
                M()->rollback();
                continue;
            }
            if ($result['is_lock'] == LOCKSTATE_LOCK) {
                M()->rollback();
                continue;
            }
            $setResult &= $model->deleteExercise($exerciseId);
            if($setResult)
                $setResult &=$logModel->insertLog($exerciseId,0,'删除习题',$clientIP,1,'system','',0,'',BEHAVIOR_ABNORMAL);
            if(!$setResult)
            {
                M()->rollback();
                continue;
            }
            M()->commit();
        }
    }

    private function __processModifyVideoResource($data)
    {
        $score = 5;
        foreach($data as $keyMain => $Row)
        {
            M()->startTrans();
            list($words, $analysis, $subjectName, $status,$code) = [$Row['name'],$Row['vid_image_path'],$Row['url'],$Row['status'],$Row['code']];
            $codes = explode('||||',$code);
            $exerciseId = $this->Tree->getExerciseIdByResourceId($Row['contactid']);
            if(empty($exerciseId)){
                // add record
                $codes = explode('||||',$code);
                $codebye = explode("||", $codes[0]);
                $codeId = explode("&amp;", $codebye[0]);
                $adddata['words'] = $words;
                $adddata['analysis'] = $analysis;
                $adddata['subject_name'] = $subjectName;
                $adddata['status'] = EXERCISE_STATE_OFFSHELF;
                $adddata['ordinary_type'] = 3;
                $adddata['types'] = 2;
                $adddata['create_at'] = time();
                $adddata['subject'] = $codeId[1];
                $adddata['home_topic_type'] = 100000;
                $adddata['count_score'] = $score;
                $adddata['score'] = $score;
                $exerciseId = $this->Create_Exercise->createExerciseInfo($adddata);
                // add join
                if($exerciseId){
                    //insert join info to knowledge_resource_exercise_contact
                    $joinData['resource_id'] = $Row['id'];
                    $joinData['contact_resource_id'] = $Row['contactid'];
                    $joinData['exercise_id'] = $exerciseId;
                    $addResult = $this->Tree->addResourceExerciseContact($joinData);
                    if(!$addResult){
                        echo '<p>'.$subjectName . "资源已经有关联 id:{$Row['id']}".'</p>';
                        $this->Create_Exercise->rollback();
                        continue;
                    }
                }
                $truep = $this->Exercises_question_processinfo->addCreatorInfo($exerciseId, 1, 'system');
                $log_id = $this->Exercises_Log->insertLog($exerciseId, $paperId = 0, '导入习题', get_client_ip(), 1, 'system', '个人导入习题', $poperatorId = 0, $poperatorName = '');
                $eqp_id = D('Exercises_question_processinfo')->setQuestionState($exerciseId, EXERCISE_STATE_ONSHELF, 1);
            }

            //edit exercise base info
            $editInfo['words'] = $words;
            $editInfo['analysis'] = $analysis;
            $editInfo['subject_name'] = $subjectName;
            $oldInfo = D('Exercises_createexercise')->getExerciseInfo($exerciseId);
            if($oldInfo['status'] == EXERCISE_STATE_ONSHELF || $oldInfo['status'] == EXERCISE_STATE_OFFSHELF){
                if($status == 2) //上架
                {
                    $status = EXERCISE_STATE_ONSHELF;
                }
                else{
                    $status = EXERCISE_STATE_OFFSHELF;
                }
                D('Exercises_question_processinfo')->setQuestionState($exerciseId, $status, 1);
            }

            D('Create_Exercise')->editExerciseInfo($editInfo,$exerciseId);
            //delete exercise tree
            $this->Tree->deleteKnowledgeByExerciseId($exerciseId);
            foreach($codes as $key => $val){
                $codebye = explode("||", $val);
                $codeId = explode("&amp;", $codebye[0]);
                $codeName = explode("&amp;", $codebye[1]);
                $is_tree = $this->Tree->addTreeInfo($codeId, $codeName, $exerciseId);
                if (!$is_tree) {
                    echo '<p>'.$subjectName . "资源无对应知识点，请添加对应知识点 id:{$Row['id']}".'</p>';
                    M()->rollback();
                    break;
                }
            }
            M()->commit();
        }
    }

    public function importBjVideoResource()
    {
        set_time_limit(0);
        $addResult = D('Knowledge_resource')->getAdditionalVideoResource();
        $this->__processAddVideoResource($addResult);
    }

    public function modifyBjVideoResourceExercise($idArray = [])
    {
        set_time_limit(0);
        if(empty($idArray)){
            $idArray = explode(',',getParameter('ids','str'));
        }
        foreach($idArray as $key => $val)
        {
            $id = [$val];
            $modifyResult = D('Knowledge_resource')->getModifyVideoResource($id);
            if(empty($modifyResult)) //resource has benn deleted
            {
                //delete exercises
                $this->__processDeleteVideoResource(array_column($modifyResult,'contactid'));
            }
            //delete non-exists exercise
            $currentContactIdArray = $this->Tree->getContactIdsByResourceId($val);
            foreach($currentContactIdArray as $null => $currentContactId){
                if(!in_array($currentContactId,array_column($modifyResult,'contactid')))
                {
                    $this->__processDeleteVideoResource([$currentContactId]);
                }
            }
            $this->__processModifyVideoResource($modifyResult);
        }


    }

    public function deleteBjVideoResourceExercise($idArray = [])
    {
        set_time_limit(0);
        if(empty($idArray)){
            $idArray = explode(',',getParameter('ids','str'));
        }
        //$result = D('Knowledge_resource')->getVideoResourcesAndKnowledge();
        $this->__processDeleteVideoResource($idArray);

    }

}