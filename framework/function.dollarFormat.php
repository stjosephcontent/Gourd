<?php
function dollarFormat($amount,$include_dollar_sign=true) {
	$o = '';
	if ($include_dollar_sign) $o .= '$';
	$o .= sprintf("%.2f",$amount);
	return $o;
}
?>