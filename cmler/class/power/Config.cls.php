<?php
require_once(WEB_ADMIN_CLASS_PATH.'/power/Power.cls.php');
/**
 * @desc 配置
 * @author  Vanni
 * @version 1.0
 * @package power
 */
class Config{
	var $db = null;
	var $operater = null;
	var $userid = 0;
	var $pageid = 0;
	var $powerObj = null;
	/**
	 * 构造函数
	 *
	 * @param &dbObj $db 数据库操作对象
	 * @param Int $operater 操作者
	 * @param Int $userid 用户ID(哪一个用户的配置)
	 * @param Int $pageid 页面ID(哪一个页面的配置)
	 * @return Config
	 */
	function Config(&$db,$operater,$userid=0,$pageid=0){
		$this -> db = &$db;
		$this -> operater = $operater?$operater:0;
		$this -> userid = $userid?$userid:0;
		$this -> pageid = $pageid?$pageid:0;
		$this -> powerObj = new Power(&$db,$operater);
	}
	/**
	 * 获得配置值
	 *
	 * @param String $key 配置名称
	 * @return String 配置的值
	 */
	function getValue($key){
		$v = $this->getUserValue($key);
		return isset($v)?$v:$this->getSysValue($key);
	}
	/**
	 * 获得用户的配置值
	 *
	 * @param String $key 配置名称
	 * @return String 配置的值
	 */
	function getUserValue($key){
		$sql = 'select o.configdefault,c.configvalue from '.WEB_ADMIN_TABPOX.'otherrule o 
				left outer join '.WEB_ADMIN_TABPOX.'config c on c.otherruleid = o.otherruleid and c.userid = '.$this -> userid."
				where o.configvarname = '$key' and (ruleid = 0 or ruleid = ".$this -> pageid.')';
		$con = $this -> db -> GetRow($sql);
		if(!$con)return false;
		else{
			$this -> powerObj -> parseSqlData(&$con);
			if(strlen($con['configvalue'])>0){
				return $con['configvalue'];
			}else{
				return $con['configdefault'];
			}
		}
	}
	/**
	 * 获得系统的配置值
	 *
	 * @param String $key 配置名称
	 * @return String 配置的值
	 */
	function getSysValue($key){
		$sql = 'select configdefault from '.WEB_ADMIN_TABPOX."otherrule where ruleid = 0 and issystemvar = 1 and configvarname = '$key'";
		$con = $this -> db -> GetRow($sql);
		if(!$con)return false;
		$this -> powerObj -> parseSqlData(&$con);
		return $con['configdefault'];
	}
}
?>