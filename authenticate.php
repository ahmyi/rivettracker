<?php
//Main Login Page

//Destroy any previous session data
//This way it requires a login
session_start();
session_destroy();

//get status
$status = $_GET['status'];

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Login</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link rel="stylesheet" href="./css/style.css" type="text/css" />
</head>
<body>
<center>
<h1>Login</h1>
<img src="images/lock.png" border="0" alt="Please Login" title="Please Login" />
<h3>Please login with your username and password.</h3>
<form action="login.php" method="POST">
<table border="0">
<tr><td class="right">
Username:</td>
<td class="left">
<input type="text" size="20" name="f_user">
</td></tr>
<tr><td class="right">
Password:</td>
<td class="left">
<input type="password" size="20" name="f_pass">
</td></tr>
<tr><td></td><td class="left">
<input type="submit" name="LogIn" value="Log In">
</td></tr>
</table>
<?php
//Display legal stuff if file exists
if (file_exists("legalterms.txt"))
	echo "<br><input type=\"checkbox\" name=\"legalterms\"> I agree to the <a href=\"legalterms.txt\">use policy and terms of service.</a>";
else //display hidden value, needed so that login.php can check the value
	echo "<input type=\"hidden\" name=\"legalterms\" value=\"on\">";

if ($status == "error")
echo "<p class=\"error\">Error, username or password is incorrect.<br>Entries are cAsESEnsITiVE, do you have your capslock key on?...</p>";
if ($status == "session")
echo "<p class=\"error\">Your session has timed out, please re-login.</p>";
if ($status == "logout")
echo "<p class=\"success\">You have successfully logged out.</p>";
if ($status == "indexlogin")
echo "<p class=\"error\">Error, this tracker requires a username and password in order to view the main page.</p>";
if ($status == "legalterms")
echo "<p class=\"error\">You need to agree to the use policy and terms of service in order to log in.</p>";
?>
</form>
<br>
<a href="index.php"><img src="images/stats.png" border="0" class="icon" alt="Tracker Statistics" title="Tracker Statistics" /></a><a href="index.php">Tracker Statistics</a><br>
</center>
</body>
</html>
