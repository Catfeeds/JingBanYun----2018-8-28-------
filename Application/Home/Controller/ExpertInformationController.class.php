<?php
namespace Home\Controller;

use Think\Controller;
define('SHUFFLING',7);//新闻轮播管理
define('TEACH',8);//教学动态管理
define('WINWORK',9);//获奖作品管理
define('HOT',10);//热点话题管理
define('EDUCATION',11);//教育新闻管理
class ExpertInformationController extends PublicController
{
    /*
     *资讯首页
     */
    function expertInformationList()
    {
        if(!session('?teacher') && !session('?student') && !session('?parent')){
            redirect(U('Index/index'));
        }
        A('Home/Common')->getUserIdRole($userId,$role);;
        A('Home/Common')->authJudgement();
        switch ($role) {
            case ROLE_TEACHER:
                layout('teacher_layout_1');
                break;
            case ROLE_STUDENT:
                layout('student_layout_1');
                break;
            case ROLE_PARENT:
                layout('parent_layout_1');
                break;
            default:
                layout('teacher_layout_1');
                break;
        }
        //轮播图
        //查询轮播时间

        $data = D('Information')->getIndexColumnResource(SHUFFLING);
        //var_dump($data);die;
        //教学动态
        $data1 = D('Information')->getIndexColumnResource(TEACH);
        //var_dump($data1);die;
        //获奖作品
        $data2 = D('Information')->getIndexColumnResource(WINWORK);
        //教育新闻
        $data3 = D('Information')->getIndexColumnResource(EDUCATION);
        //热点话题
        $data4 = D('Information')->getIndexColumnResource(HOT);

        $columnResources = D('Information')->getColumn();
        $this->assign('data',$data['resource']);
        $this->assign('data1',$data1['resource']);
        $this->assign('data2',$data2['resource']);
        $this->assign('data3',$data3['resource']);
        $this->assign('data4',$data4['resource']);
        $this->assign('columnResources', $columnResources);
        $this->assign('module', '励耘圈');
        $this->assign('nav', '专家资讯');
        $this->assign('subnav', '资讯信息');
        $this->assign('navicon', 'zhuanjiazixun');


        $this->display();
    }

    /*
     *资讯搜索结果页
     */
    public function expertInformationSearch()
    {
        if(!session('?teacher') && !session('?student') && !session('?parent')){
            redirect(U('Index/index'));
        }
        $pageIndex = getParameter('p', 'int', false);
        $pageIndex = empty($pageIndex) ? 0 : $pageIndex;
        A('Home/Common')->getUserIdRole($userId,$role);;
        A('Home/Common')->authJudgement();
        switch ($role) {
            case ROLE_TEACHER:
                layout('teacher_layout_1');
                break;
            case ROLE_STUDENT:
                layout('student_layout_1');
                break;
            case ROLE_PARENT:
                layout('parent_layout_1');
                break;
            default:
                layout('teacher_layout_1');
                break;
        }

        $Model = M('social_expert_information');
        $where['status'] = 4;
        $keyword = trim(I('keyword'));
        $time_status = getParameter('time_status', 'str', false);


        if ($keyword){
            $filter['keyword'] = preg_replace('/\s+/', ' ', $keyword);
            $filter['keyword'] = preg_replace('/\%+/', '\%', $filter['keyword']);
            $temp_arr = explode(' ',$filter['keyword']);
            foreach ($temp_arr as $item){
                $where['title'][] = array("like", "%" . $item . "%");
            }
        }

        if(!empty($time_status)){
            $where['type'] = $time_status;
        }else{
            $where['type'] = array('NEQ',SHUFFLING);
        }
        $AdminModel = M('auth_admin');
        $result = D('Information')->getSearchResources($where, $pageIndex, 5);
        $Page = new \Think\Page($result['counts'],5);
        $Page->parameter['time_status'] = $time_status;
        $Page->parameter['keyword'] = $keyword;
        $show = $Page->show();
        $columnResources = D('Information')->getColumn();//获取所有栏目

        $browseRanking = D('Information')->browseRanking();//浏览排行榜
        $times = time() - (7 * 86400);
        $this->assign('times', $times);
        $this->assign('browseRanking', $browseRanking);
        $this->assign('columnResources', $columnResources);
        $this->assign('list', $result['resource']);
        $this->assign('counts',$result['counts']);
        $this->assign('page', $show);
        $this->assign('keyword', $keyword);
        $this->assign('time_status', $time_status);
        $this->assign('module', '励耘圈');
        $this->assign('nav', '专家资讯');
        $this->assign('subnav', '资讯信息');
        $this->assign('navicon', 'zhuanjiazixun');
        $this->display('expertInformationSearch');
    }

