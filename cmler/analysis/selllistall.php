<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/stock.cls.php');
class Pagecustomer extends admin {
	var $stockObj = null;
    function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='addorder')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> addorder();
        }else if(isset($_GET['action']) && $_GET['action']=='print'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> print1();			
		}else if(isset($_GET['action']) && $_GET['action']=='recoil'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> recoil();			
		}
		else if(isset($_GET['action']) && $_GET['action']=='sellproduceexport'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> sellproduceexport();			
		}else{
            parent::Main();
        }
    }
	function sellproduceexport(){
		//定义模板
		$t = new Template('../template/analysis');
		$t -> set_file('f','sellproduceexport.html');
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
function recoil(){
	$condition =' purchase_id='.$_GET['id'];
	$sql='select * from '.WEB_ADMIN_TABPOX.'purchase  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;	
	
	$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  purchase_id DESC  LIMIT 0 ,1");
		     	while ($inrrs = &$inrs -> FetchRow()) {

			//$man=$this -> dbObj -> getone('select username from '.WEB_ADMIN_TABPOX.'user where userid = '.$this->getUid());
			//插入进货记录
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."purchase` (`purchase_no`,`order_id`,`warehouse_id`,`suppliers_id`,`employee_id`,`acount`,`creattime`,`memo`,`purchase_time`,`status`,`man`,`agencyid`) VALUES ('" .$inrrs["purchase_no"]."', '".$inrrs["order_id"]."','".$inrrs["warehouse_id"]."', '" .$inrrs["suppliers_id"]."','".$inrrs["employee_id"]."', '" .(-$inrrs["acount"])."','".$inrrs['creattime']."', '".$inrrs["memo"]."', '".$inrrs['purchase_time']."','2','".$inrrs['man']."','".$inrrs["agencyid"]."')");
			 //echo "INSERT INTO `".WEB_ADMIN_TABPOX."purchase` (`purchase_no`,`warehouse_id`,`suppliers_id`,`employee_id`,`acount`,`creattime`,`memo`,`purchase_time`,`status`,`man`,`agencyid`) VALUES ('" .$inrrs["purchase_no"]."', '".$inrrs["warehouse_id"]."', '" .$inrrs["suppliers_id"]."','".$inrrs["employee_id"]."', '" .(-$inrrs["acount"])."','".$inrrs['creattime']."', '".$inrrs["memo"]."', '".$inrrs['purchase_time']."','2','".$inrrs['man']."','".$inrrs["agencyid"]."')";
			$warehouse_id=$inrrs["warehouse_id"];
			$id = $this -> dbObj -> Insert_ID();
			
		$sqlstr='select * from '.WEB_ADMIN_TABPOX.'purchasedetail  where  agencyid ='.$_SESSION["currentorgan"].' and purchase_id='.$_GET['id'];	
		
		//插入进货明细
		$inrs1 = &$this -> dbObj -> Execute($sqlstr);
				while ($inrrs1 = &$inrs1 -> FetchRow()) {
					$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."purchasedetail` (purchase_id,produce_id,number,purchase_price,totalacount,memo,agencyid) VALUES ('".$id."','".$inrrs1["produce_id"]."','".(-$inrrs1["number"])."','".$inrrs1["purchase_price"]."','".(-$inrrs1["totalacount"])."','".$inrrs1["memo"]."','".$inrrs1["agencyid"]."')");
				 	// echo "INSERT INTO `".WEB_ADMIN_TABPOX."purchasedetail` (purchase_id,produce_id,number,purchase_price,totalacount,memo,agencyid) VALUES ('".$id."','".$inrrs1["produce_id"]."','".(-$inrrs1["number"])."','".$inrrs1["purchase_price"]."','".(-$inrrs1["totalacount"])."','".$inrrs1["memo"]."','".$inrrs1["agencyid"]."')";
					
					}
			
				}
	   $this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."purchase` SET `status`=3  WHERE purchase_id =".$_GET['id']);//4表示被反冲3反冲2正常1未提交	
	  	//更新库存
		$this->stockObj=new stock();
		$this->stockObj->purchtostock($id,$warehouse_id,$_SESSION["currentorgan"]); 
	   exit("<script>alert('反冲成功');window.location.href='purchasehistory.php';</script>");	
	}	
 
	function disp(){
		//定义模板
		//$this->perObj=new performancehistory();
		$t = new Template('../template/analysis');
		$t -> set_file('f','selllistall.html');
		//定义模板
       

		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		if($_GET["bgdate"]==''){
		$bgdate1=date("Y-m-d",strtotime("$m-3 months",time()));//设置开始时间为上次月结的上3个月
		}else{
		$bgdate1=$_GET["bgdate"];
		}
		if($_GET["enddate"]==''){
		$enddate1=date("Y-m-d",time());//设置开始时间为上次月结的上3个月
		}else{
		$enddate1=$_GET["enddate"];
		}
		$bgdate=$bgdate1." 00:00:00";
		$enddate=$enddate1." 23:59:59";		
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'sell  where status<>0 and  agencyid ='.$_SESSION["currentorgan"].' and '.$condition.$hiddenredstr;
			$sql2='select sum(xianjinvalue) as txj,sum(realpay) as tzs,sum(payable1) as tys,sum(yinkavalue) as tyk,sum(dingjinvalue) as tdj,sum(chuzhikavalue) as tcz  ,sum(yufuvalue) as tyf ,sum(zengsongvalue+xianjinquanvalue) as top ,sum(sellown) as town  from '.WEB_ADMIN_TABPOX.'sell  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition.$hiddenredstr;
			//购买产品 划卡 开卡 券类消费 购买服务 还款 预付手工 下定金
			$sqlpd='select sum(amount) as tpd from '.WEB_ADMIN_TABPOX.'sell s  INNER JOIN '.WEB_ADMIN_TABPOX.'selldetail B on s.sell_id =B.sell_id where  B.item_type=1 and s.agencyid ='.$_SESSION["currentorgan"].' and  s.'.$category.' like "'.$keywords.'" '.$hiddenredstr1;
			$sqlhk='select sum(amount) as thk from '.WEB_ADMIN_TABPOX.'sell s  INNER JOIN '.WEB_ADMIN_TABPOX.'sellconsumedetail B on s.sell_id =B.sell_id where  B.item_type=2 and s.agencyid ='.$_SESSION["currentorgan"].' and  s.'.$category.' like "'.$keywords.'" '.$hiddenredstr1;
			$sqlkk='select sum(amount) as tkk from '.WEB_ADMIN_TABPOX.'sell s  INNER JOIN '.WEB_ADMIN_TABPOX.'sellcarddetail B on s.sell_id =B.sell_id where  B.item_type=3 and s.agencyid ='.$_SESSION["currentorgan"].' and  s.'.$category.' like "'.$keywords.'" '.$hiddenredstr1;
			$sqljl='select sum(amount) as tjl from '.WEB_ADMIN_TABPOX.'sell s  INNER JOIN '.WEB_ADMIN_TABPOX.'sellconsumedetail B on s.sell_id =B.sell_id where  B.item_type=4 and s.agencyid ='.$_SESSION["currentorgan"].' and  s.'.$category.' like "'.$keywords.'" '.$hiddenredstr1;
			$sqlfw='select sum(amount) as tfw from '.WEB_ADMIN_TABPOX.'sell s  INNER JOIN '.WEB_ADMIN_TABPOX.'sellservicesdetail B on s.sell_id =B.sell_id where  B.item_type=0 and s.agencyid ='.$_SESSION["currentorgan"].' and  s.'.$category.' like "'.$keywords.'" '.$hiddenredstr1;
			$sqlrp='select sum(amount) as trp from '.WEB_ADMIN_TABPOX.'sell s  INNER JOIN '.WEB_ADMIN_TABPOX.'sellotherdetail B on s.sell_id =B.sell_id where  B.item_type=5 and B.item_id=3 and s.agencyid ='.$_SESSION["currentorgan"].' and  s.'.$category.' like "'.$keywords.'" '.$hiddenredstr1;
			$sqlyf='select sum(amount) as tyf from '.WEB_ADMIN_TABPOX.'sell s  INNER JOIN '.WEB_ADMIN_TABPOX.'sellotherdetail B on s.sell_id =B.sell_id where  B.item_type=5 and B.item_id=0 and s.agencyid ='.$_SESSION["currentorgan"].' and  s.'.$category.' like "'.$keywords.'" '.$hiddenredstr1;
			$sqldj='select sum(amount) as tdj from '.WEB_ADMIN_TABPOX.'sell s  INNER JOIN '.WEB_ADMIN_TABPOX.'sellotherdetail B on s.sell_id =B.sell_id where  B.item_type=5 and B.item_id=2 and s.agencyid ='.$_SESSION["currentorgan"].' and  s.'.$category.' like "'.$keywords.'" '.$hiddenredstr1;
			}else if($ftable<>''){
			$sql="select f.status as fstatus ,f.customer_id,s.* from ".WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.customer_id =f.customer_id  where f.".$category." like '".$keywords."' and s.status<>0 and f.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			
			 $sql2="select sum(xianjinvalue) as txj,sum(realpay) as tzs,sum(payable1) as tys,sum(yinkavalue) as tyk,sum(dingjinvalue) as tdj,sum(chuzhikavalue) as tcz  ,sum(yufuvalue) as tyf ,sum(zengsongvalue+xianjinquanvalue) as top ,sum(sellown) as town  from ".WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.customer_id =f.customer_id  where f.".$category." like '".$keywords."' and f.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			$sqlpd='select sum(B.amount) as tpd from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.customer_id =f.customer_id  INNER JOIN ".WEB_ADMIN_TABPOX."selldetail B on s.sell_id =B.sell_id   where   B.item_type=1 and f.".$category." like '".$keywords."' and f.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			$sqlhk='select sum(B.amount) as thk from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.customer_id =f.customer_id  INNER JOIN ".WEB_ADMIN_TABPOX."sellconsumedetail B on s.sell_id =B.sell_id   where   B.item_type=2 and f.".$category." like '".$keywords."' and f.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			$sqlkk='select sum(B.amount) as tkk from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.customer_id =f.customer_id  INNER JOIN ".WEB_ADMIN_TABPOX."sellcarddetail B on s.sell_id =B.sell_id   where   B.item_type=3 and f.".$category." like '".$keywords."' and f.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			$sqljl='select sum(B.amount) as tjl from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.customer_id =f.customer_id  INNER JOIN ".WEB_ADMIN_TABPOX."sellconsumedetail B on s.sell_id =B.sell_id   where   B.item_type=4 and f.".$category." like '".$keywords."' and f.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			$sqlfw='select sum(B.amount) as tfw from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.customer_id =f.customer_id  INNER JOIN ".WEB_ADMIN_TABPOX."sellservicesdetail B on s.sell_id =B.sell_id   where   B.item_type=0 and f.".$category." like '".$keywords."' and f.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			$sqlrp='select sum(B.amount) as trp from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.customer_id =f.customer_id  INNER JOIN ".WEB_ADMIN_TABPOX."sellotherdetail B on s.sell_id =B.sell_id   where   B.item_type=5 and B.item_id=3 and f.".$category." like '".$keywords."' and f.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			$sqlyf='select sum(B.amount) as tyf from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.customer_id =f.customer_id  INNER JOIN ".WEB_ADMIN_TABPOX."sellotherdetail B on s.sell_id =B.sell_id   where   B.item_type=5 and B.item_id=0 and f.".$category." like '".$keywords."' and f.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			$sqldj='select sum(B.amount) as tdj from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.customer_id =f.customer_id  INNER JOIN ".WEB_ADMIN_TABPOX."sellotherdetail B on s.sell_id =B.sell_id   where   B.item_type=5 and B.item_id=2 and f.".$category." like '".$keywords."' and f.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'sell  where status<>0 and  agencyid ='.$_SESSION["currentorgan"].$hiddenredstr;
			$sql2='select  sum(xianjinvalue) as txj,sum(realpay) as tzs,sum(payable1) as tys,sum(yinkavalue) as tyk,sum(dingjinvalue) as tdj,sum(chuzhikavalue) as tcz  ,sum(yufuvalue) as tyf ,sum(zengsongvalue+xianjinquanvalue) as top,sum(sellown) as town  from '.WEB_ADMIN_TABPOX.'sell  where  agencyid ='.$_SESSION["currentorgan"].$hiddenredstr;
			$sqlpd='select sum(amount) as tpd from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."selldetail B on s.sell_id =B.sell_id  where  B.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			$sqlpd='select sum(amount) as tpd from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."selldetail B on s.sell_id =B.sell_id  where   B.item_type=1 and  B.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			$sqlhk='select sum(amount) as thk from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."sellconsumedetail B on s.sell_id =B.sell_id  where  B.item_type=2 and  B.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			$sqlkk='select sum(amount) as tkk from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."sellcarddetail B on s.sell_id =B.sell_id  where  B.item_type=3 and  B.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			$sqljl='select sum(amount) as tjl from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."sellconsumedetail B on s.sell_id =B.sell_id  where  B.item_type=4 and  B.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			$sqlfw='select sum(amount) as tfw from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."sellservicesdetail B on s.sell_id =B.sell_id  where  B.item_type=0 and  B.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			$sqlrp='select sum(amount) as trp from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."sellotherdetail B on s.sell_id =B.sell_id  where  B.item_type=5 and B.item_id=3 and  B.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			$sqlyf='select sum(amount) as tyf from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."sellotherdetail B on s.sell_id =B.sell_id  where  B.item_type=5 and B.item_id=0 and  B.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			$sqldj='select sum(amount) as tdj from '.WEB_ADMIN_TABPOX."sell s INNER JOIN ".WEB_ADMIN_TABPOX."sellotherdetail B on s.sell_id =B.sell_id  where  B.item_type=5 and B.item_id=2 and  B.agencyid =".$_SESSION["currentorgan"].$hiddenredstr1;
			}
			
			if($bgdate1<>'' && $enddate1<>''){$sql=$sql.' and '.$timecondition;$sql2=$sql2.' and '.$timecondition;$sqlpd=$sqlpd.' and '.$timecondition;$sqlhk=$sqlhk.' and '.$timecondition;$sqlkk=$sqlkk.' and '.$timecondition;$sqljl=$sqljl.' and '.$timecondition;$sqlfw=$sqlfw.' and '.$timecondition;$sqlrp=$sqlrp.' and '.$timecondition;$sqlyf=$sqlyf.' and '.$timecondition;$sqldj=$sqldj.' and '.$timecondition;}
			 
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  sell_no DESC,sell_id DESC LIMIT ".$offset." , ".$psize);
			$inrrs=&$this -> dbObj ->GetArray($sql." ORDER BY sell_no DESC,sell_id  DESC  LIMIT ".$offset." , ".$psize);
			 
			$inrrscount=sizeof($inrrs);
			$result = &$this -> dbObj -> Execute($sql);	
			 
			$count=$result->RecordCount();
			
			if ($bgdate<>''&& $enddate<>''){$datestr="bgdate=".$bgdate1."&enddate=".$enddate1."&";}else{$datestr='';}
			
			$t -> set_var('pagelist',$this -> page("?".$datestr."category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));			
	    	$t -> set_var('recordcount',$count);	
			
	     	//while ($inrrs = &$inrs -> FetchRow()) {
			for($k=$inrrscount-1;$k>=0;$k--){
				$t -> set_var($inrrs[$k]);
				$t -> set_var($inrrs[$k]);
				$cardlevel_name=$this -> dbObj -> GetOne("select B.cardlevel_name from  ".WEB_ADMIN_TABPOX."membercard  A INNER JOIN  ".WEB_ADMIN_TABPOX."memcardlevel B ON A.cardlevel_id=B.cardlevel_id where  A.customer_id=".$inrrs[$k]['customer_id']);

				$customercatalog_name=$this -> dbObj -> GetOne("select A.customercatalog_name from  ".WEB_ADMIN_TABPOX."customercatalog  A INNER JOIN  ".WEB_ADMIN_TABPOX."customer B ON A.customercatalog_id=B.customercatalog_id where  B.customer_id=".$inrrs[$k]['customer_id']);
$cardlevel_name=$cardlevel_name?$cardlevel_name:$customercatalog_name;	
				$t -> set_var('cardlevel_name',$cardlevel_name);
				$customername=$this -> dbObj -> getone('select customer_name from '.WEB_ADMIN_TABPOX.'customer  where customer_id ='.$inrrs[$k]["customer_id"]);
				$customername=$customername<>''?$customername:'散客';
				$t -> set_var('customer_name',$customername);
			$status_name=array("<font color=blue>未完成</font>","已完成","<font color=red>红字冲销</font>","<font color=red>被红字冲销</font>","<font color=green>已提交</font>","<font color=#AAAAAA>已审核</font>","<font color=red>审核不通过</font>");
			$t -> set_var('status_name',$status_name[$inrrs[$k]['status']]);
				$t -> set_var('ss',$inrrs[$k]['xianjinvalue']+$inrrs[$k]['yinkavalue']);
				$t -> set_var('op',$inrrs[$k]['zengsongvalue']+$inrrs[$k]['xianjinquanvalue']);
				$optitle='';
				if($inrrs[$k]['zengsongvalue']>0){
					$optitle="赠送金额：".$inrrs[$k]['zengsongvalue']."元";
				}
				if($inrrs[$k]['xianjinquanvalue']>0){
					$optitle=$optitle==''?'':"\r\n";
					$optitle="现金券：".$inrrs[$k]['xianjinquanvalue']."元";
				}
				
				//$t -> set_var('op',$inrrs[$k]['zengsongvalue']+$inrrs[$k]['dingjinvalue']+$inrrs[$k]['chuzhikavalue']+$inrrs[$k]['xianjinquanvalue']+$inrrs[$k]['yufuvalue']);
				$t -> set_var('shifuvalue',$inrrs[$k]['xianjinvalue']+$inrrs[$k]['yinkavalue']);
				$t -> set_var('own',$inrrs[$k]['sellown']);
				$t -> set_var('date',date('Y-m-d',strtotime($inrrs[$k]['creattime'])));
				$pdtitle=$this -> produce($inrrs[$k]["sell_id"]);
				$kktitle=$this -> buycard($inrrs[$k]["sell_id"]);
				$hktitle=$this -> consumecard($inrrs[$k]["sell_id"]);
				$jltitle=$this -> consumecoupon($inrrs[$k]["sell_id"]);
				$fwtitle=$this -> services($inrrs[$k]["sell_id"]);
				$djtitle=$this -> dingjin($inrrs[$k]["sell_id"]);
				$rptitle=$this -> repayment($inrrs[$k]["sell_id"]);
				$yftitle=$this -> yufu($inrrs[$k]["sell_id"]);
				$hjtitle=$this -> huijiremain($inrrs[$k]["sell_id"]);
				$cztitle=$this -> chuzhiremain($inrrs[$k]["sell_id"]);
				
				$t -> set_var('pdtitle',$pdtitle);
				$t -> set_var('hktitle',$hktitle);
				$t -> set_var('kktitle',$kktitle);
				$t -> set_var('jltitle',$jltitle);
				$t -> set_var('fwtitle',$fwtitle);
				$t -> set_var('rptitle',$rptitle);
				$t -> set_var('yftitle',$yftitle);
				$t -> set_var('djtitle',$djtitle);
				$t -> set_var('hjtitle',$hjtitle);
				$t -> set_var('cztitle',$cztitle);
				$t -> set_var('optitle',$optitle);
				$t -> set_var('pd',$this -> dbObj -> getone('select sum(amount) from '.WEB_ADMIN_TABPOX.'selldetail  where item_type=1 and  sell_id='.$inrrs[$k]["sell_id"]));
				$t -> set_var('hk',$this -> dbObj -> getone('select sum(amount) from '.WEB_ADMIN_TABPOX.'sellconsumedetail  where item_type=2 and  sell_id='.$inrrs[$k]["sell_id"]));
				$t -> set_var('jl',$this -> dbObj -> getone('select sum(amount) from '.WEB_ADMIN_TABPOX.'sellconsumedetail  where item_type=4 and  sell_id='.$inrrs[$k]["sell_id"]));
				$t -> set_var('kk',$this -> dbObj -> getone('select sum(amount) from '.WEB_ADMIN_TABPOX.'sellcarddetail  where item_type=3 and  sell_id='.$inrrs[$k]["sell_id"]));
				$t -> set_var('fw',$this -> dbObj -> getone('select sum(amount) from '.WEB_ADMIN_TABPOX.'sellservicesdetail  where item_type=0 and  sell_id='.$inrrs[$k]["sell_id"]));
				$t -> set_var('rp',$this -> dbObj -> getone('select sum(amount) from '.WEB_ADMIN_TABPOX.'sellotherdetail  where item_type=5 and item_id=3 and  sell_id='.$inrrs[$k]["sell_id"]));
				
				$yf=$this -> dbObj -> getone('select sum(amount) from '.WEB_ADMIN_TABPOX.'sellotherdetail  where item_type=5 and item_id=0 and  sell_id='.$inrrs[$k]["sell_id"]);
				$yf1= $this -> dbObj -> GetOne("select yufuvalue FROM  ".WEB_ADMIN_TABPOX."sell   WHERE  sell_id =".$inrrs[$k]["sell_id"]);
				$yf=$yf-$yf1;
				$t -> set_var('yf',$yf);
				
				$dj=$this -> dbObj -> getone('select sum(amount) from '.WEB_ADMIN_TABPOX.'sellotherdetail  where item_type=5 and item_id=2 and  sell_id='.$inrrs[$k]["sell_id"]);
				$dj1= $this -> dbObj -> GetOne("select dingjinvalue FROM  ".WEB_ADMIN_TABPOX."sell   WHERE  sell_id =".$inrrs[$k]["sell_id"]);
				$dj=$dj-$dj1;
				$t -> set_var('dj',$dj);
				
				  //$t -> set_var('kx',$this -> dbObj -> getone('select sum(amount) from '.WEB_ADMIN_TABPOX.'sellotherdetail  where item_type=5 and item_id in(1) and  sell_id='.$inrrs[$k]["sell_id"]));
				$t -> set_var('dingjin',$inrrs[$k]['dingjinvalue']);
				$t -> set_var('chuzhi',$inrrs[$k]['chuzhikavalue']);
				$t -> set_var('yufu',$inrrs[$k]['yufuvalue']);
				$t -> set_var('xj',$inrrs[$k]['xianjinvalue']);
				$t -> set_var('xj',$inrrs[$k]['xianjinvalue']);
				$t -> set_var('yk',$inrrs[$k]['yinkavalue']);
				$t -> set_var('yfr',$inrrs[$k]['yufuremain']);
				$t -> set_var('yfr',$inrrs[$k]['yufuremain']);
				
				$t -> set_var('hjr',sprintf("%01.0f",$inrrs[$k]['memcardremain']));
				$t -> set_var('yfr',$inrrs[$k]['yufuremain']);
				$t -> set_var('djr',$inrrs[$k]['dingjinremain']);
				$t -> set_var('czr',$inrrs[$k]['chuzhiremain']);
				$t -> set_var('xmr',sprintf("%01.0f",$inrrs[$k]['itemcardremain']));
							
				$t -> set_var('view','<a href="#" onclick=viewbill('.$inrrs[$k]['sell_id'].');>查看</a>'); 
				$t -> set_var('print','<a href="?action=print&updid='.$inrrs[$k]['sell_id'].'" target="_blank">打印</a>');
				$t -> set_var('recoil','<a href="#"  onclick=recoil('.$inrrs[$k]['sell_id'].')>反冲</a>');
				$t -> set_var('brand_name',$this -> dbObj -> getone('select brand_name from '.WEB_ADMIN_TABPOX.'brand  where brand_id ='.$inrrs[$k]["brandid"]));
				$t -> set_var('employee_name',$this -> dbObj -> getone('select employee_name  from '.WEB_ADMIN_TABPOX.'employee  where employee_id ='.$inrrs[$k]["employee_id"]));
				$t -> set_var('order_no',$this -> dbObj -> getone('select order_no from '.WEB_ADMIN_TABPOX.'order  where order_id ='.$inrrs[$k]["order_id"]));
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs[$k]['purchase_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs[$k]['sell_id']));	
				
				
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();
			$totaldata=$this -> dbObj -> GetRow($sql2);
			$totalpd=$this -> dbObj -> GetRow($sqlpd);
			$totalhk=$this -> dbObj -> GetRow($sqlhk);
			$totalkk=$this -> dbObj -> GetRow($sqlkk);
			$totaljl=$this -> dbObj -> GetRow($sqljl);
			$totalfw=$this -> dbObj -> GetRow($sqlfw);
			$totalrp=$this -> dbObj -> GetRow($sqlrp);
			$totalyf=$this -> dbObj -> GetRow($sqlyf);
			$totalyf['tyf']=$totalyf['tyf']-$totaldata['tyf'];
			$totaldj=$this -> dbObj -> GetRow($sqldj);
			 
			$totaldj['tdj']=$totaldj['tdj']-$totaldata['tdj'];
			//$totaldj=$this -> dbObj -> GetRow($sqldj);
			
			 
			$t -> set_var('txj',$totaldata['txj']);
			$t -> set_var('tyk',$totaldata['tyk']);
			$t -> set_var('tss',$totaldata['txj']+$totaldata['tyk']);
			$t -> set_var('tzs',$totaldata['tzs']);
			$t -> set_var('tys',$totaldata['tys']);
			$t -> set_var('tyf',$totaldata['tyf']);
			$t -> set_var('tcz',$totaldata['tcz']);
			$t -> set_var('tdj',$totaldata['tdj']);
			$t -> set_var('top',$totaldata['top']);
			$t -> set_var('town',$totaldata['town']);
			$t -> set_var('tpd',$totalpd['tpd']);
			$t -> set_var('thk',$totalhk['thk']);
			$t -> set_var('tkk',$totalkk['tkk']);
			$t -> set_var('tjl',$totaljl['tjl']);
			$t -> set_var('tfw',$totalfw['tfw']);
			$t -> set_var('trp',$totalrp['trp']);
			$t -> set_var('tyf2',$totalyf['tyf']);
			$t -> set_var('tdj2',$totaldj['tdj']);
			
			if($bgdate1){
			$t -> set_var('bgdate',$bgdate1);
			}else{
			
			$bgdate1=date("Y-m-d",strtotime("$m-3 months",time()));//设置开始时间为上次月结的上3个月
			$t -> set_var('bgdate',$bgdate1);
			}
			if($enddate1){
			$t -> set_var('enddate',$enddate1);
			}else{
			$t -> set_var('enddate',date('Y-m-d'));
			}
		 
        $t -> set_var($category.'_selected','selected');
		$t -> set_var('keywords',$keywords);
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canexport',''):$t -> set_var('canexport','none');	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	
function produce($sellid){//返回产品明细备注

			$produceinrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'selldetail  where item_type=1 and  sell_id='.$sellid);
			$pdtitle='';
			while ($inrrsproduce = &$produceinrs -> FetchRow()) {
					$produce=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'produce where  produce_id='.$inrrsproduce["item_id"]);
					$unitname=$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit where  unit_id='.$produce["standardunit"]);
					
					$pdtitle=$pdtitle==''?'':$pdtitle."\r\n";
					$pdtitle=$pdtitle.$produce['produce_name']." ".$inrrsproduce['number']." ".$unitname." ".$inrrsproduce['amount']."元";
				 
			}
			return 	$pdtitle;

}
function buycard($sellid){//返回产品明细备注

			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'sellcarddetail  where item_type=3 and  sell_id='.$sellid);
			$title='';
			while ($inrrs = &$inrs -> FetchRow()) {
					$card=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'marketingcard where  marketingcard_id='.$inrrs["item_id"]);
					//$unitname=$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit where  unit_id='.$card["standardunit"]);
					$unitname='张';
					$title=$title==''?'':$title."\r\n";
					$title=$title.$card['marketingcard_name']." ".$inrrs['number']." ".$unitname." ".$inrrs['amount']."元";
				 
			}
			return 	$title;

}
function consumecard($sellid){//返回划卡明细备注

			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'sellconsumedetail  where item_type=2 and  sell_id='.$sellid);
			$title='';
			while ($inrrs = &$inrs -> FetchRow()) {
					$card=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'marketingcard where  marketingcard_id='.$inrrs["cardid"]);
					$servicesname=$this -> dbObj -> getone('select services_name from '.WEB_ADMIN_TABPOX.'services where  services_id='.$inrrs["item_id"]);
					//echo 'select services_name from '.WEB_ADMIN_TABPOX.'services where  services_id='.$inrrs["item_id"];
					$unitname='次';
					$title=$title==''?'':$title."\r\n";
					$title=$title.$card['marketingcard_name']." ".$servicesname."  ".$inrrs['number']." ".$unitname."".$inrrs['amount']."元";
				 
			}
			return 	$title;

}
function consumecoupon($sellid){//返回划卡明细备注

			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'sellconsumedetail  where item_type=4 and  sell_id='.$sellid);
			$title='';
			while ($inrrs = &$inrs -> FetchRow()) {
					$card=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'marketingcard where  marketingcard_id='.$inrrs["cardid"]);
					$servicesname=$this -> dbObj -> getone('select services_name from '.WEB_ADMIN_TABPOX.'services where  services_id='.$inrrs["item_id"]);
					//echo 'select services_name from '.WEB_ADMIN_TABPOX.'services where  services_id='.$inrrs["item_id"];
					$unitname='次';
					$title=$title==''?'':$title."\r\n";
					$title=$title.$card['marketingcard_name']." ".$servicesname."  ".$inrrs['number']." ".$unitname."".$inrrs['amount']."元";
				 
			}
			return 	$title;

}
function services($sellid){//返回划卡明细备注

			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'sellservicesdetail  where item_type=0 and  sell_id='.$sellid);
			$title='';
			while ($inrrs = &$inrs -> FetchRow()) {
					//$card=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'marketingcard where  marketingcard_id='.$inrrs["cardid"]);
					$servicesname=$this -> dbObj -> getone('select services_name from '.WEB_ADMIN_TABPOX.'services where  services_id='.$inrrs["item_id"]);
					//echo 'select services_name from '.WEB_ADMIN_TABPOX.'services where  services_id='.$inrrs["item_id"];
					$unitname='次';
					$title=$title==''?'':$title."\r\n";
					$title=$title.$servicesname."  ".$inrrs['number']." ".$unitname."".$inrrs['amount']."元";
				 
			}
			return 	$title;

}
function dingjin($sellid){//返回划卡明细备注

			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'sellotherdetail  where item_type=5 and item_id=2 and  sell_id='.$sellid);
			$title='';
			while ($inrrs = &$inrs -> FetchRow()) {
					//$card=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'marketingcard where  marketingcard_id='.$inrrs["cardid"]);
					//$servicesname=$this -> dbObj -> getone('select services_name from '.WEB_ADMIN_TABPOX.'services where  services_id='.$inrrs["item_id"]);
					//echo 'select services_name from '.WEB_ADMIN_TABPOX.'services where  services_id='.$inrrs["item_id"];
					$unitname='次';
					$title=$title==''?'':$title."\r\n";
					$title=$title.$inrrs['itemmemo'];
				 
			}
			return 	$title;

}
function yufu($sellid){//返回划卡明细备注

			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'sellotherdetail  where item_type=5 and item_id=0 and  sell_id='.$sellid);
			$title='';
			while ($inrrs = &$inrs -> FetchRow()) {
					//$card=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'marketingcard where  marketingcard_id='.$inrrs["cardid"]);
					//$servicesname=$this -> dbObj -> getone('select services_name from '.WEB_ADMIN_TABPOX.'services where  services_id='.$inrrs["item_id"]);
					//echo 'select services_name from '.WEB_ADMIN_TABPOX.'services where  services_id='.$inrrs["item_id"];
					$unitname='次';
					$title=$title==''?'':$title."\r\n";
					$title=$title.$inrrs['itemmemo'];
				 
			}
			return 	$title;

}
function repayment($sellid){//返回划卡明细备注

			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'sellotherdetail  where item_type=5 and item_id=3 and  sell_id='.$sellid);
			$title='';
			while ($inrrs = &$inrs -> FetchRow()) {
					//$card=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'marketingcard where  marketingcard_id='.$inrrs["cardid"]);
					//$servicesname=$this -> dbObj -> getone('select services_name from '.WEB_ADMIN_TABPOX.'services where  services_id='.$inrrs["item_id"]);
					//echo 'select services_name from '.WEB_ADMIN_TABPOX.'services where  services_id='.$inrrs["item_id"];
					$unitname='次';
					$title=$title==''?'':$title."\r\n";
					$title=$title.$inrrs['itemmemo'];
				 
			}
			return 	$title;

}

