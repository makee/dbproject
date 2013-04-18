<?php
include_once('connect.php');
$begin = time();
$reimport = false;
$debug = false;


function readCSV($csvFile)
{
	$file_handle = fopen($csvFile, 'r');
	while (!feof($file_handle) ) 
	{
		$line_of_text[] = fgetcsv($file_handle, 1024, ";");
	}
	fclose($file_handle);
	return $line_of_text;
}


function IDgen($orig, $table, $field, $spe=false) 
{ 
	global $conn;
	$unique_ref_length = $spe?7:3;  
	$unique_ref_found = false;  
	$possible_chars = "23456789BCDFGHJKMNPQRSTVWXYZ";  
	$orig = preg_replace('/ /', '', $orig);
    $IDText = !$spe?strtoupper(substr($orig,0,3)). '-':"";
	while (!$unique_ref_found) {  
    	$unique_ref = $IDText;  
    	$i = 0;  
    	while ($i < $unique_ref_length) {  
        	$char = substr($possible_chars, mt_rand(0, strlen($possible_chars)-1), 1);  
        	$unique_ref .= $char;  
        	$i++;  
    	}  
    	$query = "SELECT $field FROM $table WHERE $field='?'";  
    	$result = $conn->prepare($query);  
		$result->execute(array($unique_ref));
    	if ($result->rowCount() == 0) 
		{  
	        $unique_ref_found = true;  
    	}  
	}  
    return $unique_ref;
}

$dir = "data";
if (is_dir($dir)) 
{
	chdir($dir);
	foreach (glob("*.csv") as $file) 
	{
		//if($file == 'medals.csv' || $file == 'participants.csv'){
		if($file == 'athlete.csv'){
		//if(1){
			$path_parts = pathinfo($file); 
  		$filename = $path_parts['filename']; 
		$filename = strtolower($filename);
			$$filename = readCSV($file);
    }
	}
}

