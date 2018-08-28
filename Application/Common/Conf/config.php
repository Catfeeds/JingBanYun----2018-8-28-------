<?php
define ('RUN_SUCCESS',1);
define ('RUN_FAIL',0);
define ('APIINTERFACE_DIR','Version1_1');
$REMOTE_ADDR = (isset($_SERVER['LOCAL_ADDR']) && (!empty($_SERVER['LOCAL_ADDR']))) ? $_SERVER['LOCAL_ADDR'] : $_SERVER['SERVER_ADDR'];

//消息队列参数
define('HOST','118.190.65.61');
define('PORT','5672');
define('USER','admin');
define('PASS','Jingbanyun426!');
define('VHOST','');

define('CONVERTPPT_EX_NAME','direct');
define('K_ROUTE','convertQueue');
define('IGNOREINFO_ACTIVITY_ID',512);
define('LOCAL_AVATAR_TEMP_DIR','./tmp/images/');
define('PRICE_TYPE','元/套');
define('PRICE_SING','￥');

switch($REMOTE_ADDR)
{
    case '123.56.145.63'://开发机
        define('CFG_DB_HOST','123.56.145.63');
        define('CFG_DB_NAME','jingtongcloud');
        define('CFG_DB_USER','arthur');
        define('CFG_DB_PWD','password');
        define('PUSHCONFIG',1); //1为开发2为生产
        //WEB_URL域名
        define('ANDROID_PUSH_KEY','CHANHBW8GHGGYRcnhSpyVxvs');
        define('ANDROID_PUSH_SECRET','p9lD29ukXEamAynEdOOXRaffEiet3AvK');
        define('WEB_URL','jby.zhongguo789.com');
        define('DISPLAY_TEACHERSTYLE',1);
        break;
    case '118.190.65.33'://测试机*067112ECD88214BE90E11B2A07D5B0DC51D80C3E
        define('CFG_DB_HOST','118.190.65.33');
        define('CFG_DB_NAME','jingtongcloud');
        define('CFG_DB_USER','root');
        define('CFG_DB_PWD','Jby&*2016');
        define('PUSHCONFIG',1);
        define('ANDROID_PUSH_KEY','tgSaV9Fo4jXukoB6cUxbNpSd');
        define('ANDROID_PUSH_SECRET','mUqIoR5Uu790AK53OBH5ycG1FlTrYEpS');
        //WEB_URL域名
        define('WEB_URL','www.jtypt.com');
        define('DISPLAY_TEACHERSTYLE',1);
        define('WEB_URL_DIR_SUP','/home/wwwroot/');
        break;
    case '115.28.78.221'://测试机*067112ECD88214BE90E11B2A07D5B0DC51D80C3E
        define('CFG_DB_HOST','rm-2zec5i4666piy4fi2.mysql.rds.aliyuncs.com');
        define('CFG_DB_NAME','jingtongcloud');
        define('CFG_DB_USER','jbyserver');
        define('CFG_DB_PWD','Jby&*@_2016');
        define('PUSHCONFIG',2);
        define('ANDROID_PUSH_KEY','bGTXvnRG4MEfAwXaVNQ0Gqzz');
        define('ANDROID_PUSH_SECRET','UcU7ln5EWt9hjDTG3GGnCGacYL3TGPj5');
        //WEB_URL域名
        define('WEB_URL','');
        define('WEB_URL_DIR_SUP','/home/webjtypt/');

        break;
    case '114.215.106.208'://正式机
        if(strpos(dirname(__FILE__),'home/wwwtest')!==false)
        {
            define('CFG_DB_HOST','localhost');
            define('CFG_DB_NAME','jingtongcloud');
            define('CFG_DB_USER','root');
            define('CFG_DB_PWD','Jby&*2016');
            define('PUSHCONFIG',1);
            define('ANDROID_PUSH_KEY','tgSaV9Fo4jXukoB6cUxbNpSd');
            define('ANDROID_PUSH_SECRET','mUqIoR5Uu790AK53OBH5ycG1FlTrYEpS');
            //WEB_URL域名
            define('WEB_URL','test.jingbanyun.com');
            define('WEB_URL_DIR_SUP','/home/wwwtest/');
        }elseif(strpos(dirname(__FILE__),'home/wwwroot')!==false)
        {
            define('CFG_DB_HOST','rm-2zec5i4666piy4fi2.mysql.rds.aliyuncs.com');
            define('CFG_DB_NAME','jingtongcloud');
            define('CFG_DB_USER','jbyserver');
            define('CFG_DB_PWD','Jby&*@_2016');
            define('PUSHCONFIG',2);
            define('ANDROID_PUSH_KEY','bGTXvnRG4MEfAwXaVNQ0Gqzz');
            define('ANDROID_PUSH_SECRET','UcU7ln5EWt9hjDTG3GGnCGacYL3TGPj5');
            //WEB_URL域名
            define('WEB_URL','www.jingbanyun.com');
            define('WEB_URL_DIR_SUP','/home/wwwroot/');
        }
        elseif(strpos(dirname(__FILE__),'home/wwwloadsource')!==false)
        {
            define('CFG_DB_HOST','localhost');
            define('CFG_DB_NAME','jingtongloadresource');
            define('CFG_DB_USER','root');
            define('CFG_DB_PWD','Jby&*2016');
            define('PUSHCONFIG',1);
            define('ANDROID_PUSH_KEY','tgSaV9Fo4jXukoB6cUxbNpSd');
            define('ANDROID_PUSH_SECRET','mUqIoR5Uu790AK53OBH5ycG1FlTrYEpS');
            //WEB_URL域名
            define('WEB_URL','loadsource.jingbanyun.com');
        }
        define('DISPLAY_TEACHERSTYLE',0);
        break;
    default :
        define('CFG_DB_HOST','118.190.65.33');
        define('CFG_DB_NAME','jingtongcloud');
        define('CFG_DB_USER','root');
        define('CFG_DB_PWD','Jby&*2016');
        define('WEB_URL','www.jtypt.com');
        
        
        /*define('CFG_DB_HOST','localhost');
        define('CFG_DB_NAME','jingtongcloud');
        define('CFG_DB_USER','root');
        define('CFG_DB_PWD',''); */


        define('PUSHCONFIG',1);
        define('ANDROID_PUSH_KEY','tgSaV9Fo4jXukoB6cUxbNpSd');
        define('ANDROID_PUSH_SECRET','mUqIoR5Uu790AK53OBH5ycG1FlTrYEpS');
        define('DISPLAY_TEACHERSTYLE',1);
        break;
}



