<?php
require ("config.php");
require ("funcsv2.php");
session_start();
if (!$_SESSION['admin_logged_in'])
{
	header("Location: authenticate.php?status=session");
	exit();
}
?>

<html>
<head>
	<title>Upload Statistics</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link rel="stylesheet" href="./css/style.css" type="text/css" />
</head>
<body>
<h1>Upload Statistics</h1>
<h2>This may be wildly inaccurate because when torrents are deleted, the bittorrent traffic is removed yet the HTTP traffic stays the same.</h2>
<?php
$query = "SELECT SUM(".$prefix."summary.dlbytes) FROM ".$prefix."summary";
$results = $sql->query($query);
$data = $results->fetch_row();
if ($data[0] == null)
	$btuploaded = 0;
else
	$btuploaded = $data[0];
	
$query = "SELECT total_uploaded FROM ".$prefix."speedlimit";
$results = $sql->query($query);
$data = $results->fetch_row();
$httpuploaded = $data[0];
?>
<br>
<center>
<table>
<tr><th>HTTP Seeding Uploaded<span class="notice">*</span></th>
<th>Bittorrent P2P Seeding Uploaded</th></tr>
<tr>
<td align="center">
<?php
echo bytesToString($httpuploaded);
?>
</td>
<td align="center">
<?php
echo bytesToString($btuploaded);
?>
</td>
</tr>
<tr>
<td align="center">
<?php
if ($httpuploaded + $btuploaded != 0)
	echo round(($httpuploaded / ($httpuploaded + $btuploaded))*100, 2) . "%";
else
	echo "0%";
?>
</td>
<td align="center">
<?php
if ($httpuploaded + $btuploaded != 0)
	echo round(($btuploaded / ($httpuploaded + $btuploaded))*100, 2) . "%";
else
	echo "0%";
?>
</td>
</tr>
</table>
</center>
<p align="center">
<?php
echo "Total Uploaded: " . bytesToString($httpuploaded + $btuploaded);
?>
</p>
<br>
<span class="notice">* - This does not include the GetRight HTTP seeding format which links directly to files.</span>
<br><br>
<a href="admin.php"><img src="images/admin.png" border="0" class="icon" alt="Admin Page" title="Admin Page" /></a><a href="admin.php">Return to Admin Page</a>
</body>
</html>