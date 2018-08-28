--2017.03.08 14:18:00
--΢�Ż������
CREATE TABLE `activity_wx` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_name` varchar(20) NOT NULL COMMENT 'ѧ������',
  `school_name` varchar(255) NOT NULL COMMENT 'ѧУ����',
  `class_name` varchar(45) NOT NULL COMMENT '�꼶�Ͱ༶',
  `class_teacher` varchar(20) NOT NULL COMMENT 'ָ����ʦ',
  `telephone` varchar(20) NOT NULL COMMENT '��ϵ��ʽ',
  `content` text NOT NULL COMMENT '����',
  `creat_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '����ʱ��',
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
--����ȫ��ʡ������dict_citydistrict,���彨�������ݼ�dict_citydistrict.sql
--������:
--                 loaddata                       loadSchoolData
--  ѧУ��ϢCSV --------------> school_temp�� -------------------> dict_schoollist��
--                                               |
--                        dict_citydistrict�� ---
--
--ѧУ��Ϣ��ʱ��,ͨ������loadSchoolData�洢���̿ɽ��ñ�����ѧУ��Ϣ����ѧУ��Ϣ��ʽ��dict_schoollist
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
--ѧУ��Ϣ��ʽ��
create table dict_schoollist (id int auto_increment,
school_name varchar(100),
school_address varchar(200),
school_category tinyint(4) not null default 0 COMMENT '0--�׶�԰ 1--Сѧ 2--���� 3--����',
joined_level_id tinyint(4) unsigned NOT NULL DEFAULT 0,
joined_area_id  mediumint(8) unsigned NOT NULL DEFAULT 0,
primary key (id)
);

