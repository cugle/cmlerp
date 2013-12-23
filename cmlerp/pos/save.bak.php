<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/sell.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/card.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/charge.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/cmlprice.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/message.cls.php');

class Pageservices extends admin {
	var $ChargeObj = null;
     function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='charge'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> charge();			
		}else if(isset($_GET['action']) && $_GET['action']=='printticket'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> printticket();	
			}else{
            parent::Main();
        }
    }	
	function disp(){
		//定义模板
 		 
		$t = new Template('../template/pos');
		$t -> set_file('f','save.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		//$t -> set_block('f','mainlist','ml');		
   			//设置分类
		//$t -> set_var('ml');
		$sellid=$_SESSION["sellid"];
		$t -> set_var('sellid',$sellid);
		$sellid?$t -> set_var('disabled',''):$t -> set_var('disabled','disabled');
		$sellid?$t -> set_var('readonly',''):$t -> set_var('readonly','readonly');
		$t -> set_var('membercardno',$_SESSION["membercardno"]);
		$freecost=$this -> dbObj ->GetOne("SELECT freecost from `".WEB_ADMIN_TABPOX."membercard`  WHERE membercard_no =".$_SESSION["membercardno"]);	
		$dingjin=$this -> dbObj ->GetOne("SELECT dingjin from `".WEB_ADMIN_TABPOX."customer`  WHERE customer_id =".$_SESSION["currentcustomerid"]);
		$yufukuan=$this -> dbObj ->GetOne("SELECT yufukuan from `".WEB_ADMIN_TABPOX."customer`  WHERE  customer_id=".$_SESSION["currentcustomerid"]);
		$produceaccount=$this -> dbObj ->GetOne("SELECT IF(ISNULL(sum(amount)),0,sum(amount)) as produceaccount  from `".WEB_ADMIN_TABPOX."selldetail`  WHERE item_type=1 and  sell_id=".$sellid);
		 
		$cardaccount=$this -> dbObj ->GetOne("SELECT IF(ISNULL(sum(amount)),0,sum(amount)) as cardaccount  from `".WEB_ADMIN_TABPOX."sellcarddetail`  WHERE item_type=3 and returnstatus <> 1 and  sell_id=".$sellid);
		$serviceaccount=$this -> dbObj ->GetOne("SELECT IF(ISNULL(sum(amount)),0,sum(amount)) as serviceaccount from `".WEB_ADMIN_TABPOX."sellservicesdetail`  WHERE item_type=0 and  sell_id=".$sellid);
		$serviceaccount=$serviceaccount+$this -> dbObj ->GetOne("SELECT IF(ISNULL(sum(amount)),0,sum(amount)) as serviceaccount   from `".WEB_ADMIN_TABPOX."sellconsumedetail`  WHERE item_type=2 and  sell_id=".$sellid);
		$serviceaccount=$serviceaccount+$this -> dbObj ->GetOne("SELECT IF(ISNULL(sum(amount)),0,sum(amount)) as cardaccount  from `".WEB_ADMIN_TABPOX."sellcarddetail`  WHERE item_type=3 and returnstatus= 1 and  sell_id=".$sellid);
		//echo "SELECT IF(ISNULL(sum(amount)),0,sum(amount)) as cardaccount  from `".WEB_ADMIN_TABPOX."sellcarddetail`  WHERE item_type=3 and returnstatus= 1 and  sell_id=".$sellid;
		$otheraccount=$otheraccount+$this -> dbObj ->GetOne("SELECT IF(ISNULL(sum(amount)),0,sum(amount)) as  otheraccount  from `".WEB_ADMIN_TABPOX."sellotherdetail`  WHERE item_type=5 and  sell_id=".$sellid);
		$chuzhikanostr='';
		 
		if($_SESSION['currentcustomerid']<>0){
		 
		$inrschuzhikano=$this -> dbObj ->Execute("SELECT *  from `".WEB_ADMIN_TABPOX."storedvaluedcard`  WHERE customer_id=".$_SESSION['currentcustomerid']." and  status=1 and value>0 order by value desc");
		//echo "SELECT *  from `".WEB_ADMIN_TABPOX."storedvaluedcard`  WHERE customer_id=".$_SESSION['currentcustomerid']." and  status=1 and value>0 order by value desc";

		while($inrrschuzhikano = $inrschuzhikano -> FetchRow()){
		$marketingcard=$this -> dbObj ->GetRow("SELECT * from `".WEB_ADMIN_TABPOX."marketingcard`  WHERE marketingcard_id=".$inrrschuzhikano['marketingcard_id']);
		$chuzhikanostr =$chuzhikanostr."<option value=".$inrrschuzhikano['storedvaluedcard_no']." >".$marketingcard['marketingcard_name']."  ".$inrrschuzhikano['value']."</option>";	
		}
		}
		
		$t -> set_var('chuzhikanostr',$chuzhikanostr);
		$t -> set_var('produceaccount',$produceaccount);
		$t -> set_var('cardaccount',$cardaccount);
		$t -> set_var('serviceaccount',$serviceaccount);
		$t -> set_var('otheraccount',$otheraccount);
		$freecost=$freecost?$freecost:0;
		$dingjin=$dingjin?$dingjin:0;
		$t -> set_var('yufukuanyue',$yufukuan);
		$t -> set_var('freecostyue',$freecost);
		$t -> set_var('deposityue',$dingjin);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function charge(){
		//定义模板
 
        $howtopay=$_POST["howtopay"];
		
		 
		$qtpayshowvalue=$_POST['qtpayshowvalue'];
		$xianjinpayvalue=$_POST['xianjinpayvalue'];
		$yinkapayvalue=$_POST['yinkapayvalue'];
		$sellid=$_POST["sellid"]?$_POST["sellid"]:$_SESSION["sellid"];
		$payable1=$this->sellcount($sellid);
		$customerid=$_POST["customerid"]?$_POST["customerid"]:$_SESSION["currentcustomerid"];
		$membercardno=$_SESSION["membercardno"];
		$agencyid=$_SESSION["currentorgan"]?$_SESSION["currentorgan"]:1;
		$realpay=$qtpayshowvalue+$xianjinpayvalue+$yinkapayvalue;
		//echo count($howtopay);
		
		$howtopayarray=explode(";",$howtopay);
		$temp='';
		for ($i=0;$i<count($howtopayarray);$i++)	{
		$howtopayarray[$i]=explode(",",$howtopayarray[$i]);
		//if($i<>0){$temp=";".$temp;}
		//$temp=$temp.$howtopayarray[$i][0].",".$howtopayarray[$i][1].",".$howtopayarray[$i][2];
		}
		//$howtopayarray=$temp;
		$xianjinvalue= $howtopayarray[1][2];
		$yinkavalue= $howtopayarray[2][2];
		$zengsongvalue= $howtopayarray[3][2];
		$dingjinvalue= $howtopayarray[4][2];
		$chuzhikavalue==0;
		$eachczkvalue1=explode("||",$howtopayarray[5][2]);	
		for ($i=0;$i<count($eachczkvalue1);$i++){
		$chuzhikavalue=$chuzhikavalue+$eachczkvalue1[$i];
		}
		//$chuzhikavalue= $howtopayarray[5][2];
		$xianjinquanvalue= $howtopayarray[6][2];
		$yufukuanvalue= $howtopayarray[7][2];
		 //echo $sellid;
		// echo $customerid;
		if($payable1-$realpay>0){
		$this -> dbObj -> Execute("UPDATE ".WEB_ADMIN_TABPOX."membercard   SET  salesowe=salesowe+'".($payable1-$realpay)."' WHERE  membercard_no='".$membercardno."'");
		 
		}
		$realpay1=$realpay>$payable1?$payable1:$realpay;
		
		$this -> dbObj -> Execute("UPDATE ".WEB_ADMIN_TABPOX."sell  SET  howtopay='".$howtopay."' ,realpay='".$realpay1."' ,payable1=".$payable1.",xianjinvalue='".$xianjinvalue."',yinkavalue='".$yinkavalue."',zengsongvalue='".$zengsongvalue."',dingjinvalue='".$dingjinvalue."',chuzhikavalue='".$chuzhikavalue."',xianjinquanvalue='".$xianjinquanvalue."',yufuvalue='".$yufukuanvalue."',sell_time='".date('Y-m-d H:i:s',time())."' ,creattime='".date('Y-m-d H:i:s',time())."' ,customer_id =".$_SESSION['currentcustomerid'].",status=1 WHERE sell_id='".$sellid."'");
	  // echo "UPDATE ".WEB_ADMIN_TABPOX."sell  SET  howtopay='".$howtopay."' ,realpay='".$realpay."' ,payable1=".$payable1.",xianjinvalue='".$xianjinvalue."',yinkavalue='".$yinkavalue."',zengsongvalue='".$zengsongvalue."',dingjinvalue='".$dingjinvalue."',chuzhikavalue='".$chuzhikavalue."',xianjinquanvalue='".$xianjinquanvalue."',status=1 WHERE sell_id='".$sellid."'";
		
		$this->	ChargeObj=new charge();
		$sql1="select * from  ".WEB_ADMIN_TABPOX."selldetail  where  sell_id=".$sellid;
		$sql2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail   where  sell_id=".$sellid;
		$sql3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  where  sell_id=".$sellid;
		$sql4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  where  sell_id=".$sellid;
		$sql5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail  where  sell_id=".$sellid;
		$sql=$sql1." union ".$sql2." union ".$sql3." union ".$sql4." union ".$sql5;
		
		$itemdata=$this -> dbObj -> Execute($sql);
		 
		$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");
		
		while ($inrrs = $itemdata -> FetchRow()) {
		
		if($inrrs['item_type']==2 || $inrrs['item_type']==4){//消费划扣卡项
		$this -> dbObj -> Execute("START TRANSACTION");//事务开始。
		$servicesid=$inrrs['item_id'];
		$beautyid=$inrrs['beauty_id'];
		$number=$inrrs['number'];
		$discount=$inrrs['discount'];
		if($discount<>0){
			$discount=1;
			}
		$this->	ChargeObj->manualcommission($servicesid,$number,$beautyid,$sellid,$agencyid,$discount);
		
		$res=$this->	ChargeObj->card($inrrs['customercardid'],$inrrs['item_id'],$inrrs['number'],$sellid,$agencyid,$cardtable_name[$inrrs['cardtype']],$inrrs['cardid']);
		if($res){
		$this -> dbObj -> Execute("COMMIT");
		echo '';
		}else{
		$this -> dbObj -> Execute("ROLLBACK");
		echo '发生错误，提交失败，数据已经回滚。';
		}
		$this -> dbObj -> Execute("END"); 	
		}else if($inrrs['item_type']==0){//如果服务是单项服务->手工操作提成。
		$servicesid=$inrrs['item_id'];
		$beautyid=$inrrs['beauty_id'];
		$number=$inrrs['number'];
		$this->	ChargeObj->manualcommission($servicesid,$number,$beautyid,$sellid,$agencyid);
		}else if($inrrs['item_type']==1){//产品 销售提成
		$itemtypeid=1;
		$itemid=$inrrs['item_id'];
		$employeeid=$inrrs['beauty_id'];
		$number=$inrrs['number'];
		$this->	ChargeObj->sellcommission($itemtypeid,$itemid,$number,$employeeid,$sellid,$agencyid);
		
		
		//产品 减少库存
		 if ($inrrs['cardid']==0){
		$warehouseid=$this -> dbObj -> getone('select warehouse_id from '.WEB_ADMIN_TABPOX.'warehouse where type='.$inrrs['cardtype'].' and agencyid ='.$agencyid);//仓库类型
		 
		}else{
		$warehouseid=$inrrs['cardid'];
		}
		 
		$this->	ChargeObj->stock($itemid,$number,$warehouseid,$sellid,$agencyid);
		}else if($inrrs['item_type']==5){//款项
		if($inrrs['item_id']==3){//还款
		$this -> dbObj -> Execute("UPDATE ".WEB_ADMIN_TABPOX."membercard   SET  salesowe=salesowe-'".$inrrs['amount']."' WHERE  membercard_no='".$membercardno."'");
		}else if($inrrs['item_id']==0){//预收款
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."customer` SET `yufukuan` =yufukuan+ '".$inrrs['amount']."',yufu_memo='".$inrrs['memo']."'  WHERE customer_id =".$_SESSION['currentcustomerid']);		
		}else if($inrrs['item_id']==1){//储值卡充值
		$this -> dbObj -> Execute("UPDATE ".WEB_ADMIN_TABPOX."storedvaluedcard  SET value=value+".$inrrs['amount']." WHERE storedvaluedcard_id ='".$inrrs['customercardid']."'");	
		}else if($inrrs['item_id']==2){//定金
		//$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."membercard` SET `deposit` =deposit+ '".$inrrs['amount']."',depositmemo='".$inrrs['memo']."'  WHERE membercard_id =".$inrrs['customercardid']);	
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."customer` SET `dingjin` =dingjin + '".$inrrs['amount']."',dingjin_memo='".$inrrs['memo']."'  WHERE customer_id =".$_SESSION['currentcustomerid']);			
		
		
		}else if($inrrs['item_id']==4){//赠送
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."membercard` SET `freecost` =freecost+ '".$inrrs['value']*$inrrs['number']."'  WHERE membercard_id =".$inrrs['customercardid']);	
		}
		}else if($inrrs['item_type']==3){//卡项 销售提成,手工提成
		$itemtypeid=2;
		$itemid=$inrrs['item_id'];
		$employeeid=$inrrs['employee_id'];
		$number=$inrrs['number'];
		$this->	ChargeObj->sellcommission($itemtypeid,$itemid,$number,$employeeid,$sellid,$agencyid);
		$beautyid=$inrrs['beauty_id'];
		$this->	ChargeObj->beautycommission($itemtypeid,$itemid,$number,$beautyid,$sellid,$agencyid);
		
		if($inrrs['returnstatus']==1){//如果是换卡。
		
		$Prefixname=array('XM','XM','LC','TY','GS','HJ','XJ','CZ','ZDY');
		
		$agency_no=$_SESSION["agency_no"];
		$number=5;
			
		$Prefix=$Prefixname[$inrrs['cardtype']];
		$cardtable=$cardtable_name[$inrrs['cardtype']];
		$column=$cardtable.'_no';
		$id=$cardtable.'_id';
		$table=WEB_ADMIN_TABPOX.$cardtable;
		$itemcard_no=$this->makeno($Prefix,$agency_no,$table,$column,$number,$id);	
		$card_no=$itemcard_no;
		$marketingcard_id=$inrrs['cardid'];
		
		$employeeid=$inrrs['employee_id'];
		$cardtable=$cardtable_name[$inrrs['cardtype']];
		$this->	CardObj=new card();
		$newcardid = $this-> CardObj->creatcard($card_no,$marketingcard_id,$customerid,$employeeid,$agencyid,$cardtable);//生产新卡
		
		$this-> dbObj-> Execute('update  '.WEB_ADMIN_TABPOX.'sellcarddetail set customercardid ='.$newcardid.' WHERE  selldetail_id='.$inrrs["selldetail_id"]); //插入卡项id到明细中
		
		$oldcardselldetail=$this->dbObj->GetRow('SELECT *  FROM '.WEB_ADMIN_TABPOX.'sellcarddetail WHERE   selldetail_id='.$inrrs["returnid"]);
		$cardtable2=$cardtable_name[$oldcardselldetail['cardtype']];//旧卡
		
		$this-> dbObj-> Execute('update  '.WEB_ADMIN_TABPOX.$cardtable.' set status =1 WHERE  '.$cardtable.'_id='.$newcardid); 	//修改新卡状态
		$this-> dbObj-> Execute('update  '.WEB_ADMIN_TABPOX.$cardtable2.' set status =3 WHERE  '.$cardtable2.'_id='.$oldcardselldetail["customercardid"]); 	//修改旧卡状态
		
		}
		
		}
		
		
		}	
		
		//划定金账户
	 if($dingjinvalue>0){
	 $dingjin=$dingjinvalue;
	 $typeid=1;//服务类定金，暂无意义。
	 $customer_id=$_SESSION['currentcustomerid'];
	 $this -> ChargeObj->dingjin($customer_id,$typeid,$dingjin,$sellid,$agencyid);
	 }		
		
		//划赠送账户
	 if($zengsongvalue>0){
	 $zengsong=$zengsongvalue;
	 $typeid=3;//手工赠送账户
	 $membercardno=$_SESSION["membercardno"];
	 $this -> ChargeObj->zengsong($membercardno,$typeid,$zengsong,$sellid,$agencyid);
	 }		
			//划扣预收款	
	 if($yufukuanvalue>0){
	 $yufukuan=$yufukuanvalue;
	 $typeid=7;//预付账户
	 $customerid=$_SESSION["membercardno"];
	 $this->dbObj->Execute('update '.WEB_ADMIN_TABPOX.'customer set yufukuan=yufukuan-'.$yufukuan.'  WHERE  customer_id='.$_SESSION["currentcustomerid"]); 
	 }	
		
	 //扣取储值卡
	 if($chuzhikavalue>0){
		 //$cardno=$howtopayarray[5][3];
		 $eachczkvalue=explode("||",$howtopayarray[5][2]);
		 $cardno=explode("||",$howtopayarray[5][3]);
		 for ($i=0;$i<count($cardno);$i++){
				 
			$cardid=$this->dbObj->GetOne('SELECT storedvaluedcard_id FROM '.WEB_ADMIN_TABPOX.'storedvaluedcard WHERE storedvaluedcard_no="'.$cardno[$i].'"'); 
			//echo 'SELECT storedvaluedcard_id FROM '.WEB_ADMIN_TABPOX.'storedvaluedcard WHERE storedvaluedcard_no='.$cardno[$i];
			$this -> ChargeObj->czk($cardid,$eachczkvalue[$i],$sellid,$agencyid);
		 
			
		}
	 }
	 //划现金券
	 if($xianjinquanvalue>0){
		 //$cardno=$howtopayarray[6][3];
		 $cardno=explode("||",$howtopayarray[6][3]);
		 for ($i=0;$i<count($cardno);$i++){
			 
			$cardid=$this->dbObj->GetOne('SELECT cashcoupon_id FROM '.WEB_ADMIN_TABPOX.'cashcoupon WHERE cashcoupon_no="'.$cardno[$i].'"'); 
			//echo 'SELECT cashcoupon_id FROM '.WEB_ADMIN_TABPOX.'cashcoupon WHERE cashcoupon_no="'.$cardno[$i].'"';
			$this -> ChargeObj->xjq($cardid,$xianjinquanvalue,$sellid,$agencyid);
		}
	 }	 

		//入账现金或银行帐户
		$this->	ChargeObj->acount(1,$xianjinvalue,$sellid,$agencyid);//现金帐号
		$this->	ChargeObj->acount(2,$yinkavalue,$sellid,$agencyid);//银行卡帐号
		if($customerid<>0){
			
		$this->cardremain($sellid,$customerid);
		}
		
		$this->cmlpriceObj=new cmlprice();	
		$cmldiscount=$this->cmlpriceObj->fordaily($sellid);//打折处理储值卡 现金券，赠送消费金额，预付	
		$inrrsbeautytoday['value']*$cmldiscount[$inrrsbeautytoday['sell_id']];
		$this -> dbObj -> Execute("update  ".WEB_ADMIN_TABPOX."sellservicesdetail  set cmlaccount= amount*".$cmldiscount[$sellid]."  WHERE sell_id =".$sellid); 
		$this -> dbObj -> Execute("update  ".WEB_ADMIN_TABPOX."sellconsumedetail  set cmlaccount= amount*".$cmldiscount[$sellid]."  WHERE sell_id =".$sellid); 
		$this -> dbObj -> Execute("update  ".WEB_ADMIN_TABPOX."sellcarddetail  set cmlaccount= amount  WHERE sell_id =".$sellid); 
		$this -> dbObj -> Execute("update  ".WEB_ADMIN_TABPOX."selldetail  set cmlaccount= amount  WHERE sell_id =".$sellid); 
		$this -> dbObj -> Execute("update  ".WEB_ADMIN_TABPOX."sellotherdetail  set cmlaccount= amount WHERE sell_id =".$sellid); 
		$this -> dbObj -> Execute("update  ".WEB_ADMIN_TABPOX."sellreturndetail  set cmlaccount= amount  WHERE sell_id =".$sellid); 
		
		$repay=$this -> dbObj -> GetOne("select sum(amount) as account from  ".WEB_ADMIN_TABPOX."sellotherdetail where item_type=5 and item_id=3 and sell_id =".$sellid); 
		$sellown=$payable1-$realpay-$repay;
		$this -> dbObj -> Execute("update  ".WEB_ADMIN_TABPOX."sell  set sellown= ".$sellown."  WHERE sell_id =".$sellid); //写入欠款
		
		$_SESSION["sellid"]=0;	
