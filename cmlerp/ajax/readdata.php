<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/price.cls.php');
class PageUser extends admin {
function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='sellcount'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> sellcount();			
		}else if(isset($_GET['action']) && $_GET['action']=='xjq'){
		  
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> xjq();			
		}else if(isset($_GET['action']) && $_GET['action']=='czk'){
		  
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> czk();			
		}else if(isset($_GET['action']) && $_GET['action']=='notmember'){
		  
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> notmember();			
		}else if(isset($_GET['action']) && $_GET['action']=='editbill'){
		  
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> editbill();			
		}else if(isset($_GET['action']) && $_GET['action']=='chargeczk'){
		  
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> chargeczk();			
		}else if(isset($_GET['action']) && $_GET['action']=='sellhiddenred1'){
		
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> sellhiddenred1();			
		}else if(isset($_GET['action']) && $_GET['action']=='changecard'){
		
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> changecard();			
		}else if(isset($_GET['action']) && $_GET['action']=='changeproduce'){
		
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> changeproduce();			
		}else if(isset($_GET['action']) && $_GET['action']=='getsession'){
		
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> getsession();			
		}else if(isset($_GET['action']) && $_GET['action']=='setmanualbillno'){
			$this -> checkUser();//验证身份，这一步很重要。
            $this -> setmanualbillno();	

		}else{
            parent::Main();
        }
    }	
	
function setmanualbillno(){
			$_SESSION["manualbillno"]=$_POST['value'];
			echo 1;
}
function xjq(){//现金券
$table=$_POST['table']?$_POST['table']:'cashcoupon';
$column=$_POST['column']?$_POST['column']:'cashcoupon_no';
$value=$_POST['value']?$_POST['value']:'XJ0010000';
$agencyid=$_SESSION["currentorgan"]?$_SESSION["currentorgan"]:1;	
	if($this->checkxjq($value)){
	$xjqvalue = $this -> dbObj -> GetOne("select value from  ".WEB_ADMIN_TABPOX."cashcoupon   where agencyid=".$agencyid."  and cashcoupon_no='".$value."'");
	echo  '1@@@此券有效@@@'.$xjqvalue;
	}else{
	echo  '0@@@此券无效，请查正后输入@@@0';
	}
	} 
function checkxjq($xjqno){
$table=$_POST['table']?$_POST['table']:'cashcoupon';
$column=$_POST['column']?$_POST['column']:'cashcoupon_no';
$value=$_POST['value']?$_POST['value']:'XJ00100001';
$agencyid=$_SESSION["currentorgan"]?$_SESSION["currentorgan"]:1;	
	
	$xjqvalue = $this -> dbObj -> GetOne("select value from  ".WEB_ADMIN_TABPOX."cashcoupon   where agencyid=".$agencyid." and status=1 and  cashcoupon_no='".$xjqno."'");
	 
	if($xjqvalue==''){  return false;}else{
	return true;}
	}	
function czk(){//储值卡
$table=$_POST['table']?$_POST['table']:'storedvaluedcard';
$column=$_POST['column']?$_POST['column']:'storedvaluedcard_no';
$value=$_POST['value']?$_POST['value']:'CZ00100007';
$psw=$_POST['psw']?$_POST['psw']:'';
$agencyid=$_SESSION["currentorgan"]?$_SESSION["currentorgan"]:1;	

$isright = $this -> dbObj -> GetOne("select value from  ".WEB_ADMIN_TABPOX."storedvaluedcard   where   storedvaluedcard_no='".$value."' and  psw='".md5($psw)."'");
 
if($isright==''){
	echo  '0@@@卡号或密码错误，请查正后输入@@@0'+"select value from  ".WEB_ADMIN_TABPOX."storedvaluedcard   where   storedvaluedcard_no='".$value."' and  psw='".md5($psw)."'";
	}else{
	if($this->checkczk($value)){

	$czkvalue = $this -> dbObj -> GetOne("select value from  ".WEB_ADMIN_TABPOX."storedvaluedcard   where   storedvaluedcard_no='".$value."' and  psw='".md5($psw)."'");
 
	echo  '1@@@此券有效@@@'.$czkvalue;
	}else{
	echo  '0@@@此券无效，请查正后输入@@@0';
	}
	}
	} 