    /*
     *资讯详情页
     */
    public function expertInformationDetails()
    {

        if(!session('?teacher') && !session('?student') && !session('?parent')){
            redirect(U('Index/index'));
        }
        A('Home/Common')->authJudgement();
        A('Home/Common')->getUserIdRole($userId,$role);;
        switch ($role) {
            case ROLE_TEACHER:
                layout('teacher_layout_1');
                break;
            case ROLE_STUDENT:
                layout('student_layout_1');
                break;
            case ROLE_PARENT:
                layout('parent_layout_1');
                break;
            default:
                layout('teacher_layout_1');
                break;
        }
        
        $id = I('id');
		if((strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')|| strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone')||strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android'))) {
            redirect('/ApiInterface/Version1_1/ExpertInformation/informationDetails?id='.$id);
        }
		
        $this->assign('module', '励耘圈');
        $this->assign('nav', '专家资讯');
        $this->assign('subnav', '资讯详情');
        $this->assign('navicon', 'zhuanjiazixun');

        $Model = M('social_expert_information');
        $model = D('Information');
        $AdminModel = M('auth_admin');
        $result = $model->findInfor($id,'4');
        $where['social_expert_information.id'] = $id;
        $Model->where($where)->setInc('browse_count');//浏览量
        //热门推荐
        //查询当前详情属于哪个栏目下
        $where['dict_column.is_display'] = '1';
        $where['social_expert_information.status'] = '4';
        $where['social_expert_information.type'] = $result['type'];
        $where['social_expert_information.id'] = array('NEQ',$id);
        $recommended = $model->getSearchResources($where,1,5);
        $this->assign('data',$result);
        $this->assign('recommended',$recommended['resource']);
        $this->assign('module', '励耘圈');
        $this->assign('nav', '专家资讯');
        $this->assign('subnav', '资讯信息');
        $this->assign('navicon', 'zhuanjiazixun');
        $this->display();
    }


    /*
     *获奖作品详情
     */
    public function winningWorksDetails()
    {
        if(!session('?teacher') && !session('?student') && !session('?parent')){
            redirect(U('Index/index'));
        }
        $id = I('id');
        A('Home/Common')->getUserIdRole($userId,$role);;
        A('Home/Common')->authJudgement();
        switch ($role) {
            case ROLE_TEACHER:
                layout('teacher_layout_1');
                break;
            case ROLE_STUDENT:
                layout('student_layout_1');
                break;
            case ROLE_PARENT:
                layout('parent_layout_1');
                break;
            default:
                layout('teacher_layout_1');
                break;
        }
        $Model = M('social_expert_information');
        $where['social_expert_information.id'] = $id;
        $Model->where($where)->setInc('browse_count');//浏览量
        $resources = D('Information')->findInfor($id,'4');
        //热门推荐
        //查询当前详情属于哪个栏目下
        $where['dict_column.is_display'] = '1';
        $where['social_expert_information.status'] = '4';
        $where['social_expert_information.type'] = $resources['type'];
        $where['social_expert_information.id'] = array('NEQ',$id);
        $recommended = D('Information')->getSearchResources($where,1,5);
        $this->assign('data',$result);
        $this->assign('recommended',$recommended['resource']);
        $this->assign('resources',$resources);
        $this->assign('module', '励耘圈');
        $this->assign('nav', '专家资讯');
        $this->assign('subnav', '资讯详情');
        $this->assign('navicon', 'zhuanjiazixun');
        $this->display();
    }

    /*
     *更多
     */
    public function expertInformationMore(){

        if(!session('?teacher') && !session('?student') && !session('?parent')){
            redirect(U('Index/index'));
        }
        A('Home/Common')->getUserIdRole($userId,$role);;
        A('Home/Common')->authJudgement();
        switch ($role) {
            case ROLE_TEACHER:
                layout('teacher_layout_1');
                break;
            case ROLE_STUDENT:
                layout('student_layout_1');
                break;
            case ROLE_PARENT:
                layout('parent_layout_1');
                break;
            default:
                layout('teacher_layout_1');
                break;
        }

        $id = I('id');
        $pageIndex = getParameter('p', 'int', false);
        if(empty($pageIndex)){
            $pageIndex = 1;
        }

        $keyword = trim(getParameter('keyword','str',false));
        if ($keyword){
            $filter['keyword'] = preg_replace('/\s+/', ' ', $keyword);
            $filter['keyword'] = preg_replace('/\%+/', '\%',  $filter['keyword']);
            $temp_arr = explode(' ',$filter['keyword']);
            foreach ($temp_arr as $item){
                $where['title'][] = array("like", "%" . $item . "%");
            }
        }
        $where['status'] = 4;
        $where['dict_column.id'] = $id;
        if($id == WINWORK){
            $size = '4';
        }else{
            $size = '5';
        }
        //根据栏目id不同渲染不同的页面
        $result = D('Information')->getSearchResources($where,$pageIndex,$size);
        $Page = new \Think\Page($result['counts'],$size);
        $Page->parameter['id']   =   $id;
        $show = $Page->show();
        $this->assign('id',$id);
        $this->assign('list', $result['resource']);
        $this->assign('counts',$result['counts']);
        $this->assign('page', $show);
        $this->assign('keyword', $keyword);
        $this->assign('module', '励耘圈');
        $this->assign('nav', '专家资讯');
        $this->assign('subnav', '资讯信息');
        $this->assign('navicon', 'zhuanjiazixun');
        $this->display();
    }
}
