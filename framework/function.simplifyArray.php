<?php
function simplifyArray($i) {
	# take a complex array and return a flattened one with only scalar members
	$r = array();
	foreach ($i as $k => $v) {
		if (is_scalar($v)) {
			$r[$k] = $v; 
		} elseif (is_array($v)) {
			$r = array_merge($r,simplifyArray($v));
		}
	}
	return $r;
}
?>