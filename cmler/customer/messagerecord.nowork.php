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
		$t -> set_file('f','messagerecord.html');
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'messagerecord  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'messagerecord p INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on p.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  p.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'messagerecord where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  messagerecord_id DESC  LIMIT ".$offset." , ".$psize);
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
				$t -> set_var('messagetype',$this -> dbObj -> getone('select messagetype_name from '.WEB_ADMIN_TABPOX.'messagetype where messagetype_id ='.$inrrs["messagetypeid"]));	
				$t -> set_var('statename',$this -> dbObj -> getone('select messagestate_name from '.WEB_ADMIN_TABPOX.'messagestate where messagestate_no ='.$inrrs["state"]));	
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['messagerecord_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['messagerecord_id']));				
				
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$t -> set_var('messagetypelist',$this -> selectlist('messagetype','messagetype_id','messagetype_name','0'));
		$t -> set_var('messagestatelist',$this -> selectlist('messagestate','messagestate_no','messagestate_name','1'));		
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispAppend(){

		$t = new Template('../template/customer');
		$t -> set_file('f','messagerecord_detail.html');
		$t->unknowns = "keep";
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
		$t -> set_var('employee_id',"");	
		$t -> set_var('man',"");	
		$t -> set_var('title',"");	
		$t -> set_var('content',"");	
		$t -> set_var('senddate',date("Y-m-d"));	
		$t -> set_var('address',"");	
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('handphone',"");
		date_default_timezone_set('Asia/Hong_Kong');
		$t -> set_var('booktime',date("Y-m-d",time()));	
		$t -> set_var('h',$this->hourlist(date("H",time())));	
		$t -> set_var('i',$this->minutelist(date("i",time())));	
		$t -> set_var('messagetemplate_name',"");
		$t -> set_var('man',$this -> dbObj -> getone('select B.employee_name from '.WEB_ADMIN_TABPOX.'user A INNER JOIN '.WEB_ADMIN_TABPOX.'employee B ON A.employee_id=B.employee_id  where A.userid = '.$this->getUid()));
		$t -> set_var('employee_id',$this->getUid());	
		$t -> set_var('findcustomer',"");	
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'messagerecord WHERE messagerecord_id = '.$updid);
			$t -> set_var($data);
			$t -> set_var('findcustomer',"disabled='disabled'");
			$t -> set_var('ismarrycheck1',$data['ismarry']==1?'checked':'');	
			$t -> set_var('ismarrycheck2',$data['ismarry']==2?'checked':'');	
			$t -> set_var('booktime',date("Y-m-d",strtotime($data['booktime'])));	
			$t -> set_var('h',$this->hourlist(date("H",strtotime($data['booktime']))));	
			$t -> set_var('i',$this->minutelist(date("i",strtotime($data['booktime']))));	
			
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
		
		$t -> set_var('messagetypelist',$this -> selectlist('messagetype','messagetype_id','messagetype_name',$data['messagetypeid']));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}
	function hourlist($h='00'){
	$liststr='';
	for($i=0;$i<24;$i++){
	 if($i<10){
		 if($h=='0'.$i){
		  $liststr=$liststr."<option value='0".$i."' selected='selected'>0".$i."</option>";
		 }else{
	     $liststr=$liststr."<option value='0".$i."'>0".$i."</option>";
		 }
	}else{
		if($h==$i){
		 $liststr=$liststr."<option value='".$i."' selected='selected'>".$i."</option>";
		 }else{
		 $liststr=$liststr."<option value='".$i."'>".$i."</option>";
		 }
		 }	 
	}
	return $liststr;
  }	

  	function minutelist($m='00'){

	$liststr='';
	for($i=0;$i<60;$i++){
	 if($i<10){
		 if($m=='0'.$i){

		  $liststr=$liststr."<option value='0".$i."' selected='selected'>0".$i."</option>";
		 }else{
	     $liststr=$liststr."<option value='0".$i."'>0".$i."</option>";
		 }
	}else{
		if($m==$i){
		 $liststr=$liststr."<option value='".$i."'  selected='selected'>".$i."</option>";
		 }else{
		 $liststr=$liststr."<option value='".$i."'>".$i."</option>";
		 }
		 }	 
	}
	return $liststr;
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
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table);
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
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'messagerecord WHERE messagerecord_id in('.$delid.')');
		
		$info='删除';
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		$message_config=explode(';',$this -> getValue('message_config'));
		$orgid=$message_config[0];
		$username=$message_config[1];
		$passwd=$message_config[2];

		if($this -> isAppend){
			$info = '增加';	
			$customer_id=explode(";",$_POST["customer_id"]);
			$customer_name=explode(";",$_POST["customer_name"]);
			$handphone=explode(";",$_POST["handphone"]);
			$booktime=$_POST['booktime']." ".$_POST['h'].":".$_POST['i'].":00";
	     	$booktime=date('Y-m-d H:i:s',strtotime($booktime));
			$content=$_POST["content"];
			$sqlstr='';
			date_default_timezone_set('Asia/Hong_Kong');
            $sendtime=time();
			$ch = curl_init();
			for ($i=0;$i<count($customer_id);$i++)	{
			$content1= str_replace('{customername}',$customer_name[$i],$content);	
	$tel=$handphone[$i];	
	$msg=$content1;
     
    $serverUrl="http://59.42.247.51/http.php";
    
    $msg=mb_convert_encoding($msg,'gbk','utf-8');        //这个就没有乱码
	if($booktime<=date("Y-m-d",time())){//即时短信
    $post_data="act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$msg."&destnumbers=".$tel."&connkey=";
    }else if($booktime > date("Y-m-d",time())){//定时短信
	$post_data="act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$msg."&destnumbers=".$tel."&connkey=&booktime=".$booktime;
	
	}
    
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);                //返回内容中不包含 HTTP 头

    curl_setopt($ch, CURLOPT_URL,$serverUrl);
    curl_setopt($ch, CURLOPT_NOBODY, 0);                 //读取页面内容
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);            //不自动显示内容

   $sFile = curl_exec($ch);
    	
			//$content1= str_replace('{birthday}',date("Y-m-d",strtotime($booktime)),$content1);	
	
			// $result=file_get_contents("http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$content1."&destnumbers=".$handphone[$i]."&sendTime=".date("Y-m-d H:i:s",$sendtime)."&bookTime=".$booktime);
			
