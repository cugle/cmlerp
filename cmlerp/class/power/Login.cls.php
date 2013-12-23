<?php
/**
 * 包的确良
 * @desc 用户登录类
 * @author Vanni
 * @version 1.0
 * @package power
 * @deprecated 包的测试
*/
require_once('Config.cls.php');
class Login {
	/** @var 数据库对象	*/
	var $_db = null;

	/** @var 用户ID	*/
	var $_user = null;

	/** @var 权限数组[菜单ID]=>权限	*/
	var $_baseRuleArray = array();

	/** @var 附加权限*/
	var $_attachRuleArray = array();

	/** @var 超级用户登录的ID号*/
	var $_superid = null;

	/** @var 当前页面ID号*/
	var $_pageid = null;

	/** @var 当前的页面URL*/
	var $_pageURL = null;
	/**
	构造函数
	@return  void
	*/
	function Login(&$db,$pageid=null){
		$this->_db = &$db;
		if (!is_numeric($pageid)) $pageid = $this->getMenuId($pageid);
		$this->_pageid = $pageid;

		$row = $this ->_db -> GetRow('select * from '.WEB_ADMIN_TABPOX."login where clientid = '".$this->getClientId()."'");
		if(isset($row['userid'])){
			//更新状态
			$this -> _user = $row['userid'];
			$this -> _baseRuleArray = unserialize($row['rulestr']);
			$this -> _attachRuleArray = unserialize($row['attachrulestr']);
			$this -> _superid = $row['superid']?$row['superid']:null;
			$this -> _db -> Execute('update '.WEB_ADMIN_TABPOX.'login set logintime='.time()." where clientid='".$row['clientid']."'");
		}
	}
	/**
	 * 检测登录状态，以及验证
	 *
	 */
	function checkState(){
		require_once(WEB_ADMIN_CLASS_PATH.'/power/LoginCheck.cls.php');
		new LoginCheck(&$this);
	}
	/**
	 * 返回字符串用于查找
	 */
	function _baseRuleToStr(){
		$ruleStr = '';
		foreach ($this->_baseRuleArray as $k=>$v){
			if ($ruleStr=='') $ruleStr = $k;
			else $ruleStr .=','. $k;
		}
		return $ruleStr;
	}
	/**
	@desc 根据用户ID查出所有的权限字段
	@param $uid Int 用户ID
	@return Array
	*/
	function registerUser($userid=null){
		require_once('Power.cls.php');
		if ($userid) $this->_user = $userid;
		$powObj = new Power(&$this->_db,$this->_user);
		$ruleArr = $powObj->getUserRule($this->_user);
		$this->_baseRuleArray = $ruleArr['base'];
		$this->_attachRuleArray = $ruleArr['attach'];
		$this->logout();
		//插入一行记录到登录表
		$this->_db->Execute('update '.WEB_ADMIN_TABPOX.'login set updatestate=2 where userid='.$this->_user);
		$sql = 'insert into '.WEB_ADMIN_TABPOX.'login(userid,clientid,superid,rulestr,attachrulestr,updatestate,logintime)'.
		"values(".$this->_user.",'".$this->getClientId()."',".(($this->_superid)?$this->_superid:'NULL').",'".serialize($this->_baseRuleArray)."','".serialize($this->_attachRuleArray)."',0,'".time()."')";
		$this->_db->Execute($sql);
	}
	/**
	@desc 检查用户是否存在
	@return Bool
	*/
	function checkUser($name,$pass){
		$name = $this -> _db -> escape($name,get_magic_quotes_gpc());
		$uid = $this->_db->getOne('SELECT userid FROM '.WEB_ADMIN_TABPOX."user WHERE (username = '$name') AND (userpass = '".md5($pass)."')");
		if ($uid) {
			$this->_user = $uid;
			return true;
		}else {
			return false;
		}
	}
	/**
	@desc 注册组内用户
	@return Bool
	*/
	function registerGuser($guser,$superid = null,$msg = null){
		$guser = $this -> _db -> escape($guser,get_magic_quotes_gpc());
		if(!$superid) 	$superid = $this->_user;
		if(!$msg)		$msg = $_POST['gtext'];
		$sql ='
			SELECT su.userid FROM '.WEB_ADMIN_TABPOX.'groupmanager gr INNER JOIN
			      '.WEB_ADMIN_TABPOX.'usergroup ug ON ug.groupid = gr.groupid INNER JOIN
			      '.WEB_ADMIN_TABPOX."user su ON su.userid = ug.userid
			WHERE (gr.userid = $superid) AND (su.username = '$guser')";
		$subUserId = $this->_db->getOne($sql);
		if ($subUserId){
			require_once(WEB_ADMIN_CLASS_PATH.'/power/Config.cls.php');
			$c = new Config(&$this -> _db ,$this->getUid(),$subUserId);

			if(!$c -> getValue('allowGroupManagerLogi')) return '您尝试登录的用户，不接受组长以他的身份登录！';

			$n = $this -> _db -> GetOne("select count(*) from ".WEB_ADMIN_TABPOX."message where msgtitle='组长登录' and sendtoids='{$subUserId}' and userid={$superid} and hadratify=0");
			if($n >= $c -> getValue('allowErrorManagerLogi')) return '您已经达到该用户未经许可登录的最大次数，请等待该用户许可您的前次登录！';//未经允可的登录次数

			$this -> _db -> Execute("insert into s_message(msgtitle,msgtext,sendtoids,userid)values('组长登录','登录IP:".$this->getIP()." 登录原因:{$msg}','{$subUserId}',{$superid})");

			$this->_superid = $superid;
			$this->registerUser($subUserId);
			return true;
		}else{
			return $guser.'用户不存在';
		}
	}
	/**
	@param $array Array 模板里面的各权限变量
	@return array 设置好了的变量
	@desc 设置模板权限变量
	*/
	function setTemplateVal($id,$array=null){
		if (!is_numeric($id))$id = $this->getMenuId($id);
		$allowStr = $this->_baseRuleArray[$id];
		$setArr = array();
		if ($array == null) {
			$array = array(
			'is_superuser'=>'superid',
			'allow_delete'=>'delete',
			'allow_modify'=>'modify',
			'allow_append'=>'append',
			'allow_browse'=>'browse'
			);
		}
		foreach ($array as $k=>$v){
			switch ($v){
				case 'superid':  $setArr[$k] = $allowStr[0];   break;
				case 'delete':   $setArr[$k] = $allowStr[4];   break;
				case 'modify':   $setArr[$k] = $allowStr[3];   break;
				case 'append':   $setArr[$k] = $allowStr[2];   break;
				case 'browse':   $setArr[$k] = $allowStr[1];   break;
			}
		}
		return $setArr;
	}
	/**
	@desc 得到用户ID
	@return Int
	*/
	function getUid(){
		return $this->_user;
	}
	/**
	@desc 得到是否有删除权限
	@return Bool
	*/
	function getAllowDelete($id=null){
		if($id == null){
			$id = $this->_pageid;
		}else{
			if (!is_numeric($id))$id = $this->getMenuId($id);
		}
		if($id==0)return 1;
		$allowStr = $this->_baseRuleArray[$id];
		return $allowStr[4];
	}
	/**
	@desc 得到是否有修改权限
	@return Bool
	*/
	function getAllowModify($id=null){
		if($id == null){
			$id = $this->_pageid;
		}else{
			if (!is_numeric($id))$id = $this->getMenuId($id);
		}
		if($id==0)return 1;
		$allowStr = $this->_baseRuleArray[$id];
		return $allowStr[3];
	}
	/**
	@desc 得到是否有增加权限
	@return Bool
	*/
	function getAllowAppend($id=null){
		if($id == null){
			$id = $this->_pageid;
		}else{
			if (!is_numeric($id))$id = $this->getMenuId($id);
		}
		if($id==0)return 1;
		$allowStr = $this->_baseRuleArray[$id];
		return $allowStr[2];
	}
	/**
	@desc 得到是否有浏览权限
	@return Bool
	*/
	function getAllowBrowse($id=null){
		if($id == null){
			$id = $this->_pageid;
		}else{
			if (!is_numeric($id))$id = $this->getMenuId($id);
		}
		if($id==0)return 1;
		$allowStr = $this->_baseRuleArray[$id];
		return $allowStr[1];
	}
	/**
	@desc 得到是否有超用户权限
	@return Bool
	*/
	function isSuperUser($id=null){
		if($id == null){
			$id = $this->_pageid;
		}else{
			if (!is_numeric($id))$id = $this->getMenuId($id);
		}
		if($id==0)return 1;
		$allowStr = $this->_baseRuleArray[$id];
		return $allowStr[0];
	}
	/**
	@desc 退出
	*/
	function logout(){
		$this->_db->Execute('delete from '.WEB_ADMIN_TABPOX."login where clientid ='".$this->getClientId()."'");
		$c = new Config(&$this -> _db,$this->_user);
		$s = $c -> getSysValue('loginTimeOut');
		//	 	echo '<BR><BR>DEL USER IS :delete from '.WEB_ADMIN_TABPOX.'login where logintime + '.$s.' < '.time();
		$this->_db->Execute('delete from '.WEB_ADMIN_TABPOX.'login where logintime + '.$s.' < '.time());
		setcookie('PHPSESSIONID',null,time(),'/');
		unset($_COOKIE['PHPSESSIONID']);
	}
	/**
	@desc 查找页面对应的菜单ID号
	*/
	function getMenuId($name){
		if ($name)	return $this->_db->GetOne('SELECT ruleid FROM '.WEB_ADMIN_TABPOX.'rule WHERE rulename =\''.$name.'\'');
		$dr = WEB_ADMIN_PHPROOT;		//ADMIN在的文件目录
		$p = empty($_SERVER['PHP_SELF'])?$_SERVER['PATH_INFO']:$_SERVER['PHP_SELF'];	//当前页的URL
		$ppx = '';						//当前页的?后缀
		if($point = strpos($p,'?')){
			$ppx = substr($p,$point);
			$p = substr($p,0,$point);
		}
		$temp = $this -> _db -> GetArray('SELECT ruleid,ruleurl FROM '.WEB_ADMIN_TABPOX.'rule WHERE ruleurl is not null');
		foreach ($temp as $v){
			if(!$v['ruleurl'])continue;
			$px = '';					//?后缀
			if($point = strpos($v['ruleurl'],'?')){
				$px = substr($v['ruleurl'],$point);
				$v['ruleurl'] = substr($v['ruleurl'],0,$point);
			}
			$path = ($v['ruleurl'][0]=='/') ? ($_SERVER['DOCUMENT_ROOT'].$v['ruleurl']) : ($dr.'/'.$v['ruleurl']);
			$pageAdd = str_replace('\\','/',realpath($path));
			if( strpos(strtolower($pageAdd),strtolower($p))!==false){
				if ($px){
					if(strpos(strtolower($ppx),strtolower($px))){
						$this -> _pageURL = $p.$px;
						return $v['ruleid'];
					}
				}else{
					$this -> _pageURL = $p;
					return $v['ruleid'];
				}
			}
		}
	}
	/**
	 * 获得所管理的组
	 */
	function getManageGroups(){
		$return = array();
		$tmp = $this -> _db -> GetArray('SELECT groupid FROM '.WEB_ADMIN_TABPOX.'groupmanager WHERE userid = '.$this->_user);
		foreach ($tmp as $v){
			$return[] = $v['groupid'];
		}
		return $return;
	}
	/**
	 * 返加附加权，如果单选项有二个或二个以上的值，将返回一个数组
	 *
	 * @param String $varname 变量名
	 * @param Int $id 菜单ID，默认为当前面
	 * @return Mixed
	 */
	function getAttach($varname,$id=null){
		if($id == null)				$id = $this->_pageid;
		else{
			if (!is_numeric($id))	$id = $this->getMenuId($id);
		}
		if(isset($this->_attachRuleArray[$id][$varname])){
			return $this->_attachRuleArray[$id][$varname];
		}else{
			require_once(WEB_ADMIN_CLASS_PATH.'/power/Power.cls.php');
			$p = new Power($this->_db,$this->getUid());
			$sourceStr = $this -> _db -> GetRow("select vardefault as configdefault from ".WEB_ADMIN_TABPOX."otherrule where otherrulevarname = '$varname' and ruleid = ".$id);
			$p -> parseSqlData(&$sourceStr);
			if(isset($sourceStr['configdefault'])){
				if(strpos($sourceStr['configdefault'],'#')){
					return explode('#',$sourceStr['configdefault']);
				}else{
					return $sourceStr['configdefault'];
				}
			}else{
				return false;
			}
		}
	}
	/**
	 * 获得当前页的相关权限，值，或数组值
	 */
	function getAttachRule($varname,$id=null){
		require_once(WEB_ADMIN_CLASS_PATH.'/power/Power.cls.php');
		$p = new Power($this->_db,$this->getUid());

		if(is_null($id))				$id = $this->_pageid;
		else{
			if (!is_numeric($id))	$id = $this->getMenuId($id);
		}
		$sql = "
			SELECT configtype,configdefault,c.configvalue AS userdefault FROM ".WEB_ADMIN_TABPOX."otherrule o 
				LEFT OUTER JOIN	".WEB_ADMIN_TABPOX."config c ON ( (o.otherruleid = c.otherruleid) AND (c.userid = ".$this->getUid().") )
			WHERE (o.isrule = 1) AND (configvarname = '$varname') AND (ruleid = $id)
		";
		$sourceStr = $this -> _db -> GetRow($sql);
		$p -> parseSqlData(&$sourceStr);
		//		print_r($this->_attachRuleArray[$id]);
		//		print_r($sourceStr);
		//		echo '<br>',$varname,'<br>';
//		echo $id,$varname;
//		print_r($this->_attachRuleArray[$id][$varname]);
		if(isset($this->_attachRuleArray[$id][$varname])){
			if (!is_array($this->_attachRuleArray[$id][$varname])) {
				return $this->_attachRuleArray[$id][$varname];
			}else{
				if (isset($sourceStr['configtype'])){
					switch (strtolower($sourceStr['configtype'])){
						case 'text':
						case 'select':
						case 'radio'://有多个可选项的单选项
						if(strlen($sourceStr['userdefault'])>0){
							return $sourceStr['userdefault'];
						}else{
							if(in_array($sourceStr['configdefault'],$this->_attachRuleArray[$id][$varname])){
								return $sourceStr['configdefault'];
							}else{
								sort($this->_attachRuleArray[$id][$varname]);
								return $this->_attachRuleArray[$id][$varname][0];
							}
						}
						break;
						default:
							return $this->_attachRuleArray[$id][$varname];
						break;
					}
				}else{
					return false;
				}
			}
		}else{
			if(isset($sourceStr['configdefault'])){
				if(strpos($sourceStr['configdefault'],'#')){
					return explode('#',$sourceStr['configdefault']);
				}else{
					return $sourceStr['configdefault'];
				}
			}else{
				return false;
			}
		}
	}
	/**
	 * 获得客户机的唯一编号
	 *
	 * @return String
	 */
	function getClientId() {
		if(isset($_COOKIE['PHPSESSIONID'])){
			return $_COOKIE['PHPSESSIONID'];
		}else{
			$c = new Config(&$this -> _db,$this->_user);
			$s = time() + $c -> getSysValue('loginTimeOut');
			$str = md5($this -> getIP() . $s);
			setcookie('PHPSESSIONID',$str,$s,'/');
			return $str;
		}

	}
	/**
	 * 获得浏览者的IP
	 *
	 * @return String
	 */
	function getIP(){
		if (isset($_SERVER)) {
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$realip = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				$realip = $_SERVER['REMOTE_ADDR'];
			}
		} else {
			if (getenv("HTTP_X_FORWARDED_FOR")) {
				$realip = getenv( "HTTP_X_FORWARDED_FOR");
			} elseif (getenv("HTTP_CLIENT_IP")) {
				$realip = getenv("HTTP_CLIENT_IP");
			} else {
				$realip = getenv("REMOTE_ADDR");
			}
		}
		return $realip;
	}
}
?>