<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/sell.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/price.cls.php');
class Pageproduce extends admin {
    function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='step2'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> step2();			
		}else if(isset($_GET['action']) && $_GET['action']=='step3'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> step3();			
		}else{
            parent::Main();
        }
    }	
	function disp(){
		//定义模板
 		 
		$t = new Template('../template/pos');
		$t -> set_file('f','givingproduce1.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
   
        $category='B.'.$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.'="'.$keywords.'"';}else{$condition=$category.'="'.$keywords.'"';}
		}
		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:20;
			$offset = $pageid>0?($pageid-1)*$psize:0;

			//设置分类
			$agencyid=$_SESSION["currentorgan"];
			$t -> set_var('ml');	
		$warehouseid=$this -> dbObj -> getone('select warehouse_id from '.WEB_ADMIN_TABPOX.'warehouse where type=0 and agencyid ='.$agencyid);
		 
			if($condition<>''&&$ftable==''){
			$sql='SELECT A.*,B.*  from '.WEB_ADMIN_TABPOX.'stock A INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON A.produce_id=B.produce_id where  A.agencyid ='.$_SESSION["currentorgan"].' and A.warehouse_id='.$warehouseid.' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'stock s INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on s.produce_id =f.produce_id  where f.".$category." like '%".$keywords."%' and  s.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select A.*,B.*  from '.WEB_ADMIN_TABPOX.'stock A INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON A.produce_id=B.produce_id  where  A.agencyid ='.$_SESSION["currentorgan"].' and A.warehouse_id='.$warehouseid;
			 
			}
			 
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  number DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
           	$t -> set_var('recordcount',$count);
						
	     	while ($inrrs = &$inrs -> FetchRow()) {
				
				
				$t -> set_var('warehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse where warehouse_id ='.$inrrs["warehouse_id"]));
				$produce=$this -> dbObj -> Getrow('select * from '.WEB_ADMIN_TABPOX.'produce where produce_id ='.$inrrs["produce_id"]);
				$t -> set_var($produce);
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['stock_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['stock_id']));				
				$t -> set_var($inrrs);
				
				
				$t -> set_var('brand_name',$this -> dbObj -> getone('select brand_name from '.WEB_ADMIN_TABPOX.'brand  where brand_id ='.$produce["brandid"]));
				$t -> set_var('standardunit',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$produce["standardunit"]));
				$t -> set_var('standardunitid',$produce["standardunit"]);
				$t -> set_var('category_name',$this -> dbObj -> getone('select category_name from '.WEB_ADMIN_TABPOX.'procatalog where category_id ='.$produce["categoryid"]));
				
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function step2(){
		//定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','givingproduce2.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
		$producedata=$_GET['producedata'];
		$producedata = explode('@@@',$producedata);
		$produce_id=$producedata[0];
		$produce_no=$producedata[1];
		$produce_name=$producedata[2];
		$code=$producedata[3];
		$price=$producedata[4];
		$standardunit=$producedata[5];
		$t -> set_var('produce_id',$produce_id);
		$t -> set_var('produce_no',$produce_no);
		$t -> set_var('produce_name',$produce_name);
		$t -> set_var('code',$code);
		$t -> set_var('price',$price);
		$t -> set_var('standardunit',$this -> dbObj -> getone('select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$standardunit));
		//echo  'select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$standardunit;
		$t -> set_var('employee_id','');
		$t -> set_var('employee_name','');
		$t -> set_var('beauty_id','');
		$t -> set_var('beauty_name','');
		
		$t -> set_var('number',1);
		$t -> set_var('action','step3');
		 $this->PriceObj=new price();
		 $value=$this->PriceObj->produceprice($_SESSION["currentcustomerid"],$produce_id,$_SESSION["currentorgan"]);	
		 $t -> set_var('value',0);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function step3(){
		
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
		 
		 $item_id=$_POST['produce_id'];
		 $number=$_POST['number'];
		 $value=$_POST['value'];
		 $price=$_POST['price'];
		 $discount=0;
		 $beauty_id=$_POST['beauty_id'];
		 $cardtable='selldetail';
		 $employee_id=$_POST['employee_id'];
		  
		 $id = $this->SellObj->addsellitem($sellid,$item_type=1,$item_id,$number,$value,$price,$discount,$beauty_id,$cardtable,0,0,0,$employee_id);
		//最后两个0 分别是赠送仓库类型 ，自动指定仓库，
		// $this -> dbObj -> Execute('INSERT INTO  '.WEB_ADMIN_TABPOX.'sell WHERE membershipcard_id in('.$delid.')');
		
		 
		 //定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','givingproduce3.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
		

		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}


	function quit($info){
		exit("<script>alert('$info');location.href='produce.php';</script>");
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
$main = new Pageproduce();
$main -> Main();
?>
  