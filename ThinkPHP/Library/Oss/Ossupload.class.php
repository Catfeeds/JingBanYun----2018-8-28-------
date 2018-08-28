<?php 

namespace Oss;
require 'src/OSS/OssClient.php';
require 'autoload.php';
import('ORG.Util.Image');
require 'Core/MimeTypes.php';
class Ossupload 
{   
    const accessKeyId = 'LTAItpuhTlgicIVf';
    const accesKeySecret = 'UzkBrw5CAWWBYJDV8fLt3fAg3SnWtS';
    const endpoint = 'oss-cn-beijing.aliyuncs.com';
    const bucket = 'jbyoss';
    //返回错误信息 
    public $error=0;    
    
    public static function get_oss_client() {   
        $oss = new OssClient(self::accessKeyId, self::accesKeySecret, self::endpoint);  
        return $oss;
    }
    
    public static function get_bucket_name() {
        return self::bucket;
    }
    
    function random($length, $chars = '0123456789') {
        $hash = '';
        $max = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }
    
    //返回时间加随机数
    public function randomfilename(){ 
        $result = $this->random(10, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
        $result = date("Ymdhis").$result;
        return $result;
    }
    
    //创建文件夹
    public function dirfile($object){           
        $ossClient = self::get_oss_client();
        $file_status=$ossClient->doesObjectExist(self::get_bucket_name(),$object);         
        if($file_status==200){
                
        }elseif($file_status==404){
            //创建目录
            $ossClient->createObjectDir(self::get_bucket_name(),$object); 
        }else{
            $object=false;
        }  
        return $object;
    }
    
    /*
     * 生成目录
    * $config['upload_path'] = array('image/1/','image/2/'); 
    */
    public function uploadfilepath($flag){    
        $pathurl='';    
        if($flag!=3){
            $pathurl = date('Y-m-d');                //文件路径时间部分
        }
        $upload_path = C('upload_path');                //路径配制部分
        $upload_path = $upload_path[$flag].$pathurl;    //合成统一路径 
    
        $object = $this->dirfile($upload_path);     
        if ($object==false){   
            $this->_error = 5;
        }else{
            //连接/ 新版接口中Oss会在文件后面有/会创建一个空目录
            if($flag!=3){
                $object=$object.'/';
            }
        }
        return $object;
    }
    
    
    //上传文件
    /*
     *文件单个上传和多个上传
    * $file_type 1为图片 2为媒体文件   3为支持全部文件
    *$files POST的内容
    *$filesize 图片限制大小
    * $updateFileName_tag 为0修改文件名,否则就是原文件名字加时间戳
    *上传指定变量的内存值
    *返回 1  上传图片失败,不支持上传该类型的图片   
    *2 '上传图片失败,只允许小于'.$filesize.'kb以下的图片' 
    *3 $_FILES没有内容 4没有上传到服务器$files['file']['tmp_name']  
    *5文件没有创建成功
    * 
    * //$is_watermark换成了一个相对目录
    * flag为4就用相对目录(含有文件名)
   */ 
    public function upload($file_type,$files,$flag,$relative_path='',$updateFileName_tag=0,$filesize=0){
            $object = null;
            if ($flag == '' or $flag > 20){
                    $flag=C('default_dir');
            }  
            $path = $this->uploadfilepath($flag-1);             
            if($flag==4){
                $path=$path.$relative_path;
            }   
            
            if ($path != false){    
                if($file_type==1){
                    $file_type=C('allowed_types');
                    if($filesize==0){
                        $filesize=C('file_size')*1024*1024;
                    }else{
                        $filesize=$filesize*1024*1024;
                    }

                }elseif($file_type==2){
                    $file_type=C('media_allowed_types');
                    if($filesize==0){
                        $filesize=C('media_file_size')*1024*1024;
                    }else{
                        $filesize=$filesize*1024*1024;
                    }

                }elseif($file_type==3){
                    $file_type=C('allow_allowed_types');   
                    if($filesize==0){
                        $filesize=C('all_file_size')*1024*1024;
                    }else{
                        $filesize=$filesize*1024*1024;
                    }   
                }else{
                    $arr[0]=0;
                    $arr[1]='没有此类型';
                    return $arr;
                }

                if(is_array(@$files['file']['name'])){
                    $imgurl = '';
                    $error = 0; 
                    $message='';
                    foreach ($files['file']['name'] as $key=>$val){   
                        //等于4就是路径(目录和文件名)
                        if($flag==4){
                            $object = $path.strrchr($val, '.');
                        }else{
                            if($updateFileName_tag==0){
                                $randomfilename = $this->randomfilename();
                            }else{
                                $end_index = strrpos($files['file']['name'][$key],'.');
                                $randomfilename=substr($files['file']['name'][$key], 0, $end_index);
                                $randomfilename=time().'_'.$randomfilename;
                            }
                            $object = $path.$randomfilename.strrchr($val, '.');
                        }
                        if($files['file']['type'][$key]!=''){
                            if(!in_array($files['file']['type'][$key], $file_type)){ 
                                
                                $error = 1;
                                $message.=$files['file']['name'][$key].'的文件格式为'.$files['file']['type'][$key].'不支持此类型的文件,';

                            }else if($files['file']['size'][$key]>$filesize){  
                                $error = 2;
                                $message.=$files['file']['name'][$key].'文件过大,';
                            }else{  
                                $imgurl.=$object.',';
                                $allowed_image_types=C('allowed_types');
                                if($GLOBALS['is_watermark'] && in_array($files['file']['type'][$key], $allowed_image_types)){      
                                    $this->aliupload($files['file']['tmp_name'][$key], $object);
                                    $message .= $files['file']['name'][$key] . '上传成功,';
                                    
                                    $watermark_arr=explode('.',$object);
                                    $watermark_image_name=$watermark_arr[count($watermark_arr)-2].'_w'.'.'.$watermark_arr[count($watermark_arr)-1];  
            
                                    $msg=$this->imageWaterMark($files['file']['tmp_name'][$key],9,'./big_watermark.png');    
                                    if($msg!=''){
                                        $error = 3;
                                        $message.=$files['file']['name'][$key].'水印相关信息.'.$msg;
                                    }else{
                                        $this->aliupload($files['file']['tmp_name'][$key], $watermark_image_name); 
                                    } 
                                }else { 
                                    $this->aliupload($files['file']['tmp_name'][$key], $object);
                                    $message .= $files['file']['name'][$key] . '上传成功,';
                                }
                            }
                        } 
                        if ($error!=0){
                            $this->_error = $error;
                            $imgurl='';
                            $object='';
                            break;
                        }
                    }
                    $object = $imgurl; 
                }else{
                    //路径部分结束
                    $object='';     
                    if ($files){
                        if($flag == 6)
                        {
                            $object = $relative_path;
                        }
                        else if($flag==4){
                            $object = $path.strrchr($files['file']['name'], '.');
                        }else{
                            if($updateFileName_tag==0){
                                $randomfilename = $this->randomfilename();
                            }else{
                                $end_index = strrpos($files['file']['name'],'.');
                                $randomfilename=substr($files['file']['name'], 0, $end_index);
                                $randomfilename=time().'_'.$randomfilename;
                            } 
                            $object = $path.$randomfilename.strrchr($files['file']['name'], '.');      
                        }

                        
                        if(!in_array($files['file']['type'], $file_type)){ 
                            $this->_error = 1;
                            $object='';
                            $message=$files['file']['name'].'的文件格式为'.$files['file']['type'].'不支持此类型的文件'; 

                        }else if($files['file']['size']>$filesize){
                           $this->_error = 2;
                           $object='';
                           $message=$files['file']['name'].'此文件过大';

                        }else{
                            $allowed_image_types=C('allowed_types');
                            $obj = new Core\MimeTypes();
                            $currentMimeType =  $obj->getMimetype($files['file']['name']);
                            if($GLOBALS['is_watermark'] && in_array($currentMimeType, $allowed_image_types)){
                                $this->aliupload($files['file']['tmp_name'], $object);
                                $message=$files['file']['name'].'上传成功'; 
                                    
                                $watermark_arr=explode('.',$object);
                                $watermark_image_name=$watermark_arr[count($watermark_arr)-2].'_w'.'.'.$watermark_arr[count($watermark_arr)-1];

                                $msg=$this->imageWaterMark($files['file']['tmp_name'],9,'./big_watermark.png');

                                if($msg!=''){
                                    $this->_error = 3;
                                    $object='';
                                    $message=$files['file']['name'].'水印相关信息.'.$msg;
                                }else{
                                    $this->aliupload($files['file']['tmp_name'],$watermark_image_name); 
                                }
                            }else{
                                $this->aliupload($files['file']['tmp_name'],$object);
                                $object=$object.',';
                                $message=$files['file']['name'].'上传成功';
                            }

                        }  
                    }else{
                        $this->_error = 4;
                        $object='';
                        $message='上传文件为空';
                    }   
                } 
            }
            if ($this->_error){
                $arr[0]=$this->_error;
                $arr[1]=$object;  
                $arr[2]=$message;
            }else{
                $arr[0]=0;
                $arr[1]=$object;
                $arr[2]=$message;
            }             

            if($arr[1] !="")
             {
              if($arr[1][strlen($arr[1])-1] == ',')
               $arr[1] = substr($arr[1],0,-1);
             } 
            return $arr; 
    }
    
    /*
     * 视频运行文件上传
     * @param   file   上传文件路径
     * @param   upload_path 上传oss的路径(包含所有目录的路径)  
     */
    public function videoH264Upload($file,$upload_path){
        $this->aliupload($file, $upload_path);
    }
    
    //阿里云上传文件
    public function aliupload($tmpname,$object){
        $ossClient = self::get_oss_client(); 
        //$result=$ossClient->doesBucketExist(self::get_bucket_name());     
        $data=$ossClient->uploadFile(self::get_bucket_name(),$object,$tmpname);
        /*if($result==true){
            $data=$ossClient->uploadFile(self::get_bucket_name(),$object,$tmpname); 
        }*/
    }
    
    
    /*
     * 下载文件到本地目录
     * @param $file_path oss文件路径
     * @param 保存到本地的文件路径
     */
    public function downloadFile($file_path,$files){    
        $ossClient = self::get_oss_client();     
        $file_status=$ossClient->doesObjectExist(self::get_bucket_name(),$file_path); 
        if($file_status==200){
            $options=array(
                OssClient::OSS_FILE_DOWNLOAD=>$files
            );
            $data=$ossClient->getObject(self::get_bucket_name(),$file_path,$options);       
            return $data;
        }else{
            return -1;
        }
    }
    
    
    /*删除文件 支持单个文件和多个文件(不支持文件夹)
     * $files可以是一个字符串(文件路径)或者是一个一维数组(文件路径为值)
     */
    public function deleteFile($files){
        $ossClient = self::get_oss_client();
        $result=$ossClient->doesBucketExist(self::get_bucket_name());      
        if($result==true){
            if(is_array($files)){
                 $data=$ossClient->deleteObjects(self::get_bucket_name(),$files);
            }else{
                $ossClient->deleteObject(self::get_bucket_name(),$files);
            }
        }
    }
    
    /*function imageWaterMark($groundImage,$waterPos=0,$waterImage="",$waterText="",$textFont=5,$textColor="#FF0000"){ 
        
        if(!empty($groundImage) && file_exists($groundImage))     
        {     
          $ground_info = getimagesize($groundImage);     
          $ground_w   = $ground_info[0];//取得背景图片的宽     
          $ground_h   = $ground_info[1];//取得背景图片的高     

          switch($ground_info[2])//取得背景图片的格式     
          {     
              case 1:$ground_im = imagecreatefromgif($groundImage);$watermark_complete_image='water.gif';break;     
              case 2:$ground_im = imagecreatefromjpeg($groundImage);$watermark_complete_image='water.jpg';break;     
              case 3:$ground_im = imagecreatefrompng($groundImage);$watermark_complete_image='water.png';break;     
              default:return $formatMsg;
          } ;
            $image = new \Think\Image(); 
            $image->open($groundImage)->water($waterImage,\Think\Image::IMAGE_WATER_SOUTHEAST)->save($watermark_complete_image);
            return $watermark_complete_image;
        }
              
    }*/
    
    
    //图片打水印
    //$this->imageWaterMark($files['file']['tmp_name'],9,'./image/test.png');
    function imageWaterMark($groundImage,$waterPos=0,$waterImage="",$waterText="",$textFont=5,$textColor="#FF0000")
    { 
      $isWaterImage = FALSE;     
      $formatMsg = "暂不支持该文件格式，请用图片处理软件将图片转换为GIF、JPG、PNG格式。";
        //echo file_get_contents($waterImage);
      //读取水印文件
      if(!empty($waterImage) && file_exists($waterImage))     
      { 
        $isWaterImage = TRUE;     
        $water_info = getimagesize($waterImage);    
    	
        $water_w   = $water_info[0];//取得水印图片的宽     
        $water_h   = $water_info[1];//取得水印图片的高     
    
        switch($water_info[2])//取得水印图片的格式     
        {     
            case 1:$water_im = imagecreatefromgif($waterImage);$thumb_image='thumb.gif';break;     
            case 2:$water_im = imagecreatefromjpeg($waterImage);$thumb_image='thumb.jpg';break;
            case 3:$water_im = imagecreatefrompng($waterImage);$thumb_image='thumb.png';break;
            default:return $formatMsg;
        }     
      }
           // imagepng($water_im);exit;
      //读取背景图片
     
      if(!empty($groundImage) && file_exists($groundImage))     
      {     
        $ground_info = getimagesize($groundImage);     
        if(!$ground_info){
            return $formatMsg;
        }
        $ground_w   = $ground_info[0];//取得背景图片的宽     
        $ground_h   = $ground_info[1];//取得背景图片的高     
    	  
        switch($ground_info[2])//取得背景图片的格式     
        {     
            case 1:$ground_im = imagecreatefromgif($groundImage);break;     
            case 2:$ground_im = imagecreatefromjpeg($groundImage);break;
            case 3:$ground_im = imagecreatefrompng($groundImage);
                $simg = imagecreatetruecolor($ground_w, $ground_h);
                $bg = imagecolorallocatealpha($simg, 0, 0, 0, 127);
                imagefill($simg, 0, 0, $bg);
                imagecopyresized($simg, $ground_im, 0, 0, 0, 0,$ground_w, $ground_h, $ground_w, $ground_h);
                imagealphablending($simg, false);
                imagesavealpha($simg,true);
                imagepng($simg,$groundImage.'.png');imagedestroy($ground_im);$ground_im = imagecreatefrompng($groundImage.'.png');break;
            default:return $formatMsg;
        }     
      }     
      else    
      {     
        return "需要加水印的图片不存在！";
      }     

      //水印位置     
      if($isWaterImage)//图片水印     
      {     
        $w = $water_w;     
        $h = $water_h;   
    	
        $label = "图片的";     
      }     
      else//文字水印     
      {     
        $temp = imagettfbbox(ceil($textFont*5),0,"./cour.ttf",$waterText);//取得使用 TrueType 字体的文本的范围  
    	
        $w = $temp[2] - $temp[6];     
        $h = $temp[3] - $temp[7];     
        unset($temp);     
        $label = "文字区域";     
      }    
            
      
      
      switch($waterPos)     
      {     
        case 0://随机     
            $posX = rand(0,($ground_w - $w));     
            $posY = rand(0,($ground_h - $h));     
            break;     
        case 1://1为顶端居左     
            $posX = 0;     
            $posY = 0;     
            break;     
        case 2://2为顶端居中     
            $posX = ($ground_w - $w) / 2;     
            $posY = 0;     
            break;     
        case 3://3为顶端居右     
            $posX = $ground_w - $w;     
            $posY = 0;     
            break;     
        case 4://4为中部居左     
            $posX = 0;     
            $posY = ($ground_h - $h) / 2;     
            break;     
        case 5://5为中部居中     
            $posX = ($ground_w - $w) / 2;     
            $posY = ($ground_h - $h) / 2;     
            break;     
        case 6://6为中部居右     
            $posX = $ground_w - $w;     
            $posY = ($ground_h - $h) / 2;     
            break;     
        case 7://7为底端居左     
            $posX = 0;     
            $posY = $ground_h - $h;     
            break;     
        case 8://8为底端居中     
            $posX = ($ground_w - $w) / 2;     
            $posY = $ground_h - $h;     
            break;     
        case 9://9为底端居右     
            $posX = $ground_w - $w - 4;
            $posY = $ground_h - $h - 4;
            break;
        default://随机     
            $posX = rand(0,($ground_w - $w));     
            $posY = rand(0,($ground_h - $h));     
            break;       
      }  


       //视频命令ffmpeg -i intro.mp4 -qscale 5 -vf "movie=red_logo.png  [watermark]; [in][watermark] overlay=10:10:0.5 [out]" test.mp4
      //设定图像的混色模式     
      imagealphablending($water_im,true);
      
      if($isWaterImage)//图片水印     
      {     
        //imagecopy($ground_im, $water_im, $posX, $posY, 0, 0, $water_w,$water_h);//拷贝水印到目标文件
        //按照宽度八比一 款和高给的图比例是3:1     宽最小为20,高最小为10;
        $oldW =  $water_w;
        $water_w=ceil($ground_w/5);
        $water_h=ceil($water_h*($water_w/$oldW));
        if($water_h > $ground_h)
        {
            $oldH = $water_h;
            $water_h = $ground_h /3;
            $water_w = $water_w*($water_h/$oldH);
        }

            $src = imagecreatetruecolor($water_w, $water_h);

            $color = imagecolorallocatealpha($src, 0, 0, 0, 127);
            imagecolortransparent($src,$color);

            imagefill($src, 0, 0, $color);
            imagealphablending($src,true);

            imagecopyresampled($src, $water_im, 0, 0, 0, 0, $water_w, $water_h, $w, $h);
            imagecolortransparent($src,$color);

            $water_w = imagesx($src);
            $water_h = imagesy($src);
            $wStep = $ground_w * 0.1;
            $hStep = $ground_h * 0.1;

            for($x = 0;$x < $ground_w;$x+= $water_w + $wStep){
                for($y = $x-($water_h+$hStep)*$x;$y < $ground_h;$y+= $water_h + $hStep)
                {
                    //igonre untransparent area
                    $xyArray =array(
                        array('x'=>$x,'y'=>$y),
                        array('x'=>$x+$water_w,'y'=>$y),
                        array('x'=>$x+$water_w,'y'=>$y+$water_h),
                        array('x'=>$x,'y'=>$y+$water_h)
                    );
                    $bTransparent = false;
                    foreach($xyArray as $index => $data) {
                        if($data['x'] > $ground_w || $data['y'] > $ground_h){
                            $bTransparent = true;break;
                        }
                        $rgb = imagecolorat($ground_im, $data['x'], $data['y']);
                        $colors = imagecolorsforindex($ground_im, $rgb);
                        if ($colors['alpha'] == 127) {
                            $bTransparent = true;break;
                        }
                    }
                    if($bTransparent)continue;
                    imagecopy($ground_im, $src, $x,$y, 0, 0, $water_w, $water_h);
                }
            }
            imagesavealpha($ground_im, true);


      }     
      else//文字水印     
      {     
        if( !empty($textColor) && (strlen($textColor)==7) )     
        {     
            $R = hexdec(substr($textColor,1,2));     
            $G = hexdec(substr($textColor,3,2));     
            $B = hexdec(substr($textColor,5));     
        }     
        else    
        {
            return "水印文字颜色格式不正确！";
        }     
        imagestring ( $ground_im, $textFont, $posX, $posY, $waterText, imagecolorallocate($ground_im, $R, $G, $B));           
      }     
    
      //生成水印后的图片
        unlink($groundImage.'.png');
        @unlink($groundImage);
      //@unlink($thumb_image);
      
      switch($ground_info[2])//取得背景图片的格式     
      {     
        case 1:imagegif($ground_im,$groundImage);break;     
        case 2:imagejpeg($ground_im,$groundImage);break;     
        case 3:imagepng($ground_im,$groundImage);break;     
        default:return $errorMsg;
      }     
    
      //释放内存     
      if(isset($water_info)) unset($water_info);     
      if(isset($water_im)) imagedestroy($water_im);     
      unset($ground_info);   
      imagedestroy($src); 
      imagedestroy($water_im); 
      imagedestroy($ground_im);     
    } 
}