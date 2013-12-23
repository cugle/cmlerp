<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/stock.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/prodaybooks.cls.php');
class Pagecustomer extends admin {
	var $stockObj = null;
    function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='printbill')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> printbill();
        }else if(isset($_GET['action']) && $_GET['action']=='editorder'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> editorder();			
		}else if(isset($_GET['action']) && $_GET['action']=='audit'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> audit();			
		}else{
            parent::Main();
        }
    }
function audit(){	 

//改变库存，生成流水帐。
		$this -> dbObj -> Execute("START TRANSACTION");//事务开始。
		$this->prodaybooksObj=new prodaybooks();//商品流水账
		$res= $this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."moveproduce` SET `status`=4  WHERE moveproduce_id =".$_GET['id']);	
  		$agencyid=$_SESSION["currentorgan"];
		//更新库存
		$moveproducedata= $this -> dbObj -> GetRow("select * from `".WEB_ADMIN_TABPOX."moveproduce`  WHERE moveproduce_id =".$_GET['id']);	
  
		$this->stockObj=new stock();
		$fromwarehouse=$moveproducedata['fromwarehouse'];
		$towarehouse=$moveproducedata['towarehouse'];
 		//$moveproducedata=$this -> dbObj -> GetRow("SELECT * FROM `".WEB_ADMIN_TABPOX."moveproduce`  WHERE moveproduce_id =".$_GET['id']);
		$inrs=$this -> dbObj -> Execute("SELECT * FROM `".WEB_ADMIN_TABPOX."moveproducedetail`  WHERE moveproduce_id =".$_GET['id']);	 
		while ($inrrs = &$inrs -> FetchRow()){					   
				
		$number=$this -> dbObj -> getone("SELECT number FROM `".WEB_ADMIN_TABPOX."stock` where  `warehouse_id`=".$inrrs['warehouse_id']." and produce_id=".$inrrs['produce_id']." and agencyid=".$inrrs["agencyid"]);	
		//$meanprice=$this->dbObj->GetOne('SELECT stockunitprice FROM '.WEB_ADMIN_TABPOX.'stock WHERE warehouse_id='.$inrrs["warehouse_id"].' and produce_id='.$inrrs["produce_id"].' and agencyid="'.$inrrs["agencyid"].'"'); 
		//echo 'SELECT stockunitprice FROM '.WEB_ADMIN_TABPOX.'stock WHERE warehouse_id='.$inrrs["warehouse_id"].' and produce_id='.$inrrs["produce_id"].' and agencyid="'.$inrrs["agencyid"].'"';
		//$warehouse_id = $inrrs["warehouse_id"];

		//if($inrrs['addorreduce']==0){$inrrs['number']=-$inrrs['number'];}
		
		$res1=$this->stockObj->main($inrrs["produce_id"],$inrrs['number'],$inrrs['account'],$towarehouse,$agencyid);//增加目的库存
		
		$res3=$this->prodaybooksObj->main($inrrs['produce_id'],$moveproducedata["towarehouse"],$inrrs['number'],$inrrs['account'],9,$_GET['id'],$moveproducedata['man'],"调拨单; ".$moveproducedata['memo'],$inrrs["agencyid"]);//商品流水账
		
		 $res11=$this->stockObj->main($inrrs["produce_id"],-$inrrs['number'],-$inrrs['account'],$fromwarehouse,$agencyid);//减少源仓库库存
		 $res33=$this->prodaybooksObj->main($inrrs['produce_id'],$moveproducedata["fromwarehouse"],-$inrrs['number'],-$inrrs['account'],9,$_GET['id'],$moveproducedata['man'],"调拨单; ".$moveproducedata['memo'],$inrrs["agencyid"]);//商品流水账
		 
 		 }
		
		
		if($res&&$res1&&$res3&&$res11&&$res33){
		
		$this -> dbObj -> Execute("COMMIT");
			 exit("<script>alert('提交成功');window.location.href='moveproduce.php';</script>");	
		}else{
		$this -> dbObj -> Execute("ROLLBACK");
		 exit("<script>alert('发生错误，提交失败，数据已经回滚。');window.location.href='moveproduce.php';</script>");
		} 
 
 
	}	
