<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify; 

class LessonPlanningController extends Controller
{   
  
    
    public function __construct() {
        parent::__construct();   
        $this->assign('oss_path',C('oss_path'));
    }
         
     
    //备课课件模板管理
    public function lessonPlanningMgmt()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '字典数据管理');
        $this->assign('nav', '公共课件库管理');
        $this->assign('subnav', '课件列表');

        $page = $_REQUEST['page'];
        if (empty($page)) $page = 1;
        $this->assign('page', $page);

        $keyword = $_GET['keyword'];
        if (empty($keyword)) {
            $where['biz_lesson_planning_template.id'] = array('NEQ', 0);
        } else {
            $where['biz_lesson_planning_template.name'] = array('like', '%' . $keyword . '%');
            $where['biz_lesson_planning_template.description'] = array('like', '%' . $keyword . '%');
            $where['_logic'] = 'OR';
            $this->assign('keyword', $keyword);
        }

        $Model = M('biz_lesson_planning_template');
        $result = $Model
            ->join("dict_course on dict_course.id=biz_lesson_planning_template.course_id")
            ->join("dict_grade on dict_grade.id=biz_lesson_planning_template.grade_id")
            ->join("biz_textbook on biz_textbook.id=biz_lesson_planning_template.textbook_id")
            ->field("biz_lesson_planning_template.*,dict_course.course_name,dict_grade.grade,biz_textbook.name as textbook")
            ->where($where)
            ->page($page, C('PAGE_SIZE_FRONT'))
            ->select();

        $this->assign('list', $result);

        $this->display();
    }
    
    
    //发布课件模板
    public function publishLessonPlanningTemplate()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        if ($_POST) {
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 911127886;// 设置附件上传大小
            $upload->exts = array('ppt','pptx');// 设置附件上传类型
            $upload->rootPath = './Resources/lessonplanning/'; // 设置附件上传根目录
            // 上传单个文件
            $info = $upload->uploadOne($_FILES['file']);
            if (!$info) {// 上传错误提示错误信息
                $this->error($upload->getError());
            } else {// 上传成功 获取上传文件信息
                //echo $info['savepath'] . $info['savename'];
            }

            $data['name'] = remove_xss($_POST['name']);
            $data['description'] = remove_xss($_POST['description']);
            $data['course_id'] = $_POST['course_id'];
            $data['grade_id'] = $_POST['grade_id'];
            $data['textbook_id'] = $_POST['textbook_id'];
            $data['file_path'] = $info['savepath'] . $info['savename'];

            $data['type'] = 'PPT';
            $data['status'] = 1;

            $data['create_at'] = time();
            $data['update_at'] = time();

            $ResourceModel = M('biz_lesson_planning_template');

            $ResourceModel->add($data);

            $this->redirect("LessonPlanning/lessonPlanningMgmt");

        } else {
            $this->assign('module', '字典数据管理');
            $this->assign('nav', '公共课件库管理');
            $this->assign('subnav', '发布');

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            $Model = M('biz_textbook');
            $textbooks = $Model->order('sort_order asc')->select();
            $this->assign('textbooks', $textbooks);

            $this->display();
        }
    }

    //编辑课件模板
    public function editLessonPlanningTemplate()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        if ($_POST) {

            if ($_FILES["file"]["error"] == 0) {
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 911127886;// 设置附件上传大小
                $upload->exts = array('ppt,pptx');// 设置附件上传类型
                $upload->rootPath = './Resources/lessonplanning/'; // 设置附件上传根目录
                // 上传单个文件
                $info = $upload->uploadOne($_FILES['file']);
                if (!$info) {// 上传错误提示错误信息
                    $this->error($upload->getError());
                }
                $data['file_path'] = $info['savepath'] . $info['savename'];
            }

            $check['id'] = $_POST['id'];

            $data['name'] = remove_xss($_POST['name']);
            $data['description'] = remove_xss($_POST['description']);
            $data['course_id'] = $_POST['course_id'];
            $data['grade_id'] = $_POST['grade_id'];
            $data['textbook_id'] = $_POST['textbook_id'];

            $data['update_at'] = time();

            $ResourceModel = M('biz_lesson_planning_template');

            $ResourceModel->where($check)->save($data);

            $this->redirect("LessonPlanning/lessonPlanningMgmt");
        } else {
            $this->assign('module', '字典数据管理');
            $this->assign('nav', '公共课件库管理');
            $this->assign('subnav', '编辑');

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);


            $ResourceModel = M('biz_lesson_planning_template');
            $check['id'] = $_GET['id'];
            $data = $ResourceModel->where($check)->find();

            $courseId = $data['course_id'];
            $gradeId = $data['grade_id'];
            $Model = M('biz_textbook');
            $textbooks = $Model
                ->where("course_id=$courseId and grade_id=$gradeId")
                ->order('sort_order asc')->select();
            $this->assign('textbooks', $textbooks);

            $this->assign('data', $data);

            $this->display();
        }
    }

    //删除课件模板
    public function deleteLessonPlanningTemplate()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $Model = M('biz_lesson_planning_template');
        $Model->where("id=$id")->delete();
        $this->ajaxReturn('success');
    }
}
