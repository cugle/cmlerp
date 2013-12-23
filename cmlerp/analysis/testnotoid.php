<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
 

	function disp(){
 
			$inrs = &$this -> dbObj -> Execute("SELECT * FROM ".WEB_ADMIN_TABPOX."sell ");

	     	while ($inrrs = &$inrs -> FetchRow()) {
				 
 if($inrrs['membercard_no']<>''){
				$membercard = &$this -> dbObj -> GetRow("SELECT * FROM ".WEB_ADMIN_TABPOX."membercard where  membercard_no='".$inrrs['membercard_no']."' ");
					//echo "SELECT * FROM ".WEB_ADMIN_TABPOX."membercard  where membercard_no='".$inrrs['membercard_no']."' and agencyid =".$_SESSION['currentorgan'];
				if($membercard['membercard_id']<>''){
				
  				$this -> dbObj -> Execute("update  ".WEB_ADMIN_TABPOX."sell  set membercard_id  =".$membercard['membercard_id']." where sell_id=".$inrrs['sell_id']);
				echo "update  ".WEB_ADMIN_TABPOX."sell  set membercard_id =".$membercard['membercard_id']." where sell_id=".$inrrs['sell_id'];
				}
			 }
			}
			
			$inrs -> Close();	
 
	}
 
	
}
$main = new Pagecustomer();
$main -> Main();
?>
  