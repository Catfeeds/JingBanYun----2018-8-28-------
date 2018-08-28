<?php
namespace Home\Controller;

use Think\Controller;

class VideoTransitionController extends PublicController
{
    public $operation_url='jby.com';
    
    function __construct(){     
        parent::__construct(); 
        header("Content-type: text/html; charset=utf-8"); 
        set_time_limit(0);
        $parameter=I('par');    
        if(!empty($parameter)){
            if($parameter==1){
                $this->operation_url='www.jingbanyun.com';
            }else{
                $this->operation_url='www.jtypt.com';
            }
        }  
    }
    
    //处理所有视频的转换操作        
    public function all_video_transition(){ 
        while(true){
            $material_flag=0;
            $resource_flag=0;
            $jbresource_flag=0;
            $blackboard_flag=0;
            $activity_works_flag=0;
            $activity_file_flag=0;

            if($this->material_video_transition()==1001){
                $material_flag=1;
            } 

            if($this->resource_video_transition()==1001){
                $resource_flag=1;
            }
            
            if($this->jbresource_video_transition()==1001){
                $jbresource_flag=1;
            }
            
            if($this->blackboard_video_transition()==1001){
                $blackboard_flag=1;
            }
            
            if($this->activity_works_video_transition()==1001){
                $activity_works_flag=1;
            }
            
            if($this->activity_file_video_transition()==1001){
                $activity_file_flag=1;
            }

            if($material_flag==1 && $resource_flag==1 && $jbresource_flag==1 && $blackboard_flag==1 && $activity_works_flag==1 && $activity_file_flag==1){
                sleep(30); 
            }
            break;
        }
    }
    
    
    /*
     * 处理活动作品的视频转码
     */
    function activity_works_video_transition(){     
        set_time_limit(0);
        $upload = new \Oss\Ossupload();  
        $activity_works=$this->get_request(1,6); 
        $activity_works=json_decode($activity_works,true);  
        
        if(empty($activity_works)){ 
            return 1001;
        } 
        
        for($i=0;$i<count($activity_works);$i++){ 
            $path_arr=explode('/',$activity_works[$i]['works_file_path']);   
            $file_path=$path_arr[count($path_arr)-1];       
            $file_path_arr=explode('.', $file_path);                
            $file_name=$file_path_arr[0];
            $file_suffix=$file_path_arr[1]; 
            //这里如果不是mp4 就跳过当前并且修改类型
            if($file_suffix!='mp4' && $file_suffix!='mov'){ 
                $this->get_request(2,6,$activity_works[$i]['id']);
                continue;
            }
            
            $localfile='E:/'.$file_name.'new'.'.'.$file_suffix;        echo $activity_works[$i]['works_file_path'].'__'; 
             
                
            $status=$upload->downloadFile($activity_works[$i]['works_file_path'],$localfile);        
            if($status==-1){
                continue;
            }else{
                //判断视频,处理视频.这代码如果到测试的时候还需要改运行的绝对地址  
                $output='';
                $re='';
                exec('c:/ffmpeg/bin/ffprobe -i '.$localfile.' 2>&1',$output,$status);
                var_dump($output);
                var_dump($status);
                $str=implode('',$output);              
                if(strpos($str,'h264')!==false){
                    //修改数据库 
                    $this->get_request(2,6,$activity_works[$i]['id']);
                    @unlink($localfile);
                    echo '不需要转换';
                    continue;
                }else{
                    echo '需要转换';
                    $upload_file='E:/'.$file_name.'.'.$file_suffix; 
                    exec("c:/ffmpeg/bin/ffmpeg -i ".$localfile." -c:v libx264 2>&1".$upload_file,$re,$success_flag);
                    @unlink($localfile);
                    if($success_flag==1){
                        //修改失败
                        echo '修改失败'; 
                        var_dump($re);
                        var_dump($success_flag); 
                        continue;
                    }else{
                        //修改数据库,原文件名路径上传
                        $upload->videoH264Upload($upload_file,$activity_works[$i]['works_file_path']); 
                        $this->get_request(2,6,$activity_works[$i]['id']);
                        @unlink($upload_file);
                    }   
                }
            } 
        } 
        return 1002; 
    }
    
    
    /*
     * 处理活动附件的视频转码
     */
    function activity_file_video_transition(){     
        set_time_limit(0);
        $upload = new \Oss\Ossupload();         
        $activity_attachment=$this->get_request(1,5); 
        $activity_attachment_result=json_decode($activity_attachment,true);     
        
        if(empty($activity_attachment_result)){ 
            return 1001;
        } 
        
        for($i=0;$i<count($activity_attachment_result);$i++){ 
            $path_arr=explode('/',$activity_attachment_result[$i]['activity_file_path']);   
            $file_path=$path_arr[count($path_arr)-1];       
            $file_path_arr=explode('.', $file_path);                
            $file_name=$file_path_arr[0];
            $file_suffix=$file_path_arr[1]; 
            //这里如果不是mp4 就跳过当前并且修改类型
            if($file_suffix!='mp4' && $file_suffix!='mov'){ 
                $this->get_request(2,5,$activity_attachment_result[$i]['id']);
                continue;
            }
            
            $localfile='E:/'.$file_name.'new'.'.'.$file_suffix;        echo $activity_attachment_result[$i]['activity_file_path'].'__'; 
             
                
            $status=$upload->downloadFile($activity_attachment_result[$i]['activity_file_path'],$localfile);        
            if($status==-1){
                continue;
            }else{
                //判断视频,处理视频.这代码如果到测试的时候还需要改运行的绝对地址  
                $output='';
                $re='';
                exec('c:/ffmpeg/bin/ffprobe -i '.$localfile.' 2>&1',$output,$status);
                var_dump($output);
                var_dump($status);
                $str=implode('',$output);              
                if(strpos($str,'h264')!==false){
                    //修改数据库
                    //$update_data['is_transition']=1;
                    //$resource_model->where("id=".$resource_result[$i]['id'])->save($update_data);
                    $this->get_request(2,5,$activity_attachment_result[$i]['id']);
                    @unlink($localfile);
                    echo '不需要转换';
                    continue;
                }else{
                    echo '需要转换';
                    $upload_file='E:/'.$file_name.'.'.$file_suffix; 
                    exec("c:/ffmpeg/bin/ffmpeg -i ".$localfile." -c:v libx264 2>&1".$upload_file,$re,$success_flag);
                    @unlink($localfile);
                    if($success_flag==1){
                        //修改失败
                        echo '修改失败'; 
                        var_dump($re);
                        var_dump($success_flag); 
                        continue;
                    }else{
                        //修改数据库,原文件名路径上传
                        $upload->videoH264Upload($upload_file,$activity_attachment_result[$i]['activity_file_path']);
                        //$update_data['is_transition']=1;
                        //$resource_model->where("id=".$resource_result[$i]['id'])->save($update_data);
                        $this->get_request(2,5,$activity_attachment_result[$i]['id']);
                        @unlink($upload_file);
                    }   
                }
            } 
        } 
        return 1002; 
    } 
    
     
    
