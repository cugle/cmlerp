<?php
/**
*�������߱༭��PHP��
*
*gently
*2007��11��2��
*���ͣ�http://www.zendstudio.net/
*
**/
if($_GET['action']=='upload'){
	$fileType=array('jpg','gif','bmp','png');//�����ϴ����ļ�����
	$upfileDir='uploadfile/';
	$maxSize=800; //��λ��KB
	if(!in_array(substr($_FILES['file1']['name'],-3,3),$fileType))
		die("<script>alert('�������ϴ������͵��ļ���-808');window.parent.\$('divProcessing').style.display='none';history.back();</script>");
	if(strpos($_FILES['file1']['type'],'image')===false)
		die("<script>alert('�������ϴ������͵��ļ���');window.parent.\$('divProcessing').style.display='none';history.back();</script>");
	if($_FILES['file1']['size']> $maxSize*1024)
		die( "<script>alert('�ļ�����');window.parent.\$('divProcessing').style.display='none';history.back();</script>");
	if($_FILES['file1']['error'] !=0)
		die("<script>alert('δ֪�����ļ��ϴ�ʧ�ܣ�');window.parent.$('divProcessing').style.display='none';history.back();</script>");
	$targetDir=dirname(__FILE__).'/../../'.$upfileDir;
	$targetFile=date('Ymd').time().substr($_FILES['file1']['name'],-4,4);
	$realFile=$targetDir.$targetFile;
	if(function_exists('move_uploaded_file')){
		 move_uploaded_file($_FILES['file1']['tmp_name'],$realFile) && die("<script>window.parent.LoadIMG('../{$upfileDir}{$targetFile}');</script>");
	}
	else{
		@copy($_FILES['file1']['tmp_name'],$realFile) && die("<script>window.parent.LoadIMG('../{$upfileDir}{$targetFile}');</script>");
	}
}

?>