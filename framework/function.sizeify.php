<?php
if (!defined('URLROOT_SIZEIFY')) define('URLROOT_SIZEIFY','http://sizeify.sjc.io');
function sizeify($imgurl,$resizeto) {
	$a = parse_url($imgurl);
	if (!isset($a['host'])) {
		throw new Exception('That URL had no host');
		return URLROOT_SIZEIFY . '/404/no-image-404.png';
	}
	if (!isset($a['path'])) {
		throw new Exception('That URL had no path');
		return URLROOT_SIZEIFY . '/404/no-image-404.png';
	}
	if (!isset($a['scheme'])) {
		throw new Exception('That URL had no scheme');
		return URLROOT_SIZEIFY . '/404/no-image-404.png';
	}
	if ($a['host'] == parse_url(URLROOT_SIZEIFY,PHP_URL_HOST)) {
		throw new Exception('You cannot sizeify a siziefied URL');
		return URLROOT_SIZEIFY . '/404/no-image-404.png';
	}
	$rhost	= implode('.',array_reverse(explode('.',$a['host'])));
	$fltn	= str_replace('/','!',trim($a['path'],'/'));
	$r	= URLROOT_SIZEIFY . '/' . $a['scheme'] . '/' . $rhost . '/' . $resizeto . '/' . $fltn;
	return $r;
}
?>
