<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';

/*路由*/

$app->post('/myBlackboards', 'getMyBlackboards');
$app->post('/details/:id', 'getDetails');

//获得我的小黑板信息
function getMyBlackboards()
{
    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;

    $pageIndex = $_POST['pageIndex'];
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize = getPageSize();
    $startIndex = ($pageIndex - 1) * $pageSize;

    $db = new Database();
    $qr = "select left(b.message_title,20) as message_title, b.id,b.class_id,b.message,b.create_at,b.publisher,c.name as class_name from biz_blackboard b inner join biz_class c on b.class_id=c.id where publisher_id=$currentTeacherId order by b.create_at desc LIMIT $startIndex, $pageSize ";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

//获取一条小黑板消息详情
function getDetails($id)
{
    $currentTeacher = getTeacherContext();
    $currentTeacherId = $currentTeacher->id;

    $db = new Database();
    $qr = "select b.*,c.name as class_name from biz_blackboard b inner join biz_class c on b.class_id=c.id where b.id=$id order by b.create_at desc";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}


$app->run();