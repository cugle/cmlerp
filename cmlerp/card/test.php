<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/card.cls.php');

class Pagecustomer extends admin {
	var $CardObj = null;
    function Main()
    { 
	$cardtable_name=array("itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");
	
	for($i=1;$i<8;$i++){
	
	$cardtable=$cardtable_name[$i];
	
	$inrs1=$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$cardtable.' ');
	
	while ($inrrs1 = &$inrs1 -> FetchRow()) {
	
	
	$this -> dbObj -> Execute('update '.WEB_ADMIN_TABPOX.$cardtable.' set overdate ="'.date("Y-m-d",strtotime("$m+".$inrrs1['timelimit']." months",strtotime($inrrs1["activedate"]))).'" where '.$cardtable.'_id='.$inrrs1[$cardtable.'_id']);
	
	// echo 'update '.WEB_ADMIN_TABPOX.$cardtable.' set overdate ="'.date("Y-m-d",strtotime("$m+".$inrrs1['timelimit']." months",strtotime($inrrs1["activedate"]))).'" where '.$cardtable.'_id='.$inrrs1[$cardtable.'_id'];
	}
	}
	echo 'ok';
    }

	
}
$main = new Pagecustomer();
$main -> Main();
?>
  