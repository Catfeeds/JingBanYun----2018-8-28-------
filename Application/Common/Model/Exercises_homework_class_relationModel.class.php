<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/9/7
 * Time: 11:42
 */
namespace Common\Model;

use Think\Model;


class Exercises_homework_class_relationModel extends Model
{
    public $model = '';
    protected $tableName = 'exercises_homwork_class_relation';

    public function __construct()
    {
        parent::__construct();
        include $_SERVER['DOCUMENT_ROOT'].'/Application/Exercise/Conf/const.php';
    }

    /*
     *获取作业涉及的章节
     */
    public function homeWorkByChapterAndFestival($where){
        $sql = "SELECT
                	chapter.tree_point_name chapter_name,
                	festival.tree_point_name festival_name
                FROM
                	exercises_homwork_relation
                JOIN exercises_textbook_tree_info chapter ON chapter.id = exercises_homwork_relation.chapter
                JOIN exercises_textbook_tree_info festival ON festival.id = exercises_homwork_relation.festival
                WHERE
	           $where";
    }

    /*
     *增加班级相关
     */
    public function addInfo($data)
    {
        $id = M($this->tableName)->addAll($data);
        if(empty($id))
            return false;
        return $id;
    }

    public function getHomeworkCountGroupByClassId($condition)
    {
        $result = array();
        if ($condition['role'] == ROLE_STUDENT) {
            $result = M()->query("SELECT biz_class.id,concat(dict_grade.grade,class_name) name,count(1) AS homeworkcount FROM exercises_homwork_class_relation
                              JOIN biz_class ON biz_class.id = exercises_homwork_class_relation.class_id AND biz_class.is_delete = 0 
                              JOIN dict_grade ON biz_class.grade_id = dict_grade.id
                              JOIN biz_class_student ON biz_class_student.class_id = biz_class.id AND biz_class_student.status = " . STUDENT_JOINSTATE_NORMAL . "    
                              JOIN exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id AND exercises_homwork_basics.is_delete=" . STATE_NORMAL .
                " WHERE biz_class_student.student_id=" . $condition['userId'] . " GROUP BY biz_class.id"
                         );
                     } elseif ($condition['role'] == ROLE_PARENT) {
                         $result = M()->query("SELECT
             	biz_class.id,
             	concat(
             		dict_grade.grade,
             		class_name
             	) NAME,
             	count(1) AS homeworkcount
             FROM
             	exercises_homwork_class_relation
             JOIN biz_class ON biz_class.id = exercises_homwork_class_relation.class_id AND biz_class.is_delete = 0
             JOIN dict_grade ON biz_class.grade_id = dict_grade.id
             JOIN biz_class_student ON biz_class_student.class_id = exercises_homwork_class_relation.class_id  AND biz_class_student.status = " . STUDENT_JOINSTATE_NORMAL . "
             JOIN auth_student_parent_contact ON auth_student_parent_contact.student_id  = biz_class_student.student_id AND auth_student_parent_contact.parent_id = " . $condition['userId'] . "
             JOIN exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id AND exercises_homwork_basics.is_delete = 2
             LEFT JOIN auth_student ON biz_class_student.student_id = auth_student.id
             GROUP BY
             	biz_class.id "
            );
        } elseif ($condition['role'] == ROLE_TEACHER) {
            $result = M()->query("SELECT biz_class.id,concat(dict_grade.grade,class_name) name,count(1) AS homeworkcount FROM exercises_homwork_class_relation
                              JOIN biz_class ON biz_class.id = exercises_homwork_class_relation.class_id AND biz_class.is_delete = 0 
                              JOIN dict_grade ON biz_class.grade_id = dict_grade.id  
                              JOIN biz_class_teacher ON biz_class_teacher.class_id = biz_class.id      
                              JOIN exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id AND exercises_homwork_basics.create_user_id = " . $condition['userId'] . " AND exercises_homwork_basics.is_delete=" . STATE_NORMAL .
                ' GROUP BY biz_class.id ORDER BY dict_grade.id ASC'
            );
        }
        return $result;
    }

