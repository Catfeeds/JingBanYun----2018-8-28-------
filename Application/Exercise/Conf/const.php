<?php

//*************************************用户端习题常量*******************************/
define('STUDENT_HOMEWORK_DELETED',2);  //学生作业删除
define('STUDENT_HOMEWORK_NORMAL',1);  //学生作业正常(未删除)

define('STUDENT_HOMEWORK_NOTSUBMIT',2);  //学生作业未提交
define('STUDENT_HOMEWORK_SUBMITED',1);  //学生作业已提交
define('STUDENT_HOMEWORK_SUBMITANDCORRECTED',3);
define('HOMEWORK_UNPUBLISH'    ,1); //作业未布置
define('HOMEWORK_PUBLISHED'    ,2); //作业已布置
define('HOMEWORK_OVERTIME'     ,3); //作业已过期

define('STUDENT_HOMEWORK_STATUS_NORMAL'    ,1); //作业状态正常

define('STUDENT_HOMEWORK_CORRECTED'    ,1); //作业批改
define('STUDENT_HOMEWORK_UNCORRECTED'  ,0); //作业未批改

define('HOMEWORK_ONCLASS'       ,1); //课堂作业
define('HOMEWORK_AFTERCLASS'    ,2); //课后作业

define('HOMEWORK_EXERCISE_WRONG'    ,2); //学生习题错误
define('HOMEWORK_EXERCISE_RIGHT'    ,1); //学生习题正确

define('HOMEWORK_EXERCISE_CORRECTED'       ,1); //已批改
define('HOMEWORK_EXERCISE_UNCORRECTED'    ,0); //未批改

//*************************************习题类型常量*******************************/
define('EXERCISE_TYPE_NORMAL',1); //普通习题
define('EXERCISE_TYPE_ABNORMAL',2); //特殊习题

define('EXERCISE_WORD'    ,1);
define('EXERCISE_SENTENCE',2);
define('EXERCISE_VIDEO'   ,3);
define('EXERCISE_TEXTBOOK',4);
$exerciseCategory = array('1' => array('name'=>'普通习题',
                           'data'=>
                          array(
                              1=>array('id'=>1 , 'name'=>'普通习题','url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/teacher/2017-02-22/20170222053217AbbSqEUpug.jpg'),
                          )
                          ),
                          '2' => array(
                          'name'=> '语音作业',
                          'data'=>
                          array(
                              EXERCISE_WORD=>array('id'=>EXERCISE_WORD , 'name'=>'跟读-词汇','url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/teacher/2017-02-22/20170222053217AbbSqEUpug.jpg'),
                              EXERCISE_SENTENCE=>array('id'=>EXERCISE_SENTENCE , 'name'=>'跟读-课文','url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/teacher/2017-02-22/20170222053217AbbSqEUpug.jpg'),
                              EXERCISE_VIDEO=>array('id'=>EXERCISE_VIDEO , 'name'=>'观看-视频','url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/teacher/2017-02-22/20170222053217AbbSqEUpug.jpg'),
                              EXERCISE_TEXTBOOK=>array('id'=>EXERCISE_TEXTBOOK , 'name'=>'观看-课文','url'=>'http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/teacher/2017-02-22/20170222053217AbbSqEUpug.jpg'),
                           )
                          ));
$config = json_encode($exerciseCategory); //或者是 json_encode($config);
define('EXERCISE_CATEGORY', $config);

define('PLATFORM_NAME','京版云');
//*************************************角色相关常量*******************************/
define('ACCOUNT_SUPERADMIN_ID',1); //超级管理员ID

define("ROLE_INPUTEXERCISE"   ,3);//录入人员
define("ROLE_ASSIGNEXERCISE"  ,4);//内容工作人员(指派人员)
define("ROLE_MARKKNOWLEDGE"   ,1);//标引人员(教师)
define("ROLE_INTSTRUCTOR"     ,2);//教研员(知识点管理)
define("ROLE_COTENTADMIN"     ,5);//内容管理人员
define("ROLE_EXERCISEADMIN"   ,6);//管理员
define("ROLE_VERIFY"          ,7);//校审人员
define("ROLE_OTHER"           ,-1);//其它

