<?php

# $url			= explode('/',$_SERVER['REDIRECT_URL']);
# $originalfile	= array_pop($url);
# $resizeto		= strtolower(array_pop($url));
# $sourcefile		= $root_dir.'/'.$originalfile;
# $destfile		= $root_dir.'/size/'.$resizeto.'/'.$originalfile;

//$r			= compact('resizeto','sourcefile','destfile','destfilename','destdir');



function get_extention($str) {
	$arr = explode('.',$str);
	return array_pop($arr);
}

function resizeSaveAndOutputImage($sourcefile,$destfile,$newwidth,$newheight,$resizelongestsideto=0,$resizeshortestsideto=0,$pad=0) {
		//	create image
		$thumb = new Imagick();
		$thumb->readImage($_SERVER["DOCUMENT_ROOT"] . '/' . $sourcefile);
		
		if (($resizelongestsideto + $resizeshortestsideto > 0) && ($resizelongestsideto * $resizeshortestsideto == 0)) {
		//	if either $resizelongestsideto or $resizeshortestsideto has been specified
			$sourceheight	= $thumb->getImageHeight(); 
			$sourcewidth	= $thumb->getImageWidth();
			$destdim		= $resizelongestsideto + $resizeshortestsideto;
			if ( ($resizelongestsideto > 0 && ($sourcewidth > $sourceheight)) || ($resizeshortestsideto > 0 && ($sourcewidth < $sourceheight))) {
				//	resize along width
				$newwidth	= $destdim;
				$newheight	= 0;
			} else {
				//	resize along height
				$newheight	= $destdim;
				$newwidth	= 0;
			}
		
		} elseif ($pad > 0) {
		
			$sourceheight	= $thumb->getImageHeight(); 
			$sourcewidth	= $thumb->getImageWidth();
			if		($sourceheight > $sourcewidth) $thumb->borderImage('#FFFFFF',(int) ($sourceheight+-$sourcewidth)/2,0);
			elseif	($sourceheight < $sourcewidth) $thumb->borderImage('#FFFFFF',0,(int) ($sourcewidth+-$sourceheight)/2);
			$newwidth		= $pad;
			$newheight		= $pad;
			
		} elseif ($resizelongestsideto * $resizeshortestsideto > 0) {
		//	$resizelongestsideto AND $resizeshortestsideto has been sent. WTF!?
			throw new Exception('Illegal parameters');
		}
		
		//	for speed vs quality see http://php.ca/manual/en/function.imagick-resizeimage.php
		//$thumb->resizeImage($newwidth,$newheight,Imagick::FILTER_CATROM,1);	//	This one was too poor
		$thumb->resizeImage($newwidth,$newheight,Imagick::FILTER_LANCZOS,0.8);
		
		//	save and output
		$thumb->writeImage($_SERVER["DOCUMENT_ROOT"] . '/' . $destfile);
		$output		= $thumb->getimageblob();
		$outputtype = $thumb->getFormat();
		header("Content-type: $outputtype");
		echo $output;
		//	cleanup
		$thumb->clear();
		$thumb->destroy();
}

function MkdirIfNotExists($destdir) {
	if (!is_dir($_SERVER["DOCUMENT_ROOT"] . '/' . $destdir)) mkdir($_SERVER["DOCUMENT_ROOT"] . '/' . $destdir,0777,true);
}

if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/' . $sourcefile)) {
	
	$matches_nxn = preg_match('/^[0-9]+x[0-9]+$/',$resizeto);	//	ex: 300x450
	$matches_wnn = preg_match('/^w[0-9]+$/',$resizeto);			//	ex: w180
	$matches_hnn = preg_match('/^h[0-9]+$/',$resizeto);			//	ex: h500
	$matches_snn = preg_match('/^s[0-9]+$/',$resizeto);			//	ex: s500	(s for shortest.resize along the shortest dimension to n)
	$matches_lnn = preg_match('/^l[0-9]+$/',$resizeto);			//	ex: l500	(l for longest.	resize along the longest dimension to n)
	$matches_pnn = preg_match('/^p[0-9]+$/',$resizeto);			//	ex: p150	(p for pad. add white pixels)
	
	if ( ($matches_nxn + $matches_wnn + $matches_hnn + $matches_snn + $matches_lnn + $matches_pnn) != 1) {
	
		throw new Exception('i could`t figure our what to do based on the URL.');
	
	} elseif ($matches_pnn) {
	
		$newdim = str_replace('p','',$resizeto);
		MkdirIfNotExists($destdir);
		resizeSaveAndOutputImage($sourcefile,$destfile,0,0,0,0,$newdim);
	
	} elseif ($matches_nxn) {
	
		list($newwidth, $newheight) = explode('x',$resizeto);
		MkdirIfNotExists($destdir);
		resizeSaveAndOutputImage($sourcefile,$destfile,$newwidth,$newheight);
		
	} elseif ($matches_wnn) {
		
		$newwidth = str_replace('w','',$resizeto);
		MkdirIfNotExists($destdir);
		resizeSaveAndOutputImage($sourcefile,$destfile,$newwidth,0);
	
	} elseif ($matches_hnn) {

		$newheight = str_replace('h','',$resizeto);
		MkdirIfNotExists($destdir);
		resizeSaveAndOutputImage($sourcefile,$destfile,0,$newheight);
	
	} elseif ($matches_snn) {
	
		$newdim	 = str_replace('s','',$resizeto);
		MkdirIfNotExists($destdir);
		resizeSaveAndOutputImage($sourcefile,$destfile,0,0,0,$newdim);
	
	} elseif ($matches_lnn) {
	
		$newdim	 = str_replace('l','',$resizeto);
		MkdirIfNotExists($destdir);
		resizeSaveAndOutputImage($sourcefile,$destfile,0,0,$newdim);
	
	} else {
		
		throw new Exception('The impossible has happened. Please email God.');
	}
	
} elseif (isset($failfile) && file_exists($_SERVER["DOCUMENT_ROOT"] . '/' . $failfile)) {	
	
	/*
	$fail = new Imagick();
	$fail->readImage($_SERVER["DOCUMENT_ROOT"] . '/' . $failfile);
	$output		= $fail->getimageblob();
	$outputtype = $fail->getFormat();
	header("Content-type: $outputtype");
	echo $output;
	//	cleanup
	$fail->clear();
	$fail->destroy();
	*/
	
	$redir = STATIC_FILES_URLROOT . '/size/' . $resizeto . '/' . $failfile;
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	header ("Location: $redir"); 
	
	
} else {

	throw new Exception('File doesn`t exist');
}
?>