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
        if(isset($_GET['action']) && $_GET['action']=='selectwarehouse')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this ->selectwarehouse();
        }else if(isset($_GET['action']) && $_GET['action']=='submittoaudit')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this ->submittoaudit();
        }else if(isset($_GET['action']) && $_GET['action']=='audit')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this ->audit();
        }else if(isset($_GET['action']) && $_GET['action']=='recoil')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this ->recoil();
        }else if(isset($_GET['action']) && $_GET['action']=='viewbill')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this ->viewbill();
        }else if(isset($_GET['action']) && $_GET['action']=='printbill')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this ->printbill();
        }else{
            parent::Main();
        }
    }	
	function printbill(){


		//$t = new Template('../template/stock');
		$t -> set_file('f','takestockhistory_detail_print.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
		$t -> set_var('ml');
		
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "%'.$keywords.'%"';}else{$condition=$category.' like "%'.$keywords.'%"';}
		}
		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:20;
			$offset = $pageid>0?($pageid-1)*$psize:0;
			
	


 

			$updid = $_GET[MODIFY.'id'] + 0 ;
			$data=$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'takestock where takestock_id ='.$updid);
			
			
			$t -> set_var('takestock_no',$data['takestock_no']);


			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'takestockdetail where takestock_id = '.$updid.' and  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select s.* from '.WEB_ADMIN_TABPOX.'takestockdetail s INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on s.produce_id =f.produce_id  where f.".$category." like '%".$keywords."%' and  takestock_id =".$updid." and  s.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'takestockdetail  where `real_number` <> `sys_number` and   takestock_id = '.$updid.' and  agencyid ='.$_SESSION["currentorgan"];
			 
			}
          
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  sys_number DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?action=upd&updid=".$updid."&category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
           	$t -> set_var('recordcount',$count);
	     	while ($inrrs = &$inrs -> FetchRow()) {	
			


				$t -> set_var('status_name',$status_name[$inrrs['status']]);
				$t -> set_var('sys_number',$inrrs['number']);
				$inrrs['real_number']?$t -> set_var('real_number',$inrrs['real_number']):$t -> set_var('real_number',$inrrs['number']);
				$produce=$this -> dbObj -> Getrow('select * from '.WEB_ADMIN_TABPOX.'produce where produce_id ='.$inrrs["produce_id"]);
				$t -> set_var($produce);
				
				$t -> set_var('unit_name',$this -> dbObj -> GetOne('select unit_name from '.WEB_ADMIN_TABPOX.'unit where unit_id ='.$produce["standardunit"]));
			   //	$t -> set_var('delete',$this -> getDelStr('',$inrrs['takestock_id']));
		      //  $t -> set_var('edit',$this -> getupdStr('',$inrrs['takestock_id']));				
				$t -> set_var($inrrs);
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
			$status_name=array("<font color=blue>未通过审核<font>","未完成","<font color=green>待审核</font>","<font color=red>红字冲销</font>","<font color=red>被红字冲销</font>","已审核");	
$t -> set_var('status_name',$status_name[$data['status']]);
			$t -> set_var('auditor_name',$this -> dbObj -> GetOne('select employee_name from '.WEB_ADMIN_TABPOX.'employee where employee_id ='.$data["auditor_id"]));
			if($data['status']<4){
			$t ->set_var('auditdisabled','');
			$t ->set_var('recoildisabled','');
			$t ->set_var('recoildisabled','disabled="disabled"');
			}else
			{$t ->set_var('auditdisabled','disabled="disabled"');
			$t ->set_var('recoildisabled','disabled="disabled"');
			$t ->set_var('recoildisabled','');
			}
			$t -> set_var('tips',"");
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
		$t -> set_var('takestock_id',$updid);
		$t -> set_var('warehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse where warehouse_id ='.$data["warehouse_id"]));
		$t -> set_var('warehouse_id',$data["warehouse_id"]);
		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');	
		}
	function viewbill(){


		$t = new Template('../template/stock');
		$t -> set_file('f','takestockhistory_detail_viewbill.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
		$t -> set_var('ml');
		
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "%'.$keywords.'%"';}else{$condition=$category.' like "%'.$keywords.'%"';}
		}
		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:20;
			$offset = $pageid>0?($pageid-1)*$psize:0;
			
	


 

			$updid = $_GET[MODIFY.'id'] + 0 ;
			$data=$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'takestock where takestock_id ='.$updid);
			
			
			$t -> set_var('takestock_no',$data['takestock_no']);


			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'takestockdetail where takestock_id = '.$updid.' and  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select s.* from '.WEB_ADMIN_TABPOX.'takestockdetail s INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on s.produce_id =f.produce_id  where f.".$category." like '%".$keywords."%' and  takestock_id =".$updid." and  s.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'takestockdetail  where `real_number` <> `sys_number` and   takestock_id = '.$updid.' and  agencyid ='.$_SESSION["currentorgan"];
			 
			}
          
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  sys_number DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?action=upd&updid=".$updid."&category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));
			
           	$t -> set_var('recordcount',$count);
	     	while ($inrrs = &$inrs -> FetchRow()) {	
			


				$t -> set_var('status_name',$status_name[$inrrs['status']]);
				$t -> set_var('sys_number',$inrrs['number']);
				$inrrs['real_number']?$t -> set_var('real_number',$inrrs['real_number']):$t -> set_var('real_number',$inrrs['number']);
				$produce=$this -> dbObj -> Getrow('select * from '.WEB_ADMIN_TABPOX.'produce where produce_id ='.$inrrs["produce_id"]);
				$t -> set_var($produce);
				
				$t -> set_var('unit_name',$this -> dbObj -> GetOne('select unit_name from '.WEB_ADMIN_TABPOX.'unit where unit_id ='.$produce["standardunit"]));
			   //	$t -> set_var('delete',$this -> getDelStr('',$inrrs['takestock_id']));
		      //  $t -> set_var('edit',$this -> getupdStr('',$inrrs['takestock_id']));				
				$t -> set_var($inrrs);
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
			$status_name=array("<font color=blue>未通过审核<font>","未完成","<font color=green>待审核</font>","<font color=red>红字冲销</font>","<font color=red>被红字冲销</font>","已审核");	
$t -> set_var('status_name',$status_name[$data['status']]);
			$t -> set_var('auditor_name',$this -> dbObj -> GetOne('select employee_name from '.WEB_ADMIN_TABPOX.'employee where employee_id ='.$data["auditor_id"]));
			if($data['status']<4){
			$t ->set_var('auditdisabled','');
			$t ->set_var('recoildisabled','');
			$t ->set_var('recoildisabled','disabled="disabled"');
			}else
			{$t ->set_var('auditdisabled','disabled="disabled"');
			$t ->set_var('recoildisabled','disabled="disabled"');
			$t ->set_var('recoildisabled','');
			}
			$t -> set_var('tips',"");
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
		$t -> set_var('takestock_id',$updid);
		$t -> set_var('warehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse where warehouse_id ='.$data["warehouse_id"]));
		$t -> set_var('warehouse_id',$data["warehouse_id"]);
		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');	
		}		
    function turnback(){
	$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX.'takestock SET status="6"  where  takestock_id='.$_GET["takestock_id"].' and   agencyid ='.$_SESSION["currentorgan"]);
	//echo 'UPDATE '.WEB_ADMIN_TABPOX.'takestock SET status="0"  where  takestock_id='.$_GET["takestock_id"].' and   agencyid ='.$_SESSION["currentorgan"];
	 	$info='撤回';
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');		
	}	
