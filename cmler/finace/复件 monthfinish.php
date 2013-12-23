<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/stock.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/cmlprice.cls.php');
 require(WEB_ADMIN_CLASS_PATH.'/custom/performancehistory.cls.php');
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
		$t -> set_file('f','monthfinish.html');
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
	
	function monthfinish(){//月结操作
		$t = new Template('../template/finace');
		$t -> set_file('f','businessreport.html');
		$this->cmlpriceObj=new cmlprice();
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]
	//	$t -> set_block('f','main','m');
		$t -> set_block('f','todayitems','t');
		$t -> set_block('todayitems','beautylisttoday','blt');
		$t -> set_block('f','consultant','c');
		$t -> set_block('f','beauty','b');
		$t -> set_block('f','membercarlist','ml');
		$t -> set_block('beauty','beautymemberlist','bm');
		$t -> set_block('f','totalbeautymemberlist','tbm');
		$t -> set_block('consultant','consultantmemberlist','cm');
		$t -> set_block('f','totalconsultantmemberlist','tcm');
		$bgdate=$_POST["bgdate"]." 00:00:00";
		$enddate=$_POST["enddate"]." 23:59:59";	
		$bgtodate=$_POST["enddate"]." 00:00:00";	
		$endtodate=$_POST["enddate"]." 23:59:59";	
		$t -> set_var('enddate',$_POST["enddate"]);
		//$t -> set_var('m');
		//统计插入monthly表
		//美容师业绩统计
		//顾问业绩统计
/*	
本月新客数量
本月新客预付
本页新客成交
本月会员数量
本月会员预付
本月共计客户数
收费单数量
本月累计现金
本月累计刷卡
本月累计消费项次
本月累计手工费
项目卡累计款
卡-累计数 值对
产品销售金额
本月技师项次累计
本月技师项次累计 值对
疗程卡累计
卡累计
销售累计
顾问_业绩值对
销售累计值对
疗程卡值对
会籍卡值对*/

	//$result1=$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'monthlybatch (`monthlybatch_name`, `bgdate`, `enddate`, `agencyid`) VALUE ("'.$_POST["monthlybatch_name"].'","'.$_POST["bgdate"].'","'.$_POST["enddate"].'","'.$_SESSION["currentorgan"].'")');
 //========散客款项类====================
	$sql="select  * FROM ".WEB_ADMIN_TABPOX."sell  where  status in(1,4,5)  and   agencyid =".$_SESSION["currentorgan"].' and  creattime between "'.$bgdate.'" and "'.$enddate.'"' ; 
	$sellid='';
 	$sqltoday="select  * FROM ".WEB_ADMIN_TABPOX."sell  where  status in(1,4,5)  and   agencyid =".$_SESSION["currentorgan"].' and  creattime between "'.$bgtodate.'" and "'.$endtodate.'"' ; 
	
	
	$inrs=$this -> dbObj -> Execute($sql);
	$totalnewnumber = $inrs->RecordCount() ;
	while ($inrrs = $inrs -> FetchRow()) {
		$sellid=$sellid==''?$inrrs['sell_id']:$sellid.",".$inrrs['sell_id'];	 
	}
	$inrstoday=$this -> dbObj -> Execute($sqltoday);
	$recordcounttoday = $inrstoday->RecordCount() ;
	while ($inrrstoday = $inrstoday -> FetchRow()) {
		$sellidtoday=$sellidtoday==''?$inrrstoday['sell_id']:$sellidtoday.",".$inrrstoday['sell_id'];	 
	}
	
$sellid=$sellid==''?-1:$sellid;
$sellidtoday=$sellidtoday==''?-1:$sellidtoday;	
$cmldiscount=$this->cmlpriceObj->fordaily($sellid);//打折处理储值卡 现金券，赠送消费金额2010.1.20=======================================================

$sql1="select  A.*, (SELECT sum(amount)  FROM ".WEB_ADMIN_TABPOX."sellotherdetail WHERE selldetail_id = B.selldetail_id) as yufu FROM  ".WEB_ADMIN_TABPOX."sell A LEFT JOIN  ".WEB_ADMIN_TABPOX."sellotherdetail B ON A.sell_id = B.sell_id  where A.status in(1,4,5) and (A.membercard_no='' or A.membercard_no is NULL) and  A.agencyid =".$_SESSION['currentorgan'].' and    A.creattime between "'.$bgdate.'" and "'.$enddate.'" GROUP BY A.sell_id'; 	
 
	$inrs1=$this -> dbObj -> Execute($sql1);
	$newnumber = $inrs1->RecordCount() ;
	$totalnewyufu=0;
	$xianjin=0;
	$yinka=0;
	$billnumber=0;
			
	while ($inrrs1 = $inrs1 -> FetchRow()) {
  
		$totalnewyufu=$totalnewyufu+1; 
		$xianjin=$xianjin+$inrrs1['xianjinvalue'];
		$yinka=$yinka+$inrrs1['yinkavalue'];
		 
		if($inrrs1['xianjinvalue']>0 or $inrrs1['yinkavalue']>0){
		$billnumber=$billnumber+1;
		}
		if($inrrs1['payable1']-$inrrs1['realpay']>0){
		$own=$own+$inrrs1['payable1']-$inrrs1['realpay'];
		
		}		
		}

		$todaydeal=$this -> deal($sellidtoday);//今日预定散客
		$totaldeal=$this -> deal($sellid);//今日预定散客
		$t -> set_var('totaldeal',$totaldeal);
		$t -> set_var('todaydeal',$todaydeal);
		$t -> set_var('totalnewnumber',$totalnewnumber);

 
//========会员款项类====================
	$sql2="select  A.*, (SELECT sum(amount)  FROM ".WEB_ADMIN_TABPOX."sellotherdetail WHERE selldetail_id = B.selldetail_id) as yufu FROM  ".WEB_ADMIN_TABPOX."sell A LEFT JOIN  ".WEB_ADMIN_TABPOX."sellotherdetail B ON A.sell_id = B.sell_id  where A.status in(1,4,5) and A.membercard_no<>'' and  A.agencyid =".$_SESSION['currentorgan'].' and  A.creattime between "'.$bgdate.'" and "'.$enddate.'" GROUP BY A.sell_id'; 	
	 
	$inrs2=$this -> dbObj -> Execute($sql2);
	 
	$totalmembernumber= $inrs2->RecordCount() ;
	$totalmemyufu=0;
	 
	while ($inrrs2 = $inrs2 -> FetchRow()) {
		$totalmemyufu=$totalmemyufu+$inrrs2['yufu'];
		$xianjin=$xianjin+$inrrs2['xianjinvalue'];
		$yinka=$yinka+$inrrs2['yinkavalue'];
		if($inrrs2['xianjinvalue']>0 or $inrrs2['yinkavalue']>0){
		$billnumber=$billnumber+1;
		}
		}	
	$totalnumber=$totalnewnumber+$totalmembernumber;
/*	echo "散客数".$newnumber;
	echo "累计会员数".$membernumber;
	echo "累计顾客数".$totalnumber;
	echo "现金".$xianjin;
	echo "银行卡".$yinka;
	echo "会员预付".$memyufu;
	echo "新客预付".$totalnewyufu;
	echo "总业绩".($yinka+$xianjin);*/
	$own=$own==''?0:$own;
	$t -> set_var('totalxianjin',sprintf ("%01.2f",$xianjin));
	$t -> set_var('totalyinka',sprintf ("%01.2f",$yinka));
	$t -> set_var('totalnumber',$totalnumber);
	$t -> set_var('newnumber',$newnumber);
	$t -> set_var('totalmembernumber',$totalmembernumber);
	$totalmemyufu=0;
	$totalnewyufu=$this -> newyufunumber($sellid);//今日预定散客
	$totalmemyufu=$this -> memyufunumber($sellid);//今日预定散客	
	$t -> set_var('totalnewyufu',$totalnewyufu);
	$t -> set_var('totalmemyufu',$totalmemyufu);
	$t -> set_var('totojine',sprintf ("%01.2f",$xianjin+$yinka));
 
//==========今日散客============================
	$sql1_1="select  A.*, (SELECT sum(amount)  FROM ".WEB_ADMIN_TABPOX."sellotherdetail WHERE selldetail_id = B.selldetail_id) as yufu FROM  ".WEB_ADMIN_TABPOX."sell A LEFT JOIN  ".WEB_ADMIN_TABPOX."sellotherdetail B ON A.sell_id = B.sell_id  where A.status in(1,4,5) and A.sell_id in(".$sellidtoday.") and (A.membercard_no='' or A.membercard_no is NULL) and A.agencyid =".$_SESSION['currentorgan'].' and  A.creattime between "'.$bgtodate.'" and "'.$endtodate.'" GROUP BY A.sell_id'; 	
 
	$inrs1_1=$this -> dbObj -> Execute($sql1_1);
	 
	$newnumbertoday= $inrs1_1 ->RecordCount();
	$newyufu=0;
	$xianjintoday=0;
	while ($inrrs1_1 = $inrs1_1 -> FetchRow()) {
		//$newyufu=$newyufu+1;
		$xianjintoday=$xianjintoday+$inrrs1_1['xianjinvalue'];
		$yinkatoday=$yinkatoday+$inrrs1_1['yinkavalue'];
		if($inrrs1_1['payable1']-$inrrs1_1['realpay']>0){
		$own=$own+$inrrs1_1['payable1']-$inrrs1_1['realpay'];
		}
	}
 	
