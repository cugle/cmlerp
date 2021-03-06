﻿<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
    function Main()
    {
        if(isset($_GET['action']) && $_GET['action']=='view')
        {  
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> view();
        }
        else
        { 
            parent::Main();
        }
    }
    function view(){
		//定义模板
		$t = new Template('../template/news');
		$t -> set_file('f','notice_detail.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','news','a');	
		$id = $_GET['updid'];
		
		$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'news WHERE news_id = '.$id);
		$t -> set_var($data);
			$employee=$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'user A INNER JOIN '.WEB_ADMIN_TABPOX.'employee B ON A.employee_id=B.employee_id  where A.userid = '.$this->getUid());
			$t -> set_var('employee_id',$employee['employee_id']);
			$t -> set_var('employee_name',$employee['employee_name']);
			//$t -> set_var('newstype',$this->selectlist('newstype','newstype_id','newstype_name',$data['type']));
			$t -> set_var('newstype',$this -> dbObj -> GetOne('select newstype_name from '.WEB_ADMIN_TABPOX.'newstype where newstype_id='.$data['type']));
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."usernews` ( `news_id`, `user_id`, `status`)VALUES ( '".$id."', '".$this->getUid()."', 1)");
			 
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('error',"");		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');

    }
		function selectlist($table,$id,$name,$selectid=0){
	
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table);
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
	function disp(){
		//定义模板
		$t = new Template('../template/news');
		$t -> set_file('f','notice.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','news','a');		
 		
			$t -> set_var('add',$this -> getAddStr('img'));
 
			$sql='select * from '.WEB_ADMIN_TABPOX.'news order by createtime desc  ';
 			$news=$this -> dbObj -> GetRow($sql);
			$t -> set_var($news);
 
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	
	function goDispAppend(){

		$t = new Template('../template/news');
		$t -> set_file('f','news_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		//$t -> set_block('f','gender','g');	

		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');		
			$t -> set_var('type',$this->newstype());	
			$t -> set_var('error',"");	
			$t -> set_var('showeditdiv',"");		
			$t -> set_var('news_no',"");
			$t -> set_var('news_name',"");
			$t -> set_var('principal',"");
			$t -> set_var('memo',"");
			$t -> set_var('bank',"");
			$t -> set_var('createtime',date("Y-m-d H:i:s"));
			$t -> set_var('userid',$this->getUid());		
		}else{
			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'news WHERE news_id = '.$updid));			
			$t -> set_var('error',"");	
			$t -> set_var('showeditdiv',"");
			
			
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$inrs = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'news WHERE news_id = '.$updid);	
			while ($inrrs = &$inrs -> FetchRow()) {
			    
				$t -> set_var($inrrs);
				$t -> set_var('type',$this->newstype($inrrs['type']));

			}
			$inrs -> Close();
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
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'news WHERE news_id in('.$delid.')');
		$this -> quit('删除成功！');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		
		if($this -> isAppend){
			$info = '增加';	
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."news` (`news_name` ,`news_no` ,`memo` ,`agencyid` ,`principal` ,`bank` ,`type` )VALUES ( '".$_POST["news_name"]."', '".$_POST["news_no"]."',  '".$_POST["memo"]."','".$_SESSION["currentorgan"]."', '".$_POST["principal"]."','".$_POST["bank"]."', '".$_POST["type"]."')");
			//echo "INSERT INTO `".WEB_ADMIN_TABPOX."news` (`news_name` ,`news_no` ,`balance` ,`memo` ,`agencyid` ,`principal` ,`bank` ,`type` )VALUES ( '".$_POST["news_name"]."', '".$_POST["news_no"]."', '".$_POST["balance"]."', '".$_POST["memo"]."','".$_POST["agencyid"]."', '".$_POST["principal"]."','".$_POST["bank"]."', '".$_POST["type"]."')";
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];

			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."news SET news_name='".$_POST["news_name"]."', news_no='".$_POST["news_no"]."', memo='".$_POST["memo"]."', principal='".$_POST["principal"]."',bank='".$_POST["bank"]."', type='".$_POST["type"]."' WHERE news_id =".$id);

		}
		exit("<script>location.href='news.php';</script>");
		//$this -> quit($info.'成功！');
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
	function newstype($type='0'){
		$arr="";
		if($type=='1'){
		$arr="<option value='1' selected>现金</option><option value='2' >银行</option>";
		}else if($type=='2')
		{$arr="<option value='1' >现金</option><option value='2' selected>银行</option>";
		}else
		{
		$arr="<option value='1' >现金</option><option value='2'>银行</option>";
		}
		return $arr;
	}
	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');location.href='news.php';</script>");
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
  