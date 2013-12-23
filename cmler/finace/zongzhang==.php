<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
    function Main()
    {
        if(isset($_GET['action']) && $_GET['action']=='printbill')
        {
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> printbill();
        }else if(isset($_GET['action']) && $_GET['action']=='zongzhang')
        {
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> zongzhang();
        }else if(isset($_GET['action']) && $_GET['action']=='exportexcel')
        {
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> exportexcel();
        }
        else
        {
            parent::Main();
        }
    }
    function printbill(){

		//定义模板
		
		$t = new Template('../template/finace');
		$t -> set_file('f','zongzhang_bill.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
	 
	 	
//搜索 
        $accounttitle_id=$_GET["accounttitle_id"];
		$objecttype_id=$_GET["objecttype_id"];
		$objectid=$_GET["objectid"];
		$bgdate=$_GET["bgdate"];
		$enddate=$_GET["enddate"];

		$monthlybatch=$this->dbObj -> GetRow("SELECT * FROM `s_monthlybatch` WHERE `enddate`<'".$bgdate."' and agencyid =".$_SESSION['currentorgan']." order by monthlybatch_id desc");
		
		$dategapbg=$monthlybatch['enddate'];
		$monthlybatch_id=$monthlybatch['monthlybatch_id'];
		 
		$dategapbg=date("Y-m-d",strtotime("$m+1 days",strtotime($dategapbg)));//设置开始时间为下一天
		$dategapend=date("Y-m-d",strtotime("$m-1 days",strtotime($bgdate)));//设置结束时间为上一天
		
		$ftable=$_GET["ftable"];
		$accounttitleid=explode(";",$accounttitle_id);
		$t -> set_var('ml');
		for($i=0;$i<count($accounttitleid);$i++){
		$accounttitle_id=$accounttitleid[$i];	
		$condition='';
		if($accounttitle_id<>''){
		$accounttitle_no=$this->dbObj -> GetOne('SELECT accounttitle_no FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$accounttitle_id);
		
		if($accounttitle_no<>''){
		$condition=$condition. '  B.accounttitle_no like  "'.$accounttitle_no.'%"';
		}
		}
		if($objecttype_id<>''){
		$condition=$condition==''?' A.objecttype_id= "'.$objecttype_id.'"':$condition.' and  A.objecttype_id= "'.$objecttype_id.'"';
		}
		if($objectid<>''){
		$condition=$condition==''?' A.objectid= "'.$objectid.'"':$condition.' and  A.objectid= "'.$objectid.'"';
		}
		$condition1=$condition;
		if($bgdate<>''&&$enddate<>''){
		$condition=$condition==''?' C.date  between  "'.$bgdate.'" and "'.$enddate.'"':$condition.' and  C.date between  "'.$bgdate.'" and "'.$enddate.'"';
		}
//分页		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:20;
			$offset = $pageid>0?($pageid-1)*$psize:0;

			//设置商品
			 
			
			
			if($condition<>''&&$ftable==''){
				
			$sql='select A.* from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id  INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher C ON A.transfervoucher_id=C.transfervoucher_id  where  A.agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;
 			$sql1='select sum(A.lend) as totallend , sum(A.loan) as totalloan  from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id  INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher C ON A.transfervoucher_id=C.transfervoucher_id  where  A.agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;
			$sqlbg='select sum(A.lend) as bglend ,sum(A.loan) as bgloan from '.WEB_ADMIN_TABPOX.'transfervoucherhistory A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id   where  A.agencyid ='.$_SESSION["currentorgan"].' and '.$condition1.' and monthlybatch_id='.$monthlybatch_id  ;
		 	$sqlgap='select sum(A.lend) as lend,sum(A.loan) as loan from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id  INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher C ON A.transfervoucher_id=C.transfervoucher_id  where  A.agencyid ='.$_SESSION["currentorgan"].' and '.$condition1.'  and  C.date  between  "'.$dategapbg.'" and "'.$dategapend.'"' ;
			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'transfervoucherdetail r INNER JOIN '.WEB_ADMIN_TABPOX.$ftable." f on r.".$ftable."_id  =f.".$ftable."_id   where f.".$category."  like '%".$keywords."%' and  r.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id   where  A.agencyid ='.$_SESSION["currentorgan"];
			 $sql1='select sum(A.lend) as totallend , sum(A.loan) as totalloan from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id   where  A.agencyid ='.$_SESSION["currentorgan"];
			}
			
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY transfervoucherdetail_id DESC  ");
			
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist','');		
				
			
			
			$accounttitle_no=$this->dbObj -> GetOne('SELECT accounttitle_no FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$accounttitle_id);
			$nostr=mb_substr($accounttitle_no,0,1,'utf-8');
			 
/*			if(strpos('0,1,4',$nostr)){
			$fuhao=1;
			}else{
			$fuhao=-1;
			}*/
			
			
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'transfervoucherdetail  where agencyid ='.$_SESSION["currentorgan"]);
			//echo 'select * from '.WEB_ADMIN_TABPOX.'account  where agencyid ='.$_SESSION["currentorgan"];
			$bgdata=$this -> dbObj -> GetRow($sqlbg);

			$bgdata['bglend']=$bgdata['bglend']==''?0:$bgdata['bglend'];
			$bgdata['bgloan']=$bgdata['bgloan']==''?0:$bgdata['bgloan'];

			
			$gapdata=$this -> dbObj -> GetRow($sqlgap); 
			$gapdata['lend']=$gapdata['lend']==''?0:$gapdata['lend'];
			$gapdata['loan']=$gapdata['loan']==''?0:$gapdata['loan'];	
		
			$t -> set_var('bglend',$bgdata['bglend']+$gapdata['lend']);
			$t -> set_var('bgloan',$bgdata['bgloan']+$gapdata['loan']); 
			$bgbalance=($bgdata['bglend']+$gapdata['lend']-$bgdata['bgloan']-$gapdata['loan']);
			$bgbalance<0?$t -> set_var('bglendloan','贷'):$t -> set_var('bglendloan','借');	
			$t -> set_var('bgbalance',abs($bgbalance));
			
			$lend=0;
			$loan=0;
			$balance=$bgbalance;
	     	while ($inrrs = &$inrs -> FetchRow()) {
				//$t -> set_var($inrrs);
				 $lend=$lend+$inrrs['lend'];
				 $loan=$loan+$inrrs['loan'];
				 $balance=$balance+($inrrs['lend']-$inrrs['loan']);
				
				 $objecttype=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id ='.$inrrs["objecttype_id"]);
				// $t -> set_var('objecttype_name',$objecttype['objecttype_name']);
				$object=$this -> dbObj -> getrow('select *  from '.WEB_ADMIN_TABPOX.$objecttype["objecttypetable"].'  where '.$objecttype["objecttypetable"].'_id ='.$inrrs["objectid"]);
				 
				//$t -> set_var('object_name',$object[$objecttype["objecttypetable"].'_name']);
				
				//$t -> set_var('accounttitle_name',$this->dbObj -> GetOne('SELECT accounttitle_name FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$inrrs['accounttitle_id']));
				
				//$t -> set_var('accounttitle_no',$this->dbObj -> GetOne('SELECT accounttitle_no FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$inrrs['accounttitle_id']));
				
			   	//$t -> set_var('delete',$this -> getDelStr('',$inrrs['transfervoucherdetail_id']));
		        //$t -> set_var('edit',$this -> getupdStr('',$inrrs['transfervoucherdetail_id']));			
			 
			}
			$inrs -> Close();	
		$balance<0?$t -> set_var('lendloan','贷'):$t -> set_var('lendloan','借');	
		$t -> set_var('balance',abs($balance));	
		$objecttype_id=$_GET["objecttype_id"];
		$objectid=$_GET["objectid"];	
		$objecttype=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id ='.$objecttype_id);
		$object=$this -> dbObj -> getrow('select *  from '.WEB_ADMIN_TABPOX.$objecttype["objecttypetable"].'  where '.$objecttype["objecttypetable"].'_id ='.$objectid);
		$this->dbObj -> GetOne('SELECT object_name FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$objectid);
		$t -> set_var('object_name',$object[$objecttype["objecttypetable"].'_name']);
		$t -> set_var('objecttype_name',$objecttype["objecttype_name"]);
		
		$t -> set_var('accounttitle_no',$this->dbObj -> GetOne('SELECT accounttitle_no FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '. $accounttitle_id));
		$t -> set_var('accounttitle_name',$this->dbObj -> GetOne('SELECT accounttitle_name FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$accounttitle_id));		
		$t -> set_var('lend',$lend);
		$t -> set_var('loan',$loan);
		

		$totaldata = &$this -> dbObj -> GetRow($sql1);	
		$t -> set_var('totallend',$totaldata['totallend']+$bgdata['bglend']+$gapdata['lend']);
		$t -> set_var('totalloan',$totaldata['totalloan']+$bgdata['bgloan']+$gapdata['loan']);
		$totalbalance=$balance;
		$t -> set_var('totalbalance',abs($totalbalance));	
		$totalbalance<0?$t -> set_var('totallendloan','贷'):$t -> set_var('totallendloan','借');	
		$t -> set_var('accounttitlelist',$this->selectlist('accounttitle','accounttitle_id','accounttitle_name', $accounttitle_id));	
		$t -> set_var('objecttypelist',$this->selectlist('objecttype','objecttype_id','objecttype_name',$objecttype_id));
		$t -> set_var('cobject_name','');
		$t -> set_var('cobjectid','');
		
		$t -> parse('ml','mainlist',true);
		}
		
		if ($bgdate){
		$t -> set_var('bgdate',$bgdate);
		$t -> set_var('enddate',$enddate);	
		}else{
		$bgdate=$this -> dbObj -> getone('select enddate  from '.WEB_ADMIN_TABPOX.'annualbatch  where agencyid ='.$_SESSION["currentorgan"].' order by annualbatch_id desc');	
		if (!$bgdate){//如果没有月结过 查找最早的单时间。
			$bgdate=$this -> dbObj -> getone('select creattime  from '.WEB_ADMIN_TABPOX.'sell  where agencyid ='.$_SESSION["currentorgan"].' order by sell_id asc');	 
			$bgdate=date('Y-m-d',strtotime($bgdate)); 
		}else{ 
			$bgdate=date("Y-m-d",strtotime("$m+1 days",strtotime($bgdate)));//设置开始时间为上次月结的下一天
		}
		$t -> set_var('bgdate',$bgdate);
		$t -> set_var('enddate',date('Y-m-d'));	
		}
		$t -> set_var('accounttitle_name',$_GET['accounttitle_name']);
		$t -> set_var('accounttitle_id',$_GET['accounttitle_id']);
		
		$t -> set_var('datetime',date('Y-m-d h:s:i',time()));	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');		
		$t -> set_var('add',$this -> getAddStr('img'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
 


    }
    function exportexcel(){
	//定义模板
$filename="销售单".date('Y-m-d').".xsl";//先定义一个excel文件
header("Content-Type: application/vnd.ms-execl"); 
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename"); 
header("Pragma: no-cache"); 
header("Expires: 0");
echo mb_convert_encoding("科目代码\t科目名称\t借方金额","GBK","UTF-8")."\t";
echo (mb_convert_encoding("科目名称","GBK","UTF-8"))."\t";
echo (mb_convert_encoding("借方金额","GBK","UTF-8"))."\t";
echo (mb_convert_encoding("借/贷","GBK","UTF-8"))."\t";
echo (mb_convert_encoding("余额","GBK","UTF-8"))."\t";
echo(mb_convert_encoding("备注","GBK","UTF-8"))."\n";
//搜索 
        $accounttitle_id=$_GET["accounttitle_id"];
		$objecttype_id=$_GET["objecttype_id"];
		$objectid=$_GET["objectid"];
		$bgdate=$_GET["bgdate"];
		$enddate=$_GET["enddate"];

		$monthlybatch=$this->dbObj -> GetRow("SELECT * FROM `s_monthlybatch` WHERE `enddate`<'".$bgdate."' and agencyid =".$_SESSION['currentorgan']." order by monthlybatch_id desc");
		
		$dategapbg=$monthlybatch['enddate'];
		$monthlybatch_id=$monthlybatch['monthlybatch_id'];
		 
		$dategapbg=date("Y-m-d",strtotime("$m+1 days",strtotime($dategapbg)));//设置开始时间为下一天
		$dategapend=date("Y-m-d",strtotime("$m-1 days",strtotime($bgdate)));//设置结束时间为上一天
		
		$ftable=$_GET["ftable"];
		$accounttitleid=explode(";",$accounttitle_id);
		 
		for($i=0;$i<count($accounttitleid);$i++){
		$accounttitle_id=$accounttitleid[$i];	
		$condition='';
		if($accounttitle_id<>''){
		$accounttitle_no=$this->dbObj -> GetOne('SELECT accounttitle_no FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$accounttitle_id);
		
		if($accounttitle_no<>''){
		$condition=$condition. '  B.accounttitle_no like  "'.$accounttitle_no.'%"';
		}
		}
		if($objecttype_id<>''){
		$condition=$condition==''?' A.objecttype_id= "'.$objecttype_id.'"':$condition.' and  A.objecttype_id= "'.$objecttype_id.'"';
		}
		if($objectid<>''){
		$condition=$condition==''?' A.objectid= "'.$objectid.'"':$condition.' and  A.objectid= "'.$objectid.'"';
		}
		$condition1=$condition;
		if($bgdate<>''&&$enddate<>''){
		$condition=$condition==''?' C.date  between  "'.$bgdate.'" and "'.$enddate.'"':$condition.' and  C.date between  "'.$bgdate.'" and "'.$enddate.'"';
		}
//分页		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:20;
			$offset = $pageid>0?($pageid-1)*$psize:0;

			//设置商品
			 
			
			
			if($condition<>''&&$ftable==''){
				
			$sql='select A.* from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id  INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher C ON A.transfervoucher_id=C.transfervoucher_id  where  A.agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;
 			$sql1='select sum(A.lend) as totallend , sum(A.loan) as totalloan  from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id  INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher C ON A.transfervoucher_id=C.transfervoucher_id  where  A.agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;
			$sqlbg='select sum(A.lend) as bglend ,sum(A.loan) as bgloan from '.WEB_ADMIN_TABPOX.'transfervoucherhistory A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id   where  A.agencyid ='.$_SESSION["currentorgan"].' and '.$condition1.' and monthlybatch_id='.$monthlybatch_id  ;
		 	$sqlgap='select sum(A.lend) as lend,sum(A.loan) as loan from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id  INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher C ON A.transfervoucher_id=C.transfervoucher_id  where  A.agencyid ='.$_SESSION["currentorgan"].' and '.$condition1.'  and  C.date  between  "'.$dategapbg.'" and "'.$dategapend.'"' ;
			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'transfervoucherdetail r INNER JOIN '.WEB_ADMIN_TABPOX.$ftable." f on r.".$ftable."_id  =f.".$ftable."_id   where f.".$category."  like '%".$keywords."%' and  r.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id   where  A.agencyid ='.$_SESSION["currentorgan"];
			 $sql1='select sum(A.lend) as totallend , sum(A.loan) as totalloan from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id   where  A.agencyid ='.$_SESSION["currentorgan"];
			}
			
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY transfervoucherdetail_id DESC  ");
			
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			 	
				
			
			
			$accounttitle_no=$this->dbObj -> GetOne('SELECT accounttitle_no FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$accounttitle_id);
			$nostr=mb_substr($accounttitle_no,0,1,'utf-8');
			 
/*			if(strpos('0,1,4',$nostr)){
			$fuhao=1;
			}else{
			$fuhao=-1;
			}*/
			
			
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'transfervoucherdetail  where agencyid ='.$_SESSION["currentorgan"]);
			//echo 'select * from '.WEB_ADMIN_TABPOX.'account  where agencyid ='.$_SESSION["currentorgan"];
			 
			$bgdata=$this -> dbObj -> GetRow($sqlbg);

			$bgdata['bglend']=$bgdata['bglend']==''?0:$bgdata['bglend'];
			$bgdata['bgloan']=$bgdata['bgloan']==''?0:$bgdata['bgloan'];

			
			$gapdata=$this -> dbObj -> GetRow($sqlgap); 
			$gapdata['lend']=$gapdata['lend']==''?0:$gapdata['lend'];
			$gapdata['loan']=$gapdata['loan']==''?0:$gapdata['loan'];	
		
			//$t -> set_var('bglend',$bgdata['bglend']+$gapdata['lend']);
			 
			echo ($bgdata['bglend']+$gapdata['lend'])."\t";
			//$t -> set_var('bgloan',$bgdata['bgloan']+$gapdata['loan']); 
			echo ($bgdata['bgloan']+$gapdata['loan'])."\t";
			$bgbalance=($bgdata['bglend']+$gapdata['lend']-$bgdata['bgloan']-$gapdata['loan']);
			//$bgbalance<0?$t -> set_var('bglendloan','贷'):$t -> set_var('bglendloan','借');	
			echo (mb_convert_encoding("贷","GBK","UTF-8"))."\t";
			
			//$t -> set_var('bgbalance',abs($bgbalance));
			echo abs($bgbalance)."\n";
			
			$lend=0;
			$loan=0;
			$balance=$bgbalance;
			
	     	while ($inrrs = &$inrs -> FetchRow()) {
				//$t -> set_var($inrrs);
				 $lend=$lend+$inrrs['lend'];
				 $loan=$loan+$inrrs['loan'];
				 $balance=$balance+($inrrs['lend']-$inrrs['loan']);
				
				 $objecttype=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id ='.$inrrs["objecttype_id"]);
				// $t -> set_var('objecttype_name',$objecttype['objecttype_name']);
				$object=$this -> dbObj -> getrow('select *  from '.WEB_ADMIN_TABPOX.$objecttype["objecttypetable"].'  where '.$objecttype["objecttypetable"].'_id ='.$inrrs["objectid"]);
				 
				//$t -> set_var('object_name',$object[$objecttype["objecttypetable"].'_name']);
				
				//$t -> set_var('accounttitle_name',$this->dbObj -> GetOne('SELECT accounttitle_name FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$inrrs['accounttitle_id']));
				
				//$t -> set_var('accounttitle_no',$this->dbObj -> GetOne('SELECT accounttitle_no FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$inrrs['accounttitle_id']));
				
			   	//$t -> set_var('delete',$this -> getDelStr('',$inrrs['transfervoucherdetail_id']));
		        //$t -> set_var('edit',$this -> getupdStr('',$inrrs['transfervoucherdetail_id']));			
			 
			}
			$inrs -> Close();	
		//$balance<0?$t -> set_var('lendloan','贷'):$t -> set_var('lendloan','借');	
		echo (mb_convert_encoding("贷","GBK","UTF-8"))."\t";
		echo abs($balance)."\t";
		$objecttype_id=$_GET["objecttype_id"];
		$objectid=$_GET["objectid"];	
		$objecttype=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id ='.$objecttype_id);
		$object=$this -> dbObj -> getrow('select *  from '.WEB_ADMIN_TABPOX.$objecttype["objecttypetable"].'  where '.$objecttype["objecttypetable"].'_id ='.$objectid);
		$this->dbObj -> GetOne('SELECT object_name FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$objectid);
		//$t -> set_var('object_name',$object[$objecttype["objecttypetable"].'_name']);
		//$t -> set_var('objecttype_name',$objecttype["objecttype_name"]);
		$accounttitle_no=$this->dbObj -> GetOne('SELECT accounttitle_no FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '. $accounttitle_id);
		//$t -> set_var('accounttitle_no',$accounttitle_no);
		echo (mb_convert_encoding($accounttitle_no,"GBK","UTF-8"))."\t";
		
		$accounttitle_name=$this->dbObj -> GetOne('SELECT accounttitle_name FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$accounttitle_id);
		//$t -> set_var('accounttitle_name',$this->dbObj -> GetOne('SELECT accounttitle_name FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$accounttitle_id));	
		echo (mb_convert_encoding($accounttitle_name,"GBK","utf-8"))."\t";
		
		//$t -> set_var('lend',$lend);
		echo $lend."\t";
		//$t -> set_var('loan',$loan);
		echo $loan."\n";

		$totaldata = &$this -> dbObj -> GetRow($sql1);	
		//$t -> set_var('totallend',$totaldata['totallend']+$bgdata['bglend']+$gapdata['lend']);
		echo ($totaldata['totallend']+$bgdata['bglend']+$gapdata['lend'])."\t";
		//$t -> set_var('totalloan',$totaldata['totalloan']+$bgdata['bgloan']+$gapdata['loan']);
		echo ($totaldata['totalloan']+$bgdata['bgloan']+$gapdata['loan'])."\t";
		$totalbalance=$balance;
		$t -> set_var('totalbalance',abs($totalbalance));	
		echo abs($totalbalance)."\t";
		$totalbalance<0?$t -> set_var('totallendloan','贷'):$t -> set_var('totallendloan','借');	
		echo (mb_convert_encoding("贷","GBK","UTF-8"))."\t";
		//$t -> set_var('accounttitlelist',$this->selectlist('accounttitle','accounttitle_id','accounttitle_name', $accounttitle_id));	
		//$t -> set_var('objecttypelist',$this->selectlist('objecttype','objecttype_id','objecttype_name',$objecttype_id));
		//$t -> set_var('cobject_name','');
		//$t -> set_var('cobjectid','');
		
		//$t -> parse('ml','mainlist',true);
		}
		
		if ($bgdate){
		//$t -> set_var('bgdate',$bgdate);
		//$t -> set_var('enddate',$enddate);	
		}else{
		$bgdate=$this -> dbObj -> getone('select enddate  from '.WEB_ADMIN_TABPOX.'annualbatch  where agencyid ='.$_SESSION["currentorgan"].' order by annualbatch_id desc');	
		if (!$bgdate){//如果没有月结过 查找最早的单时间。
			$bgdate=$this -> dbObj -> getone('select creattime  from '.WEB_ADMIN_TABPOX.'sell  where agencyid ='.$_SESSION["currentorgan"].' order by sell_id asc');	 
			$bgdate=date('Y-m-d',strtotime($bgdate)); 
		}else{ 
			$bgdate=date("Y-m-d",strtotime("$m+1 days",strtotime($bgdate)));//设置开始时间为上次月结的下一天
		}
		//$t -> set_var('bgdate',$bgdate);
		//$t -> set_var('enddate',date('Y-m-d'));	
		}
		//$t -> set_var('accounttitle_name',$_GET['accounttitle_name']);
		//$t -> set_var('accounttitle_id',$_GET['accounttitle_id']);
		
		//$t -> set_var('datetime',date('Y-m-d h:s:i',time()));	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');		
		//$t -> set_var('add',$this -> getAddStr('img'));
		//$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		//$t -> parse('out','f');
	//	$t -> p('out');
 


    }	
	function disp(){
		//定义模板
		$t = new Template('../template/finace');
		$t -> set_file('f','zongzhangselectdate.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]
			
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
	function zongzhang(){
		//定义模板
		$t = new Template('../template/finace');
		$t -> set_file('f','zongzhang.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');	
	 
	 	
//搜索 
        $accounttitle_id=$_GET["accounttitle_id"];
		$objecttype_id=$_GET["objecttype_id"];
		$objectid=$_GET["objectid"];
		$bgdate=$_GET["bgdate"];
		$enddate=$_GET["enddate"];

		$monthlybatch=$this->dbObj -> GetRow("SELECT * FROM `s_monthlybatch` WHERE `enddate`<'".$bgdate."' and agencyid =".$_SESSION['currentorgan']." order by monthlybatch_id desc");
		
		$dategapbg=$monthlybatch['enddate'];
		$monthlybatch_id=$monthlybatch['monthlybatch_id'];
		 
		$dategapbg=date("Y-m-d",strtotime("$m+1 days",strtotime($dategapbg)));//设置开始时间为下一天
		$dategapend=date("Y-m-d",strtotime("$m-1 days",strtotime($bgdate)));//设置结束时间为上一天
		
		$ftable=$_GET["ftable"];
		$accounttitleid=explode(";",$accounttitle_id);
		$t -> set_var('ml');
		for($i=0;$i<count($accounttitleid);$i++){
		$accounttitle_id=$accounttitleid[$i];	
		$condition='';
		if($accounttitle_id<>''){
		$accounttitle_no=$this->dbObj -> GetOne('SELECT accounttitle_no FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$accounttitle_id);
		
		if($accounttitle_no<>''){
		$condition=$condition. '  B.accounttitle_no like  "'.$accounttitle_no.'%"';
		}
		}
		if($objecttype_id<>''){
		$condition=$condition==''?' A.objecttype_id= "'.$objecttype_id.'"':$condition.' and  A.objecttype_id= "'.$objecttype_id.'"';
		}
		if($objectid<>''){
		$condition=$condition==''?' A.objectid= "'.$objectid.'"':$condition.' and  A.objectid= "'.$objectid.'"';
		}
		$condition1=$condition;
		if($bgdate<>''&&$enddate<>''){
		$condition=$condition==''?' C.date  between  "'.$bgdate.'" and "'.$enddate.'"':$condition.' and  C.date between  "'.$bgdate.'" and "'.$enddate.'"';
		}
//分页		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:20;
			$offset = $pageid>0?($pageid-1)*$psize:0;

			//设置商品
			 
			
			
			if($condition<>''&&$ftable==''){
				
			$sql='select A.* from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id  INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher C ON A.transfervoucher_id=C.transfervoucher_id  where  A.agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;
 			$sql1='select sum(A.lend) as totallend , sum(A.loan) as totalloan  from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id  INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher C ON A.transfervoucher_id=C.transfervoucher_id  where  A.agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;
			$sqlbg='select sum(A.lend) as bglend ,sum(A.loan) as bgloan from '.WEB_ADMIN_TABPOX.'transfervoucherhistory A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id   where  A.agencyid ='.$_SESSION["currentorgan"].' and '.$condition1.' and monthlybatch_id='.$monthlybatch_id  ;
		 	$sqlgap='select sum(A.lend) as lend,sum(A.loan) as loan from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id  INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher C ON A.transfervoucher_id=C.transfervoucher_id  where  A.agencyid ='.$_SESSION["currentorgan"].' and '.$condition1.'  and  C.date  between  "'.$dategapbg.'" and "'.$dategapend.'"' ;
			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'transfervoucherdetail r INNER JOIN '.WEB_ADMIN_TABPOX.$ftable." f on r.".$ftable."_id  =f.".$ftable."_id   where f.".$category."  like '%".$keywords."%' and  r.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id   where  A.agencyid ='.$_SESSION["currentorgan"];
			 $sql1='select sum(A.lend) as totallend , sum(A.loan) as totalloan from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id   where  A.agencyid ='.$_SESSION["currentorgan"];
			}
			
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY transfervoucherdetail_id DESC  ");
			
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist','');		
				
			
			
			$accounttitle_no=$this->dbObj -> GetOne('SELECT accounttitle_no FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$accounttitle_id);
			$nostr=mb_substr($accounttitle_no,0,1,'utf-8');
			 
/*			if(strpos('0,1,4',$nostr)){
			$fuhao=1;
			}else{
			$fuhao=-1;
			}*/
			
			
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'transfervoucherdetail  where agencyid ='.$_SESSION["currentorgan"]);
			//echo 'select * from '.WEB_ADMIN_TABPOX.'account  where agencyid ='.$_SESSION["currentorgan"];
			$bgdata=$this -> dbObj -> GetRow($sqlbg);

			$bgdata['bglend']=$bgdata['bglend']==''?0:$bgdata['bglend'];
			$bgdata['bgloan']=$bgdata['bgloan']==''?0:$bgdata['bgloan'];

			
			$gapdata=$this -> dbObj -> GetRow($sqlgap); 
			$gapdata['lend']=$gapdata['lend']==''?0:$gapdata['lend'];
			$gapdata['loan']=$gapdata['loan']==''?0:$gapdata['loan'];	
		
			$t -> set_var('bglend',$bgdata['bglend']+$gapdata['lend']);
			$t -> set_var('bgloan',$bgdata['bgloan']+$gapdata['loan']); 
			$bgbalance=($bgdata['bglend']+$gapdata['lend']-$bgdata['bgloan']-$gapdata['loan']);
			$bgbalance<0?$t -> set_var('bglendloan','贷'):$t -> set_var('bglendloan','借');	
			$t -> set_var('bgbalance',abs($bgbalance));
			
			$lend=0;
			$loan=0;
			$balance=$bgbalance;
	     	while ($inrrs = &$inrs -> FetchRow()) {
				//$t -> set_var($inrrs);
				 $lend=$lend+$inrrs['lend'];
				 $loan=$loan+$inrrs['loan'];
				 $balance=$balance+($inrrs['lend']-$inrrs['loan']);
				
				 $objecttype=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id ='.$inrrs["objecttype_id"]);
				// $t -> set_var('objecttype_name',$objecttype['objecttype_name']);
				$object=$this -> dbObj -> getrow('select *  from '.WEB_ADMIN_TABPOX.$objecttype["objecttypetable"].'  where '.$objecttype["objecttypetable"].'_id ='.$inrrs["objectid"]);
				 
				//$t -> set_var('object_name',$object[$objecttype["objecttypetable"].'_name']);
				
				//$t -> set_var('accounttitle_name',$this->dbObj -> GetOne('SELECT accounttitle_name FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$inrrs['accounttitle_id']));
				
				//$t -> set_var('accounttitle_no',$this->dbObj -> GetOne('SELECT accounttitle_no FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$inrrs['accounttitle_id']));
				
			   	//$t -> set_var('delete',$this -> getDelStr('',$inrrs['transfervoucherdetail_id']));
		        //$t -> set_var('edit',$this -> getupdStr('',$inrrs['transfervoucherdetail_id']));			
			 
			}
			$inrs -> Close();	
		$balance<0?$t -> set_var('lendloan','贷'):$t -> set_var('lendloan','借');	
		$t -> set_var('balance',abs($balance));	
		$objecttype_id=$_GET["objecttype_id"];
		$objectid=$_GET["objectid"];	
		$objecttype=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id ='.$objecttype_id);
		$object=$this -> dbObj -> getrow('select *  from '.WEB_ADMIN_TABPOX.$objecttype["objecttypetable"].'  where '.$objecttype["objecttypetable"].'_id ='.$objectid);
		$this->dbObj -> GetOne('SELECT object_name FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$objectid);
		$t -> set_var('object_name',$object[$objecttype["objecttypetable"].'_name']);
		$t -> set_var('objecttype_name',$objecttype["objecttype_name"]);
		
		$t -> set_var('accounttitle_no',$this->dbObj -> GetOne('SELECT accounttitle_no FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '. $accounttitle_id));
		$t -> set_var('accounttitle_name',$this->dbObj -> GetOne('SELECT accounttitle_name FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$accounttitle_id));		
		$t -> set_var('lend',$lend);
		$t -> set_var('loan',$loan);
		

		$totaldata = &$this -> dbObj -> GetRow($sql1);	
		$t -> set_var('totallend',$totaldata['totallend']+$bgdata['bglend']+$gapdata['lend']);
		$t -> set_var('totalloan',$totaldata['totalloan']+$bgdata['bgloan']+$gapdata['loan']);
		$totalbalance=$balance;
		$t -> set_var('totalbalance',abs($totalbalance));	
		$totalbalance<0?$t -> set_var('totallendloan','贷'):$t -> set_var('totallendloan','借');	
		$t -> set_var('accounttitlelist',$this->selectlist('accounttitle','accounttitle_id','accounttitle_name', $accounttitle_id));	
		$t -> set_var('objecttypelist',$this->selectlist('objecttype','objecttype_id','objecttype_name',$objecttype_id));
		$t -> set_var('cobject_name','');
		$t -> set_var('cobjectid','');
		
		$t -> parse('ml','mainlist',true);
		}
		
		if ($bgdate){
		$t -> set_var('bgdate',$bgdate);
		$t -> set_var('enddate',$enddate);	
		}else{
		$bgdate=$this -> dbObj -> getone('select enddate  from '.WEB_ADMIN_TABPOX.'annualbatch  where agencyid ='.$_SESSION["currentorgan"].' order by annualbatch_id desc');	
		if (!$bgdate){//如果没有月结过 查找最早的单时间。
			$bgdate=$this -> dbObj -> getone('select creattime  from '.WEB_ADMIN_TABPOX.'sell  where agencyid ='.$_SESSION["currentorgan"].' order by sell_id asc');	 
			$bgdate=date('Y-m-d',strtotime($bgdate)); 
		}else{ 
			$bgdate=date("Y-m-d",strtotime("$m+1 days",strtotime($bgdate)));//设置开始时间为上次月结的下一天
		}
		$t -> set_var('bgdate',$bgdate);
		$t -> set_var('enddate',date('Y-m-d'));	
		}
		$t -> set_var('accounttitle_name',$_GET['accounttitle_name']);
		$t -> set_var('accounttitle_id',$_GET['accounttitle_id']);
		
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
  