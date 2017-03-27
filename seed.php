<?php
//Used for HTTP seeding
//Requires information in torrent file for client to use
require_once("config.php");
header("Content-Type: text/plain");

//error_log("One");
if (!isset($_GET["info_hash"]) || !isset($_GET["piece"]))
	reject("400 Bad Request");

if (get_magic_quotes_gpc())
	$info_hash=stripslashes($_GET["info_hash"]);
else
	$info_hash=$_GET["info_hash"];

$piece = $_GET["piece"];
//error_log("Two");

if (!is_numeric($piece) || strlen($info_hash) != 20)
	reject("400 Bad Request");

$info_hash = bin2hex($info_hash);

//error_log("Info hash=$info_hash, piece numnber=$piece");

require_once("config.php");

//change from KB to bytes
$max_upload_rate = $GLOBALS["max_upload_rate"] * 1024;

function Lock($hash, $time = 0)
{
	$results = mysql_query("SELECT GET_LOCK('$hash', $time)");
   $string = mysql_fetch_row($results);
   if (strcmp($string[0], "1") == 0)
   {
   	//error_log("Got lock $hash");
   	return true;
	}
	//error_log("Failed to lock $hash");
   return false;
}

function Unlock($hash)
{
  global $sql;
  $sql->query("SELECT RELEASE_LOCK('$hash')");
}

function reject($error = "503 Service Temporarily Unavailable", $message="")
{
	header("HTTP/1.0 $error");
	echo $message;
	die;
}

if (!Lock("WebSeedLock", 2))
	reject();

$result = $sql->query("SELECT (UNIX_TIMESTAMP() - started) FROM ".$prefix."speedlimit");
$row = $result->fetch_row();

// If nothing has happened for a little while, do NOT
// let that average enable massive bursts.
if ($row[0] > 180)
	$sql->query("UPDATE ".$prefix."speedlimit SET started=UNIX_TIMESTAMP()-1, total_uploaded=total_uploaded+uploaded, uploaded=0");

$result = $sql->query("SELECT uploaded / (UNIX_TIMESTAMP() - started) FROM ".$prefix."speedlimit");
$row = $result->fetch_row();

if ((float)($row[0]) > $max_upload_rate)
{
	$result = $sql->query("SELECT (uploaded/". $max_upload_rate . "+started) - UNIX_TIMESTAMP() FROM ".$prefix."speedlimit");
	$row = $result->fetch_row();
	reject("503 Service Temporarily Unavailable", (int)$row[0] + mt_rand(1,30));
}

$result = $sql->query("SELECT seeds FROM ".$prefix."summary WHERE info_hash=$info_hash");
if ($result)
{
	//error_log("Doing PHPBT check");
	$row = $result->fetch_assoc();
	if ($row["seeds"] > 5) //if there are seeds available, don't use HTTP seeding
		reject();
}
if ($result->num_rows == 0) //hash isn't even in database!
{
	//reject em!
	reject();
}

Unlock("WebSeedLock");

// Max uploads check
for ($lockno=0; $lockno < $GLOBALS["max_uploads"]; $lockno++)
	if (Lock("WebSeed--$lockno", 0))
		break;
//error_log("Lockno=$lockno");
if ($lockno == $GLOBALS["max_uploads"])
	reject();


// Get to work!
$result = $sql->query("SELECT ".$prefix."summary.piecelength, ".$prefix."summary.numpieces FROM ".$prefix."summary WHERE info_hash=\"$info_hash\"");
if (!$result)
	reject("500 Internal Server Error");

$config = $result->etch_assoc();
if (!$config)
	reject("403 Forbidden");

$result = $sql->query("SELECT * FROM ".$prefix."webseedfiles WHERE info_hash=\"$info_hash\" ORDER BY fileorder");

if ($config["numpieces"] < $piece || $piece < 0)
	reject("400 Bad Request");


// Data to return, and accounting.
$xmit = "";
$xmitbytes = 0;

while($row = $result->fetch_assoc())
{
	if (!($piece >= $row["startpiece"] && $piece <= $row["endpiece"]))
		continue;
	$offset = ($row["startpiece"] == $piece) ? 0 : (($piece - $row["startpiece"])*$config["piecelength"] - $row["startpieceoffset"]);
	$fd = fopen($row["filename"], "rb") or reject("500 Internal Server Error");
	if (fseek($fd, $offset) != 0)
		reject("500 Internal Server Error");
	$data = fread($fd, $config["piecelength"]-$xmitbytes);
	if ($data === false)
		reject("500 Internal Server Error");
	$xmit .= $data;
	$xmitbytes += strlen($data);
	if ($xmitbytes == $config["piecelength"])
		break;
	fclose($fd);
}


// Header is most likely already: 200 Ok

//error_log("Send length: $xmitbytes == ".strlen($xmit));

if (isset($_GET["ranges"]))
{
	$myxmit = "";
	$ranges = explode(",", $_GET["ranges"]);
	foreach ($ranges as $blocks)
	{
		$startstop = explode("-", $blocks);
		if (!is_numeric($startstop[0]) || !is_numeric($startstop[1]))
			reject("400 Bad Request");
		if (isset($startstop[2]))
			reject("400 Bad Request");
		$start = $startstop[0];
		$stop = $startstop[1];
		if ($start > $stop)
			reject("400 Bad Request");
		$myxmit .= substr($xmit, $start, $stop-$start+1);
	}
	header("Content-Length: ".strlen($myxmit));
	$sql->query("UPDATE ".$prefix."speedlimit SET uploaded=uploaded+".strlen($myxmit));
	echo $myxmit;
}
else
{
	$sql->query("UPDATE ".$prefix."speedlimit SET uploaded=uploaded+$xmitbytes");
	header("Content-Length: $xmitbytes");
	echo $xmit;
}

Unlock("WebSeed--$lockno");
exit;

?>
