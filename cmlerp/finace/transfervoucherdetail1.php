<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
 function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='mingxi'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> mingxi();			
		}else if(isset($_GET['action']) && $_GET['action']=='printbill'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> printbill();			
		}else{
            parent::Main();
        }
    }
	
	function printbill(){
		 
		//定义模板
		$t = new Template('../template/finace');
		$t -> set_file('f','transfervoucherdetail_bill.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','main','m');		
		$t -> set_block('main','mainlist','ml');	
 
		//搜索 
        $accounttitle_id=$_GET["accounttitle_id"];
		$objecttype_id=$_GET["objecttype_id"];
		$objectid=$_GET["objectid"];
		$bgdate=$_GET["bgdate"];
		$enddate=$_GET["enddate"];
		$monthlybatch=$this->dbObj -> GetRow("SELECT * FROM `s_monthlybatch` WHERE `enddate`<'".$bgdate."' and agencyid =".$_SESSION['currentorgan']." order by monthlybatch_id desc");
		
		$dategapbg=$monthlybatch['enddate'];
		$monthlybatch_id=$monthlybatch['monthlybatch_id'];
		 
 
//分页		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:600;
			$offset = $pageid>0?($pageid-1)*$psize:0;

			//机构条件
			$agencyid=$_SESSION["currentorgan"];
			$agencyidcondition="  agencyid  in (".$agencyid.")";
			$agencyidcondition1="  B.agencyid  in (".$agencyid.")";
			//时间条件
			$bgdate=$_GET['bgdate'];
			$enddate=$_GET['enddate'];
			if($bgdate<>'' && $enddate<>''){
			$mlcondition='   date between  "'.$bgdate.'" and "'.$enddate.'"';
			$mlcondition1='   B.date between  "'.$bgdate.'" and "'.$enddate.'"';
			}else if($bgdate=='' && $enddate<>''){
			$mlcondition='   date between  "'.$enddate.'" and "'.$enddate.'"';
			$mlcondition1='   B.date between  "'.$enddate.'" and "'.$enddate.'"';
			}else if($bgdate<>'' && $enddate==''){
			$mlcondition='   date between  "'.$bgdate.'" and "'.$bgdate.'"';
			$mlcondition1='   B.date between  "'.$bgdate.'" and "'.$bgdate.'"';
			}else{
			$mlcondition='   date between  "'.date('Y-m-d', strtotime('-1 month')).'" and "'.date('Y-m-d',time()).'"';//默认查询一个月
			$mlcondition1='   B.date between  "'.date('Y-m-d', strtotime('-1 month')).'" and "'.date('Y-m-d',time()).'"';//默认查询一个月
			}
			
 		$alllend=0;
		$allloan=0;
		$allbalance=0;
			
			
			$t -> set_var('m');
			
			//循环编译科目
			
 	
			
			//科目编号条件
			$bgaccounttitle_no=$_GET['bgaccounttitle_no'];
			$endaccounttitle_no=$_GET['endaccounttitle_no'];
			if($bgaccounttitle_no<>'' && $endaccounttitle_no<>''){
			$mcondition='  WHERE accounttitle_no between  "'.$bgaccounttitle_no.'" and "'.$endaccounttitle_no.'"';
			$mcondition1='  WHERE A.accounttitle_no between  "'.$bgaccounttitle_no.'" and "'.$endaccounttitle_no.'"';
			}else if($bgaccounttitle_no=='' && $endaccounttitle_no<>''){
			$mcondition=' WHERE accounttitle_no between  "'.$endaccounttitle_no.'" and "'.$endaccounttitle_no.'"';
			$mcondition1=' WHERE A.accounttitle_no between  "'.$endaccounttitle_no.'" and "'.$endaccounttitle_no.'"';
			}else if($bgaccounttitle_no<>'' && $endaccounttitle_no==''){
			$mcondition=' WHERE accounttitle_no between  "'.$bgaccounttitle_no.'" and "'.$endaccounttitle_no.'"';
			$mcondition1=' WHERE A.accounttitle_no between  "'.$bgaccounttitle_no.'" and "'.$endaccounttitle_no.'"';
			}else{
			$mcondition='';
			}

			$accounttitle=$this->dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'accounttitle  '.$mcondition." ORDER BY accounttitle_no     LIMIT ".$offset." , ".$psize);
		 
			while($accounttitleinrrs= $accounttitle -> FetchRow()){
			 
			$t -> set_var('accounttitle_no',$accounttitleinrrs['accounttitle_no']);
			$t -> set_var('accounttitle_name',$accounttitleinrrs['accounttitle_name']);
			$t -> set_var('ml');
			//循环编译编译明细

			
			$sql='select A.*,B.date,B.abstract, B.fromtype,B.frombillid  from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN  '.WEB_ADMIN_TABPOX.'transfervoucher B  ON A.transfervoucher_id=B.transfervoucher_id where  A.accounttitle_id= '.$accounttitleinrrs["accounttitle_id"].'  and '.$mlcondition1." and ".$agencyidcondition1.' ORDER BY date DESC';
			
			$inrs = &$this -> dbObj -> Execute($sql." LIMIT ".$offset." , ".$psize);
			$inrrs=&$this -> dbObj ->GetArray($sql." LIMIT ".$offset." , ".$psize);
			$inrrscount=sizeof($inrrs);
			
			$result = &$this -> dbObj -> Execute($sql);	
			
			$count=$result->RecordCount();
			
			$t -> set_var('pagelist',$this -> page("?bgaccounttitle_no=".$bgaccounttitle_no."&endaccounttitle_no=".$endaccounttitle_no."&bgdate=".$bgdate."&enddate=".$enddate,$count,$psize,$pageid));		
			
	 		//期初余额
			//$sqlbg='select *  from '.WEB_ADMIN_TABPOX.'transfervoucherhistory1  where  accounttitle_id= '.$accounttitleinrrs["accounttitle_id"].'  and  date<"'.$bgdate.'"';


	 		//期初余额
			$sqlbg='select *  from '.WEB_ADMIN_TABPOX.'monthlybatchdetail  where  accounttitle_id= '.$accounttitleinrrs["accounttitle_id"].'  and  monthlybatch_id="'.$monthlybatch_id.'"  and '.$agencyidcondition;
		$sqlgap='select sum(A.lend) as  lend, sum(A.loan) as  loan  from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id where  A.accounttitle_id= '.$accounttitleinrrs["accounttitle_id"].'  and   B.date between "'.$dategapbg.'" and "'.$bgdate.'" and '.$agencyidcondition1;//间隙时间
		
			 $bgdata=$this -> dbObj -> GetRow($sqlbg);	
 			 $gapdata=$this -> dbObj -> GetRow($sqlgap);	
			 // $bgdata=$this -> dbObj -> GetRow($sqlbg."  order by date asc ,transfervoucherhistory_id desc  LIMIT ".$offset." , ".$psize);	
				
 
		
		
		$bglend=$bgdata['adduplend']==''?0:$bgdata['adduplend'];
		$bgloan=$bgdata['adduploan']==''?0:$bgdata['adduploan'];
 		$bgbalance=$bgdata['balance']==''?0:$bgdata['balance'];
		
		$gaplend=$gapdata['lend']==''?0:$gapdata['lend'];
		$gaploan=$gapdata['loan']==''?0:$gapdata['loan'];
		
		$bglend=$bglend+$bglend;
		$bgloan=$bgloan+$bgloan;
		$bgbalance=$bgbalance+$gaplend-$gaploan;


		$t -> set_var('bglend',$bglend);
		$t -> set_var('bgloan',$bgloan);	
		$bgbalance<0?$t -> set_var('bglendloan','贷'):$t -> set_var('bglendloan','借');	
		$t -> set_var('bgbalance',abs($bgbalance));
			
		$temlend=0;
		$temloan=0;
		$t -> set_var('angecy_name','');	
			  
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'transfervoucherdetail  where agencyid ='.$_SESSION["currentorgan"]);
			//echo 'select * from '.WEB_ADMIN_TABPOX.'account  where agencyid ='.$_SESSION["currentorgan"];
	     	//while ($inrrs = &$inrs -> FetchRow()) {
			for($k=$inrrscount-1;$k>=0;$k--){
				$t -> set_var($inrrs[$k]);
				$temlend=$temlend+$inrrs[$k]['lend'];
				$temloan=$temloan+$inrrs[$k]['loan'];
				
				$t -> set_var('agency_name',$this->dbObj -> GetOne('SELECT agency_name FROM '.WEB_ADMIN_TABPOX.'agency WHERE agency_id = '.$inrrs[$k]['agencyid']));
				
				
				$t -> set_var('transfervoucher_no',$inrrs['transfervoucher_id']);
				$t -> set_var('transfervoucher_no',$this->dbObj -> GetOne('SELECT transfervoucher_no FROM '.WEB_ADMIN_TABPOX.'transfervoucher WHERE transfervoucher_id = '.$inrrs[$k]["transfervoucher_id"]));

				 
				($balance+$temlend-$temloan)<0?$t -> set_var('lendloan','贷'):$t -> set_var('lendloan','借');
				$objecttype=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id ='.$inrrs[$k]["objecttype_id"]);
				 $t -> set_var('objecttype_name',$objecttype['objecttype_name']);
				$object=$this -> dbObj -> getrow('select *  from '.WEB_ADMIN_TABPOX.$objecttype["objecttypetable"].'  where '.$objecttype["objecttypetable"].'_id ='.$inrrs[$k]["objectid"]);
				 
				$t -> set_var('object_name',$object[$objecttype["objecttypetable"].'_name']);
				$t -> set_var('balance', abs($bgbalance+$temlend-$temloan));	
				 /*
				$transfervoucher=$this->dbObj -> GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'transfervoucher WHERE transfervoucher_id = '.$inrrs[$k]['transfervoucher_id']);
				
				$t -> set_var('transfervoucher_no',$transfervoucher['transfervoucher_no']);
				$t -> set_var('date',$transfervoucher['date']);
				$t -> set_var(memo,$inrrs[$k]['vmemo']);
				
				
				$balance=$balance+$inrrs[$k]['lend']-$inrrs[$k]['loan'];
				
				$t -> set_var('balance',abs($balance));
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs[$k]['transfervoucherdetail_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs[$k]['transfervoucherdetail_id']));			
				*/
				$t -> parse('ml','mainlist',true);
			}
