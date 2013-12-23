<?
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/power/DispAttachRule.cls.php');

class PageUserconfig extends admin {
	function goModify(){
		//删除所有
		$this -> dbObj -> Execute('delete from '.WEB_ADMIN_TABPOX.'config where (userid='.$this->getUid().')');
		
		//属性
		if(isset($_POST['attachs'][0]))
		foreach ($_POST['attachs'][0] as $k => $v){
			if(is_array($v)) $v = implode('#',$v);
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'config(otherruleid,configvalue,importer,userid)values('.$k.",'".$v."',".$this->getUid().','.$this->getUid().')');
		}
		
		//处理多重权限
		if(isset($_POST['attachrule']))
		foreach ($_POST['attachrule'] as $k => $v){
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'config(otherruleid,configvalue,importer,userid)values('.$k.",'".$v."',".$this->getUid().','.$this->getUid().')');
		}
		exit("<script>alert('修改成功');location.href='userconfig.php';</script>");
	}
	function disp(){
		$powerObj = new Power(& $this -> dbObj,$this -> getUid());
		$t = new Template('../template/user');
		$t -> set_file('f','userconfig.html');
		$t -> set_block('f','row','r');
		$t -> set_block('f','rule','ru');
		if($this -> getModify()){
			$t -> set_var('form','<form name="form1" method="post" action="userconfig.php">');
			$t -> set_var('endform','</form>');
		}else{
			$t -> set_var('disabled',' disabled');
		}
		//得到所有附加权
		$attachArr = array();
		$sql = '
			select c.configvalue as userdefalue,r.rulename,o.* from '.WEB_ADMIN_TABPOX.'otherrule o 
				LEFT OUTER JOIN '.WEB_ADMIN_TABPOX.'rule r ON o.ruleid = r.ruleid 
				LEFT OUTER JOIN '.WEB_ADMIN_TABPOX.'config c ON o.otherruleid = c.otherruleid AND c.userid = '.$this->getUid().'
			where o.isrule = 1
		';
		$attachRs = $this -> dbObj -> Execute($sql);
		while ($tmpRrs = $attachRs->FetchRow()) {
			$powerObj -> parseSqlData(&$tmpRrs);
			$attachArr[$tmpRrs['ruleid']][$tmpRrs['configvarname']]=$tmpRrs;
		}
		$attachRs -> Close();
		
		//得到用户的权限
		$userRule = $powerObj -> getUserRule($this -> getUid());
		
		$have = 'none';
		foreach ($userRule['attach'] as $k=>$v){		//附加权，菜单
			foreach ($v as $ink=>$inv){					//附加权，菜单下的权
				if(is_array($inv) && count($inv)>1 && $attachArr[$k][$ink]['configtype']!='checkbox'){
					$t -> set_var('rulename',$attachArr[$k][$ink]['rulename']);
					$t -> set_var('configname',$attachArr[$k][$ink]['configname']);
					$values = '';
					$default= explode('#',$attachArr[$k][$ink]['configvalue']);
					foreach ($inv as $in_v){			//权限下的每一项
						foreach ($default as $inn_v){	//权限的默认值
							$vs = split('=',$inn_v);
							if(!isset($vs[1])) $vs[1] = $vs[0];
							$checked = '';
							if($vs[1] == $in_v){
								if($attachArr[$k][$ink]['userdefalue'] == $in_v) $checked = ' checked';
								$values .= '<input type="radio" name="attachrule['.$attachArr[$k][$ink]['otherruleid'].']" value="'.$vs[1].'"'.$checked.'>'.$vs[0].' ';
							}
						}
					}
					$t -> set_var('values',$values);
					$t -> parse('ru','rule',true);
					$have = '';
				}
			}
		}
		$t -> set_var('dispMore',$have);
		
		//显示可选择项
		$dap = new DispAttachRule(&$this->dbObj,$this->getUid());
		$rs = $this -> dbObj -> GetArray('select * from '.WEB_ADMIN_TABPOX.'otherrule where (ruleid is null OR ruleid = 0) and (issystemvar = 0) and (isrule is null OR isrule = 0)');
		foreach ($rs as $v) {
			$sval = $this -> dbObj -> GetOne('select configvalue from '.WEB_ADMIN_TABPOX.'config where (userid='.$this->getUid().') and (otherruleid='.$v['otherruleid'].')');
			$t -> set_var($dap -> disp($v,$sval,"</td><td width='50%'><nobr>",2,"</nobr></td></tr><tr><td width='25%'>"));
			$t -> parse('r','row',true);
		}
		$t -> set_var('configName','用户');
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
}
$main = new PageUserconfig();
$main -> Main();
?>