<?php
namespace Home\Controller;

use Think\Controller;

class TeacherController extends PublicController
{
    ///?m=my&c=account&a=index
    public function index()
    {
        if (!session('?teacher')) {
            redirect(U('Teach/login'), 0, '登录超时，页面跳转中...');
        }

        $this->display();
    }

    //登陆验证并跳转
    public function login()
    {
        if ($_POST) {
            $check['telephone'] = $_POST['telephone'];
            $check['password'] = sha1($_POST['password']);
            $check['flag'] = 1;

            $TeacherModel = M('auth_teacher');
            $result = $TeacherModel->where($check)->find();
            if ($result) {
                session_start();
                session('teacher', $result);
                $this->redirect("Teacher/index");
            } else {
                $this->error('登陆失败，即将返回，请输入正确的账号及密码。');
            }
        } else {
            session('teacher', null);
            $this->display();
        }
    }

    //退出登录
    public function logout()
    {
        session('teacher', null);
        $this->redirect("Teacher/login");
    }

    //OA首页
    public function oa()
    {
        $this->assign('nav', '教育信息发布系统');
        $this->assign('subnav', '我收到的信息');

        $page = $_GET['page'];
        if (empty($page)) $page = 1;

        $Model = M('oa_message');
        $result = $Model->where('status=2')
            ->order('create_at desc')
            ->page($page, 50)
            ->select();

        $this->assign('list', $result);

        $this->display();
    }

    //我的回执
    public function oaMyReceipts()
    {
        $this->assign('nav', '教育信息发布系统');
        $this->assign('subnav', '我的回执');

        $page = $_GET['page'];
        if (empty($page)) $page = 1;

        $Model = M('oa_message');
        $result = $Model->where('status=2')
            ->order('create_at desc')
            ->page($page, 50)
            ->select();

        $this->assign('list', $result);

        $this->display();
    }

    //我的发布
    public function oaMyPublished()
    {
        $this->assign('nav', '教育信息发布系统');
        $this->assign('subnav', '我发布的信息');

        $page = $_GET['page'];
        if (empty($page)) $page = 1;

        $Model = M('oa_message');
        $result = $Model->where('status=2')
            ->order('create_at desc')
            ->page($page, 50)
            ->select();

        $this->assign('list', $result);

        $this->display();
    }

    //专家资讯信息列表
    public function expertInformationList()
    {
        $this->assign('nav', '专家资讯');
        $this->assign('subnav', '资讯信息');

        $page = $_GET['page'];
        if (empty($page)) $page = 1;

        $Model = M('social_expert_information');
        $result = $Model->where('status=2')
            ->order('create_at desc')
            ->page($page, 50)
            ->select();

        $this->assign('list', $result);

        $this->display();
    }

    //专家资讯信息详情
    public function expertInformationDetails($id)
    {
        $this->assign('nav', '专家资讯');
        $this->assign('subnav', '资讯信息');

        $Model = M('social_expert_information');
        $check['id'] = $id;
        $result = $Model->where($check)->find();

        $this->assign('data', $result);

        $this->display();
    }


    //获取最新的十条精通活动
    public function getLatestActivities()
    {
        $SocialActivity = M('social_activity');
        $result = $SocialActivity->where('status=2')->order('create_at desc')->limit(10)->select();
        $this->ajaxReturn($result);
    }

    //获取精通活动的总数量
    public function getActivitiesTotalCount()
    {
        $SocialActivity = M('social_activity');
        $result = $SocialActivity->where('status=2')->count();
        $this->ajaxReturn($result);
    }

    //精通活动列表页面
    public function activities()
    {
        $this->assign('nav', '精通活动');
        $this->assign('subnav', '活动列表');

        $SocialActivity = M('social_activity');
        $result = $SocialActivity->where('status=2')->order('create_at desc')->select();

        $this->assign('activities', $result);

        $this->display();
    }

    //我报名的精通活动
    public function myRegisteredActivities()
    {
        $this->assign('nav', '精通活动');
        $this->assign('subnav', '我报名的');

        $SocialActivity = M('social_activity');
        $result = $SocialActivity
            ->join('social_activity_register on social_activity.id=social_activity_register.activity_id')
            ->where('social_activity_register.user_type=1 and social_activity_register.user_id=' . session('teacher.id'))->order('create_at desc')->select();

        $this->assign('activities', $result);

        $this->display();
    }

    //精通活动详情
    public function activityDetails($id)
    {
        $this->assign('nav', '精通活动');
        $this->assign('subnav', '活动列表');

        $SocialActivity = M('social_activity');
        $check['id'] = $id;
        $result = $SocialActivity->where($check)->find();

        $this->assign('activity', $result);

        $SocialActivityRegister = M('social_activity_register');
        //报名总数 $registerTotal
        $check2['activity_id'] = $id;
        $registerTotal = $SocialActivityRegister->where($check2)->count();
        $this->assign('registerTotal', $registerTotal);

        //报名详情
        $registerDetails = $SocialActivityRegister->where($check2)
            ->field('user_name')
            ->select();
        $this->assign('registerDetails', $registerDetails);

        //判断我是否已经报名 todo

        $this->display();
    }

