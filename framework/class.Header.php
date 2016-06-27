<?php

define('BR',"\n");

//	minify must exist on the same domain as static files for this to work
define('ROOT_OFFSET',parse_url(STATIC_FILES_URLROOT, PHP_URL_PATH));

 class Header {
 
	private $body		 			= array();
	private $javascripts		 	= array();
	private $stylesheets			= array();
	public  $DoctypeDeclaration		= '<!DOCTYPE html>';
	public  $title					= '';
	public	$load_jquery			= false;
	public  $load_jqueryui			= false;
	public  $body_id				= '';
	public  $body_class				= '';
	public	$has_opengraph_data		= false;
	public  $lang					= 'en';	
	public  $jquery_location		= 'http://www.google.com/jsapi';
	public	$combine_css			= false;
	public  $combine_js				= false;
	public	$include_html5shiv		= true;

	private function addElement($area,$stuff) {
		if (!isset($this->body[$area])) $this->body[$area] = array();
		$this->body[$area][] = $stuff;
	}
	
	public function load_jquery($trueorfalse=true) {
		$this->load_jquery = $trueorfalse;
	}

	public function load_jqueryui($trueorfalse=true) {
		$this->load_jqueryui = $trueorfalse;
	}
	
	public function setDocType($string) {
		$lcase_shorthand = preg_replace('/\s\s+/', ' ', strtolower($string));
		$ o = '';
		switch ($lcase_shorthand) {
			case 'html4strict':
			$o .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
			break;
			case 'xhtml1.1':
			$o .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
			break;
			case 'xhtml1.0':
			$o .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
			break;
			case 'html5':
			$o .= '<!DOCTYPE html>';
			break;
			default:
			$o .= $string;
			break;
		}
		$this->DoctypeDeclaration = $o;
	}
	
	public function addmeta($stuff) {
		$this->addElement('meta',$stuff);
	}
	
	public function addog($property,$content) {
		$o = '<meta property="og:' . $property . '" content="' . $content .'" />';
		$this->addElement('meta',$o);
		$this->has_opengraph_data = true;
	}
	
	public function addcss($sheet) {
		//if (strpos($sheet,'/') === 0) $sheet = ROOT_OFFSET . $sheet;
		if ( (strpos($sheet,'http://') === false) && (strpos($sheet,'https://') === false) && $this->combine_css ) {
			$body_group = 'combined_css';
			//$sheet		= trim($sheet,'/');
		} else {
			$body_group = 'css';
		}
		$this->addElement($body_group,$sheet);
	}
	
	
	
	public function addjs($script) {
		//if (strpos($script,'/') === 0) $script = ROOT_OFFSET . $script;
		if ( (strpos($script,'http://') === false) && (strpos($script,'https://') === false) && $this->combine_js ) {
			$body_group = 'combined_js';
			//$script		= trim($script,'/');
		} else {
			$body_group = 'js';
		}
		$this->addElement($body_group,$script);	
	}
	
	public function addrawjs($javascript) {
		$this->addElement('rawjs',$javascript);
	}
	
	public function addrawcss($stuff) {
		$this->addElement('rawcss',$stuff);
	}
	
	public function addjqueryfile($file) {
		if (strlen(trim($file))) {
		
			if (parse_url($file,PHP_URL_SCHEME)) {
				$j = '$.getScript("' . $file .'");';
			} else {
				$j = '$.getScript("' . ROOT_OFFSET . $file .'");';
			}	
			$this->addElement('jquery',$j);
			$this->load_jquery = true;
		}
	}
	
	public function addjquery($stuff) {
		if (strlen(trim($stuff))) {
			$this->addElement('jquery',$stuff);
			$this->load_jquery = true;
		}
	}
	
	public function addonload($stuff) {
		$this->addElement('onload',$stuff);
	}
	
	public function buildhtml() {
		
		if ($this->load_jqueryui == true) $this->load_jquery = true;
		
		$o = '';
		$o  .= $this->DoctypeDeclaration . BR;
		
		if ($this->has_opengraph_data) {
		$o .='<html xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml">';
		} else {
		$o .='<html lang="' . $this->lang .'">';
		}
		$o .= BR;
		$o .= '<head>';
		$o .= BR;
		$o .= '<title>' . $this->title . '</title>';
		
		if (isset($this->body['meta'])) {
			$meta = array_filter(array_unique($this->body['meta']));
			foreach ($meta as $m) {
				$o .= BR;
				$o .= $m;
			}
		}
		
		if ($this->include_html5shiv) {
			$o .= BR;
			$o .= '<!--[if lt IE 9]>';
			$o .= BR;
			$o .= '<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>';
			$o .= BR;
			$o .= '<![endif]-->';
		}
		
		//	external css
		if (!empty($this->body['combined_css'])) {
			$bad_css 	= array_unique(array_filter($this->body['combined_css']));
			$good_css	= array();
			foreach ($bad_css as $b) {
				if (strpos($b,'/') === 0) $b = ROOT_OFFSET . $b;
				$good_css[] = rtrim($b,'/');
			}
			$url  = MINIFY_URLROOT . '/?f=';
			$url .= implode(',',$good_css);
			if (DEBUG == 'On') $url .= '&debug=1';
			$this->addElement('css',$url);
		}
		
		if (!empty($this->body['css'])) {
			$cssarray = array_unique(array_filter($this->body['css']));
			foreach ($cssarray as $css) {
				if (strpos($css,'/') === 0) $css = ROOT_OFFSET . $css;
				$o .= BR;
				$o .= '<link rel="stylesheet" href="' . $css .'" />';
			}
		}
		
		//	inline css
		if (!empty($this->body['rawcss'])) {
			$rawcssarray = array_filter(array_unique($this->body['rawcss']));
			if (!empty($rawcssarray)) {
				$o .= BR;
				$o .= '<style type="text/css">';
				foreach ($rawcssarray as $rawcss) {
					$o .= BR;
					$o .= $rawcss;	
				}
				$o .= BR;
				$o .= '</style>';
			}		
		}
	
		//	jquery
		if ($this->load_jquery) $o .= '<script type="text/javascript" src="' . $this->jquery_location . '"></script>';
		
		//	inline javascript
		if (!empty($this->body['jquery']) || !empty($this->body['rawjs']) || !empty($this->body['rawjs'])) {
			$o .= BR;
			$o .= '<script type="text/javascript">';
			$o .= BR;
			$o .= '//<![CDATA[';
			$o .= BR;
			if (isset($this->body['jquery'])) {
				
				$o .= 'google.load("jquery", "1.5");' . BR;
				
				if ($this->load_jqueryui) $o .= BR . 'google.load("jqueryui", "1");';
				
				$o .= BR . 'google.setOnLoadCallback(function() { ';
				
				$o .= BR . "\t" . '$(function() { ';
				
				foreach ($this->body['jquery'] as $jq) {
					$o .= BR . "\t\t";
					$o .= trim($jq);
				}
				$o .= BR . "\t" . '});' . BR . '});';
			}
			if (!empty($this->body['rawjs'])) {
				foreach ($this->body['rawjs'] as $rj) {
					$o .= BR;
					$o .= $rj;
				}
			}
			if (!empty($this->body['onload'])) {
				$o .= BR;
				$o .= 'function init() {';
				$o .= BR; 
				foreach ($this->body['onload'] as $ol) {
					$o .= BR;
					$o .= "\t" . $ol;
				}
				$o .= BR;
				$o .= '}';
			}
			$o .=  BR . '//]]>' . BR . '</script>';
		}

		// external javascript
		if (!empty($this->body['combined_js'])) {
			$jsarray = array_unique(array_filter($this->body['combined_js']));
			$good_js = array();
			foreach ($jsarray as $j) {
				if (strpos($j,'/') === 0) $j = ROOT_OFFSET . $j;
				$good_js[] = trim($j,'/');
			}
			$url = MINIFY_URLROOT . '/?f=' . implode(',',$good_js);
			if (DEBUG == 'On') $url .= '&debug=1';
			$this->addElement('js',$url);
		}
		if (!empty($this->body['js'])) {
			$jsarray = array_unique(array_filter($this->body['js']));
			foreach ($jsarray as $js) {
				$o .= BR;
				if (strpos($js,'/') === 0) $js = ROOT_OFFSET . $js;
				$o .= '<script type="text/javascript" src="' . $js .'"></script>';
			}
		}
	
		$o .= BR;
		$o .= '</head>';
		$o .= BR;
		
		$o .= '<body data-spy="scroll" data-target="#sidebar"';
		if (!strlen($this->body_id))	$o .=  ' id="' . $this->body_id . '"';
		if (!strlen($this->body_class)) $o .=  ' class="' . $this->body_class . '"';
		$o .= '>';
		$o .= BR;
	
	return $o;
	
	}
	0
	public function display() {
		$html = $this->buildhtml();
		echo $html;
	}
	
	public function spill() {
		$dbg = 'ROOT_OFFSET = ' . ROOT_OFFSET . ' & STATIC_FILES_URLROOT = ' . STATIC_FILES_URLROOT;
		$html = $this->buildhtml();
		return 'FOOOOOO ' . $dbg . $html;
	}
	
	public function display_pretty() {
		$html = $this->buildhtml();
		echo $this->prettify($html);
	}
	
	public function prettify($html) {
		$tidy	= new tidy;
		$config	= array(
			'indent'         	=> true,
			'new-inline-tags'	=> 'header,canvas,article',
			'output-xhtml'		=> true,
			'indent-attributes'	=> false,
			'wrap-attributes'	=> false,
			'indent-spaces'		=> 4,
			'tab-size'			=> 4,
			'wrap'				=> 0,
			'output-bom'		=> false,
			'doctype'			=> 'omit',
			'markup'			=> true
		);
		$tidy->parseString($html, $config, 'utf8');
		$tidy->cleanRepair();
		$harr	= explode('<!-- /hdr -->',$tidy);
		$hdr	= $this->DoctypeDeclaration . "\n" . $harr[0];
		return $hdr;
	}
	
	public function dbg() {
		$html = $this->buildhtml();
		//$prettyhtml = $this->prettify($html);
		$prettyhtml = $html;
		$o = '<pre>' . htmlspecialchars($prettyhtml) . '</pre>';
		echo $o;
	}
}
?>
