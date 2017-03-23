<?php
//@ini_set("display_errors", E_ALL ^ E_NOTICE);
require_once '../includes/connection.inc.php';
require_once '../settings/db_settings.php';

function response($code, $message, $responseData, $responseData2){
	
	$responseArray = array (
			'serviceProvider' => 'RDCIT Cambridge',
			'serviceName' => 'dpcrest',
			'errorCode' => $code,
			'message' => $message,
			'data' => $responseData,
			'data2' => $responseData2
	);
//	header ( "Access-Control-Allow-Origin: *" );
	header ( 'Content-Type: application/json' );
	echo json_encode ( $responseArray );
	die();
}


//connect to the database
try {
	$dbh = new PDO("mysql:dbname=$db;host=$dbhost; charset=utf8", $dbuser, $dbpass );
}catch (PDOException $e) {
	response(1, 'Connection failed: ' . $e->getMessage(), null, null);
}



if(isset($_POST['action'])){
	
	if($_POST['action']=="getConcepts"){
		$sql = "SELECT DISTINCT concept FROM data_item_response_options ORDER BY concept";
		
		$sth = $dbh->prepare($sql);
		$sth->execute();
		
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		response(0,"",$result,null);
		
	}
	else if($_POST['action']=="getGroups"){

		if(isset($_POST['concept']) && !empty($_POST['concept'])){
			$sql = "SELECT DISTINCT concept_group FROM data_item_response_options WHERE concept = :concept ORDER BY concept_group";
			
			$sth = $dbh->prepare($sql);
			$sth->bindParam(':concept', $_POST['concept']);
			$sth->execute();
			
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);
			
			response(0,"",$result,null);
				
		}
		else{
			response(1,"Missing concept.",null,null);
		}
		
	}
	else if($_POST['action']=="getItems"){
	
		//1. have group, have item
		if(isset($_POST['group']) && $_POST['group'] !="(ALL GROUPS)" && isset($_POST['item']) && !empty($_POST['item'])){
			$sql = "SELECT data_item_id, data_item_name, description, concept, concept_group 
					FROM data_item_response_options 
					WHERE concept_group = :group
					AND data_item_name LIKE :item
					GROUP BY data_item_name
					ORDER BY data_item_name";
				
			$sth = $dbh->prepare($sql);
			$keyword = $_POST['item'];
			$keyword = "%$keyword%";
			$sth->bindParam(':group', $_POST['group']);
			$sth->bindParam(':item', $keyword);
			$s = 1;
		}
		//2. have concept, no group, have item
		else if(isset($_POST['concept']) && $_POST['concept'] !="(ALL CONCEPTS)" && $_POST['group'] == "(ALL GROUPS)" && 
				isset($_POST['item']) && !empty($_POST['item'])){
			$sql = "SELECT data_item_id, data_item_name, description, concept, concept_group
					FROM data_item_response_options
					WHERE concept = :concept
					AND data_item_name LIKE :item
					GROUP BY data_item_name
					ORDER BY data_item_name";
		
			$sth = $dbh->prepare($sql);
				
			$keyword = $_POST['item'];
			$keyword = "%$keyword%";
				
			$sth->bindParam(':concept', $_POST['concept']);
			$sth->bindParam(':item', $keyword);
			$s = 2;
		}
		
		//3. have concept, have group, no item 
		else if(isset($_POST['concept']) && $_POST['concept'] != "(ALL CONCEPTS)" && isset($_POST['group']) && $_POST['group'] !="(ALL GROUPS)" && empty($_POST['item'])){
			
			$sql = "SELECT data_item_id, data_item_name, description, concept, concept_group
					FROM data_item_response_options
					WHERE concept = :concept
					AND concept_group = :group
					GROUP BY data_item_name
					ORDER BY data_item_name";
			
			$sth = $dbh->prepare($sql);
			$sth->bindParam(':concept', $_POST['concept']);
			$sth->bindParam(':group', $_POST['group']);
			$s = 3;
		}
		//4. have item, no concept
		else if(!empty($_POST['item']) && $_POST['concept'] == "(ALL CONCEPTS)"){
			$sql = "SELECT data_item_id, data_item_name, description, concept, concept_group
					FROM data_item_response_options
					WHERE data_item_name LIKE :item
					GROUP BY data_item_name
					ORDER BY data_item_name";
			
			$sth = $dbh->prepare($sql);
			$keyword = $_POST['item'];
			$keyword = "%$keyword%";
			$sth->bindParam(':item', $keyword);
			$s = 4;
		}

		
		
		else{
			response(1,"Missing parameters.",null,null);
		}
	
		
		
		$sth->execute();
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		
		$basket = $_SESSION['shopping_basket'];
		$response = array();
		if(count($basket)>0){
			foreach($result as $items){
				$additem = true;
				$item_id = $items['data_item_id'];
				foreach($basket as $item){
					if ($item['id'] == $item_id) $additem = false;
				}
				if($additem){
					$response[]=$items;
				}
			}
		}
		else{
			$response = $result;
		}
		response(0,$s,$response,null);
	}
	else if($_POST['action'] == "getItemBasket"){
		$basket = $_SESSION['shopping_basket'];
		
		if(count($basket) == 0){
			response(0,"",'<p>There are no items in the item basket.</p>',null);
		}
		
		$_SESSION['display_controllers']['codedvalues'] = $_POST['codedvalues'];
		$_SESSION['display_controllers']['datatype'] = $_POST['datatype'];		
		$_SESSION['display_controllers']['dataunit'] = $_POST['dataunit'];
		$_SESSION['display_controllers']['responseoptions'] = $_POST['responseoptions'];

		$columns = 1;
		foreach($_SESSION['display_controllers'] as $dp){
			if($dp == "true") $columns++;
		}
		
		$html = '<table name="dataplan_preview_table" id="dataplan_preview_table" cellspacing="0"><thead><tr><td colspan="'.$columns.
		'">Variable Description</td><td colspan="5" class="coding_light">Clinical Coding</td></tr>';
		$html.='<tr><td>Data Item Name</td>';
				
		
		if($_SESSION['display_controllers']['datatype'] == "true") $html .= '<td>Type</td>';
		if($_SESSION['display_controllers']['dataunit'] == "true") $html .= '<td>Unit</td>';
		if($_SESSION['display_controllers']['responseoptions'] == "true") $html .= '<td>Response Options</td>';
		if($_SESSION['display_controllers']['codedvalues'] == "true") $html .= '<td>Coded Values</td>';
		
		
		$html.= '<td class="coding_light">Ontology Term</td><td class="coding_light">Ontology Code</td><td class="coding_light">Ontology Type</td><td class="coding_light">Ontology Modifier</td></tr><tbody>';
		
		$sql="";
		$row=3;
		$rowclass = "odd";

		foreach($basket as $basketItem){
			$sql="SELECT ";
			$sql.="data_item_name";
			//." FROM data_item_response_options WHERE data_item_id=".intval($basketItem['id']);
			
			if($_SESSION['display_controllers']['datatype'] == "true") $sql .= ', data_type';
			if($_SESSION['display_controllers']['dataunit'] == "true") $sql .= ', unit';
			if($_SESSION['display_controllers']['responseoptions'] == "true") $sql .= ', response_options';
			if($_SESSION['display_controllers']['codedvalues'] == "true") $sql .= ', response_values';
			
			$sql.= ', ontology_type, ontology_term, ontology_code, ontology_modifier FROM data_item_response_options WHERE data_item_id= :itemid';
			
			if($_SESSION['display_controllers']['responseoptions'] == "false" && $_SESSION['display_controllers']['codedvalues'] == "false") $sql .= " LIMIT 1";
			$sth = $dbh->prepare($sql);
			$sth->bindParam(":itemid",intval($basketItem['id']));
			$sth->execute();
			$result = $sth->fetchAll(PDO::FETCH_ASSOC);
			
			foreach ($result as $res){
				$html.= '<tr><td class="itemname">'.$res['data_item_name'].'</td>';
				if($_SESSION['display_controllers']['datatype'] == "true") $html .= '<td class="datatype">'.$res['data_type'].'</td>';
				if($_SESSION['display_controllers']['dataunit'] == "true") $html .= '<td class="dataunit">'.$res['unit'].'</td>';
				if($_SESSION['display_controllers']['responseoptions'] == "true") $html .='<td class="responseoptions">'.$res['response_options'].'</td>';
				if($_SESSION['display_controllers']['codedvalues'] == "true") $html .= '<td class="responsevalues">'.$res['response_values'].'</td>';
				$html.= '<td class="oterm">'.$res['ontology_term'].'</td>';
				$html.= '<td class="ocode">'.$res['ontology_code'].'</td>';
				$html.= '<td class="otype">'.$res['ontology_type'].'</td>';
				$html.= '<td class="omod">'.$res['ontology_modifier'].'</td></tr>';
					
				$row++;
			
			}
			
			if ($rowclass == "odd") {
				$rowclass = "even";
			}
			else {
				$rowclass = "odd";
			}
			
			
		}
		
		
		
		
		
		
		
		$html.='</tbody></table>';
		response(0, "", $html, null);
		
		
	}
	
	
	
}
else{
	response(1,"Missing action parameter",null,null);
}


