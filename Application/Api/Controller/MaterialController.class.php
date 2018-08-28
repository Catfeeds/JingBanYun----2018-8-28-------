<?php
namespace Api\Controller;
use Think\Controller;

class MaterialController extends Controller
{ 
    public $model='';
    public $page_size=20;
    public $firstRow=0;
    public $listRow=0;
            
    public function __construct() {
        parent::__construct(); 
        $this->model=D('Biz_material');
        header("Content-type: text/html; charset=utf-8");
    }
    
    function material_video_image_upload(){   
        $file=$_FILES['file'];  
        $array=array();   
        //
        $suffix=substr($file['name'],strrpos($file['name'],'.')+1);     
		$suffix=strtolower($suffix);
        if($suffix=='mp4' || $suffix=='mov'){ 
            $original_file=$file['tmp_name'];               
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
            $result=$upload->upload(3,$temp_file,5,0);  

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
    
    
    function blws_upload($oss_file_path){        
        //$url='http://jby.com/index.php?m=Api&c=Material&a=accept_file';
        $file_link=$oss_file_path;
        $url='http://v.polyv.net/uc/services/rest?method=uploadUrlFile&fileName=remotefiletitle&writetoken=9c538d85-340c-466c-9e35-bb301734eb0d&fileUrl='.$file_link; 
          
        $data=$this->curl_get($url);
        $data=json_decode($data,true);
        if($data['error']==0){
            $return_data['vid']=$data['data'][0]['vid']; 
            $return_data['mp4']=$data['data'][0]['mp4']; 
        }else{
            $return_data=array();
        } 
       return $return_data;
    } 
    
    
    function curl_get($url){     
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL,$url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //执行并获取HTML文档内容
        $output = curl_exec($ch);      
        //释放curl句柄
        curl_close($ch);
        //打印获得的数据
        return $output;   
    }
 
    
    public function upload_material(){         
        $file=$_FILES['file'];    
        $suffix=substr($file['name'],strrpos($file['name'],'.')+1); 
        $suffix=strtolower($suffix);
        if($suffix=='mp4' || $suffix=='mov'){
            $type='video';

        }else if($suffix=='mp3'){
            $type='audio';

        }else if($suffix=='jpg' || $suffix=='png'){
            $type='image';

        }else if($suffix=='docx' || $suffix=='doc'){
            $type='word';

        }else if($suffix=='ppt' || $suffix=='pptx'){
            $type='ppt';

        }else if($suffix=='pdf'){
            $type='pdf';

        }else{
            $arr['msg']='上传失败';
            $arr['code']=-1;
            $arr['message']='不支持此类型文件上传';
            echo json_encode($arr);die;
        }
             
        $video_array=$this->material_video_image_upload(); 
         
        $upload = new \Oss\Ossupload();// 实例化上传类
        $result=$upload->upload(3,$_FILES,5,0);
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
        $arr['message']=$result[2];  
        //$data['material_name']=$_POST['name'];
        $data['material_name']=$file['name'];
        $index=strrpos($data['material_name'],'.');
        if($index!==false){
            $data['material_name']=substr($data['material_name'],0,$index);
        }
        $data['file_path']=$result[1];      
        if(is_array($video_array)){
            $data['vid_image_path']=$video_array['video_image'];
            $data['is_transition']=$video_array['is_transition'];
        }  
        if($arr['code']==0){
            if($suffix=='mp4' || $suffix=='mp3' || $suffix=='mov'){
                $oss_file_path='http://jbyoss.oss-cn-beijing.aliyuncs.com/'.$data['file_path'];
                $blws_result=$this->blws_upload($oss_file_path);    
                if(!empty($blws_result)){
                    $data['vid']=$blws_result['vid'];
                    $data['vid_fullpath']=$blws_result['mp4'];
                } 
            } 
            $data['type']=$type;

            $data['create_at']=time();
            $data['teacher_id']=$_POST['teacher_id'];
            $model=D('biz_material');
            if($model->saveMaterial($data)==false){
                $arr['code']=-1;
                $arr['message']='网络异常';
                $arr['msg']='上传失败';
                echo json_encode($arr);
            }else{
                echo json_encode($arr);
            }
        }else{
            echo json_encode($arr);
        }
        
    }
    
    public function view(){
        $this->display();
    }
   
}