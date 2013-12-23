<?
include('../config.php');
include(WEB_ADMIN_CLASS_PATH.'/adodb/adodb.inc.php');
$dispErr = 0;
set_time_limit(0);//不限制页面执行时间 
if ($_POST) {
	$dbtype = $_POST['dbtype'];
	$dbhost = $_POST['dbhost'];
	$dbname = $_POST['dbname'];
	$dbuser = $_POST['dbuser'];
	$dbpass = $_POST['dbpass'];
	$tabpos = $_POST['tabpox'];
	$db = &db::getLink($dispErr,false,$dbtype,$dbhost,$dbuser,$dbpass,$dbname);
	
	if ($db->_errorMsg) {
		if(preg_match('/'.$dbname.'/',$db->_errorMsg)){
			echo '<br>正在创建数据库...';
			flush();
			$db = &db::getLink($dispErr,false,$dbtype,$dbhost,$dbuser,$dbpass,'');
			$db -> Execute("create database ".$dbname);
			if($dbtype == 'mssql')$db -> Execute("ALTER DATABASE $dbname COLLATE Latin1_General_CI_AS");//使用UTF-8
			$db = &db::getLink($dispErr,false,$dbtype,$dbhost,$dbuser,$dbpass,$dbname);
		}else{
			exit($db->_errorMsg);
		}
	}
	echo '<br>正在导入表结构...';
	flush();
	$tables = file_get_contents('sql/'.$dbtype.'_table.sql');
	$tables = preg_replace('/\{\$prefix\}/',$tabpos,$tables);
	$tables = split('GO',$tables);
	foreach ($tables as $k){
//		break; //已经安装完成
//		sleep(3);//安装oracle机器配置太小，所以要等待，没办法
		if(trim($k)){
			$db -> Execute($k);
			echo '.';
			flush();
		}
	}
	echo '<br>正在导入数据...';
	flush();
	$datas  = file_get_contents('sql/data.sql');
	$datas  = preg_replace('/\{\$prefix\}/',$tabpos,$datas);
//	$datas = iconv('UTF-8','GB2312',$datas);
	$datas  = split("\n",$datas);
	foreach ($datas as $k){
//		echo $k;
//		sleep(1);
		if(trim($k)){
			$db -> Execute($k);
			echo '.';
			flush();
		}
	}
	$db -> close();
//	exit('<br>...................insert ok.....................');	
	echo '<br>正在配置系统...';
	flush();
	$conStr = "
define('WEB_ADMIN_DBTYPE','$dbtype');
define('WEB_ADMIN_DBHOST','$dbhost');
define('WEB_ADMIN_DBUSER','$dbuser');
define('WEB_ADMIN_DBPASS','$dbpass');
define('WEB_ADMIN_DBNAME','$dbname');
define('WEB_ADMIN_TABPOX','$tabpos');";

	$config = file_get_contents(WEB_ADMIN_PHPROOT.'/config.php');
	$config  = preg_replace('/\/\*\{\$dbinfo\}\*\//',$conStr,$config);
	if(function_exists('file_put_contents')){
		file_put_contents(WEB_ADMIN_PHPROOT.'/config.php',$config);
	}else{
		$f = fopen(WEB_ADMIN_PHPROOT.'/config.php','w');
		fwrite($f,$config,strlen($config));
		fclose($f);
	}
	
	echo '<br>安装完成！初始用户：admin，密码：123456<br>点<a href=delfile.php>这里</a>删除安装文件';
	echo '<br><br>点<a href=../>这里</a>进入系统';
}else{
	include('setup.htm');
}
?>