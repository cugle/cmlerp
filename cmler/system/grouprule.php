<?
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/power/Menu.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/power/DispAttachRule.cls.php');
/**
 * @package System
 */
class PageGrouprule extends admin {
	var $menuObj = null;
	var $powerObj = null;
	var $_yesStr = null;
	var $_noStr = null;
	function PageGrouprule(){
		$this->__construct();
	}
	function __construct(){
		parent::__construct();
		$this -> menuObj = new Menu(&$this -> dbObj);
		$this -> powerObj = new Power(&$this->dbObj,$this->getUid());
		if($this -> getAttach('DispRule')){
			$this -> _yesStr = '<img src="'.WEB_ADMIN_HTTPPATH.'/common/img/ok.gif" align="middle" />';
			$this -> _noStr  = '<img src="'.WEB_ADMIN_HTTPPATH.'/common/img/no.gif" align="middle" />';
		}else{
			$this -> _yesStr = $this -> _noStr = '<img src="'.WEB_ADMIN_HTTPPATH.'/common/img/what.gif" align="middle" />';
		}
	}
	function goModify(){
		$curid = $this -> getUid();
		$updid = $_POST['groupid'] + 0;
		
		//操作者的权限
		$currentRule = $this -> powerObj -> getUserRule($curid);
		
		//被操作者的权限
		$sourceRule  = $this -> powerObj -> getGroupRule($updid);
		
		//权限基本数组
		$ruleArr = array('S','B','A','M','D','I','E','R','H');

		if (isset($_POST['rules'])){
			//增加
			//以提交权限为基础，所有不在操作对象权限内的提交权限被增加
			foreach ($_POST['rules'] as $k => $v){
				if (!array_key_exists($k,$sourceRule['base'])) {
					$addStr = '000000000';
					foreach ($ruleArr as $ink=>$inv){
						$addStr[$ink] = array_key_exists($inv,$v)?'1':'0';
					}
					$this -> powerObj -> addRuleTo(2,$updid,$addStr,$k);
				}else{
					//修改
					$addStr = $delStr = '000000000';
					foreach ($ruleArr as $ink=>$inv){
						$addStr[$ink] = (array_key_exists($inv,$v) && $sourceRule['base'][$k][$ink]=='0')?'1':'0';
						$delStr[$ink] = (!array_key_exists($inv,$v) && $sourceRule['base'][$k][$ink]=='1')?'1':'0';
					}
					if($addStr!='000000000'){
						$this -> powerObj -> addRuleTo(2,$updid,$addStr,$k);
					}
					if($delStr!='000000000'){
						$this -> powerObj -> delRuleFor(2,$updid,$delStr,$k);
					}
				}
			}
		}
		//删除
		//以操作者权限为基础，所有在操作者权限内的，并且在操作对象的权限内，并且不在提交的权限内的权限被删除
		foreach ($currentRule['base'] as $k => $v){
			if(array_key_exists($k,$sourceRule['base']) && !isset($_POST['rules'][$k])){
				$delStr = '000000000';
				for ($i=0; $i<5; $i++){
					$delStr[$i] = ($sourceRule['base'][$k][$i])?'1':'0';
				}
				if($delStr!='000000000'){
					$this -> powerObj -> delRuleFor(2,$updid,$delStr,$k);
				}
			}
		}

		//记录ID
		$otheridArr = array();
		$otheridRs  = $this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'otherrule where isrule = 1');
		while ($tmRrs = $otheridRs -> FetchRow()) {
			$otheridArr[$tmRrs['otherruleid']] = $tmRrs['configvarname'];
		}
		$otheridRs -> Close();
		
