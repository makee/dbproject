<?
include_once('connect.php');
include_once('array.php');
mb_internal_encoding("UCS-2");

class Athlete
{
	public $aid;
	public $aname;

	public function __construct()
	{
		foreach(get_object_vars($this) as $key => $attr)
		$this->$key = preg_replace('/ +$/', '', $attr);
	}
	
	public function listAttrib()
	{
		return (array('aid'=>$this->aid, 'aname'=>$this->aname));
	}
	
/*	public static function getAthlete($limit)
	{
		global $conn;
		$query = "SELECT TOP $limit aid FROM athlete";
		$stt = $conn->prepare($query);
		$stt->execute();
		$res = $stt->fetchAll();
		$listAthl = array();
		foreach ($res as $key => $athlete)
		{
			$listAthl[$key] = new Athlete($athlete['aid']);
	
		}
		return $listAthl;
	}*/
	
	public static function findAthlete($name)
	{
		global $conn;
		$athl = $name;
//		$athl = utf8_encode($athl);
	//	$athl = htmlentities($athl);
	//	$conn->quote($athl);
		$athl = preg_replace('/(?<!\')\'(?!\')/', '\'\'', $athl);
		$query = "SELECT aid, aname FROM athlete WHERE aname LIKE N'%$athl%'";// CONVERT(NCHAR(70), ?)";
		$stt = $conn->prepare($query);
//		$stt->execute((array)$athl);
		$stt->execute();
		$res = $stt->fetchAll(PDO::FETCH_CLASS, 'Athlete');
		if (empty($res))
			return false;
		else
			return $res[0];
	}

	public static function insert($aname)
	{
		global $conn;
		$aid = IDgen($aname, "Athlete", "aid", true); 
		//$aname = utf8_encode($aname);
		//$aname = htmlentities($aname);
		$test = Athlete::findAthlete($aname);
		if (!$test)
		{
			$aname = preg_replace('/(?<!\')\'(?!\')/', '\'\'', $aname);
			$stt = $conn->query("INSERT INTO Athlete (aid, aname) VALUES ('$aid', N'$aname')");
			$athlete = Athlete::findAthlete($aname);
			return $athlete;
		}
		else
		{
			echo "Already ";
			return $test;
		}
	}

	public static function getAthleteDetail($aid)
	{
			//SELECT g.gid + ' ' + (CASE g.season WHEN 'w' THEN 'Winter' ELSE 'Summer' END) + ' Olympics ' + g.city AS Game
		global $conn;
		$athlete = $conn->prepare("
			SELECT g.gid		 
				, c.iocCode
				, c.cname
				, s.sid
				, s.sname
				, d.did
				, p.medal
				, a.aname
			FROM game g
				, country c
				, sport s
				, discipline d
				, participation p
				, represents r
				, athlete a
			WHERE g.gid=p.gid 
				AND r.aid = a.aid
				AND r.iocCode=c.iocCode 
				AND r.aid=p.aid 
				AND p.did=d.did 
				AND s.sid=d.sid 
				AND g.year <= ALL 
					(SELECT g2.year 
					FROM game g2
						, represents r2 
					WHERE r2.aid=r.aid 
					AND r2.gid=g2.gid)
					AND r.aid LIKE ?
		");
		$athlete->execute((array)$aid);
		$athlete = $athlete->fetchAll();
		foreach($athlete[0] as $key => $attr)
			$athlete[0][$key] = preg_replace('/ +$/', '', $attr);
		return $athlete;
	}

}


class Discipline
{
	public $did;
	public $dgender;
	public $dminweight;
	public $dmaxweight;
	public $dwunit;
	public $ddist;
	public $ddunit;
	public $dteam;
	public $dcat;
	public $dname;
	public $sid;

	public function __construct()
	{
			foreach(get_object_vars($this) as $key => $attr)
				$this->$key = preg_replace('/ +$/', '', $attr);
	}


