<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class PageUser extends admin {
function disp(){
$q=$_POST['queryString'];

if(strlen(trim($q)) >0) {

	$inrs = $this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX.'agency WHERE agencytype_id='.$q);	

	while ($inrrs = &$inrs -> FetchRow()) {	
				echo '<option value='.$inrrs['agency_id'].'>'.$inrrs['agency_easyname'].'</option>';
			  }
			$inrs -> Close();	
	   }
   }
}
$main = new PageUser();
$main -> Main();
?>