<?php
require('../config.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

$t = new Template(WEB_ADMIN_PHPROOT.'/template/user/');
$t -> set_file('f','user.html');
		$t->left_delimiter = "[#"; //修改左边界符为[#
        $t->right_delimiter = "#]"; //修改右边界符#]
 
$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
$t -> parse('out','f');
$t -> p('out');
?>