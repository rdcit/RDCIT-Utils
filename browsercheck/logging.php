<?php
$logmes = json_decode(file_get_contents('php://input'),true);

$logEntry=Date("Y-m-d h:s")." IP: ".$logmes['ip'].
"; Platform: ".$logmes['platform']."; Browser: ".$logmes['bname']." ".$logmes['bver']."; Portal visible: ".$logmes['portalvisible'].
"; Gateway visible: ".$logmes['gatewayvisible']."; Canvas: ".$logmes['canvassupport']."; Audio: ".$logmes['audiosupport'].
"; Video: ".$logmes['videosupport']."; CDM: ".$logmes['cdmsupport']."; Websockets: ".$logmes['websocketsupport'].";";

$myfile = file_put_contents('logs.txt', $logEntry.PHP_EOL , FILE_APPEND);

?>