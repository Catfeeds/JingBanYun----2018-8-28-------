<?php
namespace Home\Controller;

use Common\Common\SMS;
use Think\Controller;
use Common\Common\REDIS;
use Common\Common\Filter;
use Common\Common\Smtp;
use Common\Common\simple_html_dom;

define('HTML', 1);
define('URL', 2);
define('LOCAL_URL', 3);

class SafeScanController extends PublicController
{
    private $redis = '';
    private $redis_obj = '';

    //******************** 配置信息 ********************************
    private $smtpusermail = "yxlsafescan@126.com";//SMTP服务器的用户邮箱
    private $smtpemailto = "670718452@qq.com;241809825@qq.com";//发送给谁
    private $filter;
    private $logName ;
    private $ignoreTextCheckTable = array('jby_iplist','receive_message_user','dict_citydistrict','app_statistics','usertables');
    private $scanTableConfig = array(
        'activity_vote'=>array(
            'img_path' => URL
        ),
        'activity_vote_candidate'=>array('img_path'=>URL),
        'ad_img'=>array('file_path'=>URL),
        'auth_parent'=>array('avatar'=>URL),
        'auth_student'=>array('avatar'=>URL),
        'auth_teacher'=>array('avatar'=>URL),
        'biz_blackboard'=>array('message'=>HTML),
        'biz_exercise_library'=>array('questions'=>HTML),
        'biz_lesson_planning_contact'=>array('content'=>HTML),
        'biz_material'=>array('file_path'=>URL),
        'biz_resource_contact'=>array('resource_path'=>URL),
        'knowledge_resource'=>array(
            'pc_cover'=>URL,
            'mobile_cover'=>URL,
        ),
        'knowledge_resource_file_contact'=>array(
            'resource_path'=>URL
        ),
        'role_message'=>array('message_content'=>HTML),
        'social_activity'=>array('content'=>HTML,'short_content'=>array(LOCAL_URL,'/Resources/socialactivity/')),
        'social_activity_contact_file'=>array('activity_file_path'=>URL),
        'social_activity_works_file'=>array('works_file_path'=>URL),
        'social_expert_information'=>array(
            'content'=>HTML,
            'pc_cover'=>URL,
            'mobile_cover'=>URL,
            'pc_cover_add'=>URL,
            'mobile_cover_add'=>URL
        ),
        'weixin_push' => array('img_path' => URL));
    public function __construct($imageScanConfig=array())
    {

        //防止非本机攻击
        $REMOTE_ADDR = (isset($_SERVER['LOCAL_ADDR']) && (!empty($_SERVER['LOCAL_ADDR']))) ? $_SERVER['LOCAL_ADDR'] : $_SERVER['SERVER_ADDR'];
        if(get_client_ip() != $REMOTE_ADDR)
        {;}

        header("Content-type: text/html; charset=utf-8");
        $this->redis_obj = new \Common\Common\REDIS();
        $this->redis = $this->redis_obj->init_redis();
        $this->logName = "safeCheckLog_".date('Y-m-d',time()).'.log';
        $this->filter = new \Common\Common\Filter($this->logName);
        if(!empty($imageScanConfig))
         $this->scanTableConfig = $imageScanConfig;
        //ignore sql in this controller
        $GLOBALS['parameterUnCheck'] = 1;


    }

