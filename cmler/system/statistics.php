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
		$t -> set_file('f','statisticsdetail.html');	
		$t -> set_block('f','user','u');
		$t -> set_block('f','total','t');
		$datebg=$_POST['datebg'];
		$dateend=$_POST['dateend'];
		$userid=$_POST['userid'];
        $t -> set_var('datebg',$datebg);
		$t -> set_var('dateend',$dateend);
		$condition="" ;
		if($dateend!=""&$datebg!=""){
		$condition=" and visitetime BETWEEN '".$datebg." 00:00:00' AND '".$dateend." 23:59:59'";
		$condition1=" and plantime BETWEEN '".$datebg."' AND '".$dateend."'";}
		//设置客服
			$totalplannumber=0;
			$totalvisitenumber=0;
			$totalplancomplete=0;
			$totalplanvisiteagain=0;
			$totalplanuncomplete=0;
			$totalcompleterate=0;		
		$t -> set_var('u');
		
		$rs = &$this -> dbObj -> Execute("SELECT u.* FROM ".WEB_ADMIN_TABPOX.'user u INNER JOIN '.WEB_ADMIN_TABPOX.'usergroup g on u.userid=g.userid where g.groupid=3');
		while ($rrs = &$rs -> FetchRow()) {
			//$t -> set_var($rrs);
			//设置计划数
			
			$rs1 = &$this -> dbObj -> Execute("SELECT count(planid)  FROM ".WEB_ADMIN_TABPOX."visiteplan where userid=".$rrs['userid'].$condition1);
			while ($rrs1 = &$rs1 -> FetchRow()) {
			$plannumber=$rrs1['count(planid)'];
			
			//$t -> set_var('plannumber',$rrs1['count(planid)']);
			}
			
			$rs1 -> Close();
			
			//设置访问次数
			$rs2 = &$this -> dbObj -> Execute("SELECT count(recordid)  FROM ".WEB_ADMIN_TABPOX."visiterecord where userid=".$rrs['userid'].$condition);
			while ($rrs2 = &$rs2 -> FetchRow()) {
			$visitenumber=$rrs2['count(recordid)'];
			//$t -> set_var('visitenumber',$visitenumber);
			}
			$rs2 -> Close();
			//设置计划数
			
			$planuncomplete=0;
			$plancomplete=0;
			$planvisiteagain=0;
			$rs3 = &$this -> dbObj -> Execute("SELECT *   FROM ".WEB_ADMIN_TABPOX."visiteplan where userid=".$rrs['userid'].$condition1);
			while ($rrs3 = &$rs3 -> FetchRow()) {
			if($rrs3['planstatus'] =='未处理'){
			$planuncomplete=$planuncomplete+1;
			
			}
			elseif ($rrs3['planstatus'] =='已处理')
			{$plancomplete=$plancomplete+1;
			
			}
			else{
			$planvisiteagain=$planvisiteagain+1;
			
			}
			 
			}
			$rs3 -> Close();
				
			$completerate=100*$plancomplete/$plannumber;
			if($completerate==""){
			$completerate=0;
			} 
			
			$totalplannumber=$totalplannumber+$plannumber;
			$totalvisitenumber=$totalvisitenumber+$visitenumber;
			$totalplancomplete=$totalplancomplete+$plancomplete;
			$totalplanvisiteagain=$totalplanvisiteagain+$planvisiteagain;
			$totalplanuncomplete=$totalplanuncomplete+$planuncomplete;
			$totalcompleterate=100*$totalplancomplete/$totalplannumber;
			if($totalcompleterate==""){
			$totalcompleterate=0;
			} 
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."visitecompleterate(userid,username,plannumber,visitenumber,plancomplete,planvisiteagain,planuncomplete,completerate)values('".$rrs['userid']."','".$rrs['username']."','".$plannumber."','".$visitenumber."','".$plancomplete."','".$planvisiteagain."','".$planuncomplete."','".$completerate."')");			
			$id = $this -> dbObj -> Insert_ID();
						
			
			//$t -> set_var('plancomplete',$plancomplete);
			//$t -> set_var('planvisiteagain',$planvisiteagain);
			//$t -> set_var('planuncomplete',$planuncomplete);
				

			//$t -> set_var('completerate',$plancomplete/$plannumber);
			//$t -> parse('u','user',true);
		}		
		
		
		if($_POST['ordertype']!=""){
		$rs5 = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX."visitecompleterate order by ".$_POST['ordertype']." desc");
		}else
		{
		$rs5 = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX."visitecompleterate ");
		}
		
		while ($rrs5 = &$rs5 -> FetchRow()) {	
		$t -> set_var($rrs5);
		$t -> parse('u','user',true);
		}	
		$rs5 -> Close();
		
		$this -> dbObj -> Execute('DELETE  FROM '.WEB_ADMIN_TABPOX."visitecompleterate");		
		$rs -> Close();
		
		//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX."visitecompleterate";
		//$t -> set_var($this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX."visitecompleterate"));
		//$t -> parse('u','user',true);
		$t -> set_var('t');
		
		$t -> set_var('totalplannumber',$totalplannumber);
		$t -> set_var('totalvisitenumber',$totalvisitenumber);
		$t -> set_var('totalplancomplete',$totalplancomplete);
		$t -> set_var('totalplanvisiteagain',$totalplanvisiteagain);
		$t -> set_var('totalplanuncomplete',$totalplanuncomplete);
		$t -> set_var('totalcompleterate',$totalcompleterate);		

		$t -> parse('t','total',true);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');	
	}
	function disp(){
		//定义模板
		$t = new Template('../template/system');
		$t -> set_file('f','statistics.html');

		$t -> set_var('datebg',$datebg?$datebg:date("Y-m-d"));			
		$t -> set_var('dateend',$dateend?$dateend:date("Y-m-d"));	
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
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