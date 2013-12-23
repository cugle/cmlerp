<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style type="text/css">
<!--
.style3 {
	font-size: 48px;
	color: #FFFFFF;
}
body {
	background-color: #CCCCCC;
}
.style4 {
	color: #003366;
	font-weight: bold;
}
#Layer1 {
	width:300px; 
	height:100px; 
	position:absolute; 
	margin:-50px 0px 0px -150px; 
	top:50%; 
	left:50%
}
-->
</style>
<script language="javaScript">
function changerSecond(){
	var so = document.getElementById('second');
	var sv = so.innerHTML;
	so.innerHTML = parseInt(sv)-1;
	if(parseInt(sv)<=0) location.href='./';
	else setTimeout(changerSecond,1000);
}
</script>
<title>错误，请等待！</title>
</head>

<body onload="changerSecond();">
<div id="Layer1" style="position:absolute; z-index:1">
  <p align="center" class="style4">错误登录次数太多</p>
  <p align="center">请您于&nbsp;<span class="style3" id="second">
  <?
	echo $this -> getValue('loginErrorTimeOut')-(time()-$_SESSION['errorTime'])+3;//错误超时秒数
  ?>
  </span>&nbsp;秒之后重新登录 </p>
</div>
</body>
</html>