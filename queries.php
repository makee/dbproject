<?
header("Content-Type: text/xml");
include_once('connect.php');
include_once('class.php');

function addTree2Node($dom, &$root, $tree)
{
	if ($tree != '')
	{
		$d = new DomDocument("1.0", "UCS-2");
		$tree = utf8_encode($tree);
		$d->loadXML($tree);
		$root->appendChild($dom->importNode($d->documentElement, true));
	}
}

$_POST = $_GET;

if($_POST['action'] == 'get_athlete' && isset($_POST['type']))
{
	$dom = new DomDocument("1.0", "UCS-2");
	$root = $dom->createElement('result');
	$aid = $_GET['type'];
	$relations = Athlete::getAthleteDetail($aid);
	$relations = array_map("unserialize", array_unique(array_map("serialize", $relations)));
	$athlete = $dom->createElement('athlete');
	$athlete->setAttribute('aname', $relations[0]['aname']);
	foreach($relations as $key => $result)
	{
		$game = $dom->createElement('game');
		$gid = $result['gid'];
		$gname = $conn->query("SELECT * FROM Game WHERE gid LIKE '$gid'")->fetchAll(PDO::FETCH_CLASS, 'Game');
		$gname = $gname[0];
		$game->setAttribute('gname', $gname->writeFullGame());
		$game->setAttribute('gid', $gid);
		
		$sport = $dom->createElement('sname', $result['sname']);
		$sport->setAttribute('sid', $result['sid']);	

		$did = $result['did'];
		$dname = $conn->query("SELECT * FROM Discipline WHERE did = '$did'")->fetchAll(PDO::FETCH_CLASS, 'Discipline');
		$dname = $dname[0];
		$dname = $dname->display();
		$dname = $dname[1];
		$dis = $dom->createElement('dname', $dname);
		$dis->setAttribute('did', $did);

		switch($result['medal'])
		{
			case 1:
				$med = "Gold";
				break;
			case 2:
				$med = "Silver";
				break;
			case 3:
				$med = "Bronze";
				break;
			default:
				$med = NULL;
		}
		$medal = $dom->createElement('medal', $med);
		$medal->setAttribute('med', $result['medal']);

		$coun = $dom->createElement('cname', $result['cname']);
		$coun->setAttribute('iocCode', $result['iocCode']);	


		$game->appendChild($sport);
		$game->appendChild($coun);
		$game->appendChild($dis);
		$game->appendChild($medal);
		$athlete->appendChild($game);
		
	}
	$root->appendChild($athlete);
	$xmlDat = $dom->saveXML($root);
	echo $xmlDat;

}


if ($_GET['action'] == 'get_country' && isset($_GET['type']))
{
	$limit = !isset($_GET['limit'])?10:$_GET['limit'];

	$dom = new DomDocument("1.0", "UCS-2");
	$root = $dom->createElement('result');
	$iocCode = $_GET['type'];
	$country = Country::getCountryDetail($iocCode, $limit);

	$country['host'] = array_map("unserialize", array_unique(array_map("serialize", $country['host'])));
	$root->appendChild($dom->createElement('cname', $country['host'][0]->cname)); 
	foreach($country['host'] as $hos)
	{
		$host = $dom->createElement('host');
		$gname = $dom->createElement('gname', $hos->writeFullGame());
		$host->setAttribute('gid', $hos->gid);
		$year = $dom->createElement('year', $hos->year);
		$city = $dom->createElement('city', $hos->city);
		$season = $dom->createElement('season', $hos->season=='w'?'Winter':'Summer');
		$season->setAttribute('seasoncode', $hos->season);
		$host->appendChild($year);
		$host->appendChild($city);
		$host->appendChild($season);
		$host->appendChild($gname);
		$root->appendChild($host);

	}
	$country['medal'] = array_map("unserialize", array_unique(array_map("serialize", $country['medal'])));
	foreach($country['medal'] as $medal)
	{
		$part = $dom->createElement('participation');
		$gid = $medal['gid'];
		$gam = $dom->createElement('game')->setAttribute('gid', $gid);
		$gname = $conn->query("SELECT * FROM Game WHERE gid LIKE '$gid'")->fetchAll(PDO::FETCH_CLASS, 'Game');
		$gname = $gname[0];
		$game = $dom->createElement('gname', $gname->writeFullGame());
		$athl = $dom->createElement('aname', htmlentities($medal['aname']));
		$athl->setAttribute('aid', $medal['aid']);
		$spo = $dom->createElement('sname', $medal['sname']);
		$spo->setAttribute('sid', $medal['sid']);
		$did = $medal['did'];
		$dname = $conn->query("SELECT * FROM Discipline WHERE did = '$did'")->fetchAll(PDO::FETCH_CLASS, 'Discipline');
		$dname = $dname[0];
		$dname = $dname->display();
		$dname = $dname[1];
		$dis = $dom->createElement('dname', $dname);
		$dis->setAttribute('did', $did);
		
		switch($medal['medal'])
		{
			case 1:
				$med = "Gold";
				break;
			case 2:
				$med = "Silver";
				break;
			case 3:
				$med = "Bronze";
				break;
			default:
				$med = NULL;
		}
		$med = $dom->createElement('medal', $med);
		$med->setAttribute('med', $medal['medal']);

		$part->appendChild($athl);
		$part->appendChild($spo);
		$part->appendChild($dis);
		$part->appendChild($med);
		$part->appendChild($game);
		$root->appendChild($part);
	}
	$xmlDat = $dom->saveXML($root);
	echo $xmlDat;

}

