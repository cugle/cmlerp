<?
/**
 * @package System
 */
//require(WEB_ADMIN_HTTPPATH.'/admin.inc.php');
//require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class price extends admin {

	function produceprice($customerid=21,$produceid=711,$agencyid=1){
		//返回价格。
		$price=0;
		 
		$price=$this->dbObj->GetOne('SELECT price from '.WEB_ADMIN_TABPOX.'produce   WHERE  produce_id='.$produceid);
		 
		$discount=$this->dbObj->GetOne('SELECT A.discount from '.WEB_ADMIN_TABPOX.'memcardlevel A  INNER JOIN  '.WEB_ADMIN_TABPOX.'membercard B ON A.cardlevel_id=B.cardlevel_id  WHERE B.customer_id='.$customerid);	
		
		$nodiscount=$this->dbObj->GetOne('SELECT A.nodiscount from '.WEB_ADMIN_TABPOX.'procatalog A  INNER JOIN  '.WEB_ADMIN_TABPOX.'produce B ON A.category_id=B.categoryid  WHERE B.produce_id='.$produceid);	
		//echo 'SELECT A.nodiscount from '.WEB_ADMIN_TABPOX.'procatalog A  INNER JOIN  '.WEB_ADMIN_TABPOX.'produce B ON A.category_id=B.categoryid  WHERE B.produce_id='.$produceid;
		if($nodiscount==1){$discount=100;}
		$discount=$discount/100.00;
		if($discount<>''){
		$price=$price*$discount;
		}
		//查找本日是否存在促销方案->查找方案内是否包含本商品->
		$promotionprice=$this->ispromotion($produceid);//促销价格
		if($promotionprice){$price=$promotionprice;}
		
		return  $price;
		
	}
	
	
	function itemcardprice($customerid=21,$marketingcard_id=711,$agencyid=1){
		//返回价格。
		$price=0;
		 
		$price=$this->dbObj->GetOne('SELECT price from '.WEB_ADMIN_TABPOX.'marketingcard   WHERE  marketingcard_id='.$marketingcard_id);
		 
		$discount=$this->dbObj->GetOne('SELECT A.itemdiscount from '.WEB_ADMIN_TABPOX.'memcardlevel A  INNER JOIN  '.WEB_ADMIN_TABPOX.'membercard B ON A.cardlevel_id=B.cardlevel_id  WHERE B.customer_id='.$customerid);	
		//echo 'SELECT A.itemdiscount from '.WEB_ADMIN_TABPOX.'memcardlevel A  INNER JOIN  '.WEB_ADMIN_TABPOX.'membercard B ON A.cardlevel_id=B.cardlevel_id  WHERE B.customer_id='.$customerid;
		//$nodiscount=$this->dbObj->GetOne('SELECT A.nodiscount from '.WEB_ADMIN_TABPOX.'procatalog A  INNER JOIN  '.WEB_ADMIN_TABPOX.'produce B ON A.category_id=B.categoryid  WHERE B.produce_id='.$produceid);	
		//echo 'SELECT A.nodiscount from '.WEB_ADMIN_TABPOX.'procatalog A  INNER JOIN  '.WEB_ADMIN_TABPOX.'produce B ON A.category_id=B.categoryid  WHERE B.produce_id='.$produceid;
		if(!$discount){$discount=100;}
		$discount=$discount/100.00;
		
		$price=$price*$discount;
		
		//查找本日是否存在促销方案->查找方案内是否包含本商品->
		//$promotionprice=$this->ispromotion($produceid);//促销价格
		//if($promotionprice){$price=$promotionprice;}
		
		return  $price;
		
	}	
	
	
	function existpromotion(){
	$existpromotion=$this->dbObj->Excute('SELECT * from '.WEB_ADMIN_TABPOX.'promotion A  INNER JOIN  '.WEB_ADMIN_TABPOX.'promotiondetail B ON A.promotion_id=B.promotion_id  WHERE  '.date('Y-m-d',time()).'>=A.bgdate and = '.date('Y-m-d',time()).'<=A.enddate  and B.type=0 and A.agencyid='.$_SESSION["currentorgan"]);	
	if(existpromotion){//是否存在促销方案
		return true ;
	}else{
		return false ;
	}
	}
	function ispromotion($produceid){//产品是否在方案内
	$promotion=$this->dbObj->GetRow('SELECT * from '.WEB_ADMIN_TABPOX.'promotion where  bgdate <= "'.date("Y-m-d",time()).'" and enddate  >="'.date("Y-m-d",time()).'" and status=5 and agencyid='.$_SESSION["currentorgan"]);	//是否存在促销方案
	 // echo 'SELECT * from '.WEB_ADMIN_TABPOX.'promotion where  bgdate <= "'.date("Y-m-d",time()).'" and enddate  >="'.date("Y-m-d",time()).'" and agencyid='.$_SESSION["currentorgan"];
	//找出产品类别，
	
	$produce=$this->dbObj->GetRow('SELECT * from '.WEB_ADMIN_TABPOX.'produce  where produce_id ='.$produceid.' and agencyid='.$_SESSION["currentorgan"]);//找出产品类别
	$nodiscount=$this->dbObj->GetOne("SELECT nodiscount FROM `s_procatalog` WHERE category_id=".$produce['categoryid']);//该类别是否不允许打折
	
	if($nodiscount=='1' or $produce['pronodiscount']=='1'){
	$fag=0;
	}else{
	
	if($promotion['assigntype']==1){//指定类别
	 
		 
	$promotiondetail=$this->dbObj->Execute('SELECT * from   '.WEB_ADMIN_TABPOX.'promotiondetail  WHERE promotion_id='.$promotion["promotion_id"]);	
	//如果是指定项目，直接判断。如果是指定类别，找出产品类别再判断。
	
	 	$fag=0;
		while ($inrrs = &$promotiondetail -> FetchRow()) {
			
			if($inrrs['item_id']==$produce['categoryid']){//如果包含该类别
				//返回折扣或价格
				if($inrrs['discounttype']==1){//优惠方式 ：折扣
				
				$price=$produce['price']*$inrrs['discount']/100;
				$fag=1;
				}else if($inrrs['discounttype']==0){//促销价
				$price=$inrrs['proprice'];
				$fag=1;
				 
				}
			}
			}
	}else if($promotion['assigntype']=='0'){//指定项目
	 
	//$produce=$this->dbObj->GetRow('SELECT category_id from '.WEB_ADMIN_TABPOX.'produce  where produce_id ='.$produceid.' and agencyid='.$_SESSION["currentorgan"]);//找出产品类别
	$promotiondetail=$this->dbObj->Excute('SELECT * from   '.WEB_ADMIN_TABPOX.'promotiondetail    WHERE promotion_id='.$promotion["promotion_id"]);	
	//如果是指定项目，直接判断。如果是指定类别，找出产品类别再判断。
	 	$fag=0;
		while ($inrrs = &$promotiondetail -> FetchRow()) {
			if($inrrs['item_id']==$produce['produce_id']){//如果包含该类别
				//返回折扣或价格
				if($inrrs['discounttype']==1){//优惠方式 ：折扣
				$price=$produce['price']*$inrrs['discount']/100;
				$fag=1;
				}else if($inrrs['discounttype']==0){//促销价
				$price=$inrrs['proprice'];
				$fag=1;
				}
			}
			}//endwhile
	}
	}//endif 
	
	if ($fag==1){return $price;}else{return false;}
	}	//endfunction
}

?>
  