<?php
class Itemz extends Core {
	public function connect2mysql() {
		$this->connect2mysql_db(MYSQLDB_ITEMS);
	}
	public function connect2mongo() {
		$this->connect2mongo_db(MONGODB_DB);
	}
	public function getProductGroup($PGID) {
		$this->connect2mongo();
		$r = $this->mongodb->ProductGroups->findOne(array('_id' => $PGID));
		return $r;
	}
	public function getProductGroups($c=array(),$f=array('Title')) {
		$this->connect2mongo();
		$cursor = $this->mongodb->ProductGroups->find($c,$f);
		return $cursor;
	}	
}
?>