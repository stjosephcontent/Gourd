<?php

class Shoppe extends Itemz {
	
	public $admin;
	
	public function __construct() {
		$UserID = NULL;
		if (isset($_SESSION['CMSUser'])) $UserID = $_SESSION['CMSUser']['UserID'];
		$this->admin = new Admin($UserID);
		$this->connect2mysql();
		$this->connect2mongo();
	}
	
	public function __call($method,$args) {
		if (!method_exists($this,$method)) {
			if (method_exists($this->admin,$method)) {
				return call_object_method_array($method, $this->admin, $args);
			}
		}
	}
	
	public function __get($varname) {	
		if (!property_exists($this,$varname)) {
			if(property_exists($this->admin,$varname)) return $this->admin->$varname;
		}
    }
}
?>