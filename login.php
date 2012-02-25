<?php
//Login Script
//Validates Username and Password
require_once ("config.php");

if ($_POST['legalterms'] != "on")
{
	//did not agree to legal terms, go back
	header("Location: authenticate.php?status=legalterms");
	exit();
}

if (md5($_POST['f_user'].$_POST['f_pass']) == $admin_password && $_POST['f_user'] == $admin_username)
{
	//successful admin login
	session_start();
	$_SESSION['admin_logged_in'] = true;
	header("Location: admin.php");
	exit();
}

if (md5($_POST['f_user'].$_POST['f_pass']) == $upload_password && $_POST['f_user'] == $upload_username)
{
	//successful upload login
	session_start();
	$_SESSION['upload_logged_in'] = true;
	header("Location: index.php");
	exit();
}

//Username or password was incorrect at this point!
header("Location: authenticate.php?status=error");
exit();

?>
