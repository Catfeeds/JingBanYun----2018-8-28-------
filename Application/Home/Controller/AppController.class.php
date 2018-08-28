<?php
namespace Home\Controller;
use Common\Common\JSSDK;
use Think\Controller;
use Common\Common\CSV;

define('PUTAWAY',1);
class AppController extends PublicController
{
    function __construct(){
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));        
        header("Content-type: text/html; charset=utf-8");
        $this->c_a = CONTROLLER_NAME."_".ACTION_NAME;
        //http://jtypt.com/Public/pdfjs/viewer/viewer.html?f=http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/material/2016-11-10/20161110095028FVWTDh2fqK.pdf
        
        //
    }

    
    public function jbOverview()
    {
        $Model = M('biz_bj_overview');
        $content = $Model->where('status=2')->select();
        $this->assign('data', $content[0]);

        $this->display();
    }

    //专家资讯信息详情
    public function expertInformationDetails()
    {
        $id = $_GET['id'];
        $Model = M('social_expert_information');
        $check['id'] = $id;
        $result = $Model->where($check)->field("id,title,short_content,content,create_at,update_at,'北京出版集团' publisher,"
                . "zan_count,browse_count,status,publisher_id")->find();

        $this->assign('data', $result);

        $this->display();
    } 
 

    //教师资源文件上传
    public function upload_file(){ 
        $upload = new \Oss\Ossupload();// 实例化上传类
        $result=$upload->upload(3,$_FILES,3,0); //1 pic 2//
        echo json_encode($result);
    }
        
    //精通活动详情
    /*public function activityDetails()
    {
        $id = $_GET['id'];
        $SocialActivity = M('social_activity');
        $check['id'] = $id;
        $result = $SocialActivity->where($check)->field("id,title,category,short_content,content,create_at,update_at,'北京出版集团' publisher,activity_result,"
                . "stakeholder,register_numbers,register_info,status,zan_count,favor_count,browse_count,publisher_id,class_id,is_upload")->find();   

        $this->assign('data', $result);

        $SocialActivity->where("id=$id")->setInc('browse_count', 1);


        //判断是否已经报名
        $SocialActivityRegister = M('social_activity_register');
        $checkHasRegistered['user_id'] = $_GET['user_id'];
        $checkHasRegistered['activity_id'] = $id;
        $existed = $SocialActivityRegister->where($checkHasRegistered)->find();
        $registered = empty($existed) ? 'no' : 'yes';
        $this->assign('registered', $registered);
        $this->assign('register_info', $existed['register_info']);

        //判断我是否赞过和收藏过
        $ZanModel = M('social_activity_zan');
        $zanData['social_activity_id'] = $id;
        $zanData['user_id'] = $checkHasRegistered['user_id'];
        $zanData['user_type'] = $_GET['role']  - 1 ;
        $existedZan = $ZanModel->where($zanData)->find();
        $existedZan = empty($existedZan) ? 'no' : 'yes';

        $this->assign('existedZan', $existedZan);

        $FavorModel = M('social_activity_favor');
        $favorData['social_activity_id'] = $id;
        $favorData['user_id'] = $checkHasRegistered['user_id'];
        $favorData['user_type'] =  $_GET['role']  - 1 ;
        $existedFavor = $FavorModel->where($favorData)->find();
        $existedFavor = empty($existedFavor) ? 'no' : 'yes';
        $this->assign('existedFavor', $existedFavor);


        $this->display();
    }*/

    public function xiazaidemo(){
        $id=$_GET['id'];
        $token=$_GET['token'];
        if ($token != 'Jingbanyun426!') {
            exit();
        }

        $contact_data=$this->getResourceContactFiles($id);
        $vidstr='';
        foreach ($contact_data as $k=>$v) {
            if (!empty($v['vid_fullpath'])) {
                $vidstr.= '<a href="'.U('xiazai?url='.$v['vid_fullpath']).'">'.$v['vid_fullpath'].'</a>'.'<br/>';
            }

        }
        echo $vidstr;

    }

    public function xiazai() {
        $url = $_GET['url'];
        if (!empty($url)) {
            $csv=new CSV();
            $csv->downloadMedia($url);

        }
    }


    public function getResourceContactFiles($resource_id){
        $where['biz_bj_resource_id'] = $resource_id;
        $res = M('biz_bj_resource_contact')->where($where)->select();
        return $res;

    }
    
    
    public function activityDetails(){
        $id = getParameter('id', 'int',false);
        $userId = getParameter('user_id', 'int',false);
        $role = getParameter('role', 'int',false) ; 
        if ($role >= 2 && $role <= 5 && $id) {   
                $this->activityModel=D('Social_activity');     
                $result = $this->activityModel->getActivityDetails($id);             
                
                if (!empty($result)) {
                    $register_people_number_status=1;
                    if($result['apply_people_number']!=0){
                        if($result['apply_people_number']<=$result['register_numbers']){
                            $register_people_number_status=2;
                        }
                    }
                    if($result['applystart']<=time() && $result['applyend']>=time()){
                        $activity_status=1;
                    }else{
                        if($result['applystart']>time()){
                            $activity_status=2;
                        }else{
                            $activity_status=3;
                        }
                        
                    }   
                    $activity_course_info = $this->activityModel->getActivityCourse($id);
                    //关键附件
                    $activity_contact_file = $this->activityModel->getActivityFileInfo($id);

                    $this->activityModel->setBrowseCountPlusOne($id);
                    //报名信息
                    $regInfo = $this->activityModel->getRegistered($id, $userId);       

                    $zanData = $this->activityModel->getIsZan($id, $userId, $role);
                    $favorData = $this->activityModel->getIsFavor($id, $userId, $role);

                    $this->assign('activity_course_info', $activity_course_info);       
                    $this->assign('registered', $regInfo['reged']);
                    $this->assign('register_info', $regInfo['info']);

                    $this->assign('existedZan', $zanData);
                    $this->assign('existedFavor', $favorData);
 
                    $this->assign('data', $result);
                    $this->assign('activity_contact_file', $activity_contact_file);
                    $this->assign('user_id', $userId);
                    $this->assign('role', $role);
                    $this->assign('activity_status',$activity_status);
                    $this->assign('register_people_number_status',$register_people_number_status);
                }
            } 
        $this->display();
    }
         
         

    public function registerActivity()
    {
          
        if ($_POST) {
            
            $checkAuth['id'] = $_POST['teacher_id'];
            $TeacherModel = M('auth_teacher');
            $teacher = $TeacherModel->where($checkAuth)->find();

            $data['user_id'] = $_POST['teacher_id'];
            $data['register_info'] = $_POST['register_info'];
            $data['user_type'] = 1;
            $data['register_at'] = time();
            $data['user_name'] = $teacher['name'];
            $data['activity_id'] = $_POST['id'];       
         
            $SocialActivityRegister = M('social_activity_register');
            //判断是否已经报名
            $checkHasRegistered['user_id'] = $_POST['teacher_id'];
            $checkHasRegistered['activity_id'] = $_POST['id'];
            $existed = $SocialActivityRegister->where($checkHasRegistered)->find();
            if (empty($existed)) { //如果没有报名，则进行报名
                $SocialActivityRegister->add($data);
                //更新报名总数
                $SocialActivity = M('social_activity');
                $SocialActivity->where("id=" . $_POST['id'])->setInc("register_numbers", 1);
            } 
            if(isset($_POST['unique'])){
                $host=$_SERVER['HTTP_HOST'];
                $url='http://'.$host."/ApiInterface/Version1_1/Activity/activityDetails?id=".$data['activity_id'].'&user_id='.$data['user_id'].'&role=2';
                header('Location:'.$url); 
            }else{
                $this->redirect("App/activityDetails?id=" . $_POST['id'] . "&tid=" . $teacher['id'] . "&token=" . $teacher['access_token']);
            }
        }
    }

    public function getSubResourceHTMLList($info)
    {
      $oss_path = C('oss_path');
      $infoid = $info['id'];
      $result = M('biz_bj_resource_contact')->where("biz_bj_resource_id=$infoid")->select();
      $htmlList = "";
      switch($info['type'])
         {
         case 'HTML': $htmlList = $info['content'];
                      break;
         case 'ppt':
         case 'word':$i=1;
                     foreach($result as &$val)
                      {
                         $pdf_arr=explode('.',basename($val['resource_path']));
                         $pdf_path=$pdf_arr[0];
                         $htmlList = $htmlList . '<a href="' .$oss_path .'teacher/'.$info['id'] .'/'.$val['id'] .'/'. $pdf_path .'.pdf">' . '查看第' .$i . '个资源</a></br>' ;
                         $i++;
                      }
                     break;
         case 'pdf':
                        $i=1;
                        foreach($result as &$val)
                        {
                         $htmlList = $htmlList . '<a href="' .$oss_path .$val['resource_path'] .'">' . '查看第' .$i . '个资源</a></br>' ;
                         $i++;
                        }
                        break;
         case 'image':
                        foreach($result as &$val)
                         {
                            $htmlList = $htmlList . '<img src="' .$oss_path .$val['resource_path'] .'"></img></br>' ;
                         }
                         break;
         case 'video':  $i=1;
                        foreach($result as &$val)
                         {
                            $htmlList = $htmlList . '<video controls webkit-playsinline style="background:rgba(0,0,0,0.4)" width="100%" src="' .$oss_path .$val['resource_path'] .'">' . '查看第' .$i . '个资源</video></br>' ;
                            $i++;
                         }
                         break;
         case 'audio':  $i=1;
                        foreach($result as &$val)
                         {
                            $htmlList = $htmlList . '<audio src="' .$oss_path .$val['resource_path'] .'">' . '查看第' .$i . '个资源</audio></br>' ;
                            $i++;
                         }
                         break;
         }
      return $htmlList;
    }
    
    //教师资源分享的
    public function resource()
    {
        $this->resourceDetails();
    }

    //教师资源分享的
    public function resourceDetails()
    {
        
        $id = $_GET['id']; 
        
        $goback = $_GET['goback'];
        $this->assign('showGoBackbutton', $goback);    
        if(!empty( $_GET['f']))
        $from = $_GET['f'];
        $this->assign('from', $from);       

        $Model = M('biz_resource');
        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
            ->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
            ->join('dict_course on biz_resource.course_id=dict_course.id','left')
            ->join('dict_grade on biz_resource.grade_id=dict_grade.id','left')
            ->field('biz_resource.*,biz_textbook.name as textbook,biz_resource.teacher_id,auth_teacher.brief_intro as teacher_brief_intro,auth_teacher.points as points,dict_course.course_name,dict_grade.grade')
            ->where("biz_resource.id=$id")         
            ->find();       
        //判断资源是否为空
        /*if(empty($result)){
            redirect(U('Index/systemError'));
        }*/
        $result['type'] = strtolower($result['type']);            
        $this->assign('subnav', $result['name']); 
        $this->assign('data', $result);
        
        //拿到关联表的数据
        $contact_result=$Model->where("biz_resource.id=".$id)->join("biz_resource_contact on biz_resource_contact.biz_resource_id=biz_resource.id")
            ->field("biz_resource_contact.*")->select(); 
               
        $this->assign('contact_data', $contact_result);  
    
        //判断登陆者是否和发布者是一人
         if($result['teacher_id']==$_GET['user_id'] && ($_GET['role'] == 2)){
             $this->assign('operation_status',1);
         }else{                                             
             $this->assign('operation_status',2);
         } 
         
         
          //观看次数+1
          $Model->where("id=$id")->setInc('follow_count', 1);
            
         //判断我是否赞过和收藏过 
          $ZanModel = M('biz_resource_zan');
          $zanData['resource_id'] = $id;
          $zanData['user_type'] = $_GET['role'] - 2;
          $zanData['user_id'] = $_GET['user_id'];
          $existedZan = $ZanModel->where($zanData)->find();
          $existedZan = empty($existedZan) ? 'no' : 'yes';
          $this->assign('existedZan', $existedZan);
                $FavorModel = M('biz_resource_collect');
          $favorData['resource_id'] = $id;
          $favorData['user_type'] = $_GET['role'] - 2;
          $favorData['user_id'] = $_GET['user_id'];
          $existedFavor = $FavorModel->where($favorData)->find();
          $existedFavor = empty($existedFavor) ? 'no' : 'yes';
          $this->assign('existedFavor', $existedFavor); 

        //print_r($result);die();
            
        $this->display();

    }


    //教师资源分享的
    public function resourceDetailsShare()
    {
        
        $id = $_GET['id'];
        $teacher_online=1;
        $this->res_id = $id;
        $goback = $_GET['goback'];
        $this->assign('showGoBackbutton', $goback);    
        if(!empty( $_GET['f']))
        $from = $_GET['f'];
        $this->assign('from', $from);       

        $Model = M('biz_resource');
        $result = $Model
            ->join('biz_textbook on biz_resource.textbook_id=biz_textbook.id','left')
            ->join('auth_teacher on biz_resource.teacher_id=auth_teacher.id')
            ->join('dict_course on biz_resource.course_id=dict_course.id','left')
            ->join('dict_grade on biz_resource.grade_id=dict_grade.id','left')
            ->field('biz_resource.*,biz_textbook.name as textbook,biz_resource.teacher_id,auth_teacher.brief_intro as teacher_brief_intro,auth_teacher.points as points,dict_course.course_name,dict_grade.grade')
            ->where("biz_resource.id=$id")         
            ->find();                   
        //判断资源是否为空
        /*if(empty($result)){
            redirect(U('Index/systemError'));
        }*/
        $result['type'] = strtolower($result['type']);            
        $this->assign('subnav', $result['name']); 
        $this->assign('data', $result);
        
        //拿到关联表的数据
        $contact_result=$Model->where("biz_resource.id=".$id)->join("biz_resource_contact on biz_resource_contact.biz_resource_id=biz_resource.id")
            ->field("biz_resource_contact.*")->select(); 
               
        $this->assign('contact_data', $contact_result); 
        /*if(empty($contact_result)){
            redirect(U('Index/systemError'));
        }*/
        if($teacher_online==2){
            //观看次数+1
            $Model->where("id=$id")->setInc('follow_count', 1);
        }
        //$User = M("auth_teacher");
        //$User->where("id=" . $result['teacher_id'])->setInc("points", 1);// 积分加1 
    
        //判断登陆者是否和发布者是一人
         if($result['teacher_id']==session('teacher.id')){  
             $this->assign('operation_status',1);
         }else{                                             
             $this->assign('operation_status',2);
         }
        if($teacher_online==1){
            //判断我是否赞过和收藏过
            $ZanModel = M('biz_resource_zan');
            $zanData['resource_id'] = $id;
            $zanData['user_type'] = 0;
            $zanData['user_id'] = session('teacher.id');
            $existedZan = $ZanModel->where($zanData)->find();
            $existedZan = empty($existedZan) ? 'no' : 'yes'; 
            $this->assign('existedZan', $existedZan);


            $FavorModel = M('biz_resource_collect');
            $favorData['resource_id'] = $id;
            $favorData['user_type'] = 0;
            $favorData['user_id'] = session('teacher.id');
            $existedFavor = $FavorModel->where($favorData)->find();
            $existedFavor = empty($existedFavor) ? 'no' : 'yes';
            $this->assign('existedFavor', $existedFavor);   
        }

        //print_r($result);die();
        $apkinfo = file_get_contents("http://www.jingbanyun.com/index.php?m=Home&c=Download&a=version&ostype=Android");
        $apkinfo = json_decode($apkinfo,true);
        $apkurl = $apkinfo['data']['download_path'];
        $this->assign('apkurl',$apkurl);


        $this->display();

    }

    public function blackboardDetails()
    {
        $id = $_GET['id'];
        $Model = M('biz_blackboard');
        $check['biz_blackboard.id'] = $id;
        $result = $Model
            ->join("biz_class on biz_blackboard.class_id=biz_class.id")
            ->field("biz_blackboard.*,biz_class.name as class_name")
            ->where($check)->find();

        $this->assign('data', $result);

        $this->display();
    }


    public function teacherDetails()
    {
        $id = $_GET['id'];
        $Model = M('auth_teacher');
        $check['auth_teacher.id'] = $id;
        $result = $Model
            ->join("dict_school on auth_teacher.school_id=dict_school.id")
            ->join("dict_course on auth_teacher.course_id=dict_course.id")
            ->field("auth_teacher.*,dict_school.school_name,dict_course.course_name")
            ->where($check)->find();

        $this->assign('data', $result);

        $this->display();
    }


    public function studentDetails()
    {
        $id = $_GET['id'];
        $Model = M('auth_student');
        $check['id'] = $id;
        $result = $Model
            ->where($check)->find();

        $this->assign('data', $result);

        $this->display();
    }

    public function bjResourceDetails()
    {
      $this->jbResourceDetails();
    }
    
    public function jbResourceDetails()
    {
        $id = intval($_GET['id']);
        $this->jb_id = $id;
        $from = $_GET['f'];
        $this->assign('from', $from);

        $Model = M('biz_bj_resources');
        $result = $Model
            ->join('biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id','left')
            ->join('dict_course on biz_bj_resources.course_id=dict_course.id','left')
            ->join('dict_grade on biz_bj_resources.grade_id=dict_grade.id','left')
            ->field('biz_bj_resources.*,biz_textbook.name as textbook,dict_course.course_name,dict_grade.grade')
            ->where("biz_bj_resources.status=2 and biz_bj_resources.id=$id")      
            ->find();                       
        $result['content'] = $this->getSubResourceHTMLList($result);
        $this->assign('subnav', $result['name']);

        $this->assign('data', $result);
        
        $oss_path=C('oss_path');
        //拿到关联表的数据
        $contact_result=$Model->where("biz_bj_resources.status=2 and biz_bj_resources.id=".$id)->join("biz_bj_resource_contact on biz_bj_resource_contact.biz_bj_resource_id=biz_bj_resources.id")
            ->field("biz_bj_resource_contact.*")->select();       
        if(empty($contact_result))
         {
          $contact_result['resource_path'] = $result['vid_full_path'];
         }else{ 
             foreach($contact_result as $key=>$value){
                 if($contact_result[$key]['resource_path']==''){
                    $contact_result[$key]['resource_path']=$value['vid_fullpath'];  
                 }else{ 
                    $contact_result[$key]['resource_path']=$oss_path.$contact_result[$key]['resource_path'];
                 } 
             }
         }    
         
         
        $this->assign('contact_data', $contact_result); 
        
        
        /*if(empty($result)){
            redirect(U('Index/systemError'));
        }
        if(empty($contact_result)){
            redirect(U('Index/systemError'));
        }*/

        //观看次数+1
        if(session('teacher') != 'youke') {
            $Model->where("id=$id")->setInc('follow_count', 1);    
        }
        

        //判断我是否赞过和收藏过
        $ZanModel = M('biz_bj_resource_zan');
        $zanData['resource_id'] = $id;
        $zanData['role'] = $_GET['role'] - 2;
        $zanData['user_id'] = $_GET['user_id'];
        $existedZan = $ZanModel->where($zanData)->find();      
        $existedZan = empty($existedZan) ? 'no' : 'yes';

        $this->assign('existedZan', $existedZan);

        $FavorModel = M('biz_bj_resource_collect');
        $favorData['resource_id'] = $id;
        $favorData['role'] =  $_GET['role'] - 2;
        $favorData['user_id'] =  $_GET['user_id'];
        $existedFavor = $FavorModel->where($favorData)->find();
        $existedFavor = empty($existedFavor) ? 'no' : 'yes';
        $this->assign('existedFavor', $existedFavor);


        //$arr = explode("/", $result[file_path]);      
        //$fileName = $arr[1];
        $arr = explode(".", $result[file_path]);        
        $this->assign('localPdffileName', '../../../Resources/jb/' . $arr[0] . ".pdf");

        $this->assign('data', $result);     
        $this->assign('REMOTE_ADDR',C('REMOTE_ADDR'));  


        $apkinfo = file_get_contents("http://www.jingbanyun.com/index.php?m=Home&c=Download&a=version&ostype=Android");
        $apkinfo = json_decode($apkinfo,true);
        $apkurl = $apkinfo['data']['download_path'];    
        $this->assign('apkurl',$apkurl);
        
        $this->display();

    }

    public function teachinfoDetails()
    {
        $id = $_GET['id'];
        $this->display();
    }

    public function etextbook()
    {
        $c['id'] = $_GET['id'];

        $Model = M('biz_textbook');
        $result = $Model->where($c)->find();
        $this->assign('book', $result);
        $this->display();
    }

    public function addAccessRecord()
    {
        if($_POST)
        {
        if(!isset($_POST['machine']))
          $this->ajaxReturn('missing machine parameter!');
        if(!isset($_POST['user_id']) || !isset($_POST['user_type']))
          $this->ajaxReturn('missing user_id or user_type!');
         $user_IP = ($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
         $user_IP = ($user_IP) ? $user_IP : $_SERVER["REMOTE_ADDR"];

         $addData = array(
          'user_type' => $_POST['user_type'],
          'user_id' => $_POST['user_id'],
          'machine_type' => $_POST['machine'],
          'ip_address' => $user_IP,
          'create_at' => time()
         );
         M('app_statistics') ->add($addData);
         $this->ajaxReturn('success');
        }
        else
        $this->ajaxReturn('missing post parameter!');
    }

    /*
     * 家长端升级须知
     */
    public function parentNotice()
    {
        $ostype = isset($_GET['ostype']) ? $_GET['ostype']:'';
        $version = isset($_GET['version']) ? $_GET['version']:0;
        $this->display();
//        if($ostype=='ios' && ($version>0))
//        {
//            echo "<html>
//            <head>
//            <title>用户须知</title>
//            </head>
//            用户须知
//            </html>
//            ";
//            //$this->display();
//        }else{
//            $this->display();
//        }
    }

    /*
     * 教师端升级须知
     */
    public function teacherNotice()
    {
        $ostype = isset($_GET['ostype']) ? $_GET['ostype']:'';
        $version = isset($_GET['version']) ? $_GET['version']:0;
        $this->display();
//        if($ostype=='ios' && ($version>0))
//        {
//            echo "<html>
//            <head>
//            <title>用户须知-学生</title>
//            </head>
//            用户须知-学生
//            </html>
//            ";
//            //$this->display('iosteacherNotice');
//        }else{
//            $this->display();
//        }
    }

    /*
     * 学生端端升级须知
     */
    public function studentNotice()
    {
        $ostype = isset($_GET['ostype']) ? $_GET['ostype']:'';
        $version = isset($_GET['version']) ? $_GET['version']:0;
        $this->display();
//        if($ostype=='ios' && ($version>0))
//        {
//            echo "<html>
//            <head>
//            <title>用户须知</title>
//            </head>
//            用户须知
//            </html>
//            ";
//            //$this->display('iosteacherNotice');
//        }else{
//            $this->display();
//        }
    }
   private function getPlatform()
   {
    if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
        return 'Ios';
    }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
        return 'Android';
    }else{
        return 'Other';
    }
   }
   public function MaterialDetails()
       {    
            //NOTE:ios--org file path
            //     android-- mp4 mp3 img:org file path ; pdf doc word:pdfjs
            $id = intval($_GET['id']);
            $Model = M('biz_material');
            $result = $Model ->where('id='.$id)->field('id,type,create_at,file_path,flag,material_name')->find();  
            $platform = $this->getPlatForm();
            switch($platform)
            {
             case 'Ios':     redirect(C('oss_path').$result['file_path']);
                             break;
             case 'Android':
                    default:
                             $this->assign('data',$result);
                             $this->display();

             }

       }

   //新年祝福

   public function weChatNewHappy() {
       $name = I('name');
       if ( !empty($name )) {
           $this->assign('name',$name);
       }

       $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
       $signPackage = $jssdk->GetSignPackage();
       $this->assign('signPackage',$signPackage);

       $this->display();
   }

   //新年祝福
    public function weChatNewHappyTwo() {

        $name = I('name');
        if ( !empty($name )) {
            $this->assign('name',$name);
        }

        $jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);

        $this->display();
    }
	
	public function weChatNewAjax()
	{
		$name = I('name');
        if ( !empty($name )) {
            $this->assign('name',$name);
        }
		
		$jssdk = new JSSDK("wxa6d2714aa7728aef", "4b62d67992416eac3e58f3ebd4ae7993");
        $signPackage = $jssdk->GetSignPackage();
        echo json_encode($signPackage);
	}
	
}