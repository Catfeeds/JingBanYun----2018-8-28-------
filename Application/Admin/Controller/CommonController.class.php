<?php
namespace Admin\Controller;
use Think\Controller;  
define('CLASS_STUDETN_STATUS',2); 
define('SCHOOL_CLASS',1);
define('PERSONAL_CLASS',2);
define('PERSONAL_CLASS_DENY_REMOVE_TEACHER','个人班级不能移除教师!');


class CommonController extends Controller
{   

    public $model;
    public $page_size=20; 
                
    public function __construct() {
        parent::__construct();
        $this->model=D('Auth_school_admin');
    }
    
    
    /*
     * 把某个学生从某个班级移除
     */
    public function classRemoveStudent(){ 
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $model=M('auth_student');
        $class_model=D('Biz_class');
        $class_id=getParameter('class_id','int');
        $student_id=getParameter('student_id','int');
        $condition['biz_class.id']=$class_id;
        $condition['biz_class_student.student_id']=$student_id;
        $result=$class_model->getClassStudentDataAll($condition);
        $student_info = D('Auth_student')->getStudentInfo( $student_id );
        $class_result_info=D('Biz_class')->getClassAndGradeInfo($class_id);

        if(empty($result)){
            $this->showjson(401,COMMON_FAILED_MESSAGE);
        }   
        $this->model->startTrans();
        $class_con['biz_class_student.student_id']=$student_id;  
        $class_con['biz_class.id']=$class_id;
        $class_result=$class_model->getClassStudentDataAll($class_con);
        if(!empty($class_result)){
            $student_data['student_id']=$student_id;
            $student_data['class_id']=$class_id;
            $student_data['status']=$class_result[0]['class_student_status'];
            $student_data['create_at']=time();
            $student_data['joinmode']=$class_result[0]['joinmode'];
            if(!$class_model->addClassStudentRecord($student_data)){
                $this->model->rollback();
                $this->showjson(404,'操作失败');
            }
        }
        if(!$class_model->removeClassStudentById($class_id,$student_id)){
            $this->model->rollback();
            $this->showjson(403,COMMON_FAILED_MESSAGE);
        }
        /*if(!$class_model->updateClassStudentCount($class_id)){
            $this->model->rollback();
            $this->showjson(404,COMMON_FAILED_MESSAGE);
        }*/


        $parameters = array(
            'msg' => array(
                C('SCHOOL_ROOT'),
                $student_info['student_name'],
                $class_result_info['grade'],
                $class_result_info['name'],
                C('SCHOOL_ROOT'),
                $class_result_info['grade'],
                $class_result_info['name'],
            ),
            'url' => array( 'type' => 0,)
        );
        $parameters['url'] = array( 'type' => 1,'data'=>array($student_info['id'],$student_info['student_name']));
        A('Home/Message')->addPushUserMessage('DELSTUDENTCLASS', 4,$student_info['parent_id'] , $parameters);

        //发送给学生
        $parameters['url'] =  array( 'type' => 0);
        unset($parameters['msg'][1]);
        A('Home/Message')->addPushUserMessage('SCHOOL_CLASS_REMOVE_TEACHER', 3,$student_info['id'] , $parameters);

        $this->model->commit();
        $this->showjson(200);
    }
    
    
    
