<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
    function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='checkstock')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> checkstock();
        }else if(isset($_GET['action']) && $_GET['action']=='addtakestock')
		{
		    $this -> checkUser();//验证身份，这一步很重要。
            $this -> addtakestock();	
		}else{
            parent::Main();
        }
    }	
	function checkstock(){
			//定义模板
		$t = new Template('../template/stock');
		$t -> set_file('f','takestock.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
        $warehouse_id=$_GET['warehouse_id'];
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		$Prefix='';
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'takestock';
		$column='takestock_no';
		$number=2;
		$id='takestock_id';				
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
			    //$t -> set_var('ml');
				$t -> parse('ml','mainlist',true);
 
			
			
		$t -> set_var('warehouse_id',$warehouse_id);
		$t -> set_var('batch',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));				
		$t -> set_var('warehouselist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$_GET['keywords']));	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');	
	}
		function disp(){
		//定义模板
		$t = new Template('../template/stock');
		$t -> set_file('f','selectstock.html');
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
			$t -> set_var('ml');
		
		
			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'takestock where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'takestock s INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on s.produce_id =f.produce_id  where f.".$category." like '%".$keywords."%' and  s.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'takestock  where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
			
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  takestock_id ASC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
           	$t -> set_var('recordcount',$count);

			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'produce  where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				
				
				$t -> set_var('warehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse where warehouse_id ='.$inrrs["warehouse_id"]));
				$produce=$this -> dbObj -> Getrow('select * from '.WEB_ADMIN_TABPOX.'produce where produce_id ='.$inrrs["produce_id"]);
				$t -> set_var($produce);
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['takestock_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['takestock_id']));				
				$t -> set_var($inrrs);
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	

		$t -> set_var('warehouselist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$_GET['keywords']));	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');	
	}
	function addtakestock(){
			//定义模板
		$warehouse_id=$_GET['warehouse_id'];
		$Prefix='';
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'takestock';
		$column='takestock_no';
		$number=2;
		$id='takestock_id';		
		$takestock_no=$this->makeno($Prefix,$agency_no,$table,$column,$number,$id)
		$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'takestock (takestock_no,warehouse_id,agencyid) value('.$takestock_no.','.$warehouse_id.','.$_SESSION["currentorgan"].')');
		$id = $this -> dbObj -> Insert_ID();
			$inrs = &$this -> dbObj -> Execute('select produce_id,warehouse_id,number from stock where warehouse_id='.$warehouse_id.' and agencyid='.$_SESSION["currentorgan"]);
	     	while ($inrrs = &$inrs -> FetchRow()) {
				
				
				//$t -> set_var('warehouse_name',$this -> dbObj -> getone('Insert into takestock (produce_id,warehouse_id,sys_number,) )
			}
			$inrs -> Close();

	}
	function goDispAppend(){

		$t = new Template('../template/staff');
		$t -> set_file('f','staff_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	


		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		$Prefix='';
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'takestock';
		$column='takestock_no';
		$number=2;
		$id='takestock_id';				
		$t -> set_var('takestock_no',"");	
		$t -> set_var('takestock_name',"");	
		$t -> set_var('idnumber',"");	
		$t -> set_var('email',"");	
		$t -> set_var('handphone',"");	
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
		$t -> set_var('attendancecode',"");
		
		$t -> set_var('takestock_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));			
		$t -> set_var('userid',$this->getUid());	
		$t -> set_var('picurl',"暂时没有照片");	
		$t -> set_var('picpath',"");	
		$t -> set_var('birthday',date("Y-m-d"));
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'takestock WHERE takestock_id = '.$updid);
			$t -> set_var($data);
			if ($data['picpath']==''){
			
			$t -> set_var('picurl',"暂时没有照片");	
			}else{	
			$t -> set_var('picpath',$data['picpath']);	
			$t -> set_var('picurl',"<img src=".$data['picpath']." width=120 height=150 />");
			}	
			
			$t -> set_var('ismarrycheck1',$data['ismarry']==1?'checked':'');	
			$t -> set_var('ismarrycheck2',$data['ismarry']==2?'checked':'');	
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');

		}

		//$t -> set_var('brandlist',$this ->selectlist('brand','brand_id','brand_name',$data['brandid']));
		//$t -> set_var('genderlist',"111");	
		//echo $data['genderid'];
		$t -> set_var('genderlist',$this -> gender($data['genderid']));	
		//$t -> set_var('takestocklevellist',$this ->selectlist('takestocklevel','takestocklevel_id','takestocklevel_name',$data['takestocklevelid']));
		//$t -> set_var('emploeelevellist',$this ->selectlist('emploeelevel','emploeelevel_id','emploeelevel_name',$data['emploeelevelid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));						

		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}
	
	
		function selectlist($table,$id,$name,$selectid=0){
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table .' where agencyid='.$_SESSION["currentorgan"]);

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
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'takestock WHERE takestock_id in('.$delid.') and agencyid='.$_SESSION["currentorgan"]);
		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'attendance WHERE takestock_id in('.$delid.') and agencyid='.$_SESSION["currentorgan"]);
		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'salary  WHERE takestock_id in('.$delid.') and agencyid='.$_SESSION["currentorgan"]);
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
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."takestock` (`takestock_no`, `takestock_name`, `address`, `genderid`, `birthday`,  `idnumber`, `tel`, `handphone`,  `zipcode`, `email`,  `memo`,  `agencyid`, `picpath`,ismarry,province,city,takestocklevelid,attendancecode)VALUES ( '".$_POST["takestock_no"]."','".$_POST["takestock_name"]."', '".$_POST["address"]."','".$_POST["genderid"]."', '".$_POST["birthday"]."', '".$_POST["idnumber"]."','".$_POST["tel"]."', '".$_POST["handphone"]."','".$_POST["zipcode"]."', '".$_POST["email"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["picpath"]."','".$this->intnonull($_POST["ismarry"])."', '".$_POST["province"]."','".$_POST["city"]."','".$_POST["takestocklevelid"]."','".$_POST["attendancecode"]."')");
			 $id = $this -> dbObj -> Insert_ID();
			 $this -> dbObj -> Execute("insert into `".WEB_ADMIN_TABPOX."attendance`(`takestock_id`,agencyid) values ('$id',".$_SESSION["currentorgan"].")"); 
 			 $this -> dbObj -> Execute("insert into `".WEB_ADMIN_TABPOX."salary` (`takestock_id`,`agencyid` )VALUES ('$id',".$_SESSION["currentorgan"].")");
			 echo "insert into `".WEB_ADMIN_TABPOX."salary` (`takestock_id`,`agencyid` )VALUES ('$id',".$_SESSION["currentorgan"].")";
			 
