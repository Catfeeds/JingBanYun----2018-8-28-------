<?php
define('HOSTNAME',':802');
return array(
    //'配置项'=>'配置值'
    'CONTROLLER_LEVEL' => 2,
    'URL_MODEL' => 2,
    'MESSAGE_SWITCH' => 1, //1为关闭 2 开
    'IOS_SEND_PUSH' => array(
        'default_apiKey' => 'eketfomf1M3x05svL4xxm2eK',
        'default_secretkey' => 'UcU7ln5EWt9hjDTG3GGnCGacYL3TGPj5',
    ),
    'ANDROID_SEND_PUSH' => array(
        'default_apiKey' => 'bGTXvnRG4MEfAwXaVNQ0Gqzz',
        'default_secretkey' => '7a6xppOPN4WDm88T9ifoPHIbUfKbwxWR',
    ),
    //绘本的题材
    'PICTURE_BOOK_SUBJECT' => array(
        array(
            'id' => 1,
            'name' => '小说',
        ),
        array(
            'id' => 2,
            'name' => '非小说'
        )

    ),
    //绘本的主题
    'PICTURE_BOOK_THEME' => array(
        array(
            'id' => 1,
            'name' => '分享',
        ),
        array(
            'id' => 2,
            'name' => '敬老'
        ),

        array(
            'id' => 3,
            'name' => '劳动'
        ),
        array(
            'id' => 4,
            'name' => '动物智慧'
        )
    ),
    'questionCategory' => array(  //试卷试题类型

        1=>'小升初',
        2=>'中考',
        3=>'高考',
        4=>'期中',
        5=>'期末',
        6=>'阶段测试',
    ),
    'paperCity' => array(  //试卷类型
        1=>"真题",
        2=>"模拟题",
    ),

    'APIdifficulty'=>array(
        1=> "基础",
        2=> "中等",
        3=> "难题",
        4=> "竞赛",
    ),
);