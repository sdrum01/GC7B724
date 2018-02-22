<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>Show Access - Geist vom Untermarkt</title>
<meta name="description" content="Portal zum Geist des Görlitzer Untermartktes">
<meta name="author" content="sdrum">
<link rel="shortcut icon" type="image/x-icon" href="favicon.png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style >
.linkbutton{
  margin:5px;
  background:#efefef;
  border-radius:5px;
  text-decoration:none;
  width:200px;
  height:40px;
  vertical-align:middle;
}
.linkbutton:hover{
  background:yellow;
}
  
</style>
</head>
<body>

<?php
define("DB_NAME",'useraccess_gc7b724.sqlite');
$ip_adress = $_SERVER['REMOTE_ADDR'];

/*
$db-> exec("CREATE TABLE IF NOT EXISTS tableSpieler(
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 dokoname TEXT NOT NULL DEFAULT '0',
 dokoinfo TEXT NOT NULL DEFAULT '0',
 dokosince INTEGER NOT NULL DEFAULT '0')");
*/

//$date_now = date("ymdhi",time());

//$ip_adress = 'test2';
$action = '';

function get_table($mode)
{
  //$q = "SELECT * FROM access_list WHERE step = 3 ORDER BY ts DESC LIMIT 50";
  $db = new SQLite3(DB_NAME);
  
  if(($mode == '')||($mode == 'solved')){
    $q = "SELECT * FROM access_list WHERE step == 3 ORDER BY ts DESC LIMIT 50";

  }else{
    $q = "SELECT * FROM access_list ORDER BY ts DESC LIMIT 50";

  }

$results = $db->query($q);

  $tbl = "<table width=\"100%\">";
  $tbl .= "<thead style=\"text-align:left;\">";
  $tbl .= "<tr>";
  $tbl .= "<th width=\"100\">ID</th>";
  $tbl .= "<th width=\"200\">IP</th>";
  //$tbl .= "<th width=\"200\">ts</th>";
  $tbl .= "<th width=\"200\">DATE/Time</th>";
  $tbl .= "<th width=\"200\">Step</th>";
  $tbl .= "<th width=\"200\">Versuch</th>";

  $tbl .= "<th >Checknumber</th>";
  $tbl .= "<th >Codeword</th>";
  //$tbl .= "<th >last_pw</th>";
  $tbl .= "</tr>";
  $tbl .= "</thead>";
  $tbl .= "<tbody>";
  while ($row = $results->fetchArray()) {
     $tbl .= "<tr>";
     $tbl .= "<td>".$row['id']."</td>";
     $tbl .= "<td>".$row['ip_adress']."</td>";
     //$tbl .= "<td>".$row['ts']."</td>";
     $tbl .= "<td>".date("y-m-d  H:i",$row['ts'] )."</td>";
     $tbl .= "<td>".$row['step']."</td>";
     $tbl .= "<td>".$row['try']."</td>";
     $tbl .= "<td>".$row['cross']."</td>";
     $tbl .= "<td>".$row['codeword']."</td>";
     //$tbl .= "<td>".$row['pw']."</td>";
     $tbl .= "</tr>";
  }
  $tbl .= "</tbody>";
  $tbl .= "</table>";
  return $tbl;
}

////////////////////// POST or GET requests //////////////////////
if (isset($_REQUEST['a']))
{
  $action = $_REQUEST['a'];
  $mode = $_REQUEST['mode'];
}


?>

<?php

if($action == 'drop_me')
{
  $db = new SQLite3(DB_NAME);
  $q = "DELETE FROM access_list WHERE ip_adress = '$ip_adress'";
  $results = $db->query($q);
  print "<h2>Die letzten 50 Besucher:</h2>";
  print get_table($mode);
  print "<a href=\"show_access.php?a=view&mode=solved\" ><div class=\"linkbutton\">Zeige nur gelöste</div></a>";
  print "<a href=\"show_access.php?a=view&mode=all\" ><div class=\"linkbutton\">Zeige Alles</div></a>";
  print "<a href=\"show_access.php?a=drop_me&mode=solved\" ><div class=\"linkbutton\">Lösche Eintrag mit meiner IP $ip_adress</div></a>";
}
if($action == 'view')
{
  print "<h2>Die letzten 50 Besucher:</h2>";
  print get_table($mode);
  print "<a href=\"show_access.php?a=view&mode=solved\" ><div class=\"linkbutton\">Zeige nur gelöste</div></a>";
  print "<a href=\"show_access.php?a=view&mode=all\" ><div class=\"linkbutton\">Zeige Alles</div></a>";
  print "<a href=\"show_access.php?a=drop_mew&mode=solved\" ><div class=\"linkbutton\">Lösche Eintrag mit meiner IP $ip_adress</div></a>";
}

?>
</body>
</html>
