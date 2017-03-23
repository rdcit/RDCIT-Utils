<?php
require_once 'browser.class.php';


$browser = new Browser();

echo $browser->__toString();
echo "<br/>";
echo $browser->getVersion();
echo "<br/>";

?>