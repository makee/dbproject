<?
header("Content-Type: text/xml");
include_once('connect.php');
include_once('class.php');

$_POST = $_GET;
if (isset($_POST['action']) && $_POST['action'] == "top10" && isset($_POST['type']))
{
	if(!isset($_POST['limit']))
		$_POST['limit'] = 10;
	$limit = $_POST['limit'];
	$res = Athlete::getAthlete($limit);		
	$dom = new DomDocument("1.0", "UCS-2");
	$root = $dom->createElement('result');
	$list = array();
	foreach($res as $re)
	{
		$athl = $dom->createElement('athlete');
		//$list[] = $re->listAttrib();
		foreach($re as $key => $attrib)
		{
			$attrib = utf8_encode($attrib);
			$elem = $dom->createElement($key, $attrib);
			$athl->appendChild($elem);
		}
		$root->appendChild($athl);
	}

	$dom->appendChild($root);
	$xmlData = $dom->saveXML($root);
	//echo json_encode($list);
	echo $xmlData;
}

?>
