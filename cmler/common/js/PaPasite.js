	function CheckAll(checkBoxName){
			var checkBox = document.getElementsByName(checkBoxName);
			for (var i = 0; i < checkBox.length; i++){
				var temp = checkBox[i];
				temp.checked = true;
			}
	}
	function CheckReverse(checkBoxName){
		var checkBox = document.getElementsByName(checkBoxName);
		for (var i = 0; i < checkBox.length; i++){
			var temp = checkBox[i];
			temp.checked = !temp.checked;
		}
	}
	function CheckSubmitOne(o){
		var checkBox = document.getElementsByName("TheID");
		var temp = false;
		for (var i = 0; i < checkBox.length; i++){
			if (checkBox[i].checked){temp=true;}
		}
		if (temp==false){alert("你需要至少选择一条信息..");return false;
		}else{return confirm("你确认你要执行该操作吗？");	}
	}
	function DelAlert(){if (!confirm("确定执行该操作吗？\n\n该操作可能无法挽回...")){return false;}	}
	function PPOnlyInt(o){if(".8.13.37.39.46.48.49.50.51.52.53.54.55.56.57.96.97.98.99.100.101.102.103.104.105.".indexOf("."+event.keyCode+".")<0){return false;}}
	function ListInClass(o){
		var v=document.getElementById("ClassID").value;
		if (v==""){
			alert("你还没有选择分类");
			return false;
		}else if(o==""){
			window.location.href="?ClassID="+v;
		}else{
			return true;
		}
	}
	function TrOver(o){o.style.backgroundColor='#F3F3F3'}
	function TrOut(o){o.style.backgroundColor=''}
//=====================================
function CheckAddNewsSubmit(o){
	if (document.getElementById("ClassID").value==""){alert("你还没有选择信息分类");return false;}
	if (document.getElementById("Title").value==""){alert("信息标题不能为空...");return false;}
}

