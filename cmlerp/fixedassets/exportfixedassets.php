<?php
$filename="salary.xls";//�ȶ���һ��excel�ļ�
header("Content-Type: application/vnd.ms-execl"); 
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

echo "ϵͳ����"."\t";
echo "�ʲ�����"."\t";
echo "����"."\t";
echo "���"."\t";
echo "���"."\t";
echo "��λ"."\t";
echo "��ŵص�"."\t";
echo "ԭ��ֵ"."\t";
echo "�ּ�ֵ"."\t";
echo "������Ա"."\t";
echo "ʹ����Ա"."\t";
echo "��ɫ����"."\n";

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