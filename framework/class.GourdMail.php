<?php

require_once 'AWSSDKforPHP/sdk.class.php';

//$ses = new AmazonSES();

//spl_autoload_register('__autoload');

class GourdMail extends AmazonSES {

	public function go($from,$to,$subject,$body) {
		
		$message = array(
			'Subject'	=> array('Data' => $subject),
			'Body'		=> array('Html'	=> array('Data' => $body),'Text' => array('Data' => strip_tags($body)))	
		);
		
		return $this->send_email($from, array('ToAddresses' => $to), $message);
	
	}

}

?>