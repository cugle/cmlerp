<?
/**
 * @package System
 */
 
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
function disp(){
		//定义模板
		 
		$t = new Template('../template/pos');
		$t -> set_file('f','opennew.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		 
		
		$t -> parse('out','f');
		$t -> p('out');
}
}
$main = new Pagecustomer();
$main -> Main();
?>