/*			if($booktime<=date("Y-m-d",time())){//即时短信
			
			$data = array("act" => 'send',"orgid" => $orgid,"username" => $username,"passwd" => $passwd,"msg" => $content1,"destnumbers" => $handphone[$i],"sendTime" => $sendtime); 

			
			}else if($booktime > date("Y-m-d",time())){//定时短信
				
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
			$cxContext = stream_context_create($opts);  */ 
			//$sFile = file_get_contents("http://59.42.247.51/http1.php", false, $cxContext);   
			//echo $content1;
			//echo "http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".urlencode($content1)."&destnumbers=".$handphone[$i]."&sendTime=".date("Y-m-d H:i:s",$sendtime)."&bookTime=".$booktime;
			//$content1= str_replace(' ','\r',$content1);	
			//$sFile = file_get_contents("http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".urlencode($content1)."&destnumbers=".$handphone[$i]."&sendTime=".date("Y-m-d H:i:s",$sendtime)."&bookTime=".$booktime);
			
$result= $sFile;   			
			
			//echo "http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$content1."&destnumbers=".$handphone[$i]."&sendTime=".date("Y-m-d H:i:s",$sendtime)."&bookTime=".$booktime;
			
			//if($sqlstr==''){
			if (!get_magic_quotes_gpc()){
			$content1=addslashes($content1);
			}
			$sqlstr="( '".$customer_id[$i]."','".$customer_name[$i]."', '".$_POST["title"]."', '".$content1."','".$_POST["senddate"]."', '".$handphone[$i]."', '".$this->intnonull($_POST["employee_id"])."','".$_POST["man"]."', '".$this->intnonull($_POST["state"])."','".$_POST["messagetypeid"]."','".$booktime."','".$_SESSION["currentorgan"]."')";
		
		  	$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."messagerecord` (`customer_id`, `customer_name`, `title`, `content`, `senddate`,  `handphone`, `employee_id`, `man`,  `state`, `messagetypeid`, `booktime`,`agencyid`)VALUES ".$sqlstr);
			//echo "INSERT INTO `".WEB_ADMIN_TABPOX."messagerecord` (`customer_id`, `customer_name`, `title`, `content`, `senddate`,  `handphone`, `employee_id`, `man`,  `state`, `messagetypeid`, `booktime`,`agencyid`)VALUES ".$sqlstr;
			$id = $this -> dbObj -> Insert_ID();
			
			$a=explode('&',$result);
            $b= explode('=',$a[0]);
			$state=$b[1];
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."messagerecord` SET `state` = '".$state."' WHERE messagerecord_id =".$id);
			//}else{
		 //	$sqlstr=$sqlstr.", ( '".$customer_id[$i]."','".$customer_name[$i]."', '".$_POST["title"]."', '".$content1."','".$_POST["senddate"]."', '".$handphone[$i]."', '".$this->intnonull($_POST["employee_id"])."','".$_POST["man"]."', '".$this->intnonull($_POST["state"])."','".$_POST["messagetypeid"]."','".$booktime."','".$_SESSION["currentorgan"]."')";
			//}

			}
			// echo "INSERT INTO `".WEB_ADMIN_TABPOX."messagerecord` (`customer_id`, `customer_name`, `title`, `content`, `senddate`,  `handphone`, `employee_id`, `man`,  `state`,`messagetypeid`,`booktime`, `agencyid`)VALUES ".$sqlstr;
			


			


			//echo $result;
			//$a=explode('&',$result);
            //$b= explode('=',$a[0]);
			//$state=$b[1];
			//$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."messagerecord` SET `state` = '".$state."' WHERE messagerecord_id =".$id);
		   curl_close($ch);
		   if($b[1]=='0'){$this -> quit('发送成功');}else if($b[1]=='100'){$this -> quit('定时成功');} else{$this -> quit('发送失败');}
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			
            $content=$_POST["content"];
			$booktime=$_POST['booktime']." ".$_POST['h'].":".$_POST['i'].":00";
			$booktime=date('Y-m-d H:i:s',strtotime($booktime));
            $customer_name=$_POST['customer_name'];
			$content=strtr($content,"customername",$customer_name);//替换标签

			
			$handphone= $_POST["handphone"];
			
			$sendTime=date("Y-m-d H:i:s",time());
	$tel=$_POST["handphone"];	
	$msg=$content;
     
    $serverUrl="http://59.42.247.51/http.php";
    
    $msg=mb_convert_encoding($msg,'gbk','utf-8');        //这个就没有乱码
    $post_data="act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$msg."&destnumbers=".$tel."&connkey=";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);                //返回内容中不包含 HTTP 头

    curl_setopt($ch, CURLOPT_URL,$serverUrl);
    curl_setopt($ch, CURLOPT_NOBODY, 0);                 //读取页面内容
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);            //不自动显示内容

   $sFile = curl_exec($ch);
    curl_close($ch);
    //echo $response;
   // exit();   
			//发送短信
 //$result=file_get_contents("http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$content."&destnumbers=".$handphone."&sendTime=".$sendTime."&bookTime=".$booktime);
		//echo $result;	
			// echo "http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$content."&destnumbers=".$handphone."&sendTime=".$sendTime."&bookTime=".$booktime;
