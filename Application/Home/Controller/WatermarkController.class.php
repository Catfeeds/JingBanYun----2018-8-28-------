<?php
namespace Home\Controller;

use Think\Controller;
use Common\Common\CSV;
define('TOKEN','2579');
define('PAGE_NUMBER',20);
define('VIDEO_TYPE','video');
define('IMAGE_TYPE','image');

/*
 * @date    4.22
 * @desc    操作京版资源的图片和视频的水印
 */
class WatermarkController extends Controller{
    
    public $request_url;
    public $model='';
    
    
    public function __construct() {
        parent::__construct(); 
        header("Content-type: text/html; charset=utf-8");
        set_time_limit(0);
        $this->request_url = $_SERVER['SERVER_NAME'];
        $token=getParameter('token','int');
        $parameter=getParameter('par','int',false);
        if(empty($token)){
            $this->showjson(500,'访问异常');
        }
        if($token!=TOKEN){
            $this->showjson(500,'访问异常');
        }
        if(!empty($parameter)){
            if($parameter==1){
                $this->request_url='www.jingbanyun.com';
            }elseif($parameter==2){
                $this->request_url='loadresource.jingbanyun.com';
            }else{
                $this->request_url='localhost';
                //$this->request_url='localhost';
            }   
        }   
        $this->model=M('knowledge_resource');
    }
    
    /*
     * 资源图片打水印
     */
    public function jb_resourceImageWatermark(){       
        set_time_limit(0);
        ob_end_clean();
        ob_implicit_flush(1);
        $upload = new \Oss\Ossupload();
        $total_rows=$this->getDataRequest(IMAGE_TYPE);
        if($total_rows==0){
            echo '无文件需要打水印，运行结束';die;
        }
        $total_page=ceil($total_rows/PAGE_NUMBER);
        echo str_repeat("<div></div>", 200).'<br />';
        echo 'pages:'. $total_page;
        $currentCount = 1;
        $dir='E:/temp_image/';
        for($iii=0;$iii<$total_page;$iii++){
            $result=$this->getDataRequest(IMAGE_TYPE,'',1);
            $data=json_decode($result,true);

            for($j=0;$j<count($data);$j++){
                echo str_repeat("<div></div>", 200).'<br />';
                echo 'Page:'.$iii.'  Progress:'. $currentCount++ .'/'. ($total_rows);
                sleep(1);
                if($data[$j]['resource_path']==''){
                    $this->updateDataRequest(IMAGE_TYPE,$data[$j]['id'],2);
                    echo str_repeat("<div></div>", 200).'<br />';
                    echo $data[$j]['id'].'resource_path is empty';
                    sleep(1);
                    continue;
                }

                $new_file_arr=explode('/',$data[$j]['resource_path']);
                $new_file=$new_file_arr[count($new_file_arr)-1];
                $new_file=$dir.$new_file;
                $arrr=pathinfo($data[$j]['resource_path']);

                $save_file=$dir.rand(100,1000).time().'.'.$arrr['extension'];
                $status=$upload->downloadFile($data[$j]['resource_path'],$save_file);
                if($status==-1){
                    //修改水印状态
                    $this->updateDataRequest(IMAGE_TYPE,$data[$j]['id'],3);
                    echo str_repeat("<div></div>", 200).'<br />';
                    echo $data[$j]['id'].'download failed';
                    sleep(1);
                    continue;
                }
                $result=$this->uploadImage($save_file);
                $upload_data=json_decode($result,true);
                if($upload_data[0]!=0){
                    $this->updateDataRequest(IMAGE_TYPE,$upload_data[$j]['id'],4);
                    echo str_repeat("<div></div>", 200).'<br />';
                    echo $data[$j]['id'].'upload image failed';
                    sleep(1);
                    continue;
                }
                $image=$upload_data[1];
                echo str_repeat("<div></div>", 200).'<br />';
                sleep(1);
                if(($status=$this->updateDataRequest(IMAGE_TYPE,$data[$j]['id'],1,$image))!=200){
                    echo '从表id'.$data[$j]['id'].'保存数据失败'."<hr>";
                }else{
                    echo '从表id'.$data[$j]['id'].'保存数据成功'."<hr>";
                }
                @unlink($new_file);
                @unlink($save_file);
            }
        }
        echo '运行结束';
    }
    
    
    
    
    
