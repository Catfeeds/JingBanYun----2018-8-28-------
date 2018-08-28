<?php
namespace Home\Controller;

use Think\Controller;

class DictRestController extends PublicController
{
    public function get_textbooks_by_courseAndGrade(){

        $courseId = $_GET['course_id'];
        $gradeId = $_GET['grade_id'];

        $TextbookModel = M('biz_textbook');

        $c1['course_id'] = $courseId;
        $c1['grade_id'] = $gradeId;

        $result = $TextbookModel->where($c1)->order('school_term asc')->select();

        $this->ajaxReturn($result);
    }

    public function get_exercise_template_by_course(){

        $courseId = $_GET['course_id'];

        $Model = M('biz_exercise_template');

        $c1['course_id'] = $courseId;

        $result = $Model->where($c1)->select();

        $this->ajaxReturn($result);
    }

    public function get_exercise_template_by_id(){

        $id = $_GET['id'];

        $Model = M('biz_exercise_template');

        $c1['id'] = $id;

        $result = $Model->where($c1)->find();

        $this->ajaxReturn($result);
    }

    public function getAllSchools()
    {
      $this->ajaxReturn(array('status' => 200,'result' =>D('Dict_schoollist')->getAllSchool()));
    }

    public function getCourses()
    {
      $this->ajaxReturn(array('status' => 200,'result' =>D('Dict_course')->getCourseList()));
    }

    public function getGrades()
    {
      $this->ajaxReturn(array('status' => 200,'result' =>D('Dict_grade')->getGradeList()));
    }

}