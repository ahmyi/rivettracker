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

if($_POST['login'] == "authentification")
{
  $user = $sql->real_escape_string($_POST['f_user']);
  $pass = md5($sql->real_escape_string($_POST['f_pass']));
  $result=$sql->query("select * from `user` where (`user`='$user' and `pass`='$pass')");
  if($result->num_rows == 1)
  {
      session_start();
      $data = $result->fetch_row();
      if($data[4] == 1)
      {
        $_SESSION['admin_logged_in'] = true;
        exit(header("Location: admin.php"));
      }
      else
      {
        $_SESSION['upload_logged_in'] = true;
        exit(header("Location: index.php"));
      }
  }
  else
    exit(header("Location: authenticate.php?status=error"));
}
?>
