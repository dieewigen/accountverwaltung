<?php
include "../inccon.php";
include "../inc/sv.inc.php";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Gleiche IP</title>
<?php include "cssinclude.php";?>
</head>
<body>
<?php
include "det_userdata.inc.php";

    //kopf mit ip und anzahl
    echo '<table border="1" cellpadding="0" cellspacing="1" width="200">';
    echo '<tr>';
    echo '<td align="center">IP: '.$lip.'</td>';
    echo '</tr>';
    echo '</table>';

    echo '<table border="1" cellpadding="0" cellspacing="1">';
    echo '<tr>';
    echo '<td width="50">User ID</td>';
    echo '<td width="150">Loginname</td>';
    echo '<td width="150">Spielername</td>';
    echo '<td width="200">E-Mail</td>';
    echo '<td width="150">Passwort</td>';
    echo '<td width="140">Registriert</td>';
    echo '<td width="140">Letzter Login</td>';
    echo '<td width="70">Status</td>';
    echo '<td width="40">Logins</td>';
    echo '</tr>';


    $result = mysqli_execute_query($GLOBALS['dbi'], "SELECT * FROM ls_user WHERE last_ip = ? ORDER BY pass", [$lip]);
    while($user = mysqli_fetch_array($result))
    {
      echo '<tr>';
      echo '<td><a href="idinfo.php?UID='.$user["user_id"].'" target="_blank">'.$user["user_id"].'</a></td>';
      echo '<td>'.$user["loginname"].'</td>';
      echo '<td>'.$user["spielername"].'</td>';
      echo '<td>'.$user["reg_mail"].'</td>';
      echo '<td>'.$user["pass"].'</td>';
      echo '<td>'.$user["register"].'</td>';
      echo '<td>'.$user["last_login"].'</td>';
      if ($user["acc_status"]==0) $status='Inaktiv';
      if ($user["acc_status"]==1) $status='Aktiv';
      if ($user["acc_status"]==2) $status='Gesperrt';
      if ($user["acc_status"]==3) $status='Urlaub';
      echo '<td>'.$status.'</td>';
      echo '<td>'.$user["logins"].'</td>';
      echo '</tr>';
      $gesuser++;
    }
    echo '</table><br><br> ' . $gesuser .' Spieler mit der selben IP gefunden';


?>


</body>
</html>