/*			$data = array("act" => 'send',"orgid" => $orgid,"username" => $username,"passwd" => $passwd,"msg" => $content,"destnumbers" => $handphone,"sendTime" => $sendtime,"bookTime" => $booktime);  
			 
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
			$cxContext = stream_context_create($opts);  */ 
			//$sFile = file_get_contents("http://59.42.247.51/http1.php", false, $cxContext);   
			//echo $content;
			//$content= str_replace(' ','\r',$content);	
			//$sFile=file_get_contents("http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".urlencode($content)."&destnumbers=".$_POST["handphone"]."&sendTime=".date("Y-m-d H:i:s",time())."&bookTime=".date('Y-m-d H:i:s',strtotime($_GET['booktime'])));
			
$result= $sFile;   					
		
			//更新数据库
			if (!get_magic_quotes_gpc()){
			$content=addslashes($content);
			}	
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."messagerecord` SET `customer_name` = '".$_POST["customer_name"]."',`customer_id` = '".$_POST["customer_id"]."',`title` = '".$_POST["title"]."',`content` = '".$content."',`senddate` ='".$_POST["senddate"]."',`handphone` = '".$_POST["handphone"]."',`employee_id` = '".$this->intnonull($_POST["employee_id"])."',`man` = '".$_POST["man"]."',`state` = '".$this->intnonull($_POST["state"])."', `messagetypeid`=".$_POST["messagetypeid"]." , booktime='".$booktime."' WHERE messagerecord_id =".$id);
 //echo "UPDATE `".WEB_ADMIN_TABPOX."messagerecord` SET `customer_name` = '".$_POST["customer_name"]."',`customer_id` = '".$_POST["customer_id"]."',`title` = '".$_POST["title"]."',`content` = `".$content."`,`senddate` ='".$_POST["senddate"]."',`handphone` = '".$_POST["handphone"]."',`employee_id` = '".$this->intnonull($_POST["employee_id"])."',`man` = '".$_POST["man"]."',`state` = '".$this->intnonull($_POST["state"])."', `messagetypeid`=".$_POST["messagetypeid"]." , booktime='".$booktime."' WHERE messagerecord_id =".$id;	
			
			 //$result=file_get_contents("http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$content."&destnumbers=".$_POST["handphone"]."&sendTime=".date("Y-m-d H:i:s",time())."&bookTime=".date('Y-m-d H:i:s',strtotime($_GET['booktime'])));
			
			//echo "http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$content."&destnumbers=".$_POST["handphone"]."&sendTime=".date("Y-m-d H:i:s",time())."&bookTime=".$booktime;
           // echo $_POST['booktime'];
			//echo $booktime;
			$a=explode('&',$result);
            $b= explode('=',$a[0]);
			$state=$b[1];
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."messagerecord` SET `state` = '".$state."'  WHERE messagerecord_id =".$id);

		    if($b[1]=='0'){$this -> quit('发送成功');}else{$this -> quit('发送失败');}
		}

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
		exit("<script>alert('$info');location.href='messagerecord.php';</script>");
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
  