<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
	function disp(){
		//定义模板
		$t = new Template('../template/customer');
		$t -> set_file('f','customer.html');
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'customer  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'customer p INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on p.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  p.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'customer A LEFT join '.WEB_ADMIN_TABPOX.'customercatalog B ON A.customercatalog_id=B.customercatalog_id where  A.agencyid  ='.$_SESSION["currentorgan"]."";
			 // AND (('".date('Y-m-d',time())."' >bgdate	and '".date('Y-m-d',time())."'<= enddate)  or limitdate=1 ) or limitdate=NULL;
			}
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  customer_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
           	$t -> set_var('recordcount',$count);
						
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'produce  where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('gender',$inrrs["genderid"]==1?'男':'女');
				$t -> set_var('category_name',$this -> dbObj -> getone('select category_name from '.WEB_ADMIN_TABPOX.'procatalog where category_id ='.$inrrs["categoryid"]));
				$t -> set_var('vrcount',$this -> dbObj -> getone('select count(*) from '.WEB_ADMIN_TABPOX.'visiterecord where customer_id ='.$inrrs["customer_id"]." and agencyid =".$_SESSION["currentorgan"]));	
				$t -> set_var('mrcount',$this -> dbObj -> getone('select count(*) from '.WEB_ADMIN_TABPOX.'messagerecord where customer_id ='.$inrrs["customer_id"]." and agencyid =".$_SESSION["currentorgan"]));	
				//echo 'select count(*) from '.WEB_ADMIN_TABPOX.'messagerecord where customer_id ='.$inrrs["customer_id"]." and agencyid =".$_SESSION["currentorgan"];		
				$t -> set_var('customercatalog_name',$this -> dbObj -> getone('select customercatalog_name from '.WEB_ADMIN_TABPOX.'customercatalog where customercatalog_id ='.$inrrs["customercatalog_id"]));	
				
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['customer_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['customer_id']));				
				
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();
		$t -> set_var('customercataloglist',$this ->selectlist('customercatalog','customercatalog_id','customercatalog_name',''));
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispAppend(){

		$t = new Template('../template/customer');
		$t -> set_file('f','customer_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	

		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		$Prefix='gk';
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'customer';
		$column='customer_no';
		$number=5;
		$id='customer_id';	
		$t -> set_var('updid',"");
		$t -> set_var('customer_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));	
		$t -> set_var('customer_name',"");	
		$t -> set_var('idnumber',"");	
		$t -> set_var('email',"");	
		$t -> set_var('handphone',"");	
		$t -> set_var('qq',"");	
		$t -> set_var('zipcode',"");	
		$t -> set_var('tel',"");	
		$t -> set_var('address',"");	
		$t -> set_var('birthday',"");	
		$t -> set_var('price',"");	
		$t -> set_var('efficacy',"");
		$t -> set_var('useway',"");
		$t -> set_var('basis',"");	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('memo',"");
		$t -> set_var('province',"广东");
		$t -> set_var('city',"广州");
		$t -> set_var('yufukuan',"");
		$t -> set_var('userid',$this->getUid());	
		$t -> set_var('picurl',"暂时没有照片");
		$t -> set_var('picpath',"");	
		$t -> set_var('birthday','');
		$t -> set_var('customercataloglist',$this ->selectlist('customercatalog','customercatalog_id','customercatalog_name',$data['customercatalog_id']));	
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'customer WHERE customer_id = '.$updid);
			$t -> set_var($data);
			if ($data['picpath']==''){
			
			$t -> set_var('picurl',"暂时没有照片");	
			}else{	
			$t -> set_var('picurl',$data['picpath']);	
			$t -> set_var('picurl',"<img src=".$data['picpath']." width=120 height=150 />");
			}	
			$t -> set_var('customercataloglist',$this ->selectlist('customercatalog','customercatalog_id','customercatalog_name',$data['customercatalog_id']));	
			$t -> set_var('ismarrycheck1',$data['ismarry']==1?'checked':'');	
			$t -> set_var('ismarrycheck2',$data['ismarry']==2?'checked':'');	
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');

		}
if ($date['state']==1){

		$t -> set_var('state','<input id="state" type="radio" checked="checked" value="1" name="state" /><label for="state">可用</label><input id="state" type="radio" value="0" name="state" /><label for="state">停用</label>');
}else
{
		$t -> set_var('state','<input id="state" type="radio"  value="1" name="state" /><label for="state_0">可用</label><input id="state" type="radio" value="0" checked="checked" name="state" /><label for="state">停用</label>');
}
		//$t -> set_var('brandlist',$this ->selectlist('brand','brand_id','brand_name',$data['brandid']));
		//$t -> set_var('genderlist',"111");	
		//echo $data['genderid'];
		$t -> set_var('genderlist',$this -> gender($data['genderid']));	
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));						

		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}
	
	
		function gender($selectid=2){
			$str='';
			if ($selectid==1)
			$str ="<option value='1' selected>男</option><option value='2'>女</option>";	
			else
			$str ="<option value='1'>男</option><option value='2'  selected>女</option>";			
			return  $str;	
	    }	
	
		function selectlist($table,$id,$name,$selectid=0){
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table." where  ('".date('Y-m-d',time())."' >bgdate	and '".date('Y-m-d',time())."'<= enddate)  or limitdate=1"  );
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
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'customer WHERE customer_id in('.$delid.')');
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
		$Prefix='gk';
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'customer';
		$column='customer_no';
		$number=5;
		$id='customer_id';	
		$customer_no=$this->makeno($Prefix,$agency_no,$table,$column,$number,$id);
		if($_POST['birthday']<>''){
		$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."customer` (`customer_no`, `customer_name`, `address`, `genderid`, `birthday`,  `idnumber`, `tel`, `handphone`,  `zipcode`, `email`,  `memo`,  `agencyid`, `picpath`,ismarry,province,city,qq,customercatalog_id)VALUES ( '".$customer_no."','".$_POST["customer_name"]."', '".$_POST["address"]."','".$_POST["genderid"]."', '".$_POST["birthday"]."', '".$_POST["idnumber"]."','".$_POST["tel"]."', '".$_POST["handphone"]."','".$_POST["zipcode"]."', '".$_POST["email"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["picpath"]."','".$this->intnonull($_POST["ismarry"])."', '".$_POST["province"]."','".$_POST["city"]."','".$_POST["qq"]."','".$_POST["customercatalog_id"]."')");
		}else{
		$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."customer` (`customer_no`, `customer_name`, `address`, `genderid`,  `idnumber`, `tel`, `handphone`,  `zipcode`, `email`,  `memo`,  `agencyid`, `picpath`,ismarry,province,city,qq,customercatalog_id)VALUES ( '".$customer_no."','".$_POST["customer_name"]."', '".$_POST["address"]."','".$_POST["genderid"]."', '".$_POST["idnumber"]."','".$_POST["tel"]."', '".$_POST["handphone"]."','".$_POST["zipcode"]."', '".$_POST["email"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["picpath"]."','".$this->intnonull($_POST["ismarry"])."', '".$_POST["province"]."','".$_POST["city"]."','".$_POST["qq"]."','".$_POST["customercatalog_id"]."')");
		}