//*************************************习题状态相关常量*******************************/

//define("EXERCISE_STATE_CREATESUCCESS"           ,1); //创建成功
define("EXERCISE_STATE_DRAFT"               ,18); //试卷草稿状态
define("EXERCISE_STATE_PAPEREXERCISEWAITVERIFY"          ,19); //试卷中的习题待校审
define("EXERCISE_STATE_WAITVERIFY"          ,20); //独立习题、试卷待校审
define("EXERCISE_STATE_REPROCESS"           ,25); //返工
define("EXERCISE_STATE_DECLINE"             ,25); //校审不通过
define("EXERCISE_STATE_PAPEREXERCISEDECLINE",24); //试卷中的习题在试卷审核环节中不通过，若在分配标引及其以后环节打回，则状态置为25
define("EXERCISE_STATE_WAITASSIGN"          ,50); //待分派
define("EXERCISE_STATE_REASSIGN"            ,55); //重新分派
define("EXERCISE_STATE_WAITMARK"            ,60); //待标引
define("EXERCISE_STATE_FINISH"              ,70); //已完成
define("EXERCISE_STATE_UNINBOUND"           ,80); //未入库
define("EXERCISE_STATE_INBOUND"             ,90); //已入库
define("EXERCISE_STATE_UNONSHELF"           ,90); //未上架
define("EXERCISE_STATE_ONSHELF"             ,110); //上架
define("EXERCISE_STATE_OFFSHELF"            ,120); //下架

//****************************************账号状态********************************************
define("ACCOUNT_STATUS_NORMAL"  ,2); //账号是否禁用 1:禁用2:正常
define("ACCOUNT_STATUS_DISABLE"  ,1);
define("DELETE_STATUS_TRUE"  ,1); //账号是否删除 1:删除2:正常
define("DELETE_STATUS_FALSE"  ,2);

//*************************************用户习题试卷状态相关常量*******************************/
define('EXERCISE_HASINPUT',1);
define('EXERCISE_WAITVERIFY',2);
define('EXERCISE_REPROCESS',3);

//************************************删除标志位宏定义***************************************/
define('STATE_DELETED',1);
define('STATE_NORMAL',2);

//************************************锁定标志位宏定义***************************************/
define('LOCKSTATE_LOCK',1);
define('LOCKSTATE_NORMAL',2);

//************************************行为种类标志位宏定义***************************************/
define('BEHAVIOR_ABNORMAL',1); //异常
define('BEHAVIOR_NORMAL',2);   //正常


//************************************习题是否属于试卷标志位宏定义***************************************/
define('EXERCISE_NOT_BE_OFPAPER',1); //不属于
define('EXERCISE_BE_OFPAPER',2);     //属于
define('EXERCISE_BEORNOT_OFPAPER',3);     //任意
//************************************第三方平台标志位宏定义***************************************/
define('PLATFORM_AFFORD',1); //提供
define('PLATFORM_GET',2);     //获取

//************************************第三方发布资源类型宏定义***************************************/
define('RESOURCETYPE_EXERCISE',1); //习题
define('RESOURCETYPE_PAPER',2);     //试卷

//************************************课标知识树创建状态宏定义***************************************/
define('TREE_FINISH',  1);     //完成
define('TREE_CREATING',2);     //创建中
//************************************知识树日志表中操作是否为异常行为（1:异常2:正常）******************************************************
define('ABNORMAL',1);
define('NO_ABNORMAL',2);
//************************************模板匹配数字***************************************/
define('CASEONE',1);
define('CASETWO',2);
define('CASETHREE',3);
define('CASEFOUR',4);
define('CASEFIVE',5);
define('CASESIX',6);
define('ERRORCODE',400);
define('SUCCESSCODE',200);
define('PAGESIZE',20);

/*************************用户角色状态*******************************/
define('ROLE_IS_DELETE',2);
/****************************家长端列表页的排序**********************************/
define('TIME_ASC',1);
define('TIME_DESC',2);
define('SCORE_ASC',3);
define('SCORE_DESC',4);
?>
