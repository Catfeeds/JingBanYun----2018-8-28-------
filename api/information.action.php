<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';

/*路由*/

$app->post('/latest', 'getLatestInformation'); //返回前5条最新的信息
$app->post('/all', 'getAllInformation');
$app->post('/details/:id', 'getInformationDetails');


function getLatestInformation()
{
    $db = new Database();
    $qr = "select id,title,update_at,cover_image,brief_content from biz_information where status=2 order by update_at desc limit 0,4";//status 2 已审核:
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

function getAllInformation()
{
    $db = new Database();
    $qr = "select id,title,update_at,cover_image,brief_content from biz_information where status=2 order by update_at desc";//status 2 已审核:
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}

function getInformationDetails($id)
{
    $db = new Database();
    $info = $db->fetch_single_row('biz_information', 'id', $id);
    renderJsonResponse(200, '', $info);
    $db = null;
}

$app->run();