//==========今日会员============================
	$sql2_1="select  A.*, (SELECT sum(amount)  FROM ".WEB_ADMIN_TABPOX."sellotherdetail WHERE selldetail_id = B.selldetail_id) as yufu FROM  ".WEB_ADMIN_TABPOX."sell A LEFT JOIN  ".WEB_ADMIN_TABPOX."sellotherdetail B ON A.sell_id = B.sell_id  where A.status in(1,4,5) and A.sell_id in(".$sellidtoday.") and A.membercard_no<>'' and  A.agencyid =".$_SESSION['currentorgan'].' and  A.creattime between "'.$bgtodate.'" and "'.$endtodate.'" GROUP BY A.sell_id'; 	
	 
	$inrs2_1=$this -> dbObj -> Execute($sql2_1);
	 
	$membernumbetodayr= $inrs2_1->RecordCount() ;
	$memyufu=0;
	 
	while ($inrrs2_1 = $inrs2_1 -> FetchRow()) {
		$memyufu=$memyufu+1;
		$xianjintoday=$xianjintoday+$inrrs2_1['xianjinvalue'];
		$yinkatoday=$yinkatoday+$inrrs2_1['yinkavalue'];
		if($inrrs2_1['payable1']-$inrrs2_1['realpay']>0){
		$memown=$memown+$inrrs2_1['payable1']-$inrrs2_1['realpay'];
		}
	}	
	
	$totalnumbertoday=$newnumbertoday+$membernumbetodayr;	
	
	$t -> set_var('totalrealpay',sprintf ("%01.2f",$xianjintoday+$yinkatoday));
	$t -> set_var('xianjintoday',sprintf ("%01.2f",$xianjintoday));
	$t -> set_var('yinkatoday',sprintf ("%01.2f",$yinkatoday));
	$t -> set_var('newnumbertoday',$newnumbertoday);
	$t -> set_var('membernumbetodayr',$membernumbetodayr);	
	$t -> set_var('totalnumbertoday',$totalnumbertoday);	
	$t -> set_var('memown',$memown);
	//$t -> set_var('memyufu',$memyufu);	
	$newyufu=0;
	$memyufu=0;
	$newyufu=$this -> newyufunumber($sellidtoday);//今日预定散客
	$memyufu=$this -> memyufunumber($sellidtoday);//今日预定散客
	
	$t -> set_var('newyufu',$newyufu);
	
	$t -> set_var('memyufu',$memyufu);
//=======今日明细表===============
	$sqlstrt1="select * from  ".WEB_ADMIN_TABPOX."selldetail ";
	$sqlstrt2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail ";
	$sqlstrt3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail ";
	$sqlstrt4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail ";
	$sqlstrt5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail ";
	$sqlt6=$sqlstrt1." union ".$sqlstrt2." union ".$sqlstrt3." union ".$sqlstrt4." union ".$sqlstrt5;
		$sellidtoday1=explode(",",$sellidtoday);
		for ($i=0;$i<count($sellidtoday1);$i++)	{
		 
 			$inrs6=$this -> dbObj -> Execute("select * FROM  (".$sqlt6.") A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id WHERE A.sell_id =".$sellidtoday1[$i]."  AND status in(1,4,5)  and   A.agencyid =".$_SESSION['currentorgan'].' order by B.sell_no'); 
 
			
		}
		
		/*$todaysql= "select * FROM  (".$sqlt6.") A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id WHERE    B.agencyid =".$_SESSION['currentorgan']." and B.creattime between '".$bgtodate."' and' ".$endtodate."'  order by B.sell_no" ;*/
 	 
	//$todaysql="select  * FROM  (select  * FROM ".WEB_ADMIN_TABPOX."sellconsumedetail  union select  * FROM ".WEB_ADMIN_TABPOX."sellservicesdetail ) A  RIGHT JOIN   ".WEB_ADMIN_TABPOX."sell  B  ON     A.sell_id = B.sell_id   LEFT JOIN ".WEB_ADMIN_TABPOX."selldetail C ON B.sell_id = C.sell_id   LEFT JOIN ".WEB_ADMIN_TABPOX."sellcarddetail D ON    B.sell_id = D.sell_id    WHERE  B.agencyid =".$_SESSION['currentorgan']." and B.creattime between '".$bgtodate."' and' ".$endtodate."'  " ;
	 $todaysql= "select * FROM   ".WEB_ADMIN_TABPOX."sell   WHERE   agencyid =".$_SESSION['currentorgan']." and status in(1,4,5)  and   creattime between '".$bgtodate."' and' ".$endtodate."'  order by  sell_no" ;
//===============================================begin今日项目======================================================================	

	$this->perObj1=new performancehistory();
	$t -> set_var('t');//今日项目
	$todaybeautymanual=0;
	$beautytotaltime=0;
	$beautytotaltime=0;
	$todaybeautymanual=0;
	$owntoday=0;
	$inrstoday=$this -> dbObj -> Execute($todaysql); 
		while ($inrrstoday = $inrstoday -> FetchRow()) {
		 $t -> set_var('sell_id',$inrrstoday['sell_id']);
		$t -> set_var('sell_no',$inrrstoday['sell_no']);
		$t -> set_var('xianjinvalue',$inrrstoday['xianjinvalue']);
		$t -> set_var('payable1',$inrrstoday['payable1']);
		$own=$inrrstoday['payable1']-$inrrstoday['realpay'];
		$own=$own==0?"":$own;
		$t -> set_var('own',$own);
		$t -> set_var('yinkavalue',$inrrstoday['yinkavalue']);
		$owntoday=$owntoday+$inrrstoday['payable1']-$inrrstoday['realpay'];
		$customer=$this ->dbObj-> GetRow('select * from '.WEB_ADMIN_TABPOX.'customer where customer_id="'.$inrrstoday["customer_id"].'"');
		
		$customer_name=$customer['customer_name'];
		$customer_name=$customer_name==''?"散客":$customer_name;
		$customer_no=$customer['customer_no'];
		$customer_no=$customer_no==''?" ":$customer_no;
		$t -> set_var('customer_no',$customer_no);
		if($inrrstoday['membercard_no']=='' or $inrrstoday['membercard_no']==0){
			$t -> set_var('ismember','');
			$t -> set_var('notmember',$customer_name);
		}else{
			$t -> set_var('ismember',$customer_name);
			$t -> set_var('notmember','');
			}		
		$customerconsume=$this -> dbObj -> Execute("select * FROM  (".$sqlstrt3." union ".$sqlstrt4.") A WHERE A.sell_id=".$inrrstoday['sell_id']." and A.discount>0");
		$customerconsumeitems=$customerconsume->RecordCount() ;
		$customerconsumeitems=$customerconsumeitems==0?"":$customerconsumeitems;
		$t -> set_var('customerconsumeitems',$customerconsumeitems);
		$customerproduceamount=$this -> dbObj -> GetOne("select sum(amount) from  ".WEB_ADMIN_TABPOX."selldetail where  sell_id=".$inrrstoday['sell_id']." and discount>0");
		$totalcustomerproduceamount=$totalcustomerproduceamount+$customerproduceamount;
		$t -> set_var('customerproduceamount',$customerproduceamount);
		
		//其他付方式
		$paytypeenlist=array('xianjinvalue','yinkavalue','zengsongvalue','dingjinvalue','chuzhikavalue','xianjinquanvalue','yufuvalue','yufuproducevalue','zengsongproducevalue','zengsongcardvalue');
		$paytypelist=array('现金','刷卡','扣赠送账户','扣定金','扣储值卡','扣现金券','扣预收款','扣预收产品款','扣赠送产品款','扣赠送购卡款');
		$otherpay='';
		for ($i=0;$i<count($paytypeenlist);$i++) {
			
		 
		if($inrrstoday[$paytypeenlist[$i]]>0){
			
			$otherpay=$otherpay==''?$paytypelist[$i].":".$inrrstoday[$paytypeenlist[$i]]:$otherpay."<br/>".$paytypelist[$i].":".$inrrstoday[$paytypeenlist[$i]];
		}
		}
		$t -> set_var('otherpay',$otherpay);
		
		//美容师列表
		$t -> set_var('blt');
 		$inrsbeautytoday=$this -> dbObj -> Execute("select * FROM  (".$sqlstrt3." union ".$sqlstrt4.") A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id WHERE A.sell_id =".$inrrstoday['sell_id']."  AND status in(1,4,5)  and A.discount>0 and  A.agencyid =".$_SESSION['currentorgan']); 
 		$beautylist='';
 		$beautyliststr='';	
		$beautymanual='';
		//美容师手工 现金券 储值卡 赠送消费金 打折处理。2010.1.21
		while ($inrrsbeautytoday= $inrsbeautytoday -> FetchRow()) {
		if ($inrrsbeautytoday['item_type']==0){//购买服务
		$beautymanual=$beautymanual==''?sprintf ("%01.2f",$inrrsbeautytoday['value']*$cmldiscount[$inrrsbeautytoday['sell_id']]):$beautymanual."<br/>".sprintf ("%01.2f",$inrrsbeautytoday['value']*$cmldiscount[$inrrsbeautytoday['sell_id']]);
		$todaybeautymanual=$todaybeautymanual+$inrrsbeautytoday['value']*$cmldiscount[$inrrsbeautytoday['sell_id']];
		}else{
		$beautymanual=$beautymanual==''?sprintf ("%01.2f",$inrrsbeautytoday['value']):$beautymanual."<br/>".sprintf ("%01.2f",$inrrsbeautytoday['value']);
		//$todaybeautymanual=$todaybeautymanual+$inrrsbeautytoday['value'];
		}
			
		$beautyidtoday=explode(";",$inrrsbeautytoday['beauty_id']);
		for ($i=0;$i<count($beautyidtoday);$i++){
		$beautytotaltime=$beautytotaltime+1;
		//$beautylisttemp['"'.$beautyidtoday[$i].'"']=$beautylisttemp['"'.$beautyidtoday[$i].'"']+1;
		$beautylisttem=$this -> dbObj -> GetOne("select employee_name FROM ".WEB_ADMIN_TABPOX."employee where employee_id=".$beautyidtoday[$i]." and agencyid =".$_SESSION['currentorgan']);
		$beautylist=($i=='0')?$beautylisttem:$beautylist.",".$beautylisttem;
		 
		}
		$beautyliststr=$beautyliststr==''?$beautylist:$beautyliststr."<br/>".$beautylist;

		}
		$t -> set_var('beautylist',$beautyliststr);
		
		 $t -> parse('blt','beautylisttoday',true);	
		

		$beautymanual=$this->perObj1->main1($inrrstoday['sell_id']);
		$todaybeautymanual=$todaybeautymanual+$beautymanual;
		$t -> set_var('beautymanual',$beautymanual);//手工费
		//使用卡券类
 		$inrscoupontoday=$this -> dbObj -> Execute("select * FROM  (".$sqlstrt3." union ".$sqlstrt4.") A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id WHERE A.sell_id =".$inrrstoday['sell_id']."  AND status in(1,4,5)  and A.agencyid =".$_SESSION['currentorgan']); 
 		 
		$coupon='';
		 $tempcardid='';
		 $consumecardid='';
		 $buyservicesid='';
		while ($inrrscoupontoday= $inrscoupontoday -> FetchRow()) {
		 	 
			if($inrrscoupontoday['item_type']==4){//券类
				//$couponmarketingcard=$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrscoupontoday["cardid"]);
			 
			//$coupon=$coupon==''?$couponmarketingcard['marketingcard_name']:$coupon.",".$couponmarketingcard['marketingcard_name'];
			
			$tempcardid=$tempcardid==''?$inrrscoupontoday["cardid"]:$tempcardid.",".$inrrscoupontoday["cardid"];
				}else if ($inrrscoupontoday['item_type']==2){//券类
				//$couponmarketingcard=$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrscoupontoday["cardid"]);
			 
			//$coupon=$coupon==''?$couponmarketingcard['marketingcard_name']:$coupon.",".$couponmarketingcard['marketingcard_name'];
			
			$consumecardid=$consumecardid==''?$inrrscoupontoday["cardid"]:$consumecardid.",".$inrrscoupontoday["cardid"];
				}else if ($inrrscoupontoday['item_type']==0){//购买服务
				
				//$couponmarketingcard=$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrscoupontoday["cardid"]);
			 
			//$coupon=$coupon==''?$couponmarketingcard['marketingcard_name']:$coupon.",".$couponmarketingcard['marketingcard_name'];
			 
			$buyservicesid=$buyservicesid==''?$inrrscoupontoday["item_id"]:$buyservicesid.",".$inrrscoupontoday["item_id"];
				}	 
		 
		}
			$buyservicesid=$buyservicesid==''?0:$buyservicesid;
			$tempcardid=$tempcardid==''?0:$tempcardid;
			$consumecardid=$consumecardid==''?0:$consumecardid;
			
			//消费券
	 		$couponcard=$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id  where B.marketingcard_id in ('.$tempcardid.')   group by B.marketingcard_id ');
				$coupon='';
				while ($inrrscouponcard= $couponcard -> FetchRow()) {
					$coupon=$coupon==''?$inrrscouponcard['marketingcard_name']:$coupon."<br/>".$inrrscouponcard['marketingcard_name'];

				}
		$t -> set_var('coupon',$coupon);
		
		 //消费卡
		 $consumecard=$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id  where B.marketingcard_id in ('.$consumecardid.')   group by B.marketingcard_id ');
		 $cardconsume='';
				while ($inrrsconsumecard= $consumecard -> FetchRow()) {
					$cardconsume=$cardconsume==''?$inrrsconsumecard['marketingcard_name']:$cardconsume."<br/>".$inrrsconsumecard['marketingcard_name'];
				}
		$t -> set_var('cardconsume',$cardconsume);

		 //购买服务
		 $buyservices=$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'services  where services_id in ('.$buyservicesid.')');
		  
		 $buyservicestoday='';
				while ($inrrsbuyservices= $buyservices -> FetchRow()) {
				 
				$buyservicestoday=$buyservicestoday==''?$inrrsbuyservices['services_name']:$buyservicestoday."<br/>".$inrrsbuyservices['services_name'];
				}
		$t -> set_var('buyservicestoday',$buyservicestoday);
		//======购买卡项===========================
 		$inrstoday2=$this -> dbObj -> Execute("select * FROM  (".$sqlstrt1." union ".$sqlstrt2."  union ".$sqlstrt5.") A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id WHERE A.sell_id =".$inrrstoday['sell_id']."  AND status in(1,4,5)  and A.agencyid =".$_SESSION['currentorgan'] ); 
		
 			$buycardid=0;
			$kuanxiangidtoday='';
			$produceidtoday=0;
			$buycard='';
			$buyproduce='';
			$kuanxiangvaluetoday='';
		while ($inrrstoday2= $inrstoday2 -> FetchRow()) {
		 	 
			if($inrrstoday2['item_type']==3){//卡项			
			$buycardid=$buycardid==''?$inrrstoday2["item_id"]:$buycardid.",".$inrrstoday2["item_id"];
			
				}else if ($inrrstoday2['item_type']==5){//款项
				$kuanxiangvaluetoday=$kuanxiangvaluetoday==''?$inrrstoday2["amount"]:$kuanxiangvaluetoday.",".$inrrstoday2["amount"];
			$kuanxiangidtoday=$kuanxiangidtoday==''?$inrrstoday2["item_id"]:$kuanxiangidtoday.",".$inrrstoday2["item_id"];
				}else if ($inrrstoday2['item_type']==1){//商品
			$produceidtoday=$produceidtoday==''?$inrrstoday2["item_id"]:$produceidtoday.",".$inrrstoday2["item_id"];
				}


		}
		
			$buycardid=$buycardid==''?0:$buycardid;
			 
			$kuanxiangidtoday=$kuanxiangidtoday==''?5:$kuanxiangidtoday;
			$produceidtoday=$produceidtoday==''?0:$produceidtoday;
			//购买卡项
	 		$buycardtoday=$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id  where B.marketingcard_id in ('.$buycardid.')');
				 
				while ($inrrsbuycard= $buycardtoday -> FetchRow()) {
					$buycard=$buycard==''?$inrrsbuycard['marketingcard_name']:$buycard."<br/>".$inrrsbuycard['marketingcard_name'];
				}
				
		
		 //购买商品
		 $buyproducetoday=$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'produce where   produce_id in ('.$produceidtoday.') ');
		 
		 
				while ($inrrsbuyproduce= $buyproducetoday -> FetchRow()) {
					$buyproduce=$buyproduce==''?$inrrsbuyproduce['produce_name']:$buyproduce."<br/>".$inrrsbuyproduce['produce_name'];
				}
				
				
		$t -> set_var('buyproduce',$buyproduce);
		$t -> set_var('buycard',$buycard); 
		$kxstr='';
		if($kuanxiangvaluetoday<>''){
		$kuanxiangnamelist=array('预收款',"储值卡充值","定金","还款","赠送手工","其他","预付产品款","赠送产品款","赠送购卡款",'');	
		$kuanxiangidtoday=explode(",",$kuanxiangidtoday);
		$kuanxiangvaluetoday=explode(",",$kuanxiangvaluetoday);
		
		
		for ($i=0;$i<count($kuanxiangidtoday);$i++)	{
		$kxstr=$kxstr==''?$kuanxiangnamelist[$kuanxiangidtoday[$i]].":".$kuanxiangvaluetoday[$i]:$kxstr."<br/>".$kuanxiangnamelist[$kuanxiangidtoday[$i]].":".$kuanxiangvaluetoday[$i];
		}
		}
		$t -> set_var('kuanxiang',$kxstr);
