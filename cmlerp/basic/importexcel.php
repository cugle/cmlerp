<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
	function disp(){
		//定义模板
$fname = $_FILES['MyFile']['name']; 
$do = copy($_FILES['MyFile']['tmp_name'],$fname); 
if ($do) 
{ 
echo"导入数据乐成";
} else { 
echo ""; 
}
error_reporting(0); 
//导入CSV格局的文件 
echo '<FORM action="'.$_SERVER["PHP_SELF"].'" encType=multipart/form-data  METHOD="POST"><P>导入CVS数据 <INPUT type=file name=MyFile> <INPUT type=submit value=提交> <BR></P></FORM>';

$fname = $_FILES['MyFile']['name']; 
$handle=fopen($fname,"r"); 
while($data=fgetcsv($handle,10000,",")) 
{ 
 $q="insert into s_gender  (gender_name,gender_id ) values ('$data[0]','$data[1]')"; 
 $this -> dbObj -> Execute("SET NAMES  'gbk'");
 $this -> dbObj -> Execute($q) or die('导入失败'.mysql_error());
} 
fclose($handle); 

}

	
}
$main = new Pagecustomer();
$main -> Main();

 ?>
