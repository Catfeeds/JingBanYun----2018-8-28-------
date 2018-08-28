<?php
namespace School\Controller;
use Think\Controller;
use Common\Common\CSV;
  
define('ENABLE_TEACHER_STATUS',1);
define('CHECKED_PARENT_STATUS',1);
define('DISABLE_TEACHER_STATUS',0);
define('SCHOOL_ID',session('school.school_id'));

class ParentController extends Controller
{ 

    public $model;
    public $page_size=20; 
            
    public function __construct() {
        parent::__construct();  
        $this->model=D('Auth_parent');
        $this->assign('oss_path','http://jbyoss.oss-cn-beijing.aliyuncs.com/');
    }
             
    //Home@Admin/test跨目录调用
    /*
     * 家长列表
     */
    public function parentList(){ 
        if (!session('?school')) redirect(U('Login/login')); 
        A('School/SchoolAdmin')->check_permissions();
        
        $grade_model=D('Dict_grade');
        $class_model=D('Biz_class');
        $filter['parent_name']=trim(getParameter('name','str',false));
        $filter['parent_tel']=trim(getParameter('telephone','str',false));
        $filter['privilege_type']=getParameter('privilege_type','str',false);
        $filter['student_name']=trim(getParameter('student_name','str',false)); 
        $filter['grade']=getParameter('grade','str',false);
        $filter['class']=getParameter('class','str',false); 
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];         
        $order?$order='asc':$order='desc';
        
