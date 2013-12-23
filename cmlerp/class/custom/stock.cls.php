<?
/**
 * @package System
 */
//require(WEB_ADMIN_HTTPPATH.'/admin.inc.php');
//require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class stock extends admin {
	function main($produce_id,$addnumber=0,$addacount=0,$warehouse_id=2,$agencyid){
	 
		$produce_id=explode(";",$produce_id);
		$addnumber=explode(";",$addnumber);	
		$addacount=explode(";",$addacount);	
		for ($i=0;$i<count($produce_id);$i++){	
			if($this->isexist($produce_id[$i],$warehouse_id,$agencyid)){
				 
				$rs=$this->addnumber($produce_id[$i],$addnumber[$i],$addacount[$i],$warehouse_id,$agencyid);//存在则直接更新
			}else{
				 
				$this->addproduce($produce_id[$i],$warehouse_id,$agencyid);//不存在侧先插入新记录
				
				$rs=$this->addnumber($produce_id[$i],$addnumber[$i],$addacount[$i],$warehouse_id,$agencyid);//然后更新
			}
		}
		 
		return $rs;
		
		
		
	}
	
	//判断是否存在库存表中
	function isexist($produce_id,$warehouse_id=1,$agencyid){
		 //echo 'select count(*) from '.WEB_ADMIN_TABPOX.'stock   where produce_id  ='.$produce_id.' and warehouse_id='.$warehouse_id.' and agencyid='.$agencyid;
		$count=$this -> dbObj -> GetOne('select count(*) from '.WEB_ADMIN_TABPOX.'stock   where produce_id  ='.$produce_id.' and warehouse_id='.$warehouse_id.' and agencyid='.$agencyid);
		
		if($count>0){
			
				$result=true;
		}else{
			
				$result=false;
		}
		return $result;	
	}
	//修改库存数量
	function addnumber($produce_id,$addnumber,$addacount,$warehouse_id,$agencyid){
		
	$agencyid=$agencyid?$agencyid:$_SESSION["currentorgan"];
		$number = &$this -> dbObj -> GetOne('select number from '.WEB_ADMIN_TABPOX.'stock   where produce_id  ='.$produce_id.' and warehouse_id='.$warehouse_id.' and agencyid='.$_SESSION["currentorgan"]);
		$acount = &$this -> dbObj -> GetOne('select acount from '.WEB_ADMIN_TABPOX.'stock   where produce_id  ='.$produce_id.' and warehouse_id='.$warehouse_id.' and agencyid='.$agencyid);
		//echo  'select acount from '.WEB_ADMIN_TABPOX.'stock   where produce_id  ='.$produce_id.' and warehouse_id='.$warehouse_id.' and agencyid='.$agencyid;
		//echo 'select number from '.WEB_ADMIN_TABPOX.'stock   where produce_id  ='.$produce_id.' and warehouse_id='.$warehouse_id.' and agencyid='.$_SESSION["currentorgan"];
		if(($acount+$addacount)>0&&($number+$addnumber)>0){$stockunitprice=($acount+$addacount)/($number+$addnumber);
		$this->dbObj->Execute("UPDATE `".WEB_ADMIN_TABPOX."stock` SET `number` =".($number+$addnumber).',acount='.($acount+$addacount).',stockunitprice='.$stockunitprice.' where produce_id  ='.$produce_id.' and warehouse_id='.$warehouse_id.' and agencyid='.$agencyid);
		// echo "UPDATE `".WEB_ADMIN_TABPOX."stock` SET `number` =".($number+$addnumber).',acount='.($acount+$addacount).',stockunitprice='.$stockunitprice.' where produce_id  ='.$produce_id.' and warehouse_id='.$warehouse_id.' and agencyid='.$agencyid;
		}else{
		$this->dbObj->Execute("UPDATE `".WEB_ADMIN_TABPOX."stock` SET `number` =".($number+$addnumber).',acount='.($acount+$addacount).' where produce_id  ='.$produce_id.' and warehouse_id='.$warehouse_id.' and agencyid='.$agencyid);
		 //echo "UPDATE `".WEB_ADMIN_TABPOX."stock` SET `number` =".($number+$addnumber).',acount='.($acount+$addacount).' where produce_id  ='.$produce_id.' and warehouse_id='.$warehouse_id.' and agencyid='.$agencyid;
		}
		if(mysql_affected_rows()){
		return 1;
		}else{
		return 0;
		
		}
    }
