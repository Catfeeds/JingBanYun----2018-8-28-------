<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';
include 'Common/ImageResize.php';


/*路由*/
$app->post('/my', 'getMy');
$app->get('/my', 'getMy');
$app->post('/upload', 'upload');    
$app->post('/uploadLessonPlanning', 'uploadLessonPlanning');

function getMy()
{   
    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;

    $type='';
    if(!empty($_REQUEST['type'])){
        $type=$_REQUEST['type'];
    }  
    
    $db = new Database();
    if($type==''){
        //$qr = "select * from biz_material where teacher_id=$currentTeacherId and type='image' order by create_at desc ";
        $qr = "select * from biz_material where teacher_id=$currentTeacherId order by create_at desc ";
    }else{
        $qr = "select * from biz_material where teacher_id=$currentTeacherId and type='$type' order by create_at desc ";
    } 
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    foreach($result as &$val)
    {

        if (preg_match('/Resources/', $val->file_path)){
            
            $val->img_url = OSS_URL.$val->file_path;
        } else {
            $val->img_url = "http://".WEB_URL."/Uploads/TeacherMaterials/".$val->file_path;
        }
        
        if($val->type=='video'){
            $val->vid_image_path=OSS_URL.$val->vid_image_path;
            
        }elseif($val->type=='audio'){
            $val->vid_image_path="http://".WEB_URL.'/Public/img/resource/s_radio.jpg';
            
        }elseif($val->type=='image'){
            $val->vid_image_path=$val->img_url;
            
        }elseif($val->type=='word'){
            $val->vid_image_path="http://".WEB_URL.'/Public/img/resource/s_word.jpg';
            
        }elseif($val->type=='ppt'){
            $val->vid_image_path="http://".WEB_URL.'/Public/img/resource/s_ppt.jpg';
            
        }elseif($val->type=='pdf'){
            $val->vid_image_path="http://".WEB_URL.'/Public/img/resource/s_pdf.jpg';
            
        }elseif($val->type=='swf'){
            $val->vid_image_path="http://".WEB_URL.'/Public/img/resource/s_swf.jpg';
            
        }

        
    }
    $db = null; 
 
    renderJsonResponse(200, '', $result);
}

function upload()
{                
    header("Access-Control-Allow-Origin: *");
    $fn = $_POST['name'];               
    $currentTeacherId = $_POST['teacher_id'];       
    if ($_FILES["file"]["type"] == "image/jpeg" || $_FILES["file"]["type"] == "image/png" ) {       
        if ($_FILES["file"]["error"] > 0) {
            renderJsonResponse(406, $_FILES["file"]["error"], array()); //406 Not Acceptable
        } else {        
            $dirName = "../Uploads/TeacherMaterials/";
            $fileName = $fn . ".jpg";
            $avatarThumFilename = $fn . "-t.jpg";
            move_uploaded_file($_FILES["file"]["tmp_name"], $dirName . $fileName); //注意文件夹权限
            //$resizeimage = new ResizeImage($dirName . $fileName, '80', '80', '0', $dirName . $avatarThumFilename);
            
            $dir_img = str_replace('..','',$dirName);

            $ossurl = str_replace('/api','',dirname(__FILE__));

            $imgpath = $ossurl.$dir_img . $fileName;    
            
            $urldata = curl_post('http://'.WEB_URL.'/index.php?m=Home&c=App&a=upload_file',$imgpath);

            $jsonurl = json_decode($urldata,true); //得到oss文件路径

            if( $jsonurl ) {
                
                renderJsonResponse(200, '上传成功', array());
                $material_name=$_FILES['file']['name'];
                $index=strrpos($material_name,'.');
                if($index!==false){
                    $material_name=substr($material_name,0,$index);
                }
                
                $db = new Database();
                $data = array(
                    'teacher_id' => $currentTeacherId,
                    'create_at' => time(),
                    'file_path' => $jsonurl[1],
                    'type' => 'image',
                    //'material_name' => $_FILES['file']['name'],
                    'material_name' => $material_name
                );

                $db->insert("biz_material", $data);
                unlink($dirName . $fileName);

            } else {
                renderJsonResponse(406, '上传失败', array()); 
            }

           
            $db = null;
        }
    } else { 
        renderJsonResponse(406, '图片格式不正确', array());
    }
}

function curl_post( $url,$filepath) {  
    $ch = curl_init();
    $data = array('file' => '@'.$filepath);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);    // 5.6 给改成 true了, 弄回去 
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt ( $ch, CURLOPT_HEADER, 0 );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $return = curl_exec ( $ch );        
    curl_exec($ch);
    return $return;
}

function uploadLessonPlanning()
{
    header("Access-Control-Allow-Origin: *");
    $fn = $_POST['name'];

    if ($_FILES["file"]["error"] > 0) {
        renderJsonResponse(406, $_FILES["file"]["error"], array()); //406 Not Acceptable
    } else {
        $dirName = "../resources/lessonplanning/";
        $fileName = $fn . ".ppt";
        move_uploaded_file($_FILES["file"]["tmp_name"], $dirName . $fileName); //注意文件夹权限
        renderJsonResponse(200, '上传成功', array());
    }
}

$app->get('/unprocessed/:key', 'getUnProcessedPPTs');
function getUnProcessedPPTs($key)
{
    if ($key == 'uefdsf809f9rfef343f') {
        $db = new Database();
        $qr = "select id,teacher_id,file_path,type from biz_material where flag=1 and type in ('word','ppt','pdf') order by create_at desc";
        $sel = $db->fetch_custom($qr, array());
        $result = pushToArray($sel);
        $db = null;

        renderJsonResponse(200, '', $result);
    }

}

$app->get('/updateossflag/:key/:id', 'updateOssFlag');
function updateOssFlag($key, $id)
{
    if ($key == 'uefdsf809f9rfef343f') {
        $db = new Database();
        $data = array('flag' => 2);
        $db->update('biz_material', $data, 'id', $id);
        $db = null;
        renderJsonResponse(200, '', array());
    }

}
$app->run();