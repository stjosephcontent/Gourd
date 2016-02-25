<?php

function getImmediateChildren($SUID,$stacks,$Plane=0,$opts=array()) {
	//	actually, let's not worry about plane and grab all beta and production chunks
	$SUID	= (string) $SUID;
	$r		= array();
	$sorts	= array();
	foreach ($stacks as $this_suid => $this_stack) {

		if (isset($this_stack['Chunks'][1]['PSUID']) && $this_stack['Chunks'][1]['PSUID'] == $SUID) {
			$r[$this_suid] = $this_stack;
			$sorts[] = (int) $this_stack['Chunks'][1]['sort'];
		} elseif (isset($this_stack['Chunks'][0]['PSUID']) && $this_stack['Chunks'][0]['PSUID'] == $SUID) {
			$r[$this_suid] = $this_stack;
			$sorts[] = (int) $this_stack['Chunks'][0]['sort'];		
		}
	}
	unset($this_suid);
	unset($this_stack);
	
	//	i don't know why these values would be out of sync, therefore, this is a hack
	if (sizeof($r) < sizeof($sorts)) {
		$sorts = array_slice($sorts,0,sizeof($r));
	}
	if (sizeof($r) > sizeof($sorts)) {
		$sorts = array_pad($sorts,sizeof($r),0);
	}

	array_multisort($sorts,$r);
	return $r;
}

function getPrettyPlane($plane) { 
	$greekletters = array('Alpha','Beta','Gamma','Delta','Epsilon','Zeta','Eta','Theta','Iota','Kappa','Lambda','Mu','Nu','Xi');
	if ($plane < 0) {
		$r = 'Archive #' . $plane*+-1;
	} elseif ($plane > sizeof($greekletters)) {
		$r = 'Plane #' . $plane;
	} elseif ($plane == 0) {
		$r = 'Production';
	} else {
		$r = $greekletters[$plane];
	}
	return $r;
} 

function promoteArray($key,$val) {
	$r = array(intval($key)+1 => $val);
	return $r;
}

function demoteArray($key,$val) {
	$r = array(intval($key)+-1 => $val);
	return $r;
}

function getTitleFromStack($stack) {
	$r = array('Title_en' => 'No Title', 'Title_fr' => 'Sans Title');
	if (isset($stack['Chunks'][0]['Title_en'])) {
		$r['Title_en'] = $stack['Chunks'][0]['Title_en'];
		$r['Title_fr'] = $stack['Chunks'][0]['Title_fr'];
	}
	if (isset($stack['Chunks'][1]['Title_en'])) {
		$r['Title_en'] = $stack['Chunks'][1]['Title_en'];
		$r['Title_fr'] = $stack['Chunks'][1]['Title_fr'];
	}
	return $r;
}

function getHighestChunkInStack($stack) {
	foreach ($stack['Chunks'] as $p => $chunk) {
		$o					= $chunk;
		$o['Plane']			= $p;
		$o['PrettyPlane']	= getPrettyPlane($p);
	}
	return $o;
}

function getHighestPlaneInStack($stack) {
	$r = NULL;
	foreach ($stack['Chunks'] as $p => $stack) {
		$r = $p;
	}
	return $r;
}

function getChunkFromStackByCUID($stack,$cuid) {
	foreach ($stack['Chunks'] as $p => $chunk) {
		if ($chunk['CUID'] == $cuid) {
			$o					= $chunk;
			$o['Plane']			= $p;
			$o['PrettyPlane']	= getPrettyPlane($p);
		}
	}
	return $o;
}

function getChunkFromStackByPlane($stack,$plane) {
	$o					= $stack['Chunks'][$plane];
	$o['Plane']			= $plane;
	$o['PrettyPlane']	= getPrettyPlane($plane);
	return $o;
}

function getCUIDSfromStack($stack) {
	$r = array();
	foreach ($stack['Chunks'] as $c) {
		$r[] = $c['CUID'];
	}
	return $r;
}

require_once 'class.Chunkz.php';

class CMS extends Chunkz {

	#	CMS also inherits all of `Admin`s methods

	protected $ancestralpile = array();
	
	public $admin;
	public $Plane;
	
