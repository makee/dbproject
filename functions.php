<?
include_once('connect.php');
include_once('class.php');


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
		//if($file == 'medals.csv' || $file == 'Disciplines.csv'){
		if(1){
			$path_parts = pathinfo($file); 
  		$filename = $path_parts['filename']; 
		$filename = strtolower($filename);
			$$filename = readCSV($file);
    }
	}
}


function explodeDiscipline($disc, $spo){
	// GENDER
	if (!preg_match('/men/i', $disc))
		$gender = '0';
	if (preg_match('/Men/', $disc))
		$gender = '1';
	if (preg_match('/Women/i', $disc))
		$gender = '2';
	if ($gender != '?')
		$rest = preg_replace('/Men\'s ?|Women\'s ?/', '', $disc);
	else
		$rest = $disc;
	
	// Weight
	preg_match('/([0-9.]{0,5}) ?(\+|-)? ?([(0-9.]{1,5}) ?(kg)/', $rest, $weight);
	$weight = preg_replace('/\(/', '\(', $weight);
	if(empty($weight))
	{
		$minweight = -1;
		$maxweight = -1;
		$wunit = NULL;
	}
	elseif($weight[1] != "" && $weight[2] != "" )
	{
		$minweight = $weight[1]; 
		$maxweight = $weight[3];
		$rest = preg_replace("/".$weight[1]." ?\- ?".$weight[3]." ?kg/", '', $rest);
	}
	elseif($weight[2] == "+" )
	{
		$minweight = $weight[3];
		$maxweight = -1;
		$rest = preg_replace("/\+ ?".$weight[3]." ?kg/", '', $rest);
	}
	elseif($weight[1] == "" && $weight[2] == "" )
	{
		$minweight = $weight[3];
		$maxweight = $weight[3];
		$rest = preg_replace("/".$weight[3]." ?kg/", '', $rest);
	}
	else
	{
		$minweight = -1;
		$maxweight = $weight[3];
		$rest = preg_replace("/\- ?".$weight[3]." ?kg/", '', $rest);
	}
	if (!empty($weight))
	{
		$wunit = $weight[4];
	}

	if(preg_match('/\+([0-9.]+)/', $rest, $weight) == 1)
	{
		$minweight = $weight[1];
		$maxweight = -1;
		$wunit = NULL;
		$rest = preg_replace('/\+([0-9.]+)/', '', $rest);
	}
	if(preg_match('/([0-9.]+)-([0-9.]+)/', $rest, $weight) == 1)
	{
		$minweight = $weight[1];
		$maxweight = $weight[2];
		$wunit = NULL;
		$rest = preg_replace('/[0-9.]+-[0-9.]+/', '', $rest);
	}

	if(preg_match('/(?<!\w)- ?([0-9.]+)/', $rest, $weight) == 1)
	{
		$maxweight = $weight[1];
		$minweight = -1;
		$wunit = NULL;
		$rest = preg_replace('/- ?([0-9.]+)/', '', $rest);
	}

	$minweight = preg_replace('/\(/', '', $minweight);
	$maxweight = preg_replace('/\(/', '', $maxweight);
	$minweight = preg_replace('/\\\/', '', $minweight);
	$maxweight = preg_replace('/\\\/', '', $maxweight);

	// Distance
	$dd = preg_match('/\(\d+ ?\w+ \+ \d+ ?\w+\)/', $rest, $dinfo);
	$rest =	preg_replace('/\(\d+ ?\w+ \+ \d+ ?\w+\)/', '', $rest);	
	$reg = '/(([NL]H\/)?([0-9\/,.x]+)) ?((km?|Mile|mile|metre|m|yd|yard|Yard)s?)/';
	//$reg = '/(([NL]H\/)?(\d+[\/,.x]+)?\d+) ?((m|km|Mile|yd|yard)s?)/';
	$tt = preg_match($reg , $rest, $dist);
	if($tt == 0)
	{
		$dist = -1;
		$dunit = NULL;
	}
	else
	{
		$rest = preg_replace($reg , '', $rest);
		$distt = $dist[1];
		$dunit = $dist[4];
		$dist = $distt;
		$dist = preg_replace('/(\d+)[ .](\d+)/', '$1,$2', $dist);
		$dist = preg_replace('/( ?)(km?|Mile|mile|metre|m|yd|yard|Yard)(s?) /', ' $2', $dist); 
		$dunit = preg_replace('/yard/i', 'yd', $dunit);
	}
	if ($dd)
		$rest .= $dinfo[0];

	// Team ?
	$tt = preg_match('/(Team|Individual|Single|Double|Solo|relay)s?/i', $rest, $team);
	if($tt > 0)
	{
		$team = $team[1];
		$rest = preg_replace('/(Team|Individual|Single|Double|Solo)s? ?/i', '', $rest);
		$team = preg_replace(array('/team/i', '/double/', '/relay/i', '/single/i', '/solo/i', '/individual/i'), array('Team', 'Team', 'Team', 'Individual', 'Individual', 'Individual'), $team);
		$team = strtolower($team);
		$team = ucfirst($team);
	}
	else
	{
		$team = NULL;
	}
	
	// Remove sport
	$spos = preg_split('/ |-/', $spo);
	foreach ($spos as $spo)
	{
		$rest = preg_replace('/'.$spo.'/i', '', $rest);
	}

	// Category
	preg_match('/(extra-?|Light ?|half ?|half-?)?\w+weighg?t/i', $rest, $cat);
	if(isset($cat) && isset($cat[0]))
	{
		$cat = $cat[0];
		$rest = preg_replace('/'.$cat.'/', '', $rest);
		$cat = strtolower($cat);
		$cat = ucfirst($cat);
	}
	else
	{
		$cat = NULL;
	}

	// Unit sanitize
	if($dunit)
		$dunit = preg_replace(array('/km?/i', '/metres?|ms/i', '/miles?/i', '/yds?|yards?/i'), array('km', 'm', 'mi', 'yd'), $dunit);

	// Rest sanitize
	$rest = preg_replace('/at the \d{4} (winter|summer) olympics/i', '', $rest);
	$rest = preg_replace('/ s$/', '', $rest); // Plural from units
	$rest =	preg_replace(array('/  /','/[^a-z ]- [^a-z ]|^, | ,$|^,$|,|\( ?\)| $|^ /i'), array(' ', ''), $rest);
	$rest = preg_replace('/^\)|^-/', '', $rest);
	$rest = preg_replace(array('/\( /', '/ \)/'), array('(', ')'), $rest);
	$rest = preg_replace('/^\(([^)]*)\)$/', '$1', $rest);
	$rest = ucfirst($rest);
	return array(
			'dgender' => (int)$gender, 
			'dminweight' => (int)$minweight,
			'dmaxweight' => (int)$maxweight,
			'dwunit' =>  $wunit, 
			'ddist' => $dist,
			'ddunit' => $dunit, 
			'dteam' => $team, 
			'dcat' => $cat, 
			'drest' => $rest
			);

}




?>
