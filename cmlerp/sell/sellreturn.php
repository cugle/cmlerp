<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/stock.cls.php');
class Pagecustomer extends admin {
	var $stockObj = null;
    function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='printbill')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> printbill();
        }else if(isset($_POST['action']) && $_POST['action']=='audit'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> audit();			
		}else{
            parent::Main();
        }
    }
function  printbill(){

		$t = new Template('../template/sell');
		$t -> set_file('f','sellreturn_detail_print.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	
 
			$updid = $_GET[MODIFY.'id'] + 0 ;	
			
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'sellreturn WHERE sellreturn_id = '.$updid);
			 $t -> set_var('storageidlist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$data['warehouse_id']));
			//$t -> set_var('order_no',$this->dbObj->GetOne('SELECT order_no FROM '.WEB_ADMIN_TABPOX.'order WHERE order_id = '.$data[order_id]));
			$t -> set_var('sell_no',$this -> dbObj -> getone('select sell_no from '.WEB_ADMIN_TABPOX.'sell  where sell_id ='.$data["sell_id"]));
			
		 $status_name=array("未提交","正常","<font color=red>红字冲销</font>","<font color=red>被红字冲销</font>","<font color=green>已提交</font>","<font color=#AAAAAA>已审核</font>");
			$t -> set_var('status_name',$status_name[$data['status']]);
			$t -> set_var('cmemo',$data['memo']);			
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');			
			$t -> set_var($data);			
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$t -> set_var('comdisplay',"");	
		$t -> set_var('cproduce_no',"");	
		$t -> set_var('cproduce_name',"");	
		$t -> set_var('ccode',"");	
		$t -> set_var('cproduce_id',"");	
		$t -> set_var('cstandardunit',"");	
		$t -> set_var('lowerlimit',"");	
		$t -> set_var('cprice',"");	
		$t -> set_var('cmemo',"");	
		$t -> set_var('cnumber',"");	
		$t -> set_var('cviceunitnumber',"");	
		$t -> set_var('cviceunit',"");	
		$t -> set_var('proupdid',"");	
		$t -> set_var('ctotalacount',"");	
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
		
			$inrs2 = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'employee   where employee_id  ='.$data['employee_id']);
	     	while ($inrrs2 = &$inrs2 -> FetchRow()) {
				$t -> set_var($inrrs2);
				
			}			
 					
		//设置消耗品列表
			$acount=0;
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'sellreturndetail  where sellreturn_id  ='.$updid);
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('numbers',$inrrs['number']);
				$data1=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrrs["item_id"]);
				$t -> set_var($data1);
				 $acount=$acount+$inrrs['amount'];
				$t -> set_var('unit_name',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$data1["standardunit"]));
				$t -> set_var('memo',$inrrs['memo']);
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();		
			$t -> set_var('recordcount',$inrs->RecordCount());
			$t -> set_var('acount',$acount);
 
			
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));	
		$t -> set_var('memo',$data['memo']);//因为与明细的memo冲突，故放到这里
		$t -> set_var('customer_name',$this -> dbObj -> getone('select customer_name from '.WEB_ADMIN_TABPOX.'customer  where customer_id ='.$data["customer_id"]));
		 
		//$t -> set_var('categorynamelist',$this ->selectlist('servicecategory','category_id','category_name',$data['categoryid']));		
		
		
		//$t -> set_var('ml',"");	
		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
}	

