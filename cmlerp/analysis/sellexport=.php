<?
/**
 * @package System
 */
$filename="sell.xls";//先定义一个excel文件
header("Content-Type: application/vnd.ms-execl"); 
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=$filename"); 
header("Pragma: no-cache"); 
header("Expires: 0");
echo mb_convert_encoding("编号","GBK","utf-8")."\t";
echo "姓名"."\t";
echo "性别"."\t";
echo "生日"."\n";
 ?>

