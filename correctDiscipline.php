<?
include_once('connect.php');
include_once('functions.php');
include_once('class.php');

$reimport = true;
$debug = !true;
if ($reimport || $conn->query("SELECT COUNT(*) FROM Discipline")->fetchColumn() == 0 || 1)
{
	$ct = 1;
	if ($debug)
		echo "<table><tr><th>Discipline</th><th>Gender</th><th>Min Weight</th><th>Max Weight</th><th>Wunit</th><th>Distance</th><th>Dunit</th><th>Team ? </th><th>Category</th><th>Rest</th><th>Sport</th>";
	foreach ($disciplines as $discipline)
	{
		$spo = $discipline[1];
		$disc = explodeDiscipline($discipline[0], $spo);
		foreach($disc as $key => $val)
			$$key = $val;

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
			echo "Sport created: $spo->sname: $spo->sid <br>";
			$sid = $spo->sid;
			$spo = $spo->sname;
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
			foreach ($stt as $disci)
			{
				if($disci->compare($disc))
				{
					$create = false;
					$write = $disci->display();
					echo  "Founded: ".$write[1];
					$did = $disci->did;
					break;
				}
			}
		}
		unset($stt);
		if ($create)
		{
			$discarray = array_merge($disc, array('sid' => $sid));
			$test = Discipline::insert($discarray);		
			if($test)
			{
				$write = $test->display();
				echo "Discipline created: ".$write[1]."/".$test->sid ."<br>" ;
			}	
			else
				echo "Failed to create $discipline[0]<br>";
		}
		$ct ++;
	}
	if ($debug)
		echo "</table>";
}

?>
