﻿<?
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
	 
        if(isset($_GET['action']) && $_GET['action']=='print'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> print1();			
		}else if(isset($_GET['action']) && $_GET['action']=='viewbill'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> viewbill();			
		}else if(isset($_GET['action']) && $_GET['action']=='recoil'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> recoil();			
		}else if(isset($_GET['action']) && $_GET['action']=='audit'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> audit();			
		}else if(isset($_GET['action']) && $_GET['action']=='turnback'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> turnback();			
		}else{
            parent::Main();
        }
    }
	
function turnback(){
	 $this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."lossregister` SET `status`=0  WHERE lossregister_id =".$_GET['id']);//5已经审核4表示被反冲3反冲2未审核1未提交
	 exit("<script>alert('撤回成功');window.location.href='lossregisterhistory.php';</script>");	
	}		
function audit(){
	$employee_id=$this -> dbObj -> GetOne('select employee_id from '.WEB_ADMIN_TABPOX.'user   where userid  ='.$this -> getUid());
	echo $employee_id;
	 $this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."lossregister` SET `status`=5 ,auditor_id=".$employee_id." WHERE lossregister_id =".$_GET['id']);//5已经审核4表示被反冲3反冲2未审核1未提交
	 exit("<script>alert('审核成功');window.location.href='lossregisterhistory.php';</script>");	
	}	
function recoil(){
	$this -> dbObj -> Execute("START TRANSACTION");//事务开始。
	$this->prodaybooksObj=new prodaybooks();//商品流水账
	$condition =' lossregister_id='.$_GET['id'];
	$sql='select * from '.WEB_ADMIN_TABPOX.'lossregister  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;	
	
	$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  lossregister_id DESC  LIMIT 0 ,1");
		     	while ($inrrs = &$inrs -> FetchRow()) {

			//$man=$this -> dbObj -> getone('select username from '.WEB_ADMIN_TABPOX.'user where userid = '.$this->getUid());
			//插入进货记录
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."lossregister` (`lossregister_no`,`warehouse_id`,`employee_id`,`acount`,`creattime`,`memo`,`lossregister_time`,`status`,`man`,`agencyid`) VALUES ('" .$inrrs["lossregister_no"]."', '".$inrrs["warehouse_id"]."', '".$inrrs["employee_id"]."', '" .(-$inrrs["acount"])."','".$inrrs['creattime']."', '".$inrrs["memo"]."', '".$inrrs['lossregister_time']."','3','".$inrrs['man']."','".$inrrs["agencyid"]."')");
			 // echo "INSERT INTO `".WEB_ADMIN_TABPOX."lossregister` (`lossregister_no`,`warehouse_id`,`employee_id`,`acount`,`creattime`,`memo`,`lossregister_time`,`status`,`man`,`agencyid`) VALUES ('" .$inrrs["lossregister_no"]."', '".$inrrs["warehouse_id"]."', '".$inrrs["employee_id"]."', '" .(-$inrrs["acount"])."','".$inrrs['creattime']."', '".$inrrs["memo"]."', '".$inrrs['lossregister_time']."','3','".$inrrs['man']."','".$inrrs["agencyid"]."')";
			$id = $this -> dbObj -> Insert_ID();
		$warehouse_id=$inrrs["warehouse_id"];	
		$sqlstr='select * from '.WEB_ADMIN_TABPOX.'lossregisterdetail  where  agencyid ='.$_SESSION["currentorgan"].' and lossregister_id='.$_GET['id'];	
		
		//插入进货明细
		$inrs1 = &$this -> dbObj -> Execute($sqlstr);
				while ($inrrs1 = &$inrs1 -> FetchRow()) {
					$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."lossregisterdetail` (lossregister_id,produce_id,number,lossregisterprice,totalacount,memo,agencyid) VALUES ('".$id."','".$inrrs1["produce_id"]."','".(-$inrrs1["number"])."','".$inrrs1["lossregisterprice"]."','".(-$inrrs1["totalacount"])."','".$inrrs1["memo"]."','".$inrrs1["agencyid"]."')");
				 	// echo "INSERT INTO `".WEB_ADMIN_TABPOX."lossregisterdetail` (lossregister_id,produce_id,number,lossregisterprice,totalacount,memo,agencyid) VALUES ('".$id."','".$inrrs1["produce_id"]."','".(-$inrrs1["number"])."','".$inrrs1["lossregisterprice"]."','".$inrrs1["totalacount"]."','".$inrrs1["memo"]."','".$inrrs1["agencyid"]."')";
				 $res3=$this->prodaybooksObj->main($inrrs1['produce_id'],$inrrs["warehouse_id"],$inrrs1['number'],$inrrs1['totalacount'],7,$id,$inrrs['man'],"被反冲报损单; ".$inrrs['memo'],$_SESSION["currentorgan"]);//商品流水账		
					}
			
				}
	   $this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."lossregister` SET `status`=2  WHERE lossregister_id =".$_GET['id']);//4表示被反冲3反冲2未审核1未提交	
	  	//更新库存
		$this->stockObj=new stock();
		$res1=$this->stockObj->lossregistertostock($id,$warehouse_id,$_SESSION["currentorgan"]); 	   
	   
		if($res1&&$res3){
		
		$this -> dbObj -> Execute("COMMIT");
		  exit("<script>alert('反冲成功');window.location.href='lossregisterhistory.php';</script>");	
		}else{
		$this -> dbObj -> Execute("ROLLBACK");
		 exit("<script>alert('发生错误，审核失败，数据已经回滚。');window.location.href='lossregisterhistory.php';</script>");
		}
	}
