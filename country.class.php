<?
include_once('connect.php');
include_once('class.php');

class Country
{
	public $iocCode;
	public $cname;

	public function __construct()
	{
		foreach(get_object_vars($this) as $key => $attr)
			$this->$key = preg_replace('/ +$/', '', $attr);
	}

	public static function findCountry($country)
	{
		global $conn;
		//$country = utf8_encode($country);
//		$country = "%$country%";
		$medIOC = $conn->prepare("SELECT iocCode, cname FROM Country WHERE cname LIKE ?");
		$medIOC->execute(array($country));
		$medIOC = $medIOC->fetchAll(PDO::FETCH_CLASS, "Country");
		if (empty($medIOC))
		{
			return false;
		}
		else
			return $medIOC[0];	
	}

	public static function insert($iocCode, $cname)
	{
		global $conn;
		$test = Country::findCountry($cname);
		if ($test)
		{
			echo "Already ";
			return $test;
		}
		else
		{
			$cname = utf8_encode($cname);
			$query = "INSERT INTO Country (iocCode, cname) VALUES ('$iocCode', ?)";
			$country = $conn->prepare($query);
			$country->execute((array)$cname);
			$country = Country::findCountry($cname);
			return $country;
		} 
	}

	public function getCountryDetail($ioc, $limit)
	{
		global $conn;
		//Hosto	
		$games = array();
		$games['host'] = $conn->query("
			SELECT g.*
				, c.cname
			FROM game g
				, country c
			WHERE g.iocCode LIKE '$ioc'
				AND g.iocCode = c.iocCode
		")->fetchAll(PDO::FETCH_CLASS, 'Game');
		$games['medal'] = $conn->query("
				SELECT TOP $limit 
					 g.gid 
       				, a.aname 
       				, a.aid 
					, s.sname
					, s.sid
					, d.did
					, p.medal 
				FROM game g
					, athlete a
					, represents r
					, participation p
					, discipline d
					, sport s 
				WHERE g.gid=p.gid 
					AND a.aid=p.aid 
					AND r.aid=p.aid 
					AND d.sid=s.sid 
					AND p.did=d.did 
					AND r.iocCode='$ioc'
					AND p.medal <> 0
		")->fetchAll();
		foreach($games['medal'] as $key => $attr)
			$games['medal'][$key] = preg_replace('/ +$/', '', $attr);
		return $games;
	}


	public static function search($keyword)
	{
		global $conn;
		$query = "
			SELECT c.iocCode
				, c.cname
			FROM country c
			GROUP BY c.iocCode
				, c.cname
			HAVING c.iocCode LIKE '%$keyword%'
				 OR c.cname LIKE '%$keyword%'
		";
		$statement = $conn->query($query);
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		if (empty($result))
			return false;
		else
		{
			$dom = new DomDocument("1.0", "UCS-2");
			$root = $dom->createElement('countries');
			foreach ($result as $c)
			{
				$country = $dom->createElement('country');
				$cname = $dom->createElement('cname', utf8_encode($c['cname']));
				$country->setAttribute('iocCode', $c['iocCode']);
				$country->appendChild($cname);
				$root->appendChild($country);
			}
			return $dom->saveXML($root);
		}
	}
}

?>
