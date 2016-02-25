<?php
/*
 *	make sure $section_id, $section_title, $page_id, and $page_title are always set
 *
 *	may call function.characterConversions.php if it needs to "sanitize" strings
 *
**/

if (isset($page_title) && !isset($page_id)) {
	$page_id 		= sanitize($page_title);
}

if (!isset($page_title) && isset($page_id)) {
	$page_title 	= glamourize($page_id);
}

if (isset($section_title) && !isset($section_id)) {
	$section_id 	= sanitize($section_title);
}

if (!isset($section_title) && isset($section_id)) {
	$section_title 	= glamourize($section_id);
}

if (isset($page_title) && !isset($section_title)) {
	$section_title	= $page_title;
	$section_id		= $page_id;
}

if (!isset($page_title) && isset($section_title)) {
	$page_title 	= $section_title;
	$page_id		= $section_id;
}

if (!isset($page_title)) {
	// if nothing at all was declared, create variables based on the name of the file
	$s				= $_SERVER["PHP_SELF"];
	$s				= trim($s,'/');
	$s				= str_replace('/',' • ',$s);
	$s				= str_replace('.php','',$s);
	$page_title		= glamourize($s);
	$page_id		= sanitize($s);
	$section_title 	= $page_title;
	$section_id		= $page_id;
}

function sanitize($i) {
	require_once 'function.characterConversions.php';
	$o 				= trim($i);
	$o 				= strtolower($o);
	$o 				= str_replace(' ','_',$o);
	$o 				= LegalizeText($o);
	return $o;
}

function glamourize($i) {
	$o = $i;
	$o = str_replace('_',' ',$o);
	$o = ucwords($o);
	return $o;
}

function var_name (&$iVar, &$aDefinedVars)
//	@note:		the second parameter must always be get_defined_vars()
//	@example:	var_name($iVar, get_defined_vars());
    {
    foreach ($aDefinedVars as $k=>$v)
        $aDefinedVars_0[$k] = $v;

    $iVarSave = $iVar;
    $iVar     =!$iVar;

    $aDiffKeys = array_keys (array_diff_assoc ($aDefinedVars_0, $aDefinedVars));
    $iVar      = $iVarSave;

    return $aDiffKeys[0];
    }

?>