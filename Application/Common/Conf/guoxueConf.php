<?php
$REMOTE_ADDR = (isset($_SERVER['LOCAL_ADDR']) && (!empty($_SERVER['LOCAL_ADDR']))) ? $_SERVER['LOCAL_ADDR'] : $_SERVER['SERVER_ADDR'];
define('TOUPIAO_ID','34');
define('HUODONG_ID','536');
define('ZIXUN_ID','280');
$subResourceIdList = [];
switch($REMOTE_ADDR)
{
    case '123.56.145.63'://开发机
        define('GUOXUE_ID',10615);
        $subResourceIdList = [1,2,3,4,5];
        break;
    case '118.190.65.33'://测试机*067112ECD88214BE90E11B2A07D5B0DC51D80C3E
        define('GUOXUE_ID',10615);
        $subResourceIdList =  [10684,10685,10686,10687,10688,10689,10690,10691,10692,10693,10694];
        break;
    case '115.28.78.221'://测试机*067112ECD88214BE90E11B2A07D5B0DC51D80C3E
        define('GUOXUE_ID',10615);
        $subResourceIdList = [1,2,3,4,5];
        break;
    case '114.215.106.208'://正式机
        if(strpos(dirname(__FILE__),'home/wwwtest')!==false)
        {
            define('GUOXUE_ID',11114);
            $subResourceIdList = [6483,6484,6485,6486,6487,6488,6489,6490,6491,6492,6493];
        }elseif(strpos(dirname(__FILE__),'home/wwwroot')!==false)
        {
            define('GUOXUE_ID',11114);
            $subResourceIdList = [12655, 12656, 12657, 12658, 12659,12660,12661,12662,12663,12664,12665];
        }
        elseif(strpos(dirname(__FILE__),'home/wwwloadsource')!==false) {
            define('GUOXUE_ID', 11114);
            $subResourceIdList = [12655, 12656, 12657, 12658, 12659,12660,12661,12662,12663,12664,12665];
        }
        break;
    default :
        define('GUOXUE_ID',10615);
        $subResourceIdList = [10056,10059,10060,10061,10062,10063,10064,10065,10066,10067,10068];
        break;
}
define('GUOXUE_SUBRESOURCE_IDLIST',json_encode($subResourceIdList));