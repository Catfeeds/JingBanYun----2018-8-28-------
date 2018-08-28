<?php
/**
 * Created by PhpStorm.
 * User: GM
 * Date: 2018/3/30
 * Time: 14:44
 */

namespace Common\Model;


use Think\Model;

class Direct_trainModel extends Model
{
    public $model;
    protected $tableName = 'direct_train';

    public function __construct()
    {
        parent::__construct();
        $this->model = M($this->tableName);
    }

    /**
     *描述：查询所有
     */
    public function getModelAllList($where = [], $field, $pageIndex = 1, $pageSize = 20, $map, $join = '', $order = '', &$count)
    {
        /*$bjQuerySQL = " SELECT ".$field." FORM direct_train where ppid>0 group by ppid";
        echo $bjQuerySQL;die();*/
        $map['ppid'] = array('gt', 0);
        $subQuery = $this->model
            ->field("direct_train.ppid")
            ->join('left join dict_course ON dict_course.id = direct_train.course_id')
            ->join('left join dict_grade ON dict_grade.id = direct_train.grade_id')
            ->join('left join auth_teacher ON auth_teacher.id = direct_train.special_column_editor_quizzer_id')
            ->where($map)
            ->group("direct_train.ppid")
            ->select(false);

        //进行子查询得到问题id
        $setQuery = $this->model
            ->field($field)
            ->join('left join dict_course ON dict_course.id = direct_train.course_id')
            ->join('left join dict_grade ON dict_grade.id = direct_train.grade_id')
            ->join('left join auth_teacher ON auth_teacher.id = direct_train.special_column_editor_quizzer_id')
            ->join($join)
            ->where("direct_train.id in " . "($subQuery)")
            ->order($order)
            ->select(false);

        //进行union 得到所有的结果
        $result = $this->model
            ->field($field)
            ->join('left join dict_course ON dict_course.id = direct_train.course_id')
            ->join('left join dict_grade ON dict_grade.id = direct_train.grade_id')
            ->join('left join auth_teacher ON auth_teacher.id = direct_train.special_column_editor_quizzer_id')
            ->join($join)
            ->where($where)
            ->union($setQuery)
            ->select(false);
//echo $result;die;
        $resultCount = M()->query($result);
        $count = count($resultCount);
        $size = ($pageIndex - 1) * $pageSize;
        $result = M()->query($result . " limit $size,$pageSize");

        return $result;
    }

    /**
     *描述：查找所有专栏有分页
     */
    public function getSpecialColumnAll($where = [], $field, $pageIndex = 1, $pageSize = 20, $join = '', $order = '')
    {
        if (empty($join)) {
            $join = '';
        }
        if (empty($order)) {
            $order = '';
        }
        $result = $this->model
            ->field($field)
            ->join('left join dict_course ON dict_course.id = direct_train.course_id','left')
            ->join('left join dict_grade ON dict_grade.id = direct_train.grade_id','left')
            ->join('auth_teacher ON auth_teacher.id = direct_train.special_column_editor_quizzer_id')
            ->join($join)
            ->where($where)
            ->order($order)
            ->page($pageIndex, $pageSize)
            ->select();
//echo M()->getLastSql();die;
        return $result;
    }

    public function getSpecialColumnPageList($where = [], $field, $page, $limit,$order="") //sort_by_putaway_status_time DESC,direct_train.putaway_status_time DESC
    {
        $result = $this->model
            ->field($field)
            ->join('left join dict_course ON dict_course.id = direct_train.course_id')
            ->join('left join dict_grade ON dict_grade.id = direct_train.grade_id')
            ->join('auth_teacher ON auth_teacher.id = direct_train.special_column_editor_quizzer_id')
            ->where($where)
            ->limit($limit)
            ->page($page)
            ->limit($limit)
            ->order($order)
            ->select();
        return $result;
    }

