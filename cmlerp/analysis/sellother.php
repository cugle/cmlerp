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
        if(isset($_GET['action']) && $_GET['action']=='addorder')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> addorder();
        }else if(isset($_GET['action']) && $_GET['action']=='print'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> print1();			
		}else if(isset($_GET['action']) && $_GET['action']=='recoil'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> recoil();			
		}
		else if(isset($_GET['action']) && $_GET['action']=='sellotherexport'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> sellotherexport();			
		}else{
            parent::Main();
        }
    }
	function sellotherexport(){
		//定义模板
		$t = new Template('../template/analysis');
		$t -> set_file('f','sellotherexport.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]
		$t -> set_var('bgdate',date("Y-m-d",strtotime("$m-1 month")));
		
		$t -> set_var('enddate',date('Y-m-d'));
		$t -> set_var('agencyid',$_SESSION["currentorgan"]);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
}	
function recoil(){
	$condition =' purchase_id='.$_GET['id'];
	$sql='select * from '.WEB_ADMIN_TABPOX.'purchase  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;	
	
	$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  purchase_id DESC  LIMIT 0 ,1");
		     	while ($inrrs = &$inrs -> FetchRow()) {

			//$man=$this -> dbObj -> getone('select username from '.WEB_ADMIN_TABPOX.'user where userid = '.$this->getUid());
			//插入进货记录
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."purchase` (`purchase_no`,`order_id`,`warehouse_id`,`suppliers_id`,`employee_id`,`acount`,`creattime`,`memo`,`purchase_time`,`status`,`man`,`agencyid`) VALUES ('" .$inrrs["purchase_no"]."', '".$inrrs["order_id"]."','".$inrrs["warehouse_id"]."', '" .$inrrs["suppliers_id"]."','".$inrrs["employee_id"]."', '" .(-$inrrs["acount"])."','".$inrrs['creattime']."', '".$inrrs["memo"]."', '".$inrrs['purchase_time']."','2','".$inrrs['man']."','".$inrrs["agencyid"]."')");
			 //echo "INSERT INTO `".WEB_ADMIN_TABPOX."purchase` (`purchase_no`,`warehouse_id`,`suppliers_id`,`employee_id`,`acount`,`creattime`,`memo`,`purchase_time`,`status`,`man`,`agencyid`) VALUES ('" .$inrrs["purchase_no"]."', '".$inrrs["warehouse_id"]."', '" .$inrrs["suppliers_id"]."','".$inrrs["employee_id"]."', '" .(-$inrrs["acount"])."','".$inrrs['creattime']."', '".$inrrs["memo"]."', '".$inrrs['purchase_time']."','2','".$inrrs['man']."','".$inrrs["agencyid"]."')";
			$warehouse_id=$inrrs["warehouse_id"];
			$id = $this -> dbObj -> Insert_ID();
			
		$sqlstr='select * from '.WEB_ADMIN_TABPOX.'purchasedetail  where  agencyid ='.$_SESSION["currentorgan"].' and purchase_id='.$_GET['id'];	
		
		//插入进货明细
		$inrs1 = &$this -> dbObj -> Execute($sqlstr);
				while ($inrrs1 = &$inrs1 -> FetchRow()) {
					$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."purchasedetail` (purchase_id,produce_id,number,purchase_price,totalacount,memo,agencyid) VALUES ('".$id."','".$inrrs1["produce_id"]."','".(-$inrrs1["number"])."','".$inrrs1["purchase_price"]."','".(-$inrrs1["totalacount"])."','".$inrrs1["memo"]."','".$inrrs1["agencyid"]."')");
				 	// echo "INSERT INTO `".WEB_ADMIN_TABPOX."purchasedetail` (purchase_id,produce_id,number,purchase_price,totalacount,memo,agencyid) VALUES ('".$id."','".$inrrs1["produce_id"]."','".(-$inrrs1["number"])."','".$inrrs1["purchase_price"]."','".(-$inrrs1["totalacount"])."','".$inrrs1["memo"]."','".$inrrs1["agencyid"]."')";
					
					}
			
				}
	   $this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."purchase` SET `status`=3  WHERE purchase_id =".$_GET['id']);//4表示被反冲3反冲2正常1未提交	
	  	//更新库存
		$this->stockObj=new stock();
		$this->stockObj->purchtostock($id,$warehouse_id,$_SESSION["currentorgan"]); 
	   exit("<script>alert('反冲成功');window.location.href='purchasehistory.php';</script>");	
	}	
 
	function disp(){
		//定义模板
		
		$t = new Template('../template/analysis');
		$t -> set_file('f','sellother.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		
		$bgdate=$_GET["bgdate"]." 00:00:00";
		$enddate=$_GET["enddate"]." 23:59:59";		
		$timecondition=' creattime  between "'.$bgdate.'" and "'.$enddate.'"';
		$condition='';
		if($_SESSION["hiddenred"]==1){
			$hiddenredstr=" and status<>2 and  status<>3";
			$hiddenredstr1=" and  A.status<>2 and  A.status<>3";
			$t -> set_var('hiddenredchecked','checked');
		}else{
			$hiddenredstr="";
			$t -> set_var('hiddenredchecked','');}
			
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "'.$keywords.'"';}else{$condition=$category.' like "'.$keywords.'"';}
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'sell A INNER JOIN '.WEB_ADMIN_TABPOX.'sellotherdetail B ON A.sell_id=B.sell_id  where  A.agencyid ='.$_SESSION["currentorgan"].' and status<>0 and '.$condition.$hiddenredstr1 ;

			}else if($ftable<>''){

			$sql="select A.status as fstatus ,F.".$ftable."_id,A.*,B.* from ( ".WEB_ADMIN_TABPOX.'sell A INNER JOIN '.WEB_ADMIN_TABPOX.'sellotherdetail B ON A.sell_id=B.sell_id)  INNER JOIN '.WEB_ADMIN_TABPOX.$ftable.' F on A.'.$ftable.'_id =F.'.$ftable.'_id  where F.'.$category." like '".$keywords."' and A.status<>0 and A.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			

			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'sell A INNER JOIN '.WEB_ADMIN_TABPOX.'sellotherdetail B ON A.sell_id=B.sell_id  where  A.status<>0  AND A.agencyid ='.$_SESSION["currentorgan"].$hiddenredstr1;
			 
			}
			if($_GET["bgdate"]<>'' && $_GET["enddate"]<>''){$sql=$sql.' and '.$timecondition;}
			
 
		
		
/*		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "'.$keywords.'"';}else{$condition=$category.' like "'.$keywords.'"';}
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'sellotherdetail  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql="select f.status as fstatus ,f.customer_id,s.* from ".WEB_ADMIN_TABPOX."sellotherdetail s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.sell_id =f.sell_id  where f.".$category." like '".$keywords."' and f.agencyid =".$_SESSION["currentorgan"] ;
			
			 
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'sellotherdetail  where  agencyid ='.$_SESSION["currentorgan"];
			 
			}*/
			
			 
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  A.sell_no DESC,A.sell_id DESC LIMIT ".$offset." , ".$psize);
			 
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));			
	    	$t -> set_var('recordcount',$count);	
			 while ($inrrs = &$inrs -> FetchRow()) {
				
				$t -> set_var($inrrs);
				 $customerid=$this -> dbObj -> getone('select customer_id from '.WEB_ADMIN_TABPOX.'sell  where sell_id ='.$inrrs["sell_id"]) ;
				$customername=$this -> dbObj -> getone('select customer_name from '.WEB_ADMIN_TABPOX.'customer  where customer_id ='.$customerid);
				$customername=$customername<>''?$customername:'散客';
				$t -> set_var('customer_name',$customername);
				$t -> set_var('customer_id',$customerid);
			//$status_name=array("<font color=red>未完成</font>","已完成","<font color=red>红字冲销</font>","<font color=red>被红字冲销</font>");
			//$t -> set_var('status_name',$status_name[$inrrs['status']]);

				$t -> set_var('view','<a href="#" onclick=viewbill('.$inrrs['sell_id'].');>查看</a>'); 
				$t -> set_var('print','<a href="?action=print&updid='.$inrrs['sell_id'].'" target="_blank">打印</a>');
				$t -> set_var('sell_no',$this -> dbObj -> getone('select sell_no from '.WEB_ADMIN_TABPOX.'sell  where sell_id ='.$inrrs["sell_id"]));
				$t -> set_var('beauty_name',$this -> dbObj -> getone('select employee_name  from '.WEB_ADMIN_TABPOX.'employee  where employee_id ='.$inrrs["beauty_id"]));
				$t -> set_var('creattime',$this -> dbObj -> getone('select creattime from '.WEB_ADMIN_TABPOX.'sell  where sell_id ='.$inrrs["sell_id"]));
				$t -> set_var('marketingcard_name',$this -> dbObj -> getone('select marketingcard_name  from '.WEB_ADMIN_TABPOX.'marketingcard  where marketingcard_id ='.$inrrs["cardid"])); 
				//$t -> set_var('itemname',$this -> dbObj -> getone('select marketingcardtype_name  from '.WEB_ADMIN_TABPOX.'marketingcardtype  where marketingcardtype_id ='.$inrrs["cardtype"]));
				$itemnamelist=array('预收款',"储值卡充值","定金","还款","赠送手工","其他","预付产品款","赠送产品款","赠送购卡款");		
				$cardtypelist=array('款项',"其他");	
				$itemname=$itemnamelist[$inrrs['item_id']];
				$cardtype=$cardtypelist[$inrrs['cardtype']];
				$t -> set_var('itemname',$itemname);				
				
				$t -> set_var('produce_name',$this -> dbObj -> getone('select produce_name  from '.WEB_ADMIN_TABPOX.'produce  where produce_id ='.$inrrs["item_id"]));
				$itemtype_name=array('单项服务',"购买产品","消费卡项","购买卡项","消费券项","款项");	
				 
				$t -> set_var('itemtype_name',$itemtype_name[$inrrs["item_type"]]);
				$status_name=array("<font color=blue>未完成</font>","已完成","<font color=red>红字冲销</font>","<font color=red>被红字冲销</font>","<font color=green>已提交</font>","<font color=#AAAAAA>已审核</font>");
			$t -> set_var('status_name',$status_name[$inrrs['status']]);	
			
				$employeeid=explode(";",$inrrs["employee_id"]);
				$employee_str='';
				for ($i=0;$i<count($employeeid);$i++){
				$employee_name=$this -> dbObj -> getone('select employee_name  from '.WEB_ADMIN_TABPOX.'employee  where employee_id ='.$employeeid[$i]);	
				$employee_str=$employee_str.'<a href="?category=beauty_id&keywords='.$employeeid[$i].'"> '.$employee_name.' </a>';
				}
				$t -> set_var('employee_name',$employee_str);			
				
				$t -> set_var('giving',$inrrs['discount']==0?"赠送":"");
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['purchase_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['purchase_id']));		
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$t -> set_var('bgdate',$_GET['bgdate']?$_GET['bgdate']:date('Y-m-d'));
		$t -> set_var('enddate',$_GET['enddate']?$_GET['enddate']:date('Y-m-d'));			
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	
		$this->getSupper()?$t -> set_var('canexport',''):$t -> set_var('canexport','none');	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	
 
function print1(){
		//定义模板
 		$sellid=$_GET['updid'];
		$t = new Template('../template/analysis');
		$t -> set_file('f','sell_detailprint.html');
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
  
 		$sql1="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail  where  sell_id=".$sellid;
		$sql2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail   where  sell_id=".$sellid;
		$sql3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  where  sell_id=".$sellid;
		$sql4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  where  sell_id=".$sellid;
		$sql=$sql1." union ".$sql2." union ".$sql3." union ".$sql4;
		 $table_name=array('services',"produce","services","marketingcard","services");	
		  $itemtype_name=array('单项服务',"购买产品","消费卡项","购买卡项","消费券项");	
		  
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
				}else{
					$t -> set_var('itemtype_name','test1');
			 }
			$t -> set_var('memo','');
			
			$t -> set_var('type_name',$itemtype_name[$inrrs['item_type']]);
			$t -> set_var('itemname',$itemdata[$table_name[$inrrs['item_type']].'_name']);
			$t -> set_var('itemno',$itemdata[$table_name[$inrrs['item_type']].'_no']);
			$t -> set_var('number',$inrrs['number']);
			$t -> parse('ml','mainlist',true);
		}
 
 

 
 
  		$t -> set_var('totalacount',$tempacount);
		$t -> set_var('membercard_no','');
 		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');	
	
	}
	
	function goDispAppend(){

		$t = new Template('../template/analysis');
		$t -> set_file('f','sell_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
			
		//$t -> set_var('purchase_no',"");
		$Prefix='PC';
		$agency_no=$_SESSION["agency_no"].date('ymd',time());
		$table=WEB_ADMIN_TABPOX.'purchase';
		$column='purchase_no';
		$number=3;
		$id='purchase_id';	
		
		$t -> set_var('purchase_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));	
		$t -> set_var('suppliers_no',"");	
		$t -> set_var('creattime',date('Y-m-d H:i:s',time()));	
		$t -> set_var('suppliers_no',"");
		$t -> set_var('suppliers_id',"");
		$t -> set_var('suppliers_name',"");

		$t -> set_var('employee_name',"");	
		$t -> set_var('employee_id',"");	
		$t -> set_var('purchase_time',date('Y-m-d',time()));	
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
			
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'sell WHERE sell_id = '.$updid);
			$t -> set_var('warehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse where warehouse_id = '.$data['warehouse_id']));
			$t -> set_var('storageidlist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$data['warehouse_id']));
			$t -> set_var('order_no',$this->dbObj->GetOne('SELECT order_no FROM '.WEB_ADMIN_TABPOX.'order WHERE order_id = '.$data[order_id]));
			if($data['status']==2){
			$t -> set_var('recoildisabled','disabled="disabled"');
			}else if($data['status']==3){
			$t -> set_var('recoildisabled','disabled="disabled"');
			}
			
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
		$acount = &$this -> dbObj -> GetOne('select count(*) as acount from '.WEB_ADMIN_TABPOX.'sellotherdetail  where purchase_id  ='.$updid);
		$t -> set_var('recordcount',$acount);	
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
			
			$inrs2 = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'employee   where employee_id  ='.$data['employee_id']);
	     	while ($inrrs2 = &$inrs2 -> FetchRow()) {
				$t -> set_var($inrrs2);
			}			
//设置供应商

			$inrs1 = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'suppliers  where suppliers_id  ='.$data['suppliers_id']);
	     	while ($inrrs1 = &$inrs1 -> FetchRow()) {
				$t -> set_var($inrrs1);
			}
			$inrs1 -> Close();						
		//设置消耗品列表
			
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'sellotherdetail  where sell_id  ='.$updid);
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('numbers',$inrrs['number']);
				$data1=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrrs["produce_id"]);
				$t -> set_var($data1);
				
				$t -> set_var('unit_name',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$data1["standardunit"]));
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();			

		// 修改消耗品
		 
		    if($_GET['cpurchasedetail_id']!=''){
			//$t -> set_var('cs');
		   $t -> set_var('caction','upd');	
            
			$inrs2 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'purchasedetail  where purchdetail_id ='.$_GET['cpurchasedetail_id']);
			//$t -> set_var($inrs);
			
		    $t -> set_var('cupdid',$inrs2['produce_id']);	
			$t -> set_var('proupdid',$_GET['cpurchasedetail_id']);	
			
			$t -> set_var('cnumber',$inrs2['number']);
			$t -> set_var('ctotalacount',$inrs2['totalacount']);
			$inrs3 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrs2['produce_id']);
			$t -> set_var('cproduce_no',$inrs3['produce_no']);
			$t -> set_var('cproduce_name',$inrs3['produce_name']);
			$t -> set_var('cstandardunit',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$inrs3['standardunit']));
			$t -> set_var('cproduce_id',$inrs3['produce_id']);
			$t -> set_var('ccode',$inrs3['code']);
			$t -> set_var('cprice',$inrs3['price']);
						
			//$t -> set_var('cviceunit',$inrs3['viceunit']);
			//$t -> set_var('cviceunitnumber',$inrs3['viceunitnumber']);
			//$t -> parse('cs','consumables',true);
			//$inrs2 -> Close();	
			}
	
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));	

		}
		//$t -> set_var('categorynamelist',$this ->selectlist('servicecategory','category_id','category_name',$data['categoryid']));		
		
		
		//$t -> set_var('ml',"");	
		$t -> set_var('memo',$data['memo']);//因为与明细的memo冲突，故放到这里。
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
 
		$delid = $_GET[DELETE.'id'] ;
       
		//$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'sellotherdetail WHERE sell_id in('.$delid.')');
		//$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'selldservicesetail WHERE sell_id in('.$delid.')');
		//$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'sellconsumedetail WHERE sell_id in('.$delid.')'); 
		//$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'sellcarddetail WHERE sell_id in('.$delid.')');
		//echo 'DELETE FROM '.WEB_ADMIN_TABPOX.'sellconsumedetail WHERE sellotherdetail_id in('.$delid.')';
 		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'sellotherdetail WHERE sellotherdetail_id in('.$delid.')');
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
			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."purchase` (`purchase_no`,`purchase_time`,`warehouse_id`,`suppliers_id`,`employee_id`,`memo`,`agencyid`,`creattime`,`man`) VALUES ('" .$_POST["purchase_no"]."','".$_POST["purchase_time"]."', '".$_POST["warehouse_id"]."', '" .$_POST["suppliers_id"]."','".$_POST["employee_id"]."', '" .$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["creattime"]."', '".$_POST["man"]."')");
			$id = $this -> dbObj -> Insert_ID();