/*		//发送消费短信
		$tel=$this -> dbObj -> GetOne("select handphone from  ".WEB_ADMIN_TABPOX."customer where customer_id=".$customerid); 
		$sellmsg=$this -> dbObj -> GetOne("select * from  ".WEB_ADMIN_TABPOX."sell where sell_id=".$sellid); 
		$msg= "你本次共消费 ".$sellmsg['payable1']."元，总收".$sellmsg['realpay']."元，其中 现金：".$sellmsg['xianjinvalue']." 刷卡：". $sellmsg['yinkavalue']."元，感谢光临香蔓" ;	
  		if($tel<>''){
		$this->	MessageObj=new message();
		$booktime=date('Y-m-d',time());
		$this->	MessageObj->sendmessage($tel,$msg,$agencyid,$booktime);
		}*/
		if($_POST['printticket']==1){
		// $this->printticket($sellid);
		 exit("<script>window.returnValue='1@@@".$sellid."';window.parent.close();</script>");
		//$this->printticket($sellid); 

		//$this->printticket();
		/*exit("<script>alert('付款成功');window.returnValue='1';window.close();</script>");*/
		}else if($_POST['printticket']==0) {
		exit("<script>window.parent.kkit('0');</script>");
		}else{
		exit("<script>window.parent.kkit('-1');</script>");
		}
		
		//数据流：扣赠送账户，扣储值卡，划现金券，往来帐，减库存，手工提成，销售提成。
	}