    /*
     * 处理小黑板的视频转换
     */
    function blackboard_video_transition(){
        set_time_limit(0);
        $upload = new \Oss\Ossupload();
        
        //$model=M('biz_blackboard');
        //$blackboard_result=$model->where('is_transition=0')->field('id,message,is_transition')->select();
        $blackboard_result=$this->get_request(1,4); 
        $blackboard_result=json_decode($blackboard_result,true);       
        
        $reg='/<video .+?>/is'; 
        $reg_src='/src="(.+?)"/is';  
        
                
        if(empty($blackboard_result)){
            echo 1001;
        }else{
            foreach($blackboard_result as $val){
                preg_match_all($reg,$val['message'],$reg_data);       
                
                if(empty($reg_data[0])){
                   //没有找到video标签,修改数据库 
                    echo $val['id']."<br>";
                    $this->get_request(2,4,$val['id']);
                }else{  
                    for($i=0;$i<count($reg_data[0]);$i++){
                        preg_match($reg_src, $reg_data[0][$i], $reg_src_data);        
                        if(@$reg_src_data[1]!=''){
                            $path_arr=explode('/',$reg_src_data[1]);
                            $file_path=$path_arr[count($path_arr)-1];           
                            $file_path_arr=explode('.', $file_path);    
                            $file_name=$file_path_arr[0];
                            $file_suffix=$file_path_arr[1];     
                            if($file_suffix=='mp4' || $file_suffix=='mov' ){
                                //判断编码是否是h264
                                $localfile='E:/'.$file_name.'new'.'.'.$file_suffix;         echo $localfile."<hr>"; 
                                $oss_string_arr=explode('com/',$reg_src_data[1]);
                                $oss_file_path=$oss_string_arr[1];         
                                
                                $status=$upload->downloadFile($oss_file_path,$localfile);   
                                if($status!=-1){
                                    //判断视频,处理视频.这代码如果到测试的时候还需要改运行的绝对地址  
                                    $output='';
                                    $re='';
                                    exec('c:/ffmpeg/bin/ffprobe -i '.$localfile.' 2>&1',$output,$status);
                                    var_dump($output);
                                    var_dump($status);
                                    $str=implode('',$output);
                                    if(strpos($str,'h264')!==false){ 
                                        
                                    }else{
                                        echo '需要转换';    
                                        $upload_file='E:/'.$file_name.'.'.$file_suffix;
                                        exec("c:/ffmpeg/bin/ffmpeg -i ".$localfile." -c:v libx264 2>&1".$upload_file,$re,$success_flag); 
                                        
                                        @unlink($localfile);
                                        if($success_flag==1){
                                            //修改失败
                                            echo '修改失败';
                                            var_dump($re);
                                            var_dump($success_flag);
                                            continue;
                                        }else{
                                            //原文件名路径上传
                                            $upload->videoH264Upload($upload_file,$oss_file_path); 
                                            
                                            @unlink($upload_file);
                                        }
                                    }
                                }
                            } 
                        } 
                        if($i==(count($reg_data[0])-1)){ 
                            //修改数据库
                            $this->get_request(2,4,$val['id']);
                            echo $val['id']."<br>";
                        } 
                    }
                }
            }
        }
    }
     
    
    
