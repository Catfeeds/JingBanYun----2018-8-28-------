<?php
namespace Home\Controller;
use Think\Controller;

class TextBookController extends PublicController{
    public function __construct() {
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));
        $this->c_a = CONTROLLER_NAME."_".ACTION_NAME;
    }

    ////电子课本
    public function textbookList()
    {
        $userId=0;
        $role=0;
        A('Home/Common')->getUserIdRole($userId,$role);
        A('Home/Common')->authJudgement();
        switch($role)
		{
			case ROLE_TEACHER:layout('teacher_layout_2');
				              break;
			case ROLE_STUDENT:layout('student_layout_2');
				              break;
			case ROLE_PARENT:layout('parent_layout_2');
				              break;
            default:
                layout('teacher_layout_2');
                break;
		}
		$flag = false;

        if (!empty(session('teacher')) || !empty(session('parent'))  || !empty(session('student')) ) {
            $flag= true;

        }
        if ($flag== false) {
            redirect(U('Index/index'));
        }

        /*$isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }*/

        if($_REQUEST['course'])
            $check['biz_textbook.course_id'] =getParameter('course', 'int',false);
        if($_REQUEST['grade'])
            $check['biz_textbook.grade_id'] =getParameter('grade', 'int',false);
        if($_REQUEST['textbook'])
            $check['biz_textbook.school_term'] =getParameter('textbook', 'int',false);
        if($_REQUEST['keyword']){
            $keyword=getParameter('keyword', 'str',false);
            $check['biz_textbook.name'] = array('like','%'.$keyword.'%');
        }


        $this->assign('module', '教学+');
        $this->assign('nav', '电子课本');
        $this->assign('subnav', '电子课本');
        $this->assign('navicon', 'shuzijiaocai');


        $check['_string'] = 'biz_textbook.has_ebook=1 and flag=1';
        $course_con=A('Home/Common')->getTextBookSelector($check,'course');
        if(!empty($course_con)){
            $grade_con=A('Home/Common')->getTextBookSelector($check,'grade');
            $school_term_con=A('Home/Common')->getTextBookSelector($check,'school_term');
        }else{
            if(!empty($check['biz_textbook.course_id'])){
                $course_model=D('Dict_course');
                $course_result=$course_model->getCourseInfo($check['biz_textbook.course_id']);
                $course_con[]=$course_result;
            }
            if(!empty($check['biz_textbook.grade_id'])){
                $grade_model=D('Dict_grade');
                $grade_result=$grade_model->getGradeInfo($check['biz_textbook.grade_id']);
                $grade_con[]=$grade_result;
            }
            if(!empty($check['biz_textbook.school_term'])){
                if($check['biz_textbook.school_term']==1){
                    $school_term_con[]=array('school_term'=>1);
                }elseif($check['biz_textbook.school_term']==2){
                    $school_term_con[]=array('school_term'=>2);
                }elseif($check['biz_textbook.school_term']==3){
                    $school_term_con[]=array('school_term'=>3);
                }

            }
        }

        $this->assign('courses',$course_con);
        $this->assign('grades',$grade_con);
        $this->assign('textbooks',$school_term_con);

        $this->assign('course_id', $check['biz_textbook.course_id']);
        $this->assign('grade_id', $check['biz_textbook.grade_id']);
        $this->assign('textbook_id', $check['biz_textbook.school_term']);
        $this->assign('keyword',$_REQUEST['keyword']);

        /*
        A('Home/Common')->CourseGradeTextBookSelector(array(
            'course_id'=>$check['biz_textbook.course_id'],
            'grade_id'=>$check['biz_textbook.grade_id'],
            'textbook_id'=>$check['biz_textbook.textbook_id'],
            'keyword'=>$check['keyword']
        ));*/
        $this->display();
    }

    //电子课本详情
    public function textbookDetails()
    {
        // $isAuth = $this->isAuth($this->c_a);
        // if (!$isAuth) { //如果访问的模块没有权限
        //     $teacher = session('auth_teacher');
        //     $parent = session('auth_parent');

        //     if (!empty($teacher)) {
        //         redirect(U('Teach/index1?auth_error=1'));
        //     } elseif(!empty($parent)) {
        //         redirect(U('Parent/index1?auth_error=1'));
        //     } else {
        //         redirect(U('Student/index1?auth_error=1'));
        //     }

        // }



        if (!session('?teacher') && !session('?student') && !session('?parent') && !session('?admin'))
            redirect(U('Index/index'));
        $this->assign('module', '教学+');
        $this->assign('nav', '电子课本');
        $this->assign('navicon', 'shuzijiaocai');


        //$c['id'] = $_GET['id'];
        $c['id'] = getParameter('id', 'int',false);
        if(empty($c['id'])){
            redirect(U('Index/systemError'));
        }

        $Model = M('biz_textbook');
        $result = $Model->where($c)->find();
        $this->assign('book', $result);

        $this->assign('subnav', $result['name']);

        $this->display();
    }

}