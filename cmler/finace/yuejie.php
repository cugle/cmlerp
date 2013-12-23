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
        if(isset($_GET['action']) && $_GET['action']=='monthfinish'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> monthfinish();			
		}else{
            parent::Main();
        }
    }
 
	function disp(){
		//定义模板
		
		$t = new Template('../template/finace');
		$t -> set_file('f','yuejie.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		
		$monthlybatch_name=date('y',time())."年".date('m',time())."月份";
		$t -> set_var('monthlybatch_name',$monthlybatch_name);
		 //上次月结
		$bgdate=$this -> dbObj -> getone('select enddate  from '.WEB_ADMIN_TABPOX.'monthlybatch  where agencyid ='.$_SESSION["currentorgan"].' order by monthlybatch_id desc');	
	 
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
	
	function monthfinish(){//月结操作
	
	
	$this -> dbObj -> Execute("START TRANSACTION");//事务开始。
	$bgdate=$_POST["bgdate"]." 00:00:00";
	$enddate=$_POST["enddate"]." 23:59:59";	
	$timecondition=' creattime  between "'.$bgdate.'" and "'.$enddate.'"';	
	$timecondition1=' B.creattime  between "'.$bgdate.'" and "'.$enddate.'"';	
	$notsubmit=$this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX."sell where status in(0,1) and  ".$timecondition.' and agencyid ='.$_SESSION["currentorgan"]);//如果存在未完成未提交的单据则不能月结
	
		if($notsubmit->Recordcount>0){
			exit("<script>alert('存在未提交或未完成的单据不能月结，操作失败');history.go(-1);</script>");
			}
		//期初时间			
		$bgdate=$this -> dbObj -> getone('select enddate  from '.WEB_ADMIN_TABPOX.'annualbatch  where agencyid ='.$_SESSION["currentorgan"].' order by annualbatch_id desc');	
		if (!$bgdate){//如果没有年结过 查找最早的单时间。
			$bgdate=$this -> dbObj -> getone('select creattime  from '.WEB_ADMIN_TABPOX.'sell  where agencyid ='.$_SESSION["currentorgan"].' order by sell_id asc');	 
			
			 
		}
	$timecondition2=' B.creattime  between "'.$bgdate.'" and "'.$enddate.'"';
 	$result=$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'monthlybatch where monthlybatch_name="'.$_POST["monthlybatch_name"].'" and agencyid="'.$_SESSION["currentorgan"].'"');\
	if ($result -> Recordcount()>0){
	 exit("<script>alert('存在同名月结，操作失败');history.go(-1);</script>");
	}else{
	 $result1=$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'monthlybatch (`monthlybatch_name`, `bgdate`, `enddate`, `agencyid`) VALUE ("'.$_POST["monthlybatch_name"].'","'.$_POST["bgdate"].'","'.$_POST["enddate"].'","'.$_SESSION["currentorgan"].'")');
   
   	$id = $this -> dbObj -> Insert_ID();
//月结，插入mothbatchdetail  2011.3.24

$sqltomonthbatch='select   A.transfervoucher_id, A.accounttitle_id, A.objecttype_id, A.objectid, sum(A.lend) as lend, sum(A.loan) as loan, A.memo, A.agencyid from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher  B ON A.transfervoucher_id = B.transfervoucher_id  where A.agencyid='.$_SESSION["currentorgan"].' AND '.$timecondition1.' and status=5 group by  A.accounttitle_id';
 
	$inrsmonthbatch = &$this -> dbObj -> Execute($sqltomonthbatch);
	$sqlmonthbatchstr='';
	while ($inrrsmonthbatch = &$inrsmonthbatch -> FetchRow()) {	
	 $sqllastmonthbatch='select * FROM '.WEB_ADMIN_TABPOX.'monthlybatchdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'monthlybatch B ON    A.monthlybatch=B.monthlybatch WHERE A.accounttitle_id='.$inrrsmonthbatch["accounttitle_id"].' AND A.agencyid='.$_SESSION["currentorgan"].' order by  B.monthlybatch_id desc';
 
	$lastmonthbatch=$this -> dbObj -> GetRow($sqllastmonthbatch);
	if($lastmonthbatch){
	
	$sqlmonthbatchstr=$sqlmonthbatchstr==''?'('.$id.','.$inrrsmonthbatch["accounttitle_id"].','.$inrrsmonthbatch["lend"].','.$inrrsmonthbatch["lend"]+$lastmonthbatch['adduplend'].','.$inrrsmonthbatch["loan"].','.$inrrsmonthbatch["loan"]+$lastmonthbatch['adduploan'].','.($inrrsmonthbatch["lend"]-$inrrsmonthbatch["loan"]+$lastmonthbatch['balance']).',"'.$inrrsmonthbatch["memo"].'",'.$_SESSION["currentorgan"].')':$sqlmonthbatchstr.', ('.$id.','.$inrrsmonthbatch["accounttitle_id"].','.$inrrsmonthbatch["lend"].','.$inrrsmonthbatch["lend"]+$lastmonthbatch['adduplend'].','.$inrrsmonthbatch["loan"].','.$inrrsmonthbatch["loan"]+$lastmonthbatch['adduploan'].','.($inrrsmonthbatch["lend"]-$inrrsmonthbatch["loan"]+$lastmonthbatch['balance']).',"'.$inrrsmonthbatch["memo"].'",'.$_SESSION["currentorgan"].')';
	}else{
	 
	$sqlmonthbatchstr=$sqlmonthbatchstr==''?'('.$id.','.$inrrsmonthbatch["accounttitle_id"].','.$inrrsmonthbatch["lend"].','.$inrrsmonthbatch["lend"].','.$inrrsmonthbatch["loan"].','.$inrrsmonthbatch["loan"].','.($inrrsmonthbatch["lend"]-$inrrsmonthbatch["loan"]).',"'.$inrrsmonthbatch["memo"].'",'.$_SESSION["currentorgan"].')':$sqlmonthbatchstr.', ('.$id.','.$inrrsmonthbatch["accounttitle_id"].','.$inrrsmonthbatch["lend"].','.$inrrsmonthbatch["lend"].','.$inrrsmonthbatch["loan"].','.$inrrsmonthbatch["loan"].','.($inrrsmonthbatch["lend"]-$inrrsmonthbatch["loan"]).',"'.$inrrsmonthbatch["memo"].'",'.$_SESSION["currentorgan"].')';
  
	}
 
if($sqlmonthbatchstr<>''){
  $res=$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'monthlybatchdetail (`monthlybatch_id`,`accounttitle_id`,lend,`adduplend`,`loan`, `adduploan`, `balance`,`memo`,`agencyid`) VALUE '.$sqlmonthbatchstr);  

}
	}
