<?php
namespace ApiInterface\Controller\Version1_0;
use Think\Controller;

class ClassInfoController extends PublicController
{ 

    public $model='';
    public $page_size=20;
    public $firstRow=0;
    public $listRow=0;
            
    public function __construct() {
        parent::__construct(); 
    }


    /**
     * @描述：移除班级
     * @参数：classId[int] Y 班级id
     * @参数：studentId[int] Y 学生id
     * @返回值：code[int]{
     * "-1: "退出失败",
     * "1": "成功"
     * } Y 返回代号
     */
    public function delClassData(){

        $classId = $_POST['classId'];
        $stuid = $_POST['studentId'];

        if(empty($classId)) {
            $result['code'] = 400;
            $this->ajaxReturn(array('status' => 406, 'msg'=>'error','result' => $result));
        }

        if(empty($stuid)) {
            $result['code'] = 400;
            $this->ajaxReturn(array('status' => 406, 'msg'=>'error','result' => $result));
        }

        $map['class_id'] = $classId;
        $map['student_id'] = $stuid;
        $id = M('biz_class_student')->where( $map )->delete();

        if ($id) {
            $result['code'] = 200;
            $this->ajaxReturn(array('status' => 200, 'msg'=>'success','result' => $result));
        } else {
            $result['code'] = 400;
            $this->ajaxReturn(array('status' => 406, 'msg'=>'error','result' => $result));
        }
    }


