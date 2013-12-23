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
		$t -> set_file('f','visiterecord.html');
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'visiterecord where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'visiterecord p INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on p.".$ftable."id =f.".$ftable."_id  where f.".$ftable."_name like '%".$keywords."%' and  p.agencyid =".$_SESSION["currentorgan"] ;
			echo $sql;
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'visiterecord  where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  visiterecord_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
           	$t -> set_var('recordcount',$count);
						
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'produce  where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('gender',$inrrs["genderid"]==1?'男':'女');
				//$t -> set_var('category_name',$this -> dbObj -> getone('select category_name from '.WEB_ADMIN_TABPOX.'procatalog where category_id ='.$inrrs["categoryid"]));
				$t -> set_var('satisfaction_name',$this -> dbObj -> getone('select satisfaction_name from '.WEB_ADMIN_TABPOX.'satisfaction where satisfaction_id ='.$inrrs["satisfactionid"]));
				$t -> set_var('visitetypename',$this -> dbObj -> getone('select visitetype_name from '.WEB_ADMIN_TABPOX.'visitetype where visitetype_id ='.$inrrs["visitetypeid"]));
				
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['visiterecord_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['visiterecord_id']));				
				
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	
		$t -> set_var('employeeslist',$this->selectlist('employee','employee_id','employee_name','',0));		
		$t -> set_var('vistietypelist',$this->selectlist('visitetype','visitetype_id','visitetype_name','',1));			
		$t -> set_var('satisfactionlist',$this->selectlist('satisfaction','satisfaction_id','satisfaction_name','',1));	
		

		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispAppend(){

		$t = new Template('../template/customer');
		$t -> set_file('f','visiterecord_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	


		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		if ($_GET['customer_id']==''){
		$t -> set_var('customer_id',"");}else{
		$t -> set_var('customer_id',$_GET['customer_id']);}
		if ($_GET['customer_name']==''){
		$t -> set_var('customer_name','');}else{
		$t -> set_var('customer_name',$_GET['customer_name']);
		}	
		$t -> set_var('man',"");	
		$t -> set_var('content',"");	
		$t -> set_var('handphone',"");	
		$t -> set_var('visitedate',date("Y-m-d"));	
		$t -> set_var('title',"");	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");
		
		$t -> set_var('findcustomer',"");			
		$t -> set_var('memo',"");
		$t -> set_var('userid',$this->getUid());	
		$t -> set_var('picurl',"暂时没有照片");	
		$t -> set_var('birthday',date("Y-m-d"));
		$t -> set_var('updid',"");
		$data['visitetypeid']=$_GET['visitetypeid'];
		if($_GET['visitetypeid']==3){
		$t -> set_var('title',"生日祝福");	
		$t -> set_var('content',$this -> dbObj -> getone('select content from '.WEB_ADMIN_TABPOX.'visiterecordtemplate  where visiterecordtemplate_name like "%生日%"'));	
		}
		//$t -> set_var('vistietypelist',$this->selectlist('visitetype','visitetype_id','visitetype_name',""));	
		}else{
			$t -> set_var('findcustomer',"disabled='disabled'");
			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'visiterecord WHERE visiterecord_id = '.$updid);
			$t -> set_var($data);
			if ($data['picpath']==''){
			
			$t -> set_var('picurl',"暂时没有照片");	
			}else{	
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
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));	
		$t -> set_var('vistietypelist',$this->selectlist('visitetype','visitetype_id','visitetype_name',$data['visitetypeid'],1));			
		$t -> set_var('satisfactionlist',$this->selectlist('satisfaction','satisfaction_id','satisfaction_name',$data['satisfactionid'],1));	
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
	
		function selectlist($table,$id,$name,$selectid=0,$ispub=0){
		if($ispub==0){
		$inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table.' where agencyid ='.$_SESSION["currentorgan"]);

		}else{
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table);

			}
			
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
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'visiterecord WHERE visiterecord_id in('.$delid.')');
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
			
			$customer_id=explode(";",$_POST["customer_id"]);
			$customer_name=explode(";",$_POST["customer_name"]);
			//$handphone=explode(";",$_POST["handphone"]);
			//$birthday=explode(";",$_POST['booktime']);

			$content=$_POST["content"];
			$sqlstr='';
			date_default_timezone_set('Asia/Hong_Kong');
            $sendtime=time();
			for ($i=0;$i<count($customer_id);$i++)	{
			$content1= str_replace('{customername}',$customer_name[$i],$content);			 
			$content1= str_replace('{birthday}',$birthday[$i],$content1);	
			if($sqlstr==''){

			$sqlstr="( '".$_POST["title"]."','".$customer_id[$i]."', '".$customer_name[$i]."','".$_POST["visitetypeid"]."', '".$_POST["satisfactionid"]."', '".$content1."','".$_POST["man"]."','".$_POST["employee_id"]."','".$_POST["visitedate"]."', '".$_POST["memo"]."','".$_SESSION["currentorgan"]."')";
			}else{
		 	$sqlstr=$sqlstr.", ( '".$_POST["title"]."','".$customer_id[$i]."', '".$customer_name[$i]."','".$_POST["visitetypeid"]."', '".$_POST["satisfactionid"]."', '".$content1."','".$_POST["man"]."','".$_POST["employee_id"]."','".$_POST["visitedate"]."', '".$_POST["memo"]."','".$_SESSION["currentorgan"]."')";
			}

			}
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."visiterecord` (`title`, `customer_id`, `customer_name`, `visitetypeid`, `satisfactionid`,  `content`, `man`,`employee_id`,`visitedate`,`memo`,  `agencyid`)VALUES ".$sqlstr);
			//$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."visiterecord` (`title`, `customer_id`, `customer_name`, `visitetypeid`, `satisfactionid`,  `content`, `man`,`employee_id`,`visitedate`,`memo`,  `agencyid`)VALUES ( '".$_POST["title"]."','".$_POST["customer_id"]."', '".$_POST["customer_name"]."','".$_POST["visitetypeid"]."', '".$_POST["satisfactionid"]."', '".$_POST["content"]."','".$_POST["man"]."','".$_POST["employee_id"]."','".$_POST["visitedate"]."', '".$_POST["memo"]."','".$_SESSION["currentorgan"]."')");
