/**
Vanni 2006-10-26 网站风格模板JS文件
*/
function gogo(ad){
	var subSubMenu = document.getElementById('subSubMenu');
	if( subSubMenu.scrollLeft+subSubMenu.clientWidth <= subSubMenu.scrollWidth){
		eval("subSubMenu.scrollLeft " + ad + "= 3;");
		move = setTimeout("gogo('"+ad+"')",10);
	}
}
function soso(){
	clearTimeout(move);
}
function gotoo(obj){
	p = document.getElementById('topMenu');
	po = p.rows[0];
	for(var i=0; i<po.cells.length; i++){
		if(po.cells[i] == obj || po.cells[i].className == '')continue;
		po.cells[i].className = 'topMenuItem';
	}
	po2 = p.rows[1];
	for(var i=0; i<po2.cells.length; i++){
		if(i == obj.cellIndex ) po2.cells[i].className = 'topMenuItemSel';
		else po2.cells[i].className = '';
	}
	obj.className = 'topMenuItemSel';
}
function init(){
	var tr = document.getElementById('topMenu').rows[0];
	for(var i=0; i<tr.cells.length; i++){
		if(tr.cells[i].className){
			tr.cells[i].onmouseover = function(){ if(this.className != 'topMenuItemSel') this.className = 'topMenuItemOver' };
			tr.cells[i].onmouseout  = function(){ if(this.className != 'topMenuItemSel') this.className = 'topMenuItem' };
			tr.cells[i].onclick  = function(){
				gotoo(this);
				document.getElementById('submenu').innerHTML = '';
				if(this.title){
					window.frames['inwin'].window.location.href=this.title;
					document.getElementById('subSubMenu').innerHTML = '';
					document.getElementById('currentPag').innerHTML = '首页 > ' + this.innerText;
				}
				loading();
				xajax_setSubMenuStr(this.id);
			};
		}
	}
	disp();
	document.getElementById('inwin').height=document.getElementById('foot').offsetTop-73;
};
window.onresize = function(){
	document.getElementById('inwin').height=document.getElementById('foot').offsetTop-73;
	disp();
};
function disp(){
	d  = document.getElementById('subSubMenu');
	dl = document.getElementById('mdivl'); 
	dr = document.getElementById('mdivr');
	if( d.scrollWidth > d.clientWidth && d.clientWidth>0){
		dl.style.display = '';
		dr.style.display = '';
	}else{
		dl.style.display = 'none';
		dr.style.display = 'none';
	}
	s = document.getElementById('submenu'); 
	if(s.innerText != '')	s.style.display = '';
	else s.style.display = 'none';
}
function gotoUrl(url,subId,isdir){
	if(url != ''){
		var spo = document.getElementById('subSubMenu').getElementsByTagName('a');
		for(var i=0; i<spo.length; i++){
			if(spo[i].id == subId) spo[i].className = 'subSubMenuSel';
			else spo[i].className = 'subSubMenu';
		}
		//alert(spo.length);
		xajax_setCurrentPage(subId);
		window.frames['inwin'].window.location.href=url;
	}
	if(isdir){
		loading();
		xajax_setSubSubMenuStr(subId);	
	}
}
function loading(){
	document.getElementById('loading').style.visibility = 'visible';
}
function goPageAttribute(pageid){
//	if(typeof(window.showModelessDialog)=='undefined'){
		window.open('./system/pageconfig.php?pageid='+pageid,'subwin','height=400,width=400,status=yes,toolbar=no,menubar=no,location=no');
//	}else{
//		window.showModelessDialog('./system/pageconfig.php?pageid='+pageid,'subwin','dialogWidth=400px;dialogheight=400px;help=no;center=yes;status=no;');
//	}
}