<?php
namespace ApiInterface\Controller\Version1_1;
use Common\Common\SMS;
use Think\Controller;
define('authStudent',1);
define('unAuthStudent',2);

define('approveStudent',1);
define('rejectStudent',2);

define('transferredClass',1);
define('unReceivedClass',2);

class ClassManagementController extends PublicController
{
    public $model = '';
    public $pageSize = 20;
    public $firstRow = 0;
    public $listRow = 0;


    public function __construct()
    {
        parent::__construct();
        $this->model = D('Biz_class');
        $this->assign('oss_path', C('oss_path'));
    }

    /**
     * @描述：获取我的班级列表
     * @参数：userId[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @参数：pageIndex[int] N 页码
     * @参数：pageSize[int] N 条数
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function getMyClassList()
    {
        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');
        $pageIndex = getParameter('pageIndex', 'int', false);
        $this->pageSize = getParameter('pageSize', 'int', false);
        if(empty($this->pageSize))
            $this->pageSize = 20;
        $pageIndex = empty($pageIndex) ? 1 : $pageIndex;
        $data = $this->model->getClassList($role, $userId, $pageIndex, $this->pageSize);
        $this->showMessage(200, 'success', $data);
    }

    /**
     * @描述：获取班级学生列表
     * @参数：classId[int] Y 班级ID
     * @参数：studentType[int] Y 学生类型 1--已通过学生 2--未通过学生
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function getClassStudents()
    {
        $classId = getParameter('classId', 'int');
        $studentType = getParameter('studentType', 'int', false);
        $data = $this->model->getClassStudentList($classId, $studentType);
        $this->showMessage(200, 'success', $data);
    }

    /**
     * @描述：设置学生通过状态
     * @参数：classId[int] Y 班级ID
     * @参数：userId[int] Y 学生ID
     * @参数：status[int] Y 状态 1 -- 通过 2-- 拒绝
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */

    public function setClassStudentStatus()
    {
        $classId = getParameter('classId', 'int');
        $studentId = getParameter('userId', 'int');
        $status = getParameter('status', 'int');
        $result = $this->model->updateClassStudentStatus($classId, $studentId, $status+1, $errorInfo);

        $class_result_info=D('Biz_class')->getClassAndGradeInfo($classId);
        $student_info = D('Auth_student')->getStudentInfo( $studentId );

        $teacherId = D('Biz_class')->getCodeTeacherId($classId);

        if ($result)
        {
            if($status == 1) //通过
            {
                $this->model->deleteClassStudentRecord($classId,$studentId);

                $parameters = array(
                    'msg' => array(
                        $teacherId['name'],
                        $student_info['student_name'],
                        $class_result_info['grade'],
                        $class_result_info['name'],
                    ),
                    'url' => array( 'type' => 1,'data'=>array($studentId,$student_info['student_name']))
                );

                $controller_obj=new \Home\Controller\MessageController();
                $controller_obj->addPushUserMessage('GETADOPTSTU2', 4,$student_info['parent_id'] , $parameters);

                $parametersstu = array(
                    'msg' => array(
                        $teacherId['name'],
                        $class_result_info['grade'],
                        $class_result_info['name'],
                    ),
                    'url' => array( 'type' => 0,)
                );

                $controller_obj->addPushUserMessage('STUGETADOPTSTU', 3,$studentId, $parametersstu);//学生

            } else {

                $parameters = array(
                    'msg' => array(
                        $teacherId['name'],
                        $student_info['student_name'],
                        $class_result_info['grade'],
                        $class_result_info['name'],
                    ),
                    'url' => array( 'type' => 0,)
                );

                $controller_obj=new \Home\Controller\MessageController();
                $controller_obj->addPushUserMessage('GETADOPTSTU3', 4,$student_info['parent_id'] , $parameters);


                //给学生发
                $parametersstu = array(
                    'msg' => array(
                        $teacherId['name'],
                        $class_result_info['grade'],
                        $class_result_info['name'],
                    ),
                    'url' => array( 'type' => 0,)
                );

                $controller_obj->addPushUserMessage('STUGETADOPTSTUDISABLE', 3,$studentId, $parametersstu);//学生
            }
            $this->showMessage(200, 'success', array());
        }
        else
            $this->showMessage(500, $errorInfo, array());
    }

    /**
     * @描述：获取班级教师列表
     * @参数：classId[int] Y 班级ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function getClassTeachers()
    {
        $classId = getParameter('classId', 'int');
        $where['biz_class_teacher.class_id'] = $classId;
        $where['biz_class.is_delete'] = 0;
        $data = $this->model->getClassTeacher($where);
        $this->showMessage(200, 'success', $data);
    }

    /**
     * @描述：获取我的班级信息
     * @参数：classId[int] Y 班级ID
     * @参数：userId[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function getClassInfo()
    {
        $classId = getParameter('classId', 'int');
        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');
        $data = $this->model->getBriefClassData($classId, $role, $userId);
        $this->showMessage(200, 'success', $data);
    }

    /**
     * @描述：移交班级
     * @参数：classId[int] Y 班级ID
     * @参数：userId[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @参数：course[int] Y 学科
     * @参数：telephone[str] Y 移交目标教师手机号
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */

