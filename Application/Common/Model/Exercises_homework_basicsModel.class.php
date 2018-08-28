<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/9/7
 * Time: 11:42
 */
namespace Common\Model;

use Think\Model;


class Exercises_homework_basicsModel extends Model
{
    public $model = '';
    protected $tableName = 'exercises_homwork_basics';

    public function __construct()
    {
        parent::__construct();
    }

    /*
     *增加一条作业
     */
    public function addOneHomework($data)
    {
        $id = M('exercises_homwork_basics')->add($data);
        if(empty($id))
            return false;
        return $id;
    }


    /*
     * 获取作业基础信息
     */
    public function getHomeworkBaseInfo($homeworkId)
    {
        $result = M()->query("SELECT *,
                               (CASE WHEN YEAR(release_time) = YEAR(NOW()) THEN DATE_FORMAT(release_time,'%m月%d日 %H:%i')
                              ELSE DATE_FORMAT(release_time,'%Y年%m月%d日 %H:%i') END) release_time_c,
                              (CASE WHEN YEAR(deadline) = YEAR(NOW()) THEN DATE_FORMAT(deadline,'%m月%d日 %H:%i')
                              ELSE DATE_FORMAT(deadline,'%Y年%m月%d日 %H:%i') END) end_time_c
                              FROM exercises_homwork_basics WHERE id=$homeworkId");
        return $result[0];
    }

    //根据作业id查询所属章节
    public function getHomeworkIdgetfk( $id ) {
        $list = M('exercises_homwork_relation')
            ->where("exercises_homwork_relation.work_id=$id")
            ->join('exercises_textbook_tree_info on exercises_textbook_tree_info.id=exercises_homwork_relation.chapter')
            ->join('exercises_textbook_tree_info as ett on ett.id=exercises_homwork_relation.festival')
            ->field("exercises_textbook_tree_info.tree_point_name as fname,ett.tree_point_name as sname")
            ->group("exercises_homwork_relation.festival")
            ->select();

        return $list;
    }

    //习题关联班级
    /**
     * @return string
     */
    public function addEhcr($data)
    {
        $gradewhere['id'] = $data['grade_id'];
        $grade_name = M('dict_grade')->where($gradewhere)->find();

        $classwhere['id'] = $data['class_id'];
        $class_name = M('biz_class')->where($classwhere)->find();

        $data['grade_name'] = $grade_name['grade'];
        $data['class_name'] = $class_name['name'];

        $id = M('exercises_homwork_class_relation')->add($data);

        if(empty($id))
            return false;
        return $id;
    }

    //作业习题关联表
    public function addExercisesList($selectedChapterExercise,$mid){
        if (!empty($selectedChapterExercise)) {
            $lock = true;
            foreach ($selectedChapterExercise as $k=>$v) {
                if (!empty($v)) {
                    $listdata = explode(",",$k);
                    $adddata['work_id'] = $mid;
                    $adddata['subject'] = $listdata[0];
                    $adddata['grade'] = $listdata[1];
                    $adddata['section'] = $listdata[2];
                    $adddata['chapter'] = $listdata[3];
                    $adddata['festival'] = $listdata[4];

                    foreach ($v as $ek=>$ev) {
                        $exwhere['id'] =  $ev['exercise_id'];
                        $adddata['exercises_id'] = $ev['exercise_id'];
                        $count_score = M('exercises_createexercise')->where( $exwhere )->find();
                        $adddata['exercises_score'] = $count_score['count_score'];
                        $aid = M('exercises_homwork_relation')->add($adddata);
                        if ($aid ==false) {
                            $lock = false;
                            break;
                        }
                    }
                }
            }

            if( $lock == true) {
                return true;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }

    //复制习题
    public function addCopyExercisesList($homeworkId,$mid){
        $where['work_id'] = $homeworkId;
        $list = M('exercises_homwork_relation')->where($where)->select();
        $lock = true;
        if (!empty($list)) {
            foreach ($list as $k=>&$v) {
                $v['work_id'] = $mid;
                unset($v['id']);
                $v['create_at'] = date("Y-m-d H:i:s",time());
                $aid = M('exercises_homwork_relation')->add($v);
                if ($aid ==false) {
                    $lock = false;
                    break;
                }
            }
        }
        if( $lock == true) {
            return true;
        } else {
            return false;
        }

    }

    //根据作业id获取作业所有习题
    public function getExercises($homeworkId) {
        $map['exercises_homwork_relation.work_id'] = $homeworkId;
        $list = M('exercises_homwork_relation')
            ->join('exercises_createexercise on exercises_createexercise.id=exercises_homwork_relation.exercises_id')
            ->field("exercises_createexercise.id,exercises_createexercise.ordinary_type as category,exercises_createexercise.words as name,exercises_createexercise.analysis as translation,exercises_createexercise.subject_name as url,exercises_createexercise.count_score as point")
            ->where($map)
            ->select();
        return $list;

    }

    public function getExercisesIdList($exerciseId) {
        if (!empty($exerciseId)) {

            $map['id'] = array( 'in',explode(';',$exerciseId) );
            $list = M('exercises_createexercise')
                ->field("id,ordinary_type as category,words as name,analysis as translation,subject_name as url,count_score as point")
                ->where($map)
                ->select();
            return $list;
        } else {
            return null;
        }


    }

    //布置作业
    public function setUpdateHomework($homeworkId,$arrangeTime){
        $where['id'] = $homeworkId;
        $map['release_time'] = date("Y-m-d H:i:s");
        $map['deadline'] = date("Y-m-d H:i:s",strtotime($arrangeTime));
        $map['status'] = 1;

        $id = M('exercises_homwork_basics')->where( $where )->save( $map );

        if ($id !== false) {
            return $map;
        } else {
            return false;
        }

    }

    public function updateHomeworkAuth($sourceTeacherId,$destTeacherId,$classId,$courseId){
        $courseAddStr = '';
        if($courseId != 0){
            $courseAddStr = " AND course_id = $courseId ";
        }
         $result = M()->execute("
         UPDATE exercises_homwork_basics JOIN exercises_homwork_class_relation ON
         exercises_homwork_class_relation.work_id = exercises_homwork_basics.id AND create_user_id = $sourceTeacherId AND class_id =  $classId $courseAddStr
         JOIN auth_teacher ON auth_teacher.id = $destTeacherId 
         SET exercises_homwork_basics.create_user_id = $destTeacherId,create_user_name = auth_teacher.name
         ");
        return $result !== false;
    }

}