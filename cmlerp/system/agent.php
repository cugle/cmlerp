<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class Pagevisiteplan extends admin {
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
		if($SubmitValue=="移至该员工"){
		$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX.'agent SET userid ='.$Sale_ID.' WHERE agentid in('.$TheID.')');
		//echo 'UPDATE '.WEB_ADMIN_TABPOX.'400record SET userid ='.$Sale_ID.' WHERE planid in('.$TheID.')';
		//$this -> quit('移动成功！');
		echo "<script language=javascript>history.go(-1);</script>";
		}


    }

	function disp(){
		//定义模板
		$t = new Template('../template/system');
		$t->unknowns = "keep";
		$t->left_delimiter = "["; //修改左边界符为[##
        $t->right_delimiter = "]"; //修改右边界符##]
		$t -> set_file('f','agent.html');
		$t -> set_block('f','agent','a');

		//$t -> set_block('400record','customer','c');
		$t -> set_block('agent','user','u');
		$t -> set_block('f','sale','s');
		$t -> set_block('agent','area','ar');
		$t -> set_block('f','diqu','d');			
		$t -> set_var('add',$this -> getAddStr('img'));

		

		//设置用户
		$cateid=$_POST[cateid];
		$keywords=$_POST[keywords];
		$cateid=$_GET[cateid]?$_GET[cateid]:$cateid;
		$keywords=$_GET[keywords]?$_GET[keywords]:$keywords;
		$Sale_ID=$_GET[Sale_ID];
		$datebg=$_GET[datebg];
		$dateend=$_GET[dateend];	
		if ($keywords==""&$cateid==""){$condition="";}else{$condition=$cateid."='".$keywords."'";}
		if ($Sale_ID!=""){$condition=" userid=".$Sale_ID;}
		if($dateend!=""&$datebg!=""){$condition=" creattime BETWEEN '".$datebg." 00:00:00' AND '".$dateend." 23:59:59'";}
		$condition = $condition ? " WHERE ".$condition : "";	
		$condition =$condition." ORDER BY agentid desc";
		$result= $this -> dbObj ->Execute("select *  from ".WEB_ADMIN_TABPOX."agent".$condition);
		$count=$result->RecordCount();
		$pageid=$_GET[pageid];
		$pageid = intval($pageid);
		$psize = 20;
		$offset = $pageid>0?($pageid-1)*$psize:0;

		if($_GET[qgtype]<>""&$_GET[Sale_ID]<>""){
		  $t -> set_var('pagelist',$this -> page("agent.php?qgtype=".$_GET[qgtype]."&Sale_ID=".$_GET[Sale_ID],$count,$psize,$pageid));
		}else
		{
        $t -> set_var('pagelist',$this -> page("agent.php",$count,$psize,$pageid));
		}
		//$t -> set_var('pagelist',$this -> page('400record.php',$count,$psize,$pageid));
		$rs = $this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX."agent".$condition." LIMIT ".$offset.",".$psize);
		while ($rrs = &$rs -> FetchRow()) {
			foreach ($rrs as $k=>$v)       $t -> set_var($k,$v);
			$t -> set_var('upd',$this->getUpdStr($rrs['importer'],$rrs['agentid'],'img'));
			$t -> set_var('del',$this->getDelStr($rrs['importer'],$rrs['agentid'],'img'));
			$t -> set_var('g');
			//$t -> set_var($rrs);
			
			
			//设置管理人
			$t -> set_var('u');
			$inrs1 = &$this -> dbObj ->Execute("SELECT u.* FROM  ".WEB_ADMIN_TABPOX."user u INNER JOIN  ".WEB_ADMIN_TABPOX."agent a ON u.userid = a.userid WHERE a.agentid =".$rrs['agentid']);
			while ($inrrs1 = &$inrs1 -> FetchRow()) {
				$t -> set_var('username',$inrrs1['username']);
				$t -> parse('u','user',true);
			}
			$inrs1 -> Close();			
			
			
			//设置区域
			
			$t -> set_var('ar');
			$inrs = &$this -> dbObj -> Execute("select a.* from ".WEB_ADMIN_TABPOX."area a inner join  ".WEB_ADMIN_TABPOX."agent ag on a.area_id=ag.areaid where ag.agentid =".$rrs['agentid']);
		
			while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var('area_name',$inrrs['area_name']);
				$t -> parse('ar','area',true);
			}
			$inrs -> Close();

			$t -> parse('a','agent',true);
		}
		$t -> set_var('diqu',$this->PPClass());		
		$t -> parse('d','diqu',true);
		$t -> set_var('datebg',$datebg?$datebg:date("Y-m-d"));			
		$t -> set_var('dateend',$dateend?$dateend:date("Y-m-d"));	
		$t -> set_var('sale',$this->PPClass_sale());	
		$t -> parse('s','sale',true);						
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
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
	//设置性别
	function gender($sex='2'){
		$arr="";
		if($sex=='1'){
		$arr="<option value='2' >女</option><option value='1' >男</option>";
		}else
		{$arr="<option value='2' selected>女</option><option value='1'>男</option>";
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
	function classnamelist($classid=0){
    		$sale =$this -> dbObj -> Execute('Select * From '.WEB_ADMIN_TABPOX.'400class');
			$count=$sale->RecordCount();
			$i=0;
			$arr="";
			while ($rrs = &$sale -> FetchRow()) {
			     if ($rrs['classid']==$classid){
				 $arr=$arr."<option value='".$rrs['classid']."' selected>".$rrs['classname']."</option>";
				 }else
				 {
				  $arr=$arr."<option value='".$rrs['classid']."'>".$rrs['classname']."</option>";
				 }
				 $i=$i+1;
            }
			
           return $arr;

	}		
	function goDispAppend(){

		$t = new Template('../template/system');
		$t -> set_file('f','agentdetail.html');
		$t -> set_block('f','contact','c');
		$t -> set_block('f','user','u');
		$t -> set_block('f','diqu','d');
		$t -> set_block('f','gender','g');	
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
			
				$t -> set_var('4c');
				$t -> set_var('classnamelist',$this->classnamelist());	
				$t -> parse('4c','400class',true);	
							
			if ($_GET[customerid]<>""){
			$inrs3 = &$this -> dbObj ->Execute("SELECT * FROM  ".WEB_ADMIN_TABPOX."customer WHERE customerid = ".$_GET[customerid]);
 				while ($inrrs = &$inrs3 -> FetchRow()) {
				$t -> set_var('customerid',$inrrs['customerid']);								
			    $t -> set_var('c');	
				$t -> set_var('customername',$inrrs['customername']);
				$t -> parse('c','customer',true);
		     	}
				$inrs3 -> Close();	
			}

		
			$t -> set_var('creattime',date("Y-m-d H:i:s"));
			$inrs4 = &$this -> dbObj ->Execute("SELECT * FROM  ".WEB_ADMIN_TABPOX."user WHERE userid = ".$this ->getUid());
 				while ($inrrs = &$inrs4 -> FetchRow()) {
				$t -> set_var('userid',$inrrs['userid']);								
			    $t -> set_var('u');	
				$t -> set_var('username',$inrrs['username']);
				$t -> parse('u','user',true);
		     	}
				$inrs4 -> Close();				
			


				
		}else{
			$updid = $_GET[MODIFY.'id'] + 0 ;
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'agent WHERE agentid = '.$updid));
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');


			//设置分类
			$t -> set_var('4c');
	
			$inrs5 = &$this -> dbObj ->Execute("SELECT c.* FROM  ".WEB_ADMIN_TABPOX."400class c INNER JOIN  ".WEB_ADMIN_TABPOX."400record r ON c.classid = r.classid WHERE r.400id =".$updid);
			while ($inrrs = &$inrs5 -> FetchRow()) {
				$t -> set_var('4c');
				$t -> set_var('classnamelist',$this->classnamelist($inrrs['classid']));	
				$t -> parse('4c','400class',true);	
			}
			$inrs5 -> Close();			

			//客户名称
			$t -> set_var('c');

			
			
			$inrs2 = &$this -> dbObj ->Execute("SELECT c.* FROM  ".WEB_ADMIN_TABPOX."customer c INNER JOIN  ".WEB_ADMIN_TABPOX."400record r ON c.customerid = r.customerid WHERE r.400id =".$updid);
			while ($inrrs = &$inrs2 -> FetchRow()) {
				$t -> set_var('customername',$inrrs['customername']);
				$t -> parse('c','customer',true);
			}
			$inrs2 -> Close();
		
			
			
			
			//执行人名称
			$t -> set_var('u');
			$inrs1 = &$this -> dbObj ->Execute("SELECT u.* FROM  ".WEB_ADMIN_TABPOX."user u INNER JOIN  ".WEB_ADMIN_TABPOX."agent a ON u.userid = a.userid WHERE a.agentid =".$updid);
			while ($inrrs1 = &$inrs1 -> FetchRow()) {
				$t -> set_var('username',$inrrs1['username']);
				$t -> parse('u','user',true);
			}
			$inrs1 -> Close();
			//设置地区列表

			$inrs7 = &$this -> dbObj ->Execute("SELECT a.* FROM  ".WEB_ADMIN_TABPOX."area a INNER JOIN  ".WEB_ADMIN_TABPOX."agent ag ON a.area_id = ag.areaid WHERE ag.agentid =".$updid);
			while ($inrrs = &$inrs7 -> FetchRow()) {
				$t -> set_var('diqu',$this->PPClass($inrrs['area_id']));	
				$t -> parse('d','diqu',true);
			}
			$inrs7 -> Close();			
							     
			//联系人
			$inrs6 = &$this -> dbObj ->Execute("SELECT c.* FROM  ".WEB_ADMIN_TABPOX."contact c INNER JOIN  ".WEB_ADMIN_TABPOX."agentcontact  ac ON c.contactid=ac.contactid WHERE ac.agentid = ".$updid);
    		$recordstr="";
			while ($inrrs = &$inrs6 -> FetchRow()) {
											
			        $t -> set_var('c');
				$recordstr=$recordstr."<tr class='tdBgColor' onMouseOver='TrOver(this)' onMouseOut='TrOut(this)'><td>".$inrrs['contactid']."</td> <td><a href=contact.php?action=upd&updid=".$inrrs[contactid].">".$inrrs['contactname']."</a></td><td>".$inrrs['tel']."</td><td>".$inrrs['handphone']."<br></td><td>".$inrrs['address']."</td><td>".$inrrs['email']."</td><td>".$inrrs['email']."</td><td><a href=contact.php?action=upd&updid=".$inrrs[contactid].">编辑</a></td><td><a href=contact.php?action=del&delid=".$inrrs[contactid]."&agentid=".$updid.">删除</a></td></tr>";	
				
		     	}
				$recordstr=$recordstr."<tr class='tdBgColor'><td colspan='11'><input name='button' type=button onclick=window.location.href('contact.php?action=add&agentid=".$updid."') value='添加联系人'></td></tr>";
				
				
				
				$t -> set_var('contact',$recordstr);
				$t -> parse('c','contact',true);
		        $inrs6 -> Close();	

		}
 	   $t -> set_var('diqu',$this->PPClass());		
		$t -> parse('d','diqu',true);
		$t -> set_var('gender',$this->gender());
		$t -> parse('g','gender',true);	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'agent WHERE agentid in('.$delid.')');
		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'agentcontact WHERE agentid in('.$delid.')');
		$this -> quit('删除成功！');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		if($this -> isAppend){
			$info = '增加';
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."agent(agentname,address,zipcode,fax,tel,tel1,email,qq,areaid,url,license,industry,employeesnumber,establishmentday,profile,brand)values('".$_POST['agentname']."','".$_POST['address']."','".$_POST['zipcode']."','".$_POST['fax']."','".$_POST['tel']."','".$_POST['tel1']."','".$_POST['email']."','".$_POST['qq']."','".$_POST["areaid"]."','".$_POST["url"]."','".$_POST["license"]."','".$_POST["industry"]."','".$_POST["employeesnumber"]."','".$_POST["establishmentday"]."','".$_POST["profile"]."','".$_POST["brand"]."')");
		echo 'INSERT INTO '.WEB_ADMIN_TABPOX."agent(agentname,address,zipcode,fax,tel,tel1,email,qq,areaid,url,license,industry,employeesnumber,establishmentday,profile,brand)values('".$_POST['agentname']."','".$_POST['address']."','".$_POST['zipcode']."','".$_POST['fax']."','".$_POST['tel']."','".$_POST['tel1']."','".$_POST['email']."','".$_POST['qq']."','".$_POST["areaid"]."','".$_POST["url"]."','".$_POST["license"]."','".$_POST["industry"]."','".$_POST["employeesnumber"]."','".$_POST["establishmentday"]."','".$_POST["profile"]."','".$_POST["brand"]."')";
		$id = $this -> dbObj -> Insert_ID();
		exit("<script>alert('添加成功');window.location =\"agent.php?action=upd&updid=".$id."\";</script>");
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'] + 0;
			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."agent SET agentname='".$_POST['agentname']."',address='".$_POST["address"]."',zipcode='".$_POST["zipcode"]."',fax='".$_POST["fax"]."',tel='".$_POST["tel"]."',tel1='".$_POST["tel1"]."',handphone='".$_POST["handphone"]."', areaid='".$_POST["areaid"]."', qq='".$_POST["qq"]."',url='".$_POST["url"]."', license='".$_POST["license"]."',industry='".$_POST["industry"]."', userid='".$_POST["userid"]."',email='".$_POST["email"]."',employeesnumber='".$_POST["employeesnumber"]."',profile='".$_POST["profile"]."',brand='".$_POST["brand"]."', establishmentday='".$_POST["establishmentday"]."' WHERE agentid =".$id);
		$this -> quit($info.'成功！');
		}

		
	}
	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');history.go(-1);</script>");
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
		$url = $url."?cgrand=cugle";
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
$main = new Pagevisiteplan();
$main -> Main();
?>