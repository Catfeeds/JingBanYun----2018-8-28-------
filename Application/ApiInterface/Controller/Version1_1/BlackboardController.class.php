<?php
namespace ApiInterface\Controller\Version1_1;
use Think\Controller;

class BlackboardController extends PublicController
{

    public $model='';
    public $page_size=20;
    public $firstRow=0;
    public $listRow=0;

    public function __construct() {
        parent::__construct();
        $this->model=D('Biz_blackboard');
        header("Content-type: text/html; charset=utf-8");
    }

    //分页处理
    public function pageOperation(){
        if(I('page')){
            $page=I('page')<1?1:I('page');
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
        $filter['grade_id'] = intval(I('grade_id'));
        $filter['class_id'] = intval(I('class_id'));
        $filter['class_name'] = I('class_name');
        $filter['keyword'] = I('keyword');

        $where=array();
        if (!empty($filter['grade_id'])) $where['dict_grade.id'] = $filter['grade_id'];
        if (!empty($filter['class_id'])) $where['biz_class.id'] = $filter['class_id'];
        if (!empty($filter['class_name'])) $where['biz_class.name'] = $filter['class_name'];
        if (!empty($filter['keyword'])) $where['biz_blackboard.message_title'] = array('like', '%' . $filter['keyword'] . '%');
        return $where;
    }


    /*
     * 教师的小黑板列表
     * @param   string  id教师id
     * @param   string  grade_id年级id
     * @param   string  class_id班级id
     * @param   string  keyword小黑板标题
     * @return  json
     */
    public function teachBlackboardList(){
        $id=intval(I('id'));
        if(!$id){
            $this->ajaxReturn(array('status' => 406, 'message' => '参数传递不正确'));
        }
        $teach_model=D('Auth_teacher');
        $teach_info=$teach_model->getTeachInfo($id);
        if(empty($teach_info)){
            $this->ajaxReturn(array('status' => 406, 'message' => '教师信息不存在'));
        }
        $pageData=$this->pageOperation();
        $pageStart=$pageData['start'];
        $pageEnd=$pageData['end'];

        $host=$_SERVER['HTTP_HOST'];
        $detail_url='http://'.$host.'/ApiInterface/Version1_1/Blackboard/blackboardDetail?id=';

        $check=$this->getBlackboardListCondition();

        $check['biz_blackboard.publisher_id']=$id;
        $check['biz_class.is_delete']=0;

        $result = D('Biz_blackboard')->getAppBoardList( $id,$check,$detail_url,$pageStart,$pageEnd);//小黑板列表
        $this->ajaxReturn(array('status' => 200, 'message'=>'success','result' => $result));
    } 
    
    
    /*
     * 教师的小黑板列表
     * @param   string  id教师id
     * @param   string  grade_id年级id
     * @param   string  class_name班级名称
     * @param   string  keyword小黑板标题
     * @return  json
     */
    public function teachBlackboardListS(){
        $id=intval(I('id'));
        if(!$id){
            $this->ajaxReturn(array('status' => 406, 'message' => '参数传递不正确'));
        }
        $teach_model=D('Auth_teacher');
        $teach_info=$teach_model->getTeachInfo($id);
        if(empty($teach_info)){
            $this->ajaxReturn(array('status' => 406, 'message' => '教师信息不存在'));
        }
        $pageData=$this->pageOperation();
        $pageStart=$pageData['start'];
        $pageEnd=$pageData['end'];

        $host=$_SERVER['HTTP_HOST'];
        $detail_url='http://'.$host.'/ApiInterface/Version1_1/Blackboard/blackboardDetail?id=';
        $check=$this->getBlackboardListCondition();

        $check['biz_blackboard.publisher_id']=$id;
        $check['biz_class.is_delete']=0;
        $result = D('Biz_blackboard')->getAppBoardList( $id,$check,$detail_url,$pageStart,$pageEnd);//小黑板列表
        $this->ajaxReturn(array('status' => 200, 'message'=>'success','result' => $result));
    }
    

    /*
     * 根据教师获得的年级
     * @param   string  id教师id
     * @return  json
     */
    public function teachGradeInfo(){
        $id=intval(I('id'));
        if(!$id){
            $this->ajaxReturn(array('status' => 406, 'message'=>'参数传递不正确'));
        }
        $teach_model=D('Auth_teacher');
        $teach_info=$teach_model->getTeachInfo($id);
        if(empty($teach_info)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'教师信息不存在'));
        }
        $result = D('Biz_class')->getGradeListByTeacher($id);

