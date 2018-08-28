<?php
namespace ApiInterface\Controller\Version1_1;
use Think\Controller;

class StudyController extends PublicController
{
    public $model='';
    public $page_size=20;
    public $firstRow=0;
    public $listRow=0;

    public function __construct() {
        parent::__construct();
        $this->model=D('Biz_homework');
    }
    /**
     * @描述：教师学习轨迹
     * @参数：id[int] N 班级ID
     * @返回值：HTML网页
     */
    public function teachStudyList(){
        $id =getParameter('id','int');
        if(!$id){
            //$this->showjson(-1,'failed',array());
            $this->assign('classId', null);
            $this->display();die;
        }
        $model=D('Biz_class_student');
        $result=$model->teachStudy($id);
        $this->assign('data_exists', $result);
        $this->assign('classId', $id);
        $this->display();
    }

    /**
     * @描述：家长学习轨迹
     * @参数：id[int] N 家长ID
     * @返回值：HTML网页
     */
    public function parentStudyList(){
        $id =getParameter('id','int');
        $message_id=getParameter('message_id','int',false);
        $role = getParameter('role','int',false);
        $userId = getParameter('userId','int',false);
        
        if(!$id){
             $this->assign('student_id', null);
            $this->display();die;
        }
        $parent_model=D('Auth_parent');
        $parent_result=$parent_model->getParentInfo($id);
        if(empty($parent_result)){
             $this->assign('student_id', null);
            $this->display();die;
        }
        $student_model=D('Auth_student');
        $student_info=$student_model->getParentStudents($id);
        if(empty($student_info)){
             $this->assign('student_id', null);
            $this->display();die;
        }
        if($message_id && $role && $userId){
            $read_status=2;
            $message_model=D('Message');
            $message_model->setMessageReadState($message_id,$userId,$role,$read_status);
        }
            
        $student_ids='';
        foreach($student_info as $v){
            $student_ids.=$v['id'].',';
        }
        $student_ids=rtrim($student_ids,',');

        //$result
        $model=D('Biz_class_student');
        $result=$model->parentStudy($student_ids);
        $this->assign('data_exists', $result);

        $this->assign('student_id', $student_ids);
        $this->display();

    }

    /**
     * @描述：活动表现网页
     * @参数：student_id[int] N 学生ID
     * @参数：class_id[int] N 班级ID
     * @返回值：HTML网页
     */
    public function learningPath()
    {
        $studentId=getParameter('student_id', 'int');
        $classId=getParameter('class_id', 'int',false,2);

        $this->assign('classId', $classId);

        $Model = M('biz_student_learning_path');

        $path = $Model
            ->join('biz_class on biz_class.id=biz_student_learning_path.class_id')
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_student_learning_path.*,dict_schoollist.school_name,dict_grade.grade,biz_class.name as class_name')
            ->where("biz_student_learning_path.student_id=$studentId")
            ->order("biz_student_learning_path.create_at desc")
            ->select();

        $this->assign('list', $path);
        $StudentModel = M('auth_student');
        $student = $StudentModel
            ->where("id=$studentId")
            ->find();
        $this->assign('student', $student);
        $this->display();
    }

}