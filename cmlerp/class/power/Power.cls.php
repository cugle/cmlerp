<?
/**
 * @desc 权限类
 * @author  Vanni
 * @version 1.0
 * @package power
 */
class Power{
	var $_db = null;
	var $serach = array();
	var $replace = array();
	var $operater = null;
	/**
	 * 构造函数
	 *
	 * @param &dbObj $db 数据库操作对象
	 * @param Int $operater 操作者
	 * @return Power
	 */
	function Power(&$db,$operater){
		$this->_db=&$db;
		$this->operater = $operater;
	}
	/**
	 * 得到用户的所有权限，返加二维数组
	 * Array(
	 *     [base] => Array(  //基本权限
	 *          [1] => 11001 //菜单ID => 各权限
	 *     )
	 *     [attach] => Array(
	 *          [菜单ID] => Array(
	 * 				[varname] => 值
	 * 				[varname] => array(
	 * 					[0] => 值
	 * 					[1] => 值
	 * 				)
	 * 			)
	 *     )
	 * )
	 *
	 * @param Int $userid 用户ID
	 * @return Array[][]
	 */
	function getUserRule($userid){
		$outArr = array('base'=>array(),'attach'=>array());
		$sql = '
		SELECT rr.ruleid, SUM(rr.issuperuser) AS issuperuser, SUM(rr.canbrowse) AS canbrowse,
		      SUM(rr.canappend) AS canappend, SUM(rr.canmodify) AS canmodify, SUM(rr.candelete) AS candelete , SUM(rr.canimport) AS canimport, SUM(rr.canexport) AS canexport, SUM(rr.canrecoil) AS canrecoil, SUM(rr.canaudit) AS canaudit
		FROM  '.WEB_ADMIN_TABPOX.'user s INNER JOIN
		      '.WEB_ADMIN_TABPOX.'usergroup sg ON s.userid = sg.userid INNER JOIN
		      '.WEB_ADMIN_TABPOX.'grouprole gr ON gr.groupid = sg.groupid INNER JOIN
		      '.WEB_ADMIN_TABPOX.'rolerule rr ON gr.roleid = rr.roleid
		WHERE (s.userid = '.$userid.') GROUP BY rr.ruleid';
		$rs = &$this->_db->Execute($sql);
		$this->_parseBaseRuleToArray(&$rs,&$outArr['base']);
		// 设置默认的附加权
		$this->_getDefaultAttach(1,$userid,&$outArr['attach']);
		// 更新修改后的附加权
		$this->_getAttachRule(1,$userid,&$outArr['base'],&$outArr['attach']);
//		exit();
		return $outArr;
	}
	/**
	 * 得到组的所有权限，返加二维数组
	 *
	 * @param Int $groupid 组ID
	 * @return Array[][]
	 */
	function getGroupRule($groupid){
		$outArr = array('base'=>array(),'attach'=>array());
		$sql = '
			SELECT rr.ruleid, SUM(rr.issuperuser) AS issuperuser, SUM(rr.canbrowse) AS canbrowse,
				  SUM(rr.canappend) AS canappend, SUM(rr.canmodify) AS canmodify, SUM(rr.candelete) AS candelete
			FROM  '.WEB_ADMIN_TABPOX.'grouprole gr INNER JOIN '.WEB_ADMIN_TABPOX.'rolerule rr ON gr.roleid = rr.roleid
			WHERE (gr.groupid = '.$groupid.') GROUP BY rr.ruleid';
		$rs = &$this->_db->Execute($sql);
		$this->_parseBaseRuleToArray(&$rs,&$outArr['base']);
		$this->_getDefaultAttach(2,$groupid,&$outArr['attach']);
		$this->_getAttachRule(2,$groupid,&$outArr['base'],&$outArr['attach']);
		return $outArr;
	}
	/**
	 * 得到角色的所有权限，返加二维数组
	 *
	 * @param Int $roleid 组ID
	 * @return Array[][]
	 */
	function getRoleRule($roleid){
		$outArr = array('base'=>array(),'attach'=>array());
		$sql = 'SELECT * FROM '.WEB_ADMIN_TABPOX.'rolerule WHERE (roleid = '.$roleid.')';
		$rs = &$this->_db->Execute($sql);
		$this->_parseBaseRuleToArray(&$rs,&$outArr['base']);
		$this->_getDefaultAttach(3,$roleid,&$outArr['attach']);
		$this->_getAttachRule(3,$roleid,&$outArr['base'],&$outArr['attach']);
		return $outArr;
	}

