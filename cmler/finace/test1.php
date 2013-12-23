<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/yeji.cls.php');

class Pagecustomer extends admin {
 
	function disp(){
		 
		$this->yejiObj=new yeji();
		$res=$this->yejiObj->yeji1('3611');
		if($res){
		echo "ok";
		}else{
		echo "not ok";
		}
	}
 
	
}
$main = new Pagecustomer();
$main -> Main();
?>
  