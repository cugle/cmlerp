<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/transfervoucherhistory.cls.php');
class Pagecustomer extends admin {
	
 function Main()
    {    
        if(isset($_POST['action']) && $_POST['action']=='audit'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> audit();			
		}else if(isset($_GET['action']) && $_GET['action']=='print'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> printbill();			
		}else if(isset($_GET['action']) && $_GET['action']=='viewbill'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> viewbill();			
		}else{
            parent::Main();
        }
    }
function audit(){	
		$this -> dbObj -> Execute("START TRANSACTION");//事务开始。
		$this->trhObj=new transfervoucherhistory();
		$auditor=$this -> dbObj -> GetOne("select employee_id from`".WEB_ADMIN_TABPOX."user`  WHERE `userid`=".$this->getUid());
		$res=$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."transfervoucher` SET `status`=5,auditor = ".$auditor." WHERE transfervoucher_id =".$_POST['updid']);
		$transfervoucher=$this -> dbObj -> GetRow("select * from`".WEB_ADMIN_TABPOX."transfervoucher`  WHERE `transfervoucher_id`=".$_POST['updid']);
	 	//echo "UPDATE `".WEB_ADMIN_TABPOX."transfervoucher` SET `status`=5  WHERE transfervoucher_id =".$_POST['updid'];
		//未完成,"已完成"红字冲销","被红字冲销","已提交","已审核"
		$date=$transfervoucher['date'];
        //插入凭证历史。
		$inrs=$this -> dbObj -> Execute("select * from `".WEB_ADMIN_TABPOX."transfervoucherdetail`   WHERE transfervoucher_id =".$_POST['updid']);
		while ($inrrs = &$inrs -> FetchRow()) {
			$accounttitle_id=$inrrs['accounttitle_id'];
			$objecttype_id=$inrrs['objecttype_id'];
			$objectid=$inrrs['objectid'];
			$lend=$inrrs['lend'];
			$loan=$inrrs['loan'];
			$loan=$inrrs['loan'];
			$memo=$inrrs['memo'];
			$res2=$this->trhObj->main($accounttitle_id,$objecttype_id,$objectid,$lend,$loan,$memo,$date);
		}

