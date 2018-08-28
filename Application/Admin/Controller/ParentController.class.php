<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Common\CSV;
 
define('ENABLE_TEACHER_STATUS',1);
define('DISABLE_TEACHER_STATUS',0);
define('PERSONAL_VIP',4);
define('COMMON_PRIVILEGE',2);

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
        if (!session('?admin')) redirect(U('Login/login')); 
        
        $grade_model=D('Dict_grade');
        $filter['parent_name']=getParameter('name','str',false);
        $filter['parent_tel']=getParameter('telephone','str',false);
        $filter['privilege_type']=getParameter('privilege_type','str',false);
        $filter['student_name']=getParameter('student_name','str',false);
        $filter['school_name']=getParameter('school_name','str',false);
        $filter['school_code']=getParameter('school_code','str',false);
        $filter['grade']=getParameter('grade','str',false);
        $filter['class']=getParameter('class','str',false);
        $filter['account_status']=$_GET['status'];
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $having='';
        if(!empty($filter['parent_name']))   $condition['auth_parent.parent_name']=array('like', '%' . $filter['parent_name']. '%');
        if(!empty($filter['parent_tel']))   $condition['auth_parent.telephone']=array('like', '%' . $filter['parent_tel']. '%');
        if(!empty($filter['privilege_type']))   $having='permissions_status='.$filter['privilege_type'];
        if(!empty($filter['student_name']))   $condition['auth_student.student_name']=array('like', '%' . $filter['student_name']. '%');
        if(!empty($filter['school_name']))   $condition['dict_schoollist.school_name']=array('like', '%' . $filter['school_name']. '%');
        if(!empty($filter['school_code']))   $condition['dict_schoollist.school_code']=array('like', '%' . $filter['school_code']. '%');
        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade'];
        if(!empty($filter['class']))   $condition['biz_class.id']=$filter['class'];
        if($filter['account_status']!='')   $condition['auth_parent.flag']=intval($filter['account_status']);
          
        $condition_string='';
        foreach($filter as $key=>$val){
            $condition_string.='&'.$key.'='.$val;
        }
        
        $grade_result=$grade_model->getGradeList(true);
        $result=$this->model->getParentData($condition,$order,$having);
        
        
        $this->assign('condition_str',$condition_string);
        $this->assign('parent_name',$filter['parent_name']);
        $this->assign('telephone',$filter['parent_tel']);
        $this->assign('privilege_type',$filter['privilege_type']);
        $this->assign('student_name',$filter['student_name']);                      
        $this->assign('school_name',$filter['school_name']);
        $this->assign('school_code',$filter['school_code']); 
        $this->assign('grade',$filter['grade']);
        $this->assign('class',$filter['class']);
        $this->assign('account_status',$filter['account_status']); 
        $this->assign('order',$order); 
        
        $this->assign('grade_list',$grade_result);
        $this->assign('list',$result['data']);
        $this->assign('page',$result['page']);
        
        $this->display();
    }
     
    
    
    /*
     * 启用或禁用账户状态
     */
    public function updateParentStatus(){
        $id=getParameter('id','int');
        $result=$this->model->getParentInfo($id);       
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{
            if($result['flag']){
                $status=$this->model->updateEnableStatus($id,DISABLE_TEACHER_STATUS);
            }else{
                $status=$this->model->updateEnableStatus($id,ENABLE_TEACHER_STATUS);
            }
            if($status){
                $this->showjson(200);
            }else{
                $this->showjson(402,COMMON_FAILED_MESSAGE);
            }
        }
    }
    
    
    /*
     * 移除家长
     */
    public function removeStudentParentContact(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
         
        $student_model=D('Auth_student');
        $student_id=getParameter('student_id','int');
        $parent_id=getParameter('parent_id','int');
        $result=$student_model->getStudentInfo($student_id);
        $parent_result=$this->model->getParentSimpleData($parent_id);
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{
            if(empty($parent_result)){
                $this->showjson(402,ID_NOT_EXISTS_MESSAGE);
            }
            $status=$student_model->deleteStudentParentContactData($student_id,$parent_id);
            if($status){
                $this->showjson(200);
            }else{
                $this->showjson(403,COMMON_FAILED_MESSAGE);
            }
        }
    }
     
    
    
    /*
     * 家长解除单个学生关系
     */
    public function unbindOneStudentRelation(){ 
        $id=getParameter('id','int'); 
        $student_id=getParameter('student_id','int'); 
        $result=$this->model->getParentStudentRelation($id,$student_id);
        if(empty($result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }else{
            if($this->model->deleteOneStudentRelation($id,$student_id)){
                $this->showjson(200);
            }else{
                $this->showjson(401,COMMON_FAILED_MESSAGE);
            }
        }
    }
    
    
    /*
     * 家长详情
     */
     public function parentDetail(){
         if (!session('?admin')) redirect(U('Login/login'));
         
         $id=getParameter('id','int');
         $parent_data=$this->model->getParentSimpleData($id);
         $student_data=$this->model->getParentStudentData($id);     
         $this->assign('parent_data',$parent_data);     
         $this->assign('student_list',$student_data);
         $this->display();
     }
     
     
     /*
      * 修改家长信息
      */
     public function parentModify(){
         if (!session('?admin')) redirect(U('Login/login'));
         
         if(!empty($_POST)){
             $parent_id=getParameter('parent_id','str');
             $data['parent_name']=getParameter('parent_name','str');
             $data['flag']=getParameter('status','int');
             if($data['flag']!=0 && $data['flag']!=1){
                 $this->error('参数错误!');
             }     
             $this->model->updateInfoById($data,$parent_id);
             $this->redirect(U('Parent/parentModify'),array('id'=>$parent_id));
         }else{
            $id=getParameter('id','int');
            $parent_data=$this->model->getParentSimpleData($id);   
            $student_data=$this->model->getParentStudentData($id);      
            $this->assign('parent_data',$parent_data);                 
            $this->assign('student_list',$student_data);  
            $this->display();
         }
     }
     
     
     /*
     * ajax获得一条学校数据
     */
    public function getParentInfo(){
        $parent_id=getParameter('id','int',false);
        $result=$this->model->getParentSimpleData($parent_id);        
        $this->showjson(200,'',$result);
    }
    
    
    /*
     * 给家长开通VIp
     */
    function giveParentVip(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $id=getParameter('id','int');
        $vip_type=getParameter('vip_type','int');
        $vip_use_type=getParameter('use_type','int');
        $start_time=getParameter('start_time','str');
        $end_time=getParameter('end_time','str');  
        $result=$this->model->getParentSimpleData($id);   
        if(empty($result)){        
            $this->showjson('401',ID_NOT_EXISTS_MESSAGE);
        }else{    
            $start_time=strtotime($start_time);
            $end_time = date('y-m-d 23:59:00',strtotime($end_time));
            $end_time= strtotime($end_time);
            /*if($start_time<time()){
                $this->showjson('402',COMMON_FAILED_MESSAGE);
            }*/
            if($start_time>$end_time){
                $this->showjson('403','开始时间不能晚于结束时间!');
            } 
            //试用和试用类型
            $trial_use=1;
            $use=2;
            if($vip_use_type!=$trial_use && $vip_use_type!=$use){
                $this->showjson('404','未知的权限类型!');
            }   
            if($vip_type==2){
                $data['auth_id']=PERSONAL_VIP;
                $data['user_id']=$id;
                $data['role_id']=ROLE_PARENT;
                $data['auth_start_time']=$start_time;
                $data['auth_end_time']=$end_time;
                $data['timetype']=$vip_use_type;
            }elseif($vip_type==1){
                $data['auth_id']=COMMON_PRIVILEGE;
                $data['user_id']=$id;
                $data['role_id']=ROLE_PARENT;
                $data['auth_start_time']='';
                $data['auth_end_time']='';
                $data['timetype']=$vip_use_type;
            }else{
                $this->showjson('405',COMMON_FAILED_MESSAGE);
            } 
            $auth_data=$this->model->getParentPrivilegeInfo($id);
            if(!empty($auth_data)){
                if(!$this->model->updateParentPrivilege($data,$id)){
                    $this->showjson('406',COMMON_FAILED_MESSAGE);
                }
            }else{
                if(!$this->model->addParentPrivilege($data)){
                    $this->showjson('407',COMMON_FAILED_MESSAGE);
                }
            } 
            $this->showjson(200);
        }
    }

    public function deleteParentVip() {
        $id=getParameter('id','int');
        $role_id=getParameter('role_id','int');
        $data['user_id'] = $id;
        $data['role_id'] = $role_id;
        $data['auth_id'] = 4;
        $res = M('account_user_and_auth')->where($data)->delete();

        if ( $res ) {
            $info['status'] = 200;
            $this->ajaxReturn($info);
        } else {
            $info['status'] = 400;
            $this->ajaxReturn($info);
        }
    }
    
    /*
     * 批量导出全部家长
     */
    public function exportedParent(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        set_time_limit(0); 
        if(empty($_POST)){
            $this->error('参数错误');
        }else{  
            $condition_arr=I('hid'); 
            $condition['auth_parent.id']=array('in',$condition_arr);
            $data=$this->model->getParentDataAll($condition);   
            
            $str="家长姓名,注册时间,性别,学生姓名,学生所属学校,权限类型,家长手机号,账号状态\n";
            $str=iconv('utf-8','gbk', $str);
            foreach($data as $v){
                $parent_name=iconv('utf-8','gbk', $v['parent_name']);
                $sex=iconv('utf-8','gbk', $v['sex']);
                $regTime = date("Y-m-d", $v['create_at']);
                $student_name=iconv('utf-8','gbk', $v['student_name']);  
                $school_name=iconv('utf-8','gbk', $v['school_name']);   
                if($v['permissions_status']==1){
                    $permissions_status='团体VIP';
                }else{
                    $permissions_status='普通权限';
                }
                $permissions_status=iconv('utf-8','gbk', $permissions_status);  
                if($v['flag']==1){
                    $account_status='正常';
                }else{
                    $account_status='停用';
                }
                $parent_tel=$v['telephone'];  
                $account_status=iconv('utf-8','gbk', $account_status);

                $str.=$parent_name.','.$regTime.",".$sex.",".$student_name.",".$school_name.",".$permissions_status.",".$parent_tel.",".$account_status."\n";
            } 
            $filename=date('Ymd').rand(0,1000).'Parent'.'.csv';
            $csv=new CSV();
            //export disable
            //$csv->downloadFileCsv($filename,$str);
        }
    }
    
    
    
    /*
     * 批量导出全部家长
     */
    public function exportedParentAll(){
        if (!session('?admin')) redirect(U('Login/login'));
        
        $filter['parent_name']=getParameter('name','str',false);
        $filter['parent_tel']=getParameter('telephone','str',false);
        $filter['privilege_type']=getParameter('privilege_type','str',false);
        $filter['student_name']=getParameter('student_name','str',false);
        $filter['school_name']=getParameter('school_name','str',false);
        $filter['school_code']=getParameter('school_code','str',false);
        $filter['grade']=getParameter('grade','str',false);
        $filter['class']=getParameter('class','str',false);
        $filter['account_status']=getParameter('status','str',false);
        $filter['order']=getParameter('order','int',false);
        $order=$filter['order'];
        $order?$order='asc':$order='desc';
        
        $having='';
        if(!empty($filter['parent_name']))   $condition['auth_parent.parent_name']=array('like', '%' . $filter['parent_name']. '%');
        if(!empty($filter['parent_tel']))   $condition['auth_parent.telephone']=array('like', '%' . $filter['parent_tel']. '%');
        if(!empty($filter['privilege_type']))   $having=$filter['privilege_type'];
        if(!empty($filter['student_name']))   $condition['auth_student.student_name']=array('like', '%' . $filter['student_name']. '%');
        if(!empty($filter['school_name']))   $condition['dict_schoollist.school_name']=array('like', '%' . $filter['school_name']. '%');
        if(!empty($filter['school_code']))   $condition['dict_schoollist.school_code']=array('like', '%' . $filter['school_code']. '%');
        if(!empty($filter['grade']))   $condition['dict_grade.id']=$filter['grade'];
        if(!empty($filter['class']))   $condition['biz_class.id']=$filter['class'];
        if(!empty($filter['account_status']))   $condition['auth_parent.flag']=array('like', '%' . $filter['account_status']. '%');
        
        $data=$this->model->getParentDataAll($condition,$order,$having);

        $str="家长姓名,注册时间,性别,学生姓名,学生所属学校,权限类型,家长手机号,账号状态\n";
        $str=iconv('utf-8','gbk', $str);
        foreach($data as $v){
            $parent_name=iconv('utf-8','gbk', $v['parent_name']);
            $sex=iconv('utf-8','gbk', $v['sex']);
            $regTime = date("Y-m-d", $v['create_at']);
            $student_name=iconv('utf-8','gbk', $v['student_name']);  
            $school_name=iconv('utf-8','gbk', $v['school_name']);   
            if($v['permissions_status']==1){
                $permissions_status='团体VIP';
            }else{
                $permissions_status='普通权限';
            }
            $permissions_status=iconv('utf-8','gbk', $permissions_status);  
            if($v['flag']==1){
                $account_status='正常';
            }else{
                $account_status='停用';
            }
            $parent_tel=$v['telephone'];  
            $account_status=iconv('utf-8','gbk', $account_status);  
                     
            $str.=$parent_name.','.$regTime.",".$sex.",".$student_name.",".$school_name.",".$permissions_status.",".$parent_tel.",".$account_status."\n";
        } 
        $filename=date('Ymd').rand(0,1000).'Parent'.'.csv';
        $csv=new CSV();
        //export disable
        //$csv->downloadFileCsv($filename,$str);
    }
    
}