<?php
function fractionToReal($i) {
	//	take a number represented using spaces and forward slashes
	//	and convert to real floating point number
	//	Note: If given a string with more than one space, it may barf
	$o 	= trim($i);
	$o 	= ereg_replace("[^0-9 /.]", "", $o);
	if (strlen($o)) {
		//	if there is anything at all to parse, do so
		//	otherwise return NULL
		$arr	= explode(' ',$o);
		$subtotal = 0;
		foreach($arr as $r) {
			if (is_numeric($r))	$subtotal = $subtotal + (real) $r;
			else {
				//	perform the conversion
				eval('$subtotal = $subtotal + ('.$r.');');
			}
		}
		$o = $subtotal;
	} else {
		$o = NULL;
	}
	return $o;
}

function is_whole($int){
        
        $ok2go = false;
        $msg = ' - ';
        
        /*
        // First check if it's a numeric value as either a string or number
        if(is_numeric($int) === TRUE){
            
            $msg = 'its numeric';
            
            // It's a number, but it has to be an integer
            if((int)$int == $int){

                $ok2go = true;
                $msg = 'its an int';
                
            // It's a number, but not an integer, so we fail
            }else{
            
                $ok2go =false;
                $msg = 'NOT INT';
            }
        
        // Not a number
        }else{
        
        	$msg = 'NaN!';
            //return FALSE;
            //	not a number
        }
        */
       
       $x 			= (float) $int;
       $xfloor	= (int) $int;
       
       $ff = ($x +- $xfloor);
       
       if ( ($x +- $xfloor) == 0) {		
       		$msg		.= 'its whole - ';
       		$is_whole	= true;
       }  else {
       		$is_whole = false;
       		$msg		.= 'NOT WHOLE - ';
       }
        
        if (is_numeric($x)) {
        	$msg 		.= 'its numeric';
        	$is_num = true;
       } else {
       		$msg		.= 'NOT NUMERIC';
       		$is_num = false;
       }
       
       $ok2go = ($is_num && $is_whole);
        
        //echo '<p>->'.$msg. ' (' . $int . '::'.$ff.')</p>';
        return $ok2go;
        
    }

function realToImperial($i) {
	//	do the exact opposite of fractionToReal()
	//	ie: take a number and return the english equivilant
	
	$i	= (float) $i;		// input
	$o	= NULL;				// output
	
	$w	= floor($i);		//	whole (the integer part of the number)
	$f	= (float) $i +- $w;	//	fraction (the fractional part of the number)
	$v	= '';				//	vulgar fraction (this will be the string representing our final output)
	$d	= 1;				//	denominator
	$n	= NULL;				//	numerator
	
	if ($w > 0) {
		$v .= $w . ' ';
	}
	if ($f > 0) {
		for ($x= 1; $x <= 100; $x++) {
			$n = $f * $x;
			$d = $x;
			if (is_whole($n)) {
				break;
			}
		}
		$v .= '<sup>' . $n . '</sup>' . '/' . '<sub>' . $d . '</sub>';
	}	
	$o = $v;	
	return $o; 
}

?>