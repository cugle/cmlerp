var m_iDatePickerCount=0;
var cl_dpMaxYear=9999;
var cl_dpMaxMonth=11;
var cl_dpMaxDay=31;
var cl_dpMinYear=1600;
var cl_dpMinMonth=0;
var cl_dpMinDay=1;

function createDatePicker(txtName,lYear,lMonth,lDay,flag)
{
	var dpID="dp_"+(m_iDatePickerCount++);
	var dt=dp_getValidDate(lYear,lMonth,lDay);
	if(dt==null)
		dt=new Date();
        if(flag==0)    //flag=0就日期就不可以修改,其它就可以修改
	{
           document.write("<span id="+dpID+">");
           document.write("<font class=DPYearDes>&nbsp;"+dt.getFullYear()+"</font>");
	   document.write("<font class=DPYearDes>&nbsp;年&nbsp;</font>");
	   document.write("<font class=DPMonthDes>"+(dt.getMonth()+1)+"</font>");
	   document.write("<font class=DPMonthDes>&nbsp;月&nbsp;</font>");
	   document.write("<font class=DPDayDes>"+dt.getDate()+"</font>");
	   document.write("<font class=DPDayDes>&nbsp;日</font>");
	   document.write("<span class=DPSep></span>");
        }else{          
	   document.write("<span class=DPFrame id="+dpID+">");
	   document.write("<input class=DPYear type=text value="+dt.getFullYear()+" size=4 maxlength=4 onfocus=\"return dp_focus('year');\" onblur=\"return dp_blur('year');\" onkeypress=\"return KeyFilter('number');\" onkeydown=\"return dp_keyDown('year');\">");
	   document.write("<font class=DPYearDes>年</font>");
	   document.write("<input class=DPMonth type=text value="+(dt.getMonth()+1)+" size=2 maxlength=2 onfocus=\"return dp_focus('month');\" onblur=\"return dp_blur('month');\" onkeypress=\"return KeyFilter('number');\" onkeydown=\"return dp_keyDown('month');\">");
	   document.write("<font class=DPMonthDes>月</font>");
	   document.write("<input class=DPDay type=text value="+dt.getDate()+" size=2 maxlength=2 onfocus=\"return dp_focus('day');\" onblur=\"return dp_blur('day');\" onkeypress=\"return KeyFilter('number');\" onkeydown=\"return dp_keyDown('day');\">");
	   document.write("<font class=DPDayDes>日</font>");
	   document.write("<span class=DPSep></span>");
	   document.write("<a class=DPDropBtn href=\"\" onclick=\"dp_DropClick();return false;\" title=\"选择日期\">▼</a>");
        }
	if(typeof(txtName)=="string" && txtName.length>0)
	{
		document.write("<input type=hidden value='"+dt.format("yyyy/mm/dd")+"' name="+txtName+">");
	}
	document.write("</span>");

	var dp=document.all(dpID);
	dp_initDatePicker(dp,dt);
										
	return dp;
}

function dp_getValidDate(lYear,lMonth,lDay)
{
	var dt=new Date();
	if(lYear==null || isNaN(parseInt(lYear,10)))
		lYear=dt.getFullYear();
	else
		lYear=parseInt(lYear,10);

	if(lMonth==null || isNaN(parseInt(lMonth,10)))
		lMonth=dt.getMonth();
	else
		lMonth=parseInt(lMonth,10)-1;

	if(lDay==null || isNaN(parseInt(lDay,10)))
		lDay=dt.getDate();
	else
		lDay=parseInt(lDay,10);
	
	dt=new Date(lYear,lMonth,lDay);
	var cdMax=new Date(cl_dpMaxYear,cl_dpMaxMonth,cl_dpMaxDay);
	var cdMin=new Date(cl_dpMinYear,cl_dpMinMonth,cl_dpMinDay);
	if(dt.compare(cdMax)>0 || dt.compare(cdMin)<0)
		dt=null;
	return dt;
}

