<?php
include_once('connect.php');
include_once('class.php');
include_once('functions.php');
$begin = time();
$reimport = false;
$debug = false;

// Sport import
if (isset($_GET['sport']))
{
	foreach ($sports as $sport)
	{
		if ($sport[0] != "name" && $sport[0] != NULL && $sport[0] != "Dominican Republic")
		{
			$test = Sport::insert($sport[0]);
			if($test)
				echo "Sport created: ".$test->sid."/".$test->sname ."<br>" ;
			else
				echo "Failed to create $sport[0]<br>";
		}
	}
}
if (isset($_GET['country']))
{
	foreach ($countries as $country)
	{
		if ($country[1] != NULL)
		{
			$test = Country::insert($country[1], $country[0]);
			if($test)
				echo "Country created: ".$test->iocCode."/".$test->cname ."<br>" ;
			else
				echo "Failed to create $country[1]<br>";
		}
	}
}
if (isset($_GET['game']))
{
	foreach ($games as $game)
	{
		$country = Country::findCountry($game[6]);
		$ioc = $country->iocCode;
		$test = Game::insert($game[0], $game[1], $game[5], $ioc);
		if($test)
			echo "Game created: ".$test->gid."/".$test->writeFullGame() ."<br>" ;
		else
			echo "Failed to create $country[1]<br>";
	}
}

if (isset($_GET['athlete']))
{
	if (isset($_get['max']))
		$max = $_GET['max'];
	else
		$max = count($athlete);
	if(isset($_GET['min']))
		$nb = $_GET['min'];
	else
		$nb = $conn->query("SELECT COUNT(*) FROM athlete")->fetchColumn() -100;
	for($i=$nb;$i<=$max;$i++)
	{
		$athletee = $athlete[$i];
		$athl = $athletee[0];
		if ($athl != NULL && $athl != 'name' && $athl != "")
		{
			$test = Athlete::insert($athl);
			if($test)
				echo "Athlete created: ".$test->aid."/".$test->aname."<br>";
			else
			{
				echo "Failed to create $athl<br>";
				break;
			}
		}
	}
	if ($ct == $_GET['max'])
	{
		echo "<script type='text/javascript'>window.location = 'readdata.php?min=$max&max=" . ($ct + 10) ."&athlete=12' </script>";
	}
}


if(isset($_GET['event']))
{
	$ct = 1;
	foreach ($events as $event)
	{
		if ($ct < 4150)	
		{
			$ct ++;
			continue;
		}
		$sport = Sport::findSport($event[0]);
		if ($sport)
			echo "Sport founded: $sport->sid / $sport->sname<br>";
		else
		{
			echo "Fail to find sport ".$event[0]."<br>";
			$sport = Sport::insert($event[0]);
			echo "Creation of sport ".$sport->sname."<br>";
			
		}
		$spo = $sport->sname;
		$sid = $sport->sid;
		//preg_match('/\d{4}/i', $event[1], $game);
		preg_match('/(\d{4}) (Summer|Winter)/i', $event[1], $game);
		$season = strtolower(substr($game[2], 0, 1));
		$game = Game::findGame($game[1], $season); 
		if ($game)
		{
			$write = $game->writeFullGame();
			echo "Game founded: $write<br>";
			$gid = $game->gid;
		}
		else
		{
			echo "Fail to find game ".$event[1]."<br>";
		}
		$discipline = preg_replace('/^.*pics . /i', '', $event[1]);
		$disc = explodeDiscipline($discipline, $spo);
		foreach($disc as $key => $val)
			$$key = $val;
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
					$write = $discipline->display();
					echo  "Founded: ".$write[1]."<br>";
					$did = $discipline->did;
					break;
				}
			}
		}
		if ($create)
		{
			$discarray = array_merge($disc, array('sid' => $sid));
			$test = Discipline::insert($discarray);		
			if($test)
			{
				$write = $test->display();
				echo "Discipline created: ".$write[1]."/".$test->sid ."<br>" ;
				$did = $test->did;
			}	
			else
			{
				echo "Failed to create $discipline[0]<br>";
				break;
			}
		}

		$test = Eventof::insert($did, $gid);
		echo "<br>";
		$ct ++;
		//if ($ct > 5) break;
	}
}


if(isset($_GET['part']))
{
	$ct = 1;
	foreach($participants as $participant)
	{
		if ($ct < 2 )//|| $participant[0] != "" || $participant[1] != "")
		{
			$ct++;
			continue;
		}

		$athlete = Athlete::findAthlete($participant[0]);
		if ($athlete)
		{
			echo "Athlete found: $athlete->aid / $athlete->aname<br>";
			$aid = $athlete->aid;
		}
		else
		{
			echo "Athlete not found $participant[0]<br>";
		}

		$country = Country::findCountry($participant[1]);	
		if ($country)
		{
			echo "Country found: $country->iocCode / $country->cname<br>";
			$ioc = $country->iocCode;
		}
		else
		{
			echo "Country not found $participant[1]<br>";
		}
		echo "<pre>";
		print_r($participant);
		echo "</pre>";
		echo "<br>";
		if ($ct > 5) break;
		$ct ++;
	}	
}

echo time()-$begin;
?>