function PreviewImg(){ 
	var o = document.getElementById("Img"); 
	var img = document.getElementById("DivImg");
	var patn = /\.jpg$|\.gif$/i; 
	if(patn.test(o.value)){
		img.src = o.value;
		img.style.height="100px";
		img.style.display="";
		document.getElementById("DeleteImageButton").style.display="";
	}else {
		if (o.value!==""){alert("只接受jpg和gif格式");}
		o.value="";
	}
}
function PPUploadPic(){
	var o = document.getElementById("Img");
	if (o.value==""){
		window.open("PPUpload.Asp?InputName=","papasite","width=420,height=150");
	}else{
		if (confirm("替换原图片？")){
			window.open("PPUpload.Asp?Del="+o.value,"papasitedel","width=420,height=150");
		}
	}
}
function DeleteImg(){
	var o = document.getElementById("Img"); 
	var oimg = document.getElementById("DivImg");
	document.getElementById("DeleteImageButton").style.display="none";
	if (o.value!==""){
		var sRnd=Math.floor(Math.random()*100000)+1
		var ajax=new AJAXRequest;
		ajax.get(
			"?Action=DelNewsimg&Img=" + o.value + "&Rnd="+sRnd,
			function(obj) { 
				var s=obj.responseText;
			}
		);
	}
	o.value="";
	oimg.src="";
	oimg.style.height="0";
	oimg.style.display="none";
}
function DeleteImage(s){
	if (confirm("确定删除该图片？")){DeleteImg(s);}
}
function ChangeEditor(ID){
	var IsMSIE=navigator.userAgent.indexOf("MSIE");
	if (IsMSIE>0)
	{
		var o=document.getElementById('myEditor');
		document.getElementById('Content').value=myEditor.getHTML();
		if(ID==1){o.src="PPEditor/editor.htm?id=Content&ReadCookie=0";}
		if(ID==2){o.src="PPEditor/ewebeditor.htm?id=Content&style=blue";}
		if(ID==3){o.src="PPEditor/ewebeditor.htm?id=Content&style=full";}
		if(ID==4){o.src="PPEditor/ewebeditor.htm?id=Content&style=mini";}
		TabsID.style.fontWeight="normal";Tabs[(ID-1)].style.fontWeight="Bold";TabsID=Tabs[(ID-1)];
	}else{
		alert("暂时不支持非IE浏览器下切换编辑器模式");
	}
}
//=====================================
function AJAXRequest() {
	var xmlObj = false;
	var CBfunc,ObjSelf;
	ObjSelf=this;
	try { xmlObj=new XMLHttpRequest; }
	catch(e) {
		try { xmlObj=new ActiveXObject("MSXML2.XMLHTTP"); }
		catch(e2) {
			try { xmlObj=new ActiveXObject("Microsoft.XMLHTTP"); }
			catch(e3) { xmlObj=false; }
		}
	}
	if (!xmlObj) return false;
	if(arguments[0]) this.url=arguments[0]; else this.url="";
	if(arguments[1]) this.callback=arguments[1]; else this.callback=function(obj){return};
	if(arguments[2]) this.content=arguments[2]; else this.content="";
	if(arguments[3]) this.method=arguments[3]; else this.method="POST";
	if(arguments[4]) this.async=arguments[4]; else this.async=true;
	this.send=function() {
		var purl,pcbf,pc,pm,pa;
		if(arguments[0]) purl=arguments[0]; else purl=this.url;
		if(arguments[1]) pc=arguments[1]; else pc=this.content;
		if(arguments[2]) pcbf=arguments[2]; else pcbf=this.callback;
		if(arguments[3]) pm=arguments[3]; else pm=this.method;
		if(arguments[4]) pa=arguments[4]; else pa=this.async;
		if(!pm||!purl||!pa) return false;
		xmlObj.open (pm, purl, pa);
		if(pm=="POST") xmlObj.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		xmlObj.onreadystatechange=function() {
			if(xmlObj.readyState==4) {
				if(xmlObj.status==200) {
					pcbf(xmlObj);
				}
				else {
					pcbf(null);
				}
			}
		}
		if(pm=="POST")
			xmlObj.send(pc);
		else
			xmlObj.send("");
	}
	this.get=function() {
		var purl,pcbf;
		if(arguments[0]) purl=arguments[0]; else purl=this.url;
		if(arguments[1]) pcbf=arguments[1]; else pcbf=this.callback;
		if(!purl&&!pcbf) return false;
		this.send(purl,"",pcbf,"GET",true);
	}
	this.post=function() {
		var fo,pcbf,purl,pc,pm;
		if(arguments[0]) fo=arguments[0]; else return false;
		if(arguments[1]) pcbf=arguments[1]; else pcbf=this.callback;
		if(arguments[2])
			purl=arguments[2];
		else if(fo.action)
			purl=fo.action;
		else
			purl=this.url;
		if(arguments[3])
			pm=arguments[3];
		else if(fo.method)
			pm=fo.method.toLowerCase();
		else
			pm="post";
		if(!pcbf&&!purl) return false;
		pc=this.formToStr(fo);
		if(!pc) return false;
		if(pm) {
			if(pm=="post")
				this.send(purl,pc,pcbf,"POST",true);
			else
				if(purl.indexOf("?")>0)
					this.send(purl+"&"+pc,"",pcbf,"GET",true);
				else
					this.send(purl+"?"+pc,"",pcbf,"GET",true);
		}
		else
			this.send(purl,pc,pcbf,"POST",true);
	}
	this.formToStr=function(fc) {
		var i,query_string="",and="";
		for(i=0;i<fc.length;i++) {
			e=fc[i];
			if (e.name!='') {
				if (e.type=='select-one') {
					element_value=e.options[e.selectedIndex].value;
				}
				else if (e.type=='checkbox' || e.type=='radio') {
					if (e.checked==false) {
						continue;	
					}
					element_value=e.value;
				}
				else {
					element_value=e.value;
				}
				element_value=encodeURIComponent(element_value);
				query_string+=and+e.name+'='+element_value;
				and="&";
			}
		}
		return query_string;
	}
}
