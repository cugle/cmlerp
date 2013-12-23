<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
	function disp(){
		//定义模板
		$t = new Template('../template/basic');
		$t -> set_file('f','import.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
$fname = $_FILES['MyFile']['name']; 
$do = copy($_FILES['MyFile']['tmp_name'],$fname); 
if($_FILES['MyFile']['name']){
if ($do) 
{ 
  $error= '导入成功，请返回查看';
} else { 
$error= "导入失败"; 
}}
error_reporting(0); 				
$fname = $_FILES['MyFile']['name']; 
$handle=fopen($fname,"r"); 
while($data=fgetcsv($handle,10000,",")) 
{
$this -> dbObj -> Execute("SET NAMES  'utf8'"); 
$data[2]=$data[2]=='女'?2:1;
$data[3]=$data[3]==''?0:$data[3];
$data[4]=$data[4]==''?0:$data[4];
$data[5]=$data[5]==''?0:$data[5];
$data[6]=$data[6]==''?0:$data[6];

 $q="insert into s_customer  (`customer_no`,customer_name,`genderid`,  `handphone`, `qq`,`idnumber`,`email`, `address`, `birthday`,`province`, `city`, memo,agencyid) values ('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]', '$data[9]','$data[10]','$data[11]',".$_SESSION["currentorgan"].")"; 
 $this -> dbObj -> Execute($q) ;
 if(mysql_error()) {
  $error= '导入失败'.mysql_error();
 break;}
} 
fclose($handle); 
        $t -> set_var('error',$error);
		$t -> set_var('backpath','customer.php');
		$t -> set_var('format','顾客编号 姓名 性别 手机 qq  身份证 email 地址 出生日期 省份 城市 备注');	
		$t -> set_var('PHP_SELF',$_SERVER["PHP_SELF"]);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
}

	
}
$main = new Pagecustomer();
$main -> Main();

 ?>

