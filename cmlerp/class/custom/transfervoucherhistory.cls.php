<?
/**
 * @package System
 */
 //商品流水账
//require(WEB_ADMIN_HTTPPATH.'/admin.inc.php');
//require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class transfervoucherhistory extends admin {
	function main($accounttitle_id,$objecttype_id,$objectid,$lend,$loan,$memo,$date,$agencyid,$transfervoucher_id){
	 $agencyid=$agencyid==''?$_SESSION["currentorgan"]:$agencyid;;
	$lastdata=$this->dbObj->GetRow("SELECT * FROM ".WEB_ADMIN_TABPOX."transfervoucherhistory1  where accounttitle_id=".$accounttitle_id." and agencyid=".$agencyid." order by transfervoucherhistory_id desc ");
	$adduplend=$lend+$lastdata['adduplend'];
	$adduploan=$loan+$lastdata['adduploan'];
	$balance=$adduplend-$adduploan;
	$res=$this->dbObj->Execute("INSERT INTO  `".WEB_ADMIN_TABPOX."transfervoucherhistory1` (`accounttitle_id`, transfervoucher_id,`objecttype_id`, `objectid`, `lend`, `adduplend`, `loan`, `adduploan`, `balance`, `memo`, `date`, `agencyid`) VALUE (".$accounttitle_id.",".$transfervoucher_id.",".$objecttype_id.",".$objectid.",".$lend.",".$adduplend.",".$loan.",".$adduploan.",".$balance.",'".$memo."','".$date."',".$agencyid.")");
	//echo "INSERT INTO  `".WEB_ADMIN_TABPOX."transfervoucherhistory1` (`accounttitle_id`, transfervoucher_id,`objecttype_id`, `objectid`, `lend`, `adduplend`, `loan`, `adduploan`, `balance`, `memo`, `date`, `agencyid`) VALUE (".$accounttitle_id.",".$transfervoucher_id.",".$objecttype_id.",".$objectid.",".$lend.",".$adduplend.",".$loan.",".$adduploan.",".$balance.",'".$memo."','".$date."',".$agencyid.")";
		if($res){	
		return 1;
		} else{
		return 0;
		}
 
	
}
	
}
//$main = new stock();
//$main -> Main();
?>
  