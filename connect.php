<?php
try {
   $conn = new PDO ( "sqlsrv:server = tcp:j29on7iafz.database.windows.net,1433; Database = olympics", "gr13", "{Olympics2013.}");
   $conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	echo 'Win !!'
}
catch ( PDOException $e ) {
   print( "Error connecting to SQL Server." );
   die(print_r($e));
}
?>
