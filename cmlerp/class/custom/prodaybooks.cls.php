<?
/**
 * @package System
 */
 //商品流水账
//require(WEB_ADMIN_HTTPPATH.'/admin.inc.php');
//require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class prodaybooks extends admin {
	function main($produce_id,$warehouse_id=0,$addnumber=0,$addacount=0,$billtype,$billid,$man,$memo,$agencyid){
	
		$produce_id=explode(";",$produce_id);
		$addnumber=explode(";",$addnumber);	
		$addacount=explode(";",$addacount);	
		for ($i=0;$i<count($produce_id);$i++){	
		$rs=$this->addrecord($produce_id[$i],$warehouse_id,$addnumber[$i],$addacount[$i],$billtype,$billid,$man,$memo,$agencyid);//插入商品流水记录
		}
		return $rs;
	}
	
	//商品流水账
	function addrecord($produce_id,$warehouse_id,$addnumber,$addacount,$billtype,$billid,$man,$memo,$agencyid){
		$stock=$this->dbObj->GetRow("SELECT * FROM ".WEB_ADMIN_TABPOX."stock where produce_id=".$produce_id." and  warehouse_id=".$warehouse_id." and  agencyid=".$agencyid);
		
		$stocknumber=$stock['number'];
		$stockbalance=$stock['acount'];

		$this->dbObj->Execute("INSERT INTO  `".WEB_ADMIN_TABPOX."prodaybooks` (`produce_id` ,warehouse_id,`addnumber`,addacount,`billtype`,billid,stocknumber	,stockbalance,`man`,memo, date,agencyid) VALUE (".$produce_id.",".$warehouse_id.",".$addnumber.",".$addacount.",".$billtype.",".$billid.",".$stocknumber.",".$stockbalance.",'".$man."','".$memo."'  ,'".date('Y-m-d',time())."',".$agencyid.")");
		   //echo "INSERT INTO  `".WEB_ADMIN_TABPOX."prodaybooks` (`produce_id` ,warehouse_id,`addnumber`,addacount,`billtype`,billid,stocknumber	,stockbalance,`man`,memo, date,agencyid) VALUE (".$produce_id.",".$warehouse_id.",".$addnumber.",".$addacount.",".$billtype.",".$billid.",".$stocknumber.",".$stockbalance.",'".$man."','".$memo."'  ,'".date('Y-m-d',time())."',".$agencyid.")";
//echo "INSERT INTO  `".WEB_ADMIN_TABPOX."prodaybooks` (`produce_id` ,warehouse_id,`addnumber`,addacount,`billtype`,billid,stocknumber	,stockbalance,`man`,memo, agencyid) VALUE (".$produce_id.",".$warehouse_id.",".$addnumber.",".$addacount.",".$billtype.",".$billid.",".$stocknumber.",".$stockbalance.",'".$man."','".$memo."'  ,".$agencyid.")";
	 
		if(mysql_affected_rows()){
		
		return 1;
		
		}
	    else{
		 
		return 0;
		
		}
    }
	
}
//$main = new stock();
//$main -> Main();
?>
  