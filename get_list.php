<?
include_once('connect.php');
include_once('class.php');
header("Content-Type: text/xml");


function get_list($table, $where = false)
{
	global $conn;
	if ($where)
		$WHERE = " WHERE sid LIKE '%$where%'";
	else
		$WHERE = "";
	$field = "*";
	$query = "SELECT $field FROM $table $WHERE";
	$stt = $conn->query($query);
	
	$res = $stt->fetchAll(PDO::FETCH_CLASS, $table);
	return $res;
}
if (isset($_GET['sport']))
	$sport = $_GET['sport'];
else
	$sport = false;
$list = get_list($table = $_GET['type'], $sport);
$list_fin = array();
switch ($table)
{
	case "athlete":
	foreach ($list as $athl)
	{
		$list_fin[] = $athl->aname;
	}
	break;
	case "sport":
	foreach ($list as $s)
	{
		$list_fin[] = $s->sname;
	}
	break;
	case "country":
	foreach ($list as $coun)
	{
		$list_fin[] = $coun->cname;
	}
	break;
	case "game":
	foreach ($list as $g)
	{
		$list_fin[] = $g->writeFullGame();
	}
	break;
	case "discipline":
	foreach ($list as $d)
	{
		$dname = $d->display();
		$list_fin[] = $dname[1];
	}
	break;


}
$dom = new DomDocument("1.0", "UCS-2");
$root = $dom->createElement('result');
foreach ($list_fin as $item)
{
	$k = $dom->createElement('item', utf8_encode($item));
	$root->appendChild($k);
}
echo $dom->saveXML($root);
?>
