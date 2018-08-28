<?php
namespace Admin\Controller;
use Think\Controller; 
use Common\Common\CSV;

define('ENABLE_CLASS_STATUS',1);
define('DISABLE_CLASS_STATUS',0); 
define('SCHOOL_CLASS_CODE_PRIFIX','00');
define('INDIVIDUAL_CLASS_CODE_PRIFIX','20');
define('SCHOOL_CLASS',1);
define('PERSONAL_CLASS',2); 
define('STUDENT_NOT_EXISTS','学生信息不存在');
define('STUDENT_JOIN_SCHOOL_CLASS_FAILED_MESSAGE','一个学生只能加入一个校内班!');
define('DENY_STUDENT_JOIN_CLASS',3);
define('CLASSSTATE_RECEIVED',2);

class ClassController extends Controller
{  
    public $model='';
    public $page_size=20; 
    
    public function __construct() {  
        parent::__construct();
        $this->model=D('Biz_class');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }
    
   /*
    * 班级列表
    */
    public function classList(){  
        if (!session('?admin')) redirect(U('Login/login'));
        
        $school_model=D('Dict_schoollist');
        $grade_model=D('Dict_grade');
        $filter['province']=getParameter('province','int',false);
        $filter['city']=getParameter('city','int',false);
        $filter['district']=getParameter('district','int',false);
        $filter['school_id']=getParameter('school','int',false);
        $filter['school_status']=$_GET['school_status']; 
        $filter['school_name']=getParameter('school_name','str',false);
        $filter['school_code']=$_GET['school_code'];
        $filter['school_cat']=$_GET['school_cat'];      
        $filter['class_code']=getParameter('class_code','str',false);
        $filter['grade']=getParameter('grade','int',false); 
        $filter['class_name']=getParameter('class_name','str',false); 
        $filter['class_flag']=$_GET['class_flag'];
        $filter['class_status']=getParameter('class_status','int',false);
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition=array();
        $condition['biz_class.is_delete']=0; 
        if(!empty($filter['school_status']))   $condition['dict_schoollist.flag']=intval($filter['school_status']); 
        if(!empty($filter['school_name']))   $condition['dict_schoollist.school_name']=array('like', '%' . $filter['school_name']. '%');
        if(!empty($filter['school_code']))   $condition['dict_schoollist.school_code']=array('like', '%' . $filter['school_code']. '%'); 
        if($filter['school_cat']!='')   $condition['dict_schoollist.school_category']=intval($filter['school_cat']);
        if(!empty($filter['class_code']))   $condition['biz_class.class_code']=array('like', '%' . $filter['class_code']. '%');
        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade'];
        if(!empty($filter['class_name']))   $condition['biz_class.name']=$filter['class_name'];
        if($filter['class_flag']!='')   $condition['biz_class.flag']=intval($filter['class_flag']);
        if(!empty($filter['class_status']))   $condition['biz_class.class_status']=$filter['class_status'];
        if(!empty($filter['school_id'])){   
            $con['biz_class.school_id']=intval($filter['school_id']);
            $con['auth_teacher.school_id']=intval($filter['school_id']);
            $con['_logic'] = 'or';
            $condition['_complex'] = $con;  
        } 
        if(!empty($filter['province'])){
            $con=array();
            $con['temp.provice_id']=$filter['province'];
            $con['dict_schoollist.provice_id']=$filter['province'];
            $con['_logic'] = 'or';
            $where['_complex']=$con;
        }
        if(!empty($filter['city'])){   
            $con=array();
            $con['temp.city_id']=$filter['city'];
            $con['dict_schoollist.city_id']=$filter['city'];
            $con['_logic'] = 'or';
            $where['_complex']=$con; 
        }
        if(!empty($filter['district'])){   
            $con=array();
            $con['temp.district_id']=$filter['district'];
            $con['dict_schoollist.district_id']=$filter['district'];
            $con['_logic'] = 'or';
            $where['_complex']=$con; 
        }
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        }
        
        $city_result=array();
        $district_result=array();
        $school_result=array();
        if(!empty($filter['province']))   $city_result=$school_model->getCityByProvince($filter['province']);
        if(!empty($filter['city']))       $district_result=$school_model->getDistrictByCity($filter['city']); 
        if(!empty($filter['district']))   $school_result=$school_model->getSchoolByDistrict($filter['district']);
        $province_result=$school_model->getProvince();
        $school_category=C('SCHOOL_CATEGORY');  
        $grade_result=$grade_model->getGradeList(true);
        $result=$this->model->getClassData($condition,$order,$where);          
         
        $this->assign('condition_str',$condition_string);
        $this->assign('province',$filter['province']); 
        $this->assign('city',$filter['city']);
        $this->assign('district',$filter['district']);
        $this->assign('school_id',$filter['school_id']);
        $this->assign('school_status',$filter['school_status']);
        $this->assign('school_name',$filter['school_name']);
        $this->assign('school_code',$filter['school_code']);   
        $this->assign('school_cat',$filter['school_cat']);         
        $this->assign('grade',$filter['grade']);
        $this->assign('class_code',$filter['class_code']);
        $this->assign('class_name',$filter['class_name']); 
        $this->assign('class_flag',$filter['class_flag']);
        $this->assign('class_status',$filter['class_status']);  
        $this->assign('order',$order);
         