function dp_initDatePicker(dp,dt)
{
	if(dp)
	{
		//Private Property
		dp.curDate=dt;
		dp.dpEnabled=true;
		dp.maxDay=cl_dpMaxDay;
		dp.maxMonth=cl_dpMaxMonth;
		dp.maxYear=cl_dpMaxYear;
		dp.minDay=cl_dpMinDay;
		dp.minMonth=cl_dpMinMonth;
		dp.minYear=cl_dpMinYear;
		dp.oldDate=dt.clone();

		//Private Method
		dp.getDropDownTable=dp_getDropDownTable;
		dp.getMonthName=dp_getMonthName;
		dp.hideDropDown=dp_hideDropDown;
		dp.initDropDown=dp_initDropDown;
		dp.onDateChange=dp_onDateChange;
		dp.refreshPostText=dp_refreshPostText;
		dp.showDropDown=dp_showDropDown;
		
		//Public Property
		//All Span Properties can be used;
		dp.offsetHor=0;
		
		//Public Method
		dp.setFocus=dp_setFocus;
		dp.format=dp_format;
		dp.getDateContent=dp_getDateContent;
		dp.getDay=dp_getDay;
		dp.getEnabled=dp_getEnabled;
		dp.getMonth=dp_getMonth;
		dp.getYear=dp_getYear;
		dp.refreshView=dp_refreshView;
		dp.setAccessKey=dp_setAccessKey;
		dp.setCurDate=dp_setCurDate;
		dp.setDateDes=dp_setDateDes;
		dp.setEnabled=dp_setEnabled;
		dp.setFormat=dp_setFormat;
		dp.setMaxDate=dp_setMaxDate;
		dp.setMinDate=dp_setMinDate;
		dp.setTabIndex=dp_setTabIndex;
		dp.setWeekName=dp_setWeekName;
		
		//Event
		dp.dateChanged=null;
		
		//Init View
		dp.refreshView();
	}
}

function dp_createDropDown()
{
	var ddt=getDropDownTable();
	if(ddt)
		return ddt;
	document.body.insertAdjacentHTML("BeforeEnd",
					"<TABLE id=dpDropDownTable CELLSPACING=0 "+
					"onclick=\"dp_ddt_click();\" "+
					"ondblclick=\"dp_ddt_dblclick();\">"+
					"<TR class=DPTitle>"+
					"<TD class=white_word><span onclick=\"dp_yearChange(-1);\" style=\"cursor:hand\" title=\"上一年\"><<</span>"+
					"&nbsp;<span  onclick=\"dp_monthChange(-1);\" style=\"cursor:hand\" title=\"上一月\"><</span></TD>"+
					"<TD align=center colspan=5 class=white_word></TD>"+
					"<TD class=white_word><span onclick=\"dp_monthChange(1);\" style=\"cursor:hand\" title=\"下一月\">></span>"+
					"&nbsp;<span onclick=\"dp_yearChange(1);\" style=\"cursor:hand\" title=\"下一年\">>></span></TD>"+
					"</TR>"+
					"<TR>"+
					"<TD class=DPWeekName>星期日</TD>"+
					"<TD class=DPWeekName>星期一</TD>"+
					"<TD class=DPWeekName>星期二</TD>"+
					"<TD class=DPWeekName>星期三</TD>"+
					"<TD class=DPWeekName>星期四</TD>"+
					"<TD class=DPWeekName>星期五</TD>"+
					"<TD class=DPWeekName>星期六</TD>"+
					"</TR>"+
					"<iframe src=\"javascript:false\" style=\"position:absolute;z-index:-1; width:250px ;height:140px\"></iframe></TABLE>");
	ddt=getDropDownTable();
	if(ddt)
	{
		var row=null;
		var cell=null;
		for(var i=2; i<8; i++)
		{
			row=ddt.insertRow(i);
			if(row)
			{
				for(var j=0; j<7; j++)
				{
					cell=row.insertCell(j);
//					if(cell)
//					{
//					}
				}
			}
		}
	}
	if(ddt.rows.length!=8)
		ddt=null;
	return ddt;
}

function dp_getYear()
{
	var dp=this;
	return dp.curDate.getFullYear();
}

function dp_getMonth()
{
	var dp=this;
	return dp.curDate.getMonth()+1;
}

function dp_getDay()
{
	var dp=this;
	return dp.curDate.getDate();
}

function dp_format(sFormat)
{
	var dp=this;
	return dp.curDate.format(sFormat);
}

