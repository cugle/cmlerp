<?
/**
 * @package System
 */
//require(WEB_ADMIN_HTTPPATH.'/admin.inc.php');
//require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class card extends admin {
	function main(){
		
		$this->creatcard();
		//$produce_id=explode(";",$produce_id);
		//$addnumber=explode(";",$addnumber);	
		//$addacount=explode(";",$addacount);	
		
	}
	
	function creatcard($card_no=0,$marketingcard_id=1,$customerid=0,$employeeid=0,$agencyid=1,$cardtable='itemcard', $beauty_id=0){
		$this -> dbObj -> Execute("START TRANSACTION");//事务开始。

		$res1= &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'marketingcard   where marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid);
		//echo 'select * from '.WEB_ADMIN_TABPOX.'marketingcard   where marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid;
		
		if($res1['assignservice']==1){
			
		$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail    where  servicecategory_id ="" and  marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid);
		}else if($res1['assignservice']==0){
		$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail    where  services_id="" and servicecategory_id ="" and marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid);	
		//echo 'select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail    where  services_id="" and servicecategory_id ="" and marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid;
		}else if($res1['assignservice']==2){
		$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail    where  services_id="" and marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid);	
		//echo 'select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail    where  services_id="" and marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid;
		}
		//echo 'select * from '.WEB_ADMIN_TABPOX.'marketingcard   where marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid;
		//echo 'select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail    where marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid;
		$itemlist ='';
		$remaintimeslist ='';
		while ($inrrs = $inrs -> FetchRow()) {
		if($res1['assignservice']==1){
		$itemlist=$itemlist==''?$inrrs['services_id']:$itemlist."||".$inrrs['services_id'] ;
		}else if($res1['assignservice']==2){
		$itemlist=$itemlist==''?$inrrs['servicecategory_id']:$itemlist."||".$inrrs['servicecategory_id'] ;
		}		
		$remaintimeslist=$remaintimeslist==''?$inrrs['services_times']:$remaintimeslist."||".$inrrs['services_times'] ;	
		}
		$inrs -> Close();
		$overdate=date("Y-m-d",strtotime("$m+".$res1['timelimit']." months",time()));
		//$res=$this->dbObj->Execute('INSERT INTO '.WEB_ADMIN_TABPOX.$cardtable.' (`'.$cardtable.'_no`,`marketingcard_id`,`customer_id`,`employee_id`,`assignservice`, `price`,`value`, `timelimit`, `totaltimes`, `remaintimes`,`itemlist`, `remaintimeslist`, `marketingcardtimeslist`,`marketingcardvalue`,`activedate`, `buydate`, `status`, `memo`, `agencyid`) value ("'.$card_no.'","'.$marketingcard_id.'","'.$customerid.'","'.$employeeid.'","'.$res1['assignservice'].'","'.$res1['price'].'","'.$res1['value'].'","'.$res1['timelimit'].'","'.$res1['totaltimes'].'","'.$res1['totaltimes'].'","'.$itemlist.'","'.$remaintimeslist.'","'.$remaintimeslist.'","'.$res1['value'].'","'.date('Y-m-d').'","'.date('Y-m-d').'","1","'.$res1['memo'].'","'.$agencyid.'")');	
		   $res=$this->dbObj->Execute('INSERT INTO '.WEB_ADMIN_TABPOX.$cardtable.' (`'.$cardtable.'_no`,`marketingcard_id`,`customer_id`,`employee_id`,`assignservice`, `price`,`value`, `timelimit`, `totaltimes`, `remaintimes`,`itemlist`, `remaintimeslist`, `marketingcardtimeslist`,`marketingcardvalue`,`activedate`, `buydate`, `status`, `memo`, `agencyid`,overdate, beauty_id,payalldate,paystatus) value ("'.$card_no.'","'.$marketingcard_id.'","'.$customerid.'","'.$employeeid.'","'.$res1['assignservice'].'","'.$res1['price'].'","'.$res1['value'].'","'.$res1['timelimit'].'","'.$res1['totaltimes'].'","'.$res1['totaltimes'].'","'.$itemlist.'","'.$remaintimeslist.'","'.$remaintimeslist.'","'.$res1['value'].'","'.date('Y-m-d').'","'.date('Y-m-d').'","1","'.$res1['memo'].'","'.$agencyid.'","'.$overdate.'","'.$beauty_id.'","'.date("Y-m-d").'",1)');	//生成卡项时候 paystatus 置为1 有欠款状态 收银时候 跟进情况重置
		//echo 'INSERT INTO '.WEB_ADMIN_TABPOX.$cardtable.' (`'.$cardtable.'_no`,`marketingcard_id`,`customer_id`,`employee_id`,`assignservice`, `price`,`value`, `timelimit`, `totaltimes`, `remaintimes`,`itemlist`, `remaintimeslist`, `marketingcardtimeslist`,`marketingcardvalue`,`activedate`, `buydate`, `status`, `memo`, `agencyid`,overdate) value ("'.$card_no.'","'.$marketingcard_id.'","'.$customerid.'","'.$employeeid.'","'.$res1['assignservice'].'","'.$res1['price'].'","'.$res1['value'].'","'.$res1['timelimit'].'","'.$res1['totaltimes'].'","'.$res1['totaltimes'].'","'.$itemlist.'","'.$remaintimeslist.'","'.$remaintimeslist.'","'.$res1['value'].'","'.date('Y-m-d').'","'.date('Y-m-d').'","1","'.$res1['memo'].'","'.$agencyid.'","'.$overdate.'")';
		$id = $this -> dbObj -> Insert_ID();
		
	

		if($res && $res1){
		
		$this -> dbObj -> Execute("COMMIT");
		echo '';
		}else{
		$this -> dbObj -> Execute("ROLLBACK");
		echo '发生错误，提交失败，数据已经回滚。';
		}
		$this -> dbObj -> Execute("END"); 
		return $id;
		}
	function updatecard($card_id=0,$card_no=0,$marketingcard_id=1,$customerid=0,$employeeid=0,$agencyid=1,$status=0,$activedate,$buydate,$cardtable='itemcard',$overdate){
		$this -> dbObj -> Execute("START TRANSACTION");//事务开始。
		 
		$res1= &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'marketingcard   where marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid);
		$rs1= &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.$cardtable.'   where `'.$cardtable.'_id`='.$card_id.' and agencyid='.$_SESSION["currentorgan"].' and agencyid='.$agencyid);
		if($rs1['marketingcard_id']==$marketingcard_id){//如果没有更改卡项类别
		
		if($res1['assignservice']==1){
			
		$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail    where  servicecategory_id ="" and  marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid);
		}else if($res1['assignservice']==2){
		$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail    where  services_id="" and marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid);	
		}
		
		$itemlist ='';
		$remaintimeslist ='';
		if($res1['assignservice']<>0){
		while ($inrrs = $inrs -> FetchRow()) {
		 
		if($res1['assignservice']==1){
		$itemlist=$itemlist==''?$inrrs['services_id']:$itemlist."||".$inrrs['services_id'] ;
		}else if($res1['assignservice']==2){
		$itemlist=$itemlist==''?$inrrs['servicecategory_id']:$itemlist."||".$inrrs['servicecategory_id'] ;
		}
		
		$remaintimeslist=$remaintimeslist==''?$inrrs['services_times']:$remaintimeslist."||".$inrrs['services_times'] ;	
		
		}
		$inrs -> Close();	
		}
		if($overdate==''){
		$res=$this->dbObj->Execute('UPDATE  '.WEB_ADMIN_TABPOX.$cardtable.' SET`'.$cardtable.'_no`="'.$card_no.'",`customer_id`="'.$customerid.'",`employee_id`="'.$employeeid.'",`assignservice`="'.$res1['assignservice'].'", `price`="'.$res1['price'].'",`value`="'.$res1['value'].'",`timelimit`="'.$res1['timelimit'].'", `buydate`="'.$buydate.'", `status`="'.$status.'", `memo`="'.$_POST["memo"].'" WHERE `'.$cardtable.'_id`='.$card_id.' and agencyid='.$_SESSION["currentorgan"]);	
		}else{
		$res=$this->dbObj->Execute('UPDATE  '.WEB_ADMIN_TABPOX.$cardtable.' SET`'.$cardtable.'_no`="'.$card_no.'",`customer_id`="'.$customerid.'",`employee_id`="'.$employeeid.'",`assignservice`="'.$res1['assignservice'].'", `price`="'.$res1['price'].'",`value`="'.$res1['value'].'",`timelimit`="'.$res1['timelimit'].'", `buydate`="'.$buydate.'", `overdate`="'.$overdate.'",  `status`="'.$status.'", `memo`="'.$_POST["memo"].'" WHERE `'.$cardtable.'_id`='.$card_id.' and agencyid='.$_SESSION["currentorgan"]);	
		}

		$id = $this -> dbObj -> Insert_ID();
		}else{
		
		if($res1['assignservice']==1){
			
		$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail    where  servicecategory_id ="" and  marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid);
		}else if($res1['assignservice']==2){
		$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail    where  services_id="" and marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid);	
		//echo 'select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail    where  services_id="" and marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid;
		}
		//echo 'select * from '.WEB_ADMIN_TABPOX.'marketingcard   where marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid;
		//echo 'select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail    where marketingcard_id  ='.$marketingcard_id.' and agencyid='.$agencyid;
		$itemlist ='';
		$remaintimeslist ='';
		if($res1['assignservice']<>0){
		while ($inrrs = $inrs -> FetchRow()) {
		 
		if($res1['assignservice']==1){
		$itemlist=$itemlist==''?$inrrs['services_id']:$itemlist."||".$inrrs['services_id'] ;
		}else if($res1['assignservice']==2){
		$itemlist=$itemlist==''?$inrrs['servicecategory_id']:$itemlist."||".$inrrs['servicecategory_id'] ;
		}
		
		$remaintimeslist=$remaintimeslist==''?$inrrs['services_times']:$remaintimeslist."||".$inrrs['services_times'] ;	
		
		}
		$inrs -> Close();	
		}
		if($overdate==''){
		$res=$this->dbObj->Execute('UPDATE  '.WEB_ADMIN_TABPOX.$cardtable.' SET`'.$cardtable.'_no`="'.$card_no.'",`marketingcard_id`="'.$marketingcard_id.'",`customer_id`="'.$customerid.'",`employee_id`="'.$employeeid.'",`assignservice`="'.$res1['assignservice'].'", `price`="'.$res1['price'].'",`value`="'.$res1['value'].'",`timelimit`="'.$res1['timelimit'].'",`totaltimes`="'.$res1['totaltimes'].'",`remaintimes`="'.$res1['totaltimes'].'",itemlist="'.$itemlist.'",remaintimeslist="'.$remaintimeslist.'",`buydate`="'.$buydate.'", `status`="'.$status.'", `memo`="'.$_POST["memo"].'" WHERE `'.$cardtable.'_id`='.$card_id.' and agencyid='.$_SESSION["currentorgan"]);	
		}else{
		$res=$this->dbObj->Execute('UPDATE  '.WEB_ADMIN_TABPOX.$cardtable.' SET`'.$cardtable.'_no`="'.$card_no.'",`marketingcard_id`="'.$marketingcard_id.'",`customer_id`="'.$customerid.'",`employee_id`="'.$employeeid.'",`assignservice`="'.$res1['assignservice'].'", `price`="'.$res1['price'].'",`value`="'.$res1['value'].'",`timelimit`="'.$res1['timelimit'].'",`totaltimes`="'.$res1['totaltimes'].'",`remaintimes`="'.$res1['totaltimes'].'",itemlist="'.$itemlist.'",remaintimeslist="'.$remaintimeslist.'",`buydate`="'.$buydate.'", `status`="'.$status.'", `memo`="'.$_POST["memo"].'" WHERE `'.$cardtable.'_id`='.$card_id.' and overdate="'.$overdate.'" and   agencyid='.$_SESSION["currentorgan"]);
		}
		//echo 'UPDATE  '.WEB_ADMIN_TABPOX.$cardtable.' SET`'.$cardtable.'_no`="'.$card_no.'",`marketingcard_id`="'.$marketingcard_id.'",`customer_id`="'.$customerid.'",`employee_id`="'.$employeeid.'",`assignservice`="'.$res1['assignservice'].'", `price`="'.$res1['price'].'",`value`="'.$res1['value'].'",`timelimit`="'.$res1['timelimit'].'",`totaltimes`="'.$res1['totaltimes'].'",`remaintimes`="'.$res1['totaltimes'].'",`buydate`="'.$buydate.'", `status`="'.$status.'", `memo`="'.$_POST["memo"].'" WHERE `'.$cardtable.'_id`='.$card_id.' and agencyid='.$_SESSION["currentorgan"];

		$id = $this -> dbObj -> Insert_ID();
		
	//`assignservice`="'.$res1['assignservice'].'", `price`="'.$res1['price'].'", `timelimit`="'.$res1['timelimit'].'", `totaltimes`="'.$res1['totaltimes'].'",
		}
		if($res){
		
		$this -> dbObj -> Execute("COMMIT");
		echo '提交成功。';
		}else{
		$this -> dbObj -> Execute("ROLLBACK");
		echo '发生错误，提交失败，数据已经回滚。';
		}
		$this -> dbObj -> Execute("END"); 
		return $id;
		}		
}

?>
  