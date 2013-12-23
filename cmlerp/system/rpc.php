<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class PageUser extends admin {
function disp(){
if(strlen(trim($_POST['queryString'])) >0) {
	$inrs = $this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX.'customer WHERE customername like "%'.trim($_POST['queryString']).'%" LIMIT 0 , 10');	
	while ($inrrs = &$inrs -> FetchRow()) {
				$customerid=$inrrs['customerid'];		
				$customername=$inrrs['customername'];
				$address=$inrrs['address'];
				$tel=$inrrs['tel'];
				$handphone=$inrrs['handphone'];
				$areaid=$inrrs['areaid'];
				$userid=$inrrs['userid'];
				$inrs1 = $this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX.'area WHERE area_id = "'.$areaid.'"');
				
	            while ($inrrs1 = &$inrs1 -> FetchRow()) {	
				$areaname=$inrrs1['area_name'];
				}
				$inrs1 -> Close();	
				$inrs2 = $this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX.'user WHERE userid = "'.$userid.'"');
				
	            while ($inrrs2 = &$inrs2 -> FetchRow()) {	
				$username=$inrrs2['username'];
				}
				$inrs2 -> Close();
					
				echo '<li onClick="fill(\''.$customerid.'\',\''.$customername.'\',\''.$address.'\',\''.$tel.'\',\''.$handphone.'\');">'.$customername.'['.$address.']['.$username.']</li>';
			  }
			$inrs -> Close();	
	   }
   }
}
$main = new PageUser();
$main -> Main();
?>