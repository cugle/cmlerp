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
$str    �ַ�
$start �ַ���ȡλ��
$len    ��ȡ�ַ�����
======================================================
*/
Function cSubStr($str,$start,$len) //����3������ 
{ 
$strlen=strlen($str); // ��ȡ�ַ�����
$clen=0; 
for($i=0;$i<$strlen;$i++,$clen++) 
{ 
if ($clen>=$start+$len) //�����ڽ�ȡ�ַ�����������ѭ��
   break; 
if(ord(substr($str,$i,1))>0xa0) //ord �����������ַ��� ASCII (�������ұ�׼������) ����ֵ����������chr()�����෴��
{ //0xa0 ���� ʮ���� 160,0xa0��ʾ���ֵĿ�ʼ
   if ($clen>=$start) //�жϽ�ȡλ��
    $tmpstr.=substr($str,$i,2);   //���Ľ�ȡ�����ַ�
   $i++; 
} 
   else 
{ 
   if ($clen>=$start) 
    $tmpstr.=substr($str,$i,1);   //�����Ľ�ȡһ���ַ�
} 
} 
return $tmpstr; 
} 
Function showShort($str,$len) 
{ 
$tempstr =$this-> cSubStr($str,0,$len); 
if ($str<>$tempstr) 
$tempstr .= ""; //Ҫ��ʲô��β,�޸�����Ϳ���.
return $tempstr; 
}

}
$main = new Pageselectcustomer();
$main -> Main();
?>
  