<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
	function disp(){
		//定义模板
		$t = new Template('../template/basic');
		$t -> set_file('f','lookupcard.html');
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'lookupcard    where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;
			
			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'lookupcard   a INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on a.categoryid =f.lookupcard_id  where f.lookupcard_name like '%".$keywords."%' and  a.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'lookupcard   where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
			 
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  lookupcard_id  DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
	    	$t -> set_var('recordcount',$count);		


	
			//设置分类
			$t -> set_var('ml');
			
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'lookupcard where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				if($inrrs['parentid']==0){
					$parentname='根类';
				}else{	
					$parentnamedata= $this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'lookupcard   where  lookupcard_id ='.$inrrs["parentid"]);
					$parentname=$parentnamedata['lookupcard_name'];
				}
				$t -> set_var('parentname',$parentname);
				
			  	 $t -> set_var('delete',$this -> getDelStr('',$inrrs['lookupcard_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['lookupcard_id']));					   
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
		$t -> set_file('f','lookupcard_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	


		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		$t -> set_var('lookupcardlist',$this ->selectlist('lookupcard','lookupcard_id','lookupcard_name',''));	
		$t -> set_var('lookupcard_name',""); 
		$t -> set_var('lookupcard_no',"");	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('memo',"");
		$t -> set_var('orderid','');	
		$t -> set_var('type','<label><input type="radio" name="type" id="type" value="1" checked>计算数量</label><label> <input type="radio" name="type" id="type" value="0">计算金额</label>');
		$temstr='';
		$accounttitle = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcard A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcardtype B ON A.marketingcardtype_id=B.marketingcardtype_id where A.agencyid=1 order by A.marketingcardtype_id ');	
			while ($inrrsaccounttitle = $accounttitle -> FetchRow()) {
				 
 
				 
				$temstr=$temstr.'<br>&nbsp;&nbsp;<label><input type="checkbox" name="cardlist[]" id="cardlist" value="'.$inrrsaccounttitle["marketingcard_id"].'"  onClick="showaddorreduce(this)">'.$inrrsaccounttitle["marketingcardtype_name"].' '.$inrrsaccounttitle["marketingcard_name"].'</label>';	
				  
				$temstr=$temstr.'<span id="'.$inrrsaccounttitle["accounttitle_id"].'" style="display:none"><label>&nbsp;&nbsp;<input type="checkbox" name="addorreducelist[]" id="addorreducelist" value="'.$inrrsaccounttitle["accounttitle_id"].'">-</label></span>';
				
				
				
			}
			$accounttitle -> Close();		
 		$t -> set_var('cardlist',$temstr);
		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$lookupcarddata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'lookupcard WHERE lookupcard_id = '.$updid);
			$lookupcarddata["select"]=0;
			$cardidlist=$lookupcarddata['cardidlist'];
			$cardidlist=explode(",",$cardidlist); 
				for($i=0;$i<count($cardidlist);$i++){
				$select[$cardidlist[$i]]= 1;
				 
				}
			 
			 
		 
			$t -> set_var($lookupcarddata);			
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');

			
			
			$t -> set_var('lookupcardlist',$this ->selectlist('lookupcard','lookupcard_id','lookupcard_name',$lookupcarddata['parentid']));	
			$t -> set_var('actionName','修改');
			$inrs = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'lookupcard WHERE lookupcard_id = '.$updid);	
			while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				//$t -> set_var('lookupcard_no',$inrrs['lookupcard_no']);
			}
			$inrs -> Close();
		$temstr='';
		$accounttitle = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcard A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcardtype B ON A.marketingcardtype_id=B.marketingcardtype_id where A.agencyid=1 order by A.marketingcardtype_id   ');	
		 
			while ($inrrsaccounttitle = $accounttitle -> FetchRow()) {
			 
				
				if($select[$inrrsaccounttitle["marketingcard_id"]]==1){
 
					
				$temstr=$temstr.'<br/>&nbsp;&nbsp;<label><input type="checkbox" name="cardlist[]" id="cardlist" value="'.$inrrsaccounttitle["marketingcard_id"].'" checked  onClick="showaddorreduce(this)">'.$inrrsaccounttitle["marketingcardtype_name"].' '.$inrrsaccounttitle["marketingcard_name"].'</label>';
 
				
 				
				
				}else{
				 
				$temstr=$temstr.'<br>&nbsp;&nbsp;<label><input type="checkbox" name="cardlist[]" id="cardlist" value="'.$inrrsaccounttitle["marketingcard_id"].'"  onClick="showaddorreduce(this)">'.$inrrsaccounttitle["marketingcardtype_name"].' '.$inrrsaccounttitle["marketingcard_name"].'</label>';	
 
				}
				
/*				if($addorreduce[$inrrsaccounttitle["accounttitle_id"]]==0){
				$temstr=$temstr.'<span id="'.$inrrsaccounttitle["accounttitle_id"].'" style="display:none"><label>&nbsp;&nbsp;<input type="checkbox" name="addorreducelist[]" id="addorreducelist" value="'.$inrrsaccounttitle["accounttitle_id"].'" >-</label></span>';
				}else{
				$temstr=$temstr.'<span id="'.$inrrsaccounttitle["accounttitle_id"].'" ><label>&nbsp;&nbsp;<input type="checkbox" name="addorreducelist[]" id="addorreducelist" value="'.$inrrsaccounttitle["accounttitle_id"].'" checked>-</label></span>';
				}*/
			}
			$accounttitle -> Close();		
 		$t -> set_var('cardlist',$temstr);
		
			if($lookupcarddata['type']==0){
			$t -> set_var('type','<label><input type="radio" name="type" id="type" value="1" >计算数量</label><label> <input type="radio" name="type" id="type" value="0" checked>计算金额</label>');
			}else{
			$t -> set_var('type','<label><input type="radio" name="type" id="type" value="1" checked>计算数量</label><label> <input type="radio" name="type" id="type" value="0">计算金额</label>');	
			}
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
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'lookupcard  WHERE lookupcard_id in('.$delid.')');
	 
		$info='删除';
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		$cardlist=$_POST["cardlist"];
		for($i=0;$i<count($cardlist);$i++){
			$cardliststr=$cardliststr==''?$cardlist[$i]:$cardliststr.",".$cardlist[$i];	
			}
		$addorreducelist=$_POST["addorreducelist"];
		for($i=0;$i<count($addorreducelist);$i++){
			$addorreduceliststr=$addorreduceliststr==''?$addorreducelist[$i]:$addorreduceliststr.",".$addorreducelist[$i];	
			}	
		if($this -> isAppend){
			$info = '增加';	
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."lookupcard` (`lookupcard_name` ,`agencyid` ,cardidlist,type,orderid)VALUES ( '".$_POST["lookupcard_name"]."',  '".$_SESSION["currentorgan"]."', '".$cardliststr."', '".$_POST["type"]."',  '".$_POST['orderid']."')");
 echo "INSERT INTO `".WEB_ADMIN_TABPOX."lookupcard` (`lookupcard_name` ,`agencyid` ,cardidlist,type,orderid)VALUES ( '".$_POST["lookupcard_name"]."',  '".$_SESSION["currentorgan"]."', '".$cardliststr."', '".$_POST["type"]."',  '".$_POST['orderid']."')";
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."lookupcard SET lookupcard_name='".$_POST["lookupcard_name"]."',   cardidlist='".$cardliststr."' ,type= '".$_POST["type"]."', orderid='".$_POST["orderid"]."' WHERE lookupcard_id =".$id);
			 
			//echo 'UPDATE '.WEB_ADMIN_TABPOX."lookupcard SET lookupcard_name='".$_POST["lookupcard_name"]."',  memo='".$_POST["memo"]."',lookupcard_no='".$_POST["lookupcard_no"]."', cardidlist='".$cardliststr."',type= '".$_POST["type"]."',addorreduce='".$addorreduceliststr."' WHERE lookupcard_id =".$id;
			//echo 'UPDATE '.WEB_ADMIN_TABPOX."lookupcard SET lookupcard_name='".$_POST["lookupcard_name"]."',  memo='".$_POST["memo"]."',lookupcard_no='".$_POST["lookupcard_no"]."', cardidlist='".$cardliststr."',type= '".$_POST["type"]."' WHERE lookupcard_id =".$id;

		}
//$this -> quit($info.'成功！');
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
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

	
		function selectlist($table,$id,$name,$selectid=0){
	
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table .' ');
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
		exit("<script>alert('$info');history.go(-1);;</script>");
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
  