//=======================================================		


		$t -> parse('t','todayitems',true);	
		}
		 
		 $t -> set_var('totalown',$this->totalown($sellid));
		$t -> set_var('owntoday',$owntoday);
		$t -> set_var('totalbeautymanual',sprintf ("%01.2f",$this->totalbeautymanual($sellid,$cmldiscount)));
		$t -> set_var('beautytotaltime',$beautytotaltime);
		$t -> set_var('todaybeautymanual',sprintf ("%01.2f",$todaybeautymanual));
		$t -> set_var('totalcustomerproduceamount',$totalcustomerproduceamount);
		
		
//===============================================end今日项目======================================================================		
		
		
//========服务类====================	
	//卡项消费表
	
	$sql3="select  * FROM ".WEB_ADMIN_TABPOX."sell A LEFT JOIN  ".WEB_ADMIN_TABPOX."sellconsumedetail B ON A.sell_id = B.sell_id  where A.status in(1,4,5)  and  A.agencyid =".$_SESSION['currentorgan'].' and B.discount>0 and  A.creattime between "'.$bgdate.'" and "'.$enddate.'"' ; 		
	$inrs3=$this -> dbObj -> Execute($sql3); 
	$consume = $inrs3->RecordCount() ;//本月技师 项次累计
	$manual=0;//每次补交的手工费
	while ($inrrs3 = $inrs3 -> FetchRow()) {
		$manual=$manual+$inrrs3['value'];	 
		}	
	//服务表
	 
	$sql4="select  * FROM ".WEB_ADMIN_TABPOX."sell A LEFT JOIN  ".WEB_ADMIN_TABPOX."sellservicesdetail B ON A.sell_id = B.sell_id  where A.status in(1,4,5)  and  A.agencyid =".$_SESSION["currentorgan"].' and B.discount>0 and  A.creattime between "'.$bgdate.'" and "'.$enddate.'"'; 	
	 
	$inrs4=$this -> dbObj -> Execute($sql4); 
	$consume = $consume+$inrs4->RecordCount() ;//本月消费项次
	while ($inrrs4 = $inrs4 -> FetchRow()) {
		$manual=$manual+$inrrs4['value'];	 
		}	
/*	echo "手工费".$manual;
	echo "本月消费项次".$consume;*/
/*//===================================
	//购买卡项 
$sql3="select  * FROM ".WEB_ADMIN_TABPOX."sell A LEFT JOIN  ".WEB_ADMIN_TABPOX."sellcarddetail B ON A.sell_id = B.sell_id  where A.status in(1,4,5)  and  A.agencyid =".$_SESSION["currentorgan"].' and    B.item_type=3 AND  B.discount>0 and  A.creattime between "'.$bgdate.'" and "'.$enddate.'"' ; 		
	$consume = $inrs3->RecordCount() ;//本月消费项次
	$Itemcard=0;//每次补交的手工费
	while ($inrrs3 = $inrs3 -> FetchRow()) {
		$Itemcard=$Itemcard+$inrrs3['value'];	 
	}
*/
//===================================
	//购买产品 
	$sql5="select  * FROM ".WEB_ADMIN_TABPOX."sell A LEFT JOIN  ".WEB_ADMIN_TABPOX."selldetail B ON A.sell_id = B.sell_id  where A.status in(1,4,5)  and  A.agencyid =".$_SESSION["currentorgan"].' and  B.item_type=1 AND  B.discount>0 and  A.creattime between "'.$bgdate.'" and "'.$enddate.'"' ; 		
	 
	$inrs5=$this -> dbObj -> Execute($sql5); 
	$produce=0;//产品销售金额
	while ($inrrs5 = $inrs5 -> FetchRow()) {
		$produce=$produce+$inrrs5['value'];	 
	}

/*echo "产品".$produce;*/
//===================================
	//业绩 
	
	

	
