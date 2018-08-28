<?php
/**
 * Created by PhpStorm.
 * User: GM
 * Date: 2018/3/30
 * Time: 14:44
 */

namespace Common\Model;


use Think\Model;

class Question_tagsModel extends Model
{
    public $model;
    protected $tableName = 'question_tags';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    /**
     *描述：查找所有标签分页
     */
    public function getQuestionTagsAll($where=[],$pageIndex=1,$pageSize=20){
        $result = $this->model
            ->where($where)
            ->page($pageIndex,$pageSize)
            ->order('creat_time desc')
            ->select();
//echo M()->getLastSql();die;
        return $result;
    }
    /**
     *描述：查找一个标签
     */
    public function getQuestionTagsOne($where=[]){
        $result = $this->model
            ->where($where)
            ->select();
        return $result;
    }
    /**
     *描述：添加标签
     */
    public function addQuestionTags($data){
        return $this->model
            ->add($data);
    }
    /**
     *描述：修改标签
     */
    public function updateQuestionTags($data,$where=[]){
        return $this->model
            ->where($where)
            ->save($data);
    }
    /**
     *描述：删除标签
     */
    public function deleteQuestionTags(){

    }

    /*
     *描述：往标签关联表中插入数据
     */
    public function addQuestionTagsConcat($data){
        return M('question_question_tags_concat')->add($data);
    }

    /*
     *描述：根据问题查找所拥有的的标签
     */
    public function getTagsByQuestion($where){
        $result = $this->model
            ->join('question_question_tags_concat ON question_tags_id = question_tags.id')
            ->where($where)
            ->select();
        return $result;
    }
}