<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class PageUser extends admin {
	function disp(){
		//定义模板
		$t = new Template('../template/pos');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_file('f','posmain.html');

		$Prefix='XS';
		$agency_no=$_SESSION["agency_no"];
		//$agency_no=$_SESSION["agency_no"].date('Ymd',time());
		$table=WEB_ADMIN_TABPOX.'sell';
		$column='sell_no';
		$number=5;
		$id='sell_id';
		$sellno=$sellno?$sellno:$this->makeno($Prefix,$agency_no,$table,$column,$number,$id);
		$t -> set_var('sell_no',$sellno);	
		$_SESSION["sellno"]=$sellno;
		
		$t -> set_var('user_name',$this ->dbObj-> GetOne('select A.employee_name from '.WEB_ADMIN_TABPOX.'employee A INNER JOIN '.WEB_ADMIN_TABPOX.'user B ON A.employee_id=B.employee_id where B.userid='.$this->getUid()));
		 
		//当日
		$totaltoday = $this -> dbObj -> GetRow("select sum(xianjinvalue) as todayxianjinvalue ,sum(yinkavalue) as todayyinkavalue, sum(zengsongvalue) as todayzengsongvalue,sum(dingjinvalue) as   todaydingjinvalue, sum(chuzhikavalue) as todaydingjinvalue ,sum(xianjinquanvalue) as todayxianjinquanvalue,sum(yufuvalue) as todayyufuvalue,sum(payable1-realpay) as todayown from  ".WEB_ADMIN_TABPOX."sell    where agencyid=".$_SESSION['currentorgan']." and    TO_DAYS(creattime)   =   TO_DAYS(NOW())"); 
		 //当月
		$totalthismonth = $this -> dbObj -> GetRow("select sum(xianjinvalue) as thismonthxianjinvalue ,sum(yinkavalue) as thismonthyinkavalue, sum(zengsongvalue) as thismonthzengsongvalue,sum(dingjinvalue) as   thismonthdingjinvalue, sum(chuzhikavalue) as thismonthdingjinvalue ,sum(xianjinquanvalue) as thismonthxianjinquanvalue,sum(yufuvalue) as thismonthyufuvalue ,sum(payable1-realpay) as thismonthown from  ".WEB_ADMIN_TABPOX."sell    where agencyid=".$_SESSION['currentorgan']." and  creattime >'".date('Y-m-01 00:00:00',time())."'"); 
		$totaltoday['todayxianjinvalue']=$totaltoday['todayxianjinvalue']?$totaltoday['todayxianjinvalue']:'0.00';
		$totaltoday['todayyinkavalue']=$totaltoday['todayyinkavalue']?$totaltoday['todayyinkavalue']:'0.00';
		$totaltoday['todayown']=$totaltoday['todayown']?$totaltoday['todayown']:'0.00';
		$totalthismonth['thismonthxianjinvalue']=$totalthismonth['thismonthxianjinvalue']?$totalthismonth['thismonthxianjinvalue']:'0.00';
		$totalthismonth['thismonthyinkavalue']=$totalthismonth['thismonthyinkavalue']?$totalthismonth['thismonthyinkavalue']:'0.00';
		$totalthismonth['thismonthown']=$totalthismonth['thismonthown']?$totalthismonth['thismonthown']:'0.00';
		
		$t -> set_var($totalthismonth);
		$t -> set_var($totaltoday);
		$t -> set_var('todayincome',sprintf ("%01.2f",$totaltoday['todayxianjinvalue']+$totaltoday['todayyinkavalue']));
		$t -> set_var('thismonthincome',sprintf ("%01.2f",$totalthismonth['thismonthxianjinvalue']+$thismonthtotal['thismonthyinkavalue']));
 
		//设置当前机构信息
		 $t -> set_var('currentorganname',$_SESSION["currentorganname"]); 	
		$t -> set_var('userid',$this->getUid());
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispAppend(){
		$t = new Template('../template/system');
		$t -> set_file('f','userdetail.html');
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
		//echo 'select agency_type id from '.WEB_ADMIN_TABPOX.'agency a  inner join '.WEB_ADMIN_TABPOX.'user u on a.agency_id=u.agencyid where u.userid='.$this->getUid();
		 $agency_type_id=&$this -> dbObj -> GetOne('select agencytype id from '.WEB_ADMIN_TABPOX.'agency a  inner join '.WEB_ADMIN_TABPOX.'user u on a.agency_id=u.agencyid where u.userid='.$this->getUid());
		//echo  $agency_type_id;
		//'select g.groupid from '.WEB_ADMIN_TABPOX.'groupmanager g  inner join '.WEB_ADMIN_TABPOX.'user u on g.userid=u.userid where u.agencyid='.$_SESSION["currentorgan"]
		if ($agency_type_id==1){
		$mgs = &$this -> dbObj -> Execute('select groupid from '.WEB_ADMIN_TABPOX.'group where agencyid='.$_SESSION["currentorgan"]);
		}else{
		$mgs = &$this -> dbObj -> Execute('select groupid from '.WEB_ADMIN_TABPOX.'groupmanager where userid='.$this->getUid());
		}
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
	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] + 0;
		require_once(WEB_ADMIN_CLASS_PATH.'/power/Power.cls.php');
		$powerObj = new Power(&$this->dbObj,$this->getUid());
		$powerObj -> delUser($delid);
		$this -> quit('删除成功！');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		$username=$_POST['username'];

		if($this -> isAppend){
			$info = '增加';
			$uid =$this -> dbObj -> GetOne('select userid from '.WEB_ADMIN_TABPOX."user where username = '".$username."'");
		    if($uid){exit("<script>alert('此用户已存在，请重新填写');history.go(-1);;</script>");}
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."user(username,userpass,loginnum,importer,agencyid)values('".$_POST['username']."','".md5($_POST['password'])."',0,".$this->getUid().",".$_SESSION["currentorgan"].")");
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$uid =$this -> dbObj -> GetOne('select userid from '.WEB_ADMIN_TABPOX."user where username = '".$username."'");
		    if($uid&&$uid!=$_POST['updid']){exit("<script>alert('此用户已存在，请重新填写');history.go(-1);;</script>");}
			$id = $_POST[MODIFY.'id'] + 0;
			if($_POST['password']){
				$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."user SET username='".$_POST['username']."',userpass='".md5($_POST['password'])."' WHERE userid = $id");
			}else{
				$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."user SET username='".$_POST['username']."' WHERE userid = $id");
			}
			if(isset($_POST['groups']))
			$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'usergroup WHERE userid = '.$id);
		}
		if(isset($_POST['groups']))
		foreach ($_POST['groups'] as $v){
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."usergroup(userid,groupid,importer)values($id,$v,".$this->getUid().')');
		}
		$this -> quit($info.'成功！');
	}
	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');location.href='user.php';</script>");
	}
function makeno($Prefix,$agency_no,$table,$column,$number,$id){
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by  sell_no desc, ".$id." desc limit 1");
 
//echo "select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by ".$id." desc limit 1";
$nostr=$nostr[$column];
if($nostr==''){
$nostr=$Prefix.$agency_no.str_pad(1,$number,'0',STR_PAD_LEFT);

}else{
$nostr=mb_substr($nostr,strlen($nostr)-$number,$number,'utf-8');
$nostr=$nostr+1;
$nostr=str_pad($nostr,$number,'0',STR_PAD_LEFT);
$nostr=$Prefix.$agency_no.$nostr;
}
return $nostr;
}		
}
$main = new PageUser();
$main -> Main();
?>