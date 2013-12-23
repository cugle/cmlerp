<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/sell.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/card.cls.php');
class Pageservices extends admin {
    function Main()
    {   
        if(isset($_GET['sellid']) ){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> viewbill($_GET['sellid']);			
		}else{
            parent::Main();
        }
    }	
	function viewbill($sellid){
		//定义模板
 		 
		$t = new Template('../template/analysis');
		$t -> set_file('f','viewbill.html');
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
				}else if($inrrs['item_type']==0){//服务 
				$t -> set_var('itemtype_name',$this -> dbObj -> getone('select A.category_name  from '.WEB_ADMIN_TABPOX.'servicecategory  A INNER JOIN '.WEB_ADMIN_TABPOX.'services B ON A.category_id =B.categoryid    where B.services_id ='.$inrrs["item_id"]));
				//echo 'select A.servicecategory_name from '.WEB_ADMIN_TABPOX.'servicecategory  A INNER JOIN '.WEB_ADMIN_TABPOX.'services B ON A.category_id =B.categoryid    where B.services_id ='.$inrrs["cardid"];
				}else if($inrrs['item_type']==5){//其他
				
	$itemnamelist=array('预收款',"储值卡充值","定金","还款","赠送手工","其他","预付产品款","赠送产品款","赠送购卡款");		
	$cardtypelist=array('款项',"其他");	
	$itemname=$itemnamelist[$inrrs['item_id']];
	$cardtype=$cardtypelist[$inrrs['cardtype']];
				$t -> set_var('itemtype_name',$cardtype);
				//echo 'select A.servicecategory_name from '.WEB_ADMIN_TABPOX.'servicecategory  A INNER JOIN '.WEB_ADMIN_TABPOX.'services B ON A.category_id =B.categoryid    where B.services_id ='.$inrrs["cardid"];
				}else{
				$t -> set_var('itemtype_name',$this -> dbObj -> getone('select marketingcard_name from '.WEB_ADMIN_TABPOX.'marketingcard where marketingcard_id ='.$inrrs["cardid"]));
				 
			 }
			$t -> set_var('memo','');
			$beauty_id = explode(';',$inrrs["beauty_id"]);
			$beauty_name='';
			for ($i=0;$i<count($beauty_id);$i++)	{
			$beauty_name=$beauty_name==''?$beauty_name:$beauty_name.',';
			$beauty_name =$beauty_name.$this -> dbObj -> getone('select employee_name from '.WEB_ADMIN_TABPOX.'employee  where employee_id ='.$beauty_id[$i]);
			}
			$t -> set_var('beauty',$beauty_name);

			$employee_id = explode(';',$inrrs["employee_id"]);
			$employee_name='';
			for ($i=0;$i<count($employee_id);$i++)	{
			$employee_name=$employee_name==''?$employee_name:$employee_name.',';
			$employee_name =$employee_name.$this -> dbObj -> getone('select employee_name from '.WEB_ADMIN_TABPOX.'employee  where employee_id ='.$employee_id[$i]);
			}


			$t -> set_var('consultant',$employee_name);
			$t -> set_var('type_name',$itemtype_name[$inrrs['item_type']]);
			$t -> set_var('itemname',$itemdata[$table_name[$inrrs['item_type']].'_name']);
			$t -> set_var('itemno',$itemdata[$table_name[$inrrs['item_type']].'_no']);
			$t -> set_var('number',$inrrs['number']);
			if($inrrs['item_type']==5){
				$t -> set_var('itemname',$itemname);
			$t -> set_var('itemno',$inrrs['item_type'].$inrrs['item_id']);
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

}
$main = new Pageservices();
$main -> Main();
?>
  