        $this->ajaxReturn(array('status' => 200, 'message'=>'success','result' => $result));        
    }

    
    /*
     * 根据教师获得班级
     * @param   string  id教师id
     * @param   string  grade_id年级id
     * @return  json
     */
    /**
     *
     */
    public function getTeachClassS(){
        $id=intval(I('id'));
        $grade_id=intval(I('grade_id'));
        $is_show=intval(I('isShow'));
        if(!$id || !$grade_id){
            $this->ajaxReturn(array('status' => 406, 'message'=>'参数传递不正确'));
        }
        $teach_model=D('Auth_teacher');
        $teach_info=$teach_model->getTeachInfo($id);
        if(empty($teach_info)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'教师信息不存在'));
        }
        $grade_model=D('Dict_grade');
        $grade_info=$grade_model->getGradeInfo($grade_id);
        if(empty($grade_info)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'年级信息不存在'));
        }

        $result = D('Biz_class')->getClassListAppTeacher($id,$grade_id,$is_show);
        $this->ajaxReturn(array('status' => 200, 'message'=>'success','result' => $result));
    }
    

    /*
     * 根据教师获得班级
     * @param   string  id教师id
     * @param   string  grade_id年级id
     * @return  json
     */
    public function getTeachClass(){

        $id=intval(I('id'));
        $grade_id=intval(I('grade_id'));
        $is_show=intval(I('isShow'));
        if(!$id || !$grade_id){
            $this->ajaxReturn(array('status' => 406, 'message'=>'参数传递不正确'));
        }
        $teach_model=D('Auth_teacher');
        $teach_info=$teach_model->getTeachInfo($id);
        if(empty($teach_info)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'教师信息不存在'));
        }
        $grade_model=D('Dict_grade');
        $grade_info=$grade_model->getGradeInfo($grade_id);
        if(empty($grade_info)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'年级信息不存在'));
        }

        $result = D('Biz_class')->getClassListAppTeacher($id,$grade_id,$is_show);
        $this->ajaxReturn(array('status' => 200, 'message'=>'success','result' => $result));

        /*$id=intval(I('id'));
        $grade_id=intval(I('grade_id'));
        if(!$id || !$grade_id){
            $this->ajaxReturn(array('status' => 406, 'message'=>'参数传递不正确'));
        }
        $teach_model=D('Auth_teacher');
        $teach_info=$teach_model->getTeachInfo($id);
        if(empty($teach_info)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'教师信息不存在'));
        }
        $grade_model=D('Dict_grade');
        $grade_info=$grade_model->getGradeInfo($grade_id);
        if(empty($grade_info)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'年级信息不存在'));
        }

        $model=M('biz_class');

        $check['biz_class.grade_id']=$grade_id;
        $check['biz_class.class_teacher_id']=$id;
        $result=$model->where($check)->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->field('biz_class.id,biz_class.name class_name')->order("biz_class.name")->select();       
        $this->ajaxReturn(array('status' => 200, 'message'=>'success','result' => $result));*/
    }


    /*
     * 学生的小黑板列表
     * @param   string  id学生id
     * @param   string  grade_id年级id
     * @param   string  class_id班级id
     * @param   string  keyword小黑板标题
     * @return  json
     */
    public function studentBlackboardList(){
        $id=intval(I('id'));
        if(!$id){
            $this->ajaxReturn(array('status' => 406, 'message'=>'参数传递不正确'));
        }
        $student_model=D('Auth_student');
        $student_info=$student_model->getStudentInfo($id);
        if(empty($student_info)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'学生信息不存在'));
        }
        $pageData=$this->pageOperation();
        $pageStart=$pageData['start'];
        $pageEnd=$pageData['end'];

        $host=$_SERVER['HTTP_HOST'];
        $detail_url='http://'.$host.'/ApiInterface/Version1_1/Blackboard/blackboardDetail?id=';

        $check=$this->getBlackboardListCondition();

        $check['biz_class_student.status']=2;
        $check['biz_class_student.student_id']=$id;
        $check['biz_class.is_delete']=0;
        $result = D('Biz_blackboard')->getAppBoardStudentList( $id,$check,$detail_url,$pageStart,$pageEnd);//小黑板列表

        $this->ajaxReturn(array('status' => 200, 'message'=>'success','result' => $result));
    }

    /*
     * 根据学生获得年级
     * @param   string  id学生id
     * @return  json
     */
    public function studentGradeInfo(){
        $id=intval(I('id'));
        if(!$id){
            $this->ajaxReturn(array('status' => 406, 'message'=>'参数传递不正确'));
        }
        $student_model=D('Auth_student');
        $student_info=$student_model->getStudentInfo($id);
        if(empty($student_info)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'学生信息不存在'));
        }
        $model=M('biz_class_student');

        $check['biz_class_student.student_id']=$id;
        $check['biz_class_student.status']=2;
        $result=$model->join("biz_class on biz_class.id=biz_class_student.class_id")
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->where($check)
            ->field('dict_grade.id,dict_grade.grade')->group('biz_class.grade_id')->select();
        $this->ajaxReturn(array('status' => 200, 'message'=>'success','result' => $result));
    }


    /*
     * 根据学生获得班级
     * @param   string  id学生id
     * @param   string  grade_id年级id
     * @return  json
     */
    public function getStudentClass(){
        $id=intval(I('id'));
        $grade_id=intval(I('grade_id'));
        if(!$id || !$grade_id){
            $this->ajaxReturn(array('status' => 406, 'message'=>'参数传递不正确'));
        }
        $student_model=D('Auth_student');
        $student_info=$student_model->getStudentInfo($id);
        if(empty($student_info)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'学生信息不存在'));
        }
        $grade_model=D('Dict_grade');
        $grade_info=$grade_model->getGradeInfo($grade_id);
        if(empty($grade_info)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'年级信息不存在'));
        }

        $model=M('biz_class_student');
        $check['biz_class_student.student_id']=$id;
        $check['biz_class_student.status']=2;
        $check['biz_class.grade_id']=$grade_id;

        $result=$model->join("biz_class on biz_class.id=biz_class_student.class_id")
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->where($check)
            ->field('biz_class.id,biz_class.name class_name')->order("biz_class.name")->select();
        $this->ajaxReturn(array('status' => 200, 'message'=>'success','result' => $result));
    }


    /*
     * 家长的小黑板列表
     * @param   string  id家长id
     * @param   string  grade_id年级id
     * @param   string  class_id班级id
     * @param   string  keyword小黑板标题
     * @return  json
     */
    public function ParentBlackboardList(){
        $id=intval(I('id'));
        if(!$id){
            $this->ajaxReturn(array('status' => 406, 'message'=>'参数传递不正确'));
        }
        $parent_model=D('Auth_parent');
        $parent_result=$parent_model->getParentInfo($id);
        if(empty($parent_result)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'家长信息不存在'));
        }
        $pageData=$this->pageOperation();
        $pageStart=$pageData['start'];
        $pageEnd=$pageData['end'];

        $check=$this->getBlackboardListCondition();
        $check['auth_student.parent_id']=$id;
        $check['biz_class_student.status']=2;
        $check['biz_class.is_delete']=0;

        $host=$_SERVER['HTTP_HOST'];
        $detail_url='http://'.$host.'/ApiInterface/Version1_1/Blackboard/blackboardDetail?id=';
        
        $result = D('Biz_blackboard')->getAppBoardParentList( $id,$check,$detail_url,$pageStart,$pageEnd);//小黑板列表

        $this->ajaxReturn(array('status' => 200, 'message'=>'success','result' => $result));
    }


    /*
     * 获得家长的孩子所在的年级
     * @param   string  id学生id
     * @return  json
     */
    public function ParentGradeInfo(){
        $id=intval(I('id'));
        if(!$id){
            $this->ajaxReturn(array('status' => 406, 'message'=>'参数传递不正确'));
        }
        $parent_model=D('Auth_parent');
        $parent_result=$parent_model->getParentInfo($id);
        if(empty($parent_result)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'家长信息不存在'));
        }
        $model=M('biz_class_student');
        $check['biz_class_student.status']=2;
        $check['auth_student.parent_id']=$id;

        $result=$model->join("biz_class on biz_class.id=biz_class_student.class_id")
            ->join("auth_student on auth_student.id=biz_class_student.student_id")
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->where($check)
            ->field('dict_grade.id,dict_grade.grade')->group('biz_class.grade_id')->select();
        $this->ajaxReturn(array('status' => 200, 'message'=>'success','result' => $result));
    }


    /*
     * 根据家长的孩子获得班级
     * @param   string  id家长id
     * @param   string  grade_id年级id
     * @return  json
     */
    public function getParentClass(){
        $id=intval(I('id'));
        $grade_id=intval(I('grade_id'));
        if(!$id || !$grade_id){
            $this->ajaxReturn(array('status' => 406, 'message'=>'参数传递不正确'));
        }
        $parent_model=D('Auth_parent');
        $parent_result=$parent_model->getParentInfo($id);
        if(empty($parent_result)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'家长信息不存在'));
        }
        $grade_model=D('Dict_grade');
        $grade_info=$grade_model->getGradeInfo($grade_id);
        if(empty($grade_info)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'年级信息不存在'));
        }

        $model=M('biz_class_student');
        $check['biz_class_student.status']=2;
        $check['dict_grade.id']=$grade_id;
        $check['auth_student.parent_id']=$id;

        $result=$model->join("biz_class on biz_class.id=biz_class_student.class_id")
            ->join("auth_student on auth_student.id=biz_class_student.student_id")
            ->join("dict_grade on dict_grade.id=biz_class.grade_id")
            ->where($check)
            ->field('biz_class.id,biz_class.name class_name')->group('biz_class.id')->order("biz_class.name")->select();
        $this->ajaxReturn(array('status' => 200, 'message'=>'success','result' => $result));
    }


    //小黑板编辑,读取或查看一条数据
    public function blackboardDetail(){ 
        $oss_path=c('oss_path');
        $id=intval(I('id')); 
        $message_id=getParameter('message_id','int',false);
        $role = getParameter('role','int',false);
        $userId = getParameter('userId','int',false);
        if(!$id){
            $result=array();
        }else{

            /*$Model = M('biz_blackboard');
            $result = $Model
                ->join('biz_class on biz_class.id=biz_blackboard.class_id')
                ->field('biz_blackboard.*,biz_class.name as class_name')
                ->where("biz_blackboard.id=$id")
                ->find();*/

            $result = $result = D('Biz_blackboard')->get_one_data_new( $id,$role );

            if(!empty($result)){
                $toumap['id'] = $result['publisher_id'];
                $tou = M('auth_teacher')->where( $toumap )->find();
                $result['touimg'] = $tou['avatar'];
            } 
            if($message_id && $role && $userId){
                $read_status=2;
                $message_model=D('Message');
                $message_model->setMessageReadState($message_id,$userId,$role,$read_status);
            }
        }
        $this->assign('oss_path', $oss_path);
        $this->assign('data', $result);
        $this->display();
    }

    //添加小黑板信息
    public function addBlackboard(){
        //老师id
        $id=intval(I('id'));
        $class_id=intval(I('class_id'));
        if(!$id){
            //参数错误
            $this->ajaxReturn(array('status' => 406, 'message'=>'参数传递不正确'));
        }
        $teach_model=D('Auth_teacher');
        $teach_info=$teach_model->getTeachInfo($id);
        if(empty($teach_info)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'教师信息不存在'));
        }
        $class_model=D('Biz_class');
        $class_result=$class_model->getClassInfo($class_id,$id);
        if(empty($class_result)){
            $this->ajaxReturn(array('status' => 406, 'message'=>'教师信息不存在'));
        }
        $model=$this->model;
        $data['message_title']=remove_xss(I('message_title'));
        $data['message']=remove_xss(I('message'));
        $data['message']='<p><div>'.$data['message'].'</div></p>';

        $board_result_list=remove_xss(I('board_result_list'));

        if (!empty($board_result_list)) {
            $resourceArray = array('','','');
            foreach ($board_result_list as $bk=>$v) {
                $htmlmap = explode('|',$v);
                if ($htmlmap['2'] == 'image') { //图片
                    $resourceArray[2] .= '<div class="blImgBox"><img src="'.C('oss_path').$htmlmap['0'].'" _src="'.C('oss_path').$htmlmap['0'].'"></div>';
                } elseif ($htmlmap['2'] == 'audio') { //语音
                    $resourceArray[0] .= '<div class="blAudioBox"><video class="edui-upload-video  vjs-default-skin video-js" controls="controls" preload="auto" src="'.C('oss_path').$htmlmap['0'].'" data-setup="{}"></video><br></br></div>';
                } else{ //视频
                    $resourceArray[1] .= '<div class="blVideoBox"><video class="edui-upload-video  vjs-default-skin video-js" controls="controls" preload="auto" src="'.C('oss_path').$htmlmap['0'].'" data-setup="{}"></video><br></br></div>';
                }
            }
            $data['message'].= implode('',$resourceArray);
        }


        $data['publisher']=$teach_info['name'];
        $data['publisher_id']=$id;
        //$data['class_id']=$class_id;
        $data['create_at']=time();

        if( $a_id = $model->addBlackboardData($data) ){
            $ac_id = D('Biz_blackboard')->boardLiveClass( $a_id,$class_id );
            if ( $ac_id ) {
                $this->ajaxReturn(array('status' => 200, 'message'=>'success'));
            } else {
                $this->ajaxReturn(array('status' => 406, 'message'=>'信息发布失败'));
            }
        }else {
            $this->ajaxReturn(array('status' => 406, 'message'=>'信息发布失败'));
        }

    }

    /*
     * 根据小黑板的数据id改变数据是否已经读写
     * @param   string  id 用户id
     * @param   string  role_id 用户角色id
     * @param   string  b_id 小黑板id
     * @return  json
     */
    public function is_read_write() {
        $id=intval(I('id'));
        $role_id=intval(I('role_id'));
        $b_id=intval(I('b_id'));
        $class_id=intval(I('class_id'));

        if(!$id){
            //参数错误
            $this->ajaxReturn(array('status' => 406, 'message'=>'参数传递不正确'));
        }

        if(!$role_id){
            //参数错误
            $this->ajaxReturn(array('status' => 406, 'message'=>'参数传递不正确'));
        }
        if(!$b_id){
            //参数错误
            $this->ajaxReturn(array('status' => 406, 'message'=>'参数传递不正确'));
        }

        $data['user_id'] = $id;
        $data['role_id'] = $role_id;
        $data['b_id'] = $b_id;
        $data['class_id'] = $class_id;

        $res = M('biz_isread_blackboard')->where($data)->find();

        if ( $res ) { //如果存在标志已经读了
            $this->ajaxReturn(array('status' => 200, 'message'=>'success'));
        } else { //如果不存在就是该用户未读 那么就进行入库
            $bres = M('biz_isread_blackboard')->add( $data );

            if ( $bres ) {
                $this->ajaxReturn(array('status' => 200, 'message'=>'success'));
            } else {
                $this->ajaxReturn(array('status' => 406, 'message'=>'更新小黑板信息错误'));
            }
        }

    }

    //小黑板上传资源
    public function board_upload() {
        $file=$_FILES['file'];
        if(empty($_FILES['file']['tmp_name'])) {
            $arr['status']=407;
            $arr['message']='请选择上传文件';
            echo json_encode($arr);die;
        }

        $suffix=substr($file['name'],strrpos($file['name'],'.')+1);
        $suffix=strtolower($suffix);



        if($suffix=='mp4' || $suffix=='mov'|| $suffix=='rmvb'|| $suffix=='avi' ){
            $type='video';

        }else if($suffix=='mp3' || $suffix=='wav' || $suffix=='caf'|| $suffix=='amr' || $suffix=='aac' ){
            $type='audio';

        }else if( $suffix=='jpg' || $suffix=='png' || $suffix=='jpeg' ){
            $type='image';

        }else{
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
        } else {
            $file_name = pathinfo($file['name']);
            $returninfo = array('status' => 200, 'message'=>'success','result' => $returnArray['0'].'|'.$file_name['filename']."|".$type);
        }

        echo json_encode($returninfo);

    }

    //小黑板详情
    public function browseBlackboard(){
        $id=intval(I('id'));
        if(!$id){
            //参数错误 之后商量返回负几
            $this->showjson(-1,'参数传递不正确',array());
        }else{
            $model=$this->model;
            //$result=$model->get_one_data($id);
            $result = $result = D('Biz_blackboard')->get_one_data_new( $id );

            $result['blackboard_result_list'] = $model->get_blackboard_result_list($id);

            if(empty($result)){
                $this->showjson(-2,'小黑板信息不存在',array());
            }else{
                //得到班级名称
                $class_model=D('Biz_class');
                $class_result=$class_model->getClassInfo($result['class_id']);
                if(empty($class_result)){
                    $this->showjson(-3,'班级信息不存在',array());
                }

                //$this->showjson(1,'success',$result);
                $teacher_model=D('Auth_teacher');
                $data=$teacher_model->getTeachInfo($class_result['class_teacher_id']);
                $result['publisher']=($data['name']==null)?'':$data['name'];
                $result['class_name']=$class_result['name'];
                $this->showjson(1,'success',$result);
            }

        }
    }
}
