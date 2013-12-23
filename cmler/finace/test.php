<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
/*require(WEB_ADMIN_CLASS_PATH.'/custom/cmlprice.cls.php');
*/
class Pagecustomer extends admin {
 
	function disp(){
		$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");
		

 		for($i=1;$i<9;$i++){
		$inrs = $this -> dbObj -> Execute("select * FROM ".WEB_ADMIN_TABPOX.$cardtable_name[$i]); 
		
		while ($inrrs = $inrs -> FetchRow()) {
		
		
		$sellcarddetail=$this -> dbObj -> GetRow("select * FROM ".WEB_ADMIN_TABPOX.'sellcarddetail where cardtype='.$i.' and  customercardid='.$inrrs[$cardtable_name[$i]."_id"]); 
		 
 		if($sellcarddetail){
		//echo "update ".WEB_ADMIN_TABPOX.$cardtable_name[$i].' set beauty_id="'.$sellcarddetail["beauty_id"].'", employee_id="'.$sellcarddetail["employee_id"].'" where  '.$cardtable_name[$i].'_id='.$sellcarddetail["customercardid"];
			//echo "update ".WEB_ADMIN_TABPOX.$cardtable_name[$i].' set beauty_id="'.$sellcarddetail["beauty_id"].'", employee_id="'.$sellcarddetail["employee_id"].'" where  '.$cardtable_name[$i].'_id='.$sellcarddetail["customercardid"];
		$result=$this -> dbObj -> Execute("update ".WEB_ADMIN_TABPOX.$cardtable_name[$i].' set beauty_id="'.$sellcarddetail["beauty_id"].'", employee_id="'.$sellcarddetail["employee_id"].'" where  '.$cardtable_name[$i].'_id='.$sellcarddetail["customercardid"]); 
		} 
		 
		}
		
		//echo "update ".WEB_ADMIN_TABPOX.$cardtable_name[$i].' set beauty_id="'.$sellcarddetail["beauty_id"].'", employee_id="'.$sellcarddetail["employee_id"].'" where  '.$cardtable_name[$i].'_id='.$sellcarddetail["customercardid"];
		if($result){echo $i.":ok";}
		}
		
		//定义模板
		//$t = new Template('../template/finace');
		//$t -> set_file('f','test.html');
		//$t->unknowns = "remove";
		//$t->left_delimiter = "[#"; //修改左边界符为[#
      //  $t->right_delimiter = "#]"; //修改右边界符#]
		
/*		$this->cmlpriceObj=new cmlprice();	
		$discount=$this->cmlpriceObj->main("1734");
 		 print_r($discount);
    
		echo  $discount['1734'];*/
 
		//$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		//$t -> parse('out','f');
		//$t -> p('out');
	}
 
	
}
$main = new Pagecustomer();
$main -> Main();
?>
  