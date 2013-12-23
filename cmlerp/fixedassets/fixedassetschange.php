<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagefixedassetschange extends admin {
	function disp(){
		//定义模板
		$t = new Template('../template/fixedassets');
		$t -> set_file('f','fixedassetschange.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
   
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "%'.$keywords.'%"';}else{$condition=$category.' like "%'.$keywords.'%"';}
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'fixedassetschange  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'fixedassetschange p INNER JOIN '.WEB_ADMIN_TABPOX.$ftable." f on p.".$ftable."_id =f.".$ftable."_id  where f.".$category." like '%".$keywords."%' and  p.agencyid =".$_SESSION["currentorgan"] ;
			 
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'fixedassetschange  where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
			 
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  fixedassetschange_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
           	$t -> set_var('recordcount',$count);
						
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'produce  where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$fixedassets=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'fixedassets WHERE fixedassets_id = '.$inrrs["fixedassets_id"]);
				$t -> set_var('fixedassets_code',$fixedassets['fixedassets_code']);	
				$t -> set_var('fixedassets_name',$fixedassets['fixedassets_name']);	
				$t -> set_var('fixedassets_id',$fixedassets['fixedassets_id']);	
				$t -> set_var('fixedassets_no',$fixedassets['fixedassets_no']);	
				$t -> set_var('value',$fixedassets['value']);	 		
				$fixedassetscatalog=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'fixedassetscatalog WHERE fixedassetscatalog_id = '.$fixedassets["catalogid"]);
				$employee=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'employee  where employee_id ='.$inrrs["employee_id"]);
				$t -> set_var('employee_name',$employee['employee_name']); 
				$t -> set_var('catalog_name',$fixedassetscatalog['fixedassetscatalog_name']);	
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['fixedassetschange_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['fixedassetschange_id']));				
				
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		 		
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	
		$this->getSupper()?$t -> set_var('canexport',''):$t -> set_var('canexport','none');	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> set_var('agencyid',$_SESSION["currentorgan"]);
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispAppend(){

		$t = new Template('../template/fixedassets');
		$t -> set_file('f','fixedassetschange_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	


		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');


		 
	 
		
		$t -> set_var('date',date("Y-m-d"));	
			

		$t -> set_var('picpath',"");
		$t -> set_var('description',"");	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('memo',"");
		$t -> set_var('employee_name','');	
		 $t -> set_var('employee_id',''); 
		 $t -> set_var('forwhat',"");
		$t -> set_var('picpath',"");	
		if($_GET['fixedassets_id']<>''){
			
			$fixedassets=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'fixedassets WHERE fixedassets_id = '.$_GET["fixedassets_id"]);
			$t -> set_var('fixedassets_code',$fixedassets['fixedassets_code']);	
			$t -> set_var('fixedassets_name',$fixedassets['fixedassets_name']);	
			 $t -> set_var('fixedassets_id',$fixedassets['fixedassets_id']);	
			 $t -> set_var('fixedassets_no',$fixedassets['fixedassets_no']);	
			 $t -> set_var('value',$fixedassets['value']);	
		}else{
			$t -> set_var('fixedassets_code',"");	
			$t -> set_var('fixedassets_name',"");	
			$t -> set_var('value',"0");	
			$t -> set_var('fixedassets_no',"");	
			$t -> set_var('fixedassets_id',"");
		}
			
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'fixedassetschange WHERE fixedassetschange_id = '.$updid);
			$t -> set_var($data);
			$employee=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'employee  where employee_id ='.$data["employee_id"]);
			 $t -> set_var('employee_name',$employee['employee_name']);
			 $t -> set_var('employee_id',$employee['employee_id']);
			//$usestatus_name=array("请选择","未使用","使用中","报废","保修","其他");	
			//echo $usestatus_name[$data["usestatus"]];
			//echo $usestatus_name[$data['usestatus']];
			
			$fixedassets=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'fixedassets WHERE fixedassets_id = '.$data['fixedassets_id']);
			$t -> set_var('fixedassets_code',$fixedassets['fixedassets_code']);	
			$t -> set_var('fixedassets_name',$fixedassets['fixedassets_name']);	
			 $t -> set_var('fixedassets_id',$fixedassets['fixedassets_id']);	
			 $t -> set_var('fixedassets_no',$fixedassets['fixedassets_no']);	
			 $t -> set_var('value',$fixedassets['value']);	
			$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['unitid']));
			

			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');

		}

		//$t -> set_var('brandlist',$this ->selectlist('brand','brand_id','brand_name',$data['brandid']));
		//$t -> set_var('genderlist',"111");	
		//echo $data['genderid'];
		//$t -> set_var('genderlist',$this -> gender($data['genderid']));	
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));						

		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}
	
	
		function usestatuslist1($selectid=0){
			$str='';
			$usestatus_name=array("请选择","未使用","使用中","报废","保修","其他");		
			for($i=1;$i<count($usestatus_name);$i++){	
			 
			if ($i==$selectid){
			$str=$str."<option value='".$i."' selected>".$usestatus_name[$i]."</option>";	}
			else{
			$str =$str."<option value='".$i."'>".$usestatus_name[$i]."</option>";	}
			}	
			return  $str;	
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
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'fixedassetschange WHERE fixedassetschange_id in('.$delid.')');
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
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."fixedassetschange` (`fixedassets_id`, `date`,`description`, `forwhat`, `memo`, `agencyid`,employee_id)VALUES ('".$_POST["fixedassets_id"]."','".$_POST["date"]."','".$_POST["description"]."','".$_POST["forwhat"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["employee_id"]."')");
 
//echo "INSERT INTO `".WEB_ADMIN_TABPOX."fixedassetschange` (`fixedassets_id`, `fixedassetschangevalue`, `date`,`description`, `forwhat`, `memo`, `agencyid`)VALUES ('".$_POST["fixedassets_id"]."','".$_POST["fixedassetschangevalue"]."','".$_POST["date"]."','".$_POST["description"]."','".$_POST["forwhat"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."')";
echo "INSERT INTO `".WEB_ADMIN_TABPOX."fixedassetschange` (`fixedassets_id`,  `date`,`description`, `forwhat`, `memo`, `agencyid`)VALUES ('".$_POST["fixedassets_id"]."','".$_POST["date"]."','".$_POST["description"]."','".$_POST["forwhat"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."')";
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."fixedassetschange` SET `fixedassets_id`='".$_POST["fixedassets_id"]."',description='".$_POST["description"]."',forwhat='".$_POST["forwhat"]."', `date`='".$_POST["date"]."', `memo`='".$_POST["memo"]."',employee_id='".$_POST["employee_id"]."'  WHERE fixedassetschange_id =".$id);
           
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
		exit("<script>alert('$info');location.href='fixedassetschange.php';</script>");
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
$main = new Pagefixedassetschange();
$main -> Main();
?>
  