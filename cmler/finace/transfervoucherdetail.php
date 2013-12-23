<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
    function Main()
    {
        if(isset($_GET['action']) && $_GET['action']=='detaillist')
        {
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> detaillist();
        }else
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
		$t = new Template('../template/finace');
		$t -> set_file('f','transfervoucherdetailselectdate.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_var('objecttypelist',$this->selectlist('objecttype','objecttype_id','objecttype_name',''));	
		$t -> set_var('accounttitlelist',$this->selectlist1('accounttitle','accounttitle_no','accounttitle_name',''));	
		
		$t -> set_var('enddate','');
		$t -> set_var('bgdate','');
		$t -> set_var('add',$this -> getAddStr('img'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');		
		}
	function detaillist(){
		//定义模板
	
		$t = new Template('../template/finace');
		$t -> set_file('f','transfervoucherdetail.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','main','m');
		$t -> set_block('main','objectlist','ol');
		$t -> set_block('objectlist','mainlist','ml');
		
		//机构条件
		$agencyid=$_SESSION["currentorgan"];
		$agencyidcondition="  agencyid  in (".$agencyid.")";
		$agencyidcondition1="  B.agencyid  in (".$agencyid.")";
		$fromtype=array("sell","sell","sell","sell","purchase","purchreturn","takestock","lossregister","lingyong");
		
		$viewbillfunction=array("viewsellbill","viewsellbill","viewsellbill","viewsellbill","viewpurchbill","viewpurchreturnbill","viewcheckstockbill","viewlossregisterbill","viewlingyongbill");
		$billname=array("销货单","销货单","销货单","销货单","进货单","采购退货单","盘点单","报损单","领用单");
		
		//$t -> set_var('frombill',$billname[$inrrs["fromtype"]].'<a href=#  onclick="'.$viewbillfunction[$inrrs["fromtype"]].'('.$inrrs["frombillid"].')">'.$billno.'</a>');
		//搜索 
       // $accounttitle_id=$_GET["accounttitle_id"];
		$bgaccounttitle_no=$_GET["bgaccounttitle_no"];
		$endaccounttitle_no=$_GET["endaccounttitle_no"];
		$objecttype_id=$_GET["objecttype_id"];
		//$objectid=$_GET["objectid"];
		$bgobject_no=$_GET["bgobject_no"];
		$endobject_no=$_GET["endobject_no"];
		$bgdate=$_GET["bgdate"];
		$enddate=$_GET["enddate"];

		$lend=0;
		$loan=0;
		$totalbglend=0;
		$totalbgloan=0;
		$totalbgbalance=0;			
		$t -> set_var('bgdate',$bgdate);
		$t -> set_var('enddate',$enddate);
		$t -> set_var('bgobject_no',$bgobject_no);
		$t -> set_var('endobject_no',$endobject_no);
		
		
		$monthlybatch=$this->dbObj -> GetRow("SELECT * FROM `s_monthlybatch` WHERE `enddate`<'".$bgdate."' and agencyid =".$_SESSION['currentorgan']." order by monthlybatch_id desc");
		
		$dategapbg=$monthlybatch['enddate'];
		$monthlybatch_id=$monthlybatch['monthlybatch_id'];		
		
		$t -> set_var('objecttypelist',$this->selectlist('objecttype','objecttype_id','objecttype_name',$objecttype_id));	
		$t -> set_var('bgaccounttitlelist',$this->selectlist1('accounttitle','accounttitle_no','accounttitle_name',$bgaccounttitle_no));	
		$t -> set_var('endaccounttitlelist',$this->selectlist1('accounttitle','accounttitle_no','accounttitle_name',$endaccounttitle_no));	
		$accounttitle=$this->dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_no  between "'.$bgaccounttitle_no.'" and "'.$endaccounttitle_no.'"');
		 
		//$objecttype=$this -> dbObj -> GetRow('select objecttypetable  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id='.$objecttype_id);
		$objecttype=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id='.$objecttype_id);
		$objecttypetable =	$objecttype['objecttypetable'];
		$objecttype_name =	$objecttype['objecttype_name'];
				 
		
		 
		$t -> set_var('m');
		
		while ($inrrsaccounttitle = $accounttitle -> FetchRow()) {//循环科目
	 
			$t -> set_var('objecttype_name',$objecttype_name);
			$t -> set_var('accounttitle_name',$inrrsaccounttitle['accounttitle_name']);
			$t -> set_var('accounttitle_no',$inrrsaccounttitle['accounttitle_no']);
			$inrrsaccounttitle['accounttitle_name']='';
			
			

			
			
 
			
			
			
			
			
			$t -> set_var('ol');
			$object=$this->dbObj -> Execute('SELECT *,'.$objecttypetable.'_id as id ,'.$objecttypetable.'_name as name  FROM '.WEB_ADMIN_TABPOX.$objecttypetable.' WHERE '.$objecttypetable.'_no  between "'.$bgobject_no.'" and "'.$endobject_no.'"');
			while ($inrrobject = $object -> FetchRow()) {//循环对象
			
			$flag1=0;
			
			
			
			//期初余额
			$sqlbg='select *  from '.WEB_ADMIN_TABPOX.'transfervoucherhistory  where  accounttitle_id= '.$inrrsaccounttitle["accounttitle_id"].' and objecttype_id='.$objecttype_id.' and objectid='.$inrrobject["id"].' and  monthlybatch_id="'.$monthlybatch_id.'"  and '.$agencyidcondition;
			
			$sqlgap='select sum(A.lend) as  lend, sum(A.loan) as  loan  from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id where  A.accounttitle_id= '.$inrrsaccounttitle["accounttitle_id"].'  and   B.date between "'.$dategapbg.'" and "'.$bgdate.'" and '.$agencyidcondition1;//间隙时间	
		$bgdata=$this -> dbObj -> GetRow($sqlbg);	
 		$gapdata=$this -> dbObj -> GetRow($sqlgap);	
		if($bgdata or $gapdata['lend']<>'' ){$flag1=1;}else{$flag1=0;}
		$bglend=$bgdata['lend']==''?0:$bgdata['lend'];
		$bgloan=$bgdata['loan']==''?0:$bgdata['loan'];
 		$bgbalance=$bgdata['lend']-$bgdata['loan'];
		
		$gaplend=$gapdata['lend']==''?0:$gapdata['lend'];
		$gaploan=$gapdata['loan']==''?0:$gapdata['loan'];
		
		$bglend=$bglend+$gaplend;
		
		$bgloan=$bgloan+$gaploan;
		$bgbalance=$bgbalance+$gaplend-$gaploan;

	
		$t -> set_var('bglend',$bglend);
		$t -> set_var('bgloan',$bgloan);	
		$bgbalance<0?$t -> set_var('bglendloan','贷'):$t -> set_var('bglendloan','借');	
		$t -> set_var('bgbalance',abs($bgbalance));				
			
			
			
			
			

			
			$flag=0;
				
			$t -> set_var('object_name',$inrrobject['name']);
			 $sql='select A.*,C.*,A.memo as vmemo , A.agencyid AS agencyid,A.lend as alend ,A.loan as aloan from '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'accounttitle B ON A.accounttitle_id=B.accounttitle_id  INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher C ON A.transfervoucher_id=C.transfervoucher_id  where  A.agencyid ='.$_SESSION["currentorgan"].' and  A.accounttitle_id='.$inrrsaccounttitle["accounttitle_id"].'  and objectid='.$inrrobject["id"].' and C.date  between  "'.$bgdate.'" and "'.$enddate.'"';
		    
		   $detail=$this->dbObj -> Execute($sql);
		  
			$t -> set_var('ml');
			$temlend=0;
			$temloan=0;			
			while ($inrrdetail = $detail -> FetchRow()) {//循环明细
			$t -> set_var($inrrdetail);
			$lend=$lend+$inrrdetail['alend'];
			
			$loan=$loan+$inrrdetail['aloan'];
				
			$t -> set_var('lend',$inrrdetail['alend']);
			$t -> set_var('loan',$inrrdetail['aloan']);
			$t -> set_var('transfervoucher_no',$inrrdetail['transfervoucher_no']);
			
			
				$billno=$this->dbObj -> GetOne("SELECT ".$fromtype[$inrrdetail['fromtype']]."_no FROM ".WEB_ADMIN_TABPOX.$fromtype[$inrrdetail['fromtype']]." WHERE ".$fromtype[$inrrdetail['fromtype']]."_id= ".$inrrdetail['frombillid']);
				$frombill=$billname[$inrrdetail["fromtype"]].'<a style=" cursor: hand" onclick="'.$viewbillfunction[$inrrdetail["fromtype"]].'('.$inrrdetail["frombillid"].')">'.$billno.'</a>';
				
				$temlend=$temlend+$inrrdetail['alend'];
				$temloan=$temloan+$inrrdetail['aloan'];	
				
				($bgbalance+$temlend-$temloan)<0?$t -> set_var('lendloan','贷'):$t -> set_var('lendloan','借');
				$t -> set_var('balance', abs($bgbalance+$temlend-$temloan));		
				
				
				
				
				
				
				
				$t -> set_var('frombill',$frombill);			
			
			
			
			$t -> set_var('angecy_name',$this->dbObj -> GetOne('SELECT agency_name FROM '.WEB_ADMIN_TABPOX.'agency WHERE agency_id = '.$inrrdetail['agencyid']));
			$t -> parse('ml','mainlist',true);
			$flag=1;
			}

			if($flag==1 or $flag==1){
			
			$totalbglend=$totalbglend+$bglend;
			$totalbgloan=$totalbgloan+$bgloan;
			$totalbgbalance=$totalbgbalance+$bgbalance;	
				
			$t -> parse('ol','objectlist',true);
			}
			
			
			}
			 
			$t -> parse('m','main',true);
			 

			$totalbgbalance=$totalbgbalance+$lend-$loan;
			
			 
			$t -> set_var('totallend',$lend);	
			$t -> set_var('totalloan',$loan);	
			$totalbalance<0?$t -> set_var('totallendloan','贷'):$t -> set_var('totallendloan','借');	
			$t -> set_var('totalbalance',$totalbgbalance);	
		}
		
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');		
		$t -> set_var('add',$this -> getAddStr('img'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		

	}
	function goDispAppend(){

		$t = new Template('../template/basic');
		$t -> set_file('f','room_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');

		$t -> set_var('roomgroup',$this->roomgroup());	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('room_no',"");
		$t -> set_var('room_name',"");
		$t -> set_var('principal',"");
		$t -> set_var('memo',"");
		$t -> set_var('beds',"");
		$t -> set_var('tel',"");
		$t -> set_var('createtime',date("Y-m-d H:i:s"));
		$t -> set_var('userid',$this->getUid());		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail WHERE transfervoucherdetail_id = '.$updid));			
			$t -> set_var('error',"");	
			//$t -> set_var('roomgroup',$this->roomtype());	
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$inrs = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail WHERE transfervoucherdetail_id = '.$updid);	
			while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('roomgroup',$this->roomgroup($inrrs['roomgroup_id']));
			  }
			$inrs -> Close();
		}
		$t -> set_var('showeditdiv',"");	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}
	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail WHERE transfervoucherdetail_id in('.$delid.')');
		if(mysql_affected_rows())
		$this -> quit('删除成功！');
	else
		$this -> quit('删除失败！');
		
	}
	function goAppend(){
		$id = 0;
		$info = '';
		
		if($this -> isAppend){
			$info = '增加';	
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."room` (`room_name` ,`room_no` ,`memo` ,`agencyid` ,`beds` ,`principal`,`tel`,`roomgroup_id`)VALUES ( '".$_POST["room_name"]."', '".$_POST["room_no"]."',  '".$_POST["memo"]."','".$_SESSION["currentorgan"]."', '".$_POST["beds"]."', '".$_POST["principal"]."', '".$_POST["tel"]."',".$_POST["roomgroup_id"].")");
//echo "INSERT INTO `".WEB_ADMIN_TABPOX."room` (`room_name` ,`room_no` ,`memo` ,`agencyid` ,`beds` ,`principal`,`tel`，roomgroup_id )VALUES ( '".$_POST["room_name"]."', '".$_POST["room_no"]."',  '".$_POST["memo"]."','".$_SESSION["currentorgan"]."', '".$_POST["beds"]."', '".$_POST["principal"]."', '".$_POST["tel"]."',".$_POST["roomgroup_id"].")";
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];

			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."transfervoucherdetail SET room_name='".$_POST["room_name"]."', room_no='".$_POST["room_no"]."', memo='".$_POST["memo"]."',beds='".$_POST["beds"]."',principal='".$_POST["principal"]."',tel='".$_POST["tel"]."',roomgroup_id=".$_POST["roomgroup_id"]." WHERE transfervoucherdetail_id =".$id);

		}