--ѧУ����ID���ƶ�Ӧ��ϵ��
CREATE TABLE `dict_schoolcategory` (
  `id` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into dict_schoolcategory values(0,'�׶�԰');
insert into dict_schoolcategory values(1,'Сѧ');
insert into dict_schoolcategory values(2,'����');
insert into dict_schoolcategory values(3,'����');

--���غ���ѧУ��Ϣ��CSV��ѧУ��Ϣ��ʱ��
load data infile 'd:\\school.csv' 
into table `school_temp` 
fields terminated by ',' optionally enclosed by '"' escaped by '"' 
lines terminated by '\r\n'; 

--loadSchoolData�洢���� 
--����:����school_temp��ʱѧУ�����Ϣ��dict_schoollistѧУ�ֵ��
--���:schoolcate  --ѧУ����ID ,���ü�dict_schoolcategory��
--     province_id --ʡID,��dict_citydistrict�е�ʡ��ֱϽ�е�ID
--     city_id     --��ID,��dict_citydistrict�г��е�ID
--��ע:����ֱϽ�е����,��Ҫ��ʡID����ID��Ҫ������ͬ
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
--����������Դ��Ŀ��
 create table dict_channel (
id int unsigned not null auto_increment,
name varchar(60),
primary key(id)
);

alter table biz_bj_resources add column channel_id int(11) after school_term_id;
alter table biz_bj_resources change column channel_id channel_id int(11) default 0;
insert into dict_channel values(1,'������Ŀ');
alter table biz_bj_resources add column isDisplay tinyint(4) default 1;

--2016.8.3 12:00:00 434619
create table auth_teacher_second
(
id bigint(20) not null auto_increment,
teacher_id bigint(20) not null,
`course_id` int(11) NOT NULL DEFAULT '1' COMMENT 'ѧ�Ʊ��',
`grade_id` int(11) NOT NULL DEFAULT '1' COMMENT '�꼶���',
  PRIMARY KEY (`id`)
);

--2016.8.6 14:54:00 434619
--�����Ϣ�б�TABLE
create table notice_message
(
id int unsigned not null  auto_increment,
role tinyint(1) unsigned not null ,   --0:��ʦ 1:ѧ�� 2:�ҳ� ע��ô�����
user_id int unsigned not null ,
msg_title varchar(200),
msg_content varchar(200),
create_at int(11),
read_status tinyint(1),
primary key (id),
KEY `index_query` (`role`,`user_id`) 
);
--��Ӱ༶�ƽ���Ϣ��
create table biz_class_handsoff
(
id int unsigned not null  auto_increment,
dest_teacherid  int unsigned not null,
class_id int(11) not null,
primary key (id),
KEY `class_query` (`dest_teacherid`,`class_id`)
);
--2016.8.19 15:49:00 ���Ӷ���������Ϲ�����
drop table if exists biz_lesson_planning_contact;
create table biz_lesson_planning_contact	(
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `biz_lesson_planning_id` int(10) unsigned NOT NULL COMMENT '������������ID',
  `type` varchar(20) NOT NULL COMMENT '����',
   `create_html` tinyint(3) DEFAULT '0' COMMENT '�Ƿ����ɶ�Ӧhtml�ļ� 0δ���� 1������ ',
  `file_path` varchar(320) NOT NULL COMMENT '�ļ�·��',
  `vid` varchar(50),
  PRIMARY KEY (`id`),
  KEY `biz_lesson_planning_id` (`biz_lesson_planning_id`)
 )
--2016.8.23 22:12:00 ���Ӷ���������Ϲ�����
alter table biz_bj_resources modify status tinyint not null default 1; 

--��ʦ��Դ
alter table biz_resource modify status tinyint not null default 1; 	

--�������	
alter taele biz_bj_overview add status tinyint not null default 1;	

alter table auth_admin add real_name varchar(32) not null;

alter table dict_schoollist add obligation_person varchar(32) NOT NULL comment '����������';
alter table dict_schoollist add obligation_tel varchar(20) NOT NULL comment '�������ֻ���' ;
alter table dict_schoollist add obligation_email varchar(50) NOT NULL comment '����������' ;
alter table dict_schoollist add status tinyint NOT NULL default 1;

alter table biz_resource_collect add column user_type tinyint(4) after teacher_id;

alter table biz_resource_collect change column teacher_id user_id int(11);

alter table biz_resource_collect change column teacher_name user_name varchar(32);

alter table biz_class add course_id int not null comment 'ѧ��ID' after grade_id;

alter table auth_teacher add sex enum('��','Ů') NOT NULL DEFAULT '��'  ;

alter table auth_parent add sex enum('��','Ů') NOT NULL DEFAULT '��';  

alter table auth_student add sex enum('��','Ů') NOT NULL DEFAULT '��'  ;
alter table auth_student add `birth_date` int(10) unsigned NOT NULL;

alter table biz_homework add `homework_type` varchar(32) NOT NULL comment '������ҵ,�κ���ҵ';
alter table biz_homework add `homework_status` tinyint NOT NULL comment '������ҵ״̬ 0δ����,1�Ѳ���';
alter table biz_homework add read_ids varchar(500) NOT NULL comment '�ð��Ѷ���ѧ��id,���ŷָ�';

alter tabel biz_exercise_library_chapter add festival varchar(100) not null comment '��' after chapter;
alter tabel biz_exercise_library_chapter add title varchar(320) not null comment '����' after festival;

alter table biz_homework_score_details drop PRIMARY key;
alter table biz_homework_score_details add PRIMARY key(homework_id,student_id,question_org_id);

alter table biz_bj_resources add is_download tinyint not null ;

alter table biz_resource add file_path varchar(100) not NULL COMMENT '�ļ�·��' after status;
alter table biz_resource add vid varchar(100) not NULL COMMENT '��Ƶid����������)' after file_path ;
alter table biz_resource add playerwidth varchar(20) not NULL COMMENT '��Ƶ��С(�ֱ���)' after vid ;
alter table biz_resource add playerduration varchar(20) not NULL COMMENT '��Ƶ�г�' after playerwidth;

alter table biz_bj_resource_zan drop primary key ;
alter table biz_bj_resource_zan add primary key(resource_id,role,user_id);

drop table biz_homework_exercise;
CREATE TABLE `biz_homework_exercise` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `homework_id` int(11) NOT NULL COMMENT '��ҵID',
  `exercise_id` bigint(20) NOT NULL COMMENT 'ϰ��ID',
  `chapter_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COMMENT='��ҵ��ϰ�������';

CREATE TABLE `biz_bj_resource_contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `biz_bj_resource_id` int(10) unsigned NOT NULL COMMENT '������Դ����ID',
  `resource_path` varchar(320) NOT NULL COMMENT '�����ļ�·��',
  `vid` varchar(320) NOT NULL COMMENT '��ƵID',
  `playerwidth` varchar(20) DEFAULT NULL COMMENT '��Ƶ��С(�ֱ���)',
  `playerduration` varchar(20) DEFAULT NULL COMMENT '��Ƶʱ��',
  `flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0:δ����oss�ϴ� 1������oss�ϴ�',
  PRIMARY KEY (`id`),
  KEY `biz_bj_resource_id` (`biz_bj_resource_id`)
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8;

CREATE TABLE `biz_resource_contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `biz_resource_id` int(10) unsigned NOT NULL COMMENT '��ʦ��Դ����ID',
  `resource_path` varchar(320) NOT NULL COMMENT '�����ļ�·��',
  `vid` varchar(320) NOT NULL COMMENT '��ƵID',
  `playerwidth` varchar(20) DEFAULT NULL COMMENT '��Ƶ��С(�ֱ���)',
  `playerduration` varchar(20) DEFAULT NULL COMMENT '��Ƶʱ��',
  `flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0:δ����oss�ϴ� 1������oss�ϴ�',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='��Դ���ޱ�' ;

--2016.8.25 15:09:00 add converted flag,flag_pdf and ppt_pages to biz_lesson_planning_contact table
--0 unconvert 1 converted
alter table biz_lesson_planning_contact add column flag tinyint(4) default 0 ;
alter table biz_lesson_planning_contact add column flag_pdf tinyint(4) default 0 ;
alter table biz_lesson_planning_contact add column ppt_pages int(11) default 0 ;

--2016.8.29 15:13:00 add flag field to biz_resource table
alter table biz_resource add `flag` tinyint NOT NULL COMMENT '1��pdfδת��,2��pdf��ת��'
--2016.8.31 14:16:00 add 9��һ���� 12��һ���� to schoolcategory
insert into dict_schoolcategory values (4,'����һ����');
insert into dict_schoolcategory values (5,'ʮ����һ����');

--2016.8.31 15:35:00 modify loadschooldata procedure
--loadSchoolData�洢���� 
--����:����school_temp��ʱѧУ�����Ϣ��dict_schoollistѧУ�ֵ��
--���:schoolcate  --ѧУ����ID ,���ü�dict_schoolcategory��
--     province_id --ʡID,��dict_citydistrict�е�ʡ��ֱϽ�е�ID
--     city_id     --��ID,��dict_citydistrict�г��е�ID
--��ע:����ֱϽ�е����,��Ҫ��ʡID����ID��Ҫ������ͬ
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
alter table auth_teacher add column role tinyint(4) default 0 COMMENT '0--һ���ʦ 1--�м����� 2--��������'

--2016.9.8 modify comment of biz_class_student
alter table biz_class_student modify column status tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:�ȴ����2���Ѿ�ͨ�����3:�ܾ����'

--2016.9.12 14:03:00 add VIP TABLES
-- ----------------------------
-- Table structure for account_auth
-- ----------------------------
DROP TABLE IF EXISTS `account_auth`;
CREATE TABLE `account_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Ȩ�ޱ�����id',
  `auth_name` varchar(50) DEFAULT NULL COMMENT 'Ȩ������',
  `create_at` int(11) DEFAULT NULL COMMENT '����ʱ��ʱ���',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '--1Ϊ���� --0Ϊɾ��',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='Ȩ�ޱ������ɺ�̨д��';

-- ----------------------------
-- Records of account_auth
-- ----------------------------
INSERT INTO `account_auth` VALUES ('1', '�ο�ģʽ', '1473214399', '1');
INSERT INTO `account_auth` VALUES ('2', '��ͨȨ��', '1473214425', '1');
INSERT INTO `account_auth` VALUES ('3', '����VIP', '1473214449', '1');

-- ----------------------------
-- Table structure for account_auth_notes
-- ----------------------------
DROP TABLE IF EXISTS `account_auth_notes`;
CREATE TABLE `account_auth_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Ȩ��ʱ�������id',
  `timetype` tinyint(1) DEFAULT NULL COMMENT 'ʱ������1Ϊ����ʱ�� 2Ϊʹ��ʱ��',
  `viptimelong` int(11) DEFAULT NULL COMMENT 'vipʱ����λ��',
  `editexpiretime` int(11) DEFAULT NULL COMMENT '�޸ĺ���ʱ��',
  `addtime` int(11) DEFAULT NULL COMMENT '�������ʱ��',
  `root_id` int(11) DEFAULT NULL COMMENT '������id',
  `remark` varchar(255) DEFAULT NULL COMMENT '��ע����',
  `user_id` int(11) DEFAULT NULL COMMENT '�û�id',
  `role_id` int(11) DEFAULT NULL COMMENT '��ɫid',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=387 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of account_auth_notes
-- ----------------------------
INSERT INTO `account_auth_notes` VALUES ('357', '2', '2', '1472918400', '1473413367', '1', 'admin����: ��ͨ����VIPȨ��', '990', '1');
INSERT INTO `account_auth_notes` VALUES ('358', '2', '2', '1472918400', '1473413368', '1', 'admin����: ��ͨ����VIPȨ��', '26', '4');
INSERT INTO `account_auth_notes` VALUES ('359', '2', '2', '1472918400', '1473413368', '1', 'admin����: ��ͨ����VIPȨ��', '27', '4');
INSERT INTO `account_auth_notes` VALUES ('360', '2', '2', '1472918400', '1473413368', '1', 'admin����: ��ͨ����VIPȨ��', '217', '3');
INSERT INTO `account_auth_notes` VALUES ('361', '2', '2', '1472918400', '1473413368', '1', 'admin����: ��ͨ����VIPȨ��', '218', '3');
INSERT INTO `account_auth_notes` VALUES ('362', '2', '2', '1472918400', '1473413368', '1', 'admin����: ��ͨ����VIPȨ��', '987', '2');
INSERT INTO `account_auth_notes` VALUES ('363', '2', '2', '1472918400', '1473413368', '1', 'admin����: ��ͨ����VIPȨ��', '988', '2');
INSERT INTO `account_auth_notes` VALUES ('364', '2', '2', '1472918400', '1473413368', '1', 'admin����: ��ͨ����VIPȨ��', '1008', '2');
INSERT INTO `account_auth_notes` VALUES ('365', '2', '2', '1472918400', '1473413368', '1', 'admin����: ��ͨ����VIPȨ��', '1009', '2');
INSERT INTO `account_auth_notes` VALUES ('366', '2', '2', '1472918400', '1473413368', '1', 'admin����: ��ͨ����VIPȨ��', '1010', '2');
INSERT INTO `account_auth_notes` VALUES ('367', '2', '2', '1472918400', '1473413368', '1', 'admin����: ��ͨ����VIPȨ��', '1011', '2');
INSERT INTO `account_auth_notes` VALUES ('368', '2', '2', '1472918400', '1473413368', '1', 'admin����: ��ͨ����VIPȨ��', '1012', '2');
INSERT INTO `account_auth_notes` VALUES ('369', '2', '2', '1472918400', '1473413368', '1', 'admin����: ��ͨ����VIPȨ��', '1013', '2');
INSERT INTO `account_auth_notes` VALUES ('370', '2', '2', '1472918400', '1473413368', '1', 'admin����: ��ͨ����VIPȨ��', '1014', '2');
INSERT INTO `account_auth_notes` VALUES ('371', '2', '2', '1472918400', '1473413368', '1', 'admin����: ��ͨ����VIPȨ��', '1015', '2');
INSERT INTO `account_auth_notes` VALUES ('372', '2', '5', '1473177600', '1473413398', '1', 'admin����: ��ͨ��ͨȨ��Ȩ��', '990', '1');
INSERT INTO `account_auth_notes` VALUES ('373', '2', '5', '1473177600', '1473413398', '1', 'admin����: ��ͨ��ͨȨ��Ȩ��', '26', '4');
INSERT INTO `account_auth_notes` VALUES ('374', '2', '5', '1473177600', '1473413398', '1', 'admin����: ��ͨ��ͨȨ��Ȩ��', '27', '4');
INSERT INTO `account_auth_notes` VALUES ('375', '2', '5', '1473177600', '1473413398', '1', 'admin����: ��ͨ��ͨȨ��Ȩ��', '217', '3');
INSERT INTO `account_auth_notes` VALUES ('376', '2', '5', '1473177600', '1473413398', '1', 'admin����: ��ͨ��ͨȨ��Ȩ��', '218', '3');
INSERT INTO `account_auth_notes` VALUES ('377', '2', '5', '1473177600', '1473413398', '1', 'admin����: ��ͨ��ͨȨ��Ȩ��', '987', '2');
INSERT INTO `account_auth_notes` VALUES ('378', '2', '5', '1473177600', '1473413398', '1', 'admin����: ��ͨ��ͨȨ��Ȩ��', '988', '2');
INSERT INTO `account_auth_notes` VALUES ('379', '2', '5', '1473177600', '1473413398', '1', 'admin����: ��ͨ��ͨȨ��Ȩ��', '1008', '2');
INSERT INTO `account_auth_notes` VALUES ('380', '2', '5', '1473177600', '1473413398', '1', 'admin����: ��ͨ��ͨȨ��Ȩ��', '1009', '2');
INSERT INTO `account_auth_notes` VALUES ('381', '2', '5', '1473177600', '1473413398', '1', 'admin����: ��ͨ��ͨȨ��Ȩ��', '1010', '2');
INSERT INTO `account_auth_notes` VALUES ('382', '2', '5', '1473177600', '1473413398', '1', 'admin����: ��ͨ��ͨȨ��Ȩ��', '1011', '2');
INSERT INTO `account_auth_notes` VALUES ('383', '2', '5', '1473177600', '1473413398', '1', 'admin����: ��ͨ��ͨȨ��Ȩ��', '1012', '2');
INSERT INTO `account_auth_notes` VALUES ('384', '2', '5', '1473177600', '1473413398', '1', 'admin����: ��ͨ��ͨȨ��Ȩ��', '1013', '2');
INSERT INTO `account_auth_notes` VALUES ('385', '2', '5', '1473177600', '1473413398', '1', 'admin����: ��ͨ��ͨȨ��Ȩ��', '1014', '2');
INSERT INTO `account_auth_notes` VALUES ('386', '2', '5', '1473177600', '1473413398', '1', 'admin����: ��ͨ��ͨȨ��Ȩ��', '1015', '2');

-- ----------------------------
-- Table structure for account_auth_to_node
-- ----------------------------
DROP TABLE IF EXISTS `account_auth_to_node`;
CREATE TABLE `account_auth_to_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Ȩ�޶�Ӧ��������id',
  `auth_id` int(11) DEFAULT NULL COMMENT 'Ȩ��id',
  `users_type_id` int(11) DEFAULT NULL COMMENT '�û�����id',
  `node_id` varchar(255) DEFAULT NULL COMMENT '���ܽڵ�id �Զ��ŷָ��洢',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '--1Ϊ���� --0Ϊɾ��',
  `create_at` int(11) DEFAULT NULL COMMENT '������ݵ�ʱ���',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='Ȩ�޹��ܶ�Ӧ�� ��̨����д��';

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
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '�ڵ�����id',
  `node_name` varchar(50) DEFAULT NULL COMMENT '���ܽڵ�����',
  `create_at` int(11) DEFAULT NULL COMMENT '�ڵ����ʱ���',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '--1Ϊ���� --0Ϊɾ��',
  `fid` int(11) DEFAULT '0' COMMENT '�ڵ㸸id һ��Ĭ��Ϊ0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='���ܱ� ���ݺ�̨д��';

-- ----------------------------
-- Records of account_node_list
-- ----------------------------
INSERT INTO `account_node_list` VALUES ('1', '����Ȧ', '1473216009', '1', '0');
INSERT INTO `account_node_list` VALUES ('2', '��ѧ+', '1473216034', '1', '0');
INSERT INTO `account_node_list` VALUES ('3', '�༶��', '1473216131', '1', '0');
INSERT INTO `account_node_list` VALUES ('4', '�������', '1473216161', '1', '1');
INSERT INTO `account_node_list` VALUES ('5', 'ר����Ѷ', '1473216214', '1', '1');
INSERT INTO `account_node_list` VALUES ('6', '����', '1473216243', '1', '1');
INSERT INTO `account_node_list` VALUES ('7', '��ʦ���', '1473216293', '1', '1');
INSERT INTO `account_node_list` VALUES ('8', '������Դ', '1473216329', '1', '2');
INSERT INTO `account_node_list` VALUES ('9', '����ϵͳ', '1473216424', '1', '2');
INSERT INTO `account_node_list` VALUES ('10', '���ӿα�', '1473216431', '1', '2');
INSERT INTO `account_node_list` VALUES ('11', '��ʦ��Դ����', '1473216467', '1', '2');
INSERT INTO `account_node_list` VALUES ('12', '���ֿ���', '1473216616', '1', '3');
INSERT INTO `account_node_list` VALUES ('13', 'С�ڰ�', '1473216626', '1', '3');
INSERT INTO `account_node_list` VALUES ('14', 'ѧϰ�켣', '1473216632', '1', '3');
INSERT INTO `account_node_list` VALUES ('15', '��ҵϵͳ', '1473216640', '1', '3');
INSERT INTO `account_node_list` VALUES ('16', '�༶��Ϣ����', '1473216646', '1', '3');
INSERT INTO `account_node_list` VALUES ('17', 'С�ڰ�', '1473216653', '1', '3');
INSERT INTO `account_node_list` VALUES ('18', '��ҵϵͳ', '1473216688', '1', '3');
INSERT INTO `account_node_list` VALUES ('19', '�ҵİ༶', '1473216699', '1', '3');
INSERT INTO `account_node_list` VALUES ('20', 'ѧϰ�켣', '1473216706', '1', '3');
INSERT INTO `account_node_list` VALUES ('21', '��ҵϵͳ', '1473216712', '1', '3');
INSERT INTO `account_node_list` VALUES ('22', '�ҳ���ѧ', '1473216739', '1', '3');

-- ----------------------------
-- Table structure for account_user_and_auth
-- ----------------------------
DROP TABLE IF EXISTS `account_user_and_auth`;
CREATE TABLE `account_user_and_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '�û���Ȩ������id',
  `user_id` int(11) DEFAULT NULL COMMENT '�û�id',
  `role_id` int(11) DEFAULT NULL COMMENT '�û���ɫid',
  `auth_id` int(11) DEFAULT NULL COMMENT '�û�Ȩ��id',
  `auth_start_time` int(11) DEFAULT NULL COMMENT 'Ȩ�޿�ʼʱ��',
  `auth_end_time` int(11) DEFAULT NULL COMMENT '�û�����ʱ��',
  `status` tinyint(1) DEFAULT '1' COMMENT '--1Ϊ���� --0Ϊɾ��',
  `timetype` tinyint(1) DEFAULT NULL COMMENT 'ʱ������1Ϊ����ʱ�� 2Ϊʹ��ʱ��',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=281 DEFAULT CHARSET=utf8 COMMENT='�û���Ȩ��ʱ���';

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
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '�û���������id',
  `type_name` varchar(50) DEFAULT NULL COMMENT '�û���������',
  `create_at` int(11) DEFAULT NULL COMMENT '����û����͵�ʱ���',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '--1Ϊ���� --0Ϊɾ��',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='�û����ͱ� ����д��';

-- ----------------------------
-- Records of account_users_type
-- ----------------------------
INSERT INTO `account_users_type` VALUES ('1', 'ѧУ', '1473215326', '1');
INSERT INTO `account_users_type` VALUES ('2', '��ʦ', '1473215347', '1');
INSERT INTO `account_users_type` VALUES ('3', 'ѧ��', '1473215361', '1');
INSERT INTO `account_users_type` VALUES ('4', '�ҳ�', '1473215376', '1');
INSERT INTO `account_users_type` VALUES ('5', '�ο�', '1473215376', '1');

 
alter table dict_schoollist add user_auth int not null COMMENT '�û�Ȩ��';
alter table dict_schoollist add auth_start_time int not null COMMENT 'Ȩ�޿�ʼʱ��';
alter table dict_schoollist add auth_end_time int not null COMMENT 'Ȩ�޽���ʱ��';
alter table dict_schoollist add timetype tinyint not null COMMENT 'ʱ������1Ϊ����ʱ�� 2Ϊʹ��ʱ��' ; 

--2016.9.27 11:38:00 ����APP�û�����ͳ�Ʊ�  434619
drop table if exists app_statistics;
create table app_statistics(
  id bigint NOT NULL AUTO_INCREMENT COMMENT '����id',
  user_type tinyint(4) NOT NULL,
  user_id   int NOT NULL,  
  machine_type varchar(100) NOT NULL COMMENT '��������',
  ip_address  varchar(16) NOT NULL COMMENT 'IP��ַ',
  create_at int(11) DEFAULT NULL COMMENT '������ݵ�ʱ���',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--2016.9.28 15:37:00 �����ҵ��زı�
CREATE TABLE `biz_material` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID', 
  `type` varchar(20) NOT NULL COMMENT '��Դ��� Text Image Video PPT Flash', 
   teacher_id int UNSIGNED not null, 
  `create_at` int(11) NOT NULL COMMENT '������',     
  `file_path` varchar(100) DEFAULT NULL COMMENT '�ļ�·��',
  `vid` varchar(100) DEFAULT NULL COMMENT '��Ƶid���������ӣ�', 
  `flag` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1��pdfδת��,2���°���ת��',
  PRIMARY KEY (`id`),index(teacher_id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='�ҵ��زı�'

--2016.9.30 14:49:00 �������ϱ��������ϰ汾�ֶ�
  ALTER TABLE biz_lesson_planning add column ver tinyint(4) 
  ALTER TABLE biz_lesson_planning_contact add column content text after file_path
  SET SQL_SAFE_UPDATES = 0
  UPDATE biz_lesson_planning set ver=1 where type <> ''
  UPDATE biz_lesson_planning set ver=2 where type = '' or type is null

--2016.9.30 15:53:00 �����������ϴ�����·��
alter table social_activity_register add file_path varchar(320) not null comment '�������ϴ��ļ�·��'
alter table social_activity add is_upload tinyint not null comment '�Ƿ�֧���ϴ�,0��֧��,1֧���ϴ�'
alter table biz_material add material_name varchar(320) not null comment '�ز�����'  
--2016.10.3 16:51:00 ������Դ���ӱ�������ԭʼ��Ƶ�ֶ�
alter table biz_bj_resources add column vid_fullpath varchar(100) COMMENT '��������ԭʼ��Ƶ��ַ'

--2016.10.3 16:51:00 ������Դ��ʦ��Դ�������ӱ�������ԭʼ��Ƶ��ͼ�ֶ�
alter table biz_bj_resources add vid_image_path varchar(320) not null
alter table biz_resource add vid_image_path varchar(320) not null
alter table biz_material add vid_image_path varchar(320) not null
alter table biz_material add vid_fullpath varchar(320) not null

--2016.10.18 16:14:00 ��ʦ��Դ�����زĿ�������Ƶ�Ƿ�ת���ֶ�
alter table biz_resource_contact add is_transition tinyint not null comment '�Ƿ�ת��,0Ϊδת��,1Ϊ��ת�����Ҫת���'
alter table biz_material add is_transition tinyint not null comment '�Ƿ�ת��,0Ϊδת��,1Ϊ��ת�����Ҫת���' 

--2016.10.19 10:01:00 ������Դ��ʦ��Դ�����زĿ�����PPT�Ƿ�ת����־��ҳ��
alter table biz_bj_resource_contact add column ppt_html tinyint(4) default 0 comment '0δת��1��ת��'
alter table biz_resource_contact add column ppt_html tinyint(4) default 0 comment '0δת��1��ת��'
alter table biz_material add column ppt_html tinyint(4) default 0 comment '0δת��1��ת��'

alter table biz_bj_resource_contact add column ppt_pages int default 0 comment 'PPTҳ��'
alter table biz_resource_contact add column ppt_pages int default 0 comment 'PPTҳ��'
alter table biz_material add column ppt_pages int default 0 comment 'PPTҳ��'

--2016.10.25 14:43:00 �����û����ʹ켣��
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
alter table biz_class add class_type tinyint UNSIGNED not null comment '�༶���� 1ΪУ�ڰ�,2ΪУ���' after name
CREATE TABLE `biz_class_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `biz_class_id` int UNSIGNED not null COMMENT '�༶id',    
  `teacher_id` int(11) NOT NULL,
  `flag` tinyint(4) NOT NULL DEFAULT '1' COMMENT '��ʾ 1->����',
  `create_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `key_id`(`biz_class_id`,`teacher_id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='У�ڰ༶������'
 
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

alter table biz_class add note varchar(320) not null comment '��ע�ʼ�'

CREATE TABLE `biz_isread_blackboard` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '����id',
  `role_id` tinyint(3) unsigned NOT NULL COMMENT '��ɫid,2��ʦ,3ѧ��,4�ҳ�',
  `user_id` int(11) DEFAULT NULL COMMENT '�û�id',
  `b_id` int(11) DEFAULT NULL COMMENT 'С�ڰ�id',
  `read_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4

alter table biz_isread_blackboard add `read_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP 

alter table biz_blackboard add is_transition tinyint UNSIGNED not null comment '�Ƿ�ת��,1����ת��,0��Ҫת��'  
alter table biz_blackboard modify message text not null

--2016.11.17 add message table
CREATE TABLE `role_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` varchar(30) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL COMMENT '�ٶ����͵ı���',
  `truncated_title` varchar(100) DEFAULT NULL COMMENT 'content�ض����ɵı���',
  `message_content` text COMMENT '��Ϣ������',
  `receive_num` int(11) DEFAULT NULL COMMENT '�������',
  `receive_type` int(12) DEFAULT NULL COMMENT '1.app���� 2.�������� 3.app���ͺ͸�������',
  `send_time` varchar(64) DEFAULT NULL COMMENT '����ʱ��',
  `status` int(12) DEFAULT '1' COMMENT '1.δ����2.���ͳɹ�3.����ʧ�ܡ�4.�ѳ��� 5.����ʧ��',
  `message_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1.��̨�ֹ���������Ϣ 2.ϵͳ�Զ�������Ϣ',
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='��Ϣ��ɫ���ձ�'

CREATE TABLE `receive_message_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(255) DEFAULT NULL COMMENT '��Ӧrole_message�е�id',
  `role_id` int(255) DEFAULT NULL COMMENT 'ѡ��Ľ�ɫid',
  `user_id` int(255) DEFAULT NULL COMMENT '�û���id',
  `addtime` varchar(32) DEFAULT NULL COMMENT '����ʱ��',
  `is_read` int(11) DEFAULT '1' COMMENT '1��δ�� 2���Ѷ�',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='�����˶�Ӧ��'

alter table auth_teacher add column machine_type tinyint(4) not null default 0 COMMENT '0--��Ч���͡�1--IOS 2--android'
alter table auth_student add column machine_type tinyint(4) not null default 0 COMMENT '0--��Ч���͡�1--IOS 2--android'
alter table auth_parent add column machine_type tinyint(4) not null default 0 COMMENT '0--��Ч���͡�1--IOS 2--android'

alter table auth_teacher add login_address varchar(320) not null;
alter table auth_student add login_address varchar(320) not null;
alter table auth_parent add login_address varchar(320) not null;
--2016.11.28 add message table
alter table role_message add column truncated_title varchar(100) COMMENT 'content�ض����ɵı���' after title 
--2016.12.03 ������Դ ר����Ѷ �����������ͱ�־λ
alter table biz_bj_resources add push_status tinyint UNSIGNED not null comment '0��������,1������Ҫ����,2�����Ѿ�����'
alter table social_expert_information add push_status tinyint UNSIGNED default 1 not null comment '1δ����,2�����Ѿ�����'  
alter table social_activity add push_status tinyint UNSIGNED default 1 not null comment '1δ����,2�����Ѿ�����'  

alter table user_access add column `access_date` int(11) NOT NULL;
alter table user_access add key `access_date` (`access_date`);


--2016.12.07 add activity tables
alter table biz_exercise_library add video_file_path varchar(300) NOT NULL COMMENT '��Ƶ·��'
alter table social_activity_class add parent_id tinyint UNSIGNED not null comment '����id'

alter table social_activity add apply_people_number int UNSIGNED not null comment '����ı�����������'
alter table social_activity add works_show_status tinyint UNSIGNED not null comment '�û��Ʒչʾ״̬,����Ǹ����������ͨ�����˶�����������չʾ 0��չʾ,1չʾ'


alter table social_activity_register add invitation_code varchar(60) not null comment '������'

 
ALTER TABLE `social_activity`
ADD COLUMN `code_num`  int(12)  DEFAULT NULL COMMENT '���������' AFTER `works_show_status`;

ALTER TABLE `social_activity`
ADD COLUMN `remark`  VARCHAR(255)  DEFAULT NULL COMMENT '��ע' AFTER `code_num`;

ALTER TABLE `social_activity`
ADD COLUMN `activitystart`  VARCHAR(25)  DEFAULT NULL COMMENT '���ʼʱ��' AFTER `remark`;

ALTER TABLE `social_activity`
ADD COLUMN `activityend`  VARCHAR(25)  DEFAULT NULL COMMENT '�����ʱ��' AFTER `activitystart`;

ALTER TABLE `social_activity`
ADD COLUMN `applystart`  VARCHAR(25)  DEFAULT NULL COMMENT '������ʼʱ��' AFTER `activityend`;

ALTER TABLE `social_activity`
ADD COLUMN `applyend`  VARCHAR(25)  DEFAULT NULL COMMENT '��������ʱ��' AFTER `applystart`;

ALTER TABLE `social_activity`
ADD COLUMN `is_pack`  VARCHAR(255)  DEFAULT '1' COMMENT '�����url' AFTER `applyend`;

ALTER TABLE `social_activity`
ADD COLUMN `is_grade_select`  int(12)  DEFAULT '2' COMMENT '�Ƿ�ѡ��1ѡ��2δѡ��' AFTER `is_disable`;

ALTER TABLE `social_activity`
ADD COLUMN `is_course_select`  int(12)  DEFAULT '2' COMMENT '�Ƿ�ѡ��1ѡ��2δѡ��' AFTER `is_grade_select`;

ALTER TABLE `social_activity`
ADD COLUMN `is_generate`  int(12)  DEFAULT '1' COMMENT '1Ϊ������ 2����' AFTER `is_course_select`;

ALTER TABLE `social_activity_contact_file`
ADD COLUMN `type`  VARCHAR(25)  DEFAULT NULL COMMENT '�ļ�����' AFTER `create_at`;

ALTER TABLE `social_activity_contact_file`
ADD COLUMN `vid_fullpath`  VARCHAR(320)  DEFAULT NULL COMMENT '��������·��' AFTER `create_at`;

DROP TABLE IF EXISTS `activity_download_url`;
CREATE TABLE `activity_download_url` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) DEFAULT NULL COMMENT '�id',
  `result_url` varchar(255) DEFAULT NULL COMMENT '��Դ·��',
  `status` int(11) DEFAULT '1' COMMENT '1Ϊδִ��2��ִ��',
  `oss_url` varchar(255) DEFAULT NULL COMMENT 'oss���·��',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;


