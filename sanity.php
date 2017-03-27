<?php
require ("config.php");
require_once("funcsv2.php");
//Check session
session_start();

if (!$_SESSION['admin_logged_in'])
{
	//check fails
	header("Location: authenticate.php?status=session");
	exit();
}
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Check Tracker for Expired Peers</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link rel="stylesheet" type="text/css" href="./css/style.css" />
</head>
<body>
<h1>Check Tracker for Expired Peers</h1>
<?php


error_reporting(E_ALL);
//header("Content-Type: text/plain");

//require_once("config.php");
//require_once("funcsv2.php");

$summaryupdate = array();

// Non-persistant: we lock tables!

if (isset($_GET["nolock"]))
	$locking = false;
else
	$locking = true;

// Assumes success
if ($locking)
	$sql->query("LOCK TABLES ".$prefix."summary WRITE, ".$prefix."namemap READ");

?>
<table class="torrentlist" cellspacing="1">
<!-- Column Headers -->
<tr>
	<th>Name/Info Hash</th>
	<th>Seeders</th>
	<th>Leechers</th>
	<th>Bytes Transfered</th>
	<th>Stale Clients</th>
	<th>Peer Cache</th>
</tr>
<?php

$results = $sql->query("SELECT ".$prefix."summary.info_hash, seeds, leechers, dlbytes, ".$prefix."namemap.filename FROM ".$prefix."summary LEFT JOIN ".$prefix."namemap ON ".$prefix."summary.info_hash = ".$prefix."namemap.info_hash");

$i = 0;

