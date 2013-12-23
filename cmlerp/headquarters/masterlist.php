<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/stock.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/cmlprice.cls.php');
class Pagecustomer extends admin {
	var $stockObj = null;
    function Main()
    {   
        if(isset($_POST['action']) && $_POST['action']=='masterlist'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> masterlist();			
		}else if(isset($_POST['action']) && $_POST['action']=='config'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> config();			
		}else if(isset($_POST['action']) && $_POST['action']=='printbill'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> printbill();			
		} else{
            parent::Main();
        }
    }
 	function config(){
	
	}
	function disp(){
		//定义模板
		
		$t = new Template('../template/headquarters');
		$t -> set_file('f','masterlistselectdate.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		
		$monthlybatch_name=date('m',time())."月份";
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
	
	function masterlist(){//
		$t = new Template('../template/headquarters');
		$t -> set_file('f','masterlist.html');

		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]
	//	$t -> set_block('f','main','m');
		$t -> set_block('f','agency','a');
		$t -> set_block('f','cardnamelist','cnl');
		$t -> set_block('agency','cardnumberlist','cml');
		//$t -> set_block('agency','cardnumbertodaylist','cmtl');
		$t -> set_block('f','totalcardnumberlist','tcml');
		
	 
		$bgdate=$_POST["bgdate"]." 00:00:00";
		$enddate=$_POST["enddate"]." 23:59:59";	
		$bgtodate=$_POST["enddate"]." 00:00:00";	
		$endtodate=$_POST["enddate"]." 23:59:59";	
		$t -> set_var('enddate',$_POST["enddate"]);
		$t -> set_var('bgdate',$_POST["bgdate"]);
 		
		$this->cmlpriceObj=new cmlprice();	
 
 		 $t -> set_var('cnl');//统计卡项
		 $inrscard=$this -> dbObj -> Execute("select * FROM   ".WEB_ADMIN_TABPOX."lookupcard     order by orderid"); 
		
		 while ($inrrscard = $inrscard -> FetchRow()) {
		$t -> set_var('cardname',$inrrscard['lookupcard_name']);
		$t -> parse('cnl','cardnamelist',true);	
		} 
	// 直营店列表
		$xianjinvalue=0.00;
		$yinkavalue=0.00;
		$totolconsumenumber=0.00;
		$totolconsultant=0.00;
		$t -> set_var('a');
		
		
		
 		$inrsagency=$this -> dbObj -> Execute("select * FROM   ".WEB_ADMIN_TABPOX."agency  where status=1 order by agency_no"); 		
		while ($inrrsagency= $inrsagency -> FetchRow()) {
		 
		 $t -> set_var('agency_no',$inrrsagency['agency_no']);
		 $t -> set_var('agency_name',$inrrsagency['agency_name']);
		 //现金刷卡合计
		$sqlpay="select   sum(xianjinvalue) as xianjinvalue ,sum(yinkavalue) as yinkavalue  FROM ".WEB_ADMIN_TABPOX."sell WHERE  status in(1,4,5)  and  agencyid =".$inrrsagency['agency_id'].'  and creattime between "'.$bgdate.'" and "'.$enddate.'"'; 	
		 
		 $inrspay=$this -> dbObj -> GetRow($sqlpay); 	
		  $t -> set_var('xianjinvalue',sprintf ("%01.2f",$inrspay['xianjinvalue'])); 
 		  $t -> set_var('yinkavalue',sprintf ("%01.2f",$inrspay['yinkavalue']));
		  $t -> set_var('pay',sprintf ("%01.2f",$inrspay['xianjinvalue']+$inrspay['yinkavalue']));
		  $xianjinvalue=$xianjinvalue+$inrspay['xianjinvalue'];
		  $yinkavalue=$yinkavalue+$inrspay['yinkavalue'];
		  
		//业绩
	$sqlstrt1="select * from  ".WEB_ADMIN_TABPOX."selldetail ";
	$sqlstrt2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail ";
	$sqlstrt3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail ";
	$sqlstrt4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail ";
	$sqlstrt5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail ";
	$sqlt6=$sqlstrt1." union ".$sqlstrt2." union ".$sqlstrt3." union ".$sqlstrt5;
	$inrsyeji=$this -> dbObj -> GetRow("select sum(A.amount) as yeji FROM  (".$sqlt6.") A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id WHERE  B.status in(1,4,5)  and   B.agencyid =".$inrrsagency['agency_id'].'  and B.creattime between "'.$bgdate.'" and "'.$enddate.'"order by B.sell_no'); 	  	$inrsyeji1=$this -> dbObj -> Execute("select *,A.amount as acmount FROM  (".$sqlstrt4.") A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id WHERE  B.status in(1,4,5)  and   B.agencyid =".$inrrsagency['agency_id'].'  and B.creattime between "'.$bgdate.'" and "'.$enddate.'"order by B.sell_no');
	$cmldisyeji=0;
	
	while($inrrsyeji1 = $inrsyeji1->FetchRow()){
		$cmldiscount=$this->cmlpriceObj->main($inrrsyeji1['sell_id']);
		$cmldisyeji=$cmldisyeji+$inrrsyeji1['acmount']*$cmldiscount[$inrrsyeji1['sell_id']];
		
	}
	
		  $totolconsultant=totolconsultant+$inrsyeji['yeji'];
		  $t -> set_var('yeji',sprintf ("%01.2f",$inrsyeji['yeji']-$cmldisyeji)); 
	 //操作项次
	
	
	$sqlstrt3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail ";
	$sqlstrt4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail ";
	
	$sqlt6= $sqlstrt3." union ".$sqlstrt4;
	$inrsconsumenumber=$this -> dbObj -> GetRow("select sum(A.number) as consumenumber FROM  (".$sqlt6.") A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id WHERE  B.status in(1,4,5)  and   B.agencyid =".$inrrsagency['agency_id'].'  and B.creattime between "'.$bgdate.'" and "'.$enddate.'" order by B.sell_no'); 	  
		 $totolconsumenumber=$totolconsumenumber+$inrsconsumenumber['consumenumber'];
		 $t -> set_var('consumenumber',$inrsconsumenumber['consumenumber']);
		 
		
 		 $t -> set_var('cml');//统计卡项
		 $inrscard=$this -> dbObj -> Execute("select * FROM   ".WEB_ADMIN_TABPOX."lookupcard     order by orderid"); 
		 $totalcard[$inrrscard['lookupcard_id']]='0.00';
		    $totalcard[$inrrscard['lookupcard_id']]='0.00';
		 while ($inrrscard = $inrscard -> FetchRow()) {
			 
	  if($inrrscard['type']=='1'){
		 //echo  "select count(*) as cardnumber FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3  and B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$inrrsagency['agency_id'] ;
		 $cardnumber=$this -> dbObj -> GetRow("select count(*) as cardnumber FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3  and B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$inrrsagency['agency_id'] );
		   $cardnumber['cardnumber']=$cardnumber['cardnumber']?$cardnumber['cardnumber']:'0';
		   $t -> set_var('cardnumber',$cardnumber['cardnumber']);
		    $totalcard[$inrrscard['lookupcard_id']]=  $totalcard[$inrrscard['lookupcard_id']]+ $cardnumber['cardnumber'];
		 }else if($inrrscard['type']=='0'){
			// echo "select sum(A.amount) as cardjine FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3  and B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$inrrsagency['agency_id'];
			 $cardjine=$this -> dbObj -> GetRow("select sum(A.amount) as cardjine FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3  and B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$inrrsagency['agency_id']);
			  $cardjine['cardjine']=$cardjine['cardjine']?$cardjine['cardjine']:'0.00';
			  $t -> set_var('cardnumber',$cardjine['cardjine']);
			  $totalcard[$inrrscard['lookupcard_id']]= $totalcard[$inrrscard['lookupcard_id']]+$cardjine['cardjine'];
		 }
		
		$t -> parse('cml','cardnumberlist',true);	
		}
	
	
		 $t -> parse('a','agency',true);	
		}
		 
	 	 $t -> set_var('totolconsultant',sprintf ("%01.2f",$totolconsultant)); 
		 $t -> set_var('totolconsumenumber',$totolconsumenumber); 
		 $t -> set_var('totalyinka',sprintf ("%01.2f",$yinkavalue)); 
		 $t -> set_var('totalxianjin',sprintf ("%01.2f",$xianjinvalue)); 
		 $t -> set_var('totolpay',sprintf ("%01.2f",$xianjinvalue+$yinkavalue)); 
		
 		 $t -> set_var('tcml');//统计卡项
		 $inrscard=$this -> dbObj -> Execute("select * FROM   ".WEB_ADMIN_TABPOX."lookupcard     order by orderid"); 
		 
		 while ($inrrscard = $inrscard -> FetchRow()) {
		 
		 $t -> set_var('totalcardnumber', $totalcard[$inrrscard['lookupcard_id']]);
		 $t -> parse('tcml','totalcardnumberlist',true);	
		 } 	
		
		
 
		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
}
	function printbill(){//
		$t = new Template('../template/headquarters');
		$t -> set_file('f','masterlistprint.html');

		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]
	//	$t -> set_block('f','main','m');
		$t -> set_block('f','agency','a');
		$t -> set_block('f','cardnamelist','cnl');
		$t -> set_block('agency','cardnumberlist','cml');
		//$t -> set_block('agency','cardnumbertodaylist','cmtl');
		$t -> set_block('f','totalcardnumberlist','tcml');
		
	 
		$bgdate=$_POST["bgdate"]." 00:00:00";
		$enddate=$_POST["enddate"]." 23:59:59";	
		$bgtodate=$_POST["enddate"]." 00:00:00";	
		$endtodate=$_POST["enddate"]." 23:59:59";	
		$t -> set_var('enddate',$_POST["enddate"]);
		$t -> set_var('bgdate',$_POST["bgdate"]);
 
 		$this->cmlpriceObj=new cmlprice();	
		
 		 $t -> set_var('cnl');//统计卡项
		 $inrscard=$this -> dbObj -> Execute("select * FROM   ".WEB_ADMIN_TABPOX."lookupcard     order by orderid"); 
		
		 while ($inrrscard = $inrscard -> FetchRow()) {
		$t -> set_var('cardname',$inrrscard['lookupcard_name']);
		$t -> parse('cnl','cardnamelist',true);	
		} 
	// 直营店列表
		$xianjinvalue=0;
		$yinkavalue=0;
		$totolconsumenumber=0;
		$totolconsultant=0;
		$t -> set_var('a');
		
		
		
 		$inrsagency=$this -> dbObj -> Execute("select * FROM   ".WEB_ADMIN_TABPOX."agency  where status=1 order by agency_no"); 		
		while ($inrrsagency= $inrsagency -> FetchRow()) {
		 
		 $t -> set_var('agency_no',$inrrsagency['agency_no']);
		 $t -> set_var('agency_name',$inrrsagency['agency_name']);
		 //现金刷卡合计
		$sqlpay="select   sum(xianjinvalue) as xianjinvalue ,sum(yinkavalue) as yinkavalue  FROM ".WEB_ADMIN_TABPOX."sell WHERE  status in(1,4,5)  and  agencyid =".$inrrsagency['agency_id'].'  and creattime between "'.$bgdate.'" and "'.$enddate.'"'; 	
		 
		 $inrspay=$this -> dbObj -> GetRow($sqlpay); 	
		  $t -> set_var('xianjinvalue',$inrspay['xianjinvalue']); 
 		  $t -> set_var('yinkavalue',$inrspay['yinkavalue']);
		  $t -> set_var('pay',$inrspay['xianjinvalue']+$inrspay['yinkavalue']);
		  $xianjinvalue=$xianjinvalue+$inrspay['xianjinvalue'];
		  $yinkavalue=$yinkavalue+$inrspay['yinkavalue'];
		  
		//业绩
	$sqlstrt1="select * from  ".WEB_ADMIN_TABPOX."selldetail ";
	$sqlstrt2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail ";
	$sqlstrt3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail ";
	$sqlstrt4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail ";
	$sqlstrt5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail ";
	$sqlt6=$sqlstrt1." union ".$sqlstrt2." union ".$sqlstrt3." union ".$sqlstrt5;
	$inrsyeji=$this -> dbObj -> GetRow("select sum(A.amount) as yeji FROM  (".$sqlt6.") A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id WHERE  B.status in(1,4,5)  and   B.agencyid =".$inrrsagency['agency_id'].'  and B.creattime between "'.$bgdate.'" and "'.$enddate.'"order by B.sell_no'); 	    	$inrsyeji1=$this -> dbObj -> Execute("select *,A.amount as acmount FROM  (".$sqlstrt4.") A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id WHERE  B.status in(1,4,5)  and   B.agencyid =".$inrrsagency['agency_id'].'  and B.creattime between "'.$bgdate.'" and "'.$enddate.'"order by B.sell_no');
	$cmldisyeji=0;
	
	while($inrrsyeji1 = $inrsyeji1->FetchRow()){
		$cmldiscount=$this->cmlpriceObj->main($inrrsyeji1['sell_id']);
		$cmldisyeji=$cmldisyeji+$inrrsyeji1['acmount']*$cmldiscount[$inrrsyeji1['sell_id']];
		
	}
		  $totolconsultant=totolconsultant+$inrsyeji['yeji'];
		  $t -> set_var('yeji',sprintf ("%01.2f",$inrsyeji['yeji']-$cmldisyeji)); 
		 
	 //操作项次
	
	
	$sqlstrt3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail ";
	$sqlstrt4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail ";
	
	$sqlt6= $sqlstrt3." union ".$sqlstrt4;
	$inrsconsumenumber=$this -> dbObj -> GetRow("select sum(A.number) as consumenumber FROM  (".$sqlt6.") A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id WHERE  B.status in(1,4,5)  and   B.agencyid =".$inrrsagency['agency_id'].'  and B.creattime between "'.$bgdate.'" and "'.$enddate.'" order by B.sell_no'); 	  
		 $totolconsumenumber=$totolconsumenumber+$inrsconsumenumber['consumenumber'];
		 $t -> set_var('consumenumber',$inrsconsumenumber['consumenumber']);
		 
		
 		 $t -> set_var('cml');//统计卡项
		 $inrscard=$this -> dbObj -> Execute("select * FROM   ".WEB_ADMIN_TABPOX."lookupcard     order by orderid"); 
		 $totalcard[$inrrscard['lookupcard_id']]=0;
		    $totalcard[$inrrscard['lookupcard_id']]=0;
		 while ($inrrscard = $inrscard -> FetchRow()) {
			 
	  if($inrrscard['type']=='1'){
		 //echo  "select count(*) as cardnumber FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3  and B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$inrrsagency['agency_id'] ;
		 $cardnumber=$this -> dbObj -> GetRow("select count(*) as cardnumber FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3  and B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$inrrsagency['agency_id'] );
		   $t -> set_var('cardnumber',$cardnumber['cardnumber']);
		    $totalcard[$inrrscard['lookupcard_id']]=  $totalcard[$inrrscard['lookupcard_id']]+ $cardnumber['cardnumber'];
		 }else if($inrrscard['type']=='0'){
			// echo "select sum(A.amount) as cardjine FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3  and B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$inrrsagency['agency_id'];
			 $cardjine=$this -> dbObj -> GetRow("select sum(A.amount) as cardjine FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3  and B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$inrrsagency['agency_id']);
			  
			  $t -> set_var('cardnumber',$cardjine['cardjine']);
			   $totalcard[$inrrscard['lookupcard_id']]=  $totalcard[$inrrscard['lookupcard_id']]+ $cardjine['cardjine'];
		 }
		
		$t -> parse('cml','cardnumberlist',true);	
		}
	
	
		 $t -> parse('a','agency',true);	
		}
		 
	 	 $t -> set_var('totolconsultant',sprintf ("%01.2f",$totolconsultant)); 
		 $t -> set_var('totolconsumenumber',$totolconsumenumber); 
		 $t -> set_var('totalyinka',sprintf ("%01.2f",$yinkavalue)); 
		 $t -> set_var('totalxianjin',sprintf ("%01.2f",$xianjinvalue)); 
		 $t -> set_var('totolpay',sprintf ("%01.2f",$xianjinvalue+$yinkavalue)); 
		
 		 $t -> set_var('tcml');//统计卡项
		 $inrscard=$this -> dbObj -> Execute("select * FROM   ".WEB_ADMIN_TABPOX."lookupcard     order by orderid"); 
		 
		 while ($inrrscard = $inrscard -> FetchRow()) {
		 
		 $t -> set_var('totalcardnumber', $totalcard[$inrrscard['lookupcard_id']]);
		 $t -> parse('tcml','totalcardnumberlist',true);	
		 } 	
		
		
 
		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
}
 
}
$main = new Pagecustomer();
$main -> Main();
?>
  