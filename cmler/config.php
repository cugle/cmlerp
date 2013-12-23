<?
session_start();
error_reporting(E_ALL);
header("content-type:text/html; charset=utf-8");
define('WEB_ADMIN_PHPROOT',      str_replace('\\','/',dirname(__FILE__)));							//网站根目录
if(!isset($_SERVER['DOCUMENT_ROOT']) || (isset($_SERVER['PATH_TRANSLATED']) && !eregi(preg_replace('/\\\+/','/',$_SERVER['DOCUMENT_ROOT']),preg_replace('/\\\+/','/',$_SERVER['PATH_TRANSLATED'])))){
	$_SERVER['DOCUMENT_ROOT'] = substr(preg_replace('/\\\+/','/',$_SERVER['PATH_TRANSLATED']),0,-strlen(empty($_SERVER['PHP_SELF'])?$_SERVER['PATH_INFO']:$_SERVER['PHP_SELF']));
}

if($_SERVER['DOCUMENT_ROOT'][strlen($_SERVER['DOCUMENT_ROOT'])-1] == '/') 
$_SERVER['DOCUMENT_ROOT'] = substr($_SERVER['DOCUMENT_ROOT'],0,-1);

//相对的网站目录
if( $_SERVER['DOCUMENT_ROOT'] == WEB_ADMIN_PHPROOT){
	define('WEB_ADMIN_HTTPPATH', '');
}else{
	define('WEB_ADMIN_HTTPPATH',substr(WEB_ADMIN_PHPROOT,strlen($_SERVER['DOCUMENT_ROOT'])));
}
define('WEB_ADMIN_PHPCOMMON',	 WEB_ADMIN_PHPROOT.'/common');
define('WEB_ADMIN_HTTPCOMMON',	 WEB_ADMIN_HTTPPATH.'/common');
define('WEB_ADMIN_TMPPATH',		 WEB_ADMIN_PHPROOT.'/template');
define('WEB_ADMIN_CLASS_PATH',   WEB_ADMIN_PHPROOT.'/class');										//类库目录
// 数据库设置 支持的数据库mssql,mysql,oci8
// utf-8 Latin1_General_CI_AS

define('WEB_ADMIN_DBTYPE','mysql');
define('WEB_ADMIN_DBHOST','localhost');
define('WEB_ADMIN_DBUSER','root');
define('WEB_ADMIN_DBPASS','123qaz');
define('WEB_ADMIN_DBNAME','cmlerp');
define('WEB_ADMIN_TABPOX','s_');

?>