    public function transferClass()
    {
        $classId = getParameter('classId', 'int');
        $course = getParameter('course', 'int');
        $role = getParameter('role', 'int');
        $userId = getParameter('userId', 'int');
        $telephone = getParameter('telephone', 'str');
        if ($this->model->transferClass($classId, $userId, $course, $telephone, $errorInfo)){
            $parameters = array(
                'msg' => array(
                ),
                'url' => array( 'type' => 0 )
            );
            $newTeacherInfo = D('Auth_teacher')->getTeacherByTel($telephone);
            $controller_obj=new \Home\Controller\MessageController();
            unset($_SERVER['HTTP_DEVICE']);
            $controller_obj->addPushUserMessage('YIJIAO_CLASS', 2, $newTeacherInfo['id'], $parameters);

            $this->showMessage(200, 'success', array());

        } else {
            $this->showMessage(500, $errorInfo, array());
        }

    }

    /**
     * @描述：修改未认证班级名称
     * @参数：classId[int] Y 班级ID
     * @参数：className[str] Y 班级名称
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     *
     */

    public function modifyClassName()
    {
        $classId = getParameter('classId', 'int');
        $name = getParameter('className', 'str');
        $classInfo = $this->model->getClassInfo($classId);
        {
            if(SCHOOL_CLASS == $classInfo['class_status'])
                $this->showMessage(500, '无法更改校建班名称', array());
            $grade =  $classInfo['grade_id'];
            $classInfo = $this->model->getClassInfo($classId);
            if($this->model->getClassIsExistsByNameGradeTeacher($name,$grade,$classInfo['teacher_id']))
                $this->showMessage(500, '该班级名称已经存在,无法修改班级名称', array());
        }
        if ($this->model->setClassName($classId, $name,$errorInfo)) {
            $this->showMessage(200, 'success', array());
        } else {
            $this->showMessage(500, $errorInfo, array());
        }
    }

    /**
     * @描述：修改我的班级学科
     * @参数：classId[int] Y 班级ID
     * @参数：userId[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @参数：courses[int array] Y 学科列表
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     *
     */
    /*
    public function modifyMyCourseInClass()
    {
        $classId = getParameter('telephone', 'int');
        $role = getParameter('role', 'int');
        $userId = getParameter('userId', 'int');
        $courses = getParameter('course', 'iArr');
        $this->showMessage(200, 'success', array());
    }
    */

