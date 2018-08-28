<?php
namespace Common\Model;
use Think\Model; 

define('OPERATION_CONDITION_OPEN',TRUE);
define('OPERATION_CONDITION_CLOSE',FALSE);


 

class Exercise_libraryModel extends Model{     
    
    public    $model='';
    protected $tableName = 'Biz_exercise_library';  
	
    public function __construct(){  
        parent::__construct();  
        $this->model=M($this->tableName); 
    }
    
    /*
     * 得到所有类型,有多少习题
     */
    public function getAllExerciseCount(){
        $data=$this->getExerciseCategory();
        $exercise_type_number_arr=array();
        $result=$this->getExerciseCount(OPERATION_CONDITION_CLOSE); 
        $exercise_type_number_arr[]=array('key'=>'习题资源总数','value'=>$result);      //筛选出的习题资源总数
        
        foreach($data as $val){
            $result=$this->getExerciseCount(OPERATION_CONDITION_OPEN,$val['template_name']);     
            $exercise_type_number_arr[]=array('key'=>$val['template_name'],'value'=>$result);
        } 
        return $exercise_type_number_arr;
    }
      
    
    /*
     * 获得所有习题的类型
     */
    public function getExerciseCategory(){
        $model=M('biz_exercise_template');
        $data=$model->field('id,template_name')->group('template_name')->select(); 
        return $data;
    }
    
    /*
     * 获得习题的数量
     */
    public function getExerciseCount($operation_condition=false,$exercise_type=''){  
        if($operation_condition==false){ 
            $data=$this->model->join('biz_exercise_library_chapter on biz_exercise_library_chapter.id=biz_exercise_library.chapter_id')->count('biz_exercise_library.id');     
        }else{
            $where['template_name']=$exercise_type;
            $data=$this->model->where($where)->join('biz_exercise_library_chapter on biz_exercise_library_chapter.id=biz_exercise_library.chapter_id')
                              ->join("biz_exercise_template on biz_exercise_template.id=biz_exercise_library.type")
                       ->count('biz_exercise_library.id');  
        }
        return $data;
    }
    
    
    /*
     * 按照条件查询出习题的数量
     */
    public function getExerciseInfoCount($condition=array()){ 
            $result = M('biz_exercise_library_chapter')
                ->join("biz_exercise_library on biz_exercise_library.chapter_id=biz_exercise_library_chapter.id")
                ->join("dict_grade on dict_grade.id=biz_exercise_library_chapter.grade_id")
                ->join("dict_course on dict_course.id=biz_exercise_library_chapter.course_id")
                ->join("biz_textbook on biz_textbook.id=biz_exercise_library_chapter.textbook_id")
                ->join("biz_exercise_template on biz_exercise_template.id=biz_exercise_library.type")
                ->field("count(biz_exercise_library.id) count")
                ->where($condition) 
                ->find();  
        return $result;
    }
    
    
    /*
     * 获得某一个习题类型的数据
     */
    public function getExerciseTemplateInfo($id){
        $model=M('biz_exercise_template');
        $where['id']=$id;
        $result=$model->where($where)->field('id,template_name')->find();
        return $result;
    }
}