    private function getConditionJoin($condition)
    {
        $join = 'JOIN exercises_homwork_class_relation ON exercises_homwork_class_relation.work_id = exercises_homwork_basics.id  
                             JOIN biz_class ON biz_class.id = exercises_homwork_class_relation.class_id AND biz_class.is_delete = 0  
                              LEFT JOIN (SELECT COUNT(1) student_count,class_id FROM biz_class_student WHERE status = '.STUDENT_JOINSTATE_NORMAL.' GROUP BY class_id) studentc ON biz_class.id = studentc.class_id';
        if($condition['role'] == ROLE_STUDENT)
        {
            $join .= " JOIN biz_class_student ON biz_class_student.class_id = biz_class.id AND biz_class_student.student_id = {$condition['userId']} AND biz_class_student.status=".STUDENT_JOINSTATE_NORMAL.' ';
            if(isset($condition['hasSubmited'])) {
                if ($condition['hasSubmited'] === false) {
                    $join .= " LEFT JOIN exercises_student_homework ON exercises_student_homework.work_id = exercises_homwork_class_relation.work_id AND exercises_student_homework.class_id = exercises_homwork_class_relation.class_id  AND exercises_student_homework.student_id = {$condition['userId']}  ";
                } else {
                    $join .= " JOIN exercises_student_homework ON exercises_student_homework.work_id = exercises_homwork_class_relation.work_id AND exercises_student_homework.class_id = exercises_homwork_class_relation.class_id AND exercises_student_homework.student_id = {$condition['userId']} ";
                }
            }else{
                $join .= " LEFT JOIN exercises_student_homework ON exercises_student_homework.work_id = exercises_homwork_class_relation.work_id AND exercises_student_homework.class_id = exercises_homwork_class_relation.class_id AND exercises_student_homework.student_id = {$condition['userId']}";
            }
        }
        if($condition['role'] == ROLE_PARENT)
        {
            $join .= " JOIN biz_class_student ON biz_class_student.class_id = exercises_homwork_class_relation.class_id   AND biz_class_student.status = ".STUDENT_JOINSTATE_NORMAL;
            $join .= " JOIN auth_student_parent_contact ON auth_student_parent_contact.student_id  = biz_class_student.student_id AND auth_student_parent_contact.parent_id = {$condition['userId']}  ";

            if(isset($condition['hasSubmited'])) {
                if ($condition['hasSubmited'] === false) {
                    $join .= " LEFT JOIN exercises_student_homework ON exercises_student_homework.work_id = exercises_homwork_class_relation.work_id AND exercises_student_homework.class_id = exercises_homwork_class_relation.class_id  AND exercises_student_homework.student_id = biz_class_student.student_id ";
                } else {
                    $join .= " JOIN exercises_student_homework ON exercises_student_homework.work_id = exercises_homwork_class_relation.work_id AND exercises_student_homework.class_id = exercises_homwork_class_relation.class_id  AND exercises_student_homework.student_id = biz_class_student.student_id ";
                }
            }else{
                $join .= " LEFT JOIN exercises_student_homework ON exercises_student_homework.work_id = exercises_homwork_class_relation.work_id AND exercises_student_homework.class_id = exercises_homwork_class_relation.class_id AND exercises_student_homework.student_id = biz_class_student.student_id  ";
            }


            $join .= " LEFT JOIN auth_student ON biz_class_student.student_id = auth_student.id ";
        }
        return $join;
    }
    private function getConditionWhere($condition)
    {
        $whereStr = "WHERE exercises_homwork_basics.is_delete = ".STATE_NORMAL;
        if($condition['role'] == ROLE_PARENT || $condition['role'] == ROLE_STUDENT){
//            $whereStr .= " AND exercises_homwork_basics.status = 1";
            $whereStr .= " AND exercises_homwork_basics.release_time <= now()";
        }
        foreach($condition as $key=>$val)
        {
            if(false !== $val && null !== $val) {
                if(!is_array($val))
                    $val = str_replace('%','\%',mysql_escape_string($val));
                switch ($key) {
                    case 'studentName':
                        $whereStr .= " AND auth_student.student_name like '%$val%' ";
                        break;
                    case 'classId':
                        if(!empty($val))
                        $whereStr .= " AND biz_class.id in ($val)";
                        break;
                    case 'studentCanSee':
                         if($val == 1)
                             $whereStr .= " AND NOW() > release_time ";
                             break;
                    case 'type':
                        switch($val)
                        {
                            case HOMEWORK_UNPUBLISH:$whereStr .= " AND (NOW() <= release_time OR release_time=0)";
                                break;
                            case HOMEWORK_PUBLISHED:$whereStr .= " AND NOW() > release_time ";
                                break;
                            case HOMEWORK_OVERTIME:$whereStr .= " AND NOW() > deadline AND deadline > 0 ";
                                break;
                            default:break;
                        }
                        break;
                    case 'courseId':
                        $whereStr .= " AND exercises_homwork_basics.course_id = $val";
                        break;
                    case 'gradeId':
                        $whereStr .= " AND exercises_homwork_class_relation.grade_id = $val";
                        break;
                    case 'homeworkType':
                        $whereStr .= " AND exercises_homwork_basics.type = $val";
                        break;
                    case 'keyword':
                        $whereStr .= " AND exercises_homwork_basics.name like '%$val%' ";
                        break;
                    case 'role':
                        switch($val)
                        {
                            case ROLE_TEACHER :
                                $whereStr .= " AND exercises_homwork_basics.create_user_id= ".$condition['userId'] ;
                                break;
                            case ROLE_STUDENT :
                                             if($condition['hasSubmited'] === false)
                                                 $whereStr .= " AND exercises_student_homework.id is null ";
                                             $whereStr.= " AND (biz_class_student.student_id = {$condition['userId']} AND  biz_class_student.status =".STUDENT_JOINSTATE_NORMAL.")";
                                             break;
                            case ROLE_PARENT  :
                                if($condition['hasSubmited'] === false)
                                    $whereStr .= " AND exercises_student_homework.id is null ";
                                if(!empty($condition['studentId']))
                                {
                                    $whereStr .= " AND auth_student_parent_contact.student_id = {$condition['studentId']} ";
                                }
                                break;
                            default:break;
                        }
                        break;
                    case 'release_time':
                        $whereStr .= " AND exercises_homwork_basics.release_time <= '" . date('Y-m-d H:i:s', strtotime($val) + 86399) . "' AND exercises_homwork_basics.release_time >= '" . date('Y-m-d H:i:s', strtotime($val)) . "' " ;//布置时间的筛选项
                        break;
                    case 'classIdBySerach':
                        $whereStr .= " AND biz_class.id = ".$condition['classIdBySerach'] ;
                        break;
                    case 'hasSubmitOrEnded':
                        $whereStr .= " AND (exercises_student_homework.id IS NOT NULL OR (exercises_student_homework.id IS NULL AND NOW() > deadline))";
                        break;
                    case 'hasProcessed':
                        $whereStr .= " AND (exercises_student_homework.correct_status = 1) ";
                        break;
                    case 'status':
                        switch($val){
                            case 1:  $whereStr .= " AND exercises_student_homework.id IS NULL AND NOW() <= deadline ";      //做作业
                                      break;
                            case 2:  $whereStr .= " AND exercises_student_homework.correct_status = 0 ";      //待批改
                                      break;
                            case 3:  $whereStr .= " AND exercises_student_homework.id IS NULL AND NOW() > deadline ";      //已过期
                                      break;
                            case 4:  $whereStr .= " AND exercises_student_homework.correct_status = 1 ";      //作业报告
                                      break;
                        }
                        break;
                    case 'deadline' : $whereStr .= " AND (deadline $val)";
                                       break;
                }
            }
        }
        return $whereStr;
    }