		if(isset($_POST['attachs'])){
			foreach ($_POST['attachs'] as $k => $v){
				if (!array_key_exists($k,$sourceRule['attach'])){
					foreach ($v as $ink => $inv){
						$addStr = implode('#',$inv);
						$this -> powerObj -> addRuleTo(2,$updid,$addStr,$k,$ink);	//增加
					}
				}else{
					//修改
					foreach ($v as $ink => $inv){
						$a_name = $otheridArr[$ink];
						if(!array_key_exists($a_name,$sourceRule['attach'][$k])){
							$addStr = implode('#',$inv);
							$this -> powerObj -> addRuleTo(2,$updid,$addStr,$k,$ink);	//增加
						}else{
							$addStr = $delStr = array();
							$srcArr = $sourceRule['attach'][$k][$a_name];
							if(!is_array($srcArr))	$srcArr = array($srcArr);
							//循环提交数据
							foreach ($inv as $in_v){
								if(!in_array($in_v,$srcArr)){
									$addStr[] = $in_v;
								}
							}
							//循环原数据
							foreach ($srcArr as $in_v){
								if(!in_array($in_v,$inv)){
									$delStr[] = $in_v;
								}
							}
							$this -> powerObj -> addRuleTo(2,$updid,implode('#',$addStr),$k,$ink);	//增加
							$this -> powerObj -> delRuleFor(2,$updid,implode('#',$delStr),$k,$ink);	//删除
						}
					}
				}
			}
		}
		//删除
		//以操作者权限为基础，所有在操作者权限内的，并且在操作对象的权限内，并且不在提交的权限内的权限被删除
		foreach ($currentRule['attach'] as $k => $v){
			if(array_key_exists($k,$sourceRule['attach']) && !isset($_POST['attachs'][$k])){
				foreach ($sourceRule['attach'][$k] as $ink => $inv){
					if(!is_array($inv))$inv=array($inv);
					$delStr = implode('#',$inv);
					$this -> powerObj -> delRuleFor(2,$updid,$delStr,$k,$ink);	//增加
				}
			}
		}
		//权限已更新
		$this -> dbObj -> Execute("update ".WEB_ADMIN_TABPOX."login set updatestate = 1 where userid in(
									select userid from ".WEB_ADMIN_TABPOX."usergroup where groupid = $updid)");
		exit("<script>alert('修改成功！');location.href='grouprule.php?groupid={$_POST['groupid']}';</script>");
	}
	function disp(){
		$t = new Template('../template/system');
		$t -> set_file('f','grouprule.html');
		$t -> set_block('f','grouprow','gr');
		$t -> set_block('f','row','r');
		$t -> set_block('row','attachList','a');

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

		//当前用户所管理的组的所有权限
		$d = new DispAttachRule(&$this->dbObj,$this->getUid());
		$currRules   = $this -> _currentUserRule();					//当前登录用户的权限
		$ruleArr     = $this -> powerObj -> getGroupRule($groupid);	//选择的工作组权限
		$userRuleArr = $this -> menuObj -> getMenuTreeArr(0);	 	//所有菜单
		if ($this->getModify()) $t -> set_var('bdisabled','');
		else					$t -> set_var('bdisabled',' disabled');
		
		$stateArr = array('s','b','a','m','d','i','e','r','h');	//各种状态
		foreach ($userRuleArr as $v){
			$t -> set_var('a');
			//是否显示复选框
			if(!$v['ruleurl']){
				$t -> set_var($v);
				$t -> set_var('display','none');
				foreach ($stateArr as $sv){
					$t -> set_var('r'.$sv.'disabled',' disabled');
					$t -> set_var('r'.$sv.'checked','');
					$t -> set_var('r'.$sv.'type','hidden');
					$t -> set_var('r'.$sv.'state','');
				}
				$t -> set_var('t');
				$t -> parse('r','row',true);
				continue;
			}else{
				$t -> set_var('display','');
			}

			//设置基本权限
			
			if (array_key_exists($v['ruleid'],$currRules['base'])) {
				foreach ($stateArr as $sk => $sv){
					$operater = false;
					if(isset($currRules['base'][$v['ruleid']][$sk]) && $currRules['base'][$v['ruleid']][$sk]){	//操作者是否有权
						$t -> set_var('r'.$sv.'type','checkbox');
						$operater = true;
					}else{
						$t -> set_var('r'.$sv.'type','hidden');
					}

					if (isset($ruleArr['base'][$v['ruleid']][$sk]) && $ruleArr['base'][$v['ruleid']][$sk]) {	//选中的用户是否有权
						$t -> set_var('r'.$sv.'state',$operater?'':$this -> _yesStr);
						$t -> set_var('r'.$sv.'checked',$operater?' checked':'');
						$t -> set_var('r'.$sv.'disabled','');
					}else{
						$t -> set_var('r'.$sv.'state',$operater?'':$this -> _noStr);
						$t -> set_var('r'.$sv.'checked','');
						$t -> set_var('r'.$sv.'disabled',$operater?'':' disabled');
					}

				}unset($sk,$sv);
			}else{
				foreach ($stateArr as $sk => $sv){
					if (isset($ruleArr['base'][$v['ruleid']][$sk]) && $ruleArr['base'][$v['ruleid']][$sk]) {
						$t -> set_var('r'.$sv.'disabled','');
						$t -> set_var('r'.$sv.'type','hidden');
						$t -> set_var('r'.$sv.'state',$this -> _yesStr);
						$t -> set_var('r'.$sv.'checked','');
					}else{
						$t -> set_var('r'.$sv.'disabled',' disabled');
						$t -> set_var('r'.$sv.'type','hidden');
						$t -> set_var('r'.$sv.'state',$this -> _noStr);
						$t -> set_var('r'.$sv.'checked','');
					}
				}
			}
			$t -> set_var($v);

			//设置附加权限
			$rs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'otherrule where isrule=1 and ruleid='.$v['ruleid']);
			while (!$rs->EOF) {
				$t -> set_var($d -> dispRule($rs->fields['otherruleid'],
				@$currRules['attach'][$rs->fields['ruleid']][$rs->fields['configvarname']],
				@$ruleArr['attach'][$rs->fields['ruleid']][$rs->fields['configvarname']],
				$this->getAttach('DispRule')>0));
				$rs->MoveNext();
				$t -> parse('a','attachList',true);
			}
			$rs->Close();
			$t -> parse('r','row',true);
		}

		//设置全局附加值
		$t -> set_block('f','ttr','tr');
		$t -> set_block('ttr','ttd','td');
		$rs = $this -> dbObj -> GetArray('SELECT * FROM '.WEB_ADMIN_TABPOX.'otherrule WHERE isrule = 1 AND (ruleid = 0 OR ruleid IS NULL)');
		$n = 1;
		$l =  count($rs)+1;
		foreach ($rs as $v){
			$this -> powerObj -> parseSqlData(&$v);
			$t -> set_var( $d -> dispRule($v['otherruleid'],@$currRules['attach'][$v['ruleid']][$v['configvarname']],@$ruleArr['attach'][$v['ruleid']][$v['configvarname']],$this -> getAttach('DispRule')>0));
			$t -> parse('td','ttd',true);
			if($n++ % 2 > 0){
				if ($n == $l){
					if((($l-1) % 2)==1){
						$t->set_var(array('name'=>'','value'=>''));
						$t -> parse('td','ttd',true);
					}
					$t -> parse('tr','ttr',true);
				}
			}else{
				$t -> parse('tr','ttr',true);
				$t -> set_var('td');
			}
		}

		$t -> set_var($_GET);
		#--设置菜单模块完成

		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	/**
	 * 返回当前用户的权限
	 */
	function _currentUserRule(){
		$x = $this -> getAttach('Extends');	//是否可以向下赋权
		$ra = $this -> powerObj -> getUserRule($this->getUid()); // $ra['base']= array['ruleid']
		if($x){
			return $ra;
		}else{
			//这是用户附加的权限
			$rs = $this -> dbObj -> GetArray('SELECT ruleid,otherruleid,baserule,configvalue FROM '.WEB_ADMIN_TABPOX.'attachrule WHERE (userorgrouporrole = 1) AND (addordel = 1) AND (userorgrouporroleid ='.$this->getUid().')');
			if ($rs){
				foreach ($rs as $v){
					if (array_key_exists($v['ruleid'],$ra['base'])){
						for ($i=0; $i<5; $i++){
							$ra['base'][$v['ruleid']][$i] = intval($v['baserule'][$i])?'0':$ra['base'][$v['ruleid']][$i];
						}
						if (intval($ra['base'][$v['ruleid']]) == 0) unset($ra['base'][$v['ruleid']]);
					}
					if ($v['otherruleid']) {
						$othName = $this -> dbObj -> GetRow('SELECT configvarname,configdefault FROM '.WEB_ADMIN_TABPOX.'otherrule WHERE otherruleid = '.$v['otherruleid']);
						$this -> powerObj -> parseSqlData(&$othName);
						$othName['configdefault'] = explode('#',$othName['configdefault']);	//附加值的默认值
						$userValue = explode('#',$v['configvalue']);						//附加值的用户配置
						foreach ($userValue as $inv){
							$tmpArr = & $ra['attach'][$v['ruleid']][$othName['configvarname']];
							if(!is_array($tmpArr)) $tmpArr = array($tmpArr);
							$i = array_search($inv,$tmpArr);
							if($i!==false)	 unset($tmpArr[$i]);
							if(!$tmpArr) 	 unset($tmpArr);
						}
					}
				}
			}
		}
		return $ra;
	}
}
$main = new PageGrouprule();
$main -> Main();
?>