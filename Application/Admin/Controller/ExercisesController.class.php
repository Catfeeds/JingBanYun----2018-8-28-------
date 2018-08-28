<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify; 

class ExercisesController extends Controller
{   
 
                 
    public function __construct() {
        parent::__construct();   
        $this->assign('oss_path',C('oss_path'));
    }
         
    
    //习题库管理
    function exercisesMgmt()
    {
        if (!session('?admin')) redirect(U('Login/login'));
                    
        $this->assign('nav', '习题库管理');
        $this->assign('subnav', '习题章节');
                    

        $filter['course_id'] = $_REQUEST['course_id'];
        $filter['grade_id'] = $_REQUEST['grade_id'];
        $filter['textbook_id'] = $_REQUEST['textbook_id'];
        $filter['keyword'] = $_REQUEST['keyword'];

        if (!empty($filter['course_id'])) $check['biz_exercise_library_chapter.course_id'] = $filter['course_id'];
        if (!empty($filter['grade_id'])) $check['biz_exercise_library_chapter.grade_id'] = $filter['grade_id'];
        if (!empty($filter['textbook_id'])) $check['biz_exercise_library_chapter.textbook_id'] = $filter['textbook_id']; 
        if (!empty($filter['keyword'])) $check['_string'] = "biz_exercise_library_chapter.chapter like '%".$filter['keyword']."%' or biz_exercise_library_chapter.festival like '%".$filter['keyword']."%' or "
                . "biz_exercise_library.questions like '%".$filter['keyword']."%'"; 

        //年级和学科不为空求出所有教材
        if(!empty($filter['grade_id']) && !empty($filter['course_id'])){
            $textbook_model=M('biz_textbook');
            $textbook_where['grade_id']=$filter['grade_id'];
            $textbook_where['course_id']=$filter['course_id'];
            $textbook_result=$textbook_model->where($textbook_where)->field('id,name')->select();
            $this->assign('textbook',$textbook_result);
        }
        
        $this->assign('course_id', $filter['course_id']);
        $this->assign('grade_id', $filter['grade_id']);
        $this->assign('textbook_id', $filter['textbook_id']);

        $Model = M('biz_exercise_library_chapter');

        $count = $Model
            ->join("biz_exercise_library on biz_exercise_library.chapter_id=biz_exercise_library_chapter.id")
            ->join("dict_grade on dict_grade.id=biz_exercise_library_chapter.grade_id")
            ->join("dict_course on dict_course.id=biz_exercise_library_chapter.course_id")
            ->join("biz_textbook on biz_textbook.id=biz_exercise_library_chapter.textbook_id")
            ->join("biz_exercise_template on biz_exercise_template.id=biz_exercise_library.type")
            ->field("biz_exercise_library_chapter.*,dict_grade.grade,dict_course.course_name,biz_textbook.name as textbook")
            ->where($check)
            ->group("biz_exercise_library_chapter.id")
            ->field('biz_exercise_library_chapter.id')
            ->select();         
        $count=count($count);      
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($filter as $key => $val) { 
            $Page->parameter[$key] = $val;
        }
        $show = $Page->show();


        $result = $Model
            ->join("biz_exercise_library on biz_exercise_library.chapter_id=biz_exercise_library_chapter.id")
            ->join("dict_grade on dict_grade.id=biz_exercise_library_chapter.grade_id")
            ->join("dict_course on dict_course.id=biz_exercise_library_chapter.course_id")
            ->join("biz_textbook on biz_textbook.id=biz_exercise_library_chapter.textbook_id")
            ->join("biz_exercise_template on biz_exercise_template.id=biz_exercise_library.type")
            ->field("biz_exercise_library_chapter.*,dict_grade.grade,dict_course.course_name,biz_textbook.name as textbook")
            ->where($check)
            ->group("biz_exercise_library_chapter.id")
            ->order('create_at desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();  
        
        $exercise_model=D('Exercise_library');
        $all_exercise_result=$exercise_model->getAllExerciseCount();  
        $exercise_actegory=$exercise_model->getExerciseCategory();
        $search_count=$exercise_model->getExerciseInfoCount($check);
        $search_count=$search_count['count'];
        $search_exercise=array();
        $search_exercise[]=array('key'=>'习题资源总数','value'=>$search_count);
        
        foreach($exercise_actegory as $val){
            $check['biz_exercise_template.template_name']=$val['template_name'];
            $data=$exercise_model->getExerciseInfoCount($check);
            $search_exercise[]=array('key'=>$val['template_name'],'value'=>$data['count']);
        }
                    
        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        
        $Model = M('dict_grade');
        $grades = $Model->select();
        
        $this->assign('all_exercise_count_arr', $all_exercise_result);
        $this->assign('search_exercise_arr', $search_exercise);
        $this->assign('keyword', $filter['keyword']);
        $this->assign('list', $result);
        $this->assign('page', $show);

        
        $this->assign('courses', $courses); 
        $this->assign('grades', $grades);

        $this->display();
    }
    
    
    //章节详情
    public function exerciseLibraryChapterDetails()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '习题库管理');
        $this->assign('nav', '章节详情');
        $this->assign('subnav', '章节习题');

