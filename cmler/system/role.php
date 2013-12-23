<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/power/Menu.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/power/DispAttachRule.cls.php');

class PageRole extends admin {
	var $yes = '';
	var $no  = '';
	function PageRole(){
		parent::__construct();
		$this -> yes = '<img src="'.WEB_ADMIN_HTTPPATH.'/common/img/ok.gif" align="middle" />';
		$this -> no  = '<img src="'.WEB_ADMIN_HTTPPATH.'/common/img/no.gif" align="middle" />';
	}
	function disp(){
		$t = new Template('../template/system');
		$t -> set_file('f','role.html');
		$t -> set_block('f','role','r');
		
		$t -> set_block('f','ttr','tr');
		$t -> set_block('ttr','ttd','td');

		$t -> set_block('f','ruleList','u');
		$t -> set_block('ruleList','attachList','a');
		
		 //设置角色列表
		$roleArr = $this -> dbObj -> GetArray('select * from '.WEB_ADMIN_TABPOX.'role');
		if(!isset($_GET['roleid'])){
			$id = $roleArr[0]['roleid'];
		}else{
			$id = $_GET['roleid'] + 0;
		}
		$t -> set_var('defrole',$id);
		$t -> set_var('add',$this->getAddStr('button'));
		$t -> set_var('edit',$this->getUpdStr($this->getUid(),$id,'button'));
		$t -> set_var('del',$this->getDelStr($this->getUid(),$id,'button'));
		foreach ($roleArr as $v){
			$t -> set_var($v);
			$t -> parse('r','role',true);
		}
		$d = new DispAttachRule(&$this->dbObj,$this->getUid());
		
		//设置菜单权

		$m = new Menu(&$this->dbObj);
		$rs = $m -> getMenuTreeArr(0);
		$i = 0;
		foreach ($rs as $v){
			$rolerule = $this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'rolerule where ruleid='.$v['ruleid'].' and roleid='.$id);
			if($rolerule){
				$t -> set_var('superuser',$rolerule['issuperuser']?$this->yes:$this->no);
				$t -> set_var('browsestr',$rolerule['canbrowse']?$this->yes:$this->no);
				$t -> set_var('appendstr',$rolerule['canappend']?$this->yes:$this->no);
				$t -> set_var('modifystr',$rolerule['canmodify']?$this->yes:$this->no);
				$t -> set_var('deletestr',$rolerule['candelete']?$this->yes:$this->no);
				$t -> set_var('importstr',$rolerule['canimport']?$this->yes:$this->no);
				$t -> set_var('exportstr',$rolerule['canexport']?$this->yes:$this->no);
				$t -> set_var('recoilstr',$rolerule['canrecoil']?$this->yes:$this->no);
				$t -> set_var('auditstr',$rolerule['canaudit']?$this->yes:$this->no);
			}else{
				$t -> set_var('superuser','');
				$t -> set_var('browsestr','');
				$t -> set_var('appendstr','');
				$t -> set_var('modifystr','');
				$t -> set_var('deletestr','');
				$t -> set_var('importstr','');
				$t -> set_var('exportstr','');
				$t -> set_var('recoilstr','');
				$t -> set_var('auditstr','');
			}
			$t -> set_var($v);
			$t -> set_var('tdstyle',$i%2==0?'class="tdBgColor"':'class="colBgColor"');
			
			//设置附加权

			$t -> set_var('a');
			$sql = '
				select a.configvalue,o.otherruleid from '.WEB_ADMIN_TABPOX.'otherrule o left join '.WEB_ADMIN_TABPOX.'attachrule a
				on (a.otherruleid=o.otherruleid) and (a.userorgrouporrole=3) and (a.userorgrouporroleid='.$id.') where 
				(o.ruleid='.$v['ruleid'].') and (o.isrule=1)
			';
			$inrs = $this -> dbObj -> GetArray($sql);
			foreach ($inrs as $inv){
				$t -> set_var($d -> dispValue($inv['otherruleid'],$inv['configvalue'],null,2));
				$t -> set_var('tdstyle',$i%2==0?'class="tdBgColor"':'class="colBgColor"');
				$t -> parse('a','attachList',true);
			}
			$i++;
			$t -> parse('u','ruleList',true);
		}
		
		//设置全局权

