<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=urf-8">
</head>
<?php
$fname = $_FILES['MyFile']['name']; 
$do = copy($_FILES['MyFile']['tmp_name'],$fname); 
if ($do) 
{ 
echo"���������ֳ�";
} else { 
echo ""; 
}
?> 
<FORM action="<?php echo $_SERVER["PHP_SELF"];?>" encType=multipart/form-data  METHOD="POST"> <BR>
<P>����CVS���� <INPUT type=file name=MyFile> <INPUT type=submit value=�ύ> <BR></P><BR></FORM>				  				  
<?php
error_reporting(0); 
//����CSV��ֵ��ļ� 
$connect=mysql_connect("localhost","root","123qaz") or die("could not connect to database"); 
mysql_select_db("cmlerp",$connect) or die (mysql_error()); 
$fname = $_FILES['MyFile']['name']; 
$handle=fopen("$fname","r"); 
while($data=fgetcsv($handle,10000,",")) 
{ 
$q="insert into s_produce  (`produce_no`,`produce_name`,  `categoryid`,  `brandid`, `standardunit`,  `viceunit`, `viceunitnumber`, `price`, `upperlimit`, `lowerlimit`, `code`, `shortcode` ,`address`, `efficacy`, `useway`, `basis`, `memo`) values ('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]','$data[9]','$data[10]','$data[11]','$data[12]','$data[13]','$data[14]','$data[15]','$data[16]','$data[17]')"; 
mysql_query("SET NAMES  'gbk'");
mysql_query($q) or die (mysql_error()); 

} 
fclose($handle); 
?> 			  