<?
include_once('connect.php');
include_once('class.php');

class Game{
	public $gid;
	public $year;
	public $season;
	public $city;
	public $iocCode;

	public function __construct($id=false)
	{
		foreach(get_object_vars($this) as $key => $attr)
			$this->$key = preg_replace('/ +$/', '', $attr);
		
	}

	public static function findGame($year, $season)
	{	
		global $conn;
		$query = "SELECT gid, city, iocCode, year, season FROM Game WHERE year = '$year' AND season = '$season'";
		$medGID = $conn->query($query);
		$medGID = $medGID->fetchAll(PDO::FETCH_CLASS, "Game");
		if (empty($medGID))
			return false;
		else
			return $medGID[0];
	}

	public function writeFullGame()
	{
		$season = $this->season=="s"?"Summer":"Winter";
		return "$this->year $season Olympics at $this->city";// ($this->gid)";
	}

	public static function insert($year, $season, $city, $iocCode)
	{
		global $conn;
		$test = Game::findGame($year, $season);
		if ($test)
		{
			echo "Already ";
			return $test;
		}
		else
		{
			$city = utf8_encode($city);
			$GID = $year . strtoupper($season) . $iocCode;
			$query = "INSERT INTO Game (gid, year, season, city, iocCode) VALUES ('$GID', '$year', '$season', ?, '$iocCode')";
			$tt = $conn->prepare($query);
			$tt->execute((array)($city));
			$game = Game::findGame($year, $season);
			return $game;
		} 
	}

	public function getGameDetail($gid)
	{
		global $conn;
		$g = $conn->query("
			SELECT *
			FROM eventof e
				, discipline d 
				, sport s
			WHERE e.did = d.did 
				AND s.sid = d.sid
				AND e.gid = '$gid'
			")->fetchAll();
		foreach($g as $key => $attr){
			$g[$key] = preg_replace('/ +$/', '', $attr);	
		}
		return $g;
	}

	public static function search($keyword)
	{
		global $conn;
		$query = "
			SELECT g.gid
				, g.year
				, g.season
				, g.city
				, c.cname
				, g.iocCode
			FROM game g
				, country c
			GROUP BY g.gid
				, g.year
				, g.season
				, g.city
				, g.city
				, g.iocCode
				, c.cname
				, c.iocCode
			HAVING 
				g.iocCode=c.iocCode 
				AND 
					(
					g.gid LIKE '%$keyword%' 
					OR g.year LIKE '%$keyword%' 
					OR g.season LIKE '%$keyword%' 
					OR g.city LIKE '%$keyword%'
					)
		";
		$statement = $conn->query($query);
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		if (empty($result))
			return false;
		else
		{
			$dom = new DomDocument("1.0", "UCS-2");
			$root = $dom->createElement('games');
			foreach ($result as $g)
			{
				$game = $dom->createElement('game');
				$gname = $dom->createElement('gname', $g['year'] . ($g['season'] == 's'?" Summer ":" Winter ") . "Olympics at " . $g['city'] );
				$cname = $dom->createElement('cname', utf8_encode($g['cname']));
				$city = $dom->createElement('city', utf8_encode($g['city']));
				$year = $dom->createElement('year', $g['year']);
				$season = $dom->createElement('season', $g['season'] == 's'?" Summer ":" Winter ");
				$game->setAttribute('gid', $g['gid']);
				$game->appendChild($gname);
				$game->appendChild($city);
				$game->appendChild($year);
				$game->appendChild($cname);
				$game->appendChild($season);
				$root->appendChild($game);
			}
			return $dom->saveXML($root);
		}
	}
}	


?>
