<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/stock.cls.php');
class Pagecustomer extends admin {
	var $stockObj = null;
	function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='selectwarehouse')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this ->selectwarehouse();
        }else if(isset($_GET['action']) && $_GET['action']=='submittoaudit')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this ->submittoaudit();
        }else if(isset($_GET['action']) && $_GET['action']=='audit')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this ->audit();
        }else if(isset($_GET['action']) && $_GET['action']=='print')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this ->print1();
        }else{
            parent::Main();
        }
    }	
 	function disp(){
		 
		$t = new Template('../template/stock');
		$t -> set_file('f','initstock.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_var('producecategorylist',$this ->selectlist('procatalog','category_id','category_name',0));
		$t -> set_var('warehouselist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',0));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');		
 
		}
 
	function goDispAppend(){		

		$warehouse_id=$_GET['warehouse_id'];
		$category_id=$_GET['category_id'];

		
		$sql='select produce_id from '.WEB_ADMIN_TABPOX.'produce where categoryid ='.$category_id.' and  agencyid ='.$_SESSION["currentorgan"];
		 
		$producedata = &$this -> dbObj -> Execute($sql);
		 $i=0;
		while ($inrrsproducedata = &$producedata -> FetchRow()) {	
		 
		$sqlnumber='select * from '.WEB_ADMIN_TABPOX.'stock where produce_id ='.$inrrsproducedata['produce_id'].' and warehouse_id='.$warehouse_id.' and agencyid ='.$_SESSION["currentorgan"];
		
		$producenumber =  $this -> dbObj -> Execute($sqlnumber);
		$count=$producenumber->RecordCount();
			if($count=='0'){
 			 $i=$i+1;
			$sqlinsert='INSERT INTO  '.WEB_ADMIN_TABPOX.'stock  (produce_id,warehouse_id,agencyid) VALUE ('.$inrrsproducedata['produce_id'].','.$warehouse_id.','.$_SESSION["currentorgan"].')';
			$this -> dbObj -> Execute($sqlinsert);
			}
		}
 		exit("<script>alert('导入".$i."条产品信息');history.go(-1);</script>");

		
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
		
		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'takestockdetail WHERE takestock_id in('.$delid.') and agencyid='.$_SESSION["currentorgan"]);
		echo 'DELETE FROM '.WEB_ADMIN_TABPOX.'takestock WHERE takestock_id in('.$delid.') and agencyid='.$_SESSION["currentorgan"];
		//$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'salary  WHERE takestock_id in('.$delid.') and agencyid='.$_SESSION["currentorgan"]);
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
			//插入盘点表
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'takestock (takestock_no,warehouse_id,agencyid) value("'.$_POST['takestock_no'].'","'.$_POST['warehouse_id'].'","'.$_SESSION["currentorgan"].'")');
		
		$id = $this -> dbObj -> Insert_ID();
		//插入明细
			//$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."takestockdetail` (`takestock_id`,`warehouse_id`,`produce_id`, `sys_number`, `real_number`,`agencyid`)VALUES ( '".$id."','".$_POST["warehouse_id"]."', '".$_POST["produce_id"]."','".$_POST["sys_number"]."', '".$_POST["real_number"]."', '".$_POST["idnumber"]."','".$_SESSION["currentorgan"]."')");
			

			$sql="select  * from ".WEB_ADMIN_TABPOX."stock where  warehouse_id =".$_POST['warehouse_id']." and agencyid =".$_SESSION["currentorgan"];			
			$inrs =$this->dbObj->Execute($sql);
	     	while ($inrrs = &$inrs -> FetchRow()) {	
			 $this -> dbObj ->Execute("INSERT INTO `".WEB_ADMIN_TABPOX."takestockdetail` (`takestock_id`,`warehouse_id`,`produce_id`, `sys_number`,`real_number`, `agencyid`)VALUES ( '".$id."','".$inrrs["warehouse_id"]."', '".$inrrs['produce_id']."','".$inrrs['number']."','".$inrrs['number']."', '".$_SESSION["currentorgan"]."')");
			 //echo "INSERT INTO `".WEB_ADMIN_TABPOX."takestockdetail` (`takestock_id`,`warehouse_id`,`produce_id`, `sys_number`,`real_number`, `agencyid`)VALUES ( '".$id."','".$inrrs["warehouse_id"]."', '".$inrrs['produce_id']."','".$inrrs['number']."','".$inrrs['number']."', '".$_SESSION["currentorgan"]."')";
			}
			$inrs -> Close();					
			
			 $id_list = $_POST['id_list'];
			 
			 $produce_id=explode(",",$id_list);
			
			 for ($i=0;$i<count($produce_id);$i++){
				 $sys_number=$_POST['sys_number'.$produce_id[$i]];
				 
				 $real_number=$_POST['real_number'.$produce_id[$i]];
				 $difference =$_POST['difference'.$produce_id[$i]];
				 $memo=$_POST['memo'.$produce_id[$i]];
			 $this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."takestockdetail` SET  `sys_number`='". $sys_number."', `real_number`='".$real_number."', difference=".$difference.", memo ='".$memo."' WHERE produce_id=".$produce_id[$i]." and `warehouse_id`='".$_POST["warehouse_id"]."' and  `agencyid`='".$_SESSION["currentorgan"]."' and takestock_id=".$id);
			   //echo "UPDATE `".WEB_ADMIN_TABPOX."takestockdetail` SET  `sys_number`='". $sys_number."', `real_number`='".$real_number."' and difference=".$difference." and memo ='".$memo."' WHERE produce_id=".$produce_id[$i]." and `warehouse_id`='".$_POST["warehouse_id"]."' and  `agencyid`='".$_SESSION["currentorgan"]."'";

			 }
			 
		exit("<script>alert('保存成功');location.href='checkstock.php?action=upd&updid=".$id."';</script>");
			
			// $this -> dbObj -> Execute("insert into `".WEB_ADMIN_TABPOX."attendance`(`takestock_id`,agencyid) values ('$id',".$_SESSION["currentorgan"].")"); 
 			// $this -> dbObj -> Execute("insert into `".WEB_ADMIN_TABPOX."salary` (`takestock_id`,`agencyid` )VALUES ('$id',".$_SESSION["currentorgan"].")");
			// echo "insert into `".WEB_ADMIN_TABPOX."salary` (`takestock_id`,`agencyid` )VALUES ('$id',".$_SESSION["currentorgan"].")";
			 
