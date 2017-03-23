<?php
/**
 * A basic Human Phenotype Ontology Class
 * 
 * @author Csaba Halmagyi
 *
 */
require_once 'Term.class.php';

class HPO {


	
	/**
	 * Holds the format information of the ontology
	 * @type string
	 */
	protected $formatInfo;
	
	/**
	 * Holds the terms in the ontology
	 * @var unknown
	 */
	protected $terms = array();
	
	/**
	 * Adds an HPO term to the collection
	 * @param Term $obj
	 * @param string $key
	 * @throws KeyHasUseException
	 */
	public function addTerm(&$obj, $key) {

			if (isset($this->terms[$key])) {
				throw new KeyHasUseException("Key $key already in use.");
			}
			else {
				$this->terms[$key] = $obj;
			}
	
	}
	
	/**
	 * Removes a Term from the collection
	 * @param string $key
	 * @throws KeyInvalidException
	 */
	public function deleteTerm($key) {
		if (isset($this->terms[$key])) {
			unset($this->terms[$key]);
		}
		else {
			throw new KeyInvalidException("Invalid key $key.");
		}
	}
	
	/**
	 * Returns a Term from the collection
	 * @param string $key
	 * @throws KeyInvalidException
	 */
	public function getTerm($key) {
		if (isset($this->terms[$key])) {
			return $this->terms[$key];
		}
		else {
			throw new KeyInvalidException("Invalid key $key.");
		}
	}
	/**
	 * Returns the number of Terms in the collection
	 */
	public function length() {
		return count($this->terms);
	}
	
	/**
	 * Determines whether a given key exists in the collection
	 * @param string $key
	 */
	public function keyExists($key) {
		return isset($this->terms[$key]);
	}
	
