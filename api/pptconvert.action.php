<?php
include 'DbHelper/Config.php';
include 'Common/SlimRegister.php';


/*路由*/
$app->get('/query_unconvert', 'query_unconvert');
$app->get('/update_specPPT', 'update_specPPT');

function query_unconvert()
{
    $db = new Database();
    $qr="SELECT b.id,b.resource_id as mid,b.filepath,'".OSS_URL."' as oss_path,'bjresource' as category FROM knowledge_resource
         JOIN (SELECT id,resource_id,resource_path as filepath,ppt_html from knowledge_resource_file_contact WHERE ppt_html = 0) b
         ON b.resource_id = knowledge_resource.id and knowledge_resource.file_type='ppt' UNION ALL
         SELECT b.id,b.biz_resource_id as mid,b.filepath,'".OSS_URL."' as oss_path,'teacher' as category FROM biz_resource
         JOIN (SELECT id,biz_resource_id,resource_path as filepath,ppt_html FROM biz_resource_contact WHERE ppt_html = 0) b
         ON b.biz_resource_id = biz_resource.id and biz_resource.type='ppt' UNION ALL
         SELECT id,0 as mid,file_path,'".OSS_URL."' as oss_path, 'material' as category  FROM biz_material WHERE ppt_html = 0 and type = 'ppt'";
     $sel = $db->fetch_custom($qr, array());
     $result = pushToArray($sel);
     $db = null;
     renderJsonResponse(200, '', $result);

}

function update_specPPT()
{
   $db = new Database();
   $id = $_GET['id'];
   $category = $_GET['category'];
   $pages = $_GET['pages'];
   $renderData = array();
   $data = array(
      'ppt_html' => 1,
      'ppt_pages' => $pages
   );
   switch($category)
   {
   case 'bjresource':
                    $tableName = 'knowledge_resource_file_contact';
                    break;
   case 'teacher':  $tableName = 'biz_resource_contact';
                    break;
   case 'material': $tableName = 'biz_material';
                    break;
   default:         $tableName = '';
                    break;
   }
   if($tableName != '')
   {
    $db->update($tableName, $data, 'id', $id);
    $renderData = array('ok');
   }
   $db = null;
   renderJsonResponse(200, '', $renderData);
}


$app->run();