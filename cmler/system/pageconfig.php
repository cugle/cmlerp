<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/power/Menu.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/power/DispAttachRule.cls.php');

class PagePageconfig extends admin {
	function PagePageconfig(){
		parent::__construct(0);
	}
	function goModify(){
		$power = new Power(&$this->dbObj,$this->getUid());
		$p = $_POST['updid'] + 0;
		$sql = "SELECT otherruleid,configdefault FROM ".WEB_ADMIN_TABPOX."otherrule WHERE (isrule = 0) AND (issystemvar = 0) AND (ruleid = $p)";
		$rs = $this -> dbObj -> GetArray($sql);
		foreach ($rs as $v){
			$power -> parseSqlData(&$v);
			
			$def = explode('#',$v['configdefault']);
			sort($def);
			$def = implode('#',$def);
			
			$posData = & $_POST['attachs'][$p];
			if(array_key_exists($v['otherruleid'],$posData)){
				$tposd = $posData[$v['otherruleid']];
				if (is_array($tposd)) {
					sort($tposd);
					$val = implode('#',$tposd);
				}else{
					$val = $tposd;
				}
				$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'config WHERE (userid = '.$this->getUid().') AND (otherruleid = '.$v['otherruleid'].')');
				
				if ($def != $val) {
					$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'config(userid,otherruleid,configvalue,importer)VALUES('.$this->getUid().','.$v['otherruleid'].",'".$val."',".$this->getUid().')');
				}
			}
		}
		exit('<script>alert("设置成功！");history.go(-1);</script>');
	}
	function disp(){
		$t = new Template(WEB_ADMIN_TMPPATH);
		$dispObj = new DispAttachRule(&$this->dbObj,$this->getUid());
		$t -> set_file('f','pageconfig.html');
		$t -> set_block('f','row','r');
		
		$p = $_GET['pageid'] + 0 ;
		$sql = "
			SELECT c.configvalue AS userdefalut,o.* FROM ".WEB_ADMIN_TABPOX."otherrule o 
				LEFT OUTER JOIN ".WEB_ADMIN_TABPOX."config c ON ( (o.otherruleid = c.otherruleid) AND (c.userid = ".$this->getUid().") ) 
			WHERE (o.isrule = 0) AND (o.issystemvar = 0) AND (o.ruleid = $p)
		";
		$rs = $this -> dbObj -> GetArray($sql);
		
		foreach ($rs as $v) {
			$t -> set_var($dispObj -> disp($v,$v['userdefalut']));
			$t -> parse('r','row',true);
		}

		$r = '';
		$m = new Menu(&$this->dbObj);
		$s = $m -> getRelating($p);
		for ($i=0;$i<count($s);$i++){
			if ($i==0) 	$r  = $s[$i]['rulename'];
			else 		$r .= ' > '.$s[$i]['rulename'];
		}
		$t -> set_var('updid',$p);
		$t -> set_var('address',$r);
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
}

$main = new PagePageconfig();
$main -> Main();
?>