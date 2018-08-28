<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';

/*路由*/

$app->post('/list/:cid/:gid/:pageIndex', 'getResourcesByCourseAndGrade');
$app->post('/list_textbook/:cid/:gid/:termId', 'getResourcesByCourseAndTextbook');
$app->get('/list_textbook/:cid/:gid/:termId', 'getResourcesByCourseAndTextbook');
$app->post('/details/:id', 'getResourceDetails');    //post
$app->post('/zan/:id', 'zan');
$app->post('/collect/:id', 'collect');
$app->post('/haszan/:id', 'hasZan');
$app->post('/hascollected/:id', 'hasCollect');
$app->post('/zans/:id', 'getZans');
$app->post('/mycollected', 'getMyCollectedResources');
$app->get('/mycollected', 'getMyCollectedResources');
$app->post('/mypublished', 'getMyPublishedResources');
$app->get('/mypublished', 'getMyPublishedResources');
$app->post('/bybookid/:bookId', 'getResourcesByBookId');


//根据学科和年级获取资源
function getResourcesByCourseAndGrade($cid, $gid, $pageIndex)
{
    $pageIndex = $_POST['pageIndex'];
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize = getPageSize();
    $startIndex = ($pageIndex - 1) * $pageSize;

    $db = new Database();
    $qr = "select r.id,r.name,r.type,r.textbook_id,b.name as textbook,r.teacher_id,r.teacher_name,r.description,r.create_at from biz_resource r inner join biz_textbook b on r.textbook_id=b.id where r.status=2 and r.course_id=$cid and r.grade_id=$gid and r.type!='swf' order by create_at desc LIMIT $startIndex, $pageSize ";
    $sel = $db->fetch_custom($qr);
    $result = pushToArray($sel);

    $db = null;
    renderJsonResponse(200, '', $result);
}

//根据教材获取资源
function getResourcesByCourseAndTextbook($cid, $gid, $termId)
{       
    $pageIndex = $_REQUEST['pageIndex'];
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize = getPageSize();
    $startIndex = ($pageIndex - 1) * $pageSize;

    $db = new Database();
    $qr = "select r.id,r.name,r.type,r.textbook_id,b.name as textbook,r.teacher_id,r.teacher_name,r.description,r.create_at,r.zan_count,r.favorite_count,r.follow_count,if(UNIX_TIMESTAMP(NOW())-604800>r.create_at,'no','yes') is_new from biz_resource r inner join biz_textbook b on r.textbook_id=b.id where r.status=2 and r.course_id=$cid and r.grade_id=$gid and r.type!='swf' and b.school_term=$termId order by create_at desc LIMIT $startIndex, $pageSize ";
    
    $sel = $db->fetch_custom($qr);
    $result = pushToArray($sel);

    $db = null;
    renderJsonResponse(200, '', $result);
}

//根据课本获取该课本相关的资源
function getResourcesByBookId($bookId)
{
    $pageSize = getPageSize();
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $startIndex = ($pageIndex - 1) * $pageSize;
    $db = new Database();
    $qr = "select r.id,r.name,r.type,r.textbook_id,b.name as textbook,r.teacher_id,r.teacher_name,r.description,r.create_at from biz_resource r inner join biz_textbook b on r.textbook_id=b.id where r.status=2 and r.textbook_id=$bookId and r.type!='swf' order by create_at desc LIMIT $startIndex, $pageSize ";
    $sel = $db->fetch_custom($qr);
    $result = pushToArray($sel);

    $db = null;
    renderJsonResponse(200, '', $result);
}
function getUserParas()
{
    $id = $_GET['id'];
    $role = $_POST['role'];
    if(empty($role))
     $role = 0;
    return array('id'=> $id,'role'=>$role);
}
//点赞
function zan($id)
{
    //$currentTeacher = getTeacherContext();
    //$currentTeacherId = $currentTeacher->id;
    //$currentTeacherName = $currentTeacher->name;
    $userArr = getUserParas();
    $db = new Database();

    $data = array(
        'user_id' => $userArr['id'],
        'user_type' => $userArr['role'],
        'resource_id' => $id,
        'user_name' => '',
        'create_at' => time()
    );

    try {
        $db->insert("biz_resource_zan", $data);
        //总赞数+1
        $update_zan_count = "update biz_resource set zan_count=zan_count+1 where id= $id ";
        $db->fetch_custom($update_zan_count);

        //计算赞的总数
        $resource = $db->fetch_single_row('biz_resource', 'id', $id);

        renderJsonResponse(200, '点赞成功', $resource->zan_count);
    } catch (PDOException $e) {
        //取消点赞
        $db->fetch_custom("delete from biz_resource_zan where user_type=0 and user_id=$currentTeacherId and resource_id=$id");
        //总赞数-1
        $updateFavourCount = "update biz_resource set zan_count=zan_count-1 where id=$id";
        $db->fetch_custom($updateFavourCount);
        $resource = $db->fetch_single_row('biz_resource', 'id', $id);
        renderJsonResponse(201, '已经取消了点赞', $resource->zan_count);
    }

    $db = null;
}

