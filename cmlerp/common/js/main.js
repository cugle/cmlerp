window.onload = window.onresize = function(){
	document.getElementById('inwin').height=document.getElementById('foot').offsetTop-73;
}
function getSubMenu(){
	
}
function gotoUrl(url){
//	document.getElementById('inwin').src = url;
	window.frames['inwin'].window.location.href=url;//reload();
}



//移动菜单
var beginMoving = false;
var t = null;
function MouseDownToMove (obj)
{
	obj = this;
    obj.mouseDownY = event.clientY;
    obj.mouseDownX = event.clientX;
    beginMoving = true;
    obj.setCapture ();
}

function MouseMoveToMove (obj)
{
	obj = this;
    if(!beginMoving) return false;
    obj.style.zIndex = 1;
	for (i = 0; i < obj.cells.length; i ++){
    	obj.cells [i].style.filter = "alpha(opacity = 50)";
		obj.cells [i].style.border = '1px solid #000000';
	}
    obj.style.top = (event.clientY - obj.mouseDownY);
    obj.style.left = (event.clientX - obj.mouseDownX);
}

function MouseUpToMove (obj)
{
	obj = this;
    if (!beginMoving) return false;
    obj.releaseCapture ();
    obj.style.top = 0;
    obj.style.left = 0;
    obj.style.zIndex = 0;
    beginMoving = false;

    var tempTop = event.clientY - obj.mouseDownY;
    var tempRowIndex = (tempTop - tempTop % 22) / 22;


    if (tempRowIndex + obj.rowIndex < 0 )
        tempRowIndex = -1;
    else
        tempRowIndex = tempRowIndex+obj.rowIndex;

    if (tempRowIndex >= obj.parentElement.rows.length - 1)
        tempRowIndex = obj.parentElement.rows.length - 1;

	for (i = 0; i < obj.cells.length; i ++){
    	obj.cells[i].style.filter = "alpha(opacity = 100)";
		obj.cells[i].style.border = '';
	}
    obj.parentElement.moveRow(obj.rowIndex, tempRowIndex);
}
function downfu(){
	obj = this;
	t1 = t.offsetTop;
	r = 22*(obj.rowIndex+1)+13;
	y = event.clientY;
	s = r + t1 - y;
	var newR = null;
	if(s>=0){
		newR = document.all.rsTable.insertRow(obj.rowIndex);
	}else{
		newR = document.all.rsTable.insertRow(obj.rowIndex+1);
	}
	newR.height=22;
	newR.style.position='relative';
	newR.insertCell().innerHTML = '1321';
	newR.insertCell().innerHTML = '1321';
	newR.insertCell().innerHTML = 'add';
	newR.insertCell().innerHTML = 'del';
	setEvent();
}
function setEvent(){
	t = document.getElementById('rsTable');
	for(var i=0; i<t.rows.length; i++){
		t.rows[i].ondblclick = downfu;
		t.rows[i].onmousedown= MouseDownToMove;
		t.rows[i].onmousemove= MouseMoveToMove;
		t.rows[i].onmouseup  = MouseUpToMove;
	}
}
function keydow(){
	alert(event.keyCode);
}