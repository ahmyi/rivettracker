<?php

/// Use this file as an alternative to tracker.php/announce
/// for TorrentSpy and other /scrape support.

$_SERVER["PATH_INFO"] = "/announce";
require("tracker.php");
exit;

?>