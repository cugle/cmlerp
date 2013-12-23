<html>
<head>
</head>
<?php
$fname = $_FILES['MyFile']['name']; 
$do = copy($_FILES['MyFile']['tmp_name'],$fname); 
if ($do) 
{ 
echo"导入数据乐成";
} else { 
echo ""; 
}
?> 
<FORM action="<?php echo $_SERVER["PHP_SELF"];?>" encType=multipart/form-data  METHOD="POST"> <BR>
<P>导入CVS数据 <INPUT type=file name=MyFile> <INPUT type=submit value=提交> <BR></P><BR></FORM>				  				  
<?php
error_reporting(0); 
//导入CSV格局的文件 
$connect=mysql_connect("localhost","root","123qaz") or die("could not connect to database"); 
mysql_select_db("cmlerp",$connect) or die (mysql_error()); 
$fname = $_FILES['MyFile']['name']; 
$handle=fopen("$fname","r"); 
while($data=fgetcsv($handle,10000,",")) 
{ 
$q="insert into s_gender  (gender_name,gender_id ) values ('$data[0]','$data[1]')"; 
mysql_query("SET NAMES  'gbk'");
mysql_query($q) or die (mysql_error()); 

} 
fclose($handle); 
?> 			  
