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
            $this -> checkUser();//��֤��ݣ���һ������Ҫ��
            $this -> step2();			
		}else if(isset($_GET['action']) && $_GET['action']=='step3'){
            $this -> checkUser();//��֤��ݣ���һ������Ҫ��
            $this -> step3();			
		}else if(isset($_GET['action']) && $_GET['action']=='step1_save'){
            $this -> checkUser();//��֤��ݣ���һ������Ҫ��
            $this -> step1_save();			
		}else if(isset($_GET['action']) && $_GET['action']=='step2_save'){
            $this -> checkUser();//��֤��ݣ���һ������Ҫ��
			
            $this -> step2_save();			
		}else if(isset($_GET['action']) && $_GET['action']=='step4'){
            $this -> checkUser();//��֤��ݣ���һ������Ҫ��
            $this -> step4();			
		}else{
            parent::Main();
        }
    }	
	function disp(){
		$t = new Template('../template/pos');
		$t -> set_file('f','customer_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //�޸���߽��Ϊ[#
        $t->right_delimiter = "#]"; //�޸��ұ߽��#]	

 
		$Prefix='gk';
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'customer';
		$column='customer_no';
		$number=5;
		$id='customer_id';	
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
		$t -> set_var('userid',$this->getUid());	
		$t -> set_var('picurl',"��ʱû����Ƭ");
		$t -> set_var('picpath',"");	
		$t -> set_var('birthday',date("Y-m-d"));
	 
if ($date['state']==1){

		$t -> set_var('state','<input id="state" type="radio" checked="checked" value="1" name="state" /><label for="state">����</label><input id="state" type="radio" value="0" name="state" /><label for="state">ͣ��</label>');
}else
{
		$t -> set_var('state','<input id="state" type="radio"  value="1" name="state" /><label for="state_0">����</label><input id="state" type="radio" value="0" checked="checked" name="state" /><label for="state">ͣ��</label>');
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
		//����ģ��
		$info = '����';	
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."customer` (`customer_no`, `customer_name`, `address`, `genderid`, `birthday`,  `idnumber`, `tel`, `handphone`,  `zipcode`, `email`,  `memo`,  `agencyid`, `picpath`,ismarry,province,city,qq)VALUES ( '".$_POST["customer_no"]."','".$_POST["customer_name"]."', '".$_POST["address"]."','".$_POST["genderid"]."', '".$_POST["birthday"]."', '".$_POST["idnumber"]."','".$_POST["tel"]."', '".$_POST["handphone"]."','".$_POST["zipcode"]."', '".$_POST["email"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["picpath"]."','".$this->intnonull($_POST["ismarry"])."', '".$_POST["province"]."','".$_POST["city"]."','".$_POST["qq"]."')");

//echo "INSERT INTO `".WEB_ADMIN_TABPOX."customer` (`customer_no`, `customer_name`, `address`, `genderid`, `birthday`,  `idnumber`, `tel`, `handphone`,  `zipcode`, `email`,  `memo`,  `agencyid`, `picpath`,ismarry,province,city)VALUES ( '".$_POST["customer_no"]."','".$_POST["customer_name"]."', '".$_POST["address"]."','".$_POST["genderid"]."', '".$_POST["birthday"]."', '".$_POST["idnumber"]."','".$_POST["tel"]."', '".$_POST["handphone"]."','".$this->intnonull($_POST["zipcode"])."', '".$_POST["email"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["picpath"]."','".$this->intnonull($_POST["ismarry"])."', '".$_POST["province"]."','".$_POST["city"]."')";
			$id = $this -> dbObj -> Insert_ID();
		if(mysql_affected_rows())
		 
		echo "<script>  question = confirm('�����ͻ��ɹ�,�Ƿ�Ϊ�����ÿͻ�������������ȷ��');if (question != '0'){location.href='creatmember.php?action=step2&customerid=".$id."'; }else{window.close();}</script>";
	    else
		exit("<script>alert('����ʧ�ܣ��������²���');location.href='creatmember.php';</script>");
	
	}	
	function step2(){

			
		$t = new Template('../template/pos');
		$t -> set_file('f','membercard_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //�޸���߽��Ϊ[#
        $t->right_delimiter = "#]"; //�޸��ұ߽��#]	


		 
			$t -> set_var('action','add');
			$t -> set_var('actionName','����');
		//$t -> set_var('membercard_no',"");	
		$Prefix='';
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'membercard';
		$column='membercard_no';
		$number=5;
		$id='membercard_id';			
		$t -> set_var('membercard_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));
		$t -> set_var('code',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));
		$t -> set_var('man',"");

		$customerid=$_GET['customerid'];
		if($customerid<>''){
		$customerdata=$this -> dbObj -> GetRow('SELECT customer_name,customer_id FROM '.WEB_ADMIN_TABPOX.'customer   WHERE customer_id in('.$customerid.')');
		$t -> set_var('customer_name',$customerdata['customer_name']);	
		$t -> set_var('customer_id',$customerdata['customer_id']);
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
		$t -> set_var('picurl',"��ʱû����Ƭ");	
		$t -> set_var('startdate',date("Y-m-d"));
		$years = date("Y"); //��date()��ʽȡ��Ŀǰ��ݸ�ʽ0000
		$months = date("m"); //��date()��ʽȡ��Ŀǰ�·ݸ�ʽ00
		$days = date("d"); //��date()��ʽȡ��Ŀǰ���ڸ�ʽ00

		$t -> set_var('overdate',date("Y-m-d",mktime(0,0,0,$months,$days,$years+2)));
		$t -> set_var('updid','');
if ($date['state']==1){

		$t -> set_var('state','<input id="state" type="radio" checked="checked" value="1" name="state" /><label for="state">����</label><input id="state" type="radio" value="0" name="state" /><label for="state">ͣ��</label>');
}else
{
		$t -> set_var('state','<input id="state" type="radio"  value="1" name="state" /><label for="state_0">����</label><input id="state" type="radio" value="0" checked="checked" name="state" /><label for="state">ͣ��</label>');
}
		//$t -> set_var('brandlist',$this ->selectlist('brand','brand_id','brand_name',$data['brandid']));
		//$t -> set_var('genderlist',"111");	
		//echo $data['genderid'];
		$t -> set_var('genderlist',$this -> gender($data['genderid']));	
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		$t -> set_var('memcardlevellist',$this ->selectlist('memcardlevel','cardlevel_id','cardlevel_name',$data['cardlevel_id']));						

		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');		
		
	}
	

	
	function step2_save(){

			$info = '����';	
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."membercard` (`membercard_no`, `code`,`cardlevel_id`, `customer_id`, `score`,`startdate`,`overdate`,`man`, `agencyid`)VALUES ( '".$_POST["membercard_no"]."','".$_POST["code"]."','".$_POST["cardlevel_id"]."', '".$_POST["customer_id"]."','".$this->intnonull($_POST["score"])."', '".$_POST["startdate"]."', '".$_POST["overdate"]."',  '".$_POST["man"]."', '".$_SESSION["currentorgan"]."')");

//echo "INSERT INTO `".WEB_ADMIN_TABPOX."membercard` (`membercard_no`, `cardlevel_id`, `customer_id`, `score`,`startdate`,`overdate`,` man`, ` agencyid`)VALUES ( '".$_POST["membercard_no"]."','".$_POST["cardlevel_id"]."', '".$_POST["customer_id"]."','".$this->intnonull($_POST["score"])."', '".$_POST["startdate"]."', '".$_POST["overdate"]."',  '".$_POST["man"]."', '".$_SESSION["currentorgan"]."')";
			$id = $this -> dbObj -> Insert_ID();
		 $_SESSION['currentcustomerid']=$_POST["customer_id"];
		 $_SESSION["membercardno"]=$_POST["membercard_no"];
		if(mysql_affected_rows()){
		 $marketingcardname=$this -> dbObj -> GetOne('SELECT cardlevel_name  FROM '.WEB_ADMIN_TABPOX.'memcardlevel    WHERE cardlevel_id = '.$_POST["cardlevel_id"]);
		// echo 'SELECT cardlevel_name  FROM '.WEB_ADMIN_TABPOX.'memcardlevel    WHERE cardlevel_id = '.$_POST["cardlevel_id"];
		 $marketingcarddata =$this -> dbObj -> GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcard  WHERE marketingcard_name like "%'.$marketingcardname.'%"');
		 $carddata=$marketingcarddata['marketingcard_id']."@@@".$marketingcarddata['marketingcard_name']."@@@�Ἦ��@@@".$marketingcarddata['timelimit']."@@@".$marketingcarddata['price']."@@@".$marketingcarddata['totallimit'];
		 
		echo "<script>  alert('�����ɹ�,ȷ����д��ϸ��Ϣ'); location.href='?action=step3&cardtypeid=5&carddata=".$carddata."';</script>";
		}
	    else
		exit("<script>alert('����ʧ�ܣ��������²���');location.href='creatmember.php?action?step2';</script>");
	}
	function step3(){
		//����ģ��
		$t = new Template('../template/pos');
		$t -> set_file('f','creatmember3.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //�޸���߽��Ϊ[#
        $t->right_delimiter = "#]"; //�޸��ұ߽��#]		
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
			$table=WEB_ADMIN_TABPOX.$cardtable;
		$itemcard_no=$this->makeno($Prefix,$agency_no,$table,$column,$number,$id);	
		 	$card_no=$itemcard_no;
			$marketingcard_id=$_POST['marketingcard_id'];
			
			$customerid=$_SESSION['currentcustomerid']?$_SESSION['currentcustomerid']:'0';
			$employeeid=$_POST['employee_id'];
			$agencyid=$_SESSION["currentorgan"];
			//$cardtable='itemcard';


			
		 $id = $this-> CardObj->creatcard($card_no,$marketingcard_id,$customerid,$employeeid,$agencyid,$cardtable);
		 
		 $this->SellObj=new sell();
		 
		 //$employeeid=$this ->dbObj-> GetOne('select A.employee_id from '.WEB_ADMIN_TABPOX.'employee A INNER JOIN '.WEB_ADMIN_TABPOX.'user B ON A.employee_id=B.employee_id where B.userid='.$this->getUid());
		 $sellidcount=$this ->dbObj-> GetOne('select count(*) from '.WEB_ADMIN_TABPOX.'sell  where sell_no="'.$_SESSION["sellno"].'"');
		// echo 'select count(*) from '.WEB_ADMIN_TABPOX.'sell  where sell_no='.$_SESSION["sellno"].'"';
		 if ($sellidcount==0){//���û���µ���������µ���
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
		 $beauty_id=$_POST['beauty_id'];;
		 $cardtable='sellcarddetail';
		 $employee_id=$_POST['employee_id'];
		 $id = $this->SellObj->addsellitem($sellid,$item_type,$item_id,$number,$value,$price,$discount,$beauty_id,$cardtable,0,0,0,$employee_id);
		// $this -> dbObj -> Execute('INSERT INTO  '.WEB_ADMIN_TABPOX.'sell WHERE membershipcard_id in('.$delid.')');
		
		 
		 //����ģ��
		$t = new Template('../template/pos');
		$t -> set_file('f','creatmember4.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //�޸���߽��Ϊ[#
        $t->right_delimiter = "#]"; //�޸��ұ߽��#]

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
			$str ="<option value='1' selected>��</option><option value='2'>Ů</option>";	
			else
			$str ="<option value='1'>��</option><option value='2'  selected>Ů</option>";			
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
function makeno($Prefix,$agency_no,$table,$column,$number,$id){
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by ".$id." desc limit 1");
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
		$arr="<option value='1' selected>һ��</option><option value='2' >����</option>";
		}else if($type=='2')
		{$arr="<option value='1' >һ��</option><option value='1' selected>����</option>";
		}else
		{
		$arr="<option value='1' >һ��</option><option value='1'>����</option>";
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
	#[������������]
	if(strpos($url,"?") === false)
	{
		$url = $url."?cgrand=cml";
	}
	#[����ҳ��]
	$totalPage = intval($total/$psize);
	if($total%$psize)
	{
		$totalPage++;#[�ж��Ƿ���࣬��棬���һ
	}
	#[�����ҳ����Ϊ1��0ʱ������ʾ]
	if($totalPage<2)
	{
		return false;
	}
	#[�жϷ�ҳID�Ƿ����]
	if(empty($pageid))
	{
		$pageid = 1;
	}
	#[�ж������ҳID������ҳ��ʱ]
	if($pageid > $totalPage)
	{
		$pageid = $totalPage;
	}
	#[Html]
	$array_m = 0;
	if($pageid > 0)
	{
		$returnlist[$array_m]["url"] = $url;
		$returnlist[$array_m]["name"] = "��ҳ";
		$returnlist[$array_m]["status"] = 0;
		if($pageid > 1)
		{
			$array_m++;
			$returnlist[$array_m]["url"] = $url."&pageid=".($pageid-1);
			$returnlist[$array_m]["name"] = "��ҳ";
			$returnlist[$array_m]["status"] = 0;
		}
	}
	if($halfPage>0)
	{
		#[����м���]
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
		#[���select����м���]
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
	#[���β��]
	if($pageid < $totalPage)
	{
		$array_m++;
		$returnlist[$array_m]["url"] = $url."&pageid=".($pageid+1);
		$returnlist[$array_m]["name"] = "��ҳ";
		$returnlist[$array_m]["status"] = 0;
	}
	$array_m++;
	if($pageid != $totalPage)
	{
		$returnlist[$array_m]["url"] = $url."&pageid=".$totalPage;
		$returnlist[$array_m]["name"] = "βҳ";
		$returnlist[$array_m]["status"] = 0;
	}
	#[��֯��ʽ]
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
  