function audit(){
    
$this -> dbObj -> Execute("START TRANSACTION");//事务开始。

	$updid=$_POST['updid'];
	$res= $this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."sellreturn` SET `status`=1  WHERE sellreturn_id =".$updid);//4表示被反冲3反冲2正常1未提交	
	
	//生产凭证
 	$condition =' sellreturn_id='.$updid;
	$sql='select * from '.WEB_ADMIN_TABPOX.'sellreturn  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;	
	
		$Prefix='VH';
		$agency_no=$_SESSION["agency_no"].date('ym',time());
		$table=WEB_ADMIN_TABPOX.'transfervoucher';
		$column='transfervoucher_no';
		$number=5;
		$id='transfervoucher_id';	
		
		$transfervoucher_no=$this->makeno($Prefix,$agency_no,$table,$column,$number,$id);	
		$man=$this -> dbObj -> getone('select B.employee_name from '.WEB_ADMIN_TABPOX.'user A INNER JOIN '.WEB_ADMIN_TABPOX.'employee B ON A.employee_id=B.employee_id  where A.userid = '.$this->getUid());
		$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  sellreturn_id DESC  LIMIT 0 ,1");
		  
			while ($inrrs = &$inrs -> FetchRow()) {
				
			$customer_id=$inrrs['customer_id'];
		 	$sellreturn_no=$inrrs['sellreturn_no'];
			//插入主记录
			$res1=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucher` (`transfervoucher_no`,`date`,`agencyid`,`creattime`,`man`) VALUES ('" .$transfervoucher_no."','".date('Y-m-d',time())."','".$_SESSION["currentorgan"]."','".date('Y-m-d H:i:s',time())."', '".$man."')");
 //echo "INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucher` (`transfervoucher_no`,`date`,`agencyid`,`creattime`,`man`) VALUES ('" .$transfervoucher_no."','".date('Y-m-d',time())."','".$_SESSION["currentorgan"]."','".date('Y-m-d H:i:s',time())."', '".$man."')";
			$id = $this -> dbObj -> Insert_ID();
				 
		$sqlstr='select * from '.WEB_ADMIN_TABPOX.'sellreturndetail  where  agencyid ='.$_SESSION["currentorgan"].' and sellreturn_id='.$updid;	
		
		//插入转账明细
	
				$inrs1 = &$this -> dbObj -> Execute($sqlstr);
				while ($inrrs1 = &$inrs1 -> FetchRow()) {
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','126','4','".$inrrs1['item_id']."','".$inrrs1['amount']."','0','"."销售退货 ".$sellreturn_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
			$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','0','".$inrrs1['amount']."','"."销售退货 ".$sellreturn_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				}
			
				}

		if($res&&$res1&&$res2){
		
		$this -> dbObj -> Execute("COMMIT");
		 exit("<script>alert('提交成功');window.location.href='sellreturn.php';</script>");
		}else{
		$this -> dbObj -> Execute("ROLLBACK");
		 exit("<script>alert('发生错误，提交失败，数据已经回滚。');window.location.href='sellreturn.php';</script>");
		}
	  
	
	
	}


