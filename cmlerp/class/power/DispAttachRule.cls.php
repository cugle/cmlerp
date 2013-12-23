<?
require_once(WEB_ADMIN_CLASS_PATH.'/power/Power.cls.php');
/**
 * @desc 扩展属性或扩展权限显示类
 * @author  Vanni
 * @version 1.0
 * @package power
 */
class DispAttachRule{
	/**
	 * 权限对象
	 * @var &Object
	 */
	var $pObj = null;
	/**
	 * 数据库操作对象
	 *
	 * @var Object
	 */
	var $dbObj = null;
	
	/**
	 * 构造函数
	 *
	 * @param &dbObj $db 数据库操作对象
	 * @param Int $operater 操作者
	 * @return DispAttachRule
	 */
	function DispAttachRule(&$db,$operater){
		$this -> dbObj = & $db;
		$this -> pObj = new Power(& $db,$operater);
	}
	/**
	 * 附加值基本显示方法
	 *
	 * @param Int|Array $otherRule 要显示的数据或附加值ID
	 * @param String $defaultValue 默认值
	 * @param String $pattern1 分隔符
	 * @param Int $num 分隔数
	 * @param String $pattern2 至分隔数时的分隔符
	 * @return Array
	 */
	function disp($otherRule,$defaultValue=null,$pattern1=null,$num=null,$pattern2=null){
		//如果不是数组，获得数据
		if(!is_array($otherRule)){
			$otherRule = $this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'otherrule where otherruleid='.$otherRule);
		}
		//没有默认值，设置为自己的默认值
		if($defaultValue==null)	$defaultValue = $otherRule['configdefault'];
		if($pattern1==null) 	$pattern1 = " \n";
		if($pattern2==null) 	$pattern2 = '<br>';
		
		//如果是查询语句，用SQL的查询结果做为数据源
		$this -> pObj -> parseSqlData(&$otherRule);
		$tmps['configdefault'] = & $defaultValue;
		$this -> pObj -> parseSqlData(&$tmps);
		
