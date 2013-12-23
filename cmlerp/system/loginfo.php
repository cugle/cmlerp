<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/Pages.cls.php');

class PageLoginfo extends admin {
	function disp(){

		$t = new Template('../template/system');
		$t -> set_file('f','loginfo.html');
		
		$t -> set_block('f','row','r');
		$rs = $this -> dbObj -> GetArray('select lt.logtypename,l.* from '.WEB_ADMIN_TABPOX.'log l,'.WEB_ADMIN_TABPOX.'logtype lt where l.logtypeid=lt.logtypeid and l.srcuserid='.$this->getUid());
		foreach ($rs as $v){
			$t -> set_var($v);
			$ok = ($this->loginObj->_superid)?false:true;
			$t -> set_var('del',$this->getDelStr($this->getUid(),$v['logid'],'a','loginfo.php?deltype=log',$ok));
			$t -> parse('r','row',true);
		}
		
		$t -> set_block('f','row2','r2');
		$rs = $this -> dbObj -> GetArray("select u.username,m.* from ".WEB_ADMIN_TABPOX."message m,".WEB_ADMIN_TABPOX."user u where m.userid = u.userid and  msgtitle='组长登录' and sendtoids like '".$this->getUid()."'");
		foreach ($rs as $v){
			if(!$v['hadratify']){
				if(!$this->loginObj->_superid){
					$v['accept'] = '<a href="loginfo.php?action=accept&id='.$v['msgid'].'">认可</a>';
				}else{
					$v['accept'] = '认可';
				}
			}else{
				$v['accept'] = '已认可';
			}
			$t -> set_var($v);
			$ok = ($this->loginObj->_superid)?false:true;
			$t -> set_var('del',$this->getDelStr($this->getUid(),$v['msgid'],'a','loginfo.php?deltype=msg',$ok));
			$t -> parse('r2','row2',true);
		}
		
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDelete(){
		$delid = $_GET['delid'] + 0;
		if($_GET['deltype']=='log'){
			$this -> dbObj -> Execute('delete from '.WEB_ADMIN_TABPOX.'log where logid='.$delid);
		}else{
			$this -> dbObj -> Execute('delete from '.WEB_ADMIN_TABPOX.'message where msgid='.$delid);
		}
		$this -> disp();
	}
	function Main(){
		if(isset($_GET['action']) && $_GET['action']=='accept'){
			$accid = $_GET['id'] + 0;
			$this -> dbObj -> Execute('update '.WEB_ADMIN_TABPOX.'message set hadratify=1 where msgid='.$accid);
			unset($_GET);
		}
		parent::Main();
	}
}
$main = new PageLoginfo();
$main -> Main();
?>