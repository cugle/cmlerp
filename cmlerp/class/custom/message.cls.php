<?
/**
 * @package System
 */
//require(WEB_ADMIN_HTTPPATH.'/admin.inc.php');
class message extends admin {
	
 function sendmsg($tel,$msg,$agencyid,$booktime){//发送短信
 	$message_config=explode(';',$this -> getValue('message_config'));
	$orgid=$message_config[0];
	$username=$message_config[1];
	$passwd=$message_config[2];
   
    $serverUrl="http://59.42.247.51/http1.php";
    
   // $msg=mb_convert_encoding($msg,'gbk','utf-8');        //这个就没有乱码
	if($booktime<=date("Y-m-d",time())){//即时短信
    $post_data="act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$msg."&destnumbers=".$tel."&connkey=";
    }else if($booktime > date("Y-m-d",time())){//定时短信
	$post_data="act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$msg."&destnumbers=".$tel."&connkey=&booktime=".$booktime;
	}   
	
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);                //返回内容中不包含 HTTP 头

    curl_setopt($ch, CURLOPT_URL,$serverUrl);
    curl_setopt($ch, CURLOPT_NOBODY, 0);                 //读取页面内容
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);            //不自动显示内容

    $response = curl_exec($ch);
    curl_close($ch);
   
     
	$result=$response;
	$a=explode('&',$result);
    $b= explode('=',$a[0]);
	$state=$b[1];
	return $state;

}
function sendmessage($tel,$msg,$agencyid,$booktime){
 	$message_config=explode(';',$this -> getValue('message_config'));
	$orgid=$message_config[0];
	$username=$message_config[1];
	$passwd=$message_config[2];
	
	//$msg=mb_convert_encoding($msg,'gbk','utf-8');        //这个就没有乱码
	$msg=urlencode($msg);
			//$sFile = file_get_contents("http://59.42.247.51/http1.php", false, $cxContext);   
	if($booktime<=date("Y-m-d",time())){//即时短信
	//$post_data="act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$msg."&destnumbers=".$tel."&connkey=";
	
	$sFile = file_get_contents("http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$msg."&destnumbers=".$tel."&connkey=");
	//echo "http://59.42.247.51/http.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$msg."&destnumbers=".$tel."&connkey=";
    }else if($booktime > date("Y-m-d",time())){//定时短信
	//$post_data="act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$msg."&destnumbers=".$tel."&connkey=&booktime=".$booktime;
	$sFile = file_get_contents("http://59.42.247.51/http1.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$msg."&destnumbers=".$tel."&connkey=&booktime=".$booktime);
	//echo "http://59.42.247.51/http.php?act=send&orgid=".$orgid."&username=".$username."&passwd=".$passwd."&msg=".$msg."&destnumbers=".$tel."&connkey=&booktime=".$booktime;
	}	
	
	$result=$sFile;
	$a=explode('&',$result);
    $b= explode('=',$a[0]);
	$state=$b[1];
	return $state;
	
}

}

?>
  