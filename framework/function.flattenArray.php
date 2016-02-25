<?php

$tmparr = array();

function flattenArray($arr,$key_preceed='') {
	//	takes a multidimensional array and returns a 2-d array
	global $tmparr;
	foreach ($arr as $k => $v) {
		if ($v === '') {
			$tmparr[$key_preceed . $k] = '[-]';
		} elseif (is_scalar($v)) {
			$tmparr[$key_preceed . $k] = $v;
		} elseif (is_array($v)) {
			flattenArray($v,$key_preceed . $k . '-');
		}
	}
	return $tmparr;
}

?>