<?php

function cleanForDBRecursively($i) {
	$o = $i;
	if (is_scalar($o)) {
		if ( get_magic_quotes_gpc() ) $o = stripslashes($o);
		$o = mysql_real_escape_string($o);
	} else {
		$o = array_map('cleanForDBRecursively',$i);
	}
	return $o;
}

function cleanForMongo($i) {
	#	take an n-dimensional array and return that array with any keys beginning with $ stripped
	#	note: i have no idea how this handles objects. It's meant for arrays of scalars
	$o = array();
	foreach ($i as $k => $v) {
		if (strpos($k,'$') !== 0) {
			if (is_scalar($v)) {
				$o[$k] = $v;
			} else {
				$o[$k] = cleanForMongo($i[$k]);
			}
		}
	}
	return $o;
}

class Core {

	public $mysqldb;
	public $mongo_conn;
	public $mongodb;
	public $is_connected_to_mysql = false;
	public $is_connected_to_mongo = false;
	
	public function connect2mysql_db($db) {
		if (!$this->is_connected_to_mysql) {
			$mysql_conn		= new mysqli(MYSQLDB_SERVER,MYSQLDB_USER,MYSQLDB_PASSWORD,$db);
			$this->mysqldb	= $mysql_conn;
			$this->is_connected_to_mysql= true;
			return $mysql_conn;
		}
	}
	
	public function connect2mongo_db($db) {
		if (!$this->is_connected_to_mongo) {
			try {
				if (defined('MONGODB_USER')) {
					$opts = array();
					if ( defined('MONGODB_REPLICA_01') ) {
						$this->mongo_conn = new MongoClient("mongodb://" . MONGODB_USER . ":" . MONGODB_PASSWD . "@" . MONGODB_SERVER . ":" . MONGODB_PORT . ',' . MONGODB_REPLICA_01 . ":" . MONGODB_PORT . '/' . $db,$opts);
					} else {
						$this->mongo_conn = new MongoClient("mongodb://" . MONGODB_USER . ":" . MONGODB_PASSWD . "@" . MONGODB_SERVER . ":" . MONGODB_PORT . '/' . $db,$opts);
					}
				} else {
					$this->mongo_conn	= new MongoClient(MONGODB_SERVER.':'.MONGODB_PORT);
				}
				$this->mongodb = $this->mongo_conn->selectDB($db);
				if ( defined('MONGODB_REPLICA_01') ) {
					$this->mongodb->setReadPreference(MongoClient::RP_PRIMARY_PREFERRED);
				}
				$this->is_connected_to_mongo= true;
				return $this->mongodb;
			} catch (Exception $e) {
				$this->is_connected_to_mongo= false;
				echo 'Connection Error';
			}
		}	
	}
	
	public function connect2mongo() {
		$this->connect2mongo_db(MONGODB_DB);
	}
	
	public function connect2mysql() {
		//	we are assuming stacks because we are assuming URIs live in stacks
		$this->connect2mysql_db(MYSQLDB_STACKS);
	}
	
	public function sql() {
		$this->connect2mysql();
		return $this->mysqldb;
	}
	
	public function mong() {
		$this->connect2mongo();
		return $this->mongodb;
	}
	
	public function sanitize($i) {
		$o = cleanForDBRecursively($i);
		return $o;
	}
	
	public function disconnectmysql() {
		if ($this->is_connected_to_mysql) $this->mysqldb->close();
		$this->is_connected_to_mysql = false;
	}
	
	public function disconnectmongo() {
		if ($this->is_connected_to_mongo) $this->mongo_conn->close();
		$this->is_connected_to_mongo = false;
	}
	
	public function __construct() {
		/*
		$this->connect2mysql();
		$this->connect2mongo();
		$this->is_connected_to_mongo = true;
		$this->is_connected_to_mysql = true;
		*/
	}
	
	public function __destruct() {
		$this->disconnectmysql();
		$this->disconnectmongo();
	}

	public function good_query_assoc($sql) {
		$r = false;
		$this->connect2mysql();
		if ($result = $this->mysqldb->query($sql)) {
			$r = $result->fetch_assoc();
			$result->free();
		} else {
			return 'error: ' . $this->mysqldb->error;
		}
		return $r;
	}

	public function good_query_table($sql) {
		$r = false;
		$this->connect2mysql();
		if ($result = $this->mysqldb->query($sql)) {
			while ($row = $result->fetch_assoc()) {
	        	$r[] = $row;
			}
			$result->free();
		}		
		return $r;
	}
	
	public function good_query_value($sql) {
		$r		= false;
		$this->connect2mysql();
		$result = $this->mysqldb->query($sql);
		$fields = $result->fetch_array(MYSQLI_NUM);
		$r		= $fields[0];
		return $r;
	}
	
	public function good_query($sql) {
		$r = false;
		$this->connect2mysql();
		$q = $this->mysqldb->query($sql);
		if (!$this->mysqldb->error) $r = $q;
		return $r;
	}
	
	public function getLastMySQLError() {
		return $this->mysqldb->error;
	}
	
	public function getURI($uri,$lang='en',$Plane=0) {
		$r = false;
		$this->connect2mysql();
		if ($result = $this->mysqldb->query("SELECT * FROM URIs WHERE URN_$lang = '$uri' AND Plane = $Plane")) {
			$r = $result->fetch_assoc();
		} else {
			$r = false;
		}
		return $r;
	}
	
