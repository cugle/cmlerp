<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
	function disp(){
		//定义模板
		$t = new Template('../template/procurement');
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
}
error_reporting(0); 				
$fname = $_FILES['MyFile']['name']; 
$handle=fopen($fname,"r"); 
$i=0;
$acount=0;
while($data=fgetcsv($handle,10000,",")) 
{
$result[$i]=$data;	

$i=$i+1;

/*$this -> dbObj -> Execute("SET NAMES  'utf8'"); 
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
 break;}*/
} 
for($j=5;$j<$i-2;$j++){
$acount=$acount+$result[$j][7];
}
		$Prefix='OD';
		$agency_no=$_SESSION["agency_no"].date('Ym',time());
		$table=WEB_ADMIN_TABPOX.'order';
		$column='order_no';
		$number=3;
		$id='order_id';	
 
		$suppliers_id=$_GET['suppliers_id'];
		$warehouse_id=$_GET['warehouse_id'];
		$employee_id=$_GET['employee_id'];
		$order_time=$_GET['order_time'];

		$order_no=$this->makeno($Prefix,$agency_no,$table,$column,$number,$id);	 
		$memo=mb_convert_encoding($result[3][0], "utf-8", "gbk");
		$man=$this -> dbObj -> getone('select B.employee_name from '.WEB_ADMIN_TABPOX.'user A INNER JOIN '.WEB_ADMIN_TABPOX.'employee B ON A.employee_id=B.employee_id  where A.userid = '.$this->getUid());
		$memo=substr($memo,9,strlen($memo))."; ".mb_convert_encoding($result[1][3], "utf-8", "gbk");;
		$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."order` (`order_no`,`warehouse_id`,`suppliers_id`,`employee_id`, `agencyid`,`man`,`status`,memo,acount,order_time) VALUES ('" .$order_no."' ,'".$warehouse_id."','".$suppliers_id."','".$employee_id."','".$_SESSION["currentorgan"]."', '".$man."',0,'".$memo."','".$acount."','".$order_time."')");
		//echo "INSERT INTO `".WEB_ADMIN_TABPOX."order` (`order_no`,`warehouse_id`,`suppliers_id`,`employee_id`, `agencyid`,`man`,`status`,memo,acount) VALUES ('" .$order_no."' ,'".$warehouse_id."','".$suppliers_id."','".$employee_id."','".$_SESSION["currentorgan"]."', '".$man."',0,'".$memo."','".$acount."')";
 
		$id = $this -> dbObj -> Insert_ID();
for($j=5;$j<$i-3;$j++){

$produceid=$this -> dbObj -> GetOne('select produce_id  from '.WEB_ADMIN_TABPOX.'produce  where code  ="'.$result[$j][1].'" and agencyid='.$_SESSION["currentorgan"]);
//echo $result[$j][3];//数量
 
$price=$result[$j][5]?$result[$j][5]:0;
$orderprice=$result[$j][6]?$result[$j][6]:0;//结算单价
$totalacount=$result[$j][7]?$result[$j][7]:0;//结算金额
$discount=$orderprice/$price;
if($price==0){$discount=0;}
 
$cmemo=mb_convert_encoding($result[$j][8], "utf-8", "gbk");

	$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."orderdetail` (order_id,produce_id,number,orderprice,price,discount,totalacount,memo,warehouse_id,agencyid) VALUES ('".$id."','".$produceid."','".$result[$j][3]."','".$orderprice."','".$price."','".$discount."','".$totalacount."','".$cmemo."','".$warehouse_id."','".$_SESSION["currentorgan"]."')");	
	
	 //echo "INSERT INTO `".WEB_ADMIN_TABPOX."orderdetail` (order_id,produce_id,number,orderprice,price,discount,totalacount,memo,warehouse_id,agencyid) VALUES ('".$id."','".$produceid."','".$result[$j][3]."','".$orderprice."','".$price."','".$discount."','".$totalacount."','".$cmemo."','".$warehouse_id."','".$_SESSION["currentorgan"]."')";
}	
 

$t -> set_var('backpath','order.php?action=upd&updid='.$id); 
fclose($handle); 
}else{
$t -> set_var('backpath','order.php?action=add');
}
        $t -> set_var('error',$error);
		$suppliers_id=$_GET['suppliers_id'];
		$warehouse_id=$_GET['warehouse_id'];
		$employee_id=$_GET['employee_id'];
		$order_time=$_GET['order_time'];
		$t -> set_var('format','');	
		$t -> set_var('PHP_SELF',$_SERVER["PHP_SELF"].'?suppliers_id='.$suppliers_id.'&warehouse_id='.$warehouse_id.'&employee_id='.$employee_id.'&order_time='.$order_time);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
}

function makeno($Prefix,$agency_no,$table,$column,$number,$id){
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by ".$id." desc limit 1");
//echo "select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by ".$id." desc limit 1";
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
}
$main = new Pagecustomer();
$main -> Main();

 ?>

