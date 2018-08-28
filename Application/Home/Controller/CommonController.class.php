<?php
namespace Home\Controller;

use Common\Common\SMS;

use Think\Controller;
use Common\Common\CSV;
use Common\Common\DES3;
use Common\Common\REDIS;
//use Common\Model\auth_studentModel;

class CommonController extends Controller
{
    function __construct(){
        parent::__construct();
        $this->assign('oss_path',C('oss_path'));
    }

    //直接下载文件
    public function downloadFile() {
        $filename = I('filename');
        //http://www.jingbanyun.com/index.php?m=Home&c=BjResource&a=downloadBjResource&id=3472&url=http://jbyoss.oss-cn-beijing.aliyuncs.com/public/web_img/Live/ppth/1%E3%80%81%E5%8C%97%E4%BA%AC%E5%B8%82%E8%AF%AD%E6%96%87%E6%95%99%E5%AD%A6%E5%A4%A7%E4%BC%9A%E8%AE%B2%E8%AF%9D%E7%A8%BF(%E6%B8%A9%E5%84%92%E6%95%8F)180420.pdf
        $this->forceDownload("http://jbyoss.oss-cn-beijing.aliyuncs.com/public/web_img/Live/ppth/1%E3%80%81%E5%8C%97%E4%BA%AC%E5%B8%82%E8%AF%AD%E6%96%87%E6%95%99%E5%AD%A6%E5%A4%A7%E4%BC%9A%E8%AE%B2%E8%AF%9D%E7%A8%BF(%E6%B8%A9%E5%84%92%E6%95%8F)180420.pdf");
    }

    //下载京版资源
    public function downloadBjResource()
    {
        //$id=intval(I('id'));
        $url = getParameter('url', 'str');
        preg_match('/\/([^\/]+\.[a-z]+)[^\/]*$/',$url,$match);
        if (empty($url) || $url == '') {
            redirect(U('Index/systemError'));
        } else {
            $csv = new CSV();
            $csv->downloadMediaCopy($url,$match[1]);
        }
    }

    //功能引导
    public function getBjResourcePrompt($userId,$role) {
        $data['model_name'] = 'bjresource1';
        $data['user_id'] = $userId;
        $data['role'] = $role;
        $row = M('update_model_prompt')->where( $data )->find();

        if (empty($row)) {
            $map['user_id'] = $userId;
            $map['role'] = $role;
            $map['model_name'] = 'bjresource1';
            M('update_model_prompt')->add( $map );
        }
        return $row;
    }

    //功能引导
    public function getLearnPathPrompt($userId,$role) {
        $data['model_name'] = 'TeachLearnPath1';
        $data['user_id'] = $userId;
        $data['role'] = $role;
        $row = M('update_model_prompt')->where( $data )->find();

        if (empty($row)) {
            $map['user_id'] = $userId;
            $map['role'] = $role;
            $map['model_name'] = 'TeachLearnPath1';
            M('update_model_prompt')->add( $map );
        }
        return $row;
    }


    /*
     * 获得电子课本的条件
     */
    public function getTextBookSelector($check,$group=''){
        switch ($group) {
            case 'grade': 
                $group_str='dict_grade.id';
                break;
            case 'course': 
                $group_str='dict_course.id';
                break;
            case 'school_term': 
                $group_str='school_term';
                break;
        }
        $Model = M('biz_textbook'); 
        $result = $Model
                ->join('dict_course on dict_course.id=biz_textbook.course_id')
                ->join('dict_grade on dict_grade.id=biz_textbook.grade_id')
                ->field('dict_course.id,dict_course.course_name,dict_grade.code,dict_grade.grade,school_term')
                ->where($check) 
                ->group($group_str)
                ->select();                    
        return $result;
    }


    /*
    * 获得电子课本的条件
    */
    public function getAppTextBookSelector($check,$group=''){
        switch ($group) {
            case 'grade':
                $group_str='dict_grade.id';
                break;
            case 'course':
                $group_str='dict_course.id';
                break;
            case 'school_term':
                $group_str='school_term';
                break;
        }
        $Model = M('biz_textbook');
        if ($group=='course') {
            //dict_course.id,dict_course.course_name,dict_grade.code,dict_grade.grade,school_term
            $field = 'dict_course.id,dict_course.course_name as name';
        }
        if($group=='grade') {
            $field = 'dict_grade.id,dict_grade.grade as name';
        }
        if($group=='school_term') {
            $field = 'school_term as id';
        }
        $result = $Model
            ->join('dict_course on dict_course.id=biz_textbook.course_id')
            ->join('dict_grade on dict_grade.id=biz_textbook.grade_id')
            ->field($field)
            ->where($check)
            ->group($group_str)
            ->select();
        return $result;
    }

