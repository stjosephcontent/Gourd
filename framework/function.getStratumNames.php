<?php
//	getStratumNames :: take an integer and return it's analagous name
//	@param	$s	int	(representing the stratum of the chunk)
//	@return str (the name corresponding to that int) - if there is no corresponding value it returns (string) $s


function getStratumName($s) {

	$ga = array('Alpha','Beta','Gamma','Delta','Epsilon','Zeta','Eta','Theta','Iota','Kappa','Lambda','Mu','Nu','Xi','Omicron','Pi','Rho','Sigma','Tau','Upsilon','Phi','Chi','Psi','Omega');
	
	if ($s > sizeof($ga)+-1) {
		$r = (string) $s;
	}
	elseif ($s < 0) {
		$r = 'Archive ('.-$s.')';
	}
	elseif ($s == 0) {
		$r = 'Live';
	}
	else {
		$r = $ga[$s];
	}
	return $r;
}

?>