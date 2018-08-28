<?php
namespace ApiInterface\Controller\Version1_0;

use Think\Controller;

class ActivityController extends PublicController
{
    public $pageSize = 20;
    public $activityModel;

    public function __construct()
    {
        parent::__construct();
        $this->activityModel = D('Social_activity');
        $this->assign('oss_path', C('oss_path'));
    }

    /**
     * @描述：获取活动类型列表
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getClassList()
    {
        $result = $this->activityModel->getActivityClassList();
        $this->ajaxReturn(array('status' => 200, 'result' => $result));
    }

    /**
     * @描述：根据活动类型获取活动列表
     * @参数：cate[int] Y 活动类型
     * @参数：pageIndex[int] Y 页码索引
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getActivityByClass()
    {
        $category = getParameter('cate', 'int');
        $pageIndex = getParameter('pageIndex', 'int');
        $result = $this->activityModel->getActivities($category,'', $pageIndex, $this->pageSize);
        $this->ajaxReturn(array('status' => 200, 'result' => $result));
    }

    /**
     * @描述：获取最新活动
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getFirstActivity()
    {
        $category = getParameter('cate', 'int');
        $result = $this->activityModel->getActivities($category,'', 0, 1);
        foreach ($result as &$val) {
            $val->img_url = "http://" . WEB_URL . "/Resources/socialactivity/" . $val->short_content;
        }
        $this->ajaxReturn(array('status' => 200, 'result' => $result));
    }

    /**
     * @描述：获取我收藏的活动列表
     * @参数：user_id[int] Y 用户ID
     * @参数：role[int] Y 角色 0-教师 1-学生 2-家长
     * @参数：pageIndex[int] Y 页码索引
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    public function getMyFavor()
    {
        $userInfo['user_id'] = getParameter('user_id', 'int');
        $userInfo['user_type'] = getParameter('role', 'int') + 1;
        $pageIndex = getParameter('pageIndex', 'int');
        $result = $this->activityModel->getFavorActivities($userInfo, $pageIndex, $this->pageSize);
        foreach ($result as &$val) {
            $val->img_url = "http://" . WEB_URL . "/Resources/socialactivity/" . $val->short_content;
        }
        $this->ajaxReturn(array('status' => 200, 'result' => $result));
    }

    /**
     * @描述：活动详情
     * @参数：user_id[int] Y 用户ID
     * @参数：role[int] Y 角色 2-教师 3-学生 4-家长
     * @参数：id[int] Y 活动ID
     * @返回：活动HTML页面
     */
    public function activityDetails()
    {
        $id = getParameter('id', 'int');
        $userId = getParameter('user_id', 'int');
        $role = getParameter('role', 'int');

        $result = $this->activityModel->getActivityDetails($id);
        $this->assign('data', $result);

        $this->activityModel->setBrowseCountPlusOne($id);
        //报名信息
        $regInfo = $this->activityModel->getRegistered($id, $userId);
        $this->assign('registered', $regInfo['reged']);
        $this->assign('register_info', $regInfo['info']);

        //点赞收藏信息
        $this->assign('existedZan', $this->activityModel->getIsZan($id, $userId, $role));
        $this->assign('existedFavor', $this->activityModel->getIsFavor($id, $userId, $role));
        $this->display();
    }

    /**
     * @描述：活动报名
     * @参数：teacher_id[int] Y 教师ID
     * @参数：register_info[string] Y 报名信息
     * @参数：id[int] Y 活动ID
     * @返回：重定向活动HTML页面
     */
    public function registerActivity()
    {
        if ($_POST) {
            $id = getParameter('id', 'int');
            $teacherId = getParameter('teacher_id', 'int');
            $regInfo = getParameter('register_info', 'str');

            $teacherInfo = D('Auth_teacher')->getTeachInfo($teacherId);

            $regData = array(
                'user_id' => $teacherId,
                'register_info' => $regInfo,
                'user_type' => 1,
                'register_at' => time(),
                'user_name' => $teacherInfo['name'],
                'activity_id' => $id
            );
            //如果没有报名
            if (!$this->activityModel->hasRegActivity($id, $teacherId)) {
                //报名
                $this->activityModel->regActivity($regData);
            }
            $this->redirect("App/activityDetails?id=" . $id . "&tid=" . $teacherId . "&token=" . $teacherInfo['access_token']);
        }
    }
}