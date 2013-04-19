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
	$dom = new DomDocument("1.0", "UTF-8");
	$root = $dom->createElement('result');
	foreach($res as $re)
	{
		foreach($re->listAttrib() as $key => $attrib)
		{
			$attrib = preg_replace('/ +$/', '', $attrib);
			$attrib = utf8_encode($attrib);
			$elem = $dom->createElement($key, $attrib);
			$root->appendChild($elem);
		}
	}

	$dom->appendChild($root);
	$xmlData = $dom->saveXML($root);
	echo $xmlData;
}

?>
