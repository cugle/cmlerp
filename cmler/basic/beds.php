﻿<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
	function disp(){
		//定义模板
		$t = new Template('../template/basic');
		$t -> set_file('f','beds.html');
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'beds   where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'beds  a INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on a.room_id =f.room_id  where f.room_name like '%".$keywords."%' and  a.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'beds  where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  beds_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
	    	$t -> set_var('recordcount',$count);				
	


			//设置床位
			$t -> set_var('ml');
			
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'beds  where agencyid ='.$_SESSION["currentorgan"]);
			//echo 'select * from '.WEB_ADMIN_TABPOX.'beds  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
			
				$t -> set_var('upd',$this->getUpdStr($inrrs['produre_id'],$inrrs['produre_id'],'img'));
				$t -> set_var('del',$this->getDelStr($inrrs['produre_id'],$inrrs['produre_id'],'img'));	
				$t -> set_var('room_name',$this -> dbObj -> getone('select room_name from '.WEB_ADMIN_TABPOX.'room  where room_id ='.$inrrs["room_id"]));
		        $t -> set_var('roomgroup_name',$this -> dbObj -> getone('select roomgroup_name from '.WEB_ADMIN_TABPOX.'room r inner join '.WEB_ADMIN_TABPOX.'roomgroup rg on r.roomgroup_id=rg.roomgroup_id   where r.agencyid ='.$_SESSION["currentorgan"] .' and r.room_id ='.$inrrs['room_id']));
		

			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['beds_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['beds_id']));				
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

		$t = new Template('../template/basic');
		$t -> set_file('f','beds_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		//$t -> set_block('f','gender','g');	

		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');		
				$t -> set_var('room',$this->selectlist('room','room_id','room_name',''));	
			$t -> set_var('error',"");	
			$t -> set_var('address',"");
			$t -> set_var('tel',"");
			$t -> set_var('handphone',"");
			$t -> set_var('email',"");
			$t -> set_var('showeditdiv',"");		
			$t -> set_var('beds_no',"");
			$t -> set_var('beds_name',"");
			$t -> set_var('principal',"");
			$t -> set_var('memo',"");
			$t -> set_var('bank',"");
			$t -> set_var('createtime',date("Y-m-d H:i:s"));
			$t -> set_var('userid',$this->getUid());		
		}else{
			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'beds WHERE beds_id = '.$updid));			
			$t -> set_var('error',"");	
		    $t -> set_var('showeditdiv',"");
			
			
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$inrs = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'beds WHERE beds_id = '.$updid);	
			while ($inrrs = &$inrs -> FetchRow()) {
			    
				$t -> set_var($inrrs);

				$t -> set_var('room',$this->selectlist('room','room_id','room_name',$inrrs['room_id']));

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
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'beds WHERE beds_id in('.$delid.')');
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
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."beds` ( `beds_no`, `beds_name`,`room_id`,  `memo`,  `agencyid`)VALUES ( '".$_POST["beds_no"]."', '".$_POST["beds_name"]."','".$_POST["room_id"]."', '".$_POST["memo"]."',".$_SESSION["currentorgan"].")");
			//echo "INSERT INTO `".WEB_ADMIN_TABPOX."beds` ( `beds_no`, `beds_name`,`room_id`,  `memo`,  `agencyid`)VALUES ( '".$_POST["beds_no"]."', '".$_POST["beds_name"]."','".$_POST["room_id"]."', '".$_POST["memo"]."',".$_SESSION["currentorgan"].")";
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];

			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."beds SET beds_name='".$_POST["beds_name"]."', beds_no='".$_POST["beds_no"]."', memo='".$_POST["memo"]."',room_id='".$_POST["room_id"]."' WHERE beds_id =".$id);
//echo 'UPDATE '.WEB_ADMIN_TABPOX."beds SET beds_name='".$_POST["beds_name"]."', beds_no='".$_POST["beds_no"]."', memo='".$_POST["memo"]."',room_id='".$_POST["room_id"]."' WHERE beds_id =".$id;
		}
		
		//$this -> quit($info.'成功！');
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
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
	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');location.href='beds.php';</script>");
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
  