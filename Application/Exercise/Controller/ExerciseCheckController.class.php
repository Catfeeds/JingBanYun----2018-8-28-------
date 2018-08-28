<?php
namespace Exercise\Controller;
use Think\Controller;
use Think\Verify;
use QL\QueryList;

class ExerciseCheckController extends ExerciseGlobalController
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

    public function exerciseCheckPage(){

        $data = array();
        $header = array(
            array('name'=>'序号','field'=>'rownum','callback'=>function($value){return $value;}),
            array('name'=>'试题ID','field'=>'id','callback'=>function($value){return $value;}),
            array('name'=>'学科','field'=>'course_name','callback'=>function($value){return $value;}),
            array('name'=>'年级','field'=>'grade_name'),
            array('name'=>'知识点','field'=>'knowledge_name'),
            array('name'=>'锁定状态','field'=>'is_lock','callback'=>function($value){if($value[0] != LOCKSTATE_NORMAL)return '是'; else return '否';}),
            array('name'=>'题型','field'=>'topic_name'),
            array('name'=>'题目信息','field'=>'subject_name','callback'=>function($value){return $value;}),

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
                'markSubmitStartTime' => getParameter('markSubmitStartTime','str',false),
                'markSubmitEndTime' => getParameter('markSubmitEndTime','str',false),
                'startIndex' =>  getParameter('startIndex','int',false),
                'pageSize' =>  getParameter('pageSize','int',false),
                'paperName' => getParameter('paperName'  ,'str',false),
                'isOfPaper' => getParameter('isOfPaper'  ,'int',false),
                'lock'=>getParameter('lock'  ,'int',false),
                'bNeedKnowledge' => true,
            );
        $condition['status'] =getParameter('status'  ,'int',false)===false ? array('in','\''.EXERCISE_STATE_UNINBOUND.'\'') : getParameter('status'  ,'int',false);

        $count =  D('Exercises_question_processinfo')->getQuestionCount($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
        $list =  D('Exercises_question_processinfo')->getQuestionList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
        if(!$export)
            $header[] = array('name'=>'操作','field'=>'is_lock,id','callback'=>function($value){if($value[0] == LOCKSTATE_LOCK) return 'unlock'; else return 'markAgain,lock,inbound,reject';} );


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

    function exerciseCheckMgmt(){
        $this->assign('parent', '试题审核管理');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '');
        $courses = D('Exercises_Course')->getCourseList();
        $province = D('Dict_citydistrict')->getProvince();
        $creators = D('Exercises_question_processinfo')->getDistinctCreator();
        $sections = C('Studysection');
        $this->assign('creatorList',$creators);
        $this->assign('courseList',$courses);
        $this->assign('sections',$sections);
        $this->assign('provinceList',$province);
        $this->display();
    }

    function exerciseCheckDetails(){
        $this->assign('parent', '试题审核管理');
        $this->assign('parentHref', U('ExerciseCheck/exerciseCheckMgmt'));
        $this->assign('own', ' >> 试题审核详情');

        $id = I('id');
        if(empty($id)) {
            die('参数错误');
        }
        $exercise_info = $this->Create_Exercise->getExerciseInfo( $id );
        $this->verifyExerciseOperationAuth($exercise_info,EXERCISE_STATE_UNINBOUND);
        $difficultyList = C('difficulty');
        $difficultyName = '';
        $difficulty = D('Exercises_createexercise')->getExerciseDifficulty($id);
            foreach ($difficultyList as $key2 => $val2) {
                if ($difficulty == $val2['id'])
                    $difficultyName = $val2['name'];
            }

        $textbook_tree_List = D('Exercises_textbook_tree_info_createexercise')->getKnowledgeListByExerciseId($id);
        $this->textbook_tree_List = $textbook_tree_List;
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
            $exercise_info['difficulty_name'] = $difficultyName;
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
            $exercise_info['difficulty_name'] = $difficultyName;
            $this->exercise_info = $exercise_info;
        }
        //创建防止重复提交token
        creatToken();
        $this->display();
    }

    //添加习题
    public function addExercise( $info ) {

        $study_section = $info['studyId'];//学段
        $subject = $info['courseId'];//学科
        $topic_type = $info['type'];//
        $subject_name = $info['message'];//题目类型题目
        $home_topic_type = $info['Problempresentation'];//前台展示题型
        $exerciseNums = $info['exerciseNums'];//选项数量
        $right_key = $info['trueAnswer'];//正确答案
        $analysis = $info['messageJx'];//解析
        $answer = json_encode($info['arrayMessage']);//答案
        $count_score = $info['Fractiondata'];
        $score = $info['Fractiondata'];
        $count_score = rtrim($count_score,',');


        if (strpos($count_score,',')!=false) {
            $count_score = explode(',',$count_score);
            $count_score = array_sum($count_score);
        }

        $score = rtrim($score,',');

        $answer_select = json_encode($info['answer_select']);//答案选项
        $create_at = time();
        $update_at = time();

        $data = [];
        $data['study_section'] = $study_section;
        $data['subject'] = $subject;
        $data['topic_type'] = $topic_type;
        $data['subject_name'] = $subject_name;
        $data['search_name'] = $subject_name;
        $data['home_topic_type'] = $home_topic_type;
        //$data['exerciseNums'] = $exerciseNums;
        $data['right_key'] = $right_key;
        $data['analysis'] = $analysis;
        $data['answer'] = $answer;
        $data['count_score'] = $count_score;
        $data['score'] = $score;
        $data['create_at'] = $create_at;
        $data['update_at'] = $update_at;
        $data['num'] = $exerciseNums;
        $data['answer_select'] = $answer_select;
        $data['exercise_source'] = 4;
        $data['status'] = 25;

        $this->Create_Exercise->startTrans();
        $id = $this->Create_Exercise->createExerciseInfo($data);
        if ( $id ) {
            $truep = $this->Exercises_question_processinfo->addCreatorInfo($id,session('admin.id'),session('admin.user_name'));
            $log_id = $this->Exercises_Log->insertLog($id,$paperId=0,'数据迁移',get_client_ip(),session('admin.id'),session('admin.user_name'),'个人录入习题',$poperatorId=0,$poperatorName='');

            if (!empty($big_paper_id) && !empty($paper_id)) { //录入试卷的习题
                $paperdata['exercise_id'] = $id;
                $paperdata['paper_id'] = $paper_id;
                $paperdata['big_question_id'] = $big_paper_id;
                $paperdata['create_at'] = time();
                $paperdata['big_order'] = $id;
                $p_c_id = $this->Exercises_parper_concat->addParperConcat($paperdata); //关联试卷试题的关系

                if ( $truep && $log_id && $p_c_id ) {
                    $this->Exercises_paper_bigquestion->updateParperBigSetNum($paper_id,$big_paper_id);//把大题中的小题数量加1
                    $this->Create_Exercise->commit();
                    $res['code'] = 200;
                } else {
                    $this->Create_Exercise->rollback();
                    $res['code'] = 400;
                    $res['msg'] = '添加失败';
                }

            } else { //录入单独的习题

                if ( $truep && $log_id ) {
                    $this->Create_Exercise->commit();
                    $res['code'] = 200;
                } else {
                    $this->Create_Exercise->rollback();
                    $res['code'] = 400;
                    $res['msg'] = '添加失败';
                }
            }


        } else {
            $this->Create_Exercise->rollback();
            $res['msg'] = '添加失败';
            $res['code'] = 400;
        }
        return $res;
        //$this->ajaxReturn($res);
    }

    public function pieceSelectInfo($data,&$errorinfo) {

        if (is_array($data['body'])) {
            $info = array(
                'answer' => json_encode($data['body']),
                'num' => count($data['body']),
                'subject_name' => $data['title'],
                'topic_type' => $data['type'],
                'right_key' => rtrim($data['right_key'],','),
            );
        } else {
            $info = array(
                'answer' => json_encode(explode('|',$data['body'])),
                'num' => count(explode('|',$data['body'])),
                'subject_name' => $data['title'],
                'topic_type' => $data['type'],
                'right_key' => rtrim($data['right_key'],','),
            );
        }
        return $info;
    }

    public function pieceBlanksInfo($data,&$errorinfo) {

        if (is_array($data['body'])) {
            $info = array(
                'answer' => json_encode($data['body']),
                'num' => count($data['body']),
                'subject_name' => $data['title'],
                'topic_type' => $data['type'],
                'right_key' => $data['right_key'],
                'answer' => $data['right_key'],

            );
        } else {
            $info = array(
                'answer' => json_encode(explode('|',$data['body'])),
                'num' => count(explode('|',$data['body'])),
                'subject_name' => $data['body'],
                'topic_type' => $data['type'],
                'right_key' => $data['right_key'],
                'answer' => $data['right_key'],
            );
        }

        return $info;

    }

    public function pieceSelectBlanksInfo($data,&$errorinfo) {
        $info = array(
            'answer' => json_encode($data['arrayMessage']),
            'answer_select' => json_encode($data['AnswerMessage']),
            'num' => $data['exerciseNums'],
            'subject_name' => $data['message'],
            'score' => implode(',',$data['fenshu']),
            'count_score' => array_sum($data['fenshu']),
            'analysis' => $data['messageJx'],
            'topic_type' => $data['type'],
            'small_exercise_number'=> $data['topicNum'],
        );

        if (empty($info['answer'])) {
            $errorinfo = '选择填空题答案未填写';
        }

        if (empty($info['answer_select'])) {
            $errorinfo = '选择填空题答案选项未选择';
        }

        if (empty($info['subject_name'])) {
            $errorinfo = '选择填空题二级题目未填写';
        }
        if (empty($info['score'])) {
            $errorinfo = '选择填空题分数未填写';
        }
        if (empty($info['analysis'])) {
            $errorinfo = '选择填空题解析未填写';
        }

        return $info;
    }

    public function pieceLineInfo($data,&$errorinfo) {
        $info = array(
            'answer' => json_encode($data['arrayMessage']),
            'answer_select' => json_encode($data['AnswerMessage']),
            'num' => $data['exerciseNums'],
            'subject_name' => $data['message'],
            'score' => $data['fenshu'],
            'count_score' => array_sum(explode(',',$data['fenshu'])),
            'analysis' => $data['messageJx'],
            'topic_type' => $data['type'],
            'small_exercise_number'=> $data['topicNum'],
        );

        if (empty($info['answer'])) {
            $errorinfo = '连线题答案未填写';
        }
        if (empty($info['answer_select'])) {
            $errorinfo = '连线题答案选项未选择';
        }
        if (empty($info['subject_name'])) {
            $errorinfo = '连线题二级题目未填写';
        }
        if (empty($info['score'])) {
            $errorinfo = '连线题分数未填写';
        }
        if (empty($info['analysis'])) {
            $errorinfo = '连线题解析未填写';
        }
        return $info;
    }

    public function pieceMappingInfo($data,&$errorinfo) {
        $info = array(
            'answer' => json_encode($data['arrayMessage']),
            'subject_name' => $data['message'],
            'score' => $data['fenshu'],
            'count_score' => $data['fenshu'],
            'analysis' => $data['messageJx'],
            'topic_type' => $data['type'],
            'small_exercise_number'=> $data['topicNum'],
        );

        if (empty($info['answer'])) {
            $errorinfo = '作图题答案未填写';
        }
        if (empty($info['subject_name'])) {
            $errorinfo = '作图题二级题目未填写';
        }
        if (empty($info['score'])) {
            $errorinfo = '作图题分数未填写';
        }
        if (empty($info['analysis'])) {
            $errorinfo = '作图题解析未填写';
        }

        return $info;
    }

    public function pieceAnswerInfo($data,&$errorinfo) {
        $info = array(
            'answer' => json_encode($data['answer']),
            'right_key' => $data['right_key'],
            'subject_name' => $data['title'],
            'search_name' => $data['title'],
            'topic_type' => $data['type'],
        );
        return $info;
    }

    //添加复合题
    public function addCompositeExercise( $addinfo ) {
        $exerciseList = $addinfo['exerciseList'];

        if (empty($exerciseList['firstmessage'])) {
            $res['code'] = 400;
            $res['msg'] = '请先生成模板填写数据';
            $this->ajaxReturn($res);
        }

        $firstdata = array(
            'search_name' => $exerciseList['firstmessage'],
            'subject_name' => $exerciseList['firstmessage'],
            'study_section' => $exerciseList['studyId'],
            'subject' => $exerciseList['courseId'],
            'home_topic_type' => $exerciseList['exercisesId'],
            'points' => $exerciseList['fenshu'],
            'create_at' => time(),
            'exercise_type' => 2,
            'status' => 25,
            'exercise_source' => 4,
        );

        $twodata = $exerciseList['list'];//所有的二级题干

        $twodata = array_values($twodata);
        $lock = true;
        //设置全局锁
        //入库一级题干
        $this->Create_Exercise->startTrans();
        $firstid = $this->Create_Exercise->createExerciseInfo($firstdata);
        if ( $firstid ) {
            $truep = $this->Exercises_question_processinfo->addCreatorInfo($firstid,session('admin.id'),session('admin.user_name'));
            $log_id = $this->Exercises_Log->insertLog($firstid,$paperId=0,'数据迁移',get_client_ip(),session('admin.id'),session('admin.user_name'),'个人录入习题',$poperatorId=0,$poperatorName='');
            if ( $truep && $log_id ) {

                if (!empty($big_paper_id) && !empty($paper_id)) { //录入试卷的习题

                    $paperdata['exercise_id'] = $firstid;
                    $paperdata['paper_id'] = $paper_id;
                    $paperdata['big_question_id'] = $big_paper_id;
                    $paperdata['create_at'] = time();
                    $paperdata['big_order'] = $firstid;
                    $p_c_id = $this->Exercises_parper_concat->addParperConcat($paperdata); //关联试卷试题的关系

                    if (  $p_c_id ) {
                        $this->Exercises_paper_bigquestion->updateParperBigSetNum($paper_id,$big_paper_id);//把大题中的小题数量加1
                        $res['code'] = 200;
                    } else {
                        $this->Create_Exercise->rollback();
                        $res['code'] = 400;
                    }

                } else { //录入单独的习题
                    $res['code'] = 200;
                }

            } else {
                $this->Create_Exercise->rollback();
                $res['code'] = 400;
                $res['msg'] = '添加失败';
                $lock = false;
            }
        } else {
            $this->Create_Exercise->rollback();
            $res['msg'] = '添加失败';
            $res['code'] = 400;
            $lock = false;
        }
        //入库二级题干
        $firstdata['parent_id'] = $firstid;
        $errorinfo = '';
        $savesearch_name = $exerciseList['firstmessage'];

        foreach ($twodata as $k=>$v) {
            switch ($v['type']) {
                case 1:
                    $data = $this->pieceSelectInfo($v,$errorinfo);//拼装选择题
                    break;
                case 2:
                    $data = $this->pieceBlanksInfo($v,$errorinfo);//拼装文字填空
                    break;
                case 3:
                    $data = $this->pieceSelectBlanksInfo($v,$errorinfo);//拼装选择填空
                    break;
                case 4:
                    $data = $this->pieceLineInfo($v,$errorinfo);//拼装连线
                    break;
                case 5:
                    $data = $this->pieceMappingInfo($v,$errorinfo);//拼装作图
                    break;
                case 6:
                    $data = $this->pieceAnswerInfo($v,$errorinfo);//拼装解答
                    break;
            }

            if($errorinfo!='') {
                $this->Create_Exercise->rollback();
                $error['code'] = 400;
                $error['msg'] = $errorinfo;
                $this->ajaxReturn($error);
            }

            $data = array_merge($firstdata,$data);
            $is_add =  $this->Create_Exercise->createExerciseInfo($data);
            if ($is_add) {
                $truep = $this->Exercises_question_processinfo->addCreatorInfo($is_add,session('admin.id'),session('admin.user_name'));
                //$log_id = $this->Exercises_Log->insertLog($is_add,$paperId=0,'创建习题',get_client_ip(),session('admin.id'),session('admin.user_name'),'个人录入习题',$poperatorId=0,$poperatorName='');
                if ( $truep ) {
                    $res['code'] = 200;
                } else {
                    $this->Create_Exercise->rollback();
                    $res['code'] = 400;
                    $res['msg'] = '添加失败';
                    $lock = false;
                }
            } else {
                $res['code'] = 400;
                $lock = false;
            }
            $savesearch_name.=$v['message'];

        }

        if($lock==true) {
            $saveid = $this->Create_Exercise->updateExerciseInfo($firstid,$savesearch_name);
            if ($saveid !== false) {
                $res['code'] = 200;
                $this->Create_Exercise->commit();
            } else{
                $res['code'] = 400;
                $res['msg'] = '提交失败';
                $this->Create_Exercise->rollback();
            }

        } else {
            $res['code'] = 400;
            $res['msg'] = '提交失败';
            $this->Create_Exercise->rollback();
        }

        return $res;
        //$this->ajaxReturn($res);
    }

    //老习题导入
    public function importOldExercise() {
        $where['biz_exercise_template.template_name'] = '单项文字选择题';
        $list = M('biz_exercise_library_chapter')
            ->join('left join biz_exercise_library on biz_exercise_library.chapter_id=biz_exercise_library_chapter.id')
            ->join('left join biz_exercise_template on biz_exercise_template.id=biz_exercise_library.type')
            ->field('biz_exercise_library.*,biz_exercise_template.template_name,biz_exercise_library_chapter.grade_id,biz_exercise_library_chapter.course_id')
            ->limit(2)
            ->order('id desc')
            ->where($where)
            ->select();
        echo "------------------------格式化后的数据--------------------".PHP_EOL;

        foreach ($list as $k=>$v) {
            switch ($v['template_name']){
                case '判断题'://选择填空     -->选择题
                    $data = $this->checkpanduan($v);
                    break;
                case '判断题[含多个小题]': //选择填空  -->复合题选择题
                    $data = $this->checkpanduanmore($v);
                    break;
                case '单项文字选择题': //选择题   -->选择题
                    $data = $this->checkwenzi($v);
                    break;
                case '单项选择题[含多个小题]':   //-->复合题选择题
                    $data = $this->checkxuanzhemore($v);
                    break;

                case '填空题':   //-->文字填空
                    $data = $this->checktiankong($v);

                    break;
                case '填空题[含多个小题]': //-->复合题文字填空
                    $data = $this->checktiankongmore($v);
                    break;

                case '多项文字选择题': //-->选择题
                    $data = $this->checkwenzimore($v);
                    break;
                case '多项选择题[含多个小题]': //-->复合题选择题
                    $data = $this->checkwenzhemore($v);
                    break;
                case '连线题':  //-->连线题
                    $data = $this->checklianxian($v);
                    break;
                case '问答题': //-->解答题
                    $data = $this->checkwenda($v);

                    break;
                case '问答题[含多个小题]': //-->复合题解答题
                    $data = $this->checkwendamore($v);
                    break;

                case '图形问答题': //没有

                    break;
                case '单项图形选择题': //没有

                    break;
            }

            if( $data['code'] ==200) {
                echo "成功";
            }
        }


    }


    public function checktiankongmore($info) {
        $data['questions'] = $info['questions'];
        $data['fenshu'] = $info['points'];
        $data['grade_id'] = $info['grade_id'];
        $data['course_id'] = $info['course_id'];
        $data['body'] = $info['body'];

        $rules = array(
            'main' => array('.subquestion','html'),
        );

        $childrules = array(
            'title' => array('.subquestionTitle','html'),
            'answer' => array('.subquestionAnswer','html'),
        );

        $questList = QueryList::Query($data['body'],$rules)->data; //一级

        $sendlist = [];
        foreach ($questList as $k=>$v) {
            $childquestList = QueryList::Query($v['main'],$childrules)->data; //二级
            $savedata['title'] = $childquestList[0]['title'];
            $savedata['answer'] = $childquestList[0]['answer'];

            $answer = trimall(strip_tags($savedata['answer']));
            preg_match_all("/#答案：(.*)#/iU",$answer,$pregdata);
            $savedata['right_key'] = implode(',',$pregdata[1]);
            $questList[$k] = $childquestList;
            unset($questList[$k]['main']);

            if (empty($questList[$k][0]['title'])) {
                unset($questList[$k]);
            }

            $savedata['body'] = $savedata['title'].$savedata['answer'];
            $savedata['body']=preg_replace("/\[#答案：(.*)#]/iU",'ㄖ',$savedata['body']);
            $savedata['type'] = 2;
            $sendlist[$k] = $savedata;
        }

        $data['childinfo'] = $sendlist;
        $data['type'] = 2;
        $data['grade_id'] = $info['grade_id'];
        $data['course_id'] = !empty($info['course_id'])?$info['course_id']:1;

        if ($data['grade_id']>1 && $data['grade_id']<= 6) {
            $addinfo['exerciseList']['studyId'] = 1;//学科
        }

        if ($data['grade_id']>6 && $data['grade_id']<= 9) {
            $addinfo['exerciseList']['studyId'] = 2;//学科
        }
        if ($data['grade_id']>9 && $data['grade_id']<= 12) {
            $addinfo['exerciseList']['studyId'] = 3;//学科
        }
        $addinfo['exerciseList']['firstmessage'] = $data['questions'];
        $addinfo['exerciseList']['courseId'] = $data['course_id'];
        $addinfo['exerciseList']['fenshu'] = $data['fenshu'];
        $addinfo['exerciseList']['list'] = $sendlist;
        $addprint = $this->addCompositeExercise( $addinfo );
        return $addprint;


    }


    public function checkwenzhemore($info){
        $data['questions'] = $info['questions'];
        $data['fenshu'] = $info['points'];
        $data['body'] = $info['body'];

        $rules = array(
            'main' => array('.subquestion','html'),
        );

        $childrules = array(
            'title' => array('.subquestionTitle','text'),
            'answer' => array('.subquestionAnswer','html'),
        );

        $answer= ['A','B','C','D','E','F','G','H'];
        $questList = QueryList::Query($data['body'],$rules)->data; //一级

        foreach ($questList as $k=>$v) {
            $childquestList = QueryList::Query($v['main'],$childrules)->data; //二级
            $childInfo = explode('</p>',$childquestList[0]['answer']);

            foreach ($childInfo as $ck=>$cv) {
                $title = trimall(strip_tags($cv));
                if ($title[0] =='#') {
                    $questList[$k]['right_key'] .=  $answer[$ck].',';
                }
                $questList[$k]['title'] = $childquestList[0]['title'];
            }

            $childInfo = array_filter($childInfo);

            $questList[$k]['body'] = $childInfo;
            $questList[$k]['type'] = 1;
            unset($questList[$k]['main']);
        }
        $data['childinfo'] = $questList;
        unset($data['body']);

        $data['type'] = 1;
        $data['grade_id'] = $info['grade_id'];
        $data['course_id'] = !empty($info['course_id'])?$info['course_id']:1;

        if ($data['grade_id']>1 && $data['grade_id']<= 6) {
            $addinfo['exerciseList']['studyId'] = 1;//学科
        }

        if ($data['grade_id']>6 && $data['grade_id']<= 9) {
            $addinfo['exerciseList']['studyId'] = 2;//学科
        }
        if ($data['grade_id']>9 && $data['grade_id']<= 12) {
            $addinfo['exerciseList']['studyId'] = 3;//学科
        }
        $addinfo['exerciseList']['firstmessage'] = $data['questions'];
        $addinfo['exerciseList']['courseId'] = $data['course_id'];
        $addinfo['exerciseList']['fenshu'] = $data['fenshu'];
        $addinfo['exerciseList']['list'] = $questList;

        $addprint = $this->addCompositeExercise( $addinfo );
        return $addprint;
    }

    public function checkwendamore($info) {

        $data['questions'] = $info['questions'];
        $data['fenshu'] = $info['points'];
        $data['grade_id'] = $info['grade_id'];
        $data['course_id'] = $info['course_id'];
        $data['body'] = $info['body'];

        $rules = array(
            'main' => array('.subquestion','html'),
        );

        $childrules = array(
            'title' => array('.subquestionTitle','text'),
            'answer' => array('.subquestionAnswer','text'),
        );

        $questList = QueryList::Query($data['body'],$rules)->data; //一级
        $sendlist=[];
        foreach ($questList as $k=>$v) {
            $childquestList = QueryList::Query($v['main'],$childrules)->data; //二级
            $savedata['title'] = $childquestList[0]['title'];
            $savedata['answer'] = $childquestList[0]['answer'];

            $answer = trimall(strip_tags($savedata['answer']));
            preg_match_all("/#(.*)#/iU",$answer,$pregdata);

            $savedata['right_key'] = $pregdata[1][0];
            $questList[$k] = $childquestList;
            unset($questList[$k]['main']);

            if (empty($savedata['title'])) {
                unset($questList[$k]);
            }

            $savedata['type'] = 6;
            $sendlist[$k] = $savedata;

        }

        unset($data['body']);

        $data['type'] = 6;
        $data['grade_id'] = $info['grade_id'];
        $data['course_id'] = !empty($info['course_id'])?$info['course_id']:1;

        if ($data['grade_id']>1 && $data['grade_id']<= 6) {
            $addinfo['exerciseList']['studyId'] = 1;//学科
        }

        if ($data['grade_id']>6 && $data['grade_id']<= 9) {
            $addinfo['exerciseList']['studyId'] = 2;//学科
        }
        if ($data['grade_id']>9 && $data['grade_id']<= 12) {
            $addinfo['exerciseList']['studyId'] = 3;//学科
        }
        $addinfo['exerciseList']['firstmessage'] = $data['questions'];
        $addinfo['exerciseList']['courseId'] = $data['course_id'];
        $addinfo['exerciseList']['fenshu'] = $data['fenshu'];
        $addinfo['exerciseList']['list'] = $sendlist;
        $addprint = $this->addCompositeExercise( $addinfo );
        return $addprint;
    }


    public function checklianxian($info) {
        $data['questions'] = $info['questions'];
        $data['fenshu'] = $info['points'];
        $data['grade_id'] = $info['grade_id'];
        $data['course_id'] = $info['course_id'];
        $data['body'] = $info['body'];

        $data['grade_id'] = $info['grade_id'];
        $data['course_id'] = !empty($info['course_id'])?$info['course_id']:1;
        $data['type'] = 4;
        if ($data['grade_id']>1 && $data['grade_id']<= 6) {
            $addinfo['studyId'] = 1;//学科
        }

        if ($data['grade_id']>6 && $data['grade_id']<= 9) {
            $addinfo['studyId'] = 2;//学科
        }
        if ($data['grade_id']>9 && $data['grade_id']<= 12) {
            $addinfo['studyId'] = 3;//学科
        }

        //入库操作
        $addinfo['courseId'] = $data['course_id'];//学科
        $addinfo['type'] = $data['type'];//
        $addinfo['message'] = $data['questions'];//题目类型题目
        $addinfo['Problempresentation'] = $data['course_id'];//前台展示题型
        $addinfo['exerciseNums'] = count($data['body']);//选项数量
        $addinfo['trueAnswer'] = $data['right_key'];//正确答案
        $addinfo['arrayMessage'] = $data['body'];//答案
        $addinfo['Fractiondata'] = $data['fenshu'];
        $addinfo['answer_select'] = $data['body'];//答案选项
        $addprint = $this->addExercise( $addinfo );
        return $addprint;
    }

    public function checkwenzimore( $info ) {

        $setA = ['A','B','C','D','E','F','G','H'];
        $infobody = explode('</p>',$info['body']);
        $infobody = array_filter($infobody);
        $right = '';
        foreach ($infobody as $k=>$v) {
            $title = strip_tags($v);
            if (trimall($title[0]) =='#') {
                $right .= ','.$setA[$k];
            }
        }
        $data['body'] = $infobody;
        $data['fenshu'] = $info['points'];
        $data['questions'] = $info['questions'];
        $data['right_key'] = ltrim($right,',');
        $data['grade_id'] = $info['grade_id'];
        $data['course_id'] = $info['course_id'];

        $data['grade_id'] = $info['grade_id'];
        $data['course_id'] = !empty($info['course_id'])?$info['course_id']:1;
        $data['type'] = 1;
        if ($data['grade_id']>1 && $data['grade_id']<= 6) {
            $addinfo['studyId'] = 1;//学科
        }

        if ($data['grade_id']>6 && $data['grade_id']<= 9) {
            $addinfo['studyId'] = 2;//学科
        }
        if ($data['grade_id']>9 && $data['grade_id']<= 12) {
            $addinfo['studyId'] = 3;//学科
        }

        //入库操作
        $addinfo['courseId'] = $data['course_id'];//学科
        $addinfo['type'] = $data['type'];//
        $addinfo['message'] = $data['questions'];//题目类型题目
        $addinfo['Problempresentation'] = $data['course_id'];//前台展示题型
        $addinfo['exerciseNums'] = count($data['body']);//选项数量
        $addinfo['trueAnswer'] = $data['right_key'];//正确答案
        $addinfo['arrayMessage'] = $data['body'];//答案
        $addinfo['Fractiondata'] = $data['fenshu'];
        $addinfo['answer_select'] = $data['right_key'];//答案选项
        $addprint = $this->addExercise( $addinfo );

        return $addprint;

    }

    public function checkxuanzhemore($info) {
        $data['questions'] = $info['questions'];
        $data['fenshu'] = $info['points'];
        $data['body'] = $info['body'];

        $rules = array(
            'main' => array('.subquestion','html'),
        );

        $childrules = array(
            'title' => array('.subquestionTitle','html'),
            'answer' => array('.subquestionAnswer','html'),
        );

        $answer= ['A','B','C','D','E','F','G','H'];
        $questList = QueryList::Query($data['body'],$rules)->data; //一级

        foreach ($questList as $k=>$v) {
            $childquestList = QueryList::Query($v['main'],$childrules)->data; //二级

            $childInfo = explode('</p>',$childquestList[0]['answer']);
            foreach ($childInfo as $ck=>$cv) {
                $title = trimall(strip_tags($cv));
                if ($title[0] =='#') {
                    $questList[$k]['right_key'] = $answer[$ck];
                    break;
                }
            }
            $childInfo = array_filter($childInfo);
            $questList[$k]['body'] = $childInfo;
            $questList[$k]['title'] = $childquestList[0]['title'];
            $questList[$k]['type'] = 1;
            unset($questList[$k]['main']);
        }

        $data['childinfo'] = $questList;
        unset($data['body']);

        $data['grade_id'] = $info['grade_id'];
        $data['course_id'] = !empty($info['course_id'])?$info['course_id']:1;
        $data['type'] = 1;
        if ($data['grade_id']>1 && $data['grade_id']<= 6) {
            $addinfo['exerciseList']['studyId'] = 1;//学科
        }

        if ($data['grade_id']>6 && $data['grade_id']<= 9) {
            $addinfo['exerciseList']['studyId'] = 2;//学科
        }
        if ($data['grade_id']>9 && $data['grade_id']<= 12) {
            $addinfo['exerciseList']['studyId'] = 3;//学科
        }
        $addinfo['exerciseList']['firstmessage'] = $data['questions'];
        $addinfo['exerciseList']['courseId'] = $data['course_id'];
        $addinfo['exerciseList']['fenshu'] = $data['fenshu'];
        $addinfo['exerciseList']['list'] = $questList;

        $addprint = $this->addCompositeExercise( $addinfo );
        return $addprint;
    }

    public function checkpanduan($info) {
        $data['questions'] = $info['questions'];
        $data['fenshu'] = $info['points'];
        $data['body'] = $info['body'];
        $data['explainindetail'] = $info['explainindetail'];

        $rules = array(
            'answerA' => array('.exercise_option:eq(0)','text'),
            'answerB' => array('.exercise_option:eq(1)','text'),
        );
        $questList = QueryList::Query($data['body'],$rules)->data; //一级

        if (trimall($questList[0]['answerA'][0]) =='#') {
            $data['right_key'] = 'A';
        }

        if (trimall($questList[0]['answerB'][0]) =='#') {
            $data['right_key'] = 'B';
        }
        $data['body'] = str_replace('#','',$data['body']);
        $data['body'] = array_filter(explode('</p>',$data['body']));
        $data['grade_id'] = $info['grade_id'];
        $data['course_id'] = !empty($info['course_id'])?$info['course_id']:1;
        $data['type'] = 1;
        if ($data['grade_id']>1 && $data['grade_id']<= 6) {
            $addinfo['studyId'] = 1;//学科
        }

        if ($data['grade_id']>6 && $data['grade_id']<= 9) {
            $addinfo['studyId'] = 2;//学科
        }
        if ($data['grade_id']>9 && $data['grade_id']<= 12) {
            $addinfo['studyId'] = 3;//学科
        }

        //入库操作
        $addinfo['courseId'] = $data['course_id'];//学科
        $addinfo['type'] = $data['type'];//
        $addinfo['message'] = $data['questions'];//题目类型题目
        $addinfo['Problempresentation'] = $data['course_id'];//前台展示题型
        $addinfo['exerciseNums'] = count($data['body']);//选项数量
        $addinfo['trueAnswer'] = $data['right_key'];//正确答案
        $addinfo['arrayMessage'] = $data['body'];//答案
        $addinfo['Fractiondata'] = $data['fenshu'];
        $addinfo['answer_select'] = $data['right_key'];//答案选项
        $addinfo['messageJx'] = $data['explainindetail'];//解析

        $addprint = $this->addExercise( $addinfo );
        return $addprint;
    }

    public function checkpanduanmore($info) {
        $data['questions'] = $info['questions'];
        $data['fenshu'] = $info['points'];
        $data['body'] = $info['body'];

        $rules = array(
            'main' => array('.subquestion','html'),
        );

        $childrules = array(
            'title' => array('.subquestionTitle','text'),
            'answerA' => array('.subquestionAnswer>p:eq(0)','text'),
            'answerB' => array('.subquestionAnswer>p:eq(1)','text'),
        );

        $questList = QueryList::Query($data['body'],$rules)->data; //一级

        foreach ($questList as $k=>$v) {
            $childquestList = QueryList::Query($v['main'],$childrules)->data; //二级

            foreach($childquestList as $newarray) {

                if (trimall($newarray['answerA'][0]) =='#') {
                    $newarray['right_key'] = 'A';
                }

                if (trimall($newarray['answerB'][0]) =='#') {
                    $newarray['right_key'] = 'B';
                }
                $newarray['body'] = $newarray['answerA'].'|'.$newarray['answerB'];
                $newarray['type'] = 1;
                $questList[$k] = $newarray;
            }

            unset($questList[$k]['main']);
        }
        unset($data['body']);
        $data['grade_id'] = $info['grade_id'];
        $data['course_id'] = !empty($info['course_id'])?$info['course_id']:1;
        $data['chlidInfo'] = $questList;

        if ($data['grade_id']>1 && $data['grade_id']<= 6) {
            $addinfo['exerciseList']['studyId'] = 1;//学科
        }

        if ($data['grade_id']>6 && $data['grade_id']<= 9) {
            $addinfo['exerciseList']['studyId'] = 2;//学科
        }
        if ($data['grade_id']>9 && $data['grade_id']<= 12) {
            $addinfo['exerciseList']['studyId'] = 3;//学科
        }
        $addinfo['exerciseList']['firstmessage'] = $data['questions'];
        $addinfo['exerciseList']['courseId'] = $data['course_id'];
        $addinfo['exerciseList']['fenshu'] = $data['fenshu'];
        $addinfo['exerciseList']['list'] = $data['chlidInfo'];

        $addprint = $this->addCompositeExercise( $addinfo );
        return $addprint;
    }

    public function checktiankong($info) {
        $data['questions'] = $info['questions'];
        $data['body'] = $info['body'];
        $data['fenshu'] = $info['points'];
        preg_match_all("/#答案：(.*)#]/iU",$info['body'],$pregdata);
        $data['right_key'] = $pregdata[1];
        $data['grade_id'] = $info['grade_id'];
        $data['course_id'] = !empty($info['course_id'])?$info['course_id']:1;
        $data['explainindetail'] = $info['explainindetail'];

        $data['type'] = 2;
        if ($data['grade_id']>1 && $data['grade_id']<= 6) {
            $addinfo['studyId'] = 1;//学科
        }

        if ($data['grade_id']>6 && $data['grade_id']<= 9) {
            $addinfo['studyId'] = 2;//学科
        }
        if ($data['grade_id']>9 && $data['grade_id']<= 12) {
            $addinfo['studyId'] = 3;//学科
        }

        $questions=preg_replace("/\[#答案：(.*)#]/iU",'ㄖ',$data['questions']);

        $preg_body=preg_replace("/\[#答案：(.*)#]/iU",'ㄖ',$data['body']);

        $preg_body = array_filter(explode('</p>',$preg_body));
        //入库操作
        $addinfo['courseId'] = $data['course_id'];//学科
        $addinfo['type'] = $data['type'];//
        $addinfo['message'] = $questions.implode($preg_body);//题目类型题目
        $addinfo['Problempresentation'] = $data['course_id'];//前台展示题型
        $addinfo['exerciseNums'] = count($preg_body);//选项数量
        $addinfo['trueAnswer'] = $data['right_key'];//正确答案
        $addinfo['arrayMessage'] = $data['right_key'];//答案
        $addinfo['Fractiondata'] = $data['fenshu'];
        $addinfo['answer_select'] = $data['right_key'];//答案选项
        $addinfo['messageJx'] = $data['explainindetail'];//解析

        $addprint = $this->addExercise( $addinfo );
        return $addprint;
    }

    public function checkwenda($info) {
        $data['questions'] = $info['questions'];
        $data['fenshu'] = $info['points'];
        $data['grade_id'] = $info['grade_id'];
        $data['course_id'] = $info['course_id'];
        $data['body'] = $info['body'];
        $data['right_key'] = $info['explainindetail'];

        $data['grade_id'] = $info['grade_id'];
        $data['course_id'] = !empty($info['course_id'])?$info['course_id']:1;
        $data['type'] = 6;
        if ($data['grade_id']>1 && $data['grade_id']<= 6) {
            $addinfo['studyId'] = 1;//学科
        }

        if ($data['grade_id']>6 && $data['grade_id']<= 9) {
            $addinfo['studyId'] = 2;//学科
        }
        if ($data['grade_id']>9 && $data['grade_id']<= 12) {
            $addinfo['studyId'] = 3;//学科
        }

        //入库操作
        $addinfo['courseId'] = $data['course_id'];//学科
        $addinfo['type'] = $data['type'];//
        $addinfo['message'] = $data['questions'];//题目类型题目
        $addinfo['Problempresentation'] = $data['course_id'];//前台展示题型
        $addinfo['exerciseNums'] = count($data['body']);//选项数量
        $addinfo['trueAnswer'] = $data['right_key'];//正确答案
        $addinfo['arrayMessage'] = $data['body'];//答案
        $addinfo['Fractiondata'] = $data['fenshu'];
        $addinfo['answer_select'] = $data['body'];//答案选项

        $addprint = $this->addExercise( $addinfo );
        return $addprint;
    }

    public function checkwenzi( $info ) {

        $setA = ['A','B','C','D','E','F','G','H'];
        $infobody = explode('</p>',$info['body']);
        $infobody = array_filter($infobody);

        foreach ($infobody as $k=>$v) {
            $title = strip_tags($v);
            if (trimall($title[0]) =='#') {
                $right = $setA[$k];
            }
        }

        $data['body'] = $infobody;
        $data['fenshu'] = $info['points'];
        $data['questions'] = $info['questions'];
        $data['right_key'] = $right;
        $data['grade_id'] = $info['grade_id'];
        $data['course_id'] = !empty($info['course_id'])?$info['course_id']:1;
        $data['explainindetail'] = $info['explainindetail'];

        $data['type'] = 1;
        if ($data['grade_id']>1 && $data['grade_id']<= 6) {
            $addinfo['studyId'] = 1;//学科
        }

        if ($data['grade_id']>6 && $data['grade_id']<= 9) {
            $addinfo['studyId'] = 2;//学科
        }
        if ($data['grade_id']>9 && $data['grade_id']<= 12) {
            $addinfo['studyId'] = 3;//学科
        }

        //入库操作
        $addinfo['courseId'] = $data['course_id'];//学科
        $addinfo['type'] = $data['type'];//
        $addinfo['message'] = $data['questions'];//题目类型题目
        $addinfo['Problempresentation'] = $data['course_id'];//前台展示题型
        $addinfo['exerciseNums'] = count($data['body']);//选项数量
        $addinfo['trueAnswer'] = $data['right_key'];//正确答案
        $addinfo['arrayMessage'] = $data['body'];//答案
        $addinfo['Fractiondata'] = $data['fenshu'];
        $addinfo['answer_select'] = $data['right_key'];//答案选项
        $addinfo['messageJx'] = $data['explainindetail'];//解析

        $addprint = $this->addExercise( $addinfo );
        return $addprint;

    }



}