function printticket($sellid){
		//定义模板
 		 
		$t = new Template('../template/pos');
		$t -> set_file('f','printticket.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
   			//设置分类
		$sellid=$_GET['sellid']?$_GET['sellid']:$sellid;
		$selldata=$this -> dbObj ->GetRow("select * from  ".WEB_ADMIN_TABPOX."sell  where  sell_id=".$sellid);
		
		$t->set_var($selldata);
		$employee_name=$this -> dbObj ->GetOne("select employee_name from  ".WEB_ADMIN_TABPOX."employee  where  employee_id=".$selldata['employee_id']);
		 
		$customer_name=$this -> dbObj ->GetOne("select customer_name from  ".WEB_ADMIN_TABPOX."customer  where  customer_id=".$selldata['customer_id']);
		$t->set_var('employee_name',$employee_name);
		$t->set_var('customer_name',$customer_name);
		$t -> set_var('ml');
  
 		$sql1="select * from  ".WEB_ADMIN_TABPOX."selldetail  where  sell_id=".$sellid;
		$sql2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail   where  sell_id=".$sellid;
		$sql3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  where  sell_id=".$sellid;
		$sql4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  where  sell_id=".$sellid;
		$sql5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail  where  sell_id=".$sellid;
		$sql=$sql1." union ".$sql2." union ".$sql3." union ".$sql4." union ".$sql5;
		 $table_name=array('services',"produce","services","marketingcard","services");	
		  $itemtype_name=array('单项服务',"购买产品","消费卡项","购买卡项","消费券项","其他项目");	
		  
		$inrs = $this -> dbObj -> Execute($sql);
		$tempacount=0;
		 
 		while ($inrrs = $inrs -> FetchRow()) {
			$t -> set_var($inrrs);
			  
			 $tempacount=$tempacount+$inrrs['amount'];
			$itemdata=$this -> dbObj -> GetRow("select * from ".WEB_ADMIN_TABPOX.$table_name[$inrrs['item_type']]." where ".$table_name[$inrrs['item_type']]."_id =".$inrrs['item_id']);
			 
			if($inrrs['item_type']==2){//卡项
			$t -> set_var('itemtype_name',$this -> dbObj -> getone('select marketingcard_name from '.WEB_ADMIN_TABPOX.'marketingcard  where marketingcard_id ='.$inrrs["cardid"]));
			 //echo 'select marketingcard_name from '.WEB_ADMIN_TABPOX.'marketingcard  where marketingcard_id ='.$inrrs["cardid"];
				//$this -> dbObj -> GetRow("select * from ".WEB_ADMIN_TABPOX."marketingcard WHERE  marketingcard_id=".$inrrs['cardid']);
				}else if($inrrs['item_type']==1){//产品
				$t -> set_var('itemtype_name',$this -> dbObj -> getone('select A.category_name  from '.WEB_ADMIN_TABPOX.'procatalog  A INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON A.category_id=B.categoryid   where B.produce_id ='.$inrrs["item_id"]));
				
				//echo 'select A.category_name  from '.WEB_ADMIN_TABPOX.'procatalog  A INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON A.category_id=B.categoryid   where B.produce_id ='.$inrrs["item_id"];
				}else if($inrrs['item_type']==3){//消费
				$t -> set_var('itemtype_name',$this -> dbObj -> getone('select A.marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrs["item_id"]));
				//echo 'select A.marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrs["item_id"];
				}else if($inrrs['item_type']==4){//券类消费
				$t -> set_var('itemtype_name',$this -> dbObj -> getone('select A.marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrs["cardid"]));
				//echo 'select A.marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrs["cardid"];
				//echo 'select A.marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrs["item_id"];
				}else if($inrrs['item_type']==0){//服务 
				$t -> set_var('itemtype_name',$this -> dbObj -> getone('select A.category_name  from '.WEB_ADMIN_TABPOX.'servicecategory  A INNER JOIN '.WEB_ADMIN_TABPOX.'services B ON A.category_id =B.categoryid    where B.services_id ='.$inrrs["item_id"]));
				//echo 'select A.servicecategory_name from '.WEB_ADMIN_TABPOX.'servicecategory  A INNER JOIN '.WEB_ADMIN_TABPOX.'services B ON A.category_id =B.categoryid    where B.services_id ='.$inrrs["cardid"];
				}else if($inrrs['item_type']==5){//服务 
				$itemnamelist=array('预收款',"储值卡充值","定金","还款","赠送手工","其他","预付产品款","赠送产品款","赠送购卡款");		
				$cardtypelist=array('款项',"其他");	
				$itemname=$itemnamelist[$inrrs['item_id']];
				$cardtype=$cardtypelist[$inrrs['cardtype']];
				$t -> set_var('itemtype_name',$cardtype);
				}else{
					$t -> set_var('itemtype_name','test1');
			 }
			$t -> set_var('memo','');
			
			$t -> set_var('type_name',$itemtype_name[$inrrs['item_type']]);
			$t -> set_var('itemname',$itemdata[$table_name[$inrrs['item_type']].'_name']);
			$t -> set_var('itemno',$itemdata[$table_name[$inrrs['item_type']].'_no']);
			$t -> set_var('number',$inrrs['number']);
			if($inrrs['item_type']==5){
			$t -> set_var('itemname',$itemname);
			$t -> set_var('itemno',$inrrs['item_type']);
			$t -> set_var('number',$inrrs['number']);
			}
			$t -> parse('ml','mainlist',true);
		}
 
 
			if($selldata['membercard_no']<>""){
			$cardlevel_name = $this -> dbObj -> GetOne("select cardlevel_name from ".WEB_ADMIN_TABPOX."membercard A INNER JOIN ".WEB_ADMIN_TABPOX."memcardlevel B ON  A.cardlevel_id=B.cardlevel_id where A.membercard_no='".$selldata['membercard_no']."'");	
			}else{
				if ($selldata['customer_id']<>""){
					$cardlevel_name="普通顾客";
					}else{
					$cardlevel_name="散客";
					}
			}
			$t -> set_var('cardlevel_name',$cardlevel_name);	
			
		//支付方式
		$paytypeenlist=array('xianjinvalue','yinkavalue','zengsongvalue','dingjinvalue','chuzhikavalue','xianjinquanvalue','yufuvalue','yufuproducevalue','zengsongproducevalue','zengsongcardvalue');
		$paytypelist=array('现金','刷卡','扣赠送账户','扣定金','扣储值卡','扣现金券','扣预收款','扣预收产品款','扣赠送产品款','扣赠送购卡款');
		$paytype='';
		  
		for ($i=0;$i<count($paytypeenlist);$i++) {
 
		if($selldata[$paytypeenlist[$i]]>0){
			
		$paytype=$paytype==''?$paytypelist[$i]."：".$selldata[$paytypeenlist[$i]]:$paytype."；".$paytypelist[$i]."：".$selldata[$paytypeenlist[$i]];
		}
		}
		$t -> set_var('paytype',$paytype);			
 
		$status_name=array("<font color=red>未完成</font>","已完成","<font color=red>红字冲销</font>","<font color=red>被红字冲销</font>");
		$t -> set_var('status_name',$status_name[$selldata['status']]);

 
  		$t -> set_var('totalacount',$tempacount);
		$t -> set_var('membercard_no','');
 		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');	
	
	}
	function cardremain($sellid,$customerid){
		//插入会员卡余额，会籍卡或皇冠卡
		//寻找卡级，寻找卡级对应方案。需找顾客对应卡项->>剩余次数或余额。
		//插入定金剩余，插入预付剩余。
		//插入储值剩余
		$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");
		$remaintype=array('remaintimes',"remaintimes","remaintimes","remaintimes","remaintimes","remaintimes","value","value","remaintimes");
		$memcardremain=0;
		 
		//$cardlevel_id=$this -> dbObj -> GetOne("select cardlevel_id from  ".WEB_ADMIN_TABPOX."membercard  where  customer_id=".$customerid);
		//$memcardlevel=$this -> dbObj -> GetRow("select * from  ".WEB_ADMIN_TABPOX."memcardlevel  where  cardlevel_id=".$cardlevel_id);
		//$marketingcard_id=$memcardlevel['marketingcard_id'];
		//$cardtype_id=$memcardlevel['cardtype_id'];
		//$memcardremain=$this -> dbObj -> GetOne("select ".$remaintype[$cardtype_id]." from  ".WEB_ADMIN_TABPOX.$cardtable_name[$cardtype_id]."  where  marketingcard_id=".$marketingcard_id.' and customer_id='.$customerid);
		$memcardremain=$this -> dbObj -> GetOne("select sum(remaintimes) from  ".WEB_ADMIN_TABPOX."membershipcard  where  status=1  and customer_id=".$customerid);
		//echo "select sum(remaintimes) from  ".WEB_ADMIN_TABPOX."membershipcard  where  status=1  and customer_id=".$customerid;
		$memcardremain=$memcardremain?$memcardremain:"0.00";
		$customer=$this -> dbObj -> GetRow("select * from  ".WEB_ADMIN_TABPOX."customer  where  customer_id=".$customerid);
		
		$yufuremain=$customer['yufukuan'];
		$dingjinremain=$customer['dingjin'];
		$yufuremain=$yufuremain?$yufuremain:"0.00";
		$dingjinremain=$dingjinremain?$dingjinremain:"0.00";
		
		$chuzhiremain=$this -> dbObj -> GetOne("select sum(value) from  ".WEB_ADMIN_TABPOX."storedvaluedcard  where status=1 and customer_id=".$customerid);
		
		//echo "select sum(value) from  ".WEB_ADMIN_TABPOX."storedvaluedcard  where status=1 and customer_id=".$customerid;
		$chuzhiremain=$chuzhiremain?$chuzhiremain:"0.00";
		 
	
		$itemcardremain=$this -> dbObj -> GetOne("select sum(remaintimes) from  ".WEB_ADMIN_TABPOX."itemcard  where status=1 and customer_id=".$customerid);
		$itemcardremain=$itemcardremain?$itemcardremain:"0";
	 
	 
	 	$ownremain=$this -> dbObj -> GetOne("select salesowe from  ".WEB_ADMIN_TABPOX."membercard  where  customer_id=".$customerid);
		$ownremain=$ownremain?$ownremain:"0.00";
		
		$this -> dbObj -> Execute("update   ".WEB_ADMIN_TABPOX."sell set memcardremain=".$memcardremain.", yufuremain=".$yufuremain.", dingjinremain =".$dingjinremain." , chuzhiremain=".$chuzhiremain." ,itemcardremain=".$itemcardremain."  , ownremain=".$ownremain." where  sell_id=".$sellid);
		
		//echo "update   ".WEB_ADMIN_TABPOX."sell set memcardremain=".$memcardremain.", yufuremain=".$yufuremain.", dingjinremain =".$dingjinremain." , chuzhiremain=".$chuzhiremain." ,itemcardremain=".$itemcardremain." where  sell_id=".$sellid;
		
		return true;
	}
function sellcount($sellid){
//$table=$_GET['table'];
//$ziduan=$_GET['ziduan'];
//$testname=$_GET['testname'];
$table='sell';
$column='sell_id';
$value= $sellid?$sellid:$_SESSION["sellid"];
$agencyid=$_SESSION["currentorgan"]?$_SESSION["currentorgan"]:1;
 
 

$producevalue = $this -> dbObj -> GetOne("select sum(amount) from  ".WEB_ADMIN_TABPOX."selldetail    where agencyid=".$agencyid." and  sell_id=".$value);
 //echo "select sum(value) from  ".WEB_ADMIN_TABPOX."selldetail    where agencyid=".$agencyid." and  sell_id=".$value;
$servicesvalue = $this -> dbObj -> GetOne("select sum(amount) from  ".WEB_ADMIN_TABPOX."sellservicesdetail    where agencyid=".$agencyid." and  sell_id=".$value);

$cardvalue = $this -> dbObj -> GetOne("select sum(amount) from  ".WEB_ADMIN_TABPOX."sellcarddetail    where agencyid=".$agencyid." and  sell_id=".$value);

$consumevalue = $this -> dbObj ->GetOne("select sum(amount) from  ".WEB_ADMIN_TABPOX."sellconsumedetail    where agencyid=".$agencyid." and  sell_id=".$value);
$othervalue = $this -> dbObj ->GetOne("select sum(amount) from  ".WEB_ADMIN_TABPOX."sellotherdetail    where agencyid=".$agencyid." and  sell_id=".$value);
$producevalue=$this->checknull($producevalue);
$cardvalue=$this->checknull($cardvalue);
$consumevalue=$this->checknull($consumevalue);
$servicesvalue=$this->checknull($servicesvalue);
$othervalue=$this->checknull($othervalue);
$totalvule=$producevalue+$cardvalue+$consumevalue+$servicesvalue+$othervalue;
return $totalvule;
}

function checknull($value='0.00'){
	return $value;
	}   

function makeno($Prefix,$agency_no,$table,$column,$number,$id){
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by ".$id." desc limit 1");
//echo $this -> dbObj -> GetRow("select ".$column." from ".$table." order by ".$id." desc limit 1");
$nostr=$nostr[$column];
if($nostr==''){
$nostr=$Prefix.$agency_no.str_pad(1,$number,'0',STR_PAD_LEFT);

}else{
$nostr=mb_substr($nostr,strlen($nostr)-$number,$number,'utf-8');
$nostr=$nostr+1;
$nostr=str_pad($nostr,$number,'0',STR_PAD_LEFT);
$nostr=$Prefix.$agency_no.$nostr;
}
return $nostr;
}
	function quit($info){
		exit("<script>alert('$info');location.href='services.php';</script>");
	}

	
}
$main = new Pageservices();
$main -> Main();
?>
  