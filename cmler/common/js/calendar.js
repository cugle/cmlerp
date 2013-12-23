<!--
document.write("<div id=meizzCalendarLayer style='position: absolute; z-index: 9999; width: 144; height: 193; display: none'>");
document.write("<iframe name=meizzCalendarIframe scrolling=no frameborder=0 width=100% height=100%></iframe></div>");

/* 说明:原始作者为 '梅花雪疏影横斜'.
 * 该跨平台版本by 蜡笔小新,欢迎交流,可在IE,firefox,Opera,Netscape版本上运行
 * csmfzu@163.com
 */


// section 1 : 非常重要，判断浏览器类型，版本，及运行平台，以达到跨平台目的
var sUserAgent = navigator.userAgent;
var fAppVersion = parseFloat(navigator.appVersion);

var isOpera = sUserAgent.indexOf('Opera') > -1;
var isKHTML = sUserAgent.indexOf('KHTML') > -1
                || sUserAgent.indexOf('Konqueror') > -1
                || sUserAgent.indexOf('AppleWebKit') > -1;

if (isKHTML) {
        isSafari = sUserAgent.indexOf('AppleWebKit') > -1;
        isKonq = sUserAgent.indexOf('Konqueror') > -1;
}

var isIE = sUserAgent.indexOf('compatible') > -1
            && sUserAgent.indexOf('MSIE') > -1
            && !isOpera;
var isMinIE4 = isMinIE5 = isMinIE5_5 = isMinIE6 = false;
if (isIE) {

        var reIE = new RegExp('MSIE (\\d+\\.\\d+);');
        reIE.test(sUserAgent);
        var fIEVersion = parseFloat(RegExp['$1']);
        isMinIE4 = fIEVersion >= 4;
        isMinIE5 = fIEVersion >= 5;
        isMinIE5_5 = fIEVersion >= 5.5;
        isMinIE6 = fIEVersion >= 6.0;
}


var isMoz = sUserAgent.indexOf('Gecko') > -1
                && !isKHTML;
var isMinMoz1 = sMinMoz1_4 = isMinMoz1_5 = false;
if (isMoz) {
        var reMoz = new RegExp('rv:(\\d+\\.\\d+(?:\\.\\d+)?)');
        reMoz.test(sUserAgent);
        isMinMoz1 = compareVersions(RegExp['$1'], '1.0') >= 0;
        isMinMoz1_4 = compareVersions(RegExp['$1'], '1.4') >= 0;
        isMinMoz1_5 = compareVersions(RegExp['$1'], '1.5') >= 0;
}

if (isOpera) {
        var fOperaVersion;
        if(navigator.appName == 'Opera') {
        fOperaVersion = fAppVersion;
        } else {
        var reOperaVersion = new RegExp('Opera (\\d+\\.\\d+)');
        reOperaVersion.test(sUserAgent);
        fOperaVersion = parseFloat(RegExp['$1']);
        }
        isMinOpera4 = fOperaVersion >= 4;
        isMinOpera5 = fOperaVersion >= 5;
        isMinOpera6 = fOperaVersion >= 6;
        isMinOpera7 = fOperaVersion >= 7;
        isMinOpera7_5 = fOperaVersion >= 7.5;
}

var isNS4 = !isIE && !isOpera && !isMoz && !isKHTML
                && (sUserAgent.indexOf('Mozilla') == 0)
                && (navigator.appName == 'Netscape')
                && (fAppVersion >= 4.0 && fAppVersion < 5.0);
var isMinNS4 = isMinNS4_5 = isMinNS4_7 = isMinNS4_8 = false;
if (isNS4) {
        isMinNS4 = true;
        isMinNS4_5 = fAppVersion >= 4.5;
        isMinNS4_7 = fAppVersion >= 4.7;
        isMinNS4_8 = fAppVersion >= 4.8;
}

var isWin = (navigator.platform == 'Win32') || (navigator.platform == 'Windows');
var isMac = (navigator.platform == 'Mac68K') || (navigator.platform == 'MacPPC')
                || (navigator.platform == 'Macintosh');
