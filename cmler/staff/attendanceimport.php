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
$i=1;
while($data=fgetcsv($handle,10000,",")) 
{
if($i>1){
 $data[0]=$this -> dbObj -> getone("SELECT employee_id FROM `".WEB_ADMIN_TABPOX."employee` WHERE attendancecode='$data[0]' and agencyid=".$_SESSION["currentorgan"]) ;
 $q="update s_attendance   set `planattendance`='$data[1]',`actualattendance`='$data[2]' , `leavehours`='$data[3]',`timeoff`='$data[4]',`othours`='$data[5]'  where `employee_id`='$data[0]'" ; 
 echo  $q;
 $this -> dbObj -> Execute($q) ;
 if(mysql_error()) {
  $error= '导入失败'.mysql_error();
 break;}
}
$i++;
} 
fclose($handle); 
        $t -> set_var('error',$error);
		$t -> set_var('backpath','attendance.php');
		$t -> set_var('format','考勤编号 计划出勤 实际出勤  请假(h) 补钟(h) 加班(h)');	
		$t -> set_var('PHP_SELF',$_SERVER["PHP_SELF"]);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
}

	
}
$main = new Pagecustomer();
$main -> Main();

 ?>

