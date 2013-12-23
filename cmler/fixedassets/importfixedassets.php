<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
function makeno($Prefix,$agency_no,$table,$column,$number,$id){
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by ".$id." desc limit 1");
//echo $this -> dbObj -> GetRow("select ".$column." from ".$table." order by ".$id." desc limit 1");
$nostr=$nostr[$column];
if($nostr==''){
$nostr=$Prefix.$agency_no.str_pad(1,$number,'0',STR_PAD_LEFT);

}else{
$nostr=mb_substr($nostr,strlen($nostr)-$number,$number,'utf-8');
$nostr=$nostr+1;
$nostr=str_pad($nostr,$number,'0',STR_PAD_LEFT);
$nostr=$Prefix.$agency_no.$nostr;
}
return $nostr;
}	
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
		$Prefix='ZC';
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'fixedassets';
		$column='fixedassets_no';
		$number=5;
		$id='fixedassets_id';	
while($data=fgetcsv($handle,10000,",")) 
{
if($i>1){
$tempdata4=$data[4];
$data[4]=$this -> dbObj -> getone("SELECT unit_id FROM `".WEB_ADMIN_TABPOX."unit` WHERE unit_name='$data[4]' and agencyid=".$_SESSION["currentorgan"]) ;
 if($data[4]==''){
	
	 $this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."unit` (unit_name,agencyid)VALUES('".$tempdata4."',".$_SESSION["currentorgan"].")");
	 $data[4] = $this -> dbObj -> Insert_ID();
	 }
$tempdata2=$data[2];
$data[2]=$this -> dbObj -> getone("SELECT fixedassetscatalog_id FROM `".WEB_ADMIN_TABPOX."fixedassetscatalog` WHERE fixedassetscatalog_name='$data[2]' and agencyid=".$_SESSION["currentorgan"]) ;
if($data[2]==''){
	 $this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."fixedassetscatalog` (fixedassetscatalog_name,agencyid)VALUES('".$tempdata2."',".$_SESSION["currentorgan"].")");
	 $data[2] = $this -> dbObj -> Insert_ID();
	 }	
//生产系统编码

		$fixedassets_no=$this->makeno($Prefix,$agency_no,$table,$column,$number,$id);	

 $q= "INSERT INTO `s_fixedassets` ( `fixedassets_no`, `fixedassets_code`, `fixedassets_name`,`catalogid`,  `type`, `unitid`, `address`,  `value`, `nowvalue`, `keeper`, `user`,   `description`,`getdate1`,`usestatus`,`agencyid`)VALUES ('".$fixedassets_no."', '$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]','$data[9]','$data[10]','".date('Y-m-d')."',2,".$_SESSION["currentorgan"].")" ; 
 $this -> dbObj -> Execute($q) ;
 if(mysql_error()) {
  $error= '导入失败'.mysql_error();
 break;}
}
$i++;
} 
fclose($handle); 
        $t -> set_var('error',$error);
		$t -> set_var('backpath','fixedassets.php');
		$t -> set_var('format','资产代码,名称,类别,规格，单位,存放地点,原价值,现价值,保管人员,使用人员,特色描述');	
		$t -> set_var('PHP_SELF',$_SERVER["PHP_SELF"]);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
}

	
}
$main = new Pagecustomer();
$main -> Main();

 ?>