function recoil(){
	
	$this -> dbObj -> Execute("START TRANSACTION");//事务开始。
	$this->prodaybooksObj=new prodaybooks();//商品流水账
	$this->stockObj=new stock();
	$condition =' takestock_id='.$_GET['id'];
	$sql='select * from '.WEB_ADMIN_TABPOX.'takestock  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;	
	
	$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  takestock_id DESC  LIMIT 0 ,1");
	
		while ($inrrs = &$inrs -> FetchRow()) {

			//$man=$this -> dbObj -> getone('select username from '.WEB_ADMIN_TABPOX.'user where userid = '.$this->getUid());
			//插入反冲盘点主记录
		$res1=$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'takestock (takestock_no,warehouse_id,status,auditor_id,memo,agencyid) value("'.$inrrs['takestock_no'].'","'.$inrrs['warehouse_id'].'",3,"'.$inrrs["auditor_id"].'","'.$inrrs["memo"].'","'.$inrrs["agencyid"].'")');
		//echo 'INSERT INTO '.WEB_ADMIN_TABPOX.'takestock (takestock_no,warehouse_id,status,auditor_id,memo,agencyid) value("'.$inrrs['takestock_no'].'","'.$inrrs['warehouse_id'].'",3,"'.$inrrs["auditor_id"].'","'.$inrrs["memo"].'","'.$inrrs["agencyid"].'")';
			
		$id = $this -> dbObj -> Insert_ID();
		$warehouse_id=$inrrs["warehouse_id"];	
		$sqlstr='select * from '.WEB_ADMIN_TABPOX.'takestockdetail  where  agencyid ='.$_SESSION["currentorgan"].' and takestock_id='.$_GET['id'];	
		
		//插入盘点明细
		
		$inrs1 = &$this -> dbObj -> Execute($sqlstr);
				while ($inrrs1 = &$inrs1 -> FetchRow()) {
					if($inrrs1["difference"]<>0){
					$meanprice=$this->dbObj->GetOne('SELECT stockunitprice FROM '.WEB_ADMIN_TABPOX.'stock WHERE warehouse_id='.$inrrs["warehouse_id"].' and produce_id='.$inrrs1['produce_id'].' and agencyid="'.$inrrs["agencyid"].'"'); 
					$stockdata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'stock WHERE warehouse_id='.$inrrs["warehouse_id"].' and produce_id='.$inrrs1['produce_id'].' and agencyid="'.$inrrs["agencyid"].'"'); 
					$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."takestockdetail` (`takestock_id`,`warehouse_id`,`produce_id`, `sys_number`,`real_number`,difference, `agencyid`)VALUES ( '".$id."','".$inrrs["warehouse_id"]."', '".$inrrs1['produce_id']."','".$stockdata['number']."','".($stockdata['number']-$inrrs1['difference'])."','".(-$inrrs1['difference'])."', '".$_SESSION["currentorgan"]."')");//插入反冲明细
					// echo "INSERT INTO `".WEB_ADMIN_TABPOX."takestockdetail` (`takestock_id`,`warehouse_id`,`produce_id`, `sys_number`,`real_number`,difference, `agencyid`)VALUES ( '".$id."','".$inrrs["warehouse_id"]."', '".$inrrs1['produce_id']."','".$stockdata['number']."','".($stockdata['number']-$inrrs1['difference'])."','".-$inrrs1['difference']."', '".$_SESSION["currentorgan"]."')";
				 $res3=$this->prodaybooksObj->main($inrrs1['produce_id'],$inrrs["warehouse_id"],-$inrrs1["difference"],-$inrrs1["difference"]*$meanprice,6,$id,'',"被反冲的盘点单; ".$inrrs['memo'],$_SESSION["currentorgan"]);//商品流水账	
				 $addacount=$inrrs1['difference']*$meanprice;
				 $res4=$this->stockObj->addnumber($inrrs1["produce_id"],-$inrrs1["difference"],-$addacount,$warehouse_id,$inrrs["agencyid"]);
					}
				}
			
				}
	  			$res5= $this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."takestock` SET `status`=2  WHERE takestock_id =".$_GET['id']);//4表示被反冲3反冲2未审核1未提交	
	  	//更新库存
		
		 
			if($res1&&$res2&&$res3&&$res4&&$res5){echo "ok";}else{echo "no";}
			
	   
		if($res1&&$res2&&$res3&&$res4&&$res5){
		
		$this -> dbObj -> Execute("COMMIT");
		  exit("<script>alert('反冲成功');window.location.href='checkstockhistory.php';</script>");	
		}else{
		$this -> dbObj -> Execute("ROLLBACK");
		 exit("<script>alert('发生错误，审核失败，数据已经回滚。');window.location.href='checkstockhistory.php';</script>");
		}
	}	
	function audit(){
	$addacount=0;
	$agencyid=$_SESSION["currentorgan"];
	$employee_id=$this -> dbObj -> GetOne("select employee_id from`".WEB_ADMIN_TABPOX."user`  WHERE `userid`=".$this->getUid());
	//echo "select employee_id from`".WEB_ADMIN_TABPOX."user`  WHERE `userid`=".$this->getUid()." and `agencyid`=".$_SESSION["currentorgan"];
	//$this->stockObj=new stock();
	 $this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX.'takestock SET status="5" , auditor_id='.$employee_id.' where  takestock_id='.$_GET["takestock_id"].' and   agencyid ='.$_SESSION["currentorgan"]);
	 
