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
		$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX.'visiteplan SET userid ='.$Sale_ID.' WHERE planid in('.$TheID.')');
		//$this -> quit('移动成功！');
		echo "<script language=javascript>history.go(-1);</script>";
		}


    }

	function disp(){
		//定义模板
				if( !$_SERVER['HTTP_REFERER']){
			echo   "对不起，本页不允许直接访问";   
  			exit;   

          }		
		$t = new Template('../template/system');
		$t -> set_file('f','visiteplan.html');
		$t -> set_block('f','visiteplan','v');
		$t -> set_block('visiteplan','customer','c');
		$t -> set_block('visiteplan','user','u');
		$t -> set_block('f','sale','s');	
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
		if($dateend!=""&$datebg!=""){$condition=" plantime BETWEEN '".$datebg." 00:00:00' AND '".$dateend." 23:59:59'";}
		$condition = $condition ? " WHERE ".$condition : "";	
		$condition =$condition." ORDER BY planid desc";	
		if($cateid=='username'){

		$result = $this -> dbObj -> Execute("select v.* from ".WEB_ADMIN_TABPOX."visiteplan v INNER JOIN ".WEB_ADMIN_TABPOX."user u on u.userid=v.userid where u.username like '%".$keywords."%'");	
		}elseif($cateid=='customername'){
		
		$result = $this -> dbObj -> Execute("select v.* from ".WEB_ADMIN_TABPOX."visiteplan v INNER JOIN ".WEB_ADMIN_TABPOX."customer c on c.customerid=v.customerid where c.customername like '%".$keywords."%'");
		}else{
		$result= $this -> dbObj ->Execute("select *  from ".WEB_ADMIN_TABPOX."visiteplan".$condition);
		}
		$count=$result->RecordCount();
		$pageid=$_GET[pageid];
		$pageid = intval($pageid);
		$psize=$this->getValue('pagesize');
		$psize =$psize?$psize:20;
		$offset = $pageid>0?($pageid-1)*$psize:0;
	
		if($_GET[qgtype]<>""&$_GET[Sale_ID]<>""){
		  $t -> set_var('pagelist',$this -> page("visiteplan.php?qgtype=".$_GET[qgtype]."&Sale_ID=".$_GET[Sale_ID],$count,$psize,$pageid));
		}elseif($cateid!=''){
		$t -> set_var('pagelist',$this -> page("visiteplan.php?cateid=".$cateid."&keywords=".urlencode($keywords),$count,$psize,$pageid));
		}
		else
		{
        $t -> set_var('pagelist',$this -> page("visiteplan.php",$count,$psize,$pageid));
		}
		//$t -> set_var('pagelist',$this -> page('visiteplan.php',$count,$psize,$pageid));
		
		if($cateid=='username'){
		$rs = $this -> dbObj -> Execute("select v.* from ".WEB_ADMIN_TABPOX."visiteplan v INNER JOIN ".WEB_ADMIN_TABPOX."user u on u.userid=v.userid where  u.username like '%".$keywords."%' LIMIT ".$offset.",".$psize);
		}elseif($cateid=='customername'){

		$rs = $this -> dbObj -> Execute("select v.* from ".WEB_ADMIN_TABPOX."visiteplan v INNER JOIN ".WEB_ADMIN_TABPOX."customer c on c.customerid=v.customerid where c.customername like '%".$keywords."%' LIMIT ".$offset.",".$psize);
		}else{
		$rs = $this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX."visiteplan".$condition." LIMIT ".$offset.",".$psize);
		}
		while ($rrs = &$rs -> FetchRow()) {
			foreach ($rrs as $k=>$v)       $t -> set_var($k,$v);
			$t -> set_var('upd',$this->getUpdStr($rrs['importer'],$rrs['planid'],'img'));
			$t -> set_var('del',$this->getDelStr($rrs['importer'],$rrs['planid'],'img'));
			$t -> set_var('g');


			$t -> set_var($rrs);
			$t -> set_var('c');
			$inrs = &$this -> dbObj -> Execute("select c.* from ".WEB_ADMIN_TABPOX."customer c inner join ".WEB_ADMIN_TABPOX."visiteplan vp  on vp.customerid=c.customerid where vp.planid = ".$rrs['planid']);
			while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> parse('c','customer',true);
			}
			$inrs -> Close();
			$t -> set_var('u');
			$inrs = &$this -> dbObj -> Execute("select u.* from ".WEB_ADMIN_TABPOX."user u inner join ".WEB_ADMIN_TABPOX."visiteplan vp on vp.userid=u.userid where vp.planid = ".$rrs['planid']);

			while ($inrrs = &$inrs -> FetchRow()) {
			    $t -> set_var($inrrs);
				//$t -> set_var('username',$inrrs['username']);
				$t -> parse('u','user',true);
			}
			$inrs -> Close();

			$t -> set_var('planstatus', ($rrs['planstatus']=='未处理')?'<font color=red>未处理</font>':$rrs['planstatus']);	
			$t -> parse('v','visiteplan',true);
			$t -> set_var('visitedisabled',$rrs['planstatus']=='未处理'?"":"disabled='disabled'");	
			
		}
		$t -> set_var('sale',$this->PPClass_sale());	
		$t -> parse('s','sale',true);	
		$t -> set_var('batchmovedisabled',$this->getAttach('batchmove')==1?"":"disabled='disabled'");	
		$t -> set_var('batchdeletedisabled',$this->getAttach('batchdelete')==1?"":"disabled='disabled'");	
		

		$t -> set_var('datebg',$datebg?$datebg:date("Y-m-d"));			
		$t -> set_var('dateend',$dateend?$dateend:date("Y-m-d"));	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function PPClass_sale($userid=0){
    		$sale =$this -> dbObj -> Execute("SELECT * FROM ".WEB_ADMIN_TABPOX."grouprole gr, ".WEB_ADMIN_TABPOX."usergroup ug, ".WEB_ADMIN_TABPOX."user u WHERE gr.groupid = ug.groupid AND u.userid = ug.userid AND gr.roleid in(2)");
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
	function goDispAppend(){

		$t = new Template('../template/system');
		$t -> set_file('f','visiteplandetail.html');
		$t -> set_block('f','customer','c');
		$t -> set_block('f','user','u');
		$t -> set_block('f','importancere','i');
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
			
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

		    $importance='一般';
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
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'visiteplan WHERE planid = '.$updid));
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			

			//客户名称
			$t -> set_var('c');

			
			
			$inrs2 = &$this -> dbObj ->Execute("SELECT c.* FROM  ".WEB_ADMIN_TABPOX."customer c INNER JOIN  ".WEB_ADMIN_TABPOX."visiteplan v ON c.customerid = v.customerid WHERE v.planid =".$updid);
			while ($inrrs = &$inrs2 -> FetchRow()) {
				$t -> set_var('customername',$inrrs['customername']);
				$t -> parse('c','customer',true);
			}
			$inrs2 -> Close();
		
			
			
			
			//执行人名称
			$t -> set_var('u');
			$inrs1 = &$this -> dbObj ->Execute("SELECT u.* FROM  ".WEB_ADMIN_TABPOX."user u INNER JOIN  ".WEB_ADMIN_TABPOX."visiteplan v ON u.userid = v.userid WHERE v.planid =".$updid);
			while ($inrrs1 = &$inrs1 -> FetchRow()) {
				$t -> set_var('username',$inrrs1['username']);
				$t -> parse('u','user',true);
			}
			$inrs1 -> Close();
			
			//重要程度
			
			$inrs4 = &$this -> dbObj ->Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'visiteplan WHERE planid = '.$updid);
			while ($inrrs = &$inrs4 -> FetchRow()) {
				$importance=$inrrs['planimportance'];
			}
			$inrs4 -> Close();			
         

		}
		$t -> set_var('i');
		$t -> set_var('importancere',$this->importancefunc($importance));	
		$t -> parse('i','importancere',true);		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function importancefunc($importance='一般'){
		$arr="";
		if($importance=='一般'){
		$arr=" <option value='一般'  selected>一般</option> <option value='重要'>重要</option><option value='很重要'>很重要</option><option value='紧急'>紧急</option><option value='不重要'>不重要</option>";
		}else if($importance=='重要')
		{$arr="<option value='一般' >一般</option> <option value='重要'  selected>重要</option><option value='很重要'>很重要</option><option value='紧急'>紧急</option><option value='不重要'>不重要</option>";
		} else if($importance=='很重要')
		{$arr="<option value='一般' >一般</option> <option value='重要'>重要</option><option value='很重要' selected>很重要</option><option value='紧急'>紧急</option><option value='不重要'>不重要</option>";
		}else if($importance=='紧急')
		{$arr="<option value='一般' >一般</option> <option value='重要'>重要</option><option value='很重要' >很重要</option><option value='紧急' selected>紧急</option><option value='不重要'>不重要</option>";
		}
		else if($importance=='不重要')
		{$arr="<option value='一般' >一般</option> <option value='重要'>重要</option><option value='很重要' selected>很重要</option><option value='紧急' selected>紧急</option><option value='不重要' selected>不重要</option>";
		}
		return $arr;
	}
	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
				if( !$_SERVER['HTTP_REFERER']){
			echo   "对不起，本页不允许直接访问";   
  			exit;   

          }
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'visiteplan WHERE planid in ('.$delid.')');
		$this -> quit('删除成功！');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		if($this -> isAppend){
			$info = '增加';
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."visiteplan(plantitle,customerid,userid,plantime,plan_content,planmemo,planimportance)values('".$_POST['plantitle']."',".$_POST['customerid'].",".$_POST['userid'].",'".$_POST["plantime"]."','".$_POST["plan_content"]."','".$_POST["planmemo"]."','".$_POST['planimportance']."')");

			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'] + 0;
			if($_POST['password']){
				$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."visiteplan SET visiteplanname='".$_POST['visiteplanname']."',visiteplanpass='".md5($_POST['password'])."' WHERE visiteplanid =".$id);
			}else{ 
				$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."visiteplan SET plantitle='".$_POST['plantitle']."',customerid=".$_POST["customerid"].",userid=".$_POST["userid"].",plantime='".$_POST["plantime"]."', creattime='".$_POST["creattime"]."', plan_content='".$_POST["plan_content"]."',planmemo='".$_POST["planmemo"]."',planimportance='".$_POST['planimportance']."' WHERE planid =".$id);

			}
			if(isset($_POST['groups']))
			$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'visiteplangroup WHERE visiteplanid = '.$id);
		}
		if(isset($_POST['groups']))
		foreach ($_POST['groups'] as $v){
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."visiteplangroup(visiteplanid,groupid,importer)values($id,$v,".$this->getUid().')');
		}
		$this -> quit($info.'成功！');
	}
	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');location.href='visiteplan.php';</script>");
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