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
//$data[2]=$this -> dbObj -> getone("SELECT category_id FROM `".WEB_ADMIN_TABPOX."servicecategory` WHERE category_name ='".$data[2]."' and agencyid=".$_SESSION["currentorgan"]) ;
$data[2]=$this -> dbObj -> getone("SELECT servicespart_id FROM `".WEB_ADMIN_TABPOX."servicespart` WHERE servicespart_name ='".$data[2]."' and agencyid=".$_SESSION["currentorgan"]) ;

$data[2]=$data[2]==''?0:$data[2];

 $q="insert into  ".WEB_ADMIN_TABPOX."services (services_no ,services_name,servicespartid ,price , memo,agencyid) values ('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]',".$_SESSION["currentorgan"].")"; 
 $this -> dbObj -> Execute($q) ;
 if(mysql_error()) {
  $error= '导入失败'.mysql_error();
 break;}
} 
fclose($handle); 
        $t -> set_var('error',$error);
		$t -> set_var('backpath','services.php');
		$t -> set_var('format','services_no ,services_name,categoryid ,price , memo,agencyid');	
		$t -> set_var('PHP_SELF',$_SERVER["PHP_SELF"]);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
}

	
}
$main = new Pagecustomer();
$main -> Main();

 ?>