if($_POST['cproduce_id']!=''&&$_POST['cnumber']!=''){			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."purchasedetail` (purchase_id,produce_id,number,purchaseprice,totalacount,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cprice"]."','".$_POST["ctotalacount"]."','".$_SESSION["currentorgan"]."')");	

			}
			
			
			exit("<script>alert('$info');window.location.href='purchase.php?action=upd&updid=".$id."';</script>");
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$man=$this -> dbObj -> getone('select username from '.WEB_ADMIN_TABPOX.'user where userid = '.$this->getUid());
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."purchase` SET `order_id` = '".$_POST["order_id"]."',`purchase_no` = '".$_POST["purchase_no"]."',`purchase_time` = '".$_POST["purchase_time"]."',`memo` = '".$_POST["memo"]."',`warehouse_id` = '".$_POST["warehouse_id"]."',`suppliers_id` = '".$_POST["suppliers_id"]."',`employee_id` = '".$_POST["employee_id"]."',`man`='".$man."'  WHERE purchase_id =".$id);
			

		
 if($_POST["con_act"]=='upd'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."purchasedetail` SET `produce_id` = '".$_POST["cproduce_id"]."',`number` = '".$_POST["cnumber"]."',`memo` = '".$_POST["cmemo"]."',totalacount='".$_POST["ctotalacount"]."' WHERE purchdetail_id  =".$_POST['proupdid']);		
		//echo "UPDATE `".WEB_ADMIN_TABPOX."purchasedetail` SET `produce_id` = '".$_POST["cproduce_id"]."',`number` = '".$_POST["cnumber"]."',`memo` = '".$_POST["cmemo"]."' WHERE purchasedetail_id  =".$_POST['proupdid'];
		
		exit("<script>alert('".$info."商品成功');window.location.href='purchase.php?action=upd&updid=".$id."';</script>");		
		}else{
        if($_POST["con_act"]=='add'&&$_POST["cnumber"]!=''){
		$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."purchasedetail` (purchase_id,produce_id,number,purchase_price,totalacount,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cprice"]."','".$_POST["ctotalacount"]."','".$_SESSION["currentorgan"]."')");	
		//$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."serviceconsume` SET `produce_id` = '".$_POST["produce_id"]."',`std_consumption` = '".$_POST["std_consumption"]."',`memo` = '".$_POST["memo"]."',`purchase` = '".$_POST["purchase"]."' WHERE purchase_id =".$id);
		echo "INSERT INTO `".WEB_ADMIN_TABPOX."consumables` (purchase_id,produce_id,std_consumption,memo,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cstd_consumption"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')";
		$info = '添加商品';
		}
		}
	
		}
//$this -> quit($info.'成功！');
 
		$this -> quit($info.'成功！');

	}

	function intnonull($int){
	if ($int=="")$int=0;
		return $int;
	}
	function roomtype($type='0'){
		$arr="";
		if($type=='1'){
		$arr="<option value='1' selected>一组</option><option value='2' >二组</option>";
		}else if($type=='2')
		{$arr="<option value='1' >一组</option><option value='1' selected>二组</option>";
		}else
		{
		$arr="<option value='1' >一组</option><option value='1'>二组</option>";
		}
		return $arr;
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
  