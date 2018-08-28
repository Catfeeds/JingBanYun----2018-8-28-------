<?php
namespace ApiInterface\Controller\Version1_0;
use Think\Controller;

class ResourceController extends PublicController
{
  public $pageSize = 15;
  public $Model;
  public function __construct() {
         parent::__construct();
         $this->Model = D('Biz_resource');
         $this->assign('oss_path', C('oss_path'));
     }
  function getPageSize(){
    return $this->pageSize;
  }
    /**
     * @描述：获取我收藏的教师资源列表
     * @参数：course_id[int] N 学科ID
     * @参数：grade_id[int] N 年级ID
     * @参数：keyword[string] N 关键字
     * @参数：pageIndex[int] N 页码
     * @参数：textbook_id[int] N 学期
     * @参数：type[string] N 类型
     * @参数：user_id[int] Y 用户ID
     * @参数：role[int] Y 角色 0--教师 1--学生 2--家长
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
  public function getMyCollected()
  {
      $courseId =getParameter('courseId','int',false);
      $gradeId =getParameter('gradeId','int',false);
      $pageIndex =getParameter('pageIndex','int',false);
      $keyword =getParameter('keyword','str',false);
      $textbookId = getParameter('textbook_id','int',false);
      $type = getParameter('type','int',false);
      $userId = getParameter('user_id','int',true);
      $role = getParameter('role','int',true);
      $queryParameters = array(
          'course_id' => $courseId,
          'grade_id'  => $gradeId,
          'school_term' => $textbookId,
          'type' => $type,
          'keyword'   => $keyword
      );
      $this->ajaxReturn(array('status' => 200,'result' => $this->Model->getResourceList($queryParameters,$pageIndex,$this->getPageSize(),$userId,$role)));
  }
    /**
     * @描述：获取我发布的教师资源列表
     * @参数：pageIndex[int] N 页码
     * @参数：user_id[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getMyPublished()
    {
        $pageIndex =getParameter('pageIndex','int',false);
        $userId = getParameter('user_id','int',true);
        $queryParameters = array();

        $userInfo = D('Auth_teacher')->getTeachInfo($userId);
        if (preg_match('/Resources/', $userInfo['avatar'])){
            $message = C('oss_path').$userInfo['avatar'];
        } else {
            $message = "http://".WEB_URL."/Uploads/Avatars/".$userInfo['avatar'];
        }
        $this->ajaxReturn(array('status' => 200,'message' => $message,'result' => $this->Model->getResourceList($queryParameters,$pageIndex,$this->getPageSize(),'','',$userId)));
    }
    /**
     * @描述：根据学科年级关键字查询资源列表
     * @参数：course_id[int] N 学科ID
     * @参数：grade_id[int] N 年级ID
     * @参数：pageIndex[int] N 页码
     * @参数：textbook_id[int] N 学期
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
  public function getResourcesByCourseAndTextbook()
  {
      $courseId =getParameter('course_id','int',false);
      $gradeId =getParameter('grade_id','int',false);
      $pageIndex =getParameter('pageIndex','int',false);
      $textbookId = getParameter('textbook_id','int',false);
      $queryParameters = array(
          'course_id' => $courseId,
          'grade_id'  => $gradeId,
          'school_term' => $textbookId
      );
      $this->ajaxReturn(array('status' => 200,'result' => $this->Model->getResourceList($queryParameters,$pageIndex,$this->getPageSize())));
  }

    /**
     * @描述：教师资源详情
     * @参数：id[int] Y 资源ID
     * @参数：user_id[int] 当前用户ID
     * @参数：role[int] Y 当前角色 0--教师 1--学生 2--家长
     * @返回值：详情页HTML
     */
    public function resourceDetails()
    {
        $id =getParameter('id','int',true);
        $role =getParameter('role','int',true);
        $userId =getParameter('user_id','int',true);
        $result = $this->Model->getResourceDetails($id);

        $this->assign('subnav', $result['name']);
        $this->assign('data', $result);

        $contact_result = $this->Model->getContactResourcePath($id);
        $this->assign('contact_data', $contact_result);

        //判断登陆者是否和发布者是一人
        if($result['teacher_id']==$userId && ($role == 2)){
            $this->assign('operation_status',1);
        }else{
            $this->assign('operation_status',2);
        }
        $this->assign('existedZan', $this->Model->getIsZan($id, $userId, $role));
        $this->assign('existedFavor', $this->Model->getIsFavor($id, $userId, $role));
        $this->Model->setBrowseCountPlusOne($id);

        $this->display();
    }

    /**
     * @描述：教师资源详情分享页面
     * @参数：id[int] Y 资源ID
     * @参数：user_id[int] 当前用户ID
     * @参数：role[int] Y 当前角色 0--教师 1--学生 2--家长
     * @返回值：详情页HTML
     */
    public function resourceDetailsShare()
    {
        $apkinfo = file_get_contents("http://www.jingbanyun.com/index.php?m=Home&c=Download&a=version&ostype=Android");
        $apkinfo = json_decode($apkinfo,true);
        $apkurl = $apkinfo['data']['download_path'];
        $this->assign('apkurl',$apkurl);
        $this->resourceDetails();
    }
    /**
     * @描述：获取资源是否点赞
     * @参数：id[int] Y 资源ID
     * @参数：user_id[int] 当前用户ID
     * @参数：role[int] Y 当前角色 0--教师 1--学生 2--家长
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function hasZan()
    {
        $id =getParameter('id','int',true);
        $role =getParameter('role','int',true);
        $userId =getParameter('user_id','int',true);
        $result = $this->Model->getIsZan($id,$userId,$role);
        $this->ajaxReturn(array('status' => 200,'message' => $result,'result' => $result));
    }
    /**
     * @描述：获取资源是否收藏
     * @参数：id[int] Y 资源ID
     * @参数：user_id[int] 当前用户ID
     * @参数：role[int] Y 当前角色 0--教师 1--学生 2--家长
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function hasCollect()
    {
        $id =getParameter('id','int',true);
        $role =getParameter('role','int',true);
        $userId =getParameter('user_id','int',true);
        $result = $this->Model->getIsZan($id,$userId,$role);
        $this->ajaxReturn(array('status' => 200,'message' => $result,'result' => $result));
    }
}
?>