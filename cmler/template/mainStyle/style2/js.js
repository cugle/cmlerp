/*
Vanni 2006-10-26
*/
var handMove=false;

//得到对象所在的位置
function getPoint(e){
	var left = 0;
	var top  = 0;
	while (e.offsetParent){
		left += e.offsetLeft;
		top  += e.offsetTop;
		e     = e.offsetParent;
	}
	left += e.offsetLeft;
	top  += e.offsetTop;
	return {x:left, y:top};
}
function menuObj($id,$obj,$index,$parent){
	this.id = $id;
	this.obj = $obj;
	this.index = $index;
}
function openConfig(obj){
	if(obj.openid){
		window.open('./system/pageconfig.php?pageid='+obj.openid,'subwin','height=400,width=400,status=yes,toolbar=no,menubar=no,location=no');
	}
}



function MenuClass(){
	//属性定义

	this.menuArr = Array();
	this.path    = Array();
	this.openMenu = false;
	this.openButtons = true;
	this.leftOpen    = Array();	//左边打开的窗口

	this.rightOpen   = Array(); //右边打开的窗口

	this.handNums = 0;
	this.foot    = document.getElementById('foot');
	this.head    = document.getElementById('head');
	this.handobj = document.getElementById('hand-obj');
	this.handin  = document.getElementById('hand-in');
	this.leftHurdle   = document.getElementById('leftMain');
	this.leftItems    = document.getElementById('leftItems');
	this.leftButton   = document.getElementById('leftButton');
	this.rightHurdle  = document.getElementById('rightMain');
	this.rightButton  = document.getElementById('rightButton');
	this.rightTitle   = document.getElementById('rightTitle');
	this.rightCentent = document.getElementById('rightCenter');
	this.inwins = this.rightCentent.childNodes;
	this.currentWin   = this.inwins[0];

	//初始化方法

	this.init = function(url,num,configid){
		var ie = document.attachEvent?true:false;
		if(ie){
			document.attachEvent('onclick',function(){$m.openMenu=false;$m.hiddenMenu(0);$m.initmenustyle();});
		}else{
			document.addEventListener("click",function(){$m.openMenu=false;$m.hiddenMenu(0);$m.initmenustyle();},true);
		}
		this.handNums = num;
		this.resizeinwin();
		var d = document.getElementById('menuitem').rows[0].cells;
		for(var i=0; i<d.length; i++){
			if(i%2 != 0){
				if(ie){
					d[i].attachEvent('onmouseover',this.mainMenuOver);
					d[i].attachEvent('onmouseout',this.mainMenuOut);
					d[i].attachEvent('onclick',this.mainMenuClick);
				}else{
					d[i].addEventListener('mouseover',function(){$m.mainMenuOver(this);},true);
					d[i].addEventListener('mouseout',function(){$m.mainMenuOut(this);},true);
					d[i].addEventListener('click',function(){$m.mainMenuClick(this);},true);
				}
				d[i].className='none';
			}
		}
		if(url)	this.inwins[0].src = url;
		$handobj = document.getElementById('rightWins').childNodes[0];
		this.rightOpen.push({name:'main',handobj:$handobj,content:this.inwins[0],config:configid});
	}
	//改变内窗大小
	this.resizeinwin = function(){
		var cw = document.body.clientWidth;			//浏览器宽度

		var ch = $m.foot.offsetTop;					//浏览器高度

		var mh = $m.head.clientHeight;				//菜单的高度

		var lb = $m.leftButton.clientHeight;		//左边工具栏的高度
		var ft = $m.foot.clientHeight;				//底部工具栏的高度
		var rb = $m.rightButton.clientHeight;		//右边工具栏的高度
		var rt = $m.rightTitle.clientHeight;		//右边标题的高度


		$m.handobj.style.height = (ch - mh) + 'px';
		$m.leftHurdle.style.width = $m.handNums + 'px';
		if($m.openButtons){
			$m.currentWin.height = (ch - mh - rb - rt - 4) + 'px';
			$m.currentWin.width  = (cw - $m.leftButton.clientWidth - $m.handNums - 7) + 'px';
			$m.rightHurdle.style.left = $m.handNums + 7 + 'px';
			$m.handobj.style.left = $m.handNums + 'px';
		}else{
			$m.currentWin.height = (ch - mh - rt - 4) + 'px';
			$m.currentWin.width  = (cw - $m.leftButton.clientWidth - 7) + 'px';
			$m.rightHurdle.style.left = '7px';
			$m.handobj.style.left = '0px';
		}
		$m.setLeftHurdleHeight();
	}

	this.handDown = function(){
		var cw = document.body.clientWidth;
		if(parseInt($m.handobj.style.left) == 0){
			$m.openButtons = true;
			$m.handobj.style.left = $m.handNums + 'px';
			$m.leftHurdle.style.visibility = 'visible';
			$m.rightButton.style.position = 'relative';
			$m.rightButton.style.visibility = 'visible';
			$m.rightHurdle.style.left = $m.handNums + 7 + 'px';
			$m.currentWin.width = cw - $m.handNums - 7 + 'px';
			//$m.inwins[0].height = (ch - mh - rt - 6) + 'px';
		}else{
			$m.openButtons = false;
			$m.handobj.style.left = '0px';
			$m.rightHurdle.style.left = '7px';
			$m.leftHurdle.style.visibility = 'hidden';
			$m.rightButton.style.position = 'absolute';
			$m.rightButton.style.visibility = 'hidden';
			$m.currentWin.width = cw - 7 + 'px';
			//$m.inwins[0].height = (ch - mh - rb - rt - 6) + 'px';
		}
		$m.resizeinwin();
	}

	//初始子菜单样式

	this.initmenustyle = function(){
		var $mi = document.getElementById('menuitem').rows[0].cells;
		for(var $i=0; $i<$mi.length; $i++){
			if($i%2!=0){
				$mi[$i].className='none';
			}
		}
	}
	//主菜单三种事件

	this.mainMenuOver = function(ev){
		$m.initmenustyle();
		var sr = ev.srcElement || ev;
		if($m.openMenu){
			$m.dispMenu(sr.id);
			$m.path[0] = '当前位置：首页 > ' + sr.innerText;
			sr.className='clickOK';
		}else{
			sr.className='overthe';
		}
	}
	this.mainMenuOut = function(ev){
		var sr = ev.srcElement || ev;
		if($m.openMenu==false)
		sr.className='none';
	}
	this.mainMenuClick = function(ev){
		var sr = ev.srcElement || ev;
		if(sr.title!=''){
			$m.inwin.src = sr.title;
		}
		if(sr.className=='clickOK'){
			sr.className='overthe';
		}else{
			event.cancelBubble = true;
			$m.openMenu = true;
			$m.path[0] = '当前位置：首页 > ' + sr.innerText;
			sr.className='clickOK';
			$m.dispMenu(sr.getAttribute('id'));
		}
	}

	//得到菜单元素
	this.getMenuElement = function($id){
		var $i = 0;
		var $j = false;
		for(; $i < this.menuArr.length; $i++){
			if(this.menuArr[$i].id == $id){
				$j = true;
				break;
			}
		}
		if($j)	return this.menuArr[$i].obj;
		else	return false;
	}
	//创建菜单元素
	this.createMenuItem = function($parenId,$menuId){
		$p = this.getMenuElement($parenId);
		if($p) $index = $p.style.zIndex + 1;
		else   $index = 1;
		xajax_setSubMenu($menuId,$index);
		return this.menuArr[this.menuArr.length-1].obj;
	}
	//子菜单事件

	this.subMenuOver = function(){
		var $src = event.srcElement.parentNode; //TabObj
		var $trs = $src.parentNode.rows;
		var $index = $src.parentNode.parentNode.parentNode.style.zIndex;//Div zIndex
		for(var i=0; i<$trs.length; i++){
			$trs[i].className = '';
		}
		$m.path[$index] = $src.cells[1].innerHTML;
		$src.className='subMenuSelected';
		$m.foot.childNodes[0].innerHTML = $src.title;
	}
	//子菜单的点击事件
	this.subMenuClick = function(){
		$m.openMenu = false;
		var $src = event.srcElement.parentNode;
		if($src.title!='' && this.currentWin.src!=$src.title){
			$m.openRight(event);
		}
	}
	//隐藏菜单
	this.hiddenMenu = function($index){
		for($a in this.menuArr){
			if(this.menuArr[$a].index > $index)	this.menuArr[$a].obj.style.visibility = 'hidden';
		}
	}
	//显示主菜单

	this.dispMenu = function($id){
		var $o = this.getMenuElement($id);
		this.hiddenMenu(0);
		if($o!=false){
			$o.style.visibility = 'visible';
		}else{
			var $p = getPoint(event.srcElement);
			$o = this.createMenuItem(null,$id);
			$o.style.left = $p.x;
			$o.style.top  = 55 + 'px';
			$o.style.visibility = 'visible';
		}
	}
	//显示子菜单

	this.dispSubMenu = function ($parent,$subid){
		var $o = this.getMenuElement($subid);
		var $p = this.getMenuElement($parent);
		var $scrIndex = event.srcElement.parentNode.sectionRowIndex;
		this.hiddenMenu($p.style.zIndex);
		if($o!=false){
			$o.style.visibility = 'visible';
		}else{
			$o = this.createMenuItem($parent,$subid);
			var $x = parseInt($p.style.left) + parseInt($p.style.width);
			var $y = parseInt($p.style.top) + ($scrIndex * 22);
			$o.style.left = $x;
			$o.style.top  = $y;
			$o.style.visibility = 'visible';
		}
	}
	this.openLeft = function(ev){
		ev = ev || event;
		o = ev.currentTarget || ev.srcElement;
		o = this.getLeftObj(o);
		if(o.open)return;//是打开状态

		this.setLeftHurdleHeight();
	}
	this.setLeftHurdleHeight = function(){
		//打开的个数

		var i=0;
		for(var v in this.leftOpen)	if(this.leftOpen[v].opened) i++;
		
		//总高度

		var heig = this.foot.offsetTop - this.head.clientHeight - this.leftButton.clientHeight - this.foot.clientHeight;
		//alert('已有'+ (this.leftItems.childNodes.length) +'个栏位');
		heig -= (this.leftItems.childNodes.length) * 23;
		heig = Math.floor(heig/i);
		//alert(heig);
		//设置大家的高度

		for(var k in this.leftOpen){
			if(this.leftOpen[k].opened){
				this.leftOpen[k].centent.childNodes[0].height = heig;
			}
		}
	}
	//创建Left对象
	this.getLeftObj = function(obj){
		n = obj.getAttribute('ruleid');
		if(!this.leftOpen[n]){
			d = document.createElement('div');
			d.className = 'leftItem';
			dt = document.createElement('div');
			dt.className = 'leftTitle';
			dt.innerHTML = '<div id="left">&nbsp;'+o.innerHTML+'</div><div id="right"><span onClick="$m.removeLeftObj(\''+n+'\')">关闭&nbsp;</span></div>';
			dc = document.createElement('div');
			url = obj.getAttribute('ruleurl');
			dc.innerHTML = '<iframe src="'+url+'" frameborder="0" width="100%"></iframe>';
			dc.className = 'leftContent';
			d.appendChild(dt);
			d.appendChild(dc);
			this.leftItems.appendChild(d);
			this.leftOpen[n] = {'item':d,'title':dt,'centent':dc,'opened':true};
		}
		return this.leftOpen[n];
	}
	this.removeLeftObj = function(n){
		this.leftOpen[n].item.removeNode(true);
		this.leftOpen[n] = false;
		this.setLeftHurdleHeight();
	}
	this.openRight = function(ev){
		ev = ev || event;
		o = ev.currentTarget || ev.srcElement;
		o = this.getRightObj(o);
		document.getElementById('openConfigObj').openid = o.config;
		this.currentWin = o.content;
		this.setRightHurdleHeight();
		this.resizeinwin();
		o.handobj.className = 'subWinSelect';
	}
	//创建RIGHT对象
	this.getRightObj = function(obj){
		url = obj.getAttribute('ruleurl');
		n = obj.getAttribute('ruleid');
		var j=-1;
		for(var i=0; i<this.rightOpen.length; i++){
			if(this.rightOpen[i].name == n){j=i; break;}
		}
		if(j==-1){
			d = document.createElement('<div class="subWinSelect" onClick="$m.openRight(event);" ruleid="'+n+'" rulename"'+url+'"></div>');
			d.innerHTML = obj.innerHTML;
			document.getElementById('rightWins').appendChild(d);
			c = document.createElement('<iframe src="'+url+'" frameborder="0" name="inwin'+n+'"></iframe>');
			this.rightCentent.appendChild(c);
			$configid = n.substring(5);
			this.rightOpen.push({name:n,handobj:d,content:c,config:$configid});
			j = this.rightOpen.length-1;
		}
		return this.rightOpen[j];
	}
	this.setRightHurdleHeight = function(){
		for(i=0; i<this.rightOpen.length; i++){
			this.rightOpen[i].content.height = 0;
			this.rightOpen[i].handobj.className = 'subWinTitle';
		}
	}
	this.rightClose = function(){
		if(this.rightOpen.length<=1)return;
		this.currentWin.removeNode(true);
		var rw = document.getElementById('rightWins').getElementsByTagName('DIV');
		for(var i=0; i<rw.length; i++){
			if(rw[i].className=='subWinSelect')
				rw[i].removeNode(true);
		}
		var i=0;
		for(; i<this.rightOpen.length; i++){
			if(this.rightOpen[i].content == this.currentWin){
				break;
			}
		}
		this.rightOpen[i] = this.rightOpen[this.rightOpen.length-1];
		this.rightOpen.pop();
		var j = (i-1<0?0:i-1);
		this.currentWin = this.rightOpen[j].content;
		this.setRightHurdleHeight();
		rw[j].className = 'subWinSelect';
		this.resizeinwin();
	}
}