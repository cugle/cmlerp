<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
	
 function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='incomestatement'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> incomestatement();			
		}else if(isset($_GET['action']) && $_GET['action']=='print'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> printbill();			
		}else{
            parent::Main();
        }
    }
function disp(){	
		//定义模板
		
		$t = new Template('../template/finace');
		$t -> set_file('f','incomestatementselectdate.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		
		$monthlybatch_name=date('m',time())."月份";
		$t -> set_var('monthlybatch_name',$monthlybatch_name);
		 //上次月结
		$bgdate=$this -> dbObj -> getone('select enddate  from '.WEB_ADMIN_TABPOX.'monthlybatch  where agencyid ='.$_SESSION["currentorgan"].' order by monthlybatch_id desc');	
		
		if (!$bgdate){//如果没有月结过 查找最早的单时间。
			$bgdate=$this -> dbObj -> getone('select creattime  from '.WEB_ADMIN_TABPOX.'sell  where agencyid ='.$_SESSION["currentorgan"].' order by sell_id asc');	 
			$bgdate=date('Y-m-d',strtotime($bgdate)); 
		}else{ 
			$bgdate=date("Y-m-d",strtotime("$m+1 days",strtotime($bgdate)));//设置开始时间为上次月结的下一天
		}
		$t -> set_var('bgdate',$bgdate);
		$t -> set_var('enddate',date('Y-m-d'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
 
	}
	function printbill(){

		$t = new Template('../template/finace');
		$t -> set_file('f','incomestatementtype_detail_bill.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	

			$updid = $_GET['id'] + 0 ;	
			 
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'incomestatementtype WHERE incomestatementtype_id = '.$updid);
			
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
		$t -> set_var('clend',"");	
		$t -> set_var('cmemo',"");	
		$t -> set_var('cloan',"");	
		$t -> set_var('cobject_name',"");	
		$t -> set_var('cobjectid',"");	
		$t -> set_var('employee_id',"");	
		$t -> set_var('employee_name',"");	
			
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
		 
		$t -> set_var('cincomestatementtypeprice',0);
		
 					
		//设置消耗品列表
			
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'incomestatementtypedetail  where incomestatementtype_id  ='.$updid);
			$totallend=0;
			$totalloan=0;
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$totallend=$totallend+$inrrs['lend'];
				$totalloan=$totalloan+$inrrs['loan'];
				$t -> set_var('numbers',$inrrs['number']);
				
				 
				$data1=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'accounttitle   where accounttitle_id ='.$inrrs["accounttitle_id"]);
				$t -> set_var($data1);
				 
				 $objecttype=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id ='.$inrrs["objecttype_id"]);
				 $t -> set_var('objecttype_name',$objecttype['objecttype_name']);
				$object=$this -> dbObj -> getrow('select *  from '.WEB_ADMIN_TABPOX.$objecttype["objecttypetable"].'  where '.$objecttype["objecttypetable"].'_id ='.$inrrs["objectid"]);
				 
				$t -> set_var('object_name',$object[$objecttype["objecttypetable"].'_name']);
				$t -> set_var('memo',$inrrs['memo']);
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();			
			$acount = &$this -> dbObj -> GetOne('select count(*) as acount from '.WEB_ADMIN_TABPOX.'incomestatementtypedetail  where incomestatementtype_id  ='.$updid);
			$t -> set_var('totallend',$totallend);	
			$t -> set_var('totalloan',$totalloan);
			$t -> set_var('recordcount',$acount);

			
			 
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));	



	
		//$t -> set_var('categorynamelist',$this ->selectlist('servicecategory','category_id','category_name',$data['categoryid']));		
		
		
		//$t -> set_var('ml',"");	
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}
	function incomestatement(){
		$t = new Template('../template/finace');
		$t -> set_file('f','incomestatement.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','main','m');		

//搜索
        $bgdate=$_POST["bgdate"]." 00:00:00";
		$enddate=$_POST["enddate"]." 23:59:59";		
		$timecondition=' creattime  between "'.$bgdate.'" and "'.$enddate.'"';	
		$timecondition1=' B.creattime  between "'.$bgdate.'" and "'.$enddate.'"';	
		//期初时间			
		$bgdate=$this -> dbObj -> getone('select enddate  from '.WEB_ADMIN_TABPOX.'annualbatch  where agencyid ='.$_SESSION["currentorgan"].' order by annualbatch_id desc');	
		if (!$bgdate){//如果没有年结过 查找最早的单时间。
			$bgdate=$this -> dbObj -> getone('select creattime  from '.WEB_ADMIN_TABPOX.'sell  where agencyid ='.$_SESSION["currentorgan"].' order by sell_id asc');	 
			
			 
		}	
		$timecondition2=' B.creattime  between "'.$bgdate.'" and "'.$enddate.'"';	
		 
		$category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.'  like "%'.$keywords.'%"';}else{$condition=$category.' like "%'.$keywords.'%"';}
		}
