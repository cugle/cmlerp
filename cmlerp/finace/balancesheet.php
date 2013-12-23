<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
	
 function Main()
    {   
        if(isset($_POST['action']) && $_POST['action']=='balancesheet'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> balancesheettype();			
		}else if(isset($_GET['action']) && $_GET['action']=='printbill'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> printbill();			
		}else{
            parent::Main();
        }
    }
function disp(){	
		//定义模板
		
		$t = new Template('../template/finace');
		$t -> set_file('f','balancesheetselectdate.html');
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
		$t -> set_var('bgdate',$bgdate);
		$t -> set_var('enddate',date('Y-m-d'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
 
	}
	function printbill(){
		$t = new Template('../template/finace');
		$t -> set_file('f','balancesheet_print.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','main','m');		
		$agencyid=$_SESSION["currentorgan"];
		//搜索
        // $bgdate=$_POST["bgdate"];
		//$enddate=$_POST["enddate"];
		$bgdate=$_GET["bgdate"];
		$enddate=$_GET["enddate"];		
		$datecondition=' date  between "'.$bgdate.'" and "'.$enddate.'"';	
		//$timecondition1=' B.creattime  between "'.$bgdate.'" and "'.$enddate.'"';	
		$bgyear=$this -> dbObj -> getone('select enddate  from '.WEB_ADMIN_TABPOX.'annualbatch  where agencyid ='.$_SESSION["currentorgan"].' order by annualbatch_id desc');	
		$annualbatchid=$this -> dbObj -> getone('select annualbatch_id  from '.WEB_ADMIN_TABPOX.'annualbatch  where agencyid ='.$_SESSION["currentorgan"].' order by annualbatch_id desc');	
		$annualbatchid=$annualbatchid==''?0:$annualbatchid;
		$bgdate=date("Y-m-d",strtotime("$m+1 days",strtotime($bgyear)));
		
		$bgdata=$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'annualbatchdetail A INNER JOIN   '.WEB_ADMIN_TABPOX.'annualbatch  B ON A.annualbatch_id=B.annualbatch_id   where B.enddate='.$bgyear);
		if (!$bgyear){//如果没有年结过 查找最早的单时间。
			
			 $bgyear=date("Y",time());//
			 $bgyear=$bgyear."-01-01";//设置为本年一月一日
		} 
		
		 
		$sqlzc='select * from '.WEB_ADMIN_TABPOX.'balancesheettype  where type=1  ORDER BY orderid ASC ,balancesheettype_id  ASC';//资产
		$sqlfz='select * from '.WEB_ADMIN_TABPOX.'balancesheettype  where type=0   ORDER BY orderid ASC ,balancesheettype_id  ASC';//负责		
		$sqlaccounttitle='select * from '.WEB_ADMIN_TABPOX.'accounttitle ';//科目	
		 
		$inrsaccounttitle = &$this -> dbObj -> Execute($sqlaccounttitle);
		$sqltransfervoucherhistory1='';
		$sqlbgtransfervoucherhistory1='';
		 
		while($inrrsaccounttile= $inrsaccounttitle->FetchRow()){
		$sqltransfervoucherhistory1=$sqltransfervoucherhistory1<>''?$sqltransfervoucherhistory1.' union ':'';
			$sqltransfervoucherhistory1=$sqltransfervoucherhistory1.' (select A.accounttitle_id,A.transfervoucher_id,B.date,sum(A.lend) as lend ,sum(A.loan) as loan from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN  '.WEB_ADMIN_TABPOX.'transfervoucher  B ON A.transfervoucher_id=B.transfervoucher_id where A.accounttitle_id='.$inrrsaccounttile["accounttitle_id"].' and B.agencyid in ('.$agencyid.') and  B.date  between "'.$bgyear.'" and "'.$enddate.'" having sum(A.lend) is not null )';
		//$sqlbgtransfervoucherhistory1=$sqlbgtransfervoucherhistory1<>''?$sqlbgtransfervoucherhistory1.' union ':'';	
			//$sqlbgtransfervoucherhistory1=$sqlbgtransfervoucherhistory1.' (select * from '.WEB_ADMIN_TABPOX.'transfervoucherhistory1  where accounttitle_id='.$inrrsaccounttile["accounttitle_id"].' and agencyid in ('.$agencyid.')  and  date  between "'.$bgyear.'" and "'.$enddate.'"  order by `transfervoucherhistory_id` asc limit 1 )';			
		}
		 
		//$inrsaccounttitle = &$this -> dbObj -> Execute($sqltransfervoucherhistory1);


		//$this -> dbObj -> Execute('CREATE TEMPORARY TABLE IF NOT EXISTS `'.WEB_ADMIN_TABPOX.'balancesheettembg'.$this->getUid().'` '.$sqlbgtransfervoucherhistory1);
		//$sqlbgtransfervoucherhistory1='select * from '.WEB_ADMIN_TABPOX.'balancesheettembg'.$this->getUid();
		$this -> dbObj -> Execute('CREATE TEMPORARY TABLE IF NOT EXISTS `'.WEB_ADMIN_TABPOX.'balancesheettemend'.$this->getUid().'` '.$sqltransfervoucherhistory1);
		
		$sqltransfervoucherhistory1='select * from '.WEB_ADMIN_TABPOX.'balancesheettemend'.$this->getUid();
		
$zcrows= $this -> dbObj -> GetOne('select count(*) from '.WEB_ADMIN_TABPOX.'balancesheettype  where type=1  and balancesheettype_no<>""');//资产负责列数
		$fzrows= $this -> dbObj -> GetOne('select count(*) from '.WEB_ADMIN_TABPOX.'balancesheettype  where type=0  and balancesheettype_no<>""');//负债列数
		 
		
		//$zcdata = &$this -> dbObj -> Execute($sqlzc);//资产
		//$fzdata = &$this -> dbObj -> Execute($sqlfz);//负责	
		
		$zcdata=&$this -> dbObj ->GetArray($sqlzc);
		$countzc=sizeof($zcdata);
		$fzdata=&$this -> dbObj ->GetArray($sqlfz);
		$countfz=sizeof($fzdata);
		$zcno=1;
		$fzno=$zcrows+1;

		if($countzc>$countfz){$k=$countzc;}else{$k=$countfz;}
		for($i=0;$i<$k;$i++){
		//$sqlbgzc=' select  A.*,sum(A.balance-A.lend+A.loan) as endbalance  from ('.$sqlbgtransfervoucherhistory1.')  A  WHERE A.accounttitle_id in ('.$zcdata[$i]["accounttitleid"].') ';
		//资产
		//$sqlbgzcreduce=' select  A.*,sum(A.balance-A.lend+A.loan) as endbalance  from ('.$sqlbgtransfervoucherhistory1.')  A  WHERE A.accounttitle_id in ('.$zcdata[$i]["addorreduce"].') ';
		
		
		$sqlbgzc='SELECT * FROM '.WEB_ADMIN_TABPOX.'annualbatchdetail A INNER JOIN   '.WEB_ADMIN_TABPOX.'annualbatch  B ON A.annualbatch_id=B.annualbatch_id   where B.annualbatch_id='.$annualbatchid.' and A.accounttitle_id in ('.$zcdata[$i]["accounttitleid"].') ';
		$sqlzc=' select A.*,sum(A.lend) as lend,sum(A.loan) as loan from ('.$sqltransfervoucherhistory1.')  A  WHERE A.accounttitle_id in ('.$zcdata[$i]["accounttitleid"].') ';
		$sqlzcreduce=' select A.*,sum(A.lend) as lend,sum(A.loan) as loan from ('.$sqltransfervoucherhistory1.')  A  WHERE A.accounttitle_id in ('.$zcdata[$i]["addorreduce"].') ';
		//$sqlbgfz='SELECT * FROM '.WEB_ADMIN_TABPOX.'annualbatchdetail A INNER JOIN   '.WEB_ADMIN_TABPOX.'annualbatch  B ON A.annualbatch_id=B.annualbatch_id   where B.annualbatch_id='.$annualbatchid.' and A.accounttitle_id in ('.$zcdata[$i]["addorreduce"].') ';
		//$sqlbgfz=' select A.*, sum(A.balance-A.lend+A.loan) as endbalance from ('.$sqlbgtransfervoucherhistory1.')  A  WHERE A.accounttitle_id in ('.$fzdata[$i]["accounttitleid"].') ';
		//负债
		$sqlbgfz='SELECT * FROM '.WEB_ADMIN_TABPOX.'annualbatchdetail A INNER JOIN   '.WEB_ADMIN_TABPOX.'annualbatch  B ON A.annualbatch_id=B.annualbatch_id   where B.annualbatch_id='.$annualbatchid.' and A.accounttitle_id in ('.$fzdata[$i]["accounttitleid"].') ';
		$sqlfz=' select  A.*,sum(A.lend) as lend,sum(A.loan) as loan  from ('.$sqltransfervoucherhistory1.')  A  WHERE A.accounttitle_id in ('.$fzdata[$i]["accounttitleid"].') ';
		$sqlfzreduce=' select A.*,sum(A.lend) as lend,sum(A.loan) as loan from ('.$sqlbgtransfervoucherhistory1.')  A  WHERE A.accounttitle_id in ('.$fzdata[$i]["addorreduce"].') ';
		
		 

		if($zcdata[$i]['parentid']<>0){
					 
		$parentid1=$this -> dbObj ->GetOne('SELECT  parentid FROM '.WEB_ADMIN_TABPOX.'balancesheettype where balancesheettype_id='.$zcdata[$i]["parentid"]);
						
		if($parentid1==0){
		$space="&nbsp;&nbsp;&nbsp;&nbsp;";
		}else{
		$space="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		}
		$t -> set_var('zcname',$space.$zcdata[$i]['balancesheettype_name']);
		}else{
		$space='';
		$t -> set_var('zcname','<strong>'.$space.$zcdata[$i]['balancesheettype_name'].'</strong>');
		}		
		
		
		if($zcdata[$i]['balancesheettype_no']<>""){
		
		$t -> set_var('zcno',$zcno);
		$zcno=$zcno+1; 
 
		$bgzc=$this -> dbObj -> GetRow($sqlbgzc);//资产期初 包括取反
		//$bgzcreduce=$this -> dbObj -> GetRow($sqlbgzcreduce);//资产期初 取反
		$zc=$this -> dbObj -> GetRow($sqlzc); //资产期末
		$zcreduce=$this -> dbObj -> GetRow($sqlzcreduce); //资产期末
		$zcbegining= $bgzc['balance'] ;
		$zcend=($bgzc['balance']+$zc['lend']-$zc['loan']-2*($bgzcreduce['lend']-$bgzcreduce['loan']))?($bgzc['balance']+$zc['lend']-$zc['loan']-2*($bgzcreduce['lend']-$bgzcreduce['loan'])):0;
		 

		 
		$t -> set_var('zcbegining',sprintf ("%01.2f",$zcbegining));
		$t -> set_var('zcend',sprintf ("%01.2f",$zcend));

		}else{
		$t -> set_var('zcno','');
		$t -> set_var('zcbegining','');
		$t -> set_var('zcend','');

		}
		
		
		//负责
		if($i<$countfz){
		if($fzdata[$i]['parentid']<>0){
					 
		$parentid2=$this -> dbObj ->GetOne('SELECT  parentid FROM '.WEB_ADMIN_TABPOX.'balancesheettype where balancesheettype_id='.$fzdata[$i]["parentid"]);
						
		if($parentid2==0){
		$space="&nbsp;&nbsp;&nbsp;&nbsp;";
		}else{
		$space="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		}
		$fzname=$space.$fzdata[$i]['balancesheettype_name'];
		$t -> set_var('fzname',$fzname);
		}else{
		$space='';
		$fzname='<strong>'.$space.$fzdata[$i]['balancesheettype_name'].'</strong>';
		$t -> set_var('fzname',$fzname);
		}
		}		
		
		if($fzdata[$i]['balancesheettype_no']<>""){
		$t -> set_var('fzno',$fzno);
		$fzno=$fzno+1;
		 
		$bgfz=$this -> dbObj -> GetRow($sqlbgfz); //负责期初
		$fz=$this -> dbObj -> GetRow($sqlfz); 		//负责期末
		$fzreduce=$this -> dbObj -> GetRow($sqlfzreduce); //负责期末
		$fzbegining=$bgfz['balance']?$bgfz['balance']:0;
		 
		$zcend=($bgfz['balance']+$fz['lend']-$fz['loan']-2*($bgfzreduce['lend']-$bgfzreduce['loan']))?($bgfz['balance']+$fz['lend']-$fz['loan']-2*($bgfzreduce['lend']-$bgfzreduce['loan'])):0;
		
		$t -> set_var('fzbegining',sprintf ("%01.2f",abs($fzbegining)));
		$t -> set_var('fzend',sprintf ("%01.2f",abs($zcend)));
		
		}else{
		$t -> set_var('fzno','');
		$t -> set_var('fzbegining','');
		$t -> set_var('fzend','');
		}
		
		if($countfz==$i+1 && $k>$countfz){
		$t -> set_var('fzname','');
		$t -> set_var('fzno','');
		$t -> set_var('fzbegining','');
		$t -> set_var('fzend','');
		}else if ($k==$i+1 && $k>$countfz){
		$t -> set_var('fzname',$fzname);
		$t -> set_var('fzno',$zcrows+$fzrows);
		$t -> set_var('fzbegining',sprintf ("%01.2f",abs($fzbegining)));
		$t -> set_var('fzend',sprintf ("%01.2f",abs($zcend)));		
		}
 		$t -> parse('m','main',true);
		}//end for 
		
 
		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 	
		$t -> set_var('enddate',$_GET['enddate']);
		$t -> set_var('bgdate',$_GET['bgdate']);
		$t -> set_var('hangye_name','有限责任公司');

		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');		
		$t -> set_var('add',$this -> getAddStr('img'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');	}
	
	function balancesheettype(){
		$t = new Template('../template/finace');
		$t -> set_file('f','balancesheet.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','main','m');		
		$agencyid=$_SESSION["currentorgan"];
		//搜索
        // $bgdate=$_POST["bgdate"];
		//$enddate=$_POST["enddate"];
		$bgdate=$_POST["bgdate"];
		$enddate=$_POST["enddate"];		
		$datecondition=' date  between "'.$bgdate.'" and "'.$enddate.'"';	
		//$timecondition1=' B.creattime  between "'.$bgdate.'" and "'.$enddate.'"';	
		$bgyear=$this -> dbObj -> getone('select enddate  from '.WEB_ADMIN_TABPOX.'annualbatch  where agencyid ='.$_SESSION["currentorgan"].' order by annualbatch_id desc');	
		$annualbatchid=$this -> dbObj -> getone('select annualbatch_id  from '.WEB_ADMIN_TABPOX.'annualbatch  where agencyid ='.$_SESSION["currentorgan"].' order by annualbatch_id desc');	
		$annualbatchid=$annualbatchid==''?0:$annualbatchid;
		$bgdate=date("Y-m-d",strtotime("$m+1 days",strtotime($bgyear)));
		
		$bgdata=$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'annualbatchdetail A INNER JOIN   '.WEB_ADMIN_TABPOX.'annualbatch  B ON A.annualbatch_id=B.annualbatch_id   where B.enddate='.$bgyear);
		if (!$bgyear){//如果没有年结过 查找最早的单时间。
			
			 $bgyear=date("Y",time());//
			 $bgyear=$bgyear."-01-01";//设置为本年一月一日
		} 
		
		 
		$sqlzc='select * from '.WEB_ADMIN_TABPOX.'balancesheettype  where type=1  ORDER BY orderid ASC ,balancesheettype_id  ASC';//资产
		$sqlfz='select * from '.WEB_ADMIN_TABPOX.'balancesheettype  where type=0   ORDER BY orderid ASC ,balancesheettype_id  ASC';//负责		
		$sqlaccounttitle='select * from '.WEB_ADMIN_TABPOX.'accounttitle ';//科目	
		 
		$inrsaccounttitle = &$this -> dbObj -> Execute($sqlaccounttitle);
		$sqltransfervoucherhistory1='';
		$sqlbgtransfervoucherhistory1='';
		 
		while($inrrsaccounttile= $inrsaccounttitle->FetchRow()){
		$sqltransfervoucherhistory1=$sqltransfervoucherhistory1<>''?$sqltransfervoucherhistory1.' union ':'';
			$sqltransfervoucherhistory1=$sqltransfervoucherhistory1.' (select A.accounttitle_id,A.transfervoucher_id,B.date,sum(A.lend) as lend ,sum(A.loan) as loan from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN  '.WEB_ADMIN_TABPOX.'transfervoucher  B ON A.transfervoucher_id=B.transfervoucher_id where A.accounttitle_id='.$inrrsaccounttile["accounttitle_id"].' and B.agencyid in ('.$agencyid.') and  B.date  between "'.$bgyear.'" and "'.$enddate.'" having sum(A.lend) is not null )';
		//$sqlbgtransfervoucherhistory1=$sqlbgtransfervoucherhistory1<>''?$sqlbgtransfervoucherhistory1.' union ':'';	
			//$sqlbgtransfervoucherhistory1=$sqlbgtransfervoucherhistory1.' (select * from '.WEB_ADMIN_TABPOX.'transfervoucherhistory1  where accounttitle_id='.$inrrsaccounttile["accounttitle_id"].' and agencyid in ('.$agencyid.')  and  date  between "'.$bgyear.'" and "'.$enddate.'"  order by `transfervoucherhistory_id` asc limit 1 )';			
		}
		 
		//$inrsaccounttitle = &$this -> dbObj -> Execute($sqltransfervoucherhistory1);


		//$this -> dbObj -> Execute('CREATE TEMPORARY TABLE IF NOT EXISTS `'.WEB_ADMIN_TABPOX.'balancesheettembg'.$this->getUid().'` '.$sqlbgtransfervoucherhistory1);
		//$sqlbgtransfervoucherhistory1='select * from '.WEB_ADMIN_TABPOX.'balancesheettembg'.$this->getUid();
		$this -> dbObj -> Execute('CREATE TEMPORARY TABLE IF NOT EXISTS `'.WEB_ADMIN_TABPOX.'balancesheettemend'.$this->getUid().'` '.$sqltransfervoucherhistory1);
		
		$sqltransfervoucherhistory1='select * from '.WEB_ADMIN_TABPOX.'balancesheettemend'.$this->getUid();
		
$zcrows= $this -> dbObj -> GetOne('select count(*) from '.WEB_ADMIN_TABPOX.'balancesheettype  where type=1  and balancesheettype_no<>""');//资产负责列数
		$fzrows= $this -> dbObj -> GetOne('select count(*) from '.WEB_ADMIN_TABPOX.'balancesheettype  where type=0  and balancesheettype_no<>""');//负债列数
		 
		
		//$zcdata = &$this -> dbObj -> Execute($sqlzc);//资产
		//$fzdata = &$this -> dbObj -> Execute($sqlfz);//负责	
		
		$zcdata=&$this -> dbObj ->GetArray($sqlzc);
		$countzc=sizeof($zcdata);
		$fzdata=&$this -> dbObj ->GetArray($sqlfz);
		$countfz=sizeof($fzdata);
		$zcno=1;
		$fzno=$zcrows+1;

		if($countzc>$countfz){$k=$countzc;}else{$k=$countfz;}
		for($i=0;$i<$k;$i++){
		//$sqlbgzc=' select  A.*,sum(A.balance-A.lend+A.loan) as endbalance  from ('.$sqlbgtransfervoucherhistory1.')  A  WHERE A.accounttitle_id in ('.$zcdata[$i]["accounttitleid"].') ';
		//资产
		//$sqlbgzcreduce=' select  A.*,sum(A.balance-A.lend+A.loan) as endbalance  from ('.$sqlbgtransfervoucherhistory1.')  A  WHERE A.accounttitle_id in ('.$zcdata[$i]["addorreduce"].') ';
		
		
		$sqlbgzc='SELECT * FROM '.WEB_ADMIN_TABPOX.'annualbatchdetail A INNER JOIN   '.WEB_ADMIN_TABPOX.'annualbatch  B ON A.annualbatch_id=B.annualbatch_id   where B.annualbatch_id='.$annualbatchid.' and A.accounttitle_id in ('.$zcdata[$i]["accounttitleid"].') ';
		$sqlzc=' select A.*,sum(A.lend) as lend,sum(A.loan) as loan from ('.$sqltransfervoucherhistory1.')  A  WHERE A.accounttitle_id in ('.$zcdata[$i]["accounttitleid"].') ';
		$sqlzcreduce=' select A.*,sum(A.lend) as lend,sum(A.loan) as loan from ('.$sqltransfervoucherhistory1.')  A  WHERE A.accounttitle_id in ('.$zcdata[$i]["addorreduce"].') ';
		//$sqlbgfz='SELECT * FROM '.WEB_ADMIN_TABPOX.'annualbatchdetail A INNER JOIN   '.WEB_ADMIN_TABPOX.'annualbatch  B ON A.annualbatch_id=B.annualbatch_id   where B.annualbatch_id='.$annualbatchid.' and A.accounttitle_id in ('.$zcdata[$i]["addorreduce"].') ';
		//$sqlbgfz=' select A.*, sum(A.balance-A.lend+A.loan) as endbalance from ('.$sqlbgtransfervoucherhistory1.')  A  WHERE A.accounttitle_id in ('.$fzdata[$i]["accounttitleid"].') ';
		//负债
		$sqlbgfz='SELECT * FROM '.WEB_ADMIN_TABPOX.'annualbatchdetail A INNER JOIN   '.WEB_ADMIN_TABPOX.'annualbatch  B ON A.annualbatch_id=B.annualbatch_id   where B.annualbatch_id='.$annualbatchid.' and A.accounttitle_id in ('.$fzdata[$i]["accounttitleid"].') ';
		$sqlfz=' select  A.*,sum(A.lend) as lend,sum(A.loan) as loan  from ('.$sqltransfervoucherhistory1.')  A  WHERE A.accounttitle_id in ('.$fzdata[$i]["accounttitleid"].') ';
		$sqlfzreduce=' select A.*,sum(A.lend) as lend,sum(A.loan) as loan from ('.$sqlbgtransfervoucherhistory1.')  A  WHERE A.accounttitle_id in ('.$fzdata[$i]["addorreduce"].') ';
		
		 

		if($zcdata[$i]['parentid']<>0){
					 
		$parentid1=$this -> dbObj ->GetOne('SELECT  parentid FROM '.WEB_ADMIN_TABPOX.'balancesheettype where balancesheettype_id='.$zcdata[$i]["parentid"]);
						
		if($parentid1==0){
		$space="&nbsp;&nbsp;&nbsp;&nbsp;";
		}else{
		$space="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		}
		$t -> set_var('zcname',$space.$zcdata[$i]['balancesheettype_name']);
		}else{
		$space='';
		$t -> set_var('zcname','<strong>'.$space.$zcdata[$i]['balancesheettype_name'].'</strong>');
		}		
		
		
		if($zcdata[$i]['balancesheettype_no']<>""){
		
		$t -> set_var('zcno',$zcno);
		$zcno=$zcno+1; 
 
		$bgzc=$this -> dbObj -> GetRow($sqlbgzc);//资产期初 包括取反
		//$bgzcreduce=$this -> dbObj -> GetRow($sqlbgzcreduce);//资产期初 取反
		$zc=$this -> dbObj -> GetRow($sqlzc); //资产期末
		$zcreduce=$this -> dbObj -> GetRow($sqlzcreduce); //资产期末
		$zcbegining= $bgzc['balance'] ;
		$zcend=($bgzc['balance']+$zc['lend']-$zc['loan']-2*($bgzcreduce['lend']-$bgzcreduce['loan']))?($bgzc['balance']+$zc['lend']-$zc['loan']-2*($bgzcreduce['lend']-$bgzcreduce['loan'])):0;
		 

		 
		$t -> set_var('zcbegining',sprintf ("%01.2f",$zcbegining));
		$t -> set_var('zcend',sprintf ("%01.2f",$zcend));

		}else{
		$t -> set_var('zcno','');
		$t -> set_var('zcbegining','');
		$t -> set_var('zcend','');

		}
		
		
		//负责
		if($i<$countfz){
		if($fzdata[$i]['parentid']<>0){
					 
		$parentid2=$this -> dbObj ->GetOne('SELECT  parentid FROM '.WEB_ADMIN_TABPOX.'balancesheettype where balancesheettype_id='.$fzdata[$i]["parentid"]);
						
		if($parentid2==0){
		$space="&nbsp;&nbsp;&nbsp;&nbsp;";
		}else{
		$space="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		}
		$fzname=$space.$fzdata[$i]['balancesheettype_name'];
		$t -> set_var('fzname',$fzname);
		}else{
		$space='';
		$fzname='<strong>'.$space.$fzdata[$i]['balancesheettype_name'].'</strong>';
		$t -> set_var('fzname',$fzname);
		}
		}		
		
		if($fzdata[$i]['balancesheettype_no']<>""){
		$t -> set_var('fzno',$fzno);
		$fzno=$fzno+1;
		 
		$bgfz=$this -> dbObj -> GetRow($sqlbgfz); //负责期初
		$fz=$this -> dbObj -> GetRow($sqlfz); 		//负责期末
		$fzreduce=$this -> dbObj -> GetRow($sqlfzreduce); //负责期末
		$fzbegining=$bgfz['balance']?$bgfz['balance']:0;
		 
		$zcend=($bgfz['balance']+$fz['lend']-$fz['loan']-2*($bgfzreduce['lend']-$bgfzreduce['loan']))?($bgfz['balance']+$fz['lend']-$fz['loan']-2*($bgfzreduce['lend']-$bgfzreduce['loan'])):0;
		
		$t -> set_var('fzbegining',sprintf ("%01.2f",abs($fzbegining)));
		$t -> set_var('fzend',sprintf ("%01.2f",abs($zcend)));
		
		}else{
		$t -> set_var('fzno','');
		$t -> set_var('fzbegining','');
		$t -> set_var('fzend','');
		}
		
		if($countfz==$i+1 && $k>$countfz){
		$t -> set_var('fzname','');
		$t -> set_var('fzno','');
		$t -> set_var('fzbegining','');
		$t -> set_var('fzend','');
		}else if ($k==$i+1 && $k>$countfz){
		$t -> set_var('fzname',$fzname);
		$t -> set_var('fzno',$zcrows+$fzrows);
		$t -> set_var('fzbegining',sprintf ("%01.2f",abs($fzbegining)));
		$t -> set_var('fzend',sprintf ("%01.2f",abs($zcend)));		
		}
 		$t -> parse('m','main',true);
		}//end for 
		
 
		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 	
		$t -> set_var('enddate',$_POST['enddate']);
		$t -> set_var('bgdate',$_POST['bgdate']);
		$t -> set_var('hangye_name','有限责任公司');

		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');		
		$t -> set_var('add',$this -> getAddStr('img'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	
 function deep($id){
 $sql='select * FROM `s_balancesheettype` WHERE `balancesheettype_id`= '.$id;
 $data=$this -> dbObj -> GetRow($sql);
 $deep=0;
 while($data['parentid']<>0){
 
 	$sql='select * FROM `s_balancesheettype` WHERE `balancesheettype_id`= '.$data['balancesheettype_id'];
 	$data=$this -> dbObj -> GetRow($sql);
	$deep= $deep+1;
 }
 return $deep;
 }
	
	
	function goDispAppend(){

		$t = new Template('../template/finace');
		$t -> set_file('f','balancesheettype_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
			
		//$t -> set_var('balancesheettype_no',"");
		$Prefix='VH';
		$agency_no=$_SESSION["agency_no"].date('ym',time());
		$table=WEB_ADMIN_TABPOX.'balancesheettype';
		$column='balancesheettype_no';
		$number=5;
		$id='balancesheettype_id';	
		
		$t -> set_var('balancesheettype_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));	
		$t -> set_var('suppliers_no',"");	
		$t -> set_var('creattime',date('Y-m-d H:i:s',time()));	
		$t -> set_var('cloan',"0");
		$t -> set_var('clend',"0");
		$t -> set_var('totalloan',"0");
		$t -> set_var('totallend',"0");		
		$t -> set_var('cobjectid',"");
		$t -> set_var('cobject_name',"");

		$t -> set_var('employee_name',"");	
		$t -> set_var('employee_id',"");	
		$t -> set_var('date',date('Y-m-d',time()));	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");
		$t -> set_var('comdisplay',"none");		
		$t -> set_var('memo',"");
		$t -> set_var('man',$this -> dbObj -> getone('select B.employee_name from '.WEB_ADMIN_TABPOX.'user A INNER JOIN '.WEB_ADMIN_TABPOX.'employee B ON A.employee_id=B.employee_id  where A.userid = '.$this->getUid()));
		$t -> set_var('accounttitlelist',$this->selectlist('accounttitle','accounttitle_id','accounttitle_name',''));	
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
			 
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'balancesheettype WHERE balancesheettype_id = '.$updid);
			
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
		 
		$t -> set_var('cbalancesheettypeprice',0);
		
 					
		//设置消耗品列表
			
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'balancesheettypedetail  where balancesheettype_id  ='.$updid);
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
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();			
			$acount = &$this -> dbObj -> GetOne('select count(*) as acount from '.WEB_ADMIN_TABPOX.'balancesheettypedetail  where balancesheettype_id  ='.$updid);
			$t -> set_var('accounttitlelist',$this->selectlist('accounttitle','accounttitle_id','accounttitle_name',''));	
			$t -> set_var('objecttypelist',$this->selectlist('objecttype','objecttype_id','objecttype_name',''));	
			$t -> set_var('recordcount',$acount);
			$t -> set_var('totallend',$totallend);	
			$t -> set_var('totalloan',$totalloan);	
		// 修改消耗品
		 
		    if($_GET['cbalancesheettypedetail_id']!=''){
			//$t -> set_var('cs');
		   $t -> set_var('caction','upd');	
            
			$inrs2 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'balancesheettypedetail  where balancesheettypedetail_id ='.$_GET['cbalancesheettypedetail_id']);
			//$t -> set_var($inrs);
			$t -> set_var('accounttitlelist',$this->selectlist('accounttitle','accounttitle_id','accounttitle_name',$inrs2['accounttitle_id']));	
			$t -> set_var('objecttypelist',$this->selectlist('objecttype','objecttype_id','objecttype_name',$inrs2['objecttype_id']));	
		    $t -> set_var('cupdid',$inrs2['produce_id']);	
			$t -> set_var('proupdid',$_GET['cbalancesheettypedetail_id']);	
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
			$t -> set_var('cbalancesheettypeprice',$inrs2['balancesheettypeprice']);
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
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
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
	  if($_GET['balancesheettypedetail_id']==''){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'balancesheettype WHERE balancesheettype_id in('.$delid.')');
		}else{
		 
		 $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'balancesheettypedetail WHERE balancesheettypedetail_id in('.$_GET['balancesheettypedetail_id'].')');
		
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
			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."balancesheettype` (`balancesheettype_no`,`date`,`agencyid`,`creattime`,`man`) VALUES ('" .$_POST["balancesheettype_no"]."','".$_POST["date"]."','".$_SESSION["currentorgan"]."','".$_POST["creattime"]."', '".$_POST["man"]."')");
 
			$id = $this -> dbObj -> Insert_ID();
//echo "INSERT INTO `".WEB_ADMIN_TABPOX."balancesheettype` (`balancesheettype_no`,`balancesheettype_time`,`acount`,`warehouse_id`,`suppliers_id`,`employee_id`,`memo`,`agencyid`,`creattime`,`man`) VALUES ('" .$_POST["balancesheettype_no"]."','".$_POST["balancesheettype_time"]."', '" .$_POST["acount"]."','".$_POST["warehouse_id"]."', '" .$_POST["suppliers_id"]."','".$_POST["employee_id"]."', '" .$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["creattime"]."', '".$_POST["man"]."')";
//echo $_POST['cproduce_id'].$_POST['cnumber'];
 
if($_POST['subtype']=='2'){			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."balancesheettypedetail` (`balancesheettype_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','".$_POST["caccounttitle_id"]."','".$_POST["cobjecttype_id"]."','".$_POST["cobjectid"]."','".$_POST["clend"]."','".$_POST["cloan"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')");	
			//  echo "INSERT INTO `".WEB_ADMIN_TABPOX."balancesheettypedetail` (`balancesheettype_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','".$_POST["caccounttitle_id"]."','".$_POST["cobjecttype_id"]."','".$_POST["cobjectid"]."','".$_POST["clend"]."','".$_POST["cloan"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')";
			}
			
		$acount = &$this -> dbObj -> GetOne('select sum(totalacount) from '.WEB_ADMIN_TABPOX.'balancesheettypedetail   where balancesheettype_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."balancesheettype` SET `acount` =".$acount." where balancesheettype_id=".$id) ;	
			
			exit("<script>alert('".$info."成功');window.location.href='balancesheettype.php?action=upd&updid=".$id."';</script>");
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."balancesheettype` SET `balancesheettype_no` = '".$_POST["balancesheettype_no"]."',balancesheettype_time='".$_POST["balancesheettype_time"]."', warehouse_id='".$_POST["warehouse_id"]."', suppliers_id='" .$_POST["suppliers_id"]."',employee_id='".$_POST["employee_id"]."', memo='" .$_POST["memo"]."', man='".$_POST["man"]."' WHERE balancesheettype_id =".$id);
			//echo "UPDATE `".WEB_ADMIN_TABPOX."balancesheettype` SET `balancesheettype_no` = '".$_POST["balancesheettype_no"]."',balancesheettype_time='".$_POST["balancesheettype_time"]."', warehouse_id='".$_POST["warehouse_id"]."', suppliers_id='" .$_POST["suppliers_id"]."',employee_id='".$_POST["employee_id"]."', memo='" .$_POST["memo"]."', man='".$_POST["man"]."' WHERE balancesheettype_id =".$id;

		//echo $_POST["con_act"];
		
 		if($_POST["con_act"]=='upd'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."balancesheettypedetail` SET `accounttitle_id` = '".$_POST["caccounttitle_id"]."',`objecttype_id` = '".$_POST["cobjecttype_id"]."',`objectid` = '".$_POST["cobjectid"]."',lend='".$_POST["clend"]."',loan='".$_POST["cloan"]."',memo='".$_POST["cmemo"]."' WHERE balancesheettypedetail_id  =".$_POST['proupdid']);		
		//echo "UPDATE `".WEB_ADMIN_TABPOX."balancesheettypedetail` SET `produce_id` = '".$_POST["cproduce_id"]."',`number` = '".$_POST["cnumber"]."',`memo` = '".$_POST["cmemo"]."' WHERE balancesheettypedetail_id  =".$_POST['proupdid'];
		$acount = &$this -> dbObj -> GetOne('select sum(totalacount) from '.WEB_ADMIN_TABPOX.'balancesheettypedetail   where balancesheettype_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."balancesheettype` SET `acount` =".$acount." where balancesheettype_id=".$id) ;	
		
		exit("<script>alert('".$info."商品成功');window.location.href='balancesheettype.php?action=upd&updid=".$id."';</script>");		
		}else{
if($_POST['subtype']=='2'){			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."balancesheettypedetail` ( `balancesheettype_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','".$_POST["caccounttitle_id"]."','".$_POST["cobjecttype_id"]."','".$_POST["cobjectid"]."','".$_POST["clend"]."','".$_POST["cloan"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')");	
			// echo "INSERT INTO `".WEB_ADMIN_TABPOX."balancesheettypedetail` (`balancesheettype_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','".$_POST["caccounttitle_id"]."','".$_POST["cobjecttype_id"]."','".$_POST["cobjectid"]."','".$_POST["clend"]."','".$_POST["cloan"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')";
			 
			
		//$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."serviceconsume` SET `produce_id` = '".$_POST["produce_id"]."',`std_consumption` = '".$_POST["std_consumption"]."',`memo` = '".$_POST["memo"]."',`balancesheettype` = '".$_POST["balancesheettype"]."' WHERE balancesheettype_id =".$id);
		//echo "INSERT INTO `".WEB_ADMIN_TABPOX."consumables` (balancesheettype_id,produce_id,std_consumption,memo,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cstd_consumption"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')";
		$info = '添加项目';

		}
		$acount = &$this -> dbObj -> GetOne('select sum(totalacount) from '.WEB_ADMIN_TABPOX.'balancesheettypedetail   where balancesheettype_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."balancesheettype` SET `acount` =".$acount." where balancesheettype_id=".$id) ;	
		
		}
		//echo $_POST['issave'];
		if($_POST['issave']=='1'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."balancesheettype` SET `status`=0  WHERE balancesheettype_id =".$id);//0未提交，1已提交2已收部分3全部收完
			exit("<script>alert('保存成功');window.location.href='balancesheettype.php';</script>");
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