var isUnix = (navigator.platform == 'X11') && !isWin && !isMac;
var isWin95 = isWin98 = isWinNT4 = isWin2K = isWinME = isWinXP = false;
if (isWin) {
        isWin95 = sUserAgent.indexOf("Win95") > -1
                || sUserAgent.indexOf("Windows 95") > -1;
        isWin98 = sUserAgent.indexOf("Win98") > -1
                || sUserAgent.indexOf("Windows 98") > -1;
        isWinME = sUserAgent.indexOf("Win 9x 4.90") > -1
                || sUserAgent.indexOf("Windows ME") > -1;
        isWin2K = sUserAgent.indexOf("Windows NT 5.0") > -1
                || sUserAgent.indexOf("Windows 2000") > -1;
        isWinXP = sUserAgent.indexOf("Windows NT 5.1") > -1
                || sUserAgent.indexOf("Windows XP") > -1;
        isWinNT4 = sUserAgent.indexOf("WinNT") > -1
                || sUserAgent.indexOf("Windows NT") > -1
                || sUserAgent.indexOf("WinNT4.0") > -1
                || sUserAgent.indexOf("Windows NT 4.0") > -1
                && (!isWinME && !isWin2K && !isWinXP);
}
var isMac68K = isMacPPC = false;
if (isMac) {
isMac68K = sUserAgent.indexOf("Mac_68000") > -1
            || sUserAgent.indexOf("68K") > -1;
            isMacPPC = sUserAgent.indexOf("Mac_PowerPC") > -1
            || sUserAgent.indexOf("PPC") > -1;
}
var isSunOS = isMinSunOS4 = isMinSunOS5 = isMinSunOS5_5 = false;
if (isUnix) {
        isSunOS = sUserAgent.indexOf("SunOS") > -1;
        if (isSunOS) {
        var reSunOS = new RegExp("SunOS (\\d+\\.\\d+(?:\\.\\d+)?)");
        reSunOS.test(sUserAgent);
        isMinSunOS4 = compareVersions(RegExp["$1"], "4.0") >= 0;
        isMinSunOS5 = compareVersions(RegExp["$1"], "5.0") >= 0;
        isMinSunOS5_5 = compareVersions(RegExp["$1"], "5.5") >= 0;
}
}


//用于比较版本的函数
function compareVersions(sVersion1, sVersion2) {
    var aVersion1 = sVersion1.split('.');
    var aVersion2 = sVersion2.split('.');
    if (aVersion1.length > aVersion2.length) {
    for (var i=0; i < aVersion1.length - aVersion2.length; i++) {
    aVersion2.push('0');
    }
    } else if (aVersion1.length < aVersion2.length) {
    for (var i=0; i < aVersion2.length - aVersion1.length; i++) {
    aVersion1.push('0');
    }
    }
    for (var i=0; i < aVersion1.length; i++) {
        if (aVersion1[i] < aVersion2[i]) {
        return -1;
        } else if (aVersion1[i] > aVersion2[i]) {
        return 1;
        }
        }
        return 0;
    
    
}

// end section 1

// section 2 : windows的event对象和DOM的event对象一些属性不相同，需要把
//  他们转为相同的格式
var EventUtil = new Object;
EventUtil.formatEvent = function (oEvent) {
    if (isIE && isWin) {
        oEvent.charCode = (oEvent.type == "keypress") ? oEvent.keyCode : 0;
        oEvent.eventPhase = 2;
        oEvent.isChar = (oEvent.charCode > 0);
        oEvent.pageX = oEvent.clientX + document.body.scrollLeft;
        oEvent.pageY = oEvent.clientY + document.body.scrollTop;
        oEvent.preventDefault = function () {
        this.returnValue = false;
        };
        if (oEvent.type == "mouseout") {
        oEvent.relatedTarget = oEvent.toElement;
        } else if (oEvent.type == "mouseover") {
        oEvent.relatedTarget = oEvent.fromElement;
        }
        oEvent.stopPropagation = function () {
        this.cancelBubble = true;
        };
        oEvent.target = oEvent.srcElement;
        oEvent.time = (new Date).getTime();
    }
    return oEvent;
}
//得到event
EventUtil.getEvent = function() {
    if (window.event) {
    return this.formatEvent(window.event);
} else {  
    return EventUtil.getEvent.caller.arguments[0];
    }
}