    /*
     * 这里操作京版资源的转换   
     */ 
    function jbresource_video_transition(){     
        set_time_limit(0);
        $upload = new \Oss\Ossupload(); 
        /*$jbresource_model=M('biz_bj_resource_contact');
          $jbresource_result=$jbresource_model->join("biz_bj_resource on biz_bj_resource.id=biz_bj_resource_contact.biz_bj_resource_id")
                            ->where("biz_bj_resource.type='video' and biz_bj_resource_contact.is_transition=0")->field("biz_bj_resource_contact.id,biz_bj_resource_contact.resource_path")->select(); 
         */      
        //$jbresource_result=array();
        //$jbresource_result[]=array('id'=>2263,'resource_path'=>'Resources/jb/2016-09-05/20160905110109EkgHjPjmj6.mp4');
        //$jbresource_result[]=array('id'=>2732,'resource_path'=>'Resources/jb/2016-09-22/20160922014312EmTRety8M7.mp4');
        $jbresource_result=$this->get_request(1,3); 
        $jbresource_result=json_decode($jbresource_result,true);
        
        if(empty($jbresource_result)){ 
            return 1001;
        }
         
        for($i=0;$i<count($jbresource_result);$i++){ 
            $path_arr=explode('/',$jbresource_result[$i]['resource_path']);   
            $file_path=$path_arr[count($path_arr)-1];           
            $file_path_arr=explode('.', $file_path);
            $file_name=$file_path_arr[0];
            $file_suffix=$file_path_arr[1]; 
            //这里如果不是mp4 就跳过当前并且修改类型
            if($file_suffix!='mp4' && $file_suffix!='mov'){ 
                $this->get_request(2,3,$jbresource_result[$i]['id']);
                continue;
            }
            
            $localfile='E:/'.$file_name.'new'.'.'.$file_suffix;        echo $jbresource_result[$i]['resource_path'].'__'; echo $localfile."<hr>";
             
            
            $status=$upload->downloadFile($jbresource_result[$i]['resource_path'],$localfile); 
            if($status==-1){
                continue;
            }else{
                //判断视频,处理视频.这代码如果到测试的时候还需要改运行的绝对地址  
                $output='';
                $re='';
                exec('c:/ffmpeg/bin/ffprobe -i '.$localfile.' 2>&1',$output,$status);
                var_dump($output);
                var_dump($status);
                $str=implode('',$output); 
                if(strpos($str,'h264')!==false){
                    //修改数据库
                    //$update_data['is_transition']=1;
                    //$resource_model->where("id=".$resource_result[$i]['id'])->save($update_data);
                    $this->get_request(2,3,$jbresource_result[$i]['id']);
                    @unlink($localfile);
                    echo '不需要转换';
                    continue;
                }else{
                    echo '需要转换';
                    $upload_file='E:/'.$file_name.'.'.$file_suffix; 
                    exec("c:/ffmpeg/bin/ffmpeg -i ".$localfile." -c:v libx264 2>&1".$upload_file,$re,$success_flag);
                    @unlink($localfile);
                    if($success_flag==1){
                        //修改失败
                        echo '修改失败'; 
                        var_dump($re);
                        var_dump($success_flag); 
                        continue;
                    }else{
                        //修改数据库,原文件名路径上传
                        $upload->videoH264Upload($upload_file,$jbresource_result[$i]['resource_path']);
                        //$update_data['is_transition']=1;
                        //$resource_model->where("id=".$resource_result[$i]['id'])->save($update_data);
                        $this->get_request(2,3,$jbresource_result[$i]['id']);
                        @unlink($upload_file);
                    }
                }
            } 
        } 
        return 1002; 
    }
    
    
    /*
     * 这里操作教师资源的转换   
     */ 
    function resource_video_transition(){
        set_time_limit(0);
        $upload = new \Oss\Ossupload();
        /*$resource_model=M('biz_resource_contact'); 
        $resource_result=$resource_model->join("biz_resource on biz_resource.id=biz_resource_contact.biz_resource_id")
                ->where("biz_resource_contact.is_transition=0 and biz_resource.type='video'")->field("biz_resource_contact.id,biz_resource_contact.resource_path")->select();  
         */
        $resource_result=$this->get_request(1,2); 
        $resource_result=  json_decode($resource_result,true);          
        if(empty($resource_result)){ 
            return 1001;
        }
         
        for($i=0;$i<count($resource_result);$i++){ 
            $path_arr=explode('/',$resource_result[$i]['resource_path']);   
            $file_path=$path_arr[count($path_arr)-1];           
            $file_path_arr=explode('.', $file_path);
            $file_name=$file_path_arr[0];
            $file_suffix=$file_path_arr[1]; 
            //这里如果不是mp4 就跳过当前并且修改类型
            if($file_suffix!='mp4'){ 
                $this->get_request(2,2,$resource_result[$i]['id']);
                continue;
            }
            
            $localfile='E:/'.$file_name.'new'.'.'.$file_suffix;         echo $localfile."<hr>";       
            
            
            $status=$upload->downloadFile($resource_result[$i]['resource_path'],$localfile); 
            if($status==-1){
                continue;
            }else{
                //判断视频,处理视频.这代码如果到测试的时候还需要改运行的绝对地址  
                $output='';
                $re='';
                exec('c:/ffmpeg/bin/ffprobe -i '.$localfile.' 2>&1',$output,$status);
                var_dump($output);
                var_dump($status);
                $str=implode('',$output); 
                if(strpos($str,'h264')!==false){
                    //修改数据库
                    //$update_data['is_transition']=1;
                    //$resource_model->where("id=".$resource_result[$i]['id'])->save($update_data);
                    $this->get_request(2,2,$resource_result[$i]['id']);
                    @unlink($localfile);
                    echo '不需要转换';
                    continue;
                }else{
                    echo '需要转换';
                    $upload_file='E:/'.$file_name.'.'.$file_suffix;
                    exec("c:/ffmpeg/bin/ffmpeg -i ".$localfile." -c:v libx264 2>&1".$upload_file,$re,$success_flag);
                    @unlink($localfile);
                    if($success_flag==1){
                        //修改失败
                        echo '修改失败';
                        var_dump($re);
                        var_dump($success_flag);
                        continue;
                    }else{
                        //修改数据库,原文件名路径上传
                        $upload->videoH264Upload($upload_file,$resource_result[$i]['resource_path']);
                        //$update_data['is_transition']=1;
                        //$resource_model->where("id=".$resource_result[$i]['id'])->save($update_data);
                        $this->get_request(2,2,$resource_result[$i]['id']);
                        @unlink($upload_file);
                    }
                }
            }
        } 
        return 1002; 
    }
    
