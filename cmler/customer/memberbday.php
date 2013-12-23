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
		$t -> set_file('f','memberbday.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
		$weekarray=array("日","一","二","三","四","五","六");   
        $year=$_GET["year"];
		$month=$_GET["month"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($year<>''&&$month<>''){
		if($ftable==''){$condition=' MONTH(birthday)='.$month;}else{$condition=' MONTH(birthday)='.$month;}
		}
		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:10000;
			$offset = $pageid>0?($pageid-1)*$psize:0;

			//设置分类
			$t -> set_var('ml');
		
		
			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'customer  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'customer p INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on p.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  p.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'customer  where  agencyid ='.$_SESSION["currentorgan"].' and  MONTH(birthday)='.date("m",time());
			}
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY   day( birthday) asc  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
           	$t -> set_var('recordcount',$count);
						
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'produce  where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('gender',$inrrs["genderid"]==1?'男':'女');
				$t -> set_var('category_name',$this -> dbObj -> getone('select category_name from '.WEB_ADMIN_TABPOX.'procatalog where category_id ='.$inrrs["categoryid"]));
				$t -> set_var('vrcount',$this -> dbObj -> getone('select count(*) from '.WEB_ADMIN_TABPOX.'visiterecord where customer_id ='.$inrrs["customer_id"]." and agencyid =".$_SESSION["currentorgan"]));		
				
				if(date("m-d",strtotime($inrrs['birthday']))==date("m-d",time()))
				{ 
				$t -> set_var('remind','<font color=red>今天生日</font>');
				$t -> set_var('bgColor','#F9E9E9');
				}else if(date("m-d",strtotime($inrrs['birthday']))>date("m-d",time()))
				{
				$t -> set_var('remind','未到生日');
				$t -> set_var('bgColor','#ffffff');
				}else{
				$t -> set_var('remind','已过生日');
				$t -> set_var('bgColor','#eeeeee');
				}
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['customer_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['customer_id']));	
				$t -> set_var('date',$weekarray[date("w",strtotime($inrrs['birthday']))]);	
				
				$t -> set_var('day',date("d",strtotime($inrrs['birthday'])));	
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	
		for($i=1;$i<13;$i++){
		$t -> set_var('c'.$i,'');
		}	
		if($month=='')
		{$t -> set_var('c'.(date("m",time())+0),'c');}
		else{
		$t -> set_var('c'.$month,'c');}
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispAppend(){

		$t = new Template('../template/customer');
		$t -> set_file('f','memberbday_detail.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	

		$t -> set_var('booktime',$_GET['birthday']);
		if($this -> isAppend){
		$t -> set_var('action','add');
		$t -> set_var('actionName','增加');
		$t -> set_var('customer_id',$_GET['cid']);	
		$t -> set_var('customer_name',$_GET['cname']);	
		$t -> set_var('employee_id',"");	
		$t -> set_var('man',$this -> dbObj -> getone('select B.employee_name from '.WEB_ADMIN_TABPOX.'user A INNER JOIN '.WEB_ADMIN_TABPOX.'employee B ON A.employee_id=B.employee_id  where A.userid = '.$this->getUid()));	
		$t -> set_var('title',"生日祝福");	
		$t -> set_var('content',$this -> dbObj -> getone('select content from '.WEB_ADMIN_TABPOX.'messagetemplate  where messagetemplate_name like "%生日%"'));	
		$t -> set_var('senddate',date("Y-m-d"));	
		$t -> set_var('address',"");	
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('handphone',$_GET['chandphone']);
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('memo',"");
		$t -> set_var('employee_id',$this->getUid());	
		$t -> set_var('picurl',"暂时没有照片");	
		$t -> set_var('birthday',date("Y-m-d"));
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
					$t -> set_var('customer_id',"0");	
		$t -> set_var('customer_name',"寿星");	
		$t -> set_var('employee_id','0');	
		$t -> set_var('man',"系统");	
		$t -> set_var('title',"生日祝福");	
		$t -> set_var('content',$this -> dbObj -> getone('select content from '.WEB_ADMIN_TABPOX.'messagetemplate  where messagetemplate_name like "%生日%"'));
		$t -> set_var('senddate',date("Y-m-d"));	
		$t -> set_var('address',"");	
		$t -> set_var('showeditdiv',"");
		$t -> set_var('messagetemplate_name',"生日祝福");			
		$t -> set_var('handphone','');
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'customer WHERE customer_id = '.$updid);
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
if ($date['state']==1){

		$t -> set_var('state','<input id="state" type="radio" checked="checked" value="1" name="state" /><label for="state">可用</label><input id="state" type="radio" value="0" name="state" /><label for="state">停用</label>');
}else
{
		$t -> set_var('state','<input id="state" type="radio"  value="1" name="state" /><label for="state_0">可用</label><input id="state" type="radio" value="0" checked="checked" name="state" /><label for="state">停用</label>');
}
		//$t -> set_var('brandlist',$this ->selectlist('brand','brand_id','brand_name',$data['brandid']));
		//$t -> set_var('genderlist',"111");	
		//echo $data['genderid'];
		$t -> set_var('genderlist',$this -> gender($data['genderid']));	
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));						
		$t -> set_var('messagetypelist',$this -> selectlist('messagetype','messagetype_id','messagetype_name','1'));	
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
	
		function selectlist($table,$id,$name,$selectid=0){
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table );
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
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'messagerecord WHERE customer_id in('.$delid.')');
		$info='删除';
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		$orgid='175';
		$username='xiangman';
		$passwd='8546';	
		
		if($this -> isAppend){
			$info = '增加';	
			$customer_id=explode(";",$_POST["customer_id"]);
			$customer_name=explode(";",$_POST["customer_name"]);
			$handphone=explode(";",$_POST["handphone"]);
			$birthday=explode(";",$_POST['booktime']);
	         
			$content=$_POST["content"];
			$sqlstr='';
			date_default_timezone_set('Asia/Hong_Kong');
            $sendtime=date("Y-m-d H:i:s",time());
			$sendtimenow=date("Y-m-d H:i:s",strtotime("$m-1 days",strtotime($sendtime)));
			for ($i=0;$i<count($customer_id);$i++)	{
			$content1= str_replace('{customername}',$customer_name[$i],$content);			 
			$content1= str_replace('{birthday}',$birthday[$i],$content1);
			$content1= str_replace('{agency_name}',$_SESSION["currentorganname"],$content1);
			$booktime=date("Y-m-d H:i:s",strtotime($birthday[$i].' '.date("H:m:s",time())));
			//$result=file_get_contents("http://59.42.247.51/http1.php?act=send&orgid=175&username=xiangman&passwd=8546&msg=亲爱的cugle:你好,今天2011-04-13是你的生日,祝你生日快乐,永远美丽——总部&destnumbers=13570296475&sendTime=2011-04-12 11:27:00&bookTime=2011-04-13 10:04:00");
			if($birthday[$i]<=date("Y-m-d",time())){
			
			$data = array("act" => 'send',"orgid" => $orgid,"username" => $username,"passwd" => $passwd,"msg" => $content1,"destnumbers" => $handphone[$i],"sendTime" => $sendtimenow);   

			
			}else if($birthday[$i] > date("Y-m-d",time())){
				
			$data = array("act" => 'send',"orgid" => $orgid,"username" => $username,"passwd" => $passwd,"msg" => $content1,"destnumbers" => $handphone[$i],"sendTime" => $sendtime,"bookTime" => $booktime);  
			}
			$data = http_build_query($data);   
			$opts = array(   
			  'http'=>array(   
				'method'=>"POST",   
				'header'=>"Content-type: application/x-www-form-urlencoded\r\n".   
						  "Content-length:".strlen($data)."\r\n" .    
						  "Cookie: foo=bar\r\n" .    
						  "\r\n",   
				'content' => $data,   
			  )   
			);   
			$cxContext = stream_context_create($opts);   
			//$sFile = file_get_contents("http://59.42.247.51/http1.php", false, $cxContext);   
			$sFile = file_get_contents("http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".urlencode($content1)."&destnumbers=".$handphone[$i]."&sendTime=".date("Y-m-d H:i:s",$sendtime)."&bookTime=".$booktime);
						
$result= $sFile;   
		
		
		//echo $result;
			// $result=file_get_contents("http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$content1."&destnumbers=".$handphone[$i]."&sendTime=".date("Y-m-d H:i:s",$sendtime)."&bookTime=".date("Y-m-d H:i:s",strtotime($birthday[$i].' '.date("H:m:s",time())));
			//echo "http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$content1."&destnumbers=".$handphone[$i]."&sendTime=".date("Y-m-d H:i:s",$sendtime)."&bookTime=".date("Y-m-d H:i:s",strtotime($birthday[$i].' '.date("H:m:s",time())));
			//echo "http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$content1."&destnumbers=".$handphone[$i]."&sendTime=".date("Y-m-d H:i:s",$sendtime)."&bookTime=".date("Y-m-d H:i:s",strtotime($birthday[$i]));
			if($sqlstr==''){

			$sqlstr="( '".$customer_id[$i]."','".$customer_name[$i]."', '".$_POST["title"]."', `".$content1."`,'".$_POST["senddate"]."', '".$handphone[$i]."', '".$this->intnonull($_POST["employee_id"])."','".$_POST["man"]."', '".$this->intnonull($_POST["state"])."','".$_POST["messagetypeid"]."','".$booktime."','".$_SESSION["currentorgan"]."')";
			}else{
		 	$sqlstr=$sqlstr.", ( '".$customer_id[$i]."','".$customer_name[$i]."', '".$_POST["title"]."', `".$content1."`,'".$_POST["senddate"]."', '".$handphone[$i]."', '".$this->intnonull($_POST["employee_id"])."','".$_POST["man"]."', '".$this->intnonull($_POST["state"])."','".$_POST["messagetypeid"]."','".$booktime."','".$_SESSION["currentorgan"]."')";
			}

			}


			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."messagerecord` (`customer_id`, `customer_name`, `title`, `content`, `senddate`,  `handphone`, `employee_id`, `man`,  `state`, `messagetypeid`,`booktime`,`agencyid`)VALUES ".$sqlstr);
			//echo "INSERT INTO `".WEB_ADMIN_TABPOX."messagerecord` (`customer_id`, `customer_name`, `title`, `content`, `senddate`,  `handphone`, `employee_id`, `man`,  `state`, `messagetypeid`,`booktime`,`agencyid`)VALUES ".$sqlstr;
//echo "INSERT INTO `".WEB_ADMIN_TABPOX."messagerecord` (`customer_id`, `customer_name`, `title`, `content`, `senddate`,  `handphone`, `employee_id`, `man`,  `state`, `messagetypeid`,`booktime`,`agencyid`)VALUES ".$sqlstr;
//echo "INSERT INTO `".WEB_ADMIN_TABPOX."messagerecord` (`customer_id`, `customer_name`, `title`, `content`, `senddate`,  `handphone`, `employee_id`, `man`,  `state`, `agencyid`)VALUES ( '".$_POST["customer_id"]."','".$_POST["customer_name"]."', '".$_POST["title"]."','".$_POST["senddate"]."', '".$_POST["handphone"]."', '".$this->intnonull($_POST["employee_id"])."','".$_POST["man"]."', '".$this->intnonull($_POST["state"])."','".$_SESSION["currentorgan"]."')";
			$id = $this -> dbObj -> Insert_ID();
			
			//$result=file_get_contents("http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username[i]."&passwd=".$passwd."&msg=".$_POST["content"]."&destnumbers=".$handphone[i]."&sendTime=".date("Y-m-d H:i:s",$sendtime)."&bookTime=".date("Y-m-d H:i:s",strtotime($birthday[i])));

			
			$a=explode('&',$result);
            $b= explode('=',$a[0]);
			$state=$b[1];
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."messagerecord` SET `state` = '".$state."' WHERE messagerecord_id =".$id);
		    if($b[1]=='0'){$this -> quit('发送成功');}else{$this -> quit('发送失败');}
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."customer` SET `customer_name` = '".$_POST["customer_name"]."',`customer_no` = '".$_POST["customer_no"]."',`idnumber` = '".$_POST["idnumber"]."',`tel` = '".$_POST["tel"]."',`handphone` ='".$_POST["handphone"]."',`email` = '".$_POST["email"]."',`address` = '".$_POST["address"]."',`zipcode` = '".$_POST["zipcode"]."',`birthday` = '".$_POST["birthday"]."',`genderid` = '".$this->intnonull($_POST["genderid"])."', picpath='".$_POST["picpath"]."',ismarry=".$this->intnonull($_POST['ismarry']).", province='".$_POST["province"]."', city='".$_POST["city"]."' WHERE customer_id =".$id);
//echo "UPDATE `".WEB_ADMIN_TABPOX."customer` SET `customer_name` = '".$_POST["customer_name"]."',`customer_no` = '".$_POST["customer_no"]."',`idnumber` = '".$_POST["idnumber"]."',`tel` = '".$this->intnonull($_POST["tel"])."',`handphone` ='".$this->intnonull($_POST["handphone"])."',`email` = '".$_POST["email"]."',`address` = '".$_POST["address"]."',`zipcode` = '".$this->intnonull($_POST["zipcode"])."',`birthday` = '".$_POST["birthday"]."',`genderid` = '".$this->intnonull($_POST["genderid"])."', picpath='".$_POST["picpath"]."',ismarry=".$this->intnonull($_POST['ismarry']).", province='".$_POST["province"]."', city='".$_POST["city"]."' WHERE customer_id =".$id;
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
		exit("<script>alert('$info');location.href='memberbday.php';</script>");
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
  