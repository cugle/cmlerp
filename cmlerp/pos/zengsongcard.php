<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/sell.cls.php');
class Pagecustomer extends admin {
	function disp(){
		//定义模板
		$t = new Template('../template/customer');
		$t -> set_file('f','membercard.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
   
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.'="'.$keywords.'"';}else{$condition=$category.'="'.$keywords.'"';}
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'membercard  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'membercard  p INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on p.customer_id =f.customer_id  where f.customer_name like '%".$keywords."%' and  p.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'membercard   where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  membercard_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
           	$t -> set_var('recordcount',$count);
						
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'produce  where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('gender',$inrrs["genderid"]==1?'男':'女');
				//$t -> set_var('category_name',$this -> dbObj -> getone('select category_name from '.WEB_ADMIN_TABPOX.'procatalog where category_id ='.$inrrs["categoryid"]));
				$t -> set_var('customer_name',$this -> dbObj -> getone('select customer_name from '.WEB_ADMIN_TABPOX.'customer where customer_id ='.$inrrs["customer_id"]));
				$t -> set_var('memcardlevel',$this -> dbObj -> getone('select cardlevel_name  from '.WEB_ADMIN_TABPOX.'memcardlevel where cardlevel_id ='.$inrrs["cardlevel_id"]));
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['membercard_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['membercard_id']));				
				
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispAppend(){

		$t = new Template('../template/pos');
		$t -> set_file('f','zengsongcard.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	

	
	 
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		
		$currentcustomerid=$_SESSION["currentcustomerid"];
		$membercardno=$_SESSION["membercardno"];
 
		//echo 'SELECT * FROM `'.WEB_ADMIN_TABPOX.'customer`  A INNER JOIN '.WEB_ADMIN_TABPOX.'membercard B ON A.customer_id= B.customer_id where A.customer_id ='.$currentcustomerid;
		$memberdata=$this -> dbObj -> GetRow('SELECT * FROM `'.WEB_ADMIN_TABPOX.'customer`  where  customer_id ='.$currentcustomerid);
 		
	 
		$t -> set_var('customer_no',$memberdata['customer_no']);
		$t -> set_var('customer_id',$memberdata['customer_id']);
		$t -> set_var('customer_name',$memberdata['customer_name']);
		$t -> set_var('code',$memberdata['code']);
		$t -> set_var('dingjin',$memberdata['dingjin']);
		$t -> set_var('dingjin_memo','');
		  
		
		$t -> set_var('dingjinvalue','0.00');
		$t -> set_var('employee_id','');
		$t -> set_var('employee_name','');
		$t -> set_var('beauty_id','');
		$t -> set_var('beauty_name','');

 
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'membercard  WHERE membercard_id = '.$updid);
			$t -> set_var($data);
			$t -> set_var('customer_name',$this -> dbObj -> getone('select customer_name from '.WEB_ADMIN_TABPOX.'customer where customer_id ='.$data["customer_id"]));		
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');

		}

	    
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}
	
	
 
		function selectlist($table,$id,$name,$selectid=0){
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table .' where agencyid ='.$_SESSION["currentorgan"]);
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
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'membercard   WHERE membercard_id in('.$delid.')');
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
			$info = '赠送购卡款'.$_POST["dingjinvalue"].'元';	
			//$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."customer` SET `yufukuan` =yufukuan+ '".$_POST["yufuvalue"]."',yufu_memo='".$_POST["yufu_memo"]."'  WHERE customer_id =".$_POST['customer_id']);		
			 //echo "UPDATE `".WEB_ADMIN_TABPOX."customer` SET `yufukuan` =yufukuan+ '".$_POST["yufuvalue"]."',yufumemo='".$_POST["yufumemo"]."'  WHERE customer_id =".$_POST['customer_id'];
			
		 //增加销售单项目
		 $this->SellObj=new sell();
		 $employeeid=$this ->dbObj-> GetOne('select A.employee_id from '.WEB_ADMIN_TABPOX.'employee A INNER JOIN '.WEB_ADMIN_TABPOX.'user B ON A.employee_id=B.employee_id where B.userid='.$this->getUid());
		 $sellidcount=$this ->dbObj-> GetOne('select count(*) from '.WEB_ADMIN_TABPOX.'sell  where sell_no="'.$_SESSION["sellno"].'"');
		 //echo 'select count(*) from '.WEB_ADMIN_TABPOX.'sell  where sell_no='.$_SESSION["sellno"].'"';
		 
		 if ($sellidcount==0){//如果没有新单号则插入新单号
		 $customerid=$_SESSION["currentcustomerid"]?$_SESSION["currentcustomerid"]:$_POST['customer_id'];
		 $sellid=$this->SellObj->creatsellno($_SESSION["sellno"],$customerid,$employeeid,$_SESSION["currentorgan"],$cardtable='sell');
		 
		 $_SESSION["sellid"]=$sellid;
		 }else{
		 $sellid=$_SESSION["sellid"];
		 }
		 
		 $item_id=8;
		 $number=1;
		 $value=$_POST["dingjinvalue"];
		 $price=$_POST["dingjinvalue"];
		 $discount=0;
		 $cardtype=0;
		 $cardid=0;
		 $customercardid=$_POST['customer_id'];
		 $employee_id=$_POST['employee_id'];
		 $itemmemo=$_POST['dingjin_memo'];
		 //if($_POST['givingbeauty']=='on'){
			
			// $discount=0;
			   // echo  $discount;
			// }else{
		   //  $discount=10;
		
