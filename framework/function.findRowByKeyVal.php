<?php
function findRowByKeyVal($arr,$key,$val) {
	//	search for a particular value in a particular column
	//	and return the entire row
	$index_val	= -1;
	$o			= array();
	foreach ($arr as $row) {
		$index_val++;
		if ($row[$key] == $val) {
			$o = $arr[$index_val];
			break;
		}
	}
	return $o;
}
?>