/* 
// read the category value from the url
if (isset ( $_GET ['catid'] )) {
	$category = intval($_GET ['catid']);
} // set default value for category
else {
	$category = null;
}

// read the sub category value from the url
if (isset ( $_GET ['subcatid'] )) {
	$subcat = intval($_GET ['subcatid']);
} // set default value for sub category
else {
	$subcat = null;
}

// read the data item value from the url
if (isset ( $_GET ['dataitemid'] )) {
	$dataitemid = intval($_GET ['dataitemid']);
} // set default value
else {
	$dataitemid = null;
}







if($category == null && $subcat == null && $dataitemid == null){
	$sql = "SELECT * FROM rare_disease_categories ORDER BY Category";

}


else if($category != null && $subcat == null || $subcat == '_all_types_'){
	$sql="SELECT * FROM rare_disease_sub_categories WHERE CAT_ID LIKE '".$category."' ORDER BY Sub_Category";

}
else if($subcat != null ){
	
	$sql="SELECT c.SubCat_ID, cd.CONCEPT_ID, cc.CONCEPT_TYPE, c.Data_Item_ID, cd.data_item_id, cd.description_label, cd.relevance, cd.response, cd.unit
			FROM 
			rare_disease_data_concepts cc,
			rare_disease_data_items cd,
			rare_disease_subcat_items c
			WHERE
			cd.Concept_ID=cc.CONCEPT_ID AND
			c.Data_Item_ID=cd.data_item_id AND
			c.SubCat_ID=".$subcat;

}
else if($dataitemid != null ){

	$sql="SELECT *
			FROM
			rare_disease_data_options
			WHERE
			data_item_id=".$dataitemid;

}

else {
	$sql="";
}



$sth = $dbh->prepare($sql);
$sth->execute();

$result = $sth->fetchAll(PDO::FETCH_ASSOC);
$response = array();
$basket = $_SESSION['shopping_basket'];

if(count($basket)>0){
	foreach($result as $items){
		$additem = true;
		$item_id = $items['Data_Item_ID'];
		foreach($basket as $item){
			if ($item['id'] == $item_id) $additem = false;
		}
		if($additem){
			$response[]=$items;
		}
	}	
}
else{
	$response = $result;
}


 */


?>