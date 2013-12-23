<?php
session_start();
header("Content-type: image/png");
$str = '';
while (strlen($str)<6) {
	$i = rand(49,90);
	if(($i>57 && $i<65) || ($i==79) ){
		continue;
	}
	$str .= chr($i);
}
$_SESSION['VERIFY'] = $str;
$im    = imagecreatefromgif('common/img/bg.gif');
$color = imagecolorallocate($im, 22, 55, 88);
imagestring($im,5,3,2, $str, $color);
imagepng($im);
imagedestroy($im);
?> 