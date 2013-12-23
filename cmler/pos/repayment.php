<?
/**
 * @package System
 */
 
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/sell.cls.php');
class Pageproduce extends admin {
function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='save'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> save();			
		}else{
            parent::Main();
        }
    }	
	
	function disp(){

		//定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','repayment.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	

 
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		
		$currentcustomerid=$_SESSION["currentcustomerid"];
		$membercardno=$_SESSION["membercardno"];
 
		//echo 'SELECT * FROM `'.WEB_ADMIN_TABPOX.'customer`  A INNER JOIN '.WEB_ADMIN_TABPOX.'membercard B ON A.customer_id= B.customer_id where A.customer_id ='.$currentcustomerid;
				
		$memberdata=$this -> dbObj -> GetRow('SELECT * FROM `'.WEB_ADMIN_TABPOX.'customer`  A INNER JOIN '.WEB_ADMIN_TABPOX.'membercard B ON A.customer_id= B.customer_id where B.membercard_no ="'.$membercardno.'"');
 
		$t -> set_var('membercard_id',$memberdata['membercard_id']);
		$t -> set_var('membercard_no',$memberdata['membercard_no']);
		$t -> set_var('customer_id',$memberdata['customer_id']);
		$t -> set_var('customer_name',$memberdata['customer_name']);
		$t -> set_var('code',$memberdata['code']);
		//$t -> set_var('salesowe',$memberdata['salesowe']);
		$t -> set_var('salesowe','');
		$t -> set_var('repaymentmemo','');
		$t -> set_var('repayment',0); 
		$t -> set_var('sell_no',''); 
		$t -> set_var('sell_id',''); 
		
		$t -> set_var('memcardlevellist',$this ->selectlist('memcardlevel','cardlevel_id','cardlevel_name',$memberdata['cardlevel_id']));
		//echo  'select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$standardunit;
		$t -> set_var('dingjin','0.00');
		$t -> set_var('employee_id','');
		$t -> set_var('employee_name','');
		$t -> set_var('beauty_id','');
		$t -> set_var('beauty_name','');
		

 
 
 					

		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		}
 
	function save(){
		$membercardno=$_SESSION["membercardno"];
		$acounttypeid=$_POST['acounttypeid'];
		$value=$_POST['repayment'];
		$agencyid=$_SESSION["customerorgan"]<>''?$_SESSION["customerorgan"]:$_SESSION["currentorgan"];
		$owntype=$this -> dbObj -> GetOne("select owntype from ".WEB_ADMIN_TABPOX."sell  WHERE  sell_id='".$_POST['sell_id']."'");
		$returnstatus=$owntype;
		$returnid=$_POST['sell_id'];
	// $this -> dbObj -> Execute("UPDATE ".WEB_ADMIN_TABPOX."membercard   SET  salesowe=salesowe-'".$value."' WHERE  membercard_no='".$membercardno."'");

		//$acountdata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'account WHERE `type`='.$acounttypeid.' and  agencyid='.$agencyid);
	// echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'account WHERE `type`='.$acounttypeid.' and  agencyid='.$agencyid; 
		//$acountid=$acountdata['account_id'];
		//$lastbalance=$acountdata['balance'];
		//$nowbalance=$acountdata['balance']+$value;
		//$this->dbObj->Execute('UPDATE '.WEB_ADMIN_TABPOX.'account   SET  balance =balance + '.$value.'  WHERE   agencyid='.$agencyid.' and account_id ='.$acountid);	
		//echo 'UPDATE '.WEB_ADMIN_TABPOX.'account   SET  balance =balance + '.$value.'  WHERE   agencyid='.$agencyid.' and account_id ='.$acountid;
		 
		//$type=1;//销售1
		//$repaymentmemo=$_POST['repaymentmemo'];
		//$memo=$repaymentmemo<>''?$repaymentmemo:'销售欠账还款';
		 
		//账户流水账
		
		//$this->dbObj->Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'accounthistory (`account_id`, `value`,  `type`, `sellid`, `lastbalance`, `nowbalance`, `memo`, `agencyid`)value('.$acountid.','.$value.','.$type.',"'.$sellid.'",'.$lastbalance.','.$nowbalance.',"'.$memo.'",'.$agencyid.')') ;//帐户流水账 
	//echo 'INSERT INTO '.WEB_ADMIN_TABPOX.'accounthistory (`account_id`, `value`,  `type`, `sellid`, `lastbalance`, `nowbalance`, `memo`, `agencyid`)value('.$acountid.','.$value.','.$type.',"'.$sellid.'",'.$lastbalance.','.$nowbalance.',"'.$memo.'",'.$agencyid.')';


		 //增加销售单项目
		 $this->SellObj=new sell();
		 $employeeid=$this ->dbObj-> GetOne('select A.employee_id from '.WEB_ADMIN_TABPOX.'employee A INNER JOIN '.WEB_ADMIN_TABPOX.'user B ON A.employee_id=B.employee_id where B.userid='.$this->getUid());
		 $sellidcount=$this ->dbObj-> GetOne('select count(*) from '.WEB_ADMIN_TABPOX.'sell  where sell_no="'.$_SESSION["sellno"].'"');
		 //echo 'select count(*) from '.WEB_ADMIN_TABPOX.'sell  where sell_no='.$_SESSION["sellno"].'"';
		 
		 if ($sellidcount==0){//如果没有新单号则插入新单号
		 $customerid=$_SESSION["currentcustomerid"]?$_SESSION["currentcustomerid"]:0;
		 $sellid=$this->SellObj->creatsellno($_SESSION["sellno"],$customerid,$employeeid,$_SESSION["currentorgan"],$cardtable='sell');
		 
		 $_SESSION["sellid"]=$sellid;
		 }else{
		 $sellid=$_SESSION["sellid"];
		 }
		 
		 $item_id=3;//定金$itemnamelist=array('预收款',"储值卡充值","定金","还款","赠送手工","其他","预付产品款","赠送产品款","赠送购卡款");
		 $number=1;
		 $value=$_POST["repayment"];
		 $price=$_POST["repayment"];
		 $discount=10;
		 $cardtype=0;//款项
		 $cardid=3;//定金$itemnamelist=array('预收款',"储值卡充值","定金","还款","赠送手工","其他","预付产品款","赠送产品款","赠送购卡款");
		 $customercardid=$_POST['membercard_id'];
		 $employee_id=$_POST['employee_id'];
		 //if($_POST['givingbeauty']=='on'){
			
			// $discount=0;
			   // echo  $discount;
			// }else{
		   //  $discount=10;
		
		// }
		 $item_type=5;//其他项目
		 $beauty_id=$_POST['beauty_id'];;
		 $cardtable='sellotherdetail';
		 $itemmemo=$_POST['repaymentmemo'];
		 $id = $this->SellObj->addsellitem($sellid,$item_type,$item_id,$number,$value,$price,$discount,$beauty_id,$cardtable,$cardtype,$cardid,$customercardid,$employee_id,$itemmemo);
		 $this->dbObj->Execute('update  '.WEB_ADMIN_TABPOX.'sellotherdetail set returnstatus ='.$returnstatus.' ,returnid='.$returnid.' WHERE  selldetail_id ='.$id);	
	 	
		//echo 'update  '.WEB_ADMIN_TABPOX.'sellotherdetail set returnstatus ='.$returnstatus.' ,returnid='.$returnid.' WHERE  selldetail_id ='.$id;
		
		exit("<script>alert('操作成功');window.parent.close();</script>");
	}
		function selectlist($table,$id,$name,$selectid=0){
			$agencyid=$_SESSION["customerorgan"]<>''?$_SESSION["customerorgan"]:$_SESSION["currentorgan"];
			//echo 'select * from '.WEB_ADMIN_TABPOX.$table .' where agencyid ='.$agencyid;
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table .' where agencyid ='.$agencyid);
			$str='';
	     	while ($inrrs = &$inrs -> FetchRow()) {
			
			if ($inrrs[$id]==$selectid)
			$str =$str."<option value=".$inrrs[$id]." selected>".$inrrs[$name]."</option>";	
			else
			$str =$str."<option value=".$inrrs[$id].">".$inrrs[$name]."</option>";			
			}
			$inrs-> Close();	
			return  $str;	
	    }
	
}
$main = new Pageproduce();
$main -> Main();
?>
  