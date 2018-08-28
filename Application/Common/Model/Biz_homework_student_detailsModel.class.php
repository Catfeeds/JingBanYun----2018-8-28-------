<?php
namespace Common\Model;
use Think\Model;

class Biz_homework_student_detailsModel extends Model{

    public    $model='';
    protected $tableName = 'biz_homework_student_details';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }

    public function isHomeworkSubmitted($studentId,$homeworkId)
    {
        $where['homework_id'] = $homeworkId;
        $where['student_id'] = $studentId;
        return $this->model->where($where)->field('1')->find();
    }

    //根据班级id和学生id获取该学生的学习轨迹
    public function getHomeworkScoreByStudent( $where ) {
        $Model = M('biz_homework_student_details');

        $result = $Model
            ->join('biz_homework on biz_homework.id=biz_homework_student_details.homework_id')
            ->join('biz_homework_score_details on biz_homework_score_details.student_id=biz_homework_student_details.student_id and biz_homework_score_details.homework_id=biz_homework.id')
            ->join('auth_student on auth_student.id=biz_homework_student_details.student_id')
            ->field("biz_homework.id as homework_id,biz_homework.homework_name,biz_homework_student_details.points,auth_student.student_name,auth_student.id as student_id")
            ->where($where)
            ->order('biz_homework.create_at asc')->select();

        return $result;
    }

    //根据学生的id获取学生的错题集
   /* public function getStudentErrorHomework( $studentId,$classId ) {
        $check['biz_homework_score_details.student_id'] = $studentId;
        $check['biz_homework_score_details.flag'] = array('neq', 1);
        $check['biz_homework.class_id'] = $classId;
        $Model = M('biz_homework_score_details');
        $result = $Model
            ->join("biz_exercise_library on biz_exercise_library.id=biz_homework_score_details.question_org_id")
            ->join("biz_homework on biz_homework.id=biz_homework_score_details.homework_id")
            ->field("biz_homework_score_details.*,biz_exercise_library.questions,biz_exercise_library.chapter_id,biz_exercise_library.id as bid,biz_homework.homework_name as homework,biz_homework.create_at")
            ->where($check)
            ->order('biz_homework.create_at desc')
            ->select();
        return $result;
    }*/

    public function getStudentErrorHomework( $studentId,$classId ) {
        $check['biz_homework_score_details.student_id'] = $studentId;
        $check['biz_homework_score_details.flag'] = array('neq', 1);
        $check['biz_homework.class_id'] = $classId;

        $Model = M('biz_homework');
        $result = $Model
            ->join("biz_homework_score_details on biz_homework_score_details.homework_id=biz_homework.id")
            ->join("biz_exercise_library on biz_exercise_library.id=biz_homework_score_details.question_org_id")
            ->field("biz_homework_score_details.*,biz_exercise_library.questions,biz_exercise_library.chapter_id,biz_exercise_library.id as bid,biz_homework.homework_name as homework,biz_homework.create_at")
            ->where($check)
            ->order('biz_homework.create_at desc')
            ->group('biz_homework_score_details.homework_id')
            ->select();
        return $result;
    }

    //获取作业详情
    public function getHomeWorkD( $where ) {
        $res =  M('biz_homework')->where( $where )->find();
        return $res;
    }

    //获取作业批改的学生情况
    /**
     * @return \Model|string|Model
     */
    public function getHomeWorkBhsd( $where ) {
        $res =  M('biz_homework_student_details')->where( $where )->find();
        return $res;
    }

    //根据作业id获取章节
    public function getChapterInfo( $where ) {
        $homework_exercise_model=M('biz_homework_exercise');
        $chapter_result=$homework_exercise_model->where($where)
            ->join('biz_exercise_library_chapter chapter on chapter.id=biz_homework_exercise.chapter_id')
            ->group('chapter.id')
            ->field('chapter.chapter,chapter.festival')
            ->select();
        return $chapter_result;
    }

}