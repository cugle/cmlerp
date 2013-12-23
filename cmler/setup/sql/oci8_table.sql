DECLARE w_count INT;w_name varchar2(20);
BEGIN
  w_name := '{$prefix}attachrule';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate 'DROP SEQUENCE insert_attachrule';
    EXECUTE immediate 'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE SEQUENCE insert_attachrule START WITH 1 MAXVALUE 999999999999
GO
CREATE TABLE {$prefix}attachrule (
  attacheruleid number(10) primary key,
  userorgrouporrole number(1) NOT NULL,
  userorgrouporroleid number(10) NOT NULL,
  addordel number(1) default 1,
  ruleid number(10) NOT NULL,
  baserule char(5) default '01000',
  otherruleid number(10) NULL,
  importer number(10) NOT NULL,
  createtime date default sysdate
)
GO
CREATE OR REPLACE TRIGGER attachrule_trg
BEFORE INSERT ON {$prefix}attachrule
FOR EACH ROW
BEGIN
SELECT insert_attachrule.nextval into:new.attacheruleid FROM dual;
END;
GO
DECLARE w_count INT;w_name varchar2(20);
BEGIN
  w_name := '{$prefix}feedback';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate 'DROP SEQUENCE insert_feedback';
    EXECUTE immediate  'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE SEQUENCE insert_feedback START WITH 1 MAXVALUE 999999999999
GO
CREATE TABLE {$prefix}feedback (
  feedbackid number(10) primary key,
  title varchar(255) NOT NULL,
  content varchar(4000),
  userid number(10) NOT NULL,
  senddate date default sysdate
)
GO
CREATE OR REPLACE TRIGGER feedback_trg
BEFORE INSERT ON {$prefix}feedback
FOR EACH ROW
BEGIN
SELECT insert_feedback.nextval into:new.feedbackid FROM dual;
END;
GO
DECLARE w_count INT;w_name varchar2(20);
BEGIN
  w_name := '{$prefix}group';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate 'DROP SEQUENCE insert_group';
    EXECUTE immediate 'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE SEQUENCE insert_group START WITH 1 MAXVALUE 999999999999
GO
CREATE TABLE {$prefix}group (
  groupid number(10) primary key,
  groupname varchar(20) NOT NULL,
  importer number(10)  NOT NULL,
  createtime date default sysdate
)
GO
CREATE OR REPLACE TRIGGER group_trg
BEFORE INSERT ON {$prefix}group
FOR EACH ROW
BEGIN
SELECT insert_group.nextval into:new.groupid FROM dual;
END;
GO
DECLARE w_count INT;w_name varchar2(20);
BEGIN
  w_name := '{$prefix}groupmanager';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate 'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE TABLE {$prefix}groupmanager (
  groupid number(10) NOT NULL,
  userid number(10) NOT NULL,
  importer number(10) NOT NULL,
  createtime date default sysdate
)
GO
DECLARE w_count INT;w_name varchar2(20);
BEGIN
  w_name := '{$prefix}grouprole';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate 'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE TABLE {$prefix}grouprole (
  groupid number(10) NOT NULL,
  roleid number(10) NOT NULL,
  importer number(10) NOT NULL,
  createtime date default sysdate
)
GO
DECLARE w_count INT;w_name varchar2(20); 
BEGIN
  w_name := '{$prefix}log';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate 'DROP SEQUENCE insert_log';
    EXECUTE immediate 'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE SEQUENCE insert_log START WITH 1 MAXVALUE 999999999999
GO
CREATE TABLE {$prefix}log (
  logid number(10) primary key,
  logtypeid number(10)  NOT NULL,
  logtime date default sysdate,
  srcuserid number(10)  NOT NULL,
  content varchar(4000)
)
GO
CREATE OR REPLACE TRIGGER log_trg
BEFORE INSERT ON {$prefix}log
FOR EACH ROW
BEGIN
SELECT insert_log.nextval into:new.logid FROM dual;
END;
GO
DECLARE w_count INT;w_name varchar2(20);
BEGIN
  w_name := '{$prefix}login';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate 'DROP SEQUENCE insert_login';
    EXECUTE immediate  'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE SEQUENCE insert_login START WITH 1 MAXVALUE 999999999999
