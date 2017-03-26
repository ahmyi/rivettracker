<?php
require_once ("config.php");
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
	exit();
//if hash isn't of length 40, don't even bother connecting to database
if (strlen($_GET['hash']) != 40)
{
	header("index.php"); 	
  	exit();
}
require_once ("funcsv2.php"); //required for errorMessage()
$results = $sql->query("SELECT filename FROM ".$prefix."namemap WHERE info_hash = '" . $_GET['hash'] . "'");
$row = $sql->fetch_row($results);
if ($row[0] == null)
	exit(header("Location: index.php"));
else
	$filename = $row[0];
if(!file_exists("./torrents/" . $filename . ".torrent"))
  	exit(header("Location: index.php"));
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
	exit(header("Location: index.php"));
header('Pragma: no-cache');
header('Cache-Control: no-cache, no-store, must-revalidate');
?>