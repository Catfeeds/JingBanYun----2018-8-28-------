<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';

/*路由*/
$app->post('/all', 'getAllInformation');
$app->get('/all', 'getAllInformation');
$app->post('/details/:id', 'getInformationDetails');
$app->post('/getfirst', 'getFirstExpertInformation');
//获取专家资讯列表
function getAllInformation()
{
    $pageIndex = $_REQUEST['pageIndex'];
    $pageIndex = empty($pageIndex) ? 1 : (int)$pageIndex;
    $pageSize = getPageSize();
    $startIndex = ($pageIndex - 1) * $pageSize;

    $db = new Database();
    $qr = "select social_expert_information.id,title,update_at,short_content,publisher,nickname,name from social_expert_information join auth_admin on auth_admin.id=social_expert_information.publisher_id"
            . " where status=2 order by update_at desc LIMIT $startIndex, $pageSize ";
    $sel = $db->fetch_custom($qr, array()); 
    $result = pushToArray($sel);
        
    foreach($result as &$val)
    {
        $val->img_url = "http://".WEB_URL."/Resources/expertinformation/".$val->short_content; 
        $publisher_name=$val->nickname?$val->nickname:$val->name;
        $val->publisher=$publisher_name;
    }
    $db = null;
 
    renderJsonResponse(200, '', $result);
}
//获取最新发布的专家资讯
function getFirstExpertInformation()
{
    $db = new Database();
    $qr = "select id,title,update_at,short_content,publisher from social_expert_information where status=2 order by update_at desc limit 0,1";
    $sel = $db->fetch_custom($qr, array());
    $result = pushToArray($sel);
    $db = null;

    renderJsonResponse(200, '', $result);
}
//获取专家资讯详情
function getInformationDetails($id)
{
    $db = new Database();
    $updateBrowseCount = "update social_expert_information set browse_count=browse_count+1 where id=$id";
    $db->fetch_custom($updateBrowseCount);
    $info = $db->fetch_single_row('social_expert_information', 'id', $id);
    $info->contentWithHeader = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0"/><meta name="format-detection" content="telephone=no,email=no,date=no,address=no"><title></title> <style>img{max-width: 100%; width:auto; height:auto;}</style></head><body>' .$info->content .'</body></html>';
    renderJsonResponse(200, '', $info);
    $db = null;
}

$app->run();