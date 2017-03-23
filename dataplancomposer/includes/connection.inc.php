<?php
session_start();
error_reporting(E_ERROR | E_WARNING | E_PARSE);
if ( !function_exists('get_magic_quotes_gpc') || get_magic_quotes_gpc() ) {
	$_GET = array_map('own_strip', $_GET);
	$_POST = array_map('own_strip', $_POST);
}
function own_strip($elem) {
	if ( is_array($elem) ) {
		return array_map("own_strip", $elem);
	}
	return stripslashes($elem);
}

function redirect($url_and_query) {
	// This is the proper browser redirecting
	header("HTTP 302 Found");
	// Location string must be properly urlencoded
	header("Location: ".$url_and_query);
	die();
}

?>