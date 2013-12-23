<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class PageUser extends admin {
function disp(){
//$table=$_GET['table'];
//$ziduan=$_GET['ziduan'];
//$testname=$_GET['testname'];
$table=$_POST['table'];
$ziduan=$_POST['ziduan'];
$testname=$_POST['testname'];
$id=$_POST['id'];
//if($id==''){
if($this->check_chongfu($table,$ziduan,$testname)>0){
if($ziduan=='customer_no'){
echo "<font color=red>编号重复</font>";

}else{
echo "<font color=red>存在同名顾客</font>";
}
}
else{
echo "";
}
/*}else{

if($this->check_chongfu($table,$ziduan,$testname)>1){
if($ziduan=='customer_no'){
echo "<font color=red>编号重复</font>";

}else{
echo "<font color=red>姓名重复</font>";
}
}
else{
echo "";
}

}*/
}
function check_chongfu($table,$ziduan,$testname)
{
//$result = mysql_query("select * from $table where $ziduan='$testname'");
$inrs= &$this -> dbObj -> Execute("select * from $table where $ziduan='$testname'");
$recordcount=$inrs -> Recordcount();
if($recordcount){
return $recordcount;
}else{
return 0;
}
//if(mysql_affected_rows() > 0) return true;
//else return false;
//}   
   
}
$main = new PageUser();
$main -> Main();
?>