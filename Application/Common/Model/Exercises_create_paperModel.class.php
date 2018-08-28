<?php
/**
 * Created by PhpStorm.
 * User: GuoMin
 * Date: 2017/9/7
 * Time: 11:42
 */
namespace Common\Model;
use Think\Model;



class Exercises_create_paperModel extends Model
{
    public    $model='';
    protected $tableName = 'exercises_create_paper';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }
    /*
     *查询所有不同年份
     */
    public function getDistinctYear()
    {
        $result = M()->query("SELECT DISTINCT YEAR FROM exercises_create_paper WHERE is_delete = ". STATE_NORMAL." order by YEAR ASC");
        return $result;
    }

    /*
     * 删除试卷
     */
    public function deletePaper($id)
    {
        $id = intval($id);
        $result = M()->execute("UPDATE exercises_create_paper SET is_delete = ".STATE_DELETED ." WHERE id=$id");
        if(false === $result)
            return false;
        return true;
    }

    //添加试卷
    public function addPaper( $data ) {
        $id = $this->model->add( $data );
        return $id;
    }

    //获取试卷
    public function getPaperInfo( $paperid,$field="exercises_create_paper.*,dict_grade.grade grade_name,exercises_course.name course_name,dict_citydistrict.name province_name" ) {
        $where['exercises_create_paper.id'] = $paperid;
        $id = $this->model->join('LEFT JOIN dict_grade ON dict_grade.id = exercises_create_paper.grade')
            ->join('LEFT JOIN exercises_course ON exercises_course.id = exercises_create_paper.subject')
            ->join('LEFT JOIN dict_citydistrict ON dict_citydistrict.id = exercises_create_paper.city_id')
            ->where($where)->field($field)->find( );
        return $id;
    }

    //修改试卷
    public function updatePaper($id,$data) {
        $where['id'] = $id;
        $id = $this->model->where($where)->save( $data );
        if ( $id !== false) {
            return true;
        }else {
            return false;
        }
    }

    //修改试卷的总分
    public function updatePaperCountScore( $paper_id ){
        $where['exercises_parper_concat.paper_id'] = $paper_id;
        $list = M('exercises_parper_concat')
            ->join('LEFT JOIN exercises_createexercise ON exercises_createexercise.id = exercises_parper_concat.exercise_id')
            ->where( $where )
            ->field('exercises_createexercise.count_score')
            ->select();
        if (!empty($list)) {
            $num_count=0;
            foreach ($list as $k=>$v) {
                $num_count += $v['count_score'];
            }

            if ( $num_count > 0) {
                $map['id'] = $paper_id;
                $data['score'] = $num_count;
                $is_id = M('exercises_create_paper')->where($map)->save( $data );
                if ( $is_id !== false) {
                    return true;
                }else {
                    return false;
                }
            }
        } else {
            return true;
        }

    }
}