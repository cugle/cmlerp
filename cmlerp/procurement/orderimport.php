<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><HEAD><TITLE></TITLE>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
<META http-equiv=ljz content=no-cache><LINK 
href="[#path#]css/button_css.css" type=text/css rel=stylesheet><LINK 
href="[#path#]css/page_title.css" type=text/css rel=stylesheet><LINK 
href="[#path#]css/browse.css" type=text/css rel=stylesheet>
<SCRIPT language=JavaScript>

   function secondlystep(){
     if( (document.exc_upload.excel_file.value.length==0))
     { 
       alert('你必须选择文件'); 
       return; 
     }
     var myFile   = document.getElementById("excel_fileid"); 
     var filePath = myFile.value; 
     var dotNdx   = filePath.lastIndexOf('.');                                                                         
     var exetendName =  filePath.slice(dotNdx + 1).toLowerCase();   
     if((exetendName == exc_upload.selfileformat.value ))   {  
     }else{
        alert( "选中的文件格式和你选择要导入的文件格式不一致");
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
      src="[#path#]img//nowspace.jpg" align=absMiddle>导入单位文件</TD>
  <TR>
    <TD align=left>
      <DIV style="MARGIN: 0px 0px 0px 12px"><IMG 
      src="[#path#]img/line.jpg"></DIV></TD></TR></TBODY></TABLE>
<DIV align=center>
<TABLE class=InputFrameMain height=100 cellSpacing=0 cellPadding=0 width="70%" 
border=0>
  <TBODY>
  <TR>
    <TD class=inputtitle align=middle width="19%">导入文件</TD>
    <TD class=inputtitleright width="81%"><p>&nbsp;&nbsp;&nbsp;</p></TD>
  </TR>
  <TR>
    <TD colSpan=4 height=10></TD></TR>
<FORM action="[#PHP_SELF#]" encType=multipart/form-data  METHOD="POST">
  <TR>
    <TD class=form_label>导入CVS数据:</TD>
    <TD><INPUT type=file name=MyFile>
      &nbsp;<FONT  color=#ff0000><B>[#error#]</B></FONT></TD>
  </TR>
  <TR>
    <TD class=form_label>选择导入文件格式:</TD>
    <TD><SELECT name=selfileformat><OPTION value=csv 
        selected>csv</OPTION></SELECT></TD>
  <TR>
    <TD class=form_label2 colSpan=2><FONT 
      color=#ff0000><B>注意：导入文件的格式：[#format#]</B></FONT></TD>
  </TR>
  <TR>
    <TD class=form_label2 
      colSpan=2>&nbsp;&nbsp;注意：txt格式和csv格式的文件，每一列的内容要用逗号分开的。 
      不能导入第一行文件数据。<BR>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;文件字体需要为简体(GB2312)或英文,其他字体(编码)会出现错误提示。</TD></TR>
  <TR class=bottombotton>
    <TD align=middle colSpan=2><INPUT type=hidden value=1 name=step>
      <INPUT  class=buttonsmall  name="submit" type=submit value=提交> <INPUT  class=buttonsmall  name="submit2" type=button value=返回  onclick="window.location ='[#backpath#]'"></TD>
  </TR></FORM></TBODY></TABLE></DIV></BODY></HTML>
