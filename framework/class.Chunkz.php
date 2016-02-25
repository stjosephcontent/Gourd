<?php

function stackHasChildren($SUID,$stacks,$Plane=0,$opts=array()) {
	$r = false;
	foreach ($stacks as $this_suid => $this_stack) {
		if (isset($this_stack['Chunks'][$Plane]['PSUID']) && $this_stack['Chunks'][$Plane]['PSUID'] == $SUID) $r = true;
	}
	return $r;
}

function cursorToSortedArray($cursor,$Plane) {
	$chunks			= array();
	$sorts			= array();
	while ($row		= $cursor->getNext()) {
		$this_plane = $Plane;
		if ($Plane > 0 && empty($row['Chunks'][$Plane])) $this_plane = 0;		
		//	now remove invalid elements?
		if (!empty($row['Chunks'][$this_plane])) {
			$chunks[]	= $row['Chunks'][$this_plane];
			$sorts[]	= $row['Chunks'][$this_plane]['sort'];		
		}
	}
	array_multisort($sorts,$chunks);
	return $chunks;
}

function CUIDExistsInNode($CUID,$node) {
	$r = false;
	if ($node['CUID'] == $CUID) return true;
	if (isset($node['children'])) {
		foreach ($node['children'] as $child) {
			if (CUIDExistsInNode($CUID,$child)) return true;
		}
	}
	return false;
}

function extractImmediateFamilyFromFamilyTree($familytree,$CUID) {
	$r = array();
	foreach ($familytree as $node) {
		$r = $node;
		if (CUIDExistsInNode($CUID,$node)) break;
	}
	return $r;
}

class Chunkz extends Core {

	public function __construct($UserID=NULL) {
		$this->Plane = 0;
	}
	
	public function connect2mysql() {
		$this->connect2mysql_db(MYSQLDB_STACKS);
	}

	public function connect2mongo() {
		$this->connect2mongo_db(MONGODB_DB);
	}
	
	public function generateURI($uri,$lang='en',$Plane=0) {		
		$r 						= false;
		$breadcrumb				= $this->getBreadcrumbFromAddress($uri,$lang,$Plane);
		if ($breadcrumb) {
			$URN_en 			= array();
			$URN_fr 			= array();
			foreach ($breadcrumb as $crumb) {
				$URN_en[]		= trim($crumb['SEOTitle_en']);
				$URN_fr[]		= trim($crumb['SEOTitle_fr']);
				$id				= $crumb['CUID'];
			}
			$URN_en_str			= implode('/',$URN_en);
			$URN_fr_str			= implode('/',$URN_fr);
			if (strlen($URN_en_str) < 2) $URN_en_str = '';
			if (strlen($URN_fr_str) < 2) $URN_fr_str = '';
			$r = array(
				'URN_en'		=> $URN_en_str,
				'URN_fr'		=> $URN_fr_str,
				'id'			=> $id,
				'ResourceType'	=> 'Chunk'
			);
		}		
		return $r;
	}
	
	public function generateAddressFromCUID($CUID,$Plane=0) {
		$chunk = $this->getChunk($CUID);	
		$crumbbread = array();
		if ($chunk) {
			$crumbbread[] = array(
				'URN_en'		=> $chunk['SEOTitle_en'],
				'URN_fr'		=> $chunk['SEOTitle_fr'],
				'Title_en'		=> $chunk['Title_en'],
				'Title_fr'		=> $chunk['Title_fr'],
				'id'			=> $chunk['CUID'],
				'PSUID'			=> $chunk['PSUID'],
				'ResourceType'	=> 'Chunk'
				
			);
			if ($chunk['PSUID']) {
				$c = array('_id' => $chunk['PSUID']);
				$f = array('Chunks');
				
				$pstack = $this->getStack($chunk['PSUID']);

				if (isset($pstack['Chunks'][0]['CUID'])) {
					$PCUID = $pstack['Chunks'][0]['CUID'];	
				}
				
				if (isset($pstack['Chunks'][$Plane]['CUID'])) {
					$PCUID = $pstack['Chunks'][$Plane]['CUID'];	
				}
				$crumbbread = array_merge($crumbbread,$this->generateAddressFromCUID($PCUID,$Plane));	
                                echo "<pre>";print_r($crumbbread);  echo "</pre>";
				for ($i = 1; $i < sizeof($crumbbread);$i++) {
					$h = $i +- 1;
//                                        echo "<pre>";print_r($crumbbread[$i]['URN_en']);  echo "</pre>";
                                        if ($crumbbread[$h]['URN_en'] == "BIM-Services") {
                                            $crumbbread[$h]['URN_en'] = "services" . '#' . $crumbbread[$h]['URN_en'];
                                        } else if ($crumbbread[$i]['URN_en'] == "Who-We-Are") {
                                             $crumbbread[$h]['URN_en'] = "about" . '#' . $crumbbread[$h]['URN_en'];    
                                        } else if ($crumbbread[$i]['URN_en'] == "Client-Services") {
                                             $crumbbread[$h]['URN_en'] = "services" . '#' . $crumbbread[$h]['URN_en'];
                                        } else {
//                                            var_dump("here");
                                            $crumbbread[$h]['URN_en'] = $crumbbread[$i]['URN_en'] . '/' . $crumbbread[$h]['URN_en'];
                                        }
					
				}
			}
		}
		$crumbbread = array_reverse($crumbbread);		
		return $crumbbread;
	}
	