		// }
		 $item_type=5;
		 $beauty_id=$_POST['beauty_id'];
		 $cardtable='sellotherdetail';
		 $id = $this->SellObj->addsellitem($sellid,$item_type,$item_id,$number,$value,$price,$discount,$beauty_id,$cardtable,$cardtype,$cardid,$customercardid,$employee_id,$itemmemo);
		// $this->dbObj->Execute('UPDATE '.WEB_ADMIN_TABPOX.'sellotherdetail   SET memo = '.$_POST['value'].'  WHERE   agencyid='.$agencyid.' and selldetail_id ='.$id);	
		 
		 
		 
			 
			 
			 //预收款历史。。。
			 //$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."deposit` (`membercard_id`, `deposittype`, `value`, `employee_id`, `depositmemo`, `agencyid`)VALUES ( '".$_POST["membercard_id"]."',1,'".$_POST["dingjin"]."', '".$_POST["employee_id"]."','".$_POST["depositmemo"]."', '".$_SESSION["currentorgan"]."')");
           // echo "INSERT INTO `".WEB_ADMIN_TABPOX."deposit` (`membercard_id`, `deposittype`, `value`, `employee_id`, `depositmemo`, `agencyid`)VALUES ( '".$_POST["membercard_id"]."',1,'".$_POST["dingjin"]."', '".$_POST["employee_id"]."','".$_POST["depositmemo"]."', '".$_SESSION["currentorgan"]."')";
//echo "INSERT INTO `".WEB_ADMIN_TABPOX."membercard` (`membercard_no`, `cardlevel_id`, `customer_id`, `score`,`startdate`,`overdate`,` man`, ` agencyid`)VALUES ( '".$_POST["membercard_no"]."','".$_POST["cardlevel_id"]."', '".$_POST["customer_id"]."','".$this->intnonull($_POST["score"])."', '".$_POST["startdate"]."', '".$_POST["overdate"]."',  '".$_POST["man"]."', '".$_SESSION["currentorgan"]."')";
			$id = $this -> dbObj -> Insert_ID();
			$acounttypeid=$_POST['acounttypeid'];
			$agencyid=$_SESSION["customerorgan"]<>''?$_SESSION["customerorgan"]:$_SESSION["currentorgan"];
			$value=$_POST["dingjinvalue"];
				$acountdata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'account WHERE `type`='.$acounttypeid.' and  agencyid='.$agencyid);
	// echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'account WHERE `type`='.$acounttypeid.' and  agencyid='.$agencyid; 
		$acountid=$acountdata['account_id'];
		$lastbalance=$acountdata['balance'];
		$nowbalance=$acountdata['balance']+$value;
		//$this->dbObj->Execute('UPDATE '.WEB_ADMIN_TABPOX.'account   SET  balance =balance + '.$value.'  WHERE   agencyid='.$agencyid.' and account_id ='.$acountid);	
		//echo 'UPDATE '.WEB_ADMIN_TABPOX.'account   SET  balance =balance + '.$value.'  WHERE   agencyid='.$agencyid.' and account_id ='.$acountid;
		 
		//$type=3;//销售1
		//$repaymentmemo=$_POST['yufumemo'];
		//$memo=$repaymentmemo<>''?$repaymentmemo:'预收款';
		 
		//账户流水账
		 
	//	$this->dbObj->Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'accounthistory (`account_id`, `value`,  `type`, `sellid`, `lastbalance`, `nowbalance`, `memo`, `agencyid`)value('.$acountid.','.$value.','.$type.',"'.$sellid.'",'.$lastbalance.','.$nowbalance.',"'.$memo.'",'.$agencyid.')') ;//帐户流水账 
	 // echo 'INSERT INTO '.WEB_ADMIN_TABPOX.'accounthistory (`account_id`, `value`,  `type`, `sellid`, `lastbalance`, `nowbalance`, `memo`, `agencyid`)value('.$acountid.','.$value.','.$type.',"'.$sellid.'",'.$lastbalance.','.$nowbalance.',"'.$memo.'",'.$agencyid.')';
	
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."membercard` SET `membercard_no` = '".$_POST["membercard_no"]."',`code` = '".$_POST["code"]."',`cardlevel_id` = '".$_POST["cardlevel_id"]."',`customer_id` = '".$_POST["customer_id"]."',`score` = '".$_POST["score"]."',startdate='".$_POST["startdate"]."', overdate='".$_POST["overdate"]."',man='".$_POST["man"]."' WHERE membercard_id =".$id);
//echo "UPDATE `".WEB_ADMIN_TABPOX."membercard` SET `membercard_no` = '".$_POST["membercard_no"]."',`cardlevel_id` = '".$_POST["cardlevel_id"]."',`customer_id` = '".$_POST["customer_id"]."',`score` = '".$_POST["score"]."',startdate='".$_POST["startdate"]."', overdate='".$_POST["overdate"]."',man='".$_POST["man"]."' WHERE membercard_id=".$id;
		}
//$this -> quit($info.'成功！');
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
		 
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
	function intnonull($int){
	if ($int=="")$int=0;
		return $int;
	}
	function roomtype($type='0'){
		$arr="";
		if($type=='1'){
		$arr="<option value='1' selected>一组</option><option value='2' >二组</option>";
		}else if($type=='2')
		{$arr="<option value='1' >一组</option><option value='1' selected>二组</option>";
		}else
		{
		$arr="<option value='1' >一组</option><option value='1'>二组</option>";
		}
		return $arr;
	}
	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');window.close();</script>");
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
  