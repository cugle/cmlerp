<?
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
/**
 * @package System
 */
class PageGroup extends admin {
	function PageGroup(){
		parent::__construct();
	}
	function disp(){
		$t = new Template('../template/system');
		$t -> set_file('f','group.html');
		$t -> set_block('f','row','r');
		$t -> set_block('row','role','r2');
		$t -> set_block('row','user','u');
		$t -> set_var('add',$this->getAddStr('img'));
		$rs = &$this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX.'group  where agencyid='.$_SESSION["currentorgan"]);
		while ($rrs = &$rs -> FetchRow()) {
			$t -> set_var($rrs);
			$t -> set_var('r2');
			$inrs = &$this -> dbObj -> Execute("select r.* from ".WEB_ADMIN_TABPOX."grouprole gr inner join ".WEB_ADMIN_TABPOX."role r on gr.roleid=r.roleid where gr.groupid = ".$rrs['groupid']);
			while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> parse('r2','role',true);
			}
			$inrs -> Close();
			$t -> set_var('u');
			$users = $this -> dbObj -> GetArray('select u.username from '.WEB_ADMIN_TABPOX.'groupmanager gm,'.WEB_ADMIN_TABPOX.'user u where gm.userid=u.userid and gm.groupid = '.$rrs['groupid']);
			foreach ($users as $v){
				$t -> set_var('username',$v['username']);
				$t -> parse('u','user',true);
			}
			$t -> set_var('upd',$this->getUpdStr($rrs['importer'],$rrs['groupid'],'img'));
			$t -> set_var('del',$this->getDelStr($rrs['importer'],$rrs['groupid'],'img'));
			$t -> parse('r','row',true);
		}
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] + 0;
		require_once(WEB_ADMIN_CLASS_PATH.'/power/Power.cls.php');
		$powerObj = new Power(&$this -> dbObj,$this -> getUid());
		$powerObj -> delGroup($delid);
		$this->quit('删除成功！');
	}
	function goDispAppend(){
		$t = new Template('../template/system');
		$t -> set_file('f','groupdetail.html');
		$t -> set_block('f','row','r');
		$t -> set_block('row','irow','ir');
		$t -> set_block('f','urow','ur');
		$t -> set_block('urow','uinrow','ui');
		$roleArr = $muserArr = array();
		if($this -> isModify){
			$dispId = $_GET[MODIFY.'id']+0;
			$tmp = $this -> dbObj -> GetArray('select roleid from '.WEB_ADMIN_TABPOX.'grouprole where groupid = '.$dispId);
			foreach ($tmp as $v)$roleArr[]=$v['roleid'];
			$tmp = $this -> dbObj -> GetArray('select userid from '.WEB_ADMIN_TABPOX.'groupmanager where groupid = '.$dispId);
			foreach ($tmp as $v)$muserArr[]=$v['userid'];
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'group WHERE groupid = '.$dispId));
			$t -> set_var('actionName','修改');
		}else{
			$t -> set_var('actionName','增加');
		}
		$t -> set_var($_GET);
		$rs = $this -> dbObj -> GetArray("select * from ".WEB_ADMIN_TABPOX.'role');
		$i = 1;
		$j = count($rs)+1;
		foreach ($rs as $rrs) {
			$t -> set_var($rrs);
			if (in_array($rrs['roleid'],$roleArr)) {
				$t -> set_var('gchecked',' checked');
			}else{
				$t -> set_var('gchecked','');
			}

			$t -> parse('ir','irow',true);
			if($i++ % 5 > 0){
				if ($i == $j)	$t -> parse('r','row',true);
			}else{
				$t -> parse('r','row',true);
				$t -> set_var('ir');
			}
		}
		$rs = $this -> dbObj -> GetArray('select * from '.WEB_ADMIN_TABPOX.'user');
		$i = 1;
		$j = count($rs)+1;
		foreach ($rs as $rrs){
			$t -> set_var($rrs);
			if (in_array($rrs['userid'],$muserArr)) {
				$t -> set_var('uchecked',' checked');
			}else{
				$t -> set_var('uchecked','');
			}

			$t -> parse('ui','uinrow',true);
			if($i++ % 5 > 0){
				if ($i == $j)	$t -> parse('ur','urow',true);
			}else{
				$t -> parse('ur','urow',true);
				$t -> set_var('ui');
			}
		}
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispModify(){
		$this -> goDispAppend();
	}
	function goAppend(){
		$info = '';
		$id = 0;
		if($this->isModify){
			$info = '修改';
			$id = $_POST[MODIFY.'id'] + 0;
			$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'grouprole WHERE groupid = '.$id);
			$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'groupmanager WHERE groupid = '.$id);
			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."group SET groupname = '".$_POST['groupname']."' WHERE groupid = ".$id);
		}else{
			$info = '增加';
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'group(groupname,importer,agencyid)VALUES'."('".$_POST['groupname']."',".$this->getUid().",".$_SESSION["currentorgan"].")");
			$id = $this -> dbObj -> Insert_ID(WEB_ADMIN_TABPOX.'group','groupid');
		}
		if(isset($_POST['roleids']))
		foreach ($_POST['roleids'] as $v){
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'grouprole(roleid,groupid,importer)VALUES('.$v.','.$id.','.$this->getUid().')');
		}
		if (isset($_POST['manages']))
		foreach ($_POST['manages'] as $v){
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."groupmanager(groupid,userid,importer)values($id,$v,".$this->getUid().")");
		}
		$this -> quit($info.'完成！');
	}
	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');location.href='group.php';</script>");
	}
}
$main = new PageGroup();
$main -> Main();
?>