    /*
     * 把某个教师从班级移除
     */
    public function classRemoveTeacher(){

        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $teacher_model=D('Auth_teacher');
        $class_model=D('Biz_class');
        $teacher_id=getParameter('teacher_id','int');
        $class_id=getParameter('class_id','int');
        $teacher_result=$teacher_model->getTeachInfo($teacher_id);
        $class_result=$class_model->getClassSchoolData($class_id);
        if(empty($teacher_result)){
            $this->showjson(401,ID_NOT_EXISTS_MESSAGE);
        }
        if(empty($class_result)){
            $this->showjson(402,ID_NOT_EXISTS_MESSAGE);
        }
        if($class_result==PERSONAL_CLASS){
            $this->showjson(403,PERSONAL_CLASS_DENY_REMOVE_TEACHER);
        }
        $this->model->startTrans();
        //加入教师班级记录表
        $class_teacher_result=$class_model->getClassListByTeacher($teacher_id,$class_id);
        if(!empty($class_teacher_result)){
            $record_data['teacher_id']=$teacher_id; 
            $record_data['class_id']=$class_id;
            $record_data['course_id']=$class_teacher_result[0]['course_id'];
            $record_data['is_handler']=$class_teacher_result[0]['is_handler'];
            $record_data['create_at']=time();
            if(!$class_model->addClassTeacherRecord($record_data)){
                $this->model->rollback();
                $this->showjson(404,'操作失败!');
            } 
        }
        if(!$class_model->leaveClass($class_id,PERSONAL_CLASS,$teacher_id,$error_info)){
            $this->model->rollback();
            $this->showjson(405,COMMON_FAILED_MESSAGE);
        }
        if(!$class_model->deleteClassTimetable($class_id,$teacher_id)){
            $this->model->rollback();
            $this->showjson(406,COMMON_FAILED_MESSAGE);
        }

        $parameters = array(
            'msg' => array(
                C('SCHOOL_ROOT'),
                $class_result['grade'],
                $class_result['class_name'],
                C('SCHOOL_ROOT'),
                $class_result['grade'],
                $class_result['class_name'],
            ),
            'url' => array( 'type' => 0,'data' => array() )
        );

        $add = A('Home/Message')->addPushUserMessage('CLASS_REMOVE_TEACHER', 2, $teacher_id , $parameters);
        //print_r($add);die();

        $this->model->commit();
        $this->showjson(200);
    }
    
    
    
