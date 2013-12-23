<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
	    function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='savecustomer'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> savecustomer();			
		}else if(isset($_GET['action']) && $_GET['action']=='step2'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> disp();			
		}else if(isset($_GET['action']) && $_GET['action']==''){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> disp();			
		}else{
			 
            parent::Main();
        }
    }
	function disp(){
		//定义模板
		$t = new Template('../template/getdata');
		$t -> set_file('f','getcustomer.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		

        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		$t -> set_var('customercataloglist',$this ->selectlist2('customercatalog','customercatalog_id','customercatalog_name',''));
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "%'.$keywords.'%"';}else{$condition=$category.' like " %'.$keywords.'%"';}
		}
		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:100;
			$offset = $pageid>0?($pageid-1)*$psize:0;

			//设置分类
			$t -> set_var('ml');
		
		
			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'customer  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'customer p INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on p.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  p.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'customer  where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
			if($ftable=='membercard'){
				$sql='select * from '.WEB_ADMIN_TABPOX.'customer p INNER JOIN '.WEB_ADMIN_TABPOX.$ftable." f on p.customer_id =f.customer_id  where f.membercard_no <>'' and   p.agencyid =".$_SESSION["currentorgan"] ;
				$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  P.customer_id DESC  LIMIT ".$offset." , ".$psize);
				
			}else{
				
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  customer_id DESC  LIMIT ".$offset." , ".$psize);
			}
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
           	$t -> set_var('recordcount',$count);
				
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('gender',$inrrs["genderid"]==1?'男':'女');
				//$t -> set_var('category_name',$this -> dbObj -> getone('select category_name from '.WEB_ADMIN_TABPOX.'procatalog where category_id ='.$inrrs["categoryid"]));
				if($ftable=='membercard'){
					$cardlevel_name =  $this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'memcardlevel  where cardlevel_id ='.$inrrs["cardlevel_id"]);
				 
					 
					
					//$t -> set_var('address',$cardlevel_name["cardlevel_name"]);
				}
				
$cardlevel_name=$this -> dbObj -> GetOne("select B.cardlevel_name from  ".WEB_ADMIN_TABPOX."membercard  A INNER JOIN  ".WEB_ADMIN_TABPOX."memcardlevel B ON A.cardlevel_id=B.cardlevel_id where  A.customer_id=".$inrrs['customer_id']);

$customercatalog_name=$this -> dbObj -> GetOne("select A.customercatalog_name from  ".WEB_ADMIN_TABPOX."customercatalog  A INNER JOIN  ".WEB_ADMIN_TABPOX."customer B ON A.customercatalog_id=B.customercatalog_id where  B.customer_id=".$inrrs['customer_id']);
$cardlevel_name=$cardlevel_name?$cardlevel_name:$customercatalog_name;				

				 
				$t -> set_var('customercatalog_name',$cardlevel_name);
				
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispAppend(){
		 
		$t = new Template('../template/getdata');
		$t -> set_file('f','customer_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	

 
		$Prefix='gk';
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'customer';
		$column='customer_no';
		$number=5;
		$id='customer_id';	
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
		$t -> set_var('userid',$this->getUid());	
		$t -> set_var('picurl',"暂时没有照片");
		$t -> set_var('picpath',"");	
		$t -> set_var('birthday','');
		
		$t -> set_var('customercataloglist',$this ->selectlist2('customercatalog','customercatalog_id','customercatalog_name',''));
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
		function selectlist($table,$id,$name,$selectid=0){
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table." where  ('".date('Y-m-d',time())."' >bgdate	and '".date('Y-m-d',time())."'<= enddate)  or limitdate=1" );
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
function savecustomer(){

		//定义模板
		$info = '增加';	
		  //	echo "INSERT INTO `".WEB_ADMIN_TABPOX."customer` (`customer_no`, `customer_name`, `address`, `genderid`, `birthday`,  `idnumber`, `tel`, `handphone`,  `zipcode`, `email`,  `memo`,  `agencyid`, `picpath`,ismarry,province,city,qq)VALUES ( '".$_POST["customer_no"]."','".$_POST["customer_name"]."', '".$_POST["address"]."','".$_POST["genderid"]."', '".$_POST["birthday"]."', '".$_POST["idnumber"]."','".$_POST["tel"]."', '".$_POST["handphone"]."','".$_POST["zipcode"]."', '".$_POST["email"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["picpath"]."','".$this->intnonull($_POST["ismarry"])."', '".$_POST["province"]."','".$_POST["city"]."','".$_POST["qq"]."')";
		  
		  if($_POST['birthday']<>''){
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."customer` (`customer_no`, `customer_name`, `address`, `genderid`, `birthday`,  `idnumber`, `tel`, `handphone`,  `zipcode`, `email`,  `memo`,  `agencyid`, `picpath`,ismarry,province,city,qq,customercatalog_id)VALUES ( '".$_POST["customer_no"]."','".$_POST["customer_name"]."', '".$_POST["address"]."','".$_POST["genderid"]."', '".$_POST["birthday"]."', '".$_POST["idnumber"]."','".$_POST["tel"]."', '".$_POST["handphone"]."','".$_POST["zipcode"]."', '".$_POST["email"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["picpath"]."','".$this->intnonull($_POST["ismarry"])."', '".$_POST["province"]."','".$_POST["city"]."','".$_POST["qq"]."',".$_POST['customercatalog_id'].")");	
		  }else{
		  $this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."customer` (`customer_no`, `customer_name`, `address`, `genderid`, `idnumber`, `tel`, `handphone`,  `zipcode`, `email`,  `memo`,  `agencyid`, `picpath`,ismarry,province,city,qq,customercatalog_id)VALUES ( '".$_POST["customer_no"]."','".$_POST["customer_name"]."', '".$_POST["address"]."','".$_POST["genderid"]."', '".$_POST["idnumber"]."','".$_POST["tel"]."', '".$_POST["handphone"]."','".$_POST["zipcode"]."', '".$_POST["email"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["picpath"]."','".$this->intnonull($_POST["ismarry"])."', '".$_POST["province"]."','".$_POST["city"]."','".$_POST["qq"]."',".$_POST['customercatalog_id'].")");	
		  }

		$id = $this -> dbObj -> Insert_ID();
		if(mysql_affected_rows())
		 
		echo "<script> alert('新增客户成功');location.href='getcustomer.php';</script>";
	    else
		exit("<script>alert('新增失败，返回重新操作');location.href='getcustomer.php';</script>");
}	
		function gender($selectid=2){
			$str='';
			if ($selectid==1)
			$str ="<option value='1' selected>男</option><option value='2'>女</option>";	
			else
			$str ="<option value='1'>男</option><option value='2'  selected>女</option>";			
			return  $str;	
	    }	
	function intnonull($int){
	if ($int=="")$int=0;
		return $int;
	}	
function selectlist2($table,$id,$name,$selectid=0){
			 
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table." where  ('".date('Y-m-d',time())."' >bgdate	and '".date('Y-m-d',time())."'<= enddate)  or limitdate=1" );
			//echo 'select * from '.WEB_ADMIN_TABPOX.$table." where  ('".date('Y-m-d',time())."' >bgdate	and '".date('Y-m-d',time())."'<= enddate)  or limitdate=1";
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
  