    /**
     * @描述：创建班级
     * @参数：grade[int] N 年级ID
     * @参数：className[str] N 班级名称
     * @参数：userId[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function createClass()
    {
        $grade = getParameter('grade', 'int');
        $name = getParameter('className', 'str');
        $role = getParameter('role', 'int');
        $userId = getParameter('userId', 'int');
        if ($role == ROLE_TEACHER) {
            $teacherInfo = D('Auth_teacher')->getTeachInfo($userId);
            if ($this->model->createClass($name, $grade, $teacherInfo, $errorInfo))
                $this->showMessage(200, 'success', array());
            else
                $this->showMessage(500, $errorInfo, array());
        }
    }

    /**
     * @描述：删除班级
     * @参数：classId[int] Y 班级ID
     * @参数：userId[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function deleteClass()
    {
        $classId = getParameter('classId', 'int');
        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');
        if(ROLE_TEACHER != $role)
        {
            $this->showMessage(500, '您没有删除班级的权限', array());
        }
        $classInfo = $this->model->getTeachClassData($userId,$classId);
        if(empty($classInfo))
        {
            $this->showMessage(500, '您没有删除该班级的权限', array());
        }
        if ($this->model->deleteClass($classId, $errorInfo))
            $this->showMessage(200, 'success', array());
        else
            $this->showMessage(500, $errorInfo, array());
    }

    /**
     * @描述：离开班级
     * @参数：classId[int] Y 班级ID
     * @参数：userId[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function leaveClass()
    {
        $classId = getParameter('classId', 'int');
        $role = getParameter('role', 'int');
        $userId = getParameter('userId', 'int');
        $this->model->startTrans();

        if(ROLE_TEACHER == $role) {
            $classInfo = $this->model->getTeacherClassInfo($classId, $userId);
            for ($i = 0; $i < sizeof($classInfo); $i++) {
                $classInfo[$i]['create_at'] = time();
                if (!$this->model->addClassTeacherRecord($classInfo[$i])) {
                    $this->model->rollback();
                    $this->showMessage(500, '离开班级失败', array());
                }
            }
        }
        else if(ROLE_STUDENT == $role)
        {
            $this->model->startTrans();
            $class_con['biz_class_student.student_id']=$userId;
            $class_con['biz_class.id']=$classId;
            $class_result=$this->model->getClassStudentDataAll($class_con);
            if(!empty($class_result)){
                $student_data['student_id']=$userId;
                $student_data['class_id']=$classId;
                $student_data['status']=$class_result[0]['class_student_status'];
                $student_data['create_at']=time();
                $student_data['joinmode']=$class_result[0]['joinmode'];
                if(!$this->model->addClassStudentRecord($student_data)){
                    $this->model->rollback();
                    $this->showMessage(500, '离开班级失败', array());
                }
            }
        }
        if ($this->model->leaveClass($classId, $role, $userId, $errorInfo)){
            $this->model->commit();
            $this->showMessage(200, 'success', array());
        }
        else
        {
            $this->model->rollback();
            $this->showMessage(500, $errorInfo, array());
        }

    }

    /**
     * @描述：教师或学生加入班级
     * @参数：userId[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @参数：classCode[int] N 班级编码
     * @参数：course[int] N 学科编号
     * @参数：classId[int] N 班级ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function joinClass()
    {
        $role = getParameter('role', 'int');
        $userId = getParameter('userId', 'int');

        switch ($role) {
            case ROLE_TEACHER:
                $course = getParameter('course', 'str');
                $classCode = getParameter('classCode', 'int', false);
                $classId = getParameter('classId', 'int', false);
                if(empty($classId))
                $classId = $this->model->getClassIdByClassCode($classCode);
                if (empty($classId)) {
                    if (!empty($classCode))
                        $this->showMessage(500, '抱歉,不存在该班级,请向学校管理员或已经加入该班级的老师询问正确的班级编号', array());
                    else
                        $this->showMessage(500, '班级不存在', array());
                }
                //自建班
                $classCategory = $this->model->getClassCategory($classId);
                if (empty($classCategory))
                    $this->showMessage(500, '班级不存在', array());
                if (PERSONAL_CLASS == $classCategory)
                    $this->showMessage(500, '您输入的是教师自建班级编号,教师自建班级不支持其他教师加入', array());

                else if (SCHOOL_CLASS == $classCategory) {
                    $teacherInfo = D('Auth_teacher')->getTeacherSimpleData($userId);
                    $classInfo = $this->model->getClassInfo($classId);
                    if($teacherInfo['school_id'] != $classInfo['school_id'])
                        $this->showMessage(500, '无法加入其它学校的校建班级', array());
                    if($teacherInfo['apply_school_status'] != 1)
                        $this->showMessage(500, '该教师未通过学校的审核', array());
                    //停用检查
                    if($this->model->getIsClassStop($classId,SCHOOL_CLASS))
                        $this->showMessage(500, '抱歉!该班级已被停用,请选择其他班级申请加入', array());
                }
                if ($this->model->joinClass($classId, ROLE_TEACHER, $userId, $course, $errorInfo))
                {
                    $this->model->deleteClassTeacherRecord($classId,$userId,$course);
                    $this->showMessage(200, 'success', array());
                }
                else
                    $this->showMessage(500, $errorInfo, array());

                break;
            case ROLE_STUDENT:
                $classCode = getParameter('classCode', 'int', false);
                $classId = getParameter('classId', 'int', false);

                if(empty($classId))
                $classId = $this->model->getClassIdByClassCode($classCode);

                if (empty($classId))
                    $this->showMessage(500, '班级不存在', array());

                $classCategory = $this->model->getClassCategory($classId);

                if (empty($classCategory))
                    $this->showMessage(500, '班级不存在', array());

                $studentInfo = D('Auth_student')->getStudentInfo($userId);

                if (SCHOOL_CLASS == $classCategory) {

                    if($studentInfo['apply_school_status'] != 1)
                        $this->showMessage(500, '该学生未通过学校的审核', array());
                    $classInfo = $this->model->getClassInfo($classId);
                    if($studentInfo['school_id'] != $classInfo['school_id'])
                        $this->showMessage(500, '无法加入其它学校的校建班级', array());
                    //停用检查
                    if($this->model->getIsClassStop($classId,SCHOOL_CLASS))
                        $this->showMessage(500, '该班级/班级所属学校已被停用,无法加入', array());
                    if ($this->model->studentHasJoinedSchoolClasses($userId))
                        $this->showMessage(500, '无法再次加入校建班级', array());

                } else {
                    $classInfo = $this->model->getClassInfo($classId);
                }

                if ($classInfo['class_status'] == 2) {
                    $class_info_send = $this->model->getClassAndGradeInfo( $classId );
                    $teacherId = $this->model->getTeacherId( $classId );
                    //消息推送
                    $parameters = array(
                        'msg' => array(
                            $studentInfo['student_name'],
                            $class_info_send['grade'],
                            $class_info_send['name'],
                        ),
                        'url' => array( 'type' => 1,'data'=>array($classId) )
                    );

                    $controller_obj=new \Home\Controller\MessageController();

                    $controller_obj->addPushUserMessage('STUDENT_ADD_PERSON_CLASS', 2, $teacherId['teacher_id'] , $parameters);
                }


                if ($this->model->joinClass($classId, ROLE_STUDENT, $userId, 0, $errorInfo,1))
                {
                    if (SCHOOL_CLASS == $classCategory)
                        $this->model->deleteClassStudentRecord($classId,$userId);
                    if(SCHOOL_CLASS != $classCategory)
                     $this->showMessage(200, '申请加入教师班级成功', array());
                    else
                     $this->showMessage(200, '加入成功', array());
                }

                else
                    $this->showMessage(500, $errorInfo, array());
                break;
        }

        $this->showMessage(500, '参数错误', array());
    }

    /**
     * @描述：获取教师自建班级列表
     * @参数：telephone[str] Y 教师手机号
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function getClassListByTeacherTelephone()
    {
        $telephone = getParameter('telephone', 'str');
        $teacherInfo = D('Auth_teacher')->getTeacherByTel($telephone);
        $data = $this->model->getPersonalClassList($teacherInfo['id']);
        $this->showMessage(200, 'success', $data);
    }

    /**
     * @描述：获取教师接收班级列表
     * @参数：userId[int] Y 用户ID
     * @参数：receiveClassType[int] Y 班级类型 1-- 我移交出的班级 2-- 我待接收的班级
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function receiveClassList()
    {
        $userId = getParameter('userId', 'int');
        $classType = getParameter('receiveClassType', 'int');
        $data = $this->model->getReceiveClassList($userId, $classType);
        $this->showMessage(200, 'success', $data);
    }


    /**
     * @描述：撤销移交班级
     * @参数：userId[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @参数：classId[int] Y 班级ID
     * @参数：courseId[int] Y 学科ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function undoTransferClass()
    {
        $role = getParameter('role', 'int');
        $userId = getParameter('userId', 'int');
        $classId = getParameter('classId', 'int');
        $courseId = getParameter('courseId', 'int');
        $class_info = D('Biz_class')->getClassInfo($classId);
        $teacherId = D('Biz_class')->getCodeTeacherId($classId);
        $class_info_send =D('Biz_class')->getClassHandoff($userId,$classId);

        if($class_info['class_status'] == 2) {//发送推送提醒
            $parameters = array(
                'msg' => array(
                    $teacherId['name'],
                    $class_info['grade'],
                    $class_info['name'],
                ),
                'url' => array( 'type' => 0 )
            );
            if (!empty($class_info_send)) {
                $controller_obj=new \Home\Controller\MessageController();
                $controller_obj->addPushUserMessage('JIAOSHI_CHECIAO_CLASS', 2, $class_info_send['dest_teacherid'], $parameters);
            }

        }

        if ($this->model->undoJoinClass($classId, $userId, $courseId)) {

            $this->showMessage(200, 'success', array());
        } else {
            $this->showMessage(500, '撤消失败', array());
        }

    }

    /**
     * @描述：接收班级
     * @参数：userId[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @参数：classId[int] Y 班级ID
     * @参数：courseId[int] Y 学科ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function receiveClass()
    {
        $role = getParameter('role', 'int');
        $userId = getParameter('userId', 'int');
        $classId = getParameter('classId', 'int');
        $courseId = getParameter('courseId', 'int');

        $class_model=D('Biz_class');
        $class_result=$class_model->getClassAndGradeInfo($classId);

        $teacher_model = D('Auth_teacher');
        $teacherinfo = $teacher_model->getTeachInfo( $userId );

        $teacherId = D('Biz_class')->getCodeTeacherId($classId);

        $parent_id_all = D('Biz_class')->getParentIdAll($classId);
        $course_info = D('Dict_course')->getCourseInfo($courseId);

        $student_id_all = D('Biz_class')->getStudentIdAll($classId);
        $destTeacherId = $this->model->receiveClass($classId, $userId, $courseId, CLASSSTATE_RECEIVED);
        if ($destTeacherId > 0){
            //转移作业归属
            D('Exercises_homework_basics')->updateHomeworkAuth($userId,$destTeacherId,$classId,$courseId);
            $parameters = array(
                'msg' => array(
                    $teacherinfo['name'],
                    $class_result['grade'],
                    $class_result['name'],
                ),
                'url' => array( 'type' => 0 )
            );

            $controller_obj=new \Home\Controller\MessageController();

            $controller_obj->addPushUserMessage('ACCEPT_CLASS', 2, $userId , $parameters);


            //对家长发送推送信息
            if ($class_result['class_status'] == 1) { //校建班

                //对家长的发送
                $parameters = array(
                    'msg' => array(
                        $class_result['grade'],
                        $class_result['name'],
                        $course_info['course_name'],
                        $teacherId['name'],
                    ),
                    'url' => array( 'type' => 0,)
                );

                $controller_obj=new \Home\Controller\MessageController();

                $controller_obj->addPushUserMessage('ZIJIAN_XIAOJIAN', 4,implode(',', $parent_id_all)  , $parameters);

                //对学生的发送
                $controller_obj->addPushUserMessage('STU_ZIJIAN_XIAOJIAN', 3,implode(',', $student_id_all)  , $parameters);

            } else {

                //对家长的发送
                $parameters = array(
                    'msg' => array(
                        $class_result['grade'],
                        $class_result['name'],
                        $teacherinfo['name'],
                    ),
                    'url' => array( 'type' => 0,)
                );

                $controller_obj=new \Home\Controller\MessageController();
                $controller_obj->addPushUserMessage('ZIJIAN_YIJIAO', 4,implode(',', $parent_id_all)  , $parameters);

                //对学生的发送
                $controller_obj->addPushUserMessage('STU_ZIJIAN_YIJIAO', 3,implode(',', $student_id_all)  , $parameters);
            }

            $this->showMessage(200, 'success', array());
        } else {
            $this->showMessage(500, '接收失败', array());
        }

    }

    /**
     * @描述：拒绝接收班级
     * @参数：userId[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @参数：classId[int] Y 班级ID
     * @参数：courseId[int] Y 学科ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function rejectReceiveClass()
    {
        $role = getParameter('role', 'int');
        $userId = getParameter('userId', 'int');
        $classId = getParameter('classId', 'int');
        $courseId = getParameter('courseId', 'int');
        if ($this->model->receiveClass($classId, $userId, $courseId, CLASSSTATE_REJECTED))
            $this->showMessage(200, 'success', array());
        else
            $this->showMessage(500, '撤消失败', array());
    }

    /**
     * @描述：学生加入班级列表(与该学生相关的班级列表,包括已加入,已拒绝,待审核的班级)
     * @参数：userId[int] Y 学生ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     *
     */
    public function joinClassList()
    {
        $userId = getParameter('userId', 'int');
        $data = $this->model->getJoinClassList($userId);
        $this->showMessage(200, 'success', $data);
    }

