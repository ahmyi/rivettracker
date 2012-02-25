<?php
//takes information from installer.php and creates config.php file
//allows user to save config.php

header('content-type: application/octet-stream');
header("Content-Disposition: attachment; filename=\"config.php\"");

print "<?php $config = " . var_export($config, true)  . ";"



?>
