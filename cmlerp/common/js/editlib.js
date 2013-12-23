function KeyFilter(type)
{
	var berr=false;
	
	switch(type)
	{
		case 'date':
			if (!(event.keyCode == 45 || event.keyCode == 47 || (event.keyCode>=48 && event.keyCode<=57)))
				berr=true;
			break;
		case 'number':
			if (!(event.keyCode>=48 && event.keyCode<=57))
				berr=true;
			break;
		case 'cy':
			if (!(event.keyCode == 46 || (event.keyCode>=48 && event.keyCode<=57)))
				berr=true;
			break;
		case 'long':
			if (!(event.keyCode == 45 || (event.keyCode>=48 && event.keyCode<=57)))
				berr=true;
			break;
		case 'double':
			if (!(event.keyCode == 45 || event.keyCode == 46 || (event.keyCode>=48 && event.keyCode<=57)))
				berr=true;
			break;
		default:
			if (event.keyCode == 35 || event.keyCode == 37 || event.keyCode==38)
				berr=true;
	}
	return !berr;
}

function getParentFromSrc(src,parTag)
{
	if(src && src.tagName!=parTag)
		src=getParentFromSrc(src.parentElement,parTag);
	return src;
}

function switchToOption(sel,newOption,byWhat)
{
	newOption=newOption.toString();
	if(newOption && sel && sel.tagName=="SELECT")
	{
		newOption=trim(newOption);
		var opts=sel.options;
		for(var i=0;i<opts.length;i++)
		{
			if(trim(opts[i][byWhat].toString())==newOption)
			{
				sel.selectedIndex=i;
				break;
			}
		}
	}
}

// Is a element visible?
function isElementVisible(src)
{
	if(src)
	{
		var x=getOffsetLeft(src)+2-document.body.scrollLeft;
		var y=getOffsetTop(src)+2-document.body.scrollTop;
		if(ptIsInRect(x,y,0,0,document.body.offsetWidth,document.body.offsetHeight))
		{
			var e=document.elementFromPoint(x,y);
			return src==e;
		}
	}
			
	return false;
}

function ptIsInRect(x,y,left,top,right,bottom)
{
	return (x>=left && x<right) && (y>=top && y<bottom);
}

function getOffsetLeft(src){
	var set=0;
	if(src)
	{
		if (src.offsetParent)
			set+=src.offsetLeft+getOffsetLeft(src.offsetParent);
		
		if(src.tagName!="BODY")
		{
			var x=parseInt(src.scrollLeft,10);
			if(!isNaN(x))
				set-=x;
		}
	}
	return set;
}
function getOffsetTop(src){
	var set=0;
	if(src)
	{
		if (src.offsetParent)
			set+=src.offsetTop+getOffsetTop(src.offsetParent);
		
		if(src.tagName!="BODY")
		{
			var y=parseInt(src.scrollTop,10);
			if(!isNaN(y))
				set-=y;
		}
	}
	return set;
}

function isAnyLevelParent(src,par)
{
	var hr=false;
	if(src==par)
		hr=true;
	else if(src!=null)
		hr=isAnyLevelParent(src.parentElement,par);
	
	return hr;
}

function isIE(version)
{
	var i0=navigator.appVersion.indexOf("MSIE")
	var i1=-1;
	var ver=0;
	if(i0>=0)
	{
		i1=navigator.appVersion.indexOf(" ",i0+1);
		if(i1>=0)
		{
			i0=i1;
			i1=navigator.appVersion.indexOf(";",i0+1);
			if(i1>=0)
			{
				ver=parseFloat(navigator.appVersion.substring(i0+1,i1));
				if(isNaN(ver))
					ver=0;
			}
		}
	}
	
	return (navigator.userAgent.indexOf("MSIE")!= -1 
		&& navigator.userAgent.indexOf("Windows")!=-1 
		&& ((ver<(version+1) && ver>=version) || version==0));
}

function getValidDate(str)
{
	var sDate=str.replace(/\//g,"-");
	var vArr=sDate.split("-");
	var sRet="";
	
	if(vArr.length>=3)
	{
		var year=parseInt(vArr[0],10);
		var month=parseInt(vArr[1],10);
		var day=parseInt(vArr[2],10);
		if(!(isNaN(year) || isNaN(month) || isNaN(day)))
			if(year>=1900 && year<9999 && month>=1 && month<=12)
			{
				var dt=new Date(year,month-1,day);
				year=dt.getFullYear();
				month=dt.getMonth()+1;
				day=dt.getDate();
				sRet=year+"-"+(month<10?"0":"")+month+"-"+(day<10?"0":"")+day;
			}
	}
	
	return sRet;
}

function getSafeValue(val,def)
{
	if(typeof(val)=='undefined' || val==null)
		return def;
	else
		return val;
}
document.onclick = function(){
 if(event.srcElement.name != "save32" && event.srcElement.id != "helpid"){
    HhS();
 }
}
function autoResizeIframe()
{document.all.acc.style.height=window.acc.document.body.scrollHeight;}

function submit_reset(){
	form1.reset();
}

function  loadingok(){

if(typeof(parent.parent.frames[0].form1.loadingok.value)=='undefined' ){
	parent.parent.frames[0].form1.loadingok.value  = "";
	parent.parent.frames[1].loadingbg.style.display ="none";
}

parent.parent.frames[1].loadingbg.style.display ="none";
parent.parent.frames[0].form1.loadingok.value  = "";

}
document.onload = loadingok();

function nextedit(){
form1.action.value="nextedit";
 form1.submit(); 
}

function prvedit(){
form1.action.value="prvedit";
 form1.submit(); 
}

function formhead(){
window.location.href= "#";
}
function lasthead(){
window.location.href= "#inputpro";
}

function MoveLayer(AdLayer) {
var x = 10;//浮动广告层固定于浏览器的x方向位置
var y = 300;//浮动广告层固定于浏览器的y方向位置
var diff = (document.body.scrollTop + y - document.all.AdLayer.style.posTop)*.40;
var y = document.body.scrollTop + y - diff;
eval("document.all." + AdLayer + ".style.posTop = y");
eval("document.all." + AdLayer + ".style.posLeft = x");//移动广告层
setTimeout("MoveLayer('AdLayer');", 20);//设置20毫秒后再调用函数MoveLayer()
}
function document.oncontextmenu() 
{
	
if(document.selection.type == "None" && document.activeElement.tagName != "INPUT" && document.activeElement.tagName!= "TEXTAREA"){
  return false; 
}

} 