GO
CREATE TABLE {$prefix}login (
  loginid number(10) primary key,
  userid number(10)  NOT NULL,
  loginip varchar(20) NOT NULL,
  superid number(10) NULL,
  rulestr varchar(4000) null,
  attachrulestr varchar(4000) null,
  updatestate number(1) default 0,
  logintime number(10)  NOT NULL
)
GO
CREATE OR REPLACE TRIGGER login_trg
BEFORE INSERT ON {$prefix}login
FOR EACH ROW
BEGIN
SELECT insert_login.nextval into:new.loginid FROM dual;
END;
GO
DECLARE w_count INT;w_name varchar2(20); 
BEGIN
  w_name := '{$prefix}logtype';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate 'DROP SEQUENCE insert_logtype';
    EXECUTE immediate 'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE SEQUENCE insert_logtype START WITH 1 MAXVALUE 999999999999
GO
CREATE TABLE {$prefix}logtype (
  logtypeid number(10) primary key,
  logtypename varchar(50) NOT NULL,
  importer number(10) NOT NULL,
  createtime date default sysdate
)
GO
CREATE OR REPLACE TRIGGER logtype_trg
BEFORE INSERT ON {$prefix}logtype
FOR EACH ROW
BEGIN
SELECT insert_logtype.nextval into:new.logtypeid FROM dual;
END;
GO
DECLARE w_count INT;w_name varchar2(20); 
BEGIN
  w_name := '{$prefix}message';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate 'DROP SEQUENCE insert_message';
    EXECUTE immediate  'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE SEQUENCE insert_message START WITH 1 MAXVALUE 999999999999
GO
CREATE TABLE {$prefix}message (
  msgid number(10) primary key,
  msgtitle varchar(50) NOT NULL,
  msgtext varchar(4000) NOT NULL,
  msgbound varchar(50) NULL,
  sendtoid number(10) NULL,
  userid number(10) NOT NULL,
  msgdate date default sysdate,
  hadreading number(1) default 0,
  hadratify number(1) default 0
)
GO
CREATE OR REPLACE TRIGGER message_trg
BEFORE INSERT ON {$prefix}message
FOR EACH ROW
BEGIN
SELECT insert_message.nextval into:new.msgid FROM dual;
END;
GO
DECLARE w_count INT;w_name varchar2(20); 
BEGIN
  w_name := '{$prefix}otherrule';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate 'DROP SEQUENCE insert_otherrule';
    EXECUTE immediate  'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE SEQUENCE insert_otherrule START WITH 1 MAXVALUE 999999999999
GO
CREATE TABLE {$prefix}otherrule (
  otherruleid number(10) primary key,
  ruleid number(10)  NOT NULL,
  otherrulename varchar(50) NOT NULL,
  otherrulevarname varchar(50) NOT NULL,
  vardefault number(1) default 1,
  importer number(10) NOT NULL,
  createtime date default sysdate
)
GO
CREATE OR REPLACE TRIGGER otherrule_trg
BEFORE INSERT ON {$prefix}otherrule
FOR EACH ROW
BEGIN
SELECT insert_otherrule.nextval into:new.otherruleid FROM dual;
END;
GO
DECLARE w_count INT;w_name varchar2(20); 
BEGIN
  w_name := '{$prefix}role';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate 'DROP SEQUENCE insert_role';
    EXECUTE immediate  'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE SEQUENCE insert_role START WITH 1 MAXVALUE 999999999999
