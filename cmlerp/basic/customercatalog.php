﻿<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
    function Main()
    {
        if(isset($_GET['Action']) && $_GET['Action']=='DoAllNews')
        {
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> DoAllNews();
        }
        else
        {
            parent::Main();
        }
    }
    function DoAllNews(){

 
		$Sale_ID=$_POST[Sale_ID];
		$SubmitValue=$_POST[submit];
		$TheID=$_POST[TheID];
		$TheID=implode(",",$TheID);
		if($SubmitValue=="移至该分类"){
		$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX.'customer SET userid ='.$Sale_ID.' WHERE customerid in('.$TheID.')');
		//$this -> quit('移动成功！');
		echo "<script language=javascript>alert('移动成功！');history.go(-1);</script>";
		}


    }

	function disp(){
		//定义模板
		$t = new Template('../template/basic');
		$t -> set_file('f','customercatalog.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		

//搜索
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.'  like "%'.$keywords.'%"';}else{$condition=$category.' like "%'.$keywords.'%"';}
		}
//分页		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:20;
			$offset = $pageid>0?($pageid-1)*$psize:0;

			//设置分类
			
			
			
			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'customercatalog   where  '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'customercatalog  r INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on r.customercatalog_id =f.customercatalog_id where f.roomgroup_name like '%".$keywords."%' " ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'customercatalog '   ;
			 
			}
			 
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  customercatalog_id DESC  LIMIT ".$offset." , ".$psize);
					
			$result = &$this -> dbObj -> Execute($sql);	
				
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
				
			
			
			
			
			
			//echo $sql." ORDER BY  customercatalog DESC  LIMIT ".$offset." , ".$psize;
			
			$t -> set_var('ml');
			
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'customercatalog  where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
			   		$t -> set_var('delete',$this -> getDelStr('',$inrrs['customercatalog']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['customercatalog_id']));
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
		$t -> set_file('f','customercatalog_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	


		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		$t -> set_var('bgdate',"");	
		$t -> set_var('enddate',"");	
		$t -> set_var('ismember',"");	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('memo',"");
		$t -> set_var('customercatalog_name',"");
		$t -> set_var('userid',$this->getUid());		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'customercatalog WHERE customercatalog_id = '.$updid));			
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$inrs = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'customercatalog WHERE customercatalog_id = '.$updid);	
			while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				if($inrrs['limitdate']==1){
				$t -> set_var('limitdate',"checked");
				}else{
				$t -> set_var('limitdate',"");
				}
				if($inrrs['ismember']==1){
				$t -> set_var('ismember',"checked");
				}else{
				$t -> set_var('ismember',"");
				}
				
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
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'customercatalog WHERE customercatalog_id in('.$delid.')');
		$info='删除';
		//echo 'DELETE FROM '.WEB_ADMIN_TABPOX.'customercatalog WHERE customercatalog_id in('.$delid.')';
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
			if($_POST["ismember"]=='on'){$_POST["ismember"]=1;}else{$_POST["ismember"]=0;}
			if($_POST["limitdate"]=='on'){$limitdate=1;}else{$limitdate=0;}
			//echo "INSERT INTO `".WEB_ADMIN_TABPOX."customercatalog` (`customercatalog_name` ,`memo` ,`agencyid` )VALUES ( '".$_POST["customercatalog_name"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."')";
			$enddate=$_POST['enddate']==''?NULL:$_POST['enddate'];
			$bgdate=$_POST['bgdate']==''?NULL:$_POST['bgdate'];
			if($_POST['enddate']==''||$_POST['bgdate']==''){
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."customercatalog` (`customercatalog_name` ,ismember,`memo` ,`bgdate`,`enddate`,limitdate,`agencyid` )VALUES ( '".$_POST["customercatalog_name"]."','".$_POST["ismember"]."','".$_POST["memo"]."',null,null,'".$limitdate."','".$_SESSION["currentorgan"]."')");
			}else{
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."customercatalog` (`customercatalog_name` ,ismember,`memo` ,`bgdate`,`enddate`,limitdate,`agencyid` )VALUES ( '".$_POST["customercatalog_name"]."','".$_POST["ismember"]."','".$_POST["memo"]."','".$bgdate."','".$enddate."','".$limitdate."','".$_SESSION["currentorgan"]."')");
			 
			}

			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			if($_POST["ismember"]=='on'){$_POST["ismember"]=1;}else{$_POST["ismember"]=0;}
			if($_POST["limitdate"]=='on'){$limitdate=1;}else{$limitdate=0;}
			$enddate=$_POST['enddate']==''?NULL:$_POST['enddate'];
			$bgdate=$_POST['bgdate']==''?NULL:$_POST['bgdate'];
			echo "test";
			if($_POST['enddate']==''||$_POST['bgdate']==''){
			
			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."customercatalog SET customercatalog_name='".$_POST["customercatalog_name"]."',ismember='".$_POST["ismember"]."',bgdate=null,enddate=null,limitdate='".$limitdate."', memo='".$_POST["memo"]."' WHERE customercatalog_id=".$id);	
			
			}else{
			
			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."customercatalog SET customercatalog_name='".$_POST["customercatalog_name"]."',ismember='".$_POST["ismember"]."',bgdate='".$bgdate."',enddate='".$enddate."',limitdate='".$limitdate."', memo='".$_POST["memo"]."' WHERE customercatalog_id=".$id);
			
			}
			 
			//echo 'UPDATE '.WEB_ADMIN_TABPOX."customercatalog SET customercatalog_name='".$_POST["customercatalog_name"]."',ismember='".$_POST["ismember"]."', memo='".$_POST["memo"]."' WHERE customercatalog_id=".$id;
//echo 'UPDATE '.WEB_ADMIN_TABPOX."customercatalog SET customercatalog_name='".$_POST["customercatalog_name"]."', memo='".$_POST["memo"]."' WHERE customercatalog_id=".$id;
		}
//$this -> quit($info.'成功！');
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
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
		exit("<script>alert('$info');location.href='customercatalog.php';</script>");
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
  