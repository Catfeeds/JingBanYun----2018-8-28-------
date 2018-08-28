<?php
namespace Common\Common;
   class CSV
     {
       //得到csv数据 
        public function getCsvData()
        {
            $filename = $_FILES['file']['tmp_name'];
            if (empty ($filename)) {
                return 1001;
            }
            $handle = fopen($filename, 'r');
            $result = $this->input_csv($handle);
            $len_result = count($result);
            fclose($handle); 
            if($len_result==0){
                return 1002;
            }
            $data['result']=$result;
            $data['length']=$len_result;
            return $data;
        }
        
        //解析csv文件
        public function input_csv($handle) {
            $out = array ();
            $n = 0;
            while ($data = fgetcsv($handle, 10000)) {
                    $num = count($data);
                    for ($i = 0; $i < $num; $i++) {
                            $out[$n][$i] = $data[$i];
                    }
                    $n++;
            }
            return $out;
        }
        
        //直接下载csv文件
        public function downloadFileCsv($filename,$data){
            header("Content-type:text/csv");
            header("Content-Disposition:attachment;filename=".$filename);
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Expires:0');
            header('Pragma:public');
            echo $data;
        }
        
        //直接下载文件
        public function downloadFile($file_name){
            $fileinfo = pathinfo($file_name);	 
            $file_path=realpath($file_name);
            header('Content-type: application/x-'.$fileinfo['extension']);
            header( "Accept-Ranges:  bytes ");
            header('Content-Length: '.filesize($file_path));
            header('Content-Disposition: attachment; filename='.$fileinfo['basename']);
            readfile($file_path); 
        }

        public function downloadFileCopy( $filepath,$filename ) {
            header("Content-type:text/html;charset=utf-8");

            $file_name=iconv("utf-8","gbk",$filepath);
            $file_path=$file_name;
            if(!file_exists($file_path)){
                echo "没有该文件文件";
                return ;
            }
            $fp=fopen($file_path,"r");
            $file_size=filesize($file_path);
            $file_name = $filename.'.csv'; //保存的文件名

            $ua = $_SERVER["HTTP_USER_AGENT"];
            if(strpos($ua,'MSIE')!==false || strpos($ua,'rv:11.0')) {
                $file_name = urlencode($file_name);
            }

            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length:".$file_size);
            Header("Content-Disposition: attachment; filename=".$file_name);

            $buffer=1024;
            $file_count=0;
            while(!feof($fp) && $file_count<$file_size){
                $file_con=fread($fp,$buffer);
                $file_count+=$buffer;
                echo $file_con;
            }
            fclose($fp);
        }

       public function downloadFileXls( $filepath,$filename ) {
           header("Content-type:text/html;charset=utf-8");

           $file_name=iconv("utf-8","gbk",$filepath);
           $file_path=$file_name;
           if(!file_exists($file_path)){
               echo "没有该文件文件";
               return ;
           }
           $fp=fopen($file_path,"r");
           $file_size=filesize($file_path);
           $file_name = $filename.'.xls'; //保存的文件名

           $ua = $_SERVER["HTTP_USER_AGENT"];
           if(strpos($ua,'MSIE')!==false || strpos($ua,'rv:11.0')) {
               $file_name = urlencode($file_name);
           }

           Header("Content-type: application/octet-stream");
           Header("Accept-Ranges: bytes");
           Header("Accept-Length:".$file_size);
           Header("Content-Disposition: attachment; filename=".$file_name);

           $buffer=1024;
           $file_count=0;
           while(!feof($fp) && $file_count<$file_size){
               $file_con=fread($fp,$buffer);
               $file_count+=$buffer;
               echo $file_con;
           }
           fclose($fp);
       }
         
        //远程下载视频图片文件,这里传递的是一个绝对路径
        function downloadMedia($file_name){    
            $fileinfo = pathinfo($file_name);
            $extension=$fileinfo['extension'];
            $real_file_name=md5(date('Ymd').rand(100,10000)).'.'.$extension;
            
            //$file_path=realpath($file_name);     
            $fp=fopen($file_name,"r"); 
            //$file_size=filesize($file_path); 
            Header("Content-type: application/octet-stream");
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Expires:0');
            header('Pragma:public');    //$file_name='aa.mp4';
            Header("Content-Disposition: attachment; filename=".$real_file_name);
            //分段读取
            $buffer=1024;
            while(!feof($fp)){
                $file_data=fread($fp,$buffer);
                echo $file_data;
            }
        }

       function downloadMediaCopy($file_name,$name){
           $fileinfo = pathinfo($file_name);

           // [basename] => 1、北京市语文教学大会讲话稿(温儒敏)180420.pdf  [filename] => 1、北京市语文教学大会讲话稿(温儒敏)180420
           //$real_file_name=md5(date('Ymd').rand(100,10000)).'.'.$extension;

           //$file_path=realpath($file_name);
           $fp=fopen($file_name,"r");

           //$file_size=filesize($file_path);
           Header("Content-type: application/octet-stream");
           header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
           header('Expires:0');
           header('Pragma:public');    //$file_name='aa.mp4';
           $name = iconv("utf-8","gb2312",$name);
           Header("Content-Disposition: attachment; filename=".$name);
           //分段读取
           $buffer=1024;
           while(!feof($fp)){
               $file_data=fread($fp,$buffer);
               echo $file_data;
           }
       }
}
?>