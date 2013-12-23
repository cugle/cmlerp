<?
/**
 * @package System
 */
 		date_default_timezone_set('PRC');
		
		$orgid="11";
		$username="424";
		$passwd="5445";	
		$booktime=date('Y-m-d H:i:s',time());
		$sendtime=date('Y-m-d H:i:s',time());
		 
			$data = array("act" => 'send',"orgid" => $orgid,"username" => $username,"passwd" => $passwd,"msg" => '哈哈test2222',"destnumbers" => '13570296475',"sendTime" => $sendtime,"bookTime" => $booktime);  
			 
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
			//$sFile = file_get_contents("http://localhost/chumei/customer/test1.php", false, $cxContext);   
			 $sFile = file_get_contents("http://www.qq.com");   
			$result= $sFile; 
			print_r($result);


?>
  