<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class PageUser extends admin {
	function disp(){
		//定义模板
		$t = new Template('../template/system');
		$t -> set_file('f','select_user.html');
		$t -> set_block('f','user','u');
		$t -> set_block('user','group','g');
		$t -> set_block('f','grouprow','gr');		
		$t -> set_var('add',$this -> getAddStr('img'));
		//设置用户
		if($_GET['groupid']!=''){
		$rs = $this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX.'user u inner join '.WEB_ADMIN_TABPOX.'usergroup ug on u.userid=ug.userid where ug.groupid='.$_GET['groupid']);}
		else
		{
		$rs = $this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX.'user');
		}
		while ($rrs = &$rs -> FetchRow()) {
			foreach ($rrs as $k=>$v)       $t -> set_var($k,$v);
			$t -> set_var('upd',$this->getUpdStr($rrs['importer'],$rrs['userid'],'img'));
			$t -> set_var('del',$this->getDelStr($rrs['importer'],$rrs['userid'],'img'));
			$t -> set_var('g');
			$inrs = $this -> dbObj -> Execute("select g.* from ".WEB_ADMIN_TABPOX."usergroup ug inner join ".WEB_ADMIN_TABPOX."group g on ug.groupid=g.groupid where ug.userid = ".$rrs['userid']);
			while ($inrrs = &$inrs -> FetchRow()) {
				foreach ($inrrs as $k=>$v) $t -> set_var($k,$v);
				$t -> parse('g','group',true);
			}
			$inrs -> Close();
			$t -> parse('u','user',true);
		}
		//设置组
		$grs = $this -> dbObj -> GetArray('select * from '.WEB_ADMIN_TABPOX.'group');
		if (isset($_GET['groupid'])){
			$groupid = $_GET['groupid'] + 0;
		}else{
			$groupid = $grs[0]['groupid'];
		}
		foreach ($grs as $v){
			$t -> set_var($v);
			if ($v['groupid'] == $groupid) {
				$t -> set_var('gselected',' selected');
			}else{
				$t -> set_var('gselected','');
			}
			$t -> parse('gr','grouprow',true);
		}		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispAppend(){
		$t = new Template('../template/system');
		$t -> set_file('f','userdetail.html');
		$t -> set_block('f','group','g');

		$groupArr = array();
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		}else{
			$updid = $_GET[MODIFY.'id'] + 0 ;
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'user WHERE userid = '.$updid));
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$gs = $this -> dbObj -> GetArray('SELECT groupid FROM '.WEB_ADMIN_TABPOX.'usergroup WHERE userid = '.$updid);
			foreach ($gs as $v)	$groupArr [] = $v['groupid'];
		}
		//当前用户所管理的组
		$umgs = '0';
		$mgs = &$this -> dbObj -> Execute('select groupid from '.WEB_ADMIN_TABPOX.'groupmanager where userid='.$this->getUid());
		while (!$mgs -> EOF) {
			$umgs.= ','.$mgs -> fields['groupid'];
			$mgs -> MoveNext();
		}
		//设置组列表
		$rs = &$this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX.'group where groupid in('.$umgs.')');
		while ($rrs = &$rs -> FetchRow()) {
			$t -> set_var($rrs);
			if (in_array($rrs['groupid'],$groupArr)) {
				$t -> set_var('gchecked',' checked');
			}else{
				$t -> set_var('gchecked','');
			}
			$t -> parse('g','group',true);
		}
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] + 0;
		require_once(WEB_ADMIN_CLASS_PATH.'/power/Power.cls.php');
		$powerObj = new Power(&$this->dbObj,$this->getUid());
		$powerObj -> delUser($delid);
		$this -> quit('删除成功！');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		if($this -> isAppend){
			$info = '增加';
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."user(username,userpass,loginnum,importer)values('".$_POST['username']."','".md5($_POST['password'])."',0,".$this->getUid().")");
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'] + 0;
			if($_POST['password']){
				$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."user SET username='".$_POST['username']."',userpass='".md5($_POST['password'])."' WHERE userid = $id");
			}else{
				$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."user SET username='".$_POST['username']."' WHERE userid = $id");
			}
			if(isset($_POST['groups']))
			$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'usergroup WHERE userid = '.$id);
		}
		if(isset($_POST['groups']))
		foreach ($_POST['groups'] as $v){
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."usergroup(userid,groupid,importer)values($id,$v,".$this->getUid().')');
		}
		$this -> quit($info.'成功！');
	}
	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');location.href='user.php';</script>");
	}
}
$main = new PageUser();
$main -> Main();
?>