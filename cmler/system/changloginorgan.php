<?
/**
 * @package System
 */

require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class Pagevisiteplan extends admin {


	function disp(){
		//定义模板
		$t = new Template('../template/system');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[##
        $t->right_delimiter = "#]"; //修改右边界符##]
		$t -> set_file('f','changloginorgan.html');
		$t -> set_block('f','agencylist','al');
		$t -> set_var('add',$this -> getAddStr('img'));

			//设置机构
			
			$t -> set_var('al');
			$inrs = &$this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX."agency where agencytype_id=4 order by agency_id desc");
		
			while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('agency_id',$inrrs['agency_id']);
				$t -> set_var('agency_easyname',$inrrs['agency_easyname']);

				$t -> parse('al','agencylist',true);
			}
			$inrs -> Close();
			
				
			
        $t -> set_var('pagelist',$this -> page("agent.php?qgtype=".$_GET[qgtype]."&Sale_ID=".$_GET[Sale_ID],$count,$psize,$pageid));		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	
	function goDispAppend(){
	}	
	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){

	}
	function goAppend(){

	}
	function goModify(){
		$this -> dbObj -> Execute("update ".WEB_ADMIN_TABPOX."user  SET currentorgan='".$_POST['organid']."' WHERE userid = ".$this->getUid());	
		//session_start(); 
		//session_register("currentorgan"); 
		$_SESSION["currentorgan"]=$_POST['organid']; 	
		$_SESSION["agency_no"]=$this -> dbObj -> GetOne("select  agency_no from ".WEB_ADMIN_TABPOX."agency  where  agency_id =".$_SESSION["currentorgan"]); 		
		$_SESSION["currentorganname"] = $this ->  dbObj -> GetOne("select agency_easyname from ".WEB_ADMIN_TABPOX."agency   where agency_id =".$_POST['organid']);	
	    exit("<script>parent.parent.frames[0].location.reload();parent.parent.frames[1].location.reload();location.href='changloginorgan.php';</script>");	

		//$this -> goAppend();	

		
	}
	function quit($info){
		exit("<script>alert('$info');location.href='agency.php';</script>");
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
		$url = $url."?cgrand=cugle";
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
$main = new Pagevisiteplan();
$main -> Main();
?>