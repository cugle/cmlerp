<?php

$filename="choujiang.xls";//先定义一个excel文件
header("Content-Type: application/vnd.ms-execl"); 
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 
//print(chr(0xEF).chr(0xBB).chr(0xBF));
echo  mb_convert_encoding("店名","GBK","utf-8")."\t";
echo "单据编号"."\t";
echo "顾客id"."\t";
echo "顾客编号"."\t";
echo "顾客姓名"."\t";
echo "会员卡号"."\t";
echo "顾客级别"."\t";
echo "应付"."\t";
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
echo "录单时间"."\n";


/*$conn = mysql_connect("localhost","root","123qaz");
mysql_select_db("cmlerp");
$sql="SELECT A.status as sellstatus,A.*,B.* , C.* FROM (s_sell A LEFT JOIN s_customer B ON a.customer_id=B.customer_id ) LEFT JOIN s_employee C ON C.employee_id=A.employee_id";
mysql_query("SET NAMES GBK");
$result=mysql_query($sql); 
while($row =mysql_fetch_array($result)){
echo $row['agencyid']."\t";
echo $row['sell_no']."\t";
echo $row['customer_id']."\t";
echo $row['customer_no']."\t";
echo $row['customer_name']."\t";
echo $row['membercard_no']."\t";
echo $row['membercard_level']."\t";
echo $row['payable1']."\t";
echo $row['realpay']."\t";
echo $row['employee_id']."\t";
echo $row['employee_no']."\t";
echo $row['employee_name']."\t";
echo $row['sellstatus']."\t";
echo $row['xianjinvalue']."\t";
echo $row['yinkavalue']."\t";
echo $row['zengsongvalue']."\t";
echo $row['dingjinvalue']."\t";
echo $row['chuzhikavalue']."\t";
echo $row['xianjinquanvalue']."\t";
echo $row['yufuvalue']."\t";
echo $row['creattime']."\n";
}*/

?>