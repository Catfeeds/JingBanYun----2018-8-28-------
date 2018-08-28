<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';


/*路由*/
$app->post('/listall', 'getResources4QrCode');
$app->get('/listall', 'getResources4QrCode');
$app->post('/list', 'getResources');
$app->post('/details/:id', 'getResourceDetails');
$app->post('/course/list', 'getCourses');
$app->post('/grade/list', 'getGrades');
$app->post('/list/all', 'getAllResources');      //post
$app->post('/list/myfavor','getMyFavorResources');
$app->get('/list/myfavor','getMyFavorResources');

$app->get('/get_method_course/list', 'getMethodCourses');
$app->get('/get_method_grade/list', 'getMethodGrades');
$app->get('/get_method_list', 'getMethodResources');

function getMyFavorResources()
{
    $db = new Database();
    //$currentTeacher = getTeacherContext();
    //$currentTeacherId = $currentTeacher->id;

    $currentUserId = $_REQUEST['id'];
    $currentUserRole = $_REQUEST['role'];
    $pageIndex = $_REQUEST['pageIndex'];
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize = getPageSize();
    $startIndex = ($pageIndex - 1) * $pageSize;

    $qr = "select biz_bj_resources.*,biz_textbook.name as textbook from biz_bj_resources join biz_textbook on biz_bj_resources.textbook_id=biz_textbook.id
     join biz_bj_resource_collect on biz_bj_resource_collect.resource_id=biz_bj_resources.id where biz_bj_resources.status=2 and role=$currentUserRole and biz_bj_resource_collect.user_id=" .$currentUserId
     ." order by biz_bj_resources.create_at desc limit $startIndex,$pageSize ";
     $sel = $db->fetch_custom($qr, array());
     $result = pushToArray($sel);
     $db = null;
    renderJsonResponse(200, '', $result);
}
//获取京版资源列表
function getResources4QrCode()
{
    $courseId = isset($_REQUEST['courseId']) ? $_REQUEST['courseId']:1;
    $gradeId = $_REQUEST['gradeId'];
    $keyword = $_REQUEST['keyword'];
    $schoolterm = $_REQUEST['schoolterm'];
     $where = ' ';
     if (!empty($keyword)) {
         $where .= " and r.name like '%$keyword%'";
     }
     if (!empty($courseId)) {
         $where .= " and k.course = $courseId";
     }
     if (!empty($gradeId)) {
         switch($gradeId)
         {
             case 14: $where .= " and k.grade in (1,2,3,4,5,6,14)";
                       break;
             case 15: $where .= " and k.grade in (7,8,9,15)";
                       break;
             case 16: $where .= " and k.grade in (10,11,12,16)";
                       break;
             default :$where .= " and k.grade = $gradeId";
                       break;
         }
     }
     if (!empty($schoolterm))
         $where .= " and t.school_term = $schoolterm";


     $db = new Database();
     $qr = "select r.id,r.name,r.file_type type,r.create_at,t.name as textbook,g.grade,c.course_name as course  from knowledge_resource r left join knowledge_resource_point k ON k.knowledge_resource_id = r.id left join biz_textbook t on k.textbook=t.id left join dict_course_copy_resource c on c.id=k.course "
             . "left join dict_grade g on g.id=k.grade  where 1=1 and r.file_type!='condensed' and r.file_type!='swf' and r.status=1 $where group by r.id order by create_at desc ";
     $sel = $db->fetch_custom($qr, array());
     $result = pushToArray($sel);
     $db = null;

     renderJsonResponse(200, '', $result);
}
function getResources()
{
    $courseId = $_POST['courseId'];
    $gradeId = $_POST['gradeId'];
    $keyword = $_POST['keyword'];

    $pageIndex = $_POST['pageIndex'];
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize = getPageSize();
    $startIndex = ($pageIndex - 1) * $pageSize;

    $where = ' and r.isDisplay=1 ';
    if (!empty($keyword)) {
        $where .= " and r.name like '%$keyword%'";
    }


    $db = new Database();
    $qr = "select r.id,r.name,r.type,r.create_at,t.name as textbook,g.grade,c.course_name as course  from biz_bj_resources r inner join biz_textbook t on r.textbook_id=t.id "
            . "inner join dict_course c on c.id=r.course_id inner join dict_grade g on g.id=r.grade_id where 1=1 and r.isDisplay=1 and r.type!='condensed' and r.type!='swf' and r.status=2 $where order by create_at desc LIMIT $startIndex, $pageSize ";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

function getAllResources()
{
    $pageIndex = $_POST['pageIndex'];//post
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize = getPageSize();
    $startIndex = ($pageIndex - 1) * $pageSize;

    $courseId = isset($_POST['courseId'])?$_POST['courseId']:array();
    $gradeId = isset($_POST['gradeId'])?$_POST['gradeId']:array();
    $textbookId = isset($_POST['textbookId'])?$_POST['textbookId']:array();
    $channelId = isset($_POST['channelId'])?$_POST['channelId']:array();
    $type = isset($_POST['type'])?$_POST['type']:array();

    $where = "where 1=1 and r.status=2 and r.isDisplay=1 and r.type!='condensed'";        
    if (!empty($courseId)) $where = $where . " and r.course_id=$courseId ";
    if (!empty($gradeId)) $where = $where . " and r.grade_id=$gradeId ";
    if (!empty($textbookId)) $where = $where . " and t.school_term=$textbookId ";
    if (!empty($channelId)) $where = $where . " and r.channel_id=$channelId ";
    if (!empty($type)&&(strtolower($type)!='swf')) $where = $where . " and r.type='$type' ";
    $where = $where . " and r.type!='swf' "  ;//." and status = 2 "

    $db = new Database();
    $qr = "select r.id,r.name,r.type,r.zan_count,r.follow_count,r.favorite_count,r.create_at,t.name as textbook,g.grade,g.chinese_code,c.course_name as course,r.file_path,r.vid,r.oss_path,t.school_term,if(UNIX_TIMESTAMP(NOW())-604800>r.create_at,'no','yes') is_new from biz_bj_resources r inner join biz_textbook t on r.textbook_id=t.id inner join dict_course c on c.id=r.course_id inner join dict_grade g on g.id=r.grade_id $where order by create_at desc LIMIT $startIndex, $pageSize ";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

function object_array($array){
  if(is_object($array)){
    $array = (array)$array;
  }
  if(is_array($array)){
    foreach($array as $key=>$value){
      $array[$key] = object_array($value);
    }
  }
  return $array;
}

function getResourceDetails($id)
{
    $db = new Database();
    $role = $_POST['role'];
    $userid = $_POST['user_id'];
    $qr = "SELECT
               r.id,
               r.name,
               r.type,
               r.create_at,
               r.vid_image_path as image_url,
               t.name AS textbook,
               g.grade,
               h.name AS channelName,
               c.course_name AS course,
               r.file_path,
               r.vid,
               r.oss_path,
               r.description,
               r.zan_count,
               r.favorite_count,
               (case when i.resource_id is null then 0 else 1 end) as iscollect,
               (case when j.resource_id is null then 0 else 1 end) as iszan
           FROM
               biz_bj_resources r
                   INNER JOIN
               biz_textbook t ON r.textbook_id = t.id
                   INNER JOIN
               dict_course c ON c.id = r.course_id
                   INNER JOIN
               dict_grade g ON g.id = r.grade_id
                   LEFT JOIN
           	dict_channel h ON h.id = r.channel_id
           	    LEFT JOIN
           	(select resource_id from biz_bj_resource_collect where role = $role and user_id = $userid and resource_id =$id)  i on i.resource_id = r.id
           		LEFT JOIN
           	(select resource_id from biz_bj_resource_zan where role = $role and user_id = $userid and resource_id =$id)  j on j.resource_id = r.id
           WHERE
               r.id =$id";

    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    foreach ($result as $key => $val) {
    if($val->type == 'video' || $val->type == 'audio')
      {
      $blwsQueryResult = json_decode(file_get_contents("http://v.polyv.net/uc/services/rest?method=getById&vid=".$val->vid."&readtoken=".BLWS_READTOKEN),true);
      $mediaSource = $blwsQueryResult['data'][0]['mp4'];
      $result[$key]->mediaSource = $mediaSource;
      }
      else if($val->type == 'pdf' || $val->type == 'word' || $val->type == 'ppt')
     {
        $result[$key]->full_path = $result[$key]->oss_path . '/jbresource/' . $result[$key]->id .'/' ;
        
        $arr=explode('.',basename($result[$key]->file_path));
        $result[$key]->full_path.$arr[0].'.pdf';
        //[0] . '.pdf'
     }

        if(substr($val->file_path,0,9)=='Resources')
        {
            $result[$key]->oss_path.='/'.$val->file_path.'?1=1';
        }else{
            $result[$key]->oss_path.='/bjresource/'.$id.'/'.substr($val->file_path,strrpos($val->file_path,'/')+1,strlen($val->file_path)).'?1=1';
        }
   }
    $update_follow_count = "update biz_bj_resources set follow_count=follow_count+1 where id= $id ";
    $db->fetch_custom($update_follow_count);
	$db = null;    
    renderJsonResponse(200, '', $result);
}

//获取课程字典
function getCourses()
{
    $db = new Database();
    $qr = "select id,course_name as name from dict_course_copy_resource";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

//获取年级字典
function getGrades()
{
    $db = new Database();
    $qr = "select id,grade as name from dict_grade";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

//获取课程字典
function getMethodCourses()
{
    $db = new Database();
    $qr = "select id,course_name as name from dict_course";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

//获取年级字典
function getMethodGrades()
{
    $db = new Database();
    $qr = "select id,grade as name from dict_grade";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

//获取京版资源列表
function getMethodResources()
{
    $courseId = $_GET['courseId'];
    $gradeId = $_GET['gradeId'];
    $keyword = $_GET['keyword'];

    $pageIndex = $_GET['pageIndex'];
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize = getPageSize();
    $startIndex = ($pageIndex - 1) * $pageSize;

    $where = '';
    if (!empty($keyword)) {
        $where = " and r.name like '%$keyword%'";
    }

    $where = $where . " and r.grade_id=$gradeId and r.course_id=$courseId ";

    $db = new Database();
    $qr = "select r.id,r.name,r.type,r.create_at,t.name as textbook,g.grade,c.course_name as course  from biz_bj_resources r inner join biz_textbook t on r.textbook_id=t.id inner join dict_course c on c.id=r.course_id "
            . "inner join dict_grade g on g.id=r.grade_id where 1=1 and r.isDisplay=1 and r.type!='condensed' and r.type!='swf' and r.status=2 $where order by create_at desc ";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}


$app->get('/unprocessed/:key', 'getUnProcessed');
function getUnProcessed($key)
{
    if ($key == 'uefdsf809f9rfef343f') {
        $db = new Database();
        $qr = "select id,name,type,file_path from biz_bj_resources where flag=0 and type in ('word','ppt','pdf') order by create_at desc";
        $sel = $db->fetch_custom($qr, array());
        $result = pushToArray($sel);
        $db = null;

        renderJsonResponse(200, '', $result);
    }

}

$app->get('/unprocessedSub/:key', 'getUnProcessedSub');
function getUnProcessedSub($key)
{
    if ($key == 'uefdsf809f9rfef343f') {
        $db = new Database();
        $qr = "select id,resource_id bj_resource_id,resource_path file_path,type from knowledge_resource_file_contact where type in ('word','ppt','pdf') and (flag = 1 OR flag=0) 
               union all 
               select knowledge_resource_file_contact.id,resource_id as bj_resource_id,resource_path as file_path,file_type as type from knowledge_resource_file_contact
               join (select id,file_type from knowledge_resource where file_type in ('word','ppt','pdf')) a on a.id = knowledge_resource_file_contact.resource_id where flag=1 OR flag=0";
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
        $db->update('biz_bj_resources', $data, 'id', $id);
        $db = null;
        renderJsonResponse(200, '', array());
    }

}

$app->get('/updateossflagSub/:key/:id', 'updateOssFlagSub');
function updateOssFlagSub($key, $id)
{
    if ($key == 'uefdsf809f9rfef343f') {
        $db = new Database();
        $data = array('flag' => 2);
        $db->update('knowledge_resource_file_contact', $data, 'id', $id);
        $db = null;
        renderJsonResponse(200, '', array());
    }

}

$app->run();