<?
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/power/Menu.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/power/DispAttachRule.cls.php');
/**
 * @package System
 */
class PageAttach extends admin {
	function PageAttach(){
		$this->__construct();
	}
	function __construct(){
		parent::__construct();
	}
	function disp(){
		$dsp = new DispAttachRule(&$this->dbObj,$this->getUid());
		$t = new Template(WEB_ADMIN_TMPPATH.'/system/');
		$t -> set_file('f','attach.html');
		$t -> set_block('f','row','r');
		$t -> set_var('add',$this->getAddStr('img'));
		$where = '';
		if(isset($_GET['fieldname']) && $_GET['fieldname']<>''){
			$where = ' where o.isrule = '.$_GET['fieldname'];
		}
		$rs = $this->dbObj->Execute('select o.*,r.rulename from '.WEB_ADMIN_TABPOX.'otherrule o left outer join '.WEB_ADMIN_TABPOX.'rule r on o.ruleid=r.ruleid'.$where);
		while ($v = $rs -> FetchRow()) {
			$t -> set_var($v);
			$t -> set_var($dsp -> disp($v,null,"</td><td width='50%'>",2,"</td></tr><tr><td width='50%'>"));
			$t -> set_var('type',$v['issystemvar']?'系统':'用户');
			$t -> set_var('field',$v['ruleid']?'当前页':'全局');
			$t -> set_var('edit',$this->getUpdStr($v['importer'],$v['otherruleid'],'img'));
			$t -> set_var('del', $this->getDelStr($v['importer'],$v['otherruleid'],'img'));
			$t -> parse('r','row',true);
		}
		$t -> set_var($_GET);
		$t -> set_var('path',WEB_ADMIN_HTTPCOMMON);
		$t -> parse('o','f');
		$t -> p('o');
	}
	function goDispAppend(){
		$t = new Template(WEB_ADMIN_TMPPATH.'/system/');
		$t -> set_file('f','attachdetail.html');
		$t -> set_block('f','row','r');
		$defArr = array();
		if ($this -> isAppend) {
			$t -> set_var('uchecked',' checked');
			$t -> set_var('pchecked',' checked');
			$t -> set_var('actionName','增加');
		}else{
			$updid = $_GET[MODIFY.'id'] + 0;
			$defArr = $this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'otherrule where otherruleid = '.$updid);
			$defArr['configvalue'] = str_replace('#',"\r\n",$defArr['configvalue']);
			if('[sql]' == substr($defArr['configname'],0,5)){
				$defArr['configname'] = substr($defArr['configname'],5);
				$t -> set_var('nSqlChk',' checked');
			}else{
				$t -> set_var('nSqlChk','');
			}
			if('[sql]' == substr($defArr['configdefault'],0,5)){
				$defArr['configdefault'] = substr($defArr['configdefault'],5);
				$t -> set_var('dSqlChk',' checked');
			}else{
				$t -> set_var('dSqlChk','');
			}
			if('[sql]' == substr($defArr['configvalue'],0,5)){
				$defArr['configvalue'] = substr($defArr['configvalue'],5);
				$t -> set_var('vSqlChk',' checked');
			}else{
				$t -> set_var('vSqlChk','');
			}			
			$t -> set_var($defArr);
			$t -> set_var('rchecked',$defArr['isrule']?' checked':'');
			$t -> set_var('pchecked',$defArr['isrule']?'':' checked');
			$t -> set_var('schecked',$defArr['issystemvar']?' checked':'');
			$t -> set_var('uchecked',$defArr['issystemvar']?'':' checked');
			$t -> set_var(array('rselected'=>'','cselected'=>'','tselected'=>'','sselected'=>''));
			$t -> set_var(strtolower($defArr['configtype'][0]).'selected',' selected');
			$t -> set_var('actionName','修改');
		}
		$m = new Menu(&$this -> dbObj);
		$menuData = $m -> getMenuTreeArr(0);
		foreach($menuData as $v){
			$t -> set_var($v);
			if ($v['ruleurl']){
				$t -> set_var('option','option');
			}else{
				$t -> set_var('ruleid',-1);
				$t -> set_var('option','optgroup');
			}
			if(isset($defArr['ruleid']) && $defArr['ruleid'] == $v['ruleid']){
				$t -> set_var('ruselect',' selected');
			}else{
				$t -> set_var('ruselect','');
			}
			$t -> parse('r','row',true);
		}
		$t -> set_var($_GET);
		$t -> set_var('path',WEB_ADMIN_HTTPCOMMON);
		$t -> parse('o','f');
		$t -> p('o');
	}
	function goDispModify(){
		$this -> goDispAppend();
	}
	function goAppend(){
		$rs = & $this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'otherrule where otherruleid = -1');
		$_POST['importer'] = $this -> getUid();
		$_POST['configvalue'] = str_replace("\r\n",'#',$_POST['configvalue']);
		$this -> dbObj -> Execute( $this -> dbObj -> GetInsertSQL($rs,$_POST,get_magic_quotes_gpc()));
		$this -> quit('增加成功！');
	}
	function goModify(){
		$updid = $_POST[MODIFY.'id'] + 0;
		$_POST['configvalue'] = str_replace("\r\n",'#',$_POST['configvalue']);
		$rs = & $this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'otherrule where otherruleid = '.$updid);
		$this -> dbObj -> Execute( $this -> dbObj -> GetUpdateSQL($rs,$_POST,get_magic_quotes_gpc()));
		if($_POST['isrule']){ //更新权限
			//用户
			$this -> dbObj -> Execute("
				update ".WEB_ADMIN_TABPOX."login set updatestate = 1 where userid in(
					select userorgrouporroleid from ".WEB_ADMIN_TABPOX."attachrule where otherruleid = $updid and userorgrouporrole = 1
				)");
			//组
			$this -> dbObj -> Execute("
				update ".WEB_ADMIN_TABPOX."login set updatestate = 1 where userid in(
					select userid from ".WEB_ADMIN_TABPOX."usergroup ug 
					inner join ".WEB_ADMIN_TABPOX."attachrule ar on ug.groupid = ar.userorgrouporroleid and ar.userorgrouporrole = 2
					where ar.otherruleid = $updid
				)");
			//用色
			$this -> dbObj -> Execute("
				update ".WEB_ADMIN_TABPOX."login set updatestate = 1 where userid in(
					select userid from ".WEB_ADMIN_TABPOX."usergroup ug 
					inner join ".WEB_ADMIN_TABPOX."grouprole gr on ug.groupid = gr.groupid
					inner join ".WEB_ADMIN_TABPOX."attachrule ar on gr.roleid = ar.userorgrouporroleid and ar.userorgrouporrole = 3
					where ar.otherruleid = $updid
				)");
		}
		$this -> quit('修改成功！');
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] + 0;
		$this -> dbObj -> Execute('delete from '.WEB_ADMIN_TABPOX.'attachrule where otherruleid = '.$delid);
		$this -> dbObj -> Execute('delete from '.WEB_ADMIN_TABPOX.'otherrule where otherruleid = '.$delid);
		$this -> quit('删除成功！');
	}
	function quit($info){
		$this -> __destruct();
		exit("<script>alert('$info');location.href='attach.php';</script>");
	}
}
$main = new PageAttach();
$main -> Main();
?>