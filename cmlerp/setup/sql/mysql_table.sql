SET NAMES utf8;
GO
SET SQL_MODE='';
GO
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
GO
DROP TABLE IF EXISTS `{$prefix}attachrule`;
GO
CREATE TABLE `{$prefix}attachrule` (
  `attachruleid` int(10) NOT NULL auto_increment,
  `userorgrouporrole` tinyint(1) unsigned NOT NULL,
  `userorgrouporroleid` int(10) unsigned NOT NULL,
  `addordel` tinyint(1) NOT NULL default '1',
  `ruleid` int(10) unsigned NOT NULL,
  `baserule` char(5) default '00000',
  `otherruleid` int(10) unsigned default NULL,
  `configvalue` varchar(255) default NULL,
  `importer` int(10) unsigned NOT NULL,
  `createtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`attachruleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
GO
DROP TABLE IF EXISTS `{$prefix}config`;
GO
CREATE TABLE `{$prefix}config` (
  `userid` int(10) NOT NULL default '0',
  `otherruleid` int(10) unsigned NOT NULL,
  `configvalue` varchar(255) NOT NULL,
  `importer` int(10) NOT NULL,
  `createtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`userid`,`otherruleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
GO
DROP TABLE IF EXISTS `{$prefix}feedback`;
GO
CREATE TABLE `{$prefix}feedback` (
  `feedbackid` int(10) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `content` text,
  `state` tinyint(1) NOT NULL,
  `accepter` int(10) NULL,
  `fulfill` tinyint(3) NULL,
  `score` tinyint(3) NULL,
  `userid` int(10) unsigned NOT NULL,
  `senddate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`feedbackid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
GO
DROP TABLE IF EXISTS `{$prefix}group`;
GO
CREATE TABLE `{$prefix}group` (
  `groupid` int(10) NOT NULL auto_increment,
  `groupname` varchar(20) NOT NULL,
  `importer` int(10) unsigned NOT NULL,
  `createtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
GO
DROP TABLE IF EXISTS `{$prefix}groupmanager`;
GO
CREATE TABLE `{$prefix}groupmanager` (
  `groupid` int(10) NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  `importer` int(10) NOT NULL,
  `createtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`groupid`,`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
GO
DROP TABLE IF EXISTS `{$prefix}grouprole`;
GO
CREATE TABLE `{$prefix}grouprole` (
  `groupid` int(10) unsigned NOT NULL,
  `roleid` int(10) unsigned NOT NULL,
  `importer` int(10) unsigned NOT NULL,
  `createtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`groupid`,`roleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
GO
DROP TABLE IF EXISTS `{$prefix}log`;
GO
CREATE TABLE `{$prefix}log` (
  `logid` int(10) NOT NULL auto_increment,
  `logtypeid` int(10) unsigned NOT NULL,
  `logtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `srcuserid` int(10) unsigned NOT NULL,
  `content` text,
  PRIMARY KEY  (`logid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
GO
DROP TABLE IF EXISTS `{$prefix}login`;
GO
CREATE TABLE `{$prefix}login` (
  `loginid` int(10) NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL,
  `clientid` char(32) NOT NULL,
  `superid` int(10) unsigned default NULL,
  `rulestr` text,
  `attachrulestr` text,
  `updatestate` tinyint(1) default '0',
  `logintime` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`loginid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
GO
DROP TABLE IF EXISTS `{$prefix}logtype`;
GO
CREATE TABLE `{$prefix}logtype` (
  `logtypeid` int(10) NOT NULL auto_increment,
  `logtypename` varchar(50) NOT NULL,
  `importer` int(10) unsigned NOT NULL,
  `createtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`logtypeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
GO
DROP TABLE IF EXISTS `{$prefix}message`;
GO
CREATE TABLE `{$prefix}message` (
  `msgid` int(10) NOT NULL auto_increment,
  `msgtitle` varchar(50) NOT NULL,
  `msgtext` text NOT NULL,
  `sendtoids` text NULL,
  `userid` int(10) unsigned NOT NULL,
  `msgdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `hadreading` tinyint(1) NOT NULL default '0',
  `hadratify` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`msgid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
GO
DROP TABLE IF EXISTS `{$prefix}otherrule`;
GO
CREATE TABLE `{$prefix}otherrule` (
  `otherruleid` int(10) NOT NULL auto_increment,
  `ruleid` int(10) unsigned default 0,
  `issystemvar` tinyint(1) NOT NULL default '0',
  `isrule` tinyint(1) default '0',
  `maxlength` tinyint(3) default NULL,
  `configvarname` varchar(255) NOT NULL,
  `configname` varchar(255) NOT NULL,
  `configvalue` varchar(255) NOT NULL,
  `configdefault` varchar(255) NOT NULL,
  `configtype` varchar(50) NOT NULL,
  `importer` int(10) unsigned NOT NULL,
  `createtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`otherruleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
GO
DROP TABLE IF EXISTS `{$prefix}role`;
GO
CREATE TABLE `{$prefix}role` (
  `roleid` int(10) NOT NULL auto_increment,
  `rolename` varchar(20) NOT NULL,
  `importer` int(10) unsigned NOT NULL,
  `createtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`roleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
GO
DROP TABLE IF EXISTS `{$prefix}rolerule`;
GO
CREATE TABLE `{$prefix}rolerule` (
  `roleid` int(10) NOT NULL,
  `ruleid` int(10) NOT NULL,
  `issuperuser` tinyint(1) unsigned NOT NULL,
  `canbrowse` tinyint(1) unsigned NOT NULL,
  `canappend` tinyint(1) unsigned NOT NULL,
  `canmodify` tinyint(1) unsigned NOT NULL,
  `candelete` tinyint(1) unsigned NOT NULL,
  `importer` int(10) unsigned NOT NULL,
  `createtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`roleid`,`ruleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
GO
DROP TABLE IF EXISTS `{$prefix}rule`;
GO
CREATE TABLE `{$prefix}rule` (
  `ruleid` int(10) NOT NULL auto_increment,
  `rulename` varchar(50) NOT NULL,
  `parentruleid` int(10) unsigned NOT NULL default '0',
  `ruleimg` varchar(255) default NULL,
  `rulebigimg` varchar(255) default NULL,
  `ruleurl` varchar(255) default NULL,
  `ruleorder` tinyint(1) unsigned NOT NULL,
  `layer` tinyint(1) unsigned NOT NULL,
  `importer` int(10) unsigned NOT NULL,
  `createtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ruleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
GO
DROP TABLE IF EXISTS `{$prefix}user`;
GO
CREATE TABLE `{$prefix}user` (
  `userid` int(10) NOT NULL auto_increment,
  `username` varchar(50) NOT NULL,
  `userpass` char(32) NOT NULL,
  `loginnum` int(10) unsigned NOT NULL default '0',
  `importer` int(10) unsigned NOT NULL,
  `createtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
GO
DROP TABLE IF EXISTS `{$prefix}usergroup`;
GO
CREATE TABLE `{$prefix}usergroup` (
  `userid` int(10) unsigned NOT NULL,
  `groupid` int(10) unsigned NOT NULL,
  `importer` int(10) unsigned NOT NULL,
  `createtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`userid`,`groupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
GO
SET SQL_MODE=@OLD_SQL_MODE;
GO