<?
/**
 * @desc 登录测试认证类

 * @author  Vanni
 * @version 1.0
 * @package power
 */
class LoginCheck{
	/**
	 * 构造函数

	 *
	 * @param &logObj $logObj 登录对象的引用

	 * @return LoginCheck
	 */
	function LoginCheck(&$logObj){
		//配置类

		$row = $logObj -> _db -> GetRow('select * from '.WEB_ADMIN_TABPOX."login where clientid = '".$logObj->getClientId()."'");
		//exit();
		//没有登录过

		if(!$row)
		{
			$logObj -> logout();
			$this->_exit('请登录！',WEB_ADMIN_HTTPPATH.'/');
		}
		$conObj = new Config(&$logObj->_db,$row['userid'],$row['userid']);
		//超时
		if ( ($row['logintime']+$conObj->getValue('loginTimeOut')) <= time())
		{
			$logObj -> logout();
			$this->_exit('您已超时，请重新登录！',WEB_ADMIN_HTTPPATH.'/');
		}
		//状态1
		if ($row['updatestate']==1 && $conObj->getValue('changeRuleLogout'))
		{
			$this->_exit('您的权限已被更新，系统将重新登录，以更新权限！',WEB_ADMIN_HTTPPATH.'/relogin.php');
		}
		//状态2
		if ($row['updatestate']==2  && $conObj->getValue('singleLogi'))
		{
			$logObj -> logout();
			$this->_exit('您帐号已在别处登录，您被迫退出！如果希望帐号在多处同时登录，请修改您的用户配置！',WEB_ADMIN_HTTPPATH.'/');
		}
		
		//浏览权

		if(!$logObj->getAllowBrowse() && $logObj->_pageid>0)
		{
			$this->_exit('本页浏览权已被禁止，请等待开通！');
		}
		
		//要更改密码

		$fristLogin = $logObj ->_db -> GetOne('select loginnum from '.WEB_ADMIN_TABPOX.'user where userid='.$row['userid']);
		if(!$fristLogin && $conObj->getValue('firstLoginChangePass'))
		{
			exit("
			<script>
			newpass = prompt('第一次登录必须更改密码，请输入新密码！\\n注意：密码将以明文形式输入，请留意旁边是否还有其它人！\\n','');
			var expire = '';
			expire = new Date((new Date()).getTime() + 10000);
			expire = '; expires=' + expire.toGMTString();
			document.cookie = 'NEWPASS=' + newpass + expire +'path=/';
			location.href = '".WEB_ADMIN_HTTPPATH."/?action=changerpass';
			</script>");
		}
		

		//跟踪组长登录后的操作明细
		if ($logObj ->_superid && $conObj -> getValue('logGroupManagerActio'))
		{
			$manager = $logObj->_db->GetOne('select username from '.WEB_ADMIN_TABPOX.'user where userid='.$logObj->_superid);
			$page    = $logObj->_db->GetOne('select rulename from '.WEB_ADMIN_TABPOX.'rule where ruleid='.$logObj->_pageid);
			if ($page){
				$get = $post = '';
				if($_POST){
					foreach ($_POST as $k=>$v){
						if ($post=='') 	$post = '['.$k.']=>'.$v;
						else 			$post.= ', '.'['.$k.']=>'.$v;
					}
				}
				if($_GET){
					foreach ($_GET as $k=>$v){
						if ($get=='') 	$get = '['.$k.']=>'.$v;
						else 			$get.= ','.'['.$k.']=>'.$v;
					}
				}
				$info = "manager:$manager page:$page".($get?'get:'.$get:' ').($post?'post:'.$post:'');
				if (WEB_ADMIN_DBTYPE == 'mysql'){
					$info = mysql_escape_string($info);
				}else{
					$info = str_replace("'","''",$info);
				}
				$logObj->_db->Execute("insert into ".WEB_ADMIN_TABPOX."log(logtypeid,srcuserid,content)values(2,$logObj->_user,'$info')");
			}
		}
	}

	/**
	 * 检测到不合法时的退出函数

	 *
	 * @param String $info 要显示的信息
	 * @param String $url 跳转的URL
	 */
	function _exit($info,$url=null){
		$s = '';
		if ($url)	$s = "top.location.href ='$url';";
		exit("<script>alert('$info'); $s </script>");
	}
}
?>