<?php 
/**
 * Script for creating reports for OpenClinica users
 * @author Csaba Halmagyi
 * 
 * 
 */
ini_set('display_errors', '1');
error_reporting(E_ALL | E_STRICT);

require_once 'settings/db.inc.php';



$dbh = new PDO("pgsql:dbname=$db;host=$host", $user, $pass );
//check if the connection was successful
if ($dbh) echo "<br/>Connection successful<br/>";

echo '<b>Reading database USER details from '.$db.'</b><br/><br/>';
//statement for returning data
$sql2 = "SELECT DISTINCT us.user_name 
		FROM  user_account us INNER JOIN study_user_role sr ON us.user_name = sr.user_name 
		INNER JOIN Study st ON sr.study_id = st.study_id  WHERE us.user_name <> 'root'";
//statement for returning user names
//$sql2 = "SELECT DISTINCT user_name FROM  user_account";

//return user accounts
$response = $dbh->query($sql2);

//loop through user accounts
foreach ($response as $row){
	
	$dir = 'reports/'.$row['user_name'];
	//check if the user directories are exist
	if (!is_dir($dir)){
		mkdir($dir, 0777, true);
		echo 'Directory '.$dir.' has been created succesfully.<br/>';
	}
	else {
		echo 'Directory '.$dir.' exists.<br/>';
	}

	//statement for returning study data for current user
	$sql3="SELECT * FROM study st INNER JOIN study_user_role sr ON st.study_id = sr.study_id AND sr.USER_NAME = '".$row['user_name']."'";
	//collect database header information
	$headers = array();
	$qry = $dbh->prepare($sql3);
	$qry->execute();
	$qry->fetch(PDO::FETCH_ASSOC);
	$datafile = 'reports/'.$row['user_name'].'/'.$row['user_name'].'_'.date("d-m-Y").'.csv';
	$data = fopen($datafile, 'w');
	
	while ($row2 = $qry->fetch(PDO::FETCH_ASSOC))
	{
		if(empty($headers)){ // do it only once!
			$headers = array_keys($row2); // get the column names
			fputcsv($data, $headers); // put them in csv
		}
		// Export every row to a file
		fputcsv($data, $row2);
	}
	echo 'Data exported for user <b>'.$row['user_name']. '</b><br/>';	
	
	
}// end of user loop


?>