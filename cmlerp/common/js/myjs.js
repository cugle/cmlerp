function putcon(){
   var arrItems=new Array();
   var strItem ;
   var szRef = "../getdata/getconsultant.html?template=getstaff&parameter=employeelevelid&keywords=2,3" ;
   var strItem = window.showModalDialog(szRef,"","Help=no;status:no;dialogWidth=700px;dialogHeight=500px;scroll=yes;");
  if(!strItem){return}
   arrItems = strItem.split("@@@");
   form1.employee_id.value = arrItems[0] ;  
   form1.employee_name.value = arrItems[1] ;    
} 
function putbeauty(){
   var arrItems=new Array();
   var strItem ;
   var szRef = "../getdata/getconsultant.html?template=getstaff&parameter=employeelevelid&keywords=1" ;
   var strItem = window.showModalDialog(szRef,"","Help=no;status:no;dialogWidth=700px;dialogHeight=500px;scroll=yes;");
  if(!strItem){return}
  arrItems = strItem.split("@@@");
   form1.beauty_id.value = arrItems[0] ;  
   form1.beauty_name.value = arrItems[1] ;    
} 
function towbeauty(){
	services_name=form1.services_name.value;
	beauty_id=form1.beauty_id.value;
	beauty_id = beauty_id.split(";");
	str='双人测试';
	//如果是双人项目
      if(str.indexOf(services_name)>=0 && beauty_id.length<2)
        {
		alert("对不起，该项目需要两个美容师，请选择两个美容师");
		return false; 
        }else{
		return true;
		}
 
} 