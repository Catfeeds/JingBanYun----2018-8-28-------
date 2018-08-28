<?php
namespace ApiInterface\Controller\Version1_2;

define('CATEGORY_TEXTBOOK',2);
class EtextBookController extends PublicController
{
  public $pageSize = 20;
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
        $this->ajaxReturn(array('status' => 200,'result' =>$data ));
    }





    /**
     *@描述：排行榜展示
     * @参数：url[str] 语音地址
     * @参数：userId[int] 用户ID
     * @参数：role[int] 用户role
     * @参数：pageIndex[int] 页码
     * @参数：funSelect[int] 功能选择 1--排行列表 2--自己的排名
     */
    public function rankingList()
    {
        $standardVoiceUrl = getParameter('url', 'str');//对话URL
        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');
        $pageIndex = 1;
        $pageSize = 50;
        $selfResult = [];
        //查询所有学生的积分并排序

        $result = D('Picture_books')->getTextBookVoiceRankList($standardVoiceUrl, CATEGORY_TEXTBOOK, $pageIndex, $pageSize);

        foreach ($result as $key => $value) {
            $this->getAvatar($result[$key]);//根据不同用户角色拿到不同的头像
        }
        $this->assign('result', $result);
        if ($role != ROLE_YOUKE) {
            $selfResult = D('Picture_books')->getSelfVoiceRank($standardVoiceUrl, $role, $userId);
            $this->getAvatar($selfResult);//根据不同用户角色拿到不同的头像
        }
        $title = D('Picture_books')->getTextbookNameByVoiceUrl($standardVoiceUrl);
        $this->assign('title', $title);
        $this->assign('selfResult', $selfResult);
        $this->display();


    }

    /**
     *@描述：获取自己的瓖音测评结果
     * @参数：url[str] 语音地址
     * @参数：userId[int] 用户ID
     * @参数：role[int] 用户role
     */
    public function getSelfVoiceResult()
    {
        $standardVoiceUrl = getParameter('url', 'str');//对话URL
        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');
        $selfResult = D('Picture_books')->getSelfVoiceRank($standardVoiceUrl, $role, $userId);
        $this->getAvatar($selfResult);//根据不同用户角色拿到不同的头像
        $selfResult['rankingUrl'] = 'http://'.WEB_URL."/ApiInterface/Version1_2/EtextBook/rankingList?url=$standardVoiceUrl";
        $this->showMessage(200,'success',$selfResult);
    }

    private function getAvatar(&$result)
    {
        if ($result['role'] == ROLE_TEACHER) {

            if (preg_match('/Resources/', $result['avatar'])) {
                if (strpos($result['avatar'], '.') === false) {
                    $result['avatar'] .= '.jpg';
                }
                $result['avatar'] = C('oss_path') . $result['avatar'];
            } else {
                if ($result['sex'] == '男' || empty($result['sex'])) {
                    $result['avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/teacher_m.png';
                } else {
                    $result['avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/teacher_w.png';
                }

            }
        } elseif ($result['role'] == ROLE_STUDENT) {
            if (preg_match('/Resources/', $result['avatar'])) {
                if (strpos($result['avatar'], '.') === false) {
                    $result['avatar'] .= '.jpg';
                }
                $result['avatar'] = C('oss_path') . $result['avatar'];
            } else {

                if ($result['sex'] == '男' || empty($result['sex'])) {
                    $result['avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/student_m.png';
                } else {
                    $result['avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/student_w.png';
                }

            }
        } elseif ($result['role'] == ROLE_PARENT) {


            if (preg_match('/Resources/', $result['avatar'])) {
                if (strpos($result['avatar'], '.') === false) {
                    $result['avatar'] .= '.jpg';
                }
                $result['avatar'] = C('oss_path') . $result['avatar'];
            } else {

                if ($result['sex'] == '男' || empty($result['sex'])) {
                    $result['avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/jiazhang.png';
                } else {
                    $result['avatar'] = 'http://' . WEB_URL . '/Public/img/classManage/jiazhang2.png';
                }

            }
        }
    }

    /**
     * @描述：排行榜入库积分
     * @参数：voiceUrl[str] 发声Url
     * @参数：url[str] 用户Url
     * @参数：textbookId[int] 课本ID
     * @参数：userId[int] 用户ID
     * @参数：score[int] 得分
     * @参数：role[int] 角色
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function increaseTheIntegral()
    {
        $pictureBooksOfPart_Url = getParameter('voiceUrl', 'str'); //发声Url
        $userUrl = getParameter('url', 'str'); //发声Url
        $pictureBooks_id = getParameter('textbookId', 'int'); //课本ID
        $user_id = getParameter('userId', 'int'); //用户ID
        $score = getParameter('score', 'int'); //得分
        $role = getParameter('role', 'int');//角色
        if($role == ROLE_YOUKE) {//游客暂时没有先预留
            $this->showjson(200);
        }else {
            //1.如果数据库中这个ID存在则执行更新表数据的操作

            if ($role == ROLE_STUDENT) {
                $condetion['student_id'] = !empty($user_id) ? $user_id : null;
            }
            if ($role == ROLE_PARENT) {
                $condetion['parent_id'] = !empty($user_id) ? $user_id : null;
            }
            if ($role == ROLE_TEACHER) {
                $condetion['teacher_id'] = !empty($user_id) ? $user_id : null;
            }
            $condetion['role'] = !empty($role) ? $role : null;
            $condetion['book_category'] = CATEGORY_TEXTBOOK;
            $condetion['pictureBooksOfPart_Url'] = !empty($pictureBooksOfPart_Url) ? $pictureBooksOfPart_Url : null;
            $condetion['pictureBooks_id'] = !empty($pictureBooks_id) ? $pictureBooks_id : null;
            $condetion['score'] = !empty($score) ? $score : 0;
            $condetion['user_voice_url'] = $userUrl;
            //把接收到的发声点URL路径做一下截取，只要分页的值
            preg_match('/page\d+/', $pictureBooksOfPart_Url, $pageStr);
            preg_match('/\d+/', $pageStr[0], $page);
            $condetion['page'] = empty($page[0])?0:$page[0];
            //查看该用户是否跟读过此发声点的语音
            $empty = D('Picture_books')->getResourceByWhere($condetion);//查询
            D('Picture_books')->startTrans();
            if (!empty($empty)) {
                //1.1先查询一下该发声点的所得分数，如果新的分数比旧的分数大则执行更新表数据的操作
                if ($empty[0]['score'] < $condetion['score']) {
                    if (D('Picture_books')->updataResource($condetion) === false) {
                        D('Picture_books')->rollback();
                        $this->showjson(400);
                    } else {
                        D('Picture_books')->commit();
                        $this->showjson(200);
                    }
                } else {
                    $this->showjson(200, '不做任何操作');
                }
            } else {
                //2如果数据库中这个ID不存在则执行插入的操作
                if (D('Picture_books')->insertResource($condetion) === false) {
                    D('Picture_books')->rollback();
                    $this->showjson(500);
                } else {
                    D('Picture_books')->commit();
                    $this->showjson(200);
                }
            }
        }
    }


}
?>