$androidPushKey = array( 'default_apiKey' => ANDROID_PUSH_KEY , 'default_secretkey' => ANDROID_PUSH_SECRET );
return array(
    'DB_CONFIG_ELASTICSEARCH'=> array ('DB_TYPE' => 'elasticsearch', 'DB_HOST' => '118.190.65.33','DB_PORT'=>'9200', 'DB_INDEX' => 'article','DB_TABLE'=>'article'),
    'IGNOREINFO_ACTIVITY_ID' => array(514,515),
    'IGNORE_WARNING_URL' => array(
        '/index.php?m=home&c=textbook&a=textbookdetails',
        '/ApiInterface/Version1_1/KnowledgeResource/undefined',
        '/index.php?m=home&c=schooljoin&a=schooljoin',
        '/index.php?m=home&c=index&a=schooljoin',
        '/index.php?m=wap&a=index&siteid=1',
        '/index.php?m=member&c=index&a=register&siteid=1',
        '/index.php?m=home&c=bjresource&a=bjresourcelist&keyword=atp&course=9&sort_column=6',
        '/index.php?m=home&c=student&a=forgetpassword&',
        '/index.php?m=home&c=teach&a=forgetpassword&',
        '/index.php?m=home&c=parent&a=forgetpassword&',
        '/index.php?m=Admin&c=Login&a=login&m=wap&a=index&siteid=1',
        '/index.php?m=Admin&c=Login&a=login&m=member&c=index&a=register&siteid=1',
        '/index.php?c=content&a=search&kw=&url=1%27+and+updatexml(0x5e,(md5(0x3233333333)),0x5e)+--+',
        '/index.php?a=activityMore&c=Activity&category=6&m=Home&type=work'
    ),
    'IGNORE_WARNING_AGENT' => array(
      'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
      'Alibaba.Security.Heimdall',
      'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1;Alibaba.Security.Heimdall.7136917)',
      'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1;Alibaba.Security.Heimdall.4640044)',
      'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1;Alibaba.Security.Heimdall.6577126.phpcmsv9-register-upload)',
      'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1;Alibaba.Security.Heimdall.6577126.phpcmsv9-attachment-sql)'
    ),
    /* 数据库设置 */
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  CFG_DB_HOST, // 服务器地址
    'DB_NAME'               =>  CFG_DB_NAME,          // 数据库名
    'DB_USER'               =>  CFG_DB_USER,      // 用户名
    'DB_PWD'                =>  CFG_DB_PWD,     // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DEFAULT_MODULE'     => 'Home', //默认模块
    'URL_MODEL'          => '0', //URL模式
    'SESSION_AUTO_START' => true, //是否开启session

    'TMPL_PARSE_STRING'  =>array(
        '__PUBLIC_METRO__' => '/Public/metro',
        '__PUBLIC_FRONT__' => '/Public/front',
        '__PUBLIC_API__' => 'api',
        '__PUBLIC_THEME__' => '/Public/theme/2016/assets',
        '__PUBLIC_THEME_FRONT__' => '/Public/theme/2016/front',
        '__PUBLIC_THEME_LOGIN__' => '/Public/theme/2016/login',
        '__DEFAULT_TEACHER_HEAD_IMAGE__' => 'Uploads/Avatars/default_t.jpg',
        '__DEFAULT_STUDENT_HEAD_IMAGE__' => 'Uploads/Avatars/default_t.jpg',
        '__DEFAULT_PARENT_HEAD_IMAGE__' => 'Uploads/Avatars/default_t.jpg',
    ),

    'USER_CONFIG'        => array(
    ),
    'URL_CASE_INSENSITIVE' =>true,

    'LOAD_EXT_CONFIG'    => array(
        'const',
        'createExercise'
    ),

   'ALIOSS_CONFIG'          => array(
           'KEY_ID'             => 'LTAItpuhTlgicIVf', // 阿里云oss key_id
           'KEY_SECRET'         => 'UzkBrw5CAWWBYJDV8fLt3fAg3SnWtS', // 阿里云oss key_secret
           'END_POINT'          => 'http://oss-cn-beijing.aliyuncs.com', // 阿里云oss endpoint
           'BUCKET'             => 'jbyoss'  // bucken 名称
           ),

   'BLWS_CONFIG'  =>  array(
   'READ_TOKEN'     => '95402908-8fc2-4328-a4cf-4f49601a5812',
   'WRITE_TOKEN'    => '9c538d85-340c-466c-9e35-bb301734eb0d'
   ),
   'NOBOOK_CONFIG'=>array(
       'appid'=>'251537',
       'appkey'=>'218535e5334eef9d'
   ),

    'IOS_SEND_PUSH' => array(
        'default_apiKey' => 'eketfomf1M3x05svL4xxm2eK',
        'default_secretkey' => 'UcU7ln5EWt9hjDTG3GGnCGacYL3TGPj5',
    ),
    'ANDROID_SEND_PUSH' => $androidPushKey,

   'REMOTE_ADDR' =>  $REMOTE_ADDR,
    'oss_path'    =>  'http://jbyoss.oss-cn-beijing.aliyuncs.com/',
   'REDIS_CONFIG'=>array(
       'REDIS_HOST'=>'118.190.65.33',
       'REDIS_PORT'=>$REMOTE_ADDR=='118.190.65.33'?'6372':'6372',
       'REDIS_AUTH'=>'Jingbanyun426!426'
   ),
    'TRIAL_TIME'=>300, //收费视频免费观看时间
    
    //404等错误定向
    //'TMPL_EXCEPTION_FILE'   =>  './ThinkPHP/Tpl/error.html',// 异常页面的模板文件    
    
       /*
     * 导入或注册vip配置
     * status
     * 1.赠送vip
     * 2.不赠送vip
     * 3.根据学校的权限来赋予注册者的权限
     */
    'VIP_CONFIG'=>array(
        'WEB_REGISTER_GIVE_VIP_STATUS'=>1,       //这里家长注册的时候没有状态为3的操作.每次学生注册就更新/添加家长的vip信息!
        'APP_REGISTER_GIVE_VIP_STATUS'=>1,      
        'WEB_IMPORT_GIVE_VIP_STATUS'=>1         
    ), 
    'HOLIDAY_STATUS'=>1,               //节假日状态 1为开启活动
    //运营导流的用户携带的参数配置
    'SOURCE_PARAM'=>array(
        '新浪微博'=>100010,
        '百度贴吧'=>100011,
        '网易微博'=>100012,
        '360话题讨论'=>100013
    
    ), 
    'SCHOOL_CATEGORY'=>array(
            '幼儿园','小学','初中','高中','九年一贯制学校','十二年一贯制学校','完全中学'
    ),
    'BJ_RESOURCE_UPLOAD_FILE_TYPE'=>array(
        '视频'=>array(
            'value'=>'video',
            'extval'=>'.mp4,.mov,.wmv,.flv,.avi',
        ),
        '音频'=>array(
            'value'=>'audio',
            'extval'=>'.mp3'
        ),
        'WORD'=>array(
            'value'=>'word',
            'extval'=>'.doc,.docx'
        ),
        'PPT'=>array(
            'value'=>'ppt',
            'extval'=>'.ppt,.pptx'
        ),
        'PDF'=>array(
            'value'=>'pdf',
            'extval'=>'.pdf'
        ),
        'SWF文件'=>array(
            'value'=>'swf',
            'extval'=>'.swf'
        ), 
        '图片'=>array(
            'value'=>'image',
            'extval'=>'.jpg,.png'
        ), 
        '压缩包'=>array(
            'value'=>'condensed',
            'extval'=>'.zip,.rar'
        ),
        'HTML'=>array(
            'value'=>'html',
            'extval'=>''
        ),
        '混合类型'=>array(
            'value'=>'mixed',
            'extval'=>'.zip,.rar,.doc,.docx,.mp4,.mov,.wmv,.flv,.avi'
        )
    ), 
	'RESOURCE_UPLOAD_FILE_TYPE'=>array(
        '视频'=>array(
            'value'=>'video',
            'extval'=>'.mp4,.mov,.wmv,.flv,.avi',
        ),
        '音频'=>array(
            'value'=>'audio',
            'extval'=>'.mp3'
        ),
        'WORD'=>array(
            'value'=>'word',
            'extval'=>'.doc,.docx'
        ),
        'PPT'=>array(
            'value'=>'ppt',
            'extval'=>'.ppt,.pptx'
        ),
        'PDF'=>array(
            'value'=>'pdf',
            'extval'=>'.pdf'
        ),
        'SWF文件'=>array(
            'value'=>'swf',
            'extval'=>'.swf'
        ), 
        '图片'=>array(
            'value'=>'image',
            'extval'=>'.jpg,.png'
        ), 
        '压缩包'=>array(
            'value'=>'condensed',
            'extval'=>'.zip,.rar'
        ) 
    ),

    'COPY_RESOURCE_UPLOAD_FILE_TYPE'=>array(
        array(
            'value'=>'video',
            'title' => '视频',
        ),
        array(
            'value'=>'audio',
            'title' => '音频',
        ),
        array(
            'value'=>'word',
            'title' => 'WORD',
        ),
        array(
            'value'=>'ppt',
            'title'=>'PPT',
        ),
        array(
            'value'=>'pdf',
            'title' => 'PDF',
        ),
        array(
            'value'=>'swf',
            'title' => 'SWF文件',
        ),
        array(
            'value'=>'image',
            'title' => '图片',
        ),
        array(
            'value'=>'condensed',
            'title' => '压缩包',
        )
    ),

    'KNOWLEDGE_RESOURCE_SOURCE'=>array(
        '1'=>'教师资源分享',
        '2'=>'京版活动获奖设计'
    ),
    'KNOWLEDGE_RESOURCE_TYPE'=>array(
        '1'=>'普通资源',
        '2'=>'nobook',
        '3'=>'万邦华堂资源'
    ),
    'ADMIN_TOP_MENU'=>array(  
        '班级管理'=>'index.php?m=School&c=Class&a=classList',
        '教师管理'=>'index.php?m=School&c=Teacher&a=teacherList',
        '学生管理'=>'index.php?m=School&c=Student&a=studentList',
        '家长管理'=>'index.php?m=School&c=Parent&a=parentList'
    ),
    //忽略访问记录IP
    'IGNORELOG_IP'=>array(
      '121.42.42.130',
      '121.42.13.204'
    ),
    //忽略访问记录AGENT
    'IGNORELOG_AGENT'=>array(
        'Alibaba.Security',
        'curl'
    ), 
    //推送消息配置
    /**
     * PUSH_MESSAGE 数组说明
     *  数组的KEY为消息类型字符串,其VALUE各字段说明如下:
    TYPE:消息接收类型 1--仅推送APP 2--仅个人中心 3--推送APP与个人中心
    CATEGORY:消息对应页面编号,规则如下:
    1.无
    2.消息中心详情页       1
    3.专家资讯详情页面      1
    4.京版活动详情页面
    5.京版资源详情页面
    6.电子课本列表页
    7.小黑板详情页
    8.活动表现详情页面
    9.学习轨迹
    10.教师班级列表
    11.该班级的未通过审核学生列表
    12.接收班级列表
    13.家长督学列表
    14.学生端加入班级页面
    15.学生的班级列表
    FORMAT_URL:推送至APP端网页地址的格式化字符串
    FORMAT_MSG:推送至APP端消息内容的格式化字符串
     *
     */

    /**
     * 推送消息方法:
     *  $parameters = array( 'msg' => array('hi','hello','ttt') ,    //FORMAT_MSG中格式化参数对应数组
    'url' => array( 'type' => 0, 'data' => array(0,1,2,3,4))     //FORMAT_URL中格式化参数对应数组,type=0时,仅向FORMAT_URL中的第一个%s填充MESSAGE_ID
    type=1时,向FORMAT_URL中的各个格式化参数%s依次填充data中的数据
    );
    A('Home/Message')->addPushUserMessage('TEACHER_RANK20',2,1,$parameters); //后台保存消息并推送消息至APP
     */
    'ADMIN_ROOT' => '平台管理员',
    'SCHOOL_ROOT' => '学校管理员',
    'PUSH_MESSAGE' =>
        array(
            //Common
            'LOGIN_PASS'  => array('TYPE'=> 1 , 'CATEGORY' => 1,'FORMAT_URL' => "",'FORMAT_MSG' => '','extras'=>array('loginPast'=>'您的设备已在别处登录，请重新登录')),
            'REG_SUCCESS'     => array('TYPE'=> 2 , 'CATEGORY' => 1,'FORMAT_URL' => "",'FORMAT_MSG' => '恭喜您已成功注册京版云平台，感谢您对京版云平台的使用，请妥善保管您的账号密码。您可通过京版云官网或手机APP登录并使用平台。'),
            'PASSWORD_MODIFY' => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s&isPopup=1','FORMAT_MSG' => '您的账号于  %s   进行了修改密码的操作，如果是您本人做出的修改，请忽略此消息并使用修改后的密码登录京版云平台'),
            'EXCEPTION_LOGIN' => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s&isPopup=1','FORMAT_MSG' => '您的账号近期发生了一次异常登录，请核实以下详情： 登录时间 %s 登录地点 %s 登录产品 %s 请确认该登录操作是否是您本人进行，如不是，则您的密码可能已经泄漏，请立即修改密码。'),
            'IMPORTANT_UPDATE'=> array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '%s'),
            'USER_LOCK'       => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的账号于 %s 被锁定, 账号锁定期间您将无法正常使用京版云平台. 如有疑问请与客服人员联系, 客服电话400-655-3588.'),
            'USER_UNLOCK'     => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的账号于 %s 被解锁, 您可以正常登录使用京版云平台. 如有疑问请与客服人员联系, 客服电话400-655-3588.'),
            'EXPERTINFO_PUBLISHED'  => array('TYPE'=> 1 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/ExpertInformation/informationDetails?id=%s','FORMAT_MSG' => '专家资讯：“%s”'),
            'ACTIVITY_PUBLISHED'    => array('TYPE'=> 1 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Activity/activityDetails?id=%s','FORMAT_MSG' => '京版活动：“%s”'),
            'BJRESOURCE_PUBLISHED'  => array('TYPE'=> 1 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/BjResource/jbResourceDetails?id=%s','FORMAT_MSG' => '京版资源：“%s”'),
            'ACTIVITY_REG_SUCCESS'=> array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您于 %s 报名参加了   “%s”   活动 , 已报名成功 ! 活动将定于 %s 举行  , 届时请按时参加.'),
            'ACTIVITY_CANCELED'=> array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您于 %s 报名参加了   “%s”   活动 , 活动已取消，请登录京版云查看详情'),
            'ACTIVITY_STARTTIME_MODIFIED'=> array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您所报名参加的   “%s”   原定于 %s 开始，现已更改为 %s 开始，请登录京版云查看详情.'),
            'ACTIVITY_START_NOTE' => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您所报名参加的   “%s”   将于 %s 开始,请您提前安排好时间,如不能到场，请提前与活动负责人联系。感谢您对京版云平台的使用.'),
            'VIP_EXPIREAFTER10DAYS' => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '尊敬的京版云用户：您的 %s 使用时间将于10天后（ %s ）到期，请贵校及时安排充值事宜。'),
            'VIP_SUCCESS'           => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您已成为 %s 用户，可尽情使用京版云平台的各项功能。感谢您对京版云平台的支持，希望您能在使用过程中提出宝贵意见。'),
            'VIP_EXPIRED'           => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的 %s 已过期，但您仍可使用京版云平台的部分功能。如需要使用其他功能，请联系学校安排充值事宜。感谢您对京版云平台的使用与支持。'),

            //Teacher
            'TEACHER_RANK20'  => array('TYPE'=> 1 , 'CATEGORY' => 2,'FORMAT_URL' => '','FORMAT_MSG' => '恭喜您在教师风采中的排名进入前20名！感谢您对京版云平台的使用！希望您能继续支持京版云平台，同时多多提出宝贵意见！'),
            'TEACHER_RESOURCEPASS'  => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您在教师资源分享板块分享的“%s”已通过审核，请登录京版云平台教师资源分享板块查看详情。感谢您对京版云平台的使用。'),
            'TEACHER_RESOURCEFAIL'  => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您在教师资源分享板块分享的“%s”由于不符合平台要求而未通过审核/被拒绝，请您做出相应修改后，等待再次审核。感谢您对京版云平台的使用。'),
            'TEACHER_RESOURCEDOWN'  => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您在教师资源分享板块分享的“%s”已下架，请登录京版云平台教师资源分享板块查看详情。感谢您对京版云平台的使用。'),
            'TEACHER_RESOURCEDOWN_Grad'  => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您报名参加的   “%s”   原学科“%s”已更改为 “%s”，您的报名已经无效, 请登录京版云查看详情。'),
            'CLASSROOM_MODIFIED'    => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您近期进行了一次数字课堂信息修改，课堂编号: %s ，请核实以下详情：修改前课堂信息：%s %s %s  上课教室：%s 修改后课堂信息：%s %s %s  上课教室：%s 请确认该操作是否是您本人进行，如不是，则您的密码可能已经泄漏，请立即修改密码。'),

            'CLASS_MODIFIED'        => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '%s %s 的班级信息已被修改，请登录京版云班级信息管理板块查看详情。'),
            'CLASSMOVE_SENDER'      => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您已向“%s”移交了班级，请登录京版云平台班级信息管理板块查看详情。'),
            'CLASSMOVE_RECEIVER'    => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '%s %s 的 %s (%s) 向您移交了班级“%s”，请登录京版云平台选择是否接收班级。'),
            'CLASSMOVE_RECEIVE'     => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您所移交的 %s %s 已经成功被%s(%s)接收，请登录京版云平台班级信息管理板块查看详情。'),
            'CLASSMOVE_RECEIVER_SUCCESS'=> array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您已成功接收 %s %s ，请登录京版云平台班级信息管理板块查看详情。'),
            'CLASSMOVEUNDO_SENDER'=> array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您向“%s（%s）”移交的班级“%s” 已经撤回，请登录京版云平台班级信息管理板块查看详情。'),
            'CLASSMOVEUNDO_RECEIVER'=> array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '“%s（%s）”向您移交的班级“%s” 已经撤回，请登录京版云平台班级信息管理板块查看详情。'),
            'CLASSTABLE_MODIFIED'   => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '%s %s  的班级课表已被修改，请登录京版云平台班级信息管理板块查看详情。'),
            'CLASSNOTIFY_JOIN'      => array('TYPE'=> 2 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '%s 已向您的 %s 班级发送加入申请,请登录京版云平台及时处理申请。'),


            'ADD_SCHOOL_CLASS'         => array('TYPE'=> 3 , 'CATEGORY' => 3,'FORMAT_URL' => "?role=2&vcname=class_list",'FORMAT_MSG' => '您已被 %s 添加到 %s %s 任教 %s，点击班级管理查看详细信息'),
            'ADD_NEWS_PERSON_CLASS'         => array('TYPE'=> 3 , 'CATEGORY' => 3,'FORMAT_URL' => '?role=2&vcname=class_list','FORMAT_MSG' => '%s已助您创建了自建班级 %s %s ，点击班级管理查看详细信息'),
            'STUDENT_ADD_PERSON_CLASS'         => array('TYPE'=> 3 , 'CATEGORY' => 3,'FORMAT_URL' => "?role=2&vcname=class_person&status=1&classid=%s",'FORMAT_MSG' => '%s学生申请加入您的%s %s，请您审批'),
            'STUDENT_CEXIAO_PERSON_CLASS'         => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '%s学生撤销了加入您的%s %s的申请'),
            'CLASS_REMOVE_TEACHER'         => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => "您已被%s从%s %s移除，点击查看详细信息jingbanyunxx您已被%s从%s %s移除，将会影响您以下的功能使用：<br/>     1.数字课堂：您将无法查看该班级的数字课堂信息；<br/>     2.小黑板：您将无法查看该班级的小黑板历史消息；<br/>     3.学习轨迹：您将无法查看该班级的学习轨迹信息；<br/>     4.作业系统：您可查看到该班级的作业。"),
            'TEACHER_REMOVE_SCHOOL'         => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您已被%s从%s移除，请您前往个人中心修改您的学校信息'),
            'TEACHER_Add_SCHOOL'         => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您已通过学校审核。'),
            'ADMIN_SEND_SCHOOL_TEACHER'         => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '%s 教师，您好！您所选择的学校已经开通了认证管理，学校管理员将会审核您的身份信息。'),
            'ACCEPT_CLASS'         => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '%s已经同意了您的%s %s 的移交申请，您的班级已经移交成功。'),
            'JIAOSHI_CHECIAO_CLASS'         => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '%s教师（移交发起教师）撤销了%s%s的移交申请'),
            'YIJIAO_CLASS'         => array('TYPE'=> 3 , 'CATEGORY' => 3,'FORMAT_URL' => '?role=2&vcname=receive_class_list&status=1','FORMAT_MSG' => '您有班级待接收，点击查看详细信息'),
            'CLASS_FALG_DISABLE'         => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您所在的班级：%s %s已被%s停用，点击查看详细信息jingbanyunxx您所在的班级：%s %s已被%s停用，将会影响您以下的功能使用：<br/>     1.数字课堂：您将无法查看该班级的数字课堂信息；<br/>     2.小黑板：您将无法查看该班级的小黑板历史消息；<br/>     3.学习轨迹：您将无法查看该班级的学习轨迹信息；<br/>     4.作业系统：您可查看到该班级的作业。'),
            'SCHOOL_FALG_DISABLE'         => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您所在的学校已被%s停用，点击查看详细信息jingbanyunxx您所在的学校已被平台管理员停用， 将会影响您以下的功能使用：将会影响您以下的功能使用：<br/>     1.您所在的校建班级将一并被停用，不能在本班级进行班级移交、移除学生、添加学生、导入学生等操作，但您的自建班级不受影响，您仍可正常使用自建班级；<br/>     2.数字课堂：您将无法使用校建班级的数字课堂；<br/>     3.小黑板：您将无法在校建班级内发布小黑板信息；<br/>     4.学习轨迹：您将无法为校建班级内的学生添加活动表现；<br/>     5.作业系统：您将无法为校建班级内的学生布置作业。'),
            'DELETE_CLASS'         => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您所在的班级：%s %s已被%s删除点击查看详细信息jingbanyunxx您所在的班级：%s %s已被%s删除，将会影响您以下的功能使用：<br/>     1.数字课堂：您将无法查看该班级的数字课堂信息；<br/>     2.小黑板：您将无法查看该班级的小黑板历史消息；<br/>     3.学习轨迹：您将无法查看该班级的学习轨迹信息；<br/>     4.作业系统：您可查看到该班级的作业。'),
            'XIUGAI_CLASS'         => array('TYPE'=> 3 , 'CATEGORY' => 3,'FORMAT_URL' => '?role=2&vcname=class_list','FORMAT_MSG' => '您所在的班级：%s %s 已被 %s 修改为%s %s 。'),


            //Student
            'STUDENTTEACHER_RANK20' => array('TYPE'=> 1 , 'CATEGORY' => 2,'FORMAT_URL' => '','FORMAT_MSG' => '您的老师%s在教师风采中的排名进入前20名！感谢您对京版云平台的使用！希望您能继续支持京版云平台，同时多多提出宝贵意见！'),
            'NEW_ETEXTBOOK'         => array('TYPE'=> 1 , 'CATEGORY' => 6,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/EtextBook/textBookDetails?id=%s','FORMAT_MSG' => '%s %s %s 电子课本现已上线啦！快去电子课本板块体验吧！'),
            'STUCLASSROOM_MODIFIED' => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的 %s %s 近期进行了一次数字课堂信息修改，课堂编号: %s ：修改前课堂信息：%s %s %s  上课教室：%s 修改后课堂信息：%s %s %s  上课教室：%s'),
            'BLACKBOARD_PUBLISHED'  => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/Version1_2/Blackboard/blackboardDetail?id=%s&classId=%s','FORMAT_MSG' => '您的 %s 老师近期在 %s 发布了一条小黑板消息：“%s”。详细信息请登录京版云在小黑板板块查看。'),
            'HOMEWORK_PUBLISHED'    => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的 %s 老师刚刚布置了一条新作业：“%s”。您可进入作业系统完成作业。'),
            'BUZHI_HOMEWORK_PUBLISHED'    => array('TYPE'=> 1 , 'CATEGORY' => 1,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子 %s 已经完成 %s 老师刚刚布置的 “%s” 作业。可进入家长督学模块中查看学生完成的情况。'),
            'HOMEWORK_CORRECT'      => array('TYPE'=> 1 , 'CATEGORY' => 1,'FORMAT_URL' => '','FORMAT_MSG' => '您的 %s 老师已经批改完成了作业：“%s”。请登录京版云平台进入作业系统查看您的得分。'),
            'STUDENT_EXPRESSION'    => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的 %s 老师近期给您添加了一条活动表现：“%s”。详细信息请登录京版云在  我的活动表现 板块查看。'),
            'STUDENT_EXPRESSION_DELETE'    => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的 %s 老师近期删除了一条您活动表现：“%s”。详细信息请登录京版云在  我的活动表现 板块查看。'),
            'JOINCLASS_SUCCESS'     => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您已成功加入 %s %s。'),
            'JOINCLASS_FAILED'      => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您申请加入 %s %s 的请求已拒绝，请联系任课老师核实情况。'),
            'EXITCLASS'             => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您已经被移出 %s %s 请联系任课老师核实情况。'),
            'CLASS_MODIFIED_STUDENT'=> array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您所在的  %s %s 的  班级信息已被修改，请登录京版云我的班级板块查看详情。'),
            'CLASSMOVE_SEND_STUDENT'     => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您所在的  %s %s 已向“%s（%s）”移交，请登录京版云平台我的班级板块查看详情。'),
            'CLASSMOVE_RECEIVE_STUDENT'   => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您所在的  %s %s 已经成功被%s(%s)接收，请登录京版云平台我的班级板块查看详情。'),
            'CLASSTABLE_MODIFIED_STUDENT' => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您所在的 %s %s  的班级课表已被修改，请登录京版云平台班级信息管理板块查看详情。'),
            'INFO_MODIFIED_BYOTHER_STUDENT' => array('TYPE'=> 1 , 'CATEGORY' => 1,'FORMAT_URL' => '','FORMAT_MSG' => '您的信息已被 %s 修改,详情请登录京版云个人中心查看。'),
            'INFO_MODIFIED_BYSELF_STUDENT'  => array('TYPE'=> 1 , 'CATEGORY' => 1,'FORMAT_URL' => '','FORMAT_MSG' => '您的信息已修改,详情请登录京版云个人中心查看。'),

            'GROUP_STUDENT_COMMENT'  => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '','FORMAT_MSG' => '您的同分组的同学：%s,对您的课堂表现进行了评论，课堂编号:%s,评论信息：%s'),
            'GROUP_STUDENT_SENDHOMEWORK'  => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '%s：同学们，快来做作业吧'),



            'CLASSADDSENDSTUDENT'              => array('TYPE'=> 3 , 'CATEGORY' => 3,'FORMAT_URL' => '?role=3&vcname=student_class_list','FORMAT_MSG' => '您已被%s添加到%s %s，点击查看详细信息'),
            'WEICLASSADDSENDSTUDENT'              => array('TYPE'=> 2 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '%s教师为您注册了京版云账号并将邀请您加入到%s %s，您可点击“班级管理”查看所在班级列表，可在“个人中心”修改登录密码。'),
            'STUGETADOPTSTU'              => array('TYPE'=> 3 , 'CATEGORY' => 3,'FORMAT_URL' => '?role=3&vcname=student_class_list','FORMAT_MSG' => '%s教师已经同意您加入%s %s的申请  '),
            'STUGETADOPTSTUDISABLE'              => array('TYPE'=> 3 , 'CATEGORY' => 3,'FORMAT_URL' => '?role=3&vcname=student_add_class_list','FORMAT_MSG' => '%s教师拒绝您加入%s %s的申请  '),
            'STU_CLASS_REMOVE_TEACHER'         => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => "%s老师已将您移除%s %s点击查看详细信息jingbanyunxx%s教师已将您移除%s %s，将会影响您以下的功能使用：<br/>     1.您将无法查看该班级的小黑板历史消息；<br/>     2.平板端将无法进入该班级的数字课堂；<br/>     3.您将无法提交该班级教师预留的作业，但仍可查看和完成作业。"),
            'SCHOOL_CLASS_REMOVE_TEACHER'         => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => "%s已将您移除%s %s，点击查看详细信息jingbanyunxx%s已将您移除%s %s，将会影响您以下的功能使用:<br/>     1.您将无法查看该班级的小黑板历史消息；<br/>     2.平板端将无法进入该班级的数字课堂；<br/>     3.您将无法提交该班级教师预留的作业，但仍可查看和完成作业。"),
            'SCHOOL_ADMIN_REMOVE_STU'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您已被%s从%s学校移除，请您前往个人中心修改您的学校信息。'),
            'STU_CLASS_DISABLE'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => "您所在的班级：%s %s 已被%s停用，点击查看详细信息jingbanyunxx您所在的班级：%s %s已被%s停用，将会影响您以下的功能使用：<br/>     1.平板端将无法进入该班级的数字课堂；<br/>     2.您将无法提交该班级教师预留的作业，但仍可查看和完成作业。"),
            'STU_PARENTDELETE_CLASS'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您所在的班级：%s %s 已被%s删除，点击查看详细信息jingbanyunxx您所在的班级：%s %s 已被%s删除，将会影响您以下的功能使用：<br/>     1.您将无法查看该班级的小黑板历史消息；<br/>     2.平板端将无法进入该班级的数字课堂；<br/>     3.您将无法提交该班级教师预留的作业，但仍可查看和完成作业.'),
            'STUDIT_CLASS'                  => array('TYPE'=> 3 , 'CATEGORY' => 3,'FORMAT_URL' => '?role=3&vcname=student_class_list','FORMAT_MSG' => '您所在的班级：%s %s已被%s修改为%s %s。'),
            'STU_ZIJIAN_YIJIAO'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您所在的班级：%s %s已被%s教师接收。'),
            'STU_ZIJIAN_XIAOJIAN'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您所在的班级：%s %s的%s的任教教师变更为%s老师'),
            'XUEXIAO_ZHENG'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '%s同学，您好！您所选择的学校已经开通了认证，管理员将会审核您的注册信息。'),
            'TIANJIA_SCHOOL'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您已通过学校审核。'),
            'STUDELETESCHOOLE'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您所在的学校已被平台管理员停用，点击查看详细信息jingbanyunxx您所在的学校已被平台管理员停用，将会影响您以下的功能使用：<br/>     1.您所在的校建班级将一并被停用；<br/>     2.平板端将无法进入该班级的数字课堂；<br/>     3.您将无法提交该班级教师预留的作业，但仍可查看和完成作业。'),


            //Parent
            'PARENT_PASSWORD_MODIFY' => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s&isPopup=1','FORMAT_MSG' => '您的账号  %s  于 %s 进行了修改密码的操作，如果是您本人做出的修改，请忽略此消息并使用修改后的密码登录京版云平台'),
            'CHILD_REG_SUCCESS' => array('TYPE'=> 3 , 'CATEGORY' => 1,'FORMAT_URL' => '','FORMAT_MSG' => '恭喜您，您的孩子“%s”已成功注册京版云平台，感谢您对京版云平台的使用，您的孩子可通过京版云官网或手机APP登录并使用平台。'),
            'CHILD_EXCEPTION_LOGIN' => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s&isPopup=1','FORMAT_MSG' => '您孩子的账号近期发生了一次异常登录，请核实以下详情：登录时间 %s 登录地点 %s 登录产品 %s 请确认该登录操作是否是您本人进行，如不是，则您的密码可能已经泄漏，请立即修改密码。'),
            'USER_LOCK_CHILD'       => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子 %s 账号于 %s 被锁定, 账号锁定期间您将无法正常使用京版云平台. 如有疑问请与客服人员联系, 客服电话400-655-3588.'),
            'USER_UNLOCK_CHILD'     => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子 %s 账号于 %s 被解锁, 您可以正常登录使用京版云平台. 如有疑问请与客服人员联系, 客服电话400-655-3588.'),
            'ACTIVITY_REG_SUCCESS_CHILD'=> array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子 %s %s 报名参加了   “%s”   活动 , 已报名成功 ! 活动将定于 %s 举行  , 届时请按时参加.'),
            'ACTIVITY_START_NOTE_CHILD' => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子 %s 所报名参加的   “%s”   将于 %s 开始,请您提前安排好时间,如不能到场，请提前与活动负责人联系。感谢您对京版云平台的使用.'),
            'PARENT_STUDENTTEACHER_RANK20' => array('TYPE'=> 1 , 'CATEGORY' => 2,'FORMAT_URL' => '','FORMAT_MSG' => '亲爱的京版云用户：恭喜您的孩子“%s”老师在教师风采中的排名进入前20名！感谢您对京版云平台的使用！希望您能继续支持京版云平台，同时多多提出宝贵意见！'),
            'NEW_ETEXTBOOK_CHILD'         => array('TYPE'=> 1 , 'CATEGORY' => 6,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/EtextBook/textBookDetails?id=%s','FORMAT_MSG' => '%s %s %s  电子课本现已上线啦！快让您的孩子去电子课本板块体验吧！'),
            'BLACKBOARD_PUBLISHED_CHILD'  => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/Version1_2/Blackboard/blackboardDetail?id=%s&classId=%s','FORMAT_MSG' => '您的孩子“%s”的 老师 %s 近期在 %s 发布了一条小黑板消息：“%s”。详细信息请登录京版云在小黑板板块查看。'),
            'HOMEWORK_PUBLISHED_CHILD'    => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => ' %s 老师给您的孩子 %s 布置了作业 %s ，请督促孩子及时完成。'),
            'HOMEWORK_SUBMIT_CHILD'    => array('TYPE'=> 3 , 'CATEGORY' => 1,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子“%s”已提交了作业“%s”。详细信息请登录京版云平台在作业系统中查看。'),
            'STUDENT_EXPRESSION_CHILD' => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子“%s”的 %s 老师 近期对“%s”做了一次活动评价：“%s”。详细信息请登录京版云在学习轨迹板块查看。'),
            'STUDENT_EXPRESSION_DELETE_CHILD' => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子“%s”的 %s 老师近期删除了一条您活动表现：“%s”。详细信息请登录京版云在 学习轨迹板块查看。'),
            'STUDY_LEARNPATH_CHILD' => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Study/parentStudyList?id=%s','FORMAT_MSG' => '您的孩子“%s”有新的学习轨迹哦，详细信息请登录京版云在学习轨迹板块查看。'),
            'CLASS_MODIFIED_STUDENT_CHILD'=> array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子 %s 所在的  %s %s 的  班级信息已被修改，请登录京版云我的班级板块查看详情。'),
            'CLASSTABLE_MODIFIED_STUDENT_CHILD' => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子 %s 所在的  %s %s  的班级课表已被修改，请登录京版云平台班级信息管理板块查看详情。'),
            'CLASSMOVE_SEND_STUDENT_CHILD'     => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子 %s 所在的  %s %s 已向“%s（%s）”移交，请登录京版云平台家长督学板块查看详情。'),
            'CLASSMOVE_RECEIVE_STUDENT_CHILD'   => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子 %s 所在的  %s %s 已经成功被 %s（%s）接收，请登录京版云平台我的班级板块查看详情。'),
            'CLASSMOVEUNDO_PARENT'=> array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子 %s 所在的 %s %s 已被撤回移交，请登录京版云平台家长督学板块查看详情。'),
            'ADDCHILD'              => array('TYPE'=> 1 , 'CATEGORY' => 1,'FORMAT_URL' => '','FORMAT_MSG' => '您已添加孩子 %s, 详情请登录京版云 家长督学板块查看。'),
            'ADDCHILD_TEACHER'      => array('TYPE'=> 1 , 'CATEGORY' => 1,'FORMAT_URL' => '','FORMAT_MSG' => '教师 %s 已添加孩子 %s, 详情请登录京版云 家长督学板块查看。'),
            'INFO_MODIFIED_BYSELF_CHILD'  => array('TYPE'=> 1 , 'CATEGORY' => 1,'FORMAT_URL' => '','FORMAT_MSG' => '您已修改了孩子 %s 的信息, 详情请登录京版云 家长督学板块查看。'),
            'CLASSROOM_MODIFIED_CHILD'    => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子 %s 的%s老师 %s 近期进行了一次数字课堂信息修改，课堂编号: %s ：修改前课堂信息：%s %s %s  上课教室：%s 修改后课堂信息：%s %s %s  上课教室：%s'),


            'CLASSADDSTUDENT'              => array('TYPE'=> 3 , 'CATEGORY' => 3,'FORMAT_URL' => '?role=4&vcname=parent_class_list&student_id=%s&student_name=%s','FORMAT_MSG' => '您的孩子%s已被%s添加到%s %s，点击查看详细信息'),
            'ADDSTU'              => array('TYPE'=> 3 , 'CATEGORY' => 3,'FORMAT_URL' => '?role=4&vcname=parent_class_list&student_id=%s&student_name=%s','FORMAT_MSG' => '您的孩子已被%s老师添加到%s %s，点击查看详细信息'),
            'REGISTERADDSTU'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '%s教师已为您的孩子%s注册了京版云账号并邀请他加入到%s %s，您可点击“班级管理”查看所在班级列表。'),
            'GETADOPTSTU2'              => array('TYPE'=> 1 , 'CATEGORY' => 3,'FORMAT_URL' => '?role=4&vcname=parent_class_list&student_id=%s&student_name=%s','FORMAT_MSG' => '%s教师已经同意您的孩子%s加入%s %s'),
            'GETADOPTSTU3'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '%s教师拒绝您的孩子%s加入%s %s'),
            'DELSTUDENTCLASS'              => array('TYPE'=> 3 , 'CATEGORY' => 3,'FORMAT_URL' => '?role=4&vcname=parent_class_list&student_id=%s&student_name=%s','FORMAT_MSG' => '%s教师已将您的孩子%s移除%s %s,点击查看详细信息'),
            'FLAGCLASSDISABLE'              => array('TYPE'=> 3 , 'CATEGORY' => 3,'FORMAT_URL' => '?role=4&vcname=parent_class_list&student_id=%s&student_name=%s','FORMAT_MSG' => '您的孩子所在的班级：%s %s已被%s停用，点击查看详细信息   '),
            'DELETESCHOOLE'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子所在的学校已被平台管理员停用，所在的校建班级将一并被停用。   '),
            'PARENTDELETE_CLASS'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子所在的班级：%s %s 已被%s删除。    '),
            'PARENTEDIT_CLASS'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子所在的班级：%s %s 已被%s修改为%s %s 。'),
            'ZIJIAN_YIJIAO'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子所在的班级：%s %s已被%s教师接收。'),
            'ZIJIAN_XIAOJIAN'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的孩子所在的班级：%s %s的%s的任教教师变更为%s老师'),
            'ORDER_FAILURE'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您好，您购买的%s订单因超过24小时保留时间未付款，系统已自动取消订单'),
            'ORDER_CLOSED'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您好，您购买的%s订单已取消，交易已关闭'),
            'ORDER_SUCCESS'              => array('TYPE'=> 3 , 'CATEGORY' => 2,'FORMAT_URL' => '/ApiInterface/'.APIINTERFACE_DIR.'/Message/messageDetail?id=%s','FORMAT_MSG' => '您的订单交易已经完成：<br/>     订单金额:¥%s元<br/>     商品详情：%s<br/>     订单编号：%s<br/>     点击我的订单进行查看'),

        ),
    //'配置项'=>'配置值'
    'PAGE_SIZE_FRONT' => 20,//前台每页显示记录数
    'PAGE_SIZE_ADMIN' => 200,//后台每页显示记录数
    'allowed_types'=>array('image/jpeg','image/png','image/gif','image/pjpeg','image/bmp','image/x-png','image/jpg'),         //允许的图片类型
    'file_size'=>5,         //图片尺寸默认5M
    'upload_path'=>array('Resources/jb/','Resources/lessonplanning/','Resources/teacher/','Resources/activity/','Resources/material/',"Homework/"),
    'default_dir'=>1,       //默认创建的目录列:allowed_types[1];
    'media_file_size'=>20,  //媒体默认尺寸10M
    'media_allowed_types'=>array('video/mp4','audio/mp3','application/msword','application/pdf','application/x-shockwave-flash','application/vnd.openxmlformats-officedocument.presentationml.presentation',


        'application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.wordprocessingml.document','video/quicktime','audio/aac-adts','audio/aac','video/x-ms-wmv','video/avi','video'),

    
    'all_file_size'=>5000,  //全部类型尺寸 
    'allow_allowed_types'=>array('image/jpeg','image/png','image/gif','image/pjpeg','image/bmp','image/x-png','image/jpg','video/mp4','audio/mpeg','audio/mp3','video/3gpp','application/msword','application/pdf','application/x-shockwave-flash',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation','application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.wordprocessingml.document','video/quicktime',
        'application/zip','application/octet-stream','audio/aac-adts','audio/aac','video/x-ms-wmv','application/x-zip-compressed','video/avi','video'),//全部文件类型

    'oss_path'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/',
    'education' => array(
        1=>'高中',
        2=>'专科',
        3=>'本科',
        4=>'硕士',
        5=>'博士',
    ),
    'professional' => array(
        1=>'正高级教师',
        2=>'高级教师',
        3=>'一级教师',
        4=>'二级教师',
        5=>'三级教师',
    ),
    'activity_jyyuser' => array(
        'user' => 'jyyuser',
        'password' => 'jyypassword'
    ),
    'TEXTBOOK_VERSION' => array(
        array('id'=>1,'name'=>'北京版'),
    ),
    'professionalApp' => array(
        array('id'=>1,'name'=>'正高级教师'),
        array('id'=>2,'name'=>'高级教师'),
        array('id'=>3,'name'=>'一级教师'),
        array('id'=>4,'name'=>'二级教师'),
        array('id'=>5,'name'=>'三级教师'),
    ),
    'columnNumApp' => array(
        'one'=>1,
        'two'=>2,
        'three'=>3,
    ),

);