    private function getConditionFields($condition,$groupStr='date(release_time)')
    {
        $innerOrderStr = $leftGroupStr = $rightGroupStr = '';
        if($groupStr == 'date(release_time)') {
            $innerOrderStr = ' ORDER BY release_time DESC,exercises_homwork_basics.id,biz_class.id  ';
            if ($condition['role'] == ROLE_TEACHER) {
                $innerOrderStr = ' ORDER BY exercises_homwork_basics.create_at DESC,exercises_homwork_basics.id,biz_class.id ';
            } else if ($condition['role'] == ROLE_PARENT) {
                $innerOrderStr = ' ORDER BY (CASE WHEN exercises_student_homework.id is NULL AND now()< deadline THEN 0  WHEN exercises_student_homework.id IS NOT null THEN 1 ELSE 2 END)  ASC ,
                                        (CASE WHEN exercises_student_homework.id IS NOT null THEN submit_at ELSE release_time END) DESC,exercises_homwork_basics.id ASC,biz_class.id ASC ,auth_student.id  ASC';
            }
            $leftGroupStr = ' GROUP_CONCAT(';
                $rightGroupStr = ' separator \'///\') ';
        }
        $fields = "(CASE WHEN YEAR(release_time) = YEAR(NOW()) THEN DATE_FORMAT(release_time,'%m月%d日')
                                                  ELSE DATE_FORMAT(release_time,'%Y年%m月%d日') END) date,
                                                  $leftGroupStr exercises_homwork_basics.id $innerOrderStr $rightGroupStr homework_id,
                                                  $leftGroupStr exercises_homwork_basics.type $innerOrderStr $rightGroupStr type,
                                                  $leftGroupStr exercises_homwork_basics.release_time $innerOrderStr $rightGroupStr restime,
                                                  $leftGroupStr IFNULL(biz_class.id,0) $innerOrderStr $rightGroupStr class_id,
                                                  $leftGroupStr IFNULL(CONCAT(grade_name,class_name),'') $innerOrderStr $rightGroupStr class_name,
                                                  $leftGroupStr CASE WHEN NOW() <= release_time THEN '待发布' WHEN NOW() > deadline AND deadline >0 THEN '已截止' WHEN  release_time=0 THEN '待发布'  ELSE '已发布' END $innerOrderStr $rightGroupStr status,
                                                  $leftGroupStr exercises_homwork_basics.release_time $innerOrderStr $rightGroupStr release_time,
                                                  $leftGroupStr exercises_homwork_basics.deadline $innerOrderStr $rightGroupStr org_deadline,
                                                  $leftGroupStr exercises_homwork_basics.name $innerOrderStr $rightGroupStr name,                                                   
                                                  $leftGroupStr IFNULL(studentc.student_count,0) $innerOrderStr $rightGroupStr student_count,
                                                  $leftGroupStr exercises_student_homework.wrong_num $innerOrderStr $rightGroupStr wrong_num,
                                                  $leftGroupStr IFNULL(exercises_homwork_class_relation.finish_count,0) $innerOrderStr $rightGroupStr finish_count,
                                                  $leftGroupStr exercises_homwork_basics.course_id $innerOrderStr $rightGroupStr course_id,
                                                  $leftGroupStr exercises_homwork_basics.course_name $innerOrderStr $rightGroupStr course_name,
                                                  $leftGroupStr (CASE WHEN YEAR(deadline) = YEAR(NOW()) THEN DATE_FORMAT(deadline,'%m月%d日 %H:%i')
                                                  ELSE DATE_FORMAT(deadline,'%Y年%m月%d日 %H:%i') END) $innerOrderStr $rightGroupStr deadline    ";