//进货改变库存函数	
	function purchtostock($purchase_id,$warehouse_id,$agencyid){
		$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'purchasedetail   where purchase_id  ='.$purchase_id.' and agencyid='.$agencyid);
		
		//echo 'select * from '.WEB_ADMIN_TABPOX.'purchasedetail   where purchase_id  ='.$purchase_id.' and agencyid='.$agencyid;
		//echo 'select * from '.WEB_ADMIN_TABPOX.'purchasedetail   where purchase_id  ='.$purchase_id.' and agencyid='.$agencyid;
		$produce_id='';
		$addnumber='';
		$addacount='';
		
		while ($inrrs = &$inrs -> FetchRow()) {
			echo $inrrs['produce_id'].";".$inrrs['number'].";".$inrrs['totalacount'];
			if($produce_id==''){
				$produce_id=$inrrs['produce_id'];
				$addnumber=$inrrs['number'];
				$addacount=$inrrs['totalacount'];
				
			}else{
				$produce_id=$produce_id.";".$inrrs['produce_id'];	
				$addnumber=$addnumber.";".$inrrs['number'];	
				$addacount=$addacount.";".$inrrs['totalacount']	;
			}
			

		}
		
		$this->main($produce_id,$addnumber,$addacount,$warehouse_id,$agencyid);
    }
	function purchrecoiltostock($purchase_id,$warehouse_id,$agencyid){
		$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'purchasedetail   where purchase_id  ='.$purchase_id.' and agencyid='.$agencyid);
		
		//echo 'select * from '.WEB_ADMIN_TABPOX.'purchasedetail   where purchase_id  ='.$purchase_id.' and agencyid='.$agencyid;
		//echo 'select * from '.WEB_ADMIN_TABPOX.'purchasedetail   where purchase_id  ='.$purchase_id.' and agencyid='.$agencyid;
		$produce_id='';
		$addnumber='';
		$addacount='';
		
		while ($inrrs = &$inrs -> FetchRow()) {
			echo $inrrs['produce_id'].";".$inrrs['number'].";".$inrrs['totalacount'];
			if($produce_id==''){
				$produce_id=$inrrs['produce_id'];
				$addnumber=-$inrrs['number'];
				$addacount=-$inrrs['totalacount'];
				
			}else{
				$produce_id=$produce_id.";".$inrrs['produce_id'];	
				$addnumber=$addnumber.";".(-$inrrs['number']);	
				$addacount=$addacount.";".(-$inrrs['totalacount']);
			}
			

		}
		
		$this->main($produce_id,$addnumber,$addacount,$warehouse_id,$agencyid);
    }	
//退货改变库存函数	
	function purchrerurntostock($purchreturn_id,$warehouse_id,$agencyid){
		$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'purchreturndetail    where purchreturn_id  ='.$purchreturn_id.' and agencyid='.$agencyid);
		//echo 'select * from '.WEB_ADMIN_TABPOX.'purchreturndetail    where purchreturn_id  ='.$purchreturn_id.' and agencyid='.$agencyid;
		
		$produce_id='';
		$addnumber='';
		$addacount='';
		while ($inrrs = &$inrs -> FetchRow()) {
			if($produce_id==''){
				$produce_id=$inrrs['produce_id'];
				$addnumber=$inrrs['number'];
				$addacount=$inrrs['totalacount'];
			}else{
				$produce_id=$produce_id.";".$inrrs['produce_id'];	
				$addnumber=$addnumber.";".$inrrs['number'];	
				$addacount=$addnumber.";".$inrrs['totalacount'];
			}
		}
		
		
		$this->main($produce_id,-$addnumber,-$addacount,$warehouse_id,$agencyid);
    }
	
//报损减少库存函数	
	function lossregistertostock($lossregister_id,$warehouse_id,$agencyid){
		$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'lossregisterdetail   where lossregister_id  ='.$lossregister_id.' and agencyid='.$agencyid);
		
		$produce_id='';
		$addnumber='';
		$addacount='';
		while ($inrrs = &$inrs -> FetchRow()) {
			if($produce_id==''){
				$produce_id=$inrrs['produce_id'];
				$addnumber=$inrrs['number'];
				$addacount=$inrrs['totalacount'];
				//echo 'addacount:'.$inrrs['totalacount'];
			}else{
				$produce_id=$produce_id.";".$inrrs['produce_id'];	
				$addnumber=$addnumber.";".$inrrs['number'];	
				$addacount=$addacount.";".$inrrs['totalacount']	;
			}
		}
		
		$rs=$this->main($produce_id,-$addnumber,-$addacount,$warehouse_id,$agencyid);
		return $rs;
    }	
//领用减少库存函数	
	function lingyongtostock($lingyong_id,$warehouse_id,$agencyid){
		$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'lingyongdetail   where lingyong_id  ='.$lingyong_id.' and agencyid='.$agencyid);
		
		$produce_id='';
		$addnumber='';
		$addacount='';
		while ($inrrs = &$inrs -> FetchRow()) {
			if($produce_id==''){
				$produce_id=$inrrs['produce_id'];
				$addnumber=$inrrs['number'];
				$addacount=$inrrs['totalacount'];
				//echo 'addacount:'.$inrrs['totalacount'];
			}else{
				$produce_id=$produce_id.";".$inrrs['produce_id'];	
				$addnumber=$addnumber.";".$inrrs['number'];	
				$addacount=$addacount.";".$inrrs['totalacount']	;
			}
		}
		
		$rs=$this->main($produce_id,-$addnumber,-$addacount,$warehouse_id,$agencyid);
		return $rs;
    }			
	//添加库存表记录
	function addproduce($produce_id,$warehouse_id,$agencyid){
		 //echo 'INSERT INTO '.WEB_ADMIN_TABPOX.'stock (produce_id ,warehouse_id,agencyid) value ("'.$produce_id.'","'.$warehouse_id.'","'.$agencyid.'")';
	 $this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX.'stock (produce_id ,warehouse_id,agencyid) value ("'.$produce_id.'","'.$warehouse_id.'","'.$agencyid.'")');
	  //echo 'INSERT INTO '.WEB_ADMIN_TABPOX.'stock (produce_id ,warehouse_id,agencyid) value ("'.$produce_id.'","'.$warehouse_id.'","'.$agencyid.'")';
    }	
}
//$main = new stock();
//$main -> Main();
?>
  