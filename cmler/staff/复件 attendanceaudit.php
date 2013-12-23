<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {

    function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='viewhistorty')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> viewhistorty();
        } else if(isset($_GET['action']) && $_GET['action']=='savetohistory')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> savetohistory();
        } else if(isset($_GET['action']) && $_GET['action']=='audit')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> audit();
        }
        else
        {
            parent::Main();
        }
    }
    function viewhistorty(){
		//定义模板

		$t = new Template('../template/staff');
		$t -> set_file('f','attendancehistory.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');	
		$sql='select * from '.WEB_ADMIN_TABPOX.'attendancehistory where  agencyid ='.$_SESSION["currentorgan"]." and month=".$_GET['batch'];
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute($sql);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				
				
				$data = $this -> dbObj ->GetRow("select * from ".WEB_ADMIN_TABPOX."employee where employee_id=".$inrrs['employee_id']." and  agencyid =".$_SESSION["currentorgan"]);
				$t -> set_var($data);
				$data1= $this -> dbObj ->GetRow("select * from ".WEB_ADMIN_TABPOX."employeelevel where employeelevel_id=".$data['employeelevelid']);
				$t -> set_var($data1);
				$t -> set_var('clearot',$this -> clearotlist($inrrs['attendance_id'],$inrrs['clearot']));
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['attendance_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['attendance_id']));						   
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	
		$t->set_var('batch',$_GET['batch']);
		$t->set_var('historylist',$this->historylist('attendancehistory','month','month',$_GET['batch']));
		$t->set_var('agencyid',$_SESSION["currentorgan"]);
		$t->set_var('pre',WEB_ADMIN_TABPOX);
		$t -> set_var('add',$this -> getAddStr('img'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');

    }
	function savetohistory(){
			$sql='select * from '.WEB_ADMIN_TABPOX.'attendance where  agencyid ='.$_SESSION["currentorgan"];
			
			$inrs = &$this -> dbObj -> Execute($sql);
			
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				
				//echo "INSERT INTO  ".WEB_ADMIN_TABPOX."attendancehistory (employee_id,agencyid,planattendance,actualattendance,othours,leavehours,timeoff,month) value(".$inrrs['employee_id'].",".$inrrs["agencyid"].",'".$inrrs['planattendance']."', '".$inrrs['actualattendance']."','".$inrrs['othours']."','".$inrrs['leavehours']."','".$inrrs['timeoff']."')";
				$data = $this -> dbObj ->GetRow("INSERT INTO  ".WEB_ADMIN_TABPOX."attendancehistory (employee_id,agencyid,planattendance,actualattendance,othours,leavehours,timeoff,month) value(".$inrrs['employee_id'].",".$inrrs["agencyid"].",'".$inrrs['planattendance']."', '".$inrrs['actualattendance']."','".$inrrs['othours']."','".$inrrs['leavehours']."','".$inrrs['timeoff']."','".$_GET['batch']."')");
				if($inrrs['clearot']==1){
				echo 'UPDATE '.WEB_ADMIN_TABPOX."attendance SET planattendance=0, actualattendance=0, othours=0,leavehours=0,timeoff=0,lastremainot=0,addupot=0  WHERE attendance_id =".$inrrs['attendance_id'];
				$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."attendance SET planattendance=0, actualattendance=0, othours=0,leavehours=0,timeoff=0,lastremainot=0,addupot=0  WHERE attendance_id =".$inrrs['attendance_id']);
				}else {
				echo 'UPDATE '.WEB_ADMIN_TABPOX."attendance SET planattendance=0, actualattendance=0, othours=0,leavehours=0,timeoff=0,lastremainot=".$inrrs['addupot']."  WHERE attendance_id =".$inrrs['attendance_id'];
				$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."attendance SET planattendance=0, actualattendance=0, othours=0,leavehours=0,timeoff=0,lastremainot=".$inrrs['addupot']."  WHERE attendance_id =".$inrrs['attendance_id']);				
				}
				

			}
			$inrs -> Close();	
			$this -> quit($info.'成功！');
	}
	function audit(){
	
	if ($_GET['auditstatus']==0){
	 $this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."attendance SET `auditstatus` = 0 WHERE agencyid =".$_SESSION["currentorgan"]);
	 $info='撤回';
	 }else {
	  		
			
			$sql='select * from '.WEB_ADMIN_TABPOX.'attendance where  agencyid ='.$_SESSION["currentorgan"];
			
			$inrs = &$this -> dbObj -> Execute($sql);
			
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				
				//echo "INSERT INTO  ".WEB_ADMIN_TABPOX."attendancehistory (employee_id,agencyid,planattendance,actualattendance,othours,leavehours,timeoff,month) value(".$inrrs['employee_id'].",".$inrrs["agencyid"].",'".$inrrs['planattendance']."', '".$inrrs['actualattendance']."','".$inrrs['othours']."','".$inrrs['leavehours']."','".$inrrs['timeoff']."')";
				$data = $this -> dbObj ->GetRow("INSERT INTO  ".WEB_ADMIN_TABPOX."attendancehistory (employee_id,agencyid,planattendance,actualattendance,othours,leavehours,timeoff,month) value(".$inrrs['employee_id'].",".$inrrs["agencyid"].",'".$inrrs['planattendance']."', '".$inrrs['actualattendance']."','".$inrrs['othours']."','".$inrrs['leavehours']."','".$inrrs['timeoff']."','".$_GET['batch']."')");
				if($inrrs['clearot']==1){
				//echo 'UPDATE '.WEB_ADMIN_TABPOX."attendance SET planattendance=0, actualattendance=0, othours=0,leavehours=0,timeoff=0,lastremainot=0,addupot=0  WHERE attendance_id =".$inrrs['attendance_id'];
				$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."attendance SET planattendance=0, actualattendance=0, othours=0,leavehours=0,timeoff=0,lastremainot=0,addupot=0,`auditstatus` = 2 WHERE attendance_id =".$inrrs['attendance_id']);
				}else {
				//echo 'UPDATE '.WEB_ADMIN_TABPOX."attendance SET planattendance=0, actualattendance=0, othours=0,leavehours=0,timeoff=0,lastremainot=".$inrrs['addupot']."  WHERE attendance_id =".$inrrs['attendance_id'];
				$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."attendance SET planattendance=0, actualattendance=0, othours=0,leavehours=0,timeoff=0,`auditstatus` = 2,lastremainot=".$inrrs['addupot']."  WHERE attendance_id =".$inrrs['attendance_id']);				
				}
				

			}
			$inrs -> Close();	
			
			
	  //$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."attendance SET `auditstatus` = 0 WHERE agencyid =".$_SESSION["currentorgan"]);
	  
	 $info='审核';	
	 }
	 $this -> quit($info.'成功！');
	}
	function disp(){
		//定义模板
		$t = new Template('../template/staff');
		$t -> set_file('f','attendanceaudit.html');
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
			
		
			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'attendance where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'attendancea INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on a.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  a.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'attendance where  agencyid ='.$_SESSION["currentorgan"].' and `auditstatus` = 1';
			
			
			}
			
			//if($_POST['batch']){$sql='select * from '.WEB_ADMIN_TABPOX.'attendancehistorty where  agencyid ='.$_SESSION["currentorgan"];} 
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  attendance_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);	
			$count=$result->RecordCount();
			
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
			$t -> set_var('recordcount',$count);
			//设置分类
			
			$t -> set_var('ml');
			
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'attendance where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				
				
				$data = $this -> dbObj ->GetRow("select * from ".WEB_ADMIN_TABPOX."employee where employee_id=".$inrrs['employee_id']." and  agencyid =".$_SESSION["currentorgan"]);
				$t -> set_var($data);
				$data1= $this -> dbObj ->GetRow("select * from ".WEB_ADMIN_TABPOX."employeelevel where employeelevel_id=".$data['employeelevelid']);
				$t -> set_var($data1);

				$t -> set_var('clearot',$this -> clearotlist($inrrs['attendance_id'],$inrrs['clearot']));
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['attendance_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['attendance_id']));						   
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	
	    $t->set_var('historylist',$this->historylist('attendancehistory','month','month',$_GET['batch']));
		$batch=date("d",time())-6>0?date("Ym",time()):date("Ym",strtotime('last month'));
		$t->set_var('batch',$batch);
		$t->set_var('agencyid',$_SESSION["currentorgan"]);
		$t->set_var('pre',WEB_ADMIN_TABPOX);
		$t -> set_var('add',$this -> getAddStr('img'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispAppend(){

		$t = new Template('../template/staff');
		$t -> set_file('f','attendance_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	


		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');

		$t -> set_var('attendance_name',"");	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('memo',"");
		$t -> set_var('userid',$this->getUid());		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'attendance WHERE attendance_id = '.$updid));			
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$inrs = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'attendance WHERE attendance_id = '.$updid);	
			while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
			}
			$inrs -> Close();
		}
					
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}
	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'attendance WHERE attendance_id in('.$delid.')');
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
			$customer_id=explode(";",$_POST["id"]);
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."attendance` (`attendance_name` ,`memo` ,`agencyid` )VALUES ( '".$_POST["attendance_name"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."')");

			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			
			$id = $_POST[MODIFY.'id'];
			$id=explode(",",$_POST["id"]);
			for ($i=0;$i<count($id);$i++)	
			{$planattendance=$_POST['planattendance'.$id[$i]];
			 $actualattendance= $_POST['actualattendance'.$id[$i]];
			 $othours= $_POST['othours'.$id[$i]];
			 $leavehours= $_POST['leavehours'.$id[$i]];
			 $timeoff= $_POST['timeoff'.$id[$i]];
             $clearot= $_POST['clearot'.$id[$i]];
			 $lastremainot=$_POST['lastremainot'.$id[$i]];
			 $addupot=$_POST['addupot'.$id[$i]];
			 
			 //echo 'UPDATE '.WEB_ADMIN_TABPOX."attendance SET planattendance='".$planattendance."', actualattendance='".$actualattendance."', othours='".$othours."',leavehours='".$leavehours."',timeoff='".$timeoff."',clearot='".$clearot."',addupot='".$addupot."',lastremainot='".$lastremainot."'   WHERE attendance_id =".$id[$i];
			 $this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."attendance SET planattendance='".$planattendance."', actualattendance='".$actualattendance."', othours='".$othours."',leavehours='".$leavehours."',timeoff='".$timeoff."',clearot='".$clearot."',addupot='".$addupot."',lastremainot='".$lastremainot."'   WHERE attendance_id =".$id[$i]);
			$employee_id=$this -> dbObj -> getone('select employee_id  from '.WEB_ADMIN_TABPOX.'attendance where attendance_id ='.$id[$i]); 
			if($leavehours>0){
			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."salary  SET `fullattendance` = 0,`leavepay` = '".($leavehours*15)."' WHERE employee_id =".$employee_id);
			
			}else{
			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."salary  SET `fullattendance` = 100,`leavepay` = 0 WHERE employee_id =".$employee_id);
			}
			if($clearot=='1'){//是否结算加班费
			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."salary  SET `otpay` = ".($addupot*15)." WHERE employee_id =".$employee_id);
			echo 'UPDATE '.WEB_ADMIN_TABPOX."salary  SET `otpay` = ".($addupot*15)." WHERE employee_id =".$employee_id;
			}else{
			$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."salary  SET `otpay` = 0 WHERE employee_id =".$employee_id);
			}
			
			}
			//$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."attendance SET attendance_name='".$_POST["attendance_name"]."', memo='".$_POST["memo"]."' WHERE attendance_id =".$id);

		}
//$this -> quit($info.'成功！');

		$this -> quit($info.'成功！');

	}



	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');location.href='attendanceaudit.php';</script>");
	}
	
		function historylist($table,$id,$name,$selectid='201008'){
            $inrs= &$this -> dbObj -> Execute('select distinct '.$name.' from '.WEB_ADMIN_TABPOX.$table .' where agencyid ='.$_SESSION["currentorgan"]);
			$str='';
	     	while ($inrrs = &$inrs -> FetchRow()) {
			
			if ($inrrs[$id]==$selectid)
			$str =$str."<option value=".$inrrs[$name]." selected>".$inrrs[$name]."</option>";	
			else
			$str =$str."<option value=".$inrrs[$name].">".$inrrs[$name]."</option>";			
			}
			$inrs-> Close();	

			return  $str;	
	    }
function clearotlist($attendance_id,$clearot){
 $str='';
 if ($clearot==1){
 $str="<input type='radio' name=clearot".$attendance_id." value='1' checked>是<input type='radio' name=clearot".$attendance_id." value='0'>否";
 }else {
 $str="<input type='radio' name=clearot".$attendance_id." value='1' >是<input type='radio' name=clearot".$attendance_id." value='0' checked>否";
 }
 return $str;
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
  