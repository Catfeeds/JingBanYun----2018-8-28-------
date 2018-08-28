<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2017/9/9
 * Time: 10:10
 */
namespace Common\Model;
use Think\Model;
class Exercises_textbook_tree_info_createexerciseModel extends Model{
    public    $model='';
    protected $tableName = 'exercises_textbook_tree_info_createexercise';
    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
        include $_SERVER['DOCUMENT_ROOT'].'/Application/Exercise/Conf/const.php';
    }

    /*
     * 查询习题关联知识点
     */
    public function getKnowledgeListByExerciseId($id,$joinId)
    {
        $sql = " SELECT ifnull(exercises_textbook_tree_info_createexercise.festival,exercises_textbook_tree_info_createexercise.chapter) knowledge_id,exercises_course.id courseid,dict_grade.id gradeid,exercises_textbook_tree_breviary.school_term,exercises_textbook_tree_info_createexercise.id,exercises_textbook_version.id versionid,exercises_textbook_version.version_name,exercises_course.name course_name,knowledge_name,knowledge_id,chapter.id chapter,festival.id festival,chapter.tree_point_name chapter_name,festival.tree_point_name festival_name,dict_grade.grade grade_name,school_term,exercises_textbook_tree_info_createexercise.textbook_tree_info_id gradeterm,difficulty  FROM exercises_textbook_tree_info_createexercise ".
               " JOIN exercises_course ON exercises_course.id = exercises_textbook_tree_info_createexercise.course_id ".
               " JOIN exercises_textbook_version ON exercises_textbook_version.id = exercises_textbook_tree_info_createexercise.version_id ".
               " JOIN exercises_textbook_tree_info chapter ON chapter.id = exercises_textbook_tree_info_createexercise.chapter ".
               " LEFT JOIN exercises_textbook_tree_info festival ON festival.id = exercises_textbook_tree_info_createexercise.festival ".
               " JOIN exercises_textbook_tree_breviary ON exercises_textbook_tree_breviary.id = chapter.textbook_tree_breviary_id ".
               " JOIN dict_grade ON dict_grade.id = exercises_textbook_tree_info_createexercise.grade_id ".
               " WHERE exercises_textbook_tree_info_createexercise.exercises_createexercise_id = $id";
        if(!empty($joinId))
            $sql .= ' AND exercises_textbook_tree_info_createexercise.id='.$joinId;
        $result = M()->query($sql);
        $i=0;
        foreach($result as $key=>$val)
        {
            $result[$key]['rownum'] = ++$i;
        }
        return $result;
    }
    public function getCourseGradeByTextbookId($id)
    {
      $result = M()->query('SELECT course_id,grade_id,grade grade_name,exercises_course.name course_name FROM exercises_textbook_tree_breviary 
                            JOIN exercises_course ON course_id = exercises_course.id
                            JOIN dict_grade ON grade_id = dict_grade.id
                            WHERE exercises_textbook_tree_breviary.id='.$id);
      return $result[0];
    }
    public function addKnowledge($qId,$data)
    {
       $courseGrade = $this->getCourseGradeByTextbookId($data['gradeTerm']);
       $result = M()->execute("INSERT INTO exercises_textbook_tree_info_createexercise 
                               (section_name,section_id,festival_name,chapter_name,version_name,difficulty,textbook_tree_info_id, exercises_createexercise_id, course_id, grade_id, version_id, chapter, festival, knowledge_id,knowledge_name,course_name,grade_name) 
                               VALUES 
                               ('{$data['section_name']}',{$data['section_id']},'{$data['festival_name']}','{$data['chapter_name']}','{$data['version_name']}',0,{$data['gradeTerm']},$qId,{$courseGrade['course_id']},{$courseGrade['grade_id']},{$data['versionId']},{$data['chapter']},{$data['festival']},{$data['knowledge_id']},'{$data['knowledge_name']}','{$courseGrade['course_name']}','{$courseGrade['grade_name']}')");
       return $result !== false;
    }

    public function editKnowledge($id,$data)
    {
        $courseGrade = $this->getCourseGradeByTextbookId($data['gradeTerm']);
        $result = M()->execute("UPDATE exercises_textbook_tree_info_createexercise SET difficulty=0,textbook_tree_info_id={$data['gradeTerm']},
        knowledge_id = {$data['knowledge_id']} , knowledge_name = '{$data['knowledge_name']}',course_id = {$courseGrade['course_id']},grade_id = {$courseGrade['grade_id']},version_id = {$data['versionId']},chapter = {$data['chapter']},festival = {$data['festival']} WHERE id=$id");
        return $result !== false;
    }

    public function deleteKnowledge($id)
    {
        $exerciseId = $this->getExerciseIdByPrimaryKey($id);
        //delete difficulty
        $result = M()->execute("UPDATE exercises_createexercise SET difficulty = 0 WHERE id = $exerciseId");
        if($result===false)
            return false;
        $result = M()->execute("DELETE FROM exercises_textbook_tree_info_createexercise WHERE id=$id");
        return $result !== false;
    }

    public function deleteKnowledgeByExerciseId($id)
    {
        $result = M()->execute("UPDATE exercises_createexercise SET difficulty = 0 WHERE id = $id");
        if($result===false)
            return false;
        $result = M()->execute("DELETE FROM exercises_textbook_tree_info_createexercise WHERE exercises_createexercise_id=$id");
        return $result !== false;
    }
    public function getExerciseIdByPrimaryKey($id)
    {
        $result = M()->query('SELECT exercises_createexercise_id id1 FROM exercises_textbook_tree_info_createexercise WHERE id='.$id);
        return $result[0]['id1'];
    }

    /*
     * 前台方法：由习题反查相关GROUP信息
     */
    public function getGroupInfoOfExercises($isNormalExercise,$exerciseCategory,$groupInfo,$infoId,$courseId=0,$pageIndex=-1,$pageSize=-1)
    {
         $whereStr = " exercises_createexercise.status = ". EXERCISE_STATE_ONSHELF ." AND exercises_createexercise.parent_id=0 AND exercises_createexercise.is_delete = ".STATE_NORMAL." AND exercises_createexercise.types=2";
         $fields = '';
         $groupStr = '';
         $orderStr = ' types,ordinary_type ';
         switch($groupInfo)
         {
             case 'term':$fields = 'GROUP_CONCAT(DISTINCT exercises_textbook_tree_info_createexercise.textbook_tree_info_id ORDER BY textbook_tree_info_id) id,grade_name name,GROUP_CONCAT(DISTINCT exercises_textbook_tree_info_createexercise.section_name ORDER BY textbook_tree_info_id) child';
                          $whereStr .= " AND exercises_textbook_tree_info_createexercise.version_id = $infoId AND exercises_textbook_tree_info_createexercise.course_id = $courseId ";
                          $groupStr = 'exercises_textbook_tree_info_createexercise.grade_id ';
                          $orderStr = ' exercises_textbook_tree_info_createexercise.grade_id ';
                          break;
             case 'festival':$fields = 'GROUP_CONCAT(DISTINCT exercises_textbook_tree_info_createexercise.festival ORDER BY festival)id,chapter_name name,GROUP_CONCAT( DISTINCT exercises_textbook_tree_info_createexercise.festival_name ORDER BY festival) child ';
                             $whereStr .= " AND exercises_textbook_tree_info_createexercise.textbook_tree_info_id = $infoId ";
                             $groupStr = 'exercises_textbook_tree_info_createexercise.chapter ';
                             $orderStr = ' exercises_textbook_tree_info_createexercise.chapter ';
                             break;
             case 'exerciseCategory':$fields = 'distinct exercises_createexercise.ordinary_type id ';
                                      $whereStr .= " AND exercises_textbook_tree_info_createexercise.festival = $infoId ";
                                      $groupStr = '1 ';
                                      break;
             case 'exercise':$fields = 'exercises_createexercise.id id,ordinary_type category,exercises_createexercise.words name,exercises_createexercise.analysis translation,exercises_createexercise.subject_name url,IFNULL(exercises_createexercise.score,0) point,
                                         concat(version_id,\',\',course_id,\',\',grade_id,\',\',section_id,\',\',chapter,\',\',festival,\',\',knowledge_id) knowledge_code';
                              $whereStr .= " AND exercises_textbook_tree_info_createexercise.festival = $infoId ";
                              $groupStr = 'exercises_createexercise.id ';
                              break;
         }
         if(0 == $isNormalExercise)
         {
             if(empty($exerciseCategory))
                 $whereStr .= ' AND  exercises_createexercise.ordinary_type in (1,2,3,4)';
             else
                 $whereStr .= ' AND  exercises_createexercise.ordinary_type='.$exerciseCategory.' ';
         }
         $limitStr = '';
         if($pageIndex != -1 && $pageSize != -1)
             $limitStr = " LIMIT ".($pageIndex-1)*$pageSize . ",$pageSize ";
         $result = M()->query("SELECT $fields FROM exercises_textbook_tree_info_createexercise JOIN exercises_createexercise ON exercises_createexercise.id = exercises_textbook_tree_info_createexercise.exercises_createexercise_id
                               WHERE $whereStr GROUP BY $groupStr ORDER BY $orderStr $limitStr
                              ");
        return $result;
    }


    //添加导入tree
    public function addTreeInfo($codeId,$codeName,$id) {
        if (!empty($codeId)) {
            if( !empty($codeId[0]) && $codeId[0] != -1 &&
                !empty($codeId[1]) && $codeId[1] != -1 &&
                !empty($codeId[2]) && $codeId[2] != -1 &&
                !empty($codeId[3]) && $codeId[3] != -1) {
                $tree_id = $this->getTreeIdByOptions($codeId[0], $codeId[1], $codeId[2], $codeId[3]);
                if (empty($tree_id)) {
                    return false;
                }
            }
            else{
                return false;
            }
            $data['exercises_createexercise_id'] = $id;
            $data['textbook_tree_info_id'] = $tree_id;
            $data['version_id'] = $codeId[0];
            $data['course_id'] = $codeId[1];
            $data['grade_id'] = $codeId[2];
            $data['section_id'] = $codeId[3];
            $data['chapter'] = $codeId[4];
            $data['festival'] = $codeId[5];
            $data['version_name'] = $codeName[0];
            $data['course_name'] = $codeName[1];
            $data['grade_name'] = $codeName[2];
            $data['section_name'] = $codeName[3];
            $data['chapter_name'] = $codeName[4];
            $data['festival_name'] = $codeName[5];
            //$data['exercises_score'] = 10;
            $id  = M('exercises_textbook_tree_info_createexercise')->add($data);
            if ($id) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function getTreeIdByOptions($versionId,$courseId,$gradeId,$schoolTerm)
    {
        $result = M()->query("SELECT id FROM exercises_textbook_tree_breviary WHERE version_id = $versionId AND course_id = $courseId AND grade_id =$gradeId AND school_term = $schoolTerm LIMIT 1");
        return $result[0]['id'];
    }

    public function getExerciseIdByResourceId($resourceId)
    {
        $result = M()->query("SELECT exercise_id FROM knowledge_resource_exercise_contact WHERE contact_resource_id=$resourceId LIMIT 1 ");
        return $result[0]['exercise_id'];

    }

    public function addResourceExerciseContact($data)
    {
        try {
            M('knowledge_resource_exercise_contact')->add($data);
        }catch(\Exception $e){
            return false;
        }
        return true;
    }

    public function getResourceExerciseContact($contactId)
    {
       $where['contact_resource_id'] = $contactId;
       $result = M('knowledge_resource_exercise_contact')->where($where)->field('1')->find();
       return empty($result) ? false : true;

    }

    public function getContactIdsByResourceId($resourceId)
    {
        $where['resource_id'] = $resourceId;
        $result = M('knowledge_resource_exercise_contact')->where($where)->field('contact_resource_id id')->select();
        return array_column($result,'id');
    }
}