while ($row = $results->fetch_row())
{
	$writeout = "row" . $i % 2;
	list($hash, $seeders, $leechers, $bytes, $filename) = $row;
	if ($locking)
	{
		//peercaching ALWAYS on
		$sql->query("LOCK TABLES ".$prefix."x$hash WRITE, ".$prefix."y$hash WRITE, ".$prefix."summary WRITE");
	}
	$results2 = $sql->query("SELECT status, COUNT(status) from ".$prefix."x$hash GROUP BY status");
	echo "<tr class=\"$writeout\"><td>";
	if (!is_null($filename))
		echo $filename;
	else
		echo $hash;
	echo "</td>";
	if (!$results2)
	{
		echo "<td colspan=\"4\">Unable to process:<td></tr>";
		continue;
	}

	$counts = array();
	while ($row = $results2->fetch_row())
		$counts[$row[0]] = $row[1];	
	if (!isset($counts["leecher"]))
		$counts["leecher"] = 0;
	if (!isset($counts["seeder"]))
		$counts["seeder"] = 0;

	if ($counts["seeder"] != $seeders)
	{
		$sql->query("UPDATE ".$prefix."summary SET seeds=".$counts["seeder"]." WHERE info_hash=\"$hash\"");
		echo "<td class=\"center\">$seeders -> ".$counts["seeder"]."</td>";
	}
	else
		echo "<td class=\"center\">$seeders</td>";
		
	if ($counts["leecher"] != $leechers)
	{
		$sql->query("UPDATE ".$prefix."summary SET leechers=".$counts["leecher"]." WHERE info_hash=\"$hash\"");
		echo "<td class=\"center\">$leechers -> ".$counts["leecher"]."</td>";
	}
	else
		echo "<td class=\"center\">$leechers</td>";
		
	if ($counts["leecher"] == 0)
	{
		//If there are no leechers, set the speed to zero
		$sql->query("UPDATE ".$prefix."summary set speed=0 WHERE info_hash=\"$hash\"");
	}

	if ($bytes < 0)
	{
		$sql->query("UPDATE ".$prefix."summary SET dlbytes=0 WHERE info_hash=\"$hash\"");
		echo "<td class=\"center\">$bytes -> Zero</td>";
	}
	else
		echo "<td class=\"center\">". round($bytes/1048576/1024,3) ." GB</td>";

	myTrashCollector($hash, $report_interval, time(), $writeout);
	echo "<td class=\"center\">";
	
	$result = $sql->query("SELECT ".$prefix."x$hash.sequence FROM ".$prefix."x$hash LEFT JOIN ".$prefix."y$hash ON ".$prefix."x$hash.sequence = ".$prefix."y$hash.sequence WHERE ".$prefix."y$hash.sequence IS NULL") or die(errorMessage() . "" . mysql_error() . "</p>");
	if ($result->num_rows > 0)
	{
		echo "Added ", $result->num_rows;
		$row = array();
		
		while ($data = $result->fetch_row())
				$row[] = "sequence=\"${data[0]}\"";
		$where = implode(" OR ", $row);
		$query = $sql->query("SELECT * FROM ".$prefix."x$hash WHERE $where");
		
		while ($row = $query->fetch_assoc())
		{
			$compact = mysql_escape_string(pack('Nn', ip2long($row["ip"]), $row["port"]));
				$peerid = mysql_escape_string('2:ip' . strlen($row["ip"]) . ':' . $row["ip"] . '7:peer id20:' . hex3bin($row["peer_id"]) . "4:porti{$row["port"]}e");
			$no_peerid = mysql_escape_string('2:ip' . strlen($row["ip"]) . ':' . $row["ip"] . "4:porti{$row["port"]}e");
			$sql->query("INSERT INTO ".$prefix."y$hash SET sequence=\"{$row["sequence"]}\", compact=\"$compact\", with_peerid=\"$peerid\", without_peerid=\"$no_peerid\"");
		}
	}	
	else
		echo "Added none";

	$result = $sql->query("SELECT ".$prefix."y$hash.sequence FROM ".$prefix."y$hash LEFT JOIN ".$prefix."x$hash ON ".$prefix."y$hash.sequence = ".$prefix."x$hash.sequence WHERE ".$prefix."x$hash.sequence IS NULL");
	if (mysql_num_rows($result) > 0)
	{
		echo ", Deleted ",$result->num_rows;

		$row = array();
		
		while ($data = $result->fetch_row())
			$row[] = "sequence=\"${data[0]}\"";
		$where = implode(" OR ", $row);
		$query = $sql->query("DELETE FROM ".$prefix."y$hash WHERE $where");
	}
	else
		echo ", Deleted none";

	echo "</td>";
	
	echo "</tr>\n";
	$i ++;


	if ($locking)
		$sql->query("UNLOCK TABLES");
		
	//Repair tables, is this necessary?  Sometimes the tables crash...
	//Can't repair table if locked?
	//quickQuery("REPAIR Table x$hash");
	//quickQuery("REPAIR Table y$hash");

	// Finally, it's time to do stuff to the summary table.
	if (!empty($summaryupdate))
	{
		$stuff = "";
		foreach ($summaryupdate as $column => $value)
		{
			$stuff .= ', '.$column. ($value[1] ? "=" : "=$column+") . $value[0];
		}
		mysql_query("UPDATE ".$prefix."summary SET ".substr($stuff, 1)." WHERE info_hash=\"$hash\"");
		$summaryupdate = array();
	}


}

function myTrashCollector($hash, $timeout, $now, $writeout)
{
//	error_log("Trash collector working on $hash");
 	require("config.php");
 	$peers = loadLostPeers($hash, $timeout);
 	for ($i=0; $i < $peers["size"]; $i++)
	        killPeer($peers[$i]["peer_id"], $hash, $peers[$i]["bytes"], $peers[$i]);
	if ($i != 0)
		echo "<td class=\"center\">Removed $i</td>";
	else
		echo "<td class=\"center\">Removed 0</td>";
 	$sql->query("UPDATE ".$prefix."summary SET lastcycle='$now' WHERE info_hash='$hash'");
}




?>
</table>
<p><a href="sanity.php?nolock=on">Not working? Try running this.</a></p>
<a href="index.php"><img src="images/stats.png" border="0" class="icon" alt="Tracker Statistics" title="Tracker Statistics" /></a><a href="index.php">Return to Statistics Page</a><br>
<a href="admin.php"><img src="images/admin.png" border="0" class="icon" alt="Admin Page" title="Admin Page" /></a><a href="admin.php">Return to Admin Page</a>
</body>
</html>
