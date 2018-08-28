<?php

/*公文库路径*/
$uploaddir = "../resources/lessonplanning/";

/*接收唯一号用于标识文件名称*/
$strflsid = "2222222.ppt";

/*
读取控件发过来的二进制数据，这个二进制数据即是一个完整的OFFICE文档内容，所以保存下来就是一个文档
*/

$putdata = fopen("php://input", "r");

$fp = fopen($uploaddir . $strflsid, "w");
$i = 0;
while ($data = fread($putdata, 1024)) {
    $i++;
    fwrite($fp, $data);
}

fclose($fp);

fclose($putdata);
?>