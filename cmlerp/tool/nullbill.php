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
        if(isset($_GET['action']) && $_GET['action']=='printbills')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> printbills();
        }else if(isset($_POST['action']) && $_POST['action']=='printsuccess')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> printsuccess();
        } else{
            parent::Main();
        }
    }	
	function printsuccess(){
		$nullbillno=$_POST['nullbillno'];
		$res=$this -> dbObj ->Execute(' INSERT INTO '.WEB_ADMIN_TABPOX.'nullbillno ( nullbillno_no,agencyid) VALUE ("'.$nullbillno.'",'.$_SESSION["currentorgan"].')');	
		
		if($res){
		$info='操作成功';
		exit("<script>alert('$info');window.close();</script>");
		}else{
		$info='发生错误，提交失败，数据已经回滚';
		exit("<script>alert('$info');window.close();</script>");
		 
		}
 
		
		
	}
	function disp(){
		//定义模板
 		 
		$t = new Template('../template/tool');
		$t -> set_file('f','selectnumber.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
   			//设置分类
	
		$Prefix='XS';
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'nullbillno';
		$column='nullbillno_no';
		$number=5;
		$id='nullbillno_id';
		$sell_no= $this->makeno($Prefix,$agency_no,$table,$column,$number,$id) ;
 		$t -> set_var('sell_no',$sell_no); 
		$t -> set_var('number','100'); 
 		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');			
	}
	function printbills($sellid){
		//定义模板
 		 
		$t = new Template('../template/tool');
		$t -> set_file('f','nullbill.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
   			//设置分类
	
		$Prefix='XS';
		$agency_no=$_SESSION["agency_no"];
		$table=WEB_ADMIN_TABPOX.'nullbillno';
		$column='nullbillno_no';
		$number=5;
		$id='nullbillno_id';
		$sell_no= $this->makeno($Prefix,$agency_no,$table,$column,$number,$id) ;
		
		$t -> set_var('ml');
		for($i=0;$i<$_POST['number'];$i++){
			if($i>0){
				$sell_no= $this->nextno($Prefix,$agency_no,$table,$column,$number,$id,$sell_no) ;
			} 
			
			$t -> set_var('sell_no',$sell_no); 
			$currentorganname= str_replace('香蔓','',$_SESSION["currentorganname"]);	
		    $t -> set_var('currentorganname',$currentorganname);
 			//$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 	
			
			if($i<$_POST['number']-1) {
			$t -> set_var('nextpage','<div class="PageNext"></div>');  
			}else{
			$t -> set_var('nextpage','');  
			}
			$t -> parse('ml','mainlist',true);
		}
 		
		$t -> set_var('nullbillno',$sell_no); 
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');	
	}
function makeno($Prefix,$agency_no,$table,$column,$number,$id){
$nostr = $this -> dbObj ->GetRow("select ".$column." from ".$table." where agencyid =".$_SESSION["currentorgan"]." order by  ".$column." desc, ".$id." desc limit 1");
 

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


function nextno($Prefix,$agency_no,$table,$column,$number,$id,$sellno){
 
$nostr=$sellno;
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
}
$main = new Pageservices();
$main -> Main();
?>
  