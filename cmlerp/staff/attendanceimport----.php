<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><HEAD><TITLE></TITLE>
<META http-equiv=Content-Type content="text/html; charset=gbk">
<META http-equiv=ljz content=no-cache><LINK 
href="../common/css/button_css.css" type=text/css rel=stylesheet><LINK 
href="../common/css/page_title.css" type=text/css rel=stylesheet><LINK 
href="../common/css/browse.css" type=text/css rel=stylesheet>
<SCRIPT language=JavaScript>

   function secondlystep(){
     if( (document.exc_upload.excel_file.value.length==0))
     { 
       alert('�����ѡ���ļ�'); 
       return; 
     }
     var myFile   = document.getElementById("excel_fileid"); 
     var filePath = myFile.value; 
     var dotNdx   = filePath.lastIndexOf('.');                                                                         
     var exetendName =  filePath.slice(dotNdx + 1).toLowerCase();   
     if((exetendName == exc_upload.selfileformat.value ))   {  
     }else{
        alert( "ѡ�е��ļ���ʽ����ѡ��Ҫ������ļ���ʽ��һ��");
        return ;   
     }
     exc_upload.submit();
   }
   function thirdstep(){; 
         exc_upload.submit();
   }
</SCRIPT>

<META content="MSHTML 6.00.2900.6003" name=GENERATOR></HEAD>
<BODY text=#000000 bgColor=#ffffff leftMargin=2 topMargin=0 width="100%">
<TABLE height=30 cellSpacing=0 cellPadding=0 width="100%" border=0>
  <TBODY>
  <TR>
    <TD class=pagetitle vAlign=center width="100%" height=30>&nbsp;&nbsp;<IMG 
      src="../common/img/nowspace.jpg" align=absMiddle>���뵥λ�ļ�</TD>
  <TR>
    <TD align=left>
      <DIV style="MARGIN: 0px 0px 0px 12px"><IMG 
      src="../common/img/line.jpg">
	  
	  
	  <?php
$fname = $_FILES['MyFile']['name']; 
$do = copy($_FILES['MyFile']['tmp_name'],$fname); 
if ($do) 
{ 
echo"���������ֳ�";
} else { 
echo ""; 
}
?></DIV></TD></TR></TBODY></TABLE>
<DIV align=center>
<TABLE class=InputFrameMain height=100 cellSpacing=0 cellPadding=0 width="70%" 
border=0>
  <TBODY>
  <TR>
    <TD class=inputtitle align=middle width="19%">�����ļ�</TD>
    <TD class=inputtitleright width="81%"><p>&nbsp;&nbsp;&nbsp;</p></TD>
  </TR>
  <TR>
    <TD colSpan=4 height=10></TD></TR>
<FORM action="" encType=multipart/form-data  METHOD="POST">
  <TR>
    <TD class=form_label><input name='agencyid' type='hidden' value='<?php echo $_GET['agencyid']?>'>
      <input name='pre' type='hidden' value='<?php echo $_GET['pre']?>'>
      ����CVS����:</TD>
    <TD><INPUT type=file name=MyFile>
      &nbsp;<FONT  color=#ff0000>
 	  
	  
	  <B>[#error#]</B></FONT></TD>
  </TR>
  <TR>
    <TD class=form_label>ѡ�����ļ���ʽ:</TD>
    <TD><SELECT name=selfileformat><OPTION value=csv 
        selected>csv</OPTION></SELECT></TD>
  <TR>
    <TD class=form_label2 colSpan=2><FONT 
      color=#ff0000><B>ע�⣺�����ļ��ĸ�ʽ��[#format#]</B></FONT></TD>
  </TR>
  <TR>
    <TD class=form_label2 
      colSpan=2>&nbsp;&nbsp;ע�⣺txt��ʽ��csv��ʽ���ļ���ÿһ�е�����Ҫ�ö��ŷֿ��ġ� 
      ���ܵ����һ���ļ����ݡ�<BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;�ļ�������ҪΪ����(GB2312)��Ӣ��,��������(����)����ִ�����ʾ��</TD></TR>
  <TR class=bottombotton>
    <TD align=middle colSpan=2><INPUT type=hidden value=1 name=step>
      <INPUT  class=buttonsmall  name="submit" type=submit value=�ύ> <INPUT  class=buttonsmall  name="submit2" type=button value=����  onclick="window.location ='[#backpath#]'"></TD>
  </TR></FORM>		  
</TBODY></TABLE><?php
error_reporting(0); 
//����CSV��ֵ��ļ� 
$connect=mysql_connect("localhost","root","123qaz") or die("could not connect to database"); 
mysql_select_db("cmlerp",$connect) or die (mysql_error()); 
$fname = $_FILES['MyFile']['name']; 
$handle=fopen("$fname","r"); 
while($data=fgetcsv($handle,10000,",")) 
{ 
//echo $data[0];
//echo "SELECT employee_id FROM `".$_POST['pre']."employee` WHERE employee_name='$data[0]' and agencyid=".$_POST["agencyid"];

if($_POST['pre']==''){$pre=$_POST['pre'];}else{$pre='s_';}

$result=mysql_query("SELECT * FROM `s_employee` WHERE employee_name='$data[0]' and agencyid=".$_POST['agencyid']) ;
while($row = mysql_fetch_array($result))
  {
   $data[0]=$row['employee_id'];

  }
echo $row['employee_id'];

$q="insert into s_attendance(`employee_id`,`planattendance`,`actualattendance` , `othours` ,`leavehours` ,agencyid) values ('$data[0]','$data[1]','$data[2]','$data[3]','$data[4]',".$_POST["agencyid"].")"; 


mysql_query($q) or die (mysql_error()); 

} 
fclose($handle); 
?> 	</DIV></BODY></HTML>