function huijiremain($sellid){//返回会籍卡剩余备注
			$sell=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'sell where  sell_id='.$sellid);
			
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'membershipcard where buydate between "'.date('Y-m-d',$sell["creattime"]).'" and "'.date("Y-m-d",time()).'" and status=1 and   customer_id='.$sell["customer_id"] );
			//echo 'select * from '.WEB_ADMIN_TABPOX.'membershipcard where buydate between "'.date('Y-m-d',strtotime($sell["creattime"])).'" and "'.date("Y-m-d",time()).'"  and  customer_id='.$sell["customer_id"];
			if($sell["customer_id"]>0){
			//echo 'select * from '.WEB_ADMIN_TABPOX.'membershipcard where buydate between "'.date('Y-m-d',$sell["creattime"]).'" and "'.date("Y-m-d",time()).'"  and  customer_id='.$sell["customer_id"];
					 }
			$title='';
			// echo 'select * from '.WEB_ADMIN_TABPOX.'membershipcard where buydate between "'.date('Y-m-d',$sell["creattime"]).'" and "'.date("Y-m-d",time()).'"  and  customer_id='.$sell["customer_id"];
			while ($inrrs = &$inrs -> FetchRow()) {
				 
					//$card=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'marketingcard where  marketingcard_id='.$inrrs["cardid"]);
					$marketingcard_name=$this -> dbObj -> getone('select marketingcard_name from '.WEB_ADMIN_TABPOX.'marketingcard  where  marketingcard_id='.$inrrs["marketingcard_id"]);
					//echo 'select services_name from '.WEB_ADMIN_TABPOX.'services where  services_id='.$inrrs["item_id"];
					//$unitname='次';
					 
					$title=$title==''?'':$title."\r\n";
					$title=$title.$marketingcard_name;
					
				 
			}
			return 	$title;

}