CREATE TABLE `social_activity_invitation_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) NOT NULL COMMENT '�id',
  `invitation_code` varchar(60) NOT NULL COMMENT '������',
  `status` tinyint(3) unsigned NOT NULL COMMENT '1Ϊδʹ��,2Ϊ��ʹ��',
  `create_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invitation_code` (`invitation_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='��������'



CREATE TABLE `social_activity_contact_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int not null comment '�id',
	`activity_file_name` varchar(300) not null comment '����������ļ�������',
	`activity_file_path` varchar(300) not null comment '����������ļ���·��',
	`create_at` int UNSIGNED not null  ,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='������������ļ���'
   

 CREATE TABLE `social_activity_works` (
  `id` int(11) NOT NULL AUTO_INCREMENT, 
  `activity_register_id` int UNSIGNED comment '�ע���id',
  `course` int UNSIGNED,
  `grade` int UNSIGNED,
  `works_name` varchar(320),
  `works_description` text,
  `author_remarks` varchar(320),  
  create_at int UNSIGNED not null,
  update_at int UNSIGNED not null,
  browse_number int UNSIGNED not null,
  status tinyint UNSIGNED not null comment '���״̬ 0Ϊ����� 1Ϊͨ�� 2Ϊ�ܾ�',
  point int UNSIGNED not null comment '����Ʒ�÷�',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='������Ʒ��' 
alter table social_activity_works add zan_count tinyint UNSIGNED not null  ;
alter table social_activity_works add favor_count tinyint UNSIGNED not null ;
alter table social_activity_works add column `voted_title` varchar(255) default NULL COMMENT '��������';
alter table social_activity_works add column `error_data` varchar(255) default NULL COMMENT '�ܾ�ͨ������Ϣ';

 CREATE TABLE `social_activity_works_file` (
 `id` int(11) NOT NULL AUTO_INCREMENT, 
`activity_works_id` int UNSIGNED not null comment '������Ʒ��Id', 
`works_file_name` varchar(320) not null COMMENT '��Ʒ�ļ�������',
`works_file_path` varchar(320) not null COMMENT '��Ʒ�ļ���·��',
  create_at int UNSIGNED not null, 
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='������Ʒ�ļ���'


CREATE TABLE `social_activity_works_favor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_works_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` int(11) NOT NULL COMMENT '2:��ʦ 3��ѧ�� 4���ҳ�',
  `favor_time` int(11) NOT NULL,
  PRIMARY KEY (`activity_works_id`,`user_id`,`user_type`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='������Ʒ-�ղص������'


CREATE TABLE `social_activity_works_zan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_works_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` int(11) NOT NULL DEFAULT '1' COMMENT '2:��ʦ 3��ѧ�� 4���ҳ�',
  `zan_time` int(11) NOT NULL,
  PRIMARY KEY (`activity_works_id`,`user_id`,`user_type`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='������Ʒ-�޵������'

alter table social_activity_class add background_image varchar(320) not null

insert into social_activity_class (id,class,sort_order) value(4,'���ֻ�',4);
insert into social_activity_class (id,class,sort_order) value(5,'��Ʒ����',5);
insert into social_activity_class (id,class,parent_id) value(6,'��ѧ���',5);
insert into social_activity_class (id,class,parent_id) value(7,'��ѧ����',5);
insert into social_activity_class (id,class,parent_id) value(8,'�μ�/�̰�',5);
insert into social_activity_class (id,class,parent_id) value(9,'��ѧ����',5);
insert into social_activity_class (id,class,parent_id) value(10,'��ѧ΢��',5);

update social_activity_class set background_image='http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/activity/category/jiaoxuesheji@2x.png' where id=6
update social_activity_class set background_image='http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/activity/category/jiaoxueanli@2x.png' where id=7
update social_activity_class set background_image='http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/activity/category/kejianjiaoan@2x.png' where id=8
update social_activity_class set background_image='http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/activity/category/jiaoxuelunwen@2x.png' where id=9
update social_activity_class set background_image='http://jbyoss.oss-cn-beijing.aliyuncs.com/Resources/activity/category/jiaoxueweike@2x.png' where id=10

alter table social_activity_works_file add type VARCHAR(25) not null comment '�ļ�����'
alter table social_activity_contact_file add type  VARCHAR(25) not null comment '�ļ�����'

alter table social_activity_contact_file add vid_fullpath varchar(320) not null
alter table social_activity_contact_file add vid varchar(320) not null

alter table social_activity_works_file add vid_fullpath varchar(320) not null
alter table social_activity_works_file add vid varchar(320) not null

alter table social_activity_register add lesson varchar(320) not null comment '��������'
alter table social_activity_register add province int UNSIGNED not null comment '��ʦ��д������ʡ��'
alter table social_activity_register add city int UNSIGNED not null comment '��ʦ��д����������'
alter table social_activity_register add district int UNSIGNED not null comment '��ʦ��д����������'
alter table social_activity_register add sex enum('��','Ů') NOT NULL DEFAULT '��' 
alter table social_activity_register add age tinyint NOT NULL comment '����'
alter table social_activity_register add positions tinyint NOT NULL comment 'ְ��'
alter table social_activity_register add education tinyint NOT NULL comment 'ѧ��'
alter table social_activity_register add email varchar(320) NOT NULL comment '����'
alter table social_activity_register add school_id int UNSIGNED not null comment 'ѧУid'
alter table social_activity_register add school_address varchar(320) not null 
alter table social_activity_register add post_code varchar(32) not null comment '�ʱ�'
alter table social_activity_register add tel varchar(32) not null comment '�绰'
alter table social_activity_register add telephone varchar(32) not null comment '�ֻ���'
alter table social_activity_register add local_course tinyint UNSIGNED not null comment '�ط��γ� 1��,0��'
alter table social_activity_register add school_course tinyint UNSIGNED not null comment 'ѧУ�γ� 1��,0��'
alter table social_activity_register add course tinyint UNSIGNED not null comment '��ʦ��ѧѧ��' after lesson

CREATE TABLE `social_activity_course_grade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
	 activity_id int UNSIGNED not null,
	 grade int UNSIGNED not null,  
	 course int UNSIGNED not null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='��꼶ѧ�ƹ�����'