//分页		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:20;
			$offset = $pageid>0?($pageid-1)*$psize:0;

			//设置商品
			$t -> set_var('m');
			
			
			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'incomestatementtype  where   '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'incomestatementtype r INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on r.roomgroup_id  =f.roomgroup_id   where f.roomgroup_name like '%".$keywords."%'" ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'incomestatementtype  ';
			 
			}
			 
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY incomestatementtype_id ASC  ");
			
			$result = &$this -> dbObj -> Execute($sql);		
			$count=$result->RecordCount();
 
				
			
			
			
			
			
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'incomestatementtype  where agencyid ='.$_SESSION["currentorgan"]);
			//echo 'select * from '.WEB_ADMIN_TABPOX.'account  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('zcno',$inrrs['incomestatementtype_no']);
				if($inrrs['parentid']<>0){
					 
						$parentid1=$this -> dbObj ->GetOne('SELECT  parentid FROM '.WEB_ADMIN_TABPOX.'incomestatementtype where incomestatementtype_id='.$inrrs["parentid"]);
						
						if($parentid1==0){
							
						$space="&nbsp;&nbsp;&nbsp;&nbsp;";
						}else{
							 
						$space="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
						}
					$t -> set_var('zcname',$space.$inrrs['incomestatementtype_name']);
					}else{
						$space='';
						$t -> set_var('zcname','<strong>'.$space.$inrrs['incomestatementtype_name'].'</strong>');
					}

				
				//$t -> set_var('zcname',$inrrs['incomestatementtype_name']);
				
				$addup=$this -> dbObj ->GetOne('SELECT A.addup FROM '.WEB_ADMIN_TABPOX.'incomestatement A INNER JOIN '.WEB_ADMIN_TABPOX.'incomestatementtype B ON A.incomestatementtype_id =B.incomestatementtype_id  WHERE B.incomestatementtype_id ='.$inrrs['incomestatementtype_id'].' and B.agencyid='.$_SESSION["currentorgan"].'    ORDER BY A.incomestatement_id DESC');
 
				$addup=$addup?$addup:0;
				$t -> set_var('addup',$addup);					
				//$t -> set_var('accounttitle_name',$this->dbObj -> GetOne('SELECT accounttitle_name FROM '.WEB_ADMIN_TABPOX.'accounttitle WHERE accounttitle_id = '.$inrrs['accounttitle_id']));
				//if($inrrs['accounttitleid']<>''){
				$inrrs['accounttitleid']=$inrrs['accounttitleid']?$inrrs['accounttitleid']:0;
				if($inrrs['type']==1){
				 
				$thismonth=$this -> dbObj ->GetRow('SELECT sum(A.lend-A.loan) as thismonth FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['accounttitleid'].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition1);
				$reducethismonth=$this -> dbObj ->GetRow('SELECT sum(A.lend-A.loan) as reducethismonth FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['addorreduce'].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition1); 
				 
				$thisyear=$this -> dbObj ->GetRow('SELECT sum(A.lend-A.loan) as thisyear FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['accounttitleid'].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition2);
				$reducethisyear=$this -> dbObj ->GetRow('SELECT sum(A.lend-A.loan) as reducethisyear FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['addorreduce'].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition2); 
				}else if($inrrs['type']==0){
				
				$thismonth=$this -> dbObj ->GetRow('SELECT sum(A.loan-A.lend) as thismonth FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['accounttitleid'].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition1);
				$reducethismonth=$this -> dbObj ->GetRow('SELECT sum(A.loan-A.lend) as reducethismonth FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['addorreduce'].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition1); 
				
				//本年累计
				$thisyear=$this -> dbObj ->GetRow('SELECT sum(A.loan-A.lend) as thisyear FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['accounttitleid'].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition2);
				$reducethisyear=$this -> dbObj ->GetRow('SELECT sum(A.loan-A.lend) as reducethisyear FROM '.WEB_ADMIN_TABPOX.'transfervoucherdetail A INNER JOIN '.WEB_ADMIN_TABPOX.'transfervoucher B ON A.transfervoucher_id=B.transfervoucher_id WHERE A.accounttitle_id in ('.$inrrs['addorreduce'].') and B.agencyid='.$_SESSION["currentorgan"].' and '.$timecondition2); 				
				
				}
				
				$thismonth['thismonth']=$thismonth['thismonth']?$thismonth['thismonth']:0;
				$reducethismonth['reducethismonth']=$reducethismonth['reducethismonth']?$reducethismonth['reducethismonth']:0;
				$t -> set_var('thismonth',$thismonth['thismonth']-2*$reducethismonth['$reducethismonth']);	
				
				 
				$thisyear['thisyear']=$thisyear['thisyear']?$thisyear['thisyear']:0;
				$reducethisyear['reducethisyear']=$reducethisyear['reducethisyear']?$reducethisyear['reducethisyear']:0;
				$t -> set_var('addup',$thisyear['thisyear']-2*$reducethisyear['reducethisyear']);
				
				$t -> set_var('printbill','<a href="?action=print&id='.$inrrs['incomestatementtype_id'].'" target="_blank">打印</a>');
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['incomestatementtype_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['incomestatementtype_id']));			
				$t -> parse('m','main',true);
			}
			$inrs -> Close();	
		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 	
		$t -> set_var('enddate',$_POST["enddate"]);
		$t -> set_var('bgdate',$_POST["bgdate"]);
		
		$t -> set_var('year',substr($_POST["enddate"],0,4));
		$t -> set_var('month',substr($_POST["enddate"],5,2));
		$t -> set_var('hangye_name','有限责任公司');
		
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');		
		$t -> set_var('add',$this -> getAddStr('img'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	
 
	
	
	function goDispAppend(){

		$t = new Template('../template/finace');
		$t -> set_file('f','incomestatementtype_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
			
		//$t -> set_var('incomestatementtype_no',"");
		$Prefix='VH';
		$agency_no=$_SESSION["agency_no"].date('ym',time());
		$table=WEB_ADMIN_TABPOX.'incomestatementtype';
		$column='incomestatementtype_no';
		$number=5;
		$id='incomestatementtype_id';	
		
		$t -> set_var('incomestatementtype_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));	
		$t -> set_var('suppliers_no',"");	
		$t -> set_var('creattime',date('Y-m-d H:i:s',time()));	
		$t -> set_var('cloan',"0");
		$t -> set_var('clend',"0");
		$t -> set_var('totalloan',"0");
		$t -> set_var('totallend',"0");		
		$t -> set_var('cobjectid',"");
		$t -> set_var('cobject_name',"");

		$t -> set_var('employee_name',"");	
		$t -> set_var('employee_id',"");	
		$t -> set_var('date',date('Y-m-d',time()));	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");
		$t -> set_var('comdisplay',"none");		
		$t -> set_var('memo',"");
		$t -> set_var('man',$this -> dbObj -> getone('select B.employee_name from '.WEB_ADMIN_TABPOX.'user A INNER JOIN '.WEB_ADMIN_TABPOX.'employee B ON A.employee_id=B.employee_id  where A.userid = '.$this->getUid()));
		$t -> set_var('accounttitlelist',$this->selectlist('accounttitle','accounttitle_id','accounttitle_name',''));	
		$t -> set_var('objecttypelist',$this->selectlist('objecttype','objecttype_id','objecttype_name',''));	
		$t -> set_var('acount',"");	
		$t -> set_var('recordcount',"0");	
		$t -> set_var('cstandardunit',"");	
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
		$t -> set_var('cdiscount',$this->getValue('purchdiscount'));
 
		$t -> set_var('addprodisplay',"display:none");	
		$t -> set_var('userid',$this->getUid());		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			 
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'incomestatementtype WHERE incomestatementtype_id = '.$updid);
			
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
		$t -> set_var('clend',"");	
		$t -> set_var('cmemo',"");	
		$t -> set_var('cloan',"");	
		$t -> set_var('cobject_name',"");	
		$t -> set_var('cobjectid',"");	
		$t -> set_var('employee_id',"");	
		$t -> set_var('employee_name',"");	
			
		//添加消耗品
		$t -> set_var('caction','add');
		$t -> set_var('cupdid','');
		 
		$t -> set_var('cincomestatementtypeprice',0);
		
 					
		//设置消耗品列表
			
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'incomestatementtypedetail  where incomestatementtype_id  ='.$updid);
			$totallend=0;
			$totalloan=0;
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$totallend=$totallend+$inrrs['lend'];
				$totalloan=$totalloan+$inrrs['loan'];
				$t -> set_var('numbers',$inrrs['number']);
				$data1=$this -> dbObj -> getrow('select * from '.WEB_ADMIN_TABPOX.'accounttitle   where accounttitle_id ='.$inrrs["accounttitle_id"]);
				$t -> set_var($data1);
				 
				 $objecttype=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id ='.$inrrs["objecttype_id"]);
				 $t -> set_var('objecttype_name',$objecttype['objecttype_name']);
				$object=$this -> dbObj -> getrow('select *  from '.WEB_ADMIN_TABPOX.$objecttype["objecttypetable"].'  where '.$objecttype["objecttypetable"].'_id ='.$inrrs["objectid"]);
				 
				$t -> set_var('object_name',$object[$objecttype["objecttypetable"].'_name']);
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();			
			$acount = &$this -> dbObj -> GetOne('select count(*) as acount from '.WEB_ADMIN_TABPOX.'incomestatementtypedetail  where incomestatementtype_id  ='.$updid);
			$t -> set_var('accounttitlelist',$this->selectlist('accounttitle','accounttitle_id','accounttitle_name',''));	
			$t -> set_var('objecttypelist',$this->selectlist('objecttype','objecttype_id','objecttype_name',''));	
			$t -> set_var('recordcount',$acount);
			$t -> set_var('totallend',$totallend);	
			$t -> set_var('totalloan',$totalloan);	
		// 修改消耗品
		 
		    if($_GET['cincomestatementtypedetail_id']!=''){
			//$t -> set_var('cs');
		   $t -> set_var('caction','upd');	
            
			$inrs2 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'incomestatementtypedetail  where incomestatementtypedetail_id ='.$_GET['cincomestatementtypedetail_id']);
			//$t -> set_var($inrs);
			$t -> set_var('accounttitlelist',$this->selectlist('accounttitle','accounttitle_id','accounttitle_name',$inrs2['accounttitle_id']));	
			$t -> set_var('objecttypelist',$this->selectlist('objecttype','objecttype_id','objecttype_name',$inrs2['objecttype_id']));	
		    $t -> set_var('cupdid',$inrs2['produce_id']);	
			$t -> set_var('proupdid',$_GET['cincomestatementtypedetail_id']);	
			$t -> set_var('cmemo',$inrs2['memo']);	
			$t -> set_var('clend',$inrs2['lend']);
			$t -> set_var('cloan',$inrs2['loan']);
			
				$objecttype1=$this -> dbObj -> GetRow('select *  from '.WEB_ADMIN_TABPOX.'objecttype  where objecttype_id ='.$inrs2["objecttype_id"]);
 
				$object1=$this -> dbObj -> getrow('select *  from '.WEB_ADMIN_TABPOX.$objecttype1["objecttypetable"].'  where '.$objecttype1["objecttypetable"].'_id ='.$inrs2["objectid"]);
 			$t -> set_var('cobjectid',$object1[$objecttype1['objecttypetable']."_id"]);

			 
				$t -> set_var('cobject_name',$object1[$objecttype1["objecttypetable"].'_name']);			
			
			$inrs3 = &$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'produce   where produce_id ='.$inrs2['produce_id']);
			$t -> set_var('cproduce_no',$inrs3['produce_no']);
			$t -> set_var('cproduce_name',$inrs3['produce_name']);
			$t -> set_var('cproduce_id',$inrs3['produce_id']);
			$t -> set_var('ccode',$inrs3['code']);
			$t -> set_var('cprice',$inrs3['price']);
				
			$t -> set_var('cdiscount',$inrs2['discount']);
			$t -> set_var('cincomestatementtypeprice',$inrs2['incomestatementtypeprice']);
			//$t -> set_var('cviceunitnumber',$inrs3['viceunitnumber']);
			//$t -> parse('cs','consumables',true);
			//$inrs2 -> Close();	
			}
			
			 
		//$t -> set_var('procataloglist',$this ->selectlist('procatalog','category_id','category_name',$data['categoryid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));	


		$t -> set_var('memo',$data['memo']);//因为与明细的memo冲突，故放到这里。
		}
		//$t -> set_var('categorynamelist',$this ->selectlist('servicecategory','category_id','category_name',$data['categoryid']));		
		 
		
		//$t -> set_var('ml',"");	
		
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}
	
	
	
	
		function selectlist($table,$id,$name,$selectid=0){
	
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table  );
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
	  if($_GET['incomestatementtypedetail_id']==''){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'incomestatementtype WHERE incomestatementtype_id in('.$delid.')');
		}else{
		 
		 $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'incomestatementtypedetail WHERE incomestatementtypedetail_id in('.$_GET['incomestatementtypedetail_id'].')');
		
		}
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
			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."incomestatementtype` (`incomestatementtype_no`,`date`,`agencyid`,`creattime`,`man`) VALUES ('" .$_POST["incomestatementtype_no"]."','".$_POST["date"]."','".$_SESSION["currentorgan"]."','".$_POST["creattime"]."', '".$_POST["man"]."')");
 
			$id = $this -> dbObj -> Insert_ID();
//echo "INSERT INTO `".WEB_ADMIN_TABPOX."incomestatementtype` (`incomestatementtype_no`,`incomestatementtype_time`,`acount`,`warehouse_id`,`suppliers_id`,`employee_id`,`memo`,`agencyid`,`creattime`,`man`) VALUES ('" .$_POST["incomestatementtype_no"]."','".$_POST["incomestatementtype_time"]."', '" .$_POST["acount"]."','".$_POST["warehouse_id"]."', '" .$_POST["suppliers_id"]."','".$_POST["employee_id"]."', '" .$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["creattime"]."', '".$_POST["man"]."')";
//echo $_POST['cproduce_id'].$_POST['cnumber'];
 
if($_POST['subtype']=='2'){			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."incomestatementtypedetail` (`incomestatementtype_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','".$_POST["caccounttitle_id"]."','".$_POST["cobjecttype_id"]."','".$_POST["cobjectid"]."','".$_POST["clend"]."','".$_POST["cloan"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')");	
			//  echo "INSERT INTO `".WEB_ADMIN_TABPOX."incomestatementtypedetail` (`incomestatementtype_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','".$_POST["caccounttitle_id"]."','".$_POST["cobjecttype_id"]."','".$_POST["cobjectid"]."','".$_POST["clend"]."','".$_POST["cloan"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')";
			}
			
		$acount = &$this -> dbObj -> GetOne('select sum(totalacount) from '.WEB_ADMIN_TABPOX.'incomestatementtypedetail   where incomestatementtype_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."incomestatementtype` SET `acount` =".$acount." where incomestatementtype_id=".$id) ;	
			
			exit("<script>alert('".$info."成功');window.location.href='incomestatementtype.php?action=upd&updid=".$id."';</script>");
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."incomestatementtype` SET `incomestatementtype_no` = '".$_POST["incomestatementtype_no"]."',incomestatementtype_time='".$_POST["incomestatementtype_time"]."', warehouse_id='".$_POST["warehouse_id"]."', suppliers_id='" .$_POST["suppliers_id"]."',employee_id='".$_POST["employee_id"]."', memo='" .$_POST["memo"]."', man='".$_POST["man"]."' WHERE incomestatementtype_id =".$id);
			//echo "UPDATE `".WEB_ADMIN_TABPOX."incomestatementtype` SET `incomestatementtype_no` = '".$_POST["incomestatementtype_no"]."',incomestatementtype_time='".$_POST["incomestatementtype_time"]."', warehouse_id='".$_POST["warehouse_id"]."', suppliers_id='" .$_POST["suppliers_id"]."',employee_id='".$_POST["employee_id"]."', memo='" .$_POST["memo"]."', man='".$_POST["man"]."' WHERE incomestatementtype_id =".$id;

		//echo $_POST["con_act"];
		
 		if($_POST["con_act"]=='upd'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."incomestatementtypedetail` SET `accounttitle_id` = '".$_POST["caccounttitle_id"]."',`objecttype_id` = '".$_POST["cobjecttype_id"]."',`objectid` = '".$_POST["cobjectid"]."',lend='".$_POST["clend"]."',loan='".$_POST["cloan"]."',memo='".$_POST["cmemo"]."' WHERE incomestatementtypedetail_id  =".$_POST['proupdid']);		
		//echo "UPDATE `".WEB_ADMIN_TABPOX."incomestatementtypedetail` SET `produce_id` = '".$_POST["cproduce_id"]."',`number` = '".$_POST["cnumber"]."',`memo` = '".$_POST["cmemo"]."' WHERE incomestatementtypedetail_id  =".$_POST['proupdid'];
		$acount = &$this -> dbObj -> GetOne('select sum(totalacount) from '.WEB_ADMIN_TABPOX.'incomestatementtypedetail   where incomestatementtype_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."incomestatementtype` SET `acount` =".$acount." where incomestatementtype_id=".$id) ;	
		
		exit("<script>alert('".$info."商品成功');window.location.href='incomestatementtype.php?action=upd&updid=".$id."';</script>");		
		}else{
if($_POST['subtype']=='2'){			
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."incomestatementtypedetail` ( `incomestatementtype_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','".$_POST["caccounttitle_id"]."','".$_POST["cobjecttype_id"]."','".$_POST["cobjectid"]."','".$_POST["clend"]."','".$_POST["cloan"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')");	
			// echo "INSERT INTO `".WEB_ADMIN_TABPOX."incomestatementtypedetail` (`incomestatementtype_id`, `accounttitle_id`, `objecttype_id`, `objectid`, `lend`, `loan`, `memo`, `agencyid`) VALUES ('".$id."','".$_POST["caccounttitle_id"]."','".$_POST["cobjecttype_id"]."','".$_POST["cobjectid"]."','".$_POST["clend"]."','".$_POST["cloan"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')";
			 
			
		//$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."serviceconsume` SET `produce_id` = '".$_POST["produce_id"]."',`std_consumption` = '".$_POST["std_consumption"]."',`memo` = '".$_POST["memo"]."',`incomestatementtype` = '".$_POST["incomestatementtype"]."' WHERE incomestatementtype_id =".$id);
		//echo "INSERT INTO `".WEB_ADMIN_TABPOX."consumables` (incomestatementtype_id,produce_id,std_consumption,memo,agencyid) VALUES ('".$id."','".$_POST["cproduce_id"]."','".$_POST["cstd_consumption"]."','".$_POST["cmemo"]."','".$_SESSION["currentorgan"]."')";
		$info = '添加项目';

		}
		$acount = &$this -> dbObj -> GetOne('select sum(totalacount) from '.WEB_ADMIN_TABPOX.'incomestatementtypedetail   where incomestatementtype_id  ='.$id);
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."incomestatementtype` SET `acount` =".$acount." where incomestatementtype_id=".$id) ;	
		
		}
		//echo $_POST['issave'];
		if($_POST['issave']=='1'){
		$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."incomestatementtype` SET `status`=0  WHERE incomestatementtype_id =".$id);//0未提交，1已提交2已收部分3全部收完
			exit("<script>alert('保存成功');window.location.href='incomestatementtype.php';</script>");
		}	
		}
//$this -> quit($info.'成功！');

		//修改总金额

		$this -> quit($info.'成功！');

	}

	function intnonull($int){
	if ($int=="")$int=0;
		return $int;
	}

	function goModify(){
		$this -> goAppend();
	}
function makeno($Prefix,$agency_no,$table,$column,$number,$id){
$nostr = $this -> dbObj ->GetRow("select * from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by ".$id." desc limit 1");
 
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