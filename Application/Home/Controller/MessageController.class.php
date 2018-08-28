<?php
namespace Home\Controller;

define('MESSAGE_APPPUSH', 1);
define('MESSAGE_MSGCENTER', 2);
define('MESSAGE_APPMSG', 3);

define('STATE_UNSEND',1);
define('STATE_SEND', 2);
define('STATE_FAIL', 3);
define('STATE_DRAWBACK', 4);

define('READSTATE_UNREAD',1);
define('READSTATE_READ',2);


use Think\Controller;
use Common\Common\CSV;
use Common\Common\push_function;

class MessageController extends PublicController
{
    public $model;
    private $messageKey;
    private $pageSize = 20;

    public function __construct()
    {
        parent::__construct();
        $this->model = D('Message');
        $this->messageKey = array(C('IOS_SEND_PUSH'), C('ANDROID_SEND_PUSH'));
        $this->pageSize = $this->getPageSize();
    }

    private function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * 消息列表页面
     *
     */
    public function messageList()
    {
        if (!session('?admin')) redirect(U('Index/index'));
        $queryArray['role_id'] = I('role_id');
        $queryArray['status'] = getParameter('sendState_id', 'int', false);
        $queryArray['send_time'] = getParameter('sendTime_id', 'str', false);
        $queryArray['receive_type'] = getParameter('receiveType_id', 'int', false);
        $queryArray['message_content'] = getParameter('keyword', 'str', false);
        $pageIndex = getParameter('p', 'int', false);

        $wherearr['role_id'] = $queryArray['role_id'];
        $wherearr['sendState_id'] = $queryArray['status'];
        $wherearr['sendTime_id'] = $queryArray['send_time'];
        $wherearr['receiveType_id'] = $queryArray['receive_type'];
        $wherearr['keyword'] = $queryArray['message_content'];


        $this->model->getMessageList($queryArray, $count, $result, 0, $pageIndex, $this->getPageSize());

        $Page = new \Think\Page($count[0]['count'], C('PAGE_SIZE_FRONT'));
        $Page->parameter = $wherearr;
        $show = $Page->show();
        $this->assign('page', $show);

        foreach ( $result as $k=>$v ){
            $content = strip_tags($v['message_content']);
            $content = mb_substr($content,0,20,"utf-8");

            if ( strlen($content) < 20) {
                $result[$k]['message_content'] =$content;
            } else {
                $result[$k]['message_content'] =$content.'...';
            }

        }

        $this->assign('list', $result);

        $this->assign('module', '消息管理中心');
        $this->assign('nav', '消息管理中心');
        $this->assign('subnav', '消息管理中心列表');

        $this->assign('ro_id', $queryArray['role_id']);
        $this->assign('ss_id', $queryArray['status']);
        $this->assign('s_id', $queryArray['send_time']);
        $this->assign('re_id', $queryArray['receive_type']);
        $this->assign('kw_id', $queryArray['message_content']);

        $this->display();
    }

    /**
     * 增加/修改消息AJAX
     */
    public function addEditMessage($message_title = '', $message_content = '', $receive_type = '', $role_id = '', $user_id = '')
    {

        if (!empty($message_title) || !empty($message_content) || !empty($receive_type) || !empty($role_id) || !empty($user_id)) {
            $data['title'] = $message_title;
            $data['truncated_title'] = $message_title;
            $data['message_content'] = $message_content;
            $data['receive_type'] = $receive_type;
            $data['role_id'] = $role_id;
            $data['message_type'] = 2;
            $data['receive_num'] = count(explode(',',$user_id));
            $userIdString = $user_id;
            $isSystemMessage = 1;
        } else {
            if (!session('?admin')) redirect(U('Index/index'));
            $data['id'] = getParameter('message_id', 'int', false);
            $data['title'] = getParameter('message_title', 'str');
            $data['truncated_title'] = substr($message_title,0,150);
            $data['message_content'] = getParameter('message_content', 'str');
            $data['receive_type'] = getParameter('receiveType_id', 'int');
            $data['role_id'] = getParameter('role_id', 'int');
            $userIdString = getParameter('user_ids', 'str');
            $isSystemMessage = 0;
        }
        $message_id = $this->model->addMessageData($data, $userIdString);
        if (1 != $isSystemMessage) {
            if ($message_id)
                $this->ajaxReturn(array('status' => 200, 'message' => '添加/编辑消息成功'));
            else
                $this->ajaxReturn(array('status' => 500, 'message' => '添加/编辑消息失败'));
        } else {
            if ($message_id)
                return $message_id;
            else
                return false;
        }
    }

    /**
     * 修改消息页面
     */
    public function modifyMessage()
    {
        $this->display();
    }

    /**
     * 发送一条消息
     * $usePrevChannelId 是否使用上一个CHANNELID
     * $isUserDefinedMessage 是否为透传消息
     * $extras 额外信息
     */
    public function sendMessage($messageId = '', $messageUrl = '',$pageCategory=2,$usePrevChannelId = 0,$isUserDefinedMessage = 0,$extras = [])
    {       
        //if (!session('?admin')) redirect(U('Index/index'));
        if (empty($messageId)) {
            $messageId = getParameter('message_id', 'int');
            $returnAjax = true;
        }
        else
        {
            $returnAjax = false;
        }
        $blackboard_category=7;
        $study_category=9;
        if($pageCategory==$blackboard_category || $pageCategory==$study_category){
            $messageUrl=$messageUrl.'&message_id='.$messageId;
        } 
        
        $sendMsg = $this->model->getMessageTitle($messageId);
        $messageCategory = $this->model->getMessageCategory($messageId);

        if (empty($messageUrl))
            $messageUrl = 'http://'. $_SERVER["SERVER_NAME"] .'/ApiInterface/'.APIINTERFACE_DIR . '/Message/messageDetail?id=' . $messageId;
        $customContent = [];
        if($isUserDefinedMessage == 1){
            $customContent = ['loginPast'=>1];
        }
        switch ($messageCategory) {
            case MESSAGE_APPPUSH:
                $userInfos = $this->model->getUserChannelWithUnReadCountMachineType($messageId,$usePrevChannelId);

                $bSend = batchSendPushUserDevice($this->messageKey, array('title' => $sendMsg, 'description' => ' ','extras'=>$extras), $userInfos, array('url' => $messageUrl,'category' =>$pageCategory ),$isUserDefinedMessage);
                break;
            case MESSAGE_MSGCENTER:
                $bSend = 1;
                break;
            case MESSAGE_APPMSG:
                $userInfos = $this->model->getUserChannelWithUnReadCountMachineType($messageId,$usePrevChannelId);
                $bSend = batchSendPushUserDevice($this->messageKey, array('title' => $sendMsg, 'description' => ' ','extras'=>$extras), $userInfos, array('url' => $messageUrl,'category' =>$pageCategory),$isUserDefinedMessage);
                break;
            default:
                $this->ajaxReturn(array('status' => 500, 'message' => '获取消息类型失败'));
        }
        if ($bSend == 1) {
            $this->model->setMessageState($messageId, STATE_SEND);
            $this->model->setMessageSendTime($messageId, time());
            if($returnAjax)
                $this->ajaxReturn(array('status' => 200, 'message' => '发送成功'));
            else
                return true;
        } else {
            $this->model->setMessageState($messageId, STATE_FAIL);
            $this->model->setMessageSendTime($messageId, time());
            if($returnAjax)
                $this->ajaxReturn(array('status' => 500, 'message' => '发送失败'));
            else
                return false;

        }
    }

