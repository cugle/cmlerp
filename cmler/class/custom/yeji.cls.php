<?
/**
 * @package System
 */
//require(WEB_ADMIN_HTTPPATH.'/admin.inc.php');
//require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class yeji extends admin {
	function payall($sellid){
		$this -> dbObj -> Execute("update `".WEB_ADMIN_TABPOX."cardyeji` set status=3 where sell_id=".$sellid);	//已还清欠款
		
	}
	function updateyeji($sellid){
		$this -> dbObj -> Execute("detete * from  `".WEB_ADMIN_TABPOX."cardyeji` where sell_id=".$sellid);	
		$this->cardyeji($sellid);
		$this->producedyeji($sellid);
		$this->sellservicesyeji($sellid);
		$this->otheryeji($sellid);
	}
	function recoilyeji($sellid){
	$inrs=$this -> dbObj -> Execute("select * from  `".WEB_ADMIN_TABPOX."cardyeji` where sell_id=".$sellid);	
	while ($inrrs = $inrs -> FetchRow()) {
	//echo "INSERT INTO `".WEB_ADMIN_TABPOX."cardyeji` ( `employeelevel_id`, `employee_id`, `item_type`, `cardtype`, `cardid`, `number`, `value`, `customercardid`, `payalldate`, `status`, `sell_id`, `selldetail_id`, `buydate`, `agencyid`) VALUES ('".$inrrs["employeelevel_id"]."','".$inrrs["employee_id"]."','".$inrrs["item_type"]."','".$inrrs["cardtype"]."','".$inrrs["cardid"]."','".(-$inrrs["number"])."','".(-$inrrs["value"])."','".$inrrs["customercardid"]."','".$inrrs['payalldate']."','".$inrrs["status"]."','".$inrrs["sell_id"]."','".$inrrs["selldetail_id"]."','".$inrrs['buydate']."','".$inrrs["agencyid"]."')";
	 $res=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."cardyeji` ( `employeelevel_id`, `employee_id`, `item_type`, `cardtype`, `cardid`, `number`, `value`, `customercardid`, `payalldate`, `status`, `sell_id`, `selldetail_id`, `buydate`, `agencyid`) VALUES ('".$inrrs["employeelevel_id"]."','".$inrrs["employee_id"]."','".$inrrs["item_type"]."','".$inrrs["cardtype"]."','".$inrrs["cardid"]."','".(-$inrrs["number"])."','".(-$inrrs["value"])."','".$inrrs["customercardid"]."','".$inrrs['payalldate']."','".$inrrs["status"]."','".$inrrs["sell_id"]."','".$inrrs["selldetail_id"]."','".$inrrs['buydate']."','".$inrrs["agencyid"]."')");	
	 
	 //如果反冲的是还款，需要重置之前欠款单的状态。置为已换部分 或者有欠款。
/*	 if($inrrs["item_type"]==5&&$inrrs["cardid"]==3){//还款
	  $this -> dbObj -> Execute("update `".WEB_ADMIN_TABPOX."cardyeji`  set status=2 where sell_id=".$inrrs["sell_id"]);	
	  //echo "update `".WEB_ADMIN_TABPOX."cardyeji`  set status=2 where sell_id=".$inrrs["sell_id"];
	}*/

	}
	
	$inrs=$this -> dbObj -> Execute("select * from  `".WEB_ADMIN_TABPOX."cardyeji` where item_type=5 and cardid=3 and sell_id=".$sellid." group by selldetail_id");	
	while ($inrrs = $inrs -> FetchRow()) {
	//从还款单中找到所还欠款单。
	$selldata=$this -> dbObj -> GetRow("select * from `".WEB_ADMIN_TABPOX."sellotherdetail`   where sell_id=".$inrrs["sell_id"]);
	//重置欠款单。
	//$yejidata=$this -> dbObj -> Execute("select * from `".WEB_ADMIN_TABPOX."cardyeji`   where sell_id=".$inrrs["returnid"]);
	$this -> dbObj -> Execute("update `".WEB_ADMIN_TABPOX."cardyeji`  set status=2 where sell_id=".$selldata["returnid"]);	//重置状态为已还部分
	$this -> dbObj -> Execute("update `".WEB_ADMIN_TABPOX."sell`  set ownstatus=2 ,sellownremain= sellownremain+".$inrrs["value"]."  where sell_id=".$selldata["returnid"]);	//value 在开卡时候不是实际的付款金额，而是应收金额，还款的时候应收=实收。
	}
	if($res){return true;}else{return false;}
	}
	function yeji1($sellid){
		$selldatacount= &$this -> dbObj -> GetOne('select count(*) from '.WEB_ADMIN_TABPOX.'cardyeji  where  sell_id='.$sellid);
		 
		if($selldatacount==0){
		$res1=$this->cardyeji($sellid);
		$res2=$this->producedyeji($sellid);
		$res3=$this->sellservicesyeji($sellid);
		$res4=$this->otheryeji($sellid);
		
		if($res1||$res2||$res3||$res4){return true;}
		}else{
		 
		return false;
		}
	}
	function cardyeji($sellid){
		 

		$selldata= &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'sell   where   sell_id='.$sellid);
		$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'sellcarddetail    where sell_id='.$sellid);
		 
		while ($inrrs = $inrs -> FetchRow()) {
		//按照美容师插入业绩表
		$beauty_id=explode(";",$inrrs['beauty_id']);
		
		for($i=0;$i<count($beauty_id);$i++){
		$res=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."cardyeji` ( `employeelevel_id`, `employee_id`, `item_type`, `cardtype`, `cardid`, `number`, `value`, `customercardid`, `payalldate`, `status`, `sell_id`, `selldetail_id`, `buydate`, `agencyid`) VALUES ('1','".$beauty_id[$i]."','".$inrrs["item_type"]."','".$inrrs["cardtype"]."','".$inrrs["cardid"]."','".$inrrs["number"]/count($beauty_id)."','".$inrrs["cmlaccount"]/count($beauty_id)."','".$inrrs["customercardid"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["ownstatus"]."','".$inrrs["sell_id"]."','".$inrrs["selldetail_id"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["agencyid"]."')");
		 //echo  "INSERT INTO `".WEB_ADMIN_TABPOX."cardyeji` ( `employeelevel_id`, `employee_id`, `item_type`, `cardtype`, `cardid`, `number`, `value`, `customercardid`, `payalldate`, `status`, `sell_id`, `selldetail_id`, `buydate`, `agencyid`) VALUES ('1','".$beauty_id[$i]."','".$inrrs["item_type"]."','".$inrrs["cardtype"]."','".$inrrs["cardid"]."','".$inrrs["number"]/count($beauty_id)."','".$inrrs["cmlaccount"]/count($beauty_id)."','".$inrrs["customercardid"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["ownstatus"]."','".$inrrs["sell_id"]."','".$inrrs["selldetail_id"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["agencyid"]."')";
		}
		//按照顾问插入业绩表。
		
		$employee_id=explode(";",$inrrs['employee_id']);
		
		for($i=0;$i<count($employee_id);$i++){
		$res=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."cardyeji` ( `employeelevel_id`, `employee_id`, `item_type`, `cardtype`, `cardid`, `number`, `value`, `customercardid`, `payalldate`, `status`, `sell_id`, `selldetail_id`, `buydate`, `agencyid`) VALUES ('2','".$employee_id[$i]."','".$inrrs["item_type"]."','".$inrrs["cardtype"]."','".$inrrs["cardid"]."','".$inrrs["number"]/count($employee_id)."','".$inrrs["cmlaccount"]/count($employee_id)."','".$inrrs["customercardid"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["ownstatus"]."','".$inrrs["sell_id"]."','".$inrrs["selldetail_id"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["agencyid"]."')");
		// echo "INSERT INTO `".WEB_ADMIN_TABPOX."cardyeji` ( `employeelevel_id`, `employee_id`, `item_type`, `cardtype`, `cardid`, `number`, `value`, `customercardid`, `payalldate`, `status`, `sell_id`, `selldetail_id`, `buydate`, `agencyid`) VALUES ('2','".$employee_id[$i]."','".$inrrs["item_type"]."','".$inrrs["cardtype"]."','".$inrrs["cardid"]."','".$inrrs["number"]/count($employee_id)."','".$inrrs["cmlaccount"]/count($employee_id)."','".$inrrs["customercardid"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["ownstatus"]."','".$inrrs["sell_id"]."','".$inrrs["selldetail_id"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["agencyid"]."')";
		}
		//echo 'select * from '.WEB_ADMIN_TABPOX.'marketingcard   where marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid;
		}
		
 		if($res){
		return true;
		}else{
		return false;
		}
	}
	
	
	function producedyeji($sellid){
		 

		$selldata= &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'sell   where   sell_id='.$sellid);
		$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'selldetail    where sell_id='.$sellid);
		 
		while ($inrrs = $inrs -> FetchRow()) {
		//按照美容师插入业绩表
		$beauty_id=explode(";",$inrrs['beauty_id']);
		
		for($i=0;$i<count($beauty_id);$i++){
		$res=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."cardyeji` ( `employeelevel_id`, `employee_id`, `item_type`, `cardtype`, `cardid`, `number`, `value`, `customercardid`, `payalldate`, `status`, `sell_id`, `selldetail_id`, `buydate`, `agencyid`) VALUES ('1','".$beauty_id[$i]."','".$inrrs["item_type"]."','".$inrrs["cardtype"]."','".$inrrs["cardid"]."','".$inrrs["number"]/count($beauty_id)."','".$inrrs["cmlaccount"]/count($beauty_id)."','".$inrrs["customercardid"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["ownstatus"]."','".$inrrs["sell_id"]."','".$inrrs["selldetail_id"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["agencyid"]."')");
		
		}
		//按照顾问插入业绩表。
		$employee_id=explode(";",$inrrs['employee_id']);
		for($i=1;$i<count($employee_id);$i++){
		$res=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."cardyeji` ( `employeelevel_id`, `employee_id`, `item_type`, `cardtype`, `cardid`, `number`, `value`, `customercardid`, `payalldate`, `status`, `sell_id`, `selldetail_id`, `buydate`, `agencyid`) VALUES ('2','".$employee_id[$i]."','".$inrrs["item_type"]."','".$inrrs["cardtype"]."','".$inrrs["cardid"]."','".$inrrs["number"]/count($employee_id)."','".$inrrs["cmlaccount"]/count($employee_id)."','".$inrrs["customercardid"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["ownstatus"]."','".$inrrs["sell_id"]."','".$inrrs["selldetail_id"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["agencyid"]."')");

		}
		//echo 'select * from '.WEB_ADMIN_TABPOX.'marketingcard   where marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid;
		}
		
 		if($res){
		return true;
		}else{
		return false;
		}
	}
	
	
	function sellservicesyeji($sellid){
		 

		$selldata= &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'sell   where   sell_id='.$sellid);
		$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'sellservicesdetail    where sell_id='.$sellid.' union select * from '.WEB_ADMIN_TABPOX.'sellconsumedetail    where sell_id='.$sellid);
		 
		while ($inrrs = $inrs -> FetchRow()) {
		//按照美容师插入业绩表
		$beauty_id=explode(";",$inrrs['beauty_id']);
		 
		for($i=0;$i<count($beauty_id);$i++){
	
		$res=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."cardyeji` ( `employeelevel_id`, `employee_id`, `item_type`, `cardtype`, `cardid`, `number`, `value`, `customercardid`, `payalldate`, `status`, `sell_id`, `selldetail_id`, `buydate`, `agencyid`) VALUES ('1','".$beauty_id[$i]."','".$inrrs["item_type"]."','".$inrrs["cardtype"]."','".$inrrs["cardid"]."','".$inrrs["number"]/count($beauty_id)."','".$inrrs["cmlaccount"]/count($beauty_id)."','".$inrrs["customercardid"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["ownstatus"]."','".$inrrs["sell_id"]."','".$inrrs["selldetail_id"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["agencyid"]."')");
		
		}
		//按照顾问插入业绩表。
		$employee_id=explode(";",$inrrs['employee_id']);
		for($i=1;$i<count($employee_id);$i++){
		$res=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."cardyeji` ( `employeelevel_id`, `employee_id`, `item_type`, `cardtype`, `cardid`, `number`, `value`, `customercardid`, `payalldate`, `status`, `sell_id`, `selldetail_id`, `buydate`, `agencyid`) VALUES ('2','".$employee_id[$i]."','".$inrrs["item_type"]."','".$inrrs["cardtype"]."','".$inrrs["cardid"]."','".$inrrs["number"]/count($employee_id)."','".$inrrs["cmlaccount"]/count($employee_id)."','".$inrrs["customercardid"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["ownstatus"]."','".$inrrs["sell_id"]."','".$inrrs["selldetail_id"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["agencyid"]."')");
		
		}
		//echo 'select * from '.WEB_ADMIN_TABPOX.'marketingcard   where marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid;
		}
		
 		if($res){
		return true;
		}else{
		return false;
		}
	}
	function otheryeji($sellid){
		 

		$selldata= &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'sell   where   sell_id='.$sellid);
		$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'sellotherdetail    where sell_id='.$sellid);
		 
		while ($inrrs = $inrs -> FetchRow()) {
		//按照美容师插入业绩表
		$beauty_id=explode(";",$inrrs['beauty_id']);
		
		for($i=0;$i<count($beauty_id);$i++){
		
		$res=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."cardyeji` ( `employeelevel_id`, `employee_id`, `item_type`, `cardtype`, `cardid`, `number`, `value`, `customercardid`, `payalldate`, `status`, `sell_id`, `selldetail_id`, `buydate`, `agencyid`) VALUES ('1','".$beauty_id[$i]."','".$inrrs["item_type"]."','".$inrrs["cardtype"]."','".$inrrs["cardid"]."','".$inrrs["number"]/count($beauty_id)."','".$inrrs["cmlaccount"]/count($beauty_id)."','".$inrrs["customercardid"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["ownstatus"]."','".$inrrs["sell_id"]."','".$inrrs["selldetail_id"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["agencyid"]."')");
		
		}
		//按照顾问插入业绩表。
		$employee_id=explode(";",$inrrs['employee_id']);
		for($i=1;$i<count($employee_id);$i++){
		$res=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."cardyeji` ( `employeelevel_id`, `employee_id`, `item_type`, `cardtype`, `cardid`, `number`, `value`, `customercardid`, `payalldate`, `status`, `sell_id`, `selldetail_id`, `buydate`, `agencyid`) VALUES ('2','".$employee_id[$i]."','".$inrrs["item_type"]."','".$inrrs["cardtype"]."','".$inrrs["cardid"]."','".$inrrs["number"]/count($employee_id)."','".$inrrs["cmlaccount"]/count($employee_id)."','".$inrrs["customercardid"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["ownstatus"]."','".$inrrs["sell_id"]."','".$inrrs["selldetail_id"]."','".date('Y-m-d',strtotime($selldata['creattime']))."','".$selldata["agencyid"]."')");
		
		}
		}
		
 		if($res){
		return true;
		}else{
		return false;
		}
	}		
}

?>
  