	public static function getDiscipline($did)
	{
		global $conn;
		$disc = $conn->query("SELECT * FROM Discipline WHERE did = '$did'");
		$disc = $disc->fetchAll(PDO::FETCH_CLASS, 'Discipline');
		return $disc[0];
	}	
	public function display()
	{
		switch($this->dgender)
		{
			case '1':
			$this->gender = "Men&apos;s";
			break;
			case '2':
			$this->gender = "Women&apos;s";
			break;			
			default:
			$this->gender = NULL;
		}
		$disc = "$this->gender $this->dname";
		if ($this->dminweight == -1 && $this->dmaxweight > -1)
		{
			$this->disc .= " -$this->dmaxweight";
			if ($this->dwunit != "")
				$disc .= " $this->dwunit";
		}
		if ($this->dminweight > -1 && $this->dmaxweight == -1)
		{
			$this->disc .= " +$this->dminweight";
			if ($this->dwunit != "")
				$disc .= " $this->dwunit";
		}
		if ($this->dminweight > -1 && $this->dmaxweight > -1)
		{
			$disc .= " $this->dminweight-$this->dmaxweight";
			if ($this->dwunit != "")
				$disc .= " $this->dwunit";
		}
		if ($this->dcat)
			$disc .= " ($this->dcat)";
		if ($this->ddist > -1)
			$disc .= " $this->ddist $this->ddunit";
		if ($this->dteam)
			$disc .= " - $this->dteam";
		//$disc .= "<br>";

		return array($this->did, $disc); 
	}

	private function leven($string)
	{
		return levenshtein($this->dcat, $string). "<br>";
	}

	public function compare(array $exp)
	{
		foreach ($exp as $key => $val)
		{
			$$key = $val;
		}
		if (
				($ddunit != $this->ddunit && $ddunit != "") // diff unit and imported not empty
				|| ($dwunit != $this->ddunit && $ddunit != "")// diff unit
				|| ($dminweight != $this->dminweight && $dminweight != -1) // diff min weight but imported defined
				|| ($dmaxweight != $this->dmaxweight && $dmaxweight != -1)// diff min weight but imported defined
				|| ($ddist != $this->ddist && $ddist != -1)// diff dist but imported defined
				|| ($dteam != $this->dteam && $dteam != "")// diff team but imported defined
				|| ($this->leven($dcat) > 3)
			)
			$similar = false;
		else
			$similar = true;
		return $similar;
	}

	public static function insert(array $newdisc)
	{
		global $conn;
		foreach ($newdisc as $key => $val)
		{
			$$key = $val;
		}
		$DID = IDgen($drest, "Discipline", "did", true); 
		$query = "INSERT INTO Discipline (did, dname, dgender, dminweight, dmaxweight, dwunit, ddist, ddunit, dteam, dcat, sid) VALUES ('$DID', ?, $dgender, $dminweight, $dmaxweight, '$dwunit', '$ddist', '$ddunit', '$dteam', '$dcat', '$sid')";
		$stt = $conn->prepare($query);
		$stt->execute((array)$drest);
		$disc = new Discipline();
		$disc->did = $DID;
		$disc->drest = $drest;
		$disc->dgender = $dgender;
		$disc->dminweight = $dminweight;
		$disc->dmaxweight = $dmaxweight;
		$disc->dwunit = $dwunit;
		$disc->ddist = $ddist;
		$disc->ddunit = $ddunit;
		$disc->dteam = $dteam;
		$disc->dcat = $dcat;
		$disc->sid = $sid;
		return $disc;
		
	}

	public static function getDisciplineDetail($did)
	{
		global $conn;
		$disc = $conn->query("
				SELECT g.gid
					, a1.aid AS goldaid
					, a1.aname AS goldaname
					, a2.aid AS silveraid
					, a2.aname AS silveraname
					, a3.aid AS Bronzeaid
					, a3.aname AS Bronzeaname
				FROM game g
					, athlete a1
					, athlete a2
					, athlete a3
					, participation p1
					, participation p2
					, participation p3
				WHERE p1.aid=a1.aid 
					AND p1.medal=1 
					AND p2.aid=a2.aid 
					AND p3.medal=2 
					AND p3.aid=a3.aid 
					AND p3.medal=3 
					AND p1.gid=g.gid 
					AND p2.gid=g.gid 
					AND p3.gid=g.gid
					AND p1.did='$did' 
					AND p2.did='$did' 
					AND p3.did='$did'
		")->fetchAll();
		foreach($disc as $key => $attr){
			$sport[$key] = preg_replace('/ +$/', '', $attr);	
		}
		return $disc;
	}

}


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

}	

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
		$country = utf8_encode($country);
		$country = "%$country%";
		$medIOC = $conn->prepare("SELECT iocCode, cname FROM Country WHERE cname LIKE ?");
		$medIOC->execute(array($country));
		$medIOC = $medIOC->fetchAll(PDO::FETCH_CLASS, "Country");
		if (empty($medIOC))
			return false;
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
}

