<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/sell.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/custom/card.cls.php');
class Pageservices extends admin {
    function Main()
    {   
	
        if(isset($_GET['purchid']) ){
		
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> viewpurchbill($_GET['purchid']);			
		}else{
            parent::Main();
        }
    }	
function  viewpurchbill($purchid){

		$t = new Template('../template/procurement');
		$t -> set_file('f','purchasehistory_detail_view.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_block('f','mainlist','ml');	
        //$t -> set_block('f','consumables','cs');	
 

			$updid = $purchid + 0 ;	
			
			$data=$this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'purchase WHERE purchase_id = '.$updid);
			
					$t -> set_var('warehouse_name',$this -> dbObj -> getone('select warehouse_name from '.WEB_ADMIN_TABPOX.'warehouse where warehouse_id = '.$data['warehouse_id']));
		 
			$t -> set_var('order_no',$this->dbObj->GetOne('SELECT order_no FROM '.WEB_ADMIN_TABPOX.'order WHERE order_id = '.$data[order_id]));

			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');			
			$t -> set_var($data);			
 
		$acount = &$this -> dbObj -> GetOne('select count(*) as acount from '.WEB_ADMIN_TABPOX.'purchasedetail  where purchase_id  ='.$updid);
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
			$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'purchasedetail  where purchase_id  ='.$updid);
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				$t -> set_var('numbers',$inrrs['number']);
				$t -> set_var('cmemo',$inrrs['memo']);
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

		 
		//$t -> set_var('categorynamelist',$this ->selectlist('servicecategory','category_id','category_name',$data['categoryid']));		
		
		$t -> set_var('memo',$data['memo']);//因为与明细的memo冲突，故放到这里。		
		//$t -> set_var('ml',"");	
		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
}

}
$main = new Pageservices();
$main -> Main();
?>
  