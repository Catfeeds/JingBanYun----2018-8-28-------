--2017.03.08 14:18:00
--微信活动报名表
CREATE TABLE `activity_wx` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_name` varchar(20) NOT NULL COMMENT '学生姓名',
  `school_name` varchar(255) NOT NULL COMMENT '学校名字',
  `class_name` varchar(45) NOT NULL COMMENT '年级和班级',
  `class_teacher` varchar(20) NOT NULL COMMENT '指导老师',
  `telephone` varchar(20) NOT NULL COMMENT '联系方式',
  `content` text NOT NULL COMMENT '内容',
  `creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--2016.07.25 18:37:00 434619
alter table biz_bj_resource_zan add column role int(11) after resource_id;
alter table biz_bj_resource_zan change role role int(11) not null default 0;
alter table biz_bj_resource_zan change teacher_id user_id int(11);
alter table biz_bj_resource_zan change teacher_name user_name varchar(20);

alter table biz_bj_resource_collect add column role int(11) after resource_id;
alter table biz_bj_resource_collect change role role int(11) not null default 0;
alter table biz_bj_resource_collect change teacher_id user_id int(11);
alter table biz_bj_resource_collect change teacher_name user_name varchar(20);


--2016.7.29 13:29:00 434619
--增加全国省市区表dict_citydistrict,具体建表与内容见dict_citydistrict.sql
--数据流:
--                 loaddata                       loadSchoolData
--  学校信息CSV --------------> school_temp表 -------------------> dict_schoollist表
--                                               |
--                        dict_citydistrict表 ---
--
--学校信息临时表,通过调用loadSchoolData存储过程可将该表所存学校信息导入学校信息正式表dict_schoollist
drop table if exists school_temp;
create table school_temp
(
 schoolcode int,
 schoolName varchar(100),
 schoolAddress varchar(200),
 postalcode varchar(6),
 school_category varchar(20),
 district varchar(20)
);
--学校信息正式表
create table dict_schoollist (id int auto_increment,
school_name varchar(100),
school_address varchar(200),
school_category tinyint(4) not null default 0 COMMENT '0--幼儿园 1--小学 2--初中 3--高中',
joined_level_id tinyint(4) unsigned NOT NULL DEFAULT 0,
joined_area_id  mediumint(8) unsigned NOT NULL DEFAULT 0,
primary key (id)
);

