<?php
$filename="���۵�".date('Y-m-d').".xls";//�ȶ���һ��excel�ļ�
header("Content-Type: application/vnd.ms-execl"); 
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 
echo "����"."\t";
echo "���ݱ��"."\t";
echo "�˿�id"."\t";
echo "�˿ͱ��"."\t";
echo "�˿�����"."\t";
echo "��Ա����"."\t";
echo "�˿ͼ���"."\t";
echo "Ӧ��"."\t";
echo "ʵ��"."\t";
echo "����Աid"."\t";
echo "����Ա���"."\t";
echo "����Ա����"."\t";
echo "״̬"."\t";
echo "�ֽ�֧��"."\t";
echo "���п�֧��"."\t";
echo "������֧����"."\t";
echo "����֧��"."\t";
echo "��ֵ��"."\t";
echo "�ֽ�ȯ֧��"."\t";
echo "Ԥ�տ�֧��"."\t"; 
echo "¼��ʱ��"."\t";
echo "��ע"."\n";
$status_name=array("δ���","�����","���ֳ���","�����ֳ���","���ύ","�����");
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
$memo=$memo."ʹ�ô�ֵ����".$howtopay[5][3];
}
if($howtopay[6][2]>0 ){
$memo=$memo." ʹ���ֽ�ȯ��".$howtopay[6][3];
}
echo $memo."\n";
}

?>