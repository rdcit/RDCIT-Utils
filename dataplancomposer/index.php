<?php 
require_once 'includes/connection.inc.php';


if (!file_exists('created_files')) {
	//create directory if not exists
	mkdir('created_files', 0755, true);
}

if (!is_writable("created_files/")){
	die('Need write permission for created_files directory.');
}


require_once 'includes/html_top.inc.php';
require_once 'settings/db_settings.php';



?>


<div id="sidebar">
    <h1>Welcome</h1>
  </div>
<div id="main">
<br/>
Welcoming message and a brief summary of the upsides and features of the RDCIT Data Plan Composer. 
<br/>
<?php 
//var_dump($_SESSION['shopping_basket']);
?>
<br/><br/><br/><br/>
</div>






<?php 
require_once 'includes/html_bottom.inc.php';
?>