    /**
     * 撤回一条消息（仅限于消息中心的消息）
     */
    public function withDrawMessage()
    {
        if (!session('?admin')) redirect(U('Index/index'));
        $messageId = getParameter('id', 'int');
        $messageCategory = $this->model->getMessageCategory($messageId);
        if (APPPUSH_MESSAGE == $messageCategory)
            $this->ajaxReturn(array('status' => 500, 'message' => '无法撤回APP推送的消息'));
        $bDrawBack = $this->model->withdrawMessageInfo($messageId);
        if ($bDrawBack)
            $this->ajaxReturn(array('status' => 200, 'message' => '撤回成功'));
        else
            $this->ajaxReturn(array('status' => 500, 'message' => '撤回失败'));
    }

    //删除消息
    public function deleteMessage()
    {
        if (!session('?admin')) redirect(U('Index/index'));
        if (session('admin.role') == 3) {
            echo 'error';
            die;
        }
        $id = getParameter('id', 'int');
        $bDeleted = $this->model->deleteMessage($id);
        if ($bDeleted) {
            $this->ajaxReturn(array('status' => 200, 'message' => '删除成功'));
        } else {
            $this->ajaxReturn(array('status' => 500, 'message' => '删除失败'));
        }
    }


    //开始发送消息
    public function sendMessageTest()
    {
        if (session('admin.role') == 3) {
            echo 'error';
            die;
        }
        $id = $_GET['id'];
        $message = D('Message');
        $channel_id = $message->getUserCannel_id($id); //根据消息id获取该用户下的所有百度推送渠道id

        $default_apiKey = 'bGTXvnRG4MEfAwXaVNQ0Gqzz';
        $default_secretkey = '7a6xppOPN4WDm88T9ifoPHIbUfKbwxWR';
        $sendmsg = array(
            'title' => '一封迟到的信',
            'description' => '你猜内容',
        );
        //对所有的设备进行推送
        $is_send = sendPushAll($default_apiKey, $default_secretkey, $sendmsg);

        if ($is_send) {
            $maps['status'] = 2;
            M('role_message')->where("id=" . $id)->save($maps);
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('error');
        }

    }

    public function getPersonalInfo(&$userId, &$role)
    {
        if (session('teacher.id')) {
            $userId = session('teacher.id');
            $role = 2;
        } else if (session('student.id')) {
            $userId = session('student.id');
            $role = 3;
        } else if (session('parent.id')) {
            $userId = session('parent.id');
            $role = 4;
        } else
            $this->ajaxReturn('session error');
    }

    /**
     *  个人中心消息列表页面
     */
    public function PersonalMessageList()
    {
        //按条数分页
        $queryArray['send_time'] = getParameter('sendTime', 'str', false);
        $queryArray['title'] = getParameter('keyword', 'str', false);
        $queryArray['receive_type'] = array('in', array(2, 3));
        $pageIndex = getParameter('p', 'int', false);
        $this->getPersonalInfo($userId, $role);
        $queryArray['role_id'] = array($role);
        $queryArray['user_id'] = $userId;
        $result = $this->model->getMessageList($queryArray, $count, $data, '1', $pageIndex, $this->pageSize);
        $this->ajaxReturn(array('status' => 200, 'message' => 'success', 'result' => $data));
    }

    /**
     *  个人中心消息详情页面
     */
    public function messageDetails($messageId = 0)
    {
        if ($messageId == 0)
            $messageId = getParameter('message_id', 'int');
        $this->getPersonalInfo($userId, $role);
        //这里判断了该用户是否有权限获取该条信息
        $result = $this->model->getMessageInfo($messageId, $userId, $role);
        //设置已读
        $this->model->setMessageReadState(($messageId), $userId, $role, READSTATE_READ);
        $this->assign('data', $result);
        $this->display();
    }

    /**
     * 个人中心指定消息标记已读未读
     *
     */

    public function setMessageReadState()
    {
        $messageId = getParameter('message_id', 'iArr');
        if (!$messageId)
            $this->ajaxReturn(array('status' => 500, 'message' => '消息ID无效'));
        $state = getParameter('state', 'int');
        $this->getPersonalInfo($userId, $role);
        if ($this->model->setMessageReadState($messageId, $userId, $role, $state)) {
            $this->ajaxReturn(array('status' => 200, 'message' => '设置成功'));
        } else {
            $this->ajaxReturn(array('status' => 500, 'message' => '设置失败'));
        }

    }

    /**
     * 获取当前用户未读数量
     */
    public function getUnreadMessagesCount()
    {
        $this->getPersonalInfo($userId, $role);
        $this->ajaxReturn(array('status' => 200, 'result' => $this->model->getUnreadMessagesCount($userId, $role)));
    }

    /**
     * 个人中心消息全部标为已未读
     */
    public function setAllMessageReadState()
    {
        $state = getParameter('state', 'int');
        $this->getPersonalInfo($userId, $role);
        if ($this->model->setMessageReadState('', $userId, $role, $state)) {
            $this->ajaxReturn(array('status' => 200, 'message' => '设置成功'));
        } else {
            $this->ajaxReturn(array('status' => 500, 'message' => '设置失败'));
        }
    }

    /**
     * 个人中心删除消息记录
     */
    public function deletePersonalMessage()
    {
        $messageId = getParameter('message_id', 'iArr');
        if(!$messageId)
        {
            $this->ajaxReturn(array('status' => 500, 'message' => '消息ID获取失败'));
        }
        $this->getPersonalInfo($userId, $role);
        if ($this->model->deletePersonalMessage($messageId, $userId, $role)) {
            $this->ajaxReturn(array('status' => 200, 'message' => '删除成功'));
        } else {
            $this->ajaxReturn(array('status' => 500, 'message' => '删除失败'));
        }
    }

    /**
     * 创建发送消息
     */
    public function messageCreate()
    {
        if (!session('?admin')) redirect(U('Index/index'));
        $this->assign('module', '消息管理中心');
        $this->assign('nav', '消息管理中心');
        $this->assign('subnav', '消息管理创建消息');
        $data = $this->model->getCoursesAndGrades();
        //print_r($teacherids);die();
        $this->assign('courses', $data['courses']);
        $this->assign('grades', $data['grades']);
        $this->display();
    }

