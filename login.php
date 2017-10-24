<?php
//Login Script
//Validates Username and Password
require_once ("config.php");

if($_POST['legalterms'] != "on")
	exit(header("Location: authenticate.php?status=legalterms"));	//did not agree to legal terms, go back

if($_POST['login'] == "authentification")
{
  $user = htmlspecialchars($sql->real_escape_string($_POST['f_user']), ENT_QUOTES, 'UTF-8');
  $pass = md5(htmlspecialchars($sql->real_escape_string($_POST['f_pass']), ENT_QUOTES, 'UTF-8'));
  $result=$sql->query("select * from `".$prefix."user` where (`user`='$user' and `pass`='$pass')");
  if($result->num_rows == 1)
  {
      session_start();
      $row = $result->fetch_object();
      if($row->access > 1)
      {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['username'] = $user;
        exit(header("Location: index.php"));
      }
      else
      {
        $_SESSION['upload_logged_in'] = true;
        $_SESSION['username'] = $user;
        exit(header("Location: index.php"));
      }
  }
  else
    exit(header("Location: authenticate.php?status=error"));
}
?>
