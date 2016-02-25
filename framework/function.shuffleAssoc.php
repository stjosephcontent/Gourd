<?php
function shuffleAssoc($list) {
	#	shuffle an associate array (preserve the keys, but change the order in which they appear)
	if (!is_array($list)) return $list; 
	$keys = array_keys($list); 
	shuffle($keys); 
	$random = array(); 
	foreach ($keys as $key) $random[$key] = $list[$key]; 				
	return $random; 
}
?>
 