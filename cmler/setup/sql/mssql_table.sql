if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[{$prefix}attachrule]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[{$prefix}attachrule]
GO
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[{$prefix}config]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[{$prefix}config]
GO
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[{$prefix}feedback]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[{$prefix}feedback]
GO
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[{$prefix}group]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[{$prefix}group]
GO
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[{$prefix}groupmanager]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[{$prefix}groupmanager]
GO
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[{$prefix}grouprole]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[{$prefix}grouprole]
GO
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[{$prefix}log]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[{$prefix}log]
GO
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[{$prefix}login]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[{$prefix}login]
GO
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[{$prefix}logtype]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[{$prefix}logtype]
GO
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[{$prefix}message]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[{$prefix}message]
GO
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[{$prefix}otherrule]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[{$prefix}otherrule]
GO
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[{$prefix}role]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[{$prefix}role]
GO
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[{$prefix}rolerule]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[{$prefix}rolerule]
GO
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[{$prefix}rule]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[{$prefix}rule]
GO
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[{$prefix}user]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[{$prefix}user]
GO
if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[{$prefix}usergroup]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[{$prefix}usergroup]
GO
CREATE TABLE [dbo].[{$prefix}attachrule] (
	[attachruleid] [int] IDENTITY (1, 1) NOT NULL ,
	[userorgrouporrole] [tinyint] NOT NULL ,
	[userorgrouporroleid] [int] NOT NULL ,
	[addordel] [tinyint] DEFAULT 1 NOT NULL ,
	[ruleid] [int] DEFAULT 0 NULL ,
	[baserule] [char] (5) COLLATE Latin1_General_CI_AS DEFAULT '00000' NULL ,
	[otherruleid] [int] DEFAULT 0 NULL ,
	[configvalue] [varchar] (255) COLLATE Latin1_General_CI_AS NULL ,
	[importer] [int] NOT NULL ,
	[createtime] [datetime] DEFAULT GETDATE() NOT NULL ,
	PRIMARY KEY  ([attachruleid])
)
GO
CREATE TABLE [dbo].[{$prefix}config] (
	[userid] [int] NOT NULL ,
	[otherruleid] [int] NOT NULL ,
	[configvalue] [varchar] (50) COLLATE Latin1_General_CI_AS NOT NULL ,
	[importer] [int] NOT NULL ,
	[createtiem] [datetime] DEFAULT GETDATE() NOT NULL ,
	PRIMARY KEY  ([userid],[otherruleid])
)
GO
CREATE TABLE [dbo].[{$prefix}feedback] (
	[feedbackid] [int] IDENTITY (1, 1) NOT NULL ,
	[title] [varchar] (255) COLLATE Latin1_General_CI_AS NOT NULL ,
	[content] [text] COLLATE Latin1_General_CI_AS NULL ,
	[state] [tinyint] NOT NULL ,
	[accepter] [int] NULL ,
	[fulfill] [tinyint] NULL ,
	[score] [tinyint] NULL ,
	[userid] [int] NOT NULL ,
	[senddate] [datetime] DEFAULT GETDATE() NOT NULL ,
	PRIMARY KEY  ([feedbackid])
)
GO
CREATE TABLE [dbo].[{$prefix}group] (
	[groupid] [int] IDENTITY (1, 1) NOT NULL ,
	[groupname] [varchar] (20) COLLATE Latin1_General_CI_AS NOT NULL ,
	[importer] [int] NOT NULL ,
	[createtime] [datetime] DEFAULT GETDATE() NOT NULL ,
	PRIMARY KEY  ([groupid])
)
GO
CREATE TABLE [dbo].[{$prefix}groupmanager] (
	[groupid] [int] NOT NULL ,
	[userid] [int] NOT NULL ,
	[importer] [int] NOT NULL ,
	[createtime] [datetime] DEFAULT GETDATE() NOT NULL ,
	PRIMARY KEY  ([groupid],[userid])
)
GO
CREATE TABLE [dbo].[{$prefix}grouprole] (
	[groupid] [int] NOT NULL ,
	[roleid] [int] NOT NULL ,
	[importer] [int] NOT NULL ,
	[createtime] [datetime] DEFAULT GETDATE() NOT NULL ,
	PRIMARY KEY  ([groupid],[roleid])
)
GO
CREATE TABLE [dbo].[{$prefix}log] (
	[logid] [int] IDENTITY (1, 1) NOT NULL ,
	[logtypeid] [int] NOT NULL ,
	[logtime] [datetime] DEFAULT GETDATE() NOT NULL ,
	[srcuserid] [int] NOT NULL ,
	[content] [text] COLLATE Latin1_General_CI_AS NULL ,
	PRIMARY KEY  ([logid])
)
GO
CREATE TABLE [dbo].[{$prefix}login] (
	[loginid] [int] IDENTITY (1, 1) NOT NULL ,
	[userid] [int] NOT NULL ,
	[clientid] [char] (32) COLLATE Latin1_General_CI_AS NOT NULL ,
	[superid] [int] NULL ,
	[rulestr] [text] COLLATE Latin1_General_CI_AS NULL ,
	[attachrulestr] [text] COLLATE Latin1_General_CI_AS NULL ,
	[updatestate] [tinyint] NOT NULL ,
	[logintime] [int] NOT NULL ,
	PRIMARY KEY  ([loginid])
)
GO
CREATE TABLE [dbo].[{$prefix}logtype] (
	[logtypeid] [int] IDENTITY (1, 1) NOT NULL ,
	[logtypename] [varchar] (50) COLLATE Latin1_General_CI_AS NOT NULL ,
	[importer] [int] NOT NULL ,
	[createtime] [datetime] DEFAULT GETDATE() NOT NULL ,
	PRIMARY KEY  ([logtypeid])
)
GO
CREATE TABLE [dbo].[{$prefix}message] (
	[msgid] [int] IDENTITY (1, 1) NOT NULL ,
	[msgtitle] [varchar] (50) COLLATE Latin1_General_CI_AS NOT NULL ,
	[msgtext] [text] COLLATE Latin1_General_CI_AS NOT NULL ,
	[sendtoids] [text] COLLATE Latin1_General_CI_AS NOT NULL ,
	[userid] [int] NOT NULL ,
	[msgdate] [datetime] DEFAULT GETDATE() NOT NULL ,
	[hadreading] [tinyint] DEFAULT 0 NOT NULL ,
	[hadratify] [tinyint] DEFAULT 0 NOT NULL ,
	PRIMARY KEY  ([msgid])
)
GO
CREATE TABLE [dbo].[{$prefix}otherrule] (
	[otherruleid] [int] IDENTITY (1, 1) NOT NULL ,
	[ruleid] [int] DEFAULT 0 NULL ,
	[issystemvar] [tinyint] DEFAULT 0 NOT NULL ,
	[isrule] [tinyint] DEFAULT 0 NULL ,
	[maxlength] [tinyint] NULL ,
	[configvarname] [varchar] (255) COLLATE Latin1_General_CI_AS NOT NULL ,
	[configname] [varchar] (255) COLLATE Latin1_General_CI_AS NOT NULL ,
	[configvalue] [varchar] (255) COLLATE Latin1_General_CI_AS NOT NULL ,
	[configdefault] [varchar] (255) COLLATE Latin1_General_CI_AS NOT NULL ,
	[configtype] [varchar] (50) COLLATE Latin1_General_CI_AS NOT NULL ,
	[importer] [int] NOT NULL ,
	[createtime] [datetime] DEFAULT GETDATE() NOT NULL ,
	PRIMARY KEY  ([otherruleid])
)
GO
CREATE TABLE [dbo].[{$prefix}role] (
	[roleid] [int] IDENTITY (1, 1) NOT NULL ,
	[rolename] [varchar] (20) COLLATE Latin1_General_CI_AS NOT NULL ,
	[importer] [int] NOT NULL ,
	[createtime] [datetime] DEFAULT GETDATE() NOT NULL ,
	PRIMARY KEY  ([roleid])
)
GO
CREATE TABLE [dbo].[{$prefix}rolerule] (
	[roleid] [int] NOT NULL ,
	[ruleid] [int] NOT NULL ,
	[issuperuser] [tinyint] NOT NULL ,
	[canbrowse] [tinyint] NOT NULL ,
	[canappend] [tinyint] NOT NULL ,
	[canmodify] [tinyint] NOT NULL ,
	[candelete] [tinyint] NOT NULL ,
	[importer] [int] NOT NULL ,
	[createtime] [datetime] DEFAULT GETDATE() NOT NULL ,
	PRIMARY KEY  ([roleid],[ruleid])
)
GO
CREATE TABLE [dbo].[{$prefix}rule] (
	[ruleid] [int] IDENTITY (1, 1) NOT NULL ,
	[rulename] [varchar] (50) COLLATE Latin1_General_CI_AS NOT NULL ,
	[parentruleid] [int] NOT NULL ,
	[ruleimg] [varchar] (255) COLLATE Latin1_General_CI_AS NULL ,
	[rulebigimg] [varchar] (255) COLLATE Latin1_General_CI_AS NULL ,
	[ruleurl] [varchar] (255) COLLATE Latin1_General_CI_AS NULL ,
	[ruleorder] [tinyint] NOT NULL ,
	[layer] [tinyint] NULL ,
	[importer] [int] NOT NULL ,
	[createtime] [datetime] DEFAULT GETDATE() NOT NULL ,
	PRIMARY KEY  ([ruleid])
)
GO
CREATE TABLE [dbo].[{$prefix}user] (
	[userid] [int] IDENTITY (1, 1) NOT NULL ,
	[username] [varchar] (50) COLLATE Latin1_General_CI_AS NOT NULL ,
	[userpass] [char] (32) COLLATE Latin1_General_CI_AS NOT NULL ,
	[loginnum] [int] DEFAULT 0 NOT NULL ,
	[importer] [int] NOT NULL ,
	[createtime] [datetime] DEFAULT GETDATE() NOT NULL ,
	PRIMARY KEY  ([userid])
)
GO
CREATE TABLE [dbo].[{$prefix}usergroup] (
	[userid] [int] NOT NULL ,
	[groupid] [int] NOT NULL ,
	[importer] [int] NOT NULL ,
	[createtime] [datetime] DEFAULT GETDATE() NOT NULL ,
	PRIMARY KEY  ([userid],[groupid])
)