<?
require('admin.inc.php');

if (!defined('WEB_ADMIN_DBTYPE')) exit('<script>alert("欢迎使用cml5.5客户关系管理系统！");location.href="'.WEB_ADMIN_HTTPPATH.'/setup/";</script>');

class pageIndex extends admin {
	function Main(){
//		$this -> debugOn();
		//登出
		if(isset($_GET['logout']))
		{
			$this -> loginObj -> logout();
			$this -> quit();
		}
		//已经登入
		elseif ($this->loginObj->getUid())
		{
			$uid = $this->loginObj->getUid();
			if (isset($_GET['action']) && $_GET['action'] == 'changerpass')
			{
				if(isset($_COOKIE['NEWPASS'])){
					$this -> dbObj -> Execute('update '.WEB_ADMIN_TABPOX."user set loginnum = loginnum + 1,userpass = '".md5($_COOKIE['NEWPASS'])."' where userid = $uid");
					setcookie('NEWPASS','',time()-1,'/');
				}
			}
			$this -> loginObj  = new Login(&$this -> dbObj,0);
			$this -> configObj = new Config(&$this -> dbObj,$uid,$uid);

			$this -> loginto($this -> configObj -> getValue('amdinMainStyle'));
		}
		//请求登入
		elseif (isset($_POST['gosubmit']) && $_POST['gosubmit']=='login')
		{
			if(strtoupper($_POST['verify']) != $_SESSION['VERIFY']){$this -> quit('验证失败！');}
			if( $this -> loginObj -> checkUser($_POST['username'],$_POST['userpass']) )
			{
				if( isset($_POST['guser']) && $_POST['guser'])
				{
					if ( ($info = $this -> loginObj -> registerGuser($_POST['guser'])) === true )
					{
						//$this -> loginObj -> registerGuser($_POST['guser']);
						$uid = $this -> loginObj -> getUid();
						$this -> configObj = new Config(&$this -> dbObj, $uid, $uid);
						$this -> loginto($this -> configObj -> getValue('amdinMainStyle'));
					}
					else
					{
						$this -> quit('组用户身份登录失败！信息：'.$info);
					}
				}
				else
				{
					$this -> loginObj -> registerUser();
					$uid = $this -> loginObj -> getUid();
					$this -> configObj = new Config(&$this -> dbObj, $uid, $uid);
					$this -> loginto($this -> configObj -> getValue('amdinMainStyle'));
				}
			}
			else
			{
				isset($_SESSION['errorNums']) ? $_SESSION['errorNums']++ : $_SESSION['errorNums'] = 1;
				if(!isset($_SESSION['errorTime']))$_SESSION['errorTime']=time();
		
				if( $this -> getValue('loginErrorLog') )
				{
					$this -> dbObj -> Execute('insert into '.WEB_ADMIN_TABPOX."log(logtypeid,srcuserid,content)value(1,1,'登录失败：IP:".$this -> loginObj -> getIP()." 用户:{$_POST['username']}')");
				}
				if ( $_SESSION['errorNums'] >= $this -> getValue('allowErrorLoginNums') )
				{
					if ($_SESSION['errorTime'] + $this -> getValue('loginErrorTimeOut') < time()) 
					{
						unset($_SESSION['errorTime'],$_SESSION['errorNums']);
						$this -> disp('登录失败！');
					}
					else
					{
						$this -> quit('登录失败！');
					}
				}
				else
				{
					$this -> quit('登录失败！');
				}
			}
		}
		//有过登录失败
		elseif (isset($_SESSION['errorNums']))
		{
			if ( $_SESSION['errorNums'] >= $this -> getValue('allowErrorLoginNums') )
			{
				if ($_SESSION['errorTime']+$this -> getValue('loginErrorTimeOut') < time()) 
				{
					unset($_SESSION['errorTime'],$_SESSION['errorNums']);
					$this -> disp();
				}
				else
				{
					require('wait.php');
				}
			}
			else
			{
				$this -> disp();
			}
		}
		//显示登录页

		else
		{
			$this -> disp();
		}
	}

	function disp()
	{
		if(isset($_POST['xajax'])){
			exit('已超时，请点退出，然后重新登录！');
		}else{
			require(WEB_ADMIN_TMPPATH.'/index.htm');
		}
	}
	
	function quit($info=null,$url=null)
	{
		if($info!=null) 	$info = "alert('$info');";
		if(is_null($url))	$url  = './';
		exit("<script>$info location.href='$url';</script>");
	}
	
	function loginto($style)
	{
		$this -> loginObj -> checkState();
		require(WEB_ADMIN_TMPPATH.'/mainStyle/'.$style.'/main.php');
		new pageMain(&$this);
		if(($_GET || $_POST) && !isset($_POST['xajax'])){
			$this->quit();
		}
	}
}

$main =  new pageIndex(0);
$main -> Main();
?>