    /*
     * 获得全部未转换的素材视频
     */
    public function get_transition_video(){
        $video_type=$_GET['type'];
        if($video_type=='1'){
            $material_model=M('biz_material');
            $oss_path=C('oss_path');
            $material_result=$material_model->where("is_transition=0 and type='video' and file_path!=''")->field("id,file_path")->select();
            echo json_encode($material_result);
        }elseif($video_type=='2'){
            $resource_model=M('biz_resource_contact'); 
            $resource_result=$resource_model->join("biz_resource on biz_resource.id=biz_resource_contact.biz_resource_id")
                            ->where("biz_resource_contact.is_transition=0 and biz_resource.type='video' and biz_resource_contact.resource_path!=''")->field("biz_resource_contact.id,biz_resource_contact.resource_path")->select();
            echo json_encode($resource_result);
        }elseif($video_type=='3'){
            //京版资源
            $jbresource_model=M('biz_bj_resource_contact');
            $jbresource_result=$jbresource_model->join("biz_bj_resources on biz_bj_resources.id=biz_bj_resource_contact.biz_bj_resource_id")
                            ->where("biz_bj_resources.type='video' and biz_bj_resources.flag!=0 and biz_bj_resource_contact.is_transition=0 and biz_bj_resource_contact.resource_path!=''")->field("biz_bj_resource_contact.id,biz_bj_resource_contact.resource_path")->select();
            echo json_encode($jbresource_result);
        }elseif($video_type=='4'){
            //小黑板
            $blackboard_model=M('biz_blackboard');
            $blackboard_result=$blackboard_model->where('is_transition=0')->field('id,message,is_transition')->select();
            echo json_encode($blackboard_result);
        }elseif($video_type=='5'){
            //活动附件
            //$activity_attachment_model
            $activity_model=M('social_activity');
            $activity_attachment=$activity_model->join('social_activity_contact_file file on file.activity_id=social_activity.id')
                           ->where("file.is_transition=0 and file.type='video' and activity_file_path!=''")->field('file.id,file.activity_id,file.activity_file_path')->select();  
            echo json_encode($activity_attachment);
        }else{
            //活动作品
            $activity_works_model=M('social_activity_works');
            $activity_works_result=$activity_works_model->where("is_transition=0 and type='video'")
                                ->join("social_activity_works_file file on file.activity_works_id=social_activity_works.id")
                                ->field('file.id,works_file_path')->select();       
            echo json_encode($activity_works_result);
        }
        
    }
    
