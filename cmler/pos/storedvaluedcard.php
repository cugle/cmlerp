<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/sell.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/card.cls.php');
class Pageservices extends admin {
    function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='newcard'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> newcard();			
		}else if(isset($_GET['action']) && $_GET['action']=='newcardsave'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> newcardsave();			
		}else if(isset($_GET['action']) && $_GET['action']=='charge'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> charge();			
		}else if(isset($_GET['action']) && $_GET['action']=='savecharge'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> savecharge();			
		}else if(isset($_GET['action']) && $_GET['action']=='edit'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> edit();			
		}else if(isset($_GET['action']) && $_GET['action']=='editsave'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> editsave();			
		}else{
            parent::Main();
        }
    }	
	function disp(){
		//定义模板
 		 
		$t = new Template('../template/pos');
		$t -> set_file('f','storedvaluedcard.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
   
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "%'.$keywords.'%"';}else{$condition=$category.' like " %'.$keywords.'%"';}
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'services  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'services p INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on p.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  p.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'services  where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
		
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  services_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
           	$t -> set_var('recordcount',$count);
						
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('gender',$inrrs["genderid"]==1?'男':'女');
				$t -> set_var('brand_name',$this -> dbObj -> getone('select brand_name from '.WEB_ADMIN_TABPOX.'brand  where brand_id ='.$inrrs["brandid"]));
				$t -> set_var('standardunit',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$inrrs["standardunit"]));
				$t -> set_var('category_name',$this -> dbObj -> getone('select category_name from '.WEB_ADMIN_TABPOX.'procatalog where category_id ='.$inrrs["categoryid"]));
				
	
				
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function newcard(){
		//定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','storedvaluedcard_new.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
		$Prefix='CZ';
		$agency_no=$_SESSION["agency_no"];
		$table='storedvaluedcard';
		$column='storedvaluedcard_no';
		$number=5;
		$id='storedvaluedcard_id';	
		$t -> set_var('storedvaluedcard_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));			
		$status_name=array("未激活","使用中","挂失","停用","报废");
		$customerid=$_SESSION["currentcustomerid"]?$_SESSION["currentcustomerid"]:0;
		$customer=$this ->dbObj-> GetRow('select * from '.WEB_ADMIN_TABPOX.'customer  where customer_id="'.$customerid.'"');
	
		$t -> set_var('customer_name',$customer['customer_name']);	
		$t -> set_var('customer_id',$customer['customer_id']);
		$t -> set_var('handphone',$customer['handphone']);	
		$t -> set_var('value',"");	
		$t -> set_var('storedvaluedcard_name',"");	
		$t -> set_var('marketingcard_id',"");	
		$t -> set_var('marketingcard_name',"");	
		//$t -> set_var('customer_id',"");	
		$t -> set_var('buydate',date('Y-m-d'));	
		$t -> set_var('activedate',date('Y-m-d'));	
		//$t -> set_var('customer_name',"");				
		$t -> set_var('error',"");	
		$t -> set_var('coderule',"");
		$t -> set_var('tips',"");
		//$t -> set_var('handphone',"");
		
		$t -> set_var('timelimit',"");
		$t -> set_var('pricepertime',"");
		$t -> set_var('totaltimes',"");	
		$t -> set_var('commission',"");	
		$t -> set_var('ucommission',"");	
		$t -> set_var('price',"");	
		$t -> set_var('password',"");	
		$t -> set_var('showeditdiv',"");
		$t -> set_var('comdisplay',"none");		
		 $t -> set_var('password',$this->genPassword(8));
		//echo $this->genPassword(8);
		$t -> set_var('statusnamelist',$this->statuslist($status_name));
		$t -> set_var('recordcount',"");
		$t -> set_var('employee_name',"");
 		$t -> set_var('employee_id',"");
		 
		$t -> set_var('beauty_name',"");
 		$t -> set_var('beauty_id',"");
		$t -> set_var('employee_no',"");
		$t -> set_var('memo',"");	
 
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function newcardsave(){
		$storedvaluedcardtype_id= "7";
		$id = 0;
		$info = '';
		$this->	CardObj=new card();
 

		$Prefix='CZ';
		$agency_no=$_SESSION["agency_no"];
		$table='storedvaluedcard';
		$column='storedvaluedcard_no';
		$number=5;
		$id='storedvaluedcard_id';	
		
		    $card_no=$_POST['storedvaluedcard_no'];
			$card_no=$card_no<>''?$card_no:$this->makeno($Prefix,$agency_no,$table,$column,$number,$id);
			
			
			
			$marketingcard_id=$_POST['marketingcard_id'];
			$customerid=$_POST['customer_id'];
			$employeeid=$_POST['employee_id']?$_POST['employee_id']:0;
			$beautyid=$_POST['beauty_id']?$_POST['beauty_id']:0;
			$agencyid=$_SESSION["currentorgan"];
			$cardtable='storedvaluedcard';
			$idstr='';
			
			for($i=0;$i<$_POST['number'];$i++){
			$card_no=$this->makeno($Prefix,$agency_no,$table,$column,$number,$id);	
			
			$storedvaluedcard_id = $this-> CardObj->creatcard($card_no,$marketingcard_id,$customerid,$employeeid,$agencyid,$cardtable,$beautyid);
			$idstr=$idstr==''?$storedvaluedcard_id:$idstr.";".$storedvaluedcard_id ;
			$this ->dbObj-> Execute('UPDATE '.WEB_ADMIN_TABPOX.'storedvaluedcard SET psw="'.md5($_POST['psw']).'"  where storedvaluedcard_id="'.$storedvaluedcard_id.'"');
			//echo 'UPDATE '.WEB_ADMIN_TABPOX.'storedvaluedcard SET psw="'.md5($_POST['psw']).'"  where storedvaluedcard_id="'.$id.'"';
			//$nostr=mb_substr($card_no,strlen($card_no)-$number,$number,'utf-8');
			//$nostr=str_pad($nostr+1,$number,'0',STR_PAD_LEFT);
			//$card_no=$Prefix.$agency_no.$nostr;
			
			if($_POST['sendmsg']=='on'){
		$message_config=explode(';',$this -> getValue('message_config'));
		$orgid=$message_config[0];
		$username=$message_config[1];
		$passwd=$message_config[2];
		$content="亲爱的{customername},您卡号为{card_no}的储值卡初始密码为{psw},感谢你光临{agency_name}.";
		$customer_id=explode(";",$_POST["customer_id"]);
		$customer_name=explode(";",$_POST["customer_name"]);
		$handphone=explode(";",$_POST["handphone"]);
		
		for ($j=0;$j<count($customer_id);$j++)	{
			$content1= str_replace('{customername}',$customer_name[$j],$content);	
			$content1= str_replace('{agency_name}',$_SESSION["currentorganname"],$content1);
			$content1= str_replace('{psw}',$_POST['psw'],$content1);	
			$content1= str_replace('{card_no}',$card_no,$content1);	
			
			$result=file_get_contents("http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$content1."&destnumbers=".$handphone[$j]."&sendTime=".date("Y-m-d H:i:s",time()));
			 		
		//echo "http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$content1."&destnumbers=".$handphone."&sendTime=".date("Y-m-d H:i:s",time());
		}
		}
		
		
			}
		 $this->SellObj=new sell();
		 
		 $employeeid=$this ->dbObj-> GetOne('select A.employee_id from '.WEB_ADMIN_TABPOX.'employee A INNER JOIN '.WEB_ADMIN_TABPOX.'user B ON A.employee_id=B.employee_id where B.userid='.$this->getUid());
		 $sellidcount=$this ->dbObj-> GetOne('select count(*) from '.WEB_ADMIN_TABPOX.'sell  where sell_no="'.$_SESSION["sellno"].'"');
		// echo 'select count(*) from '.WEB_ADMIN_TABPOX.'sell  where sell_no='.$_SESSION["sellno"].'"';
		 if ($sellidcount==0){//如果没有新单号则插入新单号
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
		 $itemmemo=$_POST['memo'];
		 $cardtype=7;//储值卡
		 $cardid=$_POST['marketingcard_id'];
		 $customercardid=$storedvaluedcard_id;
		 $idstr=explode(";",$idstr);
		
		 for($k=0;$k<count($idstr);$k++){
			
		 $number=1;
		 $customercardid=$idstr[$k];
		 $id = $this->SellObj->addsellitem($sellid,$item_type,$item_id,$number,$value,$price,$discount,$beauty_id,$cardtable,$cardtype,$cardid,$customercardid,$employee_id,$itemmemo);
		 
		 }
		 $customer_no=$this -> dbObj -> getone('select customer_no from '.WEB_ADMIN_TABPOX.'customer where  customer_id ='.$_POST["customer_id"]);
		 exit("<script>alert('添加成功');	window.returnValue='1@@@".$customer_no."';window.parent.close();</script>");	
	}
	function charge(){
		//定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','storedvaluedcard_charge.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
		$Prefix='CZ';
		$agency_no=$_SESSION["agency_no"];
		$table='storedvaluedcard';
		$column='storedvaluedcard_no';
		$number=5;
		$id='storedvaluedcard_id';	
		$t -> set_var('storedvaluedcard_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));			
 
		$t -> set_var('value',"");	
		$t -> set_var('storedvaluedcard_name',"");	
		$t -> set_var('marketingcard_id',"");	
		$t -> set_var('marketingcard_name',"");	
		$t -> set_var('customer_id',"");	
		$t -> set_var('buydate',date('Y-m-d'));	
		$t -> set_var('activedate',date('Y-m-d'));	
		$t -> set_var('customer_name',"");				
		$t -> set_var('error',"");	
		$t -> set_var('employee_name',"");
		$t -> set_var('employee_id',"");
		 
		
		$t -> set_var('chargevalue',"");
 
 
		$t -> set_var('chargememo',"");	
 
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');

	}
function savecharge(){
	//修改储值卡余额
// $this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX.'storedvaluedcard  SET value=value+'.$_POST['chargevalue'].' WHERE storedvaluedcard_no ="'.$_POST['storedvaluedcard_no'].'"');
   //账户金额
  // $acounttypeid=$_POST['acounttypeid'];
   $agencyid=$_SESSION["currentorgan"];
  // $acountdata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'account WHERE `type`='.$acounttypeid.' and  agencyid='.$agencyid);
	// echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'account WHERE `type`='.$acounttypeid.' and  agencyid='.$agencyid; 
	//	$acountid=$acountdata['account_id'];
		//$lastbalance=$acountdata['balance'];
		//$value=$_POST['chargevalue'];
		//$nowbalance=$acountdata['balance']+$value;
 		 //$this->dbObj->Execute('UPDATE '.WEB_ADMIN_TABPOX.'account   SET  balance =balance + '.$value.'  WHERE   agencyid='.$agencyid.' and account_id ='.$acountid);	
   //账户流水账
		 
		//$type=2;//储值卡充值2
		//$repaymentmemo=$_POST['repaymentmemo'];
		//$memo=$repaymentmemo<>''?$repaymentmemo:'储值卡充值';
		//$this->dbObj->Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'accounthistory (`account_id`, `value`,  `type`, `sellid`, `lastbalance`, `nowbalance`, `memo`, `agencyid`)value('.$acountid.','.$value.','.$type.',"'.$sellid.'",'.$lastbalance.','.$nowbalance.',"'.$memo.'",'.$agencyid.')') ;
// echo 'INSERT INTO '.WEB_ADMIN_TABPOX.'accounthistory (`account_id`, `value`,  `type`, `sellid`, `lastbalance`, `nowbalance`, `memo`, `agencyid`)value('.$acountid.','.$value.','.$type.',"'.$sellid.'",'.$lastbalance.','.$nowbalance.',"'.$memo.'",'.$agencyid.')';
		 //增加销售单项目
		 $this->SellObj=new sell();
		 $employeeid=$this ->dbObj-> GetOne('select A.employee_id from '.WEB_ADMIN_TABPOX.'employee A INNER JOIN '.WEB_ADMIN_TABPOX.'user B ON A.employee_id=B.employee_id where B.userid='.$this->getUid());
		 $sellidcount=$this ->dbObj-> GetOne('select count(*) from '.WEB_ADMIN_TABPOX.'sell  where sell_no="'.$_SESSION["sellno"].'"');
		 //echo 'select count(*) from '.WEB_ADMIN_TABPOX.'sell  where sell_no='.$_SESSION["sellno"].'"';
		 
		 if ($sellidcount==0){//如果没有新单号则插入新单号
		 $customerid=$_SESSION["currentcustomerid"]?$_SESSION["currentcustomerid"]:0;
		 $sellid=$this->SellObj->creatsellno($_SESSION["sellno"],$customerid,$employeeid,$_SESSION["currentorgan"],$cardtable='sell');
		 
		 $_SESSION["sellid"]=$sellid;
		 }else{
		 $sellid=$_SESSION["sellid"];
		 }
		 
		 $item_id=1;//定金$itemnamelist=array('预收款',"储值卡充值","定金","还款","赠送手工","其他","预付产品款","赠送产品款","赠送购卡款");
		 $number=1;
		 $value=$_POST["chargevalue"];
		 $price=$_POST["chargevalue"];
		 $discount=10;
		 $cardtype=0;//款项
		 //$cardid=0;
		 //echo 'select storedvaluedcard_id from '.WEB_ADMIN_TABPOX.'storedvaluedcard where storedvaluedcard_no="'.$_POST['storedvaluedcard_no'].'"';
		 $customercardid=$this -> dbObj -> getone('select storedvaluedcard_id from '.WEB_ADMIN_TABPOX.'storedvaluedcard where storedvaluedcard_no="'.$_POST['storedvaluedcard_no'].'"');
		 $cardid=$this -> dbObj -> getone('select marketingcard_id from '.WEB_ADMIN_TABPOX.'storedvaluedcard where storedvaluedcard_no="'.$_POST['storedvaluedcard_no'].'"');  
		 $employee_id=$_POST['employee_id'];
		  $itemmemo=$_POST['chargememo'];
		 //if($_POST['givingbeauty']=='on'){
			
			// $discount=0;
			   // echo  $discount;
			// }else{
		   //  $discount=10;
		
		// }
		 $item_type=5;//其他项目
		 $beauty_id=0;
		 $cardtable='sellotherdetail';
		 $id = $this->SellObj->addsellitem($sellid,$item_type,$item_id,$number,$value,$price,$discount,$beauty_id,$cardtable,$cardtype,$cardid,$customercardid,$employee_id,$itemmemo);
		// $this->dbObj->Execute('UPDATE '.WEB_ADMIN_TABPOX.'sellotherdetail   SET memo = '.$_POST['value'].'  WHERE   agencyid='.$agencyid.' and selldetail_id ='.$id);
	$customer_no=$this -> dbObj -> getone('select customer_no from '.WEB_ADMIN_TABPOX.'customer where  customer_id ='.$_POST["customer_id"]);
	 exit("<script>alert('充值成功');window.returnValue='2@@@".$customer_no."';location.href='storedvaluedcard.php';</script>");	
}
function edit(){
		//定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','storedvaluedcard_edit.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
		$Prefix='CZ';
		$agency_no=$_SESSION["agency_no"];
		$table='storedvaluedcard';
		$column='storedvaluedcard_no';
		$number=5;
		$id='storedvaluedcard_id';	
		$t -> set_var('storedvaluedcard_no','请输入卡号后回车');
		//$t -> set_var('storedvaluedcard_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));			
		$status_name=array("未激活","使用中","挂失","停用","报废");
		$t -> set_var('value',"");	
		$t -> set_var('storedvaluedcard_name',"");	
		$t -> set_var('marketingcard_id',"");	
		$t -> set_var('marketingcard_name',"");	
		$t -> set_var('customer_id',"");	
		$t -> set_var('buydate',date('Y-m-d'));	
		$t -> set_var('activedate',date('Y-m-d'));	
		$t -> set_var('customer_name',"");				
		$t -> set_var('error',"");	
		$t -> set_var('coderule',"");
		$t -> set_var('tips',"");
		$t -> set_var('handphone',"");
		
		$t -> set_var('timelimit',"");
		$t -> set_var('pricepertime',"");
		$t -> set_var('totaltimes',"");	
		$t -> set_var('commission',"");	
		$t -> set_var('ucommission',"");	
		$t -> set_var('price',"");	
		$t -> set_var('password',"");	
		$t -> set_var('showeditdiv',"");
		$t -> set_var('comdisplay',"none");		
		// $t -> set_var('password',$this->genPassword(8));
		//echo $this->genPassword(8);
		$t -> set_var('statusnamelist',$this->statuslist($status_name));
		$t -> set_var('recordcount',"");
		
 
		$t -> set_var('memo',"");	
 
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');

}
function editsave(){
	if($_POST['psw']==''){
 $this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX.'storedvaluedcard  SET status='.$_POST['status'].' ,customer_id='.$_POST['customer_id'].' , buydate="'.$_POST['buydate'].'" WHERE storedvaluedcard_no ="'.$_POST['storedvaluedcard_no'].'"');
 //echo 'UPDATE '.WEB_ADMIN_TABPOX.'storedvaluedcard  SET status='.$_POST['status'].' ,customer_id='.$_POST['customer_id'].' , buydate="'.$_POST['buydate'].'" WHERE storedvaluedcard_no ="'.$_POST['storedvaluedcard_no'].'"';
	}else{
 $this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX.'storedvaluedcard  SET status='.$_POST['status'].' ,customer_id='.$_POST['customer_id'].' ,psw="'.md5($_POST['psw']).'" , buydate="'.$_POST['buydate'].'" WHERE storedvaluedcard_no ="'.$_POST['storedvaluedcard_no'].'"');
 //echo 'UPDATE '.WEB_ADMIN_TABPOX.'storedvaluedcard  SET status='.$_POST['status'].' ,customer_id='.$_POST['customer_id'].' ,psw="'.md5($_POST['psw']).'" , buydate="'.$_POST['buydate'].'" WHERE storedvaluedcard_no ="'.$_POST['storedvaluedcard_no'].'"';
	}
	 exit("<script>alert('修改成功');location.href='storedvaluedcard.php';</script>");	
}
function genPassword($pw_length = 8){     
    $randpwd = '';

    for ($i = 0; $i < $pw_length; $i++)

    {

    $randpwd .= chr(mt_rand(48,57));

    }

    return   $randpwd;

 
}   
function makeno($Prefix,$agency_no,$table,$column,$number,$id){

$cardtable_name=array('itemcard'=>'XM',"itemcard"=>'XM',"treatmentcard"=>"LC","experiencecard"=>"TY","feelingcard"=>"GS","membershipcard"=>"M","cashcoupon"=>"XJ","storedvaluedcard"=>"CZ","card"=>"ZD");
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".WEB_ADMIN_TABPOX.$table." where ".$table."_no LIKE '".$cardtable_name[$table]."%' AND agencyid =".$_SESSION["currentorgan"]." order by ".$id." desc limit 1");
//echo "select ".$column." from ".WEB_ADMIN_TABPOX.$table." where ".$table."_no LIKE '".$cardtable_name[$table]."%' AND agencyid =".$_SESSION["currentorgan"]." order by ".$id." desc limit 1";
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
function statuslist($status_name,$arraselectid=0){
			$liststr='';
			for($i=0;$i<count($status_name);$i++){
					
			
			if($arraselectid==$i){
				$liststr=$liststr.'<option value="'.($i).'" selected>'.$status_name[$i].'</option>';	
				}else{
				$liststr=$liststr.'<option value="'.($i).'">'.$status_name[$i].'</option>';}
			}
		
			return $liststr;	
	}
	function quit($info){
		exit("<script>alert('$info');location.href='services.php';</script>");
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
$main = new Pageservices();
$main -> Main();
?>
  