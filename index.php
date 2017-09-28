<?php
//if config.php file not available, error out
if (!file_exists("config.php"))
{
	echo "<font color=red><strong>Error: config.php file is not available.  Did you forget to upload it?" .
	" If you haven't run the installer yet, please do so <a href=\"install.php\">here.</a></strong></font>";
	exit();
}

require_once ("config.php");
require_once ("funcsv2.php");

//Check session only if hiddentracker is TRUE
if ($hiddentracker == true)
{
	session_start();
	
	if (!$_SESSION['admin_logged_in'] && !$_SESSION['upload_logged_in'])
	{
		//check fails
		header("Location: authenticate.php?status=indexlogin");
		exit();
	}
}

if(rand(1, 10) == 1)
{
	//10% of the time, run sanity_no_output.php to prune database and keep users fresh
	include("sanity_no_output.php");
}

//variables for column totals
$total_disk_usage = 0;
$total_seeders = 0;
$total_leechers = 0;
$total_downloads = 0;
$total_bytes_transferred = 0;
$total_speed = 0;

$scriptname = $_SERVER["PHP_SELF"] . "?";
if (!isset($GLOBALS["countbytes"]))
	$GLOBALS["countbytes"] = true;

include('header.php');
echo '<div class="container">';
if ($GLOBALS["title"] != "")
  echo "<h1>".$GLOBALS["title"]."</h1>";
else
  echo "<h1>Tracker Index</h1>";
?>
<table>
<tr>
	<?php
	//Cleanup page number to prevent XSS
	@$_GET["page_number"] = htmlspecialchars(@$_GET["page_number"]);
	$scriptname = htmlspecialchars($scriptname);
	
	if (!isset($_GET["activeonly"]))
		$scriptname = $scriptname . "activeonly=	yes&amp;";
	if (isset($_GET["seededonly"]) && !isset($_GET["activeonly"]))
	{
		$scriptname = $scriptname . "seededonly=yes&";
		$_GET["page_number"] = 1;
	}
	if (isset($_GET["page_number"]))
		$scriptname = $scriptname . "page_number=" . $_GET["page_number"] . "&amp;";
		
	if (isset($_GET["activeonly"]))
		echo "<td><a href=\"$scriptname\">Show all torrents</a></td>\n";
	else
		echo "<td><a href=\"$scriptname\">Show only active torrents</a></td>\n";
		
	$scriptname = $_SERVER["PHP_SELF"] . "?";
	$scriptname = htmlspecialchars($scriptname);
	
	if (!isset($_GET["seededonly"]))
		$scriptname = $scriptname . "seededonly=yes&amp;";
	if (isset($_GET["activeonly"]) && !isset($_GET["seededonly"]))
	{
		$scriptname = $scriptname . "activeonly=yes&";
		$_GET["page_number"] = 1;
	}
	if (isset($_GET["page_number"]))
		$scriptname = $scriptname . "page_number=" . $_GET["page_number"] . "&amp;";
		
	if (isset($_GET["seededonly"]))
		echo "<td align=\"right\"><a href=\"$scriptname\">Show all torrents</a></td>\n";
	else
		echo "<td align=\"right\"><a href=\"$scriptname\">Show only seeded torrents</a></td>\n";
		
	$scriptname = $_SERVER["PHP_SELF"] . "?";
	$scriptname = htmlspecialchars($scriptname);
	
	?>
</tr>
</table>

<?php
if (isset($_GET["seededonly"]))
	$where = " WHERE seeds > 0";
else if (isset($_GET["activeonly"]))
	$where = " WHERE leechers+seeds > 0";
else
	$where = " ";

$query = "SELECT COUNT(*) FROM ".$prefix."summary $where";
$results = $sql->query($query);
$res = $results->data_seek(0);

if (isset($_GET["activeonly"]))
	$scriptname = $scriptname . "activeonly=yes&";
if (isset($_GET["seededonly"]))
	$scriptname = $scriptname . "seededonly=yes&";

echo "<p align='center'>Page: \n";
$count = 0;
$page = 1;
while($count < $res)
{
	if (isset($_GET["page_number"]) && $page == $_GET["page_number"])
		echo "<b><a href=\"$scriptname" . "page_number=$page\">($page)</a></b>-\n";
	else if (!isset($_GET["page_number"]) && $page == 1)
		echo "<b><a href=\"$scriptname" . "page_number=$page\">($page)</a></b>-\n";
	else
		echo "<a href=\"$scriptname" . "page_number=$page\">$page</a>-\n";
	$page++;
	$count = $count + 10;
}
echo "</p>\n";
?>

<table>
<tr>
	<td>
	<table class="table table-bordered">

	<!-- Column Headers -->
	<tr>
		<th>Name/Info Hash</th>
		<th>Seeders</th>
		<th>Leechers</th>
		<th>Completed D/Ls</th>
		<?php
		// Bytes mode off? Ignore the columns
		if ($GLOBALS["countbytes"])
			echo '<th>Bytes Transferred</th><th>Speed (rough estimate)</th>';
		?>
	</tr>
	
