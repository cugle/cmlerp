<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/power/Menu.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/power/DispAttachRule.cls.php');

class PageUserrule extends admin {
   function Main()
    {   
        if(isset($_POST['action']) && $_POST['action']=='printbill')
        { 
            $this -> checkUser();//验证身份，这一步很重要。
            $this -> printbill();
        }else{
            parent::Main();
        }
    }
	function printbill(){
	//定义模板
		$t = new Template('../template/tool');
		$t -> set_file('f','show.htm');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]
		$input1=$_POST['input1'];
		$input2=$_POST['input2'];
		$input3=$_POST['input3'];
		$input4=$_POST['input4'];
		$input5=$_POST['input5'];
		$input6=$_POST['input6'];
		$input7=$_POST['input7'];
		$input8=$_POST['input8'];
		$input9=$_POST['input9'];
		$input10=$_POST['input10'];
		$input11=$_POST['input11'];
		$input12=$_POST['input12'];
		$input13=$_POST['input13'];
		$input14=$_POST['input14'];
		 
 		if(stripos($input14,'.') === false){
		$input14=$input14.'.00';
		} 
		$input14=str_pad($input14,12,'0',STR_PAD_LEFT);
		//$11=mb_substr($input14,0,1,'utf-8');
		$a1=mb_substr($input14,11,1,'utf-8');
		$b1=mb_substr($input14,10,1,'utf-8');
		$c1=mb_substr($input14,8,1,'utf-8');
		$d1=mb_substr($input14,7,1,'utf-8');
		$e1=mb_substr($input14,6,1,'utf-8');
		$f1=mb_substr($input14,5,1,'utf-8');
		$g1=mb_substr($input14,4,1,'utf-8');
		$h1=mb_substr($input14,3,1,'utf-8');
		$i1=mb_substr($input14,2,1,'utf-8');
		$j1=mb_substr($input14,1,1,'utf-8');
		$k1=mb_substr($input14,0,1,'utf-8');
		//$t -> set_var('11',$11); 
		$t -> set_var('a1',$a1); 
		$t -> set_var('b1',$b1); 
		$t -> set_var('c1',$c1); 
		$t -> set_var('d1',$d1); 
		$t -> set_var('e1',$e1); 
		$t -> set_var('f1',$f1); 
		$t -> set_var('g1',$g1); 
		$t -> set_var('h1',$h1); 
		$t -> set_var('i1',$i1); 
		$t -> set_var('j1',$j1); 
		$t -> set_var('k1',$k1); 
		 
		$input15=$_POST['input15'];
		$input16=$_POST['input16'];
		$Y=$_POST['Y']; 
		$m=$_POST['m']; 
		$d=$_POST['d']; 
		$t -> set_var('Y',$Y); 
		$t -> set_var('m',$m); 
		$t -> set_var('d',$d); 
		$t -> set_var('input1',$input1); 
		$t -> set_var('input2',$input2);
		$t -> set_var('input3',$input3);
		$t -> set_var('input4',$input4);
		$t -> set_var('input5',$input5);
		$t -> set_var('input6',$input6);
		$t -> set_var('input7',$input7);
		$t -> set_var('input8',$input8);
		$t -> set_var('input9',$input9);
		$t -> set_var('input10',$input10);
		$t -> set_var('input11',$input11);
		$t -> set_var('input12',$input12);
		$t -> set_var('input13',$input13);
		$t -> set_var('input14',$input14);
		$t -> set_var('input15',$input15);
		$t -> set_var('input16',$input16);
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function disp(){
	//定义模板
		$t = new Template('../template/tool');
		$t -> set_file('f','showinput.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]	
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	} 
}
$main = new PageUserrule();
$main -> Main();
?>