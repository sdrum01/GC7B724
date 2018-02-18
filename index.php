<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<title>Der Geist vom Untermarkt</title>
<meta name="description" content="Portal zum Geist vom Untermartkt">
<meta name="author" content="sDRUM">
<link rel="stylesheet" media="(max-width: 640px)" href="html5_stylesheet_mobile.css">
<link rel="stylesheet" media="(min-width: 640px)" href="html5_stylesheet.css">
<link rel="stylesheet" media="print" href="print.css">
<link rel="shortcut icon" type="image/x-icon" href="favicon.png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>

p {
  font-size: 1em;
}

#b_submit{
  border: 2px solid #AFAFAF;
  border-radius:20px;
  margin:5px;
  font-size: 2em;
  background:white;
  cursor:pointer;
}

#b_submit:hover{
  background:#CCC8FF;
}

input[type="text"]{
  font-size: 2em;
  min-height:20px;
  width:80%;
  max-width:400px;
  text-align:center;
  border: 2px solid grey;
  border-radius:20px;
}

.container_input
{
  text-align:center;
  margin:10px;
  text-align:center;
}

#container_inputarea{
  ext-align:center;
}

.center
{
  text-align:center;
  vertical-align:middle;
}

#tbl_gr{
  display:none;
  border-top:1px solid gray;
  border-bottom:1px solid gray;
}

#tbl_gr td{
  vertical-align:top;
}

#ghost{
  width: auto; max-width:50%;
}


input:invalid{
 color:red;
}

input:valid{
 color:green;
}

input[required]{
 background-color:#F08080; /*Burlywood*/
}

#waitcontainer{
  position:absolute;
  margin-top:12px;
  margin-left:-30px;

}

#wait{
  display:none;
}

.lang{
    cursor:pointer;
    border:2px solid blue;
}
.lang:hover{
    border:2px solid red;
}
#container_lang{
    display:inline;
}

</style>
<script type="text/javascript" src="include/js/jquery.min.js" charset="utf-8"></script>
<script language="JavaScript" charset="utf-8">

  var text_ger = '';
  var text_eng = '';
  var current_lang = 'ger';

function ts()
{
  var now = new Date();
  var now_time = now.getTime();
  return(now_time);
}

function show_text(lang)
{
  $('.lang').css('border','');
  $('#lang_'+lang).css('border','2px solid yellow');
  if((lang == 'ger')||(lang == undefined)){
    $('#l_message').html(text_ger);

  }else if(lang == 'eng'){
    $('#l_message').html(text_eng);
  }
}

