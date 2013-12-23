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
        if(isset($_GET['action']) && $_GET['action']=='nianjie'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> nianjie();			
		}else{
            parent::Main();
        }
    }
 
	function disp(){
		//定义模板
		
		$t = new Template('../template/finace');
		$t -> set_file('f','nianjie.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		
		$annualbatch_name=date('Y',time())."年";
		$t -> set_var('annualbatch_name',$annualbatch_name);
		 //上次年结
		$bgdate=$this -> dbObj -> getone('select enddate  from '.WEB_ADMIN_TABPOX.'annualbatch  where agencyid ='.$_SESSION["currentorgan"].'  order by annualbatch_id desc' );	
		
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
	
	function nianjie(){//年结操作
	$bgdate=$_POST["bgdate"]." 00:00:00";
	$enddate=$_POST["enddate"]." 23:59:59";	
	$timecondition=' creattime  between "'.$bgdate.'" and "'.$enddate.'"';	
	$timecondition1=' B.creattime  between "'.$bgdate.'" and "'.$enddate.'"';	
	$this -> dbObj -> Execute("START TRANSACTION");//事务开始。
 	$result=$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'annualbatch where annualbatch_name="'.$_POST["annualbatch_name"].'" and agencyid="'.$_SESSION["currentorgan"].'"');
	
	if ($result -> Recordcount()>0){
	 exit("<script>alert('存在同名年结存，操作失败');history.go(-1);</script>");
	}else{
	 $result1=$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'annualbatch (`annualbatch_name`, `bgdate`, `enddate`, `agencyid`) VALUE ("'.$_POST["annualbatch_name"].'","'.$_POST["bgdate"].'","'.$_POST["enddate"].'","'.$_SESSION["currentorgan"].'")');
	 //echo 'INSERT INTO '.WEB_ADMIN_TABPOX.'annualbatch (`annualbatch_name`, `bgdate`, `enddate`, `agencyid`) VALUE ("'.$_POST["annualbatch_name"].'","'.$_POST["bgdate"].'","'.$_POST["enddate"].'","'.$_SESSION["currentorgan"].'")';
    	$id = $this -> dbObj -> Insert_ID();
		
		
//月结，插入annualbatchobjectdetail 2011.3.24

$sqltoannualbatch='select   A.transfervoucher_id, A.accounttitle_id, A.objecttype_id, A.objectid, sum(A.lend) as lend, sum(A.loan) as loan, A.memo, A.agencyid from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher  B ON A.transfervoucher_id = B.transfervoucher_id  where A.agencyid='.$_SESSION["currentorgan"].' AND '.$timecondition1.' and status=5 group by  A.accounttitle_id   ,A.objecttype_id,A.objectid';

	$inrsannualbatch = &$this -> dbObj -> Execute($sqltoannualbatch);
	$sqlannualbatchstr='';
	while ($inrrsannualbatch = &$inrsannualbatch -> FetchRow()) {	
	 $sqllastannualbatch='select * FROM '.WEB_ADMIN_TABPOX.'annualbatchobjectdetail A INNER JOIN  '.WEB_ADMIN_TABPOX.'annualbatch  B  ON A.monthlybatch=B.monthlybatch WHERE A.accounttitle_id='.$inrrsannualbatch["accounttitle_id"].' AND A.agencyid='.$_SESSION["currentorgan"].' order by  B.annualbatch_id desc';
 
	$lastannualbatch=$this -> dbObj -> GetRow($sqllastannualbatch);
	if($lastannualbatch){
	
	$sqlannualbatchstr=$sqlannualbatchstr==''?'('.$id.','.$inrrsannualbatch["accounttitle_id"].','.$inrrsannualbatch["objecttype_id"].','.$inrrsannualbatch["objectid"].','.$inrrsannualbatch["lend"].','.$inrrsannualbatch["lend"]+$lastannualbatch['adduplend'].','.$inrrsannualbatch["loan"].','.$inrrsannualbatch["loan"]+$lastannualbatch['adduploan'].','.($inrrsannualbatch["lend"]-$inrrsannualbatch["loan"]+$lastannualbatch['balance']).',"'.$inrrsannualbatch["memo"].'",'.$_SESSION["currentorgan"].')':$sqlannualbatchstr.', ('.$id.','.$inrrsannualbatch["accounttitle_id"].','.$inrrsannualbatch["objecttype_id"].','.$inrrsannualbatch["objectid"].','.$inrrsannualbatch["lend"].','.$inrrsannualbatch["lend"]+$lastannualbatch['adduplend'].','.$inrrsannualbatch["loan"].','.$inrrsannualbatch["loan"]+$lastannualbatch['adduploan'].','.($inrrsannualbatch["lend"]-$inrrsannualbatch["loan"]+$lastannualbatch['balance']).',"'.$inrrsannualbatch["memo"].'",'.$_SESSION["currentorgan"].')';
	}else{
	 
	$sqlannualbatchstr=$sqlannualbatchstr==''?'('.$id.','.$inrrsannualbatch["accounttitle_id"].','.$inrrsannualbatch["objecttype_id"].','.$inrrsannualbatch["objectid"].','.$inrrsannualbatch["lend"].','.$inrrsannualbatch["lend"].','.$inrrsannualbatch["loan"].','.$inrrsannualbatch["loan"].','.($inrrsannualbatch["lend"]-$inrrsannualbatch["loan"]).',"'.$inrrsannualbatch["memo"].'",'.$_SESSION["currentorgan"].')':$sqlannualbatchstr.', ('.$id.','.$inrrsannualbatch["accounttitle_id"].','.$inrrsannualbatch["objecttype_id"].','.$inrrsannualbatch["objectid"].','.$inrrsannualbatch["lend"].','.$inrrsannualbatch["lend"].','.$inrrsannualbatch["loan"].','.$inrrsannualbatch["loan"].','.($inrrsannualbatch["lend"]-$inrrsannualbatch["loan"]).',"'.$inrrsannualbatch["memo"].'",'.$_SESSION["currentorgan"].')';
  
	}
 
if($sqlannualbatchstr<>''){
  $resannualbatch=$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'annualbatchobjectdetail (`annualbatch_id`,`accounttitle_id`,`objecttype_id`,`objectid`,`lend`,`adduplend`,`loan`, `adduploan`, `balance`,`memo`,`agencyid`) VALUE '.$sqlannualbatchstr);  

}
	}
//end annualbatchobjectdetail  		
		
//将凭证明细年结到annualbatchdetail ，即将 最后一个月月结复制过来。


$sqllastmonth='select * FROM '.WEB_ADMIN_TABPOX.'monthlybatchdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'monthlybatch B ON    A.monthlybatch_id=B.monthlybatch_id WHERE  A.agencyid='.$_SESSION["currentorgan"].' order by  B.monthlybatch_id desc';//最近的月结。年结是最近的是最后一个月。
 
$lastmonth=$this -> dbObj -> Execute($sqllastmonth);
while($inrrslastmonth=$lastmonth -> FetchRow()){
	echo "test";
		$sqllastmonthstr=$sqllastmonthstr==''?'('.$id.','.$inrrslastmonth["accounttitle_id"].','.$inrrslastmonth["lend"].','.$inrrslastmonth['adduplend'].','.$inrrslastmonth["loan"].','.$inrrslastmonth['adduploan'].','.$inrrslastmonth["balance"].',"'.$inrrslastmonth["memo"].'",'.$inrrslastmonth["agencyid"].')':$sqllastmonthstr.', ('.$id.','.$inrrslastmonth["accounttitle_id"].','.$inrrslastmonth["lend"].','.$inrrslastmonth["adduplend"].','.$inrrslastmonth["loan"].','.$inrrslastmonth["adduploan"].','.$inrrslastmonth["balance"].',"'.$inrrslastmonth["memo"].'",'.$inrrslastmonth["agencyid"].')';
		
	}
	
	if($sqllastmonthstr<>''){
  $res=$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'annualbatchdetail (`annualbatch_id`,`accounttitle_id`,lend,`adduplend`,`loan`, `adduploan`, `balance`,`memo`,`agencyid`) VALUE '.$sqllastmonthstr);  
echo 'INSERT INTO '.WEB_ADMIN_TABPOX.'annualbatchdetail (`annualbatch_id`,`accounttitle_id`,lend,`adduplend`,`loan`, `adduploan`, `balance`,`memo`,`agencyid`) VALUE '.$sqllastmonthstr;
}

