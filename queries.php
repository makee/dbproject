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
	foreach($relations as $key => $result)
	{
		$game = $dom->createElement('gname');
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
		$game->appendChild($dis);
		$game->appendChild($medal);
		$athlete->appendChild($game);
		
	}
	$root->appendChild($athlete);
	$root->appendChild($game);
	$xmlDat = $dom->saveXML($root);
	echo $xmlDat;

}

?>
