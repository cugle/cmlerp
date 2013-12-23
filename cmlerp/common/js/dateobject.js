function initDateObject()
{
	Date.prototype.compare=date_compare;
	Date.prototype.clone=date_clone;
	Date.prototype.format=date_format;
}

function date_format(sFormat)
{
	var dt=this;
	if(sFormat==null || typeof(sFormat)!="string")
		sFormat="";
	sFormat=sFormat.replace(/yyyy/ig,dt.getFullYear());
	var y=""+dt.getYear();
	if(y.length>2)
	{
		y=y.substring(y.length-2,y.length);
	}
	sFormat=sFormat.replace(/yy/ig,y);
	sFormat=sFormat.replace(/mm/ig,dt.getMonth()+1);
	sFormat=sFormat.replace(/dd/ig,dt.getDate());
	return sFormat;
}

function date_clone()
{
	return new Date(this.getFullYear(),this.getMonth(),this.getDate());
}

function date_compare(dtCompare)
{
	var dt=this;
	var hr=0;
	
	if(dt && dtCompare)
	{
		if(dt.getFullYear()>dtCompare.getFullYear())
			hr=1;
		else if(dt.getFullYear()<dtCompare.getFullYear())
			hr=-1;
		else if(dt.getMonth()>dtCompare.getMonth())
			hr=1;
		else if(dt.getMonth()<dtCompare.getMonth())
			hr=-1;
		else if(dt.getDate()>dtCompare.getDate())
			hr=1;
		else if(dt.getDate()<dtCompare.getDate())
			hr=-1;
	}
	return hr;
}

function date_getDateFromVT_DATE(dt)
{
	dt=dt.replace(/-/g,"/");
	dt=Date.parse(dt);
	if(isNaN(dt))
		dt=null;
	else
		dt=new Date(dt);
	return dt;
}

//Call the initialize function
initDateObject();
