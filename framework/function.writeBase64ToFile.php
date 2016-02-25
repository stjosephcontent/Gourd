<?php
function writeBase64ToFile( $imageData, $outputfile ) {
	/* read data (binary) */ 
	/*
	$ifp = fopen( $inputfile, "rb" ); 
	$imageData = fread( $ifp, filesize( $inputfile ) ); 
	fclose( $ifp ); 
	*/
	/* encode & write data (binary) */ 
	
	$ifp = fopen( $outputfile, "wb" ); 
	fwrite( $ifp, base64_decode( $imageData ) ); 
	fclose( $ifp ); 
	/* return output filename */ 
	return( $outputfile ); 
} 
?>