	/**
	 * Returns the keys already being in use
	 */
	public function keys() {
		return array_keys($this->terms);
	}
	
	
	/**
	 * Loads an .obo file and extracts the terms which are not in the current ontology 
	 * and returns them in an array
	 * @param string $filename
	 * @return Array of Terms
	 */
	public function load($filename){
		// determines if the first term was read already
		$firstTermNotRead = true;
		$newTerm = null;
		$termVersion = null;
		$ontoArray = array();
		
		$finishedTerm = false;
		// opens the hp.obo file to read
		$handle = fopen ( $filename, "r" );
		if ($handle) {
			while ( ($line = fgets ( $handle )) !== false) {
				// remove special characters from the line
				$line = trim ( str_replace ( PHP_EOL, '', $line ) );
				$line = trim ( str_replace ( '"', '', $line ) );
				// read version and format info before finding the first term
				if ($firstTermNotRead && $line != "[Term]"){
					$words = explode ( ":", $line, 2 );
					if ($words[0]=="format-version"){
						$this->formatInfo=trim($words[1]);
					}
					if ($words[0]=="data-version"){
						$termVersion=trim($words[1]);
					}
						
				}
					
				// if a term definition is find
				if ($line == "[Term]") {
					$firstTermNotRead = false;
					
						
					// if a term is already been finished
					if ($finishedTerm) {
						//set version for the Term
						$newTerm->setVersion($termVersion);

						$ontoArray[$newTerm->getId()]=$newTerm;

						$finishedTerm = false;
						
						//reset newTerm
						$newTerm = new Term();
		
						
					}// if it is a new term
					else {
						// create a new term object

						$newTerm = new Term();
						$finishedTerm = false;
					}
				}
				// if it is a term attribute line
				if ($line != "[Term]" && strlen ( $line ) > 2) {
					$words = explode ( ":", $line, 2 );
					//set object id
					if (trim($words[0])=="id"){
						$newTerm->setId(trim($words[1]));
					}
					//set object name
					if (trim($words[0])=="name"){
						$newTerm->setName(trim($words[1]));
					}					
					//set object def
					if (trim($words[0])=="def"){
						//removing "[HPO:...]"
						$definition = explode("[",$words[1],2);
						
						$newTerm->setDef(trim($definition[0]));
					}
					//set object comment
					if (trim($words[0])=="comment"){
						$newTerm->setComment(trim($words[1]));
					}
					
					//add object synonym
					if (trim($words[0])=="synonym"){
						$newTerm->addSynonym(trim($words[1]));
					}
					//add object xref
					if (trim($words[0]) =="xref"){
						$subString = explode ( " ", trim($words[1]), 2 );
						//addXref(xrefId,xrefName)
						if ( ! isset($subString[1])) {
							$subString[1] = null;
						}
						$newTerm->addXref(trim($subString[0]),trim($subString[1]));
					}
					//add object is_a
					if (trim($words[0])=="is_a"){
						$subString = explode ( "!", trim($words[1]), 2 );
						//addIs_a(is_aId,is_aName)
						$newTerm->addIs_a(trim($subString[0]),trim($subString[1]));
					}															
				}
				// if the first term was already been read and it is an empty line
				if(!($firstTermNotRead) && strlen ( $line ) < 2){
					//we finished a term, make it ready to insert
					$finishedTerm = true;
				}

			}

		} else {
			// error opening the file.
			echo 'Error opening .obo file!';
		}
		
		//set version for the Term
		$newTerm->setVersion($termVersion);
		// if it is a new term insert add it to the array
		if (!($this->keyExists($newTerm->getId()))){
		$ontoArray[$newTerm->getId()]=$newTerm;}
		return $ontoArray;
		
		//$this->addTerm($newTerm,$newTerm->getId());
	}
 
	
	/**
	 * Loads an .obo file and inserts the new Terms to the database
	 * @uses dbconnection
	 */
	function update($filename){

		$new = 0;
		$errors = 0;
		$updated = 0;
		//loads the new terms from the file
		$terms = $this->load($filename);
		$updateTerms = array();
		$insertTerms = array();
		
		include './settings/dbsettings.php';
		
		$pdo = new PDO("mysql:dbname=$db;host=$dbhost", $dbuser, $dbpass );
		
		foreach ($terms as $termInFile){
			
			$sqlId = $termInFile->getId();
			$sqlName = $termInFile->getName();
		
			if($this->keyExists($sqlId)){
				$oldTerm = $this->getTerm($sqlId);
			
				if($oldTerm->getName() != $sqlName){

					$prevName = $oldTerm->getPrevnames()."::".$oldTerm->getName();
					$termInFile->addPrevnames($prevName);
					$updateTerms[]=$termInFile;
            	}
			
			}
			else{
			$insertTerms[] = $termInFile;
			}
		}
		
		if (empty($updateTerms) && empty($insertTerms)){
			echo '<br/>Your database is up to date.<br/>';
		}
		else if(!empty($updateTerms)){
			
			echo '<table><thead><tr><td colspan="2">Updating existing terms</td><td>Result</td></tr></thead><tbody>';
			
			foreach($updateTerms as $ut){
					
				$sqlId = $ut->getId();
				$sqlName = $ut->getName();
				$sqlDef = $ut->getDef();
				$sqlSyn = "";
				if (count($ut->getSynonyms())>0) {$sqlSyn = json_encode($ut->getSynonyms());}
				$sqlComm = $ut->getComment();
				$sqlXref = "";
				if (count($ut->getXref())>0) {$sqlXref = json_encode($ut->getXref());}
				$sqlIs_a = "";
				if (count($ut->getIs_a())>0) {$sqlIs_a = json_encode($ut->getIs_a());}
				$sqlVer = $ut->getVersion();
					
				$prevName = $ut->getPrevnames();
				//echo 'FileID: '.$sqlId.' FileName: '.$sqlName;
					
					
				$sql = "UPDATE hpo SET `Name` = :name,
            						`Def` = :def,
            						`Synonyms` = :syn,
            						`Comment` = :comm,
            						`Xref` = :xref,
            						`Is_a` = :is_a,
									`Version` = :ver,
            						`PrevNames` = :prevnames
   						WHERE `ID` = :id";
					
				$stmt = $pdo->prepare($sql);
				$stmt->bindparam(":name",$sqlName);
				$stmt->bindparam(":def",$sqlDef);
				$stmt->bindparam(":syn",$sqlSyn);
				$stmt->bindparam(":comm",$sqlComm);
				$stmt->bindparam(":xref",$sqlXref);
				$stmt->bindparam(":is_a",$sqlIs_a);
				$stmt->bindparam(":ver",$sqlVer);
				$stmt->bindparam(":prevnames",$prevName);
				$stmt->bindparam(":id",$sqlId);
			
			
				$stmt->execute();
				if ($stmt){
					echo "<tr><td>".$sqlId."</td><td>".$prevName."</td><td>Updated</td></tr>";
					$updated++;
				}
				else{
					echo "<tr><td>".$sqlId."</td><td>".$prevName."</td><td>Update failed</td></tr>";
					$errors++;
				}
					
			
			}
			echo '</tbody></table><br>';
		}
		else if(!empty($insertTerms)){
			echo '<table><thead><tr><td colspan="2">Importing new terms</td><td>Result</td></tr></thead><tbody>';
			foreach($insertTerms as $it){
			
				$sqlId = $it->getId();
				$sqlName = $it->getName();
				$sqlDef = $it->getDef();
				$sqlSyn = "";
				if (count($it->getSynonyms())>0) {$sqlSyn = json_encode($it->getSynonyms());}
				$sqlComm = $it->getComment();
				$sqlXref = "";
				if (count($it->getXref())>0) {$sqlXref = json_encode($it->getXref());}
				$sqlIs_a = "";
				if (count($it->getIs_a())>0) {$sqlIs_a = json_encode($it->getIs_a());}
				$sqlVer = $it->getVersion();
					
					
				$sql = "INSERT INTO hpo (ID, Name, Def, Synonyms, Comment, Xref, Is_a, Version)
				VALUES (:id, :name, :def, :syn, :comm, :xref, :is_a, :ver)";
					
				$stmt = $pdo->prepare($sql);
				$stmt->bindparam(":name",$sqlName);
				$stmt->bindparam(":def",$sqlDef);
				$stmt->bindparam(":syn",$sqlSyn);
				$stmt->bindparam(":comm",$sqlComm);
				$stmt->bindparam(":xref",$sqlXref);
				$stmt->bindparam(":is_a",$sqlIs_a);
				$stmt->bindparam(":ver",$sqlVer);
				$stmt->bindparam(":id",$sqlId);
				$stmt->execute();
					
				if ($stmt){
					echo "<tr><td>".$sqlId."</td><td>".$sqlName."</td><td>Imported</td></tr>";
					$new++;
				}
				else{
					echo "<tr><td>".$sqlId."</td><td>".$sqlName."</td><td>Import failed</td></tr>";
					$errors++;
				}
			}
			
			echo '</tbody></table><br>';
		}

		

		echo '<br/>New terms added: '.$new.'<br/>';
		echo 'Terms updated: '.$updated.'<br/>';
		echo 'Errors occured: '.$errors.'<br/>';
		
	}
	
