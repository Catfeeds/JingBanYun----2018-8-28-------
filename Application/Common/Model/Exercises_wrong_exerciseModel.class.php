<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/9/7
 * Time: 11:42
 */
namespace Common\Model;

use Think\Model;


class Exercises_wrong_exerciseModel extends Model
{
    public $model = '';
    protected $tableName = 'exercises_wrong_exercise';

    public function __construct()
    {
        parent::__construct();
    }

    /*
     *增加一条错题
     */
    public function add($studentId=0,$exerciseId,$courseId)
    {
        if(!intval($studentId)>0 || !intval($exerciseId)>0 ||!intval($courseId)>0 ){
            return false;
        }
        $data['student_id'] = $studentId;
        $data['exercise_id'] = $exerciseId;
        $data['course_id'] = $courseId;
        try {
            $id = M('exercises_wrong_exercise')->add($data);
        }catch(\Exception $e)
        {
            $msg = $e->getMessage();
            if(strpos($msg,'Duplicate')){
                return true;
            }
            return false;
        }
        if(empty($id))
            return false;
        return true;
    }

    //练习数加1
    public function addOnePracticeCount($studentId,$exerciseId)
    {
        $result = M()->execute("UPDATE exercises_wrong_exercise set practice_count = practice_count + 1 WHERE exercise_id in ($exerciseId) AND student_id = $studentId");
        return $result !==  false ;
    }

    //清除错题
    public function clearWrongExercise($studentId,$exerciseId)
    {
        $result = M()->execute("DELETE FROM exercises_wrong_exercise WHERE exercise_id = $exerciseId AND student_id = $studentId");
        return $result !==  false ;
    }

    public function getStudentWrongExerciseCountInfo($userId)
    {
        $result = M()->query("SELECT COUNT(1) count,course_id,name FROM exercises_wrong_exercise 
                   
                   JOIN exercises_createexercise ON exercises_createexercise.id = exercise_id AND is_delete = ".STATE_NORMAL."
                   JOIN exercises_course ON exercises_course.id = exercises_createexercise.subject
                   WHERE student_id=$userId GROUP BY course_id");
        return $result;
    }

    public function getStudentWrongExerciseList($userId,$courseId,$isObjective=0,$pageIndex=1,$pageSize=20)
    {
        $additionalWhere = ' 1=1 ';
        if($isObjective == 1)
        {
            $additionalWhere .= ' AND types=1 AND exercise_type = 1 AND topic_type IN (1,3,4) ';
        }
        if(!empty($courseId)){
            $additionalWhere .= " AND exercises_createexercise.subject=$courseId ";
        }

        if($pageIndex != -1)
        $limitStr = ' LIMIT '.($pageIndex-1)*$pageSize.",".$pageSize.' ';
        $result = M()->query("SELECT exercises_createexercise.id,practice_count,exercises_wrong_exercise.create_at,
                              exercises_createexercise.subject_name,
                              answer,
                              analysis,
                              count_score score,
                              topic_type,
                              home_topic_type,
                              types main_type,
                              ordinary_type subtype,
                              CONCAT(types,',',ordinary_type) category,
                              exercises_createexercise.difficulty,
                              (CASE WHEN exercises_collection.id is NULL THEN 0 ELSE 1 END) collect_status,
                              exercises_collection.id eid,
                              exercises_course.name title_name,
                              right_key,
                              exercises_createexercise.analysis translation,
                              json_html
                              FROM exercises_wrong_exercise 
                              JOIN exercises_createexercise ON exercises_createexercise.id = exercises_wrong_exercise.exercise_id AND student_id=$userId AND is_delete = ".STATE_NORMAL."
                              LEFT JOIN exercises_collection ON exercises_collection.exercises_id = exercises_wrong_exercise.exercise_id AND exercises_collection.role = 3 AND exercises_collection.teacher_id=$userId
                              JOIN exercises_course ON exercises_course.id = exercises_createexercise.home_topic_type
                              WHERE   $additionalWhere $limitStr");
        return $result;
    }

    public function getComments($submitId=0,$exerciseId=0)
    {
        $where = [];
        if(!empty($submitId))
            $where[] = " homework_submit_id = $submitId ";
        if(intval($exerciseId) >=0 ){
            $where[] .= " submit_exercise_id =  $exerciseId";
        }
        if(!empty($where))
         $whereStr = implode(' AND ',$where);
        else
         $whereStr = ' 1=1 ';
        $result = M('')->query("SELECT submit_exercise_id id , comment FROM exercises_homework_comment WHERE $whereStr");
        return $result;
    }

    public function getStudentWrongExerciseChapterInfo($userId,$courseId)
    {
        $result = M()->query("SELECT chapter id , chapter_name name  FROM exercises_wrong_exercise 
                   JOIN exercises_course ON exercises_course.id = course_id
                   JOIN exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercise_id = exercises_createexercise.id 
                   JOIN exercises_createexercise ON exercises_createexercise.id = exercise_id AND is_delete = ".STATE_NORMAL."
                   WHERE student_id=$userId AND course_id = $courseId GROUP BY chapter");
        return $result;
    }

    public function getStudentWrongExerciseFestivalInfo($userId,$chapterId)
    {
        $result = M()->query("SELECT festival id , festival_name name  FROM exercises_wrong_exercise 
                   JOIN exercises_course ON exercises_course.id = course_id
                   JOIN exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercise_id = exercises_createexercise.id 
                   JOIN exercises_createexercise ON exercises_createexercise.id = exercise_id AND is_delete = ".STATE_NORMAL."
                   WHERE student_id=$userId AND exercises_textbook_tree_info_createexercise.chapter = $chapterId GROUP BY festival");
        return $result;
    }


}