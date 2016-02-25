<?php
//	take an address and try to return an array containing lat / long
//	return false on fail
function getLatLong($address,$region='ca') {
	usleep(20000);
	$endpoint	= 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false&region='.$region.'&address=';		
	//	if $address includes a whitespace, we assume it is not urlencoded. if not, not.
	$address 	= trim($address);
	if (strpos($address,' ') !== false) $address = urlencode($address);
	$call 		= file_get_contents($endpoint . $address);
	$result 	= json_decode($call,true);
	if ($result['status'] == 'OK') {
		$faddr	= $lat	= $result['results'][0]['formatted_address'];
		$lat	= $result['results'][0]['geometry']['location']['lat'];
		$lng	= $result['results'][0]['geometry']['location']['lng'];
		$r		= array('Latitude' => $lat, 'Longitude' => $lng, 'FormattedAddress' => $faddr, 'status' => 'OK', 'request' => $address);
	} else {
		$r 		= false;
	}
	return $r;
}
?>