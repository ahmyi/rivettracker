<?php

require_once ("config.php");

//Check session only if hiddentracker is TRUE
if ($hiddentracker == true)
{
	session_start();
	
	if (!$_SESSION['admin_logged_in'] && !$_SESSION['upload_logged_in'])
	{
		//check fails
		header("Location: authenticate.php?status=error");
		exit();
	}
}
else
{
	//don't run
	exit();
}


//if hash isn't of length 40, don't even bother connecting to database
if (strlen($_GET['hash']) != 40)
{
	header("index.php"); 	
  	exit();
}

require_once ("funcsv2.php"); //required for errorMessage()

//connect to database and turn hash value into a filename
if ($GLOBALS["persist"])
	$db = mysql_pconnect($dbhost, $dbuser, $dbpass) or die(errorMessage() . "Tracker error: can't connect to database - " . mysql_error() . "</p>");
else
	$db = mysql_connect($dbhost, $dbuser, $dbpass) or die(errorMessage() . "Tracker error: can't connect to database - " . mysql_error() . "</p>");
mysql_select_db($database) or die(errorMessage() . "Tracker error: can't open database $database - " . mysql_error() . "</p>");
$query = "SELECT filename FROM ".$prefix."namemap WHERE info_hash = '" . $_GET['hash'] . "'";
$results = mysql_query($query) or die(errorMessage() . "Can't do SQL query - " . mysql_error() . "</p>");
$row = mysql_fetch_row($results);

if ($row[0] == null)
{
	//hash doesn't exist in database, error out
	header("Location: index.php");
  	exit();
}
else
	$filename = $row[0];

if (!file_exists("./torrents/" . $filename . ".torrent"))
{
  	header("Location: index.php");
  	exit();
}

//you have be referred from the main website URL then you can download
if (strpos($_SERVER['HTTP_REFERER'], $website_url . "/") === 0 && strpos($_SERVER['HTTP_REFERER'], "http") === 0)
{
  	$stat = stat("./torrents/" . $filename . ".torrent");
  	header("Content-Type: application/x-bittorrent");
  	header("Content-Length: " . $stat[7]);
  	header("Last-Modified: " . gmdate("D, d M Y H:i:s", $stat[9]) . " GMT");
  	header("Content-Disposition: attachment; filename=\"" . $filename . ".torrent\"");
  	readfile("./torrents/" . $filename . ".torrent");
  	exit();
}
else
{
	header("Location: index.php");
	exit();
}

header('Pragma: no-cache');
header('Cache-Control: no-cache, no-store, must-revalidate');
?>