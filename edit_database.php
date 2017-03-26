<?php
require ("config.php");
require_once ("funcsv2.php");
session_start();
if (!$_SESSION['admin_logged_in'])
	exit(header("Location: authenticate.php?status=session"));
?>
<html>
<head>
	<title>Edit Torrent in Database</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link rel="stylesheet" href="./css/style.css" type="text/css" />
</head>
<body>
<h1>Edit Torrent in Database</h1>
<h2>This page allows you to edit torrents that are already in the database.  If you need to change other things about
the torrent please <a href="deleter.php">delete it</a> and add it again.</h2>
	
<?php
//get filename from URL string
$filename = $_GET['filename'];

//if not edit database or filename set, display all torrents as links
if (!isset($_POST["editdatabase"]) && !isset($filename))
{
	?>
	<p><strong>Click on a file to edit it:</strong></p>
	<table border="0">
	<?php
	$rows = $sql->query("SELECT filename FROM ".$prefix."namemap ORDER BY filename ASC");
	
	while($data = $sql->fetch_row($rows))
	{
		echo "<tr><td><a href=\"" . $PHP_SELF . "?filename=" . rawurlencode($data[0]) . "\">" . $data[0] . "</a></td></tr>\n";
  }
	?>
	</table>
	<?php
}

if (isset($filename) && !isset($_POST["editdatabase"]))
{
	$rows = $sql->query("SELECT info_hash,filename,url,pubDate FROM ".$prefix."namemap WHERE filename = '" . $filename . "'");
	
	$data = $sql->fetch_row($rows);
	?>
	<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="POST">
	<input type="hidden" name="editdatabase" value="1">
	<input type="hidden" name="<?php echo $data[0];?>" value="<?php echo $data[0];?>">
	<input type="hidden" name="<?php echo $data[0] . "_old_filename";?>" value="<?php echo $data[1];?>">
	<table border="0">
	<tr><td><b>Info Hash: </b></td><td><?php echo $data[0];?></td></tr>
	<tr><td><b>Filename:</b></td><td><input type="text" name="<?php echo $data[0] . "_filename";?>" size="60" value="<?php echo $data[1];?>"></td></tr>
	<tr><td><b>URL:</b></td><td><input type="text" name="<?php echo $data[0] . "_url";?>" size="60" value="<?php echo $data[2];?>"></td></tr>
	<tr><td><b>Publication Date:</b></td><td><input type="text" name="<?php echo $data[0] . "_pubDate";?>" size="60" value="<?php echo $data[3];?>"></td></tr>
	<tr><td><hr></td><td><hr></td></tr>		
	
	</table>
	<br>
	<input type="submit" value="Edit Entry">
	</form>
	
	<?php
}

if (isset($_POST["editdatabase"]))
{
	$temp_counter = (count($_POST)-1)/5;
	array_shift($_POST);
	
	for ($i = 0; $i < $temp_counter; $i++)
	{
		$temp_hash = htmlspecialchars(array_shift($_POST));
		$old_filename = htmlspecialchars(array_shift($_POST));
		$temp_filename = array_shift($_POST);
		$temp_filename = Ltrim($temp_filename);
		$temp_filename = htmlspecialchars(rtrim($temp_filename));
		$temp_url = htmlspecialchars(array_shift($_POST));
		$temp_pubDate = htmlspecialchars(array_shift($_POST));
		$sql->query("UPDATE ".$prefix."namemap SET filename=\"$temp_filename\", url=\"$temp_url\", pubDate=\"$temp_pubDate\" WHERE info_hash=\"$temp_hash\"");
		//if filename changes, rename .torrent
		if ($old_filename != $temp_filename)
			rename("torrents/" . $old_filename . ".torrent", "torrents/" . $temp_filename . ".torrent");
	}
	require_once("rss_generator.php");
	echo "<br><p class=\"success\">The database was edited successfully!</p>\n";
}

?>
    <br>
    <br>
    <a href="admin.php"><img src="images/admin.png" border="0" class="icon" alt="Admin Page" title="Admin Page" /></a><a href="admin.php">Return to Admin Page</a>
  </body>
</html>
