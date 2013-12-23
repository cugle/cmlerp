/*
var m_prev_Common_onkeydown = null;
m_prev_Common_onkeydown = document.onkeydown;
document.onkeydown = _Common_document_onkeydown;

function _Common_document_onkeydown(){
	switch (event.keyCode){
		case 65:	//A	
			if ((event.altKey == true) && !(form1.thenew.disable)){
				nh('{shownew}');
			}
			break;	
		case 68:	//ctrl+D
			if ((event.altKey == true) && !(form1.thedel.disable)){
				submit_del();
			}
			break;
		case 70:	//F
			if ((event.altKey == true) && !(form1.thefind.disable)){
				mainfind();
			}
			break;
	}
}*/

var ie=document.all
var dom=document.getElementById
var ns4=document.layers
var calunits=document.layers? "" : "px"

var bouncelimit=32 //(must be divisible by 8)
var direction="up"

function initbox(){
if (!dom&&!ie&&!ns4)
return
crossobj=(dom)?document.getElementById("layermove").style : ie? document.all.layermove : document.layermove
scroll_top=(ie)? truebody().scrollTop : window.pageYOffset
crossobj.top=scroll_top-300+calunits
crossobj.visibility=(dom||ie)? "visible" : "show"
dropstart=setInterval("dropin()",50)
}

function dropin(){
scroll_top=(ie)? truebody().scrollTop : window.pageYOffset
if (parseInt(crossobj.top)<100+scroll_top)
crossobj.top=parseInt(crossobj.top)+40+calunits
else{
clearInterval(dropstart)
bouncestart=setInterval("bouncein()",50)
}
}

function bouncein(){
crossobj.top=parseInt(crossobj.top)-bouncelimit+calunits
if (bouncelimit<0)
bouncelimit+=8
bouncelimit=bouncelimit*-1
if (bouncelimit==0){
clearInterval(bouncestart)
}
}

