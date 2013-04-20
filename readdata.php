<?php
include_once('connect.php');
include_once('class.php');
include_once('functions.php');
$begin = time();
$reimport = false;
$debug = false;

// Sport import
if ($reimport || $conn->query("SELECT COUNT(*) FROM Sport")->fetchColumn() == 0 || isset($_GET['sport']))
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
if ($reimport || $conn->query("SELECT COUNT(*) FROM Country")->fetchColumn() == 0 || isset($_GET['country']))
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
if ($reimport || $conn->query("SELECT COUNT(*) FROM Game")->fetchColumn() == 0 || isset($_GET['game']))
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

if ($reimport || $conn->query("SELECT COUNT(*) FROM Athlete")->fetchColumn() == 0 || isset($_GET['athlete']))
{
	$max = count($athlete);
	$nb = $conn->query("SELECT COUNT(*) FROM athlete")->fetchColumn();
	echo "$nb $max";
	for($i=$nb-100;$i<=$max;$i++)
	{
		$athletee = $athlete[$i];
		echo "<pre>";
		var_dump($athletee);
		echo "</pre>";
		$athl = $athletee[0];
		if ($athl != NULL && $athl != 'name' && $athl != "")
		{
			$test = Athlete::insert($athl);
			if($test)
				echo "Athlete created: ".$test->aid."/".$test->aname."<br>";
			else
				echo "Failed to create $athl<br>";
		}
	}
}

echo time()-$begin;
?>