        $this->assign('school_category',$school_category);
        $this->assign('grade_list',$grade_result);
        $this->assign('province_list',$province_result);
        $this->assign('city_list',$city_result);        
        $this->assign('district_list',$district_result);       
        $this->assign('school_list',$school_result);
        $this->assign('list',$result['data']);
        $this->assign('page',$result['page']);
        $this->display();
    }
    
      
    
    /*
     * 更改班级启用或停用状态 不支持更改为移交中
     */
    public function udpateClassManagement(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $id=getParameter('id','int');
        $result=$this->model->getClassInfo($id);
        $class_result=$this->model->getClassAndGradeInfo($id);
        $teacher_id_all = $this->model->getTeacherIdAll($id);
        
        $parent_id_all = $this->model->getParentIdAll($id);//获取所有的家长的id

        $stu_id_all = $this->model->getStudentIdAll($id);//对所有的学生进行推送

        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{
            $handsoff_status=2;
            if($result['flag']==$handsoff_status){
                $this->showjson(402,COMMON_FAILED_MESSAGE);
            }
            if($result['flag']){
                $status=$this->model->udpateClassStatus($id,DISABLE_CLASS_STATUS);
            }else{
                $status=$this->model->udpateClassStatus($id,ENABLE_CLASS_STATUS);
            }
            if($status){

                if ( $result['flag'] == 1) { //该为停用班级
                    //消息推送
                    $parameters = array(
                        'msg' => array(
                            $class_result['grade'],
                            $class_result['name'],
                            C('ADMIN_ROOT'),
                            $class_result['grade'],
                            $class_result['name'],
                            C('ADMIN_ROOT'),
                        ),
                        'url' => array( 'type' => 0,)
                    );

                    $parameters_copy = array(
                        'msg' => array(
                            $class_result['grade'],
                            $class_result['name'],
                            C('ADMIN_ROOT'),
                            $class_result['grade'],
                            $class_result['name'],
                            C('ADMIN_ROOT'),
                        ),
                        'url' => array( 'type' => 0,)
                    );

                    A('Home/Message')->addPushUserMessage('CLASS_FALG_DISABLE', 2, implode(',', $teacher_id_all) , $parameters_copy);



                    //对所有的学生进行推送

                    A('Home/Message')->addPushUserMessage('STU_CLASS_DISABLE', 3,implode(',', $stu_id_all) , $parameters);

                    //对学生的所有家长进行推送
                    $studentModel = D('Auth_student');
                    foreach($stu_id_all as $key=>$studentId)
                    {
                        $studentInfo = $studentModel->getStudentInfo($studentId);
                        $parameters['url'] = array( 'type' => 1,'data' =>array($studentInfo['id'],$studentInfo['student_name']));
                        A('Home/Message')->addPushUserMessage('FLAGCLASSDISABLE', ROLE_PARENT ,$studentInfo['parent_id'] , $parameters);
                    }
                }

                $this->showjson(200);
            }else{
                $this->showjson(403,COMMON_FAILED_MESSAGE);
            }
        }
        
    }
    
    
    /*
     * 移交班级
     */
    public function transferClass(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);

        $teacher_model=D('Auth_teacher');
        $class_id=getParameter('class_id','int');
        $destTeacherTel=getParameter('destTeacherTelephone','str');
        $sendTeacherTel=getParameter('sendTeacherTelephone','str',false);
        $sendTeachercourse=getParameter('sendTeachercourse','int',false);

        $destTeacherTelinfo =$teacher_model->getTeachInfoTel($destTeacherTel);

        if($sendTeacherTel!='' && $sendTeachercourse){
            $teacher_con['telephone']=$sendTeacherTel;
            $teacher_result=$teacher_model->getTeacherInfo($teacher_con);
            if(empty($teacher_result)){
                $this->showjson(401,'移交教师信息不存在!');
            } 
            $teacher_course_result=$this->model->getDistinctClassGradeByTeacher($teacher_result['id'],$sendTeachercourse);
            if(empty($teacher_course_result)){
                $this->showjson(402,'移交教师不任教该班级该学科!');
            }
            $this->model->startTrans();
            if(!$this->model->transferClass($class_id,$teacher_result['id'],$sendTeachercourse,$destTeacherTel,$errorInfo)){
                $this->model->rollback();
                $this->showjson(404,$errorInfo);  
            }
            if(!$this->model->receiveClass($class_id,$teacher_result['id'],$sendTeachercourse,CLASSSTATE_RECEIVED)){
                $this->model->rollback();
                $this->showjson(406,'移交失败!'); 
            }

            $parameters = array(
                'msg' => array(
                ),
                'url' => array( 'type' => 0 )
            );

            A('Home/Message')->addPushUserMessage('YIJIAO_CLASS', 2, $destTeacherTelinfo['id'] , $parameters);

            $this->model->commit();
            $this->showjson(200);
        }else{ 
            $class_con['biz_class.id']=$class_id;
            $class_result=$this->model->getPersonalClassInfo($class_con);
            if(empty($class_result)){
                $this->showjson(403,'班级信息不存在!');
            }
            $this->model->startTrans();
            if(!$this->model->transferClass($class_id,$class_result['teacher_id'],0,$destTeacherTel,$errorInfo)){
                $this->model->rollback();
                $this->showjson(405,$errorInfo);
            } 
            if(!$this->model->receiveClass($class_id,$class_result['teacher_id'],0,CLASSSTATE_RECEIVED)){
                $this->model->rollback();
                $this->showjson(407,'移交失败!'); 
            }

            $parameters = array(
                'msg' => array(
                ),
                'url' => array( 'type' => 0 )
            );

            A('Home/Message')->addPushUserMessage('YIJIAO_CLASS', 2, $destTeacherTelinfo['id'] , $parameters);

            $this->model->commit();
            $this->showjson(200);
        }
        
    }
    
    
    /*
     * 删除班级
     */
    public function deleteClass(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $id=getParameter('id','int');
        $result=$this->model->getClassInfo($id);

        $class_result=$this->model->getClassAndGradeInfo($id);
        $teacher_id_all = D('Biz_class')->getTeacherIdAll($id);

        $parent_id_all = $this->model->getParentIdAll($id);//获取所有的家长的id
        $student_id_all = $this->model->getStudentIdAll($id);

        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{
            $handsoff_status=2;
            if($result['flag']==$handsoff_status){
                $this->showjson(402,'移交中的班级不允许删除!');
            }
            $this->model->startTrans();
            $status=$this->model->deleteClass($id,$errorInfo,true);  
            if(!$status){
                $this->model->rollback();
                $this->showjson(403,$errorInfo);
            } 
            if(!$this->model->deleteClassTimetable(0,$id)){
                $this->model->rollback();
                $this->showjson(404,COMMON_FAILED_MESSAGE);
            }

            $parameters = array(
                'msg' => array(
                    $class_result['grade'],
                    $class_result['name'],
                    C('ADMIN_ROOT'),
                    $class_result['grade'],
                    $class_result['name'],
                    C('ADMIN_ROOT'),
                ),
                'url' => array( 'type' => 0,)
            );

            A('Home/Message')->addPushUserMessage('DELETE_CLASS', 2, implode(',', $teacher_id_all) , $parameters);

            //给家长发

            $parameters_parent = array(
                'msg' => array(
                    $class_result['grade'],
                    $class_result['name'],
                    C('ADMIN_ROOT'),
                    $class_result['grade'],
                    $class_result['name'],
                    C('ADMIN_ROOT'),
                ),
                'url' => array( 'type' => 0,)
            );

            A('Home/Message')->addPushUserMessage('PARENTDELETE_CLASS', 4,implode(',', $parent_id_all) , $parameters_parent);

            //给学生发
            A('Home/Message')->addPushUserMessage('STU_PARENTDELETE_CLASS', 3,implode(',', $student_id_all) , $parameters_parent);

            $this->model->commit();
            $this->showjson(200);
        }
    }
    
    
    /*
     * 创建班级
     */
    public function createClass(){
        if (!session('?admin')) redirect(U('Login/login'));
          
        if(!empty($_POST)){
            $grade_model=D('Dict_grade');
            $school_model=D('Dict_schoollist');
            $teacher_model=D('Auth_teacher');
            $class_type=getParameter('class_type','int');
            $grade=getParameter('grade','int');
            $class_name=getParameter('class_name','str');
            $school_id=getParameter('schoo_id','int',false);
            $teacher_name=getParameter('teacher_name','str',false);
            $telephone=getParameter('telephone','str',false);
            $grade_result=$grade_model->getGradeInfo($grade);
            if(empty($grade_result)){
                $this->error('年级信息不存在!');
            }  
        
            $data['grade_id']=$grade;
            $data['name']=$class_name;
            $data['class_status']=$class_type;
            $data['create_at']=time();  
            $data['flag']=1;       
            if($class_type==SCHOOL_CLASS){
                //校内班
                $school_result=$school_model->getSchoolInfo($school_id);   
                if(empty($school_result)){
                    $this->error('学校信息不存在!');
                }
                $class_con['biz_class.school_id']=$school_id;
                $class_con['biz_class.grade_id']=$grade;
                $class_con['biz_class.name']=$class_name;
                $class_con['biz_class.is_delete']=0;
                $class_con['biz_class.class_status']=$class_type;
                $class_result=$this->model->getClassDataAll($class_con);    
                if(!empty($class_result)){
                    $this->error('该学校下已有相同的班级!');
                } 
                $this->model->startTrans();
                $data['school_id']=$school_id;
                if(!($insert_id=$this->model->addClass($data))){
                    $this->model->rollback();
                    $this->error('入库失败!');
                } 
                $update_data['class_code']=$insert_id+CLASS_CODE_ADD_NUMBER; 
                if(!$this->model->updateClassInfo($insert_id,$update_data)){
                    $this->model->rollback();
                    $this->error('入库失败!'); 
                }
                $this->model->commit();
                
            }elseif($class_type==PERSONAL_CLASS){  
                $tel_reg="/^1[34578]{1}\d{9}$/";   
                if(!preg_match($tel_reg,$telephone)){       
                    $this->showjson('手机格式不正确!');
                }
                $teacher_con['auth_teacher.name']=$teacher_name;
                $teacher_con['auth_teacher.telephone']=$telephone;
                $teacher_result=$teacher_model->getTeacherInfo($teacher_con);  
                if(empty($teacher_result)){ 
                    $this->error('教师信息不存在!');
                } 
                $personal_class_con['biz_class.class_status']=$class_type;
                $personal_class_con['biz_class.grade_id']=$grade;
                $personal_class_con['biz_class.name']=$class_name;
                $personal_class_con['biz_class.is_delete']=0;
                $personal_class_con['auth_teacher.id']=$teacher_result['id'];
                $class_result=$this->model->getClassDataAll($personal_class_con);
                if(!empty($class_result)){
                    $this->error('已有相同的班级存在!');
                } 
                $this->model->startTrans();
                if(!($insert_id=$this->model->addClass($data))){

                    $this->error('入库失败!');
                }
                $update_data['class_code']=$insert_id+CLASS_CODE_ADD_NUMBER;
                if(!$this->model->updateClassInfo($insert_id,$update_data)){
                    $this->model->rollback();
                    $this->error('入库失败!'); 
                }
                $classTeacherData['class_id'] = $insert_id;
                $classTeacherData['teacher_id'] = $teacher_result['teacher_id'];
                $classTeacherData['create_at'] = time();
                $classTeacherData['is_handler'] = 1;
                if(!$this->model->addClassTeacher($classTeacherData)){
                    $this->error('入库失败!');
                }

                //消息推送
                $parameters = array(
                    'msg' => array(
                        C('ADMIN_ROOT'),
                        $grade_result['grade'],
                        $class_name,
                    ),
                    'url' => array( 'type' => 0 )
                );

                A('Home/Message')->addPushUserMessage('ADD_NEWS_PERSON_CLASS', 2, $teacher_result['id'] , $parameters);

                $this->model->commit();
            }else{
                $this->error('参数错误!');
            }  
            $this->redirect(U('Class/classList'));
        }else{
            $school_model=D('Dict_schoollist');
            $grade_model=D('Dict_grade');
            $province_result=$school_model->getProvince();
            $grade_result=$grade_model->getGradeList(true);
            $this->assign('province_list',$province_result);
            $this->assign('grade_list',$grade_result);
            $this->display();
        }
    }
    
        
    /*
     * 班级详情
     */
    public function classDetail(){
        $id=getParameter('id','int');
        $result=$this->model->getClassSchoolData($id); 
        $this->assign('data',$result);      
        $this->display();
    }
    
    
    /*
     * 班级课表
     */
    public function class_iframe_classTimetable(){  
        $course_model=D('Dict_course');
        $class_id=getParameter('id','int'); 
        $result=$this->model->getClassSchoolData($class_id);        
        
        $course_result=$course_model->getCourseList();  
        $this->assign('course_list',$course_result);
        $this->assign('class_id',$class_id); 
        
         if($result['class_status']==1){ 
             $comments=$this->model->getSchoolClassComment($class_id);  
             $this->assign('comments',$comments);
             $this->display();
         }else{
             $this->assign('teacher_id',$result['teacher_id']);
             $this->display('class_iframe_classTimetable_teacher_class');
         }  
    }
    
    
    /*
     * 通过某个班级和学科获得其下面的所有教师
     */
    public function getClassCourseTeacher(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $class_id=getParameter('id','int');
        $course_id=getParameter('course_id','int');
        $condition['dict_course.id']=$course_id;
        $condition['biz_class.id']=$class_id;
        $result=$this->model->getClassTeacherCourse($condition,true);  
        $this->showjson(200,'',$result);
    }
    
    
    /*
     * 修改课表
     */
    public function updateClassTimetable(){      
        $classId=getParameter('class_id','int');    
        $data['class_id'] = $classId;
        $result=$this->model->getClassSchoolData($classId);     
        if(empty($result)){
            $this->error('参数错误!');
        } 
        $data['comments'] = getParameter('comments', 'str',false);
        $data['update_at'] = time();
        if($result['class_status']==PERSONAL_CLASS){         
            $data['content'] = $_POST['content'];       
            $this->model->updateTimeTableData($classId,$data); 
        }else{ 
            //校内班    
            $data['course_teacher']=$_POST['courses'];    
            if(empty($data['course_teacher'])){
                $update_data['comments']=$data['comments'];
                $this->model->updateSchoolClassComment($classId,$update_data);
            }else{ 
                $this->model->setClassTimeTable($classId,$data['course_teacher'],$data['comments'],$result['class_status'],$error_info);
            }
        }
        $this->redirect('Class/class_iframe_classTimetable',array('id'=>$classId));
        
    }
    
    
    /*
     * 获得班级下的学生信息 iframe
     */
    public function class_iframe_student(){
        $filter['class_id']=getParameter('id','int');        
        $filter['student_name']=getParameter('student_name','str',false);
        $filter['parent_tel']=getParameter('parent_tel','str',false);
        $filter['status']=$_GET['status']; 
        $filter['order']=getParameter('order','int',false); 
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition=array(); 
        $condition['auth_student.flag']=array('neq',-1); 
        if(!empty($filter['class_id']))   $condition['biz_class.id']=$filter['class_id'];
        if(!empty($filter['student_name']))   $condition['student_name']=array('like', '%' . $filter['student_name']. '%');
        if(!empty($filter['parent_tel']))   $condition['parent_tel']=array('like', '%' . $filter['parent_tel']. '%');
        if($filter['status']!='')   $condition['auth_student.flag']=intval($filter['status']);
        
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        }
        
        $class_result=$this->model->getClassSchoolData($filter['class_id']); 
        $result=$this->model->getClassStudentData($condition,$order);       
            
        $this->assign('condition_str',$condition_string);
        $this->assign('class_data',$class_result);
        $this->assign('student_name',$filter['student_name']);
        $this->assign('parent_tel',$filter['parent_tel']);
        $this->assign('status',$filter['status']);
        $this->assign('order',$order);
        
        $this->assign('class_id',$filter['class_id']);
        $this->assign('list',$result['data']);    
        $this->assign('page',$result['page']);
        $this->display();
    }
    
    
    /*
     * 班级添加学生
     */
    public function classAddStudent(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $student_model=D('Auth_student');
        $class_model=D('Biz_class');
        $studnet_name=getParameter('student_name','str');
        $parent_tel=getParameter('parent_tel','str');
        $class_id=getParameter('class_id','int');  
        //判断学校是否存在
        $school_class=1;
        $class_result=$this->model->getClassSchoolData($class_id);

        if(empty($class_result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        } 
        $student_con['student_name']=$studnet_name;
        $student_con['parent_tel']=$parent_tel;
        $student_result=$student_model->getStudentDataAll($student_con);

        $class_result_info=$this->model->getClassAndGradeInfo($class_id);

        $parent_info = D('Auth_parent')->getParentInfoByTelephone($parent_tel);

        if(empty($student_result)){
            $this->showjson(402,STUDENT_NOT_EXISTS);        
        }else{
            $student_id=$student_result[0]['id'];
        
            $condition['auth_student.id']=$student_id; 
            $condition['biz_class_student.status']=CLASS_STUDETN_STATUS; 
            if($class_result['class_status']==$school_class){
                if($student_result[0]['school_id']!=$class_result['school_id']){
                    $this->showjson(403,'校内班不允许跨学校加入!');
                } 
                $condition['biz_class.class_status']=$class_result['class_status'];
                $result=$class_model->getClassStudentDataAll($condition);  
                if(!empty($result)){
                    $this->showjson(404,STUDENT_JOIN_SCHOOL_CLASS_FAILED_MESSAGE);
                }
            }
        }  
        //判断该学生是否加入过该班级
        $condition['biz_class.id']=$class_id;
        $result=$class_model->getClassStudentDataAll($condition);       
        if(!empty($result)){
            $this->showjson(405,'该学生已加入该班级!');
        }
        
        $class_student_data['class_id']=$class_id;
        $class_student_data['student_id']=$student_id;
        $class_student_data['create_at']=time();
        $class_student_data['status']=2;
        $this->model->startTrans();
        if(!$class_model->addClassStudentData($class_student_data)){
            $this->model->rollback();
            $this->showjson(406,COMMON_FAILED_MESSAGE);
        }
        if(!$class_model->deleteClassStudentRecord($class_id,$student_id)){
            $this->model->rollback();
            $this->showjson(407,COMMON_FAILED_MESSAGE);
        }
        /*if(!$class_model->updateClassStudentCount($class_id,true)){
            $this->model->rollback();
            $this->showjson(407,COMMON_FAILED_MESSAGE);
        }*/

        if ($class_result['class_status'] == 2) {
            $parameters = array(
                'msg' => array(
                    $studnet_name,
                    $class_result['grade'],
                    $class_result['class_name'],
                ),
                'url' => array( 'type' => 1,'data'=>array($class_id) )
            );

            $teacher_id_send = $this->model->getTeacherId($class_result['class_id']);

            A('Home/Message')->addPushUserMessage('STUDENT_ADD_PERSON_CLASS', 2, $teacher_id_send['teacher_id'] , $parameters);
        }

        $parameters = array(
            'msg' => array(
                $studnet_name,
                C('ADMIN_ROOT'),
                $class_result_info['grade'],
                $class_result_info['name'],
            ),
            'url' => array( 'type' => 1,'data'=>array($student_id,$studnet_name))
        );

        A('Home/Message')->addPushUserMessage('CLASSADDSTUDENT', 4,$parent_info['id'] , $parameters);


        $parametersstu = array(
            'msg' => array(
                C('SCHOOL_ROOT'),
                $class_result_info['grade'],
                $class_result_info['name'],
            ),
            'url' => array( 'type' => 0,)
        );

        A('Home/Message')->addPushUserMessage('CLASSADDSENDSTUDENT', 3,$student_id , $parametersstu);

        $this->model->commit();
        $this->showjson(200);
    }
    
    
    /*
     * 同意或拒绝学生加入班级
     */ 
    public function managementStudentClassInfo(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $student_model=D('Auth_student');
        $id=getParameter('id','int');
        $student_id=getParameter('student_id','int');
        $status=getParameter('status','int');
        $class_result=$this->model->getClassSchoolData($id);
        $student_result=$student_model->getStudentInfo($student_id);   
        if(empty($class_result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        } 
        if(empty($student_result)){
            $this->showjson(402,ID_NOT_EXISTS_MESSAGE);
        }
        if(APPLY_SCHOOL_ALLOW!=$status && APPLY_SCHOOL_DENY!=$status){
            $this->showjson(403,COMMON_FAILED_MESSAGE);
        } 
        if(APPLY_SCHOOL_ALLOW==$status){
            if(!$this->model->updateClassStudentStatus($id,$student_id,2,$error_info)){
                $this->showjson(404,COMMON_FAILED_MESSAGE);
            }
        }else{
            $this->model->startTrans();
            if(!$this->model->removeClassStudentById($id,$student_id)){
                $this->model->rollback();
                $this->showjson(405,COMMON_FAILED_MESSAGE);
            }
            /*if($this->model->updateClassStudentCount($id)){
                $this->model->rollback();
                $this->showjson(406,COMMON_FAILED_MESSAGE);
            }*/
            $this->model->commit();
        }  
        $this->showjson(200); 
    }
    
    
    /*
     * 获得班级下的教师信息 iframe
     */
    public function class_iframe_teacher(){
        $filter['class_id']=getParameter('id','int'); 
        $order=getParameter('order','int',false);
        $order?$order='asc':$order='desc';
        
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        }
        $class_id=$filter['class_id'];
        $condition['biz_class.id']=$filter['class_id'];  
        $class_result=$this->model->getClassSchoolData($class_id);  
        $result=$this->model->getClassTeacher($condition,$order); 
        
        $this->assign('condition_str',$condition_string);
        $this->assign('data',$class_result);
        $this->assign('class_id',$class_id);  
        $this->assign('list',$result);
        $this->display();
    }
    
    
    /*
     * 获得教师的某个班级的任教学科
     */
    public function getTeacherCourseByClass(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $teacher_model=D('Auth_teacher');
        $telephone=getParameter('tel','str'); 
        
        $condition['telephone']=$telephone;
        $result=$teacher_model->getTeacherInfo($condition);
        if(empty($result)){
            $this->showjson(401,'教师信息不存在!');
        }
        $class_con['biz_class.class_status']=SCHOOL_CLASS;
        $class_con['biz_class.is_delete']=0;
        $class_con['biz_class_teacher.teacher_id']=$result['id'];
        $class_result=$this->model->getTeacherSchoolClassData($class_con);
        $this->showjson(200,'',$class_result);
    }
    
    
    /*
     * 获得教师的任教学科
     */
    public function getTeacherCourse(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $teacher_model=D('Auth_teacher');
        $teacher_name=getParameter('name','str');
        $telephone=getParameter('tel','str');
        $order='desc';
        $condition['auth_teacher.telephone']=$telephone;
        $condition['auth_teacher.name']=$teacher_name;
        $result=$teacher_model->getTeacherCourse($condition,$order,true);
        $this->showjson(200,'',$result);
        
    }
     
    
    
    /*
     * 获得班级下的学生的家长信息   iframe
     */
    public function class_iframe_parent(){ 
        $filter['class_id']=getParameter('id','int');
        $filter['parent_name']=getParameter('parent_name','str',false);
        $filter['parent_tel']=getParameter('parent_tel','str',false);
        $filter['status']=getParameter('status','int',false);
        $filter['student_name']=getParameter('student_name','str',false);
        $filter['privilege_type']=$_GET['privilege_type'];
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition=array(); 
        $having='';
        if(!empty($filter['class_id']))   $condition['biz_class.id']=$filter['class_id'];
        if(!empty($filter['parent_name']))   $condition['auth_parent.parent_name']=array('like', '%' . $filter['parent_name']. '%');
        if(!empty($filter['parent_tel']))   $condition['auth_student_parent_contact.parent_tel']=array('like', '%' . $filter['parent_tel']. '%');
        if(!empty($filter['status']))   $condition['auth_parent.flag']=$filter['status'];
        if(!empty($filter['student_name']))   $condition['auth_student.student_name']=array('like', '%' . $filter['student_name']. '%');
        if(!empty($filter['privilege_type']))   $having=$filter['privilege_type']; 
        
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        } 
        $result=$this->model->getClassParentData($condition,$order,$having);    
        
        $this->assign('condition_str',$condition_string);
        $this->assign('parent_name',$filter['parent_name']);
        $this->assign('parent_tel',$filter['parent_tel']);
        $this->assign('status',$filter['status']);
        $this->assign('student_name',$filter['student_name']);
        $this->assign('privilege_type',$filter['privilege_type']);
        $this->assign('order',$order); 
        
        $this->assign('list',$result['data']);
        $this->assign('page',$result['page']);
        $this->assign('class_id',$filter['class_id']);
        
        $this->display();
    }
    
     
    
    
    /*
     * 修改班级
     */
    public function updateClass(){ 
        if (!session('?admin')) redirect(U('Login/login'));
        if(!empty($_POST)){ 
            $class_id=getParameter('id','int');
            $class_name=getParameter('class','str');
            $result=$this->model->getClassInfo($class_id);

            $class_result_info=$this->model->getClassAndGradeInfo( $class_id );
            $teacher_id_all = D('Biz_class')->getTeacherIdAll( $class_id );
            $parent_id_all = D('Biz_class')->getParentIdAll( $class_id );
            $student_id_all = D('Biz_class')->getStudentIdAll( $class_id );

            if(empty($result)){
               $this->error('班级信息有误!'); 
            } 
            //查询是否已经存在相同的班级!
            if($result['class_status']==SCHOOL_CLASS){
                $class_con['biz_class.school_id']=$result['school_id'];
                $class_con['biz_class.grade_id']=$result['grade_id'];
                $class_con['biz_class.name']=$class_name;
                $class_con['biz_class.is_delete']=0;
                $class_con['biz_class.class_status']=SCHOOL_CLASS;
                $class_result=$this->model->getClassDataAll($class_con);    
                if(!empty($class_result)){
                    $this->error('该学校下已有相同的班级!');
                } 
            }else{
                $personal_class_con['biz_class.class_status']=PERSONAL_CLASS;
                $personal_class_con['biz_class.grade_id']=$result['grade_id'];
                $personal_class_con['biz_class.name']=$class_name;
                $personal_class_con['biz_class.is_delete']=0;
                $personal_class_con['auth_teacher.id']=$result['teacher_id'];
                $class_result=$this->model->getClassDataAll($personal_class_con);
                if(!empty($class_result)){
                    $this->error('已有相同的班级存在!');
                }
            }
            
            $data['name']=$class_name;
            if(!$this->model->updateClassInfo($class_id,$data)){
                $this->error('修改信息失败');
            }

            $parameters = array(
                'msg' => array(
                    $class_result_info['grade'],
                    $class_result_info['name'],
                    C('SCHOOL_ROOT'),
                    $class_result_info['grade'],
                    $class_name
                ),
                'url' => array( 'type' => 0,)
            );

            A('Home/Message')->addPushUserMessage('XIUGAI_CLASS', 2, implode(',', $teacher_id_all) , $parameters);

            A('Home/Message')->addPushUserMessage('PARENTEDIT_CLASS', 4,implode(',', $parent_id_all)  , $parameters);

            A('Home/Message')->addPushUserMessage('STUDIT_CLASS', 3,implode(',', $student_id_all)  , $parameters);

            $this->redirect(U('Class/updateClass'),array('id'=>$class_id));
        }else{ 
            $id=getParameter('id','int');      
            $result=$this->model->getClassSchoolData($id);   
            $this->assign('data',$result);         
            $this->display('classModify');
        }
    }
    
    
    /*
     * code
     * 1为gb2312转utf8
     * 2为gbk转utf8
     * 3为utf-8直接返回
     * 4为utf8转gbk
     */
    function encode_string($code,$string){
        $return_string='';
        if($code==1){
            $return_string=iconv('gbk', 'utf-8', $string);
        }else if($code==2){
            $return_string=iconv('gbk', 'utf-8', $string);
        }else if($code==0){
            $return_string=$string;
        }else{
            $return_string=iconv('utf-8', 'gbk', $string);
        }  
        return $return_string;
    }
    
    
    /*
     * 导入班级视图
     */
    public function importClassView(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        $school_model=D('Dict_schoollist'); 
        $province_result=$school_model->getProvince();
        $this->assign('province_list',$province_result);
        $this->display('classImport');
    }
    
    
    /*
     * 下载班级模板
     */
    public function downloadClassDemo(){
        $csv=new CSV(); 
        $class_type=getParameter('class_type','int');  
        if($class_type==1){
            $file="Public/csv/admin/schoolClassDemo.csv"; 
        }else{ 
            $file="Public/csv/admin/personalClassDemo.csv"; 
        } 
        $csv->downloadFile($file);
    }
    
    
    /*
     * 下载导入失败的数据
     */
    public function downloadImportErrorData(){
        if (!session('?admin')) redirect(U('Login/login'));
          
        $class_type=getParameter('class_type','int');
        $grade_arr=$_POST['grade'];
        $class_arr=$_POST['class'];  
        $teacher_name_arr=$_POST['name'];
        $telephone_arr=$_POST['telephone'];
        if($class_type){
            $str="年级,班级\n";
        }else{
            $str="年级,班级,管理教师,管理教师手机号\n";
        }
        $str=iconv('utf-8','gbk', $str);
        foreach($grade_arr as $key=>$val){
            $grade=iconv('utf-8','gbk', $val);
            $class=iconv('utf-8','gbk', $class_arr[$key]);      
            $teacher_name=iconv('utf-8','gbk', $teacher_name_arr[$key]);
            $telephone=iconv('utf-8','gbk', $telephone_arr[$key]);
            if($class_type==1){
                $str.=$grade.",".$class."\n";
            }else{
                $str.=$grade.",".$class.",".$teacher_name.",".$telephone."\n";
            }
        } 
            $filename=date('Ymd').rand(0,1000).'班级内学生导入失败信息'.'.csv';
            $csv=new CSV();
            $csv->downloadFileCsv($filename,$str);
    }
    
    
    /*
     * 导入班级
     */
    public function importClass(){
        if(empty($_FILES)){ 
            $this->showjson(1001);  //1002文件为空
        }
        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){
            $this->showjson($result);
        }
        $str_encode=A('Admin/Student')->getStringEncode($result['result'][0][0]);   
        $i_data=$result['result'];        
        $length=$result['length'];
        
        $school_model=D('Dict_schoollist'); 
        $grade_model=D('Dict_grade');  
        $teacher_model=D('Auth_teacher');           
              
        $school_class=1;
        $personal_class=2;   
        $class_type=getParameter('class_type','int');
        if($class_type==$school_class){
            $school_id=getParameter('school_id','int'); 
            $school_result=$school_model->getSchoolInfo($school_id);   
            if(empty($school_result)){
                $this->showjson(1003,'学校信息不存在!');
            }
        }
        
        
        if($class_type!=$school_class && $class_type!=$personal_class){
            $this->showjson(1004,'班级类型不正确!');
        } 
        
        $notice_array=array();
        $success_array=array();
        for($i=1;$i<$length;$i++){
            $data['grade']=$this->encode_string($str_encode,$i_data[$i][0]);
            $data['class']=$this->encode_string($str_encode,$i_data[$i][1]);
            $grade_result=$grade_model->getGradeByName($data['grade']);
            $notice=$data;
            if(empty($grade_result)){
                $notice['notice_message']='年级不存在';
                $notice_array[]=$notice;
                continue;
            } 
            if($class_type==$school_class){
                $class_condition['dict_grade.id']=$grade_result['id'];
                $class_condition['biz_class.name']=$data['class'];
                $class_condition['biz_class.school_id']=$school_id;    
                $class_condition['biz_class.is_delete']=0;
                $class_count=$this->model->getClassCount($class_condition);         
                if($class_count){
                    $notice['notice_message']='班级已存在';
                    $notice_array[]=$notice;
                    continue;
                }
                $class_data['name']=$data['class'];
                $class_data['grade_id']=$grade_result['id'];
                $class_data['school_id']=$school_id;
                $class_data['create_at']=time();
                $class_data['class_status']=$class_type;
                $class_data['flag']=1;
                 
                $this->model->startTrans();
                if(!($insert_id=$this->model->addClass($class_data))){
                    $notice['notice_message']='数据插入失败';
                    $notice_array[]=$notice;
                    continue;
                } 
                $update_data['class_code']=$insert_id+CLASS_CODE_ADD_NUMBER; 
                if(!$this->model->updateClassInfo($insert_id,$update_data)){
                    $notice['notice_message']='数据插入失败';
                    $notice_array[]=$notice;
                    continue;
                }
                $this->model->commit();
                $success_array[]=$data; 
            }else{
                //个人班级
                $data['name']=$this->encode_string($str_encode,$i_data[$i][2]);        
                $data['telephone']=$this->encode_string($str_encode,$i_data[$i][3]);   
                $notice['name']=$data['name'];
                $notice['telephone']=$data['telephone'];
                //判断手机号 
                if(!preg_match("/^1[34578]{1}\d{9}$/",$data['telephone'])){      
                    $notice['notice_message']='手机号格式不正确';
                    $notice_array[]=$notice;
                    continue;
                }
                $teacher_result=$teacher_model->getTeacherByTel($data['telephone']);       
                if(empty($teacher_result)){
                    $notice['notice_message']='该教师信息不存在';
                    $notice_array[]=$notice;
                    continue;
                }else{ 
                    if($teacher_result['name']!=$data['name']){     
                        $notice['notice_message']='该教师信息不存在';
                        $notice_array[]=$notice;
                        continue;
                    }
                } 
                $class_condition['dict_grade.id']=$grade_result['id'];
                $class_condition['biz_class.name']=$data['class'];
                $class_condition['auth_teacher.school_id']=$teacher_result['school_id']; 
                $class_condition['biz_class.is_delete']=0;
                $class_count=$this->model->getClassCount($class_condition);         
                if($class_count){
                    $notice['notice_message']='班级已存在';
                    $notice_array[]=$notice;
                    continue;
                }        
                $class_data['name']=$data['class'];
                $class_data['grade_id']=$grade_result['id'];
                $class_data['school_id']=$teacher_result['school_id'];
                $class_data['create_at']=time();
                $class_data['class_status']=$class_type;
                $class_data['flag']=1;
                  
                $this->model->startTrans();
                if(!($insert_class_id=$this->model->addClass($class_data))){
                    $this->model->rollback();
                    $notice['notice_message']='数据插入失败';
                    $notice_array[]=$notice;
                    continue;
                }
                $class_code=$insert_class_id+CLASS_CODE_ADD_NUMBER;
                $update_data['class_code']=$class_code;
                if(!$this->model->updateClassInfo($insert_id,$update_data)){
                    $this->model->rollback();
                    $notice['notice_message']='数据插入失败';
                    $notice_array[]=$notice;
                    continue;
                } 
                $class_teacher_data['class_id']=$insert_class_id;
                $class_teacher_data['teacher_id']=$teacher_result['id'];
                $class_teacher_data['create_at']=time();
                $class_teacher_data['is_handler']=1;
                if(!$this->model->addClassTeacher($class_teacher_data)){
                    $this->model->rollback();
                    $notice['notice_message']='数据插入失败';
                    $notice_array[]=$notice;
                    continue;
                } 
                $this->model->commit();
                $success_array[]=$data;
            } 
        }
        $big_data=array();
        $big_data['class_type']=$class_type;
        $big_data['success']=$success_array;
        $big_data['failed']=$notice_array;
        echo json_encode($big_data);
    }
    
    
    /*
     * 批量导出班级
     */
    public function exportedClass(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        set_time_limit(0); 
        if(empty($_POST)){
            $this->error('参数错误');
        }else{ 
            $condition_arr=I('hid'); 
            $condition['biz_class.id']=array('in',$condition_arr);
            $data=$this->model->getAllClassData($condition);    
            $str="年级,班级,班级代码,所属学校,班级状态,班级类型,创建教师,创建教师手机号\n";
            $str=iconv('utf-8','gbk', $str);
            foreach($data as $v){
                $grade=iconv('utf-8','gbk', $v['grade']);
                $class_name=iconv('utf-8','gbk', $v['class_name']);  
                $class_code=sprintf("%s",iconv('utf-8','gbk', $v['class_code'])); 
                $school_name=iconv('utf-8','gbk', $v['school_name']);
                if($v['flag']==1){
                    $class_flag='正常';
                }elseif($v['flag']==2){
                    $class_flag='移交中';
                }else{
                    $class_flag='停用';
                }
                $class_flag=iconv('utf-8','gbk', $class_flag); 
                if($v['class_status']==1){
                    $class_status='校内班';
                }else{
                    $class_status='个人班';
                }
                $class_status=iconv('utf-8','gbk', $class_status); 
                $teacher_name=iconv('utf-8','gbk', $v['teacher_name']);
                $telephone=$v['telephone'];
                $str.=$grade.",".$class_name.",".$class_code.",".$school_name.",".$class_flag.",".$class_status.",".$teacher_name.",".$telephone."\n";
            }
            $filename=date('Ymd').rand(0,1000).'Class'.'.csv';
            $csv=new CSV();
            //export disable
            //$csv->downloadFileCsv($filename,$str);
        }
    }
            
    
    /*
     * 导出全部班级
     */
    public function exportedClassAll(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        set_time_limit(0);
        $filter['province']=getParameter('province','int',false);
        $filter['city']=getParameter('city','int',false);
        $filter['district']=getParameter('district','int',false);
        $filter['school_id']=getParameter('school_id','int',false);
        $filter['school_status']=$_GET['school_status'];
        $filter['is_create_administartor']=$_GET['is_create_administartor'];
        $filter['school_name']=getParameter('school_name','int',false);
        $filter['school_code']=$_GET['school_code'];
        $filter['school_cat']=$_GET['school_cat'];  
        $filter['class_code']=getParameter('class_code','str',false);
        $filter['grade']=getParameter('grade','int',false); 
        $filter['class_name']=getParameter('class_name','str',false); 
        $filter['class_flag']=$_GET['class_flag'];
        $filter['class_status']=getParameter('class_status','int',false);
        $filter['order']=getParameter('order','int',false);
        $order=$order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition=array();
        $condition['biz_class.is_delete']=0;
        if(!empty($filter['province']))   $condition['dict_schoollist.provice_id']=$filter['province'];
        if(!empty($filter['city']))   $condition['dict_schoollist.city_id']=$filter['city'];
        if(!empty($filter['district']))   $condition['dict_schoollist.district_id']=$filter['district'];
        if(!empty($filter['school_status']))   $condition['dict_schoollist.flag']=intval($filter['school_status']);
        if(!empty($filter['is_create_administartor']))   $condition['is_create_administartor']=intval($filter['is_create_administartor']); 
        if(!empty($filter['school_code']))   $condition['dict_schoollist.school_code']=array('like', '%' . $filter['school_code']. '%');
        if(!empty($filter['school_cat']))   $condition['dict_schoollist.school_category']=intval($filter['school_cat']);
        if(!empty($filter['class_code']))   $condition['biz_class.class_code']=array('like', '%' . $filter['class_code']. '%');
        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade'];
        if(!empty($filter['class_name']))   $condition['biz_class.name']=$filter['class_name'];
        if(!empty($filter['class_flag']))   $condition['biz_class.flag']=$filter['class_flag'];
        if(!empty($filter['class_status']))   $condition['biz_class.class_status']=$filter['class_status'];
        
        $data=$this->model->getAllClassData($condition,$order); 
        $str="年级,班级,班级代码,所属学校,班级状态,班级类型,创建教师,创建教师手机号\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($data as $v){
            $grade=iconv('utf-8','gbk', $v['grade']);
            $class_name=iconv('utf-8','gbk', $v['class_name']);  
            $class_code=sprintf("%s",iconv('utf-8','gbk', $v['class_code'])); 
            $school_name=iconv('utf-8','gbk', $v['school_name']);
            if($v['flag']==1){
                $class_flag='正常';
            }elseif($v['flag']==2){
                $class_flag='移交中';
            }else{
                $class_flag='停用';
            }
            $class_flag=iconv('utf-8','gbk', $class_flag); 
            if($v['class_status']==1){
                $class_status='校内班';
            }else{
                $class_status='个人班';
            }
            $class_status=iconv('utf-8','gbk', $class_status); 
            $teacher_name=iconv('utf-8','gbk', $v['teacher_name']);
            $telephone=$v['telephone'];
            $str.=$grade.",".$class_name.",".$class_code.",".$school_name.",".$class_flag.",".$class_status.",".$teacher_name.",".$telephone."\n";
        }
        $filename=date('Ymd').rand(0,1000).'Class'.'.csv';
        $csv=new CSV();
        //export disable
        //$csv->downloadFileCsv($filename,$str);
    }
    
    
    /*
     * 下载班级内导入学生的例子
     */
    public function downloadClassStudentDemo(){
        if (!session('?admin')) redirect(U('Login/login')); 
        
        $csv=new CSV();
        $file="Public/csv/admin/classStudentDeomo.csv";   
        $csv->downloadFile($file); 
    }
    
    
    /*
     * 导入班级内的学生视图
     */
    public function importClassStudentView(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        $class_id=getParameter('id','int');     
        $class_result=$this->model->getClassSchoolData($class_id);  
        if(empty($class_result)){
            $this->error('参数错误!');
        }
        $this->assign('class_id',$class_id);
        $this->display('class_iframe_student_import');
    }
    
    
    /*
     * 下载导入失败的数据
     */
    public function downloadImportStudentErrorData(){
        if (!session('?admin')) redirect(U('Login/login'));
          
        $student_name_arr=$_POST['student_name'];
        $parent_tel_arr=$_POST['parent_tel'];  
        
        $str="学生姓名,家长手机号\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($student_name_arr as $key=>$val){
            $student_name=iconv('utf-8','gbk', $val);
            $parent_tel=iconv('utf-8','gbk', $parent_tel_arr[$key]);      
            
            $str.=$student_name.",".$parent_tel."\n";
        } 
        $filename=date('Ymd').rand(0,1000).'班级内学生导入失败信息'.'.csv';
        $csv=new CSV();
        $csv->downloadFileCsv($filename,$str);
    }
    
    
    /*
     * 导入班级下的学生
     */
    public function importClassStudent(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        if(empty($_FILES)){ 
            $this->showjson(1001,'文件为空!');  
        } 
        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){
            $this->showjson(1002,'文件内容为空!');
        }
        $str_encode=A('Admin/student')->getStringEncode($result['result'][0][0]);
        $i_data=$result['result'];        
        $length=$result['length'];
         
         
        $student_model=D('Auth_student');
        $class_id=getParameter('id','int'); 
        $class_result=$this->model->getClassSchoolData($class_id);
        if(empty($class_result)){
            $this->showjson(1003,'班级参数错误!');
        }  
        
        $notice_array=array(); 
        $success_array=array();

        $class_result_info=$this->model->getClassAndGradeInfo($class_id);
                
        //如果学生存在就该条数据就跳过
        for($i=1;$i<$length;$i++){
            $studetn_data['student_name']=$this->encode_string($str_encode,$i_data[$i][0]);
            $studetn_data['parent_tel']=$this->encode_string($str_encode,$i_data[$i][1]);
            $parent_info = D('Auth_parent')->getParentInfoByTelephone($studetn_data['parent_tel']);
            
            $notice=$studetn_data; 
            $success=$studetn_data;  
            //判断手机号 
            if(!preg_match("/^1[34578]{1}\d{9}$/",$studetn_data['parent_tel'])){      
                $notice['notice_message']='手机号格式不正确!';
                $notice_array[]=$notice;
                continue;
            }
            $student_result=$student_model->getStudentParentTel($studetn_data['student_name'],$studetn_data['parent_tel']);
            if(empty($student_result)){ 
                $notice['notice_message']='学生信息不存在!';
                $notice_array[]=$notice;
                continue;
            }
            //查看该学生是否加入过该班级
            $class_student_con['biz_class_student.class_id']=$class_id;
            $class_student_con['biz_class_student.student_id']=$student_result['id'];
            $class_result_copy=$this->model->getClassStudentCount($class_student_con);
            if(!empty($class_result_copy)){
                $notice['notice_message']='该学生已在该班级内!';
                $notice_array[]=$notice;
                continue;
            }
            $data['class_id']=$class_id;
            $data['student_id']=$student_result['id'];
            $data['create_at']=time();
            $data['status']=2;
            $this->model->startTrans();
            if(!$this->model->addClassStudentData($data)){
                $this->model->rollback();
                $notice['notice_message']='数据入库失败1!';
                $notice_array[]=$notice;
                continue;
            }
            /*if(!$this->model->updateClassStudentCount($class_id,true)){
                $this->model->rollback();
                $notice['notice_message']='数据入库失败2!';
                $notice_array[]=$notice;
                continue;
            }*/

            if ($class_result['class_status'] == 2) {
                $parameters = array(
                    'msg' => array(
                        $studetn_data['student_name'],
                        $class_result['grade'],
                        $class_result['class_name'],
                    ),
                    'url' => array( 'type' => 1, 'data'=>array($class_id) )
                );

                $teacher_id_send = $this->model->getTeacherId($class_result['class_id']);

                A('Home/Message')->addPushUserMessage('STUDENT_ADD_PERSON_CLASS', 2, $teacher_id_send['teacher_id'] , $parameters);
            }

            $parameters = array(
                'msg' => array(
                    $studetn_data['student_name'],
                    C('ADMIN_ROOT'),
                    $class_result_info['grade'],
                    $class_result_info['name'],
                ),
                'url' => array( 'type' => 0,)
            );

            A('Home/Message')->addPushUserMessage('CLASSADDSTUDENT', 4,$parent_info['id'] , $parameters);


            //给学生发送

            $parametersstu = array(
                'msg' => array(
                    C('SCHOOL_ROOT'),
                    $class_result_info['grade'],
                    $class_result_info['name'],
                ),
                'url' => array( 'type' => 0,)
            );

            A('Home/Message')->addPushUserMessage('CLASSADDSENDSTUDENT', 3,$student_result['id'] , $parametersstu);

            $this->model->commit(); 
            $success_array[]=$success; 
             
        }




        $big_data=array();
        $big_data['success']=$success_array;
        $big_data['failed']=$notice_array;
        $this->showjson(200,'success',$big_data);
    }
     
        
     
    
    /*
     * 批量导出某班级下的学生
     */
    public function exportedClassStudent(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        set_time_limit(0);
        if(empty($_POST)){
            $this->error('参数错误');
        }else{  
            $condition_arr=I('hid'); 
            $condition['auth_student.id']=array('in',$condition_arr);
            $data=$this->model->getClassStudentDataAll($condition); 
            $str="学生姓名,性别,家长手机号,账号状态\n";
            $str=iconv('utf-8','gbk', $str);
            foreach($data as $v){
                $student_name=iconv('utf-8','gbk', $v['student_name']);
                $sex=iconv('utf-8','gbk', $v['sex']); 
                $parent_tel=iconv('utf-8','gbk', $v['parent_tel']);   
                if($v['flag']==1){
                    $account_status='正常';
                }else{
                    $account_status='停用';
                }
                $account_status=iconv('utf-8','gbk', $account_status); 

                $str.=$student_name.",".$sex.",".$parent_tel.",".$account_status."\n";
            } 
            $filename=date('Ymd').rand(0,1000).'ClassStudent'.'.csv';
            $csv=new CSV();
            //export disable
            //$csv->downloadFileCsv($filename,$str);
        }
    }
    
    
    /*
     * 导出某班级下的所有学生
     */
    public function exportedClassStudentAll(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        set_time_limit(0);
        $filter['class_id']=getParameter('class_id','int');        
        $filter['student_name']=getParameter('student_name','str',false);
        $filter['parent_tel']=getParameter('parent_tel','str',false);
        $filter['status']=$_GET['status']; 
        $filter['order']=getParameter('order','int',false); 
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition=array(); 
        $condition['auth_student.flag']=array('neq',-1); 
        if(!empty($filter['class_id']))   $condition['biz_class.id']=$filter['class_id'];
        if(!empty($filter['student_name']))   $condition['student_name']=array('like', '%' . $filter['student_name']. '%');
        if(!empty($filter['parent_tel']))   $condition['parent_tel']=array('like', '%' . $filter['parent_tel']. '%');
        if($filter['status']!='')   $condition['auth_student.flag']=intval($filter['status']);
        
        $data=$this->model->getClassStudentDataAll($condition); 
        $str="学生姓名,性别,家长手机号,账号状态\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($data as $v){
            $student_name=iconv('utf-8','gbk', $v['student_name']);
            $sex=iconv('utf-8','gbk', $v['sex']); 
            $parent_tel=iconv('utf-8','gbk', $v['parent_tel']);   
            if($v['flag']==1){
                $account_status='正常';
            }else{
                $account_status='停用';
            }
            $account_status=iconv('utf-8','gbk', $account_status); 

            $str.=$student_name.",".$sex.",".$parent_tel.",".$account_status."\n";
        } 
        $filename=date('Ymd').rand(0,1000).'ClassStudent'.'.csv';
        $csv=new CSV();
        //export disable
        //$csv->downloadFileCsv($filename,$str);
    }
    
    
    /*
     * 下载班级内导入教师模板
     */
    public function downloadClassTeacherDemo(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        $csv=new CSV();
        $class_type=getParameter('type','int'); 
        if($class_type==SCHOOL_CLASS){
            $file="Public/csv/admin/schoolClassTeacherDemo.csv";
        }else{
            $file="Public/csv/admin/personalClassTeacherDemo.csv";
        } 
        $csv->downloadFile($file); 
    }
    
    /*
     * 下载导入班级内教师失败的数据
     */
    public function downloadImportTeacherErrorData(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        $class_type=getParameter('type','int');
        $teacher_name_arr=$_POST['teacher_name'];
        $telephone_arr=$_POST['telephone'];  
        $course_arr=$_POST['course'];  
        if($class_type==SCHOOL_CLASS){
            $str="教师姓名,教师手机号,任教学科\n";
        }else{
            $str="教师姓名,教师手机号\n";
        }
        $str=iconv('utf-8','gbk', $str);
        foreach($teacher_name_arr as $key=>$val){
            $teacher_name=iconv('utf-8','gbk', $val);
            $telephone=iconv('utf-8','gbk', $telephone_arr[$key]);      
            $course=iconv('utf-8','gbk', $course_arr[$key]);
            if($class_type==SCHOOL_CLASS){
                $str.=$teacher_name.",".$telephone.",".$course."\n";
            }else{
                $str.=$teacher_name.",".$telephone."\n";
            }
        } 
            $filename=date('Ymd').rand(0,1000).'班级内教师导入失败信息'.'.csv';
            $csv=new CSV();
            $csv->downloadFileCsv($filename,$str);
    }
    
    
    /*
     * 班级内导入教师视图
     */
    public function importClassTeacherView(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        $class_id=getParameter('id','int');     
        $class_result=$this->model->getClassSchoolData($class_id);
        if(empty($class_result)){
            $this->error('参数错误!');
        }
        $this->assign('class_id',$class_id);
        $this->assign('class_status',$class_result['class_status']);
        $this->display('class_iframe_teacher_import');
    }
    
    
    /*
     * 班级内导入教师
     */
    public function importClassTeacher(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        if(empty($_FILES)){ 
            $this->showjson(1001,'文件为空!'); 
        }
        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){
            $this->showjson(1002,'文件内容为空!');
        }
        $str_encode=A('Admin/Student')->getStringEncode($result['result'][0][0]);
        $i_data=$result['result'];        
        $length=$result['length'];
          
        $course_model=D('Dict_course'); 
        $teacher_model=D('Auth_teacher');
        
        $class_id=getParameter('id','int'); 
        $class_result=$this->model->getClassSchoolData($class_id);     //class_status
        $class_info = $this->model->getClassAndGradeInfo($class_id);

        if(empty($class_result)){
            $this->showjson(1003,'班级参数错误!');
        }
        
        $notice_array=array();
        $success_array=array(); 
        $tel_reg="/^1[34578]{1}\d{9}$/";
        for($i=1;$i<$length;$i++){
            $teacher_data['name']=$this->encode_string($str_encode,$i_data[$i][0]);
            $teacher_data['telephone']=$this->encode_string($str_encode,$i_data[$i][1]);
            $teacher_data['course']=$this->encode_string($str_encode,$i_data[$i][2]);
              
            $notice=$teacher_data; 
            $success_data=$teacher_data;  
            //判断手机号 
            if(!preg_match($tel_reg,$teacher_data['telephone'])){      
                $notice['notice_message']='手机号格式不正确!';
                $notice_array[]=$notice;
                continue;
            }
            $teacher_result=$teacher_model->getTeacherByTel($teacher_data['telephone']);
            if(empty($teacher_result)){
                $notice['notice_message']='教师信息不存在!';
                $notice_array[]=$notice;
                continue;
            }
            $teacher_con['name']=$teacher_data['name'];
            $teacher_con['telephone']=$teacher_data['telephone'];
            $teacher_result=$teacher_model->getTeacherInfo($teacher_con);
            if(empty($teacher_result)){
                $notice['notice_message']='教师信息填写错误!';
                $notice_array[]=$notice;
                continue;
            }  
            $class_teacher_data['class_id']=$class_id;
            $class_teacher_data['teacher_id']=$teacher_result['id']; 
            $class_teacher_data['create_at']=time();
            
            $this->model->startTrans();
            $teacher_course_con['class_id']=$class_id;
            $teacher_course_con['teacher_id']=$teacher_result['id'];
            $error=0;
            if($class_result['class_status']==SCHOOL_CLASS){ 
                $all_course=explode('.',$teacher_data['course']);
                for($j=0;$j<count($all_course);$j++){
                    $course_result=$course_model->getCourseData($all_course[$j]);
                    if(empty($course_result)){  
                        $this->model->rollback();
                        $notice['notice_message']='学科不存在!';
                        $notice_array[]=$notice;
                        $error=1;
                        break;
                    }
                    $class_teacher_data['course_id']=$course_result['id'];
                    //查询该教师是否任教该班级学科
                    
                    $teacher_course_con['course_id']=$course_result['id'];
                    $teacher_course_result=$teacher_model->getTeacherClassTeachesInfo($teacher_course_con);
                    if(!empty($teacher_course_result)){
                        $this->model->rollback();
                        $notice['notice_message']='该教师已任教该班级该学科!';
                        $notice_array[]=$notice;
                        $error=1;
                        break;
                    }
                    if(!$this->model->addClassTeacher($class_teacher_data)){
                        $this->model->rollback();
                        $notice['notice_message']='数据入库失败!';
                        $notice_array[]=$notice;
                        $error=1;
                        break;
                    }
                } 
            }else{ 
                $teacher_course_result=$teacher_model->getTeacherClassTeachesInfo($teacher_course_con);
                if(!empty($teacher_course_result)){
                    $this->model->rollback();
                    $notice['notice_message']='该教师已任教该班级!';
                    $notice_array[]=$notice;
                    continue;
                }
                if(!$this->model->addClassTeacher($class_teacher_data)){
                    $this->model->rollback();
                    $notice['notice_message']='数据入库失败!';
                    $notice_array[]=$notice;
                    continue;
                }
            }
            if($error==1){
                $this->model->rollback();
                continue;
            }

            //消息推送
            $parameters = array(
                'msg' => array(
                    C('SCHOOL_ROOT'),
                    $class_info['grade'],
                    $class_info['name'],
                    $teacher_data['course']
                ),
                'url' => array( 'type' => 0, 'data' => array( $class_id ) )
            );

            A('Home/Message')->addPushUserMessage('ADD_SCHOOL_CLASS', 2, $teacher_result['id'] , $parameters);

            $this->model->commit();

            $success_array[]=$success_data; 
        }
        $big_data=array();
        $big_data['success']=$success_array;
        $big_data['failed']=$notice_array;
        $this->showjson(200,'success',$big_data);
    }
    
    
    /*
     * 批量导出某个班下的教师
     */
    public function exportedClassTeacher(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        set_time_limit(0);
        if(empty($_POST)){
            $this->error('参数错误');
        }else{ 
            $condition_arr=I('hid'); 
            $condition['auth_teacher.id']=array('in',$condition_arr);
            $condition['biz_class_teacher.is_handler']=0;
            
            $data=$this->model->getClassTeacher($condition); 
            $str="教师姓名,教师手机号,任教学科,账号状态\n";
            $str=iconv('utf-8','gbk', $str);
            foreach($data as $v){
                $teacher_name=iconv('utf-8','gbk', $v['teacher_name']);
                $telephone=iconv('utf-8','gbk', $v['telephone']);   
                $course_name=iconv('utf-8','gbk', $v['course_name']);  
                if($v['flag']==1){
                    $account_status='正常';
                }else{
                    $account_status='停用';
                }
                $account_status=iconv('utf-8','gbk', $account_status);  
                $str.=$teacher_name.",".$telephone.",".$course_name.",".$account_status."\n";
            } 
            $filename=date('Ymd').rand(0,1000).'ClassTeacher'.'.csv';
            $csv=new CSV();
            //export disable
            //$csv->downloadFileCsv($filename,$str);
        }
    }
    
    
    /*
     * 导出某个班级下的所有教师
     */
    public function exportedClassTeacherAll(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        set_time_limit(0); 
        $filter['class_id']=getParameter('class_id','int');
        $condition['biz_class.id']=$filter['class_id']; 
        $condition['biz_class_teacher.is_handler']=0;
        
        $data=$this->model->getClassTeacher($condition); 
        $str="教师姓名,教师手机号,任教学科,账号状态\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($data as $v){
            $teacher_name=iconv('utf-8','gbk', $v['teacher_name']);
            $telephone=iconv('utf-8','gbk', $v['telephone']);   
            $course_name=iconv('utf-8','gbk', $v['course_name']);  
            if($v['flag']==1){
                $account_status='正常';
            }else{
                $account_status='停用';
            }
            $account_status=iconv('utf-8','gbk', $account_status);  
            $str.=$teacher_name.",".$telephone.",".$course_name.",".$account_status."\n";
        } 
        $filename=date('Ymd').rand(0,1000).'ClassTeacher'.'.csv';
        $csv=new CSV();
        //export disable
        //$csv->downloadFileCsv($filename,$str);
    }
    
    
    /*
     * 批量导出某个班下的家长
     */
    public function exportedClassParent(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        set_time_limit(0);  
        if(empty($_POST)){
            $this->error('参数错误');
        }else{ 
            $condition_arr=I('hid'); 
            $condition['auth_parent.id']=array('in',$condition_arr);    
            $data=$this->model->getClassParentDataAll($condition);
            
            $str="家长姓名,性别,学生姓名,权限类型,家长手机号,账号状态\n";
            $str=iconv('utf-8','gbk', $str);
            foreach($data as $v){
                $parent_name=iconv('utf-8','gbk', $v['parent_name']);
                $sex=iconv('utf-8','gbk', $v['sex']);   
                $student_name=iconv('utf-8','gbk', $v['student_name']);  
                if($v['permissions_status']==1){
                    $privilege='vip权限';
                }else{
                    $privilege='普通权限';
                }
                $privilege=iconv('utf-8','gbk',$privilege);  
                $parent_tel=iconv('utf-8','gbk', $v['parent_tel']); 
                if($v['flag']==1){
                    $account_status='正常';
                }else{
                    $account_status='停用';
                }
                $account_status=iconv('utf-8','gbk', $account_status);  
                $str.=$parent_name.",".$sex.",".$student_name.",".$privilege.",".$parent_tel.",".$account_status."\n";
            } 
            $filename=date('Ymd').rand(0,1000).'ClassParent'.'.csv';
            $csv=new CSV();
            //export disable
            //$csv->downloadFileCsv($filename,$str);
        }
    }
    
    
    /*
     * 导出某个班下的所有家长
     */
    public function exportedClassParentAll(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        set_time_limit(0); 
        $filter['class_id']=getParameter('class_id','int');
        $filter['parent_name']=getParameter('parent_name','str',false);
        $filter['parent_tel']=getParameter('parent_tel','str',false);
        $filter['status']=getParameter('status','int',false);
        $filter['student_name']=getParameter('student_name','str',false);
        $filter['privilege_type']=$_GET['privilege_type'];
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $condition=array();  
        $having='';
        if(!empty($filter['class_id']))   $condition['biz_class.id']=$filter['class_id'];
        if(!empty($filter['parent_name']))   $condition['auth_parent.parent_name']=array('like', '%' . $filter['parent_name']. '%');
        if(!empty($filter['parent_tel']))   $condition['auth_student_parent_contact.parent_tel']=array('like', '%' . $filter['parent_tel']. '%');
        if(!empty($filter['status']))   $condition['auth_parent.flag']=$filter['status'];
        if(!empty($filter['student_name']))   $condition['auth_student.student_name']=array('like', '%' . $filter['student_name']. '%');
        if(!empty($filter['privilege_type']))   $having=$filter['privilege_type']; 
        $data=$this->model->getClassParentDataAll($condition,$order,$having);   
        
        $str="家长姓名,性别,学生姓名,权限类型,家长手机号,账号状态\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($data as $v){
            $parent_name=iconv('utf-8','gbk', $v['parent_name']);
            $sex=iconv('utf-8','gbk', $v['sex']);   
            $student_name=iconv('utf-8','gbk', $v['student_name']);  
            if($v['permissions_status']==1){
                $privilege='vip权限';
            }else{
                $privilege='普通权限';
            }
            $privilege=iconv('utf-8','gbk',$privilege);  
            $parent_tel=iconv('utf-8','gbk', $v['parent_tel']); 
            if($v['flag']==1){
                $account_status='正常';
            }else{
                $account_status='停用';
            }
            $account_status=iconv('utf-8','gbk', $account_status);  
            $str.=$parent_name.",".$sex.",".$student_name.",".$privilege.",".$parent_tel.",".$account_status."\n";
        } 
        $filename=date('Ymd').rand(0,1000).'ClassParent'.'.csv';
        $csv=new CSV();
        //export disable
        //$csv->downloadFileCsv($filename,$str);
    }
}