    /**
     * @描述：学生发送加入班级提醒
     * @参数：userId[int] Y 学生ID
     * @参数：classId[int] Y 班级ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function notifyJoinClass()
    {
        $userId = getParameter('userId', 'int');
        $classId = getParameter('classId', 'int');
        $studentInfo = D('Auth_student')->getStudentInfo($userId);
        $classCategory = D('Biz_class')->getClassCategory($classId);

        $class_info = D('Biz_class')->getClassInfo($classId);

        if (PERSONAL_CLASS == $classCategory) {
            $classTeacherInfo = $this->model->getClassNameTeacher($classId);

            if($class_info['class_status'] == 2) {//发送推送提醒
                $parameter_arr = array(
                    'msg' => array($studentInfo['student_name'], $classTeacherInfo['class_name']),
                    'url' => array(
                        'type' => 1,
                        'data' => array()
                    )
                );
                $controller_obj = new \Home\Controller\MessageController();
                $controller_obj->addPushUserMessage('CLASSNOTIFY_JOIN', ROLE_TEACHER, $classTeacherInfo['teacher_id'], $parameter_arr);
            }

            $this->showMessage(200, 'success', array());
        } else
            $this->showMessage(500, '班级不存在或不是教师自建班级', array());

    }

    /**
     * @描述：获取教师可创建年级
     * @参数：userId[int] Y 教师ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */

