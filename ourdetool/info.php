<?php
include "../inccon.php";
include "../inc/sv.inc.php";
include "det_userdata.inc.php";
include "../inc/serverdata.inc.php";
include "../functions.php";

$uid=$_REQUEST['uid'];

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Info</title>
<?php include "cssinclude.php";?>
<script language="JavaScript" type="text/javascript">

function AddText(AddTxT) {
 document.getElementById("kommentartext").value = document.getElementById("kommentartext").value + AddTxT ;
 document.getElementById("kommentartext").focus();
}

function AddMsg(NewMsg) {
 var sNow = new Date();
 var sDay = ((sNow.getDate() < 10) ? "0" + sNow.getDate() : sNow.getDate());
 var sMont = sNow.getMonth() + 1;
 var sMonth = ((sMont < 10) ? "0" + sMont : sMont);

 var sHours = ((sNow.getHours() < 10) ? "0" + sNow.getHours() : sNow.getHours());
 var sMinutes = ((sNow.getMinutes() < 10) ? "0" + sNow.getMinutes() : sNow.getMinutes());
 var sSeconds = ((sNow.getSeconds() < 10) ? "0" + sNow.getSeconds() : sNow.getSeconds());

 var sDateTime = sNow.getFullYear() + "-" + sMonth + "-" + sDay + " " + sHours + ":" + sMinutes + ":" + sSeconds;

 with ( document.getElementById("kommentartext").value ) {
  switch(NewMsg) {
   case "Multi":
    AddText(sDateTime + " <? echo $det_username; ?> - gesperrt wegen Multi\r\n");
    break;
   case "Farming":
    AddText(sDateTime + " <? echo $det_username; ?> - gesperrt wegen Farming\r\n");
    break;
   case "PWS":
    AddText(sDateTime + " <? echo $det_username; ?> - gesperrt wegen PW-Sharing\r\n");
    break;
   case "FUD":
    AddText(sDateTime + " <? echo $det_username; ?> - gesperrt wegen falschen Userdaten\r\n");
    break;
   case "SBeleid":
    AddText(sDateTime + " <? echo $det_username; ?> - gesperrt wegen extremer Beleidigung\r\n");
    break;
   case "ZUBeleid":
    AddText(sDateTime + " <? echo $det_username; ?> - Zwangsurlaub wegen Beleidigung\r\n");
    break;
   case "AccWG":
    AddText(sDateTime + " <? echo $det_username; ?> - gesperrt wegen (Verdacht auf) Accountweitergabe\r\n");
    break;
   case "UserHSG":
    AddText(sDateTime + " <? echo $det_username; ?> - User hat sich gemeldet\r\n");
    break;
   case "REAKT":
    AddText(sDateTime + " <? echo $det_username; ?> - Account (nach Absprache) wieder aktiviert\r\n");
    break;
   case "ZID":
    AddText("zugeh�rige ID(s): ");
    break;
   case "MailRD":
    AddText(sDateTime + " <? echo $det_username; ?> - Mail wegen Userdaten wurde gesendet\r\n");
    break;
  	}
}
 document.getElementById("kommentartext").focus();
}
</script>
</head>
<body>
<form action="info.php?uid=<?=$uid?>" method="post">
<?php

//beobachter setzen
if($observationgo) 
{
	mysqli_execute_query($GLOBALS['dbi'], "UPDATE ls_user SET observation_by = ? WHERE user_id = ?", [$det_username, $uid]);
}

if ($stataktiv)
{
  mysqli_execute_query($GLOBALS['dbi'], "UPDATE ls_user SET acc_status = 1 WHERE user_id = ?", [$uid]);
  $savedata=1;
}
if ($statgesperrt)
{
  mysqli_execute_query($GLOBALS['dbi'], "UPDATE ls_user SET acc_status = 2, supporter = ? WHERE user_id = ?", [$det_email, $uid]);
  $savedata=1;
}
if ($kommentar) $savedata=1;  // For saving the Commenttext

if ($savedata==1 OR $speichern)
{
   mysqli_execute_query($GLOBALS['dbi'], "UPDATE ls_user SET kommentar = ?, loginname = ?, reg_mail = ?, spielername = ?, vorname = ?, nachname = ?, strasse = ?, plz = ?, ort = ?, land = ?, telefon = ? WHERE user_id = ?", [$kommentartext, $loginname, $email, $spielername, $vorname, $nachname, $strasse, $plz, $ort, $land, $telefon, $uid]);
}

