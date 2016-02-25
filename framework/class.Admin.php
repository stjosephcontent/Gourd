<?php

function call_object_method_array($func, $obj, $params=false){
    if (!method_exists($obj,$func)){        
        // object doesn't have function, return null
        return (null);
    }
    // no params so just return function
    if (!$params){
        return ($obj->$func());
    }        
    // build eval string to execute function with parameters        
    $pstr='';
    $p=0;
    foreach ($params as $param){
        $pstr.=$p>0 ? ', ' : '';
        $pstr.='$params['.$p.']';
        $p++;
    }
    $evalstr='$retval=$obj->'.$func.'('.$pstr.');';
    $evalok=eval($evalstr);
    // if eval worked ok, return value returned by function
    if ($evalok){
        return ($retval);
    } else {
        return (null);
    }        
    return (null);   
}

require_once 'class.Core.php';

class Admin extends Core {

	public $UserID;

	public function connect2mysql() {
		$this->is_connected_to_mysql = false;
		$this->connect2mysql_db(MYSQLDB_ADMIN);
	}

	public function connect2mongo() {
		$this->connect2mongo_db(MONGODB_DB);
	}

	protected function get_modules($UserID) {
		$r = array();
		if ($UserID) {
			$this->connect2mysql();
			$r = $this->good_query_table("SELECT * FROM ModuleXUser WHERE CanRead = 1");
			return $r;
		}
	}

	public function __construct($UserID=NULL) {
		if ($UserID)							$this->UserID = $UserID;
		elseif (isset($_SESSION['CMSUser']))	$this->UserID = $_SESSION['CMSUser']['UserID'];
		
		$this->is_connected_to_mysql = false;
		$this->connect2mysql();
		
		/*
		if ($UserID)							$this->UserID = $UserID;
		elseif (isset($_SESSION['CMSUser']))	$this->UserID = $_SESSION['CMSUser']['UserID'];
		$this->Plane = 1;
		*/
	}

	public function getUserID() {
		return $this->UserID;
	}
	
	public function editUser($UserID,$f) {
		$this->connect2mysql();
		if ($upd = $this->mysqldb->query("UPDATE Users SET Username = '$f[Username]', Email = '$f[Email]', FullName = '$f[FullName]' WHERE UserID = $UserID")) { 
			$r = true;
		} else {
			$r = false;
		}
		return $r;
	}

	public function getUser($UserID) {
		$this->connect2mysql();
		if ($result = $this->mysqldb->query("SELECT * FROM Users WHERE UserID = $UserID")) { 
			$r = $result->fetch_assoc();
		} else {
			$r = false;
		}
		return $r;
	}

	public function logUserIn($Username,$Password) {
		$this->connect2mysql();
		if ($result = $this->mysqldb->query("SELECT UserID,Username,FullName FROM Users WHERE Username = '$Username' AND Password = '".md5($Password)."'")) {
			$r = $result->fetch_assoc();
		} else {
			$r = false;
		}
		$this->UserID = $r['UserID'];
		return $r;
	}
	
	public function logout() {
		$this->UserID = NULL;
		return !$this->UserID;
	}
	
	public function createUser($U) {
		$this->connect2mysql();
		$md5pass = md5($U['Password']);
		if ($ins = $this->mysqldb->query("INSERT INTO Users (Username,Password,Fullname,Email) VALUES ('$U[Username]','$md5pass','$U[Fullname]','$U[Email]')")) {
			$r = $this->mysqldb->insert_id;
		} else {
			$r = false;
		}
		return $r;
	}
	
	public function getAllUsers($order_by='UserID ASC') {
		$this->connect2mysql();
		if ($result = $this->mysqldb->query("SELECT * FROM Users ORDER BY $order_by")) {
			$r = array();
			while ($row = $result->fetch_assoc()) $r[] = $row;
		} else {
			$r = false;
		}
		return $r;
	}

	public function deleteUser($UserID) {
		$this->connect2mysql();
		if ($del = $this->mysqldb->query("DELETE FROM Users WHERE UserID = $UserID")) {
			$r = true;
		} else {
			$r = false;
		}
		return $r;	
	}

	public function getModule($ModuleID) {
		$this->connect2mysql();
		$r = $this->good_query_assoc("SELECT * FROM Modules WHERE ModuleID = '$ModuleID'");
		return $r;
	}

	public function getUserPermussionsForModule($UserID,$ModuleID) {
		$this->connect2mysql();
		if ($result = $this->mysqldb->query("SELECT * FROM ModuleXUser WHERE UserID = $UserID AND ModuleID = '$ModuleID'")) { 
			$r = $result->fetch_assoc();
		} else {
			$r = false;
		}
		return $r;
	}