if ($_GET['action'] == 'get_sport' && isset($_GET['type']))
{
	$sport = Sport::getSportDetail($_GET['type']);
	$sname = $sport[0]['sname'];
	$sid = $_GET['type'];
	$dom = new DomDocument("1.0", "UCS-2");
	$root = $dom->createElement('result');
	$spo = $dom->createElement('sname', $sname);
	$root->appendChild($spo);

	$sport = array_map("unserialize", array_unique(array_map("serialize", $sport)));
	foreach($sport as $disci)
	{
		$disc = $dom->createElement('discipline');
		$did = $disci['did'];
		$dname = Discipline::getDiscipline($did);
		$disc->setAttribute('did',$did);
		$dstring = $dname->display();
		$dstring = $dstring[1];
		$dn = $dom->createElement('dname', $dstring);
		$gid = $disci['gid'];
		$gname = $conn->query("SELECT * FROM Game WHERE gid LIKE '$gid'")->fetchAll(PDO::FETCH_CLASS, 'Game');
		$gname = $gname[0];
		$game = $dom->createElement('game', $gname->writeFullGame());
		$game->setAttribute('gid', $gid);
		
		$disc->appendChild($dn);
		$disc->appendChild($game);
		$root->appendChild($disc);
	}
	$xmlDat = $dom->saveXML($root);
	echo $xmlDat;
}
if ($_GET['action'] == 'get_discipline' && isset($_GET['type']))
{
	$disc = Discipline::getDisciplineDetail($_GET['type']);
	$did = $_GET['type'];
	$dname = Discipline::getDiscipline($did);
	$dom = new DomDocument("1.0", "UCS-2");
	$root = $dom->createElement('result');
	$dstring = $dname->display();
	$dstring = $dstring[1];
	$d = $dom->createElement('dname', $dstring);
	$d->setAttribute('did', $did);
	$root->appendChild($d);

	$disc = array_map("unserialize", array_unique(array_map("serialize", $disc)));
	foreach($disc as $g)
	{
		$gg = $dom->createElement('game');
		$gid = $g['gid'];
		$gname = $conn->query("SELECT * FROM Game WHERE gid LIKE '$gid'")->fetchAll(PDO::FETCH_CLASS, 'Game');
		$gname = $gname[0];
		$game = $dom->createElement('game', $gname->writeFullGame());
		$gold = $dom->createElement('gold', $g['goldname']);
		$gold->setAttribute('aid', $g['goldaid']);
		$disc->setAttribute('did',$did);
		$silver = $dom->createElement('silver', $g['silvername']);
		$silver->setAttribute('aid', $g['silveraid']);
		$bronze = $dom->createElement('bronze', $g['bronzename']);
		$bronze->setAttribute('aid', $g['bronzeaid']);
		
		$gg->appendChild($gname);
		$gg->appendChild($gold);
		$gg->appendChild($silver);
		$gg->appendChild($bronze);
		$root->appendChild($gg);
	}
	$xmlDat = $dom->saveXML($root);
	echo $xmlDat;
}

