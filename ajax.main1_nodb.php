<?php
define("DB_NAME",'useraccess_gc7b724.sqlite');

$db = new SQLite3(DB_NAME);
$db->busyTimeout(5000);

$ip_adress = $_SERVER['REMOTE_ADDR'];// zufallszahl zum loggen
$cross = rand(1000,9999);

$ts = time(); // zum Zeitstempel aufdrücken und vergleichen

// WAL mode has better control over concurrency.
// Source: https://www.sqlite.org/wal.html
//$db->exec('PRAGMA journal_mode = wal;');
$action = '';

////////////////////// POST or GET requests //////////////////////
if (isset($_REQUEST['a']))
{
  $action = $_REQUEST['a'];
}


////////////////////// Functions //////////////////////
// wandelt eine CSV datei in ein Array, Erste Zeile ist Indexname
function csv_to_array($filename='', $delimiter=';')
{
    if(!file_exists($filename) || !is_readable($filename))
        return FALSE;

    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE)
    {
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
        {
            if(!$header)
                $header = $row;
            else
                $data[] = array_combine($header, $row);
        }
        fclose($handle);
    }
    return $data;
}

// berechnet die Quersumme einer Zahl
function crossfoot ( $digits )
{
  // Typcast falls Integer uebergeben
  $strDigits = ( string ) $digits;

  for( $intCrossfoot = $i = 0; $i < strlen ( $strDigits ); $i++ )
  {
    $intCrossfoot += $strDigits{$i};
  }

  return $intCrossfoot;
}

// baut Ajaxheader und gibt es aus
function send_ajax($arr)
{
  header('Content-Type: application/json; charset=UTF-8');
  print json_encode($arr);
}

// Hilfsfunktionen zur Abfrage
function sqlitequery($query)
{
  $arr_result = array();
  $db = new SQLite3(DB_NAME);
  $r = $db->query($query);
  while ($row = $r->fetchArray()) {
    $arr_result[] = $row;
  }
  $db->close();
  return $arr_result;
}
// Hilfsfunktion zum schreiben
function sqliteexec($query)
{
  $db = new SQLite3(DB_NAME);
  $r = $db->exec($query);
  $db->close();
  return $r;
}

// Codecheck 1 + 2
function compare_code($code,$which)
{
  global $ip_adress; // Identifikation des Users
  $ts = time(); // zum Zeitstempel aufdrücken und vergleichen
  
  $result = 0; // default: erster code falsch

  $code1 = '1931035';
  $code2 = 'Gobius';
  
  $forbidden = array(".", ",", " ");
  $code_sauber = str_replace($forbidden, "", $code);
  
  //print $code . " - " .$which . " - ".$code1;

  if($which == 1)
  {
    if( strtolower($code_sauber) == strtolower($code1))
    {
      $result = 1; // 1. code passt
    }else{
    //
    }
  }else
  if($which == 2)
  {    
    if(strtolower($code_sauber) == strtolower($code2))
    {
      $result = 3; // 2. code passt
    }else{
      $result = 2; // 2.code passt nicht
    }
  }
  // $q_write = "UPDATE access_list SET pw = '$code' ,try = 0, ts = $ts, step = $compare_result, cross = 0000 WHERE id = $id";
  
  return $result;
}

function init()
{
  global $ip_adress; // Identifikation des Users
  $ts = time(); // zum Zeitstempel aufdrücken und vergleichen
  // $max_time = $ts - 60; // 1 Minute
  $max_time = $ts - 600; // 10 Minuten Timeout

  // erstmal schauen, ob der Benutzer schon mal probiert hat, und gleich die id holen
  $q = "SELECT * FROM access_list WHERE ip_adress = '$ip_adress' AND ts > $max_time";
  $arr = sqlitequery($q);
  // positiv:
  // wenn ein datensatz kommt, war der user innerhalb der letzten Zeit mal am probieren
  if(count($arr) != 0)
  {
    $arr_row = $arr[0]; // erste Zeile reicht, es kann ja nur einen Datansatz geben
    $id = $arr_row['id'];
    $q_write = "UPDATE access_list SET pw = 'init' ,try = 0, step = 0 WHERE id = $id";
    sqliteexec($q_write);
    return true;
  }
  return false;
}

