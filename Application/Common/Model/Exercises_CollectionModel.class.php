<?php
namespace Common\Model;
use Think\Model;

class Exercises_CollectionModel extends Model{

    public    $model='';
    protected $tableName = 'exercises_collection';
    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);

    }
    //按学科GROUP BY查询该用户收藏的习题数
    public function getMyCollectCountInfo($userId,$role)
    {
       $result = M()->query("SELECT COUNT(1) count,exercises_collection.subject,name FROM exercises_collection 
                   
                   JOIN exercises_createexercise ON exercises_createexercise.id = exercises_id AND is_delete = ".STATE_NORMAL."
                   JOIN exercises_course ON exercises_course.id = exercises_createexercise.subject WHERE teacher_id=$userId AND role=$role GROUP BY subject");
       return $result;
    }

    public function getMyCollectChapterInfo($userId,$role,$courseId)
    {
        $result = M()->query("SELECT chapter id , chapter_name name FROM exercises_collection 
                   JOIN exercises_course ON exercises_course.id = subject
                   JOIN exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercise_id = exercises_createexercise.id
                   JOIN exercises_createexercise ON exercises_createexercise.id = exercises_id AND is_delete = ".STATE_NORMAL."
                   WHERE teacher_id=$userId AND role=$role AND course_id = $courseId GROUP BY chapter");
        return $result;
    }

    public function getMyCollectFestivalInfo($userId,$role,$chapterId)
    {
        $result = M()->query("SELECT festival id , festival_name name FROM exercises_collection 
                   JOIN exercises_course ON exercises_course.id = subject
                   JOIN exercises_textbook_tree_info_createexercise ON exercises_textbook_tree_info_createexercise.exercise_id = exercises_createexercise.id
                   JOIN exercises_createexercise ON exercises_createexercise.id = exercises_id AND is_delete = ".STATE_NORMAL."
                   WHERE teacher_id=$userId AND role=$role AND exercises_textbook_tree_info_createexercise.chapter = $chapterId GROUP BY festival");
        return $result;
    }

    public function getMyCollectExerciseList($userId,$role,$subject,$pageIndex=-1,$pageSize=20,$chapterId=0,$festivalId=0)
    {
        $additionalWhere = '';
        if(!empty($chapterId)){
            $additionalWhere .= " AND exercises_textbook_tree_info_createexercise.chapter = $chapterId ";
        }
        if(!empty($festivalId)){
            $additionalWhere .= " AND exercises_textbook_tree_info_createexercise.festival = $festivalId ";
        }
        if(!empty($subject)){
            $additionalWhere .= " AND exercises_collection.subject = $subject ";
        }
        if($pageIndex != -1)
            $limitStr = ' LIMIT '.($pageIndex-1)*$pageSize.",".$pageSize.' ';

        $result = M()->query("SELECT exercises_id id,
                              exercises_createexercise.subject_name,
                              words,
                              answer,
                              analysis,
                              count_score score,
                              home_topic_type,
                              answer_select,
                              json_html,
                              exercises_collection.id eid,
                              exercises_createexercise.difficulty,
                              right_key,
                              topic_type,1 as collect_status,exercises_course.name FROM exercises_collection 
                   
                   JOIN exercises_createexercise ON exercises_createexercise.id = exercises_id AND is_delete = ".STATE_NORMAL."
                   JOIN exercises_course ON exercises_course.id = exercises_createexercise.home_topic_type
                   WHERE teacher_id=$userId AND role=$role  $additionalWhere $limitStr");
        return $result;
    }

    public function deleteCollect($userId,$role,$exerciseId)
    {
        $map['exercises_id'] = $exerciseId;
        $map['teacher_id'] = $userId;
        $map['role'] =  $role;
        $result = M('exercises_collection')->where($map)->delete();
        return $result !== false;
    }

    public function addCollect($userId,$role,$exerciseId,$subject)
    {
        $map['subject'] = $subject;
        $map['grade'] = 0;
        $map['section'] = 0;
        $map['chapter_section'] = 0;
        $map['knowledge'] = 0;
        $map['teacher_id'] = $userId;
        $map['role'] = $role;
        $map['exercises_id'] = $exerciseId;
        $result = M('exercises_collection')->add($map);
        return $result;
    }

}