<?
/**
 * @package System
 */
 //商品流水账
//require(WEB_ADMIN_HTTPPATH.'/admin.inc.php');
//require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class currentaccount extends admin {
	function main($object_type,$object_id,$bill_type,$bill_id,$billtype,$bill_no,$addmoney,$lessen, $man, $memo,$agencyid){
		$rs=addrecord($object_type,$object_id,$bill_type,$bill_id,$billtype,$bill_no,$addmoney,$lessen, $man, $memo,$agencyid);
		return $rs;
	}
	
	//增加对账记录
	function addrecord($bill_type,$bill_id,$object_type,$agencyid){

		$fromtype=array("sell","sell","sell","sell","purchase","purchreturn","takestock","lossregister","fukuanbill");	
		$billname=array("销货单","销货单","销货单","销货单","进货单","采购退货单","盘点单","报损单","付款单");
		$objecttypename=array("","顾客","供应商","其他");
		$objecttable=array("","customer","suppliers","objecttype");
		$billtable=$fromtype[$bill_type];
		$bill=$this->dbObj -> GetRow("SELECT * FROM ".WEB_ADMIN_TABPOX.$billtable." WHERE ".$billtable."_id= ".$bill_id);
		 
		$memolist=array("已收顾客","已收顾客","已收顾客","已收顾客","应付供货商","应收供货商","","","已付");
	 	
		 
		
		$objecttable1=$objecttable[$object_type];
		$object_id=$bill[$objecttable1.'_id'];
		if($bill_type==8){$object_id=$bill['object_id'];}
		$object_name=$this->dbObj -> GetOne("SELECT ".$objecttable1."_name FROM ".WEB_ADMIN_TABPOX.$objecttable1." WHERE ".$objecttable1."_id= ".$object_id);
		 
		
		$bill_no=$bill[$billtable.'_no'];
		if($bill_type<4 or $bill_type==7 or $bill_type==5 or $bill_type==8){
		$addmoney=0;
		$lessen=$bill['acount'];
		}else if($bill_type==4  or $bill_type==6) {
		$addmoney=$bill['acount'];
		$lessen=0;
		}
		
		$man=$this -> dbObj -> getone('select B.employee_name from '.WEB_ADMIN_TABPOX.'user A INNER JOIN '.WEB_ADMIN_TABPOX.'employee B ON A.employee_id=B.employee_id  where A.userid = '.$this->getUid());
		$memo=$memolist[$bill_type].$object_name.$bill['acount']."元";
		$agencyid=$bill['agencyid'];
		
		$date=date("Y-m-d",time());
		$status=1;//正常1，2冲销 ，3被冲销单
		$balance=$this->dbObj->GetOne("select balance from  `".WEB_ADMIN_TABPOX."currentaccount` where agencyid=".$agencyid." order  by  currentaccount_id desclimit 1");
		$balance=$balance==''?0:$balance;
		
		$balance=$balance+$bill['acount'];;
		$rs1=$this->dbObj->Execute("INSERT INTO  `".WEB_ADMIN_TABPOX."currentaccount` (`date`, `object_type`, `object_id`, `bill_id`, `bill_no`, `bill_type`, `addmoney`, `lessen`, `balance`, `man`, `memo`, `status`, `agencyid`) VALUE ('".$date."',".$object_type.",".$object_id.",".$bill_id.",'".$bill_no."',".$bill_type.",".$addmoney.",".$lessen.",'".$balance."','".$man."','".$memo."'  ,'".$status."',".$agencyid.")");
		 //echo "INSERT INTO  `".WEB_ADMIN_TABPOX."currentaccount` (`date`, `object_type`, `object_id`, `bill_id`, `bill_no`, `bill_type`, `addmoney`, `lessen`, `balance`, `man`, `memo`, `status`, `agencyid`) VALUE ('".$date."',".$object_type.",".$object_id.",".$bill_id.",'".$bill_no."',".$bill_type.",".$addmoney.",".$lessen.",'".$balance."','".$man."','".$memo."'  ,'".$status."',".$agencyid.")";
		$id = $this -> dbObj -> Insert_ID();

		if($rs1){
		return true;	
		}else{	 
		return false;		
		}
    }
	//增加对账记录
	function addrecordsell($bill_type,$bill_id,$object_type,$agencyid){
      
		$fromtype=array("sell","sell","sell","sell","purchase","purchreturn","takestock","lossregister","fukuanbill");	
		$billname=array("销货单","销货单","销货单","销货单","进货单","采购退货单","盘点单","报损单");
		$objecttypename=array("","顾客","供应商","其他");
		$objecttable=array("","customer","suppliers","objecttype");
		$billtable=$fromtype[$bill_type];
		$bill=$this->dbObj -> GetRow("SELECT * FROM ".WEB_ADMIN_TABPOX.$billtable." WHERE ".$billtable."_id= ".$bill_id);
		
		$memolist=array("已收顾客","已收顾客","已收顾客","已收顾客","应付供货商","应收供货商","","");
	 	$sql5="select count(amount) as account from  ".WEB_ADMIN_TABPOX."sellotherdetail  where item_id=3 and agencyid =".$agencyid." and sell_id=".$bill_id;//还款
		$account=$this->dbObj -> GetOne($sql5);
		 
		$objecttable1=$objecttable[$object_type];
		$object_id=$bill[$objecttable1.'_id'];
		$object_name=$this->dbObj -> GetOne("SELECT ".$objecttable1."_name FROM ".WEB_ADMIN_TABPOX.$objecttable1." WHERE ".$objecttable1."_id= ".$object_id);
		 
		
		$bill_no=$bill[$billtable.'_no'];
		$account=$account==''?0:$account;

		$man=$this -> dbObj -> getone('select B.employee_name from '.WEB_ADMIN_TABPOX.'user A INNER JOIN '.WEB_ADMIN_TABPOX.'employee B ON A.employee_id=B.employee_id  where A.userid = '.$this->getUid());
		
		$agencyid=$bill['agencyid'];
		
		$date=date("Y-m-d",time());
		$status=1;//正常1，2冲销 ，3被冲销单
		$balance=$this->dbObj->GetOne("select balance from  `".WEB_ADMIN_TABPOX."currentaccount` where agencyid=".$agencyid." order  by  currentaccount_id desclimit 1");
		$balance=$balance==''?0:$balance;
		
		
		
		
		if($bill['payable1']<>0){//应收
		$addmoney=$bill['payable1'] ;
		$lessen=0;
		$balance=$balance+$bill['payable1'];
		$memo="应收顾客".$object_name.$bill['payable1']."元";
		$rs1=$this->dbObj->Execute("INSERT INTO  `".WEB_ADMIN_TABPOX."currentaccount` (`date`, `object_type`, `object_id`, `bill_id`, `bill_no`, `bill_type`, `addmoney`, `lessen`, `balance`, `man`, `memo`, `status`, `agencyid`) VALUE ('".$date."',".$object_type.",".$object_id.",".$bill_id.",'".$bill_no."',".$bill_type.",".$addmoney.",".$lessen.",'".$balance."','".$man."','".$memo."'  ,'".$status."',".$agencyid.")");
		
		}
		if($bill['realpay']<>0){//已付
		$addmoney=0;
		$lessen=$bill['realpay'] ;
		$balance=$balance-$bill['realpay'];
		$memo="顾客".$object_name."已付".$bill['payable1']."元";
		$rs1=$this->dbObj->Execute("INSERT INTO  `".WEB_ADMIN_TABPOX."currentaccount` (`date`, `object_type`, `object_id`, `bill_id`, `bill_no`, `bill_type`, `addmoney`, `lessen`, `balance`, `man`, `memo`, `status`, `agencyid`) VALUE ('".$date."',".$object_type.",".$object_id.",".$bill_id.",'".$bill_no."',".$bill_type.",".$addmoney.",".$lessen.",'".$balance."','".$man."','".$memo."'  ,'".$status."',".$agencyid.")");
	
		}
		
		if($account>0){//还款
		$addmoney=  $account;
		$lessen=$balance- $account;
		$balance=0;
		$memo="顾客".$object_name."还款".$account."元";
		
		$rs1=$this->dbObj->Execute("INSERT INTO  `".WEB_ADMIN_TABPOX."currentaccount` (`date`, `object_type`, `object_id`, `bill_id`, `bill_no`, `bill_type`, `addmoney`, `lessen`, `balance`, `man`, `memo`, `status`, `agencyid`) VALUE ('".$date."',".$object_type.",".$object_id.",".$bill_id.",'".$bill_no."',".$bill_type.",".$addmoney.",".$lessen.",'".$balance."','".$man."','".$memo."'  ,'".$status."',".$agencyid.")");
		}
		

		//echo "INSERT INTO  `".WEB_ADMIN_TABPOX."currentaccount` (`date`, `object_type`, `object_id`, `bill_id`, `bill_no`, `bill_type`, `addmoney`, `lessen`, `balance`, `man`, `memo`, `status`, `agencyid`) VALUE (".$date.",".$object_type.",".$object_id.",".$bill_id.",".$bill_no.",".$bill_type.",".$addmoney.",".$lessen.",'".$balance."','".$man."','".$memo."'  ,'".$status."',".$agencyid.")";
		$id = $this -> dbObj -> Insert_ID();
		
		if($rs1){
		return true;	
		}else if($bill['realpay']==0){
		return true;
		}{	 
		return false;		
		}
    }
	
	//冲销对账记录
	function recoilrecord($bill_type,$bill_id,$agencyid){
		$objecttypename=array("","顾客","供应商","其他");
		$objecttable=array("","customer","suppliers","objecttype");
		
		$res1=$this->dbObj->Execute("update  ".WEB_ADMIN_TABPOX."currentaccount set status=3 where  bill_type=".$bill_type." and bill_id in (".$bill_id.") and  agencyid=".$agencyid);
		$rres2=$this->dbObj->Execute("select * from  ".WEB_ADMIN_TABPOX."currentaccount  where bill_type=".$bill_type." and bill_id in (".$bill_id.") and  agencyid=".$agencyid);
		while ($res2 = &$rres2 -> FetchRow()) {
		$object_type=$res2['object_type'];
		$object_id=$res2['object_id'];
		$bill_type=$res2['bill_type'];
		$bill_id=$res2['bill_id'];
		$billtype=$res2['billtype'];
		$bill_no=$res2['bill_no'];
		$addmoney=$res2['lessen'];
		$lessen=$res2['addmoney'];
		$balance=$res2['balance']-($res2['addmoney']-$res2['lessen']);
		$man=$res2['man'];
		$memo="单据被红字冲销后的";
		$agencyid=$res2['agencyid'];
		$date=$res2['date'];
		$date=date("Y-m-d",time());
		$status=2;//正常1，2冲销 ，3被冲销单
		$res3=$this->dbObj->Execute("INSERT INTO  `".WEB_ADMIN_TABPOX."currentaccount` (`date`, `object_type`, `object_id`, `bill_id`, `bill_no`, `bill_type`, `addmoney`, `lessen`, `balance`, `man`, `memo`, `status`, `agencyid`) VALUE ('".$date."',".$object_type.",".$object_id.",".$bill_id.",'".$bill_no."',".$bill_type.",".$addmoney.",".$lessen.",'".$balance."','".$man."','".$memo."'  ,'".$status."',".$agencyid.")");
	 
		}
		
		 $id = $this -> dbObj -> Insert_ID();
	 	 
		if($res1&&$res3){	
		return true;	
		}else if(!$rres2){
		return true;
		}else{	 
		return false;		
		}
    }
	
}
//$main = new stock();
//$main -> Main();
?>
  