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
		$t -> set_file('f','agency.html');
		$t -> set_block('f','agency','a');
		$t -> set_var('add',$this -> getAddStr('img'));
		
			//设置机构
			
			$t -> set_var('a');
			$inrs = &$this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX."agency");
		
			while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('upd',$this->getUpdStr($inrrs['agency_id'],$inrrs['agency_id'],'img'));
				$t -> set_var('del',$this->getDelStr($inrrs['agency_id'],$inrrs['agency_id'],'img'));
				
	            $t -> set_var('agencytype_name',$this -> dbObj -> getone('select agencytype_name from '.WEB_ADMIN_TABPOX.'agencytype  where agencytype_id='.$inrrs["agencytype_id"]));	
				$t -> parse('a','agency',true);
			}
			$inrs -> Close();	

        $t -> set_var('pagelist',$this -> page("agent.php?qgtype=".$_GET[qgtype]."&Sale_ID=".$_GET[Sale_ID],$count,$psize,$pageid));		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	
	function goDispAppend(){
		$t = new Template('../template/system');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[##
        $t->right_delimiter = "#]"; //修改右边界符##]
		$t -> set_file('f','agencydetail.html');
		$t -> set_var('error',"");		
		$t -> set_var('showeditdiv',"");			
		if($this -> isAppend){
		$t -> set_var('agency_id',"");
		$t -> set_var('agency_no',"");
		
		$t -> set_var('agency_easyname',"");
		$t -> set_var('agency_name',"");
		$t -> set_var('tel1',"");
		$t -> set_var('opendate',date("Y-m-d"));
		$t -> set_var('agencytype_id',"");
		$t -> set_var('tel1',"");
		$t -> set_var('principal',"");
		$t -> set_var('mobile',"");
		$t -> set_var('memo',"");
		$t -> set_var('action','add');
		$t -> set_var('actionName','增加');
		}else{
			$updid = $_GET[MODIFY.'id'] + 0 ;
			//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'agency WHERE agency_id = '.$updid;
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'agency WHERE agency_id = '.$updid);
			$t -> set_var($data);
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');

		}
		$t -> set_var('xjaccounttitle_idlist',$this->selectlist('accounttitle','accounttitle_id','accounttitle_name',$data['xjaccounttitle_id'],' and accounttitle_name like "现金%"'));
		$t -> set_var('ykaccounttitle_idlist',$this->selectlist('accounttitle','accounttitle_id','accounttitle_name',$data['ykaccounttitle_id'],' and accounttitle_name like "银行存款%"'));
		$t -> set_var('agencynamelist',$this ->selectlist('agencytype','agencytype_id','agencytype_name',$data['agencytype_id']));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}	
	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
	
	
		$delid = $_GET[DELETE.'id'] + 0;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'agency WHERE agency_id in('.$delid.')');
		$this -> quit('删除成功！');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		if($this -> isAppend){
			$info = '增加';

			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."agency(`agency_no`, `agency_easyname`, `agency_name`, `agencytype_id`,`tel1`, `opendate`,`principal`, `mobile`,  `memo`,xjaccounttitle_id,ykaccounttitle_id) VALUES('".$_POST['agency_no']."','".$_POST['agency_easyname']."','".$_POST['agency_name']."','".$_POST['agencytype_id']."','".$_POST['tel1']."','".$_POST['opendate']."','".$_POST['principal']."','".$_POST['mobile']."','".$_POST['memo']."','".$_POST['xjaccounttitle_id']."','".$_POST['ykaccounttitle_id']."')");
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'] + 0;
			$this -> dbObj -> Execute('update '.WEB_ADMIN_TABPOX."agency SET agency_easyname='".$_POST['agency_easyname']."',agency_no='".$_POST['agency_no']."',agencytype_id=".$_POST['agencytype_id'].",agency_name='".$_POST['agency_name']."',tel1='".$_POST['tel1']."',opendate='".$_POST['opendate']."',principal='".$_POST['principal']."',mobile='".$_POST['mobile']."',memo='".$_POST['memo']."',xjaccounttitle_id='".$_POST['xjaccounttitle_id']."',ykaccounttitle_id='".$_POST['ykaccounttitle_id']."' WHERE agency_id =".$id);
			echo 'update '.WEB_ADMIN_TABPOX."agency SET agency_easyname='".$_POST['agency_easyname']."',agency_no='".$_POST['agency_no']."',agencytype_id=".$_POST['agencytype_id'].",agency_name='".$_POST['agency_name']."',tel1='".$_POST['tel1']."',opendate='".$_POST['opendate']."',principal='".$_POST['principal']."',mobile='".$_POST['mobile']."',memo='".$_POST['memo']."',xjaccounttitle_id='".$_POST['xjaccounttitle_id']."',ykaccounttitle_id='".$_POST['ykaccounttitle_id']."' WHERE agency_id =".$id;
			}
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}
	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');location.href='agency.php';</script>");
	}
	function intnonull($int){
	if ($int=="")$int=0;
		return $int;
	}
	

		function selectlist($table,$id,$name,$selectid=0,$condition=''){
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table .' where agencyid ='.$_SESSION["currentorgan"].' '.$condition);
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