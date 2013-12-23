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
		$t -> set_file('f','statisticsfollowdetail.html');	
		$t -> set_block('f','customer','c');
		$datebg=$_POST['datebg'];
		$dateend=$_POST['dateend'];
		$userid=$_POST['userid'];
		$ordertype=$_POST['ordertype'];
        $t -> set_var('datebg',$datebg);
		$t -> set_var('dateend',$dateend);
		
		$i=1;
		$condition="" ;
		if($dateend!=""&$datebg!=""){
		$condition=" and visitetime BETWEEN '".$datebg." 00:00:00' AND '".$dateend." 23:59:59'";
		
		$conditionuserid=$userid==0?" ":" where userid=".$userid;
		$conditionordertype=" order by ".$ordertype.' desc';
		
		$condition1=" and plantime BETWEEN '".$datebg."' AND '".$dateend."'";}
	

		$visitenumber=0;//访问次数
		
		$t -> set_var('c');
		
		$psize=$this->getValue('pagesize');
		$psize =$psize?$psize:100; 
		
		$rs = &$this -> dbObj -> Execute("SELECT * FROM ".WEB_ADMIN_TABPOX.'customer '.$conditionuserid);
		while ($rrs = &$rs -> FetchRow()) { 
		   $t -> set_var($rrs);	  
		   
			//设置访问次数
			$rs2 = &$this -> dbObj -> Execute("SELECT count(recordid)  FROM ".WEB_ADMIN_TABPOX."visiterecord where customerid=".$rrs['customerid'].$condition);
			while ($rrs2 = &$rs2 -> FetchRow()) {
			$visitenumber=$rrs2['count(recordid)'];
			
			//$t -> set_var('visitenumber',$visitenumber);
			}
			$rs2 -> Close();
			
			//设置上次访问时间

			$rs3 = &$this -> dbObj -> Execute("SELECT * FROM ".WEB_ADMIN_TABPOX."visiterecord where customerid=".$rrs['customerid'].$condition.'order by visitetime desc LIMIT 0 , 1');

			while ($rrs3 = &$rs3 -> FetchRow()) {
			$lasttime=$rrs3['visitetime'];

			}
			$count=$rs3->RecordCount();
			if ($count==0){$lasttime="2010-01-01 00:00:00";}
			//$t -> set_var('lasttime',$lasttime);
			$rs3 -> Close();					   
	
			//设置下次拜访时间
			$rs4 = &$this -> dbObj -> Execute("SELECT * FROM ".WEB_ADMIN_TABPOX."visiteplan where customerid=".$rrs['customerid']." and planstatus='未处理' order by plantime asc LIMIT 0 , 1");
			while ($rrs4 = &$rs4 -> FetchRow()) {
			$nexttime=$rrs4['plantime'];			
			//$t -> set_var('nexttime',$nexttime);
			
			}
			$count=$rs4->RecordCount();
			if ($count==0){$nexttime="2010-01-01";}
			//$t -> set_var('nexttime',$nexttime);
			$rs4 -> Close();				   
		  
			//设置负责人
			$rs4 = &$this -> dbObj -> Execute("SELECT * FROM ".WEB_ADMIN_TABPOX."user where userid=".$rrs['userid']);
			while ($rrs4 = &$rs4 -> FetchRow()) {
			$username=$rrs4['username'];			
			//$t -> set_var('username',$username);
			
			}
			$rs4 -> Close();				   
			//设置区域
		    if ($rrs['areaid']==0||$rrs['areaid']==''){
			$areaname='不详';
			}else{
			
			$rs5 = &$this -> dbObj -> Execute("SELECT * FROM ".WEB_ADMIN_TABPOX."area where area_id=".$rrs['areaid']);
			
			while ($rrs5 = &$rs5 -> FetchRow()) {
			$areaname=$rrs5['area_name'];
			}
			$rs5 -> Close();	
				  
		  	}
		   //$t -> set_var('areaname',$areaname);	
		   //设置客户分级
		   $degree=0;
		   //插入临时表

			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."statisticsfollow(customername, visitenumber,lasttime,nexttime,areaname,username,degree)values('".$rrs['customername']."','".$visitenumber."','".$lasttime."','".$nexttime."','".$areaname."','".$username."','".$degree."')");			

		
						
			
   
		  // $t -> parse('c','customer',true);
		}	
		$rs -> Close();
		//从临时表中读取
		if($ordertype!=""){
		$rs6 = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX."statisticsfollow order by ".$ordertype." desc  LIMIT 0 , ".$psize);
		}else
		{
		$rs6 = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX."statisticsfollow LIMIT 0 , ".$psize);
		}
		
		while ($rrs6 = &$rs6 -> FetchRow()) {	
		$t -> set_var($rrs6);
		
		$t -> set_var('customername',$rrs6['customername']);
		$t -> set_var('visitenumber',$rrs6['visitenumber']);
		$t -> set_var('lasttime',$rrs6['lasttime']=='2010-01-01 00:00:00'?'无':$rrs6['lasttime']);
		$t -> set_var('nexttime',$rrs6['nexttime']=='2010-01-01'?'无':$rrs6['nexttime']);
		$t -> set_var('areaname',$rrs6['areaname']);
		$t -> set_var('username',$rrs6['username']);				
		$t -> set_var('degree',$rrs6['degree']);
		$t -> set_var('rank',$i);
		$i=$i+1;				
		$t -> parse('c','customer',true);
		}	
		$rs6 -> Close();
		$this -> dbObj -> Execute('DELETE  FROM '.WEB_ADMIN_TABPOX."statisticsfollow ");		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');	
	}
	function disp(){
		//定义模板
		$t = new Template('../template/system');
		$t -> set_file('f','statisticsfollow.html');

		$t -> set_var('datebg','2010-01-01');			
		$t -> set_var('dateend',$dateend?$dateend:date("Y-m-d"));	
		//echo PPClass_sale();
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