--学校类型ID名称对应关系表
CREATE TABLE `dict_schoolcategory` (
  `id` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into dict_schoolcategory values(0,'幼儿园');
insert into dict_schoolcategory values(1,'小学');
insert into dict_schoolcategory values(2,'初中');
insert into dict_schoolcategory values(3,'高中');

--加载含有学校信息的CSV至学校信息临时表
load data infile 'd:\\school.csv' 
into table `school_temp` 
fields terminated by ',' optionally enclosed by '"' escaped by '"' 
lines terminated by '\r\n'; 

--loadSchoolData存储过程 
--功能:加载school_temp临时学校表的信息至dict_schoollist学校字典表
--入参:schoolcate  --学校种类ID ,配置见dict_schoolcategory表
--     province_id --省ID,即dict_citydistrict中的省或直辖市的ID
--     city_id     --市ID,即dict_citydistrict中城市的ID
--备注:对于直辖市的情况,需要将省ID与市ID需要设置相同
DELIMITER //
DROP PROCEDURE IF exists loadSchoolData;
create procedure loadSchoolData(IN schoolcate int,IN province_id int,IN city_id int)
 begin
    Declare currentCount int;
    Declare level_id int;
    Declare selectid int;
    IF city_id = -1 THEN               
     
      set level_id = 1;
      set selectid = province_id;
	 
	elseif city_id = province_id THEN  
      set level_id = 2;
      set selectid = city_id;
    else 
      set level_id = 3;
      set selectid = city_id;
	END IF;
    select count(1) into currentCount from dict_schoollist;
    if city_id = -1 then 
      insert into dict_schoollist 
		(
		select (currentCount=currentCount+1) as id,
		schoolName,
		schoolAddress,
		schoolcate as school_category,
		province_id,
		b.id as city_id,
		0 as district_id,
		'' as obligation_person,
		'' as obligation_tel,
		'' as obligation_email,
		1 as status
		from school_temp
		a join (select id,name from dict_citydistrict where level=level_id and upid=selectid) b on a.district = b.name ) ;
	else
 		insert into dict_schoollist
		(
		select (currentCount=currentCount+1) as id,
		schoolName,
		schoolAddress,
		schoolcate as school_category,
		province_id,
		city_id,
		b.id as district_id,
		'' as obligation_person,
		'' as obligation_tel,
		'' as obligation_email,
		1 as status
		from school_temp
		a join (select id,name from dict_citydistrict where level=level_id and upid=selectid) b on a.district = b.name ) ;
     END IF;   
 end
//
 
--2016.8.2 18:29:00 434619
--建立京版资源栏目表
 create table dict_channel (
id int unsigned not null auto_increment,
name varchar(60),
primary key(id)
);

alter table biz_bj_resources add column channel_id int(11) after school_term_id;
alter table biz_bj_resources change column channel_id channel_id int(11) default 0;
insert into dict_channel values(1,'足球栏目');
alter table biz_bj_resources add column isDisplay tinyint(4) default 1;

--2016.8.3 12:00:00 434619
create table auth_teacher_second
(
id bigint(20) not null auto_increment,
teacher_id bigint(20) not null,
`course_id` int(11) NOT NULL DEFAULT '1' COMMENT '学科编号',
`grade_id` int(11) NOT NULL DEFAULT '1' COMMENT '年级编号',
  PRIMARY KEY (`id`)
);

--2016.8.6 14:54:00 434619
--添加消息列表TABLE
create table notice_message
(
id int unsigned not null  auto_increment,
role tinyint(1) unsigned not null ,   --0:教师 1:学生 2:家长 注意该处定义
user_id int unsigned not null ,
msg_title varchar(200),
msg_content varchar(200),
create_at int(11),
read_status tinyint(1),
primary key (id),
KEY `index_query` (`role`,`user_id`) 
);
--添加班级移交信息表
create table biz_class_handsoff
(
id int unsigned not null  auto_increment,
dest_teacherid  int unsigned not null,
class_id int(11) not null,
primary key (id),
KEY `class_query` (`dest_teacherid`,`class_id`)
);
--2016.8.19 15:49:00 增加多个备课资料关联表
drop table if exists biz_lesson_planning_contact;
create table biz_lesson_planning_contact	(
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `biz_lesson_planning_id` int(10) unsigned NOT NULL COMMENT '备课资料主键ID',
  `type` varchar(20) NOT NULL COMMENT '类型',
   `create_html` tinyint(3) DEFAULT '0' COMMENT '是否生成对应html文件 0未生成 1已生成 ',
  `file_path` varchar(320) NOT NULL COMMENT '文件路径',
  `vid` varchar(50),
  PRIMARY KEY (`id`),
  KEY `biz_lesson_planning_id` (`biz_lesson_planning_id`)
 )
--2016.8.23 22:12:00 增加多个备课资料关联表
alter table biz_bj_resources modify status tinyint not null default 1; 

--教师资源
alter table biz_resource modify status tinyint not null default 1; 	

--京版概览	
alter taele biz_bj_overview add status tinyint not null default 1;	

alter table auth_admin add real_name varchar(32) not null;

alter table dict_schoollist add obligation_person varchar(32) NOT NULL comment '负责人姓名';
alter table dict_schoollist add obligation_tel varchar(20) NOT NULL comment '负责人手机号' ;
alter table dict_schoollist add obligation_email varchar(50) NOT NULL comment '负责人邮箱' ;
alter table dict_schoollist add status tinyint NOT NULL default 1;

alter table biz_resource_collect add column user_type tinyint(4) after teacher_id;

alter table biz_resource_collect change column teacher_id user_id int(11);

alter table biz_resource_collect change column teacher_name user_name varchar(32);

alter table biz_class add course_id int not null comment '学科ID' after grade_id;

alter table auth_teacher add sex enum('男','女') NOT NULL DEFAULT '男'  ;

alter table auth_parent add sex enum('男','女') NOT NULL DEFAULT '男';  

alter table auth_student add sex enum('男','女') NOT NULL DEFAULT '男'  ;
alter table auth_student add `birth_date` int(10) unsigned NOT NULL;

alter table biz_homework add `homework_type` varchar(32) NOT NULL comment '课堂作业,课后作业';
alter table biz_homework add `homework_status` tinyint NOT NULL comment '布置作业状态 0未布置,1已布置';
alter table biz_homework add read_ids varchar(500) NOT NULL comment '该班已读的学生id,逗号分隔';

alter tabel biz_exercise_library_chapter add festival varchar(100) not null comment '节' after chapter;
alter tabel biz_exercise_library_chapter add title varchar(320) not null comment '标题' after festival;

alter table biz_homework_score_details drop PRIMARY key;
alter table biz_homework_score_details add PRIMARY key(homework_id,student_id,question_org_id);

alter table biz_bj_resources add is_download tinyint not null ;

alter table biz_resource add file_path varchar(100) not NULL COMMENT '文件路径' after status;
alter table biz_resource add vid varchar(100) not NULL COMMENT '视频id（保利威视)' after file_path ;
alter table biz_resource add playerwidth varchar(20) not NULL COMMENT '视频大小(分辨率)' after vid ;
alter table biz_resource add playerduration varchar(20) not NULL COMMENT '视频市场' after playerwidth;

alter table biz_bj_resource_zan drop primary key ;
alter table biz_bj_resource_zan add primary key(resource_id,role,user_id);

drop table biz_homework_exercise;
CREATE TABLE `biz_homework_exercise` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `homework_id` int(11) NOT NULL COMMENT '作业ID',
  `exercise_id` bigint(20) NOT NULL COMMENT '习题ID',
  `chapter_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COMMENT='作业与习题关联表';

CREATE TABLE `biz_bj_resource_contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `biz_bj_resource_id` int(10) unsigned NOT NULL COMMENT '京版资源主键ID',
  `resource_path` varchar(320) NOT NULL COMMENT '就是文件路径',
  `vid` varchar(320) NOT NULL COMMENT '视频ID',
  `playerwidth` varchar(20) DEFAULT NULL COMMENT '视频大小(分辨率)',
  `playerduration` varchar(20) DEFAULT NULL COMMENT '视频时长',
  `flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0:未经过oss上传 1：经过oss上传',
  PRIMARY KEY (`id`),
  KEY `biz_bj_resource_id` (`biz_bj_resource_id`)
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8;

CREATE TABLE `biz_resource_contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `biz_resource_id` int(10) unsigned NOT NULL COMMENT '教师资源主键ID',
  `resource_path` varchar(320) NOT NULL COMMENT '就是文件路径',
  `vid` varchar(320) NOT NULL COMMENT '视频ID',
  `playerwidth` varchar(20) DEFAULT NULL COMMENT '视频大小(分辨率)',
  `playerduration` varchar(20) DEFAULT NULL COMMENT '视频时长',
  `flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0:未经过oss上传 1：经过oss上传',
  PRIMARY KEY (`id`),
  KEY `biz_bj_resource_id` (`biz_resource_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;

alter table biz_resource_collect drop primary key;
alter table biz_resource_collect add primary key (resource_id,user_id,user_type);
--2016.08.24 20:00:00 add biz_resouce_zan table
drop table if exists biz_resource_zan;
 CREATE TABLE `biz_resource_zan` (
  `resource_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` tinyint(4) NOT NULL,
  `create_at` int(11) NOT NULL,
  `user_name` varchar(32) NOT NULL,
  PRIMARY KEY (`resource_id`,`user_id`,`user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='资源点赞表' ;

--2016.8.25 15:09:00 add converted flag,flag_pdf and ppt_pages to biz_lesson_planning_contact table
--0 unconvert 1 converted
alter table biz_lesson_planning_contact add column flag tinyint(4) default 0 ;
alter table biz_lesson_planning_contact add column flag_pdf tinyint(4) default 0 ;
alter table biz_lesson_planning_contact add column ppt_pages int(11) default 0 ;

--2016.8.29 15:13:00 add flag field to biz_resource table
alter table biz_resource add `flag` tinyint NOT NULL COMMENT '1是pdf未转换,2是pdf已转换'
--2016.8.31 14:16:00 add 9年一贯制 12年一贯制 to schoolcategory
insert into dict_schoolcategory values (4,'九年一贯制');
insert into dict_schoolcategory values (5,'十二年一贯制');

--2016.8.31 15:35:00 modify loadschooldata procedure
--loadSchoolData存储过程 
--功能:加载school_temp临时学校表的信息至dict_schoollist学校字典表
--入参:schoolcate  --学校种类ID ,配置见dict_schoolcategory表
--     province_id --省ID,即dict_citydistrict中的省或直辖市的ID
--     city_id     --市ID,即dict_citydistrict中城市的ID
--备注:对于直辖市的情况,需要将省ID与市ID需要设置相同
DELIMITER //
DROP PROCEDURE IF exists loadSchoolData;
create procedure loadSchoolData(IN schoolcate int,IN province_id int,IN city_id int)
 begin
    Declare currentCount int;
    Declare level_id int;
    Declare selectid int;
    IF city_id = -1 THEN               
     
      set level_id = 1;
      set selectid = province_id;
	 
	elseif city_id = province_id THEN  
      set level_id = 2;
      set selectid = city_id;
    else 
      set level_id = 3;
      set selectid = city_id;
	END IF;
    select count(1) into currentCount from dict_schoollist;
    if city_id = -1 then 
      insert into dict_schoollist 
		(
		select (currentCount=currentCount+1) as id,
		schoolName,
		schoolAddress,
		schoolcate as school_category,
		province_id,
		b.id as city_id,
		0 as district_id,
		'' as obligation_person,
		'' as obligation_tel,
		'' as obligation_email,
		1 as status,
		0 as flag
		from school_temp
		a join (select id,name from dict_citydistrict where level=level_id and upid=selectid) b on a.district = b.name ) ;
	else
 		insert into dict_schoollist
		(
		select (currentCount=currentCount+1) as id,
		schoolName,
		schoolAddress,
		schoolcate as school_category,
		province_id,
		city_id,
		b.id as district_id,
		'' as obligation_person,
		'' as obligation_tel,
		'' as obligation_email,
		1 as status,
		0 as flag
		from school_temp
		a join (select id,name from dict_citydistrict where level=level_id and upid=selectid) b on a.district = b.name ) ;
     END IF;   
 end
//


--2016.9.3 add role column into auth_teacher table
alter table auth_teacher add column role tinyint(4) default 0 COMMENT '0--一般教师 1--市级教研 2--区级教研'

--2016.9.8 modify comment of biz_class_student
alter table biz_class_student modify column status tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:等待审核2：已经通过审核3:拒绝审核'

--2016.9.12 14:03:00 add VIP TABLES
-- ----------------------------
-- Table structure for account_auth
-- ----------------------------
DROP TABLE IF EXISTS `account_auth`;
CREATE TABLE `account_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '权限表自增id',
  `auth_name` varchar(50) DEFAULT NULL COMMENT '权限名称',
  `create_at` int(11) DEFAULT NULL COMMENT '创建时间时间戳',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '--1为正常 --0为删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='权限表内容由后台写死';

-- ----------------------------
-- Records of account_auth
-- ----------------------------
INSERT INTO `account_auth` VALUES ('1', '游客模式', '1473214399', '1');
INSERT INTO `account_auth` VALUES ('2', '普通权限', '1473214425', '1');
INSERT INTO `account_auth` VALUES ('3', '团体VIP', '1473214449', '1');

-- ----------------------------
-- Table structure for account_auth_notes
-- ----------------------------
DROP TABLE IF EXISTS `account_auth_notes`;
CREATE TABLE `account_auth_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '权限时间表自增id',
  `timetype` tinyint(1) DEFAULT NULL COMMENT '时间类型1为试用时间 2为使用时间',
  `viptimelong` int(11) DEFAULT NULL COMMENT 'vip时长单位天',
  `editexpiretime` int(11) DEFAULT NULL COMMENT '修改后到期时间',
  `addtime` int(11) DEFAULT NULL COMMENT '数据入库时间',
  `root_id` int(11) DEFAULT NULL COMMENT '操作人id',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注数据',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `role_id` int(11) DEFAULT NULL COMMENT '角色id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=387 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of account_auth_notes
-- ----------------------------
INSERT INTO `account_auth_notes` VALUES ('357', '2', '2', '1472918400', '1473413367', '1', 'admin操作: 开通团体VIP权限', '990', '1');
INSERT INTO `account_auth_notes` VALUES ('358', '2', '2', '1472918400', '1473413368', '1', 'admin操作: 开通团体VIP权限', '26', '4');
INSERT INTO `account_auth_notes` VALUES ('359', '2', '2', '1472918400', '1473413368', '1', 'admin操作: 开通团体VIP权限', '27', '4');
INSERT INTO `account_auth_notes` VALUES ('360', '2', '2', '1472918400', '1473413368', '1', 'admin操作: 开通团体VIP权限', '217', '3');
INSERT INTO `account_auth_notes` VALUES ('361', '2', '2', '1472918400', '1473413368', '1', 'admin操作: 开通团体VIP权限', '218', '3');
INSERT INTO `account_auth_notes` VALUES ('362', '2', '2', '1472918400', '1473413368', '1', 'admin操作: 开通团体VIP权限', '987', '2');
INSERT INTO `account_auth_notes` VALUES ('363', '2', '2', '1472918400', '1473413368', '1', 'admin操作: 开通团体VIP权限', '988', '2');
INSERT INTO `account_auth_notes` VALUES ('364', '2', '2', '1472918400', '1473413368', '1', 'admin操作: 开通团体VIP权限', '1008', '2');
INSERT INTO `account_auth_notes` VALUES ('365', '2', '2', '1472918400', '1473413368', '1', 'admin操作: 开通团体VIP权限', '1009', '2');
INSERT INTO `account_auth_notes` VALUES ('366', '2', '2', '1472918400', '1473413368', '1', 'admin操作: 开通团体VIP权限', '1010', '2');
INSERT INTO `account_auth_notes` VALUES ('367', '2', '2', '1472918400', '1473413368', '1', 'admin操作: 开通团体VIP权限', '1011', '2');
INSERT INTO `account_auth_notes` VALUES ('368', '2', '2', '1472918400', '1473413368', '1', 'admin操作: 开通团体VIP权限', '1012', '2');
INSERT INTO `account_auth_notes` VALUES ('369', '2', '2', '1472918400', '1473413368', '1', 'admin操作: 开通团体VIP权限', '1013', '2');
INSERT INTO `account_auth_notes` VALUES ('370', '2', '2', '1472918400', '1473413368', '1', 'admin操作: 开通团体VIP权限', '1014', '2');
INSERT INTO `account_auth_notes` VALUES ('371', '2', '2', '1472918400', '1473413368', '1', 'admin操作: 开通团体VIP权限', '1015', '2');
INSERT INTO `account_auth_notes` VALUES ('372', '2', '5', '1473177600', '1473413398', '1', 'admin操作: 开通普通权限权限', '990', '1');
INSERT INTO `account_auth_notes` VALUES ('373', '2', '5', '1473177600', '1473413398', '1', 'admin操作: 开通普通权限权限', '26', '4');
INSERT INTO `account_auth_notes` VALUES ('374', '2', '5', '1473177600', '1473413398', '1', 'admin操作: 开通普通权限权限', '27', '4');
INSERT INTO `account_auth_notes` VALUES ('375', '2', '5', '1473177600', '1473413398', '1', 'admin操作: 开通普通权限权限', '217', '3');
INSERT INTO `account_auth_notes` VALUES ('376', '2', '5', '1473177600', '1473413398', '1', 'admin操作: 开通普通权限权限', '218', '3');
INSERT INTO `account_auth_notes` VALUES ('377', '2', '5', '1473177600', '1473413398', '1', 'admin操作: 开通普通权限权限', '987', '2');
INSERT INTO `account_auth_notes` VALUES ('378', '2', '5', '1473177600', '1473413398', '1', 'admin操作: 开通普通权限权限', '988', '2');
INSERT INTO `account_auth_notes` VALUES ('379', '2', '5', '1473177600', '1473413398', '1', 'admin操作: 开通普通权限权限', '1008', '2');
INSERT INTO `account_auth_notes` VALUES ('380', '2', '5', '1473177600', '1473413398', '1', 'admin操作: 开通普通权限权限', '1009', '2');
INSERT INTO `account_auth_notes` VALUES ('381', '2', '5', '1473177600', '1473413398', '1', 'admin操作: 开通普通权限权限', '1010', '2');
INSERT INTO `account_auth_notes` VALUES ('382', '2', '5', '1473177600', '1473413398', '1', 'admin操作: 开通普通权限权限', '1011', '2');
INSERT INTO `account_auth_notes` VALUES ('383', '2', '5', '1473177600', '1473413398', '1', 'admin操作: 开通普通权限权限', '1012', '2');
INSERT INTO `account_auth_notes` VALUES ('384', '2', '5', '1473177600', '1473413398', '1', 'admin操作: 开通普通权限权限', '1013', '2');
INSERT INTO `account_auth_notes` VALUES ('385', '2', '5', '1473177600', '1473413398', '1', 'admin操作: 开通普通权限权限', '1014', '2');
INSERT INTO `account_auth_notes` VALUES ('386', '2', '5', '1473177600', '1473413398', '1', 'admin操作: 开通普通权限权限', '1015', '2');

-- ----------------------------
-- Table structure for account_auth_to_node
-- ----------------------------
DROP TABLE IF EXISTS `account_auth_to_node`;
CREATE TABLE `account_auth_to_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '权限对应功能自增id',
  `auth_id` int(11) DEFAULT NULL COMMENT '权限id',
  `users_type_id` int(11) DEFAULT NULL COMMENT '用户类型id',
  `node_id` varchar(255) DEFAULT NULL COMMENT '功能节点id 以逗号分隔存储',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '--1为正常 --0为删除',
  `create_at` int(11) DEFAULT NULL COMMENT '添加数据的时间戳',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='权限功能对应表 后台数据写死';

-- ----------------------------
-- Records of account_auth_to_node
-- ----------------------------
INSERT INTO `account_auth_to_node` VALUES ('1', '3', '3', '1,4,5,6,7,3', '1', '1473249273');
INSERT INTO `account_auth_to_node` VALUES ('5', '2', '4', '1,3,12,13,14,15,16,17,18,19,20,21,22', '1', '1473249303');
INSERT INTO `account_auth_to_node` VALUES ('6', '2', '2', '1,4,5,6,7,2,8,9,10,11,3,12,13,14,15,16,17,18,19,20,21,22', '1', '1473249316');
INSERT INTO `account_auth_to_node` VALUES ('7', '3', '2', '2,8,9,10,11,3,12,13,14', '1', '1473249494');

-- ----------------------------
-- Table structure for account_node_list
-- ----------------------------
DROP TABLE IF EXISTS `account_node_list`;
CREATE TABLE `account_node_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '节点自增id',
  `node_name` varchar(50) DEFAULT NULL COMMENT '功能节点名称',
  `create_at` int(11) DEFAULT NULL COMMENT '节点添加时间戳',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '--1为正常 --0为删除',
  `fid` int(11) DEFAULT '0' COMMENT '节点父id 一级默认为0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='功能表 内容后台写死';

-- ----------------------------
-- Records of account_node_list
-- ----------------------------
INSERT INTO `account_node_list` VALUES ('1', '励耘圈', '1473216009', '1', '0');
INSERT INTO `account_node_list` VALUES ('2', '教学+', '1473216034', '1', '0');
INSERT INTO `account_node_list` VALUES ('3', '班级行', '1473216131', '1', '0');
INSERT INTO `account_node_list` VALUES ('4', '京版概览', '1473216161', '1', '1');
INSERT INTO `account_node_list` VALUES ('5', '专家资讯', '1473216214', '1', '1');
INSERT INTO `account_node_list` VALUES ('6', '京版活动', '1473216243', '1', '1');
INSERT INTO `account_node_list` VALUES ('7', '教师风采', '1473216293', '1', '1');
INSERT INTO `account_node_list` VALUES ('8', '京版资源', '1473216329', '1', '2');
INSERT INTO `account_node_list` VALUES ('9', '备课系统', '1473216424', '1', '2');
INSERT INTO `account_node_list` VALUES ('10', '电子课本', '1473216431', '1', '2');
INSERT INTO `account_node_list` VALUES ('11', '教师资源分享', '1473216467', '1', '2');
INSERT INTO `account_node_list` VALUES ('12', '数字课堂', '1473216616', '1', '3');
INSERT INTO `account_node_list` VALUES ('13', '小黑板', '1473216626', '1', '3');
INSERT INTO `account_node_list` VALUES ('14', '学习轨迹', '1473216632', '1', '3');
INSERT INTO `account_node_list` VALUES ('15', '作业系统', '1473216640', '1', '3');
INSERT INTO `account_node_list` VALUES ('16', '班级信息管理', '1473216646', '1', '3');
INSERT INTO `account_node_list` VALUES ('17', '小黑板', '1473216653', '1', '3');
INSERT INTO `account_node_list` VALUES ('18', '作业系统', '1473216688', '1', '3');
INSERT INTO `account_node_list` VALUES ('19', '我的班级', '1473216699', '1', '3');
INSERT INTO `account_node_list` VALUES ('20', '学习轨迹', '1473216706', '1', '3');
INSERT INTO `account_node_list` VALUES ('21', '作业系统', '1473216712', '1', '3');
INSERT INTO `account_node_list` VALUES ('22', '家长督学', '1473216739', '1', '3');

-- ----------------------------
-- Table structure for account_user_and_auth
-- ----------------------------
DROP TABLE IF EXISTS `account_user_and_auth`;
CREATE TABLE `account_user_and_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户与权限自增id',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `role_id` int(11) DEFAULT NULL COMMENT '用户角色id',
  `auth_id` int(11) DEFAULT NULL COMMENT '用户权限id',
  `auth_start_time` int(11) DEFAULT NULL COMMENT '权限开始时间',
  `auth_end_time` int(11) DEFAULT NULL COMMENT '用户结束时间',
  `status` tinyint(1) DEFAULT '1' COMMENT '--1为正常 --0为删除',
  `timetype` tinyint(1) DEFAULT NULL COMMENT '时间类型1为试用时间 2为使用时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=281 DEFAULT CHARSET=utf8 COMMENT='用户与权限时间表';

-- ----------------------------
-- Records of account_user_and_auth
-- ----------------------------
INSERT INTO `account_user_and_auth` VALUES ('253', '26', '4', '3', '1472745600', '1472918400', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('254', '27', '4', '3', '1472745600', '1472918400', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('255', '217', '3', '3', '1472745600', '1472918400', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('256', '218', '3', '3', '1472745600', '1472918400', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('257', '987', '2', '3', '1472745600', '1472918400', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('258', '988', '2', '3', '1472745600', '1472918400', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('259', '1008', '2', '3', '1472745600', '1472918400', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('260', '1009', '2', '3', '1472745600', '1472918400', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('261', '1010', '2', '3', '1472745600', '1472918400', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('262', '1011', '2', '3', '1472745600', '1472918400', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('263', '1012', '2', '3', '1472745600', '1472918400', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('264', '1013', '2', '3', '1472745600', '1472918400', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('265', '1014', '2', '3', '1472745600', '1472918400', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('266', '1015', '2', '3', '1472745600', '1472918400', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('267', '26', '4', '2', '1472745600', '1473177600', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('268', '27', '4', '2', '1472745600', '1473177600', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('269', '217', '3', '2', '1472745600', '1473177600', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('270', '218', '3', '2', '1472745600', '1473177600', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('271', '987', '2', '2', '1472745600', '1473177600', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('272', '988', '2', '2', '1472745600', '1473177600', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('273', '1008', '2', '2', '1472745600', '1473177600', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('274', '1009', '2', '2', '1472745600', '1473177600', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('275', '1010', '2', '2', '1472745600', '1473177600', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('276', '1011', '2', '2', '1472745600', '1473177600', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('277', '1012', '2', '2', '1472745600', '1473177600', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('278', '1013', '2', '2', '1472745600', '1473177600', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('279', '1014', '2', '2', '1472745600', '1473177600', '1', '2');
INSERT INTO `account_user_and_auth` VALUES ('280', '1015', '2', '2', '1472745600', '1473177600', '1', '2');

-- ----------------------------
-- Table structure for account_users_type
-- ----------------------------
DROP TABLE IF EXISTS `account_users_type`;
CREATE TABLE `account_users_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户类型自增id',
  `type_name` varchar(50) DEFAULT NULL COMMENT '用户类型名称',
  `create_at` int(11) DEFAULT NULL COMMENT '添加用户类型的时间戳',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '--1为正常 --0为删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='用户类型表 内容写死';

-- ----------------------------
-- Records of account_users_type
-- ----------------------------
INSERT INTO `account_users_type` VALUES ('1', '学校', '1473215326', '1');
INSERT INTO `account_users_type` VALUES ('2', '教师', '1473215347', '1');
INSERT INTO `account_users_type` VALUES ('3', '学生', '1473215361', '1');
INSERT INTO `account_users_type` VALUES ('4', '家长', '1473215376', '1');
INSERT INTO `account_users_type` VALUES ('5', '游客', '1473215376', '1');

 
alter table dict_schoollist add user_auth int not null COMMENT '用户权限';
alter table dict_schoollist add auth_start_time int not null COMMENT '权限开始时间';
alter table dict_schoollist add auth_end_time int not null COMMENT '权限结束时间';
alter table dict_schoollist add timetype tinyint not null COMMENT '时间类型1为试用时间 2为使用时间' ; 

--2016.9.27 11:38:00 增加APP用户访问统计表  434619
drop table if exists app_statistics;
create table app_statistics(
  id bigint NOT NULL AUTO_INCREMENT COMMENT '自增id',
  user_type tinyint(4) NOT NULL,
  user_id   int NOT NULL,  
  machine_type varchar(100) NOT NULL COMMENT '机器类型',
  ip_address  varchar(16) NOT NULL COMMENT 'IP地址',
  create_at int(11) DEFAULT NULL COMMENT '添加数据的时间戳',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--2016.9.28 15:37:00 增加我的素材表
CREATE TABLE `biz_material` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID', 
  `type` varchar(20) NOT NULL COMMENT '资源类别 Text Image Video PPT Flash', 
   teacher_id int UNSIGNED not null, 
  `create_at` int(11) NOT NULL COMMENT '创建于',     
  `file_path` varchar(100) DEFAULT NULL COMMENT '文件路径',
  `vid` varchar(100) DEFAULT NULL COMMENT '视频id（保利威视）', 
  `flag` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1是pdf未转换,2是新版已转换',
  PRIMARY KEY (`id`),index(teacher_id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='我的素材表'

--2016.9.30 14:49:00 备课资料表增加资料版本字段
  ALTER TABLE biz_lesson_planning add column ver tinyint(4) 
  ALTER TABLE biz_lesson_planning_contact add column content text after file_path
  SET SQL_SAFE_UPDATES = 0
  UPDATE biz_lesson_planning set ver=1 where type <> ''
  UPDATE biz_lesson_planning set ver=2 where type = '' or type is null

--2016.9.30 15:53:00 京版活动表增加上传附件路径
alter table social_activity_register add file_path varchar(320) not null comment '报名者上传文件路径'
alter table social_activity add is_upload tinyint not null comment '是否支持上传,0不支持,1支持上传'
alter table biz_material add material_name varchar(320) not null comment '素材名称'  
--2016.10.3 16:51:00 京版资源增加保利威视原始视频字段
alter table biz_bj_resources add column vid_fullpath varchar(100) COMMENT '保利威视原始视频地址'

--2016.10.3 16:51:00 京版资源教师资源分享增加保利威视原始视频截图字段
alter table biz_bj_resources add vid_image_path varchar(320) not null
alter table biz_resource add vid_image_path varchar(320) not null
alter table biz_material add vid_image_path varchar(320) not null
alter table biz_material add vid_fullpath varchar(320) not null

--2016.10.18 16:14:00 教师资源分享素材库增加视频是否转换字段
alter table biz_resource_contact add is_transition tinyint not null comment '是否转变,0为未转变,1为已转变或不需要转变的'
alter table biz_material add is_transition tinyint not null comment '是否转变,0为未转变,1为已转变或不需要转变的' 

--2016.10.19 10:01:00 京版资源教师资源分享素材库增加PPT是否转换标志与页数
alter table biz_bj_resource_contact add column ppt_html tinyint(4) default 0 comment '0未转换1已转换'
alter table biz_resource_contact add column ppt_html tinyint(4) default 0 comment '0未转换1已转换'
alter table biz_material add column ppt_html tinyint(4) default 0 comment '0未转换1已转换'

alter table biz_bj_resource_contact add column ppt_pages int default 0 comment 'PPT页数'
alter table biz_resource_contact add column ppt_pages int default 0 comment 'PPT页数'
alter table biz_material add column ppt_pages int default 0 comment 'PPT页数'

--2016.10.25 14:43:00 增加用户访问轨迹表
create table user_access(
 id bigint not null auto_increment,
 role tinyint(4) not null,
 user_id int not null default 0,
 ip_address varchar(20),
 user_agent varchar(150),
 http_refer  varchar(200),
 access_url varchar(200),
 access_time int(11),
 primary key (`id`),
 key `key` (`role`,`user_id`) 
)
--2016.10.29 add index of dict_citydistrict
alter table dict_citydistrict add index(name)
--2016.11.03 add field to biz_class table
alter table biz_class add class_type tinyint UNSIGNED not null comment '班级类型 1为校内班,2为校外班' after name
CREATE TABLE `biz_class_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `biz_class_id` int UNSIGNED not null COMMENT '班级id',    
  `teacher_id` int(11) NOT NULL,
  `flag` tinyint(4) NOT NULL DEFAULT '1' COMMENT '标示 1->正常',
  `create_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `key_id`(`biz_class_id`,`teacher_id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='校内班级关联表'
 
alter table biz_exercise_collect modify id BIGINT UNSIGNED not null
alter table biz_exercise_collect drop PRIMARY key

alter table biz_exercise_collect add UNIQUE(id)
alter table biz_exercise_collect add primary key(role,user_id,exercise_id)
alter table biz_exercise_collect modify id bigint auto_increment

alter table social_activity_favor modify id int UNSIGNED not null
alter table social_activity_favor drop PRIMARY key
alter table social_activity_favor add UNIQUE(id)
alter table social_activity_favor add primary key(social_activity_id,user_id,user_type)
alter table social_activity_favor modify id int auto_increment

alter table biz_class add note varchar(320) not null comment '备注笔记'

CREATE TABLE `biz_isread_blackboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `role_id` tinyint(3) unsigned NOT NULL COMMENT '角色id,2老师,3学生,4家长',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `b_id` int(11) DEFAULT NULL COMMENT '小黑板id',
  `read_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4

alter table biz_isread_blackboard add `read_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 

alter table biz_blackboard add is_transition tinyint UNSIGNED not null comment '是否转换,1不用转换,0需要转换'  
alter table biz_blackboard modify message text not null

--2016.11.17 add message table
CREATE TABLE `role_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` varchar(30) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL COMMENT '百度推送的标题',
  `truncated_title` varchar(100) DEFAULT NULL COMMENT 'content截断生成的标题',
  `message_content` text COMMENT '消息体内容',
  `receive_num` int(11) DEFAULT NULL COMMENT '添加数量',
  `receive_type` int(12) DEFAULT NULL COMMENT '1.app推送 2.个人中心 3.app推送和个人中心',
  `send_time` varchar(64) DEFAULT NULL COMMENT '发送时间',
  `status` int(12) DEFAULT '1' COMMENT '1.未发送2.发送成功3.发送失败。4.已撤回 5.撤回失败',
  `message_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1.后台手工创建的消息 2.系统自动创建消息',
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='消息角色接收表'

CREATE TABLE `receive_message_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(255) DEFAULT NULL COMMENT '对应role_message中的id',
  `role_id` int(255) DEFAULT NULL COMMENT '选择的角色id',
  `user_id` int(255) DEFAULT NULL COMMENT '用户的id',
  `addtime` varchar(32) DEFAULT NULL COMMENT '接收时间',
  `is_read` int(11) DEFAULT '1' COMMENT '1：未读 2：已读',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='发送人对应表'

alter table auth_teacher add column machine_type tinyint(4) not null default 0 COMMENT '0--无效机型　1--IOS 2--android'
alter table auth_student add column machine_type tinyint(4) not null default 0 COMMENT '0--无效机型　1--IOS 2--android'
alter table auth_parent add column machine_type tinyint(4) not null default 0 COMMENT '0--无效机型　1--IOS 2--android'

alter table auth_teacher add login_address varchar(320) not null;
alter table auth_student add login_address varchar(320) not null;
alter table auth_parent add login_address varchar(320) not null;
--2016.11.28 add message table
alter table role_message add column truncated_title varchar(100) COMMENT 'content截断生成的标题' after title 
--2016.12.03 京版资源 专家资讯 京版活动增加推送标志位
alter table biz_bj_resources add push_status tinyint UNSIGNED not null comment '0代表不推送,1代表需要推送,2代表已经推送'
alter table social_expert_information add push_status tinyint UNSIGNED default 1 not null comment '1未推送,2代表已经推送'  
alter table social_activity add push_status tinyint UNSIGNED default 1 not null comment '1未推送,2代表已经推送'  

alter table user_access add column `access_date` int(11) NOT NULL;
alter table user_access add key `access_date` (`access_date`);


--2016.12.07 add activity tables
alter table biz_exercise_library add video_file_path varchar(300) NOT NULL COMMENT '视频路径'
alter table social_activity_class add parent_id tinyint UNSIGNED not null comment '父级id'

alter table social_activity add apply_people_number int UNSIGNED not null comment '允许的报名申请人数'
alter table social_activity add works_show_status tinyint UNSIGNED not null comment '该活动作品展示状态,这个是根据所有审核通过的人都打分了这里才展示 0不展示,1展示'


alter table social_activity_register add invitation_code varchar(60) not null comment '邀请码'

 
ALTER TABLE `social_activity`
ADD COLUMN `code_num`  int(12)  DEFAULT NULL COMMENT '邀请码个数' AFTER `works_show_status`;

ALTER TABLE `social_activity`
ADD COLUMN `remark`  VARCHAR(255)  DEFAULT NULL COMMENT '备注' AFTER `code_num`;

ALTER TABLE `social_activity`
ADD COLUMN `activitystart`  VARCHAR(25)  DEFAULT NULL COMMENT '活动开始时间' AFTER `remark`;

ALTER TABLE `social_activity`
ADD COLUMN `activityend`  VARCHAR(25)  DEFAULT NULL COMMENT '活动结束时间' AFTER `activitystart`;

ALTER TABLE `social_activity`
ADD COLUMN `applystart`  VARCHAR(25)  DEFAULT NULL COMMENT '报名开始时间' AFTER `activityend`;

ALTER TABLE `social_activity`
ADD COLUMN `applyend`  VARCHAR(25)  DEFAULT NULL COMMENT '报名结束时间' AFTER `applystart`;

ALTER TABLE `social_activity`
ADD COLUMN `is_pack`  VARCHAR(255)  DEFAULT '1' COMMENT '打包的url' AFTER `applyend`;

ALTER TABLE `social_activity`
ADD COLUMN `is_grade_select`  int(12)  DEFAULT '2' COMMENT '是否选中1选中2未选中' AFTER `is_disable`;

ALTER TABLE `social_activity`
ADD COLUMN `is_course_select`  int(12)  DEFAULT '2' COMMENT '是否选中1选中2未选中' AFTER `is_grade_select`;

ALTER TABLE `social_activity`
ADD COLUMN `is_generate`  int(12)  DEFAULT '1' COMMENT '1为不生成 2生成' AFTER `is_course_select`;

ALTER TABLE `social_activity_contact_file`
ADD COLUMN `type`  VARCHAR(25)  DEFAULT NULL COMMENT '文件类型' AFTER `create_at`;

ALTER TABLE `social_activity_contact_file`
ADD COLUMN `vid_fullpath`  VARCHAR(320)  DEFAULT NULL COMMENT '保利威视路径' AFTER `create_at`;

DROP TABLE IF EXISTS `activity_download_url`;
CREATE TABLE `activity_download_url` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) DEFAULT NULL COMMENT '活动id',
  `result_url` varchar(255) DEFAULT NULL COMMENT '资源路径',
  `status` int(11) DEFAULT '1' COMMENT '1为未执行2已执行',
  `oss_url` varchar(255) DEFAULT NULL COMMENT 'oss存放路径',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;


CREATE TABLE `social_activity_invitation_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) NOT NULL COMMENT '活动id',
  `invitation_code` varchar(60) NOT NULL COMMENT '邀请码',
  `status` tinyint(3) unsigned NOT NULL COMMENT '1为未使用,2为已使用',
  `create_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invitation_code` (`invitation_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='活动邀请码表'



CREATE TABLE `social_activity_contact_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int not null comment '活动id',
	`activity_file_name` varchar(300) not null comment '活动关联资料文件的名字',
	`activity_file_path` varchar(300) not null comment '活动关联资料文件的路径',
	`create_at` int UNSIGNED not null  ,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='活动关联的资料文件表'
   

 CREATE TABLE `social_activity_works` (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `activity_register_id` int UNSIGNED comment '活动注册表id',
  `course` int UNSIGNED,
  `grade` int UNSIGNED,
  `works_name` varchar(320),
  `works_description` text,
  `author_remarks` varchar(320),  
  create_at int UNSIGNED not null,
  update_at int UNSIGNED not null,
  browse_number int UNSIGNED not null,
  status tinyint UNSIGNED not null comment '审核状态 0为待审核 1为通过 2为拒绝',
  point int UNSIGNED not null comment '该作品得分',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='京版活动作品表' 
alter table social_activity_works add zan_count tinyint UNSIGNED not null  ;
alter table social_activity_works add favor_count tinyint UNSIGNED not null ;
alter table social_activity_works add column `voted_title` varchar(255) default NULL COMMENT '参评课题';
alter table social_activity_works add column `error_data` varchar(255) default NULL COMMENT '拒绝通过的信息';

 CREATE TABLE `social_activity_works_file` (
 `id` int(11) NOT NULL AUTO_INCREMENT, 
`activity_works_id` int UNSIGNED not null comment '京版活动作品表Id', 
`works_file_name` varchar(320) not null COMMENT '作品文件的名字',
`works_file_path` varchar(320) not null COMMENT '作品文件的路径',
  create_at int UNSIGNED not null, 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='京版活动作品文件表'


CREATE TABLE `social_activity_works_favor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_works_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` int(11) NOT NULL COMMENT '2:教师 3：学生 4：家长',
  `favor_time` int(11) NOT NULL,
  PRIMARY KEY (`activity_works_id`,`user_id`,`user_type`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='京版活动作品-收藏的详情表'


CREATE TABLE `social_activity_works_zan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_works_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` int(11) NOT NULL DEFAULT '1' COMMENT '2:教师 3：学生 4：家长',
  `zan_time` int(11) NOT NULL,
  PRIMARY KEY (`activity_works_id`,`user_id`,`user_type`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='京版活动作品-赞的详情表'

alter table social_activity_class add background_image varchar(320) not null

insert into social_activity_class (id,class,sort_order) value(4,'研讨会',4);
insert into social_activity_class (id,class,sort_order) value(5,'作品评比',5);
insert into social_activity_class (id,class,parent_id) value(6,'教学设计',5);
insert into social_activity_class (id,class,parent_id) value(7,'教学案例',5);
insert into social_activity_class (id,class,parent_id) value(8,'课件/教案',5);
insert into social_activity_class (id,class,parent_id) value(9,'教学论文',5);
insert into social_activity_class (id,class,parent_id) value(10,'教学微课',5);

update social_activity_class set background_image='http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/activity/category/jiaoxuesheji@2x.png' where id=6
update social_activity_class set background_image='http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/activity/category/jiaoxueanli@2x.png' where id=7
update social_activity_class set background_image='http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/activity/category/kejianjiaoan@2x.png' where id=8
update social_activity_class set background_image='http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/activity/category/jiaoxuelunwen@2x.png' where id=9
update social_activity_class set background_image='http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/activity/category/jiaoxueweike@2x.png' where id=10

alter table social_activity_works_file add type VARCHAR(25) not null comment '文件类型'
alter table social_activity_contact_file add type  VARCHAR(25) not null comment '文件类型'

alter table social_activity_contact_file add vid_fullpath varchar(320) not null
alter table social_activity_contact_file add vid varchar(320) not null

alter table social_activity_works_file add vid_fullpath varchar(320) not null
alter table social_activity_works_file add vid varchar(320) not null

alter table social_activity_register add lesson varchar(320) not null comment '参评课题'
alter table social_activity_register add province int UNSIGNED not null comment '教师填写的所属省份'
alter table social_activity_register add city int UNSIGNED not null comment '教师填写的所属城市'
alter table social_activity_register add district int UNSIGNED not null comment '教师填写的所属区县'
alter table social_activity_register add sex enum('男','女') NOT NULL DEFAULT '男' 
alter table social_activity_register add age tinyint NOT NULL comment '年龄'
alter table social_activity_register add positions tinyint NOT NULL comment '职称'
alter table social_activity_register add education tinyint NOT NULL comment '学历'
alter table social_activity_register add email varchar(320) NOT NULL comment '邮箱'
alter table social_activity_register add school_id int UNSIGNED not null comment '学校id'
alter table social_activity_register add school_address varchar(320) not null 
alter table social_activity_register add post_code varchar(32) not null comment '邮编'
alter table social_activity_register add tel varchar(32) not null comment '电话'
alter table social_activity_register add telephone varchar(32) not null comment '手机号'
alter table social_activity_register add local_course tinyint UNSIGNED not null comment '地方课程 1是,0否'
alter table social_activity_register add school_course tinyint UNSIGNED not null comment '学校课程 1是,0否'
alter table social_activity_register add course tinyint UNSIGNED not null comment '教师所学学科' after lesson

CREATE TABLE `social_activity_course_grade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
	 activity_id int UNSIGNED not null,
	 grade int UNSIGNED not null,  
	 course int UNSIGNED not null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='活动年级学科关联表'
alter table social_activity_course_grade add index(grade);
alter table social_activity_course_grade add index(course);


alter table social_activity_works_file add column `flag` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1是pdf未转换,2是新版已转换';
alter table social_activity_works_file add column vid_image_path varchar(320) not null COMMENT '截图路径';
alter table social_activity_works_file add is_transition tinyint not null comment '是否转变,0为未转变,1为已转变或不需要转变的';
alter table social_activity_works_file add column ppt_html tinyint(4) default 0 comment '0未转换1已转换';
alter table social_activity_works_file add column ppt_pages int default 0 comment 'PPT页数';

alter table social_activity_contact_file add column `flag` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1是pdf未转换,2是新版已转换';
alter table social_activity_contact_file add column vid_image_path varchar(320) not null COMMENT '截图路径';
alter table social_activity_contact_file add is_transition tinyint not null comment '是否转变,0为未转变,1为已转变或不需要转变的';
alter table social_activity_contact_file add column ppt_html tinyint(4) default 0 comment '0未转换1已转换';
alter table social_activity_contact_file add column ppt_pages int default 0 comment 'PPT页数';

/*
注释:	
	后台家长加了个备用手机号.
	后台习题加了个难度系数.
	现在后台用户和学校删除都为假删除,登录等正常使用.
*/ 
alter table dict_schoollist add flag tinyint not null default 1 comment '标示: 1-正常 0-禁用 -1-逻辑删除'

CREATE TABLE `biz_class_clock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(10) unsigned NOT NULL COMMENT '班级id',
  `several_week` tinyint(3) unsigned NOT NULL COMMENT '礼拜几 从0排到6 礼拜日是0',
  `clock_time` varchar(32) NOT NULL COMMENT '设置的闹钟的提醒时间 这里存的时间格式为 0922 代表9点22分',
  `clock_time_interval` tinyint(3) unsigned NOT NULL COMMENT '闹钟的间隔时间 单位是分钟',
  `create_at` int(10) unsigned NOT NULL COMMENT '该时钟的创建时间',
  `notice_number` tinyint(3) unsigned NOT NULL DEFAULT '5' COMMENT '设置提醒次数 暂设为5次,这个是针对所有用户都进行修改的',
  `clock_end_time` varchar(32) NOT NULL COMMENT '该闹钟的结束时间 时间格式为0922 代表9点22分',
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_id` (`class_id`,`several_week`,`clock_time`)
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8 COMMENT='时钟表'


CREATE TABLE `biz_clock_contact_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clock_id` int(10) unsigned NOT NULL COMMENT '时钟表id',
  `user_type` tinyint(3) unsigned NOT NULL COMMENT '用户类型 2教师,3学生,4家长',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `push_count` tinyint(3) unsigned NOT NULL COMMENT '推送次数 根据时钟表的次数来进行操作 如果统计的次数等于时钟表的次数则更改该条数据的推送状态',
  `create_at` int(10) unsigned NOT NULL COMMENT '创建时间',
  `update_at` int(10) unsigned NOT NULL COMMENT '修改时间',
  `push_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '推送状态 1为推送 0为不推送 ',
  `next_notice_time` varchar(32) NOT NULL COMMENT '下次通知时间 格式为 0922 9点22分',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8 COMMENT='时钟用户关联表'

 
-- 2017.1.4 add approve time to activity
alter table social_activity add column approve_at int(10) not null default 0 comment '发布时间' after update_at;

alter table social_activity add is_disable tinyint UNSIGNED not null comment '1,是 2,否'
-- 2017.1.7 add index to tables
alter table auth_student add index(school_id);
alter table biz_class_student add index(student_id); 
alter table auth_student add index(parent_id);
alter table account_user_and_auth add index(user_id);

alter table auth_teacher add index(school_id); 
alter table auth_teacher_second add index(teacher_id);
alter table auth_teacher_second add index(grade_id);  
alter table biz_class add index(class_teacher_id);

alter table auth_teacher_second modify `course_id` int(11) UNSIGNED NOT NULL COMMENT '学科编号';
alter table auth_teacher_second modify `grade_id` int(11) UNSIGNED NOT NULL COMMENT '学科编号';

--2017.1.7 add push number to expertinformation
alter table social_expert_information add column push_at int(10) not null COMMENT '推送时间' after update_at;

/*1.11*/
alter table auth_teacher add source varchar(320) not null comment '来源 100010如新浪微博'; 
alter table auth_parent add source varchar(320) not null comment '来源 100010如新浪微博';
alter table auth_student add source varchar(320) not null comment '来源 100010如新浪微博';

/*1.12 */
alter table dict_schoollist add school_code varchar(32) not null comment '学校编码';
alter table biz_class add class_code varchar(32) not null comment '班级编码';
alter table biz_class add class_status tinyint UNSIGNED not null default 1 comment '1为校内班 2为个人班';
alter table biz_class modify flag tinyint UNSIGNED not null comment '0为停用 1为正常 2为移交中 ';
alter table auth_teacher add auth_status tinyint UNSIGNED not null comment '教师认证状态 0待认证 1已认证 2已拒绝 这个是教师的附属属性'; 
alter table auth_teacher add `apply_school_status` tinyint unsigned NOT NULL COMMENT '教师申请加入学校的状态 0为待审核 1为学校同意加入 2为拒绝';

 alter table auth_student add apply_school_status tinyint UNSIGNED not null comment '学生申请加入学校的状态 0为待审核 1为学校同意加入 2为学校拒绝';

 alter table dict_schoollist modify school_category tinyint(4) NOT NULL DEFAULT '0' COMMENT '0--幼儿园 1--小学 2--初中 3--高中 4--九年一贯制学校 5--十二年一贯制学校';
 alter table dict_schoollist add is_create_administartor tinyint UNSIGNED not null comment '是否开创建(开通)理员 1位已经开通 0为未开通';
 alter table auth_admin add telephone varchar(32) not null comment '学校管理员手机号';
 alter table auth_admin add parent_id int UNSIGNED not null comment '学校管理员的父ID';
 alter table auth_admin add school_flag tinyint UNSIGNED not null COMMENT '学校管理员的操作标示符 0为禁用 1为启用' ;
 alter table auth_admin add email varchar(32) not null comment '邮箱地址';

/*更改biz_clsss_teacher 班级教师关联表   这版开始biz_class表教师信息不用了
    学生登录家长必须是学生信息表里的家长信息
*/
alter table biz_class_teacher drop PRIMARY KEY;
alter table biz_class_teacher drop course_id;
alter table biz_class_teacher add PRIMARY key(class_id,teacher_id) ;
alter table biz_class_teacher modify class_id int UNSIGNED not null;
alter table biz_class_teacher modify teacher_id int UNSIGNED not null; 
alter table biz_class_teacher add course_id tinyint UNSIGNED not null;
alter table biz_class_teacher add create_at int UNSIGNED not null comment '创建时间';
alter table biz_class_teacher drop index teacher_id_UNIQUE;
alter table biz_class_teacher drop primary key ;
alter table biz_class_teacher add primary key(class_id,teacher_id,course_id);
alter table biz_class_teacher add is_handler tinyint UNSIGNED not null comment '是否为管理人班主任 1为是 0为否';
  
CREATE TABLE `auth_student_parent_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `student_id` int(10) unsigned NOT NULL COMMENT '学生id',
  `parent_tel` varchar(20) NOT NULL COMMENT '家长手机号',
  `parent_id` int(10) unsigned NOT NULL COMMENT '学生id',
  `create_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='家长学生关联表';


CREATE TABLE `school_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module_action` varchar(100) NOT NULL COMMENT 'module_type为1 这里是控制器 如是控制器此字段无用,module_type为2 这里是控制器按照/拼接的方法',
  `module_name` varchar(100) NOT NULL COMMENT '模块名 如学生管理',
  `module_type` tinyint(3) unsigned NOT NULL COMMENT '模块类型 1为控制器 2为方法',
  `parent_id` int(10) unsigned NOT NULL,
  `create_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='学校权限表';

/*行政编码*/
alter table dict_citydistrict add code int UNSIGNED not null comment '行政编码'; 

CREATE TABLE `school_admin_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `permissions_id` int NOT NULL COMMENT '权限表ID 这里只能拥有school_permissions表中的方法',
  `school_admin_id` int NOT NULL COMMENT 'auth_amdin表 角色为3的学校管理员ID', 
  `create_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COMMENT='学校管理员对应权限表';
 
alter table biz_bj_resource_zan modify resource_id int UNSIGNED not null;

 --2017.2.16 add is_delete to biz_class
alter table biz_class add column is_delete tinyint(4) not null default 0 COMMENT '是否删除';
ALTER TABLE dict_schoollist add timetable VARCHAR (10000) NOT NULL DEFAULT '<table><tbody><tr><td class="blank" width="60"></td><td class="title">星期一</td><td class="title">星期二</td><td class="title">星期三</td><td class="title">星期四</td><td class="title">星期五</td><td class="title">星期六</td><td class="title">星期日</td></tr><tr><td class="time">第一节</td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td></tr><tr><td class="time">第二节</td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td></tr><tr><td class="time"></td><td class="lunch" colspan="7">课间操</td></tr><tr><td class="time">第三节</td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td></tr><tr><td class="time">第四节</td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td></tr><tr><td class="time"></td><td class="lunch" colspan="7">午休</td></tr><tr><td class="time">第五节</td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td></tr><tr><td class="time">第六节</td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td></tr><tr><td class="time"></td><td class="lunch" colspan="7">课间活动</td></tr><tr><td class="time">第七节</td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td></tr><tr><td class="time">第八节</td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td></tr></tbody></table>';
CREATE TABLE `biz_class_school_timetable` (
  `class_id` int(11) NOT NULL COMMENT '班级ID',
  `day_id` tinyint(4) NOT NULL COMMENT '星期几 eg. 星期一--1 星期日--7',
  `lesson_id` tinyint(4) NOT NULL COMMENT '第几节课 eg. 第一节课--1 第八节课--8',
  `teacher_id` int(11) COMMENT '教师ID',
  `course_id` int(11) NOT NULL COMMENT '学科ID',
  PRIMARY KEY (`class_id`,`day_id`,`lesson_id`),
  UNIQUE KEY `teacher_id` (`teacher_id`,`day_id`,`lesson_id`),
  KEY `classKey` (`class_id`),
  KEY `teacherKey` (`teacher_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

create table biz_class_school_timetable_comments (
`id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
`class_id` int(11) NOT NULL COMMENT '班级ID',
`comments` varchar(100) DEFAULT NULL,
primary key (`id`),
KEY `classKey` (`class_id`),
unique key (`class_id`)
);
alter table biz_class_timetable add column content_teacher text NOT NULL ;
alter table biz_class_timetable add column comments_teacher varchar(100);
 

CREATE TABLE `biz_class_handsoff` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `send_teacherid` int(10) NOT NULL COMMENT '源老师id',
  `dest_teacherid` int(10) unsigned NOT NULL COMMENT '目标老师id',
  `class_id` int(11) NOT NULL COMMENT '班级id',
  `course_id` int(10) DEFAULT NULL COMMENT '学科id',
  `handsoff_status` int(255) DEFAULT '1' COMMENT '1为移交中,2已接收,3已拒绝',
  PRIMARY KEY (`id`),
  KEY `class_query` (`dest_teacherid`,`class_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4;	/*这个要换成alter语句*/

alter table auth_teacher_second add UNIQUE(teacher_id,course_id,grade_id);	 
alter table dict_schoollist add create_at int UNSIGNED not null;
--2017.3.1 教学设计活动作品文件增加文件类型
alter table social_activity_works_file add  file_category tinyint(4) default 0  COMMENT '教学设计活动文件子类型 1--教学资源 2--教学设计 3--教学反思 4--教学实录' after type ;

--刷新BIZ_CLASS_TEACHER 表
insert into biz_class_teacher select id class_id , class_teacher_id ,0 course_id ,unix_timestamp(now()) create_at,1 is_handler from biz_class where class_status =2
ON duplicate key update biz_class_teacher.create_at=biz_class_teacher.create_at+1;

ALTER TABLE `auth_teacher`
add COLUMN `is_login`  int(20) NULL DEFAULT 1 COMMENT '1为未登陆 2已登陆' AFTER `apply_school_status`;

 