	public function __construct($UserID=NULL) {
		
		if ($UserID)							$this->UserID = $UserID;
		elseif (isset($_SESSION['CMSUser']))	$this->UserID = $_SESSION['CMSUser']['UserID'];
		$this->Plane = 1;
		$this->admin = new Admin($UserID);

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

	public function getRecentDrafts($n) {
		//	return stacks whose chunks have most recently been saved to "beta"
		$this->connect2mongo();
		$r			= array();
		$criterea	= array('Chunks.1.CUID' => array('$exists' => true));
		$fields		= array();
		$cursor		= $this->mongodb->stacks->find($criterea,$fields);
		$cursor->limit($n);
		$cursor->sort(array('Chunks.1.ztamp' => -1));
		while( $cursor->hasNext() ) $r[] = $cursor->getNext();
		return $r;
	}

	public function getRecentlyUpdatedStacks($n) {
		$this->connect2mongo();
		$r			= array();
		$criterea	= array('Chunks.0.CUID' => array('$exists' => true));
		$fields		= array();
		$cursor		= $this->mongodb->stacks->find($criterea,$fields);
		$cursor->limit($n);
		$cursor->sort(array('Chunks.0.ztamp' => -1));
		while( $cursor->hasNext() ) $r[] = $cursor->getNext();
		return $r;
	}

	public function getAllStacksThatHaveChunksInPlane($Plane) {
		$r			= array();
		$criterea	= array('Chunks.' . $Plane . '.CUID' => array('$exists' => true));
		$fields		= array();
		$r			= $this->getStacks($criterea,$fields);
		return iterator_to_array($r);
	}

	public function drillStackDescendants($SUID,$plane,$start_fresh=true) {
		if ($start_fresh) $this->ancestralpile = array();
		array_unshift($this->ancestralpile,$SUID);
		$this_stack = $this->getStack($SUID);
		if ($this_stack && isset($this_stack['Chunks'][$plane]['PSUID']) && $this_stack['Chunks'][$plane]['PSUID']) {
			$this->drillStackDescendants($this_stack['Chunks'][$plane]['PSUID'],$plane,false);
		}
		return $this->ancestralpile;
	} 

	public function returnancestralpile() {
		return $this->ancestralpile;
	}

	public function getSEOPathFromCUID($CUID,$lang='en') {
		
		$plane = 99;
		
		if ($le_stack = $this->findStackThatContainsCUID($CUID)) {
			foreach ($le_stack['Chunks'] as $thisplane => $thischunk) {
				if (isset($thischunk['CUID']) && $thischunk['CUID'] == $CUID) $plane = intval($thisplane);
			}
		}
		
		$descendants = $this->drillStackDescendants($le_stack['_id'],$plane);
		
		$SEOarr_en = array();
		$SEOarr_fr = array();
		
		foreach ($descendants as $thisSUID) {
			$thisstack = $this->getStack($thisSUID);
			if (isset($thisstack['Chunks'][$plane]['CUID'])) {
				$SEOarr_en[] = $thisstack['Chunks'][$plane]['SEOTitle_en'];
				$SEOarr_fr[] = $thisstack['Chunks'][$plane]['SEOTitle_fr'];
			} else {
				$SEOarr_en[] = '';
				$SEOarr_fr[] = '';
			}	
		}
		
		$seo_txt_en = implode('/',$SEOarr_en);
		$seo_txt_fr = implode('/',$SEOarr_fr);

		if (strpos($seo_txt_en,'//') !== false) $seo_txt_en = '';
		if (strpos($seo_txt_fr,'//') !== false) $seo_txt_fr = '';
		
		if (strpos($seo_txt_en,'/') === 0)		$seo_txt_en = '';
		if (strpos($seo_txt_fr,'/') === 0)		$seo_txt_fr = '';

		$r = array('en' => $seo_txt_en, 'fr' => $seo_txt_fr, 'Plane' => $plane);
		
		return $r;
	}

	public function replaceChunkInStack($SUID,$Plane,$doc) {
		$this->connect2mongo();
		$stack_exists = (bool) $this->getStack($SUID);
		
		if ($stack_exists) {
			//	don't use an existant CUID for a fresh chunk
			if (empty($stack_exists['Chunks'][$Plane]['CUID'])) unset($doc['CUID']);
			$doc = $this->addMeaningToChunk($doc);
			
			$le_stack = $this->getStack($SUID);
			
			$this->connect2mysql();
			//	change stack Parentage if necessary
			if (!
					(
					isset($le_stack['Chunks'][$Plane]['PSUID']) &&
					isset($doc['PSUID']) &&
					($le_stack['Chunks'][$Plane]['PSUID'] == $doc['PSUID'])
					)
					||
					(!isset($le_stack['Chunks'][$Plane]['PSUID']) && !isset($doc['PSUID']))
			) {			
				//	delete the old
				if (!empty($le_stack['Chunks'][$Plane]['PSUID'])) { 
					$del = $this->mysqldb->query("DELETE FROM StackHierarchy WHERE SUID = '$SUID' AND Plane = $Plane");
				}
				//	populate the new
				if (!empty($doc['PSUID'])) {
					$ins = $this->mysqldb->query("INSERT INTO StackHierarchy (SUID,PSUID,Plane) VALUES ('$SUID','".$doc['PSUID']."',$Plane) ");
				}
			}
			
			//	strip Stack:xxxxx fields
			foreach ($doc as $k1 => $v1) {
				if (strpos($k1,'Stack:') === false)	$new_chunk[$k1] = $v1;
			}
			$new_chunk	= $this->addMeaningToChunk($new_chunk);
			$swap		= $this->mongodb->stacks->update(array('_id' => $SUID), array('$set' => array("Chunks." . $Plane => $new_chunk)));
			$touch		= $this->mongodb->stacks->update(array('_id' => $SUID), array('$set' => array('ztamp' => time())));
			$r			= $swap;
		} else {
			$r = $this->createStack($doc,$SUID);	
		}
		
		//$kill_URIs = $this->deleteAllURIsInStack($SUID);
		
		return $r;
	}

	public function getPossibleParents($ModuleID,$SUID=NULL) {
		//	Note: We should be filtering out paradoxical parents (descendants)
		//	http://www.mongodb.org/display/DOCS/Advanced+Queries
		$criterea1	= array('ModuleID' => $ModuleID, 'Chunks.0.PSUID' => '0');
		$criterea2	= array('ModuleID' => $ModuleID, 'Chunks.1.PSUID' => '0');
		$fields		= array('_id' => 1,'Chunks' => 1,'SeminalCUID' => 2);
		$r1 = $this->getStacks($criterea1,$fields);
		$r2 = $this->getStacks($criterea2,$fields);
		$r = array_merge(iterator_to_array($r1),iterator_to_array($r2));
		return $r;
	}

	public function addMeaningToChunk($doc) {
		if (!isset($doc['CUID'])) $doc['CUID'] = 'c' . uniqid();
		
		$author_id			= $this->UserID;
		$author				= $this->getUser($author_id);
		$doc['AuthorID']	= $author_id;
		$doc['AuthorName']	= $author['FullName'];
		$doc['ztamp'] 		= time();
		return $doc;
	}

	public function createStack($rawdoc=array(),$SUID=NULL) {
		$this->connect2mongo();
		$this->connect2mysql();
		$new_stack = array();
		$new_chunk = array();
		//	seperate stack and chunk values
		foreach ($rawdoc as $k1 => $v1) {
			if (strpos($k1,'Stack:') === false)	$new_chunk[$k1] = $v1;
			else 								$new_stack[str_replace('Stack:','',$k1)] = $v1;
		}
		$doc 						= $this->addMeaningToChunk($new_chunk);
		if (!$SUID) $SUID			= 's' . uniqid();
		$new_stack['_id']			= $SUID;
		$new_stack['SeminalCUID']	= $doc['CUID'];
		$new_stack['ztamp']			= time();
		if ($this->Plane == 1) 		$new_stack['Chunks'][] = array();	//	Live plane: nothing
		$new_stack['Chunks'][]	= $doc;								//	Beta plane: the doc
		//	stack
		$this->mongodb->stacks->insert($new_stack, array('safe' => true));
		
		//	stackXModule
			
		$this->connect2mysql();
			
		$exists1 = $this->mysqldb->query("SELECT ModuleID FROM StackXModule WHERE SUID = '$new_stack[_id]' AND ModuleID = '$new_stack[ModuleID]'");
		if (!$exists1) {
			$this->mysqldb->query("INSERT INTO StackXModule (SUID,ModuleID) VALUES ('".$new_stack['_id']."','".$new_stack['ModuleID']."')");
		}
			
		//	Stack Hierarchy
		if (!empty($doc['PSUID'])) {
			//	first get max sort value so you can plunk it in `Sort`
			$exists2 = $this->mysqldb->query("SELECT SUID FROM StackXHierarchy WHERE SUID = '$new_stack[_id]' AND PSUID = '$doc[PSUID]' AND Plane = ".$this->Plane);
			if (!$exists2) {
				$this->mysqldb->query("INSERT INTO StackHierarchy (SUID,PSUID,Plane) VALUES ('".$new_stack['_id']."','".$doc['PSUID']."',".$this->Plane.")");
			}
		}
	
		//	add the URI
		$seopath = $this->getSEOPathFromCUID($doc['CUID']);
		
		$new_URI = array(
			'URN_en'		=> $seopath['en'],
			'URN_fr'		=> $seopath['fr'],
			'ResourceType'	=> 'Chunk',
			'id'			=> $doc['CUID']
		);
		//if ($this->Plane == 0) 	$this->repopulateURI($new_URI,'prod');		//	sean: bug #99
		//else					$this->repopulateURI($new_URI,'stage');			//	sean: bug #99
		
		return $new_stack['_id'];
	}
	
	private function cloneStack($suid) {
		$old_stack		= $this->getStack($suid);
		$chunk2becloned = getHighestChunkInStack($old_stack);
		
		$chunk2becloned['Stack:ModuleID']	= $old_stack['ModuleID'];
		$chunk2becloned['Stack:ModuleTitle']= $old_stack['ModuleTitle'];
		
		//	discard old meaning and add more meaninful meaning
		unset($chunk2becloned['CUID']);
		$chunk2becloned = $this->addMeaningToChunk($chunk2becloned);
		
		$new_stack		= $this->createStack($chunk2becloned);
		return $new_stack;
	}

	public function deleteStack($suid) {
		$this->connect2mongo();
		$this->connect2mysql();
		$query  = "DELETE FROM StackXModule WHERE SUID = '$suid';";
		$query .= "DELETE FROM StackHierarchy WHERE SUID = '$suid'";
		if ($kill_1 = $this->mysqldb->multi_query($query)) {	$this->mysqldb->next_result();	}
		
		if ($le_stack = $this->getStack($suid)) {
			foreach ($le_stack['Chunks'] as $thischunk) {	if (!empty($thischunk['CUID'])) $this->deleteURI($thischunk['CUID'],'Chunk');	}
		}
		
		$kill = $this->mongodb->stacks->remove(array('_id' => strval($suid)),array('safe' => true));
		return (bool) $kill;
	}

	public function findStackThatContainsCUID($CUID) {
		$js = "function() {
			var x = this.Chunks;
			var ok2go =false;
			for (i in this.Chunks) if (this.Chunks[i].CUID == '$CUID') ok2go = true;
			return ok2go;
		}";
		$criterea	= array('$where' => $js);
		$stack		= $this->mongodb->stacks->findOne($criterea);
		return $stack;
	}

