<?php
namespace Home\Controller;

use Think\Controller;
use Common\Common\CSV;
use Common\Common\DES3; 

class AdminController extends PublicController
{
    public function __construct() { 
        parent::__construct();  
        
        header("Content-type: text/html; charset=utf-8");  
        $this->assign('oss_path',C('oss_path'));
    }
    
     
     
    public function index()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        redirect(U('Admin/teacherMgmt'));
        $this->display();
    }

    public function me()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '个人中心');
        $this->assign('nav', '我');
        $this->assign('subnav', '我的资料');

        $this->assign('data', session('admin'));

        $this->display();
    }

    //登陆验证并跳转
    public function login()
    {
        if ($_POST) {
            $theme = "1";
            $check['name'] = $_POST['user_name'];
            $check['password'] = sha1($_POST['password_4']);
            $check['status'] = 1;

            $AdminModel = M('auth_admin'); 
            $result = $AdminModel->where($check)->find();   
            if ($result) {  
                session_start();
                session('admin', $result);

                session('theme', $theme);
                $btntheme = "primary";
                if ($theme == 2) $btntheme = "danger";
                if ($theme == 3) $btntheme = "dark";
                session('btntheme', $btntheme);

                if($result['role']==2){
                    $this->redirect("Admin/jbresources");
                }else{
                    $this->redirect("Admin/index");
                } 
            } else {
                $this->redirect("Index/index?role=a&err=1");
            }
        } else {
            session('admin', null);
            $this->assign('REMOTE_ADDR',C('REMOTE_ADDR'));
            $this->display();
        }
    }

    //退出登录
    public function logout()
    {
        session('teacher', null);
        $this->redirect("Teach/login");
    }
    
    //下载教师的示例文件
    public function downloadTeacherFile(){
        $csv=new CSV();
        if(session('admin.role') == 3){ 
            $file="Public/csv/teacherDemo03.csv";
        }else{
            $file="Public/csv/teacherDemo01.csv";
        }
        $csv->downloadFile($file);
    }
    
    //教师批量导入 1001文件为空 1002数据为空
    public function importTeacher(){       
        if (!session('?admin')) redirect(U('Index/index'));

        
        $isVip=0;
        if(session('admin.role') == 3){
            $school_result['id']=session('admin.school_id');
            $teach_info_map['id'] = session('admin.school_id');

            $teach_info = M('dict_schoollist')->where( $teach_info_map )->find();

            if ($teach_info['user_auth'] == 3 && time() >= $teach_info['auth_start_time'] && time() < $teach_info['auth_end_time'] ) {
                $isVip = 1;
            } else {
                $isVip = 2;
            }
        }

        if(empty($_FILES)){
            $data['status']=1001;
            echo json_encode($data);die;
        }
        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){
            $data['status']=$result;
            echo json_encode($data);die;
        } 
        
        
        //默认是utf8 
        $encode = mb_detect_encoding($result['result'][0][0], array('UTF-8','GB2312','GBK','EUC-CN'));  
        if($encode=='EUC-CN' || $encode=='GB2312'){
            $is_utf8=1; 
        }else if($encode=='GBK'){
            $is_utf8=2; 
        }else if($encode=='UTF-8'){
            $is_utf8=0;
        }            
        $data=$result['result'];        
        $length=$result['length'];   

        //测试开始
        //$data_a = eval('return '.iconv('gbk','utf-8',var_export($data,true)).';');
        
        /*
        $data_a = iconv('gb2312','utf-8',$data[0][0]);
        $data_b = iconv('utf-8','gb2312',$data[0][0]);    
        
        if($data_a==$data[0][0]){
            $is_utf8=1;
        }elseif($data_b==$data[0][0]){
            $is_utf8=0;
        }*/ 
       
        $vip_config=C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');
        $model=M('dict_schoollist');
        $teacher_demol=M('auth_teacher');
        $course_model=M('dict_course');
        $grade_model=M('dict_grade');
        $class_model=M('biz_class');     
        $success_number=0; 
        
        $notice_array=array();   
            for($i=1;$i<$length;$i++){ 
                $notice_tag=0;
                $model->startTrans();   
                if(session('admin.role') == 3){
                    //角色3
                    $add_data['school_id']= session('admin.school_id'); 
                    $add_data['telephone']= $this->encode_string($is_utf8,$data[$i][2]);
                    $teacher_where['telephone']= $add_data['telephone'];
                    $add_data['name'] =  $this->encode_string($is_utf8,$data[$i][0]);
                    $add_data['sex'] =  $this->encode_string($is_utf8,$data[$i][1]);
                    if($add_data['sex']!='男' && $add_data['sex']!='女'){
                        $notice_message='性别填写不正确';
                        $notice_tag=1;
                    }
                    $add_data['create_at']=time(); 
                    $add_data['brief_intro']= $this->encode_string($is_utf8,$data[$i][4]);
                    $add_data['email']= $this->encode_string($is_utf8,$data[$i][5]);
                    $grade_class= $this->encode_string($is_utf8,$data[$i][3]);
                    
                }else{ 
                    $add_data['telephone']=  $this->encode_string($is_utf8,$data[$i][2]);
                    $teacher_where['telephone']=$add_data['telephone'];                                         
                    $add_data['name'] = $this->encode_string($is_utf8,$data[$i][0]);
                    $add_data['sex'] =  $this->encode_string($is_utf8,$data[$i][1]);
                    if($add_data['sex']!='男' && $add_data['sex']!='女'){
                        $notice_message='性别填写不正确';
                        $notice_tag=1;
                    }
                    $add_data['create_at']=time(); 
                    $add_data['brief_intro']=   $this->encode_string($is_utf8,$data[$i][5]);
                    $add_data['email']=   $this->encode_string($is_utf8,$data[$i][6]);
                    $grade_class=  $this->encode_string($is_utf8,$data[$i][4]);
                    $shcool= $this->encode_string($is_utf8,$data[$i][3]);
                    $school_result=$model->where("school_name="."'$shcool'")->field("id")->find();          
                    if(empty($school_result)){
                        $notice_message='学校不存在';
                        $notice_tag=1;
                    }else{
                        $add_data['school_id']= $school_result['id'];
                        
                        $teach_info_map['id']=$school_result['id'];
                        $teach_info = M('dict_schoollist')->where( $teach_info_map )->find(); 
                        if ($teach_info['user_auth'] == 3 && time() >= $teach_info['auth_start_time'] && time() < $teach_info['auth_end_time'] ) {
                            $isVip = 1;
                        } else {
                            $isVip = 2;
                        }
                    } 
                }  
                
                if($notice_tag==0){
                    //判断手机号 
                    if(!preg_match("/^1[34578]{1}\d{9}$/",$teacher_where['telephone'])){     
                        $notice_message='手机号格式不正确';
                        $notice_tag=1;
                    } 
                    $teacher_result=$teacher_demol->where($teacher_where)->field('id,school_id,name')->find(); 
                    if(!empty($teacher_result)){
                        $notice_message='教师已经存在';
                        $notice_tag=1;

                    } else {
                        $add_data['password']= sha1('123456'); 
                        if(($teacher_id=$teacher_demol->add($add_data))==false){ 
                            $notice_message='教师信息保存失败';
                            $notice_tag=1;
                        } else {
                            
                            if(session('admin.role') == 3){ 
                                $viplog_info = array(
                                     'school_id' =>session('admin.school_id'),
                                     'school_admin_id' =>session('admin.id'),
                                     'teacher_phone' =>$teach_info['obligation_tel'],
                                     'stu_phone' =>$add_data['telephone'],
                                     'time' =>time(),
                                     'auth_type' =>$teach_info['timetype'],
                                 ); 
                            }else{
                                $viplog_info = array(
                                     'school_id' =>$add_data['school_id'],
                                     'school_admin_id' =>session('admin.id'),
                                     'teacher_phone' =>$teach_info['obligation_tel'],
                                     'stu_phone' =>$add_data['telephone'],
                                     'time' =>time(),
                                     'auth_type' =>$teach_info['timetype'],
                                 );
                            } 
                    
                            if($vip_config && $vip_config<=3){
                                $result=give_new_vip_operation(2,$vip_config,$teacher_id,$school_result['id']); 
                                if($result['status']=='failed'){
                                    $notice_tag=1;
                                }else{ 
                                    if($vip_config==1){
                                        M('vip_and_auth_import')->add( $viplog_info );
                                    }elseif($vip_config==3){
                                        M('vip_and_auth_import')->add( $viplog_info );
                                    } 
                                }
                            }
                            
                            //M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log
                            /*if($vip_config && $vip_config<=3){ 
                                $vip_teacher['user_id'] = $teacher_id;
                                $vip_teacher['role_id'] = 2;
                                $vip_teacher['auth_id'] = 4;
                                $vip_teacher['auth_start_time'] = time();
                                $vip_teacher['auth_end_time'] = time()+3600*24*30*3;
                                $vip_teacher['timetype'] = 1; 
                                $teacher_where = array(
                                    'user_id' => $teacher_id,
                                    'role_id' => 2
                                );
                                
                                if ($vip_config == 1) { 
                                    //赠送90天vip
                                    $vipdata['auth_id']=4; 
                                    M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log
                                    
                                }elseif($vip_config==2){
                                    //普通权限
                                    $vipdata['auth_id']=2; 
                                    $vipdata['auth_start_time']=0;
                                    $vipdata['auth_end_time']=0;
                                    $vipdata['timetype']=0;

                                }elseif($vip_config==3){
                                    $school_info=$teach_info; 
                                    if($isVip==1){
                                        $vipdata['timetype']=$school_info['timetype'];
                                        $vipdata['auth_id']=3;  
                                        $vipdata['auth_end_time']=$school_info['auth_end_time'];
                                        M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log
                                        
                                    }else{
                                        //普通权限
                                        $vipdata['auth_id']=2;
                                        $vipdata['auth_start_time']=0;
                                        $vipdata['auth_end_time']=0;
                                        $vipdata['timetype']=0;
                                        
                                    }
                                } 
                                $info_auth = M('account_user_and_auth')->where( $teacher_where )->find(); 
                                if(empty($info_auth)) {  
                                    $teacher_id = M('account_user_and_auth')->add( $vip_teacher );
                                } 
                                
                            } */

                        }


                    }
                }
                    
                //学科
                if($notice_tag==0){
                    $grade_class_array=explode('.',$grade_class);  
                    if(empty($grade_class_array)){
                        $notice_message='年级学科格式不正确';
                        $notice_tag=1;
                    }else{ 
                        for($t=0;$t<count($grade_class_array);$t++){
                            $tt_array= explode('级',$grade_class_array[$t]);            

                            if(count($tt_array)<=1){  
                                $notice_message='年级学科格式不正确';
                                $notice_tag=1;
                                break;
                            } 
                            $grade=$tt_array[0]."级";
                            $course=$tt_array[1];  

                            $course_result=$course_model->where("course_name="."'$course'")->field('id')->find();       
                            if(empty($course_result)){ 
                                $notice_message='学科不存在';
                                $notice_tag=1;
                                break;
                            }   

                            $grade_result=$grade_model->where("grade="."'$grade'")->field('id')->find();
                            if(empty($grade_result)){ 
                                $notice_message='年级不存在';
                                $notice_tag=1;
                                break;
                            } 
                            //判断关联表中是否存在同样的学科和年级
                            $grade_second_model=M('auth_teacher_second'); 
                            $second_result=$grade_second_model->where("teacher_id=".$teacher_id." and course_id=".$course_result['id']." and grade_id=".$grade_result['id'])->field('id')->find();
                            if(!empty($second_result)){
                                $notice_message='一个老师不能有多个相同的年级学科';
                                $notice_tag=1;
                                break;
                            }
                            
                            if($notice_tag==0){
                                //插入教师年级学科关联表
                                
                                $second_data['teacher_id']=$teacher_id;
                                $second_data['course_id']=$course_result['id'];
                                $second_data['grade_id']=$grade_result['id'];
                                if(!$grade_second_model->add($second_data)){
                                    $notice_message='年级学科信息保存失败';
                                    $notice_tag=1;
                                    break;
                                }
                            }
                        }         
                    }
                }
                    
                if($notice_tag==0){
                    //这里再更新一下那个年级的数据
                    $update_data['grade_id']=$grade_result['grade_id'];
                    $update_data['course_id']=$course_result['id'];
                    $update_where['id']=$teacher_id;
                    if($teacher_demol->where($update_where)->save($update_data)==false){ 
                        $notice_message='教师关联信息保存失败';
                        $notice_tag=1;
                    }
                }
                if($notice_tag==1){
                    $model->rollback();
                    $notice_temp_arr=array();
                    $notice_temp_arr[]=$add_data['name'];
                    $notice_temp_arr[]=$add_data['sex'];
                    $notice_temp_arr[]=$add_data['telephone'];
                    if(session('admin.role') !=3){
                        $notice_temp_arr[]=$shcool;
                    }
                    $notice_temp_arr[]=$grade_class;
                    $notice_temp_arr[]=$add_data['brief_intro'];
                    $notice_temp_arr[]=$add_data['email'];
                    $notice_temp_arr[]=$notice_message; 
                    
                    $notice_array[]=$notice_temp_arr; 
                }else{
                    $success_number++;
                    $model->commit(); 
                }  
                
            }
            //这里插入
            $return_array=array();
            if(!empty($notice_array)){
                $return_array['status']=1003;
            }else{
                $return_array['status']=1004;
            } 
                    
            $return_array['all_number']=($length-1);
            $return_array['success_number']=$success_number;
            $return_array['notice_data']=$notice_array;
            echo json_encode($return_array);  
    }
    
    //批量导出教师
    public function exportedTeacher(){
        if (!session('?admin')) redirect(U('Index/index'));
        if(empty($_POST)){
            $this->error('参数错误');
        }else{ 
            $Model = M('auth_teacher'); 
            
            $condition_arr=I('hid');
            $string='('.rtrim(implode($condition_arr,','),',').')';
             
            if(session('admin.role') == 3){
                 $result = $Model
                ->join('dict_schoollist on auth_teacher.school_id=dict_schoollist.id','left')
                ->join('biz_class on biz_class.class_teacher_id=auth_teacher.id','left')
                ->join('dict_grade on dict_grade.id=biz_class.grade_id','left')
                ->field("dict_schoollist.school_name,auth_teacher.*,biz_class.name class_name,"
                        . "dict_grade.grade,GROUP_CONCAT(dict_grade.grade,biz_class.name SEPARATOR '.')grade_name")
                ->group('auth_teacher.id') 
                ->order('auth_teacher.create_at desc')
                ->where("auth_teacher.flag=1 and auth_teacher.id in $string") 
                ->select();                 
                 $str="教师姓名,性别,手机号,注册时间,所在学校,任教班级,简介,邮箱\n"; 
                 $str=iconv('utf-8','gb2312', $str);
                 foreach($result as $v){
                    $name=iconv('utf-8','gbk', $v['name']);
                    $sex=iconv('utf-8','gb2312', $v['sex']);
                    $telephone=iconv( 'utf-8','gb2312', $v['telephone']);
                    $create_at=iconv( 'utf-8','gb2312', date('Y-m-d ',$v['create_at']));
                    $school_name=iconv( 'utf-8','gbk', $v['school_name']);
                    $course=iconv( 'utf-8','gb2312', $v['course_name']);
                    $grade_class=iconv( 'utf-8','gb2312', $v['grade_name']);
                    $brief_intro=iconv( 'utf-8','gb2312', $v['brief_intro']);
                    $email=iconv( 'utf-8','gb2312', $v['email']);
                    $str.=$name.",".$sex.",".$telephone.",".$create_at.",".$school_name.",".$grade_class.",".$brief_intro.",".$email."\n";
                 }
            }else{
                $result=$Model->where("auth_teacher.flag=1 and auth_teacher.id in $string")->join("dict_schoollist on auth_teacher.school_id=dict_schoollist.id","left")
                        ->field('dict_schoollist.school_name,auth_teacher.*')
                        ->order('auth_teacher.create_at desc')
                        ->select();
                $str="教师姓名,性别,手机号,注册时间,所在学校,简介,邮箱\n";
                $str=iconv('utf-8','gb2312', $str);
                foreach($result as $v){
                    $name=iconv('utf-8','gbk', $v['name']);
                    $sex=iconv('utf-8','gb2312', $v['sex']);
                    $telephone=iconv( 'utf-8','gb2312', $v['telephone']);
                    $create_at=iconv( 'utf-8','gb2312', date('Y-m-d ',$v['create_at']));
                    $school_name=iconv( 'utf-8','gbk', $v['school_name']);
                    $brief_intro=iconv( 'utf-8','gb2312', $v['brief_intro']);
                    $brief_intro =str_replace(',','，',$brief_intro);
                    $email=iconv( 'utf-8','gb2312', $v['email']);
                    $str.=$name.",".$sex.",".$telephone.",".$create_at.",".$school_name.",".$brief_intro.",".$email."\n";
                }
            }
            $filename=date('Ymd').rand(0,1000).'teacher'.'.csv';
            $csv=new CSV();
            $csv->downloadFileCsv($filename,$str);
        }
         
        
    }

    public function exportedTeacherAll(){
        set_time_limit(0);
        if (!session('?admin')) redirect(U('Index/index'));
         $where=array();
         $queryParas = $_GET;
         $keyword = $queryParas['keyword'];
         $status=intval($queryParas['lock_status']);
         if(!empty($status)){
             $where['auth_teacher.lock']=$status-1;
             $this->assign('status', $status);
         }


         if (session('admin.role') == 3) {
             $where['auth_teacher.school_id'] = session('admin.school_id');
         }
         $where['auth_teacher.flag']=1;

          $join[] = "INNER JOIN dict_schoollist on auth_teacher.school_id = dict_schoollist.id";

         if(!empty($queryParas['province_id']))
           $where['dict_schoollist.provice_id'] = $queryParas['province_id'];
         if(!empty($queryParas['city_id']))
           $where['dict_schoollist.city_id'] = $queryParas['city_id'];
         if(!empty($queryParas['country_id']))
            $where['dict_schoollist.district_id'] = $queryParas['country_id'];
         if(!empty($queryParas['school_id']))
            $where['auth_teacher.school_id'] = $queryParas['school_id'];
         if(!empty($queryParas['course_id']))
            $where['auth_teacher_second.course_id'] = $queryParas['course_id'];
         if(!empty($queryParas['grade_id']))
            $where['auth_teacher_second.grade_id'] = $queryParas['grade_id'];
         if (!empty($keyword)) {
                 $where['_string'] = "(auth_teacher.name like '%$keyword%') OR (auth_teacher.telephone like '%$keyword%') ";
         }

         $join[] = "INNER JOIN auth_teacher_second ON auth_teacher_second.teacher_id = auth_teacher.id";
         $join[] = "INNER JOIN dict_course ON auth_teacher_second.course_id = dict_course.id";
         $join[] = "INNER JOIN dict_grade ON auth_teacher_second.grade_id = dict_grade.id";
         $join[] = "LEFT JOIN biz_class ON biz_class.class_teacher_id = auth_teacher.id";
         $Model = M('auth_teacher');

        if(session('admin.role') == 3){
            $result = $Model
                ->where($where)
                ->join($join)
                ->order('auth_teacher.create_at desc')
                ->field("dict_schoollist.school_name,auth_teacher.*,biz_class.name class_name,"
                    . "dict_grade.grade,GROUP_CONCAT(dict_grade.grade,biz_class.name SEPARATOR '.')grade_name") 
                ->group('auth_teacher.id')
                ->order('auth_teacher.create_at desc')
                ->select();
            $str="教师姓名,性别,手机号,注册时间,所在学校,任教班级,简介,邮箱\n";
            $str=iconv('utf-8','gb2312', $str);
            foreach($result as $v){
                $name=iconv('utf-8','gbk', $v['name']);
                $sex=iconv('utf-8','gb2312', $v['sex']);
                $telephone=iconv( 'utf-8','gb2312', $v['telephone']);
                $create_at=iconv( 'utf-8','gb2312', date('Y-m-d ',$v['create_at']));
                $school_name=iconv( 'utf-8','gbk', $v['school_name']);
                $course=iconv( 'utf-8','gb2312', $v['course_name']);
                $grade_class=iconv( 'utf-8','gb2312', $v['grade_name']);
                $brief_intro=iconv( 'utf-8','gb2312', $v['brief_intro']);
                $brief_intro =str_replace(',','，',$brief_intro);
                $brief_intro =str_replace(array("\r\n", "\r", "\n"), "", $brief_intro);
                $email=iconv( 'utf-8','gb2312', $v['email']);
                $str.=$name.",".$sex.",".$telephone.",".$create_at.",".$school_name.",".$grade_class.",".$brief_intro.",".$email."\n";
            }
        }else{
            $result=$Model->join($join)
                ->where($where)
                ->field('dict_schoollist.school_name,auth_teacher.*')
                ->order('auth_teacher.create_at desc')
                ->group('auth_teacher.id')
                ->order('auth_teacher.create_at desc')
                ->select(); 
            $str="教师姓名,性别,手机号,注册时间,所在学校,简介,邮箱\n";
            $str=iconv('utf-8','gb2312', $str);
            foreach($result as $v){
                $name=iconv('utf-8','gbk', $v['name']);
                $sex=iconv('utf-8','gb2312', $v['sex']);
                $telephone=iconv( 'utf-8','gb2312', $v['telephone']);
                $create_at=iconv( 'utf-8','gb2312', date('Y-m-d ',$v['create_at']));
                $school_name=iconv( 'utf-8','gbk', $v['school_name']);
                $brief_intro=iconv( 'utf-8','gb2312', $v['brief_intro']);
                $brief_intro =str_replace(',','，',$brief_intro);
                $brief_intro =str_replace(array("\r\n", "\r", "\n"), "", $brief_intro);
                $email=iconv( 'utf-8','gb2312', $v['email']);
                $str.=$name.",".$sex.",".$telephone.",".$create_at.",".$school_name.",".$brief_intro.",".$email."\n";
            }
        }
        $filename=date('Ymd').rand(0,1000).'teacher'.'.csv';
        $csv=new CSV();
        $csv->downloadFileCsv($filename,$str);



    }
    //根据老师所教的班级拿到其下的所有班级
    function getTeacherClass(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        $id=  intval(I('id'));
        if(!$id){
            echo $data['status']='error';
            echo json_encode($data); 
        }else{
           $Model = M('biz_class'); 
           $result=$Model->where('dict_course.id='.$id)
                   ->join('auth_teacher_second on auth_teacher_second.teacher_id=biz_class.class_teacher_id')
                   ->join('dict_course on dict_course.id=auth_teacher_second.course_id')
                   ->field('biz_class.id,biz_class.name class')->select();  
            
           $data['status']='success';
           $data['data']=$result;
           echo json_encode($data);
        } 
    }

    //教师列表
    public function teacherMgmt()   
    {       
       if (!session('?admin')) redirect(U('Index/index'));
       if (session('admin.role') == 3) {
        $filterSelect = array(
       'province' =>0 ,'city'=>0,'country'=>0,'school'=>0,'course'=>1,'grade'=>1,'textbook'=>0
       );
       }
       else
        $filterSelect = array(
       'province' =>1 ,'city'=>1,'country'=>1,'school'=>1,'course'=>1,'grade'=>1,'textbook'=>0
       );

       $queryParas = $_GET;
       $keyword = $queryParas['keyword'];
       $status=intval($queryParas['lock_status']);
       $where=array();
       $where['auth_teacher.flag']=1;
       if(!empty($status)){
           $where['auth_teacher.lock']=$status-1;
           $this->assign('status', $status);
       }


       if (session('admin.role') == 3) {
           $where['auth_teacher.school_id'] = session('admin.school_id');
       }

        $join[] = "left JOIN dict_schoollist on auth_teacher.school_id = dict_schoollist.id";

       if(!empty($queryParas['province_id']))
         $where['dict_schoollist.provice_id'] = $queryParas['province_id'];
       if(!empty($queryParas['city_id']))
         $where['dict_schoollist.city_id'] = $queryParas['city_id'];
       if(!empty($queryParas['country_id']))
          $where['dict_schoollist.district_id'] = $queryParas['country_id'];
       if(!empty($queryParas['school_id']))
          $where['auth_teacher.school_id'] = $queryParas['school_id'];
       if(!empty($queryParas['course_id']))
          $where['auth_teacher_second.course_id'] = $queryParas['course_id'];
       if(!empty($queryParas['grade_id']))
          $where['auth_teacher_second.grade_id'] = $queryParas['grade_id'];
       if (!empty($keyword)) {
               $where['_string'] = "(auth_teacher.name like '%$keyword%') OR (auth_teacher.telephone like '%$keyword%') ";
       }

       $Model = M('auth_teacher');
       $course_model=M('dict_course');
       $join[] = "left JOIN auth_teacher_second ON auth_teacher_second.teacher_id = auth_teacher.id";
       $join[] = "left JOIN dict_course ON auth_teacher_second.course_id = dict_course.id";
       $join[] = "left JOIN dict_grade ON auth_teacher_second.grade_id = dict_grade.id";

       $count = $Model
           ->join($join)
           ->field("auth_teacher.id")
           ->where($where)
           ->group('auth_teacher.id')
           ->select();       

       $Page = new \Think\Page(count($count), C('PAGE_SIZE_FRONT'));
       //$Page->parameter['keyword'] = urlencode($keyword);
       $Page->parameter['keyword'] = $keyword;
       $show = $Page->show('queryList');

       $join[] = "LEFT JOIN biz_class ON biz_class.class_teacher_id = auth_teacher.id";
       $result = $Model
           ->join($join)
           ->field('dict_schoollist.school_name,auth_teacher.*,'
                   . "GROUP_CONCAT(DISTINCT dict_course.course_name SEPARATOR '.')course_name,"
                   . "GROUP_CONCAT(dict_grade.grade,biz_class.name SEPARATOR '.')grade_name")
           ->group('auth_teacher.id')
           ->order('auth_teacher.create_at desc')
           ->where($where)
           ->limit($Page->firstRow . ',' . $Page->listRows)
           ->select();      



       $auth_type_use = D('Account_auths');
       foreach ($result as $key => $value) {
           $isVipInfo = $auth_type_use->isVipInfo($value['id'],2);
           $result[$key]['vipinfo'] = $isVipInfo;
       }

       $this->assign('list', $result);
       $this->assign('page', $show);
       $this->assign('role',session('admin.role'));
       if($_POST)
       {
        $this->assign('role',session('admin.role'));
        $this->display('teacherMgmtDetails');
        exit;
       }
       else
       {
        $content = $this->fetch('teacherMgmtDetails');
        $this->assign('filterSelect',$filterSelect);
        $this->assign('module', '用户管理');
        $this->assign('nav', '教师管理');
        $this->assign('subnav', '教师列表');
        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        $this->assign('courses', $courses);


        $Model = M('dict_grade');
        $grades = $Model->select();
        $this->assign('grades', $grades);


        $this->assign('content',$content);
        $this->display();
       }



    }
    
    
    //删除教师
    public function deleteTeacherAccount(){
        if (!session('?admin')) redirect(U('Index/index')); 
        
        $id=intval(I('id'));
        if(!$id){
            $this->ajaxReturn('failed'); 
        }
        $Model = M('auth_teacher');
        $where['id']=$id;
        $data['flag']=-1;
        if(session('admin.role') == 3){
            $where['school_id']=session('admin.school_id');
        }
        if($Model->where($where)->save($data)){
            $this->ajaxReturn('success');  
        }else{
            $this->ajaxReturn('failed');  
        } 
        
    }
    
    //教师通过或者拒绝
    public function reviewedTeacher(){
        if (!session('?admin')) redirect(U('Index/index'));
          
        $id=I('id');
        $status=I('status');
        if($status==1){
            //这里是通过
            $data['lock']=2;
        }else{
            //这里是拒绝
            $data['lock']=3;
        }
        $Model = M('auth_teacher');
        $Model->where("id=$id")->save($data);

        $this->ajaxReturn('success');
    }

    //锁定教师账号
    public function lockTeacherAccount()
    {
        if (!session('?admin')) redirect(U('Index/index'));
        
        if(session('admin.role') == 3){
            echo 'error';die;
        }
        
        $id = $_GET['id'];
        $Model = M('auth_teacher');

        $data['lock'] = 1;

        $Model->where("id=$id")->save($data);

        $parameters = array( 'msg' => array(date("Y-m-d H:i:s",time())) , 'url' => array( 'type' => 0));
        A('Home/Message')->addPushUserMessage('USER_LOCK',2,$id,$parameters);

        $this->ajaxReturn('success');
    }

    //解锁教师账号
    public function unlockTeacherAccount()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        if(session('admin.role') == 3){
            echo 'error';die;
        }
        $id = $_GET['id'];
        $Model = M('auth_teacher');

        $data['lock'] = 2;

        $parameters = array( 'msg' => array(date("Y-m-d H:i:s",time())) , 'url' => array( 'type' => 0));
        A('Home/Message')->addPushUserMessage('USER_UNLOCK',2,$id,$parameters);

        $Model->where("id=$id")->save($data);

        $this->ajaxReturn('success');
    }

    //修改教师
    public function modifyTeacher()
    { 
        if (!session('?admin')) redirect(U('Index/index'));

        if ($_POST) {

            $Model = M('auth_teacher');
            $select['telephone'] = $_POST['telephone'];
            $info = $Model->where($select)->select();

            if($_POST['sex']==1){
                $data['sex']='男';
            }else{
                $data['sex']='女';
            }
            if (session('admin.role') == 3) {
                $check['school_id'] = session('admin.school_id');
            }
            $check['id'] = $_POST['id'];
            $teacher_id=$check['id'];
            if(!$teacher_id){
                $this->error('参数错误');
            }
            $data['name'] = remove_xss($_POST['name']);
            $data['telephone'] = $_POST['telephone'];
            //这里判断登录角色
            if(session('admin.role')==1){
                $data['school_id'] = $_POST['school_id'];
                //查找学校是否存在
                $school_id=$data['school_id'];
                $school_model=M('dict_schoollist'); 
                $school_result=$school_model->where("id="."'$school_id'")->field('id')->find(); 
                if(empty($school_result)){
                    $this->error('该学校不存在');
                }
            }   
             
            
            //$data['grade_id'] = $_POST['grade_id'];
            //$data['course_id'] = $_POST['course_id'];
            $data['brief_intro'] = remove_xss($_POST['brief_intro']);
            $data['update_at'] = time();
            $data['email'] = remove_xss($_POST['email']);
            //$data['lock'] = 0;
            $grade_array=$_POST['grade']; 
            $course_array=$_POST['course']; 
            $course_second=$course_array; 
            $grade_second=$grade_array; 

            $Model = M('auth_teacher'); 
            
            $not_found_array=array(); 
            $found_string="";
            
            //得到老师的姓名
            $teacher_result=$Model->where("id="."'$teacher_id'")->field("name")->find();    
            if(empty($teacher_result)){
                $Model->rollback();
                $this->error('参数错误');
            }
            
            //判断除了当前ID,手机号是否存在
            $teacher_phone=$data['telephone'];
            $phone_result=$Model->where("id !="."'$teacher_id'"."and telephone="."'$teacher_phone'")->field('id')->find();
            if(!empty($phone_result)){
                $this->error('该手机号已经存在');
            } 
             
            $course_model=M('dict_course');
            $grade_model=M('dict_grade');
            
            $Model->startTrans(); 
             
            $not_found_second_array=array();
            //这里把那个老师的关联表先删除,再插入
            $teacher_second=M('auth_teacher_second');
            if($teacher_second->where("teacher_id=".$teacher_id)->delete()===false){
                $Model->rollback();
                $this->error('数据提交失败');
            } 
            $second['teacher_id']=$teacher_id;
            for($k=0;$k<count($course_second);$k++){ 
                $course_result=$course_model->where("id="."'$course_second[$k]'")->field('id')->find();
                if(empty($course_result)){ 
                    $Model->rollback();
                    $this->error('参数错误');
                }

                $grade_result=$grade_model->where("id="."'$grade_second[$k]'")->field('id')->find();
                if(empty($grade_result)){ 
                    $Model->rollback();
                    $this->error('参数错误');
                } 
                
                
                $second['course_id']=$course_second[$k];      
                $second['grade_id']=$grade_second[$k];
                if(!$second_result=$teacher_second->add($second)){
                    $Model->rollback();
                    $this->error('数据提交失败');
                }
            } 
             
            $data['course_id']=$second['course_id'];
            $data['grade_id']=$second['grade_id'];
            //$data['lock']=0;
            
            if($Model->where($check)->save($data)===false){
                $Model->rollback();
                $this->error('数据提交失败');
            }
            $Model->commit();

            $this->redirect("Admin/teacherMgmt");

        } else {
            $this->assign('module', '用户管理');
            $this->assign('nav', '教师管理');
            $this->assign('subnav', '修改教师信息');

            $id = $_GET['id'];
            if(!$id){
                $this->error('参数错误');
            }
            $this->assign('id', $id);
            
            if(session('admin.role') == 3){ 
                $where['auth_teacher.school_id']=session('admin.school_id');
            }
            $where['auth_teacher.id']=$id; 
            

            $ModelSchool = M('dict_schoollist');
            $schools = $ModelSchool->order('school_name asc')->where('status=2')->field('id,school_name')->select();         
           
            $this->assign('schools', $schools);

            $Model = M('auth_teacher');
            $teacher_model=$Model;
            $result = $Model->where("id=$id")->find();

            $this->assign('data', $result);

            $ModelSchool = M('dict_schoollist');
            $sc_id['id'] = $result['school_id'];
            $school_name = $ModelSchool->order('school_name asc')->where($sc_id)->group('school_name')->field('id,school_name')->find();

            $this->assign('school_name', $school_name);

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);
            

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);
            
            //这里得到这个老师教的所有的年级班级等
            $class_grade=$teacher_model->where($where)
                    ->join("auth_teacher_second ON auth_teacher_second.teacher_id = auth_teacher.id") 
                    ->join("dict_grade ON dict_grade.id = auth_teacher_second.grade_id")
                    ->join("dict_course on dict_course.id=auth_teacher_second.course_id")
                    ->field("dict_grade.id grade_id,dict_grade.grade,dict_course.id course_id,dict_course.course_name")->select();          
            
                    
            $account_model=D('Account_auths');
            $list=$account_model->getUserVipAuthAll($id,2);   
            $account_list=$account_model->getAuthList(); 
            
            $this->assign('grade_class_select', $class_grade);
            $this->assign('user_auth',$list);
            $this->assign('account_list',$account_list);
            $this->display();
        }
    }
         
    
    
    //获得某一个权限信息
    public function getPrivilegeInfo(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        $auth_id=getParameter('id','int');
        $user_id=getParameter('user_id','int');
        $role_id=getParameter('role','int');
        $model=M('account_user_and_auth');
        $where['id']=$auth_id;
        $where['user_id']=$user_id;
        $where['role_id']=$role_id;
        $result=$model->where($where)->field('id,auth_id,FROM_UNIXTIME(auth_start_time,"%Y-%m-%d") start_time,FROM_UNIXTIME(auth_end_time,"%Y-%m-%d") end_time,timetype')->select();  
        echo json_encode($result); 
    }
    
    //删除权限
    public function deletePrivilegeInfo(){
        $auth_id=getParameter('id','int');
        $user_id=getParameter('user_id','int');
        $role_id=getParameter('role','int');
        $model=M('account_user_and_auth');
        $where['id']=$auth_id;
        $where['user_id']=$user_id;
        $where['role_id']=$role_id;
        if($model->where($where)->delete()){
            echo json_encode(array('status'=>1));
        }else{
            echo json_encode(array('status'=>2));
        }
    }
    
    //保存权限
	public function pushVIPSuccess($auth_type,$user_id,$role_id)
	{
         $authInfo = D('Account_auths')->getAuthList();
 		for($i=0;$i<count($authInfo);$i++)
 		{
 			if($authInfo[$i]['id'] == $auth_type)
 			{
 				$authName = $authInfo[$i]['auth_name'];
 				break;
 			}
 		}
          $parameter_arr=array(
           'msg'=>array($authName),
           'url'=>array(
               'type'=>0,
           'data'=>array()
            )
           );
          A('Home/Message')->addPushUserMessage('VIP_SUCCESS',$role_id,$user_id,$parameter_arr);
	}
    public function savePrivilegeInfo(){
        $save_type=getParameter('save_type','int');  
        $user_id=getParameter('user_id','int');
        $role_id=getParameter('role','int'); 
        $auth_type=getParameter('auth_type','int'); 
        $start_time=getParameter('start_time','str');
        $end_time=getParameter('end_time','str'); 
        $use_type=getParameter('use_type','int');
        $data=array(
            'user_id'=>$user_id, 
            'role_id'=>$role_id,
            'auth_id'=>$auth_type,
            'auth_start_time'=> strtotime($start_time),
            'auth_end_time'=> strtotime($end_time),
            'timetype'=>$use_type,
        );
        $model=M('account_user_and_auth'); 
        
        $check['auth_id']=$auth_type;
        $check['user_id']=$user_id;
        $check['_string']="(auth_start_time<=".strtotime($start_time)." and auth_end_time>=".strtotime($start_time).")"
                    . "or(auth_start_time<=".strtotime($end_time)." and auth_end_time>=".strtotime($end_time).")"
                . "or(auth_start_time>=".strtotime($start_time)." and auth_end_time<=".strtotime($end_time).")";
                    
        if($save_type==1){  
            $result=$model->where($check)->select();  
            if(!empty($result)){
                echo json_encode(array('status'=>3));
                die;
            }
                    
            if(!($insert_id=$model->add($data))){
                echo json_encode(array('status'=>2));
            }else{
                $this->pushVIPSuccess($auth_type,$user_id,$role_id);
                echo json_encode(array('status'=>1));
            }
        }else{  
            $auth_primary_id=getParameter('auth_id','int'); 
            $check['id']=array('neq',$auth_primary_id);
            $result=$model->where($check)->select();    
            if(!empty($result)){
                echo json_encode(array('status'=>3));
                die;
            } 
            
            $where['id']=$auth_primary_id;
            if($model->where($where)->save($data)===false){
                echo json_encode(array('status'=>2));
            }else{
                $this->pushVIPSuccess($auth_type,$user_id,$role_id);
                echo json_encode(array('status'=>1));
            }
        }
    }

    //创建教师账号
    public function createTeacherAccount()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        if ($_POST) {
            $check['telephone'] = $_POST['telephone'];

            $TeacherModel = M('auth_teacher');
            $teacher = $TeacherModel->where($check)->field('id')->find();
            if (!empty($teacher)) {
                $this->error('该手机号已存在');
            } 
            //查找学校是否存在
            //这里判断登录角色
            if(session('admin.role')==3){
                $data['school_id'] =  session('admin.school_id');
            }else{ 
                $data['school_id'] = $_POST['school_id'];
                $school_id=$data['school_id'];
                $school_model=M('dict_schoollist');
                $school_result=$school_model->where("id="."'$school_id'")->field('id')->find(); 
                if(empty($school_result)){
                    $this->error('该学校不存在');
                }
            }
            
            if($_POST['sex']==1){
                $data['sex']='男';
            }else{
                $data['sex']='女';
            }
            $data['name'] = remove_xss($_POST['name']);
            $data['password'] = sha1($_POST['password']);
            $data['telephone'] = $_POST['telephone'];
            $data['access_token'] = "12fefafefefef";
            
            //$data['grade_id'] = $_POST['grade_id'];
            //$data['course_id'] = $_POST['course_id'];
            $data['brief_intro'] = remove_xss($_POST['brief_intro']);
            $data['update_at'] = time();
            $data['create_at'] = time(); 
            $data['email'] = remove_xss($_POST['email']);
            $grade_second=$_POST['grade']; 
            $course_second=$_POST['course'];  
            $Model = M('auth_teacher');

            $course_model=M('dict_course');
            $grade_model=M('dict_grade'); 
              
            
            $Model->startTrans();
            if(!($insert_id=$Model->add($data))){
                $this->error('数据提交失败');
            }
            
            $second['teacher_id']=$insert_id;  
            $teacher_second=M('auth_teacher_second');
            for($k=0;$k<count($course_second);$k++){
                
                $course_result=$course_model->where("id="."'$course_second[$k]'")->field('id')->find();
                if(empty($course_result)){ 
                    $Model->rollback();
                    $this->error('参数错误');
                }

                $grade_result=$grade_model->where("id="."'$grade_second[$k]'")->field('id')->find();
                if(empty($grade_result)){ 
                    $Model->rollback();
                    $this->error('参数错误');
                } 
                
                $second['course_id']=$course_second[$k];      
                $second['grade_id']=$grade_second[$k];
                if(!$second_result=$teacher_second->add($second)){
                    $Model->rollback();
                    $this->error('数据提交失败');
                }
            }
            $Model->commit();
             
            $this->redirect("Admin/teacherMgmt");
        } else {
            $this->assign('module', '用户管理');
            $this->assign('nav', '教师管理');
            $this->assign('subnav', '创建教师账号');

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();  
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            $Model = M('biz_class');
            $class = $Model->field('id,name')->select();
            $this->assign('class', $class);

            $ModelSchool = M('dict_schoollist');
            $schools = $ModelSchool->order('school_name asc')->where("status=2")->field('id,school_name')->select();    
            $this->assign('schools', $schools);

            $this->display();
        }

    }
    
    //创建学生视图
    public function createStudent(){        
        
        $this->assign('module', '用户管理');
        $this->assign('nav', '学生管理');
        $this->assign('subnav', '创建学生信息');
        
        $parent_Model = M('auth_parent');  
        //得到所有家长
        //$parent_result=$parent_Model->field("id,parent_name")->select();
        //$this->assign('parent_data', $parent_result);
        
        $ModelSchool = M('dict_schoollist');
        $schools = $ModelSchool->order('school_name asc')->where('status=2')->field('id,school_name')->select(); 
        $this->assign('schools', $schools);
        
        $this->display();
    }
    
    //创建学生操作
    public function createStudentOp(){
        $Model = M('auth_student');
        $parent_model= M('auth_parent');
        $data['student_name']=I('name');
        
        $sex=I('sex');
        if($sex==1){
            $data['sex']='男';
        }else{
            $data['sex']='女';
        }   
        //超级管理员的角色
                    
        if(session('admin.role')==3){
            //学校管理员角色
            $data['school_id']= session('admin.school_id'); 
        }else{
            $data['school_id']=intval(I('school_id'));
            $school_model=M('dict_schoollist'); 
            $school_result=$school_model->where("id=".$data['school_id'])->field('id')->find();
            if(empty($school_result)){
                $this->error('参数错误');
            }
        }
        
        $parent_phone=I('telephone');
        $parent_result=$parent_model->field('id,parent_name')->where("telephone="."'$parent_phone'")->find();   
        if(empty($parent_result)){
            $this->error('家长手机号不存在');
        }
        $data_info['student_name'] = $data['student_name'];
        $data_info['parent_tel'] = $parent_phone;
        $find_parent = M('auth_student')->where($data_info)->find();
        if (!empty($find_parent)){
            $this->error('该名学生已经存在');
        }

        $data['parent_id']=$parent_result['id'];  
        $data['password']=  sha1(I('pwd'));
        $data['email']=I('email');
        $data['parent_name']=$parent_result['parent_name']; 
        $data['parent_tel']=$parent_phone;
        $data['birth_date']=  strtotime(I('birth_date'));
        $data['create_at']=  time();
        $data['update_at']=  time(); 
        $Model->add($data); 
        
        redirect(U('admin/studentMgmt'));
    }
    
    //修改学生视图
    public function modifyStudent(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        $this->assign('module', '用户管理');
        $this->assign('nav', '学生管理');
        $this->assign('subnav', '修改学生信息');
        
        $id = I('id'); 
        if(!$id){
            $this->error();
        }
        $where['auth_student.id']=$id;
        $Model = M('auth_student');
        $parent_Model = M('auth_parent');
        $result = $Model->where($where)
            ->join('dict_schoollist on auth_student.school_id=dict_schoollist.id')
            ->field('dict_schoollist.school_name,dict_schoollist.id school_id,auth_student.id,auth_student.student_id,auth_student.student_name,'
                    . 'auth_student.birth_date,auth_student.create_at,auth_student.email,auth_student.parent_id,auth_student.parent_name,'
                    . 'auth_student.parent_tel,auth_student.lock,auth_student.flag,sex')
            ->find();
        //print_r(M()->getLastSql());die();
        //得到所有家长
        $parent_result=$parent_Model->field("id,parent_name")->select();
        $this->assign('parent_data', $parent_result);
        
        $ModelSchool = M('dict_schoollist');
        $schools = $ModelSchool->order('school_name asc')->where('status=2')->field('id,school_name')->select(); 
        $this->assign('schools', $schools);


        $this->assign('data', $result);
        
        
        $account_model=D('Account_auths');
        $list=$account_model->getUserVipAuthAll($id,3);   
        $account_list=$account_model->getAuthList();        

        $this->assign('id',$id);
        $this->assign('user_auth',$list);
        $this->assign('account_list',$account_list);
        
        $this->display();
    }
    
    //修改学生操作
    public function modifyStudentOp(){  
        $Model = M('auth_student');
        $parent_model= M('auth_parent');
        $data['student_name']=I('name');
        $id=I('id');
        if(!$id){
            $this->error('参数错误');
        }
        //超级管理员的角色
        if(session('admin.role')==3){
            //学校管理员角色
            $data['school_id']= session('admin.school_id'); 
        }else{
            $data['school_id']=intval(I('school_id'));
            $school_model=M('dict_schoollist'); 
            $school_result=$school_model->where("id=".$data['school_id'])->field('id')->find();
            if(empty($school_result)){
                $this->error('参数错误');
            }
        } 
        $sex=I('sex');
        if($sex==1){
            $data['sex']='男';
        }else{
            $data['sex']='女';
        } 
        $phone=I('telephone'); //telephone
        $parent_result=$parent_model->field('id,parent_name,telephone')->where("telephone="."'$phone'")->find(); 
        if(empty($parent_result)){
            $this->error('参数错误');
        }
        $data['parent_id']=$parent_result['id'];
        $data['parent_name']=$parent_result['parent_name'];
        $data['email']=I('email');
        if(!empty($parent_result['telephone']))
        {
            $data['parent_tel']=$parent_result['telephone'];
        }
        $data['birth_date']=  strtotime(I('birth_date'));
        $Model->where("id=$id")->save($data); 
        
        //die;
        redirect(U('admin/studentMgmt'));
    }
    
    //家长学生下载
    public function downloadParentStudent(){ 
        $csv=new CSV();
        if(session('admin.role') == 3){ 
            $file="Public/csv/parentStudentDemo03.csv";
        }else{
            $file="Public/csv/parentStudentDemo01.csv";
        }
        $csv->downloadFile($file);
    }
        
    //家长学生导入列表视图
    public function importParentStudentList(){
        if (!session('?admin')) redirect(U('Index/index'));
        $this->assign('module', '用户管理');
        $this->assign('nav', '家长学生管理');
        $this->assign('subnav', '导入家长学生信息');
        
        $this->assign('role',  session('admin.role'));
        
        $this->display();
    }
    
    //家长学生导入
    public function importParentStudent(){
        if(empty($_FILES)){
            $data['status']=1001;
            echo json_encode($data);die;  
        }

        $isVip=0;
        if(session('admin.role') == 3){
            $teach_info_map['id'] = session('admin.school_id');

            $teach_info = M('dict_schoollist')->where( $teach_info_map )->find();

            if ($teach_info['user_auth'] == 3 && time() >= $teach_info['auth_start_time'] && time() < $teach_info['auth_end_time'] ) {
                $isVip = 1;
            } else {
                $isVip = 2;
            }
        }

        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){ 
            $data['status']=$result;
            echo json_encode($data);die;   
        } 
        
        //默认是utf8 
        $encode = mb_detect_encoding($result['result'][0][0], array('UTF-8','GB2312','GBK','EUC-CN')); 
        if($encode=='EUC-CN' || $encode=='GB2312'){
            $is_utf8=1; 
        }else if($encode=='GBK'){
            $is_utf8=2; 
        }else if($encode=='UTF-8'){
            $is_utf8=0;
        }
                    
        $notice_array=array(); 
        $data=$result['result'];        
        $length=$result['length'];
        $student_model=M('auth_student');
        $parent_demol=M('auth_parent'); 
        $school_model=M('dict_schoollist');
        $success_number=0;
        $vip_config=C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');
        
        for($i=1;$i<$length;$i++){
            $notice_tag=0;  //0为正常
            $parent_data['parent_name'] = $is_utf8?iconv('gb2312', 'utf-8', $data[$i][0]):$data[$i][0];
            $parent_data['telephone'] = $is_utf8?iconv('gb2312', 'utf-8', $data[$i][1]):$data[$i][1]; 
            
            $parent_data['sex'] = $is_utf8?iconv('gb2312', 'utf-8', $data[$i][2]):$data[$i][2];
            $parent_data['create_at'] =time();
            $parent_data['password'] =sha1('123456');               
            //判断手机号是否正确  
            if(!preg_match("/^1[34578]{1}\d{9}$/",$parent_data['telephone'])){     
                $notice_message='手机号格式不正确';
                $notice_tag=1;
            }
            //家长表中和学生表中判断手机号是否存在,如果其中一个存在就跳过该行
            $parent_where['telephone']=$parent_data['telephone'];
            $parent_result=$parent_demol->where($parent_where)->field('id')->find();    
            if(!empty($parent_result)){ 
                $notice_message='手机号在家长信息中已存在';
                $notice_tag=1;
            }
            
            if($notice_tag==0){
                $student_where['parent_tel']=$parent_data['telephone'];
                $student_result=$student_model->where($student_where)->field('id')->find();
                if(!empty($student_result)){ 
                    $notice_message='手机号在学生信息中已存在';
                    $notice_tag=1;
                }
            }
            
            if($notice_tag==0){
                if($parent_data['sex']!='男' && $parent_data['sex']!='女'){
                    $notice_message='家长性别填写不正确';
                    $notice_tag=1;
                }
            }
            
            //插入数据库
            $parent_demol->startTrans();
            if($notice_tag==0){
                $insert_parent_id=$parent_demol->add($parent_data);  
            }
            
            if(session('admin.role')==1 || session('admin.role')==2){
                //按照|拆分孩子信息,并且判断3个数组的长度是否一致 
                $student_name= $is_utf8?iconv('gb2312', 'utf-8', $data[$i][3]):$data[$i][3];
                $student_sex= $is_utf8?iconv('gb2312', 'utf-8', $data[$i][4]):$data[$i][4];
                $student_school= $is_utf8?iconv('gb2312', 'utf-8', $data[$i][5]):$data[$i][5];
                $student_brith_date= $is_utf8?iconv('gb2312', 'utf-8', $data[$i][6]):$data[$i][6];

                $student_name_arr=explode('|',$student_name);
                $student_sex_arr=explode('|',$student_sex);
                $student_school_arr=explode('|',$student_school);
                $student_brith_date_arr=explode('|',$student_brith_date); 
            
                $school_id_arr=array();
                if($notice_tag==0){
                    if(count($student_name_arr)==count($student_school_arr) && count($student_name_arr)==count($student_brith_date_arr) && count($student_name_arr)==count($student_sex_arr)){
                        foreach($student_name_arr as $k=>$v){
                            if($student_sex_arr[$k]!='男' && $student_sex_arr[$k]!='女'){
                                $parent_demol->rollback();
                                $notice_message='该条信息有学生的性别填写不正确';
                                $notice_tag=1;
                                break;
                            }
                
                            
                            //先判断学校是否存在,不存在则把$i这条信息插入异常数组
                            $school_name=$student_school_arr[$k];
                            $temp_school=$school_model->where("school_name="."'$school_name'")->field('id,user_auth,auth_start_time,auth_end_time,timetype,obligation_tel')->find();
                            if(empty($temp_school)){
                                $parent_demol->rollback();
                                $notice_message='该条信息有学生的学校不存在';
                                $notice_tag=1;
                                break;
                            }
                            
                            //判断该学生姓名是否重复
                            $parent_tel=$parent_data['telephone'];
                            $student_temp_result=$student_model->where("student_name="."'$v' and parent_tel="."'$parent_tel'")->field('id')->find();
                            if(!empty($student_temp_result)){
                                $parent_demol->rollback();
                                $notice_message='该条信息学生姓名相同';
                                $notice_tag=1;
                                break;
                            }

                            $student_data['student_name']=$v;
                            $student_data['parent_id']=$insert_parent_id;
                            $student_data['parent_tel']=$parent_data['telephone'];
                            $student_data['parent_name']=$parent_data['parent_name'];
                            $student_data['school_id']=$temp_school['id'];
                            $student_data['birth_date']=strtotime($student_brith_date_arr[$k]); 
                            $student_data['create_at']=time();
                            $student_data['password']=sha1('123456');

                            if(($vip_stu_id = $student_model->add($student_data))==false){
                                $notice_message='该条信息有学生保存失败';
                                $parent_demol->rollback();
                                $notice_tag=1;
                                break;
                            }
                            
                            $teach_info_map['id'] = $temp_school['id'];
                            $teach_info = M('dict_schoollist')->where( $teach_info_map )->find(); 

                            if ($teach_info['user_auth'] == 3 && time() >= $teach_info['auth_start_time'] && time() < $teach_info['auth_end_time'] ) {
                                $isVip = 1;
                            } else {
                                $isVip = 2;
                            }
                            $viplog_info = array(
                                'school_id' =>$temp_school['id'],
                                'school_admin_id' =>session('admin.id'),
                                'prent_phone' =>$parent_data['telephone'],
                                //'stu_phone' =>$student_data['parent_tel'],
                                'time' =>time(),
                                'auth_type' =>$temp_school['timetype'],
                                'teacher_phone' =>$temp_school['obligation_tel'],
                            ); 
                    
                            if($vip_config && $vip_config<=3){
                                $result=give_new_vip_operation(3, $vip_config,$vip_stu_id,$temp_school['id']);
                                if($result['status']=='failed'){
                                    $parent_demol->rollback();
                                    $notice_message='用户权限修改失败';
                                    $notice_tag=1;
                                    break;
                                }else{
                                    if($vip_config==1){
                                        M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log
                                    }elseif($vip_config==3){
                                        M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log
                                    }
                                }
                            }
                            /*if($vip_config && $vip_config<=3){
                                $vip_student['user_id'] = $vip_stu_id;
                                $vip_student['role_id'] = 3;
                                $vip_student['auth_id'] = 4;
                                $vip_student['auth_start_time'] = time();
                                $vip_student['auth_end_time'] = time()+3600*24*30*3;
                                $vip_student['timetype'] = 1; 
                                $student_where = array(
                                    'user_id' => $vip_stu_id,
                                    'role_id' => 3  

                                );
                                if($vip_config==1){
                                    //赠送90天vip
                                    $vipdata['auth_id']=4; 
                                    M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log
                                    
                                }elseif($vip_config==2){
                                    //普通权限
                                    $vipdata['auth_id']=2; 
                                    $vipdata['auth_start_time']=0;
                                    $vipdata['auth_end_time']=0;
                                    $vipdata['timetype']=0;
                                    
                                }elseif($vip_config==3){
                                    if ($isVip == 1) {
                                        $vipdata['timetype']=$temp_school['timetype'];
                                        $vipdata['auth_id']=3;  
                                        $vipdata['auth_end_time']=$temp_school['auth_end_time'];
                                        M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log
                                        
                                    }else{
                                        $vipdata['auth_id']=2; 
                                        $vipdata['auth_start_time']=0;
                                        $vipdata['auth_end_time']=0;
                                        $vipdata['timetype']=0;
                                        
                                    }
                                }
                                
                                $info_student = M('account_user_and_auth')->where($student_where )->find(); 
                                if(empty($info_student)) { 
                                    $student_id = M('account_user_and_auth')->add($vip_student); 
                                }
                            }*/
                            
                            //关于家长的操作
                            if($k==0){
                                $school_id_arr=$temp_school;
                            }else{
                                if($temp_school['user_auth']==3 && $temp_school['auth_end_time']>$school_id_arr['auth_end_time']){
                                    $school_id_arr=$temp_school;
                                }
                            }
                        }
                        if($notice_tag==0){
                            $teach_info_map['id'] = $school_id_arr['id'];
                            $teach_info = M('dict_schoollist')->where( $teach_info_map )->find(); 

                            if ($teach_info['user_auth'] == 3 && time() >= $teach_info['auth_start_time'] && time() < $teach_info['auth_end_time'] ) {
                                $isVip = 1;
                            } else {
                                $isVip = 2;
                            }

                            $viplog_info = array(
                                'school_id' =>$school_id_arr['id'],
                                'school_admin_id' =>session('admin.id'),
                                'prent_phone' =>$parent_data['telephone'],
                                //'stu_phone' =>$student_data['parent_tel'],
                                'time' =>time(),
                                'auth_type' =>$school_id_arr['timetype'],
                                'teacher_phone' =>$school_id_arr['obligation_tel'],
                            ); 
                            

                            if($vip_config && $vip_config<=3){
                                $result=give_new_vip_operation(4,$vip_config, $insert_parent_id,0); 
                                if($result['status']=='failed'){
                                    $parent_demol->rollback();
                                    $notice_message='用户权限修改失败';
                                    $notice_tag=1;
                                }else{
                                    if($vip_config==1){
                                        M('vip_and_auth_import')->add( $viplog_info );
                                    }elseif($vip_config==3){
                                        M('vip_and_auth_import')->add( $viplog_info );
                                    }
                                }
                            }
                            
                            /*if($vip_config && $vip_config<=3){
                                $vip_parent['user_id'] = $insert_parent_id;
                                $vip_parent['role_id'] = 4;
                                $vip_parent['auth_id'] = 4;
                                $vip_parent['auth_start_time'] = time();
                                $vip_parent['auth_end_time'] = time()+3600*24*30*3;
                                $vip_parent['timetype'] = 1; 
                                $parent_where = array(
                                    'user_id' => $insert_parent_id,
                                    'role_id' => 4
                                );
                                
                                if($vip_config==1){
                                    //赠送90天vip
                                    $vipdata['auth_id']=4; 
                                    M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log
                                    
                                }elseif($vip_config==2){
                                    //普通权限
                                    $vipdata['auth_id']=2; 
                                    $vipdata['auth_start_time']=0;
                                    $vipdata['auth_end_time']=0;
                                    $vipdata['timetype']=0;
                                    
                                }elseif($vip_config==3){
                                    if ($isVip == 1) {
                                        $vipdata['timetype']=$teach_info['timetype'];
                                        $vipdata['auth_id']=3;  
                                        $vipdata['auth_end_time']=$teach_info['auth_end_time'];
                                        M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log
                                        
                                    }else{
                                        $vipdata['auth_id']=2; 
                                        $vipdata['auth_start_time']=0;
                                        $vipdata['auth_end_time']=0;
                                        $vipdata['timetype']=0;
                                        
                                    }
                                }
                                
                                $info_parent = M('account_user_and_auth')->where($parent_where )->find(); 
                                if(empty($info_parent)) { 
                                     M('account_user_and_auth')->add($vip_parent); 
                                }
                            }*/ 
                            
                            
                            $parent_demol->commit();
                        }
                    }else{
                        $parent_demol->rollback();
                        $notice_message='该条信息学生的数据不一致';
                        $notice_tag=1;
                    }
                }else{
                    $parent_demol->rollback();
                }
            }else{
                //角色3
                //按照|拆分孩子信息,并且判断3个数组的长度是否一致
                $school_model=M('dict_schoollist');
                $school_id=session('admin.school_id');
                $temp_school=$school_model->where("id=".$school_id)->field('id,user_auth,auth_start_time,auth_end_time,timetype,obligation_tel')->find();
                
                $school_id_arr=array();
                $student_name= $is_utf8?iconv('gb2312', 'utf-8', $data[$i][3]):$data[$i][3];
                $student_sex= $is_utf8?iconv('gb2312', 'utf-8', $data[$i][4]):$data[$i][4];
                $student_brith_date= $is_utf8?iconv('gb2312', 'utf-8', $data[$i][5]):$data[$i][5]; 

                $student_name_arr=explode('|',$student_name); 
                $student_brith_date_arr=explode('|',$student_brith_date);
                $student_sex_arr=explode('|',$student_sex);
                        
                if($notice_tag==0){
                    if(count($student_name_arr)==count($student_brith_date_arr) && count($student_name_arr)==count($student_sex_arr)){ 
                        foreach($student_name_arr as $k=>$v){
                            if($student_sex_arr[$k]!='男' && $student_sex_arr[$k]!='女'){
                                $notice_message='该条信息有学生的性别填写不正确';
                                $parent_demol->rollback();
                                $notice_tag=1;
                                break;
                            }
                            
                            //判断该学生姓名是否重复
                            $parent_tel=$parent_data['telephone'];
                            $student_temp_result=$student_model->where("student_name="."'$v' and parent_tel="."'$parent_tel'")->field('id')->find();
                            if(!empty($student_temp_result)){
                                $parent_demol->rollback();
                                $notice_message='该条信息学生姓名相同';
                                $notice_tag=1;
                                break;
                            }
                            
                            $student_data['student_name']=$v;
                            $student_data['parent_id']=$insert_parent_id;
                            $student_data['parent_tel']=$parent_data['telephone'];
                            $student_data['parent_name']=$parent_data['parent_name'];
                            $student_data['school_id']=session('admin.school_id');
                            $student_data['birth_date']=strtotime($student_brith_date_arr[$k]); 
                            $student_data['create_at']=time();
                            $student_data['password']=sha1('123456');
                            
                            
                            if(($vip_stu_id = $student_model->add($student_data))==false){
                                $notice_message='该条信息有学生保存失败';
                                $parent_demol->rollback();
                                $notice_tag=1;
                                break;
                            } else {
                                $viplog_info = array(
                                    'school_id' =>session('admin.school_id'),
                                    'school_admin_id' =>session('admin.id'),
                                    'prent_phone' =>$parent_data['telephone'],
                                    //'stu_phone' =>$student_data['parent_tel'],
                                    'time' =>time(),
                                    'auth_type' =>$temp_school['timetype'],
                                    'teacher_phone' =>$temp_school['obligation_tel'],
                                ); 
                    
                                if($vip_config && $vip_config<=3){
                                    $result=give_new_vip_operation(3, $vip_config,$vip_stu_id,session('admin.school_id'));
                                    if($result['status']=='failed'){
                                        $parent_demol->rollback();
                                        $notice_message='用户权限修改失败';
                                        $notice_tag=1;
                                        break;
                                    }else{
                                        if($vip_config==1){
                                            M('vip_and_auth_import')->add( $viplog_info );
                                        }elseif($vip_config==3){
                                            M('vip_and_auth_import')->add( $viplog_info );
                                        }
                                    }
                                }
                                
                                /*if($vip_config && $vip_config<=3){
                                    $vip_student['user_id'] = $vip_stu_id;
                                    $vip_student['role_id'] = 3;
                                    $vip_student['auth_id'] = 4;
                                    $vip_student['auth_start_time'] = time();
                                    $vip_student['auth_end_time'] = time()+3600*24*30*3;
                                    $vip_student['timetype'] = 1; 
                                    $student_where = array(
                                        'user_id' => $vip_stu_id,
                                        'role_id' => 3  

                                    );
                                    if($vip_config==1){
                                        //赠送90天vip
                                        $vipdata['auth_id']=4; 
                                        M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log

                                    }elseif($vip_config==2){
                                        //普通权限
                                        $vipdata['auth_id']=2; 
                                        $vipdata['auth_start_time']=0;
                                        $vipdata['auth_end_time']=0;
                                        $vipdata['timetype']=0;

                                    }elseif($vip_config==3){
                                        if ($isVip == 1) {
                                            $vipdata['timetype']=$temp_school['timetype'];
                                            $vipdata['auth_id']=3;  
                                            $vipdata['auth_end_time']=$temp_school['auth_end_time'];
                                            M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log

                                        }else{
                                            $vipdata['auth_id']=2; 
                                            $vipdata['auth_start_time']=0;
                                            $vipdata['auth_end_time']=0;
                                            $vipdata['timetype']=0;

                                        }
                                    }

                                    $info_student = M('account_user_and_auth')->where($student_where )->find(); 
                                    if(empty($info_student)) { 
                                        $student_id = M('account_user_and_auth')->add($vip_student); 
                                    }
                                }*/ 
                                
                            }
                        }
                        if($notice_tag==0){ 
                            
                            $viplog_info = array(
                                'school_id' =>session('admin.school_id'),
                                'school_admin_id' =>session('admin.id'),
                                'prent_phone' =>$parent_data['telephone'],
                                'time' =>time(),
                                'auth_type' =>$temp_school['timetype'],
                                'teacher_phone' =>$temp_school['obligation_tel'],
                            );   
                              if($vip_config && $vip_config<=3){
                                    $result=give_new_vip_operation(4,$vip_config, $insert_parent_id,0); 
                                    if($result['status']=='failed'){
                                        $parent_demol->rollback();
                                        $notice_message='用户权限修改失败';
                                        $notice_tag=1;
                                    }else{
                                        
                                        if($vip_config==1){
                                            M('vip_and_auth_import')->add( $viplog_info );
                                        }elseif($vip_config==3){
                                            M('vip_and_auth_import')->add( $viplog_info );
                                        } 
                                    }
                              }
                            
                            /*if($vip_config && $vip_config<=3){
                                $vip_parent['user_id'] = $insert_parent_id;
                                $vip_parent['role_id'] = 4;
                                $vip_parent['auth_id'] = 4;
                                $vip_parent['auth_start_time'] = time();
                                $vip_parent['auth_end_time'] = time()+3600*24*30*3;
                                $vip_parent['timetype'] = 1; 
                                $parent_where = array(
                                    'user_id' => $insert_parent_id,
                                    'role_id' => 4
                                );
                                
                                if($vip_config==1){
                                    //赠送90天vip
                                    $vipdata['auth_id']=4; 
                                    M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log
                                    
                                }elseif($vip_config==2){
                                    //普通权限
                                    $vipdata['auth_id']=2; 
                                    $vipdata['auth_start_time']=0;
                                    $vipdata['auth_end_time']=0;
                                    $vipdata['timetype']=0;
                                    
                                }elseif($vip_config==3){
                                    if ($isVip == 1) {
                                        $vipdata['timetype']=$temp_school['timetype'];
                                        $vipdata['auth_id']=3;  
                                        $vipdata['auth_end_time']=$temp_school['auth_end_time'];
                                        M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log
                                        
                                    }else{
                                        $vipdata['auth_id']=2; 
                                        $vipdata['auth_start_time']=0;
                                        $vipdata['auth_end_time']=0;
                                        $vipdata['timetype']=0;
                                        
                                    }
                                }
                                
                                $info_parent = M('account_user_and_auth')->where($parent_where )->find(); 
                                if(empty($info_parent)) { 
                                     M('account_user_and_auth')->add($vip_parent); 
                                }
                            }*/
                    
                            
                            $parent_demol->commit();
                        }
                    }else{
                        $parent_demol->rollback();
                        $notice_message='该条信息学生的数据不一致';
                        $notice_tag=1;
                    } 
                }else{
                    $parent_demol->rollback();
                } 
            }
                
            if($notice_tag==1){ 
                $notice_temp_arr=array();
                $notice_temp_arr[]=$parent_data['parent_name'];
                $notice_temp_arr[]=$parent_data['telephone'];
                $notice_temp_arr[]=$parent_data['sex']; 
                $notice_temp_arr[]=$student_name;
                $notice_temp_arr[]=$student_sex;
                if(session('admin.role')==1){
                    $notice_temp_arr[]=$student_school;
                } 
                $notice_temp_arr[]=$student_brith_date;   
                $notice_temp_arr[]=$notice_message; 
                
                $notice_array[]=$notice_temp_arr;       
                continue;
            }else{
                $success_number++;
                continue;
            }
        }
        $return_array=array();
        if(!empty($notice_array)){
            $return_array['status']=1003;
        }else{
            $return_array['status']=1004;
        } 
        $return_array['all_number']=($length-1);
        $return_array['success_number']=$success_number;
        $return_array['notice_data']=$notice_array;
        echo json_encode($return_array);
    }
    
    //下载学生管理示例 
    public function downloadStudentFile(){
        $csv=new CSV();
        $file="Public/csv/studentDemo01.csv";
        $csv->downloadFile($file);
    }
    
    /*已注释*/
    //批量导出
    public function exportedStudent(){
        if (!session('?admin')) redirect(U('Index/index'));
        if(empty($_POST)){
            $this->error('参数错误');
        }else{ 
            $Model = M('auth_student');
            $condition_arr=I('hid');
            $string='('.rtrim(implode($condition_arr,','),',').')';     
            $result = $Model->where("auth_student.flag=1 and auth_student.id in $string")
            ->join('dict_schoollist on auth_student.school_id=dict_schoollist.id','left')
            ->field('dict_schoollist.school_name,auth_student.id,auth_student.student_id,auth_student.student_name,'
                    . 'auth_student.birth_date,auth_student.create_at,auth_student.email,auth_student.parent_name,'
                    . 'auth_student.parent_tel,auth_student.sex')
            ->order('auth_student.create_at desc') 
            ->select(); 
            $str="学生姓名,性别,学生家长,学校,学号,出生年月,注册时间,邮箱,家长手机号\n"; 
            $str=iconv('utf-8','gb2312', $str);
            foreach($result as $v){
                $name=iconv('utf-8','gbk', $v['student_name']);
                $sex=iconv('utf-8','gb2312', $v['sex']);
                $parent_name=iconv('utf-8','gbk', $v['parent_name']);
                $school_name=iconv('utf-8','gbk', $v['school_name']);
                $student_id=iconv('utf-8','gb2312', $v['student_id']);
                $birth_date=iconv('utf-8','gb2312', date('Y-m-d ',$v['birth_date']));
                $create_at=iconv('utf-8','gb2312', date('Y-m-d H:i',$v['create_at'])); 
                $email=iconv('utf-8','gb2312', $v['email']);
                $parent_tel=iconv('utf-8','gb2312', $v['parent_tel']);
                $str.=$name.",".$sex.",".$parent_name.",".$school_name.",".$student_id.",".$birth_date.",".$create_at.",".$email.",".$parent_tel."\n";
            }
            $filename=date('Ymd').rand(0,1000).'student'.'.csv';
            $csv=new CSV();
            $csv->downloadFileCsv($filename,$str);
        }
    }

    public function exportedStudentAll(){
        set_time_limit(0);
        if (!session('?admin')) redirect(U('Index/index'));

        $filter['keyword'] = $_REQUEST['keyword'];
        $filter['status'] = $_REQUEST['status'];
        $keyword = $filter['keyword'];
        $status=intval($filter['status']);
        if (!empty($keyword)) {
            $where=array(
                array(
                    'auth_student.student_name'=>array('like', '%' . $keyword . '%'),
                    'auth_student.student_id'=>array('like', '%' . $keyword . '%'),
                    'auth_student.parent_tel'=>array('like', '%' . $keyword . '%'),
                    '_logic'=>'OR'
                )
            );
            $this->assign('keyword', $keyword);
        }
        $where['auth_student.flag']=1;

        if(session('admin.role')!=3){
            $filter['province'] = $_REQUEST['province'];
            $filter['city'] = $_REQUEST['city'];
            $filter['district'] = $_REQUEST['district'];
            $filter['school'] = $_REQUEST['school'];
            $filter['grade_id'] = $_REQUEST['grade_id'];

            if (!empty($filter['province'])) $where['dict_schoollist.provice_id'] = $filter['province'];
            if (!empty($filter['city'])) $where['dict_schoollist.city_id'] = $filter['city'];
            if (!empty($filter['district'])) $where['dict_schoollist.district_id'] = $filter['district'];
            if (!empty($filter['school'])) $where['dict_schoollist.id'] = $filter['school'];
            if (!empty($filter['grade_id'])) $where['biz_class.grade_id'] = $filter['grade_id'];

        };

        $Model = M('auth_student');
        
        if(session('admin.role')==3){
            $where['dict_schoollist.id']=session('admin.school_id');
        }
        $result = $Model->where($where)
            ->join('left join dict_schoollist on auth_student.school_id=dict_schoollist.id')
            ->join('left join biz_class_student on biz_class_student.student_id=auth_student.id')
            ->join('left join biz_class on biz_class.id=biz_class_student.class_id')
            ->join('left join auth_parent on auth_student.parent_id = auth_parent.id')
            ->field('dict_schoollist.school_name,auth_student.id,auth_student.student_id,auth_student.student_name,'
                . 'auth_student.birth_date,auth_student.create_at,auth_student.email,auth_student.parent_name,'
                . 'auth_student.parent_tel,auth_student.sex')
            ->group('auth_student.id')
            ->order('auth_student.create_at desc')
            ->select();

        $str="学生姓名,性别,学生家长,学校,学号,出生年月,注册时间,邮箱,家长手机号\n";
        $str=iconv('utf-8','gb2312', $str);
        foreach($result as $v){
            $name=iconv('utf-8','gbk', $v['student_name']);
            $sex=iconv('utf-8','gb2312', $v['sex']);
            $parent_name=iconv('utf-8','gbk', $v['parent_name']);
            $school_name=iconv('utf-8','gbk', $v['school_name']);
            $student_id=iconv('utf-8','gb2312', $v['student_id']);
            $birth_date=iconv('utf-8','gb2312', date('Y-m-d ',$v['birth_date']));
            $create_at=iconv('utf-8','gb2312', date('Y-m-d H:i',$v['create_at']));
            $email=iconv('utf-8','gb2312', $v['email']);
            $parent_tel=iconv('utf-8','gb2312', $v['parent_tel']);
            $str.=$name.",".$sex.",".$parent_name.",".$school_name.",".$student_id.",".$birth_date.",".$create_at.",".$email.",".$parent_tel."\n";
        }
        $filename=date('Ymd').rand(0,1000).'student'.'.csv';
        $csv=new CSV();
        $csv->downloadFileCsv($filename,$str);

    }
    
    /*注释了
    //批量导入学生
    public function importStudent(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        if(empty($_FILES)){
            echo 1001;die;
        }
        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){
            echo $result;
            die();
        }
        $data=$result['result'];
        $length=$result['length'];
        $data_values=''; 
        $model=M('auth_student');
        $parent_demol=M('auth_parent');    
        $error='success';
        
        //查询某个手机号是否存在,不存在则跳过改行
        for($i=1;$i<$length;$i++){
            $add_data['student_name'] = iconv('gb2312', 'utf-8', $data[$i][0]);
            $add_data['sex'] = iconv('gb2312', 'utf-8', $data[$i][1]);
            $add_data['parent_name'] = iconv('gb2312', 'utf-8', $data[$i][2]);
            $add_data['student_id'] = iconv('gb2312', 'utf-8', $data[$i][3]);
            $add_data['birth_date'] = strtotime(iconv('gb2312', 'utf-8', $data[$i][4]));
            //$add_data['create_at'] = strtotime(iconv('gb2312', 'utf-8', $data[$i][5]));
            $add_data['create_at'] = time();
            $add_data['email'] = iconv('gb2312', 'utf-8', $data[$i][6]);
            $add_data['parent_tel'] = iconv('gb2312', 'utf-8', $data[$i][7]);
            $parent_tel=$add_data['parent_tel'];
            $parent_result=$parent_demol->where("telephone="."'$parent_tel'")->field('id,parent_name,telephone')->find();   
            if(!empty($parent_result)){
                $add_data['parent_id']=$parent_result['id'];
                $add_data['parent_name']=$parent_result['parent_name'];
                $add_data['parent_tel']=$parent_result['telephone'];
            }
                //这里判断这个学生是否存在
                $student_id=$add_data['student_id'];
                $student_result=$model->where("student_id="."'$student_id'")->field('id')->find();

                if(!empty($student_result)){
                    $student_primary_id=$student_result['id'];
                    $model->where("id=$student_primary_id")->save($add_data);
                }else{ 
                    $model->add($add_data);
                } 

        }
        //echo 'success';
        echo $error;
    }
    */

    //学生管理 列表
        public function studentMgmt()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '用户管理');
        $this->assign('nav', '学生管理');
        $this->assign('subnav', '学生列表');

        $filter['keyword'] = $_REQUEST['keyword'];
        $filter['status'] = $_REQUEST['status'];
        $keyword = $filter['keyword'];
        $status=intval($filter['status']);
        if (!empty($keyword)) {
            $where=array(
                array(
                    'auth_student.student_name'=>array('like', '%' . $keyword . '%'),
                    'auth_student.student_id'=>array('like', '%' . $keyword . '%'),
                    'auth_student.parent_tel'=>array('like', '%' . $keyword . '%'),
                    '_logic'=>'OR'
                )
            ); 
            $this->assign('keyword', $keyword); 
        }
        
        $where['auth_student.flag']=1;
        if(session('admin.role')!=3){ 
            $filter['province'] = $_REQUEST['province'];
            $filter['city'] = $_REQUEST['city'];
            $filter['district'] = $_REQUEST['district'];  
            $filter['school'] = $_REQUEST['school'];
            $filter['grade_id'] = $_REQUEST['grade_id'];
                    
            if (!empty($filter['province'])) $where['dict_schoollist.provice_id'] = $filter['province'];
            if (!empty($filter['city'])) $where['dict_schoollist.city_id'] = $filter['city'];
            if (!empty($filter['district'])) $where['dict_schoollist.district_id'] = $filter['district'];
            if (!empty($filter['school'])) $where['dict_schoollist.id'] = $filter['school'];
            if (!empty($filter['grade_id'])) $where['biz_class.grade_id'] = $filter['grade_id'];

        };



        $this->assign('grade_id_info', $filter['grade_id']);

        $where_condition='';
        foreach($filter as $key=>$val){
            $where_condition.='&'.$key.'='.$val;
        } 
        $this->assign('condition_str',$where_condition);
        
                    
        if (session('admin.role') == 3) {
            $where['auth_student.school_id'] = session('admin.school_id'); 
        }
        if(!empty($status)){  
            $where['auth_student.lock']=$status-1;
            $this->assign('status', $status);
        } 

        $Model = M('auth_student'); 
        $count = $Model
            ->join('left join dict_schoollist on auth_student.school_id=dict_schoollist.id')
            ->join('left join biz_class_student on biz_class_student.student_id=auth_student.id')
            ->join('left join biz_class on biz_class.id=biz_class_student.class_id')
            ->join('left join auth_parent on auth_student.parent_id = auth_parent.id') 
            ->field('dict_schoollist.school_name,auth_student.id,auth_student.student_id,auth_student.student_name,'
                    . 'auth_student.birth_date,auth_student.create_at,auth_student.email,auth_parent.parent_name,'
                    . 'auth_student.parent_tel,auth_student.lock,auth_student.flag')
            ->group('auth_student.id')
            ->order('auth_student.create_at desc')
            ->where($where)            
            ->field('auth_student.id')
            ->select();     
        $count=count($count);
                   

        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));  
        $show = $Page->show();  
        $result = $Model
            ->join('left join dict_schoollist on auth_student.school_id=dict_schoollist.id')
            ->join('left join biz_class_student on biz_class_student.student_id=auth_student.id')
            ->join('left join biz_class on biz_class.id=biz_class_student.class_id')
            ->join('left join auth_parent on auth_student.parent_id = auth_parent.id')
            ->field('dict_schoollist.school_name,auth_student.id,auth_student.student_id,auth_student.student_name,'
                . 'auth_student.birth_date,auth_student.create_at,auth_student.email,auth_parent.parent_name,'
                . 'auth_student.parent_tel,auth_student.lock,auth_student.flag,auth_student.sex,dict_schoollist.status')
        ->group('auth_student.id')
        ->order('auth_student.create_at desc')
        ->where($where)
        ->limit($Page->firstRow . ',' . $Page->listRows)
        ->select();              
        

        $auth_type_use = D('Account_auths');
        foreach ($result as $key => $value) {
            $isVipInfo = $auth_type_use->isVipInfo($value['id'],3);
            $result[$key]['vipinfo'] = $isVipInfo;
        }

        
                    
        $this->assign('role', session('admin.role'));
        $this->assign('list', $result);
        $this->assign('page', $show);

        
        //条件是否存在 
        if(!empty($filter['province'])) $check2['dict_schoollist.provice_id'] = intval($_GET['province']); 
        if(!empty($filter['city'])) $check2['dict_schoollist.city_id'] = intval($_GET['city']);
        if(!empty($filter['district'])) $check2['dict_schoollist.district_id'] = intval($_GET['district']);
        if(!empty($filter['school'])) $check2['dict_schoollist.id'] = intval($_GET['school']); 
        
        
        //取出所有省份 
        $register_check['social_activity_register.activity_id']=$id;
        $social_activity_register=M('social_activity_register'); 
        $district_model=M('dict_citydistrict');
        $province=$district_model->field('id,name')->where("upid=0")->select();
        //省份不为空,取出市 
        if(!empty($check2['dict_schoollist.provice_id'])){  
            if($check2['dict_schoollist.provice_id']==1 || $check2['dict_schoollist.provice_id']==2 || $check2['dict_schoollist.provice_id']==9 || $check2['dict_schoollist.provice_id']==22 
                    || $check2['dict_schoollist.provice_id']==33 || $check2['dict_schoollist.provice_id']==34){    
                switch ($check2['dict_schoollist.provice_id']) {        
                    case 1: 
                            $city_result[0]['id']=1;
                            $city_result[0]['name']='北京市';
                        break;
                    case 2: 
                            $city_result[0]['id']=1;
                            $city_result[0]['name']='天津市';
                        break;
                    case 9: 
                            $city_result[0]['id']=9;
                            $city_result[0]['name']='上海市';
                        break;
                    case 22: 
                            $city_result[0]['id']=22;
                            $city_result[0]['name']='重庆市';
                        break;
                    case 33: 
                            $city_result[0]['id']=33;
                            $city_result[0]['name']='香港特别行政区';
                        break;
                    case 34: 
                            $city_result[0]['id']=34;
                            $city_result[0]['name']='澳门特别行政区';
                        break;
                    default:
                        break;
                }
            }else{ 
                $city_result=$district_model->field('id,name')->where("upid=".$check2['dict_schoollist.provice_id'])->select(); 
            } 
        }
        //市不为空,取出区县
        if(!empty($check2['dict_schoollist.city_id'])){ 
            $district_result=$district_model->field('id,name')->where("upid=".$check2['dict_schoollist.city_id'])->select(); 
        } 
        //区县不为空,取出学校
        if(!empty($check2['dict_schoollist.district_id'])){
            $school_model=M('dict_schoollist');
            $school_result=$school_model->field('id,school_name')->where("district_id=".$check2['dict_schoollist.district_id'])->order("school_name asc")->select();
        } 
        //学校不为空,取出年级
        if(!empty($check2['dict_schoollist.id'])){  
            $Model=M('biz_class');
            $grade_result=$Model->where("dict_schoollist.id=".$check2['dict_schoollist.id'])->join("dict_schoollist on dict_schoollist.id=biz_class.school_id")
                    ->join("dict_grade on dict_grade.id=biz_class.grade_id")
                    ->field("dict_grade.id,dict_grade.grade")->group("dict_grade.id")->select();  
            $this->assign('grades', $grade_result);
        } 
        
        
        $this->assign('province_result', $province);    
        $this->assign('city_result',$city_result);
        $this->assign('district_result',$district_result);
        $this->assign('school_result',$school_result);
        
        $this->assign('province', $check2['dict_schoollist.provice_id']);   
        $this->assign('city', $check2['dict_schoollist.city_id']);
        $this->assign('district', $check2['dict_schoollist.district_id']);
        $this->assign('school', $check2['dict_schoollist.id']);
                    
        $this->display();
    }
    
                    
    
    //删除学生操作
    public function deleteStudent(){
        if (!session('?admin')) redirect(U('Index/index'));

        $Model = M('auth_student'); 
        $id =intval(I('id'));
        if(!$id){
            $this->ajaxReturn('failed'); 
        } 
        $where['id']=$id;
        $data['flag'] = -1; 
        if(session('admin.role') == 3){
            $where['school_id']=session('admin.school_id');
        }
        if($Model->where($where)->save($data)){
            $this->ajaxReturn('success');
        }else{
            $this->ajaxReturn('failed');
        } 
        
    }

    //锁定学生账号
    public function lockStudentAccount()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        if(session('admin.role') == 3){
            echo 'error';die;
        }
        
        $id = $_GET['id'];
        $Model = M('auth_student');
        $result=$Model->where('id='.$id)->field('id,student_name,parent_id')->find();  
        if(empty($result)){
            echo 'error';die;
        }
        $student_name=$result['student_name'];
        $parent_id=$result['parent_id'];
        
        $data['lock'] = 1;

        $Model->where("id=$id")->save($data);

        //手机推送
        $parameter_arr=array(
            'msg'=>array($student_name,date('Y-m-d H:i:s')),
            'url'=>array(
                'type'=>0,
                'data'=>array()
            )
        );
        A('Home/Message')->addPushUserMessage('USER_LOCK_CHILD',4,$parent_id,$parameter_arr);
                    
        $parameters = array( 'msg' => array(date('Y-m-d H:i:s',time())) ,
            'url' => array( 'type' => 0)
        );
        A('Home/Message')->addPushUserMessage('USER_LOCK',3,$id,$parameters); 
        
        $this->ajaxReturn('success');
    }

    //解锁学生账号
    public function unlockStudentAccount()
    {
        if (!session('?admin')) redirect(U('Index/index'));
        
        if(session('admin.role') == 3){
            echo 'error';die;
        }
        
        $id = $_GET['id'];
        $Model = M('auth_student');
        $result=$Model->where('id='.$id)->field('id,student_name,parent_id')->find();  
        if(empty($result)){
            echo 'error';die;
        }
        $student_name=$result['student_name'];
        $parent_id=$result['parent_id'];

        $data['lock'] = 0;

        $Model->where("id=$id")->save($data); 
        //手机推送
        $parameter_arr=array(
            'msg'=>array($student_name,date('Y-m-d H:i:s')),
            'url'=>array(
                'type'=>0,
                'data'=>array()
            )
        );
        A('Home/Message')->addPushUserMessage('USER_UNLOCK_CHILD',4,$parent_id,$parameter_arr);
                    
        $parameters = array( 'msg' => array(date('Y-m-d H:i:s',time())) ,
            'url' => array( 'type' => 0)
        );
        A('Home/Message')->addPushUserMessage('USER_UNLOCK',3,$id,$parameters); 
        $this->ajaxReturn('success');
    }
     
    //修改家长操作
    public function modifyParentOp(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        if(empty($_POST)){
            $this->error('参数为空');
        }else{
            $Model = M('auth_parent'); 
            $studentModel = M('auth_student'); 
            $id=intval(I('id'));
            if(!$id){
                $this->error('参数错误');
            }
             
            $data['parent_id']=$id;
            $data['parent_name']=I('name');  
            $sex=I('sex');
            if($sex==1){
                $data['sex']='男';
            }else{
                $data['sex']='女';
            }
            $data['telephone']=I('telephone');
            $phone=$data['telephone'];
            $temp_result=$Model->where("id !=".$id." and telephone="."'$phone'")->field('id')->find();  
            if(!empty($temp_result)){
                $this->error('该手机号已经存在');
            }
            
            $data['email']=I('email');
            $student_where='';
            $children=I('children'); 
            if(!empty($children)){
                $children_ids=rtrim(implode($children, ','),',');
                $student_where="id in ($children_ids)";
            }
            
            $Model->startTrans();
            $student_data['parent_id']='';
            $student_data['parent_name']='';
            $student_data['parent_tel']='';
            
            
            $after_data['parent_id']=$data['parent_id'];
             
            
            //判断父ID是否存在
            $temp_result=$Model->where("id=".$id)->field('id,parent_name')->find();
            if(empty($temp_result)){
                $this->error('参数错误');
            }
            $after_data['parent_name']=$data['parent_name'];
            $after_data['parent_tel']=$data['telephone'];
            
            //先查出父ID的所有学生
            $student_result=$studentModel->where("parent_id=".$id)->field('id')->select();           
            if(!empty($student_result)){
                $str="(";
                foreach($student_result as $v){
                    $str.=$v['id'].',';
                }
                $str=rtrim($str,',');
                $str.=")";
                $student_temp_where="id in ".$str;
            }else{
                $student_temp_where='';
            } 
            $Model->startTrans();
            
            if($Model->where("id=$id")->save($data)===false){
               $Model->rollback();
               $this->error('数据提交失败');
            }
          
            if($student_temp_where!=''){
                if($studentModel->where($student_temp_where)->save($student_data)===false){ 
                    $Model->rollback();
                    $this->error('数据提交失败');
                }
            } 
            if($student_where!=''){
                if($studentModel->where($student_where)->save($after_data)===false){
                   $Model->rollback();
                   $this->error('数据提交失败');
                } 
            }
            $Model->commit();
            redirect(U('Admin/parentMgmt'));
        }
    }
    
    //修改家长信息视图
    public function modifyParentView(){     
        if (!session('?admin')) redirect(U('Index/index'));
        
        $this->assign('module', '用户管理');
        $this->assign('nav', '家长管理');
        $this->assign('subnav', '修改家长信息');
        
        $id=I('id');
        $this->assign('id', $id);
        
        $Model = M('auth_parent'); 
        $student_Model = M('auth_student'); 
        $result = $Model     
            ->field("auth_parent.id,auth_parent.parent_name,auth_parent.telephone,auth_parent.create_at,auth_parent.email"
                    . ",auth_parent.lock,auth_parent.flag,auth_parent.sex,auth_parent.password")->where("auth_parent.id="."'$id'")
            ->find();       
        
        $student_result=$student_Model     
            ->where("auth_parent.id="."'$id'")
            ->join("auth_parent on auth_parent.id=auth_student.parent_id")
            ->field("auth_student.id,auth_student.student_name")
            ->select(); 
        
        $student_all=$student_Model       
            ->field("auth_student.id,auth_student.student_name")
            ->select(); 
        
        
        $this->assign('student_result', $student_result);
        $this->assign('data', $result);
        $this->assign('student_all', $student_all);

        
        //查询用户的权限
        $account_model=D('Account_auths');
        $list=$account_model->getUserVipAuthAll($id,4);   
        $account_list=$account_model->getAuthList(); 
        
        $this->assign('user_auth',$list);
        $this->assign('account_list',$account_list);
        $this->display();
    }
    
    //删除家长操作
    public function deleteParent(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        $id=intval(I('id'));
        $where['id']=$id;
        if (session('admin.role') == 3) { 
            $this->ajaxReturn('failed');
        }else{
            $Model = M('auth_parent');
            $data['flag']=-1;
            if($Model->where($where)->save($data)){
                $this->ajaxReturn('success');
            }else{
                $this->ajaxReturn('failed');
            } 
        }  
    }
    
    
    //下载家长的示例文件 
    public function downloadParentFile(){
        $csv=new CSV();
        $file="Public/csv/parentDemo01.csv";
        $csv->downloadFile($file);
    }
     
    //批量导出家长的信息
    public function exportedParent(){
        if (!session('?admin')) redirect(U('Index/index'));
        if(empty($_POST)){
            $this->error('参数错误');
        }else{ 
            $Model = M('auth_parent');
            
            $condition_arr=I('hid');
            $string='('.rtrim(implode($condition_arr,','),',').')'; 
            
            $result = $Model->where("auth_parent.flag=1 and auth_parent.id in $string")
            ->join("auth_student on auth_parent.id=auth_student.parent_id","left")
            ->group("auth_parent.id") 
            ->field("auth_parent.id,auth_parent.parent_name,auth_parent.telephone,auth_parent.create_at,auth_parent.email"
                    . ",auth_parent.lock,auth_parent.flag,auth_parent.sex,group_concat(auth_student.student_name SEPARATOR'|') student_name")
            ->order('auth_parent.create_at desc')
            ->select();     
            
            $str="家长姓名,性别,小孩姓名,手机号,注册时间,邮箱\n";
            $str=iconv('utf-8','gb2312', $str);
            foreach($result as $v){
                $parent_name=iconv('utf-8','gbk', $v['parent_name']);
                $sex=iconv('utf-8','gb2312', $v['sex']);
                $student_name=iconv('utf-8','gbk', $v['student_name']);
                $telephone=iconv('utf-8','gb2312', $v['telephone']);
                $create_at=iconv('utf-8','gb2312', date('Y-m-d H:i',$v['create_at'])); 
                $email=iconv('utf-8','gb2312', $v['email']); 
                $str.=$parent_name.",".$sex.",".$student_name.",".$telephone.",".$create_at.",".$email."\n";
            } 
            $filename=date('Ymd').rand(0,1000).'parent'.'.csv';
            $csv=new CSV();
            $csv->downloadFileCsv($filename,$str);
            
        }
    }

    //批量导出家长的信息
    public function exportedParentAll(){
        set_time_limit(0); 
        if (!session('?admin')) redirect(U('Index/index'));
        $where['auth_parent.flag']=1;
        if($_GET['status'])
         $where['auth_parent.lock'] = $_GET['status']-1;
        if($_GET['keyword']){
            $where = array(
                array(
                    'auth_parent.parent_name' => array('like', '%' . $_GET['keyword'] . '%'),
                    'auth_parent.telephone' => array('like', '%' . $_GET['keyword'] . '%'),
                    '_logic' => 'or',
                ),
            ); 
        }
        if (session('admin.role') == 3){
            $where['auth_student.school_id'] = session('admin.school_id');
        }else{
            $filter['province'] = $_REQUEST['province'];
            $filter['city'] = $_REQUEST['city'];
            $filter['district'] = $_REQUEST['district'];  
            $filter['school'] = $_REQUEST['school'];
            
            if (!empty($filter['province'])) $where['dict_schoollist.provice_id'] = intval($filter['province']);
            if (!empty($filter['city'])) $where['dict_schoollist.city_id'] = intval($filter['city']);
            if (!empty($filter['district'])) $where['dict_schoollist.district_id'] = intval($filter['district']);
            if (!empty($filter['school'])) $where['dict_schoollist.id'] = intval($filter['school']);
        }
                    
        $Model = M('auth_parent');
        $result = $Model->join("auth_student on auth_parent.id=auth_student.parent_id","left")
                    ->join("dict_schoollist on dict_schoollist.id=auth_student.school_id") 
                    ->where($where)
                    ->order('create_at desc')
                    ->group("auth_parent.id")
                    ->field("auth_parent.id,auth_parent.parent_name,auth_parent.telephone,auth_parent.create_at,auth_parent.email"
                        . ",auth_parent.lock,auth_parent.flag,auth_parent.sex,group_concat(auth_student.student_name SEPARATOR'|') student_name")
                    ->select();     

        $str="家长姓名,性别,小孩姓名,手机号,注册时间,邮箱\n";
        $str=iconv('utf-8','gb2312', $str);
        foreach($result as $v){
            $parent_name=iconv('utf-8','gbk', $v['parent_name']);
            $sex=iconv('utf-8','gb2312', $v['sex']);
            $student_name=iconv('utf-8','gbk', $v['student_name']);
            $telephone=iconv('utf-8','gb2312', $v['telephone']);
            $create_at=iconv('utf-8','gb2312', date('Y-m-d H:i',$v['create_at']));
            $email=iconv('utf-8','gb2312', $v['email']);
            $str.=$parent_name.",".$sex.",".$student_name.",".$telephone.",".$create_at.",".$email."\n";
        }
        $filename=date('Ymd').rand(0,1000).'parent'.'.csv';
        $csv=new CSV();
        $csv->downloadFileCsv($filename,$str);

    }
    
    /*注释了
    //导入家长的示例文件  1001参数为空 1002数据为空 1003插入家长失败 1004插入学生表失败
    public function importParent(){
        if (!session('?admin')) redirect(U('Index/index'));
         
        if(empty($_FILES)){
            echo 1001;die;
        }
        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){
            echo $result;
            die();
        }
        $data=$result['result'];    
        $length=$result['length'];
        $data_values=''; 
        
        $model=M('auth_parent');
        $student_model=M('auth_student'); 
        $error='success';
        
        for($i=1;$i<$length;$i++){
            $data['parent_name']=iconv('gb2312', 'utf-8', $data[$i][0]);
            $data['sex']=iconv('gb2312', 'utf-8', $data[$i][1]);
            $childrens=iconv('gb2312', 'utf-8', $data[$i][2]);      
            $phone=iconv('gb2312', 'utf-8', $data[$i][3]);
            
            //$data['create_at']=strtotime(iconv('gb2312', 'utf-8', $data[$i][4]));\
            $data['create_at']=time();
            $data['email']=iconv('gb2312', 'utf-8', $data[$i][5]);
            
            //这里先判断某个手机号是否存在,存在则修改改行
            $temp_result=$model->where("telephone="."'$phone'")->field('id')->find();   
            $model->startTrans(); 
            if(!empty($temp_result)){   
                $insert_id=$temp_result['id'];
                if($model->where("id="."$insert_id")->save($data)===false){    
                    $model->rollback();
                    $error=1;
                    echo $error;die;
                }
            }else{
                $data['telephone']=$phone;
                $data['password']=sha1($phone);
                if(!($insert_id=$model->add($data))){   
                    $model->rollback();
                    $error=1;
                    echo $error;die;
                } 
            } 
            
                    
            $children_arr=explode('|',$childrens);      
            //这里的逻辑是如果找到了小孩就插入
            
            if(count($children_arr)>0){
                for($j=0;$j<count($children_arr);$j++){
                    $where_student['student_name'] = $children_arr[$j];    

                    if($student_result=$student_model->where($where_student)->field('id')->find()){    
                        $temp_data['parent_id']=$insert_id; 
                        $temp_data['parent_name']=$data['parent_name'];
                        $temp_data['parent_tel']=$data['telephone'];

                        $temp_where=$student_result['id'];  
                        if($student_model->where("id=".$temp_where)->save($temp_data)===false){ 
                            $model->rollback();
                            $error=1;
                            echo $error;die;
                        }
                    }else{      
                        $error=1; 
                        continue;
                    } 
                }
            }
            $model->commit();
        } 
            echo $error;
    }
     * 
     */

    //家长管理 列表
    public function parentMgmt()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '用户管理');
        $this->assign('nav', '家长管理');
        $this->assign('subnav', '家长列表');

        $page = $_REQUEST['page'];
        if (empty($page)) $page = 1;
        $this->assign('page', $page);
                    
        $filter['keyword'] = $_GET['keyword'];
        $filter['status']=intval($_GET['status']);
        if (!empty($filter['keyword'])) {
            $where = array(
                array(
                    'auth_parent.parent_name' => array('like', '%' . $filter['keyword'] . '%'),
                    'auth_parent.telephone' => array('like', '%' . $filter['keyword'] . '%'),
                    '_logic' => 'or',
                ),
            ); 
        } 
        if(!empty($filter['status'])){  
            $where['auth_parent.lock']=$filter['status']-1;
            $this->assign('status', $filter['status']);
        }
        
        if(session('admin.role')!=3){ 
            $filter['province'] = $_REQUEST['province'];
            $filter['city'] = $_REQUEST['city'];
            $filter['district'] = $_REQUEST['district'];  
            $filter['school'] = $_REQUEST['school'];
            
            
            if (!empty($filter['province'])) $where['dict_schoollist.provice_id'] = intval($filter['province']);
            if (!empty($filter['city'])) $where['dict_schoollist.city_id'] = intval($filter['city']);
            if (!empty($filter['district'])) $where['dict_schoollist.district_id'] = intval($filter['district']);
            if (!empty($filter['school'])) $where['dict_schoollist.id'] = intval($filter['school']);
        };
                    
        if (session('admin.role') == 3) {
            $where['auth_student.school_id'] = session('admin.school_id');
        }
        $where['auth_parent.flag']=1;

        $Model = M('auth_parent');

        $count = $Model->where($where)  
            ->join("auth_student on auth_parent.id=auth_student.parent_id",'left') 
            ->join("dict_schoollist on dict_schoollist.id=auth_student.school_id",'left') 
            ->field("auth_parent.id")
            ->group("auth_parent.id")
            ->select();                 
        $count=count($count);       
            
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $Page->parameter['keyword'] = $filter['keyword'];
        $Page->parameter['status'] = $status;
        $show = $Page->show();  

        //这里必须用join,牵扯到一个学校的ID
        $result = $Model
            ->where($where)
            ->join("auth_student on auth_parent.id=auth_student.parent_id",'left') 
            ->join("dict_schoollist on dict_schoollist.id=auth_student.school_id",'left') 
            ->group("auth_parent.id")
            ->order('create_at desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->field("auth_parent.id,auth_parent.parent_name,auth_parent.telephone,auth_parent.create_at,auth_parent.email"
                    . ",auth_parent.lock,auth_parent.flag,auth_parent.sex,group_concat(auth_student.student_name SEPARATOR'|') student_name")
            ->select();              
                    
        $auth_type_use = D('Account_auths');
        foreach ($result as $key => $value) {
            $isVipInfo = $auth_type_use->isVipInfo($value['id'],4);
            $result[$key]['vipinfo'] = $isVipInfo;
        }
        
        $school_model=D('Dict_schoollist');
        $province_list=$school_model->getProvince(); 
        if(!empty($where['dict_schoollist.provice_id'])){
            $city_list=$school_model->getCityByProvince($where['dict_schoollist.provice_id']);
        }
        if(!empty($where['dict_schoollist.city_id'])){
            $district_list=$school_model->getDistrictByCity($where['dict_schoollist.city_id']);
        }
        if(!empty($where['dict_schoollist.district_id'])){
            $school_list=$school_model->getSchoolByDistrict($where['dict_schoollist.district_id']);
        } 
        
        foreach($filter as $key=>$val){
            $where_condition.='&'.$key.'='.$val;
        }
        
        $this->assign('list', $result);
        
        $this->assign('province_list', $province_list);
        $this->assign('city_list', $city_list);
        $this->assign('district_list', $district_list);
        $this->assign('school_list', $school_list);
        
        $this->assign('province', $where['dict_schoollist.provice_id']);   
        $this->assign('city', $where['dict_schoollist.city_id']);
        $this->assign('district', $where['dict_schoollist.district_id']);
        $this->assign('school', $where['dict_schoollist.id']);
        
        $this->assign('keyword', $filter['keyword']);
        $this->assign('page', $show);
        $this->assign('condition_str',$where_condition);
        $this->display();
    }

    //锁定家长账号
    public function lockParentAccount()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        if (session('admin.role') == 3) {
            echo 'error';die;
        }
        
        $id = $_GET['id'];
        $Model = M('auth_parent');

        $data['lock'] = 1;

        $Model->where("id=$id")->save($data);
        
        //手机推送
        $parameter_arr=array(
            'msg'=>array(date('Y-m-d H:i:s')),
            'url'=>array(
                'type'=>0,
                'data'=>array()
            )
        );
        $parent_id=$id;
        A('Home/Message')->addPushUserMessage('USER_LOCK',4,$parent_id,$parameter_arr);  
        
        $this->ajaxReturn('success');
    }

    //解锁家长账号
    public function unlockParentAccount()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        if (session('admin.role') == 3) {
            echo 'error';die;
        }
        
        $id = $_GET['id'];
        $Model = M('auth_parent');

        $data['lock'] = 0;

        $Model->where("id=$id")->save($data);
        //手机推送
        $parameter_arr=array(
            'msg'=>array(date('Y-m-d H:i:s')),
            'url'=>array(
                'type'=>0,
                'data'=>array()
            )
        );
        $parent_id=$id;
        A('Home/Message')->addPushUserMessage('USER_UNLOCK',4,$parent_id,$parameter_arr);

        $this->ajaxReturn('success');
    }

    //电子课本列表
    public function textbookList()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '电子课本');
        $this->assign('nav', '电子课本');
        $this->assign('subnav', '列表');

        $filter['course_id'] = $_REQUEST['course_id'];
        $filter['grade_id'] = $_REQUEST['grade_id'];
        $filter['textbook_id'] = $_REQUEST['textbook_id'];
        
        if (!empty($filter['course_id'])) $where['biz_textbook.course_id'] = $filter['course_id'];
        if (!empty($filter['grade_id'])) $where['biz_textbook.grade_id'] = $filter['grade_id'];
        if (!empty($filter['textbook_id'])) $where['biz_textbook.id'] = $filter['textbook_id'];
        
        //年级和学科不为空求出所有教材
        if(!empty($filter['grade_id']) && !empty($filter['course_id'])){
            $textbook_model=M('biz_textbook');
            $textbook_where['grade_id']=$filter['grade_id'];
            $textbook_where['course_id']=$filter['course_id'];
            $textbook_result=$textbook_model->where($textbook_where)->field('id,name')->select();
            $this->assign('textbook',$textbook_result); 
        }   
        
        $this->assign('course_id', $filter['course_id']);
        $this->assign('grade_id', $filter['grade_id']);
        $this->assign('textbook_id', $filter['textbook_id']);
        
        $page = $_REQUEST['page'];
        if (empty($page)) $page = 1;
        $this->assign('page', $page);


        $keyword = $_GET['keyword'];
        $status=intval($_GET['status']);
        $date=$_GET['date'];
        if (!empty($keyword)) {
            /*$where[] = array(
                array(
                    'name' => array('like', '%' . $keyword . '%'),
                    'publishing_house' => array('like', '%' . $keyword . '%'),
                    'edition' => array('like', '%' . $keyword . '%'),
                    //'author' => array('like', '%' . $keyword . '%'),
                    '_logic' => 'or',
                ),
            ); */
            $where['_string']="name like '%$keyword%' or publishing_house like '%$keyword%' or edition like '%$keyword%'";
            $this->assign('keyword', $keyword);
        } 
        
        if(!empty($status)){  
            $where['biz_textbook.flag']=$status;
            $this->assign('status', $status);
        }  
        
        if(!empty($date)){  
            if(!empty($where['_string'])){
                $where["_string"].=" and create_at>=".strtotime(I("date"))." and "."create_at<=".(strtotime(I("date")."+1 day")-1);
            }else{
                $where["_string"]="create_at>=".strtotime(I("date"))." and "."create_at<=".(strtotime(I("date")."+1 day")-1);
            }   
            $this->assign('default_date', $date);
        }  

        $Model = M('biz_textbook');

        $count = $Model->where($where)->count('id');        
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));      
        //$Page->parameter['keyword'] = $keyword;         //urlencode($keyword);
        $show = $Page->show();

        $result = $Model
            ->where($where)
            ->limit($Page->firstRow . ',' . $Page->listRows) 
            ->order('sort_order asc,grade_id asc')
            ->select();                   
        
        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        $this->assign('courses', $courses);

        $Model = M('dict_grade');
        $grades = $Model->select();
        $this->assign('grades', $grades);
        
        $this->assign('list', $result);
        $this->assign('page', $show);

        $this->display();
    }

    //教材详情
    public function textbookDetails()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '电子课本');
        $this->assign('nav', '电子课本');

        $id = $_GET['id'];

        $Model = M('biz_textbook');
        $result1 = $Model
            ->where("id=$id")
            ->find(); 
        
        $this->assign('subnav', $result1['name']);
        $this->assign('data', $result1);

        $this->display();
    }

    //电子书详情
    public function etextbook()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '电子课本');
        $this->assign('nav', '电子课本');


        $c['id'] = $_GET['id'];

        $Model = M('biz_textbook');
        $result = $Model->where($c)->find();
        $this->assign('book', $result);

        $this->assign('subnav', $result['name']);

        $this->display();
    }

    //上传微课
    public function createMicroCourse()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        if ($_POST) {
            $data['title'] = remove_xss($_POST['title']);
            $data['textbook_id'] = $_POST['textbook_id'];
            $data['update_at'] = time();
            $data['create_at'] = time();

            $data['video_path'] = $_POST['video_path'];

            $Model = M('biz_micro_course');

            $Model->add($data);

            $this->redirect("Admin/createMicroCourse");
        } else {
            $this->assign('module', '电子课本');
            $this->assign('nav', '电子课本');
            $this->assign('subnav', '上传微课');

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            $Model = M('biz_textbook');
            $textbooks = $Model->order('sort_order asc')->select();
            $this->assign('textbooks', $textbooks);

            $this->display();
        }
    }

    //资源列表
    public function resourceMgmt()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '资源分享审核');
        $this->assign('nav', '资源分享审核');
        $this->assign('subnav', '资源列表');

        $filter['course_id'] = $_REQUEST['course_id'];
        $filter['grade_id'] = $_REQUEST['grade_id'];
        $filter['textbook_id'] = $_REQUEST['textbook_id'];   
        $filter['type'] = $_REQUEST['type']; 
        $filter['sort_column'] = $_REQUEST['sort_column'];
        $filter['keyword'] = $_REQUEST['keyword']; 
        $filter['status'] = $_REQUEST['status'];     
                    
        
        if (!empty($filter['course_id'])) $where['biz_resource.course_id'] = $filter['course_id'];
        if (!empty($filter['grade_id'])) $where['biz_resource.grade_id'] = $filter['grade_id'];
        if (!empty($filter['textbook_id'])) $where['biz_resource.textbook_id'] = $filter['textbook_id'];
        if (!empty($filter['type'])) $where['biz_resource.type'] = $filter['type'];        
        if (empty($filter['sort_column'])) $filter['sort_column'] = 'create_at';  
        
        $this->assign('course_id', $filter['course_id']);
        $this->assign('grade_id', $filter['grade_id']);
        $this->assign('textbook_id', $filter['textbook_id']); 
        $this->assign('type', $filter['type']); 
        $this->assign('sort_column', $filter['sort_column']);
        if(!empty($filter['course_id']) && !empty($filter['grade_id'])){
            
            $TextbookModel = M('biz_textbook');
            $c1['course_id'] = $filter['course_id'];
            $c1['grade_id'] = $filter['grade_id']; 
            $textbook_result = $TextbookModel->where($c1)->field('id,name')->select();       
            $this->assign('textbook_list',$textbook_result);
        }
        
        $keyword = $filter['keyword'];
        $status=intval($filter['status']);
        if (!empty($keyword)) {
            $where['_string'] = '(biz_resource.name LIKE \'%' . $keyword .'%\' OR ' .'biz_resource.teacher_name LIKE \'%' . $keyword . '%\' OR biz_resource.description LIKE \'%' .$keyword . '%\') AND';
        } 
        if(!empty($where['_string'])){
            $where['_string']=$where['_string']."  biz_resource.type!='html'";
        }else{
            $where['_string']=" biz_resource.type!='html'";
        } 
        
        if(!empty($status)){  
            $where['_string']=$where['_string']." and biz_resource.status=".$status;
            $this->assign('status', $status);
        }   
        
        $Model = M('biz_resource'); 
        $count = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id')
            ->join('dict_course on biz_resource.course_id=dict_course.id')
            ->join('dict_grade on biz_resource.grade_id=dict_grade.id')
            ->join('auth_teacher on auth_teacher.id=biz_resource.teacher_id')
            ->field('biz_resource.id,biz_textbook.name as textbook')
            ->where($where)    
            ->count('1');          
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($filter as $key => $val) {
            //$Page->parameter[$key] = urlencode($val);
            $Page->parameter[$key] = $val;
        } 
        $show = $Page->show();          

        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id')
            ->join('dict_course on biz_resource.course_id=dict_course.id')
            ->join('dict_grade on biz_resource.grade_id=dict_grade.id')
            ->join('auth_teacher on auth_teacher.id=biz_resource.teacher_id')
            ->field('biz_resource.*,biz_textbook.name as textbook,dict_course.course_name,dict_grade.grade,auth_teacher.avatar,auth_teacher.name teacher_name')
            ->order('biz_resource.'.$filter['sort_column'].' desc')
            ->where($where)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();                                    
        //资源如果存在,就对id依次进行加密
        /*if(!empty($result)){
            $des=new DES3(); 
            foreach($result as $key=>$val){
                $result[$key]['key']=$des->des3_encrypt($val['id']); 
            }
        }*/   
        
        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        
        $Model = M('dict_grade');
        $grades = $Model->select();
        
        $resource_model=D('Biz_resource');
        $all_resource_result=$resource_model->getAllResourceCount();  
        $resource_type=$resource_model->getResourceType();       
        $search_resource=array();
        $search_resource[]=array('key'=>'资源总数','value'=>$count);
        
        foreach($resource_type as $key=>$val){
            $where['biz_resource.type']=$key;
            $data=$resource_model->getResourceInfoCount($where);
            $search_resource[]=array('key'=>$val,'value'=>$data['count']);
        }  
        
        $this->assign('search_resource_arr', $search_resource);
        $this->assign('all_resource_count_arr', $all_resource_result);
        $this->assign('list', $result);
        $this->assign('page', $show);
        $this->assign('keyword', $keyword); 
        
        $this->assign('courses', $courses); 
        $this->assign('grades', $grades);
        
        $this->display();
    }

    //资源详情
    public function resourceDetails()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '资源分享审核');
        $this->assign('nav', '资源分享审核');


        $id = $_GET['id'];

        $Model = M('biz_resource');
        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id')
            ->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
            ->join('dict_course on biz_resource.course_id=dict_course.id')
            ->join('dict_grade on biz_resource.grade_id=dict_grade.id')
            ->field('biz_resource.*,biz_textbook.name as textbook,auth_teacher.brief_intro as teacher_brief_intro,auth_teacher.points as points,dict_course.course_name,'
                    . 'dict_grade.grade,auth_teacher.name teacher_name,auth_teacher.avatar')
            ->where("biz_resource.id=$id")
            ->find();

        $this->assign('subnav', $result['name']); 
        $this->assign('data', $result); 
        $this->assign('oss_path',C('oss_path')); 
        
        $resource_list=$Model->where("biz_resource.id=".$id)->join("biz_resource_contact on biz_resource.id=biz_resource_contact.biz_resource_id")
                            ->field("biz_resource.id,biz_resource.type,biz_resource_contact.resource_path,biz_resource_contact.vid vvid")->select();
        $this->assign('resource_list', $resource_list); 
        
        $this->display();

    }

    //通过审核资源分享  
    public function approveResource()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $id = $_GET['id'];
        $Model = M('biz_resource');

        $data['status'] = 2;

        $Model->where("id=$id")->save($data);

        $resource = $Model->where("id=$id")->find();

        $User = M("auth_teacher");
        $User->where("id=" . $resource['teacher_id'])->setInc("points", 100);// 积分加100

        $parameters = array( 'msg' => array($resource['name']) , 'url' => array( 'type' => 0));
        A('Home/Message')->addPushUserMessage('TEACHER_RESOURCEPASS',2,$resource['teacher_id'],$parameters);

        $this->ajaxReturn('success');
    }

    //拒绝审核资源分享
    public function denyResource()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $id = $_GET['id'];
        $Model = M('biz_resource');

        $data['status'] = 3;

        $Model->where("id=$id")->save($data);

        $resource = $Model->where("id=$id")->find();
        //$User = M("auth_teacher");
        //$User->where("id=" . $resource['teacher_id'])->setInc("points", -100);// 积分减100
        $parameters = array( 'msg' => array($resource['name']) , 'url' => array( 'type' => 0));
        A('Home/Message')->addPushUserMessage('TEACHER_RESOURCEFAIL',2,$resource['teacher_id'],$parameters);

        $this->ajaxReturn('success');
    }
    
    //资源分享下架
    public function downResource(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        $id = $_GET['id'];
        $Model = M('biz_resource');
        $data['status'] = 1;

        $Model->where("id=$id")->save($data);

        $resource = $Model->where("id=$id")->find();
        $parameters = array( 'msg' => array($resource['name']) , 'url' => array( 'type' => 0));
        A('Home/Message')->addPushUserMessage('TEACHER_RESOURCEDOWN',2,$resource['teacher_id'],$parameters);

        $this->ajaxReturn('success');
    }
    
    //京版资源删除
    function deleteResource(){
        if (!session('?admin')) redirect(U('Index/index'));
        if(session('admin.role')==3){
            $this->ajaxReturn('error');
        }else{
            $id=getParameter('id','int');
            $resource_model=D('Biz_resource');
            if($resource_model->managementDeleteResource($id)){
                $this->ajaxReturn('success');
            }else{
                $this->ajaxReturn('failed');
            }
        }
    }

    //专家资讯信息列表
    public function expertInformationMgmt()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '励耘圈管理');
        $this->assign('nav', '专家资讯管理');
        $this->assign('subnav', '资讯信息列表');
        $status=intval($_GET['status']);
        $keyword = $_GET['keyword'];

        if(!empty($status)){  
            $where['social_expert_information.status']=$status;
            $this->assign('status', $status);
        }
        if(!empty($keyword))
        {
            $where['social_expert_information.title']=array('like','%'.$keyword.'%');
            $this->assign('keyword', $keyword);
        }
        
        $Model = M('social_expert_information');


        $count = $Model->join('auth_admin on social_expert_information.publisher_id = auth_admin.id')->where($where)->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        $result = $Model
            ->join('auth_admin on social_expert_information.publisher_id = auth_admin.id')->where($where)
            ->order('create_at desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->field('social_expert_information.*,auth_admin.nickname')
            ->select();

        $this->assign('role', session('admin.role'));
        $this->assign('list', $result);
        $this->assign('page', $show);

        $this->display();
    }

    //发布资讯
    public function publishExpertInformation()
    {
        if (!session('?admin')) redirect(U('Index/index'));

      
        if ($_POST) {
            $data['title'] = remove_xss($_POST['title']);
            //$data['short_content'] = remove_xss($_POST['short_content']);
            $data['content'] = $_POST['content'];

            $data['update_at'] = time();
            $data['create_at'] = time();

            $data['status'] = 1;

            $data['publisher_id'] = session('admin.id');
            $data['publisher'] = session('admin.name');     

            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 911127998;// 设置附件上传大小
            $upload->exts = array('jpg', 'png');// 设置附件上传类型
            $upload->rootPath = './Resources/expertinformation/'; // 设置附件上传根目录
            // 上传单个文件
            $info = $upload->uploadOne($_FILES['file']);
            if (!$info) { // 上传错误提示错误信息
                $this->error($upload->getError());
            } else { // 上传成功 获取上传文件信息
                //echo $info['savepath'] . $info['savename'];
            }
            $data['short_content'] = $info['savepath'] . $info['savename'];
            $Model = M('social_expert_information');
            $Model->add($data);
            
            $this->redirect("Admin/expertInformationMgmt");
        } else {
            $this->assign('module', '励耘圈管理');
            $this->assign('nav', '专家资讯管理');
            $this->assign('subnav', '发布资讯');

            $this->display();
        }
    }

    //专家资讯信息详情
    public function expertInformationDetails($id)
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '励耘圈');
        $this->assign('nav', '专家资讯');
        $this->assign('subnav', '资讯详情');

        $Model = M('social_expert_information');
        $check['social_expert_information.id'] = $id;
        $result = $Model->join('auth_admin on social_expert_information.publisher_id = auth_admin.id')->where($check)
        ->field('social_expert_information.*,auth_admin.nickname')
        ->find();

        $this->assign('data', $result);

        $this->display();
    }

    //修改专家资讯
    public function modifyExpertInformation()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        if ($_POST) {
            $check['id'] = $_POST['id'];
            $data['title'] = remove_xss($_POST['title']);
            $data['content'] = $_POST['content'];
            $data['update_at'] = time();
            $data['status'] = 1;

            if ($_FILES["file"]["error"] == 0) {
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 911127998;// 设置附件上传大小
                $upload->exts = array('jpg', 'png');// 设置附件上传类型
                $upload->rootPath = './Resources/expertinformation/'; // 设置附件上传根目录
                // 上传单个文件
                $info = $upload->uploadOne($_FILES['file']);
                if (!$info) { // 上传错误提示错误信息
                    $this->error($upload->getError());
                }
                $data['short_content'] = $info['savepath'] . $info['savename'];
            }

            $Model = M('social_expert_information');
            $Model->where($check)->save($data);
            $this->redirect("Admin/expertInformationMgmt");

        } else {
            $this->assign('module', '励耘圈管理');
            $this->assign('nav', '专家资讯管理');
            $this->assign('subnav', '修改资讯');

            $id = $_GET['id'];
            $this->assign('id', $id);

            $Model = M('social_expert_information');
            $result = $Model->where("id=$id")->find();

            $this->assign('data', $result);

            $this->display();
        }
    }

    //通过审核专家资讯
    public function approveExpertInformation()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $id = $_GET['id'];
        $Model = M('social_expert_information');
        $check['id']=$id;
        $result=$Model->where($check)->field('id,title,content,push_status')->find();
        if(empty($result)){
            echo $this->ajaxReturn('failed');die;
        }
        if($result['push_status']==1){
            //手机三个角色推送
            /*
            $message_id=$id;
            $message_model=D('Message');
            $message_add_data['role_id']='2,3,4';
            $message_add_data['title']='专家资讯：'.substr($result['title'],0,50);       
            $message_add_data['truncated_title']=substr($result['content'],0,50);
            $message_add_data['message_content']=$result['content'];
            $message_add_data['receive_type']=1; 
            $message_add_data['message_type']=2;
            
            $message_id=$message_id=$message_model->addMessageInfo($message_add_data);
            $people_number=$message_model->addMessageReceive($message_id);
            $message_model->updateMessageReceivenum($message_id,$people_number); 
            $parameters=array( 
                'url'=>array(
                    'type'=> 1,
                    'data'=>array($id)
                )
            );
            $config_arr=C('PUSH_MESSAGE');
            $format_url=$config_arr['EXPERTINFO_PUBLISHED']['FORMAT_URL'];
            if($parameters['url']['type'] == 0) 
            { 
                $messageUrl = 'http://'. $_SERVER["SERVER_NAME"] .sprintf($format_url, $message_id); 
            }
            else
            { 
                $messageUrl = 'http://'. $_SERVER["SERVER_NAME"] .vsprintf($format_url, $parameters['url']['data']); 
            } 
            A('Home/Message')->sendMessage($message_id,$messageUrl);

            $Model->where("id=$id")->save(array('push_status'=>2));
            */
        }
        
        $data['status'] = 2;

        $Model->where("id=$id")->save($data);

        $this->ajaxReturn('success');
    }
    public function publishExpertInformationNormal()
    {
        if (!session('?admin')) redirect(U('Index/index'));
        $id = $_GET['id'];
        $Model = M('social_expert_information');
        $data['status'] = 4;
        $Model->where("id=$id")->save($data);
        $this->ajaxReturn('success');

    }
    //拒绝审核专家资讯
    public function denyExpertInformation()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $id = $_GET['id'];
        $Model = M('social_expert_information');

        $data['status'] = 3;

        $Model->where("id=$id")->save($data);

        $this->ajaxReturn('success');
    }
    
    //删除专家资讯
    public function deleteExpertInformation()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $id = $_GET['id'];
        $Model = M('social_expert_information');
 

        $Model->where("id=$id")->delete();

        $this->ajaxReturn('success');
    }
    
    
    //下架审核专家资讯
    public function downExpertInformation(){
        if (!session('?admin')) redirect(U('Index/index'));
        $id = $_GET['id'];
        $Model = M('social_expert_information');

        $data['status'] = 2;

        $Model->where("id=$id")->save($data);

        $this->ajaxReturn('success');
        
    }

    //推送专家资讯
    public function pushExpertInformation(){
        if (!session('?admin')) redirect(U('Index/index'));
        $id = getParameter('id','int');
        $info = D('Social_expert_information')->getInformationDetails($id);
        if(D('Social_expert_information')->setPushStatus($id,EXPERTINFORMATION_HASPUSH)) {
            $parameters = array('msg' => array(
                $info['title']
            ),
                'url' => array('type' => 1, 'data' => array($id))
            );
            //手机三个角色推送
            set_time_limit(0);
            $message_model=D('Message');
            $message_add_data['role_id']='2,3,4';
            $message_add_data['title']='专家资讯：'.substr($info['title'],0,50);
            $message_add_data['truncated_title']=substr($message_add_data['title'],0,50);
            $message_add_data['message_content']=$info['content'];
            $message_add_data['receive_type']=1;
            $message_add_data['message_type']=2;

            $message_id=$message_model->addMessageInfo($message_add_data);
            $people_number=$message_model->addMessageReceive($message_id);

            $message_model->updateMessageReceivenum($message_id,$people_number);
            $parameters=array(
                'url'=>array(
                    'type'=> 1,
                    'data'=>array($id)
                )
            );
            $config_arr=C('PUSH_MESSAGE');
            $format_url=$config_arr['EXPERTINFO_PUBLISHED']['FORMAT_URL'];
            if($parameters['url']['type'] == 0)
            {
                $messageUrl = 'http://'. $_SERVER["SERVER_NAME"] .sprintf($format_url, $message_id);
            }
            else
            {
                $messageUrl = 'http://'. $_SERVER["SERVER_NAME"] .vsprintf($format_url, $parameters['url']['data']);
            }
            A('Home/Message')->sendMessage($message_id,$messageUrl);
            $this->ajaxReturn(array('status' => 200, 'message' => '推送成功'));

        }
        else
            $this->ajaxReturn(array('status' => 500, 'message' => '设置活动推送状态失败'));
    }
    
    //京版活动下报名人导出
    public function exportedActivityRegister($activity_id=0){

            $oss_path=C('oss_path');
            if(0==$activity_id)
             $activity_id=$_GET['activity_id'];
            $activity_register_model=M('social_activity_register');
            $activity_register=$activity_register_model->join("social_activity on social_activity.id=social_activity_register.activity_id")
                    ->join("auth_teacher on auth_teacher.id=social_activity_register.user_id")
                    ->join("dict_schoollist on dict_schoollist.id=auth_teacher.school_id")
                    ->join("dict_citydistrict a on a.id=dict_schoollist.provice_id")
                    ->join("dict_citydistrict b on b.id=dict_schoollist.city_id")
                    ->join("dict_citydistrict c on c.id=dict_schoollist.district_id")
                    ->field("user_name,FROM_UNIXTIME(social_activity_register.register_at) as register_time,social_activity_register.register_info,file_path,social_activity.title,"
                            . "auth_teacher.telephone,a.name provice,b.name city,c.name district,dict_schoollist.school_name,auth_teacher.email")
                    ->where("activity_id=".$activity_id)->select();
            
            //$str="活动名称,报名人姓名,报名人联系方式,填写信息,报名时间,上传附件,省份,城市,区县,学校名称\n";
            $str="报名人,报名信息,报名时间\n";
            $str=iconv('utf-8','gb2312', $str);     
            foreach($activity_register as $v){
                $teacher_name=iconv('utf-8','gbk', $v['user_name']);
                $teacher_telephone=iconv('utf-8','gb2312', $v['telephone']);
                $email = iconv('utf-8','gb2312', $v['email']);
                $register_info=iconv('utf-8','gb2312', $v['register_info']);
                $register_time=iconv('utf-8','gb2312', $v['register_time']);

                $line = $teacher_name.','.iconv('utf-8','gb2312', '手机').":".$teacher_telephone." ".iconv('utf-8','gb2312', '邮箱').':' .$email ." ".iconv('utf-8','gb2312', '报名信息').":".$register_info.",".$register_time."\n";
 
                $str.= $line;
                
            }

            $filename=date('Ymd').rand(0,1000).'activityRegister'.'.csv';
            $csv=new CSV(); 
            $csv->downloadFileCsv($filename,$str);
            die;
    }
    
    //精通活动搜索 这个没用
    public function activitySearch(){
        if (!session('?admin')) redirect(U('Index/index'));
        $this->assign('module', '励耘圈管理');
        $this->assign('nav', '京版活动管理');
        $this->assign('subnav', '京版活动列表');
        
        $SocialActivity = M('social_activity');
        $classActivity=M('social_activity_class');
                    
        $cat=I('cat');
        $searchVal=I('val'); 
        if(!empty($cat)){  
            $where['social_activity_class.id']=$cat; 
        }
        if(!empty($searchVal)){  
            $where['_string']="social_activity.title like '%".$searchVal."%'"; 
        }
        
        
        $result = $SocialActivity->join('social_activity_class on social_activity_class.id=social_activity.class_id')
            ->where($where)
            ->order('create_at desc') 
            ->Field('social_activity.id,social_activity_class.class')->select(); 
        $count=count($count);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();
        
        $result = $SocialActivity->join('social_activity_class on social_activity_class.id=social_activity.class_id')
        ->where($where)
        ->order('create_at desc') 
        ->limit($Page->firstRow . ',' . $Page->listRows)
        ->Field('social_activity.id,social_activity_class.class')->select(); 
                    
        $class_result=$classActivity->Field('id,class')->select();
        $this->assign('class_list', $class_result);
        $this->assign('list', $result);
        $this->assign('page', $show);
        $this->assign('flag', $flag);   
        $this->assign('search_val', $searchVal);
        $this->display();
    }


    //精通活动管理
    public function activitiesMgmt()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '励耘圈管理');
        $this->assign('nav', '京版活动管理');
        $this->assign('subnav', '京版活动列表');

        $cat=I('cat');
        $searchVal=I('val');
        $status=intval($_GET['status']);
        $date = I('date');

        $child_class_name = I('child_class_name');

        if(!empty($cat)){  
            $where['social_activity_class.id']=$cat; 
        }

        if (!empty($child_class_name) && $cat == 5) {
            $where['social_activity_class.id']=$child_class_name;
            $this->assign('child_class_name', $child_class_name);
        }

        if(!empty($searchVal)){
            $where['_string']="social_activity.title like '%".$searchVal."%' OR " ."auth_admin.nickname like '%".$searchVal."%' OR ". "social_activity.content like '%".$searchVal."%'";
        }
        if(!empty($status)){  
            $where['social_activity.status']=$status;   
            $this->assign('status', $status);
        }
        if(!empty($date)){
            $where['from_unixtime(social_activity.create_at,\'%Y-%m-%d\')'] = $date;
            $this->assign('publishTime', $date);
        }
        $SocialActivity = M('social_activity');
        $classActivity=M('social_activity_class');

        
        $count = $SocialActivity->join('auth_admin on social_activity.publisher_id = auth_admin.id')
            ->join('social_activity_class on social_activity_class.id=social_activity.class_id')->where($where)->count('social_activity.id');
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();      

        $result = $SocialActivity
            ->join('auth_admin on social_activity.publisher_id = auth_admin.id')
            ->join('social_activity_class on social_activity_class.id=social_activity.class_id')->where($where)->order('create_at desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->Field('social_activity.*,auth_admin.nickname as publisher_admin,social_activity_class.class,social_activity_class.parent_id')->select();

        $class_result=$classActivity->Field('id,class')->where('parent_id=0')->select();
        $this->assign('class_list', $class_result);
        $this->assign('list', $result);
        $this->assign('page', $show);
        $this->assign('role', session('admin.role'));
        $this->assign('search_val', $searchVal);
        $this->assign('cat', $cat);

        
        $this->display_nocache();
    }


    public function childPid() {
        $pid = $_GET['pid'];
        $row = M('social_activity_class')->where("parent_id=".$pid)->select();
        $data['info'] = $row;
        $this->ajaxReturn($data);

    }
                


    //发布活动
    public function publishActivity()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        if ($_POST) {

            $data['title'] = remove_xss($_POST['title']);
            //$data['short_content'] = remove_xss($_POST['short_content']);
            $data['content'] = $_POST['content'];
            $data['stakeholder'] = remove_xss($_POST['stakeholder']);
            $data['category'] = remove_xss($_POST['category']);
            $data['class_id'] = $_POST['class_id'];

            $class_type_id = $_POST['class_type_id'];
            if ($class_type_id!=0) {
                $data['class_id'] = $class_type_id;
            }

            $data['code_num'] = $_POST['limitNums'];//邀请码个数
            $data['is_disable'] = $_POST['number'];//是否限制人数
            $data['remark'] = $_POST['remark'];//邀请码个数
            //if($data['is_disable']== 1) { //点击
                $data['apply_people_number'] = $_POST['limitNums'];//限制人数

            //}


            $data['is_generate'] = $_POST['code'];//是否生成邀请码
            $data['activitystart'] = strtotime($_POST['activityStart']);//活动开始时间
            $data['activityend'] = strtotime($_POST['activityEnd']);//活动结束
            $data['applystart'] = strtotime($_POST['applyStart']);//报名开始时间
            $data['applyend'] = strtotime($_POST['applyEnd']);//报名结束时间

            if ( !empty($data['activitystart']) && !empty($data['activityend']) ) {
                if ($data['activitystart'] == $data['activityend']) {
                    $this->error("活动开始时间和活动结束时间填写错误");
                }
            }

            if ( !empty($data['applystart']) && !empty($data['applyend']) ) {
                if ($data['applystart'] == $data['applyend'] || $data['applystart'] >$data['applyend'] ) {
                    $this->error("报名开始时间和报名结束时间填写错误");
                }
            }


            var_dump($data);exit;
            if ( !empty($data['activitystart']) && !empty($data['applyend']) ) {
                if ($data['activitystart'] == $data['applyend'] ) {
                    $this->error("报名结束时间和活动开始时间是错误时间");
                }
            }

            if ( !empty($data['activityend']) && !empty($data['applyend']) ) {
                if ($data['activityend'] == $data['applyend'] ) {
                    $this->error("报名结束时间和活动结束时间是错误时间");
                }
            }


            $codenameinfo= $_POST['codename'];//生成的邀请码
            $codenameinfo = explode(",",$codenameinfo);
            $vid_file_path_info = $_POST['vid_file_path'];//上传的活动资料
            $vid_file_path_info = explode(",#",$vid_file_path_info);
            
                    
            $data['is_upload']=$_POST['is_upload'];
            
            $data['update_at'] = time();
            $data['create_at'] = time();

            $data['status'] = 1;

            $data['publisher_id'] = session('admin.id');
            $data['publisher'] = session('admin.name');

            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 911127998;// 设置附件上传大小
            $upload->exts = array('jpg', 'png');// 设置附件上传类型
            $upload->rootPath = './Resources/socialactivity/'; // 设置附件上传根目录
            // 上传单个文件
            $info = $upload->uploadOne($_FILES['filepic']);
            if (!$info) { // 上传错误提示错误信息
                $this->error($upload->getError());
            } else { // 上传成功 获取上传文件信息
                //echo $info['savepath'] . $info['savename'];
            }
            $data['short_content'] = $info['savepath'] . $info['savename'];

            $Model = M('social_activity');

            $islock = true;
            $Model->startTrans();
            $hid = $Model->add($data);

            if (!$hid) {
                $islock = false;
            }

            if (!empty($codenameinfo) && $data['is_generate'] == 2) { //邀请码入库条件
                foreach ($codenameinfo as $k=>$v) {
                    if (!empty($v)) {
                        $datacode['activity_id'] = $hid;
                        $datacode['invitation_code'] = $v;
                        $datacode['status'] = 1;
                        $datacode['create_at'] = time();
                        $sc = M('social_activity_invitation_code')->add($datacode);
                        if ( !$sc ) {
                            $islock = false;
                            $Model->rollback();
                        }
                    }
                }
            }
                    
            
            if (!empty($vid_file_path_info)) {

                foreach ($vid_file_path_info as $vk=>$vv) {
                    if(!empty($vv) && $vv!='undefined') {
                        $filedatapath = explode(":",$vv);   
                        $datafile['activity_id'] = $hid;
                        $datafile['activity_file_path'] = $filedatapath[0];
                        //$file_info_name = pathinfo($filedatapath[1]);           
                        //$datafile['activity_file_name'] =$file_info_name['filename'];
                        $datafile['activity_file_name']= substr($filedatapath[1],0,strrpos($filedatapath[1],'.')); 
                        $datafile['type'] = end(explode('.', $filedatapath[1]));                   
                        if ($datafile['type']=='jpeg' || $datafile['type']=='jpg' || $datafile['type']=='png'|| $datafile['type']=='gif') {
                            $datafile['type'] = 'image';
                        }

                        if($datafile['type']=='mp4' || $datafile['type']=='mov'|| $datafile['type']=='rmvb'|| $datafile['type']=='avi' ){
                            $datafile['type']='video';

                        }else if($datafile['type']=='mp3' || $datafile['type']=='wav' || $datafile['type']=='aac'|| $datafile['type']=='amr'  ){
                            $datafile['type']='audio';

                        }else if($datafile['type']=='docx' || $datafile['type']=='doc' ){
                            $datafile['type']='word';
	
			}else if($datafile['type']=='pptx'){
                            $datafile['type']='ppt';	
                        }

                        if ($datafile['type']=='mp4' || $datafile['type']=='mov' || $datafile['type']=='mp3' || $datafile['type']=='wmv'|| $datafile['type']=='flv'|| $datafile['type']=='avi') {
                            $vid = $_POST['vid'];
                            $vid_fullpath = $_POST['vid_fullpath'];
                            $vidarr = explode(",",$vid);
                            $vid_fullpatharr = explode(",",$vid_fullpath);

                            if (!empty($vidarr[$vk])) {
                                $datafile['vid'] = $vidarr[$vk];
                            }
                            if (!empty($vid_fullpatharr[$vk])) {
                                $datafile['vid_fullpath'] = $vid_fullpatharr[$vk];
                            }

                        }

                        $datafile['create_at'] = time();
                        $sf = M('social_activity_contact_file')->add($datafile);
                        if ( !$sf ) {
                            $islock = false;
                            $Model->rollback();
                        }
                    }
                }
            }
            $is_all=$_POST['allgradeorcourse'];
            $allcount = count($is_all);
            
            if ($allcount==2) { //选择了全学科和全年级
                $second['course']=0;
                $second['grade']=0;
                $second['activity_id']=$hid;
                $social_activity_course_grade_second=M('social_activity_course_grade');
                if(!$second_result=$social_activity_course_grade_second->add($second)){
                    $islock = false;
                    $Model->rollback();
                }

                //全部选中
                $social_activity_is_select['is_grade_select'] = 1;
                $social_activity_is_select['is_course_select'] = 1;
                $save_is_select = M('social_activity')->where("id=".$hid)->save($social_activity_is_select);

                if( $save_is_select===false || $save_is_select<0 ){

                    $islock = false;
                    $Model->rollback();
                }

            } else {

                if ($allcount == 0 || $allcount =='' ) {
                    $grade_second=$_POST['grade'];
                    $course_second=$_POST['course'];
                    $second['activity_id']=$hid;
                    $social_activity_course_grade_second=M('social_activity_course_grade');

                    if (count($course_second) > 0) {
                        $grade_second=$_POST['grade'];
                        $course_second=$_POST['course'];
                        $second['activity_id']=$hid;

                        $social_activity_course_grade_second=M('social_activity_course_grade');
                        for($k=0;$k<count($course_second);$k++){
                            $second['course']=$course_second[$k];
                            $second['grade']=$grade_second[$k];
                            if(!$second_result=$social_activity_course_grade_second->add($second)){
                                $islock = false;
                                $Model->rollback();
                            }

                            if ($second['course'] ==0) {
                                $social_activity_is_select['is_course_select'] = 1;

                                $save_is_select = M('social_activity')->where("id=".$hid)->save($social_activity_is_select);
                                if( $save_is_select===false || $save_is_select<0   ){
                                    $islock = false;
                                    $Model->rollback();
                                }

                            }else {
                                $social_activity_is_select['is_grade_select'] = 1;
                                $save_is_select = M('social_activity')->where("id=".$hid)->save($social_activity_is_select);
                                if( $save_is_select===false || $save_is_select<0   ){
                                    $islock = false;
                                    $Model->rollback();
                                }
                            }

                        }
                    } else {
                        $social_activity_course_grade_second=M('social_activity_course_grade');
                        $second['course']=0;
                        $second['grade']=0;
                        $second['activity_id']=$hid;
                        if(!$second_result=$social_activity_course_grade_second->add($second)){
                            $islock = false;
                            $Model->rollback();
                        }
                    }
                } else {
                    $grade_second=$_POST['grade'];
                    $course_second=$_POST['course'];
                    $second['activity_id']=$hid;

                    $social_activity_course_grade_second=M('social_activity_course_grade');
                    for($k=0;$k<count($course_second);$k++){
                        $second['course']=$course_second[$k];
                        $second['grade']=$grade_second[$k];
                        if(!$second_result=$social_activity_course_grade_second->add($second)){
                            $islock = false;
                            $Model->rollback();
                        }

                        if ($second['course'] ==0) {
                            $social_activity_is_select['is_course_select'] = 1;

                            $save_is_select = M('social_activity')->where("id=".$hid)->save($social_activity_is_select);
                            if( $save_is_select===false || $save_is_select<0   ){
                                $islock = false;
                                $Model->rollback();
                            }

                        }else {
                            $social_activity_is_select['is_grade_select'] = 1;
                            $save_is_select = M('social_activity')->where("id=".$hid)->save($social_activity_is_select);
                            if( $save_is_select===false || $save_is_select<0   ){
                                $islock = false;
                                $Model->rollback();
                            }
                        }

                    }
                }


            }

            if( $islock == false) {
                $this->error("添加失败");

            } else {
                $Model->commit();
                $this->redirect("Admin/activitiesMgmt");
            }

        } else {
            $this->assign('module', '励耘圈管理');
            $this->assign('nav', '京版活动管理');
            $this->assign('subnav', '发布活动');

            $Model = M('social_activity_class');
            $classes = $Model->order('sort_order asc')->where('parent_id=0')->select();
            $this->assign('classes', $classes);

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            $this->display();
        }
    }

    //修改活动
    public function modifyActivity()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        if ($_POST) {
            $check['id'] = $_POST['id'];
            $oldActivityInfo =  D('Social_activity')->getActivityDetails($check['id']);
            $data['title'] = remove_xss($_POST['title']);
            
            $newgradchordata = $_POST['newgradchordata'];
            $gradchordata = $_POST['gradchordata'];
            $newgradchordata = explode('|',$newgradchordata);
            $gradchordata = explode('|',$gradchordata);

            if (count($newgradchordata) != count($gradchordata)) { //长度不一样直接发送推送

                $sar = M('social_activity_register')->where("activity_id=".$check['id'])->select();
                $sendpushids = '';
                foreach ($sar as $sendk=>$sendv) {
                    if($sendk==0) {
                        $sendpushids.=$sendv['user_id'];
                    } else {
                        $sendpushids.=','.$sendv['user_id'];
                    }
                }

                /*$parameters = array( 'msg' => array($data['title'],$_POST['gradchordata'],$_POST['newgradchordata']) , 'url' => array( 'type' => 0));
                A('Home/Message')->addPushUserMessage('TEACHER_RESOURCEDOWN_Grad',2,$sendpushids,$parameters);*/
            } else { //长度一样，判断年级学科是不是一样
                foreach ($newgradchordata as $k=>$v) {
                    $is_false = in_array($v,$gradchordata);
                    if ($is_false != true) {

                        $sar = M('social_activity_register')->where("activity_id=".$check['id'])->select();
                        $sendpushids = '';
                        foreach ($sar as $sendk=>$sendv) {
                            if($sendk==0) {
                                $sendpushids.=$sendv['user_id'];
                            } else {
                                $sendpushids.=','.$sendv['user_id'];
                            }
                        }

                        /*$parameters = array( 'msg' => array($data['title'],$_POST['gradchordata'],$_POST['newgradchordata']) , 'url' => array( 'type' => 0));
                        A('Home/Message')->addPushUserMessage('TEACHER_RESOURCEDOWN_Grad',2,$sendpushids,$parameters);*/
                    }
                }
            }
            //$data['short_content'] = remove_xss($_POST['short_content']);
            $data['content'] = $_POST['content'];
            $data['stakeholder'] = remove_xss($_POST['stakeholder']);
            $data['category'] = remove_xss($_POST['category']);
            $data['class_id'] = $_POST['class_id'];

            $class_type_id = $_POST['class_type_id'];
            if ($class_type_id!=0) {
                $data['class_id'] = $class_type_id;
            }


            $data['update_at'] = time();
            $data['status'] = 1;
            //$data['class_id'] = $_POST['class_id'];

            $data['apply_people_number'] = $_POST['limitNums'];//限制人数
            $data['code_num'] = $_POST['limitNums'];//邀请码个数
            $data['remark'] = $_POST['remark'];//邀请码个数
            $data['is_disable'] = $_POST['number'];//是否限制人数

            $data['is_generate'] = $_POST['code'];//是否生成邀请码
            $data['activitystart'] = strtotime($_POST['activityStart']);//活动开始时间
            $data['activityend'] = strtotime($_POST['activityEnd']);//活动结束
            $data['applystart'] = strtotime($_POST['applyStart']);//报名开始时间
            $data['applyend'] = strtotime($_POST['applyEnd']);//报名结束时间


            if ( !empty($data['activitystart']) && !empty($data['activityend']) ) {
                if ($data['activitystart'] == $data['activityend']) {
                    $this->error("活动开始时间和活动结束时间填写错误");
                }
            }

            if ( !empty($data['applystart']) && !empty($data['applyend']) ) {
                if ($data['applystart'] == $data['applyend'] || $data['applystart'] >$data['applyend'] ) {
                    $this->error("报名开始时间和报名结束时间填写错误");
                }
            }



            if ( !empty($data['activitystart']) && !empty($data['applyend']) ) {
                if ($data['activitystart'] == $data['applyend']  ) {
                    $this->error("报名结束时间和活动开始时间是错误时间");
                }
            }

            if ( !empty($data['activityend']) && !empty($data['applyend']) ) {
                if ($data['activityend'] == $data['applyend'] ) {
                    $this->error("报名结束时间和活动结束时间是错误时间");
                }
            }


            $codenameinfo= $_POST['codename'];//生成的邀请码
            $codenameinfo = explode(",",$codenameinfo);
            $vid_file_path_info = $_POST['vid_file_path'];//上传的活动资料
            $vid_file_path_info = explode(",#",$vid_file_path_info);

            $hidden_resource = $_POST['hidden_resource'];//旧的资源没有删除的

            $data['is_upload']=$_POST['is_upload'];


            if ($_FILES["filepic"]["error"] == 0) {
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 911127998;// 设置附件上传大小
                $upload->exts = array('jpg', 'png');// 设置附件上传类型
                $upload->rootPath = './Resources/socialactivity/'; // 设置附件上传根目录
                // 上传单个文件
                $info = $upload->uploadOne($_FILES['filepic']);
                if (!$info) { // 上传错误提示错误信息
                    $this->error($upload->getError());
                }
                $data['short_content'] = $info['savepath'] . $info['savename'];
            }


            $Model = M('social_activity');
            $islock = true;
            $Model->startTrans();

            $hid = $Model->where($check)->save($data);

            if (!$hid) {
                $islock == false;
            }

            if (!empty($hidden_resource)) { //不为空删除相反的
                $reswhere['id'] = array('not in',$hidden_resource);
                $reswhere['activity_id'] = $check['id'];
                $delres = M('social_activity_contact_file')->where( $reswhere )->delete();
                if ($delres == false && $delres!=0 ) {
                    $islock = false;
                    $Model->rollback();
                }
            }else{ //全部删除
                $delres = M('social_activity_contact_file')->where("activity_id=".$check['id'])->delete();
                if ($delres == false && $delres!=0 ) {
                    $islock = false;
                    $Model->rollback();
                }
            }
                    
            
            if (!empty($vid_file_path_info)) {

                foreach ($vid_file_path_info as $vk=>$vv) {     
                    if(!empty($vv) && $vv!='undefined' ) {      
                        $filedatapath = explode(":",$vv);
                        $datafile_save['activity_id'] = $check['id'];
                        $datafile_save['activity_file_path'] = $filedatapath[0];
                        $file_info_name = pathinfo($filedatapath[1]);
                        $datafile_save['activity_file_name'] =$file_info_name['filename'];
                        $datafile['activity_file_name']= substr($filedatapath[1],0,strrpos($filedatapath[1],'.'));  
                        $datafile_save['type'] = end(explode('.', $filedatapath[1]));
                        if ($datafile_save['type']=='jpeg' || $datafile_save['type']=='jpg' || $datafile_save['type']=='png'|| $datafile_save['type']=='gif') {
                            $datafile_save['type'] = 'image';
                        }

                        if($datafile_save['type']=='mp4' || $datafile_save['type']=='mov'|| $datafile_save['type']=='rmvb'|| $datafile_save['type']=='avi' ){
                            $datafile_save['type']='video';

                        }else if($datafile_save['type']=='mp3' || $datafile_save['type']=='wav' || $datafile_save['type']=='aac'|| $datafile_save['type']=='amr'  ){
                            $datafile_save['type']='audio';

                        }else if($datafile_save['type']=='docx' || $datafile_save['type']=='doc' ){
                            $datafile_save['type']='word';

			}else if($datafile_save['type']=='pptx'){
                            $datafile_save['type']='ppt';
                        }


                        if ($datafile_save['type']=='mp4' || $datafile_save['type']=='mov' || $datafile_save['type']=='mp3' || $datafile_save['type']=='wmv'|| $datafile_save['type']=='flv'|| $datafile_save['type']=='avi') {
                            $vid = $_POST['vid'];
                            $vid_fullpath = $_POST['vid_fullpath'];
                            $vidarr = explode(",",$vid);
                            $vid_fullpatharr = explode(",",$vid_fullpath);

                            if (!empty($vidarr[$vk])) {
                                $datafile_save['vid'] = $vidarr[$vk];
                            }
                            if (!empty($vid_fullpatharr[$vk])) {
                                $datafile_save['vid_fullpath'] = $vid_fullpatharr[$vk];
                            }

                        }


                        $datafile_save['create_at'] = time();
                        $sf = M('social_activity_contact_file')->add($datafile_save);
                        if ( $sf == false ) {
                            $islock = false;
                            $Model->rollback();
                        }
                    }
                }
            }


            if (!empty($codenameinfo)) {
                foreach ($codenameinfo as $k=>$v) {
                    if (!empty($v)) {
                        $datacode['activity_id'] = $check['id'];
                        $datacode['invitation_code'] = $v;
                        $datacode['status'] = 1;
                        $datacode['create_at'] = time();
                        $sc = M('social_activity_invitation_code')->add($datacode);
                        if ( $sc == false ) {
                            $islock = false;
                            $Model->rollback();
                        }
                    }
                }
            }

            $delgcmap['activity_id'] = $check['id'];

            $delgc = M('social_activity_course_grade')->where( $delgcmap )->delete();

            if ($delgc == false && $delgc!=0 ) {
                $islock = false;
                $Model->rollback();
            }


            /*$grade_second=$_POST['grade'];
            $course_second=$_POST['course'];
            $second['activity_id']=$check['id'];

            $social_activity_course_grade_second=M('social_activity_course_grade');

            for($k=0;$k<count($course_second);$k++){
                $second['course']=$course_second[$k];
                $second['grade']=$grade_second[$k];
                if(!$second_result=$social_activity_course_grade_second->add($second)){
                    $islock = false;
                    $Model->rollback();
                }
            }*/

            $is_all=$_POST['allgradeorcourse'];
            $allcount = count($is_all);

            if ($allcount==2) { //选择了全学科和全年级
                $second['course']=0;
                $second['grade']=0;
                $second['activity_id']=$check['id'];

                $social_activity_course_grade_second=M('social_activity_course_grade');
                if(!$second_result=$social_activity_course_grade_second->add($second)){
                    $islock = false;
                    $Model->rollback();
                }

                //全部选中
                $social_activity_is_select['is_grade_select'] = 1;
                $social_activity_is_select['is_course_select'] = 1;
                $save_is_select = M('social_activity')->where("id=".$hid)->save($social_activity_is_select);

                if( $save_is_select===false || $save_is_select<0 ){

                    $islock = false;
                    $Model->rollback();
                }

            } else {

                if ($allcount == 0 || $allcount =='' ) {
                    $grade_second=$_POST['grade'];
                    $course_second=$_POST['course'];
                    $second['activity_id']=$check['id'];
                    $social_activity_course_grade_second=M('social_activity_course_grade');

                    if (count($course_second) > 0) {
                        $grade_second=$_POST['grade'];
                        $course_second=$_POST['course'];
                        $second['activity_id']=$check['id'];

                        $social_activity_course_grade_second=M('social_activity_course_grade');
                        for($k=0;$k<count($course_second);$k++){
                            $second['course']=$course_second[$k];
                            $second['grade']=$grade_second[$k];
                            if(!$second_result=$social_activity_course_grade_second->add($second)){
                                $islock = false;
                                $Model->rollback();
                            }

                            if ($second['course'] ==0) {
                                $social_activity_is_select['is_course_select'] = 1;

                                $save_is_select = M('social_activity')->where("id=".$hid)->save($social_activity_is_select);
                                if( $save_is_select===false || $save_is_select<0   ){
                                    $islock = false;
                                    $Model->rollback();
                                }

                            }else {
                                $social_activity_is_select['is_grade_select'] = 1;
                                $save_is_select = M('social_activity')->where("id=".$hid)->save($social_activity_is_select);
                                if( $save_is_select===false || $save_is_select<0   ){
                                    $islock = false;
                                    $Model->rollback();
                                }
                            }

                        }
                    } else {
                        $social_activity_course_grade_second=M('social_activity_course_grade');
                        $second['course']=0;
                        $second['grade']=0;
                        $second['activity_id']=$check['id'];
                        if(!$second_result=$social_activity_course_grade_second->add($second)){
                            $islock = false;
                            $Model->rollback();
                        }
                    }
                } else {
                    $grade_second=$_POST['grade'];
                    $course_second=$_POST['course'];
                    $second['activity_id']=$check['id'];

                    $social_activity_course_grade_second=M('social_activity_course_grade');
                    for($k=0;$k<count($course_second);$k++){
                        $second['course']=$course_second[$k];
                        $second['grade']=$grade_second[$k];
                        if(!$second_result=$social_activity_course_grade_second->add($second)){
                            $islock = false;
                            $Model->rollback();
                        }

                        if ($second['course'] ==0) {
                            $social_activity_is_select['is_course_select'] = 1;

                            $save_is_select = M('social_activity')->where("id=".$hid)->save($social_activity_is_select);
                            if( $save_is_select===false || $save_is_select<0   ){
                                $islock = false;
                                $Model->rollback();
                            }

                        }else {
                            $social_activity_is_select['is_grade_select'] = 1;
                            $save_is_select = M('social_activity')->where("id=".$hid)->save($social_activity_is_select);
                            if( $save_is_select===false || $save_is_select<0   ){
                                $islock = false;
                                $Model->rollback();
                            }
                        }

                    }
                }

            }


            if( $islock == false) {
                $this->error("修改失败");

            } else {
                if($oldActivityInfo['activitystart'] != $data['activitystart']) {
                    $teacherIds = D('Social_activity')->getRegisteredIds($check['id'], ROLE_TEACHER);
                    $parameters = array('msg' => array($oldActivityInfo['title'], date("Y-m-d H:i:s", $oldActivityInfo['activitystart']), date("Y-m-d H:i:s", $data['activitystart'])), 'url' => array('type' => 0));
                    A('Home/Message')->addPushUserMessage('ACTIVITY_STARTTIME_MODIFIED', 2, implode(',', array_column($teacherIds,'id')), $parameters);
                }
                $Model->commit();
                $this->redirect("Admin/activitiesMgmt");
            }

            $this->redirect("Admin/activitiesMgmt");

        } else {
            $this->assign('module', '励耘圈管理');
            $this->assign('nav', '京版活动管理');
            $this->assign('subnav', '修改活动');

            $id = $_GET['id'];
            $this->assign('id', $id);

            $Model = M('social_activity');
            $result = $Model->where("id=$id")->find();
            $this->assign('child_class', $result['class_id']);

            $result['activitystart'] = date("Y-m-d H:i:s", $result['activitystart']);
            $result['activityend'] = date("Y-m-d H:i:s", $result['activityend']);
            $result['applystart'] = date("Y-m-d H:i:s", $result['applystart']);
            $result['applyend'] = date("Y-m-d H:i:s", $result['applyend']);

            $Model = M('social_activity_class');
            $classes = $Model->order('sort_order asc')->where('parent_id=0')->select();

            $this->assign('classes', $classes);

            //根据活动id获取活动的资源
            $res = M('social_activity_contact_file')->where("activity_id=".$id)->select();

            $this->assign('resource_list', $res);

            $classrow = M('social_activity_class')->where('id='.$result['class_id'])->find();

            if (!empty($classrow['parent_id'])) {
                $result['class_id'] = $classrow['parent_id'];
            } 


            $this->assign('data', $result);

            $childdata = M('social_activity_class')->where('parent_id='.$result['class_id'])->select();

            $this->assign('childdata',$childdata);

            $codelist = M('social_activity_invitation_code')->where("activity_id=".$id)->select();
            $where['activity_id'] = $id;
            $class_grade=M('social_activity_course_grade')->where($where)
                ->join("left join dict_grade ON dict_grade.id = social_activity_course_grade.grade")
                ->join("left join dict_course on dict_course.id=social_activity_course_grade.course")
                ->field("dict_grade.id grade_id,dict_grade.grade,dict_course.id course_id,dict_course.course_name")->select();

            foreach ($class_grade as $gv=>&$gk) {
                if ( empty($gk['course_id']) && empty($gk['course_name']) ) {
                    $gk['course_id'] = 0;
                    $gk['course_name'] = '全学科';
                }

                if ( empty($gk['grade_id']) && empty($gk['grade']) ) {
                    $gk['grade_id'] = 0;
                    $gk['grade'] = '全年级';
                }
            }

            if( $result['is_grade_select'] ==1 && $result['is_course_select'] ==1 ) {
                $this->assign('is_panduan', 1);
            }

            //print_r($class_grade);die();

            $this->assign('grade_class_select', $class_grade);

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);



            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            $this->assign('codelist', $codelist);
            $this->display();
        }
    }

    //京版活动详情
    public function activityDetails($id)
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '励耘圈管理');
        $this->assign('nav', '京版活动管理');
        $this->assign('subnav', '活动详情');

        $SocialActivity = M('social_activity');
        $check['id'] = $id;
        $result = $SocialActivity->where($check)->find();      
        if ($result['class_id']) {
            $mokuaimap['id'] = $result['class_id'];
            $mokuai = M('social_activity_class')->where($mokuaimap)->find();
            $result['class_name_data'] = $mokuai['class'];
            $result['parent_class_id'] = $mokuai['parent_id'];
        }
        //查询所有的资源
        $res = M('social_activity_contact_file')->where("activity_id=".$check['id'])->select();
        $this->assign('resource_list', $res);
        $codelist = M('social_activity_invitation_code')->where("activity_id=".$id)->select(); //查询邀请码
        $this->assign('codelist', $codelist);
        $this->assign('data', $result);
        $this->assign('res_id', $check['id']);
        $data = D('Message')->getCoursesAndGrades();

        $this->assign('courses', $data['courses']);
        $this->assign('grades', $data['grades']);

        $this->display();
    }
    //根据京版活动id获取报名情况
    public function getActivityListInfo() {
        $id = $_GET['id'];
        $queryParas = $_GET;
        $keyword = $queryParas['keyword'];
        $status = intval($queryParas['lock_status']);
        $where = array();
        if (!empty($status)) {
            $where['social_activity_works.status'] = $status-1;
        }
        if (session('admin.role') == 3) {
            $where['auth_teacher.school_id'] = session('admin.school_id');
        }
        if (!empty($queryParas['province_id']))
            $where['dict_schoollist.provice_id'] = $queryParas['province_id'];
        if (!empty($queryParas['city_id']))
            $where['dict_schoollist.city_id'] = $queryParas['city_id'];
        if (!empty($queryParas['country_id']))
            $where['dict_schoollist.district_id'] = $queryParas['country_id'];
        if (!empty($queryParas['school_id']))
            $where['social_activity_register.school_id'] = $queryParas['school_id'];
        if (!empty($queryParas['course_id']))
            $where['social_activity_register.course'] = $queryParas['course_id'];
        if (!empty($queryParas['grade_id']))
            $where['social_activity_works.grade'] = $queryParas['grade_id'];
        if (!empty($queryParas['teacher_telephone']))
            $where['auth_teacher.telephone'] = array('like', '%' . $queryParas['teacher_telephone'] . '%');


        if (!empty($queryParas['register_at'])) {
            $startTime = $this->getMonthRange($queryParas['register_at'], true);
            $endTime = $this->getMonthRange($queryParas['register_at'], false);
            $where['social_activity_register.register_at'] = array(
                array('lt', $endTime),
                array('egt', $startTime)
            );
        }

        if (!empty($keyword)) {
            $where['social_activity_register.user_name|auth_teacher.telephone|social_activity_register.lesson'] = array('like', '%' . $keyword . '%');
        }

        $SocialActivityRegister = M('social_activity_register');
        $where['social_activity_register.activity_id'] = $id;
        $registerDetails = $SocialActivityRegister->where($where)
            ->join("left join social_activity_works on social_activity_works.activity_register_id=social_activity_register.id")
            ->join("left join auth_teacher on auth_teacher.id=social_activity_register.user_id")
            ->join("left join auth_teacher_second ON auth_teacher_second.teacher_id = auth_teacher.id")
            ->join("left join dict_course ON social_activity_register.course = dict_course.id")
            ->join("left join dict_grade ON social_activity_works.grade = dict_grade.id")
            ->join("left join biz_class ON biz_class.class_teacher_id = auth_teacher.id")
            ->join("left join dict_schoollist on dict_schoollist.id=social_activity_register.school_id")
            ->field('social_activity_register.lesson,auth_teacher.email,register_info,social_activity_works.works_name,social_activity_works.works_description,social_activity_register.id,social_activity_register.user_name as name,auth_teacher.telephone,dict_schoollist.school_name,social_activity_register.register_at,social_activity_register.invitation_code,social_activity_register.invitation_code,social_activity_works.status,social_activity_works.point,social_activity_works.voted_title,dict_grade.grade,dict_course.course_name')
            ->group('social_activity_register.id')
            ->select();
        for($i=0;$i<sizeof($registerDetails);$i++)
        {
            switch($registerDetails[$i]['point'])
            {
                case 1:$registerDetails[$i]['point'] = '一等奖';
                        break;
                case 2:$registerDetails[$i]['point'] = '二等奖';
                        break;
                case 3:$registerDetails[$i]['point'] = '三等奖';
                        break;
                default:$registerDetails[$i]['point'] = '';
                        break;
            }
        }
        //print_r(M()->getLastSql());die();
        $this->assign('registerDetails',$registerDetails);
        $this->assign('activity',D('Social_activity')->getActivityDetails($id));
        $html = $this->fetch('getActivityListInfo');
        $datainfo['info'] = $html;
        $this->ajaxReturn($datainfo);
    }

    //导出排名信息根据活动id
    public function exportRanking(){
        $id = $_GET['id'];
        $activityInfo = D('Social_activity')->getActivityDetails($id);
        if($activityInfo['class_id'] <5)
            $this->exportedActivityRegister($id);
        $queryParas = $_GET;
        $keyword = $queryParas['keyword'];
        $status = intval($queryParas['lock_status']);
        $where = array();
        if (!empty($status)) {
            $where['social_activity_works.status'] = $status-1;
        }
        if (session('admin.role') == 3) {
            $where['auth_teacher.school_id'] = session('admin.school_id');
        }
        if (!empty($queryParas['province_id']))
            $where['dict_schoollist.provice_id'] = $queryParas['province_id'];
        if (!empty($queryParas['city_id']))
            $where['dict_schoollist.city_id'] = $queryParas['city_id'];
        if (!empty($queryParas['country_id']))
            $where['dict_schoollist.district_id'] = $queryParas['country_id'];
        if (!empty($queryParas['school_id']))
            $where['auth_teacher.school_id'] = $queryParas['school_id'];
        if (!empty($queryParas['course_id']))
            $where['auth_teacher_second.course_id'] = $queryParas['course_id'];
        if (!empty($queryParas['grade_id']))
            $where['auth_teacher_second.grade_id'] = $queryParas['grade_id'];
        if (!empty($queryParas['teacher_telephone']))
            $where['auth_teacher.telephone'] = array('like', '%' . $queryParas['teacher_telephone'] . '%');


        if (!empty($queryParas['register_at'])) {
            $startTime = $this->getMonthRange($queryParas['register_at'], true);
            $endTime = $this->getMonthRange($queryParas['register_at'], false);
            $where['social_activity_register.register_at'] = array(
                array('lt', $endTime),
                array('egt', $startTime)
            );
        }

        if (!empty($keyword)) {
            $where['auth_teacher.name|social_activity_works.works_name|social_activity_works.works_description'] = array('like', '%' . $keyword . '%');
        }

        $SocialActivityRegister = M('social_activity_register');
        $where['social_activity_register.activity_id'] = $id;
        $registerDetails = $SocialActivityRegister->where($where)
            ->join("left join social_activity_works on social_activity_works.activity_register_id=social_activity_register.id")
            ->join("left join auth_teacher on auth_teacher.id=social_activity_register.user_id")
            ->join("left join auth_teacher_second ON auth_teacher_second.teacher_id = auth_teacher.id")
            ->join("left join dict_course ON auth_teacher_second.course_id = dict_course.id")
            ->join("left join dict_grade ON auth_teacher_second.grade_id = dict_grade.id")
            ->join("left join biz_class ON biz_class.class_teacher_id = auth_teacher.id")
            ->join("left join dict_schoollist on dict_schoollist.id=auth_teacher.school_id")
            ->field('social_activity_works.works_name,social_activity_works.works_description,social_activity_register.id,auth_teacher.name,auth_teacher.telephone,dict_schoollist.school_name,social_activity_register.register_at,social_activity_register.invitation_code,social_activity_register.invitation_code,social_activity_works.status,social_activity_works.point,social_activity_works.voted_title,dict_grade.grade,biz_class.name as bname,'
                . "GROUP_CONCAT(DISTINCT dict_course.course_name SEPARATOR '.')course_name,"
                . "GROUP_CONCAT(dict_grade.grade,biz_class.name SEPARATOR '.')grade_name")
            ->group('social_activity_register.id')
            ->select();

        $str="报名人,手机号码,参评课题,邀请码,奖项\n";
        $str=iconv('utf-8','gb2312', $str);
        foreach($registerDetails as $v){
            $name=iconv('utf-8','gbk', $v['name']);
            $telephone=iconv('utf-8','gb2312', $v['telephone']);
            $voted_title=iconv('utf-8','gbk', $v['voted_title']);
            $invitation_code=iconv('utf-8','gbk', $v['invitation_code']);
            $point=iconv('utf-8','gbk', $v['point']);
            switch($point)
            {
                case 1:$point = '一等奖';
                    break;
                case 2:$point = '二等奖';
                    break;
                case 3:$point = '三等奖';
                    break;
                default:$point = '';
                    break;
            }
            $point=iconv('utf-8','gbk', $point);
            $str.=$name.",".$telephone.",".$voted_title.",".$invitation_code.",".$point."\n";
        }
        $filename=date('Ymd').rand(0,1000).'point'.'.csv';
        $csv=new CSV();
        $csv->downloadFileCsv($filename,$str);

    }

    //导入排名
    public function importRanking() {
        $id = $_GET['id'];
        $activityDetails = D('Social_activity')->getActivityDetails($id);
        if( time() < $activityDetails['applyend'])
         {
             $data['status']=1004;
             echo json_encode($data);die;
         }

        /*if( 0 == $activityDetails['is_pack'])
        {
            $data['status']=1005;
            echo json_encode($data);die;
        }*/

        if(empty($_FILES)){
            $data['status']=1001;
            echo json_encode($data);die;
        }
        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){
            $data['status']=$result;
            echo json_encode($data);die;
        }
        $encode = mb_detect_encoding($result['result'][0][0], array('UTF-8','GB2312','GBK','EUC-CN'));
        if($encode=='EUC-CN' || $encode=='GB2312'){
            $is_utf8=1;
        }else if($encode=='GBK'){
            $is_utf8=2;
        }else if($encode=='UTF-8'){
            $is_utf8=0;
        }
        $data=$result['result'];
        $length=$result['length'];


        $allNumber =  $length - 1;
        $successNumber = 0;

        if($allNumber == 0) //empty file
        {
            $this->ajaxReturn(array('status' => 1002));
        }
        for( $i=1;$i<$length;$i++) {

            $telphone = $this->encode_string($is_utf8,$data[$i][1]); //手机号码
            $data[$i][0] = $this->encode_string($is_utf8,$data[$i][0]); //name
            if(!empty($telphone)) {
                $user_id = M('auth_teacher')->where( "telephone=".$telphone )->find();

                $map = array();
                $map['activity_id'] = $id;
                $map['user_id'] = $user_id['id'];//根据手机号码获取用户id

                $userinfo = M('social_activity_register')->where($map)->find();

                $savpoint['point'] = $this->encode_string($is_utf8,$data[$i][4]);

                if('一等奖' == $savpoint['point'])
                    $savpoint['point'] = 1;
                else if('二等奖' == $savpoint['point'])
                    $savpoint['point'] = 2;
                else if('三等奖' == $savpoint['point'])
                    $savpoint['point'] = 3;
                else
                    $savpoint['point'] = '';
                if(!empty($savpoint['point']) && !empty($userinfo)) {
                    $wmap['activity_register_id'] = $userinfo['id'];
                    $workInfo = M('social_activity_works')->where( $wmap )->find();
                     if(!empty($workInfo)) {
                         $save_id = M('social_activity_works')->where($wmap)->save($savpoint);
                         $successNumber++;
                     }
                     else
                     {
                         $errorArray[] = $data[$i];
                         $errorInfo[] = '用户未上传作品';
                     }
                }
                else{
                    $errorArray[] = $data[$i];
                    $errorInfo[] = '用户未注册或奖项设置错误';
                }

            }

        }
        //var_dump($successNumber);echo $allNumber;exit;
        if($successNumber != $allNumber)
        {

            $returnData['status']=1003;
            $returnData['all_number'] = $allNumber;
            $returnData['success_number'] = $successNumber;
            $returnData['notice_data'] = $errorArray;
            $returnData['notice_info'] = $errorInfo;
            $this->ajaxReturn(($returnData));
        }
        else if(D('Social_activity')->getHasPointPeopleNumber($id) == D('Social_activity')->getWorkUploadPeopleNumber($id))
        {
            $saveData['works_show_status'] = 1;
            M('social_activity')->where('id='.$id)->save($saveData);
        }
        $this->ajaxReturn('success');

    }

    //审核通过
    public function reviewedAdopt() {
        $id = $_GET['id'];
        $data['activity_register_id'] = $id;

        $res = M('social_activity_works')->where($data)->find();

        if (!empty($res)) {
            $savedata['status'] = 1;
            $wid = M('social_activity_works')->where($data)->save($savedata);

            if ($wid) {
                $this->ajaxReturn('success');
            }else {
                $this->ajaxReturn('error');
            }
        } else {
            $this->ajaxReturn(1001);
        }

    }

    //拒绝审核
    public function refuseAdopt() {
        $id = $_GET['id'];
        $content = $_GET['content'];
        $data['activity_register_id'] = $id;

        $res = M('social_activity_works')->where($data)->find();
        if (!empty($res)) {

            $savedata['status'] = 2;
            $savedata['error_data'] = $content;
            $wid = M('social_activity_works')->where($data)->save($savedata);

            if ($wid) {
                $this->ajaxReturn('success');
            } else {
                $this->ajaxReturn('error');
            }
        }  else {
            $this->ajaxReturn(1001);
        }

    }


    public function resdownfile()
    {
        $filepath = $_GET['path'];

        header('Content-Description: File Transfer');

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($filepath));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
    }
    //手动打分
    public function saveScore(){
        $id = $_GET['id'];
        $num = $_GET['num'];
        $data['activity_register_id'] = $id;

        $activityDetails = D('Social_activity')->getActivityDetails($id);
        if( time() < $activityDetails['applyend'])

            $this->ajaxReturn(1002);

        //if( 0 == $activityDetails['is_pack'])
        //    $this->ajaxReturn(1003);

        $da =M('social_activity_works')->where($data)->find();

        if (!empty($da)) {
            $savedata['point'] = $num;
            $res = M('social_activity_works')->where($data)->save( $savedata );
            $activityId = M('social_activity_register')->where('id='.$id)->field('activity_id id')->find();
            $activityId = $activityId['id'];
            if ($res !== false) {
                $successNumber = M('social_activity_works')->join('social_activity_register ON social_activity_register.id = social_activity_works.activity_register_id')
                    ->where('social_activity_works.point <> 0 and social_activity_register.activity_id='.$activityId)->field('count(1) as num')->find();
                $successNumber = $successNumber['num'];
                if(D('Social_activity')->getHasPointPeopleNumber($activityId) == D('Social_activity')->getWorkUploadPeopleNumber($activityId)) //所有人均打分
                {
                    $saveData['works_show_status'] = 1;
                    M('social_activity')->where('id='.$activityId)->save($saveData);
                }
                $this->ajaxReturn('success');
            }else{
                $this->ajaxReturn('error');
            }
        } else {
            $this->ajaxReturn(1001);
        }


    }

    //查看报名详情
    public function lookRanking() {
        $this->assign('module', '京版活动管理');
        $this->assign('nav', '京版活动管理');
        $this->assign('subnav', '报名/作品信息');
        $id = $_GET['id'];

        $sar = M('social_activity_register')
                ->join("left join dict_course ON dict_course.id = social_activity_register.course")
                ->join('social_activity ON social_activity.id = social_activity_register.activity_id')
                ->where("social_activity_register.id=".$id)->find();

        $saw = M('social_activity_works')
            ->join("left join dict_course ON dict_course.id = social_activity_works.course")
            ->join("left join dict_grade ON social_activity_works.grade = dict_grade.id")
            ->field("social_activity_works.*,dict_course.course_name,dict_grade.grade")
            ->where("activity_register_id=".$id)->find();




        if (!empty($sar['user_id'])) {
            $map['auth_teacher.id'] = $sar['user_id'];
            $userinfo = M('auth_teacher')
                ->join("left join auth_teacher_second ON auth_teacher_second.teacher_id = auth_teacher.id")
                ->join("left join dict_course ON auth_teacher_second.course_id = dict_course.id")
                ->join("left join dict_grade ON auth_teacher_second.grade_id = dict_grade.id")
                ->join("left join biz_class ON biz_class.class_teacher_id = auth_teacher.id")
                ->join("left join dict_schoollist on dict_schoollist.id=auth_teacher.school_id")
                ->field('dict_schoollist.provice_id,dict_schoollist.city_id,dict_schoollist.district_id,auth_teacher.name,dict_course.course_name,dict_schoollist.school_name,dict_schoollist.school_address,auth_teacher.sex,auth_teacher.email')
                ->where($map)->find();
        }

        $quxianarr = array(
            'id' => $userinfo['provice_id']
        );
        $quxian = M('dict_citydistrict')->where($quxianarr)->find();
        $this->quxian = $quxian['name'];

        $cityarr = array(
            'id' => $userinfo['city_id']
        );
        $cityinfo = M('dict_citydistrict')->where($cityarr)->find();
        $this->cityinfo = $cityinfo['name'];

        $district_idarr = array(
            'id' => $userinfo['district_id']
        );
        $district_idinfo = M('dict_citydistrict')->where($district_idarr)->find();
        $this->district_idinfo = $district_idinfo['name'];

        if(!empty($saw['id'])) {
            $resou_list = M('social_activity_works_file')->where("activity_works_id=".$saw['id'])->select();
            if (empty($resou_list)) {
                $this->assign('is_res_show', 1);
            }
        }
        $this->assign('resource_list', $resou_list);
        $this->assign('sar', $sar);
        $this->assign('saw', $saw);
        $this->assign('userinfo', $userinfo);
        $this->assign('res_id', $id);
        $this->display();
    }

    public function getMonthRange($date, $returnFirstDay = true)
    {
        $timestamp = strtotime($date);
        if ($returnFirstDay) {
            $monthFirstDay = date('Y-m-d 00:00:00', $timestamp);
            return strtotime($monthFirstDay);
        } else {

            $monthLastDay = date('Y-m-d 23:59:59', $timestamp);
            return strtotime($monthLastDay);
        }
    }

    //通过审核活动
    public function approveActivity()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $id = $_GET['id'];
        $Model = M('social_activity');

        $check['id']=$id;
        $result=$Model->where($check)->field('id,title,content,push_status')->find();
        if(empty($result)){
            $this->ajaxReturn('failed');die;
        }
        if($result['push_status']==1){
            $message_id=$id;
            $message_model=D('Message');
            $message_add_data['role_id']='2,3,4';
            $message_add_data['title']='京版活动：'.substr($result['title'],0,50); 
            $message_add_data['truncated_title']=substr($result['content'],0,50);
            $message_add_data['message_content']=$result['content'];
            $message_add_data['receive_type']=1; 
            $message_add_data['message_type']=2;
            
            $message_id=$message_id=$message_model->addMessageInfo($message_add_data); 
            $people_number=$message_model->addMessageReceive($message_id);
            $message_model->updateMessageReceivenum($message_id,$people_number);  
            $parameters=array( 
                'url'=>array(
                    'type'=>1,
                    'data'=>array($id)
                )
            );
            $config_arr=C('PUSH_MESSAGE');
            $format_url=$config_arr['ACTIVITY_PUBLISHED']['FORMAT_URL']; 
            if($parameters['url']['type'] == 0) 
            { 
                $messageUrl = 'http://'. $_SERVER["SERVER_NAME"] .sprintf($format_url, $message_id); 
            }
            else
            { 
                $messageUrl = 'http://'. $_SERVER["SERVER_NAME"] .vsprintf($format_url, $parameters['url']['data']); 
            } 
            A('Home/Message')->sendMessage($message_id,$messageUrl);
            
            $Model->where($check)->save(array('push_status'=>2));
        }
        
        $data['status'] = 2;
        $data['approve_at'] = time();
        $Model->where("id=$id")->save($data);

        $this->ajaxReturn('success');
    }

    //拒绝活动
    public function denyActivity()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $id = $_GET['id'];
        $Model = M('social_activity');

        $data['status'] = 3;

        $Model->where("id=$id")->save($data);

        $this->ajaxReturn('success');
    }

    //审核各种状态
    public function alldeny()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $id = $_GET['id'];
        $status = $_GET['status'];

        switch ($status) {
            case 1:
                $status = 2;
                break;
            case 2:
                $status = 3;
                break;
            case 3:
                $status = 4;
                break;
            case 4:
                $status = 2;
                break;
            case 5:
                $status = 5;
                break;
            case 6:
                $status = 4;
                break;

            default:
                $status = 0;
                break;
        }

        $Model = M('social_activity');
        $data['status'] = $status;
        if($status == 5)
            $data['approve_at'] = time();
        $id = $Model->where("id=$id")->save($data);

        if ( $id ) {
            $this->ajaxReturn('success');
        } else {
            $this->ajaxReturn('error');
        }

    }


    
    //删除活动
    public function deleteActivity()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $id = $_GET['id'];
        $Model = M('social_activity');

        $data['status'] = 3;

        $Model->where("id=$id")->delete();
        $teacherIds = D('Social_activity')->getRegisteredIds($id,ROLE_TEACHER);
        $activityInfo = D('Social_activity')->getActivityDetails($id);
        for($i=0;$i<count($teacherIds);$i++)
        {
            $regInfo = D('Social_activity')->getRegisterInfo($id,$teacherIds[$i]);
            $parameters = array( 'msg' => array($regInfo['register_at'],$activityInfo['title']) , 'url' => array( 'type' => 0));
            A('Home/Message')->addPushUserMessage('ACTIVITY_CANCELED',ROLE_TEACHER,$teacherIds[$i],$parameters);
            
        }
        
        $this->ajaxReturn('success');
    }
    
    /**/
    //下架活动
    public function downActivity()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $id = $_GET['id'];
        $Model = M('social_activity');

        $data['status'] = 1;

        $Model->where("id=$id")->save($data);

        $this->ajaxReturn('success');
    }
    
    //下载学校的示例文件
    public function downloadSchoolFile(){
        $csv=new CSV();
        $file="Public/csv/schoolDemo01.csv";
        $csv->downloadFile($file);
    }
    
    //学校批量导出
    public function exportedSchool(){
        if (!session('?admin')) redirect(U('Index/index'));
        if(empty($_POST)){
            $this->error('参数错误');
        }else{ 
            $Model = M('dict_schoollist');
            
            $condition_arr=I('hid');
            $string='('.rtrim(implode($condition_arr,','),',').')'; 
            $result = $Model
            ->join('auth_admin on auth_admin.school_id=dict_schoollist.id and auth_admin.role=3','left')
            ->field('dict_schoollist.id,dict_schoollist.school_name,dict_schoollist.obligation_person,'
                    . 'dict_schoollist.obligation_tel,dict_schoollist.obligation_email,dict_schoollist.status,auth_admin.name,auth_admin.real_name')
            ->where("dict_schoollist.id in $string")
            ->select();     
            
            $str="学校名称,负责人,负责人邮箱,负责人手机号,管理员名称,管理员账号\n"; 
            $str=iconv('utf-8','gb2312', $str);
            foreach($result as $v){
                $school_name=iconv('utf-8','gbk', $v['school_name']);
                $obligation_person=iconv('utf-8','gbk', $v['obligation_person']);
                $obligation_email=iconv('utf-8','gbk', $v['obligation_email']);
                $obligation_tel=iconv('utf-8','gbk', $v['obligation_tel']);
                $real_name=iconv('utf-8','gbk', $v['real_name']);
                $name=iconv('utf-8','gbk', $v['name']);
                $str.=$school_name.",".$obligation_person.",".$obligation_email.",".$obligation_tel.",".$real_name.",".$name."\n";
            } 
            $filename=date('Ymd').rand(0,1000).'school'.'.csv';
            $csv=new CSV();
            $csv->downloadFileCsv($filename,$str);
        }
    }

    //学校批量导出
    public function exportedSchoolAll(){
        set_time_limit(0);
        if (!session('?admin')) redirect(U('Index/index'));

        $filter['keyword'] = $_REQUEST['keyword'];
        $filter['status']=intval($_REQUEST['status']);
        $filter['province_id'] = $_REQUEST['province_id'];
        $filter['city_id'] = $_REQUEST['city_id'];
        $filter['country_id'] = $_REQUEST['country_id'];
        $filter['school_id'] = $_REQUEST['school_id'];


        if (!empty($filter['keyword'])) {
            $where['dict_schoollist.school_name'] = array('like', '%' . $filter['keyword'] . '%');
            $this->assign('keyword',$filter['keyword']);
        }

        if(!empty($filter['status'])){
            $where['dict_schoollist.status']=$filter['status'];
            $this->assign('status', $filter['status']);
        }

        if (!empty($filter['province_id'])) $where['dict_schoollist.provice_id'] = $filter['province_id'];
        if (!empty($filter['city_id'])) $where['dict_schoollist.city_id'] = $filter['city_id'];
        if (!empty($filter['country_id'])) $where['dict_schoollist.district_id'] = $filter['country_id'];
        if (!empty($filter['school_id'])) $where['dict_schoollist.id'] = $filter['school_id'];


        $Model = M('dict_schoollist');
        $result = $Model
            ->where($where)
            ->join('auth_admin on auth_admin.school_id=dict_schoollist.id and auth_admin.role=3','left')
            ->order('id desc')
            ->field('dict_schoollist.id,dict_schoollist.school_name,dict_schoollist.obligation_person,'
                . 'dict_schoollist.obligation_tel,dict_schoollist.obligation_email,dict_schoollist.status,auth_admin.name,auth_admin.real_name')
            ->select();
        $str="学校名称,负责人,负责人邮箱,负责人手机号,管理员名称,管理员账号\n";
        $str=iconv('utf-8','gb2312', $str);
        foreach($result as $v){
            $school_name=iconv('utf-8','gbk', $v['school_name']);
            $obligation_person=iconv('utf-8','gbk', $v['obligation_person']);
            $obligation_email=iconv('utf-8','gbk', $v['obligation_email']);
            $obligation_tel=iconv('utf-8','gbk', $v['obligation_tel']);
            $real_name=iconv('utf-8','gbk', $v['real_name']);
            $name=iconv('utf-8','gbk', $v['name']);
            $str.=$school_name.",".$obligation_person.",".$obligation_email.",".$obligation_tel.",".$real_name.",".$name."\n";
        }
        $filename=date('Ymd').rand(0,1000).'school'.'.csv';
        $csv=new CSV();
        $csv->downloadFileCsv($filename,$str);

    }
    
    
    //学校批量导入 1001文件为空 1002数据为空    
    public function importSchool(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        if(empty($_FILES)){
            echo 1001;die;
        }
        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){
            echo $result;
            die();
        }
        $data=$result['result'];
        $length=$result['length'];
        $data_values=''; 
         
        $notice_array=array();
        $model=M('dict_schoollist');
        $admin_demol=M('auth_admin');
        $error='success';
        
        for($i=1;$i<$length;$i++){
            $notice_tag=0;
            $data['school_name']=iconv('gb2312', 'utf-8', $data[$i][0]);
            $data['obligation_person']=iconv('gb2312', 'utf-8', $data[$i][1]);
            $data['obligation_email']=iconv('gb2312', 'utf-8', $data[$i][2]);
            $data['obligation_tel']=iconv('gb2312', 'utf-8', $data[$i][3]);
            $admin_data['real_name']=iconv('gb2312', 'utf-8', $data[$i][4]);
            $admin_data['name']=iconv('gb2312', 'utf-8', $data[$i][5]);
            $real_name=$admin_data['real_name'];
            $name=$admin_data['name'];
            
            $school_category=iconv('gb2312', 'utf-8', $data[$i][6]);
            $data['school_address']=iconv('gb2312', 'utf-8', $data[$i][7]);
            $provice=iconv('gb2312', 'utf-8', $data[$i][8]);
            $city=iconv('gb2312', 'utf-8', $data[$i][9]);
            $district=iconv('gb2312', 'utf-8', $data[$i][10]);
            
            //判断手机号是否正确
            if(!preg_match("/^1[34578]{1}\d{9}$/",$data['obligation_tel'])){     
                $notice_message='手机号格式不正确';
                $notice_tag=1;
            } 
            
            if($notice_tag==0){
                $schoolCategory_model=M('dict_schoolcategory');
                $schoolCategory_result=$schoolCategory_model->where("name="."'$school_category'")->field('id')->find();
                if(empty($schoolCategory_result)){
                    $notice_message='学校类别错误';
                    $notice_tag=1;
                }
            }
            if($notice_tag==0){
                $dict_model=M('dict_citydistrict');
                //判断身份,市,区。
                $provice_result=$dict_model->where("upid=0 and name="."'$provice'")->field('id')->find();
                if(empty($provice_result)){
                    $notice_message='省份不存在';
                    $notice_tag=1;
                }
            }
            if($notice_tag==0){
                $provice_id=$provice_result['id'];
                $city_result=$dict_model->where("upid=".$provice_id." and name="."'$city'")->field('id')->find();
                if(empty($city_result)){
                    $notice_message='城市不存在';
                    $notice_tag=1;
                }
            } 
            if($notice_tag==0){
                $city_id=$city_result['id'];
                $district_result=$dict_model->where("upid=".$city_id." and name="."'$district'")->field('id')->find();
                if(empty($district_result)){
                    $notice_message='区县不存在';
                    $notice_tag=1;
                } 
            }
            if($notice_tag==0){
                $data['school_category']=$schoolCategory_result['id'];
                $data['provice_id']=$provice_id;
                $data['city_id']=$city_id;
                $data['district']=$district;

                $admin_result=$admin_demol->where("real_name="."'$real_name' and name="."'$name'")->field('id,real_name,name')->find();  
                if(empty($admin_result)){
                    $notice_message='管理员不存在';
                    $notice_tag=1;
                }else{ 
                    //这里判断该学校负责人联系方式是否存在,存在的话跳过,不存在的话就插入       
                    $obligation_tel=$data['obligation_tel'];
                    $temp_result=$model->where("obligation_tel=$obligation_tel")->field('id')->find();
                    //这里先插入
                    $model->startTrans();
                    if(!empty($temp_result)){
                        $notice_message='负责人已存在';
                        $notice_tag=1;
                        /*
                        $school_id=$temp_result['id'];
                        $insert_id=$school_id;          
                        if($model->where("id=$school_id")->save($data)===false){    
                            $Model->rollback();
                            $error=1;
                            echo $error;die;
                        }*/
                    }else{
                        if(!$insert_id=$model->add($data)){
                            $Model->rollback();
                            $notice_message='数据保存失败';
                            $notice_tag=1;
                        }
                    }

                    $temp['school_id']=$insert_id;
                    $id=$admin_result['id'];
                    if($admin_demol->where("id=$id")->save($temp)===false){
                        $model->rollback();
                        $error=1;
                        echo $error;die;
                    }
                }
            }
            $model->commit();
        }
        echo $error;
        //echo 'success';
    }

    //学校管理
    public function schoolMgmt()
    {
        //http://stu.me/index.php?m=Home&c=Admin&a=schoolMgmt&province_id=1&city_id=1&country_id=37&school_id=59&course_id=3&grade_id=1&status=1&keyword=helloworld
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '字典数据管理');
        $this->assign('nav', '学校管理');
        $this->assign('subnav', '学校列表');

        $filter['keyword'] = $_REQUEST['keyword'];
        $filter['status']=intval($_REQUEST['status']);
        $filter['province_id'] = $_REQUEST['province_id'];
        $filter['city_id'] = $_REQUEST['city_id'];
        $filter['country_id'] = $_REQUEST['country_id'];
        $filter['school_id'] = $_REQUEST['school_id'];


        if (!empty($filter['keyword'])) {
            $where['dict_schoollist.school_name'] = array('like', '%' . $filter['keyword'] . '%');
            $this->assign('keyword',$filter['keyword']);
        }

        if(!empty($filter['status'])){
            $where['dict_schoollist.status']=$filter['status'];
            $this->assign('status', $filter['status']);
        }

        if (!empty($filter['province_id'])) $where['dict_schoollist.provice_id'] = $filter['province_id'];
        if (!empty($filter['city_id'])) $where['dict_schoollist.city_id'] = $filter['city_id'];
        if (!empty($filter['country_id'])) $where['dict_schoollist.district_id'] = $filter['country_id'];
        if (!empty($filter['school_id'])) $where['dict_schoollist.id'] = $filter['school_id'];
        $where['dict_schoollist.flag']=1;

        $filterSelect = array(
            'province' =>1 ,'city'=>1,'country'=>1,'school'=>1,'course'=>0,'grade'=>0,'textbook'=>0
        );
        $this->assign('filterSelect',$filterSelect);

        $where_condition='';
        foreach($filter as $key=>$val){
            $where_condition.='&'.$key.'='.$val;
        }
        $this->assign('condition_str',$where_condition);


        $Model = M('dict_schoollist');
        $count = $Model
            ->join('auth_admin on auth_admin.school_id=dict_schoollist.id and auth_admin.role=3 ','left')
            ->field('dict_schoollist.id')
            ->where($where)
            ->count('dict_schoollist.id');  
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show('queryList');
        
        //dict_schoollist.flag,
        $result = $Model
            ->join('auth_admin on auth_admin.school_id=dict_schoollist.id and auth_admin.role=3','left')
            ->field('dict_schoollist.user_auth,dict_schoollist.auth_end_time,dict_schoollist.id,dict_schoollist.school_name,dict_schoollist.obligation_person,'
                    . 'dict_schoollist.obligation_tel,dict_schoollist.obligation_email,dict_schoollist.status,auth_admin.name,auth_admin.real_name')
            ->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();             //var_dump($result);die; 

        $this->assign('list', $result);
        $this->assign('page', $show);

        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        $this->assign('courses', $courses);


        $Model = M('dict_grade');
        $grades = $Model->select();
        $this->assign('grades', $grades);
        //$this->assign('condition_str',$);
        if(IS_AJAX)
            $this->display('schoolMgmtDetails');
        else
            $this->display();

    }
    
    //删除学校
    public function deleteSchool(){
        if (!session('?admin')) redirect(U('Index/index'));
        if(session('admin.role')==3){
            $this->ajaxReturn('error');
        }else{
            $id = intval(I('id'));
            $Model = M('dict_schoollist');
            $where['id']=$id;
            $data['flag'] =-1; 
            if($Model->where($where)->save($data)){
                $this->ajaxReturn('success');
            }else{
                $this->ajaxReturn('failed');
            } 
        }
    }
    
    //审核通过学校或拒绝学校
    public function reviewedSchool(){
        if (!session('?admin')) redirect(U('Index/index'));
        $status=I('status');
        $id=I('id');
        if($status==1){
            //通过
            $data['status']=2;
        }else{
            //拒绝
            $data['status']=3;
        }
        $Model = M('dict_schoollist');
        $Model->where("id=$id")->save($data);
        $this->ajaxReturn('success');
    }

    //创建学校先注释了
    //新增学校
    function createSchool()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        if ($_POST) {

            $data['school_name'] = remove_xss($_POST['school_name']); //学校名称
            $data['obligation_person'] = remove_xss($_POST['obligation_person']);//负责人姓名
            $data['obligation_email'] = remove_xss($_POST['email']);//负责人邮箱

            $data['obligation_tel'] = remove_xss($_POST['telephone']);//负责人手机号码

            //vip权限入库操作
            $user_auth = $_POST['user_auth'];
            $try_time_from = $_POST['try_time_from'];//开始时间
            $try_time_to = $_POST['try_time_to'];//结束时间
            $timetype = $_POST['try_more_less']; //类型1为试用时间 2为使用时间
            $startime = strtotime($try_time_from);
            $endtime = strtotime($try_time_to);

            $Schooldata = array(
                'school_name'=>$data['school_name'],
                'user_auth' => $user_auth,
                'auth_start_time' => $startime,
                'auth_end_time' => $endtime,
                'timetype' => $timetype,
                'obligation_person'=> $data['obligation_person'],
                'obligation_email' => $data['obligation_email'],
                'obligation_tel' => $data['obligation_tel']
            );

            if (empty($user_auth)) {

                $Schooldata = array(
                    'school_name'=>$data['school_name'],
                    'obligation_person'=> $data['obligation_person'],
                    'obligation_email' => $data['obligation_email'],
                    'obligation_tel' => $data['obligation_tel']
                );
            }

            $admin_id_info = $_POST['admin_id_info'];

            $Model = M('dict_schoollist');
            $admin_Model= M('auth_admin');
            $Model->startTrans();
            //判断手机号是否已经存在
            $tel=$data['obligation_tel'];
            if (!empty($tel)) {
                $tel_result=$Model->where("obligation_tel="."'$tel'")->field('id')->find();
                if(!empty($tel_result)){
                    $this->error('该手机号已经存在');
                }
            }

            //先查询这个是后台管理者是否存在
            if(!empty($admin_id_info)){
                $admin_result=$admin_Model->field('id,real_name')->where("role=3 and id=".$admin_id_info)->select();

                if(empty($admin_result)){
                    $this->error('参数错误');
                }
            }

            if(!($admin_data['school_id']=$Model->add($Schooldata))){

                $Model->rollback();
                $this->error('数据提交失败');
            }

            if(!empty($admin_id_info)) {
                $admin_data['nickname'] = remove_xss($_POST['admin_nickname']);//管理员昵称
                $admin_data['real_name'] = remove_xss($_POST['admin_realname']);//管理员姓名

                $adminsave = $admin_Model->where("id=".$admin_id_info)->save($admin_data);

                if( $adminsave ===false){

                    $Model->rollback();
                    $this->error('数据提交失败');
                }

            }

            $Model->commit();


            $this->redirect("Admin/schoolMgmt");
        } else {
            $this->assign('module', '字典数据管理');
            $this->assign('nav', '学校管理');
            $this->assign('subnav', '新增学校');

            //这里只能找admin的角色等于3的
            $admin_model=M('auth_admin');
            $admin_result=$admin_model->field('id,name as real_name')->where('role=3')->select();
            $this->assign('admin_data', $admin_result);

            $auth = M('account_auth')->field('id,auth_name')->order('id desc')->select();
            $this->assign('auth', $auth);

            $this->display('addSchool');        
        }
    }

    //修改学校
    function modifySchool()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        if ($_POST) {
            
            $check['id'] = $_POST['id'];            
            if(!$check['id']){
                $this->error('参数错误');
            }
            //$data['school_name'] = remove_xss($_POST['school_name']);
            $data['obligation_person'] = remove_xss($_POST['obligation_person']);
            $data['obligation_email'] = remove_xss($_POST['email']);
            $data['status']=1;
            
            $data['obligation_tel'] = remove_xss($_POST['telephone']);
            $data['admin_id'] = $_POST['admin_id']; //dict_schoollist
            $admin_data['school_id']=$check['id'];
            $admin_data['nickname']=remove_xss($_POST['admin_nickname']);
            $admin_data['real_name']=remove_xss($_POST['admin_realname']);
            $admin_name_id=remove_xss($_POST['admin_name']);
            
            $admin_id=$data['admin_id'];


            /*if (empty($admin_id)) {
                $this->error('此学校未设置管理员账号');
            }*/

            $Model = M('dict_schoollist');
            $Model->startTrans();
            $admin_Model= M('auth_admin');
            //判断手机号是否已经存在
            $id=$check['id'];
            $tel=$data['obligation_tel'];

            if (!empty($id) && !empty($tel) ) {
                $tel_result=$Model->where("id != "."'$id'"." and obligation_tel="."'$tel'")->field('id')->find();

                if(!empty($tel_result)){
                    $this->error('该手机号已经存在');
                }
            }


            //先查询这个是后台管理者是否存在
            if (!empty($admin_id) && !empty($id) ) {
                $admin_result=$admin_Model->field('id,real_name')->where("role=3 and id=$admin_id"." and school_id=".$id)->select();

                if(empty($admin_result)){
                    $this->error('参数错误');
                }
            }


            
            if($Model->where($check)->save($data)===false){
                $Model->rollback();
                $this->error('数据提交失败');
            }

            if( !empty($admin_id) ) {

                if ( $admin_id == $admin_name_id) {
                    if($admin_Model->where("id=$admin_id")->save($admin_data)===false){
                        $Model->rollback();
                        $this->error('数据提交失败');
                    }
                } else{
                    if (!empty($admin_name_id)) {
                        if($admin_Model->where("id=$admin_name_id")->save($admin_data)===false){
                            $Model->rollback();
                            $this->error('数据提交失败');
                        } else {

                            $mapadmin_data['school_id'] = '';
                            $admin_Model->where("id=$admin_id")->save($mapadmin_data);
                        }
                    } else {
                        $mapadmin_data['school_id'] = '';
                        $mapadmin_data['nickname'] = '';
                        $mapadmin_data['real_name'] = '';
                        $admin_Model->where("id=$admin_id")->save($mapadmin_data);
                    }

                }
            } else {

                if (!empty($admin_name_id)) {

                    if($admin_Model->where("id=$admin_name_id")->save($admin_data)===false){
                        $Model->rollback();
                        $this->error('数据提交失败');
                    }
                }
            }

            
            //vip权限入库操作
            $user_auth = $_POST['user_auth'];
            $try_time_from = $_POST['try_time_from'];//开始时间
            $try_time_to = $_POST['try_time_to'];//结束时间
            $timetype = $_POST['try_more_less']; //类型1为试用时间 2为使用时间
            switch ($user_auth) {
                case '1':
                    $auth_admin_log_name = "游客模式";
                    break;
                
                case '2':
                    $auth_admin_log_name = "普通权限";
                    break;
                case '3':
                    $auth_admin_log_name = "团体VIP";
                    break;
            }

            if (!empty($user_auth) &&!empty($try_time_from) && !empty($try_time_to) && !empty($timetype) ) {
                if ( $user_auth['user_auth'] != 1 || $user_auth['user_auth'] != 2 ) { //就剩vip权限进行入库操作

                    $schoolmap['id'] = $id;
                    $type_auth_is = M('dict_schoollist')->where( $schoolmap )->field('user_auth,auth_start_time,auth_end_time,timetype')->find();

                    $startime = strtotime($try_time_from);
                    $endtime = strtotime($try_time_to);

                    /*if ($startime == $endtime) {
                        $this->error('VIP时间相同请重新选择');
                    }*//*if ($startime == $endtime) {
                    $this->error('VIP时间相同请重新选择');
                }*/

                    $viptimelong=round(($endtime-$startime)/3600/24) ;//计算时长
                    $root_id = session('admin.id');
                    $root_name = session('admin.name');

                    if ( $user_auth != $type_auth_is['user_auth']) { //说明父级权限发生了变化，就进行修改权限操作

                        $School_list = D('School_list');
                        $all_person = $School_list->getMemberList($id);//根据学校id获取学校所有人员

                        $auth_type_use = D('Account_auths');

                        $Schooldata = array(
                            'user_auth' => $user_auth['user_auth'],
                            'auth_start_time' => $startime,
                            'auth_end_time' => $endtime,
                            'timetype' => $timetype,
                        );

                        $isc = $auth_type_use->insertSchool( $id, $Schooldata ); //更新主表学校表中的权限 开始时间 结束时间
                        $remark = "{$root_name}操作: 开通{$auth_admin_log_name}权限";
                        $log_id = $auth_type_use->insertAuthLog($timetype,$edittype,$viptimelong,$endtime,$root_id,$remark,$id,1);

                        if ( !$isc || !$log_id ) { //如果入库失败则回滚操作
                            $Model->rollback();
                            $this->error('数据提交失败');
                        }

                        foreach ($all_person as $pk => $pv) {
                            foreach ($pv as $ck => $cv) {
                                $child_id = $auth_type_use->inserNode($cv['id'],$pk,$user_auth['user_auth'],$startime,$endtime,$timetype); //添加 教师 学生 家长 子节点数据
                                $log_id = $auth_type_use->insertAuthLog($timetype,$edittype,$viptimelong,$endtime,$root_id,$remark,$cv['id'],$pk);
                                if (!$child_id) { //如果子数据入库失败则回滚操作
                                    $Model->rollback();
                                    $this->error('数据提交失败');
                                }
                            }
                        }

                    } else {

                        if ($startime != $type_auth_is['auth_start_time'] || $endtime != $type_auth_is['auth_end_time']  ) {

                            $School_list = D('School_list');
                            $all_person = $School_list->getMemberList($id);//根据学校id获取学校所有人员

                            $auth_type_use = D('Account_auths');

                            $Schooldata = array(
                                'user_auth' => $user_auth['user_auth'],
                                'auth_start_time' => $startime,
                                'auth_end_time' => $endtime,
                                'timetype' => $timetype,
                            );

                            $isc = $auth_type_use->insertSchool( $id, $Schooldata ); //更新主表学校表中的权限 开始时间 结束时间
                            $remark = "{$root_name}操作: 开通{$auth_admin_log_name}权限";
                            $log_id = $auth_type_use->insertAuthLog($timetype,$edittype,$viptimelong,$endtime,$root_id,$remark,$id,1);

                            if (!$isc) { //如果入库失败则回滚操作
                                $Model->rollback();
                                $this->error('数据提交失败');
                            }

                            foreach ($all_person as $pk => $pv) {
                                foreach ($pv as $ck => $cv) {
                                    $child_id = $auth_type_use->updateNode($cv['id'],$pk,$user_auth['user_auth'],$startime,$endtime,$timetype); //添加 教师 学生 家长 子节点数据
                                    $log_id = $auth_type_use->insertAuthLog($timetype,$edittype,$viptimelong,$endtime,$root_id,$remark,$cv['id'],$pk);
                                    if (!$child_id) { //如果子数据入库失败则回滚操作
                                        $Model->rollback();
                                        $this->error('数据提交失败');
                                    }
                                }
                            }

                        } else { //时间相同

                            if ($timetype != $type_auth_is['timetype']) {

                            }
                        }
                    }

                }
            }



            $Model->commit();   

            $this->redirect("Admin/schoolMgmt");
        } else {
            $this->assign('module', '字典数据管理');
            $this->assign('nav', '学校管理');
            $this->assign('subnav', '修改学校信息');
            $id = $_GET['id'];
            if(!$id){
                $this->error('参数错误');
            }
            $Model = M('dict_schoollist');
            
            //$where['auth_admin.role']=3;
            $where['dict_schoollist.id']=$id;
            $result = $Model
            ->join('auth_admin on auth_admin.school_id=dict_schoollist.id and auth_admin.role=3','left')
            ->field('dict_schoollist.timetype,dict_schoollist.user_auth,dict_schoollist.auth_start_time,dict_schoollist.auth_end_time,dict_schoollist.id,dict_schoollist.school_name,dict_schoollist.obligation_person,'
                    . 'dict_schoollist.obligation_tel,dict_schoollist.obligation_email,dict_schoollist.status,'
                    . 'auth_admin.name,auth_admin.real_name,auth_admin.id admin_id,auth_admin.nickname') 
            ->where($where) 
            ->find();           
                
            //这里只能找admin的角色等于3的
            //$admin_model=M('auth_admin');
            //$admin_result=$admin_model->field('id,real_name')->where('role=3')->select();
            if (!empty($result['auth_start_time'])) {
                $result['auth_start_time'] = date('Y-m-d',$result['auth_start_time']);
            } else {
                unset($result['auth_start_time']);
            }
            if (!empty($result['auth_end_time'])) {
                $result['auth_end_time'] = date('Y-m-d',$result['auth_end_time']);
            } else {
                unset($result['auth_end_time']);   
            }
            //这里只能找admin的角色等于3的
            $admin_model=M('auth_admin');
            $admin_result=$admin_model->field('id,name as real_name')->where('role=3')->select();
            $this->assign('admin_data', $admin_result);
            
            
            $this->assign('data', $result);
            //$this->assign('admin_data', $admin_result);
            $auth = M('account_auth')->field('id,auth_name')->order('id desc')->select();
            $this->assign('auth', $auth);
            $this->display();
        }
    }

    //学科管理
    function courseMgmt()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '字典数据管理');
        $this->assign('nav', '学科管理');
        $this->assign('subnav', '学科列表');

        $Model = M('dict_course');
        $result = $Model->select();

        $this->assign('list', $result);

        $this->display();
    }

    //年级管理
    function gradeMgmt()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '字典数据管理');
        $this->assign('nav', '年级管理');
        $this->assign('subnav', '年级列表');

        $Model = M('dict_grade');
        $result = $Model->where('is_delete = 0')->select();

        $this->assign('list', $result);

        $this->display();
    }

    //小黑板管理
    public function blackboardMgmt()
    {

        if (!session('?admin')) redirect(U('Index/index'));

        if(session('admin.role')==3){ 
            $check['biz_class.school_id'] = session('admin.school_id');
        }
                

        if(session('admin.role')!=3){ 
            $filter['province'] = intval($_REQUEST['province']);
            $filter['city'] = intval($_REQUEST['city']);
            $filter['district'] = intval($_REQUEST['district']);  
            $filter['school'] = intval($_REQUEST['school']);
            $filter['keyword'] = $_REQUEST['keyword']; 
            $filter['date'] = $_REQUEST['date'];
            
            if (!empty($filter['province'])) $check['dict_schoollist.provice_id'] = $filter['province'];
            if (!empty($filter['city'])) $check['dict_schoollist.city_id'] = $filter['city'];
            if (!empty($filter['district'])) $check['dict_schoollist.district_id'] = $filter['district'];
            if (!empty($filter['school'])) $check['dict_schoollist.id'] = $filter['school'];
            if (!empty($filter['keyword'])) $check['_string'] ="(biz_blackboard.message_title like '%".$filter['keyword']."%' or auth_teacher.name like '%".$filter['keyword']."%' or "
                    . "biz_blackboard.message like '%".$filter['keyword']."%')"; 
            if(!empty($check['_string'])){
                if (!empty($filter['date'])) $check['_string'] .= " and (biz_blackboard.create_at>=".strtotime(I("date"))." and "."biz_blackboard.create_at<=".(strtotime(I("date")."+1 day")-1).")";
            }else{
                if (!empty($filter['date'])) $check['_string'] = " biz_blackboard.create_at>=".strtotime(I("date"))." and "."biz_blackboard.create_at<=".(strtotime(I("date")."+1 day")-1);
            }
        };
        
        //print_r($check);die(); 

        $this->assign('module', '小黑板管理');
        $this->assign('nav', '小黑板管理');
        $this->assign('subnav', '小黑板信息列表');

        $Model = M('biz_blackboard');
        $count = $Model
            ->join("LEFT JOIN biz_class on biz_blackboard.class_id = biz_class.id")
            ->join("LEFT JOIN dict_grade on biz_class.grade_id=dict_grade.id")
            ->join("LEFT JOIN auth_teacher on biz_blackboard.publisher_id = auth_teacher.id")
            ->join("LEFT JOIN dict_schoollist on auth_teacher.school_id = dict_schoollist.id")
            ->field("biz_blackboard.*,biz_class.name as class_name,dict_grade.grade")
            ->where($check)
            ->count();
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));

        /*foreach ($filter as $key => $val) {
            $Page->parameter[$key] = $val;
        }*/

        $show = $Page->show();

        $result = $Model
            ->join("LEFT JOIN biz_class on biz_blackboard.class_id = biz_class.id")
            ->join("LEFT JOIN dict_grade on biz_class.grade_id=dict_grade.id")
            ->join("LEFT JOIN auth_teacher on biz_blackboard.publisher_id = auth_teacher.id")
            ->join("LEFT JOIN dict_schoollist on auth_teacher.school_id = dict_schoollist.id")
            ->field("biz_blackboard.*,biz_class.name as class_name,dict_grade.grade,dict_schoollist.id as sid")
            ->order('biz_blackboard.create_at desc')
            ->where($check)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();                  

        $this->assign('list', $result);
        $this->assign('page', $show);

        
        //条件是否存在 
        /*if(!empty($filter['province'])) $check2['dict_schoollist.provice_id'] = intval($_GET['province']); 
        if(!empty($filter['city'])) $check2['dict_schoollist.city_id'] = intval($_GET['city']);
        if(!empty($filter['district'])) $check2['dict_schoollist.district_id'] = intval($_GET['district']);
        if(!empty($filter['school'])) $check2['dict_schoollist.id'] = intval($_GET['school']);  
         */
        $check2=$check;
        
        //取出所有省份 
        $register_check['social_activity_register.activity_id']=$id;
        $social_activity_register=M('social_activity_register'); 
        $district_model=M('dict_citydistrict');
        $province=$district_model->field('id,name')->where("upid=0")->select();
        //省份不为空,取出市 
        if(!empty($check2['dict_schoollist.provice_id'])){  
            if($check2['dict_schoollist.provice_id']==1 || $check2['dict_schoollist.provice_id']==2 || $check2['dict_schoollist.provice_id']==9 || $check2['dict_schoollist.provice_id']==22 
                    || $check2['dict_schoollist.provice_id']==33 || $check2['dict_schoollist.provice_id']==34){    
                switch ($check2['dict_schoollist.provice_id']) {        
                    case 1: 
                            $city_result[0]['id']=1;
                            $city_result[0]['name']='北京市';
                        break;
                    case 2: 
                            $city_result[0]['id']=1;
                            $city_result[0]['name']='天津市';
                        break;
                    case 9: 
                            $city_result[0]['id']=9;
                            $city_result[0]['name']='上海市';
                        break;
                    case 22: 
                            $city_result[0]['id']=22;
                            $city_result[0]['name']='重庆市';
                        break;
                    case 33: 
                            $city_result[0]['id']=33;
                            $city_result[0]['name']='香港特别行政区';
                        break;
                    case 34: 
                            $city_result[0]['id']=34;
                            $city_result[0]['name']='澳门特别行政区';
                        break;
                    default:
                        break;
                }
            }else{ 
                $city_result=$district_model->field('id,name')->where("upid=".$check2['dict_schoollist.provice_id'])->select(); 
            } 
        }  
        //市不为空,取出区县
        if(!empty($check2['dict_schoollist.city_id'])){ 
            $district_result=$district_model->field('id,name')->where("upid=".$check2['dict_schoollist.city_id'])->select(); 
        } 
        //区县不为空,取出学校
        if(!empty($check2['dict_schoollist.district_id'])){
            $school_model=M('dict_schoollist');
            $school_result=$school_model->field('id,school_name')->where("district_id=".$check2['dict_schoollist.district_id'])->order("school_name asc")->select();
        } 
        $this->assign('province_result', $province);    
        $this->assign('city_result',$city_result);
        $this->assign('district_result',$district_result);
        $this->assign('school_result',$school_result);
        
        
        $this->assign('province', $check2['dict_schoollist.provice_id']);   
        $this->assign('city', $check2['dict_schoollist.city_id']);
        $this->assign('district', $check2['dict_schoollist.district_id']);
        $this->assign('school', $check2['dict_schoollist.id']);
        $this->assign('keyword', $filter['keyword']);
        $this->assign('default_date', $filter['date']);
        
        $this->display();
    }
    
    //修改小黑板
    function modifyBlackboard(){
        if (!session('?admin')) redirect(U('Index/index'));
        $this->assign('module', '小黑板管理');
        $this->assign('nav', '小黑板管理');
        $this->assign('subnav', '小黑板消息详情');
        
        $Model = M('biz_blackboard');
        if($_POST){ 
            $id=intval($_POST['id']);
            $data['message']=$_POST['message'];
            $Model->where('id='.$id)->save($data);
            
            $this->redirect("admin/blackboardMgmt");
        }else{
            $id = $_GET['id']; 
            $check['biz_blackboard.id'] = $id;

            $result = $Model
                ->join("biz_class on biz_blackboard.class_id = biz_class.id")
                ->field("biz_blackboard.*,biz_class.name as class_name")
                ->where($check)->find(); 
            $this->assign('data', $result);
        }
        $this->display();
    }

    //小黑板详情
    function blackboardDetails()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '小黑板管理');
        $this->assign('nav', '小黑板管理');
        $this->assign('subnav', '小黑板消息详情');
        $id = $_GET['id'];
        $Model = M('biz_blackboard');
        $check['biz_blackboard.id'] = $id;

        $result = $Model
            ->join("biz_class on biz_blackboard.class_id = biz_class.id")
            ->field("biz_blackboard.*,biz_class.name as class_name")
            ->where($check)->find();

        $this->assign('data', $result);

        $this->display();
    }

    //删除小黑板信息
    function deleteBlackboardMessage()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $id = $_GET['id'];
        $Model = M('biz_blackboard');
        $Model->where("id=$id")->delete();
        $this->ajaxReturn('success');
    }
                    

    //习题库管理
    function exercisesMgmt()
    {
        if (!session('?admin')) redirect(U('Index/index'));
                    
        $this->assign('nav', '习题库管理');
        $this->assign('subnav', '习题章节');
                    

        $filter['course_id'] = $_REQUEST['course_id'];
        $filter['grade_id'] = $_REQUEST['grade_id'];
        $filter['textbook_id'] = $_REQUEST['textbook_id'];
        $filter['keyword'] = $_REQUEST['keyword'];

        if (!empty($filter['course_id'])) $check['biz_exercise_library_chapter.course_id'] = $filter['course_id'];
        if (!empty($filter['grade_id'])) $check['biz_exercise_library_chapter.grade_id'] = $filter['grade_id'];
        if (!empty($filter['textbook_id'])) $check['biz_exercise_library_chapter.textbook_id'] = $filter['textbook_id']; 
        if (!empty($filter['keyword'])) $check['_string'] = "biz_exercise_library_chapter.chapter like '%".$filter['keyword']."%' or biz_exercise_library_chapter.festival like '%".$filter['keyword']."%' or "
                . "biz_exercise_library.questions like '%".$filter['keyword']."%'"; 

        //年级和学科不为空求出所有教材
        if(!empty($filter['grade_id']) && !empty($filter['course_id'])){
            $textbook_model=M('biz_textbook');
            $textbook_where['grade_id']=$filter['grade_id'];
            $textbook_where['course_id']=$filter['course_id'];
            $textbook_result=$textbook_model->where($textbook_where)->field('id,name')->select();
            $this->assign('textbook',$textbook_result);
        }
        
        $this->assign('course_id', $filter['course_id']);
        $this->assign('grade_id', $filter['grade_id']);
        $this->assign('textbook_id', $filter['textbook_id']);

        $Model = M('biz_exercise_library_chapter');

        $count = $Model
            ->join("biz_exercise_library on biz_exercise_library.chapter_id=biz_exercise_library_chapter.id")
            ->join("dict_grade on dict_grade.id=biz_exercise_library_chapter.grade_id")
            ->join("dict_course on dict_course.id=biz_exercise_library_chapter.course_id")
            ->join("biz_textbook on biz_textbook.id=biz_exercise_library_chapter.textbook_id")
            ->join("biz_exercise_template on biz_exercise_template.id=biz_exercise_library.type")
            ->field("biz_exercise_library_chapter.*,dict_grade.grade,dict_course.course_name,biz_textbook.name as textbook")
            ->where($check)
            ->group("biz_exercise_library_chapter.id")
            ->field('biz_exercise_library_chapter.id')
            ->select();         
        $count=count($count);      
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($filter as $key => $val) { 
            $Page->parameter[$key] = $val;
        }
        $show = $Page->show();


        $result = $Model
            ->join("biz_exercise_library on biz_exercise_library.chapter_id=biz_exercise_library_chapter.id")
            ->join("dict_grade on dict_grade.id=biz_exercise_library_chapter.grade_id")
            ->join("dict_course on dict_course.id=biz_exercise_library_chapter.course_id")
            ->join("biz_textbook on biz_textbook.id=biz_exercise_library_chapter.textbook_id")
            ->join("biz_exercise_template on biz_exercise_template.id=biz_exercise_library.type")
            ->field("biz_exercise_library_chapter.*,dict_grade.grade,dict_course.course_name,biz_textbook.name as textbook")
            ->where($check)
            ->group("biz_exercise_library_chapter.id")
            ->order('create_at desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();  
        
        $exercise_model=D('Exercise_library');
        $all_exercise_result=$exercise_model->getAllExerciseCount();  
        $exercise_actegory=$exercise_model->getExerciseCategory();
        $search_count=$exercise_model->getExerciseInfoCount($check);
        $search_count=$search_count['count'];
        $search_exercise=array();
        $search_exercise[]=array('key'=>'习题资源总数','value'=>$search_count);
        
        foreach($exercise_actegory as $val){
            $check['biz_exercise_template.template_name']=$val['template_name'];
            $data=$exercise_model->getExerciseInfoCount($check);
            $search_exercise[]=array('key'=>$val['template_name'],'value'=>$data['count']);
        }
                    
        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        
        $Model = M('dict_grade');
        $grades = $Model->select();
        
        $this->assign('all_exercise_count_arr', $all_exercise_result);
        $this->assign('search_exercise_arr', $search_exercise);
        $this->assign('keyword', $filter['keyword']);
        $this->assign('list', $result);
        $this->assign('page', $show);

        
        $this->assign('courses', $courses); 
        $this->assign('grades', $grades);

        $this->display();
    }

    //章节详情
    public function exerciseLibraryChapterDetails()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '习题库管理');
        $this->assign('nav', '章节详情');
        $this->assign('subnav', '章节习题');

        $exercise_model=D('Exercise_library');
        
        $id = intval($_GET['id']);
        $category=getParameter('cat','int',false); 
        
        $where['biz_exercise_library.chapter_id']=$id;
        if($category){
            $result=$exercise_model->getExerciseTemplateInfo($category);
            if(!empty($result)){
                $where['biz_exercise_template.template_name']=$result['template_name'];
            }
        }
       
        $Model = M('biz_exercise_library');
        $list = $Model->where($where)
            ->join("biz_exercise_template on biz_exercise_library.type=biz_exercise_template.id")
            ->field("biz_exercise_library.id,biz_exercise_library.question_id,biz_exercise_library.questions,biz_exercise_library.points,biz_exercise_template.template_name")
            ->order('biz_exercise_library.question_id asc')->select();

        
        $exercise_actegory=$exercise_model->getExerciseCategory();  
        $exercise_diffculty=array('1'=>'一星','2'=>'二星','3'=>'三星','4'=>'四星','5'=>'五星');
        
        $this->assign('diffculty', $exercise_diffculty);
        $this->assign('category', $exercise_actegory);
        $this->assign('choose_category', $category);
        $this->assign('list', $list); 
        $this->assign('chapter_id', $id);
        $this->display();

    }


    public function previewExerciseLibraryChapter()
    {
        $this->assign('module', '习题库管理');
        $this->assign('nav', '章节详情');
        $this->assign('subnav', '预览章节习题');

        $id = $_GET['id'];
        $this->assign('id', $id);
        $this->display();
    }

    //预览章节中的习题
    public function previewExerciseLibraryChapterFrame()
    {
        $id = $_GET['id'];
        $this->assign('id', $id);
        $this->display();
    }


    //获取章节中的习题ajax,并连接是否收藏过该习题
    public function getExerciseLibraryChapterDetails()
    { 
        $id = $_GET['id'];
        $num = $_GET['num'];
        $Model = M('biz_exercise_library');
        $c1['biz_exercise_library.chapter_id'] = $id;
        if ($num!=2) {
            $c1['biz_exercise_library.is_pay'] = 1;
        }
        $result = $Model
            ->join('biz_exercise_template on biz_exercise_library.type=biz_exercise_template.id') 
            ->field("biz_exercise_library.*,biz_exercise_template.template_name")
            ->where($c1)
            ->order('biz_exercise_library.question_id asc')->select();
        foreach ($result as $k=> &$v) {
            $quest = explode('<p>',$v['questions']);
            $questone = '<p>'.$quest[1];
            unset($quest[0]);
            unset($quest[1]);
            $questtwo = implode($quest);
            $questtwo = str_replace('</p>','',$questtwo);
            $questtwo = '<p>'.$questtwo.'</p>';
            $v['questions'] = $questone.$questtwo;

        }
        $this->ajaxReturn($result);
    }
 
    //获取章节中的习题ajax
    public function getExerciseLibraryChapterDetailsSimple()
    {
        $id = $_GET['id'];
        $Model = M('biz_exercise_library');
        $c1['biz_exercise_library.chapter_id'] = $id;
        $result = $Model
            ->join('biz_exercise_template on biz_exercise_library.type=biz_exercise_template.id')
            ->field("biz_exercise_library.question_id,biz_exercise_library.questions,biz_exercise_template.template_name")
            ->where($c1)
            ->order('biz_exercise_library.question_id asc')->select();
        foreach ($result as $k=> &$v) {
            $quest = explode('<p>',$v['questions']);
            $questone = '<p>'.$quest[1];
            unset($quest[0]);
            unset($quest[1]);
            $questtwo = implode($quest);
            $questtwo = str_replace('</p>','',$questtwo);
            $questtwo = '<p>'.$questtwo.'</p>';
            $v['questions'] = $questone.$questtwo;

        }
        $this->ajaxReturn($result);
    }


    //创建习题
    public function createExerciseLibrary()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        if ($_POST) {
            $chapterName = remove_xss($_POST['chapter']);
            $festival = remove_xss($_POST['festival']);
            $title = remove_xss($_POST['title']);
            $textbookId = $_POST['textbook_id'];
            //判断章节是否存在
            $ChapterModel = M('biz_exercise_library_chapter');
            $checkChapter['chapter'] = $chapterName;
            $checkChapter['festival'] = $festival;
            $checkChapter['title'] = $title;
            $checkChapter['textbook_id'] = $textbookId;
            
            $chapter = $ChapterModel->where($checkChapter)->find();
            if (empty($chapter)) {
                $chapterData['grade_id'] = $_POST['grade_id'];
                $chapterData['course_id'] = $_POST['course_id'];
                $chapterData['textbook_id'] = $textbookId;
                $chapterData['exercise_count'] = 1;
                $chapterData['create_at'] = time();

                /*//根据空格分隔章节
                $arr = explode(' ', $chapterName);
                $first = $arr[0];
                $second = $arr[1];
                $title = '';
                $festival = '';
                if (strripos($first, "章") != false) {
                    $title = $first;
                };
                if (strripos($second, "节") != false) {
                    $festival = $second;
                };*/
                $chapterData['title'] = $title;
                $chapterData['festival'] = $festival;
                $chapterData['chapter'] = $chapterName;


                $chapterId = $ChapterModel->add($chapterData);
            } else {
                //习题数加1
                $ChapterModel->where($checkChapter)->setInc('exercise_count');
                $chapterId = $chapter['id'];
            }

            $Model = M('biz_exercise_library');
            $data['chapter_id'] = $chapterId;
            $data['update_at'] = time();
            $data['create_at'] = time();
            $data['questions'] = $_POST['questions'];
            $data['question_id'] = $_POST['question_id'];
            $data['type'] = $_POST['type'];
            $data['body'] = $_POST['body'];
            $data['answer'] = $_POST['answer'];
            $data['points'] = $_POST['points'];
            $data['mp3_vid'] = $_POST['mp3_vid'];

            $data['difficulty'] = $_POST['difficulty'];
            $data['knowledge_point'] = $_POST['knowledge_point'];
            $data['options_sort_order'] = $_POST['options_sort_order'];
            $data['explainInDetail'] = $_POST['explainInDetail'];

            $Model->add($data);
            $this->ajaxReturn(array());
        } else {
            $this->assign('module', '习题库管理');
            $this->assign('nav', '创建试题');
            $this->assign('subnav', '');

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            $this->display();
        }
    }

    //编辑习题
    public function editExerciseLibrary()
    {
        if (!session('?admin')) redirect(U('Index/index'));
        if ($_POST) { 
            $chapterName = remove_xss($_POST['chapter']);
            $festival = remove_xss($_POST['festival']);
            $title = remove_xss($_POST['title']);
            
            $textbookId = $_POST['textbook_id'];
            //判断章节是否存在
            $ChapterModel = M('biz_exercise_library_chapter');
            $checkChapter['chapter'] = $chapterName;
            $checkChapter['festival'] = $festival;
            $checkChapter['title'] = $title;
            $checkChapter['textbook_id'] = $textbookId;
            $chapter = $ChapterModel->where($checkChapter)->find();
            
            if (empty($chapter)) {          
                $chapterData['grade_id'] = $_POST['grade_id'];
                $chapterData['course_id'] = $_POST['course_id'];
                $chapterData['textbook_id'] = $textbookId;
                $chapterData['exercise_count'] = 1;
                $chapterData['create_at'] = time();

                /*//根据空格分隔章节
                $arr = explode(' ', $chapterName);
                $first = $arr[0];
                $second = $arr[1];
                $title = '';
                $festival = '';
                if (strripos($first, "章") != false) {
                    $title = $first;
                };
                if (strripos($second, "节") != false) {
                    $festival = $second;
                };*/
                $chapterData['title'] = $title;
                $chapterData['festival'] = $festival;
                $chapterData['chapter'] = $chapterName;

                $chapterId = $ChapterModel->add($chapterData);
            } else {
                $chapterId = $chapter['id'];
            } 

            $Model = M('biz_exercise_library');
            $id = $_POST['id'];
            $data['chapter_id'] = $chapterId;
            $data['update_at'] = time();
            $data['questions'] = $_POST['questions'];
            $data['question_id'] = $_POST['question_id'];
            $data['type'] = $_POST['type'];
            $data['body'] = $_POST['body'];
            $data['answer'] = $_POST['answer'];
            $data['points'] = $_POST['points'];
            $data['mp3_vid'] = $_POST['mp3_vid'];
            $data['difficulty'] = $_POST['difficulty'];
            $data['knowledge_point'] = $_POST['knowledge_point'];
            $data['options_sort_order'] = $_POST['options_sort_order'];
            $data['explainInDetail'] = $_POST['explainInDetail'];

            $Model->where("id=$id")->save($data);
            $this->ajaxReturn(array());

        } else {
            $this->assign('module', '习题库管理');
            $this->assign('nav', '编辑习题');
            $this->assign('subnav', '');

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);


            $id = $_GET['id'];
            $Model = M('biz_exercise_library');
            $result = $Model->where("id=$id")->find();
            $result['body'] = str_replace("'", "", $result['body']);
            $result['body'] = str_replace("[/r/n]", "######", $result['body']);
            $this->assign('data', $result);


            $chapterId = $result["chapter_id"];

            $ChapterModel = M('biz_exercise_library_chapter');
            $chapter = $ChapterModel->where("id=$chapterId")->find();
            $this->assign('chapter', $chapter);

            $courseId = $chapter["course_id"];
            $gradeId = $chapter["grade_id"];

            $Model = M('biz_textbook');
            $textbooks = $Model
                ->where("course_id=$courseId and grade_id=$gradeId")
                ->order('sort_order asc')->select();
            $this->assign('textbooks', $textbooks);

            $Model = M('biz_exercise_template');
            $templates = $Model
                ->where("course_id=$courseId")
                ->select();
            $this->assign('templates', $templates);

            $template = $Model
                ->where("id=" . $result['type'])
                ->find();
            $this->assign('template', $template);

            $this->display();
        }
    }

    //删除习题
    public function deleteExerciseLibrary()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $id = $_GET['id'];
        $Model = M('biz_exercise_library');
        $Model->where("id=$id")->delete();
        $this->ajaxReturn('success');
    }

    //删除习题章节
    public function deleteExerciseLibraryChapter()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $id = $_GET['id'];
        $Model = M('biz_exercise_library_chapter');
        $Model->where("id=$id")->delete();

        $ExerciseModel = M('biz_exercise_library');
        $ExerciseModel->where("chapter_id=$id")->delete();

        $this->ajaxReturn('success');
    }

    //京版概览
    public function jbOverviewMgmt()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '励耘圈管理');
        $this->assign('nav', '京版概览');
        $this->assign('subnav', '编辑');

        //
        $Model = M('biz_bj_overview');
        $content = $Model->select();  
        $this->assign('data', $content[0]);

        $this->display();
    }

    //京版概览 - 修改
    public function modifyjbOverview()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        if ($_POST) {
            $check['id'] = 1;
            $data['status'] =1;
            $data['content'] = $_POST['content'];
            $Model = M('biz_bj_overview');
            $Model->where($check)->save($data);
            $this->redirect("Admin/jbOverviewMgmt");
        }
    }
    
    //京版概览审核通过或拒绝
    public function reviewedjbOverview(){
        if (!session('?admin')) redirect(U('Index/index'));
        $status=I('status');
        $id=I('id');
        if($status==1){
            //通过
            $data['status']=2;
        }else{
            //拒绝
            $data['status']=3;
        }
        $Model = M('biz_bj_overview');
        $Model->where("id=$id")->save($data);
        $this->ajaxReturn('success');
    }
    
    //京版概览下架
    public function downjbOverview(){
        if (!session('?admin')) redirect(U('Index/index'));
        $id = I('id');
        $Model = M('biz_bj_overview');
        $data['status']=1;
        $Model->where("id=$id")->save($data); 

        $this->ajaxReturn('success');
    }
    
    
    

    //京版资源
    function jbresources() 
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '京版资源');
        $this->assign('nav', '京版资源');
        $this->assign('subnav', '');

        $filter['course_id'] = $_REQUEST['course_id'];
        $filter['grade_id'] = $_REQUEST['grade_id'];
        $filter['textbook_id'] = $_REQUEST['textbook_id'];  
        $filter['channel_id'] = $_REQUEST['channel_id'];
        $filter['type'] = $_REQUEST['type'];
        $filter['keyword'] = $_REQUEST['keyword'];
        $filter['sort_column'] = $_REQUEST['sort_column'];
        $filter['status'] = intval(I('status'));

        $status=intval(I('status'));
        if(!empty($status)){  
            $check['biz_bj_resources.status']=$status;
            $this->assign('status', $status);
        }

        if (!empty($filter['course_id'])) $check['biz_bj_resources.course_id'] = $filter['course_id'];
        if (!empty($filter['grade_id'])) $check['biz_bj_resources.grade_id'] = $filter['grade_id'];
        if (!empty($filter['textbook_id'])) $check['biz_bj_resources.textbook_id'] = $filter['textbook_id'];
        if (!empty($filter['type'])) $check['biz_bj_resources.type'] = $filter['type'];       
        if (!empty($filter['keyword'])) $check['biz_bj_resources.name'] = array('like', '%' . $filter['keyword'] . '%');
        if (!empty($filter['channel_id'])) $check['biz_bj_resources.channel_id'] = $filter['channel_id'];
        if (empty($filter['sort_column'])) $filter['sort_column'] = 'create_at';            
        if (empty($filter['status'])) $filter['status'] = '';

        $this->assign('course_id', $filter['course_id']);
        $this->assign('grade_id', $filter['grade_id']);
        $this->assign('textbook_id', $filter['textbook_id']);
        $this->assign('channel_id', $filter['channel_id']);
        $this->assign('type', $filter['type']);
        $this->assign('keyword', $filter['keyword']);
        $this->assign('sort_column', $filter['sort_column']);    
        if(!empty($filter['course_id']) && !empty($filter['grade_id'])){
            
            $TextbookModel = M('biz_textbook');
            $c1['course_id'] = $filter['course_id'];
            $c1['grade_id'] = $filter['grade_id']; 
            $textbook_result = $TextbookModel->where($c1)->field('id,name')->select();   
            $this->assign('textbook_list',$textbook_result);
        }        
        
        $Model = M('biz_bj_resources');

        $count = $Model
            ->join('biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id','left')
            ->join('dict_course on biz_bj_resources.course_id=dict_course.id')
            ->join('dict_grade on biz_bj_resources.grade_id=dict_grade.id')
            ->field('biz_bj_resources.*,biz_textbook.name as textbook,dict_course.course_name,dict_grade.grade')
            ->order("biz_bj_resources." . $filter['sort_column'] . " desc")
            ->where($check)
            ->count();              
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        foreach ($filter as $key => $val) {
            //$Page->parameter[$key] = urlencode($val);
            $Page->parameter[$key] = $val;
        }
        //dump($check);
        $show = $Page->show();
        $result = $Model
            ->join('biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id','left')
            ->join('dict_course on biz_bj_resources.course_id=dict_course.id')
            ->join('dict_grade on biz_bj_resources.grade_id=dict_grade.id')
            ->field('biz_bj_resources.*,biz_textbook.name as textbook,dict_course.course_name,dict_grade.grade')
            ->order("biz_bj_resources." . $filter['sort_column'] . " desc")
            ->where($check)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();             
                    
        $Model = M('dict_course');
        $courses = $Model->order('sort_order asc')->select();
        
        $Model = M('dict_grade');
        $grades = $Model->select();
        
        $Model = M('dict_channel');
        $channel = $Model->select();
        
        $jbresource_model=D('Biz_bj_resources');
        $all_jbresource_result=$jbresource_model->getAllResourceCount();
        $resource_type=$jbresource_model->getJbresourceType();
        $search_resource=array();
        $search_resource[]=array('key'=>'京版资源总数','value'=>$count); 
        
        foreach($resource_type as $key=>$val){
            $check['biz_bj_resources.type']=$key;
            $data=$jbresource_model->getJbresourceInfoCount($check);
            $search_resource[]=array('key'=>$val,'value'=>$data['count']);
        } 
                    
        $this->assign('search_resource_arr', $search_resource);
        $this->assign('all_resource_count_arr', $all_jbresource_result);
        $this->assign('list', $result);
        $this->assign('page', $show);
        $this->assign('courses', $courses);
        $this->assign('grades', $grades); 
        $this->assign('channel', $channel); 
        $this->display();
    }

    //删除京版资源
    public function deleteJBResource()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $id = $_GET['id'];
        $Model = M('biz_bj_resources');
        $Model->startTrans(); 
        if($Model->where("id=$id")->delete()===false){
            $Model->rollback();
            $this->ajaxReturn('failed'); 
        }
        
        $contact_model=M('biz_bj_resource_contact');
        if($contact_model->where("biz_bj_resource_id=".$id)->delete()===false){
            $Model->rollback();
            $this->ajaxReturn('failed');
        }
        $Model->commit();
                
        $this->ajaxReturn('success');
    }

    //创建京版资源
    function createBJResource()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        if ($_POST) {               
            $type = $_POST['type'];
            $ids=array(); 
            $ResourceModel = M('biz_bj_resources');
            $contact_model=M('biz_bj_resource_contact');
            $ResourceModel->startTrans();       
            
            if ($type == 'video' || $type == 'audio') {     
                $vid_array=explode(',', $_POST['vid']); 
                $vid_path_array=explode(',',$_POST['vid_file_path']);
                $playerwidth=explode(',',$_POST['playerwidth']);
                $playerduration=explode(',',$_POST['playerduration']);  
                $vid_fullpath_array=explode(',',$_POST['vid_fullpath']);  
                $vid_image_path_array=explode(',',$_POST['vid_image_path']);
                $vid_transition_status=explode(',',$_POST['is_transition']);
        
                $url_arr=array();
                foreach($vid_array as $key=>$v){ 
                    //$contact_data['biz_bj_resource_id']=$id;
                    $contact_data['vid']=$v;
                    $contact_data['playerwidth']=$playerwidth[$key];
                    $contact_data['playerduration']=$playerduration[$key];
                    $contact_data['vid_image_path']=$vid_image_path_array[$key];
                    $contact_data['vid_fullpath']=$vid_fullpath_array[$key];
                    $contact_data['is_transition']=$vid_transition_status[$key];

                    //$url=$this->mkdirMedia($vid_path_array[$key]); 
                    //$url_arr[]=$url;
                    $url=$vid_path_array[$key];
                    $contact_data['resource_path']=$url;

                    if(($insert_id=$contact_model->add($contact_data))==false){ 
                        $ResourceModel->rollback();
                        $this->error('数据提交失败');
                    } else{
                        $ids[]=$insert_id;
                    }
                }
                 
                $data['vid'] = $vid_array[0];
                $data['file_path']=$vid_path_array[0];
                $data['playerwidth'] = $playerwidth[0];
                $data['playerduration'] = $playerduration[0];
                $data['vid_image_path'] = $vid_image_path_array[0];
                $data['vid_fullpath'] = $vid_fullpath_array[0];
                //$data['mediaSource'] = $_POST['mp4Source'];
                
                
            } else {
                $vid_path_array=explode(',',$_POST['vid_file_path']); 
                 
                //判断有几个文件 
                $new_file=array();  
                for($i=0;$i<count($vid_path_array);$i++){ 
                        //这里先插进去,之后再更新 
                        //$contact_data['biz_bj_resource_id']=$id;
                        $contact_data['resource_path']=$vid_path_array[$i];    

                        if(($insert_id=$contact_model->add($contact_data))==false){ 
                            $ResourceModel->rollback();
                            $this->error('数据提交失败');
                        }else{                      
                            $ids[]=$insert_id;
                        }

                    
                } 
                $data['file_path'] = $vid_path_array[0];
                /*
                // 上传单个文件
                $info = $upload->uploadOne($_FILES['file']);
                if (!$info) {// 上传错误提示错误信息
                    $this->error($upload->getError());
                } else {// 上传成功 获取上传文件信息
                    //echo $info['savepath'] . $info['savename'];
                    $data['file_path'] = $info['savepath'] . $info['savename'];
                }
                 * 
                 */
            }

            $data['flag']=1;
            $data['name'] = remove_xss($_POST['name']);
            $data['description'] = remove_xss($_POST['description']);
            $data['course_id'] = $_POST['course_id'];
            $data['grade_id'] = $_POST['grade_id'];
            $data['textbook_id'] = $_POST['textbook_id'];
            //求出textbook是上册下册还是全一册
            $textbook_model=M('biz_textbook');
            $textbook_result=$textbook_model->where('id='.$data['textbook_id'])->field('id,school_term')->find();
            if(empty($textbook_result)){
                $this->error('参数错误');
            }
            $data['school_term_id']=$textbook_result['school_term']; 
            
            $data['channel_id'] = $_POST['channel_id'];
            $data['content'] = '';//todo
            $data['type'] = $type; 
            $data['status'] = 1;

            $data['create_at'] = time();
            if($_POST['isDisplay'] == NULL)
              $data['isDisplay'] = 0;
            else
              $data['isDisplay'] = 1;
            if($_POST['is_download']==1){
                $data['is_download'] = 1;
            }else{
                $data['is_download'] = 0;
            }
                    
            if($_POST['isPush'] != null){ 
                $data['push_status']=1; 
            } 
            

            if(!$insert_id=$ResourceModel->add($data)){
                $ResourceModel->rollback();
                $ResourceModel->commit('数据提交失败');
            }
             
            $string=implode(',',$ids);
            $string='('.$string.')';
            $contact_after_data['biz_bj_resource_id']=$insert_id;
            $contact_model->where('id in '.$string)->save($contact_after_data);
            
            $ResourceModel->commit();
            $where['biz_bj_resource_id'] = $insert_id;
            $result = $contact_model->where($where)->field('id,resource_path')->select();

            if ('ppt' == $type)
            for($i=0;$i<count($result);$i++) {
                    $pushMQData = array($result[$i]['id'], $insert_id, $result[$i]['resource_path'], OSS_URL, 'bjresource');
                    processMQMessage(CONVERTPPT_EX_NAME,K_ROUTE,'push', implode(' ', $pushMQData));
            }
            $this->redirect("Admin/jbresources");

        } else {
            $this->assign('module', '京版资源');
            $this->assign('nav', '京版资源');
            $this->assign('subnav', '发布资源');

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            $Model = M('biz_textbook');
            $textbooks = $Model->order('sort_order asc')->select();
            $this->assign('textbooks', $textbooks);

            $ts = time();
            $writeToken = "9c538d85-340c-466c-9e35-bb301734eb0d";
            $plain = $ts . $writeToken;
            $hash = md5($plain);


            $this->assign('ts', $ts);
            $this->assign('hash', $hash);

            $Model = M('dict_channel');
            $channel = $Model->select();
            $this->assign('channel', $channel);

            $this->display();
        }
    }

    //编辑京版资源
    function editJBResource()
    {
        if (!session('?admin')) redirect(U('Index/index'));
       
  
        if ($_POST) {      
            $global_tag=0;
            $check['id'] = $_POST['id'];
            if(!$check['id']){
                $this->error('错误参数');
            }                           
            
            $type = $_POST['type'];         
            $id=$check['id'];
            $existed_resource=$_POST['hidden_resource'];    
            
            $ResourceModel = M('biz_bj_resources'); 
            $contact_model = M('biz_bj_resource_contact');
            $ResourceModel->startTrans(); 
    

    
            //这里判断那个已经有的资源是否为空,清除不等于这些ID的数据
            if(count($existed_resource)>0){  
                $resource_ids=implode(',', $existed_resource);
                $resource_ids_string="(".$resource_ids.")";
                if($contact_model->where("biz_bj_resource_id=".$check['id']." and id not in ".$resource_ids_string)->delete()===false){ 
                    $ResourceModel->rollback();
                    $this->error('数据提交失败1');
                }
            }else{
                if($contact_model->where("biz_bj_resource_id=".$check['id'])->delete()===false){ 
                    $ResourceModel->rollback();
                    $this->error('数据提交失败2');
                }
            }    
         if($_POST['vid_file_path']!=''){     
                  
            if ($type == 'video' || $type == 'audio') { 
                 
                $vid_array=explode(',', $_POST['vid']); 
                $vid_path_array=explode(',',$_POST['vid_file_path']);
                $playerwidth=explode(',',$_POST['playerwidth']);
                $playerduration=explode(',',$_POST['playerduration']);
                $vid_fullpath=explode(',',$_POST['vid_fullpath']);
                $vid_image_path=explode(',',$_POST['vid_image_path']);
                $vid_transition_status=explode(',',$_POST['is_transition']);
               
                if(count($vid_array)<=0 && (count($existed_resource)==0)){
                    $this->error('异常操作');
                    //echo '错误页面,非正规操作';die;
                }   
                
                $url_arr=array();
                foreach($vid_array as $key=>$v){ 
                    $contact_data['biz_bj_resource_id']=$id;
                    $contact_data['vid']=$v;
                    $contact_data['playerwidth']=$playerwidth[$key];
                    $contact_data['playerduration']=$playerduration[$key];
                    $contact_data['vid_fullpath']=$vid_fullpath[$key];
                    $contact_data['vid_image_path']=$vid_image_path[$key]; 
                    $contact_data['is_transition']=$vid_transition_status[$key];

                    //$url=$this->mkdirMedia($vid_path_array[$key]); 
                    //$url_arr[]=$url;
                    $url=$vid_path_array[$key];
                    $contact_data['resource_path']=$url;

                    if(($contact_model->add($contact_data))==false){ 
                        $ResourceModel->rollback();
                        $this->error('数据提交失败3');
                    } 
                    $global_tag=1; 
                }  
                //$data['vid']=$vid_array[0];
                //$data['file_path']=$vid_path_array[0];
                //$data['playerwidth'] =$playerwidth[0] ;
                //$data['playerduration'] = $playerduration[0]; 
                
            } else { 
                $vid_path_array=explode(',',$_POST['vid_file_path']);   
                
                //判断有几个文件       
                for($i=0;$i<count($vid_path_array);$i++){ 
                    //这里入库
                    $contact_data['biz_bj_resource_id']=$id;
                    $contact_data['resource_path']=$vid_path_array[$i];    

                    if(($contact_model->add($contact_data))==false){ 
                        $ResourceModel->rollback();
                        $this->error('数据提交失败4');
                    } 
                    $global_tag=1;
                } 
                //$data['file_path'] = $vid_path_array[0];
                
               
                /*$info = $upload->uploadOne($_FILES['file']);
                if (!$info) {// 上传错误提示错误信息 
                    $this->error($upload->getError());
                } else {// 上传成功 获取上传文件信息
                    //echo $info['savepath'] . $info['savename'];
                    $data['file_path'] = $info['savepath'] . $info['savename'];
                }*/
            }      
             
            //$data['status'] = 2; 
            //这里等于0说明,把原来的主表的某些信息也删除了
            /*if($global_tag==0){ 
                if(count($existed_resource)<1){     
                    //错误页面,因为原来得值也没有,心值也没有添加
                    $ResourceModel->rollback();
                    $this->error('数据提交失败5');
                }else{
                    $contact_result=$contact_model->where("biz_bj_resource_id=".$id)->field('resource_path,vid,playerwidth,playerduration')->find();    
                    if ($type == 'video' || $type == 'audio') { 
                        if(empty($contact_result)){
                            $ResourceModel->rollback();
                            $this->error('数据提交失败6');
                        }

                        $data['vid']=$contact_result['vid'];
                        $data['playerwidth'] =$contact_result['playerwidth'];
                        $data['playerduration'] = $contact_result['playerduration'];
                        $data['file_path'] = $contact_result['resource_path'];
                    }else{
                        $data['file_path'] = $contact_result['resource_path'];
                    }
                }
            }*/   
          } 
            $contact_result=$contact_model->where("biz_bj_resource_id=".$id)->field('resource_path,vid,playerwidth,playerduration')->find();        
            if(!empty($contact_result)){
                if ($type == 'video' || $type == 'audio') { 
                    $data['vid']=$contact_result['vid'];
                    $data['playerwidth'] =$contact_result['playerwidth'];
                    $data['playerduration'] = $contact_result['playerduration'];
                    $data['file_path'] = $contact_result['resource_path'];
                    $data['vid_fullpath'] = $contact_result['vid_fullpath'];
                    $data['vid_image_path'] = $contact_result['vid_image_path'];
                }else{
                    $data['file_path'] = $contact_result['resource_path'];
                }
            }else{
                $ResourceModel->rollback();
                $this->error('数据提交失败6');
            } 
            $data['flag']=1;
            $data['status']=1;
            $data['name'] = remove_xss($_POST['name']);
            $data['description'] = remove_xss($_POST['description']);
            $data['course_id'] = $_POST['course_id'];
            $data['grade_id'] = $_POST['grade_id'];
            $data['textbook_id'] = $_POST['textbook_id'];
            //求出textbook是上册下册还是全一册
            $textbook_model=M('biz_textbook');
            $textbook_result=$textbook_model->where('id='.$data['textbook_id'])->field('id,school_term')->find();      
            if(empty($textbook_result)){
                $this->error('参数错误');
            }
            $data['school_term_id']=$textbook_result['school_term'];
            
            $data['channel_id'] = $_POST['channel_id'];
            $data['content'] = '';
            $data['type'] = $type;
            if($_POST['isDisplay'] == NULL)
              $data['isDisplay'] = 0;
            else
              $data['isDisplay'] = 1;
            
            if($_POST['is_download']==1){
                $data['is_download'] = 1;
            }else{
                $data['is_download'] = 0;
            }
            if(($ResourceModel->where($check)->save($data))===false){
                $ResourceModel->rollback();
                $this->error('数据提交失败');
            }
                $ResourceModel->commit();
          
            //die;    
            $this->redirect("Admin/jbresources");

        } else {
            $this->assign('module', '京版资源');
            $this->assign('nav', '京版资源');
            $this->assign('subnav', '修改资源');

            $id = $_GET['id'];
            if(!$id){
                $this->error('错误参数');
            }
            $this->assign('id', $id);

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            $Model = M('biz_bj_resources');
            $result = $Model
                ->join('biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id','left')
                ->join('dict_course on biz_bj_resources.course_id=dict_course.id','left')
                ->join('dict_grade on biz_bj_resources.grade_id=dict_grade.id','left')
                ->field('biz_bj_resources.*,biz_textbook.name as textbook,dict_course.course_name,dict_grade.grade')
                ->where("biz_bj_resources.id=$id")
                ->find();        
                
            if($result['flag']==1){
                $bjResource_oss_path=C('oss_path');
            }else{
                $bjResource_oss_path='/Resources/jb/';
            } 
            $this->assign('real_file_path',$bjResource_oss_path);
            
            $resource_list=$Model->where("biz_bj_resources.id=".$id)->join("biz_bj_resource_contact on biz_bj_resources.id=biz_bj_resource_contact.biz_bj_resource_id")
                            ->field("biz_bj_resource_contact.id,biz_bj_resources.type,biz_bj_resource_contact.resource_path,biz_bj_resource_contact.vid vvid,biz_bj_resource_contact.vid_image_path")->select();
            $this->assign('resource_list', $resource_list);    //echo $Model->getLastSql();die;
            
            //$this->assign('subnav', '修改 ' + $result['name']);
            $this->assign('data', $result);
            $TextbookModel = M('biz_textbook');
            $c1['course_id'] = $result['course_id'];
            $c1['grade_id'] = $result['grade_id'];
            $textbooks = $TextbookModel->where($c1)->select();
            $this->assign('textbooks', $textbooks);

            $Model = M('dict_channel');
            $channel = $Model->select();
            $this->assign('channel', $channel);

            $ts = time();
            $writeToken = "9c538d85-340c-466c-9e35-bb301734eb0d";
            $plain = $ts . $writeToken;
            $hash = md5($plain);

            $this->assign('ts', $ts);
            $this->assign('hash', $hash);
            $this->assign('REMOTE_ADDR',C('REMOTE_ADDR'));
            $this->display();
        }
    }
    
    /*function test_upload(){  
        ini_set("display_errors", "On");
        error_reporting(E_ALL);
        file_put_contents('/home/wwwroot/server/Public/tmp/aa.txt','33');
        $file='/home/wwwroot/server/Public/tmp/'.rand(100,10000).'.jpg';
        $re='';$status='';
        ///usr/local/ffmpeg/bin/
        passthru("ffmpeg -i /home/wwwroot/server/aa.mp4 -y -f image2 -ss 2.010 -t 0.001 -s 800*600 ".$file,$re);
        passthru("ls > aaa.txt"); 
        var_dump($re);
    }*/
    
    function bjresource_video_image_upload(){//    
        $file=$_FILES['file'];
        $array=array();   
        //前端框架每次发送一个file过来
        for($i=0;$i<count($file['name']);$i++){ 
            $suffix=substr($file['name'][$i],strrpos($file['name'][$i],'.')+1); 
            if($suffix=='mp4' || $suffix=='mov' || $suffix=='wmv' || $suffix=='flv' || $suffix=='avi'){ 
                $original_file=$file['tmp_name'][$i];
                $current_file='Public/tmp/'.rand(100,10000).'.jpg';
                //file_put_contents('Public/tmp/aa.txt','33');  
                exec("/usr/local/ffmpeg/bin/ffmpeg -i ".$original_file." -y -f image2 -ss 2.010 -t 0.001 -s 800*600 ".$current_file." ");
                
                //这里判断视频是否是h264格式的
                exec("/usr/local/ffmpeg/bin/ffprobe -i ".$original_file." 2>&1",$output);
                $str=implode('',$output);
                if(strpos($str,'h264')==true){ 
                    $is_h264=1;
                }else{
                    $is_h264=0;
                } 
                
                $temp_file['file']=array();
                $temp_file['file']['name'][0]=rand(100,10000).'.jpg';
                $temp_file['file']['type'][0]='image/jpeg';
                $temp_file['file']['tmp_name'][0]=$current_file;
                $temp_file['file']['error'][0]=0;
                $temp_file['file']['size'][0]=4746507;
                $upload = new \Oss\Ossupload();// 实例化上传类 
                $result=$upload->upload(3,$temp_file,1,0);
                
                @unlink($current_file);
                $data=array(); 
                $data['video_image']=$result[1];
                $data['is_transition']=$is_h264;
                return $data;
                //return $result[1];
            }else{
                return '';
            }
        }
    }
    
    //oss上传
    public function upload_file(){ 
        //处理截图
        $video_array=$this->bjresource_video_image_upload();
        //
        $file_name = $_FILES['file']['name'][0];
        $upload = new \Oss\Ossupload();// 实例化上传类
            $result=$upload->upload(3,$_FILES,1,0); //1 pic 2//
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
            if($uploadOK == 0)
             {
              $arr['msg']='上传失败';
              $arr['code']=-1;
             }
            else
             {
              $arr['msg']='上传成功';
              $arr['code']=0;
             }
            $arr['res']=$result[1];
            $arr['message']=$result[2];
            $arr['name']=$file_name;
            $arr['is_transition']='';
            $arr['message_video_image']='';
            if(is_array($video_array)){
                $arr['message_video_image']=$video_array['video_image'];
                $arr['is_transition']=$video_array['is_transition'];
            }
            echo json_encode($arr);
    }

    public function upload_file_jb(){
        //处理截图
        //$video_array=$this->bjresource_video_image_upload();
        //
        $file_name = $_FILES['file']['name'][0];
        $upload = new \Oss\Ossupload();// 实例化上传类
        $result=$upload->upload(3,$_FILES,7,0); //1 pic 2//
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
        if($uploadOK == 0)
        {
            $arr['msg']='上传失败';
            $arr['code']=-1;
        }
        else
        {
            $arr['msg']='上传成功';
            $arr['code']=0;
        }
        $arr['res']=$result[1];
        $arr['message']=$result[2];
        $arr['name']=$file_name;
        $arr['is_transition']='';
        $arr['message_video_image']='';
        if(is_array($video_array)){
            $arr['message_video_image']=$video_array['video_image'];
            $arr['is_transition']=$video_array['is_transition'];
        }
        echo json_encode($arr);
    }
    
    
    //创建音频或视频
    function mkdirMedia($url){
        //$url="http://mpv.videocc.net/81d5a70802/6/81d5a70802a66f989c9b1dccdd95d4e6_2.mp4";  
        //$url=$_GET['url'];
        $extend=pathinfo($url); 
        $extend=$extend['extension'];
        $dir_path='Resources/jb/';
        if(!is_dir($dir_path)){
            mkdir($dir_path,0777,true);
        }
        $right_string=date('Y-m-d').'/'.md5(rand(1, 10000)).'.'.$extend; 
        $file_name=$dir_path.$right_string;
        
        //$newfname='ca.mp4';
        $file = fopen ($url, "rb");
        $newf = fopen ($file_name, "wb"); 
        
        fwrite($newf,$file);
        /*while(!feof($file)) {
            fwrite($newf, fread($file, 1024 * 20 ) );
        } */
        fclose($open_r); 
        return $right_string;
    } 
    
    //下载视频
    public function test(){ 
        
        $this->display();
    }

    //备课课件模板管理
    public function lessonPlanningMgmt()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $this->assign('module', '字典数据管理');
        $this->assign('nav', '公共课件库管理');
        $this->assign('subnav', '课件列表');

        $page = $_REQUEST['page'];
        if (empty($page)) $page = 1;
        $this->assign('page', $page);

        $keyword = $_GET['keyword'];
        if (empty($keyword)) {
            $where['biz_lesson_planning_template.id'] = array('NEQ', 0);
        } else {
            $where['biz_lesson_planning_template.name'] = array('like', '%' . $keyword . '%');
            $where['biz_lesson_planning_template.description'] = array('like', '%' . $keyword . '%');
            $where['_logic'] = 'OR';
            $this->assign('keyword', $keyword);
        }

        $Model = M('biz_lesson_planning_template');
        $result = $Model
            ->join("dict_course on dict_course.id=biz_lesson_planning_template.course_id")
            ->join("dict_grade on dict_grade.id=biz_lesson_planning_template.grade_id")
            ->join("biz_textbook on biz_textbook.id=biz_lesson_planning_template.textbook_id")
            ->field("biz_lesson_planning_template.*,dict_course.course_name,dict_grade.grade,biz_textbook.name as textbook")
            ->where($where)
            ->page($page, C('PAGE_SIZE_FRONT'))
            ->select();

        $this->assign('list', $result);

        $this->display();
    }

    //发布课件模板
    public function publishLessonPlanningTemplate()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        if ($_POST) {
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 911127886;// 设置附件上传大小
            $upload->exts = array('ppt','pptx');// 设置附件上传类型
            $upload->rootPath = './Resources/lessonplanning/'; // 设置附件上传根目录
            // 上传单个文件
            $info = $upload->uploadOne($_FILES['file']);
            if (!$info) {// 上传错误提示错误信息
                $this->error($upload->getError());
            } else {// 上传成功 获取上传文件信息
                //echo $info['savepath'] . $info['savename'];
            }

            $data['name'] = remove_xss($_POST['name']);
            $data['description'] = remove_xss($_POST['description']);
            $data['course_id'] = $_POST['course_id'];
            $data['grade_id'] = $_POST['grade_id'];
            $data['textbook_id'] = $_POST['textbook_id'];
            $data['file_path'] = $info['savepath'] . $info['savename'];

            $data['type'] = 'PPT';
            $data['status'] = 1;

            $data['create_at'] = time();
            $data['update_at'] = time();

            $ResourceModel = M('biz_lesson_planning_template');

            $ResourceModel->add($data);

            $this->redirect("Admin/lessonPlanningMgmt");

        } else {
            $this->assign('module', '字典数据管理');
            $this->assign('nav', '公共课件库管理');
            $this->assign('subnav', '发布');

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);

            $Model = M('biz_textbook');
            $textbooks = $Model->order('sort_order asc')->select();
            $this->assign('textbooks', $textbooks);

            $this->display();
        }
    }

    //编辑课件模板
    public function editLessonPlanningTemplate()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        if ($_POST) {

            if ($_FILES["file"]["error"] == 0) {
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize = 911127886;// 设置附件上传大小
                $upload->exts = array('ppt,pptx');// 设置附件上传类型
                $upload->rootPath = './Resources/lessonplanning/'; // 设置附件上传根目录
                // 上传单个文件
                $info = $upload->uploadOne($_FILES['file']);
                if (!$info) {// 上传错误提示错误信息
                    $this->error($upload->getError());
                }
                $data['file_path'] = $info['savepath'] . $info['savename'];
            }

            $check['id'] = $_POST['id'];

            $data['name'] = remove_xss($_POST['name']);
            $data['description'] = remove_xss($_POST['description']);
            $data['course_id'] = $_POST['course_id'];
            $data['grade_id'] = $_POST['grade_id'];
            $data['textbook_id'] = $_POST['textbook_id'];

            $data['update_at'] = time();

            $ResourceModel = M('biz_lesson_planning_template');

            $ResourceModel->where($check)->save($data);

            $this->redirect("Admin/lessonPlanningMgmt");
        } else {
            $this->assign('module', '字典数据管理');
            $this->assign('nav', '公共课件库管理');
            $this->assign('subnav', '编辑');

            $Model = M('dict_course');
            $courses = $Model->order('sort_order asc')->select();
            $this->assign('courses', $courses);

            $Model = M('dict_grade');
            $grades = $Model->select();
            $this->assign('grades', $grades);


            $ResourceModel = M('biz_lesson_planning_template');
            $check['id'] = $_GET['id'];
            $data = $ResourceModel->where($check)->find();

            $courseId = $data['course_id'];
            $gradeId = $data['grade_id'];
            $Model = M('biz_textbook');
            $textbooks = $Model
                ->where("course_id=$courseId and grade_id=$gradeId")
                ->order('sort_order asc')->select();
            $this->assign('textbooks', $textbooks);

            $this->assign('data', $data);

            $this->display();
        }
    }

    //删除课件模板
    public function deleteLessonPlanningTemplate()
    {
        if (!session('?admin')) redirect(U('Index/index'));

        $id = $_GET['id'];
        $Model = M('biz_lesson_planning_template');
        $Model->where("id=$id")->delete();
        $this->ajaxReturn('success');
    }
    
    
    //学科创建视图
    function courseCreate(){
        if (!session('?admin')) redirect(U('Index/index'));
        $this->assign('module', '字典数据管理');
        $this->assign('nav', '学科管理');
        $this->assign('subnav', '创建学科');
        $this->display(); 
    }
    
    //学科创建操作  @这里有个排序暂时没有管
    function courseCreateOp(){
        if (!session('?admin')) redirect(U('Index/index')); 
        if(!$_POST){
            $this->ajaxReturn(array('code' => -1,'msg' => '数据不能为空'));
        }
        $data['course_name']=I('course');
        $data['code']=I('code');
        $Model = M('dict_course');
        $Model->data($data)->add();
        $this->ajaxReturn(array('code' => 0,'msg' => '添加成功'));
    }
    
    //学科删除
    function courseDelete(){
        if (!session('?admin')) redirect(U('Index/index'));
        $id = $_GET['id']; 
        $Model = M('dict_course');
        if($Model->where('id='.$id)->delete()){
            echo 1;
        } 
    }
    
    //学科修改视图
    function courseUpdView(){    
        if (!session('?admin')) redirect(U('Index/index'));
        $id=I('id');
        $Model = M('dict_course');
        $result=$Model->where("id=$id")->field('id,course_name,code')->find(); 
        if(empty($result)){
            $this->error();
        } 
        
        $this->assign('data', $result);
        $this->assign('module', '字典数据管理');
        $this->assign('nav', '学科管理');
        $this->assign('subnav', '修改学科');
        $this->display();
    }
    
    //学科修改操作
    function courseUpdOp(){
        if (!session('?admin')) redirect(U('Index/index'));
        $data['course_name']=I('course');
        $data['code']=I('code');
        $id=I('id');
        $Model = M('dict_course');
        $Model->where("id=$id")->save($data);
        redirect(U('admin/courseMgmt'));
    } 
    
    
    //京版本资源下架
    function downJBResource(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        $id=I('id'); 
        $Model = M('biz_bj_resources');
        $data['status']=1;
        if($Model->where("id=$id")->save($data)){
            echo 1;
        }else{
            echo 0;
        }
    }

    //京版资源推送
    function pushJBResource(){
        if (!session('?admin')) redirect(U('Index/index'));
        $id = getParameter('id','int');
        $info = D('Biz_resource')->getResource($id);
        $parameters = array( 'msg' => array(
            $info['name']
        ) ,
            'url' => array( 'type' => 1,'data' => array( $id))
        );
        if(A('Home/Message')->addPushAllMessage('BJRESOURCE_PUBLISHED',$parameters))
         $this->ajaxReturn(array('status' => 200,'message'=>'推送成功'));
        else
         $this->ajaxReturn(array('status' => 500,'message'=>'推送失败'));
    }
    
    //京版资源审核通过或拒绝
    function reviewJBResource(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        $id=I('id');
        $status=I('status');
        $Model = M('biz_bj_resources');
        
        //求出数据
        $check['id']=$id;
        $result=$Model->where($check)->field('id,name,push_status')->find();
        if(empty($result)){
            echo 0;die;
        }
        
        if($status==1){
            //审核通过
            $data['status']=2;
            if($result['push_status']==1){
                //手机三个角色推送 
                
                $message_id=$id;
                $message_model=D('Message');
                $message_add_data['role_id']='2,3,4';
                $message_add_data['title']='京版资源：'.substr($result['name'],0,50); 
                $message_add_data['truncated_title']=substr($result['name'],0,50);
                //$message_add_data['message_content']='';
                $message_add_data['receive_type']=1; 
                $message_add_data['message_type']=1;

                $message_id=$message_id=$message_model->addMessageInfo($message_add_data); 
                $people_number=$message_model->addMessageReceive($message_id);
                $message_model->updateMessageReceivenum($message_id,$people_number);  
                $parameters=array( 
                    'url'=>array(
                        'type'=>0,
                        'data'=>array()
                    )
                );
                $config_arr=C('PUSH_MESSAGE');
                $format_url=$config_arr['BJRESOURCE_PUBLISHED']['FORMAT_URL']; 
                if($parameters['url']['type'] == 0) 
                { 
                    $messageUrl = 'http://'. $_SERVER["SERVER_NAME"] .sprintf($format_url, $message_id); 
                }
                else
                { 
                    $messageUrl = 'http://'. $_SERVER["SERVER_NAME"] .vsprintf($format_url, $parameters['url']['data']); 
                } 
                A('Home/Message')->sendMessage($message_id,$messageUrl);
                
                
                $Model->where($check)->save(array('push_status'=>2));
            }
        }else{
            //审核拒绝
            $data['status']=3;
        }
        if($Model->where("id=$id")->save($data)){
            echo 1;
        }else{
            echo 0;
        }
    }
    
    //创建班级信息视图
    function createClass(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        $this->assign('module', '班级管理');
        $this->assign('nav', '班级管理');
        $this->assign('subnav', '创建班级信息');
         
        $grade_model=M('dict_grade'); 
           
        $grade_result=$grade_model->field('id,grade')->select();
              
        
        $this->assign('grade', $grade_result);   
        $this->display();
    }
    
    //创建班级信息操作
    function createClassOp(){ 
        if (!session('?admin')) redirect(U('Index/index'));
         
        $data['grade_id'] =  intval( I('grade'));   //var_dump($_POST);die;
        $data['name'] = remove_xss($_POST['class']);
        $data['class_teacher_id'] = intval(I('teacher_id'));    
        //$data['course_id']=  intval(I('course'));
        //$phone = I('real_phone');  
        $phone = I('telephone');  
        
        $Model = M('biz_class');
        $teacher_model=M('auth_teacher');
        $grade_model=M('dict_grade');
        $course_model=M('dict_course');
        
        $grade_result=$grade_model->where("id=".$data['grade_id'])->field('id,grade')->find();
        if(empty($grade_result)){
            $this->error('参数错误');
        } 
        $teacher_result=$teacher_model->where("telephone="."'$phone'")->field('id,name,school_id')->find();
        if(empty($teacher_result)){
            $this->error('参数错误');
        } 
        
        
        $data['class_teacher_id']=$teacher_result['id'];
        $data['class_teacher']=$teacher_result['name'];
        $data['create_at']=time(); 
        $data['school_id']=$teacher_result['school_id'];
    
        $Model->add($data);
         
        
        $this->redirect("Admin/classMgmt");
    }
    
    //修改班级信息视图
    function modifyClass(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        $this->assign('module', '班级管理');
        $this->assign('nav', '班级管理');
        $this->assign('subnav', '修改班级信息');
        
        $id =I('id'); 
        $Model = M('biz_class');
        $teacher_model=M('auth_teacher');
        $grade_model=M('dict_grade');
        
        $result = $Model
            ->join('auth_teacher on auth_teacher.id=biz_class.class_teacher_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id') 
            ->field('biz_class.id,biz_class.name,biz_class.student_count,dict_grade.id grade_id,dict_grade.grade,auth_teacher.id teahcer_id,auth_teacher.name teacher_name'
                    . ',auth_teacher.telephone teacher_phone,auth_teacher.email teacher_email')  
            ->where("biz_class.id="."'$id'")
            ->find();           //echo $Model->getLastsql();die;
 
        //$teacher_result=$teacher_model->field('id,name')->select(); 
        
        //拿到所有年级
        //$grade_result=$grade_model->field('id,grade')->select();
        
        $course_model=M('dict_course');
        $course_result=$course_model->field('id,course_name')->select();  
        
        $this->assign('course', $course_result);
        //$this->assign('grade', $grade_result);
        $this->assign('data', $result);
        //$this->assign('teacher', $teacher_result); 
        
        $this->display();
    }
    
    //修改班级信息操作
    function modifyClassOp(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        $where['id']=intval(I('id'));
        if(!$where['id']){
            $this->error('参数错误');
        } 
        
        $data['grade_id'] =  intval( I('grade'));
        $data['name'] = remove_xss($_POST['class']);  
        //$data['course_id']= intval(I('course'));
        $teahcer_data['telephone'] = I('telephone');
        $teacher_phone=$teahcer_data['telephone'] ; 
        
        $Model = M('biz_class');
        $teacher_model=M('auth_teacher');
        $grade_model=M('dict_grade');
        $course_model=M('dict_course');
        
        $grade_result=$grade_model->where("id=".$data['grade_id'])->field('id,grade')->find();      
        if(empty($grade_result)){
            $this->error('参数错误');
        }
        $teacher_result=$teacher_model->where("telephone="."'$teacher_phone'")->field('id,name')->find();   
        if(empty($teacher_result)){
            $this->error('参数错误');
        }
        //判断学科
        /*$course_result=$course_model->where('id='.$data['course_id'])->field('id')->find();
        if(empty($course_result)){
            $this->error('参数错误');
        } */ 
        $data['class_teacher_id']=$teacher_result['id'];
        $data['class_teacher']=$teacher_result['name'];
           
        if($Model->where($where)->save($data)===false){
            $Model->rollback();
            $this->error('数据提交失败');
        }
          
         
        $this->redirect("Admin/classMgmt");
    }
    
    //下载班级信息示例
    function downloadClassFile(){
        $csv=new CSV();
        if(session('admin.role') == 3){ 
            $file="Public/csv/classDemo03.csv";
        }else{
            $file="Public/csv/classDemo01.csv";
        } 
        $csv->downloadFile($file);
    }
    
    //导出全部班级文件
    function exportedClassAll(){
        if (!session('?admin')) redirect(U('Index/index')); 
        $filter['province'] = $_REQUEST['province'];
        $filter['city'] = $_REQUEST['city'];
        $filter['district'] = $_REQUEST['district'];  
        $filter['school'] = $_REQUEST['school'];
        $filter['grade'] = $_REQUEST['grade'];
        $filter['class'] = $_REQUEST['class'];
        $filter['keyword'] = $_REQUEST['keyword'];
        
        if (!empty($filter['province'])) $where['dict_schoollist.provice_id'] = $filter['province'];
        if (!empty($filter['city'])) $where['dict_schoollist.city_id'] = $filter['city'];
        if (!empty($filter['district'])) $where['dict_schoollist.district_id'] = $filter['district'];
        if (!empty($filter['school'])) $where['dict_schoollist.id'] = $filter['school'];
        if (!empty($filter['grade'])) $where['dict_grade.id'] = $filter['grade'];
        if (!empty($filter['class'])) $where['biz_class.id'] = $filter['class'];
        if (!empty($filter['keyword'])) $where['auth_teacher.name']=array('like', '%' . $keyword . '%');
        
        
        $Model = M('biz_class'); 
        if(session('admin.role') == 3){     
            $where['biz_class.school_id']=session('admin.school_id');
            $result=$Model->where($where)->join('auth_teacher on auth_teacher.id=biz_class.class_teacher_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->order('biz_class.id desc')
            ->field('biz_class.id,biz_class.name,biz_class.student_count,dict_grade.grade,auth_teacher.name teacher_name'
                    . ',auth_teacher.telephone teacher_phone,auth_teacher.email teacher_email')  
            ->select();
            $string="年级,班级,班主任姓名,班主任邮箱,班主任手机号\n";
            $string=iconv('utf-8','gb2312', $string);
            foreach($result as $v){
                $grade=iconv('utf-8','gb2312', $v['grade']);
                $name=iconv('utf-8','gb2312', $v['name']);
                $teacher=iconv('utf-8','gbk', $v['teacher_name']);
                $email=iconv('utf-8','gb2312', $v['teacher_email']);
                $tel=iconv('utf-8','gb2312', $v['teacher_phone']);
                $string.=$grade.",".$name.",".$teacher.",".$email.",".$tel."\n";
            }
            $filename=date('Ymd').rand(0,1000).'class_3_all'.'.csv';
        }else{
             $result=$Model->where($where)->join('  auth_teacher on auth_teacher.id=biz_class.class_teacher_id')
            ->join('  dict_grade on dict_grade.id=biz_class.grade_id')
            ->join('  dict_schoollist on dict_schoollist.id=biz_class.school_id')
            ->join('dict_citydistrict province on province.id=dict_schoollist.provice_id','left')
            ->join('dict_citydistrict city on city.id=dict_schoollist.city_id','left')
            ->join('dict_citydistrict district on district.id=dict_schoollist.district_id','left')
                ->order('biz_class.id desc')
            ->field('biz_class.id,biz_class.name,biz_class.student_count,dict_grade.grade,auth_teacher.name teacher_name'
                    . ',auth_teacher.telephone teacher_phone,auth_teacher.email teacher_email,'
                    . 'dict_schoollist.school_name,province.name province,city.name city,district.name district')  
            ->select();
            $string="省份,城市,区县,学校,年级,班级,班主任姓名,班主任邮箱,班主任手机号\n";
            $string=iconv('utf-8','gb2312', $string);
            foreach($result as $v){
                $province=iconv('utf-8','gb2312', $v['province']);
                $city=iconv('utf-8','gb2312', $v['city']);
                $district=iconv('utf-8','gb2312', $v['district']);
                $school=iconv('utf-8','gbk', $v['school_name']);
                
                $grade=iconv('utf-8','gb2312', $v['grade']);
                $name=iconv('utf-8','gb2312', $v['name']);
                $teacher=iconv('utf-8','gbk', $v['teacher_name']);
                $email=iconv('utf-8','gb2312', $v['teacher_email']);
                $tel=iconv('utf-8','gb2312', $v['teacher_phone']);
                $string.=$province.",".$city.",".$district.",".$school.",".$grade.",".$name.",".$teacher.",".$email.",".$tel."\n";
            }
            $filename=date('Ymd').rand(0,1000).'class_1_all'.'.csv';
        } 
        $csv=new CSV();
        $csv->downloadFileCsv($filename,$string); 
    }
    
    //批量导出班级文件
    function exportedClass(){
        if (!session('?admin')) redirect(U('Index/index'));
        if(empty($_POST)){
            $this->error('参数错误');
        }else{ 
            $Model = M('biz_class'); 
            $condition_arr=I('hid');
            $string='('.rtrim(implode($condition_arr,','),',').')'; 
            if(session('admin.role') == 3){
                $result = $Model->where("biz_class.id in $string")
                ->join('auth_teacher on auth_teacher.id=biz_class.class_teacher_id')
                ->join('dict_grade on dict_grade.id=biz_class.grade_id')
                ->field('biz_class.id,biz_class.name,biz_class.student_count,dict_grade.grade,auth_teacher.name teacher_name'
                        . ',auth_teacher.telephone teacher_phone,auth_teacher.email teacher_email')  
                ->select();     
                $string="年级,班级,班主任姓名,班主任邮箱,班主任手机号\n";
                $string=iconv('utf-8','gb2312', $string);
                foreach($result as $v){
                    $grade=iconv('utf-8','gb2312', $v['grade']);
                    $name=iconv('utf-8','gb2312', $v['name']);
                    $teacher=iconv('utf-8','gbk', $v['teacher_name']);
                    $email=iconv('utf-8','gb2312', $v['teacher_email']);
                    $tel=iconv('utf-8','gb2312', $v['teacher_phone']);
                    $string.=$grade.",".$name.",".$teacher.",".$email.",".$tel."\n";
                }
                $filename=date('Ymd').rand(0,1000).'class_3'.'.csv';
            }else{
                $result=$Model->where("biz_class.id in $string")->join('auth_teacher on auth_teacher.id=biz_class.class_teacher_id')
                ->join('dict_grade on dict_grade.id=biz_class.grade_id') 
                ->join('dict_schoollist on dict_schoollist.id=biz_class.school_id')
                ->join('dict_citydistrict province on province.id=dict_schoollist.provice_id')
                ->join('dict_citydistrict city on city.id=dict_schoollist.city_id')
                ->join('dict_citydistrict district on district.id=dict_schoollist.district_id')

                ->field('biz_class.id,biz_class.name,biz_class.student_count,dict_grade.grade,auth_teacher.name teacher_name'
                        . ',auth_teacher.telephone teacher_phone,auth_teacher.email teacher_email,'
                        . 'dict_schoollist.school_name,province.name province,city.name city,district.name district')  
                ->select();
                $string="省份,城市,区县,学校,年级,班级,班主任姓名,班主任邮箱,班主任手机号\n";
                $string=iconv('utf-8','gb2312', $string);
                foreach($result as $v){
                    $province=iconv('utf-8','gb2312', $v['province']);
                    $city=iconv('utf-8','gb2312', $v['city']);
                    $district=iconv('utf-8','gb2312', $v['district']);
                    $school=iconv('utf-8','gbk', $v['school_name']);

                    $grade=iconv('utf-8','gb2312', $v['grade']);
                    $name=iconv('utf-8','gb2312', $v['name']);
                    $teacher=iconv('utf-8','gbk', $v['teacher_name']);
                    $email=iconv('utf-8','gb2312', $v['teacher_email']);
                    $tel=iconv('utf-8','gb2312', $v['teacher_phone']);
                    $string.=$province.",".$city.",".$district.",".$school.",".$grade.",".$name.",".$teacher.",".$email.",".$tel."\n";
                }
                $filename=date('Ymd').rand(0,1000).'class_1'.'.csv';
            } 
            $csv=new CSV();
            $csv->downloadFileCsv($filename,$string); 
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
            $return_string=iconv('gb2312', 'utf-8', $string);
        }else if($code==2){
            $return_string=iconv('gbk', 'utf-8', $string);
        }else if($code==0){
            $return_string=$string;
        }else{
            $return_string=iconv('utf-8', 'gbk', $string);
        }  
        return $return_string;
    }

    //批量导入班级文件      
    function importClass(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        if(empty($_FILES)){
            $data['status']=1001;
            echo json_encode($data);die; 
        }
        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){
            $data['status']=$result;
            echo json_encode($data);
            die();
        } 
        //默认是utf8 
        $encode = mb_detect_encoding($result['result'][0][0], array('UTF-8','GB2312','GBK','EUC-CN')); 
        if($encode=='EUC-CN' || $encode=='GB2312'){
            $is_utf8=1; 
        }else if($encode=='GBK'){
            $is_utf8=2; 
        }else if($encode=='UTF-8'){
            $is_utf8=0;
        } 
                    
        
        $notice_array=array();
        $data=$result['result'];        
        $length=$result['length'];
        $data_values=''; 
         
        $Model = M('biz_class');
        $grade_model = M('dict_grade');
        $teacher_model = M('auth_teacher'); 
        $success_number=0;
        
        //如果没有找到年级或者教师就跳过改角色
        for($i=1;$i<$length;$i++){
            $notice_tag=0;
            
            if(session('admin.role')==3){  
                $grade=$this->encode_string($is_utf8,$data[$i][0]); 

                //这个是班级
                $data['name']= $this->encode_string($is_utf8,$data[$i][1]);
                $data['class_teacher']=$this->encode_string($is_utf8,$data[$i][2]); 
                $data['create_at']=time();

                $teacher_data['email']=$this->encode_string($is_utf8,$data[$i][3]); 
                $teacher_data['telephone']=$this->encode_string($is_utf8,$data[$i][4]);  
                $name=$data['name'];

                $grade_result=$grade_model->where("grade="."'$grade'")->field('id')->find();    
                if(empty($grade_result)){
                    $notice_message='该年级不存在';
                    $notice_tag=1;  
                }

                if($notice_tag==0){
                    $teacher_tel=$teacher_data['telephone'];
                    $teacher_result=$teacher_model->where("telephone="."'$teacher_tel'")->field('id,name')->find();     
                    if(empty($teacher_result)){ 
                        $notice_message='该老师不存在';
                        $notice_tag=1;  
                    }
                    if($notice_tag==0){ 
                        $data['class_teacher']=$teacher_result['name'];
                        $data['class_teacher_id']=$teacher_result['id'];
                        $data['school_id']=session('admin.school_id');
                        $data['grade_id']=$grade_result['id'];

                        //这里判断该老师是否在老师所教的关联表中存在
                        $teach_second_model=M('auth_teacher_second');
                        $teach_result=$teach_second_model->where("teacher_id=".$teacher_result['id']." and grade_id=".$data['grade_id'])->field('id')->find(); 
                        if(empty($teach_result)){
                            $notice_message='该教师没有创建此年级的权限';
                            $notice_tag=1;  
                        }

                        if($notice_tag==0){
                            //判断这个班级是否存在,存在就跳过,不存在就插入
                            $temp_result=$Model->where("name="."'$name' and grade_id=".$data['grade_id']." and school_id=".session('admin.school_id'))->field('id')->find(); 
                            if(!empty($temp_result)){ 
                                $notice_message='该班级已存在';
                                $notice_tag=1;
                            } else{
                                $Model->add($data); 
                            }
                        }
                    }
                }
            }else{ 
                $province=$this->encode_string($is_utf8,$data[$i][0]);        
                
                $city=$this->encode_string($is_utf8,$data[$i][1]);  
                $district=$this->encode_string($is_utf8,$data[$i][2]);      
                $school=$this->encode_string($is_utf8,$data[$i][3]);   
                
                //查询省份,城市,区县,学校是否存在
                $region_model=M('dict_citydistrict');
                $school_model=M('dict_schoollist');
                $province_result=$region_model->where("name='$province'")->field('id')->find(); 
                $city_result=$region_model->where("name='$city'")->field('id')->find(); 
                $district_result=$region_model->where("name='$district'")->field('id')->find();  
                $school_result=$school_model->where("school_name='$school'")->field('id')->find(); 
                if(empty($province_result)){
                    $notice_message='省份不存在';
                    $notice_tag=1;
                }
                if(empty($city_result)){
                    $notice_message='城市不存在';
                    $notice_tag=1;
                }
                if(empty($district_result)){
                    $notice_message='区县不存在';
                    $notice_tag=1;
                }
                if(empty($school_result)){
                    $notice_message='学校不存在';
                    $notice_tag=1;
                }
                if($notice_tag==0){
                    $grade=$this->encode_string($is_utf8,$data[$i][4]); 
                    $data['name']=$this->encode_string($is_utf8,$data[$i][5]); 
                    $data['class_teacher']=$this->encode_string($is_utf8,$data[$i][6]); 
                    $data['create_at']=time(); 
                    $teacher_data['email']= $this->encode_string($is_utf8,$data[$i][7]); 
                    $teacher_data['telephone']=$this->encode_string($is_utf8,$data[$i][8]);
                    $name=$data['name'];
                    
                    $grade_result=$grade_model->where("grade="."'$grade'")->field('id')->find();    
                    if(empty($grade_result)){
                        $notice_message='该年级不存在';
                        $notice_tag=1;  
                    }
                    
                    if($notice_tag==0){
                        $teacher_tel=$teacher_data['telephone'];
                        $teacher_result=$teacher_model->where("telephone="."'$teacher_tel'")->field('id,name')->find();     
                        if(empty($teacher_result)){ 
                            $notice_message='该老师不存在';
                            $notice_tag=1;  
                        }
                        if($notice_tag==0){ 
                            $data['class_teacher']=$teacher_result['name'];
                            $data['class_teacher_id']=$teacher_result['id'];
                            $data['school_id']=$school_result['id'];
                            $data['grade_id']=$grade_result['id'];

                            //这里判断该老师是否在老师所教的关联表中存在
                            $teach_second_model=M('auth_teacher_second');
                            $teach_result=$teach_second_model->where("teacher_id=".$teacher_result['id']." and grade_id=".$data['grade_id'])->field('id')->find(); 
                            if(empty($teach_result)){
                                $notice_message='该教师没有创建此年级的权限';
                                $notice_tag=1;  
                            }

                            if($notice_tag==0){
                                //判断这个班级是否存在,存在就跳过,不存在就插入
                                $temp_result=$Model->where("name="."'$name' and grade_id=".$data['grade_id']." and school_id=".$data['school_id'])->field('id')->find(); 
                                if(!empty($temp_result)){ 
                                    $notice_message='该班级已存在';
                                    $notice_tag=1;
                                } else{
                                    $Model->add($data); 
                                }
                            }
                        }
                    }
                }
            }
            
            if($notice_tag==1){
                $notice_temp_arr=array();
                if(session('admin.role')!=3){
                   $notice_temp_arr[]=$province;
                   $notice_temp_arr[]=$city;
                   $notice_temp_arr[]=$district;
                   $notice_temp_arr[]=$school;
                }
                $notice_temp_arr[]=$grade;
                $notice_temp_arr[]=$data['name'];
                $notice_temp_arr[]=$data['class_teacher'];
                $notice_temp_arr[]=$teacher_data['email'];
                $notice_temp_arr[]=$teacher_data['telephone']; 
                $notice_temp_arr[]=$notice_message;
                
                $notice_array[]=$notice_temp_arr;
                continue;
            }else{
                $success_number++;
                continue;
            } 
        }
        $return_array=array();
        if(!empty($notice_array)){
            $return_array['status']=1003;
        }else{
            $return_array['status']=1004;
        } 
        $return_array['all_number']=($length-1);
        $return_array['success_number']=$success_number;
        $return_array['notice_data']=$notice_array;
        echo json_encode($return_array); 
        //echo 'success';
    }
    
    //删除班级
    function deleteClass(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        $id = I('id');  
        $Model = M('biz_class_student'); 
        $class_model=M('biz_class');  
        $homework_model=M('biz_homework');
        $where['class_id'] = $id; 
        
        
        $Model->startTrans();  
        if($Model->where($where)->delete()===false){       
            $this->ajaxReturn('failed1');die;
        }
        
        if($homework_model->where("class_id=".$id)->delete()===false){
            $Model->rollback();
            $this->ajaxReturn('failed2');die;
        }
        
        
        if($class_model->where('id='.$id)->delete()===false){
            $Model->rollback();
            $this->ajaxReturn('failed3');die;
        }else{
            $Model->commit();
            $this->ajaxReturn('success');die;
        }
    }
       
    //班级信息列表
    function classMgmt(){       
        if (!session('?admin')) redirect(U('Index/index'));
        
        $this->assign('module', '班级管理');
        $this->assign('nav', '班级管理');
        $this->assign('subnav', '列表');
        $this->assign('role', session('admin.role'));
        
        if(session('admin.role')!=3){ 
            $filter['province'] = intval($_REQUEST['province']);
            $filter['city'] = intval($_REQUEST['city']);
            $filter['district'] = intval($_REQUEST['district']);  
            $filter['school'] = intval($_REQUEST['school']);
            
            
            if (!empty($filter['province'])) $where['dict_schoollist.provice_id'] = $filter['province'];
            if (!empty($filter['city'])) $where['dict_schoollist.city_id'] = $filter['city'];
            if (!empty($filter['district'])) $where['dict_schoollist.district_id'] = $filter['district'];
            if (!empty($filter['school'])) $where['dict_schoollist.id'] = $filter['school'];
        };
                    
        if(session('admin.role') == 3){
            $where['biz_class.school_id']=session('admin.school_id');
        }
        
        $filter['grade'] = $_REQUEST['grade'];
        $filter['class'] = $_REQUEST['class'];
        $filter['keyword'] = $_REQUEST['keyword'];
        $keyword=$filter['keyword'];
        $grade= intval(I('grade'));
        $class= (I('class'));
        
        if(!empty($keyword)){
            $where['auth_teacher.name']=array('like', '%' . $keyword . '%');
            $this->assign('keyword',$keyword);
        } 
        if(!empty($grade)){
            $where['dict_grade.id']=$grade;
            
            $class_model=M('biz_class');
            $biz_class_where['dict_grade.id']=$grade;
            if(session('admin.role') == 3){
                //$biz_class_where['biz_class.school_id']=session('admin.school_id');
                $biz_class_where['biz_class.school_id']=session('admin.school_id');
            }else{
                $biz_class_where['biz_class.school_id']=$where['dict_schoollist.id'];
            } 
            $result=$class_model->where($biz_class_where)
                ->join('auth_teacher on auth_teacher.id=biz_class.class_teacher_id')
                ->join('dict_grade on dict_grade.id=biz_class.grade_id')
                ->join("dict_schoollist on dict_schoollist.id=biz_class.school_id") 
                ->field('biz_class.id,biz_class.name') 
                ->group('biz_class.name')    
                ->select(); 
            
            
            $this->assign('grade',$grade);
            $this->assign('class_list',$result); 
        }
        if(!empty($class)){
            $where['biz_class.name']=$class;
            $this->assign('class',$class);
        }            
        
        $Model = M('biz_class');
        $grade_model = M('dict_grade');
        $teacher_model = M('auth_teacher');  

        $where_condition='';
        foreach($filter as $key=>$val){
            $where_condition.='&'.$key.'='.$val;
        } 
        $this->assign('condition_str',$where_condition);

        $count = $Model->where($where)
            ->join('auth_teacher on auth_teacher.id=biz_class.class_teacher_id')->join("dict_schoollist on dict_schoollist.id=biz_class.school_id")
            ->join('dict_grade on dict_grade.id=biz_class.grade_id') 
            ->field('biz_class.id')->select();
        $count=count($count);               
        
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        $result = $Model->where($where)
            ->join('auth_teacher on auth_teacher.id=biz_class.class_teacher_id')
            ->join('dict_grade on dict_grade.id=biz_class.grade_id')
            ->join("dict_schoollist on dict_schoollist.id=biz_class.school_id")
            ->join("biz_class_student on biz_class_student.class_id=biz_class.id and biz_class_student.`status`=2","left")
            ->join("auth_student on auth_student.id=biz_class_student.student_id","left")
            ->field('biz_class.id,biz_class.name,count(auth_student.id) student_count,dict_grade.grade,dict_schoollist.school_name,auth_teacher.name teacher_name'
                    . ',auth_teacher.telephone teacher_phone,auth_teacher.email teacher_email') 
            ->group('biz_class.id')
            ->order('biz_class.id desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)        
            ->select();                
                    
                    
        if(session('admin.role') == 3){
            $grade_result=$grade_model->field('dict_grade.id,dict_grade.grade')
                ->select();   
        }
          
        
        
        $this->assign('list', $result);
        $this->assign('page', $show);
        
        
        //条件是否存在 
        if(!empty($filter['province'])) $check2['dict_schoollist.provice_id'] = intval($_GET['province']); 
        if(!empty($filter['city'])) $check2['dict_schoollist.city_id'] = intval($_GET['city']);
        if(!empty($filter['district'])) $check2['dict_schoollist.district_id'] = intval($_GET['district']);
        if(!empty($filter['school'])) $check2['dict_schoollist.id'] = intval($_GET['school']);
        if(!empty($filter['grade'])) $check2['grade'] = intval($_GET['grade']);
        if(!empty($filter['class'])) $check2['class'] = intval($_GET['class']);
        
        //取出所有省份 
        $register_check['social_activity_register.activity_id']=$id;
        $social_activity_register=M('social_activity_register'); 
        $district_model=M('dict_citydistrict');
        $province=$district_model->field('id,name')->where("upid=0")->select();
        //省份不为空,取出市 
        if(!empty($check2['dict_schoollist.provice_id'])){  
            if($check2['dict_schoollist.provice_id']==1 || $check2['dict_schoollist.provice_id']==2 || $check2['dict_schoollist.provice_id']==9 || $check2['dict_schoollist.provice_id']==22 
                    || $check2['dict_schoollist.provice_id']==33 || $check2['dict_schoollist.provice_id']==34){    
                switch ($check2['dict_schoollist.provice_id']) {        
                    case 1: 
                            $city_result[0]['id']=1;
                            $city_result[0]['name']='北京市';
                        break;
                    case 2: 
                            $city_result[0]['id']=2;
                            $city_result[0]['name']='天津市';
                        break;
                    case 9: 
                            $city_result[0]['id']=9;
                            $city_result[0]['name']='上海市';
                        break;
                    case 22: 
                            $city_result[0]['id']=22;
                            $city_result[0]['name']='重庆市';
                        break;
                    case 33: 
                            $city_result[0]['id']=33;
                            $city_result[0]['name']='香港特别行政区';
                        break;
                    case 34: 
                            $city_result[0]['id']=34;
                            $city_result[0]['name']='澳门特别行政区';
                        break;
                    default:
                        break;
                }
            }else{ 
                $city_result=$district_model->field('id,name')->where("upid=".$check2['dict_schoollist.provice_id'])->select(); 
            } 
        }  
        //市不为空,取出区县
        if(!empty($check2['dict_schoollist.city_id'])){ 
            $district_result=$district_model->field('id,name')->where("upid=".$check2['dict_schoollist.city_id'])->select(); 
        } 
        //区县不为空,取出学校
        if(!empty($check2['dict_schoollist.district_id'])){
            $school_model=M('dict_schoollist');
            $school_result=$school_model->field('id,school_name')->where("district_id=".$check2['dict_schoollist.district_id'])->order("school_name asc")->select();
        }
        //学校不为空,取出年级
        if(!empty($check2['dict_schoollist.id'])){ 
            $grade_result=$Model->where("dict_schoollist.id=".$check2['dict_schoollist.id'])->join("dict_schoollist on dict_schoollist.id=biz_class.school_id")
                    ->join("dict_grade on dict_grade.id=biz_class.grade_id")
                    ->field("dict_grade.id,dict_grade.grade")->group("dict_grade.id")->select();        
        } 
        
        
        
        $this->assign('province_result', $province);    
        $this->assign('city_result',$city_result);
        $this->assign('district_result',$district_result);
        $this->assign('school_result',$school_result);
        
        $this->assign('province', $check2['dict_schoollist.provice_id']);   
        $this->assign('city', $check2['dict_schoollist.city_id']);
        $this->assign('district', $check2['dict_schoollist.district_id']);
        $this->assign('school', $check2['dict_schoollist.id']);
        
        
        $this->assign('grade_data', $grade_result); 
        $this->display();
    }
    
    //根据某个学校拿到其下的所有年级 根据年级分组
    function getSchoolGrade(){
        if (!session('?admin')) redirect(U('Index/index'));
        $id=  intval(I('id'));
        if(!$id){
            echo $data['status']='error';
            echo json_encode($data); 
        }else{
            $Model = M('biz_class');
            $result=$Model->where("dict_schoollist.id=".$id)->join("dict_schoollist on dict_schoollist.id=biz_class.school_id")
                    ->join("dict_grade on dict_grade.id=biz_class.grade_id")
                    ->field("dict_grade.id,dict_grade.grade")->group("dict_grade.id")->select();
            echo json_encode($result);
        } 
    }
    
    //根据某个年级拿到其下的所有班级
    function getClass(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        $id=  intval(I('id'));
        if(!$id){
            echo $data['status']='error';
            echo json_encode($data); die;
        }else{ 
            if(session('admin.role') == 3){
                $where['auth_teacher.school_id']=session('admin.school_id');
                $grade_where=$where;
            }else{
                $school_id=intval(I('school_id'));  
                if(!$school_id){
                    echo $data['status']='error';
                    echo json_encode($data);die;
                }
                $where['auth_teacher.school_id']=$school_id;
            }
           $where['biz_class.grade_id']=$id;
           $Model = M('biz_class'); 
           $result=$Model->where($where)->field('biz_class.id,biz_class.name')
                   ->join("dict_grade on dict_grade.id=biz_class.grade_id")
                   ->join("auth_teacher on auth_teacher.id=biz_class.class_teacher_id")
                   ->join("auth_teacher_second on auth_teacher_second.grade_id=biz_class.grade_id")
                   ->group("biz_class.name")
                   ->select();
           $data['status']='success';
           $data['data']=$result;
           echo json_encode($data);
        } 
    }
    
     
    //删除某班级的某个学生,让这个学生和这个班级之间断开关系
    function deleteClassStudent(){
        if (!session('?admin')) redirect(U('Index/index'));
         
        $id = I('id');
        $class_id=I('class_id');
        $Model = M('biz_class_student');

        $where['class_id'] = $class_id;
        $where['student_id'] = $id;

        $Model->where($where)->delete();    

        $class_model=M('biz_class');
        $class_model->where("id=".$class_id)->setDec("student_count");
        
        $this->ajaxReturn('success');
    }
    
    //某班级创建学生
    function createClassStudent(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        $Model=M('auth_student');
        $parent_model=M('auth_parent');
        $class_model=M('biz_class');
        $contact_model=M('biz_class_student');
        
        if(!empty($_POST)){
            $class_id=  intval(I('class_id'));  
            if(!$class_id){
                $this->error('参数错误');
            }
            //查看班级是否存在
            $class_result=$class_model->where('id='.$class_id)->field('id,school_id')->find();
            if(empty($class_result)){
                $Model->rollback();
                $this->error('班级不存在');
            }
            
            $parent_where['telephone']=  I('telephone');  
            
            $where['id']=  intval(I('id'));
            $sex=  intval(I('sex'));
            if($sex==1){
                $data['sex']='男';
            }else{
                $data['sex']='女';
            }
            
            $data['school_id']=$class_result['school_id'];
            $data['student_name']=I('student_name');
            $data['birth_date']=strtotime(I('birth_date'));
            $data['password']=sha1(I('pwd'));
            
            //查找家长是否存在
            $parent_result=$parent_model->where($parent_where)->field('id,parent_name,email,telephone')->find();    
            if(empty($parent_result)){  
                $this->error('家长不存在');
            }

            $data_info['student_name'] = $data['student_name'];
            $data_info['parent_tel'] = $parent_where['telephone'];
            $find_parent = M('auth_student')->where($data_info)->find();
            if (!empty($find_parent)){
                $this->error('该名学生已经存在');
            }

            $data['create_at']=time();
            $data['parent_id']=$parent_result['id'];
            $data['parent_tel']=$parent_result['telephone'];  
            $data['parent_name']=$parent_result['parent_name'];
            
            $Model->startTrans(); 
            
            if(($insert_id=$Model->add($data))==false){
                $Model->rollback();
                $this->error('数据提交失败');
            }
            
            $contact_data['class_id']=$class_id;
            $contact_data['student_id']=$insert_id;
            $contact_data['create_at']=time();
            $contact_data['status']=2;
            
            if($contact_model->add($contact_data)==false){
                $Model->rollback();
                $this->error('参数错误');
            }  
            $class_model->where("id=".$class_id)->setInc("student_count");
            
            $Model->commit();  
            redirect(U('Admin/classStudentMgmt?id='.$class_id));
            
        }else{
        
            $this->assign('module', '班级管理');
            $this->assign('nav', '班级学生');
            $this->assign('subnav', '创建学生信息');
            
            $class_id=I('class_id');
            if(!$class_id){
                $this->error('参数错误');
            }
            
            //$parent_result=$parent_model->field('id,parent_name')->select();
             
            $this->assign('class_id', $class_id);
            //$this->assign('parent_data', $parent_result);
            $this->display();
        }
    }
     
    
    //修改某个班的学生
    function modifyClassStudent(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        $Model=M('auth_student');
        $parent_model=M('auth_parent');
        
        if (!empty($_POST)) {   
            $class_id=  intval(I('class_id'));  
            $parent_where['telephone']=  I('telephone');
            
            $where['id']=  intval(I('id'));
            $sex=  intval(I('sex'));
            if($sex==1){
                $data['sex']='男';
            }else{
                $data['sex']='女';
            }
            $data['student_name']=I('student_name');
            $data['birth_date']=strtotime(I('birth_date'));  
            
            //查找家长是否存在
            $parent_result=$parent_model->where($parent_where)->field('id,parent_name,telephone')->find();
            if(empty($parent_result)){ 
                $this->error('家长不存在');
            }
            $data['parent_id']=$parent_result['id'];
            $data['parent_tel']=$parent_result['telephone'];
            $data['parent_name']=$parent_result['parent_name'];
             
            if($Model->where($where)->save($data)===false){ 
                $this->error('数据提交失败');
            } 
            if(!$class_id){ 
                redirect(U('Admin/classMgmt'));  
            }else{
                redirect(U('Admin/classStudentMgmt?id='.$class_id));
            }
            
            
        }else{
            $this->assign('module', '班级管理');
            $this->assign('nav', '班级学生');
            $this->assign('subnav', '修改班级学生信息');
        
            $id=intval(I('id'));
            $class_id=  intval(I('class_id'));
            $where['auth_student.id']=$id;
            
            
            
            $result = $Model->where($where)
            ->join('auth_parent on auth_parent.id=auth_student.parent_id') 
            ->join('biz_class_student on biz_class_student.student_id=auth_student.id')
            ->field('auth_student.id,auth_student.student_name,auth_student.birth_date,auth_student.sex'
                    . ',auth_parent.id parent_id,auth_parent.parent_name,auth_parent.email,auth_parent.telephone')  
            ->find();      
            //$parent_result=$parent_model->field('id,parent_name')->select();
             
            $this->assign('class_id', $class_id);
            //$this->assign('parent_data', $parent_result);
            $this->assign('data', $result);
            $this->display();
        }
    }
    
    //下载某班级学生示例
    function downloadClassStudentFile(){
        $csv=new CSV();
        $file="Public/csv/classStudentDemo01.csv";
        $csv->downloadFile($file);
    }
    
    //批量导出这个班级下的所有信息
    function exportedClassStudentAll(){
        if (!session('?admin')) redirect(U('Index/index'));
        if(empty($_GET)){
            $this->error('参数错误');
        }else{ 
            $class_id = intval($_GET['id']);
            $filter['keyword'] = $_REQUEST['keyword'];  
            
            if (!empty($filter['keyword'])) $where['_string'] = "(auth_student.student_name like '%{$filter['keyword']}%') OR (auth_parent.parent_name like '%{$filter['keyword']}%')";
            $where['biz_class_student.class_id'] = $class_id;
            $where['biz_class_student.status']=2;
            if(session('admin.role') == 3){
                $where['auth_student.school_id']=session('admin.school_id'); 
            }
            
            $model = M('auth_student');  
            $result = $model->where($where)->join('auth_parent on auth_parent.id=auth_student.parent_id') 
            ->join('biz_class_student on biz_class_student.student_id=auth_student.id')
            ->field('auth_student.id,auth_student.student_name,auth_student.birth_date,auth_student.sex'
                    . ',auth_parent.parent_name,auth_parent.email,auth_parent.telephone')  
            ->group('auth_student.id')
            ->select();         
            
            $string="学生姓名,学生性别,出生年月,家长姓名,家长邮箱,家长手机号\n";
            $string=iconv('utf-8','gb2312', $string);
            foreach($result as $v){
                $student_name=$this->encode_string(4,$v['student_name']);
                $sex=$this->encode_string(4,$v['sex']);
                $birth_date=date('Y-m-d',$this->encode_string(4,$v['birth_date']));
                $parent_name=$this->encode_string(4,$v['parent_name']);
                $email=$this->encode_string(4,$v['email']);
                $telephone= $this->encode_string(4,$v['telephone']);
                $string.=$student_name.",".$sex.",".$birth_date.",".$parent_name.",".$email.",".$telephone."\n";
            }
            $filename=date('Ymd').rand(0,1000).'classStudent_all'.'.csv';
            $csv=new CSV();
            $csv->downloadFileCsv($filename,$string); 
        }
    } 
    
    
    //批量导出文件
    function exportedClassStudent(){
        if (!session('?admin')) redirect(U('Index/index'));
        if(empty($_POST)){
            $this->error('参数错误');
        }else{
            $model = M('auth_student');
            $condition_arr=I('hid');
            $string='('.rtrim(implode($condition_arr,','),',').')';
            $result = $model->where("auth_student.id in $string")
            ->join('auth_parent on auth_parent.id=auth_student.parent_id') 
            ->join('biz_class_student on biz_class_student.student_id=auth_student.id')
            ->field('auth_student.id,auth_student.student_name,auth_student.birth_date,auth_student.sex'
                    . ',auth_parent.parent_name,auth_parent.email,auth_parent.telephone')  
            ->group('auth_student.id')
            ->select();     
            
            $string="学生姓名,学生性别,出生年月,家长姓名,家长邮箱,家长手机号\n";
            $string=iconv('utf-8','gb2312', $string);
            foreach($result as $v){
                $student_name=$this->encode_string(4,$v['student_name']);
                $sex=$this->encode_string(4,$v['sex']);
                $birth_date=date('Y-m-d',$this->encode_string(4,$v['birth_date']));
                $parent_name=$this->encode_string(4,$v['parent_name']);
                $email=$this->encode_string(4,$v['email']);
                $telephone=$this->encode_string(4,$v['telephone']);
                $string.=$student_name.",".$sex.",".$birth_date.",".$parent_name.",".$email.",".$telephone."\n";
            }
            $filename=date('Ymd').rand(0,1000).'classStudent'.'.csv';
            $csv=new CSV();
            $csv->downloadFileCsv($filename,$string); 
        }
    }
    
    //批量导入班级学生文件
    function importClassStudent(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        if(empty($_FILES)){
            $data['status']=1001;
            echo json_encode($data);die;  
        }
        $csv=new CSV();
        $result=$csv->getCsvData($_FILES);
        if(!is_array($result)){ 
            $data['status']=$result;
            echo json_encode($data);die;  
        }
        //这里要跟上一个参数班级
        $class_id=intval($_GET['id']);
        if(!$class_id){
            $data['status']=1003;
            echo json_encode($data);die;  
        } 
        //默认是utf8 
        $encode = mb_detect_encoding($result['result'][0][0], array('UTF-8','GB2312','GBK','EUC-CN')); 
        if($encode=='EUC-CN' || $encode=='GB2312'){
            $is_utf8=1; 
        }else if($encode=='GBK'){
            $is_utf8=2; 
        }else if($encode=='UTF-8'){
            $is_utf8=0;
        } 
        
        $data=$result['result'];
        $length=$result['length'];
        $data_values=''; 
         
        $model = M('auth_student');
        $parent_model = M('auth_parent');
        $class_model=M('biz_class');
        $class_student=M('biz_class_student');
        $notice_array=array();
        $success_number=0;
        
        //先判断班级是否存在
        $class_result=$class_model->where("id=$class_id")->field("id,school_id")->find();    
        if(empty($class_result)){
           $data['status']=1004;
           echo json_encode($data);die; 
        }
        $isVip=0; 
        $teach_info_map['id'] = $class_result['school_id'];

        $school_info = M('dict_schoollist')->where( $teach_info_map )->find();

        if ($school_info['user_auth'] == 3 && time() >= $school_info['auth_start_time'] && time() < $school_info['auth_end_time'] ) {
            $isVip = 1;
        } else {
            $isVip = 2;
        }
        
        $class_contact['class_id']=$class_id;
        //$vip_config=C('VIP_CONFIG.WEB_REGISTER_GIVE_VIP_STATUS');
        $vip_config=3;
        
        for($i=1;$i<$length;$i++){
            $notice_tag=0;
            $data['student_name']= $this->encode_string($is_utf8,$data[$i][0]); 
            $data['sex']=$this->encode_string($is_utf8,$data[$i][1]);  
            if($data['sex']!='男' && $data['sex']!='女'){
                $notice_message='性别填写不正确';
                $notice_tag=1;
            }
            $data['birth_date']=strtotime($this->encode_string($is_utf8,$data[$i][2]));
            $data_time= $this->encode_string($is_utf8,$data[$i][2]);
            $parent_data['parent_name']= $this->encode_string($is_utf8,$data[$i][3]);
            $parent_data['email']=   $this->encode_string($is_utf8,$data[$i][4]);
            $parent_data['telephone']=    $this->encode_string($is_utf8,$data[$i][5]);
            $parent_where['telephone']=$parent_data['telephone'];
             
            if(!preg_match("/^1[34578]{1}\d{9}$/",$parent_data['telephone'])){     
                $notice_message='手机号格式不正确';
                $notice_tag=1;
            } 
            $parent_result=$parent_model->where($parent_where)->field('id,parent_name')->find();    
            if(empty($parent_result)){ 
                $notice_message='家长信息不存在';
                $notice_tag=1; 
            }
            
            //判断该学生姓名是否存在
            $parent_tel=$parent_data['telephone'];
            $student_name=$data['student_name'];
            $student_temp_result=$model->where("student_name="."'$student_name' and parent_tel="."'$parent_tel'")->field('id')->find(); 
            if(!empty($student_temp_result)){ 
                $notice_message='该家长下已有同名的学生';
                $notice_tag=1; 
            }
            
            //判断这个姓名是否存在
            $student_where['student_name']=$data['student_name'];
            $student_where['parent_tel']=$parent_data['telephone']; 
            
            $data['parent_id']=$parent_result['id'];
            $data['parent_name']=$parent_result['parent_name'];
            $data['parent_tel']=$parent_data['telephone'];
            $data['password']=sha1('123456'); 
            $data['create_at']=time();
            //$data['school_id']=session('admin.school_id');/////////////
            $data['school_id']=$class_result['school_id'];
            
            
            if($notice_tag==0){
                $student_result=$model->where($student_where)->field("id")->find(); 
                $model->startTrans();
                if(empty($student_result)){
                    if(!($insert_id=$model->add($data))){
                        $model->rollback();
                        $notice_message='学生信息保存失败';
                        $notice_tag=1;  
                    } 
                    $class_contact['student_id']=$insert_id;
                    $class_contact['create_at']=time();
                    $class_contact['update_at']=time();  
                    $class_contact['status']=2;

                    if(!$class_student->add($class_contact)){
                        $model->rollback();
                        $notice_message='学生添加班级失败';
                        $notice_tag=1; 
                    }
                    //班级学生数量加1
                    if(!$class_model->where("id="."'$class_id'")->setInc('student_count')){
                        $model->rollback();
                        $notice_message='学生添加班级失败';
                        $notice_tag=1; 
                    } 
                    if($notice_tag==0){ 
                        $viplog_info = array(
                            'school_id' =>$class_result['school_id'],
                            'school_admin_id' =>session('admin.id'),
                            'prent_phone' =>$parent_data['telephone'],
                            //'stu_phone' =>$student_data['parent_tel'],
                            'time' =>time(),
                            'auth_type' =>$school_info['timetype'],
                            'teacher_phone' =>$school_info['obligation_tel'],
                        ); 
                    
                        if($vip_config && $vip_config<=3){
                            $result=give_new_vip_operation(3, $vip_config,$insert_id,$data['school_id']);
                            if($result['status']=='failed'){
                                $parent_demol->rollback();
                                $notice_message='用户权限修改失败';
                                $notice_tag=1; 
                            }else{
                                if($vip_config==1){
                                    M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log
                                }elseif($vip_config==3){
                                    M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log
                                }
                            }
                        }
                        /*if($vip_config && $vip_config<=3){
                            $vip_student['user_id'] = $insert_id;
                            $vip_student['role_id'] = 3;
                            $vip_student['auth_id'] = 4;
                            $vip_student['auth_start_time'] = time();
                            $vip_student['auth_end_time'] = time()+3600*24*30*3;
                            $vip_student['timetype'] = 1; 
                            $student_where = array(
                                'user_id' => $insert_id,
                                'role_id' => 3  

                            );
                            if($vip_config==1){
                                //赠送90天vip
                                $vipdata['auth_id']=4; 
                                M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log

                            }elseif($vip_config==2){
                                //普通权限
                                $vipdata['auth_id']=2; 
                                $vipdata['auth_start_time']=0;
                                $vipdata['auth_end_time']=0;
                                $vipdata['timetype']=0;

                            }elseif($vip_config==3){
                                if ($isVip == 1) {
                                    $vipdata['timetype']=$temp_school['timetype'];
                                    $vipdata['auth_id']=3;  
                                    $vipdata['auth_end_time']=$temp_school['auth_end_time'];
                                    M('vip_and_auth_import')->add( $viplog_info );//记录导入的成员log

                                }else{
                                    $vipdata['auth_id']=2; 
                                    $vipdata['auth_start_time']=0;
                                    $vipdata['auth_end_time']=0;
                                    $vipdata['timetype']=0;

                                }
                            }

                            $info_student = M('account_user_and_auth')->where($student_where )->find(); 
                            if(empty($info_student)) { 
                                $student_id = M('account_user_and_auth')->add($vip_student); 
                            }
                        }*/
                        
                        $model->commit();    
                    }
                }else{
                    //学生已经存在,现在为跳过该行
                    $notice_message='学生已经存在';
                    $notice_tag=1; 
                }
            }
            
            if($notice_tag==1){ 
                $notice_temp_arr=array();
                $notice_temp_arr[]=$data['student_name'];
                $notice_temp_arr[]=$data['sex'];
                $notice_temp_arr[]=$data_time;
                $notice_temp_arr[]=$parent_data['parent_name'];
                $notice_temp_arr[]=$parent_data['email'];   
                $notice_temp_arr[]=$data['parent_tel']; 
                $notice_temp_arr[]=$notice_message;
                
                $notice_array[]=$notice_temp_arr;
                continue;
            }else{
                $success_number++;
                continue;
            }
        }
        $return_array=array();
        if(!empty($notice_array)){
            $return_array['status']=1005;
        }else{
            $return_array['status']=1006;
        } 
        $return_array['all_number']=($length-1);
        $return_array['success_number']=$success_number;
        $return_array['notice_data']=$notice_array;
        echo json_encode($return_array);
         
    }
    
    //某班级学生信息列表
    function classStudentMgmt(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        $this->assign('module', '班级管理');
        $this->assign('nav', '班级学生');
        $this->assign('subnav', '列表');
        
        $Model = M('biz_class');
        $class_id=  intval(I('id'));
        if(!$class_id){
            $this->error('参数错误');
        }  
        if(session('admin.role') == 3){
            $where['auth_student.school_id']=session('admin.school_id'); 
        } 
        
        
        $Model=M('auth_student'); 
        if($_GET['keyword']){  
            $keyword=I('keyword'); 
            $this->assign('condition_str', '&keyword='.$keyword);
            
            $where['_string'] = "(auth_student.student_name like '%$keyword%') OR (auth_parent.parent_name like '%$keyword%')";
            $this->assign('keyword', $keyword);
        } 
        $where['biz_class_student.class_id']=$class_id; 
        $where['biz_class_student.status']=2;
        
        $count = $Model->where($where)
            ->join('auth_parent on auth_parent.id=auth_student.parent_id','left')
            ->join('biz_class_student on biz_class_student.student_id=auth_student.id')
            ->field('auth_student.id')  
            ->select();            
        $count=count($count);
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));
        $show = $Page->show();

        $result = $Model->where($where)
            ->join('auth_parent on auth_parent.id=auth_student.parent_id','left')
            ->join('biz_class_student on biz_class_student.student_id=auth_student.id')
            ->field('auth_student.id,auth_student.student_name,auth_student.birth_date,auth_student.sex'
                    . ',auth_parent.parent_name,auth_parent.email,auth_parent.telephone') 
            ->order('auth_student.id desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)            
            ->select();                  
                    
        $this->assign('list', $result);
        $this->assign('page', $show);
        $this->assign('class_id', $class_id);
        
        $this->display();
    }
    
    //根据家长手机号获得家长信息
    public function getParentInfo(){
        if (!session('?admin')) redirect(U('Index/index'));
        
        $tel=I('tel');
        if(!$tel){
            $data['status']='failed';
            echo json_encode($data);die;
        }else{
            $Model = M('auth_parent');
            $result=$Model->where("telephone like "."'$tel%'")->field('id,parent_name,email,telephone')->limit(10)->select();
            $data['status']='success';
            $data['data']=$result;
            echo json_encode($data);die;
        }  
    }
    
    //根据老师手机号获得老师信息
    public function getTeacherInfo(){
        if (!session('?admin')) redirect(U('Index/index'));
        $tel=I('tel');
        if(session('admin.role') == 3){
            $school_id=session('admin.school_id');
            $where_sql="school_id=".$school_id." and telephone like "."'$tel%'";
        }else{
            $where_sql="telephone like "."'$tel%'";
        } 
        
        if(!$tel){
            $data['status']='failed';
            echo json_encode($data);die;
        }else{
            $Model = M('auth_teacher');
            $result=$Model->where($where_sql)->field('id,name,email,telephone')->order("id desc")->limit(10)->select();   
            
            $data['status']='success';
            $data['data']=$result;
            echo json_encode($data);die;
        } 
    }
    
    //根据教师手机号获得教师教的年级和学科等 按照年级分组
    public function getTeachGradeInfo(){
        if (!session('?admin')) redirect(U('Index/index'));
        $tel=I('tel');
        if(session('admin.role') == 3){
            $school_id=session('admin.school_id');
            $where_sql="school_id=".$school_id." and telephone like "."'$tel%'";
        }else{
            $where_sql="telephone like "."'$tel%'";
        } 
        
        if(!$tel){
            $data['status']='failed';
            echo json_encode($data);die;
        }else{
            $data['status']='success';
            $Model = M('auth_teacher'); 
            $result=$Model->where($where_sql)->field('id,name,email,telephone')->order("id desc")->find();
            if(empty($result)){
                $data['data']=array();
                echo json_encode($data);die;
            }else{
                $teacher_id=$result['id'];
                $second_model=M('auth_teacher_second');
                $second_result=$second_model->where("teacher_id=".$teacher_id)
                                            ->join("dict_grade on dict_grade.id=auth_teacher_second.grade_id")
                                            ->field('auth_teacher_second.teacher_id,auth_teacher_second.grade_id,auth_teacher_second.course_id,dict_grade.grade')
                                            ->group('auth_teacher_second.grade_id')
                                            ->select();
                $data['data']=$second_result;
                echo json_encode($data);
            }  
            
        }
    }

    //查询修改记录
    public function lookAuthNotes() {

        $this->assign('module', '用户管理');
        $this->assign('nav', '学校管理');
        $this->assign('subnav', 'VIP修改记录');

        $map['user_id'] = $_GET['id'];

        $count = M('account_auth_notes')->where( $map )->count();

        $Page = new \Think\Page($count, 10);
        foreach ($where as $key => $val) {
            //$Page->parameter[$key] = urlencode($val);
            $Page->parameter[$key] = $val;
        }
        //dump($check);
        $show = $Page->show();


        $list = M('account_auth_notes')->where( $map )->order( 'id desc' )->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
        foreach ($list as $key => $value) {
            $adminmap['id'] = $value['root_id'];
            $admin_name = M('auth_admin')->where( $adminmap )->field('name')->find();
            $list[$key]['admin_name'] = $admin_name['name'];
        }


        $this->assign('list',$list);
        $this->assign('page', $show);
        $this->display();
    }

    public function createTextBook()
        {
         if($_POST)
          {
             $isEdit = isset($_GET['id']);
             //look for same grade course schoolterm book
               $check = array(
                  'course_id' => $_POST['course_id'],
                  'grade_id' => $_POST['grade_id'],
                  'school_term' => $_POST['school_term'],
                  'name' => $_POST['name']
                );
                $addData =  $check;
                if($isEdit)
                 $check['_string'] = 'id <>'. $_GET['id'];
                else {
                   $addData['create_at'] = time();
                   $addData['flag'] = 2;
                }
                $addData['update_at'] = time();
                $addData['author'] = $_POST['author'];
                $addData['isbn'] = $_POST['isbn'];
                $addData['print'] = $_POST['print'];
                $addData['has_ebook'] = $_POST['has_ebook']=='on' ? 1:0 ;
                $addData['server_path'] = $_POST['server_path'];
                $addData['edition'] = $_POST['edition'];
                $addData['sort_order'] = $_POST['sort_order'];
                $Model = M('biz_textbook');
                 $res = $Model->where($check)->find();
                if($res)
                {
                  $this->ajaxReturn(array("code" => -1 ,'msg' =>'对应年级学科分册名称的课本已存在'));
                }


              if($isEdit)
                {
                 $addData['update_at'] = time();
                 $bookId = $_GET['id'];
                 $Model->where('id='.$bookId)->save($addData);
                }
                else
                {
                $addData['create_at'] = time();
                $bookId = $Model->add($addData);
                }
               $this->ajaxReturn(array("code" => 0 ,'bookId' => $bookId,'msg' =>'添加/修改成功'));
          }
          else
          {
          $Model = M('dict_course');
          $courses = $Model->order('sort_order asc')->select();
          $this->assign('courses', $courses);

          $Model = M('dict_grade');
          $grades = $Model->select();
          $this->assign('grades', $grades);
          $this->assign('nav', '电子课本管理');

          if($_GET['id']) //editing book
          {
           $Model = M('biz_textbook');
           $data =  $Model->where('id='.$_GET['id'])->find();
           $this->assign('data',$data);
           $this->assign('subnav', '修改课本');
          }
          else
          {
           $this->assign('subnav', '创建课本');
          }
          $this->display();
          }
        }

    public function deleteTextBook()
    {
       if($_POST)
       {
        $Model = M('biz_textbook');
        $data =  $Model->where('id='.$_POST['id'])->delete();
        $this->ajaxReturn(array('code' => 0,'msg' => '删除成功'));
       }
    }
    public function textBookShelfControl()
    {
      if($_POST)
      {
        $Model = M('biz_textbook');
        $id = getParameter('id','int');
        $data['flag'] = getParameter('flag','int');
        $data =  $Model->where('id='.$id)->save($data);
        $bookInfo = D('Biz_textbook')->getTextBookDetails($id);
          if($data['flag'] == 1) {
              $studentIdList = D('Auth_student')->getStudentIdsListByGrade($bookInfo['grade_id']);
              $courseResult = D('Dict_course')->getCourseInfo($bookInfo['course_id']);
              $gradeResult = D('Dict_grade')->getGradeInfo($bookInfo['grade_id']);
              $textBook = ($bookInfo['school_term'] == 1) ? '上册' : '下册';
              $parameters = array('msg' => array(
                  $gradeResult['grade'],
                  $courseResult['course_name'],
                  $textBook
              ),
                  'url' => array('type' => 1, 'data' => array(C('APIINTERFACE_VERSION'), $id))
              );
              A('Home/Message')->addPushUserMessage('NEW_ETEXTBOOK', 3, implode(',', $studentIdList), $parameters);

              $parentIdList = D('Auth_student')->getParentList($studentIdList);
              A('Home/Message')->addPushUserMessage('NEW_ETEXTBOOK_CHILD', 4, implode(',', $parentIdList), $parameters);
          }
        $this->ajaxReturn(array('code' => 0,'msg' => '上/下架成功'));
      }
    }
    //分页StartIndex重计算
    private function startIndexFilter($allCount,$pageIndex,$pageSize)
    {
       return $allCount < ($pageIndex -1) * $pageSize ? (max(0,round(($allCount/$pageSize))-1)) * $pageSize :($pageIndex -1) * $pageSize;
    }
    private function getDaysArrayString($startTime,$endTime)
    {
      $returnArray = array();
      for($currentTime=$startTime;$currentTime <= $endTime ;$currentTime += 86400)
      {
          $returnArray[] = $currentTime;
      }
      return '('.implode(',',$returnArray).')';
    }
    public function pageVisited()
    {
    if(1)
    {
        //Paras Config
        $defaultPageSize = 10;
        $maxPieDataSize = 10;

        $queryParas =  $_GET;
        $currentTime = time();
        $constQueryParasInfo['currentDate'] = date('Y-m-d',$currentTime);
        $constQueryParasInfo['currentDateM1'] = date('Y-m-d',$currentTime-24*3600);
        $constQueryParasInfo['currentDateM7'] = date('Y-m-d',$currentTime-7*24*3600);
        $constQueryParasInfo['currentDateM30'] = date('Y-m-d',$currentTime-30*24*3600);
        $constQueryParasInfo['currentDateM60'] = date('Y-m-d',$currentTime-60*24*3600);

        //sourcetypes viewcounts searchkey
        $pageSize = empty($_GET['pagesize']) ? $defaultPageSize : $_GET['pagesize'];
        $queryParas['pagesize'] = $pageSize;

        $pageIndex = empty($_GET['p']) ? 1 : (int)$_GET['p'];
        $startIndex = ($pageIndex - 1) * $pageSize;

        if(empty($queryParas['starttime']))
            $queryParas['starttime'] = $constQueryParasInfo['currentDate'];
        if(empty($queryParas['endtime']))
            $queryParas['endtime'] = $constQueryParasInfo['currentDate'];
        if(strtotime($queryParas['starttime']) > strtotime($queryParas['endtime']))
            $queryParas['starttime'] = $queryParas['endtime'];

        if(isset($queryParas['role']))
        {
            if($queryParas['role'] == -1)
            unset($queryParas['role']);
            else
            $queryParas['_string'] = 'user_id <> 0';
        }

        $sourceTableSql = $this->getAccessRecordSql('user_access',$queryParas);
        $sourceTableSql4Statistic = $this->getAccessRecordSql('user_access',$queryParas,'browseFilter');
        $browseSelector = empty($queryParas['viewcounts']) ? 'WHERE 1=1 ': 'WHERE browsecount > '.$queryParas['viewcounts'];
        $urlFilter =  empty($queryParas['searchkey']) ? ' ': ' AND url like \'%' . $queryParas['searchkey'] . '%\' ';
        $daysArray = $this->getDaysArrayString(strtotime($queryParas['starttime']),strtotime($queryParas['endtime']));
        $sql = "
      SELECT * FROM
      (SELECT
          extract_table.access_url as url,
          COUNT(DISTINCT user_id, role) AS visitors,
          COUNT(DISTINCT ip_address) AS ips,
          COUNT(extract_table.access_url) AS browsecount,
          COUNT(1) / COUNT(DISTINCT user_id, role) AS avgbrowse,
          '--' AS holdtime,
          (CASE WHEN urlnum is null THEN 0 else urlnum end) / COUNT(DISTINCT user_id, role) AS newvisitorrate,
          '--' AS jumprate,
          '--' AS exitrate
      FROM
          (SELECT
              access_url, role, user_id, ip_address
          FROM
              $sourceTableSql
          WHERE
              access_date in $daysArray ) extract_table
               LEFT JOIN
          (SELECT
              COUNT(base_table.access_url) AS urlnum,
                  base_table.access_url AS url
          FROM
              (SELECT
              access_url,user_id,role
          FROM
              $sourceTableSql
          GROUP BY access_url, user_id, role ) base_table
          INNER JOIN (SELECT
              user_id,role
          FROM
              ((SELECT
              MIN(access_time) AS m_accesstime, user_id, role
          FROM
              $sourceTableSql
          GROUP BY user_id , role ) min_access)
          WHERE
              m_accesstime in $daysArray) min_acc ON base_table.user_id = min_acc.user_id AND base_table.role = min_acc.role
          GROUP BY base_table.access_url) out_table ON out_table.url = extract_table.access_url
      GROUP BY extract_table.access_url ) a $browseSelector $urlFilter ORDER BY browsecount desc ";

        $allDataSql = "
      SELECT
          COUNT(DISTINCT a.user_id, a.role) AS visitors,
          COUNT(a.id) AS browse
      FROM
          (SELECT * FROM user_access WHERE access_date in $daysArray) a ";

        $allDataSqlSpec = "
       SELECT
         COUNT(DISTINCT (CASE
                 WHEN
                     user_access_ext.access_date in $daysArray
                 THEN
                     CONCAT(user_access_ext.user_id, 'a', user_access_ext.role)
                 ELSE NULL
             END)) AS currentVisitors,
         SUM(CASE
             WHEN
                 user_access_ext.access_date in $daysArray
             THEN
                 1
             ELSE 0
         END) AS currentBrowse
     FROM
        $sourceTableSql4Statistic
      ";
        $Dao = M();
        $resultAllCount = $Dao->query('SELECT COUNT(1) as num FROM ('.$sql.') a');
        $startIndex = $this->startIndexFilter($resultAllCount[0]['num'],$pageIndex,$pageSize);
        $resultDetails = $Dao->query($sql." LIMIT $startIndex,$pageSize");
        $resultAllStatistics = $Dao->query($allDataSql);
        $resultAllSpecStatistics = $Dao->query($allDataSqlSpec);
        if(empty($resultAllSpecStatistics[0]['currentbrowse'] ))
            $resultAllSpecStatistics[0]['currentbrowse'] = 0;
        $resultAllStatistics = array_merge($resultAllStatistics[0],$resultAllSpecStatistics[0]);

        $pieDataSize = min($maxPieDataSize,count($resultDetails));
        for ($i = 0; $i < $pieDataSize; $i++)
        {
            $pieData[$i]['name'] = $resultDetails[$i]['url'];
            $pieData[$i]['value'] = $resultDetails[$i]['browsecount'];
        }
        //calc sum data
        for ($i = 0; $i < $pageSize; $i++)
        {
            foreach($resultDetails[$i] as $key => $value)
            {
                if($value != '--')
                    $sumdata[$key] += $value;
            }
        }

        //avgbrowse newvisitorrate avg recalc
        $sumdata['avgbrowse'] = $sumdata['browsecount'] / $sumdata['visitors'];
        $sumdata['newvisitorrate'] = $sumdata['newvisitorrate'] / count($resultDetails);
        $Page = new \Think\Page($resultAllCount[0]['num'], $queryParas['pagesize']);
        $Page->parameter= $queryParas;
        $show = $Page->show();

        $this->assign('pieData',json_encode($pieData));
        $this->assign('sumData',$sumdata);
        $this->assign('data',$resultDetails);
        $this->assign('dataAll',$resultAllStatistics);
        $this->assign('queryParas',$queryParas);
        $this->assign('page',$show);
        $this->assign('constQueryParasInfo',$constQueryParasInfo);
        $this->assign('nav','用户行为分析');
        $this->assign('subnav','受访页面');
        //var_dump($this->array_keyvalue_urlmerge($queryParas));exit;
    }
    $this->display();
}
    private function array_keyvalue_urlmerge($arr)
    {
        foreach ($arr as $key=>$val)
        {
            $querystr[] = $key.'='.$val;
        }
        return join('&',$querystr);
    }
    private function getAccessRecordSql($tableName,$paras,$filter='')
    {
        $where[] = ' 1=1 ';
		$daysArray = $this->getDaysArrayString(strtotime($paras['starttime']),strtotime($paras['endtime']));
        if(!empty($paras['sourcetypes']))
        {
            switch($paras['sourcetypes'])
            {
                case 'web' : $where[] = " (user_agent like '%Windows%' or user_agent like '%Macintosh%') ";
                    break;
                case 'ios' : $where[] = " (user_agent like '%iPhone%') ";
                    break;
                case 'android': $where[] = " (user_agent like '%Android%') ";
                    break;
            }
        }
        if(isset($paras['role'])) {
            if($paras['role'] != 3) //非游客
                $where[] = "role=" . $paras['role'] . ' AND user_id<> 0';
            else
                $where[] = "role = 0 AND user_id = 0 ";
        }

        $where = join(' AND ',$where);
        $baseTableSql = " (SELECT * FROM user_access WHERE $where) ";

        if($filter=='browseFilter' && !empty($paras['viewcounts']))
        {
            $viewCounts = $paras['viewcounts'];
            $extendSql = "(SELECT user_access.* FROM user_access JOIN (SELECT access_url,count(1) as num FROM user_access WHERE $where AND access_date in $daysArray GROUP BY access_url) b ON b.access_url=user_access.access_url WHERE num > $viewCounts)";
        }
        else
        {
            $extendSql = $baseTableSql;
        }

        return $extendSql .' user_access_ext';

    }
    
    //敏感词列表页面
    function sensitive_words(){ 
        if (!session('?admin')) redirect(U('Index/index'));
        
        $this->assign('module', '敏感词');
        $this->assign('nav', '敏感词');
        $this->assign('subnav', '敏感词列表');
                
        $filter['keyword'] = $_REQUEST['keyword'];
        if (!empty($filter['keyword'])) $check['sensitive_words'] = array('like', '%' . $filter['keyword'] . '%');
                
        $model=M('sensitive_words'); 
        $count=$model->where($check)->field('id,sensitive_words')->order('id desc')->count('id');        
        $Page = new \Think\Page($count, C('PAGE_SIZE_FRONT'));  
               
        $now_page=isset($Page->parameter['p'])?$Page->parameter['p']:1;    
        $count_page=ceil($count/C('PAGE_SIZE_FRONT')); 
        $mo_rows=$count%C('PAGE_SIZE_FRONT');   
        $page_i=($count_page-$now_page)==0?$page_i=0:($count_page-$now_page);   
        $total_rows=$page_i*C('PAGE_SIZE_FRONT')+((C('PAGE_SIZE_FRONT')-(C('PAGE_SIZE_FRONT')-$mo_rows)));    
        
        $show = $Page->show();
        
        $result=$model->where($check)->limit($Page->firstRow . ',' . $Page->listRows)->field('id,sensitive_words,create_at')->order('id desc')->select(); 
                
        
        $this->assign('keyword', $filter['keyword']);
        $this->assign('list', $result);
        $this->assign('page', $show); 
        $this->assign('data_page', $total_rows);
        $this->display();
    }
    
    //添加敏感词
    function add_sensitive_words(){
        if (!session('?admin')) redirect(U('Index/index'));
        $model=M('sensitive_words');
        $data['sensitive_words']=$_POST['sensitive_words'];
        $data['create_at']=time();
                
        if(empty($data['sensitive_words'])){
            $this->ajaxReturn('failed');
        }
        
        $result=$model->field('id')->where("sensitive_words='".$data['sensitive_words']."'")->find();      
        if(!empty($result)){
            $this->ajaxReturn('exists');
        }
        
        
        if($model->add($data)){     
            $this->ajaxReturn('success');
        }else{
            $this->ajaxReturn('failed');
        }
    }
    
    //删除敏感词
    function delete_sensitive_words(){
        if (!session('?admin')) redirect(U('Index/index'));
        $model=M('sensitive_words');
        $id=intval($_GET['id']); 
        if($model->where("id=$id")->delete()){
            $this->ajaxReturn('success');
        }else{
            $this->ajaxReturn('failed');
        }
        
    }
    function createEditGrade()
    {
        $model = M('dict_grade');
        $id = getParameter('id','int',false);
        if($_POST)
        {
            $data['grade'] = getParameter('grade','str');
            $data['code'] = getParameter('code','int');
            $data['chinese_code'] = getParameter('chinese_code','str');
            $data['short_name'] = getParameter('short_name','str');
            if(empty($id)) //add
            {
             if($model->where('code='.$data['code'])->find())
               $this->error('年级编号已经存在');
                 M('dict_grade')->add($data);
                redirect(U('Admin/gradeMgmt'));
            }
            else
            {
               $model->where('id='.$id)->save($data);
                redirect(U('Admin/gradeMgmt'));
            }
        }
        else
        {
            if(!empty($id)) {
                $data = $model->where('id=' . $id)->find();
                $this->assign('data', $data);
                $this->assign('subnav', '修改年级');
            }
            else
                $this->assign('subnav', '添加年级');
            $this->assign('module', '年级管理');
            $this->assign('nav', '年级管理');

            $this->display();
        }

    }
    function falseDeleteGrade()
    {
        $model = M('dict_grade');
        $id = getParameter('id','int');
        $data['is_delete'] = 1;
        $model->where('id='.$id)->save($data);
        $this->showMessage(200,'删除成功',array());
    }
    public function adminLogin()
    {
        if ($_GET) {

            $check['name'] = getParameter('userName','str');
            $check['password'] = (getParameter('password','str'));
            
            $activity_jyyuser_arr=C('activity_jyyuser');
            if(($check['name'] == $activity_jyyuser_arr['user']) && ($check['password'] == $activity_jyyuser_arr['password']))
                $this->showMessage(200,'success',array());
            else {
               $this->showMessage(500,'error',array());
            }
        }
    }
}