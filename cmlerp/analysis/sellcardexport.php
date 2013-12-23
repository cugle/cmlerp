<?php
$filename="卡销售历史单".date('Y-m-d').".xls";//先定义一个excel文件
header("Content-Type: application/vnd.ms-execl"); 
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 
echo "店名"."\t";
echo "单据编号"."\t";
echo "卡编号"."\t";
echo "卡类别"."\t";
echo "卡名称"."\t";
echo "顾客id"."\t";
echo "顾客编号"."\t";
echo "顾客姓名"."\t";
echo "会员卡号"."\t";
echo "顾客级别"."\t";
echo "销售id"."\t";
echo "销售编号"."\t";
echo "销售姓名"."\t";
echo "美容师id"."\t";
echo "美容编号"."\t";
echo "美容姓名"."\t";
echo "类别"."\t";
echo "状态"."\t";
echo "录单时间"."\t";
echo "备注"."\n";
$status_name=array("未完成","已完成","红字冲销","被红字冲销","已提交","已审核");
$itemtype_name=array('单项服务',"购买产品","消费卡项","购买卡项","消费券项");
$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");
$conn = mysql_connect("localhost","root","123qaz");
mysql_select_db("cmlerp");
		$bgdate=$_POST["bgdate"]." 00:00:00";
		$enddate=$_POST["enddate"]." 23:59:59";		
		$timecondition=' A.creattime  between "'.$bgdate.'" and "'.$enddate.'" AND A.status IN ('.$_POST['typelist'].')';
$sql="SELECT A.status as sellstatus,A.*,B.* , C.* FROM (s_sell A RIGHT JOIN s_sellcarddetail  B ON a.sell_id=B.sell_id ) LEFT JOIN s_employee C ON C.employee_id=A.employee_id where A.agencyid = ".$_POST["agencyid"].' and '.$timecondition;