    //图片上传
    public function uploadImage($save_file=''){  
        if($_FILES){
            set_time_limit(0);
            $upload = new \Oss\Ossupload();
            $GLOBALS['is_watermark']=1;
            $result=$upload->upload(3,$_FILES,3,0);
            echo json_encode($result);
        }else{
            $post_data['file']='@'.$save_file;
            $ch = curl_init();
            $url="http://".$this->request_url."/index.php?m=Home&c=Watermark&a=uploadImage&token=".TOKEN;
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
            return $output;
        } 
    }
    
    
    /*
     * 教师资源图片打水印
     */
    public function resourceImageWatermark(){
        set_time_limit(0);
        $upload = new \Oss\Ossupload();                     
        $total_rows=$this->getResourceDataRequest(IMAGE_TYPE);

        if($total_rows==0){
            echo '运行结束';die;
        }
        $total_page=ceil($total_rows/PAGE_NUMBER);                
        $dir='E:/temp_resource_image/';
        for($iiii=0;$iiii<($total_page);$iiii++){
            $result=$this->getResourceDataRequest(IMAGE_TYPE,'',1);
            $data=json_decode($result,true);                    
            
            
            for($j=0;$j<count($data);$j++){ 
                if($data[$j]['resource_path']==''){
                    $this->updateResourceDataRequest(IMAGE_TYPE,$data[$j]['id'],2);
                    continue;      
                }
                
                $new_file_arr=explode('/',$data[$j]['resource_path']); 
                $new_file=$new_file_arr[count($new_file_arr)-1]; 
                $new_file=$dir.$new_file; 
                $arrr=pathinfo($data[$j]['resource_path']);   
                $save_file=$dir.rand(100,1000).time().'.'.$arrr['extension']; 
                $status=$upload->downloadFile($data[$j]['resource_path'],$save_file);       
                if($status==-1){
                    //修改水印状态
                    $this->updateResourceDataRequest(IMAGE_TYPE,$data[$j]['id'],3);
                    continue; 
                }
                $result=$this->uploadImage($save_file);        
                $upload_data=json_decode($result,true);         
                if($upload_data[0]!=0){
                    $this->updateResourceDataRequest(IMAGE_TYPE,$upload_data[$j]['id'],4);
                    continue; 
                } 
                $image=$upload_data[1];             
                
                if(($status=$this->updateResourceDataRequest(IMAGE_TYPE,$data[$j]['id'],1,$image))!=200){ 
                    echo '从表id'.$data[$j]['id'].'保存数据失败'."<hr>";
                }else{
                    echo '从表id'.$data[$j]['id'].'保存数据成功'."<hr>";
                }
                @unlink($new_file);
                @unlink($save_file); 
                
            }
        }
        echo '运行结束';die;
    }
    
    
    /*
     * 京版资源视频打水印
     */
    public function jb_resourceVideoWatermark(){ 

        set_time_limit(0);
        $total_rows=$this->getDataRequest(VIDEO_TYPE);     
        $total_page=ceil($total_rows/PAGE_NUMBER);   
        $dir='E:/temp_image/';
        for($i=0;$i<count($total_page);$i++){
            $result=$this->getDataRequest(VIDEO_TYPE,'',$i+1);
            $data=json_decode($result,true);
            
            for($j=0;$j<count($data);$j++){
                if($data[$j]['vid_fullpath']==''){
                    $this->updateDataRequest(VIDEO_TYPE,$data[$j]['id'],2);
                    continue;
                }
                $new_file_arr=explode('/', $data[$j]['vid_fullpath']);
                $new_file=$new_file_arr[count($new_file_arr)-1]; 
                $new_file=$dir.$new_file; 
                $save_file=$dir.rand(100,1000).time().'.mp4';
                $status=$this->downloadFile($data[$j]['vid_fullpath'],$new_file); 
                if($status==false){
                    //修改水印状态
                    $this->updateDataRequest(VIDEO_TYPE,$data[$j]['id'],3);
                    continue;
                }  
                exec('d:/bin/ffmpeg/ffmpeg -i '.$new_file.' -qscale 5 -vf "movie=test.jpg [watermark]; [in][watermark] overlay=10:10:0.5 [out] " '.$save_file);
                //echo $new_file;
                $upload_result=$this->uploadBLWS("E:/temp_image/a.mp4");
                $upload_result=json_decode($upload_result,true);
                if($upload_result['error']!=0){
                    //修改水印状态
                    $this->updateDataRequest(VIDEO_TYPE,$data[$j]['id'],4);
                    continue;
                } 
                $vid=$upload_result[0]['vid'];
                $mp4=$upload_result[0]['mp4'];
                $vid_image_path=$upload_result[0]['images_b'];
                if(($status=$this->updateDataRequest(VIDEO_TYPE,$data[$j]['id'],1,$vid_image_path,$mp4))!=200){ 
                    echo '从表id'.$data[$j]['id'].'保存数据失败'."<hr>";
                }
                @unlink($new_file);
                @unlink($save_file);
                echo $status;die;
                die; 
            } 
        }
        echo 'finish';
        //$this->databaseRequest(1,'video','','1');
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
    
    
    /*
     * 文件上传至保利威视
     */
    public function uploadBLWS($file){  
        $url = 'http://v.polyv.net/uc/services/rest?method=uploadfile';
        $data=array('title'=>'这里是标题','tag'=>'标签','desc'=>'视频文档描述'); 
        $json_data=json_encode($data);
        $post_data = array ("writetoken" => "9c538d85-340c-466c-9e35-bb301734eb0d","JSONRPC" =>$json_data,'Filedata'=>'@'.$file);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        //打印获得的数据
        return $output;
    }
    
    
    /*
     * 获得教师数据
     */
    public function getResourceDataRequest($file_type,$all='all',$page=''){
            if($all=='all'){
                $url="http://".$this->request_url."/index.php?m=Home&c=Watermark&a=getResourceCount&token=".TOKEN.'&file_type='.$file_type;
            }else{ 
                $url="http://".$this->request_url."/index.php?m=Home&c=Watermark&a=getResourceData&token=".TOKEN.'&file_type='.$file_type.'&page='.$page;
            }       
            return $this->networkRequest($url);
    }
            
    public function getKnowledgeResourceRequest($file_type,$all='all',$index=1,$id=0)
    {
        if($all=='all'){
            $url="http://".$this->request_url."/index.php?m=Home&c=Watermark&a=getKnowledgeResourceCount&token=".TOKEN.'&file_type='.$file_type;
        }else{
            $url="http://".$this->request_url."/index.php?m=Home&c=Watermark&a=getKnowledgeResourceData&token=".TOKEN.'&file_type='.$file_type.'&index='.$index."&id=$id";
        }

            return $this->networkRequest($url);
    }
    /*
     * 获得数据
     */
    public function getDataRequest($file_type,$all='all',$page=''){      
            if($all=='all'){
                $url="http://".$this->request_url."/index.php?m=Home&c=Watermark&a=getBjresourceCount&token=".TOKEN.'&file_type='.$file_type;
            }else{ 
                $url="http://".$this->request_url."/index.php?m=Home&c=Watermark&a=getBjresourceData&token=".TOKEN.'&file_type='.$file_type.'&page='.$page;
            }     

            return $this->networkRequest($url);
    }
    
    /*
     * 修改京版数据
     */
    public function updateDataRequest($file_type,$id,$update_value,$image_file='',$vid='',$video_file=''){
        $url="http://".$this->request_url."/index.php?m=Home&c=Watermark&a=updateBjresoureWatermarkStatus&token=".TOKEN;
        $data=array();
        $data['file_type']=$file_type; 
        $data['id']=$id;
        $data['value']=$update_value;
        $data['image_path']=$image_file;
        $data['vid']=$vid;
        $data['video_file']=$video_file;    
        return $this->networkRequest($url,$data);
    }
    
    
    /*
     * 修改教师数据
     */
    public function updateResourceDataRequest($file_type,$id,$update_value,$image_file='',$vid='',$video_file=''){
        $url="http://".$this->request_url."/index.php?m=Home&c=Watermark&a=updateResoureWatermarkStatus&token=".TOKEN; 
        $data=array();
        $data['file_type']=$file_type; 
        $data['id']=$id;
        $data['value']=$update_value;
        $data['image_path']=$image_file;
        $data['vid']=$vid;
        $data['video_file']=$video_file;    
        return $this->networkRequest($url,$data);
    }
    
    //请求
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
        return $output;
    }
    
