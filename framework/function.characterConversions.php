<?php
function convertLatin1ToHtml($str) {
    $html_entities = array (
        "&" =>  "&amp;",		#ampersand  
        "á" =>  "&aacute;",     #latin small letter a
        "Â" =>  "&Acirc;",     	#latin capital letter A
        "â" =>  "&acirc;",     	#latin small letter a
        "Æ" =>  "&AElig;",     	#latin capital letter AE
        "æ" =>  "&aelig;",     	#latin small letter ae
        "À" =>  "&Agrave;",     #latin capital letter A
        "à" =>  "&agrave;",     #latin small letter a
        "Å" =>  "&Aring;",     	#latin capital letter A
        "å" =>  "&aring;",     	#latin small letter a
        "Ã" =>  "&Atilde;",     #latin capital letter A
        "ã" =>  "&atilde;",     #latin small letter a
        "Ä" =>  "&Auml;",     	#latin capital letter A
        "ä" =>  "&auml;",     	#latin small letter a
        "Ç" =>  "&Ccedil;",     #latin capital letter C
        "ç" =>  "&ccedil;",     #latin small letter c
        "É" =>  "&Eacute;",     #latin capital letter E
        "é" =>  "&eacute;",     #latin small letter e
        "Ê" =>  "&Ecirc;",     	#latin capital letter E
        "ê" =>  "&ecirc;",     	#latin small letter e
        "È" =>  "&Egrave;",     #latin capital letter E
        "û" =>  "&ucirc;",     	#latin small letter u
        "Ù" =>  "&Ugrave;",     #latin capital letter U
        "ù" =>  "&ugrave;",     #latin small letter u
        "Ü" =>  "&Uuml;",     	#latin capital letter U
        "ü" =>  "&uuml;",     	#latin small letter u
        "Ý" =>  "&Yacute;",     #latin capital letter Y
        "ý" =>  "&yacute;",     #latin small letter y
        "ÿ" =>  "&yuml;",     	#latin small letter y
        "Ÿ" =>  "&Yuml;",     	#latin capital letter Y
    );

    foreach ($html_entities as $key => $value) {
        $str = str_replace($key, $value, $str);
    }
    return $str;
}


function get_html_translation_table_CP1252() {
    $trans = get_html_translation_table(HTML_ENTITIES);
    $trans[chr(130)] = '&sbquo;';	// Single Low-9 Quotation Mark
    $trans[chr(131)] = '&fnof;';    // Latin Small Letter F With Hook
    $trans[chr(132)] = '&bdquo;';   // Double Low-9 Quotation Mark
    $trans[chr(133)] = '&hellip;';  // Horizontal Ellipsis
    $trans[chr(134)] = '&dagger;';	// Dagger
    $trans[chr(135)] = '&Dagger;';	// Double Dagger
    $trans[chr(136)] = '&circ;';    // Modifier Letter Circumflex Accent
    $trans[chr(137)] = '&permil;';	// Per Mille Sign
    $trans[chr(138)] = '&Scaron;';	// Latin Capital Letter S With Caron
    $trans[chr(139)] = '&lsaquo;';	// Single Left-Pointing Angle Quotation Mark
    $trans[chr(140)] = '&OElig;';	// Latin Capital Ligature OE
    $trans[chr(145)] = '&lsquo;';	// Left Single Quotation Mark
    $trans[chr(146)] = '&rsquo;';	// Right Single Quotation Mark
    $trans[chr(147)] = '&ldquo;';	// Left Double Quotation Mark
    $trans[chr(148)] = '&rdquo;';	// Right Double Quotation Mark
    $trans[chr(149)] = '&bull;';    // Bullet
    $trans[chr(150)] = '&ndash;';	// En Dash
    $trans[chr(151)] = '&mdash;';	// Em Dash
    $trans[chr(152)] = '&tilde;';	// Small Tilde
    $trans[chr(153)] = '&trade;';	// Trade Mark Sign
    $trans[chr(154)] = '&scaron;';	// Latin Small Letter S With Caron
    $trans[chr(155)] = '&rsaquo;';	// Single Right-Pointing Angle Quotation Mark
    $trans[chr(156)] = '&oelig;';	// Latin Small Ligature OE
    $trans[chr(159)] = '&Yuml;';	// Latin Capital Letter Y With Diaeresis
    ksort($trans);
    return $trans;
}


function super_clean($i) {
	$trans = get_html_translation_table_CP1252(HTML_ENTITIES);
	$o = strtr($i, $trans);
	return $o;
}

function LegalizeText($text) {
	$text = str_replace(' ', '_', trim($text));
	// strip out everything but alphanumeric and underlines
	//	change: added dash
	$clean = preg_replace("[^A-Za-z0-9\_-]", "", $text);
	$clean = str_replace('___', '_', $clean);
	$clean = str_replace('__', '_', $clean);
	$clean = str_replace('__', '_', $clean);
	return $clean;
}

function LegalizeTextSEO($text) {

	$clean = $text;
	//$clean = str_replace('_','-',$clean);
	$clean = LegalizeText($clean);
	$clean = str_replace('_','-',$clean);
	$clean = str_replace('---', '-', $clean);
	$clean = str_replace('--', '-', $clean);
	$clean = str_replace('--', '-', $clean);
	return $clean;

}

// Fixes the encoding to uf8 
function fixEncoding($in_str) 
{ 
  $cur_encoding = mb_detect_encoding($in_str) ; 
  if($cur_encoding == "UTF-8" && mb_check_encoding($in_str,"UTF-8")) 
    return $in_str; 
  else 
    return utf8_encode($in_str); 
} // fixEncoding 

?>