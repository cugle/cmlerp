<?php
/*
* (C) Copyright 1999-2000 NetUSE GmbH
*                    Kristian Koehntopp
*
* $Id: template.inc,v 1.12 2002/07/11 22:29:51 richardarcher Exp $
* Phzzy 修改
*
*/

class Template
{
  var $classname = "Template";

  var $debug    = false;

  var $root     = ".";

  var $file     = array();

  var $varkeys  = array();

  var $varvals  = array();

  var $unknowns = "remove";

  var $halt_on_error  = "yes";

  var $last_error     = "";
  
  var $left_delimiter = "{";
  
  var $right_delimiter = "}";

  function Template($root = ".", $unknowns = "remove") {
    if ($this->debug & 4) {
      echo "<p><b>Template:</b> root = $root, unknowns = $unknowns</p>\n";
    }
    $this->set_root($root);
    $this->set_unknowns($unknowns);
  }


  function set_root($root) {
    if ($this->debug & 4) {
      echo "<p><b>set_root:</b> root = $root</p>\n";
    }
    if (!is_dir($root)) {
      $this->halt("set_root: $root is not a directory.");
      return false;
    }

    $this->root = $root;
    return true;
  }


  function set_unknowns($unknowns = "remove") {
    if ($this->debug & 4) {
      echo "<p><b>unknowns:</b> unknowns = $unknowns</p>\n";
    }
    $this->unknowns = $unknowns;
  }



  function set_file($varname, $filename = "") {
    if (!is_array($varname)) {
      if ($this->debug & 4) {
        echo "<p><b>set_file:</b> (with scalar) varname = $varname, filename = $filename</p>\n";
      }
      if ($filename == "") {
        $this->halt("set_file: For varname $varname filename is empty.");
        return false;
      }
      $this->file[$varname] = $this->filename($filename);
    } else {
      reset($varname);
      while(list($v, $f) = each($varname)) {
        if ($this->debug & 4) {
          echo "<p><b>set_file:</b> (with array) varname = $v, filename = $f</p>\n";
        }
        if ($f == "") {
          $this->halt("set_file: For varname $v filename is empty.");
          return false;
        }
        $this->file[$v] = $this->filename($f);
      }
    }
    return true;
  }



  function set_block($parent, $varname, $name = "") {
    if ($this->debug & 4) {
      echo "<p><b>set_block:</b> parent = $parent, varname = $varname, name = $name</p>\n";
    }
    if (!$this->loadfile($parent)) {
      $this->halt("set_block: unable to load $parent.");
      return false;
    }
    if ($name == "") {
      $name = $varname;
    }

    $str = $this->get_var($parent);
    $reg = "/[ \t]*<!--\s+BEGIN $varname\s+-->\s*?\n?(\s*.*?\n?)\s*<!--\s+END $varname\s+-->\s*?\n?/sm";
    preg_match_all($reg, $str, $m);
    $str = preg_replace($reg, $this->left_delimiter . "$name" . $this->right_delimiter, $str);
    $this->set_var($varname, $m[1][0]);
    $this->set_var($parent, $str);
    return true;
  }


  function set_var($varname, $value = "", $append = false) {
    if (!is_array($varname)) {
      if (!empty($varname)) {
        if ($this->debug & 1) {
          printf("<b>set_var:</b> (with scalar) <b>%s</b> = '%s'<br>\n", $varname, htmlentities($value));
        }
        $this->varkeys[$varname] = "/".$this->varname($varname)."/";
        if ($append && isset($this->varvals[$varname])) {
          $this->varvals[$varname] .= $value;
        } else {
          $this->varvals[$varname] = $value;
        }
      }
    } else {
      reset($varname);
      while(list($k, $v) = each($varname)) {
        if (!empty($k)) {
          if ($this->debug & 1) {
            printf("<b>set_var:</b> (with array) <b>%s</b> = '%s'<br>\n", $k, htmlentities($v));
          }
          $this->varkeys[$k] = "/".$this->varname($k)."/";
          if ($append && isset($this->varvals[$k])) {
            $this->varvals[$k] .= $v;
          } else {
            $this->varvals[$k] = $v;
          }
        }
      }
    }
  }



