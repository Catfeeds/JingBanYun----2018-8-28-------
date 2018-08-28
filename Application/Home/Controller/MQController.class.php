<?php
namespace Home\Controller;
use Think\Controller;
use Common\Common\MQ;

class MQController extends PublicController
{
   public function refreshUnConvertFiles2MQ()
   {
       processMQMessage(CONVERTPPT_EX_NAME,K_ROUTE,'purge');
       $result = M()->query("SELECT b.id,b.biz_bj_resource_id as mid,b.filepath,'".OSS_URL."' as oss_path,'bjresource' as category FROM biz_bj_resources
         JOIN (SELECT id,biz_bj_resource_id,resource_path as filepath,ppt_html from biz_bj_resource_contact WHERE ppt_html = 0) b
         ON b.biz_bj_resource_id = biz_bj_resources.id and biz_bj_resources.type='ppt' UNION ALL
         SELECT b.id,b.biz_resource_id as mid,b.filepath,'".OSS_URL."' as oss_path,'teacher' as category FROM biz_resource
         JOIN (SELECT id,biz_resource_id,resource_path as filepath,ppt_html FROM biz_resource_contact WHERE ppt_html = 0) b
         ON b.biz_resource_id = biz_resource.id and biz_resource.type='ppt' UNION ALL
         SELECT id,0 as mid,file_path,'".OSS_URL."' as oss_path, 'material' as category  FROM biz_material WHERE ppt_html = 0 and type = 'ppt'");
       for($i=0;$i<count($result);$i++)
       {
           $lineStr = implode(' ',$result[$i]);
           $lineArray[] = $lineStr;
       }
       processMQMessage(CONVERTPPT_EX_NAME,K_ROUTE,'push',$lineArray);
       $this->ajaxReturn(array('status' => 200, 'message' => 'refresh success'));
   }

}
