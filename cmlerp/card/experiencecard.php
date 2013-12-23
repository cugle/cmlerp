<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/card.cls.php');

class Pagecustomer extends admin {
	var $CardObj = null;
    function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='addcard')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> addcard();
        }else if(isset($_GET['action']) && $_GET['action']=='editcard'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> editcard();			
		}else{
            parent::Main();
        }
    }
function  editcard(){
	$condition =' order_id='.$_GET['orderid'];
	$updid=$_GET['updid'];
	$sql='select * from '.WEB_ADMIN_TABPOX.'order  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;	
	
	$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  order_id DESC  LIMIT 0 ,1");
		     	while ($inrrs = &$inrs -> FetchRow()) {


			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."purchase` SET `purchase_no`='" .$_GET["purchase_no"]."',`order_id`='".$_GET['orderid']."',`warehouse_id`='".$inrrs["warehouse_id"]."',`suppliers_id`='" .$inrrs["suppliers_id"]."',`employee_id`='".$inrrs["employee_id"]."',`acount`='" .$inrrs["acount"]."',`creattime`='".date('Y-m-d H:i:s',time())."',`memo`= '".$inrrs["memo"]."',`agencyid`='".$_SESSION["currentorgan"]."' WHERE purchase_id=".$updid);
		 	//echo "UPDATE `".WEB_ADMIN_TABPOX."purchase` SET `purchase_no`='" .$_GET["purchase_no"]."',`order_id`='".$_GET['orderid']."',`warehouse_id`='".$inrrs["warehouse_id"]."',`suppliers_id`='" .$inrrs["suppliers_id"]."',`employee_id`='".$inrrs["employee_id"]."',`acount`='" .$inrrs["acount"]."',`creattime`='".date('Y-m-d',time())."',`memo`= '".$inrrs["memo"]."',`agencyid`='".$_SESSION["currentorgan"]."' WHERE purchase_id=".$updid;
			//$id = $this -> dbObj -> Insert_ID();
			//echo $id;
		$sqldel='delete from '.WEB_ADMIN_TABPOX.'purchasedetail where agencyid='.$_SESSION["currentorgan"].' and purchase_id='.$updid;
		$this -> dbObj -> Execute($sqldel);
		$sqlstr='select * from '.WEB_ADMIN_TABPOX.'orderdetail  where  agencyid ='.$_SESSION["currentorgan"].' and order_id='.$_GET['orderid'];	
		$inrs1 = &$this -> dbObj -> Execute($sqlstr);
				while ($inrrs1 = &$inrs1 -> FetchRow()) {
					$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."purchasedetail` (purchase_id,produce_id,number,purchase_price,totalacount,warehouse_id,agencyid) VALUES ('".$updid."','".$inrrs1["produce_id"]."','".$inrrs1["number"]."','".$inrrs1["orderprice"]."','".$inrrs1["totalacount"]."','".$inrrs1['warehouse_id']."','".$_SESSION["currentorgan"]."')");
					//echo "INSERT INTO `".WEB_ADMIN_TABPOX."purchasedetail` (purchase_id,produce_id,number,purchase_price,totalacount,agencyid) VALUES ('".$id."','".$inrrs1["produce_id"]."','".$inrrs1["number"]."','".$inrrs1["orderprice"]."','".$inrrs1["totalacount"]."','".$_SESSION["currentorgan"]."')";
					
					}
			
				}
	   exit("<script>window.location.href='purchase.php?action=upd&updid=".$updid."';</script>");
}	
function  addcard(){
	$condition =' marketingcard_id='.$_GET['marketingcard_id'];
	$sql='select * from '.WEB_ADMIN_TABPOX.'marketingcard  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;	
	
	
	$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  marketingcard_id DESC  LIMIT 0 ,1");
		     	while ($inrrs = &$inrs -> FetchRow()) {

			$employee_id=$this -> dbObj -> GetOne("select employee_id from`".WEB_ADMIN_TABPOX."user`  WHERE `userid`=".$this->getUid()." and `agencyid`=".$_SESSION["currentorgan"]);
			//插入进货记录
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."experiencecard` (`experiencecard_no`,`marketingcard_id`,`totaltimes`,`price`,`timelimit`,`buydate`,`activedate`,`employee_id`,`agencyid`) VALUES ('" .$_GET["experiencecard_no"]."','".$_GET['marketingcard_id']."', '".$inrrs["totaltimes"]."', '" .$inrrs["price"]."','".$inrrs["timelimit"]."', '".date('Y-m-d',time())."', '".date('Y-m-d',time())."','".$employee_id."','".$_SESSION["currentorgan"]."')");
echo "INSERT INTO `".WEB_ADMIN_TABPOX."experiencecard` (`item_no`,`marketingcard_id`,`totaltimes`,`price`,`timelimit`,`buydate`,`activedate`,`employee_id`,`agencyid`) VALUES ('" .$_GET["item_no"]."','".$_GET['marketingcard_id']."', '".$inrrs["totaltimes"]."', '" .$inrrs["price"]."','".$inrrs["timelimit"]."', '".date('Y-m-d',time())."', '".date('Y-m-d',time())."','".$employee_id."','".$_SESSION["currentorgan"]."')";
			$id = $this -> dbObj -> Insert_ID();
			echo $id;
		$sqlstr='select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail   where  agencyid ='.$_SESSION["currentorgan"].' and marketingcard_id='.$_GET['marketingcard_id'];	
		
		//插入进货明细
		$inrs1 = &$this -> dbObj -> Execute($sqlstr);
				while ($inrrs1 = &$inrs1 -> FetchRow()) {
					
					$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."experiencecarddetail` (experiencecard_id,services_id ,remaintimes,totaltimes,agencyid) VALUES ('".$id."','".$inrrs1["services_id"]."','".$inrrs1["totaltimes"]."','".$inrrs1["totaltimes"]."','".$_SESSION["currentorgan"]."')");
					echo "INSERT INTO `".WEB_ADMIN_TABPOX."experiencecarddetail` (experiencecard_id,services_id ,remaintimes,totaltimes,agencyid) VALUES ('".$id."','".$inrrs1["services_id"]."','".$inrrs1["totaltimes"]."','".$inrrs1["totaltimes"]."','".$_SESSION["currentorgan"]."')";
					
					}
			
				}
	  exit("<script>window.location.href='experiencecard.php?action=upd&updid=".$id."';</script>");
}	
	function disp(){
		
		//定义模板
		$experiencecardtype_id= "1";
		$t = new Template('../template/card');
		$t -> set_file('f','experiencecard.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "%'.$keywords.'"';}else{$condition=$category.' like "%'.$keywords.'%"';}
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
			$sql="select *,C.customer_id AS  customer_id,S.status AS  status  from ".WEB_ADMIN_TABPOX."experiencecard  S LEFT JOIN ".WEB_ADMIN_TABPOX."customer C on C.customer_id =S.customer_id LEFT JOIN ".WEB_ADMIN_TABPOX."membercard M on M.customer_id =C.customer_id  WHERE  S.status in (0,1,2,3) AND S.agencyid =".$_SESSION["currentorgan"].' and '.$condition.' GROUP BY experiencecard_id';
			}else if($ftable<>''){
			$sql="select *,C.customer_id AS  customer_id,S.status AS  status   from ".WEB_ADMIN_TABPOX."experiencecard  S LEFT JOIN ".WEB_ADMIN_TABPOX."customer C on C.customer_id =S.customer_id LEFT JOIN ".WEB_ADMIN_TABPOX."membercard M on M.customer_id =C.customer_id  LEFT JOIN ".WEB_ADMIN_TABPOX."marketingcard MC on MC.marketingcard_id =S.marketingcard_id WHERE  S.status in (0,1,2,3) AND S.agencyid =".$_SESSION["currentorgan"].' and '.$condition.' GROUP BY experiencecard_id';
		

			}else{
			$sql="select *,C.customer_id AS customer_id,S.status AS  status  from ".WEB_ADMIN_TABPOX."experiencecard S LEFT JOIN ".WEB_ADMIN_TABPOX."customer C on C.customer_id =S.customer_id LEFT JOIN ".WEB_ADMIN_TABPOX."membercard M on M.customer_id =C.customer_id  WHERE  S.status in (0,1,2,3) AND S.agencyid =".$_SESSION["currentorgan"].' GROUP BY experiencecard_id';
		 
			}
			
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  `experiencecard_id` DESC  LIMIT ".$offset." , ".$psize);
			
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));			
	    	$t -> set_var('recordcount',$count);	
			
	     	while ($inrrs = $inrs -> FetchRow()) {
				
				$t -> set_var($inrrs);
					
				$t -> set_var('marketingcard_name',$this -> dbObj -> getone('select marketingcard_name from '.WEB_ADMIN_TABPOX.'marketingcard  where marketingcard_id ='.$inrrs["marketingcard_id"]));
				$t -> set_var('customer_name',$this -> dbObj -> getone('select customer_name from '.WEB_ADMIN_TABPOX.'customer  where customer_id ='.$inrrs["customer_id"]));
				//$t -> set_var('category_name',$this -> dbObj -> getone('select category_name from '.WEB_ADMIN_TABPOX.'servicecategory  where category_id ='.$inrrs["categoryid"]));
			  	$t -> set_var('delete',$this -> getDelStr('',$inrrs['experiencecard_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['experiencecard_id']));		
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
			 
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	
 
	
	
	function goDispAppend(){

		$t = new Template('../template/card');
		$t -> set_file('f','experiencecard_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	
		$status_name=array("未激活","使用中","挂失","停用","报废");
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');
		if($this -> isAppend){
			
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		$Prefix='TY';
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'experiencecard';
		$column='experiencecard_no';
		$number=5;
		$id='experiencecard_id';	
		$t -> set_var('experiencecard_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));				
		$t -> set_var('overdate',"");		
		$t -> set_var('experiencecard_name',"");	
		$t -> set_var('marketingcard_id',"");	
		$t -> set_var('marketingcard_name',"");	
		$t -> set_var('customer_id',"");	
		$t -> set_var('buydate',date('Y-m-d'));	
		$t -> set_var('activedate',date('Y-m-d'));	
		$t -> set_var('customer_name',"");				
		$t -> set_var('error',"");	
		$t -> set_var('coderule',"");
		$t -> set_var('timelimit',"");
		$t -> set_var('pricepertime',"");
		$t -> set_var('totaltimes',"");	
		$t -> set_var('commission',"");	
		$t -> set_var('ucommission',"");	
		$t -> set_var('price',"");	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");
		$t -> set_var('comdisplay',"none");		
		$t -> set_var('memo',"");

		$t -> set_var('statusnamelist',$this->statuslist($status_name));
		$t -> set_var('recordcount',"");
		
		$t -> set_var('cservices_no',"");	
		$t -> set_var('cservices_name',"");	
		$t -> set_var('cpricepertime',"");	
		$t -> set_var('cservices_id',"");	
		$t -> set_var('remaintimeslist',"");
		$t -> set_var('remaintimes',"");	
		$t -> set_var('cservices_times',"");	
		$t -> set_var('cprice',"");	
		$t -> set_var('cmemo',"");	
		$t -> set_var('ccategory_name','');		
		$t -> set_var('ccategory_id','');			
		$t -> set_var('ml');
		$t -> set_var('userid',$this->getUid());		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$date=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'experiencecard WHERE experiencecard_id = '.$updid.' and agencyid ='.$_SESSION["currentorgan"]);
			$marketingcard_name=$this->dbObj->GetOne('SELECT marketingcard_name FROM '.WEB_ADMIN_TABPOX.'marketingcard WHERE marketingcard_id = '.$date["marketingcard_id"].' and agencyid ='.$_SESSION["currentorgan"]);
			$t -> set_var('marketingcard_name',$marketingcard_name);
			$customer_name=$this->dbObj->GetOne('SELECT customer_name FROM '.WEB_ADMIN_TABPOX.'customer WHERE customer_id = '.$date["customer_id"].' and agencyid ='.$_SESSION["currentorgan"]);
			$t -> set_var('customer_name',$customer_name);
			$t -> set_var('statusnamelist',$this->statuslist($status_name,$date["status"]));
			if($date['assignservice']==1){
			$t -> set_var('assignservice1','checked="checked"') ;
			$t -> set_var('assignservice0','') ;
			}else{
			$t -> set_var('assignservice0','checked="checked"') ;
			$t -> set_var('assignservice1','') ;
			}
			$t -> set_var('updid',$updid);
			
			$t -> set_var('action','upd');			
			$t -> set_var($date);			
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$t -> set_var('comdisplay',"");	
		$t -> set_var('cservices_no',"");	
		$t -> set_var('cservices_name',"");	
		$t -> set_var('ccode',"");	
		$t -> set_var('cservices_id',"");	
		$t -> set_var('cstandardunit',"");	
		$t -> set_var('lowerlimit',"");	
		$t -> set_var('cprice',"");	
		$t -> set_var('cmemo',"");	
		$t -> set_var('cpricepertime',"");
		$t -> set_var('cservices_times',"");	
		$t -> set_var('cviceunit',"");	
		$t -> set_var('conupdid',"");	
		
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
				
		$t -> set_var('ml');	
		$item_id=explode("||",$date['itemlist']);
	
		$remaintimes=explode("||",$date['remaintimeslist']);
		$t -> set_var('recordcount',count($remaintimes));
		for ($i=0;$i<count($remaintimes);$i++)	{
			$t -> set_var('remaintimes',$remaintimes[$i]);
			$servicesdata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'services WHERE services_id = '.$item_id[$i].' and agencyid ='.$_SESSION["currentorgan"]);		
			
			$category_name=$this->dbObj->GetRow('SELECT category_name FROM '.WEB_ADMIN_TABPOX.'servicecategory  WHERE category_id  = '.$servicesdata['categoryid'].' and agencyid ='.$_SESSION["currentorgan"]);
			$services_times=$this->dbObj->GetOne('SELECT services_times FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE  services_id = '.$item_id[$i].' and marketingcard_id='.$date["marketingcard_id"].' and agencyid ='.$_SESSION["currentorgan"]);
			
			$t -> set_var($category_name);
			$t -> set_var($servicesdata);
			$t -> set_var('services_times',$services_times);
			$t -> parse('ml','mainlist',true);
		}
		$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$date['categoryid']));	
		$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$date['standardunit']));	
		$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$date['viceunit']));	

		}
		$t -> set_var('categorynamelist',$this ->selectlist('servicecategory','category_id','category_name',$date['categoryid']));		
		
		
		

		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
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
	  if($_GET['consumables_id']==''){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'experiencecard WHERE experiencecard_id in('.$delid.')');
		}else{
		echo 'DELETE FROM '.WEB_ADMIN_TABPOX.'experiencecarddetail WHERE experiencecarddetail_id in('.$_GET['consumables_id'].')';
		 $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'experiencecarddetail WHERE experiencecarddetail_id in('.$_GET['consumables_id'].')');
		
		}
		$info='删除';
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}
	function goAppend(){
		$experiencecardtype_id= "1";
		$id = 0;
		$info = '';
		$this->	CardObj=new card();
		if($this -> isAppend){
			$info = '增加';	

			
			$card_no=$_POST['experiencecard_no'];
			$marketingcard_id=$_POST['marketingcard_id'];
			$customerid=$_POST['customer_id'];
			$employeeid='0';
			$agencyid=$_SESSION["currentorgan"];
			$cardtable='experiencecard';
			echo $card_no.$marketingcard_id.$customerid.$employeeid.$agencyid.$cardtable;
			$id = $this-> CardObj->creatcard($card_no,$marketingcard_id,$customerid,$employeeid,$agencyid,$cardtable);
			

			exit("<script>alert('新增成功');location.href='experiencecard.php?action=upd&updid=".$id."';</script>");	
		}else{
			$info = '修改';
			
			$id = $_POST[MODIFY.'id'];
			$card_no=$_POST['experiencecard_no'];
			$marketingcard_id=$_POST['marketingcard_id'];
			$customerid=$_POST['customer_id'];
			$employeeid='0';
			$agencyid=$_SESSION["currentorgan"];
			$status=$_POST["status"];
			$activedate=$_POST["activedate"];
			$buydate=$_POST["buydate"];
			$activedate=$_POST["activedate"];
			$card_id=$id ;
			$cardtable='experiencecard';
			$overdate=$_POST["overdate"];
			$this-> CardObj->updatecard($card_id,$card_no,$marketingcard_id,$customerid,$employeeid,$agencyid,$status,$activedate,$buydate,$cardtable,$overdate);
				 
		}
//$this -> quit($info.'成功！');
 
		$this -> quit($info.'成功！');

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
  