  function clear_var($varname) {
    if (!is_array($varname)) {
      if (!empty($varname)) {
        if ($this->debug & 1) {
          printf("<b>clear_var:</b> (with scalar) <b>%s</b><br>\n", $varname);
        }
        $this->set_var($varname, "");
      }
    } else {
      reset($varname);
      while(list($k, $v) = each($varname)) {
        if (!empty($v)) {
          if ($this->debug & 1) {
            printf("<b>clear_var:</b> (with array) <b>%s</b><br>\n", $v);
          }
          $this->set_var($v, "");
        }
      }
    }
  }



  function unset_var($varname) {
    if (!is_array($varname)) {
      if (!empty($varname)) {
        if ($this->debug & 1) {
          printf("<b>unset_var:</b> (with scalar) <b>%s</b><br>\n", $varname);
        }
        unset($this->varkeys[$varname]);
        unset($this->varvals[$varname]);
      }
    } else {
      reset($varname);
      while(list($k, $v) = each($varname)) {
        if (!empty($v)) {
          if ($this->debug & 1) {
            printf("<b>unset_var:</b> (with array) <b>%s</b><br>\n", $v);
          }
          unset($this->varkeys[$v]);
          unset($this->varvals[$v]);
        }
      }
    }
  }


  function subst($varname, $append = false) {
    $varvals_quoted = array();
    if ($this->debug & 4) {
      echo "<p><b>subst:</b> varname = $varname</p>\n";
    }
    if (!$this->loadfile($varname)) {
      $this->halt("subst: unable to load $varname.");
      return false;
    }

    // quote the replacement strings to prevent bogus stripping of special chars
    reset($this->varvals);
    while(list($k, $v) = each($this->varvals)) {
      $varvals_quoted[$k] = preg_replace(array('/\\\\/', '/\$/'), array('\\\\\\\\', '\\\\$'), $v);
    }

    $str = $this->get_var($varname);
    $str = preg_replace($this->varkeys, $varvals_quoted, $str);
    return $str;
  }



  function psubst($varname) {
    if ($this->debug & 4) {
      echo "<p><b>psubst:</b> varname = $varname</p>\n";
    }
    print $this->subst($varname);

    return false;
  }



  function parse($target, $varname, $append = false) {
    if (!is_array($varname)) {
      if ($this->debug & 4) {
        echo "<p><b>parse:</b> (with scalar) target = $target, varname = $varname, append = $append</p>\n";
      }
      $str = $this->subst($varname);
      if ($append) {
        $this->set_var($target, $this->get_var($target) . $str);
      } else {
        $this->set_var($target, $str);
      }
    } else {
      reset($varname);
      while(list($i, $v) = each($varname)) {
        if ($this->debug & 4) {
          echo "<p><b>parse:</b> (with array) target = $target, i = $i, varname = $v, append = $append</p>\n";
        }
        $str = $this->subst($v);
        if ($append) {
          $this->set_var($target, $this->get_var($target) . $str);
        } else {
          $this->set_var($target, $str);
        }
      }
    }

    if ($this->debug & 4) {
      echo "<p><b>parse:</b> completed</p>\n";
    }
    return $str;
  }



  function pparse($target, $varname, $append = false) {
    if ($this->debug & 4) {
      echo "<p><b>pparse:</b> passing parameters to parse...</p>\n";
    }
    print $this->finish($this->parse($target, $varname, $append));
    return false;
  }


  function get_vars() {
    if ($this->debug & 4) {
      echo "<p><b>get_vars:</b> constructing array of vars...</p>\n";
    }
    reset($this->varkeys);
    while(list($k, $v) = each($this->varkeys)) {
      $result[$k] = $this->get_var($k);
    }
    return $result;
  }