// end section 2


//初始化日历

var WebCalendar = new WebCalendar();
WebCalendar.yearFall   = 50;           //定义年下拉框的年差值
WebCalendar.format     = "1999-10-10"; //回传日期的格式
WebCalendar.timeShow   =  false; //是否返回时间
WebCalendar.drag       =  false;//是否允许拖动
WebCalendar.darkColor  = "#95B7F3";    //控件的暗色
WebCalendar.lightColor = "#FFFFFF";    //控件的亮色
WebCalendar.btnBgColor = "#E6E6FA";    //控件的按钮背景色
WebCalendar.wordColor  = "#000000";    //控件的文字颜色
WebCalendar.wordDark   = "#DCDCDC";    //控件的暗文字颜色
WebCalendar.dayBgColor = "#F5F5FA";    //日期数字背景色
WebCalendar.todayColor = "#FF0000";    //今天在日历上的标示背景色
WebCalendar.DarkBorder = "#D4D0C8";    //日期显示的立体表达色

function writeIframe()
{
    var strIframe = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=gb2312'><style>"+
    "*{font-size: 12px; font-family: 宋体}"+
    ".bg{  color: "+ WebCalendar.lightColor +"; cursor: default; background-color: "+ WebCalendar.darkColor +";}"+
    "table#tableMain{ width: 142; height: 180;}"+
    "table#tableWeek td{ color: "+ WebCalendar.lightColor +";}"+
    "table#tableDay  td{ font-weight: bold;}"+
    "td#meizzYearHead, td#meizzYearMonth{color: "+ WebCalendar.wordColor +"}"+
    ".out { text-align: center; border-top: 1px solid "+ WebCalendar.DarkBorder +"; border-left: 1px solid "+ WebCalendar.DarkBorder +";"+
    "border-right: 1px solid "+ WebCalendar.lightColor +"; border-bottom: 1px solid "+ WebCalendar.lightColor +";}"+
    ".over{ text-align: center; border-top: 1px solid #FFFFFF; border-left: 1px solid #FFFFFF;"+
    "border-bottom: 1px solid "+ WebCalendar.DarkBorder +"; border-right: 1px solid "+ WebCalendar.DarkBorder +"}"+
    "input{ border: 1px solid "+ WebCalendar.darkColor +";  padding:1px;  height: 18; cursor: hand;"+
    "       color:"+ WebCalendar.wordColor +"; background-color: "+ WebCalendar.btnBgColor +"}"+
    "</style></head><body onselectstart='return false' style='margin: 0px' oncontextmenu='return false'><form name='meizz' >";
//修改过：不允许拖动
//    if (WebCalendar.drag)
//    {
//         strIframe += "<scr"+"ipt language=javascript>"+
//                "var drag=false, cx=0, cy=0, o = parent.WebCalendar.calendar; "+
//                " document.onmousemove = function(){ "+
//                " if(parent.WebCalendar.drag && drag){if(o.style.left=='')o.style.left=0; if(o.style.top=='')o.style.top=0;"+
//                " o.style.left = parseInt(o.style.left) + window.event.clientX-cx;"+
//                " o.style.top  = parseInt(o.style.top)  + window.event.clientY-cy;}}"+
//                " document.onkeydown = function(){ switch(window.event.keyCode){  case 27 : parent.hiddenCalendar(); break;"+
//                " case 37 : parent.prevM(); break; case 38 : parent.prevY(); break; case 39 : parent.nextM(); break; case 40 : parent.nextY(); break;"+
//                " case 84 : document.forms[0].today.click(); break;} window.event.keyCode = 0; window.event.returnValue= false;}"+
//                " dragStart = function(){cx=window.event.clientX; cy=window.event.clientY; drag=true;}</scr"+"ipt>"
//    }

    strIframe += "<select name=tmpYearSelect  onblur='parent.hiddenSelect(this)' style='z-index:1;position:absolute;top:3;left:18;display:none'"+
    " onchange='parent.WebCalendar.thisYear =this.value; parent.hiddenSelect(this); parent.writeCalendar();'></select>"+
    "<select name=tmpMonthSelect onblur='parent.hiddenSelect(this)' style='z-index:1; position:absolute;top:3;left:74;display:none'"+
    " onchange='parent.WebCalendar.thisMonth=this.value; parent.hiddenSelect(this); parent.writeCalendar();'></select>"+

    "<table id=tableMain class=bg border=0 cellspacing=2 cellpadding=0>"+
    "<tr><td width=140 height=19 bgcolor='"+ WebCalendar.lightColor +"'>"+
    "    <table width=140 id=tableHead border=0 cellspacing=1 cellpadding=0><tr align=center>"+
    "    <td width=15 height=19 class=bg title='向前翻 1 月&#13;快捷键：←' style='cursor: hand' onclick='parent.prevM()'><b>&lt;</b></td>"+
    "    <td width=60 id='meizzYearHead'  title='点击此处选择年份' onclick='parent.funYearSelect(parseInt(this.innerHTML, 10))'"+
    "        onmouseover='this.bgColor=parent.WebCalendar.darkColor; this.style.color=parent.WebCalendar.lightColor'"+
    "        onmouseout='this.bgColor=parent.WebCalendar.lightColor; this.style.color=parent.WebCalendar.wordColor'></td>"+
    "    <td width=50 id=meizzYearMonth title='点击此处选择月份' onclick='parent.funMonthSelect(parseInt(this.innerHTML, 10))'"+
    "        onmouseover='this.bgColor=parent.WebCalendar.darkColor; this.style.color=parent.WebCalendar.lightColor'"+
    "        onmouseout='this.bgColor=parent.WebCalendar.lightColor; this.style.color=parent.WebCalendar.wordColor'></td>"+
    "    <td width=15 class=bg title='向后翻 1 月&#13;快捷键：→' onclick='parent.nextM()' style='cursor: hand'><b>&gt;</b></td></tr></table>"+
    "</td></tr><tr><td height=20><table id=tableWeek border=1 width=140 cellpadding=0 cellspacing=0 ";
   // if(WebCalendar.drag){strIframe += "onmousedown='dragStart()' onmouseup='drag=false' onmouseout='drag=false'";}
    strIframe += " borderColorLight='"+ WebCalendar.darkColor +"' borderColorDark='"+ WebCalendar.lightColor +"'>"+
    "    <tr align=center><td height=20>日</td><td>一</td><td>二</td><td>三</td><td>四</td><td>五</td><td>六</td></tr></table>"+
    "</td></tr><tr><td valign=top width=140 bgcolor='"+ WebCalendar.lightColor +"'>"+
    "    <table id=tableDay height=120 width=140 border=0 cellspacing=1 cellpadding=0>";
         for(var x=0; x<5; x++){ strIframe += "<tr>";
         for(var y=0; y<7; y++)  strIframe += "<td class=out id='meizzDay"+ (x*7+y) +"'></td>"; strIframe += "</tr>";}
         strIframe += "<tr>";
         for(var x=35; x<39; x++) strIframe += "<td class=out id='meizzDay"+ x +"'></td>";
         strIframe +="<td colspan=3 class=out title='"+ WebCalendar.regInfo +"'>"+
         
         "<input style=' background-color: "+
         WebCalendar.btnBgColor +";cursor: hand;   border: 0' onfocus='this.blur()'"+
         " type=button value='关闭' onclick='parent.hiddenCalendar()'></td></tr></table>"+
    "</td></tr><tr><td height=20 width=140 bgcolor='"+ WebCalendar.lightColor +"'>"+
    "    <table border=0 cellpadding=1 cellspacing=0 width=140>"+
    "    <tr><td><input  width=30 name=prevYear title='向前翻 1 年&#13;快捷键：↑' onclick='parent.prevY()' type=button value='&lt;&lt;'"+
    "    onfocus='this.blur()'  ><input width=20 "+
    "    onfocus='this.blur()' name=prevMonth title='向前翻 1 月&#13;快捷键：←' onclick='parent.prevM()' type=button value='&lt;&nbsp;'>"+
    "    </td><td align=center><input   name=today type=button value='今天' onfocus='this.blur()' style='width: 40' title='当前日期&#13;快捷键：T'"+
    "    onclick=\"parent.returnDate(new Date().getDate() +'/'+ (new Date().getMonth() +1) +'/'+ new Date().getFullYear())\">"+
    "    </td><td align=right><input width=20  title='向后翻 1 月&#13;快捷键：→' name=nextMonth onclick='parent.nextM()' type=button value='&nbsp;&gt;'"+
    "    onfocus='this.blur()'><input width=30  name=nextYear title='向后翻 1 年&#13;快捷键：↓' onclick='parent.nextY()' type=button value='&gt;&gt;'"+
    //style='meizz:expression(this.disabled=parent.WebCalendar.thisYear==9999)' originally located in line 93,IE only
    "    onfocus='this.blur()' ></td></tr></table>"+
    "</td></tr><table></form></body></html>";
    with(WebCalendar.iframe)
    {
        document.writeln(strIframe); 
        document.close();
        for(var i=0; i<39; i++)
        {
            WebCalendar.dayObj[i] = document.getElementById("meizzDay"+ i);
            WebCalendar.dayObj[i].onmouseover = dayMouseOver;
            WebCalendar.dayObj[i].onmouseout  = dayMouseOut;
            WebCalendar.dayObj[i].onclick     = returnDate;
        }  
    }
}
function WebCalendar() //初始化日历的设置
{
//  by 蜡笔小新(csmfzu@163.com)，原始版本by  walkingpoison(水晶龙) 及 (梅花雪疏影横斜)";
    this.regInfo    = "Browser Independent Calendar ver 1.0";
    this.daysMonth  = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    this.day        = new Array(39);            //定义日历展示用的数组
    this.dayObj     = new Array(39);            //定义日期展示控件数组
    this.dateStyle  = null;                     //保存格式化后日期数组
    this.objExport  = null;                     //日历回传的显示控件
    this.eventSrc   = null;                     //日历显示的触发控件
    this.inputDate  = null;                     //转化外的输入的日期(d/m/yyyy)
    this.thisYear   = new Date().getFullYear(); //定义年的变量的初始值
    this.thisMonth  = new Date().getMonth()+ 1; //定义月的变量的初始值
    this.thisDay    = new Date().getDate();     //定义日的变量的初始值
    this.today      = this.thisDay +"/"+ this.thisMonth +"/"+ this.thisYear;   //今天(d/m/yyyy)
    this.iframe     = window.frames["meizzCalendarIframe"]; //日历的 iframe 载体
    this.calendar   = getObjectById("meizzCalendarLayer");  //日历的层
    this.dateReg    = "";           //日历格式验证的正则式

    this.yearFall   = 50;           //定义年下拉框的年差值
    this.format     = "yyyy-mm-dd"; //回传日期的格式
    this.timeShow   = false;        //是否返回时间
    this.drag       = true;         //是否允许拖动
    this.darkColor  = "#0000D0";    //控件的暗色
    this.lightColor = "#FFFFFF";    //控件的亮色
    this.btnBgColor = "#E6E6FA";    //控件的按钮背景色
    this.wordColor  = "#000080";    //控件的文字颜色
    this.wordDark   = "#DCDCDC";    //控件的暗文字颜色
    this.dayBgColor = "#F5F5FA";    //日期数字背景色
    this.todayColor = "#FF0000";    //今天在日历上的标示背景色
    this.DarkBorder = "#D4D0C8";    //日期显示的立体表达色
}


