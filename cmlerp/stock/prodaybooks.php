<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
	function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='print')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this ->print1();
        }else{
            parent::Main();
        }
    }	
	function print1(){
		//定义模板
		$t = new Template('../template/stock');
		$t -> set_file('f','prodaybooks_print.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]
		$t -> set_block('f','main','m');		
		$t -> set_block('main','mainlist','ml');
		//$t -> set_block('f','mainlist','ml');		
		$visiblenonestock=$_GET["visiblenonestock"];
		$warehouse_id=$_GET['warehouse_id'];
  		if($_GET['visiblenonestock']=='1'){	
		
		$t -> set_var('visiblenonestockechecked','checked');
		$t -> set_var('visiblenonestockvalue','1');
		$ostock="s.stocknumber>=-1000";
		}else{
		$t -> set_var('visiblenonestockechecked','');
		$t -> set_var('visiblenonestockvalue','0');		
		$ostock="s.stocknumber>0";
		}
		$warehouse_id=$_GET["warehouse_id"];
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$warehouse_id=$_GET['warehouse_id'];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.'="'.$keywords.'"';}else{$condition=$category.'="'.$keywords.'"';}
		}
		$bgno=$_GET['bgno'];
		$endno=$_GET['endno'];
		$bgdate=$_GET['bgdate'];
		$enddate=$_GET['enddate'];
		$bgdate=$bgdate==''?'2010-11-01':$bgdate;
		$enddate=$enddate==''?date('Y-m-d',time()):$enddate;
		$t -> set_var('bgno',$bgno);
		$t -> set_var('endno',$endno);
		$t -> set_var('bgdate',$bgdate);
		
		$t -> set_var('enddate',$enddate);
		
 
		if($bgno<>''&&$endno<>''){
		$ftable='produce';
		$condition=$condition==''?' B.code  between "'.$bgno.'"  and  "'.$endno.'"':$condition.' and B.code  between "'.$bgno.'"  and  "'.$endno.'"';
		}
		if($bgdate<>''&&$enddate<>''){
		$condition=$condition==''?' date between "'.$bgdate.'" and  "'.$enddate.'"':$condition.' and  date between "'.$bgdate.'" and  "'.$enddate.'"';
		}

		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:100;
			$offset = $pageid>0?($pageid-1)*$psize:0;

			//设置分类
			$t -> set_var('m');	
			//for($j=1;$j<3;$j++){
			if($bgno<>''&&$endno<>''){//
			$sql3='select * from '.WEB_ADMIN_TABPOX.'prodaybooks s INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON s.produce_id=B.produce_id  where  s.agencyid ='.$_SESSION["currentorgan"].' and B.code  between "'.$bgno.'"  and  "'.$endno.'" and s.date  between "'.$bgdate.'"  and  "'.$enddate.'" and '.$ostock.' group by s.produce_id';
			 
			}else if($keywords<>''){
				// echo 'select * from '.WEB_ADMIN_TABPOX.'produce where  '.$condition.'  and agencyid ='.$_SESSION["currentorgan"];
				//$inrs3=$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'produce where  '.$condition.'  and agencyid ='.$_SESSION["currentorgan"]);
				$sql3='select * from '.WEB_ADMIN_TABPOX.'prodaybooks s INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON s.produce_id=B.produce_id  where  s.agencyid ='.$_SESSION["currentorgan"].' and B.code  between "'.$bgno.'"  and  "'.$endno.'" and s.date  between "'.$bgdate.'"  and  "'.$enddate.'" and '.$ostock.' group by s.produce_id';
				//echo 'select * from '.WEB_ADMIN_TABPOX.'produce where  code = "'.$bgno.'"  and agencyid ='.$_SESSION["currentorgan"];
			}else{
				
				$sql3='select * from '.WEB_ADMIN_TABPOX.'prodaybooks s INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON s.produce_id=B.produce_id  where  s.agencyid ='.$_SESSION["currentorgan"].'   and s.date  between "'.$bgdate.'"  and  "'.$enddate.'" and '.$ostock.' group by s.produce_id';	
				//echo 'select * from '.WEB_ADMIN_TABPOX.'prodaybooks s INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON s.produce_id=B.produce_id  where  s.agencyid ='.$_SESSION["currentorgan"].'   and s.date  between "'.$bgdate.'"  and  "'.$enddate.'" group by s.produce_id';
				//$inrs3=$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'produce where  agencyid ='.$_SESSION["currentorgan"]);
			}
			 
			$inrs3=$this -> dbObj -> Execute($sql3." LIMIT ".$offset." , ".$psize);
			$condition=$condition==''?'warehouse_id="'.$warehouse_id.'"':$condition.' and warehouse_id="'.$warehouse_id.'"';
			//echo 'select * from '.WEB_ADMIN_TABPOX.'produce where  code  between "'.$bgno.'"  and  "'.$endno.'"  and agencyid ='.$_SESSION["currentorgan"];
			$inrs31=$this -> dbObj -> Execute($sql3);
			$count=$inrs31->RecordCount();
					
			while ($inrrs3 = &$inrs3 -> FetchRow()) {
			
			$tsrnumber=0;	
			$sracount=0;
			$tfcacount=0;
			$tfcnumber=0;
			$tstocknumber=0;
			$tstockprice=0;
			$tstockbalance=0;			
			//for($j=1;$j<3;$j++){
			//期初数据
			
			$inrs4=$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'prodaybooks A INNER JOIN '.WEB_ADMIN_TABPOX."produce B on A.produce_id =B.produce_id  where  B.code='".$inrrs3['code']."' and A.date<'".$bgdate."' and  A.agencyid =".$_SESSION["currentorgan"]." and A.warehouse_id=".$warehouse_id." order by prodaybooks_id desc");
			//echo 'select * from '.WEB_ADMIN_TABPOX.'prodaybooks A INNER JOIN '.WEB_ADMIN_TABPOX."produce B on A.produce_id =B.produce_id  where  B.code='".$inrrs3['code']."' and A.date<'".$bgdate."' and  A.agencyid =".$_SESSION["currentorgan"]." order by prodaybooks_id desc";
				$bgstocknumber=$inrs4['stocknumber']==''?0:$inrs4['stocknumber'];
				$bgstockbalance=$inrs4['stockbalance'];
				$bgstockprice=$bgstockbalance/$bgstocknumber;
			//期末数据	
			$inrs5=$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'prodaybooks A INNER JOIN '.WEB_ADMIN_TABPOX."produce B on A.produce_id =B.produce_id  where  B.code='".$inrrs3['code']."' and A.date<='".$enddate."' and  A.agencyid =".$_SESSION["currentorgan"]."  and A.warehouse_id=".$warehouse_id." order by prodaybooks_id desc");	
		 
				$tstocknumber=$inrs5['stocknumber']==''?0:$inrs5['stocknumber'];
				$tstockbalance=$inrs5['stockbalance'];
				$tstockprice=$tstockbalance/$tstocknumber;
				
				$t -> set_var('code',$inrrs3['code']);
				$t -> set_var('produce_name',$inrrs3['produce_name']);
				
			$t -> set_var('ml');
		
		
			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'prodaybooks where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;
			$sql='select *,s.memo as memo1 from '.WEB_ADMIN_TABPOX.'prodaybooks s INNER JOIN '.WEB_ADMIN_TABPOX.$ftable." B on s.produce_id =B.produce_id  where ".$condition."  and  B.code='".$inrrs3['code']."' and  s.agencyid =".$_SESSION["currentorgan"] ;
			}else if($ftable<>''){
			$sql='select *,s.memo as memo1 from '.WEB_ADMIN_TABPOX.'prodaybooks s INNER JOIN '.WEB_ADMIN_TABPOX.$ftable." B on s.produce_id =B.produce_id  where ".$condition."  and  B.code='".$inrrs3['code']."' and  s.agencyid =".$_SESSION["currentorgan"] ;
			 
			}else{
			$sql='select *,s.memo as memo1 from '.WEB_ADMIN_TABPOX.'prodaybooks s INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON s.produce_id=B.produce_id  where  s.agencyid ='.$_SESSION["currentorgan"].' and B.code="'.$inrrs3["code"].'"';
			 
			}
			
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  prodaybooks_id DESC ");
			$inrrs=&$this -> dbObj ->GetArray($sql." ORDER BY  prodaybooks_id DESC ");
			
			$inrrscount=sizeof($inrrs);
 			
			$result = &$this -> dbObj -> Execute($sql);		
			//$count=$result->RecordCount();
			
	
           	$t -> set_var('recordcount',$count);

				$i=1;
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'produce  where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	//while ($inrrs = &$inrs -> FetchRow()) {
				$tsracount=0;
				for($k=$inrrscount-1;$k>=0;$k--){
				if($i==1){
				//$bgstocknumber=$inrrs['stocknumber']-$inrrs['addnumber'];
				//$bgstockbalance=$inrrs['stockbalance']-$inrrs['addacount'];
				//$bgstockprice=$bgstockbalance/$bgstocknumber;
				$i=$i+1;
				}
				//$tstocknumber=$inrrs['stocknumber'];
				//$tstockprice=$inrrs['stockprice'];
				//$tstockbalance=$inrrs['stockbalance'];
				//$tstockprice=$tstockbalance/$tstocknumber;
				$t -> set_var('warehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse where warehouse_id ='.$inrrs[$k]["warehouse_id"]));
				$produce=$this -> dbObj -> Getrow('select * from '.WEB_ADMIN_TABPOX.'produce where produce_id ='.$inrrs[$k]["produce_id"]);
				$t -> set_var($produce);
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs[$k]['prodaybooks_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs[$k]['prodaybooks_id']));				
				$t -> set_var($inrrs[$k]);
				$t -> set_var('memo',$inrrs[$k]['memo1']);

				if($inrrs[$k]['addnumber']>0&&$inrrs[$k]['addacount']>=0){//进货
				 $t -> set_var('srnumber',$inrrs[$k]['addnumber']);
				 $t -> set_var('sracount',sprintf ("%01.2f",$inrrs[$k]['addacount']));
				 $t -> set_var('srprice',sprintf ("%01.2f",$inrrs[$k]['addacount']/$inrrs[$k]['addnumber']));
				 $t -> set_var('fcnumber','');
				 $t -> set_var('fcacount','');
				 $t -> set_var('fcprice','');
				$tsracount=$tsracount+$inrrs[$k]['addacount'];
				$tsrnumber=$tsrnumber+$inrrs[$k]['addnumber'];
				}else{
				 $t -> set_var('fcnumber',-$inrrs[$k]['addnumber']);
				 $t -> set_var('fcacount',sprintf ("%01.2f",-$inrrs[$k]['addacount']));
				  $t -> set_var('fcprice',sprintf ("%01.2f",$inrrs[$k]['addacount']/$inrrs[$k]['addnumber']));
				  $t -> set_var('srnumber','');
				  $t -> set_var('srprice','');
				  $t -> set_var('sracount','');
				$tfcacount=$tfcacount+$inrrs[$k]['addacount'];
				$tfcnumber=$tfcnumber+$inrrs[$k]['addnumber'];
				}

				 
				
				 //$inrrs[$k]['stocknumber']=$inrrs[$k]['stocknumber']==''?0:$inrrs[$k]['stocknumber'];
				 //$inrrs[$k]['stockbalance']=$inrrs[$k]['stockbalance']==''?0:$inrrs[$k]['stockbalance'];
				 $t -> set_var('stockprice',sprintf ("%01.2f",$inrrs[$k]['stockbalance']/$inrrs[$k]['stocknumber']));
				$fromtype=array("sell","sell","sell","sell","purchase","purchreturn","takestock","lossregister","adjustment","moveproduce");
				$fromtype=array("sell","sell","sell","sell","purchase","purchreturn","takestock","lossregister","adjustment","moveproduce");
				$viewbillfunction=array("viewbill","viewbill","viewbill","viewbill","viewpurchbill","viewpurchreturnbill","viewcheckstockbill","viewlossregisterbill","viewadjustmentbill","viewmoveproducebill");
				 $billname=array("销货单","销货单","销货单","销货单","进货单","采购退货单","盘点单","报损单","调整单","调拨单");
				$billno=$this->dbObj -> GetOne("SELECT ".$fromtype[$inrrs[$k]['billtype']]."_no FROM ".WEB_ADMIN_TABPOX.$fromtype[$inrrs[$k]['billtype']]." WHERE ".$fromtype[$inrrs[$k]['billtype']]."_id= ".$inrrs[$k]['billid']);
 
				$t -> set_var('viewbill','<a href=#  onclick="'.$viewbillfunction[$inrrs[$k]["billtype"]].'('.$inrrs[$k]["billid"].')">'.$billno.'</a>');
				$t -> set_var('code',$inrrs3['code']);
				$t -> set_var('produce_name',$inrrs3['produce_name']);
				$t -> parse('ml','mainlist',true);
			}
			
			$inrs -> Close();	
				$t -> set_var('tsrnumber',$tsrnumber);
				 $t -> set_var('tsracount',sprintf("%01.2f",$tsracount));
				 $t -> set_var('tsrprice',sprintf("%01.2f",$tsracount/$tsrnumber));
				 
				  $t -> set_var('tfcnumber',-$tfcnumber);
				 $t -> set_var('tfcacount',sprintf("%01.2f",-$tfcacount));
				 $t -> set_var('tfcprice',sprintf("%01.2f",$tfcacount/$tfcnumber));	
				 
				 
				 $t -> set_var('bgstocknumber',$bgstocknumber);
				 $t -> set_var('bgstockprice',sprintf("%01.2f",$bgstockprice));
				 $t -> set_var('bgstockbalance',sprintf("%01.2f",$bgstockbalance));
				 
				 $t -> set_var('tstocknumber',$tstocknumber);
				 $t -> set_var('tstockprice',sprintf("%01.2f",$tstockprice));
				 $t -> set_var('tstockbalance',sprintf("%01.2f",$tstockbalance));
				 
				  
				if($inrrscount>0&&$tstocknumber>0){ //有发生,有库存
				
				//如果库存为零。如果显示
				$t -> parse('m','main',true);
				}else if($_POST['visiblenonestockvalue']=='1'){
				$t -> parse('m','main',true);
				}else if($tstocknumber>0){//有库存
				$t -> parse('m','main',true);
				}else if($inrrscount>0){//有发生
				$t -> parse('m','main',true);
				}
			}
		$t -> set_var('pagelist',$this -> page('?bgno='.$bgno.'&endno='.$endno.'&bgdate='.$bgdate.'&enddate='.$enddate.'&warehouse_id='.$warehouse_id.'&visiblenonestock='.$visiblenonestock,$count,$psize,$pageid));				
			$t -> set_var('recordcount',$count);
		$t -> set_var('warehouselist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$_GET['warehouse_id']));	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');
		$t -> set_var('category',$category);
		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 		
		$t -> set_var('keywords',$keywords);
		$t -> set_var('ftable',$ftable);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}	
	function disp(){
		//定义模板
		 print_r($_POST['visiblenonestock']);
		$t = new Template('../template/stock');
		$t -> set_file('f','prodaybooks.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]
		$t -> set_block('f','main','m');		
		$t -> set_block('main','mainlist','ml');
		//$t -> set_block('f','mainlist','ml');	
		$visiblenonestock=$_GET["visiblenonestock"];
		$warehouse_id=$_GET['warehouse_id'];
  		if($_GET['visiblenonestock']=='1'){	
		
		$t -> set_var('visiblenonestockechecked','checked');
		$t -> set_var('visiblenonestockvalue','1');
		$ostock="s.stocknumber>=-1000";
		}else{
		$t -> set_var('visiblenonestockechecked','');
		$t -> set_var('visiblenonestockvalue','0');		
		$ostock="s.stocknumber>0";
		}
		$warehouse_id=$_GET["warehouse_id"];
        $category=$_GET["category"];
		$keywords=$_GET["keywords"];
		$ftable=$_GET["ftable"];
		$warehouse_id=$_GET['warehouse_id'];
		$condition='';
		if($category<>''&&$keywords<>''){
		if($ftable==''){$condition=$category.'="'.$keywords.'"';}else{$condition=$category.'="'.$keywords.'"';}
		}
		$bgno=$_GET['bgno'];
		$endno=$_GET['endno'];
		$bgdate=$_GET['bgdate'];
		$enddate=$_GET['enddate'];
		$bgdate=$bgdate==''?'2010-11-01':$bgdate;
		$enddate=$enddate==''?date('Y-m-d',time()):$enddate;
		$t -> set_var('bgno',$bgno);
		$t -> set_var('endno',$endno);
		$t -> set_var('bgdate',$bgdate);
		
		$t -> set_var('enddate',$enddate);
		
 
		if($bgno<>''&&$endno<>''){
		$ftable='produce';
		$condition=$condition==''?' B.code  between "'.$bgno.'"  and  "'.$endno.'"':$condition.' and B.code  between "'.$bgno.'"  and  "'.$endno.'"';
		}
		if($bgdate<>''&&$enddate<>''){
		$condition=$condition==''?' date between "'.$bgdate.'" and  "'.$enddate.'"':$condition.' and  date between "'.$bgdate.'" and  "'.$enddate.'"';
		}

		
			$pageid=$_GET[pageid];
			$pageid=$pageid?$pageid:1;
			$pageid = intval($pageid);
			$psize=$this->getValue('pagesize');
			$psize =$psize?$psize:100;
			$offset = $pageid>0?($pageid-1)*$psize:0;

			//设置分类
			$t -> set_var('m');	
			//for($j=1;$j<3;$j++){
			if($bgno<>''&&$endno<>''){//
			$sql3='select * from '.WEB_ADMIN_TABPOX.'prodaybooks s INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON s.produce_id=B.produce_id  where  s.agencyid ='.$_SESSION["currentorgan"].' and B.code  between "'.$bgno.'"  and  "'.$endno.'" and s.date  between "'.$bgdate.'"  and  "'.$enddate.'" and '.$ostock.' group by s.produce_id';
			 
			}else if($keywords<>''){
				// echo 'select * from '.WEB_ADMIN_TABPOX.'produce where  '.$condition.'  and agencyid ='.$_SESSION["currentorgan"];
				//$inrs3=$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'produce where  '.$condition.'  and agencyid ='.$_SESSION["currentorgan"]);
				$sql3='select * from '.WEB_ADMIN_TABPOX.'prodaybooks s INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON s.produce_id=B.produce_id  where  s.agencyid ='.$_SESSION["currentorgan"].' and B.code  between "'.$bgno.'"  and  "'.$endno.'" and s.date  between "'.$bgdate.'"  and  "'.$enddate.'" and '.$ostock.' group by s.produce_id';
				//echo 'select * from '.WEB_ADMIN_TABPOX.'produce where  code = "'.$bgno.'"  and agencyid ='.$_SESSION["currentorgan"];
			}else{
				
				$sql3='select * from '.WEB_ADMIN_TABPOX.'prodaybooks s INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON s.produce_id=B.produce_id  where  s.agencyid ='.$_SESSION["currentorgan"].'   and s.date  between "'.$bgdate.'"  and  "'.$enddate.'" and '.$ostock.' group by s.produce_id';	
				//echo 'select * from '.WEB_ADMIN_TABPOX.'prodaybooks s INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON s.produce_id=B.produce_id  where  s.agencyid ='.$_SESSION["currentorgan"].'   and s.date  between "'.$bgdate.'"  and  "'.$enddate.'" group by s.produce_id';
				//$inrs3=$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'produce where  agencyid ='.$_SESSION["currentorgan"]);
			}
			$inrs3=$this -> dbObj -> Execute($sql3." LIMIT ".$offset." , ".$psize);
			$condition=$condition==''?'warehouse_id="'.$warehouse_id.'"':$condition.' and warehouse_id="'.$warehouse_id.'"';
			//echo 'select * from '.WEB_ADMIN_TABPOX.'produce where  code  between "'.$bgno.'"  and  "'.$endno.'"  and agencyid ='.$_SESSION["currentorgan"];
			$inrs31=$this -> dbObj -> Execute($sql3);
			$count=$inrs31->RecordCount();
				
			while ($inrrs3 = &$inrs3 -> FetchRow()) {
			
			$tsrnumber=0;	
			$sracount=0;
			$tfcacount=0;
			$tfcnumber=0;
			$tstocknumber=0;
			$tstockprice=0;
			$tstockbalance=0;			
			//for($j=1;$j<3;$j++){
			//期初数据
			
			$inrs4=$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'prodaybooks A INNER JOIN '.WEB_ADMIN_TABPOX."produce B on A.produce_id =B.produce_id  where  B.code='".$inrrs3['code']."' and A.date<'".$bgdate."' and  A.agencyid =".$_SESSION["currentorgan"]." and A.warehouse_id=".$warehouse_id." order by prodaybooks_id desc");
			//echo 'select * from '.WEB_ADMIN_TABPOX.'prodaybooks A INNER JOIN '.WEB_ADMIN_TABPOX."produce B on A.produce_id =B.produce_id  where  B.code='".$inrrs3['code']."' and A.date<'".$bgdate."' and  A.agencyid =".$_SESSION["currentorgan"]." order by prodaybooks_id desc";
				$bgstocknumber=$inrs4['stocknumber']==''?0:$inrs4['stocknumber'];
				$bgstockbalance=$inrs4['stockbalance'];
				$bgstockprice=$bgstockbalance/$bgstocknumber;
			//期末数据	
			$inrs5=$this -> dbObj -> GetRow('select * from '.WEB_ADMIN_TABPOX.'prodaybooks A INNER JOIN '.WEB_ADMIN_TABPOX."produce B on A.produce_id =B.produce_id  where  B.code='".$inrrs3['code']."' and A.date<='".$enddate."' and  A.agencyid =".$_SESSION["currentorgan"]."  and A.warehouse_id=".$warehouse_id." order by prodaybooks_id desc");	
		 
				$tstocknumber=$inrs5['stocknumber']==''?0:$inrs5['stocknumber'];
				$tstockbalance=$inrs5['stockbalance'];
				$tstockprice=$tstockbalance/$tstocknumber;
				
				$t -> set_var('code',$inrrs3['code']);
				$t -> set_var('produce_name',$inrrs3['produce_name']);
				
			$t -> set_var('ml');
		
		
			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'prodaybooks where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;
			$sql='select *,s.memo as memo1 from '.WEB_ADMIN_TABPOX.'prodaybooks s INNER JOIN '.WEB_ADMIN_TABPOX.$ftable." B on s.produce_id =B.produce_id  where ".$condition."  and  B.code='".$inrrs3['code']."' and  s.agencyid =".$_SESSION["currentorgan"] ;
			}else if($ftable<>''){
			$sql='select *,s.memo as memo1 from '.WEB_ADMIN_TABPOX.'prodaybooks s INNER JOIN '.WEB_ADMIN_TABPOX.$ftable." B on s.produce_id =B.produce_id  where ".$condition."  and  B.code='".$inrrs3['code']."' and  s.agencyid =".$_SESSION["currentorgan"] ;
			 
			}else{
			$sql='select *,s.memo as memo1 from '.WEB_ADMIN_TABPOX.'prodaybooks s INNER JOIN '.WEB_ADMIN_TABPOX.'produce B ON s.produce_id=B.produce_id  where  s.agencyid ='.$_SESSION["currentorgan"].' and B.code="'.$inrrs3["code"].'"';
			 
			}
			 
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  prodaybooks_id DESC ");
			$inrrs=&$this -> dbObj ->GetArray($sql." ORDER BY  prodaybooks_id DESC ");
			
			$inrrscount=sizeof($inrrs);
 			
			$result = &$this -> dbObj -> Execute($sql);		
			//$count=$result->RecordCount();
			
	
           	$t -> set_var('recordcount',$count);

				$i=1;
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'produce  where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	//while ($inrrs = &$inrs -> FetchRow()) {
				$tsracount=0;
				for($k=$inrrscount-1;$k>=0;$k--){
				if($i==1){
				//$bgstocknumber=$inrrs['stocknumber']-$inrrs['addnumber'];
				//$bgstockbalance=$inrrs['stockbalance']-$inrrs['addacount'];
				//$bgstockprice=$bgstockbalance/$bgstocknumber;
				$i=$i+1;
				}
				//$tstocknumber=$inrrs['stocknumber'];
				//$tstockprice=$inrrs['stockprice'];
				//$tstockbalance=$inrrs['stockbalance'];
				//$tstockprice=$tstockbalance/$tstocknumber;
				$t -> set_var('warehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse where warehouse_id ='.$inrrs[$k]["warehouse_id"]));
				$produce=$this -> dbObj -> Getrow('select * from '.WEB_ADMIN_TABPOX.'produce where produce_id ='.$inrrs[$k]["produce_id"]);
				$t -> set_var($produce);
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs[$k]['prodaybooks_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs[$k]['prodaybooks_id']));				
				$t -> set_var($inrrs[$k]);
				$t -> set_var('memo',$inrrs[$k]['memo1']);

				if($inrrs[$k]['addnumber']>0&&$inrrs[$k]['addacount']>=0){//进货
				 $t -> set_var('srnumber',$inrrs[$k]['addnumber']);
				 $t -> set_var('sracount',sprintf ("%01.2f",$inrrs[$k]['addacount']));
				 $t -> set_var('srprice',sprintf ("%01.2f",$inrrs[$k]['addacount']/$inrrs[$k]['addnumber']));
				 $t -> set_var('fcnumber','');
				 $t -> set_var('fcacount','');
				 $t -> set_var('fcprice','');
				$tsracount=$tsracount+$inrrs[$k]['addacount'];
				$tsrnumber=$tsrnumber+$inrrs[$k]['addnumber'];
				}else{
				 $t -> set_var('fcnumber',-$inrrs[$k]['addnumber']);
				 $t -> set_var('fcacount',sprintf ("%01.2f",-$inrrs[$k]['addacount']));
				  $t -> set_var('fcprice',sprintf ("%01.2f",$inrrs[$k]['addacount']/$inrrs[$k]['addnumber']));
				  $t -> set_var('srnumber','');
				  $t -> set_var('srprice','');
				  $t -> set_var('sracount','');
				$tfcacount=$tfcacount+$inrrs[$k]['addacount'];
				$tfcnumber=$tfcnumber+$inrrs[$k]['addnumber'];
				}

				 
				
				 //$inrrs[$k]['stocknumber']=$inrrs[$k]['stocknumber']==''?0:$inrrs[$k]['stocknumber'];
				 //$inrrs[$k]['stockbalance']=$inrrs[$k]['stockbalance']==''?0:$inrrs[$k]['stockbalance'];
				 $t -> set_var('stockprice',sprintf ("%01.2f",$inrrs[$k]['stockbalance']/$inrrs[$k]['stocknumber']));
				$fromtype=array("sell","sell","sell","sell","purchase","purchreturn","takestock","lossregister","adjustment","moveproduce");
				$fromtype=array("sell","sell","sell","sell","purchase","purchreturn","takestock","lossregister","adjustment","moveproduce");
				$viewbillfunction=array("viewbill","viewbill","viewbill","viewbill","viewpurchbill","viewpurchreturnbill","viewcheckstockbill","viewlossregisterbill","viewadjustmentbill","viewmoveproducebill");
				 $billname=array("销货单","销货单","销货单","销货单","进货单","采购退货单","盘点单","报损单","调整单","调拨单");
				$billno=$this->dbObj -> GetOne("SELECT ".$fromtype[$inrrs[$k]['billtype']]."_no FROM ".WEB_ADMIN_TABPOX.$fromtype[$inrrs[$k]['billtype']]." WHERE ".$fromtype[$inrrs[$k]['billtype']]."_id= ".$inrrs[$k]['billid']);
 
				$t -> set_var('viewbill','<a href=#  onclick="'.$viewbillfunction[$inrrs[$k]["billtype"]].'('.$inrrs[$k]["billid"].')">'.$billno.'</a>');
				$t -> set_var('code',$inrrs3['code']);
				$t -> set_var('produce_name',$inrrs3['produce_name']);
				$t -> parse('ml','mainlist',true);
			}
			
			$inrs -> Close();	
				$t -> set_var('tsrnumber',$tsrnumber);
				 $t -> set_var('tsracount',sprintf("%01.2f",$tsracount));
				 $t -> set_var('tsrprice',sprintf("%01.2f",$tsracount/$tsrnumber));
				 
				  $t -> set_var('tfcnumber',-$tfcnumber);
				 $t -> set_var('tfcacount',sprintf("%01.2f",-$tfcacount));
				 $t -> set_var('tfcprice',sprintf("%01.2f",$tfcacount/$tfcnumber));	
				 
				 
				 $t -> set_var('bgstocknumber',$bgstocknumber);
				 $t -> set_var('bgstockprice',sprintf("%01.2f",$bgstockprice));
				 $t -> set_var('bgstockbalance',sprintf("%01.2f",$bgstockbalance));
				 
				 $t -> set_var('tstocknumber',$tstocknumber);
				 $t -> set_var('tstockprice',sprintf("%01.2f",$tstockprice));
				 $t -> set_var('tstockbalance',sprintf("%01.2f",$tstockbalance));
				 
				  
				if($inrrscount>0&&$tstocknumber>0){ //有发生,有库存
				
				//如果库存为零。如果显示
				$t -> parse('m','main',true);
				}else if($_POST['visiblenonestockvalue']=='1'){
				$t -> parse('m','main',true);
				}else if($tstocknumber>0){//有库存
				$t -> parse('m','main',true);
				}else if($inrrscount>0){//有发生
				$t -> parse('m','main',true);
				}
			}
		$t -> set_var('pagelist',$this -> page('?bgno='.$bgno.'&endno='.$endno.'&bgdate='.$bgdate.'&enddate='.$enddate.'&warehouse_id='.$warehouse_id.'&visiblenonestock='.$visiblenonestock,$count,$psize,$pageid));	
			$t -> set_var('recordcount',$count);
		$t -> set_var('warehouselist',$this ->selectlist('warehouse','warehouse_id','warehouse_name',$_GET['warehouse_id']));	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');
				$t -> set_var('category',$category);
		$t -> set_var('keywords',$keywords);
		$t -> set_var('ftable',$ftable);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispAppend(){

		$t = new Template('../template/staff');
		$t -> set_file('f','staff_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	


		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');
		$Prefix='';
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'prodaybooks';
		$column='prodaybooks_no';
		$number=2;
		$id='prodaybooks_id';				
		$t -> set_var('prodaybooks_no',"");	
		$t -> set_var('prodaybooks_name',"");	
		$t -> set_var('idnumber',"");	
		$t -> set_var('email',"");	
		$t -> set_var('handphone',"");	
		$t -> set_var('zipcode',"");	
		$t -> set_var('tel',"");	
		$t -> set_var('address',"");	
		$t -> set_var('birthday',"");	
		$t -> set_var('price',"");	
		$t -> set_var('efficacy',"");
		$t -> set_var('useway',"");
		$t -> set_var('basis',"");	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('memo',"");
		$t -> set_var('attendancecode',"");
		
		$t -> set_var('prodaybooks_no',$this->makeno($Prefix,$agency_no,$table,$column,$number,$id));			
		$t -> set_var('userid',$this->getUid());	
		$t -> set_var('picurl',"暂时没有照片");	
		$t -> set_var('picpath',"");	
		$t -> set_var('birthday',date("Y-m-d"));
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'prodaybooks WHERE prodaybooks_id = '.$updid);
			$t -> set_var($data);
			if ($data['picpath']==''){
			
			$t -> set_var('picurl',"暂时没有照片");	
			}else{	
			$t -> set_var('picpath',$data['picpath']);	
			$t -> set_var('picurl',"<img src=".$data['picpath']." width=120 height=150 />");
			}	
			
			$t -> set_var('ismarrycheck1',$data['ismarry']==1?'checked':'');	
			$t -> set_var('ismarrycheck2',$data['ismarry']==2?'checked':'');	
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');

		}

		//$t -> set_var('brandlist',$this ->selectlist('brand','brand_id','brand_name',$data['brandid']));
		//$t -> set_var('genderlist',"111");	
		//echo $data['genderid'];
		$t -> set_var('genderlist',$this -> gender($data['genderid']));	
		//$t -> set_var('prodaybookslevellist',$this ->selectlist('prodaybookslevel','prodaybookslevel_id','prodaybookslevel_name',$data['prodaybookslevelid']));
		//$t -> set_var('emploeelevellist',$this ->selectlist('emploeelevel','emploeelevel_id','emploeelevel_name',$data['emploeelevelid']));	
		//$t -> set_var('unitlist',$this ->selectlist('unit','unit_id','unit_name',$data['standardunit']));	
		//$t -> set_var('viceunitlist',$this ->selectlist('unit','unit_id','unit_name',$data['viceunit']));						

		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}
	
	
		function selectlist($table,$id,$name,$selectid=0){
            $inrs= &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.$table .' where agencyid='.$_SESSION["currentorgan"]);

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
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'prodaybooks WHERE prodaybooks_id in('.$delid.') and agencyid='.$_SESSION["currentorgan"]);
		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'attendance WHERE prodaybooks_id in('.$delid.') and agencyid='.$_SESSION["currentorgan"]);
		$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'salary  WHERE prodaybooks_id in('.$delid.') and agencyid='.$_SESSION["currentorgan"]);
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
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."prodaybooks` (`prodaybooks_no`, `prodaybooks_name`, `address`, `genderid`, `birthday`,  `idnumber`, `tel`, `handphone`,  `zipcode`, `email`,  `memo`,  `agencyid`, `picpath`,ismarry,province,city,prodaybookslevelid,attendancecode)VALUES ( '".$_POST["prodaybooks_no"]."','".$_POST["prodaybooks_name"]."', '".$_POST["address"]."','".$_POST["genderid"]."', '".$_POST["birthday"]."', '".$_POST["idnumber"]."','".$_POST["tel"]."', '".$_POST["handphone"]."','".$_POST["zipcode"]."', '".$_POST["email"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["picpath"]."','".$this->intnonull($_POST["ismarry"])."', '".$_POST["province"]."','".$_POST["city"]."','".$_POST["prodaybookslevelid"]."','".$_POST["attendancecode"]."')");
			 $id = $this -> dbObj -> Insert_ID();
			 $this -> dbObj -> Execute("insert into `".WEB_ADMIN_TABPOX."attendance`(`prodaybooks_id`,agencyid) values ('$id',".$_SESSION["currentorgan"].")"); 
 			 $this -> dbObj -> Execute("insert into `".WEB_ADMIN_TABPOX."salary` (`prodaybooks_id`,`agencyid` )VALUES ('$id',".$_SESSION["currentorgan"].")");
			 echo "insert into `".WEB_ADMIN_TABPOX."salary` (`prodaybooks_id`,`agencyid` )VALUES ('$id',".$_SESSION["currentorgan"].")";
			 
