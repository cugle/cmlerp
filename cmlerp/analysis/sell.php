<?
/**
 * @package System
 */
 
/*销售单凭证生产模型：
1/循环付款方式：
借 现金/银行存款/储值卡/现金券/赠送金额/预付账款  对应付款方式付款金额
  贷 应收  实收金额 

2/循环销售单项目明细：
借 应收 应收金额-还款金额
loop{  
1、开卡：
   贷 开卡收入

2、开储值卡
借 储值卡成本  300
   贷 储值卡   1000

3、产品：
   贷 产品收入
非赠送：
借商品销售成本-直营体系成本
   贷 库存商品
赠送：
借 配送费用
   贷 库存商品

4、卡/券类消费 
   贷 手工收入

5、牌价服务项目： 
   贷 手工收入（减去成本的折后收入） 
   贷 储值卡成本/现金券成本/赠送金额成本/

6、 预收款 
   贷 预付账款

7、储值卡充值
借 充值卡成本 300
   贷 储值卡 1000
   
8、定金
   贷 预付账款

9、还款
   不处理 

10、赠送手工
借 赠送成本
   贷 赠送
   ｝
*/

//该凭证生产前提，储值卡，现金券，赠送金额只能付牌价服务的钱。
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/charge.cls.php');
 require(WEB_ADMIN_CLASS_PATH.'/custom/sell.cls.php');
 require_once(WEB_ADMIN_CLASS_PATH.'/custom/prodaybooks.cls.php');
 require(WEB_ADMIN_CLASS_PATH.'/custom/cmlprice.cls.php');
 require(WEB_ADMIN_CLASS_PATH.'/custom/currentaccount.cls.php');
 require(WEB_ADMIN_CLASS_PATH.'/custom/performancehistory.cls.php');
class Pagecustomer extends admin {
 
    function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='audit')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> audit();
        }else if(isset($_GET['action']) && $_GET['action']=='print'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> print1();			
		}else if(isset($_GET['action']) && $_GET['action']=='return'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> returntoedit();			
		}else if(isset($_GET['action']) && $_GET['action']=='recoil'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> recoil();			
		}else if(isset($_GET['action']) && $_GET['action']=='caiwurecoil'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> caiwurecoil();			
		}else if(isset($_GET['action']) && $_GET['action']=='caiwuaudit'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> caiwuaudit();			
		}else if(isset($_GET['action']) && $_GET['action']=='sellexport'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> sellexport();			
		}else{
            parent::Main();
        }
    }
