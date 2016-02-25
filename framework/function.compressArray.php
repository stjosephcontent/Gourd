<?php

load_function('castAs');

function compressArray($i,$convert_to=false) {
	//	converts any values that happen to be 1-element arrays, to scalars.
	//	if input value is already a scalar, returns a 1-value array
	$r = array();
	
	if (!is_array($i)) $r = array(castAs($i,$convert_to));
	else {
		foreach ($i as $k => $v) {
			if (is_array($v) && sizeof($v) == 1 && is_scalar($v[0])) 	$r[$k] = castAs($v[0],$convert_to);
			else														$r[$k] = castAs($v,$convert_to);
		}
	}	
	return $r;
}
?>