//echo "INSERT INTO `".WEB_ADMIN_TABPOX."takestock` (`takestock_no`, `takestock_name`, `address`, `genderid`, `birthday`,  `idnumber`, `tel`, `handphone`,  `zipcode`, `email`,  `memo`,  `agencyid`, `picpath`,ismarry,province,city)VALUES ( '".$_POST["takestock_no"]."','".$_POST["takestock_name"]."', '".$_POST["address"]."','".$_POST["genderid"]."', '".$_POST["birthday"]."', '".$_POST["idnumber"]."','".$_POST["tel"]."', '".$_POST["handphone"]."','".$_POST["zipcode"]."', '".$_POST["email"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["picpath"]."','".$this->intnonull($_POST["ismarry"])."', '".$_POST["province"]."','".$_POST["city"]."')";
			//$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			
			 $id_list = $_POST['id_list'];
			 $produce_id=explode(",",$id_list);
			 for ($i=0;$i<count($produce_id);$i++){
				 $sys_number=$_POST['sys_number'.$produce_id[$i]];
				 $real_number=$_POST['real_number'.$produce_id[$i]];
				 $difference =$_POST['difference'.$produce_id[$i]];
				 $memo=$_POST['memo'.$produce_id[$i]];
			 $this -> dbObj -> Execute("UPDATE  `".WEB_ADMIN_TABPOX."takestockdetail` SET   `real_number`=".$real_number." , difference=".$difference." , memo ='".$memo."' WHERE `warehouse_id`='".$_POST['warehouse_id']."' and `produce_id`='".$produce_id[$i]."' and `agencyid`='".$_SESSION["currentorgan"]."' and takestock_id=".$_POST['takestock_id']);
			 
			 }
			exit("<script>history.go(-1);</script>");
		}
//$this -> quit($info.'成功！');

		

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
		exit("<script>alert('$info');location.href='checkstock.php';</script>");
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
  