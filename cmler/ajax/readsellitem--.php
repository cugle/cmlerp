<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class PageUser extends admin {
function disp(){
//$table=$_GET['table'];
//$ziduan=$_GET['ziduan'];
//$testname=$_GET['testname'];
$table=$_POST['table']?$_POST['table']:'sellservicesdetail';
$column=$_POST['column']?$_POST['column']:'sell_no';
$value=$_POST['value']?$_POST['value']:$_SESSION["sellno"];
$agencyid=$_SESSION["currentorgan"]?$_SESSION["currentorgan"]:1;
if($column='sell_id'){
	$_SESSION["sellno"]=&$this -> dbObj -> GetOne("select  sell_no  from ".WEB_ADMIN_TABPOX."sell   where  sell_id =".$value);
	$_SESSION["currentcustomer_id"]=&$this -> dbObj -> GetOne("select  customer_id  from ".WEB_ADMIN_TABPOX."sell   where  sell_id =".$value);
	}
$inrs = $this -> dbObj -> Execute("select * from  ".WEB_ADMIN_TABPOX.$table." A INNER JOIN  ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id   where B.agencyid=".$agencyid." and  B.".$column."='".$value."'");
//echo "select * from  ".WEB_ADMIN_TABPOX.$table." A INNER JOIN  ".WEB_ADMIN_TABPOX."sell B ON A.sell_id=B.sell_id   where B.agencyid=".$agencyid." and  B.".$column."='".$value."'";
//echo "select * from (".$table." A  INNER JOIN ".WEB_ADMIN_TABPOX."membercard B ON A.customer_id=B.customer_id ) INNER JOIN ".WEB_ADMIN_TABPOX."memcardlevel C ON  B.cardlevel_id=C.cardlevel_id where B.membercard_no='".$value."'";
//$result=$this->check_chongfu($table,$ziduan,$customer_no));
//$test=$this -> dbObj ->GetOne("select * from  ".WEB_ADMIN_TABPOX.$table."  where agencyid=".$agencyid." and  sell_id='".$value."'");

//销售列表返回数组
//echo "select * from  ".WEB_ADMIN_TABPOX.$table."  where agencyid=".$_SESSION["currentorgan"]." and  sell_id='".$value."'";
$table_name=array('services',"produce","services","marketingcard");	
$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");	
$result='';
 $cardtype='';
while ($inrrs = &$inrs -> FetchRow()) {
//echo 'select *  from '.WEB_ADMIN_TABPOX.$table_name[$inrrs['item_type']].'  where  '.$table_name[$inrrs['item_type']].'_id ='.$inrrs['item_id'];
$itemdata= $this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.$table_name[$inrrs['item_type']].'  where  '.$table_name[$inrrs['item_type']].'_id ='.$inrrs['item_id']);


//类别
if ($inrrs['item_type']=='0'){//购买服务
	$cardtype='服务'  ;
	$itemname=$itemdata[$table_name[$inrrs['item_type']].'_name'];
	$itemno=$itemdata[$table_name[$inrrs['item_type']].'_no'];
	}else if($inrrs['item_type']=='1'){//购买产品
	 $cardtype= &$this -> dbObj -> GetOne("select  category_name  from ".WEB_ADMIN_TABPOX."procatalog   where  category_id =".$itemdata['categoryid']);
	echo  "select A.category_name  from ".WEB_ADMIN_TABPOX."procatalog   where  category_id =".$itemdata['categoryid'];

	$itemname=$itemdata[$table_name[$inrrs['item_type']].'_name'];
	$itemno=$itemdata[$table_name[$inrrs['item_type']].'_no'];
	}else if($inrrs['item_type']=='2'){//消费卡项
		
	$itemtypedata= $this -> dbObj -> GetRow("select *  from ".WEB_ADMIN_TABPOX."marketingcard A INNER JOIN ".WEB_ADMIN_TABPOX."marketingcardtype  B ON  A.marketingcardtype_id=B.marketingcardtype_id where  A. marketingcard_id =".$inrrs['cardtype']);
	$itemname=$inrrs[$table_name['item_type'].'_name'];
	$cardtype=$itemtypedata['marketingcardtype_name'].'>>'.$itemtypedata['marketingcard_name'];
	 
	//$result = $this -> dbObj -> GetRow("select A.*, B.* ,C.* from ((".$table." A  INNER JOIN ".WEB_ADMIN_TABPOX."membercard B ON A.customer_id=B.customer_id ) INNER JOIN ".WEB_ADMIN_TABPOX."memcardlevel C ON  B.cardlevel_id=C.cardlevel_id) where B.membercard_no='".$value."'");
	}else{//购买卡项
	$cardtype='卡项';
	$itemtypedata= $this -> dbObj -> GetRow("select *  from ".WEB_ADMIN_TABPOX."marketingcard A INNER JOIN ".WEB_ADMIN_TABPOX."marketingcardtype  B ON  A.marketingcardtype_id=B.marketingcardtype_id where  A.marketingcard_id =".$inrrs['item_id']);

	$cardtype=$itemtypedata['marketingcardtype_name'];
	$itemname=$itemtypedata['marketingcard_name'];
	$itemno=$itemtypedata['marketingcard_id'];
	}

$temp=$inrrs['selldetail_id'].'@@@'.$cardtype.'@@@'.$inrrs['item_id'].'@@@'.$itemno.'@@@'.$itemname.'@@@'.$inrrs['value'].'@@@'.$inrrs['number'].'@@@'.$inrrs['amount'];
$result=$result==''?$temp:$result."|||".$temp;
//echo "'".$table_name[$inrrs['item_type']]."_name'";
}

$inrs -> Close();	

echo $result;
}

   
}
$main = new PageUser();
$main -> Main();
?>