echo "INSERT INTO `".WEB_ADMIN_TABPOX."messagerecord` (`title`, `customer_id`, `customer_name`, `visitetypeid`, `satisfactionid`,  `content`, `man`,`employee_id`,`visitedate`,`memo`,  `agencyid`)VALUES ".$sqlstr;
//echo "INSERT INTO `".WEB_ADMIN_TABPOX."visiterecord` (`title`, `customer_id`, `customer_name`, `visitetypeid`, `satisfactionid`,  `content`, `man`,`employee_id`,`visitedate`,`memo`,  `agencyid`)VALUES ( '".$_POST["title"]."','".$_POST["customer_id"]."', '".$_POST["customer_name"]."','".$_POST["visitetypeid"]."', '".$_POST["satisfactionid"]."', '".$_POST["content"]."','".$_POST["man"]."','".$_POST["employee_id"]."','".$_POST["visitedate"]."', '".$_POST["memo"]."','".$_SESSION["currentorgan"]."')";
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."visiterecord` SET `title` = '".$_POST["title"]."',`customer_id` = '".$_POST["customer_id"]."',`customer_name` = '".$_POST["customer_name"]."',`visitetypeid` = '".$_POST["visitetypeid"]."',`satisfactionid` ='".$_POST["satisfactionid"]."',`content` = '".$_POST["content"]."',`man` = '".$_POST["man"]."',employee_id='".$_POST["employee_id"]."',visitedate='".$_POST["visitedate"]."', `memo` = '".$_POST["memo"]."' WHERE visiterecord_id =".$id);
//echo "UPDATE `".WEB_ADMIN_TABPOX."visiterecord` SET `title` = '".$_POST["title"]."',`customer_id` = '".$_POST["customer_id"]."',`customer_name` = '".$_POST["customer_name"]."',`visitetypeid` = '".$_POST["visitetypeid"]."',`satisfactionid` ='".$_POST["satisfactionid"]."',`content` = '".$_POST["content"]."',`man` = '".$_POST["man"]."',visitedate='".$_POST["visitedate"]."', `memo` = '".$_POST["memo"]."' WHERE visiterecord_id =".$id;
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
		exit("<script>alert('$info');location.href='visiterecord.php';</script>");
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
  