		$i = 0;
		$return = array('name'=>$otherRule['configname'],'value'=>'');
		switch ($otherRule['configtype']) {
			case 'text':
				$return['value'] = '<input name="attachs['.$otherRule['ruleid'].']['.$otherRule['otherruleid'].']" id="attachs'.$otherRule['otherruleid'].'" type="text" value="'.$defaultValue.'" maxlength="'.$otherRule['maxlength'].'">';
				break;
			case 'radio':
				$content = split('#',$otherRule['configvalue']);
				foreach ($content as $inv){
					$val = split('=',$inv);
					if(count($val)<2) $val[1] = $val[0];
					$p = '';
					if($num!=null && $num>1){	$p = $i++%$num==0?$pattern2:$pattern1;	}
					if ($return['value']=='')
						$return['value'] = "<input type='radio' name='attachs[".$otherRule['ruleid'].']['.$otherRule['otherruleid']."]' id='attachs{$otherRule['otherruleid']}' value='{$val[1]}'".(($val[1]==$defaultValue)?(' checked'):('')).'>'.$val[0];
					else
						$return['value'] .= $p."<input type='radio' name='attachs[".$otherRule['ruleid']."][{$otherRule['otherruleid']}]' id='attachs{$otherRule['otherruleid']}' value='{$val[1]}'".(($val[1]==$defaultValue)?(' checked'):('')).'>'.$val[0];
				}
				break;
			case 'checkbox':
				$content = split('#',$otherRule['configvalue']);
				if(!is_array($defaultValue)){
					$dfvs = split('#',$defaultValue);//默认值
				}else{
					$dfvs = $defaultValue;
				}
				foreach ($content as $inv){
					$val = split('=',$inv);
					$p = '';
					if($num!=null && $num>1){	$p = $i++%$num==0?$pattern2:$pattern1;	}
					if($return['value']=='')
						$return['value'] .= "<input type='checkbox' name='attachs[{$otherRule['ruleid']}][{$otherRule['otherruleid']}][]' id='attachs{$otherRule['otherruleid']}' value='{$val[1]}'".((in_array($val[1],$dfvs))?(' checked'):('')).'>'.$val[0];
					else
						$return['value'] .= $p."<input type='checkbox' name='attachs[".$otherRule['ruleid']."][{$otherRule['otherruleid']}][]' id='attachs{$otherRule['otherruleid']}' value='{$val[1]}'".((in_array($val[1],$dfvs))?(' checked'):('')).'>'.$val[0];
				}
				break;
			case 'select':
				$return['value'] = "<select name='attachs[{$otherRule['ruleid']}][{$otherRule['otherruleid']}]' id='attachs{$otherRule['otherruleid']}'>";
				$content = split('#',$otherRule['configvalue']);
				foreach ($content as $inv){
					$val = split('=',$inv);
					$return['value'] .= "<option value='{$val[1]}'".(($val[1]==$defaultValue)?(' selected'):('')).">{$val[0]}</option>";
				}
				break;
		}
		return $return;
	}
	/**
	 * 用于显示角色显示页的
	 *
	 * @param Int|Array $otherid 要显示的数据或附加值ID
	 * @param String $defaultValue 默认值
	 * @param String $pattern1 分隔符
	 * @param Int $num 分隔数
	 * @param String $pattern2 至分隔数时的分隔符
	 * @return Array
	 */
	function dispValue($otherid,$defaultValue=null,$pattern1=null,$num=null,$pattern2=null){
		$otherRule = $this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'otherrule where otherruleid = '.$otherid);
		if($defaultValue==null)	$defaultValue = $otherRule['configdefault'];
		if($pattern1==null) 	$pattern1 = " , ";
		if($pattern2==null) 	$pattern2 = '<br>';
		$this -> pObj -> parseSqlData(&$otherRule);
		
		$i = 0;
		$return = array('name'=>$otherRule['configname'],'value'=>'');
		switch ($otherRule['configtype']){
			case 'text':
				$return['value'] = str_replace('#',',',$defaultValue);
			break;
			case 'radio':
			case 'select':
			case 'checkbox':
				$dfArr = split('#',$defaultValue);
				$content = split('#',$otherRule['configvalue']);
				foreach ($content as $inv){
					$val = split('=',$inv);
					$p=$pattern1;
					if(!isset($val[1]))$val[1]=$val[0];
					if(in_array($val[1],$dfArr)){
						if($num!=null && $num>0){	$p = ($i++%$num==0)?($pattern2):($pattern1);	}
						if($return['value']=='')$return['value'] = $val[0];
						else 					$return['value'] .= $p.$val[0];
					}
				}
			break;
		}
		return $return;
	}
	/**
	 * 显示可以用来设置的附加值，用于用户权限设置页，和组权限设置页
	 *
	 * @param Mixed $otherid 附加的菜单ID，或菜单的具体值数组
	 * @param Mixed $values 可选值或数组
	 * @param Mixed $defvalues 已选值或数组
	 * @param Booleand $disprule 是否显示可选值之外的值
	 * @param String $pattern1 分隔符
	 * @param Int $num 分隔数
	 * @param String $pattern2 当到底分隔数时，显示的分隔符
	 * @param String $defimg 显示默认值标志的图标
	 */
	function dispRule($otherid,$values,$defvalues,$disprule,$pattern1=null,$num=null,$pattern2=null,$defimg=null){
		if(!is_array($otherid)){
			$otherid = $this -> dbObj -> GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'otherrule WHERE otherruleid = '.$otherid);
		}
		$this -> pObj -> parseSqlData(&$otherid);
		$return = array('name'=>$otherid['configname'],'value'=>'');
		
		$defConVal = explode('#',$otherid['configvalue']);//可选值
		$cdf       = explode('#',$otherid['configdefault']);//附加权的默认值
		
		if($values!=null && !is_array($values))       $values    = explode('#',$values);
		if($defvalues!=null && !is_array($defvalues)) $defvalues = explode('#',$defvalues);
		
		if($values==null){	//当前用户没有权
			if($defvalues==null){	//要显示的用户没有权
				if($disprule){		//加秘显示
					foreach ($defConVal as $v){
						$vv = split('=',$v);
						if(!isset($vv[1]))$vv[1]=$vv[0];
						if(in_array($vv[1],$cdf)){
							$return['value'].= '√'.$vv[0].'　';
						}
					}
				}else{
					$return['value'] = '?';
				}
			}else{					//要显示的用户有权
				if($disprule){
					foreach ($defConVal as $v){
						$vv = split('=',$v);
						if(!isset($vv[1]))$vv[1]=$vv[0];
						if(in_array($vv[1],$defvalues)){
							$return['value'].= '√'.$vv[0].'　';
						}
					}
				}else{
					$return['value'] = '?';
				}
			}
		}else{
			foreach ($defConVal as $v){
				$vv = split('=',$v);
				if(!isset($vv[1]))$vv[1]=$vv[0];
				if(in_array($vv[1],$values)){
					if($defvalues && in_array($vv[1],$defvalues)){
						$return['value'] .= '<input type=checkbox checked name="attachs['.$otherid['ruleid'].']['.$otherid['otherruleid'].'][]" value="'.$vv[1].'">'.$vv[0].'　';
					}else{
						$return['value'] .= '<input type=checkbox name="attachs['.$otherid['ruleid'].']['.$otherid['otherruleid'].'][]" value="'.$vv[1].'">'.$vv[0].'　';
					}
				}elseif($defvalues && in_array($vv[1],$defvalues)){
					if($disprule){
						$return['value'] .= '√'.$vv[0].'　';
					}else{
						$return['value'] .= '?';
					}
				}
			}
		}
		return $return;
	}
	/**
	 * 附加值显示页面，批量转换显示
	 *
	 * @param &Array $arrRow
	 * @param String $pattern1
	 * @param Int $num
	 * @param String $pattern2
	 * @return Arrty
	 */
	function dispRow(&$arrRow,$pattern1=null,$num=null,$pattern2=null){
		$return = array();
		while ($rrs = $arrRow->FetchRow()) {
			$return[] = array_merge ($rrs,$this -> disp($rrs,null,$pattern1,$num,$pattern2));
		}
		return $return;
	}
}

?>