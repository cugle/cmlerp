<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
class Pagecustomer extends admin {
	function disp(){
		//定义模板
		$t = new Template('../template/basic');
		$t -> set_file('f','upmorefile.html');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]		
		$t -> set_block('f','mainlist','ml');		

			//设置分类
			$t -> set_var('ml');
			
			//$inrs = &$this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'apparatus  where agencyid ='.$_SESSION["currentorgan"]);
			 //echo 'select * from '.WEB_ADMIN_TABPOX.'roomgroup  where agencyid ='.$_SESSION["currentorgan"];
	     	//while ($inrrs = &$inrs -> FetchRow()) {
				//$t -> set_var($inrrs);
			   
				//$t -> parse('ml','mainlist',true);
			//}
			//$inrs -> Close();	
		
	//$t -> set_var('add',$this -> getAddStr('img'));
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
function goAppend(){
$uptypes=array(
    'image/jpg', 
    'image/jpeg',
    'image/png',
    'image/pjpeg',
    'image/gif',
    'image/bmp',
    'image/x-png'
);

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $files = $_FILES['upfile'];
    //检查文件大小
    if($files['size'] > 2097152){
        echo '上传文件图片不得大于2M';
        exit;
    }
    //检查上传文件类型
    $ftype = $files['type'];
    if(!in_array($ftype,$uptypes)){
        echo '上传文件不符合图片类型';
        exit;
    }
    //取得上传图片的信息
    $fname = $files['tmp_name'];
    $image_info = getimagesize($fname);
    //取得上传图片的扩展名
    $name = $files['name'];
    $str_name = pathinfo($name);
    $extname = strtolower($str_name['extension']);
    //上传路径
    $upload_dir = "../upfiles/apparatus/";
    $file_name = $_SESSION["currentorgan"]."_".date("YmdHis").rand(1000,9999).".".$extname;
    $str_file = $upload_dir.$file_name;
    
    //创建上传的目录

if(!file_exists($upload_dir)) 
    { 
        mkdir($upload_dir); 
    }
       if(!move_uploaded_file($files['tmp_name'],$str_file)){
        echo "上传文件失败";
        exit;
    }

echo "<SCRIPT language=JavaScript>"; 
echo 'window.returnValue="'.$str_file.'";'; 
//echo 'closewindow(); ';
//echo 'window.returnValue="flage" ;';
echo 'window.close();';
echo '</SCRIPT> ';   

}

}
	
}
$main = new Pagecustomer();
$main -> Main();
?>
  