<?php
namespace ApiInterface\Controller\Version1_2;

class DictController extends PublicController
{
    public function __construct() {
        parent::__construct();
    }
    /**
     * @描述：获取学科列表
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getResourceCourseList()
    {
        $this->ajaxReturn(array('status' => 200,'result' => D('Dict_course')->getResourceCourseList()));
    }

    /**
     * @描述：获取学科列表
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getCourseList()
    {
        $this->ajaxReturn(array('status' => 200,'result' => D('Dict_course')->getCourseList()));
    }
    /**
     * @描述：获取年级列表
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getGradeList()
    {
        $this->ajaxReturn(array('status' => 200,'result' => D('Dict_grade')->getGradeList()));
    }

    /**
     * @描述：获取学科年级分册资源类型全列表
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getAllList()
    {
        $out = array();
        $courses = D('Dict_course')->getResourceCourseList();
        $grades = D('Dict_grade')->getGradeList();

        $classInfo = array(
            array(
                'id'=>'1',
                'name'=>'足球',
            ),
        );

        $book_type = array(
            array(
                'id'=>'1',
                'name'=>'上册',
            ),
            array(
                'id'=>'2',
                'name'=>'下册',
            ),
        );

        $file_type = array(
            array(
                'value'=>'video',
                'name'=>'视频',
            ),
            array(
                'value'=>'audio',
                'name'=>'音频',
            ),
            array(
                'value'=>'image',
                'name'=>'图片',
            ),
            array(
                'value'=>'word',
                'name'=>'Word',
            ),
            array(
                'value'=>'pdf',
                'name'=>'PDF',
            ),
            array(
                'value'=>'ppt',
                'name'=>'PPT',
            )
        );

        $out['courses'] = $courses;
        $out['grades'] = $grades;
        $out['class_info'] = $classInfo;
        $out['book_type'] = $book_type;
        $out['file_type'] = $file_type;

        echo json_encode($out);exit;
    }

    public function getAvailableCourse()
    {
        $gradeId  = getParameter('grade_id', 'int');
        $this->showMessage(200,'success',D('Dict_course')->getAvailableCourse($gradeId));
    }

    public function getAvailableGrade()
    {
        $courseId  = getParameter('course_id', 'int');
        $this->showMessage(200,'success',D('Dict_grade')->getAvailableGrade($courseId));
    }

    /**
     * @描述：根据学科查询年级（注册/完善信息用）
     * @参数：courseId[int] Y 学科ID
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getGradeInfoByCourse()
    {
        $courseId  = getParameter('courseId', 'int');
        $this->showMessage(200,'success',D('Dict_grade')->getAvailableRegGrade($courseId));
    }

    /**
     * @描述：查询可用学生所属年级、家长关注年级列表
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getStudentParentGradeList()
    {
        $gradeList = D('Dict_grade')->getGradeList(1);
        $this->showMessage(200,'success',$gradeList);
    }

    /**
     * @描述：查询教材版本列表
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getVersionList()
    {
         $this->showMessage(200,'success',C('TEXTBOOK_VERSION'));
    }
}