	public function getURIFromId($id) {
		$r = false;
		$this->connect2mysql();
		if ($result = $this->mysqldb->query("SELECT * FROM URIs WHERE id = '$id'")) {
			$r = $result->fetch_assoc();
		} else {
			$r = false;
		}
		return $r;
	}
	
	public function getURILineageFromAddress($addr,$lang='en',$Plane=0) {
		$r			= array();
		$addrbits	= explode('/',$addr);
		$count = 0;
		foreach ($addrbits as $addrbit) {
			$count++;
			$x = array_slice($addrbits,0,$count);
			$y = implode('/',$x);
			if ($this_bit = $this->getURI($y,$lang,$Plane)) {
				$r[]	= $this_bit;
			} else {
				//return false;
				$r[] = 'waa?';
			}
		}
		return $r;
	}
	
	public function getRand($collection,$n=1,$query=array(),$fields=array()) {
		$map = new MongoCode('
			function() {
				var k	= this._id;
				var z	= Math.floor(Math.random()*10000);
				emit(k, { z: z });
			}
		');
		$reduce = new MongoCode("
			function(key, values) {
				var s = values[0];
				return s;
			}
		");
		$x = $this->mong()->command(array(
			'mapreduce'		=> $collection,
			'map'			=> $map,
			'reduce'		=> $reduce,
			'verbose'		=> true,
			'query'			=> $query
		));
		$y = $this->mong()->selectCollection($x['result'])->find($query)->sort(array('value.z' => 1))->limit($n);
		$ids = array();
		while($row = $y->getNext()) {
			$ids[] = $row['_id'];
		}
		$cursor = $this->mong()->$collection->find(array('_id' => array('$in' => $ids)),$fields);
		return $cursor;
	}
		
	public function search($rawquery,$type,$limit=25,$skip=0) {
		$this->connect2mongo();
		$werds		= explode(' ',strtolower($rawquery));
		$goodwords	= array_map('SEOify',$werds);
		$c 			= array('type' => $type, 'words' => array('$type'=> 2));
		if ($type == 'all') $c = array();
		$map_js		= '
			function() {
				var key = this._id
				var s = 1;
				this.words.forEach( function(z) {
					';
					foreach ($goodwords as $w) {
					$map_js .= "if (z == '$w') emit(key, { score: s });" . "\n";
					}
					$map_js .='
				});
				emit(key, { score: 0 });
			}
		';
		$map	= new MongoCode($map_js);
		$reduce = new MongoCode("
			function(key, values) {
				var total = 0;
				for ( var i=0; i<values.length; i++ ){
					total += values[i].score;
				}
		    	return { score : total };
			}
		");
		$massaged_search = implode('+',$goodwords);
		$x = $this->mongodb->command(array(
			"mapreduce" => "Search", 
			"map"		=> $map,
			"reduce"	=> $reduce,
			'verbose' 	=> true,
			'query'		=> $c,
			'out'		=> 'search_mr_tmp'
			)
		);
		$y = $this->mongodb->selectCollection('search_mr_tmp')->find(array('value.score' => array('$gt' => 0)))->sort(array('value.score' => -1))->limit($limit)->skip($skip);	
		$r = array();
		while ($row = $y->getNext()) {
			$id		= $row['_id'];
			$r[$id] = $row['value']['score'];
		}
		return array(
			'count'	=> $y->count(false),
			'found'	=> $y->count(true),
			'limit'	=> $limit,
			'skip'	=> $skip,
			'type'	=> $type,
			'massaged_query' => $massaged_search,
			'r'		=> $r,
			'x'		=> $x,
			'y'		=> $y
		);
	}
	
	public function logSearchQuery($rawq,$massagedq,$highestmatchrank,$totalresults) {
		$this->connect2mongo();
		if ($this->mongodb->SearchLog->findOne(array('_id' => $rawq), array('_id'))) {
			$this->mongodb->SearchLog->update(array('_id' => $rawq),array('$inc' => array('n' => 1)));
		} else {
			$q = array(
				'_id'			=> $rawq,
				'massaged_query'=> $massagedq,
				'match_rank'	=> $highestmatchrank,
				'total_results'	=> $totalresults,
				'ztamp'			=> time(),
				'words'			=> explode(' ',$massagedq),
				'n'				=> 1
			);
			$this->mongodb->SearchLog->save($q);
		}		
	}
	
	public function sendMail($de,$a,$sujet,$contenu,$casse_ligne="\n") {
		load_function('gourdMail');
		$r = gourdMail($de,$a,$sujet,$contenu);
		return $r;
	}
	
	public function sendMail_OLDPEAR($de,$a,$sujet,$contenu,$casse_ligne="\n") {
		$r		= false;
		include('Mail.php');
		include('Mail/mime.php');
		$hdrs	= array('From' => $de,'Subject' => $sujet);
		$mime	= new Mail_mime($casse_ligne);
		$contenu_pauvre	= strip_tags($contenu);
		$mime->setTXTBody($contenu_pauvre);
		$mime->setHTMLBody($contenu);
		$body	= $mime->get();
		$hdrs	= $mime->headers($hdrs);
		$mail 	=& Mail::factory('mail');
		$send	= $mail->send($a, $hdrs, $body);
		if (PEAR::isError($send)) {
			$r = $send->getMessage();
		} else {
			$r = true;
		}
		return $r;
	}
}
?>