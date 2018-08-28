<?php
namespace ApiInterface\Controller\Version1_2;

class StudentHomeworkController extends PublicController{
	public function __construct(){
        parent::__construct();
        $this->assign('oss_path', C('oss_path'));
    }
    
    // 学生端首页
	public function index(){

        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');

        $this->userId = $userId;
        $this->role = $role;

		$this->display_nocache();
	}

	public function exerciseDetails() {

        $userId = getParameter('userId', 'int',false);
        $role = getParameter('role', 'int',false);
        $homeworkId = getParameter('id', 'int',false);
        $classId = getParameter('classId', 'int',false);

        $flag = getParameter('flag', 'int',false);
        $submitId = getParameter('submitId', 'int',false);
        $isEnd = getParameter('isEnd', 'int',false);

        $this->userId = $userId;
        $this->role = $role;
        $this->homeworkId = $homeworkId;
        $this->classId = $classId;
        $this->flag = $flag;
        $this->submitId = $submitId;
        $this->isEnd = $isEnd;

        $this->display();
    }

    //作业册
	public function homeworkBook() {
        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');

        $this->userId = $userId;
        $this->role = $role;
        $this->display();
    }

    //做作业列表
    public function doHomework() {
        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');

        $this->userId = $userId;
        $this->role = $role;
        $this->display();
    }

    //单个作业的列表
    public function homeworkList() {
        $userId = getParameter('userId','int',false);
        $role = getParameter('role','int',false);
        $id = getParameter('id','int',false);
        $classId = getParameter('classId', 'int',false);
        $submitId = getParameter('submitId', 'int',false);
        $isEnd = getParameter('isEnd', 'int',false);
        $name = getParameter('name', 'str',false);
        $this->userId = $userId;
        $this->role = $role;
        $this->homeworkId = $id;
        $this->classId = $classId;
        $this->submitId = $submitId;
        $this->isEnd = $isEnd;
        $this->name = $name;
        $this->display_nocache();
    }

    //答题卡
    public function answerCard() {
        $this->display();
    }

    //作业结果
    public function homeworkResult() {

        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');
        $homeworkId = getParameter('id', 'int',false);
        $classId = getParameter('classId', 'int',false);
        $submitId = getParameter('submitId', 'int',false);
        $name = getParameter('name', 'str',false);
        
        $this->userId = $userId;
        $this->role = $role;
        $this->homeworkId = $homeworkId;
        $this->classId = $classId;
        $this->submitId = $submitId;
        $this->name = $name;
        $this->display();
    }

    //
    public function sendMsgParent(){
        $Id = getParameter('userId', 'int');
        $homeworkId = getParameter('homeworkId', 'int');
        $parentId = D('Biz_class_student')->getStudentParentId( $Id );
        $studentInfo = D('Auth_student')->getStudentInfo($Id);
        $homeWorkInfo = D('Exercises_homework_basics')->getHomeworkBaseInfo($homeworkId);

        $parentIds=[];
        if (!empty($parentId)) {
            foreach ($parentId as $k=>$id) {
                $parentIds[] =   $id['parent_id'];
            }
            $parentIds_info = implode($parentIds,",");
            if (!empty($parentIds_info)) {
                $parameters = array( 'msg' => array($studentInfo['student_name'],$homeWorkInfo['create_user_name'],$homeWorkInfo['name']) ,
                    'url' => array( 'type' => 0)
                );
                D('UserInfo')->sendMsg(ROLE_PARENT,$parentIds_info,json_encode($parameters),"BUZHI_HOMEWORK_PUBLISHED",date("Y-m-d H:i:s",time()));
            }
        }
    }
    

}

