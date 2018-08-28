<?php
namespace Home\Controller;

use Think\Controller;

class TeacherStyleController extends PublicController
{
    function teacherStyle()
    {

        A('Home/Common')->getUserIdRole($userId, $role);
        switch ($role) {
            case ROLE_TEACHER:
                layout('teacher_layout_1');
                break;
            case ROLE_STUDENT:
                layout('student_layout_1');
                $this->assign('tip', 'tip');//一个标记用于页面判断校榜的展示
                break;
            case ROLE_PARENT:
                layout('parent_layout_1');
                $this->assign('tip', 'tip');//一个标记用于页面判断校榜的展示
                break;
            default:
                layout('teacher_layout_1');
                break;
        }

        $this->assign('module', '励耘圈');
        $this->assign('nav', '教师风采');
        $this->assign('subnav', '教师风采榜');
        $this->assign('navicon', 'jiaoshifengcai');

        $where = ' auth_teacher.flag=1 and auth_teacher.isfaketeacher = 0 ';
        $Model = M('auth_teacher');
        $result = $Model
            ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id')
            ->join('dict_course on dict_course.id=auth_teacher.course_id')
            ->field('auth_teacher.*,dict_schoollist.school_name,dict_course.course_name')
            ->where($where)
            ->order('points desc,create_at desc')
            ->page(1, 20)->select();
        if(1 == DISPLAY_TEACHERSTYLE)
        {
            $this->assign('list', $result);
        }
        else
        {
            if(session('teacher.telephone') == '15311410001')
                $this->assign('list', $result);
            else
                $this->assign('list', array());
        }
        $this->display();
    }

    public function teacherDetails()
    {

        A('Home/Common')->getUserIdRole($userId, $role);
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
        }
        //$id = $_GET['id'];
        $id = getParameter('id', 'int', false);
        $this->assign('module', '教师风采');
        $this->assign('nav', '教师详情');
        $this->assign('navicon', 'jiaoshifengcai');

        $Model = M('auth_teacher');
        $result = $Model
            ->join("dict_schoollist on auth_teacher.school_id=dict_schoollist.id")
            ->field('auth_teacher.id,auth_teacher.name,auth_teacher.avatar,auth_teacher.points,auth_teacher.brief_intro,auth_teacher.email,dict_schoollist.school_name')
            ->where("auth_teacher.id=$id")
            ->find();

        $this->assign('subnav', $result['name']);

        $this->assign('data', $result);


        $Model = M('biz_resource');
        $check['biz_resource.status'] = 2;
        $check['biz_resource.teacher_id'] = $id;
        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id')
            ->field('biz_resource.*,biz_textbook.name as textbook')
            ->where($check)
            ->order("biz_resource.create_at desc")
            ->select();

        $this->assign('resources', $result);

        $this->display();

    }

    /*
     *ajax筛选榜单的请求
     */
    public function getRankingByAjax(){
       /* $allOrSchoolRanking = getParameter('allOrSchool','str',false);
        $weekOrMothe = getParameter('weekOrMothe','str',false);*/
       $temp_arr = I('parms');
       //var_dump($temp_arr);die;
        A('Home/Common')->getUserIdRole($userId, $role);
        $wheres['id'] = $userId;
        //查询所在学校操作
        $model = D('Auth_teacher');
        $fromSchool = $model->getTeacherInfo($wheres);

        //拼接where条件
        $where['auth_teacher.flag'] = 1;
        $where['auth_teacher.isfaketeacher'] = 0;
        if(!empty($temp_arr[0])){
            $field = 'auth_teacher.*,dict_schoollist.school_name,dict_course.course_name';
            if($temp_arr[0] == 'all'){
                $order = 'points desc,create_at desc';
            }else{
                $where['auth_teacher.school_id'] = $fromSchool['school_id'];
                $order = 'points desc,create_at desc';
            }
        }else{
            $field = 'auth_teacher.*,dict_schoollist.school_name,dict_course.course_name';
            $order = 'points desc,create_at desc';
        }
//var_dump($temp_arr);die;
        //日、周、月榜
        $week = strtotime(date('Y-m-d'))-604800;
        $mothe = strtotime(date('Y-m-d'))-2592000;
        if(!empty($temp_arr[1])){
            if($temp_arr[1] == 'week'){
                $field = 'auth_teacher.*,dict_schoollist.school_name,dict_course.course_name,(auth_teacher.points- auth_teacher_points_history.points) count';
                $join = 'auth_teacher_points_history on auth_teacher_points_history.teacher_id=auth_teacher.id';
                $where['auth_teacher_points_history.creat_time'] = $week;
                $order = "auth_teacher.points". "-". "auth_teacher_points_history.points desc";
            }elseif($temp_arr[1] == 'mothe'){
                $field = 'auth_teacher.*,dict_schoollist.school_name,dict_course.course_name,(auth_teacher.points- auth_teacher_points_history.points) count';
                $join = 'auth_teacher_points_history on auth_teacher_points_history.teacher_id=auth_teacher.id';
                $where['auth_teacher_points_history.creat_time'] = $mothe;
                $order = "auth_teacher.points". "-". "auth_teacher_points_history.points desc";
            }else{

            }
        }

        //查询操作
        $Model = M('auth_teacher');
        $result = $Model
            ->join('dict_schoollist on dict_schoollist.id=auth_teacher.school_id')
            ->join('dict_course on dict_course.id=auth_teacher.course_id')
            ->join($join)
            ->field($field)
            ->where($where)
            ->order($order)
            ->page(1, 20)->select();//echo M()->getLastSql();die;
        $this->showjson(200,$result);

    }

    /*
     *定时往历史积分表中插入数据
     */
    public function timing(){
        $creat_time = strtotime(date('Y-m-d'));
        $star_time = strtotime(date('Y-m-d')) - 2678400;
        $Model = M();
        $Model->startTrans();
        $sql = "INSERT INTO auth_teacher_points_history (teacher_id,points,creat_time)SELECT id,points,$creat_time FROM auth_teacher";
        $delet_sql = "DELETE FROM auth_teacher_points_history WHERE creat_time= $star_time";
        $tip = $Model->execute($sql);
        $tips = $Model->execute($delet_sql);
        
        if($tip == true){
            $Model->commit();
        }else{
            $Model->rollback();
        }
    }
}