function sellexport(){
		//定义模板
		$t = new Template('../template/analysis');
		$t -> set_file('f','sellexport.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]
		$t -> set_var('bgdate',date("Y-m-d",strtotime("$m-1 month")));
		
		$t -> set_var('enddate',date('Y-m-d'));
		$t -> set_var('agencyid',$_SESSION["currentorgan"]);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
}
function  caiwurecoil(){
	 $this->chargeObj=new charge();
	 $sellid=$_GET['sellid'];
	 $sellid=explode(",",$sellid);
	 $agencyid =$_SESSION["currentorgan"];
	 $result=0;
	 for($i=0;$i<count($sellid);$i++){
	$selldata=$this -> dbObj ->GetRow("SELECT * FROM  ".WEB_ADMIN_TABPOX."sell  where agencyid =".$agencyid." and sell_id=".$sellid[$i]);
		
	if( $selldata['status']==4){	 
	$result= $result+$this->chargeObj->recoil($sellid[$i],$agencyid);
	echo "单号为".$selldata['sell_no']."的单据反冲成功<BR/>";			
	}else{
	$result=$result+0;
	echo "单号为".$selldata['sell_no']."的单据反冲失败，单据可能处于未完成状态或已经审核<BR/>";	 
	}
 
	 
	 }
	 if($result==count($sellid)){
	 exit("<script>alert('反冲成功'); window.history.go(-1);</script>");	
	 }else if($result<count($sellid) &&$result>1){
	 exit("<script>alert('部分反冲成功'); window.history.go(-1);</script>");	
	 }else {
	 exit("<script>alert('操作出现问题'); window.history.go(-1);</script>");		 
	}
}
function recoil(){
	 $this -> dbObj -> Execute("START TRANSACTION");//事务开始。
	 $this->curObj=new currentaccount();
	 $this->chargeObj=new charge();
	 $sellid=$_GET['sellid'];
	 $sellid=explode(",",$sellid);
	 $agencyid =$_SESSION["currentorgan"];
	 $result=0;
	 for($i=0;$i<count($sellid);$i++){
	
	 $selldata=$this -> dbObj ->GetRow("SELECT * FROM  ".WEB_ADMIN_TABPOX."sell  where agencyid =".$agencyid." and sell_id=".$sellid[$i]);
	 
	if($selldata['status']==1 or $selldata['status']==6){	 
	$result= $result+$this->chargeObj->recoil($sellid[$i],$agencyid);
	if($selldata['status']==6){
	$curaccount=$this ->curObj->recoilrecord(1,$sellid[$i],$agencyid);//反冲对账单记录
	}
	echo "单号为".$selldata['sell_no']."的单据反冲成功<BR/>";			
	}else{
	$result=$result+0;
	echo "单号为".$selldata['sell_no']."的单据反冲失败，单据可能处于未完成状态或已经审核<BR/>";	 
	}
	 }
	
	 if($result==count($sellid)){
	 $this -> dbObj -> Execute("COMMIT");
	 exit("<script>alert('反冲成功'); window.history.go(-1);</script>");	
	 }else if($result<count($sellid) &&$result>1){
	 $this -> dbObj -> Execute("COMMIT");
	 exit("<script>alert('部分反冲成功'); window.history.go(-1);</script>");	
	 }else {
		 $this -> dbObj -> Execute("ROLLBACK");
	 exit("<script>alert('操作出现问题,数据已回滚'); window.history.go(-1);</script>");		 
	}
	}	
function audit(){
$this -> dbObj -> Execute("START TRANSACTION");//事务开始。
	 $this->curObj=new currentaccount();
	 $this->SellObj=new sell();
	 $this->prodaybooksObj=new prodaybooks();//商品流水账
	 $this->cmlpriceObj=new cmlprice();//打折处理
	 $sellid=$_GET['sellid'];
	 $sellid=explode(",",$sellid);
	 $agencyid =$_SESSION["currentorgan"];
	 $status=4;
	 $result=0;
	 for($i=0;$i<count($sellid);$i++){
	 
	 
	 $updid=$sellid[$i];
 
	//生产凭证
	//查找供应商对应的会计科目：应付账款
		  $agencyaccounttitle=$this -> dbObj -> GetRow('select xjaccounttitle_id,ykaccounttitle_id from '.WEB_ADMIN_TABPOX.'agency  where agency_id = '.$_SESSION["currentorgan"]);
		  
		  if($agencyaccounttitle['xjaccounttitle_id']=='' or $agencyaccounttitle['ykaccounttitle_id']==''){
			echo '机构资料设置有误,请设置会计关联科目';
			exit("<script>alert('提交失败'); window.history.go(-1);</script>");	
		  }
	
 	$condition =' sell_id='.$updid;
	$sql='select * from '.WEB_ADMIN_TABPOX.'sell  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;	
	
		$Prefix='VH';
		$agency_no=$_SESSION["agency_no"].date('ym',time());
		$table=WEB_ADMIN_TABPOX.'transfervoucher';
		$column='transfervoucher_no';
		$number=5;
		$id='transfervoucher_id';	
		
		$transfervoucher_no=$this->makeno($Prefix,$agency_no,$table,$column,$number,$id);	
		$man=$this -> dbObj -> getone('select B.employee_name from '.WEB_ADMIN_TABPOX.'user A INNER JOIN '.WEB_ADMIN_TABPOX.'employee B ON A.employee_id=B.employee_id  where A.userid = '.$this->getUid());
		$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  sell_id DESC  LIMIT 0 ,1");
		     while ($inrrs = &$inrs -> FetchRow()) {
			$customer_id=$inrrs['customer_id'];
			$sell_no=$inrrs['sell_no'];
			//插入主记录
			if($inrrs['status']==6){//如果是审核不通过的单重新提交。更新凭证
			$id=$this -> dbObj -> GetOne("select transfervoucher_id from `".WEB_ADMIN_TABPOX."transfervoucher` where fromtype=1 and  frombillid=".$updid);
			$this -> dbObj -> Execute("delete from `".WEB_ADMIN_TABPOX."transfervoucherdetail` where transfervoucher_id=".$id);
			
			}else{
			$res1=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucher` (`transfervoucher_no`,`date`,`agencyid`,`creattime`,`man`,abstract,fromtype,frombillid) VALUES ('" .$transfervoucher_no."','".date('Y-m-d',time())."','".$_SESSION["currentorgan"]."','".date('Y-m-d H:i:s',time())."', '".$man."' ,'前台销售单; ".$inrrs['memo']."',1,".$updid.")");
 
			$id = $this -> dbObj -> Insert_ID();
			}

 		$sql1="select * from  ".WEB_ADMIN_TABPOX."selldetail  where  agencyid =".$_SESSION['currentorgan']." and sell_id=".$updid;
		$sql2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail  where  agencyid =".$_SESSION['currentorgan']." and sell_id=".$updid;
		$sql3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  where agencyid =".$_SESSION['currentorgan']." and  sell_id=".$updid;
		$sql4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  where agencyid =".$_SESSION['currentorgan']." and sell_id=".$updid;
		$sql5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail  where agencyid =".$_SESSION['currentorgan']." and sell_id=".$updid;
		$sqlstr=$sql1." union ".$sql2." union ".$sql3." union ".$sql4." union ".$sql5;
		//删除 agencyid！！！！！
			 
		//$sqlstr='select * from '.WEB_ADMIN_TABPOX.'selldetail  where  agencyid ='.$_SESSION["currentorgan"].' and sell_id='.$updid;	
		
		//插入转账明细

//======针对付款方式生成凭证=========================================================================================
  				if($inrrs['xianjinvalue']<>0){//现金1
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','".$agencyaccounttitle['xjaccounttitle_id']."','7','".$_SESSION['currentorgan']."','".$inrrs['xianjinvalue']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");//借现金
				 
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','0','".$inrrs['xianjinvalue']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				}
				if($inrrs['yinkavalue']<>0){//刷卡2
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','".$agencyaccounttitle['ykaccounttitle_id']."','7','".$_SESSION['currentorgan']."','".$inrrs['yinkavalue']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//借 银行存款
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','0','".$inrrs['yinkavalue']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				}
				if($inrrs['zengsongvalue']<>0){//'扣赠送账户3
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','217','1','".$customer_id."','".$inrrs['zengsongvalue']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//借赠送
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','0','".$inrrs['zengsongvalue']."','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				}
if($inrrs['dingjinvalue']<>0){//','扣定金4
			//直接挂“应收账款”贷方
			$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','100','1','".$customer_id."','".$inrrs['dingjinvalue']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	 //借定金
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','0','".$inrrs['dingjinvalue']."','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				}
if($inrrs['chuzhikavalue']<>0){//','扣储值卡5??
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','213','1','".$customer_id."','".$inrrs['chuzhikavalue']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	 //借储值卡
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','0','".$inrrs['chuzhikavalue']."','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				}
if($inrrs['xianjinquanvalue']<>0){//','扣现金券'6, ??
			$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','215','7','".$_SESSION['currentorgan']."','".$inrrs['xianjinquanvalue']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//借现金券
		// 生产现金券操作
		$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','216','7','".$_SESSION['currentorgan']."','".$inrrs['xianjinquanvalue']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//借现金券成本
			$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','215','7','".$_SESSION['currentorgan']."','0','".$inrrs['xianjinquanvalue']."','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//借现金券
			
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','4','".$customer_id."','0','".$inrrs['yinkavalue']."','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				}
if($inrrs['yufuvalue']<>0){//'扣预收款'7
				//直接挂“应收账款”贷方
				 
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','100','1','".$customer_id."','".$inrrs['yufuvalue']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//借预收款
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','0','".$inrrs['yinkavalue']."','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				}
if($inrrs['yufuproducevalue']<>0){//'扣预收产品款'8
				//直接挂“应收账款”贷方
				 
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','100','1','".$customer_id."','".$inrrs['yufuproducevalue']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//借预收款
				
				}
if($inrrs['zengsongproducevalue']<>0){//'扣赠送产品账户9
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','217','1','".$customer_id."','".$inrrs['zengsongproducevalue']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//借赠送
				 
				}			
if($inrrs['zengsongcardvalue']<>0){//'扣赠送卡项账户10
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','217','1','".$customer_id."','".$inrrs['zengsongcardvalue']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//借赠送
				}
				
				
			if ($inrrs['realpay']<>0){	
 			$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','0','".$inrrs['realpay']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//贷应收 
			 }

//======针对明细生成凭证=========================================================================================
				$repaymemt=$this -> dbObj -> GetOne("select sum(amount) as  acount from  ".WEB_ADMIN_TABPOX."sellotherdetail  where agencyid =".$_SESSION['currentorgan']." and item_id =3 and item_type=5 and sell_id=".$updid);//还款
				$rp=$repaymemt?$repaymemt:0;
				//团购
				//找团购价格。需要从方案中找出，方案中可能存在指定项目，指定类别，不指定三类。
				
				 
				$inrstuangou=$this -> dbObj -> Execute("select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  where agencyid =".$_SESSION['currentorgan']." and cardtype =9 and item_type=4 and sell_id=".$updid);
				while ($inrrstuangou= $inrstuangou -> FetchRow()) {
				$tuangouvalue=$this -> dbObj -> GetOne("SELECT value FROM `s_marketingcard` WHERE `marketingcard_id`=".$inrrstuangou['cardid']);
				//echo "SELECT value FROM `s_marketingcard` WHERE `marketingcard_id`=".$inrrstuangou['cardid'];
				$tuangou=$tuangou+$inrrstuangou['number']*$tuangouvalue;
				}
				//echo "select sum(price*number) as  acount from  ".WEB_ADMIN_TABPOX."sellconsumedetail  where agencyid =".$_SESSION['currentorgan']." and cardtype =9 and item_type=4 and sell_id=".$updid;
				$paijiaservicesacount=$this -> dbObj -> GetOne("select ssum(amount) as  acount  from  ".WEB_ADMIN_TABPOX."sellservicesdetail  where agencyid =".$_SESSION['currentorgan']." and   item_type=0 and sell_id=".$updid);
				 
				
				//if($inrrs1['item_id']==3 && $inrrs1['item_type']==5){$rp=$repaymemt;}else {$rp=0;}
				 
				if($inrrs['payable1']-$rp+$tuangou<>0){//应收大于零才生产凭证
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".($inrrs['payable1']-$rp+$tuangou)."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");//借应收
				}
				$cmldiscount=$this->cmlpriceObj->discount($sellid[$i]);
				
				$inrs1 = &$this -> dbObj -> Execute($sqlstr);
				
				while ($inrrs1 = &$inrs1 -> FetchRow()) {
				

  				if($inrrs1['item_type']==1){//产品
				//  借：应收账款   贷：产品销售收入
				if($inrrs1['discount']>0){
				if($inrrs1['returnstatus']==0){//正常购买产品
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['amount']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','126','4','".$inrrs1['item_id']."','0','".$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");//商品销售收入-产品销售收入
			//结转成本：借：营业成本    贷：库存商品
			
			//计算产品加权平均金额
			//if($inrrs1['discount']>0){//非赠送产品
			//查找正品仓
			$warehouseid=$this -> dbObj -> GetOne("SELECT warehouse_id FROM `".WEB_ADMIN_TABPOX."warehouse`  WHERE    type=1 and agencyid=".$_SESSION['currentorgan']);
			//echo "SELECT warehouse_id  FROM `".WEB_ADMIN_TABPOX."warehouse`  WHERE    type=1 and agencyid=".$_SESSION['currentorgan'];
			$meanprice=$this -> dbObj -> GetOne("SELECT acount/number FROM  `".WEB_ADMIN_TABPOX."stock`  WHERE warehouse_id=".$warehouseid." and  produce_id=".$inrrs1['item_id']." and agencyid=".$_SESSION['currentorgan']);
			//echo "SELECT acount/number FROM  `".WEB_ADMIN_TABPOX."stock`  WHERE warehouse_id= ".$warehouseid." and  produce_id=".$inrrs1['item_id']." and agencyid=".$_SESSION['currentorgan'];
			$amount=$inrrs1['number']*$meanprice;
              
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','122',7,'".$_SESSION['currentorgan']."','".$amount."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','52','4','".$inrrs1['item_id']."','0','".$amount."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");
				
				
				
			 $res3=$this->prodaybooksObj->main($inrrs1['item_id'],$warehouseid,-$inrrs1['number'],-$amount,1,$updid,$man,"销货发出; ".$inrrs['memo'],$_SESSION["currentorgan"]);//商品流水账	
			}else if($inrrs1['returnstatus']==1){//退换产品
				$oldproduce=$this -> dbObj -> GetRow("select * from ".WEB_ADMIN_TABPOX."selldetail where selldetail_id= ".$inrrs1['returnid']);
				
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','126','4','".$inrrs1['item_id']."','0','".$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");//商品销售收入-产品销售收入
			//结转成本：借：营业成本    贷：库存商品
			
			//计算产品加权平均金额
			//if($inrrs1['discount']>0){//非赠送产品
			//查找正品仓
			$warehouseid=$this -> dbObj -> GetOne("SELECT warehouse_id FROM `".WEB_ADMIN_TABPOX."warehouse`  WHERE    type=1 and agencyid=".$_SESSION['currentorgan']);
			$meanprice=$this -> dbObj -> GetOne("SELECT acount/number FROM  `".WEB_ADMIN_TABPOX."stock`  WHERE warehouse_id=".$warehouseid." and  produce_id=".$inrrs1['item_id']." and agencyid=".$_SESSION['currentorgan']);
			$amount=$inrrs1['number']*$meanprice;  
			$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','122',7,'".$_SESSION['currentorgan']."','".$amount."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//借直营体系成本。
			$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','52','4','".$inrrs1['item_id']."','0','".$amount."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");//贷 库存商品
			
			 $res3=$this->prodaybooksObj->main($inrrs1['item_id'],$warehouseid,-$inrrs1['number'],-$amount,1,$updid,$man,"销货发出; ".$inrrs['memo'],$_SESSION["currentorgan"]);//商品流水账	
				}
				}else{
					
			//赠送的产品		
			$warehouseid=$this -> dbObj -> GetOne("SELECT warehouse_id FROM `".WEB_ADMIN_TABPOX."warehouse`  WHERE    type=0 and agencyid=".$_SESSION['currentorgan']);
			//$meanprice=$this -> dbObj -> GetOne("SELECT acount/number  FROM `".WEB_ADMIN_TABPOX."stock`  WHERE warehouse_id=".$warehouseid." and  produce_id=".$inrrs1['item_id']." and agencyid=".$_SESSION['currentorgan']);
			$meanprice=$this -> dbObj -> GetOne("SELECT stockunitprice  FROM `".WEB_ADMIN_TABPOX."stock`  WHERE warehouse_id=".$warehouseid." and  produce_id=".$inrrs1['item_id']." and agencyid=".$_SESSION['currentorgan']);
			$amount=$inrrs1['number']*$meanprice;
			
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','176',7,'".$_SESSION['currentorgan']."','".$amount."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//配送费用
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','52','4','".$inrrs1['item_id']."','0','".$amount."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//库存商品
			
			
			$res3=$this->prodaybooksObj->main($inrrs1['item_id'],$warehouseid,-$inrrs1['number'],-$amount,1,$updid,$man,"销货发出; ".$inrrs['memo'],$_SESSION["currentorgan"]);//商品流水账		
				
				
				}
				//echo "INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['amount']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')";
				}else if($inrrs1['item_type']==2){//消费卡项
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['amount']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['amount']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','127','3','".$inrrs1['item_id']."','0','".$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','131','5','".$inrrs1['item_id']."','0','".$cmldiscount[$sellid[$i]]*$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				//echo "INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['amount']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')";
				}else if($inrrs1['item_type']==3){//购买卡项
				//储值卡 不算开卡费 故在购买卡项中分出来
				//会籍卡 项目卡 
				//=============================
			    if($inrrs1['returnstatus']==0){//正常购卡=========================================================
				
/*				//卡项商品流水帐。
				//判断卡是否绑定了商品，找出该卡绑定的商品，减去耗品仓改商品的数量。
				$cardforproduct=$this -> dbObj -> GetRow("select * from `".WEB_ADMIN_TABPOX."cardforproduct` where marketingcard_id=".$inrrs1['cardid']);
				 
				if($cardforproduct['produce_id']<>''){
				//找出耗品仓
				 $warehouseid=$this -> dbObj -> GetOne("select warehouse_id from `".WEB_ADMIN_TABPOX."warehouse` where type=3 and agencyid=".$_SESSION["currentorgan"]);
				 //echo "select warehouse_id from `".WEB_ADMIN_TABPOX."warehouse` where type=3 and agencyid=".$_SESSION["currentorgan"];
				//找出库存单价
				$meanprice=$this -> dbObj -> GetOne("SELECT acount/number FROM  `".WEB_ADMIN_TABPOX."stock`  WHERE warehouse_id=".$warehouseid." and  produce_id=".$inrrs1['item_id']." and agencyid=".$_SESSION['currentorgan']);
				$amount=$inrrs1['number']*$meanprice;  
				$rescardforproduct=$this->prodaybooksObj->main($cardforproduct['produce_id'],$warehouseid,-$inrrs1['number'],-$amount,1,$updid,$man,"开卡发出; ".$inrrs['memo'],$_SESSION["currentorgan"]);//商品流水账
				
				//减去库存
				$this->	ChargeObj=new charge(); 
				$this->	ChargeObj->stock($cardforproduct['produce_id'],$inrrs1['number'],$warehouseid,$updid,$_SESSION["currentorgan"]);
				}
				//end of 商品卡流水*/
					if($inrrs1['cardtype']==7){//储值卡

				$discountczk=$this->dbObj->GetOne("SELECT B.price/B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  A.`marketingcard_id`='".$inrrs1['cardid']."'");
				
				$czkvalue=$this->dbObj->GetOne("SELECT B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  A.`marketingcard_id`='".$inrrs1['cardid']."'");
				//echo "SELECT B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  A.`marketingcard_id`='".$inrrs1['cardid']."'";
				
					//查找储值卡折扣。借成本300 贷储值卡1000 （借应收已经在前面一起写了）
					$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','214','3','".$inrrs1['item_id']."','".(1-$discountczk)*$czkvalue*$inrrs1['number']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");		//借储值成本码
					$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','213','3','".$inrrs1['item_id']."','0','".$czkvalue*$inrrs1['number']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");		//贷储值卡
					
					}else if($inrrs1['cardtype']==1 or $inrrs1['cardtype']==2){//项目卡
					if($inrrs1['incometype']==1){
					$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','131','5','0','0','".$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
					}else{
				 	$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','128','3','".$inrrs1['item_id']."','0','".$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	 //开卡收入-项目卡
					}
					}else if ($inrrs1['cardtype']==5){//会籍卡 等非储值卡
					if($inrrs1['incometype']==1){
					$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','131','5','0','0','".$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
					}else{
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','129','3','".$inrrs1['item_id']."','0','".$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//开卡收入-会籍卡
					}
					}else if($inrrs1['cardtype']==8){//自定义卡项
					
					if($inrrs1['incometype']==1){
					$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','131','5','0','0','".$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
					//echo "INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','131','5','0','0','".$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')";
					}else{
					$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','128','3','".$inrrs1['item_id']."','0','".$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	 //开卡收入-项目卡
					}
					}
				}else if($inrrs1['returnstatus']==1){//换卡===================================================================
				
				$detailtablelist=array('sellservicesdetail','selldetail','sellconsumedetail','sellcarddetail','sellconsumedetail','sellotherdetail');
				$inrrsreturn=$this -> dbObj -> GetRow("select * from ".WEB_ADMIN_TABPOX.$detailtablelist[$inrrs1['item_type']]." where selldetail_id=".$inrrs1['returnid']);//查找出所退卡的selldetailid
				$customercardid=$inrrsreturn['customercardid'];
				//echo "select * from ".WEB_ADMIN_TABPOX.$detailtablelist[$inrrs1['item_type']]." where selldetail_id=".$inrrs1['returnid'];
				$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");
				$cardreturn=$this -> dbObj -> GetRow("select * from ".WEB_ADMIN_TABPOX.$cardtable_name[$inrrsreturn['cardtype']]." where ".$cardtable_name[$inrrsreturn['cardtype']]."_id=".$customercardid);//查找出所退卡的卡
				//echo "select * from ".WEB_ADMIN_TABPOX.$cardtable_name[$inrrsreturn['cardtype']]." where ".$cardtable_name[$inrrsreturn['cardtype']]."_id=".$customercardid;
				$cardnew=$this -> dbObj -> GetRow("select * from ".WEB_ADMIN_TABPOX.$cardtable_name[$inrrs1['cardtype']]." where ".$cardtable_name[$inrrs1['cardtype']]."_id=".$inrrs1['customercardid']);//查找出所买新卡
				//echo "select * from ".WEB_ADMIN_TABPOX.$cardtable_name[$inrrs1['cardtype']]." where ".$cardtable_name[$inrrs1['cardtype']]."_id=".$inrrs1['customercardid'];
				//价格 剩余次数 总次数
 //echo  "select * from ".WEB_ADMIN_TABPOX.$cardtable_name[$inrrs1['cardtype']]." where ".$cardtable_name[$inrrs1['cardtype']]."_id=".$customercardid;
				//退卡凭证begin============================
				if($inrrsreturn['cardtype']==5){//退会籍卡凭证begin
				//退卡凭证
				 $this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','129','3','".$inrrsreturn['item_id']."','".$inrrsreturn['price']."','0','销售单：".$sell_no."; ".$inrrsreturn['memo']."','".$_SESSION['currentorgan']."')");//借 开卡收入-会籍卡
				 if($cardreturn['price']*(1-$cardreturn['remaintimes']/$cardreturn['totaltimes'])>0){
				 $this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','131','5','0','0','".$cardreturn['price']*(1-$cardreturn['remaintimes']/$cardreturn['totaltimes'])."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");//贷手工收入
				 }
				}else if ($inrrsreturn['cardtype']==7){//退储值卡
				$discountczk=$this->dbObj->GetOne("SELECT B.price/B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  A.`marketingcard_id`='".$inrrsreturn['cardid']."'");
				$czkvalue=$this->dbObj->GetOne("SELECT A.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  A.storedvaluedcard_id='".$inrrsreturn['customercardid']."'");	
				
				$czkvalue2=(1-$discountczk)*$czkvalue;
				$czkvalue2=(int) $czkvalue2;
				$czkvalue2=sprintf ("%01.2f",$czkvalue2);
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','213','3','".$inrrsreturn['item_id']."','".$czkvalue."','0','销售单：".$sell_no."; ".$inrrsreturn['memo']."','".$_SESSION['currentorgan']."')");//借储值卡
				//echo "SELECT B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  A.`marketingcard_id`='".$inrrsreturn['customercardid']."'";
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','214','3','".$inrrsreturn['item_id']."','0','".$czkvalue2."','销售单：".$sell_no."; ".$inrrsreturn['memo']."','".$_SESSION['currentorgan']."')");//贷储值成本
				
				}//如有其他继续添加 退会籍卡凭证end
				 else if($inrrs1['cardtype']==8){//自定义卡项
					if($inrrs1['cardtype']==1){
					$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','131','5','0','".$inrrs1['amount']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
					}else{
					$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','128','3','".$inrrs1['item_id']."','".$inrrs1['amount']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	 //开卡收入-项目卡
					}
					}
				
				
				//新购卡凭证begin===============================================
				if($inrrs1['cardtype']==7){//储值卡
				$discountczk=$this->dbObj->GetOne("SELECT B.price/B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  A.`marketingcard_id`='".$inrrs1['cardid']."'");	
				//$czkvalue=$this->dbObj->GetOne("SELECT B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  A.`marketingcard_id`='".$inrrs1['cardid']."'");
				$czkvalue=$this->dbObj->GetOne("SELECT B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  A.storedvaluedcard_id='".$inrrs1['customercardid']."'");
				//echo "SELECT B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  A.`marketingcard_id`='".$inrrsreturn['cardid']."'";

				    //
					//查找储值卡折扣。借成本300 贷储值卡1000 （借应收已经在前面一起写了）
				$czkvalue3=(1-$discountczk)*$czkvalue;
				$czkvalue3=(int) $czkvalue3;
				$czkvalue3=sprintf ("%01.2f",$czkvalue3);
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','214','3','".$inrrsreturn['item_id']."','".$czkvalue3."','0','销售单：".$sell_no."; ".$inrrsreturn['memo']."','".$_SESSION['currentorgan']."')");		//借储值成本码
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','213','3','".$inrrsreturn['item_id']."','0','".$czkvalue*$inrrs1['number']."','销售单：".$sell_no."; ".$inrrsreturn['memo']."','".$_SESSION['currentorgan']."')");		//贷储值卡
					
				}else if($inrrsreturn['cardtype']==1 or $inrrsreturn['cardtype']==2){//项目卡
				 $res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','128','3','".$inrrsreturn['item_id']."','0','".$inrrsreturn['amount']."','销售单：".$sell_no."; ".$inrrsreturn['memo']."','".$_SESSION['currentorgan']."')");	 //开卡收入-项目卡	 
				}else if ($inrrs1['cardtype']==5){//会籍卡 等非储值卡
				 
					//退卡凭证
				// $this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','129','3','".$inrrsreturn['item_id']."','".$inrrsreturn['amount']."','0','销售单：".$sell_no."; ".$inrrsreturn['memo']."','".$_SESSION['currentorgan']."')");	//借 开卡收入-会籍卡
				 //echo "INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','129','3','".$inrrsreturn['item_id']."','".$inrrsreturn['amount']."','0','销售单：".$sell_no."; ".$inrrsreturn['memo']."','".$_SESSION['currentorgan']."')";
				 
				//$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','131','5','".$inrrs1['item_id']."','0','".$cardreturn['price']*(1-$cardreturn['remaintimes']/$cardreturn['totaltimes'])."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//贷手工收入
				//$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','0','".$cardreturn['price']*$cardreturn['remaintimes']/$cardreturn['totaltimes']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//贷应收 
				
				//新卡凭证
				//$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$cardreturn['price']*$cardreturn['remaintimes']/$cardreturn['totaltimes']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//借应收 
				$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','129','3','".$inrrs1['item_id']."','0','".$cardnew['price']."','销售单：".$sell_no."; ".$inrrsreturn['memo']."','".$_SESSION['currentorgan']."')");	//贷 开卡收入-会籍卡
				//echo "INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','129','3','".$inrrs1['item_id']."','0','".$cardnew['price']."','销售单：".$sell_no."; ".$inrrsreturn['memo']."','".$_SESSION['currentorgan']."')";
				
					}else if($inrrs1['cardtype']==8){//自定义卡项
					if($inrrs1['cardtype']==1){
					$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','131','5','0','".$inrrs1['amount']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
					}else{
					$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','128','3','".$inrrs1['item_id']."','".$inrrs1['amount']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	 //开卡收入-项目卡
					}
					}
				} 
				//===============end新购卡凭证
				
				
				/*if($inrrs1['cardtype']==7){//储值卡

				$discountczk=$this->dbObj->GetOne("SELECT B.price/B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  A.`marketingcard_id`='".$inrrs1['cardid']."'");
				
				$czkvalue=$this->dbObj->GetOne("SELECT B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  A.`marketingcard_id`='".$inrrs1['cardid']."'");
				//echo "SELECT B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  A.`marketingcard_id`='".$inrrs1['cardid']."'";
				
					//查找储值卡折扣。借成本300 贷储值卡1000 （借应收已经在前面一起写了）
					$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','214','3','".$inrrs1['item_id']."','".(1-$discountczk)*$czkvalue*$inrrs1['number']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");		//借储值成本码
					$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','213','3','".$inrrs1['item_id']."','0','".$czkvalue*$inrrs1['number']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");		//贷储值卡
					
					}else if($inrrs1['cardtype']==1 or $inrrs1['cardtype']==2){//项目卡
				 	$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','128','3','".$inrrs1['item_id']."','0','".$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	 //开卡收入-项目卡	 
					}else if ($inrrs1['cardtype']==5){//会籍卡 等非储值卡
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','129','3','".$inrrs1['item_id']."','0','".$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//开卡收入-会籍卡
					}
				}else if($inrrs1['item_type']==4){//消费券项
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['amount']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				if($inrrs1['amount']>0){
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','131','5','".$inrrs1['item_id']."','0','".$cmldiscount[$sellid[$i]]*$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				//echo "INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['amount']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')";
				}
				
				}*/
				//=======================================
				}else if($inrrs1['item_type']==4){//消费券项
				if($inrrs1['cardtype']==9){
					//如果是团购类挂应收
					//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['number']*$inrrs1['price']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");//借应收
				$tuangouvalue=$this -> dbObj -> GetOne("SELECT value FROM `s_marketingcard` WHERE `marketingcard_id`=".$inrrs1['cardid']);
				//$tuangou=$this -> dbObj -> GetOne("select number from  ".WEB_ADMIN_TABPOX."sellconsumedetail  where agencyid =".$_SESSION['currentorgan']." and cardtype =9 and item_type=4 and sell_id=".$updid);
				//$tuangou=$tuangou*$value;
					$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','131','5','".$inrrs1['item_id']."','0','".$inrrs1['number']*$tuangouvalue."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				}
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['amount']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				//echo "INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','131','5','".$inrrs1['item_id']."','0','".$cmldiscount[$sellid[$i]]*$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')";
				if($inrrs1['amount']>0){
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','131','5','".$inrrs1['item_id']."','0','".$cmldiscount[$sellid[$i]]*$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				//echo "INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['amount']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')";
				}
				}else if($inrrs1['item_type']==5){//其他项目
				//款项  ：'预收款0',"储值卡充值1","定金2","还款3", "赠送手工4" 如何生产凭证 
				if($inrrs1['item_id']==0){//预收款
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','100','1','".$customer_id."','0','".$inrrs1['amount']."','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");
				
				//直接挂“应收账款”贷方
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','100','1','".$customer_id."','0','".$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['amount']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				}else if($inrrs1['item_id']==6){//预付产品款
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','100','1','".$customer_id."','0','".$inrrs1['amount']."','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");//预付产品款
				
				}else if($inrrs1['item_id']==1){//储值卡充值????
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','62','1','".$customer_id."','".$inrrs1['amount']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");
				
				$discountczk=$this->dbObj->GetOne("SELECT B.price/B.value FROM `s_storedvaluedcard` A INNER JOIN s_marketingcard B on A.`marketingcard_id`=B.`marketingcard_id` WHERE  `storedvaluedcard_id`='".$inrrs1['cardid']."'");
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','214','1','".$customer_id."','".(1-$discountczk)*$inrrs1['amount']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//借储值卡成本300
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','213','1','".$customer_id."','0','".$discountczk*$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//贷储值卡1000

				}else if($inrrs1['item_id']==2){//定金
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','100','1','".$customer_id."','0','".$inrrs1['amount']."','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");//贷开卡定金
				
				//直接挂“应收账款”贷方
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','100','1','".$customer_id."','0','".$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['amount']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				
				}else if($inrrs1['item_id']==3){//还款
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','0','".$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				}else if($inrrs1['item_id']==4){//赠送手工
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','217','7','".$_SESSION['currentorgan']."','".$inrrs1['price']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//借赠送成本
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','218','1','".$customer_id."','0','".$inrrs1['price']."','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//贷赠送金
				}else if($inrrs1['item_id']==7){//赠送产品款
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','217','7','".$_SESSION['currentorgan']."','".$inrrs1['price']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//借赠送成本
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','218','1','".$customer_id."','0','".$inrrs1['price']."','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//贷赠送金
				}else if($inrrs1['item_id']==8){//赠送购卡款
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','217','7','".$_SESSION['currentorgan']."','".$inrrs1['price']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//借赠送成本
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','218','1','".$customer_id."','0','".$inrrs1['price']."','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//贷赠送金
				}
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['amount']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','62','6','".$inrrs1['item_id']."','0','".$inrrs1['amount']."','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				//echo "INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['amount']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')";
				}else if($inrrs1['item_type']==0){//单项服务	
				
				if($inrrs1['discount']=10){//牌价服务 相当于打折
				//牌价服务 现金券付款 储值卡付款  赠送牌价金额付款 当作打折处理
				//$cmldiscount=$this->cmlpriceObj->discount($sellid[$i]);
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['amount']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','131','5','".$inrrs1['item_id']."','0','".(1-$cmldiscount[$sellid[$i]])*$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','131','5','".$inrrs1['item_id']."','0','".$cmldiscount[$sellid[$i]]*$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//手工收入.
				}else{
				//$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['amount']."','0','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','131','5','".$inrrs1['item_id']."','0','".$inrrs1['amount']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");
				}
				//echo "INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','48','1','".$customer_id."','".$inrrs1['amount']."','0','".$inrrs1['memo']."','".$_SESSION['currentorgan']."')";
				
				
				
				} 
				} //end if
				 
				if($cmldiscount[$sellid[$i]]<>'' or $cmldiscount[$sellid[$i]]==0){
				 
				if($inrrs['chuzhikavalue']>0){
				$chuzhikadicount=$this->cmlpriceObj->chuzhikadicount($sellid[$i]);
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','214',7,'".$_SESSION['currentorgan']."','0','".(1-$chuzhikadicount[$sellid[$i]])*$inrrs['chuzhikavalue']."','销售单：".$sell_no.";  ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//牌价服务中 储值卡成本
				}
				
				 //print_r($chuzhikadicount);
				if($inrrs['xianjinquanvalue']>0){
				$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','216',7,'".$_SESSION['currentorgan']."','0','".$inrrs['xianjinquanvalue']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//牌价服务中现金券成本
				}
				  
				if($inrrs['zengsongvalue']>0){//
					$res2=$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."transfervoucherdetail` ( `transfervoucher_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','218',7,'".$_SESSION['currentorgan']."','0','".$inrrs['zengsongvalue']."','销售单：".$sell_no."; ".$inrrs1['memo']."','".$_SESSION['currentorgan']."')");	//牌价服务中赠送金额成本
				 
				}
				}
			
				}//end while 
	 
//======针对明细生成凭证 end=========================================================================================				
	 
	 
	 $result= $result+$this->SellObj->audit($sellid[$i],$agencyid,$status);
	 
	 
	
	 }
	 //插入对账记录
 
 	$curaccount=$this ->curObj->addrecordsell(1,$updid,1,$_SESSION['currentorgan']);
	 
	 if($result==count($sellid)&&$curaccount){
	 $this -> dbObj -> Execute("COMMIT");
	 exit("<script>alert('提交成功'); window.history.go(-1);</script>");	
	 }else if($result<count($sellid)&&$result>1&&$curaccount){
	 $this -> dbObj -> Execute("COMMIT");
	 exit("<script>alert('部分提交成功'); window.history.go(-1);</script>");	
	 }else {
	 $this -> dbObj -> Execute("ROLLBACK");
	 exit("<script>alert('操作出现问题,数据已回滚'); window.history.go(-1);</script>");		 
	}
	}	
function caiwuaudit(){
	
	 $this->SellObj=new sell();
	 $sellid=$_GET['sellid'];
	 $sellid=explode(",",$sellid);
	 $agencyid =$_SESSION["currentorgan"];
	 $status=5;
	  $result=0;
	 for($i=0;$i<count($sellid);$i++){
	 $result=$result+$this->SellObj->caiwuaudit($sellid[$i],$agencyid,$status);
	 }
	 if($result==count($sellid)){
	 exit("<script>alert('审核成功'); window.history.go(-1);</script>");	
	 }else if($result<count($sellid)&&$result>1){
	 exit("<script>alert('部分审核成功'); window.history.go(-1);</script>");	
	 }else {
	 exit("<script>alert('操作出现问题,数据已回滚'); window.history.go(-1);</script>");		 
	}
	}	
function returntoedit(){
	
	 $this->SellObj=new sell();
	 $sellid=$_GET['sellid'];
	 $sellid=explode(",",$sellid);
	 $agencyid =$_SESSION["currentorgan"];
	 $status=6;
	 $result=0;
	 for($i=0;$i<count($sellid);$i++){
	 $result=$result+$this->SellObj->returntoedit($sellid[$i],$agencyid,$status);
	 }
	 
	 if($result==count($sellid)){
	 exit("<script>alert('撤回成功'); window.history.go(-1);</script>");	
	 }else if($result<count($sellid)&&$result>1){
	 exit("<script>alert('部分撤回成功'); window.history.go(-1);</script>");	
	 }else {
	 exit("<script>alert('操作出现问题'); window.history.go(-1);</script>");		 
	}
	}		
	function disp(){
		//定义模板
		$this->perObj=new performancehistory();
		$t = new Template('../template/analysis');
		$t -> set_file('f','sell.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$bgdate=$_GET["bgdate"]." 00:00:00";
		$enddate=$_GET["enddate"]." 23:59:59";		
		$timecondition=' creattime  between "'.$bgdate.'" and "'.$enddate.'"';
		$condition='';
		if($_SESSION["hiddenred"]==1){
			$hiddenredstr=" and status<>2 and status<>3";
			$hiddenredstr1=" and s.status<>2 and s.status<>3";
			$t -> set_var('hiddenredchecked','checked');
		}else{
			$hiddenredstr="";
			$t -> set_var('hiddenredchecked','');}
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "'.$keywords.'"';}else{$condition=$category.' like "'.$keywords.'"';}
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'sell  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition.$hiddenredstr;

			}else if($ftable<>''){
			$sql="select f.status as fstatus ,f.customer_id,s.* from ".WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.customer_id =f.customer_id  where f.".$category." like '%".$keywords."%' and f.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			
			 
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'sell  where  agencyid ='.$_SESSION["currentorgan"].$hiddenredstr;
			 
			}
			 
			if($_GET["bgdate"]<>'' && $_GET["enddate"]<>''){$sql=$sql.' and '.$timecondition;}
			 
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  sell_no DESC,sell_id DESC LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			if ($_GET["bgdate"]<>''&& $_GET["enddate"]<>''){$datestr="bgdate=".$_GET["bgdate"]."&enddate=".$_GET["enddate"]."&";}else{$datestr='';}
			$t -> set_var('pagelist',$this -> page("?".$datestr."category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));			
	    	$t -> set_var('recordcount',$count);	
			
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$cardlevel_name=$this -> dbObj -> GetOne("select B.cardlevel_name from  ".WEB_ADMIN_TABPOX."membercard  A INNER JOIN  ".WEB_ADMIN_TABPOX."memcardlevel B ON A.cardlevel_id=B.cardlevel_id where  A.customer_id=".$inrrs['customer_id']);

				$customercatalog_name=$this -> dbObj -> GetOne("select A.customercatalog_name from  ".WEB_ADMIN_TABPOX."customercatalog  A INNER JOIN  ".WEB_ADMIN_TABPOX."customer B ON A.customercatalog_id=B.customercatalog_id where  B.customer_id=".$inrrs['customer_id']);
$cardlevel_name=$cardlevel_name?$cardlevel_name:$customercatalog_name;	
				$t -> set_var('cardlevel_name',$cardlevel_name);
				$customername=$this -> dbObj -> getone('select customer_name from '.WEB_ADMIN_TABPOX.'customer  where customer_id ='.$inrrs["customer_id"]);
				$customername=$customername<>''?$customername:'散客';
				$t -> set_var('customer_name',$customername);
			$status_name=array("<font color=blue>未完成</font>","已完成","<font color=red>红字冲销</font>","<font color=red>被红字冲销</font>","<font color=green>已提交</font>","<font color=#AAAAAA>已审核</font>","<font color=red>审核不通过</font>");
				$t -> set_var('status_name',$status_name[$inrrs['status']]);


				$t -> set_var('otherpay',$inrrs['zengsongvalue']+$inrrs['dingjinvalue']+$inrrs['chuzhikavalue']+$inrrs['xianjinquanvalue']+$inrrs['yufuvalue']);
				$t -> set_var('shifuvalue',$inrrs['xianjinvalue']+$inrrs['yinkavalue']);
				$t -> set_var('own',$inrrs['payable1']-$inrrs['realpay']);

				$t -> set_var('view','<a href="#" onclick=viewbill('.$inrrs['sell_id'].');>查看</a>'); 
				$t -> set_var('print','<a href="?action=print&updid='.$inrrs['sell_id'].'" target="_blank">打印</a>');
				$t -> set_var('recoil','<a href="#"  onclick=recoil('.$inrrs['sell_id'].')>反冲</a>');
				$t -> set_var('brand_name',$this -> dbObj -> getone('select brand_name from '.WEB_ADMIN_TABPOX.'brand  where brand_id ='.$inrrs["brandid"]));
				$t -> set_var('employee_name',$this -> dbObj -> getone('select employee_name  from '.WEB_ADMIN_TABPOX.'employee  where employee_id ='.$inrrs["employee_id"]));
				$t -> set_var('order_no',$this -> dbObj -> getone('select order_no from '.WEB_ADMIN_TABPOX.'order  where order_id ='.$inrrs["order_id"]));
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['purchase_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['sell_id']));		
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$t -> set_var('bgdate',date('Y-m-d'));
		$t -> set_var('enddate',date('Y-m-d'));
 
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canexport',''):$t -> set_var('canexport','none');	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	
 
function print1(){
		//定义模板
		
 		$sellid=$_GET['updid'];
		$t = new Template('../template/analysis');
		$t -> set_file('f','sell_detailprint.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
   			//设置分类
		$sellid=$_GET['sellid']?$_GET['sellid']:$sellid;
		$selldata=$this -> dbObj ->GetRow("select * from  ".WEB_ADMIN_TABPOX."sell  where  sell_id=".$sellid);
		
		$t->set_var($selldata);
		$employee_name=$this -> dbObj ->GetOne("select employee_name from  ".WEB_ADMIN_TABPOX."employee  where  employee_id=".$selldata['employee_id']);
		 
		$customer_name=$this -> dbObj ->GetOne("select customer_name from  ".WEB_ADMIN_TABPOX."customer  where  customer_id=".$selldata['customer_id']);
		$t->set_var('employee_name',$employee_name);
		$t->set_var('customer_name',$customer_name);
		$t -> set_var('ml');
  
 		$sql1="select * from  ".WEB_ADMIN_TABPOX."selldetail  where  sell_id=".$sellid;
		$sql2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail   where  sell_id=".$sellid;
		$sql3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  where  sell_id=".$sellid;
		$sql4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  where  sell_id=".$sellid;
		$sql5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail  where  sell_id=".$sellid;
		$sql=$sql1." union ".$sql2." union ".$sql3." union ".$sql4." union ".$sql5;
		 $table_name=array('services',"produce","services","marketingcard","services");	
		  $itemtype_name=array('单项服务',"购买产品","消费卡项","购买卡项","消费券项","其他项目");	
		  
		$inrs = $this -> dbObj -> Execute($sql);
		$tempacount=0;
		 
 		while ($inrrs = $inrs -> FetchRow()) {
			$t -> set_var($inrrs);
			  
			 $tempacount=$tempacount+$inrrs['amount'];
			$itemdata=$this -> dbObj -> GetRow("select * from ".WEB_ADMIN_TABPOX.$table_name[$inrrs['item_type']]." where ".$table_name[$inrrs['item_type']]."_id =".$inrrs['item_id']);
			 
			if($inrrs['item_type']==2){//卡项
			$t -> set_var('itemtype_name',$this -> dbObj -> getone('select marketingcard_name from '.WEB_ADMIN_TABPOX.'marketingcard  where marketingcard_id ='.$inrrs["cardid"]));
			 //echo 'select marketingcard_name from '.WEB_ADMIN_TABPOX.'marketingcard  where marketingcard_id ='.$inrrs["cardid"];
				//$this -> dbObj -> GetRow("select * from ".WEB_ADMIN_TABPOX."marketingcard WHERE  marketingcard_id=".$inrrs['cardid']);
				}else if($inrrs['item_type']==1){//产品
				$t -> set_var('itemtype_name',$this -> dbObj -> getone('select A.category_name  from '.WEB_ADMIN_TABPOX.'procatalog  A INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON A.category_id=B.categoryid   where B.produce_id ='.$inrrs["item_id"]));
				
				//echo 'select A.category_name  from '.WEB_ADMIN_TABPOX.'procatalog  A INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON A.category_id=B.categoryid   where B.produce_id ='.$inrrs["item_id"];
				}else if($inrrs['item_type']==3){//消费
				$t -> set_var('itemtype_name',$this -> dbObj -> getone('select A.marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrs["item_id"]));
				//echo 'select A.marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrs["item_id"];
				}else if($inrrs['item_type']==4){//券类消费
				$t -> set_var('itemtype_name',$this -> dbObj -> getone('select A.marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrs["cardid"]));
				
				
				//echo 'select A.marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrs["cardid"];
				//echo 'select A.marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrs["item_id"];
				
				}else if($inrrs['item_type']==5){//其他
				
	$itemnamelist=array('预收款',"储值卡充值","定金","还款","赠送手工","其他","预付产品款","赠送产品款","赠送购卡款");		
	$cardtypelist=array('款项',"其他");	
	$itemname=$itemnamelist[$inrrs['item_id']];
	$cardtype=$cardtypelist[$inrrs['cardtype']];
				$t -> set_var('itemtype_name',$cardtype);
				//echo 'select A.servicecategory_name from '.WEB_ADMIN_TABPOX.'servicecategory  A INNER JOIN '.WEB_ADMIN_TABPOX.'services B ON A.category_id =B.categoryid    where B.services_id ='.$inrrs["cardid"];
				}else if($inrrs['item_type']==0){//服务 
				$t -> set_var('itemtype_name',$this -> dbObj -> getone('select A.category_name  from '.WEB_ADMIN_TABPOX.'servicecategory  A INNER JOIN '.WEB_ADMIN_TABPOX.'services B ON A.category_id =B.categoryid    where B.services_id ='.$inrrs["item_id"]));
				//echo 'select A.servicecategory_name from '.WEB_ADMIN_TABPOX.'servicecategory  A INNER JOIN '.WEB_ADMIN_TABPOX.'services B ON A.category_id =B.categoryid    where B.services_id ='.$inrrs["cardid"];
				}else{
					$t -> set_var('itemtype_name','test1');
			 }
			$t -> set_var('memo','');
			
			$t -> set_var('type_name',$itemtype_name[$inrrs['item_type']]);
			$t -> set_var('itemname',$itemdata[$table_name[$inrrs['item_type']].'_name']);
			$t -> set_var('itemno',$itemdata[$table_name[$inrrs['item_type']].'_no']);
			$t -> set_var('number',$inrrs['number']);
			if($inrrs['item_type']==5){
				$t -> set_var('itemname',$itemname);
			$t -> set_var('itemno',$inrrs['item_type'].$inrrs['item_id']);
			$t -> set_var('number',$inrrs['number']);
				}
			$t -> parse('ml','mainlist',true);
		}
 
 

			if($selldata['membercard_no']<>""){
			$cardlevel_name = $this -> dbObj -> GetOne("select cardlevel_name from ".WEB_ADMIN_TABPOX."membercard A INNER JOIN ".WEB_ADMIN_TABPOX."memcardlevel B ON  A.cardlevel_id=B.cardlevel_id where A.membercard_no='".$selldata['membercard_no']."'");	
			}else{
				if ($selldata['customer_id']<>""){
					$cardlevel_name="普通顾客";
					}else{
					$cardlevel_name="散客";
					}
			}
			$t -> set_var('cardlevel_name',$cardlevel_name);	
			
		//支付方式
		$paytypeenlist=array('xianjinvalue','yinkavalue','zengsongvalue','dingjinvalue','chuzhikavalue','xianjinquanvalue','yufuvalue','yufuproducevalue','zengsongproducevalue','zengsongcardvalue');
		$paytypelist=array('现金','刷卡','扣赠送账户','扣定金','扣储值卡','扣现金券','扣预收款','扣预收产品款','扣赠送产品款','扣赠送购卡款');
		$paytype='';
		  
		for ($i=0;$i<count($paytypeenlist);$i++) {
 
		if($selldata[$paytypeenlist[$i]]>0){
			
		$paytype=$paytype==''?$paytypelist[$i]."：".$selldata[$paytypeenlist[$i]]:$paytype."；".$paytypelist[$i]."：".$selldata[$paytypeenlist[$i]];
		}
		}
		$t -> set_var('paytype',$paytype);			
 
		$status_name=array("<font color=red>未完成</font>","已完成","<font color=red>红字冲销</font>","<font color=red>被红字冲销</font>");
		$t -> set_var('status_name',$status_name[$selldata['status']]);
 
  		$t -> set_var('totalacount',$tempacount);
		$t -> set_var('membercard_no','');
 		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');	
	
	}
	
	function goDispAppend(){
		 
		$t = new Template('../template/analysis');
		$t -> set_file('f','sell_detail.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
   			//设置分类
		$sellid=$_GET['updid']?$_GET['updid']:$sellid;
		$t -> set_var('action','upd');
		$t -> set_var('updid',$sellid);
		$selldata=$this -> dbObj ->GetRow("select * from  ".WEB_ADMIN_TABPOX."sell  where  sell_id=".$sellid);
		if($selldata['status']>1 and $selldata['status']<6){
			$t->set_var('comsavedisabled','disabled');	
		}else{
			$t->set_var('comsavedisabled','');
		}
		
		$t->set_var($selldata);
		$employee_name=$this -> dbObj ->GetOne("select employee_name from  ".WEB_ADMIN_TABPOX."employee  where  employee_id=".$selldata['employee_id']);
		 
		$customer=$this -> dbObj ->GetRow("select * from  ".WEB_ADMIN_TABPOX."customer  where  customer_id=".$selldata['customer_id']);
		
		$t->set_var('employee_name',$employee_name);
		$t->set_var('customer_name',$customer['customer_name']);
		$t->set_var('customer_no',$customer['customer_no']);
		$t -> set_var('error','');
		$t -> set_var('ml');
  
 		$sql1="select * from  ".WEB_ADMIN_TABPOX."selldetail  where  sell_id=".$sellid;
		$sql2="select * from  ".WEB_ADMIN_TABPOX."sellcarddetail   where  sell_id=".$sellid;
		$sql3="select * from  ".WEB_ADMIN_TABPOX."sellconsumedetail  where  sell_id=".$sellid;
		$sql4="select * from  ".WEB_ADMIN_TABPOX."sellservicesdetail  where  sell_id=".$sellid;
		$sql5="select * from  ".WEB_ADMIN_TABPOX."sellotherdetail  where  sell_id=".$sellid;
		$sql=$sql1." union ".$sql2." union ".$sql3." union ".$sql4." union ".$sql5;
		$table_name=array('services',"produce","services","marketingcard","services");	
		$itemtype_name=array('单项服务',"购买产品","消费卡项","购买卡项","消费券项","其他项目");	
		  
		$inrs = $this -> dbObj -> Execute($sql);
		$count=$inrs->RecordCount();

		$tempacount=0;
			$tr=1;

 		while ($inrrs = $inrs -> FetchRow()) {
			$t -> set_var($inrrs);
			$t -> set_var('trid','tr'.$tr);
			$t -> set_var('imgid','img'.$tr);	
			$tr=$tr+1;  
			$tempacount=$tempacount+$inrrs['amount'];
			$itemdata=$this -> dbObj -> GetRow("select * from ".WEB_ADMIN_TABPOX.$table_name[$inrrs['item_type']]." where ".$table_name[$inrrs['item_type']]."_id =".$inrrs['item_id']);
			 
			if($inrrs['item_type']==2){//卡项
			$t -> set_var('itemtype_name',$this -> dbObj -> getone('select marketingcard_name from '.WEB_ADMIN_TABPOX.'marketingcard  where marketingcard_id ='.$inrrs["cardid"]));
			 //echo 'select marketingcard_name from '.WEB_ADMIN_TABPOX.'marketingcard  where marketingcard_id ='.$inrrs["cardid"];
				//$this -> dbObj -> GetRow("select * from ".WEB_ADMIN_TABPOX."marketingcard WHERE  marketingcard_id=".$inrrs['cardid']);
				}else if($inrrs['item_type']==1){//产品
				$t -> set_var('itemtype_name',$this -> dbObj -> getone('select A.category_name  from '.WEB_ADMIN_TABPOX.'procatalog  A INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON A.category_id=B.categoryid   where B.produce_id ='.$inrrs["item_id"]));
				
				//echo 'select A.category_name  from '.WEB_ADMIN_TABPOX.'procatalog  A INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON A.category_id=B.categoryid   where B.produce_id ='.$inrrs["item_id"];
				}else if($inrrs['item_type']==3){//消费
				$t -> set_var('itemtype_name',$this -> dbObj -> getone('select A.marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrs["item_id"]));
				//echo 'select A.marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrs["item_id"];
				}else if($inrrs['item_type']==0){//服务 
				$t -> set_var('itemtype_name',$this -> dbObj -> getone('select A.category_name  from '.WEB_ADMIN_TABPOX.'servicecategory  A INNER JOIN '.WEB_ADMIN_TABPOX.'services B ON A.category_id =B.categoryid    where B.services_id ='.$inrrs["item_id"]));
				//echo 'select A.servicecategory_name from '.WEB_ADMIN_TABPOX.'servicecategory  A INNER JOIN '.WEB_ADMIN_TABPOX.'services B ON A.category_id =B.categoryid    where B.services_id ='.$inrrs["cardid"];
				}else if($inrrs['item_type']==5){//其他
				
	$itemnamelist=array('预收款',"储值卡充值","定金","还款","赠送手工","其他","预付产品款","赠送产品款","赠送购卡款");		
	$cardtypelist=array('款项',"其他");	
	$itemname=$itemnamelist[$inrrs['item_id']];
	$cardtype=$cardtypelist[$inrrs['cardtype']];
				$t -> set_var('itemtype_name',$cardtype);
				//echo 'select A.servicecategory_name from '.WEB_ADMIN_TABPOX.'servicecategory  A INNER JOIN '.WEB_ADMIN_TABPOX.'services B ON A.category_id =B.categoryid    where B.services_id ='.$inrrs["cardid"];
				}else{
				$t -> set_var('itemtype_name',$this -> dbObj -> getone('select marketingcard_name from '.WEB_ADMIN_TABPOX.'marketingcard where marketingcard_id ='.$inrrs["cardid"]));
				 
			 }
			$t -> set_var('memo','');
			$t -> set_var('unit_name','');
			$t -> set_var('beauty',$this -> dbObj -> getone('select employee_name from '.WEB_ADMIN_TABPOX.'employee where employee_id ='.$inrrs["beauty_id"]));
			$t -> set_var('consultant',$this -> dbObj -> getone('select employee_name from '.WEB_ADMIN_TABPOX.'employee where employee_id ='.$inrrs["employee_id"]));
			$t -> set_var('type_name',$itemtype_name[$inrrs['item_type']]);
			
			
			$t -> set_var('itemname',$itemdata[$table_name[$inrrs['item_type']].'_name']);
			$t -> set_var('itemno',$itemdata[$table_name[$inrrs['item_type']].'_no']);
			 
			$t -> set_var('number',$inrrs['number']);
			if($inrrs['item_type']==5){
				$t -> set_var('itemname',$itemname);
			$t -> set_var('itemno',$inrrs['item_type'].$inrrs['item_id']);
			$t -> set_var('number',$inrrs['number']);
				}
			if($inrrs['item_type']==1){
				$t -> set_var('itemno',$itemdata['code']);
			}
			$t -> set_var('item_type',$inrrs['item_type']);
			
			
			
		//修改单据

		if($_GET['selldetail_id']!=''&&$_GET['item_type']!=''&&$inrrs['item_type']==$_GET['item_type']&&$inrrs['selldetail_id']==$_GET['selldetail_id']){
			if($inrrs['item_type']==2){//卡项
			$t -> set_var('ditemtype_name',$this -> dbObj -> getone('select marketingcard_name from '.WEB_ADMIN_TABPOX.'marketingcard  where marketingcard_id ='.$inrrs["cardid"]));
			 //echo 'select marketingcard_name from '.WEB_ADMIN_TABPOX.'marketingcard  where marketingcard_id ='.$inrrs["cardid"];
				//$this -> dbObj -> GetRow("select * from ".WEB_ADMIN_TABPOX."marketingcard WHERE  marketingcard_id=".$inrrs['cardid']);
				}else if($inrrs['item_type']==1){//产品
				$t -> set_var('ditemtype_name',$this -> dbObj -> getone('select A.category_name  from '.WEB_ADMIN_TABPOX.'procatalog  A INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON A.category_id=B.categoryid   where B.produce_id ='.$inrrs["item_id"]));
				   
				
				//echo 'select A.category_name  from '.WEB_ADMIN_TABPOX.'procatalog  A INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON A.category_id=B.categoryid   where B.produce_id ='.$inrrs["item_id"];
				}else if($inrrs['item_type']==3){//消费
				$t -> set_var('ditemtype_name',$this -> dbObj -> getone('select A.marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrs["item_id"]));
				//echo 'select A.marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype  A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id   where B.marketingcard_id ='.$inrrs["item_id"];
				}else if($inrrs['item_type']==0){//服务 
				$t -> set_var('ditemtype_name',$this -> dbObj -> getone('select A.category_name  from '.WEB_ADMIN_TABPOX.'servicecategory  A INNER JOIN '.WEB_ADMIN_TABPOX.'services B ON A.category_id =B.categoryid    where B.services_id ='.$inrrs["item_id"]));
				//echo 'select A.servicecategory_name from '.WEB_ADMIN_TABPOX.'servicecategory  A INNER JOIN '.WEB_ADMIN_TABPOX.'services B ON A.category_id =B.categoryid    where B.services_id ='.$inrrs["cardid"];
				
				}else if($inrrs['item_type']==5){//其他
				
	$itemnamelist=array('预收款',"储值卡充值","定金","还款","赠送手工","其他","预付产品款","赠送产品款","赠送购卡款");		
	$cardtypelist=array('款项',"其他");	
	$itemname=$itemnamelist[$inrrs['item_id']];
	$cardtype=$cardtypelist[$inrrs['cardtype']];
				$t -> set_var('ditemtype_name',$cardtype);
				//echo 'select A.servicecategory_name from '.WEB_ADMIN_TABPOX.'servicecategory  A INNER JOIN '.WEB_ADMIN_TABPOX.'services B ON A.category_id =B.categoryid    where B.services_id ='.$inrrs["cardid"];
				}else{
				$t -> set_var('ditemtype_name',$this -> dbObj -> getone('select marketingcard_name from '.WEB_ADMIN_TABPOX.'marketingcard where marketingcard_id ='.$inrrs["cardid"]));
				 
			 }
			
			$t -> set_var('dcmlaccount',$inrrs['cmlaccount']);
			$t -> set_var('dtype_name',$itemtype_name[$inrrs['item_type']]);
			
			
			$t -> set_var('ditemname',$itemdata[$table_name[$inrrs['item_type']].'_name']);
			$t -> set_var('ditemno',$itemdata[$table_name[$inrrs['item_type']].'_no']);
			
			
			
			$detailtablename=array('sellservicesdetail','selldetail',"sellconsumedetail","sellcarddetail","sellconsumedetail","sellotherdetail");	
			$t -> set_var('selldetailtable',$detailtablename[$inrrs['item_type']]);
			
			$t -> set_var('dnumber',$inrrs['number']);
			if($inrrs['item_type']==5){
				$t -> set_var('ditemname',$itemname);
			$t -> set_var('ditemno',$inrrs['item_type'].$inrrs['item_id']);
			$t -> set_var('dnumber',$inrrs['number']);
			
				}
			if($inrrs['item_type']==1){
				$t -> set_var('ditemno',$itemdata['code']);
			}
			$t -> set_var('ditem_type',$inrrs['item_type']);
		
			$t -> set_var('damount',$inrrs['amount']);
  			$t -> set_var('dprice',$inrrs['price']);
			$t -> set_var('ditemmemo',$inrrs['itemmemo']);
			$t -> set_var('dselldetail_id',$inrrs['selldetail_id']);
			$t -> set_var('daction','upd');
		 
		 $t -> set_var('dbeauty_id',$inrrs['beauty_id']);
		  $t -> set_var('dconsultant_id',$inrrs['employee_id']);
		 $t -> set_var('dbeauty_name',$this -> dbObj -> getone('select employee_name from '.WEB_ADMIN_TABPOX.'employee where employee_id ='.$inrrs["beauty_id"]));
		 $t -> set_var('dconsultant_name',$this -> dbObj -> getone('select employee_name from '.WEB_ADMIN_TABPOX.'employee where employee_id ='.$inrrs["employee_id"]));
		 $t -> set_var('detaildisplay','');
		 }

			
			$t -> parse('ml','mainlist',true);
		}
 
 		 if($_GET['selldetail_id']==''&&$_GET['item_type']==''){
		 $t -> set_var('detaildisplay','none'); 
		 $t -> set_var('daction','');
		 $t -> set_var('dtype_name','');
		 $t -> set_var('ditemtype_name','');
		 $t -> set_var('ditemname','');
		 $t -> set_var('ditemno','');
		 $t -> set_var('dnumber','');
		 $t -> set_var('dunit_name','');
		 $t -> set_var('dprice','');
		 $t -> set_var('damount','');
		 $t -> set_var('ditemmemo','');
		 $t -> set_var('dbeauty_id','');
		 $t -> set_var('dbeauty_name','');
		 $t -> set_var('dconsultant_name','');
		 $t -> set_var('dconsultant_id','');
		 }
			if($selldata['membercard_no']<>""){
			$cardlevel_name = $this -> dbObj -> GetOne("select cardlevel_name from ".WEB_ADMIN_TABPOX."membercard A INNER JOIN ".WEB_ADMIN_TABPOX."memcardlevel B ON  A.cardlevel_id=B.cardlevel_id where A.membercard_no='".$selldata['membercard_no']."'");	
			}else{
				if ($selldata['customer_id']<>""){
					$cardlevel_name="普通顾客";
					}else{
					$cardlevel_name="散客";
					}
			}
			$t -> set_var('cardlevel_name',$cardlevel_name);	
			
		//支付方式
		$paytypeenlist=array('xianjinvalue','yinkavalue','zengsongvalue','dingjinvalue','chuzhikavalue','xianjinquanvalue','yufuvalue','yufuproducevalue','zengsongproducevalue','zengsongcardvalue');
		$paytypelist=array('现金','刷卡','扣赠送账户','扣定金','扣储值卡','扣现金券','扣预收款','扣预收产品款','扣赠送产品款','扣赠送购卡款');
		$paytype='';
		  
		for ($i=0;$i<count($paytypeenlist);$i++) {
 
		if($selldata[$paytypeenlist[$i]]>0){
			
		$paytype=$paytype==''?$paytypelist[$i]."：".$selldata[$paytypeenlist[$i]]:$paytype."；".$paytypelist[$i]."：".$selldata[$paytypeenlist[$i]];
		}
		}
		$t -> set_var('paytype',$paytype);			
 
		$status_name=array("<font color=red>未完成</font>","已完成","<font color=red>红字冲销</font>","<font color=red>被红字冲销</font>");
		$t -> set_var('status_name',$status_name[$selldata['status']]);
		
		$t -> set_var('recordcount',$count);
  		$t -> set_var('totalacount',$tempacount);
		$t -> set_var('membercard_no','');
 		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 	
		

		
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');	
 
		
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
	
	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
 
		$delid = $_GET[DELETE.'id'] ;
       
		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'selldetail WHERE sell_id in('.$delid.')');
		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'selldservicesetail WHERE sell_id in('.$delid.')');
		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'sellconsumedetail WHERE sell_id in('.$delid.')'); 
		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'sellcarddetail WHERE sell_id in('.$delid.')');
		
 		 $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'sell WHERE sell_id in('.$delid.')');
		$info='删除';
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}
	function goAppend(){
	
		$id = 0;
		$info = '';
		
		
		
		
		if($this -> isAppend){
			
			$info = '增加';	
			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."purchase` (`purchase_no`,`purchase_time`,`warehouse_id`,`suppliers_id`,`employee_id`,`memo`,`agencyid`,`creattime`,`man`) VALUES ('" .$_POST["purchase_no"]."','".$_POST["purchase_time"]."', '".$_POST["warehouse_id"]."', '" .$_POST["suppliers_id"]."','".$_POST["employee_id"]."', '" .$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["creattime"]."', '".$_POST["man"]."')");
			$id = $this -> dbObj -> Insert_ID();

if($_POST['cproduce_id']!=''&&$_POST['cnumber']!=''){			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."purchasedetail` (purchase_id,produce_id,number,purchaseprice,totalacount,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cprice"]."','".$_POST["ctotalacount"]."','".$_SESSION["currentorgan"]."')");	

			}
			
			
			exit("<script>alert('$info');window.location.href='purchase.php?action=upd&updid=".$id."';</script>");
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$man=$this -> dbObj -> getone('select username from '.WEB_ADMIN_TABPOX.'user where userid = '.$this->getUid());
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."sell` SET  sellmemo='".$_POST['sellmemo']."' WHERE sell_id =".$id);
			

	 
 if($_POST["daction"]=='upd'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX.$_POST['selldetailtable']."` SET `beauty_id` = '".$_POST["dbeauty_id"]."',`employee_id` = '".$_POST["dconsultant_id"]."',`cmlaccount` = '".$_POST["dcmlaccount"]."',`itemmemo` = '".$_POST["ditemmemo"]."' WHERE  selldetail_id  =".$_POST['dselldetail_id']);	
		//echo "UPDATE `".WEB_ADMIN_TABPOX.$_POST['selldetailtable']."` SET `beauty_ic` = '".$_POST["dbeauty_id"]."',`employee_id` = '".$_POST["dconsultant_id"]."',`itemmemo` = '".$_POST["ditemmemo"]."' WHERE  selldetail_id  =".$_POST['dselldetail_id'];
		//echo "UPDATE `".WEB_ADMIN_TABPOX."purchasedetail` SET `produce_id` = '".$_POST["cproduce_id"]."',`number` = '".$_POST["cnumber"]."',`memo` = '".$_POST["cmemo"]."' WHERE purchasedetail_id  =".$_POST['proupdid'];
		
		exit("<script>alert('".$info."成功');window.location.href='sell.php?action=upd&updid=".$id."';</script>");		
		} 
		 
	
		}
//$this -> quit($info.'成功！');
 
		$this -> quit($info.'成功！');

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
function makeno($Prefix,$agency_no,$table,$column,$number,$id){
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." ORDER BY ".$id." desc limit 1");
//echo "select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." ORDER BY ".$id." desc limit 1";
//echo $this -> dbObj -> GetRow("select ".$column." from ".$table." ORDER BY ".$id." desc limit 1");
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
	function quit($info){
		exit("<script>alert('$info');history.go(-1);</script>");
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
  