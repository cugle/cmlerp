<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
	function disp(){
		//定义模板
		$selltype_id= "1";
		$t = new Template('../template/pos');
		$t -> set_file('f','sell.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "%'.$keywords.'"';}else{$condition=$category.' like "%'.$keywords.'%"';}
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'sell  where   agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql="select * from ".WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  s.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'sell  where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
			
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  sell_id DESC  LIMIT ".$offset." , ".$psize);
			
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));			
	    	$t -> set_var('recordcount',$count);	
			
			
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				
				//$t -> set_var('standardunit',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$inrrs["standardunit"]));
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['sell_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['sell_id']));		
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
		$t -> set_file('f','sell_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
			
			
		$Prefix='XS';
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'sell';
		$column='sell_no';
		$number=5;
		$id='sell_id';
		$sellno=$sellno?$sellno:$this->makeno($Prefix,$agency_no,$table,$column,$number,$id);
		
		$t -> set_var('sell_no',$sellno);	
		$t -> set_var('membercard_no',"");	
		$t -> set_var('error',"");	
		$t -> set_var('updid',"");
		$t -> set_var('timelimit',"");
		$t -> set_var('pricepertime',"");
		$t -> set_var('totaltimes',"");	
		$t -> set_var('commission',"");	
		$t -> set_var('ucommission',"");	
		$t -> set_var('price',"");	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");
		$t -> set_var('comdisplay',"none");		
		$t -> set_var('memo',"");
		
		$t -> set_var('recordcount',"");
		
		$t -> set_var('cservices_no',"");	
		$t -> set_var('cservices_name',"");	
		$t -> set_var('cpricepertime',"");	
		$t -> set_var('cservices_id',"");	
			
		$t -> set_var('cservices_times',"");	
		$t -> set_var('cprice',"");	
		$t -> set_var('cmemo',"");	
		$t -> set_var('ccategory_name','');		
		$t -> set_var('ccategory_id','');			
		$t -> set_var('ml');
		$t -> set_var('userid',$this->getUid());		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$date=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'sell WHERE sell_id = '.$updid);
			if($date['assignservice']==1){
			$t -> set_var('assignservice1','checked="checked"') ;
			$t -> set_var('assignservice0','') ;
			}else{
			$t -> set_var('assignservice0','checked="checked"') ;
			$t -> set_var('assignservice1','') ;
			}
			$t -> set_var('updid',$updid);
			
			$t -> set_var('action','upd');			
			$t -> set_var($date);			
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$t -> set_var('comdisplay',"");	
		$t -> set_var('cservices_no',"");	
		$t -> set_var('cservices_name',"");	
		$t -> set_var('ccode',"");	
		$t -> set_var('cservices_id',"");	
		$t -> set_var('cstandardunit',"");	
		$t -> set_var('lowerlimit',"");	
		$t -> set_var('cprice',"");	
		$t -> set_var('cmemo',"");	
		$t -> set_var('cpricepertime',"");	
		$t -> set_var('cservices_times',"");	
		$t -> set_var('cviceunit',"");	
		$t -> set_var('conupdid',"");	
		
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
				
			
		//设置消耗品列表
			
			$t -> set_var('ml');

						
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'selldetail  where sell_id  ='.$updid);
		

			
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);				
				$data1=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'services   where services_id ='.$inrrs["services_id"].' and agencyid ='.$_SESSION["currentorgan"]);				
				
				$data2=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'servicecategory    where category_id  ='.$data1["categoryid"]);
				$t -> set_var($data2);
				$t -> set_var($data1);
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();			
			$acount = &$this -> dbObj -> GetOne('select count(*) as acount from '.WEB_ADMIN_TABPOX.'selldetail  where sell_id  ='.$updid);
			$t -> set_var('recordcount',$acount);	
		// 修改消耗品
		 
		    if($_GET['consumables_id']!=''){
			//$t -> set_var('cs');
		   $t -> set_var('caction','upd');	

			$inrs2 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'selldetail  where selldetail_id ='.$_GET['consumables_id']);
			//$t -> set_var($inrs);
		    $t -> set_var('cupdid',$inrs2['services_id']);	
			$t -> set_var('conupdid',$_GET['consumables_id']);	
			$t -> set_var('cservices_times',$inrs2['services_times']);
			$t -> set_var('cpricepertime',$inrs2['pricepertime']);
			$inrs3 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'services   where services_id ='.$inrs2['services_id']);
			$t -> set_var('cservices_no',$inrs3['services_no']);
			$t -> set_var('cservices_name',$inrs3['services_name']);
			$t -> set_var('cservices_id',$inrs3['services_id']);
			$t -> set_var('ccode',$inrs3['code']);
			$t -> set_var('cprice',$inrs3['price']);
			$t -> set_var('cstandardunit',$inrs3['standardunit']);
			$t -> set_var('cviceunit',$inrs3['viceunit']);
			$t -> set_var('cviceunitnumber',$inrs3['viceunitnumber']);
			//$t -> parse('cs','consumables',true);
			//$inrs2 -> Close();	
			}
			
		$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$date['categoryid']));	
		$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$date['standardunit']));	
		$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$date['viceunit']));	

		}
		$t -> set_var('categorynamelist',$this ->selectlist('servicecategory','category_id','category_name',$date['categoryid']));		
		
		
		//$t -> set_var('ml',"");	

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
	  if($_GET['consumables_id']==''){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'sell WHERE sell_id in('.$delid.')');
		}else{
		echo 'DELETE FROM '.WEB_ADMIN_TABPOX.'selldetail WHERE selldetail_id in('.$_GET['consumables_id'].')';
		 $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'selldetail WHERE selldetail_id in('.$_GET['consumables_id'].')');
		
		}
		$info='删除';
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}
	function goAppend(){
		$selltype_id= "1";
		$id = 0;
		$info = '';
		
		if($this -> isAppend){
			$info = '增加';	
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."sell` (`sell_name`,`selltype_id`, `sell_no`, `coderule`,`totaltimes`, `price`, `timelimit`, `pricepertime`, `assignservice`,`memo`, `agencyid`) VALUES ('" .$_POST["sell_name"]."','".$selltype_id."','".$_POST["sell_no"]."', '".$_POST["coderule"]."', '".$_POST["totaltimes"]."', '".$this->intnonull($_POST["price"])."',  '".$this->intnonull($_POST["timelimit"])."','".$this->intnonull($_POST["pricepertime"])."','".$this->intnonull($_POST["assignservice"])."', '".$_POST["memo"]."','".$_SESSION["currentorgan"]."')");
 echo "INSERT INTO `".WEB_ADMIN_TABPOX."sell` (`sell_name`,`selltype_id`, `sell_no`, `coderule`,`totaltimes`, `price`, `timelimit`, `pricepertime`, `assignservice`,`memo`, `agencyid`) VALUES ('" .$_POST["sell_name"]."','".$selltype_id."','".$_POST["sell_no"]."', '".$_POST["coderule"]."', '".$_POST["totaltimes"]."', '".$this->intnonull($_POST["price"])."',  '".$this->intnonull($_POST["timelimit"])."','".$this->intnonull($_POST["pricepertime"])."','".$this->intnonull($_POST["assignservice"])."', '".$_POST["memo"]."','".$_SESSION["currentorgan"]."')";
			$id = $this -> dbObj -> Insert_ID();
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."selldetail` (sell_id,servicecategory_id,services_id,pricepertime,services_times ,memo,agencyid) VALUES ('".$id."','".$_POST["ccategory_id"]."','".$_POST["cservices_id"]."','".$_POST["cpricepertime"]."','".$_POST["cservices_times"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')");
			exit("<script>alert('新增成功');location.href='itemcard.php?action=upd&updid=".$id."';</script>");	
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."sell` SET `sell_name` = '".$_POST["sell_name"]."',`selltype_id` = '".$selltype_id."',`sell_no` = '".$_POST["sell_no"]."',`coderule` = '".$_POST["coderule"]."',`totaltimes` = '".$this->intnonull($_POST["totaltimes"])."',`timelimit` ='".$this->intnonull($_POST["timelimit"])."',`price` = '".$this->intnonull($_POST["price"])."',`pricepertime` = '".$this->intnonull($_POST["pricepertime"])."',`assignservice` = '".$this->intnonull($_POST["assignservice"])."',`memo` = '".$_POST["memo"]."' WHERE sell_id =".$id);
			//echo "UPDATE `".WEB_ADMIN_TABPOX."sell` SET `sell_name` = '".$_POST["sell_name"]."',`sell_no` = '".$_POST["sell_no"]."',`categoryid` = '".$_POST["categoryid"]."',`miner1` = '".$this->intnonull($_POST["miner1"])."',`miner2` ='".$this->intnonull($_POST["miner2"])."',`price` = '".$this->intnonull($_POST["price"])."',`commission` = '".$this->intnonull($_POST["commission"])."',`ucommission` = '".$this->intnonull($_POST["ucommission"])."',`compcent` = '".$_POST["compcent"]."',`ucompcent` = '".$_POST["ucompcent"]."'，`memo` = '".$_POST["memo"]."' WHERE sell_id =".$id;
		//echo $_POST["con_act"];
 if($_POST["con_act"]=='upd'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."selldetail` SET `services_id` = '".$_POST["cservices_id"]."',`pricepertime` = '".$_POST["cpricepertime"]."',`services_times` = '".$_POST["cservices_times"]."',`memo` = '".$_POST["cmemo"]."' WHERE consumables_id  =".$_POST['conupdid']);		
		echo "UPDATE `".WEB_ADMIN_TABPOX."selldetail` SET `services_id` = '".$_POST["cservices_id"]."',`pricepertime` = '".$_POST["cpricepertime"]."',`services_times` = '".$_POST["cservices_times"]."',`memo` = '".$_POST["cmemo"]."' WHERE consumables_id  =".$_POST['conupdid'];
		}else{

			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."selldetail` (sell_id,servicecategory_id,services_id,pricepertime,services_times ,memo,agencyid) VALUES ('".$id."','".$_POST["ccategory_id"]."','".$_POST["cservices_id"]."','".$_POST["cpricepertime"]."','".$_POST["cservices_times"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')");
		//$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."serviceconsume` SET `produce_id` = '".$_POST["produce_id"]."',`std_consumption` = '".$_POST["std_consumption"]."',`memo` = '".$_POST["memo"]."',`sell` = '".$_POST["sell"]."' WHERE sell_id =".$id);
		echo "INSERT INTO `".WEB_ADMIN_TABPOX."selldetail` (sell_id,services_id,pricepertime,services_times ,memo,agencyid) VALUES ('".$id."','".$_POST["cservices_id"]."','".$_POST["cpricepertime"]."','".$_POST["cservices_times"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')";
		}
	exit("<script>alert('修改成功');location.href='itemcard.php?action=upd&updid=".$id."';</script>");
		}
//$this -> quit($info.'成功！');
 
		$this -> quit($info.'成功！');

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
		exit("<script>alert('$info');history.go(-1);</script>");
	}
	
function makeno($Prefix,$agency_no,$table,$column,$number,$id){
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by ".$id." desc limit 1");
//echo "select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by ".$id." desc limit 1";
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
  