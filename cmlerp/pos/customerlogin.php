<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/sell.cls.php');
class Pageproduce extends admin {
	function disp(){
		//定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','customerlogin.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
	 
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');	}
 
	
}
$main = new Pageproduce();
$main -> Main();
?>
  