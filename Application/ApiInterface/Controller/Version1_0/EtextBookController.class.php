<?php
namespace ApiInterface\Controller\Version1_0;
use Think\Controller;

class EtextBookController extends PublicController
{
    public function __construct() {
        parent::__construct();
        header("Content-type: text/html; charset=utf-8");
    }
    public function getETextBook()
    {
        if(!empty($_POST['course']))
            $check['biz_textbook.course_id'] = $_POST['course'];
        if(!empty($_POST['grade']))
            $check['biz_textbook.grade_id'] = $_POST['grade'];
        if(!empty($_POST['textbook']))
            $check['biz_textbook.school_term'] = $_POST['textbook'];
        if(!empty($_POST['keyword']))
            $check['biz_textbook.name'] = array('like','%'.urldecode($_POST['keyword']).'%');
        $check['_string'] = 'biz_textbook.has_ebook=1 and flag=1';
        $Model = M('biz_textbook');
        $result = $Model
            ->join('dict_course on dict_course.id=biz_textbook.course_id')
            ->join('dict_grade on dict_grade.id=biz_textbook.grade_id')
            ->field('biz_textbook.*')
            ->where($check)
            ->order('course_id asc, grade_id asc, school_term asc')
            ->select();
        foreach ($result as $r) {
            $r['cover'] = $r['server_path'] . "/content/2.png";
        }
        $this->ajaxReturn(array('status' => 200,'result'=>$result));
    }
}
?>