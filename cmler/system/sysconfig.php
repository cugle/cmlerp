<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class PageSysconfig extends admin {
	function PageSysconfig(){
		parent::__construct();
	}
	function goModify(){
		$rs = $this -> dbObj -> GetArray('select * from '.WEB_ADMIN_TABPOX.'otherrule where issystemvar = 1');
		foreach ($rs as $v){
			if(array_key_exists($v['otherruleid'],$_POST)){
				if (is_array($_POST[$v['otherruleid']])) {
					$val = implode('#',$_POST[$v['otherruleid']]);
				}else{
					$val = $_POST[$v['otherruleid']];
				}
				if($val != $v['configdefault'])
				$this -> dbObj -> Execute('update '.WEB_ADMIN_TABPOX."otherrule set configdefault='$val' where otherruleid=".$v['otherruleid']);
			}
		}
		exit("<script>alert('修改成功');location.href='sysconfig.php';</script>");
	}
	function disp(){
		$t = new Template('../template/system');
		$t -> set_file('f','sysconfig.html');
		$t -> set_block('f','row','r');
		
		if($this -> getModify()){
			$t -> set_var('form','<form name="form1" method="post" action="sysconfig.php">');
			$t -> set_var('endform','</form>');
		}else{
			$t -> set_var('disabled',' disabled');
		}
		
		$rs = $this -> dbObj -> GetArray('select * from '.WEB_ADMIN_TABPOX.'otherrule where issystemvar = 1');
		foreach ($rs as $v) {
			$t -> set_var('configname',$v['configname']);
			//如果有的话，显示配置值
			$sval = $this -> dbObj -> GetOne('select configvalue from '.WEB_ADMIN_TABPOX.'config where userid=0 and otherruleid='.$v['otherruleid']);
			if ($sval===false)	$sval = $v['configdefault'];

			$configvalue = '';
			switch ($v['configtype']) {
				case 'text':
					$configvalue = '<input name="'.$v['otherruleid'].'" id="'.$v['otherruleid'].'" type="text" value="'.$sval.'" maxlength="'.$v['maxlength'].'">';
				break;
				case 'radio':
					$content = split('#',$v['configvalue']);
					foreach ($content as $inv){
						$val = split('=',$inv);
						$configvalue .= "<input type='radio' name='{$v['otherruleid']}' id='{$v['otherruleid']}' value='{$val[1]}'".(($val[1]==$sval)?(' checked'):('')).">{$val[0]}";
					}
				break;
				case 'checkbox':
					$content = split('#',$v['configvalue']);
					$dfvs    = split('#',$sval);//默认值
					foreach ($content as $inv){
						$val = split('=',$inv);
						$configvalue .= "<input type='checkbox' name='{$v['otherruleid']}[]' id='{$v['otherruleid']}' value='{$val[1]}'".((in_array($val[1],$dfvs))?(' checked'):('')).">{$val[0]}";
					}					
				break;
				case 'select':
					$configvalue = "<select name='{$v['otherruleid']}' id='{$v['otherruleid']}'>";
					$content = split('#',$v['configvalue']);
					foreach ($content as $inv){
						$val = split('=',$inv);
						$configvalue .= "<option value='{$val[1]}'".(($val[1]==$sval)?(' selected'):('')).">{$val[0]}</option>";
					}
				break;
			}
			$t -> set_var('configvalue',$configvalue);
			$t -> parse('r','row',true);
		}
		$t -> set_var('configName','系统');
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
}
$main = new PageSysconfig();
$main -> Main();
?>