    /*
     * 修改某个视频的信息
     */
    public function update_video_info(){
        $video_type=$_GET['type'];
        $id=$_GET['id'];
        if($video_type=='1'){
            $material_model=M('biz_material');
            $update_data['is_transition']=1;
            $material_model->where("id=".$id)->save($update_data); 
        }elseif($video_type=='2'){
            $resource_model=M('biz_resource_contact');
            $update_data['is_transition']=1;
            $resource_model->where("id=".$id)->save($update_data);
        }elseif($video_type=='3'){
            //京版资源
            $jbresource_model=M('biz_bj_resource_contact');
            $update_data['is_transition']=1;
            $jbresource_model->where("id=".$id)->save($update_data);
        }elseif($video_type=='4'){
            //小黑板
            $blackboard_model=M('biz_blackboard');
            $update_data['is_transition']=1;
            $blackboard_model->where("id=".$id)->save($update_data);
        }elseif($video_type=='5'){
            //活动附件
            $activity_attachment_model=M('social_activity_contact_file');   
            $update_data['is_transition']=1;
            $activity_attachment_model->where("id=".$id)->save($update_data);
        }else{
            //活动作品
            $activity_works_model=M('social_activity_works_file');
            $update_data['is_transition']=1;
            $activity_works_model->where("id=".$id)->save($update_data);
        }
    }
    
    
    /*
     * get网络请求,并返回数据
     */
    function get_request($operation_flag=1,$video_source,$id=''){ 
        if($operation_flag==1){
            if($video_source==1){
                $url="http://".$this->operation_url."/index.php?m=Home&c=VideoTransition&a=get_transition_video&type=1";
            }elseif($video_source==2){
                $url="http://".$this->operation_url."/index.php?m=Home&c=VideoTransition&a=get_transition_video&type=2";
            }elseif($video_source==3){
                //京版资源
                $url="http://".$this->operation_url."/index.php?m=Home&c=VideoTransition&a=get_transition_video&type=3";
            }elseif($video_source==4){
                //小黑板
                $url="http://".$this->operation_url."/index.php?m=Home&c=VideoTransition&a=get_transition_video&type=4";
            }elseif($video_source==5){
                //活动附件
                $url="http://".$this->operation_url."/index.php?m=Home&c=VideoTransition&a=get_transition_video&type=5";
            }else{
                //活动作品
                $url="http://".$this->operation_url."/index.php?m=Home&c=VideoTransition&a=get_transition_video&type=6";
            }
        }else{
            //修改
            if($video_source==1){
                $url="http://".$this->operation_url."/index.php?m=Home&c=VideoTransition&a=update_video_info&type=1&id=".$id;
            }elseif($video_source==2){
                $url="http://".$this->operation_url."/index.php?m=Home&c=VideoTransition&a=update_video_info&type=2&id=".$id;
            }elseif($video_source==3){
                //京版资源
                $url="http://".$this->operation_url."/index.php?m=Home&c=VideoTransition&a=update_video_info&type=3&id=".$id;
            }elseif($video_source==4){
                //小黑板
                $url="http://".$this->operation_url."/index.php?m=Home&c=VideoTransition&a=update_video_info&type=4&id=".$id;//
            }elseif($video_source==5){
                //活动附件
                $url="http://".$this->operation_url."/index.php?m=Home&c=VideoTransition&a=update_video_info&type=5&id=".$id;
            }else{
                //活动作品
                $url="http://".$this->operation_url."/index.php?m=Home&c=VideoTransition&a=update_video_info&type=6&id=".$id;
            }
        }       
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        //释放curl句柄
        curl_close($ch);
        //打印获得的数据
        print_r($output);       
        return $output;
    }  
    
    
    
