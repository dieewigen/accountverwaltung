<?PHP
 include "../inccon.php";

 function getmicrotime(){
  list($usec, $sec) = explode(" ",microtime());
  return ((float)$usec + (float)$sec);
 }

 $time_start = getmicrotime();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Suche</title>
<?php include "cssinclude.php";?>
<style >
 body { scrollbar-face-color: #000000;scrollbar-shadow-color: #000000;scrollbar-highlight-color: #333333; scrollbar-3dlight-color: #8CA0B4;scrollbar-darkshadow-color: #333333;scrollbar-track-color: #000000;
  scrollbar-arrow-color: #8CA0B4; padding: 0px; color: #3399FF; margin-left: 0px; margin-top: 0px; margin-right: 0px; margin-bottom: 0px;
  font-family: helvetica, arial,geneva, sans-serif;  font-size: 10pt;}
 table { border: 1px solid #00366C; }
 td { font-family: helvetica, arial, geneva, sans-serif; font-size: 10pt; white-space: nowrap; border: 1px solid #00366C; }
 td.r { color: #ff0000; }
 a { color: #3399ff; text-decoration: underline }
 a:hover { color: #3399ff; text-decoration: none }
</style>

</head>
<body>

<?
  echo '<table border="0" cellpadding="2" cellspacing="0">';
  echo '<tr>';
  echo '<td>UserID</td>';
  echo '<td>Suche</td>';
  echo '</tr>';

  $UCount = 0;
  if ($sstr!='')
  switch($sstr[0]){
    case '-': //nic
      $sstr = str_replace($sstr[0].$sstr[1],$sstr[1],$sstr);
      $result = mysqli_execute_query($GLOBALS['dbi'], "SELECT user_id, nic FROM ls_user WHERE nic LIKE ?", ["%".$sstr."%"]);

      while($UData = mysqli_fetch_array($result)) {
       echo '<tr><td><a href="idinfo.php?UID='.$UData["user_id"].'" target="_blank">'.$UData["user_id"].'</a></td>';
       echo '<td>'.$UData["nic"].'</td></tr>';
       $UCount++;
      }

      break;
    case '*': //spielername
      $sstr = str_replace($sstr[0].$sstr[1],$sstr[1],$sstr);
      $result = mysqli_execute_query($GLOBALS['dbi'], "SELECT user_id, spielername FROM ls_user WHERE spielername LIKE ?", ["%".$sstr."%"]);

      while($UData = mysqli_fetch_array($result)) {
       echo '<tr><td><a href="idinfo.php?UID='.$UData["user_id"].'" target="_blank">'.$UData["user_id"].'</a></td>';
       echo '<td>'.$UData["spielername"].'</td></tr>';
       $UCount++;
      }

      break;
    case '$': //email-adresse
      $sstr = str_replace($sstr[0].$sstr[1],$sstr[1],$sstr);
      $result = mysqli_execute_query($GLOBALS['dbi'], "SELECT user_id, reg_mail FROM ls_user WHERE reg_mail LIKE ?", ["%".$sstr."%"]);

      while($UData = mysqli_fetch_array($result)) {
       echo '<tr><td><a href="idinfo.php?UID='.$UData["user_id"].'" target="_blank">'.$UData["user_id"].'</a></td>';
       echo '<td>'.$UData["reg_mail"].'</td></tr>';
       $UCount++;
      }

      break;

    case '~': //IP
      $sstr = str_replace($sstr[0].$sstr[1],$sstr[1],$sstr);
      $result = mysqli_execute_query($GLOBALS['dbi'], "SELECT user_id, last_ip FROM ls_user WHERE last_ip LIKE ? ORDER BY last_ip", ["%".$sstr."%"]);
      while($UData = mysqli_fetch_array($result)) {
       echo '<tr><td><a href="idinfo.php?UID='.$UData["user_id"].'" target="_blank">'.$UData["user_id"].'</a></td>';
       echo '<td>'.$UData["last_ip"].'</td></tr>';
       $UCount++;
      }

      break;

    case '|': //Vor-/Nachname
      $sstr = str_replace($sstr[0].$sstr[1],$sstr[1],$sstr);
      $result = mysqli_execute_query($GLOBALS['dbi'], "SELECT user_id, vorname, nachname FROM ls_user WHERE vorname LIKE ? OR nachname LIKE ?", ["%".$sstr."%", "%".$sstr."%"]);
      while($UData = mysqli_fetch_array($result)) {
       echo '<tr><td><a href="idinfo.php?UID='.$UData["user_id"].'" target="_blank">'.$UData["user_id"].'</a></td>';
       echo '<td>'.$UData["vorname"].' '.$UData["nachname"].'</td></tr>';
       $UCount++;
      }
	break;
    case 'ö': //Ort
      $sstr = str_replace($sstr[0].$sstr[1],$sstr[1],$sstr);
      $result = mysqli_execute_query($GLOBALS['dbi'], "SELECT user_id, ort FROM ls_user WHERE ort LIKE ?", ["%".$sstr."%"]);
      while($UData = mysqli_fetch_array($result)) {
       echo '<tr><td><a href="idinfo.php?UID='.$UData["user_id"].'" target="_blank">'.$UData["user_id"].'</a></td>';
       echo '<td>'.$UData["ort"].'</td></tr>';
       $UCount++;
      }

    default:
      break;
  }//switch sstr ende

  echo '</table><br>'.$UCount.' User gefunden<br>';

  $time_end = getmicrotime();
  $ltime = number_format($time_end - $time_start,2,".","");
?>

 <br>
 Seite in <? echo $ltime; ?> Sekunden erstellt.

</body>
</html>