    /*
     * 得到所有京版资源需要转换的视频或图片的计数
     */
    public function getBjresourceCount(){
        $file_type=getParameter('file_type','str');
        $where['knowledge_resource.file_type']=$file_type;
        $where['watermark_status']=0;
        $result=$this->model->join('knowledge_resource_file_contact on knowledge_resource_file_contact.resource_id=knowledge_resource.id')
                    ->field('knowledge_resource_file_contact.id,knowledge_resource_file_contact.resource_path,knowledge_resource_file_contact.vid')
                    ->where($where)->group('knowledge_resource_file_contact.id')->select();
        echo count($result);
    }

    public function getKnowledgeResourceCount(){
        $file_type=getParameter('file_type','str');
        $where['knowledge_resource.file_type']=$file_type;
        $result=$this->model
            ->field('count(1) count')
            ->where($where)->find();
        echo $result['count'];
    }

    public function getKnowledgeResourceData(){
        $id = getParameter('id','int',false);
        $index=getParameter('index','int');
        $file_type=getParameter('file_type','str');
        $idWhere = '';
        if(!empty($id))
            $idWhere = " AND id=$id ";
        $result = M()->query("SELECT a.id base_id,knowledge_resource_file_contact.id,knowledge_resource_file_contact.resource_path,knowledge_resource_file_contact.vid,knowledge_resource_file_contact.vid_fullpath FROM ".
                  " ( SELECT id FROM knowledge_resource  WHERE knowledge_resource.file_type = '$file_type' $idWhere LIMIT $index,1) a JOIN knowledge_resource_file_contact ON knowledge_resource_file_contact.resource_id = a.id");
        echo json_encode($result);
    }