//收藏资源
function collect($id)
{
    //$currentTeacher = getTeacherContext();
    //$currentTeacherId = $currentTeacher->id;
    //$currentTeacherName = $currentTeacher->name;
    $userArr = getUserParas();
    $db = new Database();
    $data = array(
        'user_id' => $userArr['id'],
        'user_type' => $userArr['role'],
        'resource_id' => $id,
        'user_name' => '',
        'create_at' => time()
    );

    try {
        $db->insert("biz_resource_collect", $data);
        //总收藏数+1
        $updateFavourCount = "update biz_resource set favorite_count=favorite_count+1 where id=$id";
        $db->fetch_custom($updateFavourCount);
        renderJsonResponse(200, '收藏成功', array());
    } catch (PDOException $e) {
        //取消收藏
        $db->fetch_custom("delete from biz_resource_collect where user_type=0 and user_id=$currentTeacherId and resource_id=$id");
        //总收藏数-1
        $updateFavourCount = "update biz_resource set favorite_count=favorite_count-1 where id=$id";
        $db->fetch_custom($updateFavourCount);
        renderJsonResponse(201, '已经取消了收藏', array());
    }

    $db = null;
}

//判断是否已经点赞
function hasZan($id)
{
    //$currentTeacher = getTeacherContext();
    //$currentTeacherId = $currentTeacher->id;
    $userArr = getUserParas();
    $data = array(
        'user_id' => $userArr['id'],
        'user_type' => $userArr['role'],
        'resource_id' => $id
    );
    $db = new Database();
    $s = $db->check_exist('biz_resource_zan', $data);


    //计算赞的总数
    $resource = $db->fetch_single_row('biz_resource', 'id', $id);

    if ($s == true) {
        renderJsonResponse(200, 'yes', $resource->zan_count);
    } else {
        renderJsonResponse(200, 'no', $resource->zan_count);
    }
    $db = null;
}

//判断是否已经收藏
function hasCollect($id)
{
    //$currentTeacher = getTeacherContext();
    //$currentTeacherId = $currentTeacher->id;
    $userArr = getUserParas();
    $data = array(
        'user_id' => $userArr['id'],
        'user_type' => $userArr['role'],
        'resource_id' => $id
    );
    $db = new Database();
    $s = $db->check_exist('biz_resource_collect', $data);
    if ($s == true) {
        renderJsonResponse(200, 'yes', 'yes');
    } else {
        renderJsonResponse(200, 'no', 'no');
    }
    $db = null;
}
function getSubResourceHTMLList($info,$type)
{
  $OSS_PATH = 'http://jbyoss.oss-cn-beijing.aliyuncs.com/';
  $db = new Database();
  $oss_path = $OSS_PATH;
  $infoid = $info->id;
  $qr = "select * from biz_resource_contact where biz_resource_id=$infoid";
  $sel = $db->fetch_custom($qr);
  $result = pushToArray($sel);
  $htmlList = "";
  switch($info->type)
     {
     case 'HTML': $htmlList = $info->content;
                  break;
     case 'ppt':
     case 'word':$i=1;
                 foreach($result as &$val)
                  { 
                     $resource_arr=explode('.',basename($val->resource_path));
                     $resource_path=$resource_arr[0];
                     $htmlList = $htmlList . '<a href="' .$oss_path .'teacher/'.$info->id .'/'.$val->id .'/'. $resource_path .'.pdf">' . '查看第' .$i . '个资源</a></br>' ;
                     $i++;
                  }
                 break;
     case 'pdf':
                    $i=1;
                    foreach($result as &$val)
                    {
                     $htmlList = $htmlList . '<a href="' .$oss_path .$val->resource_path .'">' . '查看第' .$i . '个资源</a></br>' ;
                     $i++;
                    }
                    break;
     case 'image':
                    foreach($result as &$val)
                     {
                        $htmlList = $htmlList . '<img src="' .$oss_path .$val->resource_path .'"></img></br>' ;
                     }
                     break;
     case 'video':  $i=1;
                    foreach($result as &$val)
                     {
                        $htmlList = $htmlList . '<video src="' .$oss_path .$val->resource_path .'">' . '查看第' .$i . '个资源</video></br>' ;
                        $i++;
                     }
                     break;
     case 'audio':  $i=1;
                    foreach($result as &$val)
                     {
                        $htmlList = $htmlList . '<audio src="' .$oss_path .$val->resource_path .'">' . '查看第' .$i . '个资源</audio></br>' ;
                        $i++;
                     }
                     break;
     }
  $db = null;
  return $htmlList;
}
//获取资源详情
function getResourceDetails($id)
{       
    $db = new Database();       
    //TODO:add mutiple resource 
    $info = $db->fetch_single_row('biz_resource', 'id', $id);   

    $info->content = getSubResourceHTMLList($info,$info->type);
    $update_follow_count = "update biz_resource set follow_count=follow_count+1 where id= $id ";
    $db->fetch_custom($update_follow_count);
    renderJsonResponse(200, '', $info);
    $db = null;
}

