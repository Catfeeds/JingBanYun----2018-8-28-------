<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/9/7
 * Time: 11:42
 */
namespace Common\Model;

use PhpOffice\PhpWord\Element\Row;
use Think\Model;
use Common\Common\simple_html_dom;
class Exercises_student_homeworkModel extends Model
{
    public $model = '';
    protected $tableName = 'exercises_student_homework';

    public function __construct()
    {
        parent::__construct();
        include $_SERVER['DOCUMENT_ROOT'].'/Application/Exercise/Conf/const.php';
    }

    /*
     *增加作业习题相关
     */
    public function addInfo($data)
    {
        $id = M($this->tableName)->add($data);
        if(empty($id))
            return false;
        return $id;
    }
    public function addSubInfo($data)
    {
        $id = M('exercises_student_relation')->addAll($data);
        if(empty($id))
            return false;
        return $id;
    }

    public function getClassHomeworkStudentSubmitCount($homeworkId,$classId,$category,$pc='',$keyword='')
    {
        if ($pc=='pc') {

        } else {

        }
        $additionalWhere = '';
        switch($category)
        {
            case STUDENT_HOMEWORK_NOTSUBMIT:$additionalWhere = ' AND exercises_student_homework.id is null ';break;
            case STUDENT_HOMEWORK_SUBMITED:$additionalWhere = ' AND exercises_student_homework.id is not null ';break;
            default:break;
        }
        if(!empty($keyword))
        {
            $keyword = str_replace('%','\%',mysql_escape_string($keyword));
            $additionalWhere .= " AND auth_student.student_name like '%$keyword%' ";
        }

        $result = M()->query("SELECT COUNT(1) count FROM biz_class_student 
                             JOIN biz_class ON biz_class.id = biz_class_student.class_id 
                               AND biz_class.is_delete= ".STUDENT_HOMEWORK_NORMAL." AND biz_class_student.status = ".STUDENT_JOINSTATE_NORMAL." AND biz_class.id=$classId 
                             JOIN auth_student ON auth_student.id = biz_class_student.student_id
                             LEFT JOIN exercises_student_homework ON exercises_student_homework.student_id = auth_student.id 
                               AND exercises_student_homework.class_id = $classId AND exercises_student_homework.work_id = $homeworkId WHERE 1=1 $additionalWhere 
                             ");
        return $result[0]['count'];

    }
    /*
     *获取班级作业提交情况
     */
    public function getClassHomeworkStudentSubmitInfo($homeworkId,$classId,$category,$pageIndex=1,$pageSize=20,$pc='',$keyword='',$getCount=0)
    {
        if ($pc=='pc') {

        } else {

        }
        $additionalWhere = '';
        switch($category)
        {
            case STUDENT_HOMEWORK_NOTSUBMIT:$additionalWhere = ' AND  exercises_student_homework.id is null ';break;
            case STUDENT_HOMEWORK_SUBMITED:$additionalWhere = ' AND exercises_student_homework.id is not null ';break;
            case STUDENT_HOMEWORK_SUBMITANDCORRECTED:$additionalWhere = ' AND exercises_student_homework.id is not null AND correct_status=1';break;
            default:break;
        }
        if(!empty($keyword))
        {
            $keyword = str_replace('%','\%',mysql_escape_string($keyword));
            $additionalWhere .= " AND auth_student.student_name like '%$keyword%' ";
        }


        if($pageIndex == 0)
            $pageIndex =1;
        if($pageSize == 0)
            $pageSize = 20;
        $limitStr = '';
        if($pageIndex != -1)
        {
            $startIndex = ($pageIndex-1)*$pageSize;
            $limitStr = " LIMIT $startIndex,$pageSize";
        }
        if(0 == $getCount) {
            $totalExerciseNum = M()->query("SELECT exercises_num COUNT FROM exercises_homwork_basics WHERE id = $homeworkId");
            $totalExerciseNum = $totalExerciseNum[0]['count'];
            $result = M()->query("SELECT auth_student.id as sid,exercises_student_homework.id,auth_student.student_name,auth_student.sex,exercises_student_homework.correct_status,exercises_student_homework.work_timeout,exercises_student_homework.total_score total_score,(CASE WHEN YEAR(submit_at) = YEAR(NOW()) THEN DATE_FORMAT(submit_at,'%m月%d日 %H:%i')
                              ELSE DATE_FORMAT(submit_at,'%Y年%m月%d日 %H:%i') END) create_at,auth_student.avatar FROM biz_class_student 
                             JOIN biz_class ON biz_class.id = biz_class_student.class_id 
                               AND biz_class.is_delete= 0 AND biz_class_student.status = " . STUDENT_JOINSTATE_NORMAL . " AND biz_class.id=$classId 
                             JOIN auth_student ON auth_student.id = biz_class_student.student_id
                             LEFT JOIN exercises_student_homework ON exercises_student_homework.student_id = auth_student.id 
                               AND exercises_student_homework.class_id = $classId AND exercises_student_homework.work_id = $homeworkId WHERE 1=1 $additionalWhere $limitStr
                             ");
        }
        else {
            $result = M()->query("SELECT COUNT(1) count FROM biz_class_student 
                             JOIN biz_class ON biz_class.id = biz_class_student.class_id 
                               AND biz_class.is_delete= 0 AND biz_class_student.status = " . STUDENT_JOINSTATE_NORMAL . " AND biz_class.id=$classId 
                             JOIN auth_student ON auth_student.id = biz_class_student.student_id
                             LEFT JOIN exercises_student_homework ON exercises_student_homework.student_id = auth_student.id 
                               AND exercises_student_homework.class_id = $classId AND exercises_student_homework.work_id = $homeworkId WHERE 1=1 $additionalWhere 
                             ");
            $result = $result[0]['count'];
        }

        return $result;
    }

    public function getStudentHomeworkBriefInfo($submitId=0)
    {
        $totalExerciseNum = M()->query("SELECT COUNT(1) COUNT FROM exercises_student_relation WHERE work_id = $submitId");
        $totalExerciseNum = $totalExerciseNum[0]['count'];
        $result = M()->query("SELECT exercises_student_homework.id,
                                      exercises_student_homework.student_id,
                                      exercises_student_homework.work_id,
                                      exercises_student_homework.total_score,
                                      exercises_student_homework.correct_status,
                                      exercises_student_homework.submit_at,
                                      exercises_student_homework.correct_at,
                                      exercises_student_homework.class_id,
                                      exercises_student_homework.correct_teacher_id,
                                      exercises_student_homework.correct_teacher_name,
                                      exercises_student_homework.work_timeout,
                                      SUM(exercises_student_relation.status=0) unprocess_num,
                                      SUM(exercises_student_relation.is_right=2) wrong_num,
                                      exercises_homwork_basics.name,
                                      exercises_homwork_basics.exercises_num,
                                      exercises_homwork_basics.course_name,
                                      exercises_homwork_basics.total_score as total_score_base,auth_student.student_name,exercises_homwork_basics.create_at,exercises_homwork_basics.deadline,exercises_homwork_basics.jobsments  FROM exercises_student_homework 
                             JOIN exercises_homwork_basics ON exercises_homwork_basics.id = exercises_student_homework.work_id
                             JOIN exercises_student_relation ON exercises_student_relation.work_id = exercises_student_homework.id
                             JOIN auth_student ON auth_student.id =  exercises_student_homework.student_id                             
                             WHERE exercises_student_homework.id=$submitId GROUP BY exercises_student_homework.id");
        return $result[0];
    }

    public function getOrderedExerciseList($idList='',$role=0,$userId=0,$isNormalExercise = 0)
    {
        $additionalWhere = '';
        if($isNormalExercise){
            $additionalWhere = ' AND exercises_createexercise.types = '.EXERCISE_TYPE_NORMAL.' ' ;
        }
        $result = M()->query("
                               SELECT exercises_createexercise.id,
                                       exercises_createexercise.id pid,
                                       (words ) name,
                                       (subject_name ) url,
                                       (analysis ) translation,
                                        types main_type,
                                        ordinary_type sub_type,
                                        home_topic_type,
                                        right_key,
                                        answer_select,
                                        subject_name,
                                        difficulty,
                                        answer,
                                        class_type,
                                        count_score score,
                                        topic_type,
                                        json_html,
                                        difficulty,
                                       (CASE WHEN exercise_type = 2 THEN (SELECT GROUP_CONCAT(subject_name separator '///') FROM exercises_createexercise WHERE id=pid) ELSE '' END) sub_subject_name,
                                       (CASE WHEN exercise_type = 2 THEN (SELECT GROUP_CONCAT(answer separator '///') FROM exercises_createexercise WHERE id=pid) ELSE '' END) sub_anaser,  
                                       (CASE WHEN exercise_type = 2 THEN (SELECT GROUP_CONCAT(topic_type separator '///') FROM exercises_createexercise WHERE id=pid) ELSE '' END) sub_topic_type,
                                       CONCAT(types,',',ordinary_type) category,
                                       exercises_collection.id eid
                                       FROM exercises_createexercise 
                               LEFT JOIN exercises_course ON exercises_createexercise.home_topic_type = exercises_course.id
                               LEFT JOIN exercises_collection ON exercises_collection.exercises_id=exercises_createexercise.id AND exercises_collection.role=$role AND teacher_id=$userId
                               WHERE exercises_createexercise.id IN ($idList) AND exercises_createexercise.status = ".EXERCISE_STATE_ONSHELF." AND exercises_createexercise.is_delete=".STATE_NORMAL." $additionalWhere ".
            " ORDER BY types desc,ordinary_type,exercises_course.setsort");
        return $result;
    }


    public function getHomeworkExerciseList($homeworkId,$classId=0,$submitId=0,$exerciseIds=[],$role=0,$userId=0)
    {
        $result = array();
        if($submitId == 0) {
            if ($classId == 0 && $submitId == 0) //查看作业基础信息
            {
                $result = M()->query("
                               SELECT exercises_createexercise.id,
                                       exercises_createexercise.id pid,
                                       (words ) name,
                                       (subject_name ) url,
                                       (analysis ) translation,
                                       (exercises_homwork_relation.exercises_score ) score,
                                        types main_type,
                                        ordinary_type sub_type,
                                        home_topic_type,
                                        subject_name,
                                        answer,
                                        topic_type,
                                        right_key,
                                        class_type,
                                        json_html,
                                       (CASE WHEN exercise_type = 2 THEN (SELECT GROUP_CONCAT(subject_name separator '///') FROM exercises_createexercise WHERE id=pid) ELSE '' END) sub_subject_name,
                                       (CASE WHEN exercise_type = 2 THEN (SELECT GROUP_CONCAT(answer separator '///') FROM exercises_createexercise WHERE id=pid) ELSE '' END) sub_anaser,  
                                       (CASE WHEN exercise_type = 2 THEN (SELECT GROUP_CONCAT(topic_type separator '///') FROM exercises_createexercise WHERE id=pid) ELSE '' END) sub_topic_type,
                                       CONCAT(types,',',ordinary_type) category
                                       FROM exercises_homwork_relation 
                               JOIN exercises_createexercise ON exercises_createexercise.id = exercises_homwork_relation.exercises_id
                               LEFT JOIN exercises_course ON exercises_createexercise.home_topic_type = exercises_course.id
                               WHERE exercises_homwork_relation.work_id = $homeworkId AND exercises_createexercise.status = ".EXERCISE_STATE_ONSHELF." AND exercises_createexercise.is_delete=".STATE_NORMAL.
                    " ORDER BY types desc,ordinary_type,exercises_course.setsort,exercises_homwork_relation.id");
            }
            else if ($classId != 0) //查看班级作业统计
            {

                $result = M()->query("
                               SELECT exercises_createexercise.id,
                                       words  name,
                                       (subject_name ) url,
                                       (analysis ) translation,
                                       (count_score) score ,
                                       home_topic_type,
                                       ifnull(finishcount,0) finishcount, 
                                       CONCAT(types,',',ordinary_type) category, 
                                       IFNULL(ratio.pointratio,'--') pointratio
                                       FROM exercises_homwork_relation 
                               JOIN exercises_createexercise ON exercises_createexercise.id = exercises_homwork_relation.exercises_id
                               LEFT JOIN (SELECT count(1) finishcount,exercises_id FROM exercises_student_relation WHERE homework_id = $homeworkId AND class_id = $classId GROUP BY exercises_id) finish 
                               ON finish.exercises_id = exercises_createexercise.id
                               LEFT JOIN (SELECT FORMAT(100*SUM(exercises_score)/SUM(total_score),2) pointratio,exercises_id FROM exercises_student_relation WHERE homework_id = $homeworkId AND class_id = $classId GROUP BY exercises_id) ratio
                               ON ratio.exercises_id = exercises_createexercise.id
                               
                               WHERE exercises_homwork_relation.work_id = $homeworkId AND exercises_createexercise.status = ".EXERCISE_STATE_ONSHELF." AND exercises_createexercise.is_delete=".STATE_NORMAL.
                    " ORDER BY types desc ,ordinary_type,home_topic_type,exercises_homwork_relation.id");

            }
        }
        else
        {
            //获取某学生的作业
            $additionalWhere = '';
            if(!empty($exerciseIds))
            {
                $additionalWhere = ' AND exercises_createexercise.id IN ('.implode(',',$exerciseIds).') ';
            }
            $result = M()->query("
                    SELECT exercises_createexercise.id,
                            exercises_createexercise.id pid,
                            exercises_createexercise.class_type,
                            words  name,
                            home_topic_type,
                            (CASE WHEN exercises_student_relation.exercises_score = exercises_student_relation.total_score THEN 1 ELSE 0 END) is_correct,
                           (exercises_student_relation.answer ) url,
                           (analysis ) translation,
                           (exercises_student_relation.exercises_score ) point ,
                           exercises_student_relation.status has_process,
                           (exercises_student_relation.total_score) total_point ,
                           (exercises_createexercise.subject_name) org_answer_url,                            
                           CONCAT(types,',',ordinary_type) category,
                           subject_name,
                           exercises_createexercise.answer,
                           right_key answer_select,
                           exercises_student_relation.answer student_answer,
                           json_html,
                           topic_type,
                           types main_type,
                           ordinary_type sub_type,
                           exercises_student_relation.status,
                          (CASE WHEN exercise_type = 2 THEN (SELECT GROUP_CONCAT(subject_name separator '///') FROM exercises_createexercise WHERE id=pid) ELSE '' END) sub_subject_name,
                          (CASE WHEN exercise_type = 2 THEN (SELECT GROUP_CONCAT(right_key separator '///') FROM exercises_createexercise WHERE id=pid) ELSE '' END) sub_answer_select,
                          (CASE WHEN exercise_type = 2 THEN (SELECT GROUP_CONCAT(answer separator '///') FROM exercises_createexercise WHERE id=pid) ELSE '' END) sub_anaser,  
                          (CASE WHEN exercise_type = 2 THEN (SELECT GROUP_CONCAT(topic_type separator '///') FROM exercises_createexercise WHERE id=pid) ELSE '' END) sub_topic_type,
                           exercises_homework_comment.comment,
                           exercises_collection.id eid              
                           FROM exercises_student_relation 
                   JOIN exercises_createexercise ON exercises_createexercise.id = exercises_student_relation.exercises_id
                   JOIN exercises_homwork_relation ON exercises_homwork_relation.exercises_id = exercises_createexercise.id AND exercises_homwork_relation.work_id = exercises_student_relation.homework_id
                   LEFT JOIN  exercises_homework_comment ON exercises_homework_comment.submit_exercise_id = exercises_student_relation.id
                   LEFT JOIN exercises_collection ON exercises_createexercise.id = exercises_collection.exercises_id AND role=$role AND exercises_collection.teacher_id=$userId
                   WHERE exercises_student_relation.work_id = $submitId $additionalWhere ".
                " ORDER BY types desc ,ordinary_type,home_topic_type,exercises_homwork_relation.id");

        }
        return $result;
    }

    public function getHomeworkIsSubmit($studentId,$homeworkId,$classId)
    {
       $result = M()->query("SELECT 1 FROM exercises_student_homework WHERE student_id = $studentId AND work_id = $homeworkId AND class_id = $classId LIMIT 1");
       if(empty($result))
           return false;
        return true;
    }

    public function getSubmitIdByHomeworkIdClassIdStudentId($studentId,$homeworkId,$classId)
    {
        $result = M()->query("SELECT id FROM exercises_student_homework WHERE student_id = $studentId AND work_id = $homeworkId AND class_id = $classId LIMIT 1");
        return $result[0]['id'];
    }

    public function getNextSubmitId($submitId,$homeworkId,$classId)
    {
        $result = M()->query("SELECT id FROM exercises_student_homework WHERE id>$submitId AND work_id = $homeworkId AND class_id = $classId LIMIT 1");
        return $result[0]['id'];
    }

    //根据学生id和作业id获取作业的总分
    public function getStudentScore($id){
        $map['id'] = $id;
        $score = M('exercises_student_homework')->where($map)->find();
        $totalExerciseNum = M()->query("SELECT COUNT(1) COUNT FROM exercises_student_relation WHERE work_id = $id");
        $totalExerciseNum = $totalExerciseNum[0]['count'];
        return round($score['total_score']/$totalExerciseNum);
    }

    //根据学生id和作业id获取习题详情
    public function getStudentExList( $studentId,$homewordId,$classId ) {
        $map['exercises_student_homework.student_id'] = $studentId;
        $map['exercises_student_homework.work_id'] = $homewordId;
        $map['exercises_student_homework.class_id'] = $classId;
        $list = M('exercises_student_homework')
            ->join('exercises_student_relation on exercises_student_relation.work_id=exercises_student_homework.id')
            ->join('exercises_createexercise on exercises_createexercise.id=exercises_student_relation.exercises_id')
            ->field("exercises_createexercise.id,exercises_createexercise.ordinary_type as category,exercises_createexercise.words as name,exercises_createexercise.analysis as translation,exercises_createexercise.subject_name as url,exercises_student_relation.exercises_score as point,exercises_student_relation.total_score,exercises_student_relation.answer")

            ->where($map)
            ->select();
        //print_r(M()->getLastSql());die();

        foreach ($list as $k=>$v) {
            $list[$k]['rate'] = $v['point'].'%';
            if ($v['point'] != $v['total_score']) {
                $list[$k]['is_right'] = 0;
            } else {
                $list[$k]['is_right'] = 1; //is_right等于1是正确
            }
        }

        return $list;
    }

    public function getSubmitIdList($homeworkId,$classId)
    {
        $result = M()->query("SELECT id FROM exercises_student_homework WHERE work_id = $homeworkId AND class_id = $classId");
        return $result;
    }

    public function getHomeworkTotalScore($studentId,$homeworkId,$classId)
    {
        $result = M()->query("SELECT 1,total_score FROM exercises_student_homework WHERE student_id = $studentId AND work_id = $homeworkId AND class_id = $classId LIMIT 1");
        return $result;
    }

    private function __getScore($standardAnswer,$answer,$type,$totalScore)
    {
       if($type == 1){
          if($standardAnswer == $answer){
              return $totalScore;
          }
          else
              return 0;
       }
       else if($type == 3)
       {
          $studentAnswerArray = explode(',',$answer);
          $standardAnswerArray = json_decode($standardAnswer,true);
          $html =  new simple_html_dom();
           if(empty($studentAnswerArray))
               return 0;
           foreach($standardAnswerArray as $key=>$val)
           {
               $val = htmlspecialchars_decode($val);
               $html->load($val);
               if($html->find('p',0)->innertext !== $studentAnswerArray[$key])
                   return 0;
           }
           return $totalScore;
       }
       else if($type == 4) //连线题
       {
           $allLines = sizeof(json_decode($standardAnswer,true));
           $answer = json_decode($answer,true);
           $posArray = [];
           foreach($answer as $key=>$subAnswer){
               $pos = strpos(strtolower($standardAnswer),strtolower($subAnswer));
                if($pos !== false){
                    $posArray[] = $pos;
                }
           }
           //去重
           $result_01 = array_flip($posArray);
           $posArray    = array_keys($result_01);
           return floatval($totalScore) * sizeof($posArray) / $allLines;

       }
       return 0;
    }

    public function autoCorrectHomework($submitId)
    {
         $exerciseList = M()->query("SELECT id,answer,exercises_id,exercises_score,total_score FROM exercises_student_relation WHERE work_id=$submitId");
         foreach($exerciseList as $key => $val){
           $exerciseInfo = M()->query("SELECT right_key,topic_type,exercise_type,types,ordinary_type,answer,answer_select FROM exercises_createexercise WHERE id={$val['exercises_id']} LIMIT 1")[0];

               if($exerciseInfo['exercise_type'] == 2){ //复合题
                   $subExerciseAnswer = M()->query("SELECT right_key,home_topic_type,answer FROM exercises_createexercise WHERE parent_id={$val['exercises_id']}");
                   $correctArray = [];
                   $scoreArray = [];
                   foreach($subExerciseAnswer as $i => $standardRightInfo){
                       if($exerciseInfo['topic_type'] ==1 || $exerciseInfo['topic_type'] ==3 || $exerciseInfo['topic_type'] ==4) //客观题
                       {
                           if($exerciseInfo['topic_type'] ==4 || $exerciseInfo['topic_type'] ==3) //连线
                           {
                               $standardRightInfo['right_key'] = $standardRightInfo['answer'];
                           }
                           $scoreArray[] = $this->__getScore($standardRightInfo['right_key'],json_decode($val['answer'])[$i],$exerciseInfo['topic_type'],$val['total_score']);
                           $correctArray[] = 1;
                       }
                       else
                       {
                           $scoreArray[] = 0;
                           $correctArray[] = 0;
                       }
                   }
                   //write correct status and score
                   $scoreStr = json_encode($scoreArray);
                   $correctStr = json_encode($correctArray);
                   M()->execute("UPDATE exercises_student_relation SET subexercise_correct_status = '$correctStr',subexercise_score=' $scoreStr WHERE id={$val['id']}'");
               }
               else{ //独立题

                   if($exerciseInfo['topic_type'] ==1 || $exerciseInfo['topic_type'] ==3 || $exerciseInfo['topic_type'] ==4 ||
                      ($exerciseInfo['types'] == 2 && ($exerciseInfo['ordinary_type'] == 3) || $exerciseInfo['ordinary_type'] == 4)) //客观题
                   {
                       if($exerciseInfo['types'] != 2) { //普通题
                           if($exerciseInfo['topic_type'] ==4 || $exerciseInfo['topic_type'] ==3) //连线 选择填空
                           {
                               $exerciseInfo['right_key'] = $exerciseInfo['answer'];
                           }
                           $exerciseScore = $this->__getScore($exerciseInfo['right_key'], $val['answer'], $exerciseInfo['topic_type'], $val['total_score']);
                           if($exerciseScore != $val['total_score'])
                               $is_right = HOMEWORK_EXERCISE_WRONG;
                           else
                               $is_right = HOMEWORK_EXERCISE_RIGHT;
                       }
                       else {
                           //语音视频 看课本
                           $is_right = $val['exercises_score'] != 0 ? HOMEWORK_EXERCISE_RIGHT : HOMEWORK_EXERCISE_WRONG;
                           $exerciseScore = $val['exercises_score'];
                       }
                       M()->execute("UPDATE exercises_student_relation SET status = 1,exercises_score= $exerciseScore,is_right =  $is_right WHERE id={$val['id']}");
                   }
                   else if(($exerciseInfo['types'] == 2 && ($exerciseInfo['ordinary_type'] == 1) || $exerciseInfo['ordinary_type'] == 2)){ //语音句子 单词
                       $val['exercises_score'] = round($val['exercises_score']*$val['total_score']/100);
                       M()->execute("UPDATE exercises_student_relation SET exercises_score = {$val['exercises_score']} WHERE id={$val['id']}");
                       $is_right =  $val['exercises_score'] == $val['total_score'] ? HOMEWORK_EXERCISE_RIGHT:HOMEWORK_EXERCISE_WRONG;
                       M()->execute("UPDATE exercises_student_relation SET status = 1,is_right =  $is_right WHERE id={$val['id']}");
                   }
               }
           }
           M()->execute("UPDATE exercises_student_homework 
                         SET 
                             exercises_student_homework.total_score = (SELECT 
                                     SUM(exercises_student_relation.exercises_score)
                                 FROM
                                     exercises_student_relation
                                 WHERE
                                     work_id = $submitId AND status = 1)
                         WHERE
                             exercises_student_homework.id = $submitId");
    }

    public function getExerciseIdBySubmitId($submitId)
    {
        $result = M()->query("SELECT exercises_id FROM exercises_student_relation 
                              JOIN exercises_createexercise ON exercises_createexercise.id = exercises_student_relation.exercises_id
                              WHERE work_id = $submitId");
        return array_column($result,'exercises_id');
    }

    public function getWrongExerciseId($submitId)
    {
        $result = M()->query("SELECT exercises_id FROM exercises_student_relation 
                              JOIN exercises_createexercise ON exercises_createexercise.id = exercises_student_relation.exercises_id
                              where work_id = $submitId AND exercises_createexercise.ordinary_type not in(3,4) AND is_right = ".HOMEWORK_EXERCISE_WRONG." AND exercises_student_relation.status =". HOMEWORK_EXERCISE_CORRECTED);
        return array_column($result,'exercises_id');
    }

    public function getSubmitExerciseInfo($id)
    {
        $result = M()->query("SELECT subject_name,types,ordinary_type,exercise_type,exercises_homwork_relation.exercises_score total_score,exercises_createexercise.answer right_answer,exercises_student_relation.answer FROM exercises_student_relation 
                              JOIN exercises_createexercise ON exercises_createexercise.id = exercises_student_relation.exercises_id
                              JOIN exercises_homwork_relation ON exercises_homwork_relation.exercises_id = exercises_homwork_relation.exercises_id AND exercises_student_relation.homework_id = exercises_homwork_relation.work_id
                              WHERE exercises_student_relation.id=$id LIMIT 1");
        if($result[0]['exercise_type'] == 2) //复合题
        {
            $additionalInfo = M()->query("SELECT subject_name,types,ordinary_type,answer right_answer FROM exercises_createexercise WHERE parent_id=$id");
            $result[0]['subInfo'] =  $additionalInfo;
        }
        return $result[0]?$result[0]:[];
    }

    public function getWrongExerciseIdSubject($submitId)
    {
        $result = M()->query("SELECT exercises_id,exercises_createexercise.subject FROM exercises_student_relation 
                              JOIN exercises_createexercise ON exercises_createexercise.id = exercises_student_relation.exercises_id
                              where work_id = $submitId AND exercises_createexercise.types = 1 AND is_right = " . HOMEWORK_EXERCISE_WRONG . " AND exercises_student_relation.status =" . HOMEWORK_EXERCISE_CORRECTED);
        return $result;
    }


}