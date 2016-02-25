<?php
function castAs($i,$type) {
	//	cast a value or just send it right back
	$o = $i;
	if ($type) eval('$o = ('.$type.') $o;');
	return $o;
}


function castArrayElementsAs($iarr,$type) {
	//	accept an array and then return the same array with all the values casted as $type
	$types	= array();
	$types	= array_pad($types, sizeof($iarr), $type);
	$r		= array_map('castAs',$iarr,$types);
	return $r;
}

?>