	public function getBreadcrumbFromAddress($uri,$lang,$Plane=0) {
	
		$this->connect2mongo();
		$r			= array();
		$addrbits	= explode('/',$uri);
		$PSUID		= 0;
		
		$this_URI_en = array();
		$this_URI_fr = array();
		$URI_en_is_sullied	= false;
		$URI_fr_is_sullied	= false; 
		
		foreach ($addrbits as $addrbit) {
			$c		= array("Chunks.$Plane.SEOTitle_$lang" => $addrbit,"Chunks.$Plane.PSUID" => (string) $PSUID);
			$c2		= array("Chunks.0.SEOTitle_$lang" => $addrbit,"Chunks.0.PSUID" => (string) $PSUID);
			$f		= array("Chunks");
			
			$this_plane = $Plane;
			
			$this_stack = $this->mongodb->stacks->findOne($c,$f);
			
			if ($Plane > 0 && empty($this_stack)) {
				$this_stack = $this->mongodb->stacks->findOne($c2,$f);
				$this_plane = 0;
			} 
			
			//if ($this_stack = $this->mongodb->stacks->findOne($c,$f)) {
			
			if ($this_stack) {
			
				//	set parentage | set invalid URLs to zero-length strings
				$this_URI_en[] = $this_stack['Chunks'][$this_plane]['SEOTitle_en'];
				$this_URI_fr[] = $this_stack['Chunks'][$this_plane]['SEOTitle_fr'];
				if ($URI_en_is_sullied) $this_URI_en = array();
				if ($URI_fr_is_sullied) $this_URI_fr = array();
				if (!strlen($this_stack['Chunks'][$this_plane]['SEOTitle_en'])) {
					$URI_en_is_sullied = true;
					$this_URI_en = array();
				}
				if (!strlen($this_stack['Chunks'][$this_plane]['SEOTitle_fr'])) {
					$URI_fr_is_sullied = true;
					$this_URI_fr = array();
				}
				$r[] = array(
					'Title_en'		=> $this_stack['Chunks'][$this_plane]['Title_en'],
					'Title_fr'		=> $this_stack['Chunks'][$this_plane]['Title_fr'],
					'URN_en'		=> implode('/',$this_URI_en),
					'URN_fr'		=> implode('/',$this_URI_fr),
					'id'			=> $this_stack['Chunks'][$this_plane]['CUID'],
					'ResourceType'	=> 'Chunk'
					//'PSUID'			=> $this_stack['_id'],	//	prolly not required, but wouldn't hurt
				);
				
				$PSUID = $this_stack['_id'];
			} else {
				$r = false;
				//	maybe it's a vagabond
				$c		= array("Chunks.$Plane.SEOTitle_$lang" => $addrbit,"Chunks.$Plane.PSUID" => '');
				$c2		= array("Chunks.0.SEOTitle_$lang" => $addrbit,"Chunks.0.PSUID" => '');	
				$f		= array("Chunks");
				
				$this_plane = $Plane;
			
				$this_stack = $this->mongodb->stacks->findOne($c,$f);
			
				if ($Plane > 0 && !$this_stack) {
					$this_stack = $this->mongodb->stacks->findOne($c2,$f);
					$this_plane = 0;
				} 
			
				//if ($this_stack = $this->mongodb->stacks->findOne($c,$f)) {
			
				if ($this_stack) {
				
				//if ($this_stack = $this->mongodb->stacks->findOne($c,$f)) {
					$r = array(array(
						'Title_en'		=> $this_stack['Chunks'][$this_plane]['Title_en'],
						'Title_fr'		=> $this_stack['Chunks'][$this_plane]['Title_fr'],
						'URN_en'		=> $this_stack['Chunks'][$this_plane]['SEOTitle_en'],
						'URN_fr'		=> $this_stack['Chunks'][$this_plane]['SEOTitle_fr'],
						'id'			=> $this_stack['Chunks'][$this_plane]['CUID'],
						'ResourceType'	=> 'Chunk'
					));
				}
			}
		}
		return $r;
	}
	
