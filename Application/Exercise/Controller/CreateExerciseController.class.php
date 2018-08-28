<?php
namespace Exercise\Controller;
use Think\Controller;
use Think\Verify;
use Common\Common\CSV;
use Common\Common\simple_html_dom;
class CreateExerciseController extends ExerciseGlobalController
{

    public $getCourseList;
    public $Create_Exercise;
    public $Exercises_question_processinfo;
    public $Exercises_Log;
    public $page_size=PAGESIZE;
    private $userInfo;
    public $City;
    public $paper;
    public $Exercises_paper_processinfo;
    public $Exercises_paper_bigquestion;
    public $Exercises_parper_concat;

    public function __construct() {
        parent::__construct();
        $this->getCourseList = D('Exercises_Course');
        $this->Create_Exercise = D('Create_Exercise');
        $this->Exercises_question_processinfo = D('Exercises_question_processinfo');
        $this->Exercises_Log = D('Exercises_log');
        $this->City = D('Dict_citydistrict');
        $this->paper = D('Exercises_create_paper');
        $this->Exercises_paper_processinfo = D('Exercises_paper_processinfo');
        $this->Exercises_paper_bigquestion = D('Exercises_paper_bigquestion');
        $this->Exercises_parper_concat = D('Exercises_parper_concat');
        $this->userInfo = $this->getUserRoleAuth();
    }

    //下载示例表格
    public function downloadFile() {
       /* set_time_limit(0);

        $limitnum = 2119435SUCCESSCODE;

        for ($i=1;$i<ERRORCODE0;$i++) {
            if ( $i == 19) {
                $url = file_get_contents("https://yunqivod.xiaozhizuo.tv/clip/0x0/da3aa340adc7d0e1480642a35c8705b0/alilive-E_2_2-1507856622-".$limitnum."-1920x1080-1165-2320.ts");
            } else {
                $url = file_get_contents("https://yunqivod.xiaozhizuo.tv/clip/0x0/da3aa340adc7d0e1480642a35c8705b0/alilive-E_2_2-1507856622-".$limitnum."-1920x1080-1165-3000.ts");
            }

            echo "https://yunqivod.xiaozhizuo.tv/clip/0x0/da3aa340adc7d0e1480642a35c8705b0/alilive-E_2_2-1507856622-".$limitnum."-1920x1080-1165-3000.ts".PHP_EOL;

            if ($url != false && !empty($url)) {
                $filename = "$i.ts";
                $fp = @ fopen('./ttimg/'.$filename, 'a');
                //写入图片到指定的文本
                fwrite($fp, $url);
                fclose($fp);
            }

            if ( $i == 19) {
                $limitnum+=208800;
            } else {
                $limitnum+=270000;
            }
        }*/


        $csv=new CSV();
        $filepath="./Public/csv/exercise.xls";
        $filename = '导入习题模板表格';
        $csv->downloadFileXls($filepath,$filename);
    }
    //习题预览
    public function exerciseDetails() {

        $id = I('id');
        if( empty($id) || is_numeric($id)==false ) {
            die('参数错误');
        }
        $exercise_info = $this->Create_Exercise->getExerciseInfo( $id );

        if (empty($exercise_info)) {
            die("非法错误信息");
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
            $exercise_info['track'] = $this->Exercises_Log->getQuestionLog($id);
            if (!empty($exercise_info['subject'])) {
                $course_name = $this->getCourseList->getCourseName($exercise_info['subject']);
            }
            $exercise_info['course_name'] = $course_name['name'];

            $getCourse = $this->getCourseList->getCourseList($exercise_info['subject']);
            if (!empty($getCourse)) {
                foreach ($getCourse as $k=>$v) {
                    if ($exercise_info['home_topic_type'] == $v['id']) {
                        $exercise_info['topic_type_name']['name'] = $v['name'];
                        break;
                    }
                }
            }

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
        }

        //创建防止重复提交token
        creatToken();
        $NextE = $this->Create_Exercise->getNextExerceseInfo($id); //获取下一题的信息
        $this->NextE = $NextE;

        $this->display();
    }

    //习题校审
    public function examinationQuestions() {
        $this->assign('parent', '试题录入管理');
        $this->assign('parentHref', U('CreateExercise/exerciseEntering'));
        $this->assign('own', ' >> 试题校审');
        $id = I('id');
        if( empty($id) || is_numeric($id)==false ) {
            die('参数错误');
        }
        $exercise_info = $this->Create_Exercise->getExerciseInfo( $id );

        if (empty($exercise_info)) {
            die("非法错误信息");
        }
        if($exercise_info['types'] == EXERCISE_TYPE_ABNORMAL)
        {
            redirect('/index.php?m=Exercise&c=Multimedia&a=homeworkDetails&id='.$id);
        }
        $this->verifyExerciseOperationAuth($exercise_info,EXERCISE_STATE_WAITVERIFY);
        $creatorInfo = $this->Exercises_question_processinfo->getCreatorInfo($id);
        if($creatorInfo['creator_id'] == $this->userInfo['id'])
        {
            $this->error('您没有校审该题的权限');
        }
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

            $getCourse = $this->getCourseList->getCourseList($exercise_info['subject']);
            if (!empty($getCourse)) {
                foreach ($getCourse as $k=>$v) {
                    if ($exercise_info['home_topic_type'] == $v['id']) {
                        $exercise_info['topic_type_name']['name'] = $v['name'];
                        break;
                    }
                }
            }

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
        }

        //创建防止重复提交token
        creatToken();
        $NextE = $this->Create_Exercise->getNextExerceseInfo($id); //获取下一题的信息
        $this->NextE = $NextE;

