<?php
//	http://php.ca/manual/en/function.in-array.php#97465
//	If you found yourself in need of a multidimensional array in_array like function you can use the one below. Works in a fair amount of time
function in_multiarray($elem, $array) {
	$top = sizeof($array) - 1;
		$bottom = 0;
		while($bottom <= $top) {
			if($array[$bottom] == $elem) return true;
			else 
				if(is_array($array[$bottom]))
					if(in_multiarray($elem, ($array[$bottom])))
						return true;
	        $bottom++;
	    }
	return false;
}
?>