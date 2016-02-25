<?php
function shortenTextSimply($i,$length) {

	if (strlen($i) > $length) {
		$o = substr($i,0,$length) . '...';
	} else {
		$o = $i;
	}

	
	return $o;
}

function shortenText($i,$maxcharlength,$type='simple') {

	$o = 'no return value';

	if ($type == 'simple') $o = shortenTextSimply($i,$maxcharlength);
	
	else {
	
		$sentances = explode('.',$i);
	
		//	rebuild paragraph until we violate or equal $characterlength
		$new_paragraph = '';
	
		foreach ($sentances as $sentance) if (strlen($new_paragraph) < $maxcharlength && strlen($sentance)) {
			if (strlen($new_paragraph)) $new_paragraph .= '. ';
			$new_paragraph .= $sentance;
		}
	
		//	if our new paragraph is still too long, we'll shorten it
		if ((strlen($new_paragraph) +- $maxcharlength) > 35) {
		
			$words = explode(' ',$new_paragraph);
		
			$new_chopsuey = '';
			
			foreach ($words as $word) {
				if (strlen($new_chopsuey) < $maxcharlength) $new_chopsuey .= ' ' . $word;
			}
			
			$o = $new_chopsuey . '&#8230;';
			
		
		} else {
			
			$o = $new_paragraph . '.';
		
		}
	
	}
	
	return trim($o);

}

?>