//传入event对象
function calendar( e  ) //主调函数
{    
    writeIframe();
    var o = WebCalendar.calendar.style; WebCalendar.eventSrc = e;
    //if (arguments.length == 0) WebCalendar.objExport = e;
   // else WebCalendar.objExport = eval(arguments[0]);
    eval('WebCalendar.objExport =e');
    var tab=document.getElementById('tableWeek');
    //WebCalendar.iframe.tableWeek
    //tab.style.cursor ="hand";// WebCalendar.drag ? "move" : "default";
    var t = e.offsetTop,  h = e.clientHeight, l = e.offsetLeft, p = e.type;
    while (e = e.offsetParent){t += e.offsetTop; l += e.offsetLeft;}
    o.display = ""; 
    WebCalendar.iframe.document.body.focus();
    var cw = WebCalendar.calendar.clientWidth, ch = WebCalendar.calendar.clientHeight;
    var dw = document.body.clientWidth, dl = document.body.scrollLeft, dt = document.body.scrollTop;

    if (document.body.clientHeight + dt - t - h >= ch) o.top = (p=="image")? t + h : t + h + 6;
    else o.top  = (t - dt < ch) ? ((p=="image")? t + h : t + h + 6) : t - ch;
    if (dw + dl - l >= cw) o.left = l; else o.left = (dw >= cw) ? dw - cw + dl : dl;

    if  (!WebCalendar.timeShow) WebCalendar.dateReg = /^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2})$/;
    else WebCalendar.dateReg = /^(\d{1,4})(-|\/)(\d{1,2})\2(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/;

    try{
        if (WebCalendar.objExport.value.trim() != ""){
            WebCalendar.dateStyle = WebCalendar.objExport.value.trim().match(WebCalendar.dateReg);
            if (WebCalendar.dateStyle == null)
            {
                WebCalendar.thisYear   = new Date().getFullYear();
                WebCalendar.thisMonth  = new Date().getMonth()+ 1;
                WebCalendar.thisDay    = new Date().getDate();
                //alert("原文本框里的日期有错误！\n可能与你定义的显示时分秒有冲突！");
                writeCalendar(); return false;
            }
            else
            {
                WebCalendar.thisYear   = parseInt(WebCalendar.dateStyle[1], 10);
                WebCalendar.thisMonth  = parseInt(WebCalendar.dateStyle[3], 10);
                WebCalendar.thisDay    = parseInt(WebCalendar.dateStyle[4], 10);
                WebCalendar.inputDate  = parseInt(WebCalendar.thisDay, 10) +"/"+ parseInt(WebCalendar.thisMonth, 10) +"/"+
                parseInt(WebCalendar.thisYear, 10); writeCalendar();
            }
        }  else writeCalendar();
    }  catch(e){writeCalendar();}
}
function funMonthSelect() //月份的下拉框
{
    var m = isNaN(parseInt(WebCalendar.thisMonth, 10)) ? new Date().getMonth() + 1 : parseInt(WebCalendar.thisMonth);
    var e = WebCalendar.iframe.document.forms[0].tmpMonthSelect;
    for (var i=1; i<13; i++) e.options.add(new Option(i +"月", i));
    e.style.display = ""; e.value = m; e.focus(); window.status = e.style.top;
}
function funYearSelect() //年份的下拉框
{
    var n = WebCalendar.yearFall;
    var e = WebCalendar.iframe.document.forms[0].tmpYearSelect;
    var y = isNaN(parseInt(WebCalendar.thisYear, 10)) ? new Date().getFullYear() : parseInt(WebCalendar.thisYear);
        y = (y <= 1000)? 1000 : ((y >= 9999)? 9999 : y);
    var min = (y - n >= 1000) ? y - n : 1000;
    var max = (y + n <= 9999) ? y + n : 9999;
        min = (max == 9999) ? max-n*2 : min;
        max = (min == 1000) ? min+n*2 : max;
    for (var i=min; i<=max; i++) e.options.add(new Option(i +"年", i));
    e.style.display = ""; e.value = y; e.focus();
}
function prevM()  //往前翻月份
{
    WebCalendar.thisDay = 1;
    if (WebCalendar.thisMonth==1)
    {
        WebCalendar.thisYear--;
        WebCalendar.thisMonth=13;
    }
    WebCalendar.thisMonth--; writeCalendar();
}
function nextM()  //往后翻月份
{
    WebCalendar.thisDay = 1;
    if (WebCalendar.thisMonth==12)
    {
        WebCalendar.thisYear++;
        WebCalendar.thisMonth=0;
    }
    WebCalendar.thisMonth++; writeCalendar();
}
function prevY()
{
    WebCalendar.thisDay = 1; 
    WebCalendar.thisYear--; 
    writeCalendar();
} 
function nextY()
{
    WebCalendar.thisDay = 1; 
    WebCalendar.thisYear++; 
    writeCalendar();
} 
function hiddenSelect(e)
{ 
    //不要用原来的e.options.remove(i)方法，那是ie特有的
    for(var i=e.options.length; i>-1; i--)
        e.options[i]= null ; 
    e.style.display="none";
}
function getObjectById(id)
{
    return document.getElementById(id);
}
function hiddenCalendar()
{
    getObjectById("meizzCalendarLayer").style.display = "none";
}
function appendZero(n)
{
    return(("00"+ n).substr(("00"+ n).length-2));
}
//不要写成 function String.prototype.trim() 这种格式,firefox,opera等浏览器不支持
String.prototype.trim = function()
{
    return this.valueOf();
    //return this.replace(/(^\s*)|(\s*$)/g,"");
}
function dayMouseOver()
{
    
    this.className = "over";
    var d = WebCalendar.day[this.id.substr(8)], a = d.split("/");
    if(d == WebCalendar.today  || d == WebCalendar.inputDate)
        return true;
    this.style.backgroundColor = WebCalendar.darkColor;
    if(a[1] == WebCalendar.thisMonth)
        this.style.color = WebCalendar.lightColor;
}
function dayMouseOut()
{
    this.className = "out"; 
    var d = WebCalendar.day[this.id.substr(8)], a = d.split("/");
    if(d == WebCalendar.today  || d == WebCalendar.inputDate)
        return true;
    
    this.style.backgroundColor =WebCalendar.lightColor;
    if(a[1] == WebCalendar.thisMonth && d != WebCalendar.today)
    {
        if(WebCalendar.dateStyle && a[0] == parseInt(WebCalendar.dateStyle[4], 10))
        this.style.color = WebCalendar.lightColor;
        this.style.color = WebCalendar.wordColor; 
    }

}
function writeCalendar() //对日历显示的数据的处理程序
{
    var y = WebCalendar.thisYear;
    var m = WebCalendar.thisMonth;
    var d = WebCalendar.thisDay;
    WebCalendar.daysMonth[1] = (0==y%4 && (y%100!=0 || y%400==0)) ? 29 : 28;
    if (!(y<=9999 && y >= 1000 && parseInt(m, 10)>0 && parseInt(m, 10)<13 && parseInt(d, 10)>0)){
        alert("对不起，你输入了错误的日期！");
        WebCalendar.thisYear   = new Date().getFullYear();
        
        WebCalendar.thisMonth  = new Date().getMonth()+ 1;
        WebCalendar.thisDay    = new Date().getDate(); }
    y = WebCalendar.thisYear;
    m = WebCalendar.thisMonth;
    d = WebCalendar.thisDay;
    
    //修改过：下面这行代码不要写成WebCalendar.iframe.meizzYearHead.innerHTML  = y +" 年",有些浏览器会报错
    WebCalendar.iframe.document.getElementById('meizzYearHead').innerHTML=y +" 年";
    //修改过：下面这行代码不要写成WebCalendar.iframe.meizzYearMonth.innerHTML = parseInt(m, 10) +" 月",有些浏览器会报错
    WebCalendar.iframe.document.getElementById('meizzYearMonth').innerHTML=parseInt(m, 10) +" 月"  ;

    WebCalendar.daysMonth[1] = (0==y%4 && (y%100!=0 || y%400==0)) ? 29 : 28; //闰年二月为29天
    var w = new Date(y, m-1, 1).getDay();
    var prevDays = m==1  ? WebCalendar.daysMonth[11] : WebCalendar.daysMonth[m-2];
    for(var i=(w-1); i>=0; i--) //这三个 for 循环为日历赋数据源（数组 WebCalendar.day）格式是 d/m/yyyy
    {
        WebCalendar.day[i] = prevDays +"/"+ (parseInt(m, 10)-1) +"/"+ y;
        if(m==1) WebCalendar.day[i] = prevDays +"/"+ 12 +"/"+ (parseInt(y, 10)-1);
        prevDays--;
    }
    for(var i=1; i<=WebCalendar.daysMonth[m-1]; i++) WebCalendar.day[i+w-1] = i +"/"+ m +"/"+ y;
    for(var i=1; i<39-w-WebCalendar.daysMonth[m-1]+1; i++)
    {
        WebCalendar.day[WebCalendar.daysMonth[m-1]+w-1+i] = i +"/"+ (parseInt(m, 10)+1) +"/"+ y;
        if(m==12) WebCalendar.day[WebCalendar.daysMonth[m-1]+w-1+i] = i +"/"+ 1 +"/"+ (parseInt(y, 10)+1);
    }
    for(var i=0; i<39; i++)    //这个循环是根据源数组写到日历里显示
    {
        var a = WebCalendar.day[i].split("/");
        //修改过：不要使用innerText，这是ie specific，使用innerHTML
        WebCalendar.dayObj[i].innerHTML    = a[0];
        WebCalendar.dayObj[i].title        = a[2] +"-"+ appendZero(a[1]) +"-"+ appendZero(a[0]);
        WebCalendar.dayObj[i].bgColor      = WebCalendar.dayBgColor;
        WebCalendar.dayObj[i].style.color  = WebCalendar.wordColor;
        if ((i<10 && parseInt(WebCalendar.day[i], 10)>20) || (i>27 && parseInt(WebCalendar.day[i], 10)<12))
            WebCalendar.dayObj[i].style.color = WebCalendar.wordDark;
        if (WebCalendar.inputDate==WebCalendar.day[i])    //设置输入框里的日期在日历上的颜色
        {WebCalendar.dayObj[i].bgColor = WebCalendar.darkColor; WebCalendar.dayObj[i].style.color = WebCalendar.lightColor;}
        if (WebCalendar.day[i] == WebCalendar.today)      //设置今天在日历上反应出来的颜色
        {WebCalendar.dayObj[i].bgColor = WebCalendar.todayColor; WebCalendar.dayObj[i].style.color = WebCalendar.lightColor;}
    }
}
function returnDate() //根据日期格式等返回用户选定的日期
{
    
    if(WebCalendar.objExport)
    {
        var returnValue;
        if( isIE) //如果是ie
        {
            var a = (arguments.length==0) ? WebCalendar.day[this.id.substr(8)].split("/") : arguments[0].split("/");
        }
        else  //如果是 firefox,netscape,safari,opera
        {
            if(typeof arguments[0] == 'string')
            {  
                var a =   arguments[0].split("/")
             }
             else if (typeof arguments[0] == 'object')
             {
                 var a =  WebCalendar.day[this.id.substr(8)].split("/")   ;
             }
        }
        var d = WebCalendar.format.match(/^(\w{4})(-|\/)(\w{1,2})\2(\w{1,2})$/);
        if(d==null){alert("你设定的日期输出格式不对！\r\n\r\n请重新定义 WebCalendar.format ！"); return false;}
        var flag = d[3].length==2 || d[4].length==2; //判断返回的日期格式是否要补零
        returnValue = flag ? a[2] +d[2]+ appendZero(a[1]) +d[2]+ appendZero(a[0]) : a[2] +d[2]+ a[1] +d[2]+ a[0];
        if(WebCalendar.timeShow)
        {
            var h = new Date().getHours(), m = new Date().getMinutes(), s = new Date().getSeconds();
            returnValue += flag ? " "+ appendZero(h) +":"+ appendZero(m) +":"+ appendZero(s) : " "+  h  +":"+ m +":"+ s;
        }
        //if( arguments[0] == "" ) returnValue = "" ;
        WebCalendar.objExport.value = returnValue;
        hiddenCalendar();
    }
}