alter table social_activity_course_grade add index(grade);
alter table social_activity_course_grade add index(course);


alter table social_activity_works_file add column `flag` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1��pdfδת��,2���°���ת��';
alter table social_activity_works_file add column vid_image_path varchar(320) not null COMMENT '��ͼ·��';
alter table social_activity_works_file add is_transition tinyint not null comment '�Ƿ�ת��,0Ϊδת��,1Ϊ��ת�����Ҫת���';
alter table social_activity_works_file add column ppt_html tinyint(4) default 0 comment '0δת��1��ת��';
alter table social_activity_works_file add column ppt_pages int default 0 comment 'PPTҳ��';

alter table social_activity_contact_file add column `flag` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1��pdfδת��,2���°���ת��';
alter table social_activity_contact_file add column vid_image_path varchar(320) not null COMMENT '��ͼ·��';
alter table social_activity_contact_file add is_transition tinyint not null comment '�Ƿ�ת��,0Ϊδת��,1Ϊ��ת�����Ҫת���';
alter table social_activity_contact_file add column ppt_html tinyint(4) default 0 comment '0δת��1��ת��';
alter table social_activity_contact_file add column ppt_pages int default 0 comment 'PPTҳ��';

/*
ע��:	
	��̨�ҳ����˸������ֻ���.
	��̨ϰ����˸��Ѷ�ϵ��.
	���ں�̨�û���ѧУɾ����Ϊ��ɾ��,��¼������ʹ��.
*/ 
alter table dict_schoollist add flag tinyint not null default 1 comment '��ʾ: 1-���� 0-���� -1-�߼�ɾ��'