	private function insertIntoStack($suid,$doc,$plane) {
		$this->connect2mongo();
		$criteria 		= array('_id' => $suid);
		$doc 			= $this->addMeaningToChunk($doc);
		$u 				= $this->mongodb->stacks->update($criteria, array('$set' => array('Chunks.'.strval($plane) => $doc,'LastModified' => time())));
		return $u;
	}
	
	public function updateStack($crit,$action) {
		$this->connect2mongo();
		$u 				= $this->mongodb->stacks->update($crit,$action);
		return $u;
	}
	
	public function advanceStack($suid,$by=1) {
		$shiftamount	= $by * -1;
		$r = $this->shiftStack($suid,$shiftamount);
		return $r;
	}
	
	public function retreatStack($suid,$by=1) {
		$shiftamount	= $by * 1;
		$r = $this->shiftStack($suid,$shiftamount);
		return $r;
	}

	protected function findAllCUIDsInStack($suid) {
		$r = array();
		$stack = $this->getStack($suid);
		foreach ($stack['Chunks'] as $chunk) {
			if (isset($chunk['CUID'])) $r[] = $chunk['CUID'];
		}
		return $r;
	}

	private function shiftStack($suid,$shiftamount) {
		$this->connect2mongo();
		$criteria 		= array('_id' => $suid);
		$options		= array('w' => true);
		$old_stack		= $this->getStack($suid);
		
		$old_chunks		= $old_stack['Chunks'];
		$new_chunks		= array();
		$old_CUIDS		= array();
		foreach ($old_chunks as $plane => $chunk) {
			if (sizeof($chunk)) {
				$new_plane							= (int) $plane + (int) $shiftamount; 
				$new_chunks[(string) $new_plane]	= $chunk;
				$old_CUIDS[] = $chunk['CUID'];
			}
		}
		//	a stack must have a chunk in either beta or production (for now)
		if (! (array_key_exists('1',$new_chunks) || array_key_exists('0',$new_chunks))) {
			return false;
		}
	
				
		$u = $this->mongodb->stacks->update($criteria, array('$set' => array('Chunks' => $new_chunks,'LastShiftedBy' => $this->UserID)), $options);
		load_function('arrayToQuotedList');
		
		//	delete URIs
		$oldCUIDS_string	= arrayToQuotedList(array_filter(array_unique($old_CUIDS)),"'");
		$sql				= "DELETE FROM URIs WHERE id IN ($oldCUIDS_string) AND ResourceType = 'Chunk'";
		$killURIs			= $this->good_query($sql);
		
		//	search
		if (!empty($new_chunks['0']) && $old_stack['ModuleID'] != 'htmlbits') {
			$new_chunks['0']['_id'] = $suid;
			$this->saveToSearch($new_chunks['0'],'Chunk',array('CUID','PSUID','ztamp','MainImage'));
		} else {
			$this->removeFromSearch($suid);
		}
		return $sql;
	}

	public function addToStack($suid,$doc=array()) {
		$this->connect2mongo();
		$criteria 		= array('_id' => $suid);
		$doc 			= $this->addMeaningToChunk($doc);
		$u = $this->mongodb->stacks->update($criteria, array('$push' => array('Chunks' => $doc)));
		return $u;
	}

}
?>