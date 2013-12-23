<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class Pageselectcustomer extends admin {
 

	function disp(){


             $inrs = &$this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX."area");
		    	while ($inrrs = $inrs -> FetchRow())
 		 {
            //echo mb_substr($inrrs['area_name'], 0, 2, 'utf-8');
			$this -> dbObj -> Execute("UPDATE s_beautyshop  SET areaid =".$inrrs['area_id']." WHERE profile  like '%".mb_substr($inrrs['area_name'], 0, 2, 'utf-8')."%'"); 
			 echo "UPDATE s_beautyshop  SET areaid =".$inrrs['area_id']." WHERE profile  like '%".mb_substr($inrrs['area_name'], 0, 2, 'utf-8')."%'";
			}
			$inrs -> Close();

echo "ok";
}	

/*
======================================================
$str    字符
$start 字符截取位置
$len    截取字符的数
======================================================
*/
Function cSubStr($str,$start,$len) //设置3个参数 
{ 
$strlen=strlen($str); // 获取字符长度
$clen=0; 
for($i=0;$i<$strlen;$i++,$clen++) 
{ 
if ($clen>=$start+$len) //当大于截取字符数，则跳出循环
   break; 
if(ord(substr($str,$i,1))>0xa0) //ord 本函数返回字符的 ASCII (美国国家标准交换码) 序数值。本函数和chr()函数相反。
{ //0xa0 代表 十进制 160,0xa0表示汉字的开始
   if ($clen>=$start) //判断截取位置
    $tmpstr.=substr($str,$i,2);   //中文截取两个字符
   $i++; 
} 
   else 
{ 
   if ($clen>=$start) 
    $tmpstr.=substr($str,$i,1);   //非中文截取一个字符
} 
} 
return $tmpstr; 
} 
Function showShort($str,$len) 
{ 
$tempstr =$this-> cSubStr($str,0,$len); 
if ($str<>$tempstr) 
$tempstr .= ""; //要以什么结尾,修改这里就可以.
return $tempstr; 
}

}
$main = new Pageselectcustomer();
$main -> Main();
?>
  