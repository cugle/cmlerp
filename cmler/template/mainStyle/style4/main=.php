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
		$t = new Template('template/mainStyle/style3/');
		$t -> set_file('f','main.html');		//设置一个模板文件，用f做为句柄变量
		$t -> set_var('ajaxstr',$this -> xo -> getJavascript(WEB_ADMIN_HTTPCOMMON.'/js/',false));//设置AJAX路径
		session_start(); 
		session_register("currentorgan");
		session_register("currentorganname"); 
		session_register("agency_no");
		if(!$_SESSION["currentorgan"])
		{
		
		
		
		$_SESSION["currentorgan"]=$this -> po -> dbObj -> GetOne("select  agencyid from ".WEB_ADMIN_TABPOX."user  where  userid =".$this -> po -> loginObj->getUid());
		//echo "select a.agency_easyname from ".WEB_ADMIN_TABPOX."agency a inner join  ".WEB_ADMIN_TABPOX."user u on a.agency_id=u.agencyid  where u.userid =".$this -> po -> loginObj->getUid();
		$_SESSION["agency_no"]=$this -> po -> dbObj -> GetOne("select  agency_no from ".WEB_ADMIN_TABPOX."agency  where  agency_id =".$_SESSION["currentorgan"]); 
		$_SESSION["currentorganname"] = $this -> po -> dbObj -> GetOne("select  agency_easyname from ".WEB_ADMIN_TABPOX."agency where agency_id =".$_SESSION["currentorgan"]);
	    }

		//设置主菜单
		$t -> set_block('f','menu','m');
		$rule = $this -> mo -> getSubMenuItem(0,$this -> str);
		foreach ($rule as $v){
			$t -> set_var($v);
			if($v['ruleimg']){
				$t -> set_var('img','<img onMouseOver="event.cancelBubble = true;" onMouseOut="event.cancelBubble = true;" onClick="event.cancelBubble = true;" src="'.WEB_ADMIN_HTTPCOMMON.'/img/'.$v['ruleimg'].'" align="absmiddle">');
			}else{
				$t -> set_var('img','');
			}
			$t -> parse('m','menu',true);
		}
		
		//左侧工具栏
		$t -> set_block('f','leftButton','lb');
		$lbs = $this -> po -> getValue('leftButtons');
		if($lbs){
			$lbs = explode('#',$lbs);
			foreach ($lbs as $v){
				$rs = $this -> po -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'rule where ruleid = '.$v);
				if($rs['ruleimg']){
					$t -> set_var('leftImg',"<img align='absmiddle' src='".WEB_ADMIN_HTTPCOMMON.'/img/'.$rs['ruleimg']."' onClick='event.cancelBubble = true;'>");
				}else{
					$t -> set_var('leftImg','');
				}
				$t -> set_var($rs);
				$t -> parse('lb','leftButton',true);
			}
		}

		//右侧工具栏
		$t -> set_block('f','rightButton','rb');
		$rbs = $this -> po -> getValue('topButtons');
		if($rbs){
			$rbs = explode('#',$rbs);
			foreach ($rbs as $v){
				$rs = $this -> po -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'rule where ruleid = '.$v);
				if($rs['rulebigimg']){
					$t -> set_var('rightImg',"<img src='".WEB_ADMIN_HTTPCOMMON.'/img/'.$rs['rulebigimg']."' onClick='event.cancelBubble = true;'>");
				}else{
					$t -> set_var('rightImg','');
				}
				$t -> set_var($rs);
				$t -> parse('rb','rightButton',true);
			}
		}

		//默认页
		$defaultPage = $this -> po -> getValue('defaultPage');
		if (!$defaultPage || !array_key_exists($defaultPage,$this -> po -> loginObj -> _baseRuleArray)){
			foreach ($this -> po -> loginObj -> _baseRuleArray as $k => $v){
				$defaultPage = $k;
				continue;
			}
		}
		$defaultMenu = $this -> mo -> getRelating($defaultPage);
		
		//当前位置
		$currPageInfo = '首页';
		$j = count($defaultMenu);
		for ($i=0; $i<$j; $i++){
			$currPageInfo .= ' > '.$defaultMenu[$i]['rulename'];
		}
		$t -> set_var('currentpage',$currPageInfo);
		
		//标题
		$t -> set_var('title','cml店务管理系统');
		
		//键接
		$t -> set_var('initurl',$defaultMenu[$j-1]['ruleurl']);
		$t -> set_var('currentPageId',$defaultMenu[$j-1]['ruleid']);
		
		//当前用户
		$t -> set_var('username',$this -> po -> dbObj -> GetOne('select username from '.WEB_ADMIN_TABPOX.'user where userid='.$this -> po -> loginObj->getUid()));
		//设置当前机构信息
		 $t -> set_var('currentorganname',$_SESSION["currentorganname"]); 		
		
		//左边宽度
		$t -> set_var('leftWidth',$this -> po -> getValue('MainLeftWidth'));
	 
	
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

				$users = & $this -> po -> dbObj -> Execute('select u.username from '.WEB_ADMIN_TABPOX.'usergroup ug,'.WEB_ADMIN_TABPOX.'user u where ug.userid=u.userid and ug.groupid = '.$adminGroupsRrs['groupid'].' and u.userid <> '.$uid.' and agencyid ='.$_SESSION["currentorgan"]);
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
	
		//资源路径
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/template/mainStyle/style3/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function forAjax(){
		$this -> xo = new xajax();

		$GLOBALS['AjaxMo']  = &$this -> mo;
		$GLOBALS['AjaxStr'] = $this -> str;

		function setSubMenu($id,$index){
			$objResponse = new xajaxResponse();
			$subArr = $GLOBALS['AjaxMo'] -> getSubMenuItem($id,$GLOBALS['AjaxStr']);
			$scriptStr = 'var $str = \'<div id="menu" style="position:absolute;width:150px; left:0px; top:0px; z-index:'.$index.'"></div>\';';
			$scriptStr.= 'var $menuObj = document.createElement($str);';
			$scriptStr.= 'var $str2= \'<table width="100%"  border="0" cellpadding="0" cellspacing="0">\'+';
			foreach ($subArr as $v){
				$img = $v['ruleimg']?("<img src=".WEB_ADMIN_HTTPCOMMON.'/img/'.$v['ruleimg'].">"):'';
				if($GLOBALS['AjaxMo'] -> haveSubMenu($v['ruleid'])){
					$scriptStr.= "'<tr title=\"".$v['ruleurl']."\" id=\"".$v['ruleid']."\" onClick=\"\$m.subMenuClick();\" onMouseOver=\"\$m.subMenuOver();\$m.dispSubMenu(\'".$id."\',\'".$v['ruleid']."\',\'".$index."\');\">".
								'<td width="10" height="22" onMouseOver="event.cancelBubble = true;" onClick="event.cancelBubble = true;">'.$img.'</td>'.
								"<td ruleid=\'right{$v['ruleid']}\' ruleurl=\'{$v['ruleurl']}\'>".$v['rulename']."</td><td width=\"10\">&gt;</td></tr>'+\r\n";
				}else{
					$scriptStr.= "'<tr title=\"".$v['ruleurl']."\" id=\"id\" onClick=\"\$m.subMenuClick();\" onMouseOver=\"\$m.subMenuOver();\$m.hiddenMenu(\'".$index."\');\">".
								'<td width="10" height="22" onMouseOver="event.cancelBubble = true;" onClick="event.cancelBubble = true;">'.$img.'</td>'.
								"<td ruleid=\'right{$v['ruleid']}\' ruleurl=\'{$v['ruleurl']}\'>".$v['rulename']."</td><td width=\"10\"></td></tr>'+\r\n";
				}
			}
			$scriptStr.= "'</table>';";
			$scriptStr.='$menuObj.innerHTML = $str2;';
			$scriptStr.='$menuObj.style.visibility = \'hidden\';';
			$scriptStr.='document.body.insertBefore($menuObj);';
			$scriptStr.='$m.menuArr.push(new menuObj('.$id.',$menuObj,'.$index.'));';
//			$scriptStr.='return $m.menuArr[$m.menuArr.length-1].obj;';
			$objResponse->addScript($scriptStr);
			return $objResponse;
		}

		$this -> xo -> registerFunction("setSubMenu");
		$this -> xo -> processRequests();
	}
}
?>