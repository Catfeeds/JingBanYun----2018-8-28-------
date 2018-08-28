<?php

//*************************************名称相关常量*******************************/

define('WEB_BEIJING_PRESS_TITLE','北京出版集团');
define("OSS_URL", "http://jbyoss.oss-cn-beijing.aliyuncs.com/");
define('USE_PREV_CHANNEL_ID',1);
define('USE_USER_DEFINE_MESSAGE',1); //使用透传推送
define("ROLE_TEACHER" ,2);
define("ROLE_STUDENT" ,3);
define("ROLE_PARENT"  ,4);
define("ROLE_YOUKE"  ,5);
define("ROLE_ADMIN"  ,6);
//*************************************路径相关常量*******************************/

//*************************************活动相关常量*******************************/
define("ACTIVITY_MYFAVOR" ,0);
define("ACTIVITY_MYJOIN" ,1);

/**************广告状态*************/
define('PROPAGANDA_STATUS_DECLINED',1);       //已拒绝
define('PROPAGANDA_STATUS_WAITFORVERIFY',2);  //待审核
define('PROPAGANDA_STATUS_VERIFIED',3);       //已审核
define('PROPAGANDA_STATUS_OVER',4);           //已结束
define('PROPAGANDA_STATUS_OFFSHELF',5);       //已下架
define('PROPAGANDA_STATUS_PUBLISHING',6);     //发布中
define('PROPAGANDA_STATUS_PUBLISHED',7);      //已发布


/**************京版活动状态*************/
define('ACTIVITY_STATUS_WAITFORVERIFY',1);  //待审核
define('ACTIVITY_STATUS_VERIFIED',2);       //已审核
define('ACTIVITY_STATUS_DECLINED',3);       //已拒绝
define('ACTIVITY_STATUS_OFFSHELF',4);       //已下架
define('ACTIVITY_STATUS_PUBLISHED',5);      //已发布

/**************京版活动作品审核状态*************/
define('ACTIVITY_WORK_STATUS_WAITFORVERIFY',0);  //待审核
define('ACTIVITY_WORK_STATUS_VERIFIED',1);       //通过
define('ACTIVITY_WORK_STATUS_DECLINED',2);       //拒绝



/**************京版活动投票审核状态*************/
define('VOTE_FLAG_UNAUTH',1);
define('VOTE_FLAG_AUTH',2);
define('VOTE_FLAG_REJECT',3);
define('VOTE_FLAG_ONLINE',4);
define('VOTE_FLAG_OFFLINE',5);


/**************京版活动投票类型*************/
define('VOTE_TYPE_NORMAL',1);
define('VOTE_TYPE_WEIXIN',2);

/**************京版活动邀请码状态*************/
define('ACTIVITY_CODE_STATUS_UNUSED',1);         //未使用
define('ACTIVITY_CODE_STATUS_USED',2);           //已使用

/**************专家资讯推送状态*************/
define('EXPERTINFORMATION_UNPUSH',1);          //未推送
define('EXPERTINFORMATION_HASPUSH',2);         //已推送

/**************专家资讯审核状态*************/
define('EXPERTINFO_STATUS_UNAUTH',1);
define('EXPERTINFO_STATUS_AUTH',2);
define('EXPERTINFO_STATUS_ONLINE',4);
define('EXPERTINFO_STATUS_OFFLINE',3);

/**************后台学校登录错误信息*************/
define('SCHOOL_LOGIN_FAILED','账号或密码错误');
define('SCHOOL_LOGIN_VERIFY_FAILED','验证码错误');

/**************后台登陆后在线时间过长,失效*************/
define('ACCOUNT_FAILURE','账户信息已失效,请重新登录!');

define('COMMON_FAILED_MESSAGE','抱歉,由于网络原因操作失败,请刷新页面后重试!');
define('ID_NOT_EXISTS_MESSAGE','抱歉,由于数据异常操作失败!');

/**************设置其他学校的ID为2000*************/
define('OTHER_SCHOOL_ID',2000);
define('CLASS_CODE_ADD_NUMBER',100000);
define('APPLY_SCHOOL_ALLOW', 1);
define('APPLY_SCHOOL_DENY', 2);
define('SEX_MAN','男');
define('SEX_WOMAN','女');
define('ADMIN_IMPORT_PASSWORD','123456');

define('APIINTERFACE_EXEERTINFOMATION_VER','Version1_2');

include_once 'guoxueConf.php';
include_once 'esConfig.php';
/*************教师分享的资源状态******************/
define('TEACHER_SHARE_STATUS',2);//审核通过

/*************学生加入班级状态******************/
define('STUDENT_JOINSTATE_NORMAL',2);

/*******************绘本的状态*********************************/
define('AUDIT_WAIT',1);//待审核
define('AUDIT_YES',2);//审核通过
define('AUDIT_NO',3);//审核不通过
define('ONSHELF',2);//上架
define('OFFSHELF',1);//下架
define('DELETE',4);//已删除
/************************绘本提供方******************************/
define('GAODENGJIAOYUCHUBANSHE',1);
return array(
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
    //绘本提供方
    'PICTURE_BOOK_SOURCE' => array(
    array(
        'id' => 1,
        'name' => '高等教育出版社',
    )
)
);
?>
