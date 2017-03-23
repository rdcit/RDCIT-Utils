<?php
require_once 'includes/connection.inc.php';
require_once 'includes/html_top.inc.php';
require_once 'settings/db_settings.php';


try {
	$dbh = new PDO("mysql:dbname=$db;host=$dbhost", $dbuser, $dbpass );
}catch (PDOException $e) {
	echo 'Connection failed: ' . $e->getMessage();
}


?>

<div id="sidebar">
Select a concept and a group to display all items.
  </div>
<div id="main">



<script type="text/javascript">
jQuery(document).ready(function($){

	startDPC();
    //loadConcepts();

	jQuery( 'body' ).on('change', '#conceptselect', function () {
		conceptChange();
    	});

	jQuery( 'body' ).on('change', '#groupselect', function () {
		searchItem();
    	});

	jQuery("body").on("keyup", ":input", function() {
		searchItem();
	});

	
	
});
</script>
<?php 
//var_dump($_SESSION);
?>
<div id="conceptandgroupselectdiv">
<table class="noborder"><tr><td>Concepts</td><td> <select id="conceptselect"></select></td></tr>
<tr><td>Groups</td><td><select id="groupselect"><option>(ALL GROUPS)</option></select></td></tr>
<tr><td>Items</td><td><input type="text" id="iteminput"/></td></tr>

</table></div>
<br/>
<br/>
<div id="item_list_div" name="item_list_div"></div>


</div>



<?php 
require_once 'includes/html_bottom.inc.php';
?>