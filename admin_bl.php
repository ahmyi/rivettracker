<?php
require_once('config.php');
include('header.php');
if($_SESSION['admin_logged_in'] == true)
{
  if(isset($_REQUEST["action"]))
  {
    $action = htmlspecialchars($_REQUEST["action"], ENT_QUOTES, 'UTF-8');
    if($action == "del")
    {
      $id = $_GET["id"];
      $sql->query("delete from `blacklist` where `id`='$id'");
      exit(header("Location: admin_bl.php"));
    }
    if($action == "add")
    {
      $agent = $_POST['useragent'];
      $sql->query("INSERT INTO `blacklist` (`useragent`) VALUES ('$agent')");
      exit(header("Location: admin_bl.php"));
    }
  }
  echo '<div class="container">
          <table class="table table-hover">
            <thead>
              <tr>
                <td>ID</td>
                <td>User Agent</td>
                <td>Remove</td>
              </tr>
            </thead>
            <tbody>';
  $data = $sql->query("select * from `blacklist`");
  if($data->num_rows >= 1)
  {
    while($row = $data->fetch_row())
    {
      echo "<tr>
              <td>".$row[0]."</td>
              <td>".$row[1]."</td>
              <td><a class='btn btn-danger' href='admin_bl.php?action=del&id=".$row[0]."'>Remove</a></td>
            </tr>";
    }
  }
  else
    echo "<tr><td>0</td><td>No client in blacklist</td><td>-</td></tr>
          </tbody>
        </table>
        <hr/>";
  echo '</tbody>
        </table>
        <hr/>
<form action="admin_bl.php" method="POST">
  <div class="form-group">
    <label>Add User-Agent to blacklist</label>
    <input type="text" class="form-control" placeholder="BitComet" name="useragent" />
  </div>
  <button type="submit" class="btn btn-primary" name="action" value="add">Add</button>
</from>
</div></body></html>';
}