    public function CourseGradeTextBookSelector($data=array())
    { 
     $this->assign('courses', D('Dict_course')->getAvailableCourse($data['grade_id']),$data['textbook_id']);
     $this->assign('grades', D('Dict_grade')->getAvailableGrade($data['course_id'],$data['textbook_id']));
     $this->assign('textbooks', D('Biz_textbook')->getAvailableSchoolTerm($data['course_id'],$data['grade_id']));

     $this->assign('course_id', $data['course_id']);
     $this->assign('grade_id', $data['grade_id']);
     $this->assign('textbook_id', $data['textbook_id']);
     $this->assign('keyword',$data['keyword']);
    }


    private function videoCutImage($dirNo){
        $file=$_FILES['file'];
        $array=array();
        //前端框架每次发送一个file过来
        for($i=0;$i<count($file['name']);$i++){
            $suffix=substr($file['name'][$i],strrpos($file['name'][$i],'.')+1);
            if($suffix=='mp4'){
                $original_file=$file['tmp_name'][$i];
                $current_file='Public/tmp/'.rand(100,10000).'.jpg';
                //file_put_contents('Public/tmp/aa.txt','33');
                exec("/usr/local/ffmpeg/bin/ffmpeg -i ".$original_file." -y -f image2 -ss 2.010 -t 0.001 -s 800*600 ".$current_file." ");

                //这里判断视频是否是h264格式的
                exec("/usr/local/ffmpeg/bin/ffprobe -i ".$original_file." 2>&1",$output,$status);
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
                $result=$upload->upload(3,$temp_file,$dirNo,0);

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
    /**上传文件方法
     * @param $dirNo 目录编号 upload_path
     * @return array  成功失败标志
     */
    public function baseUploader($dirNo)
    {
        set_time_limit(0);
        $upload = new \Oss\Ossupload();// 实例化上传类
        $result=$upload->upload(3,$_FILES,$dirNo,0); //1 pic 2//
        $returnArray = explode(",",$result[1]);
        $uploadOK = true;
        for($i=0;$i<sizeof($returnArray);$i++)
        {
            if($returnArray[$i] == "")
            {
                 $uploadOK = false;
            }
            $fileNameSingle = $_FILES['file']['name'][0];
            $fileName = $fileNameSingle;

        }
        //todo :视频截图
        $image = $this->videoCutImage($dirNo);
        return array('status' => $uploadOK,'msg'=>$result[2],'filePath' => $result[1],'imagePath' => $image,'fileName' =>$fileName);
    }

    public function getUserIdRole(&$userId,&$role){
        //TODO:judgement by useragent
        if(!$this->isApp()) {
            if (session('teacher.id')) {
                $userId = session('teacher.id');
                $role = ROLE_TEACHER;
            } else if (session('student.id')) {
                $userId = session('student.id');
                $role = ROLE_STUDENT;
            } else if (session('parent.id')) {
                $userId = session('parent.id');
                $role = ROLE_PARENT;
            } else {
                $userId = -1;
                $role = -1;
            }
        }
        else{
            //get userId role from header
            if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'iphone') || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'ipad')){
                $userId = empty($_REQUEST['userId'])?-1:$_REQUEST['userId'];
                if($userId != -1)
                    $role = empty($_REQUEST['role'])?-1:$_REQUEST['role'];
            }
            else if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android')){
                preg_match("/&userId=(\d+)/is",$_SERVER['HTTP_USER_AGENT'],$regs);
                $userId = $regs[1];
                if(empty($user_id))
                    $userId=-1;
                preg_match("/&role=(\d+)/is",$_SERVER['HTTP_USER_AGENT'],$regs);
                $role = $regs[1];
                if(empty($regs[1]))
                    $role=-1;
                if(!empty($_REQUEST['userId']) && !empty($_REQUEST['role'])){
                    $userId = $_REQUEST['userId'];
                    $role = $_REQUEST['role'];
                }

            }
        }
    }

    public function getUserCourseGradeInfo($role,$userId)
    {
        $courseId = 0;
        $gradeId = 0;
        switch($role)
        {
            case ROLE_TEACHER:
                //获取老师首个学科年级
                $teacherInfo = D('Auth_teacher_second')->getCourseGradeById($userId);
                $courseId = $teacherInfo[0]['course_id'];
                $gradeId = $teacherInfo[0]['grade_id'];
                break;
            case ROLE_STUDENT:
                //get grade info from school class
                $model = D('Biz_class');
                $classId = $model->getStudentSchoolClass($userId);
                if(!empty($classId))
                {
                    $classInfo = $model->getClassInfo($classId);
                    $gradeId = $classInfo['grade_id'];
                }
                break;
            default:break;
        }
        return array('courseId'=>$courseId,'gradeId'=>$gradeId);
    }

    /*
     *查看当前用户有已报名的但是没有上传作品的
     */
    public function registered_but_no_uploadworks($userId,$role)
    {
        //$this->getUserIdRole($userId,$role);
        $model = D('Social_activity');
        return $model->registered_but_no_uploadworks($userId,$role);
    }
    public function authJudgement($role='')
   {   $this->getUserIdRole($user_id,$role);    
            
       switch($role)
       {
           case ROLE_TEACHER : $isAuth = A('Home/Teach')->isAuth(CONTROLLER_NAME.'_'.ACTION_NAME);    
               if (!$isAuth) { //如果访问的模块没有权限
                   redirect(U('Teach/index1?auth_error=1'));
               }
               break;
           case ROLE_STUDENT : $isAuth = A('Home/Student')->isAuth(CONTROLLER_NAME.'_'.ACTION_NAME);
               if (!$isAuth) { //如果访问的模块没有权限
                   redirect(U('Student/index1?auth_error=1'));
               }
               break;
           case ROLE_PARENT :  $isAuth = A('Home/Parent')->isAuth(CONTROLLER_NAME.'_'.ACTION_NAME);
               if (!$isAuth) { //如果访问的模块没有权限
                   redirect(U('Parent/index1?auth_error=1'));
               }
               break;
           default:
               $isAuth = A('Home/Teach')->isAuth(CONTROLLER_NAME.'_'.ACTION_NAME);
               if (!$isAuth) { //如果访问的模块没有权限
                   redirect(U('Teach/index1?auth_error=1'));
               }
               break;
       }
   }

    public function isApp()
    {
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'jingbanyun') !== false )
          return true;
        return false;
    }

    //获取登陆用户的id role_id 去查询信息
    public function getMessageIsBox () {
        $studentsession = session('student');
        $parentsession = session('parent');
        $teachersession = session('teacher');
        if ( !empty($studentsession) ) {
            $userid = $studentsession['id'];
            $role = 3;
        }
        if ( !empty($parentsession) ) {
            $userid = $parentsession['id'];
            $role = 4;
        }
        if ( !empty($teachersession) ) {
            $userid = $teachersession['id'];
            $role = 2;
        }

        $data = array(
            'receive_message_user.role_id'=>$role,
            'receive_message_user.user_id'=> $userid,
            'role_message.is_box' => 2,
        );

        $sendinfo = M('receive_message_user')
                ->join('role_message on role_message.id=receive_message_user.message_id')
                ->where( $data )
                ->select();

        $str = '';
        foreach ( $sendinfo as $k=>$v ) {
            if ( $k==0 ) {
                $str.= $v['title'];
            } else {
                $str.=','.$v['title'];
            }
            $savemap['id'] = $v['message_id'];
            $savedata['is_box'] = 1;
            M('role_message')->where( $savemap )->save( $savedata );
        }

        $senddata['sendinfo'] = $str;
        echo json_encode($senddata);
    }

    function gmt_iso8601($time) {
            $dtStr = date("c", $time);
            $mydatetime = new \DateTime($dtStr);
            $expiration = $mydatetime->format(\DateTime::ISO8601);
            $pos = strpos($expiration, '+');
            $expiration = substr($expiration, 0, $pos);
            return $expiration."Z";
        }

        public function getOssPolicy()
       {
           $alioss_config=C('ALIOSS_CONFIG');
           $id= $alioss_config['KEY_ID'];
           $key=$alioss_config['KEY_SECRET'];
           $host = C('oss_path');

           $now = time();
           $expire = 30; //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问
           $end = $now + $expire;
           $expiration = $this->gmt_iso8601($end);

           $dir = Date('Y-m-d') .'/';

           //最大文件大小.用户可以自己设置
           $condition = array(0=>'content-length-range', 1=>0, 2=>1048576000);
           $conditions[] = $condition;

           //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
           $start = array(0=>'starts-with', 1=>'$key', 2=>$dir);
           $conditions[] = $start;


           $arr = array('expiration'=>$expiration,'conditions'=>$conditions);
           //echo json_encode($arr);
           //return;
           $policy = json_encode($arr);
           $base64_policy = base64_encode($policy);
           $string_to_sign = $base64_policy;
           $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

           $response = array();
           $response['accessid'] = $id;
           $response['host'] = $host;
           $response['policy'] = $base64_policy;
           $response['signature'] = $signature;
           $response['expire'] = $end;
           //这个参数是设置用户上传指定的前缀
           $response['dir'] = $dir;
           echo json_encode($response);


       }

     public function getPostSignature()
     {
         $alioss_config=C('ALIOSS_CONFIG');
         $id= $alioss_config['KEY_ID'];
         $key=$alioss_config['KEY_SECRET'];
         $host = C('oss_path');
         $fileName = I('fileName');
         $content_md5 = '';
         $contentType = 'application/octet-stream';
         $dir = Date('Y-m-d') .'/';

         $date = gmdate('D, d M Y H:i:s \G\M\T');
         $CanonicalizedOSSHeaders ='x-oss-date:'.$date;
         $CanonicalizedResource = '/'.$alioss_config['BUCKET'].'/'.$dir. $fileName. '?uploads';
         $string_to_sign = "POST\n".$content_md5 ."\n" . $contentType . "\n" . $date . "\n" . $CanonicalizedOSSHeaders ."\n" . $CanonicalizedResource ;
         $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));
         $response['Authorization'] = "OSS " . $id . ":" .$signature;
         $response['Date'] = $date;
         $response['dir'] =  $dir;
         $response['host'] =  $host;
         echo json_encode($response);
     }
    public function getPutSignature()
    {
        $alioss_config=C('ALIOSS_CONFIG');
        $id= $alioss_config['KEY_ID'];
        $key=$alioss_config['KEY_SECRET'];
        $host = C('oss_path');
        $fileAndParas = urldecode($_POST['fileAndParas']);
        $content_md5 = '';
        $contentType = '';
        $dir = Date('Y-m-d') .'/';

        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $CanonicalizedOSSHeaders ='x-oss-date:'.$date;
        $CanonicalizedResource = '/'.$alioss_config['BUCKET'].'/'. $fileAndParas;
        $string_to_sign = "PUT\n".$content_md5 ."\n" . $contentType . "\n" . $date . "\n" . $CanonicalizedOSSHeaders ."\n" . $CanonicalizedResource ;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));
        $response['Authorization'] = "OSS " . $id . ":" .$signature;
        $response['Date'] = $date;
        $response['dir'] =  $dir;
        $response['host'] =  $host;
        echo json_encode($response);
    }

    public function getCompleteSignature()
    {
        $alioss_config=C('ALIOSS_CONFIG');
        $id= $alioss_config['KEY_ID'];
        $key=$alioss_config['KEY_SECRET'];
        $host = C('oss_path');
        $fileAndParas = urldecode($_POST['fileAndParas']);
        $content_md5 = '';
        $contentType = 'application/x-www-form-urlencoded, application/octet-stream';
        $dir = Date('Y-m-d') .'/';

        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $CanonicalizedOSSHeaders ='x-oss-date:'.$date;
        $CanonicalizedResource = '/'.$alioss_config['BUCKET'].'/'. $fileAndParas;
        $string_to_sign = "POST\n".$content_md5 ."\n" . $contentType . "\n" . $date . "\n" . $CanonicalizedOSSHeaders ."\n" . $CanonicalizedResource ;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));
        $response['Authorization'] = "OSS " . $id . ":" .$signature;
        $response['Date'] = $date;
        $response['dir'] =  $dir;
        $response['host'] =  $host;
        echo json_encode($response);
    }

    public function pushErrorLog()
    {
        $data = getParameter('data','str');
        $specNum = getParameter('specNum','int',false);
        $redis_obj=new REDIS();
        $redis=$redis_obj->init_redis();
        $redis_key='ERR_'. time();
        if(!empty($specNum))
            $redis_key .= '_UPLOAD'.$specNum;
        $redis->setex($redis_key,3600 * 24,$data.' AGENT: '. $_SERVER['HTTP_USER_AGENT']);
        $redis->close();
        echo 'pushLog';
    }
