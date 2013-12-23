<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
 

	function disp(){
 
			$inrs = &$this -> dbObj -> Execute("SELECT * FROM ".WEB_ADMIN_TABPOX."sell where  agencyid =".$_SESSION['currentorgan']);

	     	while ($inrrs = &$inrs -> FetchRow()) {
				 
 if($inrrs['membercard_id']<>0){
				$membercard = &$this -> dbObj -> GetRow("SELECT * FROM ".WEB_ADMIN_TABPOX."membercard where  membercard_id='".$inrrs['membercard_id']."'   and  agencyid =".$_SESSION['currentorgan']);
					//echo "SELECT * FROM ".WEB_ADMIN_TABPOX."membercard  where membercard_no='".$inrrs['membercard_no']."' and agencyid =".$_SESSION['currentorgan'];
				if($membercard['membercard_no']<>''){
				
  				$this -> dbObj -> Execute("update  ".WEB_ADMIN_TABPOX."sell  set membercard_no  ='".$membercard['membercard_no']."' where sell_id=".$inrrs['sell_id']);
				echo "update  ".WEB_ADMIN_TABPOX."sell  set membercard_no ='".$membercard['membercard_no']."' where sell_id=".$inrrs['sell_id'];
				}
			 }
			}
			
			$inrs -> Close();	
 
	}
 
	
}
$main = new Pagecustomer();
$main -> Main();
?>
  