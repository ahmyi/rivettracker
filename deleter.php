<?php
require ("config.php");
session_start();
if (!$_SESSION['admin_logged_in'])
{
	header("Location: authenticate.php?status=session");
	exit();
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Delete Torrent(s) From Database</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link rel="stylesheet" type="text/css" href="./css/style.css" />
	<script language="javascript">
	function selectRow(checkBox)
	{
		if (checkBox.value % 2 == 1)
			var Style = "row1";
		else
			var Style = "row0";
		if (checkBox.checked == true)
			var Style = "selected";
		var el = checkBox.parentNode;
		while(el.tagName.toLowerCase() != "tr")
		{
			el = el.parentNode;
    		}
    		el.className = Style;
	}
	</script>
  </head>
  <body>
    <form action="<?php echo $_SERVER["PHP_SELF"];?>"  method="POST">
<?php
require_once("funcsv2.php");
if (isset($dbuser) && isset($dbpass))
{
	foreach ($_POST as $left => $right)
	{
		if (strlen($left) == 41)
		{
			if (!is_numeric($right) || !verifyHash(substr($left, 1)))
				continue;
			$hash = substr($left, 1);
			$query = "SELECT filename FROM ".$prefix."namemap WHERE info_hash =\"$hash\"";
			$delete_file = $sql->query($query);
			$delete = $sql->fetch_row($delete_file);
			unlink("torrents/" . $delete[0] . ".torrent");
			$sql->query("DELETE FROM " . $prefix . "summary WHERE info_hash=\"$hash\"");
			$sql->query("DELETE FROM " . $prefix . "namemap WHERE info_hash=\"$hash\""); 
			$sql->query("DELETE FROM " . $prefix . "timestamps WHERE info_hash=\"$hash\"");
			$sql->query("DELETE FROM " . $prefix . "webseedfiles WHERE info_hash=\"$hash\"");
			$sql->query("DROP TABLE " . $prefix . "y$hash");
			$sql->query("DROP TABLE " . $prefix . "x$hash");
			$sql->query("OPTIMIZE TABLE " . $prefix . "summary");
			$sql->query("OPTIMIZE TABLE " . $prefix . "namemap");
			$sql->query("OPTIMIZE TABLE " . $prefix . "timestamps");
			require_once("rss_generator.php");
		}
	}
}
else
{
	$GLOBALS["maydelete"] = false;
}

?>
  <h1>Delete Torrent(s) From Database</h1>
  <table class="torrentlist" cellspacing="1">
    <tr>
      <th>Name/Info Hash</th>
      <th>File Size</th>
      <th>Seeders</th>
      <th>Leechers</th>
      <th>Completed D/Ls</th>
      <th>Bytes Transfered</th>
      <th>Delete?</th>
    </tr>
<?php
$results = $sql->query("SELECT ".$prefix."summary.info_hash, ".$prefix."namemap.size, ".$prefix."summary.seeds, ".$prefix."summary.leechers, format(".$prefix."summary.finished,0), format(".$prefix."summary.dlbytes/1073741824,3), ".$prefix."namemap.filename FROM ".$prefix."summary LEFT JOIN ".$prefix."namemap ON ".$prefix."summary.info_hash = ".$prefix."namemap.info_hash ORDER BY ".$prefix."namemap.filename") or die(errorMessage() . "" . mysql_error() . "</p>");
$i = 0;
while ($data = $results->fetch_row()) 
{
	$writeout = "row" . $i % 2;
	$hash = $data[0];
	if(is_null($data[6]))
		$data[6] = $data[0];
	if(strlen($data[6]) == 0)
		$data[6] = $data[0];
	echo "<tr class=\"$writeout\">\n";
	echo "\t<td>".$data[6]."</td>\n";
	echo "\t<td>".bytesToString($data[1])."</td>\n";
	for ($j=2; $j < 5; $j++)
		echo "\t<td class=\"center\">$data[$j]</td>\n";
	echo "\t<td class=\"center\">$data[5] GB</td>\n";
	
	echo "\t<td class=\"center\"><input type=\"checkbox\" name=\"x$hash\" value=\"$i\" onclick=\"selectRow(this);\"/></td>\n";
	echo "</tr>\n";
	$i++;
}
?>
  </table>
    <p class="error">Warning: there is no confirmation for deleting files. Clicking this button is final.</p>
    <p class="center"><input type="submit" value="Delete" /></p>
    </form>
    <a href="index.php"><img src="images/stats.png" border="0" class="icon" alt="Tracker Statistics" title="Tracker Statistics" /></a><a href="index.php">Return to Statistics Page</a><br>
    <a href="admin.php"><img src="images/admin.png" border="0" class="icon" alt="Admin Page" title="Admin Page" /></a><a href="admin.php">Return to Admin Page</a>
  </body>
</html>
