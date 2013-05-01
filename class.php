<?
include_once('connect.php');
mb_internal_encoding("UCS-2");
include_once('athlete.class.php');
include_once('country.class.php');
include_once('sport.class.php');
include_once('discipline.class.php');
include_once('game.class.php');

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
		$part = $conn->query("SELECT * FROM Participation WHERE aid = '$aid' AND gid = '$gid' AND did = '$did'");// AND medal = $medal");
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
