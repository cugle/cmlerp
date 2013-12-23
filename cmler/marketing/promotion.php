<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
	
    function Main(){   
	 if(isset($_GET['action']) && $_GET['action']=='submittoaudit'){
			$this -> submittoaudit();
		}else{
            parent::Main();
       }
    }	
	function submittoaudit(){
	$this -> dbObj -> Execute("START TRANSACTION");//事务开始。
	$status=$this -> dbObj -> GetOne("select status from `".WEB_ADMIN_TABPOX."promotion` where promotion_id=".$_GET['updid']);
	$res=$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."promotion` SET `status` = 4 where promotion_id=".$_GET['updid']);	
		if($res&&$status==1){
		$this -> dbObj -> Execute("COMMIT");
		exit("<script>alert('提交成功');window.location.href='promotion.php';</script>");
		}else{
		$this -> dbObj -> Execute("ROLLBACK");
		
		$this -> quit('提交失败！');	
		} 
	
	
	}
	function disp(){
		//定义模板
		$promotiontype_id= "8";
		$t = new Template('../template/marketing');
		$t -> set_file('f','promotion.html');
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
			$status_name=array("<font color=blue>未完成</font>","已完成","<font color=red>作废</font>","<font color=red>被红字冲销</font>","<font color=green>已提交</font>","<font color=#AAAAAA>已审核</font>","<font color=#AAAAAA>未通过审核</font>");
			 
			
		
			//设置分类
			$t -> set_var('ml');

			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'promotion  where promotiontype_id ='.$promotiontype_id.' and  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql="select * from ".WEB_ADMIN_TABPOX."promotion s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and s.promotiontype_id ='.$promotiontype_id.'  and  s.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'promotion  where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  promotion_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));			
	    	$t -> set_var('recordcount',$count);	
			
			
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('status_name',$status_name[$inrrs['status']]);	

			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['promotion_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['promotion_id']));		
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

		$t = new Template('../template/marketing');
		$t -> set_file('f','promotion_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','ser_mainlist','sml');	
		$t -> set_block('f','cat_mainlist','cml');	
        //$t -> set_block('f','consumables','cs');	
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		$t -> set_var('promotion_no',"");	
		$t -> set_var('promotion_name',"");	
		$t -> set_var('error',"");	
		$Prefix='CX';
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'promotion';
		$column='promotion_no';
		$number=5;
		$id='itemcard_id';	
		$t -> set_var('promotion_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));			
		
		$t -> set_var('bgdate',"");
		$t -> set_var('enddate',"");
		$t -> set_var('totaltimes',"");	
		$t -> set_var('commission',"");	
		$t -> set_var('ucommission',"");	
		$t -> set_var('price',"");	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");
		$t -> set_var('servicetabledisplay',"none");	
		$t -> set_var('servicelisttabledisplay',"none");	
		$t -> set_var('categorylisttabledisplay',"none");	
		$t -> set_var('categorytalbedisplay',"none");	
			$t -> set_var('assignservice0','checked="checked"') ;
			$t -> set_var('assignservice0','') ;
			$t -> set_var('assignservice1','') ; 	
		$t -> set_var('memo',"");
		
		
			$t -> set_var('dproprice','0') ;
			$t -> set_var('ddiscount','') ;
			$t -> set_var('assignservice1','checked="checked"') ;
			$t -> set_var('ditem_name','') ;
			$t -> set_var('ditem_id','') ;
			$t -> set_var('dtype','0') ;
			$t -> set_var('sertype','0') ;
		    $t -> set_var('dmemo',"");	
		$t -> set_var('ser_discount',"");	
		$t -> set_var('ser_proprice',"");	
		$t -> set_var('ser_memo',"");	
		$t -> set_var('ser_no',"");	
		$t -> set_var('ser_name',"");	
		$t -> set_var('ser_id',"");	
		$t -> set_var('ser_services_times',"");	
		$t -> set_var('ser_recordcount',"");
	
		$t -> set_var('cat_action',"add");	
		$t -> set_var('c_updid',"");	
		$t -> set_var('cat_updid',"");	
		$t -> set_var('ser_action',"add");	
		$t -> set_var('s_updid',"");	
		$t -> set_var('ser_updid',"");		


			$discounttypechecked0=array("checked","");
			$discounttypechecked1=array("","checked");
			$dpropricedisplay0=array("","none");
			$dpropricedisplay1=array("none","");
			$t -> set_var('discounttypechecked0',$discounttypechecked0[1]);
			$t -> set_var('discounttypechecked1',$discounttypechecked1[1]);
			$t -> set_var('dpropricedisplay0',$dpropricedisplay0[1]);
			$t -> set_var('dpropricedisplay1',$dpropricedisplay1[1]);
			
			$t -> set_var('serdiscounttypechecked0',$discounttypechecked0[1]);
			$t -> set_var('serdiscounttypechecked1',$discounttypechecked1[1]);
			$t -> set_var('serpropricedisplay0',$dpropricedisplay0[1]);
			$t -> set_var('serpropricedisplay1',$dpropricedisplay1[1]);
		$t -> set_var('sml','');
		$t -> set_var('cml','');
		$t -> set_var('userid',$this->getUid());	
		$t -> set_var('submittoauditdisable','disabled');	//提交按钮可用状态
		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$date=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'promotion WHERE promotion_id = '.$updid);
			 $t -> set_var('sertype','0') ;
			 $t -> set_var('dtype','0') ;
			 
		$submittoauditdisablelist=array("disabled","","disabled","disabled","disabled","disabled","disabled","disabled");
		$t -> set_var('submittoauditdisable',$submittoauditdisablelist[$date['status']]);	//提交按钮可用状态	
		
			if($_GET['cat_promotiondetail_id']=='' and  $_GET['ser_promotiondetail_id']==''){
			
			$assigntype=array("checked","");
			$assigntype1=array("","checked"); 
			 
			$t -> set_var('assignservice0',$assigntype[$date['assigntype']]);
			$t -> set_var('assignservice1',$assigntype1[$date['assigntype']]);
			if($date['assigntype']==0){
			//$t -> set_var('assignservice0','checked="checked"') ;
			//$t -> set_var('assignservice0','') ;
			//$t -> set_var('assignservice1','') ;
			$t -> set_var('servicetabledisplay',"");	
			$t -> set_var('servicelisttabledisplay',"");	
			$t -> set_var('categorylisttabledisplay',"none");	
			$t -> set_var('categorytalbedisplay',"none");	
			$serdiscounttypechecked0=array("checked","");
			$serdiscounttypechecked1=array("","checked");
			$dpropricedisplay0=array("","none");
			$dpropricedisplay1=array("none","");
			
			$t -> set_var('serdiscounttypechecked0',$serdiscounttypechecked0[1]);
			$t -> set_var('serdiscounttypechecked1',$serdiscounttypechecked1[1]);
			$t -> set_var('dpropricedisplay0',$dpropricedisplay0[1]);
			$t -> set_var('dpropricedisplay1',$dpropricedisplay1[1]);
			
			$t -> set_var('ser_proprice',"");	
			$t -> set_var('ser_discount',"");
			
			}else{
			
			$t -> set_var('dmemo','') ;
			$t -> set_var('dproprice','0') ;
			$t -> set_var('ddiscount','') ;
			$t -> set_var('assignservice1','checked="checked"') ;
			$t -> set_var('ditem_name','') ;
			$t -> set_var('ditem_id','') ;
			
			$t -> set_var('servicetabledisplay',"none");	
			$t -> set_var('servicelisttabledisplay',"none");	
			$t -> set_var('categorylisttabledisplay',"");	
			$t -> set_var('categorytalbedisplay',"");	
			
			$discounttypechecked0=array("checked","");
			$discounttypechecked1=array("","checked");
			$dpropricedisplay0=array("","none");
			$dpropricedisplay1=array("none","");
			$t -> set_var('discounttypechecked0',$discounttypechecked0[1]);
			$t -> set_var('discounttypechecked1',$discounttypechecked1[1]);
			$t -> set_var('dpropricedisplay0',$dpropricedisplay0[1]);
			$t -> set_var('dpropricedisplay1',$dpropricedisplay1[1]);			
			
			}
			}else if($_GET['cat_promotiondetail_id']<>''){
			$t -> set_var('dmemo','') ;
			$t -> set_var('dproprice','0') ;
			$t -> set_var('ddiscount','') ;
			$t -> set_var('assignservice1','checked="checked"') ;
			$t -> set_var('ditem_name','') ;
			$t -> set_var('ditem_id','') ;
			$t -> set_var('dtype','0') ;
			$t -> set_var('servicetabledisplay',"none");	
			$t -> set_var('servicelisttabledisplay',"none");	
			$t -> set_var('categorylisttabledisplay',"");	
			$t -> set_var('categorytalbedisplay',"");					
			}elseif($_GET['ser_promotiondetail_id']<>''){
			$t -> set_var('assignservice1','') ;
			$t -> set_var('assignservice0','checked="checked"') ;
			//$t -> set_var('assignservice1','') ;
			$t -> set_var('servicetabledisplay',"");	
			$t -> set_var('servicelisttabledisplay',"");	
			$t -> set_var('categorylisttabledisplay',"none");	
			$t -> set_var('categorytalbedisplay',"none");	
			}else{
			$t -> set_var('assignservice0','checked="checked"') ;
			$t -> set_var('assignservice0','') ;
			$t -> set_var('assignservice1','') ;
			$t -> set_var('servicetabledisplay',"none");	
			$t -> set_var('servicelisttabledisplay',"none");	
			$t -> set_var('categorylisttabledisplay',"none");	
			$t -> set_var('categorytalbedisplay',"none");	
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
		$t -> set_var('cat_action',"add");	
		$t -> set_var('c_updid',"");	
		$t -> set_var('cat_updid',"");	
		$t -> set_var('ser_action',"add");	
		$t -> set_var('s_updid',"");	
		$t -> set_var('ser_updid',"");		
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
		$t -> set_var('cat_id','');		
		$t -> set_var('cat_name','');
		$t -> set_var('cat_memo',"");	
		$t -> set_var('cat_pricepertime',"");
		$t -> set_var('cat_services_times',"");
		$t -> set_var('cat_recordcount',"");
		$t -> set_var('ser_times',"");	
		$t -> set_var('ser_price',"");	
		$t -> set_var('ser_memo',"");	
		$t -> set_var('ser_no',"");	
		$t -> set_var('ser_name',"");	
		$t -> set_var('ser_pricepertime',"");	
		$t -> set_var('ser_id',"");	
		$t -> set_var('ser_services_times',"");	
		$t -> set_var('ser_recordcount',"");
		//设置服务列表
			
			$t -> set_var('sml');

						
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'promotiondetail  where  assigntype=0 and promotion_id  ='.$updid);
 
 			if($inrs&&$date['assigntype']==0){
			
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);				
				//$data1=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'services   where services_id ='.$inrrs["services_id"].' and agencyid ='.$_SESSION["currentorgan"]);				
				
				
				//echo 'select * from '.WEB_ADMIN_TABPOX.'produce  where produce_id  ='.$inrrs["item_id"];
				 $produce=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'produce  where produce_id  ='.$inrrs["item_id"]);
				 $procatalog=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'procatalog    where category_id  ='.$produce["categoryid"]);
				$t -> set_var('category_name',$procatalog['category_name']);
				$t -> set_var('services_no',$produce['code']);
				$t -> set_var('services_name',$produce['produce_name']);
				$t -> parse('sml','ser_mainlist',true);
			}
			}
			$inrs -> Close();			
			//$acount = &$this -> dbObj -> GetOne('select count(*) as acount from '.WEB_ADMIN_TABPOX.'promotiondetail  where promotion_id  ='.$updid);
			$t -> set_var('recordcount',$acount);	

		//设置类别列表
			
			$t -> set_var('cml');

						
			$inrs4 = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'promotiondetail  where   assigntype=1 and promotion_id  ='.$updid);
  
			if($inrs4&&$date['assigntype']==1){
	     	while ($inrrs4 = &$inrs4 -> FetchRow()) {
				$t -> set_var($inrrs4);	
							
				//$data4=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'services   where services_id ='.$inrrs4["services_id"].' and agencyid ='.$_SESSION["currentorgan"]);				
				
				//$data5=$this -> dbObj ->  Execute('select * from '.WEB_ADMIN_TABPOX.'servicecategory    where category_id  in ('.$inrrs4["servicecategory_id"].')');
				
				//$t -> set_var($data5);
				//$category_name='';
				//while ($inrrs2 = $data5 -> FetchRow()) {
					//$category_name=$category_name==''?$inrrs2['category_name']:$category_name.','.$inrrs2['category_name'];
					 
				//}
				 
			 	if($inrrs4['type']==0&&$inrrs4['assigntype']==1){//产品类别
				$item=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'procatalog   where category_id ='.$inrrs4["item_id"].' and agencyid ='.$_SESSION["currentorgan"]);
				$t -> set_var('item_name',$item['category_name']);
				$t -> set_var('item_no',$item['category_id']);
				//echo 'select * from '.WEB_ADMIN_TABPOX.'procatalog   where category_id ='.$inrrs4["item_id"].' and agencyid ='.$_SESSION["currentorgan"];
				}
				$t -> parse('cml','cat_mainlist',true);
			}
			 }
			$inrs4 -> Close();			
			//$acount = &$this -> dbObj -> GetOne('select count(*) as acount from '.WEB_ADMIN_TABPOX.'promotiondetail  where promotion_id  ='.$updid);
			//$t -> set_var('recordcount',$acount);	
 
		// 修改项目
		 
		    if($_GET['ser_promotiondetail_id']!=''){
			//$t -> set_var('cs');
		
			$t -> set_var('ser_action',"upd");	
			$t -> set_var('s_updid',"");	
			$t -> set_var('ser_updid',"");	

			$inrs2 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'promotiondetail  where promotiondetail_id ='.$_GET['ser_promotiondetail_id']);
			//$t -> set_var($inrs);
			$t -> set_var('sellcommission',$inrs2['sellcommission']);
		    $t -> set_var('s_updid',$inrs2['services_id']);	
			$t -> set_var('ser_updid',$_GET['ser_promotiondetail_id']);	
			$t -> set_var('ser_services_times',$inrs2['services_times']);
			$t -> set_var('ser_pricepertime',$inrs2['pricepertime']);
			$inrs3 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrs2['item_id']);
			$t -> set_var('ser_no',$inrs3['code']);
			$t -> set_var('ser_name',$inrs3['produce_name']);
			$t -> set_var('ser_id',$inrs3['produce_id']);
			$t -> set_var('ser_discount',$inrs2['discount']);
			$t -> set_var('ser_proprice',$inrs2['proprice']);
			$discounttypechecked0=array("checked","");
			$discounttypechecked1=array("","checked");
			$dpropricedisplay0=array("","none");
			$dpropricedisplay1=array("none","");
			$t -> set_var('serdiscounttypechecked0',$discounttypechecked0[$inrs2['discounttype']]);
			$t -> set_var('serdiscounttypechecked1',$discounttypechecked1[$inrs2['discounttype']]);
			$t -> set_var('serpropricedisplay0',$dpropricedisplay0[$inrs2['discounttype']]);
			$t -> set_var('serpropricedisplay1',$dpropricedisplay1[$inrs2['discounttype']]);
			//$inrs2 -> Close();	
			}
			
		// 修改服务类别
		 
		    if($_GET['cat_promotiondetail_id']!=''){
			//$t -> set_var('cs');
		
			$t -> set_var('cat_action',"upd");	

			$inrs2 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'promotiondetail  where promotiondetail_id ='.$_GET['cat_promotiondetail_id']);
			 
			//$t -> set_var($inrs);
		    $t -> set_var('c_updid',$inrs2['servicecategory_id']);	
			$t -> set_var('cat_updid',$_GET['cat_promotiondetail_id']);	
			$t -> set_var('ditem_id',$inrs2['item_id']);
			
			$t -> set_var('ddiscount',$inrs2['discount']);
			$t -> set_var('dproprice',$inrs2['proprice']);
			$discounttypechecked0=array("checked","");
			$discounttypechecked1=array("","checked");
			$dpropricedisplay0=array("","none");
			$dpropricedisplay1=array("none","");
			$t -> set_var('discounttypechecked0',$discounttypechecked0[$inrs2['discounttype']]);
			$t -> set_var('discounttypechecked1',$discounttypechecked1[$inrs2['discounttype']]);
			$t -> set_var('dpropricedisplay0',$dpropricedisplay0[$inrs2['discounttype']]);
			$t -> set_var('dpropricedisplay1',$dpropricedisplay1[$inrs2['discounttype']]);
			$t -> set_var('ditem_name', $this -> dbObj -> GetOne('select category_name  from '.WEB_ADMIN_TABPOX.'procatalog  where category_id ='.$inrs2['item_id']));
			//echo 'select category_name  from '.WEB_ADMIN_TABPOX.'servicecategory    where category_id ='.$inrs2['servicecategory_id'];
			//$t -> set_var('cservices_no',$inrs3['services_no']);
			//$t -> set_var('cservices_name',$inrs3['services_name']);
			//$t -> set_var('cservices_id',$inrs3['services_id']);
			//$t -> set_var('ccode',$inrs3['code']);
			//$t -> set_var('cprice',$inrs3['price']);
			//$t -> set_var('cstandardunit',$inrs3['standardunit']);
			//$t -> set_var('cviceunit',$inrs3['viceunit']);
			//$t -> set_var('cviceunitnumber',$inrs3['viceunitnumber']);
			//$t -> parse('cs','consumables',true);
			//$inrs2 -> Close();	
			}
		$t -> set_var('price',$date['price']);		
		$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$date['categoryid']));	
		$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$date['standardunit']));	
		$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$date['viceunit']));	

		}
		$t -> set_var('categorynamelist',$this ->selectlist('servicecategory','category_id','category_name',$date['categoryid']));		
		
		
		//$t -> set_var('ml',"");	

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
	  if($_GET['consumables_id']==''){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'promotion WHERE promotion_id in('.$delid.')');
		}else{
		//echo 'DELETE FROM '.WEB_ADMIN_TABPOX.'promotiondetail WHERE promotiondetail_id in('.$_GET['consumables_id'].')';
		 $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'promotiondetail WHERE promotiondetail_id in('.$_GET['consumables_id'].')');
		
		}
		$info='删除';
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}
	function goAppend(){
		$promotiontype_id= "8";
		$id = 0;
		$info = '';
		
		if($this -> isAppend){
			$info = '增加';	
			$man=$this -> dbObj -> GetOne("select B.employee_name from `".WEB_ADMIN_TABPOX."user` A INNER JOIN  `".WEB_ADMIN_TABPOX."employee` B ON A.employee_id =B.employee_id  WHERE  A.userid =".$this->getUid());	
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."promotion` (`promotion_no`,`promotion_name`, `bgdate`, `enddate`,`assigntype`, `memo`, man,stauts,`agencyid`) VALUES ('" .$_POST["promotion_no"]."','".$_POST["promotion_name"]."','".$_POST["bgdate"]."', '".$_POST["enddate"]."', '".$_POST["assigntype"]."', '".$_POST["memo"]."', '".$man."',1,'".$_SESSION["currentorgan"]."')");
			
  //echo "INSERT INTO `".WEB_ADMIN_TABPOX."promotion` (`promotion_no`,`promotion_name`, `bgdate`, `enddate`,`assigntype`, `memo`, `agencyid`) VALUES ('" .$_POST["promotion_no"]."','".$_POST["promotion_name"]."','".$_POST["bgdate"]."', '".$_POST["enddate"]."', '".$_POST["assigntype"]."', '".$_POST["memo"]."', '".$_SESSION["currentorgan"]."')";
			$id = $this -> dbObj -> Insert_ID();
			if($_POST["assigntype"]=='1'){
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."promotiondetail` (`promotion_id`, `type`, `assigntype`, `item_id`, `discounttype`, `discount`, `proprice`,agencyid) VALUES ('".$id."','".$_POST["dtype"]."','".$_POST["assigntype"]."','".$_POST["ditem_id"]."','".$_POST["ddiscounttype"]."','".$_POST["ddiscount"]."','".$_POST["dproprice"]."','".$_SESSION["currentorgan"]."')");
			//echo "INSERT INTO `".WEB_ADMIN_TABPOX."promotiondetail` (`promotion_id`, `type`, `assigntype`, `item_id`, `discounttype`, `discount`, `proprice`,agencyid) VALUES ('".$id."','".$_POST["dtype"]."','".$_POST["assigntype"]."','".$_POST["ditem_id"]."','".$_POST["ddiscounttype"]."','".$_POST["ddiscount"]."','".$_POST["dproprice"]."','".$_SESSION["currentorgan"]."')";
			}else if($_POST["assigntype"]=='0'){
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."promotiondetail` (`promotion_id`, `type`, `assigntype`, `item_id`, `discounttype`, `discount`, `proprice`,agencyid) VALUES ('".$id."','".$_POST["sertype"]."','".$_POST["assigntype"]."','".$_POST["ser_id"]."','".$_POST["serdiscounttype"]."','".$_POST["ser_discount"]."','".$_POST["ser_proprice"]."','".$_SESSION["currentorgan"]."')");
 			//echo "INSERT INTO `".WEB_ADMIN_TABPOX."promotiondetail` (`promotion_id`, `type`, `assigntype`, `item_id`, `discounttype`, `discount`, `proprice`,agencyid) VALUES ('".$id."','".$_POST["sertype"]."','".$_POST["assigntype"]."','".$_POST["ser_id"]."','".$_POST["serdiscounttype"]."','".$_POST["ser_discount"]."','".$_POST["ser_proprice"]."','".$_SESSION["currentorgan"]."')";
			//$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."promotiondetail` (`promotion_id`, `type`, `assigntype`, `item_id`, `discounttype`, `discount`, `proprice`,agencyid) VALUES ('".$id."','".$_POST["dtype"]."','".$_POST["assigntype"]."','".$_POST["ditem_id"]."','".$_POST["ddiscounttype"]."','".$_POST["ddiscount"]."','".$_POST["dproprice"]."','".$_SESSION["currentorgan"]."')");
			
			}

			//echo "INSERT INTO `".WEB_ADMIN_TABPOX."promotiondetail` (promotion_id,servicecategory_id,services_id,pricepertime,services_times ,memo,agencyid) VALUES ('".$id."','".$_POST["cat_id"]."','".$_POST["ser_id"]."','".$_POST["cat_pricepertime"]."','".$_POST["cat_services_times"]."','".$_POST["cat_memo"]."','".$_SESSION["currentorgan"]."')";
			exit("<script>alert('新增成功');location.href='promotion.php?action=upd&updid=".$id."';</script>");	
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."promotion` SET `promotion_name` = '".$_POST["promotion_name"]."', `promotion_no` = '".$_POST["promotion_no"]."',`bgdate` = '".$_POST["bgdate"]."',`enddate` = '".$_POST["enddate"]."',`assigntype` ='".$_POST["assigntype"]."', `memo` = '".$_POST["memo"]."', `status` = '1' WHERE promotion_id =".$id);
			//echo "UPDATE `".WEB_ADMIN_TABPOX."promotion` SET `promotion_name` = '".$_POST["promotion_name"]."',`promotiontype_id` = '".$promotiontype_id."',`promotion_no` = '".$_POST["promotion_no"]."',`coderule` = '".$_POST["coderule"]."',`totaltimes` = '".$this->intnonull($_POST["totaltimes"])."',`timelimit` ='".$this->intnonull($_POST["timelimit"])."',`price` = '".$this->intnonull($_POST["price"])."',`pricepertime` = '".$this->intnonull($_POST["pricepertime"])."',`assignservice` = '".$this->intnonull($_POST["assignservice"])."',`value` = '".$_POST["value"]."',`memo` = '".$_POST["memo"]."' WHERE promotion_id =".$id;
		//echo $_POST["con_act"];
	
	//修改服务
 if ($_POST["assigntype"]=='0'){
 if($_POST["ser_act"]=='upd'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."promotiondetail` SET `item_id` = '".$_POST["ser_id"]."',`assigntype` = '".$_POST["assigntype"]."',`type` = '".$_POST["sertype"]."',`discounttype` = '".$_POST["serdiscounttype"]."',`discount` = '".$_POST["ser_discount"]."',`proprice` = '".$_POST["ser_proprice"]."'  WHERE promotiondetail_id  =".$_POST['ser_updid']);		
		// echo "UPDATE `".WEB_ADMIN_TABPOX."promotiondetail` SET `item_id` = '".$_POST["ser_id"]."',`assigntype` = '".$_POST["assigntype"]."',`type` = '".$_POST["sertype"]."',`discounttype` = '".$_POST["serdiscounttype"]."',`discount` = '".$_POST["ser_discount"]."',`proprice` = '".$_POST["ser_proprice"]."'  WHERE promotiondetail_id  =".$_POST['ser_updid'];
		exit("<script>alert('修改成功');location.href='promotion.php?action=upd&updid=".$id."';</script>");
		}else if($_POST['d1add']=='add'){

			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."promotiondetail` (`promotion_id`, `type`, `assigntype`, `item_id`, `discounttype`, `discount`, `proprice`,agencyid) VALUES ('".$id."','".$_POST["sertype"]."','".$_POST["assigntype"]."','".$_POST["ser_id"]."','".$_POST["serdiscounttype"]."','".$_POST["ser_discount"]."','".$_POST["ser_proprice"]."','".$_SESSION["currentorgan"]."')");
		  
		// echo "INSERT INTO `".WEB_ADMIN_TABPOX."promotiondetail` (`promotion_id`, `type`, `assigntype`, `item_id`, `discounttype`, `discount`, `proprice`,agencyid) VALUES ('".$id."','".$_POST["sertype"]."','".$_POST["assigntype"]."','".$_POST["ser_id"]."','".$_POST["serdiscounttype"]."','".$_POST["ser_discount"]."','".$_POST["ser_proprice"]."','".$_SESSION["currentorgan"]."')";
		}
	}
	//修改服务类别
 
	if ($_POST["assigntype"]=='1'){
 	if($_POST["cat_act"]=='upd'){
 
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."promotiondetail` SET `item_id` = '".$_POST["ditem_id"]."',`assigntype` = '".$_POST["assigntype"]."',`type` = '".$_POST["dtype"]."',`discounttype` = '".$_POST["ddiscounttype"]."',`discount` = '".$_POST["ddiscount"]."',`proprice` = '".$_POST["dproprice"]."'  WHERE promotiondetail_id  =".$_POST['cat_updid']);		
		 //echo "UPDATE `".WEB_ADMIN_TABPOX."promotiondetail` SET `item_id` = '".$_POST["ditem_id"]."',`assigntype` = '".$_POST["assigntype"]."',`type` = '".$_POST["dtype"]."',`discounttype` = '".$_POST["ddiscounttype"]."',`discount` = '".$_POST["ddiscount"]."',`proprice` = '".$_POST["dproprice"]."'  WHERE promotiondetail_id  =".$_POST['cat_updid'];
		exit("<script>alert('修改成功');location.href='promotion.php?action=upd&updid=".$id."';</script>");
		}else if($_POST['d2add']=='add'){
 
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."promotiondetail` (`promotion_id`, `type`, `assigntype`, `item_id`, `discounttype`, `discount`, `proprice`,agencyid) VALUES ('".$id."','".$_POST["dtype"]."','".$_POST["assigntype"]."','".$_POST["ditem_id"]."','".$_POST["ddiscounttype"]."','".$_POST["ddiscount"]."','".$_POST["dproprice"]."','".$_SESSION["currentorgan"]."')");
		
		//echo "INSERT INTO `".WEB_ADMIN_TABPOX."promotiondetail` (`promotion_id`, `type`, `assigntype`, `item_id`, `discounttype`, `discount`, `proprice`,agencyid) VALUES ('".$id."','".$_POST["dtype"]."','".$_POST["assigntype"]."','".$_POST["ditem_id"]."','".$_POST["ddiscounttype"]."','".$_POST["ddiscount"]."','".$_POST["dproprice"]."','".$_SESSION["currentorgan"]."')";
		exit("<script>alert('新增成功');location.href='promotion.php?action=upd&updid=".$id."';</script>");
		}
	}
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
  