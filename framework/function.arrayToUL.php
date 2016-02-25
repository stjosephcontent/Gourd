<?php

function arrayToUL($arr) {
	//	takes a multidimensional array and returns a list like this:
	/*
	<ul>
		<li><strong>key one</strong>: value one</li>
		<li><strong>key two</strong>: value two</li>
		<li>
			<strong>group one</strong>:
			<ul>
				<li><strong>key A</strong>: value A</li>
				<li><strong>key B</strong>: value B</li>
			</ul>
		</li>
	</ul>
	*/
	
	$r = '<ul class="keyvaluepairs">';
	foreach ($arr as $k => $v) {
		if (is_scalar($v))	$r .= '<li><strong>' . str_ireplace('_',' ',$k) . '</strong>: ' . $v . '</li>';
		if (is_array($v))	$r .= '<li><strong>' . str_ireplace('_',' ',$k) . '</strong>: ' . arrayToUL($v) . '</li>';
	}
	$r .= '</ul>';
	return $r;
}

?>