<?php
function extractYearsFromString($i) {
	#	take a string, return an array of unique integers. converts things like '99 into 1999 and 04 into 2004
	$nlttcnpby		= 1900;	//	numbers lower than this could not possibly be a year
	$nhttcnpsy		= 2500;	//	numbers higher than this could not possibly be a year
	$thresh			= 25;	//	if this number or anything less apears, we assume it's 20xx. anything higher we assume 19xx
	$o				= array();		
	$numbers		= array_values(array_filter(explode('~',preg_replace('/(\D)/','~',$i))));
	foreach ($numbers as $number) {
		$number		= (int) $number;
		if ($number > $nlttcnpby && $number < $nhttcnpsy) {
			$o[] 	= $number;
		} elseif ($number < $thresh) {
			$o[] 	= $number + 2000;
		} elseif ($number <= 99) {
			$o[] 	= $number + 1900;
		}
	}
	$o = array_unique($o);
	return $o;
}
?>