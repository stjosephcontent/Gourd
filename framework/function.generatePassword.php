<?php
/*
function generatePassword

@description		generates a password of specified length

@param		int		$plength	(number of characters the password ought to be)
@return		str					(returns the password)

@notes				currently restriced to alphanumeric

*/

function generatePassword($plength) {
	srand(make_seed());
	$alfa = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
	$token = "";
	for($i = 0; $i < $plength; $i ++) {
		$token .= $alfa[rand(0, strlen($alfa))];
	}
	return $token;
}

function make_seed() {
	list($usec, $sec) = explode(' ', microtime());
	return (float) $sec + ((float) $usec * 100000);
}	
	
?>