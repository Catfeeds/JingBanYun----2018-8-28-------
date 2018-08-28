<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';

/*路由*/
$app->post('/all', 'getAllActivities');
$app->post('/details/:id', 'getActivityById');
$app->get('/details/:id', 'getActivityById');
$app->post('/getfirst', 'getFirstActivity');
$app->get('/getfirst', 'getFirstActivity');
$app->post('/register', 'register');    //post
$app->post('/hasRegistered', 'hasRegistered');

$app->post('/classlist','getActivitiesClass');
$app->post('/getmyfavor','getMyFavorActivities');
$app->get('/getmyfavor','getMyFavorActivities');
$app->post('/list','getActivityByCategory');
$app->get('/list','getActivityByCategory');

//根据类型获取京版活动
function getActivityByCategory()
{
    $pageIndex = $_REQUEST['pageIndex'];
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize = getPageSize();
    $startIndex = ($pageIndex - 1) * $pageSize;
    $cate = $_REQUEST['cate'];

    $db = new Database();
    if(!empty($cate))
    $qr = "select id,title,update_at,short_content,'北京出版集团'as publisher,if(UNIX_TIMESTAMP(NOW())-604800>create_at,'no','yes') is_new from social_activity where status=5 and class_id='" .$cate."' order by update_at desc LIMIT $startIndex, $pageSize ";
    else
    $qr = "select id,title,update_at,short_content,'北京出版集团'as publisher,if(UNIX_TIMESTAMP(NOW())-604800>create_at,'no','yes') is_new from social_activity where status=5 order by update_at desc LIMIT $startIndex, $pageSize ";

    $sel = $db->fetch_custom($qr, array());     

    $result = pushToArray($sel);            
    foreach($result as &$val)
    {
        $val->img_url = "http://".WEB_URL."/Resources/socialactivity/".$val->short_content;
    }
    $db = null; 

    renderJsonResponse(200, '', $result);
}
//获取京版活动种类列表
function getActivitiesClass()
{
    $db = new Database();
    $qr = "select id,class,sort_order from social_activity_class";
    $sel = $db->fetch_custom($qr,array());
    $result = pushToArray($sel);
    $db = null;
    renderJsonResponse(200, '', $result);
}
//获取我收藏的活动
function getMyFavorActivities()
{
    $userid = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']):'';
    $role = isset($_REQUEST['role']) ? intval($_REQUEST['role']):'';

    $pageIndex = isset($_REQUEST['pageIndex']) ? intval($_REQUEST['pageIndex']):'';
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize =  getPageSize();
    $startIndex =($pageIndex - 1) * $pageSize;

    $role++;

    $db = new Database();
    $qr = "select social_activity.id,title,update_at,short_content,publisher from social_activity join
    (select id,social_activity_id,user_id,user_type,favor_time from social_activity_favor where user_type=$role and user_id = $userid) a
     on a.social_activity_id = social_activity.id
     where status=5 order by update_at desc LIMIT $startIndex, $pageSize ";
    //$qr = "select id,social_activity_id,user_id,user_type,favor_time from social_activity_favor where user_type=$role and user_id = $userid";
    $sel = $db->fetch_custom($qr,array());
    $result = pushToArray($sel);
    foreach($result as &$val)
    {
        $val->img_url = "http://".WEB_URL."/Resources/socialactivity/".$val->short_content;
    }
    $db = null;
    renderJsonResponse(200, '', $result);
}
//获取所有京版活动
function getAllActivities()
{
    $pageIndex = $_POST['pageIndex'];
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize = getPageSize();
    $startIndex = ($pageIndex - 1) * $pageSize;

    $db = new Database();
    $qr = "select id,title,update_at,short_content,publisher from social_activity where status=5 order by update_at desc LIMIT $startIndex, $pageSize ";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

//获取京版活动详情byId
function getActivityById($id)
{
    $db = new Database();
    $userid = $_REQUEST['user_id'];
    $role = $_REQUEST['role'];
    $role = $role + 1;
    $updateBrowseCount = "update social_activity set browse_count=browse_count+1 where id=$id";     
    $db->fetch_custom($updateBrowseCount);
    $qr = "select r.*,(case when a.social_activity_id is null then 0 else 1 end ) as isfavor,
                  (case when b.social_activity_id is null then 0 else 1 end ) as isZan
                  from (select * from social_activity where id=$id) r left join
              (select social_activity_id from social_activity_favor where social_activity_id =$id and user_id=$userid and user_type=$role) a on r.id = a.social_activity_id left join
              (select social_activity_id from social_activity_zan where social_activity_id =$id and user_id=$userid and user_type=$role) b on r.id=b.social_activity_id";
     
    $sel = $db->fetch_custom($qr,array());
    $result = pushToArray($sel);    
    if($result[0]->is_upload==1){
        /*'<div style="color:red;"><h2>[该活动含有附件,不支持在手机报名]</h2></div>'*/
        $result[0]->content='<div style="color:red;"><h2>[该活动含有附件,不支持在手机报名]</h2></div>'.$result[0]->content;
    }
    $result[0]->contentWithHeader = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0"/><meta name="format-detection" content="telephone=no,email=no,date=no,address=no"><title></title> <style>img{max-width: 100%; width:auto; height:auto;}</style></head><body>' .$result[0]->content .'</body></html>';
    renderJsonResponse(200, '', $result);
    $db = null;
}

//获取最新发布的京版活动
function getFirstActivity()
{
    $db = new Database();
    $cate = $_REQUEST['cate'];
    if(empty($cate))
    $qr = "select id,title,update_at,short_content,publisher from social_activity where status=5 order by update_at desc limit 0,1";
    else
    $qr = "select id,title,update_at,short_content,publisher from social_activity where status=5 and class_id=$cate order by update_at desc limit 0,1";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    foreach($result as &$val)
    {
        $val->img_url = "http://".WEB_URL."/Resources/socialactivity/".$val->short_content;
    }
    $db = null;

    renderJsonResponse(200, '', $result);
}

//活动报名
function register()
{   
    $currentTeacher = getTeacherContext();      
    $currentTeacherId = $currentTeacher->id;        
    $currentTeacherName = $currentTeacher->name;         
  
    $reason = $_POST['reason'];     //post
    $id = $_POST['activity_id'];    //post
    $db = new Database();
    
    $sql="select id,is_upload from social_activity where id=".$id;      
    $sel = $db->fetch_custom($sql, array());
    $result = pushToArray($sel);        
    if($result[0]->is_upload==1){
        renderJsonResponse(201, '抱歉该活动含有附件,不支持在手机报名', array());
        $db = null;
    }else{  
        $data = array(
            'activity_id' => $id,
            'user_id' => $currentTeacherId,
            'user_name' => $currentTeacherName,
            'register_at' => time(),
            'register_info' => $reason,
            'user_type' => 1
        );
        $db->insert("social_activity_register", $data);
        renderJsonResponse(200, '报名成功', array());
        $db = null;
    }
}

function hasRegistered()
{
    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;

    $id = $_POST['activity_id'];
    $db = new Database();
    $qr = "select * from social_activity_register where activity_id=$id and user_id=$currentTeacherId";
    $cust = $db->fetch_custom($qr, array());
	$result = pushToArray($cust);
    if (!empty($result)) {
        renderJsonResponse(200, "已经注册", array($result));
    } else {
        renderJsonResponse(200, '尚未注册', array());
    }
    $db = null;
}

$app->run();