// die eigentliche Codeauswertung mit Blick auf die Fehlversuche:
// Rückgabe: 0=erster code falsch; 1=erster Code richtig; 2=2.code falsch; 3= 2.code richtig; 5= Zu viele falsche versuche
function check_code($code)
{
  global $ip_adress; // Identifikation des Users
  global $cross; // Zufallszahl, die der Logger eingeben muss
  $ts = time(); // zum Zeitstempel aufdrücken und vergleichen

  //$max_time = $ts - 60; // die letzte Minute
  $max_time = $ts - 600; // 10 Minuten Timeout

  $compare_result = 5; // default: Zu viele ungültige Versuche

  // erstmal schauen, ob der Benutzer schon mal probiert hat, und gleich die id holen
  $q = "SELECT * FROM access_list WHERE ip_adress = '$ip_adress' AND ts > $max_time";
  $arr = sqlitequery($q);
  // positiv:
  // wenn ein datensatz kommt, war der user innerhalb der letzten Zeit mal am probieren
  if(count($arr) != 0)
  {
    $arr_row = $arr[0]; // erste Zeile reicht, es kann ja nur einen Datansatz geben
    $id = $arr_row['id'];
    $try = $arr_row['try'];
    $step = $arr_row['step']; // welcher Versuch war schonmal richtig?
    // Bis zu 10 Versuche lassen wir zu
    if($try < 9)
    {
      $textnr = 1;
      if($step == 0){$textnr = 1;}
      if($step == 1){$textnr = 2;}
      if($step == 2){$textnr = 2;}

      //$cross = crossfoot($ts);
      $cross = rand(1000,9999);

      //if($step == 3){$textnr = 2;}
      $compare_result = compare_code($code,$textnr);
      if(($compare_result == 1)||($compare_result == 3))
      {
        $q_write = "UPDATE access_list SET pw = '$code' ,try = 0, ts = $ts, step = $compare_result, cross = $cross WHERE id = $id";
      }
      else
      {
        $try = $try+1;
        $q_write = "UPDATE access_list SET pw = '$code' ,try = $try, step = $compare_result, cross = $cross WHERE id = $id";
      }

      sqliteexec($q_write);
    }
  }
  else
  {
    // hier isses ohnehin der erste Versuch nach einer gewissen Zeit
    $id = 0;
    $try = 0;
    $step = 0;
    $compare_result = compare_code($code,1);
    // Egal ob richtig oder falsch, ersten Versuch registrieren
    if($compare_result == 1){$try = 0;}else{$try = 1;} // wenn falsch, dann Versuchszähler eins hoch
    $q_write = "INSERT INTO access_list (ip_adress,ts,pw,try,step) VALUES ('$ip_adress',$ts,'$code',$try,$compare_result)";
    sqliteexec($q_write);
  }
  return $compare_result;
}

function write_codeword($codeword)
{
  global $ip_adress; // Identifikation des Users
  $ts = time(); // zum Zeitstempel aufdrücken und vergleichen
  $max_time = $ts - 600; // 10 Minuten Timeout

  // anhand der Benutzer-ip die id holen
  $q = "SELECT * FROM access_list WHERE ip_adress = '$ip_adress' AND ts > $max_time";
  $arr = sqlitequery($q);
  if(count($arr) != 0)
  {
    $arr_row = $arr[0]; // erste Zeile reicht, es kann ja nur einen Datansatz geben
    $id = $arr_row['id'];
    if($id > 0)
    {
      $q_write = "UPDATE access_list SET codeword = '$codeword' WHERE id = $id";
      sqliteexec($q_write);
      return true;
    }
  }
  return false;
}


// Wörterliste laden und Array draus machen
function load_csv()
{
  $filename = 'woerterliste.csv';
  $arr_csv = csv_to_array($filename);
  $rand = rand(0,count($arr_csv)-1);

  $arr_result = $arr_csv[$rand];
  //write_codeword($arr_result['de-gr']);
  return $arr_result;
}

////////////////////// MAIN //////////////////////


