<?php
/**
 * A Human Phenotype Ontology Term 
 * 
 * @author Csaba Halmagyi
 *
 */
class Term implements JsonSerializable{
	/**
	 * Term id
	 * @var unknown
	 */
	protected $id;
	
	/**
	 * Term name
	 * @var unknown
	 */
	protected $name;
	
	/**
	 * Term definition
	 * @var unknown
	 */
	protected $def;
	
	/**
	 * Term synonym's ids
	 * @var unknown
	 */
	protected $synonyms = array();
	
	/**
	 * Comment
	 * @var unknown
	 */
	protected $comment;
	
	/**
	 * Cross references to God knows where
	 * @var unknown
	 */
	protected $xref = array();
	
	/**
	 * Reference to parent Term
	 * @var unknown
	 */
	protected $is_a = array();
	
	
	/**
	 * Holds the version of the term
	 * @type string
	 */
	protected $version;
	
	protected $prevnames;
	
	//SETTERS
	
	/**
	 * Sets id attribute
	 * @param unknown $id
	 */
	public function setId($id) {
			$this->id = $id;
	}
	
	/**
	 * Sets name attribute
	 * @param unknown $name
	 */
	public function setName($name) {
		$this->name = $name;
	}	
	
	/**
	 * Sets def attribute
	 * @param unknown $def
	 */
	public function setDef($def) {
		$this->def = $def;
	}
	
	/**
	 * Sets comment attribute
	 * @param unknown $id
	 */
	public function setComment($comment) {
		$this->comment = $comment;
	}
	
	/**
	 * Sets version attribute
	 * @param unknown $version
	 */
	public function setVersion($version) {
		$this->version = $version;
	}
	/**
	 * Adds a new synonym to synonym array
	 * @param unknown $syn
	 */
	public function addSynonym(&$syn) {
		$this->synonyms[] = $syn;
		
	}
	
	/**
	 * Adds a new xref to xref assoc array
	 * @param unknown $xrefId
	 * @param unknown $xrefName
	 */
	public function addXref($xrefId,$xrefName){
		$this->xref[$xrefId]=$xrefName;
		
	}

	/**
	 * Adds a new is_a relationship to is_a assoc array
	 * @param unknown $is_aId
	 * @param unknown $is_aName
	 */
	public function addIs_a($is_aId,$is_aName){
		$this->is_a[$is_aId]=$is_aName;
		
	}
	
	public function addPrevnames($prevname) {
		$this->prevnames = $prevname;
	
	}
	
	//GETTERS
	
	
	public function getId(){
		return $this->id;
	}
	
	public function getName(){
		return $this->name;
	}	

	public function getDef(){
		return $this->def;
	}

	public function getComment(){
		return $this->comment;
	}
	
	public function getSynonyms(){
		return $this->synonyms;
	}
	
	public function getXrefKeys(){
		return array_keys($this->xref);
	}

	public function getXref(){
		return $this->xref;
	}
	
	public function getIs_aKeys(){
		return array_keys($this->is_a);
	}

	public function getIs_a(){
		return $this->is_a;
	}
	
	public function XrefKeyExists($key){
		return isset($this->xref[$key]);
	}
	
	public function Is_aKeyExists($key){
		return isset($this->is_a[$key]);
	}
	
	public function getVersion(){
		return $this->version;
	}
	
	public function getPrevnames(){
		return $this->prevnames;
	}
	
	
	public function __toString(){
		$syns=null;
		foreach ($this->synonyms as $syn){
			$syns.=$syn.';';
		}
		
		$xrefstring=null;
		foreach ($this->xref as $key=>$value){
			$xrefstring.=$value.';';
		}
		
		$is_astring=null;
		foreach ($this->is_a as $key=>$value){
			$is_astring.=$value.';';
		}
		
		
		return 'id: '.$this->getId().' Name: '.$this->getName().' Def: '.$this->getDef()
		.' Comm: '.$this->getComment().' Syns: '.$syns.' Xrefs: '.$xrefstring.'Is_a: '.$is_astring.'Ver: '.$this-getVer();
	}
	
	public function jsonSerialize()
	{
		return get_object_vars($this);
	}

}

?>