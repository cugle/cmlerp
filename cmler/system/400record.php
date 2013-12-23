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
		echo 'UPDATE '.WEB_ADMIN_TABPOX.'400record SET userid ='.$Sale_ID.' WHERE planid in('.$TheID.')';
		//$this -> quit('移动成功！');
		echo "<script language=javascript>history.go(-1);</script>";
		}


    }

	function disp(){
		//定义模板
		$t = new Template('../template/system');
		$t -> set_file('f','400record.html');
		$t -> set_block('f','400record','v');
		//$t -> set_block('400record','customer','c');
		$t -> set_block('400record','user','u');
		$t -> set_block('f','sale','s');
		$t -> set_block('400record','400class','4c');	
		$t -> set_block('f','400class1','4c1');	
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
		$condition =$condition." ORDER BY 400id desc";
		$result= $this -> dbObj ->Execute("select *  from ".WEB_ADMIN_TABPOX."400record".$condition);
		$count=$result->RecordCount();
		$pageid=$_GET[pageid];
		$pageid = intval($pageid);
		$psize=$this->getValue('pagesize');
		$psize =$psize?$psize:20;
		$offset = $pageid>0?($pageid-1)*$psize:0;

		if($_GET[qgtype]<>""&$_GET[Sale_ID]<>""){
		  $t -> set_var('pagelist',$this -> page("400record.php?qgtype=".$_GET[qgtype]."&Sale_ID=".$_GET[Sale_ID],$count,$psize,$pageid));
		}else
		{
        $t -> set_var('pagelist',$this -> page("400record.php",$count,$psize,$pageid));
		}
		//$t -> set_var('pagelist',$this -> page('400record.php',$count,$psize,$pageid));
		$rs = $this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX."400record".$condition." LIMIT ".$offset.",".$psize);
		while ($rrs = &$rs -> FetchRow()) {
			foreach ($rrs as $k=>$v)       $t -> set_var($k,$v);
			$t -> set_var('upd',$this->getUpdStr($rrs['importer'],$rrs['400id'],'img'));
			$t -> set_var('del',$this->getDelStr($rrs['importer'],$rrs['400id'],'img'));
			$t -> set_var('g');


			//设置分类
			//$t -> set_var('4c');
	
			$inrs5 = &$this -> dbObj ->Execute("SELECT c.* FROM  ".WEB_ADMIN_TABPOX."400class c INNER JOIN  ".WEB_ADMIN_TABPOX."400record r ON c.classid = r.classid WHERE r.400id =".$rrs['400id']);
			while ($inrrs = &$inrs5 -> FetchRow()) {
				$t -> set_var('4c');
				//$t -> set_var('classnamelist',$this->classnamelist($_GET['cateid']=='classid'?$_GET['keywords']:''));	
				$t -> set_var('classname',$inrrs['classname']);		
				$t -> parse('4c','400class',true);	
			}
			$inrs5 -> Close();	
/*
			$t -> set_var($rrs);
			$t -> set_var('c');
			$inrs = &$this -> dbObj -> Execute("select c.* from ".WEB_ADMIN_TABPOX."customer c inner join ".WEB_ADMIN_TABPOX."400record r  on r.customerid=c.customerid where r.400id = ".$rrs['400id']);
			
			while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> parse('c','customer',true);
			}
			$inrs -> Close();
*/			
			$t -> set_var('u');
			$inrs = &$this -> dbObj -> Execute("select u.* from ".WEB_ADMIN_TABPOX."user u inner join ".WEB_ADMIN_TABPOX."400record r on r.userid=u.userid where r.400id = ".$rrs['400id']);

			while ($inrrs = &$inrs -> FetchRow()) {
			    $t -> set_var($inrrs);
				//$t -> set_var('username',$inrrs['username']);
				$t -> parse('u','user',true);
			}
			$inrs -> Close();

			$t -> set_var('processstate', ($rrs['processstate']=='未处理')?'<font color=red>未处理</font>':$rrs['processstate']);	
			$t -> parse('v','400record',true);
		}
		//设置国分类列表
		$t -> set_var('4c1');
		$t -> set_var('classnamelist',$this->classnamelist($_GET['cateid']=='classid'?$_GET['keywords']:''));		
		$t -> parse('4c1','400class1',true);	

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
		$t -> set_file('f','400recorddetail.html');
		
		//$t -> set_block('f','customer','c');
		$t -> set_block('f','user','u');
		$t -> set_block('f','400class','4c');
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
			
		
				
		}else{
			$updid = $_GET[MODIFY.'id'] + 0 ;
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'400record WHERE 400id = '.$updid));
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
			$inrs1 = &$this -> dbObj ->Execute("SELECT u.* FROM  ".WEB_ADMIN_TABPOX."user u INNER JOIN  ".WEB_ADMIN_TABPOX."400record r ON u.userid = r.userid WHERE r.400id =".$updid);
			while ($inrrs1 = &$inrs1 -> FetchRow()) {
				$t -> set_var('username',$inrrs1['username']);
				$t -> parse('u','user',true);
			}
			$inrs1 -> Close();
			


		}
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] ;
		echo 'DELETE FROM '.WEB_ADMIN_TABPOX.'400record WHERE 400id in('.$delid.')';
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'400record WHERE 400id in('.$delid.')');
		$this -> quit('删除成功！');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		if($this -> isAppend){
			$info = '增加';
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."400record(customerid,customername,address,tel,handphone,content,result,callersatisfaction,creattime,processstate,userid ,classid,classname)values('".$_POST['customerid']."','".$_POST['customername']."','".$_POST['address']."','".$_POST['tel']."','".$_POST['handphone']."','".$_POST['content']."','".$_POST['result']."','".$_POST["callersatisfaction"]."','".$_POST["creattime"]."','".$_POST["processstate"]."','".$_POST["userid"]."','".$_POST["classid"]."','".$_POST["classname"]."')");
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'] + 0;
			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."400record SET customerid='".$_POST['customerid']."',customername='".$_POST['customername']."',address='".$_POST["address"]."',tel='".$_POST["tel"]."',handphone='".$_POST["handphone"]."', content='".$_POST["content"]."', result='".$_POST["result"]."',callersatisfaction='".$_POST["callersatisfaction"]."', creattime='".$_POST["creattime"]."', processstate='".$_POST["processstate"]."', userid=".$_POST["userid"].", classid=".$_POST["classid"].", classname='".$_POST["classname"]."' WHERE 400id =".$id);

		}

		$this -> quit($info.'成功！');
	}
	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');location.href='400record.php';</script>");
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