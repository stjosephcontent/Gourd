<?php
function deTab_olde($i) {
//	take a block of text that might be "entabbed" and make it flush to the left
//	Simpler. possibly less buggy
	$o = $i;
	$o = str_replace('	',' ',$o);
	$o = str_replace('  ',' ',$o);
	$o = str_replace('  ',' ',$o);
	return $o;
}

function deTab($i) {
//	take a block of text that might be "entabbed" and make it flush to the left
//	Tries to preserve natural tabbing with the block
	$bits = explode("\n",trim($i));
	$lowest_numtabs = 99;
	$first_pass = true;
	foreach ($bits as $bit) {
		if (!$first_pass) {
			$chars = str_split($bit);
			$numtabs = 0;
			foreach ($chars as $char) {
				if ($char == '	') $numtabs++;
			}	
			if ($numtabs < $lowest_numtabs) {
				$lowest_numtabs = $numtabs;
			}
		}
	$first_pass = false;
	}
	//	Got the indentation value, now strip it
	$magic_string = "\n";
	for ($x = 1; $x <= $lowest_numtabs; $x++) {
	    $magic_string .= '	';
	}
	$o = "\n" . $i . "\n";
	$o = str_replace($magic_string,"\n",$o);
	$o = trim($o);
	return $o;
}

?>