mysql_query("SET NAMES GBK");
$result=mysql_query($sql); 
while($row =mysql_fetch_array($result)){
$agency_name='';
$agdata=mysql_query('SELECT * FROM s_agency where agency_id='.$_POST["agencyid"]); 	
while($agencydata =mysql_fetch_array($agdata)){
$agency_name=$agencydata['agency_name'];
}
echo $agency_name."\t";
echo $row['sell_no']."\t";
//echo $row['item_id']."\t";


/*//卡类别卡编号 卡名称

$marketingcardtype_name='';
echo $row['cardtype'];
if($row['cardtype']<>0){
	
$ctsqlstr='select marketingcardtype_name  from s_marketingcardtype  where marketingcardtype_id ='.$row["cardtype"].' limit 1';
echo $ctsqlstr;
$ctdata=mysql_query($ctsqlstr); 	
while($cardtypedata =mysql_fetch_array($ctdata)){
$marketingcardtype_name=$cardtypedata['marketingcardtype_name'];
}
} 
echo $marketingcardtype_name."\t";

$marketingcard_name='';
if($row['cardid']<>0){
$carditystr='select marketingcard_name  from s_marketingcard  where marketingcard_id ='.$row["cardid"].' limit 1';
 
$cardidata=mysql_query($carditystr); 	
while($cardiddata =mysql_fetch_array($cardidata)){
$marketingcard_name =$cardiddata['marketingcard_name'];
 
}
} 
echo $marketingcard_name."\t";


$customercard_no='';
if($row['customercardid']<>0){
$customercstr='select '.$cardtable_name[$row['cardtype']].'_no  from s_'.$cardtable_name[$row['cardtype']].'  where  '.$cardtable_name[$row['cardtype']].'_id ='.$row["customercardid"].' limit 1';
$customercdata=mysql_query($customercstr); 	
while($customercarddata =mysql_fetch_array($customercdata)){
$customercard_no =$customercarddata[$cardtable_name[$row['cardtype']].'_no'];
 
}
} 
echo $customercard_no."\t";
echo $row['customercardid']."\t";

*/



$services_no='';
$services_name='';
$services_id='';
if($row['item_id']<>''){
	
$cardstr="select *  from s_marketingcard A INNER JOIN s_marketingcardtype  B ON  A.marketingcardtype_id=B.marketingcardtype_id where  A.marketingcard_id =".$row['item_id']." limit 1";
 //echo "select *  from s_marketingcard A INNER JOIN s_marketingcardtype  B ON  A.marketingcardtype_id=B.marketingcardtype_id where  A.marketingcard_id =".$row['item_id']." limit 1";
$cardata=mysql_query($cardstr); 	
while($carddata =mysql_fetch_array($cardata)){
$marketingcardtype_name=$carddata['marketingcardtype_name'];
$marketingcard_name=$carddata['marketingcard_name'];
//$services_id=$servicesdata['marketingcard_no'];
}
} 
echo $row['item_id']."\t";
echo $marketingcardtype_name."\t";
echo $marketingcard_name."\t";


echo $row['customer_id']."\t";



$customer_no='';
$customer_name='';
 
if($row['customer_id']<>0){
	
$cusstr='SELECT * FROM  s_customer A   WHERE  customer_id='.$row['customer_id'].' limit 1';
$cusdata=mysql_query($cusstr); 	
while($customerdata =mysql_fetch_array($cusdata)){
$customer_name=$customerdata['customer_name'];
$customer_no=$customerdata['customer_no'];
 
}
} 
echo $customer_no."\t";
echo $customer_name."\t";
//echo $membercard_no."\t";
//echo $cardlevel_name."\t";

echo $row['membercard_no']."\t";
$cardlevel_name='';
if($row['membercard_no']<>''){
$memstr='SELECT * FROM (s_customer A LEFT JOIN s_membercard B ON a.customer_id = B.customer_id) LEFT JOIN s_memcardlevel C ON B.cardlevel_id = C.cardlevel_id WHERE A.customer_id='.$row['customer_id'].' limit 1';
$memdata=mysql_query($memstr); 	
while($memberdata =mysql_fetch_array($memdata)){
$cardlevel_name=$memberdata['cardlevel_name'];
}
} 
echo $cardlevel_name."\t";
//echo $row['customer_no']."\t";
//echo $row['customer_name']."\t";
//echo $row['membercard_no']."\t";
/*$cardlevel_name='';
if($row['membercard_no']<>''){
$memstr='SELECT * FROM (s_customer A LEFT JOIN s_membercard B ON a.customer_id = B.customer_id) LEFT JOIN s_memcardlevel C ON B.cardlevel_id = C.cardlevel_id WHERE A.customer_id='.$row['customer_id'].' limit 1';
$memdata=mysql_query($memstr); 	
while($memberdata =mysql_fetch_array($memdata)){
$cardlevel_name=$memberdata['cardlevel_name'];
}
} 
echo $cardlevel_name."\t";	*/
echo $row['employee_id']."\t";
echo $row['employee_no']."\t";
echo $row['employee_name']."\t";
//echo $row['sellstatus']."\t";

//美容师
$beauty_name='';
$beauty_no='';
if($row['beauty_id']<>0){
$beauty_id=str_replace(";",",",$row['beauty_id']);
$beautysqlstr='select * from s_employee  where employee_id in('.$beauty_id.') '; 
$beaudata=mysql_query($beautysqlstr); 	
while($beautydata =mysql_fetch_array($beaudata)){
$beauty_name=$beauty_name==''?$beautydata['employee_name']:$beauty_name.";".$beautydata['employee_name'];
$beauty_no=$beauty_no==''?$beautydata['employee_no']:$beauty_no.";".$beautydata['employee_no'];
}
} 
echo $row['beauty_id']."\t";
echo $beauty_no."\t";
echo $beauty_name."\t";	
				 
echo $itemtype_name[$row['item_type']]."\t";

echo $status_name[$row['sellstatus']]."\t";
echo $row['creattime']."\t";
echo $memo."\n";
}

?>