    /**
     *描述：查找已购专栏
     */
    public function getPurchaseSpecialColumnAll($where = [], $field, $pageIndex = 1, $pageSize = 20)
    {
        $result = $this->model
            ->field($field)
            ->join('left join dict_course ON dict_course.id = direct_train.course_id')
            ->join('left join dict_grade ON dict_grade.id = direct_train.grade_id')
            ->join('auth_teacher ON auth_teacher.id = direct_train.special_column_editor_quizzer_id')
            ->join('order_info ON order_info.resources_id = direct_train.id')
            ->where($where)
            ->page($pageIndex, $pageSize)
            ->order()
            ->select();
//echo M()->getLastSql();die;
        return $result;
    }

    /**
     *描述：查找一个专栏
     */
    public function getSpecialColumnOne($where = [], $join = '', $limit = '', $order = '', $field = 'direct_train.*,dict_grade.grade,dict_course.course_name,dict_course.code,auth_teacher.name,auth_teacher.avatar')
    {
        if (empty($join)) {
            $join = '';
        }
        if (empty($limit)) {
            $limit = '';
        }
        if (empty($order)) {
            $order = '';
        }
        $result = $this->model
            ->field($field)
            ->join('dict_course ON dict_course.id = direct_train.course_id', 'left')
            ->join('dict_grade ON dict_grade.id = direct_train.grade_id', 'left')
            ->join('auth_teacher ON auth_teacher.id = direct_train.special_column_editor_quizzer_id')
            ->join($join)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->select();
        return $result;
    }

    /*
     *描述：获取专栏的详情
     */
    public function SpecialColumnDetails($where, $join = '', $filed = 'direct_train.*,dict_course.course_name course_name,resource_path,vid,auth_teacher.name teacher_name,avatar,vid_fullpath')
    {
        $result = $this->model
            ->field($filed)
            ->join('left join dict_course ON dict_course.id = direct_train.course_id', 'left')
            ->join('auth_teacher ON auth_teacher.id = direct_train.special_column_editor_quizzer_id')
            ->join('special_column_file_concat ON special_column_file_concat.special_column_id = direct_train.id', 'left')
            ->join($join)
            ->where($where)
            ->find();
        return $result;
    }

    /**
     *描述：添加专栏
     */
    public function addSpecialColumn($data)
    {
        return $this->model
            ->add($data);
    }

    /**
     *描述：修改专栏
     */
    public function updateSpecialColumn($data, $where = [])
    {
        return $this->model
            ->where($where)
            ->save($data);
    }

    /**
     *描述：删除专栏
     */
    public function deleteSpecialColumn()
    {

    }

    /**
     *描述：查询问题
     */
    public function getChildReply($map = null)
    {
        $list = $this->model
            ->field('direct_train.*,auth_teacher.name,auth_teacher.avatar,dict_course.course_name,aut.name originatorName,auth_teacher.sex,dict_grade.grade')
            ->join('auth_teacher ON auth_teacher.id = direct_train.special_column_editor_quizzer_id')
            ->join('left join auth_teacher aut ON aut.id = direct_train.quizzer_replier_concat_id')
            ->join('left join dict_course ON dict_course.id = direct_train.course_id')
            ->join('left join dict_grade ON dict_grade.id = direct_train.grade_id')
            ->where($map)->order('putaway_status_time asc')->select();
        return $list;
    }

    public function getDetails($map = null)
    {
        return $this->model->where($map)->find();
    }

    public function getDetailsInfo($map = null)
    {
        return $this->model
            ->join('left join dict_course ON dict_course.id = direct_train.course_id')
            ->join('left join dict_grade ON dict_grade.id = direct_train.grade_id')
            ->join('auth_teacher ON auth_teacher.id = direct_train.special_column_editor_quizzer_id')
            ->where($map)
            ->field("special_column_editor_quizzer_id,dict_course.id as course_id,direct_train.fascicule_id,direct_train.id,auth_teacher.avatar,direct_train.special_column_price,direct_train.special_column_question_title,direct_train.special_column_question_visit_count,direct_train.creat_time,auth_teacher.name,dict_course.course_name,dict_grade.grade,direct_train.special_column_question_reply_description,sex,pid,ppid,direct_train.special_column_type")
            ->find();
    }


