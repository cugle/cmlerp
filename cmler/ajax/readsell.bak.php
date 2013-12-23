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
$table=$_POST['table']?$_POST['table']:'sell';
$column=$_POST['column']?$_POST['column']:'creattime';
$value=$_POST['value']?$_POST['value']:date();
$agencyid=$_SESSION["currentorgan"]?$_SESSION["currentorgan"]:1;

if($column=='customer_id'){
	$inrs = $this -> dbObj -> Execute("select * from  ".WEB_ADMIN_TABPOX.$table."    where   ".$column."=".$value." order by sell_id desc");
	}else{
$inrs = $this -> dbObj -> Execute("select * from  ".WEB_ADMIN_TABPOX.$table."    where  (TO_DAYS(creattime)   =   TO_DAYS(NOW())  or  `status`  =0) order by sell_id desc");
						
}
//echo "select * from  ".WEB_ADMIN_TABPOX.$table."    where agencyid=".$agencyid." and  TO_DAYS(creattime)   =   TO_DAYS(NOW())";
//echo "select * from (".$table." A  INNER JOIN ".WEB_ADMIN_TABPOX."membercard B ON A.customer_id=B.customer_id ) INNER JOIN ".WEB_ADMIN_TABPOX."memcardlevel C ON  B.cardlevel_id=C.cardlevel_id where B.membercard_no='".$value."'";
//$result=$this->check_chongfu($table,$ziduan,$customer_no));
//$test=$this -> dbObj ->GetOne("select * from  ".WEB_ADMIN_TABPOX.$table."  where agencyid=".$agencyid." and  sell_id='".$value."'");

//销售列表返回数组
//echo "select * from  ".WEB_ADMIN_TABPOX.$table."  where agencyid=".$_SESSION["currentorgan"]." and  sell_id='".$value."'";
$table_name=array('services',"produce","services");	
$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");	
$result='';
 $statusname=array('未完成',"已完成","反冲","被反冲","已提交","已审核");	
while ($inrrs = &$inrs -> FetchRow()) {
//echo 'select *  from '.WEB_ADMIN_TABPOX.$table_name[$inrrs['item_type']].'  where  '.$table_name[$inrrs['item_type']].'_id ='.$inrrs['item_id'];
//$itemdata= $this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.$table_name[$inrrs['item_type']].'  where  '.$table_name[$inrrs['item_type']].'_id ='.$inrrs['item_id']);
// $cardtype='';

$customer_name= $this -> dbObj -> GetOne('select customer_name from '.WEB_ADMIN_TABPOX.'customer  where customer_id ='.$inrrs['customer_id']);
$customer_name=$customer_name?$customer_name:"散客";
$employee_name= $this -> dbObj -> GetOne('select employee_name from '.WEB_ADMIN_TABPOX.'employee  where employee_id ='.$inrrs['employee_id']);

$status=$inrrs['status']==0?'<a href="#" title="点击继续编辑该单据" onclick="editbill('.$inrrs['sell_id'].');"><font color=red>'.$statusname[$inrrs['status']].'</font></a>':$statusname[$inrrs['status']];

		$sql1="select * from  ".WEB_ADMIN_TABPOX."selldetail  where  agencyid =".$_SESSION['currentorgan'];
		$sql2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail  where  agencyid =".$_SESSION['currentorgan'];
		$sql3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  where agencyid =".$_SESSION['currentorgan'];
		$sql4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  where agencyid =".$_SESSION['currentorgan'];
		$sql5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail  where agencyid =".$_SESSION['currentorgan'];
		$sqlstr=$sql1." union ".$sql2." union ".$sql3." union ".$sql4." union ".$sql5;
		$payable1=$this -> dbObj ->GetOne("select sum(amount) as amount FROM  (".$sqlstr.") A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id WHERE A.sell_id =".$inrrs['sell_id']);

$cardlevel_name=$this -> dbObj -> GetOne("select B.cardlevel_name from  ".WEB_ADMIN_TABPOX."membercard  A INNER JOIN  ".WEB_ADMIN_TABPOX."memcardlevel B ON A.cardlevel_id=B.cardlevel_id where  A.customer_id=".$inrrs['customer_id']);
if(($inrrs['payable1']-$inrrs['realpay'])>0){
$own='<font color=red>'.($inrrs['payable1']-$inrrs['realpay']).'</font>';
}else{
$own=$inrrs['payable1']-$inrrs['realpay'];
}
$customercatalog_name=$this -> dbObj -> GetOne("select A.customercatalog_name from  ".WEB_ADMIN_TABPOX."customercatalog  A INNER JOIN  ".WEB_ADMIN_TABPOX."customer B ON A.customercatalog_id=B.customercatalog_id where  B.customer_id=".$inrrs['customer_id']);
$cardlevel_name=$cardlevel_name?$cardlevel_name:$customercatalog_name;				
$otheraccount=$inrrs['zengsongvalue']+$inrrs['dingjinvalue']+$inrrs['chuzhikavalue']+$inrrs['xianjinquanvalue']+$inrrs['yufuvalue'];
$temp=date('Y-m-d',strtotime($inrrs['creattime'])).'@@@'.$inrrs['sell_no'].'@@@'.$inrrs['realpay'].'@@@'.$customer_name.'@@@'.$employee_name.'@@@'. $status.'@@@'.$payable1.'@@@'.$inrrs['sell_id'].'@@@'.$cardlevel_name.'@@@'.$inrrs['memcardremain'].'@@@'.$inrrs['yufuremain'].'@@@'.$inrrs['dingjinremain'].'@@@'.$inrrs['chuzhiremain'].'@@@'.$inrrs['itemcardremain']."@@@".$inrrs['xianjinvalue']."@@@".$inrrs['yinkavalue']."@@@".$otheraccount."@@@".($inrrs['yinkavalue']+$inrrs['xianjinvalue'])."@@@".$own;
$result=$result==''?$temp:$result."|||".$temp;
//echo "'".$table_name[$inrrs['item_type']]."_name'";
}

$inrs -> Close();	

echo $result;
}

   
}
$main = new PageUser();
$main -> Main();
?>