class Sport
{
	public $sname;
	public $sid;

	public function __construct()
	{
		foreach(get_object_vars($this) as $key => $attr)
			$this->$key = preg_replace('/ +$/', '', $attr);
	}
	public static function findSport($sname)
	{
		global $conn;
		$sname = "$sname%";
		$spo = $conn->prepare("SELECT * FROM Sport WHERE sname LIKE ?");
		$spo->execute(array($sname));
		$spo = $spo->fetchAll(PDO::FETCH_CLASS, "Sport");
		if (empty($spo))
			return false;
		else
			return $spo[0];	
	}

	public static function insert($sname)
	{
		global $conn;
		$sport = Sport::findSport($sname);
		if (!$sport)
		{
			$SID = IDgen($sname, "Sport", "sid"); 
			$conn->query("INSERT INTO Sport (sid, sname) VALUES ('$SID', '$sname')");
			$sport = new Sport();
			$sport->sid = $SID;
			$sport->sname = $sname;
		}
		else
		{
			echo "Already ";
		}
		return $sport;
	}

	public static function getSportDetail($sid)
	{
		global $conn;
		$sport = $conn->query("
			SELECT TOP 10
				d.did
				, g.* 
				, s.sname
			FROM discipline d
				, game g
				, participation p
				, sport s
			WHERE d.did=p.did 
				AND d.sid = s.sid
				AND p.gid=g.gid
				AND d.sid = '$sid' 
		")->fetchAll();
		foreach($sport as $key => $attr){
			$sport[$key] = preg_replace('/ +$/', '', $attr);	
		}
		return $sport;
	}
}

class Participation
{
	public $did;
	public $gid;
	public $aid;
	public $medal;

	public function __construct()
	{
		foreach(get_object_vars($this) as $key => $attr)
			$this->$key = preg_replace('/ +$/', '', $attr);
	}
	public static function findPart($aid, $did, $gid, $medal)
	{
		global $conn;
		$part = $conn->query("SELECT * FROM Participation WHERE aid = '$aid' AND gid = '$gid' AND did = '$did' AND medal = $medal");
		$part = $part->fetchAll(PDO::FETCH_CLASS, "Participation");
		if (empty($part))
			return false;
		else
			return $part[0];	
	}

	public static function insert($aid, $did, $gid, $medal)
	{
		global $conn;
		$test = Participation::findPart($aid, $did, $gid, $medal);
		if (!$test)
		{
			$conn->query("INSERT INTO Participation (aid, did, gid, medal) VALUES ('$aid', '$did', '$gid', '$medal')");
			$part= new Participation();
			$part->aid = $aid;
			$part->did = $did;
			$part->gid = $gid;
			$part->medal = $medal;
			return $part;
		}
		else
		{
			echo "Already ";
			return $test;
		}
	}
}

class Represents
{
	public $iocCode;
	public $aid;
	public $gid;

	public static function findRepres($iocCode, $aid, $gid)
	{
		global $conn;
		$event = $conn->query("SELECT * FROM Represents WHERE iocCode = '$iocCode' AND aid = '$aid' AND gid = '$gid'");
		$event = $event->fetchAll(PDO::FETCH_CLASS, "Represents");
		if (empty($event))
			return false;
		else
			return $event[0];	
	}

	public static function insert($iocCode, $aid, $gid)
	{
		global $conn;
		$rep = Represents::findRepres($iocCode, $aid, $gid);
		if (!$rep)
		{
			$conn->query("INSERT INTO Represents (iocCode, aid, gid) VALUES ('$iocCode', '$aid', '$gid')");
			$rep = Represents::findRepres($iocCode, $aid, $gid);
			return $rep;
		}
		return $rep;
	}

}


class Eventof
{
	public $did;
	public $gid;

	public static function findEvent($did, $gid)
	{
		global $conn;
		$rep = $conn->query("SELECT * FROM Eventof WHERE did = '$did' AND gid = '$gid'");
		$rep = $rep->fetchAll(PDO::FETCH_CLASS, "Eventof");
		if (empty($rep))
			return false;
		else
			return $rep[0];	
	}

	public static function insert($did, $gid)
	{
		global $conn;
		$event = Eventof::findEvent($did, $gid);
		if (!$event)
		{
			$conn->query("INSERT INTO Eventof (did, gid) VALUES ('$did', '$gid')");
			$event = Eventof::findEvent($did, $gid);
			return $event;
		}
		return $event;
	}

}



?>
