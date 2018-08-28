<?php
namespace Home\Controller;
use Common\Common\simple_html_dom;
class ParentHomeworkController extends PublicController{
    private $exerciseConfig;
	public function __construct(){
        $this->exerciseConfig = require($_SERVER['DOCUMENT_ROOT'].'/Application/Exercise/Conf/createExercise.php');
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
        $this->assign('navicon', 'zuoyexitong');
        require($_SERVER['DOCUMENT_ROOT'].'/Application/Exercise/Conf/const.php');
    }

    public function index()
    {
        $this->display();
    }

    public function homeworkNotSubmit()
    {
        $homeworkId = getParameter('homeworkId','int');
        $classId = getParameter('classId','int');
        $studentId = getParameter('userId','int');
        $submitId = D('Exercises_student_homework')->getSubmitIdByHomeworkIdClassIdStudentId($studentId,$homeworkId,$classId);
        if($submitId){
            redirect(U('ParentHomework/homeworkSubmit').'&submitId='.$submitId);
        }
        $this->display();
    }


}