/*	 $result=$this -> dbObj -> GetRow("select * from`".WEB_ADMIN_TABPOX."takestock`  WHERE `takestock_id`='".$_GET['takestock_id']."' and `agencyid`='".$_SESSION["currentorgan"]."'");
	 $warehouse_id=$result['warehouse_id'];
	 
	 $data=$this -> dbObj -> Execute("select * from`".WEB_ADMIN_TABPOX."takestockdetail`  WHERE `takestock_id`='".$_GET['takestock_id']."' and `agencyid`='".$_SESSION["currentorgan"]."'");
	 
	 while ($inrrs = &$data -> FetchRow()) {			
            $gap=$inrrs['real_number']-$inrrs['sys_number'];	
			
			if($gap!=0){
		$meanprice=$this->dbObj->GetOne('SELECT acount/number FROM '.WEB_ADMIN_TABPOX.'stock WHERE warehouse_id='.$inrrs["warehouse_id"].' and produce_id='.$inrrs["produce_id"].' and agencyid="'.$inrrs["agencyid"].'"'); 
		$addacount=$meanprice*$gap;
		//$this->stockObj->addnumber($inrrs["produce_id"],$gap,$addacount,$warehouse_id,$agencyid);
				
			//$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX.'stock SET number=number+'.$gap.' where produce_id ="'.$inrrs["produce_id"].'" and warehouse_id="'.$warehouse_id.'" and  agencyid="'.$_SESSION["currentorgan"].'"');
//echo 'UPDATE '.WEB_ADMIN_TABPOX.'stock SET number=number+'.$gap.' where produce_id ="'.($inrrs["produce_id"]).'" and warehouse_id="'.$warehouse_id.'" and  agencyid="'.$_SESSION["currentorgan"].'"';
			}
			}
			$data -> Close();		*/ 
	    
		$info='审核';
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}	
	function submittoaudit(){
	$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX.'takestock SET status="2"  where  takestock_id='.$_GET["takestock_id"].' and   agencyid ='.$_SESSION["currentorgan"]);
	//echo 'UPDATE '.WEB_ADMIN_TABPOX.'takestock SET status="2"  where  takestock_id='.$_GET["takestock_id"].' and   agencyid ='.$_SESSION["currentorgan"];
		$info='提交';
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}
	function selectwarehouse(){
		$t = new Template('../template/stock');
		$t -> set_file('f','selectstockhistory.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
	
		$t -> set_var('warehouselist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$_GET['keywords']));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');		
		
/*		
		$warehouse_id=$_POST['warehouse_id'];
		$Prefix='TS'.date('Ym',time());
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'takestock';
		$column='takestock_no';
		$number=3;
		$id='takestock_id';		
		$takestock_no=$this->makeno($Prefix,$agency_no,$table,$column,$number,$id);
		$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'takestock (takestock_no,warehouse_id,agencyid) value("'.$takestock_no.'","'.$warehouse_id.'","'.$_SESSION["currentorgan"].'")');
		echo 'INSERT INTO '.WEB_ADMIN_TABPOX.'takestock (takestock_no,warehouse_id,agencyid) value('.$takestock_no.','.$warehouse_id.','.$_SESSION["currentorgan"].')';
		$id = $this -> dbObj -> Insert_ID();
		header("Location: checkstock.php?action=upd&updid=".$id);
	*/	
		}
	function disp(){
		//定义模板
		$t = new Template('../template/stock');
		$t -> set_file('f','takestockhistory.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
   
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "%'.$keywords.'%"';}else{$condition=$category.' like "%'.$keywords.'%"';}
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'takestock where  status>1 and agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'takestock s INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on s.produce_id =f.produce_id  where f.".$category." like '%".$keywords."%' and  s.status>1 and s.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'takestock  where status>1 and agencyid ='.$_SESSION["currentorgan"];
			 
			}
			
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  takestock_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
           	$t -> set_var('recordcount',$count);
			
			$status_name=array("<font color=blue>未完成<font>","未提交","<font color=red>红字冲销</font>","<font color=red>被红字冲销</font>","<font color=blue>待审核</font>","已审核");				
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'produce  where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				
				
				$t -> set_var('warehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse where warehouse_id ='.$inrrs["warehouse_id"]));
				$t -> set_var('auditor_name',$this -> dbObj -> getone('select employee_name from '.WEB_ADMIN_TABPOX.'employee where employee_id ='.$inrrs["auditor_id"]));
				$t -> set_var('status_name',$status_name[$inrrs['status']]);
				$produce=$this -> dbObj -> Getrow('select * from '.WEB_ADMIN_TABPOX.'produce where produce_id ='.$inrrs["produce_id"]);
				$t -> set_var($produce);
				$t -> set_var('view','<a href="checkstockhistory.php?action=upd&updid='.$inrrs['takestock_id'].'">查看</a>');
				$t -> set_var('printbill','<a href="?action=printbill&updid='.$inrrs['takestock_id'].'" target="_blank">打印</a>');
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['takestock_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['takestock_id']));				
				$t -> set_var($inrrs);
				
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		
		$liststr='';
			
		for($i=0;$i<count($status_name);$i++){
			if($_GET['category']=='status'&&$_GET['keywords']==$i){
				$liststr=$liststr.'<option value="'.($i).'" selected>'.$status_name[$i].'</option>';	
					}else{
				$liststr=$liststr.'<option value="'.($i).'">'.$status_name[$i].'</option>';}
				}
		$t -> set_var('statusnamelist',$liststr);
		
		$t -> set_var('warehouselist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$_GET['keywords']));	
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
		$t -> set_file('f','takestockhistory_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
		
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "%'.$keywords.'%"';}else{$condition=$category.' like "%'.$keywords.'%"';}
		}
		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:20;
			$offset = $pageid>0?($pageid-1)*$psize:0;
			
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		$Prefix='TS'.date('Ym',time());
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'takestock';
		$column='takestock_no';
		$number=3;
		$id='takestock_id';	
		$warehouse_id=$_GET['warehouse_id'];
		$takestock_no=$this->makeno($Prefix,$agency_no,$table,$column,$number,$id);
		$t -> set_var('takestock_no',$takestock_no);	
		$t -> set_var('warehouse_name',$this -> dbObj -> GetOne('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse where warehouse_id ='.$warehouse_id));
		$t -> set_var('warehouselist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$_GET['keywords']));
		$t -> set_var('userid',$this->getUid());	
		$t -> set_var('picurl',"暂时没有照片");	
		$t -> set_var('picpath',"");	
		$t -> set_var('birthday',date("Y-m-d"));
		
		$t -> set_var('ml');
					
		
			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'stock where warehouse_id =".$warehouse_id." and  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select s.* from '.WEB_ADMIN_TABPOX.'stock s INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on s.produce_id =f.produce_id  where f.".$category." like '%".$keywords."%' and s.warehouse_id =".$warehouse_id." and   s.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql="select  * from ".WEB_ADMIN_TABPOX."stock where `real_number` <> `sys_number` and  warehouse_id =".$warehouse_id." and agencyid =".$_SESSION["currentorgan"];
			 
			}	
		
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  sys_number DESC  LIMIT ".$offset." , ".$psize);
			
			$inrs =$this->dbObj->Execute($sql);
	     	while ($inrrs = &$inrs -> FetchRow()) {			
				$t -> set_var('status_name',$status_name[$inrrs['status']]);
				$t -> set_var('sys_number',$inrrs['number']);
				$inrrs['real_number']?$t -> set_var('real_number',$inrrs['real_number']):$t -> set_var('real_number',$inrrs['number']);
				$produce=$this -> dbObj -> Getrow('select * from '.WEB_ADMIN_TABPOX.'produce where produce_id ='.$inrrs["produce_id"]);
				$t -> set_var($produce);
				$t -> set_var($inrrs);
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');		
		}else{


		$t = new Template('../template/stock');
		$t -> set_file('f','takestockhistory_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
		$t -> set_var('ml');

			$updid = $_GET[MODIFY.'id'] + 0 ;
			$data=$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'takestock where takestock_id ='.$updid);
			
			
			$t -> set_var('takestock_no',$data['takestock_no']);


			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'takestockdetail where takestock_id = '.$updid.' and  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select s.* from '.WEB_ADMIN_TABPOX.'takestockdetail s INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on s.produce_id =f.produce_id  where f.".$category." like '%".$keywords."%' and  takestock_id =".$updid." and  s.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'takestockdetail  where `real_number` <> `sys_number` and   takestock_id = '.$updid.' and  agencyid ='.$_SESSION["currentorgan"];
			 
			}
          
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  sys_number DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?action=upd&updid=".$updid."&category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
           	$t -> set_var('recordcount',$count);
	     	while ($inrrs = &$inrs -> FetchRow()) {	
			


				$t -> set_var('status_name',$status_name[$inrrs['status']]);
				$t -> set_var('sys_number',$inrrs['number']);
				$inrrs['real_number']?$t -> set_var('real_number',$inrrs['real_number']):$t -> set_var('real_number',$inrrs['number']);
				$produce=$this -> dbObj -> Getrow('select * from '.WEB_ADMIN_TABPOX.'produce where produce_id ='.$inrrs["produce_id"]);
				$t -> set_var($produce);
				
				$t -> set_var('unit_name',$this -> dbObj -> GetOne('select unit_name from '.WEB_ADMIN_TABPOX.'unit where unit_id ='.$produce["standardunit"]));
			   //	$t -> set_var('delete',$this -> getDelStr('',$inrrs['takestock_id']));
		      //  $t -> set_var('edit',$this -> getupdStr('',$inrrs['takestock_id']));				
				$t -> set_var($inrrs);
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
			$status_name=array("<font color=blue>未完成<font>","未提交","<font color=red>红字冲销</font>","<font color=red>被红字冲销</font>","<font color=blue>待审核</font>","已审核");	
			$t -> set_var('status_name',$status_name[$data['status']]);
			$t -> set_var('auditor_name',$this -> dbObj -> GetOne('select employee_name from '.WEB_ADMIN_TABPOX.'employee where employee_id ='.$data["auditor_id"]));
			if($data['status']==4){
			$t ->set_var('auditdisabled','');
			$t ->set_var('recoildisabled','');
			$t ->set_var('recoildisabled','');
			}else
			{$t ->set_var('auditdisabled','disabled="disabled"');
			$t ->set_var('recoildisabled','disabled="disabled"');
			$t ->set_var('recoildisabled','disabled="disabled"');
			}
			$t -> set_var('tips',"");
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
		$t -> set_var('takestock_id',$updid);
		$t -> set_var('warehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse where warehouse_id ='.$data["warehouse_id"]));
		$t -> set_var('warehouse_id',$data["warehouse_id"]);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');	
		}

		//$t -> set_var('brandlist',$this ->selectlist('brand','brand_id','brand_name',$data['brandid']));
		//$t -> set_var('genderlist',"111");	
		//echo $data['genderid'];
		//$t -> set_var('genderlist',$this -> gender($data['genderid']));	
		//$t -> set_var('takestocklevellist',$this ->selectlist('takestocklevel','takestocklevel_id','takestocklevel_name',$data['takestocklevelid']));
		//$t -> set_var('emploeelevellist',$this ->selectlist('emploeelevel','emploeelevel_id','emploeelevel_name',$data['emploeelevelid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));						


		
	}
	
	
		function selectlist($table,$id,$name,$selectid=0){
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table .' where agencyid='.$_SESSION["currentorgan"]);

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
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'takestock WHERE takestock_id in('.$delid.') and agencyid='.$_SESSION["currentorgan"]);
		
		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'takestockdetail WHERE takestock_id in('.$delid.') and agencyid='.$_SESSION["currentorgan"]);
		//echo 'DELETE FROM '.WEB_ADMIN_TABPOX.'takestock WHERE takestock_id in('.$delid.') and agencyid='.$_SESSION["currentorgan"];
		//$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'salary  WHERE takestock_id in('.$delid.') and agencyid='.$_SESSION["currentorgan"]);
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
			//插入盘点表
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'takestock (takestock_no,warehouse_id,agencyid) value("'.$_POST['takestock_no'].'","'.$_POST['warehouse_id'].'","'.$_SESSION["currentorgan"].'")');
		
		$id = $this -> dbObj -> Insert_ID();
		//插入明细
			//$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."takestockdetail` (`takestock_id`,`warehouse_id`,`produce_id`, `sys_number`, `real_number`,`agencyid`)VALUES ( '".$id."','".$_POST["warehouse_id"]."', '".$_POST["produce_id"]."','".$_POST["sys_number"]."', '".$_POST["real_number"]."', '".$_POST["idnumber"]."','".$_SESSION["currentorgan"]."')");
			

			$sql="select  * from ".WEB_ADMIN_TABPOX."stock where  warehouse_id =".$_POST['warehouse_id']." and agencyid =".$_SESSION["currentorgan"];			
			$inrs =$this->dbObj->Execute($sql);
	     	while ($inrrs = &$inrs -> FetchRow()) {	
			 $this -> dbObj ->Execute("INSERT INTO `".WEB_ADMIN_TABPOX."takestockdetail` (`takestock_id`,`warehouse_id`,`produce_id`, `sys_number`,`real_number`, `agencyid`)VALUES ( '".$id."','".$inrrs["warehouse_id"]."', '".$inrrs['produce_id']."','".$inrrs['number']."','".$inrrs['number']."', '".$_SESSION["currentorgan"]."')");
			//echo "INSERT INTO `".WEB_ADMIN_TABPOX."takestockdetail` (`takestock_id`,`warehouse_id`,`produce_id`, `sys_number`, `agencyid`)VALUES ( '".$id."','".$inrrs["warehouse_id"]."', '".$inrrs['produce_id']."','".$inrrs['number']."', '".$_SESSION["currentorgan"]."')";
			}
			$inrs -> Close();					
			
			 $id_list = $_POST['id_list'];
			 $produce_id=explode(",",$id_list);
			 for ($i=0;$i<count($produce_id);$i++){
				 $sys_number=$_POST['sys_number'.$produce_id[$i]];
				 $real_number=$_POST['real_number'.$produce_id[$i]];
			 $this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."takestockdetail` SET  `sys_number`='". $sys_number."', `real_number`='".$real_number."' WHERE produce_id=".$produce_id[$i]." and `warehouse_id`='".$_POST["warehouse_id"]."' and  `agencyid`='".$_SESSION["currentorgan"]."'");
			 //echo "UPDATE `".WEB_ADMIN_TABPOX."takestockdetail` SET  `sys_number`='". $sys_number."', `real_number`='".$real_number."' WHERE `takestock_id`='".$id."' and  `agencyid`='".$_SESSION["currentorgan"]."'";

			 }
			
			// $this -> dbObj -> Execute("insert into `".WEB_ADMIN_TABPOX."attendance`(`takestock_id`,agencyid) values ('$id',".$_SESSION["currentorgan"].")"); 
 			// $this -> dbObj -> Execute("insert into `".WEB_ADMIN_TABPOX."salary` (`takestock_id`,`agencyid` )VALUES ('$id',".$_SESSION["currentorgan"].")");
			// echo "insert into `".WEB_ADMIN_TABPOX."salary` (`takestock_id`,`agencyid` )VALUES ('$id',".$_SESSION["currentorgan"].")";
			 
