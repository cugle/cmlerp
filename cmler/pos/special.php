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
        if(isset($_GET['action']) && $_GET['action']=='freecost'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> freecost();			
		}else if(isset($_GET['action']) && $_GET['action']=='step3'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> step3();			
		}else if(isset($_GET['action']) && $_GET['action']=='shouquan1'){
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> shouquan1();			
		}else{
            parent::Main();
        }
    }
	
	function shouquan(){
		//定义模板
		 

		$t = new Template('../template/pos');
		$t -> set_file('f','shouquan.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
   		//echo "select * from  ".WEB_ADMIN_TABPOX."user A INNER JOIN ".WEB_ADMIN_TABPOX."employee B ON A.employee_id=B.employee_id   where B.employeelevelid in(1,2) AND B.agencyid =".$_SESSION["currentorgan"];
		$t -> set_var('password','');
		$userinrs = $this -> dbObj -> Execute( "select * from  ".WEB_ADMIN_TABPOX."user A INNER JOIN ".WEB_ADMIN_TABPOX."employee B ON A.employee_id=B.employee_id   where B.employeelevelid in(3,2) and B.status='1' AND B.agencyid =".$_SESSION["currentorgan"]);
		 
		//echo  "select * from  ".WEB_ADMIN_TABPOX."user A INNER JOIN ".WEB_ADMIN_TABPOX."employee B ON A.employee_id=B.employee_id   where B.employeelevelid in(3,2) and B.status='1' AND B.agencyid =".$_SESSION["currentorgan"];
		while ($user = $userinrs -> FetchRow()) {
		$username=$username.'<option value="'.$user["username"].'">'.$user["username"].'</option>';
		}
	 $t -> set_var('username',$username);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}	
		function shouquan1(){
		//定义模板
		//授权登录
		//登录成功
		$username=$_POST['username'];
		$psw=$_POST['password'];
		$user = $this -> dbObj -> GetRow("select * from  ".WEB_ADMIN_TABPOX."user   where   username='".$username."' and  userpass='".md5($psw)."'");
		
		if($user){
			$employeelevelid = $this -> dbObj -> GetOne("select employeelevelid from  ".WEB_ADMIN_TABPOX."employee where employee_id=".$user['employee_id']."  and employeelevelid in(3,2)");
			
		}
		if($employeelevelid){
		$checkin=1;
		}else{
		$checkin=0;
		}
		
		if($checkin==1){
		$_SESSION['shouquan']=1;
		
		$this->disp();
		}else{
		//登录失败
		$this->shouquan();
		}
	}
	function disp(){
		//定义模板
		 
		//if($_SESSION['shouquan']==0){
		//	$this->shouquan();
		//	}else{
		$t = new Template('../template/pos');
		$t -> set_file('f','special.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		
   
 
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		//	}
	}
	function freecost(){
		//定义模板
		
		$currentcustomerid=$_SESSION["currentcustomerid"];
		$membercardno=$_SESSION["membercardno"];
		$t = new Template('../template/pos');
		$t -> set_file('f','freecost.html');
		$t->unknowns = "keep";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');
		//echo 'SELECT * FROM `'.WEB_ADMIN_TABPOX.'customer`  A INNER JOIN '.WEB_ADMIN_TABPOX.'membercard B ON A.customer_id= B.customer_id where A.customer_id ='.$currentcustomerid;
		$memberdata=$this -> dbObj -> GetRow('SELECT * FROM `'.WEB_ADMIN_TABPOX.'customer`  A INNER JOIN '.WEB_ADMIN_TABPOX.'membercard B ON A.customer_id= B.customer_id where A.customer_id ='.$currentcustomerid);
 
		 
		$t -> set_var('membercard_no',$memberdata['membercard_no']);
		$t -> set_var('customer_id',$memberdata['customer_id']);
		$t -> set_var('customer_name',$memberdata['customer_name']);
		$t -> set_var('code',$memberdata['code']);
		$t -> set_var('freecost',$memberdata['freecost']);
		  
		$t -> set_var('memcardlevellist',$this ->selectlist('memcardlevel','cardlevel_id','cardlevel_name',$memberdata['cardlevel_id']));
		//echo  'select unit_name from '.WEB_ADMIN_TABPOX.'unit  where unit_id ='.$standardunit;
		$t -> set_var('zengsong','0.00');
		$t -> set_var('employee_id','');
		$t -> set_var('employee_name','');
		$t -> set_var('number',1);
		$t -> set_var('action','step3');
		 $this->PriceObj=new price();
		 $value=$this->PriceObj->produceprice($_SESSION["currentcustomerid"],$produce_id,$_SESSION["currentorgan"]);	
		 $t -> set_var('value',$value);
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
		 $discount=10;
		 $beauty_id=0;
		 $cardtable='selldetail';
		 $beauty_id=$_POST['employee_id'];
		 $id = $this->SellObj->addsellitem($sellid,$item_type=1,$item_id,$number,$value,$price,$discount=10,$beauty_id,$cardtable='selldetail',1,0);
		//最后那个1和0分别指 默认仓库，自动指定仓库
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
	
}
$main = new Pageproduce();
$main -> Main();
?>
  