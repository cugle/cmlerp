<?
/**
 * @package System
 */
//require(WEB_ADMIN_HTTPPATH.'/admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/stock.cls.php');
require_once(WEB_ADMIN_CLASS_PATH.'/custom/yeji.cls.php');
require_once(WEB_ADMIN_CLASS_PATH.'/custom/prodaybooks.cls.php');
class charge extends admin {
var $stockObj = null;
	function main(){
		
		$this->stock();
		$this->card();
		$this->commission();
		$this->czk();
		$this->xjq();
		$this->zengsong();
		$this->dingjin();
		//$produce_id=explode(";",$produce_id);
		//$addnumber=explode(";",$addnumber);	
		//$addacount=explode(";",$addacount);	
		
	}
	
	
 function othermoney($item_type=5,$item_id,$value,$customercardid=0,$agencyid){//其他款项 预收款,储值卡充值,定金,还款,赠送手工
	 $tablenamelist=array('customer',"storedvaluedcard","membercard","membercard","membercard");	
	 if($item_id==0){
 
	 $this->dbObj->Execute('UPDATE '.WEB_ADMIN_TABPOX.$tablenamelist[$item_id].'   SET  yufukuan =yufukuan + '.+$value.'  WHERE   agencyid='.$agencyid.' and  customer_id ='.$customercardid);	 
	 } else if($item_id==1){
	 $this->dbObj->Execute('UPDATE '.WEB_ADMIN_TABPOX.$tablenamelist[$item_id].'   SET  value =value  '.+$value.'  WHERE   agencyid='.$agencyid.' and  storedvaluedcard_id ='.$customercardid);	
	// echo 'UPDATE '.WEB_ADMIN_TABPOX.$tablenamelist[$item_id].'   SET  value =value  '.+$value.'  WHERE   agencyid='.$agencyid.' and  storedvaluedcard_id ='.$customercardid;
	 } else if($item_id==2){
	 $this->dbObj->Execute('UPDATE '.WEB_ADMIN_TABPOX.$tablenamelist[$item_id].'   SET  deposit =deposit '.+$value.'  WHERE   agencyid='.$agencyid.' and  membercard_id ='.$customercardid);	
	 } else if($item_id==3){
	 $this->dbObj->Execute('UPDATE '.WEB_ADMIN_TABPOX.$tablenamelist[$item_id].'   SET  salesowe =salesowe -'.($value).'  WHERE   agencyid='.$agencyid.' and  membercard_id ='.$customercardid);	
	 //echo 'UPDATE '.WEB_ADMIN_TABPOX.$tablenamelist[$item_id].'   SET  salesowe =salesowe -'.($value).' WHERE   agencyid='.$agencyid.' and  membercard_id ='.$customercardid;
	 }else if($item_id==4){
	 $this->dbObj->Execute('UPDATE '.WEB_ADMIN_TABPOX.$tablenamelist[$item_id].'   SET  freecost	 =freecost	  '.+$value.'  WHERE   agencyid='.$agencyid.' and  membercard_id ='.$customercardid);	
	 }
	 }			
	
	
	function recoil($sellid,$agencyid){
		//定义模板
		$this->yejiObj=new yeji();
		$res=$this->yejiObj->recoilyeji($sellid);
		 $this->prodaybooksObj=new prodaybooks();//商品流水账数量。


		$agencyid=$agencyid<>''?$agencyid:$_SESSION["currentorgan"];
 		$selldata=$this -> dbObj ->GetRow("SELECT * FROM  ".WEB_ADMIN_TABPOX."sell  where agencyid =".$agencyid." and sell_id=".$sellid);
		 
		$this -> dbObj -> Execute("UPDATE ".WEB_ADMIN_TABPOX."sell  SET status=3 WHERE  agencyid =".$agencyid." AND sell_id='".$sellid."'");//被红字冲销
		$this -> dbObj -> Execute( "INSERT INTO  ".WEB_ADMIN_TABPOX."sell  (`sell_no`, `customer_id`, membercard_id,`membercard_no`, `customer_name`,  `employee_id`,  `payable1`, `realpay`, `howtopay`, `xianjinvalue`, `yinkavalue`, `zengsongvalue`, `dingjinvalue`, `chuzhikavalue`, `xianjinquanvalue`, `yufuvalue`, `status`, sellown,`agencyid`,memcardremain,yufuremain,dingjinremain,chuzhiremain,itemcardremain,ownremain,ownstatus,sellownremain,owntype) VALUE ('".$selldata['sell_no']."','".$selldata['customer_id']."','".$selldata['membercard_id']."','".$selldata['membercard_no']."','".$selldata['sell_time']."','".$selldata['employee_id']."','".-$selldata['payable1']."','".-$selldata['realpay']."','".$selldata['howtopay']."','".-$selldata['xianjinvalue']."','".-$selldata['yinkavalue']."','".-$selldata['zengsongvalue']."','".-$selldata['dingjinvalue']."','".-$selldata['chuzhikavalue']."','".-$selldata['xianjinquanvalue']."','-".$selldata['yufuvalue']."',2,'".$selldata['sellown']."','".$selldata['agencyid']."','".$selldata['memcardremain']."','".$selldata['yufuremain']."','".$selldata['dingjinremain']."','".$selldata['chuzhiremain']."','".$selldata['itemcardremain']."','".$selldata['ownremain']."','".$selldata['ownstatus']."','".$selldata['sellownremain']."','".$selldata['owntype']."') ");//插入冲销单
		//echo "INSERT INTO  ".WEB_ADMIN_TABPOX."sell  (`sell_no`, `customer_id`, `membercard_no`, `customer_name`,  `employee_id`,  `payable1`, `realpay`, `howtopay`, `xianjinvalue`, `yinkavalue`, `zengsongvalue`, `dingjinvalue`, `chuzhikavalue`, `xsellownremainianjinquanvalue`, `yufuvalue`, `status`,  `agencyid`) VALUE ('".$selldata['sell_no']."','".$selldata['customer_id']."','".$selldata['membercard_no']."','".$selldata['sell_time']."','".$selldata['employee_id']."','".$selldata['payable1']."','".$selldata['realpay']."','".$selldata['howtopay']."','".$selldata['xianjinvalue']."','".$selldata['yinkavalue']."','".$selldata['zengsongvalue']."','".$selldata['dingjinvalue']."','".$selldata['chuzhikavalue']."','".$selldata['xianjinquanvalue']."','".$selldata['yufuvalue']."',2,'".$selldata['agencyid']."') ";
	    $recoilsellid = $this -> dbObj -> Insert_ID();
	 	$recoildata=$this -> dbObj ->GetRow("SELECT * FROM  ".WEB_ADMIN_TABPOX."sell  where agencyid =".$agencyid." and sell_id=".$recoilsellid);
		 
		$howtopay=explode(";", $recoildata['howtopay']);//分割付款方式
		
		for($i=0;$i<count($howtopay);$i++){
			$howtopay[$i]=explode(",", $howtopay[$i]);
		}
		$howtopayarray=$howtopay;
		 //echo $recoildata['xianjinvalue'];
		$xianjinvalue= $recoildata['xianjinvalue'];
		$yinkavalue= $recoildata['yinkavalue'];
		$zengsongvalue= $recoildata['zengsongvalue'];
		$dingjinvalue= $recoildata['dingjinvalue'];
		$chuzhikavalue= $recoildata['chuzhikavalue'];
		$xianjinquanvalue= $recoildata['xianjinquanvalue'];
		$yufukuanvalue= $recoildata['yufuvalue'];
 
		$payable1=$recoildata['payable1'];
		$realpay=$recoildata['realpay'];
		$membercard_id=$recoildata['membercard_id'];
		//读取明细
		 $sql1="select * from ".WEB_ADMIN_TABPOX."sellservicesdetail where agencyid =".$agencyid." and sell_id=".$sellid;
		 $sql2="select * from ".WEB_ADMIN_TABPOX."selldetail where agencyid =".$agencyid." and sell_id=".$sellid;
		 $sql3="select * from ".WEB_ADMIN_TABPOX."sellcarddetail where agencyid =".$agencyid." and sell_id=".$sellid;
		 $sql4= "select * from ".WEB_ADMIN_TABPOX."sellconsumedetail where agencyid =".$agencyid." and sell_id=".$sellid;
		 $sql5="select * from ".WEB_ADMIN_TABPOX."sellotherdetail where agencyid =".$agencyid." and sell_id=".$sellid;
		 $sql=$sql1." union ".$sql2." union ".$sql3." union ".$sql4." union ".$sql5;
		 $selldetaildata=$this -> dbObj ->Execute($sql);
		 
 		//echo "update  ".WEB_ADMIN_TABPOX."sell set status=3  where agencyid =".$agencyid." and  sell_id=".$sellid;
		//$this -> dbObj ->Execute("UPDATE ".WEB_ADMIN_TABPOX."sell SET status=2 where agencyid =".$agencyid." and sell_id=".$recoilsellid);
 		//插入明细
		$detailtablelist=array('sellservicesdetail','selldetail','sellconsumedetail','sellcarddetail','sellconsumedetail','sellotherdetail');
		while ($inrrs = $selldetaildata -> FetchRow()) {
			
		$this->dbObj->Execute('INSERT INTO  '.WEB_ADMIN_TABPOX.$detailtablelist[$inrrs["item_type"]].' (`sell_id`,`item_type`,`item_id`,`value`,`price`,`number`,`beauty_id`,`agencyid`,cardtype,cardid,amount,customercardid,employee_id,discount) VALUE("'.$recoilsellid.'","'.$inrrs['item_type'].'","'.$inrrs['item_id'].'","'.$inrrs['value'].'","'.$inrrs['price'].'","'.-$inrrs['number'].'" ,"'.$inrrs['beauty_id'].'",'.$_SESSION["currentorgan"].','.$inrrs['cardtype'].','.$inrrs['cardid'].','.-$inrrs['amount'].','.$inrrs['customercardid'].',"'.$inrrs['employee_id'].'","'.$inrrs['discount'].'")');	
 
		}
		//读取刚插入冲销明细项目
		 $sql1="select * from ".WEB_ADMIN_TABPOX."sellservicesdetail where agencyid =".$agencyid." and sell_id=".$recoilsellid;
		 $sql2="select * from ".WEB_ADMIN_TABPOX."selldetail where agencyid =".$agencyid." and sell_id=".$recoilsellid;
		 $sql3="select * from ".WEB_ADMIN_TABPOX."sellcarddetail where agencyid =".$agencyid." and sell_id=".$recoilsellid;
		 $sql4= "select * from ".WEB_ADMIN_TABPOX."sellconsumedetail where agencyid =".$agencyid." and sell_id=".$recoilsellid;
		 $sql5="select * from ".WEB_ADMIN_TABPOX."sellotherdetail where agencyid =".$agencyid." and sell_id=".$recoilsellid;
		 $sql=$sql1." union ".$sql2." union ".$sql3." union ".$sql4." union ".$sql5;
		 $itemdata=$this -> dbObj ->Execute($sql);
		$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");
		$sellid=$recoilsellid;
		while ($inrrs = $itemdata -> FetchRow()) {
		  
		if($inrrs['item_type']==2 || $inrrs['item_type']==4){//消费划扣卡项
		//$this -> dbObj -> Execute("START TRANSACTION");//事务开始。
		
		$servicesid=$inrrs['item_id'];
		$beautyid=$inrrs['beauty_id'];
		$number=$inrrs['number'];
		$discount=$inrrs['discount'];
		if($discount<>0){
			$discount=1;
			}
	 	 
		$this->manualcommission($servicesid,$number,$beautyid,$sellid,$agencyid,$discount);//手工费冲销？
		 
		$res=$this->card($inrrs['customercardid'],$inrrs['item_id'],$inrrs['number'],$sellid,$agencyid,$cardtable_name[$inrrs['cardtype']],$inrrs['cardid']);//冲销划卡次数
		
		 
		//if($res){
		//$this -> dbObj -> Execute("COMMIT");
		//echo '';
		//}else{
		//$this -> dbObj -> Execute("ROLLBACK");
		//echo '发生错误，提交失败，数据已经回滚。';
		//}
		//$this -> dbObj -> Execute("END"); 	
		}else if($inrrs['item_type']==0){//如果服务是单项服务->手工操作提成。
		 
		$servicesid=$inrrs['item_id'];
		$beautyid=$inrrs['beauty_id'];
		$number=$inrrs['number'];
		$this->	manualcommission($servicesid,$number,$beautyid,$sellid,$agencyid);
		}else if($inrrs['item_type']==1){//产品 销售提成
		 
		$itemtypeid=1;
		$itemid=$inrrs['item_id'];
		$employeeid=$inrrs['beauty_id'];
		$number=$inrrs['number'];
		$this->	sellcommission($itemtypeid,$itemid,$number,$employeeid,$sellid,$agencyid);//销售提成
		
		
		//产品 减少库存
		 
		 if ($inrrs['cardid']==0){
		$warehouseid=$this -> dbObj -> getone('select warehouse_id from '.WEB_ADMIN_TABPOX.'warehouse where type='.$inrrs['cardtype'].' and agencyid ='.$agencyid);//仓库类型
		  
		}else{
		$warehouseid=$inrrs['cardid'];
		}
		$this->	ChargeObj=new charge();
		 
		$this->	ChargeObj->stock($itemid,$number,$warehouseid,$sellid,$agencyid);
		//找出库存单价
				$meanprice=$this -> dbObj -> GetOne("SELECT acount/number FROM  `".WEB_ADMIN_TABPOX."stock`  WHERE warehouse_id=".$warehouseid." and  produce_id=".$inrrs['item_id']." and agencyid=".$_SESSION['currentorgan']);
				$amount=$inrrs['number']*$meanprice;
				$man=$this -> dbObj -> getone('select username from '.WEB_ADMIN_TABPOX.'user where userid = '.$this->getUid());
				
				$rescardforproduct=$this->prodaybooksObj->main($itemid,$warehouseid,-$inrrs['number'],-$amount,1,$sellid,$man,"销售单反冲; ".$inrrs['memo'],$_SESSION["currentorgan"]);//商品流水账
		
		
		 
		}else if($inrrs['item_type']==5){
		 		//划款项
		$item_type=$inrrs['item_type'];
		$itemid=$inrrs['item_id'];
		$customercardid=$inrrs['customercardid'];
		
		 
		$value=-$inrrs['value'];				
		$this-> othermoney($item_type=5,$itemid,$value,$customercardid,$agencyid) ;
		
		}else if($inrrs['item_type']==3){//卡项 销售提成,手工提成
		
		$itemtypeid=2;
		$itemid=$inrrs['item_id'];
		$employeeid=$inrrs['employee_id'];
		$number=$inrrs['number'];
		 
		$this ->sellcommission($itemtypeid,$itemid,$number,$employeeid,$sellid,$agencyid);//卡项销售提成
		$beautyid=$inrrs['beauty_id'];
		 
		$this->beautycommission($itemtypeid,$itemid,$number,$beautyid,$sellid,$agencyid);//卡项手工提成
		 
		//冲销流水，卡库存 
				//卡库存
		//卡项商品流水帐。
				//判断卡是否绑定了商品，找出该卡绑定的商品，减去耗品仓改商品的
				
				$cardforproduct=$this -> dbObj -> GetRow("select * from `".WEB_ADMIN_TABPOX."cardforproduct` where marketingcard_id=".$inrrs['cardid']);
				 
				if($cardforproduct['produce_id']<>''){
				//找出耗品仓
				 $warehouseid=$this -> dbObj -> GetOne("select warehouse_id from `".WEB_ADMIN_TABPOX."warehouse` where type=3 and agencyid=".$_SESSION["currentorgan"]);
				 //echo "select warehouse_id from `".WEB_ADMIN_TABPOX."warehouse` where type=3 and agencyid=".$_SESSION["currentorgan"];
				//找出库存单价
				$meanprice=$this -> dbObj -> GetOne("SELECT acount/number FROM  `".WEB_ADMIN_TABPOX."stock`  WHERE warehouse_id=".$warehouseid." and  produce_id=".$inrrs['item_id']." and agencyid=".$_SESSION['currentorgan']);
				$amount=$inrrs['number']*$meanprice;
				$man=$this -> dbObj -> getone('select username from '.WEB_ADMIN_TABPOX.'user where userid = '.$this->getUid());
				
				$rescardforproduct=$this->prodaybooksObj->main($cardforproduct['produce_id'],$warehouseid,-$inrrs['number'],-$amount,1,$sellid,$man,"开卡反冲; ".$inrrs['memo'],$_SESSION["currentorgan"]);//商品流水账
				
				//减去库存
				$this->	ChargeObj=new charge(); 
				$this->	ChargeObj->stock($cardforproduct['produce_id'],$inrrs['number'],$warehouseid,$sellid,$_SESSION["currentorgan"]);
				}
				//end of 商品卡流水
				
				 
		 
		}
		
		 
		}	
	 	
		//划定金账户
	 if($dingjinvalue<>0){
	 $dingjin=$dingjinvalue;
	 $typeid=1;//服务类定金，暂无意义。
	 $customer_id=$recoildata["customer_id"];
	 $this ->dingjin($customer_id,$typeid,$dingjin,$sellid,$agencyid);
	 }		
 		
		//划赠送账户
	 if($zengsongvalue<>0){
	 $zengsong=$zengsongvalue;
	 $typeid=3;//手工赠送账户
	 $membercardno=$recoildata["membercard_no"];
	 $this ->zengsong($membercardno,$typeid,$zengsong,$sellid,$agencyid);
	 }		
			//划扣预收款	
	 if($yufukuanvalue<>0){
	 $yufukuan=$yufukuanvalue;
	 $typeid=7;//预付账户
	 $customerid=$recoildata["customer_id"];
	 $this->dbObj->Execute('update '.WEB_ADMIN_TABPOX.'customer set yufukuan=yufukuan-'.$yufukuan.'  WHERE  customer_id='.$customerid); 
	
	 }	
 		
	 //扣取储值卡
  		 
	 if($chuzhikavalue<>0){
			
		 //$cardno=$howtopayarray[5][3];
		 $eachczkvalue=explode("||",$howtopayarray[5][2]);
		 $cardno=explode("||",$howtopayarray[5][3]);
		 for ($i=0;$i<count($cardno);$i++){
			
			$cardid=$this->dbObj->GetOne('SELECT storedvaluedcard_id FROM '.WEB_ADMIN_TABPOX.'storedvaluedcard WHERE storedvaluedcard_no="'.$cardno[$i].'"'); 
			
			$this ->czk($cardid,-$eachczkvalue[$i],$sellid,$agencyid);
		 
			
		}
		 
		
	 }
	 //划现金券
	 if($xianjinquanvalue<>0){
		 //$cardno=$howtopayarray[6][3];
		 $cardno=explode("||",$howtopayarray[6][3]);
		 for ($i=0;$i<count($cardno);$i++){
			 
			$cardid=$this->dbObj->GetOne('SELECT cashcoupon_id FROM '.WEB_ADMIN_TABPOX.'cashcoupon WHERE cashcoupon_no="'.$cardno[$i].'"'); 
			//echo 'SELECT cashcoupon_id FROM '.WEB_ADMIN_TABPOX.'cashcoupon WHERE cashcoupon_no="'.$cardno[$i].'"';
			$this ->xjq($cardid,$xianjinquanvalue,$sellid,$agencyid);
		}
	 }	 

 		if($payable1-$realpay<0 && membercard_id<>'0'){
			 
			$this ->own($membercard_id,$payable1-$realpay);
		}
		

		//入账现金或银行帐户
		 
		$this ->acount(1,$xianjinvalue,$sellid,$agencyid);//现金帐号
		 
		$this ->acount(2,$yinkavalue,$sellid,$agencyid);//银行卡帐号
		 
		return 1;
	}	
	function stock($produceid=0,$number=0,$warehouseid,$sellid=0,$agencyid=0){
		//扣减库存。
		$warehouse_id=$warehouseid;
		$this->stockObj=new stock();
		$meanprice=$this->dbObj->GetOne('SELECT acount/number FROM '.WEB_ADMIN_TABPOX.'stock WHERE warehouse_id='.$warehouseid.' and produce_id='.$produceid.' and agencyid="'.$agencyid.'"'); 
		//echo 'SELECT acount/number FROM '.WEB_ADMIN_TABPOX.'stock WHERE warehouse_id='.$warehouseid.'  and produce_id='.$produceid.' and agencyid="'.$agencyid.'"';
		$addacount=$meanprice*$number;
		
		$this->stockObj->main($produceid,-$number,-$addacount,$warehouse_id,$agencyid);
		 //echo 'UPDATE `'.WEB_ADMIN_TABPOX.'stock` SET `number`=number-'.$number.'  WHERE produce_id='.$produceid;
		//$this -> dbObj -> Execute('UPDATE `'.WEB_ADMIN_TABPOX.'stock` SET `number`=number-'.$number.'  WHERE produce_id='.$produceid);
		//$this->dbObj->Execute('UPDATE  '.WEB_ADMIN_TABPOX.'stock SET `number`=number-'.$number.'  WHERE produceid='.$produceid);	
	}
	function own($membercard_id,$addaccount){
	$res=$this->dbObj->Execute('update '.WEB_ADMIN_TABPOX.'membercard set salesowe=salesowe+'.$addaccount.'  WHERE membercard_id='.$membercard_id);
	
	}
	function card($customercardid=0,$itemid,$number=0,$sellid=0,$agencyid=0,$cardtable,$cardid){
		//划扣卡项。
	 	 
		$carddata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.$cardtable.'  WHERE '.$cardtable.'_id='.$customercardid);
		$remaintimes=$carddata['remaintimes']-$number;
		if($carddata['totaltimes']==$carddata['remaintimes']){//如果第一次消费，激活这张卡。修改激活时间和结束时间
			$overdate=date("Y-m-d",strtotime("$m+".$carddata['timelimit']." months",time()));
			$activedate=date('Y-m-d');
			$active=1;
			}
		//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.$cardtable.'  WHERE '.$cardtable.'_id='.$customercardid;
		$itemlist=$carddata['itemlist'];
		$remaintimeslist=$carddata['remaintimeslist'];
				//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.$cardtable.'  WHERE '.$cardtable.'_id='.$cardid;
		//echo $remaintimeslist;
		$itemlist=explode("||",$carddata['itemlist']);
		$remaintimeslist=explode("||",$carddata['remaintimeslist']);
		