if ($_GET['action'] == 'get_game' && isset($_GET['type']))
{
	$g = Game::getGameDetail($_GET['type']);
	$gid = $_GET['type'];
	$gname = $conn->query("SELECT * FROM Game WHERE gid LIKE '$gid'")->fetchAll(PDO::FETCH_CLASS, 'Game');
	$gname = $gname[0];
	$dom = new DomDocument("1.0", "UCS-2");
	$game = $dom->createElement('game');
	$game->setAttribute('gname', $gname->writeFullGame());
	$game->setAttribute('gid', $gid);
	$root = $dom->createElement('result');
	

	$g = array_map("unserialize", array_unique(array_map("serialize", $g)));
	foreach($g as $s)
	{
		$sport = $dom->createElement('sport');
		$sport->setAttribute('sname', $s['sname']);
		$sport->setAttribute('sid', $s['sid']);
		$did = $s['did'];
		$dname = Discipline::getDiscipline($did);
		$dstring = $dname->display();
		$dstring = $dstring[1];
		$dname = htmlentities($dstring);
		$disc = $dom->createElement('discipline', $dname);
		$disc->setAttribute('did', $did);
		
		$sport->appendChild($disc);
		$game->appendChild($sport);
	}
	$root->appendChild($game);
	$xmlDat = $dom->saveXML($root);
	echo $xmlDat;
}


if ($_GET['action'] == 'globalquery' && isset($_GET['keyword']))
{
	$keyword = $_GET['keyword'];
	$dom = new DomDocument("1.0", "UCS-2");
	$root = $dom->createElement('result');

	addTree2Node($dom, $root, Athlete::search($keyword));
	addTree2Node($dom, $root, Country::search($keyword));
	addTree2Node($dom, $root, Sport::search($keyword));
	addTree2Node($dom, $root, Discipline::search($keyword));
	addTree2Node($dom, $root, Game::search($keyword));

	$xml = $dom->saveXML($root);
	$xml = preg_replace('/ +</', '<', $xml);
	echo $xml;
}

