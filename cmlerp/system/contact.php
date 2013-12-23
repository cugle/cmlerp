<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class Pagecontact extends admin {
	function disp(){
		//定义模板
		
		$t = new Template('../template/system');
		$t -> set_file('f','contact.html');
		$t -> set_block('f','contact','c');

		$t -> set_var('add',$this -> getAddStr('img'));

		//设置用户
		$cateid=$_POST[cateid];
		$keywords=$_POST[keywords];
		$cateid=$_GET[cateid]?$_GET[cateid]:$cateid;
		$keywords=$_GET[keywords]?$_GET[keywords]:$keywords;
		$Sale_ID=$_GET[Sale_ID];
		if ($keywords==""&$cateid==""){$condition="";}else{$condition=$cateid." like '".$keywords."'";}
		if ($Sale_ID!=""){$condition=" userid=".$Sale_ID;}
		$condition = $condition ? " WHERE ".$condition : "";
		$condition =$condition." ORDER BY contactid desc";		
		$result= $this -> dbObj ->Execute("select *  from ".WEB_ADMIN_TABPOX."contact".$condition);
		$count=$result->RecordCount();
		$pageid=$_GET[pageid];
		$pageid = intval($pageid);
		$psize=$this->getValue('pagesize');
		$psize =$psize?$psize:20;
		$offset = $pageid>0?($pageid-1)*$psize:0;
		
        $t -> set_var('pagelist',$this -> page('contact.php',$count,$psize,$pageid));
		$rs = $this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX."contact".$condition." LIMIT ".$offset.",".$psize);
		while ($rrs = &$rs -> FetchRow()) {
			foreach ($rrs as $k=>$v)       $t -> set_var($k,$v);
			$t -> set_var('upd',$this->getUpdStr($rrs['importer'],$rrs['contactid'],'img'));
			$t -> set_var('del',$this->getDelStr($rrs['importer'],$rrs['contactid'],'img'));
			$t -> set_var('g');

			$t -> parse('c','contact',true);
		}
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispAppend(){

		$t = new Template('../template/system');
		$t -> set_file('f','contactdetail.html');
		$t -> set_block('f','gender','g');	
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		}else{
			$updid = $_GET[MODIFY.'id'] + 0 ;
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'contact WHERE contactid = '.$updid));
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
		
			$inrs2 = &$this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX."contact where contactid =".$updid);	
			while ($inrrs2 = &$inrs2 -> FetchRow()) {
				$sex=$inrrs2['gender'];
			}
			$inrs2 -> Close();
		}

		$t -> set_var('agentid',$_GET['agentid']);
		$t -> set_var('shopid',$_GET['shopid']);
		$t -> set_var('gender',$this->gender($sex));
		$t -> parse('g','gender',true);			
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');

			
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'contact WHERE contactid in('.$delid.')');
		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'agentcontact WHERE contactid in('.$delid.')');

		exit("<script>alert('删除成功！');history.go(-1);</script>");
		//$this -> quit('删除成功！');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		if($this -> isAppend){
			$info = '增加';
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."contact(contactname,contactpass,gender,tel, handphone,address,email,loginnum,importer)values('".$_POST['contactname']."','".md5($_POST['password'])."','".$_POST['gender']."','".$_POST['tel']."','".$_POST['handphone']."','".$_POST['caddress']."','".$_POST['email']."',0,".$this->getUid().")");
			
			$id = $this -> dbObj -> Insert_ID();
			echo $_POST['agentid'];
			if ($_POST['agentid']!=""){
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."agentcontact(agentid,contactid)values('".$_POST['agentid']."','".$id."')");		
			}
			if ($_POST['shopid']!=""){
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."shopcontact(shopid,contactid)values('".$_POST['shopid']."','".$id."')");		
			
			}
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'] + 0;
			if($_POST['password']){
				$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."contact SET contactname='".$_POST['contactname']."',contactpass='".md5($_POST['password'])."' WHERE contactid = $id");
			}else{ 
				$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."contact SET contactname='".$_POST['contactname']."',gender=".$_POST["gender"].",birthday='".$_POST["birthday"]."',national='".$_POST["national"]."', idnumber='".$_POST["idnumber"]."', tel='".$_POST["tel"]."', handphone='".$_POST["handphone"]."', address='".$_POST["address"]."', zipcode='".$_POST["zipcode"]."', email='".$_POST["email"]."' WHERE contactid = $id");
			}
			if(isset($_POST['groups']))
			$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'contactgroup WHERE contactid = '.$id);
			
		}
		if(isset($_POST['groups']))
		foreach ($_POST['groups'] as $v){
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."contactgroup(contactid,groupid,importer)values($id,$v,".$this->getUid().')');
		
		}
		$this -> quit($info.'成功！');

		
	}
	function gender($sex='2'){
		$arr="";
		if($sex=='1'){
		$arr="<option value='2' >女</option><option value='1' >男</option>";
		}else
		{$arr="<option value='2' selected>女</option><option value='1'>男</option>";
		}
		
		return $arr;
	}
	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');history.go(-2);</script>");
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
$main = new Pagecontact();
$main -> Main();
?>