function chuzhiremain($sellid){//返回会籍卡剩余备注
			$sell=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'sell where  sell_id='.$sellid);
			
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'storedvaluedcard where buydate between "'.date('Y-m-d',$sell["creattime"]).'" and "'.date("Y-m-d",time()).'" and status=1 and   customer_id='.$sell["customer_id"] );
			//echo 'select * from '.WEB_ADMIN_TABPOX.'membershipcard where buydate between "'.date('Y-m-d',strtotime($sell["creattime"])).'" and "'.date("Y-m-d",time()).'"  and  customer_id='.$sell["customer_id"];
			if($sell["customer_id"]>0){
			//echo 'select * from '.WEB_ADMIN_TABPOX.'membershipcard where buydate between "'.date('Y-m-d',$sell["creattime"]).'" and "'.date("Y-m-d",time()).'"  and  customer_id='.$sell["customer_id"];
					 }
			$title='';
			// echo 'select * from '.WEB_ADMIN_TABPOX.'membershipcard where buydate between "'.date('Y-m-d',$sell["creattime"]).'" and "'.date("Y-m-d",time()).'"  and  customer_id='.$sell["customer_id"];
			while ($inrrs = &$inrs -> FetchRow()) {
				 
					//$card=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'marketingcard where  marketingcard_id='.$inrrs["cardid"]);
					$marketingcard_name=$this -> dbObj -> getone('select marketingcard_name from '.WEB_ADMIN_TABPOX.'marketingcard  where  marketingcard_id='.$inrrs["marketingcard_id"]);
					//echo 'select services_name from '.WEB_ADMIN_TABPOX.'services where  services_id='.$inrrs["item_id"];
					//$unitname='次';
					 
					$title=$title==''?'':$title."\r\n";
					$title=$title.$marketingcard_name;
					
				 
			}
			return 	$title;

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
		$sql=$sql1." union ".$sql2." union ".$sql3." union ".$sql4;
		 $table_name=array('services',"produce","services","marketingcard","services");	
		  $itemtype_name=array('单项服务',"购买产品","消费卡项","购买卡项","消费券项");	
		  
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
			$t -> parse('ml','mainlist',true);
		}
 
 

 
 
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
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
			
		//$t -> set_var('purchase_no',"");
		$Prefix='PC';
		$agency_no=$_SESSION["agency_no"].date('ymd',time());
		$table=WEB_ADMIN_TABPOX.'purchase';
		$column='purchase_no';
		$number=3;
		$id='purchase_id';	
		
		$t -> set_var('purchase_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));	
		$t -> set_var('suppliers_no',"");	
		$t -> set_var('creattime',date('Y-m-d H:i:s',time()));	
		$t -> set_var('suppliers_no',"");
		$t -> set_var('suppliers_id',"");
		$t -> set_var('suppliers_name',"");

		$t -> set_var('employee_name',"");	
		$t -> set_var('employee_id',"");	
		$t -> set_var('purchase_time',date('Y-m-d',time()));	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");
		$t -> set_var('comdisplay',"none");		
		$t -> set_var('memo',"");

		$t -> set_var('man',$this -> dbObj -> getone('select B.employee_name from '.WEB_ADMIN_TABPOX.'user A INNER JOIN '.WEB_ADMIN_TABPOX.'employee B ON A.employee_id=B.employee_id  where A.userid = '.$this->getUid()));

		$t -> set_var('cproduce_no',"");
		$t -> set_var('cproduce_name',"");
		$t -> set_var('cproduce_id',"");	
		$t -> set_var('ccode',"");	
		$t -> set_var('ctotalacount',"");	
		$t -> set_var('cnumber','');	
		$t -> set_var('cprice',"");	
		$t -> set_var('cviceunit','');
		$t -> set_var('cmemo','');
		$t -> set_var('ml',"");	
		$t -> set_var('storageidlist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',''));	
		$t -> set_var('addprodisplay',"display:none");	
		$t -> set_var('userid',$this->getUid());		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'sell WHERE sell_id = '.$updid);
			$t -> set_var('warehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse where warehouse_id = '.$data['warehouse_id']));
			$t -> set_var('storageidlist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$data['warehouse_id']));
			$t -> set_var('order_no',$this->dbObj->GetOne('SELECT order_no FROM '.WEB_ADMIN_TABPOX.'order WHERE order_id = '.$data[order_id]));
			if($data['status']==2){
			$t -> set_var('recoildisabled','disabled="disabled"');
			}else if($data['status']==3){
			$t -> set_var('recoildisabled','disabled="disabled"');
			}
			
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');			
			$t -> set_var($data);			
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$t -> set_var('comdisplay',"");	
		$t -> set_var('cproduce_no',"");	
		$t -> set_var('cproduce_name',"");	
		$t -> set_var('ccode',"");	
		$t -> set_var('cproduce_id',"");	
		$t -> set_var('cstandardunit',"");	
		$t -> set_var('lowerlimit',"");	
		$t -> set_var('cprice',"");	
		$t -> set_var('cmemo',"");	
		$t -> set_var('cnumber',"");	
		$t -> set_var('cviceunitnumber',"");	
		$t -> set_var('cviceunit',"");	
		$t -> set_var('proupdid',"");	
		$t -> set_var('ctotalacount',"");	
		$acount = &$this -> dbObj -> GetOne('select count(*) as acount from '.WEB_ADMIN_TABPOX.'selldetail  where purchase_id  ='.$updid);
		$t -> set_var('recordcount',$acount);	
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
			
			$inrs2 = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'employee   where employee_id  ='.$data['employee_id']);
	     	while ($inrrs2 = &$inrs2 -> FetchRow()) {
				$t -> set_var($inrrs2);
			}			