function dp_setAccessKey(sKey)
{
	var dp=this;
	var src=dp.children[0];
	if(src && src.tagName=="INPUT")
	{
		src.accessKey=sKey;
	}
}

function dp_getEnabled()
{
	var dp=this;
	var val=false;
	
	if(dp.dpEnabled)
		val=true;
	else
		val=false;
	return val;
}

function dp_setEnabled(val)
{
	var dp=this;
	var hr=false;
	
	var src=dp.children[0];
	if(src && src.tagName=="INPUT")
	{
		src.disabled=!val;
		src=dp.children[2];
		if(src && src.tagName=="INPUT")
		{
			src.disabled=!val;
			src=dp.children[4];
			if(src && src.tagName=="INPUT")
			{
				src.disabled=!val;
				dp.dpEnabled=val;
				hr=true;
			}
		}
	}
	return hr;
}

function dp_setFocus()
{
	var dp=this;
	var src=dp.children[0];
	if(src && src.tagName=="INPUT" && !src.disabled)
	{
		src.focus();
	}
}

function dp_getDateContent()
{
	var dp=this;
	var con="";
	var sYearDes="";
	var sMonthDes="";
	var sDayDes="";
	var src=dp.children[1];
	
	if(src && src.tagName=="FONT")
	{
		sYearDes=src.innerText;
		src=dp.children[3];
		if(src && src.tagName=="FONT")
		{
			sMonthDes=src.innerText;
			src=dp.children[5];
			if(src && src.tagName=="FONT")
			{
				sDayDes=src.innerText;
				var dt=dp.curDate;
				con=dt.getFullYear()+sYearDes+(dt.getMonth()+1)+sMonthDes+dt.getDate()+sDayDes;
			}
		}
	}
	return con;
}

function dp_setFormat(sFormat)
{
	this.formatString=sFormat;
	this.refreshPostText();
}

function dp_refreshPostText()
{
	var dp=this;
	var sFormat="yyyy/mm/dd";
	
	if(typeof(dp.formatString)=="string")
		sFormat=dp.formatString;
	var txt=dp.children[8];
	if(txt && txt.tagName=="INPUT")
		txt.value=dp.format(sFormat);
}

function dp_initDropDown()
{
	var dp=this;
	var ddt=dp.getDropDownTable();
	if(ddt)
	{
		ddt.curCell=null;
		var cell=null;
		var dt=new Date(dp.curDate.getFullYear(),dp.curDate.getMonth(),1);
		cell=ddt.rows[0].cells[1];
		if(cell)
		{
			cell.innerText=dp.getMonthName(dt.getMonth())+" "+dt.getFullYear();
		}

		var wd=dt.getDay();
		dt=new Date(dt.getFullYear(),dt.getMonth(),1-wd);
		var day=dt.getDate();
		
		
		for(var i=2; i<8; i++)
		{
			for(var j=0; j<7; j++)
			{
				cell=ddt.rows[i].cells[j];
				if(cell)
				{
					if(dp.curDate.getMonth()!=dt.getMonth())
						cell.className="DPCellOther";
					else if(dp.curDate.getDate()!=dt.getDate())
						cell.className="DPCell";
					else
					{
						cell.className="DPCell";
						dp_onCell(cell);
					}
					cell.innerText=day;
					cell.year=dt.getFullYear();
					cell.month=dt.getMonth();
					dt.setDate(day+1);
					day=dt.getDate();
				}
			}
		}
	}
}

function dp_getMonthName(lMonth)
{
	var mnArr=new Array("一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月");
	return mnArr[lMonth];
}

function dp_setWeekName()
{
	var dp=this;
	var ddt=dp.getDropDownTable();
	if(ddt)
	{
		var cell=null;
		
		for(var j=0; j<7; j++)
		{
			cell=ddt.rows[1].cells[j];
			if(cell)
			{
				cell.innerText=arguments[j];
			}
		}
	}
}