//$this -> quit($info.'成功！');
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
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
		function selectlist($table,$id,$name,$selectid=0){
	
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table  );
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
		
		function selectlist1($table,$id,$name,$selectid=0){
			$no=$table."_no";
			
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table  );
			$str='';
	     	while ($inrrs = &$inrs -> FetchRow()) {
			
			if ($inrrs[$id]==$selectid)
			$str =$str."<option value=".$inrrs[$id]." selected>".$inrrs[$no].$inrrs[$name]."</option>";	
			else
			$str =$str."<option value=".$inrrs[$id].">".$inrrs[$no].$inrrs[$name]."</option>";			
			}
			$inrs-> Close();	
			return  $str;	
	    }		
	function roomgroup($roomgroup_id=0){
		$arr="";
			$inrs = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'roomgroup WHERE agencyid ='.$_SESSION["currentorgan"]);	
			while ($inrrs = &$inrs -> FetchRow()) {
				if($roomgroup_id==$inrrs['roomgroup_id']){				
				$arr =$arr."<option value='".$inrrs['roomgroup_id']."' selected>".$inrrs['roomgroup_name']."</option>";
				}elseif($inrrs['roomgroup_id']==0){
				$arr =$arr."<option value='0' selected> 请选择房间组</option>";
				}
				else{
				$arr  =$arr."<option value='".$inrrs['roomgroup_id']."' >".$inrrs['roomgroup_name']."</option>";
				}
			  }
			
			$inrs -> Close();		
		

		return $arr;
	}
	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');location.href='room.php';</script>");
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
  