		if($res&&$res2){
		
		$this -> dbObj -> Execute("COMMIT");
		 exit("<script>alert('提交成功');window.location.href='transfervoucher.php';</script>");	
		}else{
		$this -> dbObj -> Execute("ROLLBACK");
		exit("<script>alert('发生错误，审核失败，数据已经回滚。');window.location.href='transfervoucher.php';</script>");	
		}	
 
	}
	function viewbill(){

		$t = new Template('../template/finace');
		$t -> set_file('f','transfervoucher_detail_viewbill.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	

			$updid = $_GET['id'] + 0 ;	
			 
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'transfervoucher WHERE transfervoucher_id = '.$updid);
			
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
		$t -> set_var('clend',"");	
		$t -> set_var('cmemo',"");	
		$t -> set_var('cloan',"");	
		$t -> set_var('cobject_name',"");	
		$t -> set_var('cobjectid',"");	
		$t -> set_var('employee_id',"");	
		$t -> set_var('employee_name',"");	
			
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
		 
		$t -> set_var('ctransfervoucherprice',0);
		
 					
		//设置消耗品列表
			
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'transfervoucherdetail  where transfervoucher_id  ='.$updid);
			$totallend=0;
			$totalloan=0;
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$totallend=$totallend+$inrrs['lend'];
				$totalloan=$totalloan+$inrrs['loan'];
				$t -> set_var('numbers',$inrrs['number']);
				
				 
				$data1=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'accounttitle   where accounttitle_id ='.$inrrs["accounttitle_id"]);
				$t -> set_var($data1);
				 
				 $objecttype=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id ='.$inrrs["objecttype_id"]);
				 $t -> set_var('objecttype_name',$objecttype['objecttype_name']);
				$object=$this -> dbObj -> getrow('select *  from '.WEB_ADMIN_TABPOX.$objecttype["objecttypetable"].'  where '.$objecttype["objecttypetable"].'_id ='.$inrrs["objectid"]);
				 
				$t -> set_var('object_name',$object[$objecttype["objecttypetable"].'_name']);
				$t -> set_var('memo',$inrrs['memo']);
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();			
			$acount = &$this -> dbObj -> GetOne('select count(*) as acount from '.WEB_ADMIN_TABPOX.'transfervoucherdetail  where transfervoucher_id  ='.$updid);
			$t -> set_var('totallend',$totallend);	
			$t -> set_var('totalloan',$totalloan);
			$t -> set_var('recordcount',$acount);

			
			 
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));	



	
		//$t -> set_var('categorynamelist',$this ->selectlist('servicecategory','category_id','category_name',$data['categoryid']));		
		
		
		//$t -> set_var('ml',"");	
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}	
	function printbill(){

		$t = new Template('../template/finace');
		$t -> set_file('f','transfervoucher_detail_bill.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	

			$updid = $_GET['id'] + 0 ;	
			 
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'transfervoucher WHERE transfervoucher_id = '.$updid);
			
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
		$t -> set_var('clend',"");	
		$t -> set_var('cmemo',"");	
		$t -> set_var('cloan',"");	
		$t -> set_var('cobject_name',"");	
		$t -> set_var('cobjectid',"");	
		$t -> set_var('employee_id',"");	
		$t -> set_var('employee_name',"");	
			
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
		 
		$t -> set_var('ctransfervoucherprice',0);
		
 					
		//设置消耗品列表
			
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'transfervoucherdetail  where transfervoucher_id  ='.$updid);
			$totallend=0;
			$totalloan=0;
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$totallend=$totallend+$inrrs['lend'];
				$totalloan=$totalloan+$inrrs['loan'];
				$t -> set_var('numbers',$inrrs['number']);
				
				 
				$data1=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'accounttitle   where accounttitle_id ='.$inrrs["accounttitle_id"]);
				$t -> set_var($data1);
				 
				 $objecttype=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id ='.$inrrs["objecttype_id"]);
				 $t -> set_var('objecttype_name',$objecttype['objecttype_name']);
				$object=$this -> dbObj -> getrow('select *  from '.WEB_ADMIN_TABPOX.$objecttype["objecttypetable"].'  where '.$objecttype["objecttypetable"].'_id ='.$inrrs["objectid"]);
				 
				$t -> set_var('object_name',$object[$objecttype["objecttypetable"].'_name']);
				$t -> set_var('memo',$inrrs['memo']);
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();			
			$acount = &$this -> dbObj -> GetOne('select count(*) as acount from '.WEB_ADMIN_TABPOX.'transfervoucherdetail  where transfervoucher_id  ='.$updid);
			$t -> set_var('totallend',$totallend);	
			$t -> set_var('totalloan',$totalloan);
			$t -> set_var('recordcount',$acount);

			
			 
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));	



	
		//$t -> set_var('categorynamelist',$this ->selectlist('servicecategory','category_id','category_name',$data['categoryid']));		
		
		
		//$t -> set_var('ml',"");	
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}
	function disp(){
		$t = new Template('../template/finace');
		$t -> set_file('f','transfervoucher.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','room','r');		

//搜索
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.'  like "%'.$keywords.'%"';}else{$condition=$category.' like "%'.$keywords.'%"';}
		}