/*
 *@获取该用户购买该资源的订单状态
 * @返回1：已购买并支付完成或免费资源2：生成订单单位支付 0：收费资源没有购买
 */
    public function returnStatus($resource_id,$user,$role){
        $resources = D('Knowledge_resource')->getResourceInfo($resource_id);
        if($resources['charge_status'] == 2){//判断是否为收费资源
            $data = array();
            $payment_order_info=D('Order_info')->getPaymentOrderResource($resource_id,$user,$role);
            if(!empty($payment_order_info)){
                if($payment_order_info['order_status'] == 2){//已支付
                    $data['is_allowed_browse'] =1;
                }elseif ($payment_order_info['order_status'] == 1){//未支付
                    $data['is_allowed_browse'] =2;
                    $data['noPaymentOrder_sn']  = $payment_order_info['order_sn'];//未支付的订单号
                }
                else
                {
                    $data['is_allowed_browse'] =0;
                }
            }else{
                $data['is_allowed_browse'] =0;
            }

            return $data;
        }else{
            $data['is_allowed_browse'] =1;
            return $data;
        }

    }

    public function getSTSToken()
    {
        $apiParams = array();
        foreach ($apiParams as $key => $value) {
            $apiParams[$key] = $this->prepareValue($value);
        }
        $apiParams['Format'] = 'JSON';
        $apiParams['Version'] = '2015-04-01';
        $apiParams["AccessKeyId"] = 'LTAIzkKlIihB7Mkf';
        $apiParams["SignatureMethod"] = 'HMAC-SHA1';
        $apiParams["SignatureVersion"] = '1.0';
        $apiParams["SignatureNonce"] = uniqid();
        date_default_timezone_set("GMT");
        $apiParams["Timestamp"] = date('Y-m-d\TH:i:s\Z');
        $apiParams["Action"] = 'AssumeRole';
        $apiParams["RoleArn"] = 'acs:ram::1467262512804062:role/nei-rong';
        $apiParams["RoleSessionName"] = 'approle';
        $apiParams["Policy"] = '{"Version":"1","Statement":[{"Effect":"Allow","Action":["oss:PutObject"],"Resource":["acs:oss:*:*:*"],"Condition":{}}]}';
        $apiParams["Signature"] = $this->computeSignature($apiParams, '7fqg9MfxkgffS3665iLdbWSPiHWu48');
        $requestUrl = 'https://sts.aliyuncs.com/?';
            foreach ($apiParams as $apiParamKey => $apiParamValue) {
                $requestUrl .= "$apiParamKey=" . urlencode($apiParamValue) . "&";
            }

        $result = file_get_contents($requestUrl);

            echo $result;
            exit;
    }

    private function computeSignature($parameters, $accessKeySecret)
    {
        ksort($parameters);
        $canonicalizedQueryString = '';
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key). '=' . $this->percentEncode($value);
        }
        $stringToSign = 'GET'.'&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret.'&', true));//$iSigner->signString($stringToSign, $accessKeySecret."&");
        return $signature;
    }

    protected function percentEncode($str)
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }
    private function __echoDifference($str)
    {
        exec($str,$re,$res);
        foreach ($re as $re1)
        {
            echo $re1."<br>";
        }

    }
    public function showDbDiffForPreReleaseAndRelease(){

        $execStr = 'mysqldiff --server1=root:Jby\&*2016@localhost:3306 --server2=jbyserver:Jby\&*@_2016@rm-2zec5i4666piy4fi2.mysql.rds.aliyuncs.com:3306 jingtongcloud:jingtongcloud --difftype=sql --changes-for=server2 --skip-table-options --force | grep "^[^#]" ';
        $this->__echoDifference($execStr);
    }

    public function showDbDiffForTestAndPreRelease(){
        $execStr = 'mysqldiff --server1=root:Jby\&*2016@www.jtypt.com:3306 --server2=root:Jby\&*2016@localhost:3306 jingtongcloud:jingtongcloud --difftype=sql --changes-for=server2 --skip-table-options --force | grep "^[^#]" ';
        $this->__echoDifference($execStr);
    }
}

?>