function truebody(){
return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function mainfind(){
bouncelimit=32
direction="up"

document.getElementById("whatdofindq").disabled = true;
document.getElementById("howdofindq").disabled = true;
document.getElementById("findwhatq").disabled = true;
document.getElementById("buttonq").disabled = true;

initbox()
}

function InitAjax()
{
　var ajax=false; 
　try { 
　　ajax = new ActiveXObject("Msxml2.XMLHTTP"); 
　} catch (e) { 
　　try { 
　　　ajax = new ActiveXObject("Microsoft.XMLHTTP"); 
　　} catch (E) { 
　　　ajax = false; 
　　} 
　}
　if (!ajax && typeof XMLHttpRequest!='undefined') { 
　　ajax = new XMLHttpRequest(); 
　} 
　return ajax;
}

   function CheckAll(form){
   	 var str_colorflag="#FFFFFF";
     for (var i=0;i<form.elements.length;i++){ 
         var strelement = form.elements[i];
         if (strelement.name != 'chkall' && !strelement.disabled){
             strelement.checked = form.chkall.checked;
             if(form.chkall.checked==true){
               var ln = contenttableid.getElementsByTagName("tr").length;
               for(k=0;k<ln;k++){
                  contenttableid.getElementsByTagName("tr")[k].style.backgroundColor="#FFEA96";
               }
             }else{
               var ln = contenttableid.getElementsByTagName("tr").length;
               for(k=0;k<ln;k++){
               	  if(str_colorflag=="#FFFFFF"){
                     contenttableid.getElementsByTagName("tr")[k].style.backgroundColor="#EEF2FF";
                     str_colorflag="#EEF2FF"
                  }else{
                     contenttableid.getElementsByTagName("tr")[k].style.backgroundColor="#FFFFFF";
                     str_colorflag="#FFFFFF"
                  }
               }
             }
         }
      }
   }
   
   function viewPage(num){
        form1.now.value = num;
        //2007-5-8日加入
        thisURL = document.URL;  
        tmpUPage = thisURL.split( "/" );  
        thisUPage = tmpUPage[ tmpUPage.length-1 ];  
        tmpUrlPage = thisUPage.split( "?" );  
        thisUPage = tmpUrlPage[0];
        document.form1.action=thisUPage;
        //-------------------
	      form1.submit(); 
   }
   
   function chgrows(){
	   form1.submit(); 
   }
   
      
   function submit_order(done){
    	if(form1.order.value == done){ 
       	   if(form1.upordown.value == 'asc'){
       	      form1.upordown.value = "desc";
       	   }else{ 
              form1.upordown.value = "asc";
           }   
     }else{
       form1.upordown.value = "asc";
     } 
     form1.order.value = done;
	   form1.submit();
   }
   
   function editdata(url2){
	   
	   if(url2 == ""){
		   return false;   
	  }
	   
	    var checknum,i,selectvalue,checkdd;  
      if (confirm("是否操作所有选定的资料？")) {
	    	checknum = 0;            
        selectvalue = "";
	      checkdd =   form1.recodeto.value - form1.recodeform.value;   
         		 
        for(i=1;i <= checkdd ;i++){ 
           if (document.getElementById("arrlabel["+i+"]").checked==true){
           	  if(selectvalue==""){
                selectvalue = document.getElementById("arrlabel["+i+"]").value;
				
              }else{
                selectvalue =selectvalue+"@"+document.getElementById("arrlabel["+i+"]").value;
              } 
		      	  checknum ++ ;
           } 	
        }
		
        var postStr = "selectvalue="+ selectvalue;
　      //需要进行Ajax的URL地址
　      var url = "../ajaxread/readalledit.php";
　      //实例化Ajax对象
　      var ajax = InitAjax();
　      //使用POST方式进行请求
　      ajax.open("POST", url, true); 
        //定义传输的文件HTTP头信息
　      ajax.setRequestHeader("Content-Type","application/x-www-form-urlencoded"); 
　      //发送POST数据
　      ajax.send(postStr);
　      //获取执行状态
　      ajax.onreadystatechange = function() { 
　      //如果执行是状态正常，那么就把返回的内容赋值给上面指定的层
　        if(ajax.readyState == 4 && ajax.status == 200){
	           if(ajax.responseText == 1 && checknum > 0 ){
       	        window.location.href = url2+'&tempurl=' + tempurl;
				     }else{
				        return false; 
				     }
　        }
　      }
             
      }else{
         return false; 
      }
 }

   
   
   function submit_del(){
   	   /*var strtemptr="arrlabel[8]";
   	   if(document.getElementById(strtemptr).checked==true){
   	   	   alert(document.getElementById(strtemptr).value);
   	   }else{
   	       alert("");
   	   }*/
      if (confirm("是否删除所有选定的记录？")) {
      	
        //form1.action.value = "delete";
        //2007-5-8日加入
        thisURL = document.URL;  
        tmpUPage = thisURL.split( "/" );  
        thisUPage = tmpUPage[ tmpUPage.length-1 ];  
        tmpUrlPage = thisUPage.split( "?" );  
        thisUPage = tmpUrlPage[0];
        document.form1.action=thisUPage+"?action=delete";
        //----------------
	      form1.submit();
      }
   } 
   
   function submit_collect(){
      if (confirm("是否汇总数据？")) {
        //form1.action.value = "collect";
        //2007-5-8日加入
		
        thisURL = document.URL;  
        tmpUPage = thisURL.split( "/" );  
        thisUPage = tmpUPage[ tmpUPage.length-1 ];  
        tmpUrlPage = thisUPage.split( "?" );  
        thisUPage = tmpUrlPage[0];		 
        document.form1.action=thisUPage+"?action=collect";
        //-----------------------
		 
	      form1.submit();
      }
   }
  
    function linkurl(url){
        /*arrytemp = url.split("?");
        finduse = "" ;
        for (var i=0;i<form1.elements.length;i++){ 
          var e = form1.elements[i];
          if ((e.name == 'whatdofind[]') && (e.selectedIndex > 0)){
          	  if(finduse == ''){
	          	  finduse = '&whatdofind[]='+ e.options[e.selectedIndex] ;
	          }else{
  	          	  finduse = finduse + '&whatdofind[]=' + e.options[e.selectedIndex] ;
	          }
          }           
          if ((e.name == 'howdofind[]') && (e.selectedIndex > 0)){
          	  if(finduse == ''){
	          	  finduse = '&howdofind[]='+ e.options[e.selectedIndex] ;
	          }else{
  	          	  finduse = finduse + '&howdofind[]=' + e.options[e.selectedIndex] ;
	          }
          }
          if ((e.name == 'findwhat[]') && (e.value != "undefined")){
          	  if(finduse == ''){
	          	  finduse = '&findwhat[]='+ e.value ;
	          }else{
  	          	  finduse = finduse + '&findwhat[]=' + e.value ;
	          }
	  }
        }

    	if (typeof(arrytemp[1]) == "undefined"){
	    urluse = url +'?tempurl=' + tempurl + finduse ;
	}else{
	    urluse = url +'&tempurl=' + tempurl + finduse ;
	}*/
	    //if(parent.parent.canResize) parent.parent.frames[3].location.href = urluse
	    // else
	    //alert( url+'&tempurl=' + tempurl);
	     	window.location.href = url+'&tempurl=' + tempurl;
    }
    
    function windowopen(url){   //------------2005-1-1-------------------
    	window.open(url);
    }    	
    function newdata(url){
	     //if(parent.parent.canResize) parent.parent.frames[3].location.href = url
	     //else 
alert("tets");
       tmpUrlPage = url.split("?");
       if(tmpUrlPage[1]!=undefined){
       	 window.location.href = url+'&tempurl=' + tempurl;
alert(url+'&tempurl=' + tempurl);
       }else{
	       window.location.href = url+'?tempurl=' + tempurl;
alert(url+'?tempurl=' + tempurl);
	     }
    }
    function inputfiledata(url){    //导入文件
	     window.location.href = url
    }
    
    function outtoexcledata(outtoexcle){  //导出excel
    	  //处理要导出的数据
    	  var fielddd,i,fieldnum,outputfieldvalue;   
        fieldnum = 0;   
        outputfieldvalue = "";
        fielddd = document.getElementsByName("outputfield").length;   
          
        for(i=0;i<fielddd;i++){   
           if (document.getElementsByName("outputfield")[i].checked==true){
           	  if(outputfieldvalue==""){
                outputfieldvalue =document.getElementsByName("outputfield")[i].value;   
              }else{
                outputfieldvalue =outputfieldvalue+"@"+document.getElementsByName("outputfield")[i].value;
              }
              fieldnum =fieldnum + 1;   
           } 
        }     
        if(fieldnum==0){   
          alert("必须有一条记录被选中！");   
          return false;   
        }else{   
           if(confirm("确定要导出这些属性吗？请再次确认！")){
              var postStr = "outputfieldvalue="+ outputfieldvalue;
　            //需要进行Ajax的URL地址
　            var url = "../ajaxread/ajaxexcelfield.php";
　            //实例化Ajax对象
　            var ajax = InitAjax();
　            //使用POST方式进行请求
　            ajax.open("POST", url, true); 
              //定义传输的文件HTTP头信息
　            ajax.setRequestHeader("Content-Type","application/x-www-form-urlencoded"); 
　            //发送POST数据
　            ajax.send(postStr);
　            //获取执行状态
　            ajax.onreadystatechange = function() { 
　            //如果执行是状态正常，那么就把返回的内容赋值给上面指定的层
　              if(ajax.readyState == 4 && ajax.status == 200){
	                 var objexceldate=document.form1.outputexceldate;
	                 if(objexceldate[0].checked==true){
	                   window.location.href = outtoexcle+'?outputexceldate=0&tempurl='+tempurl;
	                 }else{
	                   window.location.href = outtoexcle+'?outputexceldate=1&tempurl='+tempurl;
	                 }
　              }
　            }
           }else{
              return false; 
           }  
        }
        //-------------------------------------
    	  
    }
    
    function closeexceldate(){  //关闭导出excel层
    	  exceltype.style.display="none";
    }
    
	function closeexcelselect(){
		  excelselect.style.display="none";
		  sendingbg.style.display="none";
		  exceltype.style.display="none";
	}
	
    function openexcledata(){ //打开导出excel层
    	  exceltype.style.display="";
    }
        
    //function mainfind(){
	 //    layermove.style.visibility='visible';
    //}
    
    function selectit(){
	    layermove.style.top = (document.body.offsetHeight - layermove.offsetHeight) / 3;
	    layermove.style.left = (document.body.offsetWidth - layermove.offsetWidth) / 2;
	    
		  document.onmousedown = DownMouse;
	    window.document.onmousemove = MoveLayer;
	    window.document.onmouseup   = UpMouse;
		 
    }

    var down = false; 
    
    function UpMouse(){ 
    	down = false; 
    } 
    
    var startX = 0; startY = 0;startLeft = 0;startTop = 0; 
    
    function MoveLayer(){ 
    	if (down && (event.button==1)){
    		layerX = startLeft+event.clientX-startX;
    		layerY = startTop+event.clientY-startY;
    		if(layerX<0)
    			layerX = 0;
    		if(layerY<0)
    			layerY = 0;
    		if(layerX + thelayer.offsetWidth > document.body.offsetWidth)
    			layerX = document.body.offsetWidth - thelayer.offsetWidth;
    		if(layerY + thelayer.offsetHeight > document.body.offsetHeight)
    			layerY = document.body.offsetHeight - thelayer.offsetHeight;
    		thelayer.style.pixelTop = layerY;
    		thelayer.style.pixelLeft = layerX;
    	}
    	positiontip();
    }
    
    function DownMouse(){ 
    	if (!document.all) return true;
    	//alert(event.which);
    	if ((event.srcElement.id=="tdmove" || event.srcElement.id=="fontmove") && (event.button==1)){//对应要拖动的层的name 
    		thelayer = layermove; 
    		down = true; 
    		startX = event.clientX; 
    		startY = event.clientY; 
    		startLeft = thelayer.style.pixelLeft; 
    		startTop = thelayer.style.pixelTop; 
    	}
    }
    
    function dofind(){ 
      /*var usedir = location.href.substring(0,location.href.lastIndexOf('/')+1);
      var useurl = location.href.substring(usedir.length,location.href.length+1);
      var cc = useurl.indexOf('&whatdofind[]') ;
      var bb = useurl.substr(0,cc);
      form1.action = bb ;*/
      form1.submit();
    }
    
      function cannotfind(){ 
 
   document.getElementById("whatdofind").value = ""; 		
   document.getElementById("howdofind").value = ""; 		
   document.getElementById("findwhat").value = ""; 
 
  var postStr ;
　//需要进行Ajax的URL地址
　var url = "../ajaxread/readclearselect.php";
　//实例化Ajax对象
　var ajax = InitAjax();
　//使用POST方式进行请求
　ajax.open("POST", url, true); 
  //定义传输的文件HTTP头信息
　ajax.setRequestHeader("Content-Type","application/x-www-form-urlencoded"); 
　//发送POST数据
　ajax.send(postStr);
　//获取执行状态
　ajax.onreadystatechange = function() { 
　　//如果执行是状态正常，那么就把返回的内容赋值给上面指定的层
　　if (ajax.readyState == 4 && ajax.status == 200) { 
      //获取显示层的位置    
	  
     form1.submit();
　　} 
　}
 } 
    
    function hiddenfind(){
    	
    	 	document.getElementById("whatdofindq").disabled = "";
        document.getElementById("howdofindq").disabled = "";
        document.getElementById("findwhatq").disabled = "";
        document.getElementById("buttonq").disabled = "";
    	
    	layermove.style.visibility='hidden';
    }
    
    function selecl_file(){
    	form1.submit();
    }
    
function help(){  
   if(document.getElementById("helpid").style.pixelHeight == 0 &&  document.getElementById("helpid").style.display == "none"){      
       document.getElementById("helpid").style.display = "";
	   document.getElementById("whatdofindq").style.display = "none";
	  ChS();
   }else{   
	   HhS();    	  
   }
}

function ChS(){
if(helpid.style.pixelHeight<400){helpid.style.pixelHeight+=10;setTimeout("ChS()",0.5);
} 
}
function HhS(){
if(helpid.style.pixelHeight > 0){helpid.style.pixelHeight-=10;setTimeout("HhS()",0.5);
}
if(helpid.style.pixelHeight == 0){
 document.getElementById("helpid").style.display = "none";
 document.getElementById("whatdofindq").style.display = "";
 }
}

document.onclick = function(){
 if(event.srcElement.name != "save32" && event.srcElement.id != "helpid"){
    HhS();
 }
}
function  loadingok(){
parent.parent.frames[1].loadingbg.style.display ="none";
parent.parent.frames[0].form1.loadingok.value  = "";
}

function showexcledata(url){
	
	excelselect.style.display="";
	sendingbg.style.display="";
}

document.onload = loadingok();

function showMenu()

{
 
    popMenu(itemMenu,100);
    event.returnValue=false;
    event.cancelBubble=true;
    return false;
}

function popMenu(menuDiv,width)

{
    //创建弹出菜单
    var pop=window.createPopup();
    //设置弹出菜单的内容
    pop.document.body.innerHTML=menuDiv.innerHTML;  
    var rowObjs=pop.document.body.all[0].rows;
    //获得弹出菜单的行数
    var rowCount=rowObjs.length;
	var x = 2;
    //循环设置每行的属性
    for(var i=0;i<rowObjs.length;i++)
    {
        //如果设置该行不显示，则行数减一
        //var hide= rowObjs[i].style.display != '';
					
        if(rowObjs[i].cells[0].style.display == 'none'){         
		   rowCount--;
		   x ++ ;
        }
        //设置是否显示该行
      //  rowObjs[i].style.display=(hide)?"none":"";

        //设置鼠标滑入该行时的效果
        if( i != '0'){
        rowObjs[i].cells[0].onmouseover=function()
        {
            this.style.background="#818181";
            this.style.color="white";
        }

        //设置鼠标滑出该行时的效果

        rowObjs[i].cells[0].onmouseout=function(){
            this.style.background="#f4f4f4";
            this.style.color="black";
        }
	    }
    }
   
    //屏蔽菜单的菜单

    pop.document.oncontextmenu=function()
    {
            return false;
    }

    //选择右键菜单的一项后，菜单隐藏

    pop.document.onclick=function()
    {
            pop.hide();
    }
    //显示菜单
	 
    pop.show(event.clientX-1,event.clientY,width,rowCount*18+x,document.body);
    return true;
}

//获取多选框选择后改变底色
function changecolor(theRow , str_color , labelnum , selcolor){
	
	strcheckboxid = "arrlabel["+labelnum+"]";
	if(document.getElementById(strcheckboxid).checked == true){
		theRow.style.backgroundColor=selcolor;
	}else{
	  theRow.style.backgroundColor=str_color;
  }
}

function browsezhantie(){
if( document.selection.type == "None"){
document.execCommand('Paste')
}else{
return false;
}
}
document.oncontextmenu = function(evt){      //这里比起之前多了个参数，这是由于firefox需要吧event作为参数传输才能进行处理
	evt = evt || window.event;  
	return false;   
}