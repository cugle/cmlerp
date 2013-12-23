<?
/**
 * @package System
 */
 //商品流水账
//require(WEB_ADMIN_HTTPPATH.'/admin.inc.php');
//require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class performancehistory extends admin {
	function main($sellid){
	 $agencyid=$_SESSION["currentorgan"];
	 //$selldata=$this -> dbObj ->GetRow("select * from ".WEB_ADMIN_TABPOX."sell where agencyid =".$agencyid." and sell_id=".$sellid);
	 $sql1="select * from ".WEB_ADMIN_TABPOX."sellservicesdetail where agencyid =".$agencyid;
	 $sql2="select * from ".WEB_ADMIN_TABPOX."selldetail where agencyid =".$agencyid;
	 $sql3="select * from ".WEB_ADMIN_TABPOX."sellcarddetail where agencyid =".$agencyid;
	 $sql4= "select * from ".WEB_ADMIN_TABPOX."sellconsumedetail where agencyid =".$agencyid;
	 $sql5="select * from ".WEB_ADMIN_TABPOX."sellotherdetail where agencyid =".$agencyid;
	 $sql=$sql4." union ".$sql1." union ".$sql2." union ".$sql5." union ".$sql3;//按此顺序搜索本单顾问
	 $sql="select * from (".$sql.") A INNER JOIN ".WEB_ADMIN_TABPOX."sell B ON A.sell_id= B.sell_id where B.sell_id in(".$sellid.")";	
	
	 $sell=$this->dbObj->Execute("select B.*,sum(amount) as manual,A.employee_id as employeeid from (".$sql4." union ".$sql1.") A INNER JOIN ".WEB_ADMIN_TABPOX."sell B ON A.sell_id= B.sell_id where B.sell_id in(".$sellid.") and A.agencyid =".$agencyid.' group by A.sell_id');//服务类金额

	while($selldata= $sell-> FetchRow()){
	
	//echo $selldata['manual']-$selldata['xianjinquanvalue']-$selldata['chuzhikavalue']-$selldata['zengsongvalue']-$selldata['yufuvalue'];
	 $manual["'".$selldata['employeeid']."'"]=$manual["'".$selldata['employeeid']."'"]+$selldata['manual']-$selldata['xianjinquanvalue']-$selldata['chuzhikavalue']-$selldata['zengsongvalue']-$selldata['yufuvalue'];//手工费=券类全部算手工+划扣卡项+牌价服务-现金券-储值卡-赠送金额-预付手工
	
	}
	 //计算顾问业绩
	 //刷卡+现金-定金-开卡费-产品费用=该单顾问业绩,顾问搜索默认顺序 服务，产品 ，预付，开卡。
	 //开卡费（非会籍卡）-定金=开卡业绩（多张卡怎么办？）
	 //因为业绩只算手工，所以   （预收款不算业绩）业绩=券类+划卡+牌价服务-现金券-储值卡-赠送金额-扣预收款。
	 //（预收款算业绩）业绩=券类+划卡+预收款+牌价服务-现金券-储值卡-赠送金额-扣预收款
	 
	 //计算美容师业绩
	 //手工费=券类全部算手工+划扣卡项+牌价服务-现金券-储值卡-赠送金额-预付手工。
		//$manual=$manual?$manual:0;
		
		return $manual;
 
 
	
}
	function main1($sellid){//一个单，返回该单手工费。
	 $agencyid=$_SESSION["currentorgan"];
	 //$selldata=$this -> dbObj ->GetRow("select * from ".WEB_ADMIN_TABPOX."sell where agencyid =".$agencyid." and sell_id=".$sellid);
	 $sql1="select * from ".WEB_ADMIN_TABPOX."sellservicesdetail where agencyid =".$agencyid;
	 $sql2="select * from ".WEB_ADMIN_TABPOX."selldetail where agencyid =".$agencyid;
	 $sql3="select * from ".WEB_ADMIN_TABPOX."sellcarddetail where agencyid =".$agencyid;
	 $sql4= "select * from ".WEB_ADMIN_TABPOX."sellconsumedetail where agencyid =".$agencyid;
	 $sql5="select * from ".WEB_ADMIN_TABPOX."sellotherdetail where agencyid =".$agencyid;
	 $sql=$sql4." union ".$sql1." union ".$sql2." union ".$sql5." union ".$sql3;//按此顺序搜索本单顾问
	 $sql="select * from (".$sql.") A INNER JOIN ".WEB_ADMIN_TABPOX."sell B ON A.sell_id= B.sell_id where B.sell_id in=".$sellid." and agencyid =".$agencyid;	
	 $selldata=$this->dbObj->GetRow("select B.*,sum(amount) as manual,A.employee_id as employeeid from (".$sql4." union ".$sql1.") A INNER JOIN ".WEB_ADMIN_TABPOX."sell B ON A.sell_id= B.sell_id where B.sell_id =".$sellid);//服务类金额
	 //echo "select B.*,sum(amount) as manual,A.employee_id as employeeid from (".$sql4." union ".$sql1.") A INNER JOIN ".WEB_ADMIN_TABPOX."sell B ON A.sell_id= B.sell_id where B.sell_id =".$sellid;
	
	 $manual=$selldata['manual']-$selldata['xianjinquanvalue']-$selldata['chuzhikavalue']-$selldata['zengsongvalue']-$selldata['yufuvalue'];//手工费=券类全部算手工+划扣卡项+牌价服务-现金券-储值卡-赠送金额-预付手工
	 
	 //计算顾问业绩
	 //刷卡+现金-定金-开卡费-产品费用=该单顾问业绩,顾问搜索默认顺序 服务，产品 ，预付，开卡。
	 //开卡费（非会籍卡）-定金=开卡业绩（多张卡怎么办？）
	 //因为业绩只算手工，所以   （预收款不算业绩）业绩=券类+划卡+牌价服务-现金券-储值卡-赠送金额-扣预收款。
	 //（预收款算业绩）业绩=券类+划卡+预收款+牌价服务-现金券-储值卡-赠送金额-扣预收款
	 
	 //计算美容师业绩
	 //手工费=券类全部算手工+划扣卡项+牌价服务-现金券-储值卡-赠送金额-预付手工。
		$manual=$manual?$manual:0;
		return $manual;
 
 
	
}	
}
//$main = new stock();
//$main -> Main();
?>
  