function  addorder(){
	$condition =' order_id='.$_GET['orderid'];
	$sql='select * from '.WEB_ADMIN_TABPOX.'order  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;	
	
	
	$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  order_id DESC  LIMIT 0 ,1");
		     	while ($inrrs = &$inrs -> FetchRow()) {

			$man=$this -> dbObj -> getone('select username from '.WEB_ADMIN_TABPOX.'user where userid = '.$this->getUid());
			//插入进货记录
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."sellreturn` (`sellreturn_no`,`order_id`,`warehouse_id`,`suppliers_id`,`employee_id`,`acount`,`creattime`,`memo`,`sellreturn_time`,`man`,`agencyid`) VALUES ('" .$_GET["sellreturn_no"]."','".$_GET['orderid']."', '".$inrrs["warehouse_id"]."', '" .$inrrs["suppliers_id"]."','".$inrrs["employee_id"]."', '" .$inrrs["acount"]."','".date('Y-m-d',time())."', '".$inrrs["memo"]."', '".date('Y-m-d',time())."','".$man."','".$_SESSION["currentorgan"]."')");
			//echo "INSERT INTO `".WEB_ADMIN_TABPOX."sellreturn` (`sellreturn_no`,`order_id`,`warehouse_id`,`suppliers_id`,`employee_id`,`acount`,`creattime`,`memo`,`agencyid`) VALUES ('" .$_GET["sellreturn_no"]."','".$_GET['orderid']."', '".$inrrs["warehouse_id"]."', '" .$inrrs["suppliers_id"]."','".$inrrs["employee_id"]."', '" .$inrrs["acount"]."','".date('Y-m-d',time())."', '".$inrrs["memo"]."','".$_SESSION["currentorgan"]."')";
			$id = $this -> dbObj -> Insert_ID();
			//echo $id;
		$sqlstr='select * from '.WEB_ADMIN_TABPOX.'orderdetail  where  agencyid ='.$_SESSION["currentorgan"].' and order_id='.$_GET['orderid'];	
		
		//插入进货明细
		$inrs1 = &$this -> dbObj -> Execute($sqlstr);
				while ($inrrs1 = &$inrs1 -> FetchRow()) {
					$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."sellreturndetail` (sellreturn_id,produce_id,number,sellreturn_price,totalacount,memo,agencyid) VALUES ('".$id."','".$inrrs1["produce_id"]."','".$inrrs1["number"]."','".$inrrs1["orderprice"]."','".$inrrs1["totalacount"]."','".$inrrs1["memo"]."','".$_SESSION["currentorgan"]."')");
				//	echo "INSERT INTO `".WEB_ADMIN_TABPOX."sellreturndetail` (sellreturn_id,produce_id,number,sellreturn_price,totalacount,agencyid) VALUES ('".$id."','".$inrrs1["produce_id"]."','".$inrrs1["number"]."','".$inrrs1["orderprice"]."','".$inrrs1["totalacount"]."','".$_SESSION["currentorgan"]."')";
					
					}
			
				}
	   exit("<script>window.location.href='sellreturn.php?action=upd&updid=".$id."';</script>");
}
	function disp(){
		//定义模板
		$t = new Template('../template/sell');
		$t -> set_file('f','sellreturn.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "%'.$keywords.'"';}else{$condition=$category.' like "%'.$keywords.'%"';}
		}
		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:20;
			$offset = $pageid>0?($pageid-1)*$psize:0;

		
			//设置分类
			$t -> set_var('ml');

			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'sellreturn where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql="select * from ".WEB_ADMIN_TABPOX."sellreturn INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  s.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'sellreturn WHERE  agencyid ='.$_SESSION["currentorgan"].' and status in (0,1,2,3,4,5) ';
			 
			}
		 
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  sellreturn_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));			
	    	$t -> set_var('recordcount',$count);	
			
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$sell=$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'sell  where sell_id ='.$inrrs["sell_id"]);
				$t -> set_var('sell_no',$sell['sell_no']);
				
				
				$t -> set_var('brand_name',$this -> dbObj -> getone('select brand_name from '.WEB_ADMIN_TABPOX.'brand  where brand_id ='.$inrrs["brandid"]));
				$t -> set_var('employee_name',$this -> dbObj -> getone('select employee_name  from '.WEB_ADMIN_TABPOX.'employee  where employee_id ='.$inrrs["employee_id"]));
				$t -> set_var('order_no',$this -> dbObj -> getone('select order_no from '.WEB_ADMIN_TABPOX.'order  where order_id ='.$inrrs["order_id"]));
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['sellreturn_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['sellreturn_id']));	
				$t -> set_var('print','<a href="?action=printbill&updid='.$inrrs['sellreturn_id'].'" target="_blank">打印</a>'); 	
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	
 
	
	
	function goDispAppend(){

		$t = new Template('../template/sell');
		$t -> set_file('f','sellreturn_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
			
		//$t -> set_var('sellreturn_no',"");
		$Prefix='SR';
		$agency_no=$_SESSION["agency_no"].date('ymd',time());
		$table=WEB_ADMIN_TABPOX.'sellreturn';
		$column='sellreturn_no';
		$number=3;
		$id='sellreturn_id';	
		
		$t -> set_var('sellreturn_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));	
		$t -> set_var('sell_no',"");	
		$t -> set_var('sell_id',"");
		$t -> set_var('customer_name',"");
		$t -> set_var('creattime',date('Y-m-d H:i:s',time()));	
		$t -> set_var('cstandardunit',"");
		$t -> set_var('acount',"0");
		$t -> set_var('recordcount',"0");
		$t -> set_var('order_no',"");	
		$t -> set_var('employee_name',"");	
		$t -> set_var('employee_id',"");	
		$t -> set_var('sellreturn_time',date('Y-m-d',time()));	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");
		$t -> set_var('comdisplay',"none");		
		$t -> set_var('memo',"");

		$t -> set_var('man',$this -> dbObj -> getone('select B.employee_name from '.WEB_ADMIN_TABPOX.'user A INNER JOIN '.WEB_ADMIN_TABPOX.'employee B ON A.employee_id=B.employee_id  where A.userid = '.$this->getUid()));

		$t -> set_var('cproduce_no',"");
		$t -> set_var('cproduce_name',"");
		$t -> set_var('cproduce_id',"");	
		$t -> set_var('ccode',"");	
		$t -> set_var('ctotalacount',"");	
		$t -> set_var('cnumber','');	
		$t -> set_var('cprice',"");	
		$t -> set_var('cviceunit','');
		$t -> set_var('cmemo','');
		$t -> set_var('ml',"");	
		$t -> set_var('storageidlist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',''));	
		$t -> set_var('addprodisplay',"display:none");	
		$t -> set_var('userid',$this->getUid());		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'sellreturn WHERE sellreturn_id = '.$updid);
			 $t -> set_var('storageidlist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$data['warehouse_id']));
			//$t -> set_var('order_no',$this->dbObj->GetOne('SELECT order_no FROM '.WEB_ADMIN_TABPOX.'order WHERE order_id = '.$data[order_id]));
			$t -> set_var('sell_no',$this -> dbObj -> getone('select sell_no from '.WEB_ADMIN_TABPOX.'sell  where sell_id ='.$data["sell_id"]));
			
		 

			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');			
			$t -> set_var($data);			
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$t -> set_var('comdisplay',"");	
		$t -> set_var('cproduce_no',"");	
		$t -> set_var('cproduce_name',"");	
		$t -> set_var('ccode',"");	
		$t -> set_var('cproduce_id',"");	
		$t -> set_var('cstandardunit',"");	
		$t -> set_var('lowerlimit',"");	
		$t -> set_var('cprice',"");	
		$t -> set_var('cmemo',"");	
		$t -> set_var('cnumber',"");	
		$t -> set_var('cviceunitnumber',"");	
		$t -> set_var('cviceunit',"");	
		$t -> set_var('proupdid',"");	
		$t -> set_var('ctotalacount',"");	
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
		
			$inrs2 = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'employee   where employee_id  ='.$data['employee_id']);
	     	while ($inrrs2 = &$inrs2 -> FetchRow()) {
				$t -> set_var($inrrs2);
				
			}			
 					
		//设置消耗品列表
			$acount=0;
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'sellreturndetail  where sellreturn_id  ='.$updid);
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('numbers',$inrrs['number']);
				$data1=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrrs["item_id"]);
				$t -> set_var($data1);
				 $acount=$acount+$inrrs['amount'];
				$t -> set_var('unit_name',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$data1["standardunit"]));
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();		
			$t -> set_var('recordcount',$inrs->RecordCount());
			$t -> set_var('acount',$acount);
		// 修改消耗品
		 
		    if($_GET['csellreturndetail_id']!=''){
			//$t -> set_var('cs');
		   $t -> set_var('caction','upd');	
            
			$inrs2 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'sellreturndetail  where sellreturndetail_id ='.$_GET['csellreturndetail_id']);
			 
			//$t -> set_var($inrs);
			
		    $t -> set_var('cupdid',$inrs2['item_id']);	
			$t -> set_var('proupdid',$inrs2['sellreturndetail_id']);	
			
			$t -> set_var('cnumber',$inrs2['number']);
			$t -> set_var('ctotalacount',$inrs2['amount']);
			$inrs3 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrs2['item_id']);
			$t -> set_var('cproduce_no',$inrs3['produce_no']);
			$t -> set_var('cproduce_name',$inrs3['produce_name']);
			$t -> set_var('cstandardunit',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$inrs3['standardunit']));
			$t -> set_var('cproduce_id',$inrs3['produce_id']);
			$t -> set_var('ccode',$inrs3['code']);
			$t -> set_var('cprice',$inrs3['price']);
			$t -> set_var('cmemo',$inrs2['memo']);			
			//$t -> set_var('cviceunit',$inrs3['viceunit']);
			//$t -> set_var('cviceunitnumber',$inrs3['viceunitnumber']);
			//$t -> parse('cs','consumables',true);
			//$inrs2 -> Close();	
			}
			
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));	
		$t -> set_var('memo',$data['memo']);//因为与明细的memo冲突，故放到这里
		$t -> set_var('customer_name',$this -> dbObj -> getone('select customer_name from '.WEB_ADMIN_TABPOX.'customer  where customer_id ='.$data["customer_id"]));
		}
		//$t -> set_var('categorynamelist',$this ->selectlist('servicecategory','category_id','category_name',$data['categoryid']));		
		
		
		//$t -> set_var('ml',"");	

		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}
	
	
	
	
		function selectlist($table,$id,$name,$selectid=0){
	
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table .' where agencyid ='.$_SESSION["currentorgan"]);
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
	
	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
	  if($_GET['sellreturndetail_id']==''){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'sellreturn WHERE sellreturn_id in('.$delid.')');
		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'purchasedetail WHERE sellreturn_id in('.$delid.')');
		
		}else{

		 $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'sellreturndetail WHERE sellreturndetail_id in('.$_GET['sellreturndetail_id'].')');
		
		}
		$info='删除';
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		
		if($this -> isAppend){
			
			$info = '增加';	
			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."sellreturn` (`sellreturn_no`,`sell_id`,`customer_id`,`employee_id`, `status`,`agencyid`,`payable1`,sellreturn_time,memo) VALUES ('" .$_POST["sellreturn_no"]."','" .$_POST["sell_id"]."','".$_POST["customer_id"]."','" .$_POST["employee_id"]."','1', '".$_SESSION["currentorgan"]."','0','" .$_POST["sellreturn_time"]."','" .$_POST["memo"]."' )");
			
 		//echo "INSERT INTO `".WEB_ADMIN_TABPOX."sellreturn` (`sellreturn_no`,`sell_id`,`customer_id`,`employee_id`, `status`,`agencyid`,`payable1`) VALUES ('" .$_POST["sellreturn_no"]."','" .$_POST["sell_id"]."','".$_POST["customer_id"]."' ,'" .$_POST["employee_id"]."','1', '".$_SESSION["currentorgan"]."','0' )";
			
			$id = $this -> dbObj -> Insert_ID();

