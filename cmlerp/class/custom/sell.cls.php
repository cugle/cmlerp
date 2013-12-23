<?
/**
 * @package System
 */
//require(WEB_ADMIN_HTTPPATH.'/admin.inc.php');
//require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class sell extends admin {
    function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='recoil')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> recoil();
        }else if(isset($_GET['action']) && $_GET['action']=='audit')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> audit();
        }else{
            parent::Main();
        }
    }
	function disp(){
		$this->creatsellno();
		}
	function recoil($sellid=0,$agencyid){
		//反冲单据。
		$selldata=$this -> dbObj ->GetRow("SELECT * FROM  ".WEB_ADMIN_TABPOX."sell  where agencyid =".$agencyid." and sell_id=".$sellid);
		 
	if($selldata['status']==1){
		$agencyid=$agencyid==''?$agencyid:$_SESSION["currentorgan"];
		//复制销售单， 复制项目，复制前置反包括金额 数量 总额 手工提成 销售提成  帐号金额 各种历史 
		 $selldata=$this -> dbObj ->GetRow("select * from ".WEB_ADMIN_TABPOX."sell where agencyid =".$agencyid." and sell_id=".$sellid);
		 $sql1="select * from ".WEB_ADMIN_TABPOX."sellservicesdetail where agencyid =".$agencyid." and sell_id=".$sellid;
		 $sql2="select * from ".WEB_ADMIN_TABPOX."selldetail where agencyid =".$agencyid." and sell_id=".$sellid;
		 $sql3="select * from ".WEB_ADMIN_TABPOX."sellcarddetail where agencyid =".$agencyid." and sell_id=".$sellid;
		 $sql4= "select * from ".WEB_ADMIN_TABPOX."sellconsumedetail where agencyid =".$agencyid." and sell_id=".$sellid;
		 $sql5="select * from ".WEB_ADMIN_TABPOX."sellotherdetail where agencyid =".$agencyid." and sell_id=".$sellid;
		 $sql=$sql1." union ".$sql2." union ".$sql3." union ".$sql4." union ".$sql5;
		 $selldetaildata=$this -> dbObj ->Execute($sql);
		 
		// $sellservicesdata=$this ->dbObj ->Execute("select * from ".WEB_ADMIN_TABPOX."sellservicesdetail where agencyid =".$agencyid."where sell_id=".$sellid);
		// $sellproducedata=$this -> dbObj ->Execute("select * from ".WEB_ADMIN_TABPOX."selldetail where agencyid =".$agencyid."where sell_id=".$sellid);
		// $sellcarddata=$this -> dbObj ->Execute("select * from ".WEB_ADMIN_TABPOX."sellcarddetail where agencyid =".$agencyid."where sell_id=".$sellid);
		// $sellconsumedata=$this -> dbObj ->Execute("select * from ".WEB_ADMIN_TABPOX."sellconsumedetail where agencyid =".$agencyid."where sell_id=".$sellid);
		// $recoilsellid=$sellotherdata=$this -> dbObj ->Execute("select * from ".WEB_ADMIN_TABPOX."sellotherdetail where agencyid =".$agencyid."where sell_id=".$sellid);
		 
		 //$recoilsellid=$this->creatsellno($selldata['sell_no'],$selldata['customer_id'],$selldata['employee_id'],$selldata['agencyid'],'sell',$selldata['membercard_no']);
		// echo 'INSERT INTO '.WEB_ADMIN_TABPOX.'sell (`sell_no`,`customer_id`,`membercard_no`,`employee_id`, `status`,`agencyid`) value ("'.$selldata['sell_no'].'","'.$selldata['customer_id'].'","'.$selldata['membercard_no'].'","'.$selldata['employee_id'].'",2,"'.$selldata['agencyid'].'")';
		$this->dbObj->Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'sell (`sell_no`,`customer_id`,`membercard_no`,`employee_id`, `status`,`agencyid`,`payable1`,`realpay`,`howtopay`,`xianjinvalue`, `yinkavalue`, `zengsongvalue`, `dingjinvalue`, `chuzhikavalue`, `xianjinquanvalue`) value ("'.$selldata['sell_no'].'","'.$selldata['customer_id'].'","'.$selldata['membercard_no'].'","'.$selldata['employee_id'].'",2,"'.$selldata['agencyid'].'","'.-$selldata['payable1'].'","'.-$selldata['realpay'].'","'.$selldata['howtopay'].'","'.$selldata['xianjinvalue'].'","'.$selldata['yinkavalue'].'","'.$selldata['zengsongvalue'].'","'.$selldata['dingjinvalue'].'","'.$selldata['chuzhikavalue'].'","'.$selldata['xianjinquanvalue'].'")');
		$recoilsellid = $this -> dbObj -> Insert_ID();
		$this -> dbObj ->Execute("update  ".WEB_ADMIN_TABPOX."sell set status=3  where agencyid =".$agencyid." and sell_id=".$sellid);
 		//echo "update  ".WEB_ADMIN_TABPOX."sell set status=3  where agencyid =".$agencyid." and  sell_id=".$sellid;
		//$this -> dbObj ->Execute("UPDATE ".WEB_ADMIN_TABPOX."sell SET status=2 where agencyid =".$agencyid." and sell_id=".$recoilsellid);
 
		$detailtablelist=array('sellservicesdetail','selldetail','sellconsumedetail','sellcarddetail','sellconsumedetail','sellotherdetail');
		while ($inrrs = $selldetaildata -> FetchRow()) {
		 
		 $this ->addsellitem($recoilsellid,$inrrs['item_type'],$inrrs['item_id'],-$inrrs['number'],$inrrs['value'],$inrrs['price'],$inrrs['discount'],$inrrs['beauty_id'],$detailtablelist[$inrrs['item_type']],$inrrs['cardtype'],$inrrs['cardid'],$inrrs['customercardid'],$inrrs['employee_id']);
		}
	echo "单号为".$selldata['sell_no']."的单据反冲成功<BR/>";		
	return 1;	
	}else{
	echo "单号为".$selldata['sell_no']."的单据反冲失败，单据可能处于未完成状态或已经审核<BR/>";	
	return 0;
	}
	}
	function audit($sellid=0,$agencyid=0,$status=4){
	//店长审核单据。

	$selldata=$this -> dbObj ->GetRow("SELECT * FROM  ".WEB_ADMIN_TABPOX."sell  where agencyid =".$agencyid." and sell_id=".$sellid);
	 
	if($selldata['status']==1 or $selldata['status']==6 ){
 	
	$this -> dbObj ->Execute("update  ".WEB_ADMIN_TABPOX."sell set status=".$status."  where agencyid =".$agencyid." and sell_id=".$sellid);
	 
	echo "单号为".$selldata['sell_no']."的单据提交成功<BR/>";	
	return 1;
 
	}else{
	echo "单号为".$selldata['sell_no']."的单据提交操作失败，单据可能处于不能提交状态 <BR/>";
	return 0;
	}
	}
	function caiwuaudit($sellid=0,$agencyid=0,$status=5){
	//财务审核单据。

	$selldata=$this -> dbObj ->GetRow("SELECT * FROM  ".WEB_ADMIN_TABPOX."sell  where agencyid =".$agencyid." and sell_id=".$sellid);
 
	if($selldata['status']==4){
 	
	$this -> dbObj ->Execute("update  ".WEB_ADMIN_TABPOX."sell set status=".$status."  where agencyid =".$agencyid." and sell_id=".$sellid);
	 
	echo "单号为".$selldata['sell_no']."的单据审核成功<BR/>";	
	return 1;
 
	}else{
	echo "单号为".$selldata['sell_no']."的单据审核操作失败，单据可能处于不能审核状态 <BR/>";
	return 0;
	}
	}	
	function returntoedit($sellid=0,$agencyid=0,$status=0){
	//审核单据。

	$selldata=$this -> dbObj ->GetRow("SELECT * FROM  ".WEB_ADMIN_TABPOX."sell  where agencyid =".$agencyid." and sell_id=".$sellid);
	 
	if($selldata['status']==5 or $selldata['status']==4){
	$sell_no=$this -> dbObj ->GetOne("SELECT sell_no FROM  ".WEB_ADMIN_TABPOX."sell  where agencyid =".$agencyid." and sell_id=".$sellid);	
	
	if($sell_no<>''){	
	$this -> dbObj ->Execute("update  ".WEB_ADMIN_TABPOX."sell set status=".$status."  where agencyid =".$agencyid." and sell_id=".$sellid);
	echo "单号为".$selldata['sell_no']."的单据撤回成功<BR/>";	
	return 1;
	}else{
	echo "单号为".$selldata['sell_no']."的单据撤回操作失败，只能撤回当天的单 <BR/>";
	return 0;	
	}
	}else{
	echo "单号为".$selldata['sell_no']."的单据撤回操作失败，单据可能处于不能撤回状态 <BR/>";
	return 0;
	}
	}	
	function promotion($agencyid=0){
	//返回店面促销信息
	}
	function addsellpaytype($membershipcard=0,$item_id=0,$agencyid=0){
		//添加付款方式
	}
	function checkout($membershipcard=0,$item_id=0,$agencyid=0){
		//结账
	}	
	function creatsellno($sellno=0,$customerid='0',$employeeid=0,$agencyid=1,$cardtable='sell',$membercard_no=''){
		$membercard_no=$membercard_no<>''?$membercard_no:$_SESSION["membercardno"];
		$this -> dbObj -> Execute("START TRANSACTION");//事务开始。
		$Prefix='XS';
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'sell';
		$column='sell_no';
		$number=5;
		$id='sell_id';
		$manualbillno=$_SESSION["manualbillno"];
		$sellno=$sellno?$this->makeno($Prefix,$agency_no,$table,$column,$number,$id):$sellno;

		//echo 'INSERT INTO '.WEB_ADMIN_TABPOX.$cardtable.' (`'.$cardtable.'_no`,`customer_id`,`membercard_no`,`employee_id`, `status`,`agencyid`) value ("'.$sellno.'","'.$customerid.'","'.$membercard_no.'","'.$employeeid.'",0,"'.$agencyid.'")';
		if($membercard_no<>''){
		$membercard = &$this -> dbObj -> GetRow("SELECT * FROM ".WEB_ADMIN_TABPOX."membercard where  membercard_no='".$membercard_no."' ");
		$membercard_id=$membercard["membercard_id"];
		}
		$membercard_id=$membercard_id==''?0:$membercard_id;
		$res=$this->dbObj->Execute('INSERT INTO '.WEB_ADMIN_TABPOX.$cardtable.' (`'.$cardtable.'_no`,`customer_id`,`membercard_id`,`membercard_no`,`employee_id`, `status`,`agencyid`) value ("'.$sellno.'","'.$customerid.'","'.$membercard_id.'","'.$membercard_no.'","'.$employeeid.'",0,"'.$agencyid.'")');	
	
		 //echo 'INSERT INTO '.WEB_ADMIN_TABPOX.$cardtable.' (`'.$cardtable.'_no`,`customer_id`,`membercard_no`,`employee_id`, `status`,`agencyid`) value ("'.$sellno.'","'.$customerid.'","'.$membercard_no.'","'.$employeeid.'",0,"'.$agencyid.'")';
		$id = $this -> dbObj -> Insert_ID();
		
		$this -> dbObj -> GetRow("update ".WEB_ADMIN_TABPOX."sell set sellmemo ='".$manualbillno."'  where  sell_id='".$id."' ");
		$membercard_id=$membercard["membercard_id"];

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
	function addsellitem($sellid=1,$item_type=0,$item_id=0,$number=1,$value=0,$price=0,$discount=10,$beauty_id,$cardtable='selldetail',$cardtype=0,$cardid=0,$customercardid=0,$employee_id=0,$itemmemo=''){
		$this -> dbObj -> Execute("START TRANSACTION");//事务开始。
		 
 		//echo 'INSERT INTO  '.WEB_ADMIN_TABPOX.$cardtable.' (`sell_id`,`item_type`,`item_id`,`value`,`price`,`number`,`beauty_id`,`agencyid`,cardtype,cardid,amount,customercardid,employee_id,discount) VALUE("'.$sellid.'","'.$item_type.'","'.$item_id.'","'.$value.'","'.$price.'","'.$number.'" ,"'.$beauty_id.'",'.$_SESSION["currentorgan"].','.$cardtype.','.$cardid.','.$number*$value.','.$customercardid.',"'.$employee_id.'","'.$discount.'")';
		 
		$res=$this->dbObj->Execute('INSERT INTO  '.WEB_ADMIN_TABPOX.$cardtable.' (`sell_id`,`item_type`,`item_id`,`value`,`price`,`number`,`beauty_id`,`agencyid`,cardtype,cardid,amount,customercardid,employee_id,discount,itemmemo) VALUE("'.$sellid.'","'.$item_type.'","'.$item_id.'","'.$value.'","'.$price.'","'.$number.'" ,"'.$beauty_id.'",'.$_SESSION["currentorgan"].','.$cardtype.','.$cardid.','.($number*$value*$discount/10).','.$customercardid.',"'.$employee_id.'","'.$discount.'","'.$itemmemo.'")');	
 
	  // echo 'INSERT INTO  '.WEB_ADMIN_TABPOX.$cardtable.' (`sell_id`,`item_type`,`item_id`,`value`,`price`,`number`,`beauty_id`,`agencyid`,cardtype,cardid,amount,customercardid,employee_id,discount,itemmemo) VALUE("'.$sellid.'","'.$item_type.'","'.$item_id.'","'.$value.'","'.$price.'","'.$number.'" ,"'.$beauty_id.'",'.$_SESSION["currentorgan"].','.$cardtype.','.$cardid.','.($number*$value*$discount/10).','.$customercardid.',"'.$employee_id.'","'.$discount.'","'.$itemmemo.'")';

		$id = $this -> dbObj -> Insert_ID();
		
	//`assignservice`="'.$res1['assignservice'].'", `price`="'.$res1['price'].'", `timelimit`="'.$res1['timelimit'].'", `totaltimes`="'.$res1['totaltimes'].'",

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
	function updatesellitem($selldetail_id,$sellid,$item_type=0,$item_id=0,$number=1,$value=0,$price=0,$discount=10,$cardtable='selldetail'){
		$this -> dbObj -> Execute("START TRANSACTION");//事务开始。
		 
		 
		$res=$this->dbObj->Execute('UPDATE  '.WEB_ADMIN_TABPOX.$cardtable.' SET`'.$cardtable.'_id`="'.$sellid.'",`item_type`="'.$item_type.'",`item_id`="'.$item_id.'", `value`="'.$res1['price'].'",`value`="'.$res1['value'].'",`number`="'.$number.'" ,agencyid='.$_SESSION["currentorgan"].' WHERE selldetail_id='.$selldetail_id);	
	
	
	//`assignservice`="'.$res1['assignservice'].'", `price`="'.$res1['price'].'", `timelimit`="'.$res1['timelimit'].'", `totaltimes`="'.$res1['totaltimes'].'",

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
function makeno($Prefix,$agency_no,$table,$column,$number,$id){
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by  sell_no desc, ".$id." desc limit 1");
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
}

?>
  