function  printbill(){

		$t = new Template('../template/stock');
		$t -> set_file('f','moveproduce_detail_print.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	
 

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'moveproduce WHERE moveproduce_id = '.$updid);
			$t -> set_var('storageidlist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$data['warehouse_id']));
			//$t -> set_var('order_no',$this->dbObj->GetOne('SELECT order_no FROM '.WEB_ADMIN_TABPOX.'order WHERE order_id = '.$data[order_id]));

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
		$t -> set_var('cmoveproduce_price',"");
		$t -> set_var('cdiscount',"");
		$t -> set_var('cmemo',"");	
		$t -> set_var('cnumber',"");	
		$t -> set_var('cviceunitnumber',"");	
		$t -> set_var('cviceunit',"");	
		$t -> set_var('proupdid',"");	
		$t -> set_var('ctotalaccount',"");	
		//$acount = &$this -> dbObj -> GetOne('select count(*) as acount from '.WEB_ADMIN_TABPOX.'moveproducedetail  where moveproduce_id  ='.$updid);
		$t -> set_var('recordcount',$acount);			
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
			
			$inrs2 = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'employee   where employee_id  ='.$data['employee_id']);
	     	while ($inrrs2 = &$inrs2 -> FetchRow()) {
				$t -> set_var($inrrs2);
			}			
 				
		//设置消耗品列表
			$sumacount=0;
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'moveproducedetail  where moveproduce_id  ='.$updid);
	     	while ($inrrs = &$inrs -> FetchRow()) {
			    $sumacount=$sumacount+$inrrs['totalaccount'];
				$t -> set_var($inrrs);
				$t -> set_var('numbers',$inrrs['number']);
				$data1=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrrs["produce_id"]);
				$t -> set_var($data1);
				
				$t -> set_var('unit_name',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$data1["standardunit"]));
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();			
			$t -> set_var('sumacount',$sumacount);
		// 修改消耗品
		 
		    if($_GET['cmoveproducedetail_id']!=''){
			//$t -> set_var('cs');
		   $t -> set_var('caction','upd');	
            
			$inrs2 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'moveproducedetail  where moveproducedetail_id ='.$_GET['cmoveproducedetail_id']);
			//$t -> set_var($inrs);
			
		    $t -> set_var('cupdid',$inrs2['produce_id']);	
			$t -> set_var('proupdid',$_GET['cmoveproducedetail_id']);	
			
			$t -> set_var('cnumber',$inrs2['number']);
			$t -> set_var('ctotalaccount',$inrs2['totalaccount']);
			$inrs3 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrs2['produce_id']);
			$t -> set_var('cproduce_no',$inrs3['produce_no']);
			$t -> set_var('cproduce_name',$inrs3['produce_name']);
			
			$t -> set_var('cstandardunit',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$inrs3['standardunit']));
			$t -> set_var('cproduce_id',$inrs3['produce_id']);
			$t -> set_var('ccode',$inrs3['code']);
			$t -> set_var('cprice',$inrs3['price']);
			$t -> set_var('cdiscount',$inrs2['discount']);
			$t -> set_var('cmoveproduce_price',$inrs2['moveproduce_price']);
			$t -> set_var('cmemo',$inrs2['memo']);			
			//$t -> set_var('cviceunit',$inrs3['viceunit']);
			//$t -> set_var('cviceunitnumber',$inrs3['viceunitnumber']);
			//$t -> parse('cs','consumables',true);
			//$inrs2 -> Close();	
			}
			
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));	
		$t -> set_var('memo',$data['memo']);//因为与明细的memo冲突，故放到这里。
	 
		//$t -> set_var('categorynamelist',$this ->selectlist('servicecategory','category_id','category_name',$data['categoryid']));		
		$t -> set_var('warehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse where warehouse_id = '.$data['warehouse_id']));//设置供应商
		$t -> set_var('status', $data['status']);
		//$t -> set_var('ml',"");	
		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
}	
function  addorder(){
	$condition =' order_id='.$_GET['orderid'];
	$sql='select * from '.WEB_ADMIN_TABPOX.'order  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;	
	
	
	$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  order_id DESC  LIMIT 0 ,1");
		     	while ($inrrs = &$inrs -> FetchRow()) {

			$man=$this -> dbObj -> getone('select username from '.WEB_ADMIN_TABPOX.'user where userid = '.$this->getUid());
			//插入进货记录
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."moveproduce` (`moveproduce_no`,`order_id`,`warehouse_id`,`suppliers_id`,`employee_id`,`acount`,`creattime`,`memo`,`moveproduce_time`,`man`,`agencyid`) VALUES ('" .$_GET["moveproduce_no"]."','".$_GET['orderid']."', '".$inrrs["warehouse_id"]."', '" .$inrrs["suppliers_id"]."','".$inrrs["employee_id"]."', '" .$inrrs["acount"]."','".date('Y-m-d',time())."', '".$inrrs["memo"]."', '".date('Y-m-d',time())."','".$man."','".$_SESSION["currentorgan"]."')");
			//echo "INSERT INTO `".WEB_ADMIN_TABPOX."moveproduce` (`moveproduce_no`,`order_id`,`warehouse_id`,`suppliers_id`,`employee_id`,`acount`,`creattime`,`memo`,`agencyid`) VALUES ('" .$_GET["moveproduce_no"]."','".$_GET['orderid']."', '".$inrrs["warehouse_id"]."', '" .$inrrs["suppliers_id"]."','".$inrrs["employee_id"]."', '" .$inrrs["acount"]."','".date('Y-m-d',time())."', '".$inrrs["memo"]."','".$_SESSION["currentorgan"]."')";
			$id = $this -> dbObj -> Insert_ID();
			 
		$sqlstr='select * from '.WEB_ADMIN_TABPOX.'orderdetail  where  agencyid ='.$_SESSION["currentorgan"].' and order_id='.$_GET['orderid'];	
		
		//插入进货明细
		$purchdiscount=$this -> getValue('purchdiscount');
		$inrs1 = &$this -> dbObj -> Execute($sqlstr);
				while ($inrrs1 = &$inrs1 -> FetchRow()) {
					$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."moveproducedetail` (moveproduce_id,produce_id,number,moveproduce_price,price,discount,totalaccount,memo,warehouse_id,agencyid) VALUES ('".$id."','".$inrrs1["produce_id"]."','".$inrrs1["number"]."','".$inrrs1["orderprice"]*$purchdiscount."','".$inrrs1["orderprice"]."','".$purchdiscount."','".$inrrs1["totalaccount"]."','".$inrrs1["memo"]."','".$inrrs1[warehouse_id]."','".$_SESSION["currentorgan"]."')");
				//	echo "INSERT INTO `".WEB_ADMIN_TABPOX."moveproducedetail` (moveproduce_id,produce_id,number,moveproduce_price,totalaccount,agencyid) VALUES ('".$id."','".$inrrs1["produce_id"]."','".$inrrs1["number"]."','".$inrrs1["orderprice"]."','".$inrrs1["totalaccount"]."','".$_SESSION["currentorgan"]."')";
					
					}
			
				}
	   exit("<script>window.location.href='moveproduce.php?action=upd&updid=".$id."';</script>");
}
	function disp(){
		//定义模板
		$t = new Template('../template/stock');
		$t -> set_file('f','moveproduce.html');
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'moveproduce  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql="select * from ".WEB_ADMIN_TABPOX."moveproduce s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  s.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'moveproduce  where  agencyid ='.$_SESSION["currentorgan"].' and status in(0,1) ';
			 
			}
			
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  moveproduce_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);	
			 
			$count=$result->RecordCount();
			
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));			
	    	$t -> set_var('recordcount',$count);	
			
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				
				$t -> set_var('fwarehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse  where warehouse_id ='.$inrrs["fromwarehouse"]));
				$t -> set_var('twarehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse  where warehouse_id ='.$inrrs["towarehouse"]));
				
				
				$t -> set_var('brand_name',$this -> dbObj -> getone('select brand_name from '.WEB_ADMIN_TABPOX.'brand  where brand_id ='.$inrrs["brandid"]));
				$t -> set_var('employee_name',$this -> dbObj -> getone('select employee_name  from '.WEB_ADMIN_TABPOX.'employee  where employee_id ='.$inrrs["employee_id"]));
				$t -> set_var('order_no',$this -> dbObj -> getone('select order_no from '.WEB_ADMIN_TABPOX.'order  where order_id ='.$inrrs["order_id"]));
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['moveproduce_id']));
		        
				$t -> set_var('edit',$this -> getupdStr('',$inrrs['moveproduce_id']));	
				$t -> set_var('printbill','<A href="?action=printbill&updid='.$inrrs['moveproduce_id'].'">打印</A>');					
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

		$t = new Template('../template/stock');
		$t -> set_file('f','moveproduce_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
			
		//$t -> set_var('moveproduce_no',"");
		$Prefix='MV';
		$agency_no=$_SESSION["agency_no"].date('ymd',time());
		$table=WEB_ADMIN_TABPOX.'moveproduce';
		$column='moveproduce_no';
		$number=4;
		$id='moveproduce_id';	
		
		$t -> set_var('moveproduce_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));	
		$t -> set_var('suppliers_no',"");	
		$t -> set_var('createtime',date('Y-m-d H:i:s',time()));	
		$t -> set_var('suppliers_no',"");
		$t -> set_var('suppliers_id',"");
		$t -> set_var('suppliers_name',"");
		$t -> set_var('forwhat',"");	
		$t -> set_var('employee_name',"");	
		$t -> set_var('employee_id',"");	
		$t -> set_var('date',date('Y-m-d',time()));	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");
		$t -> set_var('comdisplay',"none");		
		$t -> set_var('memo',"");

		$t -> set_var('man',$this -> dbObj -> getone('select B.employee_name from '.WEB_ADMIN_TABPOX.'user A INNER JOIN '.WEB_ADMIN_TABPOX.'employee B ON A.employee_id=B.employee_id  where A.userid = '.$this->getUid()));
		
		$t -> set_var('recordcount',"");	
		$t -> set_var('cstandardunit',"");	
		$t -> set_var('cstocknumber',"");	
		
		$t -> set_var('cproduce_no',"");
		$t -> set_var('cproduce_name',"");
		$t -> set_var('cproduce_id',"");	
		$t -> set_var('ccode',"");	
		$t -> set_var('caccount',"");	
		$t -> set_var('cnumber','');	
		$t -> set_var('cprice',"");	
		$t -> set_var('cmoveproduceprice',"");
		$t -> set_var('cdiscount',"");
		
		$t -> set_var('cviceunit','');
		$t -> set_var('cmemo','');
		$t -> set_var('ml',"");	
		$t -> set_var('fromstorageidlist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',''));	
		$t -> set_var('tostorageidlist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',''));	
		$t -> set_var('addprodisplay',"display:none");	
		$t -> set_var('userid',$this->getUid());		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'moveproduce WHERE moveproduce_id = '.$updid);
			$t -> set_var('fromstorageidlist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$data['fromwarehouse']));
			$t -> set_var('tostorageidlist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$data['towarehouse']));
			//$t -> set_var('order_no',$this->dbObj->GetOne('SELECT order_no FROM '.WEB_ADMIN_TABPOX.'order WHERE order_id = '.$data[order_id]));
			$t -> set_var('cstocknumber',"");	
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
		$t -> set_var('cmoveproduceprice',"");
		$t -> set_var('cdiscount',"");
		$t -> set_var('cmemo',"");	
		$t -> set_var('cnumber',"");	
		$t -> set_var('cviceunitnumber',"");	
		$t -> set_var('cviceunit',"");	
		$t -> set_var('proupdid',"");	
		$t -> set_var('caccount',"");	
		
		//$acount = &$this -> dbObj -> GetOne('select count(*) as acount from '.WEB_ADMIN_TABPOX.'moveproducedetail  where moveproduce_id  ='.$updid);
		$t -> set_var('recordcount',$acount);			
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
			
			$inrs2 = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'employee   where employee_id  ='.$data['employee_id']);
	     	while ($inrrs2 = &$inrs2 -> FetchRow()) {
				$t -> set_var($inrrs2);
			}			
 				
		//设置消耗品列表
			$sumacount=0;
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'moveproducedetail  where moveproduce_id  ='.$updid);
	     	while ($inrrs = &$inrs -> FetchRow()) {
			    $sumacount=$sumacount+$inrrs['account'];
				$t -> set_var($inrrs);
				$t -> set_var('numbers',$inrrs['number']);
				if($inrrs['addorreduce']==1){$t -> set_var('addorreduce','+');}else{$t -> set_var('addorreduce','-');}
				$data1=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrrs["produce_id"]);
				$t -> set_var($data1);
				
				$t -> set_var('unit_name',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$data1["standardunit"]));
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();			
			$t -> set_var('totalaccount',$sumacount);
		// 修改消耗品
		
		    if($_GET['cmoveproducedetail_id']!=''){
			//$t -> set_var('cs');
		   $t -> set_var('caction','upd');	
            
			$inrs2 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'moveproducedetail  where moveproducedetail_id ='.$_GET['cmoveproducedetail_id']);
			//$t -> set_var($inrs);
			
		    $t -> set_var('cupdid',$inrs2['produce_id']);	
			$t -> set_var('proupdid',$_GET['cmoveproducedetail_id']);	
			
			$t -> set_var('cnumber',$inrs2['number']);
			$t -> set_var('ctotalaccount',$inrs2['totalaccount']);
			$inrs3 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrs2['produce_id']);
			$t -> set_var('cproduce_no',$inrs3['produce_no']);
			$t -> set_var('cproduce_name',$inrs3['produce_name']);
			
			$t -> set_var('cstandardunit',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$inrs3['standardunit']));
			$t -> set_var('cproduce_id',$inrs3['produce_id']);
			$t -> set_var('ccode',$inrs3['code']);
			$t -> set_var('cprice',$inrs3['price']);
			$t -> set_var('cdiscount',$inrs2['discount']);
			$t -> set_var('cmoveproduce_price',$inrs2['moveproduce_price']);
			$t -> set_var('cmemo',$inrs2['memo']);			
			$t -> set_var('cmoveproduceprice',$inrs2['moveproduceprice']);
			$t -> set_var('caccount',$inrs2['account']);
			//$t -> parse('cs','consumables',true);
			//$inrs2 -> Close();	
			}
			
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));	
		$t -> set_var('memo',$data['memo']);//因为与明细的memo冲突，故放到这里。
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
	  if($_GET['moveproducedetail_id']==''){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'moveproduce WHERE moveproduce_id in('.$delid.')');
		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'moveproducedetail WHERE moveproduce_id in('.$delid.')');
		
		}else{

		 $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'moveproducedetail WHERE moveproducedetail_id in('.$_GET['moveproducedetail_id'].')');
		
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
			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."moveproduce` ( `moveproduce_no`, `fromwarehouse`,`towarehouse`, `date`, `man`, `employee_id`,  `memo`,  `agencyid`) VALUES ('" .$_POST["moveproduce_no"]."', '".$_POST["fromwarehouse"]."','".$_POST["towarehouse"]."', '".$_POST["date"]."','".$_POST["man"]."', '" .$_POST["employee_id"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."')");
			//echo "INSERT INTO `".WEB_ADMIN_TABPOX."moveproduce` ( `moveproduce_no`, `fromwarehouse`,`towarehouse`, `date`, `man`, `employee_id`,  `memo`,  `agencyid`) VALUES ('" .$_POST["moveproduce_no"]."', '".$_POST["fromwarehouse"]."','".$_POST["towarehouse"]."', '".$_POST["date"]."','".$_POST["man"]."', '" .$_POST["employee_id"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."')";
			$id = $this -> dbObj -> Insert_ID();