CREATE TABLE `biz_class_clock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(10) unsigned NOT NULL COMMENT '�༶id',
  `several_week` tinyint(3) unsigned NOT NULL COMMENT '��ݼ� ��0�ŵ�6 �������0',
  `clock_time` varchar(32) NOT NULL COMMENT '���õ����ӵ�����ʱ�� ������ʱ���ʽΪ 0922 ����9��22��',
  `clock_time_interval` tinyint(3) unsigned NOT NULL COMMENT '���ӵļ��ʱ�� ��λ�Ƿ���',
  `create_at` int(10) unsigned NOT NULL COMMENT '��ʱ�ӵĴ���ʱ��',
  `notice_number` tinyint(3) unsigned NOT NULL DEFAULT '5' COMMENT '�������Ѵ��� ����Ϊ5��,�������������û��������޸ĵ�',
  `clock_end_time` varchar(32) NOT NULL COMMENT '�����ӵĽ���ʱ�� ʱ���ʽΪ0922 ����9��22��',
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_id` (`class_id`,`several_week`,`clock_time`)
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8 COMMENT='ʱ�ӱ�'


CREATE TABLE `biz_clock_contact_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clock_id` int(10) unsigned NOT NULL COMMENT 'ʱ�ӱ�id',
  `user_type` tinyint(3) unsigned NOT NULL COMMENT '�û����� 2��ʦ,3ѧ��,4�ҳ�',
  `user_id` int(10) unsigned NOT NULL COMMENT '�û�id',
  `push_count` tinyint(3) unsigned NOT NULL COMMENT '���ʹ��� ����ʱ�ӱ�Ĵ��������в��� ���ͳ�ƵĴ�������ʱ�ӱ�Ĵ�������ĸ������ݵ�����״̬',
  `create_at` int(10) unsigned NOT NULL COMMENT '����ʱ��',
  `update_at` int(10) unsigned NOT NULL COMMENT '�޸�ʱ��',
  `push_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '����״̬ 1Ϊ���� 0Ϊ������ ',
  `next_notice_time` varchar(32) NOT NULL COMMENT '�´�֪ͨʱ�� ��ʽΪ 0922 9��22��',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8 COMMENT='ʱ���û�������'

 
-- 2017.1.4 add approve time to activity
alter table social_activity add column approve_at int(10) not null default 0 comment '����ʱ��' after update_at;

alter table social_activity add is_disable tinyint UNSIGNED not null comment '1,�� 2,��'
-- 2017.1.7 add index to tables
alter table auth_student add index(school_id);
alter table biz_class_student add index(student_id); 
alter table auth_student add index(parent_id);
alter table account_user_and_auth add index(user_id);

alter table auth_teacher add index(school_id); 
alter table auth_teacher_second add index(teacher_id);
alter table auth_teacher_second add index(grade_id);  
alter table biz_class add index(class_teacher_id);

alter table auth_teacher_second modify `course_id` int(11) UNSIGNED NOT NULL COMMENT 'ѧ�Ʊ��';
alter table auth_teacher_second modify `grade_id` int(11) UNSIGNED NOT NULL COMMENT 'ѧ�Ʊ��';

--2017.1.7 add push number to expertinformation
alter table social_expert_information add column push_at int(10) not null COMMENT '����ʱ��' after update_at;

/*1.11*/
alter table auth_teacher add source varchar(320) not null comment '��Դ 100010������΢��'; 
alter table auth_parent add source varchar(320) not null comment '��Դ 100010������΢��';
alter table auth_student add source varchar(320) not null comment '��Դ 100010������΢��';

/*1.12 */
alter table dict_schoollist add school_code varchar(32) not null comment 'ѧУ����';
alter table biz_class add class_code varchar(32) not null comment '�༶����';
alter table biz_class add class_status tinyint UNSIGNED not null default 1 comment '1ΪУ�ڰ� 2Ϊ���˰�';
alter table biz_class modify flag tinyint UNSIGNED not null comment '0Ϊͣ�� 1Ϊ���� 2Ϊ�ƽ��� ';
alter table auth_teacher add auth_status tinyint UNSIGNED not null comment '��ʦ��֤״̬ 0����֤ 1����֤ 2�Ѿܾ� ����ǽ�ʦ�ĸ�������'; 
alter table auth_teacher add `apply_school_status` tinyint unsigned NOT NULL COMMENT '��ʦ�������ѧУ��״̬ 0Ϊ����� 1ΪѧУͬ����� 2Ϊ�ܾ�';

 alter table auth_student add apply_school_status tinyint UNSIGNED not null comment 'ѧ���������ѧУ��״̬ 0Ϊ����� 1ΪѧУͬ����� 2ΪѧУ�ܾ�';

 alter table dict_schoollist modify school_category tinyint(4) NOT NULL DEFAULT '0' COMMENT '0--�׶�԰ 1--Сѧ 2--���� 3--���� 4--����һ����ѧУ 5--ʮ����һ����ѧУ';
 alter table dict_schoollist add is_create_administartor tinyint UNSIGNED not null comment '�Ƿ񿪴���(��ͨ)��Ա 1λ�Ѿ���ͨ 0Ϊδ��ͨ';
 alter table auth_admin add telephone varchar(32) not null comment 'ѧУ����Ա�ֻ���';
 alter table auth_admin add parent_id int UNSIGNED not null comment 'ѧУ����Ա�ĸ�ID';
 alter table auth_admin add school_flag tinyint UNSIGNED not null COMMENT 'ѧУ����Ա�Ĳ�����ʾ�� 0Ϊ���� 1Ϊ����' ;
 alter table auth_admin add email varchar(32) not null comment '�����ַ';

/*����biz_clsss_teacher �༶��ʦ������   ��濪ʼbiz_class���ʦ��Ϣ������
    ѧ����¼�ҳ�������ѧ����Ϣ����ļҳ���Ϣ
*/
alter table biz_class_teacher drop PRIMARY KEY;
alter table biz_class_teacher drop course_id;
alter table biz_class_teacher add PRIMARY key(class_id,teacher_id) ;
alter table biz_class_teacher modify class_id int UNSIGNED not null;
alter table biz_class_teacher modify teacher_id int UNSIGNED not null; 
alter table biz_class_teacher add course_id tinyint UNSIGNED not null;
alter table biz_class_teacher add create_at int UNSIGNED not null comment '����ʱ��';
alter table biz_class_teacher drop index teacher_id_UNIQUE;
alter table biz_class_teacher drop primary key ;
alter table biz_class_teacher add primary key(class_id,teacher_id,course_id);
alter table biz_class_teacher add is_handler tinyint UNSIGNED not null comment '�Ƿ�Ϊ�����˰����� 1Ϊ�� 0Ϊ��';
  
CREATE TABLE `auth_student_parent_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `student_id` int(10) unsigned NOT NULL COMMENT 'ѧ��id',
  `parent_tel` varchar(20) NOT NULL COMMENT '�ҳ��ֻ���',
  `parent_id` int(10) unsigned NOT NULL COMMENT 'ѧ��id',
  `create_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='�ҳ�ѧ��������';


CREATE TABLE `school_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module_action` varchar(100) NOT NULL COMMENT 'module_typeΪ1 �����ǿ����� ���ǿ��������ֶ�����,module_typeΪ2 �����ǿ���������/ƴ�ӵķ���',
  `module_name` varchar(100) NOT NULL COMMENT 'ģ���� ��ѧ������',
  `module_type` tinyint(3) unsigned NOT NULL COMMENT 'ģ������ 1Ϊ������ 2Ϊ����',
  `parent_id` int(10) unsigned NOT NULL,
  `create_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='ѧУȨ�ޱ�';