		//echo $itemid;
		if($carddata['assignservice']==2){//如果卡项是按类别指定次数，则先查出项目的类别，在比对。
		$itemid =$this->dbObj->GetOne('SELECT categoryid  FROM '.WEB_ADMIN_TABPOX.'services  WHERE services_id='.$itemid);
		//echo 'SELECT categoryid  FROM '.WEB_ADMIN_TABPOX.'services  WHERE services_id='.$itemid;
		}
		
		//如果是以服务指定次数，直接比对。
		$temp='';
		 // $remaintimes =0;
		for ($i=0;$i<count($itemlist);$i++)	{//实现扣减对应项目的次数
		$itemlist[$i]=explode(",",$itemlist[$i]);
		
		for($j=0;$j<count($itemlist[$i]);$j++)	{
			
			//echo $remaintimeslist[$i];
			if($itemlist[$i][$j]==$itemid){
				// echo $remaintimeslist[$i];
				 $time= ($remaintimeslist[$i]-$number);
				 $remaintimeslist[$i]=$time;
				// echo  $remaintimeslist[$i];
				//$remaintimeslist[$i]=$remaintimeslist[$i]-$number;//扣减次数 
				
				}
		}
			//$remaintimes =$remaintimes +$remaintimeslist[$i];
			$temp=$temp==''?$remaintimeslist[$i]:$temp."||".$remaintimeslist[$i];
			
			}
		$remaintimeslist=$temp;	
		if($remaintimes>1){
		$res=$this->dbObj->Execute('UPDATE  '.WEB_ADMIN_TABPOX.$cardtable.'  SET `remaintimeslist`="'.$remaintimeslist.'" ,remaintimes='.$remaintimes.'  WHERE '.$cardtable.'_id='.$customercardid);
		}else if($remaintimes<=0){
		$res=$this->dbObj->Execute('UPDATE  '.WEB_ADMIN_TABPOX.$cardtable.'  SET `remaintimeslist`="'.$remaintimeslist.'" ,remaintimes='.$remaintimes.' ,status=3 WHERE '.$cardtable.'_id='.$customercardid);
		}	
		if($active==1){	
			$res=$this->dbObj->Execute('UPDATE  '.WEB_ADMIN_TABPOX.$cardtable.'  SET `activedate`="'.$activedate.'" ,overdate="'.$overdate.'"  WHERE '.$cardtable.'_id='.$customercardid);
			//echo 'UPDATE  '.WEB_ADMIN_TABPOX.$cardtable.'  SET `activedate`="'.$activedate.'" ,overdate='.$overdate.'  WHERE '.$cardtable.'_id='.$customercardid;
			}
/*		if($remaintimes<=0){
			$res=$this->dbObj->Execute('UPDATE  '.WEB_ADMIN_TABPOX.$cardtable.'  SET `remaintimeslist`="'.$remaintimeslist.'" ,remaintimes='.$remaintimes.' ,status=3 WHERE '.$cardtable.'_id='.$customercardid);
		
		}else{
		$res=$this->dbObj->Execute('UPDATE  '.WEB_ADMIN_TABPOX.$cardtable.'  SET `remaintimeslist`="'.$remaintimeslist.'" ,remaintimes='.$remaintimes.'  WHERE '.$cardtable.'_id='.$customercardid);
		}*/
		
			
		 if($res){
		 return 1;
		}else{
		 return 0;
		}
		 
		
		// echo 'UPDATE  '.WEB_ADMIN_TABPOX.$cardtable.'  SET `remaintimeslist`="'.$remaintimeslist.'"  WHERE '.$cardtable.'_id='.$customercardid;
		
	}
	function manualcommission($servicesid=0,$number,$beautyid=0,$sellid=0,$agencyid=0,$givingbeauty=1){
		//手工提成
		 
		if($givingbeauty==0){
		 $commissionvalue=0;
		}else{
		 $commissionvalue=$this->dbObj->GetOne('SELECT commission FROM '.WEB_ADMIN_TABPOX.'services WHERE services_id='.$servicesid);
		
		}
		$beautyid=explode(";",$beautyid);
		$commissionvalue=$commissionvalue*$number/count($beautyid);
		for ($i=0;$i<count($beautyid);$i++){
		$this->dbObj->Execute('INSERT INTO  '.WEB_ADMIN_TABPOX.'manualcommission (`beauty_id`, `services_id`, `sell_id`, `commissionvalue`, `agencyid`)VALUE ('.$beautyid[$i].','.$servicesid.','.$sellid.','.$commissionvalue.','.$agencyid.')');
       
		}
	}
	
	function beautycommission($itemtypeid,$itemid=0,$number=1,$beautyid=0,$sellid=0,$agencyid=0){
		//销售 卡项 美容师提成
		$beautyid=explode(";",$beautyid);
		$commissionvalue=$this->dbObj->GetOne('SELECT beautycommission  FROM '.WEB_ADMIN_TABPOX.'marketingcard WHERE marketingcard_id='.$itemid); 
		
		$commissionvalue=$commissionvalue*$number/count($beautyid);
		for ($i=0;$i<count($beautyid);$i++){		
		$res=$this->dbObj->Execute('INSERT INTO  '.WEB_ADMIN_TABPOX.'sellcommission (`employeeid`, `itemtypeid`, `itemid`,`sellid`, `commissionvalue` ,`agencyid`)VALUE ('.$beautyid[$i].','.$itemtypeid.','.$itemid.','.$sellid.','.$commissionvalue.','.$agencyid.')');
           //echo 'INSERT INTO  '.WEB_ADMIN_TABPOX.'sellcommission (`employeeid`, `itemtypeid`, `itemid`,`sellid`, `commissionvalue` ,`agencyid`)VALUE ('.$beautyid.','.$itemtypeid.','.$itemid.','.$sellid.','.$commissionvalue.','.$agencyid.')';
		}
	}	
	function sellcommission($itemtypeid,$itemid=0,$number=1,$employeeid=0,$sellid=0,$agencyid=0){
		//销售提成  卡销售提成，产品销售提成，
		$employeeid=explode(";",$employeeid); 
		$itemtypename=array("","produce","marketingcard");
		$commissionvalue=$this->dbObj->GetOne('SELECT sellcommission FROM '.WEB_ADMIN_TABPOX.$itemtypename[$itemtypeid].' WHERE '.$itemtypename[$itemtypeid].'_id='.$itemid); 
		//echo 'SELECT sellcommission FROM '.WEB_ADMIN_TABPOX.$itemtypename[$itemtypeid].' WHERE '.$itemtypename[$itemtypeid].'_id='.$itemid;
		$commissionvalue=$commissionvalue*$number/count($beautyid);
		//$commissionvalue=$commissionvalue?$commissionvalue:0;
		for ($i=0;$i<count($employeeid);$i++){
		$res=$this->dbObj->Execute('INSERT INTO  '.WEB_ADMIN_TABPOX.'sellcommission (`employeeid`, `itemtypeid`, `itemid`,`sellid`, `commissionvalue` ,`agencyid`)VALUE ('.$employeeid[$i].','.$itemtypeid.','.$itemid.','.$sellid.','.$commissionvalue.','.$agencyid.')');
		//echo 'INSERT INTO  '.WEB_ADMIN_TABPOX.'sellcommission (`employeeid`, `itemtypeid`, `itemid`,`sellid`, `commissionvalue` ,`agencyid`)VALUE ('.$employeeid.','.$itemtypeid.','.$itemid.','.$sellid.','.$commissionvalue.','.$agencyid.')';
	 	}
	}
	function czk($cardid=0,$value=0,$sellid=0,$agencyid=0){
		//划扣储值卡
		$res=$this->dbObj->Execute('UPDATE '.WEB_ADMIN_TABPOX.'storedvaluedcard  SET value =value +'.(-$value).', memo=CONCAT(memo,"||","'.$sellid.':'.$value.'") WHERE storedvaluedcard_id='.$cardid);
		
	}
	function xjq($cardid=0,$xianjinquanvalue=0,$sellid=0,$agencyid=0,$status=4){
		//划现金券
		//echo 'UPDATE '.WEB_ADMIN_TABPOX.'cashcoupon  SET status =4, activedate ="'.date("Y-m-d").'"  WHERE cashcoupon_id='.$cardid;
		$res=$this->dbObj->Execute('UPDATE '.WEB_ADMIN_TABPOX.'cashcoupon  SET status ='.$status.', activedate ="'.date("Y-m-d").'"  WHERE cashcoupon_id='.$cardid);		
	}	
	function zengsong($membercardno=0,$typeid=0,$zengsong=0,$sellid=0,$agencyid=0){
		//划赠送
	 $this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."membercard` SET `freecost`=freecost-".$zengsong."  WHERE membercard_no='".$membercardno."'");		
	 
	}
	function dingjin($customer_id=0,$typeid=0,$dingjin,$sellid=0,$agencyid=0){
		//划定金
		
	// $this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."membercard` SET `deposit`=deposit-".$dingjin."  WHERE membercard_no='".$membercardno."'");		
	// echo "UPDATE `".WEB_ADMIN_TABPOX."membercard` SET `freecost`=freecost-".$zengsong."  WHERE membercard_no='".$membercardno."'";	
	 $this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."customer` SET `dingjin`=dingjin-".$dingjin."  WHERE customer_id='".$customer_id."'");		
	}	
	function acount($acounttypeid=0,$value=0,$sellid=0,$agencyid=0){
		//划账户
		 
		if($value!=0){
		//$acountid=$this->dbObj->GetOne('SELECT `account_id` FROM '.WEB_ADMIN_TABPOX.'account WHERE `type`='.$acounttypeid.' and  agencyid='.agencyid);	
		$acountdata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'account WHERE `type`='.$acounttypeid.' and  agencyid='.$agencyid);
		
		$acountid=$acountdata['account_id'];
		$lastbalance=$acountdata['balance'];
		$nowbalance=$acountdata['balance']+$value;
		//echo 'UPDATE '.WEB_ADMIN_TABPOX.'account   SET  balance =balance + '.$value.'  WHERE   agencyid='.$agencyid.' and account_id ='.$acountid;
		$this->dbObj->Execute('UPDATE '.WEB_ADMIN_TABPOX.'account   SET  balance =balance + '.$value.'  WHERE   agencyid='.$agencyid.' and account_id ='.$acountid);	
		 
		$type=1;//销售1
		$memo='销售收入';
		//账户流水账
		$this->dbObj->Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'accounthistory (`account_id`, `value`,  `type`, `sellid`, `lastbalance`, `nowbalance`, `memo`, `agencyid`)value('.$acountid.','.$value.','.$type.','.$sellid.','.$lastbalance.','.$nowbalance.',"'.$memo.'",'.$agencyid.')') ;//帐户流水账
		// echo 'INSERT INTO '.WEB_ADMIN_TABPOX.'accounthistory (`account_id`, `value`,  `type`, `sellid`, `lastbalance`, `nowbalance`, `memo`, `agencyid`)value('.$acountid.','.$value.','.$type.','.$sellid.','.$lastbalance.','.$nowbalance.',"'.$memo.'",'.$agencyid.')';
	}
	}


}

?>
  