function checkczk($czno){
$table=$_POST['table']?$_POST['table']:'storedvaluedcard';
$column=$_POST['column']?$_POST['column']:'storedvaluedcard_no';
$value=$_POST['value']?$_POST['value']:'';
$psw=$_POST['psw']?$_POST['psw']:'';
$agencyid=$_SESSION["currentorgan"]?$_SESSION["currentorgan"]:1;	
	//echo "select value from  ".WEB_ADMIN_TABPOX."storedvaluedcard   where agencyid=".$agencyid." and  storedvaluedcard_no='".$czno."'";
	$czvalue = $this -> dbObj -> GetOne("select value from  ".WEB_ADMIN_TABPOX."storedvaluedcard   where  storedvaluedcard_no='".$value."' and  psw='".md5($psw)."'");
	 
	if($czvalue==''){  return false;}else{
	return true;}
	 

	
}
function  notmember(){
$_SESSION["currentcustomerid"]=0;
$_SESSION["membercardno"]='';
echo "1@@@设置完成";
}
function editbill($sellid){
$agencyid=$_SESSION["currentorgan"];	
$sellid=$_POST['value']?$_POST['value']:'392';
$selldata = $this -> dbObj -> GetRow("select * from  ".WEB_ADMIN_TABPOX."sell   where agencyid=".$agencyid." and  sell_id=".$sellid);

$_SESSION["sellid"]=$sellid;
$_SESSION["sellno"]=$selldata['sell_no'];
//$_SESSION["membercardno"]
$_SESSION["currentcustomerid"]=$selldata['customer_id'];
echo  $sellid.'@@@'.$selldata['sell_no'].'@@@'.$selldata['customer_id'].'@@@'.$selldata['membercard_no'];
}