/*��������*/
alter table dict_citydistrict add code int UNSIGNED not null comment '��������'; 

CREATE TABLE `school_admin_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `permissions_id` int NOT NULL COMMENT 'Ȩ�ޱ�ID ����ֻ��ӵ��school_permissions���еķ���',
  `school_admin_id` int NOT NULL COMMENT 'auth_amdin�� ��ɫΪ3��ѧУ����ԱID', 
  `create_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COMMENT='ѧУ����Ա��ӦȨ�ޱ�';
 
alter table biz_bj_resource_zan modify resource_id int UNSIGNED not null;

 --2017.2.16 add is_delete to biz_class
alter table biz_class add column is_delete tinyint(4) not null default 0 COMMENT '�Ƿ�ɾ��';
ALTER TABLE dict_schoollist add timetable VARCHAR (10000) NOT NULL DEFAULT '<table><tbody><tr><td class="blank" width="60"></td><td class="title">����һ</td><td class="title">���ڶ�</td><td class="title">������</td><td class="title">������</td><td class="title">������</td><td class="title">������</td><td class="title">������</td></tr><tr><td class="time">��һ��</td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td></tr><tr><td class="time">�ڶ���</td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td></tr><tr><td class="time"></td><td class="lunch" colspan="7">�μ��</td></tr><tr><td class="time">������</td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td></tr><tr><td class="time">���Ľ�</td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td></tr><tr><td class="time"></td><td class="lunch" colspan="7">����</td></tr><tr><td class="time">�����</td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td></tr><tr><td class="time">������</td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td></tr><tr><td class="time"></td><td class="lunch" colspan="7">�μ�</td></tr><tr><td class="time">���߽�</td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td></tr><tr><td class="time">�ڰ˽�</td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td><td class="drop droppable"></td></tr></tbody></table>';
CREATE TABLE `biz_class_school_timetable` (
  `class_id` int(11) NOT NULL COMMENT '�༶ID',
  `day_id` tinyint(4) NOT NULL COMMENT '���ڼ� eg. ����һ--1 ������--7',
  `lesson_id` tinyint(4) NOT NULL COMMENT '�ڼ��ڿ� eg. ��һ�ڿ�--1 �ڰ˽ڿ�--8',
  `teacher_id` int(11) COMMENT '��ʦID',
  `course_id` int(11) NOT NULL COMMENT 'ѧ��ID',
  PRIMARY KEY (`class_id`,`day_id`,`lesson_id`),
  UNIQUE KEY `teacher_id` (`teacher_id`,`day_id`,`lesson_id`),
  KEY `classKey` (`class_id`),
  KEY `teacherKey` (`teacher_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

create table biz_class_school_timetable_comments (
`id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
`class_id` int(11) NOT NULL COMMENT '�༶ID',
`comments` varchar(100) DEFAULT NULL,
primary key (`id`),
KEY `classKey` (`class_id`),
unique key (`class_id`)
);
alter table biz_class_timetable add column content_teacher text NOT NULL ;
alter table biz_class_timetable add column comments_teacher varchar(100);
 

CREATE TABLE `biz_class_handsoff` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `send_teacherid` int(10) NOT NULL COMMENT 'Դ��ʦid',
  `dest_teacherid` int(10) unsigned NOT NULL COMMENT 'Ŀ����ʦid',
  `class_id` int(11) NOT NULL COMMENT '�༶id',
  `course_id` int(10) DEFAULT NULL COMMENT 'ѧ��id',
  `handsoff_status` int(255) DEFAULT '1' COMMENT '1Ϊ�ƽ���,2�ѽ���,3�Ѿܾ�',
  PRIMARY KEY (`id`),
  KEY `class_query` (`dest_teacherid`,`class_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4;	/*���Ҫ����alter���*/

alter table auth_teacher_second add UNIQUE(teacher_id,course_id,grade_id);	 
alter table dict_schoollist add create_at int UNSIGNED not null;
--2017.3.1 ��ѧ��ƻ��Ʒ�ļ������ļ�����
alter table social_activity_works_file add  file_category tinyint(4) default 0  COMMENT '��ѧ��ƻ�ļ������� 1--��ѧ��Դ 2--��ѧ��� 3--��ѧ��˼ 4--��ѧʵ¼' after type ;

--ˢ��BIZ_CLASS_TEACHER ��
insert into biz_class_teacher select id class_id , class_teacher_id ,0 course_id ,unix_timestamp(now()) create_at,1 is_handler from biz_class where class_status =2
ON duplicate key update biz_class_teacher.create_at=biz_class_teacher.create_at+1;

ALTER TABLE `auth_teacher`
add COLUMN `is_login`  int(20) NULL DEFAULT 1 COMMENT '1Ϊδ��½ 2�ѵ�½' AFTER `apply_school_status`;

 