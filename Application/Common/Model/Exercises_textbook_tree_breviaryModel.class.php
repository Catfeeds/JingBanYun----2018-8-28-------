<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/9/20
 * Time: 10:10
 */
namespace Common\Model;

use Think\Model;

class Exercises_textbook_tree_breviaryModel extends Model
{
    public $model = '';
    protected $tableName = 'exercises_textbook_tree_breviary';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);

    }

    /*
     *首页获取分册所有数据
     */
    public function getListFasciculeAll()
    {
        $list = M('exercises_school_term')
            ->field('dict_grade.grade,exercises_school_term.*')
            ->join("dict_grade on dict_grade.id = exercises_school_term.grade_id")
            ->select();
        return $list;
    }

    /*
     *首页数据
     */
    public function getList($where = array())
    {
        $reslut = $this->model
            ->field('creat_status,id,version_id,course_id,grade_id,school_term')
            ->where($where)
            ->find(); //echo M()->getLastSql();
        return $reslut;
    }

    /*
     *知识点标引根据学科和版本查询所有的年级和分册
     */
    public function getCourseSchoolTermAll($where = array())
    {
        $reslut = $this->model
            ->field('exercises_textbook_tree_breviary.id,exercises_textbook_tree_breviary.school_term,exercises_textbook_tree_breviary.grade_id,dict_grade.grade')
            ->join('dict_grade on dict_grade.id = exercises_textbook_tree_breviary.grade_id')
            ->where($where)
            ->order('grade_id')
            ->select(); //echo M()->getLastSql();
        return $reslut;
    }

    /*
     *教材知识树关联数据
     */
    public function textbookConcat($where)
    {
        $reslut = $this->model
            ->field('exercises_textbook_tree_info.*')
            ->join("exercises_textbook_tree_info on exercises_textbook_tree_info.textbook_tree_breviary_id = exercises_textbook_tree_breviary.id")
            ->where($where)
            ->order('exercises_textbook_tree_info.sort')
            ->select(); //echo M()->getLastSql();die;
        return $reslut;
    }

    /*
    * 获得某个子级知识点
    */
    public function getTextbookKnowledgePointByParentId($id)
    {
        $model = M('exercises_textbook_tree_info');
        $where['parent_id'] = $id;
        $result = $model->where($where)->order('sort')->select();
        return $result;
    }

    /*
     *教材知识树知识点的增加
     */
    public function addTextbookKnowledgePoint($data)
    {
        $model = M('exercises_textbook_tree_info');
        if ($insert_id = $model->add($data)) {
            return $insert_id;
        } else {
            return false;
        }
    }

    /*
    *教材知识树知识点的删除
    */
    public function deleteTextbookKnowledgePoint($id)
    {
        $model = M('exercises_textbook_tree_info');
        $data = $this->getNeedDeleteKnowledgePoint($id);
        $result = array_column($data, 'id');
        $where['id'] = array('in', $result);
        if ($model->where($where)->delete() === false) {
            return false;
        } else {
            return true;
        }
    }

    /*
     * 获得需要删除知识点
     */
    public function getNeedDeleteKnowledgePoint($id)
    {
        static $index = 0;
        $model = M('exercises_textbook_tree_info');

        $index++;
        if ($index == 1) {
            $where['id'] = $id;
            $result = $model->where($where)->field('id,textbook_tree_breviary_id,level,parent_id,tree_point_name')->select();
            if ($result[0]['level'] != 4) {
                return array_merge($result, $this->getNeedDeleteKnowledgePoint($id));
            } else {
                return $result;
            }
        } else {
            $where['parent_id'] = $id;
            $result = $model->where($where)->field('id,textbook_tree_breviary_id,level,parent_id,tree_point_name')->select();
            if (!empty($result)) {
                if ($result[0]['level'] != 4) {
                    foreach ($result as $key => $val) {
                        return array_merge($result, $this->getNeedDeleteKnowledgePoint($val['id']));
                    }
                } else {
                    return $result;
                }
            } else {
                return array();
            }
        }
    }

    /*
    *教材知识树知识点的修改
    */
    public function saveTextbookKnowledgePoint($id, $data)
    {
        $model = M('exercises_textbook_tree_info');
        $where['id'] = $id;
        if ($model->where($where)->save($data) === false) {
            return false;
        } else {
            return true;
        }
    }

    /*
     *教材知识树的创建
     */
    public function creatTextbookTree($data)
    {
        $insertId = $this->model
            ->add($data);
        return $insertId;
    }

    /*
     *教材知识树的修改
     */

    public function saveTextbookTree($where, $data)
    {
        $saveId = $this->model
            ->where($where)
            ->save($data);
        return $insertId;
    }

    private function getConditionWhere($condition)
    {
        $whereStr = " WHERE 1=1 ";
        foreach ($condition as $key => $val) {
            if (!empty($val))
                switch ($key) {
                    case 'versionId':
                        $whereStr .= "AND version_id=$val ";
                        break;
                    case 'courseId':
                        $whereStr .= " AND course_id=$val ";
                        break;
                    case 'schoolTermId':
                        $whereStr .= " AND school_term=$val ";
                        break;
                    case 'gradeId':
                        $whereStr .= " AND grade_id=$val ";
                        break;
                    default:
                        break;
                }
        }
        return $whereStr;
    }

    private function getConditionJoin($condition)
    {
        $joinStr = '';
        if ($condition['keyword']) {
            $joinStr .= " JOIN (SELECT DISTINCT textbook_tree_breviary_id id FROM exercises_textbook_tree_info WHERE tree_point_name like '%{$condition['keyword']}%') c ON c.id = exercises_textbook_tree_breviary.id ";
        }
        return $joinStr;
    }

    public function getTreeCount($condition)
    {
        $whereStr = $this->getConditionWhere($condition);
        $joinStr = $this->getConditionJoin($condition);
        $result = M()->query("SELECT COUNT(1) count FROM exercises_textbook_tree_breviary $joinStr $whereStr");
        return $result[0]['count'];
    }

    public function getTreeList($condition)
    {
        $whereStr = $this->getConditionWhere($condition);
        $joinStr = $this->getConditionJoin($condition);
        if(isset($condition['startIndex']) && $condition['pageSize'])
            $limitStr = ' LIMIT '.$condition['startIndex'] .',' .$condition['pageSize'].' ';
        $result = M()->query(" SELECT exercises_textbook_tree_breviary.id,exercises_textbook_version.version_name,exercises_textbook_tree_breviary.school_term,dict_grade.grade grade_name,exercises_course.name course_name,exercises_textbook_tree_breviary.creat_status,exercises_textbook_tree_breviary.creat_account,exercises_textbook_tree_breviary.creat_name FROM (SELECT id FROM exercises_textbook_tree_breviary $whereStr) a " .
            " JOIN exercises_textbook_tree_breviary  ON a.id = exercises_textbook_tree_breviary.id " .
            " JOIN exercises_course ON exercises_textbook_tree_breviary.course_id = exercises_course.id " .
            " JOIN dict_grade ON exercises_textbook_tree_breviary.grade_id = dict_grade.id " .
            " JOIN exercises_textbook_version ON exercises_textbook_tree_breviary.version_id = exercises_textbook_version.id " .
            $joinStr.
            $limitStr
        );
        return $result;
    }

    /*
    * 查询指定ID的信息
    */
    public function isEmpty($where)
    {
        return M('exercises_textbook_tree_info')->field('id,tree_point_name,textbook_tree_breviary_id,level')->where($where)->select();
    }

    /*
   *教材知识树知识点的修改(根据课标树做修改)
   */
    public function saveTextbookKnowledgePointByCuriculumTree($id, $data)
    {
        $model = M('exercises_textbook_tree_info');
        $where['curriculum_tree_info_id'] = $id;
        if ($model->where($where)->save($data) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     *描述：教材知识树关联课标知识点字段的修改
     */
    public function saveTextbookCurriculum_tree_info_idByCuriculumTree($id, $data)
    {
        $model = M('exercises_textbook_tree_info');
        $data_id = $this->getNeedKnowledgePoint($id);
        $result = array_column($data_id, 'id');
        $where['curriculum_tree_info_id'] = array('in', $result);
        if ($model->where($where)->save($data) === false) {
            return false;
        } else {
            return true;
        }
    }

    /*
     * 获得需要的课标树知识点
     */
    /**
     * @param $id
     * @return array|false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getNeedKnowledgePoint($id)
    {
        static $index = 0;
        $model = M('exercises_curriculum_tree_info');

        $index++;
        if ($index == 1) {
            $where['id'] = $id;
            $result = $model->where($where)->field('id,curriculum_tree_breviary_id,level,parent_id,tree_point_name')->select();
            if ($result[0]['level'] != 4) {
                return array_merge($result, $this->getNeedKnowledgePoint($id));
            } else {
                return $result;
            }
        } else {
            $where['parent_id'] = $id;
            $result = $model->where($where)->field('id,curriculum_tree_breviary_id,level,parent_id,tree_point_name')->select();
            if (!empty($result)) {
                if ($result[0]['level'] != 4) {
                    foreach ($result as $key => $val) {
                        return array_merge($result, $this->getNeedKnowledgePoint($val['id']));
                    }
                } else {
                    return $result;
                }
            } else {
                return array();
            }
        }
    }

    /*
     * 获取树INFO
     */

    public function getTreeInfoByTreeId($id)
    {
        $result = M()->query("SELECT * FROM exercises_textbook_tree_breviary WHERE id=$id");
        return $result[0];
    }

    /*
     * 获取树INFO
     */
    public function getTreeIdByOptions($versionId,$courseId,$gradeId,$schoolTerm)
    {
       $result = M()->query("SELECT id FROM exercises_textbook_tree_breviary WHERE version_id = $versionId AND course_id = $courseId AND grade_id =$gradeId AND school_term = $schoolTerm LIMIT 1");
       return $result[0]['id'];
    }

    //TODO:exercises_textbook_tree_curriculum_tree关联表的添加
    /*
     *描述：exercises_textbook_tree_curriculum_tree关联表的添加
     */
    public function add_by_exercises_textbook_tree_curriculum_tree($data = ''){
        $result = M('exercises_textbook_tree_curriculum_tree')->add($data);
        return $result;
    }
    //TODO:exercises_textbook_tree_curriculum_tree关联表的删除
    /*
    *描述：exercises_textbook_tree_curriculum_tree关联表的删除
    */
    public function delete_from_exercises_textbook_tree_curriculum_tree($where = ''){
        $result = M('exercises_textbook_tree_curriculum_tree')->where($where)->delete();
        return $result;
    }
}