    public function getAvailableCreateGrade()
    {
        $teacherId = getParameter('userId', 'int');
        $this->showMessage(200, 'success', D('Dict_grade')->getTeacherGrade($teacherId));
    }

    /**
     * @描述：学生不加入班级
     * @参数：userId[int] Y 学生ID
     * @参数：classId[int] Y 班级ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function undoJoinClass()
    {
        $userId = getParameter('userId', 'int');
        $classId = getParameter('classId', 'int');

        $class_result_info=D('Biz_class')->getClassAndGradeInfo($classId);
        $teacherall_id = D('Biz_class')->getTeacherIdAll($classId);
        $userinfo = D('Auth_student')->getStudentInfo($userId);

        $class_info = D('Biz_class')->getClassInfo($classId);


        if ($this->model->undoStudentJoinClass($userId, $classId, $errorInfo)) {

            //消息推送
            if( $class_info['class_status'] == 2) { //学生加入自建班级进行推送
                $parameters = array(
                    'msg' => array(
                        $userinfo['student_name'],
                        $class_result_info['grade'],
                        $class_result_info['name'],
                    ),
                    'url' => array('type' => 0,)
                );
                if (!empty($teacherall_id)) {
                    $controller_obj = new \Home\Controller\MessageController();
                    $controller_obj->addPushUserMessage('STUDENT_CEXIAO_PERSON_CLASS', 2, implode(',', $teacherall_id), $parameters);
                }
            }

            $this->showMessage(200, 'success', array());
        } else {
            $this->showMessage(500, $errorInfo, array());
        }
    }

    /**
     * @描述：添加学生
     * @参数：name[int] Y 学生姓名
     * @参数：telephone[str] Y 家长手机号
     * @参数：classId[int] N 班级ID
     * @参数：parentId[int] N 家长本人ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function addStudent($name = '', $telephone = '', $classId = 0)
    {
        $isNewStudent = 0;
        if(!empty($name))
            $isNewStudent = 1;
        if ('' == $name)
            $name = getParameter('name', 'str');
        if ('' == $telephone)
            $telephone = getParameter('telephone', 'str');
        if (0 == $classId)
            $classId = getParameter('classId', 'int', false);


        $studentInfo = D('Auth_student')->getStudentInfoByTelAndName($telephone, $name);
        $class_result_info_add=D('Biz_class')->getClassAndGradeInfo($classId);

        $teacherId = D('Biz_class')->getCodeTeacherId($classId);


        if (empty($studentInfo))
            $this->showMessage(501, 'unregistered', array());
        else {
            if (!empty($classId)) //teacher add student
            {
                $isClassFlag = D('Biz_class')->isClassInfoFlag( $classId );

                if ( $isClassFlag == false ){
                    $this->showMessage(500, '该班级已经被停用不能关联学生', array());
                }
                $studentInfoDetail = D('Auth_student')->getStudentInfo($studentInfo['id']);

                $classCategory = $this->model->getClassCategory($classId);
                if (SCHOOL_CLASS == $classCategory) {
                    $classInfo = $this->model->getClassInfo($classId);
                    if($studentInfoDetail['school_id'] != $classInfo['school_id'])
                        $this->showMessage(500, '无法加入其它学校的学生进入该校建班级', array());

                    if ($studentInfoDetail['apply_school_status'] != 1) {
                        $this->showMessage(500, '该学生未通过学校的审核', array());
                    }

                    if ($this->model->studentHasJoinedSchoolClasses($studentInfo['id']))
                        $this->showMessage(500, '该学生已经加入了其它校建班，无法再次加入校建班级', array());
                }
                if ($this->model->joinClass($classId, ROLE_STUDENT, $studentInfo['id'], 0, $errorInfo,0)) {
                    if ($this->model->updateClassStudentStatus($classId, $studentInfo['id'], STUDENTJOINSTATE_AUTH , $stateErrorInfo)) {

                        $parameters = array(
                            'msg' => array(
                                $teacherId['name'],
                                $class_result_info_add['grade'],
                                $class_result_info_add['name'],
                            ),
                            'url' => array( 'type' => 0)
                        );
                        $controller_obj=new \Home\Controller\MessageController();
                        $controller_obj->addPushUserMessage('CLASSADDSENDSTUDENT', 3,$studentInfoDetail['id'] , $parameters);//学生
                        $parameters['url'] = array( 'type' => 1,'data'=>array($studentInfo['id'],$studentInfo['student_name']));
                        $controller_obj->addPushUserMessage('ADDSTU', 4,$studentInfoDetail['parent_id'] , $parameters); //家长
                        //如果该学生是刚注册的学生则发短信通知
                        if($isNewStudent) {
                            $smsApi = new SMS();
                            $smsApi->templateSMSAddStudent($telephone, $name, $teacherId['name'], $class_result_info_add['grade'] . $class_result_info_add['name']);
                        }
                        $this->showMessage(200, 'success', array());
                    } else{
                        $this->showMessage(500, $stateErrorInfo, array());
                    }

                } else
                    $this->showMessage(500, $errorInfo, array());
            } else //parent add student
            {
                $parentId = getParameter('parentId', 'int');
                $parentInfo = D('Auth_parent')->getParentInfo($parentId);
                if (empty($parentInfo)) {
                    $this->showMessage(500, '家长不存在', array());
                }
                $relationInfo = D('Auth_parent')->getParentStudentRelation($parentId, $studentInfo['id']);
                if (!empty($relationInfo))
                    $this->showMessage(500, '该家长和学生已经绑定', array());
                if (D('Auth_student')->updateStudentParentInfo($parentId, $studentInfo['id'])) {
                    $this->showMessage(200, 'success', array());
                } else
                    $this->showMessage(500, '绑定家长学生失败', array());
            }

        }
    }

    /**
     * @描述：班级管理中简易注册学生（加入班级）
     * @参数：name[int] Y 学生姓名
     * @参数：telephone[str] Y 家长手机号
     * @参数：classId[int] N 班级ID
     * @参数：password[str] Y 密码
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function regStudent()
    {
        $name = getParameter('name', 'str');
        $telephone = getParameter('telephone', 'str');
        $classId = getParameter('classId', 'int', false);
        $password = getParameter('password', 'str');
        $data['parent_tel'] = $telephone;
        $data['student_name'] = $name;
        $data['password'] = sha1($password);
        $data['create_at'] = time();
        $classInfo = $this->model->getClassInfo($classId);
        $data['school_id'] = $classInfo['school_id'];
        $data['apply_school_status'] = 1;
        $studentInfo = D('Auth_student')->getStudentInfoByTelAndName($telephone, $name);

        if (!empty($data['parent_tel']))
        $parent_info = D('Auth_parent')->getParentInfoByTelephone($data['parent_tel']);

        if (!empty($studentInfo)) {
            $this->showMessage(200, '该学生已经注册', array());
        }

        $sid = D('Auth_student')->addStudentData($data);
        $teacherId = D('Biz_class')->getCodeTeacherId($classId);

        //添加赠送vip
        $vipstatus = C('VIP_CONFIG.APP_REGISTER_GIVE_VIP_STATUS');
        if ( $vipstatus == 1) {

            $vipdata = array(
                'user_id' => $sid,
                'role_id' => 3,
                'auth_id' => 4,
                'auth_start_time' => time(),
                'auth_end_time' => time()+3600*24*30*3,
                'timetype' => 1,
            );
            $auth_type_use = D('Account_auths');

            $auth_type_use->addUserVip($vipdata);

        }

        $class_result_info_add=D('Biz_class')->getClassAndGradeInfo($classId);

        $parameters = array(
            'msg' => array(
                $teacherId['name'],
                $data['student_name'],
                $class_result_info_add['grade'],
                $class_result_info_add['name'],
            ),
            'url' => array( 'type' => 0,)
        );

        $controller_obj=new \Home\Controller\MessageController();
        $controller_obj->addPushUserMessage('REGISTERADDSTU', 4,$parent_info['id'] , $parameters); //家长

        $parametersstu = array(
            'msg' => array(
                $teacherId['name'],
                $class_result_info_add['grade'],
                $class_result_info_add['name'],
            ),
            'url' => array( 'type' => 0,)
        );

        $controller_obj->addPushUserMessage('WEICLASSADDSENDSTUDENT', 3,$sid, $parametersstu);//学生

        if (false !== $sid ) {
            $this->addStudent($name, $telephone, $classId);
        }
        $this->showMessage(500, '该学生已经注册', array());
    }

    /**
     * @描述：班级课表页
     * @参数：classId[int] Y 班级ID
     * @返回值：HTML网页
     */
    public function classTimeTable()
    {
        $classId = getParameter('classId', 'int');
        $this->assign('classId',$classId);
        $this->display();
    }