	/**
	 * Reads the Term IDs from the database
	 * @uses dbconnection
	 */
	function read(){

		include './settings/dbsettings.php';
		
		$dbh = new PDO("mysql:dbname=$db;host=$dbhost", $dbuser, $dbpass );
		
		$query = "SELECT ID, Name FROM hpo";
		$sth = $dbh->prepare($query);
		$sth->execute();
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		foreach ($result as $t){
			$newTerm = new Term();
			$newTerm->setId($t['ID']);
			$newTerm->setName($t['Name']);
			$this->addTerm($newTerm,$t['ID']);
		}
		//var_dump($this->terms);
	}
	
	
	
	/**
	 * Performs a search on the terms
	 * @param string $subString
	 * @param int $maxResults
	 * @uses dbconnection
	 */
	function search($subString, $maxResults = 15){
		$resultArray = array();
		$resCount = 0;
		$resultTermsInName = array();
		$resultTermsInSynonym = array();
		$resultTermsInDescription = array();
		
		include './settings/dbsettings.php';

		
		$dbh = new PDO("mysql:dbname=$db;host=$dbhost", $dbuser, $dbpass );
		
		//if the passed value is a number or it starts with "HP:"
		if (is_numeric($subString) || substr(strtolower($subString), 0, 3) === "hp:"){
			$query = "SELECT * FROM hpo where ID LIKE '%".$subString."%' LIMIT ".$maxResults;
			
			$sth = $dbh->prepare($query);
			$sth->execute();
			$resultTermsInName = $sth->fetchAll(PDO::FETCH_ASSOC);
			$resCount=count($resultTermsInName);
			
		}
		else {
			$query = "SELECT * FROM hpo where Name LIKE '".$subString."%' OR PrevNames LIKE '".$subString."' LIMIT ".$maxResults;
				
			$sth = $dbh->prepare($query);
			$sth->execute();
			$resultTermsInName = $sth->fetchAll(PDO::FETCH_ASSOC);
			$resCount=count($resultTermsInName);	
			
			if ($resCount<$maxResults){
				$remaining = $maxResults-$resCount;
				$sql = "SELECT * FROM (select * from hpo where Name NOT LIKE '".$subString."%' AND PrevNames NOT LIKE '".$subString."') WHERE Name LIKE '%".$subString."%' LIMIT ".$remaining;

				$sth = $dbh->prepare($sql);
				$sth->execute();
				$result = $sth->fetchAll(PDO::FETCH_ASSOC);
				$resCount+=count($result);
				$resultTermsInName = array_merge($resultTermsInName, $result);
				
			}
			if ($resCount<$maxResults){
				$remaining = $maxResults-$resCount;
				$sql2 = "SELECT * FROM hpo where Synonyms LIKE '%".$subString."%' and ID NOT IN (
						select ID from hpo where Name LIKE '%".$subString."%')  
										 LIMIT ".$remaining;
				
				$sth = $dbh->prepare($sql2);
				$sth->execute();
				$result = $sth->fetchAll(PDO::FETCH_ASSOC);
				$resCount+=count($result);
				$resultTermsInSynonym = $result;
				
				
			}
			if ($resCount<$maxResults){
				$remaining = $maxResults-$resCount;
				$sql3 = "SELECT * FROM hpo where Def LIKE '%".$subString."%' 
						and ID NOT IN (SELECT ID FROM hpo where Synonyms LIKE '%".$subString."%' OR Name LIKE '%".$subString."%')  
								 LIMIT ".$remaining;
			
				$sth = $dbh->prepare($sql3);
				$sth->execute();
				$result = $sth->fetchAll(PDO::FETCH_ASSOC);
				$resCount+=count($result);
				$resultTermsInDescription = $result;
			
			
			}
		}
		
