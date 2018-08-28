<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2017/9/9
 * Time: 10:10
 */
namespace Common\Model;
use Think\Model;
class Exercises_textbook_tree_infoModel extends Model{
    public    $model='';
    protected $tableName = 'exercises_textbook_tree_info';
    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }

    /*
     * 根据版本关键字查询所有年级分册
     */
    public function getGradeTermByVersionAndKeyword($versionId,$keyword,$courseId)
    {
        return M()->query("
        SELECT tree_id id,dict_grade.grade name,school_term FROM (SELECT DISTINCT(textbook_tree_breviary_id) tree_id FROM exercises_textbook_tree_info WHERE tree_point_name LIKE '%$keyword%') a JOIN exercises_textbook_tree_breviary ON a.tree_id = exercises_textbook_tree_breviary.id
        and exercises_textbook_tree_breviary.version_id=$versionId AND exercises_textbook_tree_breviary.course_id = $courseId JOIN dict_grade ON dict_grade.id = exercises_textbook_tree_breviary.grade_id GROUP BY tree_id");
    }

    public function getChapterByKeywordAndTree($treeId,$keyword)
    {
       $maxLevel = M()->query('SELECT MAX(level) level FROM exercises_textbook_tree_info LIMIT 1');
       $maxLevel = $maxLevel[0]['level'];
       $tableNameAliasString  = "abcdefghijklmnopqrstuvwxyz";
       $sqlArray = array();
       for($currentLevel = 1 ;$currentLevel < $maxLevel +1 ;$currentLevel++)
       {
           $sql = "(select id,tree_point_name name,parent_id from exercises_textbook_tree_info WHERE textbook_tree_breviary_id = $treeId AND level = $currentLevel AND tree_point_name like '%$keyword%') a ";
           for($i=1;$i<$currentLevel;$i++)
           {
               $sql = "( SELECT exercises_textbook_tree_info.id,exercises_textbook_tree_info.tree_point_name name,exercises_textbook_tree_info.parent_id FROM  $sql JOIN exercises_textbook_tree_info ON ".$tableNameAliasString[$i-1].".parent_id = exercises_textbook_tree_info.id ) ". $tableNameAliasString[$i];
           }
           $sqlArray[] = "(SELECT id,name FROM $sql GROUP BY id)";

       }
       $result = M()->query(implode(' UNION ',$sqlArray));
       return $result;
    }

    public function getFestivalByKeywordAndChapter($chapterId,$keyword)
    {
        $maxLevel = M()->query('SELECT MAX(level) level FROM exercises_textbook_tree_info LIMIT 1');
        $maxLevel = $maxLevel[0]['level'];
        $tableNameAliasString  = "abcdefghijklmnopqrstuvwxyz";
        $sqlArray = array();
        for($currentLevel = 2 ;$currentLevel < $maxLevel +1 ;$currentLevel++)
        {
            $sql = "(select id,tree_point_name name,parent_id from exercises_textbook_tree_info WHERE level = $currentLevel AND tree_point_name like '%$keyword%') b ";
            for($i=2;$i<$currentLevel;$i++)
            {
                $sql = "( SELECT exercises_textbook_tree_info.id,exercises_textbook_tree_info.tree_point_name name,exercises_textbook_tree_info.parent_id FROM  $sql JOIN exercises_textbook_tree_info ON ".$tableNameAliasString[$i-1].".parent_id = exercises_textbook_tree_info.id ) ". $tableNameAliasString[$i];
            }
            $sqlArray[] = "SELECT id,name FROM $sql WHERE parent_id=$chapterId GROUP BY id";
        }
        $result = M()->query(implode(' UNION ',$sqlArray));
        return $result;
    }

    public function getNameListByKeywordVersion($keyword,$versionId,$courseId)
    {
        $result = M()->query("SELECT exercises_textbook_tree_info.id,exercises_textbook_tree_info.tree_point_name title FROM exercises_textbook_tree_info
                    JOIN exercises_textbook_tree_breviary ON exercises_textbook_tree_breviary.id = exercises_textbook_tree_info.textbook_tree_breviary_id
                    AND version_id = $versionId AND course_id = $courseId AND level = 3 
                    WHERE tree_point_name LIKE '%$keyword%'  ");
        return $result;
    }
    /*
     * 根据知识点ID获取知识点
     */
    public function getKnowledgeNameByKnowledgeId($id)
    {
        $result = M()->query("SELECT tree_point_name FROM exercises_textbook_tree_info WHERE id = $id LIMIT 1");
        return $result[0]['tree_point_name'];
    }
}