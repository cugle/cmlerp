<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/sell.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/card.cls.php');
class Pagecustomer extends admin {
    function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='step2'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> step2();			
		}else if(isset($_GET['action']) && $_GET['action']=='step3'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> step3();			
		}else if(isset($_GET['action']) && $_GET['action']=='step1_save'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> step1_save();			
		}else if(isset($_GET['action']) && $_GET['action']=='step2_save'){
            $this -> checkUser();//验证身份，这一步很重要。
			
            $this -> step2_save();			
		}else if(isset($_GET['action']) && $_GET['action']=='step4'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> step4();			
		}else{
            parent::Main();
        }
    }	
	function disp(){
		$t = new Template('../template/pos');
		$t -> set_file('f','customer_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	

 
		$Prefix='gk';
		$agency_no=$_SESSION["agency_no"];
		$table='customer';
		$column='customer_no';
		$number=5;
		$id='customer_id';	
		$t -> set_var('updid',"");	
		$t -> set_var('customer_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));	
		$t -> set_var('customer_name',"");	
		$t -> set_var('idnumber',"");	
		$t -> set_var('email',"");	
		$t -> set_var('handphone',"");	
		$t -> set_var('qq',"");	
		$t -> set_var('zipcode',"");	
		$t -> set_var('tel',"");	
		$t -> set_var('address',"");	
		$t -> set_var('birthday',"");	
		$t -> set_var('price',"");	
		$t -> set_var('efficacy',"");
		$t -> set_var('useway',"");
		$t -> set_var('basis',"");	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('memo',"");
		$t -> set_var('province',"广东");
		$t -> set_var('city',"广州");
		$t -> set_var('userid',$this->getUid());	
		$t -> set_var('picurl',"暂时没有照片");
		$t -> set_var('picpath',"");	
		$t -> set_var('birthday',date("Y-m-d"));
		$t -> set_var('customercataloglist',$this ->selectlist2('customercatalog','customercatalog_id','customercatalog_name',$data['customercatalog_id']));	
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

		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');	

	}
	function step1_save(){
		//定义模板
		$info = '增加';	
		//如果存在同名
		$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."customer` (`customer_no`, `customer_name`, `address`, `genderid`, `birthday`,  `idnumber`, `tel`, `handphone`,  `zipcode`, `email`,  `memo`,  `agencyid`, `picpath`,ismarry,province,city,qq,customercatalog_id)VALUES ( '".$_POST["customer_no"]."','".$_POST["customer_name"]."', '".$_POST["address"]."','".$_POST["genderid"]."', '".$_POST["birthday"]."', '".$_POST["idnumber"]."','".$_POST["tel"]."', '".$_POST["handphone"]."','".$_POST["zipcode"]."', '".$_POST["email"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["picpath"]."','".$this->intnonull($_POST["ismarry"])."', '".$_POST["province"]."','".$_POST["city"]."','".$_POST["qq"]."','".$_POST["customercatalog_id"]."')");

//echo "INSERT INTO `".WEB_ADMIN_TABPOX."customer` (`customer_no`, `customer_name`, `address`, `genderid`, `birthday`,  `idnumber`, `tel`, `handphone`,  `zipcode`, `email`,  `memo`,  `agencyid`, `picpath`,ismarry,province,city)VALUES ( '".$_POST["customer_no"]."','".$_POST["customer_name"]."', '".$_POST["address"]."','".$_POST["genderid"]."', '".$_POST["birthday"]."', '".$_POST["idnumber"]."','".$_POST["tel"]."', '".$_POST["handphone"]."','".$this->intnonull($_POST["zipcode"])."', '".$_POST["email"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["picpath"]."','".$this->intnonull($_POST["ismarry"])."', '".$_POST["province"]."','".$_POST["city"]."')";
			$id = $this -> dbObj -> Insert_ID();
		if(mysql_affected_rows())
		 
		echo "<script>  question = confirm('新增客户成功,是否为继续该客户开卡？是请点击确定');if (question != '0'){location.href='creatmember.php?action=step2&customerid=".$id."&customercatalog_id=".$_POST['customercatalog_id']."'; }else{window.close();}</script>";
	    else
		exit("<script>alert('新增失败，返回重新操作');location.href='creatmember.php';</script>");
	
	}	
	function step2(){

			
		$t = new Template('../template/pos');
		$t -> set_file('f','membercard_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	


		 	$t -> set_var('tips','');
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		//$t -> set_var('membercard_no',"");	
		$Prefix='M';
		$agency_no=$_SESSION["agency_no"];
		$table='membercard';
		$column='membercard_no';
		$number=5;
		$id='membercard_id';			
		$t -> set_var('membercard_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));
		$t -> set_var('code',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));
		$t -> set_var('man',"");

		$customerid=$_GET['customerid'];
		$customercatalog_id=$_GET['customercatalog_id'];
		if($customerid<>''){
		$customerdata=$this -> dbObj -> GetRow('SELECT customer_name,customer_id FROM '.WEB_ADMIN_TABPOX.'customer   WHERE customer_id in('.$customerid.')');
		
		$customercatalog_name=$this -> dbObj -> GetOne('SELECT customercatalog_name FROM '.WEB_ADMIN_TABPOX.'customercatalog   WHERE customercatalog_id ='.$customercatalog_id);
		$memcardlevel_id=$this -> dbObj -> GetOne('SELECT cardlevel_id FROM '.WEB_ADMIN_TABPOX.'memcardlevel   WHERE cardlevel_name ="'.$customercatalog_name.'"');
		
		$t -> set_var('customer_name',$customerdata['customer_name']);	
		$t -> set_var('customer_id',$customerdata['customer_id']);
		$t -> set_var('cardlevel_name',$customercatalog_name);	
		$t -> set_var('cardlevel_id',$$customercatalog_id);
		}else {
		$t -> set_var('customer_name',"");	
		$t -> set_var('customer_id',"");
		}
		$t -> set_var('idnumber',"");	
		$t -> set_var('email',"");	
		$t -> set_var('handphone',"");	
		$t -> set_var('zipcode',"");	
		$t -> set_var('tel',"");	
		$t -> set_var('address',"");	
		$t -> set_var('birthday',"");	
		$t -> set_var('price',"");	
		$t -> set_var('score',"");
		$t -> set_var('useway',"");
		$t -> set_var('basis',"");	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('memo',"");
		
		$t -> set_var('userid',$this->getUid());	
		$t -> set_var('picurl',"暂时没有照片");	
		$t -> set_var('startdate',date("Y-m-d"));
		$years = date("Y"); //用date()函式取得目前年份格式0000
		$months = date("m"); //用date()函式取得目前月份格式00
		$days = date("d"); //用date()函式取得目前日期格式00

		$t -> set_var('overdate',date("Y-m-d",mktime(0,0,0,$months,$days,$years+2)));
		$t -> set_var('updid','');
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
		$t -> set_var('memcardlevellist',$this ->selectlist('memcardlevel','cardlevel_id','cardlevel_name',$memcardlevel_id));						

		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');		
		
	}
	

	
	function step2_save(){

			$info = '增加';	
			$isexist=$this -> dbObj -> GetOne("select count(*) from `".WEB_ADMIN_TABPOX."membercard` where customer_id='".$_POST["customer_id"]."' and agencyid='".$_SESSION["currentorgan"]."'");
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."membercard` (`membercard_no`, `code`,`cardlevel_id`, `customer_id`, `score`,`startdate`,`overdate`,`man`,password, `agencyid`)VALUES ( '".$_POST["membercard_no"]."','".$_POST["code"]."','".$_POST["cardlevel_id"]."', '".$_POST["customer_id"]."','".$this->intnonull($_POST["score"])."', '".$_POST["startdate"]."', '".$_POST["overdate"]."',  '".$_POST["man"]."',  '".md5($_POST['password'])."', '".$_SESSION["currentorgan"]."')");
			

//echo "INSERT INTO `".WEB_ADMIN_TABPOX."membercard` (`membercard_no`, `cardlevel_id`, `customer_id`, `score`,`startdate`,`overdate`,` man`, ` agencyid`)VALUES ( '".$_POST["membercard_no"]."','".$_POST["cardlevel_id"]."', '".$_POST["customer_id"]."','".$this->intnonull($_POST["score"])."', '".$_POST["startdate"]."', '".$_POST["overdate"]."',  '".$_POST["man"]."', '".$_SESSION["currentorgan"]."')";
			$id = $this -> dbObj -> Insert_ID();
		 $_SESSION['currentcustomerid']=$_POST["customer_id"];
		 $_SESSION["membercardno"]=$_POST["membercard_no"];
		 $customer_id=$_POST["customer_id"];
		 $customer_name=$_POST["membername"];
		 if($isexist==0){
		if(mysql_affected_rows()){
		 $memcardlevel=$this -> dbObj -> GetRow('SELECT *  FROM '.WEB_ADMIN_TABPOX.'memcardlevel    WHERE cardlevel_id = '.$_POST["cardlevel_id"]);
		 $cardlevel_name=$memcardlevel['cardlevel_name'];
		 $cardtype_id=$memcardlevel['cardtype_id'];
		 $marketingcard_id=$memcardlevel['marketingcard_id'];
		 $sendmsg=$_POST['sendmsg'];
		 if($_POST['sendmsg']=='on'){//发送欢迎短信
		 
		 $tel=$this -> dbObj -> GetOne("select handphone from `".WEB_ADMIN_TABPOX."customer` where customer_id='".$_POST["customer_id"]."' and agencyid='".$_SESSION["currentorgan"]."'");
		 $msg1='尊敬的顾客，欢迎光临'.$_SESSION["currentorganname"].',恭喜你已成为本店会员，会员等级为'.$cardlevel_name.'，会员卡号：'.$_POST["membercard_no"].',密码：'.$_POST["password"].' 有效期至'.$_POST["overdate"];
		$message_config=explode(';',$this -> getValue('message_config'));
		$orgid=$message_config[0];
		$username=$message_config[1];
		$passwd=$message_config[2];	 
		$msg=urlencode($msg1);
		//$msg=mb_convert_encoding($msg1,'gbk','utf-8');
/*    $serverUrl="http://59.42.247.51/http.php";
    
    $msg=mb_convert_encoding($msg1,'gbk','utf-8');        //这个就没有乱码
    $post_data="act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$msg."&destnumbers=".$tel."&connkey=";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);                //返回内容中不包含 HTTP 头

    curl_setopt($ch, CURLOPT_URL,$serverUrl);
    curl_setopt($ch, CURLOPT_NOBODY, 0);                 //读取页面内容
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);            //不自动显示内容

   $sFile = curl_exec($ch);
    curl_close($ch);*/	
	
$sFile=file_get_contents("http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$msg."&destnumbers=".$tel."&connkey=&booktime=".$booktime);	
	$result= $sFile;   		
	
			$a=explode('&',$result);
            $b= explode('=',$a[0]);
			$state=$b[1];
	
		 if (!get_magic_quotes_gpc()){$msg=addslashes($msg);}
		 $employeeid=$this ->dbObj-> GetOne('select A.employee_id from '.WEB_ADMIN_TABPOX.'employee A INNER JOIN '.WEB_ADMIN_TABPOX.'user B ON A.employee_id=B.employee_id where B.userid='.$this->getUid());
		$sqlstr="( '".$customer_id."','".$customer_name."', '会员开卡欢迎短信', '".$msg1."','".date('Y-m-d')."', '".$tel."', '".$employeeid."','".$_POST["man"]."', '".$state."','5','".date('Y-m-d')."','".$_SESSION["currentorgan"]."')";
		
		//echo "INSERT INTO `".WEB_ADMIN_TABPOX."messagerecord` (`customer_id`, `customer_name`, `title`, `content`, `senddate`,  `handphone`, `employee_id`, `man`,  `state`, `messagetypeid`, `booktime`,`agencyid`)VALUES ".$sqlstr;
		$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."messagerecord` (`customer_id`, `customer_name`, `title`, `content`, `senddate`,  `handphone`, `employee_id`, `man`,  `state`, `messagetypeid`, `booktime`,`agencyid`)VALUES ".$sqlstr);
		if($b[1]=='0'){echo "短信发送成功";}else if($b[1]=='100'){ echo '发送失败' ;} 	
		 }
		 $marketingcardname=$this -> dbObj -> GetOne('SELECT cardlevel_name  FROM '.WEB_ADMIN_TABPOX.'memcardlevel    WHERE cardlevel_id = '.$_POST["cardlevel_id"]);
		// echo 'SELECT cardlevel_name  FROM '.WEB_ADMIN_TABPOX.'memcardlevel    WHERE cardlevel_id = '.$_POST["cardlevel_id"];
		 $marketingcarddata =$this -> dbObj -> GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcard  WHERE marketingcard_id = "'.$marketingcard_id.'" and agencyid='.$_SESSION["currentorgan"]);
		 $carddata=$marketingcarddata['marketingcard_id']."@@@".$marketingcarddata['marketingcard_name']."@@@".$cardlevel_name."@@@".$marketingcarddata['timelimit']."@@@".$marketingcarddata['price']."@@@".$marketingcarddata['totallimit'];
		
		echo "<script>  alert('开卡成功,确定填写详细信息'); location.href='?action=step3&cardtypeid=".$cardtype_id."&carddata=".$carddata."';</script>";
		}
	    else
		exit("<script>alert('开卡失败，返回重新操作');location.href='creatmember.php?action=step2';</script>");
		 }else{
		exit("<script>alert('该顾客已经是会员，请不要重复开卡');location.href='creatmember.php?action=step2';</script>");
		 }
	}
	function step3(){
		//定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','creatmember3.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
		$carddata=$_GET['carddata'];
		$carddata = explode('@@@',$carddata);
		$marketingcard_id=$carddata[0];
		$marketingcard_name=$carddata[1];
		$marketingcardtype_name=$carddata[2];
		$timelimit=$carddata[3];
		$price=$carddata[4];
		$t -> set_var('marketingcard_id',$marketingcard_id);
		$t -> set_var('marketingcard_name',$marketingcard_name);
		$cardtypeid=$_GET['cardtypeid']==''?5:$_GET['cardtypeid'];
		
		$t -> set_var('markercardtypelist',$this ->selectlist1('marketingcardtype','marketingcardtype_id','marketingcardtype_name',$cardtypeid));
		$t -> set_var('markercardlist',$this ->selectlist('memcardlevel','cardlevel_id','cardlevel_name',$marketingcard_id));
		$t -> set_var('marketingcardtype_name',$marketingcardtype_name);
		$t -> set_var('timelimit',$timelimit);
		$t -> set_var('price',$price);
		$t -> set_var('standardunit',$code);
		$t -> set_var('beauty_id','');
		$t -> set_var('beauty_name','');
		$t -> set_var('employee_id','');
		$t -> set_var('employee_name','');
		$t -> set_var('number',1);
		$t -> set_var('action','step3');
		$t -> set_var('cardtypeid',$_GET['cardtypeid']);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function step4(){
		$this->	CardObj=new card();
		$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");
		//$Prefix='XM';
		$Prefixname=array('XM','XM','LC','TY','GS','HJ','XJ','CZ','ZDY');
		
		$agency_no=$_SESSION["agency_no"];
		
		
		$number=5;
			
			$Prefix=$Prefixname[$_POST['cardtypeid']];
			$cardtable=$cardtable_name[$_POST['cardtypeid']];
			$column=$cardtable.'_no';
			$id=$cardtable.'_id';
			$table=$cardtable;
			$itemcard_no=$this->makeno($Prefix,$agency_no,$table,$column,$number,$id);	
		 	$card_no=$itemcard_no;
			$marketingcard_id=$_POST['marketingcard_id'];
			
			$customerid=$_SESSION['currentcustomerid']?$_SESSION['currentcustomerid']:'0';
			$employeeid=$_POST['employee_id'];
			$beauty_id=$_POST['beauty_id'];
			$agencyid=$_SESSION["currentorgan"];
			//$cardtable='itemcard';
			$employee_id=$this ->dbObj-> GetOne('select A.employee_id from '.WEB_ADMIN_TABPOX.'employee A INNER JOIN '.WEB_ADMIN_TABPOX.'user B ON A.employee_id=B.employee_id where B.userid='.$this->getUid());

			
			$id = $this-> CardObj->creatcard($card_no,$marketingcard_id,$customerid,$employee_id,$agencyid,$cardtable,$beauty_id);
		 
			if($_POST['cardtypeid']==7){//如果是储值卡
			//读出储值卡密码
			$password=$this ->dbObj-> GetOne('select password from '.WEB_ADMIN_TABPOX.'membercard  where customer_id="'.$customerid.'" order by  membercard_id desc'); 
			 
			//插入卡密码。
			$this ->dbObj-> Execute('update '.WEB_ADMIN_TABPOX.$cardtable.' set psw="'.$password.'" where '.$cardtable.'_id="'.$id .'"'); 
			 
			}
		
		 
		 
		 $this->SellObj=new sell();
		 
		 //$employeeid=$this ->dbObj-> GetOne('select A.employee_id from '.WEB_ADMIN_TABPOX.'employee A INNER JOIN '.WEB_ADMIN_TABPOX.'user B ON A.employee_id=B.employee_id where B.userid='.$this->getUid());
		 $sellidcount=$this ->dbObj-> GetOne('select count(*) from '.WEB_ADMIN_TABPOX.'sell  where sell_no="'.$_SESSION["sellno"].'"');
		// echo 'select count(*) from '.WEB_ADMIN_TABPOX.'sell  where sell_no='.$_SESSION["sellno"].'"';
		 if ($sellidcount==0){//如果没有新单号则插入新单号
		 $employeeid=$this ->dbObj-> GetOne('select A.employee_id from '.WEB_ADMIN_TABPOX.'employee A INNER JOIN '.WEB_ADMIN_TABPOX.'user B ON A.employee_id=B.employee_id where B.userid='.$this->getUid());
		 $sellid=$this->SellObj->creatsellno($_SESSION["sellno"],$_SESSION["currentcustomerid"],$employeeid,$_SESSION["currentorgan"],$cardtable='sell');
		 
		 $_SESSION["sellid"]=$sellid;
		 }else{
		 $sellid=$_SESSION["sellid"];
		 }
		 $item_type=3;
		 $item_id=$_POST['marketingcard_id'];
		 $number=$_POST['number'];
		 $value=$_POST['price'];
		 $price=$_POST['price'];
		 $discount=10;
		 $beauty_id=$_POST['beauty_id'];
		 $cardtable='sellcarddetail';
		 $employee_id=$_POST['employee_id'];
		 $cardtype=$_POST['cardtypeid'];
		 $cardid=$_POST['marketingcard_id'];
		 $customercardid=$id;
		 $id = $this->SellObj->addsellitem($sellid,$item_type,$item_id,$number,$value,$price,$discount,$beauty_id,$cardtable,$cardtype,$cardid,$customercardid,$employee_id);
		// $this -> dbObj -> Execute('INSERT INTO  '.WEB_ADMIN_TABPOX.'sell WHERE membershipcard_id in('.$delid.')');
		
		 
		 //定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','creatmember4.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]

		$customer_id= $_SESSION['currentcustomerid'];
		$membercard_no=$_SESSION["membercardno"];	
		$t -> set_var('customer_id',$customer_id);
		$t -> set_var('membercard_no',$membercard_no);	
		$t -> set_block('f','mainlist','ml');
		

		
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
			
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table .' where agencyid ='.$_SESSION["currentorgan"]);
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
		
		function selectlist2($table,$id,$name,$selectid=0){
			 
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table." where  ('".date('Y-m-d',time())."' >bgdate	and '".date('Y-m-d',time())."'<= enddate)  or limitdate=1" );
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
		
function makeno($Prefix,$agency_no,$table,$column,$number,$id){
	$cardtable_name=array('itemcard'=>'XM',"itemcard"=>'XM',"treatmentcard"=>"LC","experiencecard"=>"TY","feelingcard"=>"GS","membershipcard"=>"HJ","cashcoupon"=>"XJ","storedvaluedcard"=>"CZ","card"=>"ZD","membercard"=>"M");
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".WEB_ADMIN_TABPOX.$table." where  ".$table."_no LIKE '".$cardtable_name[$table]."%' AND agencyid =".$_SESSION["currentorgan"]." order by ".$id." desc limit 1");

//echo $this -> dbObj -> GetRow("select ".$column." from ".$table." order by ".$id." desc limit 1");
$nostr=$nostr[$column];
if($nostr==''){
$nostr=$Prefix.$agency_no.str_pad(1,$number,'0',STR_PAD_LEFT);

}else{
$nostr=mb_substr($nostr,strlen($nostr)-$number,$number,'utf-8');
$nostr=$nostr+1;
$nostr=str_pad($nostr,$number,'0',STR_PAD_LEFT);
$nostr=$Prefix.$agency_no.$nostr;
}
return $nostr;
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
		exit("<script>alert('$info');location.href='creatmember.php';</script>");
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
  