<?php

if (!function_exists('str_split')) {
	function str_split($str, $len = 1) {
		$list = array();
		for ($i = 0, $l = strlen($str); $i < $l; $i += $len) {
			$list[] = substr($str, $i, $len);
		}

		return $list;
	}
}

//list($month, $day, $year) = split('[/.-]', $date);
$base_path_Modx=$modx->getOption('base_path');
if (!is_file($fullfile = $base_path_Modx.$p_img)) return $nophoto;

list($w, $h, $type) = @getimagesize($fullfile);
if (!$w || !$h) return $nophoto;

if (!$width && !$height) return $p_img;
if (!$width) $width = $w / $h * $height;
elseif (!$height) $height = $h / $w * $width;

//MI NORMA
if ($aspectratio=="1"){
	if (!isset($height)){
		//HEIGHT NOT SET
		$height=(($width*$h)/$w);
	}
	if (!isset($width)){
		//WIDTH NOT SET
		$width=(($height*$w)/$h);
	}
}


umask(0777);
$mask=$folder_permisions;
//$mask = intval($modx->getOption['new_folder_permissions'], 8);
if (!is_dir($base_path_Modx.$mp_dir)) {
	if (!@mkdir($base_path_Modx.$mp_dir, $mask)) return $nophoto;
	chmod($base_path_Modx.$mp_dir, $mask);
}

$imgfunc = array(
	1 => array('imagecreate' => 'imagecreatefromgif', 'imagesave' => 'imagegif'),
	2 => array('imagecreate' => 'imagecreatefromjpeg', 'imagesave' => 'imagejpeg'),
	3 => array('imagecreate' => 'imagecreatefrompng', 'imagesave' => 'imagepng')
);

$ph = $imgfunc[$type]['imagecreate']($fullfile);
$thumb = imagecreatetruecolor($width, $height);

if (!$isclip && $bgcolor) {
	$cl = str_split($bgcolor, 2);
	$color = imagecolorallocate($thumb, hexdec($cl[0]), hexdec($cl[1]), hexdec($cl[2]));
	imagefilledrectangle($thumb, 0, 0, $width, $height, $color);
}

if ($type == 2) imageinterlace($thumb, $interlace);

$dw = 0;
$dh = 0;

if (!$isclip) {
	$prop = $w / $h;
	if ($prop < $width / $height) {
		$dw = round(($width - $height * $prop) / 2);
	} else {
		$dh = round(($height - $width / $prop) / 2);
	}

	if (!@imagecopyresampled($thumb, $ph, $dw, $dh, 0, 0, $width-$dw-$dw, $height-$dh-$dh, $w, $h)) return $nophoto;
} else {
	$prop = $width / $height;
	if ($prop > $w / $h) {
		$dh = round(($h - $w / $prop) / 2);
	} else {
		$dw = round(($w - $h * $prop) / 2);
	}

	if (!@imagecopyresampled($thumb, $ph, 0, 0, $dw, $dh, $width, $height, $w-$dw-$dw, $h-$dh-$dh)) return $nophoto;
}

if (!($type == 2 ? @$imgfunc[$type]['imagesave']($thumb, $modx->config['base_path'].$mp_img, $quality) : @$imgfunc[$type]['imagesave']($thumb, $modx->config['base_path'].$mp_img))) return $nophoto;

chmod($base_path_Modx.$mp_img, $folder_permisions);

return $mp_img;

?>