<?php
/**
 * 设置分页字符串,特色:提供灵活的分页方式,而业可以设置上下页图片
 * 测试方法
 * <?php
 * 	$test=new Pages(234,$_GET['p'],5);
 * 	echo $test->disp();
 * ?>
 * @author Vanni
*/
class Pages{
	var $disp_be_switch;				//是否显示第一页和最后一页
	var $disp_result_number;			//每页显示的记录数
	var $pagesNum;						//总页数
	var $dispPagesNum;					//显示页数
	var $currentPage;					//当前页
	var $resultNumbers;					//总记录数
	var $disp_type=array(
			1=>array(array('&lt;&lt;previous','next&gt;&gt;'),'..',array('<font color=red>[',']</font>')),
			2=>array(array('&lt;&lt;Previous','Next&gt;&gt;'),'…',array('<font color=red>〖','〗</font>')),
			3=>array(array('&lt;&lt;previous','next&gt;&gt;'),'┈',array('[',']')),
			4=>array(array('&lt;&lt;previous','next&gt;&gt;'),'·',array('〖','〗')),
			5=>array(array('&lt;&lt;','&gt;&gt;'),'┈',array('[',']')),
			6=>array(array('&lt;&lt;','&gt;&gt;'),'┈',array('<font color=red>[',']</font>'))
						);				//默认的几种样式
	/**
	* @desc 构造函数,给定:总记录数,当前页数,每页显示的记录数,分页样式0-5,是否绍终显示第一页和最后一页
	* @param int $resultNumber		总记录数
	* @param int $current				当前页数
	* @param int $rowNum				每页显示的记录数
	* @param int $dispNum				活动页数
	* @param int $type				分页样式0-5
	* @param boolean $be_switch		是否绍终显示第一页和最后一页
	* @return void
	*/
	function Pages($resultNumber,$current,$rowNum,$dispNum=5,$type=6,$be_switch=false){
		if ($type>6||$type<0)$type=6;
		$this->resultNumbers=$resultNumber;
		$this->pagesNum=ceil($resultNumber/$rowNum);	//总页数
		$this->currentPage=$current;					//当前页数
		$this->disp_result_number=$rowNum;				//每页显示的记录数
		$this->dispPagesNum=$dispNum;					//显示的活动页数
		$this->disp_type=$this->disp_type[$type];		//分页的样式
		$this->disp_be_switch=$be_switch;				//是否显示第一页和最后一页
	}
	/**
	* @desc 设置自己的样式时使用的函数
	第一个参数为数组array('','')
	第二个参数为数组array('','')
	第三个参数为字符
	第四个参数为布尔
	* @param array $previus_and_next	前一页,下一页的样式
	* @param array $height_light		当前页高亮显示
	* @param string $over				太多页时中间的省略部分
	* @return void
	*/
	function setOtherDispParam($previous_and_next,$height_light,$over,$dispNum=5,$be_switch=false){
		$this->disp_type=null;
		$this->disp_type=array($previous_and_next,$over,$height_light);
		$this->disp_be_switch=$be_switch;				//是否显示第一页和最后一页
		$this->dispPagesNum=$dispNum;					//显示的活动页数
	}
	/**
	* @desc 输出内容
	* @param string $param			分页参数
	* @param string $pageurl			URL地址
	* @return string
	*/
	function disp($param='p',$pageurl=''){
		if (!$pageurl)$pageurl="http://".$_SERVER['HTTP_HOST']. $_SERVER['PHP_SELF'].'?';	//当前文件路径
		$middle=(int)($this->dispPagesNum/2);					//中间数
		$startRow=1;											//开始下标
		$endRow=$this->pagesNum;							//结束下标
		$previous=false;										//有前导
		$next=false;											//有后导
		if ($this->pagesNum>$this->dispPagesNum){				//总页数大于总显示页数,说明使用 << >> 符号
			if ($this->currentPage>$middle+1){					//当前页大于总显示的一半+1,说明使用 << 符号
				$previous=true;									//将前导设置为有
				$startRow=($this->currentPage<$this->pagesNum-$middle)?$this->currentPage-$middle:($this->pagesNum-$this->dispPagesNum)+1;
				//将开始下标设置成:[当前页数 < 总页数 - 中间页数] ? 是[ 当前页-中间页 ] : 否[ 总页数 - 显示宽度 ]+1
			}
			else												//没有前导
			{
				$startRow=1;									//设置下标为第一页
			}
			if ($this->currentPage<$this->pagesNum-$middle){	//当前页小于总页数的一半,说明使用 >>符号
				$next=true;										//将后导设置为有
				$endRow=($startRow<$middle)?$endRow=$this->dispPagesNum:$this->currentPage+$middle;
				//将结束下标设置成:[开结行 < 中间行] ? 是[显示宽度] : 否[当前页 + 中间行]
			}
			else												//没有后导
			{
				$endRow=$this->pagesNum;						//将结束行设置成总页数
			}
		}
		$out='';												//用于输出的字串
		for ($i=$startRow;$i<$endRow+1;$i++){
			if ($this->currentPage==$i){
				$out.="{$this->disp_type[2][0]}{$i}{$this->disp_type[2][1]} ";
			}else {
				$out.="<a href='{$pageurl}{$param}={$i}'>{$i}</a> ";
			}
		}
		$previousPage=$this->currentPage-1;						//上一页
		$nextPage=$this->currentPage+1;							//下一页
		if ($previous){											//如果有前导,加上前导
			if ($this->disp_be_switch)							//是否显示最前页
				$out="<a href='{$pageurl}{$param}={$previousPage}'>{$this->disp_type[0][0]}</a> <a href='{$pageurl}{$param}=1'>1</a> {$this->disp_type[1]} {$out}";
			else 
				$out="<a href='{$pageurl}{$param}={$previousPage}'>{$this->disp_type[0][0]}</a> {$this->disp_type[1]} {$out}";
		}
		if ($next){												//如果有后导,加上后导
			if ($this->disp_be_switch)							//是否显示最后页
				$out.=" {$this->disp_type[1]} <a href='{$pageurl}{$param}={$this->pagesNum}'>{$this->pagesNum}</a> <a href='{$pageurl}{$param}={$nextPage}'>{$this->disp_type[0][1]}</a>";
			else 
				$out.=" {$this->disp_type[1]} <a href='{$pageurl}{$param}={$nextPage}'>{$this->disp_type[0][1]}</a>";
		}
		return $out;
	}
	/**
	* @desc 获得总页数
	* @return int
	*/
	function getCountPages(){
		return $this->pagesNum;
	}
	/**
	* @desc 获得总记录数
	* @return int
	*/
	function getCountResult(){
		return $this->resultNumbers;
	}
	/**
	* @desc 获得当前页的开始记录数
	* @return int
	*/
	function getCurrentPageStart(){
		return (($this->getCurrentPageNumber()-1)*$this->disp_result_number)+1;
	}
	/**
	* @desc 获得当前页的结束记录数
	* @return int
	*/
	function getCurrentPageEnd(){
		if ( ($this->currentPage*$this->disp_result_number)>$this->resultNumbers) return $this->resultNumbers;
		return $this->currentPage*$this->disp_result_number;
	}
	/**
	* @decs 获得当前页的页号
	* @return int
	*/
	function getCurrentPageNumber(){
		return $this->currentPage;
	}
}
?>