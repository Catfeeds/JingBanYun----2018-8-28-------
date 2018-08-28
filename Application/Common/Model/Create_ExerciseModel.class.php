<?php
namespace Common\Model;
use Think\Model;

class Create_ExerciseModel extends Model{

    public    $model='';
    protected $tableName = 'exercises_createexercise';
    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);

    }
    private function getUpdateLastInfoSql($userId,$questionId)
    {
        return "UPDATE exercises_question_process SET lastoperator_id=$userId,lastoperate_time=now() WHERE question_id=$questionId";
    }
    /*
     * 入库习题
     */

    public function createExerciseInfo( $data )
    {
        if(!empty($data)) {
            $id = $this->model->add($data);
            return $id;
        }
    }
    public function editExerciseInfo( $data,$id){
        if(!empty($data)) {
            $map['id'] = $id;
            $id = $this->model->where($map)->save($data);
            return $id;
        }
    }
    //获取习题信息
    public function getExerciseInfo( $id ) {
        $data['exercises_createexercise.id'] = $id;
        $info  =  $this->model
            ->join("left join exercises_parper_concat on exercises_parper_concat.exercise_id=exercises_createexercise.id")
            ->join("left join exercises_create_paper on exercises_create_paper.id=exercises_parper_concat.paper_id")
            ->where($data)
            ->field("exercises_createexercise.*,exercises_create_paper.id as pid,exercises_create_paper.paper_name,exercises_create_paper.year")
            ->find();
        return $info;
    }

    //获取习题信息
    public function getExerciseWorkInfo( $id ) {
        $data['exercises_createexercise.id'] = $id;
        $info  =  $this->model
            ->join("left join exercises_parper_concat on exercises_parper_concat.exercise_id=exercises_createexercise.id")
            ->join("left join exercises_create_paper on exercises_create_paper.id=exercises_parper_concat.paper_id")
            ->join("left join exercises_textbook_tree_info_createexercise on exercises_textbook_tree_info_createexercise.exercises_createexercise_id=exercises_createexercise.id")
            ->join("left join exercises_question_processinfo on exercises_question_processinfo.question_id=exercises_createexercise.id")

            ->where($data)
            ->field("exercises_createexercise.id as eid,exercises_createexercise.*,exercises_create_paper.id as pid,exercises_create_paper.paper_name,exercises_create_paper.year,exercises_textbook_tree_info_createexercise.*,exercises_textbook_tree_info_createexercise.course_name as cnamecourse,exercises_question_processinfo.creator_name")
            ->find();
        return $info;
    }
    //更新搜索
    public function updateExerciseInfo( $id,$search_name )
    {   $whereid['id'] = $id;
        $savedata['search_name'] = $search_name;
        $id = $this->model->where($whereid)->save($savedata);
        return $id;
    }
    //查询所有的复合题子题
    public function getChildExercise($id) {
        $data['parent_id'] = $id;
        return $this->model->where($data)->select();
    }

    //删除父选项和所有子选项
    public function delChildExercise( $id ) {

        $whereparent['parent_id'] = $id;
        $childid = $this->model->where($whereparent)->select();
        $ed = $this->model->where($whereparent)->delete();

        $fock = true;
        if (!empty($childid)) {
            foreach ($childid as $k=>$v) {
                $childp['question_id'] = $v['id'];
                $pd = M('exercises_question_processinfo')->where($childp)->delete();
                if( $pd === false) {
                    $fock=false;
                }
            }
        }

        if ( $ed !==false && $fock==true  ) {
            return true;
        } else {
            return false;
        }


    }

    //更新习题信息
    public function save_updateExerciseInfo( $firstdata,$pid ) {
        $where['id'] = $pid;
        $id = $this->model->where($where)->save($firstdata);
        if ($id!==false) {
            return true;
        } else {
            return false;
        }

    }

    //获取下一道待审核习题
    public function getNextExerceseInfo($id) {
        $where['exercises_createexercise.id'] = array('neq',$id);
        $where['exercises_createexercise.status'] = EXERCISE_STATE_WAITVERIFY;
        $where['exercises_createexercise.is_delete'] = STATE_NORMAL;
        $where['exercises_createexercise.parent_id'] = 0;
        $where['exercises_question_processinfo.creator_id'] = array('neq',session('admin.id'));
        $info = $this->model
            ->join("exercises_question_processinfo on exercises_question_processinfo.question_id=exercises_createexercise.id")
            ->where($where)->order('exercises_createexercise.id asc')->find();
        return $info;
    }

    //获取下一道返工待修改习题
    public function getReworkNextExerceseInfo($id) {
        $where['exercises_createexercise.id'] = array('neq',$id);
        $where['exercises_createexercise.status'] = EXERCISE_STATE_DECLINE;
        $where['exercises_createexercise.is_delete'] = STATE_NORMAL;
        $where['exercises_createexercise.parent_id'] = 0;
        $where['exercises_question_processinfo.creator_id'] = array('eq',session('admin.id'));
        $info = $this->model
            ->join("exercises_question_processinfo on exercises_question_processinfo.question_id=exercises_createexercise.id")
            ->where($where)->order('exercises_createexercise.id asc')->find();
        return $info;
    }

    //添加入库数据
    public function addMasterAndSlavEx($masterdata,$slavdata,$id) {

        $this->model->startTrans();
        $whereid['id'] = $id;
        $masterid = $this->model->where($whereid)->save($masterdata);

        $map['exercises_createexercise_id'] = $id;
        $slavid = M('exercises_textbook_tree_info_createexercise')->where($map)->save($slavdata);


        if ( $masterid !== false && $slavid!== false ) {
            $this->model->commit();
            return true;
        } else {
            $this->model->rollback();
            return false;
        }

    }

}