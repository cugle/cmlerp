<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/power/Menu.cls.php');

class PageRule extends admin {
	var $menuObj = null;
	var $x = null;
	function PageRule(){
		parent::__construct();
		$this->menuObj = new Menu(&$this->dbObj);
	}

	function disp(){
		$t = new Template('../template/system');
		$t -> set_file('f','rule.html');
		$t -> set_block('f','row','r');
		$t -> set_var('addstr',$this->getAddStr('img'));
		$menuData = $this->menuObj->getMenuTreeArr(0);
		foreach ($menuData as $v){
			$t -> set_var($v);
			$t -> set_var('updstr',$this->getUpdStr($v['importer'],$v['ruleid'],'img'));
			$t -> set_var('delstr',$this->getDelStr($v['importer'],$v['ruleid'],'img'));
			$t -> parse('r','row',true);
		}
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	
	function goDispModify(){
		$this -> forAjax();
		$t = new Template(WEB_ADMIN_TMPPATH.'/system');
		$t -> set_file('f','ruledetail.html');
		$t -> set_block('f','row','r');
		$t -> set_block('f','sub','s');
		$t -> set_block('f','role','o');
		$t -> set_var('ajaxstr',$this -> x -> getJavascript(WEB_ADMIN_HTTPCOMMON.'/js/'));//设置AJAX路径
		
		//设置主菜单
		$pid 	  = 0;
		$updId    = @$_GET['updid']+0;
		$pageDate = $this -> dbObj -> GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'rule WHERE ruleid = '.$updId);
		$menuData = $this -> menuObj -> getMenuTreeArr(0);
		foreach ($menuData as $v){
			$t -> set_var($v);
			if($v['ruleid'] == @$pageDate['parentruleid']){
				$t -> set_var('selected',' selected');
				$pid = $pageDate['parentruleid'];
			}
			else $t -> set_var('selected','');
			$t -> parse('r','row',true);
		}
		
		//设置从菜单
		$menuSubData = $this -> menuObj -> getSubMenuItem($pid);
		$row = count($menuSubData);
		$t -> set_var('subNum',(@$_GET['action']=='add')?($row+1):$row);
		foreach ($menuSubData as $v){
			$t -> set_var($v);
			$t -> parse('s','sub',true);
		}
		
		//设置角色
		$roleRs = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'role');
		while (!$roleRs -> EOF) {
			$roleInfo = $this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'rolerule where roleid='.$roleRs->fields['roleid'].' and ruleid='.$updId);
			if ($roleInfo) {
				$t -> set_var(array(
					'Rchecked' => $roleInfo?' checked':'',
					'Schecked' => $roleInfo['issuperuser']?' checked':'',
					'Bchecked' => $roleInfo['canbrowse']?' checked':'',
					'Achecked' => $roleInfo['canappend']?' checked':'',
					'Mchecked' => $roleInfo['canmodify']?' checked':'',
					'Dchecked' => $roleInfo['candelete']?' checked':'',
					'Ichecked' => $roleInfo['canimport']?' checked':'',
					'Echecked' => $roleInfo['canexport']?' checked':'',
					'Rechecked' => $roleInfo['canrecoil']?' checked':'',
					'Hchecked' => $roleInfo['canaudit']?' checked':'',
				));
			}
			$t -> set_var($roleRs -> fields);
			$t -> parse('o','role',true);
			$roleRs -> MoveNext();
		}
		$roleRs -> Close();
		$t -> set_var($_GET);
		
		if($this -> isModify){
			$pageDate['actionName'] = '修改';
			$pid = $pageDate['parentruleid'];
			$t -> set_var($pageDate);
			$t -> set_var('defaultid',$pageDate['ruleorder']);
			if($pageDate['ruleurl'])$t -> set_var('defDisp','visible');
			else $t -> set_var('defDisp','hidden');
		}else{
			$t -> set_var('actionName','增加');
			$t -> set_var('newrulename','<option selected>【新项目】</option>');
			$t -> set_var('ruleurl','');
			$t -> set_var('rulename','【新项目】');
			$t -> set_var('ruleid','');
			$t -> set_var('defaultid','');
			$t -> set_var('ruleorder','');
			$t -> set_var('defDisp','hidden');
		}
		
		$t -> set_var('path',WEB_ADMIN_HTTPCOMMON);
		$t -> set_var('documentroot',WEB_ADMIN_PHPROOT);
		$t -> parse('out','f');
		$t -> p('out');
	}
	
	function goDispAppend(){
		$this -> goDispModify();
	}
	
	function goModify(){
		$updId = $_POST['ruleid']+0;
		//查找所选的上级菜单的层次
		$layer = $this -> dbObj -> GetOne('select layer from '.WEB_ADMIN_TABPOX.'rule where ruleid='.$_POST['parentruleid']);
		$_POST['layer'] = $layer + 1;
		//得到自己原本的下级菜单
		$subitem = $this -> menuObj -> getMenuItemId($updId);
		if($_POST['parentruleid'] == $_POST['ruleid'] || in_array($_POST['parentruleid'],$subitem)){
			exit('对不起，父级菜单有误');
		}
		//移动菜单
		$this -> menuObj -> moveMenuPoint($updId,$_POST['parentruleid'],$_POST['ruleorder'],$_POST['layer']);
		//增加菜单
		$rs = $this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'rule where ruleid = '.$updId);
		unset($_POST['ruleorder'],$_POST['ruleid']);
		$this -> dbObj -> Execute($this -> dbObj -> GetUpdateSQL($rs,$_POST,get_magic_quotes_gpc()));
		
		//修改所属角色
		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'rolerule WHERE ruleid = '.$updId);
		if($_POST['ruleurl'] && isset($_POST['roleid'])){
			
			foreach ($_POST['roleid'] as $v1){
				$v = $_POST['base_'.$v1];

				$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'rolerule(roleid,ruleid,issuperuser,canbrowse,canappend,canmodify,candelete,canimport, canexport, canrecoil, canaudit ,importer)'.
							"VALUES($v1,$updId,".
							(in_array('S',$v)?1:0).','.
							(in_array('B',$v)?1:0).','.
							(in_array('A',$v)?1:0).','.
							(in_array('M',$v)?1:0).','.
							(in_array('D',$v)?1:0).','.
							(in_array('I',$v)?1:0).','.
							(in_array('E',$v)?1:0).','.
							(in_array('R',$v)?1:0).','.
							(in_array('H',$v)?1:0).','.$this->getUid().')');

			}
			$updRRSs = implode(',',$_POST['roleid']);
			$this -> dbObj -> Execute("update ".WEB_ADMIN_TABPOX."login set updatestate = 1 where userid in(
				select ug.userid from ".WEB_ADMIN_TABPOX."usergroup ug 
				inner join ".WEB_ADMIN_TABPOX."grouprole gr on ug.groupid = gr.groupid where gr.roleid in($updRRSs))");
		}
		$this -> updateFile($updId);
		exit('<script>alert("修改成功");location.href="rule.php";</script>');
	}
	
	function goAppend(){
		$layer = $this -> dbObj -> GetOne('select layer from '.WEB_ADMIN_TABPOX.'rule where ruleid='.$_POST['parentruleid']);
		$_POST['layer']=$layer + 1;
		$_POST['importer']=$this->getUid();
		$moveto = $_POST['ruleorder'];
		$this -> menuObj -> insMesuItem($_POST);
		$indId = $this -> dbObj -> Insert_ID(WEB_ADMIN_TABPOX.'rule','ruleid');
		$this -> menuObj -> moveMenuPoint($indId,$_POST['parentruleid'],$moveto,$_POST['layer']);
		
		//增加所属角色
		if($_POST['ruleurl'] && isset($_POST['roleid'])){
			foreach ($_POST['roleid'] as $v1){
				$v = $_POST['base_'.$v1];
				$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'rolerule(roleid,ruleid,issuperuser,canbrowse,canappend,canmodify,candelete,canimport, canexport, canrecoil, canaudit ,importer)'.
							"VALUES($v1,$indId,".
							(in_array('S',$v)?1:0).','.
							(in_array('B',$v)?1:0).','.
							(in_array('A',$v)?1:0).','.
							(in_array('M',$v)?1:0).','.
							(in_array('D',$v)?1:0).','.
							(in_array('I',$v)?1:0).','.
							(in_array('E',$v)?1:0).','.
							(in_array('R',$v)?1:0).','.
							(in_array('H',$v)?1:0).','.$this->getUid().')');
			}
			$updRRSs = implode(',',$_POST['roleid']);
			$this -> dbObj -> Execute("update ".WEB_ADMIN_TABPOX."login set updatestate = 1 where userid in(
				select ug.userid from ".WEB_ADMIN_TABPOX."usergroup ug 
				inner join ".WEB_ADMIN_TABPOX."grouprole gr on ug.groupid = gr.groupid where gr.roleid in($updRRSs))");
		}
		$this -> updateFile($indId,false);
		exit('<script>alert("增加成功");location.href="rule.php";</script>');
	}
	
	function goDelete(){
		$this -> menuObj -> delMenuItem($_GET['delid']+0);
		$this->disp();
	}
	
	function forAjax(){
		require(WEB_ADMIN_CLASS_PATH.'/xajax/xajax.inc.php');
		$this -> x = new xajax();
		$GLOBALS['ajaxMenu'] = & $this -> menuObj;
		function setSubMenu($id,$order,$addorupd){
			$objResponse = new xajaxResponse();
			$subMenuStr = '';
			$subArr = $GLOBALS['ajaxMenu'] -> getSubMenuItem($id);
			foreach ($subArr as $v){
				$subMenuStr .= 'p.options.add(new Option("'.$v['rulename'].'"));';
			}
			$subMenuStr .= 'p.options.add(new Option( document.getElementById("rulename").value ));';
			$con = count($subArr);
			$subMenuStr .= 'p.selectedIndex = '.$con.';';
			$subMenuStr .= 'p.size = '.($con+1).';';
			$subMenuStr .= 'sruleid = o.value = '.($con+1).';';
			$subMenuStr = 'p.innerHTML = "";'.$subMenuStr;
			$objResponse->addScript($subMenuStr);
			return $objResponse;
		}
		$this -> x -> registerFunction("setSubMenu");
		$this -> x -> processRequests();
	}
	
	function updateFile($id,$upd=true){
		//小图标
		if (substr($_FILES['img']['type']['small'],0,5) == 'image') {
			if($_FILES['img']['size']['small']>0 && $_FILES['img']['size']['small']<50000){//50k
				$exn = substr($_FILES['img']['name']['small'],strpos($_FILES['img']['name']['small'],'.'));
				$name = date('YmdHis').'small'.$exn;
				if (move_uploaded_file($_FILES['img']['tmp_name']['small'],WEB_ADMIN_PHPCOMMON.'/img/ico/'.$name)) {
					if($upd){
						$srcImg = $this -> dbObj -> GetOne('select ruleimg from '.WEB_ADMIN_TABPOX.'rule where ruleid = '.$id);
						if($srcImg && file_exists(WEB_ADMIN_PHPCOMMON.'/img/ico/'.$srcImg)){
							unlink(WEB_ADMIN_PHPCOMMON.'/img/ico/'.$srcImg);
						}
					}
					$this -> dbObj -> Execute('update '.WEB_ADMIN_TABPOX."rule set ruleimg ='ico/$name' where ruleid = ".$id);
				}
			}
		}
		//大图标
		if (substr($_FILES['img']['type']['big'],0,5) == 'image') {
			if($_FILES['img']['size']['big']>0 && $_FILES['img']['size']['big']<100000){//100k
				$exn = substr($_FILES['img']['name']['big'],strpos($_FILES['img']['name']['big'],'.'));
				$name = date('YmdHis').'big'.$exn;
				if (move_uploaded_file($_FILES['img']['tmp_name']['big'],WEB_ADMIN_PHPCOMMON.'/img/ico/'.$name)) {
					if($upd){
						$srcImg = $this -> dbObj -> GetOne('select rulebigimg from '.WEB_ADMIN_TABPOX.'rule where ruleid = '.$id);
						if($srcImg && file_exists(WEB_ADMIN_PHPCOMMON.'/img/ico/'.$srcImg)){
							unlink(WEB_ADMIN_PHPCOMMON.'/img/ico/'.$srcImg);
						}
					}
					$this -> dbObj -> Execute('update '.WEB_ADMIN_TABPOX."rule set rulebigimg ='ico/$name' where ruleid = ".$id);
				}
			}
		}
	}
}
$main = new PageRule();
$main -> Main();
?>