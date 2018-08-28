<?php
namespace ApiInterface\Controller\Version1_2;

class Helper_Tool {
    static function unicodeDecode($data)
    {
        function replace_unicode_escape_sequence($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
        }

        $rs = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $data);
        $rs = str_replace('u003E','>',$rs);
        return $rs;
    }
}

class BlackboardController extends PublicController
{

    public $model='';
    public $page_size=20;
    public $firstRow=0;
    public $listRow=0;

    public function __construct() {
        parent::__construct();
        $this->model=D('Biz_blackboard');
    }

    //分页处理
    public function pageOperation(){
        if(I('pageIndex')){
            $page=I('pageIndex')<1?1:I('pageIndex');
        }else{
            $page=1;
        }
        $data['start']=($page-1)*$this->page_size;
        $data['end']=  $this->page_size;
        return $data;
    }

    /*
     * 获得小黑板列表的条件
     * @return  array
     */
    public function getBlackboardListCondition(){
        $filter['grade_id'] = getParameter('gradeId','int',false);
        $filter['class_id'] = getParameter('classId','int',false);
        $filter['class_name'] = I('class_name');
        $filter['keyword'] = getParameter('keyword','str',false);

        $where=array();
        if (!empty($filter['grade_id'])) $where['dict_grade.id'] = $filter['grade_id'];
        if (!empty($filter['class_id'])) $where['biz_class.id'] = $filter['class_id'];
        if (!empty($filter['class_name'])) $where['biz_class.name'] = $filter['class_name'];
        if (!empty($filter['keyword'])) $where['biz_blackboard.message_title'] = array('like', '%' . $filter['keyword'] . '%');
        return $where;
    }

    /**
     * @描述：获取我的所有班级列表
     * @参数：userId[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function getMyAllClassList()
    {
        $userId = getParameter('userId', 'int');
        $role = getParameter('role', 'int');
        $pageIndex = empty($pageIndex) ? 1 : $pageIndex;
        $result = D('Biz_class')->getClassList($role, $userId, -1, 0);
        $convertArray = array(
            array('classid'=> TYPE_FIELD),array('id'),
            array('classname' =>TYPE_FIELD) ,array('name')
        );
        $result = fieldsCompose($result,$convertArray);
        $this->showMessage(200, 'success', $result);
    }

    /**
     * @描述：获取我的所有班级列表
     * @参数：userId[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @参数：gradeId[int] N 年级ID
     * @参数：classId[int] N 班级ID
     * @参数：keyword[int] N 小黑板标题
     * @参数：pageIndex[int] N 页码
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function getBlackboardList()
    {
        $role = getParameter('role','int');
        switch($role)
        {
            case ROLE_TEACHER:$this->teachBlackboardList();
                               break;
            case ROLE_STUDENT:$this->studentBlackboardList();
                               break;
            case ROLE_PARENT: $this->parentBlackboardList();
                               break;
            default:$this->showMessage(500,'暂无数据');break;
        }
    }

    /*
     * 教师的小黑板列表
     */
    private function teachBlackboardList(){
        $userId=getParameter('userId','int');
        $teach_model=D('Auth_teacher');
        $teach_info=$teach_model->getTeachInfo($userId);
        if(empty($teach_info)){
            $this->ajaxReturn(array('status' => 500, 'message' => '教师信息不存在'));
        }
        $pageData=$this->pageOperation();
        $pageStart=$pageData['start'];
        $pageEnd=$pageData['end'];

        $host=$_SERVER['HTTP_HOST'];
        $detail_url='http://'.$host.'/ApiInterface/Version1_2/Blackboard/blackboardDetail?id=';

        $check=$this->getBlackboardListCondition();

        $check['biz_blackboard.publisher_id']=$userId;
        $check['biz_class.is_delete']=0;

        $result = $this->model->getAppBoardList( $userId,$check,$detail_url,$pageStart,$pageEnd);//小黑板列表

        $convertArray = array(
            array('id'=>TYPE_FIELD),array('id'),
            array('message_title'=> TYPE_FIELD),array('title'),
            array('message' =>TYPE_FIELD) ,array('des'),
            array('classlist' =>TYPE_FIELD) ,array('content1'),
            array('createdate' =>TYPE_FIELD) ,array('content2'),
            array('url'=>TYPE_FIELD) ,array('url'),
        );

        $result = fieldsCompose($result,$convertArray);
        $this->ajaxReturn(array('status' => 200, 'message'=>'success','result' => array('data'=>$result)));
    } 


