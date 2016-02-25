<?php

define('OneWeek', 604800);	// one week in seconds
define('OneDay',86400);		// one day in seconds

function timeStampToMySQLDate($ztamp) {
	// accept a unix timestamp and format it so that it can be inserted into the MySQL date field
	$format_3 = "Y-m-d";
	$o = date($format_3,$ztamp);
	return $o;
}


function MySQLDateToStamp($m) {
	$dateparts	= explode('-',$m);
	//$o			= makestamp($dateparts[0],$dateparts[1],$dateparts[2],0,0,0);
	$o = mktime(0,0,0,$dateparts[1],$dateparts[2],$dateparts[0]);
	return $o;
}

function StampToMySQL($ztamp) {
	$mysqltime = date ("Y-m-d H:i:s", $ztamp);
	return $mysqltime;
}

function StampToMySQLDate($ztamp) {
	$mysqltime = date ("Y-m-d", $ztamp);
	return $mysqltime;
}

function MySQLToPretty($m,$f) {
/*	accept a MySQL-formatted date sting and return a date formatted as specified
 *	@param	$m	str		MySQL date string
 *	@param	$f	str		date format as per http://php.ca/manual/en/function.date.php
 *	@note:	doesn't handle minutes, seconds, or hours since the MySQL Date field doesn't.
 */
	$ymd	= explode('-',$m);
	$z		= mktime(0,0,0,$ymd[1],$ymd[2],$ymd[0]);
	$o		= date($f,$z);
	return $o;
}

function getFirstMondayOf($d1) {
	//	accept a date and return first monday of that week
	//	@param		int		timestamp
	//	@return		int		timestamp representing monday 12:00:01am of that week
	$one_day = (int) (OneWeek / 7);
	$counter = 0;
	for ($i = $d1; date('N',$i) > 1; $i = $i +- $one_day) { 
		$counter++;
		$d2 = $i;
		if ($counter > 8) break; // avoid infinite loop
	}
	$d2 = $d2 +- $one_day;
	// subtract hour and minute values
	$d3 = mktime( 0,0,0,date('n',$d2),date('j',$d2),date('Y',$d2) );
	$d3 = $d3 + 1;
	return $d3;
}

function makestamp($dy=NULL,$dm=NULL,$dd=NULL,$th=NULL,$tm=NULL,$ts=NULL) {
	//	make a timestamp in a more sensical way than mktime()
	//	@params	all integers. all optional. From Year down to Second
	//	@return	int	timestamp
	//	goes like this: year,month,day,hour,minute,second
	if (is_null($dy)) $dy = (int) date('Y');
	if (is_null($dm)) $dm = (int) date('n');
	if (is_null($dd)) $dd = (int) date('j');
	if (is_null($th)) $th = (int) date('H');
	if (is_null($tm)) $tm = (int) date('i');
	if (is_null($ts)) $ts = (int) date('s');
	$o	= mktime($th,$tm,$ts,$dm,$dd,$dy);
	return $o;
}
?>