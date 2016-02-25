<?php
/* takes the full path of an image and creates a new one in a specified directory
*
* @param:	originalfile	: string. full path of original file
* @param:	newfile			: string. full path again.
* @param:	newwidth		: integer.
* @param:	type			: string. must be in the format returned by $_FILES['formfield']['type'] (ie: 'image/jpeg')
* @returns:	nothing
*
* NOTE: allowed types: jpg,gif,png. only really tested with jpg. bmp not supported.
* 
*
*/


function resizeImageAlongShortestDimension($originalfile, $newfile, $newvalue, $type) {
	
	try {
	
		list($originalwidth, $originalheight) = getimagesize($originalfile);
		if ($originalwidth > $originalheight) {
			$r = resizeImageToHeight($originalfile, $newfile, $newvalue, $type);
		} else {
			$r = resizeImageToWidth($originalfile, $newfile, $newvalue, $type);
		}
	
	return $r;	
	
	} catch (Exception $e) {
		echo 'function.resizeImage :: Caught exception: ',  $e->getMessage(), "\n";
		return false;
	}
}

function resizeImageAlongLongestDimension($originalfile, $newfile, $newvalue, $type) {
	
	try {
	
		list($originalwidth, $originalheight) = getimagesize($originalfile);
		if ($originalwidth < $originalheight) {
			$r = resizeImageToHeight($originalfile, $newfile, $newvalue, $type);
		} else {
			$r = resizeImageToWidth($originalfile, $newfile, $newvalue, $type);
		}
	
	return $r;	
	
	} catch (Exception $e) {
		echo 'function.resizeImage :: Caught exception: ',  $e->getMessage(), "\n";
		return false;
	}
}


function resizeImageToWidth($originalfile, $newfile, $newwidth, $type) {
	
	try {
	
		list($originalwidth, $originalheight) = getimagesize($originalfile);
		$diff = $originalwidth / $newwidth;
		$newheight = $originalheight / $diff;
		$tn = imagecreatetruecolor($newwidth, $newheight);
		
		switch ($type) {
			
			case 'image/gif':
			$image = imagecreatefromgif($originalfile);
			imagecopyresampled($tn, $image, 0, 0, 0, 0, $newwidth, $newheight, $originalwidth, $originalheight);
			imagegif($tn, $newfile, 100);
			break;
			
			case 'image/png':
			$image = imagecreatefrompng($originalfile);
			imagecopyresampled($tn, $image, 0, 0, 0, 0, $newwidth, $newheight, $originalwidth, $originalheight);
			imagepng($tn, $newfile, 100);
			break;
			
			default: // assume it's jpeg
			$image = imagecreatefromjpeg($originalfile);
			imagecopyresampled($tn, $image, 0, 0, 0, 0, $newwidth, $newheight, $originalwidth, $originalheight);
			imagejpeg($tn, $newfile, 100);
			break;
		
		}
		
		imagedestroy($tn);
		return true;
	
	} catch (Exception $e) {
    	echo 'function.resizeImage :: Caught exception: ',  $e->getMessage(), "\n";
    	return false;
	}
}


function resizeImageToHeight($originalfile, $newfile, $newheight, $type) {
	
	try {
	
		list($originalwidth, $originalheight) = getimagesize($originalfile);
		$diff = $originalheight / $newheight;
		$newwidth = $originalwidth / $diff;
		$tn = imagecreatetruecolor($newwidth, $newheight);
		
		switch ($type) {
			
			case 'image/gif':
			$image = imagecreatefromgif($originalfile);
			imagecopyresampled($tn, $image, 0, 0, 0, 0, $newwidth, $newheight, $originalwidth, $originalheight);
			imagegif($tn, $newfile, 100);
			break;
			
			case 'image/png':
			$image = imagecreatefrompng($originalfile);
			imagecopyresampled($tn, $image, 0, 0, 0, 0, $newwidth, $newheight, $originalwidth, $originalheight);
			imagepng($tn, $newfile, 100);
			break;
			
			default: // assume it's jpeg
			$image = imagecreatefromjpeg($originalfile);
			imagecopyresampled($tn, $image, 0, 0, 0, 0, $newwidth, $newheight, $originalwidth, $originalheight);
			imagejpeg($tn, $newfile, 100);
			break;
		
		}
		
		imagedestroy($tn);
		return true;
	
	} catch (Exception $e) {
    	echo 'function.resizeImage :: Caught exception: ',  $e->getMessage(), "\n";
    	return false;
	}
}


function resizeImageTo($originalfile, $newfile, $newwidth, $newheight, $type) {
	
	try {
	
		list($originalwidth, $originalheight) = getimagesize($originalfile);
		//$diff = $originalwidth / $newwidth;
		//$newheight = $originalheight / $diff;
		$tn = imagecreatetruecolor($newwidth, $newheight);
		
		switch ($type) {
			
			case 'image/gif':
			$image = imagecreatefromgif($originalfile);
			imagecopyresampled($tn, $image, 0, 0, 0, 0, $newwidth, $newheight, $originalwidth, $originalheight);
			imagegif($tn, $newfile, 100);
			break;
			
			case 'image/png':
			$image = imagecreatefrompng($originalfile);
			imagealphablending($image, true); // setting alpha blending on
			imagesavealpha($image, true);		// save alphablending setting (important)	
			imagecopyresampled($tn, $image, 0, 0, 0, 0, $newwidth, $newheight, $originalwidth, $originalheight);
			imagepng($tn, $newfile, 100);	
			break;
			
			default: // assume it's jpeg
			$image = imagecreatefromjpeg($originalfile);
			imagecopyresampled($tn, $image, 0, 0, 0, 0, $newwidth, $newheight, $originalwidth, $originalheight);
			imagejpeg($tn, $newfile, 100);
			break;
		
		}
		
		imagedestroy($tn);
		return true;
	
	} catch (Exception $e) {
    	echo 'function.resizeImage :: Caught exception: ',  $e->getMessage(), "\n";
    	return false;
	}
}


?>