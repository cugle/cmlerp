<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/sell.cls.php');
class Pageproduce extends admin {
    function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='step2'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> step2();			
		}else if(isset($_GET['action']) && $_GET['action']=='step3'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> step3();			
		}else if(isset($_GET['action']) && $_GET['action']=='step2_1'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> step2_1();			
		}else if(isset($_GET['action']) && $_GET['action']=='step2_2'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> step2_2();			
		}else if(isset($_GET['action']) && $_GET['action']=='step4'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> step4();			
		}else{
            parent::Main();
        }
    }	
	function disp(){
		//定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','consume1.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
  		$customer_id=$_SESSION["currentcustomerid"];
		if($customer_id==0 or $customer_id==''){
		exit("<script>alert('请刷会员卡');window.close();</script>");
		}
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "%'.$keywords.'%"';}else{$condition=$category.' like " %'.$keywords.'%"';}
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
		$sql='select * from '.WEB_ADMIN_TABPOX.'card  where  customer_id='.$customer_id.' and agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;
		$sql2='select * from '.WEB_ADMIN_TABPOX.'itemcard where customer_id='.$customer_id.' and agencyid ='.$_SESSION["currentorgan"].' and '.$condition;
	   $sql3='select * from '.WEB_ADMIN_TABPOX.'treatmentcard where customer_id='.$customer_id.' and agencyid ='.$_SESSION["currentorgan"].' and '.$condition;
	 $sql4='select * from '.WEB_ADMIN_TABPOX.'membershipcard where customer_id='.$customer_id.' and agencyid ='.$_SESSION["currentorgan"].' and '.$condition;
	 $sql5='select * from '.WEB_ADMIN_TABPOX.'experiencecard where customer_id='.$customer_id.' and agencyid ='.$_SESSION["currentorgan"].' and '.$condition;
	 $sql6='select * from '.WEB_ADMIN_TABPOX.'feelingcard where customer_id='.$customer_id.' and agencyid ='.$_SESSION["currentorgan"].' and '.$condition;
			 $sql=$sql." union ".$sql2." union ".$sql3." union ".$sql4." union ".$sql5." union ".$sql6;
			

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'card p INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on p.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  p.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'card  where customer_id='.$customer_id.' and status=1 and (timelimit=0 or overdate>"'.date('Y-m-d').'")' ;
			 $sql2='select * from '.WEB_ADMIN_TABPOX.'itemcard  where customer_id='.$customer_id.' and status=1 and (timelimit=0 or overdate>"'.date('Y-m-d').'")' ;
			 $sql3='select * from '.WEB_ADMIN_TABPOX.'treatmentcard  where customer_id='.$customer_id.' and status=1 and (timelimit=0 or overdate>"'.date('Y-m-d').'")' ;
			 $sql4='select * from '.WEB_ADMIN_TABPOX.'membershipcard  where customer_id='.$customer_id.' and status=1 and (timelimit=0 or overdate>"'.date('Y-m-d').'")' ;
			 $sql5='select * from '.WEB_ADMIN_TABPOX.'experiencecard  where customer_id='.$customer_id.' and status=1 and (timelimit=0 or overdate>"'.date('Y-m-d').'")' ;
			 $sql6='select * from '.WEB_ADMIN_TABPOX.'feelingcard  where customer_id='.$customer_id.' and status=1 and (timelimit=0 or overdate>"'.date('Y-m-d').'")' ;
			// $sql7='select * from '.WEB_ADMIN_TABPOX.'cashcoupon  where customer_id='.$customer_id;
			 $sql=$sql." union ".$sql2." union ".$sql3." union ".$sql4." union ".$sql5." union ".$sql6;
			}
		
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  card_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
           	$t -> set_var('recordcount',$count);
						
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				//$t -> set_var('gender',$inrrs["genderid"]==1?'男':'女');
				$t -> set_var('marketingcard_name',$this -> dbObj -> getone('select marketingcard_name from '.WEB_ADMIN_TABPOX.'marketingcard  where marketingcard_id ='.$inrrs["marketingcard_id"]));
				$marketingcardtypedata=$this -> dbObj -> getrow('select A.* from '.WEB_ADMIN_TABPOX.'marketingcardtype A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id where B.marketingcard_id ='.$inrrs["marketingcard_id"]);
				$t -> set_var('marketingcardtype_name',$marketingcardtypedata['marketingcardtype_name']);
				$t -> set_var('marketingcardtype_id',$marketingcardtypedata['marketingcardtype_id']);
				
				//echo 'select A.marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id where B.marketingcard_id ='.$inrrs["marketingcard_id"];
				$t -> set_var('category_name',$this -> dbObj -> getone('select category_name from '.WEB_ADMIN_TABPOX.'procatalog where category_id ='.$inrrs["categoryid"]));
				$t -> set_var('usetimes',$inrrs['totaltimes']-$inrrs['remaintimes']);
				$t -> set_var('marketingcar_id',$inrrs["marketingcard_id"]);
				
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();
			if($customer_id==0){
				$customer_name='散客';
			}else{
			$customer_name=$this->dbObj->GetOne('SELECT customer_name FROM '.WEB_ADMIN_TABPOX.'customer WHERE customer_id = '.$customer_id);
			}
			$t->set_var('customer_name',$customer_name);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function step2_1(){
		
		$producedata=$_GET['producedata'];
		$producedata = explode('@@@',$producedata);
		$card_id=$producedata[0];
		$produce_no=$producedata[1];
		$produce_name=$producedata[2];
		$itemlist=$producedata[3];
		$remaintimeslist=$producedata[4];
		$marketingcard_id=$producedata[5];
		$agencyid=$_SESSION["customerorgan"]?$_SESSION["customerorgan"]:$_SESSION["currentorgan"];
		$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");
		//echo 'SELECT marketingcardtype_id FROM '.WEB_ADMIN_TABPOX.'marketingcard WHERE marketingcard_id = '.$marketingcard_id;
		$marketingcardtype_id=$this->dbObj->GetOne('SELECT marketingcardtype_id FROM '.WEB_ADMIN_TABPOX.'marketingcard WHERE marketingcard_id = '.$marketingcard_id);
	$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.$cardtable_name[$marketingcardtype_id].' WHERE '.$cardtable_name[$marketingcardtype_id].'_id  = '.$card_id.' and agencyid ='.$agencyid);
	 //echo  'SELECT * FROM '.WEB_ADMIN_TABPOX.$cardtable_name[$marketingcardtype_id].' WHERE '.$cardtable_name[$marketingcardtype_id].'_id  = '.$card_id.' and agencyid ='.$agencyid;
	    if($data['assignservice']==1 ){
		  
			$this->step2();
		}else if($data['assignservice']==0){
		$this->step2_0();
		}else if($data['assignservice']==2){
		
		//定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','consume2_1.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
		$producedata=$_GET['producedata'];
		$producedata = explode('@@@',$producedata);
		$card_id=$producedata[0];
		$produce_no=$producedata[1];
		$produce_name=$producedata[2];
		$itemlist=$producedata[3];
		$remaintimeslist=$producedata[4];
		$marketingcard_id=$producedata[5];
		$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'card WHERE card_id = '.$card_id.' and agencyid ='.$agencyid);
		//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'card WHERE card_id = '.card_id.' and agencyid ='.$agencyid;
		$t -> set_var('ml');	
		$item_id=explode("||",$itemlist);
		$type=array("A","B","C","D","E");
		$remaintimes=explode("||",$remaintimeslist);
		
		$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");	
		//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcard WHERE marketingcard_id = '.$marketingcard_id;
		$marketingcarddata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcard WHERE marketingcard_id = '.$marketingcard_id);
		$carddata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.$cardtable_name[$marketingcarddata["marketingcardtype_id"]].' WHERE '.$cardtable_name[$marketingcarddata["marketingcardtype_id"]].'_id = '.$card_id);
		
		$t -> set_var('recordcount',count($remaintimes));
		for ($i=0;$i<count($remaintimes);$i++)	{
			$t -> set_var('remaintimes',$remaintimes[$i]);
			
			if($remaintimes[$i]>0){
			$t -> set_var('disabled','');
			}else{
			$t -> set_var('disabled','disabled'); 
			 
			}
		if($carddata['pricepertimelist']){$pricepertimelist=explode("||",$carddata['pricepertimelist']);}	
			$servicesdata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'services WHERE services_id = '.$item_id[$i].' and agencyid ='.$agencyid);		
			//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'services WHERE services_id = '.$item_id[$i].' and agencyid ='.$_SESSION["currentorgan"];
			//$category_name=$this->dbObj->GetRow('SELECT category_name FROM '.WEB_ADMIN_TABPOX.'servicecategory  WHERE category_id  = '.$servicesdata['categoryid'].' and agencyid ='.$_SESSION["currentorgan"]);
			
				$data2=$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'servicecategory  where category_id  in('.$item_id[$i].')');
				
				$category_name='';
				while ($inrrs2 = $data2 -> FetchRow()) {
					$category_name=$category_name==''?$inrrs2['category_name']:$category_name.','.$inrrs2['category_name'];
					 
				}
	
				//$t -> set_var($inrrs2);
				 
				$t -> set_var('category_name',$category_name);			
			
			
			if($data['assignservice']==1){
			 	
			$services_times=$this->dbObj->GetOne('SELECT services_times FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE  services_id = '.$item_id[$i].' and marketingcard_id='.$date["marketingcard_id"].' and agencyid ='.$agencyid);
			}else if($data['assignservice']==2){
			 
			$services_times=$this->dbObj->GetOne('SELECT services_times FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE  servicecategory_id  = '.$item_id[$i].' and marketingcard_id='.$date["marketingcard_id"].' and agencyid ='.$agencyid);
			
			}
			$t -> set_var('services_no',$item_id[$i]);
			$t -> set_var('category_id',$item_id[$i]);
			$t -> set_var($servicesdata);
			$t -> set_var('services_name',$servicesdata['services_name']);
			$t -> set_var('services_times',$services_times);
			$t -> set_var('marketingcard_id',$marketingcard_id);
			if($pricepertimelist[$i]==''){
			$t -> set_var('value',$this->dbObj->GetOne('SELECT pricepertime FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE servicecategory_id = "'.$item_id[$i].'" and marketingcard_id ='.$marketingcard_id.' and  agencyid ='.$agencyid));
			}else{	
				$t -> set_var('value',$pricepertimelist[$i]);
			}
			// echo 'SELECT pricepertime FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE servicecategory_id ="'.$item_id[$i].'" and marketingcard_id ='.$marketingcard_id.' and  agencyid ='.$agencyid;
			$t -> set_var('card_id',$card_id);
			

			$t -> parse('ml','mainlist',true);
		}
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
		
		}
	}
	function step2_2(){
		//定义模板

		$t = new Template('../template/pos');
		$t -> set_file('f','consume2.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
		$producedata=$_GET['producedata'];
		$agencyid=$_SESSION["customerorgan"]?$_SESSION["customerorgan"]:$_SESSION["currentorgan"];
		$producedata = explode('@@@',$producedata);
		$category_id=$producedata[0];
		$card_id=$producedata[1];
		//$produce_name=$producedata[2];
		//$itemlist=$producedata[3];
		$value=$producedata[4];
		$marketingcard_id=$producedata[5];
		//$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'card WHERE card_id = '.$card_id.' and agencyid ='.$_SESSION["currentorgan"]);
		//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'card WHERE category_id = '.$category_id.' and agencyid ='.$_SESSION["currentorgan"];
		$t -> set_var('ml');	
		$item_id=explode("||",$itemlist);
		$type=array("A","B","C","D","E");
		$remaintimes=explode("||",$remaintimeslist);
		//搜索
	 	 $category=$_GET['category'];
		 $category=$category<>''?$category:$_POST['howdofindq'][0];
		 if($category=='serivces_name'){$category='services_name';}//编码问题，勿删，勿改。
		 if($category=='﻿services_no'){$category='services_no';}
		 $keywords=$_GET['keywords'];
		 $keywords=$keywords<>''?$keywords:$_POST['findwhat'][0]; 
		 
		 $url="action=".$_GET['action']."&marketingcard_id=".$_GET['marketingcard_id']."&producedata=".$_GET['producedata'];
		 $t -> set_var('url',$url);
		 if($keywords<>''){
			 $condition= ' and '.$category.' like "%'. $keywords.'%"';

 			 $servicesdata=$this->dbObj->Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'services WHERE   agencyid ='.$agencyid.$condition);
			  
		 } else{	
		
		$servicesdata=$this->dbObj->Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'services WHERE categoryid   in ('.$category_id.') and agencyid ='.$agencyid);
		 }
		$count=$servicesdata->RecordCount();
		$t -> set_var('recordcount',$count);
		//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'services WHERE categoryid  in ('.$category_id.') and agencyid ='.$_SESSION["currentorgan"];
		while ($inrrs3 = $servicesdata -> FetchRow()) {
			//$t -> set_var('remaintimes',$remaintimes[$i]);
			//echo "test";
				
			//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'services WHERE services_id = '.$item_id[$i].' and agencyid ='.$_SESSION["currentorgan"];
			//$category_name=$this->dbObj->GetRow('SELECT category_name FROM '.WEB_ADMIN_TABPOX.'servicecategory  WHERE category_id  = '.$servicesdata['categoryid'].' and agencyid ='.$_SESSION["currentorgan"]);
			
				$t -> set_var('category_name',$this -> dbObj -> GetOne('select category_name  from '.WEB_ADMIN_TABPOX.'servicecategory  where category_id  ='.$inrrs3['categoryid']));
				
			$t -> set_var('card_id',$card_id);	 
 
			$t -> set_var('remaintimes','');
			$t -> set_var($inrrs3);
 
			//$t -> set_var('marketingcard_id',$marketingcard_id);
			
			//$t -> set_var('value',$this->dbObj->GetOne('SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE services_id = '.$item_id[$i].' and marketingcard_id ='.$marketingcard_id.' and  agencyid ='.$_SESSION["currentorgan"]));
			//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE services_id = '.$item_id[$i].' and marketingcard_id ='.$marketingcard_id.' and  agencyid ='.$_SESSION["currentorgan"];
			
			

			$t -> parse('ml','mainlist',true);
		}
		$marketingcardtype=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcardtype A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id WHERE   marketingcard_id ='.$marketingcard_id.' and  B.agencyid ='.$agencyid);
		 $t -> set_var('value',$value);
		 
		$t -> set_var('marketingcardtype_id',$marketingcardtype['marketingcardtype_id']);
		$t -> set_var('marketingcard_id',$marketingcard_id);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function step2(){
		//定义模板
		
		$t = new Template('../template/pos');
		$t -> set_file('f','consume2.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
		$producedata=$_GET['producedata'];
		$producedata = explode('@@@',$producedata);
		$card_id=$producedata[0];
		 
		$produce_no=$producedata[1];
		$produce_name=$producedata[2];
		$itemlist=$producedata[3];
		$remaintimeslist=$producedata[4];
		$marketingcard_id=$producedata[5];
		$customer_id=$_SESSION["currentcustomerid"]?$_SESSION["currentcustomerid"]:0;
		$agencyid=$_SESSION["customerorgan"]?$_SESSION["customerorgan"]:$_SESSION["currentorgan"];
		$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");	
		//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcard WHERE marketingcard_id = '.$marketingcard_id;
		$marketingcarddata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcard WHERE marketingcard_id = '.$marketingcard_id);
		$carddata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.$cardtable_name[$marketingcarddata["marketingcardtype_id"]].' WHERE '.$cardtable_name[$marketingcarddata["marketingcardtype_id"]].'_id = '.$card_id);

	 	 $category=$_GET['category'];
		 $category=$category<>''?$category:$_POST['howdofindq'][0];
		 if($category=='serivces_name'){$category='services_name';}//编码问题，勿删，勿改。
		 if($category=='﻿services_no'){$category='services_no';}
		 $keywords=$_GET['keywords'];
		 $keywords=$keywords<>''?$keywords:$_POST['findwhat'][0]; 
		 $condition= ' and '.$category.' like "%'. $keywords.'%"';
		 $url="action=".$_GET['action']."&marketingcard_id=".$_GET['marketingcard_id']."&producedata=".$_GET['producedata'];
		 $t -> set_var('url',$url);
		 
		 	 
			 $sql='select * from '.WEB_ADMIN_TABPOX.'card  where card_id = '.$card_id;
			 $sql2='select * from '.WEB_ADMIN_TABPOX.'itemcard  where itemcard_id = '.$card_id;
			 $sql3='select * from '.WEB_ADMIN_TABPOX.'treatmentcard  where treatmentcard_id = '.$card_id;
			 $sql4='select * from '.WEB_ADMIN_TABPOX.'membershipcard  where membershipcard_id = '.$card_id;
			 $sql5='select * from '.WEB_ADMIN_TABPOX.'experiencecard  where experiencecard_id = '.$card_id;
			 $sql6='select * from '.WEB_ADMIN_TABPOX.'feelingcard  where feelingcard_id = '.$card_id;
			// $sql=$sql." union ".$sql2." union ".$sql3." union ".$sql4." union ".$sql5." union ".$sql6;
			$sql='select * from '.WEB_ADMIN_TABPOX.$cardtable_name[$marketingcarddata["marketingcardtype_id"]].'  where '.$cardtable_name[$marketingcarddata["marketingcardtype_id"]].'_id = '.$card_id;
			
			 	$data=$this->dbObj->GetRow($sql);
		$t -> set_var('ml');	
		$item_id=explode("||",$itemlist);
		$type=array("A","B","C","D","E");
		$remaintimes=explode("||",$remaintimeslist);
		$t -> set_var('recordcount',count($remaintimes));
		for ($i=0;$i<count($remaintimes);$i++)	{
			$t -> set_var('remaintimes',$remaintimes[$i]);
			if($remaintimes[$i]>0){
			$t -> set_var('disabled','');
			}else{
			$t -> set_var('disabled','disabled'); 
			}
		//搜索
		
		 if($carddata['pricepertimelist']<>NULL){$pricepertimelist=explode("||",$carddata['pricepertimelist']);}
 
		
		 if($keywords<>''){
 		 $servicesdata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'services WHERE services_id = '.$item_id[$i].' and agencyid ='.$agencyid.$condition);
		if($servicesdata){ 
				$data2=$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'servicecategory  where category_id  in('.$item_id[$i].')');
				
				$category_name='';
				while ($inrrs2 = $data2 -> FetchRow()) {
					$category_name=$category_name==''?$inrrs2['category_name']:$category_name.','.$inrrs2['category_name'];
					 
				}
				
				
				$t -> set_var('category_name',$category_name);			
			
			$t -> set_var($servicesdata);
			 
				//echo 'SELECT value FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE services_id = '.$item_id[$i].' and marketingcard_id ='.$marketingcard_id.' and  agencyid ='.$agencyid;
				
				if($pricepertimelist[$i]==''){
				
				$t -> set_var('value',$this->dbObj->GetOne('SELECT pricepertime FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE services_id = '.$item_id[$i].' and marketingcard_id ='.$marketingcard_id.' and  agencyid ='.$agencyid)); 	
				}else{
					
				$t -> set_var('value',$pricepertimelist[$i]);
				}
			$services_times=$this->dbObj->GetOne('SELECT services_times FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE  services_id = '.$item_id[$i].' and marketingcard_id='.$date["marketingcard_id"].' and agencyid ='.$agencyid);
			 
			//$t -> set_var('services_no',$item_id[$i]);
			//$t -> set_var($category_name);
			
			$t -> set_var('services_name',$servicesdata['services_name']);
			
			$t -> set_var('services_times',$services_times);
			$t -> set_var('marketingcard_id',$marketingcard_id);
			
			//$t -> set_var('value',$this->dbObj->GetOne('SELECT value FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE services_id = '.$item_id[$i].' and marketingcard_id ='.$marketingcard_id.' and  agencyid ='.$agencyid));
			//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE services_id = '.$item_id[$i].' and marketingcard_id ='.$marketingcard_id.' and  agencyid ='.$_SESSION["currentorgan"];
			
			$t -> set_var('card_id',$card_id);
 
			$t -> parse('ml','mainlist',true);	
		 }
		 } else{	


			$servicesdata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'services WHERE services_id = '.$item_id[$i].' and agencyid ='.$agencyid);	
			
			//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'services WHERE services_id = '.$item_id[$i].' and agencyid ='.$_SESSION["currentorgan"];
			//$category_name=$this->dbObj->GetRow('SELECT category_name FROM '.WEB_ADMIN_TABPOX.'servicecategory  WHERE category_id  = '.$servicesdata['categoryid'].' and agencyid ='.$_SESSION["currentorgan"]);
			
				$data2=$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'servicecategory  where category_id  in('.$item_id[$i].')');
				
				$category_name='';
				while ($inrrs2 = $data2 -> FetchRow()) {
					$category_name=$category_name==''?$inrrs2['category_name']:$category_name.','.$inrrs2['category_name'];
					 
				}
				
			
		
				//$t -> set_var($inrrs2);
				 
				$t -> set_var('category_name',$category_name);			
			
			$t -> set_var($servicesdata);
			 	echo "testds";
			if($data['assignservice']==1){
				//echo 'SELECT value FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE services_id = '.$item_id[$i].' and marketingcard_id ='.$marketingcard_id.' and  agencyid ='.$agencyid;
			
			if($pricepertimelist[$i]==''){
					 
				$t -> set_var('value',$this->dbObj->GetOne('SELECT pricepertime FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE services_id = '.$item_id[$i].' and marketingcard_id ='.$marketingcard_id.' and  agencyid ='.$agencyid)); 	
				}else{
					 
				$t -> set_var('value',$pricepertimelist[$i]);
				}	
			
			$services_times=$this->dbObj->GetOne('SELECT services_times FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE  services_id = '.$item_id[$i].' and marketingcard_id='.$date["marketingcard_id"].' and agencyid ='.$agencyid);
			}else if($data['assignservice']==2){
			$t -> set_var('value',$this->dbObj->GetOne('SELECT pricepertime FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE services_id = '.$item_id[$i].' and marketingcard_id ='.$marketingcard_id.' and  agencyid ='.$agencyid));
			
			$services_times=$this->dbObj->GetOne('SELECT services_times FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE  servicecategory_id  = '.$item_id[$i].' and marketingcard_id='.$date["marketingcard_id"].' and agencyid ='.$agencyid);
			}
			//$t -> set_var('services_no',$item_id[$i]);
			//$t -> set_var($category_name);
			
			$t -> set_var('services_name',$servicesdata['services_name']);
			$t -> set_var('services_times',$services_times);
			$t -> set_var('marketingcard_id',$marketingcard_id);
			
			//$t -> set_var('value',$this->dbObj->GetOne('SELECT value FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE services_id = '.$item_id[$i].' and marketingcard_id ='.$marketingcard_id.' and  agencyid ='.$agencyid));
			//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE services_id = '.$item_id[$i].' and marketingcard_id ='.$marketingcard_id.' and  agencyid ='.$_SESSION["currentorgan"];
			
			$t -> set_var('card_id',$card_id);
 
			$t -> parse('ml','mainlist',true);
		 }	
		}
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	
	function step2_0(){
		//定义模板
		  
		$t = new Template('../template/pos');
		$t -> set_file('f','consume2.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
		$producedata=$_GET['producedata'];
		$producedata = explode('@@@',$producedata);
		$card_id=$producedata[0];
		 
		$produce_no=$producedata[1];
		$produce_name=$producedata[2];
		$itemlist=$producedata[3];
		$remaintimeslist=$producedata[4];
		$marketingcard_id=$producedata[5];
		$customer_id=$_SESSION["currentcustomerid"]?$_SESSION["currentcustomerid"]:0;
		$agencyid=$_SESSION["customerorgan"]?$_SESSION["customerorgan"]:$_SESSION["currentorgan"];
		$cardtable_name=array('itemcard',"itemcard","treatmentcard","experiencecard","feelingcard","membershipcard","cashcoupon","storedvaluedcard","card");	
		//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcard WHERE marketingcard_id = '.$marketingcard_id;
		$marketingcarddata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcard WHERE marketingcard_id = '.$marketingcard_id);
		$carddata=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.$cardtable_name[$marketingcarddata["marketingcardtype_id"]].' WHERE '.$cardtable_name[$marketingcarddata["marketingcardtype_id"]].'_id = '.$card_id);
		$pricepertime=$carddata['pricepertime']?$carddata['pricepertime']:$marketingcarddata['pricepertime'];
	    $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.' like "%'.$keywords.'%"';}else{$condition=$category.' like " %'.$keywords.'%"';}
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'services  where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'services p INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on p.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  p.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'services  where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
		
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  services_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
           	$t -> set_var('recordcount',$count);
						
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);

				$t -> set_var('category_name',$this -> dbObj -> getone('select  category_name  from '.WEB_ADMIN_TABPOX.'servicecategory  where category_id ='.$inrrs["categoryid"]));
				//echo 'select  category_name  from '.WEB_ADMIN_TABPOX.'servicecategory  where category_id ='.$inrrs["categoryid"];
	
				$t -> set_var('value',$pricepertime);
				$t -> set_var('remaintimes','');
				$t -> set_var('marketingcard_id',$marketingcard_id);
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$t -> set_var('card_id',$card_id);	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}	
	function step3(){
		//定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','consume3.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
		$producedata=$_GET['producedata'];
		//echo $producedata;
		$producedata = explode('@@@',$producedata);
		$services_id=$producedata[0];
		$services_no=$producedata[1];
		$services_name=$producedata[2];
		
		$price=$producedata[3];
		$value=$producedata[4];
		$marketingcard_id=$producedata[5];
		$card_id=$producedata[6];
		//$date=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'card WHERE card_id = '.card_id.' and agencyid ='.$_SESSION["currentorgan"]);
		//$t -> set_var('ml');	
 		$agencyid=$_SESSION["customerorgan"]?$_SESSION["customerorgan"]:$_SESSION["currentorgan"];
		
		$marketingcardtype=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcardtype A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id WHERE   marketingcard_id ='.$marketingcard_id.' and  B.agencyid ='.$agencyid);
		
		//echo 'SELECT category_id FROM '.WEB_ADMIN_TABPOX.'services WHERE services_id='.$services_id;
		//echo  'SELECT servicecategory_id FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE marketingcard_id ='.$marketingcard_id;
		
		//echo 'SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcardtype A INNER JOIN '.WEB_ADMIN_TABPOX.'marketingcard B ON A.marketingcardtype_id=B.marketingcardtype_id WHERE   marketingcard_id ='.$marketingcard_id.' and  B.agencyid ='.$agencyid;
		$t -> set_var('marketingcardtype_id',$marketingcardtype['marketingcardtype_id']);
		$t -> set_var('marketingcard_id',$marketingcard_id);
		$t -> set_var('marketingcard_name',$marketingcardtype['marketingcard_name']);
		$t -> set_var('value',$value);
		$t -> set_var('number',1);
		$t -> set_var('beauty_id',$_SESSION["beauty_id"]);
		$t -> set_var('beauty_name',$_SESSION["beauty_name"]);

		$t -> set_var('employee_id',$_SESSION["consultant_id"]);
		$t -> set_var('employee_name',$_SESSION["consultant_name"]);
		$t -> set_var('services_id',$services_id);
		$t -> set_var('services_no',$services_no);
		$t -> set_var('services_name',$services_name);
		$t -> set_var('price',$price);
		$t -> set_var('card_id',$card_id);
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');

	}	
	function step4(){
		 $this->SellObj=new sell();
		 $employeeid=$this ->dbObj-> GetOne('select A.employee_id from '.WEB_ADMIN_TABPOX.'employee A INNER JOIN '.WEB_ADMIN_TABPOX.'user B ON A.employee_id=B.employee_id where B.userid='.$this->getUid());
		 //$employeeid=$_POST['employee_id'];
		 $sellidcount=$this ->dbObj-> GetOne('select count(*) from '.WEB_ADMIN_TABPOX.'sell  where sell_no="'.$_SESSION["sellno"].'"');
		// echo 'select count(*) from '.WEB_ADMIN_TABPOX.'sell  where sell_no='.$_SESSION["sellno"].'"';
		 if ($sellidcount==0){//如果没有新单号则插入新单号
		 $customerid=$_SESSION["currentcustomerid"]?$_SESSION["currentcustomerid"]:0;
		 $sellid=$this->SellObj->creatsellno($_SESSION["sellno"],$customerid,$employeeid,$_SESSION["currentorgan"],$cardtable='sell');
		 
		 $_SESSION["sellid"]=$sellid;
		 }else{
		 $sellid=$_SESSION["sellid"];
		 }
		 $item_type=2;
		 $item_id=$_POST['services_id'];
		 $number=$_POST['number'];
		 $value=$_POST['value'];
		 $price=$_POST['price'];
		  
		 if($_POST['givingbeauty']=='on'){
			
			 $discount=0;
			    //echo  $discount;
			 }else{
		     $discount=10;
		
		 }
		 $beauty_id=$_POST['beauty_id'];
		 $customercardid=$_POST['card_id'];
		  
		 $cardtable='sellconsumedetail';
		 $cardtype=$_POST['marketingcardtype_id'];
		 $cardid=$_POST['marketingcard_id'];
		 $employee_id=$_POST['employee_id'];
		 $_SESSION["beauty_id"]=$_POST['beauty_id'];
		 $_SESSION["beauty_name"]=$_POST['beauty_name']; 
		 $_SESSION["consultant_id"]=$_POST['employee_id'];
		 $_SESSION["consultant_name"]=$_POST['employee_name'];
		 
		 
		 $id = $this->SellObj->addsellitem($sellid,$item_type,$item_id,$number,$value,$price,$discount,$beauty_id,$cardtable,$cardtype,$cardid,$customercardid,$employee_id);
		// $this -> dbObj -> Execute('INSERT INTO  '.WEB_ADMIN_TABPOX.'sell WHERE membershipcard_id in('.$delid.')');
		
		 
		 //定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','consume4.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
		

		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function step5(){
		
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
		 $value=$_POST['price'];
		 $price=$_POST['price'];
		 if($_POST['givingbeauty']=='on'){
			
			 $discount=0;
			  //  echo  $discount;
			 }else{
		     $discount=10;
		
		 }
		 $beauty_id=0;
		 $cardtable='sellconsumedetail';
		 $id = $this->SellObj->addsellitem($sellid,$item_type=1,$item_id,$number,$value,$price,$discount,$beauty_id,$cardtable='selldetail');
		// $this -> dbObj -> Execute('INSERT INTO  '.WEB_ADMIN_TABPOX.'sell WHERE membershipcard_id in('.$delid.')');
		
		 
		 //定义模板
		$t = new Template('../template/pos');
		$t -> set_file('f','buyproduce3.html');
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
  