    /**
     * @描述：教师课表页
     * @参数：userId[int] Y 教师ID
     * @参数：classId[int] N 班ID
     * @返回值：HTML网页
     */
    public function teacherTimeTable()
    {
        $userId = getParameter('userId', 'int');
        $classId = getParameter('classId', 'int',false);
        $this->assign('classId',$classId);
        $this->assign('teacherId',$userId);
        $this->display();
    }

    /**
     * @描述：获取我可加入的校建班级
     * @参数：role[int] Y 角色ID
     * @参数：userId[int] Y 用户ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function getAvailableSchoolClasses()
    {
        $role = getParameter('role', 'int');
        $userId = getParameter('userId', 'int');

        switch ($role) {
            case ROLE_TEACHER:
                $userInfo = D('Auth_teacher')->getTeachInfo($userId);
                break;
            case ROLE_STUDENT:
                $userInfo = D('Auth_student')->getStudentInfo($userId);
                break;
            default:
                break;
        }
        if (!empty($userInfo)) {
            $data = $this->model->getAvailableClasses($userInfo['school_id']);
            $this->showMessage(200, 'success', $data);
        }
        $this->showMessage(500, '用户信息不存在', array());

    }

    /**
     * @描述：获取教师自建班级列表
     * @参数：telephone[str] Y 教师手机号
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function getAvailableTeacherClasses()
    {
        $telephone = getParameter('telephone', 'str');
        $userInfo = D('Auth_teacher')->getTeacherByTel($telephone);
        if (empty($userInfo)) {
            $this->showMessage(500, '用户信息不存在', array());
        }
        $data = $this->model->getAvailableTeacherClasses($userInfo['id']);
        $this->showMessage(200, 'success', $data);

    }

    /**
     * @描述：获取家长学生列表
     * @参数：userId[str] Y 家长ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function getParentStudents()
    {
        $userId = getParameter('userId', 'int');
        $data = D('Auth_parent')->getParentStudentData($userId);
        $this->showMessage(200, 'success', $data);

    }

    public function getClassTimeTableInfo()
    {
        $classId = getParameter('classId', 'int');
        if($data = $this->model->getClassTimeTableInfo($classId,$errorInfo))
          $this->showMessage(200, 'success', $data);
        else
          $this->showMessage(500, $errorInfo, array());
    }
    public function getTeacherTimeTableInfo()
    {
        $teacherId = getParameter('teacherId', 'int');
        $classId = getParameter('classId', 'int',false);
        if($data = $this->model->getTeacherTimeTableInfo($classId,$teacherId,$errorInfo)) {
            if(isset($data['content'])) {
                $html = new \Common\Common\simple_html_dom();
                $html->load('<html><body>' . $data['content'] . '</body></html>');
                $mediaRes = $html->find('div');
                $teacherCourses = D('Auth_teacher')->getTeacherAllCourse($teacherId);
                $courseList = D('Dict_course')->getCourseList();
                for ($i = 0; $i < sizeof($teacherCourses); $i++) {
                    for ($j = 0; $j < sizeof($courseList); $j++) {
                        if ($teacherCourses[$i]['course_id'] == $courseList[$j]['id']) {
                            unset($courseList[$j]['id']);
                            unset($courseList[$j]['name']);
                            break;
                        }
                    }

                }
                for ($i = 0; $i < sizeof($mediaRes); $i++) {
                    for ($j = 0; $j < sizeof($courseList); $j++) {
                        $courseNameLength = strlen($courseList[$j]['name']);
                        //var_dump(substr($mediaRes[$i]->innertext,$courseNameLength * (-1),$courseNameLength));
                        if ($courseList[$j]['name'] === substr($mediaRes[$i]->innertext, $courseNameLength * (-1), $courseNameLength)) {
                            $html->find('div', $i)->innertext = '';
                            break;
                        }
                    }
                }
                $data['content'] = $html->save();
            }
            $this->showMessage(200, 'success', $data);
        }
        else
            $this->showMessage(500, $errorInfo, array());
    }
    public function getClassTimeTableTemplate()
    {
        $classId = getParameter('classId', 'int');
        $classInfo = $this->model->getClassInfo($classId);
        $timeTableTemplate = D('Dict_schoollist')->getSchoolInfo($classInfo['school_id']);
        $timeTableTemplate = $timeTableTemplate['timetable'];
        $this->showMessage(200, 'success', $timeTableTemplate);
    }
    public function getTeacherTimeTableTemplate()
    {
        $teacherId = getParameter('teacherId', 'int');
        $teacherInfo = D('Auth_teacher')->getTeachInfo($teacherId);
        $timeTableTemplate = D('Dict_schoollist')->getSchoolInfo($teacherInfo['school_id']);
        $timeTableTemplate = $timeTableTemplate['timetable'];
        $this->showMessage(200, 'success', $timeTableTemplate);
    }

    public function setClassTimeTable()
    {
        $classId = getParameter('classId', 'int');
        $info = getParameter('info', 'sArr',false);
        $comments = getParameter('comments', 'str',false);
        $content = getParameter('content', 'str',false);
        if(empty($info))
        {
            $info['content'] = $content;
            $info['comments'] = $comments;
        }
        $classInfo = $this->model->getClassInfo($classId);
        if (1 == $classInfo['is_delete'] || empty($classInfo)) {
            $this->showMessage(500, '班级不存在', array());
        }

        if($this->model->setClassTimeTable($classId,$info,$classInfo['class_status'],$errorInfo))
            $this->showMessage(200, 'success', array());
        else
            $this->showMessage(500, $errorInfo, array());
    }

    public function setTeacherTimeTable()
    {
        $classId = getParameter('classId', 'int');
        $comments = getParameter('comments', 'str');
        $content = getParameter('content', 'str');
        $classInfo = $this->model->getClassInfo($classId);
        if (1 == $classInfo['is_delete'] || empty($classInfo)) {
            $this->showMessage(500, '班级不存在', array());
        }
        if(SCHOOL_CLASS == $classInfo['class_status'])
            $this->showMessage(500, '无法修改校建班教师课表', array());

        $info['content'] = $content;
        $info['comments'] = $comments;

        if($this->model->setTeacherTimeTable($classId,$info,$errorInfo))
            $this->showMessage(200, 'success', array());
        else
            $this->showMessage(500, $errorInfo, array());

    }
}