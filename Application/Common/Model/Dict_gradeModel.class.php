<?php
namespace Common\Model;
use Think\Model; 

class Dict_gradeModel extends Model{
    
    public    $model='';
    protected $tableName = 'dict_grade';  
	
    public function __construct(){
        parent::__construct();  
        $this->model=M($this->tableName);
    }
    
    /*
     * 得到一条年级信息
     * id       年级ID
     */ 
    public function getGradeInfo($id){
        $model=$this->model;
        $result=$model->where('id='.$id)->field('id,id grade_id,grade,code')->find();  
        return $result;
    }
    /*
     * 得到年级信息
     */
    public function getGradeList($filter=0){    
        $where=array();
        if($filter!=0){
            $where['id']=array('not in','14,15,16');
        }
        $model=$this->model;
        $result=$model->where($where)->field('id,grade as name')->select();    
        return $result;
    }

    /*
    * 得到年级信息
    */
    public function getAppGradeList($filter=0){

        $where=array();
        if($filter!=0){
            $where['id']=array('not in','14,15,16');
        }
        $model=$this->model;
        $result=$model->where($where)->field('id as value,grade as title')->select();
        return $result;
    }
    
    /*
     * 根据名称获得年级信息
     */
    public function getGradeByName($grade){
        $where['grade']=$grade;
        $result=$this->model->where($where)->field('id,grade')->find();     
        return $result;
    }
    
    public function getAvailableGrade($courseId,$textbookId)
    {
        $where['biz_textbook.has_ebook'] = 1 ;
        $where['biz_textbook.flag'] = 1 ;
        if ($courseId != '')
        {
            $where['biz_textbook.course_id'] = $courseId;
        }
        if ($textbookId != '')
        {
            $where['biz_textbook.school_term'] = $textbookId;
        }
        return M('biz_textbook')->join('dict_grade ON dict_grade.id = biz_textbook.grade_id')->where($where)->group('dict_grade.id')->field('dict_grade.code,dict_grade.grade')->select();
    }

    public function getTeacherGrade($teacherId)
    {
        $model = M('auth_teacher_second');
        $where['teacher_id'] = $teacherId;
        return $model->where($where)
            ->join('dict_grade ON dict_grade.id = auth_teacher_second.grade_id')
            ->group('dict_grade.id')
            ->field('dict_grade.id,dict_grade.grade name')
            ->select();
    }

    public function getAvailableRegGrade($courseId)
    {
       $result = M()->query("SELECT dict_grade.id,dict_grade.grade name FROM dict_coursegrade 
                             JOIN dict_grade ON dict_grade.id = dict_coursegrade.grade_id AND dict_coursegrade.course_id = $courseId ");
       return $result;
    }
    /*
    * 得到年级
    */
    public function getGradeListBySchool($where=''){
        /*if($filter!=0){
            $where['id']=array('not in','14,15,16');
        }*/
        $result=$this->model
            ->field('dict_grade.id,grade,count(biz_class.id) count')
            ->join('biz_class on biz_class.grade_id=dict_grade.id')
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id', 'left')
            ->group('dict_grade.id')
            ->where($where)
            ->select();
        return $result;
    }
}