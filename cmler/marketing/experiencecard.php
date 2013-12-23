<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
	function disp(){
		//定义模板
		$marketingcardtype_id= "3";
		$t = new Template('../template/marketing');
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
			$sql='select * from '.WEB_ADMIN_TABPOX.'marketingcard  where marketingcardtype_id ='.$marketingcardtype_id.' and  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql="select * from ".WEB_ADMIN_TABPOX."marketingcard s INNER JOIN ".WEB_ADMIN_TABPOX."$ftable f on s.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and s.marketingcardtype_id ='.$marketingcardtype_id.'  and  s.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'marketingcard  where marketingcardtype_id ='.$marketingcardtype_id.'  and  agencyid ='.$_SESSION["currentorgan"];
			 
			}
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  marketingcard_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));			
	    	$t -> set_var('recordcount',$count);	
			
			
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				
				$t -> set_var('marketingcardtype_name',$this -> dbObj -> getone('select marketingcardtype_name from '.WEB_ADMIN_TABPOX.'marketingcardtype  where marketingcardtype_id ='.$inrrs["marketingcardtype_id"]));
				$t -> set_var('brand_name',$this -> dbObj -> getone('select brand_name from '.WEB_ADMIN_TABPOX.'brand  where brand_id ='.$inrrs["brandid"]));
				$t -> set_var('category_name',$this -> dbObj -> getone('select category_name from '.WEB_ADMIN_TABPOX.'servicecategory  where category_id ='.$inrrs["categoryid"]));
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['marketingcard_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['marketingcard_id']));		
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
		$t -> set_file('f','experiencecard_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','ser_mainlist','sml');	
		$t -> set_block('f','cat_mainlist','cml');	
        //$t -> set_block('f','consumables','cs');	
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		$t -> set_var('marketingcard_no',"");	
		$t -> set_var('marketingcard_name',"");	
		$t -> set_var('error',"");	
		$t -> set_var('coderule',"");
		$t -> set_var('value',"");
		$t -> set_var('sellcommission',"");	
		$t -> set_var('beautycommission',"");	
		$t -> set_var('leadercommission',"");	
		
		$t -> set_var('timelimit',"");
		$t -> set_var('pricepertime',"");
		$t -> set_var('totaltimes',"");	
		$t -> set_var('commission',"");	
		$t -> set_var('ucommission',"");	
		$t -> set_var('price',"");	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");
		$t -> set_var('servicetabledisplay',"");	
		$t -> set_var('servicelisttabledisplay',"none");	
		$t -> set_var('categorylisttabledisplay',"none");	
		$t -> set_var('categorytalbedisplay',"none");	
			$t -> set_var('assignservice1','checked="checked"') ;
			$t -> set_var('assignservice0','') ;
			$t -> set_var('assignservice2','') ; 	
		$t -> set_var('memo',"");
		
		$t -> set_var('bgdate',"");	
		$t -> set_var('enddate',"");
		$t -> set_var('cat_recordcount',"");
		$t -> set_var('cat_id','');		
		$t -> set_var('cat_name','');
		$t -> set_var('cat_memo',"");	
		$t -> set_var('cat_pricepertime',"");
		$t -> set_var('cat_services_times',"");
		$t -> set_var('ser_times',"");	
		$t -> set_var('ser_price',"");	
		$t -> set_var('ser_memo',"");	
		$t -> set_var('ser_no',"");	
		$t -> set_var('ser_name',"");	
		$t -> set_var('ser_pricepertime',"");	
		$t -> set_var('ser_id',"");	
		$t -> set_var('ser_services_times',"");	
		$t -> set_var('ser_recordcount',"");
	
		$t -> set_var('cat_action',"add");	
		$t -> set_var('c_updid',"");	
		$t -> set_var('cat_updid',"");	
		$t -> set_var('ser_action',"add");	
		$t -> set_var('s_updid',"");	
		$t -> set_var('ser_updid',"");		
		
		$t -> set_var('sml','');
		$t -> set_var('cml','');
		$t -> set_var('userid',$this->getUid());		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$date=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'marketingcard WHERE marketingcard_id = '.$updid);
			
			if($_GET['cat_marketingcarddetail_id']=='' and  $_GET['ser_marketingcarddetail_id']==''){
			if ($date['assignservice']==0){
			$t -> set_var('assignservice0','checked="checked"') ;
			$t -> set_var('assignservice1','') ;
			$t -> set_var('assignservice2','') ;
			$t -> set_var('servicetabledisplay',"none");	
			$t -> set_var('servicelisttabledisplay',"none");	
			$t -> set_var('categorylisttabledisplay',"none");	
			$t -> set_var('categorytalbedisplay',"none");	
			}else if($date['assignservice']==1){
			$t -> set_var('assignservice1','checked="checked"') ;
			$t -> set_var('assignservice0','') ;
			$t -> set_var('assignservice2','') ;
			$t -> set_var('servicetabledisplay',"");	
			$t -> set_var('servicelisttabledisplay',"");	
			$t -> set_var('categorylisttabledisplay',"none");	
			$t -> set_var('categorytalbedisplay',"none");	
			}else{
			$t -> set_var('assignservice2','checked="checked"') ;
			$t -> set_var('assignservice1','') ;
			$t -> set_var('assignservice0','') ;
			$t -> set_var('servicetabledisplay',"none");	
			$t -> set_var('servicelisttabledisplay',"none");	
			$t -> set_var('categorylisttabledisplay',"");	
			$t -> set_var('categorytalbedisplay',"");		
			}
			}else if($_GET['cat_marketingcarddetail_id']<>''){
			$t -> set_var('assignservice0','') ;
			$t -> set_var('assignservice1','') ;
			$t -> set_var('assignservice2','checked="checked"') ;
			$t -> set_var('servicetabledisplay',"none");	
			$t -> set_var('servicelisttabledisplay',"none");	
			$t -> set_var('categorylisttabledisplay',"");	
			$t -> set_var('categorytalbedisplay',"");				
			}elseif($_GET['ser_marketingcarddetail_id']<>''){
			$t -> set_var('assignservice0','') ;
			$t -> set_var('assignservice1','checked="checked"') ;
			$t -> set_var('assignservice2','') ;
			$t -> set_var('servicetabledisplay',"");	
			$t -> set_var('servicelisttabledisplay',"");	
			$t -> set_var('categorylisttabledisplay',"none");	
			$t -> set_var('categorytalbedisplay',"none");	
			}else{
			$t -> set_var('assignservice0','checked="checked"') ;
			$t -> set_var('assignservice1','') ;
			$t -> set_var('assignservice2','') ;
			$t -> set_var('servicetabledisplay',"none");	
			$t -> set_var('servicelisttabledisplay',"none");	
			$t -> set_var('categorylisttabledisplay',"none");	
			$t -> set_var('categorytalbedisplay',"none");	
			}
			$t -> set_var('updid',$updid);
			
			$t -> set_var('action','upd');			
			$t -> set_var($date);	
			if($date['limitdate']==1){
				$t -> set_var('limitdate',"checked");
				}else{
				$t -> set_var('limitdate',"");
				}
			if($date['pricetype']==1){
				
			$t -> set_var('pricetype1selected',"selected");
			}else{
			$t -> set_var('pricetype0selected',"selected");
			}	
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

						
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail  where services_id <>"" and marketingcard_id  ='.$updid);
		

			
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);		
				 
				$data1=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'services   where services_id ='.$inrrs["services_id"].' and agencyid ='.$_SESSION["currentorgan"]);				
				
				$data2=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'servicecategory    where category_id  ='.$data1["categoryid"]);
				$t -> set_var($data2);
				$t -> set_var($data1);
				$t -> parse('sml','ser_mainlist',true);
			}
			$inrs -> Close();			
			$acount = &$this -> dbObj -> GetOne('select count(*) as acount from '.WEB_ADMIN_TABPOX.'marketingcarddetail  where marketingcard_id  ='.$updid);
			$t -> set_var('recordcount',$acount);	

		//设置类别列表
			
			$t -> set_var('cml');

						
			$inrs4 = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail  where services_id ="" and marketingcard_id  ='.$updid);
		

			 
	     	while ($inrrs4 = &$inrs4 -> FetchRow()) {
				$t -> set_var($inrrs4);				
				//$data4=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'services   where services_id ='.$inrrs4["services_id"].' and agencyid ='.$_SESSION["currentorgan"]);				
				
				$data5=$this -> dbObj ->  Execute('select * from '.WEB_ADMIN_TABPOX.'servicecategory    where category_id  in ('.$inrrs4["servicecategory_id"].')');
				
				//$t -> set_var($data5);
				$category_name='';
				while ($inrrs2 = $data5 -> FetchRow()) {
					$category_name=$category_name==''?$inrrs2['category_name']:$category_name.','.$inrrs2['category_name'];
					 
				}
				$t -> set_var('category_name',$category_name);
				$t -> parse('cml','cat_mainlist',true);
			}
			$inrs4 -> Close();			
			$acount = &$this -> dbObj -> GetOne('select count(*) as acount from '.WEB_ADMIN_TABPOX.'marketingcarddetail  where marketingcard_id  ='.$updid);
			$t -> set_var('recordcount',$acount);	

		// 修改服务
		 
		    if($_GET['ser_marketingcarddetail_id']!=''){
			//$t -> set_var('cs');
		
		$t -> set_var('ser_action',"upd");	
		$t -> set_var('s_updid',"");	
		$t -> set_var('ser_updid',"");	

			$inrs2 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail  where marketingcarddetail_id ='.$_GET['ser_marketingcarddetail_id']);
			//$t -> set_var($inrs);
			$t -> set_var('sellcommission',$inrs2['sellcommission']);
		    $t -> set_var('s_updid',$inrs2['services_id']);	
			$t -> set_var('ser_updid',$_GET['ser_marketingcarddetail_id']);	
			$t -> set_var('ser_services_times',$inrs2['services_times']);
			$t -> set_var('ser_pricepertime',$inrs2['pricepertime']);
			$inrs3 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'services   where services_id ='.$inrs2['services_id']);
			$t -> set_var('ser_no',$inrs3['services_no']);
			$t -> set_var('ser_name',$inrs3['services_name']);
			$t -> set_var('ser_id',$inrs3['services_id']);
			$t -> set_var('ser_code',$inrs3['code']);
			$t -> set_var('ser_price',$inrs3['price']);
			//$t -> set_var('cstandardunit',$inrs3['standardunit']);
			//$t -> set_var('cviceunit',$inrs3['viceunit']);
			//$t -> set_var('cviceunitnumber',$inrs3['viceunitnumber']);
			//$t -> parse('cs','consumables',true);
			//$inrs2 -> Close();	
			}
			
		// 修改服务类别
		 
		    if($_GET['cat_marketingcarddetail_id']!=''){
			//$t -> set_var('cs');
		
			$t -> set_var('cat_action',"upd");	

			$inrs2 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'marketingcarddetail  where marketingcarddetail_id ='.$_GET['cat_marketingcarddetail_id']);
			//$t -> set_var($inrs);
		    $t -> set_var('c_updid',$inrs2['servicecategory_id']);	
			$t -> set_var('cat_updid',$_GET['cat_marketingcarddetail_id']);	
			$t -> set_var('cat_id',$inrs2['servicecategory_id']);
			
			$t -> set_var('cat_services_times',$inrs2['services_times']);
			$t -> set_var('cat_pricepertime',$inrs2['pricepertime']);
			$t -> set_var('cat_name', $this -> dbObj -> GetOne('select category_name  from '.WEB_ADMIN_TABPOX.'servicecategory    where category_id ='.$inrs2['servicecategory_id']));
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
			
		$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$date['categoryid']));	
		$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$date['standardunit']));	
		$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$date['viceunit']));	

		}
		$t -> set_var('categorynamelist',$this ->selectlist('servicecategory','category_id','category_name',$date['categoryid']));		
		
		$t -> set_var('price', $date['price']);	
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
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'marketingcard WHERE marketingcard_id in('.$delid.')');
		}else{
		echo 'DELETE FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE marketingcarddetail_id in('.$_GET['consumables_id'].')';
		 $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'marketingcarddetail WHERE marketingcarddetail_id in('.$_GET['consumables_id'].')');
		
		}
		$info='删除';
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}
	function goAppend(){
		$marketingcardtype_id= "3";
		$id = 0;
		$info = '';
		
		if($this -> isAppend){
			$info = '增加';	
			$limitdate=$_POST['limitdate']=='on'?1:0;
			if($_POST['bgdate']<>''&&$_POST['enddate']<>''){
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."marketingcard` (`marketingcard_name`,`marketingcardtype_id`, `marketingcard_no`, `coderule`,`totaltimes`, `price`, `timelimit`, `pricepertime`, `assignservice`,`value`,`memo`, `agencyid`,`sellcommission`,`leadercommission`,`beautycommission`,bgdate,enddate,limitdate,pricetype) VALUES ('" .$_POST["marketingcard_name"]."','".$marketingcardtype_id."','".$_POST["marketingcard_no"]."', '".$_POST["coderule"]."', '".$_POST["totaltimes"]."', '".$this->intnonull($_POST["price"])."',  '".$this->intnonull($_POST["timelimit"])."','".$this->intnonull($_POST["pricepertime"])."','".$this->intnonull($_POST["assignservice"])."', '".$_POST["value"]."', '".$_POST["memo"]."','".$_SESSION["currentorgan"]."', '".$_POST["sellcommission"]."', '".$_POST["leadercommission"]."', '".$_POST["beautycommission"]."','".$_POST["bgdate"]."','".$_POST["enddate"]."','".$limitdate."','".$_POST["pricetype"]."')");
 
 			}else{
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."marketingcard` (`marketingcard_name`,`marketingcardtype_id`, `marketingcard_no`, `coderule`,`totaltimes`, `price`, `timelimit`, `pricepertime`, `assignservice`,`value`,`memo`, `agencyid`,`sellcommission`,`leadercommission`,`beautycommission`,bgdate,enddate,limitdate,pricetype) VALUES ('" .$_POST["marketingcard_name"]."','".$marketingcardtype_id."','".$_POST["marketingcard_no"]."', '".$_POST["coderule"]."', '".$_POST["totaltimes"]."', '".$this->intnonull($_POST["price"])."',  '".$this->intnonull($_POST["timelimit"])."','".$this->intnonull($_POST["pricepertime"])."','".$this->intnonull($_POST["assignservice"])."', '".$_POST["value"]."', '".$_POST["memo"]."','".$_SESSION["currentorgan"]."', '".$_POST["sellcommission"]."', '".$_POST["leadercommission"]."', '".$_POST["beautycommission"]."',null,null,'".$limitdate."','".$_POST["pricetype"]."')");
			}
			$id = $this -> dbObj -> Insert_ID();
			if($_POST["assignservice"]=='2'){
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."marketingcarddetail` (marketingcard_id,servicecategory_id,services_id,pricepertime,services_times ,memo,agencyid) VALUES ('".$id."','".$_POST["cat_id"]."','".$_POST["ser_id"]."','".$_POST["cat_pricepertime"]."','".$_POST["cat_services_times"]."','".$_POST["cat_memo"]."','".$_SESSION["currentorgan"]."')");
			}else if($_POST["assignservice"]=='1'){
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."marketingcarddetail` (marketingcard_id,servicecategory_id,services_id,pricepertime,services_times ,memo,agencyid) VALUES ('".$id."','','".$_POST["ser_id"]."','".$_POST["ser_pricepertime"]."','".$_POST["ser_services_times"]."','".$_POST["ser_memo"]."','".$_SESSION["currentorgan"]."')");
			
			}

			//echo "INSERT INTO `".WEB_ADMIN_TABPOX."marketingcarddetail` (marketingcard_id,servicecategory_id,services_id,pricepertime,services_times ,memo,agencyid) VALUES ('".$id."','".$_POST["cat_id"]."','".$_POST["ser_id"]."','".$_POST["cat_pricepertime"]."','".$_POST["cat_services_times"]."','".$_POST["cat_memo"]."','".$_SESSION["currentorgan"]."')";
			exit("<script>alert('新增成功');location.href='experiencecard.php?action=upd&updid=".$id."';</script>");	
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$limitdate=$_POST['limitdate']=='on'?1:0;
			if($_POST['bgdate']<>''&&$_POST['enddate']<>''){
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."marketingcard` SET `marketingcard_name` = '".$_POST["marketingcard_name"]."',`marketingcardtype_id` = '".$marketingcardtype_id."',`marketingcard_no` = '".$_POST["marketingcard_no"]."',`coderule` = '".$_POST["coderule"]."',`totaltimes` = '".$this->intnonull($_POST["totaltimes"])."',`timelimit` ='".$this->intnonull($_POST["timelimit"])."',`price` = '".$this->intnonull($_POST["price"])."',`pricepertime` = '".$this->intnonull($_POST["pricepertime"])."',`assignservice` = '".$this->intnonull($_POST["assignservice"])."',`value` = '".$_POST["value"]."',`memo` = '".$_POST["memo"]."',`sellcommission` = '".$_POST["sellcommission"]."' ,`leadercommission` = '".$_POST["leadercommission"]."' ,`beautycommission` = '".$_POST["beautycommission"]."' ,`bgdate` = '".$_POST["bgdate"]."',`enddate` = '".$_POST["enddate"]."',`limitdate` = '".$limitdate."' ,pricetype='".$_POST["pricetype"]."'  WHERE marketingcard_id =".$id);
			//echo "UPDATE `".WEB_ADMIN_TABPOX."marketingcard` SET `marketingcard_name` = '".$_POST["marketingcard_name"]."',`marketingcardtype_id` = '".$marketingcardtype_id."',`marketingcard_no` = '".$_POST["marketingcard_no"]."',`coderule` = '".$_POST["coderule"]."',`totaltimes` = '".$this->intnonull($_POST["totaltimes"])."',`timelimit` ='".$this->intnonull($_POST["timelimit"])."',`price` = '".$this->intnonull($_POST["price"])."',`pricepertime` = '".$this->intnonull($_POST["pricepertime"])."',`assignservice` = '".$this->intnonull($_POST["assignservice"])."',`value` = '".$_POST["value"]."',`memo` = '".$_POST["memo"]."' WHERE marketingcard_id =".$id;
		//echo $_POST["con_act"];
		}else{
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."marketingcard` SET `marketingcard_name` = '".$_POST["marketingcard_name"]."',`marketingcardtype_id` = '".$marketingcardtype_id."',`marketingcard_no` = '".$_POST["marketingcard_no"]."',`coderule` = '".$_POST["coderule"]."',`totaltimes` = '".$this->intnonull($_POST["totaltimes"])."',`timelimit` ='".$this->intnonull($_POST["timelimit"])."',`price` = '".$this->intnonull($_POST["price"])."',`pricepertime` = '".$this->intnonull($_POST["pricepertime"])."',`assignservice` = '".$this->intnonull($_POST["assignservice"])."',`value` = '".$_POST["value"]."',`memo` = '".$_POST["memo"]."',`sellcommission` = '".$_POST["sellcommission"]."' ,`leadercommission` = '".$_POST["leadercommission"]."' ,`beautycommission` = '".$_POST["beautycommission"]."' ,`bgdate` = null,`enddate` = null,`limitdate` = '".$limitdate."',pricetype='".$_POST["pricetype"]."'  WHERE marketingcard_id =".$id);	
		}
	//修改服务
 if ($_POST["assignservice"]=='1'){
 if($_POST["ser_act"]=='upd'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."marketingcarddetail` SET `servicecategory_id`='', `services_id` = '".$_POST["ser_id"]."',`pricepertime` = '".$_POST["ser_pricepertime"]."',`services_times` = '".$_POST["ser_services_times"]."',`memo` = '".$_POST["ser_memo"]."' WHERE marketingcarddetail_id  =".$_POST['ser_updid']);		
		//echo "UPDATE `".WEB_ADMIN_TABPOX."marketingcarddetail` SET `services_id` = '".$_POST["ser_id"]."',`pricepertime` = '".$_POST["ser_pricepertime"]."',`services_times` = '".$_POST["ser_services_times"]."',`memo` = '".$_POST["ser_memo"]."' WHERE marketingcarddetail_id  =".$_POST['ser_updid'];
		exit("<script>alert('修改成功');location.href='experiencecard.php?action=upd&updid=".$id."';</script>");
		}else if($_POST['ser_id']&&$_POST['ser_services_times']&&$_POST['ser_pricepertime']){

			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."marketingcarddetail` (marketingcard_id,servicecategory_id,services_id,pricepertime,services_times ,memo,agencyid) VALUES ('".$id."','','".$_POST["ser_id"]."','".$_POST["ser_pricepertime"]."','".$_POST["ser_services_times"]."','".$_POST["ser_memo"]."','".$_SESSION["currentorgan"]."')");
		 
		//echo "INSERT INTO `".WEB_ADMIN_TABPOX."marketingcarddetail` (marketingcard_id,services_id,pricepertime,services_times ,memo,agencyid) VALUES ('".$id."','".$_POST["ser_id"]."','".$_POST["ser_pricepertime"]."','".$_POST["ser_services_times"]."','".$_POST["ser_memo"]."','".$_SESSION["currentorgan"]."')";
		}
	}
	//修改服务类别
	if ($_POST["assignservice"]=='2'){
 	if($_POST["cat_act"]=='upd'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."marketingcarddetail` SET `servicecategory_id` = '".$_POST["cat_id"]."',`pricepertime` = '".$_POST["cat_pricepertime"]."',`services_times` = '".$_POST["cat_services_times"]."',`memo` = '".$_POST["cat_memo"]."' WHERE marketingcarddetail_id  =".$_POST['cat_updid']);		
		//echo "UPDATE `".WEB_ADMIN_TABPOX."marketingcarddetail` SET `servicecategory_id` = '".$_POST["cat_id"]."',`pricepertime` = '".$_POST["cat_pricepertime"]."',`services_times` = '".$_POST["cat_services_times"]."',`memo` = '".$_POST["cat_memo"]."' WHERE marketingcarddetail_id  =".$_POST['cat_updid'];
		exit("<script>alert('修改成功');location.href='experiencecard.php?action=upd&updid=".$id."';</script>");
		}else if($_POST['cat_id']&&$_POST['cat_services_times']&&$_POST['cat_pricepertime']){

			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."marketingcarddetail` (marketingcard_id,servicecategory_id,services_id,pricepertime,services_times ,memo,agencyid) VALUES ('".$id."','".$_POST["cat_id"]."','".$_POST["cat_services_id"]."','".$_POST["cat_pricepertime"]."','".$_POST["cat_services_times"]."','".$_POST["cat_memo"]."','".$_SESSION["currentorgan"]."')");
		
		//echo "INSERT INTO `".WEB_ADMIN_TABPOX."marketingcarddetail` (marketingcard_id,servicecategory_id,services_id,pricepertime,services_times ,memo,agencyid) VALUES ('".$id."','".$_POST["cat_id"]."','".$_POST["cat_services_id"]."','".$_POST["cat_pricepertime"]."','".$_POST["cat_services_times"]."','".$_POST["cat_memo"]."','".$_SESSION["currentorgan"]."')";
		exit("<script>alert('修改成功');location.href='experiencecard.php?action=upd&updid=".$id."';</script>");
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
  