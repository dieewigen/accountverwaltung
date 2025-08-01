<?php
include_once "../inc/sv.inc.php";
include "../inccon.php";
?>
<html>
<head>
<title>Lastreg</title>
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
<center>
<?php

include "det_userdata.inc.php";

 function getmicrotime(){
  list($usec, $sec) = explode(" ",microtime());
  return ((float)$usec + (float)$sec);
 }

 $time_start = getmicrotime();

    echo '<table border="0" cellpadding="2" cellspacing="0">';
    echo '<tr>';
    echo '<td>UserID</td>';
    echo '<td>Loginname</td>';
    echo '<td>Spielername</td>';
    echo '<td>Vorname/Nachname</td>';
    echo '<td>E-Mail</td>';
    echo '<td>Passwort</td>';
    echo '<td>Registriert</td>';
    echo '<td>Letzter Login</td>';
    echo '<td>letzte IP</td>';
    echo '<td>Status</td>';
    echo '<td>Logins</td>';
    echo '<td>Werber-ID</td>';
    echo '</tr>';

$sql = "SELECT * FROM ls_user ORDER BY `user_id` DESC LIMIT 500";
$result = mysqli_execute_query($GLOBALS['dbi'], $sql);
while($user = mysqli_fetch_array($result)){
  if ($user["acc_status"]==0) $status='Inaktiv';
  if ($user["acc_status"]==1) $status='Aktiv';
  if ($user["acc_status"]==2) $status='Gesperrt';
  if ($user["acc_status"]==3) $status='Urlaub';

    echo '<tr>';
    echo '<td><a href="idinfo.php?UID='.$user["user_id"].'" target="_blank">'.$user["user_id"].'</a></td>';
    echo '<td>'.$user["loginname"].'</td>';
    echo '<td>'.$user["spielername"].'</td>';
    echo '<td>'.$user["vorname"].' '.$user["nachname"].'</td>';
    echo '<td>'.$user["reg_mail"].'</td>';
    echo '<td'.$str.'>'.modpass($user["pass"]).'</td>';
    echo '<td>'.$user["register"].'</td>';
    echo '<td>'.$user["last_login"].'</td>';
    echo '<td>'.$user["last_ip"].'</td>';
    //$status.=' <a href="de_set_user_status.php?uid='.$user["user_id"].'&status=2" target="setuserstatus">[S]</a>';
    echo '<td>'.$status.'</td>';
    echo '<td>'.$user["logins"].'</td>';
    echo '<td>'.$user["werberid"].'</td>';
    echo '</tr>';

}
echo '</table><br><br>';

  $time_end = getmicrotime();
  $ltime = number_format($time_end - $time_start,2,".","");

function modpass($pass)
{
  $pass[0]="*";
  $pass[1]="*";
  $pass[2]="*";
  $pass[3]="*";
  return($pass);
}
?>
</center>
 <br>
 Seite in <? echo $ltime; ?> Sekunden erstellt.
</body>
</html>