        $this->display();
    }


    //复合题模板
    public function moreQuestions() {
        $this->assign('parent', '试题录入管理');
        $this->assign('parentHref', U('CreateExercise/exerciseEntering'));
        $this->assign('own', ' >> 复合题录入');
        $type =  rtrim(I('type'),',');
        if ( !empty($type) ) {
            $type = explode(',',$type);
            $this->Counttype = count($type);
        }
        $this->type = $type;
        $resCourse = $this->getCourseList->getCourseList();
        if (!empty($resCourse[0]['id'])) {
            $this->exercisesType = $this->getCourseList->getCourseList($resCourse[0]['id']);
        }
        $this->getCourse = $this->getCourseList->getCourseList();

        $big_paper_id = I('big_paper_id');
        $paper_id = I('paper_id');

        if ($big_paper_id=='undefined') {
            $big_paper_id='';
        }

        if ($paper_id=='undefined') {
            $paper_id='';
        }

        if(!empty($big_paper_id) && !empty($paper_id) && $big_paper_id>0 && $paper_id>0) { //试卷录入
            $this->isPaper = 1;
            $this->big_paper_id=$big_paper_id;
            $this->paper_id = $paper_id;
        }
        //创建防止重复提交token
        creatToken();
        $this->display();
    }

    //修改复合题模板
    public function compositeeditChoiceExercise() {
        $type = I('type');
        $id = I('id');
        if(  empty($id) ) {
            die('参数错误');
        }

        $creatorInfo = $this->Exercises_question_processinfo->getCreatorInfo($id);
        if($creatorInfo['creator_id'] != $this->userInfo['id'])
        {
            $this->error('您没有修改该试题的权限');
        }
        $this->type = $type;
        $this->getCourse = $this->getCourseList->getCourseList();
        if (!empty($id)) {
            $exercise_info = $this->Create_Exercise->getExerciseInfo( $id );
            if($exercise_info['types'] == EXERCISE_TYPE_ABNORMAL)
            {
                redirect('/index.php?m=Exercise&c=Multimedia&a=homeworkDetails&id='.$id);
            }
            $exercise_info['topic_type_show'] = $exercise_info['topic_type']-1;
            $exercise_info['topic_type_name'] = C('exercisesType.'.$exercise_info['topic_type_show']);
            $exercise_info['answer'] = json_decode($exercise_info['answer'],true);
            $exercise_info['answer_count'] = count($exercise_info['answer']);

            if (!empty($exercise_info['answer'])){
                if (is_array($exercise_info['answer'])) {
                    $newarray=[];
                    foreach ($exercise_info['answer'] as $k=>$v) {
                        $newarray[] = html_entity_decode($v);
                    }
                    $exercise_info['answer'] = implode('jingbanyunxx',$newarray);
                }

            }


            if (!empty($exercise_info['answer_select'])) {
                if ($exercise_info['topic_type']==4) {
                    $exercise_info['answer_select'] = html_entity_decode(json_decode($exercise_info['answer_select'],true));
                } else {
                    $exercise_info['answer_select'] = json_decode($exercise_info['answer_select'],true);
                    $answer_selectarray=[];
                    foreach ($exercise_info['answer_select'] as $k=>$v) {
                        $answer_selectarray[] = html_entity_decode($v);
                    }
                    $exercise_info['answer_select'] = implode('jingbanyunxx',$answer_selectarray);
                }

            }

            $exercise_info['track'] = $this->Exercises_Log->getQuestionLog($id);
            $exercise_info['numcopy'] = $exercise_info['num']-1;

            $this->exercise_info = $exercise_info;
        }

        //创建防止重复提交token
        creatToken();

        switch ($type) {
            case CASEONE:
                $this->display();
                break;
            case CASETWO:
                $this->addExercise = 'addExercise';
                $this->display('compositeeditCompletion');
                break;
            case CASETHREE:
                $this->addExercise = 'xuanzhetiankongExercise';
                $this->display('compositeeditCompletionChoice');
                break;
            case CASEFOUR:
                $this->addExercise = 'lianxianExercise';
                $this->display('compositeeditMatchingExercise');
                break;
            case CASEFIVE:
                $this->addExercise = 'zuotuaddExercise';
                $this->display('compositeeditDrawingExercise');
                break;
            case CASESIX:
                $this->addExercise = 'jiedaaddExercise';
                $this->display('compositeeditAnswerQuestions');
                break;
            default :
                $this->type = CASEONE;
                $this->display();
                break;
        }

    }

    //复合题模板
    public function compositecreateChoiceExercise() {
        $type = I('type');
        $this->type = $type;
        $this->getCourse = $this->getCourseList->getCourseList();

        switch ($type) {
            case CASEONE:
                $this->display();
                break;
            case CASETWO:
                $this->addExercise = 'addExercise';
                $this->display('compositecreateCompletion');
                break;
            case CASETHREE:
                $this->addExercise = 'xuanzhetiankongExercise';
                $this->display('compositecreateCompletionChoice');
                break;
            case CASEFOUR:
                $this->addExercise = 'lianxianExercise';
                $this->display('compositecreateMatchingExercise');
                break;
            case CASEFIVE:
                $this->addExercise = 'zuotuaddExercise';
                $this->display('compositecreateDrawingExercise');
                break;
            case CASESIX:
                $this->addExercise = 'jiedaaddExercise';
                $this->display('compositecreateAnswerQuestions');
                break;
            default :
                $this->type = CASEONE;
                $this->display();
                break;
        }

    }

    //修改复合题数据
    public function editCompositeExercise() {
        $exerciseList = I('exerciseList');

        $pid = $exerciseList['id'];

        if(empty($pid) || is_numeric($pid)==false ) {
            $res['code'] = ERRORCODE;
            $res['msg'] = '数据错误';
            $this->ajaxReturn($res);
        }

        $_send_paperid = $exerciseList['_send_paperid'];

        $exercise_info = $this->Create_Exercise->getExerciseInfo( $pid );

        if ($_send_paperid>0 && is_numeric($_send_paperid)){ //从试题过来的 试卷id
            $paperinfo = $this->paper->getPaperInfo($_send_paperid);

            if ($paperinfo['status'] != EXERCISE_STATE_DRAFT ) {
                if ($paperinfo['status'] != EXERCISE_STATE_REPROCESS) {
                    $res['code'] = ERRORCODE;
                    $res['msg'] = '试卷操作中,只有返工状态或者草稿状态的试卷才可以修改';
                    $this->ajaxReturn($res);
                } else {
                    if($exercise_info['status'] != EXERCISE_STATE_PAPEREXERCISEDECLINE) { //正常状态
                        $res['code'] = ERRORCODE;
                        $res['msg'] = '习题操作中,只有返工状态的习题才可以修改';
                        $this->ajaxReturn($res);
                    }
                }
            }

        } else {
            if($exercise_info['status'] != EXERCISE_STATE_REPROCESS) { //正常状态
                $res['code'] = ERRORCODE;
                $res['msg'] = '习题操作中,只有返工状态的习题才可以修改';
                $this->ajaxReturn($res);
            }
        }

        $firstdata = array(
            'search_name' => $exerciseList['firstmessage'],
            'subject_name' => $exerciseList['firstmessage'],
            'study_section' => $exerciseList['studyId'],
            'subject' => $exerciseList['courseId'],
            'home_topic_type' => $exerciseList['exercisesId'],
            'update_at' => time(),
            'exercise_type' => 2,
            'status' => EXERCISE_STATE_WAITVERIFY,
            'class_type_arr' => json_encode($exerciseList['answerArr']),
            'json_html' => json_encode($exerciseList['html']),
        );

        $twodata = $exerciseList['list'];//所有的二级题干
        $twodata = array_values($twodata);

        if (!empty($twodata)) {
            $count = 0;
            foreach ($twodata as $kvs => $cvs) {
                if (is_array($cvs['fenshu'])) {
                    foreach ($cvs['fenshu'] as $fenshu) {
                        $count += intval($fenshu);
                    }
                } else {
                    $count += intval($cvs['fenshu']);
                }

            }
            $firstdata['score'] = $count;
            $firstdata['count_score'] = $count;
        }


        $lock = true;
        //设置全局锁
        //入库一级题干
        $this->Create_Exercise->startTrans();

        //先全部删除
        $isdel = $this->Create_Exercise->delChildExercise($pid);

        if ($isdel==false) {
            $this->Create_Exercise->rollback();
            $res['code'] = ERRORCODE;
            $res['msg'] = '修改失败';
            $lock = false;
        }


        $editfirstid = $this->Create_Exercise->save_updateExerciseInfo($firstdata,$pid); //修改习题的信息

        $is_save = $this->Exercises_question_processinfo->setQuestionState($pid,EXERCISE_STATE_WAITVERIFY,session('admin.id')); //修改习题的状态
        $saveState = $this->Exercises_question_processinfo->setStateUser($pid,session('admin.id'),session('admin.user_name'),1,'reprocess');
        $firstid = $pid;
        if ( $editfirstid && $is_save!=false && $saveState ) {
            //$truep = $this->Exercises_question_processinfo->updateCreatorInfo($firstid,session('admin.id'),session('admin.user_name'));
            $log_id = $this->Exercises_Log->insertLog($firstid,$paperId=0,'修改',get_client_ip(),session('admin.id'),session('admin.user_name'),'',$poperatorId=0,$poperatorName='');
            if ( $log_id ) {
                $res['code'] = SUCCESSCODE;
            } else {
                $this->Create_Exercise->rollback();
                $res['code'] = ERRORCODE;
                $res['msg'] = '修改失败';
                $lock = false;
            }
        } else {
            $this->Create_Exercise->rollback();
            $res['msg'] = '修改失败';
            $res['code'] = ERRORCODE;
            $lock = false;
        }
        //入库二级题干
        $firstdata['parent_id'] = $firstid;
        $errorinfo = '';
        $savesearch_name = $exerciseList['firstmessage'];
        foreach ($twodata as $k=>$v) {
            switch ($v['type']) {
                case CASEONE:
                    $data = $this->pieceSelectInfo($v,$errorinfo);//拼装选择题
                    break;
                case CASETWO:
                    $data = $this->pieceBlanksInfo($v,$errorinfo);//拼装文字填空
                    break;
                case CASETHREE:
                    $data = $this->pieceSelectBlanksInfo($v,$errorinfo);//拼装选择填空
                    break;
                case CASEFOUR:
                    $data = $this->pieceLineInfo($v,$errorinfo);//拼装连线
                    break;
                case CASEFIVE:
                    $data = $this->pieceMappingInfo($v,$errorinfo);//拼装作图
                    break;
                case CASESIX:
                    $data = $this->pieceAnswerInfo($v,$errorinfo);//拼装解答
                    break;
            }

            if($errorinfo!='') {
                $this->Create_Exercise->rollback();
                $error['code'] = ERRORCODE;
                $error['msg'] = $errorinfo;
                $this->ajaxReturn($error);
            }

            $data = array_merge($firstdata,$data);
            $data['update_at'] = time();
            $is_add =  $this->Create_Exercise->createExerciseInfo($data);
            if ($is_add) {
                $truep = $this->Exercises_question_processinfo->addCreatorInfo($is_add,session('admin.id'),session('admin.user_name'));
                //$log_id = $this->Exercises_Log->insertLog($is_add,$paperId=0,'创建习题',get_client_ip(),session('admin.id'),session('admin.user_name'),'个人录入习题',$poperatorId=0,$poperatorName='');
                if ( $truep ) {
                    $res['code'] = SUCCESSCODE;
                } else {
                    $this->Create_Exercise->rollback();
                    $res['code'] = ERRORCODE;
                    $res['msg'] = '修改失败';
                    $lock = false;
                }
            } else {
                $res['code'] = ERRORCODE;
                $lock = false;
            }
            $savesearch_name.=$v['message'];

        }

        if($lock==true) {
            $saveid = $this->Create_Exercise->updateExerciseInfo($firstid,$savesearch_name);
            if ($saveid !== false) {
                $res['code'] = SUCCESSCODE;
                $this->Create_Exercise->commit();
            } else{
                $res['code'] = ERRORCODE;
                $res['msg'] = '修改失败';
                $this->Create_Exercise->rollback();
            }

        } else {
            $res['code'] = ERRORCODE;
            $res['msg'] = '修改失败';
            $this->Create_Exercise->rollback();
        }
        $this->ajaxReturn($res);
    }

    //添加复合题
    public function addCompositeExercise() {
        $exerciseList = I('exerciseList');

        if (empty($exerciseList['firstmessage'])) {
            $res['code'] = ERRORCODE;
            $res['msg'] = '请先生成模板填写数据';
            $this->ajaxReturn($res);
        }

        $firstdata = array(
            'search_name' => $exerciseList['firstmessage'],
            'subject_name' => $exerciseList['firstmessage'],
            'study_section' => $exerciseList['studyId'],
            'subject' => $exerciseList['courseId'],
            'home_topic_type' => $exerciseList['exercisesId'],
            'create_at' => time(),
            'exercise_type' => 2,
            'class_type_arr' => json_encode($exerciseList['answerArr']),
            'json_html' => json_encode($exerciseList['html']),
        );

        /*$token = $exerciseList['token'];//表单重复提交验证
        if (!checkToken($token)) {
            $res['code'] = ERRORCODE;
            $res['msg'] = '不要重复提交表单！';
            $this->ajaxReturn($res);
        }*/

        $big_paper_id = $exerciseList['big_paper_id']; //试卷大题id
        $paper_id = $exerciseList['paper_id'];//试卷id

        if ($big_paper_id=='undefined') {
            $big_paper_id='';
        }

        if ($paper_id=='undefined') {
            $paper_id='';
        }

        if (!empty($big_paper_id) && !empty($paper_id)) {
            $firstdata['status'] = 19;
        }


        $twodata = $exerciseList['list'];//所有的二级题干
        $twodata = array_values($twodata);

        if (!empty($twodata)) {
            $count = 0;
            foreach ($twodata as $kvs => $cvs) {
                if (is_array($cvs['fenshu'])) {
                    foreach ($cvs['fenshu'] as $fenshu) {
                        $count += intval($fenshu);
                    }
                } else {
                    $count += intval($cvs['fenshu']);
                }

            }
            $firstdata['score'] = $count;
            $firstdata['count_score'] = $count;
        }

        $lock = true;
        //设置全局锁
        //入库一级题干
        $this->Create_Exercise->startTrans();
        $firstid = $this->Create_Exercise->createExerciseInfo($firstdata);
        if ( $firstid ) {
            $truep = $this->Exercises_question_processinfo->addCreatorInfo($firstid,session('admin.id'),session('admin.user_name'));
            $log_id = $this->Exercises_Log->insertLog($firstid,$paperId=0,'创建',get_client_ip(),session('admin.id'),session('admin.user_name'),'',$poperatorId=0,$poperatorName='');
            if ( $truep && $log_id ) {

                if (!empty($big_paper_id) && !empty($paper_id)) { //录入试卷的习题

                    $paperdata['exercise_id'] = $firstid;
                    $paperdata['paper_id'] = $paper_id;
                    $paperdata['big_question_id'] = $big_paper_id;
                    $paperdata['create_at'] = time();
                    $paperdata['big_order'] = $firstid;
                    $paperdata['exercises_score'] = $firstdata['count_score'];
                    $p_c_id = $this->Exercises_parper_concat->addParperConcat($paperdata); //关联试卷试题的关系

                    if (  $p_c_id ) {
                        $this->Exercises_paper_bigquestion->updateParperBigSetNum($paper_id,$big_paper_id);//把大题中的小题数量加1
                        $res['code'] = SUCCESSCODE;
                    } else {
                        $this->Create_Exercise->rollback();
                        $res['code'] = ERRORCODE;
                    }

                } else { //录入单独的习题
                    $res['code'] = SUCCESSCODE;
                }

            } else {
                $this->Create_Exercise->rollback();
                $res['code'] = ERRORCODE;
                $res['msg'] = '添加失败';
                $lock = false;
            }
        } else {
            $this->Create_Exercise->rollback();
            $res['msg'] = '添加失败';
            $res['code'] = ERRORCODE;
            $lock = false;
        }
        //入库二级题干
        $firstdata['parent_id'] = $firstid;
        $errorinfo = '';
        $savesearch_name = $exerciseList['firstmessage'];
        foreach ($twodata as $k=>$v) {
            switch ($v['type']) {
                case CASEONE:
                    $data = $this->pieceSelectInfo($v,$errorinfo);//拼装选择题
                    break;
                case CASETWO:
                    $data = $this->pieceBlanksInfo($v,$errorinfo);//拼装文字填空
                    break;
                case CASETHREE:
                    $data = $this->pieceSelectBlanksInfo($v,$errorinfo);//拼装选择填空
                    break;
                case CASEFOUR:
                    $data = $this->pieceLineInfo($v,$errorinfo);//拼装连线
                    break;
                case CASEFIVE:
                    $data = $this->pieceMappingInfo($v,$errorinfo);//拼装作图
                    break;
                case CASESIX:
                    $data = $this->pieceAnswerInfo($v,$errorinfo);//拼装解答
                    break;
            }

            if($errorinfo!='') {
                $this->Create_Exercise->rollback();
                $error['code'] = ERRORCODE;
                $error['msg'] = $errorinfo;
                $this->ajaxReturn($error);
            }
            $data['class_type'] = $v['answerWidth'];
            $data = array_merge($firstdata,$data);
            $is_add =  $this->Create_Exercise->createExerciseInfo($data);
            if ($is_add) {
                $truep = $this->Exercises_question_processinfo->addCreatorInfo($is_add,session('admin.id'),session('admin.user_name'));
                //$log_id = $this->Exercises_Log->insertLog($is_add,$paperId=0,'创建习题',get_client_ip(),session('admin.id'),session('admin.user_name'),'个人录入习题',$poperatorId=0,$poperatorName='');
                if ( $truep ) {
                    $res['code'] = SUCCESSCODE;
                } else {
                    $this->Create_Exercise->rollback();
                    $res['code'] = ERRORCODE;
                    $res['msg'] = '添加失败';
                    $lock = false;
                }
            } else {
                $res['code'] = ERRORCODE;
                $lock = false;
            }
            $savesearch_name.=$v['message'];

        }

        if($lock==true) {
            $saveid = $this->Create_Exercise->updateExerciseInfo($firstid,$savesearch_name);
            if ($saveid !== false) {
                $res['code'] = SUCCESSCODE;
                $this->Create_Exercise->commit();
            } else{
                $res['code'] = ERRORCODE;
                $res['msg'] = '提交失败';
                $this->Create_Exercise->rollback();
            }

        } else {
            $res['code'] = ERRORCODE;
            $res['msg'] = '提交失败';
            $this->Create_Exercise->rollback();
        }
        $this->ajaxReturn($res);
    }

    public function pieceSelectInfo($data,&$errorinfo) {
        $info = array(
            'answer' => json_encode($data['arrayMessage']),
            'num' => $data['exerciseNums'],
            'subject_name' => $data['message'],
            'score' => $data['fenshu'],
            'count_score' => $data['fenshu'],
            'analysis' => $data['messageJx'],
            'topic_type' => $data['type'],
            'small_exercise_number'=> $data['topicNum'],
            'right_key' => $data['right_key'],
        );

        if (empty($info['answer'])) {
            $errorinfo = '选择题答案未填写';
        }
        /*if (empty($info['subject_name'])) {
            $errorinfo = '选择题二级题目未填写';
        }*/
        if (empty($info['score'])) {
            $errorinfo = '选择题二级题目分数未填写';
        }
        if (empty($info['analysis'])) {
            $errorinfo = '选择题二级题目解析未填写';
        }
        return $info;
    }

    public function pieceBlanksInfo($data,&$errorinfo) {
        $info = array(
            'answer' => json_encode($data['arrayMessage']),
            'num' => $data['exerciseNums'],
            'subject_name' => $data['message'],
            'score' => implode(',',$data['fenshu']),
            'count_score' => array_sum($data['fenshu']),
            'analysis' => $data['messageJx'],
            'topic_type' => $data['type'],
            'small_exercise_number'=> $data['topicNum'],
        );

        if (empty($info['answer'])) {
            $errorinfo = '文字填空题答案未填写';
        }
        /*if (empty($info['subject_name'])) {
            $errorinfo = '文字填空题二级题目未填写';
        }*/
        if (empty($info['score'])) {
            $errorinfo = '文字填空题分数未填写';
        }
        if (empty($info['analysis'])) {
            $errorinfo = '文字填空题解析未填写';
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

        /*if (empty($info['subject_name'])) {
            $errorinfo = '选择填空题二级题目未填写';
        }*/
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
        /*if (empty($info['subject_name'])) {
            $errorinfo = '连线题二级题目未填写';
        }*/
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
       /* if (empty($info['subject_name'])) {
            $errorinfo = '作图题二级题目未填写';
        }*/
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
            'answer' => json_encode($data['arrayMessage']),
            'subject_name' => $data['message'],
            'score' => $data['fenshu'],
            'count_score' => $data['fenshu'],
            'analysis' => $data['messageJx'],
            'topic_type' => $data['type'],
            'small_exercise_number'=> $data['topicNum'],
        );

        if (empty($info['answer'])) {
            $errorinfo = '解答题答案未填写';
        }
       /* if (empty($info['subject_name'])) {
            $errorinfo = '解答题二级题目未填写';
        }*/
        if (empty($info['score'])) {
            $errorinfo = '解答题分数未填写';
        }
        if (empty($info['analysis'])) {
            $errorinfo = '解答题解析未填写';
        }

        return $info;
    }

    //错误试题导出
    public function ImportErrorExercise(){
        $errorjson = $_POST['errorjson'];
        $errorjson = json_decode($errorjson,true);

        $filename=date('Ymd').rand(0,1000).'.csv';
        $header = array(array('name'=>'题目'),array('name'=>'题目类型'),array('name'=>'前台显示类型'),array('name'=>'分值'),array('name'=>'总分值'),array('name'=>'解析'),array('name'=>'学科'),array('name'=>'学段'),array('name'=>'答案'),array('name'=>'正确答案'),array('name'=>'选项数量'),array('name'=>'错误信息'));

        $this->exportCSV($filename,$header,$errorjson);
    }


    //试题导入
    public function bulkImport() {
        $this->assign('parent', '试题录入管理');
        $this->assign('parentHref', U('CreateExercise/exerciseEntering'));
        $this->assign('own', ' >> 试题导入');
        $this->display();
    }

    //导入习题
    public function ImportExerciseInfo() {
        set_time_limit(0);
        import("Org.Util.Ereader");
        import("Org.Util.Sreader");

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('xlsx', 'ods', 'xls', 'csv');// 设置附件上传类型
        $upload->rootPath  =      './Uploads/'; // 设置附件上传根目录
        // 上传单个文件
        $info   =   $upload->uploadOne($_FILES['file']);
        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{// 上传成功 获取上传文件信息
            $file_path = $upload->rootPath.$info['savepath'].$info['savename'];
        }

        if (!empty($file_path)) {
            $Reader = new \SpreadsheetReader($file_path);
            $array=[];
            $errordata=[];
            foreach ($Reader as $k=>$Row) { //进行校验数据的正确性
                if ($k==1) {
                    continue;
                } else {
                    $truedata = $this->importCheck($Row); //校验数据的正确性

                    foreach ($Row as $rk=>$rv) {

                        if ( $truedata != false ) {//没有错误
                            $array[$k][$rk] = $rv;
                        } else { //有错误
                            $errordata[$k] = $Row;
                        }
                    }
                }
            }
            unlink($file_path);
            if (!empty($errordata)) { //下载错误数据到前端
                $errordata = array_values($errordata);
                $res['msg'] = json_encode($errordata);
                $res['code'] = ERRORCODE;
                $this->ajaxReturn($res);
            }
            //下面得到的数据就是已通过校验的数据
            if(!empty($array)) {
                $array = array_values($array);
                foreach ( $array as $addk=>$addv) {
                    $adddata = [];
                    $adddata['study_section'] = $addv[7]; //学段
                    $adddata['subject'] = $addv[6];//学科
                    $adddata['topic_type'] = $addv[1];//题目类型
                    $adddata['subject_name'] = htmlspecialchars($addv[0]); //题目
                    $adddata['search_name'] = htmlspecialchars($addv[0]); //题目
                    $adddata['home_topic_type'] = $addv[2];//前台展示类型
                    $adddata['num'] = $addv[10];//选项数量
                    $adddata['right_key'] = $addv[9];
                    $adddata['analysis'] = $addv[5];

                    if(strpos($addv[8],'jingbanyuncopyright')) {
                        $answer = explode('jingbanyuncopyright',$addv[8]);//以京版云进行分割
                        $adddata['num'] = count($answer);
                    } else {
                        $answer = $addv[8];//以京版云进行分割
                        $adddata['num'] = 1;
                    }

                    $adddata['answer'] = json_encode($answer);
                    $adddata['count_score'] = $addv[4];
                    $adddata['score'] = $addv[3];

                    if(strpos($addv[11],'jingbanyuncopyright')) {
                        $answer_select = explode('jingbanyuncopyright',$addv[11]);//以京版云进行分割
                    } else {
                        $answer_select = $addv[11];//以京版云进行分割
                    }

                    $adddata['answer_select'] = json_encode($answer_select);
                    $adddata['create_at'] = time();
                    $adddata['update_at'] = time();
                    $adddata['exercise_source'] = 2;

                    $this->Create_Exercise->startTrans();
                    $id = $this->Create_Exercise->createExerciseInfo($adddata);
                    if ( $id ) {
                        $truep = $this->Exercises_question_processinfo->addCreatorInfo($id,session('admin.id'),session('admin.user_name'));

                        $log_id = $this->Exercises_Log->insertLog($id,$paperId=0,'导入习题',get_client_ip(),session('admin.id'),session('admin.user_name'),'个人导入习题',$poperatorId=0,$poperatorName='');

                        if ($truep && $log_id) {
                            $this->Create_Exercise->commit();
                            $res['code'] = SUCCESSCODE;
                        } else {
                            $this->Create_Exercise->rollback();
                            $res['code'] = ERRORCODE;
                            $res['msg'] = '添加失败';
                        }
                    } else {
                        $this->Create_Exercise->rollback();
                        $res['msg'] = '添加失败';
                        $res['code'] = ERRORCODE;
                    }
                }
                $this->ajaxReturn($res);
            }

        } else {
            $res['code'] = ERRORCODE;
            $res['msg'] = '上传失败';
            $this->ajaxReturn($res);
        }

    }

    //校验导入的数据
    public function importCheck(&$Row) {
        if (empty($Row[1])) {
            $msg[]='题目类型不可以为空';
            $Row = array_merge($Row,$msg);
            return false;
        }

        switch ($Row[1]) {
            case CASEONE:
                if (empty($Row[0])) {
                    $msg[]='题目不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }
                if (empty($Row[1])) {
                    $msg[]='题目类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }
                if (empty($Row[2])) {
                    $msg[]='前台显示类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[3])) {
                    $msg[]='分值类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[4])) {
                    $msg[]='总分值类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[5])) {
                    $msg[]='解析不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[6])) {
                    $msg[]='学科不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[7])) {
                    $msg[]='学段不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[8])) {
                    $msg[]='答案不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[9])) {
                    $msg[]='正确答案不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[10])) {
                    $msg[]='选项数量不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                break;
            case CASETWO:

                if (empty($Row[0])) {
                    $msg[]='题目不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }
                if (empty($Row[1])) {
                    $msg[]='题目类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }
                if (empty($Row[2])) {
                    $msg[]='前台显示类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[3])) {
                    $msg[]='分值类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[4])) {
                    $msg[]='总分值类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[5])) {
                    $msg[]='解析不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[6])) {
                    $msg[]='学科不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[7])) {
                    $msg[]='学段不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[8])) {
                    $msg[]='答案不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                break;
            case CASETHREE:
                if (empty($Row[0])) {
                    $msg[]='题目不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }
                if (empty($Row[1])) {
                    $msg[]='题目类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }
                if (empty($Row[2])) {
                    $msg[]='前台显示类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[3])) {
                    $msg[]='分值类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[4])) {
                    $msg[]='总分值类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[5])) {
                    $msg[]='解析不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[6])) {
                    $msg[]='学科不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[7])) {
                    $msg[]='学段不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[8])) {
                    $msg[]='答案不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[10])) {
                    $msg[]='选项数量不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[11])) {
                    $msg[]='答案选项不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                break;
            case CASEFOUR:

                if (empty($Row[0])) {
                    $msg[]='题目不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }
                if (empty($Row[1])) {
                    $msg[]='题目类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }
                if (empty($Row[2])) {
                    $msg[]='前台显示类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[3])) {
                    $msg[]='分值类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[4])) {
                    $msg[]='总分值类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[5])) {
                    $msg[]='解析不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[6])) {
                    $msg[]='学科不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[7])) {
                    $msg[]='学段不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[8])) {
                    $msg[]='答案不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[10])) {
                    $msg[]='选项数量不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[11])) {
                    $msg[]='答案选项不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                break;
            case CASEFIVE:

                if (empty($Row[0])) {
                    $msg[]='题目不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }
                if (empty($Row[1])) {
                    $msg[]='题目类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }
                if (empty($Row[2])) {
                    $msg[]='前台显示类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[3])) {
                    $msg[]='分值类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[4])) {
                    $msg[]='总分值类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[5])) {
                    $msg[]='解析不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[6])) {
                    $msg[]='学科不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[7])) {
                    $msg[]='学段不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[8])) {
                    $msg[]='答案不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[9])) {
                    $msg[]='正确答案不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                break;
            case CASESIX:


                if (empty($Row[0])) {
                    $msg[]='题目不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }
                if (empty($Row[1])) {
                    $msg[]='题目类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }
                if (empty($Row[2])) {
                    $msg[]='前台显示类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[3])) {
                    $msg[]='分值类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[4])) {
                    $msg[]='总分值类型不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[5])) {
                    $msg[]='解析不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[6])) {
                    $msg[]='学科不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[7])) {
                    $msg[]='学段不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[8])) {
                    $msg[]='答案不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                if (empty($Row[9])) {
                    $msg[]='正确答案不可以为空';
                    $Row = array_merge($Row,$msg);
                    return false;
                }

                break;
            default :
                $this->type = 1;
                $this->display();
                break;
        }

        return true;
    }

    //获取学科
    public function getCourse(){
        $list = $this->getCourseList->getCourseList();
        $this->ajaxReturn($list);
    }
    //根据学科获取展示类型
    public function getCourseChild() {
        $id = I('id');
        if(0 == $id){
            $this->ajaxReturn(array());
        }
        if(empty($id)  || is_numeric($id)==false ) {
            die('参数错误');
        }
        $list = $this->getCourseList->getCourseList($id);
        $this->ajaxReturn($list);
    }
    //添加习题页面模板
    public function createChoiceExercise(){
        $this->assign('parent', '试题录入管理');
        $this->assign('parentHref', U('CreateExercise/exerciseEntering'));
        $this->assign('own', ' >> 独立题录入');
         $type = I('type');
         $this->type = $type;
         $this->getCourse = $this->getCourseList->getCourseList();
         $big_paper_id = I('big_paper_id');
         $paper_id = I('paper_id');

         if ($big_paper_id=='undefined') {
            $big_paper_id='';
         }

         if ($paper_id=='undefined') {
            $paper_id='';
         }

         if(!empty($big_paper_id) && !empty($paper_id)) { //试卷录入
             $this->isPaper = 1;
             $this->big_paper_id=$big_paper_id;
             $this->paper_id = $paper_id;
         }

         //创建防止重复提交token
         creatToken();

         switch ($type) {
             case CASEONE:
                 $this->display();
                 break;
             case CASETWO:
                 $this->addExercise = 'addExercise';
                 $this->display('createCompletion');
                 break;
             case CASETHREE:
                 $this->addExercise = 'xuanzhetiankongExercise';
                 $this->display('createCompletionChoice');
                 break;
             case CASEFOUR:
                 $this->addExercise = 'lianxianExercise';
                 $this->display('createMatchingExercise');
                 break;
             case CASEFIVE:
                 $this->addExercise = 'zuotuaddExercise';
                 $this->display('createDrawingExercise');
                 break;
             case CASESIX:
                 $this->addExercise = 'jiedaaddExercise';
                 $this->display('createAnswerQuestions');
                 break;
             default :
                 $this->type = 1;
                 $this->display();
                 break;
         }

    }

    //修改习题页面
    public function editChoiceExercise(){
        $this->assign('parent', '试题录入管理');
        $this->assign('parentHref', 'javascript:history.go(-1)');
        $this->assign('own', ' >> 试题详情');
        $id = I('id');

        if(empty($id)  || is_numeric($id)==false ) {
            die('参数错误');
        }

        $creatorInfo = $this->Exercises_question_processinfo->getCreatorInfo($id);
        if($creatorInfo['creator_id'] != $this->userInfo['id'])
        {
            $this->error('您没有修改该试题的权限');
        }
        $paper_id = I('paper_id'); //大题id
        if ($paper_id>0 && is_numeric($paper_id)){
            $this->isPaper = 1;
            $this->paper_id = $paper_id;
        }

        $NextE = $this->Create_Exercise->getReworkNextExerceseInfo($id); //获取下一题的信息
        $this->NextE = $NextE;

        if (IS_POST) {
            $this->check($_POST);
           /* $token = I('token');//表单重复提交验证
            if (!checkToken($token)) {
                $res['code'] = ERRORCODE;
                $res['msg'] = '不要重复提交表单！';
                $this->ajaxReturn($res);
            }*/

            $id = I('id');//修改的id
            $_send_paperid = I('_send_paperid');
            $exercise_info = $this->Create_Exercise->getExerciseInfo( $id );

            if ($_send_paperid>0 && is_numeric($_send_paperid)){ //从试题过来的 试卷id
                $paperinfo = $this->paper->getPaperInfo($_send_paperid);

                if ($paperinfo['status'] != EXERCISE_STATE_DRAFT ) {
                    if ($paperinfo['status'] != EXERCISE_STATE_REPROCESS) {
                        $res['code'] = ERRORCODE;
                        $res['msg'] = '试卷操作中,只有返工状态或者草稿状态的试卷才可以修改';
                        $this->ajaxReturn($res);
                    } else {
                        if($exercise_info['status'] != EXERCISE_STATE_PAPEREXERCISEDECLINE) { //正常状态
                            $res['code'] = ERRORCODE;
                            $res['msg'] = '习题操作中,只有返工状态的习题才可以修改';
                            $this->ajaxReturn($res);
                        }
                    }
                }

            } else {
                if($exercise_info['status'] != EXERCISE_STATE_REPROCESS) { //正常状态
                    $res['code'] = ERRORCODE;
                    $res['msg'] = '习题操作中,只有返工状态的习题才可以修改';
                    $this->ajaxReturn($res);
                }
            }

            $study_section = I('studyId');//学段
            $subject = I('courseId');//学科
            $topic_type = I('type');//题目类型
            $subject_name = I('message');//题目
            $home_topic_type = I('Problempresentation');//前台展示题型
            $exerciseNums = I('exerciseNums');//选项数量
            $right_key = I('trueAnswer');//正确答案
            $analysis = I('messageJx');//解析
            $answer = json_encode(I('arrayMessage'));//答案
            $count_score = I('Fractiondata');
            $score = I('Fractiondata');
            $count_score = rtrim($count_score,',');
            $answerWidth = I('answerWidth');
            $json_html = I('html');

            if (strpos($count_score,',')!=false) {
                $count_score = explode(',',$count_score);
                $count_score = array_sum($count_score);
            }

            $score = rtrim($score,',');

            $answer_select = json_encode(I('answer_select'));//答案选项
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
            $data['update_at'] = $update_at;
            $data['num'] = $exerciseNums;
            $data['answer_select'] = $answer_select;
            $data['status'] = 20;
            $data['class_type'] = $answerWidth;
            $data['json_html'] = json_encode($json_html);

            $this->Create_Exercise->startTrans();
            $eid = $this->Create_Exercise->editExerciseInfo($data,$id);
            if (  false !== $eid ) {
                //$truep = $this->Exercises_question_processinfo->updateCreatorInfo($id,session('admin.id'),session('admin.user_name'));
                //输入参数：习题ID 试卷ID 操作内容 IP地址 操作人ID 操作人姓名 备注 被操作人ID 被操作人姓名
                $log_id = $this->Exercises_Log->insertLog($id,$paperId=0,'修改',get_client_ip(),session('admin.id'),session('admin.user_name'),'',$poperatorId=0,$poperatorName='');

                $is_save = $this->Exercises_question_processinfo->setQuestionState($id,EXERCISE_STATE_WAITVERIFY,session('admin.id'));
                $saveState = $this->Exercises_question_processinfo->setStateUser($id,session('admin.id'),session('admin.user_name'),1,'reprocess');
                if (  $log_id!=false && $is_save!=false && $saveState !== false) {
                    $this->Create_Exercise->commit();
                    $res['code'] = SUCCESSCODE;
                    $this->ajaxReturn($res);
                } else {
                    $this->Create_Exercise->rollback();
                    $res['code'] = ERRORCODE;
                    $res['msg'] = '修改失败';
                    $this->ajaxReturn($res);
                }
            } else {
                $res['code'] = ERRORCODE;
                $this->ajaxReturn($res);
            }

        } else {

            //创建防止重复提交token
            creatToken();

            $exercise_info = $this->Create_Exercise->getExerciseInfo( $id );
            if($exercise_info['types'] == EXERCISE_TYPE_ABNORMAL)
            {
                redirect('/index.php?m=Exercise&c=Multimedia&a=homeworkDetails&id='.$id);
            }
            if ($exercise_info['exercise_type']==1) { //独立题
                $exercise_info['topic_type_show'] = $exercise_info['topic_type']-1;
                $exercise_info['topic_type_name'] = C('exercisesType.'.$exercise_info['topic_type_show']);
                $exercise_info['answer'] = json_decode($exercise_info['answer'],true);
                $exercise_info['answer_count'] = count($exercise_info['answer']);

                if (!empty($exercise_info['answer'])){
                    $newarray=[];
                    foreach ($exercise_info['answer'] as $k=>$v) {
                        $newarray[] = html_entity_decode($v);
                    }
                    $exercise_info['answer'] = implode('jingbanyunxx',$newarray);
                }


                if (!empty($exercise_info['answer_select'])) {
                    if ($exercise_info['topic_type']==4) {
                        $exercise_info['answer_select'] = html_entity_decode(json_decode($exercise_info['answer_select'],true));
                    } else {
                        $exercise_info['answer_select'] = json_decode($exercise_info['answer_select'],true);
                        $answer_selectarray=[];
                        foreach ($exercise_info['answer_select'] as $k=>$v) {
                            $answer_selectarray[] = html_entity_decode($v);
                        }
                        $stranswer_selectarray = implode('jingbanyunxx',$answer_selectarray);
                        $stranswer_selectarray = str_replace('"','\"',$stranswer_selectarray);
                        $stranswer_selectarray = str_replace("'","\'",$stranswer_selectarray);
                        $exercise_info['answer_select'] = $stranswer_selectarray;
                    }

                }

                $exercise_info['track'] = $this->Exercises_Log->getQuestionLog($id);
                $exercise_info['numcopy'] = $exercise_info['num']-1;

                $this->exercise_info = $exercise_info;
                $this->getCourse = $this->getCourseList->getCourseList();
                if (!empty($exercise_info['subject'])) {
                    $this->getChildCourse = $this->getCourseList->getCourseList($exercise_info['subject']);
                }

                $this->type = $exercise_info['topic_type'];

                switch ($exercise_info['topic_type']) {
                    case CASEONE:
                        $this->display();
                        break;
                    case CASETWO:
                        $this->addExercise = 'editExercise';
                        $this->display('editCompletion');
                        break;
                    case CASETHREE:
                        $this->addExercise = 'xuanzhetiankongExercise';
                        $this->display('editCompletionChoice');
                        break;
                    case CASEFOUR:
                        $this->addExercise = 'editlianxianExercise';
                        $this->display('editMatchingExercise');
                        break;
                    case CASEFIVE:
                        $this->addExercise = 'zuotuaddExercise';
                        $this->display('editDrawingExercise');
                        break;
                    case CASESIX:
                        $this->addExercise = 'jiedaaddExercise';
                        $this->display('editAnswerQuestions');
                        break;
                    default : //默认是复合题
                        $this->type = NUll;
                        $this->display('editmoreQuestions');
                        break;
                }
            } else { //复合题

                $exercise_info['track'] = $this->Exercises_Log->getQuestionLog($id);
                $exercise_info['numcopy'] = $exercise_info['num']-1;
                $exercise_info['class_type_arr'] = !empty($exercise_info['class_type_arr'])?json_decode($exercise_info['class_type_arr'],true):$exercise_info['class_type_arr'];
                $exercise_info['class_type_arr'] = implode("|",$exercise_info['class_type_arr']);
                $this->exercise_info = $exercise_info;
                $this->getCourse = $this->getCourseList->getCourseList();
                if (!empty($exercise_info['subject'])) {
                    $this->getChildCourse = $this->getCourseList->getCourseList($exercise_info['subject']);
                }
                $childExercise = $this->Create_Exercise->getChildExercise($exercise_info['id']);
                $this->childExerciseCount = count($childExercise);
                if (!empty($childExercise)) {
                    foreach ($childExercise as $k=>$v) {
                        //$childExercise[$k]['search_name'] = html_entity_decode($v['search_name']);
                        //$childExercise[$k]['subject_name'] = html_entity_decode($v['subject_name']);
                        //$childExercise[$k]['analysis'] = html_entity_decode($v['analysis']);
                        $childExercise[$k]['answer'] = json_decode($v['answer'],true);
                        $childExercise[$k]['answer_select'] = json_decode($v['answer_select'],true);
                    }
                }
                $this->childExerciseList = $childExercise;
                $this->display('editmoreQuestions');
            }

        }


    }

    //添加习题
    public function addExercise() {

        $study_section = I('studyId');//学段
        $subject = I('courseId');//学科
        $topic_type = I('type');//
        $subject_name = I('message');//题目类型题目
        $home_topic_type = I('Problempresentation');//前台展示题型
        $exerciseNums = I('exerciseNums');//选项数量
        $right_key = I('trueAnswer');//正确答案
        $analysis = I('messageJx');//解析
        $answer = json_encode(I('arrayMessage'));//答案
        $count_score = I('Fractiondata');
        $score = I('Fractiondata');
        $count_score = rtrim($count_score,',');
        $answerWidth = I('answerWidth');

        $big_paper_id = I('big_paper_id'); //试卷大题id
        $paper_id = I('paper_id');//试卷id

        $json_html = I('html');//试卷id

        $this->check($_POST);

        /*$token = I('token');//表单重复提交验证
        if (!checkToken($token)) {
            $res['code'] = ERRORCODE;
            $res['msg'] = '不要重复提交表单！';
            $this->ajaxReturn($res);
        }*/

        if (strpos($count_score,',')!=false) {
            $count_score = explode(',',$count_score);
            $count_score = array_sum($count_score);
        }

        $score = rtrim($score,',');

        $answer_select = json_encode(I('answer_select'));//答案选项
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
        $data['class_type'] = $answerWidth;
        $data['json_html'] = json_encode($json_html);

        if ($big_paper_id=='undefined') {
            $big_paper_id='';
        }

        if ($paper_id=='undefined') {
            $paper_id='';
        }

        if (!empty($big_paper_id) && !empty($paper_id)) {
            $data['status'] = 19;
        }

        $this->Create_Exercise->startTrans();
        $id = $this->Create_Exercise->createExerciseInfo($data);
        if ( $id ) {
            $truep = $this->Exercises_question_processinfo->addCreatorInfo($id,session('admin.id'),session('admin.user_name'));
            $log_id = $this->Exercises_Log->insertLog($id,$paperId=0,'创建',get_client_ip(),session('admin.id'),session('admin.user_name'),'',$poperatorId=0,$poperatorName='');

            if (!empty($big_paper_id) && !empty($paper_id)) { //录入试卷的习题
                $paperdata['exercise_id'] = $id;
                $paperdata['paper_id'] = $paper_id;
                $paperdata['big_question_id'] = $big_paper_id;
                $paperdata['create_at'] = time();
                $paperdata['big_order'] = $id;
                $paperdata['exercises_score'] = $count_score;
                $p_c_id = $this->Exercises_parper_concat->addParperConcat($paperdata); //关联试卷试题的关系

                if ( $truep && $log_id && $p_c_id ) {
                    $this->Exercises_paper_bigquestion->updateParperBigSetNum($paper_id,$big_paper_id);//把大题中的小题数量加1
                    $this->Create_Exercise->commit();
                    $res['code'] = SUCCESSCODE;
                } else {
                    $this->Create_Exercise->rollback();
                    $res['code'] = ERRORCODE;
                    $res['msg'] = '添加失败';
                }

            } else { //录入单独的习题
                if ( $truep && $log_id ) {
                    $this->Create_Exercise->commit();
                    $res['code'] = SUCCESSCODE;
                } else {
                    $this->Create_Exercise->rollback();
                    $res['code'] = ERRORCODE;
                    $res['msg'] = '添加失败';
                }
            }


        } else {
            $this->Create_Exercise->rollback();
            $res['msg'] = '添加失败';
            $res['code'] = ERRORCODE;
        }
        $this->ajaxReturn($res);
    }

    //校验跳转方法
    public function check( $post ) {
        if(empty($post['type'])){
            $res['msg'] = '添加失败';
            $res['code'] = ERRORCODE;
            $this->ajaxReturn($res);
        }

        switch ($post['type']) {
            case CASEONE:
                $this->createChoiceExerciseCheck($post);
                break;
            case CASETWO:
                $this->createCompletionCheck($post);
                break;
            case CASETHREE:
                $this->createCompletionChoiceeCheck($post);
                break;
            case CASEFOUR:
                $this->createMatchingExerciseCheck($post);
                break;
            case CASEFIVE:
                $this->createDrawingExerciseCheck($post);
                break;
            case CASESIX:
                $this->createAnswerQuestionsCheck($post);
                break;
        }
    }

    //公共的校验
    public function commonCheck($post) {
        if (empty($post['studyId'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '没有选择学段';
            $this->ajaxReturn($errorInfo);
        }
        if (empty($post['courseId'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '没有选择学科';
            $this->ajaxReturn($errorInfo);
        }
        if (empty($post['message'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '没有填写题目';
            $this->ajaxReturn($errorInfo);
        }
        if (empty($post['Problempresentation'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '没有选择展示题型';
            $this->ajaxReturn($errorInfo);
        }

        if (empty($post['messageJx'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '没有填写解析';
            $this->ajaxReturn($errorInfo);
        }

    }

    //校验连线题
    public function createMatchingExerciseCheck($post) {
        $this->commonCheck($post);
        if (empty($post['answer_select'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '选择内容没有填写';
            $this->ajaxReturn($errorInfo);
        }

        if (empty($post['arrayMessage'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '答案没有填写';
            $this->ajaxReturn($errorInfo);
        }else {
            foreach ($post['arrayMessage'] as $k=>$v) {
                if (empty($v)) {
                    $errorInfo['code'] = ERRORCODE;
                    $errorInfo['msg'] = '没有填写答案';
                    $this->ajaxReturn($errorInfo);
                }
            }
        }

        if (empty($post['Fractiondata'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '没有填写分数';
            $this->ajaxReturn($errorInfo);
        } else {
            $post['Fractiondata'] = rtrim($post['Fractiondata'],',');
            $Fractiondata = explode(',',$post['Fractiondata']);

            foreach ($Fractiondata as $fk=>$fv) {
                if (is_numeric($fv) == false ) {
                    $errorInfo['code'] = ERRORCODE;
                    $errorInfo['msg'] = '分数填写不正确';
                    $this->ajaxReturn($errorInfo);
                }
            }
        }

    }

    //校验选择填空
    public function createCompletionChoiceeCheck($post) {
        $this->commonCheck($post);

        if (empty($post['answer_select'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '选择内容没有填写';
            $this->ajaxReturn($errorInfo);
        }

        if (empty($post['arrayMessage'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '答案没有填写';
            $this->ajaxReturn($errorInfo);
        } else {
            foreach ($post['arrayMessage'] as $k=>$v) {
                if (empty($v)) {
                    $errorInfo['code'] = ERRORCODE;
                    $errorInfo['msg'] = '没有填写答案';
                    $this->ajaxReturn($errorInfo);
                }
            }
        }

        if (empty($post['Fractiondata'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '没有填写分数';
            $this->ajaxReturn($errorInfo);
        } else {
            $Fractiondata = explode(',',$post['Fractiondata']);
            foreach ($Fractiondata as $fk=>$fv) {
                if (is_numeric($fv) == false ) {
                    $errorInfo['code'] = ERRORCODE;
                    $errorInfo['msg'] = '分数填写不正确';
                    $this->ajaxReturn($errorInfo);
                }
            }
        }

    }


    //校验文字填空
    public function createCompletionCheck($post) {
        $this->commonCheck($post);

        if (empty($post['arrayMessage'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '没有填写答案';
            $this->ajaxReturn($errorInfo);
        } else {
            foreach ($post['arrayMessage'] as $k=>$v) {
                if (empty($v)) {
                    $errorInfo['code'] = ERRORCODE;
                    $errorInfo['msg'] = '没有填写答案';
                    $this->ajaxReturn($errorInfo);
                }
            }
        }

        if (empty($post['Fractiondata'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '没有填写分数';
            $this->ajaxReturn($errorInfo);
        } else {
            $Fractiondata = explode(',',$post['Fractiondata']);
            foreach ($Fractiondata as $fk=>$fv) {
                if (is_numeric($fv) == false ) {
                    $errorInfo['code'] = ERRORCODE;
                    $errorInfo['msg'] = '分数填写不正确';
                    $this->ajaxReturn($errorInfo);
                }
            }
        }
    }

    //校验选择题
    public function createChoiceExerciseCheck($post) {
        $this->commonCheck($post);

        if (empty($post['exerciseNums'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '没有选择选择数量';
            $this->ajaxReturn($errorInfo);
        }

        if (empty($post['trueAnswer'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '没有填写正确答案';
            $this->ajaxReturn($errorInfo);
        }

        if (empty($post['Fractiondata'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '没有填写分数';
            $this->ajaxReturn($errorInfo);
        } else {
            if (is_numeric($post['Fractiondata']) == false ) {
                $errorInfo['code'] = ERRORCODE;
                $errorInfo['msg'] = '分数填写不正确';
                $this->ajaxReturn($errorInfo);
            }

        }
    }

    //校验作图

    public function createDrawingExerciseCheck($post) {
        $this->commonCheck($post);

        if (empty($post['trueAnswer'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '没有填写正确答案';
            $this->ajaxReturn($errorInfo);
        }

        if (empty($post['Fractiondata'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '没有填写分数';
            $this->ajaxReturn($errorInfo);
        }else {
            if (is_numeric($post['Fractiondata']) == false ) {
                $errorInfo['code'] = ERRORCODE;
                $errorInfo['msg'] = '分数填写不正确';
                $this->ajaxReturn($errorInfo);
            }

        }
    }

    //校验解答题
    public function createAnswerQuestionsCheck($post) {
        $this->commonCheck($post);

        if (empty($post['trueAnswer'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '没有填写正确答案';
            $this->ajaxReturn($errorInfo);
        }

        if (empty($post['Fractiondata'])) {
            $errorInfo['code'] = ERRORCODE;
            $errorInfo['msg'] = '没有填写分数';
            $this->ajaxReturn($errorInfo);
        }else {
            if (is_numeric($post['Fractiondata']) == false ) {
                $errorInfo['code'] = ERRORCODE;
                $errorInfo['msg'] = '分数填写不正确';
                $this->ajaxReturn($errorInfo);
            }

        }
    }

    public function createCompletion(){
         $this->display();
    }

    public function createCompletionChoice(){
         $this->display();
    }

    public function createAnswerQuestions(){
         $this->display();
    }

    public function createDrawingExercise(){
         $this->display();
    }

    public function createMatchingExercise(){
         $this->display();
    }

    public function exerciseEnteringPage(){
        $data = array();
        $header = array(
            array('name'=>'序号','field'=>'rownum','callback'=>function($value){return $value;}),
            array('name'=>'试题ID','field'=>'id','callback'=>function($value){return $value;}),
            array('name'=>'学科','field'=>'course_name','callback'=>function($value){return $value;}),
            array('name'=>'题型','field'=>'topic_name'),
            array('name'=>'学段','field'=>'study_section','callback'=>function($value){foreach(C('Studysection') as $key=>$val){ if($val['id'] == $value) return $val['name'];}return '';}),
            array('name'=>'录入人员','field'=>'creator_name'),
            array('name'=>'题目信息','field'=>'subject_name','callback'=>function($value){return $value;}),

        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $export =  getParameter('export','str',false);
        if($export)
            $condition = json_decode(htmlspecialchars_decode(getParameter('formCondition','str',false)),true);
        else
            $condition = array(
                'questionId'  =>   getParameter('questionId','int',false),
                'courseId' =>   getParameter('courseId','int',false),
                'exerciseCategory' => getParameter('exerciseCategory','int',false),
                'keyword' => getParameter('keyword','str',false),
                'study_section' => getParameter('section','int',false),
                'creatorId' => getParameter('creatorId','int',false),
                'inputStartTime' => getParameter('inputStartTime','str',false),
                'inputEndTime' => getParameter('inputEndTime','str',false),
                'startIndex' =>  getParameter('startIndex','int',false),
                'pageSize' =>  getParameter('pageSize','int',false),
                'cat' => getParameter('cat'  ,'str',false),
            );
        switch($condition['cat'])
        {
            case EXERCISE_HASINPUT:
                $condition['is_self'] = 1;
                $condition['status'] = EXERCISE_STATE_WAITVERIFY;
                $count =  D('Exercises_question_processinfo')->getQuestionCount($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
                $list =  D('Exercises_question_processinfo')->getQuestionList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
                if(!$export)
                    $header[] = array('name'=>'操作','field'=>'id','callback'=>function($value){return 'delete';});
                break;
            case EXERCISE_WAITVERIFY:
                $condition['is_self'] = -1;
                $condition['status'] = EXERCISE_STATE_WAITVERIFY;
                $count =  D('Exercises_question_processinfo')->getQuestionCount($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
                $list =  D('Exercises_question_processinfo')->getQuestionList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
                if(!$export)
                    $header[] = array('name'=>'操作','field'=>'id','callback'=>function($value){return 'verify';});
                break;
            case EXERCISE_REPROCESS:
                $condition['is_self'] = 1;
                $condition['status'] = EXERCISE_STATE_REPROCESS;
                $order =  'reject_time';
                $count =  D('Exercises_question_processinfo')->getQuestionCount($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition,$order);
                $list =  D('Exercises_question_processinfo')->getQuestionList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition,$order);
                if(!$export)
                    $header[] = array('name'=>'操作','field'=>'id','callback'=>function($value){return 'modify,delete';});
                break;
            default:$count=0;$list=array();
        }

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
    public function exerciseEntering(){
        $this->assign('parent', '试题录入管理');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '');
        //getParameter('')
        $courses = D('Exercises_Course')->getCourseList();
        $province = D('Dict_citydistrict')->getProvince();
        $creators = D('Exercises_question_processinfo')->getDistinctCreator();
        $sections = C('Studysection');
        if(empty($_GET['cat']))
            $_GET['cat'] = 1;
        $this->assign('creatorList',$creators);
        $this->assign('courseList',$courses);
        $this->assign('sections',$sections);
        $this->assign('provinceList',$province);
        $this->display_nocache();
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

       );
       $data['sEcho'] = getParameter('sEcho','str',false);
       $export =  getParameter('export','str',false);
       if($export)
           $condition = json_decode(htmlspecialchars_decode(getParameter('formCondition','str',false)),true);
       else
       $condition = array(
         'paperId'  =>   getParameter('paperId','int',false),
         'courseId' =>   getParameter('courseId','int',false),
         'gradeId'  =>   getParameter('gradeId','int',false),
         'paperName' => getParameter('paperName','str',false),
         'provinceId' => getParameter('provinceId','int',false),
         'section' =>   getParameter('section','int',false),
         'paperCategory' => getParameter('paperCategory','int',false),
         'year' => getParameter('year','int',false),
         'creatorId' => getParameter('creatorId','int',false),
         'inputStartTime' => getParameter('inputStartTime','str',false),
         'inputEndTime' => getParameter('inputEndTime','str',false),
         'startIndex' =>  getParameter('startIndex','int',false),
         'pageSize' =>  getParameter('pageSize','int',false),
         'cat' => getParameter('cat'  ,'str',false)

       );

       switch($condition['cat'])
       {
           case EXERCISE_HASINPUT:
                         $condition['is_self'] = 1;
                         $condition['status'] = EXERCISE_STATE_WAITVERIFY;
                         $count =  D('Exercises_paper_processinfo')->getPaperCount($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
                         $list =  D('Exercises_paper_processinfo')->getPaperList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
                         if(!$export)
                         $header[] = array('name'=>'操作','field'=>'id','callback'=>function($value){return 'delete';});
                         break;
           case EXERCISE_WAITVERIFY:
                         $condition['is_self'] = -1;
                         $condition['status'] = EXERCISE_STATE_WAITVERIFY;
                         $count =  D('Exercises_paper_processinfo')->getPaperCount($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
                         $list =  D('Exercises_paper_processinfo')->getPaperList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition);
                         if(!$export)
                         $header[] = array('name'=>'操作','field'=>'id','callback'=>function($value){return 'verify';});
                         break;
           case EXERCISE_REPROCESS:
                         $condition['is_self'] = 1;
                         $condition['status'] = EXERCISE_STATE_REPROCESS;
                         $order =  'reject_time';
                         $count =  D('Exercises_paper_processinfo')->getPaperCount($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition,$order);
                         $list =  D('Exercises_paper_processinfo')->getPaperList($this->userInfo['id'],ROLE_INPUTEXERCISE,$condition,$order);
                         if(!$export)
                             $header[] = array('name'=>'操作','field'=>'id','callback'=>function($value){return 'modify,delete';});
                         break;
           default:$count=0;$list=array();
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

    //试卷列表
    function testEntering(){
        $this->assign('parent', '试卷录入管理');
        $this->assign('parentHref', 'javascript:;');
        $this->assign('own', '');

         $courses = D('Exercises_Course')->getCourseList();
         $grade = D('Dict_grade')->getGradeList(1);
         $province = D('Dict_citydistrict')->getProvince();
         $year = D('Exercises_create_paper')->getDistinctYear();
         $creators = D('Exercises_paper_processinfo')->getDistinctCreator();
        if(empty($_GET['cat']))
            $_GET['cat'] = 1;
         $this->assign('creatorList',$creators);
         $this->assign('yearList',$year);
         $this->assign('courseList',$courses);
         $this->assign('gradeList',$grade);
         $this->assign('provinceList',$province);
         $this->display_nocache();
    }

    //添加试卷
    public function createPaper() {
        $this->assign('parent', '试卷录入管理');
        $this->assign('parentHref', U('CreateExercise/testEntering'));
        $this->assign('own', ' >> 试卷录入');
        if ( IS_POST ) {
            $errorinfo='';
            $this->checkPaper($_POST,$errorinfo);
            if(!empty($errorinfo)) {
                $res['code'] = ERRORCODE;
                $res['msg'] = $errorinfo;
                $this->ajaxReturn($res);
            }
            $questionCategory = I('questionCategory'); //来源
            $gradeid = I('gradeid');
            $termid = I('termid'); //分册
            $yearinfo = I('yearinfo');
            $regioninfo = I('regioninfo');
            $courseid = I('courseid');
            $cityid = I('cityid');
            $paperType = I('paperType');
            $paper_name = I('paper_name');
            $paper_model_num = I('paper_model_num');
            $big_topic_asnumber = I('big_topic_asnumber');
            $big_topic_name = I('big_topic_name');
            $big_topic_describe = I('big_topic_describe');

            $data = array(
                'paper_name' => $paper_name,
                'paper_type' => $paperType,
                'grade' => $gradeid,
                'section' => $termid,
                'period' => $questionCategory,
                'year' => $yearinfo,
                'region' => $regioninfo,
                'subject' => $courseid,
                'city_id' => $cityid,
                'paper_num' => $paper_model_num,
                'create_at' => time(),
            );

            if ($questionCategory ==2 || $questionCategory==3) {
                unset($data['grade']);
                unset($data['section']);
            }

            $this->paper->startTrans();

            $paperId = $this->paper->addPaper( $data );
            $paper_prc_id = $this->Exercises_paper_processinfo->addCreatorInfo( $paperId,session('admin.id'),session('admin.user_name') );
            $log_id = $this->Exercises_Log->insertLog($id=0,$paperId,'创建',get_client_ip(),session('admin.id'),session('admin.user_name'),'',$poperatorId=0,$poperatorName='');

            $lock = true;

            for ($i=0;$i<$paper_model_num;$i++) {
                $bigdata = array(
                    'big_topic_number' =>$i,
                    'paper_id' => $paperId,
                    'big_topic_asnumber' => $big_topic_asnumber[$i],
                    'big_topic_name' => $big_topic_name[$i],
                    'big_topic_describe' => $big_topic_describe[$i],
                );
                $big_id = $this->Exercises_paper_bigquestion->addPaperBigQuestion( $bigdata );
                if ( !$big_id ) {
                    $lock = false;
                    break;
                }
            }

            if($paperId && $paper_prc_id==true && $log_id==true && $lock == true) {

                $this->paper->commit();
                $res['code'] = SUCCESSCODE;
                $res['id'] = $paperId;
            } else {
                $this->paper->rollback();
                $res['code'] = ERRORCODE;
                $res['msg'] = '添加失败';
            }
            $this->ajaxReturn($res);
        } else{
            $this->getCourse = $this->getCourseList->getCourseList();
            $this->city = $this->City->getProvince();
            $this->display('testPaperInput');
        }

    }

    //修改试卷
    public function editpaper() {
        $url  =   $_SERVER["HTTP_REFERER"];
        if (strpos($url,'draftList') !== false || strpos($url,'testEntering') !== false ) {
            $this->isUrl = 1;
        } else {
            $this->isUrl = 2;
        }
        $this->assign('parent', '试卷管理');
        $this->assign('parentHref', U('TestMgmt/testMgmt'));

        $paperid = I('paperid'); //试卷id
        if (empty($paperid)) {
            die('非法传值');
        }
        $paperinfo = $this->paper->getPaperInfo( $paperid );

        $creatorInfo = $this->Exercises_paper_processinfo->getCreatorInfo($paperid);
        if($creatorInfo['creator_id'] != $this->userInfo['id'])
        {
            $this->error('您没有修改该试卷的权限');
        }

        $this->getCourse = $this->getCourseList->getCourseList();
        $this->city = $this->City->getProvince();
        $bigList = $this->Exercises_paper_bigquestion->getPaperBigQuestion( $paperid );
        if (!empty($bigList)) {
            foreach ($bigList as $k=>$v) {
                if (!empty($v['id'])) {
                    $data['big_question_id'] = $v['id'];
                    $childquestions = $this->Exercises_parper_concat->getQuestionBigPaper($data); //关联试卷试题的关系
                    if (!empty($childquestions)) {
                        $bigList[$k]['childquestions'] = $childquestions;
                    }
                }
            }
        }

        $this->bigList = $bigList;

        if($paperinfo['status'] == EXERCISE_STATE_DRAFT)
        $this->assign('own', ' >> 草稿详情');
        else
        $this->assign('own', ' >> 试卷详情');
        $paperinfo['track'] = $this->Exercises_Log->getPaperLog(  $paperid );
        $this->paperinfo = $paperinfo;
        $this->display();
    }

    //根据试卷id获取试卷的所有习题
    public function getPaperExList() {
        $paperid = I('paperid');
        $bigList = $this->Exercises_paper_bigquestion->getPaperBigQuestion( $paperid );
        if (!empty($bigList)) {

            foreach ($bigList as $k=>$v) {
                if (!empty($v['id'])) {
                    $data['big_question_id'] = $v['id'];
                    $exercise_info = $this->Exercises_parper_concat->getQuestionBigPaper($data); //关联试卷试题的关系
                    $difficultyArray = C('difficulty');
                    $html = new simple_html_dom();

                    foreach ($exercise_info as $kk=>&$vv) {
                        //$vv['json_html'] = html_entity_decode(json_decode($vv['json_html'],true));
                        $template = html_entity_decode(json_decode($vv['json_html'],true));
                        $html->load('<html><body>'.$template. '</body></html>');
                        $result = $html->find('.difficulty span')[1];
                        $result->outertext = $difficultyArray[$vv['difficulty']-1]['name'];
                        $result = $html->find('.exerciseDifficulty span')[0];
                        $result->outertext = $difficultyArray[$vv['difficulty']-1]['name'];
                        $vv['json_html'] = $html->save();
                    }
                    $bigList[$k]['childquestions']  = $exercise_info;

                }
            }
        }

        $res['code'] = ERRORCODE;
        $res['msg'] = $bigList;
        $this->ajaxReturn($res);

    }

    //修改试卷数据
    public function EditPaperInfo() {
        $errorinfo='';
        $this->checkPaper($_POST,$errorinfo);
        if(!empty($errorinfo)) {
            $res['code'] = ERRORCODE;
            $res['msg'] = $errorinfo;
            $this->ajaxReturn($res);
        }
        $paper_id = I('paper_id');
        if(empty($paper_id)) {
            die('参数错误');
        }

        $questionCategory = I('questionCategory'); //来源
        $gradeid = I('gradeid');
        $termid = I('termid'); //分册
        $yearinfo = I('yearinfo');
        $regioninfo = I('regioninfo');
        $courseid = I('courseid');
        $cityid = I('cityid');
        $paperType = I('paperType');
        $paper_name = I('paper_name');
        $paper_model_num = I('paper_model_num');
        $big_topic_asnumber = I('big_topic_asnumber');
        $big_topic_name = I('big_topic_name');
        $big_topic_describe = I('big_topic_describe');
        $big_question_id = I('big_question_id');

        $data = array(
            'paper_name' => $paper_name,
            'paper_type' => $paperType,
            'grade' => $gradeid,
            'section' => $termid,
            'period' => $questionCategory,
            'year' => $yearinfo,
            'region' => $regioninfo,
            'subject' => $courseid,
            'city_id' => $cityid,
            'paper_num' => $paper_model_num,
            'create_at' => time(),
        );

        $this->paper->startTrans();

        $paperId = $this->paper->updatePaper( $paper_id,$data );
        $paper_prc_id = $this->Exercises_paper_processinfo->updateCreatorInfo( $paper_id,session('admin.id'),session('admin.user_name') );
        $log_id = $this->Exercises_Log->insertLog($id=0,$paper_id,'修改',get_client_ip(),session('admin.id'),session('admin.user_name'),'',$poperatorId=0,$poperatorName='');

        $lock = true;

        for ($i=0;$i<$paper_model_num;$i++) {
            $bigdata = array(
                'big_topic_name' => $big_topic_name[$i],
                'big_topic_describe' => $big_topic_describe[$i],
            );
            $big_id = $this->Exercises_paper_bigquestion->updatePaperBigQuestion( $big_question_id[$i],$bigdata );

            if ( $big_id==false ) {
                $lock = false;
                break;
            }
        }

        if( $paperId && $paper_prc_id==true && $log_id==true && $lock == true ) {

            $this->paper->commit();
            $res['code'] = SUCCESSCODE;
            $res['id'] = $paperId;
        } else {
            $this->paper->rollback();
            $res['code'] = ERRORCODE;
            $res['msg'] = '添加失败';
        }
        $this->ajaxReturn($res);
    }

    //试卷校验
    public function checkPaper( $post,&$errorinfo ) {
        if(empty($post['questionCategory'])) {
            $errorinfo = '请选择试卷来源';
            return false;
        }
       /* if(empty($post['gradeid'])) {
            $errorinfo = '请选择试卷年级';
            return false;
        }
        if(empty($post['termid'])) {
            $errorinfo = '请选择试卷分册';
            return false;
        }*/
        if(empty($post['yearinfo'])) {
            $errorinfo = '请填写试卷年份';
            return false;
        }
        /*if(empty($post['regioninfo'])) {
            $errorinfo = '请填写试卷地区';
            return false;
        }*/
        if(empty($post['courseid'])) {
            $errorinfo = '请选择试卷科目';
            return false;
        }
        if(empty($post['cityid'])) {
            $errorinfo = '请选择试卷省份';
            return false;
        }
        if(empty($post['paper_name'])) {
            $errorinfo = '请填写试卷名称';
            return false;
        }
    }

    //提交试卷
    public function submitPaper() {

        $paper_id = I('paper_id');
        if (empty($paper_id)  || is_numeric($paper_id)==false ) {
            $res['status'] = ERRORCODE;
            $res['message'] = '参数有误';
            $this->ajaxReturn($res);
        }

        $count = $this->Exercises_parper_concat->getPaperQuestionCount($paper_id);
        if ( $count==0 || $count<0 ) {
            $res['status'] = ERRORCODE;
            $res['message'] = '请选择至少一个习题';
            $this->ajaxReturn($res);
        }

        $isTrue = $this->Exercises_parper_concat->setPaperStatusPending( $paper_id );

        $isPaper = $this->paper->updatePaperCountScore( $paper_id ); //修改试卷的总分

        $upId = $this->Exercises_paper_processinfo->setPaperState($paper_id,EXERCISE_STATE_WAITVERIFY,session('admin.id'));
        $currentStatus = $this->Exercises_paper_processinfo->getPaperStatus($paper_id);
         $setState = true;
        if(EXERCISE_STATE_REPROCESS == $currentStatus)
         $setState = $this->Exercises_paper_processinfo->setStateUser($paper_id,session('admin.id'),session('admin.user_name'),1,'reprocess');
        if ( $isTrue == true && $isPaper == true && $upId == true && $setState) {
            $res['status'] = SUCCESSCODE;
        } else {
            $res['status'] = ERRORCODE;
            $res['message'] = '提交失败';
        }
        $this->ajaxReturn($res);
    }

    function draftListPage()
    {
        $data = array();
        $header = array(
            array('name'=>'试卷名称','field'=>'paper_name','callback'=>function($value){return $value;}),
            array('name'=>'试卷ID','field'=>'id','callback'=>function($value){return $value;}),
            array('name'=>'试卷类型','field'=>'paper_type','callback'=>function($value){switch($value){case CASEONE: return '真题';case CASETWO: return '模拟题';default:return '';}}),
            array('name'=>'省份','field'=>'province','callback'=>function($value){return $value;}),
            array('name'=>'年份','field'=>'year'),
            array('name'=>'分册','field'=>'section','callback'=>function($value){foreach(C('schoolTerm') as $key=>$val){ if($val['id'] == $value) return $val['name'];}return '';}),
            array('name'=>'年级','field'=>'grade_name','callback'=>function($value){return $value;}),
            array('name'=>'学科','field'=>'course_name','callback'=>function($value){return $value;}),
            array('name'=>'试卷模块数','field'=>'paper_num','callback'=>function($value){return $value;}),
            array('name'=>'试卷总分','field'=>'truetotalscore','callback'=>function($value){return $value;}),
        );
        $data['sEcho'] = getParameter('sEcho','str',false);
        $export =  getParameter('export','str',false);
        $condition = array(
            'startIndex' =>  getParameter('startIndex','int',false),
            'pageSize' =>  getParameter('pageSize','int',false),
        );
        $condition['is_self'] = 1;
        $condition['status'] = EXERCISE_STATE_DRAFT;
        $condition['needTotalScore'] =1;
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
    /*
     *我的草稿
     */
    public function draftList(){
        $this->assign('parent', '试卷录入管理');
        $this->assign('parentHref', U('CreateExercise/testEntering'));
        $this->assign('own', ' >> 我的草稿');
        $this->display();
    }

    public function examinePaper()
    {
        $this->assign('parent', '试卷录入管理');
        $this->assign('parentHref', U('CreateExercise/testEntering'));
        $this->assign('own', ' >> 试卷校审');
        $this->examinationParpe();
    }
    //试卷预览
    public function paperDetails()
    {
        $paperid = I('paperid'); //试卷id
        if ( empty($paperid)   || is_numeric($paperid)==false  ) {
            die('非法传值');
        }
        $this->getCourse = $this->getCourseList->getCourseList();
        $this->city = $this->City->getProvince();
        $bigList = $this->Exercises_paper_bigquestion->getPaperBigQuestion( $paperid );
        if (!empty($bigList)) {
            foreach ($bigList as $k=>$v) {
                if (!empty($v['id'])) {
                    $data['big_question_id'] = $v['id'];
                    $childquestions = $this->Exercises_parper_concat->getQuestionBigPaper($data); //关联试卷试题的关系
                    if (!empty($childquestions)) {
                        $bigList[$k]['childquestions'] = $childquestions;
                    }
                }
            }
        }

        $this->bigList = $bigList;
        $paperinfo = $this->paper->getPaperInfo( $paperid );
        $paperinfo['track'] = $this->Exercises_Log->getPaperLog(  $paperid );
        $this->paperinfo = $paperinfo;
        $this->display();
    }
    //试卷校审
    public function examinationParpe() {
        $paperid = I('paperid'); //试卷id
        if ( empty($paperid)   || is_numeric($paperid)==false  ) {
            die('非法传值');
        }
        $paperinfo = $this->paper->getPaperInfo( $paperid );

        $this->verifyPaperOperationAuth($paperinfo,EXERCISE_STATE_WAITVERIFY);
        $creatorInfo = $this->Exercises_paper_processinfo->getCreatorInfo($paperid);
        if($creatorInfo['creator_id'] == $this->userInfo['id'])
        {
            $this->error('您没有校审该试卷的权限');
        }
        $this->getCourse = $this->getCourseList->getCourseList();
        $this->city = $this->City->getProvince();
        $bigList = $this->Exercises_paper_bigquestion->getPaperBigQuestion( $paperid );
        if (!empty($bigList)) {
            foreach ($bigList as $k=>$v) {
                if (!empty($v['id'])) {
                    $data['big_question_id'] = $v['id'];
                    $childquestions = $this->Exercises_parper_concat->getQuestionBigPaper($data); //关联试卷试题的关系
                    if (!empty($childquestions)) {
                        $bigList[$k]['childquestions'] = $childquestions;
                    }
                }
            }
        }

        $this->bigList = $bigList;

        $paperinfo['track'] = $this->Exercises_Log->getPaperLog(  $paperid );
        $this->paperinfo = $paperinfo;
        $this->display('examinationParpe');
    }

    //根据习题id获取习题详情
    public function getApiExercisesInfo() {
        $id = I('id');
        $info = $this->Create_Exercise->getExerciseInfo($id);

        $html = new simple_html_dom();
        $difficultyArray = C('difficulty');
        $template = html_entity_decode(json_decode($info['json_html'],true));
        $html->load('<html><body>'.$template. '</body></html>');
        $result = $html->find('.difficulty span')[1];
        $result->outertext = $difficultyArray[$info['difficulty']-1]['name'];
        $info['json_html'] = $html->save();

        if ($info['exercise_type'] ==2) {
            $info['child'] = $this->Create_Exercise->getChildExercise($id);
        }
        $this->showjson(200, '', $info);

    }

}