if($_POST['cproduce_id']!=''&&$_POST['cnumber']!=''){			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."sellreturndetail` (sellreturn_id,`item_id`,number,`value`,amount,agencyid,memo) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cprice"]."','".$_POST["ctotalacount"]."','".$_SESSION["currentorgan"]."','".$_POST["cmemo"]."')");	

			}
			
		$acount = &$this -> dbObj -> GetRow('select sum(amount) as amount from '.WEB_ADMIN_TABPOX.'purchasedetail    where sellreturn_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."sellreturn` SET `realpay` =".$acount['amount']." where sellreturn_id=".$id) ;	
		
			exit("<script>alert('".$info."成功');window.location.href='sellreturn.php?action=upd&updid=".$id."';</script>");
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			//$man=$this -> dbObj -> getone('select username from '.WEB_ADMIN_TABPOX.'user where userid = '.$this->getUid());
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."sellreturn` SET `sell_id` = '".$_POST["sell_id"]."',`sellreturn_no` = '".$_POST["sellreturn_no"]."',`sellreturn_time` = '".$_POST["sellreturn_time"]."',`memo` = '".$_POST["memo"]."', `employee_id` = '".$_POST["employee_id"]."'    WHERE sellreturn_id =".$id);
			//echo "UPDATE `".WEB_ADMIN_TABPOX."sellreturn` SET `sell_id` = '".$_POST["sell_id"]."',`sellreturn_no` = '".$_POST["sellreturn_no"]."',`sellreturn_time` = '".$_POST["sellreturn_time"]."',`memo` = '".$_POST["memo"]."', `employee_id` = '".$_POST["employee_id"]."'    WHERE sellreturn_id =".$id;
			

		//echo $_POST["con_act"];
 if($_POST["con_act"]=='upd'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."sellreturndetail` SET `item_id` = '".$_POST["cproduce_id"]."',`number` = '".$_POST["cnumber"]."',`memo` = '".$_POST["cmemo"]."',amount='".$_POST["ctotalacount"]."',`warehouse_id` = '".$_POST["warehouse_id"]."' WHERE sellreturndetail_id  =".$_POST['proupdid']);		
		//echo "UPDATE `".WEB_ADMIN_TABPOX."sellreturndetail` SET `item_id` = '".$_POST["cproduce_id"]."',`number` = '".$_POST["cnumber"]."',`memo` = '".$_POST["cmemo"]."',amount='".$_POST["ctotalacount"]."',`warehouse_id` = '".$_POST["warehouse_id"]."' WHERE sellreturndetail_id  =".$_POST['proupdid'];
		//echo "UPDATE `".WEB_ADMIN_TABPOX."sellreturndetail` SET `produce_id` = '".$_POST["cproduce_id"]."',`number` = '".$_POST["cnumber"]."',`memo` = '".$_POST["cmemo"]."' WHERE purchasedetail_id  =".$_POST['proupdid'];
		$acount = &$this -> dbObj -> GetOne('select sum(amount) from '.WEB_ADMIN_TABPOX.'purchasedetail    where sellreturn_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."sellreturn` SET `amount` =".$acount." where sellreturn_id=".$id) ;
		exit("<script>alert('".$info."商品成功');window.location.href='sellreturn.php?action=upd&updid=".$id."';</script>");		
		}else{
        if($_POST["con_act"]=='add'&&$_POST["cnumber"]!=''){
		$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."sellreturndetail` (sellreturn_id,`item_id`,number,`value`,amount,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cprice"]."','".$_POST["ctotalacount"]."','".$_SESSION["currentorgan"]."')");	
 //echo "INSERT INTO `".WEB_ADMIN_TABPOX."sellreturndetail` (sellreturn_id,`item_id`,number,`value`,amount,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cprice"]."','".$_POST["ctotalacount"]."','".$_SESSION["currentorgan"]."')";
		
		$info = '添加商品';
		}
		echo 'select sum(totalacount) from '.WEB_ADMIN_TABPOX.'purchasedetail    where sellreturn_id  ='.$id;
		$acount = &$this -> dbObj -> GetOne('select sum(totalacount) from '.WEB_ADMIN_TABPOX.'purchasedetail    where sellreturn_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."sellreturn` SET `acount` =".$acount." where sellreturn_id=".$id) ;
		}
		if($_POST['issave']=='1'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."sellreturn` SET `status`=0  WHERE sellreturn_id =".$id);//0未提交，1已提交2已收部分3全部收完
		
		
		//更新库存
		$this->stockObj=new stock();
		$this->stockObj->purchtostock($id,$_POST["warehouse_id"],$_SESSION["currentorgan"]);
		//$this->stockObj->main($_POST["cproduce_id"],$_POST["cnumber"],$_POST["warehouse_id"],$_SESSION["currentorgan"]);
		exit("<script>alert('保存成功');window.location.href='sellreturn.php';</script>");	
		
		}
		}
