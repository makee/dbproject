<?php
$host = "eu-cdbr-azure-north-a.cloudapp.net";
$user = "b2ef0ef13409f8";
$pwd = "8368b3af";
$db = "olympics";
// Connect to database.
try {
//	$conn = new PDO( "mysql:host=$host;dbname=$db", $user, $pwd);
//    $conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	$conn = new PDO ( "sqlsrv:server = tcp:hyprus176b.database.windows.net,1433; Database = olympics", "gr13", "Db13indaplace..");
    $conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    }
catch(Exception $e){
	echo "<pre>";
    die(var_dump($e));
	echo "</pre>";
    }

/*$conn = new PDO( "mysql:host=$host;dbname=$db", $user, $pwd);
$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );*/
global $conn;
?>
