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
        if(isset($_GET['action']) && $_GET['action']=='produceprice'){
			
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> produceprice();			
		} else{
            parent::Main();
        }
    }	
function produceprice(){//现金券
 $this->PriceObj=new price();
 $price=$this->PriceObj->produceprice(21,710,1);
 echo $price;
} 
	
}

$main = new PageUser();
$main -> Main();
?>