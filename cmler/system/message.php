<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/xajax/xajax.inc.php');

class PageMessage extends admin {
	var $xo = null;
	function disp(){
		$this -> forAjax();
		$t = new Template('../template/system');
		$t -> set_file('f','message.html');
		$t -> set_var('ajaxstr',$this -> xo -> getJavascript(WEB_ADMIN_HTTPCOMMON.'/js/'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> set_var('curuser',$this->getUid());
		$t -> set_var('disabled',$this->getAppend()?'':' disabled');
		$t -> set_block('f','row','r');
		
		$rs = &$this -> dbObj -> Execute("select u.username,m.* from ".WEB_ADMIN_TABPOX."message m,".WEB_ADMIN_TABPOX."user u where m.userid=u.userid AND sendtoids like '%,".$this->getUid().",%'");
		while (!$rs->EOF) {
			//其它接收者
			$otherUserId = substr($rs->fields['sendtoids'],1,strlen($rs->fields['sendtoids'])-2);
			$inRs = $this -> dbObj -> Execute('select distinct username from '.WEB_ADMIN_TABPOX.'user where userid <> '.$this -> getUid().' and userid in('.$otherUserId.')');
			if(!$inRs->EOF){
				$t -> set_var('dispOther','');
				$oname = '';
				while (!$inRs -> EOF) {
					$oname .= ' '.$inRs->fields['username'];
					$inRs -> MoveNext();
				}
				$t -> set_var('otherName',$oname);
			}else{
				$t -> set_var('otherName','');
				$t -> set_var('dispOther','none');
			}
			$t -> set_var($rs->fields);
			$t -> set_var('del',$this->getDelStr($this->getUid(),$rs->fields['msgid'],'a','message.php?srcids='.$rs->fields['sendtoids']));
			$rs -> MoveNext();
			$t -> parse('r','row',true);
		}
		//查询初始用户
		$defuser = '';
		if(true){
			$userSql = 'select userid,username from '.WEB_ADMIN_TABPOX.'user';
		}else{
			$userSql = 'SELECT DISTINCT u2.userid,u2.username FROM '.WEB_ADMIN_TABPOX.'user u 
				INNER JOIN '.WEB_ADMIN_TABPOX.'usergroup ug ON (u.userid = ug.userid) AND (u.userid = '.$this->getUid().')
				INNER JOIN '.WEB_ADMIN_TABPOX.'usergroup ug2 ON (ug.groupid = ug.groupid)
				INNER JOIN '.WEB_ADMIN_TABPOX.'user u2 ON (u2.userid = ug2.userid)
			';
		}
		$rs = $this -> dbObj -> Execute($userSql);
		while (!$rs->EOF) {
			$defuser .= '<input name="sendtoids[]" type="checkbox" value="'.$rs->fields['userid'].'" checked>'.$rs->fields['username'].'&nbsp;';
			$rs -> MoveNext();
		}
		$t -> set_var('defuser',$defuser);
		
		$t -> parse('out','f');
		$t -> p('out');
		echo '|'.$this -> getValue('loginErrorTimeOut').'|';
	}
	function goAppend(){
		$rs = & $this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'message where msgid = -1');
		$_POST['msgtext'] = str_replace("\r\n",'<br>',$_POST['msgtext']);
		$_POST['sendtoids'] = is_array($_POST['sendtoids'])?(','.implode(',',$_POST['sendtoids']).','):'';
		$this -> dbObj -> Execute($this -> dbObj -> GetInsertSQL(&$rs,$_POST,get_magic_quotes_gpc()) );
		$this -> disp();
	}
	function goDelete(){
		$delid = $_GET['delid'] + 0;
		$new = str_replace(','.$this->getUid().',',',',$_GET['srcids']);
		if($new == ','){
			$this -> dbObj -> Execute('delete from '.WEB_ADMIN_TABPOX.'message where msgid = '.$delid);
		}else{
			$this -> dbObj -> Execute('update '.WEB_ADMIN_TABPOX."message set sendtoids = '$new' where msgid = $delid");
		}
		$this -> disp();
	}
	function forAjax(){
		$this -> xo = new xajax();
		$GLOBALS['AjaxDo']  = &$this -> dbObj;
		$GLOBALS['AjaxUid'] = $this -> getUid();
		function getList($field){
			$r = new xajaxResponse();
			$returnStr = '';
			if($field == 'global'){
				$userSql = 'select userid,username from '.WEB_ADMIN_TABPOX.'user';
			}else{
				$userSql = 'SELECT DISTINCT u2.userid,u2.username FROM '.WEB_ADMIN_TABPOX.'user u 
					INNER JOIN '.WEB_ADMIN_TABPOX.'usergroup ug ON (u.userid = ug.userid) AND (u.userid = '.$GLOBALS['AjaxUid'].')
					INNER JOIN '.WEB_ADMIN_TABPOX.'usergroup ug2 ON (ug2.groupid = ug.groupid)
					INNER JOIN '.WEB_ADMIN_TABPOX.'user u2 ON (u2.userid = ug2.userid)
				';
			}
			$rs = $GLOBALS['AjaxDo']->Execute($userSql);
			while (!$rs->EOF) {
				$returnStr .= '<input name="sendtoids[]" type="checkbox" value="'.$rs->fields['userid'].'" checked>'.$rs->fields['username'].'&nbsp;';
				$rs -> MoveNext();
			}
			$r -> addAssign('touser','innerHTML',$returnStr);
			$r -> addAssign('touser','innerHTML',$returnStr);
			return $r;
		}
		$this -> xo -> registerFunction("getList");
		$this -> xo -> processRequests();
	}
}
$main = new PageMessage();
$main -> Main();
?>