<?php
if (!isset($_GET["page_number"]))
	$query = "SELECT ".$prefix."summary.info_hash, ".$prefix."summary.seeds, ".$prefix."summary.leechers, ".$prefix."summary.finished, ".$prefix."summary.dlbytes, ".$prefix."namemap.filename, ".$prefix."namemap.url, ".$prefix."namemap.size, ".$prefix."summary.speed FROM ".$prefix."summary LEFT JOIN ".$prefix."namemap ON ".$prefix."summary.info_hash = ".$prefix."namemap.info_hash $where ORDER BY ".$prefix."namemap.filename LIMIT 0,10";
else
{
	if ($_GET["page_number"] <= 0) //account for possible negative number entry by user
		$_GET["page_number"] = 1;
	
	$page_limit = ($_GET["page_number"] - 1) * 10;
	$query = "SELECT ".$prefix."summary.info_hash, ".$prefix."summary.seeds, ".$prefix."summary.leechers, ".$prefix."summary.finished, ".$prefix."summary.dlbytes, ".$prefix."namemap.filename, ".$prefix."namemap.url, ".$prefix."namemap.size, ".$prefix."summary.speed, ".$prefix."namemap.magnet FROM ".$prefix."summary LEFT JOIN ".$prefix."namemap ON ".$prefix."summary.info_hash = ".$prefix."namemap.info_hash $where ORDER BY ".$prefix."namemap.filename LIMIT $page_limit,10";
}

$results = $sql->query($query) or die(errorMessage() . "Can't do SQL query - " . mysqli_error($sql) . "</p>");
$i = 0;

while ($data = $results->fetch_row()) {
	// NULLs are such a pain at times. isset($nullvar) == false
	if (is_null($data[5]))
		$data[5] = $data[0];
	if (is_null($data[6]))
		$data[6] = "";
	if (is_null($data[7]))
		$data[7] = "";
	if (strlen($data[5]) == 0)
		$data[5] = $data[0];
	$myhash = $data[0];
  $magnet = $data[9];
	$writeout = "row" . $i % 2;
	echo "<tr class=\"$writeout\">\n";
	echo "\t<td>";
	if (strlen($data[6]) > 0)
		echo "<a href=\"${data[6]}\">${data[5]}</a> - ";
	else
		echo $data[5] . " - ";
	if ($hiddentracker == true) //obscure direct link to torrent, use dltorrent.php script
		echo "<a href=\"dltorrent.php?hash=" . $myhash . "\">(Torrent)</a><a href='$magnet'>(Magnet)</a>";
	else //just display ordinary direct link
		echo "<a href=\"torrents/" . rawurlencode($data[5]) . ".torrent\">Torrent</a> <a href='$magnet'>(Magnet)</a>";
	if (strlen($data[7]) > 0) //show file size
	{
		echo "<br/>".bytesToString($data[7]);
		$total_disk_usage = $total_disk_usage + $data[7]; //total up file sizes
	}
	echo "</td>\n";
	for ($j=1; $j < 4; $j++) //show seeders, leechers, and completed downloads
	{
		echo "\t<td class=\"center\">$data[$j]</td>\n";
		if ($j == 1) //add to total seeders
			$total_seeders = $total_seeders + $data[1];
		if ($j == 2) //add to total leechers
			$total_leechers = $total_leechers + $data[2];
		if ($j == 3) //add to completed downloads
			$total_downloads = $total_downloads + $data[3];
	}

	if ($GLOBALS["countbytes"])
	{
		echo "\t<td class=\"center\">" . bytestoString($data[4]) . "</td>\n";
		$total_bytes_transferred = $total_bytes_transferred + $data[4]; //add to total GB transferred

		// The SPEED column calculations.
		if ($data[8] <= 0)
		{
			$speed = "0";
			$total_speed = $total_speed - $data[8]; //for total speed column
		}
		else if ($data[8] > 2097152)
			$speed = round($data[8] / 1048576, 2) . " MB/sec";
		else
			$speed = round($data[8] / 1024, 2) . " KB/sec";
		echo "\t<td class=\"center\">$speed</td>\n";
		$total_speed = $total_speed + $data[8]; //add to total speed, in bytes
	}
	echo "</tr>\n";
	$i++;
}

if ($i == 0)
	echo "<tr class=\"row0\"><td style=\"text-align: center;\" colspan=\"6\">No torrents</td></tr>";

//show totals in last row
echo "<tr>";
echo "<th>Space Used: " . bytesToString($total_disk_usage) . "</th>";
echo "<th>" . $total_seeders . "</th>";
echo "<th>" . $total_leechers . "</th>";
echo "<th>" . $total_downloads . "</th>";
if ($GLOBALS["countbytes"]) //stop count bytes variable
{
	echo "<th>" . bytestoString($total_bytes_transferred) . "</th>";
	if ($total_speed > 2097152)
		echo "<th>" . round($total_speed / 1048576, 2) . " MB/sec</th>";
	else
		echo "<th>" . round($total_speed / 1024, 2) . " KB/sec</th>";
}

?>
	</tr>
  </table>
  </td>
</tr>
</table>
</div>
</body>
</html>