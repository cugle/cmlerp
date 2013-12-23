<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>删除冗余信息</title>
</head>
<body>
<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class Pagedeleteredundancecustomer extends admin {
 

	function disp(){


             $inrs = &$this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX."area");
		    	while ($inrrs = $inrs -> FetchRow())
 		 {
            //echo mb_substr($inrrs['area_name'], 0, 2, 'utf-8');

			$this -> dbObj -> Execute("create table bak as (select * from s_customer group by customername having count(*)=1); insert into bak (select * from s_customer group by customername having count(*)>1); truncate table s_customer; insert into s_customer select * from bak;DROP TABLE bak;"); 
			
			 //echo "UPDATE s_customer SET areaid =".$inrrs['area_id']." WHERE address like '%".$this->showShort($inrrs['area_name'],3)."%'";
			}
			$inrs -> Close();

echo "成功删除冗余信息！";
}	

}
$main = new Pagedeleteredundancecustomer();
$main -> Main();
?>
</body> 
</html>