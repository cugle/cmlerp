<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
    function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='import')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> import();
        }else if(isset($_GET['action']) && $_GET['action']=='export'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> export();			
		}else{
            parent::Main();
        }
    }	
	
	function import(){
		//定义模板
		$t = new Template('../template/basic');
		$t -> set_file('f','import.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
$fname = mb_convert_encoding($_FILES['MyFile']['name'],'utf-8','gbk'); 
$do = copy(mb_convert_encoding($_FILES['MyFile']['tmp_name'],'utf-8','gbk'),$fname); 
if(mb_convert_encoding($_FILES['MyFile']['name'],'utf-8','gbk')){
if ($do) 
{ 
  $error= '导入成功，请返回查看';
} else { 
$error= "导入失败"; 
}}
error_reporting(0); 				
$fname = mb_convert_encoding($_FILES['MyFile']['name'],'utf-8','gbk'); 
$handle=fopen($fname,"r"); 
$i=1;
while($data=fgetcsv($handle,10000,",")) 
{
if($i>1&&$data[1]<>''){
$this -> dbObj -> Execute("SET NAMES  'utf8'"); 

$data[2]=$this -> dbObj -> getone("SELECT category_id FROM `".WEB_ADMIN_TABPOX."procatalog` WHERE category_name='".$data[2]."' and agencyid=".$_SESSION["currentorgan"]) ;

$data[3]=$this -> dbObj -> getone("SELECT brand_id FROM `".WEB_ADMIN_TABPOX."brand` WHERE brand_name  like '".$data[3]."%' and agencyid=".$_SESSION["currentorgan"]) ;
$data[4]=$this -> dbObj -> getone("SELECT unit_id FROM `".WEB_ADMIN_TABPOX."unit` WHERE unit_name='".$data[4]."' and agencyid=".$_SESSION["currentorgan"]) ;

$data[5]=$this -> dbObj -> getone("SELECT unit_id FROM `".WEB_ADMIN_TABPOX."unit` WHERE unit_name='".$data[5]."' and agencyid=".$_SESSION["currentorgan"]) ;

$produce_id='';
$produce_id=$this -> dbObj ->GetOne("SELECT produce_id FROM `".WEB_ADMIN_TABPOX."produce` WHERE code ='".$data[10]."' and agencyid=".$_SESSION['currentorgan']);
if($data[10]=='' or $produce_id){$produce_id=$this -> dbObj ->GetOne("SELECT produce_id FROM `".WEB_ADMIN_TABPOX."produce` WHERE produce_name ='".$data[1]."' and agencyid=".$_SESSION['currentorgan']);
//echo "SELECT produce_id FROM `".WEB_ADMIN_TABPOX."produce` WHERE produce_name ='".$data[1]."' and agencyid=".$_SESSION['currentorgan'];
}
if($produce_id==''){
		$data[5]=$data[5]?$data[5]:0; 
		$data[2]=$data[2]?$data[2]:0; 
		$data[3]=$data[3]?$data[3]:0; 
		$data[4]=$data[4]?$data[4]:0; 	
$q="insert into s_produce  (`produce_no`,produce_name,`categoryid`,  `brandid`, `standardunit`,`viceunit`,`viceunitnumber`, `price`, `upperlimit`, `lowerlimit`, `code`, `shortcode`, `address`, `efficacy`, `useway`, `basis`, memo,agencyid) values ('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]','$data[5]','$data[6]','$data[7]','$data[8]','$data[9]','$data[10]','$data[11]','$data[12]','$data[13]','$data[14]','$data[15]','$data[16]',".$_SESSION["currentorgan"].")"; 

 $this -> dbObj -> Execute($q) ;
 }else {
	 if($data[10]<>''){
		$data[5]=$data[5]?$data[5]:0; 
		$data[2]=$data[2]?$data[2]:0; 
		$data[3]=$data[3]?$data[3]:0; 
		$data[4]=$data[4]?$data[4]:0; 
 		$q="update ".WEB_ADMIN_TABPOX."produce set `produce_no`='".$data[0]."',produce_name='".$data[1]."',categoryid='".$data[2]."',standardunit='".$data[4]."' ,brandid='".$data[3]."' ,price='".$data[7]."',viceunit='".$data[5]."' ,shortcode='".$data[11]."' ,memo ='".$data[16]."' where code='".$data[10]."'";  
		 //$q="update ".WEB_ADMIN_TABPOX."produce set  viceunit='".$data[5]."'  where code='".$data[10]."'";  
		
	 	$this -> dbObj -> Execute($q) ;
	 }
 }

 if(mysql_error()) {
  $error= '导入失败'.mysql_error();
 break;}
	}
$i=$i+1;
}
fclose($handle); 
        $t -> set_var('error',$error);
		$t -> set_var('backpath','produce.php');
		$t -> set_var('format','编码,名称,类别,品牌,主单位,副单位,容量,牌价,库存上限,下限,代码,简码,产地,功效,用法,成分,备注');	
		$t -> set_var('PHP_SELF',$_SERVER["PHP_SELF"]."?action=import");
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
}	
	function export(){
		
$filename=mb_convert_encoding("商品表",'gbk','utf-8').".xls";//先定义一个excel文件
header("Content-Type: application/vnd.ms-execl"); 
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 
echo mb_convert_encoding("编码",'gbk','utf-8')."\t";
echo mb_convert_encoding("名称",'gbk','utf-8')."\t";
echo mb_convert_encoding("类别",'gbk','utf-8')."\t";
echo mb_convert_encoding("品牌",'gbk','utf-8')."\t";
echo mb_convert_encoding("主单位",'gbk','utf-8')."\t";
echo mb_convert_encoding("副单位",'gbk','utf-8')."\t";
echo mb_convert_encoding("容量",'gbk','utf-8')."\t";
echo mb_convert_encoding("牌价",'gbk','utf-8')."\t";
echo mb_convert_encoding("库存上限",'gbk','utf-8')."\t";
echo mb_convert_encoding("下限",'gbk','utf-8')."\t";
echo mb_convert_encoding("代码",'gbk','utf-8')."\t";
echo mb_convert_encoding("简码",'gbk','utf-8')."\t";
echo mb_convert_encoding("产地",'gbk','utf-8')."\t";
echo mb_convert_encoding("功效",'gbk','utf-8')."\t";
echo mb_convert_encoding("用法",'gbk','utf-8')."\t";
echo mb_convert_encoding("成分",'gbk','utf-8')."\t";
echo mb_convert_encoding("备注",'gbk','utf-8')."\n";

$agencyid=$_SESSION["currentorgan"];
$sql='select * from '.WEB_ADMIN_TABPOX.'produce  where agencyid ='.$agencyid.' ORDER BY  produce_id DESC ';
$this -> dbObj -> Execute("SET NAMES GBK");
$result=$this -> dbObj -> Execute($sql); 
while ($inrrs = &$result -> FetchRow()) {	
$standardunit_name=$this -> dbObj -> GetOne("select unit_name from ".WEB_ADMIN_TABPOX."unit   where  unit_id=".$inrrs['standardunit']);
$viceunit_name=$this -> dbObj -> GetOne("select unit_name from ".WEB_ADMIN_TABPOX."unit   where  unit_id=".$inrrs['viceunit']);
$category_name=$this -> dbObj -> GetOne("select category_name from ".WEB_ADMIN_TABPOX."procatalog  where  category_id	=".$inrrs['categoryid']);
$brand_name=$this -> dbObj -> GetOne("select brand_name from ".WEB_ADMIN_TABPOX."brand  where  brand_id	=".$inrrs['brandid']);
echo $inrrs["produce_no"]."\t";
echo $inrrs['produce_name']."\t";
echo $category_name."\t";
echo $brand_name."\t";
echo $standardunit_name."\t";
echo $viceunit_name."\t";
echo $inrrs['viceunitnumber']."\t";
echo $inrrs['price']."\t";
echo $inrrs['upperlimit']."\t";
echo $inrrs['lowerlimit']."\t";
echo $inrrs['code']."\t";
echo $inrrs['shortcode']."\t";
echo $inrrs['address']."\t";
echo $inrrs['efficacy']."\t";
echo $inrrs['useway']."\t";
echo $inrrs['basis']."\t";
echo $inrrs['memo']."\n";
}		
		
	}
	function disp(){
		//定义模板
		$t = new Template('../template/basic');
		$t -> set_file('f','produce.html');
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'produce  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'produce p INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on p.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  p.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'produce  where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  produce_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			$t -> set_var('recordcount',$count);			

						
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'produce  where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('standardunit',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$inrrs["standardunit"]));
				$t -> set_var('brand_name',$this -> dbObj -> getone('select brand_name from '.WEB_ADMIN_TABPOX.'brand  where brand_id ='.$inrrs["brandid"]));
				$t -> set_var('category_name',$this -> dbObj -> getone('select category_name from '.WEB_ADMIN_TABPOX.'procatalog where category_id ='.$inrrs["categoryid"]));
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['produce_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['produce_id']));	
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispAppend(){

		$t = new Template('../template/basic');
		$t -> set_file('f','produce_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	


		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
			$t -> set_var('nodiscount',"");	
		$t -> set_var('produce_no',"");	
		$t -> set_var('produce_name',"");	
		$t -> set_var('state',"");	
		$t -> set_var('shortcode',"");	
		$t -> set_var('saletichpcent',"");	
		$t -> set_var('code',"");	
		$t -> set_var('viceunitnumber',"");	
		$t -> set_var('upperlimit',"");	
		$t -> set_var('lowerlimit',"");	
		$t -> set_var('price',"");	
		$t -> set_var('address',"");	
		$t -> set_var('efficacy',"");
		$t -> set_var('useway',"");
		$t -> set_var('basis',"");	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('memo',"");
		$t -> set_var('userid',$this->getUid());	
		$t -> set_var('picurl',"暂时没有照片");	
		$t -> set_var('picpath',"");
		//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'procatalog WHERE category_name="制成品" and agencyid = '.$_SESSION["currentorgan"];
		$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'procatalog WHERE category_name="制成品" and agencyid = '.$_SESSION["currentorgan"]);
		$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['category_id']));	
		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'produce WHERE produce_id = '.$updid);
			$t -> set_var($data);
			if ($data['picpath']==''){
			
			$t -> set_var('picurl',"暂时没有照片");	
			$t -> set_var('picpath',"");	
			}else{	
			$t -> set_var('picpath',$data['picpath']);
			$t -> set_var('picurl',"<img src=".$data['picpath']." width=120 height=150 />");}			
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
				 if($data['pronodiscount']==1){
				$t -> set_var('pronodiscount',"checked");
				}else{
				$t -> set_var('pronodiscount',"");
				}

		}
