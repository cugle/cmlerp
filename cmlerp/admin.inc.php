<?
/**
 * @desc HooPower 接口类
 * @author  Vanni
 * @version 1.0
 */
require('config.php');

require_once(WEB_ADMIN_CLASS_PATH.'/adodb/adodb.inc.php');

require_once(WEB_ADMIN_CLASS_PATH.'/power/Login.cls.php');
require_once(WEB_ADMIN_CLASS_PATH.'/power/Config.cls.php');
date_default_timezone_set('PRC');
define('ACTION','action');//事件名
define('APPEND','add');//增加
define('MODIFY','upd');//修改
define('DELETE','del');//删除

//Style
define('APPEND_YES_IMG',WEB_ADMIN_HTTPPATH.'/common/img/add.gif');			//可以增加时的增加按钮图片
define('APPEND_NO_IMG',WEB_ADMIN_HTTPPATH.'/common/img/add_no.gif');		//不可以增加时的增加按钮图片
define('MODIFY_YES_IMG',WEB_ADMIN_HTTPPATH.'/common/img/edit.gif');			//可以修改时的修改按钮图片
define('MODIFY_NO_IMG',WEB_ADMIN_HTTPPATH.'/common/img/edit_no.gif');		//不可以修改时的修改按钮图片
define('DELETE_YES_IMG',WEB_ADMIN_HTTPPATH.'/common/img/delete.gif');		//可以删除时的删除按钮图片
define('DELETE_NO_IMG',WEB_ADMIN_HTTPPATH.'/common/img/delete_no.gif');		//不可以删除时的删除按钮图片

/**
 * 后台管理页面入口类
 * 
 * @author Vanni
 * @version 1.0
 */
