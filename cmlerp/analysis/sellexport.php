<?php
$filename="销售单".date('Y-m-d').".xls";//先定义一个excel文件
header("Content-Type: application/vnd.ms-execl"); 
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 
echo "店名"."\t";
echo "单据编号"."\t";
echo "顾客id"."\t";
echo "顾客编号"."\t";
echo "顾客姓名"."\t";
echo "会员卡号"."\t";
echo "顾客级别"."\t";
echo "应收"."\t";
echo "实付"."\t";
echo "收银员id"."\t";
echo "收银员编号"."\t";
echo "收银员姓名"."\t";
echo "状态"."\t";
echo "现金支付"."\t";
echo "银行卡支付"."\t";
echo "赠送账支付户"."\t";
echo "定金支付"."\t";
echo "储值卡"."\t";
echo "现金券支付"."\t";
echo "预收款支付"."\t"; 
echo "录单时间"."\t";
echo "备注"."\n";
$status_name=array("未完成","已完成","红字冲销","被红字冲销","已提交","已审核");
$conn = mysql_connect("localhost","root","123qaz");
mysql_select_db("cmlerp");
		$bgdate=$_POST["bgdate"]." 00:00:00";
		$enddate=$_POST["enddate"]." 23:59:59";		
		$timecondition=' A.creattime  between "'.$bgdate.'" and "'.$enddate.'" AND A.status IN ('.$_POST['typelist'].')';
$sql="SELECT A.status as sellstatus,A.*,B.* , C.* FROM (s_sell A LEFT JOIN s_customer B ON a.customer_id=B.customer_id ) LEFT JOIN s_employee C ON C.employee_id=A.employee_id where A.agencyid = ".$_POST["agencyid"].' and '.$timecondition;

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
echo $row['customer_id']."\t";
echo $row['customer_no']."\t";
echo $row['customer_name']."\t";

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
echo $row['payable1']."\t";
echo $row['realpay']."\t";
echo $row['employee_id']."\t";
echo $row['employee_no']."\t";
echo $row['employee_name']."\t";
//echo $row['sellstatus']."\t";
echo $status_name[$row['sellstatus']]."\t";
echo $row['xianjinvalue']."\t";
echo $row['yinkavalue']."\t";
echo $row['zengsongvalue']."\t";
echo $row['dingjinvalue']."\t";
echo $row['chuzhikavalue']."\t";
echo $row['xianjinquanvalue']."\t";
echo $row['yufuvalue']."\t";
echo $row['creattime']."\t";
$memo='';
$howtopay=explode(";",$row['howtopay']);
for($i=0;$i<count($howtopay);$i++){
$howtopay[$i]=explode(",",$howtopay[$i]);	
}
if($howtopay[5][2]>0 ){
$memo=$memo."使用储值卡：".$howtopay[5][3];
}
if($howtopay[6][2]>0 ){
$memo=$memo." 使用现金券：".$howtopay[6][3];
}
echo $memo."\n";
}

?>