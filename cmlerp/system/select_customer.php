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
		$t = new Template('../template/system');
		$t -> set_file('f','select_customer.html');
		$t -> set_block('f','customer','c');
		$t -> set_block('customer','area','a');
		$t -> set_block('customer','user1','u');
		$t -> set_block('f','sale','s');
		$t -> set_block('f','arealist','al');
		
		$t -> set_var('add',$this -> getAddStr('img'));

		//设置用户
		$cateid=$_POST[cateid];
		$keywords=$_POST[keywords];
		if ($_GET[cateid]){
		if ($_GET[keywords]==0)$keywords='0';
		
		//$keywords=$_GET[keywords]==0?0:$keywords;
		}
		$cateid=$_GET[cateid]?$_GET[cateid]:$cateid;
		$keywords=$_GET[keywords]?$_GET[keywords]:$keywords;
		$Sale_ID=$_GET[Sale_ID];
		if ($keywords==""&$cateid==""){$condition="";}else{$condition=$cateid." like '".$keywords."%'";}
		if ($Sale_ID!=""){$condition=" userid=".$Sale_ID;}
		$condition = $condition ? " WHERE ".$condition : "";
		$condition =$condition." ORDER BY customerid desc";		
		$result= $this -> dbObj ->Execute("select *  from ".WEB_ADMIN_TABPOX."customer".$condition);
		$count=$result->RecordCount();
		$pageid=$_GET[pageid];
		$pageid = intval($pageid);
		$psize=$this->getValue('pagesize');
		$psize =$psize?$psize:30;
		$offset = $pageid>0?($pageid-1)*$psize:0;
		
		if($_GET[qgtype]<>""&$_GET[Sale_ID]<>""){
		  $t -> set_var('pagelist',$this -> page("select_customer.php?qgtype=".$_GET[qgtype]."&Sale_ID=".$_GET[Sale_ID],$count,$psize,$pageid));
		}else
		{
        $t -> set_var('pagelist',$this -> page("select_customer.php",$count,$psize,$pageid));
		}
		$rs = $this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX."customer".$condition." LIMIT ".$offset.",".$psize);
		while ($rrs = &$rs -> FetchRow()) {
			foreach ($rrs as $k=>$v)       $t -> set_var($k,$v);
			$t -> set_var('upd',$this->getUpdStr($rrs['importer'],$rrs['customerid'],'img'));
			$t -> set_var('del',$this->getDelStr($rrs['importer'],$rrs['customerid'],'img'));
			$t -> set_var('g');
		
             //处理程况
	
			
			//$t -> set_var($rrs);//设置区域
			$t -> set_var('a');
			$inrs = &$this -> dbObj -> Execute("select a.* from ".WEB_ADMIN_TABPOX."area a inner join  ".WEB_ADMIN_TABPOX."customer c on a.area_id=c.areaid where c.customerid =".$rrs['customerid']);
			while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var('area_name',$inrrs['area_name']);
				$t -> parse('a','area',true);
			}
			$inrs -> Close();

            //$t -> set_var($rrs);//设置负责人
			$t -> set_var('u');
			$inrs2 = &$this -> dbObj ->Execute("SELECT u.* FROM  ".WEB_ADMIN_TABPOX."user u INNER JOIN  ".WEB_ADMIN_TABPOX."customer c ON c.userid = u.userid WHERE c.customerid =".$rrs['customerid']);
			while ($inrrs = &$inrs2 -> FetchRow()) {
				$t -> set_var('username',$inrrs['username']);
				$t -> parse('u','user1',true);
			}
			$inrs2 -> Close();
	

			
			$t -> parse('c','customer',true);
		}

		$t -> set_var('arealist',$this->PPClass());	
		$t -> parse('al','arealist',true);		
		$t -> set_var('sale',$this->PPClass_sale());	
		$t -> parse('s','sale',true);	
		$t -> set_var('batchmovedisabled',$this->getAttach('batchmove')==1?"":"disabled='disabled'");	
		$t -> set_var('batchdeletedisabled',$this->getAttach('batchdelete')==1?"":"disabled='disabled'");	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispAppend(){

		$t = new Template('../template/system');
		$t -> set_file('f','customerdetail.html');
		$t -> set_block('f','gender','g');	
		$t -> set_block('f','area','a');
		$t -> set_block('f','sale','s');	
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		$t -> set_var('createtime',date("Y-m-d H:i:s"));
		$t -> set_var('userid',$this->getUid());			
		
		}else{
			$updid = $_GET[MODIFY.'id'] + 0 ;
			
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'customer WHERE customerid = '.$updid));			
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');

			$inrs = &$this -> dbObj -> Execute("select a.* from ".WEB_ADMIN_TABPOX."area a inner join  ".WEB_ADMIN_TABPOX."customer c on a.area_id=c.areaid where c.customerid =".$updid);		
			while ($inrrs = &$inrs -> FetchRow()) {
				$area=$inrrs['area_id'];
			}
			$inrs -> Close();
			$inrs1 = &$this -> dbObj -> Execute("select u.* from ".WEB_ADMIN_TABPOX."user u inner join  ".WEB_ADMIN_TABPOX."customer c on u.userid=c.userid where c.customerid =".$updid);		
			while ($inrrs1 = &$inrs1 -> FetchRow()) {
				$userid=$inrrs1['userid'];
				
			}
			$inrs1 -> Close();
			
			$inrs2 = &$this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX."customer where customerid =".$updid);	
			while ($inrrs2 = &$inrs2 -> FetchRow()) {
				$sex=$inrrs2['gender'];
			}
			$inrs2 -> Close();
					
		}
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		//$this->PPClass();
					
		$t -> set_var('area');
		$t -> set_var('area',$this->PPClass($area));			
		$t -> parse('a','area',true);	
		$t -> set_var('sale',$this->PPClass_sale($userid));	
		$t -> parse('s','sale',true);
		$t -> set_var('gender',$this->gender($sex));	
		$t -> parse('g','gender',true);	
			
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'customer WHERE customerid in('.$delid.')');
		$this -> quit('删除成功！');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		if($this -> isAppend){
			$info = '增加';
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."customer(customername,gender,birthday,national,idnumber,tel, handphone,address,zipcode,email,areaid,customerpass,loginnum,userid,importer,qq)values('".$_POST['customername']."',".$_POST["gender"].",'".$_POST["birthday"]."','".$_POST["national"]."','".$_POST["idnumber"]."','".$_POST["tel"]."','".$_POST["handphone"]."','".$_POST["address"]."','".$_POST["zipcode"]."','".$_POST["email"]."',".$_POST["areaid"].",'".md5($_POST['customerpass'])."',0,".$_POST["userid"].",".$this->getUid().",'".$_POST["qq"]."')");

			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'] + 0;
			if($_POST['password']){
				$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."customer SET customername='".$_POST['customername']."',gender=".$_POST["gender"].",birthday='".$_POST["birthday"]."',national='".$_POST["national"]."', idnumber='".$_POST["idnumber"]."', tel='".$_POST["tel"]."', handphone='".$_POST["handphone"]."', address='".$_POST["address"]."', zipcode='".$_POST["zipcode"]."', email='".$_POST["email"]."',areaid=".$_POST["ClassID"]." WHERE customerid =".$id);
			}else{ 
				$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."customer SET customername='".$_POST['customername']."',gender=".$_POST["gender"].",birthday='".$_POST["birthday"]."',national='".$_POST["national"]."', idnumber='".$_POST["idnumber"]."', tel='".$_POST["tel"]."', handphone='".$_POST["handphone"]."', address='".$_POST["address"]."', zipcode='".$_POST["zipcode"]."', email='".$_POST["email"]."',areaid=".$_POST["areaid"].",userid=".$_POST["userid"].",qq='".$_POST["qq"]."' WHERE customerid =".$id);
echo 'UPDATE '.WEB_ADMIN_TABPOX."customer SET customername='".$_POST['customername']."',gender=".$_POST["gender"].",birthday='".$_POST["birthday"]."',national='".$_POST["national"]."', idnumber='".$_POST["idnumber"]."', tel='".$_POST["tel"]."', handphone='".$_POST["handphone"]."', address='".$_POST["address"]."', zipcode='".$_POST["zipcode"]."', email='".$_POST["email"]."',areaid=".$_POST["areaid"].",userid=".$_POST["userid"].",qq='".$_POST["qq"]."' WHERE customerid =".$id;
			}
			if(isset($_POST['groups']))
			$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'customergroup WHERE customerid = '.$id);
		}
		if(isset($_POST['groups']))
		foreach ($_POST['groups'] as $v){
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."customergroup(customerid,groupid,importer)values($id,$v,".$this->getUid().')');
		}
		$this -> quit($info.'成功！');
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
	function gender($sex='2'){
		$arr="";
		if($sex=='1'){
		$arr="<option value='2' >女</option><option value='1' >男</option>";
		}else
		{$arr="<option value='2' selected>女</option><option value='1'>男</option>";
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
  