    /**
     * @描述：获取老师班级列表
     * @参数：classId[int] Y 班级id
     * @参数：studentId[int] Y 学生id
     * @返回值：code[int]{
     * "-1: "退出失败",
     * "1": "成功"
     * } Y 返回代号
     */
    public function teachClassList() {

        $id = $_REQUEST['id'];
        if (empty($id)) {
            $result['msg'] = '对不起老师id参数错误';
            $this->ajaxReturn(array('status' => 406, 'msg'=>'error','result' => $result));
        }

        $teach_model=D('Auth_teacher');
        $teach_info=$teach_model->getTeachInfo($id);

        $Model = M('biz_class');

        $filter['grade_id'] = $_REQUEST['grade'];
        if ($filter['grade_id'] == 0) {
            $filter['class_id'] = 0;
        } else {
            $filter['class_id'] = $_REQUEST['class'];
        }

        if (!empty($filter['grade_id'])) $check['dict_grade.id'] = $filter['grade_id'];
        if (!empty($filter['class_id'])) $check['biz_class.id'] = $filter['class_id'];

        $this->assign('default_grade',$check['dict_grade.id']);
        $this->assign('default_class',$check['biz_class.id']);
        $check['dest_teacherid'] =  $_REQUEST['id'];
        $check['biz_class.flag'] =  1;

        if (!empty($filter['grade_id'])) $where['dict_grade.id'] = $filter['grade_id'];
        if (!empty($filter['class_id'])) $where['biz_class.id'] = $filter['class_id'];
        $where['biz_class.class_teacher_id'] = $_REQUEST['id'];
        $where['biz_class.flag'] =  1;

        if (!empty($filter['grade_id'])){

            $class_model = M('biz_class');
            $classmap['grade_id'] = $filter['grade_id'];
            $class_list = $class_model->where($classmap)->select();
            $this->assign('class_list', $class_list);
        }

        $rlModel = M('biz_class_handsoff');
        $rlResult = $rlModel
            -> where($check)
            -> join('biz_class on biz_class.id = biz_class_handsoff.class_id')
            -> join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
            -> join('dict_grade on dict_grade.id=biz_class.grade_id')
            -> field('biz_class.*,dict_schoollist.school_name,dict_grade.grade,dict_grade.id as did')
            -> order('biz_class.create_at desc')
            -> select();

        for($i=0;$i<sizeof($rlResult);$i++)
            $rlResult[$i]['flag'] = 3;

        $result = $Model
            ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->field('biz_class.*,dict_schoollist.school_name,dict_grade.grade,dict_grade.id as did')
            ->where($where)
            ->order('biz_class.create_at desc')
            //->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        //print_r(M()->getLastSql());die();

        $result = array_merge($rlResult,$result);

        $big_data['teach_info']=$teach_info;
        $big_data['class_data']=$result;
        $this->ajaxReturn(array('status' => 200, 'msg'=>'success','result' => $big_data));
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

    //查看某个班级的学生
    public function classStudent(){
        //班级ID
        $id=intval(I('id'));
        if(empty($id)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确'));
        }
        $class_model=D('Biz_class');
        $class_result=$class_model->getClassInfo($id);
        if(empty($class_result)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'班级信息不存在'));
        }
        $student_class_model=D('Biz_class_student');
        $student_result=$student_class_model->getClassInfo($id);
        $student_ids='';
        if(empty($student_result)){
            $big_data['student']=array();
            $big_data['class']=array();
            $this->ajaxReturn(array('status' => 406, 'msg'=>'该班级下还没有学生','result' => $big_data));
        }else{
            $student_ids='';
            foreach($student_result as $v){
                $student_ids.=$v['student_id'].',';
            }
            $student_ids=rtrim($student_ids,',');
            $student_model=D('Auth_student');
            $student_info=$student_model->getStudents($student_ids);

            if(empty($student_info)){
                $big_data['student']=array();
                $big_data['class']=array();
                $this->ajaxReturn(array('status' => 406, 'msg'=>'该班级下还没有学生','result' => $big_data));
            }else{
                //拼接学生的头像
                foreach($student_info as $key=>$val){

                    if (preg_match('/Resources/', $val['avatar'])){
                        $student_info[$key]['avatar'] = C('oss_path').$val['avatar'];
                    } else {
                        $student_info[$key]['avatar']='http://'.C('REMOTE_ADDR').'/Uploads/StudentAvatars/'.$val['id'].'.jpg';
                    }
                }
                $big_data['student']=$student_info;
                $big_data['class']=$class_result;
                $this->ajaxReturn(array('status' => 200, 'msg'=>'success','result' => $big_data));
            }
        }
    }


    //学生的班级列表,我的班级
    public function studentClassList(){
        //学生ID
        $id=intval(I('id'));
        if(!$id){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确'));
        }
        $student_model=D('Auth_student');
        $student_info=$student_model->getStudentInfo($id);
        if(empty($student_info)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'学生信息不存在'));
        }
        $class_student_model=D('Biz_class_student');
        $class_student=$class_student_model->getStudentClass($id);
        if(empty($class_student)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'您还没有加入任何班级'));
        }else{
            $class_model=D('Biz_class');
            $class_ids='';
            foreach($class_student as $v){
                $class_ids.=$v['class_id'].',';
            }
            $class_ids=rtrim($class_ids, ',');
            $class_result=$class_model->getClassDataInfo($class_ids);
            if(empty($class_result)){
                $this->ajaxReturn(array('status' => 406, 'msg'=>'班级信息不存在'));
            }
            $school_model=D('Dict_schoollist');
            $grade_model=D('Dict_grade');

            foreach($class_result as $key=>$val){
                $school_result=$school_model->getSchoolInfo($val['school_id']);
                $grade_result=$grade_model->getGradeInfo($val['grade_id']);
                if(!empty($grade_result) && !empty($school_result)){
                    $class_result[$key]['school_name']=$school_result['school_name'];
                    $class_result[$key]['grade']=empty($school_result['grade']) ? '':$school_result['grade'];
                }else{
                    unset($class_result[$key]);
                }
            }
            sort($class_result);
            $this->ajaxReturn(array('status' => 200, 'msg'=>'success','result' => $class_result) );
        }
    }


    //家长督学
    public function parentClassList(){
        //家长ID
        $id=intval(I('id'));
        if(!$id){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确') );
        }
        $student_model=D('Auth_student');
        $student_info=$student_model->getParentStudent($id);
        if(empty($student_info)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'您还没有添加任何小孩') );
        }
        $student_ids='';
        foreach($student_info as $v){
            $student_ids.=$v['id'].',';
        }
        $student_ids=rtrim($student_ids,',');
        $student_model=D('Biz_class_student');
        $student_class=$student_model->getStudentClassData($student_ids);
        if(empty($student_class)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'您的小孩还没有加入任何班级') );
        }else{
            //这里去得到年级,学校

            $student_model=D('Auth_teacher');//getTeachInfo
            $school_model=D('Dict_schoollist');
            $grade_model=D('Dict_grade');

            foreach($student_class as $key=>&$val){
                if($val['class_teacher_id']==null){
                    $student_class[$key]['teach_name']=null;
                }else{
                    $teach=$student_model->getTeachInfo($val['class_teacher_id']);
                    $student_class[$key]['teach_name']=$teach['name'];
                }
                if($val['school_id']==null){
                    $student_class[$key]['school_name']=null;
                }else{
                    $school_result=$school_model->getSchoolInfo($val['school_id']);
                    $student_class[$key]['school_name']=$school_result['school_name'];
                }
                if($val['grade_id']==null){
                    $student_class[$key]['grade']=null;
                }else{
                    $grade_result=$grade_model->getGradeInfo($val['grade_id']);
                    $student_class[$key]['grade']=empty($grade_result['grade']) ? '':$grade_result['grade'];
                }


            }
            $this->ajaxReturn(array('status' => 200, 'msg'=>'success','result' => $student_class) );
        }
    }

    //添加家长信息到学生表中
    public function updateStudentParent(){
        //Auth_parent
        $student_id=intval(I('student_id'));
        $parent_id=intval(I('parent_id'));
        if(!$student_id || !$parent_id){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确') );
        }
        $student_model=D('Auth_student');
        $student_info=$student_model->getStudentInfo($student_id);
        if(empty($student_info)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'学生信息不存在') );
        }
        $parent_model=D('Auth_parent');
        $parent_info=$parent_model->getParentInfo($parent_id);
        if(empty($parent_info)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'家长信息不存在') );
        }
        if($student_model->updateStudentParentInfo($parent_id,$student_id)){
            $this->ajaxReturn(array('status' => 200, 'msg'=>'success') );
        }else{
            $this->ajaxReturn(array('status' => 406, 'msg'=>'添加失败') );
        }

    }

    //班级详情添加学生(把该班级和学生进行关联)
    public function addClassStudent(){
        $class_id=intval(I('class_id'));
        $student_id=intval(I('student_id'));
        if(!$class_id || !$student_id){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'参数传递不正确') );
        }
        $class_model=D('Biz_class');
        $class_result=$class_model->getClassInfo($class_id);
        if(empty($class_result)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'班级信息不存在') );
        }
        $student_model=D('Auth_student');
        $student_info=$student_model->getStudentInfo($student_id);
        if(empty($student_info)){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'学生信息不存在') );
        }
        $parent_model=D('Biz_class_student');
        if($parent_model->addClassStudent($class_id,$student_id)){
            $this->ajaxReturn(array('status' => 200, 'msg'=>'success') );
        }else{
            $this->ajaxReturn(array('status' => 406, 'msg'=>'添加失败') );
        }
    }


    //班级课表
    public function classTimeatable(){
        //班级id
        $id=intval(I('id'));
        if(!$id){
            $this->ajaxReturn(array('status' => 406, 'msg'=>'failed') );
        }
        $class_model=D('biz_class');
        $timetable=$class_model->getClassTimetable($id);    //var_dump($timetable);
        if(!empty($timetable)){
            $reg='/width="(\d){2,3}"/';
            $reg2='/<tr><td class="time"><\/td><td class="lunch" colspan="7">(.){6,12}<\/td><\/tr>/';
            $timetable['content']=preg_replace($reg,'', $timetable['content']);
            $timetable['content']=preg_replace($reg2,'', $timetable['content']);
        }
        $this->assign('timetable',$timetable);
        $this->display();
    }
}