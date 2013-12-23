<?
/**
 * @package System
 */
 //商品流水账
//require(WEB_ADMIN_HTTPPATH.'/admin.inc.php');
//require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class cmlprice extends admin {
	function main($sellid){
	 
		$sellid=explode(",",$sellid);
		 
		for ($i=0;$i<count($sellid);$i++){
		$discount[$sellid[$i]]=1;
		$sell=$this->dbObj->GetRow("SELECT * FROM ".WEB_ADMIN_TABPOX."sell  where   sell_id=".$sellid[$i]);
		$account=$inrs=$this->dbObj->GetOne("SELECT sum(amount) as account FROM ".WEB_ADMIN_TABPOX."sellservicesdetail  where   sell_id=".$sellid[$i]." and discount=10 ");	
		if($sell['xianjinquanvalue']>0&&$sell['chuzhikavalue']==0&&$sell['zengsongvalue']==0){//现金券付款
		  
		 if($account>$sell['xianjinquanvalue']){//需要补款的情况
		
		 $discount[$sellid[$i]]=($account-$sell['xianjinquanvalue'])/$account;
		
		 }else{//不需要补款
		 $discount[$sellid[$i]]=0;
		 }
	
		}else if($sell['xianjinquanvalue']==0&&$sell['chuzhikavalue']==0&&$sell['zengsongvalue']>0){//赠送牌价消费金额付款
		  
		 if($account>$sell['zengsongvalue']){//需要补款的情况
		 $discount[$sellid[$i]]=($account-$sell['zengsongvalue'])/$account;
		 }else{//不需要补款
		 $discount[$sellid[$i]]=0;
		 }	
			
		}else if($sell['xianjinquanvalue']==0&&$sell['chuzhikavalue']>0&&$sell['zengsongvalue']==0){//储值卡付款
		 
		 if($account>$sell['chuzhikavalue']){//需要补款的情况
		
		// $discount[$sellid[$i]]=($account-$sell['chuzhikavalue'])/$account;
		$czkdpay=0;//储值卡折扣付款金额
		$howtopay=explode(";",$sell['howtopay']);
		$howtopay[5]=explode(",",$howtopay[5]);
		$howtopay[5][2]=explode("||",$howtopay[5][2]);
		$howtopay[5][3]=explode("||",$howtopay[5][3]);
		for($j=0;$j<count($howtopay[5][2]);$j++){
		$czkno[$j]=$howtopay[5][3][$j];
		
		//查出储值卡折扣
		$discountczk[$j]=1;
		$discountczk[$j]=$this->dbObj->GetOne("SELECT B.price/B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  `storedvaluedcard_no`='".$czkno[$j]."'");
 
		$czkvalue[$j]=$howtopay[5][2][$j];
		$czkdpay=$czkdpay+$discountczk[$j]*$czkvalue[$j];
		
		
		//$lastdiscount=$lastdiscount+($czkvalue[$j]/$sell['zengsongvalue'])*$discount[$j]
		}
		
		$discount[$sellid[$i]]=($czkdpay+$account-$sell['chuzhikavalue'])/$account;

		 
		 
		 }else{//不需要补款
		  
		// $discount[$sellid[$i]]=($account-$sell['chuzhikavalue'])/$account;
		$czkdpay=0;//储值卡折扣付款金额
		$lastdiscount=0;
		 
		$howtopay=explode(";",$sell['howtopay']);
		$howtopay[5]=explode(",",$howtopay[5]);

     
		$howtopay[5][2]=explode("||",$howtopay[5][2]);
		$howtopay[5][3]=explode("||",$howtopay[5][3]);

		for($j=0;$j<count($howtopay[5][2]);$j++){
		$czkno[$j]=$howtopay[5][3][$j];
		
		//查出储值卡折扣
		$discountczk[$j]=1;
		$discountczk[$j]=$this->dbObj->GetOne("SELECT B.price/B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  `storedvaluedcard_no`='".$czkno[$j]."'");
		
		$czkvalue[$j]=$howtopay[5][2][$j];
		$czkdpay=$czkdpay+$discountczk[$j]*$czkvalue[$j];
		
		
		//$lastdiscount=$lastdiscount+($czkvalue[$j]/$sell['zengsongvalue'])*$discount[$j]
		}
		
		$discount[$sellid[$i]]=$czkdpay/$account;
		 
		 
		 
		 }	
			
		}else if($sell['xianjinquanvalue']>0 or $sell['chuzhikavalue']>0 or $sell['zengsongvalue']>0){//储值卡现金券 赠送金额混合付款
		
		 if($account>$sell['chuzhikavalue']+$sell['xianjinquanvalue']+ $sell['zengsongvalue']){//需要补款的情况
		$dpay=0;//储值卡折扣付款金额
		if($sell['chuzhikavalue']>0){
		// $discount[$sellid[$i]]=($account-$sell['chuzhikavalue'])/$account;
		
		$howtopay=explode(";",$sell['howtopay']);
		$howtopay[5]=explode(",",$howtopay[5]);
		$howtopay[5][2]=explode("||",$howtopay[5][2]);
		$howtopay[5][3]=explode("||",$howtopay[5][3]);
		for($j=0;$j<count($howtopay[5][2]);$j++){
		$czkno[$j]=$howtopay[5][3][$j];
		
		//查出储值卡折扣
		$discountczk[$j]=1;
		$discountczk[$j]=$this->dbObj->GetOne("SELECT B.price/B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  `storedvaluedcard_no`='".$czkno[$j]."'");
 
		$czkvalue[$j]=$howtopay[5][2][$j];
		$dpay=$dpay+$discountczk[$j]*$czkvalue[$j];
		}
		if($sell['xianjinquanvalue']>0 or $sell['zengsongvalue']>0){
			
		$dpay=$dpay;	
		}
		 
		//$lastdiscount=$lastdiscount+($czkvalue[$j]/$sell['zengsongvalue'])*$discount[$j]
		}
		
		$discount[$sellid[$i]]=($dpay+$account-$sell['chuzhikavalue']-$sell['xianjinquanvalue']-$sell['zengsongvalue'])/$account;

		 
		 
		 }else{//不需要补款
		  
		// $discount[$sellid[$i]]=($account-$sell['chuzhikavalue'])/$account;
		$czkdpay=0;//储值卡折扣付款金额
		$lastdiscount=0;
		 
		$howtopay=explode(";",$sell['howtopay']);
		$howtopay[5]=explode(",",$howtopay[5]);

     
		$howtopay[5][2]=explode("||",$howtopay[5][2]);
		$howtopay[5][3]=explode("||",$howtopay[5][3]);

		for($j=0;$j<count($howtopay[5][2]);$j++){
		$czkno[$j]=$howtopay[5][3][$j];
		
		//查出储值卡折扣
		$discountczk[$j]=1;
		$discountczk[$j]=$this->dbObj->GetOne("SELECT B.price/B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  `storedvaluedcard_no`='".$czkno[$j]."'");
		
		$czkvalue[$j]=$howtopay[5][2][$j];
		$czkdpay=$czkdpay+$discountczk[$j]*$czkvalue[$j];
		
		
		//$lastdiscount=$lastdiscount+($czkvalue[$j]/$sell['zengsongvalue'])*$discount[$j]
		}
		
		$discount[$sellid[$i]]=$czkdpay/$account;
		 
		 
		 
		 }	
			
		}
		
		
		
		
		
		
		
		}
		return $discount;
	}
	
 //==========================================================================================================
 	function fordaily($bgdate,$enddate){
	 
		$sellid=explode(",",$sellid);
		 
		for ($i=0;$i<count($sellid);$i++){
		$discount[$sellid[$i]]=1;
		$sell=$this->dbObj->GetRow("SELECT * FROM ".WEB_ADMIN_TABPOX."sell  where  creattime between '".$bgdate."' and '".$enddate."'");
		$account=$inrs=$this->dbObj->GetOne("SELECT sum(amount) as account FROM ".WEB_ADMIN_TABPOX."sellservicesdetail  where   sell_id=".$sellid[$i]." and discount=10 ");	
		$account=$account+$this->dbObj->GetOne("SELECT sum(amount) as account FROM ".WEB_ADMIN_TABPOX."sellconsumedetail  where   sell_id=".$sellid[$i]." and discount=10 ");
		if($sell['xianjinquanvalue']>0 or $sell['chuzhikavalue']>0 or $sell['zengsongvalue']>0 or $sell['yufuvalue']>0){//储值卡现金券 赠送金额混合付款
		
		 if($account>$sell['chuzhikavalue']+$sell['xianjinquanvalue']+ $sell['zengsongvalue']+ $sell['yufuvalue']){//需要补款的情况
				
		$discount[$sellid[$i]]=($account-$sell['chuzhikavalue']-$sell['xianjinquanvalue']-$sell['zengsongvalue']- $sell['yufuvalue'])/$account;

		 
		 
		 }else{//不需要补款

		
		$discount[$sellid[$i]]=0;
		 
		 
		 
		 }	
			
		}
		
	
		}
		return $discount;
	}
 function discount($sellid){
	 //查储值卡折扣，E折后价。。1-E折后价/折前价。
		$sellid=explode(",",$sellid);
		 
		for ($i=0;$i<count($sellid);$i++){
		$dpay=0;//储值卡折后付款金额
		$discount[$sellid[$i]]=1;
		$sell=$this->dbObj->GetRow("SELECT * FROM ".WEB_ADMIN_TABPOX."sell  where   sell_id=".$sellid[$i]);
		$account=$this->dbObj->GetOne("SELECT sum(amount) as account FROM ".WEB_ADMIN_TABPOX."sellservicesdetail  where   sell_id=".$sellid[$i]." and discount=10 ");	
		$account=$account+$this->dbObj->GetOne("SELECT sum(amount) as account FROM ".WEB_ADMIN_TABPOX."sellconsumedetail  where   sell_id=".$sellid[$i]." and discount=10 ");	
		if($sell['chuzhikavalue']>0){//存在储值卡付款
		
		$howtopay=explode(";",$sell['howtopay']);
		
		$howtopay[5]=explode(",",$howtopay[5]);
		//$howtopay[3]=explode(",",$howtopay[3]);
    	//$howtopay[6]=explode(",",$howtopay[6]);
		
		$howtopay[5][2]=explode("||",$howtopay[5][2]);
		$howtopay[5][3]=explode("||",$howtopay[5][3]);

		for($j=0;$j<count($howtopay[5][2]);$j++){
		$czkno[$j]=$howtopay[5][3][$j];
		
		//查出储值卡折扣
		$discountczk[$j]=1;
		$discountczk[$j]=$this->dbObj->GetOne("SELECT B.price/B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  `storedvaluedcard_no`='".$czkno[$j]."'");
		
		$czkvalue[$j]=$howtopay[5][2][$j];
		$czkdpay=$czkdpay+(1-$discountczk[$j])*$czkvalue[$j];
		
		
		//$lastdiscount=$lastdiscount+($czkvalue[$j]/$sell['zengsongvalue'])*$discount[$j]
		}			
		
		}
		$discount[$sellid[$i]]=($account-$czkdpay-$sell['xianjinquanvalue'] -$sell['zengsongvalue'])/$account;
		}
		return $discount;
	 
 }
	
