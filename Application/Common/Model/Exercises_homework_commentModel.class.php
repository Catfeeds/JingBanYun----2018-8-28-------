<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/9/7
 * Time: 11:42
 */
namespace Common\Model;

use Think\Model;


class Exercises_homework_commentModel extends Model
{
    public $model = '';
    protected $tableName = 'exercises_homework_comment';

    public function __construct()
    {
        parent::__construct();
    }

    /*
     *增加一条评论
     */
    public function add($submitId=0,$submitExerciseId,$comment)
    {
        if(!intval($submitId)>0){
            return false;
        }
        $data['homework_submit_id'] = $submitId;
        $data['submit_exercise_id'] = $submitExerciseId;
        $data['comment'] = $comment;
        $id = M('exercises_homework_comment')->add($data);
        if(empty($id))
            return false;
        return $id;
    }

    public function getComments($submitId=0,$exerciseId=0)
    {
        $where = [];
        if(!empty($submitId))
            $where[] = " homework_submit_id = $submitId ";
        if(intval($exerciseId) >=0 ){
            $where[] .= " submit_exercise_id =  $exerciseId";
        }
        if(!empty($where))
         $whereStr = implode(' AND ',$where);
        else
         $whereStr = ' 1=1 ';
        $result = M('')->query("SELECT submit_exercise_id id , comment FROM exercises_homework_comment WHERE $whereStr");
        return $result;
    }


}