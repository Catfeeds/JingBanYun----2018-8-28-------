<?php
namespace Home\Controller;

use Common\Common\SMS;
use Think\Controller;
use Common\Common\CSV;
use Common\Common\DES3;
use Common\Common\REDIS;

class TeachblackboardController extends PublicController
{
    public $c_a = '';
    function __construct(){
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));
        $this->c_a = CONTROLLER_NAME."_".ACTION_NAME;
        set_time_limit(0);
        $this->teacherId_id_online = session('teacher.id');
    }


    //小黑板
    public function blackboard()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $id = $_GET['auth_id'];
        $isAuth = $this->isAuth($this->c_a);
        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }


        if (!$this->teacherId_id_online) {
            redirect(U('Teach/index1?auth_error=1'));
        }
        
        $is_board = session('teacher.is_board');

        if ( $is_board == 1) {
            $teacher_id = session('teacher.id');
            D('Biz_classList')->isFirstLoginBoard( $teacher_id );
            $this->redirect('blackboradFunctionGuidancecopy');
        }


        $this->assign('module', '班级行');
        $this->assign('nav', '小黑板');
        $this->assign('subnav', '消息列表');
        $this->assign('navicon', 'xiaoheiban');

        //$filter['grade'] = $_REQUEST['grade'];
        //$filter['class'] = $_REQUEST['class'];
        $filter['grade'] = getParameter('grade', 'int',false);
        $filter['class'] = getParameter('class', 'str',false);

        $filter['keyword'] = $_REQUEST['keyword'];
        if ($filter['keyword'] != NULL) {
            $this->assign('kw',1);
        }

        if (!empty($filter['keyword'])) $check['biz_blackboard.message_title'] = array('like', '%' . $filter['keyword'] . '%');
        $check['publisher_id']=session('teacher.id');
        $check['biz_class.is_delete'] = 0;
        $checkmap['biz_class.is_delete'] = 0;

       // $check['biz_class.is_delete']=0;

        $checkmap['publisher_id']=session('teacher.id');
        //$checkmap['biz_class.is_delete']=0;

        //获取发布消息的总数
        $countboard = D('Biz_blackboard')->getBoardCount( $checkmap );//小黑板总数
        $this->assign('countboard',count($countboard));
        if (!empty($checkmap['publisher_id'])) {
            $result = D('Biz_blackboard')->getBoardList( $check );//小黑板列表
        }


        $this->assign('list', $result['result']);
        $this->assign('page', $result['show']);

        $teacher_id = session('teacher.id');
        if(!empty($teacher_id)) {
            $grade_result = D('Biz_class')->getGradeListByTeacher( $teacher_id );
        }

        $this->assign('grade_list', $grade_result);

        //年级不为空,求出班级和学科
        if(!empty($check['dict_grade.id'])){
            $class_model = M('biz_class');
            $class_result=$class_model->where("grade_id=".$check['dict_grade.id']." and biz_class_teacher.teacher_id=".session('teacher.id'))->field('biz_class.id,biz_class.name')
                ->join('biz_class_teacher on biz_class.id=biz_class_teacher.class_id')
                ->group("biz_class.name")->select();
        }

        $this->assign('grade_list', $grade_result);
        $this->assign('class_list', $class_result);
        $this->assign('default_grade', $filter['grade']);
        $this->assign('default_class', $filter['class']);
        $this->assign('keyword', $filter['keyword']);


        $this->display_nocache();
    }

    public function blackboradFunctionGuidancecopy() {
        session('teacher.is_board',2);
        $this->display();
    }
    //删除消息
    public function deleteBlackboardMessage()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        //$id = $_GET['id'];
        $id = getParameter('id', 'int');

        $ResourceModel = M('biz_blackboard');
        $c1['id'] = $id;
        $ResourceModel->where($c1)->delete();
        $map['b_id'] = $id;
        M('boardandclass')->where($map)->delete();
        $this->ajaxReturn('success');
    }



    //发布小黑板消息
    public function publishBlackboardMessage()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        if ($_POST) {
            if (!session('?teacher')) {
                redirect(U('Index/index'), 0, '登录超时，请重新登录...');
            }
            /*
            $data['class_id'] = $_POST['class_id'];
            $data['message_title'] = remove_xss($_POST['message_title']);
            $data['message'] = $_POST['message'];
            */
            //$data['class_id'] = getParameter('class_id', 'int',false);
            $data['message_title'] = getParameter('message_title', 'str',false);
            $data['message'] = $_POST['message'];
            $class_id = $_POST['class_id'];
            $studentList = D('Biz_class_student')->getClassStudentAll($class_id);

            $data['publisher_id'] = session('teacher.id');
            $data['publisher'] = session('teacher.name');
            $data['school_id'] = session('teacher.school_id');
            $data['create_at'] = time();
            $ResourceModel = M('biz_blackboard');

            $ResourceModel->startTrans();

            $id = $ResourceModel->add($data);

            if ( $id ) {
                if (!empty($class_id)) {

                    foreach ( $class_id as $k=>$v) {
                        $dataclass = array(
                            'b_id' => $id,
                            'class_id' => $v,
                        );
                        $databoard = M('boardandclass')->where($dataclass)->find();
                        if (empty($databoard)) {
                            $bid = M('boardandclass')->add($dataclass);
                            if (!$bid) {
                                $ResourceModel->rollback();
                                $this->error("创建小黑板失败");
                            }
                        }
                    }
                }
            } else {
                $ResourceModel->rollback();
                $this->error("创建小黑板失败");
            }

            $ResourceModel->commit();

            if(!empty($class_id)) {
                //按班分批推送
                foreach($class_id as $key=>$classId)
                {
                    $class_model = D('Biz_class');
                    $class_result = $class_model->getClassNameTeacher($classId);
                    $parameters = array( 'msg' => array(session('teacher.name'),$class_result['class_name'],$data['message_title']) ,
                        'url' => array( 'type' => 1,'data' => array($id,$classId))
                    );
                    $studentIdArray = D('Biz_class')->getStudentIdAll($classId);
                    D('UserInfo')->sendMsg( ROLE_STUDENT,implode(',',$studentIdArray),$parameters,'BLACKBOARD_PUBLISHED',date('Y-m-d H:i:s',time()));
                    //家长推送
                    foreach($studentIdArray as $null => $studentId)
                    {
                        $studentInfo = D('Auth_student')->getStudentInfo($studentId);
                        $parentsInfo = D('Auth_student')->getStudentAllParent($studentId);
                        if(!empty($parentsInfo))
                        {
                            $parent_parameters=array( 'msg' => array($studentInfo['student_name'],session('teacher.name'),$class_result['class_name'],$data['message_title']) ,
                                'url' => array( 'type' => 1,'data' => array($id,$classId))
                            );
                            $ids = array_column($parentsInfo,'id');
                            D('UserInfo')->sendMsg( ROLE_PARENT,implode(',',$ids),$parent_parameters,'BLACKBOARD_PUBLISHED_CHILD',date('Y-m-d H:i:s',time()));
                        }
                    }
                }
            }

            $this->redirect("blackboard");
        } else {
            $this->assign('module', '班级行');
            $this->assign('nav', '小黑板');
            $this->assign('subnav', '发布消息');
            $this->assign('navicon', 'xiaoheiban');

            //获得该教师所教得年级
            /* $class_model = M('biz_class');
             $grade_result=$class_model
                         ->join('dict_grade on dict_grade.id=biz_class.grade_id')
                         ->where('biz_class.class_teacher_id='.session("teacher.id"))
                         ->field('dict_grade.id,dict_grade.grade')
                         ->group("dict_grade.id")
                         ->select();*/
            $teacher_id = session('teacher.id');
            //$grade_result = D('Biz_class')->getGradeListByTeacher( $teacher_id );

            $grade_result = D('Biz_class')->getGradeClassListTeacher( $teacher_id );
//            print_r($grade_result);die();
            $this->assign('grade_list', $grade_result);

            $this->display();
        }

    }

    //小黑板消息详情
    public function blackboardMessageDetails()
    {
        if (!session('?teacher')) redirect(U('Index/index'));

        $isAuth = $this->isAuth($this->c_a);

        if (!$isAuth) { //如果访问的模块没有权限
            redirect(U('Teach/index1?auth_error=1'));
        }

        $this->assign('module', '班级行');
        $this->assign('nav', '小黑板');
        $this->assign('navicon', 'xiaoheiban');


        //$id = $_GET['id'];
        $id = getParameter('id', 'int',false);
        $read_person_number = getParameter('read_person_number', 'int',false);

        $Model = M('biz_blackboard');
        $map['biz_blackboard.id'] = $id;
        $map['biz_class.is_delete'] = 0;
        $result = $Model
            ->join('boardandclass on boardandclass.b_id=biz_blackboard.id')
            ->join('biz_class_teacher on biz_class_teacher.class_id=boardandclass.class_id')
            ->join('biz_class on biz_class.id=biz_class_teacher.class_id')
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->field('group_concat(grade,biz_class.name) as cgname,biz_blackboard.*,biz_class.name as class_name')
            ->where( $map )
            ->find();
        
        if (empty($result['id'])) {
            $this->error('小黑板已经被删除');
        }

        $cgname = explode(',',$result['cgname']);
        $result['cgname'] = array_unique($cgname);

        $toumap['id'] = $result['publisher_id'];
        $tou = M('auth_teacher')->where( $toumap )->find();
        $result['touimg'] = $tou['avatar'];

        $this->assign('data', $result);


        $add_data['user_id']=session('teacher.id');
        $add_data['b_id']=$result['id'];
        $add_data['role_id']=2;
        $model=M('biz_isread_blackboard');
        //判断是否存在,不存在则入库

        if(!empty($result)){
            $browse_result=$model->where('role_id=2'.' and user_id='.session('teacher.id').' and b_id='.$result['id'])->field('id')->find();

            if(empty($browse_result)){
                $model->add($add_data);
            }
        }



        $this->assign('subnav', $result['class_name'] . " 小黑板");

        $Model->where("id=$id")->setInc('view_count', 1);

        $this->assign('read_person_number',$read_person_number);

        $this->display();
    }

    //判断是否有权限
    public function isAuth( $c_a ) {

        $teacher = session('auth_teacher');
        $parent = session('auth_parent');
        $student = session('auth_student');
        $admin = session('admin');
        if (!empty($teacher)) {

            $is_auth = in_array($c_a, session('auth_teacher'));

        } elseif(!empty($parent)) {
            $is_auth = in_array($c_a, session('auth_parent'));

        }elseif(!empty($student)){
            $is_auth = in_array($c_a, session('auth_student'));

        } elseif(!empty($admin)) {
            return true;
        }

        if ( $is_auth ) {
            return true;
        } else {
            return false;
        }
    }
}