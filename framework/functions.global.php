<?php

function localize($var,$mode='wimpy') {
	global $G;
	global $$var;
	$o = $$var;
	if (!isset($$var) || $mode == 'greedy') {
		// follow variables_order: "EGPCS"
		if (isset($_COOKIE[$var]))	{ $o = $_COOKIE[$var]; }
		if (isset($_POST[$var]))	{ $o = $_POST[$var]; }
		if (isset($_GET[$var]))		{ $o = $_GET[$var]; }
		if (isset($G[$var]))		{ $o = $G[$var]; }
		#if (isset($_SERVER[$var]))	{ $o = $_SERVER[$var]; }
	}
	return $o;
}
function getAddress() {
	// strip out real path. No args. also, not "/" at the beginning or end.
	// ex:	products/Kraft/KraftDinner
	$address	= $_SERVER['REQUEST_URI'];
	$x			= explode('?',$address);
	$address	= $x[0];
	$y			= explode('#',$address);
	$address	= $y[0];
	$pieces		= explode('/',$address);
	$pieces		= array_filter($pieces);
	$drop1st	= array_shift($pieces);
	$address	= trim($address,'/');
	return $address;
}


function __autoload($class_name) {
	// NEED TO FIX THIS
	if ($class_name == 'Translation_Entry') {
		$path1 = 'entry.php';
		require_once $path1;
	} else if ($class_name == 'Translations'){
		$path1 = 'translations.php';
		require_once $path1;
	} else if ($class_name == 'NOOP_Translations'){
		$path1 = 'translations.php';
		require_once $path1;
	} else if ($class_name == 'POMO_Reader'){
		$path1 = 'streams.php';
		require_once $path1;
	} else if ($class_name == 'POMO_FileReader'){
		$path1 = 'streams.php';
		require_once $path1;
	} else if ($class_name == 'POMO_StringReader'){
		$path1 = 'streams.php';
		require_once $path1;
	} else if ($class_name == 'POMO_CachedFileReader'){
		$path1 = 'streams.php';
		require_once $path1;
	} else if ($class_name == 'POMO_CachedIntFileReader'){
		$path1 = 'streams.php';
		require_once $path1;
	} else if ($class_name == 'MO'){
		$path1 = 'mo.php';
		require_once $path1;
	} else if ($class_name == 'wp_atom_server'){
		$path1 = 'pluggable-deprecated.php';
		require_once $path1;
	} else {
		$path1 = 'class.' . $class_name . '.php';
		//$path2 = strtolower($class_name) . '.class.php';
		//if (file_exists($path1)) {
		require_once $path1;
		//} else {
		//	require_once $path2;
		//}
	}
}

function load_function($func_name) {
	require_once 'function.' . $func_name . '.php';
}

function load_header() {
	global $header;
	if (! ($header instanceof Header)) $header = new Header;
}

function load_itemz() {
	global $itemz;
	if (! ($itemz instanceof Itemz)) $itemz = new Itemz;
}

function load_chunkz() {
	global $chunkz;
	if (! ($chunkz instanceof Chunkz)) $chunkz = new Chunkz;
}

function load_shoppe() {
	global $shoppe;
	if (! ($shoppe instanceof Shoppe)) $shoppe = new Shoppe;
}

function load_cms() {
	global $cms;
	if (! ($cms instanceof CMS)) $cms = new CMS;
}

function load_core() {
	global $core;
	if (! ($core instanceof Core)) $core = new Core;
}

function load_admin() {
	global $admin;
	if (! ($admin instanceof Admin)) $admin = new Admin;
}

function get_content($url)
//	when fopen() is restricted, this seems to work. And besides, cURL is better.
{
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_HEADER, 0);
    ob_start();
    curl_exec ($ch);
    curl_close ($ch);
    $string = ob_get_contents();
    ob_end_clean();
    return $string;
}

function remove_accent($str)  {
	$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
	$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
	return str_replace($a, $b, $str);
}

function SEOify($i){
	//	http://php.ca/manual/en/function.preg-replace.php#90316
	$o			= $i;
	$o			= htmlspecialchars_decode($o);
	$o			= remove_accent(trim($i));
	$o			= str_ireplace('/', ' ',$o);
	$o			= str_ireplace('\\',' ',$o);
	$o			= str_ireplace('(', ' ',$o);
	$o			= str_ireplace(')', ' ',$o);
	$o			= str_ireplace('[', ' ',$o);
	$o			= str_ireplace(']', ' ',$o);
	$o			= trim($o);
    $patterns   = array( "([\40])" , "([^a-zA-Z0-9_-])", "(-{2,})" );
    $replacers	= array("-", "", "-");
    $o			= preg_replace($patterns, $replacers, $o);
    return $o;
}

function prettify($html) {
	$tidy	= new tidy;
	$config	= array(
		'clean'				=> true,
		'indent'            => true,
		'new-inline-tags'	=> 'header,canvas,article',
		'vertical-space'	=> false,
		'wrap-php'			=> false,
		'output-xhtml'		=> true,
		'indent-attributes'	=> false,
		'wrap-attributes'	=> false,
		'wrap-sections'		=> false,
		'indent-spaces'		=> 4,
		'tab-size'			=> 4,
		'wrap'				=> 0,
		'output-bom'		=> false,
		'doctype'			=> 'omit',
		'markup'			=> true
	);
	$tidy->parseString($html, $config, 'utf8');
	$tidy->cleanRepair();
	return $tidy;
}

function minify($html) {
	$o = preg_replace('/\s\s+/', ' ', $html);
	$o = str_replace('> <','><',$o);
	return $o;
}

function increaseNumberOfColumns($n=1) {
	global $Page;
	$Page['numberofcolumns'] = $Page['numberofcolumns'] + $n;
}

function d($list) {
	// simple scalar debugger
	$things = func_get_args();
	$o  = '';
	$o .= '<div class="debug">d()</div>';
	$o .= '<ul>';
	foreach ($things as $n=>$v) {
		$o .= '<li><span class="varname">'.$n.'</span><span class="varval">'.$v.'</span></li>';
	}
	$o .= '</ul>';
	$o .= '</div>';
	return $o;
}

function z($p) {
    $o = STATIC_FILES_URLROOT . '/' . trim($p,'/');
    return $o;
}

?>