		$sql = '
				select a.configvalue,o.otherruleid from '.WEB_ADMIN_TABPOX.'otherrule o left join '.WEB_ADMIN_TABPOX.'attachrule a 
				on (a.otherruleid=o.otherruleid) and (a.userorgrouporrole=3) and (a.userorgrouporroleid='.$id.') where 
				(o.ruleid=0 or o.ruleid is null) and (o.isrule=1)
		';
		$rs = $this -> dbObj -> Execute($sql);
		$n = 1;
		$l =  $rs -> NumRows()+1;
		while ($rrs = $rs ->FetchRow()) {
			$t -> set_var($d -> dispValue($rrs['otherruleid'],$rrs['configvalue'],null,5));
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
		
		//设置所属工作组
		$gss = '';
		$gs = $this -> dbObj -> GetArray('select groupname from '.WEB_ADMIN_TABPOX.'group g,'.WEB_ADMIN_TABPOX.'grouprole gr where g.groupid = gr.groupid and gr.roleid='.$id);
		foreach ($gs as $v){
			if($gss == '') $gss  = $v['groupname'];
			else 		   $gss .= ' , '.$v['groupname'];
		}
		$t -> set_var('groups',$gss);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] + 0;
		$powerObj = new Power(&$this -> dbObj,$this->getUid());
		$powerObj -> delRole($delid);
		$this -> quit('删除成功！');
	}
	function goDispAppend(){
		$this -> goDispModify();
	}
	function goDispModify(){
		$m = new Menu(&$this->dbObj);
		$p = new Power(&$this->dbObj,$this->getUid());
		$d = new DispAttachRule(&$this->dbObj,$this->getUid());
		$t = new Template('../template/system');
		$t -> set_file('f','roledetail.html');
		//设置全局权限块
		$t -> set_block('f','ttr','tr');
		$t -> set_block('ttr','ttd','td');
		//设置菜单权限块
		$t -> set_block('f','ruleList','u');
		$t -> set_block('ruleList','attachList','a');
		//设置组块
		$t -> set_block('f','gtr','gr');
		$t -> set_block('gtr','gtd','gd');
		
		//默认值
		$rudf = $gdf = array();
		if($this->isAppend){
			$t -> set_var('actionName','增加');
			$rudf = array('base'=>array(),'attach'=>array());
		}else{
			$updid = $_GET[MODIFY.'id'] + 0;
			$rudf  = $p -> getRoleRule($updid);
			$t -> set_var('actionName','修改');
			$t -> set_var($this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'role where roleid = '.$updid));
			$gdfr = $this -> dbObj -> GetArray('select groupid from '.WEB_ADMIN_TABPOX.'grouprole where roleid = '.$updid);
			foreach ($gdfr as $v) $gdf[] = $v['groupid'];
		}
		$t -> set_var($_GET);
		//设置全局菜单
		$grus = $this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'otherrule where isrule=1 and (ruleid=0 or ruleid is null)');
		$n = 1;
		$l =  $grus -> NumRows()+1;
		while ($rs = &$grus -> FetchRow()) {
			$gr_df = isset($rudf['attach'][0][$rs['configvarname']])?$rudf['attach'][0][$rs['configvarname']]:null;
			$t -> set_var($d -> disp($rs,$gr_df,"</td><td width='25%'>",4,"</td></tr><tr><td width='25%'>"));
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
		
		//设置菜单权限
		$rus = $m -> getMenuTreeArr(0);
		foreach ($rus as $v){
			if($v['ruleurl'])$t -> set_var('display','');
			else $t -> set_var('display','none');
			if (array_key_exists($v['ruleid'],$rudf['base'])){
				$t -> set_var('rschecked',$rudf['base'][$v['ruleid']][0]?' checked':'');
				$t -> set_var('rbchecked',$rudf['base'][$v['ruleid']][1]?' checked':'');
				$t -> set_var('rachecked',$rudf['base'][$v['ruleid']][2]?' checked':'');
				$t -> set_var('rmchecked',$rudf['base'][$v['ruleid']][3]?' checked':'');
				$t -> set_var('rdchecked',$rudf['base'][$v['ruleid']][4]?' checked':'');
				$t -> set_var('richecked',$rudf['base'][$v['ruleid']][5]?' checked':'');
				$t -> set_var('rechecked',$rudf['base'][$v['ruleid']][6]?' checked':'');
				$t -> set_var('rrchecked',$rudf['base'][$v['ruleid']][7]?' checked':'');
				$t -> set_var('rhchecked',$rudf['base'][$v['ruleid']][8]?' checked':'');
			
			}else{
				$t -> set_var('rschecked','');
				$t -> set_var('rbchecked','');
				$t -> set_var('rachecked','');
				$t -> set_var('rmchecked','');
				$t -> set_var('rdchecked','');		
				$t -> set_var('richecked','');		
				$t -> set_var('rechecked','');	
				$t -> set_var('rrchecked','');		
				$t -> set_var('rhchecked','');	
			}
			$t -> set_var($v);
			$t -> set_var('a');
			//设置菜单的附加值
			$ar = $this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'otherrule where isrule=1 and ruleid='.$v['ruleid']);
			while ($arr = &$ar -> FetchRow()) {
				$ar_df = isset($rudf['attach'][$v['ruleid']][$arr['configvarname']])?$rudf['attach'][$v['ruleid']][$arr['configvarname']]:null;
				$t -> set_var($d -> disp($arr,$ar_df,"</td><td width='50%'>",2,"</td></tr><tr><td width='50%'>"));
				$t -> parse('a','attachList',true);
			}
			$t -> parse('u','ruleList',true);
		}
		
		//设置组列表
		$gs = $this -> dbObj -> GetArray('select * from '.WEB_ADMIN_TABPOX.'group');
		$j = count($gs)+1;
		$i = 1;
		foreach ($gs as $v){
			$t -> set_var($v);
			if(in_array($v['groupid'],$gdf)){
				$t -> set_var('gchecked',' checked'); 
			}else{
				$t -> set_var('gchecked',''); 
			}
			$t -> parse('gd','gtd',true);
			
			if($i++ % 6 > 0){
				if ($i == $j)	$t -> parse('gr','gtr',true);
			}else{
				$t -> parse('gr','gtr',true);
				$t -> set_var('gd');
			}
		}
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goAppend(){
		$p = new Power(&$this->dbObj,$this->getUid());
		$id = 0;
		$info = '';
		if($this->isModify){
			$info = '修改';
			$id = $_POST[MODIFY.'id'] + 0;
			$this -> dbObj -> Execute('update '.WEB_ADMIN_TABPOX."role set rolename='".$_POST['rolename']."' where roleid = ".$id);
			$this -> dbObj -> Execute('delete from '.WEB_ADMIN_TABPOX.'rolerule where roleid = '.$id);
			$this -> dbObj -> Execute('delete from '.WEB_ADMIN_TABPOX.'grouprole where roleid = '.$id);
			$this -> dbObj -> Execute('delete from '.WEB_ADMIN_TABPOX.'attachrule where userorgrouporrole = 3 and userorgrouporroleid = '.$id);
		}else{
			$info = '增加';
			$this -> dbObj -> Execute('insert into '.WEB_ADMIN_TABPOX."role(rolename,importer)values('".$_POST['rolename']."',".$this->getUid().")");
			$id = $this -> dbObj -> Insert_ID();
		}
		//角色权
		if(isset($_POST['rules']))
		foreach ($_POST['rules'] as $k => $v){
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'rolerule(roleid,ruleid,issuperuser,canbrowse,canappend,canmodify,candelete,canimport, canexport, canrecoil, canaudit ,importer)'.
				"VALUES($id,$k.,".
				(array_key_exists('S',$v)?1:0).','.
				(array_key_exists('B',$v)?1:0).','.
				(array_key_exists('A',$v)?1:0).','.
				(array_key_exists('M',$v)?1:0).','.
				(array_key_exists('D',$v)?1:0).','.
				(array_key_exists('I',$v)?1:0).','.
				(array_key_exists('E',$v)?1:0).','.
				(array_key_exists('R',$v)?1:0).','.
				(array_key_exists('H',$v)?1:0).','.$this->getUid().')');
 
		}
		//附加权
		if(isset($_POST['attachs']))
		foreach ($_POST['attachs'] as $k=>$v){
			foreach ($v as $ink => $inv){
				$currentStr = $inv;
				if(is_array($inv)){
					sort($inv);
					reset($inv);
					$currentStr = implode('#',$inv);
				}
				$df = $this -> dbObj -> GetRow('select configdefault from '.WEB_ADMIN_TABPOX.'otherrule where otherruleid = '.$ink);
				$p -> parseSqlData(&$df);
				//默认值
				$sourceStr = explode('#',$df['configdefault']);
				sort($sourceStr);
				$sourceStr = implode('#',$sourceStr);
				if($sourceStr != $currentStr){
					$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'attachrule(userorgrouporrole,userorgrouporroleid,addordel,ruleid,otherruleid,configvalue,importer)'.
						"values(3,$id,1,$k,$ink,'$currentStr',".$this->getUid().')'
					);
				}
			}
		}
		//所在组
		if(isset($_POST['groups']))
		foreach ($_POST['groups'] as $v){
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."grouprole(groupid,roleid,importer)VALUES($v,$id,".$this->getUid().')');
		}
		if($this->isModify){
			$this -> dbObj -> Execute("update ".WEB_ADMIN_TABPOX."login set updatestate = 1 where userid in(
				select ug.userid from ".WEB_ADMIN_TABPOX."usergroup ug 
				inner join ".WEB_ADMIN_TABPOX."grouprole gr on ug.groupid = gr.groupid where gr.roleid = $id)");
		}
		$this -> quit($info.'完成！');
	}
	function goModify(){
		$this->goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');location.href='role.php';</script>");
	}
}
$main = new PageRole();
$main ->Main();
?>