// Sport import
if ($reimport || $conn->query("SELECT COUNT(*) FROM Sport")->fetchColumn() == 0)
{
	foreach ($sports as $sport)
	{
		if ($sport[0] != "name" && $sport[0] != NULL && $sport[0] != "Dominican Republic")
		{
			$SID = IDgen($sport[0], "Sport", "sid"); 
			$conn->query("INSERT INTO Sport (sid, sname) VALUES ('$SID', '$sport[0]')");
		}
	}
}
if ($reimport || $conn->query("SELECT COUNT(*) FROM Discipline")->fetchColumn() == 0)
{
	if ($debug)
		echo "<table>";
	foreach ($disciplines as $discipline)
	{
		$sportmatch = $conn->query("SELECT sid FROM Sport WHERE sname LIKE '$discipline[1]'")->fetchColumn(); 
		if ($sportmatch != NULL )
		{
			$DID = IDgen($discipline[0], "discipline", "did", true);
			$conn->prepare("INSERT INTO Discipline (did, dname, sid) VALUES ('$DID', ?, '$sportmatch')")->execute(array($discipline[0]));
		}
		elseif ($debug)
		{
			echo "<tr>";
			echo "<td>$discipline[0]</td>";
			echo "<td>$discipline[1]</td>";
			echo "<td>" . $sportmatch ."</td>";
			echo "</tr>";
		}
	}
	if ($debug)
		echo "</table>";
}
if ($reimport || $conn->query("SELECT COUNT(*) FROM Country")->fetchColumn() == 0)
{
	foreach ($countries as $country)
	{
		if ($country[1] != NULL)
			$conn->prepare("INSERT INTO Country (iocCode, cname) VALUES (?, ?)")->execute(array($country[1], $country[0]));
		elseif ($debug)
			echo $country[0];
	}
}
if ($reimport || $conn->query("SELECT COUNT(*) FROM Game")->fetchColumn() == 0)
{
	if ($debug)
		echo "hh<table>";
	foreach ($games as $game)
	{
		$countrymatch = $conn->query("SELECT iocCode FROM Country WHERE cname LIKE '$game[6]'")->fetchColumn(); 
		if ($countrymatch != "")
		{
			$GID = $game[0] . strtoupper($game[1]) . $countrymatch;
			echo "$GID $game[0] $game[1] $game[5] $countrymatch <br>";
			$conn->prepare("INSERT INTO Game (gid, year, season, city, iocCode) VALUES ('$GID', '$game[0]', '$game[1]', ?, '$countrymatch');")->execute(array($game[5]));
		}
		elseif ($debug)
		{
			echo "<tr>";
			echo "<td>$game[0]</td>";
			echo "<td>$game[1]</td>";
			echo "<td>$game[2]</td>";
			echo "<td>$game[3]</td>";
			echo "<td>" . $countrymatch ."</td>";
			echo "</tr>";
		}
	}
	if ($debug)
		echo "</table>";
}
if ($reimport || $conn->query("SELECT COUNT(*) FROM Athlete")->fetchColumn() == 0)
{
	foreach ($athlete as $athletee)
	{
		$athl = $athletee[0];
		if ($athl != NULL && $athl != 'name' && $athl != "")
		{
			$AID = IDGen($athl, 'Athlete', 'aid', true);
			$conn->prepare("INSERT INTO Athlete (aid, aname) VALUES (?, ?)")->execute(array($AID, $athl));
		}
		elseif ($debug)
			echo $athl;
	}
}
/*
if ($reimport || $conn->query("SELECT COUNT(*) FROM Participation")->fetchColumn() == 0 || 1)
{
	$ct = 1;
	echo "<table>";
	foreach ($medals as $medal)
	{
		if ($ct > 7385)
		{
			$coun = $medal[0];
			$spo = $medal[1];
			$disc = $medal[3];
			$disc = preg_replace('/^ | $/', '', $disc);
			if ((!preg_match('/medal/i', $medal[4]) || $medal[4] == "" || $medal[4] == NULL) && preg_match('/medal/i', $medal[5]))
			{
				$misc = $medal[4];
				for ($k=5; $k<count($medal)-1;$k++)
					$medal[$k-1] = $medal[$k];
				unset($medal[$k]);

			}
			$med = $medal[4];
			preg_match('/^\d{4}/', $medal[2], $ye);
			preg_match('/winter|summer/i', $medal[2], $seas);
			$seas = preg_replace(array('/winter/i', '/summer/i'), array('w', 's'), $seas[0]);
			$med = preg_replace(array('/Gold medal/i', '/Silver medal/i', '/Bronze medal/i', '/^$/'), array(1, 3, 3, 0), $med); 
			$medGID = $conn->query("SELECT gid FROM Game WHERE year = '$ye[0]' AND season = '$seas'")->fetchColumn();
			$medIOC = $conn->prepare("SELECT iocCode FROM Country WHERE cname = ?");
			$medIOC->execute(array($coun));
			$medIOC = $medIOC->fetchColumn();
			$medDS = $conn->prepare("SELECT d.did, s.sid FROM Discipline d, Sport s WHERE dname LIKE ? AND d.sid = s.sid AND s.sname = ?");
			$medDS->execute(array("$disc%", $spo));
			$medDS = $medDS->fetch(PDO::FETCH_NUM);
				if (empty($medDS))
				{
					$medSID = $conn->prepare("SELECT sid FROM sport WHERE sname = ?");
					$medSID->execute(array($spo));
					$medSID = $medSID->fetchColumn();
					if (empty($medSID))
					{
						$stmt = $conn->prepare("INSERT INTO Sport (sid, sname) VALUES (?, ?)");
						$medSID = IDgen($spo, 'Sport', 'sid');
						$stmt->execute(array($medSID, $spo));
						echo " Sport created: $spo<br>";
					}
					$stmt = $conn->prepare("INSERT INTO Discipline (did, dname, sid) VALUES (?, ?, ?)");
					$medDID = IDgen($disc, 'Discipline', 'did', true);
					$stmt->execute(array($medDID, $disc, $medSID));
					echo " Discipline created: $spo/$disc<br>";
					//echo "Missing $disc<br>";
				}
			else
			{
				$medSID = $medDS[1];
				$medDID = $medDS[0];
			}
			$athstr = "";
			for ($zz=5; $zz<count($medal)-1; $zz++)
			{
				$medAID = $conn->prepare("SELECT aid FROM Athlete WHERE aname LIKE ?");// COLLATE utf8_general_ci");
				$medAID->execute(array("$medal[$zz]"));
				$medAID = $medAID->fetchColumn();
				if (!$medAID)
				{
					echo "$ct Missing $medal[$zz]<br>";
				}
				else
				{
					if (!$conn->query("SELECT aid FROM Participation where aid = '$medAID' AND did = '$medDID' AND gid = '$medGID'")->fetchColumn())
					{
						$statmnt = $conn->prepare("INSERT INTO Participation (aid, did, gid, medal) VALUES (?, ?, ?, ?)");
						$statmnt->execute(array($medAID, $medDID, $medGID, $med));
					}
				}///
				/*if ($zz != 5)
					$athstr .= "<br>";
				$athstr .= $medAID;//
			}
			//echo "<tr><td>$medGID</td><td>$medIOC</td><td>$medSID</td><td>$medDID</td><td>$med</td><td>$athstr</td></tr>";
		}
		$ct++;
	}
	echo "</table>";
}
/*
if ($reimport || $conn->query("SELECT COUNT(*) FROM Represents")->fetchColumn() == 0)
{
	echo "<pre>";
	print_r($participants);
	echo "</pre>";
}

*/
echo time()-$begin;
?>