$employee=$this -> dbObj -> Execute("select * from  ".WEB_ADMIN_TABPOX."employee  where  employeelevelid  in (2,3)  and  agencyid =".$_SESSION['currentorgan']);//顾问
$beauty=$this -> dbObj -> Execute("select * from  ".WEB_ADMIN_TABPOX."employee  where  employeelevelid in (1)  and  agencyid =".$_SESSION['currentorgan']);//美容师
$membercard=$this -> dbObj -> Execute("select * from  ".WEB_ADMIN_TABPOX."marketingcard  where  marketingcardtype_id   in (5)  and  agencyid =".$_SESSION['currentorgan'].' order by  marketingcard_id desc');//会籍卡
 
		
	//当天业绩
	$consultantperformancetoday=$this ->consultanttodayyeji($bgtodate,$endtodate,$cmldiscount); 
 
	$sqlstr1="select * from  ".WEB_ADMIN_TABPOX."selldetail";
	$sqlstr2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail ";
	$sqlstr3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  ";
	$sqlstr4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  ";
	$sqlstr5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail ";
	$sql6=$sqlstr1." union ".$sqlstr2." union ".$sqlstr3." union ".$sqlstr4." union ".$sqlstr5;
	
 	$inrs6=$this -> dbObj -> Execute("select * FROM  (".$sql6.") A  WHERE A.sell_id in (".$sellid.") AND  A.agencyid =".$_SESSION['currentorgan']); 
	
	while ($inrrs6 = $inrs6 -> FetchRow()) {
//=====顾问=============================		 
		if($inrrs6["employee_id"]<>'' && $inrrs6["employee_id"]<>0){
			
		$employee_id=explode(";",$inrrs6["employee_id"]);
		for ($i=0;$i<count($employee_id);$i++)	{
		if($inrrs6['item_type']==0){
		$consultantperformance["'".$employee_id[$i]."'"]=$consultantperformance["'".$employee_id[$i]."'"]+$inrrs6['value']*$inrrs6['number']*$cmldiscount[$inrrs6['sell_id']]/count($employee_id);//顾问业绩 	
			 
		}else{
		$consultantperformance["'".$employee_id[$i]."'"]=$consultantperformance["'".$employee_id[$i]."'"]+$inrrs6['value']*$inrrs6['number']/count($employee_id);//顾问业绩 
		}
 
	
		if($inrrs6["item_type"]==1){	//顾问产品销售累计
		$consultantproduce["'".$employee_id[$i]."'"]=$consultantproduce["'".$employee_id[$i]."'"]+$inrrs6['value']*$inrrs6['number']/count($employee_id);
		
		}else if($inrrs6["item_type"]==3){
		
		$marketingcard=$this -> dbObj -> GetRow("select * FROM  ".WEB_ADMIN_TABPOX."marketingcard A INNER JOIN ".WEB_ADMIN_TABPOX."marketingcardtype B ON A.marketingcardtype_id =B. marketingcardtype_id   WHERE   A.agencyid =".$_SESSION['currentorgan']." and A.marketingcard_id=".$inrrs6['item_id']);
			if($marketingcard['marketingcardtype_id']==1){//顾问项目卡销售累计
			$consultantitem["'".$employee_id[$i]."'"]=$consultantitem["'".$employee_id[$i]."'"]+$inrrs6['value']*$inrrs6['number']/count($employee_id);
			}else if($marketingcard['marketingcardtype_id']==5){//顾问会籍卡销售累计
			
			$consultantmember["'".$employee_id[$i]."'"]["'".$marketingcard['marketingcard_id']."'"]=$consultantmember["'".$employee_id[$i]."'"]["'".$marketingcard['marketingcard_id']."'"]+$inrrs6['value']*$inrrs6['number']/count($employee_id);
			$consultantmembernumber["'".$employee_id[$i]."'"]["'".$marketingcard['marketingcard_id']."'"]=$consultantmembernumber["'".$employee_id[$i]."'"]["'".$marketingcard['marketingcard_id']."'"]+$inrrs6['number']/count($employee_id);
		  
			}else if($marketingcard['marketingcardtype_id']==7){//顾问储值卡销售累计
			$consultantczk["'".$employee_id[$i]."'"]=$consultantczk["'".$employee_id[$i]."'"]+$inrrs6['value']*$inrrs6['number']/count($employee_id);
			}
		
		
		} 
		
		
		 
		}
		}

//=========美容师===============
		if($inrrs6["beauty_id"]<>'' && $inrrs6["beauty_id"]<>0){	
		if($inrrs6["item_type"]==0 or $inrrs6["item_type"]==2 or $inrrs6["item_type"]==4){
		
		$beauty_id=explode(";",$inrrs6["beauty_id"]);
		for ($i=0;$i<count($beauty_id);$i++)	{
		//$beautymanual["'".$beauty_id[$i]."'"]=$beautymanual["'".$beauty_id[$i]."'"]+$inrrs6['value']*$inrrs6['number']/count($beauty_id);
			if($inrrs6["discount"]>0){
			$beautymanual["'".$beauty_id[$i]."'"]=$beautymanual["'".$beauty_id[$i]."'"]+$inrrs6['value']*$inrrs6['number']/count($beauty_id);
			$consumenumber["'".$beauty_id[$i]."'"]=$consumenumber["'".$beauty_id[$i]."'"]+1;//美容师手工费业绩（非提成）
			
			
		//顾问产品销售累计
		//顾问疗程销售累计
		//顾问会籍卡销售累计
		//顾问储值卡销售累计
			}
		}
		}
		else if($inrrs6["item_type"]==3){//开卡
		 
		$beauty_id=explode(";",$inrrs6["beauty_id"]);
		$marketingcard=$this -> dbObj -> GetRow("select * FROM  ".WEB_ADMIN_TABPOX."marketingcard A INNER JOIN ".WEB_ADMIN_TABPOX."marketingcardtype B ON A.marketingcardtype_id =B. marketingcardtype_id   WHERE   A.agencyid =".$_SESSION['currentorgan']." and A.marketingcard_id=".$inrrs6['item_id']);
		
		for ($i=0;$i<count($beauty_id);$i++)	{
			
			
			if($marketingcard['marketingcardtype_id']==1){//美容师项目卡销售累计
			
			$beautyitem["'".$beauty_id[$i]."'"]=$beautyitem["'".$beauty_id[$i]."'"]+$inrrs6['value']*$inrrs6['number']/count($beauty_id);
			}/*else if($marketingcard['marketingcardtype_id']==5){//美容师会籍卡销售累计
			
			$beautymember["'".$beauty_id[$i]."'"]["'".$marketingcard['marketingcard_id']."'"]=$beautymember["'".$beauty_id[$i]."'"]["'".$marketingcard['marketingcard_id']."'"]+$inrrs6['value']*$inrrs6['number']/count($beauty_id);//美容师会籍卡业绩
			$beautymembernumber["'".$beauty_id[$i]."'"]["'".$marketingcard['marketingcard_id']."'"]=$beautymembernumber["'".$beauty_id[$i]."'"]["'".$marketingcard['marketingcard_id']."'"]+$inrrs6['number']/count($beauty_id);//美容会籍卡数目			
			 
			}else if($marketingcard['marketingcardtype_id']==7){//美容师储值卡销售累计
			$beautyczk["'".$beauty_id[$i]."'"]=$beautyczk["'".$beauty_id[$i]."'"]+$inrrs6['value']*$inrrs6['number']/count($beauty_id);
			}*/else{
				
				$inrscard=$this -> dbObj -> Execute("select * FROM   ".WEB_ADMIN_TABPOX."lookupcard     order by orderid");
				while ($inrrscard = $inrscard -> FetchRow()) {	
				
				if($marketingcard['inheritedid'] ==$inrrscard['cardidlist']){
					
					if($inrrscard['type']=='1'){
					
					$beautymembernumber["'".$beauty_id[$i]."'"]["'".$inrrscard['cardidlist']."'"]=$beautymembernumber["'".$beauty_id[$i]."'"]["'".$inrrscard['cardidlist']."'"]+$inrrs6['number']/count($beauty_id);
					
					}else if($inrrscard['type']=='0'){
					$beautymembernumber["'".$beauty_id[$i]."'"]["'".$inrrscard['cardidlist']."'"]=$beautymembernumber["'".$beauty_id[$i]."'"]["'".$inrrscard['cardidlist']."'"]+$inrrs6['value']*$inrrs6['number']/count($beauty_id);;	
					}
				}
				}

		}
			
		}
		}

	 }
	 
	 if($inrrs6["item_type"]==1){//美容师产品销售
		 $employee_id=explode(";",$inrrs6["employee_id"]);
		 for ($i=0;$i<count($employee_id);$i++)	{
		 $beautyproduce["'".$employee_id[$i]."'"]=$beautyproduce["'".$employee_id[$i]."'"]+$inrrs6['value']*$inrrs6['number']/count($employee_id);
		 }
		 }
	}
 
$i=0;
$t -> set_var('ml');//会籍卡列表
$inrscard=$this -> dbObj -> Execute("select * FROM   ".WEB_ADMIN_TABPOX."lookupcard     order by orderid");
while ($inrrscard = $inrscard -> FetchRow()) {//会籍卡
        $marketingcard_id[$i]=$membercarddata['marketingcard_id'];	
		$i=$i+1;
		$t -> set_var('membercarname',$inrrscard['lookupcard_name']);
		$t -> parse('ml','membercarlist',true);	
	} 
		$marketingcard_id_size=$i;
		
		
		$t -> set_var('c');//编译顾问
		$consultantlpf_no=0;
		
$consultantitemtoday=$this ->consultantitemtoday($sellidtoday);
$consultantproducetoday=$this ->consultantproducetoday($sellidtoday);
$consultantaddupyeji=$this ->consultantaddupyeji($sellid);
$consultantdingjintoday=$this ->consultantdingjintoday($sellidtoday);
$addupdingjin=$this ->consultantdingjintoday($sellid);

$yufutoday=$this ->consultantyufu($sellidtoday);
$addupyufu=$this ->consultantyufu($sellid);//预收款