if($action == 'check_code')
{
  //$code = mysql_real_escape_string($_REQUEST['code']);
  $code = trim($_REQUEST['code']);
  $which = trim($_REQUEST['wh']);



  $arr_result = array();
// GER
  $txt0_ger = 'Nein, der Code "'.$code.'" ist leider falsch.<br/>Versuchs nochmal von Vorn!<br/>Gib die Zahl <b>[X]</b> ins Eingabefeld ein.';

  $txt1_ger = '<div class="answer_correct"><b >Richtig! <img src="img/ghost_happy.jpeg" style="width:50px;"/>Du hast mich im "Flüsterbogen" , Untermarkt 22 gefunden!</b>';
  //$txt1 .= '<p><br/>Solltest Du zu Zweit sein, kannst Du mal versuchen, auf einer Seite in die Rille des Bogens zu flüstern, während auf der anderen Seite Jemand versucht, das geflüsterte Wort zu hören!</p>';
  $txt1_ger .= '<br/>Geh doch mal hinein und suche auf der linken Seite hinter der Wand mein Klingelschild! <br/>Klingel bei mir,<b> finde meinen Namen heraus und gib Ihn hier ein!</b><br/><div id="descript_remark">Sollte die Tür im Winter zu sein, drücke vorsichtig dagegen, und schließe sie wieder hinter Dir!<br/>Sollte leider komplett verschlossen sein, dann nutze bitte die Logbedingung per Mail an <a href="mailto:geistvomuntermarkt-1931035@s-drum.de">geistvomuntermarkt-1931035@s-drum.de</a> !</div>';
  $txt1_ger .= '</div>';
  $txt2_ger = 'Nein, "'.$code.'" heiße ich leider nicht!<br/>Gehe hinein, suche auf der linken Seite hinter der Tür hinter der Wand und <br/><b>finde meinen Namen heraus, indem Du mal bei mir klingelst!</b>!';

  $txt3_ger = '<b>Gratulation, so heiße ich!</b>';
  $txt3_ger .= '<p >Danke, dass Du mich, den Geist des Untermarkts besucht hast. Du darfst nun natürlich loggen, wenn Du die Logbedingung respektierst.</p>';
  $txt3_ger .= '<p >Nun zur Logbedingung: <br/>Bitte baue in Deinen Online-Log diesen typischen Görlitzer Ausdruck zusammen mit der 4-Stelligen Kontrollzahl ein: </p>';

// ENG
  $txt0_eng = 'No, Sorry. the code "'.$code.'" was wrong or you have been waiting too long.<br/>Try again!Enter the number <b>[X]</b> in the input field to get the next step';

  $txt1_eng = '<b>Correct, you have found me! I\'m living in the "Flüsterbogen"</b>';
  //$txt1 .= '<p><br/>Solltest Du zu Zweit sein, kannst Du mal versuchen, auf einer Seite in die Rille des Bogens zu flüstern, während auf der anderen Seite Jemand versucht, das geflüsterte Wort zu hören!</p>';
  $txt1_eng .= '<p>Enter the entrance portal and search my doorbell to <b>find out my name !</b><br/> Enter the Name in the Input-field<br/><br/>';
  $txt1_eng .= 'The doorbell you will find on the left side behind the wall</p>';

  $txt2_eng = 'No, my name isn\'t "'.$code.'", sorry!<br/>Enter the entrance portal, search on the left side behind the door and ring on my bell to <b>find out my name!</b><br/>';

  $txt3_eng = '<b>Yes, it\'s my Name, congratulations!</b>';
  $txt3_eng .= '<p >Thank you for visiting me, the ghost of the Untermarkt. Of course, you may log, when you respect my log-condition! I hope, you had a lot of fun with me.</p>';
  $txt3_eng .= '<p >Here comes the log-condition:<br/>Please insert this typical dialect word and the 4Digit check-number into your Online-Log:</p>';


  $coderesult = 0;

  if (preg_match("/^([a-zA-Z0-9öäüßÖÄÜß?!,.;:_() \n\r-]+)$/is", $code))
  {
    //$coderesult = check_code($code);
    $coderesult = compare_code($code,$which);
  }

  $snip = '';
  
  $arr_result['result'] = $coderesult;

  if($coderesult == 0){
    $arr_result['answer_ger'] = $txt0_ger;
    $arr_result['answer_eng'] = $txt0_eng;
    $arr_result['pic'] = 'umarkt_2.jpg';
  }else if($coderesult == 1){
    $arr_result['answer_ger'] = $txt1_ger;
    $arr_result['answer_eng'] = $txt1_eng;
    $arr_result['pic'] = 'fbogen.jpg';
  }else if($coderesult == 2){
    $arr_result['answer_ger'] = $txt2_ger;
    $arr_result['answer_eng'] = $txt2_eng;
    $arr_result['pic'] = 'fbogen.jpg';
  }else if($coderesult == 3){
    $arr_snipplet = load_csv();
    $arr_result['answer_ger'] = $txt3_ger;
    $arr_result['answer_eng'] = $txt3_eng;
    $arr_result['pic'] = '100629069_8.jpg';
    $arr_result['snipplet'] = $arr_snipplet;
    $arr_result['crossfoot'] = $cross;
    
    $snip = $arr_snipplet['de-gr'];
  }
  
  send_ajax( $arr_result );
  
  //log
    $q_write = "INSERT INTO access_list (ip_adress,ts,pw,try,step,cross, codeword) VALUES (
    '$ip_adress',
    $ts,
    '$code',
    0,'$coderesult', 
    '$cross', 
    '$snip')";
    
    sqliteexec($q_write);
}

if($action == 'init')
{
  //$txt0_ger = 'Willkommen, edler Fremder! <br/>In nur 2 Schritten bist Du am Ziel. <br/>Finde zuerst heraus, wo ich wohne!  Gib den Namen des Hauses gefolgt von der Zahl [X] ein.<br/>Beispiel: Geisterhaus12345';
  $txt0_ger = 'Willkommen auf dem Untermarkt, edler Fremder! <br/>In nur 2 Schritten bist Du am Ziel. <br/>Gib zur Kontrolle die 7-Stellige Zahl <b>[X]</b> ins Eingabefeld ein.';
  $txt0_eng = 'Welcome to the Untermarket, dear visitor!<br/>In only 2 steps you are at the goal.<br/>Enter the number <b>[X]</b> (7 digits) to get the next step!';

  //init();
  $arr_result = array();

  $arr_result['result'] = -1;
  $arr_result['answer_ger'] = $txt0_ger;
  $arr_result['answer_eng'] = $txt0_eng;
  send_ajax( $arr_result );
}



?>
