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
		$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX.'400record SET userid ='.$Sale_ID.' WHERE 400id in('.$TheID.')');
		//echo 'UPDATE '.WEB_ADMIN_TABPOX.'400record SET userid ='.$Sale_ID.' WHERE planid in('.$TheID.')';
		//$this -> quit('移动成功！');
		echo "<script language=javascript>history.go(-1);</script>";
		}


    }

	function disp(){
		$t = new Template('../template/system');
		$t -> set_file('f','myagentdetail.html');
		$t -> set_block('f','contact','c');
		//$t -> set_block('f','customer','c');
		$t -> set_block('f','user','u');
		//$t -> set_block('f','400class','4c');
		$t -> set_block('f','diqu','d');
				
			$inrs8 = &$this -> dbObj ->Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'agent WHERE userid = '.$this ->getUid());
			 
			while ($inrrs8 = &$inrs8 -> FetchRow()) {

				$updid=$inrrs8['agentid'];	

			}	
			$inrs8 -> Close();	
			
			$updid = $updid?$updid:$_GET[MODIFY.'id'] + 0 ;		
			 
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'agent WHERE userid = '.$this ->getUid()));
			//$t -> set_var('updid',$updid);
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
			$inrs1 = &$this -> dbObj ->Execute("SELECT u.* FROM  ".WEB_ADMIN_TABPOX."user u INNER JOIN  ".WEB_ADMIN_TABPOX."agent a ON u.userid = a.userid WHERE a.userid =".$this ->getUid());
			if($inrs1->RecordCount()==0){
			echo "<script>window.location =\"myagent.php?action=add\";</script>";
			}
			while ($inrrs1 = &$inrs1 -> FetchRow()) {
				$t -> set_var($inrrs1);
				$t -> parse('u','user',true);
			}
			$inrs1 -> Close();
			//设置地区列表
			$inrs7 = &$this -> dbObj ->Execute("SELECT a.* FROM  ".WEB_ADMIN_TABPOX."area a INNER JOIN  ".WEB_ADMIN_TABPOX."agent ag ON a.area_id = ag.areaid WHERE ag.agentid =".$updid);
			while ($inrrs = &$inrs7 -> FetchRow()) {
				$area_id=$inrrs['area_id'];	
			}
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
		$t -> set_var('diqu',$this->PPClass($area_id));		
		$t -> parse('d','diqu',true); 
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
	function PPClass_sale(){
    		$sale =$this -> dbObj -> Execute('Select * From '.WEB_ADMIN_TABPOX.'user');
			$count=$sale->RecordCount();
			$i=0;
			$arrs="";
			while ($rrs = &$sale -> FetchRow()) {
			if ($_GET['cateid']=='userid'&$_GET['keywords']==$rrs['userid']){
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
		$t -> set_file('f','myagentdetail.html');
		//$t -> set_block('f','customer','c');
		$t -> set_block('f','user','u');
		//$t -> set_block('f','400class','4c');
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
			
			//联系人
			$inrs6 = &$this -> dbObj ->Execute("SELECT c.* FROM  ".WEB_ADMIN_TABPOX."contact c INNER JOIN  ".WEB_ADMIN_TABPOX."agentcontact  ac ON c.contactid=ac.contactid WHERE ac.agentid = ".$updid);
    		$recordstr="";
			while ($inrrs = &$inrs6 -> FetchRow()) {
											
			        $t -> set_var('c');
				$recordstr=$recordstr."<tr class='tdBgColor' onMouseOver='TrOver(this)' onMouseOut='TrOut(this)'><td>".$inrrs['contactid']."</td> <td><a href=contact.php?action=upd&updid=".$inrrs[contactid].">".$inrrs['contactname']."</a></td><td>".$inrrs['tel']."</td><td>".$inrrs['handphone']."<br></td><td>".$inrrs['address']."</td><td>".$inrrs['email']."</td><td>".$inrrs['email']."</td><td><a href=contact.php?action=upd&updid=".$inrrs[contactid].">编辑</a></td><td><a href=contact.php?action=del&delid=".$inrrs[contactid]."&agentid=".$updid.">删除</a></td></tr>";	
				
		     	}
				$recordstr=$recordstr."<tr class='tdBgColor'><td colspan='11'><input name='button' type=button onclick=window.location.href('contact.php?action=add&agentid=".$updid."') value='添加联系人'></td></tr>";
				

		}
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] + 0;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'agent WHERE agentid='.$delid);
		$this -> quit('删除成功！');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		if($this -> isAppend){
			$info = '增加';
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."agent(agentname,address,zipcode,fax,tel,tel1,email,qq,areaid,url,license,industry,employeesnumber,establishmentday,profile,brand,userid)values('".$_POST['agentname']."','".$_POST['address']."','".$_POST['zipcode']."','".$_POST['fax']."','".$_POST['tel']."','".$_POST['tel1']."','".$_POST['email']."','".$_POST['qq']."','".$_POST["areaid"]."','".$_POST["url"]."','".$_POST["license"]."','".$_POST["industry"]."','".$_POST["employeesnumber"]."','".$_POST["establishmentday"]."','".$_POST["profile"]."','".$_POST["brand"]."','".$_POST["userid"]."')");
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'] + 0;
			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."agent SET agentname='".$_POST['agentname']."',address='".$_POST["address"]."',zipcode='".$_POST["zipcode"]."',fax='".$_POST["fax"]."',tel='".$_POST["tel"]."',tel1='".$_POST["tel1"]."',handphone='".$_POST["handphone"]."', areaid='".$_POST["areaid"]."', qq='".$_POST["qq"]."',url='".$_POST["url"]."', license='".$_POST["license"]."',industry='".$_POST["industry"]."', userid='".$_POST["userid"]."',email='".$_POST["email"]."',employeesnumber='".$_POST["employeesnumber"]."',profile='".$_POST["profile"]."',brand='".$_POST["brand"]."', establishmentday='".$_POST["establishmentday"]."' WHERE agentid =".$id);
		}

		$this -> quit($info.'成功！');
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