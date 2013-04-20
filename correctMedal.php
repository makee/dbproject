<?
include_once('connect.php');
include_once('functions.php');

$reimport = true;
$debug = !true;

			//$exec = array_merge((array)$DID, $exp, (array)$sportmatch);
			//$query =  "INSERT INTO Discipline (did, dgender, dminweight, dmaxweight, dwunit, ddist, ddunit, dteam, dcat, dname, sid) VALUES ('$DID', '$dgender', '$dminweight', '$dmaxweight', '$dwunit', '$ddist', '$ddunit', '$dteam', '$dcat', '$drest', '$sportmatch')";

unset($medals[0]);
if ($reimport || $conn->query("SELECT COUNT(*) FROM Participation")->fetchColumn() == 0 || 1)
{
	$ct = 1;
	echo "<table>";
	foreach ($medals as $medal)
	{
		if ($ct >= $_GET['min'] && $ct < $_GET['max'])
		{
			$coun = $medal[0];
			$spo = $medal[1];
			$disc = $medal[3];
			echo "$ct $spo: $disc <br>";
			if ($disc == "")
				continue;
			$disc = explodeDiscipline($disc, $spo);
			foreach ($disc as $key => $val)
			{
				$$key = $val;
			}
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
	
			$medGID = Game::findGame($ye[0], $seas);
			echo $medGID->writeFullGame()."<br>";
			$medIOC = Country::findCountry($coun);
			if ($medIOC)
				echo $medIOC->iocCode."<br>";
			else
				echo "$coun not found<br>";
			$medSID = Sport::findSport($spo);
			if($medSID)
			{
				$sid = $medSID->sid;
				echo "Sport found: ".$sid. " ".$medSID->sname. "<br>";	
			}
			else 
			{
				echo "Sport not found: $spo<br>";
				$spo = Sport::insert($spo);
				echo "Sport created: $spo->sname: $spo->sid";
				$spo = $spo->sname;
				$sid = $spo->sid;
			}
			$query = "SELECT d.*, s.sid FROM Discipline d, Sport s WHERE s.sid = d.sid AND s.sname LIKE '$spo'";
			$query .= " AND dname LIKE ?";
			$query .= " AND dgender LIKE '$dgender'";
			$stt = $conn->prepare($query);
			$stt->execute((array)$drest);
			$stt = $stt->fetchAll(PDO::FETCH_CLASS, "discipline");	
			$create = true;
			if (!empty($stt))
			{
				foreach ($stt as $discipline)
				{
					if($discipline->compare($disc))
					{
						$create = false;
						echo  "Founded: ".$discipline->display()[1];
						$did = $discipline->did;
						break;
					}
				}
			}
			unset($stt);
			if ($create)
			{
				$newdisc = Discipline::insert(array_merge($disc, array('sid' => $sid)));		
				echo "Creation of: ". $newdisc->display()[1]. "(".$newdisc->display()[0].")"."<br>";
				$did = $newdisc->did;
			}
			if($_GET['athl'] == 'oui'){
			for ($zz=5; $zz<count($medal)-1; $zz++)
			{
				$athlete = Athlete::findAthlete($medal[$zz]);
				if (!$athlete)
				{
					echo "Missing $medal[$zz]<br>";
					$newathl = Athlete::insert($medal[$zz]);	
					echo "Creation of athlete: ".$newathl->aid. " ".$newathl->aname. "<br>";
					$aid = $newathl->aid;
				}
				else
					$aid = $athlete->aid;
				if ($_GET['part'] == 'oui')
				{
				$participation = Participation::insert($aid, $did, $gid, $med);
				echo $participation?"Participation insertion successfully completed<br>":"Participation insertion failed<br>";
				}
				/*{
					if (!$conn->query("SELECT aid FROM Participation where aid = '$medAID' AND did = '$medDID' AND gid = '$medGID'")->fetchColumn())
					{
					//	$statmnt = $conn->prepare("INSERT INTO Participation (aid, did, gid, medal) VALUES (?, ?, ?, ?)");
					//	$statmnt->execute(array($medAID, $medDID, $medGID, $med));
					}
				}*/
			}
			}

			echo "<br>";
		}
		$ct++;
	}
	echo "</table>";
}

?>
