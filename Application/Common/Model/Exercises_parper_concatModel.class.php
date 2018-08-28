<?php
namespace Common\Model;
use Think\Model;

class Exercises_parper_concatModel extends Model{

    public function __construct(){
        parent::__construct();
    }

    /*
     * 获取习题所属试卷ID
     */
    public function getPaperIdByQuestionId($questionId)
    {
         $questionId = intval($questionId);
         $result = M()->query("SELECT paper_id FROM exercises_parper_concat WHERE exercise_id = $questionId LIMIT 1");
         return $result[0];
    }

    /*
     * 获取试卷习题数量
     */
    public function getPaperQuestionCount($paperId,$status=0)
    {
        $paperId = intval($paperId);
        $status = intval($status);
        $where = " paper_id = $paperId ";
        if($status != 0)
        $where .= " AND exercises_createexercise.status = $status ";
        $where .= " AND exercises_createexercise.is_delete = ".STATE_NORMAL;
        $result = M()->query("SELECT count(1) AS count FROM exercises_parper_concat JOIN exercises_createexercise ON exercises_createexercise.id = exercises_parper_concat.exercise_id WHERE $where LIMIT 1");
        return $result[0]['count'];
    }

    /*
     * 获取试卷所包含习题ID
     */
    public function getQuestionIdsByPaperId($paperId)
    {
        $where = " paper_id = $paperId ";
        $result = M()->query("SELECT exercise_id id FROM exercises_parper_concat WHERE $where ");
        return $result;
    }

    //入库表关系
    public function addParperConcat( $data ) {
        $id = M('exercises_parper_concat')->add($data);
        return $id;
    }

    //根据大题获取所有的习题
    public function getQuestionBigPaper( $data ) {
        $data['exercises_createexercise.is_delete'] = 2;
        $list = M('exercises_parper_concat')
                ->join('exercises_createexercise on exercises_createexercise.id=exercises_parper_concat.exercise_id')
                ->field('exercises_parper_concat.big_question_id as big_question_id,exercises_createexercise.*')
                ->where($data)
                ->select();
        return $list;
    }

    //设置试卷的状态为 20：待校审 试卷的所有习题状态为 19:试卷中的习题待校审
    public function setPaperStatusPending( $paper_id ) {
        $paperdata['id'] = $paper_id;
        $paperwhere['paper_id'] = $paper_id;
        $saveinfo['status'] = 20;
        $setPaper = M('exercises_create_paper')->where( $paperdata )->save( $saveinfo );
        $childQuestionList = M('exercises_parper_concat')->where( $paperwhere )->select();
        if (empty($childQuestionList)) {
            if ($setPaper !== false) {
                return true;
            } else {
                return false;
            }
        } else {
            $exercise_id = [];
            foreach ($childQuestionList as $k=>$v) {
                $exercise_id[] = $v['exercise_id'];
            }
            $saveexerciseinfo['status'] = 19;
            $map['id'] = array('in',implode(',',$exercise_id));
            $id = M('exercises_createexercise')->where( $map )->save( $saveexerciseinfo );
            if ($setPaper !== false && $id !== false ) {
                return true;
            } else {
                return false;
            }
        }
    }

    /*
     * 根据大题ID获取小题信息列表
     */
    public function getQuestionsByBigQuestionId($id)
    {
        $result = M()->query("SELECT exercises_createexercise.* FROM exercises_parper_concat JOIN exercises_createexercise on exercises_createexercise.id=exercises_parper_concat.exercise_id
                    WHERE exercises_createexercise.is_delete = ".STATE_NORMAL." AND big_question_id = $id ORDER BY big_order
      ");
        return $result;
    }
    /*
     * 删除试卷小题关联
     */
    public function deletePaperExerciseJoin($paperId)
    {
        $result = M()->execute("DELETE FROM exercises_parper_concat WHERE paper_id = $paperId");
        return $result !== false;
    }
}