    /*
     * 学生的小黑板列表
     */
    private function studentBlackboardList(){
        $userId=getParameter('userId','int');
        if(!$userId){
            $this->ajaxReturn(array('status' => 406, 'message'=>'参数传递不正确'));
        }
        $student_model=D('Auth_student');
        $student_info=$student_model->getStudentInfo($userId);
        if(empty($student_info)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'学生信息不存在'));
        }
        $pageData=$this->pageOperation();
        $pageStart=$pageData['start'];
        $pageEnd=$pageData['end'];

        $host=$_SERVER['HTTP_HOST'];
        $detail_url='http://'.$host.'/ApiInterface/Version1_2/Blackboard/blackboardDetail?id=';

        $check=$this->getBlackboardListCondition();

        $check['biz_class_student.status']=2;
        $check['biz_class_student.student_id']=$userId;
        $check['biz_class.is_delete']=0;
        $result = D('Biz_blackboard')->getAppBoardStudentList( $userId,$check,$detail_url,$pageStart,$pageEnd);//小黑板列表
        $convertArray = array(
            array('id'=>TYPE_FIELD),array('id'),
            array('message_title'=> TYPE_FIELD),array('title'),
            array('is_read' =>TYPE_FIELD) ,array('is_read'),
            array('message' =>TYPE_FIELD) ,array('des'),
            array('发布人：'=>TYPE_STRING,'publisher' =>TYPE_FIELD) ,array('content1'),
            array('createdate' =>TYPE_FIELD) ,array('content2'),
            array('gradeclass' =>TYPE_FIELD) ,array('content3'),
            array('url'=>TYPE_FIELD,'&classId='=>TYPE_STRING,'class_id'=>TYPE_FIELD) ,array('url'),
        );
        $unReadCount = $this->model->getUnreadBoardCount($check,$userId,ROLE_STUDENT);
        $result = fieldsCompose($result,$convertArray);
        $this->ajaxReturn(array('status' => 200, 'message'=>'success','result' => array('unReadCount' => $unReadCount,'data'=>$result)));
    }


    /*
     * 家长的小黑板列表
     */
    public function parentBlackboardList(){
        $userId=getParameter('userId','int');
        if(!$userId){
            $this->ajaxReturn(array('status' => 406, 'message'=>'参数传递不正确'));
        }
        $parent_model=D('Auth_parent');
        $parent_result=$parent_model->getParentInfo($userId);
        if(empty($parent_result)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'家长信息不存在'));
        }
        $pageData=$this->pageOperation();
        $pageStart=$pageData['start'];
        $pageEnd=$pageData['end'];

        $check=$this->getBlackboardListCondition();
        $check['auth_student.parent_id']=$userId;
        $check['biz_class_student.status']=2;
        $check['biz_class.is_delete']=0;

        $host=$_SERVER['HTTP_HOST'];
        $detail_url='http://'.$host.'/ApiInterface/Version1_2/Blackboard/blackboardDetail?id=';
        
        $result = D('Biz_blackboard')->getAppBoardParentList( $userId,$check,$detail_url,$pageStart,$pageEnd);//小黑板列表
        $convertArray = array(
            array('id'=>TYPE_FIELD),array('id'),
            array('message_title'=> TYPE_FIELD),array('title'),
            array('is_read' =>TYPE_FIELD) ,array('is_read'),
            array('message' =>TYPE_FIELD) ,array('des'),
            array('发布人：'=>TYPE_STRING,'publisher' =>TYPE_FIELD) ,array('content1'),
            array('createdate' =>TYPE_FIELD) ,array('content2'),
            array('gradeclass' =>TYPE_FIELD) ,array('content3'),
            array('url'=>TYPE_FIELD,'&classId='=>TYPE_STRING,'class_id'=>TYPE_FIELD) ,array('url'),
        );
        $unReadCount = $this->model->getUnreadBoardCount($check,$userId,ROLE_PARENT);
        $result = fieldsCompose($result,$convertArray);
        $this->ajaxReturn(array('status' => 200, 'message'=>'success','result' => array('unReadCount' => $unReadCount,'data'=>$result)));
    }



    //小黑板编辑,读取或查看一条数据
    public function blackboardDetail(){ 
        $oss_path=c('oss_path');
        $id = getParameter('id','int');
        $message_id = getParameter('message_id','int',false);
        $role = getParameter('role','int',false);
        $userId = getParameter('userId','int',false);
        $classId = getParameter('classId','int',false);
        if(!$id){
            $result=array();
        }else{
            $result = $result = D('Biz_blackboard')->get_one_data_new( $id,$role,$classId);
            if(!empty($result)){
                $toumap['id'] = $result['publisher_id'];
                $tou = M('auth_teacher')->where( $toumap )->find();
                $result['touimg'] = $tou['avatar'];
            }
            $this->model->setReadStatus($id,$classId,$userId,$role);
        }
        $this->assign('oss_path', $oss_path);
        $this->assign('data', $result);
        $this->display();
    }


    /**
     * @描述：添加小黑板信息
     * @参数：userId[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @参数：message[int] N 小黑板内容
     * @参数：messageTitle[int] Y 标题
     * @参数：classIds[int] N 班级ID列表,多个用逗号隔开
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function addBlackboard(){
        $userId=getParameter('userId','int');
        $role=getParameter('role','int');
        if(ROLE_TEACHER != $role)
        {
            $this->showMessage(500,'您没有添加小黑板消息的权限',array());
        }
        $classList=explode(',',getParameter('classIds','str'));

        $teach_model = D('Auth_teacher');

        $teach_info = $teach_model->getTeachInfo($userId);
        if (empty($teach_info)) {
            $this->ajaxReturn(array('status' => 500, 'message' => '教师信息不存在'));
        }
        // add black board message
        M()->startTrans();
        $model = $this->model;
        $data['message_title'] = remove_xss(getParameter('messageTitle','str'));
        $data['message'] =Helper_Tool::unicodeDecode($_POST['message']);
        $data['publisher'] = $teach_info['name'];
        $data['publisher_id'] = $userId;
        $data['create_at'] = time();

        if (!($a_id = $model->addBlackboardData($data)))
        {
            M()->rollback();
            $this->ajaxReturn(array('status' => 500, 'message' => '信息发布失败'));
        }

        for($i=0;$i<sizeof($classList);$i++) {
            $class_id = $classList[$i];
            $class_model = D('Biz_class');
            $class_result = $class_model->getClassInfo($class_id, $userId);
            if (empty($class_result)) {
                M()->rollback();
                $this->ajaxReturn(array('status' => 500, 'message' => '班级信息不存在'));
            }
                $ac_id = D('Biz_blackboard')->boardLiveClass($a_id, $class_id);
                if (!$ac_id) {
                    M()->rollback();
                    $this->ajaxReturn(array('status' => 500, 'message' => '信息发布失败'));
                }

        }
        M()->commit();

        //按班分批推送
        foreach($classList as $key=>$classId)
        {
            $class_model = D('Biz_class');
            $class_result = $class_model->getClassNameTeacher($classId);
            $parameters = array( 'msg' => array($teach_info['name'],$class_result['class_name'],$data['message_title']) ,
                'url' => array( 'type' => 1,'data' => array($a_id,$classId))
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
                    $parent_parameters=array( 'msg' => array($studentInfo['student_name'],$teach_info['name'],$class_result['class_name'],$data['message_title']) ,
                        'url' => array( 'type' => 1,'data' => array($a_id,$classId))
                    );
                    $ids = array_column($parentsInfo,'id');
                    D('UserInfo')->sendMsg( ROLE_PARENT,implode(',',$ids),$parent_parameters,'BLACKBOARD_PUBLISHED_CHILD',date('Y-m-d H:i:s',time()));
                }
            }
        }

        $this->ajaxReturn(array('status' => 200, 'message' => 'success'));
    }

    //小黑板上传资源
    public function boardFileUpload() {
        $file=$_FILES['file'];
        if(empty($_FILES['file']['tmp_name'])) {
            $arr['status']=407;
            $arr['message']='请选择上传文件';
            echo json_encode($arr);die;
        }
        $type = getFileType($file['name']);
        if($type == '')
        {
            $arr['status']=406;
            $arr['message']='不支持此类型文件上传';
            echo json_encode($arr);die;
        }

        $upload = new \Oss\Ossupload();// 实例化上传类
        $result=$upload->upload(3,$_FILES,6,0);
        $returnArray = explode(",",$result[1]);
        $uploadOK = 1;
        for($i=0;$i<sizeof($returnArray);$i++)
        {
            if($returnArray[$i] == "")
            {
                $uploadOK = 0;
                break;
            }
        }
        if($uploadOK == 0) {
            $arr['message']='上传失败';
            $arr['status']=406;
            echo json_encode($arr);die;
        } else {
            $this->showMessage(200,'success',array('path'=>C('oss_path').$returnArray['0']));
        }

    }

    /**
     * @描述：删除小黑板信息
     * @参数：userId[int] Y 用户ID
     * @参数：role[int] Y 角色ID
     * @参数：id[int] N 小黑板id
     * @返回值：array(
     *    status 状态码
     *    message 信息字符串
     *    result 结果数组
     * )
     */
    public function deleteBlackboard(){
        $userId=getParameter('userId','int');
        $role=getParameter('role','int');
        if($role != ROLE_TEACHER)
        {
            $this->showMessage(500,'您没有删除小黑板信息的权限',array());
        }
        $id=getParameter('id','int');
        if(!$this->model->isBoardInfoExists($userId,$id))
            $this->showMessage(500,'您没有删除该条小黑板信息的权限',array());
        if($this->model->deleteBlackboard($id)){
            $this->showMessage(200,'success',array());
        }else{
            $this->showMessage(500,'删除信息失败',array());
        }
    }
}
