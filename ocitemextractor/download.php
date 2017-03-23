<?php
$file='items_octest_all.csv';
$ftype = filetype($file);
// Get a date and timestamp
$today = date("F j, Y, g:i a");
$time = time();
// Send file headers
header("Content-type: $ftype");
header("Content-Disposition: attachment;filename={$file}");
header("Content-Transfer-Encoding: binary");
header('Pragma: no-cache');
header('Expires: 0');
// Send the file contents.
set_time_limit(0);
readfile($file);

?>