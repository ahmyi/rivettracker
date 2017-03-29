<?php
require_once("config.php");
require_once("funcsv2.php");
session_start();
?>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title><?=$GLOBALS['title'];?></title>

    <!-- Bootstrap -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <!--[if lt IE 9]>
      <script src="js/html5shiv.min.js"></script>
      <script src="js/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <nav class="navbar navbar-default">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#"><?=$GLOBALS['title'];?></a>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="nav navbar-nav">
            <li><a href="index.php">Home</a></li>
            <li><a href="rss/rss.xml">RSS</a></li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
<?php
if(@$_SESSION['admin_logged_in'] == true or @$_SESSION['upload_logged_in'] == true)
{
  echo '
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'.$_SESSION['username'].' <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="newtorrents.php">Upload</a></li>
                <li><a href="batch_upload.php">Upload Batch</a></li>';
 if(@$_SESSION['admin_logged_in'] == true)
   echo '
                <li role="separator" class="divider"></li>
                <li><a href="edit_database.php">Edit Torrent in database</a></li>
                <li><a href="DumpTorrentCGI.php">Show Information on Torrent</a></li>
                <li><a href="sanity.php">Check Tracker for Expired Peers</a></li>
                <li><a href="statistics.php">User Statistics</a></li>
                <li><a href="deleter.php">Delete Torrent</a></li>
                <li><a href="editconfig.php">Edit Config</a></li>
                <li><a href="uploadstats.php">Upload Statistics</a></li>
                <li><a href="css.php">Change CSS</a></li>
                <li><a href="admin_bl.php">Client Blacklist</a></li>';
  echo '        <li role="separator" class="divider"></li>
                <li><a href="authenticate.php?status=logout">Logout</a></li>
              </ul>
            </li>';
}
else
    echo '<li><a href="authenticate.php">Login</a></li>';
?>
          </ul>
        </div><!-- /.navbar-collapse -->
      </div><!-- /.container-fluid -->
    </nav>