<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class PageUser extends admin {
function disp(){
//$table=$_GET['table'];
//$ziduan=$_GET['ziduan'];
//$testname=$_GET['testname'];
$table=$_POST['table']?$_POST['table']:'sellservicesdetail';
$column=$_POST['column']?$_POST['column']:'selldetail_id';
$value=$_POST['value']?$_POST['value']:10;
$sellno= $_SESSION["sellno"];
$agencyid=$_SESSION["currentorgan"]?$_SESSION["currentorgan"]:1;
$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");
$selldetail=$this -> dbObj -> GetRow("select *  from  ".WEB_ADMIN_TABPOX.$table."   where  agencyid=".$agencyid." and selldetail_id ='".$value."'");
$this -> dbObj -> Execute("START TRANSACTION");//事务开始。
if($selldetail['item_type']==3){
	$res1=$this -> dbObj -> Execute("delete  from  ".WEB_ADMIN_TABPOX.$cardtable_name[$selldetail['cardtype']]."   where  agencyid=".$agencyid." and marketingcard_id=".$selldetail['cardid']." and ".$cardtable_name[$selldetail['cardtype']]."_id ='".$selldetail['customercardid']."'");
	}
	$res2=$this -> dbObj -> Execute("delete  from  ".WEB_ADMIN_TABPOX.$table."   where  agencyid=".$agencyid." and selldetail_id ='".$value."'");
	
	
	if($res2){
		$this -> dbObj -> Execute("COMMIT");
		echo 1;
	 }else {
		$this -> dbObj -> Execute("ROLLBACK");
		echo 0;
	}

}

   
}
$main = new PageUser();
$main -> Main();
?>