function dp_showDropDown()
{
	var dp=this;
	var ddt=dp.getDropDownTable();

	if(ddt)
	{
		if(ddt.style.display=="block")
		{
			dp.hideDropDown();
		}
		else
		{
			dp.initDropDown();
			if(ddt.clientWidth==0)
			{
				ddt.style.pixelLeft=-500;
				ddt.style.pixelTop=-500;
				ddt.style.display="block";
			}

			var ddtWidth=ddt.clientWidth==0?266:ddt.clientWidth;
			var ddtHeight=ddt.clientHeight==0?133:ddt.clientHeight;

			var lLeft=getOffsetLeft(dp);
			var lTop=getOffsetTop(dp)+dp.offsetHeight;	
			if((lTop+ddtHeight)>(document.body.clientHeight+document.body.scrollTop))
			{
				lTop-=(ddtHeight+dp.offsetHeight+2);
			}

			if((lLeft+ddtWidth)>(document.body.clientWidth+document.body.scrollLeft))
			{
				lLeft=document.body.clientWidth+document.body.scrollLeft-ddtWidth-2;
			}

			var off=parseInt(dp.offsetHor,10);
			if(isNaN(off))
				off=0;
			ddt.style.pixelLeft=lLeft+off;
			ddt.style.pixelTop=lTop;
			ddt.dpOldDocClick=document.onclick;
			ddt.dpOldDocKeyDown=document.onkeydown;

			event.cancelBubble=true;
			event.returnValue=false;
			document.onclick=dp_sub_docClick;
			document.onkeydown=dp_sub_dockeydown;

			ddt.style.display="block";
		}
	}
}

function getDropDownTable()
{
	var ddt=document.all("dpDropDownTable");
	if(!(ddt && ddt.tagName=="TABLE"))
		ddt=null;
	return ddt;
}

function dp_hideDropDown()
{
	var ddt=getDropDownTable();
	if(ddt)
	{
		ddt.style.display="none";
		document.onclick=ddt.dpOldDocClick;
		document.onkeydown=ddt.dpOldDocKeyDown;
	}
}

function dp_getDropDownTable()
{
	var dp=this;
	dp.dropDownTable=dp_createDropDown();

	if(dp.dropDownTable && dp.dropDownTable.tagName=="TABLE")
	{
		dp.dropDownTable.dp=dp;
		return dp.dropDownTable;
	}
	else
		return null;
}

function dp_onDateChange()
{
	var dp=this;
	if(dp.curDate.compare(dp.oldDate)!=0)
	{
		dp.oldDate=dp.curDate.clone();
		dp.refreshView();
		dp.refreshPostText();
		if(typeof(dp.dateChanged)=="function")
			dp.dateChanged(dp.curDate.getFullYear(),dp.curDate.getMonth()+1,dp.curDate.getDate());
	}
}

function dp_refreshView()
{
	var dp=this;
	var hr=false;
	
	if(dp && dp.curDate)
	{
		var src=dp.children[0];
		if(src && src.tagName=="INPUT")
		{
			src.value=dp.curDate.getFullYear();
			src=dp.children[2];
			if(src && src.tagName=="INPUT")
			{
				src.value=dp.curDate.getMonth()+1;
				src=dp.children[4];
				if(src && src.tagName=="INPUT")
				{
					src.value=dp.curDate.getDate();
					hr=true;
				}
			}
		}
	}
	return hr;
}

function dp_setTabIndex(lTabIndex)
{
	var dp=this;
	var hr=false;
	
	if(dp)
	{
		var src=dp.children[0];
		if(src && src.tagName=="INPUT")
		{
			src.tabIndex=lTabIndex;
			src=dp.children[2];
			if(src && src.tagName=="INPUT")
			{
				src.tabIndex=lTabIndex;
				src=dp.children[4];
				if(src && src.tagName=="INPUT")
				{
					src.tabIndex=lTabIndex;
					src=dp.children[7];
					if(src && src.tagName=="A")
					{
						src.tabIndex=lTabIndex;
						hr=true;
					}
				}
			}
		}
	}
	return hr;
}

function dp_setDateDes(sYearDes,sMonthDes,sDayDes)
{
	if(sYearDes==null)
		sYearDes="-";
	if(sMonthDes==null)
		sMonthDes="-";
	if(sDayDes==null)
		sDayDes="";
	
	var dp=this;
	var hr=false;
	
	var src=dp.children[1];
	if(src && src.tagName=="FONT")
	{
		src.innerText=sYearDes;
		src=dp.children[3];
		if(src && src.tagName=="FONT")
		{
			src.innerText=sMonthDes;
			src=dp.children[5];
			if(src && src.tagName=="FONT")
			{
				src.innerText=sDayDes;
				hr=true;
			}
		}
	}
	return hr;
}