		$resultArray['serviceProvider']="CIT Cambridge";
		$resultArray['ontology']="Human Phenotype Ontology";
		$resultArray['result']=$resCount;
		$resultArray['resultsInName']=$resultTermsInName;
		$resultArray['resultsInSynonym']=$resultTermsInSynonym;
		$resultArray['resultsInDescription']=$resultTermsInDescription;

		return $resultArray;
	}//end of search
	/**
	 * Returns all child element of a parent term
	 * 
	 * @param string $parentTerm
	 * @return multitype:array of Terms
	 * @uses dbconnection
	 */
	public function getChildren($parentTerm){
		$children = array();

		include './settings/dbsettings.php';
		$dbh = new PDO("mysql:dbname=$db;host=$dbhost", $dbuser, $dbpass );
		
		$sql = "SELECT * FROM hpo WHERE Is_a LIKE '%".$parentTerm."%'";
		$sth = $dbh->prepare($sql);
		$sth->execute();
		$children = $sth->fetchAll(PDO::FETCH_ASSOC);
		return $children;
		
	}
	
	/**
	 * Returns true if the passed term has got at least one child element
	 * @param string $parentTerm
	 * @return boolean
	 * @uses dbconnection
	 */
	public function hasChild($parentTerm){
		
		include './settings/dbsettings.php';
		$dbh = new PDO("mysql:dbname=$db;host=$dbhost", $dbuser, $dbpass );
		
		$sql = "SELECT * FROM hpo WHERE Is_a LIKE '%".$parentTerm."%'";
		
		$sth = $dbh->prepare($sql);
		$sth->execute();
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
		$resCount=count($resultTermsInName);
		
		if (empty($result)){
			return false;
		}
		return true;
		
	}
	
	public function getFirstParent($term){
		include './settings/dbsettings.php';
		$dbh = new PDO("mysql:dbname=$db;host=$dbhost", $dbuser, $dbpass );
		
		$sql = "SELECT Is_a FROM hpo WHERE ID LIKE '%".$term."%'";
		$sth = $dbh->prepare($sql);
		$sth->execute();
		$sthanswer = $sth->fetch(PDO::FETCH_ASSOC);
		
      $parents = json_decode($sthanswer['Is_a'],true);
		if(empty($parents)) return null;
		else {
			$firstParent = null;
			foreach($parents as $key=>$val){
				if ($firstParent == null){
					return $key;
				}
			}
		}

	}
	
  	public function getFirstPath($from,$to){
		$path = array($from);
		$curr = $from;
		$error = null;
		while($curr != $to){
			$firstParent = $this->getFirstParent($curr); 
			
			if ($firstParent == null) {
				$error = 'Root element reached without finding the destination term';
				break;
			}
			else{
				$path[] = $firstParent;
				$curr = $firstParent;
			}
		}
		
		if($error == null){
			return array("error"=>null,"path"=>$path);
		}
		else{
			return array("error"=>$error,"path"=>array());
		}
	}
  
	
	/**
	 * Returns the children elements of a term
	 * @param string $parentTerm
	 */
	public function children($parentTerm){

		$resultArray = array();
		$resCount = 0;
		$childall = $this->getChildren($parentTerm);
		
		$resultArray['serviceProvider']="CIT Cambridge";
		$resultArray['ontology']="Human Phenotype Ontology";
		$resultArray['result']= count($childall);
		$resultArray['children']=$childall;
		
	return $resultArray;	
	}//end of children
	
	/**
	 * Returns the sub graph of the ontology excluding the parent Term
	 * @param string $parentTerm
	 * @return array of Terms
	 */
	public function subGraph($parentTerm){
		$subGraph = array();
		$keys = array();
		//get the children elements of the parent Term
		
		$tempChildren = array();
		$tempChildren = $this->getChildren($parentTerm);
		for ($i=0;$i<count($tempChildren);$i++){
			
			$subGraph[]=$tempChildren[$i];
			$keys[] = $tempChildren[$i]->getId();
		}
		

		//get all the terms and their subterms
		$tempChildren = array();
		
		for ($i=0;$i<count($keys);$i++){
			$tempChildren = $this->getChildren($keys[$i]);

			for($j=0;$j<count($tempChildren);$j++){

				$id = $tempChildren[$j]->getId();

				if (!in_array($id,$keys)){
					$subGraph[]=$tempChildren[$j];
					$keys[]=$id;
				}
			}
			$tempChildren = array();
		} 
		
		return $subGraph;
	}
	
	
	
	
}//end of class


?>