while ($employeedata = $employee -> FetchRow()) {//顾问业绩
		$consultantlpf_no=$consultantlpf_no+1;
 		$t -> set_var('consultantlpf_no',$consultantlpf_no);
	 	$t -> set_var('consultant_name',$employeedata['employee_name']);
		//$consultantperformancetoday["'".$employeedata['employee_id']."'"]=$consultantperformancetoday["'".$employeedata['employee_id']."'"]==''?0:$consultantperformancetoday["'".$employeedata['employee_id']."'"];
		$consultantperformancetodaytotal=$consultantperformancetodaytotal+$consultantperformancetoday["'".$employeedata['employee_id']."'"];
		$t -> set_var('consultantperformancetoday',sprintf ("%01.2f",$consultantperformancetoday["'".$employeedata['employee_id']."'"]));
		$t -> set_var('consultantperformance',sprintf ("%01.2f",$consultantaddupyeji["'".$employeedata['employee_id']."'"]));
		//$consultantproduce["'".$employeedata['employee_id']."'"]=$consultantproduce["'".$employeedata['employee_id']."'"]==''?0:$consultantproduce["'".$employeedata['employee_id']."'"];
		$t -> set_var('consultantproduce',$consultantproduce["'".$employeedata['employee_id']."'"]);
		$totalconsultantproduce=$totalconsultantproduce+$consultantproduce["'".$employeedata['employee_id']."'"];
		
		//$consultantproducetoday["'".$employeedata['employee_id']."'"]=$consultantproducetoday["'".$employeedata['employee_id']."'"]==''?0:$consultantproducetoday["'".$employeedata['employee_id']."'"];
		$totalconsultantproducetoday=$totalconsultantproducetoday+$consultantproducetoday["'".$employeedata['employee_id']."'"];
		$t -> set_var('consultantproducetoday',$consultantproducetoday["'".$employeedata['employee_id']."'"]);
		
		$totalconsultantdingjin=$totalconsultantdingjin+$consultantdingjintoday["'".$employeedata['employee_id']."'"];
		$t -> set_var('consultantdingjin',$consultantdingjintoday["'".$employeedata['employee_id']."'"]);
		
		$totalyufu=$totalyufu+$yufutoday["'".$employeedata['employee_id']."'"];
		$t -> set_var('yufutoday',$yufutoday["'".$employeedata['employee_id']."'"]);		//$consultantitem["'".$employeedata['employee_id']."'"]=$consultantitem["'".$employeedata['employee_id']."'"]==''?0:$consultantitem["'".$employeedata['employee_id']."'"];
		
		
		$totaladdupdingjin=$totaladdupdingjin+$addupdingjin["'".$employeedata['employee_id']."'"];
		$t -> set_var('addupdingjin',$addupdingjin["'".$employeedata['employee_id']."'"]);
		
		$totaladdupyufu=$totaladdupyufu+$addupyufu["'".$employeedata['employee_id']."'"];
		$t -> set_var('addupyufu',$addupyufu["'".$employeedata['employee_id']."'"]);
		
		$t -> set_var('consultantitem',$consultantitem["'".$employeedata['employee_id']."'"]);
		
		
		 
		//$consultantitemtoday["'".$employeedata['employee_id']."'"]=$consultantitemtoday["'".$employeedata['employee_id']."'"]==''?0:$consultantitemtoday["'".$employeedata['employee_id']."'"];
		$t -> set_var('consultantitemtoday',$consultantitemtoday["'".$employeedata['employee_id']."'"]); //顾问当天疗程卡业绩
		$totalconsultantitemtoday=$totalconsultantitemtoday+$consultantitemtoday["'".$employeedata['employee_id']."'"];
		
		
		//$consultantczk["'".$employeedata['employee_id']."'"]=$consultantczk["'".$employeedata['employee_id']."'"]==''?0:$consultantczk["'".$employeedata['employee_id']."'"];
		$t -> set_var('consultantczk',$consultantczk["'".$employeedata['employee_id']."'"]);
	 $consultantperformancestr=$consultantperformancestr==''?$employeedata['employee_id'].','. $consultantperformance["'".$employeedata['employee_id']."'"]:$consultantperformancestr.';'.$employeedata['employee_id'].','. $consultantperformance["'".$employeedata['employee_id']."'"];
	$totolconsultant=$totolconsultant+$consultantaddupyeji["'".$employeedata['employee_id']."'"];//本月顾问总业绩
	$consultantproducestr=$consultantproducestr==''?$employeedata['employee_id'].','.$consultantproduce["'".$employeedata['employee_id']."'"]:$consultantproducestr.';'.$employeedata['employee_id'].','.$consultantproduce["'".$employeedata['employee_id']."'"];//顾问产品销售值对
	$consultantczkstr=$consultantczkstr==''?$employeedata['employee_id'].','.$consultantczk["'".$employeedata['employee_id']."'"]:$consultantczkstr.';'.$employeedata['employee_id'].','.$consultantczk["'".$employeedata['employee_id']."'"];//顾问储值卡销售值对
	
/*		while ($membercarddata = $membercard -> FetchRow()) {//会籍卡
		
		$consultantmemberstr=$consultantmemberstr==''?$employeedata['employee_id'].','.$consultantmember["'".$employeedata['employee_id']."'"]["'".$membercarddata['marketingcard_id']."'"]:$consultantmemberstr.';'.$employeedata['employee_id'].','.$consultantmember["'".$employeedata['employee_id']."'"]["'".$membercarddata['marketingcard_id']."'"];
		
		 echo "顾问id".$employeedata['employee_id'];
		}*/
		$consultantmemberstr='';
		$t -> set_var('cm');//顾问开卡业绩
$inrscard=$this -> dbObj -> Execute("select * FROM   ".WEB_ADMIN_TABPOX."lookupcard     order by orderid");		
while ($inrrscard = $inrscard -> FetchRow()) {	 

	  if($inrrscard['type']=='1'){
		  
		 //echo  "select count(*) as cardnumber FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3  and B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$inrrsagency['agency_id'] ;
		 $cardnumber1=$this -> dbObj -> GetRow("select count(*) as cardnumber FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3  and A.employee_id=".$employeedata['employee_id']." and B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$_SESSION['currentorgan']);
           $cardnumber1['cardnumber']=$cardnumber1['cardnumber']==0?'':$cardnumber1['cardnumber'];
		   $t -> set_var('consultantmembernumber',$cardnumber1['cardnumber']);
		   //$totalcard[$inrrscard['lookupcard_id']]=  $totalcard[$inrrscard['lookupcard_id']]+ $cardnumber['cardnumber'];
		 }else if($inrrscard['type']=='0'){
			// echo "select sum(A.amount) as cardjine FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3  and B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$inrrsagency['agency_id'];
	  
			 $cardjine1=$this -> dbObj -> GetRow("select sum(A.amount) as cardjine FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3 and  A.employee_id=".$employeedata['employee_id']." and B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$_SESSION['currentorgan']);
			   $cardjine1['cardjine']=  $cardjine1['cardjine']==0?'': $cardjine1['cardjine'];			  
			  $t -> set_var('consultantmembernumber', $cardjine1['cardjine']);
			  //echo "select sum(A.amount) as cardjine FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3 and B.employee_id=".$employeedata['employee_id']." and B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$_SESSION['currentorgan'];
			  //$totalcard[$inrrscard['lookupcard_id']]=  $totalcard[$inrrscard['lookupcard_id']]+ $cardjine['cardjine'];
		 }	
		 $t -> parse('cm','consultantmemberlist',true);	
		
}
		
		
/*		for($i=0;$i<$marketingcard_id_size;$i++){//会籍卡
		
 
				$totalconsultantmember["'".$marketingcard_id[$i]."'"]=$totalconsultantmember["'".$marketingcard_id[$i]."'"]+$consultantmember["'".$employeedata['employee_id']."'"]["'".$marketingcard_id[$i]."'"];	
						 
			$consultantmemberstr=$consultantmemberstr==''?$consultantmember["'".$employeedata['employee_id']."'"]["'".$marketingcard_id[$i]."'"]:$consultantmemberstr.','.$consultantmember["'".$employeedata['employee_id']."'"]["'".$marketingcard_id[$i]."'"];
			
			$totalconsultantmembernumber["'".$marketingcard_id[$i]."'"]=$totalconsultantmembernumber["'".$marketingcard_id[$i]."'"]+$consultantmembernumber["'".$employeedata['employee_id']."'"]["'".$marketingcard_id[$i]."'"];	
			$t -> set_var('consultantmembernumber',$consultantmembernumber["'".$employeedata['employee_id']."'"]["'".$marketingcard_id[$i]."'"]);	
			
			$t -> set_var('consultantmember',$consultantmember["'".$employeedata['employee_id']."'"]["'".$marketingcard_id[$i]."'"]);	
			$t -> parse('cm','consultantmemberlist',true);	
		
		}
		
		$consultantmemberstr=$employeedata['employee_id'].';'.$consultantmemberstr;
		
		$consmemstr=$consmemstr==''?$consultantmemberstr:$consmemstr.'@@@'.$consultantmemberstr;*/

		//$t -> parse('c','consultant',true);	
	
/*	$t -> set_var('tcm');//顾问会籍卡业绩
	for($i=0;$i<$marketingcard_id_size;$i++){
			$t -> set_var('totalconsultantmember', $totalconsultantmember["'".$marketingcard_id[$i]."'"]);	
			$t -> set_var('totalconsultantmembernumber', $totalconsultantmembernumber["'".$marketingcard_id[$i]."'"]);	
			
			$t -> parse('tcm','totalconsultantmemberlist',true);	
	}*/
	$t -> set_var('tcm');//顾问会籍卡总业绩
$inrscard=$this -> dbObj -> Execute("select * FROM   ".WEB_ADMIN_TABPOX."lookupcard     order by orderid");		
while ($inrrscard = $inrscard -> FetchRow()) {	 

	  if($inrrscard['type']=='1'){
		 
		  $cardnumber=$this -> dbObj -> GetRow("select count(*) as cardnumber FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3  and  B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$_SESSION['currentorgan']);
          $cardnumber['cardnumber']=$cardnumber['cardnumber']==0?'':$cardnumber['cardnumber'];
		 $t -> set_var('totalconsultantmembernumber',$cardnumber['cardnumber']);
		    
		 }else if($inrrscard['type']=='0'){
		 
			 $cardjine=$this -> dbObj -> GetRow("select sum(A.amount) as cardjine FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3 and   B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$_SESSION['currentorgan']);
			// echo "select sum(A.amount) as cardjine FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3 and   B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$_SESSION['currentorgan'];
			  $cardjine['cardjine']=$cardjine['cardjine']==0?'':$cardjine['cardjine'];
			  $t -> set_var('totalconsultantmembernumber',$cardjine['cardjine']);
			   
		 }	
		$t -> parse('tcm','totalconsultantmemberlist',true);
		
}	  
	
	$totalconsultantczk=$totalconsultantczk+$consultantczk["'".$employeedata['employee_id']."'"];
	$totalconsultantitem=$totalconsultantitem+$consultantitem["'".$employeedata['employee_id']."'"];	
	$t -> parse('c','consultant',true);	
	}  
	
		$t -> set_var('consultantperformancetodaytotal',sprintf ("%01.2f",$consultantperformancetodaytotal));
		$t -> set_var('totalcproducet',sprintf ("%01.2f",$totalconsultantproducetoday));
		$t -> set_var('totalconsultantdingjin',sprintf ("%01.2f",$totalconsultantdingjin));
		$t -> set_var('totalyufu',sprintf ("%01.2f",$totalyufu));
		
		$t -> set_var('totaladdupdingjin',sprintf ("%01.2f",$totaladdupdingjin));
		$t -> set_var('totaladdupyufu',sprintf ("%01.2f",$totaladdupyufu));
		$t -> set_var('totalconsultantproduce',sprintf ("%01.2f",$totalconsultantproduce));
		$t -> set_var('totalconsultantczk',sprintf ("%01.2f",$totalconsultantczk));	
		$t -> set_var('totalconsultantitem',sprintf ("%01.2f",$totalconsultantitem));
		$t -> set_var('totolconsultant',sprintf ("%01.2f",$totolconsultant));	
		$t -> set_var('totalconsultantitemtoday',sprintf ("%01.2f",$totalconsultantitemtoday));	
		 
/*  echo "产品销售".$consultantproducestr;
  echo "储值卡销售".$consultantczkstr;
  echo "会籍卡".$consmemstr;*/
  
$totolconsumenumber=0;
 		$t -> set_var('b');//编译美容师
		$beautylpf_no=0;
$beautyconsumenumbertoday=$this->beautyconsumenumbertoday($sellidtoday);//美容师 当天项次	

$beautyitemtoday=$this->beautyitemtoday($sellidtoday);//美容师 当天疗程卡业绩	
$beautyproducetoday=$this->beautyproducetoday($sellidtoday);//美容师 当天销售产品	
while ($beautydata = $beauty -> FetchRow()) {//美容师业绩
		$beautylpf_no=$beautylpf_no+1;
		$t -> set_var('beautylpf_no',$beautylpf_no);
 	if(!$beautymanual["'".$beautydata['employee_id']."'"]){
		$beautymanual["'".$beautydata['employee_id']."'"]=0;
	}
 	if(!$consumenumber["'".$beautydata['employee_id']."'"]){
		$consumenumber["'".$beautydata['employee_id']."'"]=0;
	}	
	
		$t -> set_var('consumenumber',$consumenumber["'".$beautydata['employee_id']."'"]);
		$t -> set_var('beauty_name',$beautydata['employee_name']);
		
		//$beautyconsumenumbertoday["'".$beautydata['employee_id']."'"]=$beautyconsumenumbertoday["'".$beautydata['employee_id']."'"]==''?0:$beautyconsumenumbertoday["'".$beautydata['employee_id']."'"];
		 
		$t -> set_var('beautyconsumenumbertoday',$beautyconsumenumbertoday["'".$beautydata['employee_id']."'"]);
		$totalbeautyconsumenumbertoday=$totalbeautyconsumenumbertoday+$beautyconsumenumbertoday["'".$beautydata['employee_id']."'"];
		
		$t -> set_var('beautyproducetoday',$beautyproducetoday["'".$beautydata['employee_id']."'"]);//美容师 当天销售产品
		$totalbeautyproducetoday=$totalbeautyproducetoday+$beautyproducetoday["'".$beautydata['employee_id']."'"];	
		
		$t -> set_var('beautyitemtoday',$beautyitemtoday["'".$beautydata['employee_id']."'"]);//美容师 当天疗程卡
		$totalbeautyitemtoday=$totalbeautyitemtoday+$beautyitemtoday["'".$beautydata['employee_id']."'"];	
		
		$t -> set_var('beautyproduce',$beautyproduce["'".$beautydata['employee_id']."'"]);//美容师 产品 累计销售
		$totalbeautyproduce=$totalbeautyproduce+$beautyproduce["'".$beautydata['employee_id']."'"];
		
		
		
		$t -> set_var('beautymanual',$beautymanual["'".$beautydata['employee_id']."'"]);
		//$t -> parse('b','beauty',true);
    $beautystr=$beautystr==''?$beautydata['employee_id'].','.$beautymanual["'".$beautydata['employee_id']."'"]:$beautystr.';'.$beautydata['employee_id'].','.$beautymanual["'".$beautydata['employee_id']."'"];
	$totolbeauty=$totolbeauty+$beautymanual["'".$beautydata['employee_id']."'"];//本月美容师总业绩
   
	 $consumenumberstr=$consumenumberstr==''?$beautydata['employee_id'].','.$consumenumber["'".$beautydata['employee_id']."'"]:$consumenumberstr.';'.$employeedata['employee_id'].','.$consumenumber["'".$beautydata['employee_id']."'"];
	$totolconsumenumber=$totolconsumenumber+$consumenumber["'".$beautydata['employee_id']."'"];//本月美容师总项次
	//美容师会籍卡业绩
	 
/*		$t -> set_var('bm');
		for($i=0;$i<$marketingcard_id_size;$i++){//会籍卡
		 
 
			 $totalbeautymember["'".$marketingcard_id[$i]."'"]=$totalbeautymember["'".$marketingcard_id[$i]."'"]+$beautymember["'".$beautydata['employee_id']."'"]["'".$marketingcard_id[$i]."'"];	
			 
			 $totalbeautymembernumber["'".$marketingcard_id[$i]."'"]=$totalbeautymembernumber["'".$marketingcard_id[$i]."'"]+$beautymembernumber["'".$beautydata['employee_id']."'"]["'".$marketingcard_id[$i]."'"];	
			  
			$t -> set_var('beautymembernumber',$beautymembernumber["'".$beautydata['employee_id']."'"]["'".$marketingcard_id[$i]."'"]);	
			$t -> set_var('beautymember',$beautymember["'".$beautydata['employee_id']."'"]["'".$marketingcard_id[$i]."'"]);	
			$t -> parse('bm','beautymemberlist',true);	
			
		} */


$inrscard=$this -> dbObj -> Execute("select * FROM   ".WEB_ADMIN_TABPOX."lookupcard     order by orderid");		
$t -> set_var('bm');
$cgbeautycardnumber=0;
while ($inrrscard = $inrscard -> FetchRow()) {	 

 	
	$t -> set_var('beautymembernumber',$beautymembernumber["'".$beautydata['employee_id']."'"]["'".$inrrscard['cardidlist']."'"]);		
	$t -> parse('bm','beautymemberlist',true);		
}	



	



	//$beautyczk["'".$beautydata['employee_id']."'"]=$beautyczk["'".$beautydata['employee_id']."'"]==''?0:$beautyczk["'".$beautydata['employee_id']."'"];
	$t -> set_var('beautyczk',$beautyczk["'".$beautydata['employee_id']."'"]);
	//$beautyitem["'".$beautydata['employee_id']."'"]=$beautyitem["'".$beautydata['employee_id']."'"]==''?0:$beautyitem["'".$beautydata['employee_id']."'"];
	$t -> set_var('beautyitem',$beautyitem["'".$beautydata['employee_id']."'"]);
	$totalbeautyczk=$totalbeautyczk+$beautyczk["'".$beautydata['employee_id']."'"];
	$totalbeautyitem=$totalbeautyitem+$beautyitem["'".$beautydata['employee_id']."'"];
	$t -> parse('b','beauty',true);
	} 
	$t -> set_var('totalbeautyitem',$totalbeautyitem);	
	$t -> set_var('totalbeautyczk',$totalbeautyczk);
	
	$t -> set_var('tbm');//美容师开卡总业绩
/*	for($i=0;$i<$marketingcard_id_size;$i++){
			$t -> set_var('totalbeautymember', $totalbeautymember["'".$marketingcard_id[$i]."'"]);	
			$t -> set_var('totalbeautymembernumber',$totalbeautymembernumber["'".$marketingcard_id[$i]."'"]);
			$t -> parse('tbm','totalbeautymemberlist',true);	
	}
*/
$inrscard=$this -> dbObj -> Execute("select * FROM   ".WEB_ADMIN_TABPOX."lookupcard     order by orderid");		
while ($inrrscard = $inrscard -> FetchRow()) {	//美容师开卡业绩 
	  if($inrrscard['type']=='1'){
		  	$totalbeautymembernumber=$this -> dbObj -> GetRow("select count(*) as cardnumber FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3  and  B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$_SESSION['currentorgan']);
         $totalbeautymembernumber['cardnumber']=$totalbeautymembernumber['cardnumber']==0?'':$totalbeautymembernumber['cardnumber'];
		  
		
	   $t -> set_var('totalbeautymembernumber',$totalbeautymembernumber["cardnumber"]);
	  }else if($inrrscard['type']=='0'){
 		$totalbeautymembernumber=$this -> dbObj -> GetRow("select sum(A.amount) as cardjine FROM  ".WEB_ADMIN_TABPOX."sellcarddetail A INNER JOIN   ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id left join  ".WEB_ADMIN_TABPOX."marketingcard C ON A.item_id=C.marketingcard_id  WHERE  B.status in(1,4,5) and A.item_type =3 and  B.creattime between '".$bgdate."' and '".$enddate."' and C.inheritedid in(".$inrrscard['cardidlist'].") and A.agencyid =".$_SESSION['currentorgan']);
			  $totalbeautymembernumber['cardjine']= $totalbeautymembernumber['cardjine']==0?'':$totalbeautymembernumber['cardjine'];	  
		    $t -> set_var('totalbeautymembernumber',$totalbeautymembernumber["cardjine"]);
	  }
	  	$t -> parse('tbm','totalbeautymemberlist',true);	
	  
}
	$t -> set_var('totalbeautyitemtoday',$totalbeautyitemtoday);//美容师 当天累计疗程卡 业绩
	$t -> set_var('totalbeautyproducetoday',$totalbeautyproducetoday);//美容师 当天累计销售产品
	$t -> set_var('totalbeautyconsumenumbertoday',$totalbeautyconsumenumbertoday);
	$t -> set_var('totolconsumenumber',$totolconsumenumber);

	$t -> set_var('totalbeautyproduce',$totalbeautyproduce);
	
	
/*	
	echo "本月消费项次".$totolconsumenumber;
	echo "美容师业绩".$beautystr;
	echo "美容师总业绩".$totolbeauty;
	echo "顾问业绩".  $consultantperformancestr;
	echo "顾问总业绩".$totolconsultant;*/
/* 
	$employee=$this -> dbObj -> Execute("select * from  ".WEB_ADMIN_TABPOX."employee  where  employeelevelid  in (2)  and  agencyid =".$_SESSION['currentorgan']);//顾问
 	
		$performance=0;
		while ($employeedata = $employee -> FetchRow()) {
		$performance=$performance+$this -> dbObj -> Execute("select  sum(value)  FROM  ($sql5) A  WHERE A.employee_id=".$employeedata['employee_id']." and A.agencyid =".$_SESSION['currentorgan']); //顾问业绩
		echo "select  sum(value)  FROM  ($sql5) A  WHERE A.employee_id=".$employeedata['employee_id']." and A.agencyid =".$_SESSION['currentorgan'];
		}
	
	$beauty=$this -> dbObj -> Execute("select * from  ".WEB_ADMIN_TABPOX."employee  where  employeelevel_id in (1)  and  agencyid =".$_SESSION['currentorgan']);//美容师
	 
	while ($inrrs5 = $inrs5 -> FetchRow()) {
		
		if($inrrs5['item_type']==0){//服务 
		$produce=$produce+$inrrs5['value'];	 
		}else if($inrrs5['item_type']==1){//产品
		
		}else if($inrrs5['item_type']==2){//消费
		
		}else if($inrrs5['item_type']==3){//卡项
		
		}else if($inrrs5['item_type']==4){//券类消费
		
		}else if($inrrs5['item_type']==5){//款项
		}
	}

echo $produce;*/



/*
			$t -> set_var('c');//编译顾问
			$inrs = &$this -> dbObj -> Execute("select r.* from ".WEB_ADMIN_TABPOX."grouprole gr inner join ".WEB_ADMIN_TABPOX."role r on gr.roleid=r.roleid where gr.groupid = ".$rrs['groupid']);
			while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> parse('r2','role',true);
			}
			$inrs -> Close();


			 
			$t -> set_var('c');//编译美容师
			$inrs = &$this -> dbObj -> Execute("select r.* from ".WEB_ADMIN_TABPOX."grouprole gr inner join ".WEB_ADMIN_TABPOX."role r on gr.roleid=r.roleid where gr.groupid = ".$rrs['groupid']);
			while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> parse('r2','role',true);
			}
			$inrs -> Close();*/
 

		//$t -> parse('b','beauty',true);
		//$t -> parse('t','test',true);	 
		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
}

 function consultanttodayyeji($bgtodate,$endtodate,$cmldiscount){//顾问当天业绩
	$this->perObj=new performancehistory();
	$sqltoday="select  * FROM ".WEB_ADMIN_TABPOX."sell  where  status in(1,4,5)  and   agencyid =".$_SESSION["currentorgan"].' and  creattime between "'.$bgtodate.'" and "'.$endtodate.'"' ; 
	
	$inrstoday=$this -> dbObj -> Execute($sqltoday);
	 
	while ($inrrstoday = $inrstoday -> FetchRow()) {
		$sellidtoday=$sellidtoday==''?$inrrstoday['sell_id']:$sellidtoday.",".$inrrstoday['sell_id'];	 
	}
	$sellidtoday=$sellidtoday==''?0:$sellidtoday;
	$consultantperformancetoday1=$this->perObj->main($sellidtoday);
/*	
	$sqlstr1="select * from  ".WEB_ADMIN_TABPOX."selldetail";
	$sqlstr2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail ";
	$sqlstr3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  ";
	$sqlstr4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  ";
	$sqlstr5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail ";
	$sql=$sqlstr1." union ".$sqlstr2." union ".$sqlstr3." union ".$sqlstr4." union ".$sqlstr5;
 	$inrstoday1=$this -> dbObj -> Execute("select * FROM  (".$sql.") A  WHERE A.sell_id in (".$sellidtoday.") AND  A.agencyid =".$_SESSION['currentorgan']); 	 
	while ($inrrstoday1 = $inrstoday1 -> FetchRow()) {
//=====顾问=============================

		if($inrrstoday1["employee_id"]<>'' && $inrrstoday1["employee_id"]<>0){
		$employee_id=explode(";",$inrrstoday1["employee_id"]);
		
		for ($i=0;$i<count($employee_id);$i++)	{
		
		if($inrrstoday1['item_type']==0){
			
		$consultantperformancetoday1["'".$employee_id[$i]."'"]=$consultantperformancetoday1["'".$employee_id[$i]."'"]+$inrrstoday1['value']*$inrrstoday1['number']*$cmldiscount[$inrrstoday1['sell_id']]/count($employee_id);
			//$consultantperformancetoday1["'".$employee_id[$i]."'"]=$consultantperformancetoday1["'".$employee_id[$i]."'"]+($inrrstoday1['value']*$inrrstoday1['number']*$cmldiscount[$inrrstoday1['sell_id']])/count($employee_id);//顾问业绩 	
			 
		}else{
		$consultantperformancetoday1["'".$employee_id[$i]."'"]=$consultantperformancetoday1["'".$employee_id[$i]."'"]+$inrrstoday1['value']*$inrrstoday1['number']/count($employee_id);
		}
		}

		}
	}
*/ 
 return $consultantperformancetoday1;	
 }
 
  function consultantaddupyeji($sellid){//顾问累计业绩
	$this->perObj=new performancehistory();

	$consultantperformance=$this->perObj->main($sellid);
 
 	return $consultantperformance;	
 }
 function shougongfei($sellid){
	$this->perObj1=new performancehistory();
	 $beautymanual1=$this->perObj1->main($sellid);
	
	return $beautymanual1;
}
 
 function consultantproducetoday($sellidtoday){//顾问当天销售业绩
 
	$sellidtoday=$sellidtoday==''?0:$sellidtoday;
	$sqlstr1="select * from  ".WEB_ADMIN_TABPOX."selldetail";
/*	$sqlstr2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail ";
	$sqlstr3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  ";
	$sqlstr4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  ";
	$sqlstr5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail ";*/
	$sql=$sqlstr1;
 	$inrstoday1=$this -> dbObj -> Execute("select * FROM  (".$sql.") A  WHERE A.sell_id in (".$sellidtoday.") AND  A.agencyid =".$_SESSION['currentorgan']); 	
	while ($inrrstoday1 = $inrstoday1 -> FetchRow()) {
//=====顾问=============================		 
		if($inrrstoday1["employee_id"]<>'' && $inrrstoday1["employee_id"]<>0){
		$employee_id=explode(";",$inrrstoday1["employee_id"]);
		for ($i=0;$i<count($employee_id);$i++)	{
		$consultantproducetoday1["'".$employee_id[$i]."'"]=$consultantproducetoday1["'".$employee_id[$i]."'"]+$inrrstoday1['value']*$inrrstoday1['number']/count($employee_id);

		 }

		}
	}
 
 return $consultantproducetoday1;	
 }
 
 function consultantdingjintoday($sellidtoday){//顾问定金
 
	$sellidtoday=$sellidtoday==''?0:$sellidtoday;
/*	$sqlstr1="select * from  ".WEB_ADMIN_TABPOX."selldetail";
	$sqlstr2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail ";
	$sqlstr3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  ";
	$sqlstr4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  ";*/
	$sqlstr5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail ";
	$sql=$sqlstr5;
 	$inrstoday1=$this -> dbObj -> Execute("select * FROM  (".$sql.") A  WHERE A.sell_id in (".$sellidtoday.") AND A.item_type=5 and A.item_id=2 and  A.agencyid =".$_SESSION['currentorgan']); 	
	while ($inrrstoday1 = $inrstoday1 -> FetchRow()) {
//=====顾问=============================		 
		if($inrrstoday1["employee_id"]<>'' && $inrrstoday1["employee_id"]<>0){
		$employee_id=explode(";",$inrrstoday1["employee_id"]);
		for ($i=0;$i<count($employee_id);$i++)	{
		$consultantdingjintoday["'".$employee_id[$i]."'"]=$consultantdingjintoday["'".$employee_id[$i]."'"]+$inrrstoday1['value']*$inrrstoday1['number']/count($employee_id);

		 }

		}
	}
 
 return $consultantdingjintoday;	
 } 
 
 function consultantyufu($sellid){//顾问预付手工 
	$sellid=$sellid==''?0:$sellid;
/*	$sqlstr1="select * from  ".WEB_ADMIN_TABPOX."selldetail";
	$sqlstr2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail ";
	$sqlstr3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  ";
	$sqlstr4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  ";*/
	$sqlstr5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail ";
	$sql=$sqlstr5;
 	$inrs=$this -> dbObj -> Execute("select * FROM  (".$sql.") A  WHERE A.sell_id in (".$sellid.") AND A.item_type=5 and A.item_id=0 and  A.agencyid =".$_SESSION['currentorgan']); 	
	while ($inrrs = $inrs -> FetchRow()) {
//=====顾问=============================		 
		if($inrrs["employee_id"]<>'' && $inrrs["employee_id"]<>0){
		$employee_id=explode(";",$inrrs["employee_id"]);
		for ($i=0;$i<count($employee_id);$i++)	{
		$consultantyufu["'".$employee_id[$i]."'"]=$consultantyufu["'".$employee_id[$i]."'"]+$inrrs['value']*$inrrs['number']/count($employee_id);

		 }

		}
	}
 
 return $consultantyufu;	
 } 
function consultantitemtoday($sellidtoday){//顾问当天 疗程卡(项目卡itemcard)业绩
 
	$sellidtoday=$sellidtoday==''?0:$sellidtoday;
	$sqlstr1="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail";
/*	$sqlstr2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail ";
	$sqlstr3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  ";
	$sqlstr4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  ";
	$sqlstr5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail ";*/
	$sql=$sqlstr1;
 	$inrstoday1=$this -> dbObj -> Execute("select * FROM  (".$sql.") A  WHERE A.sell_id in (".$sellidtoday.") AND A.item_type=3 AND  A.agencyid =".$_SESSION['currentorgan']); 


	
	while ($inrrstoday1 = $inrstoday1 -> FetchRow()) {
		
//=====顾问=============================		 
		$marketingcard=$this -> dbObj -> GetRow("select * FROM  ".WEB_ADMIN_TABPOX."marketingcard A INNER JOIN ".WEB_ADMIN_TABPOX."marketingcardtype B ON A.marketingcardtype_id =B. marketingcardtype_id   WHERE   A.agencyid =".$_SESSION['currentorgan']." and A.marketingcard_id=".$inrrstoday1['item_id']);	
		
		if($inrrstoday1["employee_id"]<>'' && $inrrstoday1["employee_id"]<>0){
		$employee_id=explode(";",$inrrstoday1["employee_id"]);
		for ($i=0;$i<count($employee_id);$i++)	{
			
		
		if($marketingcard['marketingcardtype_id']==1){//顾问项目卡销售累计
		$consultantitemtoday1["'".$employee_id[$i]."'"]=$consultantitemtoday1["'".$employee_id[$i]."'"]+$inrrstoday1['value']*$inrrstoday1['number']/count($employee_id);
			}


		 }

		}
	}
 
 return $consultantitemtoday1;	
 }
function beautyconsumenumbertoday($sellidtoday){//美容师当天 项次 业绩
 
	$sellidtoday=$sellidtoday==''?0:$sellidtoday;
	$sqlstr3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  ";
	$sqlstr4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  ";

	$sql=$sqlstr3." union ".$sqlstr4;
 	$inrstoday1=$this -> dbObj -> Execute("select * FROM  (".$sql.") A  WHERE A.sell_id in (".$sellidtoday.") AND A.discount>0 AND A.item_type in(2,4) AND  A.agencyid =".$_SESSION['currentorgan']); 
 
	while ($inrrstoday1 = $inrstoday1 -> FetchRow()) {
		if($inrrstoday1["beauty_id"]<>'' && $inrrstoday1["beauty_id"]<>0){
		$beauty_id=explode(";",$inrrstoday1["beauty_id"]);
		for ($i=0;$i<count($beauty_id);$i++){	 
		 
		$beautyconsumenumbertoday["'".$beauty_id[$i]."'"]=$beautyconsumenumbertoday["'".$beauty_id[$i]."'"]+1;
		 }

		}
	}
 
 return $beautyconsumenumbertoday;	
 } 
 function beautyproducetoday($sellidtoday){//美容师当天 项次 业绩

	$sellidtoday=$sellidtoday==''?0:$sellidtoday;
	$sqlstr3="select * from  ".WEB_ADMIN_TABPOX."selldetail  ";
	if ($sellidtoday<>0){
	

	$sql=$sqlstr3 ;
 	$inrstoday1=$this -> dbObj -> Execute("select * FROM  (".$sql.") A  WHERE A.sell_id in (".$sellidtoday.") AND A.discount>0 AND A.item_type in(1) AND  A.agencyid =".$_SESSION['currentorgan']); 

	while ($inrrstoday1 = $inrstoday1 -> FetchRow()) {
		if($inrrstoday1["employee_id"]<>'' && $inrrstoday1["employee_id"]<>0){
		$employee_id=explode(";",$inrrstoday1["employee_id"]);
		for ($i=0;$i<count($employee_id);$i++){	 
		 
		$beautyproducetoday["'".$employee_id[$i]."'"]=$beautyproducetoday["'".$employee_id[$i]."'"]+$inrrstoday1['value']*$inrrstoday1['number']/count($employee_id);
		 }

		}
	}
	
 return $beautyproducetoday;	
 }else{
  return 0;
 }
 
 } 
 
function beautyitemtoday($sellidtoday){//美容师 当天 疗程卡(项目卡itemcard)业绩
 
	$sellidtoday=$sellidtoday==''?0:$sellidtoday;
	$sqlstr1="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail";
/*	$sqlstr2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail ";
	$sqlstr3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  ";
	$sqlstr4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  ";
	$sqlstr5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail ";*/
	$sql=$sqlstr1;
 	$inrstoday1=$this -> dbObj -> Execute("select * FROM  (".$sql.") A  WHERE A.sell_id in (".$sellidtoday.") AND A.item_type=3 AND  A.agencyid =".$_SESSION['currentorgan']); 


	
	while ($inrrstoday1 = $inrstoday1 -> FetchRow()) {
		
//===== =============================		 
		$marketingcard=$this -> dbObj -> GetRow("select * FROM  ".WEB_ADMIN_TABPOX."marketingcard A INNER JOIN ".WEB_ADMIN_TABPOX."marketingcardtype B ON A.marketingcardtype_id =B. marketingcardtype_id   WHERE   A.agencyid =".$_SESSION['currentorgan']." and A.marketingcard_id=".$inrrstoday1['item_id']);	
		
		if($inrrstoday1["beauty_id"]<>'' && $inrrstoday1["beauty_id"]<>0){
		$beauty_id=explode(";",$inrrstoday1["beauty_id"]);
		for ($i=0;$i<count($beauty_id);$i++)	{
			
		
		if($marketingcard['marketingcardtype_id']==1){//项目卡销售累计
		$beautyitemtoday["'".$beauty_id[$i]."'"]=$beautyitemtoday["'".$beauty_id[$i]."'"]+$inrrstoday1['value']*$inrrstoday1['number']/count($beauty_id);
			}


		 }

		}
	}
/* print_r($beautyitemtoday);*/
 return $beautyitemtoday;	
 }
function newnumber($sellid){//散客人数
	$sellid=$sellid==''?0:$sellid;
 	$newnumber=$this -> dbObj -> Execute("select  count(*) as   number FROM  ".WEB_ADMIN_TABPOX."sell where  sell_id in(".$sellid.")   GROUP BY customer_id"); 	
	$number=$newnumber ->RecordCount();
	return $number;	
 } 
function newyufunumber($sellidtoday){//预定散客人事
 
	$sellidtoday=$sellidtoday==''?0:$sellidtoday;
 
 	$newyufu=$this -> dbObj -> Execute("select count(*) as newyufunumber FROM ".WEB_ADMIN_TABPOX."sellotherdetail A LEFT JOIN  ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id   WHERE  A.sell_id in (".$sellidtoday.") AND A.item_type=5 AND A.item_id=0  and (B.membercard_no='' or B.membercard_no is NULL) AND  B.agencyid =".$_SESSION['currentorgan'].' GROUP BY B.customer_id '); 
$newyufunumber=$newyufu -> RecordCount();
 return $newyufunumber;	
 }
function memyufunumber($sellidtoday){//预定会员人数
 
	$sellidtoday=$sellidtoday==''?0:$sellidtoday;
 
 	$memyufu=$this -> dbObj -> Execute("select count(*) as memyufunumber FROM ".WEB_ADMIN_TABPOX."sellotherdetail  A LEFT JOIN  ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id   WHERE  A.sell_id in (".$sellidtoday.") AND A.item_type=5 AND A.item_id IN(0,2)  and  B.membercard_no<>''  AND  B.agencyid =".$_SESSION['currentorgan'].' GROUP BY B.customer_id '); 
 
 $memyufunumber=$memyufu->RecordCount();
 return $memyufunumber;	
 } 
function deal($sellid){//成交数 
	$sellid=$sellid==''?0:$sellid;
 	$inrs1=$this -> dbObj -> Execute("select * FROM ".WEB_ADMIN_TABPOX."sellcarddetail  A LEFT JOIN  ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id   WHERE  A.sell_id in (".$sellid.") AND A.item_type=3   AND A.discount>0 and A.value>0 AND B.agencyid =".$_SESSION['currentorgan'].' GROUP BY B.customer_id'); 
 	$deal=0;	
	while ($inrrs1 = $inrs1 -> FetchRow()) {	 
		$marketingcard=$this -> dbObj -> GetRow("select * FROM  ".WEB_ADMIN_TABPOX."marketingcard A INNER JOIN ".WEB_ADMIN_TABPOX."marketingcardtype B ON A.marketingcardtype_id =B. marketingcardtype_id   WHERE   A.agencyid =".$_SESSION['currentorgan']." and A.marketingcard_id=".$inrrs1['item_id']);		
		 if( $inrrs1['membercard_no']=='' or $inrrs1['membercard_no']){//散客 计算疗程卡 储蓄卡
			if($marketingcard['marketingcardtype_id']==1  or $marketingcard['marketingcardtype_id']==2 or   $marketingcard['marketingcardtype_id']==7 ){//开疗程卡 储蓄卡
				if($inrrs1['value']>100){//假设一个临界点 ，开了100元以上的算成交
				$deal=$deal+1;
				}
			}
		 }else  if( $inrrs1['membercard_no']<>'' ){//散客 计算会籍卡
				if( $marketingcard['marketingcardtype_id']==5 ){//开疗程卡 储蓄卡
				if($inrrs1['value']>100){// 
					$deal=$deal+1;
					}
				}
		}
	}
	

 return $deal;	
 }
function totalbeautymanual($sellid,$cmldiscount){//本月手工
$totalbeautymanual=0;
$this->perObj2=new performancehistory();

$sellid=explode(",",$sellid);
for ($i=0;$i<count($sellid);$i++)	{
$totalbeautymanual=$totalbeautymanual+$this->perObj2->main1($sellid[$i]);
}
/*
	$sellid=$sellid==''?0:$sellid;
	$sqlstr3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  ";
	$sqlstr4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  ";

	$sql=$sqlstr3." union ".$sqlstr4;
 	$inrs=$this -> dbObj -> Execute("select * FROM  (".$sql.") A  WHERE A.sell_id in (".$sellid.") AND A.discount>0 AND A.item_type in(0,2,4) AND  A.agencyid =".$_SESSION['currentorgan']); 
 
	while ($inrrs = $inrs -> FetchRow()) {		 
		$totalbeautymanual=$totalbeautymanual+$inrrs["amount"]*$cmldiscount[$inrrs['sell_id']];
		}
	 */
 
 return $totalbeautymanual;	
 } 
 function totalown($sellid){//本月欠款
 	$totalown=0;
	$sellid=$sellid==''?0:$sellid;
	 
 	$inrs=$this -> dbObj -> Execute("select payable1,realpay FROM   ".WEB_ADMIN_TABPOX."sell WHERE  sell_id in (".$sellid.") AND  agencyid =".$_SESSION['currentorgan']); 
 
	while ($inrrs = $inrs -> FetchRow()) {		 
		$totalown=$totalown+$inrrs["payable1"]-$inrrs["realpay"];
		}
	 
 
 return $totalown;	
 } 
 function ownsellid($sellid){//读出欠款sellid
 	$ownsellid='';
	$sellid=$sellid==''?0:$sellid;
	 
 	$inrs=$this -> dbObj -> Execute("select sell_id FROM   ".WEB_ADMIN_TABPOX."sell WHERE  sell_id in (".$sellid.") AND  agencyid =".$_SESSION['currentorgan']); 
 
	while ($inrrs = $inrs -> FetchRow()) {		 
		$ownsellid=$ownsellid==''?$inrrs["sell_id"]:$ownsellid.",".$inrrs["sell_id"];
		}
	 
 
 return $ownsellid;	
 }  
function yejisellid($sellid){//算出sellid平均业绩
 	$yejisellid='';
	$sellid=$sellid==''?0:$sellid;
	$sqlstr1="select * from  ".WEB_ADMIN_TABPOX."selldetail";
	$sqlstr2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail ";
	$sqlstr3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  ";
	$sqlstr4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  ";
	$sqlstr5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail ";
	$sql=$sqlstr1." union ".$sqlstr2." union ".$sqlstr3." union ".$sqlstr4." union ".$sqlstr5;
 	$inrs=$this -> dbObj -> Execute("select * FROM  (".$sql.") A  WHERE A.sell_id in (".$sellid.") AND  A.agencyid =".$_SESSION['currentorgan']); 	$i=$inrs-> RecordCount();
	while ($inrrs = $inrs -> FetchRow()) {		 
			$totalown= $inrrs["payable1"]-$inrrs["realpay"];
		}
	$yejisellid=$totalown/$i;
 
 return $yejisellid;	
 }
}
$main = new Pagecustomer();
$main -> Main();
?>
  