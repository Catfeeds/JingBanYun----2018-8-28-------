<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Verify; 

class TextbookController extends Controller
{   
 
    public function __construct() {
        parent::__construct();   
        $this->assign('oss_path',C('oss_path'));
    }
         
    
    //电子课本列表
    public function textbookList()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '电子课本');
        $this->assign('nav', '电子课本');
        $this->assign('subnav', '列表');

        $filter['course_id'] = $_REQUEST['course_id'];
        $filter['grade_id'] = $_REQUEST['grade_id'];
        $filter['textbook_id'] = $_REQUEST['textbook_id'];
        
        if (!empty($filter['course_id'])) $where['biz_textbook.course_id'] = $filter['course_id'];
        if (!empty($filter['grade_id'])) $where['biz_textbook.grade_id'] = $filter['grade_id'];
        if (!empty($filter['textbook_id'])) $where['biz_textbook.id'] = $filter['textbook_id'];
        
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
        
        $page = $_REQUEST['page'];
        if (empty($page)) $page = 1;
        $this->assign('page', $page);


        $keyword = $_GET['keyword'];
        $status=intval($_GET['status']);
        $date=$_GET['date'];
        if (!empty($keyword)) {
            /*$where[] = array(
                array(
                    'name' => array('like', '%' . $keyword . '%'),
                    'publishing_house' => array('like', '%' . $keyword . '%'),
                    'edition' => array('like', '%' . $keyword . '%'),
                    //'author' => array('like', '%' . $keyword . '%'),
                    '_logic' => 'or',
                ),
            ); */
            $where['_string']="name like '%$keyword%' or publishing_house like '%$keyword%' or edition like '%$keyword%'";
            $this->assign('keyword', $keyword);
        } 
        
        if(!empty($status)){  
            $where['biz_textbook.flag']=$status;
            $this->assign('status', $status);
        }  
        
        if(!empty($date)){  
            if(!empty($where['_string'])){
                $where["_string"].=" and create_at>=".strtotime(I("date"))." and "."create_at<=".(strtotime(I("date")."+1 day")-1);
            }else{
                $where["_string"]="create_at>=".strtotime(I("date"))." and "."create_at<=".(strtotime(I("date")."+1 day")-1);
            }   
            $this->assign('default_date', $date);
        }  

        $Model = M('biz_textbook');

        $count = $Model->where($where)->count('id');        
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));      
        //$Page->parameter['keyword'] = $keyword;         //urlencode($keyword);
        $show = $Page->show();

        $result = $Model
            ->where($where)
            ->limit($Page->firstRow . ',' . $Page->listRows) 
            ->order('sort_order asc,grade_id asc')
            ->select();                   
        
        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        $this->assign('courses', $courses);

        $Model = M('dict_grade');
        $grades = $Model->select();
        $this->assign('grades', $grades);
        
        $this->assign('list', $result);
        $this->assign('page', $show);

        $this->display();
    }
    
    
    //教材详情
    public function textbookDetails()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '电子课本');
        $this->assign('nav', '电子课本');

        $id = $_GET['id'];

        $Model = M('biz_textbook');
        $result1 = $Model
            ->where("id=$id")
            ->find(); 
        
        $this->assign('subnav', $result1['name']);
        $this->assign('data', $result1);

        $this->display();
    }

    //电子书详情
    public function etextbook()
    {
        if (!session('?admin')) redirect(U('Login/login'));

        $this->assign('module', '电子课本');
        $this->assign('nav', '电子课本');


        $c['id'] = $_GET['id'];

        $Model = M('biz_textbook');
        $result = $Model->where($c)->find();
        $this->assign('book', $result);

        $this->assign('subnav', $result['name']);

        $this->display();
    }
  
    
    public function createTextBook()
        {
         if($_POST)
          {
             $isEdit = isset($_GET['id']);
             //look for same grade course schoolterm book
               $check = array(
                  'course_id' => $_POST['course_id'],
                  'grade_id' => $_POST['grade_id'],
                  'school_term' => $_POST['school_term'],
                  'name' => $_POST['name']
                );
                $addData =  $check;
                if($isEdit)
                 $check['_string'] = 'id <>'. $_GET['id'];
                else {
                   $addData['create_at'] = time();
                   $addData['flag'] = 2;
                }
                $addData['update_at'] = time();
                $addData['author'] = $_POST['author'];
                $addData['isbn'] = $_POST['isbn'];
                $addData['print'] = $_POST['print'];
                $addData['has_ebook'] = $_POST['has_ebook']=='on' ? 1:0 ;
                $addData['server_path'] = $_POST['server_path'];
                $addData['edition'] = $_POST['edition'];
                $addData['sort_order'] = $_POST['sort_order'];
                $Model = M('biz_textbook');
                 $res = $Model->where($check)->find();
                if($res)
                {
                  $this->ajaxReturn(array("code" => -1 ,'msg' =>'对应年级学科分册名称的课本已存在'));
                }


              if($isEdit)
                {
                 $addData['update_at'] = time();
                 $bookId = $_GET['id'];
                 $Model->where('id='.$bookId)->save($addData);
                }
                else
                {
                $addData['create_at'] = time();
                $bookId = $Model->add($addData);
                }
               $this->ajaxReturn(array("code" => 0 ,'bookId' => $bookId,'msg' =>'添加/修改成功'));
          }
          else
          {
          $Model = M('dict_course');
          $courses = $Model->order('sort_order asc')->select();
          $this->assign('courses', $courses);

          $Model = M('dict_grade');
          $grades = $Model->select();
          $this->assign('grades', $grades);
          $this->assign('nav', '电子课本管理');

          if($_GET['id']) //editing book
          {
           $Model = M('biz_textbook');
           $data =  $Model->where('id='.$_GET['id'])->find();       
           $this->assign('data',$data);
           $this->assign('subnav', '修改课本');
          }
          else
          {
           $this->assign('subnav', '创建课本');
          }
          $this->display();
          }
        }

    public function deleteTextBook()
    {
       if($_POST)
       {
        $Model = M('biz_textbook');
        $data =  $Model->where('id='.$_POST['id'])->delete();
        $this->ajaxReturn(array('code' => 0,'msg' => '删除成功'));
       }
    }
    
    public function textBookShelfControl()
    {
      if($_POST)
      {
        $Model = M('biz_textbook');
        $id = getParameter('id','int');
        $data['flag'] = getParameter('flag','int');
        $data =  $Model->where('id='.$id)->save($data);
        if(false === $data)
        {
            $this->ajaxReturn(array('code' => 500,'msg' => '上/下架失败'));
        }
        $bookInfo = D('Biz_textbook')->getTextBookDetails($id);
          if($bookInfo['flag'] == 1) {
              $studentIdList = D('Auth_student')->getStudentIdsListByGrade($bookInfo['grade_id']);
              $courseResult = D('Dict_course')->getCourseInfo($bookInfo['course_id']);
              $gradeResult = D('Dict_grade')->getGradeInfo($bookInfo['grade_id']);
              $textBook = ($bookInfo['school_term'] == 1) ? '上册' : '下册';
              $parameters = array('msg' => array(
                  $gradeResult['grade'],
                  $courseResult['course_name'],
                  $textBook
              ),
                  'url' => array('type' => 1, 'data' => array($id))
              );
              A('Home/Message')->addPushUserMessage('NEW_ETEXTBOOK', 3, implode(',', $studentIdList), $parameters);

              $parentIdList = D('Auth_student')->getParentList($studentIdList);
              A('Home/Message')->addPushUserMessage('NEW_ETEXTBOOK_CHILD', 4, implode(',', $parentIdList), $parameters);
          }
        $this->ajaxReturn(array('code' => 0,'msg' => '上/下架成功'));
      }
    }
}