//echo "INSERT INTO `".WEB_ADMIN_TABPOX."takestock` (`takestock_no`, `takestock_name`, `address`, `genderid`, `birthday`,  `idnumber`, `tel`, `handphone`,  `zipcode`, `email`,  `memo`,  `agencyid`, `picpath`,ismarry,province,city)VALUES ( '".$_POST["takestock_no"]."','".$_POST["takestock_name"]."', '".$_POST["address"]."','".$_POST["genderid"]."', '".$_POST["birthday"]."', '".$_POST["idnumber"]."','".$_POST["tel"]."', '".$_POST["handphone"]."','".$_POST["zipcode"]."', '".$_POST["email"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["picpath"]."','".$this->intnonull($_POST["ismarry"])."', '".$_POST["province"]."','".$_POST["city"]."')";
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."takestock` SET `takestock_name` = '".$_POST["takestock_name"]."',`takestock_no` = '".$_POST["takestock_no"]."',`idnumber` = '".$_POST["idnumber"]."',`tel` = '".$_POST["tel"]."',`handphone` ='".$_POST["handphone"]."',`email` = '".$_POST["email"]."',`address` = '".$_POST["address"]."',`zipcode` = '".$_POST["zipcode"]."',`birthday` = '".$_POST["birthday"]."',`genderid` = '".$this->intnonull($_POST["genderid"])."', picpath='".$_POST["picpath"]."',ismarry=".$this->intnonull($_POST['ismarry']).", province='".$_POST["province"]."', city='".$_POST["city"]."',takestocklevelid='".$_POST["takestocklevelid"]."',attendancecode='".$_POST["attendancecode"]."' WHERE takestock_id =".$id);
//echo "UPDATE `".WEB_ADMIN_TABPOX."customer` SET `customer_name` = '".$_POST["customer_name"]."',`customer_no` = '".$_POST["customer_no"]."',`idnumber` = '".$_POST["idnumber"]."',`tel` = '".$this->intnonull($_POST["tel"])."',`handphone` ='".$this->intnonull($_POST["handphone"])."',`email` = '".$_POST["email"]."',`address` = '".$_POST["address"]."',`zipcode` = '".$this->intnonull($_POST["zipcode"])."',`birthday` = '".$_POST["birthday"]."',`genderid` = '".$this->intnonull($_POST["genderid"])."', picpath='".$_POST["picpath"]."',ismarry=".$this->intnonull($_POST['ismarry']).", province='".$_POST["province"]."', city='".$_POST["city"]."' WHERE customer_id =".$id;
		}
//$this -> quit($info.'成功！');
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}
function makeno($Prefix,$agency_no,$table,$column,$number,$id){
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by ".$column." desc limit 1");
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

	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');location.href='takestock.php';</script>");
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
  