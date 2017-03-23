<!DOCTYPE html>
<html>
<head>
<title>RDCIT Browser Detect</title>
<script src="js/jquery.min.js"></script>
<script type="text/javascript">
jQuery(document).ready(function(){

	var FileAPI = typeof FileReader != 'undefined';
	var ServerSentEvents = typeof EventSource !== 'undefined';


function ccheck(){
	var test_canvas = document.createElement("canvas") //try and create sample canvas element
	var canvascheck=(test_canvas.getContext)? true : false;
	var canvasField = jQuery("#canvassupport");
	
	if(canvascheck){
		canvasField.addClass("success");
		canvasField.text('Your browser supports HTML5 canvas.');

	}
	else{
		canvasField.addClass("error");
		canvasField.text('Your browser DOES NOT support HTML5 canvas.');
	}	
}	

function mediacheck(){

	var test_audio= document.createElement("audio") //try and create sample audio element
	var test_video= document.createElement("video") //try and create sample video element
	var mediasupport={audio: (test_audio.play)? true : false, video: (test_video.play)? true : false}

	var videoField = jQuery("#videosupport");
	var audioField = jQuery("#audiosupport");

	
	if(mediasupport.audio){
		audioField.addClass("success");
		audioField.text('Your browser supports HTML5 audio elements.');
	}
	else{
		audioField.addClass("error");
		audioField.text('Your browser DOES NOT support HTML5 audio elements.');
	}

	if(mediasupport.video){
		videoField.addClass("success");
		videoField.text('Your browser supports HTML5 video elements.');
	}
	else{
		videoField.addClass("error");
		videoField.text('Your browser DOES NOT support HTML5 video elements.');
	}
	
}

function cdmandsockets(){
	var CDM = !!window.postMessage;
	var WebSockets = !!window.WebSocket;
	
	if(CDM){
		jQuery("#cdmsupport").addClass("success");
		jQuery("#cdmsupport").text('Your browser supports HTML5 CDM.');
	}
	else{
		jQuery("#cdmsupport").addClass("error");
		jQuery("#cdmsupport").text('Your browser DOES NOT support HTML5 CDM.');
	}
	
	if(WebSockets){
		jQuery("#websocketssupport").addClass("success");
		jQuery("#websocketssupport").text('Your browser supports WebSockets.');
	}
	else{
		jQuery("#websocketssupport").addClass("error");
		jQuery("#websocketssupport").text('Your browser DOES NOT support WebSockets.');
	}	
}

function checkServerStatus()
{
	var url = "https://portal.rd.trc.nihr.ac.uk";
    var script = document.body.appendChild(document.createElement("script"));
    script.onload = function()
    {
        portalvis(true);
    };
    script.onerror = function()
    {
        portalvis(false);
    };
    script.src = url;

    var url2 = "https://gateway.rd.trc.nihr.ac.uk";
    var script2 = document.body.appendChild(document.createElement("script"));
    script2.onload = function()
    {
        gatewayvis(true);
    };
    script2.onerror = function()
    {
        gatewayvis(false);
    };
    script2.src = url2;
  
}

function insertMessage(){
	jQuery("#sendInfo").val("Thank you for submitting your browser info");
	jQuery("#sendInfo").attr("disabled", true);
}


function portalvis(isPortalVisible){

		
	if(isPortalVisible){
		jQuery("#portalvisible").addClass("success");
		jQuery("#portalvisible").text('YES');
	}
	else{
		jQuery("#portalvisible").addClass("error");
		jQuery("#portalvisible").html('NO <a href="https://portal.rd.trc.nihr.ac.uk" target="_blank">Manual test</a>');
	}
}

function gatewayvis(isGatewayVisible){

	
	if(isGatewayVisible){
		jQuery("#gatewayvisible").addClass("success");
		jQuery("#gatewayvisible").text('YES');
	}
	else{
		jQuery("#gatewayvisible").addClass("error");
		jQuery("#gatewayvisible").html('NO <a href="https://gateway.rd.trc.nihr.ac.uk" target="_blank">Manual test</a>');
	}
}

function sendInfo(){
var bname = jQuery('#bname').text();
var bver = 	jQuery('#bver').text();
var platform = 	jQuery('#platform').text();
var portalvisible = "";
var gatewayvisible = "";
var canvassupport = "";
var videosupport = "";
var audiosupport = "";
var cdmsupport = "";
var websocketsupport = "";
var ipaddress = jQuery('#ipaddress').text();


if(jQuery('#portalvisible').hasClass('success')){
	portalvisible = "Yes";
}
else {
	portalvisible = "No";
}
if(jQuery('#gatewayvisible').hasClass('success')){
	gatewayvisible = "Yes";
}
else {
	gatewayvisible = "No";
}

if(jQuery('#canvassupport').hasClass('success')){
	canvassupport = "Yes";
}
else {
	canvassupport = "No";
}

if(jQuery('#videosupport').hasClass('success')){
	videosupport = "Yes";
}
else {
	videosupport = "No";
}

if(jQuery('#audiosupport').hasClass('success')){
	audiosupport = "Yes";
}
else {
	audiosupport = "No";
}

if(jQuery('#cdmsupport').hasClass('success')){
	cdmsupport = "Yes";
}
else {
	cdmsupport = "No";
}

if(jQuery('#websocketssupport').hasClass('success')){
	websocketsupport = "Yes";
}
else {
	websocketsupport = "No";
}

var browserInfo = {"ip":ipaddress,"bname":bname,"bver":bver,"platform":platform,"portalvisible":portalvisible,"gatewayvisible":gatewayvisible,
		"canvassupport":canvassupport,"videosupport":videosupport,"audiosupport":audiosupport,"cdmsupport":cdmsupport,"websocketsupport":websocketsupport}; 

jQuery.ajax({
    url: "logging.php"
,   type: 'POST'
,   contentType: 'application/json'
,   data: JSON.stringify(browserInfo)
,   success: function(data) {
	insertMessage();
}
});

}

checkServerStatus();
ccheck();
mediacheck();
cdmandsockets();

jQuery("#sendInfo").click(function (){
	sendInfo();
});

});