        if($condition['role'] == ROLE_STUDENT)
        {
            $fields .= " ,$leftGroupStr ROUND(IFNULL(exercises_student_homework.total_score,0)/exercises_homwork_basics.exercises_num) $innerOrderStr $rightGroupStr student_score ";
            $fields .= " ,$leftGroupStr IFNULL(exercises_student_homework.id,0) $innerOrderStr $rightGroupStr student_homeworkid ";
            $fields .= " ,$leftGroupStr IFNULL(exercises_student_homework.work_timeout,0) $innerOrderStr $rightGroupStr duration ";
            $fields .= " ,$leftGroupStr exercises_homwork_basics.exercises_num $innerOrderStr $rightGroupStr exercise_count ";
            $fields .= " ,$leftGroupStr exercises_homwork_basics.subject_num $innerOrderStr $rightGroupStr subject_num ";
            $fields .= " ,$leftGroupStr exercises_homwork_basics.total_score $innerOrderStr $rightGroupStr total_score ";
            $fields .= " ,$leftGroupStr exercises_homwork_basics.create_user_name $innerOrderStr $rightGroupStr teacher_name ";
            $fields .= " ,$leftGroupStr IFNULL(exercises_student_homework.correct_status,0) $innerOrderStr $rightGroupStr correct_status ";
            if($condition['hasSubmited'] === true || isset($condition['hasSubmitOrEnded']) )
            {
                $fields .= " ,$leftGroupStr (CASE WHEN exercises_student_homework.correct_status = 0  THEN '待批改' 
                                                 WHEN exercises_student_homework.id IS NULL AND NOW() > deadline THEN '已过期' 
                                                 ELSE '' END) $innerOrderStr $rightGroupStr correct_info ";
                $fields .= " ,$leftGroupStr (CASE WHEN exercises_student_homework.correct_status = 1 THEN ROUND(IFNULL(exercises_student_homework.total_score,0)/exercises_homwork_basics.exercises_num) ELSE '' END) $innerOrderStr $rightGroupStr sum_score ";
            }
            else if(isset($condition['status'])){
                $fields .= " ,$leftGroupStr (CASE WHEN  exercises_student_homework.id IS NULL AND NOW() <= deadline THEN '做作业'
                                                 WHEN exercises_student_homework.correct_status = 0 THEN '待批改' 
                                                 WHEN exercises_student_homework.id IS NULL AND NOW() > deadline THEN '已过期' 
                                                 WHEN exercises_student_homework.correct_status = 1 THEN '作业报告'
                                                 ELSE '' END) $innerOrderStr $rightGroupStr correct_info ";
                $fields .= " ,$leftGroupStr (CASE WHEN  exercises_student_homework.id IS NULL AND NOW() <= deadline THEN '1'
                                                 WHEN exercises_student_homework.correct_status = 0  THEN '2' 
                                                 WHEN exercises_student_homework.id IS NULL AND NOW() > deadline THEN '3' 
                                                 WHEN exercises_student_homework.correct_status = 1 THEN '4'
                                                 ELSE '' END) $innerOrderStr $rightGroupStr correct_info_num ";
                $fields .= " ,$leftGroupStr (CASE WHEN exercises_student_homework.correct_status = 1 THEN exercises_student_homework.total_score ELSE '' END) $innerOrderStr $rightGroupStr sum_score ";
            }
        }
        if($condition['role'] == ROLE_PARENT)
        {
            $fields .= " ,$leftGroupStr IFNULL(auth_student.id,0) $innerOrderStr $rightGroupStr student_id ";
            $fields .= " ,$leftGroupStr exercises_homwork_basics.exercises_num $innerOrderStr $rightGroupStr exercise_count ";
            $fields .= " ,$leftGroupStr exercises_homwork_basics.create_user_name $innerOrderStr $rightGroupStr teacher_name ";
            $fields .= " ,$leftGroupStr exercises_homwork_basics.total_score $innerOrderStr $rightGroupStr total_score ";
            $fields .= " ,$leftGroupStr (CASE  WHEN exercises_student_homework.id is null  AND release_time >= (unix_timestamp(release_time)+86400)  AND NOW() < deadline  THEN '待完成'
           WHEN  exercises_student_homework.correct_status = 0 AND NOW() <= deadline THEN '待批改' 
           WHEN exercises_student_homework.correct_status = 1   THEN '查看报告' END) $innerOrderStr $rightGroupStr parent_status ";
            $fields .= " ,$leftGroupStr ROUND(IFNULL(exercises_student_homework.total_score,0)/exercises_homwork_basics.exercises_num) $innerOrderStr $rightGroupStr student_score ";
            $fields .= " ,$leftGroupStr IFNULL(exercises_student_homework.id,0) $innerOrderStr $rightGroupStr student_homeworkid ";
            $fields .= " ,$leftGroupStr IFNULL(exercises_student_homework.work_timeout,0) $innerOrderStr $rightGroupStr duration ";
            $fields .= " ,$leftGroupStr IFNULL(auth_student.student_name,'')  $innerOrderStr $rightGroupStr student_name ";

        }
        return $fields;
    }
    private function getConditionOrder($condition=array(),$groupStr)
    {
        $sortStr = ' release_time DESC ';
        if(isset($condition['sort']))
        {
            switch($condition['sort'])
            {
                case TIME_ASC:
                    $sortStr = ' work_timeout ASC,release_time DESC';
                    break;
                case TIME_DESC:
                    $sortStr = ' work_timeout DESC,release_time DESC';
                    break;
                case SCORE_ASC:
                    $sortStr = ' student_score ASC,release_time DESC';
                    break;
                case SCORE_DESC:
                    $sortStr = ' student_score DESC,release_time DESC';
                    break;
            }
        }
        else if($groupStr == 'id')
        {
            $sortStr = ' release_time DESC,exercises_homwork_basics.id DESC,biz_class.id ';
        }
        return $sortStr;
    }
    public function getHomeworkListCount($condition=array(),$groupStr='date(release_time)')
    {
        $join = $this->getConditionJoin($condition);
        $where = $this->getConditionWhere($condition);
        if($groupStr == 'id')
        {
            $groupStr = 'exercises_homwork_basics.id,biz_class.id';
            if($condition['role'] == ROLE_PARENT)
            {
                if(empty($condition['studentId']))
                    $groupStr = 'exercises_homwork_basics.id,biz_class.id,biz_class_student.student_id';
            }
        }
        $result = M()->query("SELECT count(1) count FROM (SELECT              COUNT(1) count                                                  
                                                  FROM exercises_homwork_basics 
                                                  $join
                                                  $where
                                                  GROUP BY $groupStr ) a");
        return $result[0]['count'];
    }
    public function getHomeworkListGroup($condition=array(),$pageIndex=1,$pageSize=4,$groupStr='date(release_time)')
    {
        $stateSetResult = M()->execute('SET SESSION group_concat_max_len=102400;');
        if(false === $stateSetResult)
            return false;
        $fields = $this->getConditionFields($condition,$groupStr);
        $join = $this->getConditionJoin($condition);
        $where = $this->getConditionWhere($condition);
        $sort = $this->getConditionOrder($condition,$groupStr);
        if(intval($pageIndex)<1)
            $pageIndex = 1;
        $startIndex = ($pageIndex-1)*$pageSize;

        $limitStr = '';
        if($pageSize != -1)
            $limitStr = " LIMIT $startIndex,$pageSize ";
        if($groupStr == 'id')
        {
            $groupStr = 'exercises_homwork_basics.id,biz_class.id';
            if($condition['role'] == ROLE_PARENT)
            {
                if(empty($condition['studentId']))
                    $groupStr = 'exercises_homwork_basics.id,biz_class.id,biz_class_student.student_id';
            }
        }
        $result = M()->query("SELECT              $fields                                                   
                                                  FROM exercises_homwork_basics 
                                                  $join
                                                  $where
                                                  GROUP BY $groupStr ORDER BY $sort $limitStr");//echo M()->getLastSql();die;
        return $result;
    }

    public function getHomeworkCountGroupByCourse($condition)
    {
        $fields = ' exercises_homwork_basics.course_id,exercises_homwork_basics.course_name,COUNT(DISTINCT exercises_student_homework.id) AS count';
        $join = $this->getConditionJoin($condition);
        $where = $this->getConditionWhere($condition);
        $result = M()->query("SELECT              $fields                                                   
                                                  FROM exercises_homwork_basics 
                                                  $join
                                                  $where
                                                  GROUP BY exercises_homwork_basics.course_id  ");
        return $result;
    }

    public function getUnFinishHomeworkCount($studentId)
    {
        $result = M()->query("SELECT COUNT(1) count FROM exercises_student_homework WHERE student_id = $studentId");
        return $result[0]['count'];
    }

    /*
     * 获取学生作业中的习题是否全部已评分完毕，如是，则返回总分
     */
    public function getHomeworkIsAllCorrected($submitId)
    {
        $result = M()->query("SELECT 1 FROM exercises_student_relation WHERE work_id=$submitId AND status = 0");
        if(!empty($result))
            return false; //仍有习题未评分
        $result = M()->query("SELECT SUM(exercises_score) score FROM exercises_student_relation WHERE work_id=$submitId ");
        return $result[0]['score']; 
    }
    public function updateCorrectStatusPoint($submitId,$totalScore,$teacherId,$teacherName,$teacherMessage='')
    {
        $data['total_score'] = $totalScore;
        $data['correct_status'] = 1;
        $data['teacher_message'] = $teacherMessage;
        $data['correct_teacher_id'] = $teacherId;
        $data['correct_teacher_name'] = $teacherName;
        $updateStr = 'correct_at = NOW()';
        foreach($data as $key=>$val)
        {
            if(is_string($val))
                $updateStr .= ",$key = '$val'";
            else if(is_numeric($val))
                $updateStr .= ",$key = $val";
        }
        $result = M()->execute("UPDATE exercises_student_homework SET $updateStr WHERE id=$submitId");
        $result &= M()->execute("UPDATE exercises_student_homework SET wrong_num = (SELECT SUM(is_right=2) FROM exercises_student_relation WHERE work_id = $submitId)  WHERE id=$submitId");
        if(false !== $result)
            return true;
        return false;
    }


    /*
     * 更新作业完成数
     */
    public function updateHomeworkFinishCount($homeworkId,$classId)
    {
        $result = M()->execute("UPDATE exercises_homwork_class_relation 
                              JOIN (SELECT COUNT(1) count FROM exercises_student_homework WHERE work_id=$homeworkId AND class_id = $classId ) a  
                               ON 1=1
                              SET finish_count = a.count WHERE exercises_homwork_class_relation.class_id = $classId AND exercises_homwork_class_relation.work_id = $homeworkId");
        return $result !== false;
    }

//    public function getHomeworkListByClass($condition){
//        $result = M()->query("SELECT exercises_homwork_basics.name FROM exercises_homwork_class_relation
//                              JOIN biz_class ON biz_class.id = exercises_homwork_class_relation.class_id AND biz_class.is_delete = 0
//                              JOIN dict_grade ON biz_class.grade_id = dict_grade.id
//                              JOIN biz_class_teacher ON biz_class_teacher.class_id = biz_class.id
//                              JOIN exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id AND exercises_homwork_basics.create_user_id = " . $condition['userId'] . " AND exercises_homwork_basics.is_delete=" . STATE_NORMAL .
//            ' ORDER BY dict_grade.id ASC'
//        );
//        return $result;
//
//
//    }

    public function getHomeworkListByClass($where = [], $field, $pageIndex = 1, $pageSize = 20, $join = '', $order = '',$havingwhere)
    {
        $result = M('exercises_homwork_class_relation')
            ->field($field)
            ->join('biz_class_teacher ON biz_class_teacher.class_id = exercises_homwork_class_relation.class_id')
            ->join('biz_class ON biz_class.id = exercises_homwork_class_relation.class_id')
            ->join('dict_grade ON biz_class.grade_id = dict_grade.id')
            ->join('exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id')
            ->join('left join exercises_homwork_relation ON exercises_homwork_relation.work_id = exercises_homwork_basics.id')
            ->join('left join biz_class_student ON biz_class_student.class_id = biz_class.id and biz_class_student.status=2')
            ->join('left join exercises_student_homework ON exercises_student_homework.work_id = exercises_homwork_basics.id AND exercises_student_homework.class_id=exercises_homwork_class_relation.class_id')
            ->where($where)
            ->order($order)
            ->page($pageIndex, $pageSize)
            ->group("exercises_homwork_basics.id,biz_class.id")
            ->having($havingwhere)
            ->select();
        //echo M()->getLastSql();die;
        return $result;
    }

    public function getHomeworkSearchWhere($where = [], $field, $pageIndex = 1, $pageSize = 20, $join = '', $order = '',$havingwhere)
    {
        $result = M('exercises_homwork_class_relation')
            ->field($field)
            ->join('biz_class ON biz_class.id = exercises_homwork_class_relation.class_id')
            ->join('dict_grade ON biz_class.grade_id = dict_grade.id')
            ->join('exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id')
            ->join('left join exercises_homwork_relation ON exercises_homwork_relation.work_id = exercises_homwork_basics.id')
            ->join('left join biz_class_student ON biz_class_student.class_id = biz_class.id and biz_class_student.status=2')
            ->join('left join exercises_student_homework ON exercises_student_homework.work_id = exercises_homwork_basics.id')
            ->where($where)
            ->order($order)
            ->group("exercises_homwork_class_relation.grade_id,exercises_homwork_class_relation.class_id")
            ->having($havingwhere)
            ->select();
        return $result;
    }

    //根据学科年级还有作业id获取作业下面所有学生
    public function getStudentHomeWorkList($where = [], $field, $pageIndex = 1, $pageSize = 20, $join = '', $order = '',$havingwhere,$classId) {
        $result = M('exercises_homwork_class_relation')
            ->field($field)
            ->join('biz_class ON biz_class.id = exercises_homwork_class_relation.class_id')
            ->join('biz_class_student ON biz_class_student.class_id = biz_class.id and biz_class_student.status=2')
            ->join('auth_student ON auth_student.id = biz_class_student.student_id')
            ->join('exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id')
            ->join('left join exercises_student_homework ON exercises_student_homework.student_id=auth_student.id'." and exercises_student_homework.work_id=".$where['exercises_homwork_class_relation.work_id']." and exercises_student_homework.class_id=".$classId)
            ->where($where)
            ->order($order)
            ->page($pageIndex,$pageSize)
            ->select();
        return $result;
    }

    public function getStudentHomeWorkListGroup($where = [], $field, $pageIndex = 1, $pageSize = 20, $join = '', $order = '',$havingwhere,$classId) {
        $result = M('exercises_homwork_class_relation')
            ->field($field)
            ->join('biz_class ON biz_class.id = exercises_homwork_class_relation.class_id')
            ->join('biz_class_student ON biz_class_student.class_id = biz_class.id and biz_class_student.status=2')
            ->join('auth_student ON auth_student.id = biz_class_student.student_id')
            ->join('exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id')
            ->join('left join exercises_student_homework ON exercises_student_homework.student_id=auth_student.id'." and exercises_student_homework.work_id=".$where['exercises_homwork_class_relation.work_id']." and exercises_student_homework.class_id=".$classId)
            ->join('left join exercises_student_relation ON exercises_student_relation.work_id=exercises_student_homework.id')
            ->where($where)
            ->order($order)
            ->group("exercises_student_relation.exercises_id")
            ->select();

        return $result;
    }

    //根据学科年级还有作业id获取作业下面所有学生
    public function push($where = [], $field, $pageIndex = 1, $pageSize = 20, $join = '', $order = '',$havingwhere,$classId) {
        $result = M('exercises_homwork_class_relation')
            ->field($field)
            ->join('biz_class ON biz_class.id = exercises_homwork_class_relation.class_id')
            ->join('biz_class_student ON biz_class_student.class_id = biz_class.id and biz_class_student.status=2')
            ->join('auth_student ON auth_student.id = biz_class_student.student_id')
            ->join('exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id')
            ->join('left join exercises_student_homework ON exercises_student_homework.student_id=auth_student.id'." and exercises_student_homework.work_id=".$where['exercises_homwork_class_relation.work_id']." and exercises_student_homework.class_id=".$classId)
            //->join('left join exercises_student_relation ON exercises_student_relation.work_id=exercises_student_homework.id')
            ->where($where)
            ->order($order)
            ->select();
        return $result;
    }

    //获取题号
    public function getStudentHomeWorkListEx($where,$field) {
        $result = M('exercises_student_relation')
            ->join("exercises_createexercise ON exercises_createexercise.id = exercises_student_relation.exercises_id")
            ->where($where)
            ->field($field)
            ->group('exercises_id')
            ->select();

        return $result;
    }

    //获取组卷
    //获取试卷列表
    public function getPaperListView($where,$field, $pageIndex = 1, $pageSize = 20) {
        $res    = M('exercises_create_paper')
            ->join("left join exercises_course_type ON exercises_course_type.id = exercises_create_paper.subject")
            ->join("left join dict_grade ON dict_grade.id = exercises_create_paper.grade")
            ->field($field)
            ->page($pageIndex, $pageSize)
            ->where($where)
            ->order("exercises_create_paper.create_at desc")
            ->select();
        return $res;
    }

    public function getyear() {
        $res    = M('exercises_create_paper')
            ->field("year")
            ->group('year')
            ->select();
        return $res;
    }


    //获取题号
    public function studentCorrectHomeWorkSituation($where,$field) {
        $result = M('exercises_student_relation')
            ->where($where)
            ->field($field)
            ->group('exercises_id')
            ->select();

        return $result;
    }

    //
    public function getMyQuestionBankList($condition) {
        $result = M('exercises_homwork_class_relation')
            ->field("biz_class.id,count(distinct exercises_id) as exercises_count,biz_class.name,dict_grade.grade,biz_class.grade_id")
            ->join('biz_class_teacher ON biz_class_teacher.class_id = exercises_homwork_class_relation.class_id')
            ->join('biz_class ON biz_class.id = exercises_homwork_class_relation.class_id')
            ->join('dict_grade ON biz_class.grade_id = dict_grade.id')
            ->join('exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id')
            ->join('left join exercises_homwork_relation ON exercises_homwork_relation.work_id = exercises_homwork_basics.id')
            ->where($condition)
            ->group("biz_class.id")
            ->select();
        return $result;
    }

    public function getMyQuestionMaterialBankList($condition){
        $result = M('exercises_homwork_class_relation')
            ->field("biz_class.id,count(distinct exercises_id) as exercises_count,biz_class.name,dict_grade.grade,biz_class.id,group_concat(distinct exercises_homwork_relation.chapter) as chapter_id,tree_point_name")
            ->join('biz_class_teacher ON biz_class_teacher.class_id = exercises_homwork_class_relation.class_id')
            ->join('biz_class ON biz_class.id = exercises_homwork_class_relation.class_id')
            ->join('dict_grade ON biz_class.grade_id = dict_grade.id')
            ->join('exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id')
            ->join('left join exercises_homwork_relation ON exercises_homwork_relation.work_id = exercises_homwork_basics.id')
            ->join('left join exercises_textbook_tree_info ON exercises_textbook_tree_info.id = exercises_homwork_relation.chapter and exercises_textbook_tree_info.level=1')
            ->where($condition)
            ->group("exercises_homwork_relation.chapter")
            ->select();

        return $result;
    }


    public function getMyQuestionBankErrorList($condition) {
        $result = M('exercises_homwork_class_relation')
            ->field("biz_class.id,count(distinct exercises_homwork_relation.exercises_id) as exercises_count,biz_class.name,dict_grade.grade")
            ->join('biz_class_teacher ON biz_class_teacher.class_id = exercises_homwork_class_relation.class_id')
            ->join('biz_class ON biz_class.id = exercises_homwork_class_relation.class_id')
            ->join('dict_grade ON biz_class.grade_id = dict_grade.id')
            ->join('exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id')
            ->join('exercises_homwork_relation ON exercises_homwork_relation.work_id = exercises_homwork_basics.id')
            ->join('exercises_student_relation ON exercises_student_relation.exercises_id = exercises_homwork_relation.exercises_id')
            ->where($condition)
            ->group("biz_class.id")
            ->select();
        return $result;
    }

    public function getMyQuestionMaterialBankErrorList($condition){
        $result = M('exercises_homwork_class_relation')
            ->field("biz_class.id,count(distinct exercises_homwork_relation.exercises_id) as exercises_count,biz_class.name,dict_grade.grade,biz_class.id,group_concat(distinct exercises_homwork_relation.chapter) as chapter_id,tree_point_name")
            ->join('biz_class_teacher ON biz_class_teacher.class_id = exercises_homwork_class_relation.class_id')
            ->join('biz_class ON biz_class.id = exercises_homwork_class_relation.class_id')
            ->join('dict_grade ON biz_class.grade_id = dict_grade.id')
            ->join('exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id')
            ->join('exercises_homwork_relation ON exercises_homwork_relation.work_id = exercises_homwork_basics.id')
            ->join('exercises_student_relation ON exercises_student_relation.exercises_id = exercises_homwork_relation.exercises_id')
            ->join('left join exercises_textbook_tree_info ON exercises_textbook_tree_info.id = exercises_homwork_relation.chapter and exercises_textbook_tree_info.level=1')
            ->where($condition)
            ->group("exercises_homwork_relation.chapter")
            ->select();

        return $result;
    }

    public function getMyExerciseBankList($condition,$pageIndex = 1, $pageSize = 20) {
        $result = M('exercises_homwork_class_relation')
            ->field("exercises_homwork_relation.exercises_id,exercises_createexercise.*")
            ->join('biz_class_teacher ON biz_class_teacher.class_id = exercises_homwork_class_relation.class_id')
            ->join('biz_class ON biz_class.id = exercises_homwork_class_relation.class_id')
            ->join('dict_grade ON biz_class.grade_id = dict_grade.id')
            ->join('exercises_homwork_basics ON exercises_homwork_basics.id = exercises_homwork_class_relation.work_id')
            ->join('left join exercises_homwork_relation ON exercises_homwork_relation.work_id = exercises_homwork_basics.id')
            ->join('left join exercises_createexercise ON exercises_createexercise.id = exercises_homwork_relation.exercises_id')
            ->join('exercises_student_relation ON exercises_student_relation.exercises_id = exercises_homwork_relation.exercises_id')
            ->where($condition)
            ->page($pageIndex, $pageSize)
            ->group("exercises_homwork_relation.exercises_id")
            ->select();
        return $result;
    }

    public function getAnalysis($ids=[])
    {
        if(empty($ids))
            return [];
        $idString = implode(',',$ids);
        $result = M()->query("SELECT  id eid,
                             json_html,
                             
                            (CASE WHEN exercise_type = 1 THEN analysis ELSE 
                            (SELECT GROUP_CONCAT(analysis separator '<br>') analysis FROM exercises_createexercise WHERE parent_id = eid GROUP BY parent_id) END) analysis 
                    FROM exercises_createexercise WHERE id IN ($idString)");
        return $result;
    }
}