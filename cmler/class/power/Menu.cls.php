<?php
/**
 * @desc 二级菜单生成类
 * @author  Vanni
 * @copyright 1.0
 * @package power
 */
class Menu{
	/** @var 数据库操作对象	*/
	var $_db;
	
	/** @var 表名*/
	var $_table;
	
	/** @var 表的各字段*/
	var $_table_field;
		
	/** @var 表的主键*/
	var $_primary = 'ruleid';
	
	/** @var 表的外键*/
	var $_foreign = 'parentruleid';
	
	/** @var 表的层次字段*/
	var $_layerKey = 'layer';
	
	/** @var 排序字段*/
	var $_sortKey = 'ruleorder';
	
	/**
	@desc 构造函数
	@param $table String 表名
	*/
	function Menu(&$db,$table=null){
		$this->_db = &$db;
		$this->_table = $table ? $table : (WEB_ADMIN_TABPOX . 'rule');
		$this->_table_field = array('ruleid','rulename','parentruleid','ruleimg','ruleurl','ruleorder','layer','importer','createtime');
	}
	############################################### 私有方法 ###################################################
	/**
	@desc 私有方法。返回一句SQL语句
	@return String SQL语句
	*/
	function getSql($dls,$mids,$join='INNER'){
		$sql = "";								//最终SQL语句
		$field = $link = ''; 					//字段，条件
		$fieldArr = $this->_table_field;		//字段
		for ($i=$dls; $i>0; $i--){
			if ($i==$dls){
				$tempField = '';
				foreach ($fieldArr as $v){
					if ($tempField == '') $tempField = 'a'.$i.'.'.$v.' AS '.$v.$i;
					else $tempField .= ', a'.$i.'.'.$v.' AS '.$v.$i;
				}
				$field  = $tempField;
				$link   = $this->_table.' a'.$i;
			} else {
				$tempField = '';
				foreach ($fieldArr as $v){
					if ($tempField == '') $tempField = 'a'.$i.'.'.$v.' AS '.$v.$i;
					else $tempField .= ', a'.$i.'.'.$v.' AS '.$v.$i;
				}
				$field .= ', '.$tempField;
				$link  .= ' '.$join.' JOIN '.$this->_table.' a'.$i.' ON a'.($i+1).'.'.$this->_foreign.' = a'.$i.'.'.$this->_primary;
			}
		}
		$sql = 'SELECT '.$field.' FROM '.$link;
		if ($mids) $sql .= ' WHERE (a'.$dls.'.'.$this->_primary.' IN('.$mids.'))';
		return $sql;
	}
	/**
	@desc 内部函数，将多维数组转成二维数组
	@return Array[][]
	*/
	function gosort($out,&$out2){
		usort($out,create_function('$a,$b','if($a["'.$this->_sortKey.'"]==$b["'.$this->_sortKey.'"])return 0;return $a["'.$this->_sortKey.'"]>$b["'.$this->_sortKey.'"]?1:-1;'));
		foreach ($out as $v){
			$temp = array();
			foreach ($v as $ink=>$inv){
				if (!is_array($inv))
					$temp[$ink]=$inv;
			}
			if (count(@$v['submenu'])>0) {
				$out2[]=$temp;
				$this->gosort($v['submenu'],&$out2);
			}else{
				$out2[]=$temp;
			}
		}
	}
	############################################### 私有方法 ###################################################
	
	
	############################################### 公共方法 ###################################################
	/**
	@desc 得到指字菜单的所有父级菜单
	@param $mid Int 菜单ID
	@param $dls Int 层次，默认为空，如果为空，系统自动查找此菜单的层次
	@return Array 二维数据，将先后顺序排序
	*/
	function getRelating($mid,$dls=null){
		$outArr = array();
		if ($dls==null)
			$dls = $this -> _db -> getOne("SELECT {$this->_layerKey} FROM {$this->_table} WHERE {$this->_primary}={$mid}");	//手动得到层次
		$tempArr = $this -> _db -> getRow( $this->getSql($dls,$mid) );
		for ($i=0; $i<$dls; $i++){
			foreach ($this->_table_field as $v){
				$outArr[$i][$v]=$tempArr[$v.($i+1)];
			}
		}
		return $outArr;
	}
	/**
	@desc 得到指定菜单ID的所有下级菜单
	@param $mid Int 菜单的ID号，0表示根菜单
	@param $mids String 权限字符串，如果为空表示查找所有菜单，支持形式如：id-1111,id-1010,....
	@return array[][].. 多维数组
	*/
	function getMenuItem($mid,$mids=null){ //123-1111,1234-1111
		//返回所有菜单
		if ($mids==null)	return $this->getAllMenuItem($mid);
		//去除权限字符
//		$mids = preg_replace(array('/^,/','/,$/'),array('',''),$mids);
		$maxLay = $this -> _db -> getOne("SELECT MAX({$this->_layerKey}) FROM {$this->_table} WHERE ({$this->_primary} IN({$mids}))");
	 	$sql = $this->getSql($maxLay,$mids,'LEFT');
		$thers  = $this -> _db -> Execute( $sql );
		$tempArr = array();		//记录已经存在的菜单
		$out	 = array(); 	//用于返回的最终结果
		$ind 	 = array(); 	//保存父级ID的索引数组
		if ($thers) {
			while (!$thers->EOF && $thers) {
				for ($i=1; $i<($maxLay+1); $i++){	//最大的层次+1
					if ( $thers->fields[$this->_primary.$i] ){	//如果存在的话
						if (!in_array($thers->fields[$this->_primary.$i],$tempArr)){	//菜单是否已经存在了
							$tempArr[] = $thers->fields[$this->_primary.$i];			//将菜单加入数组
							$temp = array();
							foreach ($this->_table_field as $v)		$temp[$v]=$thers->fields[$v.$i];
							$temp['submenu'] = array();								//初始化一下菜单项
							if($thers->fields[$this->_foreign.$i] == $mid) {			//要显示的菜单
								$j = count($out);
								$out[$j] = $temp;
								$ind[$thers->fields[$this->_primary.$i]] = &$out[$j];
							}else { 
								$j = @count($ind[$thers->fields[$this->_foreign.$i]]['submenu']); 
								$ind[$thers->fields[$this->_foreign.$i]]['submenu'][$j] = $temp; 
								$ind[$thers->fields[$this->_primary.$i]] = &$ind[$thers->fields[$this->_foreign.$i]]['submenu'][$j]; 
							}
						}else{
							continue;
						}
					}
				}
				$thers->MoveNext();
			}
		$thers->Close();
		}
		return $out;
	}
	/**
	@desc 得到所有菜单项
	*/
	function getAllMenuItem($mid){
		$rs = $this -> _db -> getArray("SELECT * FROM ".$this->_table);
		uasort($rs,create_function('$a,$b','if($a["'.$this->_foreign.'"]==$b["'.$this->_foreign.'"]) return 0;return $a["'.$this->_foreign.'"]>$b["'.$this->_foreign.'"]?1:-1;'));

		$out = array(); 
	    $ind = array(); 

	    $break = 0;			//退出条件
	    $nextar = Array();	// 保存末定义的父节点的节点. 
		while(true){
			foreach($rs as $v) { 
				$v['submenu'] = array();
				if($v[$this->_foreign] == $mid) { 
					$i = count($out); 
					$out[$i] = $v; 
					$ind[$v[$this->_primary]] =& $out[$i];
				}else { 
					if(!is_array(@$ind[$v[$this->_foreign]])){ 
						$nextar[] = $v; 
						continue; 
					}else {
						$i = count($ind[$v[$this->_foreign]]['submenu']); 
						$ind[$v[$this->_foreign]]['submenu'][$i] = $v; 
						$ind[$v[$this->_primary]] =& $ind[$v[$this->_foreign]]['submenu'][$i]; 
					}
				} 
			}
			$b = count($nextar);
			if($b == 0 || $b == $break)	break; 
			else{
				$rs = $nextar; 
				$break = $b;
				$nextar = Array(); 
			}
		}
		return $out;
	}
	/**
	@desc 以二维数组的形式返回指定菜单的所有下级菜单
	@param $mid Int 菜单ID
	@param $allowStr String 权限字符串
	@return Array[][]
	*/
	function getMenuItemToArray2($mid,$allowStr=null){
		$out2=array();
		$out = $this->getMenuItem($mid,$allowStr);
		$this->gosort($out,&$out2);
		return $out2;
	}
	/**
	@desc 删除一个菜单
	*/
	function delMenuItem($mid){
		require_once(WEB_ADMIN_CLASS_PATH.'/power/Power.cls.php');
		$p = new Power(&$this->_db,null);
		$rs = $this->getMenuItemToArray2($mid);
		$rs[count($rs)]['ruleid']=$mid;
		foreach ($rs as $v){
			$imgfiles = $this -> _db -> GetRow('SELECT ruleimg,rulebigimg FROM '.WEB_ADMIN_TABPOX.'rule WHERE ruleid = '.$v['ruleid']);
			if($imgfiles['ruleimg']) unlink(WEB_ADMIN_PHPCOMMON.'/img/ico/'.$imgfiles['ruleimg']);
			if($imgfiles['rulebigimg']) unlink(WEB_ADMIN_PHPCOMMON.'/img/ico/'.$imgfiles['rulebigimg']);
			$p -> delRule($v['ruleid']);
		}
	}
	/**
	@desc 增加一个菜单
	@param $insArr Array 数据
	*/
	function insMesuItem($insArr){
		if(isset($insArr['ruleid']))unset($insArr['ruleid']);
		$rs = &$this->_db->Execute('SELECT * FROM '.$this->_table.' WHERE ruleid=-1');
		$maxid = $this -> _db -> GetOne('SELECT MAX(ruleorder) FROM '.$this->_table.' WHERE parentruleid = '.$insArr['parentruleid']);
		$maxid += 1;
		$insArr['ruleorder'] = $maxid;
		$sql = $this->_db->GetInsertSQL($rs,$insArr,get_magic_quotes_gpc());
		$this -> _db -> Execute($sql);
	}
	/**
	@desc 跟据菜单名返回ID号
	@return Int
	*/
	function getMenuId($name){
		return $this -> _db -> getOne("SELECT ruleid FROM ".$this->_table." WHERE rulename = '$name'");
	}
	/**
	@desc 得到所有子菜单的ID字符串
	@return Array ID数组
	*/
	function getMenuItemId($mid,$mids=null){
		$outStr = array();
		$menuItem = $this->getMenuItemToArray2($mid,$mids);
		foreach ($menuItem as $v){
			$outStr[]  = $v['ruleid'];
		}
		return $outStr;
	}
	/**
	@desc 得到子菜单的数据，只返回一层数据
	*/
	function getSubMenuItem($mid,$mids=null){
		$out = $this->getMenuItem($mid,$mids);
		foreach ($out as $k=>$v){
			if (is_array($v['submenu']))	unset($out[$k]['submenu']);
		}
		$out2 = array();
		$this->gosort($out,&$out2);
		return $out2;
	}
	/**
	@desc 得到菜单的名字
	*/
	function getMenuName($id){
		return $this -> _db -> getOne('SELECT rulename FROM '.$this->_table.' WHERE ruleid = '.$id);
	}
	/**
	@desc 得到类似下拉树状的数组
	*/
	function getMenuTreeArr($mid,$allowStr=null){
		$arr = $this->getMenuItemToArray2($mid,$allowStr);
		$j = count($arr);
		for ($i=0; $i<$j; $i++){
			$space = '';
			for ($k = 0; $k<$arr[$i]['layer']-1; $k++)$space .= '│';
			if($arr[$i]['layer']>@$arr[$i+1]['layer'])$space .= '└ ';
			else $space .= '├ ';
			$arr[$i]['space'] = $space;
		}
		return $arr;
	}
	/**
	@desc 移动菜单
	*/
	function moveMenuPoint($ruleid,$parent,$topoint,$layer){
		$thisRule = $this -> _db -> GetRow('SELECT * FROM '.$this->_table.' WHERE ruleid = '.$ruleid);
		if ($thisRule['parentruleid'] != $parent) {
			$s = 'update '.$this->_table.' set ruleorder = ruleorder - 1 where parentruleid = '.$thisRule['parentruleid'].' and ruleorder > '.$thisRule['ruleorder'];
			$this -> _db -> Execute($s);
			$s = 'update '.$this->_table.' set ruleorder = ruleorder + 1 where parentruleid = '.$parent.' and ruleorder >= '.$topoint;
			$this -> _db -> Execute($s);
		}else {
			if($thisRule['ruleorder'] > $topoint){	//从大到小
				$s = 'update '.$this->_table.' set ruleorder = ruleorder + 1 where parentruleid = '.$parent
					.' and ruleorder < '.$thisRule['ruleorder']
					.' and ruleorder >='.$topoint;
				$this -> _db -> Execute($s);
			}elseif ($thisRule['ruleorder'] < $topoint){//从小到大
				$s = 'update '.$this->_table.' set ruleorder = ruleorder - 1 where parentruleid = '.$parent
					.' and ruleorder > '.$thisRule['ruleorder']
					.' and ruleorder <='.$topoint;
				$this -> _db -> Execute($s);
			}else{
				return;
			}
		}
		if($thisRule['layer'] != $layer){
			//移动层次
			$subArr = $this -> getMenuItemToArray2($ruleid);
			foreach ($subArr as $v){
				$this -> _db -> Execute('update '.$this->_table.' set layer = layer +'.($layer - $thisRule['layer']).' where ruleid='.$v['ruleid']);
			}
		}
		$this -> _db -> Execute('update '.$this->_table.' set ruleorder = '.$topoint.' where ruleid = '.$thisRule['ruleid']);
	}
	/**
	 * 有子菜单？
	 */
	function haveSubMenu($menuid){
		return $this -> _db -> GetOne('select ruleid from '.$this->_table.' where parentruleid = '.$menuid);
	}
	
	
	    function cugleMenutree($mid,$allowStr=null){
		$arr = $this->getMenuItemToArray2($mid,$allowStr);
		$j = count($arr);
		for ($i=0; $i<$j; $i++){
			$space = '';
			
			if($this->haveSubMenu($arr[$i]['ruleid'])){$space.=" ";}
			for ($k = 0; $k<$arr[$i]['layer']-1; $k++)$space .= ' ';
			if($arr[$i]['layer']>@$arr[$i+1]['layer'])
			{$space .= '<li class=folder1><A onclick=Exp(this) HREF=javascript:loader("../'.$arr[$i]['ruleurl'].'") style="color=#000000"><img src="'.WEB_ADMIN_HTTPPATH.'/common/img/l.gif" align=absMiddle border=0> '.$arr[$i]['rulename'].'</A></li>';
			for($x=0;@$x<$arr[$i]['layer']-@$arr[$i+1]['layer'];$x++){
			$space .=  '</ul></li>';
			}
			}
			else {
			
			if($this->haveSubMenu($arr[$i]['ruleid'])){$space .= '<li class=folder><A onclick=Exp(this) HREF="#">'.$arr[$i]['rulename'].'</A><UL class="folder disnone">';}else{$space .= '<li class=folder1><A onclick=Exp(this) HREF=javascript:loader("../'.$arr[$i]['ruleurl'].'") style="color=#000000"><img src="'.WEB_ADMIN_HTTPPATH.'/common/img/c.gif" align=absMiddle border=0> '.$arr[$i]['rulename'].'</A></li>';}
			}
			$arr[$i]['space'] = $space;
		}
		return $arr;
	
	}
}
?>