<?php
$queryCreate = array(
	"CREATE TABLE Sport
	(
		sid CHAR (7) NOT NULL,
		sname CHAR (40) NOT NULL,
		PRIMARY KEY (sid)
	);",
	"CREATE TABLE Discipline
	(
		did CHAR (7) NOT NULL,
		dname CHAR (60) NOT NULL,
		sid CHAR (7) NOT NULL,
		PRIMARY KEY (did),
		FOREIGN KEY (sid) REFERENCES Sport (sid)
	);",
	"CREATE TABLE Country
	(
		iocCode CHAR (3) NOT NULL,
		cname CHAR (40) NOT NULL,
		PRIMARY KEY (iocCode)
	);",
	"CREATE TABLE Game
	(
		gid CHAR (8) NOT NULL,
		year CHAR (4) NOT NULL,
		season CHAR (1) NOT NULL,
		city CHAR (20) NOT NULL,
		iocCode CHAR (3) NOT NULL,
		PRIMARY KEY (gid),
		FOREIGN KEY (iocCode) REFERENCES Country(iocCode)
	);",
	"CREATE TABLE Athlete
	(
		aid CHAR (7) NOT NULL,
		aname CHAR (30) NOT NULL,
		PRIMARY KEY (aid)
	);",
	"CREATE TABLE Participation
	(
		aid CHAR (7) NOT NULL,
		did CHAR (7) NOT NULL,
		gid CHAR (8) NOT NULL,
		medal INTEGER,
		PRIMARY KEY (aid, gid, did),
		FOREIGN KEY (aid) REFERENCES Athlete(aid),
		FOREIGN KEY (did) REFERENCES Discipline(did),
		FOREIGN KEY (gid) REFERENCES Game(gid)
	);",
	"CREATE TABLE Represents
	(
		aid CHAR (7) NOT NULL,
		iocCode CHAR (3) NOT NULL,
		gid CHAR (8) NOT NULL,
		PRIMARY KEY (aid, iocCode, gid),
		FOREIGN KEY (aid) REFERENCES Athlete(aid),
		FOREIGN KEY (iocCode) REFERENCES Country(iocCode),
		FOREIGN KEY (gid) REFERENCES Game(gid)
	);",
	"CREATE TABLE eventOf
	(
		did CHAR (7) NOT NULL,
		gid CHAR (8) NOT NULL,
		PRIMARY KEY (did, gid),
		FOREIGN KEY (did) REFERENCES Discipline(did),
		FOREIGN KEY (gid) REFERENCES Game(gid)
	);");
$i = 1;
foreach ($queryCreate as $query){
	if ($i > 1)
		$conn->query($query);
	$i ++;
}
/*
$queryShowTables = "SHOW TABLES FROM olympics";
$tables = $conn->query($queryShowTables);
$disp = "<table>";
foreach ($tables->fetchAll() as $table){
	$disp .= "<tr>";
	$disp .= "<td>$table[0]</td>";
	$columns = $conn->query("SHOW COLUMNS FROM $table[0]");
	foreach ($columns as $column){
		$disp .= "<td>$column[0]</td>";
	}
	$disp .= "</tr>";
}
$disp .= "</table>";
echo $disp;
*/
?>