function dp_setMaxDate(lYear,lMonth,lDay)
{
	var dp=this;
	var hr=false;
	
	if(dp)
	{
		lYear=parseInt(lYear,10);
		lMonth=parseInt(lMonth,10);
		lDay=parseInt(lDay,10);
		
		if(!(isNaN(lYear) || isNaN(lMonth) || isNaN(lDay)))
		{
			lMonth--;
			var dt=new Date(lYear,lMonth,lDay);
			var dMin=new Date(dp.minYear,dp.minMonth,dp.minDay);
			var cdMax=new Date(cl_dpMaxYear,cl_dpMaxMonth,cl_dpMaxDay);
			
			if(dt.compare(cdMax)<=0 && dt.compare(dMin)>=0)
			{
				dp.maxYear=dt.getFullYear();
				dp.maxMonth=dt.getMonth();
				dp.maxDay=dt.getDate();
				hr=true;
			}
		}
	}
	return hr;
}

function dp_setMinDate(lYear,lMonth,lDay)
{
	var dp=this;
	var hr=false;
	
	if(dp)
	{
		lYear=parseInt(lYear,10);
		lMonth=parseInt(lMonth,10);
		lDay=parseInt(lDay,10);
		
		if(!(isNaN(lYear) || isNaN(lMonth) || isNaN(lDay)))
		{
			lMonth--;
			var dt=new Date(lYear,lMonth,lDay);
			var dMax=new Date(dp.maxYear,dp.maxMonth,dp.maxDay);
			var cdMin=new Date(cl_dpMinYear,cl_dpMinMonth,cl_dpMinDay);
			
			if(dt.compare(dMax)<=0 && dt.compare(cdMin)>=0)
			{
				dp.minYear=dt.getFullYear();
				dp.minMonth=dt.getMonth();
				dp.minDay=dt.getDate();
				hr=true;
			}
		}
	}
	return hr;
}

function dp_setCurDate(lYear,lMonth,lDay)
{
	var dp=this;
	var hr=false;

	lYear=parseInt(lYear,10);
	lMonth=parseInt(lMonth,10);
	lDay=parseInt(lDay,10);
	
	if(!(isNaN(lYear) || isNaN(lMonth) || isNaN(lDay)))
	{
		var dt=new Date(lYear,lMonth-1,lDay);
		var dMax=new Date(dp.maxYear,dp.maxMonth,dp.maxDay);
		var dMin=new Date(dp.minYear,dp.minMonth,dp.minDay);
		if(dt.compare(dMax)<=0 && dt.compare(dMin)>=0)
		{
			dp.curDate=dt;
			dp.onDateChange();
			hr=true;
		}
	}
	
	if(!hr)
		dp.refreshView();
	return hr;
}

function dp_DropClick()
{
	var src=event.srcElement;
	var dp=getParentFromSrc(src,"SPAN")
	if(dp && dp.className=="DPFrame" && dp.dpEnabled)
	{
		dp.showDropDown();
	}
}

function dp_focus(srcType)
{
	var src=event.srcElement;
	if(src && src.tagName=="INPUT")
	{
		switch(srcType)
		{
			case 'year':
				break;
			case 'month':
				break;
			case 'day':
				break;
			default:;
		}
		src.select();
	}
	return true;
}

function dp_blur(srcType)
{
	var src=event.srcElement;
	var dp=getParentFromSrc(src,"SPAN")
	if(src && src.tagName=="INPUT" && dp && dp.className=="DPFrame")
	{
		var lYear=dp.curDate.getFullYear();
		var lMonth=dp.curDate.getMonth()+1;
		var lDay=dp.curDate.getDate();
		
		var val=parseInt(src.value,10);
		if(isNaN(val))
			val=-1;
		switch(srcType)
		{
			case 'year':
				lYear=val==-1?lYear:val;
				break;
			case 'month':
				lMonth=val==-1?lMonth:val;
				break;
			case 'day':
				lDay=val==-1?lDay:val;
				break;
			default:;
		}
		dp.setCurDate(lYear,lMonth,lDay);
		if(val==-1)
			dp.refreshView();
	}
	return true;
}