function ajax_send_codeword(init)
{

  var mycode = $('#e_passwd').val();
  var url = "ajax.main1.php";
  var action = 'check_code';
  if(init == true){action = 'init'; mycode='dummy';}
  if(mycode != '')
  {
    $('#wait').show();
    $.ajax({
      dataType: "json",
      url: url,
      method: "POST",
      data: {
        code: mycode,
        a: action
      },
      // bei Fehlern
      error: function(jqXHR, textStatus){
        if(textStatus == 'timeout'){
          text_ger = 'Sorry, der Server scheint gerade etwas langsam zu sein oder die Internetverbindung wurde unterbrochen.';
          text_eng = 'Sorry, the Server seems very slow or the internet connection is offline.';
          show_text(current_lang);
        }
        $('#wait').hide();
      },
      // wenns geklappt hat
      success: function(response)
      {
        $('#wait').hide();
        if(response != undefined)
        {
          var result = response.result;
          var answer_ger = response.answer_ger;
          var answer_eng = response.answer_eng;
          var pic = '<img src="img/'+response.pic+'" alt="'+response.pic+'" />';
          var ghost = '<img src="ghost.png" id="ghost"/>';
          text_ger = answer_ger;
          text_eng = answer_eng;
        }


        if(result == 1)
        {

          //$('#l_message').html(answer);
          show_text(current_lang);

          $('#myfigure').fadeOut(500,function(){
            $('#myfigure').html(pic).fadeIn(1000);
            $('#e_passwd').val('').focus();
          })
        }else if (result == 3)
        {
          $('.container_input').hide();

          if(response.snipplet != undefined)
          {
            var gr = response.snipplet['de-gr'];
            var de = response.snipplet['de-de'];
            var descript = response.snipplet['descript'];
            var cross = response.crossfoot;
          }

          //$('#l_message').html('');
          show_text(current_lang);
          $('#content_gr').html(gr);
          $('#content_de').html(de);
          if(descript != ''){
            $('#content_descript').html(descript);
            $('#content_descript_l').html('Beispiel/Example:');
          }
          if(descript != ''){
            $('#content_cross').html(cross);
            $('#content_cross_l').html('Kontrollzahl / checknumber:');
          }
          $('#tbl_gr').fadeIn(1000);
          //$('#l_message').html(answer);
          //$('#myfigure').html(ghost);
          $('#myfigure').html(pic);
          $('#l_figure').html('Vielen Dank an das Hotel "Börse" für die Erlaubnis und an Stefan Sander für die schönen Fotos');
        }
        else if ((result == 0)||(result == 2))
        {
          $('#myfigure').html(pic);
          $('#e_passwd').val('').focus();
          //$('#l_message').html(answer);
          show_text(current_lang);
        }else if (result == 5)
        {
          //$('#l_message').html('In der letzten Zeit hattest Du zu viele Fehleingaben. Bitte einen Moment warten...');
          text_ger = 'In der letzten Zeit hattest Du zu viele Fehleingaben. Bitte ca.10 Minuten warten...';
          text_eng = 'In the past there was too many incorrect entries. Please wait 10 minutes...';
          show_text(current_lang);

        }
        else  // Init-Antwort
        {
          //$('#l_message').html(answer);

          show_text(current_lang);


          $('#e_passwd').val('').focus();
        }
      },
      timeout: 20000 // sets timeout to x seconds
    });
  }else{
    if(init == undefined){
      //$('#l_message').html('Es wäre zu gütig, wenn Du ein Codewort eingeben würdest.');
      text_ger = 'Es wäre zu gütig, wenn Du ein Codewort eingeben würdest.';
      text_eng = 'Please enter a valid codeword!';
      show_text(current_lang);
      $('#e_passwd').val('').focus();
      $('#wait').hide();
    }
  }
}



$(document).ready(function(){


  $('#b_submit').click(function(){
    ajax_send_codeword();
  });

  $('.lang').click(function(){
    current_lang = $(this).attr('language');
    show_text(current_lang);
    $('#e_passwd').focus();
  });

  $('#e_passwd').focus();

  $('#e_passwd').keyup(function(e) // Enter-Taste abfangen beim Eingabeelement
  {
    var keycode = e.keyCode;
    if(keycode == 13) {
      ajax_send_codeword();
    }
  });

  // init
  ajax_send_codeword(true);
});

</script>
</head>

<body>
<header>
  <div id="container_lang"><img class="lang" language="ger" src="ger_30x50.gif" id="lang_ger"/><img class="lang" language="eng" src="eng_30x45.gif" id="lang_eng"/></div>
  Der Geist vom Untermarkt<img src="sDRUM_Logo_invers.png"  alt="sDRUM" id="logo"/>
</header>

<div id="hilfscontainer">
<main>

  <article>
    <div id="container_inputarea">
      <div id="l_message" class="text center"></div>
      <div class="container_input">
        <!--<input  type="number" id="e_passwd" pattern="[0-9]{5}" />-->
        <input type="text" id="e_passwd" />
        <span id="waitcontainer"><img id="wait" src="wait1.gif" /></span>
        <button id="b_submit" >Check</button>
      </div>
    </div>
  <div id="container_gr">

    <table id="tbl_gr">
      <tr>
        <td id="content_gr_l">Görlitzerisch:</td>
        <td id="content_gr"></td>
      </tr>
      <tr>
        <td id="content_de_l">Deutsch:</td>
        <td id="content_de"></td>
      </tr>
      <tr>
        <td id="content_cross_l"></td>
        <td id="content_cross"></td>
      </tr>

    </table>
    <div id="content_descript_l"></div>
    <div id="content_descript"></div>

  </div>
  <div id="umarkt" class="center">
  <figure id="myfigure"><img src="img/umarkt_1.jpg"  alt="Görlitzer Untermarkt" />
  <figcaption class="center"> </figcaption>
  </figure>
  <div id="l_figure"></div>
  </div>



  <div id="result"></div>
  </article>
</main>

</div>

</body>
</html>