	public function getFamilyTree($ModuleID,$Plane,$PSUID=0,$f=array('Chunks')) {
		
		$r 					= array();
		$c 					= array('ModuleID' => $ModuleID,"Chunks.$Plane.PSUID" => (string) $PSUID);
		
		if ($Plane > 0) {
			$c2				= array('ModuleID' => $ModuleID,"Chunks.0.PSUID" => (string) $PSUID);
			$c3				= array('$or' => array($c,$c2));
			$this_gen_cursor = $this->getStacks($c3,$f);
		} else {
			$this_gen_cursor = $this->getStacks($c,$f);
		}
		
		$this_gen			= array();
		$sorts				= array();
		while($this_stack	= $this_gen_cursor->getNext()) {
			$this_plane		= $Plane;
			
			if ($Plane > 0 && empty($this_stack['Chunks'][$Plane])) {
				$this_plane	= 0;
			}
			
			if (!empty($this_stack['Chunks'][$this_plane])) {
				$new_guy		= $this_stack['Chunks'][$this_plane];
				$new_guy['SUID']= $this_stack['_id'];
				$this_gen[] 	= $new_guy;
				$sorts[]		= $new_guy['sort'];
			}			
		}
		array_multisort($sorts,$this_gen);
		
		foreach ($this_gen as $this_guy) {
			if ($children = $this->getFamilyTree($ModuleID,$Plane,$this_guy['SUID'],$f)) {
				$this_guy['children'] = $children;	
			}
			$r[] = $this_guy;
		}
		
		return $r;
	}
	
	public function getChunkFromAddress($addr,$lang='en',$Plane=0) {
		$r			= false;
		$ChunkID	= $this->good_query_value("SELECT id FROM URIs WHERE ResourceType = 'Chunk' AND URI_$lang = '$addr'");
		$criterea	= array('Chunks.CUID' => $ChunkID);
		$stack		= $this->findStack($criterea);
		$r			= $stack['Chunks'][$Plane];
		return $r;
	}

	public function getChunk($CUID,$Plane=0) {
		//	Note: we really don't need $Plane because every chunk has a CUID
		$this->connect2mongo();
		$c1			= array('Chunks.0.CUID' => $CUID);
		$c2			= array('Chunks.1.CUID' => $CUID);
		$c3			= array('Chunks.CUID' => $CUID);
		$criteria	= array('$or' => array($c1,$c2,$c3));
		$fields		= array();
		$stack		= $this->mongodb->stacks->findOne($criteria,$fields);
		foreach ($stack['Chunks'] as $chunk) {
			if (!empty($chunk['CUID']) && $chunk['CUID'] == $CUID) $r = $chunk;
		}
		//$r			= $stack['Chunks'][$Plane];
		$r['ModuleID']	= $stack['ModuleID'];
		$r['SUID']		= $stack['_id'];
		return $r;
	}

	public function getVagabonds($module_id,$Plane=0) {
		$r1			= array();
		$r2			= array();
		$criterea2	= array('ModuleID' => (string) $module_id,'Chunks.'.$Plane.'.PSUID' => '');
		$fields		= array();
		$c2			= $this->mong()->stacks->find($criterea2,$fields)->sort(array('Chunks.'.$Plane.'.sort' => 1));
		if ($Plane == 1) {
			$NotPlane = (int) $Plane * -1 + 1;
			$criterea1	= array('ModuleID' => (string) $module_id, 'Chunks.'.$NotPlane.'.PSUID' => '');
			$c1		= $this->mong()->stacks->find($criterea1,$fields)->sort(array('Chunks.'.$NotPlane.'.sort' => 1));
			$r1		= iterator_to_array($c1);
		} else {
			$r1		= array();
		}
		$r2			= iterator_to_array($c2);
		$r			= array_merge($r2,$r1);
		return $r;
	}

	public function getVagabonds_OLD($module_id,$Plane=0) {
		$js = "function() {
			var x = this.Chunks;
			var ok2go =false;
			for (i in this.Chunks) if (this.Chunks[i].PSUID == '') ok2go = true;
			return ok2go;
		}";
		$criterea	= array( 'ModuleID' => $module_id , '$where' => $js);
		$fields		= array();
		$r			= $this->getStacks($criterea,$fields);
		//$r->sort(array('Chunks.'.$Plane.'.sort' => 1));
		//$r->sort(array('Chunks.'.$Plane.'.sort' => 1));

		$NotPlane	= (int) $Plane * -1 + 1;
		$r->sort(array(
				'Chunks.'.$Plane.'.sort'	=> 1,
				'Chunks.'.$NotPlane.'.sort'	=> 1
			)
		);
		return iterator_to_array($r);
	}
	
	public function getProgenitors($module_id,$Plane=0) {
		$criterea	= array( 'ModuleID' => $module_id , 'Chunks.'.$Plane.'.PSUID' => '0');
		$fields		= array();
		$r = $this->getStacks($criterea,$fields);
		return iterator_to_array($r);
	}

	public function getStacks($criterea=array(),$fields=array()) {
		$this->connect2mongo();
		$r = array();
		$cursor = $this->mongodb->stacks->find($criterea,$fields);
		return $cursor;
	}

	public function getStack($suid,$fields=array()) {
		$this->connect2mongo();
		$r = $this->mongodb->stacks->findOne(array("_id" => strval($suid)),$fields);
		return $r;
	}
	
	public function saveForm($FormName,$FormData) {
		$this->connect2mongo();
		$ztamp = time();
		$submittal = array(
			'ztamp'		=> $ztamp,
			'users_IP'	=> $_SERVER["REMOTE_ADDR"],
			'FormName'	=> $FormName,
			'POST'		=> $FormData
		);
		$saved = $this->mongodb->Forms->save($submittal);
	}
}
?>