<?php

require ("config.php");
require ("funcsv2.php");
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
	<title>Admin Page</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link rel="stylesheet" href="./css/style.css" type="text/css" />
</head>
<body>
<h1>Admin Page</h1>

<a href="newtorrents.php"><img src="images/add.png" border="0" class="icon" alt="Add Torrent" title="Add Torrent" /></a><a href="newtorrents.php">Add Torrent to Tracker Database</a><br>
<a href="batch_upload.php"><img src="images/batch_upload.png" border="0" class="icon" alt="Batch Upload Torrents" title="Batch Upload Torrents" /></a><a href="batch_upload.php">Batch Upload Torrents</a><br>
<a href="edit_database.php"><img src="images/database.png" border="0" class="icon" alt="Edit Torrent in Database" title="Edit Torrent in Database" /></a><a href="edit_database.php">Edit Torrent Already in Database</a><br>
<a href="DumpTorrentCGI.php"><img src="images/torrent.png" border="0" class="icon" alt="Show Information on Torrent" title="Show Information on Torrent" /></a><a href="DumpTorrentCGI.php">Show Information on Torrent File</a><br>
<a href="index.php"><img src="images/stats.png" border="0" class="icon" alt="Tracker Statistics" title="Tracker Statistics" /></a><a href="index.php">Show Current Tracker Statistics</a><br>
<a href="sanity.php"><img src="images/check.png" border="0" class="icon" alt="Check for Expired Peers" title="Check for Expired Peers" /></a><a href="sanity.php">Check Tracker for Expired Peers</a><br>
<a href="statistics.php"><img src="images/userstats.png" border="0" class="icon" alt="User Statistics" title="User Statistics" /></a><a href="statistics.php">Detailed User Statistics from Tracker</a><br>
<a href="deleter.php"><img src="images/delete.png" border="0" class="icon" alt="Delete Torrent" title="Delete Torrent" /></a><a href="deleter.php">Delete Torrent from Tracker Database</a><br>
<a href="editconfig.php"><img src="images/edit.png" border="0" class="icon" alt="Edit Config File" title="Edit Config File" /></a><a href="editconfig.php">Edit Configuration Settings</a><br>
<a href="uploadstats.php"><img src="images/download.png" border="0" class="icon" alt="Upload Statistics" title="Upload Statistics" /></a><a href="uploadstats.php">Upload Statistics</a><br>
<a href="css.php"><img src="images/color.png" border="0" class="icon" alt="Change CSS File" title="Change CSS File" /></a><a href="css.php">Change CSS File</a><br>
<a href="./docs/help.html"><img src="images/help.png" border="0" class="icon" alt="Help" title="Help" /></a><a href="./docs/help.html">Help</a><br>
<a href="authenticate.php?status=logout"><img src="images/logout.png" border="0" class="icon" alt="Logout" title="Logout" /></a><a href="authenticate.php?status=logout">Logout</a><br>

<?php
//Check for install.php file, security risk if still available
if (file_exists("install.php"))
{
	echo errorMessage() . "Your install.php file has NOT been deleted.  This is a security risk, please delete it immediately.</p>\n";
}

if (!is_writeable("./torrents/"))
{
	echo errorMessage() . "The 'torrents' folder does not have write access, check the permissions.</p>\n";
}

if (!is_writeable("./rss/"))
{
	echo errorMessage() . "The 'rss' folder does not have write access, check the permissions.</p>\n";
}

?>
</body>
</html>