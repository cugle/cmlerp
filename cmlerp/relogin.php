<?
/**
 * @desc 自动重新登录
 * @author  Vanni
 * @version 1.0
 */

require('config.php');

require(WEB_ADMIN_CLASS_PATH.'/adodb/adodb.inc.php');

require(WEB_ADMIN_CLASS_PATH.'/power/Login.cls.php');

require(WEB_ADMIN_CLASS_PATH.'/power/LoginCheck.cls.php');

$db = db::getLink();

$lo = new Login(&$db);

if(! $lo -> getUid() ) header('Location:'.'/');

if(isset($_POST['subname']) && $_POST['subname'] ) {

	$subusername = $db -> escape($_POST['subname'] , get_magic_quotes_gpc());
	
	$uid = $db -> GetOne('select userid from '.WEB_ADMIN_TABPOX."user where username = '".$subusername."'");

	if($uid != $lo -> _superid){

		$info = $lo -> registerGuser($subusername , $lo -> _superid , $_POST['msg']);//注册组内用户

		if($info !== true){

			$info = "alert('$info');";

			exit("<script>$info location.href='".WEB_ADMIN_HTTPPATH."/';</script>");

		}

	}else{
		$supid = $lo -> _superid;

		$lo -> _superid = null;

		$lo -> registerUser($supid); //重新登录
		
	}

}else 

$lo -> registerUser($lo -> getUid()); //重新登录

new LoginCheck(&$lo);
	
header('Location:'.WEB_ADMIN_HTTPPATH.'/');

?>