//分页		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:20;
			$offset = $pageid>0?($pageid-1)*$psize:0;

			//设置商品
			$t -> set_var('r');
			
			
			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'transfervoucher  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'transfervoucher r INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on r.roomgroup_id  =f.roomgroup_id   where f.roomgroup_name like '%".$keywords."%' and  r.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'transfervoucher  where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY transfervoucher_id DESC  LIMIT ".$offset." , ".$psize);
			
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
				
			
			
			
			
			
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'transfervoucher  where agencyid ='.$_SESSION["currentorgan"]);
			//echo 'select * from '.WEB_ADMIN_TABPOX.'account  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				
				$t -> set_var('agency_name',$this->dbObj -> GetOne('SELECT agency_name FROM '.WEB_ADMIN_TABPOX.'agency WHERE agency_id = '.$inrrs['agencyid']));
				$t -> set_var('accounttitle_name',$this->dbObj -> GetOne('SELECT accounttitle_name FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$inrrs['accounttitle_id']));
				$t -> set_var('accounttitle_name',$this->dbObj -> GetOne('SELECT accounttitle_name FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$inrrs['accounttitle_id']));
				$t -> set_var('auditor_name',$this->dbObj -> GetOne('SELECT employee_name FROM '.WEB_ADMIN_TABPOX.'employee WHERE employee_id = '.$inrrs['auditor']));
			$status_name=array("<font color=blue>未完成</font>","已完成","<font color=red>红字冲销</font>","<font color=red>被红字冲销</font>","<font color=green>已提交</font>","<font color=#AAAAAA>已审核</font>");
			 
			$t -> set_var('status_name',$status_name[$inrrs['status']]);	
				$t -> set_var('printbill','<a href="?action=print&id='.$inrrs['transfervoucher_id'].'" target="_blank">打印</a>');
							
				
				$fromtype=array("sell","sell","sell","sell","purchase","purchreturn","takestock","lossregister","lingyong");
				$billno=$this->dbObj -> GetOne("SELECT ".$fromtype[$inrrs['fromtype']]."_no FROM ".WEB_ADMIN_TABPOX.$fromtype[$inrrs['fromtype']]." WHERE ".$fromtype[$inrrs['fromtype']]."_id= ".$inrrs['frombillid']);
				$viewbillfunction=array("viewbill","viewbill","viewbill","viewbill","viewpurchbill","viewpurchreturnbill","viewcheckstockbill","viewlossregisterbill","viewlingyongbill");
				 $billname=array("销货单","销货单","销货单","销货单","进货单","采购退货单","盘点单","报损单","领用单");
				$t -> set_var('frombill',$billname[$inrrs["fromtype"]].'<a href=#  onclick="'.$viewbillfunction[$inrrs["fromtype"]].'('.$inrrs["frombillid"].')">'.$billno.'</a>');
				 
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['transfervoucher_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['transfervoucher_id']));			
				$t -> parse('r','room',true);
			}
			$inrs -> Close();	
				
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');		
		$t -> set_var('add',$this -> getAddStr('img'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	
 
	
	
	function goDispAppend(){

		$t = new Template('../template/finace');
		$t -> set_file('f','transfervoucher_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
			
		//$t -> set_var('transfervoucher_no',"");
		$Prefix='VH';
		$agency_no=$_SESSION["agency_no"].date('ym',time());
		$table=WEB_ADMIN_TABPOX.'transfervoucher';
		$column='transfervoucher_no';
		$number=5;
		$id='transfervoucher_id';	
		
		$t -> set_var('transfervoucher_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));	
		$t -> set_var('suppliers_no',"");	
		$t -> set_var('creattime',date('Y-m-d H:i:s',time()));	
		$t -> set_var('cloan',"0");
		$t -> set_var('clend',"0");
		$t -> set_var('totalloan',"0");
		$t -> set_var('totallend',"0");		
		$t -> set_var('cobjectid',"");
		$t -> set_var('cobject_name',"");
		$t -> set_var('auditor_name',"");

		$t -> set_var('employee_name',"");	
		$t -> set_var('employee_id',"");	
		$t -> set_var('date',date('Y-m-d',time()));	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");
		$t -> set_var('comdisplay',"none");		
		$t -> set_var('memo',"");
		$t -> set_var('abstract',"");
		$t -> set_var('frombill',"");
		
		$t -> set_var('man',$this -> dbObj -> getone('select B.employee_name from '.WEB_ADMIN_TABPOX.'user A INNER JOIN '.WEB_ADMIN_TABPOX.'employee B ON A.employee_id=B.employee_id  where A.userid = '.$this->getUid()));
		$t -> set_var('accounttitlelist',$this->selectlist1('accounttitle','accounttitle_id','accounttitle_name',''));	
		$t -> set_var('objecttypelist',$this->selectlist('objecttype','objecttype_id','objecttype_name',''));	
		$t -> set_var('acount',"");	
		$t -> set_var('recordcount',"0");	
		$t -> set_var('cstandardunit',"");	
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
		$t -> set_var('cdiscount',$this->getValue('purchdiscount'));
 
		$t -> set_var('addprodisplay',"display:none");	
		$t -> set_var('userid',$this->getUid());		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			 
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'transfervoucher WHERE transfervoucher_id = '.$updid);
			if($data['status']==5){//已审核 不可修改
			$t -> set_var('savedisabled','disabled');
			$t -> set_var('auditdisabled','disabled');
			}else if($data['status']==0){
			$t -> set_var('auditdisabled','disabled');
			//$t -> set_var('savedisabled','disabled');
			}
				$fromtype=array("sell","sell","sell","sell","purchase","purchreturn","takestock","lossregister","lingyong");
				$billno=$this->dbObj -> GetOne("SELECT ".$fromtype[$data['fromtype']]."_no FROM ".WEB_ADMIN_TABPOX.$fromtype[$data['fromtype']]." WHERE ".$fromtype[$data['fromtype']]."_id= ".$data['frombillid']);
				$viewbillfunction=array("viewbill","viewbill","viewbill","viewbill","viewpurchbill","viewpurchreturnbill","viewcheckstockbill","viewlossregisterbill","viewlingyongbill");
				$billname=array("销货单","销货单","销货单","销货单","进货单","采购退货单","盘点单","报损单","领用单");
				$t -> set_var('frombill',$billname[$data["fromtype"]].'<a href=#  onclick="'.$viewbillfunction[$data["fromtype"]].'('.$data["frombillid"].')">'.$billno.'</a>');
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');			
			$t -> set_var($data);			
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$t -> set_var('comdisplay',"");	
		$auditor_name=$this -> dbObj -> GetOne("select employee_name from`".WEB_ADMIN_TABPOX."employee`  WHERE `employee_id`=".$data['auditor']);
		$t -> set_var('auditor_name',$auditor_name);	
		$t -> set_var('comdisplay',"");	
		$t -> set_var('cproduce_no',"");	
		$t -> set_var('cproduce_name',"");	
		$t -> set_var('ccode',"");	
		$t -> set_var('cproduce_id',"");	
		$t -> set_var('cstandardunit',"");	
		$t -> set_var('lowerlimit',"");	
		$t -> set_var('clend',"");	
		$t -> set_var('cmemo',"");	
		$t -> set_var('cloan',"");	
		$t -> set_var('cobject_name',"");	
		$t -> set_var('cobjectid',"");	
		$t -> set_var('employee_id',"");	
		$t -> set_var('employee_name',"");	
		$t -> set_var('cloan',"0");
		$t -> set_var('clend',"0");			
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
		 
		$t -> set_var('ctransfervoucherprice',0);
		
 					
		//设置内列表
			
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'transfervoucherdetail  where transfervoucher_id  ='.$updid);
			$totallend=0;
			$totalloan=0;
			$tr=1;
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var('trid','tr'.$tr);
				$t -> set_var('imgid','img'.$tr);
				
				$tr=$tr+1;
				$t -> set_var($inrrs);
				
				$t -> set_var('vmemo',$inrrs['memo']);
				$totallend=$totallend+$inrrs['lend'];
				$totalloan=$totalloan+$inrrs['loan'];
				$t -> set_var('numbers',$inrrs['number']);
				$data1=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'accounttitle   where accounttitle_id ='.$inrrs["accounttitle_id"]);
				$t -> set_var($data1);
				 
				 $objecttype=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id ='.$inrrs["objecttype_id"]);
				 $t -> set_var('objecttype_name',$objecttype['objecttype_name']);
				$object=$this -> dbObj -> getrow('select *  from '.WEB_ADMIN_TABPOX.$objecttype["objecttypetable"].'  where '.$objecttype["objecttypetable"].'_id ='.$inrrs["objectid"]);
				 
				$t -> set_var('object_name',$object[$objecttype["objecttypetable"].'_name']);
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();			
			$acount = &$this -> dbObj -> GetOne('select count(*) as acount from '.WEB_ADMIN_TABPOX.'transfervoucherdetail  where transfervoucher_id  ='.$updid);
			$t -> set_var('accounttitlelist',$this->selectlist1('accounttitle','accounttitle_id','accounttitle_name',''));	
			$t -> set_var('objecttypelist',$this->selectlist('objecttype','objecttype_id','objecttype_name',''));	
			$t -> set_var('recordcount',$acount);
			$t -> set_var('totallend',$totallend);	
			$t -> set_var('totalloan',$totalloan);	
		// 修改消耗品
		 
		    if($_GET['ctransfervoucherdetail_id']!=''){
			//$t -> set_var('cs');
		   $t -> set_var('caction','upd');	
            
			$inrs2 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'transfervoucherdetail  where transfervoucherdetail_id ='.$_GET['ctransfervoucherdetail_id']);
			//$t -> set_var($inrs);
			$t -> set_var('accounttitlelist',$this->selectlist1('accounttitle','accounttitle_id','accounttitle_name',$inrs2['accounttitle_id']));	
			$t -> set_var('objecttypelist',$this->selectlist('objecttype','objecttype_id','objecttype_name',$inrs2['objecttype_id']));	
		    $t -> set_var('cupdid',$inrs2['produce_id']);	
			$t -> set_var('proupdid',$_GET['ctransfervoucherdetail_id']);	
			$t -> set_var('cmemo',$inrs2['memo']);	
			$t -> set_var('clend',$inrs2['lend']);
			$t -> set_var('cloan',$inrs2['loan']);
			
				$objecttype1=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id ='.$inrs2["objecttype_id"]);
 
				$object1=$this -> dbObj -> getrow('select *  from '.WEB_ADMIN_TABPOX.$objecttype1["objecttypetable"].'  where '.$objecttype1["objecttypetable"].'_id ='.$inrs2["objectid"]);
 			$t -> set_var('cobjectid',$object1[$objecttype1['objecttypetable']."_id"]);

			 
				$t -> set_var('cobject_name',$object1[$objecttype1["objecttypetable"].'_name']);			
			
			$inrs3 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrs2['produce_id']);
			$t -> set_var('cproduce_no',$inrs3['produce_no']);
			$t -> set_var('cproduce_name',$inrs3['produce_name']);
			$t -> set_var('cproduce_id',$inrs3['produce_id']);
			$t -> set_var('ccode',$inrs3['code']);
			$t -> set_var('cprice',$inrs3['price']);
				
			$t -> set_var('cdiscount',$inrs2['discount']);
			$t -> set_var('ctransfervoucherprice',$inrs2['transfervoucherprice']);
			//$t -> set_var('cviceunitnumber',$inrs3['viceunitnumber']);
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
		$this->getSupper()?$t -> set_var('canaudit',''):$t -> set_var('canaudit','none');	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}
	
	
		function selectlist1($table,$id,$name,$selectid=0){
			$no=$table."_no";
			
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table  );
			$str='';
	     	while ($inrrs = &$inrs -> FetchRow()) {
			
			if ($inrrs[$id]==$selectid)
			$str =$str."<option value=".$inrrs[$id]." selected>".$inrrs[$no].$inrrs[$name]."</option>";	
			else
			$str =$str."<option value=".$inrrs[$id].">".$inrrs[$no].$inrrs[$name]."</option>";			
			}
			$inrs-> Close();	
			return  $str;	
	    }	
	
		function selectlist($table,$id,$name,$selectid=0){
	
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table  );
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
	  if($_GET['transfervoucherdetail_id']==''){
		$delid = $_GET[DELETE.'id'] ;
		$status=$this -> dbObj -> GetOne('SELECT status FROM '.WEB_ADMIN_TABPOX.'transfervoucher WHERE transfervoucher_id in('.$delid.')');
		if($status==5){
			$info="凭证已审核，不可删除。";
			$this -> quit($info.'删除失败！');
		}else{
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'transfervoucher WHERE transfervoucher_id in('.$delid.')');
		 
		 
		 $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail WHERE transfervoucher_id in('.$delid.')');
		}
		}else{
		 $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail WHERE transfervoucherdetail_id in('.$_GET['transfervoucherdetail_id'].')');
		}
		$info=$info.'删除';
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
			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucher` (`transfervoucher_no`,`date`,`agencyid`,`creattime`,`man`) VALUES ('" .$_POST["transfervoucher_no"]."','".$_POST["date"]."','".$_SESSION["currentorgan"]."','".$_POST["creattime"]."', '".$_POST["man"]."')");
 
			$id = $this -> dbObj -> Insert_ID();
//echo "INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucher` (`transfervoucher_no`,`transfervoucher_time`,`acount`,`warehouse_id`,`suppliers_id`,`employee_id`,`memo`,`agencyid`,`creattime`,`man`) VALUES ('" .$_POST["transfervoucher_no"]."','".$_POST["transfervoucher_time"]."', '" .$_POST["acount"]."','".$_POST["warehouse_id"]."', '" .$_POST["suppliers_id"]."','".$_POST["employee_id"]."', '" .$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["creattime"]."', '".$_POST["man"]."')";
//echo $_POST['cproduce_id'].$_POST['cnumber'];
 
if($_POST['subtype']=='2'){			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` (`transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','".$_POST["caccounttitle_id"]."','".$_POST["cobjecttype_id"]."','".$_POST["cobjectid"]."','".$_POST["clend"]."','".$_POST["cloan"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')");	
			//  echo "INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` (`transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','".$_POST["caccounttitle_id"]."','".$_POST["cobjecttype_id"]."','".$_POST["cobjectid"]."','".$_POST["clend"]."','".$_POST["cloan"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')";
			}
			
		$acount = &$this -> dbObj -> GetOne('select sum(totalacount) from '.WEB_ADMIN_TABPOX.'transfervoucherdetail   where transfervoucher_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."transfervoucher` SET `acount` =".$acount." where transfervoucher_id=".$id) ;	
			
			exit("<script>alert('".$info."成功');window.location.href='transfervoucher.php?action=upd&updid=".$id."';</script>");
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."transfervoucher` SET `transfervoucher_no` = '".$_POST["transfervoucher_no"]."',date='".$_POST["date"]."', abstract='".$_POST["abstract"]."' , man='".$_POST["man"]."' WHERE transfervoucher_id =".$id);
			 

		//echo $_POST["con_act"];
		
 		if($_POST["con_act"]=='upd'){
		 
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."transfervoucherdetail` SET `accounttitle_id` = '".$_POST["caccounttitle_id"]."',`objecttype_id` = '".$_POST["cobjecttype_id"]."',`objectid` = '".$_POST["cobjectid"]."',lend='".$_POST["clend"]."',loan='".$_POST["cloan"]."',memo='".$_POST["cmemo"]."' WHERE transfervoucherdetail_id  =".$_POST['proupdid']);		
		//echo "UPDATE `".WEB_ADMIN_TABPOX."transfervoucherdetail` SET `produce_id` = '".$_POST["cproduce_id"]."',`number` = '".$_POST["cnumber"]."',`memo` = '".$_POST["cmemo"]."' WHERE transfervoucherdetail_id  =".$_POST['proupdid'];
		$acount = &$this -> dbObj -> GetOne('select sum(totalacount) from '.WEB_ADMIN_TABPOX.'transfervoucherdetail   where transfervoucher_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."transfervoucher` SET `acount` =".$acount." where transfervoucher_id=".$id) ;	
		
		exit("<script>alert('".$info."项目成功');window.location.href='transfervoucher.php?action=upd&updid=".$id."';</script>");		
		}else{
if($_POST['subtype']=='2'){		
			$cobjecttype_id=$_POST["cobjecttype_id"]==''?0:$_POST["cobjecttype_id"];
			$cobjectid=$_POST["cobjectid"]==''?0:$_POST["cobjectid"];
			$cloan=$_POST["cloan"]==''?0:$_POST["cloan"];
			$clend=$_POST["clend"]==''?0:$_POST["clend"];
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','".$_POST["caccounttitle_id"]."','".$cobjecttype_id."','".$cobjectid."','".$clend."','".$cloan."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')");	
			// echo "INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` (`transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','".$_POST["caccounttitle_id"]."','".$_POST["cobjecttype_id"]."','".$_POST["cobjectid"]."','".$_POST["clend"]."','".$_POST["cloan"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')";
			 
			
		//$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."serviceconsume` SET `produce_id` = '".$_POST["produce_id"]."',`std_consumption` = '".$_POST["std_consumption"]."',`memo` = '".$_POST["memo"]."',`transfervoucher` = '".$_POST["transfervoucher"]."' WHERE transfervoucher_id =".$id);
		//echo "INSERT INTO `".WEB_ADMIN_TABPOX."consumables` (transfervoucher_id,produce_id,std_consumption,memo,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cstd_consumption"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')";
		$info = '添加项目';

		}
		$acount = &$this -> dbObj -> GetOne('select sum(totalacount) from '.WEB_ADMIN_TABPOX.'transfervoucherdetail   where transfervoucher_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."transfervoucher` SET `acount` =".$acount." where transfervoucher_id=".$id) ;	
		
		}
		//echo $_POST['issave'];
		if($_POST['issave']=='1'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."transfervoucher` SET `status`=1  WHERE transfervoucher_id =".$id);//0未提交，1已提交2已收部分3全部收完
			exit("<script>alert('保存成功');window.location.href='transfervoucher.php';</script>");
		}	
		}
//$this -> quit($info.'成功！');

		//修改总金额

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
$nostr = $this -> dbObj ->GetRow("select * from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by ".$id." desc limit 1");
 
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