class admin{
	/**
	 * 数据库操作对象
	 *
	 * @var Obj
	 */
	var $dbObj = null;
	/**
	 * 登录对象
	 *
	 * @var Obj
	 */
	var $loginObj = null;
	/**
	 * 页面配置对象
	 *
	 * @var Obj
	 */
	var $configObj = null;
	/**
	 * 页面的URL
	 *
	 * @var String
	 */
	var $pageUrl = null;
	/**
	 * 是不是增加页
	 *
	 * @var Boolean
	 */
	var $isAppend = false;
	/**
	 * 是不是修改页
	 *
	 * @var Boolean
	 */
	var $isModify = false;
	/**
	 * 是不是删除页
	 *
	 * @var Boolean
	 */
	var $isDelete = false;
	/**
	 * 是不是有POST提交
	 *
	 * @var Boolean
	 */
	var $isPostBack = false;
	/**
	 * 是不是有GET提交
	 *
	 * @var Boolean
	 */
	var $isGetBack = false;
	/**
	 * 构造方法，内部实际是调用 __construct()方法构造
	 *
	 * @return admin
	 */
	function admin($pageid=null){
		$this -> __construct($pageid);
	}
	/**
	 * 入口函数
	 *
	 */
	function Main(){
		$this -> loginObj -> checkState();
		$this -> configObj -> userid = $this -> getUid();
		
		if (isset($_GET[ACTION])) {
			$this -> isGetBack = true;
			if ($_GET[ACTION] == DELETE)
			{
				if ($this->loginObj->getAllowDelete()) {
					$this -> isDelete = true;
					$this -> goDelete();
				}
				else
				{
					$this -> quit('没有删除权');
				}
			}
			elseif ($_GET[ACTION] == APPEND)
			{
				if ($this->loginObj->getAllowAppend())
				{
					$this -> isAppend = true;
					$this -> goDispAppend();
				}
				else
				{
					$this -> quit('没有增加权');
				}
			}
			elseif ($_GET[ACTION] == MODIFY)
			{
				if ($this->loginObj->getAllowModify())
				{
					$this -> isModify = true;
					$this -> goDispModify();
				}
				else
				{
					$this -> quit('没有修改权');
				}
			}
		}
		elseif(isset($_POST[ACTION]))
		{
			$this -> isPostBack = true;
			if ($_POST[ACTION] == MODIFY) 
			{
				if ($this->loginObj->getAllowModify())
				{
					$this -> isModify = true;
					$this -> goModify();
				}
				else
				{
					$this -> quit('没有修改权');
				}
			}
			elseif ($_POST[ACTION] == APPEND)
			{
				if ($this->loginObj->getAllowAppend())
				{
					$this -> isAppend = true;
					$this -> goAppend();
				}
				else
				{
					$this -> quit('没有增加权');
				}
			}
		}
		else 
		{
			$this -> disp();
		}
	}
	/**
	 * 构造函数，管理类
	 * 提供的方法只为当前页面服务。自动分析当前用户所在的页的权限
	 * 通过 getXxx() 来获得是否有操作权 Append、Modify、Delete、Supper
	 * 通过 getAttach($name) 来获得是否有附加权
	 * 另外还可以通过 getValue($name) 来获得用户自己配置的页面属性
	 *
	 * @return admin
	 */
	function __construct($pageid=null){
		$this->dbObj = &db::getLink();
		$this->loginObj = new Login(&$this->dbObj,$pageid);
		$this->configObj = new Config(&$this->dbObj,$this->loginObj->getUid(),$this->loginObj->getUid(),$this->loginObj->_pageid);
		$this -> pageUrl = $this->loginObj->_pageURL;
	}
	/**
	 * 析构函数，释放DB对象
	 */
	function __destruct(){
		$this ->dbObj -> Close();
	}
	/**
	 * 开启数据库SQL语句提示
	 */
	function debugOn(){
		$this->dbObj->debug=true;
	}
	/**
	 * 关闭数据库SQL语句提示
	 */
	function debugOff(){
		$this->dbObj->debug=false;
	}
	/**
	 * 得到当前用户的ID
	 *
	 * @return Int
	 */
	function getUid(){
		return $this->loginObj->getUid();
	}
	/**
	 * 检查用户，无返回，如果不合法，自动跳转
	 */
	function checkUser(){
		$this -> loginObj -> checkState();
	}
	/**
	 * 是否可以修改
	 *
	 * @return Boolean
	 */
	function getModify(){
		return $this->loginObj->getAllowModify();
	}
	/**
	 * 是否可以增加
	 *
	 * @return Boolean
	 */
	function getAppend(){
		return $this->loginObj->getAllowAppend();
	}
	/**
	 * 是否可以删除
	 *
	 * @return Boolean
	 */
	function getDelete(){
		return $this->loginObj->getAllowDelete();
	}
	/**
	 * 是否超用户
	 *
	 * @return Boolean
	 */
	function getSupper(){
		return $this->loginObj->isSuperUser();
	}
	/**
	 * 得到用户的附加允可权
	 *
	 * @param String $name 页面权限变量
	 * @return Boolean
	 */
	function getAttach($name,$id=null){
		return $this->loginObj->getAttachRule($name,$id);
	}
	/**
	 * 获得一个数组
	 * 如果提供参数，参数为一个数组，返回格式如下：
	 * 
	 * 参数：     array('superid','append','browse','delete','modify');
	 * 返回结果： array(0=>false,1=>true,2=>true,3=>false,4=>true);
	 * 
	 * 默认将返回：
	 * array('is_superuser'=>false,'allow_delete'=>false,'allow_modify'=>true,'allow_append'=>true,'allow_browse'=>true);
	 *
	 * @return Array
	 */
	function getVal2Arr($arr){
		return $this->loginObj->setTemplateVal(null,$arr);
	}
	/**
	 * 得到页面配置值
	 *
	 * @param String $name 页面属性变量
	 * @return Boolean
	 */
	function getValue($name){
		return $this->configObj->getValue($name);
	}
	/**
	 * 虚拟方法，请重构，以实现删除功能
	 */
	function goDelete(){}
	/**
	 * 虚拟方法，请重构，以实现增加功能
	 */
	function goAppend(){}
	/**
	 * 虚拟方法，请重构，以实现修改功能
	 */
	function goModify(){}
	/**
	 * 虚拟方法，请重构，以实现显示增加页面功能
	 */
	function goDispAppend(){}
	/**
	 * 虚拟方法，请重构，以实现显示修改页面功能
	 */
	function goDispModify(){}
	/**
	 * 退出方法，接受退出信息，未定义具体内容，可以重构，以实现自己的效果
	 *
	 * @param String $info
	 */
	function quit($info){}
	/**
	 * 默认的显示方法
	 *
	 */
	function disp(){}
	/**
	 * 得到可以增加的字符串
	 *
	 * @param String $type 返回类型，可以为a,button,img
	 * @param String $url 提交的页面地址，默认为空表示当前页
	 * @param Boolean $onoff 强制开关
	 * @param Array $imgArr 如果类型$type为img的时候可以指定img的图片，默认为系统定义的常量
	 * @return String
	 */
	function getAddStr($type='a',$url=null,$onoff=null,$imgArr=''){
		return $this -> _toUrlStr(APPEND,0,0,$type,$url,$onoff,$imgArr);
	}
	/**
	 * 得到可以增加的字符串
	 *
	 * @param Int $srcId 要修改的记录的用户ID
	 * @param String $type 返回类型，可以为a,button,img
	 * @param String $url 提交的页面地址，默认为空表示当前页
	 * @param Boolean $onoff 强制开关
	 * @param Array $imgArr 如果类型$type为img的时候可以指定img的图片，默认为系统定义的常量
	 * @return String
	 */
	function getUpdStr($srcId,$updid,$type='a',$url=null,$onoff=null,$imgArr=''){
		return $this -> _toUrlStr(MODIFY,$srcId,$updid,$type,$url,$onoff,$imgArr);
	}
	/**
	 * 得到可以删除的字符串
	 *
	 * @param Int $srcId 要删除的记录的用户ID
	 * @param String $type 返回类型，可以为a,button,img
	 * @param String $url 提交的页面地址，默认为空表示当前页
	 * @param Boolean $onoff 强制开关
	 * @param Array $imgArr 如果类型$type为img的时候可以指定img的图片，默认为系统定义的常量
	 * @return String
	 */
	function getDelStr($srcId,$delid,$type='a',$url=null,$onoff=null,$imgArr=''){
		return $this -> _toUrlStr(DELETE,$srcId,$delid,$type,$url,$onoff,$imgArr);
	}
	/**
	 * 权有方法，处理当前权限应该返回的字符串
	 *
	 * @param String $type
	 * @param Int $uId
	 * @param Int $sId
	 * @param String $sType
	 * @param String $url
	 * @param Boolean $onoff
	 * @param Array $imgArr
	 * @return String
	 */
	function _toUrlStr($type,$uId,$sId,$sType,$url,$onoff,$imgArr){
		if (!$url){
			$url  = $this -> pageUrl;
		}
		if (strpos($url,'?')) 	$url .= '&'.ACTION.'='.$type;
		else					$url .= '?'.ACTION.'='.$type;

		if ($type!=APPEND)			$url .= '&'.$type.'id='.$sId;
		
		$fun = $name = $imgYes = $imgNo = $delTitle = $delEvent = '';
		switch ($type){
			case APPEND:$fun='getAppend';$name='增加';$imgYes=APPEND_YES_IMG;$imgNo=APPEND_NO_IMG;break;
			case MODIFY:$fun='getModify';$name='修改';$imgYes=MODIFY_YES_IMG;$imgNo=MODIFY_NO_IMG;break;
			case DELETE:
				$fun='getDelete';
				$name='删除';
				$imgYes=DELETE_YES_IMG;
				$imgNo=DELETE_NO_IMG;
				if($this->getValue('delete')){
					$delTitle = ' onClick = "if(confirm(\'真的确定删除吗？\'))return true;else return false;" ';//删除提示
					$delEvent = ' if(!confirm("真的确定删除吗？"))return false; ';
				}
			break;
		}

		if(is_null($onoff)){
			if ($this -> getSupper() || $type==APPEND){
				switch (strtolower($sType)){
					case 'a':
						if ($this -> $fun())	return "<a href='$url'$delTitle>$name</a>";
						else return $name;
					break;
					case 'button':
						if ($this -> $fun())	return "<input type='button' onclick='$delEvent location.href=\"$url\"' value='$name' />";
						else return "<input type='button' value='$name' disabled />";
					break;
					case 'img':
						if ($this -> $fun())	return "<a href='$url'$delTitle><img align='middle' alt='$name' src='".(($imgArr)?$imgArr[0]:$imgYes)."' border='0' /></a>";
						else return '<img align="middle" alt="$name" src="'.(($imgArr)?$imgArr[1]:$imgNo).'" />';
					break;
				}
			}else{
				switch (strtolower($sType)){
					case 'a':
						if ($this -> $fun() && $this -> getUid() == $uId)	return "<a href='$url'$delTitle>$name</a>";
						else return $name;
					break;
					case 'button':
						if ($this -> $fun() && $this -> getUid() == $uId)	return "<input type='button' onclick='$delEvent location.href=\"$url\"' value='$name' />";
						else return "<input type='button' value='$name' disabled />";
					break;
					case 'img':
						if ($this -> $fun() && $this -> getUid() == $uId)	return "<a href='$url'$delTitle><img align='middle' alt='$name' src='".(($imgArr)?$imgArr[0]:$imgYes)."' border='0' /></a>";
						else return "<img align='middle' alt='$name' src='".(($imgArr)?$imgArr[1]:$imgNo)."' />";
						break;
				}
			}
		}else{
			switch (strtolower($sType)){
				case 'a':
					if ($onoff){
						return "<a href='$url'$delTitle>$name</a>";
					}
					else return $name;
				break;
				case 'button':
					if ($onoff)	return "<input type='button' onclick='$delEvent location.href=\"$url\"' value='$name' />";
					else return "<input type='button' value='$name' disabled />";
				break;
				case 'img':
					if ($onoff)	return "<a href='$url'$delTitle><img align='middle' alt='$name' src='".(($imgArr)?$imgArr[0]:$imgYes)."' border='0' /></a>";
					else return '<img align="middle" alt="$name" src="'.(($imgArr)?$imgArr[1]:$imgNo).'" />';
				break;
			}
		}
	}
}
?>