	public function getUserPermissions($UserID) {
		$this->connect2mysql();
		$r = array();
		$up = "
		SELECT		mxu.ModuleID,mxu.CanRead,mxu.CanWrite,mxu.CanAlter,m.Title,m.Group,m.Target
		FROM		ModuleXUser AS mxu
		JOIN		Modules AS m
		ON			m.ModuleID = mxu.ModuleID
		WHERE		mxu.UserID = $UserID
		ORDER BY	m.Group ASC, m.Title ASC
		";
		if ($result = $this->mysqldb->query($up)) {
			while ($row = $result->fetch_assoc()) {
				if ($row['CanRead']) {
					$key1 = $row['Group'];
					$key2 = $row['ModuleID'];
					$r[$key1][$key2] = array('CanRead' => $row['CanRead'], 'CanWrite' => $row['CanWrite'], 'CanAlter' => $row['CanAlter'], 'Title' => $row['Title'], 'Target' => $row['Target']);
				}	
			}
		} else {
			$r = false;
		}
		return $r;
	}
	
	public function saveToSearch($item,$type,$omit_keys_also=array(),$test=false) {
		
		$r 					= false;
		$omit_keys_base		= array('_id','LastUpdated','FirstCreated','AuthorID','AuthorName','URL');
		$omit_keys			= array_merge($omit_keys_also,$omit_keys_base);
		$GLOBALS['omit_keys'] = $omit_keys;
		global $omit_keys;
		$efile				= file_get_contents('/var/www/data/stopwords_english.txt');
		$english_stop_words	= explode("\n",$efile);
		$GLOBALS['english_stop_words'] = $english_stop_words;

		$good_words = array();
		$GLOBALS['good_words'] = $good_words;
		
		if (!function_exists('longEnough')) {
			function longEnough($i) {
				$minlength = 3;
				$r = true;
				if (strlen($i) < $minlength) $r =false;
				return $r;
			}
		}		
		
		if (!function_exists('extract_words')) {
			function extract_words($item, $key) {
				global $omit_keys;
				global $english_stop_words;
				if (!in_array($key,$omit_keys)) {
					$item		= str_ireplace("\n\r",' ',$item);
					$item		= str_ireplace("\n",' ',$item);
					$item		= str_ireplace('><','> <',$item);
					$werds		= explode(' ',strtolower(str_replace('-',' ',strip_tags($item))));
					$werds		= array_map('SEOify',$werds);
					$werds		= array_filter($werds,'longEnough');
					$werds		= array_diff($werds,$english_stop_words);
					$GLOBALS['good_words'] = array_merge($GLOBALS['good_words'],$werds);
				}
			}
		}
		
		array_walk_recursive($item,'extract_words');
		$GLOBALS['good_words']	= array_filter($GLOBALS['good_words']);
		$GLOBALS['good_words']	= array_unique($GLOBALS['good_words']);
		$GLOBALS['good_words']  = array_values($GLOBALS['good_words']);

		if (is_array($GLOBALS['good_words'])) {
			$r = array(
				'_id'	=>	$item['_id'],
				'type'	=>	$type,
				'words'	=>	array_values($GLOBALS['good_words'])
			);
			if (!$test) {
				$this->connect2mongo();
				$this->mongodb->Search->save($r);
			}	
		} else {
			$r = false;
		}
		return $r;
	}
	
	public function removeFromSearch($id) {
		if (is_array($id)) $id = $id['_id'];
		$this->connect2mongo();
		return $this->mongodb->Search->remove(array('_id' => $id));
	}
	
	public function updateUserPermissions($UserID,$perms) {
		$this->connect2mysql();
		$del = $this->mysqldb->query("DELETE FROM ModuleXUser WHERE UserID = $UserID");
		$stmt = $this->mysqldb->prepare("INSERT INTO ModuleXUser (UserID,ModuleID,CanRead,CanWrite,CanAlter) VALUES(?,?,?,?,?)");
		foreach ($perms as $module_id => $plevel) {
			$CanRead	= (bool) ($plevel > 0);
			$CanWrite	= (bool) ($plevel > 1);
			$CanAlter	= (bool) ($plevel > 2);
			$stmt->bind_param("isiii", $UserID, $module_id, $CanRead, $CanWrite, $CanAlter);
			$stmt->execute();
		}
		$r = $stmt->affected_rows;
		$stmt->close();
		return $r;
	}
	
	public function getAvailableModules() {
		$r = false;
		$this->connect2mysql();
		$modules = $this->good_query_table("SELECT * FROM Modules");
		foreach ($modules as $module) {
			extract($module);
			$r[$Group][$ModuleID] = $Title;
		}
		return $r;
	}
	
	public function repopulateURI($URI) {
		$go = $this->deleteURI($URI['id'],$URI['ResourceType'],$boundFor);
		if (strlen($URI['URN_en'])) {
			$go = $this->addURI($URI,$boundFor);
		}
		return $go;
	}

	public function deleteURI($id,$ResourceType) {
		//	you really shoudn't need a ResourceType
		$r = $this->good_query("DELETE FROM URIs WHERE id = '$id' AND ResourceType = '$ResourceType'");
		return $r;
	}
	
	public function addURI($URI,$Plane=0) {
		extract($URI);
		$r = $this->good_query("INSERT INTO URIs (URN_en,URN_fr,ResourceType,id,Plane,ztamp) VALUES ('$URN_en','$URN_fr','$ResourceType','$id',$Plane,NOW())");
		if ($this->mysqldb->error) {
			$r = 'Error: ' . $this->mysqldb->error;
		} else {
			$r = true;
		}
		return $r;
	}

}
?>