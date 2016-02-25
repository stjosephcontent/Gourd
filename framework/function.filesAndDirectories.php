<?php
/*
ensure all files in dir1 are also in dir2 and copy them over if necessary.
also copies from dir1->dir2 AND dir2->dir1

@param	string	$dir1	localpath
@param	string	$dir2	localpath

@return	true or false	[ currently just returns true. no error checking	]

*/
function ensureRedundancy($dir1,$dir2) {
	
	$filez1 	= listFilesIn($dir1);
	$filez2 	= listFilesIn($dir2);
	
	$extraz1	= array_diff($filez1,$filez2);
	$extraz2	= array_diff($filez2,$filez1);
	
	foreach ($extraz1 as $e) {
		$source_file	= $dir1 . $e;
		$dest_file		= $dir2 . $e;
		if (!is_dir($source_file))	copy($source_file,$dest_file);
	}
	
	foreach ($extraz2 as $e2) {
		$source_file	= $dir2 . $e2;
		$dest_file		= $dir1 . $e2;
		if (!is_dir($source_file))	copy($source_file,$dest_file);
	}
	
	return true;
	
}

function getExtension($filename) {
	$f	= explode('.',$filename);
	$r	= array_pop($f);
	unset($f);
	return $r;
}

/*
function removeItemsInThisArrayFromItemsInThisArray($targetarray,$filterarray) {
	$o = array();	
	return $o;
}
*/

function listFilesIn($dir,$opts=array()) {
	//	returns an array of all the files in the specified folder
	//	@param	$dir	str		(local path of folder in question)
	//	@return			array	(indexed array)
	$filez		= array();
	$dirHandle	= opendir($dir);
	$showdirs	= false;
	if (isset($opts['showdirs'])) $showdirs = (bool) $opts['showdirs'];
	while (false !== ($file = readdir($dirHandle))) {
		if((!is_dir($file) || $showdirs) && $file != '..' && $file != '.') $filez[] = $file;
	}
	closedir($dirHandle);
	
	//	options
	//if (isset($opts['exclude'])) $filez = removeItemsInThisArrayFromItemsInThisArray($filez,$opts['exclude']);
	
	return $filez;
}

function listDirectoriesIn($dir,$opts=array()) {
	$r = false;
	if ($h = opendir($dir)) {
		$r = array();
		while(false !== ($f = readdir($h))) {
			if (is_dir($f) && $f != '..' && $f != '.') $r[] = $f;
		}
		if (isset($opts['omit'])) {			
			$r = array_diff($r,$opts['omit']);
		}
	}
	
	return $r;
	
}


function breadth_first_file_search ( $root, $file, $callback = NULL, $omit = array() ) {
  $queue = array( rtrim( $root, '/' ).'/' ); // normalize all paths
  foreach ( $omit as &$path ) { // &$path Req. PHP ver 5.x and later
    $path = $root.trim( $path, '/' ).'/';
  }
  while ( $base = array_shift( $queue ) ) {
    $file_path = $base.$file;
    if ( file_exists( $file_path ) ) { // file found
      if ( is_callable( $callback ) ) {
        $callback( $file_path ); // callback => CONTINUE
      } else {
        return $file_path; // return file-path => EXIT
      }
    }
    if ( ( $handle = opendir( $base ) ) ) {
      while ( ( $child = readdir( $handle ) ) !== FALSE ) {
        if ( is_dir( $base.$child ) && $child != '.' && $child != '..' ) {
          $combined_path = $base.$child.'/';
          if ( !in_array( $combined_path, $omit ) ) {
            array_push( $queue, $combined_path);
          }
        }
      }
      closedir( $handle );
    } // else unable to open directory => NEXT CHILD
  }
  return FALSE; // end of tree, file not found
}

?>