    //加载教师列表
    private function insertKeyIntoSet($set,$value)
    {
        if(!in_array($value,$set))
        {
            $set[] = $value;
        }
        return $set;
    }
    public function getTeacherList()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $queryParas = $_GET;
        $teacher_name = $queryParas['teacher_name'];
        $status = intval($queryParas['lock_status']);
        $join = array();
        $where = array();
        if (!empty($status)) {
            $where['auth_teacher.lock'] = $status - 1;
            $this->assign('status', $status);
        }

        if (session('admin.role') == 3) {
            $where['auth_teacher.school_id'] = session('admin.school_id');
        }
        $join = $this->insertKeyIntoSet($join,'INNER JOIN dict_schoollist on auth_teacher.school_id = dict_schoollist.id');
        $join = $this->insertKeyIntoSet($join,'LEFT JOIN account_user_and_auth ON account_user_and_auth.user_id = auth_teacher.id and account_user_and_auth.role_id = 2');

        if (!empty($queryParas['province_id'])) {
            $where['dict_schoollist.provice_id'] = $queryParas['province_id'];
            $join = $this->insertKeyIntoSet($join,'INNER JOIN dict_schoollist on auth_teacher.school_id = dict_schoollist.id');
        }
        if (!empty($queryParas['city_id'])) {
            $where['dict_schoollist.city_id'] = $queryParas['city_id'];
            $join = $this->insertKeyIntoSet($join,'INNER JOIN dict_schoollist on auth_teacher.school_id = dict_schoollist.id');
        }
        if (!empty($queryParas['country_id'])) {
            $where['dict_schoollist.district_id'] = $queryParas['country_id'];
            $join = $this->insertKeyIntoSet($join,'INNER JOIN dict_schoollist on auth_teacher.school_id = dict_schoollist.id');
        }
        if (!empty($queryParas['school_id']))
            $where['auth_teacher.school_id'] = $queryParas['school_id'];
        if (!empty($queryParas['course_id'])) {
            $where['auth_teacher_second.course_id'] = $queryParas['course_id'];
            $join = $this->insertKeyIntoSet($join,'INNER JOIN auth_teacher_second ON auth_teacher_second.teacher_id = auth_teacher.id');
            $join = $this->insertKeyIntoSet($join,'INNER JOIN dict_course ON auth_teacher_second.course_id = dict_course.id');
        }
        if (!empty($queryParas['grade_id'])) {
            $where['auth_teacher_second.grade_id'] = $queryParas['grade_id'];
            $join = $this->insertKeyIntoSet($join,'INNER JOIN auth_teacher_second ON auth_teacher_second.teacher_id = auth_teacher.id');
            $join = $this->insertKeyIntoSet($join,'INNER JOIN dict_grade ON auth_teacher_second.grade_id = dict_grade.id');
            $join = $this->insertKeyIntoSet($join,'LEFT JOIN biz_class ON biz_class.class_teacher_id = auth_teacher.id');
        }
        if (!empty($queryParas['teacher_telephone']))
            $where['auth_teacher.telephone'] = array('like', '%' . $queryParas['teacher_telephone'] . '%');
        if (!empty($queryParas['vip_type'])) {
            if ($queryParas['vip_type'] == 2) {
                $where['account_user_and_auth.auth_id'] = array('exp', 'is null');
                $join = $this->insertKeyIntoSet($join,'LEFT JOIN account_user_and_auth ON account_user_and_auth.user_id = auth_teacher.id and account_user_and_auth.role_id = 2');
            } else {
                $where['account_user_and_auth.auth_id'] = $queryParas['vip_type'];
                $join = $this->insertKeyIntoSet($join,'LEFT JOIN account_user_and_auth ON account_user_and_auth.user_id = auth_teacher.id and account_user_and_auth.role_id = 2');
            }
        }

        if (!empty($queryParas['teacher_time_id'])) {
            $startTime = $this->getMonthRange($queryParas['teacher_time_id'], true);
            $where['account_user_and_auth.auth_start_time'] = array('lt', $startTime);
            $where['account_user_and_auth.auth_end_time'] = array('egt', $startTime);
            $join = $this->insertKeyIntoSet($join,'LEFT JOIN account_user_and_auth ON account_user_and_auth.user_id = auth_teacher.id and account_user_and_auth.role_id = 2');
        }

        if (!empty($teacher_name)) {
            $where['_string'] = "(auth_teacher.name like '%$teacher_name%') ";
        }

        $is_checked = $queryParas['is_checked'];
        $ids = $queryParas['ids'];
        if ($is_checked == 1) { //查询选中的
            $map['auth_teacher.id'] = array('in', $ids);
            $info = $this->model->getTeacherAjaxData($map,$join);
        } else {//查询正常的
            $info = $this->model->getTeacherAjaxData($where,$join);
        } 

