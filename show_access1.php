<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Die 3 Meta-Tags oben *müssen* zuerst im head stehen; jeglicher sonstiger head-Inhalt muss *nach* diesen Tags kommen -->
    <meta name="description" content="Portal zum Geist des Görlitzer Untermartktes">
    <meta name="author" content="sdrum">
    <link rel="shortcut icon" type="image/x-icon" href="favicon.png">

    <title>Show Access - Geist vom Untermarkt</title>

    <!-- Bootstrap-CSS -->
    <link href="include/bootstrap-3.3.5-dist/css/bootstrap.min.css" rel="stylesheet">

   
    <!-- Unterstützung für Media Queries und HTML5-Elemente in IE8 über HTML5 shim und Respond.js -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  <style>
  body { padding-top: 70px; }
  
  </style>
  </head>

  <body>

    <!-- Statische Navbar -->
    <nav class="navbar navbar-default navbar-fixed-top navbar-inverse">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Access Viewer<img src="sDRUM_Logo_invers.png" style="width:80px; float:left;"/></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            
            <li class="active"><a href="show_access1.php?a=view&mode=solved">Gelöste</a></li>
            <li><a href="show_access1.php?a=view&mode=all">Alles</a></li>
            <li><a href="show_access1.php?a=drop_me&mode=">lösche meine Einträge</a></li>
            
          </ul>
          
        </div><!--/.nav-collapse -->
      </div>
    </nav>


    <div class="container">
   <div class="table-responsive"> 
<?php
define("DB_NAME",'useraccess_gc7b724.sqlite');
$ip_adress = $_SERVER['REMOTE_ADDR'];
$action = '';

// kurzform ohne zeit:
function ts_short($ts)
{
  $temp = bcdiv($ts, 86400, 0);
  return ($temp);
}

function get_table($mode)
{
  $date_ts_now = ts_short(time());
  //$q = "SELECT * FROM access_list WHERE step = 3 ORDER BY ts DESC LIMIT 50";
  $db = new SQLite3(DB_NAME);

  if(($mode == '')||($mode == 'solved')){
    $q = "SELECT * FROM access_list WHERE step == 3 ORDER BY ts DESC LIMIT 50";

  }else{
    $q = "SELECT * FROM access_list ORDER BY ts DESC LIMIT 50";

  }

$results = $db->query($q);

  $tbl = '<table class="table table-striped">';
  $tbl .= "<thead style=\"text-align:left;\">";
  $tbl .= "<tr>";
  $tbl .= "<th >ID</th>";
  $tbl .= "<th >IP</th>";
  //$tbl .= "<th width=\"200\">ts</th>";
  $tbl .= "<th >DATE</th>";
  $tbl .= "<th >Time</th>";
  $tbl .= "<th >Step</th>";
  $tbl .= "<th >Versuch</th>";

  $tbl .= "<th >Checknumber</th>";
  $tbl .= "<th >Codeword</th>";
  $tbl .= "<th >pw versuch</th>";
  $tbl .= "</tr>";
  $tbl .= "</thead>";
  $tbl .= "<tbody>";
  while ($row = $results->fetchArray()) {
     $css = '';
     $date_ts = ts_short($row['ts']);
     if($date_ts == $date_ts_now){$css = 'class="success"';}else
     if($date_ts == $date_ts_now-1){$css = 'class="info"';}
     //if($date_ts == $date_ts_now-2){$css = 'class="yellow"';}
     $tbl .= "<tr $css>";
     $tbl .= "<td>".$row['id']."</td>";
     $tbl .= "<td>".$row['ip_adress']."</td>";
     //$tbl .= "<td>".$row['ts']."</td>";
     $tbl .= "<td>".date("d.m.y",$row['ts'] )."</td>";
     $tbl .= "<td>".date("H:i",$row['ts'] )."</td>";
     $tbl .= "<td>".$row['step']."</td>";
     $tbl .= "<td>".$row['try']."</td>";
     $tbl .= "<td>".$row['cross']."</td>";
     $tbl .= "<td>".$row['codeword']."</td>";
     $tbl .= "<td>".$row['pw']."</td>";
     $tbl .= "</tr>";
  }
  $tbl .= "</tbody>";
  $tbl .= '</table>';
  return $tbl;
}

////////////////////// requests //////////////////////
if (isset($_REQUEST['a']))
{
  $action = $_REQUEST['a'];
  $mode = '';
  if(isset($_REQUEST['mode'])){$mode = $_REQUEST['mode'];}
}



if($action == 'drop_me')
{
  $db = new SQLite3(DB_NAME);
  $q = "DELETE FROM access_list WHERE ip_adress = '$ip_adress'";
  $results = $db->query($q);
  print get_table($mode);
}
if($action == 'view')
{
  print get_table($mode);
}

?>      
    </div> <!-- /responsive  table -->   
    </div> <!-- /container -->



    <!-- Bootstrap-JavaScript
    ================================================== -->
    <!-- Am Ende des Dokuments platziert, damit Seiten schneller laden -->
    <script src="include/js/jquery.min.js"></script>
    <script src="include/bootstrap-3.3.5-dist/js/bootstrap.min.js"></script>
    <!-- IE10-Anzeigefenster-Hack für Fehler auf Surface und Desktop-Windows-8 -->
    <script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>
