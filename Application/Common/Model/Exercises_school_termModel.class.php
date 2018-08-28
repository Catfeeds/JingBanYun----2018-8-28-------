<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2017/9/15
 * Time: 14:35
 */
namespace Common\Model;
use Think\Model;

class Exercises_school_termModel extends Model{

    public    $model='';
    protected $tableName = 'exercises_school_term';
    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);

    }
    private function getUpdateLastInfoSql($userId,$questionId)
    {
        return "UPDATE exercises_question_process SET lastoperator_id=$userId,lastoperate_time=now() WHERE question_id=$questionId";
    }
    /*
     * 获取分册
     */

    public function getSchoolTermList(&$count,$pageIndex = 1, $pageSize = 20)
    {
        $list = $this->model
            ->field('exercises_school_term.*,dict_grade.grade')
            ->join("dict_grade on dict_grade.id = exercises_school_term.grade_id")
            ->page($pageIndex . ',' . $pageSize)
            ->order('dict_grade.code,exercises_school_term.school_term')
            ->select();
        $count = M()->query("select count(1) count from exercises_school_term INNER JOIN dict_grade on dict_grade.id = exercises_school_term.grade_id ");
        return $list;
    }

    /*
     *分册的添加
     */
    public function dataAdd($schoolterm,$grade){
            $addStatus = M()->execute("insert into exercises_school_term (school_term,grade_id) VALUE ($schoolterm,$grade)");
        return $addStatus;
    }

    /*
     *分册的修改
     */
    public function dataSave($schoolterm,$grade,$where){
        $saveStutus = M()->execute("update exercises_school_term set school_term=$schoolterm,grade_id=$grade WHERE id=$where");
        return $saveStutus;
    }

    /*
     *获取年级
     */
    public function getGradeList(){
        $list = M()->query("select grade,id from dict_grade");
        return $list;
    }

    /*
     *获取分册详情
     */
    public function getInfo($id){
        $info = M()->query("select * from exercises_school_term WHERE id=$id");
        return $info;
    }

    /*
    *
    *获取分册和年级的共同数据（无分页）
    */
    public function getCourseGrade($where){
        $list = $this->model
            ->field('exercises_school_term.*,dict_grade.grade')
            ->join("dict_grade on dict_grade.id = exercises_school_term.grade_id")
            ->where($where)
            ->find();
            return $list;
    }

    /*
     * 根据年级学期查询主键ID
     */
    public function getIdByGradeTerm($grade,$schoolTerm)
    {
        if(empty($grade) || empty($schoolTerm))
            return array();
        $result = M()->query("SELECT id FROM exercises_school_term WHERE grade_id=$grade AND school_term=$schoolTerm");
        return $result[0];
    }
}