<?php
namespace ApiInterface\Controller\Version1_2;

class TeachStyleController extends PublicController
{
    public function __construct() {
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));

    }
    public function teachStyleList()
    {
        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');
        $where['auth_teacher.flag'] = 1;
        $where['auth_teacher.isfaketeacher'] = 0;
        $Model = D('Auth_teacher');

        //获取页面数据20条
        $result = $Model->getGoodTeacherList(1, 20);

        if ($role == 2) {
            //老师的总数
            $teacherCount = $Model->getTeacherCounts();

            //获得已登录教师的积分和详情
            $where['auth_teacher.id'] = $userId;
            $teacherInfo = $Model->getTeacherInfo($where);
            $this->assign('teacherInfo', $teacherInfo);

            //拼接Where条件查询总数
            $wheres['auth_teacher.flag'] = 1;
            $wheres['auth_teacher.isfaketeacher'] = 0;
            $wheres['auth_teacher.points'] = array('LT', $teacherInfo['points']);
            $loserCount = $Model->getGoodTeacherResource($wheres, $count);
            $percentage = round(($count / $teacherCount) * 100);
            $this->assign('percentage', $percentage);
        }
        $this->assign('userId', $userId);
        $this->assign('role', $role);
        //$this->assign('list', $result);
        $this->display();
    }

    /*
     *详情
     */
    public function teacherDetails()
    {
        $userId = getParameter('userId', 'int', false);
        $role = getParameter('role', 'int', false);
        //教师详情
        $id = getParameter('id', 'int', false);
        $Model = M('auth_teacher');
        $result = $Model
            ->join("dict_schoollist on auth_teacher.school_id=dict_schoollist.id")
            ->field('auth_teacher.id,auth_teacher.name,auth_teacher.avatar,auth_teacher.points,auth_teacher.brief_intro,auth_teacher.email,dict_schoollist.school_name,auth_teacher.school_age,auth_teacher.professional,auth_teacher.sex')
            ->where("auth_teacher.id=$id")
            ->find();
        $this->assign('data', $result);

        //教师任教详情
        $TeacherModelSecond = M('auth_teacher_second');
        $courseGradeIdNameSecond = $TeacherModelSecond
            ->where("auth_teacher_second.teacher_id=$id")
            ->join('dict_course on auth_teacher_second.course_id=dict_course.id')
            ->join('dict_grade on auth_teacher_second.grade_id=dict_grade.id')
            ->field('auth_teacher_second.course_id,auth_teacher_second.grade_id,dict_course.course_name,dict_grade.grade,auth_teacher_second.p_type')
            ->select();
        $this->assign('gradeCourseList', $courseGradeIdNameSecond);

        //分享的资源
        $Model = M('biz_resource');
        $check['biz_resource.status'] = 2;
        $check['biz_resource.teacher_id'] = $id;
        $results = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id')
            ->join('biz_resource_collect on biz_resource_collect.resource_id=biz_resource.id','left')
            ->field('biz_resource.*,biz_textbook.name as textbook,biz_resource_collect.resource_id as c_id')
            ->where($check)
            ->group('biz_resource.id')
            ->order("biz_resource.create_at desc")
            ->select();//echo M()->getLastSql();die;
        $this->assign('resources', $results);
        $this->assign('userId', $userId);
        $this->assign('role', $role);
        $this->display();

    }

    /*
     *收藏操作
     */
    public function collection()
    {
        //$teacherId = session('teacher.id');
        $userId = getParameter('userId','int');
        $role = getParameter('role','int');
        $id = getParameter('id', 'int');//资源ID

        $FavorModel = M('biz_resource_collect');
        $favorData['resource_id'] = $id;
        $favorData['user_id'] = $userId;
        if($role == 2){ //2老师3学生4家长
            $favorData['user_type'] = 0;
        }elseif($role == 3){
            $favorData['user_type'] = 1;
        }elseif ($role == 4){
            $favorData['user_type'] = 2;
        }


        $existed = $FavorModel->where($favorData)->find();
        if (empty($existed)) {
            $favorData['create_at'] = time();
            if($role == 2){
                $res = M('auth_teacher')->where("id=$userId")->find();
                $favorData['user_name'] = $res['name'];
            }elseif ($role == 3){
                $res = M('auth_student')->where("id=$userId")->find();
                $favorData['user_name'] = $res['student_name'];
            }elseif($role == 4){
                $res = M('auth_parent')->where("id=$userId")->find();
                $favorData['user_name'] = $res['parent_name'];
            }

            $FavorModel->add($favorData);

            $Model = M('biz_resource');
            $Model->where("id=$id")->setInc('favorite_count', 1);

            $resource = $Model->where("id=$id")->find();
            if($role == 2){
                $User = M("auth_teacher");
                $User->where("id=" . $resource['teacher_id'])->setInc("points", 5);// 积分加5
            }
            $this->ajaxReturn("success");
        } else {
            $FavorModel->where("resource_id=$id and user_type=0 and user_id=$userId")->delete();
            $Model = M('biz_resource');
            $Model->where("id=$id")->setDec('favorite_count', 1);

            $resource = $Model->where("id=$id")->find();
            if($role == 2) {
                $User = M("auth_teacher");
                $User->where("id=" . $resource['teacher_id'])->setDec("points", 5);//积分减5
            }
            $this->ajaxReturn("已经取消收藏");
        }
    }

}