//$this -> quit($info.'成功！');
 
		$this -> quit($info.'成功！');

	}

	function intnonull($int){
	if ($int=="")$int=0;
		return $int;
	}
 
	function goModify(){
		$this -> goAppend();
	}
function makeno($Prefix,$agency_no,$table,$column,$number,$id){
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." ORDER BY ".$id." desc limit 1");
//echo "select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." ORDER BY ".$id." desc limit 1";
//echo $this -> dbObj -> GetRow("select ".$column." from ".$table." ORDER BY ".$id." desc limit 1");
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
		exit("<script>alert('$info');history.go(-1);</script>");
	}
	function page($url,$total=0,$psize=30,$pageid=0,$halfPage=5,$is_select=true)
{
	if(empty($psize))
	{
		$psize = 30;
	}
	#[添加链接随机数]
	if(strpos($url,"?") === false)
	{
		$url = $url."?cgrand=cml";
	}
	#[共有页数]
	$totalPage = intval($total/$psize);
	if($total%$psize)
	{
		$totalPage++;#[判断是否存余，如存，则加一
	}
	#[如果分页总数为1或0时，不显示]
	if($totalPage<2)
	{
		return false;
	}
	#[判断分页ID是否存在]
	if(empty($pageid))
	{
		$pageid = 1;
	}
	#[判断如果分页ID超过总页数时]
	if($pageid > $totalPage)
	{
		$pageid = $totalPage;
	}
	#[Html]
	$array_m = 0;
	if($pageid > 0)
	{
		$returnlist[$array_m]["url"] = $url;
		$returnlist[$array_m]["name"] = "首页";
		$returnlist[$array_m]["status"] = 0;
		if($pageid > 1)
		{
			$array_m++;
			$returnlist[$array_m]["url"] = $url."&pageid=".($pageid-1);
			$returnlist[$array_m]["name"] = "上页";
			$returnlist[$array_m]["status"] = 0;
		}
	}
	if($halfPage>0)
	{
		#[添加中间项]
		for($i=$pageid-$halfPage,$i>0 || $i=0,$j=$pageid+$halfPage,$j<$totalPage || $j=$totalPage;$i<$j;$i++)
		{
			$l = $i + 1;
			$array_m++;
			$returnlist[$array_m]["url"] = $url."&pageid=".$l;
			$returnlist[$array_m]["name"] = $l;
			$returnlist[$array_m]["status"] = ($l == $pageid) ? 1 : 0;
		}
	}
	if($is_select)
	{
		if($halfPage <1)
		{
			$halfPage = 5;
		}
		#[添加select里的中间项]
		for($i=$pageid-$halfPage*3,$i>0 || $i=0,$j=$pageid+$halfPage*3,$j<$totalPage || $j=$totalPage;$i<$j;$i++)
		{
			$l = $i + 1;
			$select_option_msg = "<option value='".$l."'";
			if($l == $pageid)
			{
				$select_option_msg .= " selected";
			}
			$select_option_msg .= ">".$l."</option>";
			$select_option[] = $select_option_msg;
		}
	}
	#[添加尾项]
	if($pageid < $totalPage)
	{
		$array_m++;
		$returnlist[$array_m]["url"] = $url."&pageid=".($pageid+1);
		$returnlist[$array_m]["name"] = "下页";
		$returnlist[$array_m]["status"] = 0;
	}
	$array_m++;
	if($pageid != $totalPage)
	{
		$returnlist[$array_m]["url"] = $url."&pageid=".$totalPage;
		$returnlist[$array_m]["name"] = "尾页";
		$returnlist[$array_m]["status"] = 0;
	}
	#[组织样式]
	$msg = "<table class='pagelist'><tr><td class='n'>".$total."/".$psize."</td>";
	foreach($returnlist AS $key=>$value)
	{
		if($value["status"])
		{
			$msg .= "<td class='m'>".$value["name"]."</td>";
		}
		else
		{
			$msg .= "<td class='n'><a href='".$value["url"]."'>".$value["name"]."</a></td>";
		}
	}
	if($is_select)
	{
		$msg .= "<td><select onchange=\"tourl('".$url."&pageid='+this.value)\">".implode("",$select_option)."</option></select></td>";
	}
	$msg .= "</tr></table>";
	unset($returnlist);
	return $msg;
    }
	
}
$main = new Pagecustomer();
$main -> Main();
?>
  