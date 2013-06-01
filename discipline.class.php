<?
include_once('class.php');
include_once('connect.php');

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
				|| ($dwunit != $this->dwunit && $dwunit != "")// diff unit
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

	public static function search($keyword)
	{
		global $conn;
		$query = "
			SELECT d.dname
				, d.did
				, s.sname
			FROM discipline d
				, sport s
			GROUP BY d.did
				, d.dname
				, s.sname
				, d.sid
				, s.sid
			HAVING 
				(	
					d.dname LIKE '%$keyword%' 
					OR s.sname LIKE '%$keyword%' 
				)
				AND d.sid=s.sid
							";
		$statement = $conn->query($query);
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		if (empty($result))
			return false;
		else
		{
			$dom = new DomDocument("1.0", "UCS-2");
			$root = $dom->createElement('disciplines');
			foreach ($result as $s)
			{
				$disc = $dom->createElement('discipline');
				$temp = Discipline::getDiscipline($s['did'])->display();
				$temp = $temp[0];
				$dname = $dom->createElement('dname', $temp);
				$sname = $dom->createElement('sname', utf8_encode($s['sname']));
				$disc->setAttribute('did', $s['did']);
				$disc->appendChild($sname);
				$disc->appendChild($dname);
				$root->appendChild($disc);
			}
			return $dom->saveXML($root);
		}
	}
}
?>