</script>


<style>
.success{
color:green;
}
.error{
color:red;
}
table{
    margin-left:auto; 
    margin-right:auto;

}
table thead{
	background-color: #FFE853;
}
.odd{
	background-color: #FFF4A5;
}

.even{
	background-color: #FFED78;
}
  td{
  padding:2px;
  }
</style>

</head>
<body>




<?php
require_once 'browser.class.php';


$browser = new Browser();

$br = $browser->getBrowser();
$platform = $browser->getPlatform();
$br_version = $browser->getVersion();

function get_ip_address() {
	// check for shared internet/ISP IP
	if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
		return $_SERVER['HTTP_CLIENT_IP'];
	}

	// check for IPs passing through proxies
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		// check if multiple ips exist in var
		if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
			$iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			foreach ($iplist as $ip) {
				if (validate_ip($ip))
					return $ip;
			}
		} else {
			if (validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
	}
	if (!empty($_SERVER['HTTP_X_FORWARDED']) && validate_ip($_SERVER['HTTP_X_FORWARDED']))
		return $_SERVER['HTTP_X_FORWARDED'];
	if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
		return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
	if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
		return $_SERVER['HTTP_FORWARDED_FOR'];
	if (!empty($_SERVER['HTTP_FORWARDED']) && validate_ip($_SERVER['HTTP_FORWARDED']))
		return $_SERVER['HTTP_FORWARDED'];

	// return unreliable ip since all else failed
	return $_SERVER['REMOTE_ADDR'];
}

/**
 * Ensures an ip address is both a valid IP and does not fall within
 * a private network range.
 */
function validate_ip($ip) {
	if (strtolower($ip) === 'unknown')
		return false;

	// generate ipv4 network address
	$ip = ip2long($ip);

	// if the ip is set and not equivalent to 255.255.255.255
	if ($ip !== false && $ip !== -1) {
		// make sure to get unsigned long representation of ip
		// due to discrepancies between 32 and 64 bit OSes and
		// signed numbers (ints default to signed in PHP)
		$ip = sprintf('%u', $ip);
		// do private network range checking
		if ($ip >= 0 && $ip <= 50331647) return false;
		if ($ip >= 167772160 && $ip <= 184549375) return false;
		if ($ip >= 2130706432 && $ip <= 2147483647) return false;
		if ($ip >= 2851995648 && $ip <= 2852061183) return false;
		if ($ip >= 2886729728 && $ip <= 2887778303) return false;
		if ($ip >= 3221225984 && $ip <= 3221226239) return false;
		if ($ip >= 3232235520 && $ip <= 3232301055) return false;
		if ($ip >= 4294967040) return false;
	}
	return true;
}

$ip=get_ip_address();



?>

<br/><br/>
<form method="post">
<table id="infotable">
<thead><tr><td colspan="3"><h3>Detecting browser info</h3></td></tr></thead>
<tbody>
<tr class="odd"><td>1.</td><td><b>Browser name:</b></td><td id="bname"><?php echo $br;?></td></tr>
<tr class="even"><td>2.</td><td><b>Browser version:</b></td><td id="bver"><?php echo $br_version;?></td></tr>
<tr class="odd"><td>3.</td><td><b>Platform:</b></td><td id="platform"><?php echo $platform;?></td></tr>
<tr class="even"><td>4.</td><td><b>Is portal visible?</b></td><td id="portalvisible">Testing connection...</td></tr>
<tr class="odd"><td>5.</td><td><b>Is gateway visible?</b></td><td id="gatewayvisible">Testing connection...</td></tr>

<tr class="even"><td>6.</td><td><b>HTML5 canvas support:</b></td><td id="canvassupport">Testing browser...</td></tr>
<tr class="odd"><td>7.</td><td><b>HTML5 video support:</b></td><td id="videosupport">Testing browser...</td></tr>
<tr class="even"><td>8.</td><td><b>HTML5 audio support:</b></td><td id="audiosupport">Testing browser...</td></tr>
<tr class="odd"><td>9.</td><td><b>HTML5 Cross-document Messaging support:</b></td><td id="cdmsupport">Testing browser...</td></tr>
<tr class="even"><td>10.</td><td><b>HTML5 WebSockets support:</b></td><td id="websocketssupport">Testing browser...</td></tr>
<tr class="odd"><td>11.</td><td><b>IP address:</b></td><td id="ipaddress"><?php echo $ip;?></td></tr>
<tr><td colspan="3"><input id = "sendInfo" type="button" value="Submit browser info"/></td></tr>
</tbody>
</table>

</form>




</body>
</html>