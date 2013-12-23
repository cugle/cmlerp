<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {

    function Main()
    {   
        if(isset($_GET['action']) && $_GET['action']=='viewhistorty')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> viewhistorty();
        } else if(isset($_GET['action']) && $_GET['action']=='savetohistory')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> savetohistory();
        } else if(isset($_GET['action']) && $_GET['action']=='audit')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> audit();
        }else if(isset($_GET['action']) && $_GET['action']=='exportexcel')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> exportexcel();
        }else if(isset($_GET['action']) && $_GET['action']=='printview')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> printview();
        }
        else
        {
            parent::Main();
        }
    }
    function viewhistorty(){
		//定义模板

		$t = new Template('../template/staff');
		$t -> set_file('f','salary.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');	
		$sql='select * from '.WEB_ADMIN_TABPOX.'salaryhistory where  agencyid ='.$_SESSION["currentorgan"]." and month=".$_GET['batch'];
			$t -> set_var('ml');
			$inrs = &$this -> dbObj -> Execute($sql);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				
				
				$data = $this -> dbObj ->GetRow("select * from ".WEB_ADMIN_TABPOX."employee where employee_id=".$inrrs['employee_id']." and  agencyid =".$_SESSION["currentorgan"]);
				$t -> set_var($data);
				$data1= $this -> dbObj ->GetRow("select * from ".WEB_ADMIN_TABPOX."employeelevel where employeelevel_id=".$data['employeelevelid']);
				$t -> set_var($data1);
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['salary_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['salary_id']));						   
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	
		$t->set_var('batch',$_GET['batch']);
		$t->set_var('historylist',$this->historylist('salaryhistory','month','month',$_GET['batch']));
		$t ->set_var('tips','');
		$t ->set_var('action','salaryhistory');
		$t ->set_var('tabletype','salaryhistory');
		$t ->set_var('gobackthismondisplay','');
		$t->set_var('agencyid',$_SESSION["currentorgan"]);
		$t->set_var('pre',WEB_ADMIN_TABPOX);
		$t -> set_var('add',$this -> getAddStr('img'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');

    }
	function savetohistory(){
			$sql='select * from '.WEB_ADMIN_TABPOX.'salary where  agencyid ='.$_SESSION["currentorgan"];
			
			$inrs = &$this -> dbObj -> Execute($sql);
			
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				
				echo "INSERT INTO  ".WEB_ADMIN_TABPOX."salaryhistory  (employee_id,`basic` ,`sel_royalties` ,`ser_royalties` ,`bonus` ,`fine`  ,`wagespayable`  ,`otpay` ,`postwage` ,`fullattendance` ,`livingallowance`,`agencyid` ,`month` ) value(".$inrrs['employee_id'].",".$inrrs["basic"].",'".$inrrs['sel_royalties']."', '".$inrrs['ser_royalties']."','".$inrrs['bonus']."','".$inrrs['fine']."','".$inrrs['wagespayable']."','".$inrrs['otpay']."','".$inrrs['postwage']."','".$inrrs['fullattendance']."','".$inrrs['livingallowance']."','".$inrrs['agencyid']."','".$_GET['batch']."')";
				$data = $this -> dbObj ->GetRow("INSERT INTO  ".WEB_ADMIN_TABPOX."salaryhistory  (employee_id,`basic` ,`sel_royalties` ,`ser_royalties` ,`bonus` ,`fine`  ,`wagespayable`  ,`otpay` ,`postwage` ,`fullattendance` ,`livingallowance`,`agencyid` ,`month`,leavepay ) value(".$inrrs['employee_id'].",".$inrrs["basic"].",'".$inrrs['sel_royalties']."', '".$inrrs['ser_royalties']."','".$inrrs['bonus']."','".$inrrs['fine']."','".$inrrs['wagespayable']."','".$inrrs['otpay']."','".$inrrs['postwage']."','".$inrrs['fullattendance']."','".$inrrs['livingallowance']."','".$inrrs['agencyid']."','".$_GET['batch']."','".$inrrs['leavepay']."')");

			 $this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."salary SET `sel_royalties` = 0,`ser_royalties` = 0,`bonus` = 0,`fine` =0,`wagespayable` =0,`otpay` = 0,`leavepay` =0,`livingallowance` = 0 WHERE salary_id =".$inrrs[salary_id]);

			}
			$inrs -> Close();	
			$this -> quit($info.'成功！');
	}
	function audit(){
	 $this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."salary SET `auditstatus` = 1 WHERE agencyid =".$_SESSION["currentorgan"]);
	 $this -> quit('提交成功！');
	}
	function exportexcel(){
	$filename="salary.xls";//先定义一个excel文件
header("Content-Type: application/vnd.ms-execl"); 
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 

echo "编号"."\t";
echo "姓名"."\t";
echo "职级"."\t";
echo "基本工资"."\t";
echo "加班费"."\t";
echo "应扣休假"."\t";
echo "手工提成"."\t";
echo "销售提成"."\t";
echo "岗位工资"."\t";
echo "全勤奖"."\t";
echo "生活补贴"."\t";
echo "宝贝"."\t";
echo "奖金"."\t";
echo "应发工资"."\t";
echo "月份"."\n";
$sql='select * from '.WEB_ADMIN_TABPOX.'salary where  agencyid ='.$_SESSION["currentorgan"]." ORDER BY  salary_id DESC ";

$result= &$this -> dbObj -> Execute($sql);
while($inrrs =mysql_fetch_array($result)){
echo $inrrs['employee_id']."\t";
echo $inrrs["basic"]."\t";
echo $inrrs["basic"]."\t";
echo $inrrs["basic"]."\t";
echo $inrrs['otpay']."\t";
echo $inrrs['leavepay']."\t";
echo $inrrs['ser_royalties']."\t";
echo $inrrs['sel_royalties']."\t";
echo $inrrs['postwage']."\t";
echo $inrrs['fullattendance']."\t";
echo $inrrs['livingallowance']."\t";
echo $inrrs['fine']."\t";
echo $inrrs['bonus']."\t";
echo $inrrs['wagespayable']."\t";
echo $inrrs['batch']."\n";
}
}
function printview(){
		//定义模板
		$t = new Template('../template/staff');
		$t -> set_file('f','salaryprint.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		

  		 
		$tabletype=$_GET['type']?$_GET['type']:'salary';
		$batch=$_GET['batch']?$_GET['batch']:'';
		
        $category=$_GET["category"];
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
			
		
			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'salary where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'salary a INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on a.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  a.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			if($tabletype=='salary'){
			$sql='select * from '.WEB_ADMIN_TABPOX.$tabletype.' where  agencyid ='.$_SESSION["currentorgan"];
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.$tabletype.' where month='.$batch.' and  agencyid ='.$_SESSION["currentorgan"];
			}
			 
			}

			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  salary_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);	
			$count=$result->RecordCount();
			
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
			$t -> set_var('recordcount',$count);
			//设置分类
			$totalbasic=0;
			$totalotpay=0;
			$totalleavepay=0;
			$totalser_royalties=0;
			$totalsel_royalties=0;
			$totalpostwage=0;
			$totalfullattendance=0;
			$totallivingallowance=0;
			$totalfine=0;
			$totalbonus=0;
			$t -> set_var('ml');
			
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'salary where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				
				
				$data = $this -> dbObj ->GetRow("select * from ".WEB_ADMIN_TABPOX."employee where employee_id=".$inrrs['employee_id']." and  agencyid =".$_SESSION["currentorgan"]);
				$t -> set_var($data);
				$data1= $this -> dbObj ->GetRow("select * from ".WEB_ADMIN_TABPOX."employeelevel where employeelevel_id=".$data['employeelevelid']);
				$t -> set_var($data1);

			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['salary_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['salary_id']));						   
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();
			
			if($tabletype=='salary'){
				$data2 = $this -> dbObj ->GetRow("select sum(basic) as totalbasic,sum(otpay) as totalotpay,sum(leavepay) as totalleavepay,sum(ser_royalties) as totalser_royalties,sum(sel_royalties) as totalsel_royalties,sum(postwage) as totalpostwage,sum(fullattendance) as totalfullattendance,sum(livingallowance) as totallivingallowance,sum(fine) as totalfine,sum(bonus) as totalbonus ,sum(wagespayable) as totalwagespayable from ".WEB_ADMIN_TABPOX."salary where  agencyid =".$_SESSION["currentorgan"]);
				}else{
				$data2 = $this -> dbObj ->GetRow("select sum(basic) as totalbasic,sum(otpay) as totalotpay,sum(leavepay) as totalleavepay,sum(ser_royalties) as totalser_royalties,sum(sel_royalties) as totalsel_royalties,sum(postwage) as totalpostwage,sum(fullattendance) as totalfullattendance,sum(livingallowance) as totallivingallowance,sum(fine) as totalfine,sum(bonus) as totalbonus ,sum(wagespayable) as totalwagespayable from ".WEB_ADMIN_TABPOX."salaryhistory where month=".$batch." and   agencyid =".$_SESSION["currentorgan"]);

				
				}
				$t -> set_var($data2);
		$t -> set_var('currentorganname',$_SESSION["currentorganname"]); 	
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	
		$t->set_var('historylist',$this->historylist('salaryhistory','month','month',$_GET['batch']));
		$batch=date("d",time())-6>0?date("Ym",time()):date("Ym",strtotime('last month'));
		$t ->set_var('gobackthismondisplay','display:none');
		$t->set_var('batch',$batch);	
		$t->set_var('agencyid',$_SESSION["currentorgan"]);
		$t->set_var('pre',WEB_ADMIN_TABPOX);
		$t -> set_var('add',$this -> getAddStr('img'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');

}
	function disp(){
		//定义模板
		$t = new Template('../template/staff');
		$t -> set_file('f','salary.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		

   
        $category=$_GET["category"];
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
			
		
			if($condition<>''&&$ftable==''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'salary where  agencyid ='.$_SESSION["currentorgan"].' and '.$condition ;

			}else if($ftable<>''){
			$sql='select * from '.WEB_ADMIN_TABPOX.'salary a INNER JOIN '.WEB_ADMIN_TABPOX."$ftable f on a.categoryid =f.category_id  where f.category_name like '%".$keywords."%' and  a.agencyid =".$_SESSION["currentorgan"] ;
			
			}else{
			$sql='select * from '.WEB_ADMIN_TABPOX.'salary where  agencyid ='.$_SESSION["currentorgan"];
			 
			}
			
			$inrs = &$this -> dbObj -> Execute($sql." ORDER BY  salary_id DESC  LIMIT ".$offset." , ".$psize);
			$result = &$this -> dbObj -> Execute($sql);	
			$count=$result->RecordCount();
			
			$t -> set_var('pagelist',$this -> page("?category=".$category."&keywords=".urlencode($keywords)."&ftable=".$ftable,$count,$psize,$pageid));		
			
			$t -> set_var('recordcount',$count);
			//设置分类
			
			$t -> set_var('ml');
			
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'salary where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
				
				
				$data = $this -> dbObj ->GetRow("select * from ".WEB_ADMIN_TABPOX."employee where employee_id=".$inrrs['employee_id']." and  agencyid =".$_SESSION["currentorgan"]);
				$t -> set_var($data);
				$data1= $this -> dbObj ->GetRow("select * from ".WEB_ADMIN_TABPOX."employeelevel where employeelevel_id=".$data['employeelevelid']);
				$t -> set_var($data1);
			   	$t -> set_var('delete',$this -> getDelStr('',$inrrs['salary_id']));
		        $t -> set_var('edit',$this -> getupdStr('',$inrrs['salary_id']));						   
				$t -> parse('ml','mainlist',true);
			}
			$inrs -> Close();
			
		    $auditstatus=$this -> dbObj ->GetOne("select auditstatus from ".WEB_ADMIN_TABPOX."salary where  agencyid =".$_SESSION["currentorgan"]);
			if($auditstatus=='1'){
			$t ->set_var('auditdisabled','disabled="disabled"');
			$t ->set_var('tips','>>数据已提交，请耐心等待财务审核');
			$t ->set_var('savetohistorydisabled','disabled="disabled"');
			}else if($auditstatus=='2'){
			$t ->set_var('auditdisabled','disabled="disabled"');
			$t ->set_var('tips','>>数据已通过审核！');
			$t ->set_var('savetohistorydisabled','disabled="disabled"');
			}else if($auditstatus=='3'){
			$t ->set_var('auditdisabled','');
			$t ->set_var('tips','>>没有通过审核，请仔细核对修改后再次提交！');
			$t ->set_var('savetohistorydisabled','disabled="disabled"');
			}else{
			$t ->set_var('auditdisabled','');
			$t ->set_var('tips','');
			$t ->set_var('savetohistorydisabled','disabled="disabled"');
			}		
			$atdauditstatus=$this -> dbObj ->GetOne("select auditstatus from ".WEB_ADMIN_TABPOX."attendance where  agencyid =".$_SESSION["currentorgan"]);
			if($atdauditstatus==2){
			if($auditstatus=='1'){
			$t ->set_var('submitauditdisabled','disabled="disabled"');
			
			}else
			{$t ->set_var('submitauditdisabled','');
			}
			}else if ($atdauditstatus==1){
			$t ->set_var('tips','>>考勤尚未审核，请先审核！');
			$t ->set_var('submitauditdisabled','disabled="disabled"');
			}else{
			
			$t ->set_var('submitauditdisabled','disabled="disabled"');
			}
		$this->getModify()?$t -> set_var('canedit',''):$t -> set_var('canedit','none');
		$this->getDelete()?$t -> set_var('candelete',''):$t -> set_var('candelete','none');
		$this->getAppend()?$t -> set_var('canadd',''):$t -> set_var('canadd','none');	
		$this->getSupper()?$t -> set_var('canimport',''):$t -> set_var('canimport','none');	
		$this->getSupper()?$t -> set_var('canexport',''):$t -> set_var('canexport','none');	
		$t->set_var('historylist',$this->historylist('salaryhistory','month','month',$_GET['batch']));
		$batch=date("d",time())-6>0?date("Ym",time()):date("Ym",strtotime('last month'));
		$t ->set_var('gobackthismondisplay','display:none');
		$t->set_var('batch',$batch);	
		$t ->set_var('action','salary');
		$t ->set_var('tabletype','salary');
		$t->set_var('agencyid',$_SESSION["currentorgan"]);
		$t->set_var('pre',WEB_ADMIN_TABPOX);
		$t -> set_var('add',$this -> getAddStr('img'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispAppend(){

		$t = new Template('../template/staff');
		$t -> set_file('f','salary_detail.html');
		$t->unknowns = "move";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	


		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','增加');

		$t -> set_var('salary_name',"");	
		$t -> set_var('error',"");	
		$t -> set_var('showeditdiv',"");		
		$t -> set_var('memo',"");
		$t -> set_var('userid',$this->getUid());		
		}else{

			$updid = $_GET[MODIFY.'id'] + 0 ;	
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'salary WHERE salary_id = '.$updid));			
			$t -> set_var('error',"");
			$t -> set_var('showeditdiv',"");
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','修改');
			$inrs = &$this -> dbObj -> Execute('SELECT * FROM '.WEB_ADMIN_TABPOX.'salary WHERE salary_id = '.$updid);	
			while ($inrrs = &$inrs -> FetchRow()) {
				$t -> set_var($inrrs);
			}
			$inrs -> Close();
		}
					
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
		
	}
	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] ;
        $this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'salary WHERE salary_id in('.$delid.')');
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
			$customer_id=explode(";",$_POST["id"]);
			$this -> dbObj -> Execute("INSERT INTO `".WEB_ADMIN_TABPOX."salary` (`salary_name` ,`memo` ,`agencyid` )VALUES ( '".$_POST["salary_name"]."','".$_POST["memo"]."','".$_SESSION["currentorgan"]."')");

			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '修改';
			
			$id = $_POST[MODIFY.'id'];
			echo $_POST["id"];
			$id=explode(",",$_POST["id"]);
			for ($i=0;$i<count($id);$i++)	
			{$basic=$_POST['basic'.$id[$i]];
			 $sel_royalties= $_POST['sel_royalties'.$id[$i]];
			 $ser_royalties= $_POST['ser_royalties'.$id[$i]];
			 $bonus= $_POST['bonus'.$id[$i]];
			 $fine=$_POST['fine'.$id[$i]];
			 //$elseitem= $_POST['elseitem'.$id[$i]];
			 $deduct_vacation= $_POST['deduct_vacation'.$id[$i]];
			 $wagespayable= $_POST['wagespayable'.$id[$i]];
			 //$realwages=$_POST['realwages'.$id[$i]];
			 $otpay= $_POST['otpay'.$id[$i]];
			 $leavepay= $_POST['leavepay'.$id[$i]];
			 $postwage= $_POST['postwage'.$id[$i]];
			 $ser_royalties= $_POST['ser_royalties'.$id[$i]];
			 $fullattendance= $_POST['fullattendance'.$id[$i]];			 
			 $livingallowance= $_POST['livingallowance'.$id[$i]];
			 
			 //echo 'UPDATE '.WEB_ADMIN_TABPOX."salary SET plansalary='".$plansalary."', actualsalary='".$actualsalary."', othours='".$othours."',leavehours='".$leavehours."' WHERE salary_id =".$id;
			
//echo 'UPDATE '.WEB_ADMIN_TABPOX."salary SET `basic` = ".$basic.",`sel_royalties` = ".$sel_royalties.",`ser_royalties` = ".$ser_royalties.",`bonus` = ".$bonus.",`fine` = ".$fine.",`elseitem` = ".$elseitem.",`deduct_vacation` =".$deduct_vacation.",`wagespayable` = ".$wagespayable.",`realwages` = ".$realwages.",`month` = ".$month.",`otpay` = ".$otpay.",`postwage` = ".$postwage.",`fullattendance` = ".$fullattendance.",`livingallowance` = ".$livingallowance."' WHERE salary_id =".$id[$i];		
			echo 'UPDATE '.WEB_ADMIN_TABPOX."salary SET `basic` = '".$basic."',`sel_royalties` = '".$sel_royalties."',`ser_royalties` = '".$ser_royalties."',`bonus` = '".$bonus."',`fine` = '".$fine."',`wagespayable` ='".$wagespayable."',`month` = '".$month."',`otpay` = '".$otpay."',`postwage` = '".$postwage."',`fullattendance` = '".$fullattendance."',`livingallowance` = '".$livingallowance."' WHERE salary_id =".$id[$i];
			 $this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."salary SET `basic` = '".$basic."',`sel_royalties` = '".$sel_royalties."',`ser_royalties` = '".$ser_royalties."',`bonus` = '".$bonus."',`fine` = '".$fine."',`wagespayable` ='".$wagespayable."',`otpay` = '".$otpay."',`leavepay` = '".$leavepay."',`postwage` = '".$postwage."',`fullattendance` = '".$fullattendance."',`livingallowance` = '".$livingallowance."' WHERE salary_id =".$id[$i]);
			}
			//$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."salary SET salary_name='".$_POST["salary_name"]."', memo='".$_POST["memo"]."' WHERE salary_id =".$id);

		}
//$this -> quit($info.'成功！');

		$this -> quit($info.'成功！');

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
		exit("<script>alert('$info');location.href='salary.php';</script>");
	}
		function historylist($table,$id,$name,$selectid='201008'){
            $inrs= &$this -> dbObj -> Execute('select distinct '.$name.' from '.WEB_ADMIN_TABPOX.$table .' where agencyid ='.$_SESSION["currentorgan"]." ORDER BY  month  DESC");
			$str='';
	     	while ($inrrs = &$inrs -> FetchRow()) {
			
			if ($inrrs[$id]==$selectid)
			$str =$str."<option value=".$inrrs[$name]." selected>".$inrrs[$name]."</option>";	
			else
			$str =$str."<option value=".$inrrs[$name].">".$inrrs[$name]."</option>";			
			}
			$inrs-> Close();	

			return  $str;	
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
  