    //教师风采
    public function teacherStyle(){
        $this->display();
    }


    ///////////////////教学+
    //资源列表
    public function resourceList()
    {
        $this->assign('nav', '教学资源');
        $this->assign('subnav', '资源仓库');

        $page = $_GET['page'];
        if (empty($page)) $page = 1;

        $Model = M('biz_resource');
        $result = $Model->where('status=2')
            ->order('create_at desc')
            ->page($page, 50)
            ->select();

        $this->assign('list', $result);

        $this->display();
    }

    //收藏的资源
    public function favoriteResourceList()
    {
        $this->assign('nav', '教学资源');
        $this->assign('subnav', '我收藏的资源');

        $page = $_GET['page'];
        if (empty($page)) $page = 1;

        $Model = M('biz_resource');
        $result = $Model->where('status=2')
            ->order('create_at desc')
            ->page($page, 50)
            ->select();

        $this->assign('list', $result);

        $this->display();
    }

    //发布的资源
    public function mySharedResourceList()
    {
        $this->assign('nav', '教学资源');
        $this->assign('subnav', '我发布的资源');

        $page = $_GET['page'];
        if (empty($page)) $page = 1;

        $Model = M('biz_resource');
        $result = $Model->where('status=2')
            ->order('create_at desc')
            ->page($page, 50)
            ->select();

        $this->assign('list', $result);

        $this->display();
    }

    //我要发布
    public function publishResourcePage(){

        $this->assign('nav', '教学资源');
        $this->assign('subnav', '我要发布');

        $this->display();
    }

    public function publishResourceCusPage(){

        $this->assign('nav', '教学资源');
        $this->assign('subnav', '我要发布');
        $this->display();
    }

    public function publishResourcePptPage(){

        $this->assign('nav', '教学资源');
        $this->assign('subnav', '我要发布');

        $this->display();
    }

    public function createPPT(){

        $this->display();
    }


    //论坛/////
    //论坛板块
    public function forum(){

        $this->assign('nav', '编教论坛');
        $this->assign('subnav', '论坛板块');

        $Model = M('bbs_class');
        $result = $Model->where("status=1 and grade='小学'")->order('sort_order asc')->select();
        $this->assign('list1', $result);

        $result = $Model->where("status=1 and grade='初中'")->order('sort_order asc')->select();
        $this->assign('list2', $result);

        $result = $Model->where("status=1 and grade='高中'")->order('sort_order asc')->select();
        $this->assign('list3', $result);

        $this->display();
    }

    //我收藏的帖子
    public function myFavoriteForum(){
        $this->assign('nav', '编教论坛');
        $this->assign('subnav', '我收藏的板块');

        $Model = M('bbs_class');
        $result = $Model
            ->join('bbs_my_favorite_class on bbs_class.id=bbs_my_favorite_class.class_id')
            ->field('bbs_class.*')
            ->where("bbs_class.status=1 and bbs_my_favorite_class.user_type=1 and bbs_my_favorite_class.user_id=".session('teacher.id'))->order('sort_order asc')->select();
        $this->assign('list', $result);

        $this->display();
    }

    //某个板块下的帖子
    public function bbsTopics($id){
        $this->assign('nav', '编教论坛');
        $this->assign('subnav', '论坛板块');

        $Model = M('bbs_topic');
        $result = $Model
            ->where("class_id=$id")->order('create_at desc')->select();
        $this->assign('list', $result);

        $this->display();
    }
    //我发布的帖子
    public function bbsMyPublishedTopics(){

        $this->assign('nav', '编教论坛');
        $this->assign('subnav', '我发布的帖子');

        $Model = M('bbs_topic');
        $result = $Model
            ->join('bbs_class on bbs_topic.class_id=bbs_class.id')
            ->field('bbs_topic.*,bbs_class.class_name')
            ->where("bbs_topic.creater_id=" . session('teacher.id'))->order('create_at desc')->select();
        $this->assign('list', $result);

        $this->display();
    }

    //我回复的帖子
    public function bbsMyReceivedTopics(){

        $this->assign('nav', '编教论坛');
        $this->assign('subnav', '我回复的帖子');

        $Model = M('bbs_reply');
        $result = $Model
            ->join('bbs_topic on bbs_reply.topic_id=bbs_topic.id')
            ->join('bbs_class on bbs_topic.class_id=bbs_class.id')
            ->field('bbs_reply.*,bbs_class.class_name,bbs_topic.view_count,bbs_topic.reply_count')
            ->where("bbs_reply.creater_id=" . session('teacher.id'))->order('create_at desc')->select();
        $this->assign('list', $result);

        $this->display();
    }



    /////////////////////////////班级行
    //小黑板
    public function blackboard(){
        $this->display();
    }









}