<?
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');
require(WEB_ADMIN_CLASS_PATH.'/Pages.cls.php');
/**
 * @package System
 */
class PageFeedback extends admin {
	function Main(){
		$this -> checkUser();

		if(isset($_GET['action'])){
			if ($_GET['action']=='add')
			{
				$_GET['userid'] = $this -> getUid();
				$rs = & $this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'feedback where feedbackid = -1');
				$sql = $this -> dbObj -> GetInsertSQL($rs,$_GET,get_magic_quotes_gpc());
				$this -> dbObj -> Execute($sql);
			}
			elseif ($_GET['action']=='del')
			{
				$delid = $_GET['delid'] + 0;
				$this -> dbObj -> Execute('delete from '.WEB_ADMIN_TABPOX.'feedback where feedbackid = '.$delid);
			}
			elseif ($_GET['action']=='upd')
			{
				$updid = $_GET['updid'] + 0;
				$rs = & $this -> dbObj -> Execute('select * from '.WEB_ADMIN_TABPOX.'feedback where feedbackid = '.$updid);
				$sql = $this -> dbObj -> GetUpdateSQL($rs,$_GET,get_magic_quotes_gpc());
				$this -> dbObj -> Execute($sql);
			}
			elseif($_GET['action']=='changeSchedule')
			{
				$id = $_GET['id'] + 0;
				$value = $_GET['value'] + 0;
				$this -> dbObj -> Execute('update '.WEB_ADMIN_TABPOX.'feedback set fulfill='.$value.' where feedbackid = '.$id);
			}
			elseif ($_GET['action']=='changeAppraise')
			{
				$id = $_GET['id'] + 0;
				$value = $_GET['value'] + 0;
				$this -> dbObj -> Execute('update '.WEB_ADMIN_TABPOX.'feedback set score='.$value.' where feedbackid = '.$id);
			}
			elseif ($_GET['action']=='accept')
			{
				$id = $_GET['id'] + 0;
				$this -> dbObj -> Execute('update '.WEB_ADMIN_TABPOX.'feedback set accepter='.$this->getUid().' where feedbackid = '.$id);
			}
		}
		$this -> disp();
	}
	function disp(){
		$stateArr = array('1'=>'紧急','2'=>'一般','3'=>'建议');

		$t = new Template('../template/system');
		$t -> set_file('f','feedback.html');
		$curUid = $this -> getUid();
		$t -> set_var('stateString',$this->getStateStr('state'));
		
		if($this->getAppend())$t -> set_var('disabled','');
		else $t -> set_var('disabled',' disabled');
		
		//处理显示的结果集
		$t -> set_block('f','row','r');
		$pages = isset($_GET['p'])?$_GET['p']+0:1;
		$rs = & $this -> dbObj -> pageExecute('select * from '.WEB_ADMIN_TABPOX.'feedback order by feedbackid desc',2,$pages);
		$po = new Pages($rs->_maxRecordCount,$pages,2);
		$t -> set_var('pages',$po -> disp());

		while (!$rs -> EOF) {
			$v = $rs -> fields;

			$row = array(
				'msgState'=>'','msgTitle'=>'','msgContent'=>'','msgDisp'=>'','msgUserGroup'=>'','msgAccepterStr'=>'',
				'msgAccepter'=>'','msgDoneStr'=>'','msgDone'=>'','msgOkStr'=>'','msgOk'=>'','msgIsOk'=>'','msgStyle'=>''
			);

			$row['msgUser'] = $this -> dbObj -> GetOne('select username from '.WEB_ADMIN_TABPOX.'user where userid = '.$v['userid']);
			$insql = 'select groupname from '.WEB_ADMIN_TABPOX.'group g,'.WEB_ADMIN_TABPOX.'usergroup ug where g.groupid = ug.groupid and ug.userid = '.$v['userid'];
			$inrs = $this -> dbObj -> GetArray($insql);
			foreach ($inrs as $inv){
				if($row['msgUserGroup'] == '') $row['msgUserGroup'] = $inv['groupname'];
				else $row['msgUserGroup'] .= '、'.$inv['groupname'];
			}
			
			$row['senddate'] = $v['senddate'];
//			print_r($this -> loginObj -> _attachRuleArray );
//			echo '|',$this->getAttach('Accept'),'|';
			if(!$v['accepter']){	//没有接受人
				if($v['userid'] != $curUid && $this->getAttach('Accept')){	//不是自己所题的问题
					//可以接受任务
					$row['msgState'] = $stateArr[$v['state']];
					if($v['state'] == 1) $row['msgStyle'] = 'style3';
					$row['msgTitle'] = $v['title'];
					$row['msgContent'] = $v['content'];
					$row['msgAccepterStr'] = '<input type=button value="接收问题并开始处理" onClick="location.href=\'feedback.php?action=accept&id='.$v['feedbackid'].'&p='.$pages.'\'">';
				}else{
					$row['msgState'] = $this->getStateStr('state'.$v['feedbackid'],$v['state']);
					$row['msgTitle'] = '<input id="title'.$v['feedbackid'].'" type=text value ='.$v['title'].'>';
					$row['msgContent'] = '<textarea id="content'.$v['feedbackid'].'" cols="50" rows="2" wrap="VIRTUAL">'.$v['content'].'</textarea>';
					if(($this->getModify() && $v['userid']==$this->getUid()) || ($this->getModify() && $this->getSupper()) )
						$row['msgContent'] .= '<input type=button value="修改" onclick="goupdate('.$v['feedbackid'].','.$pages.');">';
					if(($this->getDelete() && $v['userid']==$this->getUid()) || ($this->getDelete() && $this->getSupper()) )
						$row['msgContent'] .= '<input onclick="location.href=\'feedback.php?action=del&delid='.$v['feedbackid'].'&p='.$pages.'\'" type=button value="删除">';
					$row['msgDisp'] = 'none';
					//可以修改，或删除任务
				}
			}else{					//已经有人在处理了
				if($v['state'] == 1) $row['msgStyle'] = 'style3';
				$row['msgState'] = $stateArr[$v['state']];
				$row['msgTitle'] = $v['title'];
				$row['msgContent'] = $v['content'];
				$row['msgAccepterStr'] = '处理者：';
				$row['msgAccepter'] = $this -> dbObj -> GetOne('select username from '.WEB_ADMIN_TABPOX.'user where userid = '.$v['accepter']);
				$row['msgDoneStr'] = '进度：';
				
				if($v['fulfill'] >= 100){	//已处理完
					$row['msgDone'] = '已完成';
					$row['msgOkStr'] = '评价：';
					if($v['userid'] == $curUid){  //是自己所提交的问题
						//可以设置评价
						if(!$v['score'])
							$row['msgOk'] = $this -> getProgressStr('changeAppraise',$v['feedbackid'],$v['score'],$pages);
						else 
							$row['msgOk'] = $v['score'];
					}else{
						if(!$v['score'])$v['score']='尚未做出评价';
						$row['msgOk'] = $v['score'];
					}
				}else{
					if($v['accepter'] == $curUid){ //是自己所处理的问题
						//可以更新完成进度
						$row['msgDone'] = $this -> getProgressStr('changeSchedule',$v['feedbackid'],$v['fulfill'],$pages);
					}else{
						$row['msgDone'] = $v['fulfill'].'%';
					}
				}
			}
			$t -> set_var($row);
			$t -> parse('r','row',true);
			$rs -> MoveNext();
		}
		
		$t -> parse('out','f');
		$t -> p('out');
	}
	function getProgressStr($name,$id,$def,$p){
		$s = "<select onChange='location.href=\"feedback.php?action=$name&id=$id&p=$p&value=\"+this.value;'>";
		for ($i=0; $i<=100; $i=$i+10){
			$s.= "<option value=$i".($i==$def?' selected':'').">$i</option>";
		}
		return $s.'</select>';
	}
	function getStateStr($name,$def=''){
		$stateArr = array('1'=>'紧急','2'=>'一般','3'=>'建议');
		$stateString = "<select name='$name' id='$name'>";
		foreach ($stateArr as $k => $v){
			$stateString .= '<option value="'.$k.'"'.($k==$def?' selected':'').'>'.$v.'</option>';
		}
		return $stateString.'</select>';
	}
}
$main = new PageFeedback();
$main -> Main();
?>