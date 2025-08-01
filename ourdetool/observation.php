<?php
include_once "../inc/sv.inc.php";
include "../inccon.php";
?>
<html>
<head>
<title>Beobachtungsliste</title>
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

<?php
include "det_userdata.inc.php";

function getmicrotime(){
list($usec, $sec) = explode(" ",microtime());
return ((float)$usec + (float)$sec);
}

$time_start = getmicrotime();

// Oberservation_tag entfernen
if (isset($_GET['uid'])) 
{
  $uid = intval($_GET['uid']);
  $sql = "UPDATE ls_user SET observation_by = '' WHERE user_id = ?";
  @mysqli_execute_query($GLOBALS['dbi'], $sql, [$uid]);
}

// table start
echo '
  <form action="observation.php?" method="post">
  <table border="1" cellspacing="1" cellpadding="1">
    <tr>
      <td>Account-ID</td>
      <td>Loginname</td>
      <td>letzte IP</td>
	  <td>letzter Login</td>
      <td>Status</td>
      <td>Beobachter</td>
    </tr>
';

//abfrage ob es f�lle zur beobachtung gibt
$sql = "SELECT * FROM ls_user WHERE observation_by <>'' ORDER BY observation_by, user_id";
$db_daten = mysqli_execute_query($GLOBALS['dbi'], $sql);
while ($row = mysqli_fetch_array($db_daten)) {

  switch ($row['acc_status']) {
    case 0:
      $status = "vor Aktivierung";
    break;
    case 1:
      $status = "Aktiv";
    break;
    case 2:
      $status = "gesperrt";
    break;
    default:
      $status = "Aktiv";
    break;
  }
  echo '
    <tr>
      <td align="center"><a href="idinfo.php?UID='.$row['user_id'].'" target="_blank">'.$row['user_id'].'</a></td>
      <td align="center"><a href="idinfo.php?UID='.$row['user_id'].'" target="_blank">'.$row['loginname'].'</a></td>
      <td align="right">'.$row['last_ip'].'</td>
	  <td align="center">'.$row['last_login'].'</td>
      <td align="center">'.$status.'</td>
      <td align="center">'.$row['observation_by'].'</td>
      <td align="center"><a href="observation.php?uid='.$row['user_id'].'">entfernen</a></td>
    </tr>
  ';
}

// table close
echo '
  </table>
  </form>
';


//$exist_observation = mysqli_execute_query($GLOBALS['dbi'], "SELECT...");

  $time_end = getmicrotime();
  $ltime = number_format($time_end - $time_start,2,".","");

?>

 <br>
 Seite in <? echo $ltime; ?> Sekunden erstellt.
</body>
</html>
