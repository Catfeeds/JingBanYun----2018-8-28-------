<?php
namespace ApiInterface\Controller\Version1_1;
use Think\Controller;

class BjResourceController extends PublicController
{
  public $pageSize = 15;
  public $Model;
  public function __construct() {
         parent::__construct();
         $this->Model = D('Biz_bj_resources');
         $this->assign('oss_path', C('oss_path'));
     }
  function getPageSize(){
    return $this->pageSize;
  }
    /**
     * @描述：获取我收藏的京版资源列表
     * @参数：course_id[int] N 学科ID
     * @参数：grade_id[int] N 年级ID
     * @参数：keyword[string] N 关键字
     * @参数：pageIndex[int] N 页码
     * @参数：textbook_id[int] Y 学期
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
      $courseId =getParameter('course_id','int',false);
      $gradeId =getParameter('grade_id','int',false);
      $pageIndex =getParameter('pageIndex','int',false);
      $keyword =getParameter('keyword','str',false);
      $textbookId = getParameter('textbook_id','int',false);
      $type = getParameter('type','str',false);
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
     * @描述：根据学科年级关键字查询资源列表
     * @参数：course_id[int] N 学科ID
     * @参数：grade_id[int] N 年级ID
     * @参数：schoolterm[int] N 分册
     * @参数：keyword[string] N 关键字
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getResources4QrCode()
    {
        $courseId =getParameter('course_id','int',false);
        $gradeId =getParameter('grade_id','int',false);
        $schoolTerm = getParameter('schoolterm','int',false);
        $pageIndex =getParameter('pageIndex','int',false);
        $keyword =getParameter('keyword','str',false);

        $queryParameters = array(
          'course_id' => $courseId,
          'grade_id'  => $gradeId,
          'school_term' => $schoolTerm,
          'keyword'   => $keyword
        );
        $this->ajaxReturn(array('status' => 200,'result' => $this->Model->getResourceList($queryParameters,$pageIndex,$this->getPageSize())));
    }

    /**
     * @描述：查询京版资源列表
     * @参数：course_id[int] N 学科ID
     * @参数：grade_id[int] N 年级ID
     * @参数：keyword[string] N 关键字
     * @参数：pageIndex[int] N 页码
     * @参数：type[string] N 类型
     * @参数：textbook_id[int] N 学期
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getResourceList()
    {
        $courseId =getParameter('course_id','int',false);
        $gradeId =getParameter('grade_id','int',false);
        $pageIndex =getParameter('pageIndex','int',false);
        $keyword =getParameter('keyword','str',false);
        $textbookId = getParameter('textbook_id','int',false);
        $type = getParameter('type','str',false);
        $queryParameters = array(
            'course_id' => $courseId,
            'grade_id'  => $gradeId,
            'school_term' => $textbookId,
            'type' => $type,
            'keyword'   => $keyword
        );
        $this->ajaxReturn(array('status' => 200,'result' => $this->Model->getResourceList($queryParameters,$pageIndex,$this->getPageSize())));
    }
    /**
     * @描述：京版资源详情分享
     * @参数：id[int] Y 资源ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function jbResourceDetails()
    {
        $id =getParameter('id','int',true);
        $role =getParameter('role','int',false);
        $userId =getParameter('user_id','int',false);   
        $result = $this->Model->getResourceDetails($id);                    
    
        $this->assign('subnav', $result['name']);
        $this->assign('data', $result);

        $contact_result = $this->Model->getContactResourcePath($id);
        $this->assign('contact_data', $contact_result);

        $this->assign('existedZan', $this->Model->getIsZan($id, $userId, $role));
        $this->assign('existedFavor', $this->Model->getIsFavor($id, $userId, $role));
        $this->assign('jb_id',$id);
            
        //观看次数+1
        if(session('teacher') != 'youke') {
            $this->Model->setBrowseCountPlusOne($id);
        }

        $arr = explode(".", $result[file_path]);
        $this->assign('localPdffileName', '../../../Resources/jb/' . $arr[0] . ".pdf");
        $result['name_uion'] = urlencode($result['name']);
        $this->assign('data', $result);
        $this->assign('REMOTE_ADDR',C('REMOTE_ADDR'));
        $apkinfo = file_get_contents("http://www.jingbanyun.com/index.php?m=Home&c=Download&a=version&ostype=Android");
        $apkinfo = json_decode($apkinfo,true);
        $apkurl = $apkinfo['data']['download_path'];
        $this->assign('apkurl',$apkurl);

        $url = str_replace('jbResourceDetails','bjResourceDetails',WEB_URL.$_SERVER['REQUEST_URI']);
        $this->assign('urldata',"http://".$url ); 
         
        $this->assign('host','http://'.$_SERVER['HTTP_HOST']);
        if( !(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'qq')) && !(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'micromessenger'))){
            $this->display('bjResourceDetails');
        } else {
            $this->display();
        }

    }
    /**
     * @描述：京版资源详情
     * @参数：id[int] Y 资源ID
     * @参数：user_id[int] 当前用户ID
     * @参数：role[int] Y 当前角色 0--教师 1--学生 2--家长
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function bjResourceDetails()
    {
        $this->jbResourceDetails();
    }


}
?>