    /*
     * 得到京版资源需要转换的视频或图片的数据
     */
    public function getBjresourceData(){
        $page=getParameter('page','int');
        $file_type=getParameter('file_type','str');
        $where['knowledge_resource.file_type']=$file_type;
        $where['watermark_status']=0;
        $start_rows=($page-1)*PAGE_NUMBER;
        $result=$this->model->join('knowledge_resource_file_contact on knowledge_resource_file_contact.resource_id=knowledge_resource.id')
                    ->field('knowledge_resource.id base_id,knowledge_resource_file_contact.id,knowledge_resource.file_type type,knowledge_resource_file_contact.resource_path,knowledge_resource_file_contact.vid,knowledge_resource_file_contact.vid_fullpath')
                    ->where($where)->group('knowledge_resource_file_contact.id')->limit($start_rows.','.PAGE_NUMBER)->select();
        echo json_encode($result);
    }
    
    
    /*
     * 得到所有教师资源需要转换的视频和图片的计数
     */
    public function getResourceCount(){
        $file_type=getParameter('file_type','str');
        $where['biz_resource.type']=$file_type;
        $where['watermark_status']=0;
        $result=M('biz_resource')->join('biz_resource_contact on biz_resource_contact.biz_resource_id=biz_resource.id')
                    ->field('biz_resource_contact.id,biz_resource_contact.resource_path,biz_resource_contact.vid')
                    ->where($where)->group('biz_resource_contact.id')->select();                     
        echo count($result);
    }
    
    
    /*
     * 得到教师资源需要转换的视频或图片的数据
     */
    public function getResourceData(){
        $page=getParameter('page','int');
        $file_type=getParameter('file_type','str');
        $where['biz_resource.type']=$file_type;
        $where['watermark_status']=0;
        $start_rows=($page-1)*PAGE_NUMBER;
        $result=M('biz_resource')->join('biz_resource_contact on biz_resource_contact.biz_resource_id=biz_resource.id')
                    ->field('biz_resource_contact.id,biz_resource.type,biz_resource_contact.resource_path,biz_resource_contact.vid')
                    ->where($where)->group('biz_resource_contact.id')->limit($start_rows.','.PAGE_NUMBER)->select();   
        echo json_encode($result);
    }
    
    
    /*
     * 修改京版资源水印状态
     */
    public function updateBjresoureWatermarkStatus(){    
        $model=M('knowledge_resource_file_contact');
        $file_type=getParameter('file_type','str'); 
        $data['watermark_status']=getParameter('value','int');
        $where['id']=getParameter('id','int');
        
        $image_path=getParameter('image_path','str',false);
        $vid=getParameter('vid','int',false);
        $video_file=getParameter('video_file','str',false);
        
        if($file_type==VIDEO_TYPE){
            if(!empty($image_path)){
                $data['vid_image_path']=$image_path;
                $data['vid']=$vid;
                $data['vid_fullpath']=$video_file;
            }  
        }else{
            if(!empty($image_path)){
                $data['watermark_img']=$image_path;
                $ext = pathinfo($image_path, PATHINFO_EXTENSION);
                $data['resource_path']=substr($image_path,0,-1*(1+mb_strlen($ext))).'_w.'.$ext;
            } 
        } 
        if($model->where($where)->save($data)===false){
            echo 500;
        }else{
            echo 200;
        } 
    }
    
    
    /*
     * 修改教师资源水印状态
     */
    public function updateResoureWatermarkStatus(){     
        $model=M('biz_resource_contact');
        $file_type=getParameter('file_type','str'); 
        $data['watermark_status']=getParameter('value','int');
        $where['id']=getParameter('id','int');
        
        $image_path=getParameter('image_path','str',false);
        $vid=getParameter('vid','int',false);
        $video_file=getParameter('video_file','str',false);
        
        if($file_type==VIDEO_TYPE){
            if(!empty($image_path)){
                $data['vid_image_path']=$image_path;
                $data['vid']=$vid;
                $data['vid_fullpath']=$video_file;
            }  
        }else{
            if(!empty($image_path)){
                $data['watermark_img']=$image_path;
                $ext = pathinfo($image_path, PATHINFO_EXTENSION);
                $data['resource_path']=substr($image_path,0,-1*(1+mb_strlen($ext))).'_w.'.$ext;
            } 
        }
        if($model->where($where)->save($data)===false){
            echo 500;
        }else{
            echo 200;
        } 
    }
    private function reshapeImageTo1V1($imgPath)
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
            case 1:imagegif($image,$imgPath);break;
            case 2:imagejpeg($image,$imgPath);break;
            case 3:imagepng($image,$imgPath);break;
            default:return $errorMsg;
        }
        return 1;
    }
    public function getAllMobileCover()
    {
        $allResult = M('knowledge_resource')->field('id,mobile_cover')->select();
        $this->showMessage(200,'success',$allResult);
    }
    public function updateCoverPath()
    {
        $model=M('knowledge_resource');
        $data['mobile_cover']=getParameter('url','str');
        $where['id']=getParameter('id','int');
        if($model->where($where)->save($data)===false){
            echo 500;
        }else{
            echo 200;
        }
    }


    public function updatePCAPPCoverPath()
    {
        $model=M('knowledge_resource');
        $mobileCover = getParameter('mobile_cover','str',false);
        $data = [];
        if(!empty($mobileCover))
        $data['mobile_cover'] = $mobileCover;

        $pcCover = getParameter('pc_cover','str',false);
        if(!empty($pcCover))
            $data['pc_cover'] = $pcCover;
        $where['id']=getParameter('id','int');
        if($model->where($where)->save($data)===false){
            echo 500;
        }else{
            echo 200;
        }
    }

    /*
     * 修改教师数据
     */
    public function updateResourceMobileCoverRequest($serverName,$path,$id){
        $url="http://".$serverName."/index.php?m=Home&c=Watermark&a=updateCoverPath&token=".TOKEN;
        $data=array();
        $data['url']=$path;
        $data['id']=$id;
        return $this->networkRequest($url,$data);
    }

    public function updateResourceCoverRequest($serverName,$pcPath,$appPath,$id){
        $url="http://".$serverName."/index.php?m=Home&c=Watermark&a=updatePCAPPCoverPath&token=".TOKEN;
        $data=array();
        $data['pc_cover']=$pcPath;
        $data['mobile_cover']=$appPath;
        $data['id']=$id;
        return $this->networkRequest($url,$data);
    }

    public function refreshMobileCover()
    {
        set_time_limit(0);
        ob_end_clean();
        ob_implicit_flush(1);
        $serverName = getParameter('server','str');
        $url="http://".$serverName."/index.php?m=Home&c=Watermark&a=getAllMobileCover&token=".TOKEN;
        $result = $this->networkRequest($url);
        $result = json_decode($result);
        $allResult = $result->data;

        for($i=0;$i<sizeof($allResult);$i++){
          //download pic
          if('' == $allResult[$i]->mobile_cover)
              continue;
            echo str_repeat(" <div></div>", 200).'<br />';
            echo "\n".$allResult[$i]->id . ' '.$i.'/'.sizeof($allResult);
            ob_flush();
            flush();//break;
            //sleep(1);
          $url = (false === strpos($allResult[$i]->mobile_cover,'http://')) ? C('oss_path').$allResult[$i]->mobile_cover:$allResult[$i]->mobile_cover;
          $ext = pathinfo($url, PATHINFO_EXTENSION);
          $newFileName = 'f:/imgs/oldmobile.'.$ext;
            echo str_repeat(" <div></div>", 200).'<br />';
            echo "\n".'download file';
            ob_flush();
            flush();//break;
          $downloadStatus = $this->downloadFile($url,$newFileName);
          if($downloadStatus === -1)
          {
              echo str_repeat(" <div></div>", 200).'<br />';
              echo "\n".'download failed';
              ob_flush();
              flush();//break;
              continue;
          }
          if(!is_file($newFileName))
          {
              echo str_repeat(" <div></div>", 200).'<br />';
              echo "\n".'file not exists failed';
              ob_flush();
              flush();//break;
              continue;
          }
            echo str_repeat(" <div></div>", 200).'<br />';
            echo "\n".'reshaping file';

          if(2 == $this->reshapeImageTo1V1($newFileName))
          {
              echo str_repeat(" <div></div>", 200).'<br />';
              echo "\n".'ignore shape';
              ob_flush();
              flush();//break;
              continue;
          }
          //upload to oss


          $GLOBALS['is_watermark']=0;
          $file['file']['name'] =  $file['file']['tmp_name'] = $newFileName;
          $file['file']['type'] =  'image/png';
          $failCount = 0;
       tryAgain:
          try {
              echo str_repeat(" <div></div>", 200).'<br />';
              echo "\n".'uploading file';
              ob_flush();
              flush();//break;

              $result=$this->uploadImage($newFileName);
              $upload_data=json_decode($result,true);
              $imagePath=$upload_data[1];

              if ($result[0] != 0) {
                  echo str_repeat(" <div></div>", 200) . '<br />';
                  echo "\n" . 'upload failed';
                  ob_flush();
                  flush();//break;
                  continue;
              }
          }catch(\Exception $e)
          {
              if(++$failCount == 3)
              {
                 echo str_repeat(" <div></div>", 200) . '<br />';
                 echo "\n" . 'upload 3 times failed';
                  ob_flush();
                  flush();//break;
                 continue;
              }
              print $e->getMessage();
              goto tryAgain;
          }
            unlink($newFileName);
          //update mobile cover
            if(($status=$this->updateResourceMobileCoverRequest($serverName,$imagePath, $allResult[$i]->id))!=200){
                echo 'id'.$allResult[$i]->id.'保存数据失败'."<hr>";
            }
        }
        echo "\n".'finished';
        ob_flush();
        flush();//break;

    }
    //获取目标、源图像裁剪起始XY与宽高
    private function getSourceDestXY($currentImageIndex,$destWidth,$destHeight,$sourceWidth,$sourceHeight,$isFloor = 0)
    {
      $destInfo = [];
      $sourceInfo = [];
      $margin = 5.0;
      $currentDestImageWidth = $destWidth/2;
      $currentDestImageHeight = $destHeight/2;
      $whRatio = floatval($currentDestImageWidth) / $currentDestImageHeight;

      if($sourceHeight * $whRatio > $sourceWidth){
          //base on width
          $cutHeight = $sourceWidth / $whRatio;
          $sourceInfo['x'] = 0;
          $sourceInfo['y'] = (floatval($sourceHeight) - $cutHeight ) /2;
          $sourceInfo['width'] = $sourceWidth;
          $sourceInfo['height'] = $cutHeight;
      }
      else
      {
          $sourceInfo['y'] = 0;
          $cutWidth = $sourceHeight * $whRatio;
          $sourceInfo['x'] = (floatval($sourceWidth) - $cutWidth ) /2;
          $sourceInfo['height'] = $sourceHeight;
          $sourceInfo['width'] = $cutWidth;
      }
      //层叠式
      if(0 == $isFloor){
          $destInfo['x'] = ($currentImageIndex % 2) * $destWidth / 2 + ($currentImageIndex % 2) * $margin;
          $destInfo['y'] = intval($currentImageIndex / 2) * $destHeight / 2 + intval($currentImageIndex / 2) * $margin;
          $destInfo['width'] = $destWidth / 2 - $margin / 2;
          $destInfo['height'] = $destHeight / 2 - $margin / 2;
      }
      else
      {
          $destInfo['x'] = $destWidth/2 - (intval($currentImageIndex)) * ($destWidth/6) ;
          $destInfo['y'] = $currentImageIndex * ($destHeight/6) ;
          $destInfo['width'] = $destWidth / 2 ;
          $destInfo['height'] = $destHeight / 2 ;
      }


      return [$destInfo,$sourceInfo];
    }

    private function generateCover($currentImageIndex,$imgPath,$allImageCount,&$canvas)
    {
        $pcSize = [400,266];
        $appSize = [200,200];
        $new_file_arr=explode('/', $imgPath);
        $newFileName=$new_file_arr[count($new_file_arr)-1];
        $localFileName = $_SERVER['DOCUMENT_ROOT'].'/tmp/'.$newFileName;
        A('Home/Watermark')->downloadFile($imgPath,$localFileName);
        list($sourceWidth,$sourceHeight,$format) = getimagesize($imgPath);
        switch($format)//取得背景图片的格式
        {
            case 1:$ground_im = imagecreatefromgif($imgPath);break;
            case 2:$ground_im = imagecreatefromjpeg($imgPath);break;
            case 3:$ground_im = imagecreatefrompng($imgPath);break;
            default:return false;
        }
        if(empty($canvas)){
            $canvas['PC'] = imagecreatetruecolor($pcSize[0], $pcSize[1]);
            $canvas['APP'] = imagecreatetruecolor($appSize[0], $appSize[1]);
            $bg =imagecolorallocate($canvas['PC'] , 234, 234, 234);
            imagefill($canvas['PC'] , 0, 0, $bg);
            $bg =imagecolorallocate($canvas['APP'] , 234, 234, 234);
            imagefill($canvas['APP'] , 0, 0, $bg);
        }
        switch($allImageCount){
            //1张图
            case 1:imagecopyresized($canvas['PC'], $ground_im, 0,0, 0, 0, $pcSize[0],$pcSize[1], $sourceWidth,$sourceHeight);
                    imagecopyresized($canvas['APP'], $ground_im, 0,0, 0, 0, $appSize[0],$appSize[1], $sourceWidth,$sourceHeight);
                    break;
            //2 3张图
            case 2:
            case 3:
                if(0 == $currentImageIndex) {
                imagecopyresized($canvas['PC'], $ground_im, 0, 0, 0, 0, $pcSize[0], $pcSize[1], $sourceWidth, $sourceHeight);
                imagecopyresized($canvas['APP'], $ground_im, 0, 0, 0, 0, $appSize[0], $appSize[1], $sourceWidth, $sourceHeight);
                }
                    break;
            //4张图
            case 4:
                    list($destInfo,$sourceInfo) = $this->getSourceDestXY($currentImageIndex,$pcSize[0],$pcSize[1],$sourceWidth,$sourceHeight);
                    imagecopyresized($canvas['PC'], $ground_im, $destInfo['x'],$destInfo['y'], $sourceInfo['x'], $sourceInfo['y'], $destInfo['width'],$destInfo['height'], $sourceInfo['width'],$sourceInfo['height']);
                    list($destInfo,$sourceInfo) = $this->getSourceDestXY($currentImageIndex,$appSize[0],$appSize[1],$sourceWidth,$sourceHeight,1);
                    imagecopyresized($canvas['APP'], $ground_im, $destInfo['x'],$destInfo['y'], $sourceInfo['x'], $sourceInfo['y'], $destInfo['width'],$destInfo['height'], $sourceInfo['width'],$sourceInfo['height']);
                    break;
            default:break;
        }
        unlink($localFileName);
        return true;
    }

    public function refreshKnowledgeCover()
    {
        $id = getParameter('id','int',false);
        if(empty($id))
         $total_rows = $this->getKnowledgeResourceRequest(IMAGE_TYPE);
        else
         $total_rows = 1;
        for($i=0;$i<$total_rows;$i++) {
            if(empty($id))
             $result = $this->getKnowledgeResourceRequest(IMAGE_TYPE, '', $i);
            else
             $result = $this->getKnowledgeResourceRequest(IMAGE_TYPE, '', 0,$id);
            $contactData=json_decode($result,true);
            $fileCount = sizeof($contactData);
            $minCount = min($fileCount,4);
            $canvas = [];
            $localDestImagePath = ['PC'=>$_SERVER['DOCUMENT_ROOT'].'/tmp/'.time().rand(10000,99999).'.jpg','APP'=>$_SERVER['DOCUMENT_ROOT'].'/tmp/'.time().rand(10000,99999).'.jpg'];
            for($i=0;$i<$minCount;$i++) {
                $imgPath =  OSS_URL.$contactData[$i]['resource_path'];
                $genResult = $this->generateCover($i,$imgPath,$minCount,$canvas);
                if(!$genResult) //生成失败
                {

                }
             }
            $remoteFilePath = [];
            foreach($canvas as $key=>$val){
                imagejpeg($val,$localDestImagePath[$key]);
                $result=$this->uploadImage($localDestImagePath[$key]);
                $upload_data=json_decode($result,true);

                if ($result[0] != 0) { //上传失败
                    echo str_repeat(" <div></div>", 200) . '<br />';
                    echo "\n" . 'upload failed';
                    ob_flush();
                    flush();//break;
                    continue;
                }

                $remoteFilePath[$key] = $image=$upload_data[1];
            }
            if(sizeof($remoteFilePath) < 2) //1 fail at least
            {
                echo 'id'.$contactData[0]['base_id'].'至少有1个文件上传失败';continue;
            }
            $result = $this->updateResourceCoverRequest($this->request_url,$remoteFilePath['PC'],$remoteFilePath['APP'],$contactData[0]['base_id']);
            if($result != 200){
                echo 'id'.$contactData[0]['base_id'].'保存数据失败'."<hr>";
            }else{
                echo 'id'.$contactData[0]['base_id'].'保存数据成功'."<hr>";
            }
        }

    }
}