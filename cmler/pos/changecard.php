<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/sell.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/card.cls.php');
class Pageservices extends admin {
    function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='step1'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> step1();			
		}else if(isset($_GET['action']) && $_GET['action']=='step2'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> step2();			
		}else if(isset($_GET['action']) && $_GET['action']=='step3'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> step3();			
		}else{
            parent::Main();
        }
    }	
	function disp1(){
		//定义模板
  		$customer_id=$_SESSION["currentcustomerid"];
		if($customer_id==0 or $customer_id==''){
		exit("<script>alert('请刷会员卡');window.close();</script>");
		} 
		$t = new Template('../template/pos');
		$t -> set_file('f','buycard.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
   
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "%'.$keywords.'%"';}else{$condition=$category.' like " %'.$keywords.'%"';}
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'services  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'services p INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on p.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  p.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'services  where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
		
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  services_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
           	$t -> set_var('recordcount',$count);
						
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('gender',$inrrs["genderid"]==1?'男':'女');
				$t -> set_var('brand_name',$this -> dbObj -> getone('select brand_name from '.WEB_ADMIN_TABPOX.'brand  where brand_id ='.$inrrs["brandid"]));
				$t -> set_var('standardunit',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$inrrs["standardunit"]));
				$t -> set_var('category_name',$this -> dbObj -> getone('select category_name from '.WEB_ADMIN_TABPOX.'procatalog where category_id ='.$inrrs["categoryid"]));
				
	
				
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function step1(){
		//定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','buycard1.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
	    $t -> set_block('f','mainlist','ml');	
		$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");	
        $marketingcardtype_id=$_GET['cardtypeid'];
		
		//$cardtable_name[$marketingcardtype_id];
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "%'.$keywords.'%"';}else{$condition=$category.' like " %'.$keywords.'%"';}
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'marketingcard  where  marketingcardtype_id ='.$marketingcardtype_id.' and  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'marketingcard p INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on p.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  p.marketingcardtype_id  ='.$marketingcardtype_id.' and   p.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'marketingcard  where  marketingcardtype_id ='.$marketingcardtype_id.' and  agencyid ='.$_SESSION["currentorgan"];
			 
			}
		
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  marketingcard_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
           	$t -> set_var('recordcount',$count);
						
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('gender',$inrrs["genderid"]==1?'男':'女');
				$t -> set_var('marketingcardtype_name',$this -> dbObj -> getone('select marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype  where marketingcardtype_id ='.$marketingcardtype_id));
				$t -> set_var('standardunit',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$inrrs["standardunit"]));
				$t -> set_var('category_name',$this -> dbObj -> getone('select category_name from '.WEB_ADMIN_TABPOX.'procatalog where category_id ='.$inrrs["categoryid"]));
				
	
				
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$t -> set_var('cardtypeid',$marketingcardtype_id);	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function disp(){
		//定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','changecard.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
		$carddata=$_GET['carddata'];
		$agencyid=$_SESSION["currentorgan"];
		$carddata = explode('@@@',$carddata);
		$marketingcard_id=$carddata[0];
		$marketingcard_name=$carddata[1];
		$marketingcardtype_name=$carddata[2];
		$timelimit=$carddata[3];
		$price=$carddata[4];
		if($price==0){
		$t -> set_var('givingchecked',"checked");
		}else{
		$t -> set_var('givingchecked',"");
		}
		$customer_id=$_SESSION["currentcustomerid"]?$_SESSION["currentcustomerid"]:0;
		
		$customer_name=$this->dbObj->GetOne('SELECT customer_name  FROM '.WEB_ADMIN_TABPOX.'customer WHERE  customer_id='.$customer_id);
		 
		$t-> set_var('itemmemo','');
		$t-> set_var('customer_name',$customer_name);
		$t-> set_var('customer_id',$customer_id);
		$t -> set_var('oldcardlist',$this -> oldcard($customer_id));
		$t -> set_var('marketingcard_id',$marketingcard_id);
		$t -> set_var('marketingcard_name',$marketingcard_name);
		$t -> set_var('marketingcardtype_name',$marketingcardtype_name);
		$t -> set_var('timelimit',$timelimit);
		$t -> set_var('price',$price);
		$t -> set_var('standardunit',$code);
		$t -> set_var('beauty_id','');
		$t -> set_var('beauty_name','');
		$t -> set_var('employee_id','');
		$t -> set_var('employee_name','');
		$t -> set_var('number',1);
		$t -> set_var('value','');
		
		$t -> set_var('action','step3');
		$t -> set_var('cardtypeid',$_GET['cardtypeid']);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function step3(){
		$this->	CardObj=new card();
		$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");
		//$Prefix='XM';
		$Prefixname=array('XM','XM','LC','TY','GS','HJ','XJ','CZ','ZDY');
		
		$agency_no=$_SESSION["agency_no"];
		
		
		$number=5;
			
			$Prefix=$Prefixname[$_POST['cardtypeid']];
			$cardtable=$cardtable_name[$_POST['cardtypeid']];
			$column=$cardtable.'_no';
			$id=$cardtable.'_id';
			$table=WEB_ADMIN_TABPOX.$cardtable;
			$itemcard_no=$this->makeno($Prefix,$agency_no,$table,$column,$number,$id);	
		 	$card_no=$itemcard_no;
			
			
			$marketingcard_id=$_POST['marketingcard_id'];
			
			$customerid=$_SESSION['currentcustomerid']?$_SESSION['currentcustomerid']:'0';
			$employeeid=$_POST['employee_id'];
			$agencyid=$_SESSION["currentorgan"];
			$clearyufu=$_POST['clearyufu'];
			//$this->dbObj->Execute('update  '.WEB_ADMIN_TABPOX.'customer set yufukuan=0 WHERE  customer_id='.$customerid);
			
			$oldcard=explode("@@@",$_POST['marketingcard_id2']);
			$marketingcard_id2=$oldcard[0];
			$customercardid=$oldcard[1];
			$cardtype2=$this->dbObj->GetOne('SELECT marketingcardtype_id  FROM '.WEB_ADMIN_TABPOX.'marketingcard WHERE  marketingcard_id='.$marketingcard_id2.' and agencyid ='.$agencyid);
			//找到旧卡单号
			
			$oldcardselldetail=$this->dbObj->GetRow('SELECT *  FROM '.WEB_ADMIN_TABPOX.'sellcarddetail WHERE  item_type=3 and	cardtype='.$cardtype2.' and cardid='.$marketingcard_id2.' and customercardid='.$customercardid.'  and agencyid ='.$agencyid);
			 //echo 'SELECT *  FROM '.WEB_ADMIN_TABPOX.'sellcarddetail WHERE  item_type=3 and	cardtype='.$cardtype2.' and cardid='.$marketingcard_id2.' and customercardid='.$customercardid.'  and agencyid ='.$agencyid;
			$cardtable2=$cardtable_name[$cardtype2];
			$cardtype=$this->dbObj->GetOne('SELECT marketingcardtype_id  FROM '.WEB_ADMIN_TABPOX.'marketingcard WHERE  marketingcard_id='.$_POST["marketingcard_id"].' and agencyid ='.$agencyid); 			
			$cardtable=$cardtable_name[$cardtype];
			
			//$newcardid = $this-> CardObj->creatcard($card_no,$marketingcard_id,$customerid,$employeeid,$agencyid,$cardtable);//生产新卡
			//$this-> dbObj-> Execute('update  '.WEB_ADMIN_TABPOX.$cardtable.' set status =0 WHERE  '.$cardtable.'_id='.$newcardid.' and agencyid ='.$agencyid);//新卡状态为未激活
/*			if($cardtype2=5 && $cardtype==5){//=====================================================
			$oldmarketingcard=$this->dbObj->Execute('SELECT *  FROM '.WEB_ADMIN_TABPOX.'marketingcard A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcarddetail B on A.marketingcard_id=B.marketingcard_id WHERE A.marketingcard_id='.$marketingcard_id2);//原卡方案
			while($oldmarketingcardinrrs=$oldmarketingcard-> FetchRow()){
			$oldmarketingcarditemtime["'".$oldmarketingcardinrrs['servicecategory_id']."'"]=$oldmarketingcardinrrs['services_times'];
			}
			
			$oldcard=$this->dbObj->GetRow('SELECT *  FROM '.WEB_ADMIN_TABPOX.$cardtable2.' WHERE '.$cardtable2.'_id='.$customercardid);//原卡
			//echo 'SELECT *  FROM '.WEB_ADMIN_TABPOX.$cardtable2.' WHERE '.$cardtable2.'_id='.$customercardid;
			
			$oldcarditemid=explode("||",$oldcard['itemlist']);
			$oldcardtime=explode("||",$oldcard['remaintimeslist']);
			for($i=0;$i<count($oldcarditemid);$i++){
			$oldcarditemtime["'".$oldcarditemid[$i]."'"]=$oldcardtime[$i];
			
			}
			
			$newcard=$this->dbObj->GetRow('SELECT *  FROM '.WEB_ADMIN_TABPOX.$cardtable.' WHERE '.$cardtable.'_id='.$id);//新卡
			//echo 'SELECT *  FROM '.WEB_ADMIN_TABPOX.$cardtable.' WHERE '.$cardtable.'_id='.$id;
			$newcarditemid=explode("||",$newcard['itemlist']);
			$newcardtime=explode("||",$newcard['remaintimeslist']);
			for($j=0;$j<count($newcarditemid);$j++){
			
			$newcarditemtime["'".$newcarditemid[$j]."'"]=$newcardtime[$j]-$oldmarketingcarditemtime["'".$newcarditemid[$j]."'"]+$oldcarditemtime["'".$newcarditemid[$j]."'"];
			$newcardtimestr=$newcardtimestr==''?$newcarditemtime["'".$newcarditemid[$j]."'"]:$newcardtimestr."||".$newcarditemtime["'".$newcarditemid[$j]."'"];
			
			}
			$remaintimes=$newcard["remaintimes"]-$oldcard["totaltimes"]+$oldcard["remaintimes"];
			$this->dbObj->Execute('update '.WEB_ADMIN_TABPOX.$cardtable.' set remaintimeslist="'.$newcardtimestr.'",remaintimes='.$remaintimes.'  WHERE '.$cardtable.'_id='.$id);//新卡
			//echo 'update '.WEB_ADMIN_TABPOX.$cardtable.' set remaintimeslist="'.$newcardtimestr.'",remaintimes='.$remaintimes.'  WHERE '.$cardtable.'_id='.$id;
			}//================================================================================*/
		 $this->SellObj=new sell();
		 
		 //$employeeid=$this ->dbObj-> GetOne('select A.employee_id from '.WEB_ADMIN_TABPOX.'employee A INNER JOIN '.WEB_ADMIN_TABPOX.'user B ON A.employee_id=B.employee_id where B.userid='.$this->getUid());
		 $sellidcount=$this ->dbObj-> GetOne('select count(*) from '.WEB_ADMIN_TABPOX.'sell  where sell_no="'.$_SESSION["sellno"].'"');
		// echo 'select count(*) from '.WEB_ADMIN_TABPOX.'sell  where sell_no='.$_SESSION["sellno"].'"';
		 if ($sellidcount==0){//如果没有新单号则插入新单号
		 $sellemployeeid=$this ->dbObj-> GetOne('select A.employee_id from '.WEB_ADMIN_TABPOX.'employee A INNER JOIN '.WEB_ADMIN_TABPOX.'user B ON A.employee_id=B.employee_id where B.userid='.$this->getUid());
		 $sellid=$this->SellObj->creatsellno($_SESSION["sellno"],$_SESSION["currentcustomerid"],$sellemployeeid,$_SESSION["currentorgan"],$cardtable='sell');
		 
		 $_SESSION["sellid"]=$sellid;
		 }else{
		 $sellid=$_SESSION["sellid"];
		 }
		 $item_type=3;
		 $item_id=$_POST['marketingcard_id'];
		 $number=$_POST['number'];
		 $value=$_POST['value'];
		 $price=$_POST['price'];
		 $discount=10;
		 if($_POST['giving']=='0'){
		  $discount=0;
		 }else{
		 $discount=10;}
		 //$cardtype=$_POST['cardtypeid'];
		 
		
		 $beauty_id=$_POST['beauty_id'];
		 $cardtable='sellcarddetail';
		 $employee_id=$_POST['employee_id'];
		 $itemmemo=$_POST['itemmemo'];
		 $cardid=$_POST['marketingcard_id'];
		 //$customercardid=$newcardid;
		 $customercardid=0;
		 $id = $this->SellObj->addsellitem($sellid,$item_type,$item_id,$number,$value,$price,$discount,$beauty_id,$cardtable,$cardtype,$cardid,$customercardid,$employee_id,$itemmemo);
		// $this -> dbObj -> Execute('INSERT INTO  '.WEB_ADMIN_TABPOX.'sell WHERE membershipcard_id in('.$delid.')');
		 $this-> dbObj-> Execute('update  '.WEB_ADMIN_TABPOX.$cardtable.' set returnstatus =1 ,returnid='.$oldcardselldetail["selldetail_id"].' WHERE  selldetail_id ='.$id);//添加换卡标志
		 //echo 'update  '.WEB_ADMIN_TABPOX.$cardtable.' set returnstatus =1 ,returnid='.$oldcardselldetail["selldetail_id"].' WHERE  selldetail_id ='.$id;
		// $this-> dbObj-> Execute('update  '.WEB_ADMIN_TABPOX.$cardtable2.' set status =3 WHERE  '.$cardtable2.'_id='.$customercardid.' and agencyid ='.$agencyid); 	//修改旧卡状态
		// echo 'update  '.WEB_ADMIN_TABPOX.$cardtable2.' set status =3 WHERE  '.$cardtable2.'_id='.$customercardid.' and agencyid ='.$agencyid;
		 //定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','buycard3.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
		

		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
function oldcard($customer_id){

			 $sql='select marketingcard_id, card_id as customercardid from '.WEB_ADMIN_TABPOX.'card  where customer_id='.$customer_id.' and status=1' ;
			 $sql2='select marketingcard_id, itemcard_id as customercardid  from '.WEB_ADMIN_TABPOX.'itemcard  where customer_id='.$customer_id.' and status=1' ;
			 $sql3='select marketingcard_id, treatmentcard_id as customercardid  from '.WEB_ADMIN_TABPOX.'treatmentcard  where customer_id='.$customer_id.' and status=1' ;
			 $sql4='select marketingcard_id, membershipcard_id as customercardid  from '.WEB_ADMIN_TABPOX.'membershipcard  where customer_id='.$customer_id.' and status=1' ;
			 $sql5='select marketingcard_id, experiencecard_id as customercardid  from '.WEB_ADMIN_TABPOX.'experiencecard  where customer_id='.$customer_id.' and status=1' ;
			 $sql6='select marketingcard_id, feelingcard_id as customercardid  from '.WEB_ADMIN_TABPOX.'feelingcard  where customer_id='.$customer_id.' and status=1' ;
			 $sql7='select marketingcard_id, storedvaluedcard_id as customercardid  from '.WEB_ADMIN_TABPOX.'storedvaluedcard  where customer_id='.$customer_id.' and status=1' ;
			 $sql=$sql." union ".$sql2." union ".$sql3." union ".$sql4." union ".$sql5." union ".$sql6." union ".$sql7;
			 $inrs = &$this -> dbObj -> Execute($sql);
			 $oldcard='';
			
			 while ($inrrs = &$inrs -> FetchRow()) {
				
				//$t -> set_var('gender',$inrrs["genderid"]==1?'男':'女');
				$marketingcard_name=$this -> dbObj -> getone('select marketingcard_name from '.WEB_ADMIN_TABPOX.'marketingcard  where marketingcard_id ='.$inrrs["marketingcard_id"]);
				$oldcard=$oldcard.'<option value="'.$inrrs["marketingcard_id"].'@@@'.$inrrs["customercardid"].'">'.$marketingcard_name.'</option> ';
				}
				
			return $oldcard;							 
}
function makeno($Prefix,$agency_no,$table,$column,$number,$id){
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by ".$id." desc limit 1");
//echo $this -> dbObj -> GetRow("select ".$column." from ".$table." order by ".$id." desc limit 1");
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
		exit("<script>alert('$info');location.href='services.php';</script>");
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
$main = new Pageservices();
$main -> Main();
?>
  