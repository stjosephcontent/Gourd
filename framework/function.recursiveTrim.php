<?php
function recursiveTrim($i) {
	if (is_scalar($i)) {
		$o = trim($i);
	} else {
		$o = array_map('recursiveTrim',$i);
	}
	return $o;
}
?>