        $data['list'] = $info['result'];
        $data['page'] = $info['page'];
        $data['count'] = $info['count'];
        $this->ajaxReturn($data);


    }

    public function getMonthRange($date, $returnFirstDay = true)
    {
        $timestamp = strtotime($date);
        if ($returnFirstDay) {
            $monthFirstDay = date('Y-m-d 00:00:00', $timestamp);
            return strtotime($monthFirstDay);
        } else {

            $monthLastDay = date('Y-m-d 23:59:59', $timestamp);
            return strtotime($monthLastDay);
        }
    }


    //加载学生列表

    public function getStuList()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $filter['stuname'] = $_REQUEST['stuname'];
        $filter['status'] = $_REQUEST['status'];
        $filter['vip_type'] = $_REQUEST['vip_type'];
        $filter['stu_time_id'] = $_REQUEST['stu_time_id'];

        $stuname = $filter['stuname'];
        $status = intval($filter['status']);

        if (!empty($stuname)) {
            $where['auth_student.student_name'] = array('like', '%' . $stuname . '%');
        }

        if (session('admin.role') != 3) {
            $filter['province'] = $_REQUEST['province'];
            $filter['city'] = $_REQUEST['city'];
            $filter['district'] = $_REQUEST['country_id'];
            $filter['school_id'] = $_REQUEST['school_id'];
            $filter['grade_id'] = $_REQUEST['stu_grade_id'];
            $filter['stuparentphone'] = $_REQUEST['stuparentphone'];
            if (!empty($filter['stuparentphone'])) $where['auth_student.parent_tel'] = $filter['stuparentphone'];
            if (!empty($filter['province'])) $where['dict_schoollist.provice_id'] = $filter['province'];
            if (!empty($filter['city'])) $where['dict_schoollist.city_id'] = $filter['city'];
            if (!empty($filter['district'])) $where['dict_schoollist.district_id'] = $filter['district'];
            if (!empty($filter['school_id'])) $where['dict_schoollist.id'] = $filter['school_id'];
            if (!empty($filter['grade_id'])) $where['biz_class.grade_id'] = $filter['grade_id'];

        };

        if (!empty($filter['vip_type'])) {
            if ($filter['vip_type'] == 2) {
                $where['account_user_and_auth.auth_id'] = array('exp', 'is null');
            } else {
                $where['account_user_and_auth.auth_id'] = $filter['vip_type'];
            }
        }

        if (!empty($filter['stu_time_id'])) {
            $startTime = $this->getMonthRange($filter['stu_time_id'], true);
            $where['account_user_and_auth.auth_start_time'] = array('lt', $startTime);
            $where['account_user_and_auth.auth_end_time'] = array('egt', $startTime);
        }


        if (session('admin.role') == 3) {
            $where['auth_student.school_id'] = session('admin.school_id');
        }
        if (!empty($status)) {
            $where['auth_student.lock'] = $status - 1;
            $this->assign('status', $status);
        }

        $is_checked = $_REQUEST['is_checked'];
        $ids = $_REQUEST['ids'];
        if ($is_checked == 1) { //查询选中的
            $map['auth_student.id'] = array('in', $ids);
            $info = $this->model->getStuAjaxData($map);
        } else {//查询正常的
            $info = $this->model->getStuAjaxData($where);
        }

        $data['list'] = $info['result'];
        $data['page'] = $info['page'];
        $data['count'] = $info['count'];

        $this->ajaxReturn($data);
    }

    //加载家长
    public function getParentList()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $parent_name = $_GET['parent_name'];
        $parent_phone = $_GET['parent_phone'];
        $status = intval($_GET['status']);
        $parent_type = $_GET['parent_type'];
        $parent_time_id = $_GET['parent_time_id'];

        if (!empty($parent_name)) {
            $where['auth_parent.parent_name'] = array('like', '%' . $parent_name . '%');
        }
        if (!empty($parent_phone)) {
            $where['auth_parent.telephone'] = $parent_phone;
        }

        if (!empty($status)) {
            $where['auth_parent.lock'] = $status - 1;
        }

        if (session('admin.role') == 3) {
            $where['auth_student.school_id'] = session('admin.school_id');
        }

        if (!empty($parent_type)) {
            if ($parent_type == 2) {
                $where['account_user_and_auth.auth_id'] = array('exp', 'is null');
            } else {
                $where['account_user_and_auth.auth_id'] = $parent_type;
            }
        }

        if (!empty($parent_time_id)) {
            $startTime = $this->getMonthRange($parent_time_id, true);
            $where['account_user_and_auth.auth_start_time'] = array('lt', $startTime);
            $where['account_user_and_auth.auth_end_time'] = array('egt', $startTime);
        }

        $is_checked = $_REQUEST['is_checked'];
        $ids = $_REQUEST['ids'];
        if ($is_checked == 1) { //查询选中的
            $map['auth_parent.id'] = array('in', $ids);
            $info = $this->model->getParentAjaxData($map);
        } else {//查询正常的
            $info = $this->model->getParentAjaxData($where);
        }


        $data['list'] = $info['result'];
        $data['page'] = $info['page'];
        $data['count'] = $info['count'];
        $this->ajaxReturn($data);

    }

    //消息入库操作
    public function saveMessageInfo()
    {
        set_time_limit(0);
        $teaids = $_REQUEST['teaids'];
        $stuids = $_REQUEST['stuids'];
        $parentids = $_REQUEST['parentids'];


        $is_all_teacher = $_REQUEST['is_all_teacher'];
        $is_all_stu = $_REQUEST['is_all_stu'];
        $is_all_parent = $_REQUEST['is_all_parent'];

        $role_id = $_REQUEST['role_id'];//用户角色
        $apppush = $_REQUEST['apppush'];
        $title = $_REQUEST['message_title'];
        $content = $_REQUEST['content'];
        $is_content = strip_tags($content);
        if (strlen($is_content) < 20) {
            $data['code'] = 400;
            $data['info'] = '消息推送内容太少了,不能少于20个字';
            $this->ajaxReturn($data);
        }

        if (mb_strlen($is_content,'utf-8') > 500) {
            $data['code'] = 400;
            $data['info'] = '字数超过最大允许值';
            $this->ajaxReturn($data);
        }

        if (empty($teaids) && empty($stuids) && empty($parentids) && $is_all_teacher == 'false' && $is_all_stu == 'false' && $is_all_parent == 'false') {
            $data['code'] = 400;
            $data['info'] = '没有选择要推送的用户';
            $this->ajaxReturn($data);
        }

        if ($apppush == 'true') {
            $receive_type = 3;
        } else {
            $receive_type = 2;
        }

        if (empty($title)) {
            $title = strip_tags($_REQUEST['content']);
            $title = substr($title, 0, 20);
            $truncated_title_lenth = strip_tags($_REQUEST['content']);
            $truncated_title_lenth = substr($truncated_title_lenth, 0, 20);

        } else {
            $truncated_title_lenth = $title;
        }



        $data = array(
            'role_id' => $role_id,
            'title' => $title,
            'message_content' => $content,
            'receive_type' => $receive_type,
            'send_time' => time(),
            'message_type' => 1,
            'truncated_title' =>$truncated_title_lenth
        );
        M('role_message')->startTrans();
        //M('role_message')->rollback();
        $message_id = $this->model->addMessageInfo($data); //先入库

        //全部选中后是否进行了保存
        $is_all_select_teacher = $_REQUEST['is_all_select_teacher'];
        $is_all_select_stu = $_REQUEST['is_all_select_stu'];
        $is_all_select_parent = $_REQUEST['is_all_select_parent'];

        if ($message_id) {

            if ($is_all_teacher == "true" && $is_all_select_teacher ==1) { //所有的老师都发
                $this->model->pushAllUser('2', $message_id);
            } else { //只发选择的部分

                if (!empty($teaids)) {
                    $tids = explode(",", $teaids);

                    if (is_array($tids)) {
                        foreach ($tids as $tk => $tv) {
                            $teachermap = array(
                                'message_id' => $message_id,
                                'role_id' => 2,
                                'user_id' => $tv,
                                'addtime' => time(),
                            );
                            $this->model->pushAddUser($teachermap);
                        }
                    } else {

                        $teachermap = array(
                            'message_id' => $message_id,
                            'role_id' => 2,
                            'user_id' => $tids,
                            'addtime' => time(),
                        );

                        $this->model->pushAddUser($teachermap);
                    }
                }

            }

            if ($is_all_stu == "true" && $is_all_select_stu == 1) { //所有的学生都发
                $this->model->pushAllUser('3', $message_id);
            } else { //只发选择的部分

                if (!empty($stuids)) {
                    $sids = explode(",", $stuids);
                    if (is_array($sids)) {

                        foreach ($sids as $sk => $sv) {
                            $stumap = array(
                                'message_id' => $message_id,
                                'role_id' => 3,
                                'user_id' => $sv,
                                'addtime' => time(),
                            );

                            $this->model->pushAddUser($stumap);
                        }
                    } else {
                        $stumap = array(
                            'message_id' => $message_id,
                            'role_id' => 3,
                            'user_id' => $sids,
                            'addtime' => time(),
                        );

                        $this->model->pushAddUser($stumap);
                    }
                }

            }

            if ($is_all_parent == "true" && $is_all_select_parent == 1) { //所有的家长都发
                $this->model->pushAllUser('4', $message_id);
            } else { //只发选择的部分

                if (!empty($parentids)) {
                    $pids = explode(",", $parentids);
                    if (is_array($pids)) {

                        foreach ($pids as $pk => $pv) {
                            $parentmap = array(
                                'message_id' => $message_id,
                                'role_id' => 4,
                                'user_id' => $pv,
                                'addtime' => time(),
                            );

                            $this->model->pushAddUser($parentmap);
                        }
                    } else {
                        $parentmap = array(
                            'message_id' => $message_id,
                            'role_id' => 4,
                            'user_id' => $pids,
                            'addtime' => time(),
                        );

                        $this->model->pushAddUser($parentmap);
                    }
                }

            }

            $updateid = $this->model->updateMessageInfo($message_id);

            if ($updateid) {
                M('role_message')->commit();
                $message_info['code'] = 200;
                $message_info['info'] = '成功';
                $this->ajaxReturn($message_info);
            } else {
                $message_info['code'] = 400;
                $message_info['info'] = '失败';
                $this->ajaxReturn($message_info);
            }


        }


    }

    //修改消息
    public function editMessageInfo()
    {
        $this->assign('module', '消息管理中心');
        $this->assign('nav', '消息管理中心');
        $this->assign('subnav', '消息管理中心修改消息');
        $id = $_REQUEST['id'];
        $messageInfo = $this->model->getMessageDetails($id);
        $role_userid = $this->model->getMessageidUserid($id);//根据message_id获取三个角色的发送的id
        $this->assign('role_userid', $role_userid);
        $this->assign('messageInfo', $messageInfo);

        $data = $this->model->getCoursesAndGrades();
        //print_r($teacherids);die();
        $this->assign('courses', $data['courses']);
        $this->assign('grades', $data['grades']);

        $this->display();
    }

    //保存要修改的消息
    public function saveEditMessage()
    {
        set_time_limit(0);
        $teaids = $_REQUEST['teaids'];
        $stuids = $_REQUEST['stuids'];
        $parentids = $_REQUEST['parentids'];


        $is_all_teacher = $_REQUEST['is_all_teacher'];
        $is_all_stu = $_REQUEST['is_all_stu'];
        $is_all_parent = $_REQUEST['is_all_parent'];

        $message_id = $_REQUEST['message_id'];//消息id

        $role_id = $_REQUEST['role_id'];//用户角色
        $apppush = $_REQUEST['apppush'];
        $title = $_REQUEST['message_title'];
        $content = $_REQUEST['content'];
        $is_content = strip_tags($content);
        if (strlen($is_content) < 20) {
            $data['code'] = 400;
            $data['info'] = '消息推送内容太少了';
            $this->ajaxReturn($data);
        }

        if (empty($teaids) && empty($stuids) && empty($parentids) && $is_all_teacher == 'false' && $is_all_stu == 'false' && $is_all_parent == 'false') {
            $data['code'] = 400;
            $data['info'] = '没有选择要推送的用户';
            $this->ajaxReturn($data);
        }

        if ($apppush == 'true') {
            $receive_type = 3;
        } else {
            $receive_type = 2;
        }

        if (empty($title)) {
            $title = strip_tags($_REQUEST['content']);
            $title = substr($title, 0, 20);
        }

        $truncated_title_lenth = strip_tags($_REQUEST['content']);
        $truncated_title_lenth = substr($truncated_title_lenth, 0, 20);

        //全部选中后是否进行了保存
        $is_all_select_teacher = $_REQUEST['is_all_select_teacher'];
        $is_all_select_stu = $_REQUEST['is_all_select_stu'];
        $is_all_select_parent = $_REQUEST['is_all_select_parent'];

        $savedata = array(
            'role_id' => $role_id,
            'title' => $title,
            'message_content' => $content,
            'receive_type' => $receive_type,
            'message_type' => 1,
            'truncated_title' => $truncated_title_lenth,
        );

        $edie_message_id = $this->model->editMessageInfo($savedata, $message_id); //先入库

        if ($edie_message_id) {
            $del_id = $this->model->delMessageUser($message_id);
        }


        if ($del_id !=false && $edie_message_id!=false ) {

            if ($is_all_teacher == "true" && $is_all_select_teacher==1) { //所有的老师都发
                $this->model->pushAllUser('2', $message_id);
            } else { //只发选择的部分

                if (!empty($teaids)) {
                    $tids = explode(",", $teaids);
                    if (is_array($tids)) {
                        foreach ($tids as $tk => $tv) {
                            $teachermap = array(
                                'message_id' => $message_id,
                                'role_id' => 2,
                                'user_id' => $tv,
                                'addtime' => time(),
                            );
                            $this->model->pushAddUser($teachermap);
                        }
                    } else {
                        $teachermap = array(
                            'message_id' => $message_id,
                            'role_id' => 2,
                            'user_id' => $tids,
                            'addtime' => time(),
                        );
                        $this->model->pushAddUser($teachermap);
                    }
                }

            }

            if ($is_all_stu == "true" && $is_all_select_stu==1) { //所有的学生都发
                $this->model->pushAllUser('3', $message_id);
            } else { //只发选择的部分

                if (!empty($stuids)) {
                    $sids = explode(",", $stuids);
                    if (is_array($sids)) {
                        foreach ($sids as $sk => $sv) {
                            $stumap = array(
                                'message_id' => $message_id,
                                'role_id' => 3,
                                'user_id' => $sv,
                                'addtime' => time(),
                            );

                            $this->model->pushAddUser($stumap);
                        }
                    } else {
                        $stumap = array(
                            'message_id' => $message_id,
                            'role_id' => 3,
                            'user_id' => $sids,
                            'addtime' => time(),
                        );

                        $this->model->pushAddUser($stumap);
                    }
                }

            }

            if ($is_all_parent == "true" && $is_all_select_parent==1) { //所有的家长都发
                $this->model->pushAllUser('4', $message_id);
            } else { //只发选择的部分

                if (!empty($parentids)) {
                    $pids = explode(",", $parentids);
                    if (is_array($pids)) {
                        foreach ($pids as $pk => $pv) {
                            $parentmap = array(
                                'message_id' => $message_id,
                                'role_id' => 4,
                                'user_id' => $pv,
                                'addtime' => time(),
                            );

                            $this->model->pushAddUser($parentmap);
                        }
                    } else {
                        $parentmap = array(
                            'message_id' => $message_id,
                            'role_id' => 4,
                            'user_id' => $pids,
                            'addtime' => time(),
                        );

                        $this->model->pushAddUser($parentmap);
                    }
                }

            }

            $updateid = $this->model->updateMessageInfo($message_id);

            if ($updateid) {
                M('role_message')->commit();
                $data['code'] = 200;
                $data['info'] = '修改成功';
                $this->ajaxReturn($data);
            } else {
                M('role_message')->rollback();
                $data['code'] = 400;
                $data['info'] = '修改失败';
                $this->ajaxReturn($data);
            }


        } else { //没有修改任何数据
            $data['code'] = 200;
            $data['info'] = '修改成功';
            $this->ajaxReturn($data);
        }


    }

    public function pushMessageTest()
    {
        //$userInfos = $this->model->getUserChannelWithUnReadCount($messageId);
        $userInfos = array(array(
            'machine_type' => 2,
            'channel_id' => '3889924855524511468',
            'unreadnum' => 113
        ));
        //var_dump($this->messageKey);exit;
        $bSend = batchSendPushUserDevice($this->messageKey, array('title' => 'hi message title' . time(), 'description' => 'des'), $userInfos, array('url' => 'http://www.baidu.com', 'category' => 3));
        echo $bSend;
    }

    /**
     * @描述：增加用户行为所产生的消息并推送至用户
     * @参数：category[string] Y 推送消息类型字符串,见C('PUSH_MESSAGE')
     * @参数：role[int] 用户角色 2--教师 3--学生 4--家长
     * @参数：userId[string] Y 用户ID字符串,多个用户用逗号分隔
     * @参数：parameters[array] Y 消息参数数组
     *        $parameters = array( 'msg' => array('hi','hello','ttt') ,                         //FORMAT_MSG中格式化参数对应数组
    'url' => array( 'type' => 0, 'data' => array(0,1,2,3,4))     //FORMAT_URL中格式化参数对应数组,type=0时,仅向FORMAT_URL中的第一个%s填充MESSAGE_ID
    type=1时,向FORMAT_URL中的各个格式化参数%s依次填充data中的数据
    );
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */
    

    public function addPushUserMessage($category, $role, $userId, $parameters,$usePrevChannelId = 0,$isUserDefinedMessage = 0)
    {


        $config_arr=C('PUSH_MESSAGE');
        $format_msg=$config_arr[$category]['FORMAT_MSG'];

        $message_content = vsprintf($format_msg,$parameters['msg']);

        if (strpos($message_content,'jingbanyunxx') != false) {
            $message_title_copy = explode('jingbanyunxx',$message_content);
            $message_title = $message_title_copy[0];
            $message_content = $message_title_copy[1];
        } else {
            $message_title = substr(strip_tags($message_content),0,200);
        }

        $message_title = preg_replace ('/&nbsp;/is', '', $message_title);
        $message_title = trim($message_title);

        $message_type=$config_arr[$category]['TYPE'];

        $format_url=$config_arr[$category]['FORMAT_URL'];

        $format_category=$config_arr[$category]['CATEGORY'];

        $messageId = $this->addEditMessage($message_title, $message_content, $message_type, $role, $userId);
        if($parameters['url']['type'] == 0) //trans messageid into url
        {
            $messageUrl = 'http://'. $_SERVER["SERVER_NAME"] .sprintf($format_url, $messageId);
        }
        else
        {
            $messageUrl = 'http://'. $_SERVER["SERVER_NAME"] .vsprintf($format_url, $parameters['url']['data']);
        }

        $messageCategory = $format_category;

        return $this->sendMessage($messageId, $messageUrl,$messageCategory,$usePrevChannelId,$isUserDefinedMessage,$config_arr[$category]['extras']);
    }

    /**
     * @描述：向所有用户推送消息(消息不入库)
     * @参数：category[string] Y 推送消息类型字符串,见C('PUSH_MESSAGE')
     * @参数：parameters[array] Y 消息参数数组
     *        $parameters = array( 'msg' => array('hi','hello','ttt') ,                         //FORMAT_MSG中格式化参数对应数组
    'url' => array( 'type' => 0, 'data' => array(0,1,2,3,4))     //FORMAT_URL中格式化参数对应数组,type=0时,仅向FORMAT_URL中的第一个%s填充MESSAGE_ID
    type=1时,向FORMAT_URL中的各个格式化参数%s依次填充data中的数据
    );
     * @返回值：array(
     *    status 状态码
     *    result 结果数组
     * )
     */

    public function addPushAllMessage($category, $parameters)
    {
        $config_arr=C('PUSH_MESSAGE');
        $format_msg=$config_arr[$category]['FORMAT_MSG'];

        $message_content = vsprintf($format_msg,$parameters['msg']);
        $message_title = substr($message_content,0,150);

        $format_url=$config_arr[$category]['FORMAT_URL'];

        $messageCategory=$config_arr[$category]['CATEGORY'];

        $messageUrl = 'http://'. $_SERVER["SERVER_NAME"] .vsprintf($format_url, $parameters['url']['data']);

        return sendPushAllDevice($this->messageKey,array('title' => $message_title, 'description' => ' '),  array('url' => $messageUrl,'category' =>$messageCategory));
    }

        
    /*
     * 向指定用户推送消息(消息不入库)
     */
    public function addPushClockMessage($teacher_array){
        $push_message_title='准备上课啦!'; 
        return sendPushClockSend($this->messageKey,$push_message_title,$teacher_array);
    }


    //消息详情
    public function lookMessageInfo() {
        $this->assign('module', '消息管理中心');
        $this->assign('nav', '消息管理中心');
        $this->assign('subnav', '消息管理中心消息详情');
        $id = $_REQUEST['id'];
        $messageInfo = $this->model->getMessageDetails( $id );

        if ( in_array(2,explode(',',$messageInfo['role_id'])) ){
            $this->is_teacher_active = 'active';
        } else {
            if ( in_array(3,explode(',',$messageInfo['role_id'])) ){
                $this->is_stu_active = 'active';
            } else {
                $this->is_parent_active = 'active';
            }
        }

        if ( in_array(2,explode(',',$messageInfo['role_id'])) ){
            $this->is_teacher_active_show = 'in active';
        } else {
            if ( in_array(3,explode(',',$messageInfo['role_id'])) ){
                $this->is_stu_active_show = 'in active';
            } else {
                $this->is_parent_active_show = 'in active';
            }
        }


        $data = $this->model->getCoursesAndGrades();
        //print_r($teacherids);die();
        $this->assign('courses', $data['courses']);
        $this->assign('grades', $data['grades']);

        $this->assign('messageInfo',$messageInfo);
        $this->display();
    }

    //加载选中的所有学生
    public function getSelectAllStu(){
        $message_id = $_REQUEST['message_id'];
        $type = $_REQUEST['type'];

        $filter['stuname'] = $_REQUEST['stuname'];
        $filter['status'] = $_REQUEST['status'];
        $filter['vip_type'] = $_REQUEST['vip_type'];
        $filter['stu_time_id'] = $_REQUEST['stu_time_id'];

        $stuname = $filter['stuname'];
        $status=intval($filter['status']);

        if (!empty($stuname)) {
            $where['auth_student.student_name']=array('like', '%' . $stuname . '%');
        }

        if(session('admin.role')!=3){
            $filter['province'] = $_REQUEST['province'];
            $filter['city'] = $_REQUEST['city'];
            $filter['district'] = $_REQUEST['country_id'];
            $filter['school_id'] = $_REQUEST['school_id'];
            $filter['grade_id'] = $_REQUEST['stu_grade_id'];
            $filter['stuparentphone'] = $_REQUEST['stuparentphone'];
            if (!empty($filter['stuparentphone'])) $where['auth_student.parent_tel'] = $filter['stuparentphone'];
            if (!empty($filter['province'])) $where['dict_schoollist.provice_id'] = $filter['province'];
            if (!empty($filter['city'])) $where['dict_schoollist.city_id'] = $filter['city'];
            if (!empty($filter['district'])) $where['dict_schoollist.district_id'] = $filter['district'];
            if (!empty($filter['school_id'])) $where['dict_schoollist.id'] = $filter['school_id'];
            if (!empty($filter['grade_id'])) $where['biz_class.grade_id'] = $filter['grade_id'];

        };

        if(!empty($filter['vip_type'])) {
            if ($filter['vip_type'] ==2) {
                $where['account_user_and_auth.auth_id'] = array('exp','is null');
            } else{
                $where['account_user_and_auth.auth_id'] = $filter['vip_type'];
            }
        }

        if(!empty($filter['stu_time_id'])) {
            $startTime = $this->getMonthRange($filter['stu_time_id'], true);
            $where['account_user_and_auth.auth_start_time'] = array('lt',$startTime);
            $where['account_user_and_auth.auth_end_time'] = array('egt',$startTime);
        }


        if (session('admin.role') == 3) {
            $where['auth_student.school_id'] = session('admin.school_id');
        }
        if(!empty($status)){
            $where['auth_student.lock']=$status-1;
            $this->assign('status', $status);
        }

        $where['receive_message_user.message_id'] = $message_id;
        $where['receive_message_user.role_id'] = $type;

        $info = $this->model->getSelectAllStuModel( $where );

        $data['list'] = $info['result'];
        $data['page'] = $info['page'];
        $data['count'] = $info['count'];
        $this->ajaxReturn( $data );
    }

    //加载选中的所有家长
    public function getSelectAllParent(){

        $message_id = $_REQUEST['message_id'];
        $type = $_REQUEST['type'];

        $parent_name = $_GET['parent_name'];
        $parent_phone = $_GET['parent_phone'];
        $status = $_GET['status'];
        $status=intval($_GET['status']);
        $parent_type = $_GET['parent_type'];
        $parent_time_id = $_GET['parent_time_id'];

        if (!empty($parent_name)) {
            $where['auth_parent.parent_name'] = array('like', '%' . $parent_name . '%');
        }
        if (!empty($parent_phone)) {
            $where['auth_parent.telephone'] = $parent_phone;
        }

        if(!empty($status)){
            $where['auth_parent.lock']=$status-1;
        }

        if (session('admin.role') == 3) {
            $where['auth_student.school_id'] = session('admin.school_id');
        }

        if(!empty($parent_type)) {
            if ($parent_type ==2) {
                $where['account_user_and_auth.auth_id'] = array('exp','is null');
            } else{
                $where['account_user_and_auth.auth_id'] = $parent_type;
            }
        }

        if(!empty($parent_time_id)) {
            $startTime = $this->getMonthRange($parent_time_id, true);
            $where['account_user_and_auth.auth_start_time'] = array('lt',$startTime);
            $where['account_user_and_auth.auth_end_time'] = array('egt',$startTime);
        }

        $where['receive_message_user.message_id'] = $message_id;
        $where['receive_message_user.role_id'] = $type;

        $info = $this->model->getSelectAllParentModel( $where );

        $data['list'] = $info['result'];
        $data['page'] = $info['page'];
        $data['count'] = $info['count'];
        $this->ajaxReturn( $data );
    }

    //加载所有选中的老师
    public function getSelectAllTearch(){
        $message_id = $_REQUEST['message_id'];
        $type = $_REQUEST['type'];
        $queryParas = $_GET;
        $teacher_name = $queryParas['teacher_name'];
        $status=intval($queryParas['lock_status']);
        $where=array();
        if(!empty($status)){
            $where['auth_teacher.lock']=$status-1;
            $this->assign('status', $status);
        }

        if (session('admin.role') == 3) {
            $where['auth_teacher.school_id'] = session('admin.school_id');
        }

        if(!empty($queryParas['province_id']))
            $where['dict_schoollist.provice_id'] = $queryParas['province_id'];
        if(!empty($queryParas['city_id']))
            $where['dict_schoollist.city_id'] = $queryParas['city_id'];
        if(!empty($queryParas['country_id']))
            $where['dict_schoollist.district_id'] = $queryParas['country_id'];
        if(!empty($queryParas['school_id']))
            $where['auth_teacher.school_id'] = $queryParas['school_id'];
        if(!empty($queryParas['course_id']))
            $where['auth_teacher_second.course_id'] = $queryParas['course_id'];
        if(!empty($queryParas['grade_id']))
            $where['auth_teacher_second.grade_id'] = $queryParas['grade_id'];
        if(!empty($queryParas['teacher_telephone']))
            $where['auth_teacher.telephone'] = $queryParas['teacher_telephone'];
        if(!empty($queryParas['vip_type'])) {
            if ($queryParas['vip_type'] ==2) {
                $where['account_user_and_auth.auth_id'] = array('exp','is null');
            } else{
                $where['account_user_and_auth.auth_id'] = $queryParas['vip_type'];
            }
        }

        if(!empty($queryParas['teacher_time_id'])) {
            $startTime = $this->getMonthRange($queryParas['teacher_time_id'], true);
            $where['account_user_and_auth.auth_start_time'] = array('lt',$startTime);
            $where['account_user_and_auth.auth_end_time'] = array('egt',$startTime);
        }

        if (!empty($teacher_name)) {
            $where['_string'] = "(auth_teacher.name like '%$teacher_name%') ";
        }
        $where['receive_message_user.message_id'] = $message_id;
        $where['receive_message_user.role_id'] = $type;

        $info = $this->model->getSelectAllTeacherModel( $where );

        $data['list'] = $info['result'];
        $data['page'] = $info['page'];
        $data['count'] = $info['count'];
        $this->ajaxReturn( $data );

    }

    //查询排名前20的用户
    public function ranking() {
        set_time_limit(0);
        $userString = file_get_contents('./Public/ranking.txt');
        $oldUser = explode(',',$userString);
        $newUser = M('auth_teacher')->order("points desc")->field('id')->limit(20)->select();
        $newUserString = '';

        foreach ($newUser as $k=>$v) {
            if ($k== 0) {
                $newUserString.=$v['id'];
            } else {
                $newUserString.= ','.$v['id'];
            }
            //如果存在就不发送，不存在就发送
            if ( !in_array($v['id'],$oldUser) ) {
                echo "执行次数:".$k;
                $parameters = array( 'msg' => array(),'url' => array( 'type' => 0 ));
                $this->addPushUserMessage('TEACHER_RANK20',2, $v['id'],$parameters);
            }
            //sleep(1);
        }
        //发送完毕写入文件
        file_put_contents('./Public/ranking.txt',$newUserString);
        echo "执行完毕";
    }

    //根据老师的id查询老师的学生
    public function stuRanking() {
        $userString = file_get_contents('./Public/sturanking.txt'); //自己的老师
        $oldUser = explode(',',$userString);

        $newUserString = file_get_contents('./Public/ranking.txt');
        $newUserdata = explode(',',$newUserString);

        $newUserString = '';

        foreach ($newUserdata as $k=>$v) {
            if ($k== 0) {
                $newUserString.=$v;
            } else {
                $newUserString.= ','.$v;
            }
            if ( !in_array($v,$oldUser) ) { //如果不存在就根据$v去查询所有的学生
                echo "执行次数:".$k;
                $map['biz_class.class_teacher_id'] = $v;
                $map['biz_class_student.student_id'] = array('exp','is not null');
                $res = M('biz_class')
                    ->join('left join biz_class_student on biz_class.id=biz_class_student.class_id')
                    ->field("biz_class_student.student_id,biz_class.class_teacher")
                    ->where( $map )
                    ->group('biz_class.id')
                    ->select();
                $teachername= '';
                $newlist = array();
                if (!empty($res)) {
                    foreach ($res as $rk=> $rv ) {
                        $newlist[] = $rv['student_id'];
                        if ($rk == 0) {
                            $teachername = $rv['class_teacher'];
                        }
                    }
                }
                $newlist = array_unique($newlist);
                $sendmessageuser = implode(',',$newlist);
                $parameters = array( 'msg' => array($teachername),'url' => array( 'type' => 0 ));
                $this->addPushUserMessage('STUDENTTEACHER_RANK20',3, $sendmessageuser,$parameters); //群发学生
            }
            //sleep(1);
        }

        file_put_contents('./Public/sturanking.txt',$newUserString);
        echo "执行完毕";

    }

    //根据老师的id查询所有的家长
    public function parentRanking() {
        $userString = file_get_contents('./Public/parentranking.txt'); //自己的老师
        $oldUser = explode(',',$userString);

        $newUserString = file_get_contents('./Public/ranking.txt');
        $newUserdata = explode(',',$newUserString);

        $newUserString = '';

        foreach ($newUserdata as $k=>$v) {
            if ($k== 0) {
                $newUserString.=$v;
            } else {
                $newUserString.= ','.$v;
            }
            if ( !in_array($v,$oldUser) ) { //如果不存在就根据$v去查询所有的学生
                echo "执行次数:".$k;
                $map['biz_class.class_teacher_id'] = $v;
                $map['biz_class_student.student_id'] = array('exp','is not null');
                $res = M('biz_class')
                    ->join('left join biz_class_student on biz_class.id=biz_class_student.class_id')
                    ->join('left join auth_student on biz_class_student.student_id=auth_student.id')
                    ->field("biz_class_student.student_id,biz_class.class_teacher,auth_student.parent_id")
                    ->where( $map )
                    ->group('biz_class.id')
                    ->select();

                $teachername= '';
                $newlist = array();
                if (!empty($res)) {
                    foreach ($res as $rk=> $rv ) {
                        $newlist[] = $rv['parent_id'];
                        if ($rk == 0) {
                            $teachername = $rv['class_teacher'];
                        }
                    }
                }

                $newlist = array_unique($newlist);
                $sendmessageuser = implode(',',$newlist);

                $parameters = array( 'msg' => array($teachername),'url' => array( 'type' => 0 ));
                $this->addPushUserMessage('PARENT_STUDENTTEACHER_RANK20',4, $sendmessageuser,$parameters); //群发学生
            }
            //sleep(1);
        }

        file_put_contents('./Public/parentranking.txt',$newUserString);
        echo "执行完毕";
    }

    //后台推送京版活动消息
    public function sendJbActivity() {
        $id = $_REQUEST['id'];
        $title = $_REQUEST['title'];
        $parameters = array( 'msg' => array($title),'url' => array( 'type' => 1,'data'=>array($id) ));
        $is_send = $this->addPushAllMessage('ACTIVITY_PUBLISHED',$parameters); //群发学生
        if ( $is_send ) {
            $this->ajaxReturn( 'success' );
        } else{
            $this->ajaxReturn( 'error' );
        }
    }

    //订单失效时间 修改状态为取消订单
    public function getOrderOrderfailure() {
        $ids = [];
        $where = [
            'order_status' => 1,
            'order_info.is_delete' => 1,
        ];

        $join[] = 'knowledge_resource ON knowledge_resource.id = order_info.resources_id';

        $order = M('order_info')
                ->join( $join )
                ->where( $where )
                ->field("order_info.*,knowledge_resource.name")
                ->select();

        foreach ( $order as $k=>$v ) {

            if ( $v['create_at']+1800 < time() ) { //已过期
                $parameters = [];
                $parameters = array(
                    'msg' => array(
                        $v['name'],
                    ),
                    'url' => array( 'type' => 0)
                );
                $this->addPushUserMessage('ORDER_FAILURE', $v['user_role'],$v['user_id'], $parameters);
                $map['id'] = $v['id'];
                $savedata['order_status'] = 3;
                $savedata['order_cancel_create_at'] = time();
                M('order_info')->where( $map )->save( $savedata );
                file_put_contents('./Public/order.json',$v['order_sn'].'|'.date('Y-m-d H:i:s').PHP_EOL,FILE_APPEND);
            }
        }

    }


}
