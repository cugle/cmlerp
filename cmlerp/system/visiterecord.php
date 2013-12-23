<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class Pagevisiterecord extends admin {
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

 
		$Sale_ID=$_POST[userid];
		$SubmitValue=$_POST[submit];
		$TheID=$_POST[TheID];
		$TheID=implode(",",$TheID);
		if($SubmitValue=="移至该员工"){
		$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX.'visiterecord SET userid ='.$Sale_ID.' WHERE recordid in('.$TheID.')');
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
		$t -> set_file('f','visiterecord.html');
		$t -> set_block('f','visiterecord','v');
		$t -> set_block('visiterecord','customer','c');
		$t -> set_block('visiterecord','user','u');
		$t -> set_block('visiterecord','plan','p');	
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
		if($dateend!=""&$datebg!=""){$condition=" visitetime BETWEEN '".$datebg." 00:00:00' AND '".$dateend." 23:59:59'";}
		$condition = $condition ? " WHERE ".$condition : "";	
		$condition =$condition." ORDER BY recordid desc";
		if($cateid=='username'){

		$result = $this -> dbObj -> Execute("select v.* from ".WEB_ADMIN_TABPOX."visiterecord v INNER JOIN ".WEB_ADMIN_TABPOX."user u on u.userid=v.userid where u.username like '%".$keywords."%'");	
		}elseif($cateid=='customername'){
		
		$result = $this -> dbObj -> Execute("select v.* from ".WEB_ADMIN_TABPOX."visiterecord v INNER JOIN ".WEB_ADMIN_TABPOX."customer c on c.customerid=v.customerid where c.customername like '%".$keywords."%'");
		}else{
		$result= $this -> dbObj ->Execute("select *  from ".WEB_ADMIN_TABPOX."visiterecord".$condition);
		}
		$count=$result->RecordCount();
		$pageid=$_GET[pageid];
		$pageid = intval($pageid);
		$psize=$this->getValue('pagesize');
		$psize =$psize?$psize:20;
		$offset = $pageid>0?($pageid-1)*$psize:0;

		if($_GET[qgtype]<>""&$_GET[Sale_ID]<>""){
		  $t -> set_var('pagelist',$this -> page("visiterecord.php?qgtype=".$_GET[qgtype]."&Sale_ID=".$_GET[Sale_ID],$count,$psize,$pageid));
		}elseif($cateid!=''){
		$t -> set_var('pagelist',$this -> page("visiterecord.php?cateid=".$cateid."&keywords=".urlencode($keywords),$count,$psize,$pageid));
		}else
		{
        $t -> set_var('pagelist',$this -> page("visiterecord.php",$count,$psize,$pageid));
		}
		
		if($cateid=='username'){
		$rs = $this -> dbObj -> Execute("select v.* from ".WEB_ADMIN_TABPOX."visiterecord v INNER JOIN ".WEB_ADMIN_TABPOX."user u on u.userid=v.userid where  u.username like '%".$keywords."%' LIMIT ".$offset.",".$psize);
		}elseif($cateid=='customername'){
		$rs = $this -> dbObj -> Execute("select v.* from ".WEB_ADMIN_TABPOX."visiterecord v INNER JOIN ".WEB_ADMIN_TABPOX."customer c on c.customerid=v.customerid where c.customername like '%".$keywords."%' LIMIT ".$offset.",".$psize);
		}else{
		$rs = $this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX."visiterecord".$condition." LIMIT ".$offset.",".$psize);
		}
		while ($rrs = &$rs -> FetchRow()) {
			foreach ($rrs as $k=>$v)       $t -> set_var($k,$v);
			$t -> set_var('upd',$this->getUpdStr($rrs['importer'],$rrs['recordid'],'img'));
			$t -> set_var('del',$this->getDelStr($rrs['importer'],$rrs['recordid'],'img'));
			$t -> set_var('g');

            //设置客户
			$t -> set_var($rrs);
			$t -> set_var('c');
			$inrs = &$this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX."customer  where customerid = ".$rrs['customerid']);

			while ($inrrs = &$inrs -> FetchRow()) {
				//$t -> set_var($inrrs);
				$t -> set_var('customername',$inrrs['customername']);
				
				$t -> parse('c','customer',true);
			}
			$inrs -> Close();
	        //设置员工
			$t -> set_var('u');
			$inrs = &$this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX."user WHERE userid = ".$rrs['userid']);

			while ($inrrs = &$inrs -> FetchRow()) {
			    $t -> set_var($inrrs);
				//$t -> set_var('username',$inrrs['username']);
				$t -> parse('u','user',true);
			}
			$inrs -> Close();
            
			$t -> set_var('p');
			$inrs = &$this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX."visiteplan WHERE planid = ".$rrs['planid']);
			while ($inrrs = &$inrs -> FetchRow()) {
			    $t -> set_var($inrrs);
				//$t -> set_var('username',$inrrs['username']);
				$t -> parse('p','plan',true);
			}
			$inrs -> Close();

			$t -> parse('v','visiterecord',true);
		}
		$t -> set_var('datebg',$datebg?$datebg:date("Y-m-d"));			
		$t -> set_var('dateend',$dateend?$dateend:date("Y-m-d"));
			
		$t -> set_var('batchmovedisabled',$this->getAttach('batchmove')==1?"":"disabled='disabled'");	
		$t -> set_var('batchdeletedisabled',$this->getAttach('batchdelete')==1?"":"disabled='disabled'");					
				
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
				 } else {
				  $arr=$arr."<option value='".$rrs['area_id']."'>".$rrs['area_name']."</option>";
				 }
				 $i=$i+1;
				

            }
           return $arr;

	}
	function PPClass_sale($userid=-1){
    		$sale =$this -> dbObj -> Execute("SELECT * FROM ".WEB_ADMIN_TABPOX."grouprole gr, ".WEB_ADMIN_TABPOX."usergroup ug, ".WEB_ADMIN_TABPOX."user u WHERE gr.groupid = ug.groupid AND u.userid = ug.userid AND gr.roleid =2");
			$count=$sale->RecordCount();
			$i=0;
			$arrs="";
			while ($rrs = &$sale -> FetchRow()) {
                 if($userid==$rrs['userid']){
				 
				 $arrs=$arrs."<option value='".$rrs['userid']."' selected>".$rrs['username']."</option>";
				 }elseif($userid==0){
				  $arrs=$arrs."<option value='0'  selected>不详</option>";
				 }
				 else
				 {
				 $arrs=$arrs."<option value='".$rrs['userid']."'>".$rrs['username']."</option>";
				 }
				// $arrs=$arrs."[0,".$rrs['userid'].",'".$rrs['username']."']";
			     //$arr=$arr."[".$rrs['area_parent_id'].",".$rrs['area_id'].",'".$rrs['area_name']."']";
				 $i=$i+1;
				// if ($i<$count){$arrs=$arrs.",";}

            }
           return $arrs;

	}	
	function goDispAppend(){
    	$t = new Template('../template/system');
		$t -> set_file('f','visiterecorddetail.html');
		$t -> set_block('f','visiterecord','vr');		
		$t -> set_block('f','customer','c');
		$t -> set_block('f','gender','g');	
		$t -> set_block('f','sale','s');
		$t -> set_block('f','area','a');	
		$t -> set_block('f','user','u');
		$t -> set_block('f','planstatus','p');
		$t -> set_block('f','importancere','i');
		 
		if($this -> isAppend){
			$planid=$_GET['planid'];

		    $t -> set_var($this -> dbObj ->GetRow("SELECT * FROM  ".WEB_ADMIN_TABPOX."visiteplan  WHERE planid = ".$planid));		
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		
	
		 								
			$inrs3 = $this -> dbObj ->Execute("SELECT c.* FROM  ".WEB_ADMIN_TABPOX."customer c INNER JOIN  ".WEB_ADMIN_TABPOX."visiteplan v ON c.customerid=v.customerid WHERE v.planid = ".$planid);
				while ($inrrs = $inrs3 -> FetchRow()) {
				$t -> set_var($inrrs);
				//$t -> set_var('c');
				//$t -> set_var('customerid',$inrrs['customerid']);								
				//$t -> set_var('customername',$inrrs['customername']);
			$customerid=$inrrs['customerid'];
            $importance='一般';
	
			$t -> set_var('area',$this->PPClass($inrrs['areaid']));	
			$t -> parse('a','area',true);		
			$t -> set_var('sale',$this->PPClass_sale($inrrs['userid']));	
			$t -> parse('s','sale',true);			
				$sex=$inrrs['gender'];
				$t -> parse('c','customer',true);
		     	}
				$inrs3 -> Close();	


			
		
			$t -> set_var('visitetime',date("Y-m-d H:i:s"));
			$inrs4 = &$this -> dbObj ->Execute("SELECT * FROM  ".WEB_ADMIN_TABPOX."user   WHERE userid = ".$this->getUid());
 				while ($inrrs = &$inrs4 -> FetchRow()) {
											
			    $t -> set_var('u');	
				$t -> set_var('userid',$inrrs['userid']);	
				$t -> set_var('username',$inrrs['username']);
				$t -> parse('u','user',true);
		     	}
				$inrs4 -> Close();		
		 									
			//拜访记录
			$inrs6 = &$this -> dbObj ->Execute("SELECT * FROM  ".WEB_ADMIN_TABPOX."visiterecord   WHERE customerid = ".$customerid." ORDER BY recordid desc");
		  
 			$recordstr="";
			while ($inrrs = &$inrs6 -> FetchRow()) {
											
			        $t -> set_var('vr');
					
				//设置执行人
					$inrs10 = &$this -> dbObj ->Execute("SELECT * FROM  ".WEB_ADMIN_TABPOX."user  WHERE userid = ".$inrrs['userid']);
					while($inrrs10 = $inrs10 -> FetchRow()) {
					
					$username=$inrrs10['username'];
				}
					 $inrs10 -> Close();						
					
					
				$recordstr=$recordstr."<tr class='tdBgColor' onMouseOver='TrOver(this)' onMouseOut='TrOut(this)'><td>".$inrrs['recordid']."</td> <td><a href='visiterecord.php?action=upd&updid=".$inrrs[recordid]."'>".$inrrs['plantitle']."</a></td><td>".$inrrs['visiteresult']."</td><td>".$username."<br></td><td>".$inrrs['visitetime']."</td><td>".$inrrs['importance']."</td></tr>";	
				
		     	}
				//计划状态
					$inrs8 = &$this -> dbObj ->Execute("SELECT * FROM  ".WEB_ADMIN_TABPOX."visiteplan   WHERE planid = ".$_GET['planid']);
					while($inrrs8 = $inrs8 -> FetchRow()) {
					
					$planstatus=$inrrs8['planstatus'];
				}
					 $inrs8 -> Close();					
				
				$t -> set_var('visiterecord',$recordstr);
				$t -> parse('vr','visiterecord',true);
		        $inrs6 -> Close();								     

	 		


				
		}else{
			$updid = $_GET[MODIFY.'id'] + 0 ;
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'visiterecord WHERE recordid = '.$updid));
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			
			//客户名称
			$t -> set_var('c');		
			$inrs2 = &$this -> dbObj ->Execute("SELECT c.* FROM  ".WEB_ADMIN_TABPOX."customer c INNER JOIN  ".WEB_ADMIN_TABPOX."visiterecord v ON c.customerid = v.customerid WHERE v.recordid =".$updid);

			while ($inrrs = &$inrs2 -> FetchRow()) {
			    $t -> set_var($inrrs);
				//$t -> set_var($inrrs);
				$customerid=$inrrs['customerid'];								
			   // $t -> set_var('c');	
				//$t -> set_var('customername',$inrrs['customername']);
			
			$t -> set_var('sale',$this->PPClass_sale($inrrs['userid']));	
			$t -> parse('s','sale',true);	
			$t -> set_var('area',$this->PPClass($inrrs['areaid']));	
			$t -> parse('a','area',true);	
				$t -> parse('c','customer',true);
			}
			$inrs2 -> Close();
		
			
			
			
			//执行人名称
			$t -> set_var('u');
			$inrs1 = &$this -> dbObj ->Execute("SELECT u.* FROM  ".WEB_ADMIN_TABPOX."user u INNER JOIN  ".WEB_ADMIN_TABPOX."visiterecord v ON u.userid = v.userid WHERE v.recordid =".$updid);
			while ($inrrs1 = &$inrs1 -> FetchRow()) {
			
				$t -> set_var('username',$inrrs1['username']);
				$t -> parse('u','user',true);
			}
			$inrs1 -> Close();
			
			//拜访记录
			$inrs6 = &$this -> dbObj ->Execute("SELECT * FROM  ".WEB_ADMIN_TABPOX."visiterecord   WHERE customerid = ".$customerid." ORDER BY recordid desc");
		      
 			$recordstr="";
			while ($inrrs = &$inrs6 -> FetchRow()) {
					$planstatus=$inrrs['visitestatus'];		
					$plan_content=$inrrs['plan_content'];
					$t -> set_var('plan_content',$plan_content);				
			        $importance=$inrrs['importance'];
					$inrs7 = &$this -> dbObj ->Execute("SELECT * FROM  ".WEB_ADMIN_TABPOX."user   WHERE userid = ".$inrrs['userid']);
					while($inrrs7 = $inrs7 -> FetchRow()) {
					$username=$inrrs7['username'];
					}
					 $inrs7 -> Close();	
					 
					$inrs8 = &$this -> dbObj ->Execute("SELECT * FROM  ".WEB_ADMIN_TABPOX."visiteplan   WHERE planid = ".$inrrs['planid']);
					while($inrrs8 = $inrs8 -> FetchRow()) {
					
					//$plan_content=$inrrs8['plan_content'];
					
				}
					 $inrs8 -> Close();						 
					 
				$t -> set_var('vr');	
				$recordstr=$recordstr."<tr class='tdBgColor' onMouseOver='TrOver(this)' onMouseOut='TrOut(this)'><td>".$inrrs['recordid']."</td> <td><a href='visiterecord.php?action=upd&updid=".$inrrs[recordid]."'>".$inrrs['plantitle']."</a></td><td>".$inrrs['visiteresult']."</td><td>".$username."<br></td><td>".$inrrs['visitetime']."</td><td>".$inrrs['importance']."</td></tr>";	
				
		     	}
				$t -> set_var('visiterecord',$recordstr);
				$t -> parse('vr','visiterecord',true);
		        $inrs6 -> Close();				
		}
		//echo $this->importancefunc($importance);
		
		$t -> set_var('importancere',$this->importancefunc($importance));	
		$t -> parse('i','importancere',true);				
		$t -> set_var('planstatus',$this->planstatusfunc($planstatus));	
		$t -> parse('p','planstatus',true);				
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> set_var('gender',$this->gender($sex));	
		$t -> parse('g','gender',true);			
		$t -> parse('out','f');
		$t -> p('out');
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
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'visiterecord WHERE recordid in ('.$delid.')');
		$this -> quit('删除成功！');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		if($this -> isAppend){
			$info = '增加';
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."visiterecord(planid,plantitle,customerid,userid,plantime,creattime,visitetime,visiteresult,plan_content,visitememo,importance,visitestatus)values('".$_POST['planid']."','".$_POST['plantitle']."',".$_POST['customerid'].",".$_POST['userid'].",'".$_POST["plantime"]."','".$_POST["creattime"]."','".$_POST["visitetime"]."','".$_POST["visiteresult"]."','".$_POST["plan_content"]."','".$_POST["visitememo"]."','".$_POST["importance"]."','".$_POST["planstatus"]."')");
$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."visiteplan  SET planstatus='".$_POST["planstatus"]."',planmemo='".$_POST["visitememo"]."' WHERE	planid=".$_POST["planid"]);	

			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'] + 0;

			$this -> dbObj -> Execute("UPDATE ".WEB_ADMIN_TABPOX."visiterecord SET plantitle='".$_POST['plantitle']."',customerid=".$_POST["customerid"].",userid=".$_POST["userid"].",plantime='".$_POST["plantime"]."', creattime='".$_POST["creattime"]."', visitetime='".$_POST["visitetime"]."',visiteresult='".$_POST["visiteresult"]."', plan_content='".$_POST["plan_content"]."',visitememo='".$_POST["visitememo"]."',importance ='".$_POST["importance"]."' WHERE recordid =".$id);

		}
		if(isset($_POST['groups']))
		foreach ($_POST['groups'] as $v){
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."visiteplangroup(visiteplanid,groupid,importer)values($id,$v,".$this->getUid().')');
		}
		$this -> quit($info.'成功！');
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
	function planstatusfunc($planstatus='未处理'){
		$arr="";
		 
		if($planstatus=='未处理'){
		$arr=" <option value='未处理'  selected>未处理</option> <option value='需跟进'>需跟进</option><option value='已处理'>已处理</option>";
		}else if($planstatus=='需跟进')
		{$arr="<option value='未处理'>未处理</option> <option value='需跟进' selected>需跟进</option><option value='已处理'>已处理</option>";
		} else if($planstatus=='已处理')
		{$arr="<option value='未处理'>未处理</option> <option value='需跟进' >需跟进</option><option value='已处理'  selected>已处理</option>";
		}
		return $arr;
	}	
	function importancefunc($importance='一般'){
		$arr="";
		echo $planstatus;
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
	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');history.go(-2);</script>");
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
$main = new Pagevisiterecord();
$main -> Main();
?>