if ($uid>0)
{
  //ls_user
  $result = mysqli_execute_query($GLOBALS['dbi'], "SELECT * FROM ls_user WHERE user_id = ?", [$uid]);
  $row = mysqli_fetch_array($result);

  echo '<table border="0" cellpadding="10" cellspacing="0">';
  echo '<tr><td align="center">';

  echo '<table border="0" cellpadding="5" cellpadding="0">';
  echo '<tr><td>';

    echo '<table border="1" cellpadding="0" cellspacing="1">';
      echo '<tr>';
      echo '<td width="100" align="center">Kooperation</td>';
      $coop='keine';
      if($row[cooperation]==1)$coop='<font color="#0000FF"><b>BIGPOINT-DE</b></font>';
      if($row[cooperation]==2)$coop='<font color="#0000FF"><b>KWICK-DE</b></font>';
      echo '<td width="200" align="center">'.$row[cooperation].' - '.$coop.'</td>';
      echo '</tr>';
    
      echo '<tr>';
      echo '<td width="100" align="center">Account ID</td>';
      echo '<td align="center">'.$uid.' (<a href="info.php?observationgo=1&uid='.$uid.'">beobachten</a>)</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">Beobachter</td>';
      echo '<td width="200" align="center">'.$row['observation_by'].'</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">Loginname</td>';
      echo '<td width="200" align="center"><input type="text" name="loginname" value="'.$row["loginname"].'"></td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">Spielername</td>';
      echo '<td width="200" align="center"><input type="text" name="spielername" value="'.$row["spielername"].'"></td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">E-Mail</td>';
      echo '<td width="200" align="center"><input type="text" name="email" value="'.$row["reg_mail"].'"><br><A HREF="mailto:'.$row["reg_mail"].'">'.$row["reg_mail"].'</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">Letzte IP</td>';
      echo '<td width="200" align="center"><a href="sameip.php?lip='.$row["last_ip"].'" target="_blank">'.$row["last_ip"].'</a>
            <a href="http://ripe.net/perl/whois?form_type=simple&full_query_string=&searchtext='.$row["last_ip"].'" target="_blank">[Info]</a></td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">Registriert</td>';
      echo '<td width="200" align="center">'.$row["register"].'</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">Zuletzt online</td>';
      echo '<td width="200" align="center">'.$row["last_login"].'</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">Logins</td>';
      echo '<td width="200" align="center">'.$row["logins"].'</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">Passwort</td>';
      echo '<td width="200" align="center">'.modpass($row["pass"]).'</td>';
      echo '</tr>';
    echo '</table></td>';

    echo '<td><table border="1" cellpadding="0" cellspacing="1">';
    echo '<tr>';
      echo '<td width="100" align="center">Vorname</td>';
      echo '<td width="200" align="center"><input type="text" name="vorname" value="'.$row["vorname"].'"></td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">Nachname</td>';
      echo '<td width="200" align="center"><input type="text" name="nachname" value="'.$row["nachname"].'"></td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">Strasse</td>';
      echo '<td width="200" align="center"><input type="text" name="strasse" value="'.$row["strasse"].'"></td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">PLZ</td>';
      echo '<td width="200" align="center"><input type="text" name="plz" value="'.$row["plz"].'"></td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">Ort</td>';
      echo '<td width="200" align="center"><input type="text" name="ort" value="'.$row["ort"].'"></td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">Land</td>';
      echo '<td width="200" align="center"><input type="text" name="land" value="'.$row["land"].'"></td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">Telefon</td>';
      echo '<td width="200" align="center"><input type="text" name="telefon" value="'.$row["telefon"].'"></td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">Geburtsdatum</td>';
      echo '<td width="200" align="center">'.$row["tag"].'-'.$row["monat"].'-'.$row["jahr"].'</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">Geschlecht</td>';
      if ($row["geschlecht"]==1)$geschlecht='m&auml;nnlich';else $geschlecht='weiblich';
      echo '<td width="200" align="center">'.$geschlecht.'</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td width="100" align="center">Status</td>';
      if ($row["acc_status"]==0)$status='Inaktiv';
      elseif ($row["acc_status"]==1)$status='Aktiv';
      elseif ($row["acc_status"]==2)$status='Gesperrt';
      elseif ($row["acc_status"]==3)$status='Urlaub';
      echo '<td width="200" align="center">'.$row["acc_status"].' = '.$status.'</td>';
      
      echo '<tr><td align="center">Forum (ID / Nick)</td>';
      echo '<td align="center">'.$row['forum_user_id'].' / '.$row['forum_nick'].'</td></tr>';
      echo '</tr>';

  echo '</table>';
  echo '</td></tr>';
  echo '</table>';

  echo '<br>Spielerstatus ver&auml;ndern:<br>';
  echo '<input type="Submit" name="stataktiv" value="Aktiv" style="width:130px;"> ';
  echo '<input type="Submit" name="statgesperrt" value="Gesperrt" style="width:130px;"> ';
  echo '<br><br>Kommentar:<br>';
  $kommentar=$row["kommentar"];
  $kommentar=str_replace('\r\n', "\r\n", $kommentar);
  echo '<textarea name="kommentartext" id="kommentartext" cols="130" rows="20">'.$kommentar.'</textarea>';
echo '</td></tr>';
echo '<tr><td align="center">';
echo '<br><input type="Submit" name="kommentar" value="aktuelle Daten speichern" style="width:175px; color:#ff0000;"><br><br>';
echo '</td></tr>';
echo '</table><br><br>';

echo $servermobi_lang['serveruebersicht'].'<br>';
echo '<table border="0" cellpadding="3" cellspacing="0">
      <tr align="center">
        <td width="200"><b>Server</td>
        <td width="150"><b>Account-ID</td>
      </tr>';

for ($i=0;$i<=$sindex;$i++){
  //feststellen auf welchen servern er einen account hat
  $hasaccount=doPost($serverdata[$i][6].'rpc.php', 'authcode='.$GLOBALS['env_rpc_authcode'].'&isaccount=1&id='.$uid, $serverdata[$i][5]);

  echo "<tr>
          <td>".$serverdata[$i][0]." - ".$serverdata[$i][1]."</td>";
  echo   '<td><div align="center">'.$hasaccount.' <a href="https://'.$serverdata[$i][5].'/ourdetool/idinfo.php?UID='.$hasaccount.'" target="_blank">LINK</a></div></td>
        </tr>';
}
echo '</table>';


}
else echo 'Kein User ausgew&auml;hlt.';

function modpass($pass)
{
  $pass[0]="*";
  $pass[1]="*";
  $pass[2]="*";
  $pass[3]="*";
  return($pass);
}

?>
</form>
</body>
</html>