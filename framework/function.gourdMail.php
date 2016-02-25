<?php
function gourdMail($from,$to,$subject,$body) {
	/*
	@description:	uses Amazon SES to send the most basic bare-bones type email. Designed for quick deployment
	@param string $from 	:: like this: "Mister Rogers <mrogers@nbc.com>"
	@param string $to		:: like this: "Sarah Jane <sjane@hotmail.com>"
	@param string $subject	:: like this: "Notice of Renewal"
	@param string $body		:: like this: "<p>Hi Sarah,</p><p>We are writing to tell you you are <strong>amazing!</strong></p>"
	*/
	require_once 'AWSSDKforPHP/sdk.class.php';
	$ses = new AmazonSES();
	$message = array(
		'Subject'	=> array('Data' => $subject),
		'Body'		=> array('Html'	=> array('Data' => $body),'Text' => array('Data' => strip_tags($body)))
	);	
	$r = $ses->send_email('daemon@gourdisgood.com', array('ToAddresses' => $to), $message, $opt = null);
	spl_autoload_register('__autoload');
	return $r;
}
?>