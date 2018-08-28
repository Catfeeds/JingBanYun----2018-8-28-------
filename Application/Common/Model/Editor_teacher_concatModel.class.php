<?php
/**
 * Created by PhpStorm.
 * User: GM
 * Date: 2018/3/30
 * Time: 14:44
 */

namespace Common\Model;


use Think\Model;

class Editor_teacher_concatModel extends Model
{
    public $model;
    protected $tableName = 'editor_teacher_concat';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    /**
     *
     */
    public function getEditorAll($where=[],$pageIndex=1,$pageSize=20){
        $result = $this->model
            ->field('editor_teacher_concat.*,auth_teacher.name,auth_teacher.telephone,dict_publishing_house.house,dict_course.course_name,auth_teacher.flag')
            ->join('auth_teacher ON auth_teacher.id = editor_teacher_concat.teacher_id')
            ->join('dict_publishing_house ON dict_publishing_house.id = editor_teacher_concat.dict_publishing_house_id')
            ->join('dict_course ON dict_course.id = editor_teacher_concat.course_id')
            ->where($where)
            ->page($pageIndex,$pageSize)
            ->order('editor_teacher_concat.creat_time desc')
            ->select();
//echo M()->getLastSql();die;
        return $result;
    }
    /**
     *
     */
    public function getgetEditorOne($where=[]){
        $result = $this->model
            ->field('editor_teacher_concat.*,auth_teacher.name,auth_teacher.telephone,dict_publishing_house.house,dict_course.course_name,auth_teacher.password')
            ->join('auth_teacher ON auth_teacher.id = editor_teacher_concat.teacher_id')
            ->join('dict_publishing_house ON dict_publishing_house.id = editor_teacher_concat.dict_publishing_house_id')
            ->join('dict_course ON dict_course.id = editor_teacher_concat.course_id')
            ->where($where)
            ->select();
        return $result;
    }
    /**
     *
     */
    public function addEditor($data){
        return $this->model
            ->add($data);
    }
    /**
     *
     */
    public function updateEditor($data,$where=[]){
        return $this->model
            ->where($where)
            ->save($data);
    }
}