<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class PageUser extends admin {
    function Main()
    {
        if(isset($_GET['Action']) && $_GET['Action']=='statistics')
        {
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> statistics();
        }
        else
        {
            parent::Main();
        }
    }
	function statistics(){
		//定义模板
		$t = new Template('../template/system');
		$t -> set_file('f','statisticstrenddetail.html');	
		$t -> set_block('f','customer','c');
		$bgyear=$_POST['bgyear'];
		$bgmonth=$_POST['bgmonth'];
		$endyear=$_POST['endyear'];
		$endmonth=$_POST['endmonth'];
		$userid=$_POST['userid'];
		$areaid=$_POST['areaid'];
		$conditionuserid=$userid==0?"":" userid=".$userid;
		
		$conditionareaid=$areaid==0?"":" areaid=".areaid;
		if($conditionuserid!=""&&$conditionareaid!=""){
				$condition=" and ".$conditionuserid." and ".$conditionareaid;
		}elseif($conditionuserid==""&&$conditionareaid==""){
		$condition="";
		}else{
		$condition=" and ".$conditionuserid.$conditionareaid;
		}
		
		$length=($endyear-$bgyear)*12+($endmonth-$bgmonth);
		
		$datebg=$bgyear."-".$bgmonth."-01 00:00:00";
		$dateend=$endyear."-".$endmonth."-31 23:59:59";
		$classtype=$_POST['classtype'];
        $t -> set_var('datebg',$datebg);
		$t -> set_var('dateend',$dateend);
		$i=1;
        //$t -> set_var('customer');
		//echo "SELECT u.username ,count(*)  FROM ".WEB_ADMIN_TABPOX."customer c inner join ".WEB_ADMIN_TABPOX."user u on u.userid=c.userid group  by u.userid";
		
		$temp=$bgyear."-".$bgmonth;
		for($j=0;$j<$length+1;$j++)
		{
            $month=$temp;
			$tempbg=$temp."-01 00:00:00";
			$temp= date('Y-m', strtotime("+1 month", strtotime($temp)));
			
            $tempend=$temp."-01 00:00:00";
			
		    $rs1 = &$this -> dbObj -> Execute("SELECT count(*)  FROM ".WEB_ADMIN_TABPOX."customer   where createtime  between '".$tempbg."' and '".$tempend."' ".$condition);
			while ($rrs1 = &$rs1 -> FetchRow()) {
			
			$newcustomernumber=$rrs1['count(*)'];

			$username=$rrs1['username'];
			$t -> set_var('newcustomernumber',$newcustomernumber);
			$t -> set_var('month',$month);
			$t -> set_var('rank',$i);
			$i=$i+1;
		    $t -> parse('c','customer',true);	
		  	}		
		 }
			
			$rs1 -> Close();		

		
		//设置总增加客户数量
		    $rs3 = &$this -> dbObj -> Execute("SELECT count(*)  FROM ".WEB_ADMIN_TABPOX."customer  where createtime   between '".$datebg."' and '".$dateend."'".$condition);
			while ($rrs3 = &$rs3 -> FetchRow()) {
			
			$totalnewcustomernumber=$rrs3['count(*)'];
			$t -> set_var('totalnewcustomernumber',$totalnewcustomernumber);
			$t -> set_var('rank',$i);
			$i=$i+1;
		    $t -> parse('c','customer',true);			
			}
			
			$rs1 -> Close();
			$t -> set_var('length',$length);			
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');	
	}
	function disp(){
		//定义模板
		$t = new Template('../template/system');
		$t -> set_file('f','statisticstrend.html');
		$t -> set_var('arealist',$this->PPClass());
		$t -> set_var('userlist',$this->PPClass_sale());
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
		function PPClass_sale($userid=0){
    		$sale =$this -> dbObj -> Execute("SELECT * FROM ".WEB_ADMIN_TABPOX."grouprole gr, ".WEB_ADMIN_TABPOX."usergroup ug, ".WEB_ADMIN_TABPOX."user u WHERE gr.groupid = ug.groupid AND u.userid = ug.userid AND gr.roleid =3");
			$count=$sale->RecordCount();
			$i=0;
			$arrs="";
			while ($rrs = &$sale -> FetchRow()) {
			if($userid==$rrs['userid']){
				 $arrs=$arrs."<option value='".$rrs['userid']."' selected>".$rrs['username']."</option>";
				 }else
				 {
				 $arrs=$arrs."<option value='".$rrs['userid']."'>".$rrs['username']."</option>";
				 }
                  
				 //$arrs=$arrs."[0,".$rrs['userid'].",'".$rrs['username']."']";
			     //$arr=$arr."[".$rrs['area_parent_id'].",".$rrs['area_id'].",'".$rrs['area_name']."']";
				 $i=$i+1;
				 if ($i<$count){$arrs=$arrs.",";}

            }
           return $arrs;

	}	
	function PPClass($areaid=0){
    		$area =$this -> dbObj -> Execute('Select * From s_area order by area_sort  asc,area_id');
			$count=$area->RecordCount();
			$i=0;
			$arr="";
			while ($rrs = &$area -> FetchRow()) {
			if($areaid==$rrs['area_id']){
				 $arr=$arr."<option value='".$rrs['area_id']."' selected>".$rrs['area_name']."</option>";
				 }else
				 {
				 $arr=$arr."<option value='".$rrs['area_id']."'>".$rrs['area_name']."</option>";
				 }
			     //$arr=$arr."[".$rrs['area_parent_id'].",".$rrs['area_id'].",'".$rrs['area_name']."']";
				 $i=$i+1;
				 //if ($i<$count){$arr=$arr.",";}

            }
           return $arr;

	}
	function goDispAppend(){
		$t = new Template('../template/system');
		$t -> set_file('f','statisticsdetail.html');
		$t -> set_block('f','group','g');

		$groupArr = array();
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		}else{
			$updid = $_GET[MODIFY.'id'] + 0 ;
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'user WHERE userid = '.$updid));
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$gs = $this -> dbObj -> GetArray('SELECT groupid FROM '.WEB_ADMIN_TABPOX.'usergroup WHERE userid = '.$updid);
			foreach ($gs as $v)	$groupArr [] = $v['groupid'];
		}
		//当前用户所管理的组
		$umgs = '0';
		$mgs = &$this -> dbObj -> Execute('select groupid from '.WEB_ADMIN_TABPOX.'groupmanager where userid='.$this->getUid());
		while (!$mgs -> EOF) {
			$umgs.= ','.$mgs -> fields['groupid'];
			$mgs -> MoveNext();
		}
		//设置组列表
		$rs = &$this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX.'group where groupid in('.$umgs.')');
		while ($rrs = &$rs -> FetchRow()) {
			$t -> set_var($rrs);
			if (in_array($rrs['groupid'],$groupArr)) {
				$t -> set_var('gchecked',' checked');
			}else{
				$t -> set_var('gchecked','');
			}
			$t -> parse('g','group',true);
		}
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}

	function quit($info){
		exit("<script>alert('$info');location.href='user.php';</script>");
	}
}
$main = new PageUser();
$main -> Main();
?>