<? 
$conn = mysql_connect("localhost","root","123qaz");
mysql_select_db("choujiang");
$sql="SELECT * FROM userinfo";
mysql_query("SET NAMES GBK");
$result1=mysql_query($sql); 
mysql_close($con);
$conn = mysql_connect("localhost","root","123qaz");
mysql_select_db("power");
mysql_query("SET NAMES GBK");
while($row1 =mysql_fetch_array($result1)){
$idnumber=$row1['idnumber'];
mysql_select_db("power");

$sql="SELECT count(*)  FROM s_customer where idnumber='".$idnumber."'";
$result2=mysql_query($sql); 
while($row2 =mysql_fetch_array($result2)){
if ($row2['count(*)']>0){
echo "";
}else
{
//mysql_query("INSERT INTO s_customer(customername,gender,birthday,national,idnumber,tel, handphone,address,zipcode,email,areaid,customerpass,loginnum,userid,importer,qq)values('".$rows['customername']."',".$rows["gender"].",'".$rows["birthday"]."','".$rows["national"]."','".$rows["idnumber"]."','".$rows["tel"]."','".$rows["handphone"]."','".$rows["address"]."','".$rows["zipcode"]."','".$rows["email"]."',".$rows["areaid"].",'".md5($rows['customerpass'])."',0,".$rows["userid"].",".1.",'".$rows["qq"]."')");

mysql_query("INSERT INTO `s_customer` (`customerid`, `customername`, `customerpass`, `gender`, `birthday`, `national`, `idnumber`, `tel`, `handphone`, `address`, `zipcode`, `email`, `store_name`, `store_tel`, `store_address`, `procode`, `createtime`, `customerstatus`, `memo`, `cwstatus`, `loginnum`, `importer`, `areaid`, `ishide`, `orderid`, `fraction`, `satisfaction`, `userid`, `qq`) VALUES
( '".$row1['id']."', '".$row1['username']."', '0',".$row1["gender"].",'".$row1["birthday"]."','".$row1["national"]."','".$row1["idnumber"]."','".$row1["tel"]."','".$row1["handphone"]."','".$row1["address"]."','".$row1["zipcode"]."','".$row1["email"]."', '".$row1["store_name"]."',  '".$row1["store_tel"]."', '".$row1["store_address"]."',  '".$row1["procode"]."',  '".$row1["created"]."',  '".$row1["statuss"]."',  '".$row1["memo"]."',  '".$row1["cwstatus"]."', 0, 1, 0, 0, NULL, 0, NULL, 0, NULL)");
}
}

//$sql="";
//mysql_query($sql); 
}
echo "导入成功！";
mysql_close($con);
?> 