    private function privateSendEmail($to,$from,$title,$content)
    {
        $url = 'http://api.sendcloud.net/apiv2/mail/send';
        $API_USER = 'yxlsafescan_test_lpU1Ca';
        $API_KEY = 'API_KEY已发送到您的注册邮箱';

        //您需要登录SendCloud创建API_USER，使用API_USER和API_KEY才可以进行邮件的发送。
        $param = array(
            'apiUser' => $API_USER,
            'apiKey' => 'LgPI3jTaKHSbbth2',
            'from' =>$from,
            'fromName' => 'YXL安全检测报告',
            'to' => $to,
            'subject' => $title,
            'html' => $content,
            'respEmailId' => 'true');

        $data = http_build_query($param);

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $data
            ));

        $context  = stream_context_create($options);

        $result = file_get_contents($url, false, $context);

        return $result;
    }


    public function textPeriodScan()
    {

        set_time_limit(0);
        $model = M();
        $tableArray = $model->query('SHOW TABLES');
        $errorInfo = array();
        $allCount = 0;
        foreach ($tableArray as $database => $tables) {

            $tableName = current($tables);
            $descriptionList = $model->query("DESC $tableName");
            if(in_array($tableName,$this->ignoreTextCheckTable))
                continue;
            $checkFields = array();
            foreach ($descriptionList as $key => $val) {
                if (strpos($val['type'], 'varchar') !== false || $val['type'] == 'text') {
                    $checkFields[] = $val['field'];
                }
            }
            if (empty($checkFields))
                continue;
            $checkName = 'CONCAT(' . implode(',', $checkFields) . ') AS textfield';
            $remainCount = $model->query("SELECT COUNT(1) AS count FROM $tableName");
            $remainCount = $remainCount[0]['count'];
            $pageSize = 20;
            $currentCount = 0;

            while ($remainCount > 0) {

                if(in_array('id',array_column($descriptionList,'field')))
                    $fields = 'id,'.$checkName;
                else
                    $fields = $checkName;
                $result = $model->query("SELECT $fields FROM $tableName LIMIT $currentCount,$pageSize");
                $count = sizeof($result);
                //check records
                for ($i = 0; $i < $count; $i++) {
                    $result[$i]['textfield'] = strip_tags($result[$i]['textfield']);
                    for ($startIndex = 0; $startIndex < strlen($result[$i]['textfield']); $startIndex += 4000) {
                        $checkStr = substr($result[$i]['textfield'], $startIndex, 4000);

                        $checkResult = $this->filter->textFilter($checkStr);
                        //$checkResult = CHECK_OK;
                        $allCount++;
                        if (CHECK_BLOCK == $checkResult) {
                            //save redis result
                            if(!empty($result[$i]['id']))
                            {
                                $redis_key = $_SERVER['SERVER_NAME'] . ":textBlock:$tableName:id_{$result[$i]['id']}";
                                $this->redis->setex($redis_key, 86400 * 7, $checkStr);
                                $errorInfo[] = "文本异常: 表名:$tableName id: {$result[$i]['id']} 异常内容: ".base64_encode($checkStr).' （请base64decode后查看）</br>';
                            }
                            else
                            {
                                $redis_key = $_SERVER['SERVER_NAME'] . ":textBlock:$tableName:line_{$allCount}";
                                $this->redis->setex($redis_key, 86400 * 7, $checkStr);
                                $errorInfo[] = "文本异常: 表名:$tableName line: {$allCount} 异常内容: ".base64_encode($checkStr).' （请base64decode后查看）</br>';
                            }

                        }
                        if (CHECK_ERR == $checkResult) {
                            //interface error
                            $redis_key = $_SERVER['SERVER_NAME'] . ":textInterfaceError:$tableName:id_{$result[$i]['id']}";
                            $this->redis->setex($redis_key, 86400 * 7, $checkStr);
                        }
                    }
                }
                $remainCount -= $count;
                $currentCount += $count;
            }

        }
        $report = "<div>内容检测报告--文本扫描 扫描时间:" . date('Y-m-d H:i:s', time()) . "</div>";
        $report .= "<div>本次共扫描{$allCount}条记录</div>";
        if (empty($errorInfo)) {
            $report .= '<div>无文本出现异常</div>';
        } else {
            for ($i = 0; $i < sizeof($errorInfo); $i++) {
                $report .= $errorInfo[$i] . '</br>';
            }
        }
        date_default_timezone_set("PRC");
        $mailtitle = "内容检测报告--文本扫描";//邮件主题
        $mailcontent = $report;//邮件内容
        $state = $this->privateSendEmail($this->smtpemailto, $this->smtpusermail, $mailtitle, $mailcontent);
        $this->redis->close();
    }

    private function getImagesFromHTML($htmlContent)
    {
        $html = new simple_html_dom();
        $html->load('<html><body>' . $htmlContent . '</body></html>');
        $imageResources = $html->find('img');
        $returnArray = array();
        for ($i = 0; $i < sizeof($imageResources); $i++) {
            $returnArray[] = $imageResources[$i]->src;
        }
        return $returnArray;
    }

    public function imagePeriodScan()
    {

        set_time_limit(0);
        $model = M();
        $errorInfo = array();
        $allCount = 0;

        foreach ($this->scanTableConfig as $tableName => $fields) {
            $fieldArray = array();
            foreach ($fields as $key => $val) {
                $fieldArray[] = $key;
            }
            $checkName = implode(',', $fieldArray);
            $remainCount = $model->query("SELECT COUNT(1) AS count FROM $tableName");
            $remainCount = $remainCount[0]['count'];
            $pageSize = 20;
            $currentCount = 0;

            while ($remainCount > 0) {

                $result = $model->query("SELECT id,$checkName FROM $tableName LIMIT $currentCount,$pageSize");
                $count = sizeof($result);
                //check records
                for ($i = 0; $i < $count; $i++) {
                    foreach ($fields as $fieldName => $fieldType) {
                        $imageArray = array();
                        if (is_array($fieldType)) {
                            if ($fieldType[0] == LOCAL_URL)
                                $imageArray = array('http://' . $_SERVER['SERVER_NAME'] . $fieldType[1] . $result[$i][$fieldName]);
                        } else if ($fieldType == URL) {         //field is URL
                            if(false !== strpos($result[$i][$fieldName],'http://') || false !== strpos($result[$i][$fieldName],'https://'))
                             $imageArray = array($result[$i][$fieldName]);
                            else {
                                if(!empty($result[$i][$fieldName]))
                                $imageArray = array(C('oss_path') . $result[$i][$fieldName]);
                            }
                        } else if ($fieldType == HTML) {
                            $imageArray = $this->getImagesFromHTML($result[$i][$fieldName]);
                        }
                        if(empty($imageArray))
                            continue;
                        foreach ($imageArray as $null => $imageFullPath)
                            foreach (array( 'terrorism', 'sface') as $key => $val) {
                                usleep(300000);
                                $checkResult = $this->filter->imageSyncScanRequest($imageFullPath, $val);
                                if (CHECK_BLOCK == $checkResult) {
                                    $redis_key = $_SERVER['SERVER_NAME'] . ":imageBlock:$tableName:id_{$result[$i]['id']}";
                                    $this->redis->setex($redis_key, 86400 * 7, $imageFullPath);
                                    $errorInfo[] = "图片异常: 表名:$tableName id: {$result[$i]['id']} 异常图片路径: {$imageFullPath} 异常原因: $val";
                                    break;
                                } else if (CHECK_ERR == $checkResult) {
                                    //interface error
                                    $redis_key = $_SERVER['SERVER_NAME'] . ":imageInterfaceError:$tableName:id_{$result[$i]['id']}";
                                    $this->redis->setex($redis_key, 86400 * 7, ($imageFullPath));
                                }
                            }
                    }
                    $allCount++;
                }
                $remainCount -= $count;
                $currentCount += $count;
            }

        }
        date_default_timezone_set("PRC");
        $report = "<div>内容检测报告--图片扫描 扫描时间:" . date('Y-m-d H:i:s', time()) . "</div>";
        $report .= "<div>本次共扫描{$allCount}条记录</div>";
        if (empty($errorInfo)) {
            $report .= '<div>无图片出现异常</div>';
        } else {
            for ($i = 0; $i < sizeof($errorInfo); $i++) {
                $report .= $errorInfo[$i] . '</br>';
            }
        }

        //send report email
        $mailtitle = "内容检测报告--图片扫描";//邮件主题
        $mailcontent = $report;//邮件内容
        $state = $this->privateSendEmail($this->smtpemailto, $this->smtpusermail, $mailtitle, $mailcontent);
        $this->redis->close();

    }

    private function regExtractOnce($sql, $expression)
    {
        preg_match_all($expression, $sql, $matches);
        return array_unique(array_pop($matches));
    }

    private function splitInsertValue($str){
        $outerArray = array();
        $innerArray = array();
        $outerStart = 0;
        $innerStart = 0;
        $currentStr = '';
        for($i=0;$i<strlen($str);$i++)
        {
            switch($str[$i])
            {
                case '(':if(0 == $outerStart)
                    $outerStart = 1;
                    break;
                case ')':if(1 == $outerStart)
                {
                    if(0 == $innerStart)
                    {
                        $outerStart = 0;
                        if($currentStr!='')
                        {
                            $innerArray[] = $currentStr;
                            $currentStr = '';
                        }
                        $outerArray[] = $innerArray;
                        $innerArray = array();
                    }
                    else
                        $currentStr.=$str[$i];
                }
                    break;
                case '\'':
                    if(0 == $innerStart)
                    {
                        $innerStart = 1;
                    }
                    else
                    {
                        $innerStart = 0;
                    }
                    break;
                case '\\':$currentStr.= ($str[$i].$str[$i+1]);
                    $i++;
                    break;
                case ',': if(1 == $innerStart)
                    $currentStr.=$str[$i];
                else
                {
                    if(1 == $outerStart)
                    {
                        $innerArray[] = $currentStr;
                        $currentStr = '';
                    }
                    else
                    {
                        //
                    }
                }
                    break;
                default: $currentStr.=$str[$i];
                          break;
            }
        }
        return $outerArray;
    }

    private function getUpdateFields($str)
    {
        $str = str_replace(" ","",$str);
        $len = strlen($str);
        $isStr = false;
        $fieldArray = array();
        $currentStr = '';
        for($i=0;$i<$len;$i++)
        {
            switch($str[$i])
            {
                case '=':
                    if(!$isStr) {
                        $fieldArray[] = $currentStr;
                        $currentStr = '';
                    }
                    break;
                case '\'':
                    $isStr = !$isStr;
                    break;
                case '\\':
                    $i++;
                    break;
                case ',':  if(!$isStr)
                    $currentStr = '';
                    break;
                default:  if(!$isStr)
                    $currentStr .= $str[$i];
                    break;

            }
        }
        return $fieldArray;
    }

    private function textCheck($tableName,$textArray,$id=0)
    {
        $errorInfo = array();
        $idKey = '';
        $idException = '';
        if($id != 0)
        {
            $idKey = "id_$id:";
            $idException = " id:$id ";
        }
        for($i=0;$i<sizeof($textArray);$i++)
        for ($startIndex = 0; $startIndex < strlen($textArray[$i]); $startIndex += 4000) {
            $checkStr = substr($textArray[$i], $startIndex, 4000);
            $checkResult = $this->filter->textFilter($checkStr);
            if (CHECK_BLOCK == $checkResult) {
                //save redis result
                $redis_key = $_SERVER['SERVER_NAME'] . ":textBlock:$tableName:" .$idKey .date("Y-m-d H_i_s",time())."_".rand();
                $this->redis->setex($redis_key, 86400 * 7, $checkStr);
                $errorInfo[] = "文本异常: 表名:$tableName $idException 异常内容: ".base64_encode($checkStr). ' （请base64decode后查看）</br>';
            }
            if (CHECK_ERR == $checkResult) {
                //interface error
                $redis_key = $_SERVER['SERVER_NAME'] . ":textInterfaceError:$tableName:" .$idKey .date("Y-m-d H_i_s",time())."_".rand();
                $this->redis->setex($redis_key, 86400 * 7, $checkStr);
            }
        }
        return $errorInfo;
    }

    private function imageCheck($tableName,$imageArray,$id=0)
    {
        $errorInfo = array();
        $idKey = '';
        $idException = '';
        if($id != 0)
        {
            $idKey = "id_$id:";
            $idException = " id:$id ";
        }
        foreach ($imageArray as $null => $imageFullPath)
            foreach (array('terrorism', 'sface') as $key => $val) {
                $checkResult = $this->filter->imageSyncScanRequest($imageFullPath, $val);
                if (CHECK_BLOCK == $checkResult) {
                    $redis_key = $_SERVER['SERVER_NAME'] . ":imageBlock:$tableName:".$idKey.date("Y-m-d H_i_s",time())."_".rand();
                    $this->redis->setex($redis_key, 86400 * 7, $imageFullPath);
                    $errorInfo[] = "图片异常: 表名:$tableName $idException 异常图片路径: ".($imageFullPath)." 异常原因: $val </br>";
                    break;
                } else if (CHECK_ERR == $checkResult) {
                    //interface error
                    $redis_key = $_SERVER['SERVER_NAME'] . ":imageInterfaceError:$tableName:".$idKey.date("Y-m-d H_i_s",time())."_".rand();
                    $this->redis->setex($redis_key, 86400 * 7, ($imageFullPath));
                    //$errorInfo[] = 'image Filter query error! Image Path:'.json_encode($imageFullPath) .'</br>';
                }
            }
        return $errorInfo;
    }

    private function getCheckTextFieldsIndex($tableName, $fieldsArray = array())
    {
        $model = M();
        $descriptionList = $model->query("DESC $tableName");
        $checkFieldsIndex = array();
        for($i=0;$i<sizeof($descriptionList);$i++)
        {
            if((strpos($descriptionList[$i]['type'],'varchar') !== false || $descriptionList[$i]['type']=='text') && (in_array($descriptionList[$i]['field'],$fieldsArray) || empty($fieldsArray)))
            {
                $checkFieldsIndex[] = empty($fieldsArray)? $i-1:array_search($descriptionList[$i]['field'],$fieldsArray); //minus 1 means ignore id field
            }
        }
        return $checkFieldsIndex;
    }

    private function filterCheckTextFields($tableName, $fieldsArray )
    {
        if(empty($fieldsArray))
            return false;
        $model = M();
        $descriptionList = $model->query("DESC $tableName");
        $checkFields = array();
        for($i=0;$i<sizeof($descriptionList);$i++)
        {
            if((strpos($descriptionList[$i]['type'],'varchar') !== false || $descriptionList[$i]['type']=='text') && (in_array($descriptionList[$i]['field'],$fieldsArray) || empty($fieldsArray)))
            {
                $checkFields[] = $descriptionList[$i]['field'];
            }
        }
        return $checkFields;
    }


    private function getCheckImageFieldsIndex($tableName, $fieldsArray = array())
    {
        $model = M();
        $descriptionList = $model->query("DESC $tableName");
        $checkFieldsIndex = array();
        for($i=0;$i<sizeof($descriptionList);$i++)
        {
            if(( in_array($descriptionList[$i]['field'],array_keys($this->scanTableConfig[$tableName]))) && (in_array($descriptionList[$i]['field'],$fieldsArray) || empty($fieldsArray)))
            {
                $checkFieldsIndex[] = array(empty($fieldsArray)? $i-1:array_search($descriptionList[$i]['field'],$fieldsArray),$this->scanTableConfig[$tableName][$descriptionList[$i]['field']]); //minus 1 means ignore id field
            }
        }
        return $checkFieldsIndex;
    }

    private function filterCheckImageFields($tableName, $fieldsArray = array())
    {
        $model = M();
        $descriptionList = $model->query("DESC $tableName");
        $checkFields = array();
        for($i=0;$i<sizeof($descriptionList);$i++)
        {
            if(( in_array($descriptionList[$i]['field'],array_keys($this->scanTableConfig[$tableName]))) && (in_array($descriptionList[$i]['field'],$fieldsArray) || empty($fieldsArray)))
            {
                $checkFields = array_merge($checkFields,array($descriptionList[$i]['field'] => $this->scanTableConfig[$tableName][$descriptionList[$i]['field']]));
            }
        }
        return $checkFields;
    }

    private function getUnderCheckText($tableName,$fields,$condition)
    {
        if(empty($fields))
            return array();
        if(empty($condition))
            $condition = " 1=1 ";
        $sql = "SELECT id,CONCAT(".implode(',',$fields).") AS text FROM $tableName WHERE $condition";
        try {
            $result = M()->query($sql);
        }catch(Exception $e)
        {
            $sql = "SELECT CONCAT(".implode(',',$fields).") AS text FROM $tableName WHERE $condition";
            $result = M()->query($sql);
        }
        $arrayReturn = array();
        $len = sizeof($result);
        for($i=0;$i<$len;$i++)
        {
            $arrayReturn[] = array('id'=>$result[$i]['id'],'text'=>$result[$i]['text']);
        }
        return $arrayReturn;
    }

    private function getUnderCheckImagesList($tableName,$fields,$condition)
    {
        if(empty($fields))
            return array();
        if(empty($condition))
            $condition = " 1=1 ";

        $sql = "SELECT id,".implode(',',array_keys($fields))." FROM $tableName WHERE $condition";
        try {
            $result = M()->query($sql);
        }catch(Exception $e)
        {
            $sql = "SELECT ".implode(',',array_keys($fields))." FROM $tableName WHERE $condition";
            $result = M()->query($sql);
        }
        $arrayReturn = array();
        $len = sizeof($result);
        for($i=0;$i<$len;$i++)
        {
            $id = 0;
            $data = array();

            foreach($result[$i] as $key => $val)
            {

                if($key == 'id')
                    $id = $val;
                else
                {
                    $fieldType = $fields[$key];

                    $imageArray = array();
                    if (is_array($fieldType)) {
                        if ($fieldType[0] == LOCAL_URL)
                            $imageArray = array('http://' . $_SERVER['SERVER_NAME'] . $fieldType[1] . $val);

                    } else if ($fieldType == URL) {         //field is URL
                        if(false !== strpos($val,'http://') || false !== strpos($val,'https://'))
                            $imageArray = array($val);
                        else
                            $imageArray = array(C('oss_path') . $val);
                    } else if ($fieldType == HTML) {
                        $imageArray = $this->getImagesFromHTML($val);
                    }
                    if(!empty($imageArray))
                    $data = array_merge($data,$imageArray);
                }
            }
            $arrayReturn[] = array('id'=>$id,'images'=>$data);
        }
        return $arrayReturn;

    }

    private function getCheckContentList($valueList,$checkIndexList){
        $checkIndexArray = array();
        for($i=0;$i<sizeof($valueList);$i++)
        {
            $str = '';
            for($j=0;$j<sizeof($checkIndexList);$j++)
            {
                $str .= $valueList[$i][$checkIndexList[$j]];
            }
            $checkIndexArray[] = $str;
        }
        return $checkIndexArray;
    }

    private function getCheckImageList($valueList,$checkIndexList){
        $checkImageArray = array();
        for($i=0;$i<sizeof($valueList);$i++)
        {
            $innerImageArray = array();
            for($j=0;$j<sizeof($checkIndexList);$j++)
            {
                //$checkIndexList[$j][0] -- index $checkIndexList[$j][1] -- URL HTML array(...)

                $imageArray = array();

                if (is_array($checkIndexList[$j][1])) {
                    if ($checkIndexList[$j][1][0] == LOCAL_URL)
                        $imageArray = array('http://' . $_SERVER['SERVER_NAME'] . $checkIndexList[$j][1][1] . $valueList[$i][$checkIndexList[$j][0]]);
                } else if ($checkIndexList[$j][1] == URL) {         //field is URL
                    if(false !== strpos($valueList[$i][$checkIndexList[$j][0]],'http://') || false !== strpos($valueList[$i][$checkIndexList[$j][0]],'https://'))
                        $imageArray = array($valueList[$i][$checkIndexList[$j][0]]);
                    else
                    {
                        if(!empty($valueList[$i][$checkIndexList[$j][0]]))
                        $imageArray = array(C('oss_path') . $valueList[$i][$checkIndexList[$j][0]]);
                    }

                } else if ($checkIndexList[$j][1] == HTML) {
                    $imageArray = $this->getImagesFromHTML($valueList[$i][$checkIndexList[$j][0]]);
                }
                $innerImageArray= array_merge($innerImageArray,$imageArray);
            }
            if(!empty($innerImageArray))
            $checkImageArray[] = $innerImageArray;
        }
        return $checkImageArray;
    }

    private function deleteRecord($tableName,$id)
    {
        M($tableName)->where('id='.$id)->delete();
    }

    private function clearRecord($tableName,$fieldsArray,$id)
    {
        $data = array();
        foreach($fieldsArray as $key => $val)
        {
            $data[$val] = '';
        }
        M($tableName)->where('id='.$id)->save($data);
    }

    public function sqlSafeCheck($sql='',$firstId=0)
    {
        $sql = empty($sql)?$_POST['sql']:$sql;
        $sql = preg_replace("#\\\u([0-9a-f]+)#ie","iconv('UCS-2','UTF-8', pack('H4', '\\1'))",$sql);

        $firstId = empty($firstId)?$_POST['firstId']:$firstId; //for insert sql
        if(strpos($sql,'user_access') !== false)
            exit;

        $sql = str_replace("`","",$sql);
        if(strpos($sql,'INSERT') === 0){

            $tableNameExpression = '/INSERT\\s+?INTO[\\s`]*?(\\w+)[\\s`]*?/is';
            $fieldsExpression = '/INSERT\\s+?INTO[\\s`]*?\\w+[\\s`]*?(\(.+\))[\\s]+VALUES/is';
            $valuesExpression = '/INSERT\\s+?INTO.+VALUES[\\s]*(\(.+\))*/is';
            $tableName = $this->regExtractOnce($sql,$tableNameExpression)[0];
            $fieldsTestResult = $this->regExtractOnce($sql,$fieldsExpression);

            if(is_array($fieldsTestResult) && !empty($fieldsTestResult))
            {
                //has FIELDS expression
                $fields = str_replace("'","",$fieldsTestResult[0]);
                $fields = str_replace("(","",$fields);
                $fields = str_replace("`","",$fields);
                $fields = str_replace(")","",$fields);
                $fieldsArray = explode(',',$fields);
                $checkTextIndexList = $this->getCheckTextFieldsIndex($tableName,$fieldsArray);
                $checkImageIndexList = $this->getCheckImageFieldsIndex($tableName,$fieldsArray);

            }
            else
            {
                //TODO: parse insert into table select ...
                $checkTextIndexList = $this->getCheckTextFieldsIndex($tableName);
                $checkImageIndexList = $this->getCheckImageFieldsIndex($tableName);
            }
            /************TEXT check***********/
            //extract values
            $values = $this->regExtractOnce($sql,$valuesExpression);
            $valueList = $this->splitInsertValue($values[0]);

            $checkTextArray = $this->getCheckContentList($valueList,$checkTextIndexList);
            $errorInfo = array();
            for($i=0;$i<sizeof($checkTextArray);$i++) {
                if(!empty($checkTextArray[$i]))
                {
             //       $result =  $this->textCheck($tableName,array($checkTextArray[$i]),$firstId+$i);
                    if(!empty($result))
                    {
                        $errorInfo[] = $result;
                        //remove record
//                        $this->deleteRecord($tableName,$firstId+$i);
                    }
                }
            }

            /*********** IMAGE check ***********/
            $checkImageArray = $this->getCheckImageList($valueList,$checkImageIndexList);
            //var_dump($checkImageArray);exit;
            for($i=0;$i<sizeof($checkImageArray);$i++) {
                if(!empty($checkImageArray[$i]))
                {
                    $result = $this->imageCheck($tableName,($checkImageArray[$i]),$firstId+$i);
                    if(!empty($result))
                    {
                        $errorInfo[] = $result;
//                        $this->deleteRecord($tableName,$firstId+$i);
                        //remove record
                    }
                }
            }
            if(!empty($errorInfo))
            {
                $mailtitle = "插入内容异常";//邮件主题
                $mailcontent = '';

                foreach($errorInfo as $key=>$val)
                {
                    $mailcontent .= implode('',$val);
                }
                //$this->redis->setex(time(), 86400 * 7, $sql);
                $state = $this->privateSendEmail($this->smtpemailto, $this->smtpusermail, $mailtitle, $mailcontent);
            }

        }
        else if(strpos($sql,'UPDATE') === 0)
        {
            // UPDATE 正则条件
            $tableNameExpression = '/UPDATE[\\s`]*?(\\w+)[\\s`]*?/is';
            $tableName = $this->regExtractOnce($sql,$tableNameExpression)[0];

            //extract update fields and where condition
            preg_match("/UPDATE +([0-9a-z_]+) +SET *(,?.*=.*)+( +WHERE +(.+))/is",$sql,$regs);
            $fields =  $this->getUpdateFields($regs[2]);
            $condition = $regs[4];
            if(empty($condition))
            {
                $redis_key = $_SERVER['SERVER_NAME'] . ":emptyCondition:$tableName:".date("Y-m-d H_i_s",time())."_".rand();
                $this->redis->setex($redis_key, 86400 * 7, $sql);
                exit;
            }
            //filter fields
            $textFilterFields = $this->filterCheckTextFields($tableName,$fields);
            $imageFilterFields = $this->filterCheckImageFields($tableName,$fields);
            //get value from table
            $underCheckText = $this->getUnderCheckText($tableName,$textFilterFields,$condition);
            $underCheckImages = $this->getUnderCheckImagesList($tableName,$imageFilterFields,$condition);
            //1.text check
            foreach($underCheckText as $key => $val)
            {
                if(!empty($val['text']))
                {
                    $result = $this->textCheck($tableName,array($val['text']),$val['id']);
                    if(!empty($result))
                    {
                        $errorInfo[] = $result;
//                        $this->clearRecord($tableName,$textFilterFields,$val['id']);
                        //clear update result

                    }
                }
            }

            //2.image check
            foreach($underCheckImages as $key => $val)
            {
                if(!empty($val['images']))
                {
                    $result = $this->imageCheck($tableName,$val['images'],$val['id']);
                    if(!empty($result))
                    {
                        $errorInfo[] = $result;
//                        $this->clearRecord($tableName,$imageFilterFields,$val['id']);
                        //clear update result
                    }
                }
            }
            if(!empty($errorInfo))
            {
                $mailtitle = "更新内容异常";//邮件主题
                $mailcontent = '';

                foreach($errorInfo as $key=>$val)
                {
                    $mailcontent .= implode('',$val);
                }
                //$this->redis->setex(time(), 86400 * 7, $sql);
                $state = $this->privateSendEmail($this->smtpemailto, $this->smtpusermail, $mailtitle, $mailcontent);
            }

        }
        $this->redis->close();
    }

}