//echo "INSERT INTO `".WEB_ADMIN_TABPOX."prodaybooks` (`prodaybooks_no`, `prodaybooks_name`, `address`, `genderid`, `birthday`,  `idnumber`, `tel`, `handphone`,  `zipcode`, `email`,  `memo`,  `agencyid`, `picpath`,ismarry,province,city)VALUES ( '".$_POST["prodaybooks_no"]."','".$_POST["prodaybooks_name"]."', '".$_POST["address"]."','".$_POST["genderid"]."', '".$_POST["birthday"]."', '".$_POST["idnumber"]."','".$_POST["tel"]."', '".$_POST["handphone"]."','".$_POST["zipcode"]."', '".$_POST["email"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."','".$_POST["picpath"]."','".$this->intnonull($_POST["ismarry"])."', '".$_POST["province"]."','".$_POST["city"]."')";
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			$id = $_POST[MODIFY.'id'];
			$this -> dbObj -> Execute("UPDATE `".WEB_ADMIN_TABPOX."prodaybooks` SET `prodaybooks_name` = '".$_POST["prodaybooks_name"]."',`prodaybooks_no` = '".$_POST["prodaybooks_no"]."',`idnumber` = '".$_POST["idnumber"]."',`tel` = '".$_POST["tel"]."',`handphone` ='".$_POST["handphone"]."',`email` = '".$_POST["email"]."',`address` = '".$_POST["address"]."',`zipcode` = '".$_POST["zipcode"]."',`birthday` = '".$_POST["birthday"]."',`genderid` = '".$this->intnonull($_POST["genderid"])."', picpath='".$_POST["picpath"]."',ismarry=".$this->intnonull($_POST['ismarry']).", province='".$_POST["province"]."', city='".$_POST["city"]."',prodaybookslevelid='".$_POST["prodaybookslevelid"]."',attendancecode='".$_POST["attendancecode"]."' WHERE prodaybooks_id =".$id);
//echo "UPDATE `".WEB_ADMIN_TABPOX."customer` SET `customer_name` = '".$_POST["customer_name"]."',`customer_no` = '".$_POST["customer_no"]."',`idnumber` = '".$_POST["idnumber"]."',`tel` = '".$this->intnonull($_POST["tel"])."',`handphone` ='".$this->intnonull($_POST["handphone"])."',`email` = '".$_POST["email"]."',`address` = '".$_POST["address"]."',`zipcode` = '".$this->intnonull($_POST["zipcode"])."',`birthday` = '".$_POST["birthday"]."',`genderid` = '".$this->intnonull($_POST["genderid"])."', picpath='".$_POST["picpath"]."',ismarry=".$this->intnonull($_POST['ismarry']).", province='".$_POST["province"]."', city='".$_POST["city"]."' WHERE customer_id =".$id;
		}
//$this -> quit($info.'成功！');
		if(mysql_affected_rows())
		$this -> quit($info.'成功！');
	    else
		$this -> quit($info.'失败！');
	}
function makeno($Prefix,$agency_no,$table,$column,$number,$id){
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by ".$column." desc limit 1");
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
		exit("<script>alert('$info');location.href='prodaybooks.php';</script>");
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
  