//获取获得的赞的情况
function getZans($id)
{
    $db = new Database();
    $qr = "select * from biz_resource_zan where resource_id=$id order by create_at desc";
    $sel = $db->fetch_custom($qr);
    $result = pushToArray($sel);

    $db = null;
    renderJsonResponse(200, '', $result);
}

//获取我收藏的资源
function getMyCollectedResources()
{
    //$currentTeacher = getTeacherContext();
    //$currentTeacherId = $currentTeacher->id;
    $userArr = getUserParas();
    $db = new Database();
    $pageIndex = $_REQUEST['pageIndex'];
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize = getPageSize();
	$userArr_id = $userArr['id'];
	$userArr_role = $userArr['role'];
    $startIndex = ($pageIndex - 1) * $pageSize;
    $qr = "select r.id,r.name,r.type,r.textbook_id,b.name as textbook,r.teacher_id,r.teacher_name,r.description,r.create_at,r.create_at,r.follow_count,r.favorite_count,r.zan_count,te.avatar from biz_resource_collect c inner join biz_resource r on c.resource_id=r.id inner join biz_textbook b on r.textbook_id=b.id inner join auth_teacher te on r.teacher_id=te.id  where r.status=2 and c.user_id=$userArr_id and user_type=$userArr_role and r.type!='swf' order by create_at desc LIMIT $startIndex, $pageSize";
	
    $sel = $db->fetch_custom($qr);
    $result = pushToArray($sel);
    foreach($result as &$val)
    {
        
        if (preg_match('/Resources/', $val->avatar)){
            
            $val->img_url = OSS_URL.$val->avatar;
        } else {
            $val->img_url = "http://".WEB_URL."/Uploads/Avatars/".$val->avatar;
        }
    }
    $db = null;
    renderJsonResponse(200, '', $result);
}

//获取我发布的资源
function getMyPublishedResources()
{
    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;

    $db = new Database();
    $pageIndex = $_REQUEST['pageIndex'];
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize = getPageSize();
    $startIndex = ($pageIndex - 1) * $pageSize;
    $qr = "select r.id,r.name,r.type,r.textbook_id,b.name as textbook,r.teacher_id,r.teacher_name,r.description,r.create_at,r.follow_count,r.favorite_count,r.zan_count from biz_resource r inner join biz_textbook b on r.textbook_id=b.id where r.teacher_id=$currentTeacherId and r.type!='swf' order by create_at desc LIMIT $startIndex, $pageSize";
    $sel = $db->fetch_custom($qr);
    $result = pushToArray($sel);
    $db = null;

    $db = new Database();
    $user = $db->fetch_single_row('auth_teacher', 'id', $currentTeacherId);

    if (preg_match('/Resources/', $user->avatar)){

        $message = OSS_URL.$user->avatar;
    } else {
        $message = "http://".WEB_URL."/Uploads/Avatars/".$user->avatar;
    }

    renderJsonResponse(200, $message, $result);
}

$app->get('/unprocessed/:key', 'getUnProcessedPPTs');
function getUnProcessedPPTs($key)
{
    if ($key == 'uefdsf809f9rfef343f') {
        $db = new Database();
        $qr = "select id,name,teacher_id,file_path from biz_resource where flag=0 and type in ('word','ppt','pdf') order by create_at desc";
        $sel = $db->fetch_custom($qr, array());
        $result = pushToArray($sel);
        $db = null;

        renderJsonResponse(200, '', $result);
    }

}

$app->get('/unprocessedSub/:key', 'getUnProcessedSubPPTs');
function getUnProcessedSubPPTs($key)
{
    if ($key == 'uefdsf809f9rfef343f') {
           $db = new Database();
           $qr = "select biz_resource_contact.id,type,biz_resource_id as bj_resource_id,resource_path as file_path,type from biz_resource_contact
                  join (select id,type from biz_resource where type in ('word','ppt','pdf')) a on a.id = biz_resource_contact.biz_resource_id where flag=1";
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
        $db->update('biz_resource', $data, 'id', $id);
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
        $db->update('biz_resource_contact', $data, 'id', $id);
        $db = null;
        renderJsonResponse(200, '', array());
    }

}
$app->run();