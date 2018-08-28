<?php
namespace Common\Model;
use Think\Model;

class Exercises_paper_bigquestionModel extends Model{

    public    $model='';
    protected $tableName = 'exercises_paper_bigquestion';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }



    //添加大题题号
    public function addPaperBigQuestion( $data ) {
        $id = $this->model->add( $data );
        return $id;
    }

    //根据试卷id获取所有的大题列表
    public function getPaperBigQuestion( $paperid ) {
        $where['paper_id'] = $paperid;
        $list = $this->model->where( $where )->select();
        return $list;
    }

    //更新小题的数量
    public function updateParperBigSetNum($paper_id,$big_paper_id) {
        $where['paper_id'] = $paper_id;
        $where['id'] = $big_paper_id;
        $this->model->where( $where )->setInc('big_topic_num',1);
    }

    //删除所有的大题
    public function deleteBigQuestion($paper_id) {
        $where['paper_id'] = $paper_id;
        $id = $this->model->where( $where )->delete();
        if ($id !== false) {
            return true;
        } else {
            return false;
        }
    }

    //修改大题的内容
    public function updatePaperBigQuestion( $big_question_id,$bigdata ) {
        $where['id'] = $big_question_id;

        $id = $this->model->where( $where )->save( $bigdata );
        if ($id !== false) {
            return true;
        } else {
            return false;
        }
    }

    //更新小题的数量
    public function updateParperBigReduceNum($bigid) {
        $where['id'] = $bigid;
        $id = $this->model->where( $where )->setDec('big_topic_num');
        if ( $id !== false) {
            return true;
        } else {
            return false;
        }

    }

}