//设置供应商

			$inrs1 = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'suppliers  where suppliers_id  ='.$data['suppliers_id']);
	     	while ($inrrs1 = &$inrs1 -> FetchRow()) {
				$t -> set_var($inrrs1);
			}
			$inrs1 -> Close();						
		//设置消耗品列表
			
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'selldetail  where sell_id  ='.$updid);
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('numbers',$inrrs['number']);
				$data1=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrrs["produce_id"]);
				$t -> set_var($data1);
				
				$t -> set_var('unit_name',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$data1["standardunit"]));
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();			

		// 修改消耗品
		 
		    if($_GET['cpurchasedetail_id']!=''){
			//$t -> set_var('cs');
		   $t -> set_var('caction','upd');	
            
			$inrs2 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'purchasedetail  where purchdetail_id ='.$_GET['cpurchasedetail_id']);
			//$t -> set_var($inrs);
			
		    $t -> set_var('cupdid',$inrs2['produce_id']);	
			$t -> set_var('proupdid',$_GET['cpurchasedetail_id']);	
			
			$t -> set_var('cnumber',$inrs2['number']);
			$t -> set_var('ctotalacount',$inrs2['totalacount']);
			$inrs3 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrs2['produce_id']);
			$t -> set_var('cproduce_no',$inrs3['produce_no']);
			$t -> set_var('cproduce_name',$inrs3['produce_name']);
			$t -> set_var('cstandardunit',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$inrs3['standardunit']));
			$t -> set_var('cproduce_id',$inrs3['produce_id']);
			$t -> set_var('ccode',$inrs3['code']);
			$t -> set_var('cprice',$inrs3['price']);
						
			//$t -> set_var('cviceunit',$inrs3['viceunit']);
			//$t -> set_var('cviceunitnumber',$inrs3['viceunitnumber']);
			//$t -> parse('cs','consumables',true);
			//$inrs2 -> Close();	
			}
	
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));	

		}
		//$t -> set_var('categorynamelist',$this ->selectlist('servicecategory','category_id','category_name',$data['categoryid']));		
		
		
		//$t -> set_var('ml',"");	
		$t -> set_var('memo',$data['memo']);//因为与明细的memo冲突，故放到这里。
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
       
		//$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'selldetail WHERE sell_id in('.$delid.')');
		//$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'selldservicesetail WHERE sell_id in('.$delid.')');
		//$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'sellconsumedetail WHERE sell_id in('.$delid.')'); 
		//$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'sellcarddetail WHERE sell_id in('.$delid.')');
		//echo 'DELETE FROM '.WEB_ADMIN_TABPOX.'sellconsumedetail WHERE selldetail_id in('.$delid.')';
 		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'selldetail WHERE selldetail_id in('.$delid.')');
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
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."purchase` SET `order_id` = '".$_POST["order_id"]."',`purchase_no` = '".$_POST["purchase_no"]."',`purchase_time` = '".$_POST["purchase_time"]."',`memo` = '".$_POST["memo"]."',`warehouse_id` = '".$_POST["warehouse_id"]."',`suppliers_id` = '".$_POST["suppliers_id"]."',`employee_id` = '".$_POST["employee_id"]."',`man`='".$man."'  WHERE purchase_id =".$id);
			

		//echo $_POST["con_act"];
 if($_POST["con_act"]=='upd'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."purchasedetail` SET `produce_id` = '".$_POST["cproduce_id"]."',`number` = '".$_POST["cnumber"]."',`memo` = '".$_POST["cmemo"]."',totalacount='".$_POST["ctotalacount"]."' WHERE purchdetail_id  =".$_POST['proupdid']);		
		//echo "UPDATE `".WEB_ADMIN_TABPOX."purchasedetail` SET `produce_id` = '".$_POST["cproduce_id"]."',`number` = '".$_POST["cnumber"]."',`memo` = '".$_POST["cmemo"]."' WHERE purchasedetail_id  =".$_POST['proupdid'];
		
		exit("<script>alert('".$info."商品成功');window.location.href='purchase.php?action=upd&updid=".$id."';</script>");		
		}else{
        if($_POST["con_act"]=='add'&&$_POST["cnumber"]!=''){
		$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."purchasedetail` (purchase_id,produce_id,number,purchase_price,totalacount,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cnumber"]."','".$_POST["cprice"]."','".$_POST["ctotalacount"]."','".$_SESSION["currentorgan"]."')");	
		//$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."serviceconsume` SET `produce_id` = '".$_POST["produce_id"]."',`std_consumption` = '".$_POST["std_consumption"]."',`memo` = '".$_POST["memo"]."',`purchase` = '".$_POST["purchase"]."' WHERE purchase_id =".$id);
		//echo "INSERT INTO `".WEB_ADMIN_TABPOX."consumables` (purchase_id,produce_id,std_consumption,memo,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cstd_consumption"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')";
		$info = '添加商品';
		}
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
  