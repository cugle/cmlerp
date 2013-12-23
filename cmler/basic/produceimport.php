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
$data[2]=$this -> dbObj -> getone("SELECT category_id FROM `".WEB_ADMIN_TABPOX."procatalog` WHERE category_name='".$data[2]."' and agencyid=".$_SESSION["currentorgan"]) ;
$data[3]=$this -> dbObj -> getone("SELECT brand_id FROM `".WEB_ADMIN_TABPOX."brand` WHERE brand_name='".$data[3]."' and agencyid=".$_SESSION["currentorgan"]) ;
$data[4]=$this -> dbObj -> getone("SELECT unit_id FROM `".WEB_ADMIN_TABPOX."unit` WHERE unit_name='".$data[4]."' and agencyid=".$_SESSION["currentorgan"]) ;
$data[5]=$this -> dbObj -> getone("SELECT unit_id FROM `".WEB_ADMIN_TABPOX."unit` WHERE unit_name='".$data[5]."' and agencyid=".$_SESSION["currentorgan"]) ;
$data[3]=$data[3]==''?0:$data[3];
$data[4]=$data[4]==''?0:$data[4];
$data[5]=$data[5]==''?0:$data[5];
$data[6]=$data[6]==''?0:$data[6];

 $q="insert into s_produce  (`produce_no`,produce_name,`categoryid`,  `brandid`, `standardunit`,`viceunit`,`viceunitnumber`, `price`, `upperlimit`, `lowerlimit`, `code`, `shortcode`, `address`, `efficacy`, `useway`, `basis`, memo,agencyid) values ('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]','$data[9]','$data[10]','$data[11]','$data[12]','$data[13]','$data[14]','$data[15]','$data[16]',".$_SESSION["currentorgan"].")"; 
 $this -> dbObj -> Execute($q) ;
 if(mysql_error()) {
  $error= '导入失败'.mysql_error();
 break;}
} 
fclose($handle); 
        $t -> set_var('error',$error);
		$t -> set_var('backpath','produce.php');
		$t -> set_var('format','编码,名称,类别,品牌,主单位,副单位,容量,牌价,库存上限,下限,代码,简码,产地,功效,用法,成分,备注');	
		$t -> set_var('PHP_SELF',$_SERVER["PHP_SELF"]);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
}

	
}
$main = new Pagecustomer();
$main -> Main();

 ?>