	/**
	 * 
	 */
	function _getDefaultAttach($type,$id,&$attachArr){
		#得到所有的菜单ID
		$ruleids = '';
		if($type == 1){
			$sql1 = '
			SELECT DISTINCT r.ruleid FROM '.
				WEB_ADMIN_TABPOX.'usergroup u INNER JOIN '.
				WEB_ADMIN_TABPOX.'grouprole g ON u.groupid = g.groupid INNER JOIN '.
				WEB_ADMIN_TABPOX.'rolerule r ON g.roleid = r.roleid 
				WHERE u.userid = '.$id;
			#得到所有附加的菜单ID
			$sql2 = '
			SELECT DISTINCT a.ruleid FROM '.
				WEB_ADMIN_TABPOX.'usergroup u INNER JOIN '.
				WEB_ADMIN_TABPOX.'grouprole g ON u.groupid = g.groupid INNER JOIN '.
				WEB_ADMIN_TABPOX.'rolerule r ON g.roleid = r.roleid INNER JOIN '.
				WEB_ADMIN_TABPOX.'attachrule a ON (
					(u.userid = a.userorgrouporroleid AND a.userorgrouporrole = 1) OR
					(g.groupid = a.userorgrouporroleid AND a.userorgrouporrole = 2) OR
					(r.roleid = a.userorgrouporroleid AND a.userorgrouporrole = 3)
				)WHERE u.userid='.$id;
		}elseif ($type == 2){
			$sql1 = '
			SELECT DISTINCT r.ruleid FROM '.
				WEB_ADMIN_TABPOX.'grouprole g INNER JOIN '.
				WEB_ADMIN_TABPOX.'rolerule r ON g.roleid = r.roleid 
				WHERE g.groupid = '.$id;
			#得到所有附加的菜单ID
			$sql2 = '
			SELECT DISTINCT a.ruleid FROM '.
				WEB_ADMIN_TABPOX.'grouprole g INNER JOIN '.
				WEB_ADMIN_TABPOX.'rolerule r ON g.roleid = r.roleid INNER JOIN '.
				WEB_ADMIN_TABPOX.'attachrule a ON (
					(g.groupid = a.userorgrouporroleid AND a.userorgrouporrole = 2) OR
					(r.roleid = a.userorgrouporroleid AND a.userorgrouporrole = 3)
				)WHERE g.groupid='.$id;
		}elseif ($type == 3){
			$sql1 = 'SELECT DISTINCT ruleid FROM '.WEB_ADMIN_TABPOX.'rolerule WHERE roleid = '.$id;
			#得到所有附加的菜单ID
			$sql2 = '
			SELECT DISTINCT a.ruleid FROM '.
				WEB_ADMIN_TABPOX.'rolerule r INNER JOIN '.
				WEB_ADMIN_TABPOX.'attachrule a ON (r.roleid = a.userorgrouporroleid AND a.userorgrouporrole = 3)
			WHERE r.roleid='.$id;
		}
		$rs1 = &$this -> _db -> Execute($sql1);
		while ($rrs = $rs1 -> FetchRow())	$ruleids .= $rrs['ruleid'].',';
		$rs1 -> Close();
		$rs2 = &$this -> _db -> Execute($sql2);
		while ($rrs = $rs2 -> FetchRow())	$ruleids .= $rrs['ruleid'].',';
		$rs2 -> Close();
		if(strlen($ruleids)>0){
			$ruleids = explode(',',substr($ruleids,0,-1));
			$ruleids = implode(',',array_unique($ruleids));
			#查找附加值
			$sql = 'SELECT ruleid,configvarname,configdefault FROM '.WEB_ADMIN_TABPOX.
				   'otherrule WHERE ruleid IN('.$ruleids.') AND (isrule = 1)
					AND otherruleid NOT IN(SELECT otherruleid FROM '.WEB_ADMIN_TABPOX.'attachrule WHERE userorgrouporrole = 3)
				   ORDER BY ruleid';
			$rs = &$this -> _db -> Execute($sql);
			while ($rrs = $rs -> FetchRow()){
				$this -> parseSqlData(&$rrs);
				if(ereg('#',$rrs['configdefault'])) $rrs['configdefault'] = split('#',$rrs['configdefault']);
				$attachArr[$rrs['ruleid']][$rrs['configvarname']] = $rrs['configdefault'];
			}
			$rs -> Close();
		}
	}
	/**
	 * 私有方法，分析一个结果集的基本权限，到一个数组里面
	 *
	 * @param dbRs $rs 查询结果集
	 * @param &Array $out 输出的数组
	 */
	function _parseBaseRuleToArray(&$rs,&$out){
		while ($rrs = &$rs->FetchRow()) {
			$str = ($rrs['issuperuser']?1:0).($rrs['canbrowse']?1:0).($rrs['canappend']?1:0).($rrs['canmodify']?1:0).($rrs['candelete']?1:0).($rrs['canimport']?1:0).($rrs['canexport']?1:0).($rrs['canrecoil']?1:0).($rrs['canaudit']?1:0);
			if (is_null(@$out[$rrs['ruleid']])){
				$out[$rrs['ruleid']] = $str;
			}else{
				$out[$rrs['ruleid']] = $this->_calculateBaseRule($out[$rrs['ruleid']],$str,'add');
			}
		}
		$rs->Close();
	}
	/**
	 * 私有方法，分析一个结果集的附加权限，到一个数组里面
	 *
	 * @param dbRs $rs 查询结果集
	 * @param &Array $baseRule 基本权限数组的引用
	 * @param &Array $attachRule 附加权限数组的引用
	 * @param Boolean $addordel 是增加还是删除权限
	 */
	function _parseAttachRuleToArray($sql,&$baseRule,&$attachRule,$addordel){
		$rs = &$this->_db->Execute($sql);
		if($rs !== false){
			while ($rrs = &$rs->FetchRow()) {
				//---附加的基本权限
				if($rrs['ruleid']>0){
					if(!isset($baseRule[$rrs['ruleid']])){
						$baseRule[$rrs['ruleid']] = $rrs['baserule'];
					}else{
						$baseRule[$rrs['ruleid']] = $this->_calculateBaseRule(&$baseRule[$rrs['ruleid']],$rrs['baserule'],$addordel);
					}
				}
				//---附加的页面权限
				$this -> parseSqlData(&$rrs);
				if ( !isset($attachRule[$rrs['ruleid']]) && $rrs['configvarname']) $attachRule[$rrs['ruleid']] = array();
				if ($rrs['configvarname'] && strlen($rrs['configvalue'])>0) {	//有附加权
					$source = &$attachRule[$rrs['ruleid']];
					$configvalue = (strpos($rrs['configvalue'],'#')) ? explode('#',$rrs['configvalue']) : $rrs['configvalue'];
					if(array_key_exists($rrs['configvarname'],$source)){		//如果存在此附加权
						if (is_array($source[$rrs['configvarname']])) {	//如果是数组，表示为多选项
							if($addordel){
								if (is_array($configvalue)){
									foreach ($configvalue as $inv){
										if (!in_array($inv,$source[$rrs['configvarname']])) {
											$source[$rrs['configvarname']][]=$inv;
										}
									}
								}else{
									if (!in_array($configvalue,$source[$rrs['configvarname']])) {
										$source[$rrs['configvarname']][]=$configvalue;
									}
								}
							}else{
								if (is_array($configvalue)){
									foreach ($configvalue as $inv){
										if (($ai = array_search($inv,$source[$rrs['configvarname']])) !== false) {
											unset($source[$rrs['configvarname']][$ai]);
										}
									}
								}else{
									if (($ai = array_search($configvalue,$source[$rrs['configvarname']])) !== false) {
										unset($source[$rrs['configvarname']][$ai]);
									}
								}
								if(count($source[$rrs['configvarname']])==0) unset($source[$rrs['configvarname']]);
							}
						}else{
							if ($addordel) {
								if (is_array($configvalue)){
									if(!in_array($source[$rrs['configvarname']],$configvalue)){
										$configvalue[]=$source[$rrs['configvarname']];
										$source[$rrs['configvarname']]=$configvalue;
									}
								}else{
									if($source[$rrs['configvarname']] != $configvalue){
										$source[$rrs['configvarname']]=array($configvalue,$source[$rrs['configvarname']]);
									}
								}
							}else{
								if (is_array($configvalue)){
									foreach ($configvalue as $inv){
										if (($ai = array_search($inv,$source[$rrs['configvarname']])) !== false) {
											unset($source[$rrs['configvarname']][$ai]);
										}
									}
									if (count($source[$rrs['configvarname']]) == 0) unset($source[$rrs['configvarname']]);
								}else{
									if ($configvalue == $source[$rrs['configvarname']]) {
										unset($source[$rrs['configvarname']]);
									}
								}
//								if(count($source[$rrs['configvarname']])==0) unset($source[$rrs['configvarname']]);
							}
						}
					}else{
						if($addordel){
							$source[$rrs['configvarname']] = $configvalue;
						}
					}
				}
				if(isset($attachRule[$rrs['ruleid']]) && count($attachRule[$rrs['ruleid']])==0)unset($attachRule[$rrs['ruleid']]);
			}
			$rs->Close();
		}else{
			//内部错误
		}
//		print_r($attachRule);
	}
	/**
	 * 权有方法，分析一个SQL语句。
	 *
	 * @param String $sql
	 * @param &Array[] $baseRule 基本权限数组的引用
	 * @param &Array[] $out 附加权限数组的引用
	 */
	function _parseSql($sql,&$baseRule,&$out){
		$this->_parseAttachRuleToArray($sql,&$baseRule,&$out,1);
		$sql = str_replace('(a.addordel = 1)','(a.addordel = 0)',$sql);
		$this->_parseAttachRuleToArray($sql,&$baseRule,&$out,0);
	}
	/**
	 * 得到附加权限
	 *
	 * @param Int $userorgrouporrole 用户或组或角色，1，2，3
	 * @param Boolean $addordel 增加或删除
	 * @param Int $id 用户或组或角色的ID
	 * @param &Array $baseRule 基本权的引用
	 * @param &Array $out 输出的数组引用
	 */
	function _getAttachRule($userorgrouporrole,$id,&$baseRule,&$out){
		if ($userorgrouporrole==1){
			//角色
			$sql = '
				SELECT a.ruleid, a.baserule, o.configvarname, a.configvalue
				FROM  '.WEB_ADMIN_TABPOX.'usergroup ug INNER JOIN
				      '.WEB_ADMIN_TABPOX.'grouprole gr ON ug.groupid = gr.groupid INNER JOIN
				      '.WEB_ADMIN_TABPOX.'attachrule a ON gr.roleid = a.userorgrouporroleid AND a.userorgrouporrole = 3 LEFT OUTER JOIN
				      '.WEB_ADMIN_TABPOX.'otherrule o ON a.otherruleid = o.otherruleid
				WHERE (ug.userid = '.$id.') AND (a.addordel = 1)';
			$this->_parseSql($sql,&$baseRule,&$out);

			//用户组
			$sql = '
				SELECT a.ruleid, a.baserule, o.configvarname, a.configvalue
				FROM  '.WEB_ADMIN_TABPOX.'usergroup ug INNER JOIN
				      '.WEB_ADMIN_TABPOX.'attachrule a ON ug.groupid = a.userorgrouporroleid AND a.userorgrouporrole = 2 LEFT OUTER JOIN
				      '.WEB_ADMIN_TABPOX.'otherrule o ON a.otherruleid = o.otherruleid
				WHERE (ug.userid = '.$id.') AND (a.addordel = 1)';
			$this->_parseSql($sql,&$baseRule,&$out);

			//用户
			$sql = '
				SELECT a.ruleid, a.baserule, o.configvarname, a.configvalue
				FROM  '.WEB_ADMIN_TABPOX.'attachrule a LEFT OUTER JOIN
				      '.WEB_ADMIN_TABPOX.'otherrule o ON a.otherruleid = o.otherruleid
				WHERE (a.userorgrouporrole = 1) AND (a.userorgrouporroleid = '.$id.') AND (a.addordel = 1)';
			$this->_parseSql($sql,&$baseRule,&$out);

		}elseif ($userorgrouporrole==2){
			//角色
			$sql2 = '
				SELECT a.ruleid, a.baserule, o.configvarname, a.configvalue
				FROM  '.WEB_ADMIN_TABPOX.'grouprole gr INNER JOIN
				      '.WEB_ADMIN_TABPOX.'attachrule a ON gr.roleid = a.userorgrouporroleid AND a.userorgrouporrole = 3 LEFT JOIN
				      '.WEB_ADMIN_TABPOX.'otherrule o ON a.otherruleid = o.otherruleid
				WHERE (gr.groupid = '.$id.') AND (a.addordel = 1)';
			$this->_parseSql($sql2,&$baseRule,&$out);

			//组
			$sql = '
				SELECT a.ruleid, a.baserule, o.configvarname, a.configvalue
				FROM  '.WEB_ADMIN_TABPOX.'attachrule a LEFT OUTER JOIN
				      '.WEB_ADMIN_TABPOX.'otherrule o ON a.otherruleid = o.otherruleid
				WHERE (a.userorgrouporrole = 2) AND (a.userorgrouporroleid = '.$id.') AND (a.addordel = 1)';
			$this->_parseSql($sql,&$baseRule,&$out);

		}elseif ($userorgrouporrole==3){
			$sql = '
				SELECT a.ruleid, a.baserule, o.configvarname, a.configvalue
				FROM  '.WEB_ADMIN_TABPOX.'attachrule a LEFT JOIN 
					 '.WEB_ADMIN_TABPOX.'otherrule o ON a.otherruleid = o.otherruleid
				WHERE (a.userorgrouporrole = 3 ) AND (a.userorgrouporroleid = '.$id.') AND (a.addordel = 1)';
			$this->_parseSql($sql,&$baseRule,&$out);
		}
	}
	/**
	 * 计算基本权限，返回计算后的新字符串
	 *
	 * @param String $sorStr 原始字符串
	 * @param String $newStr 新的字符串
	 * @param Boolean $addordel 计算方法True增加，False删除
	 * @return String
	 */
	function _calculateBaseRule($sorStr,$newStr,$addordel){
		$resultStr = '';
		if(!($newStr+0))return $sorStr;
		for ($i=0; $i<9; $i++){
			if ($addordel) {
				$allow_i = intval($sorStr[$i]) | intval($newStr[$i]);
			}else{
				$allow_i = intval($newStr[$i]) ? 0 :( intval($sorStr[$i]) | intval($newStr[$i]) );
			}
			$resultStr .= strval($allow_i);
		}
		return $resultStr;
	}
	/////////////////////////////////////////////////////////////私有方法结束
	/**
	 * 增加一个权限
	 *
	 * @param Int $getterType 要增加的类型，1用户，2组，3角色
	 * @param Int $getterid 对应的类弄ID号
	 * @param String $newStr 要增加的新字符串
	 * @param Int $ruleid 增加的权限ID
	 * @param String $name 附加值的名称，NULL表示增加的是基本权
	 */
	function addRuleTo($getterType,$getterid,$newStr,$ruleid=0,$name=null){
		$this -> _modifyRule(1,$getterType,$getterid,$newStr,$ruleid,$name);
	}
	/**
	 * 删除一个权限
	 *
	 * @param Int $getterType 要删除的类型，1用户，2组，3角色
	 * @param Int $getterid 对应的类弄ID号
	 * @param String $newStr 要删除的新字符串
	 * @param Int $ruleid 删除的权限ID
	 * @param String $name 附加值的名称，NULL表示删除的是基本权
	 */
	function delRuleFor($getterType,$getterid,$delStr,$ruleid=0,$name=null){
		$this -> _modifyRule(0,$getterType,$getterid,$delStr,$ruleid,$name);
	}
	/**
	 * 处理权限，私有方法。
	 *	1、如果操作对象有增加了删除权限在此项目上，则删除此删除权限，将自动转自增加权
	 *	2、如果之前有增加操作的，删除之前的增加权限
	 *
	 * @param add|del $addOrdel
	 * @param Int $getterType
	 * @param Int $getterid
	 * @param String $newStr
	 * @param Int $ruleid
	 * @param String|Int $name 名称或ID
	 */
	function _modifyRule($addOrdel,$getterType,$getterid,$newStr,$ruleid=0,$name=null){
		
		if($name){
			$whereSql = '';
			if(is_numeric($name)){
				$otherid  = $name;
				$whereSql = "otherruleid = '$name'";
			}else{
				$whereSql = "configvarname = '$name'";
				$otherid = $this -> _db -> GetOne("SELECT otherruleid FROM ".WEB_ADMIN_TABPOX."otherrule WHERE $whereSql");
			}
			$addSql = 'SELECT a.attachruleid,a.baserule,a.configvalue FROM '.WEB_ADMIN_TABPOX.'attachrule a INNER JOIN '.WEB_ADMIN_TABPOX.'otherrule o
					ON a.otherruleid = o.otherruleid AND a.ruleid = o.ruleid AND o.'.$whereSql." 
					WHERE (a.addordel = 1) AND (a.userorgrouporrole = $getterType) AND (a.userorgrouporroleid = $getterid) AND (a.ruleid = $ruleid)";
			$delSql = str_replace('(a.addordel = 1)','(a.addordel = 0)',$addSql);
		}else{
			$addSql = 'SELECT attachruleid,baserule,configvalue FROM '.WEB_ADMIN_TABPOX."attachrule WHERE (addordel = 1) AND (userorgrouporrole = $getterType) AND (userorgrouporroleid = $getterid) AND (ruleid = $ruleid)";
			$delSql = str_replace('(addordel = 1)','(addordel = 0)',$addSql);
		}
		$rsDel = $this -> _db -> GetRow($delSql);//是否以前有删除过
		$rsAdd = $this -> _db -> GetRow($addSql);//是否有增加过
		if($rsDel && !$rsDel['baserule']) $rsDel['baserule'] = '000000000';
		if($rsAdd && !$rsAdd['baserule']) $rsAdd['baserule'] = '000000000';
		if($addOrdel){//增加
			$addStr = $updDelStr = '';	//需要增加的，需要修改删除的
			if($rsDel){					//之前有删除过
				if($name){				//是附加值
					$arr = explode('#',$rsDel['configvalue']);
					$newArr = explode('#',$newStr);
					$newAdd = array();
					foreach ($newArr as $v){
						if(($k=array_search($v,$arr))!==false){	//如果在之前删除过里面的话
							unset($arr[$k]);	//将原始删除的项去掉，即不删除
						}else{
							$newAdd[]=$v;		//如果不在，则表示直接增加
						}
					}
					$updDelStr = implode('#',$arr);
					//修改删除的记录
					if($this -> _parseAttachStr(&$updDelStr)){
						$updateSql = 'UPDATE '.WEB_ADMIN_TABPOX."attachrule SET configvalue = '$updDelStr' WHERE attachruleid = ".$rsDel['attachruleid'];
						$this -> _db -> Execute($updateSql);
					}
					//修改要增加的记录
					if ($rsAdd) {	//有插入的记录
						$arr = explode('#',$rsAdd['configvalue']);
						foreach ($arr as $v){				//将原来的增加权限加上
							if(!in_array($v,$newAdd))	$newAdd[]=$v;
						}
						$addStr    = implode('#',$newAdd);
						$this -> _parseAttachStr(&$addStr);
						$insertSql = 'UPDATE '.WEB_ADMIN_TABPOX."attachrule SET configvalue = '$addStr' WHERE attachruleid = ".$rsAdd['attachruleid'];
					}else{
						$this -> _parseAttachStr(&$addStr);
						$insertSql = 'INSERT INTO '.WEB_ADMIN_TABPOX.'attachrule(userorgrouporrole,userorgrouporroleid,addordel,ruleid,otherruleid,configvalue,importer)'.
									 "VALUES($getterType,$getterid,1,$ruleid,$otherid,'$addStr',".$this->operater.')';
					}
					
					if(strlen($addStr)>0)	$this -> _db -> Execute($insertSql);
				}else{			//是基本权限
					for ($i=0; $i<9; $i++){
						if($rsDel['baserule'][$i]){
							if ($newStr[$i]) {
								$updDelStr .='0';
								$addStr    .='0';
							}else{
								$updDelStr .='1';
								$addStr	   .='0';
							}
						}else{
							if($newStr[$i]) {
								$updDelStr .='0';
								$addStr    .='1';
							}else{
								$updDelStr .='0';
								if($rsAdd){
									if($rsAdd['baserule'][$i]){
										$addStr .='1';
									}else{
										$addStr .='0';
									}
								}else{
									$addStr .='0';
								}
							}
						}
					}
//					echo 'source str:'.$rsAdd['baserule'][$i].',in str:'.$newStr[$i].'.out updadd = '.$updAddStr[$i].',del='.$delStr[$i].'<br>';
					$updateSql = 'UPDATE '.WEB_ADMIN_TABPOX."attachrule SET baserule ='$updDelStr' WHERE attachruleid = ".$rsDel['attachruleid'];
					$this -> _db -> Execute($updateSql);
					if($rsAdd){
						$insertSql = 'UPDATE '.WEB_ADMIN_TABPOX."attachrule SET baserule ='$addStr' WHERE attachruleid = ".$rsAdd['attachruleid'];
					}else{
						$insertSql = 'INSERT INTO '.WEB_ADMIN_TABPOX.'attachrule(userorgrouporrole,userorgrouporroleid,addordel,ruleid,baserule,importer)'.
									 "VALUES($getterType,$getterid,1,$ruleid,'$addStr',".$this->operater.')';
					}
					
					$this -> _db -> Execute($insertSql);
				}
			}else{//没有删除过，直接增加
				if($name){				//是附加值
					if($rsAdd){
						$arr = explode('#',$rsAdd['configvalue']);
						$newArr = explode('#',$newStr);
						foreach ($newArr as $v){
							if(!in_array($v,$arr))	$arr[]=$v;
						}
						$addStr = implode('#',$arr);
						$this -> _parseAttachStr(&$addStr);
						$insertSql = 'UPDATE '.WEB_ADMIN_TABPOX."attachrule SET configvalue = '$addStr' WHERE attachruleid = ".$rsAdd['attachruleid'];
					}else{
						$this -> _parseAttachStr(&$newStr);
						$insertSql = 'INSERT INTO '.WEB_ADMIN_TABPOX.'attachrule(userorgrouporrole,userorgrouporroleid,addordel,ruleid,otherruleid,configvalue,importer)'.
									 "VALUES($getterType,$getterid,1,$ruleid,$otherid,'$newStr',".$this->operater.')';
					}
					
					$this -> _db -> Execute($insertSql);
				}else{
					if($rsAdd){
//						echo '有增加过';
//						print_r($rsAdd);
						for($i=0;$i<9;$i++){
							$rsAdd['baserule'][$i] = ($rsAdd['baserule'][$i] || $newStr[$i])?'1':'0';
						}
						$insertSql = 'UPDATE '.WEB_ADMIN_TABPOX."attachrule SET baserule ='".$rsAdd['baserule']."' WHERE attachruleid = ".$rsAdd['attachruleid'];
					}else{
						$insertSql = 'INSERT INTO '.WEB_ADMIN_TABPOX.'attachrule(userorgrouporrole,userorgrouporroleid,addordel,ruleid,baserule,importer)'.
									 "VALUES($getterType,$getterid,1,$ruleid,'$newStr',".$this->operater.')';
					}
					
					$this -> _db -> Execute($insertSql);
				}
			}
		}else{//增加完毕  删除开始
			$delStr = $updAddStr = '';
			if($rsAdd){ //如果之前有增加过
				if($name){
					$arr = explode('#',$rsAdd['configvalue']);
					$newArr = explode('#',$newStr);
					$newDel = array();
					foreach ($newArr as $v){
						if(($k=array_search($v,$arr))!==false){
							unset($arr[$k]);
						}else{
							$newDel[]=$v;
						}
					}
					$updAddStr = implode('#',$arr);
					if($this -> _parseAttachStr(&$updAddStr)){
						$updateSql = 'UPDATE '.WEB_ADMIN_TABPOX."attachrule SET configvalue = '$updAddStr' WHERE attachruleid = ".$rsAdd['attachruleid'];
						$this -> _db -> Execute($updateSql);
					}
					if ($rsDel) {
						$arr = explode('#',$rsDel['configvalue']);
						foreach ($arr as $v){
							if(!in_array($v,$newDel))	$newDel[]=$v;
						}
						$delStr    = implode('#',$newDel);
						$this -> _parseAttachStr(&$delStr);
						$insertSql = 'UPDATE '.WEB_ADMIN_TABPOX."attachrule SET configvalue = '$delStr' WHERE attachruleid = ".$rsDel['attachruleid'];
					}else{
						$this -> _parseAttachStr(&$delStr);
						$insertSql = 'INSERT INTO '.WEB_ADMIN_TABPOX.'attachrule(userorgrouporrole,userorgrouporroleid,addordel,ruleid,otherruleid,configvalue,importer)'.
									 "VALUES($getterType,$getterid,0,$ruleid,$otherid,'$delStr',".$this->operater.')';
					}
				
					$this -> _db -> Execute($insertSql);
				}else{ //附加权处理完毕 基本权限开始
					for ($i=0; $i<9; $i++){
						if($rsAdd['baserule'][$i]){
							if ($newStr[$i]) {
								$updAddStr .='0';
								$delStr    .='0';
							}else{
								$updAddStr .='1';
								$delStr	   .='0';
							}
						}else{
							if($newStr[$i]) {
								$updAddStr .='0';
								$delStr    .='1';
							}else{
								$updAddStr .='0';
								if($rsDel){
									if($rsDel['baserule'][$i]){
										$delStr .='1';
									}else{
										$delStr .='0';
									}
								}else{
									$delStr .='0';
								}
							}
						}
//						echo 'source str:'.$rsAdd['baserule'][$i].',in str:'.$newStr[$i].'.out updadd = '.$updAddStr[$i].',del='.$delStr[$i].'<br>';
					}
					$updateSql = 'UPDATE '.WEB_ADMIN_TABPOX."attachrule SET baserule ='$updAddStr' WHERE attachruleid = ".$rsAdd['attachruleid'];
					$this -> _db -> Execute($updateSql);
					if($rsDel){
						$insertSql = 'UPDATE '.WEB_ADMIN_TABPOX."attachrule SET baserule ='$delStr' WHERE attachruleid = ".$rsDel['attachruleid'];
					}else{
						$insertSql = 'INSERT INTO '.WEB_ADMIN_TABPOX.'attachrule(userorgrouporrole,userorgrouporroleid,addordel,ruleid,baserule,importer)'.
									 "VALUES($getterType,$getterid,0,$ruleid,'$delStr',".$this->operater.')';
					}
					
					$this -> _db -> Execute($insertSql);
				}
			}else{//没有增加过，直接删除
				if($name){				//是附加值
					if($rsDel){
						$arr = explode('#',$rsDel['configvalue']);
						$newArr = explode('#',$newStr);
						foreach ($newArr as $v){
							if(!in_array($v,$arr))	$arr[]=$v;
						}
						$delStr = implode('#',$arr);
						$this -> _parseAttachStr(&$delStr);
						$insertSql = 'UPDATE '.WEB_ADMIN_TABPOX."attachrule SET configvalue = '$delStr' WHERE attachruleid = ".$rsDel['attachruleid'];
					}else{
						$this -> _parseAttachStr(&$newStr);
						$insertSql = 'INSERT INTO '.WEB_ADMIN_TABPOX.'attachrule(userorgrouporrole,userorgrouporroleid,addordel,ruleid,otherruleid,configvalue,importer)'.
									 "VALUES($getterType,$getterid,0,$ruleid,$otherid,'$newStr',".$this->operater.')';
					}
					
					$this -> _db -> Execute($insertSql);
				}else{
					if($rsDel){
						for($i=0;$i<9;$i++){
							$rsDel['baserule'][$i] = ($rsDel['baserule'][$i] || $newStr[$i])?'1':'0';
						}
						$insertSql = 'UPDATE '.WEB_ADMIN_TABPOX."attachrule SET baserule ='".$rsDel['baserule']."' WHERE attachruleid = ".$rsDel['attachruleid'];
					}else{
						$insertSql = 'INSERT INTO '.WEB_ADMIN_TABPOX.'attachrule(userorgrouporrole,userorgrouporroleid,addordel,ruleid,baserule,importer)'.
									 "VALUES($getterType,$getterid,0,$ruleid,'$newStr',".$this->operater.')';
					}
					
					$this -> _db -> Execute($insertSql);
				}
			}
		}
		$this -> _db -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'attachrule WHERE baserule = \'00000\' AND configvalue = \'\'');
	}
	function _parseAttachStr(&$str){
		if(strlen($str)==0) return false;
		if($str[0]=='#') $str = substr($str,1);
		if($str[strlen($str)-1]=='#') $str = substr($str,0,-1);
		$str = preg_replace('/#+/','#',$str);
		if(strlen($str)>0) return true;
		else return false;
	}
	/**
	 * 删除一个角色
	 *
	 * @param Int $roleid 角色ID
	 */
	function delRole($roleid){
		$sql[] = 'delete from '.WEB_ADMIN_TABPOX.'grouprole where roleid='.$roleid;
		$sql[] = 'delete from '.WEB_ADMIN_TABPOX.'rolerule where roleid='.$roleid;
		$sql[] = 'delete from '.WEB_ADMIN_TABPOX.'attachrule where userorgrouporrole = 3 and userorgrouporroleid='.$roleid;
		$sql[] = 'delete from '.WEB_ADMIN_TABPOX.'role where roleid='.$roleid;
		foreach ($sql as $s)$this->_db->Execute($s);
	}
	/**
	 * 删除一个工作组
	 *
	 * @param Int $groupid 工作组ID
	 */
	function delGroup($groupid){
		$sql[] = 'delete from '.WEB_ADMIN_TABPOX.'usergroup where groupid='.$groupid;
		$sql[] = 'delete from '.WEB_ADMIN_TABPOX.'grouprole where groupid='.$groupid;
		$sql[] = 'delete from '.WEB_ADMIN_TABPOX.'attachrule where userorgrouporrole =2 and userorgrouporroleid='.$groupid;
		$sql[] = 'delete from '.WEB_ADMIN_TABPOX.'group where groupid='.$groupid;
		foreach ($sql as $s)$this->_db->Execute($s);
	}
	/**
	 * 删除一个用户
	 *
	 * @param Int $userid 用户ID
	 */
	function delUser($userid){
		$sql[] = 'delete from '.WEB_ADMIN_TABPOX.'usergroup where userid='.$userid;
		$sql[] = 'delete from '.WEB_ADMIN_TABPOX.'user where userid='.$userid;
		$sql[] = 'delete from '.WEB_ADMIN_TABPOX.'attachrule where userorgrouporrole =1 and userorgrouporroleid='.$userid;
		foreach ($sql as $s)$this->_db->Execute($s);
	}
	/**
	 * 删除一个权限
	 *
	 * @param Int $ruleid 权限的ID
	 */
	function delRule($ruleid){
		$sql[] = 'delete from '.WEB_ADMIN_TABPOX.'attachrule where ruleid='.$ruleid;
		$sql[] = 'delete from '.WEB_ADMIN_TABPOX.'rolerule where ruleid='.$ruleid;
		$sql[] = 'delete from '.WEB_ADMIN_TABPOX.'rule where ruleid='.$ruleid;
		$sql[] = 'delete '.WEB_ADMIN_TABPOX.'config,'.WEB_ADMIN_TABPOX.'ruleconfig from
 				 '.WEB_ADMIN_TABPOX.'config, '.WEB_ADMIN_TABPOX.'ruleconfig where 
 				 '.WEB_ADMIN_TABPOX.'config.ruleconfigid = '.WEB_ADMIN_TABPOX.'ruleconfig.ruleconfigid and '.WEB_ADMIN_TABPOX.'ruleconfig.ruleid = '.$ruleid;
		foreach ($sql as $s)$this->_db->Execute($s);
	}
	/**
	 * 初始化搜索数据，用于附加值里面包括“SQL特殊值”的替换方式
	 */
	function initSearchDate(){
		if(!$this -> serach){
			$currStr = $this -> _db -> GetOne('select rulestr from '.WEB_ADMIN_TABPOX.'login where userid = '.$this -> operater);
			$curPower = '';
			if($currStr){
				$curPower = implode(',' , array_keys( unserialize( $currStr ) ) );
			}
			if($curPower == '')	$curPower = "''";
			$this -> search = array('/当前用户/','/当前时间/','/当前权限/');
			$this -> replace = array($this -> operater,"'".date('Y-m-d H:i:s')."'",$curPower);
		}
	}
	/**
	 * 将SQL语句中的特定词替换，并返回对应的结果
	 *
	 * @param &Array $rsArr
	 */
	function parseSqlData(&$rsArr){
		$this -> _db -> SetFetchMode(ADODB_FETCH_NUM);
		if(isset($rsArr['configname']) && '[sql]' == substr($rsArr['configname'],0,5)){	//名称
			$this -> initSearchDate();
			$rsArr['configname'] = preg_replace($this->search,$this->replace,$rsArr['configname']);
			$v = $this -> _db -> GetOne(substr($rsArr['configname'],5));
			if($v === false){
				echo '<br>Error Sql:'.substr($rsArr['configname'],5).'<br>';
			}else{
				$rsArr['configname'] = $v;
			}
		}
		if(isset($rsArr['configvalue']) && '[sql]' == substr($rsArr['configvalue'],0,5)){	//值文本
			$this -> initSearchDate();
			$str = '';
			$rsArr['configvalue'] =  preg_replace($this->search,$this->replace,$rsArr['configvalue']);
			$rs = $this -> _db -> GetArray(substr($rsArr['configvalue'],5));
			if($rs === false){
				echo '<br>Error Sql:'.substr($rsArr['configvalue'],5).'<br>';
			}else{
				foreach ($rs as $v){
					if($str == '') $str = $v[0].'='.$v[1];
					else 		   $str.= '#'.$v[0].'='.$v[1];
				}
			}
			$rsArr['configvalue'] = $str;
		}
		if (isset($rsArr['configdefault']) && !is_array($rsArr['configdefault']) && '[sql]' == substr($rsArr['configdefault'],0,5)) {	//默认值
			$this -> initSearchDate();
			$str = '';
			$rsArr['configdefault'] =  preg_replace($this->search,$this->replace,$rsArr['configdefault']);
			$rs = $this -> _db -> GetArray(substr($rsArr['configdefault'],5));
			if($rs === false){
				echo '<br>Error Sql:'.substr($rsArr['configdefault'],5).'<br>';
			}else{
				foreach ($rs as $v){
					if($str == '') $str = $v[0];
					else 		   $str.= '#'.$v[0];
				}
			}
			$rsArr['configdefault'] = $str;
		}
		$this -> _db -> SetFetchMode(ADODB_FETCH_ASSOC);
	}
}
?>