function  print1(){
		//定义模板
		$t = new Template('../template/stock');
		$t -> set_file('f','lossregisterhistory_detail_print.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	


			$updid = $_GET['id'] + 0 ;	
			
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'lossregister WHERE lossregister_id = '.$updid);
			$t -> set_var('mmemo',$data['memo']);//因为与明细的memo冲突，故放到这里。
			$t -> set_var('storageidlist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$data['warehouse_id']));
			//$t -> set_var('order_no',$this->dbObj->GetOne('SELECT order_no FROM '.WEB_ADMIN_TABPOX.'order WHERE order_id = '.$data[order_id]));
	       $status_name=array("撤回修改","未提交","<font color=green>未审核</font>","<font color=red>红字冲销</font>","<font color=red>被红字冲销</font>","已审核");	
			$t -> set_var('status_name',$status_name[$data['status']]);
			
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
		
		$acount = &$this -> dbObj -> GetOne('select count(*) from '.WEB_ADMIN_TABPOX.'lossregisterdetail  where lossregister_id  ='.$updid);
		$t -> set_var('recordcount',$acount);			
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
			
			
			$t -> set_var('auditor_name',$this -> dbObj -> GetOne('select employee_name from '.WEB_ADMIN_TABPOX.'employee   where employee_id  ='.$data['auditor_id']));
			$t -> set_var('employee_name',$this -> dbObj -> GetOne('select employee_name from '.WEB_ADMIN_TABPOX.'employee   where employee_id  ='.$data['employee_id']));
			$t -> set_var('auditor',$this -> dbObj -> GetOne('select employee_name from '.WEB_ADMIN_TABPOX.'employee   where employee_id  ='.$data['superior']));
			
			$t -> set_var('warehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse where warehouse_id = '.$data['warehouse_id']));//设置供应商
			


			//$inrs1 = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'suppliers  where suppliers_id  ='.$data['suppliers_id']);
	     //	while ($inrrs1 = &$inrs1 -> FetchRow()) {
			//	$t -> set_var($inrrs1);
		//	}
		//	$inrs1 -> Close();						
		//设置退货明细列表
			
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'lossregisterdetail  where lossregister_id  ='.$updid);
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('numbers',$inrrs['number']);
				$data1=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrrs["produce_id"]);
				$t -> set_var($data1);
				$t -> set_var('vmemo',$inrrs['memo']);
				$t -> set_var('unit_name',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$data1["standardunit"]));
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();			


			
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));	
		
		
		//$t -> set_var('categorynamelist',$this ->selectlist('servicecategory','category_id','category_name',$data['categoryid']));		
		
		$t -> set_var('cmemo',$data['memo']);//因为与明细的memo冲突，故放到这里。
		//$t -> set_var('ml',"");	
		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
}
function  viewbill(){
		//定义模板
		$t = new Template('../template/stock');
		$t -> set_file('f','lossregisterhistory_detail_viewbill.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	


			$updid = $_GET['updid'] + 0 ;	
			
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'lossregister WHERE lossregister_id = '.$updid);
			$t -> set_var('mmemo',$data['memo']);//因为与明细的memo冲突，故放到这里。
			$t -> set_var('storageidlist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$data['warehouse_id']));
			//$t -> set_var('order_no',$this->dbObj->GetOne('SELECT order_no FROM '.WEB_ADMIN_TABPOX.'order WHERE order_id = '.$data[order_id]));
	       $status_name=array("撤回修改","未提交","<font color=green>未审核</font>","<font color=red>红字冲销</font>","<font color=red>被红字冲销</font>","已审核");	
			$t -> set_var('status_name',$status_name[$data['status']]);
			
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
		
		$acount = &$this -> dbObj -> GetOne('select count(*) from '.WEB_ADMIN_TABPOX.'lossregisterdetail  where lossregister_id  ='.$updid);
		$t -> set_var('recordcount',$acount);			
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
			
			
			$t -> set_var('auditor_name',$this -> dbObj -> GetOne('select employee_name from '.WEB_ADMIN_TABPOX.'employee   where employee_id  ='.$data['auditor_id']));
			$t -> set_var('employee_name',$this -> dbObj -> GetOne('select employee_name from '.WEB_ADMIN_TABPOX.'employee   where employee_id  ='.$data['employee_id']));
			$t -> set_var('auditor',$this -> dbObj -> GetOne('select employee_name from '.WEB_ADMIN_TABPOX.'employee   where employee_id  ='.$data['superior']));
			
			$t -> set_var('warehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse where warehouse_id = '.$data['warehouse_id']));//设置供应商
			


			//$inrs1 = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'suppliers  where suppliers_id  ='.$data['suppliers_id']);
	     //	while ($inrrs1 = &$inrs1 -> FetchRow()) {
			//	$t -> set_var($inrrs1);
		//	}
		//	$inrs1 -> Close();						
		//设置退货明细列表
			
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'lossregisterdetail  where lossregister_id  ='.$updid);
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('numbers',$inrrs['number']);
				$data1=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrrs["produce_id"]);
				$t -> set_var($data1);
				$t -> set_var('vmemo',$inrrs['memo']);
				$t -> set_var('unit_name',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$data1["standardunit"]));
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();			


			
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));	
		
		
		//$t -> set_var('categorynamelist',$this ->selectlist('servicecategory','category_id','category_name',$data['categoryid']));		
		
		$t -> set_var('cmemo',$data['memo']);//因为与明细的memo冲突，故放到这里。
		//$t -> set_var('ml',"");	
		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
}
function disp(){
		//定义模板
		$t = new Template('../template/stock');
		$t -> set_file('f','lossregisterhistory.html');
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'lossregister where  agencyid ='.$_SESSION["currentorgan"].' and status>1 and '.$condition ;

			}else if($ftable<>''){
			$sql="select * from ".WEB_ADMIN_TABPOX."lossregister s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.".$ftable."_id =f.".$ftable."_id  where f.".$category." like '%".$keywords."%'  and s.status>1 and  s.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'lossregister WHERE  agencyid ='.$_SESSION["currentorgan"].' and status>1 ';
			 
			}

			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  lossregister_no desc  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));			
	    	$t -> set_var('recordcount',$count);	
			$statuslistname=array("未审核","红字冲销","被红字冲销","已审核");
			$liststr='';
			
			for($i=0;$i<count($statuslistname);$i++){
				$liststr=$liststr.'<option value="'.($i+2).'">'.$statuslistname[$i].'</option>';
				}
			$t -> set_var('statuslist',$liststr);
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
	            $status_name=array("未完成","已完成","<font color=red>红字冲销</font>","<font color=red>被红字冲销</font>","<font color=blue>已提交</font>","<font color=green>已审核</font>");
				$t -> set_var('status_name',$status_name[$inrrs['status']]);				
				$t -> set_var('warehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse  where warehouse_id ='.$inrrs["warehouse_id"]));
				
				
				$t -> set_var('brand_name',$this -> dbObj -> getone('select brand_name from '.WEB_ADMIN_TABPOX.'brand  where brand_id ='.$inrrs["brandid"]));
				$t -> set_var('employee_name',$this -> dbObj -> getone('select employee_name  from '.WEB_ADMIN_TABPOX.'employee  where employee_id ='.$inrrs["employee_id"]));
				$t -> set_var('order_no',$this -> dbObj -> getone('select order_no from '.WEB_ADMIN_TABPOX.'order  where order_id ='.$inrrs["order_id"]));
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['lossregister_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['lossregister_id']));	
				$t -> set_var('view','<a href="?action=upd&updid='.$inrrs['lossregister_id'].'">查看</a>');
				$t -> set_var('print','<a href="?action=print&id='.$inrrs['lossregister_id'].'" target="_blank">打印</a>');				
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	
		$this->getSupper()?$t -> set_var('canaudit',''):$t -> set_var('canaudit','none');	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	
 
	
	
	function goDispAppend(){

		$t = new Template('../template/stock');
		$t -> set_file('f','lossregisterhistory_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
			
		//$t -> set_var('lossregister_no',"");
		$Prefix='PC';
		$agency_no=$_SESSION["agency_no"].date('ymd',time());
		$table=WEB_ADMIN_TABPOX.'lossregister';
		$column='lossregister_no';
		$number=3;
		$id='lossregister_id';	
		
		$t -> set_var('lossregister_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));	
		$t -> set_var('suppliers_no',"");	
		$t -> set_var('creattime',date('Y-m-d H:i:s',time()));	
		$t -> set_var('suppliers_no',"");
		$t -> set_var('suppliers_id',"");
		$t -> set_var('suppliers_name',"");
		$t -> set_var('order_no',"");	
		$t -> set_var('employee_name',"");	
		$t -> set_var('employee_id',"");	
		$t -> set_var('lossregister_time',date('Y-m-d',time()));	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");
		$t -> set_var('comdisplay',"none");		
		$t -> set_var('mmemo',"");

		$t -> set_var('man',$this -> dbObj -> getone('select B.employee_name from '.WEB_ADMIN_TABPOX.'user A INNER JOIN '.WEB_ADMIN_TABPOX.'employee B ON A.employee_id=B.employee_id  where A.userid = '.$this->getUid()));

		$t -> set_var('cproduce_no',"");
		$t -> set_var('cproduce_name',"");
		$t -> set_var('cproduce_id',"");	
		$t -> set_var('ccode',"");	
		$t -> set_var('ctotalacount',"");	
		$t -> set_var('recordcount',"");
		$t -> set_var('acount',"");
		$t -> set_var('cnumber','');	
		$t -> set_var('cprice',"");	
		$t -> set_var('cviceunit','');
		$t -> set_var('cstandardunit','');
		$t -> set_var('cmemo','');
		$t -> set_var('ml',"");	
		$t -> set_var('storageidlist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',''));	
		$t -> set_var('addprodisplay',"display:none");	
		$t -> set_var('userid',$this->getUid());		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'lossregister WHERE lossregister_id = '.$updid);
			$t -> set_var('warehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse where warehouse_id ='.$data["warehouse_id"]));
			$t -> set_var('storageidlist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$data['warehouse_id']));
		    $t -> set_var('mmemo',$data['memo']);//因为与明细的memo冲突，故放到这里。			
			//$t -> set_var('order_no',$this->dbObj->GetOne('SELECT order_no FROM '.WEB_ADMIN_TABPOX.'order WHERE order_id = '.$data[order_id]));

			if($data['status']<>4){
			$t -> set_var('auditdisabled','disabled="disabled"');
			}else{
			$t -> set_var('auditdisabled','');

			}
			 
			if($data['status']<4 or $data['status']>4){
				

			$t -> set_var('recoildisabled','disabled="disabled"');

			}else{
			 $t -> set_var('recoildisabled','');
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
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
			
			$inrs2 = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'employee   where employee_id  ='.$data['employee_id']);
	     	while ($inrrs2 = &$inrs2 -> FetchRow()) {
				$t -> set_var($inrrs2);
			}			
					
		//设置消耗品列表
			
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'lossregisterdetail  where lossregister_id  ='.$updid);
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
		 
		    if($_GET['clossregisterdetail_id']!=''){
			//$t -> set_var('cs');
		   $t -> set_var('caction','upd');	
            
			$inrs2 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'lossregisterdetail  where lossregisterdetail_id ='.$_GET['clossregisterdetail_id']);
			
			//$t -> set_var($inrs);
			
		    $t -> set_var('cupdid',$inrs2['produce_id']);	
			$t -> set_var('proupdid',$_GET['clossregisterdetail_id']);	
			
			$t -> set_var('cnumber',$inrs2['number']);
			$t -> set_var('ctotalacount',$inrs2['totalacount']);
			$inrs3 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrs2['produce_id']);
			$t -> set_var('cproduce_no',$inrs3['produce_no']);
			$t -> set_var('cproduce_name',$inrs3['produce_name']);
			$t -> set_var('cstandardunit',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$inrs3['standardunit']));
			$t -> set_var('cproduce_id',$inrs3['produce_id']);
			$t -> set_var('ccode',$inrs3['code']);
			$t -> set_var('cprice',$inrs3['price']);
			$t -> set_var('cmemo',$inrs2['memo']);
			
			
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
	  if($_GET['lossregisterdetail_id']==''){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'lossregister WHERE lossregister_id in('.$delid.')');
		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'lossregisterdetail WHERE lossregister_id in('.$delid.')');
		
		}else{

		 $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'lossregisterdetail WHERE lossregisterdetail_id in('.$_GET['lossregisterdetail_id'].')');
		
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
			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."lossregister` (`lossregister_no`,`lossregister_time`,`warehouse_id`,`employee_id`,`memo`,`agencyid`,`creattime`,`man`) VALUES ('" .$_POST["lossregister_no"]."','".$_POST["lossregister_time"]."', '".$_POST["warehouse_id"]."', '".$_POST["employee_id"]."', '" .$_POST["mmemo"]."','".$_SESSION["currentorgan"]."','".$_POST["creattime"]."', '".$_POST["man"]."')");
			//echo "INSERT INTO `".WEB_ADMIN_TABPOX."lossregister` (`lossregister_no`,`lossregister_time`,`warehouse_id`,`employee_id`,`memo`,`agencyid`,`creattime`,`man`) VALUES ('" .$_POST["lossregister_no"]."','".$_POST["lossregister_time"]."', '".$_POST["warehouse_id"]."', '".$_POST["employee_id"]."', '" .$_POST["cmemo"]."','".$_SESSION["currentorgan"]."','".$_POST["creattime"]."', '".$_POST["man"]."')";
			$id = $this -> dbObj -> Insert_ID();

