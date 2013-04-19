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
}

?>