//echo "INSERT INTO `".WEB_ADMIN_TABPOX."takestock` (`takestock_no`, `takestock_name`, `address`, `genderid`, `birthday`,  `idnumber`, `tel`, `handphone`,  `zipcode`, `email`,  `memo`,  `agencyid`, `picpath`,ismarry,province,city)VALUES ( '".$_POST["takestock_no"]."','".$_POST["takestock_name"]."', '".$_POST["address"]."','".$_POST["genderid"]."', '".$_POST["birthday"]."', '".$_POST["idnumber"]."','".$_POST["tel"]."', '".$_POST["handphone"]."','".$_POST["zipcode"]."', '".$_POST["email"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["picpath"]."','".$this->intnonull($_POST["ismarry"])."', '".$_POST["province"]."','".$_POST["city"]."')";
			//$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			
			 $id_list = $_POST['id_list'];
			 $produce_id=explode(",",$id_list);
			 for ($i=0;$i<count($produce_id);$i++){
				 $sys_number=$_POST['sys_number'.$produce_id[$i]];
				 $real_number=$_POST['real_number'.$produce_id[$i]];
			 $this -> dbObj -> Execute("UPDATE  `".WEB_ADMIN_TABPOX."takestockdetail` SET   `real_number`=".$real_number." WHERE `warehouse_id`='".$_POST['warehouse_id']."' and `produce_id`='".$produce_id[$i]."' and `agencyid`='".$_SESSION["currentorgan"]."'");
			 
			 }
			//$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."takestock` SET `takestock_name` = '".$_POST["takestock_name"]."',`takestock_no` = '".$_POST["takestock_no"]."',`idnumber` = '".$_POST["idnumber"]."',`tel` = '".$_POST["tel"]."',`handphone` ='".$_POST["handphone"]."',`email` = '".$_POST["email"]."',`address` = '".$_POST["address"]."',`zipcode` = '".$_POST["zipcode"]."',`birthday` = '".$_POST["birthday"]."',`genderid` = '".$this->intnonull($_POST["genderid"])."', picpath='".$_POST["picpath"]."',ismarry=".$this->intnonull($_POST['ismarry']).", province='".$_POST["province"]."', city='".$_POST["city"]."',takestocklevelid='".$_POST["takestocklevelid"]."',attendancecode='".$_POST["attendancecode"]."' WHERE takestock_id =".$id);
//echo "UPDATE `".WEB_ADMIN_TABPOX."customer` SET `customer_name` = '".$_POST["customer_name"]."',`customer_no` = '".$_POST["customer_no"]."',`idnumber` = '".$_POST["idnumber"]."',`tel` = '".$this->intnonull($_POST["tel"])."',`handphone` ='".$this->intnonull($_POST["handphone"])."',`email` = '".$_POST["email"]."',`address` = '".$_POST["address"]."',`zipcode` = '".$this->intnonull($_POST["zipcode"])."',`birthday` = '".$_POST["birthday"]."',`genderid` = '".$this->intnonull($_POST["genderid"])."', picpath='".$_POST["picpath"]."',ismarry=".$this->intnonull($_POST['ismarry']).", province='".$_POST["province"]."', city='".$_POST["city"]."' WHERE customer_id =".$id;
		}
//$this -> quit($info.'成功！');
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}
function makeno($Prefix,$agency_no,$table,$column,$number,$id){
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by ".$column." desc limit 1");
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
	function intnonull($int){
	if ($int=="")$int=0;
		return $int;
	}

	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');location.href='checkstockhistory.php';</script>");
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
  