    /*
     * 这里操作素材库的转换 
     */ 
    function material_video_transition(){      
        set_time_limit(0);
        $upload = new \Oss\Ossupload();
        /*$material_model=M('biz_material');
        $oss_path=C('oss_path');
        $material_result=$material_model->where("is_transition=0 and type='video'")->field("id,file_path")->select();*/
        $material_result=$this->get_request(1,1);   
        $material_result=  json_decode($material_result,true);      
        if(empty($material_result)){ 
            return 1001;
        }    
        
        for($i=0;$i<count($material_result);$i++){  
            $path_arr=explode('/',$material_result[$i]['file_path']);   
            $file_path=$path_arr[count($path_arr)-1]; 
            $file_path_arr=explode('.', $file_path);
            $file_name=$file_path_arr[0];
            $file_suffix=$file_path_arr[1]; 
            
            $localfile='E:/'.$file_name.'new'.'.'.$file_suffix;         
            
            
            $status=$upload->downloadFile($material_result[$i]['file_path'],$localfile);   
            if($status==-1){
                continue;
            }else{
                //判断视频,处理视频.这代码如果到测试的时候还需要改运行的绝对地址  
                $output='';
                $re='';
                exec('c:/ffmpeg/bin/ffprobe -i '.$localfile.' 2>&1',$output,$status);
                var_dump($output);
                var_dump($status);
                $str=implode('',$output); 
                if(strpos($str,'h264')!==false){
                    //修改数据库
                    //$update_data['is_transition']=1;
                    //$material_model->where("id=".$material_result[$i]['id'])->save($update_data);
                    $this->get_request(2,1,$material_result[$i]['id']);
                    @unlink($localfile);
                    echo '不需要转换'; 
                    echo $i;
                }else{
                    echo '需要转换';
                    $upload_file='E:/'.$file_name.'.'.$file_suffix;
                    exec("c:/ffmpeg/bin/ffmpeg -i ".$localfile." -c:v libx264 2>&1".$upload_file,$re,$success_flag);
                    @unlink($localfile);
                    if($success_flag==1){
                        //修改失败
                        echo '修改失败';
                        var_dump($re);
                        var_dump($success_flag);
                        continue;
                    }else{
                        //修改数据库,原文件名路径上传
                        $upload->videoH264Upload($upload_file,$material_result[$i]['file_path']);
                        $this->get_request(2,1,$material_result[$i]['id']);
                        //$update_data['is_transition']=1;
                        //$material_model->where("id=".$material_result[$i]['id'])->save($update_data);
                        @unlink($upload_file);
                    }
                }
            }
        }  
        return 1002; 
    }

}