function dp_keyDown(srcType)
{
	var src=event.srcElement;
	var dp=getParentFromSrc(src,"SPAN")
	var bRefresh=true;
	
	if(dp && dp.className=="DPFrame")
	{
		var lYear=dp.curDate.getFullYear();
		var lMonth=dp.curDate.getMonth();
		var lDay=dp.curDate.getDate();
		var lStep=0;
		
		switch(event.keyCode)
		{
			case 38:
				lStep=1;
				break;
			case 40:
				lStep=-1;
				break;
			case 13:
				event.keyCode=9;
				break;
			default:
				bRefresh=false;
		}

		switch(srcType)
		{
			case 'year':
				lYear+=lStep;
				break;
			case 'month':
				lMonth+=lStep;
				break;
			case 'day':
				lDay+=lStep;
				break;
			default:;
		}
		if(bRefresh)
			dp.setCurDate(lYear,lMonth+1,lDay);
	}
	return true;
}

function dp_monthChange(lStep)
{
	var src=event.srcElement;
	if(src)
	{
		var ddt=getDropDownTable();
		if(ddt && ddt.dp)
		{
			var dt=ddt.dp.curDate.clone();
			var lOldMonth=dt.getMonth();
			var lOldDay=dt.getDate();
				
			dt.setDate(1);
			dt.setMonth(lOldMonth+lStep+1);
			dt.setDate(0);
			if(dt.getDate()>lOldDay)
				dt.setDate(lOldDay);
			if(ddt.dp.setCurDate(dt.getFullYear(),dt.getMonth()+1,dt.getDate()))
				ddt.dp.initDropDown();
		}
	}
}

function dp_yearChange(lStep)
{
	var src=event.srcElement;
	if(src)
	{
		var ddt=getDropDownTable();
		if(ddt && ddt.dp)
		{
			var dt=ddt.dp.curDate.clone();
			var lOldYear=dt.getYear();
			var lOldMonth=dt.getMonth();
			
			if(lOldYear == "99"){lOldYear = 1999;}
			dt.setYear(lOldYear+lStep);
			dt.setMonth(lOldMonth)	;		
			if(ddt.dp.setCurDate(dt.getFullYear(),dt.getMonth()+1,dt.getDate()))
				ddt.dp.initDropDown();
		}
	}
}

function dp_ddt_click()
{
	var src=event.srcElement;
	if(src && src.tagName=="TD")
	{
		var ddt=getDropDownTable();
		if(ddt && ddt.dp)
		{
			var lOldMonth=ddt.dp.curDate.getMonth();
			if(ddt.dp.setCurDate(src.year,parseInt(src.month,10)+1,parseInt(src.innerText,10)))
			{
				if(src.month!=lOldMonth)
					ddt.dp.initDropDown();
				else
					dp_onCell(src);
			}
		}
	}
}

function dp_onCell(src)
{
	var row=src.parentElement;
	if(row && row.tagName=="TR" && row.rowIndex>1)
	{
		var ddt=getDropDownTable();
		if(ddt)
		{
			if(ddt.curCell)
				ddt.curCell.className=ddt.curCellOldClass;
			ddt.curCellOldClass=src.className;
			src.className="DPCellSelect";
			ddt.curCell=src;
		}
	}
}

function dp_ddt_dblclick()
{
	var src=event.srcElement;
	if(src && src.tagName=="TD")
	{
		var ddt=getDropDownTable();
		if(ddt && ddt.dp)
		{
			var lOldMonth=ddt.dp.curDate.getMonth();
			if(ddt.dp.setCurDate(src.year,parseInt(src.month,10)+1,parseInt(src.innerText,10)))
			{
				ddt.dp.hideDropDown();
			}
		}
	}
}

function dp_sub_docClick()
{
	var src=event.srcElement;
	var ddt=getParentFromSrc(src,"TABLE");
	if(!ddt || ddt.id!="dpDropDownTable")
	{
		dp_hideDropDown();
	}
	event.cancelBubble=true;
	event.returnValue=false;

	return false;

}

function dp_sub_dockeydown()
{
	dp_hideDropDown();
	return true;
}
