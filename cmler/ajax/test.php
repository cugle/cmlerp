<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class PageUser extends admin {
function disp(){
		$v=$_POST[value];
		$re=$this -> dbObj -> Execute("select * from transfervoucher where transfervoucher_no like '%$v%' order by transfervoucher_no desc limit 10");
		if(mysql_num_rows($re)<=0) exit('0');
		echo '<ul>';
		while ($ro = &$re -> FetchRow()) {
		echo '<li><a href="">'.$ro['transfervoucher_no']..$ro['transfervoucher_name'].'</a></li>';
		}
		echo '<li class="cls"><a href="javascript:;" onclick="$(this).parent().parent().parent().fadeOut(100)">关闭</a& gt;</li>';
		echo '</ul>';
}
$main = new PageUser();
$main -> Main();
?>

 