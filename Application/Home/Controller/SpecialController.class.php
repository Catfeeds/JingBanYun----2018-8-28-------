<?php
namespace Home\Controller;

use Think\Controller;
class SpecialController extends Controller
{
	public function unusedVidList(){
		set_time_limit(0);
        $res = file_get_contents('http://v.polyv.net/uc/services/rest?method=getNewList&readtoken=95402908-8fc2-4328-a4cf-4f49601a5812&pageNum=1');
        $fileCount = json_decode($res)->total;

        $databaseVid = M()->query('select vid from (select vid from biz_bj_resource_contact union select vid from biz_resource_contact union select vid from biz_material
        union select vid from social_activity_contact_file union select vid from social_activity_works_file UNION SELECT mp3_vid vid FROM jingtongcloud.biz_exercise_library) a where vid is not null and vid <> \'\'');
        $vidArray = array_column($databaseVid,'vid');
        $res = file_get_contents('http://v.polyv.net/uc/services/rest?method=getNewList&readtoken=95402908-8fc2-4328-a4cf-4f49601a5812&pageNum=1'.'&numPerPage='.$fileCount);
        $allData = json_decode($res)->data;
        $delArray = array();
        for($i=0;$i<sizeof($allData);$i++)
        {
            //var_dump($allData[$i]->vid);
            if(!in_array($allData[$i]->vid,$vidArray))
            {
                $delArray[] = $allData[$i]->vid;
            }
        }
        var_dump($delArray);exit;
	}
}