//echo "INSERT INTO `".WEB_ADMIN_TABPOX."customer` (`customer_no`, `customer_name`, `address`, `genderid`, `birthday`,  `idnumber`, `tel`, `handphone`,  `zipcode`, `email`,  `memo`,  `agencyid`, `picpath`,ismarry,province,city)VALUES ( '".$_POST["customer_no"]."','".$_POST["customer_name"]."', '".$_POST["address"]."','".$_POST["genderid"]."', '".$_POST["birthday"]."', '".$_POST["idnumber"]."','".$_POST["tel"]."', '".$_POST["handphone"]."','".$this->intnonull($_POST["zipcode"])."', '".$_POST["email"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["picpath"]."','".$this->intnonull($_POST["ismarry"])."', '".$_POST["province"]."','".$_POST["city"]."')";
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$_POST["yufukuan"]=$_POST["yufukuan"]==''?0.00:$_POST["yufukuan"];
			$_POST["dingjin"]=$_POST["dingjin"]==''?0.00:$_POST["dingjin"];
			
			if($_POST['birthday']<>''){
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."customer` SET `customer_name` = '".$_POST["customer_name"]."',`customer_no` = '".$_POST["customer_no"]."',`idnumber` = '".$_POST["idnumber"]."',`tel` = '".$_POST["tel"]."',`handphone` ='".$_POST["handphone"]."',`email` = '".$_POST["email"]."',`address` = '".$_POST["address"]."',`zipcode` = '".$_POST["zipcode"]."',`birthday` = '".$_POST["birthday"]."',`genderid` = '".$this->intnonull($_POST["genderid"])."', picpath='".$_POST["picpath"]."',ismarry=".$this->intnonull($_POST['ismarry']).", province='".$_POST["province"]."', city='".$_POST["city"]."', qq='".$_POST["qq"]."', memo='".$_POST["memo"]."', yufukuan ='".$_POST["yufukuan"]."',dingjin='".$_POST["dingjin"]."',customercatalog_id='".$_POST["customercatalog_id"]."' WHERE customer_id =".$id);
			}else{
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."customer` SET `customer_name` = '".$_POST["customer_name"]."',`customer_no` = '".$_POST["customer_no"]."',`idnumber` = '".$_POST["idnumber"]."',`tel` = '".$_POST["tel"]."',`handphone` ='".$_POST["handphone"]."',`email` = '".$_POST["email"]."',`address` = '".$_POST["address"]."',`zipcode` = '".$_POST["zipcode"]."',`birthday` = NULL,`genderid` = '".$this->intnonull($_POST["genderid"])."', picpath='".$_POST["picpath"]."',ismarry=".$this->intnonull($_POST['ismarry']).", province='".$_POST["province"]."', city='".$_POST["city"]."', qq='".$_POST["qq"]."', memo='".$_POST["memo"]."', yufukuan ='".$_POST["yufukuan"]."',dingjin='".$_POST["dingjin"]."',customercatalog_id='".$_POST["customercatalog_id"]."' WHERE customer_id =".$id);
			//echo "UPDATE `".WEB_ADMIN_TABPOX."customer` SET `customer_name` = '".$_POST["customer_name"]."',`customer_no` = '".$_POST["customer_no"]."',`idnumber` = '".$_POST["idnumber"]."',`tel` = '".$_POST["tel"]."',`handphone` ='".$_POST["handphone"]."',`email` = '".$_POST["email"]."',`address` = '".$_POST["address"]."',`zipcode` = '".$_POST["zipcode"]."',`birthday` = NULL,`genderid` = '".$this->intnonull($_POST["genderid"])."', picpath='".$_POST["picpath"]."',ismarry=".$this->intnonull($_POST['ismarry']).", province='".$_POST["province"]."', city='".$_POST["city"]."', qq='".$_POST["qq"]."', memo='".$_POST["memo"]."', yufukuan ='".$_POST["yufukuan"]."',customercatalog_id='".$_POST["customercatalog_id"]."' WHERE customer_id =".$id;
			}
//echo "UPDATE `".WEB_ADMIN_TABPOX."customer` SET `customer_name` = '".$_POST["customer_name"]."',`customer_no` = '".$_POST["customer_no"]."',`idnumber` = '".$_POST["idnumber"]."',`tel` = '".$this->intnonull($_POST["tel"])."',`handphone` ='".$this->intnonull($_POST["handphone"])."',`email` = '".$_POST["email"]."',`address` = '".$_POST["address"]."',`zipcode` = '".$this->intnonull($_POST["zipcode"])."',`birthday` = '".$_POST["birthday"]."',`genderid` = '".$this->intnonull($_POST["genderid"])."', picpath='".$_POST["picpath"]."',ismarry=".$this->intnonull($_POST['ismarry']).", province='".$_POST["province"]."', city='".$_POST["city"]."' WHERE customer_id =".$id;
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
		exit("<script>alert('$info');location.href='customer.php';</script>");
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
  