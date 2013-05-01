<?
include_once('connect.php');
include_once('class.php');
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

	public static function testAth($aid){
		global $conn;
		$statement = $conn->prepare("
			SELECT g.gid
				, g.iocCode as giocCode		 
				, g.season
				, g.year
				, g.city
				, c.iocCode as riocCode
				, c.cname
				, s.sid
				, s.sname
				, d.did
				, p.medal
				, a.*
			FROM game g
				, country c
				, country cc
				, sport s
				, discipline d
				, participation p
				, represents r
				, athlete a
			WHERE g.gid=p.gid 
				AND r.aid = a.aid
				AND r.iocCode=c.iocCode 
				AND g.iocCode=cc.iocCode 
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
		$statement->execute((array)$aid);
		$athlete = $statement->fetchAll();
		foreach($athlete[0] as $key => $attr)
			$athlete[0][$key] = preg_replace('/ +$/', '', $attr);
		$output = array();
		foreach($athlete as $val)
		{
			/*$output[$val['aid']]['aid'] = $val['aid'];
			$output[$val['aid']]['aname'] = $val['aname'];
			$output[$val['aid']]['games'][$val['gid']][$val['year']][] = $val['medal']; */
		/*	$arr1 = array('aid' => $val['aid'], 'aname' => $val['aname']);
			$output[$val['aid']] = $arr1;
			$arr2 = array('gid' => $val['gid'], 'year' => $val['year'], 'riocCode' => $val['riocCode'], 'giocCode' => $val['giocCode'], 'season' => $val['season'], 'city' => $val['city']);	
			$output[$val['aid']]['games'][$val['gid']] = $arr2;
			$arr3 = array('sid' => $val['sid'], 'sname' => $val['sname']);	
			$output[$val['aid']]['games'][$val['gid']]['sports'][$val['sid']] = $arr3;
			$arr4 = array('did' => $val['did'], 'medal' => $val['medal']);
			$output[$val['aid']]['games'][$val['gid']]['sports'][$val['sid']]['disc'][$val['did']] = $arr4;
			echo $val['did']."<br>";*/
			$output[$val['aid']]['aname'] = $val['aname'];
			$output[$val['aid']]['aid'] = $val['aid'];
			$output[$val['aid']]['games'][$val['gid']]['gid'] = $val['gid'];
			$output[$val['aid']]['games'][$val['gid']]['giocCode'] = $val['giocCode'];
			$output[$val['aid']]['games'][$val['gid']]['city'] = $val['city'];
			$output[$val['aid']]['games'][$val['gid']]['riocCode'] = $val['riocCode'];
			$output[$val['aid']]['games'][$val['gid']]['year'] = $val['year'];
			$output[$val['aid']]['games'][$val['gid']]['season'] = $val['season'];
			$output[$val['aid']]['games'][$val['gid']]['sports'][$val['sid']]['sid'] = $val['sid'];
			$output[$val['aid']]['games'][$val['gid']]['sports'][$val['sid']]['sname'] = $val['sname'];
			$output[$val['aid']]['games'][$val['gid']]['sports'][$val['sid']]['disc'][$val['did']]['medal'] = $val['medal'];
		}
		return Athlete::getXML($output);
	}

	public static function getXML($output)
	{
		
		$dom = new DomDocument("1.0", "UCS-2");
		$root = $dom->createElement('result');
		foreach($output as $a)
		{
			$athl = $dom->createElement('athlete');
			$aid = $dom->createElement('aid', $a['aid']);
			$aname = $dom->createElement('aname', $a['aname']);
			$athl->appendChild($aid);
			$athl->appendChild($aname);
			foreach($a['games'] as $g)
			{
				$game = $dom->createElement('game');
				$gid = $dom->createElement('gid', $g['gid']);
				$gconcat = $g['year'] . ($g['season'] == 's' ? " Summer ":" Winter ") . "Olympics at " .$g['city'];
				$gname = $dom->createElement('gname', $gconcat);
				$game->appendChild($gid);
				$game->appendChild($gname);
				foreach($g['sports'] as $s)
				{
					$sport = $dom->createElement('sport');
					$sname = $dom->createElement('sname', $s['sname']);
					$sid = $dom->createElement('sid', $s['sid']);
					$sport->appendChild($sid);
					$sport->appendChild($sname);
					foreach($s['disc'] as $key => $d)
					{
						$disc = $dom->createElement('discipline');
						$dname = Discipline::getDiscipline($key);
						$dname = $dom->createElement('dname', $dname->display()[1]);
						$did = $dom->createElement('did', $key);
						$medal = $dom->createElement('medal', $d['medal']);
						$disc->appendChild($did);
						$disc->appendChild($dname);
						$disc->appendChild($medal);
						$sport->appendChild($disc);
					}
					$game->appendChild($sport);
				}
				$athl->appendChild($game);
			}
			$root->appendChild($athl);
		}

		$xml = $dom->saveXML($root);
		$xml = preg_replace('/ +</', '<', $xml);
		return $xml;
	
	}


	public static function search($keyword)
	{
		global $conn;
		$query = "
			SELECT DISTINCT a.aid
				, a.aname
				, c.cname
				, c.iocCode
				, s.sname
				, s.sid
			FROM athlete a
				, represents r
				, participation p
				, country c
				, sport s
				, discipline d
				, game g
			GROUP BY a.aid
				, r.aid
				, p.aid
				, r.gid
				, a.aname
				, c.cname
				, s.sname
				, d.sid
				, s.sid
				, r.iocCode
				, c.iocCode
				, p.did
				, d.did
				, g.year
				, g.gid
			HAVING a.aid = r.aid 
				AND a.aid = p.aid 
				AND p.did=d.did 
				AND d.sid=s.sid 
				AND r.iocCode=c.iocCode 
				AND r.gid=g.gid
				AND g.year <= ALL 
					(
					SELECT g2.year 
					FROM game g2
						, represents r2 
					WHERE r2.aid=r.aid 
						AND r2.gid=g2.gid
					)
				AND a.aname LIKE '%$keyword%'
		";
		$statement = $conn->prepare($query);
		$statement->execute();
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		if (empty($result))
			return false;
		else
		{
			$dom = new DomDocument("1.0", "UCS-2");
			$root = $dom->createElement('athletes');
			foreach ($result as $a)
			{
				$athlete = $dom->createElement('athlete');
				$aname = $dom->createElement('aname', utf8_encode($a['aname']));
				$athlete->setAttribute('aid', $a['aid']);
				$cname = $dom->createElement('cname', $a['cname']);
				$cname->setAttribute('iocCode', $a['iocCode']);
				$sname = $dom->createElement('sname', $a['sname']);
				$sname->setAttribute('sid', $a['sid']);
				$athlete->appendChild($aname);
				$athlete->appendChild($cname);
				$athlete->appendChild($sname);
				$root->appendChild($athlete);
			}
			return $dom->saveXML($root);
		}
	}

}
?>