if($_POST['subsave']==1){		

				$meanprice=$this->dbObj->GetOne('SELECT stockunitprice FROM '.WEB_ADMIN_TABPOX.'stock WHERE warehouse_id='.$_POST["fromwarehouse"].' and produce_id='.$_POST["cproduce_id"].' and agencyid="'.$_SESSION["currentorgan"].'"'); 
				$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."moveproducedetail` (`moveproduce_id`, `produce_id`, `number`, `price`,`moveproduceprice`, `account`, `agencyid`) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cprice"]."','".$meanprice."','".$meanprice*$_POST["cnumber"]."','".$_SESSION["currentorgan"]."')");	
			 	//echo "INSERT INTO `".WEB_ADMIN_TABPOX."moveproducedetail` (`moveproduce_id`, `produce_id`, `number`, `price`,`moveproduceprice`, `account`, `agencyid`) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cprice"]."','".$meanprice."','".$meanprice*$_POST["cnumber"]."','".$_SESSION["currentorgan"]."')";

			}
			
		$totalaccount = &$this -> dbObj -> GetOne('select sum(acount) from '.WEB_ADMIN_TABPOX.'moveproducedetail    where moveproduce_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."moveproduce` SET `totalaccount` =".$totalaccount." where moveproduce_id=".$id) ;	
		
		exit("<script>alert('".$info."成功');window.location.href='moveproduce.php?action=upd&updid=".$id."';</script>");
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$man=$this -> dbObj -> getone('select username from '.WEB_ADMIN_TABPOX.'user where userid = '.$this->getUid());
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."moveproduce` SET  `moveproduce_no` = '".$_POST["moveproduce_no"]."',`date` = '".$_POST["date"]."',`towarehouse` = '".$_POST["towarehouse"]."',`memo` = '".$_POST["memo"]."',`fromwarehouse` = '".$_POST["fromwarehouse"]."',`employee_id` = '".$_POST["employee_id"]."',`man`='".$man."'  WHERE moveproduce_id =".$id);
			
			 //echo "UPDATE `".WEB_ADMIN_TABPOX."moveproduce` SET  `moveproduce_no` = '".$_POST["moveproduce_no"]."',`date` = '".$_POST["date"]."',`towarehouse` = '".$_POST["towarehouse"]."',`memo` = '".$_POST["memo"]."',`fromwarehouse` = '".$_POST["fromwarehouse"]."',`employee_id` = '".$_POST["employee_id"]."',`man`='".$man."'  WHERE moveproduce_id =".$id;
 if($_POST["con_act"]=='upd'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."moveproducedetail` SET `produce_id` = '".$_POST["cproduce_id"]."',`number` = '".$_POST["cnumber"]."',`price` = '".$_POST["cprice"]."',account='".$_POST["caccount"]."' WHERE moveproducedetail_id  =".$_POST['proupdid']);		
		//echo "UPDATE `".WEB_ADMIN_TABPOX."moveproducedetail` SET `produce_id` = '".$_POST["cproduce_id"]."',`number` = '".$_POST["cnumber"]."',`memo` = '".$_POST["cmemo"]."' WHERE moveproducedetail_id  =".$_POST['proupdid'];
		$totalaccount = &$this -> dbObj -> GetOne('select sum(account) from '.WEB_ADMIN_TABPOX.'moveproducedetail    where moveproduce_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."moveproduce` SET `totalaccount` =".$totalaccount." where moveproduce_id=".$id) ;
		
		exit("<script>alert('".$info."商品成功');window.location.href='moveproduce.php?action=upd&updid=".$id."';</script>");		
		
		}else{
        if($_POST["con_act"]=='add'&&$_POST['subsave']==1){
		$meanprice=$this->dbObj->GetOne('SELECT stockunitprice FROM '.WEB_ADMIN_TABPOX.'stock WHERE warehouse_id='.$_POST["fromwarehouse"].' and produce_id='.$_POST["cproduce_id"].' and agencyid="'.$_SESSION["currentorgan"].'"'); 
		
		$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."moveproducedetail` (`moveproduce_id`, `produce_id`, `number`, `price`,`moveproduceprice`, `account`, `agencyid`) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cprice"]."','".$meanprice."','".$meanprice*$_POST["cnumber"]."','".$_SESSION["currentorgan"]."')");	
		 //echo "INSERT INTO `".WEB_ADMIN_TABPOX."moveproducedetail` (`moveproduce_id`, `produce_id`, `number`, `price`,`moveproduceprice`, `account`, `agencyid`) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cprice"]."','".$meanprice."','".$meanprice*$_POST["cnumber"]."','".$_SESSION["currentorgan"]."')";
		//echo "INSERT INTO `".WEB_ADMIN_TABPOX."moveproducedetail` (moveproduce_id,produce_id,number,moveproduce_price,discount,price,totalaccount,warehouse_id,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cmoveproduce_price"]."','".$_POST["cdiscount"]."','".$_POST["cprice"]."','".$_POST["ctotalaccount"]."','".$_POST["warehouse_id"]."','".$_SESSION["currentorgan"]."')";
		//$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."serviceconsume` SET `produce_id` = '".$_POST["produce_id"]."',`std_consumption` = '".$_POST["std_consumption"]."',`memo` = '".$_POST["memo"]."',`moveproduce` = '".$_POST["moveproduce"]."' WHERE moveproduce_id =".$id);
		//echo "INSERT INTO `".WEB_ADMIN_TABPOX."consumables` (moveproduce_id,produce_id,std_consumption,memo,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cstd_consumption"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')";
		
		$info = '添加商品';
		}
		//echo 'select sum(totalaccount) from '.WEB_ADMIN_TABPOX.'moveproducedetail    where moveproduce_id  ='.$id;
		$totalaccount = &$this -> dbObj -> GetOne('select sum(account) from '.WEB_ADMIN_TABPOX.'moveproducedetail    where moveproduce_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."moveproduce` SET `totalaccount` =".$totalaccount." where moveproduce_id=".$id) ;
		//echo "UPDATE `".WEB_ADMIN_TABPOX."moveproduce` SET `totalaccount` =".$totalaccount." where moveproduce_id=".$id;
		}
		if($_POST['issave']=='1'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."moveproduce` SET `status`=1  WHERE moveproduce_id =".$id);//0未提交，1已提交2已收部分3全部收完
	 
		

		exit("<script>alert('保存成功');window.location.href='moveproduce.php';</script>");	
		
		}
		}
