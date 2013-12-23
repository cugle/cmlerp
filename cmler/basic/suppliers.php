<?
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
		$t -> set_file('f','suppliers.html');
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
			
			//设置
			
		
			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'suppliers   where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'suppliers  a INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on a.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  a.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'suppliers  where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  suppliers_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
	    	$t -> set_var('recordcount',$count);				

			//设置商品
			$t -> set_var('ml');
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'suppliers  where agencyid ='.$_SESSION["currentorgan"]);
			//echo 'select * from '.WEB_ADMIN_TABPOX.'suppliers  where agencyid ='.$_SESSION["currentorgan"];
	     	
			while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['suppliers_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['suppliers_id']));				
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
		$t -> set_file('f','suppliers_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		//$t -> set_block('f','gender','g');	

		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');		
			$t -> set_var('cstareaid',$this->selectlist('customizearea','cstarea_id','cstarea_name',''));	
			$t -> set_var('error',"");	
			$t -> set_var('address',"");
			$t -> set_var('tel',"");
			$t -> set_var('handphone',"");
			$t -> set_var('email',"");
			$t -> set_var('showeditdiv',"");		
			$t -> set_var('suppliers_no',"");
			$t -> set_var('suppliers_name',"");
			$t -> set_var('principal',"");
			$t -> set_var('memo',"");
			$t -> set_var('bank',"");
			$t -> set_var('createtime',date("Y-m-d H:i:s"));
			$t -> set_var('userid',$this->getUid());		
			 $t -> set_var('yifuaccounttitle_idlist',$this->selectlist1('accounttitle','accounttitle_id','accounttitle_name','',' and accounttitle_name like "应付帐款%"'));
		}else{
			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'suppliers WHERE suppliers_id = '.$updid));			
			$t -> set_var('error',"");	
		//	$t -> set_var('showeditdiv',$this->accounttype());
			
			
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$inrs = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'suppliers WHERE suppliers_id = '.$updid);	
			while ($inrrs = &$inrs -> FetchRow()) {
			    
				$t -> set_var($inrrs);
			$t -> set_var('cstareaid',$this->selectlist('customizearea','cstarea_id','cstarea_name',$inrrs['cstareaid']));
$t -> set_var('yifuaccounttitle_idlist',$this->selectlist1('accounttitle','accounttitle_id','accounttitle_name',$inrrs['yifuaccounttitle_id'],' and accounttitle_name like "应付帐款%"'));

			}
			$inrs -> Close();
		}
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> set_var('showeditdiv',"");				
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'suppliers WHERE suppliers_id in('.$delid.')');
		$this -> quit('删除成功！');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		
		if($this -> isAppend){
			$info = '增加';	
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."suppliers` ( `suppliers_no`, `suppliers_name`, `address`, `principal`, `tel`, `handphone`, `email`,cstareaid, `agencyid`,yifuaccounttitle_id)VALUES ( '".$_POST["suppliers_no"]."', '".$_POST["suppliers_name"]."',  '".$_POST["address"]."','".$_SESSION["principal"]."', '".$_POST["tel"]."','".$_POST["handphone"]."', '".$_POST["email"]."',  '".$_POST["cstareaid"]."', ".$_SESSION["currentorgan"].",  '".$_POST["yifuaccounttitle_id"]."')");
			//echo "INSERT INTO `".WEB_ADMIN_TABPOX."suppliers` ( `suppliers_no`, `suppliers_name`, `address`, `principal`, `tel`, `handphone`, `email`, `agencyid`)VALUES ( '".$_POST["suppliers_no"]."', '".$_POST["suppliers_name"]."',  '".$_POST["address"]."','".$_SESSION["principal"]."', '".$_POST["tel"]."','".$_POST["handphone"]."', '".$_POST["email"]."', ".$_SESSION["currentorgan"].")";
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];

			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."suppliers SET suppliers_name='".$_POST["suppliers_name"]."', suppliers_no='".$_POST["suppliers_no"]."', memo='".$_POST["memo"]."', principal='".$_POST["principal"]."',address='".$_POST["address"]."', tel='".$_POST["tel"]."', handphone='".$_POST["handphone"]."', email='".$_POST["email"]."',cstareaid='".$_POST["cstareaid"]."',yifuaccounttitle_id='".$_POST["yifuaccounttitle_id"]."' WHERE suppliers_id =".$id);
//echo 'UPDATE '.WEB_ADMIN_TABPOX."suppliers SET suppliers_name='".$_POST["suppliers_name"]."', suppliers_no='".$_POST["suppliers_no"]."', memo='".$_POST["memo"]."', principal='".$_POST["principal"]."',address='".$_POST["address"]."', tel='".$_POST["tel"]."', handphone='".$_POST["handphone"]."', email='".$_POST["email"]."',cstareaid='".$_POST["cstareaid"]."' WHERE suppliers_id =".$id;
		}
		exit("<script>location.href='suppliers.php';</script>");
		//$this -> quit($info.'成功！');
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
		function selectlist1($table,$id,$name,$selectid=0,$condition=''){
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table .' where agencyid =1 '.$condition);
			 
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
		exit("<script>alert('$info');location.href='suppliers.php';</script>");
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
  