  function get_var($varname) {
    if (!is_array($varname)) {
      if (isset($this->varvals[$varname])) {
        $str = $this->varvals[$varname];
      } else {
        $str = "";
      }
      if ($this->debug & 2) {
        printf ("<b>get_var</b> (with scalar) <b>%s</b> = '%s'<br>\n", $varname, htmlentities($str));
      }
      return $str;
    } else {
      reset($varname);
      while(list($k, $v) = each($varname)) {
        if (isset($this->varvals[$v])) {
          $str = $this->varvals[$v];
        } else {
          $str = "";
        }
        if ($this->debug & 2) {
          printf ("<b>get_var:</b> (with array) <b>%s</b> = '%s'<br>\n", $v, htmlentities($str));
        }
        $result[$v] = $str;
      }
      return $result;
    }
  }

  function get_undefined($varname) {
    if ($this->debug & 4) {
      echo "<p><b>get_undefined:</b> varname = $varname</p>\n";
    }
    if (!$this->loadfile($varname)) {
      $this->halt("get_undefined: unable to load $varname.");
      return false;
    }

    preg_match_all("/{([^ \t\r\n}]+)}/", $this->get_var($varname), $m);
    $m = $m[1];
    if (!is_array($m)) {
      return false;
    }

    reset($m);
    while(list($k, $v) = each($m)) {
      if (!isset($this->varkeys[$v])) {
        if ($this->debug & 4) {
         echo "<p><b>get_undefined:</b> undefined: $v</p>\n";
        }
        $result[$v] = $v;
      }
    }

    if (count($result)) {
      return $result;
    } else {
      return false;
    }
  }

  function finish($str) {
    switch ($this->unknowns) {
      case "keep":
      break;

      case "remove":
        $str = preg_replace('/{[^ \t\r\n}]+}/', "", $str);
      break;

      case "comment":
        $str = preg_replace('/{([^ \t\r\n}]+)}/', "<!-- Template variable \\1 undefined -->", $str);
      break;
    }

    return $str;
  }


  function p($varname) {
    print $this->finish($this->get_var($varname));
  }


  function get($varname) {
    return $this->finish($this->get_var($varname));
  }


  function filename($filename) {
    if ($this->debug & 4) {
      echo "<p><b>filename:</b> filename = $filename</p>\n";
    }
    if (substr($filename, 0, 1) != "/") {
      $filename = $this->root."/".$filename;
    }

    if (!file_exists($filename)) {
      $this->halt("文件:  $filename 不存在。有可能您的安装没有成功。");
    }
    return $filename;
  }


  function varname($varname) {
    return preg_quote($this->left_delimiter.$varname.$this->right_delimiter);
  }

  function loadfile($varname) {
    if ($this->debug & 4) {
      echo "<p><b>loadfile:</b> varname = $varname</p>\n";
    }

    if (!isset($this->file[$varname])) {
      // $varname does not reference a file so return
      if ($this->debug & 4) {
        echo "<p><b>loadfile:</b> varname $varname does not reference a file</p>\n";
      }
      return true;
    }

    if (isset($this->varvals[$varname])) {
      // will only be unset if varname was created with set_file and has never been loaded
      // $varname has already been loaded so return
      if ($this->debug & 4) {
        echo "<p><b>loadfile:</b> varname $varname is already loaded</p>\n";
      }
      return true;
    }
    $filename = $this->file[$varname];

    /* use @file here to avoid leaking filesystem information if there is an error */
    $str = implode("", @file($filename));
    if (empty($str)) {
      $this->halt("loadfile: While loading $varname, $filename does not exist or is empty.");
      return false;
    }

    if ($this->debug & 4) {
      printf("<b>loadfile:</b> loaded $filename into $varname<br>\n");
    }
    $this->set_var($varname, $str);

    return true;
  }

  function halt($msg) {
    $this->last_error = $msg;

    if ($this->halt_on_error != "no") {
      $this->haltmsg($msg);
    }

    if ($this->halt_on_error == "yes") {
      die("<b>程序意外终止。</b>");
    }

    return false;
  }

