<?php
require_once 'includes/connection.inc.php';
require_once 'includes/html_top.inc.php';
require_once 'settings/db_settings.php';


try {
	$dbh = new PDO("mysql:dbname=$db;host=$dbhost", $dbuser, $dbpass );
}catch (PDOException $e) {
	echo 'Connection failed: ' . $e->getMessage();
}

//if ($dbh) echo 'Connected to the database!<br/>';
//var_dump($_SESSION);
?>

  <div id="sidebar">
    <p>You can reorder your items in the basket by dragging and dropping them.</p>
  </div>
<?php if (sizeof($_SESSION['shopping_basket'])>0){
	
?>

    <div id="main">
  
<script type="text/javascript">
jQuery(document).ready(function($){
	jQuery('table tbody').sortable({
	    helper: fixWidthHelper
	}).disableSelection();
	    
	function fixWidthHelper(e, ui) {
	    ui.children().each(function() {
	        $(this).width($(this).width());
	    });
	    return ui;
	}
});
</script>
<table class="noborder">
<tr>
<td><input type="button" onclick="emptyBasket()"/ value="Empty basket"></td>
<td><input type="button" onclick="removeItems()"/ value="Remove selected"></td>
<td><input type="button" onclick="saveItemOrder()"/ value="Save item order"></td>
</tr>
</table>

<div id="cartcontentdiv">
<table id="cartcontenttable" class="noborder">
<thead>
<tr><td colspan="5" style="text-align:center;"><b>Items in your basket</b></td></tr>
<tr><td>Select</td><td>ItemName</td><td>Description</td><td>Concept</td><td>Group</td></tr></thead>
<tbody>
<?php 
foreach ($_SESSION['shopping_basket'] as $items){
	echo '<tr class="odd"><td><input type="checkbox" class="cb-item" id="'.$items['id'].'"/></td><td>'.$items['name'].'</td><td>'.$items['descr'].'</td><td>'.$items['concept'].'</td><td>'.$items['group'].'</td></tr>';
}

?>

</tbody>
</table>

</div>
<table class="noborder">
<tr>
<td><input type="button" onclick="emptyBasket()"/ value="Empty basket"></td>
<td><input type="button" onclick="removeItems()"/ value="Remove selected"></td>
<td><input type="button" onclick="saveItemOrder()"/ value="Save item order"></td>
</tr>
</table>

</div>
<?php }
else echo '<div id="main"><br/><table class="noborder"><tr><td>Your shopping cart is empty!</td></tr></table><br/><br/><br/></div>';
?>
<?php 
require_once 'includes/html_bottom.inc.php';
?>