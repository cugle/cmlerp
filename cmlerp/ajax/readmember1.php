<?
/**
 * @package System
 */

require('../admin.inc.php');

require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

$main = new admin();
echo $_GET['sendTime'];
echo $_GET['bookTime'];
 
?>