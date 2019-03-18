<?php
  $url = parse_url(getenv("CLEARDB_DATABASE_URL"));
  $servername = $url["host"];
  $username = $url["user"];
  $password = $url["pass"];
  $dbname = substr($url["path"], 1);
?>