if ($_GET['action'] == 'specialquery' && $_GET['type'] == 'A')
{
	$query = "
		SELECT a.aname 
			, a.aid 
		FROM athlete a 
		GROUP BY a.aid 
			, a.aname 
		HAVING a.aid IN 
		( 
		 ( 
		  SELECT p1.aid 
		  FROM participation p1 
		  	, game g1 
		  WHERE p1.gid = g1.gid 
		  	AND g1.season = 's' 
		  	AND p1.medal < >0
		 ) 
		 INTERSECT 
		 ( 
		  SELECT p2.aid 
		  FROM participation p2 
		  	, game g2 
		  WHERE p2.gid = g2.gid 
		  	AND g2.season = 'w' 
		  	AND p2.medal < >0
		 )
		)
	";
	$statement = $conn->query($query);
	$results = $statement->fetchAll(PDO::FETCH_CLASS, 'Athlete');
	$dom = new DOMDocument('1.0', 'UCS-2');
	$root = $dom->createElement('results');
	foreach ($results as $athl)
	{
		$result = $dom->createElement('result');
		$aname = $dom->createElement('Name', $athl->aname);
		$aid = $dom->createElement('ID', $athl->aid);
		$result->appendChild($aname);
		$result->appendChild($aid);
		$root->appendChild($result);
	}
	echo $dom->saveXML($root);
}
if ($_GET['action'] == 'advanced' && isset($_GET['type']))
{
	$query = array(
		'A' => "	 
		SELECT 
		athleteSW.aname
		, athleteSW.aid
		FROM athlete athleteSW
		GROUP BY athleteSW.aid
		, athleteSW.aname
		HAVING athleteSW.aid IN
		(
		 (
		  SELECT partS.aid 
		  FROM participation partS
		  , game gameS
		  WHERE partS.gid=gameS.gid 
		  AND gameS.season='s' 
		  AND partS.medal <>0
		 )
		 INTERSECT
		 (
		  SELECT partW.aid 
		  FROM participation partW
		  , game gameW
		  WHERE partW.gid=gameW.gid 
		  AND gameW.season='w' 
		  AND partW.medal <>0
		 )
		)
",
		'B' => "
				SELECT athleteOnce.aname 
				FROM athlete athleteOnce
				, participation partGold
				WHERE athleteOnce.aid=partGold.aid 
				AND partGold.medal=1 
				AND athleteOnce.aid NOT IN 
				(
				 SELECT partNot.aid 
				 FROM participation partNot
				 , participation partNot2 
				 WHERE partNot.aid=partNot2.aid 
				 AND partNot.gid<>partNot2.gid
				)
				GROUP BY athleteOnce.aname
	",
		'C' => "
				SELECT countryMedal.cname
				, gameFirst.gid
				, gameFirst.year 
				FROM participation partMedal
				, represents reprCountry
				, game gameFirst
				, country countryMedal
				WHERE partMedal.medal <>0 
				AND partMedal.aid=reprCountry.aid 
				AND gameFirst.gid=reprCountry.gid 
				AND countryMedal.iocCode=reprCountry.iocCode
				GROUP BY countryMedal.cname
				, reprCountry.iocCode
				, gameFirst.gid
				, gameFirst.year 
				HAVING gameFirst.year <= ALL 
				(
				 SELECT gamePart.year 
				 FROM game gamePart
				 , represents reprCountry2 
				 WHERE reprCountry2.iocCode=reprCountry.iocCode
				 AND reprCountry2.gid=gamePart.gid
				) ",
		'D' => "
		SELECT
		(
		 SELECT MASMax.iocCode 
		 FROM MedalsAll MASMax
		 GROUP BY MASMax.iocCode
		 , MASMax.season
		 HAVING MASMax.season='s' 
		 AND Count(MASMax.iocCode) >= ALL
		 (
		  SELECT Count(MAS.iocCode) 
		  FROM MedalsAll MAS
		  WHERE MAS.season='s'
		  GROUP BY MAS.iocCode
		 )
		) AS 'Summer Games',
		(
		 SELECT MAWMax.iocCode 
		 FROM MedalsAll MAWMax 
		 GROUP BY MAWMax.iocCode
		 , MAWMax.season 
		 HAVING MAWMax.season='w' 
		 AND Count(MAWMax.iocCode) >= ALL
		 (
		  SELECT Count(MAW.iocCode) 
		  FROM MedalsAll MAW
		  WHERE MAW.season='w'
		  GROUP BY MAW.iocCode
		 )
		) AS 'Winter Games'",
		'E' => "
		SELECT gamesHost.city 
		FROM game gamesHost
		, game gamesHostAgain
		WHERE gamesHost.gid <> gamesHostAgain.gid 
		AND gamesHost.city = gamesHostAgain.city
		GROUP BY gamesHost.city
		",
		'F' => "
		SELECT athleteMore.aname
		FROM athlete athleteMore
		, represents reprMore
		WHERE athleteMore.aid=reprMore.aid 
		AND reprMore.aid IN
		(
		 SELECT reprCountry1.aid 
		 FROM represents reprCountry1
		 , represents reprCountry2
		 WHERE reprCountry1.aid=reprCountry2.aid 
		 AND reprCountry1.iocCode <> reprCountry2.iocCode
		)
		GROUP BY athleteMore.aname
		",
		'G' => "
		SELECT MaxRC.gameID
		, MaxRC.IOCCode
		, MaxRC.nbParticipants
		FROM RepresentantsCountry MaxRC
		WHERE MaxRC.nbParticipants >= ALL 
		(
		 SELECT RC.nbParticipants 
		 FROM RepresentantsCountry RC
		 WHERE RC.IOCCode<>MaxRC.IOCCode 
		 AND RC.gameID=MaxRC.gameID
		)
		GROUP BY MaxRC.gameID
		, MaxRC.IOCCode
		, MaxRC.nbParticipants 
		",
		'H' => "
		SELECT CountryNever.cname 
		FROM country CountryNever
		WHERE CountryNever.iocCode NOT IN 
		(
		 SELECT reprCountryMedal.iocCode 
		 FROM represents reprCountryMedal
		 , participation partMedals
		 WHERE reprCountryMedal.aid = partMedals.aid 
		 AND partMedals.medal <>0
		)
		GROUP BY CountryNever.cname
		"
	);
	$id_bind = array(
		'aid' => 'aname',
		'iocCode' => 'cname',
		'sid' => 'sname'
	);
	$query_num = $_GET['type'];
	$stt = $conn->query($query[$query_num]);
	$results = $stt->fetchAll(PDO::FETCH_ASSOC);
	$dom = new DOMDocument('1.0', 'UCS-2');
	$root = $dom->createElement('results');
	foreach($results as $result)
	{
		$row = $dom->createElement('row');
		foreach($result as $key => $colres)
		{
			$colres = utf8_encode($colres);
			$colres = preg_replace('/ +$/', '', $colres);
			$col = $dom->createElement($key, $colres);
			$row->appendChild($col);
		}
		$root->appendChild($row);
	}
	echo $dom->saveXML($root);
		
}
?>