    /*
     * 通过某个年级获得该年级下所有班级
     */
    public function getClassByGrade(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $class_model=D('Biz_class');
        $grade_id=getParameter('grade_id','int');
        $school_id=getParameter('school_id','int');
        $class_type=getParameter('class_type','int',false); 
        $group_class_flag=getParameter('group_class_flag','int',false); 
        if($class_type==1){
            //校内班
            $result=$class_model->getClassDataBySchool($school_id,$grade_id);  
        }else if($class_type==2){
            //个人班
            $result=$class_model->getClassDataByTeacherSchool($school_id,$grade_id); 
        }else{
            //全部班级 
            $result=$class_model->getClassDataByTeacherSchool($school_id,$grade_id,true,$group_class_flag);
        } 
        $this->showjson(200,'',$result);
    }
    
    
     /*
     * 根据省份获得城市
     */
    public function getCityByProvince(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $school_model=D('Dict_schoollist');
        $id=getParameter('province_id','int');
        $result=$school_model->getCityByProvince($id);
        $this->showjson(200,'',$result);
    }
    
    
    /*
     * 根据城市获得县区
     */
    public function getDistrictByCity(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $school_model=D('Dict_schoollist');
        $id=getParameter('city_id','int');
        $result=$school_model->getDistrictByCity($id);
        $this->showjson(200,'',$result);
    }
    
    
    /*
     * 根据区县获得学校
     */
    public function getSchoolByDistrict(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $school_model=D('Dict_schoollist');
        $id=getParameter('district_id','int');
        $result=$school_model->getSchoolByDistrict($id);
        $this->showjson(200,'',$result);
    }
    
    
    /*
     * 重置学校管理员(顶级管理员)密码
     */
    public function resetSchoolAdminPassword(){
        if (!session('?admin')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $admin_model=D('Auth_admin');
        $admin_id=getParameter('id','int');
        $result=$admin_model->getSchoolAdminData($admin_id,true);
        if(empty($result)){
            $this->showjson(401,COMMON_FAILED_MESSAGE);
        }
        $rand_password=rand(100000,999999);
        $data['password']=sha1($rand_password);
        if(!$admin_model->updateSchoolAdminData($admin_id,$data)){
            $this->showjson(402,COMMON_FAILED_MESSAGE);
        }
        $this->showjson(200,'',$rand_password);
    }
    
    
    /*
     * 获得某个学校的信息
     */
    public function getSchoolInfo(){
        if (!session('?school')) $this->showjson(400,ACCOUNT_FAILURE);
        
        $school_model=D('Dict_schoollist');
        $school_id=getParameter('school_id','int');
        $result=$school_model->getSchoolInfo($school_id);
        $this->showjson(200,'',$result);
    }

    /*
     *在后台资源删除、新增、上下架、审核操作时要更新knowledge_count字段
     */
    public function updateKnowledge_count(){
        $model = D('Knowledge_resource');
        $model->updateKnowledgeCountChapter();
        $model->updateKnowledgeCountFestival();
        $model->updateKnowledgeCountKnowledge();
    }

    /*
     * 下载文件到本地
     */
    public function downloadFile($url,$new_file){
        $file=fopen($url,'rb');
        if($file){
            $newf=fopen($new_file,'wb');
            if($newf){
                while(!feof($file)){
                    fwrite($newf,fread($file,1024*8),1024*8);
                }
            }
            if($file){
                fclose($file);
            }
            if($newf){
                fclose($newf);
            }
        }else{
            return false;
        }
        return true;
    }

    public function downPostFile($url, $file,$savePath = './uploads')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $post_data['filePath']=$file;
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_NOBODY, FALSE);    //需要response body
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('filePath:'.$file));
        $response = curl_exec($ch);
        //分离header与body
        $header = '';
        $body = '';
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200') {
            if (file_put_contents($savePath, $response)) {
                return $file;
            }
            curl_close($ch);
        }
        return false;
    }
    /*
     * PPT WORD PDF SWF转图片
     * 需要事先安装
     * libreoffice ImageMagick swftools 及simsun.ttc中文字体至LINUX系统
     *
     */
    public function convertResource2JPG($filePath='',$remoteFilePath='')
    {
        if($filePath == '')
        {
            return false;
        }
        $fileType = getFileType(($filePath));
        if($fileType == '')
        {
            return false;
        }
        $fileNameWithoutExt = explode('.',basename($filePath));
        $fileNameWithoutExt = $fileNameWithoutExt[0];
        $outputJpgFile = "/tmp/{$fileNameWithoutExt}.jpg";
        switch($fileType)
        {
            case 'ppt': $result = $this->downPostFile('http://121.42.13.204:12345/pptconvert',$remoteFilePath,$outputJpgFile);
                         if(false == $result)
                             return false;
                         break;
            case 'word':
                         exec("export HOME=/tmp && soffice --headless --convert-to pdf $filePath --outdir  /tmp/");
                         $pdfFileName  = '/tmp/'.$fileNameWithoutExt.'.pdf';
            case 'pdf': if(!$pdfFileName)
                          $pdfFileName = $filePath;
                         //convert pdf[0] to jpg
                         exec('convert -density 120 -quality 100 '.$pdfFileName.'[0] '.$outputJpgFile);
                         @unlink($pdfFileName);
                         break;
            case 'swf': exec("swfdec-thumbnailer -s 400 $filePath $outputJpgFile");
                         break;
            default:return false;
        }
        @unlink($filePath);
        if(!is_file($outputJpgFile))
            return false;
        return $outputJpgFile;
    }

    public function uploadOssFile($save_file=''){
        if($_FILES){
            set_time_limit(0);
            $upload = new \Oss\Ossupload();
            //$GLOBALS['is_watermark']=1;
            $result=$upload->upload(3,$_FILES,3,0);
            echo json_encode($result);
        }else{
            $post_data['file']='@'.$save_file;
            $ch = curl_init();
            $url="http://".$_SERVER['SERVER_NAME']."/index.php?m=Admin&c=Common&a=uploadOssFile";
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // post数据
            curl_setopt($ch, CURLOPT_POST, 1);
            // post的变量
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            $output = curl_exec($ch);
            curl_close($ch);
            //打印获得的数据
            print_r($output);
            $output = json_decode($output);
            if($output[0]!=0)
                return -1;
            return $output[1];
        }

    }
    public function reshapeImage($imgPath,$newPath,$newWidth=450,$newHeight=364)
    {
        $imgInfo = getimagesize($imgPath);
        $w   = $imgInfo[0];
        $h   = $imgInfo[1];
        if($w==$newWidth && $h==$newHeight)
            return 2;
        if($w/$newWidth < $h/$newHeight)
        {
            $targetStartX = ($newWidth-$w*($newHeight/$h)) /2 ;
            $targetWidth = $newWidth - 2*$targetStartX;
            $targetStartY =  0;
            $targetHeight =  $newHeight;
        }
        else
        {
            $targetStartY = ($newHeight-$h*($newWidth/$w)) /2 ;
            $targetHeight = $newHeight - 2*$targetStartY;
            $targetStartX =  0;
            $targetWidth =  $newWidth;
        }
        //echo $targetStartX.' ';echo $targetWidth.' ';echo $targetStartY.' ';echo $targetHeight.' ';exit;
        switch($imgInfo[2])//取得背景图片的格式
        {
            case 1:$ground_im = imagecreatefromgif($imgPath);break;
            case 2:$ground_im = imagecreatefromjpeg($imgPath);break;
            case 3:$ground_im = imagecreatefrompng($imgPath);
                $simg = imagecreatetruecolor($w, $h);
                $bg =imagecolorallocate($simg, 255, 255, 255);
                imagefill($simg, 0, 0, $bg);
                imagecopyresized($simg, $ground_im, 0, 0, 0, 0,$w, $h, $w, $h);
                imagepng($simg,$imgPath.'.png');imagedestroy($ground_im);$ground_im = imagecreatefrompng($imgPath.'.png');break;
            default:return $formatMsg;
        }
        $image = imagecreatetruecolor($newWidth, $newHeight);
        $color = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagecolortransparent($image,$color);
        imagecopyresampled($image, $ground_im, $targetStartX, $targetStartY, 0, 0, $targetWidth, $targetHeight, $w, $h);
        switch($imgInfo[2])//取得背景图片的格式
        {
            case 1:imagegif($image,$newPath);break;
            case 2:imagejpeg($image,$newPath);break;
            case 3:imagepng($image,$newPath);break;
            default:return $errorMsg;
        }
        return 1;
    }

    public function reshapeImageTo1V1($imgPath,$newPath)
    {
        $imgInfo = getimagesize($imgPath);
        $w   = $imgInfo[0];
        $h   = $imgInfo[1];
        if($w==$h && $w==200)
            return 2;
        $newImgWidth = $w<$h?$w:$h;
        $startX = $startY = 0;
        if($w<$h)
            $startY = ($h - $w)/2;
        else
            $startX = ($w - $h)/2;
        switch($imgInfo[2])//取得背景图片的格式
        {
            case 1:$ground_im = imagecreatefromgif($imgPath);break;
            case 2:$ground_im = imagecreatefromjpeg($imgPath);break;
            case 3:$ground_im = imagecreatefrompng($imgPath);
                $simg = imagecreatetruecolor($w, $h);
                $bg =imagecolorallocate($simg, 255, 255, 255);
                imagefill($simg, 0, 0, $bg);
                imagecopyresized($simg, $ground_im, 0, 0, 0, 0,$w, $h, $w, $h);
                imagepng($simg,$imgPath.'.png');imagedestroy($ground_im);$ground_im = imagecreatefrompng($imgPath.'.png');break;
            default:return $formatMsg;
        }
        $src = imagecreatetruecolor($newImgWidth, $newImgWidth);
        $color = imagecolorallocatealpha($src, 0, 0, 0, 127);
        imagecolortransparent($src,$color);
        imagealphablending($src,true);

        imagecopy($src, $ground_im, 0,0, $startX, $startY, $newImgWidth, $newImgWidth);
        $image = imagecreatetruecolor(200, 200);
        imagecopyresampled($image, $src, 0, 0, 0, 0, 200, 200, $newImgWidth, $newImgWidth);
        switch($imgInfo[2])//取得背景图片的格式
        {
            case 1:imagegif($image,$newPath);break;
            case 2:imagejpeg($image,$newPath);break;
            case 3:imagepng($image,$newPath);break;
            default:return $errorMsg;
        }
        return 1;
    }
    public function getRefreshResourceCoverCount()
    {
        $result = M('knowledge_resource_file_contact')->join('knowledge_resource ON knowledge_resource.id = knowledge_resource_file_contact.resource_id AND knowledge_resource.file_type in (\'ppt\',\'word\',\'pdf\',\'swf\')')
            ->field('knowledge_resource.id')->group('knowledge_resource.id')->select();
        $this->showMessage(200,'success',count($result));
    }
    public function refreshAllJBResourceCover()
    {
        set_time_limit(0);
        $pageSize = 1;
        $pageIndex = getParameter('pageIndex','int');
        $result = M('knowledge_resource_file_contact')->join('knowledge_resource ON knowledge_resource.id = knowledge_resource_file_contact.resource_id AND knowledge_resource.file_type in (\'ppt\',\'word\',\'pdf\',\'swf\')')
            ->field('knowledge_resource.id,resource_path')->group('knowledge_resource.id')->page($pageIndex.','.$pageSize)->select();
        $successId = array();
        $upload = new \Oss\Ossupload();
        for($i=0;$i<sizeof($result);$i++)
        {
            if(false === strpos($result[$i]['resource_path'],'http://'))
                $remoteFilePath = C('oss_path').$result[$i]['resource_path'];
            else
                $remoteFilePath = $result[$i]['resource_path'];
            $localFileName = '/tmp/'.basename($remoteFilePath);
            $this->downloadFile($remoteFilePath,$localFileName);
            if(!is_file($localFileName))
            {
                $remoteFilePath = 'http://'.$_SERVER['SERVER_NAME'] .'/Resources/jb/'.$result[$i]['resource_path'];
                $this->downloadFile($remoteFilePath,$localFileName);
                if(!is_file($localFileName))
                {echo 'file not exists:'.$remoteFilePath;    continue;}
            }

            $jpgPath = $this->convertResource2JPG($localFileName,$remoteFilePath);
            if(false === $jpgPath)
                continue;
            else
                $ossPCCoverPath = $this->uploadOssFile($jpgPath);

            if(-1 == $ossPCCoverPath)
                continue;
            else
            {
                //generate mobile cover
                $newPath = $jpgPath.'_m.jpg';
                $cutResult = $this->reshapeImageTo1V1($jpgPath,$newPath);
                if(1 !== $cutResult)
                    continue;
                else {
                    $ossMobileCoverPath = $this->uploadOssFile($newPath);
                    //update pc cover and mobile cover
                    $data['pc_cover'] = $ossPCCoverPath;
                    $data['mobile_cover'] = $ossMobileCoverPath;
                    if(false === M('knowledge_resource')->where('id='.$result[$i]['id'])->save($data))
                    {
                        $this->showMessage(500,$result[$i]['id']."转换错误");
                    }
                    $successId[] = $result[$i]['id'];
                }
            }
        }
        $this->showMessage(200,'success',$successId);
    }
    public function remoteRefreshCover()
    {
          set_time_limit(0);
          ob_end_clean();
          ob_implicit_flush(1);

          $server = getParameter('server','str');
          $result = $this->networkRequest('http://'.$server.'/index.php?m=Admin&c=Common&a=getRefreshResourceCoverCount');
          $jsonResult =  json_decode($result);
          $count = $jsonResult->data;
          $pageSize = 1;


          for($i=0;$i<=$count/$pageSize;$i++)
          {
              $result = $this->networkRequest('http://'.$server.'/index.php?m=Admin&c=Common&a=refreshAllJBResourceCover&pageIndex='.($i+1));
              print_r($result);
              echo str_repeat(" <div></div>", 200).'<br />';
              ob_flush();
              flush();
          }

    }
    public function networkRequest($url,$data=array()){
        $ch = curl_init();
        //设置选项，包括URL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        //释放curl句柄
        curl_close($ch);
        //打印获得的数据
        //print_r($output);
        return $output;
    }
}
