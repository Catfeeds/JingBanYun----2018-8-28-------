<?php
namespace Common\Model;
use Think\Model; 

class Dict_course_copy_resourceModel extends Model{
    
    public    $model='';
    protected $tableName = 'dict_course_copy_resource';
	
    public function __construct(){
        parent::__construct();  
        $this->model=M($this->tableName);
    }
    
    /*
     * 得到学科信息
     */
    public function getCourseList(){
        $model=$this->model;
        $result=$model->field('id,course_name as name')->select();
        return $result;
    }
    public function getCourseInfo($id)
    {
        return $this->model->where('id='.$id)->field('id,id course_id,course_name,course_name course')->find();
    }
    public function getAvailableCourse($gradeId,$textbookId)
    {
        $where['biz_textbook.has_ebook'] = 1 ;
        $where['biz_textbook.flag'] = 1 ;
        if ($gradeId != '')
        {
            $where['biz_textbook.grade_id'] = $gradeId;
        }
        if ($textbookId != '')
        {
            $where['biz_textbook.school_term'] = $textbookId;
        }
        return M('biz_textbook')->join('dict_course_copy_resource ON dict_course.id = biz_textbook.course_id')->where($where)->group('dict_course_copy_resource.id')->order('dict_course_copy_resource.sort_order asc')->field('dict_course_copy_resource.id,dict_course_copy_resource.course_name')->select();
    }
    
    /*
     * 根据名称获得学科信息
     */
    public function getCourseData($course){
        $where['course_name']=$course;
        $result=$this->model->where($where)->field('id,course_name')->find();
        return $result;
    }

}