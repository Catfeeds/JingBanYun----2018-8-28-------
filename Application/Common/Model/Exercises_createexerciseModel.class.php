<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/9/7
 * Time: 11:42
 */
namespace Common\Model;
use Think\Model;



class Exercises_createexerciseModel extends Model
{
    public    $model='';
    protected $tableName = 'exercises_createexercise';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }
    /*
     * 删除习题
     */
    public function deleteExercise($id)
    {
       $id = intval($id);
       $result = M()->execute("UPDATE exercises_createexercise SET is_delete = ".STATE_DELETED ." WHERE id=$id");
       if(false === $result)
           return false;
       return true;
    }

    public function getExercisesGroupCount($groupByCondition)
    {
        $sql = '';
        switch($groupByCondition)
        {
            case 'publisher':
                             $sql = "SELECT version_name name,ifnull(count,0) count FROM exercises_textbook_version LEFT JOIN (SELECT version_id,COUNT(DISTINCT exercises_createexercise_id) count FROM exercises_textbook_tree_info_createexercise GROUP BY version_id) a ON a.version_id = exercises_textbook_version.id";
                             break;
            case 'grade':$sql = "SELECT grade name,ifnull(count,0) count FROM dict_grade LEFT JOIN (SELECT grade_id,ifnull(COUNT(DISTINCT exercises_createexercise_id),0) count FROM exercises_textbook_tree_info_createexercise GROUP BY grade_id) a ON a.grade_id = dict_grade.id";
                          break;
            case 'course':
                          $sql = "SELECT name,ifnull(count,0) count FROM exercises_course LEFT JOIN (SELECT course_id,ifnull(COUNT(DISTINCT exercises_createexercise_id),0) count FROM exercises_textbook_tree_info_createexercise GROUP BY course_id) a ON a.course_id = exercises_course.id WHERE parent_id=0";
                          break;
            case 'difficulty':
                          $sql = "SELECT difficulty name,ifnull(COUNT(DISTINCT id),0) count FROM exercises_createexercise WHERE difficulty is not NULL GROUP BY difficulty ";
                          break;

        }
        $result = M()->query($sql." HAVING COUNT <> 0");
        return $result;
    }

    /*
     * 设置习题难度
     */
    public function setDifficulty($id,$difficulty)
    {
        $result = M()->execute("UPDATE exercises_createexercise set difficulty=$difficulty WHERE id=$id");
        return $result !== false;
    }

    public function getExerciseDifficulty($id)
    {
        $result = M()->query("SELECT difficulty FROM exercises_createexercise WHERE id=$id LIMIT 1");
        return $result[0]['difficulty'];
    }

    public function getExerciseKnowledge($id)
    {
        $result = M()->query("SELECT difficulty FROM exercises_createexercise WHERE id=$id LIMIT 1");
        return $result[0]['difficulty'];
    }

    public function getExerciseInfo($id)
    {
        $result = M()->query("SELECT * FROM exercises_createexercise WHERE id=$id LIMIT 1");
        return $result[0];
    }

    public function getExerciseIsSubjective($id)
    {
        $result = M()->query("SELECT * FROM exercises_createexercise WHERE id=$id LIMIT 1")[0];
        if($result['types'] == 1 && ($result['topic_type'] == 2 || $result['topic_type'] == 5 || $result['topic_type'] == 6))
            return true;
        else
            return false;
    }
    //TODO:习题反馈表查询操作
    public function getAllDataByErrorCorrection($page = 0,$where='1=1'){
        $count = M('error_correction')
            ->query("SELECT
	NAME,
	telephone,
	a.content,
	from_unixtime(a.create_at) time,
	a.exercise_id,
	a.flag_type,
	status
FROM
	(
		SELECT
		    error_correction.status,
			error_correction.exercise_id,
			error_correction.flag_type,
			error_correction.content,
			error_correction.create_at,
			name,
			telephone
		FROM
			auth_teacher
		JOIN error_correction ON error_correction.user_id = auth_teacher.id and error_correction.role=2
		WHERE
			$where
		UNION
			SELECT
			    error_correction.status,
				error_correction.exercise_id,
				error_correction.flag_type,
			error_correction.content,
			error_correction.create_at,
				student_name name,
				telephone
			FROM
				auth_student
			JOIN error_correction ON error_correction.user_id = auth_student.id and error_correction.role=3
			HAVING 
				$where
	) a 
");
        $result['data'] = M('error_correction')
            ->query("SELECT
	NAME,
	id,
	telephone,
	a.content,
	from_unixtime(a.create_at) time,
	a.exercise_id,
	a.flag_type,
	status
FROM
	(
		SELECT
		    error_correction.status,
		    error_correction.id,
			error_correction.exercise_id,
			error_correction.flag_type,
			error_correction.content,
			error_correction.create_at,
			name,
			telephone
		FROM
			auth_teacher
		JOIN error_correction ON error_correction.user_id = auth_teacher.id and error_correction.role=2
		WHERE
			$where
		UNION
			SELECT
			    error_correction.status,
			    error_correction.id,
				error_correction.exercise_id,
				error_correction.flag_type,
			error_correction.content,
			error_correction.create_at,
				student_name name,
				parent_tel telephone
			FROM
				auth_student
			JOIN error_correction ON error_correction.user_id = auth_student.id and error_correction.role=3
			HAVING 
				$where
	) a LIMIT $page,20
");
        $result['count'] = count($count);
        return $result;
    }
    //TODO:习题反馈表修改操作
    public function saveByErrorCorrection($where = '',$data= ''){
        $result = M('error_correction')
           ->where($where)
            ->save($data);
        return $result;
    }

    //TODO:习题反馈表查询操作
    public function selectByErrorCorrection($where){
        $result = M('error_correction')
            ->where($where)
            ->find();
        return $result;
    }
}