//$this -> quit($info.'成功！');
 
		 $this -> quit($info.'成功！');

	}
	function acount($acounttypeid=0,$value=0,$sellid=0,$agencyid=0){
		//划账户
		$value=-$value;
		if($value!=0){
		//$acountid=$this->dbObj->GetOne('SELECT `account_id` FROM '.WEB_ADMIN_TABPOX.'account WHERE `type`='.$acounttypeid.' and  agencyid='.agencyid);	
		$acountdata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'account WHERE `type`='.$acounttypeid.' and  agencyid='.$agencyid);
		
		$acountid=$acountdata['account_id'];
		$lastbalance=$acountdata['balance'];
		$nowbalance=$acountdata['balance']+$value;
		//echo 'UPDATE '.WEB_ADMIN_TABPOX.'account   SET  balance =balance + '.$value.'  WHERE   agencyid='.$agencyid.' and account_id ='.$acountid;
		$this->dbObj->Execute('UPDATE '.WEB_ADMIN_TABPOX.'account   SET  balance =balance + '.$value.'  WHERE   agencyid='.$agencyid.' and account_id ='.$acountid);	
		 
		$type=4;//进货1
		$memo='进货支出';
		//账户流水账
		$this->dbObj->Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'accounthistory (`account_id`, `value`,  `type`, `sellid`, `lastbalance`, `nowbalance`, `memo`, `agencyid`)value('.$acountid.','.$value.','.$type.','.$sellid.','.$lastbalance.','.$nowbalance.',"'.$memo.'",'.$agencyid.')') ;//帐户流水账
		// echo 'INSERT INTO '.WEB_ADMIN_TABPOX.'accounthistory (`account_id`, `value`,  `type`, `sellid`, `lastbalance`, `nowbalance`, `memo`, `agencyid`)value('.$acountid.','.$value.','.$type.','.$sellid.','.$lastbalance.','.$nowbalance.',"'.$memo.'",'.$agencyid.')';
	}
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
  