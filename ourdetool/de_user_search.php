<?php
include_once "../inc/sv.inc.php";
include "../inccon.php";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Suche</title>
<?php include "cssinclude.php";?>
</head>
<body>
<form action="de_user_search.php" method="get">
(+ ID, * Spielername, % Mail) (?[-*%~|] wildcard)
&nbsp;&nbsp;
<input type="text" name="sstr" value="">
<input type="Submit" name="search" value="Suchen">

<?php
//schauen wonach gesucht wird
//+ user_id
//- nic
//* spielername
//% Mail
//~ IP

//| Vor-/Nachname
//� Ort

$sstr = isset($_REQUEST['sstr']) ? $_REQUEST['sstr'] : '';
  if ($sstr!='')
  switch($sstr[0]){
    case '+': //user_id
      $sstr = str_replace($sstr[0].$sstr[1],$sstr[1],$sstr);
      $sql = "SELECT user_id FROM ls_user WHERE user_id=?";
      $db_daten = mysqli_execute_query($GLOBALS['dbi'], $sql, [$sstr]);
      $row = mysqli_fetch_array($db_daten);
      $sstr=$row["user_id"];
      break;
    case '*': //spielername
      $sstr = str_replace($sstr[0].$sstr[1],$sstr[1],$sstr);
      $sql = "SELECT user_id FROM ls_user WHERE spielername=?";
      $db_daten = mysqli_execute_query($GLOBALS['dbi'], $sql, [$sstr]);
      $row = mysqli_fetch_array($db_daten);
      $sstr=$row["user_id"];
      break;
    case '%': //email-adresse
      $sstr = str_replace($sstr[0].$sstr[1],$sstr[1],$sstr);
      $sql = "SELECT user_id FROM ls_user WHERE reg_mail=?";
      $db_daten = mysqli_execute_query($GLOBALS['dbi'], $sql, [$sstr]);
      $row = mysqli_fetch_array($db_daten);
      $sstr=$row["user_id"];
      break;
    case '?': //wildcard suche
      $sstr = str_replace($sstr[0].$sstr[1],$sstr[1],$sstr);
      $sstr = str_replace("%","$",$sstr);
      echo '<script type="text/javascript">'."\r\n".'<!--'."\r\n".'parent.frames["de_user_anzeige"].location.href = "wildcard.php?sstr='.$sstr.'";'."\r\n".'//-->'."\r\n".'</script>'."\r\n";
      $sstr = '';
      break;      
    default: //user_id
      $sstr = str_replace($sstr[0].$sstr[1],$sstr[1],$sstr);
      $sql = "SELECT user_id FROM ls_user WHERE user_id=?";
      $db_daten = mysqli_execute_query($GLOBALS['dbi'], $sql, [$sstr]);
      $row = mysqli_fetch_array($db_daten);
      $sstr=$row["user_id"];
      break;
  }//switch sstr ende
  if ($sstr=='')die ('Kein User gefunden.');
  else
{
$sstr=trim($sstr);
echo '&nbsp;&nbsp;User ID: '.$sstr;
echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;<a href="info.php?uid='.$sstr.'" target="de_user_anzeige">Info</a>';
echo '&nbsp;&nbsp;&nbsp;&nbsp;<a href="de_user_logviewer.php?uid='.$sstr.'" target="de_user_anzeige">Logviewer 2</a>';
echo '&nbsp;&nbsp;&nbsp;&nbsp;<a href="de_user_ips.php?uid='.$sstr.'" target="de_user_anzeige">IPs</a>';
echo '&nbsp;&nbsp;&nbsp;&nbsp;<a href="de_user_credits.php?uid='.$sstr.'" target="de_user_anzeige">Credits</a>';
echo '</form>';

echo '<script type="text/javascript">'."\r\n".'<!--'."\r\n".'parent.frames["de_user_anzeige"].location.href = "info.php?uid='.$sstr.'";'."\r\n".'//-->'."\r\n".'</script>'."\r\n";
}
?>

</body>
</html>
