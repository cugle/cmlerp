<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
	function disp(){
		date_default_timezone_set('PRC');
		$message_config=explode(';',$this -> getValue('message_config'));
		$orgid=$message_config[0];
		$username=$message_config[1];
		$passwd=$message_config[2];	
		$booktime=date('Y-m-d H:i:s',time());
		$sendtime=date('Y-m-d H:i:s',time());
		 
			$data = array("act" => 'send',"orgid" => $orgid,"username" => $username,"passwd" => $passwd,"msg" => 'test2222',"destnumbers" => '13570296475',"sendTime" => $sendtime,"bookTime" => $booktime);  
			 
			$data = http_build_query($data);   
			$opts = array(   
			  'http'=>array(   
				'method'=>"POST",   
				'header'=>"Content-type: application/x-www-form-urlencoded\r\n".   
						  "Content-length:".strlen($data)."\r\n" .    
						  "Cookie: foo=bar\r\n" .    
						  "\r\n",   
				'content' => $data,   
			  )   
			);   
			$cxContext = stream_context_create($opts);   
			//$result=file_get_contents("http://localhost/chumei/customer/test1.php");
			$sFile = file_get_contents("http://localhost/chumei/customer/test1.php", false, $cxContext);   
			 
			$result= $sFile; 
			echo $result;
 
    }
	
}
$main = new Pagecustomer();
$main -> Main();
?>
  