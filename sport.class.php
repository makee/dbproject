<?
include_once('connect.php');
include_once('class.php');

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
			SELECT 
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

	public static function search($keyword)
	{
		global $conn;
		$query = "
			SELECT s.sid
				, s.sname
			FROM sport s
			GROUP BY s.sid
				, s.sname
			HAVING s.sid LIKE '%$keyword%' 
				OR s.sname LIKE '%$keyword%'
				";
		$statement = $conn->query($query);
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		if (empty($result))
			return false;
		else
		{
			$dom = new DomDocument("1.0", "UCS-2");
			$root = $dom->createElement('sports');
			foreach ($result as $s)
			{
				$sport = $dom->createElement('sport');
				$sname = $dom->createElement('sname', utf8_encode($s['sname']));
				$sport->setAttribute('sid', $s['sid']);
				$sport->appendChild($sname);
				$root->appendChild($sport);
			}
			return $dom->saveXML($root);
		}
	}


}
?>
