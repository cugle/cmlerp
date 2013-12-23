<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><HEAD><TITLE></TITLE>
<META http-equiv=Content-Type content="text/html; charset=gbk">
</HEAD>
<?php
header("Content-Type:text/html;charset=gbk");
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
$agencyid=1;
$data[0]=mysql_query("SELECT employee_id FROM `s_employee` WHERE employee_name='$data[0]' and agencyid=".$agencyid) ;
$q="insert into s_attendance(`employee_id`,`planattendance`,`actualattendance` , `othours` ,`leavehours` ,agencyid) values ('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]',".$agencyid.")"; 

mysql_query($q) or die (mysql_error()); 

} 
fclose($handle); 
?> 			  