function chuzhikadicount($sellid){	
		$sellid=explode(",",$sellid);	 
		for ($i=0;$i<count($sellid);$i++){
		$sell=$this->dbObj->GetRow("SELECT * FROM ".WEB_ADMIN_TABPOX."sell  where   sell_id=".$sellid[$i]);
		if($sell['chuzhikavalue']>0){//存在储值卡付款
		
		$howtopay=explode(";",$sell['howtopay']);
		
		$howtopay[5]=explode(",",$howtopay[5]);
		//$howtopay[3]=explode(",",$howtopay[3]);
    	//$howtopay[6]=explode(",",$howtopay[6]);
		
		$howtopay[5][2]=explode("||",$howtopay[5][2]);
		$howtopay[5][3]=explode("||",$howtopay[5][3]);

		for($j=0;$j<count($howtopay[5][2]);$j++){
		$czkno[$j]=$howtopay[5][3][$j];
		
		//查出储值卡折扣
		$discountczk[$j]=1;
		$discountczk[$j]=$this->dbObj->GetOne("SELECT B.price/B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  `storedvaluedcard_no`='".$czkno[$j]."'");
		
		$czkvalue[$j]=$howtopay[5][2][$j];
		$czkdpay=$czkdpay+$discountczk[$j]*$czkvalue[$j];
		$czkpay=$czkpay+$czkvalue[$j];
		
		//$lastdiscount=$lastdiscount+($czkvalue[$j]/$sell['zengsongvalue'])*$discount[$j]
		}			
		
		}
		$discount[$sellid[$i]]=$czkdpay/$czkpay;
		}		
		return $discount;
		 
	}
}
//$main = new cmlprice();
//$main -> Main();
?>
  