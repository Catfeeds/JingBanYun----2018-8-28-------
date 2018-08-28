<?php
namespace Common\Model;
use Think\Model; 

class Biz_class_studentModel extends Model{
    public    $model='';
    protected $tableName = 'biz_class_student';  
	
    public function __construct(){
        parent::__construct();  
        $this->model=M($this->tableName);
    }
     
    /*
     * 得到学生和班级的信息
     * id       学生ID   
     */ 
    public function getStudentClass($id,$start=0,$end=0){
        $model=$this->model;
        if($end==0){
            $result=$model->where('status=2 and student_id='.$id)->field('class_id,student_id')->select(); 
        }else{
            $result=$model->where('status=2 and student_id='.$id)->field('class_id,student_id')->limit($start,$end)->select(); 
        }  
        return $result;
    }
    
    /*
     * 得到学生和班级的信息
     * ids      学生ids
     */
    public function getStudentClassInfo($ids){
        $model=$this->model;
        $result=$model->where('status=2 and student_id in('.$ids.')')->field('class_id,student_id')->select();  
        return $result;
    }
    
    
    /*
     * 得到学生的信息和班级信息,以学生为主
     * ids       学生ID(字符串)  
     */ 
    public function getStudentClassData($ids){
        $model=$this->model; 
        $result=$model->where('auth_student.id in('.$ids.')')
                        ->join('auth_student on biz_class_student.student_id=auth_student.id','right')
                        ->join('biz_class on biz_class.id=biz_class_student.class_id and status=2','left')
                        ->join('auth_teacher on auth_teacher.id=biz_class.class_teacher_id')
                        ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
                        ->join('dict_grade on dict_grade.id=biz_class.grade_id') 
                        ->field('auth_student.id student_id,auth_student.student_name,biz_class.id class_id,biz_class.name class_name,'
                                . 'biz_class.student_count,biz_class.class_teacher_id,auth_student.school_id,biz_class.grade_id,'
                                . 'auth_teacher.name teach_name,dict_schoollist.school_name,dict_grade.grade')
                        ->group('auth_student.id,biz_class.id')
                        ->select();     
        return $result;
    }
    
    
    /*
     * 根据班级id得到信息
     * id       班级id
     */
    public function getClassInfo($id){
        $model=$this->model;
        $result=$model->where('status=2 and class_id='.$id)->field('class_id,student_id')->select();    //echo $model->getLastsql();die;
        return $result;
    }
    
    
    /*
     * 根据班级ids得到信息
     * ids      班级ids(字符串 班级分组)
     */
    public function getClassData($ids,$start=0,$end=0){ 
        $model=$this->model;
        if($end!=0){
            $result=$model->where('status=2 and class_id in('.$ids.')')->field('class_id,student_id')->limit($start,$end)->group("class_id")->select();
        }else{
            $result=$model->where('status=2 and class_id in('.$ids.')')->field('class_id,student_id')->group("class_id")->select();
        }
        return $result;
    }

    /*
     * 添加一条班级学生数据
     * class_id     班级id
     * student_id   学生id
     */
    public function addClassStudent($class_id,$student_id){
        $model=$this->model;
        $data['class_id']=$class_id;
        $data['student_id']=$student_id;
        $data['create_at']=time(); 
        $data['status']=2; //addClassStudent
        $class_model=D('Biz_class');
        $class_model->addClassStudent($class_id);
        if($model->add($data)){     
            return true;
        }else{
            return false;
        }
    }
     
    /*
     * 得到某个班级下的学生
     */
    public function getClassStudent($id){
        $Model = M('biz_class_student');
        $result = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->join('biz_class on biz_class.id=biz_class_student.class_id')
            ->field('auth_student.*,biz_class_student.class_id')
            ->where("biz_class_student.class_id=$id and biz_class_student.status=2")
            ->order('auth_student.student_name asc') 
            ->select();
        return $result;
    }