/*			$inrs -> Close();	
		
		$totaldata = &$this -> dbObj -> GetRow($sql1);	
		$t -> set_var('totallend',$totaldata['totallend']+$bgdata['bglend']+$gapdata['lend']);
		$t -> set_var('totalloan',$totaldata['totalloan']+$bgdata['bgloan']+$gapdata['loan']);
		$totalbalance=$totaldata['totallend']+$bgdata['bglend']+$gapdata['lend']-$totaldata['totalloan']-$bgdata['bgloan']-$gapdata['loan'];
		$t -> set_var('totalbalance',abs($totalbalance));
		$totalbalance<0?$t -> set_var('totallendloan','贷'):$t -> set_var('totallendloan','借');
		
		$t -> set_var('objecttypelist',$this->selectlist('objecttype','objecttype_id','objecttype_name',$objecttype_id));
		$t -> set_var('cobject_name','');
		$t -> set_var('cobjectid','');
		if ($bgdate){
		$t -> set_var('bgdate',$bgdate);
		$t -> set_var('enddate',$enddate);	
		}else{
		$bgdate=$this -> dbObj -> getone('select enddate  from '.WEB_ADMIN_TABPOX.'monthlybatch  where agencyid ='.$_SESSION["currentorgan"].' order by monthlybatch_id desc');	
		
		if (!$bgdate){//如果没有月结过 查找最早的单时间。
			$bgdate=$this -> dbObj -> getone('select creattime  from '.WEB_ADMIN_TABPOX.'sell  where agencyid ='.$_SESSION["currentorgan"].' order by sell_id asc');	 
			$bgdate=date('Y-m-d',strtotime($bgdate)); 
		}else{ 
			$bgdate=date("Y-m-d",strtotime("$m+1 days",strtotime($bgdate)));//设置开始时间为上次月结的下一天
		}
		
		$t -> set_var('bgdate',$bgdate);
		$t -> set_var('enddate',date('Y-m-d'));	
		}*/
		
		//期末余额
		
		 //小计
		$t -> set_var('totallend',$temlend);
		$t -> set_var('totalloan',$temloan);
		$endlend=$temlend+$bglend;
		$endloan=$temloan+$bgloan;
		$totalbalance=$bgbalance+$endlend-$endloan;
		
 
		$t -> set_var('totalbalance', abs($totalbalance));		
		$totalbalance<0?$t -> set_var('totallendloan','贷'):$t -> set_var('totallendloan','借');	
		
		



		//$sqlend='select sum(lend) as totallend, sum(loan) as totalloan  from '.WEB_ADMIN_TABPOX.'transfervoucherhistory1  where  accounttitle_id= '.$accounttitleinrrs["accounttitle_id"].'  and '.$mlcondition." and ".$agencyidcondition;
		// $enddata=$this -> dbObj -> GetRow(sqlend);	
				
		//$endlend=$bgdata['lend']==''?0:$bgdata['lend'];
		//$endloan=$bgdata['loan']==''?0:$bgdata['loan'];
 		//$endbalance=$bgdata['balance']==''?0:$bgdata['balance'];
		

		
 
 
		//$endlend=$bgdata['bglend']+$enddata['totallend'];
		//$endloan=$bgdata['bgloan']+$enddata['totalloan'];
		//$endbalance=$bgbalance+totalbalance;
		$endbalance=$totalbalance;
		$t -> set_var('endlend',$endlend);
		$t -> set_var('endloan',$endloan);
		$t -> set_var('endbalance',abs($endbalance));
		$endbalance<0?$t -> set_var('endlendloan','贷'):$t -> set_var('endlendloan','借');	
		
		$alllend=$alllend+$endlend;
		$allloan=$allloan+$endloan;
		$allbalance=$allbalance+$endbalance;
		 
		if($endbalance<>0){
		$t -> parse('m','main',true);
		}
		}
		
		$t -> set_var('alllend',$alllend);
		$t -> set_var('allloan',$allloan);
		$t -> set_var('allbalance',abs($allbalance));	
		$allbalance<0?$t -> set_var('alllendloan','贷'):$t -> set_var('alllendloan','借');
		
		$t -> set_var('bgaccounttitlelist',$this->selectlist1('accounttitle','accounttitle_no','accounttitle_name',$bgaccounttitle_no));
		$t -> set_var('endaccounttitlelist',$this->selectlist1('accounttitle','accounttitle_no','accounttitle_name',$endaccounttitle_no));	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	
		$t -> set_var('bgdate',$bgdate);
		$t -> set_var('enddate',$enddate);
		$t -> set_var('add',$this -> getAddStr('img'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	
function disp(){	
		//定义模板
		
		$t = new Template('../template/finace');
		$t -> set_file('f','transfervoucherdetail1selectdate.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		
		$monthlybatch_name=date('m',time())."月份";
		$t -> set_var('monthlybatch_name',$monthlybatch_name);
		 //上次月结
		$bgdate=$this -> dbObj -> getone('select enddate  from '.WEB_ADMIN_TABPOX.'annualbatch  where agencyid ='.$_SESSION["currentorgan"].' order by annualbatch_id desc');	
		
		if (!$bgdate){//如果没有月结过 查找最早的单时间。
			$bgdate=$this -> dbObj -> getone('select creattime  from '.WEB_ADMIN_TABPOX.'sell  where agencyid ='.$_SESSION["currentorgan"].' order by sell_id asc');	 
			$bgdate=date('Y-m-d',strtotime($bgdate)); 
		}else{ 
			$bgdate=date("Y-m-d",strtotime("$m+1 days",strtotime($bgdate)));//设置开始时间为上次月结的下一天
		}
		$t -> set_var('accounttitlelist',$this->selectlist1('accounttitle','accounttitle_id','accounttitle_name',''));	
		$t -> set_var('bgdate',$bgdate);
		$t -> set_var('enddate',date('Y-m-d'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
 
	}
	function mingxi(){
		 
		//定义模板
		$t = new Template('../template/finace');
		$t -> set_file('f','transfervoucherdetail1.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','main','m');		
		$t -> set_block('main','mainlist','ml');	
		
		$fromtype=array("sell","sell","sell","sell","purchase","purchreturn","takestock","lossregister","lingyong");

		$viewbillfunction=array("viewsellbill","viewsellbill","viewsellbill","viewsellbill","viewpurchbill","viewpurchreturnbill","viewcheckstockbill","viewlossregisterbill","viewlingyongbill");
		$billname=array("销货单","销货单","销货单","销货单","进货单","采购退货单","盘点单","报损单","领用单"); 
		//搜索 
        $accounttitle_id=$_GET["accounttitle_id"];
		$objecttype_id=$_GET["objecttype_id"];
		$objectid=$_GET["objectid"];
		$bgdate=$_GET["bgdate"];
		$enddate=$_GET["enddate"];
		$monthlybatch=$this->dbObj -> GetRow("SELECT * FROM `s_monthlybatch` WHERE `enddate`<'".$bgdate."' and agencyid =".$_SESSION['currentorgan']." order by monthlybatch_id desc");
		
		$dategapbg=$monthlybatch['enddate'];
		$monthlybatch_id=$monthlybatch['monthlybatch_id'];
		 
 
//分页		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:1000;
			$offset = $pageid>0?($pageid-1)*$psize:0;

			//机构条件
			$agencyid=$_SESSION["currentorgan"];
			$agencyidcondition="  agencyid  in (".$agencyid.")";
			$agencyidcondition1="  B.agencyid  in (".$agencyid.")";
			//时间条件
			$bgdate=$_GET['bgdate'];
			$enddate=$_GET['enddate'];
			if($bgdate<>'' && $enddate<>''){
			$mlcondition='   date between  "'.$bgdate.'" and "'.$enddate.'"';
			$mlcondition1='   B.date between  "'.$bgdate.'" and "'.$enddate.'"';
			}else if($bgdate=='' && $enddate<>''){
			$mlcondition='   date between  "'.$enddate.'" and "'.$enddate.'"';
			$mlcondition1='   B.date between  "'.$enddate.'" and "'.$enddate.'"';
			}else if($bgdate<>'' && $enddate==''){
			$mlcondition='   date between  "'.$bgdate.'" and "'.$bgdate.'"';
			$mlcondition1='   B.date between  "'.$bgdate.'" and "'.$bgdate.'"';
			}else{
			$mlcondition='   date between  "'.date('Y-m-d', strtotime('-1 month')).'" and "'.date('Y-m-d',time()).'"';//默认查询一个月
			$mlcondition1='   B.date between  "'.date('Y-m-d', strtotime('-1 month')).'" and "'.date('Y-m-d',time()).'"';//默认查询一个月
			}
			
 		$alllend=0;
		$allloan=0;
		$allbalance=0;
			
			
			$t -> set_var('m');
			
			//循环编译科目
			
 	
			
			//科目编号条件
			$bgaccounttitle_no=$_GET['bgaccounttitle_no'];
			$endaccounttitle_no=$_GET['endaccounttitle_no'];
			if($bgaccounttitle_no<>'' && $endaccounttitle_no<>''){
			$mcondition='  WHERE accounttitle_no between  "'.$bgaccounttitle_no.'" and "'.$endaccounttitle_no.'"';
			$mcondition1='  WHERE A.accounttitle_no between  "'.$bgaccounttitle_no.'" and "'.$endaccounttitle_no.'"';
			}else if($bgaccounttitle_no=='' && $endaccounttitle_no<>''){
			$mcondition=' WHERE accounttitle_no between  "'.$endaccounttitle_no.'" and "'.$endaccounttitle_no.'"';
			$mcondition1=' WHERE A.accounttitle_no between  "'.$endaccounttitle_no.'" and "'.$endaccounttitle_no.'"';
			}else if($bgaccounttitle_no<>'' && $endaccounttitle_no==''){
			$mcondition=' WHERE accounttitle_no between  "'.$bgaccounttitle_no.'" and "'.$endaccounttitle_no.'"';
			$mcondition1=' WHERE A.accounttitle_no between  "'.$bgaccounttitle_no.'" and "'.$endaccounttitle_no.'"';
			}else{
			$mcondition='';
			}

			$accounttitle=$this->dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'accounttitle  '.$mcondition." ORDER BY accounttitle_no     LIMIT ".$offset." , ".$psize);
		 
			while($accounttitleinrrs= $accounttitle -> FetchRow()){
			 
			$t -> set_var('accounttitle_name',$accounttitleinrrs['accounttitle_name']);
			$t -> set_var('ml');
			//循环编译编译明细

			
			$sql='select A.*,B.date,B.abstract, B.fromtype,B.frombillid  from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN  '.WEB_ADMIN_TABPOX.'transfervoucher B  ON A.transfervoucher_id=B.transfervoucher_id where  A.accounttitle_id= '.$accounttitleinrrs["accounttitle_id"].'  and '.$mlcondition1." and ".$agencyidcondition1.' ORDER BY date DESC';
			
			$inrs = &$this -> dbObj -> Execute($sql." LIMIT ".$offset." , ".$psize);
			$inrrs=&$this -> dbObj ->GetArray($sql." LIMIT ".$offset." , ".$psize);
			$inrrscount=sizeof($inrrs);
			
			$result = &$this -> dbObj -> Execute($sql);	
			
			$count=$result->RecordCount();
			
			$t -> set_var('pagelist',$this -> page("?bgaccounttitle_no=".$bgaccounttitle_no."&endaccounttitle_no=".$endaccounttitle_no."&bgdate=".$bgdate."&enddate=".$enddate,$count,$psize,$pageid));		
			
	 		//期初余额
			//$sqlbg='select *  from '.WEB_ADMIN_TABPOX.'transfervoucherhistory1  where  accounttitle_id= '.$accounttitleinrrs["accounttitle_id"].'  and  date<"'.$bgdate.'"';


	 		//期初余额
			$sqlbg='select *  from '.WEB_ADMIN_TABPOX.'monthlybatchdetail  where  accounttitle_id= '.$accounttitleinrrs["accounttitle_id"].'  and  monthlybatch_id="'.$monthlybatch_id.'"  and '.$agencyidcondition;
		$sqlgap='select sum(A.lend) as  lend, sum(A.loan) as  loan  from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id where  A.accounttitle_id= '.$accounttitleinrrs["accounttitle_id"].'  and   B.date between "'.$dategapbg.'" and "'.$bgdate.'" and '.$agencyidcondition1;//间隙时间
		
			 $bgdata=$this -> dbObj -> GetRow($sqlbg);	
 			 $gapdata=$this -> dbObj -> GetRow($sqlgap);	
			 // $bgdata=$this -> dbObj -> GetRow($sqlbg."  order by date asc ,transfervoucherhistory_id desc  LIMIT ".$offset." , ".$psize);	
				
 
		
		
		$bglend=$bgdata['adduplend']==''?0:$bgdata['adduplend'];
		$bgloan=$bgdata['adduploan']==''?0:$bgdata['adduploan'];
 		$bgbalance=$bgdata['balance']==''?0:$bgdata['balance'];
		
		$gaplend=$gapdata['lend']==''?0:$gapdata['lend'];
		$gaploan=$gapdata['loan']==''?0:$gapdata['loan'];
		
		$bglend=$bglend+$bglend;
		$bgloan=$bgloan+$bgloan;
		$bgbalance=$bgbalance+$gaplend-$gaploan;


		$t -> set_var('bglend',$bglend);
		$t -> set_var('bgloan',$bgloan);	
		$bgbalance<0?$t -> set_var('bglendloan','贷'):$t -> set_var('bglendloan','借');	
		$t -> set_var('bgbalance',abs($bgbalance));
			
		$temlend=0;
		$temloan=0;
		$t -> set_var('angecy_name','');	
			  
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'transfervoucherdetail  where agencyid ='.$_SESSION["currentorgan"]);
			//echo 'select * from '.WEB_ADMIN_TABPOX.'account  where agencyid ='.$_SESSION["currentorgan"];
	     	//while ($inrrs = &$inrs -> FetchRow()) {
			for($k=$inrrscount-1;$k>=0;$k--){
				$t -> set_var($inrrs[$k]);
				$temlend=$temlend+$inrrs[$k]['lend'];
				$temloan=$temloan+$inrrs[$k]['loan'];
				

				
				
				$t -> set_var('transfervoucher_no',$inrrs['transfervoucher_id']);
				$t -> set_var('transfervoucher_no',$this->dbObj -> GetOne('SELECT transfervoucher_no FROM '.WEB_ADMIN_TABPOX.'transfervoucher WHERE transfervoucher_id = '.$inrrs[$k]["transfervoucher_id"]));
 				$t -> set_var('angecy_name',$this->dbObj -> GetOne('SELECT agency_name FROM '.WEB_ADMIN_TABPOX.'agency WHERE agency_id = '.$inrrs[$k]['agencyid']));
				
				($balance+$temlend-$temloan)<0?$t -> set_var('lendloan','贷'):$t -> set_var('lendloan','借');
				$objecttype=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id ='.$inrrs[$k]["objecttype_id"]);
				 $t -> set_var('objecttype_name',$objecttype['objecttype_name']);
				$object=$this -> dbObj -> getrow('select *  from '.WEB_ADMIN_TABPOX.$objecttype["objecttypetable"].'  where '.$objecttype["objecttypetable"].'_id ='.$inrrs[$k]["objectid"]);
				 
				$t -> set_var('object_name',$object[$objecttype["objecttypetable"].'_name']);
				$t -> set_var('balance', abs($bgbalance+$temlend-$temloan));	
				 /*
				$transfervoucher=$this->dbObj -> GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'transfervoucher WHERE transfervoucher_id = '.$inrrs[$k]['transfervoucher_id']);
				
				$t -> set_var('transfervoucher_no',$transfervoucher['transfervoucher_no']);
				$t -> set_var('date',$transfervoucher['date']);
				$t -> set_var(memo,$inrrs[$k]['vmemo']);
				
				
				$balance=$balance+$inrrs[$k]['lend']-$inrrs[$k]['loan'];
				
				$t -> set_var('balance',abs($balance));
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs[$k]['transfervoucherdetail_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs[$k]['transfervoucherdetail_id']));			
				*/
				$billno=$this->dbObj -> GetOne("SELECT ".$fromtype[$inrrs[$k]['fromtype']]."_no FROM ".WEB_ADMIN_TABPOX.$fromtype[$inrrs[$k]['fromtype']]." WHERE ".$fromtype[$inrrs[$k]['fromtype']]."_id= ".$inrrs[$k]['frombillid']);
				$frombill=$billname[$inrrs[$k]["fromtype"]].'<a style=" cursor: hand" onclick="'.$viewbillfunction[$inrrs[$k]["fromtype"]].'('.$inrrs[$k]["frombillid"].')">'.$billno.'</a>';
				$t -> set_var('frombill',$frombill);
				$t -> parse('ml','mainlist',true);
			}
/*			$inrs -> Close();	
		
		$totaldata = &$this -> dbObj -> GetRow($sql1);	
		$t -> set_var('totallend',$totaldata['totallend']+$bgdata['bglend']+$gapdata['lend']);
		$t -> set_var('totalloan',$totaldata['totalloan']+$bgdata['bgloan']+$gapdata['loan']);
		$totalbalance=$totaldata['totallend']+$bgdata['bglend']+$gapdata['lend']-$totaldata['totalloan']-$bgdata['bgloan']-$gapdata['loan'];
		$t -> set_var('totalbalance',abs($totalbalance));
		$totalbalance<0?$t -> set_var('totallendloan','贷'):$t -> set_var('totallendloan','借');
		
		$t -> set_var('objecttypelist',$this->selectlist('objecttype','objecttype_id','objecttype_name',$objecttype_id));
		$t -> set_var('cobject_name','');
		$t -> set_var('cobjectid','');
		if ($bgdate){
		$t -> set_var('bgdate',$bgdate);
		$t -> set_var('enddate',$enddate);	
		}else{
		$bgdate=$this -> dbObj -> getone('select enddate  from '.WEB_ADMIN_TABPOX.'monthlybatch  where agencyid ='.$_SESSION["currentorgan"].' order by monthlybatch_id desc');	
		
		if (!$bgdate){//如果没有月结过 查找最早的单时间。
			$bgdate=$this -> dbObj -> getone('select creattime  from '.WEB_ADMIN_TABPOX.'sell  where agencyid ='.$_SESSION["currentorgan"].' order by sell_id asc');	 
			$bgdate=date('Y-m-d',strtotime($bgdate)); 
		}else{ 
			$bgdate=date("Y-m-d",strtotime("$m+1 days",strtotime($bgdate)));//设置开始时间为上次月结的下一天
		}
		
		$t -> set_var('bgdate',$bgdate);
		$t -> set_var('enddate',date('Y-m-d'));	
		}*/
		
		//期末余额
		
		 //小计
		$t -> set_var('totallend',$temlend);
		$t -> set_var('totalloan',$temloan);
		$endlend=$temlend+$bglend;
		$endloan=$temloan+$bgloan;
		$totalbalance=$bgbalance+$endlend-$endloan;
		
 
		$t -> set_var('totalbalance', abs($totalbalance));		
		$totalbalance<0?$t -> set_var('totallendloan','贷'):$t -> set_var('totallendloan','借');	
		
		



		//$sqlend='select sum(lend) as totallend, sum(loan) as totalloan  from '.WEB_ADMIN_TABPOX.'transfervoucherhistory1  where  accounttitle_id= '.$accounttitleinrrs["accounttitle_id"].'  and '.$mlcondition." and ".$agencyidcondition;
		// $enddata=$this -> dbObj -> GetRow(sqlend);	
				
		//$endlend=$bgdata['lend']==''?0:$bgdata['lend'];
		//$endloan=$bgdata['loan']==''?0:$bgdata['loan'];
 		//$endbalance=$bgdata['balance']==''?0:$bgdata['balance'];
		

		
 
 
		//$endlend=$bgdata['bglend']+$enddata['totallend'];
		//$endloan=$bgdata['bgloan']+$enddata['totalloan'];
		//$endbalance=$bgbalance+totalbalance;
		$endbalance=$totalbalance;
		$t -> set_var('endlend',$endlend);
		$t -> set_var('endloan',$endloan);
		$t -> set_var('endbalance',abs($endbalance));
		$endbalance<0?$t -> set_var('endlendloan','贷'):$t -> set_var('endlendloan','借');	
		
		$alllend=$alllend+$endlend;
		$allloan=$allloan+$endloan;
		$allbalance=$allbalance+$endbalance;
		 
		if($endbalance<>0){
		$t -> parse('m','main',true);
		}
		}
		
		$t -> set_var('alllend',$alllend);
		$t -> set_var('allloan',$allloan);
		$t -> set_var('allbalance',abs($allbalance));	
		$allbalance<0?$t -> set_var('alllendloan','贷'):$t -> set_var('alllendloan','借');
		
		$t -> set_var('bgaccounttitlelist',$this->selectlist1('accounttitle','accounttitle_no','accounttitle_name',$bgaccounttitle_no));
		$t -> set_var('endaccounttitlelist',$this->selectlist1('accounttitle','accounttitle_no','accounttitle_name',$endaccounttitle_no));	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	
		$t -> set_var('bgdate',$bgdate);
		$t -> set_var('enddate',$enddate);	
		$t -> set_var('bgaccounttitle_no',$bgaccounttitle_no);
		$t -> set_var('endaccounttitle_no',$endaccounttitle_no);
		$t -> set_var('add',$this -> getAddStr('img'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	
	function goDispAppend(){

		$t = new Template('../template/basic');
		$t -> set_file('f','room_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');

		$t -> set_var('roomgroup',$this->roomgroup());	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('room_no',"");
		$t -> set_var('room_name',"");
		$t -> set_var('principal',"");
		$t -> set_var('memo',"");
		$t -> set_var('beds',"");
		$t -> set_var('tel',"");
		$t -> set_var('createtime',date("Y-m-d H:i:s"));
		$t -> set_var('userid',$this->getUid());		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail WHERE transfervoucherdetail_id = '.$updid));			
			$t -> set_var('error',"");	
			//$t -> set_var('roomgroup',$this->roomtype());	
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$inrs = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail WHERE transfervoucherdetail_id = '.$updid);	
			while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('roomgroup',$this->roomgroup($inrrs['roomgroup_id']));
			  }
			$inrs -> Close();
		}
		$t -> set_var('showeditdiv',"");	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}
	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail WHERE transfervoucherdetail_id in('.$delid.')');
		if(mysql_affected_rows())
		$this -> quit('删除成功！');
	else
		$this -> quit('删除失败！');
		
	}
	function goAppend(){
		$id = 0;
		$info = '';
		
		if($this -> isAppend){
			$info = '增加';	
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."room` (`room_name` ,`room_no` ,`memo` ,`agencyid` ,`beds` ,`principal`,`tel`,`roomgroup_id`)VALUES ( '".$_POST["room_name"]."', '".$_POST["room_no"]."',  '".$_POST["memo"]."','".$_SESSION["currentorgan"]."', '".$_POST["beds"]."', '".$_POST["principal"]."', '".$_POST["tel"]."',".$_POST["roomgroup_id"].")");
//echo "INSERT INTO `".WEB_ADMIN_TABPOX."room` (`room_name` ,`room_no` ,`memo` ,`agencyid` ,`beds` ,`principal`,`tel`，roomgroup_id )VALUES ( '".$_POST["room_name"]."', '".$_POST["room_no"]."',  '".$_POST["memo"]."','".$_SESSION["currentorgan"]."', '".$_POST["beds"]."', '".$_POST["principal"]."', '".$_POST["tel"]."',".$_POST["roomgroup_id"].")";
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];

			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."transfervoucherdetail SET room_name='".$_POST["room_name"]."', room_no='".$_POST["room_no"]."', memo='".$_POST["memo"]."',beds='".$_POST["beds"]."',principal='".$_POST["principal"]."',tel='".$_POST["tel"]."',roomgroup_id=".$_POST["roomgroup_id"]." WHERE transfervoucherdetail_id =".$id);

		}
//$this -> quit($info.'成功！');
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}
	function PPClass($areaid=0){
    		$area =$this -> dbObj -> Execute('Select * From s_area order by area_sort  asc,area_id');
			$count=$area->RecordCount();
			$i=0;
			$arr="";
			while ($rrs = &$area -> FetchRow()) {
			if($areaid==$rrs['area_id']){
				 $arr=$arr."<option value='".$rrs['area_id']."' selected>".$rrs['area_name']."</option>";
				 }else
				 {
				 $arr=$arr."<option value='".$rrs['area_id']."'>".$rrs['area_name']."</option>";
				 }
			     //$arr=$arr."[".$rrs['area_parent_id'].",".$rrs['area_id'].",'".$rrs['area_name']."']";
				 $i=$i+1;
				 //if ($i<$count){$arr=$arr.",";}

            }
           return $arr;

	}
	function PPClass_sale($userid=0){
    		$sale =$this -> dbObj -> Execute("SELECT * FROM ".WEB_ADMIN_TABPOX."grouprole gr, ".WEB_ADMIN_TABPOX."usergroup ug, ".WEB_ADMIN_TABPOX."user u WHERE gr.groupid = ug.groupid AND u.userid = ug.userid AND gr.roleid =". $this -> getValue('superintendentid') );
			$count=$sale->RecordCount();
			$i=0;
			$arrs="";
			while ($rrs = &$sale -> FetchRow()) {
			if($userid==$rrs['userid']){
				 $arrs=$arrs."<option value='".$rrs['userid']."' selected>".$rrs['username']."</option>";
				 }else
				 {
				 $arrs=$arrs."<option value='".$rrs['userid']."'>".$rrs['username']."</option>";
				 }
                  
				 //$arrs=$arrs."[0,".$rrs['userid'].",'".$rrs['username']."']";
			     //$arr=$arr."[".$rrs['area_parent_id'].",".$rrs['area_id'].",'".$rrs['area_name']."']";
				 $i=$i+1;
				 if ($i<$count){$arrs=$arrs.",";}

            }
           return $arrs;

	}
 		
		function selectlist1($table,$id,$name,$selectid=0){
			$no=$table."_no";
			
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table  );
			 
			$str='';
	     	while ($inrrs = &$inrs -> FetchRow()) {
			
			if ($inrrs[$id]==$selectid)
			$str =$str."<option value=".$inrrs[$no]." selected>".$inrrs[$no].$inrrs[$name]."</option>";	
			else
			$str =$str."<option value=".$inrrs[$no].">".$inrrs[$no].$inrrs[$name]."</option>";			
			}
			$inrs-> Close();	
			return  $str;
	    }		
	function roomgroup($roomgroup_id=0){
		$arr="";
			$inrs = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'roomgroup WHERE agencyid ='.$_SESSION["currentorgan"]);	
			while ($inrrs = &$inrs -> FetchRow()) {
				if($roomgroup_id==$inrrs['roomgroup_id']){				
				$arr =$arr."<option value='".$inrrs['roomgroup_id']."' selected>".$inrrs['roomgroup_name']."</option>";
				}elseif($inrrs['roomgroup_id']==0){
				$arr =$arr."<option value='0' selected> 请选择房间组</option>";
				}
				else{
				$arr  =$arr."<option value='".$inrrs['roomgroup_id']."' >".$inrrs['roomgroup_name']."</option>";
				}
			  }
			
			$inrs -> Close();		
		

		return $arr;
	}
	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');location.href='room.php';</script>");
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
  