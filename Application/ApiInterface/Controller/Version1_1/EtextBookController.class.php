<?php
namespace ApiInterface\Controller\Version1_1;
use Think\Controller;

class EtextBookController extends PublicController
{
  public $pageSize = 99;
  public $Model;
  public function __construct() {
         parent::__construct();
         $this->Model = D('Biz_textbook');
     }
  function getPageSize(){
      return $this->pageSize;
  }
    /**
     * @描述：查询电子课本列表
     * @参数：course_id[int] N 学科ID
     * @参数：grade_id[int] N 年级ID
     * @参数：textbook_id[int] N 学期
     * @参数：pageIndex[int] N 页码
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
  public function getETextBook()
  {
      $courseId =getParameter('course_id','int',false);
      $gradeId =getParameter('grade_id','int',false);
      $school_term =getParameter('textbook_id','int',false);
      $pageIndex =getParameter('pageIndex','int',false);
      $pageSize =I('pageSize','int',false);
      if (!empty($pageSize)) {
          $this->pageSize = $pageSize;
      }
      $courseId = empty($courseId)|| $courseId==-1?'':$courseId;
      $gradeId = empty($gradeId)|| $gradeId==-1?'':$gradeId;
      $school_term = empty($school_term)|| $school_term==-1?'':$school_term;

      $queryParameters = array(
          'course_id' => $courseId,
          'grade_id'  => $gradeId,
          'school_term' => $school_term,
      );

      $this->ajaxReturn(array('status' => 200,'result' => $this->Model->getTextBookList($queryParameters,$pageIndex,$this->getPageSize())));
  }



    public function getCourseList() {

        $gradeId =getParameter('grade_id','int',false);
        $school_term =getParameter('textbook_id','int',false);

        if($gradeId)
            $check['biz_textbook.grade_id'] =$gradeId;
        if($school_term)
            $check['biz_textbook.school_term'] =$school_term;
        if($_REQUEST['keyword']){
            $keyword=getParameter('keyword', 'str',false);
            $check['biz_textbook.name'] = array('like','%'.$keyword.'%');
        }

        $check['_string'] = 'biz_textbook.has_ebook=1 and flag=1';
        $controller_obj=new \Home\Controller\CommonController();

        $course_con_data[0]['id'] = -1;
        $course_con_data[0]['name'] = '不限';
        $course_con = $controller_obj->getAppTextBookSelector($check,'course');
        $course_con = array_merge($course_con_data,$course_con);

        $this->ajaxReturn(array('status' => 200,'result' =>$course_con ));
    }

    public function getGradeList() {
        $courseId =getParameter('course_id','int',false);
        $school_term =getParameter('textbook_id','int',false);

        if($courseId)
            $check['biz_textbook.course_id'] =$courseId;
        if($school_term)
            $check['biz_textbook.school_term'] =$school_term;
        if($_REQUEST['keyword']){
            $keyword=getParameter('keyword', 'str',false);
            $check['biz_textbook.name'] = array('like','%'.$keyword.'%');
        }

        $check['_string'] = 'biz_textbook.has_ebook=1 and flag=1';
        $controller_obj=new \Home\Controller\CommonController();

        $course_con_data[0]['id'] = -1;
        $course_con_data[0]['name'] = '不限';
        $grade_con=$controller_obj->getAppTextBookSelector($check,'grade');
        $grade_con = array_merge($course_con_data,$grade_con);

        $this->ajaxReturn(array('status' => 200,'result' =>$grade_con ));
    }

    public function getSchoolermList() {
        $courseId =getParameter('course_id','int',false);
        $gradeId =getParameter('grade_id','int',false);

        if($courseId)
            $check['biz_textbook.course_id'] =$courseId;
        if($gradeId)
            $check['biz_textbook.grade_id'] =$gradeId;
        if($_REQUEST['keyword']){
            $keyword=getParameter('keyword', 'str',false);
            $check['biz_textbook.name'] = array('like','%'.$keyword.'%');
        }

        $check['_string'] = 'biz_textbook.has_ebook=1 and flag=1';

        $course_con_data[0]['id'] = -1;
        $course_con_data[0]['name'] = '不限';

        $controller_obj=new \Home\Controller\CommonController();
        $school_term_con=$controller_obj->getAppTextBookSelector($check,'school_term');
        foreach ($school_term_con as $k=>$v){
            if ($v['id'] == 1) {
                $school_term_con[$k]['name'] = '上册';
            }
            if ($v['id'] == 2) {
                $school_term_con[$k]['name'] = '下册';
            }
            if ($v['id'] == 3) {
                $school_term_con[$k]['name'] = '全一册';
            }
        }
        $school_term_con = array_merge($course_con_data,$school_term_con);
        $this->ajaxReturn(array('status' => 200,'result' =>$school_term_con ));
    }



    /**
     * @描述：查询所有电子课本列表
     * @参数：courseId[int] Y 学科ID
     * @参数：gradeId[int] Y 年级ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getAllETextBook()
    {
        $courseId =getParameter('courseId','int');
        $gradeId =getParameter('gradeId','int');
        $queryParameters = array(
            'course_id' => $courseId,
            'grade_id'  => $gradeId,
        );
        $this->ajaxReturn(array('status' => 200,'result' => $this->Model->getAllTextBookList($queryParameters)));
    }
    public function textBookDetails()
    {
        $id =getParameter('id','int',true);
        $result = $this->Model->getTextBookDetails($id);
        $result['name'] = $result['name'] . ' ';
        $this->ajaxReturn(array('status' => 200,'result' => $result));
    }


    public function getTextGradeCourseSTextBook() {
        $courseId =getParameter('course_id','int',false);
        $gradeId =getParameter('grade_id','int',false);
        $school_term =getParameter('textbook_id','int',false);
        $pageIndex =getParameter('pageIndex','int',false);
        $queryParameters = array(
            'course_id' => $courseId,
            'grade_id'  => $gradeId,
            'school_term' => $school_term,
        );

        if($courseId)
            $check['biz_textbook.course_id'] =$courseId;
        if($gradeId)
            $check['biz_textbook.grade_id'] =$gradeId;
        if($school_term)
            $check['biz_textbook.school_term'] =$school_term;
        if($_REQUEST['keyword']){
            $keyword=getParameter('keyword', 'str',false);
            $check['biz_textbook.name'] = array('like','%'.$keyword.'%');
        }

        $check['_string'] = 'biz_textbook.has_ebook=1 and flag=1';
        $controller_obj=new \Home\Controller\CommonController();
        $course_con=$controller_obj->getTextBookSelector($check,'course');
        if(!empty($course_con)){
            $grade_con=$controller_obj->getTextBookSelector($check,'grade');
            $school_term_con=$controller_obj->getTextBookSelector($check,'school_term');
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
        $data['courses'] = $course_con;
        $data['grades'] = $grade_con;
        $data['textbooks'] = $school_term_con;
        if(!empty($_REQUEST['course'])) {
            $data['courses']=[];
        }
        $data['result'] = $this->Model->getTextBookList($queryParameters,$pageIndex,$this->getPageSize());
        print_r($data);die();
        $this->ajaxReturn(array('status' => 200,'result' =>$data ));
    }
}
?>