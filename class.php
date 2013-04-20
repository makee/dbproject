<?
include_once('connect.php');

class Athlete
{
	public $aid;
	public $aname;

	public function __construct($aid = false)
	{
		global $conn;
		if ($aid)
		{
			$query = "SELECT * FROM athlete WHERE aid = '$aid'";	
			$res = $conn->prepare($query);
			$res->execute();
			$res = $res->fetch();
			$this->aid = $res['aid'];
			$this->aname = $res['aname'];
			$this->aname = preg_replace('/ +$/', '', $this->aname);
		}
	}
	
	public function listAttrib()
	{
		return (array('aid'=>$this->aid, 'aname'=>$this->aname));
	}
	
	public static function getAthlete($limit)
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
	}
	
	public static function findAthlete($name)
	{
		global $conn;
		$athl = utf8_encode($name);
		//$athl = htmlentities($athl);
		$athl = "%".$athl."%";
		$query = "SELECT aid FROM athlete WHERE aname LIKE ?";
		$stt = $conn->prepare($query);
		$stt->execute((array)$athl);
		$res = $stt->fetchColumn();
		return $res;

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

	public function display()
	{
		switch($this->dgender)
		{
			case '0':
			$this->gender = NULL;
			break;
			case '1':
			$this->gender = "Men's";
			break;
			case '2':
			$this->gender = "Women's";
			break;			
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
		$disc .= "<br>";

		return array($this->did, $disc); 
	}

	public function leven($string)
	{
		return levenshtein($this->dcat, $string). "<br>";
	}
}

?>
