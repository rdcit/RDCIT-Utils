<?php
header ( "Content-Type: text/html; charset=utf-8" );
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" href="css/style.css" type="text/css" />
<title>RDCIT Data Plan Composer</title>
<script src="js/jquery.min.js"></script>
<script src="js/jquery-ui.min.js"></script>
<script src="js/rdcit_dpc.js"></script>

</head>
<body>
<div id="container">
  <div id="header"> <a href="#">RDCIT Data Plan Composer</a> </div>
  <div id="menu"> <a href="index.php">HOME</a> &nbsp; &nbsp; &nbsp; &nbsp; <a href="items.php">SELECT ITEMS</a> &nbsp; &nbsp; &nbsp; &nbsp; <a href="basket.php">MANAGE BASKET</a> &nbsp; &nbsp; &nbsp; &nbsp; <a href="createdp.php">CREATE DATAPLAN</a> &nbsp; &nbsp; &nbsp; &nbsp; <a href="forms.php">CREATE FORMS</a>  </div>

<?php 
//			if ($_SESSION['shopping_cart']==null) echo '<img src="./images/basket_empty.png" id="basketstate">';
//			else echo '<img src="./images/basket_notempty.png" id="basketstate">';
?>