  function haltmsg($msg) {
    printf("<b>模板错误:</b> %s<br>\n", $msg);
  }

//------------------------------------------Fyini 增加
	/**
	* @desc 存储输出的变量到文件，请使用一个绝对文件地址
	* @param $filename file name
	* @param $varname var name
	* @return boolean
	*/
	function saveOutToFile($filename,$varname)
	{
		$tempString=$this->get($varname);
		if (!$filehandle=fopen($filename,'w'))		return false;
		if (!flock($filehandle,LOCK_EX))			return false;			//防止可能有同时修改文件的可能
		if (fwrite($filehandle,$tempString)===false)return false;
		if (!flock($filehandle,LOCK_UN))			return false;
		return true;
	}
	/**
	@desc 分板整块，特点，无需设置块即无需调用set_block()方法，自动分析，即无需调用parse()方法分析块，自动调，并增加到块
	$DataArr：要填充的数据引用　形式为array('0'=>array('name'=>'老千','sex'=>'男'),'1'=>array('name'=>'小林','sex'=>'男')....)
	$disposeField：要特别处理的字段　默认为空　形式为array('字段1'=>'方法1#参数1#参数2,方法2#参数1,方法3...','字段2'=>'方法1',....)
	$className：为参数4的操作类名。注，是类名字而非类对象，默认为StringClass类
	注意！：$disposeField 的方法为 $className 类里的方法，此处并没嵌入类文件。如无此类，新建此类，构造方法应为 $className(str)
	方法与参数之间用#隔开，没有#号表示无参，而方法跟方法之间用‘,’号隔开，两都均为小写分隔符
	
	@param String $parentBlockName 父级块名
	@param String $blockName 模块里的块名
	@param &Array $DataArr 数据结果集的引用，二维数组
	@param Array  $disposeField 需要特别处理的字段
	@param String $className 处理特殊字段时要使用的类名
	*/
	function parseBlock($parentBlockName,$blockName,&$DataArr,$disposeField=null,$className='StringClass'){
		$blockNameVal=$blockName.'_s';
		$this->set_block($parentBlockName,$blockName,$blockNameVal);
		$this->parseInBlock($blockName,$blockNameVal,&$DataArr,$disposeField,$className);
	}
	/**
	@desc 分板内部块
	$DataArr：要填充的数据引用　形式为array('0'=>array('name'=>'老千','sex'=>'男'),'1'=>array('name'=>'小林','sex'=>'男')....)
	$disposeField：要特别处理的字段　默认为空　形式为array('字段1'=>'方法1#参数1#参数2,方法2#参数1,方法3...','字段2'=>'方法1',....)
	$className：为参数4的操作类名。注，是类名字而非类对象，默认为StringClass类
	注意！：$disposeField 的方法为 $className 类里的方法，此处并没嵌入类文件。如无此类，新建此类，构造方法应为 $className(str)
	方法与参数之间用#隔开，没有#号表示无参，而方法跟方法之间用‘,’号隔开，两都均为小写分隔符
	
	@param String $blockName 模块里的块名
	@param String $blockNameVal 模块的变量名
	@param &Array $DataArr 数据结果集的引用，二维数组
	@param Array  $disposeField 需要特别处理的字段
	@param String $className 处理特殊字段时要使用的类名
	*/
	function parseInBlock($blockName,$blockNameVal,&$DataArr,$disposeField=null,$className='StringClass'){
		$this->set_var($blockNameVal);
		for ($i=0;$i<count($DataArr);$i++){
			if ($disposeField&&is_array($disposeField)){
				foreach ($disposeField as $k=>$v){					//循环需要处理的字段
					$tempStrObj=new $className($DataArr[$i][$k]);
					$funs=explode(',',$v);
					for ($j=0;$j<count($funs);$j++)	{					//循环方法列表
						$params=explode('#',$funs[$j]);					//处理参数
						if (method_exists($tempStrObj,$params[0])) {	//如果有此方法
							$evalStr = '$DataArr[$i][$k]=$tempStrObj->$params[0](';
							for ($n=1; $n<count($params);$n++) {
								if ($n==1)	$evalStr .= '$params['.$n.']';
								else 		$evalStr .= ',$params['.$n.']';
							}
							eval($evalStr.');');
						}
					}
				}
			}
			$this->set_var($DataArr[$i]);
			$this->parse($blockNameVal,$blockName,true);
		}
	}
}
?>
