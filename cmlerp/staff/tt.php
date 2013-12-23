<?
/**
 * @package System
 */
header("Content-Type:text/html;charset=gbk");
require('../admin.inc.php');
header("Content-Type:text/html;charset=gbk");
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
header("Content-Type:text/html;charset=gbk");
class Pagecustomer extends admin {
	function disp(){
		//定义模板
		$t = new Template('../template/basic');
		$t -> set_file('f','import1.html');
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
$name=$data[0];
 $data[0]=$this -> dbObj -> getone("SELECT employee_id FROM `".WEB_ADMIN_TABPOX."employee` WHERE employee_name='$data[0]' and agencyid=".$_SESSION["currentorgan"]) ;
echo "SELECT employee_id FROM `".WEB_ADMIN_TABPOX."employee` WHERE employee_name='".$data[0]."' and agencyid=".$_SESSION["currentorgan"];
 $q="insert into s_attendance(`employee_id`,`planattendance`,`actualattendance` , `othours` ,`leavehours` ,agencyid) values ('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]',".$_SESSION["currentorgan"].")"; 
 $this -> dbObj -> Execute($q) ;
 if(mysql_error()) {
  $error= '导入失败'.mysql_error();
 break;}
} 
fclose($handle); 
        $t -> set_var('error',$error);
		$t -> set_var('backpath','attendance.php');
		$t -> set_var('format','员工编号 计划出勤 实际出勤 加班(h) 请假(h)');	
		$t -> set_var('PHP_SELF',$_SERVER["PHP_SELF"]);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
}

	
}
$main = new Pagecustomer();
$main -> Main();

 ?>