function chargeczk(){
$storedvaluedcard_no=$_POST['value']?$_POST['value']:'XM00100030';
$agencyid=$_SESSION["currentorgan"];
$carddata = $this -> dbObj -> GetRow("select *,A.status as cardstatus   from  ".WEB_ADMIN_TABPOX."storedvaluedcard A LEFT JOIN  ".WEB_ADMIN_TABPOX."customer B ON A.customer_id=B.customer_id   where  A.storedvaluedcard_no='".$storedvaluedcard_no."'");
$storedvaluedcard_id=$carddata['storedvaluedcard_id'];
$storedvaluedcard_no=$carddata['storedvaluedcard_no'];
$value=$carddata['value'];
$customer_id=$carddata['customer_id'];
$customer_name=$carddata['customer_name'];
$handphone=$carddata['handphone'];
$buydate=$carddata['buydate'];
$status=$carddata['cardstatus'];
$status_name=array("未激活","使用中","挂失","停用","报废");
$statusstr='<SELECT name=status>'.$this->statuslist($status_name,$status).'</SELECT>';
$marketingcard=$this -> dbObj -> GetRow("select * from  ".WEB_ADMIN_TABPOX."marketingcard   where agencyid=".$agencyid." and  marketingcard_id='".$carddata['marketingcard_id']."'");
$marketingcard_name=$marketingcard['marketingcard_name'];
$timelimit=$marketingcard['timelimit'];
$price=$marketingcard['price'];
echo  $storedvaluedcard_id.'@@@'.$storedvaluedcard_no.'@@@'.$value.'@@@'.$customer_id.'@@@'.$customer_name.'@@@'.$marketingcard_name.'@@@'.$handphone.'@@@'.$buydate.'@@@'.$statusstr.'@@@'.$timelimit.'@@@'.$price;
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
function sellhiddenred1(){
$value=$_POST['value']<>''?$_POST['value']:'1';
$_SESSION["hiddenred"]=$value;
echo $_SESSION["hiddenred"];
}

function changecard(){
//会籍卡转会籍卡
$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");
$marketingcard_id=$_POST['marketingcard_id'];
$oldcard=explode("@@@",$_POST['marketingcard_id2']);
$customerid=$_POST['customerid'];
$marketingcard_id2=$oldcard[0];
$customercardid=$oldcard[1];
$cardtype2=$this->dbObj->GetOne('SELECT marketingcardtype_id  FROM '.WEB_ADMIN_TABPOX.'marketingcard WHERE  marketingcard_id='.$marketingcard_id2);
$cardtable2=$cardtable_name[$cardtype2];
//新卡的价格
$newmarketingcard=$this->dbObj->GetRow('SELECT *  FROM '.WEB_ADMIN_TABPOX.'marketingcard A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcarddetail B on A.marketingcard_id=B.marketingcard_id WHERE A.marketingcard_id='.$marketingcard_id);//新卡方案

$oldmarketingcard=$this->dbObj->GetRow('SELECT *  FROM '.WEB_ADMIN_TABPOX.'marketingcard A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcarddetail B on A.marketingcard_id=B.marketingcard_id WHERE A.marketingcard_id='.$marketingcard_id2);//原卡方案
$oldcard=$this->dbObj->GetRow('SELECT *  FROM '.WEB_ADMIN_TABPOX.$cardtable2.' WHERE '.$cardtable2.'_id='.$customercardid);//原卡
//价格 总次数 剩余次数。 计算出剩余金额.

$newmarketingcardprice=$newmarketingcard['price'];

//预收款价格
$yufukuan=0;
//$yufukuan=$this->dbObj->GetOne('select yufukuan from '.WEB_ADMIN_TABPOX.'customer  WHERE  customer_id='.$customerid);
if($cardtype2==5&&$newmarketingcard['marketingcardtype_id']==5){
$differenceprice=$newmarketingcardprice-$oldcard['price']*$oldcard['remaintimes']/$oldcard['totaltimes']-$yufukuan;

}else if($cardtype2==5&&$newmarketingcard['marketingcardtype_id']==7){
$differenceprice=$newmarketingcardprice-$oldcard['price']*$oldcard['remaintimes']/$oldcard['totaltimes']-$yufukuan;	


//会籍卡转储值卡
}else if($cardtype2==7&&$newmarketingcard['marketingcardtype_id']==5){
$differenceprice=$newmarketingcardprice-$oldcard['value']*$oldmarketingcard['price']/$oldmarketingcard['value']-$yufukuan;
//储值卡转会籍卡
}else if($cardtype2==7&&$newmarketingcard['marketingcardtype_id']==7){
$differenceprice=$newmarketingcardprice-$oldcard['value']*$oldmarketingcard['price']/$oldmarketingcard['value']-$yufukuan;
//储值卡转储值卡
}
echo $differenceprice;
}



function changeproduce(){
//会籍卡转会籍卡
$this->PriceObj=new price();
$value=$this->PriceObj->produceprice($_POST['customerid'],$_POST['produce_id'],$_SESSION["currentorgan"]);	
$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");
$produce_id=$_POST['produce_id'];
$oldproduce=explode("@@@",$_POST['oldproduce']);
$customerid=$_POST['customerid'];
$selldetail_id=$oldproduce[0];
$wearhouse_id=$oldproduce[1];
$oldproduce_id=$oldproduce[2];
$oldproducevalue=$this->dbObj->GetOne('SELECT amount  FROM '.WEB_ADMIN_TABPOX.'selldetail WHERE  selldetail_id='.$selldetail_id);
echo $value-$oldproducevalue;
}

function getsession(){
$sessionname=$_SESSION[$_POST['sessionname']];
echo $sessionname;
}

}

$main = new PageUser();
$main -> Main();
?>