<?
/**
 * @package System
 */

require('../admin.inc.php');

require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/card.cls.php');

class Pagecustomer extends admin {
	var $cardObj = null;
    function Main()
    {   
	echo "test";
	$this->cardObj=new card();
	$this->cardObj->main();

    }
	
}
$main = new Pagecustomer();
$main -> Main();
?>
  