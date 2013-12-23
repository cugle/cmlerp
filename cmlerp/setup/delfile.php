<?

require('../config.php');

$dir = WEB_ADMIN_PHPROOT.'/setup';
if(is_dir($dir))
delTheDir($dir);
else exit($dir.'目录不存在');

function delTheDir($dir){
	$dirHand = opendir($dir);
	while (($file = readdir($dirHand)) ==! false ) {
		if($file == '.' || $file == '..')continue;
		if(is_dir("$dir/$file")){
			delTheDir("$dir/$file");
		}else{
			echo "删除文件 $dir/$file <br>";
			flush();
			unlink("$dir/$file");
		}
	}
	closedir($dirHand);
	echo "删除目录 $dir <br>";
	flush();
	rmdir($dir);
}
echo '完成';
?>