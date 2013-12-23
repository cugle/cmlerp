<?
require(WEB_ADMIN_CLASS_PATH.'/xajax/xajax.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/power/Menu.cls.php');

class pageMain {
	var $po = null;
	var $mo = null;
	var $xo = null;
	var $str = '';
	function pageMain(&$parentObj){
		$this -> po = &$parentObj;
		$this -> mo = new Menu(&$parentObj-> dbObj);
		$this -> str = $parentObj -> loginObj -> _baseRuleToStr();
		$this -> disp();
	}
	function disp(){
		$this -> forAjax();
		$t = new Template('template/mainStyle/style1/');
		$t -> set_file('f','main.html');		//设置一个模板文件，用f做为句柄变量
		$t -> set_var('ajaxstr',$this -> xo -> getJavascript(WEB_ADMIN_HTTPCOMMON.'/js/'));//设置AJAX路径
		
		//定义模板
		$t -> set_block('f','mainMenu','m');	//设置一个块，块名为menu，用m做为句柄变量，父级变量为f
		$t -> set_block('f','mainMenu2','m2');
		$t -> set_block('f','subMenu','s');
		
		//默认菜单的上级菜单
		
		$defaultPage = $this -> po -> getValue('defaultPage');//默认菜单
		if (!$defaultPage || !array_key_exists($defaultPage,$this -> po -> loginObj -> _baseRuleArray)){
			foreach ($this -> po -> loginObj -> _baseRuleArray as $k => $v){
				$defaultPage = $k;
				continue;
			}
		}
		$defaultMenu = $this -> mo -> getRelating($defaultPage);
		
		//初始化菜单
		$currPageInfo = '<a href="./">首页</a>';
		$subMenuInfo  = '';
		$url 		  = '';
		
		//设置一级菜单
		$arr = $this -> mo -> getSubMenuItem(0,$this -> str);
		foreach ($arr as $var){
			if($var['ruleid'] == $defaultMenu[0]['ruleid']){
				if($var['ruleurl'])	$url = $var['ruleurl'];
				$t -> set_var('style','topMenuItemSel');
				$t -> set_var('style2','topMenuItemSel');
			}else{
				$t -> set_var('style','topMenuItem');
				$t -> set_var('style2','');
			}
			$t -> set_var($var);
			if($var['ruleimg']){
				$t -> set_var('img','<img src="'.WEB_ADMIN_HTTPCOMMON.'/img/'.$var['ruleimg'].'" align="absmiddle" style="" />&nbsp;');
			}else{
				$t -> set_var('img','');
			}
			$t -> parse('m','mainMenu',true);//分板模板里的menu块，将分析的结果给m变量，true表示以增加分析
			$t -> parse('m2','mainMenu2',true);
		}
		
		//设置倒数第二级菜单
		if( isset($defaultMenu[count($defaultMenu)-2]['ruleid']) ){
			$subId  = $defaultMenu[count($defaultMenu)-2]['ruleid'];
			$subArr = $this -> mo -> getSubMenuItem($subId,$this -> str);
			$j      = 0;
			foreach ($subArr as $var){
				if ($var['ruleid'] == $defaultPage){
					$url = $var['ruleurl'];
					$t -> set_var('substyle','subSubMenuSel');
				}else{
					$t -> set_var('substyle','subSubMenu');
				}
				$t -> set_var('isdir',($this -> mo -> haveSubMenu($var['ruleid']))?'true':'false');
				$t -> set_var('spacestr',($j++==0)?'':'|');
				$t -> set_var($var);
				if(!$var['ruleurl'])$t -> set_var('rulename',$var['rulename'].'..');
				$t -> parse('s','subMenu',true);
			}
		}
		
		//设置导航条
		$j = count($defaultMenu);
		for ($i=0; $i<$j; $i++){
			if($i!=0 && $i!=$j-1){
				$subMenuInfo .= '<a id='.$defaultMenu[$i]['ruleid'].' onClick ="gotoUrl(\''.$defaultMenu[$i]['ruleurl'].'\',this.id,true)" class="handStyle">'.$defaultMenu[$i]['rulename'].'</a> > ';
			}
			$isdir = !($i==$j-1);
			$currPageInfo .= ' > <a id ='.$defaultMenu[$i]['ruleid'].' onClick="gotoUrl(\''.$defaultMenu[$i]['ruleurl'].'\',this.id,'.$isdir.')" class="handStyle">'.$defaultMenu[$i]['rulename'].'</a>';
		}
		
		//设置可以跳转的用户
//		  <{optname} {attrib}='{subusername}'>{subusername}</{optname}>
		$t -> set_block('f','subUser','su');
		$uid = $this -> po -> loginObj->getUid();
		$pid = $this -> po -> loginObj->_superid ? $this -> po -> loginObj->_superid : $uid;
		$adminGroups = & $this -> po -> dbObj -> Execute('select g.groupid,g.groupname from '.WEB_ADMIN_TABPOX.'groupmanager gm,'.WEB_ADMIN_TABPOX.'group g where gm.groupid=g.groupid and gm.userid = '.$pid);
		if($adminGroups -> _numOfRows){
			$t -> set_var('dispgo','');
			while ($adminGroupsRrs = $adminGroups -> FetchRow()) {
				$t -> set_var('optname','optgroup');
				$t -> set_var('attrib','label');
				$t -> set_var('subusername',$adminGroupsRrs['groupname']);
				$t -> parse('su','subUser',true);
				$users = & $this -> po -> dbObj -> Execute('select u.username from '.WEB_ADMIN_TABPOX.'usergroup ug,'.WEB_ADMIN_TABPOX.'user u where ug.userid=u.userid and ug.groupid = '.$adminGroupsRrs['groupid'].' and u.userid <> '.$uid);
				while ($inRrs = $users -> FetchRow()) {
					$t -> set_var('optname','option');
					$t -> set_var('attrib','value');
					$t -> set_var('subusername',$inRrs['username']);
					$t -> parse('su','subUser',true);
				}
			}
		}else{
			$t -> set_var('dispgo','none');
		}

		//设置页面属性可选项
		$atr = $this -> po -> dbObj -> GetOne('select otherruleid from '.WEB_ADMIN_TABPOX.'otherrule where (isrule=0 or isrule is null) and (issystemvar=0 or issystemvar is null) and ruleid = '.$defaultPage);
		if($atr){
			$currPageInfo.=" <a onClick='goPageAttribute({$defaultPage})'>[页面属性]</a>";
		}
		$t -> set_var('username',$this -> po -> dbObj -> GetOne('select username from '.WEB_ADMIN_TABPOX.'user where userid='.$this -> po -> loginObj->getUid()));
		$t -> set_var('subMenuInfo',$subMenuInfo);
		$t -> set_var('currentpage',$currPageInfo);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/template/mainStyle/style1/');
		$t -> set_var('startUrl',$url);
		$t -> parse('out','f');
		$t -> p('out');
		
		
	}
	function forAjax(){
		$this -> xo = new xajax();
//		$this -> xo -> debugOn();

		$GLOBALS['AjaxDo']  = &$this -> po -> dbObj;
		$GLOBALS['AjaxMo']  = &$this -> mo;
		$GLOBALS['AjaxStr'] = $this -> str;
		
		function setSubMenuStr($id){
			$objResponse = new xajaxResponse();
			$objResponse -> addAssign("loading","style.visibility",'hidden');
			$subMenuStr = '';
			$subArr = $GLOBALS['AjaxMo'] -> getSubMenuItem($id,$GLOBALS['AjaxStr']);
			foreach ($subArr as $v){
				$open = '';
				if(!$v['ruleurl']) $open = '.. ';
				$haveStr = $GLOBALS['AjaxMo'] -> haveSubMenu($v['ruleid'])?'true':'false';
				if($subMenuStr == ''){
					$subMenuStr ='<a id="'.$v['ruleid'].'" onClick="gotoUrl(\''.$v['ruleurl'].'\',this.id,'.$haveStr.')" class="subSubMenu">'.$v['rulename'].$open.'</a>';
				}else{
					$subMenuStr .=' | <a id="'.$v['ruleid'].'" onClick="gotoUrl(\''.$v['ruleurl'].'\',this.id,'.$haveStr.')" class="subSubMenu">'.$v['rulename'].$open.'</a>';
				}
			}
			$objResponse->addScript('document.getElementById("subSubMenu").scrollLeft = 0;document.getElementById("subSubMenu").innerHTML = "'.str_replace('"','\"',$subMenuStr).'";disp();');
			return $objResponse;
		}
		function setSubSubMenuStr($id){
			$objResponse = new xajaxResponse();
			$subMenuStr = $pStr = '';
			$subArr = $GLOBALS['AjaxMo'] -> getSubMenuItem($id,$GLOBALS['AjaxStr']);
			foreach ($subArr as $v){
				$open = '';
				if(!$v['ruleurl']) $open = '..';
				$haveStr = $GLOBALS['AjaxMo'] -> haveSubMenu($v['ruleid'])?'true':'false';
				if($subMenuStr == ''){
					$subMenuStr ='<a id="'.$v['ruleid'].'" onClick="gotoUrl(\''.$v['ruleurl'].'\',this.id,'.$haveStr.')" class="subSubMenu"">'.$v['rulename'].$open.'</a>';
				}else{
					$subMenuStr .=' | <a id="'.$v['ruleid'].'" onClick="gotoUrl(\''.$v['ruleurl'].'\',this.id,'.$haveStr.')" class="subSubMenu">'.$v['rulename'].$open.'</a>';
				}
			}
			$pArr = $GLOBALS['AjaxMo'] -> getRelating($id);
			for ($i=1; $i<count($pArr); $i++){
				$haveStr = $GLOBALS['AjaxMo'] -> haveSubMenu($id)?'true':'false';
				$pStr .= '<a id='.$pArr[$i]['ruleid'].' onClick="gotoUrl(\''.$pArr[$i]['ruleurl'].'\',this.id,'.$haveStr.')" class="handStyle">'.$pArr[$i]['rulename'].'</a> >&nbsp;';
			}
			$objResponse->addAssign('submenu','innerHTML',$pStr);
			$objResponse->addAssign('submenu','style.display','');
			$objResponse->addScript('document.getElementById("subSubMenu").scrollLeft = 0;document.getElementById("subSubMenu").innerHTML = "'.str_replace('"','\"',$subMenuStr).'";disp();');
			$objResponse->addAssign("loading","style.visibility",'hidden');
			return $objResponse;
		}
		function setCurrentPage($id){
			$relating = $GLOBALS['AjaxMo'] -> getRelating($id);
			$str = '<a href="./">首页</a>';
			foreach ($relating as $v){
				$haveStr = $GLOBALS['AjaxMo'] -> haveSubMenu($v['ruleid'])?'true':'false';
				$str .= ' > <a id="'.$v['ruleid'].'" onClick="gotoUrl(\''.$v['ruleurl'].'\',this.id,'.$haveStr.')" class="handStyle">'.$v['rulename'].'</a>';
			}
			
			$atr = $GLOBALS['AjaxDo'] -> GetOne('select otherruleid from '.WEB_ADMIN_TABPOX.'otherrule where (isrule=0 or isrule is null) and (issystemvar=0 or issystemvar is null) and ruleid = '.$id);
			if($atr){
				$str.=' <a onClick="goPageAttribute('.$id.')">[页面属性]</a>';
			}
			$objResponse = new xajaxResponse();
			$objResponse->addAssign('currentPag','innerHTML',$str);
			$objResponse->addScript('disp();');
			return $objResponse;
		}
		$this -> xo -> registerFunction("setSubMenuStr");
		$this -> xo -> registerFunction("setSubSubMenuStr");
		$this -> xo -> registerFunction("setCurrentPage");
		$this -> xo -> processRequests();
	}
}
?>