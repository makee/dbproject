<?
include_once('connect.php');
include_once('functions.php');

$reimport = true;
$debug = !true;
if ($reimport || $conn->query("SELECT COUNT(*) FROM Discipline")->fetchColumn() == 0 || 1)
{
	$ct = 1;
	if ($debug)
		echo "<table><tr><th>Discipline</th><th>Gender</th><th>Min Weight</th><th>Max Weight</th><th>Wunit</th><th>Distance</th><th>Dunit</th><th>Team ? </th><th>Category</th><th>Rest</th><th>Sport</th>";
	foreach ($disciplines as $discipline)
	{
		$sportmatch = $conn->query("SELECT sid FROM Sport WHERE sname LIKE '$discipline[1]'")->fetchColumn(); 
		/*if ($sportmatch != NULL )
		{
			$DID = IDgen($discipline[0], "discipline", "did", true);
		//	$conn->prepare("INSERT INTO Discipline (did, dname, sid) VALUES ('$DID', ?, '$sportmatch')")->execute(array($discipline[0]));
		}*/
		$exp = explodeDiscipline($dis[0], $disc[1]);
		if ($debug)
		{
			echo "<tr>";
			echo "<td>$discipline[0]</td>";
			echo "<td>$exp[0]</td>";
			echo "<td>$exp[1]</td>";
			echo "<td>$exp[2]</td>";
			echo "<td>$exp[3]</td>";
			echo "<td>$exp[4]</td>";
			echo "<td>$exp[5]</td>";
			echo "<td>$exp[6]</td>";
			echo "<td>$exp[7]</td>";
			echo "<td>$exp[8]</td>";
			echo "<td>";
			echo $sportmatch?$sportmatch:$discipline[1];
			echo "</td>";
			echo "</tr>";
		}
		else
		{
			$DID = IDgen($exp['drest'], "discipline", "did", true);
			foreach ($exp as $key => $val)
			{
				$$key = $val;
			}
			$exec = array_merge((array)$DID, $exp, (array)$sportmatch);
			echo "<pre>";
			//var_dump($exec);
			echo "</pre>";
			$query =  "INSERT INTO Discipline (did, dgender, dminweight, dmaxweight, dwunit, ddist, ddunit, dteam, dcat, dname, sid) VALUES ('$DID', '$dgender', '$dminweight', '$dmaxweight', '$dwunit', '$ddist', '$ddunit', '$dteam', '$dcat', '$drest', '$sportmatch')";
			$conn->query($query);
			//echo $conn->prepare("INSERT INTO Discipline (did, dgender, dminweight, dmaxweight, dwunit, ddist, ddunit, dteam, dcat, dname, sid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute($exec)?'oui':'non';
		}
		$ct ++;
//		if($ct > 1) break;
	}
	if ($debug)
		echo "</table>";
}

?>