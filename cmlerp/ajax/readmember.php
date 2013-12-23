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
$table=$_POST['table']?$_POST['table']:'s_customer';
$column=$_POST['column']?$_POST['column']:'code';
$value=$_POST['value']?$_POST['value']:'0';

$result = $this -> dbObj -> GetRow("select A.*, B.* ,C.* from ((".$table." A  INNER JOIN ".WEB_ADMIN_TABPOX."membercard B ON A.customer_id=B.customer_id ) INNER JOIN ".WEB_ADMIN_TABPOX."memcardlevel C ON  B.cardlevel_id=C.cardlevel_id) where B.".$column."='".$value."'");
if($result['customer_id']=='') {
$result = $this -> dbObj -> GetRow("select A.*, B.* ,C.* from ((".$table." A  INNER JOIN ".WEB_ADMIN_TABPOX."membercard B ON A.customer_id=B.customer_id ) INNER JOIN ".WEB_ADMIN_TABPOX."memcardlevel C ON  B.cardlevel_id=C.cardlevel_id) where B.membercard_no='".$value."' and A.agencyid =".$_SESSION["currentorgan"] );
}
if($result['customer_id']=='') {
$result = $this -> dbObj -> GetRow("select * from s_customer where customer_no='".$value."'");

$result1 = $this -> dbObj -> GetRow("select A.*, B.* ,C.* from ((".WEB_ADMIN_TABPOX."customer A  INNER JOIN ".WEB_ADMIN_TABPOX."membercard B ON A.customer_id=B.customer_id ) INNER JOIN ".WEB_ADMIN_TABPOX."memcardlevel C ON  B.cardlevel_id=C.cardlevel_id) where A.customer_id='".$result['customer_id']."'" );

if($result1){
	$result=$result1;
}

}
//echo "select * from (".$table." A  INNER JOIN ".WEB_ADMIN_TABPOX."membercard B ON A.customer_id=B.customer_id ) INNER JOIN ".WEB_ADMIN_TABPOX."memcardlevel C ON  B.cardlevel_id=C.cardlevel_id where B.membercard_no='".$value."'";
//$result=$this->check_chongfu($table,$ziduan,$customer_no));



if($result['customer_id']!='') {
$_SESSION["currentcustomerid"]=$result['customer_id'];
$_SESSION["membercardno"]=$result['membercard_no'];
$agencyname=$this -> dbObj -> GetOne('select agency_name from '.WEB_ADMIN_TABPOX.'agency where agency_id='.$result["agencyid"]);

	if($_SESSION["currentorgan"]<>$result["agencyid"]){
	$_SESSION["customerorgan"]=$result["agencyid"];
	$errortype='3';
	}else{
	$_SESSION["customerorgan"]=$result["agencyid"];
	$errortype='0';
	}
	if($result['overdate']<date('Y-m-d')&&$result['membercard_no']<>''){$tips='<font color=red>该会员卡已过有效期</font>';}else{$tips='';}
$agencyid='1';
$deposit=$result["dingjin"];	
$salesowe=$result['salesowe'];
$salesowe=$salesowe>0?$salesowe.' <a  href=# onclick="repayment()"; ><font color=red>马上还款</font></a>':$salesowe;
$info='';

}else {
	$_SESSION["currentcustomerid"]='0';
	$_SESSION["membercardno"]='';
	$errortype='1';//错误类型：卡号错误，密码错误，跨店客户，卡号过期。
	}
	$customercatalog_name=$this -> dbObj -> GetOne('select customercatalog_name from '.WEB_ADMIN_TABPOX.'customercatalog where customercatalog_id='.$result["customercatalog_id"]);
	$result['cardlevel_name']=$result['cardlevel_name']?$result['cardlevel_name']:$customercatalog_name;
echo $errortype.'@@@'.$result['customer_name'].'@@@'.$result['cardlevel_name'].'@@@'.$result['customer_id'].'@@@'.$agencyname.'@@@'.$deposit.'@@@'.$salesowe.'@@@'.$agencyid.'@@@'.$result['customer_no'].'@@@'.$result['membercard_no'].'@@@'.$result['yufukuan'].'@@@'.$tips;


}

   
}
$main = new PageUser();
$main -> Main();
?>