GO
CREATE TABLE {$prefix}role (
  roleid number(10) primary key,
  rolename varchar(20) NOT NULL,
  importer number(10) NOT NULL,
  createtime date default sysdate
)
GO
CREATE OR REPLACE TRIGGER role_trg
BEFORE INSERT ON {$prefix}role
FOR EACH ROW
BEGIN
SELECT insert_role.nextval into:new.roleid FROM dual;
END;
GO
DECLARE w_count INT;w_name varchar2(20); 
BEGIN
  w_name := '{$prefix}rolerule';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate  'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE TABLE {$prefix}rolerule (
  roleid number(10) NOT NULL,
  ruleid number(10) NOT NULL,
  issuperuser number(1) NOT NULL,
  canbrowse number(1) NOT NULL,
  canappend number(1) NOT NULL,
  canmodify number(1) NOT NULL,
  candelete number(1) NOT NULL,
  importer number(10) NOT NULL,
  createtime date default sysdate
)
GO
DECLARE w_count INT;w_name varchar2(20); 
BEGIN
  w_name := '{$prefix}rule';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate 'DROP SEQUENCE insert_rule';
    EXECUTE immediate  'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE SEQUENCE insert_rule START WITH 1 MAXVALUE 999999999999
GO
CREATE TABLE {$prefix}rule (
  ruleid number(10) primary key,
  rulename varchar(50) NOT NULL,
  parentruleid number(10) default 0,
  ruleimg varchar(255) NULL,
  ruleurl varchar(255) NULL,
  ruleorder number(1) NOT NULL,
  layer number(1) NOT NULL,
  importer number(10) NOT NULL,
  createtime date default sysdate
)
GO
CREATE OR REPLACE TRIGGER rule_trg
BEFORE INSERT ON {$prefix}rule
FOR EACH ROW
BEGIN
SELECT insert_rule.nextval into:new.ruleid FROM dual;
END;
GO
DECLARE w_count INT;w_name varchar2(20); 
BEGIN
  w_name := '{$prefix}ruleconfig';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate 'DROP SEQUENCE insert_ruleconfig';
    EXECUTE immediate  'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE SEQUENCE insert_ruleconfig START WITH 1 MAXVALUE 999999999999
GO
CREATE TABLE {$prefix}ruleconfig (
  ruleconfigid number(10) primary key,
  ruleid number(10) NULL,
  issystemvar number(1) default 0,
  maxlength number(3) NULL,
  configvarname varchar(255) NOT NULL,
  configname varchar(255) NOT NULL,
  configvalue varchar(255) NOT NULL,
  configdefault varchar(50) NOT NULL,
  configtype varchar(50) NOT NULL,
  importer number(10) NOT NULL,
  createtime date default sysdate
)
GO
CREATE OR REPLACE TRIGGER ruleconfig_trg
BEFORE INSERT ON {$prefix}ruleconfig
FOR EACH ROW
BEGIN
SELECT insert_ruleconfig.nextval into:new.ruleconfigid FROM dual;
END;
GO
DECLARE w_count INT;w_name varchar2(20); 
BEGIN
  w_name := '{$prefix}user';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate 'DROP SEQUENCE insert_user';
    EXECUTE immediate  'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE SEQUENCE insert_user START WITH 1 MAXVALUE 999999999999
GO
CREATE TABLE {$prefix}user (
  userid number(10) primary key,
  username varchar(50) NOT NULL,
  userpass char(32) NOT NULL,
  loginnum number(10) DEFAULT 0 NOT NULL,
  importer number(10)  NOT NULL,
  createtime date default sysdate
)
GO
CREATE OR REPLACE TRIGGER user_trg
BEFORE INSERT ON {$prefix}user
FOR EACH ROW
BEGIN
SELECT insert_user.nextval into:new.userid FROM dual;
END;
GO
DECLARE w_count INT;w_name varchar2(20); 
BEGIN
  w_name := '{$prefix}userconfig';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate  'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE TABLE {$prefix}userconfig (
  userid number(10) NOT NULL,
  ruleconfigid number(10) NOT NULL,
  configvalue varchar(50) NOT NULL,
  importer number(10) NOT NULL,
  createtime date default sysdate
)
GO
DECLARE w_count INT;w_name varchar2(20); 
BEGIN
  w_name := '{$prefix}usergroup';
  SELECT COUNT(*) INTO w_count FROM user_tables WHERE table_name = UPPER(w_name) ;
  IF (w_count>0) THEN
    EXECUTE immediate  'DROP TABLE '||w_name; 
  END IF;
END;
GO
CREATE TABLE {$prefix}usergroup (
  userid number(10) NOT NULL,
  groupid number(10) NOT NULL,
  importer number(10) NOT NULL,
  createtime date default sysdate
)