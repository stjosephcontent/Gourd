<?php

function vulagarFractionToEnglish($i) {
	//	Takes fractions and the " character and returns words representing them
	//	ex: 'two thirds inch'
	$n = $i;
	$n = html_entity_decode($n);
	$n = str_replace('"',' inch ',$n);
	$n = str_replace('½','half',$n);
	$n = str_replace('¾','three quarters',$n);
	$n = str_replace('¼','a quarter',$n);
	$n = str_replace('⅔','two thirds',$n);
	$n = str_replace('⅓','a third',$n);
	
	return $n;

}


/**
*	Function: numberToString
*	Descrtipt: take an integer and return the english word that corresponds to it
*	@param:int
*	@return:string
*/
// 
function numberToString($i) {
	
	$nums 		= array();
	$nums[0]	= 'zero';
	$nums[1]	= 'one';
	$nums[2]	= 'two';
	$nums[3]	= 'three';
	$nums[4]	= 'four';
	$nums[5]	= 'five';
	$nums[6]	= 'six';
	$nums[7]	= 'seven';
	$nums[8]	= 'eight';
	$nums[9]	= 'nine';
	
	if 		(!is_integer($i))	$o = 'Not-Integer';
	elseif	($i < 0 || $i > 9)	$o = 'Out-of-range';
	else						$o = $nums[$i];
	return $o;
}

/** 
*  Function:   convert_number 
*
*  Description: 
*  Converts a given integer (in range [0..1T-1], inclusive) into 
*  alphabetical format ("one", "two", etc.)
*
*  @int
*
*  @return string
*
*/ 
function convert_number($number) 
{ 
    if (($number < 0) || ($number > 999999999)) 
    { 
    throw new Exception("Number is out of range");
    } 

    $Gn = floor($number / 1000000);  /* Millions (giga) */ 
    $number -= $Gn * 1000000; 
    $kn = floor($number / 1000);     /* Thousands (kilo) */ 
    $number -= $kn * 1000; 
    $Hn = floor($number / 100);      /* Hundreds (hecto) */ 
    $number -= $Hn * 100; 
    $Dn = floor($number / 10);       /* Tens (deca) */ 
    $n = $number % 10;               /* Ones */ 

    $res = ""; 

    if ($Gn) 
    { 
        $res .= convert_number($Gn) . " Million"; 
    } 

    if ($kn) 
    { 
        $res .= (empty($res) ? "" : " ") . 
            convert_number($kn) . " Thousand"; 
    } 

    if ($Hn) 
    { 
        $res .= (empty($res) ? "" : " ") . 
            convert_number($Hn) . " Hundred"; 
    } 

    $ones = array("", "One", "Two", "Three", "Four", "Five", "Six", 
        "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen", 
        "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eightteen", 
        "Nineteen"); 
    $tens = array("", "", "Twenty", "Thirty", "Fourty", "Fifty", "Sixty", 
        "Seventy", "Eigthy", "Ninety"); 

    if ($Dn || $n) 
    { 
        if (!empty($res)) 
        { 
            $res .= " and "; 
        } 

        if ($Dn < 2) 
        { 
            $res .= $ones[$Dn * 10 + $n]; 
        } 
        else 
        { 
            $res .= $tens[$Dn]; 

            if ($n) 
            { 
                $res .= "-" . $ones[$n]; 
            } 
        } 
    } 

    if (empty($res)) 
    { 
        $res = "zero"; 
    } 

    return $res; 
} 
/*

$cheque_amt = 8747484 ; 
try
    {
    echo convert_number($cheque_amt);
    }
catch(Exception $e)
    {
    echo $e->getMessage();
    }
*/ 
?>