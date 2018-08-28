<?php
namespace Common\Model;
use Think\Model;

class Auth_teacher_secondModel extends Model{

    public    $model='';
    protected $tableName = 'auth_teacher_second';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }

    /*
     * 获得某个老师所教的学科年级
     * id  教师ID
     */
    public function getCourseGradeById($id)
    {
      $courseGradeIdNameSecond = $this->model
                                     ->where("auth_teacher_second.teacher_id=$id")
                                     ->join('dict_course on auth_teacher_second.course_id=dict_course.id')
                                     ->join('dict_grade on auth_teacher_second.grade_id=dict_grade.id')
                                     ->field('auth_teacher_second.course_id,auth_teacher_second.grade_id,dict_course.course_name,dict_grade.grade')
                                     ->select();
      return  $courseGradeIdNameSecond;
    }

    /*
     * 获得某个老师所教的学科年级
     * id  教师ID
     */
    public function getVersionCourseGradeById($id)
    {
        $courseGradeIdNameSecond = $this->model
            ->where("auth_teacher_second.teacher_id=$id")
            ->join('dict_course on auth_teacher_second.course_id=dict_course.id')
            ->join('dict_grade on auth_teacher_second.grade_id=dict_grade.id')
            ->field('auth_teacher_second.course_id,auth_teacher_second.grade_id,dict_course.course_name,dict_grade.grade,p_type_id version_id')
            ->select();
        return  $courseGradeIdNameSecond;
    }


    /*
     * 设置某个教师所教的学科年级信息
     * id  教师ID
     * info 学科年级字符串(e.g:1,2,2,3) (courseid,gradeid,courseid...)
     */

    public function updateCourseGradeInfo($id,$info)
    {
       $this->model->where("teacher_id = $id")->delete();

       $secondStr = substr($info,0);
       $pieces = explode(",", $secondStr);
       $count = count($pieces);
       if($count % 2 == 1) //parameter error
        {
         return array(RUN_FAIL,'Info String Error');
        }
       $secondData['teacher_id'] = $id;
       for ($i = 0; $i < $count; $i+=2) {
         $secondData['course_id'] =  $pieces[$i];
         $secondData['grade_id'] =  $pieces[$i+1];
         $secondModel ->add($secondData);
       }
    }

    /**
     *描述：获取所有教师已登录的人数(折线图用)
     */
    public function getAlreadyLoggedInCount($id,$where,$goup){
        $sql = "SELECT
	count(distinct(auth_teacher.id)) access_total,
	auth_teacher_second.course_id,
	dict_course.course_name,
	a.access_time
FROM
	auth_teacher_second
INNER JOIN usertables.access_history_teacher a ON a.teacher_id = auth_teacher_second.teacher_id
INNER JOIN auth_teacher ON auth_teacher.id = a.teacher_id
INNER JOIN dict_schoollist ON dict_schoollist.id = auth_teacher.school_id
INNER JOIN dict_course ON dict_course.id = auth_teacher_second.course_id
WHERE
$where
AND apply_school_status = 1
AND school_id = $id
GROUP BY
	$goup";
        $result = $this->model->query($sql);
        return $result;
    }

    /**
     *描述：获取所有教师已登录的人数2（柱状图用）
     */
    public function getAlreadyLoggedInCount2($where=''){
      $result = M('usertables.access_history_teacher')
                ->field('count(distinct(auth_teacher.id)) access_total,auth_teacher_second.course_id,dict_course.course_name,usertables.access_history_teacher.access_time')
                ->join('auth_teacher_second ON auth_teacher_second.teacher_id = usertables.access_history_teacher.teacher_id')
                ->join('auth_teacher ON auth_teacher.id = usertables.access_history_teacher.teacher_id')
                ->join('dict_schoollist ON dict_schoollist.id = auth_teacher.school_id')
                ->join('dict_course ON dict_course.id = auth_teacher_second.course_id')
                ->where($where)
                ->group('dict_course.id')
                ->select();
        return $result;
    }

    /**
     *描述：获取所有的教师人数
     */
    public function getTotal($where = ''){
        $this->model
            ->field('count(1) count,auth_teacher_second.course_id,dict_course.course_name')
            ->join('auth_teacher on auth_teacher.id=auth_teacher_second.teacher_id')
            ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id')
            ->join('dict_course on dict_course.id=auth_teacher_second.course_id')
            ->where($where)
            ->group('auth_teacher_second.course_id')
            ->select();
    }
}