if ($date['state']==1){

		$t -> set_var('state','<input id="state" type="radio" checked="checked" value="1" name="state" /><label for="state">可用</label><input id="state" type="radio" value="0" name="state" /><label for="state">停用</label>');
}else
{
		$t -> set_var('state','<input id="state" type="radio"  value="1" name="state" /><label for="state_0">可用</label><input id="state" type="radio" value="0" checked="checked" name="state" /><label for="state">停用</label>');
}
		$t -> set_var('brandlist',$this ->selectlist('brand','brand_id','brand_name',$data['brandid']));				
		
		$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));						
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
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
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'produce WHERE produce_id in('.$delid.')');
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
			if($_POST["pronodiscount"]=='on'){$_POST["pronodiscount"]=1;}else{$_POST["pronodiscount"]=0;}
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."produce` (`produce_name`, `produce_no`, `categoryid`,  `brandid`, `standardunit`,  `viceunit`, `viceunitnumber`, `price`, `upperlimit`, `lowerlimit`, `pic`, `address`, `efficacy`, `useway`, `basis`, `memo`,  `state`, `agencyid`, `saletichpcent`, `code`, `shortcode`,`picpath`,pronodiscount)VALUES ( '".$_POST["produce_name"]."','".$_POST["produce_no"]."', '".$_POST["categoryid"]."','".$_POST["brandid"]."', '".$_POST["standardunit"]."', '".$_POST["viceunit"]."','".$this->intnonull($_POST["viceunitnumber"])."', '".$this->intnonull($_POST["price"])."','".$this->intnonull($_POST["upperlimit"])."', '".$this->intnonull($_POST["lowerlimit"])."','".$_POST["pic"]."','".$_POST["address"]."','".$_POST["efficacy"]."','".$_POST["useway"]."','".$_POST["basis"]."','".$_POST["memo"]."','".$_POST["state"]."','".$_SESSION["currentorgan"]."','".$this->intnonull($_POST["saletichpcent"])."','".$_POST["code"]."','".$_POST["shortcode"]."','".$_POST["picpath"]."','".$_POST['pronodiscount']."')");

 
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			if($_POST["pronodiscount"]=='on'){$_POST["pronodiscount"]=1;}else{$_POST["pronodiscount"]=0;}
			$this -> dbObj -> Execute("UPDATE `cmlerp`.`s_produce` SET `produce_name` = '".$_POST["produce_name"]."',`produce_no` = '".$_POST["produce_no"]."',`categoryid` = '".$_POST["categoryid"]."',`model` = '".$_POST["model"]."',`brandid` ='".$_POST["brandid"]."',`standardunit` = '".$_POST["standardunit"]."',`viceunit` = '".$_POST["viceunit"]."',`viceunitnumber` = '".$this->intnonull($_POST["viceunitnumber"])."',`price` = '".$this->intnonull($_POST["price"])."',`upperlimit` = '".$this->intnonull($_POST["upperlimit"])."',`lowerlimit` = '".$this->intnonull($_POST["lowerlimit"])."',`pic` = '".$_POST["pic"]."',`address` = '".$_POST["address"]."',`efficacy` = '".$_POST["efficacy"]."',`useway` = '".$_POST["useway"]."',`basis` = '".$_POST["basis"]."',`memo` ='".$_POST["memo"]."',`state` = '".$_POST["state"]."',`saletichpcent` = '".$_POST["saletichpcent"]."',`code` = '".$_POST["code"]."',`shortcode` = '".$_POST["shortcode"]."', picpath='".$_POST["picpath"]."', pronodiscount='".$_POST["pronodiscount"]."' WHERE produce_id =".$id);
		}
//$this -> quit($info.'成功！');
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
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
		exit("<script>alert('$info');location.href='produce.php';</script>");
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
  