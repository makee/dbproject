<?
header("Content-Type: text/xml");
include_once('connect.php');
include_once('class.php');

$_POST = $_GET;

if($_POST['action'] == 'get_athlete' && isset($_POST['aid']))
{
	$dom = new DomDocument("1.0", "UCS-2");
	$root = $dom->createElement('result');
	$aid = $_GET['aid'];
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
		$dname = $conn->query("SELECT * FROM Discipline WHERE did = '$did'")->fetchAll(PDO::FETCH_CLASS, 'Discipline')[0]->display()[1];
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


if ($_GET['action'] == 'get_country' && isset($_GET['iocCode']))
{
	$limit = !isset($_GET['limit'])?50:$_GET['limit'];

	$dom = new DomDocument("1.0", "UCS-2");
	$root = $dom->createElement('result');
	$iocCode = $_GET['iocCode'];
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
		$athl = $dom->createElement('aname', $medal['aname']);
		$athl->setAttribute('aid', $medal['aid']);
		$spo = $dom->createElement('sname', $medal['sname']);
		$spo->setAttribute('sid', $medal['sid']);
		$did = $medal['did'];
		$dname = $conn->query("SELECT * FROM Discipline WHERE did = '$did'")->fetchAll(PDO::FETCH_CLASS, 'Discipline')[0]->display()[1];
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

if ($_GET['action'] == 'get_sport' && isset($_GET['sid']))
{
	$sport = Sport::getSportDetail($_GET['sid']);
	$sname = $sport[0]['sname'];
	$sid = $_GET['sid'];
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
		$dstring = $dname->display()[1];
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
if ($_GET['action'] == 'get_discipline' && isset($_GET['did']))
{
	$disc = Discipline::getDisciplineDetail($_GET['did']);
	$did = $_GET['did'];
	$dname = Discipline::getDiscipline($did);
	$dom = new DomDocument("1.0", "UCS-2");
	$root = $dom->createElement('result');
	$d = $dom->createElement('dname', $dname->display()[1]);
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

if ($_GET['action'] == 'get_game' && isset($_GET['gid']))
{
	$g = Game::getGameDetail($_GET['gid']);
	$gid = $_GET['gid'];
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
		$dname = htmlentities($dname->display()[1]);
		$disc = $dom->createElement('discipline', $dname);
		$disc->setAttribute('did', $did);
		
		$sport->appendChild($disc);
		$game->appendChild($sport);
	}
	$root->appendChild($game);
	$xmlDat = $dom->saveXML($root);
	echo $xmlDat;
}


?>