if($_POST['cproduce_id']!=''&&$_POST['cnumber']!=''){			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."lossregisterdetail` (lossregister_id,produce_id,number,memo,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')");	
			echo "INSERT INTO `".WEB_ADMIN_TABPOX."lossregisterdetail` (lossregister_id,produce_id,number,memo,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')";
			}
			
		$acount = &$this -> dbObj -> GetOne('select sum(totalacount) from '.WEB_ADMIN_TABPOX.'lossregisterdetail    where lossregister_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."lossregister` SET `acount` =".$acount." where lossregister_id=".$id) ;	
		
			exit("<script>alert('增加成功');window.location.href='lossregister.php?action=upd&updid=".$id."';</script>");
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$man=$this -> dbObj -> getone('select username from '.WEB_ADMIN_TABPOX.'user where userid = '.$this->getUid());
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."lossregister` SET `lossregister_no` = '".$_POST["lossregister_no"]."',`lossregister_time` = '".$_POST["lossregister_time"]."',`memo` = '".$_POST["mmemo"]."',`warehouse_id` = '".$_POST["warehouse_id"]."',`employee_id` = '".$_POST["employee_id"]."',`man`='".$man."'  WHERE lossregister_id =".$id);
			echo "UPDATE `".WEB_ADMIN_TABPOX."lossregister` SET `lossregister_no` = '".$_POST["lossregister_no"]."',`lossregister_time` = '".$_POST["lossregister_time"]."',`memo` = '".$_POST["mmemo"]."',`warehouse_id` = '".$_POST["warehouse_id"]."',`employee_id` = '".$_POST["employee_id"]."',`man`='".$man."'  WHERE lossregister_id =".$id;

		//echo $_POST["con_act"];
 if($_POST["con_act"]=='upd'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."lossregisterdetail` SET `produce_id` = '".$_POST["cproduce_id"]."',`number` = '".$_POST["cnumber"]."',`memo` = '".$_POST["cmemo"]."'  WHERE lossregisterdetail_id  =".$_POST['proupdid']);	
		echo "UPDATE `".WEB_ADMIN_TABPOX."lossregisterdetail` SET `produce_id` = '".$_POST["cproduce_id"]."',`number` = '".$_POST["cnumber"]."',`memo` = '".$_POST["cmemo"]."' WHERE lossregisterdetail_id  =".$_POST['proupdid'];
		//echo "UPDATE `".WEB_ADMIN_TABPOX."lossregisterdetail` SET `produce_id` = '".$_POST["cproduce_id"]."',`number` = '".$_POST["cnumber"]."',`memo` = '".$_POST["cmemo"]."' WHERE lossregisterdetail_id  =".$_POST['proupdid'];
		//$acount = &$this -> dbObj -> GetOne('select sum(totalacount) from '.WEB_ADMIN_TABPOX.'lossregisterdetail    where lossregister_id  ='.$id);
		//$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."lossregister` SET `acount` =".$acount." where lossregister_id=".$id) ;
		exit("<script>alert('".$info."商品成功');window.location.href='lossregister.php?action=upd&updid=".$id."';</script>");		
		}else{
        if($_POST["con_act"]=='add'&&$_POST["cnumber"]!=''){
		$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."lossregisterdetail` (lossregister_id,produce_id,number,memo,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')");	
		//$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."lossregisterdetail` (lossregister_id,produce_id,number,memo,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')");
		echo "INSERT INTO `".WEB_ADMIN_TABPOX."lossregisterdetail` (lossregister_id,produce_id,number,memo,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')";
		
		$info = '添加商品';
		}
		echo 'select sum(totalacount) from '.WEB_ADMIN_TABPOX.'lossregisterdetail    where lossregister_id  ='.$id;
		$acount = &$this -> dbObj -> GetOne('select sum(totalacount) from '.WEB_ADMIN_TABPOX.'lossregisterdetail    where lossregister_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."lossregister` SET `acount` =".$acount." where lossregister_id=".$id) ;
		}
		if($_POST['issave']=='1'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."lossregister` SET `status`=1  WHERE lossregister_id =".$id);//0未提交，1已提交2已收部分3全部收完
		
		
		//更新库存
		$this->stockObj=new stock();
		$this->stockObj->purchtostock($id,$_POST["warehouse_id"],$_SESSION["currentorgan"]);
		//$this->stockObj->main($_POST["cproduce_id"],$_POST["cnumber"],$_POST["warehouse_id"],$_SESSION["currentorgan"]);
		exit("<script>alert('提交成功');window.location.href='lossregister.php';</script>");	
		
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
  