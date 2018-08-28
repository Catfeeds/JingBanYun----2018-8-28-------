<?php
namespace ApiInterface\Controller\Version1_0;
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
        $filter['keyword'] = I('keyword');
        
        $where=array();
        if (!empty($filter['grade_id'])) $where['dict_grade.id'] = $filter['grade_id'];
        if (!empty($filter['class_id'])) $where['biz_class.id'] = $filter['class_id'];
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
            $this->ajaxReturn(array('status' => 406, 'msg' => '参数传递不正确'));
        }
        $teach_model=D('Auth_teacher');
        $teach_info=$teach_model->getTeachInfo($id);    
        if(empty($teach_info)){
            $this->ajaxReturn(array('status' => 406, 'msg' => '教师信息不存在'));
        }
        $pageData=$this->pageOperation();
        $pageStart=$pageData['start'];
        $pageEnd=$pageData['end']; 
        
        $host=$_SERVER['HTTP_HOST']; 
        $detail_url='http://'.$host.'/ApiInterface/Version1_0/blackboard/blackboardDetail?id=';
        $model=M('biz_blackboard');
        $check=$this->getBlackboardListCondition();
        
        $check['biz_blackboard.publisher_id']=$id;
        $result=$model->where($check)->join("biz_class on biz_class.id=biz_blackboard.class_id")->join("dict_grade on dict_grade.id=biz_class.grade_id")
                ->field('biz_blackboard.id,biz_blackboard.class_id,biz_blackboard.message_title,1 message,biz_blackboard.publisher,'
                        . 'biz_blackboard.create_at,biz_class.name class_name,dict_grade.grade,CONCAT("'.$detail_url.'",biz_blackboard.id) url')->order("biz_blackboard.create_at desc")->limit($pageStart,$pageEnd)->select();   

        foreach ($result as $k=>$v) {
            $data['role_id'] = 2;
            $data['user_id'] = $id;
            $data['b_id'] = $v['id'];
            $bid = M('biz_isread_blackboard')->where( $data )->find();
            if ( $bid ) {
                $result[$k]['is_read'] = 1; //已读
            } else {
                $result[$k]['is_read'] = 2; //未读
            }
        }

        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $result));
    }
    
    /*
     * 根据教师获得的年级 
     * @param   string  id教师id
     * @return  json 
     */
    public function teachGradeInfo(){
        $id=intval(I('id'));
        if(!$id){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确'));
        }
        $teach_model=D('Auth_teacher');
        $teach_info=$teach_model->getTeachInfo($id);    
        if(empty($teach_info)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'教师信息不存在'));
        }
        $teach_second_model=M('auth_teacher_second');
        
        $check['auth_teacher_second.teacher_id']=$id;
        $result=$teach_second_model->join("dict_grade on dict_grade.id=auth_teacher_second.grade_id")->where($check)
                ->field('dict_grade.id,dict_grade.grade')->group('auth_teacher_second.grade_id')->select();
        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $result));
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
        if(!$id || !$grade_id){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确'));
        }
        $teach_model=D('Auth_teacher');
        $teach_info=$teach_model->getTeachInfo($id);    
        if(empty($teach_info)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'教师信息不存在'));
        }
        $grade_model=D('Dict_grade');
        $grade_info=$grade_model->getGradeInfo($grade_id);    
        if(empty($grade_info)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'年级信息不存在'));
        }
        
        $model=M('biz_class'); 
        
        $check['biz_class.grade_id']=$grade_id;
        $check['biz_class.class_teacher_id']=$id;
        $result=$model->where($check)->join("dict_grade on dict_grade.id=biz_class.grade_id")
                ->field('biz_class.id,biz_class.name class_name')->order("biz_class.name")->select();
        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $result));
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
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确'));
        } 
        $student_model=D('Auth_student');
        $student_info=$student_model->getStudentInfo($id);      
        if(empty($student_info)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'学生信息不存在'));
        }
        $pageData=$this->pageOperation();
        $pageStart=$pageData['start'];
        $pageEnd=$pageData['end']; 
        
        $host=$_SERVER['HTTP_HOST']; 
        $detail_url='http://'.$host.'/ApiInterface/Version1_0/blackboard/blackboardDetail?id=';
        
        $model=M('biz_blackboard'); 
        
        $check=$this->getBlackboardListCondition();
        $check['biz_class_student.student_id']=$id;
        $check['biz_class_student.status']=2;
         
        $result=$model->where($check)->join('biz_class on biz_class.id=biz_blackboard.class_id')->join('dict_grade on dict_grade.id=biz_class.grade_id')
                ->join('biz_class_student on biz_class_student.class_id=biz_class.id')
                ->field('biz_blackboard.id,biz_blackboard.class_id,biz_blackboard.message_title,1 message,biz_blackboard.publisher,'
                        . 'biz_blackboard.create_at,biz_class.name class_name,dict_grade.grade,CONCAT("'.$detail_url.'",biz_blackboard.id) url')
                ->order("biz_blackboard.create_at desc")->limit($pageStart,$pageEnd)->select();

        foreach ($result as $k=>$v) {
            $data['role_id'] = 3;
            $data['user_id'] = $id;
            $data['b_id'] = $v['id'];
            $bid = M('biz_isread_blackboard')->where( $data )->find();
            if ( $bid ) {
                $result[$k]['is_read'] = 1; //已读
            } else {
                $result[$k]['is_read'] = 2; //未读
            }
        }

        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $result));
    }
    
    /*
     * 根据学生获得年级 
     * @param   string  id学生id
     * @return  json 
     */
    public function studentGradeInfo(){
        $id=intval(I('id'));
        if(!$id){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确'));
        }
        $student_model=D('Auth_student');
        $student_info=$student_model->getStudentInfo($id);      
        if(empty($student_info)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'学生信息不存在'));
        }
        $model=M('biz_class_student');
        
        $check['biz_class_student.student_id']=$id;
        $check['biz_class_student.status']=2;
        $result=$model->join("biz_class on biz_class.id=biz_class_student.class_id")
                ->join("dict_grade on dict_grade.id=biz_class.grade_id")
                ->where($check)
                ->field('dict_grade.id,dict_grade.grade')->group('biz_class.grade_id')->select();
        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $result));
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
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确'));
        }
        $student_model=D('Auth_student');
        $student_info=$student_model->getStudentInfo($id);      
        if(empty($student_info)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'学生信息不存在'));
        }
        $grade_model=D('Dict_grade');
        $grade_info=$grade_model->getGradeInfo($grade_id);    
        if(empty($grade_info)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'年级信息不存在'));
        }
         
        $model=M('biz_class_student'); 
        $check['biz_class_student.student_id']=$id;
        $check['biz_class_student.status']=2;
        $check['biz_class.grade_id']=$grade_id;
        
        $result=$model->join("biz_class on biz_class.id=biz_class_student.class_id")
                ->join("dict_grade on dict_grade.id=biz_class.grade_id")
                ->where($check)
                ->field('biz_class.id,biz_class.name class_name')->order("biz_class.name")->select();
        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $result));
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
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确'));
        }
        $parent_model=D('Auth_parent');
        $parent_result=$parent_model->getParentInfo($id);        
        if(empty($parent_result)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'家长信息不存在'));
        }
        $pageData=$this->pageOperation();
        $pageStart=$pageData['start'];
        $pageEnd=$pageData['end']; 
        
        $model=M('biz_blackboard');
        $check=$this->getBlackboardListCondition();
        $check['auth_student.parent_id']=$id;
        $check['biz_class_student.status']=2;
        
        $host=$_SERVER['HTTP_HOST']; 
        $detail_url='http://'.$host.'/ApiInterface/Version1_0/blackboard/blackboardDetail?id=';
         
        $result=$model->where($check)->join('biz_class on biz_class.id=biz_blackboard.class_id')->join('dict_grade on dict_grade.id=biz_class.grade_id')
                ->join('biz_class_student on biz_class_student.class_id=biz_class.id')->join('auth_student on auth_student.id=biz_class_student.student_id')
                ->field('biz_blackboard.id,biz_blackboard.class_id,biz_blackboard.message_title,1 message,biz_blackboard.publisher,'
                        . 'biz_blackboard.create_at,biz_class.name class_name,dict_grade.grade,CONCAT("'.$detail_url.'",biz_blackboard.id) url')->group("biz_blackboard.id")->order("biz_blackboard.create_at desc")
                ->limit($pageStart,$pageEnd)->select();

        foreach ($result as $k=>$v) {
            $data['role_id'] = 4;
            $data['user_id'] = $id;
            $data['b_id'] = $v['id'];
            $bid = M('biz_isread_blackboard')->where( $data )->find();
            if ( $bid ) {
                $result[$k]['is_read'] = 1; //已读
            } else {
                $result[$k]['is_read'] = 2; //未读
            }
        }

        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $result));
    }
    
    
    /*
     * 获得家长的孩子所在的年级 
     * @param   string  id学生id
     * @return  json 
     */
    public function ParentGradeInfo(){
        $id=intval(I('id'));
        if(!$id){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确'));
        }
        $parent_model=D('Auth_parent');
        $parent_result=$parent_model->getParentInfo($id);        
        if(empty($parent_result)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'家长信息不存在'));
        } 
        $model=M('biz_class_student');
        $check['biz_class_student.status']=2;
        $check['auth_student.parent_id']=$id;
        
        $result=$model->join("biz_class on biz_class.id=biz_class_student.class_id")
                ->join("auth_student on auth_student.id=biz_class_student.student_id")
                ->join("dict_grade on dict_grade.id=biz_class.grade_id")
                ->where($check)
                ->field('dict_grade.id,dict_grade.grade')->group('biz_class.grade_id')->select();
        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $result));
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
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确'));
        }
        $parent_model=D('Auth_parent');
        $parent_result=$parent_model->getParentInfo($id);        
        if(empty($parent_result)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'家长信息不存在'));
        }
        $grade_model=D('Dict_grade');
        $grade_info=$grade_model->getGradeInfo($grade_id);    
        if(empty($grade_info)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'年级信息不存在'));
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
        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','data' => $result));
    }
    
    
    //小黑板编辑,读取或查看一条数据         
    public function blackboardDetail(){
        $oss_path='http://jbyoss.oss-cn-beijing.aliyuncs.com/'; 
        $id=intval(I('id'));
        if(!$id){  
            $result=array(); 
        }else{  
            
            $Model = M('biz_blackboard'); 
            $result = $Model
                ->join('biz_class on biz_class.id=biz_blackboard.class_id')
                ->field('biz_blackboard.*,biz_class.name as class_name')
                ->where("biz_blackboard.id=$id")
                ->find();       
            if(!empty($result)){
                $toumap['id'] = $result['publisher_id'];
                $tou = M('auth_teacher')->where( $toumap )->find();
                $result['touimg'] = $tou['avatar'];
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
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确'));
        }
        $teach_model=D('Auth_teacher'); 
        $teach_info=$teach_model->getTeachInfo($id);   
        if(empty($teach_info)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'教师信息不存在'));
        }
        $class_model=D('Biz_class');
        $class_result=$class_model->getClassInfo($class_id,$id);   
        if(empty($class_result)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'教师信息不存在'));
        } 
        $model=$this->model;
        $data['message_title']=remove_xss(I('message_title'));
        $data['msg']=remove_xss(I('msg')); 
        $data['msg']='<p><div>'.$data['msg'].'</div></p>';
        
        $data['publisher']=$teach_info['name'];  
        $data['publisher_id']=$id;
        $data['class_id']=$class_id;
        $data['create_at']=time();
        if($model->addBlackboardData($data)){
            $this->ajaxReturn(array('status' => 200, 'msg'=>'success'));
        }else{
            $this->ajaxReturn(array('status' => 406, 'msg'=>'信息发布失败'));
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

        if(!$id){
            //参数错误
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确'));
        }

        if(!$role_id){
            //参数错误
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确'));
        }
        if(!$b_id){
            //参数错误
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确'));
        }

        $data['user_id'] = $id;
        $data['role_id'] = $role_id;
        $data['b_id'] = $b_id;

        $res = M('biz_isread_blackboard')->where($data)->find();

        if ( $res ) { //如果存在标志已经读了
            $this->ajaxReturn(array('status' => 200, 'msg'=>'success'));
        } else { //如果不存在就是该用户未读 那么就进行入库
            $bres = M('biz_isread_blackboard')->add( $data );

            if ( $bres ) {
                $this->ajaxReturn(array('status' => 200, 'msg'=>'success'));
            } else {
                $this->ajaxReturn(array('status' => 406, 'msg'=>'更新小黑板信息错误'));
            }
        }

    }

    public function test() {
        $default_apiKey = 'bGTXvnRG4MEfAwXaVNQ0Gqzz';
        $default_secretkey = '7a6xppOPN4WDm88T9ifoPHIbUfKbwxWR';
        $sendmsg = array(
            'title' => '一封迟到的信',
            'description' => '你猜内容',
        );
        //对所有的设备进行推送
        $is_send = sendPushAll($default_apiKey,$default_secretkey,$sendmsg);

        //对固定设备的推送 一维数组
       /* $channel_id = array(
            '4306213570843985962'
        );
        $is_send = sendPushUserDevice($default_apiKey,$default_secretkey,$sendmsg,$channel_id);*/


        if ( $is_send ) {
            echo "成功";
        } else {
            echo "失败";
        }
    }
}