    public function getClassStudentAll($id){

        $Model = M('biz_class_student');
        $map['biz_class_student.status'] = 2;
        $map['biz_class_student.class_id'] = array('in',$id);
        $result = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->join('biz_class on biz_class.id=biz_class_student.class_id')
            ->field('auth_student.*,biz_class_student.class_id')
            ->where($map)
            ->order('auth_student.student_name asc')
            ->select();
        return $result;
    }

    /*
     * 得到某个班级下的学生的家长
     */
    public function getClassStudentParent($id)
    {
        $Model = M('biz_class_student');
        $result = $Model
            ->join('auth_student on auth_student.id=biz_class_student.student_id')
            ->join('auth_parent on auth_parent.telephone=auth_student.parent_tel')
            ->join('biz_class on biz_class.id=biz_class_student.class_id')
            ->field('auth_parent.id,auth_student.student_name')
            ->where("biz_class_student.class_id=$id and biz_class_student.status=2")
            ->select();
        return $result;

    }
    //老师的学习轨迹
    public function teachStudy($id){
        $Model = M('biz_homework_student_details');

        $c1['biz_homework.class_id'] = $id;
        $result = $Model
            ->join('biz_homework on biz_homework.id=biz_homework_student_details.homework_id')
            ->join('auth_student on auth_student.id=biz_homework_student_details.student_id')
            ->field("biz_homework.id as homework_id,biz_homework.homework_name,biz_homework_student_details.points,auth_student.student_name,auth_student.id as student_id")
            ->where($c1)
            ->order('biz_homework.create_at asc')->select();   
        return $result;
    }
  
    /*
     * 家长的学习轨迹
     * ids      学生ids
     */
    public function parentStudy($ids){ 
        $Model = M('biz_homework_student_details');
        $c1['_string'] ="auth_student.id in (".$ids.')';
        $result = $Model
            ->join('biz_homework on biz_homework.id=biz_homework_student_details.homework_id')
            ->join('auth_student on auth_student.id=biz_homework_student_details.student_id')
            ->field("biz_homework.id as homework_id,biz_homework.homework_name,biz_homework_student_details.points,auth_student.student_name,auth_student.id as student_id")
            ->where($c1)
            ->order('biz_homework.create_at asc')->select();
        return $result;
    }

    /*
     * 查看学生是否在此班
     */
    public function getStudentIsExistsInClass($studentId,$classId)
    {
        $result = M()->query("SELECT 1 FROM biz_class_student WHERE class_id = $classId AND student_id = $studentId AND status =".STUDENT_JOINSTATE_NORMAL);
        return !empty($result);
    }

    /*
     * 获取学生数量
     */
    public function getStudentCount($classId)
    {
        $result = M()->query("SELECT COUNT(1) count FROM biz_class_student WHERE class_id = $classId  AND status =".STUDENT_JOINSTATE_NORMAL);
        return $result[0]['count'];
    }

    /*
     * 根据班级ID获取所有学生ID及其家长ID
     *
     */
    public function getStudentIdParentIdByClassId($classId='')
    {
        if(empty($classId))
            return array();
        $stateSetResult = M()->execute('SET SESSION group_concat_max_len=102400;');
        if(false === $stateSetResult)
            return false;
        $studentResult = M()->query("SELECT GROUP_CONCAT(DISTINCT student_id separator ',') studentid FROM biz_class_student WHERE class_id IN ($classId) AND status = ".STUDENT_JOINSTATE_NORMAL);
        $parentResult = M()->query("SELECT auth_student_parent_contact.parent_id id, auth_student.student_name name FROM biz_class_student 
                                    JOIN auth_student_parent_contact ON auth_student_parent_contact.student_id = biz_class_student.student_id
                                    JOIN auth_student ON auth_student.id = biz_class_student.student_id
                                    WHERE class_id IN ($classId) AND status = ".STUDENT_JOINSTATE_NORMAL);
        return array('studentId'=>array_column($studentResult,'studentid')[0],'parentStudentName'=>$parentResult);
    }
    //根据学生id获取家长id
    public function getStudentParentId($student_id) {
        $result = M()->query("SELECT parent_id FROM auth_student_parent_contact WHERE student_id = $student_id");
        return $result;
    }
}
 