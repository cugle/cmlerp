<?php
require('../config.php');
require(WEB_ADMIN_CLASS_PATH.'/template.cls.php');

$t = new Template(WEB_ADMIN_PHPROOT.'/template/user/');
$t -> set_file('f','user.html');
		$t->left_delimiter = "[#"; //�޸���߽��Ϊ[#
        $t->right_delimiter = "#]"; //�޸��ұ߽��#]
 
$t -> set_var('path',WEB_ADMIN_HTTPPATH.'/common/');
$t -> parse('out','f');
$t -> p('out');
?>