<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
	function disp(){
		//定义模板
		$t = new Template('../template/finace');
		$t -> set_file('f','skuanbill.html');
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
			
		
			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'skuanbill    where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;
			
			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'skuanbill   a INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on a.categoryid =f.skuanbill_id  where f.skuanbill_name like '%".$keywords."%' and  a.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'skuanbill   where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
			 
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  skuanbill_id  DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
	    	$t -> set_var('recordcount',$count);		


			$objecttypename=array("","顾客","供应商","其他");
			$objecttable=array("","customer","suppliers","objecttype");
			//设置分类
			$t -> set_var('ml');
			
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'skuanbill where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				if($inrrs['parentid']==0){
					$parentname='根类';
				}else{	
					$parentnamedata= $this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'skuanbill   where  skuanbill_id ='.$inrrs["parentid"]);
					$parentname=$parentnamedata['skuanbill_name'];
				}
				$t -> set_var('parentname',$parentname);
				$t -> set_var('object_type_name',$objecttypename[$inrrs["object_type"]]);
				$t -> set_var('object_name',$this->dbObj->getone("select ".$objecttable[$inrrs['object_type']]."_name from ".WEB_ADMIN_TABPOX.$objecttable[$inrrs['object_type']]." WHERE  ".$objecttable[$inrrs['object_type']]."_id=".$inrrs['object_id']));
				$t -> set_var('employee_name',$this -> dbObj -> getone('select employee_name  from '.WEB_ADMIN_TABPOX.'employee  where employee_id ='.$inrrs["employee_id"]));
			  	$t -> set_var('delete',$this -> getDelStr('',$inrrs['skuanbill_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['skuanbill_id']));					   
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
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

		$t = new Template('../template/finace');
		$t -> set_file('f','skuanbill_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	


		if($this -> isAppend){
			
		$Prefix='SK';
		$agency_no=$_SESSION["agency_no"].date('ym',time());
		$table=WEB_ADMIN_TABPOX.'skuanbill';
		$column='skuanbill_no';
		$number=3;
		$id='skuanbill_id';	
		
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		//$t -> set_var('skuanbilllist',$this ->selectlist('skuanbill','skuanbill_id','skuanbill_name',''));	
		$t -> set_var('skuanbill_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id)); 
		
		$t -> set_var('skuanbill_id',"");	
		 
		$t -> set_var('employee_id',"");	
		$t -> set_var('employee_name',"");
		$t -> set_var('object_name',""); 
		$t -> set_var('object_id',"");	
		$t -> set_var('account',"");
		$t -> set_var('date',date('Y-m-d',time()));
		$t -> set_var('accountlist',$this ->selectlist('account','account_id','account_name',''));	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('memo',"");
		$t -> set_var('man',$this-> dbObj->getone('select employee_name from '.WEB_ADMIN_TABPOX.'employee A inner join '.WEB_ADMIN_TABPOX.'user B ON A.employee_id=B.employee_id where userid='.$this->getUid()));
		$t -> set_var('objecttypelist',$this->objecttype());
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$skuanbilldata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'skuanbill WHERE skuanbill_id = '.$updid);
			$t -> set_var($skuanbilldata);			
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			//$t -> set_var('skuanbilllist',$this ->selectlist('skuanbill','skuanbill_id','skuanbill_name',$skuanbilldata['parentid']));	
			$t -> set_var('actionName','修改');
			
			$t -> set_var('accountlist',$this ->selectlist('account','account_id','account_name',$skuanbilldata['account_id']));	
			$objecttypename=array("","顾客","供应商","其他");
			$objecttable=array("","customer","suppliers","objecttype");
			$t -> set_var('object_name',$this->dbObj->getone("select ".$objecttable[$skuanbilldata['object_type']]."_name from ".WEB_ADMIN_TABPOX.$objecttable[$skuanbilldata['object_type']]." WHERE  ".$objecttable[$skuanbilldata['object_type']]."_id=".$skuanbilldata['object_id']));
			$t -> set_var('employee_name',$this -> dbObj -> getone('select employee_name  from '.WEB_ADMIN_TABPOX.'employee  where employee_id ='.$skuanbilldata["employee_id"]));
			$t -> set_var('objecttypelist',$this->objecttype($skuanbilldata["object_type"]));
			
		}
					
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}
	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'skuanbill  WHERE skuanbill_id in('.$delid.')');
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
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."skuanbill` (`skuanbill_no` ,`object_type`, `object_id`,   `account_id`, `account`, `date`, `employee_id`, `man`, `memo`, `agencyid` )VALUES ( '".$_POST['skuanbill_no']."', '".$_POST['object_type']."','".$_POST['object_id']."', '".$_POST['account_id']."','".$_POST['account']."', '".$_POST['date']."','".$_POST['employee_id']."', '".$_POST['man']."',  '".$_POST["memo"]."','".$_SESSION['currentorgan']."')");
echo "INSERT INTO `".WEB_ADMIN_TABPOX."skuanbill` (`skuanbill_no` ,`object_type`, `object_id`,   `account_id`, `account`, `date`, `employee_id`, `man`, `memo`, `agencyid` )VALUES ( '".$_POST['skuanbill_no']."', '".$_POST['object_type']."','".$_POST['object_id']."','".$_POST['account_id']."', '".$_POST['account']."', '".$_POST['date']."','".$_POST['employee_id']."', '".$_POST['man']."',  '".$_POST["memo"]."','".$_SESSION['currentorgan']."')";
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."skuanbill SET skuanbill_no='".$_POST["skuanbill_no"]."',  object_type='".$_POST['object_type']."',object_id='".$_POST['object_id']."',account_id='".$_POST['account_id']."', account='".$_POST['account']."', date='".$_POST['date']."',employee_id='".$_POST['employee_id']."', man='".$_POST['man']."',  memo='".$_POST["memo"]."'  WHERE skuanbill_id =".$id);

		}
//$this -> quit($info.'成功！');
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}
	function objecttype($type){
		$arr="";
		if($type=='2'){
		$arr="<option value='2' selected>供货商</option><option value='1' >顾客</option>";
		}else if($type=='1')
		{$arr="<option value='2' >供货商</option><option value='1' selected>顾客</option>";
		}else
		{
		$arr="<option value='2' >供货商</option><option value='1'>顾客</option>";
		}
		return $arr;
	}
function makeno($Prefix,$agency_no,$table,$column,$number,$id){
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." ORDER BY ".$id." desc limit 1");
//echo "select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." ORDER BY ".$id." desc limit 1";
//echo $this -> dbObj -> GetRow("select ".$column." from ".$table." ORDER BY ".$id." desc limit 1");
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

	function goModify(){
		$this -> goAppend();
	}

	
		function selectlist($table,$id,$name,$selectid=0){
	
            $inrs= &$this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX.$table ." where agencyid =".$_SESSION['currentorgan']);
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
		
	function quit($info){
		exit("<script>alert('$info');location.href='skuanbill.php';</script>");
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
  