        $exercise_model=D('Exercise_library');
        
        $id = intval($_GET['id']);
        $category=getParameter('cat','int',false); 
        
        $where['biz_exercise_library.chapter_id']=$id;
        if($category){
            $result=$exercise_model->getExerciseTemplateInfo($category);
            if(!empty($result)){
                $where['biz_exercise_template.template_name']=$result['template_name'];
            }
        }
       
        $Model = M('biz_exercise_library');
        $list = $Model->where($where)
            ->join("biz_exercise_template on biz_exercise_library.type=biz_exercise_template.id")
            ->field("biz_exercise_library.id,biz_exercise_library.question_id,biz_exercise_library.questions,biz_exercise_library.points,biz_exercise_template.template_name,biz_exercise_library.teachingsvn,biz_exercise_library.is_pay")
            ->order('biz_exercise_library.question_id asc')->select();

        
        $exercise_actegory=$exercise_model->getExerciseCategory();  
        $exercise_diffculty=array('1'=>'一星','2'=>'二星','3'=>'三星','4'=>'四星','5'=>'五星');
        
        $this->assign('diffculty', $exercise_diffculty);
        $this->assign('category', $exercise_actegory);
        $this->assign('choose_category', $category);
        $this->assign('list', $list); 
        $this->assign('chapter_id', $id);
        $this->display();

    }
    
    
    public function previewExerciseLibraryChapter()
    {
        $this->assign('module', '习题库管理');
        $this->assign('nav', '章节详情');
        $this->assign('subnav', '预览章节习题');

        $id = $_GET['id'];
        $this->assign('id', $id);
        $this->display();
    }

    //预览章节中的习题
    public function previewExerciseLibraryChapterFrame()
    {
        $id = $_GET['id'];
        $this->assign('id', $id);
        $this->display();
    }
     
    //创建习题
    public function createExerciseLibrary()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        if ($_POST) {
            $chapterName = remove_xss($_POST['chapter']);
            $festival = remove_xss($_POST['festival']);
            $title = remove_xss($_POST['title']);
            $textbookId = $_POST['textbook_id'];
            //判断章节是否存在
            $ChapterModel = M('biz_exercise_library_chapter');
            $checkChapter['chapter'] = $chapterName;
            $checkChapter['festival'] = $festival;
            $checkChapter['title'] = $title;
            $checkChapter['textbook_id'] = $textbookId;
            
            $chapter = $ChapterModel->where($checkChapter)->find();
            if (empty($chapter)) {
                $chapterData['grade_id'] = $_POST['grade_id'];
                $chapterData['course_id'] = $_POST['course_id'];
                $chapterData['textbook_id'] = $textbookId;
                $chapterData['exercise_count'] = 1;
                $chapterData['create_at'] = time();
                $chapterData['create_at'] = time();

                /*//根据空格分隔章节
                $arr = explode(' ', $chapterName);
                $first = $arr[0];
                $second = $arr[1];
                $title = '';
                $festival = '';
                if (strripos($first, "章") != false) {
                    $title = $first;
                };
                if (strripos($second, "节") != false) {
                    $festival = $second;
                };*/
                $chapterData['title'] = $title;
                $chapterData['festival'] = $festival;
                $chapterData['chapter'] = $chapterName;


                $chapterId = $ChapterModel->add($chapterData);
            } else {
                //习题数加1
                $ChapterModel->where($checkChapter)->setInc('exercise_count');
                $chapterId = $chapter['id'];
            }

            $Model = M('biz_exercise_library');
            $data['chapter_id'] = $chapterId;
            $data['update_at'] = time();
            $data['create_at'] = time();
            $data['questions'] = $_POST['questions'];
            $data['question_id'] = $_POST['question_id'];
            $data['type'] = $_POST['type'];
            $data['body'] = $_POST['body'];
            $data['answer'] = $_POST['answer'];
            $data['points'] = $_POST['points'];
            $data['mp3_vid'] = $_POST['mp3_vid'];

            $data['difficulty'] = $_POST['difficulty'];
            $data['knowledge_point'] = $_POST['knowledge_point'];
            $data['options_sort_order'] = $_POST['options_sort_order'];
            $data['explainInDetail'] = $_POST['explainInDetail'];
            $data['teachingsvn'] = $_POST['teachingsvn'];
            $data['is_pay'] = $_POST['is_pay'];

            $Model->add($data);
            $this->ajaxReturn(array('msg'=>'操作成功!'));
        } else {
            $this->assign('module', '习题库管理');
            $this->assign('nav', '创建试题');
            $this->assign('subnav', '');

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            $this->display();
        }
    }

    //编辑习题
    public function editExerciseLibrary()
    {
        if (!session('?admin')) redirect(U('Login/login'));
        if ($_POST) { 
            $chapterName = remove_xss($_POST['chapter']);
            $festival = remove_xss($_POST['festival']);
            $title = remove_xss($_POST['title']);
            
            $textbookId = $_POST['textbook_id'];
            //判断章节是否存在
            $ChapterModel = M('biz_exercise_library_chapter');
            $checkChapter['chapter'] = $chapterName;
            $checkChapter['festival'] = $festival;
            $checkChapter['title'] = $title;
            $checkChapter['textbook_id'] = $textbookId;
            $chapter = $ChapterModel->where($checkChapter)->find();
            
            if (empty($chapter)) {          
                $chapterData['grade_id'] = $_POST['grade_id'];
                $chapterData['course_id'] = $_POST['course_id'];
                $chapterData['textbook_id'] = $textbookId;
                $chapterData['exercise_count'] = 1;
                $chapterData['create_at'] = time();

                /*//根据空格分隔章节
                $arr = explode(' ', $chapterName);
                $first = $arr[0];
                $second = $arr[1];
                $title = '';
                $festival = '';
                if (strripos($first, "章") != false) {
                    $title = $first;
                };
                if (strripos($second, "节") != false) {
                    $festival = $second;
                };*/
                $chapterData['title'] = $title;
                $chapterData['festival'] = $festival;
                $chapterData['chapter'] = $chapterName;

                $chapterId = $ChapterModel->add($chapterData);
            } else {
                $chapterId = $chapter['id'];
            } 

            $Model = M('biz_exercise_library');
            $id = $_POST['id'];
            $data['chapter_id'] = $chapterId;
            $data['update_at'] = time();
            $data['questions'] = $_POST['questions'];
            $data['question_id'] = $_POST['question_id'];
            $data['type'] = $_POST['type'];
            $data['body'] = $_POST['body'];
            $data['answer'] = $_POST['answer'];
            $data['points'] = $_POST['points'];
            $data['mp3_vid'] = $_POST['mp3_vid'];
            $data['difficulty'] = $_POST['difficulty'];
            $data['knowledge_point'] = $_POST['knowledge_point'];
            $data['options_sort_order'] = $_POST['options_sort_order'];
            $data['explainInDetail'] = $_POST['explainInDetail'];
            $data['teachingsvn'] = $_POST['teachingsvn'];
            $data['is_pay'] = $_POST['is_pay'];

            $Model->where("id=$id")->save($data);
            $this->ajaxReturn(array());

        } else {
            $this->assign('module', '习题库管理');
            $this->assign('nav', '编辑习题');
            $this->assign('subnav', '');

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);


            $id = $_GET['id'];
            $Model = M('biz_exercise_library');
            $result = $Model->where("id=$id")->find();
            $result['body'] = str_replace("'", "", $result['body']);
            $result['body'] = str_replace("[/r/n]", "######", $result['body']);
            $this->assign('data', $result);


            $chapterId = $result["chapter_id"];

            $ChapterModel = M('biz_exercise_library_chapter');
            $chapter = $ChapterModel->where("id=$chapterId")->find();
            $this->assign('chapter', $chapter);

            $courseId = $chapter["course_id"];
            $gradeId = $chapter["grade_id"];

            $Model = M('biz_textbook');
            $textbooks = $Model
                ->where("course_id=$courseId and grade_id=$gradeId")
                ->order('sort_order asc')->select();
            $this->assign('textbooks', $textbooks);

            $Model = M('biz_exercise_template');
            $templates = $Model
                ->where("course_id=$courseId")
                ->select();
            $this->assign('templates', $templates);

            $template = $Model
                ->where("id=" . $result['type'])
                ->find();
            $this->assign('template', $template);

            $this->display();
        }
    }

    //删除习题
    public function deleteExerciseLibrary()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $Model = M('biz_exercise_library');
        $Model->where("id=$id")->delete();
        $this->ajaxReturn('success');
    }

    //删除习题章节
    public function deleteExerciseLibraryChapter()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $id = $_GET['id'];
        $Model = M('biz_exercise_library_chapter');
        $Model->where("id=$id")->delete();

        $ExerciseModel = M('biz_exercise_library');
        $ExerciseModel->where("chapter_id=$id")->delete();

        $this->ajaxReturn('success');
    }
}