//结资产负债
 	$sql='select * from '.WEB_ADMIN_TABPOX.'balancesheettype  where type=1  ORDER BY balancesheettype_id ASC ';
	$sql2='select * from '.WEB_ADMIN_TABPOX.'balancesheettype  where type=0  ORDER BY balancesheettype_id ASC ';
 
			
	$inrs = &$this -> dbObj -> Execute($sql);
	$inrs2 = &$this -> dbObj -> Execute($sql2);
	$sqlstr='';
	$sqlstr2='';
	while ($inrrs = &$inrs -> FetchRow()) {		
	//资产类年结
		$zcend=$this -> dbObj ->GetRow('SELECT sum(A.lend-A.loan) as zcend FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id =B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['accounttitleid'].') and B.agencyid="'.$_SESSION["currentorgan"].'"and '.$timecondition1);
		//echo 'SELECT sum(A.lend-A.loan) as zcend FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id =B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['accounttitleid'].') and B.agencyid="'.$_SESSION["currentorgan"].'"and '.$timecondition1;
		$reducezcend=$this -> dbObj ->GetRow('SELECT sum(A.lend-A.loan) as reducezcend FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs["addorreduce"].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition1); 
		$zcend['zcend']=$zcend['zcend']?$zcend['zcend']:0;
		$reducezcend['reducezcend']=$reducezcend['reducezcend']?$reducezcend['reducezcend']:0;
		
		$sqlstr=$sqlstr==''?'('.$inrrs["balancesheettype_id"].','.$id.','.($zcend['zcend']-2*$reducezcend['reducezcend']).','.$_SESSION["currentorgan"].')':$sqlstr.",".'('.$inrrs["balancesheettype_id"].','.$id.','.($zcend['zcend']-2*$reducezcend['reducezcend']).','.$_SESSION["currentorgan"].')';
		
	}
	 
	$res=$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'balancesheet (`balancesheettype_id`, `annualbatch_id`,`end`, `agencyid`) VALUE '.$sqlstr);
//echo 'INSERT INTO '.WEB_ADMIN_TABPOX.'balancesheet (`balancesheettype_id`, `annualbatch_id`,`end`, `agencyid`) VALUE '.$sqlstr;

/*//结损益表
 
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
	
		$addupdata=$this -> dbObj ->GetRow('SELECT sum(lend-loan) as zcend FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JJOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id =B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['accounttitleid'].') and B.agencyid="'.$_SESSION["currentorgan"].'"');
		
		
		$addup=$addupdata["addup"]?$addupdata["addup"]:0;
		$sqlstr=$sqlstr==''?'('.$inrrs["incomestatementtype_id"].','.$id.','.$addup.','.$_SESSION["currentorgan"].')':$sqlstr.",".'('.$inrrs["incomestatementtype_id"].','.$id.','.$addup.','.$_SESSION["currentorgan"].')';
		
	}
	 
	$res1=$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'incomestatement (`incomestatementtype_id`, `annualbatch_id`,`addup`, `agencyid`) VALUE '.$sqlstr);*/
		
	if($result1&&$res&&$resannualbatch){
		$this -> dbObj -> Execute("COMMIT");
		$info='年结成功';
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
  