//end monthdetail   
   
//结损益表
 
 	$sql='select * from '.WEB_ADMIN_TABPOX.'incomestatementtype ORDER BY incomestatementtype_id ASC ';
	
	
	$inrs = &$this -> dbObj -> Execute($sql);
	$sqlstr='';
	while ($inrrs = &$inrs -> FetchRow()) {	
			if($inrrs['type']==1){
				 
			$thismonth=$this -> dbObj ->GetRow('SELECT sum(A.lend-A.loan) as thismonth FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['accounttitleid'].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition1);
			$reducethismonth=$this -> dbObj ->GetRow('SELECT sum(A.lend-A.loan) as reducethismonth FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['addorreduce'].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition1); 
				$thisyear=$this -> dbObj ->GetRow('SELECT sum(A.lend-A.loan) as thisyear FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['accounttitleid'].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition2);
				$reducethisyear=$this -> dbObj ->GetRow('SELECT sum(A.lend-A.loan) as reducethisyear FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['addorreduce'].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition2); 
				}else if($inrrs['type']==0){
				
				$thismonth=$this -> dbObj ->GetRow('SELECT sum(A.loan-A.lend) as thismonth FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['accounttitleid'].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition1);
				$reducethismonth=$this -> dbObj ->GetRow('SELECT sum(A.loan-A.lend) as reducethismonth FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['addorreduce'].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition1); 
				
				//本年累计
				$thisyear=$this -> dbObj ->GetRow('SELECT sum(A.loan-A.lend) as thisyear FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['accounttitleid'].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition2);
				$reducethisyear=$this -> dbObj ->GetRow('SELECT sum(A.loan-A.lend) as reducethisyear FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['addorreduce'].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition2); 				
				
				}
				
				
				$thismonth['thismonth']=$thismonth['thismonth']?$thismonth['thismonth']:0;
				$reducethismonth['reducethismonth']=$reducethismonth['reducethismonth']?$reducethismonth['reducethismonth']:0;
 
				 
				$thisyear['thisyear']=$thisyear['thisyear']?$thisyear['thisyear']:0;
				$reducethisyear['reducethisyear']=$reducethisyear['reducethisyear']?$reducethisyear['reducethisyear']:0;
				
				$thismonth1=$thismonth['thismonth']-2*$thismonth['reducethismonth'];
				$addup=$thisyear['thisyear']-2*$reducethisyear['reducethisyear'];
				
 
		$sqlstr=$sqlstr==''?'('.$inrrs["incomestatementtype_id"].','.$id.','.$thismonth1.','.$addup.','.$_SESSION["currentorgan"].')':$sqlstr.",".'('.$inrrs["incomestatementtype_id"].','.$id.','.$thismonth1.','.$addup.','.$_SESSION["currentorgan"].')';
		
	}
if($sqlstr<>''){ 
	$res=$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'incomestatement (`incomestatementtype_id`,`annualbatch_id`,thismonth,`addup`, `agencyid`) VALUE '.$sqlstr);  
}




//凭证月结  
  
  $sqlth='select A.transfervoucherdetail_id, A.transfervoucher_id, A.accounttitle_id, A.objecttype_id, A.objectid, sum(A.lend) as lend, sum(A.loan) as loan, A.memo, A.agencyid from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id = B.transfervoucher_id  where A.agencyid='.$_SESSION["currentorgan"].' AND '.$timecondition1.' group by  A.accounttitle_id,A.objecttype_id,A.objectid';
	 
	
	$inrsth = &$this -> dbObj -> Execute($sqlth);
	$sqlthstr='';
	while ($inrrsth = &$inrsth -> FetchRow()) {	
	 $sqllastth='select * FROM '.WEB_ADMIN_TABPOX.'transfervoucherhistory WHERE accounttitle_id='.$inrrsth["accounttitle_id"].' AND objecttype_id='.$inrrsth["objecttype_id"].' AND objectid='.$inrrsth["objectid"].' AND agencyid='.$_SESSION["currentorgan"];
	
	$lastth=$this -> dbObj -> GetRow($sqllastth);
	if($lastth){
	  
	$sqlthstr=$sqlthstr==''?'('.$id.','.$inrrsth["accounttitle_id"].','.$inrrsth["objecttype_id"].','.$inrrsth["objectid"].','.($inrrsth["lend"]+$lastth['lend']).','.($inrrsth["loan"]+$lastth['loan']).','.$_SESSION["currentorgan"].')':$sqlthstr.', ('.$id.','.$inrrsth["accounttitle_id"].','.$inrrsth["objecttype_id"].','.$inrrsth["objectid"].','.($inrrsth["lend"]+$lastth['lend']).','.($inrrsth["loan"]+$lastth['loan']).','.$_SESSION["currentorgan"].')';
	
	}else{
	   
	$sqlthstr=$sqlthstr==''?'('.$id.','.$inrrsth["accounttitle_id"].','.$inrrsth["objecttype_id"].','.$inrrsth["objectid"].','.$inrrsth["lend"].','.$inrrsth["loan"].','.$_SESSION["currentorgan"].')':$sqlthstr.', ('.$id.','.$inrrsth["accounttitle_id"].','.$inrrsth["objecttype_id"].','.$inrrsth["objectid"].','.$inrrsth["lend"].','.$inrrsth["loan"].','.$_SESSION["currentorgan"].')';
	
	}
  
	}
if($sqlthstr<>''){
  $res=$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'transfervoucherhistory (`monthlybatch_id`,`accounttitle_id`,objecttype_id,`objectid`,`lend`, `loan`, `agencyid`) VALUE '.$sqlthstr);  

}
 
 	if($result1&&$res&&$res){
		$this -> dbObj -> Execute("COMMIT");
		$info='月结成功';
		exit("<script>alert('$info');history.go(-1);</script>");
		}else{
		$this -> dbObj -> Execute("ROLLBACK");
		$info='发生错误，提交失败，数据已经回滚';
		exit("<script>alert('$info');history.go(-1);</script>");
		 
		
	
	 }  
   
   
   

	 }
 }
 
}
$main = new Pagecustomer();
$main -> Main();
?>
  