    public function getDetailsInfoAll($map = null,$orderwhere=null)
    {
        return $this->model
            ->join('left join dict_course ON dict_course.id = direct_train.course_id')
            ->join('left join dict_grade ON dict_grade.id = direct_train.grade_id')
            ->join('left join auth_teacher ON auth_teacher.id = direct_train.special_column_editor_quizzer_id')
            ->join('left join order_info ON order_info.resources_id = direct_train.id'.$orderwhere)
            ->join('left join special_column_file_concat ON special_column_file_concat.special_column_id = direct_train.id')
            ->where($map)
            ->field("special_column_price,special_column_question_reply_description,special_column_file_concat.vid,direct_train.special_column_article_show,special_column_article,order_info.id as oid,special_column_type,special_column_editor_quizzer_id,dict_course.id as course_id,direct_train.fascicule_id,direct_train.id,auth_teacher.avatar,direct_train.special_column_price,direct_train.special_column_question_title,direct_train.special_column_question_visit_count,direct_train.creat_time,auth_teacher.name,dict_course.course_name,dict_grade.grade,direct_train.phase_of_studying_id,vid_fullpath")
            ->find();

    }

    /*
     *描述：往关联表中写数据
     */
    public function insertIntoFileConcat($data)
    {
        return M('special_column_file_concat')
            ->add($data);
    }

    /*
     *描述：查询关联表中数据
     */
    public function getDataForFileConcat($where)
    {
        return M('special_column_file_concat')
            ->where($where)
            ->select();
    }

    /*
    *描述：删除关联表中数据
    */
    public function deleteDataForFileConcat($where)
    {
        return M('special_column_file_concat')
            ->where($where)
            ->delete();
    }

    /*
     *描述：获取当前问题下的回复下的所有回复和问题
     */
    public function getReplyAndQuestionAll($where)
    {
        $result = $this->model
            ->field('direct_train.*,auth_teacher.name passiveName,aut.name originatorName,aut.avatar')
            ->join('auth_teacher ON auth_teacher.id = direct_train.quizzer_replier_concat_id')
            ->join('auth_teacher aut ON aut.id = direct_train.special_column_editor_quizzer_id')
            ->where($where)
            ->order('question_reply_concat_id,putaway_status_time desc')
            ->select();
        return $result;
    }

    /*
     *描述：问题浏览量和专栏观看数量和问题回复数自增
     */
    public function visitAndReplyNumberSetInc($where, $filed)
    {
        $status = $this->model
            ->where($where)
            ->setInc($filed);
        return $status;
    }

    /*
     *描述：专栏详情
     */
    public function details($where)
    {
        $result = $this->model
            ->field('special_column_file_concat.*,putaway_status_time,special_column_question_visit_count,special_column_price,special_column_question_reply_description')
            ->join('special_column_file_concat ON special_column_file_concat.special_column_id = direct_train.id', 'left')
            ->where($where)
            ->find();
        return $result;
    }

    /*
     *描述：浏览时间关联表的查询
     */
    public function getDataFormVisitTime($where)
    {
        return M('direct_train_visit_time_concat')->where($where)
            ->select();
    }

    /*
     *描述：浏览时间关联表的数据删除
     */
    public function deleteDataFormVisitTime($where)
    {
        return M('direct_train_visit_time_concat')->where($where)
            ->delete();
    }

    /*
     *描述：浏览时间关联表的数据添加
     */
    public function addDataFormVisitTime($data)
    {
        return M('direct_train_visit_time_concat')
            ->add($data);
    }

    /*
     *描述：浏览时间关联表的数据修改
     */
    public function saveDataFormVisitTime($data, $where)
    {
        return M('direct_train_visit_time_concat')
            ->where($where)
            ->save($data);
    }
}