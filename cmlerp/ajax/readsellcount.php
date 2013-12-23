<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class PageUser extends admin {
function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='sellcount'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> sellcount();			
		}else{
            parent::Main();
        }
    }	
function sellcount(){
//$table=$_GET['table'];
//$ziduan=$_GET['ziduan'];
//$testname=$_GET['testname'];
$table=$_POST['table']?$_POST['table']:'sell';
$column=$_POST['column']?$_POST['column']:'sell_no';
$value=$_POST['value']?$_POST['value']:$_SESSION["sellno"];
$agencyid=$_SESSION["currentorgan"]?$_SESSION["currentorgan"]:1;
 
$value = $this -> dbObj -> GetOne("select sell_id from  ".WEB_ADMIN_TABPOX."sell   where agencyid=".$agencyid." and  sell_no='".$value."'");

$producevalue = $this -> dbObj -> GetOne("select sum(amount) from  ".WEB_ADMIN_TABPOX."selldetail    where agencyid=".$agencyid." and  sell_id=".$value);
 
$servicesvalue = $this -> dbObj -> GetOne("select sum(amount) from  ".WEB_ADMIN_TABPOX."sellservicesdetail    where agencyid=".$agencyid." and  sell_id=".$value);

$cardvalue = $this -> dbObj -> GetOne("select sum(amount) from  ".WEB_ADMIN_TABPOX."sellcarddetail    where agencyid=".$agencyid." and  sell_id=".$value);

$consumevalue = $this -> dbObj ->GetOne("select sum(amount) from  ".WEB_ADMIN_TABPOX."sellconsumedetail    where agencyid=".$agencyid." and  sell_id=".$value);
$othervalue = $this -> dbObj ->GetOne("select sum(amount) from  ".WEB_ADMIN_TABPOX."sellotherdetail    where agencyid=".$agencyid." and  sell_id=".$value);
$producevalue=$this->checknull($producevalue);
$cardvalue=$this->checknull($cardvalue);
$consumevalue=$this->checknull($consumevalue);
$servicesvalue=$this->checknull($servicesvalue);
$othervalue=$this->checknull($othervalue);
$totalvule=$producevalue+$cardvalue+$consumevalue+$servicesvalue+$othervalue;
echo $totalvule;
}

function checknull($value='0.00'){
	return $value;
	}   
}
$main = new PageUser();
$main -> Main();
?>