        $condition['auth_student.flag']=array('neq',-1);
        $condition['auth_student.school_id']=SCHOOL_ID;
        if(!empty($filter['parent_name']))   $condition['auth_parent.parent_name']=array('like', '%' . $filter['parent_name']. '%');
        if(!empty($filter['parent_tel']))   $condition['auth_parent.telephone']=array('like', '%' . $filter['parent_tel']. '%');
        if(!empty($filter['privilege_type']))   $having='permissions_status='.$filter['privilege_type'];
        if(!empty($filter['student_name']))   $condition['auth_student.student_name']=array('like', '%' . $filter['student_name']. '%'); 
        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade'];
        if(!empty($filter['class']))   $condition['biz_class.id']=$filter['class'];
        
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        } 
         
        if(!empty($filter['grade'])) $class_list=$class_model->getClassDataBySchool(SCHOOL_ID,$filter['grade']);
        $result=$this->model->getParentData($condition,$order,$having);
        $grade_result=$grade_model->getGradeList(true);

        $this->assign('condition_str',$condition_string);
        $this->assign('condition_string',$condition_string);
        $this->assign('parent_name',$filter['parent_name']);
        $this->assign('telephone',$filter['parent_tel']);
        $this->assign('privilege_type',$filter['privilege_type']);
        $this->assign('student_name',$filter['student_name']);
        $this->assign('grade',$filter['grade']);
        $this->assign('class',$filter['class']);
        $this->assign('class_list',$class_list);
        $this->assign('order',$order);      
         
             
        $this->assign('grade_list',$grade_result);  
        $this->assign('list',$result['data']);
        $this->assign('page',$result['page']);
        
        $this->display();
    }
       
    
    /*
     * 家长详情
     */
     public function parentDetail(){
         if (!session('?school')) redirect(U('Login/login'));
          A('School/SchoolAdmin')->check_permissions();
          
         $id=getParameter('id','int');
         $parent_data=$this->model->getParentSimpleData($id,SCHOOL_ID);             
         $student_data=$this->model->getParentStudentData($id,SCHOOL_ID);
         $this->assign('parent_data',$parent_data); 
         $this->assign('student_list',$student_data);   
         $this->display();
     }
     
     
     
     /*
      * 批量导出家长
      */
     public function exportedParent(){
         if (!session('?school')) redirect(U('Login/login'));
         A('School/SchoolAdmin')->check_permissions();
         
        set_time_limit(0); 
        if(empty($_POST)){
            $this->error('参数错误');
        }else{  
            $condition_arr=I('hid'); 
            $condition['auth_parent.id']=array('in',$condition_arr);
            $condition['auth_student.school_id']=SCHOOL_ID;
            $data=$this->model->getParentDataAll($condition);   
 
            $str="家长姓名,性别,家长手机号,学生姓名,权限类型,账号状态\n";
            $str=iconv('utf-8','gbk', $str);
            foreach($data as $val){
                    $parent_name=iconv('utf-8','gbk', $val['parent_name']); 
                    $sex=iconv('utf-8','gbk', $val['sex']);
                    $parent_tel=$val['telephone'];
                    $student_name=iconv('utf-8','gbk', $val['student_name']); 
                    if($val['permissions_status']==1){
                        $permissions=iconv('utf-8','gbk','个人vip');
                    }else{
                        $permissions=iconv('utf-8','gbk','普通权限');
                    }
                    if($val['flag']==1){
                        $status=iconv('utf-8','gbk','正常');
                    }else{
                        $status=iconv('utf-8','gbk','停用');
                    } 
                    $str.=$parent_name.",".$sex.",".$parent_tel.",".$student_name.",".$permissions.",".$status."\n";
                }
                $filename=date('Ymd').rand(0,1000).'parent'.'.csv';
                $csv=new CSV();
                //export disable
                $csv->downloadFileCsv($filename,$str);
        }
     }
     
     
     /*
      * 导出全部家长
      */
     public function exportedParentAll(){
         if (!session('?school')) redirect(U('Login/login'));
         A('School/SchoolAdmin')->check_permissions();
         
        $filter['parent_name']=getParameter('name','str',false);
        $filter['parent_tel']=getParameter('telephone','str',false);
        $filter['privilege_type']=getParameter('privilege_type','str',false);
        $filter['student_name']=getParameter('student_name','str',false); 
        $filter['grade']=getParameter('grade','str',false);
        $filter['class']=getParameter('class','str',false); 
        $filter['order']=getParameter('order','int',false); 
        $order=$filter['order'];         
        $order?$order='asc':$order='desc';
        
        $having='';
        $condition['auth_student.school_id']=SCHOOL_ID;
        if(!empty($filter['parent_name']))   $condition['auth_parent.parent_name']=array('like', '%' . $filter['parent_name']. '%');
        if(!empty($filter['parent_tel']))   $condition['auth_parent.parent_tel']=array('like', '%' . $filter['parent_tel']. '%');
        if(!empty($filter['privilege_type']))   $having=$filter['privilege_type'];
        if(!empty($filter['student_name']))   $condition['auth_student.student_name']=array('like', '%' . $filter['student_name']. '%'); 
        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade'];
        if(!empty($filter['class']))   $condition['biz_class.id']=$filter['class'];
         
        $data=$this->model->getParentDataAll($condition,$order,$having);
        $str="家长姓名,性别,家长手机号,学生姓名,权限类型,账号状态\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($data as $val){
                $parent_name=iconv('utf-8','gbk', $val['parent_name']); 
                $sex=iconv('utf-8','gbk', $val['sex']);
                $parent_tel=$val['telephone'];
                $student_name=iconv('utf-8','gbk', $val['student_name']); 
                if($val['permissions_status']==1){
                    $permissions=iconv('utf-8','gbk','个人vip');
                }else{
                    $permissions=iconv('utf-8','gbk','普通权限');
                }
                if($val['flag']==1){
                    $status=iconv('utf-8','gbk','正常');
                }else{
                    $status=iconv('utf-8','gbk','停用');
                } 
                $str.=$parent_name.",".$sex.",".$parent_tel.",".$student_name.",".$permissions.",".$status."\n";
            }
            $filename=date('Ymd').rand(0,1000).'parent'.'.csv';
            $csv=new CSV();
            //export disable
            $csv->downloadFileCsv($filename,$str);
     }
}