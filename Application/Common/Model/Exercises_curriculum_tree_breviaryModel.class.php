<?php
namespace Common\Model;

use Think\Model;

define('MIN_LEVEL', 4);
define('MAX_LEVEL', 1);

class Exercises_curriculum_tree_breviaryModel extends Model
{

    public $model = '';
    protected $tableName = 'exercises_curriculum_tree_breviary';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);

    }

    private function getConditionWhere($condition)
    {
        $whereStr = " WHERE 1=1 ";
        foreach ($condition as $key => $val) {
            if (!empty($val))
                switch ($key) {
                    case 'courseId':
                        $whereStr .= " AND course_id=$val";
                        break;
                    case 'section':
                        $whereStr .= " AND learning_period_id=$val";
                        break;
                    default:
                        break;
                }
        }
        return $whereStr;
    }

    public function getTreeCount($condition)
    {
        $whereStr = $this->getConditionWhere($condition);
        $result = M()->query("SELECT COUNT(1) count FROM exercises_curriculum_tree_breviary JOIN exercises_course ON exercises_curriculum_tree_breviary.course_id = exercises_course.id $whereStr");
        return $result[0]['count'];
    }

    public function getTreeList($condition)
    {
        $whereStr = $this->getConditionWhere($condition);
        if(isset($condition['startIndex']) && $condition['pageSize'])
            $limitStr = ' LIMIT '.$condition['startIndex'] .',' .$condition['pageSize'].' ';
        $result = M()->query(" SELECT b.id,b.learning_period_id,exercises_course.name,b.creat_status,b.creat_account,b.creat_name FROM (SELECT id FROM exercises_curriculum_tree_breviary $whereStr) a " .
            " JOIN exercises_curriculum_tree_breviary b ON a.id = b.id " .
            " JOIN exercises_course ON b.course_id = exercises_course.id $limitStr"
        );
        return $result;
    }

    /*
     *首页(课标知识树学科数据)
     */
    public function getList($where = array())
    {
        $reslut = $this->model
            ->field('creat_status,learning_period_id,course_id,id')
            ->where($where)
            ->find(); //echo M()->getLastSql();
        return $reslut;
    }

    /*
     *教材知识树关联数据
     */
    public function curriculumTreeConcat($where)
    {
        $reslut = $this->model
            ->field('exercises_curriculum_tree_info.*')
            ->join("exercises_curriculum_tree_info on exercises_curriculum_tree_info.curriculum_tree_breviary_id = exercises_curriculum_tree_breviary.id")
            ->where($where)
            ->order('exercises_curriculum_tree_info.sort')
            ->select(); //echo M()->getLastSql();
        return $reslut;
    }

    /*
    * 获得某个子级知识点
    */
    public function getCurriculumKnowledgePointByParentId($id)
    {
        $model = M('exercises_curriculum_tree_info');
        $where['parent_id'] = $id;
        $result = $model->where($where)->order('sort')->select();
        return $result;
    }

    /*
     *教材知识树知识点的增加
     */
    public function addCurriculumKnowledgePoint($data)
    {
        $model = M('exercises_curriculum_tree_info');
        if ($insert_id = $model->add($data)) {
            return $insert_id;
        } else {
            return false;
        }
    }

    /*
    *教材知识树知识点的删除
    */
    public function deleteCurriculumKnowledgePoint($id)
    {
        $model = M('exercises_curriculum_tree_info');
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
    *教材知识树知识点的修改
    */
    public function saveCurriculumKnowledgePoint($id, $data)
    {
        $model = M('exercises_curriculum_tree_info');
        $where['id'] = $id;
        if ($model->where($where)->save($data) === false) {
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
        $model = M('exercises_curriculum_tree_info');

        $index++;
        if ($index == 1) {
            $where['id'] = $id;
            $result = $model->where($where)->field('id,curriculum_tree_breviary_id,level,parent_id,tree_point_name')->select();
            if ($result[0]['level'] != 4) {
                return array_merge($result, $this->getNeedDeleteKnowledgePoint($id));
            } else {
                return $result;
            }
        } else {
            $where['parent_id'] = $id;
            $result = $model->where($where)->field('id,curriculum_tree_breviary_id,level,parent_id,tree_point_name')->select();
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
     *课标树的创建
     */
    public function creatTextbookTree($data)
    {
        $insertId = $this->model
            ->add($data);
        return $insertId;
    }

    /*
    *课标知识树的修改
    */

    public function saveCurriculumTree($where, $data)
    {
        $saveId = $this->model
            ->where($where)
            ->save($data);
        return $saveId;
    }

    public function getTreeInfoChain($textbookKnowledgeId)
    {
        $resultArray = array();
        $curId = M()->query("SELECT curriculum_tree_info_id id FROM exercises_textbook_tree_info WHERE id=$textbookKnowledgeId");
        $curId = $curId[0]['id'];
        if (empty($curId))
            return array();
        do {
            $currentLevelInfo = M()->query("SELECT exercises_course.name course_name,b.Learning_period_id,b.course_id,a.id,a.tree_point_name name,curriculum_tree_breviary_id,level,a.parent_id FROM exercises_curriculum_tree_info a JOIN exercises_curriculum_tree_breviary b 
        ON a.curriculum_tree_breviary_id = b.id JOIN exercises_course ON exercises_course.id = b.course_id
        WHERE a.id=$curId");
            $currentLevel = $currentLevelInfo[0]['level'];
            $resultArray = array_merge($currentLevelInfo, $resultArray);
            $curId = $currentLevelInfo[0]['parent_id'];
        } while ($currentLevel != 1);
        return $resultArray;
    }

    /*
     * 根据某个id获得该父级的树形结构数据
     */
    public function getParentTreeData($id, $data = array())
    {
        static $index = 0;
        $model = M('exercises_curriculum_tree_info');

        $index++;
        if ($index == 1) {
            $where['id'] = $id;
            $result = $model->where($where)->field('id,level,parent_id,tree_point_name,sort,curriculum_tree_breviary_id')->order('sort')->select();
            if ($result[0]['level'] != MAX_LEVEL) {
                return $this->getParentTreeData($result[0]['parent_id']);
            } else {
                $whereData['Learning_period_id'] = $data['Learning_period_id'];
                $whereData['course_id'] = $data['course_id'];
                $whereData['parent_id'] = 0;
                $result = $this->curriculumTreeConcat($whereData);//查询所有章
                return $result;
            }
        } else {
            if ($index == 2) {
                $where['parent_id'] = $id;
            } else {
                $where['id'] = $id;
            }
            $result = $model->where($where)->field('id,level,parent_id,tree_point_name,sort,curriculum_tree_breviary_id')->order('sort')->select();
            if (!empty($data)) {
                $result[0]['child'] = $data;
            }
            $all_parent_result = array();
            if (!empty($result)) {
                if ($result[0]['level'] != MAX_LEVEL) {
                    $parent_one_result = $model->where('id=' . $result[0]['parent_id'])->field('id,level,parent_id,tree_point_name,sort,curriculum_tree_breviary_id')->select();
                    if (!empty($parent_one_result)) {
                        $parent_one_result[0]['child'] = $result;

                        if ($parent_one_result[0]['level'] == MAX_LEVEL) {
                            $parent_result = $model->where('curriculum_tree_breviary_id=' . $parent_one_result[0]['curriculum_tree_breviary_id'] . ' and parent_id=' . $parent_one_result[0]['parent_id'] . ' and id!=' . $parent_one_result[0]['id'])
                                ->field('id,parent_id,tree_point_name,sort')->order('sort')->select();
                        } else {
                            $parent_result = $model->where('parent_id=' . $parent_one_result[0]['parent_id'] . ' and id!=' . $parent_one_result[0]['id'])
                                ->field('id,parent_id,tree_point_name,sort')->order('sort')->select();
                        }
                        $all_parent_result = array_merge($parent_result, $parent_one_result);
                        $all_parent_result = my_sort($all_parent_result, 'sort');

                        if ($parent_one_result[0]['level'] == MAX_LEVEL) {
                            return $all_parent_result;
                        } else {
                            return $this->getParentTreeData($parent_one_result[0]['parent_id'], $all_parent_result);
                        }
                    }
                } else {
                    if (!empty($data)) {
                        $result[0]['child'] = $data;
                    }
                    $parent_result = $model->where('curriculum_tree_breviary_id=' . $result[0]['curriculum_tree_breviary_id'] . ' and parent_id=' . $result[0]['parent_id'] . ' and id!=' . $result[0]['id'])
                        ->field('id,parent_id,tree_point_name,sort')->order('sort')->select();
                    $all_parent_result = array_merge($parent_result, $result);
                    $all_parent_result = my_sort($all_parent_result, 'sort');
                    return $all_parent_result;
                }
            } else {
                return array();
            }
        }
    }

    /*
     * 查询教材知识树中是否有关联的课标树ID
     */
    public function isEmpty($where)
    {
        return M('exercises_curriculum_tree_info')->field('id,tree_point_name,curriculum_tree_breviary_id,level,parent_id')->where($where)->select();
    }
}