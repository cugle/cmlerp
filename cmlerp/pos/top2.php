<?
/**
 * @package System
 */
require('../admin.inc.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

class PageUser extends admin {
	function disp(){
		//����ģ��
		$t = new Template('../template/pos');
		$t->unknowns = "remove";
		$t->left_delimiter = "[#"; //�޸���߽��Ϊ[#
        $t->right_delimiter = "#]"; //�޸��ұ߽��#]	
		$t -> set_file('f','top.html');
		//��ǰ�û�
		$t -> set_var('username',$this ->dbObj-> GetOne('select username from '.WEB_ADMIN_TABPOX.'user where userid='.$this->getUid()));
		//���õ�ǰ������Ϣ
		 $t -> set_var('currentorganname',$_SESSION["currentorganname"]); 	
		 $sql='select * from '.WEB_ADMIN_TABPOX.'customer  where  agencyid ='.$_SESSION["currentorgan"].' and  MONTH(birthday)='.date("m",time())." and day(birthday)=".date("d",time());
		
		$inrs = &$this -> dbObj -> Execute($sql);
		$result = &$this -> dbObj -> Execute($sql);		
		$count=$result->RecordCount();
		 
		 
		 $t -> set_var('remind',"���칲��".$count."�˿�����"); 	
		$t -> set_var('userid',$this->getUid());
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispAppend(){
		$t = new Template('../template/system');
		$t -> set_file('f','userdetail.html');
		$t -> set_block('f','group','g');

		$groupArr = array();
		if($this -> isAppend){
			$t -> set_var('action','add');
			$t -> set_var('actionName','����');
		}else{
			$updid = $_GET[MODIFY.'id'] + 0 ;
			$t -> set_var($this->dbObj->GetRow('SELECT * FROM '.WEB_ADMIN_TABPOX.'user WHERE userid = '.$updid));
			$t -> set_var('updid',$updid);
			$t -> set_var('action','upd');
			$t -> set_var('actionName','�޸�');
			$gs = $this -> dbObj -> GetArray('SELECT groupid FROM '.WEB_ADMIN_TABPOX.'usergroup WHERE userid = '.$updid);
			foreach ($gs as $v)	$groupArr [] = $v['groupid'];
		}
		//��ǰ�û�����������
		$umgs = '0';
		//echo 'select agency_type id from '.WEB_ADMIN_TABPOX.'agency a  inner join '.WEB_ADMIN_TABPOX.'user u on a.agency_id=u.agencyid where u.userid='.$this->getUid();
		 $agency_type_id=&$this -> dbObj -> GetOne('select agencytype id from '.WEB_ADMIN_TABPOX.'agency a  inner join '.WEB_ADMIN_TABPOX.'user u on a.agency_id=u.agencyid where u.userid='.$this->getUid());
		//echo  $agency_type_id;
		//'select g.groupid from '.WEB_ADMIN_TABPOX.'groupmanager g  inner join '.WEB_ADMIN_TABPOX.'user u on g.userid=u.userid where u.agencyid='.$_SESSION["currentorgan"]
		if ($agency_type_id==1){
		$mgs = &$this -> dbObj -> Execute('select groupid from '.WEB_ADMIN_TABPOX.'group where agencyid='.$_SESSION["currentorgan"]);
		}else{
		$mgs = &$this -> dbObj -> Execute('select groupid from '.WEB_ADMIN_TABPOX.'groupmanager where userid='.$this->getUid());
		}
		while (!$mgs -> EOF) {
			$umgs.= ','.$mgs -> fields['groupid'];
			$mgs -> MoveNext();
		}
		//�������б�
		$rs = &$this -> dbObj -> Execute("select * from ".WEB_ADMIN_TABPOX.'group where groupid in('.$umgs.')');
		while ($rrs = &$rs -> FetchRow()) {
			$t -> set_var($rrs);
			if (in_array($rrs['groupid'],$groupArr)) {
				$t -> set_var('gchecked',' checked');
			}else{
				$t -> set_var('gchecked','');
			}
			$t -> parse('g','group',true);
		}
		$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
		$t -> parse('out','f');
		$t -> p('out');
	}
	function goDispModify(){
		$this-> goDispAppend();
	}
	function goDelete(){
		$delid = $_GET[DELETE.'id'] + 0;
		require_once(WEB_ADMIN_CLASS_PATH.'/power/Power.cls.php');
		$powerObj = new Power(&$this->dbObj,$this->getUid());
		$powerObj -> delUser($delid);
		$this -> quit('ɾ���ɹ���');
	}
	function goAppend(){
		$id = 0;
		$info = '';
		$username=$_POST['username'];

		if($this -> isAppend){
			$info = '����';
			$uid =$this -> dbObj -> GetOne('select userid from '.WEB_ADMIN_TABPOX."user where username = '".$username."'");
		    if($uid){exit("<script>alert('���û��Ѵ��ڣ���������д');history.go(-1);;</script>");}
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."user(username,userpass,loginnum,importer,agencyid)values('".$_POST['username']."','".md5($_POST['password'])."',0,".$this->getUid().",".$_SESSION["currentorgan"].")");
			$id = $this -> dbObj -> Insert_ID();
		}else{
			$info = '�޸�';
			$uid =$this -> dbObj -> GetOne('select userid from '.WEB_ADMIN_TABPOX."user where username = '".$username."'");
		    if($uid&&$uid!=$_POST['updid']){exit("<script>alert('���û��Ѵ��ڣ���������д');history.go(-1);;</script>");}
			$id = $_POST[MODIFY.'id'] + 0;
			if($_POST['password']){
				$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."user SET username='".$_POST['username']."',userpass='".md5($_POST['password'])."' WHERE userid = $id");
			}else{
				$this -> dbObj -> Execute('UPDATE '.WEB_ADMIN_TABPOX."user SET username='".$_POST['username']."' WHERE userid = $id");
			}
			if(isset($_POST['groups']))
			$this -> dbObj -> Execute('DELETE FROM '.WEB_ADMIN_TABPOX.'usergroup WHERE userid = '.$id);
		}
		if(isset($_POST['groups']))
		foreach ($_POST['groups'] as $v){
			$this -> dbObj -> Execute('INSERT INTO '.WEB_ADMIN_TABPOX."usergroup(userid,groupid,importer)values($id,$v,".$this->getUid().')');
		}
		$this -> quit($info.'�ɹ���');
	}
	function goModify(){
		$this -> goAppend();
	}
	function quit($info){
		exit("<script>alert('$info');location.href='user.php';</script>");
	}
}
$main = new PageUser();
$main -> Main();
?>