<?php
namespace Common\Model;
use Think\Model; 

class Dict_courseModel extends Model{
    
    public    $model='';
    protected $tableName = 'dict_course';
	
    public function __construct(){
        parent::__construct();  
        $this->model=M($this->tableName);
    }
    
    /*
     * 得到学科信息
     */
    public function getResourceCourseList(){
        $model=M('dict_course_copy_resource');
        $result=$model->field('id,course_name as name')->select();
        return $result;
    }

    public function getCourseList(){
        $model=$this->model;
        $result=$model->field('id,course_name as name')->select();
        return $result;
    }

    public function getAppCourseList(){

        $model=$this->model;
        $result=$model->field('id as value,course_name as title')->select();
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
        return M('biz_textbook')->join('dict_course ON dict_course.id = biz_textbook.course_id')->where($where)->group('dict_course.id')->order('dict_course.sort_order asc')->field('dict_course.id,dict_course.course_name')->select();
    }
    
    /*
     * 根据名称获得学科信息
     */
    public function getCourseData($course){
        $where['course_name']=$course;
        $result=$this->model->where($where)->field('id,course_name')->find();
        return $result;
    }

    public function refreshCourseGradeInfo($courseGradeTable)
    {
        //delete old table
        M()->execute("TRUNCATE TABLE dict_coursegrade");
        $courseList = M()->query("SELECT id,course_name FROM dict_course");
        $insertValueArray = array();
        foreach($courseList as $key=>$course)
        {
            $bIsFind = false;
            $courseId =  $course['id'];
            foreach($courseGradeTable as $courseName => $data) {
                if (strpos($courseName,$course['course_name']) !== false) {
                   $bIsFind = true;
                   for($i=$data['startGrade'];$i<=$data['endGrade'];$i++)
                   {
                       $insertValueArray[] = "($i,$courseId)";
                   }
                   foreach(explode(',',$data['additional']) as $i => $gradeId)
                   {
                       $insertValueArray[] = "($gradeId,$courseId)";
                   }
                   break;
                }
            }
            if(!$bIsFind)
            {
                $allGradeList = array(1,2,3,4,5,6,7,8,9,10,11,12,14,15,16);
                foreach($allGradeList as $i => $gradeId)
                {
                   // $insertValueArray[] = "($gradeId,$courseId)";
                }
            }
        }
        $result = M()->execute("INSERT INTO dict_coursegrade (grade_id,course_id) VALUES ".implode(',',$insertValueArray));
        if(false === $result)
        {
            return false;
        }
        return true;
    }
/*
 *教师分享用学科根据已分享的资源进行反查
 */
    public function getCourseListByReverseQuery($user_id='',$role=''){
        $where = 'biz_resource.status ='.TEACHER_SHARE_STATUS;
        $model=$this->model;
        $join="biz_resource_collect collect_ on biz_resource.id=collect_.resource_id AND collect_.user_id = $user_id " .' and collect_.user_type='.($role-ROLE_NUMBER);
        $result=$model
            ->join('biz_resource on biz_resource.course_id = dict_course.id')
            ->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
            ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id')
            ->join('dict_citydistrict on dict_citydistrict.id=dict_schoollist.district_id')
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
            ->join('dict_grade on dict_grade.id=biz_resource.grade_id')
            ->join($join,'left')
            ->field('dict_course.id,dict_course.course_name as name')
            ->where($where)
            ->group('dict_course.id')
            ->select();
        return $result;
    }
}