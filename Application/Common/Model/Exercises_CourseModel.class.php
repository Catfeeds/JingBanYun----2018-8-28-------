<?php
namespace Common\Model;
use Think\Model;

class Exercises_CourseModel extends Model{

    public    $model='';
    protected $tableName = 'exercises_course';
    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);

    }
    private function getUpdateLastInfoSql($userId,$questionId)
    {
        return "UPDATE exercises_question_process SET lastoperator_id=$userId,lastoperate_time=now() WHERE question_id=$questionId";
    }
    /*
     * 获取学科
     */

    public function getCourseList( $id,&$count )
    {
        if(!empty($id)) {
            $list = $this->model->where("parent_id=$id")->select();
            return $list;
        } else {
            $list = $this->model->where('parent_id=0')->select();
            $count = count($list);
            return $list;
        }

    }


    public function getCourseName( $id )
    {
        if(!empty($id)) {
            $list = $this->model->where("id=$id")->find();
            return $list;
        }

    }

    /*
     *添加学科和习题类型
     */
    public function dataAdd($name,$id){
        if(!empty($id)){
            $data['name'] = $name;
            $data['parent_id'] = $id;
            $addStatus = $this->model->add($data);
        }else{
            $data['name'] = $name;
            $addStatus = $this->model->add($data);
        }

        return $addStatus;
    }

    /*
     *修改学科和习题类型
     */
    public function dataSave($name,$where){
        $saveStutus = $this->model
                    ->where($where)
                    ->save($name); //echo M()->getLastSql();
        return $saveStutus;
    }

    /**
     * 描述：修改习题关联知识点表中的冗余字段
     */
    public function redundantSave($name,$where)
    {
        $saveStutus = M('exercises_textbook_tree_info_createexercise')
            ->where($where)
            ->save($name); //echo M()->getLastSql();
        return $saveStutus;
    }

    /**
     * 描述：根据课标树和教材树中的关联ID查找教材知识树的ID
     */
    public function getKnowledgeById($id)
    {
        //根据课标树中知识点查找教材树知识点ID
        $model = M('exercises_textbook_tree_info');
        $where['curriculum_tree_info_id'] = $id;
        $reslut = $model->field('id')->where($where)->select();
        return $reslut;
    }

    /**
     * 描述：查询知识点是否关联习题
     */
    public function associatedOfExercises($where)
    {
        $saveStutus = M('exercises_textbook_tree_info_createexercise')
            ->where($where)
            ->select(); //echo M()->getLastSql();
        return $saveStutus;
    }

    /**
     * 描述：查询习题类型是否关联习题
     */
    public function associatedOfExercisesByType($where)
    {
        $stutus = M('exercises_createexercise')
            ->where($where)
            ->select(); //echo M()->getLastSql();
        return $stutus;
    }
    /*
     *删除习题类型
     */
    public function detelteType($where){
        $saveStutus = M()->execute("delete from  exercises_course  WHERE id=$where");
        return $saveStutus;
    }

    
    /*
     * 获取课标树创建状态
     */
    public function getCurriculumTreeState($periodArray)
    {
        if(sizeof($periodArray) != 3)
            return false;
        $sql = "SELECT
                exercises_course.name course_name,
                CONCAT(IFNULL(a.creat_status, '0'),
                        ',',
                        IFNULL(b.creat_status, '0'),
                        ',',
                        IFNULL(c.creat_status, '0')) data
            FROM
                exercises_course
                    LEFT JOIN
                exercises_curriculum_tree_breviary a ON a.course_id = exercises_course.id
                    AND a.learning_period_id = 1
                    LEFT JOIN
                exercises_curriculum_tree_breviary b ON b.course_id = exercises_course.id
                    AND b.learning_period_id = 2
                    LEFT JOIN
                exercises_curriculum_tree_breviary c ON c.course_id = exercises_course.id
                    AND c.learning_period_id = 3
            WHERE
                exercises_course.parent_id = 0";
        $result = M()->query($sql);
        return $result;
    }
    /*
     *学科详情
     */
        public function getCourseInfo($where){
        $sql = "SELECT
	exercises_course. name,b.id,b.name pname
FROM
	exercises_course
left JOIN exercises_course b ON b.parent_id = exercises_course.id
WHERE exercises_course.parent_id=0 and exercises_course.id=$where";
        $info = M()->query($sql);
        return $info;
    }

    public function getInfoList(){
        $sql = "SELECT
            exercises_course. name,exercises_course.id,group_concat(b.name) pname
        FROM
            exercises_course
        left JOIN exercises_course b ON b.parent_id = exercises_course.id
        WHERE exercises_course.parent_id=0
        GROUP BY exercises_course.id ";
        $list = M()->query($sql);
        return $list;
    }

    /*
     *获取习题类型详情
     */
    public function getTypeInfo($id){
        $sql = "select exercises_course.name from exercises_course where exercises_course.id = $id";
        $info = M()->query($sql);
        return $info;
    }

    /*
     * 获取教材知识树状态
     */
    public function getTextbookTreeState($schoolTermList)
    {
        foreach($schoolTermList as $key=>$val)
        {
            $schoolTermArr[] = " (SELECT {$val['id']} id,'{$val['name']}' name) ";
        }
        $schoolTermTable = '('.implode(' UNION ',$schoolTermArr).') ';
        $sql = "SELECT 
                 CONCAT(b.grade, c.name) name, a.data
             FROM
                 (SELECT 
                     a.version_id,
                         a.grade_id,
                         a.school_term,
                         GROUP_CONCAT((CASE
                             WHEN b.id IS NULL THEN 0
                             ELSE b.creat_status
                         END)
                             ORDER BY a.course_id ASC
                             SEPARATOR ',') data
                 FROM
                     (SELECT 
                     exercises_textbook_version.id version_id,
                         version_name,
                         exercises_course.id course_id,
                         exercises_course.name,
                         grade_id,
                         school_term
                 FROM
                     exercises_textbook_version
                 JOIN exercises_course ON exercises_course.parent_id = 0
                 JOIN exercises_school_term) a
                 LEFT JOIN exercises_textbook_tree_breviary b ON a.version_id = b.version_id
                     AND a.grade_id = b.grade_id
                     AND a.course_id = b.course_id
                     AND a.school_term = b.school_term
                 GROUP BY a.version_id , a.grade_id , a.school_term) a
                     JOIN
                 dict_grade b ON a.grade_id = b.id
                     JOIN
                 $schoolTermTable c ON a.school_term = c.id
             ORDER BY a.version_id , a.grade_id , a.school_term";
          $result = M()->query($sql);
          return $result;
    }

    /*
     *获取所有学科和题型
     */
    public function getAllResource($where){
        return $this->model->field('id,name')
            ->where($where)
            ->find();
    }

    public function getAllTopicIdName(){
        return $this->model->field('id,name')
            ->where('parent_id <> 0 ')
            ->select();
    }
}