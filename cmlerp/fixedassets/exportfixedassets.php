<?php
$filename="salary.xls";//先定义一个excel文件
header("Content-Type: application/vnd.ms-execl"); 
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

echo "系统编码"."\t";
echo "资产代码"."\t";
echo "名称"."\t";
echo "类别"."\t";
echo "规格"."\t";
echo "单位"."\t";
echo "存放地点"."\t";
echo "原价值"."\t";
echo "现价值"."\t";
echo "保管人员"."\t";
echo "使用人员"."\t";
echo "特色描述"."\n";

$conn = mysql_connect("localhost","root","123qaz");
mysql_select_db("cmlerp");
$agencyid=$_GET['agencyid'];
$sql='select * from s_fixedassets  where agencyid ='.$agencyid.' ORDER BY  fixedassets_id DESC ';
mysql_query("SET NAMES GBK");
$result=mysql_query($sql); 
while($inrrs =mysql_fetch_array($result)){
	
$unit=mysql_query("select * from s_unit   where  unit_id=".$inrrs['unitid']." LIMIT 0 , 1 ");
while($row =mysql_fetch_array($unit)){
$unit_name=$row['unit_name'];
}
$catalog=mysql_query("select * from s_fixedassetscatalog  where  fixedassetscatalog_id=".$inrrs['catalogid']." LIMIT 0 , 1 ");
while($row =mysql_fetch_array($catalog)){
$fixedassetscatalog_name=$row['fixedassetscatalog_name'];
}
echo $inrrs["fixedassets_no"]."\t";
echo $inrrs['fixedassets_code']."\t";
echo $inrrs['fixedassets_name']."\t";
echo $fixedassetscatalog_name."\t";
echo $inrrs['type']."\t";
echo $unit_name."\t";
echo $inrrs['address']."\t";
echo $inrrs['value']."\t";
echo $inrrs['nowvalue']."\t";
echo $inrrs['keeper']."\t";
echo $inrrs['user']."\t";
echo $inrrs['description']."\n";
}

?>